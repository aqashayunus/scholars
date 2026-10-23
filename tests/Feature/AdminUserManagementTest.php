<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role'      => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_get_admin_users_redirects_to_dashboard()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.users.index'));

        $response->assertRedirect(route('admin.dashboard') . '#users');
    }

    public function test_get_admin_user_by_id_does_not_throw_method_not_allowed()
    {
        $targetUser = User::factory()->create([
            'name'  => 'John Target',
            'email' => 'target@example.com',
            'role'  => 'student',
        ]);

        // Standard web browser request (GET /admin/users/{id})
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users/' . $targetUser->id);

        $response->assertRedirect(route('admin.dashboard') . '#users');

        // AJAX / JSON request (GET /admin/users/{id})
        $jsonResponse = $this->actingAs($this->adminUser)
            ->getJson('/admin/users/' . $targetUser->id);

        $jsonResponse->assertOk()
            ->assertJson([
                'id'    => $targetUser->id,
                'email' => 'target@example.com',
            ]);
    }

    public function test_admin_can_update_user_via_put_or_post()
    {
        $targetUser = User::factory()->create([
            'name'  => 'Original Name',
            'email' => 'original@example.com',
            'role'  => 'student',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson('/admin/users/' . $targetUser->id, [
                'name'  => 'Updated Name',
                'email' => 'updated@example.com',
                'role'  => 'teacher',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('users', [
            'id'    => $targetUser->id,
            'name'  => 'UPDATED NAME',
            'email' => 'updated@example.com',
            'role'  => 'teacher',
        ]);
    }

    public function test_admin_can_delete_user_via_delete_method()
    {
        $targetUser = User::factory()->create([
            'name'  => 'To Be Deleted',
            'email' => 'tobedeleted@example.com',
            'role'  => 'student',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->deleteJson('/admin/users/' . $targetUser->id);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
    }

    public function test_admin_can_delete_user_via_post_with_method_delete_spoofing()
    {
        $targetUser = User::factory()->create([
            'name'  => 'To Be Deleted Via Post',
            'email' => 'tobedeletedpost@example.com',
            'role'  => 'student',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson('/admin/users/' . $targetUser->id, [
                '_method' => 'DELETE',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
    }

    public function test_admin_can_delete_user_via_delete_post_route()
    {
        $targetUser = User::factory()->create([
            'name'  => 'To Be Deleted Post Route',
            'email' => 'tobedeletedpostroute@example.com',
            'role'  => 'student',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson('/admin/users/' . $targetUser->id . '/delete');

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
    }
}
