<?php

namespace App\Services\Student;

use App\Models\LiveClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Services\AttendanceService;
use App\Services\StudentAccessRestrictionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClassScheduleService
{
    /**
     * Query builder for all live classes a student is eligible to see:
     * direct course enrollment, plus a dual-layer match on courses that
     * share the same subject as one the student is enrolled in.
     *
     * Used wherever a single source of truth for "this student's classes"
     * is needed outside this service's own view-model builders (e.g.
     * AttendanceService's stats and join-tracking).
     */
    public static function getStudentLiveClassesQuery(Student $student)
    {
        return LiveClass::where(function ($q) use ($student) {
            // 1. Direct course enrollment
            $q->whereHas('course.enrollments', function ($eq) use ($student) {
                $eq->where('student_id', $student->id);
            });

            // 2. Dual-layer: match courses with the same subject
            $enrolledSubjectIds = DB::table('enrollments')
                ->join('courses', 'courses.id', '=', 'enrollments.course_id')
                ->where('enrollments.student_id', $student->id)
                ->pluck('courses.subject_id')
                ->filter()
                ->unique()
                ->toArray();

            if (!empty($enrolledSubjectIds)) {
                $subjectNames = DB::table('subjects')->whereIn('id', $enrolledSubjectIds)->pluck('name')->toArray();
                $allMatchingSubjectIds = DB::table('subjects')->whereIn('name', $subjectNames)->pluck('id')->toArray();

                $q->orWhereHas('course', function ($cq) use ($allMatchingSubjectIds) {
                    $cq->whereIn('subject_id', $allMatchingSubjectIds);
                });
            }
        });
    }

    /**
     * Builds the full view-model for the student's Classes/Timetable page.
     */
    public function classesData(Student $student): array
    {
        $liveClasses = LiveClass::whereHas('course', function ($q) use ($student) {
                $q->whereHas('enrollments', fn ($q2) => $q2->where('student_id', $student->id));
            })
            ->with('course.subject', 'course.teacher.user')
            ->orderBy('scheduled_at')
            ->get();

        $now = now();
        $weekDays = collect();
        for ($i = 0; $i < 7; $i++) {
            $day = $now->copy()->addDays($i);
            $weekDays->push([
                'date'    => $day,
                'label'   => $day->format('D'),
                'full'    => $day->format('l'),
                'dateStr' => $day->format('d M'),
                'isToday' => $day->isToday(),
                'classes' => $liveClasses->filter(fn ($lc) =>
                    $lc->scheduled_at->format('Y-m-d') === $day->format('Y-m-d')
                )->values(),
            ]);
        }

        $upcomingClasses = $liveClasses->filter(fn ($lc) => $lc->scheduled_at->gte($now))->values();
        $pastClasses     = $liveClasses->filter(fn ($lc) => $lc->scheduled_at->lt($now))->sortByDesc('scheduled_at')->values();

        // Student's recorded attendances for live classes
        $attendanceMap = StudentAttendance::where('student_id', $student->id)
            ->pluck('status', 'live_class_id');

        // Dynamic Month Attendance Analytics from unified helper
        $attendanceStats = $this->attendanceStats($student);

        return [
            'weekDays'         => $weekDays,
            'upcomingClasses'  => $upcomingClasses,
            'pastClasses'      => $pastClasses,
            'liveClasses'      => $liveClasses,
            'attendanceStats'  => $attendanceStats,
            'attendanceMap'    => $attendanceMap,
        ];
    }

    /**
     * Shared attendance-rate calculation used by the dashboard, classes, and
     * progress pages — kept as one method so all three stay consistent.
     */
    public function attendanceStats(Student $student): array
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth   = now()->endOfMonth();

        // All live classes this month for student's enrolled courses
        $monthLiveClasses = LiveClass::whereHas('course', function ($q) use ($student) {
            $q->whereHas('enrollments', fn ($q2) => $q2->where('student_id', $student->id));
        })
        ->whereBetween('scheduled_at', [$startOfMonth, $endOfMonth])
        ->get();

        $totalClasses = $monthLiveClasses->count();

        // Past classes this month
        $pastClassIds = $monthLiveClasses->where('scheduled_at', '<', now())->pluck('id');

        $attendanceRecords = StudentAttendance::where('student_id', $student->id)
            ->whereIn('live_class_id', $pastClassIds)
            ->get();

        $attended = $attendanceRecords->whereIn('status', ['attended', 'late'])->count();
        $absent   = $attendanceRecords->where('status', 'absent')->count();
        $late     = $attendanceRecords->where('status', 'late')->count();

        $totalPast = $pastClassIds->count();
        $rate = $totalPast > 0 ? (int) round(($attended / $totalPast) * 100) : 0;

        return [
            'total'    => $totalClasses,
            'attended' => $attended,
            'absent'   => $absent,
            'late'     => $late,
            'pct'      => $rate,
        ];
    }

    /**
     * Checks billing restriction + enrollment, then delegates the actual
     * join-attempt recording to AttendanceService. Returns a plain result
     * array (success/error/message/...) — the controller is the only place
     * that turns this into an HTTP response (redirect vs JSON, status code).
     */
    public function authorizeAndJoin(
        Student $student,
        LiveClass $liveClass,
        ?Request $request,
        StudentAccessRestrictionService $restrictionService,
        AttendanceService $attendanceService
    ): array {
        if ($restrictionService->isStudentRestricted($student)) {
            return [
                'success' => false,
                'error'   => 'payment_restricted',
                'message' => 'Your academic access is temporarily on hold due to an overdue payment balance. Please settle your invoice to access live classes.',
                'details' => $restrictionService->getRestrictionDetails($student),
            ];
        }

        $isEnrolled = self::getStudentLiveClassesQuery($student)
            ->where('live_classes.id', $liveClass->id)
            ->exists();

        if (!$isEnrolled) {
            return [
                'success' => false,
                'error'   => 'not_enrolled',
                'message' => 'You are not enrolled in this live class.',
            ];
        }

        $result = $attendanceService->recordStudentJoin($student, $liveClass, $request);

        if (!$result['success']) {
            return $result;
        }

        $meetingUrl = $result['meeting_url'] ?: $liveClass->meeting_url;

        if (empty($meetingUrl)) {
            return [
                'success' => false,
                'error'   => 'missing_url',
                'message' => 'The instructor has not provided a live meeting link for this session yet.',
            ];
        }

        return [
            'success'      => true,
            'redirect_url' => $meetingUrl,
            'is_rejoin'    => $result['is_rejoin'] ?? false,
        ];
    }

    /**
     * Validates enrollment, then delegates to AttendanceService to create
     * the correction request record.
     */
    public function submitCorrectionRequest(
        Student $student,
        LiveClass $liveClass,
        string $reason,
        string $requestedStatus,
        AttendanceService $attendanceService
    ): array {
        $isEnrolled = self::getStudentLiveClassesQuery($student)
            ->where('live_classes.id', $liveClass->id)
            ->exists();

        if (!$isEnrolled) {
            return ['success' => false, 'message' => 'You are not enrolled in this class.'];
        }

        $req = $attendanceService->submitCorrectionRequest($student, $liveClass, $reason, $requestedStatus);

        return [
            'success' => true,
            'message' => 'Attendance correction request submitted for teacher review.',
            'request' => $req,
        ];
    }
}
