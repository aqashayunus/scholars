<?php

namespace App\Http\Controllers;

use App\Http\Requests\Timetable\AdminQuickCreateSessionRequest;
use App\Http\Requests\Timetable\AdminRescheduleRequest;
use App\Http\Requests\Timetable\QuickCreateSessionRequest;
use App\Http\Requests\Timetable\StoreAcademicEventRequest;
use App\Http\Requests\Timetable\TeacherRescheduleRequest;
use App\Http\Requests\Timetable\UpdateAcademicEventRequest;
use App\Models\LiveClass;
use App\Services\TimetableService;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    public function __construct(private TimetableService $timetable) {}

    // ─────────────────────────────────────────────────────────────────────────
    // 1. STUDENT TIMETABLE
    // ─────────────────────────────────────────────────────────────────────────
    public function studentIndex(Request $request)
    {
        return view('student.timetable', $this->timetable->studentIndexData(auth()->user()));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. TEACHER TIMETABLE
    // ─────────────────────────────────────────────────────────────────────────
    public function teacherIndex(Request $request)
    {
        return view('teacher.timetable', $this->timetable->teacherIndexData(auth()->user()));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. PARENT TIMETABLE
    // ─────────────────────────────────────────────────────────────────────────
    public function parentIndex(Request $request)
    {
        return view('parent.timetable', $this->timetable->parentIndexData(auth()->user()));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3B. ADMIN MASTER TIMETABLE
    // ─────────────────────────────────────────────────────────────────────────
    public function adminIndex(Request $request)
    {
        return redirect(route('admin.dashboard').'#timetable');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4. UNIFIED EVENTS JSON API (For Month, Week, Day, Annual views)
    // ─────────────────────────────────────────────────────────────────────────
    public function getEventsJson(Request $request)
    {
        $user = auth()->user();
        $year = (int) $request->query('year', now()->year);
        $role = $request->query('role', $user->role ?? 'student');

        $payload = $this->timetable->eventsJson(
            $user,
            $request->query('start'),
            $request->query('end'),
            $year,
            $role,
            $request->filled('teacher_id') ? $request->query('teacher_id') : null,
            $request->filled('course_id') ? $request->query('course_id') : null,
        );

        return response()->json(array_merge(['success' => true], $payload));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 5. TEACHER DIRECT RESCHEDULE & UPDATE
    // ─────────────────────────────────────────────────────────────────────────
    public function teacherReschedule(TeacherRescheduleRequest $request, $id)
    {
        $validated = $request->validated();

        $result = $this->timetable->rescheduleByTeacher(auth()->user(), (int) $id, $validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Session successfully rescheduled to {$result['newDateTime']->format('d M Y, h:i A')}. All enrolled students and parents have been notified.",
                'class' => $result['liveClass'],
            ]);
        }

        return redirect()->back()->with('success', "Session successfully rescheduled to {$result['newDateTime']->format('d M Y, h:i A')}. Students and parents have been notified.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6. TEACHER QUICK CREATE SESSION
    // ─────────────────────────────────────────────────────────────────────────
    public function quickCreateSession(QuickCreateSessionRequest $request)
    {
        $validated = $request->validated();

        $liveClass = $this->timetable->quickCreateByTeacher(auth()->user(), $validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'New live class session created successfully.',
                'class' => $liveClass,
            ]);
        }

        return redirect()->back()->with('success', 'New live class session scheduled successfully!');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6B. ADMIN RESCHEDULE & QUICK CREATE
    // ─────────────────────────────────────────────────────────────────────────
    public function adminReschedule(AdminRescheduleRequest $request, $id)
    {
        $validated = $request->validated();

        $result = $this->timetable->rescheduleByAdmin((int) $id, $validated, auth()->id());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Class session successfully rescheduled to {$result['newDateTime']->format('d M Y, h:i A')}. Teacher, students, and parents notified.",
                'class' => $result['liveClass'],
            ]);
        }

        return redirect()->back()->with('success', "Class session rescheduled successfully to {$result['newDateTime']->format('d M Y, h:i A')}.");
    }

    public function adminQuickCreateSession(AdminQuickCreateSessionRequest $request)
    {
        $validated = $request->validated();

        $liveClass = $this->timetable->quickCreateByAdmin($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'New live class session scheduled successfully.',
                'class' => $liveClass,
            ]);
        }

        return redirect()->back()->with('success', 'Live class scheduled successfully!');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6C. ACADEMIC EVENTS CRUD (Holidays, Breaks, Terms, Exams)
    // ─────────────────────────────────────────────────────────────────────────
    public function storeAcademicEvent(StoreAcademicEventRequest $request)
    {
        $validated = $request->validated();

        $event = $this->timetable->storeAcademicEvent($validated, auth()->id(), $request->boolean('is_all_day', true));

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Academic event created successfully.', 'event' => $event]);
        }

        return redirect()->back()->with('success', 'Academic event created successfully.');
    }

    public function updateAcademicEvent(UpdateAcademicEventRequest $request, $id)
    {
        $validated = $request->validated();

        $event = $this->timetable->updateAcademicEvent((int) $id, $validated, $request->boolean('is_all_day', true));

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Academic event updated successfully.', 'event' => $event]);
        }

        return redirect()->back()->with('success', 'Academic event updated successfully.');
    }

    public function destroyAcademicEvent(Request $request, $id)
    {
        $this->timetable->destroyAcademicEvent((int) $id);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Academic event deleted successfully.']);
        }

        return redirect()->back()->with('success', 'Academic event deleted successfully.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 7. ICAL (.ics) EXPORT
    // ─────────────────────────────────────────────────────────────────────────
    public function exportIcs(Request $request, $id)
    {
        $liveClass = LiveClass::with(['course.subject', 'course.teacher.user'])->findOrFail($id);

        $start = $liveClass->scheduled_at;
        $end = $start->copy()->addMinutes($liveClass->duration_minutes ?? 60);

        $title = $liveClass->title;
        $subject = $liveClass->course->subject->display_name ?? $liveClass->course->subject->name ?? 'Course';
        $teacher = $liveClass->course->teacher->user->name ?? 'Olympian Scholars Instructor';
        $description = "Subject: {$subject}\\nInstructor: {$teacher}\\nLink: ".($liveClass->meeting_url ?? 'N/A');

        $ics = "BEGIN:VCALENDAR\r\n".
               "VERSION:2.0\r\n".
               "PRODID:-//Olympian Scholars//Timetable Calendar//EN\r\n".
               "CALSCALE:GREGORIAN\r\n".
               "METHOD:PUBLISH\r\n".
               "BEGIN:VEVENT\r\n".
               "UID:olympian-class-{$liveClass->id}@olympianscholars.com\r\n".
               'DTSTAMP:'.now()->utc()->format('Ymd\THis\Z')."\r\n".
               'DTSTART:'.$start->utc()->format('Ymd\THis\Z')."\r\n".
               'DTEND:'.$end->utc()->format('Ymd\THis\Z')."\r\n".
               "SUMMARY:{$title} ({$subject})\r\n".
               "DESCRIPTION:{$description}\r\n".
               "STATUS:CONFIRMED\r\n".
               "END:VEVENT\r\n".
               "END:VCALENDAR\r\n";

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="olympian-class-'.$liveClass->id.'.ics"',
        ]);
    }
}
