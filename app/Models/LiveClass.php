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

    public function getStatusAttribute($value): string
    {
        if ($value === 'cancelled') {
            return 'cancelled';
        }
        if ($value === 'live') {
            return 'live';
        }
        if (!$this->scheduled_at) {
            return $value ?? 'upcoming';
        }
        return $this->computed_status;
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

    /**
     * Scope a query to only include classes that are currently live or upcoming.
     */
    public function scopeActiveOrUpcoming($query)
    {
        $now = now();
        $isSqlite = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite';
        $endExpr = $isSqlite
            ? "datetime(scheduled_at, '+' || COALESCE(duration_minutes, 60) || ' minutes')"
            : "DATE_ADD(scheduled_at, INTERVAL COALESCE(duration_minutes, 60) MINUTE)";

        return $query->where('status', '!=', 'cancelled')
            ->where(function($q) use ($now, $endExpr) {
                $q->where('status', 'live')
                  ->orWhere('scheduled_at', '>=', $now)
                  ->orWhereRaw("{$endExpr} >= ?", [$now]);
            });
    }

    /**
     * Scope a query to only include classes that are completed or whose scheduled duration has passed.
     */
    public function scopeCompletedOrPast($query)
    {
        $now = now();
        $isSqlite = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite';
        $endExpr = $isSqlite
            ? "datetime(scheduled_at, '+' || COALESCE(duration_minutes, 60) || ' minutes')"
            : "DATE_ADD(scheduled_at, INTERVAL COALESCE(duration_minutes, 60) MINUTE)";

        return $query->where('status', '!=', 'cancelled')
            ->where(function($q) use ($now, $endExpr) {
                $q->where('status', 'completed')
                  ->orWhere(function($sub) use ($now, $endExpr) {
                      $sub->where('status', '!=', 'live')
                          ->whereRaw("{$endExpr} < ?", [$now]);
                  });
            });
    }

    public function isOngoingOrUpcoming(): bool
    {
        if ($this->status === 'cancelled') return false;
        if ($this->status === 'live') return true;
        if (!$this->scheduled_at) return true;
        $end = $this->scheduled_at->copy()->addMinutes($this->duration_minutes ?: 60);
        return $end->gte(now());
    }

    public function isPastOrCompleted(): bool
    {
        if ($this->status === 'completed') return true;
        if ($this->status === 'live') return false;
        if (!$this->scheduled_at) return false;
        $end = $this->scheduled_at->copy()->addMinutes($this->duration_minutes ?: 60);
        return $end->lt(now());
    }
}
