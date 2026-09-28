<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    /**
     * Regression: this endpoint previously accepted unvalidated child_name/child_email
     * and created a second User with a hardcoded 'password123' fallback password.
     * That branch has been removed entirely — posting those fields must not create
     * any additional user.
     */
    public function test_child_fields_no_longer_create_a_second_account(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Parent Only',
            'email'                 => 'parentonly@example.com',
            'password'              => 'Password1!',
            'password_confirmation' => 'Password1!',
            'role'                  => 'parent',
            'child_name'            => 'Injected Child',
            'child_email'           => 'injectedchild@example.com',
        ]);

        $this->assertAuthenticated();
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseMissing('users', ['email' => 'injectedchild@example.com']);

        $parent = User::where('email', 'parentonly@example.com')->first();
        $this->assertNotNull($parent);
        $this->assertSame('parent', $parent->role);
    }
}
