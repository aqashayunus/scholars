<?php

namespace App\Models;

use App\Services\CourseProgressService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int|null $questions_count
 */
class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'total_questions',
        'time_limit_minutes',
        'passing_marks',
        'due_date',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    // Keep enrollments.progress_pct in sync (see CourseProgressService).
    protected static function booted(): void
    {
        static::saved(function (Quiz $quiz) {
            $progress = app(CourseProgressService::class);
            $progress->recalculateCourse((int) $quiz->course_id);

            $previous = (int) $quiz->getOriginal('course_id');
            if ($quiz->wasChanged('course_id') && $previous) {
                $progress->recalculateCourse($previous);
            }
        });

        static::deleted(fn (Quiz $quiz) => app(CourseProgressService::class)->recalculateCourse((int) $quiz->course_id));
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /** @return HasMany<StudentQuiz, $this> */
    public function results(): HasMany
    {
        return $this->hasMany(StudentQuiz::class);
    }

    /** @return HasMany<Question, $this> */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
