<?php

namespace App\Services;

use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceEvent;
use App\Models\LiveClass;
use App\Models\Setting;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Teacher;
use App\Models\User;
use App\Models\UserNotification;
use App\Http\Controllers\StudentDashboardController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * Record a student's initiated join attempt for a live class.
     * Idempotent, transaction-safe, and preserves teacher-verified records.
     */
    public function recordStudentJoin(Student $student, LiveClass $liveClass, ?Request $request = null): array
    {
        // 1. Check class cancellation
        if ($liveClass->status === 'cancelled') {
            return [
                'success' => false,
                'error'   => 'cancelled',
                'message' => 'This class session has been cancelled.',
            ];
        }

        // 2. Schedule window checks
        $scheduledAt  = $liveClass->scheduled_at ? Carbon::parse($liveClass->scheduled_at) : now();
        $durationMins = (int) ($liveClass->duration_minutes ?: 60);
        $classEndTime = $scheduledAt->copy()->addMinutes($durationMins);
        $earlyWindow  = (int) Setting::get('attendance.early_join_window_minutes', 15);
        $now          = now();

        if ($now->lt($scheduledAt->copy()->subMinutes($earlyWindow))) {
            return [
                'success' => false,
                'error'   => 'early',
                'message' => "This class is not open yet. You can join {$earlyWindow} minutes before the scheduled start time.",
            ];
        }

        // 3. Process attendance record idempotently in a database transaction
        return DB::transaction(function () use ($student, $liveClass, $scheduledAt, $classEndTime, $now, $request) {
            $attendance = StudentAttendance::where('student_id', $student->id)
                ->where('live_class_id', $liveClass->id)
                ->lockForUpdate()
                ->first();

            $lateThresholdMins = (int) Setting::get('attendance.late_threshold_minutes', 15);
            $lateCutoff        = $scheduledAt->copy()->addMinutes($lateThresholdMins);

            if ($attendance) {
                // If already verified by a teacher, NEVER overwrite the verified status
                if ($attendance->isVerified()) {
                    $this->logEvent(
                        studentId: $student->id,
                        liveClassId: $liveClass->id,
                        attendanceId: $attendance->id,
                        actorId: auth()->id(),
                        actorRole: 'student',
                        eventType: AttendanceEvent::TYPE_REJOIN_ATTEMPT,
                        metadata: [
                            'ip'               => $request?->ip(),
                            'verified_status'  => $attendance->status,
                            'verified_at'      => $attendance->verified_at?->toIso8601String(),
                            'verified_by'      => $attendance->verified_by,
                            'rejoin_timestamp' => $now->toIso8601String(),
                        ]
                    );

                    return [
                        'success'     => true,
                        'attendance'  => $attendance,
                        'is_rejoin'   => true,
                        'meeting_url' => $liveClass->meeting_url,
                    ];
                }

                // If not verified yet, preserve first joined_at and log rejoin
                if (!$attendance->joined_at) {
                    $attendance->joined_at = $now;
                }

                // If status was pending_verification, resolve to attended or late
                if ($attendance->status === StudentAttendance::STATUS_PENDING) {
                    $attendance->status = $now->lte($lateCutoff)
                        ? StudentAttendance::STATUS_ATTENDED
                        : StudentAttendance::STATUS_LATE;
                    $attendance->save();
                }

                $this->logEvent(
                    studentId: $student->id,
                    liveClassId: $liveClass->id,
                    attendanceId: $attendance->id,
                    actorId: auth()->id(),
                    actorRole: 'student',
                    eventType: AttendanceEvent::TYPE_REJOIN_ATTEMPT,
                    metadata: [
                        'ip'               => $request?->ip(),
                        'current_status'   => $attendance->status,
                        'first_joined_at'  => $attendance->joined_at?->toIso8601String(),
                        'rejoin_timestamp' => $now->toIso8601String(),
                    ]
                );

                return [
                    'success'     => true,
                    'attendance'  => $attendance,
                    'is_rejoin'   => true,
                    'meeting_url' => $liveClass->meeting_url,
                ];
            }

            // Fresh join attempt: determine initial automated status
            $initialStatus = $now->lte($lateCutoff)
                ? StudentAttendance::STATUS_ATTENDED
                : StudentAttendance::STATUS_LATE;

            $attendance = StudentAttendance::create([
                'student_id'    => $student->id,
                'live_class_id' => $liveClass->id,
                'status'        => $initialStatus,
                'joined_at'     => $now,
            ]);

            $this->logEvent(
                studentId: $student->id,
                liveClassId: $liveClass->id,
                attendanceId: $attendance->id,
                actorId: auth()->id(),
                actorRole: 'student',
                eventType: AttendanceEvent::TYPE_JOIN_ATTEMPT,
                metadata: [
                    'ip'                => $request?->ip(),
                    'user_agent'        => substr($request?->userAgent() ?? '', 0, 255),
                    'calculated_status' => $initialStatus,
                    'scheduled_at'      => $scheduledAt->toIso8601String(),
                    'joined_at'         => $now->toIso8601String(),
                ]
            );

            return [
                'success'     => true,
                'attendance'  => $attendance,
                'is_rejoin'   => false,
                'meeting_url' => $liveClass->meeting_url,
            ];
        });
    }

    /**
     * Compute attendance statistics for a student over a given period (default: current month).
     * Single source of truth across Student, Teacher, Parent and Admin modules.
     *
     * Rules:
     * 1. Excludes future classes.
     * 2. Excludes cancelled classes.
     * 3. Unmarked past classes remain 'pending_verification' (NOT automatically marked absent).
     * 4. Excludes 'pending_verification' from finalized attendance rate denominator.
     * 5. Honors admin setting for excused absence denominator inclusion.
     */
    public function getStudentAttendanceStats(Student $student, ?Carbon $start = null, ?Carbon $end = null): array
    {
        $start = $start ?? now()->startOfMonth();
        $end   = $end ?? now()->endOfMonth();

        // 1. Fetch eligible live classes
        $eligibleClasses = StudentDashboardController::getStudentLiveClassesQuery($student)
            ->whereBetween('scheduled_at', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->orderBy('scheduled_at', 'asc')
            ->get();

        $totalClasses   = $eligibleClasses->count();
        $pastClasses    = $eligibleClasses->where('scheduled_at', '<', now());
        $pastClassIds   = $pastClasses->pluck('id');
        $totalPast      = $pastClassIds->count();
        $totalUpcoming  = $totalClasses - $totalPast;

        // 2. Fetch existing attendance records
        $attendanceRecords = StudentAttendance::where('student_id', $student->id)
            ->whereIn('live_class_id', $pastClassIds)
            ->get()
            ->keyBy('live_class_id');

        $attendedCount = 0;
        $lateCount     = 0;
        $absentCount   = 0;
        $excusedCount  = 0;
        $pendingCount  = 0;

        foreach ($pastClassIds as $classId) {
            $rec = $attendanceRecords->get($classId);

            if (!$rec) {
                // Past class with no attendance record is Pending Verification (not auto-absent)
                $pendingCount++;
                continue;
            }

            switch ($rec->status) {
                case StudentAttendance::STATUS_ATTENDED:
                    $attendedCount++;
                    break;
                case StudentAttendance::STATUS_LATE:
                    $lateCount++;
                    break;
                case StudentAttendance::STATUS_ABSENT:
                    $absentCount++;
                    break;
                case StudentAttendance::STATUS_EXCUSED:
                    $excusedCount++;
                    break;
                default:
                    $pendingCount++;
                    break;
            }
        }

        $presentTotal = $attendedCount + $lateCount;

        // 3. Counted eligible classes for denominator
        // Standard formula: Present + Late + Finalized Absent (+ Excused if configured)
        $includeExcusedInDenominator = (bool) Setting::get('attendance.excused_in_denominator', 0);
        $countedEligible = $presentTotal + $absentCount + ($includeExcusedInDenominator ? $excusedCount : 0);

        $rate = $countedEligible > 0
            ? min(100, (int) round(($presentTotal / $countedEligible) * 100))
            : ($totalPast > 0 && $pendingCount === $totalPast ? 0 : 100);

        // Chart percentages
        $chartDenominator = max(1, $totalPast);
        $attendedPct = (int) round(($attendedCount / $chartDenominator) * 100);
        $latePct     = (int) round(($lateCount / $chartDenominator) * 100);
        $absentPct   = (int) round(($absentCount / $chartDenominator) * 100);
        $pendingPct  = (int) round(($pendingCount / $chartDenominator) * 100);

        return [
            'total'         => $totalClasses,
            'past'          => $totalPast,
            'upcoming'      => $totalUpcoming,
            'attended'      => $attendedCount,
            'late'          => $lateCount,
            'present_total' => $presentTotal,
            'absent'        => $absentCount,
            'excused'       => $excusedCount,
            'pending'       => $pendingCount,
            'counted'       => $countedEligible,
            'pct'           => $rate,
            'attended_pct'  => $attendedPct,
            'late_pct'      => $latePct,
            'absent_pct'    => $absentPct,
            'pending_pct'   => $pendingPct,
        ];
    }

    /**
     * Teacher save / verify attendance in Class Workspace.
     * Records changes in attendance_events and marks verified_at / verified_by.
     */
    public function saveTeacherAttendance(Teacher $teacher, LiveClass $liveClass, array $records, ?User $actor = null): array
    {
        $actor = $actor ?? auth()->user();

        return DB::transaction(function () use ($teacher, $liveClass, $records, $actor) {
            $updated = 0;
            $now     = now();

            foreach ($records as $item) {
                $studentId = $item['student_id'] ?? null;
                $status    = $item['status'] ?? null;
                $remarks   = $item['remarks'] ?? null;

                if (!$studentId || !$status) {
                    continue;
                }

                // Normalize status
                $validStatuses = [
                    StudentAttendance::STATUS_ATTENDED,
                    StudentAttendance::STATUS_LATE,
                    StudentAttendance::STATUS_ABSENT,
                    StudentAttendance::STATUS_EXCUSED,
                    StudentAttendance::STATUS_PENDING,
                ];

                // Accept title case UI inputs e.g. 'Present' -> 'attended'
                $normalized = strtolower($status);
                if ($normalized === 'present') {
                    $normalized = StudentAttendance::STATUS_ATTENDED;
                }

                if (!in_array($normalized, $validStatuses, true)) {
                    continue;
                }

                $attendance = StudentAttendance::where('student_id', $studentId)
                    ->where('live_class_id', $liveClass->id)
                    ->lockForUpdate()
                    ->first();

                $oldStatus = $attendance?->status;

                if ($attendance) {
                    $attendance->status      = $normalized;
                    $attendance->verified_at = $now;
                    $attendance->verified_by = $actor->id;
                    if ($remarks !== null) {
                        $attendance->remarks = $remarks;
                    }
                    $attendance->save();
                } else {
                    $attendance = StudentAttendance::create([
                        'student_id'    => $studentId,
                        'live_class_id' => $liveClass->id,
                        'status'        => $normalized,
                        'verified_at'   => $now,
                        'verified_by'   => $actor->id,
                        'remarks'       => $remarks,
                    ]);
                }

                $eventType = match ($normalized) {
                    StudentAttendance::STATUS_ATTENDED => AttendanceEvent::TYPE_TEACHER_MARKED_PRESENT,
                    StudentAttendance::STATUS_LATE     => AttendanceEvent::TYPE_TEACHER_MARKED_LATE,
                    StudentAttendance::STATUS_ABSENT   => AttendanceEvent::TYPE_TEACHER_MARKED_ABSENT,
                    StudentAttendance::STATUS_EXCUSED  => AttendanceEvent::TYPE_TEACHER_MARKED_EXCUSED,
                    default                            => AttendanceEvent::TYPE_ATTENDANCE_VERIFIED,
                };

                $this->logEvent(
                    studentId: $studentId,
                    liveClassId: $liveClass->id,
                    attendanceId: $attendance->id,
                    actorId: $actor->id,
                    actorRole: $actor->role ?? 'teacher',
                    eventType: $eventType,
                    metadata: [
                        'old_status' => $oldStatus,
                        'new_status' => $normalized,
                        'remarks'    => $remarks,
                    ]
                );

                // Send notification to student if marked absent
                if ($normalized === StudentAttendance::STATUS_ABSENT && $oldStatus !== StudentAttendance::STATUS_ABSENT) {
                    $student = Student::find($studentId);
                    if ($student && $student->user_id) {
                        UserNotification::create([
                            'user_id' => $student->user_id,
                            'type'    => 'attendance_absent',
                            'title'   => 'Class Attendance Notice',
                            'message' => "You were marked Absent for {$liveClass->title} on {$liveClass->scheduled_at->format('d M Y')}. If this is an error, you can submit a correction request.",
                        ]);
                    }
                }

                $updated++;
            }

            return [
                'success' => true,
                'count'   => $updated,
                'message' => "Successfully updated attendance for {$updated} students.",
            ];
        });
    }

    /**
     * Submit an attendance correction request by a student.
     */
    public function submitCorrectionRequest(Student $student, LiveClass $liveClass, string $reason, string $requestedStatus = 'attended'): AttendanceCorrectionRequest
    {
        return DB::transaction(function () use ($student, $liveClass, $reason, $requestedStatus) {
            $attendance = StudentAttendance::where('student_id', $student->id)
                ->where('live_class_id', $liveClass->id)
                ->first();

            $req = AttendanceCorrectionRequest::create([
                'student_id'            => $student->id,
                'live_class_id'         => $liveClass->id,
                'student_attendance_id' => $attendance?->id,
                'requested_status'      => $requestedStatus,
                'reason'                => $reason,
                'status'                => AttendanceCorrectionRequest::STATUS_PENDING,
            ]);

            $this->logEvent(
                studentId: $student->id,
                liveClassId: $liveClass->id,
                attendanceId: $attendance?->id,
                actorId: auth()->id(),
                actorRole: 'student',
                eventType: AttendanceEvent::TYPE_CORRECTION_REQUESTED,
                metadata: [
                    'correction_request_id' => $req->id,
                    'reason'                => $reason,
                    'requested_status'      => $requestedStatus,
                ]
            );

            // Notify assigned teacher
            $teacherUser = $liveClass->assigned_teacher?->user;
            if ($teacherUser) {
                UserNotification::create([
                    'user_id' => $teacherUser->id,
                    'type'    => 'attendance_correction_request',
                    'title'   => 'Attendance Correction Request',
                    'message' => "{$student->user->name} submitted an attendance correction request for {$liveClass->title}.",
                ]);
            }

            return $req;
        });
    }

    /**
     * Review an attendance correction request (approve or reject).
     */
    public function reviewCorrectionRequest(AttendanceCorrectionRequest $correctionReq, User $reviewer, string $action, ?string $remarks = null): bool
    {
        return DB::transaction(function () use ($correctionReq, $reviewer, $action, $remarks) {
            $isApproved = ($action === 'approved');

            $correctionReq->update([
                'status'           => $isApproved ? AttendanceCorrectionRequest::STATUS_APPROVED : AttendanceCorrectionRequest::STATUS_REJECTED,
                'reviewer_id'      => $reviewer->id,
                'reviewer_remarks' => $remarks,
                'reviewed_at'      => now(),
            ]);

            if ($isApproved) {
                $attendance = StudentAttendance::updateOrCreate(
                    [
                        'student_id'    => $correctionReq->student_id,
                        'live_class_id' => $correctionReq->live_class_id,
                    ],
                    [
                        'status'      => $correctionReq->requested_status,
                        'verified_at' => now(),
                        'verified_by' => $reviewer->id,
                        'remarks'     => 'Correction approved: ' . ($remarks ?? 'Verified attendance'),
                    ]
                );

                $correctionReq->update(['student_attendance_id' => $attendance->id]);

                $this->logEvent(
                    studentId: $correctionReq->student_id,
                    liveClassId: $correctionReq->live_class_id,
                    attendanceId: $attendance->id,
                    actorId: $reviewer->id,
                    actorRole: $reviewer->role,
                    eventType: AttendanceEvent::TYPE_CORRECTION_APPROVED,
                    metadata: [
                        'correction_request_id' => $correctionReq->id,
                        'new_status'            => $correctionReq->requested_status,
                        'reviewer_remarks'      => $remarks,
                    ]
                );

                // Notify student
                if ($correctionReq->student?->user_id) {
                    UserNotification::create([
                        'user_id' => $correctionReq->student->user_id,
                        'type'    => 'attendance_correction_approved',
                        'title'   => 'Attendance Correction Approved',
                        'message' => "Your attendance correction request for {$correctionReq->liveClass->title} was approved.",
                    ]);
                }
            } else {
                $this->logEvent(
                    studentId: $correctionReq->student_id,
                    liveClassId: $correctionReq->live_class_id,
                    attendanceId: $correctionReq->student_attendance_id,
                    actorId: $reviewer->id,
                    actorRole: $reviewer->role,
                    eventType: AttendanceEvent::TYPE_CORRECTION_REJECTED,
                    metadata: [
                        'correction_request_id' => $correctionReq->id,
                        'reviewer_remarks'      => $remarks,
                    ]
                );

                // Notify student
                if ($correctionReq->student?->user_id) {
                    UserNotification::create([
                        'user_id' => $correctionReq->student->user_id,
                        'type'    => 'attendance_correction_rejected',
                        'title'   => 'Attendance Correction Notice',
                        'message' => "Your attendance correction request for {$correctionReq->liveClass->title} was reviewed: {$remarks}",
                    ]);
                }
            }

            return true;
        });
    }

    /**
     * Append-only event logger for attendance audit trail.
     */
    public function logEvent(
        int $studentId,
        int $liveClassId,
        ?int $attendanceId,
        ?int $actorId,
        string $actorRole,
        string $eventType,
        array $metadata = []
    ): AttendanceEvent {
        return AttendanceEvent::create([
            'student_attendance_id' => $attendanceId,
            'student_id'            => $studentId,
            'live_class_id'         => $liveClassId,
            'actor_id'              => $actorId,
            'actor_role'            => $actorRole,
            'event_type'            => $eventType,
            'event_timestamp'       => now(),
            'metadata'              => $metadata,
        ]);
    }
}
