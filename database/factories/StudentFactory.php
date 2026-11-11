<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'student']),
            'level' => fake()->randomElement(['SPM', 'IGCSE', 'STPM', 'Foundation']),
            'status' => 'active',
            'total_points' => 0,
            'streak_days' => 1,
        ];
    }
}
