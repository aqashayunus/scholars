<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\Billplz\BillplzService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BillplzController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private BillplzService $billplzService
    ) {}

    /**
     * Initiate Billplz FPX online checkout for a single invoice.
     * POST /billing/billplz/checkout/{invoice}
     */
    public function checkout(Request $request, Invoice $invoice)
    {
        if (!$invoice->requiresPayment()) {
            return back()->with('info', 'This invoice has already been fully paid or cancelled.');
        }

        $user = Auth::user();
        $this->authorizeInvoicePayment($invoice, $user);

        try {
            $result = $this->paymentService->initiateBillplzPayment([$invoice->id], $user);

            // Redirect user to Billplz hosted FPX checkout page
            return redirect()->away($result['bill_url']);
        } catch (\Throwable $e) {
            Log::error('[BillplzController] checkout failed: ' . $e->getMessage(), [
                'invoice_id' => $invoice->id,
                'user_id'    => $user->id,
            ]);

            return back()->with('error', 'Unable to connect to online banking gateway: ' . $e->getMessage());
        }
    }

    /**
     * Initiate Billplz FPX online checkout for multiple invoices (parent bulk checkout).
     * POST /billing/billplz/checkout-multi
     */
    public function checkoutMulti(Request $request)
    {
        $request->validate([
            'invoice_ids'   => ['required', 'array', 'min:1'],
            'invoice_ids.*' => ['integer', 'exists:invoices,id'],
        ]);

        $user = Auth::user();
        $invoices = Invoice::whereIn('id', $request->invoice_ids)->get();

        foreach ($invoices as $inv) {
            $this->authorizeInvoicePayment($inv, $user);
        }

        try {
            $result = $this->paymentService->initiateBillplzPayment($request->invoice_ids, $user);

            return redirect()->away($result['bill_url']);
        } catch (\Throwable $e) {
            Log::error('[BillplzController] checkoutMulti failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
            ]);

            return back()->with('error', 'Unable to initiate online payment: ' . $e->getMessage());
        }
    }

    /**
     * Synchronous Return Redirect from Billplz after bank payment.
     * GET /billing/billplz/return
     */
    public function handleReturn(Request $request)
    {
        Log::info('[BillplzController] Return URL hit', $request->all());

        // Extract Billplz response parameters
        // When Billplz redirects, parameters are nested inside billplz[...] or top level
        $billplzData = $request->input('billplz', $request->all());
        $billId      = $billplzData['id'] ?? $request->input('billplz_id');
        $isPaid      = isset($billplzData['paid']) && ($billplzData['paid'] === 'true' || $billplzData['paid'] === true);

        $user = Auth::user();
        $redirectRoute = ($user && ($user->isParent() || $user->role === 'parent'))
            ? route('parent.fees')
            : route('student.fees');

        if (empty($billId)) {
            return redirect($redirectRoute)->with('info', 'Returned from payment gateway.');
        }

        // Verify signature if provided
        $isValidSig = $this->billplzService->verifySignature($request->all());
        if (!$isValidSig) {
            Log::warning('[BillplzController] Return signature mismatch.', $request->all());
        }

        if ($isPaid) {
            try {
                $payment = $this->paymentService->completeGatewayPayment($billId, $request->all());
                $receiptNo = $payment->receipt_number ? " (Receipt No: {$payment->receipt_number})" : '';
                return redirect($redirectRoute)->with('success', "Payment successful! Your fees have been settled{$receiptNo}.");
            } catch (\Throwable $e) {
                Log::error('[BillplzController] Return completion error: ' . $e->getMessage());
                return redirect($redirectRoute)->with('success', 'Payment processed by bank. Your invoice status will update momentarily.');
            }
        }

        // Payment was not completed or user cancelled at bank
        try {
            $this->paymentService->failGatewayPayment($billId, $request->all(), 'Payment cancelled or incomplete at FPX checkout');
        } catch (\Throwable $e) {}

        return redirect($redirectRoute)->with('error', 'Online payment was not completed or was cancelled. Please try again or use bank transfer.');
    }

    /**
     * Asynchronous Webhook callback from Billplz (server-to-server).
     * POST /billing/billplz/webhook
     */
    public function handleWebhook(Request $request)
    {
        Log::info('[BillplzController] Webhook received', $request->all());

        try {
            $this->paymentService->handleGatewayWebhook($request->all());
            return response()->json(['status' => 'ok'], 200);
        } catch (\InvalidArgumentException $e) {
            Log::warning('[BillplzController] Webhook bad request: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            Log::error('[BillplzController] Webhook processing exception: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Internal processing error'], 500);
        }
    }

    /**
     * Authorize user to pay the given invoice.
     */
    private function authorizeInvoicePayment(Invoice $invoice, $user): void
    {
        if ($user->role === 'student') {
            $student = $user->student;
            if (!$student || $student->id !== $invoice->student_id) {
                abort(403, 'Unauthorized access to this invoice.');
            }
            return;
        }

        if ($user->role === 'parent') {
            $childIds = $user->authorizedChildren()->pluck('students.id')->toArray();
            if (!in_array($invoice->student_id, $childIds)) {
                abort(403, 'Unauthorized access to child invoice.');
            }
            return;
        }

        if ($user->role === 'admin') {
            return;
        }

        abort(403, 'Unauthorized.');
    }
}
