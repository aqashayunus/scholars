<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\StudentTopicMastery;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentTopicMastery>
 */
class StudentTopicMasteryFactory extends Factory
{
    protected $model = StudentTopicMastery::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'subject_id' => Subject::factory(),
            'topic_name' => fake()->word(),
            'mastery_percentage' => fake()->numberBetween(0, 100),
            'quizzes_attempted' => fake()->numberBetween(0, 10),
            'last_practiced_at' => now(),
        ];
    }
}
