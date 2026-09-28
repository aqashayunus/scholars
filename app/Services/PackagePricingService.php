<?php

namespace App\Services;

use App\Models\FeeStructure;
use App\Models\FeeStructurePriceTier;
use App\Models\Subject;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * PackagePricingService — Server-side authoritative pricing engine.
 *
 * ALL pricing decisions flow through this service.
 * Controllers, Blade templates, and JavaScript must NEVER be the
 * authoritative source for package pricing calculations.
 *
 * Usage:
 *   $service = app(PackagePricingService::class);
 *   $result  = $service->calculate($feeStructure, $subjectIds);
 *   // $result->totalAmount is authoritative
 *
 * Pricing priority (highest to lowest):
 *   1. Explicit package pricing (this service)
 *   2. Package-specific billing items
 *   3. Tiered pricing rules
 *   4. Legacy elective discount (billing.bundle_elective_discount_pct)
 *   5. Subject monthly_fee sum (individual unbundled)
 */
class PackagePricingService
{
    // ── Public API ────────────────────────────────────────────────────────────

    /**
     * Retrieve packages available for a given context.
     *
     * @param  string|null $educationLevel  e.g. 'Form 5', 'Year 4'
     * @param  int|null    $studentId       for targeted package filtering
     * @return Collection<FeeStructure>
     */
    public function getAvailablePackages(?string $educationLevel = null, ?int $studentId = null): Collection
    {
        $query = FeeStructure::with(['subjects', 'items', 'priceTiers'])
            ->availableForRegistration();

        if ($educationLevel) {
            $query->where(function ($q) use ($educationLevel) {
                $q->where('education_level', 'All')
                  ->orWhere('education_level', $educationLevel)
                  ->orWhere('education_level', 'like', '%' . explode(' ', $educationLevel)[0] . '%');
            });
        }

        // Student-targeted packages: show general packages + packages targeted at this student
        // Exclude packages targeted at OTHER students
        if ($studentId) {
            $query->where(function ($q) use ($studentId) {
                $q->whereNull('student_id')
                  ->orWhere('student_id', $studentId);
            });
        } else {
            $query->whereNull('student_id');
        }

        return $query->orderBy('amount')->get();
    }

