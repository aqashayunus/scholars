<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        return [
            'subject_id'    => Subject::factory(),
            'teacher_id'    => Teacher::factory(),
            'title'         => fake()->sentence(3),
            'description'   => fake()->paragraph(),
            'level'         => 'SPM',
            'price'         => 99.00,
            'status'        => 'published',
            'total_lessons' => fake()->numberBetween(4, 24),
        ];
    }
}
