<?php

namespace App\Services\Parent;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\Billplz\BillplzService;
use App\Services\PaymentService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Family-scoped billing logic extracted from ParentDashboardController.
 * outstandingSummary() converges the "pending-Payment-sum vs.
 * Invoice-balance-sum fallback" + "next payment due" calculation that
 * dashboard() and payments() both used to inline independently.
 */
class BillingService
{
    /**
     * Shared outstanding-balance + next-payment-due summary. $payments is
     * the caller's already-scoped Payment collection (dashboard() and
     * payments() scope it differently upstream — this method only
     * converges what happens to it afterward).
     */
    public function outstandingSummary(Collection $payments, Collection $children, ?Student $child): array
    {
        // Payment has no 'overdue' state (pending/successful/failed/cancelled) —
        // overdue-ness is an Invoice property, not a Payment one.
        $totalOutstanding = (float) $payments->where('status', PaymentStatus::Pending)->sum('amount');
        $pendingCount = $payments->where('status', PaymentStatus::Pending)->count();

        $lastPaymentMade = $payments->first(fn ($p) => $p->status === PaymentStatus::Successful);

        $relevantChildIds = $child ? [$child->id] : $children->pluck('id')->filter()->all();
        $nextInvoiceDue = null;

        if (! empty($relevantChildIds)) {
            $nextInvoiceDue = Invoice::whereIn('student_id', $relevantChildIds)
                ->whereIn('status', InvoiceStatus::unpaidStatuses())
                ->with('student.user')
                ->orderBy('due_date', 'asc')
                ->first();

            // Sync total outstanding and pending count if child invoices exist outside payments table
            $childInvoiceOutstanding = (float) Invoice::whereIn('student_id', $relevantChildIds)
                ->whereIn('status', InvoiceStatus::unpaidStatuses())
                ->sum(DB::raw('total_amount - amount_paid'));

            if ($totalOutstanding <= 0 && $childInvoiceOutstanding > 0) {
                $totalOutstanding = $childInvoiceOutstanding;
                $pendingCount = Invoice::whereIn('student_id', $relevantChildIds)
                    ->whereIn('status', InvoiceStatus::unpaidStatuses())
                    ->count();
            }
        }

        return [
            'totalOutstanding' => $totalOutstanding,
            'pendingCount' => $pendingCount,
            'lastPaymentMade' => $lastPaymentMade,
            'nextPaymentDue' => $this->buildNextPaymentDue($nextInvoiceDue, $payments),
        ];
    }

    private function buildNextPaymentDue(?Invoice $nextInvoiceDue, Collection $payments): ?object
    {
        if ($nextInvoiceDue) {
            $isOverdue = $nextInvoiceDue->due_date ? ($nextInvoiceDue->due_date->isPast() && ! $nextInvoiceDue->due_date->isToday()) : false;
            $isToday = $nextInvoiceDue->due_date ? $nextInvoiceDue->due_date->isToday() : false;
            $daysDiff = $nextInvoiceDue->due_date ? (int) abs(now()->startOfDay()->diffInDays($nextInvoiceDue->due_date->startOfDay())) : null;

            if ($isOverdue) {
                $statusLabel = 'Overdue ('.$daysDiff.' '.Str::plural('day', $daysDiff).')';
            } elseif ($isToday) {
                $statusLabel = 'Due today';
            } elseif ($daysDiff !== null) {
                $statusLabel = 'Due in '.$daysDiff.' '.Str::plural('day', $daysDiff);
            } else {
                $statusLabel = 'Pending';
            }

            return (object) [
                'type' => 'invoice',
                'amount' => (float) $nextInvoiceDue->outstanding_amount,
                'title' => $nextInvoiceDue->title,
                'invoice_number' => $nextInvoiceDue->invoice_number,
                'due_date' => $nextInvoiceDue->due_date,
                'student_name' => $nextInvoiceDue->student?->user?->name,
                'is_overdue' => $isOverdue,
                'is_today' => $isToday,
                'status_label' => $statusLabel,
                'subtext' => $nextInvoiceDue->student?->user?->name ?? $nextInvoiceDue->invoice_number,
            ];
        }

        // Payment has no 'overdue' state — this comparison could never be
        // true even before the enum conversion, kept as-is (not a phantom
        // branch worth deleting in this slice; behavior-preserving).
        if ($pendingP = $payments->where('status', PaymentStatus::Pending)->first()) {
            $isOverdue = false;

            return (object) [
                'type' => 'payment',
                'amount' => (float) $pendingP->amount,
                'title' => $pendingP->description,
                'invoice_number' => $pendingP->reference_no,
                'due_date' => null,
                'student_name' => $pendingP->student?->user?->name,
                'is_overdue' => $isOverdue,
                'is_today' => false,
                'status_label' => $isOverdue ? 'Overdue' : 'Pending',
                'subtext' => $pendingP->reference_no ?? 'Tuition Fee',
            ];
        }

        return null;
    }

