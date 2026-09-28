<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeachingGig extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'title',
        'gig_type',
        'scheduled_at',
        'duration_minutes',
        'payout_amount',
        'surge_bonus',
        'status',
        'assigned_teacher_id',
        'claimed_at',
        'checked_in_at',
        'completed_at',
        'meeting_url',
        'notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'claimed_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'assigned_teacher_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function getTotalPayoutAttribute()
    {
        return $this->payout_amount + $this->surge_bonus;
    }
}
