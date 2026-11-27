<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** 2026-11-28: the parent portal gets Profile & Settings (details, password, subscription). */
class ParentProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $parent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parent = User::factory()->create(['role' => 'parent', 'name' => 'Siti Aminah', 'email' => 'siti@example.test', 'password' => Hash::make('OldPass123')]);
    }

    public function test_profile_page_renders_with_tabs_and_is_in_the_sidebar(): void
    {
        $child = Student::factory()->create(['user_id' => User::factory()->create(['role' => 'student', 'name' => 'Aina'])->id, 'level' => 'Form 2']);
        $child->parents()->attach($this->parent->id, ['relationship' => 'Mother', 'is_primary' => true, 'status' => 'active']);

        $this->actingAs($this->parent)->get(route('parent.profile'))
            ->assertOk()
            ->assertSee('Parent Details')
            ->assertSee('Subscription')
            ->assertSee('SITI AMINAH')
            ->assertSee('AINA')
            ->assertSee(route('parent.profile'), false);
    }

    public function test_parent_updates_their_details(): void
    {
        $this->actingAs($this->parent)->patch(route('parent.profile.update'), [
            'name' => 'Siti Aminah Binti Ali', 'email' => 'siti.new@example.test', 'phone' => '+60 12-345 6789',
            'address_line_1' => '12 Jalan Mawar', 'postcode' => '43300', 'city' => 'Seri Kembangan', 'state' => 'Selangor',
        ])->assertRedirect(route('parent.profile', ['tab' => 'profile']))->assertSessionHas('profile_updated');

        $this->parent->refresh();
        $this->assertSame('SITI AMINAH BINTI ALI', $this->parent->name);
        $this->assertSame('siti.new@example.test', $this->parent->email);
        $this->assertSame('Selangor', $this->parent->state);
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'taken@example.test']);

        $this->actingAs($this->parent)->patch(route('parent.profile.update'), ['name' => 'Siti', 'email' => 'taken@example.test'])
            ->assertSessionHasErrors('email');
        $this->assertSame('siti@example.test', $this->parent->fresh()->email);
    }

    public function test_password_change_needs_the_current_password(): void
    {
        $this->actingAs($this->parent)->patch(route('parent.profile.password'), [
            'current_password' => 'wrong', 'password' => 'NewPass123', 'password_confirmation' => 'NewPass123',
        ])->assertSessionHasErrors('current_password');

        $this->actingAs($this->parent)->patch(route('parent.profile.password'), [
            'current_password' => 'OldPass123', 'password' => 'NewPass123', 'password_confirmation' => 'NewPass123',
        ])->assertRedirect(route('parent.profile', ['tab' => 'security']));

        $this->assertTrue(Hash::check('NewPass123', $this->parent->fresh()->password));
    }

    public function test_only_parents_can_open_it(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'student']))->get(route('parent.profile'))->assertForbidden();
    }

    public function test_parent_cannot_manage_someone_elses_child(): void
    {
        $other = Student::factory()->create(['user_id' => User::factory()->create(['role' => 'student'])->id]);

        $this->actingAs($this->parent)->post(route('subscription.decline', $other->id))->assertForbidden();
    }
}
