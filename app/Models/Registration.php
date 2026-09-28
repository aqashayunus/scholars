<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'register_as',
        'student_name',
        'parent_name',
        'educator_name',
        'school_name',
        'email',
        'phone',
        'programme',
        'level',
        'subjects',
        'schedule',
        'promo_code',
        'notes',
        'status',
    ];

    protected $casts = [
        'subjects' => 'array',
    ];

    /**
     * Store and retrieve names in uppercase.
     */
    protected function studentName(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn ($value) => $value ? mb_strtoupper($value, 'UTF-8') : $value,
            set: fn ($value) => $value ? mb_strtoupper(trim($value), 'UTF-8') : $value,
        );
    }

    protected function parentName(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn ($value) => $value ? mb_strtoupper($value, 'UTF-8') : $value,
            set: fn ($value) => $value ? mb_strtoupper(trim($value), 'UTF-8') : $value,
        );
    }

    protected function educatorName(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn ($value) => $value ? mb_strtoupper($value, 'UTF-8') : $value,
            set: fn ($value) => $value ? mb_strtoupper(trim($value), 'UTF-8') : $value,
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
