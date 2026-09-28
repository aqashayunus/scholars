<?php

namespace Database\Factories;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Teacher>
 */
class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    public function definition(): array
    {
        return [
            'user_id'          => User::factory()->state(['role' => 'teacher']),
            'specialization'   => fake()->randomElement(['Mathematics', 'Science', 'English', 'General Academic']),
            'qualification'    => 'B.Ed',
            'rating'           => 5.0,
            'experience_years' => fake()->numberBetween(1, 15),
        ];
    }
}
