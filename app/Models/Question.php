<?php

namespace App\Models;

use App\Services\CourseProgressService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'type',
        'question_text',
        'image_path',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_option',
        'explanation',
    ];

    // A quiz only counts towards course progress once it has questions.
    protected static function booted(): void
    {
        static::created(fn (Question $q) => app(CourseProgressService::class)->recalculateForQuiz($q->quiz_id));
        static::deleted(fn (Question $q) => app(CourseProgressService::class)->recalculateForQuiz($q->quiz_id));
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}
