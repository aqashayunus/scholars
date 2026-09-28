<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatMessage extends Model
{
    use SoftDeletes;

    protected $table = 'chat_messages';

    protected $fillable = [
        'conversation_id', 'sender_id', 'body',
        'type', 'reply_to_id', 'status',
    ];

    protected $casts = [
        'edited_at' => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function replyTo()
    {
        return $this->belongsTo(ChatMessage::class, 'reply_to_id');
    }

    public function attachments()
    {
        return $this->hasMany(ChatMessageAttachment::class, 'message_id');
    }

    public function reads()
    {
        return $this->hasMany(ChatMessageRead::class, 'message_id');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    public function isReadBy(int $userId): bool
    {
        return $this->reads()->where('user_id', $userId)->exists();
    }

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->sender?->name ?? '');
        return strtoupper(
            substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : '')
        );
    }
}
