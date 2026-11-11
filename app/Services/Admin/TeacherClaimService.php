<?php

namespace App\Services\Admin;

use App\Models\TeacherClassClaim;
use App\Models\UserNotification;
use Illuminate\Support\Carbon;

class TeacherClaimService
{
    public function approve(int $id): TeacherClassClaim
    {
        $claim = TeacherClassClaim::findOrFail($id);
        $claim->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        if ($claim->teacher?->user_id) {
            UserNotification::create([
                'user_id' => $claim->teacher->user_id,
                'type' => 'claim',
                'title' => 'Class Fee Claim Approved',
                'message' => "Your class fee claim {$claim->claim_number} (RM ".number_format($claim->amount, 2).") for '{$claim->class_title}' has been approved by admin.",
            ]);
        }

        return $claim;
    }

    public function reject(int $id, ?string $reason): TeacherClassClaim
    {
        $claim = TeacherClassClaim::findOrFail($id);
        $reason = $reason ?: 'Claim not authorized.';

        $claim->update([
            'status' => 'rejected',
            'admin_notes' => $reason,
        ]);

        if ($claim->teacher?->user_id) {
            UserNotification::create([
                'user_id' => $claim->teacher->user_id,
                'type' => 'claim',
                'title' => 'Class Fee Claim Rejected',
                'message' => "Your class fee claim {$claim->claim_number} was rejected. Reason: {$reason}",
            ]);
        }

        return $claim;
    }

    public function markPaid(int $id, array $validated): TeacherClassClaim
    {
        $claim = TeacherClassClaim::findOrFail($id);

        $ref = ! empty($validated['payment_reference'])
            ? $validated['payment_reference']
            : ('TRF-'.strtoupper(substr(md5(uniqid()), 0, 8)));

        $claim->update([
            'status' => 'paid',
            'payment_method' => $validated['payment_method'] ?? 'Bank Transfer',
            'payment_reference' => $ref,
            'paid_at' => ! empty($validated['paid_at']) ? Carbon::parse($validated['paid_at']) : now(),
        ]);

        if ($claim->teacher?->user_id) {
            UserNotification::create([
                'user_id' => $claim->teacher->user_id,
                'type' => 'claim',
                'title' => 'Class Fee Claim Paid Out',
                'message' => 'Payment of RM '.number_format($claim->amount, 2)." for claim {$claim->claim_number} ({$claim->class_title}) has been recorded. Reference: {$claim->payment_reference}.",
            ]);
        }

        return $claim;
    }

    public function dashboardData(): array
    {
        return [
            'allTeacherClaims' => TeacherClassClaim::with(['teacher.user', 'liveClass', 'course'])
                ->orderByDesc('id')
                ->get(),
        ];
    }
}
