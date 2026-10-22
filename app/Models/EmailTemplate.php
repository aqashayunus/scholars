<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'key',
        'category',
        'subject',
        'body',
        'banner_url',
        'cta_label',
        'cta_url',
        'status',
        'created_by',
        'updated_by',
    ];

    protected static function booted()
    {
        static::creating(function ($template) {
            if (empty($template->key)) {
                $template->key = \Illuminate\Support\Str::slug($template->name, '_') . '_' . uniqid();
            }
            if (empty($template->status)) {
                $template->status = self::STATUS_ACTIVE;
            }
        });
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function setIsActiveAttribute($val): void
    {
        $this->attributes['status'] = $val ? self::STATUS_ACTIVE : self::STATUS_INACTIVE;
    }

    public function setBodyTextAttribute($val): void
    {
        $this->attributes['body'] = $val;
    }

    public const CATEGORY_GREETINGS   = 'greetings';
    public const CATEGORY_PAYMENT     = 'payment';
    public const CATEGORY_RESTRICTION = 'restriction';
    public const CATEGORY_CLASS       = 'class';
    public const CATEGORY_ACADEMIC    = 'academic';
    public const CATEGORY_RENEWAL     = 'renewal';
    public const CATEGORY_SUPPORT     = 'support';
    public const CATEGORY_GENERAL     = 'general';

    public const STATUS_ACTIVE   = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_ARCHIVED = 'archived';

    public static function categories(): array
    {
        return [
            self::CATEGORY_GREETINGS   => 'Greetings & Onboarding',
            self::CATEGORY_PAYMENT     => 'Invoices & Payments',
            self::CATEGORY_RESTRICTION => 'Access & Restrictions',
            self::CATEGORY_CLASS       => 'Classes & Timetables',
            self::CATEGORY_ACADEMIC    => 'Academic & Reports',
            self::CATEGORY_RENEWAL     => 'Renewals & Engagement',
            self::CATEGORY_SUPPORT     => 'Support & Account',
            self::CATEGORY_GENERAL     => 'General Announcements',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function versions()
    {
        return $this->hasMany(EmailTemplateVersion::class)->orderBy('version_number', 'desc');
    }

    public function campaigns()
    {
        return $this->hasMany(EmailCampaign::class, 'template_id');
    }

    public function automations()
    {
        return $this->hasMany(EmailAutomation::class, 'email_template_id');
    }

    public function logs()
    {
        return $this->hasMany(EmailLog::class, 'email_template_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeNotArchived($query)
    {
        return $query->where('status', '!=', self::STATUS_ARCHIVED);
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::categories()[$this->category] ?? ucfirst($this->category);
    }

    /**
     * Create a new version snapshot before saving edits.
     */
    public function recordVersion(?int $userId = null): void
    {
        $latest = $this->versions()->max('version_number') ?? 0;

        EmailTemplateVersion::create([
            'email_template_id' => $this->id,
            'version_number'    => $latest + 1,
            'subject'           => $this->subject,
            'body'              => $this->body,
            'cta_label'         => $this->cta_label,
            'cta_url'           => $this->cta_url,
            'changed_by'        => $userId ?? auth()->id(),
            'created_at'        => now(),
        ]);
    }
}
