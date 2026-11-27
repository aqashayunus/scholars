<?php

namespace App\Services;

use App\Enums\EnrollmentStatus;
use App\Enums\InvoiceStatus;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\StudentRenewal;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Recurring billing after "Continue in {school year}?" (decision 2026-11-27):
 *
 *   - run():               on the 1st, one invoice per confirmed student for
 *                          the month (never twice for the same month)
 *   - autoCancelUnpaid():  daily, cancel invoices with nothing paid that are
 *                          14 days past due, and pause the student's classes
 *   - closeUnanswered():   daily, after the reply-by date, pause students who
 *                          never answered the invitation
 *
 * "Paused" means the enrolments are set to dropped. The student can still log
 * in (StudentStatus::Inactive would block login) and come back by renewing.
 */
class MonthlyBillingService
{
    public const GRACE_DAYS = 14;

    public function __construct(
        private InvoiceService $invoices,
        private InvoiceCancellationService $cancellation,
    ) {}

    /**
     * @return array{month: string, skipped_reason: ?string, issued: list<string>, already: list<string>, failed: list<string>}
     */
    public function run(Carbon $month, bool $dryRun = false): array
    {
        $month = $month->copy()->startOfMonth();
        $period = $month->format('F Y');
        $result = ['month' => $period, 'skipped_reason' => null, 'issued' => [], 'already' => [], 'failed' => []];

        $year = SchoolYear::current();
        if (! $year) {
            $result['skipped_reason'] = 'The school year is not set up (Admin > Renewals).';

            return $result;
        }
        if (! $year->bills($month)) {
            $result['skipped_reason'] = "{$period} is outside the billed months of school year {$year->label}.";

            return $result;
        }
        if (InvoiceService::autoInvoicingPaused()) {
            $result['skipped_reason'] = 'Free trial is on until '.InvoiceService::freeTrialUntil()?->toDateString().'.';

            return $result;
        }

        $renewals = StudentRenewal::with('student.user')
            ->billableFor($year->label, $month)
            ->orderBy('id')
            ->get();

        foreach ($renewals as $renewal) {
            $student = $renewal->student;
            if (! $student) {
                continue;
            }
            $who = '#'.$renewal->student_id.' '.($student->user->name ?? '');
            if ($this->hasInvoiceFor($student, $period)) {
                $result['already'][] = $who;

                continue;
            }
            if (! $student->activeEnrollments()->exists()) {
                $result['failed'][] = "{$who}: no active classes (paused?), not billed.";

                continue;
            }
            if ($dryRun) {
                $result['issued'][] = "{$who} (dry run)";

                continue;
            }
            try {
                $invoice = $this->invoices->generateForStudent($student, $renewal->fee_structure_id, [
                    'billing_period' => $period,
                    'notes' => "Monthly tuition, school year {$year->label}.",
                ]);
                $result['issued'][] = "{$who}: {$invoice->invoice_number} RM".number_format((float) $invoice->total_amount, 2);
            } catch (\Throwable $e) {
                Log::warning("[MonthlyBilling] {$period} {$who}: ".$e->getMessage());
                $result['failed'][] = "{$who}: ".$e->getMessage();
            }
        }

        return $result;
    }

    /**
     * @return array{cancelled: list<string>, needs_admin: list<string>}
     */
    public function autoCancelUnpaid(?Carbon $today = null, bool $dryRun = false): array
    {
        $cutoff = ($today ?? now())->copy()->startOfDay()->subDays(self::GRACE_DAYS)->toDateString();
        $result = ['cancelled' => [], 'needs_admin' => []];

        // Starts together with renewals: nothing is auto-cancelled until the
        // school year is set up in Admin > Renewals.
        if (! SchoolYear::current()) {
            return $result;
        }

        $invoices = Invoice::with('student.user')
            ->whereIn('status', array_map(fn ($s) => $s->value, InvoiceStatus::unpaidStatuses()))
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', $cutoff)
            ->orderBy('id')
            ->get();

        foreach ($invoices as $invoice) {
            if ($msg = $this->cancellation->blocker($invoice)) {
                $result['needs_admin'][] = $msg;

                continue;
            }
            if ($dryRun) {
                $result['cancelled'][] = "{$invoice->invoice_number} (dry run)";

                continue;
            }
            DB::transaction(function () use ($invoice) {
                $this->cancellation->cancel($invoice, 'unpaid '.self::GRACE_DAYS.' days after the due date.', null, ['auto' => true]);
                if ($invoice->student) {
                    $this->pause($invoice->student, "Invoice {$invoice->invoice_number} unpaid ".self::GRACE_DAYS.' days after the due date.');
                }
            });
            $result['cancelled'][] = $invoice->invoice_number;
        }

        return $result;
    }

