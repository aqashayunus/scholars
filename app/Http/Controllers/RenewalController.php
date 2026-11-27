<?php

namespace App\Http\Controllers;

use App\Models\StudentRenewal;
use App\Models\User;
use App\Services\RenewalService;
use App\Services\SchoolYear;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The "Continue in {school year}?" page families reach from the email
 * (student, linked parent, or admin acting for the family).
 */
class RenewalController extends Controller
{
    public function __construct(private RenewalService $renewals) {}

    public function show(Request $request, string $token): View
    {
        $renewal = $this->authorised($request, $token);
        $student = $renewal->student()->with(['user', 'activeEnrollments.course.subject'])->firstOrFail();

        return view('renew.show', [
            'renewal' => $renewal,
            'student' => $student,
            'year' => SchoolYear::current(),
            'choices' => $this->renewals->choices(),
            'suggestedLevel' => $renewal->level ?: $this->renewals->suggestedLevel($student),
            'currentSubjectNames' => $student->activeEnrollments->map(fn ($e) => $e->course?->subject?->name)->filter()->values()->all(),
            'invoiceRoute' => $this->user($request)->isParent() ? 'parent.fees.show' : 'student.fees.show',
        ]);
    }

    public function confirm(Request $request, string $token): RedirectResponse
    {
        $renewal = $this->authorised($request, $token);
        $data = $request->validate([
            'level' => ['required', 'string'],
            'subject_ids' => ['required', 'array', 'min:1'],
            'subject_ids.*' => ['integer'],
            'fee_structure_id' => ['nullable', 'integer'],
        ], [
            'subject_ids.required' => 'Choose at least one subject.',
        ]);

        $invoice = $this->renewals->confirm(
            $renewal,
            $data['level'],
            array_values(array_map('intval', $data['subject_ids'])),
            isset($data['fee_structure_id']) ? (int) $data['fee_structure_id'] : null,
            $this->user($request),
        );

        $msg = 'Thank you! '.($renewal->student->user->name ?? 'Your child').' continues in '.$renewal->school_year.' ('.$data['level'].').';
        if ($invoice) {
            $msg .= " Invoice {$invoice->invoice_number} for {$invoice->billing_period} (RM".number_format((float) $invoice->total_amount, 2).') has been sent by email.';
        }

        return redirect()->route('renew.show', $token)->with('success', $msg);
    }

    public function decline(Request $request, string $token): RedirectResponse
    {
        $renewal = $this->authorised($request, $token);
        $this->renewals->decline($renewal, $this->user($request));

        return redirect()->route('renew.show', $token)
            ->with('success', 'Thank you for letting us know. No invoice will be issued. You can still change your mind on this page.');
    }

    public function stop(Request $request, string $token): RedirectResponse
    {
        $renewal = $this->authorised($request, $token);
        abort_unless($renewal->status === StudentRenewal::CONFIRMED, 422, 'Only a continuing student can be stopped.');
        $last = $this->renewals->stopAfterThisMonth($renewal, $this->user($request));

        return redirect()->route('renew.show', $token)
            ->with('success', "Done. {$last->format('F Y')} is the last month billed; classes end after that month.");
    }

    private function authorised(Request $request, string $token): StudentRenewal
    {
        $renewal = StudentRenewal::with('student.user')->where('token', $token)->firstOrFail();
        abort_unless($renewal->student && $this->renewals->canAct($this->user($request), $renewal->student), 403);

        return $renewal;
    }

    private function user(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        return $user;
    }
}
