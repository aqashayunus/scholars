<?php

namespace App\Services\Admin;

use App\Models\StudentAssignment;

class DiagnosticReviewService
{
    /**
     * Apply admin-awarded marks (and optional override status/feedback) to a diagnostic
     * submission's per-question breakdown, recompute the overall score, and persist it.
     *
     * @param  StudentAssignment $studentAssignment
     * @param  array<string,float|int|string> $scores  question_number => awarded marks
     * @param  string|null $teacherFeedback
     * @param  string|null $status  explicit override ('graded'|'pending_review'|'submitted'); auto-derived when null
     * @return array{score:int,tahap_kepandaian:string,status:string,total_earned:float,total_possible:float}
     */
    public function saveAdminGrade(
        StudentAssignment $studentAssignment,
        array $scores,
        ?string $teacherFeedback,
        ?string $status
    ): array {
        $answersBreakdown = $studentAssignment->answers_json ?: [];

        // Reconstruct from the assignment's question list if no breakdown was ever stored.
        if (empty($answersBreakdown) && $studentAssignment->assignment) {
            $questionsList = $studentAssignment->assignment->getQuestionsList();
            foreach ($questionsList as $q) {
                $qNum = (string) ($q['number'] ?? 1);
                $answersBreakdown[] = [
                    'section'         => ($q['type'] ?? '') === 'mcq' ? 'A' : 'B',
                    'type'            => $q['type'] ?? 'mcq',
                    'question_number' => (int) $qNum,
                    'title'           => $q['title'] ?? ('Question ' . $qNum),
                    'student_choice'  => '—',
                    'correct_option'  => (string) ($q['correct_answer'] ?? ''),
                    'is_correct'      => false,
                    'is_partial'      => false,
                    'is_manual'       => false,
                    'score'           => 0.0,
                    'max_marks'       => (float) ($q['marks'] ?? 1.0),
                    'feedback'        => '',
                ];
            }
        }

        $totalEarned = 0.0;
        $totalPossible = 0.0;
        $hasPendingManual = false;

        foreach ($answersBreakdown as &$item) {
            $qNum = (string) ($item['question_number'] ?? 0);
            $maxMarks = (float) ($item['max_marks'] ?? 1.0);
            $totalPossible += $maxMarks;

            if (array_key_exists($qNum, $scores)) {
                $awarded = max(0.0, min($maxMarks, (float) $scores[$qNum]));
                $item['score'] = $awarded;
                $item['is_correct'] = ($awarded >= $maxMarks);
                $item['is_partial'] = ($awarded > 0 && $awarded < $maxMarks);
                $item['is_manual'] = false;
            } elseif (!empty($item['is_manual'])) {
                $hasPendingManual = true;
            }

            $totalEarned += (float) ($item['score'] ?? 0.0);
        }
        unset($item);

        $percentage = $totalPossible > 0 ? (int) round(($totalEarned / $totalPossible) * 100) : 0;
        $percentage = max(0, min(100, $percentage));

        $tahapKepandaian = match (true) {
            $percentage >= 80 => 'Cemerlang',
            $percentage >= 60 => 'Baik',
            $percentage >= 40 => 'Sederhana',
            default           => 'Perlu Bimbingan',
        };

        $newStatus = $status ?: ($hasPendingManual ? 'pending_review' : 'graded');

        $studentAssignment->update([
            'score'            => $percentage,
            'tahap_kepandaian' => $tahapKepandaian,
            'status'           => $newStatus,
            'answers_json'     => array_values($answersBreakdown),
            'teacher_feedback' => $teacherFeedback ?? $studentAssignment->teacher_feedback,
        ]);

        return [
            'score'           => $percentage,
            'tahap_kepandaian'=> $tahapKepandaian,
            'status'          => $newStatus,
            'total_earned'    => $totalEarned,
            'total_possible'  => $totalPossible,
        ];
    }
}
