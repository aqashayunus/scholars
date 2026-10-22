<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailAutomationExecution extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'email_automation_id',
        'trigger_payload',
        'idempotency_key',
        'status',
        'recipient_email',
        'error_message',
        'executed_at',
        'created_at',
    ];

    protected $casts = [
        'trigger_payload' => 'array',
        'executed_at'     => 'datetime',
        'created_at'      => 'datetime',
    ];

    public function automation()
    {
        return $this->belongsTo(EmailAutomation::class, 'email_automation_id');
    }
}
