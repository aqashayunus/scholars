<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition(): array
    {
        $unitPrice = fake()->randomFloat(2, 50, 300);
        $quantity = 1;

        return [
            'invoice_id'        => Invoice::factory(),
            'subject_id'        => null,
            'description'       => fake()->sentence(3),
            'unit_price'        => $unitPrice,
            'quantity'          => $quantity,
            'amount'            => $unitPrice * $quantity,
            'item_type'         => 'tuition',
            'package_name'      => null,
            'billing_frequency' => 'monthly',
        ];
    }
}
