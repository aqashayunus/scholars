<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'fee_structure_id',  // legacy — kept for backward compatibility
        'invoice_number',
        'title',
        'description',
        'notes',
        'billing_period',
        'subtotal',
        'discount_amount',
        'total_amount',
        'amount_paid',
        'issued_date',
        'due_date',
        'status',
    ];

    protected $casts = [
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount'    => 'decimal:2',
        'amount_paid'     => 'decimal:2',
        'issued_date'     => 'date',
        'due_date'        => 'date',
        'status'          => InvoiceStatus::class,
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /** @deprecated Use invoice items for pricing detail. Kept for legacy records. */
    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function paymentInvoices()
    {
        return $this->hasMany(PaymentInvoice::class);
    }

    /**
     * All payment transactions allocated to this invoice.
     */
    public function paymentTransactions()
    {
        return $this->belongsToMany(Payment::class, 'payment_invoices')
                    ->withPivot('amount_applied')
                    ->withTimestamps();
    }

    public function reminders()
    {
        return $this->hasMany(InvoiceReminder::class)->orderBy('scheduled_at');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    /**
     * Outstanding amount = total - amount already paid.
     */
    public function getOutstandingAmountAttribute(): float
    {
        return max(0, (float) $this->total_amount - (float) $this->amount_paid);
    }

    /**
     * Human-readable status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }

    /**
     * CSS badge class for status display.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            InvoiceStatus::Draft         => 'badge-secondary',
            InvoiceStatus::Issued        => 'badge-info',
            InvoiceStatus::PartiallyPaid => 'badge-warning',
            InvoiceStatus::Paid          => 'badge-success',
            InvoiceStatus::Overdue       => 'badge-danger',
            InvoiceStatus::Cancelled     => 'badge-dark',
        };
    }

    // ── Status Helpers ────────────────────────────────────────────────────────

    public function isFullyPaid(): bool
    {
        return $this->status === InvoiceStatus::Paid;
    }

    public function isCancelled(): bool
    {
        return $this->status === InvoiceStatus::Cancelled;
    }

    public function isOverdue(): bool
    {
        return $this->status === InvoiceStatus::Overdue;
    }

    /**
     * True when the invoice is active and not yet fully paid.
     */
    public function requiresPayment(): bool
    {
        return in_array($this->status, InvoiceStatus::unpaidStatuses(), true);
    }

    /**
     * True when the invoice has passed its due date and is not paid.
     */
    public function isPastDue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && !$this->isFullyPaid();
    }
}
