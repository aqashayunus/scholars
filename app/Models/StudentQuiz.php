<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentQuiz extends Model
{
    protected $fillable = ['student_id','quiz_id','score','total_questions','completed_at'];
    protected $casts = ['completed_at' => 'datetime'];

    public function student() { return $this->belongsTo(Student::class); }
    public function quiz()    { return $this->belongsTo(Quiz::class); }

    public function getPercentAttribute(): int
    {
        return $this->total_questions > 0
            ? (int) round(($this->score / $this->total_questions) * 100)
            : 0;
    }
}
