<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'user_id'                => User::factory(),
            'invoice_id'              => null,
            'student_id'              => null,
            'payer_id'               => null,
            'payer_type'             => 'student',
            'course_id'              => null,
            'description'            => fake()->sentence(4),
            'amount'                 => fake()->randomFloat(2, 50, 500),
            'payment_method'         => fake()->randomElement(['bank_transfer', 'fpx', 'cash']),
            'proof_file_path'        => null,
            'payment_proof_notes'    => null,
            'reference_no'           => fake()->unique()->numerify('REF########'),
            'gateway_transaction_id' => null,
            'billplz_bill_id'        => null,
            'billplz_url'            => null,
            'gateway_payload'        => null,
            // 'pending' is valid under both the old DB-level enum and the
            // PaymentStatus cast -- see the note in Payment.php history about
            // the stale vocabulary mismatch between them.
            'status'                 => 'pending',
            'receipt_number'         => null,
            'verified_by'            => null,
            'paid_at'                => null,
        ];
    }
}
