<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Carbon;

/**
 * The school year families renew for (admin setting, decision 2026-11-27).
 *
 *   school_year.label           e.g. "2027"
 *   school_year.start / .end    Y-m-d, the school year itself
 *   school_year.first_billing   Y-m-d (1st of a month): first month invoiced
 *   school_year.reply_by        Y-m-d: last day to answer "Continue?";
 *                               unanswered invitations are paused after it
 */
final class SchoolYear
{
    public const KEYS = [
        'label' => 'school_year.label',
        'start' => 'school_year.start',
        'end' => 'school_year.end',
        'first_billing' => 'school_year.first_billing',
        'reply_by' => 'school_year.reply_by',
    ];

    private function __construct(
        public readonly string $label,
        public readonly Carbon $start,
        public readonly Carbon $end,
        public readonly Carbon $firstBilling,
        public readonly ?Carbon $replyBy,
    ) {}

    /** The configured school year, or null while it is not (fully) set up. */
    public static function current(): ?self
    {
        $label = trim((string) Setting::get(self::KEYS['label'], ''));
        $start = self::date(Setting::get(self::KEYS['start']));
        $end = self::date(Setting::get(self::KEYS['end']));
        if ($label === '' || ! $start || ! $end || $end->lt($start)) {
            return null;
        }
        $firstBilling = (self::date(Setting::get(self::KEYS['first_billing'])) ?? $start)->copy()->startOfMonth();

        return new self($label, $start, $end->copy()->endOfDay(), $firstBilling, self::date(Setting::get(self::KEYS['reply_by']))?->endOfDay());
    }

    /** Whether the given month is inside the billed part of the school year. */
    public function bills(Carbon $month): bool
    {
        $first = $month->copy()->startOfMonth();

        return $first->gte($this->firstBilling) && $first->lte($this->end->copy()->startOfMonth());
    }

    /** The month a newly confirmed family is billed for first: the first billing month, or this month if later. */
    public function firstMonthFor(Carbon $today): Carbon
    {
        $thisMonth = $today->copy()->startOfMonth();

        return $thisMonth->gt($this->firstBilling) ? $thisMonth : $this->firstBilling->copy();
    }

    private static function date(mixed $raw): ?Carbon
    {
        if (! is_string($raw) || $raw === '') {
            return null;
        }
        try {
            return Carbon::createFromFormat('Y-m-d', $raw)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
