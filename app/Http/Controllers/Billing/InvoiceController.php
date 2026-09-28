<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Student;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    // ── Student: Invoice List ─────────────────────────────────────────────────

    public function studentIndex()
    {
        $user    = Auth::user();
        $student = $user->student;

        if (!$student) {
            abort(404, 'Student profile not found.');
        }

        $invoices = Invoice::where('student_id', $student->id)
            ->with(['items'])
            ->orderByDesc('created_at')
            ->get();

        $summary = [
            'total_invoices' => $invoices->count(),
            'outstanding'    => $invoices->whereIn('status', Invoice::UNPAID_STATUSES)->count(),
            'paid'           => $invoices->where('status', Invoice::STATUS_PAID)->count(),
            'overdue'        => $invoices->where('status', Invoice::STATUS_OVERDUE)->count(),
            'total_owed'     => $invoices->whereIn('status', Invoice::UNPAID_STATUSES)->sum('outstanding_amount'),
        ];

        return view('student.fees.index', compact('invoices', 'summary', 'student'));
    }

    // ── Parent: Invoice List ──────────────────────────────────────────────────

    public function parentIndex()
    {
        $user = Auth::user();

        // Load all authorized children
        $children = $user->authorizedChildren()
            ->with(['invoices' => fn ($q) => $q->with('items')->orderByDesc('created_at')])
            ->get();

        // Flatten all outstanding invoices across children for checkout
        $outstandingInvoices = $children->flatMap(fn ($s) => $s->invoices->whereIn('status', Invoice::UNPAID_STATUSES));

        return view('parent.fees.index', compact('children', 'outstandingInvoices'));
    }

    // ── Show Invoice (shared by student and parent) ───────────────────────────

    public function show(Invoice $invoice)
    {
        // Authorization: only the student or their parent can view
        $this->authorizeInvoiceAccess($invoice);

        $invoice->loadMissing(['items', 'student.user', 'paymentTransactions.verifier', 'reminders']);

        return view('student.fees.show', compact('invoice'));
    }

    // ── Upload Payment Proof ──────────────────────────────────────────────────

    public function uploadProof(Request $request, Invoice $invoice)
    {
        $this->authorizeInvoiceAccess($invoice);

        if (!$invoice->requiresPayment()) {
            return back()->with('error', 'This invoice does not require payment.');
        }

        $request->validate([
            'proof_file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'reference_no' => ['nullable', 'string', 'max:100'],
        ]);

        // Find or create a pending payment for this invoice
        $payment = $invoice->paymentTransactions()
            ->where('status', \App\Models\Payment::STATUS_PENDING)
            ->first();

        if (!$payment) {
            // Create the payment record
            $payment = $this->paymentService->initiateManualTransfer(
                [$invoice->id],
                Auth::user(),
                ['reference_no' => $request->reference_no]
            );
        }

        // Upload the proof file
        $this->paymentService->uploadProof($payment, $request->file('proof_file'));

        return back()->with('success', 'Payment proof uploaded successfully. Your payment is pending admin verification.');
    }

    // ── Authorization Helper ──────────────────────────────────────────────────

    private function authorizeInvoiceAccess(Invoice $invoice): void
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return;
        }

        if ($user->role === 'student') {
            $student = $user->student;
            if (!$student || $student->id !== $invoice->student_id) {
                abort(403, 'You do not have access to this invoice.');
            }
            return;
        }

        if ($user->role === 'parent') {
            $childIds = $user->authorizedChildren()->pluck('students.id')->toArray();
            if (!in_array($invoice->student_id, $childIds)) {
                abort(403, 'You do not have access to this invoice.');
            }
            return;
        }

        abort(403);
    }
}
