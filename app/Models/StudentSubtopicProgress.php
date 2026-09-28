<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentSubtopicProgress extends Model
{
    protected $table = 'student_subtopic_progress';
    protected $fillable = ['student_id', 'subtopic_id', 'status'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subtopic()
    {
        return $this->belongsTo(SubjectSubtopic::class, 'subtopic_id');
    }
}
