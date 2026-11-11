<?php

namespace Tests\Feature;

use App\Models\AcademicEvent;
use App\Models\Course;
use App\Models\LiveClass;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * TimetableService (618 lines / 14 methods) had zero test coverage for its
 * write-side methods -- EventsJsonTest only exercises eventsJson(), the one
 * read method. This covers the teacher/admin direct-reschedule and
 * quick-create paths (now routed through the Timetable FormRequests added
 * in Phase 4's gap closure) and the academic-events CRUD, driven through
 * the real routes so the FormRequest + service combination is proven
 * together, not just the service in isolation.
 */
class TimetableServiceContractTest extends TestCase
{
    use RefreshDatabase;

    private function makeLiveClass(Teacher $teacher, Course $course, Carbon $scheduledAt): LiveClass
    {
        return LiveClass::create([
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'title' => 'Test Session',
            'scheduled_at' => $scheduledAt,
            'duration_minutes' => 60,
        ]);
    }

    // ── Teacher: direct reschedule ──────────────────────────────────────

    public function test_teacher_can_reschedule_their_own_class(): void
    {
        $teacher = Teacher::factory()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $class = $this->makeLiveClass($teacher, $course, now()->addDay());

        $response = $this->actingAs($teacher->user)->post(route('teacher.classes.reschedule-direct', $class->id), [
            'scheduled_date' => now()->addDays(3)->toDateString(),
            'scheduled_time' => '14:00',
            'reason' => 'Venue conflict resolved by moving the session.',
        ]);

        $response->assertRedirect();
        $this->assertTrue((bool) $class->fresh()->is_rescheduled);
        $this->assertDatabaseHas('class_requests', [
            'live_class_id' => $class->id,
            'type' => 'reschedule',
            'status' => 'approved',
        ]);
    }

    public function test_teacher_cannot_reschedule_another_teachers_class(): void
    {
        $owner = Teacher::factory()->create();
        $ownerCourse = Course::factory()->create(['teacher_id' => $owner->id]);
        $class = $this->makeLiveClass($owner, $ownerCourse, now()->addDay());

        $otherTeacher = Teacher::factory()->create();

        $response = $this->actingAs($otherTeacher->user)->post(route('teacher.classes.reschedule-direct', $class->id), [
            'scheduled_date' => now()->addDays(3)->toDateString(),
            'scheduled_time' => '14:00',
            'reason' => 'Trying to move someone elses class.',
        ]);

        $response->assertNotFound();
        $this->assertFalse((bool) $class->fresh()->is_rescheduled);
    }

    public function test_teacher_can_quick_create_a_session_for_their_own_course(): void
    {
        $teacher = Teacher::factory()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);

        $response = $this->actingAs($teacher->user)->post(route('teacher.classes.quick-create'), [
            'course_id' => $course->id,
            'title' => 'Extra Revision Session',
            'scheduled_date' => now()->addDays(2)->toDateString(),
            'scheduled_time' => '10:00',
            'duration_minutes' => 45,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('live_classes', [
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'title' => 'Extra Revision Session',
        ]);
    }

    public function test_teacher_cannot_quick_create_a_session_for_a_course_they_dont_teach(): void
    {
        $teacher = Teacher::factory()->create();
        Course::factory()->create(['teacher_id' => $teacher->id]);

        $otherTeacher = Teacher::factory()->create();
        $otherCourse = Course::factory()->create(['teacher_id' => $otherTeacher->id]);

        $response = $this->actingAs($teacher->user)->post(route('teacher.classes.quick-create'), [
            'course_id' => $otherCourse->id,
            'title' => 'Should be rejected',
            'scheduled_date' => now()->addDays(2)->toDateString(),
            'scheduled_time' => '10:00',
            'duration_minutes' => 45,
        ]);

        $response->assertSessionHasErrors('course_id');
        $this->assertDatabaseMissing('live_classes', ['title' => 'Should be rejected']);
    }

    // ── Admin: reschedule & quick-create ────────────────────────────────

    public function test_admin_can_reschedule_any_class(): void
    {
        $admin = User::factory()->create(['name' => 'Admin', 'email' => 'admin-tt@test.com', 'role' => 'admin', 'password' => Hash::make('password')]);
        $teacher = Teacher::factory()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $class = $this->makeLiveClass($teacher, $course, now()->addDay());

        $response = $this->actingAs($admin)->post(route('admin.classes.reschedule-direct', $class->id), [
            'scheduled_date' => now()->addDays(5)->toDateString(),
            'scheduled_time' => '09:00',
            'reason' => 'Admin-initiated reschedule.',
        ]);

        $response->assertRedirect();
        $this->assertTrue((bool) $class->fresh()->is_rescheduled);
        $this->assertSame($admin->id, $class->fresh()->rescheduled_by);
    }

    public function test_admin_can_quick_create_a_session_for_any_course(): void
    {
        $admin = User::factory()->create(['name' => 'Admin2', 'email' => 'admin-tt2@test.com', 'role' => 'admin', 'password' => Hash::make('password')]);
        $teacher = Teacher::factory()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);

        $response = $this->actingAs($admin)->post(route('admin.classes.quick-create'), [
            'course_id' => $course->id,
            'title' => 'Admin Scheduled Session',
            'scheduled_date' => now()->addDays(2)->toDateString(),
            'scheduled_time' => '11:00',
            'duration_minutes' => 60,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('live_classes', ['course_id' => $course->id, 'title' => 'Admin Scheduled Session']);
    }

    // ── Admin: academic events CRUD ─────────────────────────────────────

    public function test_admin_can_create_an_academic_event(): void
    {
        $admin = User::factory()->create(['name' => 'Admin3', 'email' => 'admin-tt3@test.com', 'role' => 'admin', 'password' => Hash::make('password')]);

        $response = $this->actingAs($admin)->post(route('admin.academic-events.store'), [
            'title' => 'Mid-Term Break',
            'event_type' => 'school_break',
            'start_date' => now()->addWeek()->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('academic_events', ['title' => 'Mid-Term Break', 'event_type' => 'school_break', 'created_by' => $admin->id]);
    }

    public function test_admin_can_update_an_academic_event(): void
    {
        $admin = User::factory()->create(['name' => 'Admin4', 'email' => 'admin-tt4@test.com', 'role' => 'admin', 'password' => Hash::make('password')]);
        $event = AcademicEvent::factory()->create(['title' => 'Old Title', 'event_type' => 'school_break']);

        $response = $this->actingAs($admin)->put(route('admin.academic-events.update', $event->id), [
            'title' => 'Renamed Break',
            'event_type' => 'school_break',
            'start_date' => $event->start_date->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertSame('Renamed Break', $event->fresh()->title);
    }

    public function test_admin_can_delete_an_academic_event(): void
    {
        $admin = User::factory()->create(['name' => 'Admin5', 'email' => 'admin-tt5@test.com', 'role' => 'admin', 'password' => Hash::make('password')]);
        $event = AcademicEvent::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.academic-events.destroy', $event->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('academic_events', ['id' => $event->id]);
    }
}
