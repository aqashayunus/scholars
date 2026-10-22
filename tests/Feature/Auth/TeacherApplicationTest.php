<?php

namespace Tests\Feature\Auth;

use App\Models\TeacherApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeacherApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_application_screen_renders_with_document_fields(): void
    {
        $response = $this->get(route('register.teacher'));

        $response->assertStatus(200);
        $response->assertSee('Resume / Curriculum Vitae (CV)', false);
        $response->assertSee('Highest Academic Qualification Certificate', false);
        $response->assertSee('Academic Transcript', false);
        $response->assertSee('Additional Document', false);
        $response->assertSee('enctype="multipart/form-data"', false);
    }

    public function test_teacher_application_fails_without_required_resume(): void
    {
        $response = $this->from(route('register.teacher'))->post(route('register.teacher.submit'), [
            'full_name'          => 'Cikgu Sarah',
            'email'              => 'sarah_teacher@example.com',
            'phone'              => '+60 12-345 6789',
            'qualification'      => 'Bachelor of Science (Mathematics)',
            'experience_years'   => 4,
            'preferred_subjects' => 'Additional Mathematics, Modern Mathematics',
            'teaching_level'     => ['Upper Secondary (Form 4–5)'],
        ]);

        $response->assertRedirect(route('register.teacher'));
        $response->assertSessionHasErrors(['resume']);
        $this->assertDatabaseMissing('teacher_applications', [
            'email' => 'sarah_teacher@example.com',
        ]);
    }

    public function test_teacher_application_succeeds_with_required_resume(): void
    {
        Storage::fake('public');

        $resume = UploadedFile::fake()->create('sarah_cv.pdf', 500, 'application/pdf');

        $response = $this->post(route('register.teacher.submit'), [
            'full_name'          => 'Cikgu Sarah',
            'email'              => 'sarah_teacher@example.com',
            'phone'              => '+60 12-345 6789',
            'qualification'      => 'Bachelor of Science (Mathematics)',
            'experience_years'   => 4,
            'preferred_subjects' => 'Additional Mathematics, Modern Mathematics',
            'teaching_level'     => ['Upper Secondary (Form 4–5)'],
            'resume'             => $resume,
        ]);

        $response->assertRedirect(route('register.teacher.success'));

        $application = TeacherApplication::where('email', 'sarah_teacher@example.com')->first();
        $this->assertNotNull($application);
        $this->assertNotNull($application->resume_path);
        Storage::disk('public')->assertExists($application->resume_path);
    }

    public function test_teacher_application_succeeds_with_all_optional_documents(): void
    {
        Storage::fake('public');

        $resume      = UploadedFile::fake()->create('farhan_cv.docx', 800, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $certificate = UploadedFile::fake()->image('farhan_degree.jpg');
        $transcript  = UploadedFile::fake()->create('farhan_transcript.pdf', 1200, 'application/pdf');
        $additional  = UploadedFile::fake()->create('teaching_license.pdf', 300, 'application/pdf');

        $response = $this->post(route('register.teacher.submit'), [
            'full_name'           => 'Ustaz Farhan',
            'email'               => 'farhan@example.com',
            'phone'               => '+60 17-987 6543',
            'institution'         => 'SMK Damansara Utama',
            'qualification'       => 'Master in Education (Physics)',
            'experience_years'    => 6,
            'preferred_subjects'  => 'Physics, Science',
            'teaching_level'      => ['Lower Secondary (Form 1–3)', 'Upper Secondary (Form 4–5)'],
            'resume'              => $resume,
            'certificate'         => $certificate,
            'transcript'          => $transcript,
            'additional_document' => $additional,
        ]);

        $response->assertRedirect(route('register.teacher.success'));

        $application = TeacherApplication::where('email', 'farhan@example.com')->first();
        $this->assertNotNull($application);
        $this->assertNotNull($application->resume_path);
        $this->assertNotNull($application->certificate_path);
        $this->assertNotNull($application->transcript_path);
        $this->assertNotNull($application->additional_document_path);

        Storage::disk('public')->assertExists($application->resume_path);
        Storage::disk('public')->assertExists($application->certificate_path);
        Storage::disk('public')->assertExists($application->transcript_path);
        Storage::disk('public')->assertExists($application->additional_document_path);
    }
}
