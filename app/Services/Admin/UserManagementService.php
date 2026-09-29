<?php

namespace App\Services\Admin;

use App\Enums\InvoiceStatus;
use App\Enums\StudentStatus;
use App\Mail\EngagementBlastMail;
use App\Mail\WelcomeMail;
use App\Models\Enrollment;
use App\Models\FinancialAuditLog;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\BillingNotificationService;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserManagementService
{
    // ── User CRUD ────────────────────────────────────────────────────────────

    public function createUser(array $validated): User
    {
        // Random temporary password — previously derived as 2 letters of the name
        // + last 6 IC digits, which is guessable by anyone who knows the user's
        // name and IC (and the public IC-lookup endpoint handed out exactly
        // that). must_change_password forces a real password on first login.
        $passwordText = Str::password(14);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'role'     => $validated['role'],
            'phone'    => $validated['phone'] ?? null,
            'password' => Hash::make($passwordText),
        ]);
        $user->must_change_password = true;
        $user->save();

        if ($user->role === 'student') {
            $student = Student::create([
                'user_id'        => $user->id,
                'parent_user_id' => $validated['parent_user_id'] ?? null,
                'level'          => 'SPM',
                'total_points'   => 0,
            ]);

            if (!empty($validated['courses'])) {
                foreach ($validated['courses'] as $courseId) {
                    Enrollment::create([
                        'student_id'        => $student->id,
                        'course_id'         => $courseId,
                        'status'            => 'active',
                        'progress_pct'      => 0,
                        'completed_lessons' => 0,
                    ]);
                }

                try {
                    app(InvoiceService::class)->generateForStudent($student);
                } catch (\Exception $e) {
                    Log::info("Admin auto-invoice note for student #{$student->id}: " . $e->getMessage());
                }
            }
        } elseif ($user->role === 'teacher') {
            Teacher::firstOrCreate(['user_id' => $user->id], ['specialization' => 'General Academic', 'rating' => 5.0, 'experience_years' => 1]);
        }

        try {
            Mail::to($user->email)->send(new WelcomeMail($user, $passwordText));
        } catch (\Exception $e) {
            report($e);
        }

        return $user;
    }

    public function updateUser(User $user, array $validated): User
    {
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->phone = $validated['phone'] ?? null;

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();

        if ($user->role === 'student' && !$user->studentProfile) {
            Student::create(['user_id' => $user->id, 'level' => 'SPM', 'total_points' => 0]);
        } elseif ($user->role === 'teacher' && !$user->teacherProfile) {
            Teacher::create(['user_id' => $user->id, 'specialization' => 'General Academic', 'rating' => 5.0, 'experience_years' => 1]);
        }

        try {
            UserNotification::create([
                'user_id' => $user->id,
                'type'    => 'profile',
                'title'   => 'ACCOUNT PROFILE UPDATED',
                'message' => 'Your profile details have been updated.',
                'read_at' => null,
            ]);
        } catch (\Throwable $e) {
        }

        return $user;
    }

    public function deleteUser(int $id): string
    {
        $user = User::findOrFail($id);
        $userName = $user->name;

        if ($user->studentProfile) {
            $user->studentProfile()->delete();
        }
        if ($user->teacherProfile) {
            $user->teacherProfile()->delete();
        }
        $user->delete();

        return $userName;
    }

    public function resetPassword(User $user, ?string $password): void
    {
        // A random password when the admin doesn't supply one — never a fixed,
        // shared default. Either way the user must set their own on next login,
        // and the value is never echoed back into the UI (it was previously
        // rendered into the success flash message, which persists in the
        // session store and any screenshot/log of the admin's screen).
        $newPassword = $password ?: Str::password(14);

        $user->password = Hash::make($newPassword);
        $user->must_change_password = true;
        $user->save();

        try {
            UserNotification::create([
                'user_id' => $user->id,
                'type'    => 'security',
                'title'   => 'PASSWORD UPDATED',
                'message' => 'Your account password has been reset by an administrator. You will be asked to set a new password on your next login.',
                'read_at' => null,
            ]);
        } catch (\Throwable $e) {
        }
    }

    // ── Status Toggles ───────────────────────────────────────────────────────

    public function toggleStudentStatus(int $id): array
    {
        $student = Student::findOrFail($id);
        $newStatus = ($student->status === StudentStatus::Inactive) ? StudentStatus::Active : StudentStatus::Inactive;
        $student->update(['status' => $newStatus]);
        if ($student->user) {
            $student->user->update(['is_active' => ($newStatus === StudentStatus::Active)]);
        }

        $name = $student->user->name ?? 'Student #' . $id;
        $label = ucfirst($newStatus->value);

        try {
            if ($student->user_id) {
                UserNotification::create([
                    'user_id' => $student->user_id,
                    'type'    => 'account',
                    'title'   => 'ACCOUNT STATUS UPDATED',
                    'message' => "Your student portal account status has been updated to {$label}.",
                    'read_at' => null,
                ]);
            }
            $parents = BillingNotificationService::getStudentParents($student);
            foreach ($parents as $parent) {
                UserNotification::create([
                    'user_id' => $parent->id,
                    'type'    => 'account',
                    'title'   => 'STUDENT ACCOUNT STATUS UPDATED',
                    'message' => "The account status for student {$name} has been updated to {$label}.",
                    'read_at' => null,
                ]);
            }
        } catch (\Throwable $e) {
        }

        try {
            FinancialAuditLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'student_status_changed',
                'target_type' => Student::class,
                'target_id'   => $student->id,
                'ip_address'  => request()->ip(),
                'details'     => "Student '{$name}' (ID: {$student->id}) status set to {$newStatus->value} by Admin.",
            ]);
        } catch (\Throwable $e) {
            // Ignore audit fail silently to prevent blocking admin action
        }

        return [$name, $label];
    }

    public function toggleTeacherStatus(int $id): array
    {
        $teacher = Teacher::findOrFail($id);
        $newStatus = ($teacher->application_status === 'inactive' || $teacher->application_status === 'deactivated') ? 'active' : 'inactive';

        $updateData = ['application_status' => $newStatus];
        if ($newStatus === 'inactive') {
            $updateData['is_online'] = false;
        }
        $teacher->update($updateData);
        if ($teacher->user) {
            $teacher->user->update(['is_active' => ($newStatus === 'active')]);
        }

        $name = $teacher->user->name ?? 'Teacher #' . $id;
        $label = ucfirst($newStatus);

        try {
            if ($teacher->user_id) {
                UserNotification::create([
                    'user_id' => $teacher->user_id,
                    'type'    => 'account',
                    'title'   => 'ACCOUNT STATUS UPDATED',
                    'message' => "Your educator account status has been updated to {$label}.",
                    'read_at' => null,
                ]);
            }
        } catch (\Throwable $e) {
        }

        try {
            FinancialAuditLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'teacher_status_changed',
                'target_type' => Teacher::class,
                'target_id'   => $teacher->id,
                'ip_address'  => request()->ip(),
                'details'     => "Teacher '{$name}' (ID: {$teacher->id}) status set to {$newStatus} by Admin for company record archiving.",
            ]);
        } catch (\Throwable $e) {
            // Ignore audit fail silently to prevent blocking admin action
        }

        return [$name, $label];
    }

    /**
     * @return array{0: string, 1: string}|null null when the admin tried to deactivate themselves.
     */
    public function toggleUserIsActive(int $id): ?array
    {
        $user = User::with(['studentProfile', 'teacherProfile'])->findOrFail($id);

        if ($user->id === auth()->id()) {
            return null;
        }

        $newActive = !$user->is_active;
        $user->update(['is_active' => $newActive]);

        if ($user->studentProfile) {
            $user->studentProfile->update(['status' => $newActive ? 'active' : 'inactive']);
        }
        if ($user->teacherProfile) {
            $user->teacherProfile->update(['application_status' => $newActive ? 'active' : 'inactive']);
        }

        $label = $newActive ? 'Reactivated' : 'Deactivated';

        try {
            UserNotification::create([
                'user_id' => $user->id,
                'type'    => 'account',
                'title'   => 'ACCOUNT STATUS UPDATED',
                'message' => "Your account has been {$label} by the administrator.",
                'read_at' => null,
            ]);
        } catch (\Throwable $e) {
        }

        try {
            FinancialAuditLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'user_is_active_toggled',
                'target_type' => User::class,
                'target_id'   => $user->id,
                'ip_address'  => request()->ip(),
                'details'     => "User '{$user->name}' (ID: {$user->id}, Role: {$user->role}) {$label} by Admin.",
            ]);
        } catch (\Throwable $e) {
        }

        return [$user->name, $label];
    }

    // ── Outreach & Engagement ────────────────────────────────────────────────

    /**
     * Non-active user ids: deactivated accounts, students restricted for
     * overdue payment, and deactivated teachers. Shared by the engagement
     * blast and the CSV export so both stay in sync on what "non-active" means.
     */
    public function nonActiveUserIds(): array
    {
        $restrictedStudentUserIds = Student::whereHas('invoices', function ($q) {
            $q->where('status', InvoiceStatus::Overdue)->whereDate('due_date', '<', now());
        })->pluck('user_id')->filter();

        $inactiveTeacherUserIds = Teacher::whereIn('application_status', ['inactive', 'deactivated'])
            ->pluck('user_id')->filter();

        $deactivatedUserIds = User::where('is_active', false)->pluck('id');

        $nonActiveUserIds = $deactivatedUserIds
            ->merge($restrictedStudentUserIds)
            ->merge($inactiveTeacherUserIds)
            ->unique()->values();

        return [$nonActiveUserIds, $restrictedStudentUserIds, $inactiveTeacherUserIds];
    }

    public function sendEngagementBlast(array $validated): array
    {
        [$nonActiveUserIds] = $this->nonActiveUserIds();

        $query = User::whereIn('id', $nonActiveUserIds);

        switch ($validated['blast_audience']) {
            case 'students_only':
                $query->where('role', 'student');
                break;
            case 'parents_only':
                $query->where('role', 'parent');
                break;
            case 'teachers_only':
                $query->where('role', 'teacher');
                break;
        }

        $recipients = $query->get();
        $sent = 0;
        $failed = 0;

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email)->send(
                    new EngagementBlastMail(
                        $recipient,
                        $validated['blast_subject'],
                        $validated['blast_body'],
                        $validated['blast_cta_link'] ?? '',
                        $validated['blast_cta_label'] ?? 'Return to Platform',
                    )
                );
                $sent++;
            } catch (\Throwable $e) {
                $failed++;
            }
        }

        try {
            FinancialAuditLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'engagement_blast_sent',
                'target_type' => User::class,
                'target_id'   => auth()->id(),
                'ip_address'  => request()->ip(),
                'details'     => "Engagement blast '{$validated['blast_subject']}' sent to {$sent} recipient(s) (audience: {$validated['blast_audience']}). Failed: {$failed}.",
            ]);
        } catch (\Throwable $e) {
        }

        return [$sent, $failed];
    }

    public function exportInactiveUsersCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        [$nonActiveUserIds, $restrictedStudentUserIds, $inactiveTeacherUserIds] = $this->nonActiveUserIds();

        $users = User::whereIn('id', $nonActiveUserIds)->latest('id')->get();

        return response()->streamDownload(function () use ($users, $restrictedStudentUserIds, $inactiveTeacherUserIds) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['User ID', 'Full Name', 'Role', 'Email', 'Phone', 'Inactivity Reason', 'Registration Date']);
            foreach ($users as $u) {
                $reasons = [];
                if ($u->is_active === false) {
                    $reasons[] = 'Account Deactivated';
                }
                if ($restrictedStudentUserIds->contains($u->id)) {
                    $reasons[] = 'Payment Restricted';
                }
                if ($inactiveTeacherUserIds->contains($u->id)) {
                    $reasons[] = 'Teacher Deactivated';
                }
                fputcsv($handle, [
                    '#' . $u->id,
                    $u->name,
                    ucfirst($u->role),
                    $u->email,
                    $u->phone ?? '',
                    implode('; ', $reasons) ?: 'Deactivated',
                    $u->created_at ? $u->created_at->format('d M Y') : '',
                ]);
            }
            fclose($handle);
        }, 'inactive-users-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
