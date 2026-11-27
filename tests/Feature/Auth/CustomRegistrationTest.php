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

    private function studentPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'SPM Scholar',
            'ic_number' => '090101-10-9876',
            'email' => 'spm_scholar@example.com',
            'phone' => '+60 12-345 6789',
            'nationality' => 'Malaysian',
            'state' => 'Kuala Lumpur',
            'programme' => 'secondary',
            'school_name' => 'SMK Taman Melawati',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
        ], $overrides);
    }

    public function test_student_registers_with_a_single_level(): void
    {
        $response = $this->post(route('register.student.submit'), $this->studentPayload(['level' => 'Form 4']));

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('students', ['level' => 'Form 4', 'school_name' => 'SMK Taman Melawati']);
    }

    public function test_student_cannot_register_with_multiple_levels(): void
    {
        $response = $this->from(route('register.student'))
            ->post(route('register.student.submit'), $this->studentPayload(['levels' => ['Form 4', 'Form 5']]));

        $response->assertRedirect(route('register.student'));
        $response->assertSessionHasErrors(['levels']);
        $this->assertDatabaseMissing('users', ['email' => 'spm_scholar@example.com']);
    }

    public function test_student_cannot_sneak_multiple_levels_through_the_level_field(): void
    {
        $response = $this->from(route('register.student'))
            ->post(route('register.student.submit'), $this->studentPayload(['level' => 'Form 4, Form 5']));

        $response->assertSessionHasErrors(['level']);
        $this->assertDatabaseMissing('users', ['email' => 'spm_scholar@example.com']);
    }

    public function test_student_cannot_pick_subjects_from_another_level(): void
    {
        $response = $this->from(route('register.student'))
            ->post(route('register.student.submit'), $this->studentPayload([
                'level' => 'Form 4',
                'subjects' => ['Bahasa Melayu - Form 4', 'Bahasa Melayu - Form 5'],
            ]));

        $response->assertSessionHasErrors(['subjects']);
        $this->assertDatabaseMissing('users', ['email' => 'spm_scholar@example.com']);
    }

    public function test_registration_form_offers_one_level_choice(): void
    {
        $html = $this->get(route('register.student'))->assertOk()->getContent();

        $this->assertStringContainsString('type="radio" name="level"', $html);
        $this->assertStringNotContainsString('name="levels[]"', $html);
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
