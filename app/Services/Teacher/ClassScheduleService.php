<?php

namespace App\Services\Teacher;

use App\Models\AttendanceCorrectionRequest;
use App\Models\ClassRequest;
use App\Models\LiveClass;
use App\Models\Teacher;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

class ClassScheduleService
{
    private function getTeacher(): Teacher
    {
        return Teacher::where('user_id', auth()->id())->firstOrFail();
    }

    public function toggleDuty(): Teacher
    {
        $teacher = $this->getTeacher();
        $teacher->is_online = ! $teacher->is_online;
        $teacher->save();

        return $teacher;
    }

    public function storeClass(Request $request): LiveClass
    {
        $teacher = $this->getTeacher();
        $courseIds = $teacher->courses->pluck('id')->toArray();

        $validated = $request->validate([
            'course_id' => 'required|in:'.implode(',', $courseIds),
            'title' => 'required|string|max:255',
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'nullable|integer|min:1|max:480',
            'meeting_url' => 'nullable|url|max:255',
        ]);

        $validated['teacher_id'] = $teacher->id;
        $validated['status'] = 'upcoming';

        return LiveClass::create($validated);
    }

    public function requestCancel(int $id, Request $request): void
    {
        $teacher = $this->getTeacher();
        $courseIds = $teacher->courses->pluck('id')->toArray();

        $liveClass = LiveClass::where('id', $id)
            ->where(function ($q) use ($courseIds, $teacher) {
                $q->whereIn('course_id', $courseIds)->orWhere('teacher_id', $teacher->id);
            })
            ->firstOrFail();

        $validated = $request->validate([
            'reason' => 'required|string|min:5|max:1000',
        ]);

        ClassRequest::create([
            'live_class_id' => $liveClass->id,
            'teacher_id' => $teacher->id,
            'type' => 'cancel',
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);
    }

    public function requestReschedule(int $id, Request $request): void
    {
        $teacher = $this->getTeacher();
        $courseIds = $teacher->courses->pluck('id')->toArray();

        $liveClass = LiveClass::where('id', $id)
            ->where(function ($q) use ($courseIds, $teacher) {
                $q->whereIn('course_id', $courseIds)->orWhere('teacher_id', $teacher->id);
            })
            ->firstOrFail();

        $validated = $request->validate([
            'reason' => 'required|string|min:5|max:1000',
            'requested_at' => 'required|date|after:now',
        ]);

        ClassRequest::create([
            'live_class_id' => $liveClass->id,
            'teacher_id' => $teacher->id,
            'type' => 'reschedule',
            'reason' => $validated['reason'],
            'requested_at' => $validated['requested_at'],
            'status' => 'pending',
        ]);
    }

    /**
     * Teacher starts a session: sets/updates the meeting URL and marks it
     * live. Relies on LiveClass::getComputedStatusAttribute()'s 'live'
     * override to survive the saving() hook's recomputation.
     */
    public function updateMeetingUrl(Request $request, $id): LiveClass
    {
        $teacher = $this->getTeacher();
        $courseIds = $teacher->courses->pluck('id')->toArray();
        $targetId = $request->input('live_class_id', $id);

        $liveClass = LiveClass::where(function ($q) use ($courseIds, $teacher) {
            $q->whereIn('course_id', $courseIds)->orWhere('teacher_id', $teacher->id);
        })
            ->where(function ($q) use ($targetId, $id) {
                $q->where('id', $targetId)
                    ->orWhere('id', $id)
                    ->orWhere('course_id', $id);
            })
            ->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$targetId])
            ->latest('scheduled_at')
            ->firstOrFail();

        $validated = $request->validate([
            'meeting_url' => 'required|url|max:1000',
        ]);

        $liveClass->update([
            'meeting_url' => $validated['meeting_url'],
            'status' => 'live',
        ]);

        return $liveClass;
    }

    /**
     * Teacher saves/verifies attendance for a class in Class Workspace.
     */
    public function saveAttendance(LiveClass $liveClass, Request $request, AttendanceService $attendanceService): array
    {
        $teacher = $this->getTeacher();

        if ($liveClass->teacher_id !== $teacher->id && $liveClass->course?->teacher_id !== $teacher->id) {
            abort(403, 'You are not authorized to manage attendance for this class.');
        }

        $validated = $request->validate([
            'records' => 'required|array',
            'records.*.student_id' => 'required|integer|exists:students,id',
            'records.*.status' => 'required|string',
            'records.*.remarks' => 'nullable|string|max:500',
        ]);

        return $attendanceService->saveTeacherAttendance($teacher, $liveClass, $validated['records'], auth()->user());
    }

    /**
     * Teacher approves/rejects a student's attendance correction request.
     */
    public function reviewCorrection(AttendanceCorrectionRequest $correctionRequest, Request $request, AttendanceService $attendanceService): string
    {
        $teacher = $this->getTeacher();
        $liveClass = $correctionRequest->liveClass;

        if ($liveClass->teacher_id !== $teacher->id && $liveClass->course?->teacher_id !== $teacher->id) {
            abort(403, 'You are not authorized to review this request.');
        }

        $validated = $request->validate([
            'action' => 'required|string|in:approved,rejected',
            'remarks' => 'nullable|string|max:500',
        ]);

        $attendanceService->reviewCorrectionRequest($correctionRequest, auth()->user(), $validated['action'], $validated['remarks'] ?? null);

        return $validated['action'];
    }
}
