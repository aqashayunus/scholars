<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassRequest extends Model
{
    protected $fillable = [
        'live_class_id',
        'teacher_id',
        'type',
        'reason',
        'requested_at',
        'status',
        'admin_note'
    ];

    protected $casts = [
        'requested_at' => 'datetime',
    ];

    public function liveClass()
    {
        return $this->belongsTo(LiveClass::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
