<?php

namespace Database\Factories;

use App\Models\FeeStructure;
use App\Models\FeeStructureItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FeeStructureItem>
 */
class FeeStructureItemFactory extends Factory
{
    protected $model = FeeStructureItem::class;

    public function definition(): array
    {
        return [
            'fee_structure_id' => FeeStructure::factory(),
            'name' => fake()->words(2, true),
            'description' => null,
            'item_type' => 'tuition',
            'amount' => fake()->randomFloat(2, 50, 300),
            'quantity' => 1,
            'billing_frequency' => 'monthly',
            'is_optional' => false,
            'is_refundable' => false,
            'sort_order' => 0,
        ];
    }
}
