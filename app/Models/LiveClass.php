<?php

namespace App\Models;

use App\Enums\LiveClassStatus;
use Illuminate\Database\Eloquent\Model;

class LiveClass extends Model
{
    protected $fillable = [
        'course_id',
        'teacher_id',
        'education_level',
        'target_age',
        'topic',
        'subtopic',
        'title',
        'scheduled_at',
        'duration_minutes',
        'meeting_url',
        'status',
        'is_rescheduled',
        'original_scheduled_at',
        'reschedule_reason',
        'rescheduled_by',
    ];
    protected $casts = [
        'scheduled_at'          => 'datetime',
        'original_scheduled_at' => 'datetime',
        'duration_minutes'      => 'integer',
        'is_rescheduled'        => 'boolean',
    ];

    protected static function booted()
    {
        static::saving(function ($liveClass) {
            $liveClass->status = $liveClass->computed_status;
        });
    }

    public function getComputedStatusAttribute(): string
    {
        // Manual overrides ('live' via updateMeetingUrl; 'cancelled') must
        // survive the saving() hook's recomputation below, or the next save
        // silently discards them back to a time-based value.
        $current = $this->attributes['status'] ?? null;
        if ($current === 'cancelled') {
            return 'cancelled';
        }
        if ($current === 'live') {
            return 'live';
        }

        if (!$this->scheduled_at) {
            return 'upcoming';
        }
        $start = $this->scheduled_at instanceof \Carbon\Carbon ? $this->scheduled_at : \Carbon\Carbon::parse($this->scheduled_at);
        $minutes = (int) ($this->duration_minutes ?: 60);
        $end = $start->copy()->addMinutes($minutes);
        $now = now();

        if ($now->lt($start)) {
            return 'upcoming';
        } elseif ($now->between($start, $end)) {
            return 'live';
        } else {
            return 'completed';
        }
    }

    /**
     * Status is computed live from scheduled_at/duration (see
     * getComputedStatusAttribute()), not a simple stored value — this
     * accessor wraps that raw string in the LiveClassStatus enum so callers
     * get the same type-safe comparison behavior as every other status
     * field. The underlying DB column still stores the raw string (written
     * by the saving() hook below via computed_status), since MySQL's native
     * enum column needs a string, not a PHP enum instance.
     */
    public function getStatusAttribute($value): LiveClassStatus
    {
        if (!$this->scheduled_at) {
            return LiveClassStatus::tryFrom($value ?? 'upcoming') ?? LiveClassStatus::Upcoming;
        }
        return LiveClassStatus::from($this->computed_status);
    }

    public function course()      { return $this->belongsTo(Course::class); }
    public function teacher()     { return $this->belongsTo(Teacher::class); }
    public function rescheduler() { return $this->belongsTo(User::class, 'rescheduled_by'); }
    public function attendances() { return $this->hasMany(StudentAttendance::class); }
    public function attendanceEvents() { return $this->hasMany(AttendanceEvent::class); }
    public function attendanceCorrectionRequests() { return $this->hasMany(AttendanceCorrectionRequest::class); }

    public function getAssignedTeacherAttribute()
    {
        return $this->teacher ?? $this->course?->teacher;
    }
}
