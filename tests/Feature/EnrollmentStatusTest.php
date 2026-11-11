<?php

namespace Tests\Feature;

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Phase 3 — EnrollmentStatus had zero test references despite being one of
 * the 7 enum conversions; this is the one guarding test. Covers the cast
 * round-trip, Student::activeEnrollments()'s raw-string query-builder scope
 * (SQL-level comparison, not a PHP loose comparison, so it stays correct
 * after the cast), and Chat\MessagingService's pattern of passing enum cases
 * directly into whereIn() -- Laravel resolves BackedEnum values when
 * binding, but that's exactly the kind of thing worth proving rather than
 * assuming.
 */
class EnrollmentStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_casts_to_the_enum_on_read(): void
    {
        $enrollment = Enrollment::factory()->create(['status' => 'active']);

        $this->assertSame(EnrollmentStatus::Active, $enrollment->fresh()->status);
    }

    public function test_status_is_persisted_as_the_raw_string_value(): void
    {
        $enrollment = Enrollment::factory()->create(['status' => EnrollmentStatus::Completed]);

        $raw = DB::table('enrollments')->where('id', $enrollment->id)->value('status');

        $this->assertSame('completed', $raw);
    }

    public function test_active_enrollments_scope_only_returns_active_status(): void
    {
        $student = Student::factory()->create();
        Enrollment::factory()->create(['student_id' => $student->id, 'status' => EnrollmentStatus::Active]);
        Enrollment::factory()->create(['student_id' => $student->id, 'status' => EnrollmentStatus::Dropped]);
        Enrollment::factory()->create(['student_id' => $student->id, 'status' => EnrollmentStatus::Completed]);

        $active = $student->activeEnrollments()->get();

        $this->assertCount(1, $active);
        $this->assertSame(EnrollmentStatus::Active, $active->first()->status);
    }

    public function test_where_in_with_enum_cases_matches_by_value_not_reference(): void
    {
        $student = Student::factory()->create();
        $enrolled = Enrollment::factory()->create(['student_id' => $student->id, 'status' => EnrollmentStatus::Enrolled]);
        $active = Enrollment::factory()->create(['student_id' => $student->id, 'status' => EnrollmentStatus::Active]);
        $completed = Enrollment::factory()->create(['student_id' => $student->id, 'status' => EnrollmentStatus::Completed]);
        $dropped = Enrollment::factory()->create(['student_id' => $student->id, 'status' => EnrollmentStatus::Dropped]);

        $matched = Enrollment::where('student_id', $student->id)
            ->whereIn('status', [EnrollmentStatus::Enrolled, EnrollmentStatus::Active, EnrollmentStatus::Completed])
            ->pluck('id')
            ->all();

        $this->assertEqualsCanonicalizing([$enrolled->id, $active->id, $completed->id], $matched);
        $this->assertNotContains($dropped->id, $matched);
    }
}
