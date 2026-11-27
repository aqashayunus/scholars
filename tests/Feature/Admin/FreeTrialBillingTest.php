<?php

namespace Tests\Feature\Admin;

use App\Enums\InvoiceStatus;
use App\Http\Controllers\Auth\StudentRegistrationController;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\InvoiceReminder;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * 2026-11-27: free trial classes on weekends 5–27 Dec 2026. During a free
 * trial no invoice is issued automatically, and admin can cancel the unpaid
 * invoices already issued (due 3 Dec, which would restrict students from 4 Dec).
 */
class FreeTrialBillingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function enrolledStudent(): Student
    {
        $teacher = Teacher::factory()->create(['application_status' => 'active']);
        $subject = Subject::create(['name' => 'Trial Maths', 'slug' => 'trial-maths-f4', 'code' => 'TRM4', 'education_type' => 'secondary',
            'form_year' => 'Form 4', 'category' => 'Core', 'monthly_fee' => 69, 'is_active' => true]);
        Course::create(['title' => 'Trial Maths', 'teacher_id' => $teacher->id, 'subject_id' => $subject->id, 'level' => 'Form 4', 'price' => 0, 'status' => 'published']);
        $student = Student::factory()->create(['user_id' => User::factory()->create(['role' => 'student'])->id, 'level' => 'Form 4']);

        StudentRegistrationController::enrollInSubjects($student, ['Trial Maths'], 'Form 4');

        return $student;
    }

    public function test_no_invoice_is_issued_on_enrolment_during_a_free_trial(): void
    {
        Setting::set(InvoiceService::FREE_TRIAL_UNTIL, now()->addDays(10)->toDateString());

        $student = $this->enrolledStudent();

        $this->assertSame(1, $student->enrollments()->count());
        $this->assertSame(0, Invoice::where('student_id', $student->id)->count());
    }

    public function test_invoices_are_issued_again_once_the_trial_has_ended(): void
    {
        Setting::set(InvoiceService::FREE_TRIAL_UNTIL, now()->subDay()->toDateString());

        $this->assertFalse(InvoiceService::autoInvoicingPaused());
        $student = $this->enrolledStudent();
        $this->assertSame(1, Invoice::where('student_id', $student->id)->count());
    }

    public function test_last_day_of_the_trial_still_counts(): void
    {
        Setting::set(InvoiceService::FREE_TRIAL_UNTIL, now()->toDateString());

        $this->assertTrue(InvoiceService::autoInvoicingPaused());
    }

    public function test_admin_turns_the_free_trial_on_and_off(): void
    {
        $until = now()->addDays(30)->toDateString();

        $this->actingAs($this->admin)->post(route('admin.billing.free-trial.update'), ['free_trial_until' => $until])
            ->assertRedirect(route('admin.dashboard').'#financials');
        $this->assertSame($until, InvoiceService::freeTrialUntil()?->toDateString());

        $this->actingAs($this->admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Free trial on until');

        $this->actingAs($this->admin)->post(route('admin.billing.free-trial.update'), ['turn_off' => '1']);
        $this->assertNull(InvoiceService::freeTrialUntil());
    }

    public function test_free_trial_date_cannot_be_in_the_past(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.billing.free-trial.update'), ['free_trial_until' => now()->subDay()->toDateString()])
            ->assertSessionHasErrors('free_trial_until');
        $this->assertNull(InvoiceService::freeTrialUntil());
    }

    public function test_bulk_cancel_cancels_only_untouched_unpaid_invoices(): void
    {
        $open = Invoice::factory()->create(['invoice_number' => 'INV-OPEN-1']);
        InvoiceReminder::create(['invoice_id' => $open->id, 'student_id' => $open->student_id, 'reminder_type' => 'due_today',
            'scheduled_at' => now()->addDays(3), 'status' => 'pending', 'recipient_email' => 'x@example.test', 'recipient_type' => 'student']);
        $partPaid = Invoice::factory()->create(['invoice_number' => 'INV-PART-1', 'status' => 'partially_paid', 'amount_paid' => 50]);
        $withSlip = Invoice::factory()->create(['invoice_number' => 'INV-SLIP-1']);
        Payment::factory()->create(['invoice_id' => $withSlip->id, 'status' => 'pending']);

        // The modal only offers the untouched one.
        $this->actingAs($this->admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('1 unpaid invoice(s) with nothing paid on them');

        // Even if someone posts all three, only the untouched one is cancelled.
        $this->actingAs($this->admin)->post(route('admin.billing.invoices.cancel-unpaid'), [
            'invoice_ids' => [$open->id, $partPaid->id, $withSlip->id],
            'reason' => 'Free trial',
            'confirm' => '1',
        ])->assertRedirect(route('admin.dashboard').'#financials');

        $this->assertSame(InvoiceStatus::Cancelled, $open->fresh()->status);
        $this->assertStringContainsString('Free trial', (string) $open->fresh()->notes);
        $this->assertSame('skipped', InvoiceReminder::where('invoice_id', $open->id)->value('status'));
        $this->assertSame(InvoiceStatus::PartiallyPaid, $partPaid->fresh()->status);
        $this->assertSame(InvoiceStatus::Issued, $withSlip->fresh()->status);
        $this->assertDatabaseHas('financial_audit_logs', ['action' => 'invoice_cancelled', 'target_id' => $open->id]);
    }

    public function test_bulk_cancel_needs_confirmation(): void
    {
        $open = Invoice::factory()->create();

        $this->actingAs($this->admin)->post(route('admin.billing.invoices.cancel-unpaid'), [
            'invoice_ids' => [$open->id], 'reason' => 'Free trial',
        ])->assertSessionHasErrors('confirm');

        $this->assertSame(InvoiceStatus::Issued, $open->fresh()->status);
    }

    public function test_non_admins_cannot_use_the_billing_tools(): void
    {
        $open = Invoice::factory()->create();
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($teacher)->post(route('admin.billing.invoices.cancel-unpaid'), [
            'invoice_ids' => [$open->id], 'reason' => 'x', 'confirm' => '1',
        ])->assertForbidden();
        $this->actingAs($teacher)->post(route('admin.billing.free-trial.update'), ['turn_off' => '1'])->assertForbidden();

        $this->assertSame(InvoiceStatus::Issued, $open->fresh()->status);
    }

    public function test_payment_verification_lists_only_invoices_with_a_pending_payment(): void
    {
        $waiting = Invoice::factory()->create(['invoice_number' => 'INV-WAIT-01']);
        Payment::factory()->create(['invoice_id' => $waiting->id, 'status' => 'pending']);
        $viaPivot = Invoice::factory()->create(['invoice_number' => 'INV-PIVOT-01']);
        $p = Payment::factory()->create(['status' => 'pending']);
        $p->invoices()->attach($viaPivot->id, ['amount_applied' => 10]);
        Invoice::factory()->create(['invoice_number' => 'INV-NOPAY-01']);

        $html = $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk()->getContent();
        $table = substr($html, (int) strpos($html, 'id="verificationsTable"'));
        $table = substr($table, 0, (int) strpos($table, '</table>'));

        $this->assertStringContainsString('INV-WAIT-01', $table);
        $this->assertStringContainsString('INV-PIVOT-01', $table);
        $this->assertStringNotContainsString('INV-NOPAY-01', $table);
    }

    public function test_level_cleanup_does_not_reissue_during_a_free_trial(): void
    {
        Setting::set(InvoiceService::FREE_TRIAL_UNTIL, now()->addDays(10)->toDateString());
        $teacher = Teacher::factory()->create(['application_status' => 'active']);
        $student = Student::factory()->create(['user_id' => User::factory()->create(['role' => 'student'])->id, 'level' => 'Form 4, Form 5']);
        foreach (['Form 4', 'Form 5'] as $lvl) {
            $s = Subject::create(['name' => 'Trial English', 'slug' => str()->slug("trial english {$lvl}"), 'code' => 'TE'.substr($lvl, -1),
                'education_type' => 'secondary', 'form_year' => $lvl, 'category' => 'Core', 'monthly_fee' => 69, 'is_active' => true]);
            $c = Course::create(['title' => 'Trial English', 'teacher_id' => $teacher->id, 'subject_id' => $s->id, 'level' => 'SPM', 'price' => 0, 'status' => 'published']);
            Enrollment::create(['student_id' => $student->id, 'course_id' => $c->id])->forceFill(['status' => 'active'])->save();
        }
        $old = Invoice::factory()->create(['student_id' => $student->id, 'total_amount' => 138, 'subtotal' => 138]);

        $this->actingAs($this->admin)
            ->get(route('admin.students.level-cleanup.preview', ['student' => $student->id, 'level' => 'Form 4']))
            ->assertSee('no new invoice');

        $this->actingAs($this->admin)
            ->post(route('admin.students.level-cleanup.apply', $student->id), ['level' => 'Form 4', 'confirm' => '1'])
            ->assertSessionHas('success');

        $this->assertSame(InvoiceStatus::Cancelled, $old->fresh()->status);
        $this->assertSame(1, Invoice::where('student_id', $student->id)->count());
        $this->assertSame('Form 4', $student->fresh()->level);
    }
}
