<?php

namespace Database\Factories;

use App\Models\EmailCampaign;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailCampaign>
 */
class EmailCampaignFactory extends Factory
{
    protected $model = EmailCampaign::class;

    public function definition(): array
    {
        return [
            'name'             => fake()->sentence(4),
            'category'         => 'general',
            'description'      => fake()->optional()->sentence(),
            'subject'          => fake()->sentence(6),
            'body'             => '<p>' . fake()->paragraph() . '</p>',
            'cta_label'        => null,
            'cta_url'          => null,
            'template_id'      => null,
            'audience_type'    => fake()->randomElement(['all_students', 'all_parents', 'all_teachers', 'custom']),
            'audience_filters' => null,
            'total_recipients' => 0,
            'sent_count'       => 0,
            'failed_count'     => 0,
            'status'           => 'draft',
            'scheduled_at'     => null,
            'started_at'       => null,
            'completed_at'     => null,
            'created_by'       => null,
        ];
    }
}
