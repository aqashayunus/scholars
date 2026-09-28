<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentTopicMastery extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'subject_id',
        'topic_name',
        'mastery_percentage',
        'quizzes_attempted',
        'last_practiced_at',
    ];

    protected $casts = [
        'last_practiced_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
