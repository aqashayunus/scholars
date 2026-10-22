<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAttendance extends Model
{
    public const STATUS_ATTENDED = 'attended';
    public const STATUS_LATE     = 'late';
    public const STATUS_ABSENT   = 'absent';
    public const STATUS_EXCUSED  = 'excused';
    public const STATUS_PENDING  = 'pending_verification';

    protected $fillable = [
        'student_id',
        'live_class_id',
        'status',
        'joined_at',
        'left_at',
        'verified_at',
        'verified_by',
        'remarks',
    ];

    protected $casts = [
        'joined_at'   => 'datetime',
        'left_at'     => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function liveClass()
    {
        return $this->belongsTo(LiveClass::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function events()
    {
        return $this->hasMany(AttendanceEvent::class);
    }

    public function correctionRequests()
    {
        return $this->hasMany(AttendanceCorrectionRequest::class);
    }

    public function isVerified(): bool
    {
        return !is_null($this->verified_at);
    }

    public function isAttendedOrLate(): bool
    {
        return in_array($this->status, [self::STATUS_ATTENDED, self::STATUS_LATE], true);
    }
}
