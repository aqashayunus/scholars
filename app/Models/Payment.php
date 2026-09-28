<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    // ── Status Constants ──────────────────────────────────────────────────────
    const STATUS_PENDING    = 'pending';
    const STATUS_SUCCESSFUL = 'successful';
    const STATUS_FAILED     = 'failed';
    const STATUS_CANCELLED  = 'cancelled';

    protected $fillable = [
        'user_id',
        'invoice_id',          // legacy: single-invoice payments
        'student_id',
        'payer_id',
        'payer_type',
        'course_id',
        'description',
        'amount',
        'payment_method',
        'proof_file_path',
        'payment_proof_notes',
        'reference_no',
        'gateway_transaction_id',
        'status',
        'receipt_number',
        'verified_by',
        'paid_at',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payer()
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /** @deprecated for new multi-invoice payments; use invoices() instead */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * All invoices this payment was allocated to (via payment_invoices pivot).
     */
    public function invoices()
    {
        return $this->belongsToMany(Invoice::class, 'payment_invoices')
                    ->withPivot('amount_applied')
                    ->withTimestamps();
    }

    public function paymentInvoices()
    {
        return $this->hasMany(PaymentInvoice::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // ── Accessors & Helpers ───────────────────────────────────────────────────

    public function getProofUrlAttribute(): ?string
    {
        return $this->proof_file_path ? asset('storage/' . $this->proof_file_path) : null;
    }

    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCESSFUL;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Generate a receipt number for this payment.
     * Format: RCP-YYYY-NNNNN
     */
    public static function generateReceiptNumber(): string
    {
        $year    = date('Y');
        $prefix  = "RCP-{$year}-";
        $last    = static::where('receipt_number', 'like', "{$prefix}%")
                         ->orderByDesc('receipt_number')
                         ->value('receipt_number');

        $nextSeq = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad($nextSeq, 5, '0', STR_PAD_LEFT);
    }
}
