<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplateVersion extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'email_template_id',
        'version_number',
        'subject',
        'body',
        'cta_label',
        'cta_url',
        'changed_by',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function template()
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
