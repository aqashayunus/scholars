<?php

namespace App\Services\Admin;

use App\Enums\TeacherApplicationStatus;
use App\Mail\WelcomeMail;
use App\Models\Course;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherApplication;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TeacherApplicationService
{
    /**
     * Activate the applicant as a teacher.
     *
     * A brand-new account gets a random temporary password, emailed in the
     * welcome mail (the only place it ever exists) and replaced at first
     * login via must_change_password. The mail is sent only after the
     * approval has committed, so a rolled-back approval never emails a
     * password for an account that does not exist.
     *
     * @return array{newAccount: bool, welcomeEmailSent: bool}
     */
    public function approve(TeacherApplication $application, ?string $adminNotes): array
    {
        $tempPassword = null;

        $user = DB::transaction(function () use ($application, $adminNotes, &$tempPassword) {
            $user = User::where('email', $application->email)->first();

            if (! $user) {
                // Random per-teacher password, never derived from the IC number
                // (which appears on many documents and would make the password
                // guessable from the applicant's name + IC).
                $tempPassword = Str::password(14);

                $user = new User([
                    'name' => $application->full_name,
                    'email' => $application->email,
                    'phone' => $application->phone,
                    'password' => Hash::make($tempPassword),
                ]);
                $user->role = 'teacher';
                $user->must_change_password = true;
            } else {
                $user->role = 'teacher';
            }

            $this->attachIcHash($user, $application->ic_number);
            $user->save();

            $teacher = Teacher::firstOrNew(['user_id' => $user->id]);
            $teacher->application_status = 'active';
            $teacher->qualification = $application->qualification;
            $teacher->experience_years = $application->experience_years;
            $teacher->bio = $application->preferred_subjects ? "Specialist educator in: {$application->preferred_subjects}" : 'Certified Aster Edu Educator';
            $teacher->save();

            $application->update([
                'user_id' => $user->id,
                'status' => TeacherApplicationStatus::Approved,
                'admin_notes' => $adminNotes ?? 'Application approved.',
                'approved_at' => now(),
            ]);

            try {
                UserNotification::create([
                    'user_id' => $user->id,
                    'type' => 'application',
                    'title' => 'TEACHER APPLICATION APPROVED',
                    'message' => 'Congratulations! Your educator application has been approved. Your educator account is now active.',
                    'read_at' => null,
                ]);
            } catch (\Throwable $e) {
            }

            return $user;
        });

        if ($tempPassword === null) {
            return ['newAccount' => false, 'welcomeEmailSent' => false];
        }

        try {
            Mail::to($user->email)->send(new WelcomeMail($user, $tempPassword));
            $sent = true;
        } catch (\Throwable $e) {
            report($e);
            $sent = false;
        }

        return ['newAccount' => true, 'welcomeEmailSent' => $sent];
    }

    /**
     * Store the applicant's MyKad as the account's IC hash (the same hash
     * parents' accounts use), so admins and parent-link lookups can match it.
     * Skipped for passports (normaliseIc() keeps digits only, which would
     * mangle them), when the account already has a hash, or when another
     * account already holds this IC (users.ic_number_hash is unique, and a
     * collision must not block the approval).
     */
    private function attachIcHash(User $user, ?string $icNumber): void
    {
        if (! $icNumber || $user->ic_number_hash || strlen(User::normaliseIc($icNumber)) !== 12) {
            return;
        }

        $hash = User::hashIc($icNumber);
        $takenByOther = User::where('ic_number_hash', $hash)
            ->when($user->exists, fn ($q) => $q->whereKeyNot($user->getKey()))
            ->exists();

        if (! $takenByOther) {
            $user->ic_number_hash = $hash;
            $user->ic_masked = Student::maskedIc($icNumber);
        }
    }

    public function reject(TeacherApplication $application, ?string $adminNotes): void
    {
        $application->update([
            'status' => TeacherApplicationStatus::Rejected,
            'admin_notes' => $adminNotes ?? 'Application not approved at this time.',
        ]);

        if ($application->user_id) {
            try {
                UserNotification::create([
                    'user_id' => $application->user_id,
                    'type' => 'application',
                    'title' => 'APPLICATION STATUS UPDATE',
                    'message' => 'Your educator application has been reviewed. Status: Not approved at this time.',
                    'read_at' => null,
                ]);
            } catch (\Throwable $e) {
            }
        }
    }

    public function updateSubjects(Teacher $teacher, array $subjectIds): void
    {
        $teacher->subjects()->sync($subjectIds);

        try {
            if ($teacher->user_id) {
                UserNotification::create([
                    'user_id' => $teacher->user_id,
                    'type' => 'subject',
                    'title' => 'TEACHING SUBJECTS UPDATED',
                    'message' => 'Your assigned teaching subjects have been updated.',
                    'read_at' => null,
                ]);
            }
        } catch (\Throwable $e) {
        }
    }

    public function createClass(Teacher $teacher, array $validated): Course
    {
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'subject_id' => $validated['subject_id'],
            'title' => $validated['title'],
            'level' => $validated['level'],
            'schedule' => $validated['schedule'] ?? null,
            'total_lessons' => $validated['total_lessons'] ?? 12,
            'status' => $validated['status'],
            'price' => $validated['price'],
            'description' => $validated['description'] ?? null,
        ]);

        if (! $teacher->subjects()->where('subject_id', $validated['subject_id'])->exists()) {
            $teacher->subjects()->attach($validated['subject_id']);
        }

        try {
            if ($teacher->user_id) {
                UserNotification::create([
                    'user_id' => $teacher->user_id,
                    'type' => 'course',
                    'title' => 'NEW CLASS ASSIGNED',
                    'message' => "A new class '{$course->title}' ({$course->level}) has been assigned to your educator profile.",
                    'read_at' => null,
                ]);
            }
        } catch (\Throwable $e) {
        }

        return $course;
    }
}
