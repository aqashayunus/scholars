<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherClassClaim extends Model
{
    use HasFactory;

    protected $fillable = [
        'claim_number',
        'teacher_id',
        'live_class_id',
        'course_id',
        'subject_name',
        'class_title',
        'class_date',
        'duration_minutes',
        'rate_per_class',
        'amount',
        'teacher_notes',
        'proof_attachment_path',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'status',
        'admin_notes',
        'approved_by',
        'approved_at',
        'paid_at',
        'payment_method',
        'payment_reference',
    ];

    protected $casts = [
        'class_date' => 'date',
        'duration_minutes' => 'integer',
        'rate_per_class' => 'decimal:2',
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function liveClass()
    {
        return $this->belongsTo(LiveClass::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public static function generateClaimNumber(): string
    {
        $prefix = 'CLM-'.date('Ym').'-';
        $latest = static::where('claim_number', 'LIKE', $prefix.'%')
            ->orderByDesc('id')
            ->first();

        if ($latest && preg_match('/'.preg_quote($prefix, '/').'(\d+)/', $latest->claim_number, $matches)) {
            $num = intval($matches[1]) + 1;
        } else {
            $num = 1;
        }

        return $prefix.str_pad((string) $num, 4, '0', STR_PAD_LEFT);
    }
}
