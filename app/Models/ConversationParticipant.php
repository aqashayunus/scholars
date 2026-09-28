<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversationParticipant extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'conversation_id', 'user_id', 'role',
        'last_read_message_id', 'last_read_at',
        'muted_at', 'archived_at', 'joined_at', 'left_at',
    ];

    protected $casts = [
        'last_read_at' => 'datetime',
        'muted_at'     => 'datetime',
        'archived_at'  => 'datetime',
        'joined_at'    => 'datetime',
        'left_at'      => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function isMuted(): bool
    {
        return $this->muted_at !== null;
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }
}