    /** @return list<string> students paused */
    public function closeUnanswered(?Carbon $today = null, bool $dryRun = false): array
    {
        $year = SchoolYear::current();
        $today = ($today ?? now())->copy();
        if (! $year || ! $year->replyBy || $today->lte($year->replyBy)) {
            return [];
        }

        $paused = [];
        $renewals = StudentRenewal::with('student.user')
            ->where('school_year', $year->label)
            ->whereIn('status', [StudentRenewal::INVITED, StudentRenewal::DECLINED])
            ->get();
        foreach ($renewals as $renewal) {
            if (! $renewal->student || ! $renewal->student->activeEnrollments()->exists()) {
                continue;
            }
            $paused[] = '#'.$renewal->student_id.' '.($renewal->student->user->name ?? '');
            if (! $dryRun) {
                $renewal->status === StudentRenewal::DECLINED
                    ? $this->pause($renewal->student, 'Not continuing in '.$year->label.'.', StudentRenewal::DECLINED)
                    : $this->pause($renewal->student, 'No reply to "Continue in '.$year->label.'?" by '.$year->replyBy->format('j M Y').'.');
            }
        }

        return $paused;
    }

    /**
     * Students who chose "stop after this month": once that month is over,
     * their classes are paused (no more invoices are issued for them anyway).
     *
     * @return list<string>
     */
    public function closeStopped(?Carbon $today = null, bool $dryRun = false): array
    {
        $thisMonth = ($today ?? now())->copy()->startOfMonth()->toDateString();
        $paused = [];
        $renewals = StudentRenewal::with('student.user')
            ->where('status', StudentRenewal::STOPPED)
            ->whereNotNull('stops_after')
            ->whereDate('stops_after', '<', $thisMonth)
            ->get();
        foreach ($renewals as $renewal) {
            if (! $renewal->student || ! $renewal->student->activeEnrollments()->exists()) {
                continue;
            }
            $paused[] = '#'.$renewal->student_id.' '.($renewal->student->user->name ?? '');
            if (! $dryRun) {
                $this->pause($renewal->student, 'Stopped after '.$renewal->stops_after?->format('F Y').'.', StudentRenewal::STOPPED);
            }
        }

        return $paused;
    }

    /**
     * Pause a student's classes (keeps the login) and record why on this
     * year's renewal (status lapsed, or the given status, e.g. declined).
     */
    public function pause(Student $student, string $reason, string $status = StudentRenewal::LAPSED): void
    {
        Enrollment::where('student_id', $student->id)
            ->where('status', EnrollmentStatus::Active->value)
            ->update(['status' => EnrollmentStatus::Dropped->value]);

        $year = SchoolYear::current();
        if ($year) {
            $renewal = StudentRenewal::firstOrCreate(
                ['student_id' => $student->id, 'school_year' => $year->label],
                ['status' => $status]
            );
            $renewal->forceFill([
                'status' => $status,
                'paused_at' => now(),
                'pause_reason' => $reason,
            ])->save();
        }

        Log::info("[MonthlyBilling] Paused student #{$student->id}: {$reason}");
    }

    private function hasInvoiceFor(Student $student, string $period): bool
    {
        return Invoice::where('student_id', $student->id)
            ->where('billing_period', $period)
            ->whereNotIn('status', [InvoiceStatus::Cancelled->value, InvoiceStatus::Draft->value])
            ->exists();
    }
}
