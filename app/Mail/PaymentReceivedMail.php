<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
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
            subject: "Scholars – Payment Received for Invoice {$this->invoice->invoice_number}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice.payment-received',
            with: $this->buildViewData(),
        );
    }

    /**
     * Dispatch to a recipient array (e.g. ['email'=>..., 'type'=>...]).
     */
    public static function dispatchToRecipient(Invoice $invoice, array $recipient): void
    {
        \Illuminate\Support\Facades\Mail::to($recipient['email'])
            ->queue(new static($invoice, $recipient['email'], $recipient['type']));
    }

    public function buildViewData(): array
    {
        $invoice = $this->invoice->loadMissing(['items', 'student.user', 'student.parents', 'paymentTransactions']);
        $student = $invoice->student;

        if ($this->recipientType === 'parent') {
            $parentUser    = $student->parents->first(fn ($p) => $p->email === $this->recipientEmail);
            $recipientName = $parentUser?->name ?? 'Parent/Guardian';
        } else {
            $recipientName = $student?->user?->name ?? 'Student';
        }

        $latestPayment = $invoice->paymentTransactions->sortByDesc('pivot.created_at')->first();

        return [
            'invoice'           => $invoice,
            'recipientName'     => $recipientName,
            'recipientType'     => $this->recipientType,
            'studentName'       => $student?->user?->name ?? '—',
            'invoiceNumber'     => $invoice->invoice_number,
            'billingPeriod'     => $invoice->billing_period,
            'issueDate'         => $invoice->issued_date?->format('d M Y') ?? '—',
            'dueDate'           => $invoice->due_date?->format('d M Y') ?? '—',
            'items'             => $invoice->items,
            'totalAmount'       => number_format($invoice->total_amount, 2),
            'amountPaid'        => number_format($invoice->amount_paid, 2),
            'outstandingAmount' => number_format($invoice->outstanding_amount, 2),
            'status'            => $invoice->status_label,
            'receiptNumber'     => $latestPayment?->receipt_number ?? '—',
            'paymentDate'       => $latestPayment?->paid_at?->format('d M Y H:i') ?? now()->format('d M Y H:i'),
            'invoiceUrl'        => ($this->recipientType === 'parent') ? (route('parent.fees') . '#invoice-' . $invoice->id) : (route('student.fees') . '#invoice-' . $invoice->id),
            'senderName'        => Setting::get('billing.email_sender_name', 'Olympia Scholars'),
        ];
    }
}
