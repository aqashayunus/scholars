<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentInvoice>
 */
class PaymentInvoiceFactory extends Factory
{
    protected $model = PaymentInvoice::class;

    public function definition(): array
    {
        return [
            'payment_id' => Payment::factory(),
            'invoice_id' => Invoice::factory(),
            'amount_applied' => fake()->randomFloat(2, 50, 300),
        ];
    }
}
