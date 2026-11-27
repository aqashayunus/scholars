<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinancialAuditLog;
use App\Models\Setting;
use App\Services\InvoiceCancellationService;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Financials tools: the free-trial switch (no automatic invoices until a
 * date) and "Cancel unpaid invoices" in bulk.
 */
class BillingToolsController extends Controller
{
    public function __construct(private InvoiceCancellationService $cancellation) {}

    public function updateFreeTrial(Request $request): RedirectResponse
    {
        if ($request->boolean('turn_off')) {
            Setting::set(InvoiceService::FREE_TRIAL_UNTIL, '', 'billing', 'Free trial: no automatic invoices until');
            $this->audit('free_trial_off', ['until' => null]);

            return $this->back('Free trial switched off. New registrations get invoices again.');
        }

        $validator = Validator::make($request->all(), [
            'free_trial_until' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
        ], [
            'free_trial_until.required' => 'Choose the last day of the free trial.',
            'free_trial_until.after_or_equal' => 'The last day of the free trial cannot be in the past.',
        ]);
        if ($validator->fails()) {
            return redirect()->to(route('admin.dashboard').'#financials')->withErrors($validator)->withInput();
        }
        $data = $validator->validated();

        Setting::set(InvoiceService::FREE_TRIAL_UNTIL, $data['free_trial_until'], 'billing', 'Free trial: no automatic invoices until');
        $this->audit('free_trial_on', ['until' => $data['free_trial_until']]);

        return $this->back('Free trial on until '.date('j M Y', (int) strtotime($data['free_trial_until'])).'. No invoices are issued automatically until then.');
    }

    public function cancelUnpaid(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'invoice_ids' => ['required', 'array', 'min:1'],
            'invoice_ids.*' => ['integer'],
            'reason' => ['required', 'string', 'max:255'],
            'confirm' => ['accepted'],
        ], [
            'invoice_ids.required' => 'Tick at least one invoice to cancel.',
            'confirm.accepted' => 'Tick the box to confirm.',
        ]);

        $adminId = Auth::id();
        $result = $this->cancellation->cancelMany(
            array_values(array_map('intval', $data['invoice_ids'])),
            $data['reason'],
            $adminId === null ? null : (int) $adminId,
        );

        $msg = count($result['cancelled']).' invoice(s) cancelled'.($result['cancelled'] ? ': '.implode(', ', $result['cancelled']) : '').'.';
        if ($result['skipped'] !== []) {
            $msg .= ' Skipped: '.implode(' ', $result['skipped']);
        }

        return $this->back($msg);
    }

    private function back(string $message): RedirectResponse
    {
        return redirect()->to(route('admin.dashboard').'#financials')->with('success', $message);
    }

    /** @param array<string, mixed> $details */
    private function audit(string $action, array $details): void
    {
        FinancialAuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'target_type' => 'Setting',
            'target_id' => null,
            'ip_address' => request()->ip(),
            'details' => json_encode($details),
        ]);
    }
}
