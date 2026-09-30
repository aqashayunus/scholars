<?php

namespace App\Services\Student;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Student;
use App\Models\UserNotification;
use Illuminate\Http\UploadedFile;

class BillingService
{
    /**
     * Builds the full view-model for the student's "My Fees" portal.
     */
    public function feesData(Student $student): array
    {
        Invoice::syncStudentInvoices($student);

        $invoices = Invoice::where('student_id', $student->id)
            ->with(['feeStructure', 'course.subject', 'payments'])
            ->latest('due_date')
            ->get();

        $outstandingInvoices = $invoices->filter(fn ($inv) => in_array($inv->status, InvoiceStatus::unpaidStatuses(), true))->values();
        $paidInvoices        = $invoices->filter(fn ($inv) => $inv->status === InvoiceStatus::Paid)->values();
        $overdueInvoices     = $invoices->filter(fn ($inv) => $inv->status === InvoiceStatus::Overdue)->values();

        $totalOutstandingAmount = $outstandingInvoices->sum('total_amount');
        $totalPaidAmount        = $paidInvoices->sum('total_amount');

        $payments = Payment::where('student_id', $student->id)
            ->with(['invoice', 'payer'])
            ->latest('id')
            ->get();

        // Public Company Bank details for manual bank transfer
        $bankDetails = [
            'bank_name'      => Setting::get('company_bank_name', 'Maybank Islamic Berhad'),
            'account_name'   => Setting::get('company_bank_account_name', 'Olympia Scholars Academy Sdn Bhd'),
            'account_number' => Setting::get('company_bank_account_number', '5642 8901 2345'),
            'instructions'   => Setting::get('company_bank_instructions', 'Please include your Student Name & Invoice No in the transfer reference.'),
        ];

        return compact(
            'invoices',
            'outstandingInvoices',
            'paidInvoices',
            'overdueInvoices',
            'totalOutstandingAmount',
            'totalPaidAmount',
            'payments',
            'bankDetails'
        );
    }

    /**
     * Process an invoice payment by the student.
     * Returns ['status' => 'already_paid'|'pending_verification'|'success', 'message' => string].
     */
    public function payInvoice(Student $student, int $invoiceId, string $paymentMethod, ?UploadedFile $proofFile): array
    {
        $invoice = Invoice::where('student_id', $student->id)->findOrFail($invoiceId);

        if ($invoice->status === InvoiceStatus::Paid) {
            return ['status' => 'already_paid', 'message' => 'This invoice has already been fully paid.'];
        }

        if ($paymentMethod === 'bank_transfer') {
            $proofPath = $proofFile->store('payment_proofs', 'public');
            $refNo = 'TXN-TRF-' . strtoupper(substr(md5(uniqid()), 0, 8));

            Payment::create([
                'user_id'         => auth()->id(),
                'invoice_id'      => $invoice->id,
                'student_id'      => $student->id,
                'payer_id'        => auth()->id(),
                'payer_type'      => 'student',
                'description'     => $invoice->title . ' (Bank Transfer Slip Uploaded)',
                'amount'          => $invoice->total_amount,
                'payment_method'  => 'bank_transfer',
                'proof_file_path' => $proofPath,
                'reference_no'    => $refNo,
                'status'          => PaymentStatus::Pending,
                'paid_at'         => now(),
            ]);

            // 'pending_verification' was never a valid Invoice status (enum:
            // draft/issued/partially_paid/paid/overdue/cancelled) — would
            // throw under strict SQL mode. The Payment record just created
            // above already correctly carries PaymentStatus::Pending, which
            // is the real "awaiting verification" signal; the invoice stays
            // at its current status (issued/overdue) until an admin approves
            // the payment via AdminDashboardController::verifyPayment().

            return ['status' => 'pending_verification', 'message' => 'Payment proof submitted successfully! Your payment is pending verification.'];
        }

        $refNo = 'TXN-FPX-' . strtoupper(substr(md5(uniqid()), 0, 8));
        $receiptNo = 'RCP-' . date('Y') . '-' . str_pad($invoice->id * 10 + rand(1, 9), 5, '0', STR_PAD_LEFT);

        Payment::create([
            'user_id'        => auth()->id(),
            'invoice_id'     => $invoice->id,
            'student_id'     => $student->id,
            'payer_id'       => auth()->id(),
            'payer_type'     => 'student',
            'description'    => $invoice->title,
            'amount'         => $invoice->total_amount,
            'payment_method' => $paymentMethod,
            'reference_no'   => $refNo,
            'receipt_number' => $receiptNo,
            'status'         => PaymentStatus::Successful,
            'paid_at'        => now(),
        ]);

        $invoice->update(['status' => InvoiceStatus::Paid]);

        UserNotification::create([
            'user_id' => auth()->id(),
            'type'    => 'payment',
            'title'   => 'PAYMENT SUCCESSFUL',
            'message' => 'Payment of RM ' . number_format($invoice->total_amount, 2) . " for '{$invoice->title}' has been processed. Receipt No: {$receiptNo}.",
            'read_at' => null,
        ]);

        return ['status' => 'success', 'message' => "Payment successful! Receipt #{$receiptNo} has been generated."];
    }
}
