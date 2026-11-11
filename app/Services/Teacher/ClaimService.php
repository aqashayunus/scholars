<?php

namespace App\Services\Teacher;

use App\Models\LiveClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherClassClaim;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;

class ClaimService
{
    private function getTeacher(): Teacher
    {
        return Teacher::where('user_id', auth()->id())->with(['courses.subject'])->firstOrFail();
    }

    public function claimsIndexData(): array
    {
        $teacher = $this->getTeacher();

        $claims = TeacherClassClaim::where('teacher_id', $teacher->id)
            ->with(['liveClass', 'course'])
            ->orderByDesc('id')
            ->get();

        $stats = [
            'total_claims'    => $claims->count(),
            'total_amount'    => $claims->sum('amount'),
            'pending_count'   => $claims->where('status', 'pending')->count(),
            'pending_amount'  => $claims->where('status', 'pending')->sum('amount'),
            'approved_count'  => $claims->where('status', 'approved')->count(),
            'approved_amount' => $claims->where('status', 'approved')->sum('amount'),
            'paid_count'      => $claims->where('status', 'paid')->count(),
            'paid_amount'     => $claims->where('status', 'paid')->sum('amount'),
            'rejected_count'  => $claims->where('status', 'rejected')->count(),
        ];

        $courseIds = $teacher->courses->pluck('id')->toArray();
        $completedClasses = LiveClass::where(function ($q) use ($teacher, $courseIds) {
                $q->where('teacher_id', $teacher->id)
                    ->orWhereIn('course_id', $courseIds);
            })
            ->where('scheduled_at', '<=', now())
            ->where('status', '!=', 'cancelled')
            ->with('course.subject')
            ->orderByDesc('scheduled_at')
            ->take(40)
            ->get();

        $claimedLiveClassIds = $claims->pluck('live_class_id')->filter()->toArray();

        $assignedSubjects = $teacher->subjects
            ->merge($teacher->courses->pluck('subject'))
            ->filter()
            ->unique('id')
            ->sortBy(fn ($subj) => $subj->display_name ?? $subj->name)
            ->values();

        if ($assignedSubjects->isEmpty()) {
            $assignedSubjects = Subject::active()->orderBy('name')->get();
        }

        return [
            'teacher'             => $teacher,
            'claims'              => $claims,
            'stats'               => $stats,
            'completedClasses'    => $completedClasses,
            'claimedLiveClassIds' => $claimedLiveClassIds,
            'assignedSubjects'    => $assignedSubjects,
        ];
    }

    public function storeClaim(Request $request): TeacherClassClaim
    {
        $teacher = $this->getTeacher();

        $validated = $request->validate([
            'claim_type'       => 'required|in:live_class,manual',
            'live_class_id'    => 'nullable|exists:live_classes,id',
            'course_id'        => 'nullable|exists:courses,id',
            'subject_name'     => 'required|string|max:255',
            'class_title'      => 'required|string|max:255',
            'class_date'       => 'required|date',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'rate_per_class'   => 'nullable|numeric|min:0.01',
            'amount'           => 'nullable|numeric|min:0.01',
            'teacher_notes'    => 'nullable|string|max:2000',
            'proof_attachment' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf',
        ]);

        if (!empty($validated['live_class_id'])) {
            $alreadyClaimed = TeacherClassClaim::where('live_class_id', $validated['live_class_id'])
                ->whereIn('status', ['pending', 'approved', 'paid'])
                ->exists();
            if ($alreadyClaimed) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'live_class_id' => 'This live class session has already been claimed or is pending approval.',
                ]);
            }
        }

        $bankName = trim((string) $teacher->bank_name);
        $bankNumber = trim((string) $teacher->bank_account_number);
        $bankHolder = trim((string) ($teacher->bank_account_holder ?: ($teacher->user->name ?? '')));

        if (empty($bankName) || empty($bankNumber)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'bank' => 'Please set up your Bank Name and Account Number before submitting a claim.',
            ]);
        }

        $proofPath = null;
        if ($request->hasFile('proof_attachment')) {
            $proofPath = $request->file('proof_attachment')->store('claim_proofs', 'public');
        }

        $ratePerClass = 50.00;
        if ($request->filled('rate_per_class') && is_numeric($request->input('rate_per_class'))) {
            $ratePerClass = max(0.01, (float) $request->input('rate_per_class'));
        } elseif ($request->filled('amount') && is_numeric($request->input('amount'))) {
            $ratePerClass = max(0.01, (float) $request->input('amount'));
        }

        $claim = TeacherClassClaim::create([
            'claim_number'          => TeacherClassClaim::generateClaimNumber(),
            'teacher_id'            => $teacher->id,
            'live_class_id'         => $validated['live_class_id'] ?? null,
            'course_id'             => $validated['course_id'] ?? null,
            'subject_name'          => $validated['subject_name'],
            'class_title'           => $validated['class_title'],
            'class_date'            => $validated['class_date'],
            'duration_minutes'      => $validated['duration_minutes'] ?? 60,
            'rate_per_class'        => $ratePerClass,
            'amount'                => $ratePerClass,
            'teacher_notes'         => $validated['teacher_notes'] ?? null,
            'proof_attachment_path' => $proofPath,
            'bank_name'             => $bankName,
            'bank_account_number'   => $bankNumber,
            'bank_account_holder'   => $bankHolder,
            'status'                => 'pending',
        ]);

        $adminUsers = User::where('role', 'admin')->get();
        $formattedAmount = number_format($claim->amount, 2);
        foreach ($adminUsers as $admin) {
            UserNotification::create([
                'user_id' => $admin->id,
                'type'    => 'claim',
                'title'   => 'New Teacher Class Claim Received',
                'message' => "Cikgu {$teacher->user->name} has submitted a class fee claim of RM {$formattedAmount} ({$claim->claim_number}) for '{$claim->class_title}' ({$claim->subject_name}).",
            ]);
        }

        return $claim;
    }

    /**
     * Minimal bank-details update scoped to the claims feature -- khai-QA-refactor
     * has no general teacher "My Profile" edit page (unlike main), so this exists
     * only to make the bank-details prerequisite for storeClaim() satisfiable.
     */
    public function updateBankDetails(Request $request): Teacher
    {
        $teacher = $this->getTeacher();

        $validated = $request->validate([
            'bank_name'           => 'required|string|max:100',
            'bank_account_number' => 'required|string|max:50',
            'bank_account_holder' => 'nullable|string|max:255',
        ]);

        $teacher->update([
            'bank_name'           => $validated['bank_name'],
            'bank_account_number' => $validated['bank_account_number'],
            'bank_account_holder' => $validated['bank_account_holder'] ?: ($teacher->user->name ?? null),
        ]);

        return $teacher;
    }
}
