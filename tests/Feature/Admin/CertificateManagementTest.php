<?php

namespace Tests\Feature\Admin;

use App\Models\Certificate;
use App\Models\Student;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CertificateManagementTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_admin_can_issue_certificate_and_student_and_parent_are_notified(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $parent = User::factory()->create(['role' => 'parent']);
        $student = Student::factory()->create(['parent_user_id' => $parent->id]);

        $response = $this->actingAs($admin)->post(route('admin.certificates.store'), [
            'student_id' => $student->id,
            'title'      => 'Certificate of Excellence',
            'file'       => UploadedFile::fake()->create('cert.pdf', 100, 'application/pdf'),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('certificates', [
            'student_id' => $student->id,
            'title'      => 'Certificate of Excellence',
        ]);

        $certificate = Certificate::first();
        $this->assertNotNull($certificate->cert_number);
        $this->assertNotNull($certificate->file_path);
        Storage::disk('public')->assertExists($certificate->file_path);

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $student->user_id,
            'type'    => 'certificate',
            'title'   => 'NEW CERTIFICATE AWARDED',
        ]);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $parent->id,
            'type'    => 'certificate',
            'title'   => 'STUDENT CERTIFICATE AWARDED',
        ]);
    }

    public function test_duplicate_cert_number_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = Student::factory()->create();
        Certificate::create([
            'student_id'  => $student->id,
            'title'       => 'Existing',
            'cert_number' => 'OSC-DUPLICATE',
            'issued_at'   => now(),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.certificates.store'), [
            'student_id'  => $student->id,
            'title'       => 'New Certificate',
            'cert_number' => 'OSC-DUPLICATE',
        ]);

        $response->assertSessionHasErrors('cert_number');
        $this->assertSame(1, Certificate::count());
    }

    public function test_admin_can_delete_certificate_and_its_file(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $student = Student::factory()->create();
        $filePath = UploadedFile::fake()->create('cert.pdf', 50)->store('certificates', 'public');

        $certificate = Certificate::create([
            'student_id'  => $student->id,
            'title'       => 'To Be Removed',
            'cert_number' => 'OSC-TO-REMOVE',
            'file_path'   => $filePath,
            'issued_at'   => now(),
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.certificates.destroy', $certificate->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('certificates', ['id' => $certificate->id]);
        Storage::disk('public')->assertMissing($filePath);
    }
}
