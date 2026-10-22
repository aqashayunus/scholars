<?php

namespace App\Services\Grading;

use App\Models\Assignment;

/**
 * DiagnosticGradingEngine
 *
 * Evaluates student answers against parsed rule ASTs from DiagnosticRuleParser
 * and computes awarded marks, partial credit, and transparent feedback.
 */
class DiagnosticGradingEngine
{
    /**
     * Grade a single question's student answer against a rule string.
     *
     * @param  string      $ruleString     Admin-defined grading rule
     * @param  string|null $studentAnswer  Raw student answer
     * @param  float       $questionMarks  Max marks for this question
     * @param  string      $questionType   'mcq' or 'fill_in_blank'
     * @return array {
     *     score:             float   — marks awarded
     *     max_marks:         float   — maximum possible marks
     *     is_correct:        bool    — true if full marks awarded
     *     is_partial:        bool    — true if partial credit awarded
     *     is_manual:         bool    — true if requires teacher grading
     *     matched_criteria:  array   — criteria/parts that matched
     *     unmatched_criteria:array   — criteria/parts that did NOT match
     *     feedback:          string  — human-readable grading note
     * }
     */
    public function gradeQuestion(
        string $ruleString,
        ?string $studentAnswer,
        float $questionMarks = 1.0,
        string $questionType = 'fill_in_blank'
    ): array {
        // MCQ always uses a simple case-insensitive exact match
        if ($questionType === 'mcq') {
            return $this->gradeMcq($ruleString, $studentAnswer, $questionMarks);
        }

        $ast = DiagnosticRuleParser::parse($ruleString, $questionMarks);

        return match ($ast['type']) {
            'manual'        => $this->gradeManual($ast),
            'empty'         => $this->gradeEmpty($ast, $studentAnswer),
            'exact'         => $this->gradeExact($ast, $studentAnswer),
            'fuzzy'         => $this->gradeFuzzy($ast, $studentAnswer),
            'numerical'     => $this->gradeNumerical($ast, $studentAnswer),
            'criteria'      => $this->gradeCriteria($ast, $studentAnswer),
            'multi_required'=> $this->gradeMultiRequired($ast, $studentAnswer),
            'alternatives'  => $this->gradeAlternatives($ast, $studentAnswer),
            'legacy'        => $this->gradeLegacy($ast, $studentAnswer),
            default         => $this->gradeEmpty($ast, $studentAnswer),
        };
    }

    /**
     * Grade a complete diagnostic test submission.
     *
     * @param  Assignment $assignment
     * @param  array      $submittedAnswers  ['1' => 'A', '2' => 'solid', ...]
     * @param  array      $legacySectionB    legacy fallback section_b answers
     * @return array {
     *     total_score:       float   — sum of all earned marks
     *     total_possible:    float   — sum of all question max marks
     *     percentage:        int     — 0–100
     *     correct_count:     int     — number of full-mark questions
     *     mcq_correct:       int
     *     mcq_total:         int
     *     fill_correct:      int
     *     fill_total:        int
     *     has_manual:        bool    — true if any question needs teacher review
     *     breakdown:         array   — per-question grading detail
     * }
     */
    public function gradeSubmission(
        Assignment $assignment,
        array $submittedAnswers,
        array $legacySectionB = []
    ): array {
        $questionsList = $assignment->getQuestionsList();

        $totalScore    = 0.0;
        $totalPossible = 0.0;
        $correctCount  = 0;
        $mcqCorrect    = 0;
        $mcqTotal      = 0;
        $fillCorrect   = 0;
        $fillTotal     = 0;
        $hasManual     = false;
        $breakdown     = [];

        foreach ($questionsList as $q) {
            $qNum        = (string) $q['number'];
            $type        = $q['type'] ?? 'mcq';
            $ruleString  = (string) ($q['correct_answer'] ?? '');
            $questionMax = (float) ($q['marks'] ?? 1.0);

            $totalPossible += $questionMax;

            // Retrieve student answer
            $rawStudentAns = $submittedAnswers[$qNum]
                ?? $submittedAnswers[(int) $qNum]
                ?? ($type === 'fill_in_blank' ? ($legacySectionB[$qNum] ?? null) : null);

            // Grade the question
            $result = $this->gradeQuestion($ruleString, $rawStudentAns, $questionMax, $type);

            // Accumulate totals
            $totalScore += $result['score'];

            if ($result['is_manual']) {
                $hasManual = true;
            }

            if ($type === 'mcq') {
                $mcqTotal++;
                if ($result['is_correct']) {
                    $mcqCorrect++;
                }
            } else {
                $fillTotal++;
                if ($result['is_correct']) {
                    $fillCorrect++;
                }
            }

            if ($result['is_correct']) {
                $correctCount++;
            }

            $breakdown[$qNum] = [
                'section'          => $type === 'mcq' ? 'A' : 'B',
                'type'             => $type,
                'question_number'  => (int) $qNum,
                'title'            => $q['title'] ?? "Question {$qNum}",
                'student_choice'   => $rawStudentAns,
                'correct_option'   => $ruleString ?: '(Any answer accepted)',
                'is_correct'       => $result['is_correct'],
                'is_partial'       => $result['is_partial'],
                'is_manual'        => $result['is_manual'],
                'score'            => $result['score'],
                'max_marks'        => $questionMax,
                'matched_criteria' => $result['matched_criteria'],
                'feedback'         => $result['feedback'],
            ];
        }

        $percentage = $totalPossible > 0
            ? (int) round(($totalScore / $totalPossible) * 100)
            : 0;

        return [
            'total_score'   => $totalScore,
            'total_possible'=> $totalPossible,
            'percentage'    => $percentage,
            'correct_count' => $correctCount,
            'mcq_correct'   => $mcqCorrect,
            'mcq_total'     => $mcqTotal,
            'fill_correct'  => $fillCorrect,
            'fill_total'    => $fillTotal,
            'has_manual'    => $hasManual,
            'breakdown'     => $breakdown,
        ];
    }

