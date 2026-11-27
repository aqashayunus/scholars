<?php

namespace App\Http\Controllers;

use App\Models\StudentRenewal;
use App\Models\User;
use App\Services\RenewalService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * /renew/{token}: admin's "Confirm for family" page in Admin > Financials & Sales > Renewals & Subscriptions.
 * Families who open an older link land on their own Manage Subscription
 * (student or parent profile, Subscription tab).
 */
class RenewalController extends Controller
{
    public function __construct(private RenewalService $renewals) {}

    public function show(Request $request, string $token): View|RedirectResponse
    {
        $renewal = StudentRenewal::with('student.user')->where('token', $token)->firstOrFail();
        $user = $request->user();
        abort_unless($user instanceof User && $renewal->student && $this->renewals->canAct($user, $renewal->student), 403);

        if ($user->role !== 'admin') {
            return redirect()->route('subscription.go', $renewal->student_id);
        }

        return view('renew.show', $this->renewals->panel($renewal->student));
    }
}
