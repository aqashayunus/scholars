<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceReminder extends Model
{
    protected $fillable = [
        'invoice_id',
        'reminder_type',
        'recipient_email',
        'recipient_type',
        'scheduled_at',
        'sent_at',
        'status',
        'error_message',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at'      => 'datetime',
    ];

    /**
     * All valid reminder type identifiers.
     */
    public const TYPES = [
        'invoice_issued',
        'due_7_days',
        'due_3_days',
        'due_today',
        'overdue_1_day',
        'overdue_3_days',
        'overdue_7_days',
    ];

    /**
     * Human-readable label for each reminder type.
     */
    public static function typeLabel(string $type): string
    {
        return match ($type) {
            'invoice_issued'  => 'Invoice Issued',
            'due_7_days'      => '7 Days Before Due',
            'due_3_days'      => '3 Days Before Due',
            'due_today'       => 'Due Today',
            'overdue_1_day'   => '1 Day Overdue',
            'overdue_3_days'  => '3 Days Overdue',
            'overdue_7_days'  => '7 Days Overdue',
            default           => ucwords(str_replace('_', ' ', $type)),
        };
    }

    /**
     * Email subject line for each reminder type.
     */
    public static function emailSubject(string $type, string $invoiceNumber): string
    {
        return match ($type) {
            'invoice_issued'  => "Scholars – Invoice {$invoiceNumber} Issued",
            'due_7_days'      => "Reminder: Your Scholars Payment Is Due in 7 Days",
            'due_3_days'      => "Reminder: Your Scholars Payment Is Due in 3 Days",
            'due_today'       => "Scholars – Payment Due Today",
            'overdue_1_day'   => "Scholars – Payment Overdue: Action Required",
            'overdue_3_days'  => "Scholars – Payment Reminder: Invoice Overdue (3 Days)",
            'overdue_7_days'  => "Scholars – Payment Overdue: Immediate Attention Required",
            default           => "Scholars – Invoice {$invoiceNumber} Notification",
        };
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }
}
