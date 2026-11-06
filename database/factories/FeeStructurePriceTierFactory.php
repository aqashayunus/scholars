<?php

namespace Database\Factories;

use App\Models\FeeStructure;
use App\Models\FeeStructurePriceTier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FeeStructurePriceTier>
 */
class FeeStructurePriceTierFactory extends Factory
{
    protected $model = FeeStructurePriceTier::class;

    public function definition(): array
    {
        return [
            'fee_structure_id' => FeeStructure::factory(),
            'minimum_subjects' => 1,
            'maximum_subjects' => 1,
            'price'            => fake()->randomFloat(2, 50, 300),
            'sort_order'       => 0,
        ];
    }
}
