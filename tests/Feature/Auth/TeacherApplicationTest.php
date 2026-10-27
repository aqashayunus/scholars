<?php

namespace Tests\Feature\Auth;

use App\Mail\WelcomeMail;
use App\Models\TeacherApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeacherApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_application_screen_renders_with_document_fields_and_ic(): void
    {
        $response = $this->get(route('register.teacher'));

        $response->assertStatus(200);
        $response->assertSee('IC / Passport Number', false);
        $response->assertSee('Resume / Curriculum Vitae (CV)', false);
        $response->assertSee('Highest Academic Qualification Certificate', false);
        $response->assertSee('Academic Transcript', false);
        $response->assertSee('Additional Document', false);
        $response->assertSee('enctype="multipart/form-data"', false);
    }

    public function test_teacher_application_fails_without_required_ic_number(): void
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
        $response->assertSessionHasErrors(['ic_number']);
        $this->assertDatabaseMissing('teacher_applications', [
            'email' => 'sarah_teacher@example.com',
        ]);
    }

    public function test_teacher_application_fails_without_required_resume(): void
    {
        $response = $this->from(route('register.teacher'))->post(route('register.teacher.submit'), [
            'full_name'          => 'Cikgu Sarah',
            'email'              => 'sarah_teacher@example.com',
            'phone'              => '+60 12-345 6789',
            'ic_number'          => '950812-14-5678',
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
            'ic_number'          => '950812-14-5678',
            'qualification'      => 'Bachelor of Science (Mathematics)',
            'experience_years'   => 4,
            'preferred_subjects' => 'Additional Mathematics, Modern Mathematics',
            'teaching_level'     => ['Upper Secondary (Form 4–5)'],
            'resume'             => $resume,
        ]);

        $response->assertRedirect(route('register.teacher.success'));

        $application = TeacherApplication::where('email', 'sarah_teacher@example.com')->first();
        $this->assertNotNull($application);
        $this->assertEquals('950812-14-5678', $application->ic_number);
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
            'ic_number'           => '880102-10-1234',
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
        $this->assertEquals('880102-10-1234', $application->ic_number);
        $this->assertNotNull($application->resume_path);
        $this->assertNotNull($application->certificate_path);
        $this->assertNotNull($application->transcript_path);
        $this->assertNotNull($application->additional_document_path);

        Storage::disk('public')->assertExists($application->resume_path);
        Storage::disk('public')->assertExists($application->certificate_path);
        Storage::disk('public')->assertExists($application->transcript_path);
        Storage::disk('public')->assertExists($application->additional_document_path);
    }

    public function test_admin_approving_teacher_application_generates_personalized_password_from_name_and_ic(): void
    {
        Mail::fake();

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        // Aisyah, IC 950812-14-5678 -> First 2 uppercase letters 'AI' + Last 6 digits '145678' = AI145678
        $application = TeacherApplication::create([
            'full_name'          => 'Aisyah binti Rahman',
            'email'              => 'aisyah@example.com',
            'phone'              => '+60 12-345 6789',
            'ic_number'          => '950812-14-5678',
            'qualification'      => 'Bachelor of Education',
            'experience_years'   => 5,
            'preferred_subjects' => 'Science',
            'status'             => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.teacher-applications.approve', $application->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user = User::where('email', 'aisyah@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('teacher', $user->role);
        $this->assertTrue(Hash::check('AI145678', $user->password));
        $this->assertTrue($user->matchesIc('950812-14-5678'));

        $application->refresh();
        $this->assertEquals('approved', $application->status);
        $this->assertEquals($user->id, $application->user_id);

        $sentMailable = Mail::sent(\App\Mail\OfficialBrandedMail::class, function ($mail) {
            return $mail->hasTo('aisyah@example.com');
        })->count() > 0 || Mail::sent(WelcomeMail::class, function ($mail) {
            return $mail->hasTo('aisyah@example.com');
        })->count() > 0;

        $this->assertTrue($sentMailable, 'Neither OfficialBrandedMail nor WelcomeMail was sent to the approved educator.');
    }
}
