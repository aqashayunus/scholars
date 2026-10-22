<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailAutomation extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'category',
        'trigger_event',
        'description',
        'email_template_id',
        'is_enabled',
        'delay_minutes',
        'recipient_rules',
        'last_triggered_at',
        'success_count',
        'failure_count',
    ];

    protected $casts = [
        'is_enabled'         => 'boolean',
        'delay_minutes'      => 'integer',
        'recipient_rules'    => 'array',
        'last_triggered_at'  => 'datetime',
        'success_count'      => 'integer',
        'failure_count'      => 'integer',
    ];

    public function template()
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }

    public function executions()
    {
        return $this->hasMany(EmailAutomationExecution::class)->orderBy('executed_at', 'desc');
    }

    public function logs()
    {
        return $this->hasMany(EmailLog::class, 'email_automation_id');
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }
}
