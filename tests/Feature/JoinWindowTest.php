<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LiveClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * "Join" is offered only from 15 minutes before a class until it ends
 * (the dashboard used to say "Join Class Now" for tomorrow's class).
 */
class JoinWindowTest extends TestCase
{
    use RefreshDatabase;

    private User $studentUser;

    private LiveClass $liveClass;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-10-15 09:00:00'));

        $teacher = Teacher::factory()->create(['application_status' => 'active'])->fresh();
        $subject = Subject::create(['name' => 'Physics', 'slug' => 'physics-jw', 'level' => 'SPM']);
        $course = Course::create(['title' => 'SPM Physics', 'subject_id' => $subject->id, 'teacher_id' => $teacher->id, 'level' => 'SPM', 'status' => 'published', 'price' => 50]);

        $this->studentUser = User::factory()->create(['role' => 'student']);
        $student = Student::factory()->create(['user_id' => $this->studentUser->id, 'status' => 'active', 'level' => 'SPM']);
        Enrollment::create(['student_id' => $student->id, 'course_id' => $course->id, 'status' => 'active', 'progress_pct' => 0]);

        $this->liveClass = LiveClass::create([
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'title' => 'Mechanics Session',
            'scheduled_at' => Carbon::parse('2026-10-16 10:00:00'),
            'duration_minutes' => 60,
            'meeting_url' => 'https://meet.google.com/abc-defg-hij',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_dashboard_does_not_offer_join_for_tomorrows_class(): void
    {
        $this->actingAs($this->studentUser)->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('Mechanics Session')
            ->assertSee('Join opens')
            ->assertDontSee('Join Class Now')
            ->assertDontSee('https://meet.google.com/abc-defg-hij');
    }

    public function test_dashboard_offers_join_15_minutes_before_class(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-16 09:50:00'));

        $this->actingAs($this->studentUser)->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('Join Class Now')
            ->assertSee(route('student.classes.join', $this->liveClass->id), false)
            ->assertDontSee('Join opens');
    }

    public function test_timetable_events_carry_the_join_window(): void
    {
        $events = $this->actingAs($this->studentUser)
            ->getJson(route('timetable.events', ['role' => 'student', 'year' => 2026]))
            ->assertOk()
            ->json('events');

        $event = collect($events)->firstWhere('class_id', $this->liveClass->id);
        $this->assertNotNull($event);
        $this->assertSame(
            Carbon::parse('2026-10-16 09:45:00')->toIso8601String(),
            $event['joinable_from'],
        );
    }
}
