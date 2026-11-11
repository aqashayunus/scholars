<?php

namespace Database\Factories;

use App\Models\Teacher;
use App\Models\TeacherEarning;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeacherEarning>
 */
class TeacherEarningFactory extends Factory
{
    protected $model = TeacherEarning::class;

    public function definition(): array
    {
        return [
            'teacher_id' => Teacher::factory(),
            'teaching_gig_id' => null,
            'description' => fake()->sentence(4),
            'base_amount' => fake()->randomFloat(2, 30, 150),
            'bonus_amount' => 0,
            'total_amount' => fake()->randomFloat(2, 30, 150),
            'status' => 'approved',
            'payout_batch_id' => null,
            'paid_at' => null,
        ];
    }
}
