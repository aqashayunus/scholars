<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Pivot model for payment_invoices table.
 *
 * Represents the allocation of a payment (checkout transaction)
 * to a specific invoice.
 */
class PaymentInvoice extends Model
{
    protected $fillable = [
        'payment_id',
        'invoice_id',
        'amount_applied',
    ];

    protected $casts = [
        'amount_applied' => 'decimal:2',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
