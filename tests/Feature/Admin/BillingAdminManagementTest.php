<?php

namespace Tests\Feature\Admin;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\FeeStructure;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentInvoice;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingAdminManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    // ── Fee Structure CRUD ───────────────────────────────────────────────────────

    public function test_admin_can_create_a_fee_structure(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.fee-structures.store'), [
            'name'            => 'Standard Tuition',
            'amount'          => 250,
            'education_level' => 'SPM',
            'frequency'       => 'monthly',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('fee_structures', ['name' => 'Standard Tuition', 'amount' => 250]);
        $this->assertDatabaseHas('financial_audit_logs', ['action' => 'package_created']);
    }

    public function test_admin_can_update_a_fee_structure(): void
    {
        $fee = FeeStructure::create(['name' => 'Old', 'amount' => 100, 'education_level' => 'SPM', 'frequency' => 'monthly', 'is_active' => true]);

        $response = $this->actingAs($this->admin)->put(route('admin.fee-structures.update', $fee->id), [
            'name'            => 'New Name',
            'amount'          => 150,
            'education_level' => 'SPM',
            'frequency'       => 'monthly',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('fee_structures', ['id' => $fee->id, 'name' => 'New Name', 'amount' => 150]);
        $this->assertDatabaseHas('financial_audit_logs', ['action' => 'package_updated', 'target_id' => $fee->id]);
    }

    public function test_admin_can_delete_a_fee_structure(): void
    {
        $fee = FeeStructure::create(['name' => 'To Remove', 'amount' => 100, 'education_level' => 'SPM', 'frequency' => 'monthly', 'is_active' => true]);

        $response = $this->actingAs($this->admin)->delete(route('admin.fee-structures.destroy', $fee->id));

        $response->assertRedirect();
        $this->assertSoftDeleted('fee_structures', ['id' => $fee->id]);
    }

    // ── Package Engine ───────────────────────────────────────────────────────────

    public function test_admin_can_sync_fee_structure_subjects(): void
    {
        $fee = FeeStructure::create(['name' => 'Bundle', 'amount' => 300, 'education_level' => 'SPM', 'frequency' => 'monthly', 'is_active' => true]);
        $subject = Subject::factory()->create();

        $response = $this->actingAs($this->admin)->putJson(route('admin.fee-structures.subjects.update', $fee->id), [
            'subjects' => [
                ['subject_id' => $subject->id, 'is_required' => true, 'sort_order' => 0],
            ],
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('fee_structure_subjects', ['fee_structure_id' => $fee->id, 'subject_id' => $subject->id]);
    }

    public function test_admin_can_sync_fee_structure_items(): void
    {
        $fee = FeeStructure::create(['name' => 'Bundle', 'amount' => 300, 'education_level' => 'SPM', 'frequency' => 'monthly', 'is_active' => true]);

        $response = $this->actingAs($this->admin)->putJson(route('admin.fee-structures.items.update', $fee->id), [
            'items' => [
                ['name' => 'Registration Fee', 'item_type' => 'registration', 'amount' => 50, 'billing_frequency' => 'one_time'],
            ],
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('fee_structure_items', ['fee_structure_id' => $fee->id, 'name' => 'Registration Fee']);
    }

    public function test_admin_can_sync_fee_structure_tiers(): void
    {
        $fee = FeeStructure::create(['name' => 'Tiered', 'amount' => 0, 'education_level' => 'SPM', 'frequency' => 'monthly', 'is_active' => true]);

        $response = $this->actingAs($this->admin)->putJson(route('admin.fee-structures.tiers.update', $fee->id), [
            'tiers' => [
                ['minimum_subjects' => 1, 'maximum_subjects' => 2, 'price' => 200],
                ['minimum_subjects' => 3, 'maximum_subjects' => 5, 'price' => 350],
            ],
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('fee_structure_price_tiers', ['fee_structure_id' => $fee->id, 'price' => 200]);
    }

    public function test_admin_can_duplicate_a_fee_structure(): void
    {
        $fee = FeeStructure::create(['name' => 'Original', 'amount' => 100, 'education_level' => 'SPM', 'frequency' => 'monthly', 'is_active' => true]);

        $response = $this->actingAs($this->admin)->post(route('admin.fee-structures.duplicate', $fee->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('fee_structures', ['name' => 'Copy of Original', 'status' => FeeStructure::STATUS_DRAFT]);
    }

    public function test_admin_can_create_a_new_fee_structure_version(): void
    {
        $fee = FeeStructure::create(['name' => 'Original', 'amount' => 100, 'education_level' => 'SPM', 'frequency' => 'monthly', 'is_active' => true, 'version' => 1]);

        $response = $this->actingAs($this->admin)->post(route('admin.fee-structures.version', $fee->id), [
            'amount' => 120,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('fee_structures', ['id' => $fee->id, 'status' => FeeStructure::STATUS_INACTIVE]);
        $this->assertDatabaseHas('fee_structures', ['name' => 'Original v2', 'version' => 2, 'amount' => 120]);
    }

    public function test_api_fee_structure_subjects_returns_package_and_subjects(): void
    {
        $fee = FeeStructure::create(['name' => 'Bundle', 'amount' => 300, 'education_level' => 'SPM', 'frequency' => 'monthly', 'is_active' => true, 'package_type' => 'fixed_bundle']);
        $subject = Subject::factory()->create();
        $fee->subjects()->attach($subject->id, ['is_required' => true, 'sort_order' => 0]);

        $response = $this->actingAs($this->admin)->getJson(route('admin.api.fee-structures.subjects', $fee->id));

        $response->assertOk()->assertJsonPath('package_type', 'fixed_bundle')->assertJsonCount(1, 'subjects');
    }

    public function test_api_fee_structure_preview_rejects_unavailable_package(): void
    {
        $fee = FeeStructure::create(['name' => 'Draft', 'amount' => 100, 'education_level' => 'SPM', 'frequency' => 'monthly', 'is_active' => false, 'status' => FeeStructure::STATUS_DRAFT]);

        $response = $this->actingAs($this->admin)->getJson(route('admin.api.fee-structures.preview', $fee->id));

        $response->assertStatus(422)->assertJson(['error' => 'Package is not available.']);
    }

    public function test_api_fee_structure_preview_returns_pricing_for_available_individual_package(): void
    {
        $fee = FeeStructure::create(['name' => 'Individual', 'amount' => 200, 'education_level' => 'SPM', 'frequency' => 'monthly', 'is_active' => true, 'package_type' => 'individual']);

        $response = $this->actingAs($this->admin)->getJson(route('admin.api.fee-structures.preview', $fee->id));

        $response->assertOk();
    }

    // ── Invoice CRUD ─────────────────────────────────────────────────────────────

    public function test_admin_can_update_an_invoice(): void
    {
        $student = Student::factory()->create();
        $invoice = Invoice::create([
            'student_id' => $student->id, 'invoice_number' => 'INV-TEST-1', 'title' => 'Old Title',
            'subtotal' => 100, 'total_amount' => 100, 'due_date' => now()->addDays(7), 'status' => InvoiceStatus::Issued,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.invoices.admin-update', $invoice->id), [
            'title'    => 'New Title',
            'due_date' => now()->addDays(7)->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'title' => 'New Title']);
    }

    public function test_updating_invoice_due_date_reconciles_overdue_status(): void
    {
        $student = Student::factory()->create();
        $invoice = Invoice::create([
            'student_id' => $student->id, 'invoice_number' => 'INV-TEST-2', 'title' => 'Invoice',
            'subtotal' => 100, 'total_amount' => 100, 'due_date' => now()->subDays(3), 'status' => InvoiceStatus::Issued,
        ]);

        $response = $this->actingAs($this->admin)->putJson(route('admin.invoices.due-date.update', $invoice->id), [
            'due_date' => now()->subDays(10)->toDateString(),
            'reason'   => 'Testing overdue reconciliation',
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $invoice->refresh();
        $this->assertSame(InvoiceStatus::Overdue, $invoice->status);
        $this->assertDatabaseHas('financial_audit_logs', ['action' => 'update_invoice_due_date', 'target_id' => $invoice->id]);
    }

    public function test_admin_can_update_default_due_days(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.billing.default-due-days.update'), [
            'invoice_due_days' => 14,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('settings', ['key' => 'billing.invoice_due_days', 'value' => '14']);
    }

    public function test_admin_can_delete_an_invoice_and_reset_linked_pending_payment(): void
    {
        $student = Student::factory()->create();
        $invoice = Invoice::create([
            'student_id' => $student->id, 'invoice_number' => 'INV-TEST-3', 'title' => 'Invoice',
            'subtotal' => 100, 'total_amount' => 100, 'due_date' => now()->addDays(7), 'status' => InvoiceStatus::Issued,
        ]);
        $payment = Payment::create(['user_id' => $student->user_id, 'description' => 'Tuition', 'amount' => 100, 'status' => PaymentStatus::Pending]);
        PaymentInvoice::create(['payment_id' => $payment->id, 'invoice_id' => $invoice->id, 'amount_applied' => 100]);

        $response = $this->actingAs($this->admin)->delete(route('admin.invoices.destroy', $invoice->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
        $payment->refresh();
        $this->assertSame(PaymentStatus::Failed, $payment->status);
    }

    // ── Finance Settings ─────────────────────────────────────────────────────────

    public function test_admin_can_update_finance_settings(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.settings.finance'), [
            'company_bank_name'           => 'Test Bank',
            'company_bank_account_name'   => 'Olympia Scholars Sdn Bhd',
            'company_bank_account_number' => '1234567890',
            'gateway_provider'            => 'mock_fpx',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('settings', ['key' => 'company_bank_name', 'value' => 'Test Bank']);
    }

    public function test_admin_can_reveal_gateway_credentials(): void
    {
        $response = $this->actingAs($this->admin)->postJson(route('admin.settings.finance.reveal'));

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('financial_audit_logs', ['action' => 'view_credentials']);
    }

    // ── CSV Exports ──────────────────────────────────────────────────────────────

    public function test_admin_can_export_payments_csv(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.export.payments'));

        $response->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_can_export_enrollments_csv(): void
    {
        $course = Course::factory()->create();
        $student = Student::factory()->create();
        Enrollment::create(['student_id' => $student->id, 'course_id' => $course->id, 'status' => 'active']);

        $response = $this->actingAs($this->admin)->get(route('admin.export.enrollments'));

        $response->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
