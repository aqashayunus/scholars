<?php

namespace App\Services\Student;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Student;

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
        $paidInvoices = $invoices->filter(fn ($inv) => $inv->status === InvoiceStatus::Paid)->values();
        $overdueInvoices = $invoices->filter(fn ($inv) => $inv->status === InvoiceStatus::Overdue)->values();

        $totalOutstandingAmount = $outstandingInvoices->sum('total_amount');
        $totalPaidAmount = $paidInvoices->sum('total_amount');

        $payments = Payment::where('student_id', $student->id)
            ->with(['invoice', 'payer'])
            ->latest('id')
            ->get();

        // Bank details for manual bank transfer: the same billing.bank_* settings
        // the invoice page and emails use. No made-up fallback account.
        $bankDetails = [
            'bank_name' => Setting::get('billing.bank_name', ''),
            'account_name' => Setting::get('billing.bank_account_name', ''),
            'account_number' => Setting::get('billing.bank_account_number', ''),
            'instructions' => Setting::get('company_bank_instructions', 'Please include your Student Name & Invoice No in the transfer reference.'),
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
}
