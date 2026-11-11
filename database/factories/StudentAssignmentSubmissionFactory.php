<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\Student;
use App\Models\StudentAssignment;
use App\Models\StudentAssignmentSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentAssignmentSubmission>
 */
class StudentAssignmentSubmissionFactory extends Factory
{
    protected $model = StudentAssignmentSubmission::class;

    public function definition(): array
    {
        return [
            'student_assignment_id' => StudentAssignment::factory(),
            'assignment_id' => Assignment::factory(),
            'student_id' => Student::factory(),
            'status' => 'draft',
            'current_page' => 1,
            'zoom_level' => 1.0,
            'total_pages' => 1,
            'version' => 1,
            'final_pdf_path' => null,
            'started_at' => now(),
            'last_saved_at' => now(),
            'submitted_at' => null,
        ];
    }
}
