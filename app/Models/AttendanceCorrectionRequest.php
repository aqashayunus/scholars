<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceCorrectionRequest extends Model
{
    public const STATUS_PENDING      = 'pending';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_APPROVED     = 'approved';
    public const STATUS_REJECTED     = 'rejected';

    protected $fillable = [
        'student_id',
        'live_class_id',
        'student_attendance_id',
        'requested_status',
        'reason',
        'status',
        'reviewer_id',
        'reviewer_remarks',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function liveClass()
    {
        return $this->belongsTo(LiveClass::class);
    }

    public function studentAttendance()
    {
        return $this->belongsTo(StudentAttendance::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
