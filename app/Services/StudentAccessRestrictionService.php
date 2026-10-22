<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class StudentAccessRestrictionService
{
    /**
     * Check if a student is currently restricted due to overdue or past-due unpaid invoices.
     * Evaluated strictly per-student.
     */
    public function isStudentRestricted(Student|int $student): bool
    {
        $studentId = $student instanceof Student ? $student->id : (int) $student;

        return Invoice::where('student_id', $studentId)
            ->where(function ($query) {
                $query->where('status', Invoice::STATUS_OVERDUE)
                    ->orWhere(function ($pastDueQuery) {
                        $pastDueQuery->whereIn('status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PARTIALLY_PAID])
                            ->whereNotNull('due_date')
                            ->where('due_date', '<', now()->toDateString());
                    });
            })
            ->exists();
    }

    /**
     * Get detailed restriction data for a student.
     * Returns null if the student has no overdue balance.
     */
    public function getRestrictionDetails(Student|int $student): ?array
    {
        $studentId = $student instanceof Student ? $student->id : (int) $student;

        $overdueInvoices = Invoice::where('student_id', $studentId)
            ->where(function ($query) {
                $query->where('status', Invoice::STATUS_OVERDUE)
                    ->orWhere(function ($pastDueQuery) {
                        $pastDueQuery->whereIn('status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PARTIALLY_PAID])
                            ->whereNotNull('due_date')
                            ->where('due_date', '<', now()->toDateString());
                    });
            })
            ->with(['items'])
            ->orderBy('due_date', 'asc')
            ->get();

        if ($overdueInvoices->isEmpty()) {
            return null;
        }

        $earliestInvoice = $overdueInvoices->first();
        $earliestDueDate = $earliestInvoice->due_date ? Carbon::parse($earliestInvoice->due_date) : now();

        $daysOverdue = max(1, (int) now()->startOfDay()->diffInDays($earliestDueDate->copy()->startOfDay()));
        $totalOutstanding = (float) $overdueInvoices->sum(fn ($inv) => $inv->outstanding_amount);

        // Restriction date is when the earliest invoice became overdue (the day after due_date)
        $restrictionDate = $earliestDueDate->copy()->addDay();

        return [
            'is_restricted'       => true,
            'status'              => 'RESTRICTED',
            'outstanding_amount'  => $totalOutstanding,
            'overdue_invoices'    => $overdueInvoices,
            'earliest_due_date'   => $earliestDueDate,
            'days_overdue'        => $daysOverdue,
            'restriction_date'    => $restrictionDate,
            'primary_invoice'     => $earliestInvoice,
            'invoice_count'       => $overdueInvoices->count(),
            'message'             => "This student's access is temporarily restricted due to an outstanding payment. Please settle the outstanding balance to restore access.",
        ];
    }

    /**
     * Get payment status string for a student:
     * - RESTRICTED: Has overdue invoice(s)
     * - PARTIALLY_PAID: Has partially paid invoice(s), none overdue
     * - PENDING: Has issued unpaid invoice(s), none overdue
     * - PAID: All invoices are fully paid (or no invoices)
     */
    public function getStudentPaymentStatus(Student|int $student): string
    {
        $studentId = $student instanceof Student ? $student->id : (int) $student;

        if ($this->isStudentRestricted($studentId)) {
            return 'RESTRICTED';
        }

        $activeInvoices = Invoice::where('student_id', $studentId)
            ->whereNotIn('status', [Invoice::STATUS_CANCELLED, Invoice::STATUS_DRAFT])
            ->get();

        if ($activeInvoices->isEmpty()) {
            return 'PAID';
        }

        if ($activeInvoices->contains('status', Invoice::STATUS_PARTIALLY_PAID)) {
            return 'PARTIALLY_PAID';
        }

        if ($activeInvoices->contains('status', Invoice::STATUS_ISSUED)) {
            return 'PENDING';
        }

        $allPaid = $activeInvoices->every(fn ($inv) => $inv->status === Invoice::STATUS_PAID);

        return $allPaid ? 'PAID' : 'PENDING';
    }

    /**
     * Synchronize the student's status column in database.
     * When restricted -> status = 'restricted'
     * When no longer restricted and previously 'restricted' -> status = 'active'
     * Preserves admin-set 'inactive' status.
     */
    public function syncStudentStatus(Student $student): void
    {
        $isRestricted = $this->isStudentRestricted($student);

        if ($isRestricted) {
            if ($student->status !== 'inactive' && $student->status !== 'restricted') {
                $student->status = 'restricted';
                $student->save();

                // Trigger automated official restriction notification
                try {
                    \App\Services\Email\EmailAutomationService::trigger(
                        'account_restricted',
                        ['student' => $student],
                        "student_{$student->id}_restricted_" . now()->toDateString()
                    );
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('[StudentAccessRestrictionService] Restriction email failed: ' . $e->getMessage());
                }
            }
        } else {
            if ($student->status === 'restricted') {
                $student->status = 'active';
                $student->save();

                // Trigger automated official access restored notification
                try {
                    \App\Services\Email\EmailAutomationService::trigger(
                        'access_restored',
                        ['student' => $student],
                        "student_{$student->id}_restored_" . now()->toDateString()
                    );
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('[StudentAccessRestrictionService] Restoration email failed: ' . $e->getMessage());
                }
            }
        }
    }
}
