<?php

namespace App\Services\Admin;

use App\Enums\EnrollmentStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\CourseProgressService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Admin roster for one class (Course Catalog > Students): add an existing
 * student, or remove one.
 *
 * - Adding never creates an invoice. InvoiceService::generateForStudent
 *   bills ALL of a student's active subjects on each call, so calling it
 *   here would double-bill; the admin bills the new subject from Billing.
 * - A student's last class can't be removed: a student with no enrolments
 *   is auto-enrolled into the default subjects on their next page load
 *   (PageController::syncStudentEnrollments).
 */
class CourseRosterService
{
    public function __construct(private CourseProgressService $progress) {}

    /**
     * @return array{enrolled: list<array<string, mixed>>, available: list<array<string, mixed>>}
     */
    public function roster(Course $course): array
    {
        $enrollments = Enrollment::where('course_id', $course->id)->get()->keyBy('student_id');

        $enrolled = [];
        foreach ($this->withUsers(Student::whereIn('id', $enrollments->keys())->get()) as $row) {
            $enrollment = $enrollments->get($row['id']);
            $row['enrolled_at'] = ($enrollment->enrolled_at ?? $enrollment->created_at)?->format('d M Y');
            $enrolled[] = $row;
        }

        $studentUserIds = User::where('role', 'student')->pluck('id');
        $available = $this->withUsers(
            Student::whereNotIn('id', $enrollments->keys())->whereIn('user_id', $studentUserIds)->get()
        );

        return ['enrolled' => $enrolled, 'available' => $available];
    }

    public function enrol(Course $course, int $studentId): Enrollment
    {
        $student = Student::findOrFail($studentId);
        $name = User::whereKey($student->user_id)->value('name') ?? 'This student';

        $enrollment = DB::transaction(function () use ($course, $student, $name) {
            $exists = Enrollment::where('student_id', $student->id)
                ->where('course_id', $course->id)
                ->lockForUpdate()
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages(['student_id' => "{$name} is already in this class."]);
            }

            $enrollment = new Enrollment;
            $enrollment->student_id = $student->id;
            $enrollment->course_id = $course->id;
            $enrollment->status = EnrollmentStatus::Active;
            $enrollment->enrolled_at = now();
            $enrollment->progress_pct = 0;
            $enrollment->completed_lessons = 0;
            $enrollment->save();

            return $enrollment;
        });

        // Pick up any quiz results the student already has in this class.
        $this->progress->recalculateCourse($course->id);

        if ($student->user_id) {
            UserNotification::create([
                'user_id' => $student->user_id,
                'type' => 'enrolment',
                'title' => 'Added to a class',
                'message' => "You've been added to {$course->title}. You'll find it under My Learning.",
            ]);
        }

        return $enrollment;
    }

    public function remove(Course $course, int $studentId): void
    {
        DB::transaction(function () use ($course, $studentId) {
            $enrollments = Enrollment::where('student_id', $studentId)->lockForUpdate()->get();
            $enrollment = $enrollments->firstWhere('course_id', $course->id);

            if (! $enrollment) {
                throw ValidationException::withMessages(['student_id' => 'This student is not in this class.']);
            }

            if ($enrollments->count() <= 1) {
                throw ValidationException::withMessages(['student_id' => "This is the student's only class, so it can't be removed. Add them to another class first."]);
            }

            $enrollment->delete();
        });
    }

    /**
     * Roster rows sorted by name. Users are loaded by user_id directly;
     * students without a user account are skipped.
     *
     * @param  Collection<int, Student>  $students
     * @return list<array<string, mixed>>
     */
    private function withUsers(Collection $students): array
    {
        $users = User::whereIn('id', $students->pluck('user_id')->filter())->get()->keyBy('id');

        $rows = [];
        foreach ($students as $student) {
            $user = $users->get($student->user_id);
            if (! $user) {
                continue;
            }
            $rows[] = [
                'id' => $student->id,
                'name' => (string) $user->name,
                'email' => $user->email,
                'level' => $student->level,
            ];
        }

        usort($rows, fn (array $a, array $b) => strcasecmp($a['name'], $b['name']));

        return $rows;
    }
}
