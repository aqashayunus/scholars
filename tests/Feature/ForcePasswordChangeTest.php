<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ForcePasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_flagged_user_is_redirected_to_set_a_new_password(): void
    {
        $user = User::factory()->create([
            'name' => 'Flagged User',
            'email' => 'flagged@test.com',
            'role' => 'student',
            'password' => Hash::make('temp-password'),
        ]);
        $user->must_change_password = true;
        $user->save();

        $response = $this->actingAs($user)->get(route('student.dashboard'));

        $response->assertRedirect(route('password.force.show'));
    }

    public function test_setting_a_new_password_clears_the_flag_and_restores_access(): void
    {
        $user = User::factory()->create([
            'name' => 'Flagged User 2',
            'email' => 'flagged2@test.com',
            'role' => 'student',
            'password' => Hash::make('temp-password'),
        ]);
        $user->must_change_password = true;
        $user->save();

        $response = $this->actingAs($user)->post(route('password.force.update'), [
            'password' => 'BrandNewPassword1!',
            'password_confirmation' => 'BrandNewPassword1!',
        ]);

        $response->assertRedirect(route($user->getDashboardRoute()));

        $user->refresh();
        $this->assertFalse((bool) $user->must_change_password);
        $this->assertTrue(Hash::check('BrandNewPassword1!', $user->password));

        // Now a normal request should NOT be redirected back to the force-change screen.
        $profile = $this->actingAs($user)->get('/profile');
        $profile->assertOk();
    }

    public function test_flagged_user_can_still_reach_the_set_password_screen_itself(): void
    {
        $user = User::factory()->create([
            'name' => 'Flagged User 3',
            'email' => 'flagged3@test.com',
            'role' => 'student',
            'password' => Hash::make('temp-password'),
        ]);
        $user->must_change_password = true;
        $user->save();

        $response = $this->actingAs($user)->get(route('password.force.show'));

        $response->assertOk();
    }

    public function test_set_password_screen_shows_the_olympian_scholars_logo(): void
    {
        $user = User::factory()->create(['role' => 'teacher', 'password' => Hash::make('temp-password')]);
        $user->must_change_password = true;
        $user->save();

        $this->actingAs($user)->get(route('password.force.show'))
            ->assertOk()
            ->assertSee(asset('images/logo-icon.png'), false)
            ->assertSee('alt="Olympian Scholars"', false);
    }
}
