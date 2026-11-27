<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\StudentRenewal;
use App\Models\User;
use App\Services\RenewalService;
use App\Services\SchoolYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Admin Dashboard > Renewals: school year settings, sending the
 * "Continue in {year}?" invitations and reminders, and stopping billing.
 * Admin confirms on a family's behalf on the family's own /renew page.
 */
class RenewalAdminController extends Controller
{
    public function __construct(private RenewalService $renewals) {}

    public function saveSettings(Request $request): RedirectResponse
    {
        $v = Validator::make($request->all(), [
            'label' => ['required', 'string', 'max:9'],
            'start' => ['required', 'date_format:Y-m-d'],
            'end' => ['required', 'date_format:Y-m-d', 'after:start'],
            'first_billing' => ['required', 'date_format:Y-m'],
            'reply_by' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:end'],
        ], [
            'end.after' => 'The school year must end after it starts.',
            'first_billing.date_format' => 'Choose the first month to bill.',
        ]);
        if ($v->fails()) {
            return $this->back()->withErrors($v, 'renewals')->withInput();
        }
        $d = $v->validated();

        Setting::set(SchoolYear::KEYS['label'], trim($d['label']), 'school_year', 'School year');
        Setting::set(SchoolYear::KEYS['start'], $d['start'], 'school_year', 'School year starts');
        Setting::set(SchoolYear::KEYS['end'], $d['end'], 'school_year', 'School year ends');
        Setting::set(SchoolYear::KEYS['first_billing'], $d['first_billing'].'-01', 'school_year', 'First month billed');
        Setting::set(SchoolYear::KEYS['reply_by'], $d['reply_by'] ?? '', 'school_year', 'Reply by');

        return $this->back()->with('success', "School year {$d['label']} saved.");
    }

    public function invite(Request $request): RedirectResponse
    {
        $request->validate(['confirm' => ['accepted']], ['confirm.accepted' => 'Tick the box to send the invitations.']);
        $year = SchoolYear::current();
        if (! $year) {
            return $this->back()->with('error', 'Set up the school year first.');
        }
        $n = $this->renewals->inviteAll($year);

        return $this->back()->with('success', "{$n} invitation(s) sent for {$year->label}.");
    }

    public function remind(): RedirectResponse
    {
        $year = SchoolYear::current();
        if (! $year) {
            return $this->back()->with('error', 'Set up the school year first.');
        }
        $n = $this->renewals->remindUnanswered($year);

        return $this->back()->with('success', "{$n} reminder(s) sent.");
    }

    public function stop(Request $request, StudentRenewal $renewal): RedirectResponse
    {
        if ($renewal->status !== StudentRenewal::CONFIRMED) {
            return $this->back()->with('error', 'Only a continuing student can be stopped.');
        }
        $user = $request->user();
        abort_unless($user instanceof User, 401);
        $last = $this->renewals->stopAfterThisMonth($renewal, $user);

        return $this->back()->with('success', ($renewal->student->user->name ?? 'Student').": billing stops after {$last->format('F Y')}.");
    }

    private function back(): RedirectResponse
    {
        return redirect()->to(route('admin.dashboard').'#renewals');
    }
}
