<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = ['student_id','live_class_id','status'];

    public function student()   { return $this->belongsTo(Student::class); }
    public function liveClass() { return $this->belongsTo(LiveClass::class); }
}
