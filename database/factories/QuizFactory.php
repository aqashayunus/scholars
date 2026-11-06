<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quiz>
 */
class QuizFactory extends Factory
{
    protected $model = Quiz::class;

    public function definition(): array
    {
        return [
            'course_id'          => Course::factory(),
            'title'              => fake()->sentence(3),
            'description'        => fake()->optional()->sentence(),
            'total_questions'    => 10,
            'time_limit_minutes' => 30,
            'passing_marks'      => 50,
            'due_date'           => null,
        ];
    }
}
