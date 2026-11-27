<?php

namespace Tests\Feature\Admin;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * 2026-11-28: IC detail in Admin > Student Relationship & Linkages. Only a
 * one-way hash of the IC is stored; we now also keep a masked copy
 * ("******-**-1234") and admin can verify an IC given to them.
 */
class StudentIcTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Student $student;

    private User $parent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->student = Student::factory()->create([
            'user_id' => User::factory()->create(['role' => 'student', 'name' => 'Aina'])->id,
            'ic_number_hash' => Student::hashIc('080512-10-1234'),
            'ic_masked' => null, // registered before masked ICs were kept
            'date_of_birth' => '2008-05-12', 'gender' => 'female', 'nationality' => 'Malaysian',
        ]);
        $this->parent = User::factory()->create(['role' => 'parent', 'ic_number_hash' => User::hashIc('800101-14-5678')]);
        $this->student->parents()->attach($this->parent->id, ['relationship' => 'Mother', 'is_primary' => true, 'status' => 'active']);
    }

    private function verify(array $body, ?User $as = null): TestResponse
    {
        return $this->actingAs($as ?? $this->admin)->postJson(route('admin.students.verify-ic', $this->student->id), $body);
    }

    public function test_new_registrations_keep_a_masked_ic(): void
    {
        $this->post(route('register.student.submit'), [
            'name' => 'New Scholar', 'ic_number' => '090101-10-9876', 'email' => 'new@example.test', 'phone' => '+60 12-345 6789',
            'nationality' => 'Malaysian', 'state' => 'Selangor', 'programme' => 'secondary', 'level' => 'Form 4',
            'password' => 'StrongPass123!', 'password_confirmation' => 'StrongPass123!',
        ])->assertSessionHasNoErrors();

        $student = Student::whereHas('user', fn ($q) => $q->where('email', 'new@example.test'))->sole();
        $this->assertSame('******-**-9876', $student->ic_masked);
        $this->assertTrue($student->matchesIc('090101-10-9876'));
    }

    public function test_modal_shows_ic_details(): void
    {
        $this->actingAs($this->admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('id="modalStudentIc"', false)
            ->assertSee('On file (verify to show)')
            ->assertSee('12 May 2008 (age', false)
            ->assertSee('Verify IC');
    }

    public function test_matching_ic_is_confirmed_and_the_masked_ic_is_saved(): void
    {
        $this->verify(['ic' => '080512101234', 'who' => 'student'])
            ->assertOk()
            ->assertJson(['match' => true, 'masked' => '******-**-1234']);

        $this->assertSame('******-**-1234', $this->student->fresh()->ic_masked);
    }

    public function test_wrong_ic_does_not_match_and_saves_nothing(): void
    {
        $this->verify(['ic' => '080512-10-9999', 'who' => 'student'])
            ->assertOk()
            ->assertJson(['match' => false]);

        $this->assertNull($this->student->fresh()->ic_masked);
    }

    public function test_parent_ic_can_be_verified_but_only_for_a_linked_parent(): void
    {
        $this->verify(['ic' => '800101-14-5678', 'who' => 'parent:'.$this->parent->id])
            ->assertOk()->assertJson(['match' => true, 'masked' => '******-**-5678']);
        $this->assertSame('******-**-5678', $this->parent->fresh()->ic_masked);

        $stranger = User::factory()->create(['role' => 'parent', 'ic_number_hash' => User::hashIc('700101-14-0000')]);
        $this->verify(['ic' => '700101-14-0000', 'who' => 'parent:'.$stranger->id])->assertStatus(422);
        $this->assertNull($stranger->fresh()->ic_masked);
    }

    public function test_only_admins_can_verify(): void
    {
        $this->verify(['ic' => '080512-10-1234', 'who' => 'student'], $this->parent)->assertForbidden();
    }
}
