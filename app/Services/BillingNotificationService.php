<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceReminder;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BillingNotificationService
{
    /**
     * Get all parent User models linked to a student.
     */
    public static function getStudentParents($student): Collection
    {
        if (!$student) {
            return collect();
        }

        $parents = collect();
        if (!empty($student->parent_user_id)) {
            $p = User::find($student->parent_user_id);
            if ($p) {
                $parents->push($p);
            }
        }

        $pivotParents = DB::table('parent_student')
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->pluck('parent_user_id');

        if ($pivotParents->isNotEmpty()) {
            $pivotUsers = User::whereIn('id', $pivotParents)->get();
            $parents = $parents->merge($pivotUsers);
        }

        return $parents->unique('id');
    }

    /**
     * Notify student and parents when a new invoice is issued.
     */
    public static function notifyInvoiceIssued(Invoice $invoice): void
    {
        try {
            $invoice->loadMissing('student.user');
            $student = $invoice->student;
            $studentName = $student?->user?->name ?? 'Student';
            $amountFormatted = number_format($invoice->total_amount, 2);
            $dueDateFormatted = $invoice->due_date ? $invoice->due_date->format('d M Y') : 'Due on receipt';

            // 1. Notify Student
            if ($student?->user_id) {
                UserNotification::create([
                    'user_id' => $student->user_id,
                    'type'    => 'invoice',
                    'title'   => 'NEW INVOICE ISSUED',
                    'message' => "Invoice #{$invoice->invoice_number} ({$invoice->title}) for RM {$amountFormatted} has been issued. Due: {$dueDateFormatted}.",
                ]);
            }

            // 2. Notify Parents
            $parents = self::getStudentParents($student);
            foreach ($parents as $parent) {
                UserNotification::create([
                    'user_id' => $parent->id,
                    'type'    => 'invoice',
                    'title'   => 'NEW INVOICE ISSUED',
                    'message' => "Invoice #{$invoice->invoice_number} ({$invoice->title}) for {$studentName} (RM {$amountFormatted}) has been issued. Due: {$dueDateFormatted}.",
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('[BillingNotificationService] notifyInvoiceIssued failed: ' . $e->getMessage());
        }
    }

    /**
     * Notify student and parents when a payment is recorded/authorized (full or partial).
     */
    public static function notifyPaymentRecorded(Invoice $invoice, float $amount, ?string $receiptNumber = null, bool $isPartial = false): void
    {
        try {
            $invoice->loadMissing('student.user');
            $student = $invoice->student;
            $studentName = $student?->user?->name ?? 'Student';
            $paidFormatted = number_format($amount, 2);
            $balanceFormatted = number_format($invoice->outstanding_amount, 2);
            $receiptSuffix = $receiptNumber ? " Receipt: {$receiptNumber}." : "";

            if ($isPartial) {
                $studentTitle = 'PARTIAL PAYMENT AUTHORIZED';
                $studentMsg = "Partial payment of RM {$paidFormatted} for '{$invoice->title}' has been recorded. Remaining balance: RM {$balanceFormatted}.{$receiptSuffix}";

                $parentTitle = 'PARTIAL PAYMENT RECORDED';
                $parentMsg = "Partial payment of RM {$paidFormatted} for {$studentName}'s invoice '{$invoice->title}' has been recorded. Remaining balance: RM {$balanceFormatted}.{$receiptSuffix}";
            } else {
                $studentTitle = 'PAYMENT SETTLED';
                $studentMsg = "Payment of RM {$paidFormatted} for '{$invoice->title}' has been fully settled.{$receiptSuffix}";

                $parentTitle = 'PAYMENT SETTLED';
                $parentMsg = "Invoice '{$invoice->title}' for {$studentName} has been settled in full (RM {$paidFormatted}).{$receiptSuffix}";
            }

            if ($student?->user_id) {
                UserNotification::create([
                    'user_id' => $student->user_id,
                    'type'    => 'payment',
                    'title'   => $studentTitle,
                    'message' => $studentMsg,
                ]);
            }

            $parents = self::getStudentParents($student);
            foreach ($parents as $parent) {
                UserNotification::create([
                    'user_id' => $parent->id,
                    'type'    => 'payment',
                    'title'   => $parentTitle,
                    'message' => $parentMsg,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('[BillingNotificationService] notifyPaymentRecorded failed: ' . $e->getMessage());
        }
    }

    /**
     * Notify admins and users when a bank transfer slip is submitted and awaits verification.
     */
    public static function notifyBankTransferSubmitted(Invoice $invoice, Payment $payment, User $payer): void
    {
        try {
            $invoice->loadMissing('student.user');
            $student = $invoice->student;
            $amountFormatted = number_format($payment->amount, 2);

            // Notify payer
            UserNotification::create([
                'user_id' => $payer->id,
                'type'    => 'payment',
                'title'   => 'PAYMENT SLIP SUBMITTED',
                'message' => "Bank transfer slip for invoice #{$invoice->invoice_number} (RM {$amountFormatted}) has been received and is pending verification.",
            ]);

            // Notify student if payer was parent
            if ($student?->user_id && $student->user_id !== $payer->id) {
                UserNotification::create([
                    'user_id' => $student->user_id,
                    'type'    => 'payment',
                    'title'   => 'PAYMENT SLIP SUBMITTED',
                    'message' => "A bank transfer payment of RM {$amountFormatted} for invoice #{$invoice->invoice_number} has been submitted by {$payer->name} and is pending verification.",
                ]);
            }

            // Notify admins
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                UserNotification::create([
                    'user_id' => $admin->id,
                    'type'    => 'payment',
                    'title'   => 'NEW PAYMENT SLIP PENDING',
                    'message' => "New bank transfer slip submitted by {$payer->name} for invoice #{$invoice->invoice_number} (RM {$amountFormatted}).",
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('[BillingNotificationService] notifyBankTransferSubmitted failed: ' . $e->getMessage());
        }
    }

    /**
     * Notify student/parent when admin approves or rejects a bank transfer payment.
     */
    public static function notifyPaymentVerified(Payment $payment, bool $approved, ?string $notes = null): void
    {
        try {
            $payment->loadMissing(['invoice.student.user']);
            $invoice = $payment->invoice;
            $student = $invoice?->student;
            $amountFormatted = number_format($payment->amount, 2);
            $receiptSuffix = $payment->receipt_number ? " Receipt: {$payment->receipt_number}." : "";

            if ($approved) {
                $title = 'PAYMENT VERIFIED';
                $message = "Your bank transfer payment of RM {$amountFormatted} for invoice #{$invoice?->invoice_number} has been verified and approved.{$receiptSuffix}";
            } else {
                $title = 'PAYMENT STATUS UPDATE';
                $reasonSuffix = $notes ? " Reason: {$notes}." : "";
                $message = "Your bank transfer payment of RM {$amountFormatted} for invoice #{$invoice?->invoice_number} could not be verified.{$reasonSuffix}";
            }

            // Notify user who uploaded / payer
            if ($payment->user_id) {
                UserNotification::create([
                    'user_id' => $payment->user_id,
                    'type'    => 'payment',
                    'title'   => $title,
                    'message' => $message,
                ]);
            }

            // Also notify student if different
            if ($student?->user_id && $student->user_id !== $payment->user_id) {
                UserNotification::create([
                    'user_id' => $student->user_id,
                    'type'    => 'payment',
                    'title'   => $title,
                    'message' => $message,
                ]);
            }

            // Also notify parents if student paid
            if ($student) {
                $parents = self::getStudentParents($student);
                foreach ($parents as $parent) {
                    if ($parent->id !== $payment->user_id) {
                        UserNotification::create([
                            'user_id' => $parent->id,
                            'type'    => 'payment',
                            'title'   => $title,
                            'message' => $message,
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('[BillingNotificationService] notifyPaymentVerified failed: ' . $e->getMessage());
        }
    }

    /**
     * Notify user when automated invoice reminder triggers.
     */
    public static function notifyReminderSent(InvoiceReminder $reminder): void
    {
        try {
            $reminder->loadMissing(['invoice.student.user']);
            $invoice = $reminder->invoice;
            if (!$invoice) return;

            $recipientUser = User::where('email', $reminder->recipient_email)->first();
            if (!$recipientUser) return;

            $isOverdue = str_contains($reminder->reminder_type, 'overdue');
            $title = $isOverdue ? 'INVOICE OVERDUE ALERT' : 'PAYMENT DUE REMINDER';
            $balance = number_format($invoice->outstanding_amount, 2);
            $dueDate = $invoice->due_date ? $invoice->due_date->format('d M Y') : 'Due immediately';

            UserNotification::create([
                'user_id' => $recipientUser->id,
                'type'    => 'invoice',
                'title'   => $title,
                'message' => "Payment reminder: Invoice #{$invoice->invoice_number} ({$invoice->title}) has an outstanding balance of RM {$balance}. Due date: {$dueDate}.",
            ]);
        } catch (\Throwable $e) {
            Log::error('[BillingNotificationService] notifyReminderSent failed: ' . $e->getMessage());
        }
    }

    /**
     * Notify student and parents when an invoice due date is adjusted or extended.
     * Note: Does NOT mention "admin" to ensure neutral, institutional tone.
     */
    public static function notifyInvoiceDueDateUpdated(Invoice $invoice, ?string $reason = null, ?string $oldDueDate = null): void
    {
        try {
            $invoice->loadMissing('student.user');
            $student = $invoice->student;
            $studentName = $student?->user?->name ?? 'Student';
            $dueDateFormatted = $invoice->due_date ? $invoice->due_date->format('d M Y') : 'Due immediately';
            $reasonNote = $reason ? " Note: {$reason}." : "";

            // 1. Notify Student
            if ($student?->user_id) {
                UserNotification::create([
                    'user_id' => $student->user_id,
                    'type'    => 'invoice',
                    'title'   => 'PAYMENT DUE DATE UPDATED',
                    'message' => "The payment due date for invoice #{$invoice->invoice_number} ({$invoice->title}) has been updated to {$dueDateFormatted}.{$reasonNote}",
                ]);
            }

            // 2. Notify Parents
            $parents = self::getStudentParents($student);
            foreach ($parents as $parent) {
                UserNotification::create([
                    'user_id' => $parent->id,
                    'type'    => 'invoice',
                    'title'   => 'PAYMENT DUE DATE UPDATED',
                    'message' => "The payment due date for {$studentName}'s invoice #{$invoice->invoice_number} ({$invoice->title}) has been updated to {$dueDateFormatted}.{$reasonNote}",
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('[BillingNotificationService] notifyInvoiceDueDateUpdated failed: ' . $e->getMessage());
        }
    }

    /**
     * Notify student and parents when invoice details are modified.
     * Note: Does NOT mention "admin" to ensure neutral, institutional tone.
     */
    public static function notifyInvoiceUpdated(Invoice $invoice): void
    {
        try {
            $invoice->loadMissing('student.user');
            $student = $invoice->student;
            $studentName = $student?->user?->name ?? 'Student';
            $dueDateFormatted = $invoice->due_date ? $invoice->due_date->format('d M Y') : 'Due immediately';
            $balanceFormatted = number_format($invoice->outstanding_amount, 2);

            // 1. Notify Student
            if ($student?->user_id) {
                UserNotification::create([
                    'user_id' => $student->user_id,
                    'type'    => 'invoice',
                    'title'   => 'INVOICE DETAILS UPDATED',
                    'message' => "Invoice #{$invoice->invoice_number} has been updated. Amount due: RM {$balanceFormatted}. Due date: {$dueDateFormatted}.",
                ]);
            }

            // 2. Notify Parents
            $parents = self::getStudentParents($student);
            foreach ($parents as $parent) {
                UserNotification::create([
                    'user_id' => $parent->id,
                    'type'    => 'invoice',
                    'title'   => 'INVOICE DETAILS UPDATED',
                    'message' => "Invoice #{$invoice->invoice_number} for {$studentName} has been updated. Amount due: RM {$balanceFormatted}. Due date: {$dueDateFormatted}.",
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('[BillingNotificationService] notifyInvoiceUpdated failed: ' . $e->getMessage());
        }
    }

    /**
     * Notify student and parents when invoice status changes.
     * Note: Does NOT mention "admin" to ensure neutral, institutional tone.
     */
    public static function notifyInvoiceStatusUpdated(Invoice $invoice, ?string $oldStatus = null): void
    {
        try {
            $invoice->loadMissing('student.user');
            $student = $invoice->student;
            $studentName = $student?->user?->name ?? 'Student';
            $statusLabel = strtoupper(str_replace('_', ' ', $invoice->status));

            // 1. Notify Student
            if ($student?->user_id) {
                UserNotification::create([
                    'user_id' => $student->user_id,
                    'type'    => 'invoice',
                    'title'   => 'INVOICE STATUS UPDATED',
                    'message' => "The status of invoice #{$invoice->invoice_number} ({$invoice->title}) is now {$statusLabel}.",
                ]);
            }

            // 2. Notify Parents
            $parents = self::getStudentParents($student);
            foreach ($parents as $parent) {
                UserNotification::create([
                    'user_id' => $parent->id,
                    'type'    => 'invoice',
                    'title'   => 'INVOICE STATUS UPDATED',
                    'message' => "The status of {$studentName}'s invoice #{$invoice->invoice_number} ({$invoice->title}) is now {$statusLabel}.",
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('[BillingNotificationService] notifyInvoiceStatusUpdated failed: ' . $e->getMessage());
        }
    }
}

