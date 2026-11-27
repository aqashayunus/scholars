<?php

namespace App\Services\Admin;

use App\Enums\EnrollmentStatus;
use App\Enums\InvoiceStatus;
use App\Enums\StudentAssignmentStatus;
use App\Http\Controllers\Auth\StudentRegistrationController;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\FeeStructure;
use App\Models\FinancialAuditLog;
use App\Models\Invoice;
use App\Models\Setting;
use App\Models\Student;
use App\Models\StudentAssignment;
use App\Models\Subject;
use App\Services\InvoiceCancellationService;
use App\Services\InvoiceService;
use App\Services\PackagePricingService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Reduces a student who registered for several levels ("Form 4, Form 5") to
 * the one level they really study, now that registration allows only one.
 *
 * For the chosen level it:
 *   - removes enrolments in subjects of any other Form / Year (and the still
 *     pending tasks that came with them),
 *   - adds the chosen level's version of a removed subject when the student
 *     has none (e.g. Form 1 Bahasa Melayu -> Form 3 Bahasa Melayu),
 *   - cancels each unpaid invoice and issues a corrected one for the same
 *     package and billing period,
 *   - sets students.level to the one level.
 *
 * plan() only reads. apply() re-plans inside a transaction, refuses anything
 * with money involved (paid or part-paid invoices, recorded payments), and
 * copies every row it removes or changes into level_cleanup_backups first.
 */
class LevelCleanupService
{
    public function __construct(
        private InvoiceService $invoiceService,
        private InvoiceCancellationService $cancellation,
    ) {}

    /**
     * Students whose level holds more than one Form / Year.
     *
     * @return Collection<int, array{student: Student, levels: list<string>, enrolled_years: mixed, open_invoices: mixed, suggested: string|null}>
     */
    public function candidates(): Collection
    {
        return Student::with(['user:id,name,email', 'enrollments.course.subject', 'invoices'])
            ->where('level', 'like', '%,%')
            ->orderBy('id')
            ->get()
            ->map(fn (Student $s) => [
                'student' => $s,
                'levels' => $this->levelsOf($s),
                'enrolled_years' => $s->enrollments
                    ->map(fn ($e) => $e->course?->subject?->form_year ?: '—')
                    ->countBy()
                    ->all(),
                'open_invoices' => $s->invoices->filter(fn ($i) => in_array($i->status, InvoiceStatus::unpaidStatuses(), true)),
                'suggested' => $this->suggestedLevel($s),
            ]);
    }

