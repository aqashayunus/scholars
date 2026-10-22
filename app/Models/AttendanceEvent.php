<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceEvent extends Model
{
    public const TYPE_JOIN_ATTEMPT           = 'join_attempt';
    public const TYPE_REJOIN_ATTEMPT         = 'rejoin_attempt';
    public const TYPE_TEACHER_MARKED_PRESENT = 'teacher_marked_present';
    public const TYPE_TEACHER_MARKED_LATE    = 'teacher_marked_late';
    public const TYPE_TEACHER_MARKED_ABSENT  = 'teacher_marked_absent';
    public const TYPE_TEACHER_MARKED_EXCUSED = 'teacher_marked_excused';
    public const TYPE_ATTENDANCE_VERIFIED    = 'attendance_verified';
    public const TYPE_CORRECTION_REQUESTED   = 'correction_requested';
    public const TYPE_CORRECTION_APPROVED    = 'correction_approved';
    public const TYPE_CORRECTION_REJECTED    = 'correction_rejected';

    protected $fillable = [
        'student_attendance_id',
        'student_id',
        'live_class_id',
        'actor_id',
        'actor_role',
        'event_type',
        'event_timestamp',
        'metadata',
    ];

    protected $casts = [
        'event_timestamp' => 'datetime',
        'metadata'        => 'array',
    ];

    public function studentAttendance()
    {
        return $this->belongsTo(StudentAttendance::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function liveClass()
    {
        return $this->belongsTo(LiveClass::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
