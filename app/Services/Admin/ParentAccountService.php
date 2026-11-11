<?php

namespace App\Services\Admin;

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ParentAccountService
{
    /**
     * Link a parent account to a student via the parent_student pivot, enforcing
     * a single is_primary link per student and keeping the legacy
     * Student::parent_user_id column in sync for code that still reads it directly.
     *
     * @return array{parent:User,student:Student}
     */
    public function linkParentStudent(array $validated, bool $isPrimary): array
    {
        $parent = User::findOrFail($validated['parent_user_id']);
        if ($parent->role !== 'parent') {
            throw new \InvalidArgumentException('Selected user is not a parent account.');
        }

        $relationship = $validated['relationship'] ?: 'Guardian';

        if ($isPrimary) {
            DB::table('parent_student')->where('student_id', $validated['student_id'])->update(['is_primary' => false]);
        }

        $existing = DB::table('parent_student')
            ->where('parent_user_id', $validated['parent_user_id'])
            ->where('student_id', $validated['student_id'])
            ->first();

        if ($existing) {
            DB::table('parent_student')->where('id', $existing->id)->update([
                'relationship' => $relationship,
                'is_primary'   => $isPrimary,
                'status'       => 'active',
                'updated_at'   => now(),
            ]);
        } else {
            DB::table('parent_student')->insert([
                'parent_user_id' => $validated['parent_user_id'],
                'student_id'     => $validated['student_id'],
                'relationship'   => $relationship,
                'is_primary'     => $isPrimary,
                'status'         => 'active',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

        $student = Student::findOrFail($validated['student_id']);
        if (!$student->parent_user_id || $isPrimary) {
            $student->update(['parent_user_id' => $validated['parent_user_id']]);
        }

        return ['parent' => $parent, 'student' => $student];
    }

    public function unlinkParentStudent(array $validated): void
    {
        DB::table('parent_student')
            ->where('parent_user_id', $validated['parent_user_id'])
            ->where('student_id', $validated['student_id'])
            ->delete();

        $student = Student::find($validated['student_id']);
        if ($student && $student->parent_user_id == $validated['parent_user_id']) {
            $nextParent = DB::table('parent_student')->where('student_id', $student->id)->first();
            $student->update(['parent_user_id' => $nextParent?->parent_user_id]);
        }
    }

    /**
     * @return array{allParentsList: \Illuminate\Support\Collection, allParentStudentLinks: \Illuminate\Support\Collection}
     */
    public function parentsDirectoryData(): array
    {
        $allParentsList = User::where('role', 'parent')
            ->with(['studentChildren.user', 'children.user', 'payments'])
            ->latest('id')
            ->get();

        $allParentStudentLinks = DB::table('parent_student')
            ->join('users as parents', 'parents.id', '=', 'parent_student.parent_user_id')
            ->join('students', 'students.id', '=', 'parent_student.student_id')
            ->join('users as students_users', 'students_users.id', '=', 'students.user_id')
            ->select(
                'parent_student.*',
                'parents.id as parent_user_id',
                'parents.name as parent_name',
                'parents.email as parent_email',
                'parents.phone as parent_phone',
                'students.id as student_id',
                'students.level as student_level',
                'students.school_name as student_school',
                'students_users.name as student_name',
                'students_users.email as student_email'
            )
            ->orderByDesc('parent_student.id')
            ->get();

        return [
            'allParentsList'        => $allParentsList,
            'allParentStudentLinks' => $allParentStudentLinks,
        ];
    }
}
