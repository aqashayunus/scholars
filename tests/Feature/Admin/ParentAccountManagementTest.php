<?php

namespace Tests\Feature\Admin;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ParentAccountManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_link_a_parent_to_a_student_as_primary(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $parent = User::factory()->create(['role' => 'parent']);
        $student = Student::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.parents.link'), [
            'parent_user_id' => $parent->id,
            'student_id' => $student->id,
            'relationship' => 'Mother',
            'is_primary' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('parent_student', [
            'parent_user_id' => $parent->id,
            'student_id' => $student->id,
            'relationship' => 'Mother',
            'is_primary' => 1,
            'status' => 'active',
        ]);

        $this->assertEquals($parent->id, $student->refresh()->parent_user_id);
    }

    public function test_linking_a_second_primary_parent_unsets_the_previous_primary(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = Student::factory()->create();
        $firstParent = User::factory()->create(['role' => 'parent']);
        $secondParent = User::factory()->create(['role' => 'parent']);

        DB::table('parent_student')->insert([
            'parent_user_id' => $firstParent->id,
            'student_id' => $student->id,
            'relationship' => 'Father',
            'is_primary' => true,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)->post(route('admin.parents.link'), [
            'parent_user_id' => $secondParent->id,
            'student_id' => $student->id,
            'relationship' => 'Mother',
            'is_primary' => '1',
        ]);

        $this->assertDatabaseHas('parent_student', [
            'parent_user_id' => $firstParent->id,
            'student_id' => $student->id,
            'is_primary' => 0,
        ]);
        $this->assertDatabaseHas('parent_student', [
            'parent_user_id' => $secondParent->id,
            'student_id' => $student->id,
            'is_primary' => 1,
        ]);
        $this->assertEquals($secondParent->id, $student->refresh()->parent_user_id);
    }

    public function test_cannot_link_a_non_parent_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = Student::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.parents.link'), [
            'parent_user_id' => $teacher->id,
            'student_id' => $student->id,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('parent_student', [
            'parent_user_id' => $teacher->id,
            'student_id' => $student->id,
        ]);
    }

    public function test_admin_can_unlink_a_parent_and_legacy_column_repoints_to_remaining_link(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = Student::factory()->create();
        $primaryParent = User::factory()->create(['role' => 'parent']);
        $secondaryParent = User::factory()->create(['role' => 'parent']);

        DB::table('parent_student')->insert([
            ['parent_user_id' => $primaryParent->id, 'student_id' => $student->id, 'relationship' => 'Father', 'is_primary' => true, 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['parent_user_id' => $secondaryParent->id, 'student_id' => $student->id, 'relationship' => 'Mother', 'is_primary' => false, 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
        ]);
        $student->update(['parent_user_id' => $primaryParent->id]);

        $response = $this->actingAs($admin)->post(route('admin.parents.unlink'), [
            'parent_user_id' => $primaryParent->id,
            'student_id' => $student->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('parent_student', [
            'parent_user_id' => $primaryParent->id,
            'student_id' => $student->id,
        ]);
        $this->assertEquals($secondaryParent->id, $student->refresh()->parent_user_id);
    }

    public function test_unlinking_the_only_parent_clears_the_legacy_column(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = Student::factory()->create();
        $parent = User::factory()->create(['role' => 'parent']);

        DB::table('parent_student')->insert([
            'parent_user_id' => $parent->id,
            'student_id' => $student->id,
            'relationship' => 'Guardian',
            'is_primary' => true,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $student->update(['parent_user_id' => $parent->id]);

        $this->actingAs($admin)->post(route('admin.parents.unlink'), [
            'parent_user_id' => $parent->id,
            'student_id' => $student->id,
        ]);

        $this->assertNull($student->refresh()->parent_user_id);
    }

    public function test_non_admin_cannot_link_parent_to_student(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $parent = User::factory()->create(['role' => 'parent']);
        $student = Student::factory()->create();

        $response = $this->actingAs($teacher)->post(route('admin.parents.link'), [
            'parent_user_id' => $parent->id,
            'student_id' => $student->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_parent_accounts_directory_renders_on_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $parent = User::factory()->create(['role' => 'parent']);
        $student = Student::factory()->create();

        DB::table('parent_student')->insert([
            'parent_user_id' => $parent->id,
            'student_id' => $student->id,
            'relationship' => 'Guardian',
            'is_primary' => true,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertViewHas('allParentsList', function ($list) use ($parent) {
            return $list->contains('id', $parent->id);
        });
    }
}
