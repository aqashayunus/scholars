<?php

namespace Tests\Feature;

use App\Models\Teacher;
use App\Models\TeacherApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeacherProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function createTeacherUser(): array
    {
        $user = User::factory()->create([
            'role'     => 'teacher',
            'password' => Hash::make('Password123!'),
        ]);

        $teacher = Teacher::create([
            'user_id'            => $user->id,
            'application_status' => 'active',
            'qualification'      => 'Bachelor of Science (Physics)',
            'experience_years'   => 4,
            'specialization'     => 'Physics, Science',
            'bio'                => 'Passionate physics teacher',
        ]);

        $application = TeacherApplication::create([
            'user_id'            => $user->id,
            'full_name'          => $user->name,
            'email'              => $user->email,
            'phone'              => '+60 12-345 6789',
            'qualification'      => 'Bachelor of Science (Physics)',
            'experience_years'   => 4,
            'preferred_subjects' => 'Physics, Science',
            'status'             => 'approved',
        ]);

        return [$user, $teacher, $application];
    }

    public function test_guest_cannot_access_teacher_profile(): void
    {
        $response = $this->get(route('teacher.profile'));
        $response->assertRedirect(route('login'));
    }

    public function test_student_cannot_access_teacher_profile(): void
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        $response = $this->actingAs($studentUser)->get(route('teacher.profile'));
        $response->assertStatus(403);
    }

    public function test_teacher_can_view_profile_page(): void
    {
        [$user, $teacher, $application] = $this->createTeacherUser();

        $response = $this->actingAs($user)->get(route('teacher.profile'));

        $response->assertStatus(200);
        $response->assertSee('Credentials &amp; Documents', false);
        $response->assertSee('Resume / Curriculum Vitae (CV)');
        $response->assertSee('Highest Academic Qualification Certificate');
        $response->assertSee('Academic Transcript');
        $response->assertSee('Additional Supporting Document');
        $response->assertSee($teacher->qualification);
    }

    public function test_teacher_can_upload_and_replace_documents(): void
    {
        Storage::fake('public');
        [$user, $teacher, $application] = $this->createTeacherUser();

        // 1. Upload Resume
        $resumeFile = UploadedFile::fake()->create('updated_cv.pdf', 400, 'application/pdf');
        $response = $this->actingAs($user)->post(route('teacher.profile.documents.upload'), [
            'document_type' => 'resume',
            'file'          => $resumeFile,
        ]);

        $response->assertRedirect(route('teacher.profile', ['tab' => 'documents']));
        $response->assertSessionHas('success');

        $application->refresh();
        $this->assertNotNull($application->resume_path);
        Storage::disk('public')->assertExists($application->resume_path);

        // 2. Upload Certificate
        $certFile = UploadedFile::fake()->image('degree_cert.jpg');
        $response = $this->actingAs($user)->post(route('teacher.profile.documents.upload'), [
            'document_type' => 'certificate',
            'file'          => $certFile,
        ]);

        $response->assertRedirect(route('teacher.profile', ['tab' => 'documents']));
        $application->refresh();
        $this->assertNotNull($application->certificate_path);
        Storage::disk('public')->assertExists($application->certificate_path);
    }

    public function test_teacher_can_view_and_download_their_document(): void
    {
        Storage::fake('public');
        [$user, $teacher, $application] = $this->createTeacherUser();

        $file = UploadedFile::fake()->create('my_cv.pdf', 300, 'application/pdf');
        $stored = $file->store('teacher_applications/resumes', 'public');
        $application->update(['resume_path' => $stored]);

        // View inline
        $viewResponse = $this->actingAs($user)->get(route('teacher.profile.documents.view', ['type' => 'resume']));
        $viewResponse->assertStatus(200);
        $viewResponse->assertHeader('Content-Type', 'application/pdf');

        // Download
        $downloadResponse = $this->actingAs($user)->get(route('teacher.profile.documents.download', ['type' => 'resume']));
        $downloadResponse->assertStatus(200);
        $downloadResponse->assertHeader('Content-Disposition');
    }

    public function test_teacher_can_delete_optional_document(): void
    {
        Storage::fake('public');
        [$user, $teacher, $application] = $this->createTeacherUser();

        $file = UploadedFile::fake()->image('cert.png');
        $stored = $file->store('teacher_applications/certificates', 'public');
        $application->update(['certificate_path' => $stored]);

        $response = $this->actingAs($user)->delete(route('teacher.profile.documents.delete', ['type' => 'certificate']));
        $response->assertRedirect(route('teacher.profile', ['tab' => 'documents']));

        $application->refresh();
        $this->assertNull($application->certificate_path);
    }

    public function test_teacher_can_update_profile_info(): void
    {
        [$user, $teacher, $application] = $this->createTeacherUser();

        $response = $this->actingAs($user)->put(route('teacher.profile.update'), [
            'name'             => 'Cikgu Siti Aminah',
            'phone'            => '+60 19-987 6543',
            'qualification'    => 'Master of Education (Curriculum & Instruction)',
            'experience_years' => 6,
            'specialization'   => 'Pure Mathematics & Statistics',
            'teaching_style'   => 'Visual & Real-world Applications',
            'bio'              => 'Dedicated mathematics teacher with 6 years experience in SPM excellence.',
            'address_line_1'   => 'No 15, Jalan Melati 3',
            'city'             => 'Shah Alam',
            'state'            => 'Selangor',
            'postcode'         => '40000',
        ]);

        $response->assertRedirect(route('teacher.profile', ['tab' => 'profile']));
        $response->assertSessionHas('success');

        $user->refresh();
        $teacher->refresh();
        $application->refresh();

        $this->assertEquals('CIKGU SITI AMINAH', $user->name);
        $this->assertEquals('+60 19-987 6543', $user->phone);
        $this->assertEquals('Master of Education (Curriculum & Instruction)', $teacher->qualification);
        $this->assertEquals(6, $teacher->experience_years);
        $this->assertEquals('Master of Education (Curriculum & Instruction)', $application->qualification);
    }

    public function test_teacher_can_update_password(): void
    {
        [$user, $teacher, $application] = $this->createTeacherUser();

        $response = $this->actingAs($user)->put(route('teacher.profile.password'), [
            'current_password'      => 'Password123!',
            'password'              => 'NewSecurePass2026!',
            'password_confirmation' => 'NewSecurePass2026!',
        ]);

        $response->assertRedirect(route('teacher.profile', ['tab' => 'security']));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('NewSecurePass2026!', $user->password));
    }
}
