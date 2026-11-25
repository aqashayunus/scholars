<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\StudentQuiz;
use Illuminate\Support\Collection;

/**
 * Course progress (enrollments.progress_pct) is derived from quizzes:
 *
 *   progress = average, over every quiz in the course that has questions,
 *              of the student's latest score on it as a percentage
 *              (a quiz not attempted yet counts as 0%).
 *
 * So progress rises both by attempting more of the course's quizzes and by
 * scoring better on them, and drops when the teacher adds a new quiz the
 * student hasn't done yet. Quiz, Question and StudentQuiz model events call
 * recalculateCourse(), so every create/edit/delete/submit path stays in sync.
 */
class CourseProgressService
{
    public function recalculateForQuiz(?int $quizId): void
    {
        if (! $quizId) {
            return;
        }

        $courseId = Quiz::whereKey($quizId)->value('course_id');
        if ($courseId) {
            $this->recalculateCourse((int) $courseId);
        }
    }

    public function recalculateCourse(int $courseId): void
    {
        $quizzes = Quiz::where('course_id', $courseId)
            ->has('questions')
            ->withCount('questions')
            ->get(['id']);

        $enrollments = Enrollment::where('course_id', $courseId)->get(['id', 'student_id', 'progress_pct']);
        if ($enrollments->isEmpty()) {
            return;
        }

        $resultsByStudent = $quizzes->isEmpty()
            ? collect()
            : StudentQuiz::whereIn('quiz_id', $quizzes->pluck('id'))
                ->whereIn('student_id', $enrollments->pluck('student_id'))
                ->whereNotNull('completed_at')
                ->get(['student_id', 'quiz_id', 'score', 'total_questions'])
                ->groupBy('student_id');

        foreach ($enrollments as $enrollment) {
            $progress = $this->progressFor($quizzes, $resultsByStudent->get($enrollment->student_id, collect()));

            if ((int) $enrollment->progress_pct !== $progress) {
                Enrollment::whereKey($enrollment->id)->update(['progress_pct' => $progress]);
            }
        }
    }

    /**
     * @param  Collection<int, Quiz>  $quizzes
     * @param  Collection<int, StudentQuiz>  $results
     */
    private function progressFor($quizzes, $results): int
    {
        if ($quizzes->isEmpty()) {
            return 0;
        }

        $byQuiz = $results->keyBy('quiz_id');
        $sum = 0.0;

        foreach ($quizzes as $quiz) {
            $result = $byQuiz->get($quiz->id);
            if (! $result) {
                continue;
            }

            $total = (int) ($result->total_questions ?: $quiz->questions_count);
            if ($total > 0) {
                $sum += min(100, max(0, ($result->score / $total) * 100));
            }
        }

        return (int) round($sum / $quizzes->count());
    }
}
