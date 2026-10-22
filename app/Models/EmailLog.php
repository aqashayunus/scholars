<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_campaign_id',
        'email_template_id',
        'email_automation_id',
        'recipient_user_id',
        'recipient_email',
        'recipient_name',
        'recipient_type',
        'related_student_id',
        'related_invoice_id',
        'subject',
        'category',
        'body_preview',
        'status',
        'provider_message_id',
        'attempts',
        'error_message',
        'queued_at',
        'sent_at',
        'delivered_at',
    ];

    protected $casts = [
        'attempts'     => 'integer',
        'queued_at'    => 'datetime',
        'sent_at'      => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public const STATUS_QUEUED    = 'queued';
    public const STATUS_SENT      = 'sent';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_FAILED    = 'failed';

    public function campaign()
    {
        return $this->belongsTo(EmailCampaign::class, 'email_campaign_id');
    }

    public function template()
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }

    public function automation()
    {
        return $this->belongsTo(EmailAutomation::class, 'email_automation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'related_student_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'related_invoice_id');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeSent($query)
    {
        return $query->whereIn('status', [self::STATUS_SENT, self::STATUS_DELIVERED]);
    }
}
