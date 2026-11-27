<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Phase 3 — real-render smoke coverage for every billing view touched by the
 * Invoice/Payment enum conversion. Grep sweeps repeatedly missed live crash
 * sites this pass (bare Blade echoes, match() on raw strings, ucfirst() on
 * an enum instance all throw once status is cast to a backed enum) — this
 * empirically renders every page with real invoices/payments across every
 * status value, rather than trusting the sweep alone.
 */
class BillingEnumRenderSmokeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $parent;

    private User $studentUser;

    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['name' => 'Admin', 'email' => 'admin@render.test', 'role' => 'admin', 'password' => Hash::make('password')]);
        $this->parent = User::factory()->create(['name' => 'Parent', 'email' => 'parent@render.test', 'role' => 'parent', 'password' => Hash::make('password')]);
        $this->studentUser = User::factory()->create(['name' => 'Student', 'email' => 'student@render.test', 'role' => 'student', 'password' => Hash::make('password')]);
        $this->student = Student::factory()->create(['user_id' => $this->studentUser->id, 'parent_user_id' => $this->parent->id, 'level' => 'SPM']);
        // authorizeInvoiceAccess() (Billing\InvoiceController) checks only the
        // pivot-based authorizedChildren(), not the legacy parent_user_id
        // column — both must be set for the parent fixture to be authorized.
        DB::table('parent_student')->insert([
            'parent_user_id' => $this->parent->id,
            'student_id' => $this->student->id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // One invoice per status so every match()/badge branch actually renders.
        foreach (InvoiceStatus::cases() as $i => $status) {
            Invoice::factory()->create([
                'student_id' => $this->student->id,
                'invoice_number' => 'INV-RENDER-'.$i,
                'title' => 'Render Test Invoice '.$status->value,
                'billing_period' => 'September 2026',
                'subtotal' => 100,
                'total_amount' => 100,
                'amount_paid' => $status === InvoiceStatus::Paid ? 100 : 0,
                'issued_date' => now()->toDateString(),
                'due_date' => now()->addDays(7)->toDateString(),
                'status' => $status,
            ]);
        }

        // One payment per status so every match()/badge branch actually renders.
        foreach (PaymentStatus::cases() as $i => $status) {
            Payment::create([
                'user_id' => $this->parent->id,
                'student_id' => $this->student->id,
                'payer_id' => $this->parent->id,
                'payer_type' => 'parent',
                'description' => 'Render Test Payment '.$status->value,
                'amount' => 50,
                'payment_method' => 'bank_transfer',
                'reference_no' => 'REF-RENDER-'.$i,
                'status' => $status,
                'paid_at' => now(),
            ]);
        }
    }

    public function test_admin_dashboard_billing_section_renders(): void
    {
        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_retired_billing_dashboard_redirects_to_financials(): void
    {
        $this->actingAs($this->admin)->get(route('admin.billing.dashboard'))
            ->assertRedirect(route('admin.dashboard').'#financials');
    }

    // Note: admin.billing.invoices / .show are deprecated redirect-only
    // routes — they send the admin to admin/dashboard#invoices, which is
    // where the real invoice management UI now lives (already covered by
    // test_admin_dashboard_billing_section_renders above). Pre-existing app
    // behavior, not part of this enum conversion — nothing to smoke-test here.

    public function test_retired_payments_pages_redirect_to_financials(): void
    {
        $payment = Payment::factory()->create();

        $this->actingAs($this->admin)->get(route('admin.billing.payments'))
            ->assertRedirect(route('admin.dashboard').'#financials');
        $this->actingAs($this->admin)->get(route('admin.billing.payment.show', $payment))
            ->assertRedirect(route('admin.dashboard').'#financials');
    }

    public function test_parent_fees_page_renders(): void
    {
        $this->actingAs($this->parent)->get(route('parent.fees'))->assertOk();
    }

    public function test_parent_fees_show_renders_for_every_status(): void
    {
        foreach (Invoice::all() as $invoice) {
            $this->actingAs($this->parent)
                ->get(route('parent.fees.show', $invoice))
                ->assertOk();
        }
    }

    public function test_parent_payments_page_renders(): void
    {
        $this->actingAs($this->parent)->get(route('parent.payments'))->assertOk();
    }

    public function test_student_fees_page_renders(): void
    {
        $this->actingAs($this->studentUser)->get(route('student.fees'))->assertOk();
    }

    public function test_student_fees_show_renders_for_every_status(): void
    {
        foreach (Invoice::all() as $invoice) {
            $this->actingAs($this->studentUser)
                ->get(route('student.fees.show', $invoice))
                ->assertOk();
        }
    }
}
