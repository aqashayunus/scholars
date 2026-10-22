<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\StudentAssignment;
use App\Models\StudentAssignmentSubmission;
use App\Models\SubmissionPage;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InteractiveAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $teacherUser;
    private Teacher $teacher;
    private User $studentUserA;
    private Student $studentA;
    private User $studentUserB;
    private Student $studentB;
    private Subject $subject;
    private Course $course;
    private Assignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        // 1. Admin
        $this->adminUser = User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@olympia.test',
            'role'     => 'admin',
            'password' => Hash::make('password'),
        ]);

        // 2. Teacher
        $this->teacherUser = User::create([
            'name'     => 'Cikgu Rahman',
            'email'    => 'rahman@teacher.test',
            'role'     => 'teacher',
            'password' => Hash::make('password'),
        ]);
        $this->teacher = Teacher::create([
            'user_id'            => $this->teacherUser->id,
            'application_status' => 'active',
            'qualification'      => 'Master of Mathematics',
        ]);

        // 3. Student A
        $this->studentUserA = User::create([
            'name'     => 'Student A',
            'email'    => 'studenta@olympia.test',
            'role'     => 'student',
            'password' => Hash::make('password'),
        ]);
        $this->studentA = Student::create([
            'user_id' => $this->studentUserA->id,
            'level'   => 'Form 4',
            'status'  => 'active',
        ]);

        // 4. Student B
        $this->studentUserB = User::create([
            'name'     => 'Student B',
            'email'    => 'studentb@olympia.test',
            'role'     => 'student',
            'password' => Hash::make('password'),
        ]);
        $this->studentB = Student::create([
            'user_id' => $this->studentUserB->id,
            'level'   => 'Form 4',
            'status'  => 'active',
        ]);

        // 5. Subject & Course
        $this->subject = Subject::create([
            'name' => 'Additional Mathematics',
            'slug' => 'add-math',
            'code' => 'ADM',
        ]);
        $this->course = Course::create([
            'title'      => 'Add Math Form 4 Intensive',
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'level'      => 'Form 4',
            'price'      => 120,
        ]);

        // Enroll Student A in Course
        Enrollment::create([
            'student_id' => $this->studentA->id,
            'course_id'  => $this->course->id,
            'status'     => 'active',
        ]);

        // 6. Assignment with PDF file
        $pdfContent = "%PDF-1.4\n1 0 obj\n<<\n/Type /Catalog\n/Pages 2 0 R\n>>\nendobj\ntrailer\n<<\n/Root 1 0 R\n>>\n%%EOF";
        $pdfPath = 'assignments/test_assignment.pdf';
        Storage::disk('public')->put($pdfPath, $pdfContent);

        $this->assignment = Assignment::create([
            'course_id'          => $this->course->id,
            'title'              => 'Quadratic Equations Exercise 01',
            'type'               => 'Assignment',
            'total_marks'        => 100,
            'due_date'           => now()->addDays(7)->toDateString(),
            'file_path'          => $pdfPath,
            'file_name'          => 'test_assignment.pdf',
            'file_size'          => strlen($pdfContent),
            'is_interactive_pdf' => true,
        ]);
    }

    public function test_student_can_open_interactive_viewer_for_enrolled_assignment(): void
    {
        $response = $this->actingAs($this->studentUserA)
            ->get(route('student.assignments.interactive', $this->assignment->id));

        $response->assertStatus(200);
        $response->assertSee('Quadratic Equations Exercise 01');

        $this->assertDatabaseHas('student_assignments', [
            'student_id'    => $this->studentA->id,
            'assignment_id' => $this->assignment->id,
        ]);

        $this->assertDatabaseHas('student_assignment_submissions', [
            'assignment_id' => $this->assignment->id,
            'student_id'    => $this->studentA->id,
            'status'        => 'draft',
        ]);
    }

    public function test_student_cannot_access_another_students_submission(): void
    {
        // Student A opens submission
        $this->actingAs($this->studentUserA)
            ->get(route('student.assignments.interactive', $this->assignment->id));

        $submissionA = StudentAssignmentSubmission::where('student_id', $this->studentA->id)->firstOrFail();

        // Student B tries to inspect Student A's submission state directly
        $response = $this->actingAs($this->studentUserB)
            ->get(route('student.submissions.init', $submissionA->id));

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get(route('student.assignments.interactive', $this->assignment->id));
        $response->assertRedirect('/login');
    }

    public function test_teacher_can_review_interactive_submission(): void
    {
        // Student A creates draft
        $sa = StudentAssignment::create([
            'student_id'    => $this->studentA->id,
            'assignment_id' => $this->assignment->id,
            'status'        => 'submitted',
            'submitted_at'  => now(),
        ]);

        $sub = StudentAssignmentSubmission::create([
            'student_assignment_id' => $sa->id,
            'assignment_id'         => $this->assignment->id,
            'student_id'            => $this->studentA->id,
            'status'                => 'submitted',
            'submitted_at'          => now(),
        ]);

        $response = $this->actingAs($this->teacherUser)
            ->get(route('teacher.submissions.review', $sub->id));

        $response->assertStatus(200);
        $response->assertSee('Quadratic Equations Exercise 01');
    }

    public function test_teacher_cannot_review_submission_from_unrelated_course(): void
    {
        // Unrelated teacher
        $otherTeacherUser = User::create([
            'name'     => 'Cikgu Siti',
            'email'    => 'siti@teacher.test',
            'role'     => 'teacher',
            'password' => Hash::make('password'),
        ]);
        $otherTeacher = Teacher::create([
            'user_id'            => $otherTeacherUser->id,
            'application_status' => 'active',
            'qualification'      => 'Bachelor of Education',
        ]);

        $sa = StudentAssignment::create([
            'student_id'    => $this->studentA->id,
            'assignment_id' => $this->assignment->id,
            'status'        => 'submitted',
        ]);
        $sub = StudentAssignmentSubmission::create([
            'student_assignment_id' => $sa->id,
            'assignment_id'         => $this->assignment->id,
            'student_id'            => $this->studentA->id,
            'status'                => 'submitted',
        ]);

        $response = $this->actingAs($otherTeacherUser)
            ->get(route('teacher.submissions.review', $sub->id));

        $response->assertStatus(403);
    }

    public function test_admin_can_review_any_interactive_submission(): void
    {
        $sa = StudentAssignment::create([
            'student_id'    => $this->studentA->id,
            'assignment_id' => $this->assignment->id,
            'status'        => 'submitted',
        ]);
        $sub = StudentAssignmentSubmission::create([
            'student_assignment_id' => $sa->id,
            'assignment_id'         => $this->assignment->id,
            'student_id'            => $this->studentA->id,
            'status'                => 'submitted',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.submissions.review', $sub->id));

        $response->assertStatus(200);
        $response->assertSee('Quadratic Equations Exercise 01');
    }

    public function test_student_can_save_page_annotation_draft(): void
    {
        $this->actingAs($this->studentUserA)
            ->get(route('student.assignments.interactive', $this->assignment->id));

        $sub = StudentAssignmentSubmission::where('student_id', $this->studentA->id)->firstOrFail();

        $payload = [
            'annotation_json' => json_encode([
                'version' => 1,
                'strokes' => [
                    [
                        'id'     => 'stroke-1',
                        'tool'   => 'pen',
                        'color'  => '#111827',
                        'width'  => 3,
                        'points' => [[0.1, 0.2, 0.5], [0.15, 0.25, 0.5]],
                    ],
                ],
            ]),
            'stroke_count' => 1,
            'current_zoom' => 1.25,
            'total_pages'  => 5,
        ];

        $response = $this->actingAs($this->studentUserA)
            ->putJson(route('student.submissions.pages.save', ['submission' => $sub->id, 'pageNumber' => 1]), $payload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'page_number' => 1]);

        $this->assertDatabaseHas('submission_pages', [
            'submission_id' => $sub->id,
            'page_number'   => 1,
            'stroke_count'  => 1,
        ]);
    }

    public function test_malformed_annotation_payload_is_rejected(): void
    {
        $this->actingAs($this->studentUserA)
            ->get(route('student.assignments.interactive', $this->assignment->id));

        $sub = StudentAssignmentSubmission::where('student_id', $this->studentA->id)->firstOrFail();

        $response = $this->actingAs($this->studentUserA)
            ->putJson(route('student.submissions.pages.save', ['submission' => $sub->id, 'pageNumber' => 1]), [
                'annotation_json' => 'not-valid-json',
            ]);

        $response->assertStatus(422);
    }

    public function test_student_can_submit_assignment_and_it_locks(): void
    {
        $this->actingAs($this->studentUserA)
            ->get(route('student.assignments.interactive', $this->assignment->id));

        $sub = StudentAssignmentSubmission::where('student_id', $this->studentA->id)->firstOrFail();

        $fakePdf = UploadedFile::fake()->create('submission_final.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->studentUserA)
            ->postJson(route('student.submissions.submit', $sub->id), [
                'final_pdf' => $fakePdf,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $sub->refresh();
        $this->assertEquals('submitted', $sub->status);
        $this->assertNotNull($sub->submitted_at);
        $this->assertTrue($sub->isLocked());

        // Verify parent StudentAssignment is also marked submitted
        $sa = StudentAssignment::where('student_id', $this->studentA->id)
            ->where('assignment_id', $this->assignment->id)
            ->firstOrFail();

        $this->assertEquals('submitted', $sa->status);
    }

    public function test_submitted_assignment_rejects_further_page_saves(): void
    {
        $this->actingAs($this->studentUserA)
            ->get(route('student.assignments.interactive', $this->assignment->id));

        $sub = StudentAssignmentSubmission::where('student_id', $this->studentA->id)->firstOrFail();

        // Submit to lock it
        $this->actingAs($this->studentUserA)
            ->postJson(route('student.submissions.submit', $sub->id));

        // Attempt to save new strokes
        $response = $this->actingAs($this->studentUserA)
            ->putJson(route('student.submissions.pages.save', ['submission' => $sub->id, 'pageNumber' => 1]), [
                'annotation_json' => json_encode(['strokes' => []]),
            ]);

        $response->assertStatus(403);
    }

    public function test_teacher_can_upload_graded_task_and_authorized_users_can_download(): void
    {
        // 1. Create a StudentAssignment for Student A
        $sa = StudentAssignment::firstOrCreate(
            ['assignment_id' => $this->assignment->id, 'student_id' => $this->studentA->id],
            ['status' => 'submitted', 'submitted_at' => now()]
        );

        $fakeGradedPdf = UploadedFile::fake()->create('corrected_exam_a.pdf', 300, 'application/pdf');

        // 2. Teacher grades the assignment and uploads graded file
        $response = $this->actingAs($this->teacherUser)
            ->post(route('teacher.tasks.grade', ['assignmentId' => $this->assignment->id, 'studentId' => $this->studentA->id]), [
                'score'            => 92,
                'status'           => 'graded',
                'teacher_feedback' => 'Excellent reasoning in step 3!',
                'graded_file'      => $fakeGradedPdf,
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // 3. Verify StudentAssignment updated in database
        $sa->refresh();
        $this->assertEquals(92, $sa->score);
        $this->assertEquals('graded', $sa->status);
        $this->assertEquals('Excellent reasoning in step 3!', $sa->teacher_feedback);
        $this->assertNotNull($sa->graded_file_path);
        $this->assertEquals('corrected_exam_a.pdf', $sa->graded_file_name);
        Storage::disk('public')->assertExists($sa->graded_file_path);

        // 4. Student A (owner) can download their graded file
        $downloadResponse = $this->actingAs($this->studentUserA)
            ->get(route('student-assignments.download-graded', $sa->id));

        $downloadResponse->assertStatus(200);

        // 5. Student B (unauthorized) cannot download Student A's graded file
        $forbiddenResponse = $this->actingAs($this->studentUserB)
            ->get(route('student-assignments.download-graded', $sa->id));

        $forbiddenResponse->assertStatus(403);

        // 6. Teacher can download the graded file
        $teacherDownload = $this->actingAs($this->teacherUser)
            ->get(route('student-assignments.download-graded', $sa->id));

        $teacherDownload->assertStatus(200);

        // 7. Admin can download the graded file
        $adminDownload = $this->actingAs($this->adminUser)
            ->get(route('student-assignments.download-graded', $sa->id));

        $adminDownload->assertStatus(200);
    }

    public function test_admin_can_upload_graded_task_and_receive_json_response(): void
    {
        $sa = StudentAssignment::firstOrCreate(
            ['assignment_id' => $this->assignment->id, 'student_id' => $this->studentA->id],
            ['status' => 'submitted', 'submitted_at' => now()]
        );

        $fakeGradedPdf = UploadedFile::fake()->create('admin_graded_notes.pdf', 400, 'application/pdf');

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.tasks.grade', ['assignmentId' => $this->assignment->id, 'studentId' => $this->studentA->id]), [
                'score'            => 98,
                'teacher_feedback' => 'Flawless work across all sections.',
                'graded_file'      => $fakeGradedPdf,
            ], ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertStatus(200);
        $response->assertJson([
            'success'          => true,
            'graded_file_name' => 'admin_graded_notes.pdf',
        ]);

        $sa->refresh();
        $this->assertEquals(98, $sa->score);
        $this->assertEquals('graded', $sa->status);
        $this->assertEquals('admin_graded_notes.pdf', $sa->graded_file_name);
        Storage::disk('public')->assertExists($sa->graded_file_path);
    }
}


