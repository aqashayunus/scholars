<?php

namespace App\Services\Admin;

use App\Models\ClassRequest;
use App\Models\UserNotification;
use App\Services\TimetableService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Admin review of teacher-submitted class requests (reschedule / cancel),
 * created by Teacher\ClassScheduleService::requestReschedule/requestCancel.
 * Approving applies the change through TimetableService, which owns the
 * student/parent/teacher notifications for schedule changes.
 */
class ClassRequestService
{
    public function __construct(private TimetableService $timetable) {}

    /**
     * @return array{allClassRequests: Collection<int, ClassRequest>, pendingClassRequestsCount: int, approvedClassRequestsCount: int, rejectedClassRequestsCount: int, rescheduleRequestsCount: int, cancelRequestsCount: int}
     */
    public function dashboardData(): array
    {
        $all = ClassRequest::with(['teacher.user', 'liveClass.course.subject', 'liveClass.teacher.user'])
            ->latest('id')
            ->get();

        return [
            'allClassRequests' => $all,
            'pendingClassRequestsCount' => $all->where('status', 'pending')->count(),
            'approvedClassRequestsCount' => $all->where('status', 'approved')->count(),
            'rejectedClassRequestsCount' => $all->where('status', 'rejected')->count(),
            'rescheduleRequestsCount' => $all->where('type', 'reschedule')->count(),
            'cancelRequestsCount' => $all->where('type', 'cancel')->count(),
        ];
    }

    public function approve(int $id, ?string $adminNote, int $actorId): ClassRequest
    {
        return DB::transaction(function () use ($id, $adminNote, $actorId) {
            $classRequest = $this->lockPending($id);

            if ($classRequest->liveClass) {
                if ($classRequest->type === 'cancel') {
                    $this->timetable->cancelByAdmin($classRequest->live_class_id, $classRequest->reason);
                } elseif ($classRequest->type === 'reschedule') {
                    if (! $classRequest->requested_at) {
                        throw ValidationException::withMessages(['admin_note' => 'This reschedule request has no requested date and cannot be approved.']);
                    }

                    $this->timetable->rescheduleByAdmin($classRequest->live_class_id, [
                        'scheduled_date' => $classRequest->requested_at->format('Y-m-d'),
                        'scheduled_time' => $classRequest->requested_at->format('H:i'),
                        'reason' => $classRequest->reason,
                    ], $actorId);
                }
            }

            $classRequest->update([
                'status' => 'approved',
                'admin_note' => $adminNote ?: 'Approved by administration.',
            ]);

            return $classRequest;
        });
    }

    public function reject(int $id, ?string $adminNote): ClassRequest
    {
        return DB::transaction(function () use ($id, $adminNote) {
            $classRequest = $this->lockPending($id);
            $note = $adminNote ?: 'Request was not approved by administration.';

            $classRequest->update(['status' => 'rejected', 'admin_note' => $note]);

            if ($classRequest->teacher?->user_id) {
                $title = $classRequest->liveClass?->title ?? 'Session';
                UserNotification::create([
                    'user_id' => $classRequest->teacher->user_id,
                    'type' => 'class_reminder',
                    'title' => 'Class Request Rejected',
                    'message' => "Your {$classRequest->type} request for '{$title}' was not approved by administration. Reason: \"{$note}\".",
                ]);
            }

            return $classRequest;
        });
    }

    /**
     * Row-locked fetch that refuses already-decided requests, so a double
     * submit (or two admins at once) cannot apply the same change twice.
     */
    private function lockPending(int $id): ClassRequest
    {
        $classRequest = ClassRequest::with(['liveClass', 'teacher.user'])->lockForUpdate()->findOrFail($id);

        if ($classRequest->status !== 'pending') {
            throw ValidationException::withMessages(['admin_note' => "This request has already been {$classRequest->status}."]);
        }

        return $classRequest;
    }
}
