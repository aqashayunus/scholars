<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'subject_id',
        'description',
        'unit_price',
        'quantity',
        'amount',
        // Snapshot fields — point-in-time values preserved for historical invoice immutability
        'item_type',         // e.g. tuition, material, registration, deposit
        'package_name',      // name of the fee structure/package at time of invoice
        'billing_frequency', // e.g. monthly, annual, one_time
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'amount'     => 'decimal:2',
        'quantity'   => 'integer',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
