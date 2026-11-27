<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Admin > Student Relationship & Linkages > Verify IC (2026-11-28).
 *
 * Only a one-way hash of each IC is stored, so admin cannot read an IC. Admin
 * can check one given to them (e.g. by a parent on the phone) against the
 * student's or a linked parent's hash. A match also saves the masked IC
 * ("******-**-1234") so it shows next time. The IC typed in is never stored.
 */
class StudentIcController extends Controller
{
    public function verify(Request $request, Student $student): JsonResponse
    {
        $data = $request->validate([
            'ic' => ['required', 'string', 'max:20'],
            'who' => ['required', 'string', 'regex:/^(student|parent:\d+)$/'],
        ]);

        $digits = preg_replace('/\D/', '', $data['ic']) ?? '';
        if (strlen($digits) !== 12) {
            return response()->json(['match' => false, 'message' => 'Enter the 12-digit IC number.'], 422);
        }

        if ($data['who'] === 'student') {
            $target = $student;
            $label = 'the student';
        } else {
            $parentId = (int) substr($data['who'], 7);
            $target = $this->linkedParent($student, $parentId);
            if (! $target) {
                return response()->json(['match' => false, 'message' => 'That parent is not linked to this student.'], 422);
            }
            $label = 'this parent';
        }

        if (! $target->ic_number_hash) {
            return response()->json(['match' => false, 'message' => "No IC is registered for {$label}."]);
        }

        $match = $target->matchesIc($data['ic']);
        $masked = null;
        if ($match) {
            $masked = Student::maskedIc($data['ic']);
            if (! $target->ic_masked) {
                $target->ic_masked = $masked;
                $target->save();
            }
        }

        Log::info('[VerifyIc] admin #'.$request->user()?->id." checked {$data['who']} of student #{$student->id}: ".($match ? 'match' : 'no match'));

        return response()->json([
            'match' => $match,
            'masked' => $masked,
            'message' => $match
                ? "✓ Matches the IC registered for {$label} ({$masked})."
                : "✗ Does not match the IC registered for {$label}.",
        ]);
    }

    private function linkedParent(Student $student, int $parentId): ?User
    {
        if ($student->parent_user_id === $parentId) {
            return User::find($parentId);
        }

        return $student->parents()->where('users.id', $parentId)->first();
    }
}
