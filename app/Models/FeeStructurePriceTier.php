<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tiered price rule for a fee structure.
 *
 * Example for a tiered package:
 *   min=1, max=1, price=90.00   → 1 subject  → RM90
 *   min=2, max=2, price=160.00  → 2 subjects → RM160
 *   min=3, max=3, price=210.00  → 3 subjects → RM210
 *
 * Tiers must be non-overlapping and defined before the package becomes active.
 * PackagePricingService validates tier integrity on package save.
 */
class FeeStructurePriceTier extends Model
{
    protected $table = 'fee_structure_price_tiers';

    protected $fillable = [
        'fee_structure_id',
        'minimum_subjects',
        'maximum_subjects',
        'price',
        'sort_order',
    ];

    protected $casts = [
        'minimum_subjects' => 'integer',
        'maximum_subjects' => 'integer',
        'price'            => 'decimal:2',
        'sort_order'       => 'integer',
    ];

    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class);
    }

    /**
     * Does this tier apply to the given subject count?
     */
    public function appliesTo(int $subjectCount): bool
    {
        return $subjectCount >= $this->minimum_subjects
            && $subjectCount <= $this->maximum_subjects;
    }
}
