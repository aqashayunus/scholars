<?php

namespace App\Models;

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

    public function getStatusAttribute($value): string
    {
        if (!$this->scheduled_at) {
            return $value ?? 'upcoming';
        }
        return $this->computed_status;
    }

    public function course()      { return $this->belongsTo(Course::class); }
    public function teacher()     { return $this->belongsTo(Teacher::class); }
    public function rescheduler() { return $this->belongsTo(User::class, 'rescheduled_by'); }
    public function attendances() { return $this->hasMany(StudentAttendance::class); }

    public function getAssignedTeacherAttribute()
    {
        return $this->teacher ?? $this->course?->teacher;
    }
}
