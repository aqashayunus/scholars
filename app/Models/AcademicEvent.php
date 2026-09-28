<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class AcademicEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'event_type',
        'start_date',
        'end_date',
        'is_all_day',
        'color',
        'state_or_region',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_all_day' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Scope to only public holidays */
    public function scopeHolidays(Builder $query): Builder
    {
        return $query->where('event_type', 'public_holiday');
    }

    /** Scope to events spanning or occurring within a date range */
    public function scopeInDateRange(Builder $query, $start, $end): Builder
    {
        $startDate = $start instanceof Carbon ? $start->toDateString() : $start;
        $endDate   = $end instanceof Carbon ? $end->toDateString() : $end;

        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhere(function ($q2) use ($startDate, $endDate) {
                  $q2->whereNotNull('end_date')
                     ->where('start_date', '<=', $endDate)
                     ->where('end_date', '>=', $startDate);
              });
        });
    }

    /** Scope for a specific year */
    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->where(function ($q) use ($year) {
            $q->whereYear('start_date', $year)
              ->orWhere(function ($q2) use ($year) {
                  $q2->whereNotNull('end_date')
                     ->whereYear('end_date', $year);
              });
        });
    }

    /** Event color badge fallback */
    public function getDisplayColorAttribute(): string
    {
        if ($this->color) {
            return $this->color;
        }

        return match ($this->event_type) {
            'public_holiday' => '#ef4444', // red
            'school_break'   => '#f59e0b', // amber
            'exam_period'    => '#8b5cf6', // purple
            'academic_term'  => '#3b82f6', // blue
            default          => '#10b981', // emerald
        };
    }
}