    // ── Grading Methods per Rule Type ─────────────────────────────────────────

    protected function gradeMcq(string $correctAnswer, ?string $studentAnswer, float $maxMarks): array
    {
        $correct = strtoupper(trim($correctAnswer));
        $student = $studentAnswer !== null ? strtoupper(trim($studentAnswer)) : null;
        $isCorrect = !empty($correct) && $student === $correct;

        return $this->result(
            score:     $isCorrect ? $maxMarks : 0.0,
            maxMarks:  $maxMarks,
            isCorrect: $isCorrect,
            feedback:  $isCorrect
                ? "Correct (selected {$correct})"
                : "Incorrect (selected: " . ($student ?? '—') . ", expected: {$correct})"
        );
    }

    protected function gradeManual(array $ast): array
    {
        return $this->result(
            score:     0.0,
            maxMarks:  $ast['max_marks'],
            isCorrect: false,
            isManual:  true,
            feedback:  'Manual review required: This question requires teacher evaluation.'
        );
    }

    protected function gradeEmpty(array $ast, ?string $studentAnswer): array
    {
        $hasAnswer = $studentAnswer !== null && trim($studentAnswer) !== '';
        return $this->result(
            score:     $hasAnswer ? $ast['max_marks'] : 0.0,
            maxMarks:  $ast['max_marks'],
            isCorrect: $hasAnswer,
            feedback:  $hasAnswer
                ? 'Any non-empty answer accepted.'
                : 'No answer provided.'
        );
    }

    protected function gradeExact(array $ast, ?string $studentAnswer): array
    {
        // Case-sensitive exact match (no normalization)
        $student  = trim($studentAnswer ?? '');
        $expected = trim($ast['answer']);
        $isCorrect = ($student === $expected) && $student !== '';

        return $this->result(
            score:     $isCorrect ? $ast['max_marks'] : 0.0,
            maxMarks:  $ast['max_marks'],
            isCorrect: $isCorrect,
            feedback:  $isCorrect
                ? "Exact match: \"{$expected}\""
                : "Exact match required. Expected: \"{$expected}\", got: \"{$student}\""
        );
    }

