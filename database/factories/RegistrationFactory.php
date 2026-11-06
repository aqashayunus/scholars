<?php

namespace Database\Factories;

use App\Models\Registration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Registration>
 */
class RegistrationFactory extends Factory
{
    protected $model = Registration::class;

    public function definition(): array
    {
        return [
            'user_id'       => null,
            'register_as'   => 'student',
            'student_name'  => fake()->name(),
            'parent_name'   => fake()->optional()->name(),
            'educator_name' => null,
            'school_name'   => fake()->optional()->company(),
            'email'         => fake()->unique()->safeEmail(),
            'phone'         => fake()->numerify('01#-#######'),
            'programme'     => fake()->randomElement(['SPM', 'IGCSE', 'STPM']),
            'level'         => fake()->optional()->word(),
            'subjects'      => null,
            'schedule'      => null,
            'promo_code'    => null,
            'notes'         => null,
            'status'        => 'pending',
        ];
    }
}
