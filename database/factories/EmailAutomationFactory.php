<?php

namespace Database\Factories;

use App\Models\EmailAutomation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailAutomation>
 */
class EmailAutomationFactory extends Factory
{
    protected $model = EmailAutomation::class;

    public function definition(): array
    {
        return [
            'key' => fake()->unique()->word().'_'.fake()->unique()->numberBetween(1, 1000000),
            'name' => fake()->words(3, true),
            'category' => fake()->randomElement(['greetings', 'payment', 'restriction', 'class', 'academic', 'renewal', 'support', 'general']),
            'trigger_event' => fake()->randomElement(['student.registered', 'invoice.overdue', 'class.scheduled']),
            'description' => fake()->optional()->sentence(),
            'email_template_id' => null,
            'is_enabled' => true,
            'delay_minutes' => 0,
            'recipient_rules' => null,
            'last_triggered_at' => null,
            'success_count' => 0,
            'failure_count' => 0,
        ];
    }
}
