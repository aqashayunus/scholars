<?php

namespace Database\Factories;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BlogPost>
 */
class BlogPostFactory extends Factory
{
    protected $model = BlogPost::class;

    public function definition(): array
    {
        $title = fake()->sentence(6);

        return [
            'user_id' => User::factory()->state(['role' => 'admin']),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 1000000),
            'excerpt' => fake()->sentence(15),
            'body' => fake()->paragraphs(4, true),
            'thumbnail' => null,
            'category' => fake()->randomElement(['Academic Tips', 'News', 'Exam Prep']),
            'published_at' => now(),
        ];
    }
}
