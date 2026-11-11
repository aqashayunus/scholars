<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\RecordedClass;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecordedClass>
 */
class RecordedClassFactory extends Factory
{
    protected $model = RecordedClass::class;

    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'subject_id' => Subject::factory(),
            'title' => fake()->sentence(4),
            'topic_name' => fake()->word(),
            'video_url' => 'https://example.com/videos/'.fake()->uuid().'.mp4',
            'thumbnail' => null,
            'duration_minutes' => 45,
            'views_count' => 0,
            'summary' => fake()->optional()->sentence(),
        ];
    }
}
