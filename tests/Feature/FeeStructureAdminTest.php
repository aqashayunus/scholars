<?php

namespace Tests\Feature;

use App\Models\FeeStructure;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeeStructureAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_create_general_fee_structure(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.fee-structures.store'), [
            'name'            => 'PT3 Intensive Revision Pack',
            'description'     => 'Comprehensive PT3 revision program',
            'amount'          => 180.00,
            'education_level' => 'Form 1–3',
            'frequency'       => 'monthly',
            'is_active'       => '1',
            'student_id'      => '',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('fee_structures', [
            'name'            => 'PT3 Intensive Revision Pack',
            'amount'          => 180.00,
            'education_level' => 'Form 1–3',
            'frequency'       => 'monthly',
            'is_active'       => 1,
            'student_id'      => null,
        ]);
    }

    public function test_admin_can_create_targeted_student_fee_structure(): void
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'level'   => 'Form 5',
            'status'  => 'active',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.fee-structures.store'), [
            'name'            => 'SPM Elite Scholarship Plan',
            'description'     => 'Custom scholarship plan for top performer',
            'amount'          => 120.00,
            'education_level' => 'Form 5',
            'frequency'       => 'monthly',
            'is_active'       => '1',
            'student_id'      => $student->id,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('fee_structures', [
            'name'            => 'SPM Elite Scholarship Plan',
            'student_id'      => $student->id,
            'is_active'       => 1,
        ]);
    }

    public function test_admin_can_update_fee_structure_including_status_and_target(): void
    {
        $fee = FeeStructure::create([
            'name'            => 'Primary Standard Tuition',
            'description'     => 'Original description',
            'amount'          => 150.00,
            'education_level' => 'Year 1–6',
            'frequency'       => 'monthly',
            'is_active'       => true,
            'student_id'      => null,
        ]);

        $studentUser = User::factory()->create(['role' => 'student']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'level'   => 'Year 6',
            'status'  => 'active',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.fee-structures.update', $fee->id), [
            'name'            => 'Primary Standard Tuition (Discounted)',
            'description'     => 'Updated description with subsidy',
            'amount'          => 100.00,
            'education_level' => 'Year 6',
            'frequency'       => 'term',
            'is_active'       => '0',
            'student_id'      => $student->id,
        ]);

        $response->assertSessionHas('success');
        $fee->refresh();

        $this->assertEquals('Primary Standard Tuition (Discounted)', $fee->name);
        $this->assertEquals(100.00, (float) $fee->amount);
        $this->assertEquals('Year 6', $fee->education_level);
        $this->assertEquals('term', $fee->frequency);
        $this->assertFalse($fee->is_active);
        $this->assertEquals($student->id, $fee->student_id);
    }

    public function test_admin_can_delete_fee_structure(): void
    {
        $fee = FeeStructure::create([
            'name'            => 'Temporary Fee Plan',
            'amount'          => 50.00,
            'education_level' => 'All',
            'frequency'       => 'one_time',
            'is_active'       => true,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.fee-structures.destroy', $fee->id));

        $response->assertSessionHas('success');
        $this->assertSoftDeleted('fee_structures', ['id' => $fee->id]);
    }
}
