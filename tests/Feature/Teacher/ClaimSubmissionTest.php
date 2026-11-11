<?php

namespace Tests\Feature\Teacher;

use App\Models\Course;
use App\Models\LiveClass;
use App\Models\Teacher;
use App\Models\TeacherClassClaim;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ClaimSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function makeTeacher(array $overrides = []): Teacher
    {
        $user = User::factory()->create(['role' => 'teacher']);

        return Teacher::create(array_merge([
            'user_id'          => $user->id,
            'qualification'    => 'B.Ed',
            'experience_years' => 5,
        ], $overrides));
    }

    public function test_teacher_without_bank_details_is_blocked_from_submitting_a_claim(): void
    {
        $teacher = $this->makeTeacher();

        $response = $this->actingAs($teacher->user)->post(route('teacher.claims.store'), [
            'claim_type'       => 'manual',
            'subject_name'     => 'Mathematics',
            'class_title'      => 'Algebra',
            'class_date'       => now()->toDateString(),
            'duration_minutes' => 60,
        ]);

        $response->assertSessionHasErrors('bank');
        $this->assertSame(0, TeacherClassClaim::count());
    }

    public function test_teacher_with_bank_details_can_submit_a_manual_claim_with_default_rate(): void
    {
        $teacher = $this->makeTeacher([
            'bank_name'           => 'Maybank',
            'bank_account_number' => '1234567890',
        ]);

        $response = $this->actingAs($teacher->user)->post(route('teacher.claims.store'), [
            'claim_type'       => 'manual',
            'subject_name'     => 'Mathematics',
            'class_title'      => 'Algebra Revision',
            'class_date'       => now()->toDateString(),
            'duration_minutes' => 60,
        ]);

        $response->assertRedirect(route('teacher.claims'));
        $this->assertDatabaseHas('teacher_class_claims', [
            'teacher_id'          => $teacher->id,
            'subject_name'        => 'Mathematics',
            'rate_per_class'      => 50.00,
            'amount'              => 50.00,
            'status'              => 'pending',
            'bank_name'           => 'Maybank',
            'bank_account_number' => '1234567890',
        ]);
    }

    public function test_teacher_can_submit_a_claim_with_custom_rate_and_proof_attachment(): void
    {
        $teacher = $this->makeTeacher([
            'bank_name'           => 'CIMB Bank',
            'bank_account_number' => '9988776655',
        ]);

        $response = $this->actingAs($teacher->user)->post(route('teacher.claims.store'), [
            'claim_type'       => 'manual',
            'subject_name'     => 'Science',
            'class_title'      => 'Photosynthesis',
            'class_date'       => now()->toDateString(),
            'duration_minutes' => 90,
            'rate_per_class'   => 75.50,
            'proof_attachment' => UploadedFile::fake()->create('proof.pdf', 100, 'application/pdf'),
        ]);

        $response->assertRedirect(route('teacher.claims'));
        $this->assertDatabaseHas('teacher_class_claims', [
            'teacher_id'     => $teacher->id,
            'rate_per_class' => 75.50,
            'amount'         => 75.50,
        ]);
        $claim = TeacherClassClaim::first();
        $this->assertNotNull($claim->proof_attachment_path);
    }

    public function test_duplicate_claim_on_same_live_class_is_blocked(): void
    {
        $teacher = $this->makeTeacher([
            'bank_name'           => 'Maybank',
            'bank_account_number' => '1234567890',
        ]);

        $liveClass = LiveClass::create([
            'course_id'        => Course::factory()->create()->id,
            'title'            => 'Algebra Revision',
            'scheduled_at'     => now()->subDay(),
            'duration_minutes' => 60,
            'status'           => 'completed',
        ]);

        TeacherClassClaim::create([
            'claim_number'     => TeacherClassClaim::generateClaimNumber(),
            'teacher_id'       => $teacher->id,
            'live_class_id'    => $liveClass->id,
            'subject_name'     => 'Mathematics',
            'class_title'      => 'Algebra',
            'class_date'       => now(),
            'duration_minutes' => 60,
            'rate_per_class'   => 50,
            'amount'           => 50,
            'status'           => 'pending',
        ]);

        $response = $this->actingAs($teacher->user)->post(route('teacher.claims.store'), [
            'claim_type'       => 'live_class',
            'live_class_id'    => $liveClass->id,
            'subject_name'     => 'Mathematics',
            'class_title'      => 'Algebra',
            'class_date'       => now()->toDateString(),
            'duration_minutes' => 60,
        ]);

        $response->assertSessionHasErrors('live_class_id');
        $this->assertEquals(1, TeacherClassClaim::count());
    }

    public function test_teacher_can_update_bank_details(): void
    {
        $teacher = $this->makeTeacher();

        $response = $this->actingAs($teacher->user)->post(route('teacher.claims.bank-details'), [
            'bank_name'           => 'Public Bank',
            'bank_account_number' => '5566778899',
            'bank_account_holder' => 'Jane Doe',
        ]);

        $response->assertRedirect(route('teacher.claims'));
        $teacher->refresh();
        $this->assertEquals('Public Bank', $teacher->bank_name);
        $this->assertEquals('5566778899', $teacher->bank_account_number);
        $this->assertEquals('Jane Doe', $teacher->bank_account_holder);
    }

    public function test_claims_index_page_loads_for_teacher(): void
    {
        $teacher = $this->makeTeacher();

        $response = $this->actingAs($teacher->user)->get(route('teacher.claims'));

        $response->assertOk();
        $response->assertViewHas('teacher');
        $response->assertViewHas('stats');
    }
}
