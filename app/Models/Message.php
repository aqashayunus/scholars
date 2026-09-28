<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Message extends Model
{
    protected $fillable = ['sender_id', 'receiver_id', 'body', 'read_at'];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    // ── Relationships ───────────────────────────────
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    // ── Scopes ──────────────────────────────────────

    /**
     * All messages in a conversation between two users.
     */
    public function scopeBetween(Builder $query, int $userA, int $userB): Builder
    {
        return $query->where(function ($q) use ($userA, $userB) {
            $q->where('sender_id', $userA)->where('receiver_id', $userB);
        })->orWhere(function ($q) use ($userA, $userB) {
            $q->where('sender_id', $userB)->where('receiver_id', $userA);
        });
    }

    // ── Helpers ─────────────────────────────────────
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }
}
