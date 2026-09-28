<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinancialAuditLog;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceReminder;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Subject;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{
    public function __construct(
        private InvoiceService $invoiceService,
        private PaymentService $paymentService,
    ) {}

    // ── Dashboard Stats ───────────────────────────────────────────────────────

    public function index()
    {
        $stats = [
            'total_invoices'    => Invoice::count(),
            'issued_invoices'   => Invoice::where('status', Invoice::STATUS_ISSUED)->count(),
            'paid_invoices'     => Invoice::where('status', Invoice::STATUS_PAID)->count(),
            'overdue_invoices'  => Invoice::where('status', Invoice::STATUS_OVERDUE)->count(),
            'partial_invoices'  => Invoice::where('status', Invoice::STATUS_PARTIALLY_PAID)->count(),
            'outstanding_amount'=> Invoice::whereIn('status', Invoice::UNPAID_STATUSES)->get()->sum('outstanding_amount'),
            'total_collected'   => Payment::where('status', Payment::STATUS_SUCCESSFUL)->sum('amount'),
            'pending_payments'  => Payment::where('status', Payment::STATUS_PENDING)->count(),
        ];

        $recentInvoices = Invoice::with(['student.user'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $pendingPayments = Payment::with(['student.user', 'invoices'])
            ->where('status', Payment::STATUS_PENDING)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('admin.billing.dashboard', compact('stats', 'recentInvoices', 'pendingPayments'));
    }

    // ── Invoice Management ────────────────────────────────────────────────────

    public function invoices(Request $request)
    {
        $query = Invoice::with(['student.user', 'items'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('invoice_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('student.user', fn ($sq) => $sq->where('name', 'like', '%' . $request->search . '%'));
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
        $students = Student::whereHas('user', fn($q) => $q->where('role', 'student'))->with('user')->orderBy('id')->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();

        return view('admin.billing.invoices.create', compact('students', 'subjects'));
    }

    public function invoiceStore(Request $request)
    {
        $request->validate([
            'student_id'     => ['required', 'exists:students,id'],
            'billing_period' => ['required', 'string'],
            'due_date'       => ['required', 'date'],
            'items'          => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.amount'      => ['required', 'numeric', 'min:0'],
        ]);

        $student = Student::findOrFail($request->student_id);

        return DB::transaction(function () use ($request, $student) {
            $subtotal = collect($request->items)->sum('amount');

            $invoice = Invoice::create([
                'student_id'     => $student->id,
                'invoice_number' => $this->invoiceService->generateInvoiceNumber(),
                'title'          => 'Tuition Fee – ' . $request->billing_period,
                'billing_period' => $request->billing_period,
                'subtotal'       => $subtotal,
                'discount_amount'=> 0,
                'total_amount'   => $subtotal,
                'amount_paid'    => 0,
                'issued_date'    => now()->toDateString(),
                'due_date'       => $request->due_date,
                'status'         => Invoice::STATUS_ISSUED,
            ]);

            foreach ($request->items as $item) {
                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'description' => $item['description'],
                    'unit_price'  => $item['amount'],
                    'quantity'    => 1,
                    'amount'      => $item['amount'],
                ]);
            }

            // Schedule reminders
            $this->invoiceService->scheduleReminders($invoice, $student);

            FinancialAuditLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'invoice_created_by_admin',
                'target_type' => 'Invoice',
                'target_id'   => $invoice->id,
                'ip_address'  => request()->ip(),
                'details'     => json_encode(['invoice_number' => $invoice->invoice_number, 'amount' => $subtotal]),
            ]);

            return redirect()->route('admin.billing.invoices.show', $invoice)
                ->with('success', "Invoice {$invoice->invoice_number} created successfully.");
        });
    }

    public function invoiceCancel(Invoice $invoice)
    {
        if (in_array($invoice->status, [Invoice::STATUS_PAID, Invoice::STATUS_CANCELLED])) {
            return back()->with('error', 'Cannot cancel a paid or already cancelled invoice.');
        }

        $invoice->update(['status' => Invoice::STATUS_CANCELLED]);

        // Skip all pending reminders
        app(\App\Services\ReminderService::class)->skipRemainingReminders($invoice);

        FinancialAuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'invoice_cancelled',
            'target_type' => 'Invoice',
            'target_id'   => $invoice->id,
            'ip_address'  => request()->ip(),
            'details'     => json_encode(['invoice_number' => $invoice->invoice_number]),
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

    // ── Reminder History ──────────────────────────────────────────────────────

    public function reminderHistory(Invoice $invoice)
    {
        $reminders = $invoice->reminders()->orderBy('scheduled_at')->get();

        return view('admin.billing.reminder-history', compact('invoice', 'reminders'));
    }

    // ── Subject Fee Management ────────────────────────────────────────────────

    public function subjectFees()
    {
        $subjects = Subject::where('is_active', true)
            ->orderBy('education_type')
            ->orderBy('name')
            ->get();

        return view('admin.billing.subject-fees', compact('subjects'));
    }

    public function updateSubjectFee(Request $request, Subject $subject)
    {
        $request->validate([
            'monthly_fee' => ['required', 'numeric', 'min:0', 'max:9999.99'],
        ]);

        $oldFee = $subject->monthly_fee;
        $subject->update(['monthly_fee' => $request->monthly_fee]);

        FinancialAuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'subject_fee_updated',
            'target_type' => 'Subject',
            'target_id'   => $subject->id,
            'ip_address'  => request()->ip(),
            'details'     => json_encode([
                'subject'  => $subject->name,
                'old_fee'  => $oldFee,
                'new_fee'  => $request->monthly_fee,
            ]),
        ]);

        return back()->with('success', "Fee for {$subject->name} updated to RM " . number_format($request->monthly_fee, 2) . ". New invoices will use this rate.");
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
            'user_id'     => Auth::id(),
            'action'      => 'billing_settings_updated',
            'target_type' => 'Setting',
            'target_id'   => 0,
            'ip_address'  => request()->ip(),
            'details'     => json_encode(['updated_by' => Auth::user()->email]),
        ]);

        return back()->with('success', 'Email reminder settings saved successfully.');
    }
}
