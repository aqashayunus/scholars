<?php

namespace Tests\Feature;

use App\Enums\EnrollmentStatus;
use App\Models\Course;
use App\Models\EmailLog;
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
use App\Services\RenewalService;
use App\Services\SchoolYear;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Phase 3 step 2: "Continue in 2027?" from the family's side. Mid-December,
 * during the free trial (5-27 Dec 2026), a Form 4 student moves up to Form 5.
 */
class RenewalFlowTest extends TestCase
{
    use RefreshDatabase;

    private Student $student;

    private User $parent;

    /** @var array<string, array<string, Subject>> */
    private array $subjects = [];

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Carbon::setTestNow('2026-12-10 10:00:00');

        Setting::set(SchoolYear::KEYS['label'], '2027');
        Setting::set(SchoolYear::KEYS['start'], '2027-01-04');
        Setting::set(SchoolYear::KEYS['end'], '2027-11-30');
        Setting::set(SchoolYear::KEYS['first_billing'], '2027-01-01');
        Setting::set(SchoolYear::KEYS['reply_by'], '2026-12-27');
        Setting::set(InvoiceService::FREE_TRIAL_UNTIL, '2026-12-27');
        Setting::set('billing.invoice_due_days', '7');

        $teacher = Teacher::factory()->create(['application_status' => 'active']);
        foreach (['Form 4', 'Form 5'] as $level) {
            foreach (['RF Maths' => 'Core', 'RF Science' => 'Core', 'RF Art' => 'Elective'] as $name => $cat) {
                $slug = str()->slug("{$name} {$level}");
                $s = Subject::create(['name' => $name, 'slug' => $slug, 'code' => strtoupper(substr(md5($slug), 0, 6)),
                    'education_type' => 'secondary', 'form_year' => $level, 'category' => $cat, 'monthly_fee' => 69, 'is_active' => true]);
                Course::create(['title' => $name, 'teacher_id' => $teacher->id, 'subject_id' => $s->id, 'level' => $level, 'price' => 0, 'status' => 'published']);
                $this->subjects[$level][$name] = $s;
            }
        }

