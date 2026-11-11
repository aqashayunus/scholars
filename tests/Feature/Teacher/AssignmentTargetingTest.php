<?php

namespace Tests\Feature\Teacher;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\StudentAssignment;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignmentTargetingTest extends TestCase
{
    use RefreshDatabase;

    private Teacher $teacher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->teacher = Teacher::factory()->create()->fresh();
    }

    private function actingAsTeacher()
    {
        return $this->actingAs($this->teacher->user);
    }

    public function test_assign_target_all_creates_a_student_assignment_for_every_enrollment(): void
    {
        $course = Course::factory()->create(['teacher_id' => $this->teacher->id]);
        $students = Student::factory()->count(3)->create();
        foreach ($students as $student) {
            Enrollment::create(['student_id' => $student->id, 'course_id' => $course->id, 'status' => 'active']);
        }

        $response = $this->actingAsTeacher()->post(route('teacher.classes.tasks.store', $course->id), [
            'title' => 'Homework for Everyone',
            'due_date' => now()->addDays(3)->toDateString(),
            'assign_target' => 'all',
        ]);

        $response->assertRedirect();
        $assignment = Assignment::where('title', 'Homework for Everyone')->firstOrFail();
        $this->assertEquals('all', $assignment->assign_target);
        $this->assertEquals(3, StudentAssignment::where('assignment_id', $assignment->id)->count());
    }

    public function test_assign_target_selected_only_creates_rows_for_chosen_students(): void
    {
        $course = Course::factory()->create(['teacher_id' => $this->teacher->id]);
        $students = Student::factory()->count(3)->create();
        foreach ($students as $student) {
            Enrollment::create(['student_id' => $student->id, 'course_id' => $course->id, 'status' => 'active']);
        }
        $chosen = $students->take(2)->pluck('id')->all();

        $response = $this->actingAsTeacher()->post(route('teacher.classes.tasks.store', $course->id), [
            'title' => 'Homework for Selected',
            'due_date' => now()->addDays(3)->toDateString(),
            'assign_target' => 'selected',
            'student_ids' => $chosen,
        ]);

        $response->assertRedirect();
        $assignment = Assignment::where('title', 'Homework for Selected')->firstOrFail();
        $this->assertEquals('selected', $assignment->assign_target);
        $this->assertEquals(2, StudentAssignment::where('assignment_id', $assignment->id)->count());
        foreach ($chosen as $studentId) {
            $this->assertDatabaseHas('student_assignments', ['assignment_id' => $assignment->id, 'student_id' => $studentId]);
        }
        $excluded = $students->last()->id;
        $this->assertDatabaseMissing('student_assignments', ['assignment_id' => $assignment->id, 'student_id' => $excluded]);
    }

    public function test_tasks_data_roster_narrows_for_a_selected_target_assignment(): void
    {
        $course = Course::factory()->create(['teacher_id' => $this->teacher->id]);
        $students = Student::factory()->count(3)->create();
        foreach ($students as $student) {
            Enrollment::create(['student_id' => $student->id, 'course_id' => $course->id, 'status' => 'active']);
        }
        $chosen = $students->take(2)->pluck('id')->all();

        $this->actingAsTeacher()->post(route('teacher.classes.tasks.store', $course->id), [
            'title' => 'Targeted Task',
            'due_date' => now()->addDays(3)->toDateString(),
            'assign_target' => 'selected',
            'student_ids' => $chosen,
        ]);

        $response = $this->actingAsTeacher()->get(route('teacher.tasks'));

        $response->assertOk();
        $response->assertViewHas('mappedTasks', function ($tasks) {
            $task = collect($tasks)->firstWhere('title', 'Targeted Task');

            return $task !== null
                && $task['assign_target'] === 'selected'
                && $task['totalStudents'] === 2
                && count($task['roster']) === 2;
        });
    }
}
