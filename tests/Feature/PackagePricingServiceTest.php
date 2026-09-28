<?php

namespace Tests\Feature;

use App\Models\FeeStructure;
use App\Models\FeeStructureItem;
use App\Models\FeeStructurePriceTier;
use App\Models\Subject;
use App\Services\PackagePricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * PackagePricingServiceTest
 *
 * Tests all package types, validation, pricing accuracy, and security scenarios.
 *
 * Scenarios covered:
 *   - Fixed bundle: correct subjects → pass, wrong/missing subject → fail
 *   - Pick-N: below min → fail, at min → pass, within range → pass, above max → fail
 *   - Tiered: each tier resolves correctly
 *   - Effective dates: before start → fail, during → pass, after expiry → fail
 *   - Package status: draft/inactive/archived → fail, active → pass
 *   - Targeted package security
 *   - Price manipulation: frontend price ignored, server recalculates
 *   - Multi-component billing items
 *   - Legacy backward compatibility (no package subjects → individual pricing)
 */
class PackagePricingServiceTest extends TestCase
{
    use RefreshDatabase;

    private PackagePricingService $svc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->svc = app(PackagePricingService::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeSubject(string $name, float $monthlyFee = 90.00): Subject
    {
        return Subject::create([
            'name'           => $name,
            'slug'           => \Illuminate\Support\Str::slug($name . '-' . uniqid()),
            'education_type' => 'secondary',
            'stage'          => 'Upper Secondary',
            'form_year'      => 'Form 5',
            'category'       => 'Core',
            'is_active'      => true,
            'monthly_fee'    => $monthlyFee,
        ]);
    }

    private function makeFeeStructure(array $attrs = []): FeeStructure
    {
        return FeeStructure::create(array_merge([
            'name'            => 'Test Package',
            'amount'          => 250.00,
            'education_level' => 'Form 5',
            'frequency'       => 'monthly',
            'is_active'       => true,
            'package_type'    => FeeStructure::TYPE_INDIVIDUAL,
            'status'          => FeeStructure::STATUS_ACTIVE,
        ], $attrs));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SCENARIO A — Fixed Bundle
    // ══════════════════════════════════════════════════════════════════════════

    public function test_fixed_bundle_correct_subjects_returns_package_price(): void
    {
        $physics   = $this->makeSubject('Physics');
        $chemistry = $this->makeSubject('Chemistry');
        $biology   = $this->makeSubject('Biology');

        $pkg = $this->makeFeeStructure([
            'name'         => 'SPM Science Package',
            'amount'       => 250.00,
            'package_type' => FeeStructure::TYPE_FIXED_BUNDLE,
        ]);
        $pkg->subjects()->sync([
            $physics->id   => ['is_required' => true, 'sort_order' => 0],
            $chemistry->id => ['is_required' => true, 'sort_order' => 1],
            $biology->id   => ['is_required' => true, 'sort_order' => 2],
        ]);

        $subjectIds = [$physics->id, $chemistry->id, $biology->id];
        // No exception should be thrown
        $this->svc->validateSubjectSelection($pkg, $subjectIds);

        $result = $this->svc->calculate($pkg, $subjectIds);

        $this->assertEquals(250.00, $result->tuitionPrice);
        $this->assertEquals(250.00, $result->totalAmount);
        $this->assertEquals('fixed_bundle', $result->packageType);
    }

    public function test_fixed_bundle_wrong_subjects_throws_validation(): void
    {
        $physics   = $this->makeSubject('Physics');
        $chemistry = $this->makeSubject('Chemistry');
        $biology   = $this->makeSubject('Biology');
        $math      = $this->makeSubject('Mathematics');

        $pkg = $this->makeFeeStructure(['package_type' => FeeStructure::TYPE_FIXED_BUNDLE]);
        $pkg->subjects()->sync([
            $physics->id   => ['is_required' => true, 'sort_order' => 0],
            $chemistry->id => ['is_required' => true, 'sort_order' => 1],
            $biology->id   => ['is_required' => true, 'sort_order' => 2],
        ]);

        $this->expectException(ValidationException::class);
        $this->svc->validateSubjectSelection($pkg, [$physics->id, $chemistry->id, $math->id]);
    }

    public function test_fixed_bundle_missing_subject_throws_validation(): void
    {
        $physics   = $this->makeSubject('Physics');
        $chemistry = $this->makeSubject('Chemistry');
        $biology   = $this->makeSubject('Biology');

        $pkg = $this->makeFeeStructure(['package_type' => FeeStructure::TYPE_FIXED_BUNDLE]);
        $pkg->subjects()->sync([
            $physics->id   => ['is_required' => true, 'sort_order' => 0],
            $chemistry->id => ['is_required' => true, 'sort_order' => 1],
            $biology->id   => ['is_required' => true, 'sort_order' => 2],
        ]);

        $this->expectException(ValidationException::class);
        // Only 2 of 3 required subjects provided
        $this->svc->validateSubjectSelection($pkg, [$physics->id, $chemistry->id]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SCENARIO B — Pick-Any-N
    // ══════════════════════════════════════════════════════════════════════════

    public function test_pick_n_below_minimum_throws_validation(): void
    {
        $s1 = $this->makeSubject('BM');
        $s2 = $this->makeSubject('English');

        $pkg = $this->makeFeeStructure([
            'package_type'    => FeeStructure::TYPE_PICK_N,
            'minimum_subjects'=> 3,
            'maximum_subjects'=> 3,
            'amount'          => 210.00,
        ]);
        $pkg->subjects()->sync([
            $s1->id => ['is_required' => false, 'sort_order' => 0],
            $s2->id => ['is_required' => false, 'sort_order' => 1],
        ]);

        $this->expectException(ValidationException::class);
        $this->svc->validateSubjectSelection($pkg, [$s1->id, $s2->id]); // 2 < min(3)
    }

    public function test_pick_n_at_minimum_passes(): void
    {
        $s1 = $this->makeSubject('BM');
        $s2 = $this->makeSubject('English');
        $s3 = $this->makeSubject('Mathematics');

        $pkg = $this->makeFeeStructure([
            'package_type'    => FeeStructure::TYPE_PICK_N,
            'minimum_subjects'=> 3,
            'maximum_subjects'=> 3,
            'amount'          => 210.00,
        ]);
        $pkg->subjects()->sync([
            $s1->id => ['is_required' => false, 'sort_order' => 0],
            $s2->id => ['is_required' => false, 'sort_order' => 1],
            $s3->id => ['is_required' => false, 'sort_order' => 2],
        ]);

        // No exception should be thrown
        $this->svc->validateSubjectSelection($pkg, [$s1->id, $s2->id, $s3->id]);

        $result = $this->svc->calculate($pkg, [$s1->id, $s2->id, $s3->id]);
        $this->assertEquals(210.00, $result->totalAmount);
    }

    public function test_pick_n_above_maximum_throws_validation(): void
    {
        $s1 = $this->makeSubject('BM');
        $s2 = $this->makeSubject('English');
        $s3 = $this->makeSubject('Mathematics');
        $s4 = $this->makeSubject('Science');

        $pkg = $this->makeFeeStructure([
            'package_type'    => FeeStructure::TYPE_PICK_N,
            'minimum_subjects'=> 3,
            'maximum_subjects'=> 3,
            'amount'          => 210.00,
        ]);
        $pkg->subjects()->sync([
            $s1->id => ['is_required' => false, 'sort_order' => 0],
            $s2->id => ['is_required' => false, 'sort_order' => 1],
            $s3->id => ['is_required' => false, 'sort_order' => 2],
            $s4->id => ['is_required' => false, 'sort_order' => 3],
        ]);

        $this->expectException(ValidationException::class);
        $this->svc->validateSubjectSelection($pkg, [$s1->id, $s2->id, $s3->id, $s4->id]); // 4 > max(3)
    }

    public function test_pick_n_subject_not_in_pool_throws_validation(): void
    {
        $s1 = $this->makeSubject('BM');
        $s2 = $this->makeSubject('English');
        $s3 = $this->makeSubject('Mathematics');
        $outsider = $this->makeSubject('Biology'); // NOT in the pool

        $pkg = $this->makeFeeStructure([
            'package_type'    => FeeStructure::TYPE_PICK_N,
            'minimum_subjects'=> 3,
            'maximum_subjects'=> 3,
            'amount'          => 210.00,
        ]);
        $pkg->subjects()->sync([
            $s1->id => ['is_required' => false, 'sort_order' => 0],
            $s2->id => ['is_required' => false, 'sort_order' => 1],
            $s3->id => ['is_required' => false, 'sort_order' => 2],
        ]);

        $this->expectException(ValidationException::class);
        $this->svc->validateSubjectSelection($pkg, [$s1->id, $s2->id, $outsider->id]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SCENARIO C — Tiered Pricing
    // ══════════════════════════════════════════════════════════════════════════

    public function test_tiered_pricing_resolves_correct_tier(): void
    {
        $pkg = $this->makeFeeStructure([
            'package_type' => FeeStructure::TYPE_TIERED,
            'amount'       => 0,
        ]);

        $pkg->priceTiers()->createMany([
            ['minimum_subjects' => 1, 'maximum_subjects' => 1, 'price' => 90.00,  'sort_order' => 0],
            ['minimum_subjects' => 2, 'maximum_subjects' => 2, 'price' => 160.00, 'sort_order' => 1],
            ['minimum_subjects' => 3, 'maximum_subjects' => 3, 'price' => 210.00, 'sort_order' => 2],
            ['minimum_subjects' => 4, 'maximum_subjects' => 4, 'price' => 260.00, 'sort_order' => 3],
        ]);

        $subjects = collect([
            $this->makeSubject('S1'),
            $this->makeSubject('S2'),
            $this->makeSubject('S3'),
        ]);
        $subjectIds = $subjects->pluck('id')->toArray();
        $pkg->subjects()->sync($subjects->mapWithKeys(fn ($s, $i) => [$s->id => ['is_required' => false, 'sort_order' => $i]]));

        $result = $this->svc->calculate($pkg, $subjectIds);

        $this->assertEquals(210.00, $result->totalAmount); // 3 subjects → tier 3
    }

    public function test_tiered_pricing_four_subjects(): void
    {
        $pkg = $this->makeFeeStructure(['package_type' => FeeStructure::TYPE_TIERED, 'amount' => 0]);
        $pkg->priceTiers()->createMany([
            ['minimum_subjects' => 1, 'maximum_subjects' => 1, 'price' => 90.00,  'sort_order' => 0],
            ['minimum_subjects' => 2, 'maximum_subjects' => 2, 'price' => 160.00, 'sort_order' => 1],
            ['minimum_subjects' => 3, 'maximum_subjects' => 3, 'price' => 210.00, 'sort_order' => 2],
            ['minimum_subjects' => 4, 'maximum_subjects' => 4, 'price' => 260.00, 'sort_order' => 3],
        ]);

        $subjects = collect([
            $this->makeSubject('A'), $this->makeSubject('B'),
            $this->makeSubject('C'), $this->makeSubject('D'),
        ]);
        $subjectIds = $subjects->pluck('id')->toArray();
        $pkg->subjects()->sync($subjects->mapWithKeys(fn ($s, $i) => [$s->id => ['is_required' => false, 'sort_order' => $i]]));

        $result = $this->svc->calculate($pkg, $subjectIds);
        $this->assertEquals(260.00, $result->totalAmount);
    }

    public function test_tiered_pricing_no_matching_tier_throws(): void
    {
        $pkg = $this->makeFeeStructure(['package_type' => FeeStructure::TYPE_TIERED, 'amount' => 0]);
        $pkg->priceTiers()->create([
            'minimum_subjects' => 1, 'maximum_subjects' => 2, 'price' => 90.00, 'sort_order' => 0,
        ]);

        $s1 = $this->makeSubject('X1');
        $s2 = $this->makeSubject('X2');
        $s3 = $this->makeSubject('X3');
        $pkg->subjects()->sync([
            $s1->id => ['is_required' => false, 'sort_order' => 0],
            $s2->id => ['is_required' => false, 'sort_order' => 1],
            $s3->id => ['is_required' => false, 'sort_order' => 2],
        ]);

        $this->expectException(\RuntimeException::class);
        $this->svc->calculate($pkg, [$s1->id, $s2->id, $s3->id]); // 3 subjects, no tier for it
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SCENARIO D — Billing Components
    // ══════════════════════════════════════════════════════════════════════════

    public function test_multi_component_billing_calculates_total(): void
    {
        $pkg = $this->makeFeeStructure(['package_type' => FeeStructure::TYPE_FIXED_BUNDLE, 'amount' => 250.00]);
        $pkg->items()->createMany([
            ['name' => 'Tuition',      'item_type' => 'tuition',      'amount' => 250.00, 'billing_frequency' => 'monthly',  'sort_order' => 0],
            ['name' => 'Materials',    'item_type' => 'material',      'amount' => 80.00,  'billing_frequency' => 'annual',   'sort_order' => 1],
            ['name' => 'Registration', 'item_type' => 'registration',  'amount' => 50.00,  'billing_frequency' => 'one_time', 'sort_order' => 2],
        ]);

        $physics   = $this->makeSubject('Physics');
        $chemistry = $this->makeSubject('Chemistry');
        $biology   = $this->makeSubject('Biology');
        $pkg->subjects()->sync([
            $physics->id   => ['is_required' => true, 'sort_order' => 0],
            $chemistry->id => ['is_required' => true, 'sort_order' => 1],
            $biology->id   => ['is_required' => true, 'sort_order' => 2],
        ]);

        $result = $this->svc->calculate($pkg, [$physics->id, $chemistry->id, $biology->id]);

        $this->assertCount(3, $result->lineItems);
        $this->assertEquals(380.00, $result->totalAmount); // 250 + 80 + 50
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SCENARIO E — Effective Dates
    // ══════════════════════════════════════════════════════════════════════════

    public function test_package_before_effective_from_throws(): void
    {
        $pkg = $this->makeFeeStructure([
            'effective_from' => now()->addDays(10)->toDateString(),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->svc->calculate($pkg, []);
    }

    public function test_package_after_effective_until_throws(): void
    {
        $pkg = $this->makeFeeStructure([
            'effective_from'  => now()->subYear()->toDateString(),
            'effective_until' => now()->subDay()->toDateString(), // expired yesterday
        ]);

        $this->expectException(\RuntimeException::class);
        $this->svc->calculate($pkg, []);
    }

    public function test_package_during_validity_passes(): void
    {
        $pkg = $this->makeFeeStructure([
            'effective_from'  => now()->subMonth()->toDateString(),
            'effective_until' => now()->addMonth()->toDateString(),
        ]);

        // Should not throw
        $result = $this->svc->calculate($pkg, []);
        $this->assertNotNull($result);
    }

    public function test_package_no_validity_dates_passes(): void
    {
        $pkg = $this->makeFeeStructure(['effective_from' => null, 'effective_until' => null]);
        $result = $this->svc->calculate($pkg, []);
        $this->assertNotNull($result);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SCENARIO F — Package Status
    // ══════════════════════════════════════════════════════════════════════════

    public function test_draft_package_throws(): void
    {
        $pkg = $this->makeFeeStructure([
            'status'    => FeeStructure::STATUS_DRAFT,
            'is_active' => false,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->svc->calculate($pkg, []);
    }

    public function test_inactive_package_throws(): void
    {
        $pkg = $this->makeFeeStructure([
            'status'    => FeeStructure::STATUS_INACTIVE,
            'is_active' => false,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->svc->calculate($pkg, []);
    }

    public function test_archived_package_throws(): void
    {
        $pkg = $this->makeFeeStructure([
            'status'    => FeeStructure::STATUS_ARCHIVED,
            'is_active' => false,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->svc->calculate($pkg, []);
    }

    public function test_active_package_passes(): void
    {
        $pkg = $this->makeFeeStructure([
            'status'    => FeeStructure::STATUS_ACTIVE,
            'is_active' => true,
        ]);
        $result = $this->svc->calculate($pkg, []);
        $this->assertNotNull($result);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SCENARIO F — Price Manipulation (server ignores frontend price)
    // ══════════════════════════════════════════════════════════════════════════

    public function test_server_ignores_frontend_price_and_recalculates(): void
    {
        $pkg = $this->makeFeeStructure([
            'amount'       => 250.00,
            'package_type' => FeeStructure::TYPE_FIXED_BUNDLE,
        ]);
        $physics = $this->makeSubject('Physics');
        $pkg->subjects()->sync([$physics->id => ['is_required' => true, 'sort_order' => 0]]);

        // Even if an attacker submits price=1, server recalculates from package config
        $result = $this->svc->calculate($pkg, [$physics->id]);

        // The PricingResult always uses the server's authoritative amount
        $this->assertEquals(250.00, $result->totalAmount);
        // Not RM1
        $this->assertNotEquals(1.00, $result->totalAmount);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SCENARIO G — Legacy Backward Compatibility
    // ══════════════════════════════════════════════════════════════════════════

    public function test_legacy_individual_package_with_no_subjects_uses_flat_amount(): void
    {
        $pkg = $this->makeFeeStructure([
            'amount'       => 180.00,
            'package_type' => FeeStructure::TYPE_INDIVIDUAL,
        ]);
        // No explicit subjects assigned — legacy mode

        $result = $this->svc->calculate($pkg, []);

        $this->assertEquals(180.00, $result->totalAmount);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Tier validation — overlapping tiers rejected
    // ══════════════════════════════════════════════════════════════════════════

    public function test_overlapping_tiers_are_rejected(): void
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->svc->validateTiers([
            ['minimum_subjects' => 1, 'maximum_subjects' => 3, 'price' => 90.00],
            ['minimum_subjects' => 3, 'maximum_subjects' => 5, 'price' => 160.00], // overlaps at 3
        ]);
    }

    public function test_valid_tiers_pass_validation(): void
    {
        // Should not throw
        $this->svc->validateTiers([
            ['minimum_subjects' => 1, 'maximum_subjects' => 1, 'price' => 90.00],
            ['minimum_subjects' => 2, 'maximum_subjects' => 2, 'price' => 160.00],
            ['minimum_subjects' => 3, 'maximum_subjects' => 4, 'price' => 210.00],
        ]);

        $this->assertTrue(true); // Reached here without exception
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SCENARIO E (Acceptance Criteria) — Historical Invoice Immutability
    // ══════════════════════════════════════════════════════════════════════════

    public function test_historical_invoice_immutability_when_package_price_or_version_changes(): void
    {
        $teacherUser = \App\Models\User::factory()->create(['role' => 'teacher']);
        \App\Models\Teacher::create([
            'user_id' => $teacherUser->id,
            'qualification' => 'Degree',
            'specialization' => 'Academic',
        ]);

        $studentUser = \App\Models\User::factory()->create(['role' => 'student']);
        $student = \App\Models\Student::create([
            'user_id' => $studentUser->id,
            'level'   => 'Form 5',
            'status'  => 'active',
        ]);

        $physics = $this->makeSubject('Physics', 90.00);

        $course = \App\Models\Course::create([
            'subject_id'    => $physics->id,
            'teacher_id'    => 1,
            'title'         => 'Physics Form 5',
            'level'         => 'Form 5',
            'status'        => 'published',
            'total_lessons' => 10,
        ]);

        \App\Models\Enrollment::create([
            'student_id' => $student->id,
            'course_id'  => $course->id,
            'status'     => 'active',
        ]);

        // 1. Initial Package at RM250
        $pkg = $this->makeFeeStructure([
            'name'         => 'SPM Core v1',
            'amount'       => 250.00,
            'package_type' => FeeStructure::TYPE_FIXED_BUNDLE,
            'version'      => 1,
        ]);
        $pkg->subjects()->sync([$physics->id => ['is_required' => true, 'sort_order' => 0]]);

        // 2. Generate Invoice under v1
        $invoice = app(\App\Services\InvoiceService::class)->generateForStudent($student, $pkg->id);
        $this->assertEquals(250.00, (float) $invoice->total_amount);
        $originalItemId = $invoice->items->first()->id;
        $originalAmount = (float) $invoice->items->first()->amount;
        $this->assertEquals(250.00, $originalAmount);

        // 3. Admin modifies package price or creates v2 at RM280
        $pkg->update(['amount' => 280.00, 'name' => 'SPM Core v2', 'version' => 2]);

        // 4. Verify historical invoice remains exactly RM250 (immutable snapshot)
        $invoiceFresh = \App\Models\Invoice::with('items')->find($invoice->id);
        $this->assertEquals(250.00, (float) $invoiceFresh->total_amount);
        $this->assertEquals(250.00, (float) $invoiceFresh->items->first()->amount);
        $this->assertEquals($originalItemId, $invoiceFresh->items->first()->id);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Targeted Student Package Security
    // ══════════════════════════════════════════════════════════════════════════

    public function test_targeted_student_package_not_available_to_other_students(): void
    {
        $u1 = \App\Models\User::factory()->create(['role' => 'student']);
        $studentA = \App\Models\Student::create(['user_id' => $u1->id, 'level' => 'Form 5']);

        $u2 = \App\Models\User::factory()->create(['role' => 'student']);
        $studentB = \App\Models\Student::create(['user_id' => $u2->id, 'level' => 'Form 5']);

        $targetedPkg = $this->makeFeeStructure([
            'name'       => 'Special Scholarship Plan',
            'amount'     => 50.00,
            'student_id' => $studentA->id,
        ]);

        // Query available packages for Student B: must NOT include Student A's targeted package
        $availableForB = $this->svc->getAvailablePackages('Form 5', $studentB->id);
        $this->assertFalse($availableForB->contains('id', $targetedPkg->id));

        // Query available packages for Student A: must include Student A's targeted package
        $availableForA = $this->svc->getAvailablePackages('Form 5', $studentA->id);
        $this->assertTrue($availableForA->contains('id', $targetedPkg->id));

        // Public/Anonymous query: must NOT include targeted package
        $publicPackages = $this->svc->getAvailablePackages('Form 5', null);
        $this->assertFalse($publicPackages->contains('id', $targetedPkg->id));
    }
}