    protected function gradeFuzzy(array $ast, ?string $studentAnswer): array
    {
        $student    = DiagnosticRuleParser::normalizeText($studentAnswer ?? '');
        $expected   = DiagnosticRuleParser::normalizeText($ast['answer']);
        $similarity = DiagnosticRuleParser::similarity($student, $expected);
        $distance   = levenshtein($student, $expected);

        $isCorrect = $student !== ''
            && ($similarity >= $ast['similarity'] || $distance <= $ast['max_distance']);

        return $this->result(
            score:     $isCorrect ? $ast['max_marks'] : 0.0,
            maxMarks:  $ast['max_marks'],
            isCorrect: $isCorrect,
            feedback:  $isCorrect
                ? "Fuzzy match accepted (similarity: " . round($similarity * 100) . "%)"
                : "Fuzzy match failed for \"{$ast['answer']}\" (similarity: " . round($similarity * 100) . "%, required ≥" . round($ast['similarity'] * 100) . "%)"
        );
    }

    protected function gradeNumerical(array $ast, ?string $studentAnswer): array
    {
        $extracted = DiagnosticRuleParser::extractNumber($studentAnswer ?? '');

        if ($extracted === null) {
            return $this->result(
                score:     0.0,
                maxMarks:  $ast['max_marks'],
                isCorrect: false,
                feedback:  "No numerical value found in answer: \"{$studentAnswer}\""
            );
        }

        $epsilon   = 0.00001;
        $isCorrect = ($extracted >= ($ast['min'] - $epsilon)) && ($extracted <= ($ast['max'] + $epsilon));

        return $this->result(
            score:     $isCorrect ? $ast['max_marks'] : 0.0,
            maxMarks:  $ast['max_marks'],
            isCorrect: $isCorrect,
            feedback:  $isCorrect
                ? "Numerical value {$extracted} is within range [{$ast['min']}, {$ast['max']}]"
                : "Value {$extracted} is outside accepted range [{$ast['min']}, {$ast['max']}] (target: {$ast['target']} ±{$ast['tolerance']})"
        );
    }

    protected function gradeCriteria(array $ast, ?string $studentAnswer): array
    {
        $studentNorm  = DiagnosticRuleParser::normalizeText($studentAnswer ?? '');
        $scored       = 0.0;
        $matched      = [];
        $unmatched    = [];

        foreach ($ast['criteria'] as $criterion) {
            $criterionMatched = false;

            foreach ($criterion['keywords'] as $keyword) {
                // Keyword containment or exact match
                if ($keyword === '') {
                    continue;
                }
                $pattern = '/(?:\b|^)' . preg_quote($keyword, '/') . '(?:\b|$)/iu';
                if (preg_match($pattern, $studentNorm) || strcasecmp($studentNorm, $keyword) === 0) {
                    $criterionMatched = true;
                    break;
                }
            }

            $label = implode(' / ', $criterion['keywords']);
            if ($criterionMatched) {
                $scored  += $criterion['marks'];
                $matched[] = $label;
            } else {
                $unmatched[] = $label;
            }
        }

        // Cap at max_marks
        $scored    = min($scored, $ast['max_marks']);
        $isCorrect = $scored >= $ast['max_marks'] - 0.001;
        $isPartial = !$isCorrect && $scored > 0;

        $matchedStr   = !empty($matched)   ? implode(', ', $matched)   : 'none';
        $unmatchedStr = !empty($unmatched) ? implode(', ', $unmatched) : 'none';

        return $this->result(
            score:     $scored,
            maxMarks:  $ast['max_marks'],
            isCorrect: $isCorrect,
            isPartial: $isPartial,
            matched:   $matched,
            unmatched: $unmatched,
            feedback:  "Matched criteria: [{$matchedStr}]. Unmatched: [{$unmatchedStr}]. Score: {$scored}/{$ast['max_marks']}"
        );
    }

