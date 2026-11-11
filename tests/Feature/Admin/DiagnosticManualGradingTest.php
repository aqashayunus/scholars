<?php

namespace Tests\Feature\Admin;

use App\Enums\StudentAssignmentStatus;
use App\Models\Assignment;
use App\Models\Student;
use App\Models\StudentAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiagnosticManualGradingTest extends TestCase
{
    use RefreshDatabase;

    protected function makeSubmittedDiagnostic(): StudentAssignment
    {
        $studentUser = User::factory()->create(['role' => 'student', 'is_active' => true]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'parent_id' => null,
            'level' => 'Form 4',
            'birth_date' => '2008-01-01',
            'school' => 'SMK Olympian',
            'gender' => 'Male',
        ]);

        $assignment = Assignment::create([
            'title' => 'Diagnostic With Essay Question',
            'type' => 'Diagnostic Test',
            'time_limit_minutes' => 30,
            'total_questions' => 2,
            'is_global' => false,
        ]);

        return StudentAssignment::create([
            'student_id' => $student->id,
            'assignment_id' => $assignment->id,
            'status' => StudentAssignmentStatus::PendingReview,
            'score' => 20,
            'answers_json' => [
                [
                    'section' => 'A',
                    'type' => 'mcq',
                    'question_number' => 1,
                    'title' => 'Question 1',
                    'student_choice' => 'A',
                    'correct_option' => 'A',
                    'is_correct' => true,
                    'is_partial' => false,
                    'is_manual' => false,
                    'score' => 1.0,
                    'max_marks' => 1.0,
                ],
                [
                    'section' => 'B',
                    'type' => 'fill_in_blank',
                    'question_number' => 2,
                    'title' => 'Question 2 (essay)',
                    'student_choice' => 'A long essay answer.',
                    'correct_option' => '(Manual review)',
                    'is_correct' => false,
                    'is_partial' => false,
                    'is_manual' => true,
                    'score' => 0.0,
                    'max_marks' => 4.0,
                ],
            ],
        ]);
    }

    public function test_admin_can_save_manual_grade_and_status_flips_to_graded(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $studentAssignment = $this->makeSubmittedDiagnostic();

        $response = $this->actingAs($admin)->postJson(route('admin.diagnostic.grade', $studentAssignment->id), [
            'scores' => ['1' => 1.0, '2' => 3.0],
            'teacher_feedback' => 'Good structure, minor gaps.',
            'status' => 'graded',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true, 'status' => 'graded']);

        $studentAssignment->refresh();
        $this->assertEquals(StudentAssignmentStatus::Graded, $studentAssignment->status);
        $this->assertEquals(80, $studentAssignment->score); // (1 + 3) / 5 = 80%
        $this->assertEquals('Good structure, minor gaps.', $studentAssignment->teacher_feedback);
    }

    public function test_manual_grade_defaults_to_pending_review_when_a_manual_question_is_left_unscored(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $studentAssignment = $this->makeSubmittedDiagnostic();

        $response = $this->actingAs($admin)->postJson(route('admin.diagnostic.grade', $studentAssignment->id), [
            'scores' => ['1' => 1.0], // question 2 (is_manual) left unscored
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true, 'status' => 'pending_review']);

        $studentAssignment->refresh();
        $this->assertEquals(StudentAssignmentStatus::PendingReview, $studentAssignment->status);
    }

    public function test_non_admin_cannot_save_manual_grade(): void
    {
        $studentAssignment = $this->makeSubmittedDiagnostic();
        $teacher = User::factory()->create(['role' => 'teacher']);

        $response = $this->actingAs($teacher)->postJson(route('admin.diagnostic.grade', $studentAssignment->id), [
            'scores' => ['1' => 1.0, '2' => 3.0],
        ]);

        $response->assertStatus(403);
    }

    public function test_guest_cannot_save_manual_grade(): void
    {
        $studentAssignment = $this->makeSubmittedDiagnostic();

        $response = $this->postJson(route('admin.diagnostic.grade', $studentAssignment->id), [
            'scores' => ['1' => 1.0, '2' => 3.0],
        ]);

        $response->assertStatus(401);
    }
}
