<?php

namespace Database\Factories;

use App\Models\TeacherApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeacherApplication>
 */
class TeacherApplicationFactory extends Factory
{
    protected $model = TeacherApplication::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('01#-#######'),
            'qualification' => "Bachelor's Degree",
            'experience_years' => fake()->numberBetween(1, 15),
            'preferred_subjects' => 'Mathematics, Science',
            'teaching_level' => null,
            'resume_path' => null,
            'certificate_path' => null,
            'transcript_path' => null,
            'additional_document_path' => null,
            'status' => 'pending',
            'admin_notes' => null,
            'approved_at' => null,
        ];
    }
}
