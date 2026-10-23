<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\StudentAssignment;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPersonalTaskUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_personal_task_and_redirect_to_student_directory(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'role' => 'student',
            'name' => 'Muhammad Hariz',
        ]);

        $student = \App\Models\Student::create([
            'user_id' => $user->id,
            'level'   => 'SPM',
        ]);

        $subject = Subject::create([
            'name' => 'Mathematics',
            'slug' => 'mathematics',
            'code' => 'MATH-SPM',
            'category' => 'Core',
        ]);

        $course = Course::create([
            'subject_id' => $subject->id,
            'title' => 'SPM Mathematics Intensive',
            'level' => 'SPM',
            'grade' => 'Form 5',
            'is_active' => true,
        ]);

        $file = UploadedFile::fake()->create('remedial_notes.pdf', 500, 'application/pdf');

        $response = $this->actingAs($admin)->post(route('admin.tasks.store'), [
            'student_id'         => $student->id,
            'redirect_tab'       => '#student',
            'title'              => 'SPM Math Remedial Notes',
            'type'               => 'Remedial Notes',
            'course_id'          => $course->id,
            'total_marks'        => 100,
            'is_interactive_pdf' => '1',
            'instructions'       => 'Please complete all exercises on page 2.',
            'file'               => $file,
        ]);

        $response->assertRedirect(route('admin.dashboard') . '#student');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('assignments', [
            'title'              => 'SPM Math Remedial Notes',
            'type'               => 'Remedial Notes',
            'course_id'          => $course->id,
            'total_marks'        => 100,
            'is_interactive_pdf' => 1,
        ]);

        $assignment = Assignment::where('title', 'SPM Math Remedial Notes')->first();
        $this->assertNotNull($assignment);
        $this->assertNotNull($assignment->file_path);
        Storage::disk('public')->assertExists($assignment->file_path);

        $this->assertDatabaseHas('student_assignments', [
            'student_id'    => $student->id,
            'assignment_id' => $assignment->id,
            'status'        => 'pending',
        ]);
    }

    public function test_admin_can_upload_personal_task_and_redirect_to_diagnostic_roster(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'role' => 'student',
            'name' => 'Ahmad Faiz',
        ]);

        $student = \App\Models\Student::create([
            'user_id' => $user->id,
            'level'   => 'Form 4',
        ]);

        $file = UploadedFile::fake()->create('diagnostic_notes.docx', 300, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $response = $this->actingAs($admin)->post(route('admin.tasks.store'), [
            'student_id'   => $student->id,
            'redirect_tab' => '#students',
            'title'        => 'Diagnostic Follow-Up Notes',
            'type'         => 'Assignment',
            'due_date'     => now()->addDays(5)->toDateString(),
            'total_marks'  => 80,
            'instructions' => 'Read and answer the questions.',
            'file'         => $file,
        ]);

        $response->assertRedirect(route('admin.dashboard') . '#students');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('assignments', [
            'title'       => 'Diagnostic Follow-Up Notes',
            'type'        => 'Assignment',
            'total_marks' => 80,
        ]);

        $this->assertDatabaseHas('student_assignments', [
            'student_id' => $student->id,
            'status'     => 'pending',
        ]);
    }
}
