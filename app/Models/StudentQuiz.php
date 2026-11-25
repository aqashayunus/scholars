<?php

namespace App\Models;

use App\Services\CourseProgressService;
use Illuminate\Database\Eloquent\Model;

class StudentQuiz extends Model
{
    protected $fillable = ['student_id', 'quiz_id', 'score', 'total_questions', 'completed_at'];

    protected $casts = ['completed_at' => 'datetime'];

    protected static function booted(): void
    {
        static::saved(fn (StudentQuiz $r) => app(CourseProgressService::class)->recalculateForQuiz($r->quiz_id));
        static::deleted(fn (StudentQuiz $r) => app(CourseProgressService::class)->recalculateForQuiz($r->quiz_id));
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function getPercentAttribute(): int
    {
        return $this->total_questions > 0
            ? (int) round(($this->score / $this->total_questions) * 100)
            : 0;
    }
}
