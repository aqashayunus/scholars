<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A billing component within a fee structure.
 *
 * Supports multi-component billing:
 *   Tuition   RM250  monthly
 *   Materials  RM80  annual
 *   Registration RM50 one_time
 *   Deposit   RM100  one_time
 *
 * The parent fee_structures.amount remains the "base tuition" for backward
 * compatibility and single-item packages. FeeStructureItems are the
 * rich, itemized version used by the new PackagePricingService.
 */
class FeeStructureItem extends Model
{
    protected $table = 'fee_structure_items';

    // Item type constants
    const TYPE_REGISTRATION = 'registration';
    const TYPE_TUITION      = 'tuition';
    const TYPE_MATERIAL     = 'material';
    const TYPE_RESOURCE     = 'resource';
    const TYPE_ASSESSMENT   = 'assessment';
    const TYPE_DEPOSIT      = 'deposit';
    const TYPE_OTHER        = 'other';

    // Billing frequency constants
    const FREQ_ONE_TIME = 'one_time';
    const FREQ_MONTHLY  = 'monthly';
    const FREQ_TERM     = 'term';
    const FREQ_ANNUAL   = 'annual';

    protected $fillable = [
        'fee_structure_id',
        'name',
        'description',
        'item_type',
        'amount',
        'quantity',
        'billing_frequency',
        'is_optional',
        'is_refundable',
        'sort_order',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'quantity'     => 'integer',
        'is_optional'  => 'boolean',
        'is_refundable'=> 'boolean',
        'sort_order'   => 'integer',
    ];

    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class);
    }

    /**
     * Total for this line item (amount × quantity).
     */
    public function getLineAmountAttribute(): float
    {
        return round((float) $this->amount * (int) $this->quantity, 2);
    }

    /**
     * Human-readable billing frequency label.
     */
    public function getFrequencyLabelAttribute(): string
    {
        return match ($this->billing_frequency) {
            self::FREQ_ONE_TIME => 'One-time',
            self::FREQ_MONTHLY  => 'Monthly',
            self::FREQ_TERM     => 'Per Term',
            self::FREQ_ANNUAL   => 'Annual',
            default             => ucfirst(str_replace('_', ' ', $this->billing_frequency)),
        };
    }
}
