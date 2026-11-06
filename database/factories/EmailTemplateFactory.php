<?php

namespace Database\Factories;

use App\Models\EmailTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<EmailTemplate>
 */
class EmailTemplateFactory extends Factory
{
    protected $model = EmailTemplate::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name'       => $name,
            'key'        => Str::slug($name, '_') . '_' . fake()->unique()->numberBetween(1, 1000000),
            'category'   => fake()->randomElement(['greetings', 'payment', 'restriction', 'class', 'academic', 'renewal', 'support', 'general']),
            'subject'    => fake()->sentence(6),
            'body'       => '<p>' . fake()->paragraph() . '</p>',
            'banner_url' => null,
            'cta_label'  => null,
            'cta_url'    => null,
            'status'     => 'active',
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
