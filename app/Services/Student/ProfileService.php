<?php

namespace App\Services\Student;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    public function profileData(Student $student, string $activeTab): array
    {
        $student->load(['user', 'parent', 'parents', 'enrollments.course.subject']);

        $user = auth()->user();
        $parent = $student->parent ?? $student->parents?->first();
        $enrolledSubjects = $student->enrollments->map(fn ($e) => $e->course->subject->name ?? null)->filter()->unique()->values();

        return compact('user', 'parent', 'enrolledSubjects', 'activeTab');
    }

    /**
     * Link the student's account to a parent account by the parent's IC number.
     * Returns ['success' => bool, 'message' => string, 'field_error' => ?string].
     */
    public function linkParent(Student $student, string $rawIc, string $relationship): array
    {
        // Only digits must be 12 characters
        $cleanDigits = preg_replace('/\D/', '', $rawIc);
        if (strlen($cleanDigits) !== 12) {
            return [
                'success' => false,
                'field_error' => 'Please enter a valid 12-digit MyKad number (e.g. 901231-14-5678).',
            ];
        }

        // Look up parent by hashed IC
        $icHash = User::hashIc($rawIc);
        $parentUser = User::where('ic_number_hash', $icHash)
                          ->where('role', 'parent')
                          ->first();

        if (!$parentUser) {
            return [
                'success' => false,
                'field_error' => 'No parent account found with that IC number. Please check and try again.',
            ];
        }

        // Check if already linked
        $alreadyLinked = DB::table('parent_student')
            ->where('parent_user_id', $parentUser->id)
            ->where('student_id', $student->id)
            ->exists() || $student->parent_user_id === $parentUser->id;

        if ($alreadyLinked) {
            return [
                'success' => true,
                'message' => "Your account is already linked to {$parentUser->name}.",
            ];
        }

        // Create the link
        DB::table('parent_student')->insertOrIgnore([
            'parent_user_id' => $parentUser->id,
            'student_id'     => $student->id,
            'relationship'   => $relationship,
            'is_primary'     => true,
            'status'         => 'active',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        // Backfill legacy column if empty
        if (!$student->parent_user_id) {
            $student->update(['parent_user_id' => $parentUser->id]);
        }

        return [
            'success' => true,
            'message' => "Successfully linked to parent account: {$parentUser->name}.",
        ];
    }

    /**
     * Update the student's personal information (name, email, phone, level).
     */
    public function updateProfile(User $user, ?Student $student, array $validated, bool $removeAvatar, ?UploadedFile $avatar): void
    {
        // Handle Avatar Removal
        if ($removeAvatar) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = null;
        }

        // Handle New Avatar Upload
        if ($avatar) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $avatar->store('avatars', 'public');
        }

        $user->name = $validated['name'];
        if (!empty($validated['email'])) {
            if ($user->email !== $validated['email']) {
                $user->email_verified_at = null;
            }
            $user->email = $validated['email'];
        }
        if (isset($validated['phone'])) {
            $user->phone = $validated['phone'];
        }
        $user->save();

        if ($student) {
            $student->update([
                'school_name' => $validated['school_name'] ?? $student->school_name,
            ]);
        }
    }

    public function updatePassword(User $user, string $newPassword): void
    {
        $user->update(['password' => Hash::make($newPassword)]);
    }

    public function isParentManaged(?Student $student): bool
    {
        return (bool) ($student && $student->parent_user_id);
    }
}
