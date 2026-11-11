<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\LearningMaterial;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LearningMaterial>
 */
class LearningMaterialFactory extends Factory
{
    protected $model = LearningMaterial::class;

    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'subject_id' => null,
            'topic_id' => null,
            'teacher_id' => Teacher::factory(),
            'title' => fake()->sentence(4),
            'type' => 'Notes',
            'topic' => fake()->word(),
            'description' => fake()->optional()->sentence(),
            'file_path' => 'learning-materials/'.fake()->uuid().'.pdf',
            'file_name' => fake()->word().'.pdf',
            'file_size' => fake()->numberBetween(1024, 5_000_000),
            'file_type' => 'pdf',
            'visibility' => 'all',
            'status' => 'Published',
        ];
    }
}
