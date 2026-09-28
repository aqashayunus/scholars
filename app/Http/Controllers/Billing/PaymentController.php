<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    // ── Initiate Payment (single invoice) ────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'invoice_id'   => ['required', 'integer', 'exists:invoices,id'],
            'payment_method' => ['required', 'in:bank_transfer,online'],
            'reference_no' => ['nullable', 'string', 'max:100'],
        ]);

        $invoice = Invoice::findOrFail($request->invoice_id);

        if (!$invoice->requiresPayment()) {
            return back()->with('error', 'This invoice is already paid or cancelled.');
        }

        // Authorization: only the student or their parent
        $this->authorizePayment($invoice);

        try {
            $payment = $this->paymentService->initiateManualTransfer(
                [$invoice->id],
                Auth::user(),
                ['reference_no' => $request->reference_no]
            );

            $redirectRoute = (Auth::check() && (Auth::user()->isParent() || Auth::user()->role === 'parent'))
                ? route('parent.fees.show', $invoice)
                : route('student.fees.show', $invoice);

            return redirect($redirectRoute)
                ->with('success', 'Payment initiated. Please upload your bank transfer proof below.');

        } catch (\Exception $e) {
            Log::error('[PaymentController] store failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to initiate payment. Please try again.');
        }
    }

    // ── Multi-Invoice Checkout (parent pays for multiple children) ────────────

    public function storeMulti(Request $request)
    {
        $request->validate([
            'invoice_ids'    => ['required', 'array', 'min:1'],
            'invoice_ids.*'  => ['integer', 'exists:invoices,id'],
            'reference_no'   => ['nullable', 'string', 'max:100'],
        ]);

        $user = Auth::user();

        // Verify all invoices belong to this parent's authorized children
        if ($user->role === 'parent') {
            $childIds = $user->authorizedChildren()->pluck('students.id')->toArray();
            $invoices = Invoice::whereIn('id', $request->invoice_ids)->get();

            foreach ($invoices as $invoice) {
                if (!in_array($invoice->student_id, $childIds)) {
                    abort(403, 'You do not have access to one or more selected invoices.');
                }
            }
        }

        try {
            $payment = $this->paymentService->initiateManualTransfer(
                $request->invoice_ids,
                $user,
                ['reference_no' => $request->reference_no]
            );

            return redirect()->route('parent.fees')
                ->with('success', "Payment of RM " . number_format($payment->amount, 2) . " initiated. Please upload your bank transfer proof.");

        } catch (\Exception $e) {
            Log::error('[PaymentController] storeMulti failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to initiate payment. ' . $e->getMessage());
        }
    }

    // ── Gateway Webhook (stub for future payment gateway integration) ─────────

    public function gatewayWebhook(Request $request)
    {
        // IMPORTANT: In production, verify the webhook signature before processing.
        // Do NOT process unauthenticated webhooks.
        Log::info('[PaymentController] Gateway webhook received.', $request->all());

        try {
            $this->paymentService->handleGatewayWebhook($request->all());
            return response()->json(['status' => 'ok'], 200);
        } catch (\Exception $e) {
            Log::error('[PaymentController] Gateway webhook error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Internal error'], 500);
        }
    }

    // ── Authorization Helper ──────────────────────────────────────────────────

    private function authorizePayment(Invoice $invoice): void
    {
        $user = Auth::user();

        if ($user->role === 'student') {
            $student = $user->student;
            if (!$student || $student->id !== $invoice->student_id) {
                abort(403);
            }
            return;
        }

        if ($user->role === 'parent') {
            $childIds = $user->authorizedChildren()->pluck('students.id')->toArray();
            if (!in_array($invoice->student_id, $childIds)) {
                abort(403);
            }
            return;
        }

        abort(403);
    }
}
