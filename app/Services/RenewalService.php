<?php

namespace App\Services;

use App\Enums\EnrollmentStatus;
use App\Enums\InvoiceStatus;
use App\Http\Controllers\Auth\StudentRegistrationController;
use App\Models\Course;
use App\Models\EmailTemplate;
use App\Models\Enrollment;
use App\Models\FeeStructure;
use App\Models\Invoice;
use App\Models\Setting;
use App\Models\Student;
use App\Models\StudentRenewal;
use App\Models\Subject;
use App\Models\User;
use App\Services\Admin\LevelCleanupService;
use App\Services\Email\EmailSendingService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * "Continue in {school year}?" (decision 2026-11-27): invite, remind,
 * confirm (level + subjects + package -> first invoice), decline, and
 * "stop after this month".
 */
class RenewalService
{
    public function __construct(private InvoiceService $invoices) {}

    // ── Who can act ──────────────────────────────────────────────────────────

    /** The student themself, a linked parent, or an admin. */
    public function canAct(User $user, Student $student): bool
    {
        if ($user->role === 'admin' || $user->id === $student->user_id || $user->id === $student->parent_user_id) {
            return true;
        }

        return $student->parents()->where('users.id', $user->id)->exists();
    }

    // ── Invite / remind ──────────────────────────────────────────────────────

    /**
     * Students who can be invited for the school year: with classes (active
     * or paused) and no renewal row for that year yet.
     *
     * @return Collection<int, Student>
     */
    public function invitable(SchoolYear $year): Collection
    {
        return Student::with('user')
            ->whereHas('enrollments')
            ->whereDoesntHave('renewals', fn ($q) => $q->where('school_year', $year->label))
            ->whereHas('user', fn ($q) => $q->where('role', 'student'))
            ->orderBy('id')
            ->get()
            ->values();
    }

    /** @return int number invited */
    public function inviteAll(SchoolYear $year): int
    {
        $n = 0;
        foreach ($this->invitable($year) as $student) {
            $renewal = StudentRenewal::create([
                'student_id' => $student->id,
                'school_year' => $year->label,
                'status' => StudentRenewal::INVITED,
                'invited_at' => now(),
            ]);
            $this->sendInvitation($renewal, $year, false);
            $n++;
        }

        return $n;
    }

    /** @return int number reminded */
    public function remindUnanswered(SchoolYear $year): int
    {
        $n = 0;
        $renewals = StudentRenewal::with('student.user')
            ->where('school_year', $year->label)
            ->where('status', StudentRenewal::INVITED)
            ->get();
        foreach ($renewals as $renewal) {
            $this->sendInvitation($renewal, $year, true);
            $renewal->forceFill(['reminded_at' => now()])->save();
            $n++;
        }

        return $n;
    }

    public function sendInvitation(StudentRenewal $renewal, SchoolYear $year, bool $isReminder): void
    {
        $student = $renewal->student()->with(['user', 'parents'])->firstOrFail();
        $childName = $student->user->name ?? 'your child';
        $replyBy = $year->replyBy ? ' by '.$year->replyBy->format('l, j F Y') : '';
        $template = new EmailTemplate([
            'name' => 'Continue in '.$year->label,
            'subject' => ($isReminder ? 'Reminder: ' : '')."Continue with Olympia Scholars in {$year->label}?",
            'body' => "Dear {{recipient_name}},\n\n"
                ."Thank you for learning with Olympia Scholars. Will {$childName} continue with us in {$year->label}?\n\n"
                ."Please let us know{$replyBy}. Click the button below, confirm {$childName}'s Form / Year for {$year->label} and choose the subjects. "
                .'The first monthly invoice is issued once you confirm.'
                ."\n\nIf {$childName} will not continue, you can tell us on the same page.",
            'cta_label' => 'Continue in '.$year->label,
            'cta_url' => route('renew.show', $renewal->token),
        ]);

        $recipients = collect();
        if ($student->user?->email) {
            $recipients->push(['email' => $student->user->email, 'name' => $student->user->name, 'type' => 'student', 'user_id' => $student->user_id]);
        }
        foreach ($student->parents as $parent) {
            if ($parent->email) {
                $recipients->push(['email' => $parent->email, 'name' => $parent->name, 'type' => 'parent', 'user_id' => $parent->id]);
            }
        }

        foreach ($recipients->unique('email') as $r) {
            EmailSendingService::send(
                $r['email'],
                (string) $template->subject,
                $template,
                ['recipient_name' => $r['name'], 'recipient_type' => $r['type'], 'student' => $student],
                EmailTemplate::CATEGORY_RENEWAL,
                $r['user_id'],
                $student->id,
            );
        }
    }

