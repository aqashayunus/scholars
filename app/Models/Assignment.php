<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = [
        'course_id',
        'is_global',
        'target_level',
        'created_by_admin',
        'title',
        'type',
        'topic',
        'description',
        'instructions',
        'due_date',
        'total_marks',
        'duration_minutes',
        'exam_date',
        'file_path',
        'file_name',
        'file_size',
        'answer_key',
        'total_questions',
        'time_limit_minutes',
        'section_b_answer_key',
        'section_b_questions',
        'questions_data',
    ];
    protected $casts = [
        'due_date'             => 'date',
        'exam_date'            => 'date',
        'is_global'            => 'boolean',
        'created_by_admin'     => 'boolean',
        'answer_key'           => 'array',
        'total_questions'      => 'integer',
        'time_limit_minutes'   => 'integer',
        'section_b_answer_key' => 'array',
        'section_b_questions'  => 'integer',
        'questions_data'       => 'array',
    ];

    public function isDiagnostic(): bool
    {
        return $this->type === 'Diagnostic Test';
    }

    public function getAnswerKeyMap(): array
    {
        if (is_array($this->answer_key)) {
            return $this->answer_key;
        }
        if (is_string($this->answer_key)) {
            $decoded = json_decode($this->answer_key, true);
            if (is_array($decoded)) {
                return $decoded;
            }
            // If comma-separated (e.g. A, B, C, D)
            $parts = array_map('trim', explode(',', $this->answer_key));
            $map = [];
            foreach ($parts as $idx => $opt) {
                if (!empty($opt)) {
                    $map[(string)($idx + 1)] = strtoupper($opt);
                }
            }
            return $map;
        }
        return [];
    }

    /**
     * Returns a 1-indexed map of Section B (fill-in-blank) correct answers.
     * e.g. ['1' => 'hydrogen', '2' => 'oxygen']
     */
    public function getSectionBAnswerKeyMap(): array
    {
        if (is_array($this->section_b_answer_key)) {
            return $this->section_b_answer_key;
        }
        if (is_string($this->section_b_answer_key)) {
            $decoded = json_decode($this->section_b_answer_key, true);
            if (is_array($decoded)) {
                return $decoded;
            }
            // If comma-separated (e.g. hydrogen, oxygen, covalent bond)
            $parts = array_map('trim', explode(',', $this->section_b_answer_key));
            $map = [];
            foreach ($parts as $idx => $ans) {
                if ($ans !== '') {
                    $map[(string)($idx + 1)] = $ans;
                }
            }
            return $map;
        }
        return [];
    }

    /**
     * Whether this assignment has Section B fill-in-blank questions.
     */
    public function hasSectionB(): bool
    {
        return ($this->section_b_questions ?? 0) > 0 && !empty($this->getSectionBAnswerKeyMap());
    }

    /**
     * Returns an ordered array of all questions with their type, title, and correct answer.
     * Backwards-compatible: if questions_data is empty, generates from answer_key & section_b_answer_key.
     */
    public function getQuestionsList(): array
    {
        if (!empty($this->questions_data) && is_array($this->questions_data)) {
            $list = [];
            foreach ($this->questions_data as $idx => $q) {
                $qNum = (int) ($q['number'] ?? ($idx + 1));
                $type = isset($q['type']) && in_array($q['type'], ['mcq', 'fill_in_blank']) ? $q['type'] : 'mcq';
                $ans  = trim((string) ($q['correct_answer'] ?? ''));
                $list[] = [
                    'number'         => $qNum,
                    'type'           => $type,
                    'title'          => !empty($q['title']) ? $q['title'] : "Soalan {$qNum}",
                    'correct_answer' => $type === 'mcq' ? strtoupper($ans) : $ans,
                    'image'          => $q['image'] ?? null,
                    'page_number'    => $q['page_number'] ?? ($q['page'] ?? 1),
                    'prompt'         => $q['prompt'] ?? null,
                    'options_labels' => $q['options_labels'] ?? ($q['options'] ?? []),
                    'passage'        => $q['passage'] ?? null,
                ];
            }
            if (!empty($list)) {
                return $list;
            }
        }

        // Fallback: build from legacy answer_key (MCQ) and section_b_answer_key (fill-in-blank)
        $list = [];
        $answerKeyMap = $this->getAnswerKeyMap();
        $totalA = $this->total_questions ?: count($answerKeyMap);
        if ($totalA <= 0 && empty($answerKeyMap) && empty($this->getSectionBAnswerKeyMap())) {
            $totalA = 10;
        }

        for ($i = 1; $i <= $totalA; $i++) {
            $list[] = [
                'number'         => $i,
                'type'           => 'mcq',
                'title'          => "Soalan {$i}",
                'correct_answer' => $answerKeyMap[(string)$i] ?? '',
            ];
        }

        $sectionBMap = $this->getSectionBAnswerKeyMap();
        $totalB = (int) ($this->section_b_questions ?: count($sectionBMap));
        for ($j = 1; $j <= $totalB; $j++) {
            $list[] = [
                'number'         => $totalA + $j,
                'type'           => 'fill_in_blank',
                'title'          => "Soalan B{$j}",
                'correct_answer' => $sectionBMap[(string)$j] ?? '',
            ];
        }

        return $list;
    }

    public function getTotalQuestionsCount(): int
    {
        return count($this->getQuestionsList());
    }

    public function getMcqQuestionsCount(): int
    {
        return count(array_filter($this->getQuestionsList(), fn($q) => ($q['type'] ?? '') === 'mcq'));
    }

    public function getFillInBlankQuestionsCount(): int
    {
        return count(array_filter($this->getQuestionsList(), fn($q) => ($q['type'] ?? '') === 'fill_in_blank'));
    }

    public function course()  { return $this->belongsTo(Course::class); }
    public function results() { return $this->hasMany(StudentAssignment::class); }
    public function studentAssignments() { return $this->hasMany(StudentAssignment::class); }

    protected static function booted()
    {
        static::deleting(function ($assignment) {
            $assignment->studentAssignments()->delete();
            if ($assignment->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($assignment->file_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($assignment->file_path);
            }
        });
    }

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }
}