        $this->student = Student::factory()->create([
            'user_id' => User::factory()->create(['role' => 'student', 'email' => 'aina@example.test'])->id,
            'level' => 'Form 4', 'status' => 'active',
        ]);
        foreach (['RF Maths', 'RF Science'] as $name) {
            $course = $this->subjects['Form 4'][$name]->courses()->first();
            Enrollment::create(['student_id' => $this->student->id, 'course_id' => $course->id])->forceFill(['status' => 'active'])->save();
        }
        $this->parent = User::factory()->create(['role' => 'parent', 'email' => 'mum@example.test']);
        $this->student->parents()->attach($this->parent->id, ['relationship' => 'Mother', 'is_primary' => true, 'status' => 'active']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function invite(): StudentRenewal
    {
        app(RenewalService::class)->inviteAll(SchoolYear::current());

        return StudentRenewal::where('student_id', $this->student->id)->sole();
    }

    public function test_invitation_goes_to_student_and_parent(): void
    {
        $renewal = $this->invite();

        $this->assertSame(StudentRenewal::INVITED, $renewal->status);
        $logs = EmailLog::where('related_student_id', $this->student->id)->pluck('recipient_email')->all();
        $this->assertEqualsCanonicalizing(['aina@example.test', 'mum@example.test'], $logs);
        $this->assertStringContainsString('Continue with Olympia Scholars in 2027', (string) EmailLog::first()->subject);

        // Inviting again does not duplicate.
        $this->assertSame(0, app(RenewalService::class)->inviteAll(SchoolYear::current()));
    }

    public function test_only_the_family_or_admin_can_open_the_page(): void
    {
        $renewal = $this->invite();

        $this->get(route('renew.show', $renewal->token))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create(['role' => 'parent']))->get(route('renew.show', $renewal->token))->assertForbidden();
        $this->actingAs($this->parent)->get(route('renew.show', $renewal->token))->assertOk()->assertSee('Form 5 (suggested)');
        $this->actingAs($this->student->user)->get(route('renew.show', $renewal->token))->assertOk();
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('renew.show', $renewal->token))->assertOk();
    }

    public function test_confirming_moves_up_a_level_and_issues_the_january_invoice(): void
    {
        $renewal = $this->invite();
        $f5 = $this->subjects['Form 5'];

        $this->actingAs($this->parent)->post(route('renew.confirm', $renewal->token), [
            'level' => 'Form 5',
            'subject_ids' => [$f5['RF Maths']->id, $f5['RF Science']->id],
        ])->assertRedirect(route('renew.show', $renewal->token))->assertSessionHas('success');

        $this->student->refresh();
        $this->assertSame('Form 5', $this->student->level);

        $active = $this->student->activeEnrollments()->with('course.subject')->get();
        $this->assertSame(['Form 5'], $active->map(fn ($e) => $e->course->subject->form_year)->unique()->values()->all());
        $this->assertSame(2, $active->count());
        $this->assertSame(2, $this->student->enrollments()->where('status', EnrollmentStatus::Completed->value)->count());

        $renewal->refresh();
        $this->assertSame(StudentRenewal::CONFIRMED, $renewal->status);
        $this->assertSame('2027-01-01', $renewal->billing_starts->toDateString());
        $this->assertSame($this->parent->id, $renewal->responded_by_user_id);

        // The free trial pauses automatic invoices, but a confirmed renewal is billed.
        $invoice = Invoice::findOrFail($renewal->first_invoice_id);
        $this->assertSame('January 2027', $invoice->billing_period);
        $this->assertSame('2027-01-08', $invoice->due_date->toDateString());
        $this->assertEquals(138.00, (float) $invoice->total_amount);

        // 1 January: the monthly run does not bill January twice.
        Carbon::setTestNow('2027-01-01 07:00:00');
        Setting::set(InvoiceService::FREE_TRIAL_UNTIL, '');
        $run = app(MonthlyBillingService::class)->run(now());
        $this->assertCount(1, $run['already']);
        $this->assertSame(1, Invoice::where('student_id', $this->student->id)->count());

        // 1 February: next month is billed automatically.
        Carbon::setTestNow('2027-02-01 07:00:00');
        app(MonthlyBillingService::class)->run(now());
        $this->assertSame(1, Invoice::where('student_id', $this->student->id)->where('billing_period', 'February 2027')->count());
    }

    public function test_subjects_from_another_level_are_refused(): void
    {
        $renewal = $this->invite();

        $this->actingAs($this->parent)->post(route('renew.confirm', $renewal->token), [
            'level' => 'Form 5',
            'subject_ids' => [$this->subjects['Form 4']['RF Maths']->id],
        ])->assertSessionHasErrors('subject_ids');

        $this->assertSame(StudentRenewal::INVITED, $renewal->fresh()->status);
        $this->assertSame(0, Invoice::count());
    }

    public function test_not_continuing_means_no_invoice_and_classes_pause_after_the_reply_by_date(): void
    {
        $renewal = $this->invite();

        $this->actingAs($this->parent)->post(route('renew.decline', $renewal->token))->assertSessionHas('success');
        $this->assertSame(StudentRenewal::DECLINED, $renewal->fresh()->status);
        $this->assertSame(2, $this->student->activeEnrollments()->count()); // trial continues

        $paused = app(MonthlyBillingService::class)->closeUnanswered(Carbon::parse('2026-12-28 07:00'));
        $this->assertCount(1, $paused);
        $this->assertSame(0, $this->student->activeEnrollments()->count());
        $this->assertSame(StudentRenewal::DECLINED, $renewal->fresh()->status);
        $this->assertSame(0, Invoice::count());
        $this->assertTrue($this->student->user->fresh()->isAccountActive());
    }

    public function test_a_paused_family_can_still_come_back(): void
    {
        $renewal = $this->invite();
        app(MonthlyBillingService::class)->closeUnanswered(Carbon::parse('2026-12-28 07:00'));
        $this->assertSame(StudentRenewal::LAPSED, $renewal->fresh()->status);

        Carbon::setTestNow('2027-01-15 09:00:00');
        Setting::set(InvoiceService::FREE_TRIAL_UNTIL, '');
        $this->actingAs($this->student->user)->post(route('renew.confirm', $renewal->token), [
            'level' => 'Form 5',
            'subject_ids' => [$this->subjects['Form 5']['RF Maths']->id],
        ])->assertSessionHas('success');

        $this->assertSame(1, $this->student->activeEnrollments()->count());
        $invoice = Invoice::where('student_id', $this->student->id)->sole();
        $this->assertSame('January 2027', $invoice->billing_period); // joins mid-January: billed for January
        $this->assertSame('2027-01-22', $invoice->due_date->toDateString());
    }

    public function test_stop_after_this_month(): void
    {
        $renewal = $this->invite();
        $this->actingAs($this->parent)->post(route('renew.confirm', $renewal->token), [
            'level' => 'Form 5', 'subject_ids' => [$this->subjects['Form 5']['RF Maths']->id],
        ]);

        // Stopping in December: January (already invoiced) is the last month.
        $this->actingAs($this->parent)->post(route('renew.stop', $renewal->token))->assertSessionHas('success');
        $renewal->refresh();
        $this->assertSame(StudentRenewal::STOPPED, $renewal->status);
        $this->assertSame('2027-01-01', $renewal->stops_after->toDateString());

        Carbon::setTestNow('2027-02-01 07:00:00');
        Setting::set(InvoiceService::FREE_TRIAL_UNTIL, '');
        $billing = app(MonthlyBillingService::class);
        $this->assertCount(0, $billing->run(now())['issued']);
        $this->assertCount(1, $billing->closeStopped());
        $this->assertSame(0, $this->student->activeEnrollments()->count());
    }
}
