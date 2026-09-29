<?php

namespace Tests\Feature\Admin;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LiveClass;
use App\Models\RecordedClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassSchedulingManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    // ── Live Class ───────────────────────────────────────────────────────────

    public function test_admin_can_schedule_live_class_and_teacher_and_students_are_notified(): void
    {
        $teacher = Teacher::factory()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $student = Student::factory()->create();
        Enrollment::create([
            'student_id' => $student->id,
            'course_id'  => $course->id,
            'status'     => 'active',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.live-classes.store'), [
            'course_id'        => $course->id,
            'topic'            => 'Algebra',
            'title'            => 'Live Algebra Session',
            'scheduled_at'     => now()->addDay()->toDateTimeString(),
            'duration_minutes' => 60,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('live_classes', [
            'course_id' => $course->id,
            'title'     => 'Live Algebra Session',
        ]);

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $teacher->user_id,
            'type'    => 'live_class',
            'title'   => 'LIVE CLASS SCHEDULED',
        ]);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $student->user_id,
            'type'    => 'live_class',
            'title'   => 'NEW LIVE CLASS SCHEDULED',
        ]);
    }

    public function test_live_class_inherits_teacher_from_course_when_omitted(): void
    {
        $teacher = Teacher::factory()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);

        $this->actingAs($this->admin)->post(route('admin.live-classes.store'), [
            'course_id'        => $course->id,
            'topic'            => 'Geometry',
            'title'            => 'Live Geometry Session',
            'scheduled_at'     => now()->addDay()->toDateTimeString(),
            'duration_minutes' => 45,
        ]);

        $liveClass = LiveClass::where('title', 'Live Geometry Session')->first();
        $this->assertSame($teacher->id, $liveClass->teacher_id);
    }

    public function test_admin_can_update_live_class(): void
    {
        $course = Course::factory()->create();
        $liveClass = LiveClass::create([
            'course_id'        => $course->id,
            'topic'            => 'Old Topic',
            'title'            => 'Old Title',
            'scheduled_at'     => now()->addDay(),
            'duration_minutes' => 60,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.live-classes.update', $liveClass->id), [
            'course_id'        => $course->id,
            'topic'            => 'New Topic',
            'title'            => 'New Title',
            'scheduled_at'     => now()->addDays(2)->toDateTimeString(),
            'duration_minutes' => 90,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('live_classes', ['id' => $liveClass->id, 'title' => 'New Title']);
    }

    public function test_admin_can_delete_live_class(): void
    {
        $course = Course::factory()->create();
        $liveClass = LiveClass::create([
            'course_id'        => $course->id,
            'topic'            => 'Topic',
            'title'            => 'To Remove',
            'scheduled_at'     => now()->addDay(),
            'duration_minutes' => 60,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.live-classes.destroy', $liveClass->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('live_classes', ['id' => $liveClass->id]);
    }

    // ── Recorded Class ───────────────────────────────────────────────────────

    public function test_admin_can_add_recorded_class_and_enrolled_students_are_notified(): void
    {
        $course = Course::factory()->create();
        $student = Student::factory()->create();
        Enrollment::create([
            'student_id' => $student->id,
            'course_id'  => $course->id,
            'status'     => 'active',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.recorded-classes.store'), [
            'course_id'        => $course->id,
            'title'            => 'Recorded Lesson 1',
            'topic_name'       => 'Fractions',
            'video_url'        => 'https://videos.example.com/1',
            'duration_minutes' => 30,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('recorded_classes', ['title' => 'Recorded Lesson 1', 'views_count' => 0]);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $student->user_id,
            'type'    => 'course',
            'title'   => 'NEW RECORDED LESSON AVAILABLE',
        ]);
    }

    public function test_admin_can_update_recorded_class(): void
    {
        $recordedClass = RecordedClass::create([
            'title'            => 'Old Title',
            'topic_name'       => 'Topic',
            'video_url'        => 'https://videos.example.com/2',
            'duration_minutes' => 20,
            'views_count'      => 5,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.recorded-classes.update', $recordedClass->id), [
            'title'            => 'New Title',
            'topic_name'       => 'Topic',
            'video_url'        => 'https://videos.example.com/2',
            'duration_minutes' => 25,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('recorded_classes', ['id' => $recordedClass->id, 'title' => 'New Title']);
    }

    public function test_admin_can_delete_recorded_class(): void
    {
        $recordedClass = RecordedClass::create([
            'title'            => 'To Remove',
            'topic_name'       => 'Topic',
            'video_url'        => 'https://videos.example.com/3',
            'duration_minutes' => 15,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.recorded-classes.destroy', $recordedClass->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('recorded_classes', ['id' => $recordedClass->id]);
    }
}
