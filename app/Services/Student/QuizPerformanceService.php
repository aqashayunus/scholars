<?php

namespace App\Services\Student;

use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\Student;
use App\Models\StudentQuiz;

class QuizPerformanceService
{
    /**
     * Redirects to the student's first available quiz, if any.
     */
    public function firstQuizId(Student $student): ?int
    {
        $courseIds = Enrollment::where('student_id', $student->id)->pluck('course_id');

        return Quiz::whereIn('course_id', $courseIds)->value('id');
    }

    public function showQuiz(int $id): Quiz
    {
        return Quiz::with(['course.subject', 'questions'])->findOrFail($id);
    }

    public function submitQuiz(Student $student, int $quizId, int $score, int $totalQuestions): void
    {
        $quiz = Quiz::findOrFail($quizId);

        StudentQuiz::updateOrCreate([
            'student_id' => $student->id,
            'quiz_id'    => $quiz->id,
        ], [
            'score'           => $score,
            'total_questions' => $totalQuestions,
            'completed_at'    => now(),
        ]);
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
            $performanceData   = $recentQuizzes->map(fn ($q) => $q->total_questions > 0 ? (int) round(($q->score / $q->total_questions) * 100) : 0)->toArray();
        } else {
            $performanceLabels = [now()->format('M')];
            $performanceData   = [0];
        }

        return [
            'performanceLabels' => $performanceLabels,
            'performanceData'   => $performanceData,
        ];
    }
}
