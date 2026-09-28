<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type', 'context_type', 'context_id', 'title',
        'last_message_id', 'status', 'created_by',
    ];

    // ── Relationships ────────────────────────────────────────────────

    public function participants()
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function lastMessage()
    {
        return $this->belongsTo(ChatMessage::class, 'last_message_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    /**
     * Get the other participant in a 1-to-1 conversation.
     */
    public function otherParticipant(int $myUserId): ?User
    {
        $p = $this->participants()
            ->with('user')
            ->where('user_id', '!=', $myUserId)
            ->first();

        return $p?->user;
    }

    /**
     * Check if a user is a participant.
     */
    public function hasParticipant(int $userId): bool
    {
        return $this->participants()->where('user_id', $userId)->exists();
    }

    /**
     * Get unread count for a specific user.
     */
    public function unreadCountFor(int $userId): int
    {
        $participant = $this->participants()->where('user_id', $userId)->first();
        if (!$participant) return 0;

        $lastReadId = $participant->last_read_message_id ?? 0;

        return $this->messages()
            ->where('id', '>', $lastReadId)
            ->where('sender_id', '!=', $userId)
            ->count();
    }
}