    /**
     * Financial metric-cards payments query for dashboard(). Moved verbatim
     * out of ParentDashboardController::dashboard().
     */
    public function dashboardPayments(User $user, Collection $children, ?Student $child): Collection
    {
        $paymentsQuery = Payment::where(function ($q) use ($user, $children) {
            $q->where('user_id', $user->id)
                ->orWhere('payer_id', $user->id);
            if ($children->isNotEmpty()) {
                $q->orWhereIn('student_id', $children->pluck('id'));
            }
        })->with('course.subject');

        if ($child) {
            $childCourseIds = $child->enrollments->pluck('course_id')->filter()->all();
            $paymentsQuery->where(function ($q) use ($child, $childCourseIds) {
                $q->where('student_id', $child->id);
                if (! empty($childCourseIds)) {
                    $q->orWhereIn('course_id', $childCourseIds);
                }
            });
        }

        return $paymentsQuery->orderByDesc('id')->get();
    }

    /**
     * Full payments-page data for payments(). Moved verbatim out of
     * ParentDashboardController::payments().
     */
    public function paymentsPageData(User $user, Collection $children, ?Student $child): array
    {
        $paymentsQuery = Payment::where('user_id', $user->id)->with('course.subject');

        if ($child) {
            $childCourseIds = $child->enrollments->pluck('course_id')->filter()->all();
            if (! empty($childCourseIds)) {
                $paymentsQuery->where(function ($q) use ($childCourseIds) {
                    $q->whereIn('course_id', $childCourseIds)
                        ->orWhereNull('course_id');
                });
            }
        }

        $payments = $paymentsQuery->orderByDesc('id')->get();

        $totalPaid = (float) $payments->where('status', PaymentStatus::Successful)->sum('amount');
        $paidCount = $payments->where('status', PaymentStatus::Successful)->count();
        $latestPayment = $payments->where('status', PaymentStatus::Successful)->first();

        $summary = $this->outstandingSummary($payments, $children, $child);

        // Pending invoices list for quick checkout
        $pendingPayments = $payments->where('status', PaymentStatus::Pending)->values();

        return [
            'payments' => $payments,
            'totalPaid' => $totalPaid,
            'totalOutstanding' => $summary['totalOutstanding'],
            'pendingCount' => $summary['pendingCount'],
            'paidCount' => $paidCount,
            'latestPayment' => $latestPayment,
            'pendingPayments' => $pendingPayments,
            'lastPaymentMade' => $summary['lastPaymentMade'],
            'nextPaymentDue' => $summary['nextPaymentDue'],
        ];
    }