    protected function gradeMultiRequired(array $ast, ?string $studentAnswer): array
    {
        $studentNorm = DiagnosticRuleParser::normalizeText($studentAnswer ?? '');
        $scored      = 0.0;
        $matched     = [];
        $unmatched   = [];

        foreach ($ast['parts'] as $partIdx => $alternatives) {
            $partMatched = false;
            foreach ($alternatives as $alt) {
                if ($alt === '') {
                    continue;
                }
                $pattern = '/(?:\b|^)' . preg_quote($alt, '/') . '(?:\b|$)/iu';
                if (strcasecmp($studentNorm, $alt) === 0
                    || preg_match($pattern, $studentNorm)
                ) {
                    $partMatched = true;
                    break;
                }
            }

            $label = implode(' / ', $alternatives);
            if ($partMatched) {
                $scored  += $ast['marks_per_part'];
                $matched[] = $label;
            } else {
                $unmatched[] = $label;
            }
        }

        $scored    = min($scored, $ast['max_marks']);
        $isCorrect = $scored >= $ast['max_marks'] - 0.001;
        $isPartial = !$isCorrect && $scored > 0;

        return $this->result(
            score:     $scored,
            maxMarks:  $ast['max_marks'],
            isCorrect: $isCorrect,
            isPartial: $isPartial,
            matched:   $matched,
            unmatched: $unmatched,
            feedback:  "Required parts matched: " . implode(', ', $matched)
                . (!empty($unmatched) ? " | Missing: " . implode(', ', $unmatched) : '')
                . " ({$scored}/{$ast['max_marks']} marks)"
        );
    }

    protected function gradeAlternatives(array $ast, ?string $studentAnswer): array
    {
        $studentNorm = DiagnosticRuleParser::normalizeText($studentAnswer ?? '');
        $isCorrect   = false;
        $matchedAlt  = null;

        foreach ($ast['alternatives'] as $alt) {
            if ($alt === '') {
                continue;
            }
            // Exact normalised match
            if ($studentNorm === $alt) {
                $isCorrect  = true;
                $matchedAlt = $alt;
                break;
            }
            // Keyword containment
            $pattern = '/(?:\b|^)' . preg_quote($alt, '/') . '(?:\b|$)/iu';
            if (preg_match($pattern, $studentNorm)) {
                $isCorrect  = true;
                $matchedAlt = $alt;
                break;
            }
        }

        $acceptedList = implode(', ', $ast['alternatives']);

        return $this->result(
            score:     $isCorrect ? $ast['max_marks'] : 0.0,
            maxMarks:  $ast['max_marks'],
            isCorrect: $isCorrect,
            matched:   $isCorrect ? [$matchedAlt] : [],
            feedback:  $isCorrect
                ? "Accepted: \"{$matchedAlt}\""
                : "Expected one of: [{$acceptedList}]"
        );
    }

    protected function gradeLegacy(array $ast, ?string $studentAnswer): array
    {
        // Backward-compatible: comma/pipe/slash separated alternatives
        $studentNorm = DiagnosticRuleParser::normalizeText(
            preg_replace('/[\.\,\!\?]+$/u', '', trim($studentAnswer ?? ''))
        );
        $isCorrect  = false;
        $matchedAlt = null;

        if ($studentNorm === '') {
            return $this->result(
                score:     0.0,
                maxMarks:  $ast['max_marks'],
                isCorrect: false,
                feedback:  'No answer provided.'
            );
        }

        foreach ($ast['alternatives'] as $alt) {
            if ($alt === '') {
                continue;
            }
            if ($studentNorm === $alt) {
                $isCorrect  = true;
                $matchedAlt = $alt;
                break;
            }
            $pattern = '/(?:\b|^)' . preg_quote($alt, '/') . '(?:\b|$)/iu';
            if (preg_match($pattern, $studentNorm)) {
                $isCorrect  = true;
                $matchedAlt = $alt;
                break;
            }
        }

        $acceptedList = implode(', ', $ast['alternatives']);

        return $this->result(
            score:     $isCorrect ? $ast['max_marks'] : 0.0,
            maxMarks:  $ast['max_marks'],
            isCorrect: $isCorrect,
            matched:   $isCorrect ? [$matchedAlt] : [],
            feedback:  $isCorrect
                ? "Correct: \"{$matchedAlt}\" matched."
                : "Expected: [{$acceptedList}]"
        );
    }

    // ── Result Builder ────────────────────────────────────────────────────────

    protected function result(
        float $score,
        float $maxMarks,
        bool $isCorrect,
        bool $isPartial = false,
        bool $isManual = false,
        array $matched = [],
        array $unmatched = [],
        string $feedback = ''
    ): array {
        return [
            'score'              => round($score, 4),
            'max_marks'          => round($maxMarks, 4),
            'is_correct'         => $isCorrect,
            'is_partial'         => $isPartial,
            'is_manual'          => $isManual,
            'matched_criteria'   => $matched,
            'unmatched_criteria' => $unmatched,
            'feedback'           => $feedback,
        ];
    }
}
