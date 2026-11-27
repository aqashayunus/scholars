<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentRenewal;
use App\Models\User;
use App\Services\RenewalService;
use App\Services\SchoolYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Manage Subscription: continue / change, not continuing, stop after this
 * month, undo stop. Used from the student profile, the parent profile and
 * by admin (for a family). The renewal email links to go().
 */
class SubscriptionController extends Controller
{
    public function __construct(private RenewalService $renewals) {}

    /** Email link: open the right Manage Subscription page for whoever is logged in. */
    public function go(Request $request, Student $student): RedirectResponse
    {
        $this->authorise($request, $student);

        return redirect()->to($this->destination($this->user($request), $student));
    }

    public function continue(Request $request, Student $student): RedirectResponse
    {
        $this->authorise($request, $student);
        $year = $this->openYear();
        $data = $request->validate([
            'level' => ['required', 'string'],
            'subject_ids' => ['required', 'array', 'min:1'],
            'subject_ids.*' => ['integer'],
            'fee_structure_id' => ['nullable', 'integer'],
        ], [
            'subject_ids.required' => 'Choose at least one subject.',
        ]);

        $renewal = $this->renewals->ensureRenewal($student, $year);
        $invoice = $this->renewals->confirm(
            $renewal,
            $data['level'],
            array_values(array_map('intval', $data['subject_ids'])),
            isset($data['fee_structure_id']) ? (int) $data['fee_structure_id'] : null,
            $this->user($request),
        );

        $msg = ($student->user->name ?? 'Your child').' continues in '.$year->label.' ('.$data['level'].').';
        if ($invoice) {
            $msg .= " Invoice {$invoice->invoice_number} for {$invoice->billing_period} (RM".number_format((float) $invoice->total_amount, 2).') has been issued and emailed.';
        }

        return $this->done($request, $student, $msg);
    }

    public function decline(Request $request, Student $student): RedirectResponse
    {
        $this->authorise($request, $student);
        $renewal = $this->renewals->ensureRenewal($student, $this->openYear());
        $this->renewals->decline($renewal, $this->user($request));

        return $this->done($request, $student, 'Thank you for letting us know. No invoice will be issued. You can change your mind here any time.');
    }

    public function stop(Request $request, Student $student): RedirectResponse
    {
        $this->authorise($request, $student);
        $renewal = $student->renewals()->where('school_year', $this->openYear()->label)->first();
        if (! $renewal || $renewal->status !== StudentRenewal::CONFIRMED) {
            return $this->done($request, $student, 'There is no active subscription to stop.', 'error');
        }
        $last = $this->renewals->stopAfterThisMonth($renewal, $this->user($request));

        return $this->done($request, $student, "Done. {$last->format('F Y')} is the last month billed; classes end after that month. You can undo this until then.");
    }

    public function resume(Request $request, Student $student): RedirectResponse
    {
        $this->authorise($request, $student);
        $renewal = $student->renewals()->where('school_year', $this->openYear()->label)->first();
        if (! $renewal || ! $this->renewals->resume($renewal, $this->user($request))) {
            return $this->done($request, $student, 'This subscription can no longer be resumed here. Please confirm again below.', 'error');
        }

        return $this->done($request, $student, 'Your subscription continues. Invoices carry on as usual on the 1st of each month.');
    }

    /** Where this user manages this student's subscription. */
    public function destination(User $user, Student $student): string
    {
        if ($user->role === 'admin') {
            $year = SchoolYear::current();
            if ($year) {
                return route('renew.show', $this->renewals->ensureRenewal($student, $year)->token);
            }

            return route('admin.dashboard').'#renewals';
        }
        if ($user->isParent()) {
            return route('parent.profile', ['tab' => 'subscription', 'child' => $student->id]);
        }

        return route('student.profile', ['tab' => 'subscription']);
    }

    private function done(Request $request, Student $student, string $message, string $type = 'success'): RedirectResponse
    {
        return redirect()->to($this->destination($this->user($request), $student))->with('subscription_'.$type, $message);
    }

    private function openYear(): SchoolYear
    {
        $year = SchoolYear::current();
        abort_unless($year !== null, 422, 'Renewals are not open yet.');

        return $year;
    }

    private function authorise(Request $request, Student $student): void
    {
        abort_unless($this->renewals->canAct($this->user($request), $student), 403);
    }

    private function user(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        return $user;
    }
}
