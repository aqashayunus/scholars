<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Assignment>
 */
class AssignmentFactory extends Factory
{
    protected $model = Assignment::class;

    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'subject_id' => null,
            'is_global' => false,
            'target_level' => null,
            'created_by_admin' => false,
            'title' => fake()->sentence(4),
            'type' => 'homework',
            'topic' => fake()->word(),
            'description' => fake()->optional()->sentence(),
            'instructions' => fake()->optional()->sentence(),
            'due_date' => fake()->dateTimeBetween('now', '+2 weeks')->format('Y-m-d'),
            'total_marks' => 100,
            'duration_minutes' => null,
            'exam_date' => null,
            'file_path' => null,
        ];
    }
}
