<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'description',
        'subject',
        'body',
        'cta_label',
        'cta_url',
        'template_id',
        'audience_type',
        'audience_filters',
        'total_recipients',
        'sent_count',
        'failed_count',
        'status',
        'scheduled_at',
        'started_at',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'audience_filters' => 'array',
        'scheduled_at'     => 'datetime',
        'started_at'       => 'datetime',
        'completed_at'     => 'datetime',
        'total_recipients' => 'integer',
        'sent_count'       => 'integer',
        'failed_count'     => 'integer',
    ];

    public const STATUS_DRAFT      = 'draft';
    public const STATUS_SCHEDULED  = 'scheduled';
    public const STATUS_QUEUED     = 'queued';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED  = 'completed';
    public const STATUS_CANCELLED  = 'cancelled';
    public const STATUS_FAILED     = 'failed';

    public function template()
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients()
    {
        return $this->hasMany(EmailCampaignRecipient::class);
    }

    public function logs()
    {
        return $this->hasMany(EmailLog::class, 'email_campaign_id');
    }

    public function getProgressPercentAttribute(): int
    {
        if ($this->total_recipients <= 0) {
            return $this->status === self::STATUS_COMPLETED ? 100 : 0;
        }

        $processed = $this->sent_count + $this->failed_count;
        return (int) min(100, round(($processed / $this->total_recipients) * 100));
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SCHEDULED, self::STATUS_QUEUED]);
    }
}
