<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassRequest extends Model
{
    protected $fillable = [
        'live_class_id',
        'teacher_id',
        'type',
        'reason',
        'requested_at',
        'status',
        'admin_note',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<LiveClass, $this>
     */
    public function liveClass(): BelongsTo
    {
        return $this->belongsTo(LiveClass::class);
    }

    /**
     * @return BelongsTo<Teacher, $this>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
