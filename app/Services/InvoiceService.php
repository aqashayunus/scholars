<?php

namespace App\Services;

use App\Models\FeeStructure;
use App\Models\FinancialAuditLog;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceReminder;
use App\Models\Setting;
use App\Models\Student;
use App\Services\PackagePricingService;
use App\Services\PricingResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    // ── Invoice Generation ────────────────────────────────────────────────────

    /**
     * Generate a new invoice for a student whose enrollment has just been confirmed/activated.
     *
     * Flow:
     *   1. Load active enrollments → courses → subjects with monthly_fee
     *   2. Snapshot each subject's current fee into invoice_items
     *   3. Create the invoice (status = issued)
     *   4. Schedule all 7 reminder records
     *   5. Dispatch the "invoice issued" email (queued)
     *   6. Write audit log
     *
     * @throws \RuntimeException if the student has no active enrollments with a configured fee.
     */
    public function generateForStudent(Student $student, ?int $feeStructureId = null): Invoice
    {
        // Load active enrollments with their course and subject
        $student->loadMissing('activeEnrollments.course.subject');

        $enrollments = $student->activeEnrollments;

        // Gather subjects (unique, with a configured fee)
        $subjects = $enrollments
            ->map(fn ($e) => $e->course?->subject)
            ->filter()
            ->unique('id')
            ->values();

        if ($subjects->isEmpty()) {
            throw new \RuntimeException(
                "Student #{$student->id} has no active enrollments with subjects. Cannot generate invoice."
            );
        }

        $dueDays       = (int) Setting::get('billing.invoice_due_days', 7);
        $billingPeriod = now()->format('F Y');    // e.g. "September 2026"
        $issueDate     = now()->toDateString();
        $dueDate       = now()->addDays($dueDays)->toDateString();

        $feeStructure = $feeStructureId ? \App\Models\FeeStructure::where('is_active', true)->find($feeStructureId) : null;

        return DB::transaction(function () use ($student, $subjects, $billingPeriod, $issueDate, $dueDate, $feeStructure) {
            $studentName = $student->user->name ?? "Student #{$student->id}";
            $invoiceNumber = $this->generateInvoiceNumber();

            if ($feeStructure) {
                $isNewEnginePackage = $feeStructure->hasItems()
                    || $feeStructure->priceTiers()->exists()
                    || $feeStructure->hasSubjects()
                    || ($feeStructure->package_type && $feeStructure->package_type !== FeeStructure::TYPE_INDIVIDUAL);

                if ($isNewEnginePackage) {
                    $pricing = app(PackagePricingService::class)->calculate($feeStructure, $subjects->pluck('id')->toArray());

                    $invoiceTitle = "{$pricing->packageName} {$billingPeriod} {$studentName}";

                    $invoice = Invoice::create([
                        'student_id'       => $student->id,
                        'fee_structure_id' => $feeStructure->id,
                        'invoice_number'   => $invoiceNumber,
                        'title'            => $invoiceTitle,
                        'billing_period'   => $billingPeriod,
                        'subtotal'         => $pricing->subtotal,
                        'discount_amount'  => $pricing->discountAmount,
                        'total_amount'     => $pricing->totalAmount,
                        'amount_paid'      => 0.00,
                        'issued_date'      => $issueDate,
                        'due_date'         => $dueDate,
                        'status'           => Invoice::STATUS_ISSUED,
                    ]);

                    foreach ($pricing->lineItems as $line) {
                        InvoiceItem::create(array_merge(
                            ['invoice_id' => $invoice->id],
                            $line
                        ));
                    }
                } else {
                    // Separate Core package subjects from Elective add-ons dynamically (Legacy)
                    $coreSubjects = $subjects->filter(fn ($s) => strtolower($s->category ?? 'core') === 'core')->values();
                    $electiveSubjects = $subjects->filter(fn ($s) => strtolower($s->category ?? '') !== 'core')->values();

                    // Fallback: If no subjects are categorized as 'core', treat all as package subjects
                    if ($coreSubjects->isEmpty()) {
                        $coreSubjects = $subjects;
                        $electiveSubjects = collect();
                    }

                    $packageAmount = (float) $feeStructure->amount;
                    $coreRegularTotal = $coreSubjects->sum(fn ($s) => (float) ($s->monthly_fee ?? 0));

                    $electiveDiscountPct = (float) Setting::get('billing.bundle_elective_discount_pct', 0);
                    $electiveRegularTotal = $electiveSubjects->sum(fn ($s) => (float) ($s->monthly_fee ?? 0));
                    $electiveDiscountedTotal = $electiveSubjects->sum(function ($s) use ($electiveDiscountPct) {
                        $rate = (float) ($s->monthly_fee ?? 0);
                        return round($rate * (1 - ($electiveDiscountPct / 100)), 2);
                    });

                    $subtotal = $coreRegularTotal + $electiveRegularTotal;
                    $effectivePackagePrice = min($subtotal, $packageAmount);
                    $totalAmount = $effectivePackagePrice + $electiveDiscountedTotal;
                    $discountAmount = max(0, $subtotal - $totalAmount);

                    $invoiceTitle = "{$feeStructure->name} {$billingPeriod} {$studentName}";

                    $invoice = Invoice::create([
                        'student_id'       => $student->id,
                        'fee_structure_id' => $feeStructure->id,
                        'invoice_number'   => $invoiceNumber,
                        'title'            => $invoiceTitle,
                        'billing_period'   => $billingPeriod,
                        'subtotal'         => $subtotal,
                        'discount_amount'  => $discountAmount,
                        'total_amount'     => $totalAmount,
                        'amount_paid'      => 0.00,
                        'issued_date'      => $issueDate,
                        'due_date'         => $dueDate,
                        'status'           => Invoice::STATUS_ISSUED,
                    ]);

                    // Line item for the Core Package
                    $coreNames = $coreSubjects->pluck('name')->implode(', ');
                    InvoiceItem::create([
                        'invoice_id'  => $invoice->id,
                        'description' => "{$feeStructure->name} ({$coreNames})",
                        'unit_price'  => $effectivePackagePrice,
                        'quantity'    => 1,
                        'amount'      => $effectivePackagePrice,
                    ]);

                    // Line items for any elective add-on subjects
                    foreach ($electiveSubjects as $elective) {
                        $unitPrice = round((float) ($elective->monthly_fee ?? 0) * (1 - ($electiveDiscountPct / 100)), 2);
                        $desc = $elective->name . ($electiveDiscountPct > 0 ? " ({$electiveDiscountPct}% Bundle Add-on Promo)" : '');
                        InvoiceItem::create([
                            'invoice_id'  => $invoice->id,
                            'subject_id'  => $elective->id,
                            'description' => $desc,
                            'unit_price'  => $unitPrice,
                            'quantity'    => 1,
                            'amount'      => $unitPrice,
                        ]);
                    }
                }
            } else {
                // Calculate totals from snapshotted fees (unbundled)
                $subtotal = $subjects->sum(fn ($s) => (float) ($s->monthly_fee ?? 0));

                $invoice = Invoice::create([
                    'student_id'      => $student->id,
                    'invoice_number'  => $invoiceNumber,
                    'title'           => "Tuition Fee {$billingPeriod} {$studentName}",
                    'billing_period'  => $billingPeriod,
                    'subtotal'        => $subtotal,
                    'discount_amount' => 0.00,
                    'total_amount'    => $subtotal,
                    'amount_paid'     => 0.00,
                    'issued_date'     => $issueDate,
                    'due_date'        => $dueDate,
                    'status'          => Invoice::STATUS_ISSUED,
                ]);

                // Create line items (price-snapshotted from subject.monthly_fee)
                foreach ($subjects as $subject) {
                    $fee = (float) ($subject->monthly_fee ?? 0);
                    InvoiceItem::create([
                        'invoice_id'  => $invoice->id,
                        'subject_id'  => $subject->id,
                        'description' => $subject->name,   // snapshot of name
                        'unit_price'  => $fee,             // snapshot of price
                        'quantity'    => 1,
                        'amount'      => $fee,
                    ]);
                }
            }

            // Schedule all reminder records
            $this->scheduleReminders($invoice, $student);

            // Dispatch the "invoice issued" email immediately (via queue)
            $this->dispatchIssuedEmail($invoice);

            // In-app alert for student & parent
            \App\Services\BillingNotificationService::notifyInvoiceIssued($invoice);

            // Audit log
            $this->log('invoice_issued', 'Invoice', $invoice->id, $student->user_id ?? null, [
                'invoice_number'   => $invoiceNumber,
                'total_amount'     => $invoice->total_amount,
                'fee_structure_id' => $feeStructure?->id,
                'subjects'         => $subjects->pluck('name')->implode(', '),
            ]);

            return $invoice->fresh(['items', 'student.user']);
        });
    }

    // ── New Package Engine Path ───────────────────────────────────────────────

    /**
     * Generate an invoice using a pre-calculated PricingResult from PackagePricingService.
     *
     * This is the NEW canonical path for package-based billing.
     * The old generateForStudent() remains for backward compatibility.
     *
     * Flow:
     *   PackagePricingService::calculate() → PricingResult
     *   InvoiceService::generateFromPricingResult() → Invoice + InvoiceItems
     *
     * Historical invoice immutability is guaranteed by:
     *   - Snapshotting all values at invoice creation time
     *   - Never reading live package prices when displaying old invoices
     *   - Using invoice_items.package_name, item_type, billing_frequency snapshots
     *
     * @param  Student       $student
     * @param  PricingResult $pricing   Result from PackagePricingService::calculate()
     * @param  array         $options   Optional: ['billing_period', 'title', 'notes']
     * @return Invoice
     */
    public function generateFromPricingResult(Student $student, PricingResult $pricing, array $options = []): Invoice
    {
        $dueDays       = (int) Setting::get('billing.invoice_due_days', 7);
        $billingPeriod = $options['billing_period'] ?? now()->format('F Y');
        $issueDate     = now()->toDateString();
        $dueDate       = now()->addDays($dueDays)->toDateString();
        $studentName   = $student->user->name ?? "Student #{$student->id}";

        return DB::transaction(function () use ($student, $pricing, $billingPeriod, $issueDate, $dueDate, $studentName, $options) {
            $invoiceNumber = $this->generateInvoiceNumber();

            $title = $options['title']
                ?? "{$pricing->packageName} {$billingPeriod} {$studentName}";

            // Create the invoice with snapshotted totals
            $invoice = Invoice::create([
                'student_id'       => $student->id,
                'fee_structure_id' => $pricing->feeStructureId,
                'invoice_number'   => $invoiceNumber,
                'title'            => $title,
                'description'      => $options['description'] ?? null,
                'notes'            => $options['notes'] ?? null,
                'billing_period'   => $billingPeriod,
                'subtotal'         => $pricing->subtotal,
                'discount_amount'  => $pricing->discountAmount,
                'total_amount'     => $pricing->totalAmount,
                'amount_paid'      => 0.00,
                'issued_date'      => $issueDate,
                'due_date'         => $dueDate,
                'status'           => Invoice::STATUS_ISSUED,
            ]);

            // Create snapshotted line items (immutable historical record)
            foreach ($pricing->lineItems as $line) {
                InvoiceItem::create(array_merge(
                    ['invoice_id' => $invoice->id],
                    $line
                ));
            }

            // Schedule reminder records
            $this->scheduleReminders($invoice, $student);

            // Dispatch the "invoice issued" email
            $this->dispatchIssuedEmail($invoice);

            // In-app notification
            \App\Services\BillingNotificationService::notifyInvoiceIssued($invoice);

            // Audit log
            $this->log('invoice_issued', 'Invoice', $invoice->id, $student->user_id ?? null, [
                'invoice_number'   => $invoiceNumber,
                'total_amount'     => $invoice->total_amount,
                'fee_structure_id' => $pricing->feeStructureId,
                'package_name'     => $pricing->packageName,
                'package_type'     => $pricing->packageType,
                'subject_ids'      => $pricing->subjectIds,
                'line_items_count' => count($pricing->lineItems),
            ]);

            return $invoice->fresh(['items', 'student.user']);
        });
    }

    // ── Invoice Number ────────────────────────────────────────────────────────

    /**
     * Generate a unique, sequential invoice number.
     * Format: INV-YYYY-NNNNN
     */
    public function generateInvoiceNumber(): string
    {
        $year   = date('Y');
        $prefix = "INV-{$year}-";
        $last   = Invoice::where('invoice_number', 'like', "{$prefix}%")
                         ->orderByDesc('invoice_number')
                         ->lockForUpdate()
                         ->value('invoice_number');

        $nextSeq = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad($nextSeq, 5, '0', STR_PAD_LEFT);
    }

    // ── Amount Paid Recalculation ─────────────────────────────────────────────

    /**
     * Recalculate invoice.amount_paid from all successful payment allocations.
     * Then determine correct status: issued / partially_paid / paid.
     * Called after any payment is approved or recorded.
     */
    public function recalculateAmountPaid(Invoice $invoice): void
    {
        $totalPaid = $invoice->paymentInvoices()
            ->whereHas('payment', fn ($q) => $q->where('status', \App\Models\Payment::STATUS_SUCCESSFUL))
            ->sum('amount_applied');

        $totalPaid = (float) $totalPaid;
        $totalDue  = (float) $invoice->total_amount;

        if ($totalPaid >= $totalDue) {
            $newStatus = Invoice::STATUS_PAID;
        } elseif ($totalPaid > 0) {
            $newStatus = Invoice::STATUS_PARTIALLY_PAID;
        } elseif ($invoice->isPastDue()) {
            $newStatus = Invoice::STATUS_OVERDUE;
        } else {
            $newStatus = Invoice::STATUS_ISSUED;
        }

        $invoice->update([
            'amount_paid' => $totalPaid,
            'status'      => $newStatus,
        ]);

        // Synchronize student's restriction status immediately
        if ($invoice->student) {
            app(\App\Services\StudentAccessRestrictionService::class)->syncStudentStatus($invoice->student);
        }

        // If now fully paid, stop all pending reminders
        if ($newStatus === Invoice::STATUS_PAID) {
            app(ReminderService::class)->skipRemainingReminders($invoice);

            // Send payment received email
            $this->dispatchPaymentReceivedEmail($invoice);

            $this->log('invoice_paid', 'Invoice', $invoice->id, null, [
                'invoice_number' => $invoice->invoice_number,
                'amount_paid'    => $totalPaid,
            ]);
        }
    }

    // ── Mark Overdue ──────────────────────────────────────────────────────────

    /**
     * Scan all unpaid invoices past their due date and mark them OVERDUE.
     * Called daily by the scheduler command.
     */
    public function markOverdueInvoices(): int
    {
        $invoices = Invoice::whereIn('status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PARTIALLY_PAID])
            ->where('due_date', '<', now()->toDateString())
            ->with('student')
            ->get();

        $count = 0;
        foreach ($invoices as $inv) {
            $inv->update(['status' => Invoice::STATUS_OVERDUE]);
            $count++;
            if ($inv->student) {
                app(\App\Services\StudentAccessRestrictionService::class)->syncStudentStatus($inv->student);
            }
        }

        if ($count > 0) {
            Log::info("[InvoiceService] Marked {$count} invoice(s) as OVERDUE.");
        }

        return $count;
    }

    // ── Reminder Scheduling ───────────────────────────────────────────────────

    /**
     * Create InvoiceReminder records for all configured reminder types.
     * Skips any that are disabled in settings.
     */
    public function scheduleReminders(Invoice $invoice, Student $student): void
    {
        $dueDate = $invoice->due_date;

        $schedule = [
            'invoice_issued'  => ['date' => now(),                           'setting' => 'billing.reminder_invoice_issued'],
            'due_7_days'      => ['date' => $dueDate->copy()->subDays(7),    'setting' => 'billing.reminder_due_7_days'],
            'due_3_days'      => ['date' => $dueDate->copy()->subDays(3),    'setting' => 'billing.reminder_due_3_days'],
            'due_today'       => ['date' => $dueDate->copy(),                'setting' => 'billing.reminder_due_today'],
            'overdue_1_day'   => ['date' => $dueDate->copy()->addDays(1),    'setting' => 'billing.reminder_overdue_1_day'],
            'overdue_3_days'  => ['date' => $dueDate->copy()->addDays(3),    'setting' => 'billing.reminder_overdue_3_days'],
            'overdue_7_days'  => ['date' => $dueDate->copy()->addDays(7),    'setting' => 'billing.reminder_overdue_7_days'],
        ];

        // Collect all recipients: student + all verified parents
        $recipients = $this->getRecipients($student);

        foreach ($schedule as $type => $config) {
            // Skip if disabled in settings
            if (!Setting::get($config['setting'], '1')) {
                continue;
            }

            $scheduledAt = $config['date']->startOfDay();

            foreach ($recipients as $recipient) {
                InvoiceReminder::firstOrCreate(
                    [
                        'invoice_id'      => $invoice->id,
                        'reminder_type'   => $type,
                        'recipient_email' => $recipient['email'],
                    ],
                    [
                        'recipient_type' => $recipient['type'],
                        'scheduled_at'   => $scheduledAt,
                        'status'         => 'pending',
                    ]
                );
            }
        }
    }

    /**
     * Collect student and parent email recipients for an invoice.
     */
    private function getRecipients(Student $student): array
    {
        $recipients = [];

        // Student
        if ($student->user && $student->user->email) {
            $recipients[] = ['email' => $student->user->email, 'type' => 'student'];
        }

        // Parents (via parent_student pivot)
        $student->loadMissing('parents');
        foreach ($student->parents as $parentUser) {
            if ($parentUser->email) {
                $recipients[] = ['email' => $parentUser->email, 'type' => 'parent'];
            }
        }

        return $recipients;
    }

    // ── Email Dispatch ────────────────────────────────────────────────────────

    private function dispatchIssuedEmail(Invoice $invoice): void
    {
        // Get the "invoice_issued" reminder records and dispatch job for each
        $reminders = InvoiceReminder::where('invoice_id', $invoice->id)
            ->where('reminder_type', 'invoice_issued')
            ->where('status', 'pending')
            ->get();

        foreach ($reminders as $reminder) {
            \App\Jobs\SendInvoiceReminderJob::dispatch($reminder)->onQueue('emails');
        }
    }

    private function dispatchPaymentReceivedEmail(Invoice $invoice): void
    {
        $invoice->loadMissing(['student.user', 'student.parents', 'items']);

        // Collect recipients
        $student = $invoice->student;
        $recipients = $this->getRecipients($student);

        foreach ($recipients as $recipient) {
            \App\Mail\PaymentReceivedMail::dispatchToRecipient($invoice, $recipient);
        }
    }

    // ── Audit Log ─────────────────────────────────────────────────────────────

    private function log(string $action, string $targetType, int $targetId, ?int $userId, array $details = []): void
    {
        try {
            FinancialAuditLog::create([
                'user_id'     => $userId ?? 0,
                'action'      => $action,
                'target_type' => $targetType,
                'target_id'   => $targetId,
                'ip_address'  => request()->ip(),
                'details'     => json_encode($details),
            ]);
        } catch (\Exception $e) {
            Log::error("[InvoiceService] Audit log failed: " . $e->getMessage());
        }
    }
}
