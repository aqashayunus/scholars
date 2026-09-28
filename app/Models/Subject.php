<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'education_type', // kindergarten, primary, secondary
        'stage',          // Preschool, Primary, Lower Secondary, Upper Secondary
        'form_year',      // Preschool, Year 1–6, Form 1–3, Form 4, Form 5, Form 4–5
        'category',       // Core, Elective, Early Development, Skill & Applied, STEM Elective, Humanities Elective, Commerce Elective
        'stream_package', // STEM Pure Science, Commerce & Accounting, Humanities & Arts, STEM Computing, General
        'code',           // KPM curriculum code
        'description',
        'icon',
        'level',          // Legacy/badge field e.g. KSSR, KSSM, SPM, Tadika
        'color',
        'order',
        'is_active',
        'monthly_fee',    // Admin-configurable monthly fee for this subject (authoritative pricing source)
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'order'       => 'integer',
        'monthly_fee' => 'decimal:2',
    ];

    // ── Relationships ────────────────────────────────────────────────────────
    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    /**
     * Invoice line items that have this subject (historical records).
     */
    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Fee structure packages that include this subject.
     */
    public function feeStructures()
    {
        return $this->belongsToMany(FeeStructure::class, 'fee_structure_subjects')
                    ->withPivot(['is_required', 'sort_order'])
                    ->withTimestamps();
    }

    /**
     * Returns the current monthly fee, or 0 if not configured.
     */
    public function getCurrentFeeAttribute(): float
    {
        return (float) ($this->monthly_fee ?? 0);
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'teacher_subject');
    }

    public function topics()
    {
        return $this->hasMany(SubjectTopic::class)->orderBy('chapter_number', 'asc');
    }

    public function learningMaterials()
    {
        return $this->hasMany(LearningMaterial::class);
    }

    // ── Query Scopes ─────────────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeKindergarten($query)
    {
        return $query->where('education_type', 'kindergarten');
    }

    public function scopePrimary($query)
    {
        return $query->where('education_type', 'primary');
    }

    public function scopeSecondary($query)
    {
        return $query->where('education_type', 'secondary');
    }

    public function scopeLowerSecondary($query)
    {
        return $query->where('education_type', 'secondary')
                     ->where(function ($q) {
                         $q->where('stage', 'Lower Secondary')
                           ->orWhere('form_year', 'like', '%Form 1%')
                           ->orWhere('form_year', 'like', '%Form 2%')
                           ->orWhere('form_year', 'like', '%Form 3%')
                           ->orWhere('form_year', 'Form 1–3');
                     });
    }

    public function scopeUpperSecondary($query)
    {
        return $query->where('education_type', 'secondary')
                     ->where(function ($q) {
                         $q->where('stage', 'Upper Secondary')
                           ->orWhere('form_year', 'like', '%Form 4%')
                           ->orWhere('form_year', 'like', '%Form 5%')
                           ->orWhere('form_year', 'Form 4–5');
                     });
    }

    public function scopeCore($query)
    {
        return $query->where('category', 'Core');
    }

    public function scopeElective($query)
    {
        return $query->where(function ($q) {
            $q->where('category', 'Elective')
              ->orWhere('category', 'like', '%Elective%');
        });
    }

    public function scopeActiveOffered($query)
    {
        return $query->where('is_active', true)
                     ->where('education_type', '!=', 'kindergarten')
                     ->whereIn('form_year', [
                         'Year 4', 'Year 5', 'Year 6',
                         'Form 1', 'Form 2', 'Form 3',
                         'Form 4', 'Form 5'
                     ]);
    }

    public function scopeByFormYear($query, $formYear)
    {
        return $query->where('form_year', $formYear);
    }

    // ── Accessors & Helpers ──────────────────────────────────────────────────
    public function getDisplayNameAttribute(): string
    {
        return $this->form_year ? "{$this->name} ({$this->form_year})" : $this->name;
    }
    public function getCurriculumBadgeAttribute(): string
    {
        if ($this->education_type === 'kindergarten') {
            return 'KSPK Prasekolah';
        }
        if ($this->education_type === 'primary') {
            return 'KSSR ' . ($this->form_year ?? 'Primary');
        }
        if ($this->stage === 'Lower Secondary' || in_array($this->form_year, ['Form 1', 'Form 2', 'Form 3', 'Form 1–3'])) {
            return 'KSSM ' . ($this->form_year ?? 'Form 1–3');
        }
        return 'KSSM SPM (' . ($this->category === 'Core' ? 'Teras' : 'Elektif') . ')';
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
