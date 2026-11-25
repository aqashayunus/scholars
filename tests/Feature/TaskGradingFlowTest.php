<?php

namespace Tests\Feature;

use App\Enums\StudentAssignmentStatus;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\StudentAssignment;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Teacher sets a task out of N marks -> student submits -> teacher grades:
 * the score is capped at N, the student sees "x / N", and a graded task
 * can no longer be resubmitted (which used to reset the grade).
 */
class TaskGradingFlowTest extends TestCase
{
    use RefreshDatabase;

    private Teacher $teacher;

    private User $studentUser;

    private Student $student;

    private Course $course;

    private Assignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = Teacher::factory()->create()->fresh();
        $this->studentUser = User::factory()->create(['role' => 'student']);
        $this->student = Student::factory()->create(['user_id' => $this->studentUser->id, 'status' => 'active']);
        $this->course = Course::factory()->create(['teacher_id' => $this->teacher->id]);
        Enrollment::create(['student_id' => $this->student->id, 'course_id' => $this->course->id, 'status' => 'active']);
        $this->assignment = $this->course->assignments()->create(['title' => 'Fractions worksheet', 'due_date' => now()->addWeek(), 'total_marks' => 20]);
    }

    private function grade(int $score, ?Student $student = null)
    {
        return $this->actingAs($this->teacher->user)
            ->from(route('teacher.tasks'))
            ->post(route('teacher.tasks.grade', [$this->assignment->id, ($student ?? $this->student)->id]), [
                'score' => $score,
                'status' => 'graded',
                'teacher_feedback' => 'Good work',
            ]);
    }

    private function submission(): StudentAssignment
    {
        return StudentAssignment::firstOrCreate(
            ['student_id' => $this->student->id, 'assignment_id' => $this->assignment->id],
            ['status' => 'pending'],
        );
    }

    public function test_score_above_the_tasks_total_marks_is_rejected(): void
    {
        $this->grade(25)->assertSessionHasErrors('score');

        $this->grade(15)->assertSessionHasNoErrors();
        $this->assertSame(15, (int) $this->submission()->fresh()->score);
    }

    public function test_teacher_cannot_grade_a_student_outside_the_class(): void
    {
        $outsider = Student::factory()->create();

        $this->grade(10, $outsider)->assertNotFound();
        $this->assertFalse(StudentAssignment::where('student_id', $outsider->id)->exists());
    }

    public function test_student_sees_score_out_of_the_tasks_total_marks(): void
    {
        $this->submission();
        $this->grade(15);

        $this->actingAs($this->studentUser)->get(route('student.tasks'))
            ->assertOk()
            ->assertSee('totalMarks: 20', false)
            ->assertSee('isGraded: true', false);
    }

    public function test_a_graded_task_cannot_be_resubmitted(): void
    {
        $sub = $this->submission();
        $this->grade(15);

        $this->actingAs($this->studentUser)
            ->from(route('student.tasks'))
            ->post(route('student.tasks.submit', $sub->id), ['submission_notes' => 'second try'])
            ->assertSessionHasErrors('submission');

        $sub->refresh();
        $this->assertSame(StudentAssignmentStatus::Graded, $sub->status);
        $this->assertSame(15, (int) $sub->score);
    }

    public function test_an_ungraded_task_can_still_be_resubmitted(): void
    {
        $sub = $this->submission();

        $this->actingAs($this->studentUser)
            ->post(route('student.tasks.submit', $sub->id), ['submission_notes' => 'first'])
            ->assertSessionHasNoErrors();
        $this->actingAs($this->studentUser)
            ->post(route('student.tasks.submit', $sub->id), ['submission_notes' => 'updated'])
            ->assertSessionHasNoErrors();

        $this->assertSame('updated', $sub->fresh()->submission_notes);
    }
}
