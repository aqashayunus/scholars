<?php

namespace Tests\Feature;

use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceEvent;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\LiveClass;
use App\Models\Setting;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    private User $teacherUser;
    private Teacher $teacher;
    private User $studentUser;
    private Student $student;
    private Subject $subject;
    private Course $course;
    private LiveClass $liveClass;

    protected function setUp(): void
    {
        parent::setUp();

        // Configure attendance settings for deterministic tests
        Setting::set('attendance.late_threshold_minutes', 15);
        Setting::set('attendance.early_join_window_minutes', 15);
        Setting::set('attendance.excused_in_denominator', 0);

        // 1. Teacher
        $this->teacherUser = User::create([
            'name'     => 'Cikgu Rahman',
            'email'    => 'rahman@teacher.test',
            'role'     => 'teacher',
            'password' => Hash::make('password'),
        ]);

        $this->teacher = Teacher::create([
            'user_id'            => $this->teacherUser->id,
            'application_status' => 'active',
            'qualification'      => 'Master of Mathematics',
        ]);

        // 2. Student
        $this->studentUser = User::create([
            'name'     => 'Nurul Huda',
            'email'    => 'huda@student.test',
            'role'     => 'student',
            'password' => Hash::make('password'),
        ]);

        $this->student = Student::create([
            'user_id' => $this->studentUser->id,
            'level'   => 'SPM',
            'status'  => 'active',
        ]);

        // 3. Subject & Course
        $this->subject = Subject::create([
            'name'  => 'Matematik Tambahan',
            'slug'  => 'matematik-tambahan',
            'level' => 'SPM',
        ]);

        $this->course = Course::create([
            'title'       => 'SPM Add Math Masterclass',
            'subject_id'  => $this->subject->id,
            'teacher_id'  => $this->teacher->id,
            'level'       => 'SPM',
            'status'      => 'published',
            'price'       => 99.00,
        ]);

        // 4. Enrollment
        Enrollment::create([
            'student_id'   => $this->student->id,
            'course_id'    => $this->course->id,
            'enrolled_at'  => now()->subDays(5),
            'progress_pct' => 50,
            'status'       => 'active',
        ]);

        // 5. Live Class (Scheduled for now)
        $this->liveClass = LiveClass::create([
            'course_id'        => $this->course->id,
            'teacher_id'       => $this->teacher->id,
            'title'            => 'Calculus & Integration Session 1',
            'scheduled_at'     => now()->addMinutes(5),
            'duration_minutes' => 60,
            'meeting_url'      => 'https://meet.google.com/abc-defg-hij',
        ]);
    }

    /**
     * Test 1: Student clicking Join records a join attempt with timestamp and status,
     * but remains unverified by teacher until reviewed (Improvements #1 & #2).
     */
    public function test_student_join_records_attempt_and_audit_event_without_automatic_teacher_verification(): void
    {
        $response = $this->actingAs($this->studentUser)
            ->post(route('student.classes.join', $this->liveClass->id));

        $response->assertRedirect($this->liveClass->meeting_url);

        // Check attendance record exists
        $attendance = StudentAttendance::where('student_id', $this->student->id)
            ->where('live_class_id', $this->liveClass->id)
            ->first();

        $this->assertNotNull($attendance);
        $this->assertNotNull($attendance->joined_at);
        $this->assertEquals(StudentAttendance::STATUS_ATTENDED, $attendance->status);
        // Improvement #1: not teacher-verified yet
        $this->assertNull($attendance->verified_at);
        $this->assertNull($attendance->verified_by);

        // Improvement #5: check audit event
        $this->assertDatabaseHas('attendance_events', [
            'student_id'    => $this->student->id,
            'live_class_id' => $this->liveClass->id,
            'event_type'    => AttendanceEvent::TYPE_JOIN_ATTEMPT,
        ]);
    }

    /**
     * Test 2: Idempotent Join — Repeated join calls preserve the initial joined_at timestamp
     * and do not create duplicate rows (Improvement #4).
     */
    public function test_rejoining_is_idempotent_and_preserves_first_timestamp(): void
    {
        $firstJoinTime = now()->subMinutes(10);
        Carbon::setTestNow($firstJoinTime);

        // First join
        $this->actingAs($this->studentUser)
            ->post(route('student.classes.join', $this->liveClass->id));

        $record = StudentAttendance::where('student_id', $this->student->id)
            ->where('live_class_id', $this->liveClass->id)
            ->first();

        $originalJoinedAt = $record->joined_at;

        // Second join 5 minutes later
        Carbon::setTestNow($firstJoinTime->copy()->addMinutes(5));
        $this->actingAs($this->studentUser)
            ->post(route('student.classes.join', $this->liveClass->id));

        // Assert only 1 record exists in DB
        $count = StudentAttendance::where('student_id', $this->student->id)
            ->where('live_class_id', $this->liveClass->id)
            ->count();
        $this->assertEquals(1, $count);

        $reloaded = $record->fresh();
        $this->assertEquals($originalJoinedAt->toDateTimeString(), $reloaded->joined_at->toDateTimeString());

        // Check rejoin event was logged in audit trail
        $this->assertDatabaseHas('attendance_events', [
            'student_id'    => $this->student->id,
            'live_class_id' => $this->liveClass->id,
            'event_type'    => AttendanceEvent::TYPE_REJOIN_ATTEMPT,
        ]);

        Carbon::setTestNow();
    }

    /**
     * Test 3: Joining after late threshold sets status to 'late' (Improvements #2 & Policy).
     */
    public function test_joining_after_threshold_marks_status_late(): void
    {
        // Scheduled at 10:00 AM, student joins at 10:20 AM (> 15 min threshold)
        $scheduledTime = Carbon::parse('2026-10-01 10:00:00');
        $this->liveClass->update(['scheduled_at' => $scheduledTime]);

        Carbon::setTestNow(Carbon::parse('2026-10-01 10:20:00'));

        $this->actingAs($this->studentUser)
            ->post(route('student.classes.join', $this->liveClass->id));

        $attendance = StudentAttendance::where('student_id', $this->student->id)
            ->where('live_class_id', $this->liveClass->id)
            ->first();

        $this->assertEquals(StudentAttendance::STATUS_LATE, $attendance->status);

        Carbon::setTestNow();
    }

    /**
     * Test 4: Teacher-verified attendance cannot be overwritten by subsequent student join attempts (Improvement #7).
     */
    public function test_teacher_verified_attendance_is_protected_from_student_join_overwrite(): void
    {
        // Teacher manually verified student as 'excused'
        $attendance = StudentAttendance::create([
            'student_id'    => $this->student->id,
            'live_class_id' => $this->liveClass->id,
            'status'        => StudentAttendance::STATUS_EXCUSED,
            'verified_at'   => now()->subHour(),
            'verified_by'   => $this->teacherUser->id,
            'remarks'       => 'Medical certificate presented',
        ]);

        // Student tries to join
        $this->actingAs($this->studentUser)
            ->post(route('student.classes.join', $this->liveClass->id));

        $reloaded = $attendance->fresh();
        // Must still remain EXCUSED, not downgraded/changed to attended
        $this->assertEquals(StudentAttendance::STATUS_EXCUSED, $reloaded->status);
        $this->assertEquals($this->teacherUser->id, $reloaded->verified_by);
        $this->assertEquals('Medical certificate presented', $reloaded->remarks);
    }

    /**
     * Test 5: Calculation excludes pending_verification, future classes, and cancelled classes (Improvement #3).
     */
    public function test_attendance_calculation_excludes_pending_future_and_cancelled_classes(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-15 12:00:00'));

        // Past verified attended class
        $class1 = LiveClass::create([
            'course_id'        => $this->course->id,
            'teacher_id'       => $this->teacher->id,
            'title'            => 'Class 1 - Held',
            'scheduled_at'     => Carbon::parse('2026-10-02 10:00:00'),
            'duration_minutes' => 60,
            'status'           => 'completed',
        ]);
        StudentAttendance::create([
            'student_id'    => $this->student->id,
            'live_class_id' => $class1->id,
            'status'        => StudentAttendance::STATUS_ATTENDED,
            'verified_at'   => now(),
            'verified_by'   => $this->teacherUser->id,
        ]);

        // Past class where student did not join and teacher hasn't verified yet (pending_verification)
        $class2 = LiveClass::create([
            'course_id'        => $this->course->id,
            'teacher_id'       => $this->teacher->id,
            'title'            => 'Class 2 - Unverified Past',
            'scheduled_at'     => Carbon::parse('2026-10-05 10:00:00'),
            'duration_minutes' => 60,
            'status'           => 'completed',
        ]);
        // Note: No attendance row OR pending_verification row
        StudentAttendance::create([
            'student_id'    => $this->student->id,
            'live_class_id' => $class2->id,
            'status'        => StudentAttendance::STATUS_PENDING,
        ]);

        // Past class cancelled
        $class3 = LiveClass::create([
            'course_id'        => $this->course->id,
            'teacher_id'       => $this->teacher->id,
            'title'            => 'Class 3 - Cancelled',
            'scheduled_at'     => Carbon::parse('2026-10-08 10:00:00'),
            'duration_minutes' => 60,
            'status'           => 'cancelled',
        ]);

        // Future class (not yet held)
        $class4 = LiveClass::create([
            'course_id'        => $this->course->id,
            'teacher_id'       => $this->teacher->id,
            'title'            => 'Class 4 - Future',
            'scheduled_at'     => Carbon::parse('2026-10-25 10:00:00'),
            'duration_minutes' => 60,
            'status'           => 'scheduled',
        ]);

        $service = app(AttendanceService::class);
        $stats = $service->getStudentAttendanceStats($this->student);

        // Only class1 has finalized past status ('attended')
        // class2 is pending verification (excluded from past denominator)
        // class3 is cancelled (excluded)
        // class4 is future (excluded)
        $this->assertEquals(2, $stats['past']);
        $this->assertEquals(1, $stats['attended']);
        $this->assertEquals(100, $stats['pct']);

        Carbon::setTestNow();
    }

    /**
     * Test 6: Teacher can batch save attendance with verification timestamps and audit logging (Improvement #5 & #10).
     */
    public function test_teacher_can_save_attendance_batch_and_create_audit_events(): void
    {
        $payload = [
            'records' => [
                [
                    'student_id' => $this->student->id,
                    'status'     => 'attended',
                    'remarks'    => 'Active participation during Q&A',
                ],
            ],
        ];

        $response = $this->actingAs($this->teacherUser)
            ->postJson(route('teacher.classes.attendance.save', $this->liveClass->id), $payload);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $attendance = StudentAttendance::where('student_id', $this->student->id)
            ->where('live_class_id', $this->liveClass->id)
            ->first();

        $this->assertEquals(StudentAttendance::STATUS_ATTENDED, $attendance->status);
        $this->assertNotNull($attendance->verified_at);
        $this->assertEquals($this->teacherUser->id, $attendance->verified_by);
        $this->assertEquals('Active participation during Q&A', $attendance->remarks);

        // Check teacher save event in audit table
        $this->assertDatabaseHas('attendance_events', [
            'student_id'    => $this->student->id,
            'live_class_id' => $this->liveClass->id,
            'event_type'    => AttendanceEvent::TYPE_TEACHER_MARKED_PRESENT,
            'actor_id'      => $this->teacherUser->id,
        ]);
    }

    /**
     * Test 7: Student Attendance Correction Request Workflow (Improvement #6).
     */
    public function test_student_correction_request_lifecycle(): void
    {
        // Class held, student was marked absent
        $attendance = StudentAttendance::create([
            'student_id'    => $this->student->id,
            'live_class_id' => $this->liveClass->id,
            'status'        => StudentAttendance::STATUS_ABSENT,
            'verified_at'   => now(),
            'verified_by'   => $this->teacherUser->id,
        ]);

        // 1. Student submits correction request
        $response = $this->actingAs($this->studentUser)
            ->postJson(route('student.attendance.correction.submit'), [
                'live_class_id'    => $this->liveClass->id,
                'reason'           => 'I joined using my phone audio link due to home router power outage.',
                'requested_status' => 'attended',
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $correctionReq = AttendanceCorrectionRequest::where('student_id', $this->student->id)
            ->where('live_class_id', $this->liveClass->id)
            ->first();

        $this->assertNotNull($correctionReq);
        $this->assertEquals('pending', $correctionReq->status);

        // 2. Teacher approves the correction request
        $reviewResponse = $this->actingAs($this->teacherUser)
            ->postJson(route('teacher.attendance.corrections.review', $correctionReq->id), [
                'action'  => 'approved',
                'remarks' => 'Verified phone participation logs.',
            ]);

        $reviewResponse->assertOk();
        $this->assertEquals('approved', $correctionReq->fresh()->status);

        // Student's attendance record should now be updated to attended!
        $this->assertEquals(StudentAttendance::STATUS_ATTENDED, $attendance->fresh()->status);
    }

    /**
     * Test 8: Student restricted by overdue invoice cannot join live class (Improvement #8).
     */
    public function test_student_with_overdue_invoice_is_blocked_from_joining(): void
    {
        // Create overdue invoice for student
        Invoice::create([
            'student_id'      => $this->student->id,
            'invoice_number'  => 'INV-2026-OVERDUE-01',
            'title'           => 'SPM Monthly Tuition Fee',
            'billing_period'  => 'September 2026',
            'subtotal'        => 150.00,
            'total_amount'    => 150.00,
            'amount_paid'     => 0.00,
            'issued_date'     => now()->subDays(20)->toDateString(),
            'due_date'        => now()->subDays(10)->toDateString(),
            'status'          => 'overdue',
        ]);

        $response = $this->actingAs($this->studentUser)
            ->post(route('student.classes.join', $this->liveClass->id));

        // Blocked with 403 Restricted
        $response->assertStatus(403);

        // No attendance record created
        $this->assertDatabaseMissing('student_attendances', [
            'student_id'    => $this->student->id,
            'live_class_id' => $this->liveClass->id,
        ]);
    }
}
