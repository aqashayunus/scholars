<?php

namespace App\Services\Admin;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Enrollment;
use App\Models\FeeStructure;
use App\Models\FinancialAuditLog;
use App\Models\Invoice;
use App\Models\InvoiceReminder;
use App\Models\Payment;
use App\Models\PaymentInvoice;
use App\Models\Setting;
use App\Services\BillingNotificationService;
use App\Services\InvoiceService;
use App\Services\PackagePricingService;
use App\Services\StudentAccessRestrictionService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BillingAdminService
{
    public function __construct(
        private PackagePricingService $pricing,
        private StudentAccessRestrictionService $accessRestriction,
        private InvoiceService $invoices,
    ) {}

    // ── CSV Exports ──────────────────────────────────────────────────────────────

    public function exportPaymentsCsv(): StreamedResponse
    {
        $payments = Payment::with(['user', 'course'])->latest('id')->get();
        $symbol = Setting::get('currency_symbol', 'RM');
        $fileName = strtolower(str_replace(' ', '_', Setting::get('platform_name', 'olympiascholar'))) . '_payments_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($payments, $symbol) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Invoice ID', 'Payer Name', 'Payer Email', 'Course Item', 'Payment Method', "Amount ({$symbol})", 'Status', 'Payment Date']);

            foreach ($payments as $pay) {
                fputcsv($handle, [
                    'INV-' . str_pad($pay->id, 5, '0', STR_PAD_LEFT),
                    $pay->user->name ?? 'User #' . $pay->user_id,
                    $pay->user->email ?? 'N/A',
                    $pay->course->title ?? $pay->description ?? 'Tuition Fee',
                    $pay->payment_method ?? 'FPX Online',
                    number_format($pay->amount, 2, '.', ''),
                    strtoupper($pay->status?->value ?? 'PAID'),
                    $pay->paid_at ? $pay->paid_at->format('Y-m-d H:i') : ($pay->created_at ? $pay->created_at->format('Y-m-d H:i') : 'N/A'),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    public function exportEnrollmentsCsv(): StreamedResponse
    {
        $enrollments = Enrollment::with(['student.user', 'course.subject', 'course.teacher.user'])->latest('id')->get();
        $fileName = strtolower(str_replace(' ', '_', Setting::get('platform_name', 'olympiascholar'))) . '_enrollments_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($enrollments) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Enrollment ID', 'Student Name', 'Student Email', 'Course Title', 'Subject', 'Teacher Name', 'Progress (%)', 'Enrolled Date']);

            foreach ($enrollments as $enr) {
                fputcsv($handle, [
                    'ENR-' . str_pad($enr->id, 5, '0', STR_PAD_LEFT),
                    $enr->student?->user?->name ?? 'Student #' . $enr->student_id,
                    $enr->student?->user?->email ?? 'N/A',
                    $enr->course->title ?? 'Course #' . $enr->course_id,
                    $enr->course->subject->name ?? 'General',
                    $enr->course->teacher->user->name ?? 'Staff',
                    $enr->progress_pct ?? 0,
                    $enr->enrolled_at ? $enr->enrolled_at->format('Y-m-d H:i') : ($enr->created_at ? $enr->created_at->format('Y-m-d H:i') : 'N/A'),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    // ── Fee Structure CRUD ───────────────────────────────────────────────────────

    public function createFeeStructure(Request $request): FeeStructure
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string',
            'amount'          => 'required|numeric|min:0',
            'education_level' => 'required|string|max:100',
            'frequency'       => 'required|in:monthly,term,one_time,yearly',
            'is_active'       => 'nullable',
            'student_id'      => 'nullable|exists:students,id',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        if (empty($validated['student_id'])) {
            $validated['student_id'] = null;
        }

        $fee = FeeStructure::create($validated);

        FinancialAuditLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'package_created',
            'target_type' => 'FeeStructure',
            'ip_address'  => $request->ip(),
            'details'     => "Created fee structure '{$fee->name}' at RM {$fee->amount}" . ($fee->student_id ? " (Targeted to student #{$fee->student_id})" : ""),
        ]);

        return $fee;
    }

    public function updateFeeStructure(FeeStructure $fee, Request $request): FeeStructure
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string',
            'amount'          => 'required|numeric|min:0',
            'education_level' => 'required|string|max:100',
            'frequency'       => 'required|in:monthly,term,one_time,yearly',
            'is_active'       => 'nullable',
            'student_id'      => 'nullable|exists:students,id',
            // Package engine fields
            'package_type'     => 'nullable|in:individual,fixed_bundle,pick_n,tiered,custom',
            'minimum_subjects' => 'nullable|integer|min:1|max:20',
            'maximum_subjects' => 'nullable|integer|min:1|max:20|gte:minimum_subjects',
            'effective_from'   => 'nullable|date',
            'effective_until'  => 'nullable|date|after_or_equal:effective_from',
            'status'           => 'nullable|in:draft,active,inactive,archived',
            'subjects'               => 'nullable|array',
            'subjects.*.subject_id'  => 'required|exists:subjects,id',
            'subjects.*.is_required' => 'required|boolean',
            'subjects.*.sort_order'  => 'nullable|integer|min:0',
        ]);

        $validated['is_active']  = $request->boolean('is_active', true);
        $validated['student_id'] = $request->filled('student_id') ? $validated['student_id'] : null;

        $oldValues = $fee->only(['name', 'amount', 'package_type', 'status']);
        $fee->update(\Illuminate\Support\Arr::except($validated, ['subjects']));

        // Sync subjects inline when submitted alongside the general update
        // (the dedicated updateFeeStructureSubjects endpoint still exists
        // separately for the package-builder UI's own save action).
        if (in_array($validated['package_type'] ?? $fee->package_type, ['fixed_bundle', 'pick_n'])) {
            $syncData = [];
            foreach ($validated['subjects'] ?? [] as $idx => $sub) {
                $syncData[$sub['subject_id']] = [
                    'is_required' => (bool) $sub['is_required'],
                    'sort_order'  => (int) ($sub['sort_order'] ?? $idx),
                ];
            }
            if (!empty($syncData)) {
                $fee->subjects()->sync($syncData);
            }
        } elseif (($validated['package_type'] ?? null) === 'individual') {
            $fee->subjects()->detach();
        }

        FinancialAuditLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'package_updated',
            'target_type' => 'FeeStructure',
            'target_id'   => $fee->id,
            'ip_address'  => $request->ip(),
            'details'     => json_encode([
                'old' => $oldValues,
                'new' => $fee->only(['name', 'amount', 'package_type', 'status']),
            ]),
        ]);

        return $fee;
    }

    public function deleteFeeStructure(int $id): string
    {
        $fee = FeeStructure::findOrFail($id);
        $name = $fee->name;
        // Soft delete (SoftDeletes trait on FeeStructure)
        // Invoice references (fee_structure_id) remain intact for history
        $fee->delete();

        return $name;
    }

    // ── Package Engine ───────────────────────────────────────────────────────────

    /**
     * Replace all subject associations for a fee structure package.
     */
    public function syncFeeStructureSubjects(FeeStructure $fee, Request $request): void
    {
        $validated = $request->validate([
            'subjects'                  => 'nullable|array',
            'subjects.*.subject_id'     => 'required|exists:subjects,id',
            'subjects.*.is_required'    => 'required|boolean',
            'subjects.*.sort_order'     => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($fee, $validated, $request) {
            $syncData = [];
            foreach ($validated['subjects'] ?? [] as $idx => $sub) {
                $syncData[$sub['subject_id']] = [
                    'is_required' => (bool) $sub['is_required'],
                    'sort_order'  => (int) ($sub['sort_order'] ?? $idx),
                ];
            }
            $fee->subjects()->sync($syncData);

            FinancialAuditLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'subject_sync',
                'target_type' => 'FeeStructure',
                'target_id'   => $fee->id,
                'ip_address'  => $request->ip(),
                'details'     => json_encode(['subject_ids' => array_keys($syncData)]),
            ]);
        });
    }

    /**
     * Replace all billing components (items) for a fee structure.
     */
    public function syncFeeStructureItems(FeeStructure $fee, Request $request): void
    {
        $validated = $request->validate([
            'items'                      => 'nullable|array',
            'items.*.name'               => 'required|string|max:255',
            'items.*.description'        => 'nullable|string',
            'items.*.item_type'          => 'required|in:registration,tuition,material,resource,assessment,deposit,other',
            'items.*.amount'             => 'required|numeric|min:0',
            'items.*.quantity'           => 'nullable|integer|min:1',
            'items.*.billing_frequency'  => 'required|in:one_time,monthly,term,annual',
            'items.*.is_optional'        => 'nullable|boolean',
            'items.*.is_refundable'      => 'nullable|boolean',
            'items.*.sort_order'         => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($fee, $validated, $request) {
            $fee->items()->delete();

            foreach ($validated['items'] ?? [] as $idx => $item) {
                $fee->items()->create([
                    'name'              => $item['name'],
                    'description'       => $item['description'] ?? null,
                    'item_type'         => $item['item_type'],
                    'amount'            => $item['amount'],
                    'quantity'          => $item['quantity'] ?? 1,
                    'billing_frequency' => $item['billing_frequency'],
                    'is_optional'       => $item['is_optional'] ?? false,
                    'is_refundable'     => $item['is_refundable'] ?? false,
                    'sort_order'        => $item['sort_order'] ?? $idx,
                ]);
            }

            FinancialAuditLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'billing_component_sync',
                'target_type' => 'FeeStructure',
                'target_id'   => $fee->id,
                'ip_address'  => $request->ip(),
                'details'     => json_encode(['items_count' => count($validated['items'] ?? [])]),
            ]);
        });
    }

    /**
     * Replace all pricing tiers for a tiered fee structure.
     */
    public function syncFeeStructureTiers(FeeStructure $fee, Request $request): void
    {
        $validated = $request->validate([
            'tiers'                       => 'required|array|min:1',
            'tiers.*.minimum_subjects'    => 'required|integer|min:1',
            'tiers.*.maximum_subjects'    => 'required|integer|min:1|gte:tiers.*.minimum_subjects',
            'tiers.*.price'               => 'required|numeric|min:0',
            'tiers.*.sort_order'          => 'nullable|integer|min:0',
        ]);

        // Validate non-overlapping tiers
        $this->pricing->validateTiers($validated['tiers']);

        DB::transaction(function () use ($fee, $validated, $request) {
            $fee->priceTiers()->delete();

            foreach ($validated['tiers'] as $idx => $tier) {
                $fee->priceTiers()->create([
                    'minimum_subjects' => $tier['minimum_subjects'],
                    'maximum_subjects' => $tier['maximum_subjects'],
                    'price'            => $tier['price'],
                    'sort_order'       => $tier['sort_order'] ?? $idx,
                ]);
            }

            FinancialAuditLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'pricing_changed',
                'target_type' => 'FeeStructure',
                'target_id'   => $fee->id,
                'ip_address'  => $request->ip(),
                'details'     => json_encode(['tiers' => $validated['tiers']]),
            ]);
        });
    }

    /**
     * Duplicate an existing fee structure (copy config, subjects, items, tiers).
     * Does NOT copy enrollments, invoices, or payments.
     */
    public function duplicateFeeStructure(int $id, Request $request): FeeStructure
    {
        $original = FeeStructure::with(['subjects', 'items', 'priceTiers'])->findOrFail($id);

        return DB::transaction(function () use ($original, $request) {
            $new = $original->replicate(['student_id', 'parent_fee_structure_id', 'version', 'deleted_at']);
            $new->name      = 'Copy of ' . $original->name;
            $new->status    = FeeStructure::STATUS_DRAFT;
            $new->is_active = false;
            $new->version   = 1;
            $new->parent_fee_structure_id = null;
            $new->save();

            foreach ($original->subjects as $subject) {
                $new->subjects()->attach($subject->id, [
                    'is_required' => $subject->pivot->is_required,
                    'sort_order'  => $subject->pivot->sort_order,
                ]);
            }

            foreach ($original->items as $item) {
                $new->items()->create($item->only([
                    'name', 'description', 'item_type', 'amount',
                    'quantity', 'billing_frequency', 'is_optional', 'is_refundable', 'sort_order',
                ]));
            }

            foreach ($original->priceTiers as $tier) {
                $new->priceTiers()->create($tier->only(['minimum_subjects', 'maximum_subjects', 'price', 'sort_order']));
            }

            FinancialAuditLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'package_duplicated',
                'target_type' => 'FeeStructure',
                'target_id'   => $new->id,
                'ip_address'  => $request->ip(),
                'details'     => json_encode(['original_id' => $original->id, 'original_name' => $original->name]),
            ]);

            return $new;
        });
    }

    /**
     * Create a new version of an existing fee structure.
     * Existing students remain on the old version.
     */
    public function createFeeStructureVersion(int $id, Request $request): FeeStructure
    {
        $original = FeeStructure::with(['subjects', 'items', 'priceTiers'])->findOrFail($id);

        $validated = $request->validate([
            'amount'          => 'nullable|numeric|min:0',
            'effective_from'  => 'nullable|date',
            'effective_until' => 'nullable|date|after_or_equal:effective_from',
            'notes'           => 'nullable|string|max:500',
        ]);

        return DB::transaction(function () use ($original, $validated, $request) {
            $original->update(['status' => FeeStructure::STATUS_INACTIVE]);

            $new = $original->replicate(['deleted_at', 'version']);
            $new->version = $original->version + 1;
            $new->name    = $original->name . ' v' . $new->version;
            $new->parent_fee_structure_id = $original->parent_fee_structure_id ?? $original->id;
            $new->status    = FeeStructure::STATUS_DRAFT;
            $new->is_active = false;

            if (isset($validated['amount']))          $new->amount         = $validated['amount'];
            if (isset($validated['effective_from']))  $new->effective_from = $validated['effective_from'];
            if (isset($validated['effective_until'])) $new->effective_until = $validated['effective_until'];

            $new->save();

            foreach ($original->subjects as $subject) {
                $new->subjects()->attach($subject->id, [
                    'is_required' => $subject->pivot->is_required,
                    'sort_order'  => $subject->pivot->sort_order,
                ]);
            }

            foreach ($original->items as $item) {
                $new->items()->create($item->only([
                    'name', 'description', 'item_type', 'amount',
                    'quantity', 'billing_frequency', 'is_optional', 'is_refundable', 'sort_order',
                ]));
            }

            foreach ($original->priceTiers as $tier) {
                $new->priceTiers()->create($tier->only(['minimum_subjects', 'maximum_subjects', 'price', 'sort_order']));
            }

            FinancialAuditLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'package_version_created',
                'target_type' => 'FeeStructure',
                'target_id'   => $new->id,
                'ip_address'  => $request->ip(),
                'details'     => json_encode([
                    'original_id'  => $original->id,
                    'new_version'  => $new->version,
                    'notes'        => $validated['notes'] ?? null,
                ]),
            ]);

            return $new;
        });
    }

    /**
     * API: Get subjects for a fee structure (for JS live preview in registration).
     */
    public function feeStructureSubjectsForApi(int $id): array
    {
        $fee = FeeStructure::with('subjects')->findOrFail($id);

        return [
            'package_type'     => $fee->package_type ?? 'individual',
            'minimum_subjects' => $fee->minimum_subjects,
            'maximum_subjects' => $fee->maximum_subjects,
            'subjects'         => $fee->subjects->map(fn ($s) => [
                'id'          => $s->id,
                'name'        => $s->name,
                'is_required' => (bool) $s->pivot->is_required,
                'monthly_fee' => (float) ($s->monthly_fee ?? 0),
            ]),
        ];
    }

    /**
     * API: Return a pricing preview for a fee structure + subject selection.
     * Used by the registration form's live price preview.
     * NOTE: This is for UX only — server always recalculates on final submit.
     *
     * @return array{error?: string, status: int, pricing?: array}
     */
    public function previewFeeStructurePricing(int $id, Request $request): array
    {
        $fee = FeeStructure::with(['subjects', 'items', 'priceTiers'])->findOrFail($id);

        if (!$fee->is_available) {
            return ['error' => 'Package is not available.', 'status' => 422];
        }

        $subjectIds = array_map('intval', array_filter((array) $request->query('subject_ids', [])));

        try {
            $pricing = $this->pricing->calculate($fee, $subjectIds);
            return ['pricing' => $pricing->toArray(), 'status' => 200];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage(), 'status' => 422];
        }
    }

    // ── Invoice CRUD (Admin) ─────────────────────────────────────────────────────

    public function updateInvoice(Invoice $invoice, Request $request): Invoice
    {
        $oldDueDate = $invoice->due_date ? $invoice->due_date->toDateString() : null;

        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'billing_period' => 'nullable|string|max:100',
            'due_date'       => 'required|date',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $dueDateChanged = $oldDueDate !== Carbon::parse($validated['due_date'])->toDateString();

        if (isset($validated['notes']) && !Schema::hasColumn('invoices', 'notes')) {
            $validated['description'] = $validated['notes'];
            unset($validated['notes']);
        }

        $invoice->update($validated);

        if ($dueDateChanged) {
            $this->syncInvoiceDueDateChange($invoice, 'Updated via invoice editor', $oldDueDate);
        } else {
            BillingNotificationService::notifyInvoiceUpdated($invoice);
        }

        return $invoice;
    }

    /**
     * @return array{invoice: Invoice, message: string}
     */
    public function updateInvoiceDueDate(Invoice $invoice, Request $request): array
    {
        $oldDueDate = $invoice->due_date ? $invoice->due_date->toDateString() : null;

        $validated = $request->validate([
            'due_date' => 'required|date',
            'reason'   => 'nullable|string|max:255',
        ]);

        $invoice->due_date = $validated['due_date'];
        $invoice->save();

        $this->syncInvoiceDueDateChange($invoice, $validated['reason'] ?? null, $oldDueDate);

        $message = "Due date for invoice {$invoice->invoice_number} updated to " . $invoice->due_date->format('d M Y') . ".";

        return ['invoice' => $invoice->fresh(), 'message' => $message];
    }

    public function updateDefaultDueDays(Request $request): void
    {
        $validated = $request->validate([
            'invoice_due_days'             => 'required|integer|min:1|max:180',
            'bundle_elective_discount_pct' => 'nullable|numeric|min:0|max:100',
        ]);

        Setting::set('billing.invoice_due_days', (string)$validated['invoice_due_days'], 'billing', 'Invoice Due Days (after issue date)');
        if (isset($validated['bundle_elective_discount_pct'])) {
            Setting::set('billing.bundle_elective_discount_pct', (string)$validated['bundle_elective_discount_pct'], 'billing', 'Bundle Elective Add-on Discount (%)');
        }
        Setting::clearCache();

        FinancialAuditLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'update_default_due_days',
            'target_type' => 'Setting',
            'ip_address'  => $request->ip(),
            'details'     => "Updated default invoice payment due timeline to {$validated['invoice_due_days']} days (bundle elective discount: " . ($validated['bundle_elective_discount_pct'] ?? '0') . "%).",
        ]);
    }

    /**
     * Reconcile status, reminders, and student access restriction when an invoice due date changes.
     */
    private function syncInvoiceDueDateChange(Invoice $invoice, ?string $reason = null, ?string $oldDueDate = null): void
    {
        $newDueDate = Carbon::parse($invoice->due_date)->startOfDay();
        $today = now()->startOfDay();

        // 1. Reconcile status if invoice is active (not fully paid and not cancelled)
        if (!in_array($invoice->status, [InvoiceStatus::Paid, InvoiceStatus::Cancelled])) {
            if ($newDueDate->lt($today)) {
                $invoice->status = InvoiceStatus::Overdue;
            } else {
                // If it was overdue, lift overdue back to partially_paid or issued
                if ((float)$invoice->amount_paid > 0) {
                    $invoice->status = InvoiceStatus::PartiallyPaid;
                } else {
                    $invoice->status = InvoiceStatus::Issued;
                }
            }
            $invoice->save();
        }

        // 2. Sync student access restriction immediately
        if ($invoice->student) {
            $this->accessRestriction->syncStudentStatus($invoice->student);
        }

        // 3. Reschedule pending email reminders to match new due date
        InvoiceReminder::where('invoice_id', $invoice->id)->where('status', 'pending')->delete();
        if ($invoice->student && in_array($invoice->status, InvoiceStatus::unpaidStatuses())) {
            $this->invoices->scheduleReminders($invoice, $invoice->student);
        }

        // 4. Record Financial Audit Log
        $newDueDateStr = $invoice->due_date ? $invoice->due_date->format('d M Y') : $newDueDate->toDateString();
        $oldDueDateStr = $oldDueDate ? Carbon::parse($oldDueDate)->format('d M Y') : 'N/A';

        FinancialAuditLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'update_invoice_due_date',
            'target_type' => 'Invoice',
            'target_id'   => $invoice->id,
            'ip_address'  => request()->ip(),
            'details'     => "Updated due date for invoice #{$invoice->invoice_number} from {$oldDueDateStr} to {$newDueDateStr}." .
                             ($reason ? " Reason: {$reason}" : ""),
        ]);

        // 5. Notify Users (Student & Parents) without mentioning admin
        BillingNotificationService::notifyInvoiceDueDateUpdated($invoice, $reason, $oldDueDateStr);
    }

    public function deleteInvoice(int $id): string
    {
        $invoice = Invoice::findOrFail($id);
        $number  = $invoice->invoice_number;

        // 1. Collect linked payment IDs BEFORE deleting pivot rows
        $linkedPaymentIds = PaymentInvoice::where('invoice_id', $id)->pluck('payment_id');

        // 2. Remove related items, reminders, and payment_invoices pivot rows
        $invoice->items()->delete();
        $invoice->reminders()->delete();
        PaymentInvoice::where('invoice_id', $id)->delete();

        // 3. Sync orphaned / affected payment records so Ledger stays accurate
        foreach ($linkedPaymentIds as $paymentId) {
            $payment = Payment::find($paymentId);
            if (!$payment) continue;
            $remaining = PaymentInvoice::where('payment_id', $paymentId)->count();
            $adminNote = "Invoice {$number} deleted by admin.";
            if ($remaining === 0) {
                if ($payment->status === PaymentStatus::Pending) {
                    $payment->update(['status' => PaymentStatus::Failed, 'payment_proof_notes' => $adminNote]);
                } else {
                    $payment->update(['amount' => 0, 'payment_proof_notes' => $adminNote]);
                }
            } else {
                $newAmt = PaymentInvoice::where('payment_id', $paymentId)->sum('amount_applied');
                $payment->update(['amount' => $newAmt]);
            }
        }

        // 4. Delete the invoice
        $invoice->delete();

        return $number;
    }

    // ── Finance Settings ─────────────────────────────────────────────────────────

    public function updateFinanceSettings(Request $request): void
    {
        $validated = $request->validate([
            'company_bank_name'           => 'nullable|string|max:255',
            'company_bank_account_name'   => 'nullable|string|max:255',
            'company_bank_account_number' => 'nullable|string|max:255',
            'company_bank_instructions'   => 'nullable|string',
            'gateway_provider'            => 'nullable|string|max:100',
            'gateway_merchant_id'         => 'nullable|string|max:255',
            'gateway_sandbox_mode'        => 'nullable|boolean',
            'billplz_api_key'             => 'nullable|string|max:255',
            'billplz_x_signature_key'     => 'nullable|string|max:255',
            'billplz_collection_id'       => 'nullable|string|max:255',
            'billplz_sandbox'             => 'nullable|boolean',
            'is_gateway_form'             => 'nullable|boolean',
        ]);

        if ($request->has('company_bank_name')) {
            Setting::set('company_bank_name', $validated['company_bank_name'] ?? '', 'finance', 'Company Bank Name');
        }
        if ($request->has('company_bank_account_name')) {
            Setting::set('company_bank_account_name', $validated['company_bank_account_name'] ?? '', 'finance', 'Company Account Name');
        }
        if ($request->has('company_bank_account_number')) {
            Setting::set('company_bank_account_number', $validated['company_bank_account_number'] ?? '', 'finance', 'Company Account Number');
        }
        if ($request->has('company_bank_instructions')) {
            Setting::set('company_bank_instructions', $validated['company_bank_instructions'] ?? '', 'finance', 'Transfer Instructions');
        }
        if ($request->has('gateway_provider')) {
            Setting::set('gateway_provider', $validated['gateway_provider'] ?? 'billplz', 'finance', 'Gateway Provider');
        }
        if ($request->has('gateway_merchant_id')) {
            Setting::set('gateway_merchant_id', $validated['gateway_merchant_id'] ?? '', 'finance', 'Gateway Merchant ID');
        }

        // Billplz Gateway Credentials
        if ($request->has('billplz_api_key')) {
            Setting::set('billplz_api_key', trim((string) $request->input('billplz_api_key')), 'finance', 'Billplz API Secret Key');
        }
        if ($request->has('billplz_x_signature_key')) {
            Setting::set('billplz_x_signature_key', trim((string) $request->input('billplz_x_signature_key')), 'finance', 'Billplz X-Signature Key');
        }
        if ($request->has('billplz_collection_id')) {
            Setting::set('billplz_collection_id', trim((string) $request->input('billplz_collection_id')), 'finance', 'Billplz Collection ID');
        }
        if ($request->has('is_gateway_form')) {
            Setting::set('billplz_sandbox', $request->has('billplz_sandbox') ? '1' : '0', 'finance', 'Billplz Sandbox Mode');
            Setting::set('gateway_sandbox_mode', $request->has('billplz_sandbox') ? '1' : '0', 'finance', 'Gateway Sandbox Mode');
        }

        FinancialAuditLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'update_settings',
            'target_type' => 'FinanceSettings',
            'ip_address'  => $request->ip(),
            'details'     => "Updated company banking & Billplz payment gateway settings.",
        ]);
    }

    public function revealGatewayCredentials(Request $request): array
    {
        FinancialAuditLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'view_credentials',
            'target_type' => 'GatewayCredentials',
            'ip_address'  => $request->ip(),
            'details'     => "Admin revealed sensitive payment gateway credentials.",
        ]);

        $billplzService = new \App\Services\Billplz\BillplzService();

        return [
            'success'                 => true,
            'merchant_id'             => Setting::get('gateway_merchant_id', 'OSC_MERCHANT_8892'),
            'api_key'                 => $billplzService->getApiKey() ?: 'sk_live_' . md5('osc_live_secret_key_prod'),
            'billplz_api_key'         => $billplzService->getApiKey() ?: '',
            'billplz_x_signature_key' => $billplzService->getXSignatureKey() ?: '',
            'billplz_collection_id'   => $billplzService->getCollectionId() ?: '',
            'billplz_sandbox'         => $billplzService->isSandbox(),
            'provider'                => Setting::get('gateway_provider', 'billplz'),
        ];
    }

    /**
     * Test connection to the Billplz API with provided or configured credentials.
     */
    public function testBillplzConnection(Request $request): array
    {
        $billplzService = new \App\Services\Billplz\BillplzService();
        $apiKey       = $request->filled('api_key') ? trim($request->input('api_key')) : null;
        $collectionId = $request->filled('collection_id') ? trim($request->input('collection_id')) : null;
        $isSandbox    = $request->has('sandbox') ? $request->boolean('sandbox') : null;

        return $billplzService->testConnection($apiKey, $collectionId, $isSandbox);
    }
}
