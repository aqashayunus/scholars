<?php

namespace Database\Factories;

use App\Models\TeachingGig;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeachingGig>
 */
class TeachingGigFactory extends Factory
{
    protected $model = TeachingGig::class;

    public function definition(): array
    {
        return [
            'subject_id'          => null,
            'title'               => fake()->sentence(3),
            'gig_type'            => 'scheduled_class',
            'scheduled_at'        => fake()->dateTimeBetween('now', '+1 week'),
            'duration_minutes'    => 60,
            'payout_amount'       => fake()->randomFloat(2, 30, 150),
            'surge_bonus'         => 0,
            'status'              => 'open',
            'assigned_teacher_id' => null,
            'claimed_at'          => null,
            'checked_in_at'       => null,
            'completed_at'        => null,
            'meeting_url'         => null,
            'notes'               => null,
        ];
    }
}