    // ── The Continue page ────────────────────────────────────────────────────

    /**
     * Active subjects and packages per level for the Continue page.
     *
     * @return array{levels: array<int, string>, subjects: array<string, array<int, array{id: int, name: string, fee: float, category: string}>>,
     *               packages: array<int, array{id: int, name: string, amount: float, education_level: string}>}
     */
    public function choices(): array
    {
        $levels = StudentRegistrationController::REGISTRABLE_LEVELS;
        $subjects = [];
        foreach ($levels as $level) {
            $subjects[$level] = $this->subjectsFor($level)
                ->map(fn (Subject $s) => ['id' => $s->id, 'name' => (string) $s->name, 'fee' => (float) ($s->monthly_fee ?? 0), 'category' => (string) ($s->category ?? 'Core')])
                ->values()->all();
        }
        $packages = FeeStructure::where('is_active', true)
            ->whereNull('student_id')
            ->orderBy('name')
            ->get()
            ->map(fn (FeeStructure $f) => ['id' => $f->id, 'name' => (string) $f->name, 'amount' => (float) $f->amount, 'education_level' => (string) ($f->education_level ?? 'All')])
            ->values()->all();

        return ['levels' => $levels, 'subjects' => $subjects, 'packages' => $packages];
    }

    /** Suggested level for next year: one Form / Year up from the current one. */
    public function suggestedLevel(Student $student): ?string
    {
        $levels = StudentRegistrationController::REGISTRABLE_LEVELS;
        $current = trim((string) $student->level);
        $i = array_search($current, $levels, true);
        if ($i === false) {
            return null;
        }
        $next = $levels[$i + 1] ?? null;

        // Year 6 -> Form 1 is a school change; suggest it, Form 5 has no next level.
        return $next ?? null;
    }

