<?php

namespace Tests\Feature;

use App\Enums\EnrollmentStatus;
use App\Enums\InvoiceStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\Setting;
use App\Models\Student;
use App\Models\StudentRenewal;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\InvoiceService;
use App\Services\MonthlyBillingService;
use App\Services\SchoolYear;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Phase 3 step 1 (decision 2026-11-27): monthly invoices for confirmed
 * students, 14-day auto-cancel, pausing unanswered renewals.
 */
class MonthlyBillingTest extends TestCase
{
    use RefreshDatabase;

    private MonthlyBillingService $billing;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Carbon::setTestNow('2027-01-01 07:00:00');
        $this->billing = app(MonthlyBillingService::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function setUpYear(string $replyBy = '2026-12-27'): void
    {
        Setting::set(SchoolYear::KEYS['label'], '2027');
        Setting::set(SchoolYear::KEYS['start'], '2027-01-04');
        Setting::set(SchoolYear::KEYS['end'], '2027-11-30');
        Setting::set(SchoolYear::KEYS['first_billing'], '2027-01-01');
        Setting::set(SchoolYear::KEYS['reply_by'], $replyBy);
    }

    private function student(): Student
    {
        $teacher = Teacher::factory()->create(['application_status' => 'active']);
        $subject = Subject::create(['name' => 'MB Maths', 'slug' => 'mb-maths-'.uniqid(), 'code' => strtoupper(substr(uniqid(), -6)),
            'education_type' => 'secondary', 'form_year' => 'Form 4', 'category' => 'Core', 'monthly_fee' => 69, 'is_active' => true]);
        $course = Course::create(['title' => 'MB Maths', 'teacher_id' => $teacher->id, 'subject_id' => $subject->id, 'level' => 'Form 4', 'price' => 0, 'status' => 'published']);
        $student = Student::factory()->create(['user_id' => User::factory()->create(['role' => 'student'])->id, 'level' => 'Form 4', 'status' => 'active']);
        Enrollment::create(['student_id' => $student->id, 'course_id' => $course->id])->forceFill(['status' => 'active'])->save();

        return $student;
    }

    private function confirmed(Student $student, array $attrs = []): StudentRenewal
    {
        return StudentRenewal::create(array_merge([
            'student_id' => $student->id, 'school_year' => '2027', 'status' => StudentRenewal::CONFIRMED,
            'level' => 'Form 4', 'billing_starts' => '2027-01-01',
        ], $attrs));
    }

    public function test_nothing_happens_until_the_school_year_is_set_up(): void
    {
        $this->confirmed($this->student());

        $run = $this->billing->run(now());

        $this->assertNotNull($run['skipped_reason']);
        $this->assertSame(0, Invoice::count());
    }

    public function test_confirmed_student_gets_one_invoice_per_month(): void
    {
        $this->setUpYear();
        $student = $this->student();
        $this->confirmed($student);

        $first = $this->billing->run(now());
        $again = $this->billing->run(now());

        $this->assertCount(1, $first['issued']);
        $this->assertCount(1, $again['already']);
        $invoice = Invoice::where('student_id', $student->id)->sole();
        $this->assertSame('January 2027', $invoice->billing_period);
        $this->assertEquals(69.00, (float) $invoice->total_amount);
        $this->assertStringContainsString('school year 2027', (string) $invoice->notes);
    }

    public function test_only_confirmed_students_in_their_billing_window_are_billed(): void
    {
        $this->setUpYear();
        $this->confirmed($this->student(), ['status' => StudentRenewal::INVITED]);
        $this->confirmed($this->student(), ['status' => StudentRenewal::DECLINED]);
        $this->confirmed($this->student(), ['billing_starts' => '2027-02-01']);
        $stopped = $this->student();
        $this->confirmed($stopped, ['status' => StudentRenewal::STOPPED, 'stops_after' => '2027-01-01']);

        // January: only the stopped student, who still pays their last month.
        $jan = $this->billing->run(Carbon::parse('2027-01-01'));
        $this->assertCount(1, $jan['issued']);
        $this->assertSame(1, Invoice::where('student_id', $stopped->id)->where('billing_period', 'January 2027')->count());

        // February: only the February starter; the stopped student is done.
        Carbon::setTestNow('2027-02-01 07:00:00');
        $feb = $this->billing->run(now());
        $this->assertCount(1, $feb['issued']);
        $this->assertSame(0, Invoice::where('student_id', $stopped->id)->where('billing_period', 'February 2027')->count());
    }

    public function test_months_outside_the_school_year_and_the_free_trial_are_skipped(): void
    {
        $this->setUpYear();
        $this->confirmed($this->student());

        $this->assertNotNull($this->billing->run(Carbon::parse('2027-12-01'))['skipped_reason']);

        Setting::set(InvoiceService::FREE_TRIAL_UNTIL, '2027-01-05');
        $this->assertStringContainsString('Free trial', (string) $this->billing->run(now())['skipped_reason']);
        $this->assertSame(0, Invoice::count());
    }

    public function test_unpaid_invoice_is_cancelled_after_14_days_and_classes_are_paused(): void
    {
        $this->setUpYear();
        $student = $this->student();
        $this->confirmed($student);
        $old = Invoice::factory()->create(['student_id' => $student->id, 'due_date' => '2026-12-17']); // 15 days ago
        $recent = Invoice::factory()->create(['student_id' => $student->id, 'due_date' => '2026-12-20']); // 12 days ago
        $partPaid = Invoice::factory()->create(['due_date' => '2026-12-01', 'status' => 'partially_paid', 'amount_paid' => 20]);

        $result = $this->billing->autoCancelUnpaid();

        $this->assertSame([$old->invoice_number], $result['cancelled']);
        $this->assertCount(1, $result['needs_admin']);
        $this->assertSame(InvoiceStatus::Cancelled, $old->fresh()->status);
        $this->assertSame(InvoiceStatus::Issued, $recent->fresh()->status);
        $this->assertSame(InvoiceStatus::PartiallyPaid, $partPaid->fresh()->status);

        $this->assertSame(0, $student->activeEnrollments()->count());
        $this->assertSame(EnrollmentStatus::Dropped, $student->enrollments()->first()->status);
        $renewal = $student->renewals()->first();
        $this->assertSame(StudentRenewal::LAPSED, $renewal->status);
        $this->assertStringContainsString($old->invoice_number, (string) $renewal->pause_reason);

        // Paused, not deactivated: the family can still log in and come back.
        $this->assertTrue($student->user->fresh()->isAccountActive());
    }

    public function test_auto_cancel_waits_for_the_school_year_setup(): void
    {
        $invoice = Invoice::factory()->create(['due_date' => '2026-11-01']);

        $this->assertSame([], $this->billing->autoCancelUnpaid()['cancelled']);
        $this->assertSame(InvoiceStatus::Issued, $invoice->fresh()->status);
    }

    public function test_unanswered_invitations_are_paused_after_the_reply_by_date(): void
    {
        $this->setUpYear('2026-12-27');
        $silent = $this->student();
        $this->confirmed($silent, ['status' => StudentRenewal::INVITED]);
        $yes = $this->student();
        $this->confirmed($yes);

        $this->assertSame([], $this->billing->closeUnanswered(Carbon::parse('2026-12-27 18:00')));

        $paused = $this->billing->closeUnanswered(Carbon::parse('2026-12-28 07:00'));

        $this->assertCount(1, $paused);
        $this->assertSame(0, $silent->activeEnrollments()->count());
        $this->assertSame(1, $yes->activeEnrollments()->count());
    }

    public function test_the_daily_command_runs(): void
    {
        $this->setUpYear();
        $this->confirmed($this->student());

        $this->artisan('billing:run', ['--dry-run' => true])
            ->expectsOutputToContain('Monthly invoices January 2027: 1 issued')
            ->assertSuccessful();
        $this->assertSame(0, Invoice::count());

        $this->artisan('billing:run')->assertSuccessful();
        $this->assertSame(1, Invoice::count());
    }
}
