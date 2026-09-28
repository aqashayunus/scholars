<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessageAttachment extends Model
{
    protected $table = 'chat_message_attachments';

    protected $fillable = [
        'message_id', 'disk', 'path',
        'original_name', 'mime_type', 'size_bytes', 'checksum',
    ];

    public function message()
    {
        return $this->belongsTo(ChatMessage::class, 'message_id');
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function humanSize(): string
    {
        $bytes = $this->size_bytes;
        if ($bytes < 1024) return "{$bytes} B";
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }
}
