<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * FeeStructure — Package & Pricing Engine
 *
 * Backward-compatible upgrade of the original flat fee structure.
 *
 * NEW package types (package_type):
 *   individual   — flat price, no subject constraint (legacy behaviour)
 *   fixed_bundle — exact set of predefined subjects at one price
 *   pick_n       — student picks min–max subjects from a defined pool
 *   tiered       — price scales with number of subjects selected
 *   custom       — admin-controlled special pricing
 *
 * Versioning:
 *   version + parent_fee_structure_id track package history.
 *   Existing enrollments reference the version that was active at enrollment time.
 *   Historical invoices remain immutable through invoice_items snapshots.
 */
class FeeStructure extends Model
{
    use SoftDeletes;

    // ── Package Type Constants ────────────────────────────────────────────────
    const TYPE_INDIVIDUAL    = 'individual';
    const TYPE_FIXED_BUNDLE  = 'fixed_bundle';
    const TYPE_PICK_N        = 'pick_n';
    const TYPE_TIERED        = 'tiered';
    const TYPE_CUSTOM        = 'custom';

    // ── Status Constants ──────────────────────────────────────────────────────
    const STATUS_DRAFT    = 'draft';
    const STATUS_ACTIVE   = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        // ── Legacy / backward-compatible fields ──────────────────────────────
        'name',
        'description',
        'amount',           // base tuition amount (authoritative for individual/fixed_bundle/pick_n/custom)
        'education_level',
        'frequency',        // monthly|term|yearly|one_time (legacy billing frequency)
        'is_active',        // boolean — kept for backward compat; new code uses `status`
        'student_id',       // targeted student (optional personalised pricing)

        // ── New package engine fields ─────────────────────────────────────────
        'package_type',
        'minimum_subjects',
        'maximum_subjects',
        'effective_from',
        'effective_until',
        'status',
        'version',
        'parent_fee_structure_id',
        'is_stackable_discount',
    ];

    protected $casts = [
        'amount'               => 'decimal:2',
        'is_active'            => 'boolean',
        'is_stackable_discount'=> 'boolean',
        'minimum_subjects'     => 'integer',
        'maximum_subjects'     => 'integer',
        'version'              => 'integer',
        'effective_from'       => 'date',
        'effective_until'      => 'date',
        'deleted_at'           => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    /** Legacy: invoices that were created under this fee structure. */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /** Targeted student (optional personalised pricing). */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Subjects explicitly assigned to this package.
     *
     * For fixed_bundle: these are the required subjects included in the price.
     * For pick_n/tiered: these are the allowed subjects the student may pick from.
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'fee_structure_subjects')
                    ->withPivot(['is_required', 'sort_order'])
                    ->withTimestamps()
                    ->orderBy('fee_structure_subjects.sort_order');
    }

    /**
     * Required subjects only (for fixed_bundle packages).
     */
    public function requiredSubjects()
    {
        return $this->belongsToMany(Subject::class, 'fee_structure_subjects')
                    ->withPivot(['is_required', 'sort_order'])
                    ->wherePivot('is_required', true)
                    ->orderBy('fee_structure_subjects.sort_order');
    }

    /**
     * Configurable billing components (tuition, materials, registration, etc.).
     * When present, these drive invoice generation instead of the flat `amount`.
     */
    public function items()
    {
        return $this->hasMany(FeeStructureItem::class)->orderBy('sort_order');
    }

    /**
     * Price tiers (for tiered packages).
     */
    public function priceTiers()
    {
        return $this->hasMany(FeeStructurePriceTier::class)->orderBy('sort_order')->orderBy('minimum_subjects');
    }

    /**
     * Parent package (when this is a newer version of an existing package).
     */
    public function parent()
    {
        return $this->belongsTo(FeeStructure::class, 'parent_fee_structure_id');
    }

    /**
     * Newer versions of this package.
     */
    public function children()
    {
        return $this->hasMany(FeeStructure::class, 'parent_fee_structure_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->where('status', self::STATUS_ACTIVE)
              ->orWhere(function ($q2) {
                  // Legacy: packages without the new status column use is_active
                  $q2->whereNull('status')->where('is_active', true);
              });
        });
    }

    public function scopeCurrentlyValid($query)
    {
        $today = now()->toDateString();
        return $query->where(function ($q) use ($today) {
            $q->whereNull('effective_from')->orWhere('effective_from', '<=', $today);
        })->where(function ($q) use ($today) {
            $q->whereNull('effective_until')->orWhere('effective_until', '>=', $today);
        });
    }

    /**
     * Packages available for new registrations.
     */
    public function scopeAvailableForRegistration($query)
    {
        return $query->active()->currentlyValid();
    }

    // ── Accessors & Helpers ───────────────────────────────────────────────────

    public function getIsCurrentlyValidAttribute(): bool
    {
        $today = now()->toDate();
        $validFrom  = $this->effective_from  ? $this->effective_from->toDate()  : null;
        $validUntil = $this->effective_until ? $this->effective_until->toDate() : null;

        if ($validFrom  && $today < $validFrom)  return false;
        if ($validUntil && $today > $validUntil) return false;
        return true;
    }

    public function getIsAvailableAttribute(): bool
    {
        $activeStatus = $this->status === self::STATUS_ACTIVE
            || ($this->status === null && $this->is_active);

        return $activeStatus && $this->is_currently_valid;
    }

    /**
     * True when this package has explicit billing components configured.
     * When true, PackagePricingService uses items instead of flat `amount`.
     */
    public function hasItems(): bool
    {
        return $this->items()->exists();
    }

    /**
     * True when this package has explicit subject mappings.
     */
    public function hasSubjects(): bool
    {
        return $this->subjects()->exists();
    }

    /**
     * Human-readable package type label.
     */
    public function getPackageTypeLabelAttribute(): string
    {
        return match ($this->package_type) {
            self::TYPE_FIXED_BUNDLE => 'Fixed Bundle',
            self::TYPE_PICK_N       => 'Pick-Any-N',
            self::TYPE_TIERED       => 'Tiered Pricing',
            self::TYPE_CUSTOM       => 'Custom',
            default                 => 'Individual',
        };
    }

    /**
     * Status label (supports legacy is_active-only packages).
     */
    public function getStatusLabelAttribute(): string
    {
        if ($this->status) {
            return ucfirst($this->status);
        }
        return $this->is_active ? 'Active' : 'Inactive';
    }
}
