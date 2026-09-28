<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomRegistrationTest extends TestCase
{
    use RefreshDatabase;
    public function test_student_registration_screen_renders_successfully(): void
    {
        $response = $this->get(route('register.student'));
        $response->assertStatus(200);
        $response->assertSee('studentRegData()');
        $response->assertSee('step: 1', false);
    }

    public function test_student_registration_password_error_restores_to_step_4(): void
    {
        $response = $this->from(route('register.student'))->post(route('register.student.submit'), [
            'name' => 'John Doe',
            'ic_number' => '080512-10-1234',
            'email' => 'johndoe_test@example.com',
            'phone' => '+60123456789',
            'nationality' => 'Malaysian',
            'state' => 'Selangor',
            'programme' => 'secondary',
            'level' => 'Form 4',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertRedirect(route('register.student'));
        $response->assertSessionHasErrors(['password']);

        $follow = $this->get(route('register.student'));
        $follow->assertStatus(200);
        $follow->assertSee('step: 4', false);
        $follow->assertSee('Form 4', false);
    }

    public function test_parent_registration_screen_renders_successfully(): void
    {
        $response = $this->get(route('register.parent'));
        $response->assertStatus(200);
        $response->assertSee('parentRegData()');
        $response->assertSee('step: 1', false);
    }

    public function test_parent_registration_child_error_restores_to_step_2(): void
    {
        $response = $this->from(route('register.parent'))->post(route('register.parent.submit'), [
            'nationality_type' => 'local',
            'parent_name' => 'Jane Doe',
            'parent_ic_number' => '800101-14-1234',
            'relationship' => 'Mother',
            'address_line_1' => '123 Test Street',
            'postcode' => '43300',
            'city' => 'Seri Kembangan',
            'state' => 'Selangor',
            'email' => 'janedoe_test@example.com',
            'phone' => '+60123456788',
            'password' => 'ValidPass123!',
            'password_confirmation' => 'ValidPass123!',
            'child_has_account' => 'yes',
            'child_ic_number' => 'invalid-ic',
        ]);

        $response->assertRedirect(route('register.parent'));
        $response->assertSessionHasErrors(['child_ic_number']);

        $follow = $this->get(route('register.parent'));
        $follow->assertStatus(200);
        $follow->assertSee('step: 2', false);
    }
}
