<?php

namespace Tests\Feature;

use App\Models\TeacherApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTeacherApplicationDocumentsTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdminUser(): User
    {
        return User::factory()->create([
            'role' => 'admin',
        ]);
    }

    protected function createRegularUser(): User
    {
        return User::factory()->create([
            'role' => 'student',
        ]);
    }

    public function test_guest_cannot_view_teacher_application_document_via_admin_route(): void
    {
        $app = TeacherApplication::create([
            'full_name'          => 'Test Applicant',
            'email'              => 'applicant@example.com',
            'phone'              => '+60 12-345 6789',
            'qualification'      => 'B.Sc Mathematics',
            'experience_years'   => 5,
            'preferred_subjects' => 'Maths',
            'resume_path'        => 'teacher_applications/resumes/sample.pdf',
        ]);

        $response = $this->get(route('admin.teacher-applications.document', ['id' => $app->id, 'type' => 'resume']));
        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_user_cannot_view_teacher_application_document_via_admin_route(): void
    {
        $user = $this->createRegularUser();
        $app = TeacherApplication::create([
            'full_name'          => 'Test Applicant',
            'email'              => 'applicant@example.com',
            'phone'              => '+60 12-345 6789',
            'qualification'      => 'B.Sc Mathematics',
            'experience_years'   => 5,
            'preferred_subjects' => 'Maths',
            'resume_path'        => 'teacher_applications/resumes/sample.pdf',
        ]);

        $response = $this->actingAs($user)->get(route('admin.teacher-applications.document', ['id' => $app->id, 'type' => 'resume']));
        $response->assertStatus(403);
    }

    public function test_admin_can_view_uploaded_teacher_resume_via_admin_route(): void
    {
        Storage::fake('public');
        $admin = $this->createAdminUser();

        $file = UploadedFile::fake()->create('my_resume.pdf', 300, 'application/pdf');
        $storedPath = $file->store('teacher_applications/resumes', 'public');

        $app = TeacherApplication::create([
            'full_name'          => 'Siti Aisyah',
            'email'              => 'aisyah@example.com',
            'phone'              => '+60 11-123 4567',
            'qualification'      => 'Master in Education',
            'experience_years'   => 4,
            'preferred_subjects' => 'Biology',
            'resume_path'        => $storedPath,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.teacher-applications.document', [
            'id'   => $app->id,
            'type' => 'resume',
        ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_fallback_storage_route_serves_teacher_application_file(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('RhrG9zYzqpPj1DlGgyyUVTC2zAcqTs6Bp86YWfsN.pdf', 250, 'application/pdf');
        $storedPath = $file->storeAs('teacher_applications/resumes', 'RhrG9zYzqpPj1DlGgyyUVTC2zAcqTs6Bp86YWfsN.pdf', 'public');

        $response = $this->get('/storage/teacher_applications/resumes/RhrG9zYzqpPj1DlGgyyUVTC2zAcqTs6Bp86YWfsN.pdf');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_admin_route_shows_friendly_not_found_view_when_file_is_missing_on_disk(): void
    {
        $admin = $this->createAdminUser();

        $app = TeacherApplication::create([
            'full_name'          => 'Ahmad Missing',
            'email'              => 'missing@example.com',
            'phone'              => '+60 19-876 5432',
            'qualification'      => 'Diploma',
            'experience_years'   => 1,
            'preferred_subjects' => 'History',
            'resume_path'        => 'teacher_applications/resumes/non_existent_file_12345.pdf',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.teacher-applications.document', [
            'id'   => $app->id,
            'type' => 'resume',
        ]));

        $response->assertStatus(404);
        $response->assertSee('Document File Not Found');
        $response->assertSee('Ahmad Missing');
        $response->assertSee('non_existent_file_12345.pdf');
    }
}
