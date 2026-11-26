<?php

namespace App\Services\Student;

use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\Student;
use App\Models\StudentQuiz;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class QuizPerformanceService
{
    /**
     * Quizzes of the classes the student is enrolled in. Every student quiz
     * lookup goes through this, so a quiz ID from another class is a 404.
     *
     * @return Builder<Quiz>
     */
    public function visibleQuizzes(Student $student): Builder
    {
        $courseIds = Enrollment::where('student_id', $student->id)->pluck('course_id');

        return Quiz::whereIn('course_id', $courseIds);
    }

    /**
     * The "My Quizzes" list: quizzes that have questions, each with the
     * student's latest result (or null when not attempted).
     *
     * @return Collection<int, array{quiz: Quiz, result: StudentQuiz|null}>
     */
    public function listQuizzes(Student $student): Collection
    {
        $quizzes = $this->visibleQuizzes($student)
            ->with('course.subject')
            ->withCount('questions')
            ->orderByDesc('created_at')
            ->get()
            ->filter(fn (Quiz $quiz) => $quiz->questions_count > 0)
            ->values();

        $results = StudentQuiz::where('student_id', $student->id)
            ->whereIn('quiz_id', $quizzes->pluck('id'))
            ->get()
            ->keyBy('quiz_id');

        return $quizzes->map(fn (Quiz $quiz) => [
            'quiz' => $quiz,
            'result' => $results->get($quiz->id),
        ]);
    }

    public function showQuiz(Student $student, int $id): Quiz
    {
        return $this->visibleQuizzes($student)
            ->with(['course.subject', 'questions'])
            ->findOrFail($id);
    }

    /**
     * Grades the student's answers on the server; the quiz page never
     * receives the answer key. Unanswered questions count as wrong (the
     * timer can submit a half-finished quiz).
     *
     * @param  array<int|string, string|null>  $answers  question id => chosen option (A-D)
     * @return array{score: int, total_questions: int, review: array<int, array{correct_option: string, explanation: string}>}
     */
    public function submitQuiz(Student $student, int $quizId, array $answers): array
    {
        $quiz = $this->visibleQuizzes($student)->with('questions')->findOrFail($quizId);

        $score = 0;
        $review = [];
        foreach ($quiz->questions as $question) {
            $correct = strtoupper((string) $question->correct_option);
            $chosen = strtoupper((string) ($answers[$question->id] ?? ''));
            if ($chosen !== '' && $chosen === $correct) {
                $score++;
            }
            $review[$question->id] = [
                'correct_option' => $correct,
                'explanation' => $question->explanation ?: 'No tip available.',
            ];
        }

        $total = $quiz->questions->count();

        StudentQuiz::updateOrCreate([
            'student_id' => $student->id,
            'quiz_id' => $quiz->id,
        ], [
            'score' => $score,
            'total_questions' => $total,
            'completed_at' => now(),
        ]);

        return ['score' => $score, 'total_questions' => $total, 'review' => $review];
    }

    /**
     * Personal performance trajectory chart data, shared by the dashboard
     * and progress pages so both stay in sync.
     */
    public function performanceChartData(Student $student): array
    {
        $recentQuizzes = StudentQuiz::where('student_id', $student->id)
            ->orderBy('completed_at', 'asc')
            ->take(6)
            ->get();

        if ($recentQuizzes->isNotEmpty()) {
            $performanceLabels = $recentQuizzes->map(fn ($q) => $q->completed_at ? $q->completed_at->format('M') : 'Quiz')->toArray();
            $performanceData = $recentQuizzes->map(fn ($q) => $q->total_questions > 0 ? (int) round(($q->score / $q->total_questions) * 100) : 0)->toArray();
        } else {
            $performanceLabels = [now()->format('M')];
            $performanceData = [0];
        }

        return [
            'performanceLabels' => $performanceLabels,
            'performanceData' => $performanceData,
        ];
    }
}
