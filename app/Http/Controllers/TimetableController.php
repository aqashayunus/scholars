<?php

namespace App\Http\Controllers;

use App\Models\AcademicEvent;
use App\Models\Assignment;
use App\Models\ClassRequest;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LiveClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Models\UserNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimetableController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // 1. STUDENT TIMETABLE
    // ─────────────────────────────────────────────────────────────────────────
    public function studentIndex(Request $request)
    {
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)->firstOrFail();

        // Enrolled courses
        $courses = Course::whereHas('enrollments', fn($q) => $q->where('student_id', $student->id))
            ->with('subject')
            ->get();

        $subjects = $courses->pluck('subject')->filter()->unique('id')->values();

        // Check if student has restriction
        $restrictionService = app(\App\Services\StudentAccessRestrictionService::class);
        $isRestricted = $restrictionService->isStudentRestricted($student);
        $restrictionDetails = $isRestricted ? $restrictionService->getRestrictionDetails($student) : null;

        return view('student.timetable', compact(
            'student',
            'courses',
            'subjects',
            'isRestricted',
            'restrictionDetails'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. TEACHER TIMETABLE
    // ─────────────────────────────────────────────────────────────────────────
    public function teacherIndex(Request $request)
    {
        $user = auth()->user();
        $teacher = Teacher::where('user_id', $user->id)
            ->with('courses.subject')
            ->firstOrFail();

        $courses = $teacher->courses()->withCount('enrollments')->with('subject')->get();
        $subjects = $courses->pluck('subject')->filter()->unique('id')->values();

        // Summary stats for timetable header
        $courseIds = $courses->pluck('id');
        $classesThisWeek = LiveClass::where(function ($q) use ($courseIds, $teacher) {
                $q->whereIn('course_id', $courseIds)->orWhere('teacher_id', $teacher->id);
            })
            ->whereBetween('scheduled_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        $upcomingCount = LiveClass::where(function ($q) use ($courseIds, $teacher) {
                $q->whereIn('course_id', $courseIds)->orWhere('teacher_id', $teacher->id);
            })
            ->activeOrUpcoming()
            ->count();

        $rescheduledCount = LiveClass::where(function ($q) use ($courseIds, $teacher) {
                $q->whereIn('course_id', $courseIds)->orWhere('teacher_id', $teacher->id);
            })
            ->where('is_rescheduled', true)
            ->count();

        return view('teacher.timetable', compact(
            'teacher',
            'courses',
            'subjects',
            'classesThisWeek',
            'upcomingCount',
            'rescheduledCount'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. PARENT TIMETABLE
    // ─────────────────────────────────────────────────────────────────────────
    public function parentIndex(Request $request)
    {
        $user = auth()->user();
        $children = $this->getParentChildren($user);

        $palette = [
            ['color' => '#2563eb', 'bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'text' => 'text-blue-800', 'dot' => 'bg-blue-600', 'hex_bg' => '#eff6ff', 'hex_border' => '#bfdbfe', 'hex_text' => '#1e40af'],
            ['color' => '#7c3aed', 'bg' => 'bg-purple-50', 'border' => 'border-purple-200', 'text' => 'text-purple-800', 'dot' => 'bg-purple-600', 'hex_bg' => '#f5f3ff', 'hex_border' => '#ddd6fe', 'hex_text' => '#5b21b6'],
            ['color' => '#059669', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'text' => 'text-emerald-800', 'dot' => 'bg-emerald-600', 'hex_bg' => '#ecfdf5', 'hex_border' => '#a7f3d0', 'hex_text' => '#065f46'],
            ['color' => '#d97706', 'bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'text' => 'text-amber-800', 'dot' => 'bg-amber-600', 'hex_bg' => '#fffbeb', 'hex_border' => '#fde68a', 'hex_text' => '#92400e'],
            ['color' => '#e11d48', 'bg' => 'bg-rose-50', 'border' => 'border-rose-200', 'text' => 'text-rose-800', 'dot' => 'bg-rose-600', 'hex_bg' => '#fff1f2', 'hex_border' => '#fecdd3', 'hex_text' => '#9f1239'],
            ['color' => '#0891b2', 'bg' => 'bg-cyan-50', 'border' => 'border-cyan-200', 'text' => 'text-cyan-800', 'dot' => 'bg-cyan-600', 'hex_bg' => '#ecfeff', 'hex_border' => '#a5f3fc', 'hex_text' => '#155e75'],
        ];

        foreach ($children as $idx => $ch) {
            $ch->theme = $palette[$idx % count($palette)];
        }

        return view('parent.timetable', compact(
            'children'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3B. ADMIN MASTER TIMETABLE
    // ─────────────────────────────────────────────────────────────────────────
    public function adminIndex(Request $request)
    {
        return redirect(route('admin.dashboard') . '#timetable');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4. UNIFIED EVENTS JSON API (For Month, Week, Day, Annual views)
    // ─────────────────────────────────────────────────────────────────────────
    public function getEventsJson(Request $request)
    {
        $user = auth()->user();
        $startStr = $request->query('start');
        $endStr   = $request->query('end');
        $year     = (int) $request->query('year', now()->year);
        $role     = $request->query('role', $user->role ?? 'student');

        if ($startStr && $endStr) {
            $startDate = Carbon::parse($startStr)->startOfDay();
            $endDate   = Carbon::parse($endStr)->endOfDay();
        } else {
            // Default to entire requested year for seamless annual + month navigation
            $startDate = Carbon::createFromDate($year, 1, 1)->startOfDay();
            $endDate   = Carbon::createFromDate($year, 12, 31)->endOfDay();
        }

        $events = [];

        // 1. Fetch Public Holidays & Academic Events
        $academicEvents = AcademicEvent::inDateRange($startDate, $endDate)->get();
        foreach ($academicEvents as $ae) {
            $events[] = [
                'id'          => 'academic_' . $ae->id,
                'academic_id' => $ae->id,
                'type'        => $ae->event_type, // public_holiday, school_break, exam_period, academic_term
                'title'       => $ae->title,
                'description' => $ae->description,
                'start'       => $ae->start_date->format('Y-m-d'),
                'end'         => $ae->end_date ? $ae->end_date->format('Y-m-d') : $ae->start_date->format('Y-m-d'),
                'is_all_day'  => (bool) $ae->is_all_day,
                'color'       => $ae->display_color,
                'can_edit'    => $role === 'admin',
            ];
        }

        // 2. Fetch Live Classes according to user role
        if ($role === 'student') {
            $student = Student::where('user_id', $user->id)->first();
            if ($student) {
                $this->appendStudentClassEvents($student, $startDate, $endDate, $events);
                $this->appendStudentAssignmentEvents($student, $startDate, $endDate, $events);
            }
        } elseif ($role === 'teacher') {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if ($teacher) {
                $this->appendTeacherClassEvents($teacher, $startDate, $endDate, $events);
            }
        } elseif ($role === 'parent') {
            $children = $this->getParentChildren($user);

            // Always display all children simultaneously with vibrant distinct colors
            $palette = [
                ['color' => '#2563eb', 'bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'text' => 'text-blue-800', 'dot' => 'bg-blue-600', 'hex_bg' => '#eff6ff', 'hex_border' => '#bfdbfe', 'hex_text' => '#1e40af'],
                ['color' => '#7c3aed', 'bg' => 'bg-purple-50', 'border' => 'border-purple-200', 'text' => 'text-purple-800', 'dot' => 'bg-purple-600', 'hex_bg' => '#f5f3ff', 'hex_border' => '#ddd6fe', 'hex_text' => '#5b21b6'],
                ['color' => '#059669', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'text' => 'text-emerald-800', 'dot' => 'bg-emerald-600', 'hex_bg' => '#ecfdf5', 'hex_border' => '#a7f3d0', 'hex_text' => '#065f46'],
                ['color' => '#d97706', 'bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'text' => 'text-amber-800', 'dot' => 'bg-amber-600', 'hex_bg' => '#fffbeb', 'hex_border' => '#fde68a', 'hex_text' => '#92400e'],
                ['color' => '#e11d48', 'bg' => 'bg-rose-50', 'border' => 'border-rose-200', 'text' => 'text-rose-800', 'dot' => 'bg-rose-600', 'hex_bg' => '#fff1f2', 'hex_border' => '#fecdd3', 'hex_text' => '#9f1239'],
                ['color' => '#0891b2', 'bg' => 'bg-cyan-50', 'border' => 'border-cyan-200', 'text' => 'text-cyan-800', 'dot' => 'bg-cyan-600', 'hex_bg' => '#ecfeff', 'hex_border' => '#a5f3fc', 'hex_text' => '#155e75'],
            ];

            foreach ($children as $idx => $child) {
                $theme = $palette[$idx % count($palette)];
                $childName = $child->user->name ?? 'Child ' . ($idx + 1);
                $this->appendStudentClassEvents($child, $startDate, $endDate, $events, $childName, $theme['color'], $theme);
                $this->appendStudentAssignmentEvents($child, $startDate, $endDate, $events, $childName, $theme['color'], $theme);
            }
        } elseif ($role === 'admin') {
            $classQuery = LiveClass::whereBetween('scheduled_at', [$startDate, $endDate])
                ->with(['course.subject', 'course.enrollments', 'teacher.user', 'course.teacher.user']);

            if ($request->filled('teacher_id')) {
                $tId = $request->query('teacher_id');
                $classQuery->where(function ($q) use ($tId) {
                    $q->where('teacher_id', $tId)
                      ->orWhereHas('course', fn($c) => $c->where('teacher_id', $tId));
                });
            }

            if ($request->filled('course_id')) {
                $classQuery->where('course_id', $request->query('course_id'));
            }

            $classes = $classQuery->get();
            foreach ($classes as $lc) {
                $course = $lc->course;
                $subject = $course?->subject?->display_name ?? $course?->subject?->name ?? 'Teaching Session';
                $teacherName = $lc->teacher?->user?->name ?? $course?->teacher?->user?->name ?? 'Assigned Instructor';
                $duration = (int) ($lc->duration_minutes ?: 60);
                $endTime = $lc->scheduled_at->copy()->addMinutes($duration);
                $studentCount = $course?->enrollments?->count() ?? 0;

                $events[] = [
                    'id'                    => 'class_' . $lc->id,
                    'class_id'              => $lc->id,
                    'course_id'             => $course?->id,
                    'teacher_id'            => $lc->teacher_id ?? $course?->teacher_id,
                    'type'                  => 'live_class',
                    'title'                 => $lc->title,
                    'subject_name'          => $subject,
                    'course_title'          => $course?->title ?? '',
                    'teacher_name'          => $teacherName,
                    'student_count'         => $studentCount,
                    'start'                 => $lc->scheduled_at->toIso8601String(),
                    'end'                   => $endTime->toIso8601String(),
                    'date_str'              => $lc->scheduled_at->format('Y-m-d'),
                    'time_str'              => $lc->scheduled_at->format('h:i A'),
                    'duration_minutes'      => $duration,
                    'meeting_url'           => $lc->meeting_url,
                    'status'                => $lc->status,
                    'is_rescheduled'        => (bool) $lc->is_rescheduled,
                    'original_scheduled_at' => $lc->original_scheduled_at ? $lc->original_scheduled_at->format('d M Y, h:i A') : null,
                    'reschedule_reason'     => $lc->reschedule_reason,
                    'color'                 => '#2563eb',
                    'can_edit'              => true,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'year'    => $year,
            'start'   => $startDate->toDateString(),
            'end'     => $endDate->toDateString(),
            'events'  => $events,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 5. TEACHER DIRECT RESCHEDULE & UPDATE
    // ─────────────────────────────────────────────────────────────────────────
    public function teacherReschedule(Request $request, $id)
    {
        $user = auth()->user();
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
        $courseIds = $teacher->courses->pluck('id')->toArray();

        $liveClass = LiveClass::where('id', $id)
            ->where(function ($q) use ($courseIds, $teacher) {
                $q->whereIn('course_id', $courseIds)->orWhere('teacher_id', $teacher->id);
            })
            ->with(['course.subject', 'course.enrollments.student.user'])
            ->firstOrFail();

        $validated = $request->validate([
            'scheduled_date'   => 'required|date|after_or_equal:today',
            'scheduled_time'   => 'required|string',
            'duration_minutes' => 'nullable|integer|min:15|max:300',
            'reason'           => 'required|string|min:5|max:1000',
            'meeting_url'      => 'nullable|url|max:1000',
        ]);

        $newDateTime = Carbon::parse($validated['scheduled_date'] . ' ' . $validated['scheduled_time']);
        $oldDateTime = $liveClass->scheduled_at ? $liveClass->scheduled_at->copy() : null;

        // Update live class with reschedule audit info
        $liveClass->update([
            'is_rescheduled'        => true,
            'original_scheduled_at' => $liveClass->original_scheduled_at ?? $oldDateTime,
            'scheduled_at'          => $newDateTime,
            'duration_minutes'      => $validated['duration_minutes'] ?? $liveClass->duration_minutes,
            'meeting_url'           => !empty($validated['meeting_url']) ? $validated['meeting_url'] : $liveClass->meeting_url,
            'reschedule_reason'     => $validated['reason'],
            'rescheduled_by'        => $user->id,
        ]);

        // Keep ClassRequest record for administrative oversight
        ClassRequest::create([
            'live_class_id' => $liveClass->id,
            'teacher_id'    => $teacher->id,
            'type'          => 'reschedule',
            'reason'        => $validated['reason'],
            'requested_at'  => $newDateTime,
            'status'        => 'approved',
            'admin_note'    => 'Rescheduled directly by instructor ' . $user->name,
        ]);

        // Dispatch notifications to enrolled students & their parents
        $this->notifyStudentsAndParentsOfReschedule($liveClass, $oldDateTime, $newDateTime, $validated['reason'], $user->name);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Session successfully rescheduled to {$newDateTime->format('d M Y, h:i A')}. All enrolled students and parents have been notified.",
                'class'   => $liveClass,
            ]);
        }

        return redirect()->back()->with('success', "Session successfully rescheduled to {$newDateTime->format('d M Y, h:i A')}. Students and parents have been notified.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6. TEACHER QUICK CREATE SESSION
    // ─────────────────────────────────────────────────────────────────────────
    public function quickCreateSession(Request $request)
    {
        $user = auth()->user();
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
        $courseIds = $teacher->courses->pluck('id')->toArray();

        $validated = $request->validate([
            'course_id'        => 'required|in:' . implode(',', $courseIds),
            'title'            => 'required|string|max:255',
            'scheduled_date'   => 'required|date|after_or_equal:today',
            'scheduled_time'   => 'required|string',
            'duration_minutes' => 'required|integer|min:15|max:300',
            'meeting_url'      => 'nullable|url|max:1000',
        ]);

        $scheduledAt = Carbon::parse($validated['scheduled_date'] . ' ' . $validated['scheduled_time']);
        $course = Course::findOrFail($validated['course_id']);

        $liveClass = LiveClass::create([
            'course_id'        => $course->id,
            'teacher_id'       => $teacher->id,
            'title'            => $validated['title'],
            'scheduled_at'     => $scheduledAt,
            'duration_minutes' => $validated['duration_minutes'],
            'meeting_url'      => $validated['meeting_url'] ?? null,
            'status'           => 'upcoming',
        ]);

        // Dispatch notifications to enrolled students & linked parents
        $this->notifyStudentsAndParentsOfNewClass($liveClass);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'New live class session created successfully.',
                'class'   => $liveClass,
            ]);
        }

        return redirect()->back()->with('success', 'New live class session scheduled successfully!');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6B. ADMIN RESCHEDULE & QUICK CREATE
    // ─────────────────────────────────────────────────────────────────────────
    public function adminReschedule(Request $request, $id)
    {
        $liveClass = LiveClass::with(['course.subject', 'course.teacher.user', 'teacher.user', 'course.enrollments.student.user'])
            ->findOrFail($id);

        $validated = $request->validate([
            'scheduled_date'   => 'required|date',
            'scheduled_time'   => 'required|string',
            'duration_minutes' => 'nullable|integer|min:15|max:300',
            'reason'           => 'required|string|min:3|max:1000',
            'meeting_url'      => 'nullable|url|max:1000',
        ]);

        $newDateTime = Carbon::parse($validated['scheduled_date'] . ' ' . $validated['scheduled_time']);
        $oldDateTime = $liveClass->scheduled_at ? $liveClass->scheduled_at->copy() : null;

        $liveClass->update([
            'is_rescheduled'        => true,
            'original_scheduled_at' => $liveClass->original_scheduled_at ?? $oldDateTime,
            'scheduled_at'          => $newDateTime,
            'duration_minutes'      => $validated['duration_minutes'] ?? $liveClass->duration_minutes,
            'reschedule_reason'     => $validated['reason'],
            'meeting_url'           => $validated['meeting_url'] ?? $liveClass->meeting_url,
            'rescheduled_by'        => auth()->id(),
        ]);

        $teacherUser = $liveClass->teacher?->user ?? $liveClass->course?->teacher?->user;
        $teacherName = $teacherUser?->name ?? 'School Administration';

        // Notify students and parents
        $this->notifyStudentsAndParentsOfReschedule($liveClass, $oldDateTime, $newDateTime, $validated['reason'], $teacherName);

        // Notify teacher as well
        if ($teacherUser) {
            UserNotification::create([
                'user_id' => $teacherUser->id,
                'type'    => 'class_reminder',
                'title'   => "Class Session Rescheduled by Administrator",
                'message' => "Session '{$liveClass->title}' has been moved to {$newDateTime->format('d M Y, h:i A')} by administration. Reason: \"{$validated['reason']}\".",
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Class session successfully rescheduled to {$newDateTime->format('d M Y, h:i A')}. Teacher, students, and parents notified.",
                'class'   => $liveClass,
            ]);
        }

        return redirect()->back()->with('success', "Class session rescheduled successfully to {$newDateTime->format('d M Y, h:i A')}.");
    }

    public function adminQuickCreateSession(Request $request)
    {
        $validated = $request->validate([
            'course_id'        => 'required|exists:courses,id',
            'teacher_id'       => 'nullable|exists:teachers,id',
            'title'            => 'required|string|max:255',
            'scheduled_date'   => 'required|date',
            'scheduled_time'   => 'required|string',
            'duration_minutes' => 'required|integer|min:15|max:300',
            'meeting_url'      => 'nullable|url|max:1000',
        ]);

        $course = Course::findOrFail($validated['course_id']);
        $teacherId = (!empty($validated['teacher_id'])) ? $validated['teacher_id'] : $course->teacher_id;
        $scheduledAt = Carbon::parse($validated['scheduled_date'] . ' ' . $validated['scheduled_time']);

        // Auto-sync eligible students to this course if it currently has 0 enrollments
        if ($course->enrollments()->count() === 0) {
            $subjectName = $course->subject?->name;
            $candidateStudentIds = collect();

            if ($subjectName) {
                $matchingCourseIds = Course::whereHas('subject', fn($sq) => $sq->where('name', $subjectName))->pluck('id');
                $candidateStudentIds = Enrollment::whereIn('course_id', $matchingCourseIds)->pluck('student_id');
            }

            if ($candidateStudentIds->isEmpty()) {
                if ($course->level === 'Primary' || str_contains(strtolower($course->level ?? ''), 'year')) {
                    $candidateStudentIds = Student::where('education_level', 'Primary')->orWhereNull('education_level')->pluck('id');
                } else {
                    $candidateStudentIds = Student::pluck('id');
                }
            }

            foreach ($candidateStudentIds->unique() as $stId) {
                Enrollment::firstOrCreate(
                    ['student_id' => $stId, 'course_id' => $course->id],
                    ['status' => 'active', 'progress_pct' => 0, 'completed_lessons' => 0, 'enrolled_at' => now()]
                );
            }
        }

        $liveClass = LiveClass::create([
            'course_id'        => $course->id,
            'teacher_id'       => $teacherId,
            'title'            => $validated['title'],
            'scheduled_at'     => $scheduledAt,
            'duration_minutes' => $validated['duration_minutes'],
            'meeting_url'      => $validated['meeting_url'] ?? null,
            'status'           => 'upcoming',
        ]);

        // Notify assigned instructor
        $teacher = Teacher::with('user')->find($teacherId);
        if ($teacher?->user) {
            UserNotification::create([
                'user_id' => $teacher->user->id,
                'type'    => 'class_reminder',
                'title'   => "New Class Session Scheduled by Administration",
                'message' => "A new live class session '{$liveClass->title}' has been scheduled for {$scheduledAt->format('d M Y, h:i A')}.",
            ]);
        }

        // Dispatch notifications to enrolled students & linked parents
        $this->notifyStudentsAndParentsOfNewClass($liveClass);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'New live class session scheduled successfully.',
                'class'   => $liveClass,
            ]);
        }

        return redirect()->back()->with('success', 'Live class scheduled successfully!');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6C. ACADEMIC EVENTS CRUD (Holidays, Breaks, Terms, Exams)
    // ─────────────────────────────────────────────────────────────────────────
    public function storeAcademicEvent(Request $request)
    {
        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string|max:1000',
            'event_type'      => 'required|in:public_holiday,school_break,exam_period,academic_term',
            'start_date'      => 'required|date',
            'end_date'        => 'nullable|date|after_or_equal:start_date',
            'is_all_day'      => 'nullable|boolean',
            'color'           => 'nullable|string|max:30',
            'state_or_region' => 'nullable|string|max:100',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['is_all_day'] = $request->boolean('is_all_day', true);

        $event = AcademicEvent::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Academic event created successfully.',
                'event'   => $event,
            ]);
        }

        return redirect()->back()->with('success', 'Academic event created successfully.');
    }

    public function updateAcademicEvent(Request $request, $id)
    {
        $event = AcademicEvent::findOrFail($id);

        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string|max:1000',
            'event_type'      => 'required|in:public_holiday,school_break,exam_period,academic_term',
            'start_date'      => 'required|date',
            'end_date'        => 'nullable|date|after_or_equal:start_date',
            'is_all_day'      => 'nullable|boolean',
            'color'           => 'nullable|string|max:30',
            'state_or_region' => 'nullable|string|max:100',
        ]);

        $validated['is_all_day'] = $request->boolean('is_all_day', true);
        $event->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Academic event updated successfully.',
                'event'   => $event,
            ]);
        }

        return redirect()->back()->with('success', 'Academic event updated successfully.');
    }

    public function destroyAcademicEvent(Request $request, $id)
    {
        $event = AcademicEvent::findOrFail($id);
        $event->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Academic event deleted successfully.',
            ]);
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
        $description = "Subject: {$subject}\\nInstructor: {$teacher}\\nLink: " . ($liveClass->meeting_url ?? 'N/A');

        $ics = "BEGIN:VCALENDAR\r\n" .
               "VERSION:2.0\r\n" .
               "PRODID:-//Olympian Scholars//Timetable Calendar//EN\r\n" .
               "CALSCALE:GREGORIAN\r\n" .
               "METHOD:PUBLISH\r\n" .
               "BEGIN:VEVENT\r\n" .
               "UID:olympian-class-{$liveClass->id}@olympianscholars.com\r\n" .
               "DTSTAMP:" . now()->utc()->format('Ymd\THis\Z') . "\r\n" .
               "DTSTART:" . $start->utc()->format('Ymd\THis\Z') . "\r\n" .
               "DTEND:" . $end->utc()->format('Ymd\THis\Z') . "\r\n" .
               "SUMMARY:{$title} ({$subject})\r\n" .
               "DESCRIPTION:{$description}\r\n" .
               "STATUS:CONFIRMED\r\n" .
               "END:VEVENT\r\n" .
               "END:VCALENDAR\r\n";

        return response($ics, 200, [
            'Content-Type'        => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="olympian-class-' . $liveClass->id . '.ics"',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private function appendStudentClassEvents(Student $student, Carbon $start, Carbon $end, array &$events, ?string $childLabel = null, ?string $overrideColor = null, ?array $childTheme = null): void
    {
        $classes = StudentDashboardController::getStudentLiveClassesQuery($student)
            ->whereBetween('scheduled_at', [$start, $end])
            ->with(['course.subject', 'course.teacher.user'])
            ->get();

        // Attendance mapping
        $attendances = StudentAttendance::where('student_id', $student->id)
            ->whereIn('live_class_id', $classes->pluck('id'))
            ->pluck('status', 'live_class_id');

        foreach ($classes as $lc) {
            $course = $lc->course;
            $subject = $course?->subject?->display_name ?? $course?->subject?->name ?? 'Academic Class';
            $teacherName = $lc->teacher?->user?->name ?? $course?->teacher?->user?->name ?? 'Instructor';
            $attStatus = $attendances[$lc->id] ?? null;

            $duration = (int) ($lc->duration_minutes ?: 60);
            $endTime = $lc->scheduled_at->copy()->addMinutes($duration);

            $events[] = [
                'id'                    => 'class_' . $lc->id . '_' . $student->id,
                'class_id'              => $lc->id,
                'type'                  => 'live_class',
                'title'                 => $lc->title,
                'subject_name'          => $subject,
                'course_title'          => $course?->title ?? '',
                'teacher_name'          => $teacherName,
                'start'                 => $lc->scheduled_at->toIso8601String(),
                'end'                   => $endTime->toIso8601String(),
                'date_str'              => $lc->scheduled_at->format('Y-m-d'),
                'time_str'              => $lc->scheduled_at->format('h:i A'),
                'duration_minutes'      => $duration,
                'meeting_url'           => $lc->meeting_url,
                'status'                => $lc->status, // upcoming, live, completed
                'is_rescheduled'        => (bool) $lc->is_rescheduled,
                'original_scheduled_at' => $lc->original_scheduled_at ? $lc->original_scheduled_at->format('d M Y, h:i A') : null,
                'reschedule_reason'     => $lc->reschedule_reason,
                'attendance_status'     => $attStatus, // present, absent, late, null
                'child_name'            => $childLabel,
                'child_id'              => $student->id,
                'child_theme'           => $childTheme,
                'color'                 => $overrideColor ?: '#2563eb', // Default Blue
                'can_edit'              => false,
            ];
        }
    }

    private function appendTeacherClassEvents(Teacher $teacher, Carbon $start, Carbon $end, array &$events): void
    {
        $courseIds = $teacher->courses->pluck('id');

        $classes = LiveClass::where(function ($q) use ($courseIds, $teacher) {
                $q->whereIn('course_id', $courseIds)->orWhere('teacher_id', $teacher->id);
            })
            ->whereBetween('scheduled_at', [$start, $end])
            ->with(['course.subject', 'course.enrollments'])
            ->get();

        foreach ($classes as $lc) {
            $course = $lc->course;
            $subject = $course?->subject?->display_name ?? $course?->subject?->name ?? 'Teaching Session';
            $duration = (int) ($lc->duration_minutes ?: 60);
            $endTime = $lc->scheduled_at->copy()->addMinutes($duration);
            $studentCount = $course?->enrollments?->count() ?? 0;

            $events[] = [
                'id'                    => 'class_' . $lc->id,
                'class_id'              => $lc->id,
                'course_id'             => $course?->id,
                'type'                  => 'live_class',
                'title'                 => $lc->title,
                'subject_name'          => $subject,
                'course_title'          => $course?->title ?? '',
                'student_count'         => $studentCount,
                'start'                 => $lc->scheduled_at->toIso8601String(),
                'end'                   => $endTime->toIso8601String(),
                'date_str'              => $lc->scheduled_at->format('Y-m-d'),
                'time_str'              => $lc->scheduled_at->format('h:i A'),
                'duration_minutes'      => $duration,
                'meeting_url'           => $lc->meeting_url,
                'status'                => $lc->status,
                'is_rescheduled'        => (bool) $lc->is_rescheduled,
                'original_scheduled_at' => $lc->original_scheduled_at ? $lc->original_scheduled_at->format('d M Y, h:i A') : null,
                'reschedule_reason'     => $lc->reschedule_reason,
                'color'                 => '#2563eb',
                'can_edit'              => true,
            ];
        }
    }

    private function appendStudentAssignmentEvents(Student $student, Carbon $start, Carbon $end, array &$events, ?string $childLabel = null, ?string $overrideColor = null, ?array $childTheme = null): void
    {
        $assignments = Assignment::whereHas('course.enrollments', fn($q) => $q->where('student_id', $student->id))
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$start, $end])
            ->with('course.subject')
            ->get();

        foreach ($assignments as $a) {
            $dueDate = Carbon::parse($a->due_date);
            $events[] = [
                'id'           => 'task_' . $a->id . '_' . $student->id,
                'type'         => 'assignment',
                'title'        => 'Due: ' . $a->title,
                'subject_name' => $a->course->subject->display_name ?? $a->course->subject->name ?? 'Course Task',
                'course_title' => $a->course->title ?? '',
                'start'        => $dueDate->toIso8601String(),
                'end'          => $dueDate->copy()->addHour()->toIso8601String(),
                'date_str'     => $dueDate->format('Y-m-d'),
                'time_str'     => $dueDate->format('h:i A'),
                'child_name'   => $childLabel,
                'child_id'     => $student->id,
                'child_theme'  => $childTheme,
                'color'        => $overrideColor ?: '#f59e0b', // Amber
                'can_edit'     => false,
            ];
        }
    }

    private function notifyStudentsAndParentsOfReschedule(LiveClass $liveClass, ?Carbon $oldTime, Carbon $newTime, string $reason, string $teacherName): void
    {
        $oldTimeStr = $oldTime ? $oldTime->format('D, d M h:i A') : 'previous time';
        $newTimeStr = $newTime->format('D, d M h:i A');

        $enrollments = $liveClass->course?->enrollments()->with('student.user')->get() ?? collect();

        foreach ($enrollments as $enrollment) {
            $student = $enrollment->student;
            if (!$student) {
                continue;
            }

            // 1. Notify Student
            if ($student->user) {
                UserNotification::create([
                    'user_id' => $student->user->id,
                    'type'    => 'class_reminder',
                    'title'   => 'Class Rescheduled: ' . $liveClass->title,
                    'message' => "Teacher {$teacherName} has rescheduled {$liveClass->title} from {$oldTimeStr} to {$newTimeStr}. Reason: \"{$reason}\".",
                ]);
            }

            // 2. Notify Linked Parent(s)
            $parentUserIds = [];
            if ($student->parent_user_id) {
                $parentUserIds[] = $student->parent_user_id;
            }

            $pivotParents = DB::table('parent_student')
                ->where('student_id', $student->id)
                ->where('status', 'active')
                ->pluck('parent_user_id')
                ->toArray();

            $parentUserIds = array_unique(array_merge($parentUserIds, $pivotParents));

            foreach ($parentUserIds as $parentId) {
                UserNotification::create([
                    'user_id' => $parentId,
                    'type'    => 'class_reminder',
                    'title'   => "Child's Class Rescheduled ({$student->user->name})",
                    'message' => "{$liveClass->title} for {$student->user->name} has been moved to {$newTimeStr} by {$teacherName}. Reason: \"{$reason}\".",
                ]);
            }
        }
    }

    private function notifyStudentsAndParentsOfNewClass(LiveClass $liveClass): void
    {
        $liveClass->loadMissing(['course.subject', 'teacher.user', 'course.teacher.user', 'course.enrollments.student.user']);
        $subjectName = $liveClass->course?->subject?->name ?? 'Class';
        $timeStr = $liveClass->scheduled_at ? $liveClass->scheduled_at->format('D, d M Y, h:i A') : 'TBD';
        $teacherName = $liveClass->teacher?->user?->name ?? $liveClass->course?->teacher?->user?->name ?? 'Instructor';

        $enrollments = $liveClass->course?->enrollments ?? collect();

        foreach ($enrollments as $enrollment) {
            $student = $enrollment->student;
            if (!$student) continue;

            // 1. Notify Student
            if ($student->user) {
                UserNotification::create([
                    'user_id' => $student->user->id,
                    'type'    => 'class_reminder',
                    'title'   => "New Class Scheduled: {$liveClass->title}",
                    'message' => "A new {$subjectName} live class '{$liveClass->title}' with {$teacherName} has been scheduled for {$timeStr}.",
                ]);
            }

            // 2. Notify Linked Parent(s)
            $parentUserIds = [];
            if ($student->parent_user_id) {
                $parentUserIds[] = $student->parent_user_id;
            }
            $pivotParents = DB::table('parent_student')
                ->where('student_id', $student->id)
                ->where('status', 'active')
                ->pluck('parent_user_id')
                ->toArray();
            $parentUserIds = array_unique(array_merge($parentUserIds, $pivotParents));

            foreach ($parentUserIds as $parentId) {
                UserNotification::create([
                    'user_id' => $parentId,
                    'type'    => 'class_reminder',
                    'title'   => "New Class Scheduled for {$student->user->name}",
                    'message' => "A new live class session '{$liveClass->title}' ({$subjectName}) has been scheduled for {$student->user->name} on {$timeStr}.",
                ]);
            }
        }
    }

    private function getParentChildren(User $user): \Illuminate\Database\Eloquent\Collection
    {
        $legacyIds = Student::where('parent_user_id', $user->id)->pluck('id')->toArray();
        $pivotIds = DB::table('parent_student')
            ->where('parent_user_id', $user->id)
            ->where('status', 'active')
            ->pluck('student_id')
            ->toArray();

        $allIds = array_unique(array_merge($legacyIds, $pivotIds));

        return Student::whereIn('id', $allIds)
            ->with(['user', 'enrollments.course.subject'])
            ->get();
    }
}