    /** @return list<string> */
    public function levelsOf(Student $student): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $student->level))));
    }

    /**
     * The level to pre-select: the only one of the student's own levels that
     * they are actually enrolled in. Null when it is not obvious.
     */
    public function suggestedLevel(Student $student): ?string
    {
        $student->loadMissing('enrollments.course.subject');
        $enrolled = $student->enrollments->map(fn ($e) => $e->course?->subject?->form_year)->filter()->unique();
        $matches = array_values(array_filter($this->levelsOf($student), fn ($l) => $enrolled->contains($l)));

        return count($matches) === 1 ? $matches[0] : null;
    }

    /**
     * Levels admin may pick: the student's own first, then the rest.
     *
     * @return list<string>
     */
    public function levelChoices(Student $student): array
    {
        $own = array_values(array_intersect($this->levelsOf($student), StudentRegistrationController::REGISTRABLE_LEVELS));

        return array_values(array_unique(array_merge($own, StudentRegistrationController::REGISTRABLE_LEVELS)));
    }

    /**
     * What apply() would do. Read-only.
     *
     * @return array{student: Student, level: string, keep: Collection<int, Enrollment>, remove: Collection<int, Enrollment>,
     *               add: Collection<int, array{subject: Subject, course: Course}>,
     *               assignments_remove: Collection<int, StudentAssignment>, invoices_reissue: Collection<int, Invoice>,
     *               reissue: bool, expected_total: ?float, blockers: list<string>, warnings: list<string>}
     */
    public function plan(Student $student, string $level): array
    {
        $student->load(['user', 'enrollments.course.subject', 'invoices.items']);
        $blockers = [];
        $warnings = [];

        if (! in_array($level, StudentRegistrationController::REGISTRABLE_LEVELS, true)) {
            $blockers[] = "\"{$level}\" is not a level students can register for.";
        }
        if (! str_contains((string) $student->level, ',')) {
            $blockers[] = "This student already has a single level ({$student->level}).";
        }

        [$keep, $remove] = $student->enrollments->partition(
            fn (Enrollment $e) => $this->subjectFitsLevel($e->course?->subject, $level)
        );

        // Replacements: the chosen level's version of each removed subject the
        // student does not already have at that level.
        $keptNames = $keep->map(fn ($e) => mb_strtolower((string) $e->course?->subject?->name))->filter()->all();
        $add = $remove
            ->map(fn ($e) => $e->course?->subject)
            ->filter()
            ->unique(fn ($s) => mb_strtolower($s->name))
            ->reject(fn ($s) => in_array(mb_strtolower($s->name), $keptNames, true))
            ->map(function (Subject $old) use ($level, &$warnings) {
                // Several subjects can share a name and level; take one that has a course.
                $sameSubject = fn ($q) => $q->where('is_active', true)
                    ->where('name', $old->name)
                    ->where('form_year', $level)
                    ->when($old->education_type, fn ($q) => $q->where('education_type', $old->education_type));

                $course = Course::whereHas('subject', $sameSubject)
                    ->orderByRaw("CASE WHEN status = 'published' THEN 0 ELSE 1 END")
                    ->latest('id')
                    ->first();
                if (! $course) {
                    $warnings[] = Subject::where($sameSubject)->exists()
                        ? "{$old->name} ({$level}) has no course yet, so it is not added."
                        : "No active {$old->name} subject for {$level}, so it is not added.";

                    return null;
                }

                return ['subject' => $course->subject, 'course' => $course];
            })
            ->filter()
            ->values();

        // Still-pending tasks that only exist because of a removed course/subject.
        $removedCourseIds = $remove->pluck('course_id')->all();
        $removedDiagnosticIds = $remove->map(fn ($e) => $e->course?->subject?->diagnostic_assignment_id)->filter()->all();
        $assignmentsRemove = $removedCourseIds === [] ? collect() : StudentAssignment::where('student_id', $student->id)
            ->where('status', StudentAssignmentStatus::Pending->value)
            ->where(function ($q) use ($removedCourseIds, $removedDiagnosticIds) {
                $q->whereHas('assignment', fn ($a) => $a->whereIn('course_id', $removedCourseIds));
                if ($removedDiagnosticIds !== []) {
                    $q->orWhereIn('assignment_id', $removedDiagnosticIds);
                }
            })
            ->get();

        // Invoices: only untouched unpaid ones are cancelled (and re-issued
        // unless a free trial is on); any money stops the clean-up.
        $invoicesReissue = collect();
        foreach ($student->invoices as $invoice) {
            if (in_array($invoice->status, [InvoiceStatus::Cancelled, InvoiceStatus::Draft], true)) {
                continue;
            }
            if ($msg = $this->cancellation->blocker($invoice)) {
                $blockers[] = $msg;

                continue;
            }
            $invoicesReissue->push($invoice);
        }
        $reissue = ! InvoiceService::autoInvoicingPaused();
        if (! $reissue && $invoicesReissue->isNotEmpty()) {
            $warnings[] = 'Free trial is on until '.InvoiceService::freeTrialUntil()?->format('j M Y').': the old invoice is cancelled and no new invoice is issued.';
        }

        $finalSubjects = $keep->map(fn ($e) => $e->course?->subject)->filter()
            ->merge($add->pluck('subject'))
            ->unique('id')
            ->values();

        if ($finalSubjects->isEmpty()) {
            $warnings[] = "After the clean-up the student has no {$level} subjects.";
        }

        $expectedTotal = null;
        if ($reissue && $invoicesReissue->isNotEmpty()) {
            if ($invoicesReissue->count() > 1) {
                $warnings[] = 'The student has more than one unpaid invoice. They are all cancelled and replaced by one corrected invoice.';
            }
            $expectedTotal = $finalSubjects->isEmpty() ? null : $this->estimateTotal($invoicesReissue->first(), $finalSubjects, $warnings);
            if ($finalSubjects->isEmpty()) {
                $blockers[] = 'There would be no subjects left to bill. Pick a level the student has subjects for.';
            }
        }

        return [
            'student' => $student,
            'level' => $level,
            'keep' => $keep->values(),
            'remove' => $remove->values(),
            'add' => $add,
            'assignments_remove' => $assignmentsRemove,
            'invoices_reissue' => $invoicesReissue,
            'reissue' => $reissue,
            'expected_total' => $expectedTotal,
            'blockers' => array_values(array_unique($blockers)),
            'warnings' => array_values(array_unique($warnings)),
        ];
    }

    /**
     * Carry out the plan. Returns a summary for the flash message.
     *
     * @return array{batch: string, removed: int, added: int, tasks_removed: int, cancelled: list<string>, new_invoice: ?Invoice}
     *
     * @throws \RuntimeException when the plan has blockers
     */
    public function apply(Student $student, string $level, ?int $adminUserId): array
    {
        return DB::transaction(function () use ($student, $level, $adminUserId) {
            $student = Student::whereKey($student->id)->lockForUpdate()->firstOrFail();
            $plan = $this->plan($student, $level);
            if ($plan['blockers'] !== []) {
                throw new \RuntimeException(implode(' ', $plan['blockers']));
            }

            $batch = (string) Str::uuid();
            $backup = function (string $action, Model $row) use ($batch, $student, $adminUserId) {
                DB::table('level_cleanup_backups')->insert([
                    'batch' => $batch,
                    'student_id' => $student->id,
                    'admin_user_id' => $adminUserId,
                    'table_name' => $row->getTable(),
                    'row_id' => $row->getKey(),
                    'action' => $action,
                    'original' => $action === 'created' ? null : json_encode($row->getRawOriginal()),
                    'created_at' => now(),
                ]);
            };

            $oldLevel = $student->level;
            $backup('updated', $student);
            $student->level = $level;
            $student->save();

            foreach ($plan['remove'] as $enrollment) {
                $backup('deleted', $enrollment);
                $enrollment->delete();
            }
            foreach ($plan['assignments_remove'] as $sa) {
                $backup('deleted', $sa);
                $sa->delete();
            }
            foreach ($plan['add'] as $item) {
                $enrollment = Enrollment::firstOrNew(['student_id' => $student->id, 'course_id' => $item['course']->id]);
                if (! $enrollment->exists) {
                    $enrollment->status = EnrollmentStatus::Active;
                    $enrollment->enrolled_at = now();
                    $enrollment->progress_pct = 0;
                    $enrollment->completed_lessons = 0;
                    $enrollment->save();
                    $backup('created', $enrollment);
                }
            }

            $student->unsetRelations();
            $student->syncGlobalAndCourseAssignments();

            $cancelled = [];
            $template = $plan['invoices_reissue']->first();
            foreach ($plan['invoices_reissue'] as $invoice) {
                $backup('updated', $invoice);
                $this->cancellation->cancel($invoice, "level clean-up ({$oldLevel} -> {$level}).", $adminUserId, ['batch' => $batch]);
                $cancelled[] = $invoice->invoice_number;
            }

            $newInvoice = null;
            if ($template && $plan['reissue']) {
                $newInvoice = $this->invoiceService->generateForStudent(Student::findOrFail($student->id), $template->fee_structure_id);
                // Keep the original billing period so the family sees the same month.
                $newPeriod = $newInvoice->billing_period;
                $newInvoice->billing_period = $template->billing_period;
                $newInvoice->title = str_replace((string) $newPeriod, (string) $template->billing_period, (string) $newInvoice->title);
                $newInvoice->notes = 'Replaces '.implode(', ', $cancelled)." (level corrected to {$level}).";
                $newInvoice->save();
                $backup('created', $newInvoice);
            }

            FinancialAuditLog::create([
                'user_id' => $adminUserId,
                'action' => 'student_level_cleanup',
                'target_type' => 'Student',
                'target_id' => $student->id,
                'ip_address' => request()->ip(),
                'details' => json_encode([
                    'batch' => $batch,
                    'from' => $oldLevel,
                    'to' => $level,
                    'enrolments_removed' => $plan['remove']->pluck('id')->all(),
                    'courses_added' => $plan['add']->map(fn ($a) => $a['course']->id)->all(),
                    'invoices_cancelled' => $cancelled,
                    'new_invoice' => $newInvoice?->invoice_number,
                ]),
            ]);

            return [
                'batch' => $batch,
                'removed' => $plan['remove']->count(),
                'added' => $plan['add']->count(),
                'tasks_removed' => $plan['assignments_remove']->count(),
                'cancelled' => $cancelled,
                'new_invoice' => $newInvoice,
            ];
        });
    }

    /**
     * Whether a subject belongs to the chosen level: no form_year, the same
     * one, or a band that covers it ("Form 4–5").
     */
    public static function subjectFitsLevel(?Subject $subject, string $level): bool
    {
        $year = trim((string) $subject?->form_year);
        if ($year === '' || $year === $level) {
            return true;
        }
        if (preg_match('/^(Form|Year)\s*(\d)\s*[–-]\s*(\d)$/u', $year, $band)
            && preg_match('/^(Form|Year)\s*(\d)$/', $level, $lv)
            && $band[1] === $lv[1]) {
            return (int) $lv[2] >= (int) $band[2] && (int) $lv[2] <= (int) $band[3];
        }

        return false;
    }

    /** Best estimate of the corrected invoice, mirroring InvoiceService::generateForStudent(). */
    /**
     * @param  Collection<int, Subject>  $subjects
     * @param  list<string>  $warnings
     */
    private function estimateTotal(Invoice $template, Collection $subjects, array &$warnings): ?float
    {
        $feeStructure = $template->fee_structure_id
            ? FeeStructure::where('is_active', true)->find($template->fee_structure_id)
            : null;

        if ($template->fee_structure_id && ! $feeStructure) {
            $warnings[] = 'The package on the old invoice is no longer active, so the new invoice is priced per subject.';
        }

        if (! $feeStructure) {
            return round($subjects->sum(fn ($s) => (float) ($s->monthly_fee ?? 0)), 2);
        }

        $isNewEngine = $feeStructure->hasItems()
            || $feeStructure->priceTiers()->exists()
            || $feeStructure->hasSubjects()
            || ! in_array($feeStructure->package_type, [null, '', FeeStructure::TYPE_INDIVIDUAL], true);

        if ($isNewEngine) {
            try {
                return round(app(PackagePricingService::class)->calculate($feeStructure, $subjects->pluck('id')->all())->totalAmount, 2);
            } catch (\Throwable $e) {
                $warnings[] = 'Could not price the package ahead of time: '.$e->getMessage();

                return null;
            }
        }

        // Legacy package: core subjects capped at the package amount, electives at their (discounted) fee.
        $core = $subjects->filter(fn ($s) => strtolower($s->category ?? 'core') === 'core');
        $electives = $subjects->reject(fn ($s) => strtolower($s->category ?? 'core') === 'core');
        if ($core->isEmpty()) {
            [$core, $electives] = [$subjects, collect()];
        }
        $pct = (float) Setting::get('billing.bundle_elective_discount_pct', 0);

        return round(
            min($core->sum(fn ($s) => (float) ($s->monthly_fee ?? 0)), (float) $feeStructure->amount)
            + $electives->sum(fn ($s) => round((float) ($s->monthly_fee ?? 0) * (1 - $pct / 100), 2)),
            2
        );
    }
}
