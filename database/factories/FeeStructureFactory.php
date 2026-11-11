<?php

namespace Database\Factories;

use App\Models\FeeStructure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FeeStructure>
 */
class FeeStructureFactory extends Factory
{
    protected $model = FeeStructure::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true).' Package',
            'description' => fake()->optional()->sentence(),
            'amount' => fake()->randomFloat(2, 100, 600),
            'education_level' => 'All',
            'frequency' => 'monthly',
            'is_active' => true,
            'student_id' => null,
            'package_type' => 'individual',
            'minimum_subjects' => null,
            'maximum_subjects' => null,
            'effective_from' => null,
            'effective_until' => null,
            'status' => 'active',
            'version' => 1,
            'parent_fee_structure_id' => null,
            'is_stackable_discount' => false,
        ];
    }
}
