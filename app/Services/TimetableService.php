<?php

namespace App\Services;

use App\Models\AcademicEvent;
use App\Models\Assignment;
use App\Models\ClassRequest;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LiveClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Teacher;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\Parent\ChildAccessService;
use App\Services\Student\ClassScheduleService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Timetable domain logic extracted from TimetableController: calendar
 * event aggregation, teacher/admin reschedule + quick-create, academic
 * events, and the related student/parent notifications.
 */
class TimetableService
{
    public function __construct(private ChildAccessService $childAccess)
    {
    }

    public static function colorPalette(): array
    {
        return [
            ['color' => '#2563eb', 'bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'text' => 'text-blue-800', 'dot' => 'bg-blue-600', 'hex_bg' => '#eff6ff', 'hex_border' => '#bfdbfe', 'hex_text' => '#1e40af'],
            ['color' => '#7c3aed', 'bg' => 'bg-purple-50', 'border' => 'border-purple-200', 'text' => 'text-purple-800', 'dot' => 'bg-purple-600', 'hex_bg' => '#f5f3ff', 'hex_border' => '#ddd6fe', 'hex_text' => '#5b21b6'],
            ['color' => '#059669', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'text' => 'text-emerald-800', 'dot' => 'bg-emerald-600', 'hex_bg' => '#ecfdf5', 'hex_border' => '#a7f3d0', 'hex_text' => '#065f46'],
            ['color' => '#d97706', 'bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'text' => 'text-amber-800', 'dot' => 'bg-amber-600', 'hex_bg' => '#fffbeb', 'hex_border' => '#fde68a', 'hex_text' => '#92400e'],
            ['color' => '#e11d48', 'bg' => 'bg-rose-50', 'border' => 'border-rose-200', 'text' => 'text-rose-800', 'dot' => 'bg-rose-600', 'hex_bg' => '#fff1f2', 'hex_border' => '#fecdd3', 'hex_text' => '#9f1239'],
            ['color' => '#0891b2', 'bg' => 'bg-cyan-50', 'border' => 'border-cyan-200', 'text' => 'text-cyan-800', 'dot' => 'bg-cyan-600', 'hex_bg' => '#ecfeff', 'hex_border' => '#a5f3fc', 'hex_text' => '#155e75'],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PAGE VIEW-MODELS
    // ─────────────────────────────────────────────────────────────────────────

    public function studentIndexData(User $user): array
    {
        $student = Student::where('user_id', $user->id)->firstOrFail();

        $courses = Course::whereHas('enrollments', fn($q) => $q->where('student_id', $student->id))
            ->with('subject')
            ->get();

        $subjects = $courses->pluck('subject')->filter()->unique('id')->values();

        $restrictionService = app(StudentAccessRestrictionService::class);
        $isRestricted = $restrictionService->isStudentRestricted($student);
        $restrictionDetails = $isRestricted ? $restrictionService->getRestrictionDetails($student) : null;

        return compact('student', 'courses', 'subjects', 'isRestricted', 'restrictionDetails');
    }

    public function teacherIndexData(User $user): array
    {
        $teacher = Teacher::where('user_id', $user->id)
            ->with('courses.subject')
            ->firstOrFail();

        $courses = $teacher->courses()->withCount('enrollments')->with('subject')->get();
        $subjects = $courses->pluck('subject')->filter()->unique('id')->values();

        $courseIds = $courses->pluck('id');
        $classesThisWeek = LiveClass::where(fn($q) => $q->whereIn('course_id', $courseIds)->orWhere('teacher_id', $teacher->id))
            ->whereBetween('scheduled_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        $upcomingCount = LiveClass::where(fn($q) => $q->whereIn('course_id', $courseIds)->orWhere('teacher_id', $teacher->id))
            ->activeOrUpcoming()
            ->count();

        $rescheduledCount = LiveClass::where(fn($q) => $q->whereIn('course_id', $courseIds)->orWhere('teacher_id', $teacher->id))
            ->where('is_rescheduled', true)
            ->count();

        return compact('teacher', 'courses', 'subjects', 'classesThisWeek', 'upcomingCount', 'rescheduledCount');
    }

    public function parentIndexData(User $user): array
    {
        $children = $this->childAccess->getParentChildren($user);
        $palette  = self::colorPalette();

        foreach ($children as $idx => $ch) {
            $ch->theme = $palette[$idx % count($palette)];
        }

        return compact('children');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // UNIFIED EVENTS JSON API
    // ─────────────────────────────────────────────────────────────────────────

    public function eventsJson(User $user, ?string $startStr, ?string $endStr, int $year, string $role, ?string $teacherIdFilter, ?string $courseIdFilter): array
    {
        if ($startStr && $endStr) {
            $startDate = Carbon::parse($startStr)->startOfDay();
            $endDate   = Carbon::parse($endStr)->endOfDay();
        } else {
            // Default to entire requested year for seamless annual + month navigation
            $startDate = Carbon::createFromDate($year, 1, 1)->startOfDay();
            $endDate   = Carbon::createFromDate($year, 12, 31)->endOfDay();
        }

        $events = [];

        // 1. Public Holidays & Academic Events
        $academicEvents = AcademicEvent::inDateRange($startDate, $endDate)->get();
        foreach ($academicEvents as $ae) {
            $events[] = [
                'id'          => 'academic_' . $ae->id,
                'academic_id' => $ae->id,
                'type'        => $ae->event_type,
                'title'       => $ae->title,
                'description' => $ae->description,
                'start'       => $ae->start_date->format('Y-m-d'),
                'end'         => $ae->end_date ? $ae->end_date->format('Y-m-d') : $ae->start_date->format('Y-m-d'),
                'is_all_day'  => (bool) $ae->is_all_day,
                'color'       => $ae->display_color,
                'can_edit'    => $role === 'admin',
            ];
        }

        // 2. Live Classes according to user role
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
            $children = $this->childAccess->getParentChildren($user);
            $palette  = self::colorPalette();

            foreach ($children as $idx => $child) {
                $theme = $palette[$idx % count($palette)];
                $childName = $child->user->name ?? 'Child ' . ($idx + 1);
                $this->appendStudentClassEvents($child, $startDate, $endDate, $events, $childName, $theme['color'], $theme);
                $this->appendStudentAssignmentEvents($child, $startDate, $endDate, $events, $childName, $theme['color'], $theme);
            }
        } elseif ($role === 'admin') {
            $classQuery = LiveClass::whereBetween('scheduled_at', [$startDate, $endDate])
                ->with(['course.subject', 'course.enrollments', 'teacher.user', 'course.teacher.user']);

            if ($teacherIdFilter) {
                $classQuery->where(function ($q) use ($teacherIdFilter) {
                    $q->where('teacher_id', $teacherIdFilter)
                      ->orWhereHas('course', fn($c) => $c->where('teacher_id', $teacherIdFilter));
                });
            }

            if ($courseIdFilter) {
                $classQuery->where('course_id', $courseIdFilter);
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
                    'status'                => $lc->status->value,
                    'is_rescheduled'        => (bool) $lc->is_rescheduled,
                    'original_scheduled_at' => $lc->original_scheduled_at ? $lc->original_scheduled_at->format('d M Y, h:i A') : null,
                    'reschedule_reason'     => $lc->reschedule_reason,
                    'color'                 => '#2563eb',
                    'can_edit'              => true,
                ];
            }
        }

        return [
            'year'   => $year,
            'start'  => $startDate->toDateString(),
            'end'    => $endDate->toDateString(),
            'events' => $events,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TEACHER DIRECT RESCHEDULE & QUICK CREATE
    // ─────────────────────────────────────────────────────────────────────────

    public function rescheduleByTeacher(User $user, int $liveClassId, array $validated): array
    {
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
        $courseIds = $teacher->courses->pluck('id')->toArray();

        $liveClass = LiveClass::where('id', $liveClassId)
            ->where(fn($q) => $q->whereIn('course_id', $courseIds)->orWhere('teacher_id', $teacher->id))
            ->with(['course.subject', 'course.enrollments.student.user'])
            ->firstOrFail();

        $newDateTime = Carbon::parse($validated['scheduled_date'] . ' ' . $validated['scheduled_time']);
        $oldDateTime = $liveClass->scheduled_at ? $liveClass->scheduled_at->copy() : null;

        $liveClass->update([
            'is_rescheduled'        => true,
            'original_scheduled_at' => $liveClass->original_scheduled_at ?? $oldDateTime,
            'scheduled_at'          => $newDateTime,
            'duration_minutes'      => $validated['duration_minutes'] ?? $liveClass->duration_minutes,
            'meeting_url'           => !empty($validated['meeting_url']) ? $validated['meeting_url'] : $liveClass->meeting_url,
            'reschedule_reason'     => $validated['reason'],
            'rescheduled_by'        => $user->id,
        ]);

        ClassRequest::create([
            'live_class_id' => $liveClass->id,
            'teacher_id'    => $teacher->id,
            'type'          => 'reschedule',
            'reason'        => $validated['reason'],
            'requested_at'  => $newDateTime,
            'status'        => 'approved',
            'admin_note'    => 'Rescheduled directly by instructor ' . $user->name,
        ]);

        $this->notifyStudentsAndParentsOfReschedule($liveClass, $oldDateTime, $newDateTime, $validated['reason'], $user->name);

        return [
            'liveClass'   => $liveClass,
            'newDateTime' => $newDateTime,
        ];
    }

    public function quickCreateByTeacher(User $user, array $validated): LiveClass
    {
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
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

        $this->notifyStudentsAndParentsOfNewClass($liveClass);

        return $liveClass;
    }

    public function teacherCourseIds(User $user): array
    {
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
        return $teacher->courses->pluck('id')->toArray();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ADMIN RESCHEDULE & QUICK CREATE
    // ─────────────────────────────────────────────────────────────────────────

    public function rescheduleByAdmin(int $liveClassId, array $validated, int $actorId): array
    {
        $liveClass = LiveClass::with(['course.subject', 'course.teacher.user', 'teacher.user', 'course.enrollments.student.user'])
            ->findOrFail($liveClassId);

        $newDateTime = Carbon::parse($validated['scheduled_date'] . ' ' . $validated['scheduled_time']);
        $oldDateTime = $liveClass->scheduled_at ? $liveClass->scheduled_at->copy() : null;

        $liveClass->update([
            'is_rescheduled'        => true,
            'original_scheduled_at' => $liveClass->original_scheduled_at ?? $oldDateTime,
            'scheduled_at'          => $newDateTime,
            'duration_minutes'      => $validated['duration_minutes'] ?? $liveClass->duration_minutes,
            'reschedule_reason'     => $validated['reason'],
            'meeting_url'           => $validated['meeting_url'] ?? $liveClass->meeting_url,
            'rescheduled_by'        => $actorId,
        ]);

        $teacherUser = $liveClass->teacher?->user ?? $liveClass->course?->teacher?->user;
        $teacherName = $teacherUser?->name ?? 'School Administration';

        $this->notifyStudentsAndParentsOfReschedule($liveClass, $oldDateTime, $newDateTime, $validated['reason'], $teacherName);

        if ($teacherUser) {
            UserNotification::create([
                'user_id' => $teacherUser->id,
                'type'    => 'class_reminder',
                'title'   => 'Class Session Rescheduled by Administrator',
                'message' => "Session '{$liveClass->title}' has been moved to {$newDateTime->format('d M Y, h:i A')} by administration. Reason: \"{$validated['reason']}\".",
            ]);
        }

        return [
            'liveClass'   => $liveClass,
            'newDateTime' => $newDateTime,
        ];
    }

    public function quickCreateByAdmin(array $validated): LiveClass
    {
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

        $teacher = Teacher::with('user')->find($teacherId);
        if ($teacher?->user) {
            UserNotification::create([
                'user_id' => $teacher->user->id,
                'type'    => 'class_reminder',
                'title'   => 'New Class Session Scheduled by Administration',
                'message' => "A new live class session '{$liveClass->title}' has been scheduled for {$scheduledAt->format('d M Y, h:i A')}.",
            ]);
        }

        $this->notifyStudentsAndParentsOfNewClass($liveClass);

        return $liveClass;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ACADEMIC EVENTS CRUD
    // ─────────────────────────────────────────────────────────────────────────

    public function storeAcademicEvent(array $validated, int $createdBy, bool $isAllDay): AcademicEvent
    {
        $validated['created_by'] = $createdBy;
        $validated['is_all_day'] = $isAllDay;

        return AcademicEvent::create($validated);
    }

    public function updateAcademicEvent(int $id, array $validated, bool $isAllDay): AcademicEvent
    {
        $event = AcademicEvent::findOrFail($id);
        $validated['is_all_day'] = $isAllDay;
        $event->update($validated);

        return $event;
    }

    public function destroyAcademicEvent(int $id): void
    {
        AcademicEvent::findOrFail($id)->delete();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private function appendStudentClassEvents(Student $student, Carbon $start, Carbon $end, array &$events, ?string $childLabel = null, ?string $overrideColor = null, ?array $childTheme = null): void
    {
        $classes = ClassScheduleService::getStudentLiveClassesQuery($student)
            ->whereBetween('scheduled_at', [$start, $end])
            ->with(['course.subject', 'course.teacher.user'])
            ->get();

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
                'status'                => $lc->status->value,
                'is_rescheduled'        => (bool) $lc->is_rescheduled,
                'original_scheduled_at' => $lc->original_scheduled_at ? $lc->original_scheduled_at->format('d M Y, h:i A') : null,
                'reschedule_reason'     => $lc->reschedule_reason,
                'attendance_status'     => $attStatus,
                'child_name'            => $childLabel,
                'child_id'              => $student->id,
                'child_theme'           => $childTheme,
                'color'                 => $overrideColor ?: '#2563eb',
                'can_edit'              => false,
            ];
        }
    }

    private function appendTeacherClassEvents(Teacher $teacher, Carbon $start, Carbon $end, array &$events): void
    {
        $courseIds = $teacher->courses->pluck('id');

        $classes = LiveClass::where(fn($q) => $q->whereIn('course_id', $courseIds)->orWhere('teacher_id', $teacher->id))
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
                'status'                => $lc->status->value,
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
                'color'        => $overrideColor ?: '#f59e0b',
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

            if ($student->user) {
                UserNotification::create([
                    'user_id' => $student->user->id,
                    'type'    => 'class_reminder',
                    'title'   => 'Class Rescheduled: ' . $liveClass->title,
                    'message' => "Teacher {$teacherName} has rescheduled {$liveClass->title} from {$oldTimeStr} to {$newTimeStr}. Reason: \"{$reason}\".",
                ]);
            }

            foreach ($this->activeParentIdsOf($student) as $parentId) {
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

            if ($student->user) {
                UserNotification::create([
                    'user_id' => $student->user->id,
                    'type'    => 'class_reminder',
                    'title'   => "New Class Scheduled: {$liveClass->title}",
                    'message' => "A new {$subjectName} live class '{$liveClass->title}' with {$teacherName} has been scheduled for {$timeStr}.",
                ]);
            }

            foreach ($this->activeParentIdsOf($student) as $parentId) {
                UserNotification::create([
                    'user_id' => $parentId,
                    'type'    => 'class_reminder',
                    'title'   => "New Class Scheduled for {$student->user->name}",
                    'message' => "A new live class session '{$liveClass->title}' ({$subjectName}) has been scheduled for {$student->user->name} on {$timeStr}.",
                ]);
            }
        }
    }

    private function activeParentIdsOf(Student $student): array
    {
        $parentUserIds = [];
        if ($student->parent_user_id) {
            $parentUserIds[] = $student->parent_user_id;
        }

        $pivotParents = DB::table('parent_student')
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->pluck('parent_user_id')
            ->toArray();

        return array_unique(array_merge($parentUserIds, $pivotParents));
    }
}
