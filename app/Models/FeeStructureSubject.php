<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Pivot model for the fee_structure_subjects relationship.
 *
 * Tracks which subjects belong to each fee structure package,
 * including whether a subject is required (fixed bundle) or optional (pool).
 */
class FeeStructureSubject extends Model
{
    protected $table = 'fee_structure_subjects';

    protected $fillable = [
        'fee_structure_id',
        'subject_id',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'is_required'  => 'boolean',
        'sort_order'   => 'integer',
    ];

    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
