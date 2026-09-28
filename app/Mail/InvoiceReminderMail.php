<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\InvoiceReminder;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Single reusable Mailable for all 6 post-issue reminder types:
 *   due_7_days, due_3_days, due_today,
 *   overdue_1_day, overdue_3_days, overdue_7_days
 *
 * The $reminderType parameter drives subject line and template copy.
 */
class InvoiceReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public string  $reminderType,
        public string  $recipientEmail,
        public string  $recipientType,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address(
                config('mail.from.address'),
                Setting::get('billing.email_sender_name', 'Olympia Scholars')
            ),
            subject: InvoiceReminder::emailSubject($this->reminderType, $this->invoice->invoice_number),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice.reminder',
            with: $this->buildViewData(),
        );
    }

    public function buildViewData(): array
    {
        $invoice  = $this->invoice->loadMissing(['items', 'student.user', 'student.parents']);
        $student  = $invoice->student;

        // Recipient name
        if ($this->recipientType === 'parent') {
            $parentUser    = $student->parents->first(fn ($p) => $p->email === $this->recipientEmail);
            $recipientName = $parentUser?->name ?? 'Parent/Guardian';
        } else {
            $recipientName = $student?->user?->name ?? 'Student';
        }

        // Contextual copy based on reminder type
        [$headline, $daysLabel, $isOverdue] = $this->getTypeCopy();

        return [
            'invoice'            => $invoice,
            'reminderType'       => $this->reminderType,
            'recipientName'      => $recipientName,
            'recipientType'      => $this->recipientType,
            'studentName'        => $student?->user?->name ?? '—',
            'invoiceNumber'      => $invoice->invoice_number,
            'billingPeriod'      => $invoice->billing_period,
            'issueDate'          => $invoice->issued_date?->format('d M Y') ?? '—',
            'dueDate'            => $invoice->due_date?->format('d M Y') ?? '—',
            'items'              => $invoice->items,
            'totalAmount'        => number_format($invoice->total_amount, 2),
            'amountPaid'         => number_format($invoice->amount_paid, 2),
            'outstandingAmount'  => number_format($invoice->outstanding_amount, 2),
            'status'             => $invoice->status_label,
            'headline'           => $headline,
            'daysLabel'          => $daysLabel,
            'isOverdue'          => $isOverdue,
            'bankName'           => Setting::get('billing.bank_name', ''),
            'bankAccountNumber'  => Setting::get('billing.bank_account_number', ''),
            'bankAccountName'    => Setting::get('billing.bank_account_name', ''),
            'paymentInstructions'=> Setting::get('billing.payment_instructions', ''),
            'invoiceUrl'         => ($this->recipientType === 'parent') ? (route('parent.fees') . '#invoice-' . $invoice->id) : (route('student.fees') . '#invoice-' . $invoice->id),
            'senderName'         => Setting::get('billing.email_sender_name', 'Olympia Scholars'),
        ];
    }

    private function getTypeCopy(): array
    {
        return match ($this->reminderType) {
            'due_7_days'     => ['This invoice is due in <strong>7 days</strong>.',    '7 days', false],
            'due_3_days'     => ['This invoice is due in <strong>3 days</strong>.',    '3 days', false],
            'due_today'      => ['This invoice is <strong>due today</strong>.',        'today',  false],
            'overdue_1_day'  => ['This invoice is now <strong>overdue</strong>.',      '1 day',  true],
            'overdue_3_days' => ['This invoice is <strong>3 days overdue</strong>.',   '3 days', true],
            'overdue_7_days' => ['This invoice is <strong>7 days overdue</strong>.',   '7 days', true],
            default          => ['Your payment is due.',                               '',       false],
        };
    }
}
