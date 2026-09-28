<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
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

    public function course()  { return $this->belongsTo(Course::class); }
    public function results() { return $this->hasMany(StudentQuiz::class); }
    public function questions() { return $this->hasMany(Question::class); }
}
