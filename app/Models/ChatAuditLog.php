<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatAuditLog extends Model
{
    protected $table = 'chat_audit_logs';

    public $timestamps = false;

    protected $fillable = [
        'viewer_id', 'child_id', 'conversation_id',
        'action', 'metadata', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'created_at' => 'datetime',
    ];

    public function viewer()
    {
        return $this->belongsTo(User::class, 'viewer_id');
    }

    public function child()
    {
        return $this->belongsTo(User::class, 'child_id');
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
