<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\FeeStructure;
use App\Models\FinancialAuditLog;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\PaymentInvoice;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Subject;
use App\Services\BillingNotificationService;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use App\Services\ReminderService;
use App\Services\StudentAccessRestrictionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BillingController extends Controller
{
    public function __construct(
        private InvoiceService $invoiceService,
        private PaymentService $paymentService,
    ) {}

    // ── Invoice Management ────────────────────────────────────────────────────
    // (The standalone Billing dashboard was retired 2026-11-27; /admin/billing
    // now redirects to Admin Dashboard > Financials & Sales.)

    public function invoices(Request $request)
    {
        $query = Invoice::with(['student.user', 'items'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('invoice_number', 'like', '%'.$request->search.'%')
                    ->orWhereHas('student.user', fn ($sq) => $sq->where('name', 'like', '%'.$request->search.'%'));
            });
        }

        $invoices = $query->paginate(25)->withQueryString();

        return view('admin.billing.invoices.index', compact('invoices'));
    }

    public function invoiceShow(Invoice $invoice)
    {
        $invoice->loadMissing(['items', 'student.user', 'paymentTransactions.verifier', 'reminders']);

        return view('admin.billing.invoices.show', compact('invoice'));
    }

    public function invoiceCreate()
    {
        $students = Student::whereHas('user', fn ($q) => $q->where('role', 'student'))->with('user')->orderBy('id')->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();

        return view('admin.billing.invoices.create', compact('students', 'subjects'));
    }

    public function invoiceStore(Request $request)
    {
        $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'billing_period' => ['required', 'string'],
            'due_date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.amount' => ['required', 'numeric', 'min:0'],
        ]);

        $student = Student::findOrFail($request->student_id);

        return DB::transaction(function () use ($request, $student) {
            $subtotal = collect($request->items)->sum('amount');

            $invoice = new Invoice([
                'student_id' => $student->id,
                'invoice_number' => $this->invoiceService->generateInvoiceNumber(),
                'title' => 'Tuition Fee – '.$request->billing_period,
                'billing_period' => $request->billing_period,
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'total_amount' => $subtotal,
                'issued_date' => now()->toDateString(),
                'due_date' => $request->due_date,
            ]);
            $invoice->amount_paid = 0;
            $invoice->status = InvoiceStatus::Issued;
            $invoice->save();

            foreach ($request->items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'],
                    'unit_price' => $item['amount'],
                    'quantity' => 1,
                    'amount' => $item['amount'],
                ]);
            }

            // Schedule reminders
            $this->invoiceService->scheduleReminders($invoice, $student);

            FinancialAuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'invoice_created_by_admin',
                'target_type' => 'Invoice',
                'target_id' => $invoice->id,
                'ip_address' => request()->ip(),
                'details' => json_encode(['invoice_number' => $invoice->invoice_number, 'amount' => $subtotal]),
            ]);

            return redirect()->route('admin.billing.invoices.show', $invoice)
                ->with('success', "Invoice {$invoice->invoice_number} created successfully.");
        });
    }

    /**
     * Extracted from an inline route closure in routes/web.php (the admin
     * dashboard's quick single-line invoice form) -- a 4th privileged route
     * closure missed during the Phase 4 route-closure extraction pass, found
     * during the Phase 4 gap-closure validation. Same behavior as before,
     * including the dispatch to invoiceStore() when an itemized `items[]`
     * payload is submitted instead of this quick single-line form.
     */
    public function invoiceStoreQuick(Request $request)
    {
        if ($request->has('items') && is_array($request->items)) {
            return $this->invoiceStore($request);
        }

        $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'title' => ['required', 'string', 'max:255'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'due_date' => ['required', 'date'],
        ]);

        $student = Student::with('user')->findOrFail($request->student_id);
        $subtotal = (float) $request->subtotal;
        $discount = (float) ($request->discount_amount ?? 0);
        $total = max(0, $subtotal - $discount);

        $feeType = 'Tuition Fee';
        if ($request->fee_structure_id) {
            $fs = FeeStructure::find($request->fee_structure_id);
            if ($fs) {
                $feeType = $fs->name;
            }
        }
        $monthYear = now()->format('F Y');
        $studentName = $student->user->name ?? "Student #{$student->id}";
        $title = $request->filled('title') ? $request->title : "{$feeType} {$monthYear} {$studentName}";

        $invoice = new Invoice([
            'student_id' => $student->id,
            'fee_structure_id' => $request->fee_structure_id ?: null,
            'invoice_number' => $this->invoiceService->generateInvoiceNumber(),
            'title' => $title,
            'billing_period' => $monthYear,
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'total_amount' => $total,
            'issued_date' => now()->toDateString(),
            'due_date' => $request->due_date,
        ]);
        $invoice->amount_paid = 0;
        $invoice->status = InvoiceStatus::Issued;
        $invoice->save();

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => $title,
            'unit_price' => $subtotal,
            'quantity' => 1,
            'amount' => $subtotal,
        ]);

        $this->invoiceService->scheduleReminders($invoice, $student);

        // In-app alert for student & parent
        BillingNotificationService::notifyInvoiceIssued($invoice);

        return back()->with('success', "Invoice {$invoice->invoice_number} created successfully.");
    }

    public function invoiceCancel(Invoice $invoice)
    {
        if (in_array($invoice->status, [InvoiceStatus::Paid, InvoiceStatus::Cancelled])) {
            return back()->with('error', 'Cannot cancel a paid or already cancelled invoice.');
        }

        $invoice->status = InvoiceStatus::Cancelled;
        $invoice->save();

        // Skip all pending reminders
        app(ReminderService::class)->skipRemainingReminders($invoice);

        FinancialAuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'invoice_cancelled',
            'target_type' => 'Invoice',
            'target_id' => $invoice->id,
            'ip_address' => request()->ip(),
            'details' => json_encode(['invoice_number' => $invoice->invoice_number]),
        ]);

        return redirect()->route('admin.billing.invoices.show', $invoice)
            ->with('success', 'Invoice cancelled successfully.');
    }

    // ── Payment Management ────────────────────────────────────────────────────

    public function payments(Request $request)
    {
        $query = Payment::with(['payer', 'student.user', 'invoices'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->paginate(25)->withQueryString();

        return view('admin.billing.payments.index', compact('payments'));
    }

    public function paymentShow(Payment $payment)
    {
        $payment->loadMissing(['payer', 'student.user', 'invoices.items', 'verifier']);

        return view('admin.billing.payments.show', compact('payment'));
    }

    public function verifyPayment(Request $request, Payment $payment)
    {
        $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->paymentService->approveManualPayment($payment, Auth::user(), $request->notes);

            return back()->with('success', 'Payment approved successfully. Receipt generated and invoice updated.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function rejectPayment(Request $request, Payment $payment)
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->paymentService->rejectManualPayment($payment, Auth::user(), $request->reason);

            return back()->with('success', 'Payment rejected.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ── Quick Dashboard Actions ──────────────────────────────────────────────
    // Extracted from inline route closures in routes/web.php (admin dashboard's
    // quick invoice-status/record-payment/verify-payment widgets) — same
    // behavior as before, except quickVerifyPayment() now validates
    // approved_amount, which the closure never did.

    public function updateInvoiceStatusQuick(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:draft,issued,partially_paid,paid,overdue,cancelled',
            'amount_paid' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $invoice = Invoice::findOrFail($id);
        $invoice->status = $request->status;

        if ($request->status === 'paid') {
            $invoice->amount_paid = $invoice->total_amount;
            app(ReminderService::class)->skipRemainingReminders($invoice);
        } elseif ($request->status === 'partially_paid') {
            if ($request->filled('amount_paid') && (float) $request->amount_paid > 0) {
                $invoice->amount_paid = min((float) $invoice->total_amount, (float) $request->amount_paid);
            } elseif ($invoice->amount_paid <= 0) {
                $invoice->amount_paid = round($invoice->total_amount / 2, 2);
            }
        } elseif ($request->status === 'issued') {
            if ($request->input('reset_amount') == '1' || $request->amount_paid === '0') {
                $invoice->amount_paid = 0;
            }
        }

        if ($request->filled('notes')) {
            $invoice->notes = $request->notes;
        }

        $invoice->save();

        // Sync linked Payment record so the Payment Ledger shows the correct amount
        if (in_array($request->status, ['paid', 'partially_paid'])) {
            $pendingPayment = $invoice->paymentTransactions()
                ->where('payments.status', PaymentStatus::Pending)
                ->first();

            if ($pendingPayment) {
                $amountToApply = (float) $invoice->amount_paid;
                $pendingPayment->amount = $amountToApply;

                if ($request->status === 'paid') {
                    if (! $pendingPayment->receipt_number) {
                        $pendingPayment->receipt_number = Payment::generateReceiptNumber();
                    }
                    $pendingPayment->status = PaymentStatus::Successful;
                    $pendingPayment->verified_by = Auth::id();
                    $pendingPayment->paid_at = now();
                }

                $pendingPayment->save();

                PaymentInvoice::where('payment_id', $pendingPayment->id)
                    ->where('invoice_id', $invoice->id)
                    ->update(['amount_applied' => $amountToApply]);
            }
        }

        if ($invoice->student) {
            app(StudentAccessRestrictionService::class)->syncStudentStatus($invoice->student);
        }

        return back()->with('success', "Invoice {$invoice->invoice_number} status updated to {$invoice->status_label}.");
    }

    public function recordInvoicePaymentQuick(Request $request, $id)
    {
        $invoice = Invoice::with('student.user')->findOrFail($id);

        $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'string'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $amount = (float) $request->amount;
        $admin = Auth::user();
        $receiptNumber = null;

        DB::transaction(function () use ($invoice, $amount, $request, $admin, &$receiptNumber) {
            $receiptNumber = Payment::generateReceiptNumber();
            $payerId = $invoice->student->user_id ?? $admin->id;

            $payment = Payment::create([
                'user_id' => $payerId,
                'reference_no' => $request->reference_no ?: ('ADM-'.strtoupper(Str::random(8))),
                'payer_id' => $payerId,
                'payer_type' => 'student',
                'student_id' => $invoice->student_id,
                'description' => "Payment for Invoice #{$invoice->invoice_number}",
                'amount' => $amount,
                'payment_method' => $request->payment_method,
                'status' => PaymentStatus::Successful,
                'receipt_number' => $receiptNumber,
                'verified_by' => $admin->id,
                'paid_at' => now(),
                'payment_proof_notes' => $request->notes,
            ]);

            PaymentInvoice::create([
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
                'amount_applied' => $amount,
            ]);

            $this->invoiceService->recalculateAmountPaid($invoice);
        });

        $invoice->refresh();
        $typeLabel = $invoice->status === InvoiceStatus::Paid ? 'full' : 'partial';
        $isPartial = $invoice->status !== InvoiceStatus::Paid;

        BillingNotificationService::notifyPaymentRecorded($invoice, $amount, $receiptNumber, $isPartial);

        return back()->with('success', "Authorized {$typeLabel} payment of RM ".number_format($amount, 2)." for Invoice {$invoice->invoice_number}. Current status: {$invoice->status_label}.");
    }

    public function verifyPaymentQuick(Request $request, $payment)
    {
        $p = Payment::findOrFail($payment);

        if ($request->input('action') === 'reject') {
            $request->validate(['reason' => ['nullable', 'string', 'max:500']]);
            $this->paymentService->rejectManualPayment($p, Auth::user(), $request->input('reason', 'Payment could not be verified'));

            return back()->with('success', 'Payment rejected successfully.');
        }

        if ($request->input('action') === 'pending') {
            $p->status = 'pending';
            $p->verified_by = null;
            $p->save();

            return back()->with('success', "Payment {$p->reference_no} has been reset to Pending Review.");
        }

        // Fix (2026-10-27): approved_amount was never validated here, unlike
        // every other money-accepting endpoint in this controller — a
        // non-numeric value silently cast to 0.0 via (float), and there was
        // no upper bound against the invoice total.
        if ($request->filled('approved_amount')) {
            $request->validate([
                'approved_amount' => ['numeric', 'min:0.01'],
            ]);
            $approvedAmt = (float) $request->approved_amount;
            $p->amount = $approvedAmt;
            $p->save();
            PaymentInvoice::where('payment_id', $p->id)->update(['amount_applied' => $approvedAmt]);
        }

        $request->validate(['notes' => ['nullable', 'string', 'max:1000']]);
        $this->paymentService->approveManualPayment($p, Auth::user(), $request->input('notes'));

        return back()->with('success', "Payment {$p->reference_no} of RM ".number_format($p->amount, 2).' approved and verified successfully.');
    }

    // ── Reminder History ──────────────────────────────────────────────────────

    public function reminderHistory(Invoice $invoice)
    {
        $reminders = $invoice->reminders()->orderBy('scheduled_at')->get();

        return view('admin.billing.reminder-history', compact('invoice', 'reminders'));
    }

    // ── Email Reminder Settings ───────────────────────────────────────────────

    public function emailSettings()
    {
        $settings = Setting::group('billing');

        return view('admin.billing.email-settings', compact('settings'));
    }

    public function updateEmailSettings(Request $request)
    {
        $toggles = [
            'billing.reminder_invoice_issued',
            'billing.reminder_due_7_days',
            'billing.reminder_due_3_days',
            'billing.reminder_due_today',
            'billing.reminder_overdue_1_day',
            'billing.reminder_overdue_3_days',
            'billing.reminder_overdue_7_days',
        ];

        foreach ($toggles as $key) {
            Setting::set($key, $request->has(str_replace('.', '_', $key)) ? '1' : '0', 'billing');
        }

        $textFields = [
            'billing.invoice_due_days',
            'billing.email_sender_name',
            'billing.bank_name',
            'billing.bank_account_number',
            'billing.bank_account_name',
            'billing.payment_instructions',
        ];

        foreach ($textFields as $key) {
            $inputName = str_replace('.', '_', $key);
            if ($request->filled($inputName)) {
                Setting::set($key, $request->input($inputName), 'billing');
            }
        }

        FinancialAuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'billing_settings_updated',
            'target_type' => 'Setting',
            'target_id' => 0,
            'ip_address' => request()->ip(),
            'details' => json_encode(['updated_by' => Auth::user()->email]),
        ]);

        return back()->with('success', 'Email reminder settings saved successfully.');
    }
}
