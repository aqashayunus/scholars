<?php

namespace Tests\Feature\Timetable;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LiveClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Regression coverage for TimetableController::getEventsJson — this route
 * had no test at all, and until now the student/parent branches called
 * StudentDashboardController::getStudentLiveClassesQuery(), a static method
 * that does not exist on that class (it was moved to
 * Student\ClassScheduleService during an earlier refactor and this call
 * site was never updated). Every student or parent request to this
 * endpoint threw an undefined-method fatal error in production.
 */
class EventsJsonTest extends TestCase
{
    use RefreshDatabase;

    private Teacher $teacher;
    private Course $course;
    private LiveClass $liveClass;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-10-15 09:00:00'));

        $teacherUser = User::create(['name' => 'Cikgu Lee', 'email' => 'lee@tt.test', 'role' => 'teacher', 'password' => Hash::make('password')]);
        $this->teacher = Teacher::create(['user_id' => $teacherUser->id, 'application_status' => 'active', 'qualification' => 'Master of Physics']);

        $subject = Subject::create(['name' => 'Physics', 'slug' => 'physics-tt', 'level' => 'SPM']);
        $this->course = Course::create([
            'title'      => 'SPM Physics',
            'subject_id' => $subject->id,
            'teacher_id' => $this->teacher->id,
            'level'      => 'SPM',
            'status'     => 'published',
            'price'      => 50.00,
        ]);

        $this->liveClass = LiveClass::create([
            'course_id'        => $this->course->id,
            'teacher_id'       => $this->teacher->id,
            'title'            => 'Mechanics Session',
            'scheduled_at'     => Carbon::parse('2026-10-16 10:00:00'),
            'duration_minutes' => 60,
            'meeting_url'      => 'https://meet.test/physics',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_student_role_events_json_does_not_crash_and_includes_the_live_class(): void
    {
        $studentUser = User::create(['name' => 'Ali', 'email' => 'ali@tt.test', 'role' => 'student', 'password' => Hash::make('password')]);
        $student = Student::create(['user_id' => $studentUser->id, 'level' => 'SPM']);
        Enrollment::create(['student_id' => $student->id, 'course_id' => $this->course->id, 'enrolled_at' => now(), 'progress_pct' => 0, 'status' => 'active']);

        $response = $this->actingAs($studentUser)
            ->getJson(route('timetable.events', ['role' => 'student', 'year' => 2026]));

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $events = $response->json('events');
        $this->assertTrue(collect($events)->contains(fn($e) => $e['type'] === 'live_class' && $e['class_id'] === $this->liveClass->id));
    }

    public function test_parent_role_events_json_does_not_crash_and_includes_the_childs_live_class(): void
    {
        $parentUser = User::create(['name' => 'Parent Tan', 'email' => 'tan@tt.test', 'role' => 'parent', 'password' => Hash::make('password')]);
        $studentUser = User::create(['name' => 'Mei', 'email' => 'mei@tt.test', 'role' => 'student', 'password' => Hash::make('password')]);
        $student = Student::create(['user_id' => $studentUser->id, 'parent_user_id' => $parentUser->id, 'level' => 'SPM']);
        Enrollment::create(['student_id' => $student->id, 'course_id' => $this->course->id, 'enrolled_at' => now(), 'progress_pct' => 0, 'status' => 'active']);

        $response = $this->actingAs($parentUser)
            ->getJson(route('timetable.events', ['role' => 'parent', 'year' => 2026]));

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $events = $response->json('events');
        $this->assertTrue(collect($events)->contains(fn($e) => $e['type'] === 'live_class' && $e['class_id'] === $this->liveClass->id));
    }

    public function test_teacher_role_events_json_includes_own_class(): void
    {
        $response = $this->actingAs($this->teacher->user)
            ->getJson(route('timetable.events', ['role' => 'teacher', 'year' => 2026]));

        $response->assertOk();
        $events = $response->json('events');
        $this->assertTrue(collect($events)->contains(fn($e) => $e['type'] === 'live_class' && $e['class_id'] === $this->liveClass->id));
    }
}