    /**
     * Process a child invoice payment by a parent. Returns a result array;
     * the controller maps 'status' to the right redirect/back response
     * (same convention as Student/BillingService::payInvoice()).
     */
    public function payInvoice(User $parent, array $childIds, int $invoiceId, string $paymentMethod, ?UploadedFile $proofFile): array
    {
        $invoice = Invoice::whereIn('student_id', $childIds)->with('student.user')->findOrFail($invoiceId);

        if ($invoice->status === InvoiceStatus::Paid) {
            return ['status' => 'already_paid', 'message' => 'This invoice has already been fully paid.'];
        }

        if ($paymentMethod === 'bank_transfer') {
            $proofPath = $proofFile->store('payment_proofs', 'public');
            $refNo = 'TXN-TRF-'.strtoupper(substr(md5(uniqid()), 0, 8));

            Payment::create([
                'user_id' => $parent->id,
                'invoice_id' => $invoice->id,
                'student_id' => $invoice->student_id,
                'payer_id' => $parent->id,
                'payer_type' => 'parent',
                'description' => "Payment for {$invoice->student->user->name} – {$invoice->title} (Bank Transfer Slip Uploaded)",
                'amount' => $invoice->total_amount,
                'payment_method' => 'bank_transfer',
                'proof_file_path' => $proofPath,
                'reference_no' => $refNo,
                'status' => 'pending',
                'paid_at' => now(),
            ]);

            // Invoice status is deliberately left untouched here, matching
            // Student/BillingService::payInvoice()'s bank_transfer branch —
            // 'pending_verification' is not a valid InvoiceStatus case (the
            // original code set this raw string directly, which crashed
            // with a ValueError the moment anything read the invoice back
            // through its enum cast; the Payment row's own Pending status
            // already conveys "awaiting verification").
            return [
                'status' => 'bank_transfer_submitted',
                'message' => "Payment proof for {$invoice->student->user->name} submitted! Your payment is pending verification.",
            ];
        }

        // Billplz Online Banking Gateway
        $billplzService = new BillplzService;
        if ($billplzService->getApiKey()) {
            try {
                /** @var PaymentService $paymentService */
                $paymentService = app(PaymentService::class);
                $result = $paymentService->initiateBillplzPayment([$invoice->id], $parent);

                return ['status' => 'redirect_away', 'url' => $result['bill_url']];
            } catch (\Throwable $e) {
                Log::error('[Parent\\BillingService] Billplz checkout error: '.$e->getMessage());

                return ['status' => 'gateway_error', 'message' => 'Online payment gateway error: '.$e->getMessage()];
            }
        }

        // Mock fallback if gateway credentials not set
        $refNo = 'TXN-FPX-'.strtoupper(substr(md5(uniqid()), 0, 8));
        $receiptNo = 'RCP-'.date('Y').'-'.str_pad($invoice->id * 10 + rand(1, 9), 5, '0', STR_PAD_LEFT);

        Payment::create([
            'user_id' => $parent->id,
            'invoice_id' => $invoice->id,
            'student_id' => $invoice->student_id,
            'payer_id' => $parent->id,
            'payer_type' => 'parent',
            'description' => "Payment for {$invoice->student->user->name} – {$invoice->title}",
            'amount' => $invoice->total_amount,
            'payment_method' => $paymentMethod,
            'reference_no' => $refNo,
            'receipt_number' => $receiptNo,
            'status' => PaymentStatus::Successful,
            'paid_at' => now(),
        ]);

        $invoice->status = InvoiceStatus::Paid;
        $invoice->save();

        UserNotification::create([
            'user_id' => $parent->id,
            'type' => 'payment',
            'title' => 'PAYMENT SUCCESSFUL',
            'message' => 'Payment of RM '.number_format($invoice->total_amount, 2)." for {$invoice->student->user->name} ('{$invoice->title}') has been processed. Receipt No: {$receiptNo}.",
            'read_at' => null,
        ]);

        if ($invoice->student->user_id) {
            UserNotification::create([
                'user_id' => $invoice->student->user_id,
                'type' => 'payment',
                'title' => 'TUITION FEE PAID BY PARENT',
                'message' => "Your parent ({$parent->name}) has completed the payment for '{$invoice->title}'. Receipt No: {$receiptNo}.",
                'read_at' => null,
            ]);
        }

        return [
            'status' => 'success',
            'message' => "Payment for {$invoice->student->user->name} was successful! Receipt #{$receiptNo} has been generated.",
        ];
    }

    /**
     * Process simulated / gateway payment for one or more pending Payments.
     */
    public function processPayment(User $user, array $paymentIds, string $paymentMethod, ?string $bankName): array
    {
        $paymentsToPay = Payment::where('user_id', $user->id)
            ->whereIn('id', $paymentIds)
            // 'overdue' has never been a valid Payment status (enum:
            // pending/successful/failed/cancelled).
            ->where('status', PaymentStatus::Pending)
            ->get();

        if ($paymentsToPay->isEmpty()) {
            return ['success' => false, 'message' => 'No eligible pending invoices found for payment.'];
        }

        $methodTitle = $paymentMethod;
        if (! empty($bankName)) {
            $methodTitle .= ' ('.$bankName.')';
        }

        $totalPaidAmount = 0;
        foreach ($paymentsToPay as $p) {
            $txnRef = 'OS-TXN-'.date('Ymd').'-'.strtoupper(Str::random(6));
            $p->update([
                'status' => PaymentStatus::Successful,
                'payment_method' => $methodTitle,
                'reference_no' => $txnRef,
                'paid_at' => now(),
            ]);
            $totalPaidAmount += (float) $p->amount;
        }

        return [
            'success' => true,
            'message' => '✓ Payment of RM '.number_format($totalPaidAmount, 2)." completed successfully via {$methodTitle}! Official receipt generated.",
        ];
    }

    /**
     * Create a manual custom tuition or materials fee invoice. Returns the
     * generated invoice reference.
     */
    public function createCustomInvoice(User $user, string $description, float $amount, ?int $courseId): string
    {
        $invoiceRef = 'OS-INV-'.date('Ym').'-'.strtoupper(Str::random(5));

        Payment::create([
            'user_id' => $user->id,
            'course_id' => $courseId,
            'description' => $description,
            'amount' => $amount,
            'status' => 'pending',
            'reference_no' => $invoiceRef,
            'created_at' => now(),
        ]);

        return $invoiceRef;
    }
}