    /**
     * Get the allowed subjects for a given fee structure.
     *
     * For packages with explicit subject mappings, returns those.
     * For legacy packages (no mappings), returns all active subjects
     * matching the education level.
     *
     * @return Collection<Subject>
     */
    public function getAvailableSubjects(FeeStructure $feeStructure): Collection
    {
        if ($feeStructure->hasSubjects()) {
            return $feeStructure->subjects()->get();
        }

        // Legacy fallback: return subjects matching education_level
        return Subject::where('is_active', true)
            ->when($feeStructure->education_level && $feeStructure->education_level !== 'All', function ($q) use ($feeStructure) {
                $level = $feeStructure->education_level;
                $q->where(function ($inner) use ($level) {
                    $inner->where('form_year', $level)
                          ->orWhere('stage', 'like', '%' . $level . '%');
                });
            })
            ->orderBy('order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Validate that the provided subject IDs are acceptable for the given package.
     *
     * @param  FeeStructure $feeStructure
     * @param  int[]        $subjectIds
     * @throws ValidationException if selection is invalid
     */
    public function validateSubjectSelection(FeeStructure $feeStructure, array $subjectIds): void
    {
        $packageType = $feeStructure->package_type ?? FeeStructure::TYPE_INDIVIDUAL;

        switch ($packageType) {
            case FeeStructure::TYPE_FIXED_BUNDLE:
                $this->validateFixedBundle($feeStructure, $subjectIds);
                break;

            case FeeStructure::TYPE_PICK_N:
                $this->validatePickN($feeStructure, $subjectIds);
                break;

            case FeeStructure::TYPE_TIERED:
                $this->validateTiered($feeStructure, $subjectIds);
                break;

            case FeeStructure::TYPE_INDIVIDUAL:
            case FeeStructure::TYPE_CUSTOM:
            default:
                // No specific subject constraint — allow any valid subjects
                $this->validateSubjectsExist($subjectIds);
                break;
        }
    }

    /**
     * Calculate the authoritative price for a package + subject selection.
     *
     * Returns a PricingResult value object. The frontend price is NEVER trusted.
     *
     * @param  FeeStructure $feeStructure  Must be loaded with items and priceTiers
     * @param  int[]        $subjectIds    Subject IDs selected by the student
     * @return PricingResult
     * @throws \RuntimeException if pricing cannot be determined
     */
    public function calculate(FeeStructure $feeStructure, array $subjectIds = []): PricingResult
    {
        // Security: ensure package is available
        $this->assertPackageAvailable($feeStructure);

        $subjects = $subjectIds
            ? Subject::whereIn('id', $subjectIds)->get()
            : collect();

        $packageType = $feeStructure->package_type ?? FeeStructure::TYPE_INDIVIDUAL;

        // Determine the base tuition price based on package type
        $tuitionPrice = match ($packageType) {
            FeeStructure::TYPE_TIERED       => $this->resolveTieredPrice($feeStructure, count($subjectIds)),
            FeeStructure::TYPE_INDIVIDUAL   => $this->resolveIndividualPrice($feeStructure, $subjects),
            default                         => (float) $feeStructure->amount, // fixed_bundle, pick_n, custom
        };

        // Build line items
        $lineItems = $this->buildLineItems($feeStructure, $subjects, $tuitionPrice);

        // Calculate totals
        $subtotal = collect($lineItems)->sum('amount');

        return new PricingResult(
            feeStructureId:  $feeStructure->id,
            packageName:     $feeStructure->name,
            packageType:     $packageType,
            subjectIds:      $subjectIds,
            tuitionPrice:    $tuitionPrice,
            lineItems:       $lineItems,
            subtotal:        $subtotal,
            discountAmount:  0.0,  // Promotions extend here in a future phase
            totalAmount:     $subtotal,
        );
    }

    // ── Tier Resolution ───────────────────────────────────────────────────────

    /**
     * Find the applicable price tier for the given subject count.
     *
     * @throws \RuntimeException if no tier matches
     */
    public function resolveTier(FeeStructure $feeStructure, int $subjectCount): FeeStructurePriceTier
    {
        $feeStructure->loadMissing('priceTiers');

        $tier = $feeStructure->priceTiers
            ->first(fn ($t) => $t->appliesTo($subjectCount));

        if (!$tier) {
            throw new \RuntimeException(
                "No pricing tier defined for {$subjectCount} subject(s) in package \"{$feeStructure->name}\"."
            );
        }

        return $tier;
    }

    /**
     * Validate that price tiers are non-overlapping and cover a contiguous range.
     *
     * @param  FeeStructurePriceTier[] $tiers
     * @throws ValidationException
     */
    public function validateTiers(array $tiers): void
    {
        if (empty($tiers)) return;

        // Sort by minimum
        usort($tiers, fn ($a, $b) => $a['minimum_subjects'] <=> $b['minimum_subjects']);

        $prev = null;
        foreach ($tiers as $tier) {
            $min = (int) ($tier['minimum_subjects'] ?? 0);
            $max = (int) ($tier['maximum_subjects'] ?? 0);

            if ($min > $max) {
                throw ValidationException::withMessages([
                    'tiers' => "Tier minimum ({$min}) cannot exceed maximum ({$max}).",
                ]);
            }

            if ($prev !== null && $min <= $prev['maximum_subjects']) {
                throw ValidationException::withMessages([
                    'tiers' => "Pricing tiers overlap: range {$min}–{$max} overlaps with {$prev['minimum_subjects']}–{$prev['maximum_subjects']}.",
                ]);
            }

            $prev = ['minimum_subjects' => $min, 'maximum_subjects' => $max];
        }
    }

    // ── Private Validation Helpers ────────────────────────────────────────────

    private function assertPackageAvailable(FeeStructure $feeStructure): void
    {
        // Status check
        $isActive = $feeStructure->status === FeeStructure::STATUS_ACTIVE
            || ($feeStructure->status === null && $feeStructure->is_active);

        if (!$isActive) {
            throw new \RuntimeException(
                "Package \"{$feeStructure->name}\" is not active (status: {$feeStructure->status_label})."
            );
        }

        // Effective date check
        if (!$feeStructure->is_currently_valid) {
            throw new \RuntimeException(
                "Package \"{$feeStructure->name}\" is outside its validity period."
            );
        }
    }

    private function validateFixedBundle(FeeStructure $feeStructure, array $subjectIds): void
    {
        $feeStructure->loadMissing('subjects');
        $requiredIds = $feeStructure->subjects->pluck('id')->sort()->values()->toArray();

        if (empty($requiredIds)) {
            // Legacy: no subjects mapped, skip bundle validation
            return;
        }

        $selectedSorted = collect($subjectIds)->sort()->values()->toArray();

        if ($selectedSorted !== $requiredIds) {
            throw ValidationException::withMessages([
                'subjects' => "Fixed bundle \"{$feeStructure->name}\" requires exactly: "
                    . $feeStructure->subjects->pluck('name')->implode(', ') . '.',
            ]);
        }
    }

    private function validatePickN(FeeStructure $feeStructure, array $subjectIds): void
    {
        $min = (int) ($feeStructure->minimum_subjects ?? 1);
        $max = (int) ($feeStructure->maximum_subjects ?? PHP_INT_MAX);
        $count = count($subjectIds);

        if ($count < $min) {
            throw ValidationException::withMessages([
                'subjects' => "Package \"{$feeStructure->name}\" requires at least {$min} subject(s). You selected {$count}.",
            ]);
        }

        if ($count > $max) {
            throw ValidationException::withMessages([
                'subjects' => "Package \"{$feeStructure->name}\" allows at most {$max} subject(s). You selected {$count}.",
            ]);
        }

        // Validate selected subjects are in the allowed pool
        if ($feeStructure->hasSubjects()) {
            $this->assertSubjectsInPool($feeStructure, $subjectIds);
        } else {
            $this->validateSubjectsExist($subjectIds);
        }
    }

    private function validateTiered(FeeStructure $feeStructure, array $subjectIds): void
    {
        // Just ensure subjects are in the allowed pool; price is resolved from tiers
        if ($feeStructure->hasSubjects()) {
            $this->assertSubjectsInPool($feeStructure, $subjectIds);
        } else {
            $this->validateSubjectsExist($subjectIds);
        }
    }

    private function assertSubjectsInPool(FeeStructure $feeStructure, array $subjectIds): void
    {
        $feeStructure->loadMissing('subjects');
        $allowedIds = $feeStructure->subjects->pluck('id')->toArray();

        foreach ($subjectIds as $id) {
            if (!in_array($id, $allowedIds)) {
                $subject = Subject::find($id);
                $subjectLabel = $subject ? $subject->name : '#' . $id;
                throw ValidationException::withMessages([
                    'subjects' => "Subject \"{$subjectLabel}\" is not part of package \"{$feeStructure->name}\".",
                ]);
            }
        }
    }

    private function validateSubjectsExist(array $subjectIds): void
    {
        if (empty($subjectIds)) return;

        $found = Subject::whereIn('id', $subjectIds)->where('is_active', true)->count();
        if ($found !== count($subjectIds)) {
            throw ValidationException::withMessages([
                'subjects' => 'One or more selected subjects are invalid or inactive.',
            ]);
        }
    }

    // ── Price Resolvers ───────────────────────────────────────────────────────

    private function resolveTieredPrice(FeeStructure $feeStructure, int $count): float
    {
        if ($count === 0) return 0.0;
        return (float) $this->resolveTier($feeStructure, $count)->price;
    }

    private function resolveIndividualPrice(FeeStructure $feeStructure, Collection $subjects): float
    {
        if ($subjects->isEmpty()) {
            // Fallback to flat amount
            return (float) ($feeStructure->amount ?? 0);
        }
        return $subjects->sum(fn ($s) => (float) ($s->monthly_fee ?? 0));
    }

    // ── Line Item Builder ─────────────────────────────────────────────────────

    /**
     * Build invoice line item data from a fee structure and subject selection.
     *
     * When fee structure has explicit items (fee_structure_items), use those.
     * Otherwise, fall back to single tuition line item.
     *
     * All values are snapshotted at calculation time for historical immutability.
     *
     * @return array[]  Each element is an array suitable for InvoiceItem::create()
     */
    public function buildLineItems(FeeStructure $feeStructure, Collection $subjects, float $tuitionPrice): array
    {
        $feeStructure->loadMissing(['items', 'subjects']);
        $packageName = $feeStructure->name;

        // If structured billing components are configured, use them
        if ($feeStructure->items->isNotEmpty()) {
            return $this->buildFromItems($feeStructure, $subjects, $packageName);
        }

        // Legacy / simple: single tuition line item
        return [$this->buildTuitionLine($feeStructure, $subjects, $tuitionPrice, $packageName)];
    }

    private function buildFromItems(FeeStructure $feeStructure, Collection $subjects, string $packageName): array
    {
        $lines = [];
        foreach ($feeStructure->items as $item) {
            $lineAmount = round((float) $item->amount * max(1, (int) $item->quantity), 2);

            $lines[] = [
                'subject_id'       => null,
                'description'      => $item->name . ($item->description ? ': ' . $item->description : ''),
                'item_type'        => $item->item_type,
                'package_name'     => $packageName,
                'billing_frequency'=> $item->billing_frequency,
                'unit_price'       => (float) $item->amount,
                'quantity'         => max(1, (int) $item->quantity),
                'amount'           => $lineAmount,
            ];
        }
        return $lines;
    }

    private function buildTuitionLine(FeeStructure $feeStructure, Collection $subjects, float $tuitionPrice, string $packageName): array
    {
        $subjectNames = $subjects->isNotEmpty()
            ? ' (' . $subjects->pluck('name')->implode(', ') . ')'
            : '';

        return [
            'subject_id'       => null,
            'description'      => $packageName . $subjectNames,
            'item_type'        => 'tuition',
            'package_name'     => $packageName,
            'billing_frequency'=> $feeStructure->frequency ?? 'monthly',
            'unit_price'       => $tuitionPrice,
            'quantity'         => 1,
            'amount'           => $tuitionPrice,
        ];
    }
}
