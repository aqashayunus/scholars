<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Student;
use App\Models\StudentAssignment;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Http\Controllers\Auth\StudentRegistrationController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubjectDiagnosticTest extends TestCase
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

    public function test_admin_can_attach_diagnostic_test_to_subject()
    {
        $subject = Subject::firstOrCreate(
            ['slug' => 'test-math-f4'],
            [
                'name'           => 'Test Mathematics Form 4',
                'education_type' => 'secondary',
                'stage'          => 'Upper Secondary',
                'form_year'      => 'Form 4',
                'category'       => 'Core',
                'code'           => 'TEST-MATH-F4',
                'is_active'      => true,
            ]
        );

        $diagnostic = Assignment::create([
            'title'              => 'Form 4 Mathematics Diagnostic Assessment',
            'type'               => 'Diagnostic Test',
            'time_limit_minutes' => 30,
            'total_questions'    => 20,
            'is_global'          => false,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.subjects.diagnostic.set', $subject->id), [
                'diagnostic_assignment_id'    => $diagnostic->id,
                'diagnostic_duration_minutes' => 20,
            ]);

        $response->assertRedirect(route('admin.dashboard') . '#courses-subjects');
        $subject->refresh();

        $this->assertEquals($diagnostic->id, $subject->diagnostic_assignment_id);
        $this->assertEquals(20, $subject->diagnostic_duration_minutes);
        $this->assertEquals(20, $subject->diagnostic_duration);
        $this->assertTrue($subject->hasDiagnosticTest());
    }

    public function test_admin_can_create_and_attach_new_diagnostic_test()
    {
        $subject = Subject::firstOrCreate(
            ['slug' => 'test-sci-f4'],
            [
                'name'           => 'Test Science Form 4',
                'education_type' => 'secondary',
                'stage'          => 'Upper Secondary',
                'form_year'      => 'Form 4',
                'category'       => 'Core',
                'code'           => 'TEST-SCI-F4',
                'is_active'      => true,
            ]
        );

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.subjects.diagnostic.create', $subject->id), [
                'title'                       => 'Diagnostic Assessment: Science (Form 4)',
                'total_questions'             => 25,
                'diagnostic_duration_minutes' => 20,
                'total_marks'                 => 25,
                'answer_key'                  => 'A,B,C,D,A',
            ]);

        $response->assertRedirect(route('admin.dashboard') . '#courses-subjects');
        $subject->refresh();

        $this->assertNotNull($subject->diagnostic_assignment_id);
        $this->assertEquals(20, $subject->diagnostic_duration);

        $createdAssignment = $subject->diagnosticTest;
        $this->assertNotNull($createdAssignment);
        $this->assertEquals('Diagnostic Assessment: Science (Form 4)', $createdAssignment->title);
        $this->assertEquals('Diagnostic Test', $createdAssignment->type);
        $this->assertEquals($subject->id, $createdAssignment->subject_id);
        $this->assertEquals(20, $createdAssignment->time_limit_minutes);
    }

    public function test_admin_can_detach_diagnostic_test_from_subject()
    {
        $diagnostic = Assignment::create([
            'title' => 'Form 5 English Diagnostic',
            'type'  => 'Diagnostic Test',
        ]);

        $subject = Subject::firstOrCreate(
            ['slug' => 'test-eng-f5'],
            [
                'name'                     => 'Test English Form 5',
                'education_type'           => 'secondary',
                'stage'                    => 'Upper Secondary',
                'form_year'                => 'Form 5',
                'category'                 => 'Core',
                'code'                     => 'TEST-ENG-F5',
                'is_active'                => true,
                'diagnostic_assignment_id' => $diagnostic->id,
            ]
        );

        $response = $this->actingAs($this->adminUser)
            ->delete(route('admin.subjects.diagnostic.detach', $subject->id));

        $response->assertRedirect(route('admin.dashboard') . '#courses-subjects');
        $subject->refresh();

        $this->assertNull($subject->diagnostic_assignment_id);
        $this->assertFalse($subject->hasDiagnosticTest());
        // Diagnostic test itself is preserved
        $this->assertDatabaseHas('assignments', ['id' => $diagnostic->id]);
    }

    public function test_student_enrollment_auto_assigns_subject_diagnostic_tests()
    {
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::create([
            'user_id'       => $teacherUser->id,
            'qualification' => 'Degree in Education',
        ]);

        $mathDiagnostic = Assignment::create([
            'title' => 'Mathematics Diagnostic',
            'type'  => 'Diagnostic Test',
        ]);

        $scienceDiagnostic = Assignment::create([
            'title' => 'Science Diagnostic',
            'type'  => 'Diagnostic Test',
        ]);

        $math = Subject::firstOrCreate(
            ['slug' => 'test-math-reg'],
            [
                'name'                     => 'Test Math Reg',
                'education_type'           => 'secondary',
                'stage'                    => 'Upper Secondary',
                'form_year'                => 'Form 4',
                'category'                 => 'Core',
                'code'                     => 'TMATH',
                'is_active'                => true,
                'diagnostic_assignment_id' => $mathDiagnostic->id,
            ]
        );

        $science = Subject::firstOrCreate(
            ['slug' => 'test-sci-reg'],
            [
                'name'                     => 'Test Sci Reg',
                'education_type'           => 'secondary',
                'stage'                    => 'Upper Secondary',
                'form_year'                => 'Form 4',
                'category'                 => 'Core',
                'code'                     => 'TSCI',
                'is_active'                => true,
                'diagnostic_assignment_id' => $scienceDiagnostic->id,
            ]
        );

        $studentUser = User::factory()->create(['role' => 'student']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'level'   => 'Form 4',
        ]);

        // Enroll student in both subjects
        StudentRegistrationController::enrollInSubjects(
            $student,
            ['Test Math Reg', 'Test Sci Reg'],
            'Form 4'
        );

        // Verify diagnostic tests were auto-assigned for both subjects
        $this->assertDatabaseHas('student_assignments', [
            'student_id'    => $student->id,
            'assignment_id' => $mathDiagnostic->id,
            'status'        => 'pending',
        ]);

        $this->assertDatabaseHas('student_assignments', [
            'student_id'    => $student->id,
            'assignment_id' => $scienceDiagnostic->id,
            'status'        => 'pending',
        ]);
    }

    public function test_admin_can_create_diagnostic_test_with_word_document()
    {
        Storage::fake('public');

        $subject = Subject::firstOrCreate(
            ['slug' => 'test-bm-f4'],
            [
                'name'           => 'Test Bahasa Melayu Form 4',
                'education_type' => 'secondary',
                'stage'          => 'Upper Secondary',
                'form_year'      => 'Form 4',
                'category'       => 'Core',
                'code'           => 'TEST-BM-F4',
                'is_active'      => true,
            ]
        );

        $wordDoc = UploadedFile::fake()->create('sample_diagnostic_worksheet.docx', 500, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.subjects.diagnostic.create', $subject->id), [
                'title'                       => 'Diagnostic Assessment: BM Worksheet',
                'total_questions'             => 10,
                'diagnostic_duration_minutes' => 30,
                'total_marks'                 => 100,
                'answer_key'                  => 'A,B,C,D',
                'file'                        => $wordDoc,
                'is_interactive_pdf'          => 1, // Accidentally checked interactive PDF with docx
            ]);

        $response->assertRedirect(route('admin.dashboard') . '#courses-subjects');
        $subject->refresh();

        $created = $subject->diagnosticTest;
        $this->assertNotNull($created);
        $this->assertEquals('Diagnostic Assessment: BM Worksheet', $created->title);
        $this->assertEquals('sample_diagnostic_worksheet.docx', $created->file_name);
        // Non-PDF must safely disable interactive PDF canvas mode
        $this->assertFalse((bool) $created->is_interactive_pdf);
    }

    public function test_student_can_save_and_resume_diagnostic_test_draft()
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'level'   => 'Form 4',
        ]);

        $diagnostic = Assignment::create([
            'title'              => 'Physics Diagnostic Assessment',
            'type'               => 'Diagnostic Test',
            'time_limit_minutes' => 30,
            'total_questions'    => 5,
            'is_global'          => false,
        ]);

        $sa = StudentAssignment::create([
            'student_id'    => $student->id,
            'assignment_id' => $diagnostic->id,
            'status'        => 'pending',
        ]);

        // Student sends autosave draft request
        $response = $this->actingAs($studentUser)
            ->postJson(route('student.diagnostic.save-draft', $diagnostic->id), [
                'answers'            => ['q1' => 'B', 'q2' => 'D', 'q3' => 'A'],
                'time_taken_seconds' => 140,
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Progress saved successfully.',
            ]);

        $sa->refresh();
        $this->assertEquals('pending', $sa->status);
        $this->assertEquals(140, $sa->time_taken_seconds);
        $this->assertIsArray($sa->answers_json);
        $this->assertEquals('B', $sa->answers_json['q1']);
        $this->assertEquals('D', $sa->answers_json['q2']);
        $this->assertEquals('A', $sa->answers_json['q3']);

        // Test take page provides draft answers
        $takeResponse = $this->actingAs($studentUser)
            ->get(route('student.diagnostic.take', $diagnostic->id));

        $takeResponse->assertOk();
        $takeResponse->assertViewHas('draftAnswers', function ($answers) {
            return isset($answers['q1']) && $answers['q1'] === 'B';
        });
    }

    public function test_diagnostic_pdf_displays_instantly_without_download_button()
    {
        Storage::fake('public');

        $studentUser = User::factory()->create(['role' => 'student']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'level'   => 'Form 1',
        ]);

        $dummyPdf = UploadedFile::fake()->create('form1_diagnostic.pdf', 100, 'application/pdf');
        $savedPath = $dummyPdf->store('assignments', 'public');

        $diagnostic = Assignment::create([
            'title'              => 'Diagnostic Assessment: Form 1 Mathematics',
            'type'               => 'Diagnostic Test',
            'topic'              => 'General Diagnostic',
            'target_level'       => 'Form 1',
            'time_limit_minutes' => 30,
            'total_questions'    => 10,
            'is_global'          => true,
            'file_path'          => $savedPath,
            'file_name'          => 'form1_diagnostic.pdf',
        ]);

        $takeResponse = $this->actingAs($studentUser)
            ->get(route('student.diagnostic.take', $diagnostic->id));

        $takeResponse->assertOk();
        $takeResponse->assertViewHas('hasPdfFile', true);
        $takeResponse->assertViewHas('documentType', 'pdf');
        $takeResponse->assertViewHas('pdfUrl');
        
        // Assert the student is NEVER given a download button
        $takeResponse->assertDontSee('Download Assessment File');
        $takeResponse->assertDontSee('Download the file below to view the questions');

        // Test stream endpoint returns inline header to prevent browser download prompt
        $streamResponse = $this->actingAs($studentUser)
            ->get(route('student.diagnostic.pdf', $diagnostic->id));

        $streamResponse->assertOk();
        $this->assertStringContainsString('inline', $streamResponse->headers->get('Content-Disposition') ?? '');
    }
}
