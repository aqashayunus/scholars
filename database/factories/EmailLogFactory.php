<?php

namespace Database\Factories;

use App\Models\EmailLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailLog>
 */
class EmailLogFactory extends Factory
{
    protected $model = EmailLog::class;

    public function definition(): array
    {
        return [
            'email_campaign_id' => null,
            'email_template_id' => null,
            'email_automation_id' => null,
            'recipient_user_id' => null,
            'recipient_email' => fake()->safeEmail(),
            'recipient_name' => fake()->name(),
            'recipient_type' => fake()->randomElement(['parent', 'student', 'teacher', 'admin', 'custom']),
            'related_student_id' => null,
            'related_invoice_id' => null,
            'subject' => fake()->sentence(6),
            'category' => 'general',
            'body_preview' => fake()->sentence(),
            'status' => 'queued',
            'provider_message_id' => null,
            'attempts' => 1,
            'error_message' => null,
            'queued_at' => now(),
            'sent_at' => null,
            'delivered_at' => null,
        ];
    }
}
