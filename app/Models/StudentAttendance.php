<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAttendance extends Model
{
    protected $fillable = ['student_id', 'live_class_id', 'status', 'joined_at'];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function liveClass()
    {
        return $this->belongsTo(LiveClass::class);
    }
}
