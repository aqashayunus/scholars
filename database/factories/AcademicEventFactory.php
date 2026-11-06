<?php

namespace Database\Factories;

use App\Models\AcademicEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademicEvent>
 */
class AcademicEventFactory extends Factory
{
    protected $model = AcademicEvent::class;

    public function definition(): array
    {
        return [
            'title'           => fake()->sentence(3),
            'description'     => fake()->optional()->sentence(),
            'event_type'      => fake()->randomElement(['public_holiday', 'academic_term', 'school_break', 'exam_period', 'special_event']),
            'start_date'      => fake()->dateTimeBetween('now', '+3 months')->format('Y-m-d'),
            'end_date'        => null,
            'is_all_day'      => true,
            'color'           => fake()->randomElement(['#ef4444', '#f59e0b', '#10b981']),
            'state_or_region' => null,
            'created_by'      => User::factory()->state(['role' => 'admin']),
        ];
    }
}
