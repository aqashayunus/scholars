<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * A student's answer to "Continue in {school year}?" and the monthly billing
 * window that follows from it. See the create_student_renewals migration.
 *
 * @property int $id
 * @property int $student_id
 * @property string $school_year
 * @property string $status
 * @property string $token
 * @property string|null $level
 * @property list<int>|null $subject_ids
 * @property int|null $fee_structure_id
 * @property Carbon|null $billing_starts
 * @property Carbon|null $stops_after
 * @property Carbon|null $invited_at
 * @property Carbon|null $reminded_at
 * @property Carbon|null $responded_at
 * @property int|null $responded_by_user_id
 * @property Carbon|null $paused_at
 * @property string|null $pause_reason
 * @property int|null $first_invoice_id
 */
class StudentRenewal extends Model
{
    public const INVITED = 'invited';

    public const CONFIRMED = 'confirmed';

    public const DECLINED = 'declined';

    public const LAPSED = 'lapsed';

    public const STOPPED = 'stopped';

    public const LABELS = [
        self::INVITED => 'Invited, no reply yet',
        self::CONFIRMED => 'Continuing',
        self::DECLINED => 'Not continuing',
        self::LAPSED => 'Paused',
        self::STOPPED => 'Stopped',
    ];

    protected $fillable = [
        'student_id', 'school_year', 'status', 'token', 'level', 'subject_ids', 'fee_structure_id',
        'billing_starts', 'stops_after', 'invited_at', 'reminded_at', 'responded_at',
        'responded_by_user_id', 'paused_at', 'pause_reason', 'first_invoice_id',
    ];

    protected $casts = [
        'subject_ids' => 'array',
        'billing_starts' => 'date',
        'stops_after' => 'date',
        'invited_at' => 'datetime',
        'reminded_at' => 'datetime',
        'responded_at' => 'datetime',
        'paused_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (StudentRenewal $r) {
            $r->token = $r->token ?: Str::random(48);
        });
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /** @return BelongsTo<FeeStructure, $this> */
    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class);
    }

    /** @return BelongsTo<User, $this> */
    public function respondedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by_user_id');
    }

    public function label(): string
    {
        return self::LABELS[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Confirmed renewals that should be billed for the given month: billing
     * has started and has not been stopped before that month.
     *
     * @param  Builder<StudentRenewal>  $q
     * @return Builder<StudentRenewal>
     */
    public function scopeBillableFor(Builder $q, string $schoolYear, Carbon $month): Builder
    {
        $first = $month->copy()->startOfMonth()->toDateString();

        return $q->where('school_year', $schoolYear)
            ->whereIn('status', [self::CONFIRMED, self::STOPPED])
            ->whereNotNull('billing_starts')
            ->whereDate('billing_starts', '<=', $first)
            ->where(fn ($s) => $s->whereNull('stops_after')->orWhereDate('stops_after', '>=', $first));
    }
}
