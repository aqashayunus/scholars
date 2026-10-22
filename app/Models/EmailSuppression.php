<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailSuppression extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'email',
        'reason',
        'suppressed_at',
        'created_at',
    ];

    protected $casts = [
        'suppressed_at' => 'datetime',
        'created_at'    => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($suppression) {
            $suppression->suppressed_at = $suppression->suppressed_at ?: now();
            $suppression->created_at = $suppression->created_at ?: now();
        });
    }

    public static function isSuppressed(string $email): bool
    {
        return static::where('email', strtolower(trim($email)))->exists();
    }

    public static function suppress(string $email, string $reason = 'unsubscribe'): self
    {
        return static::firstOrCreate(
            ['email' => strtolower(trim($email))],
            [
                'reason'        => $reason,
                'suppressed_at' => now(),
                'created_at'    => now(),
            ]
        );
    }
}