    /**
     * Confirm: set the level, move classes to the chosen subjects, and issue
     * the first monthly invoice.
     *
     * @param  list<int>  $subjectIds
     *
     * @throws ValidationException
     */
    public function confirm(StudentRenewal $renewal, string $level, array $subjectIds, ?int $feeStructureId, User $by): ?Invoice
    {
        $year = SchoolYear::current();
        if (! $year || $year->label !== $renewal->school_year) {
            throw ValidationException::withMessages(['level' => 'Renewals for '.$renewal->school_year.' are closed.']);
        }
        if (! in_array($level, StudentRegistrationController::REGISTRABLE_LEVELS, true)) {
            throw ValidationException::withMessages(['level' => 'Choose a Form / Year.']);
        }
        $subjects = Subject::where('is_active', true)->whereIn('id', $subjectIds)->get();
        if ($subjects->isEmpty() || $subjects->count() !== count(array_unique($subjectIds))) {
            throw ValidationException::withMessages(['subject_ids' => 'Choose at least one subject.']);
        }
        foreach ($subjects as $s) {
            if (! LevelCleanupService::subjectFitsLevel($s, $level)) {
                throw ValidationException::withMessages(['subject_ids' => "{$s->name} ({$s->form_year}) is not a {$level} subject."]);
            }
        }
        $feeStructure = null;
        if ($feeStructureId) {
            $feeStructure = FeeStructure::where('is_active', true)->whereNull('student_id')->find($feeStructureId);
            if (! $feeStructure) {
                throw ValidationException::withMessages(['fee_structure_id' => 'That package is not available.']);
            }
            try {
                app(PackagePricingService::class)->validateSubjectSelection($feeStructure, $subjects->pluck('id')->all());
            } catch (\Throwable $e) {
                throw ValidationException::withMessages(['fee_structure_id' => $e->getMessage()]);
            }
        }

        return DB::transaction(function () use ($renewal, $level, $subjects, $feeStructure, $by, $year) {
            $student = Student::whereKey($renewal->student_id)->lockForUpdate()->firstOrFail();
            $student->level = $level;
            $student->save();

            // Courses for the chosen subjects (one that exists, published first).
            $courseIds = [];
            foreach ($subjects as $subject) {
                $course = Course::where('subject_id', $subject->id)
                    ->orderByRaw("CASE WHEN status = 'published' THEN 0 ELSE 1 END")
                    ->latest('id')
                    ->first();
                if (! $course) {
                    throw ValidationException::withMessages(['subject_ids' => "{$subject->name} ({$level}) has no class yet. Please contact us."]);
                }
                $courseIds[] = $course->id;
            }

            // Last year's / other classes end; chosen ones are (re)activated.
            Enrollment::where('student_id', $student->id)
                ->whereNotIn('course_id', $courseIds)
                ->whereIn('status', [EnrollmentStatus::Active->value, EnrollmentStatus::Dropped->value, EnrollmentStatus::Enrolled->value])
                ->update(['status' => EnrollmentStatus::Completed->value]);
            foreach ($courseIds as $courseId) {
                $e = Enrollment::firstOrNew(['student_id' => $student->id, 'course_id' => $courseId]);
                if (! $e->exists) {
                    $e->enrolled_at = now();
                    $e->progress_pct = 0;
                    $e->completed_lessons = 0;
                }
                $e->status = EnrollmentStatus::Active;
                $e->save();
            }
            $student->unsetRelations();
            $student->syncGlobalAndCourseAssignments();

            $firstMonth = $year->firstMonthFor(now());
            $renewal->forceFill([
                'status' => StudentRenewal::CONFIRMED,
                'level' => $level,
                'subject_ids' => $subjects->pluck('id')->values()->all(),
                'fee_structure_id' => $feeStructure?->id,
                'billing_starts' => $firstMonth->toDateString(),
                'stops_after' => null,
                'responded_at' => now(),
                'responded_by_user_id' => $by->id,
                'paused_at' => null,
                'pause_reason' => null,
            ])->save();

            // First monthly invoice, unless that month already has one.
            $period = $firstMonth->format('F Y');
            $existing = Invoice::where('student_id', $student->id)
                ->where('billing_period', $period)
                ->whereNotIn('status', [InvoiceStatus::Cancelled->value, InvoiceStatus::Draft->value])
                ->first();
            if ($existing) {
                return null;
            }
            $dueDays = (int) Setting::get('billing.invoice_due_days', 7);
            $due = Carbon::now()->max($firstMonth)->copy()->addDays($dueDays);
            $invoice = $this->invoices->generateForStudent(Student::findOrFail($student->id), $feeStructure?->id, [
                'billing_period' => $period,
                'due_date' => $due->toDateString(),
                'notes' => "Monthly tuition, school year {$year->label}.",
            ]);
            $renewal->forceFill(['first_invoice_id' => $invoice->id])->save();

            return $invoice;
        });
    }

    /** "Not continuing": no invoices; classes are paused after the reply-by date (or now, if it has passed). */
    public function decline(StudentRenewal $renewal, User $by): void
    {
        $renewal->forceFill([
            'status' => StudentRenewal::DECLINED,
            'responded_at' => now(),
            'responded_by_user_id' => $by->id,
        ])->save();

        $year = SchoolYear::current();
        if ($year && $year->replyBy && now()->gt($year->replyBy) && $renewal->student) {
            app(MonthlyBillingService::class)->pause($renewal->student, 'Not continuing in '.$renewal->school_year.'.', StudentRenewal::DECLINED);
        }
    }

    /** "Stop after this month": the current (or first) billed month is the last one. */
    public function stopAfterThisMonth(StudentRenewal $renewal, User $by): Carbon
    {
        $thisMonth = now()->startOfMonth();
        $last = $renewal->billing_starts && $renewal->billing_starts->gt($thisMonth) ? $renewal->billing_starts->copy() : $thisMonth;
        $renewal->forceFill([
            'status' => StudentRenewal::STOPPED,
            'stops_after' => $last->toDateString(),
            'responded_at' => now(),
            'responded_by_user_id' => $by->id,
        ])->save();

        return $last;
    }

    /** @return Collection<int, Subject> */
    private function subjectsFor(string $level): Collection
    {
        return Subject::where('is_active', true)
            ->whereHas('courses')
            ->orderByRaw("CASE WHEN LOWER(COALESCE(category, 'core')) = 'core' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get()
            ->filter(fn (Subject $s) => trim((string) $s->form_year) !== '' && LevelCleanupService::subjectFitsLevel($s, $level))
            ->unique(fn (Subject $s) => mb_strtolower((string) $s->name))
            ->values();
    }
}
