<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\Student;
use App\Models\StudentAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentAssignment>
 */
class StudentAssignmentFactory extends Factory
{
    protected $model = StudentAssignment::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'assignment_id' => Assignment::factory(),
            'status' => 'pending',
            'score' => null,
            'submitted_at' => null,
        ];
    }
}
