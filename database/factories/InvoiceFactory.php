<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $total = fake()->randomFloat(2, 100, 500);

        return [
            'student_id'      => Student::factory(),
            'invoice_number'  => 'INV-TEST-' . fake()->unique()->numberBetween(100000, 999999),
            'title'           => 'Tuition Fee',
            'billing_period'  => now()->format('F Y'),
            'subtotal'        => $total,
            'discount_amount' => 0,
            'total_amount'    => $total,
            'amount_paid'     => 0,
            'issued_date'     => now()->toDateString(),
            'due_date'        => now()->addDays(7)->toDateString(),
            'status'          => 'issued',
        ];
    }
}
