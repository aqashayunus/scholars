<?php

namespace Tests\Feature\Admin;

use App\Models\Course;
use App\Models\EmailLog;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\StudentRenewal;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\SchoolYear;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/** Phase 3 step 3: Admin Dashboard > Renewals. */
class RenewalAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Carbon::setTestNow('2026-12-08 09:00:00');
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function saveYear(array $overrides = []): TestResponse
    {
        return $this->actingAs($this->admin)->post(route('admin.renewals.settings'), array_merge([
            'label' => '2027', 'start' => '2027-01-04', 'end' => '2027-11-30',
            'first_billing' => '2027-01', 'reply_by' => '2026-12-27',
        ], $overrides));
    }

    private function enrolledStudent(string $name): Student
    {
        $teacher = Teacher::factory()->create(['application_status' => 'active']);
        $s = Subject::create(['name' => 'RA '.$name, 'slug' => str()->slug('ra '.$name), 'code' => strtoupper(substr(md5($name), 0, 6)),
            'education_type' => 'secondary', 'form_year' => 'Form 2', 'monthly_fee' => 59, 'is_active' => true]);
        $c = Course::create(['title' => 'RA '.$name, 'teacher_id' => $teacher->id, 'subject_id' => $s->id, 'level' => 'Form 2', 'price' => 0]);
        $student = Student::factory()->create(['user_id' => User::factory()->create(['role' => 'student', 'name' => $name])->id, 'level' => 'Form 2']);
        Enrollment::create(['student_id' => $student->id, 'course_id' => $c->id])->forceFill(['status' => 'active'])->save();

        return $student;
    }

    public function test_tab_and_sidebar_render_before_setup(): void
    {
        $this->actingAs($this->admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('id="tab-renewals"', false)
            ->assertSee('id="nav-renewals"', false)
            ->assertSee('Not set up yet');
    }

    public function test_renewal_emails_are_listed_in_email_settings(): void
    {
        $this->actingAs($this->admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Continue Next School Year: Invitation')
            ->assertSee('Continue Next School Year: Reminder')
            ->assertSee('Open Email Settings');
    }

    public function test_admin_sets_up_the_school_year(): void
    {
        $this->saveYear()->assertRedirect(route('admin.dashboard').'#renewals');

        $year = SchoolYear::current();
        $this->assertSame('2027', $year?->label);
        $this->assertSame('2027-01-01', $year->firstBilling->toDateString());
        $this->assertSame('2026-12-27', $year->replyBy?->toDateString());
    }

    public function test_school_year_must_end_after_it_starts(): void
    {
        $this->saveYear(['end' => '2026-12-01'])->assertSessionHasErrors('end', null, 'renewals');
        $this->assertNull(SchoolYear::current());
    }

    public function test_admin_sends_invitations_and_reminders(): void
    {
        $this->saveYear();
        $a = $this->enrolledStudent('Aina');
        $this->enrolledStudent('Batrisya');

        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertSee('to 2 student(s)');

        $this->actingAs($this->admin)->post(route('admin.renewals.invite'))->assertSessionHasErrors('confirm');
        $this->assertSame(0, StudentRenewal::count());

        $this->actingAs($this->admin)->post(route('admin.renewals.invite'), ['confirm' => '1'])->assertSessionHas('success');
        $this->assertSame(2, StudentRenewal::where('status', StudentRenewal::INVITED)->count());
        $this->assertSame(2, EmailLog::where('category', 'renewal')->count());

        $this->actingAs($this->admin)->post(route('admin.renewals.remind'))->assertSessionHas('success');
        $this->assertSame(4, EmailLog::where('category', 'renewal')->count());
        $this->assertNotNull(StudentRenewal::where('student_id', $a->id)->value('reminded_at'));

        $this->actingAs($this->admin)->get(route('admin.dashboard'))
            ->assertSee('2 family(ies) have not replied')
            ->assertSee('Confirm for family');
    }

    public function test_admin_can_stop_a_continuing_student(): void
    {
        $this->saveYear();
        $student = $this->enrolledStudent('Aina');
        $r = StudentRenewal::create(['student_id' => $student->id, 'school_year' => '2027', 'status' => StudentRenewal::CONFIRMED, 'billing_starts' => '2027-01-01']);

        $this->actingAs($this->admin)->post(route('admin.renewals.stop', $r->id))->assertSessionHas('success');

        $r->refresh();
        $this->assertSame(StudentRenewal::STOPPED, $r->status);
        $this->assertSame('2027-01-01', $r->stops_after?->toDateString());
    }

    public function test_non_admins_cannot_manage_renewals(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($teacher)->post(route('admin.renewals.settings'), ['label' => '2027'])->assertForbidden();
        $this->actingAs($teacher)->post(route('admin.renewals.invite'), ['confirm' => '1'])->assertForbidden();
        $this->assertNull(SchoolYear::current());
    }
}
