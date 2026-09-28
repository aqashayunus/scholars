<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherEarning extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'teaching_gig_id',
        'description',
        'base_amount',
        'bonus_amount',
        'total_amount',
        'status',
        'payout_batch_id',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function gig()
    {
        return $this->belongsTo(TeachingGig::class, 'teaching_gig_id');
    }
}
