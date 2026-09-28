<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id','teacher_id','title','description',
        'level','price','status','total_lessons','schedule','thumbnail'
    ];

    public function subject()     { return $this->belongsTo(Subject::class); }
    public function teacher()     { return $this->belongsTo(Teacher::class); }
    public function enrollments() { return $this->hasMany(Enrollment::class); }
    public function assignments() { return $this->hasMany(Assignment::class); }
    public function quizzes()     { return $this->hasMany(Quiz::class); }
    public function liveClasses()       { return $this->hasMany(LiveClass::class); }
    public function learningMaterials() { return $this->hasMany(LearningMaterial::class); }

    public function enrolledStudents()  { return $this->hasManyThrough(Student::class, Enrollment::class, 'course_id', 'id', 'id', 'student_id'); }
}
