<?php

namespace Tests\Feature;

use App\Models\AttendanceCorrectionRequest;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LiveClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Found 2026-11-26 on the test site:
 * - the student Classes page crashed (undefined $correctionRequests) as
 *   soon as the student had a past or ongoing class;
 * - the dashboard "Next Live Class" card showed any class that started in
 *   the last 2h, so an ended class stayed as "UPCOMING" and hid the next
 *   one, cancelled classes showed, and classes over 2h dropped off early.
 */
class StudentClassesAndNextClassTest extends TestCase
{
    use RefreshDatabase;

    private User $studentUser;

    private Student $student;

    private Teacher $teacher;

    private Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = Teacher::factory()->create(['application_status' => 'active'])->fresh();
        $this->studentUser = User::factory()->create(['role' => 'student']);
        $this->student = Student::factory()->create(['user_id' => $this->studentUser->id, 'level' => 'Year 4', 'status' => 'active']);
        $subject = Subject::create(['name' => 'Mathematics', 'slug' => 'scn-m4', 'code' => 'SCNM4', 'education_type' => 'primary', 'form_year' => 'Year 4']);
        $this->course = Course::create(['title' => 'Demo Maths Class', 'teacher_id' => $this->teacher->id, 'subject_id' => $subject->id, 'level' => 'Year 4', 'price' => 0]);
        Enrollment::create(['student_id' => $this->student->id, 'course_id' => $this->course->id]);
    }

    /**
     * Title shown in the dashboard's "Next Live Class" hero card (the
     * weekly calendar strip lists every class of the day, so plain
     * assertSee/assertDontSee on the page can't tell them apart).
     */
    private function nextClassTitle(): ?string
    {
        $html = $this->actingAs($this->studentUser)->get(route('student.dashboard'))->assertOk()->getContent();

        return preg_match('#<h2 class="font-heading font-black text-2xl sm:text-3xl text-gray-900 leading-tight">\s*(.*?)\s*</h2>#s', $html, $m)
            ? html_entity_decode($m[1])
            : null;
    }

    private function liveClass(string $title, \DateTimeInterface $start, int $minutes = 60, array $extra = []): LiveClass
    {
        return LiveClass::create([
            'course_id' => $this->course->id,
            'teacher_id' => $this->teacher->id,
            'topic' => 'Fractions',
            'title' => $title,
            'scheduled_at' => $start,
            'duration_minutes' => $minutes,
            'meeting_link' => 'https://meet.google.com/abc-defg-hij',
        ] + $extra);
    }

    public function test_classes_page_loads_with_past_and_ongoing_classes(): void
    {
        $past = $this->liveClass('Yesterday Class', now()->subDay());
        $this->liveClass('Ongoing Class', now()->subMinutes(20));

        $this->actingAs($this->studentUser)->get(route('student.classes'))
            ->assertOk()
            ->assertSee('Yesterday Class');

        AttendanceCorrectionRequest::create([
            'student_id' => $this->student->id,
            'live_class_id' => $past->id,
            'requested_status' => 'attended',
            'reason' => 'I was there',
            'status' => 'pending',
        ]);

        $this->actingAs($this->studentUser)->get(route('student.classes'))
            ->assertOk()
            ->assertSee('Correction: Pending');
    }

    public function test_dashboard_skips_an_ended_class_and_shows_the_next_one(): void
    {
        $this->liveClass('Ended Class', now()->subMinutes(90), 60);
        $this->liveClass('Tomorrow Class', now()->addDay());

        $this->assertSame('Tomorrow Class', $this->nextClassTitle());
    }

    public function test_dashboard_keeps_join_for_the_whole_running_class(): void
    {
        $long = $this->liveClass('Long Workshop', now()->subMinutes(150), 180);

        $this->actingAs($this->studentUser)->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('Long Workshop')
            ->assertSee('LIVE NOW')
            ->assertSee(route('student.classes.join', $long->id), false);
    }

    public function test_dashboard_skips_a_cancelled_class(): void
    {
        $cancelled = $this->liveClass('Cancelled Class', now()->addHour());
        $cancelled->update(['status' => 'cancelled']);
        $this->liveClass('Real Next Class', now()->addHours(3));

        $this->assertSame('Real Next Class', $this->nextClassTitle());
    }
}
