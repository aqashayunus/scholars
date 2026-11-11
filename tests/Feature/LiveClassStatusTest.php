<?php

namespace Tests\Feature;

use App\Enums\LiveClassStatus;
use App\Models\Course;
use App\Models\LiveClass;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Phase 3 — LiveClass::status is architecturally different from the other
 * enum conversions: it's computed live from scheduled_at/duration via a
 * custom accessor (getStatusAttribute), not a simple stored value. Changing
 * that accessor's return type from string to LiveClassStatus carries real
 * risk to a real-time feature, so this proves the computation is still
 * correct across all three states rather than trusting the conversion.
 */
class LiveClassStatusTest extends TestCase
{
    use RefreshDatabase;

    private function makeLiveClass(Carbon $scheduledAt): LiveClass
    {
        $teacher = Teacher::factory()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);

        return LiveClass::create([
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'title' => 'Test Class',
            'scheduled_at' => $scheduledAt,
            'duration_minutes' => 60,
        ]);
    }

    public function test_a_future_class_is_upcoming(): void
    {
        $class = $this->makeLiveClass(now()->addDay());

        $this->assertSame(LiveClassStatus::Upcoming, $class->status);
        $this->assertSame('upcoming', $class->status->value);
    }

    public function test_a_class_currently_within_its_scheduled_window_is_live(): void
    {
        $class = $this->makeLiveClass(now()->subMinutes(10));

        $this->assertSame(LiveClassStatus::Live, $class->status);
    }

    public function test_a_class_past_its_scheduled_window_is_completed(): void
    {
        $class = $this->makeLiveClass(now()->subHours(3));

        $this->assertSame(LiveClassStatus::Completed, $class->status);
    }

    public function test_status_is_persisted_as_a_raw_string_the_db_column_accepts(): void
    {
        $class = $this->makeLiveClass(now()->addDay());

        $raw = DB::table('live_classes')->where('id', $class->id)->value('status');

        $this->assertSame('upcoming', $raw);
    }
}
