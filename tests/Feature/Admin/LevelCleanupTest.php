<?php

namespace Tests\Feature\Admin;

use App\Enums\InvoiceStatus;
use App\Http\Controllers\Auth\StudentRegistrationController;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\FeeStructure;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * 2026-11-27: registration allowed several levels, and six real students
 * signed up as "Form 4, Form 5" etc. These cases mirror what prod held:
 *   - Form 4 + Form 5 enrolments with a per-subject invoice charged double (#132)
 *   - "Form 3, Form 4" enrolled in Form 1 subjects with no invoice (#141)
 */
class LevelCleanupTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    /** @var array<string, array<string, Course>> level => subject name => course */
    private array $courses = [];

    private const SUBJECTS = ['Bahasa Melayu', 'English', 'Mathematics', 'Sejarah', 'Science'];

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $teacher = Teacher::factory()->create(['application_status' => 'active']);

        foreach (['Form 1' => 59, 'Form 3' => 59, 'Form 4' => 69, 'Form 5' => 69] as $level => $fee) {
            foreach (self::SUBJECTS as $i => $name) {
                $slug = str()->slug("lc {$name} {$level}");
                $subject = Subject::create([
                    'name' => $name, 'slug' => $slug, 'code' => strtoupper(substr(md5($slug), 0, 6)),
                    'education_type' => 'secondary', 'form_year' => $level,
                    'stage' => in_array($level, ['Form 4', 'Form 5']) ? 'Upper Secondary' : 'Lower Secondary',
                    'category' => 'Core', 'monthly_fee' => $fee, 'is_active' => true,
                ]);
                $this->courses[$level][$name] = Course::create([
                    'title' => $name, 'teacher_id' => $teacher->id, 'subject_id' => $subject->id,
                    'level' => 'SPM', 'price' => 0, 'status' => 'published',
                ]);
            }
        }
    }

    private function student(string $level, array $enrolIn): Student
    {
        $user = User::factory()->create(['role' => 'student']);
        $student = Student::factory()->create(['user_id' => $user->id, 'level' => $level]);
        foreach ($enrolIn as $lvl => $names) {
            foreach ($names as $name) {
                Enrollment::create(['student_id' => $student->id, 'course_id' => $this->courses[$lvl][$name]->id])
                    ->forceFill(['status' => 'active'])->save();
            }
        }

        return $student;
    }

    private function perSubjectInvoice(Student $student, float $total, array $overrides = []): Invoice
    {
        $invoice = Invoice::factory()->create(array_merge([
            'student_id' => $student->id, 'fee_structure_id' => null,
            'invoice_number' => 'INV-2026-00002', 'billing_period' => 'November 2026',
            'subtotal' => $total, 'total_amount' => $total,
        ], $overrides));
        InvoiceItem::create(['invoice_id' => $invoice->id, 'description' => 'Tuition', 'unit_price' => $total, 'quantity' => 1, 'amount' => $total]);

        return $invoice;
    }

    public function test_list_shows_only_multi_level_students_with_a_suggestion(): void
    {
        $this->student('Form 4', ['Form 4' => self::SUBJECTS]);
        $mixed = $this->student('Form 2, Form 4', ['Form 4' => self::SUBJECTS]); // like #144

        $this->actingAs($this->admin)->get(route('admin.students.level-cleanup.index'))
            ->assertOk()
            ->assertSee("#{$mixed->id}", false)
            ->assertSee('Form 2, Form 4')
            ->assertSee('Form 4 — suggested')
            ->assertSee('1</strong> student(s) to fix', false);
    }

    public function test_double_charged_form_4_and_5_student_is_reduced_to_form_4_and_reinvoiced(): void
    {
        $student = $this->student('Form 4, Form 5', ['Form 4' => self::SUBJECTS, 'Form 5' => self::SUBJECTS]);
        $old = $this->perSubjectInvoice($student, 690.00);

        $this->actingAs($this->admin)
            ->get(route('admin.students.level-cleanup.preview', ['student' => $student->id, 'level' => 'Form 4']))
            ->assertOk()
            ->assertSee('about RM345.00')
            ->assertSee('− Remove', false);

        $this->actingAs($this->admin)
            ->post(route('admin.students.level-cleanup.apply', $student->id), ['level' => 'Form 4', 'confirm' => '1'])
            ->assertRedirect(route('admin.students.level-cleanup.index'))
            ->assertSessionHas('success');

        $student->refresh();
        $this->assertSame('Form 4', $student->level);
        $years = $student->enrollments()->with('course.subject')->get()->map(fn ($e) => $e->course->subject->form_year)->unique()->values()->all();
        $this->assertSame(['Form 4'], $years);
        $this->assertSame(5, $student->enrollments()->count());

        $this->assertSame(InvoiceStatus::Cancelled, $old->fresh()->status);
        $new = Invoice::where('student_id', $student->id)->where('id', '!=', $old->id)->sole();
        $this->assertSame(InvoiceStatus::Issued, $new->status);
        $this->assertEquals(345.00, (float) $new->total_amount);
        $this->assertSame('November 2026', $new->billing_period);
        $this->assertStringContainsString('INV-2026-00002', (string) $new->notes);

        // Every removed enrolment, the student row and the old invoice were backed up.
        $this->assertSame(5, DB::table('level_cleanup_backups')->where('table_name', 'enrollments')->where('action', 'deleted')->count());
        $this->assertSame(1, DB::table('level_cleanup_backups')->where('table_name', 'students')->count());
        $this->assertSame(1, DB::table('level_cleanup_backups')->where('table_name', 'invoices')->where('action', 'updated')->count());
        $studentBackup = json_decode(DB::table('level_cleanup_backups')->where('table_name', 'students')->value('original'), true);
        $this->assertSame('Form 4, Form 5', $studentBackup['level']);
    }

    public function test_package_invoice_is_reissued_on_the_same_package_price(): void
    {
        // Like #134 / #136: Early Bird package RM229 flat, subjects listed twice.
        $package = FeeStructure::factory()->create(['name' => 'Early Bird Package', 'amount' => 229.00]);
        $student = $this->student('Form 4, Form 5', ['Form 4' => self::SUBJECTS, 'Form 5' => self::SUBJECTS]);
        $old = $this->perSubjectInvoice($student, 229.00, ['fee_structure_id' => $package->id, 'invoice_number' => 'INV-2026-00004']);

        $this->actingAs($this->admin)
            ->get(route('admin.students.level-cleanup.preview', ['student' => $student->id, 'level' => 'Form 5']))
            ->assertSee('about RM229.00');

        $this->actingAs($this->admin)
            ->post(route('admin.students.level-cleanup.apply', $student->id), ['level' => 'Form 5', 'confirm' => '1'])
            ->assertSessionHas('success');

        $new = Invoice::where('student_id', $student->id)->where('id', '!=', $old->id)->sole();
        $this->assertEquals(229.00, (float) $new->total_amount);
        $this->assertSame($package->id, $new->fee_structure_id);
        $this->assertSame(1, $new->items()->count());
        $this->assertSame(5, substr_count((string) $new->items()->value('description'), ',') + 1);
    }

    public function test_student_enrolled_in_the_wrong_form_gets_the_right_subjects_and_no_new_invoice(): void
    {
        // Like #141: "Form 3, Form 4" but enrolled in four Form 1 subjects, no invoice.
        $student = $this->student('Form 3, Form 4', ['Form 1' => ['Bahasa Melayu', 'English', 'Mathematics', 'Sejarah']]);

        $this->actingAs($this->admin)
            ->post(route('admin.students.level-cleanup.apply', $student->id), ['level' => 'Form 3', 'confirm' => '1'])
            ->assertSessionHas('success');

        $student->refresh();
        $this->assertSame('Form 3', $student->level);
        $enrolled = $student->enrollments()->with('course.subject')->get();
        $this->assertSame(4, $enrolled->count());
        $this->assertSame(['Form 3'], $enrolled->map(fn ($e) => $e->course->subject->form_year)->unique()->values()->all());
        $this->assertSame(0, Invoice::where('student_id', $student->id)->count());
    }

    public function test_money_on_an_invoice_blocks_the_cleanup_and_changes_nothing(): void
    {
        $student = $this->student('Form 4, Form 5', ['Form 4' => self::SUBJECTS, 'Form 5' => self::SUBJECTS]);
        $this->perSubjectInvoice($student, 690.00, ['amount_paid' => 100, 'status' => 'partially_paid']);

        $this->actingAs($this->admin)
            ->get(route('admin.students.level-cleanup.preview', ['student' => $student->id, 'level' => 'Form 4']))
            ->assertOk()
            ->assertSee('has money paid on it');

        $this->actingAs($this->admin)
            ->post(route('admin.students.level-cleanup.apply', $student->id), ['level' => 'Form 4', 'confirm' => '1'])
            ->assertSessionHas('error');

        $this->assertSame('Form 4, Form 5', $student->fresh()->level);
        $this->assertSame(10, $student->enrollments()->count());
        $this->assertSame(0, DB::table('level_cleanup_backups')->count());
    }

    public function test_confirm_box_is_required(): void
    {
        $student = $this->student('Form 4, Form 5', ['Form 4' => self::SUBJECTS, 'Form 5' => self::SUBJECTS]);

        $this->actingAs($this->admin)
            ->post(route('admin.students.level-cleanup.apply', $student->id), ['level' => 'Form 4'])
            ->assertSessionHasErrors('confirm');

        $this->assertSame(10, $student->enrollments()->count());
    }

    public function test_non_admins_cannot_use_it(): void
    {
        $student = $this->student('Form 4, Form 5', ['Form 4' => self::SUBJECTS]);
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($teacher)->get(route('admin.students.level-cleanup.index'))->assertForbidden();
        $this->actingAs($teacher)
            ->post(route('admin.students.level-cleanup.apply', $student->id), ['level' => 'Form 4', 'confirm' => '1'])
            ->assertForbidden();
        $this->assertSame('Form 4, Form 5', $student->fresh()->level);
    }

    public function test_dashboard_banner_links_to_the_cleanup(): void
    {
        $this->student('Form 1, Form 2', ['Form 1' => ['English']]);

        $this->actingAs($this->admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Open Level Clean-up');
    }

    public function test_enrolment_never_falls_back_to_another_form(): void
    {
        // Only Form 1 has "Geography": a Form 3 student asking for it must not get Form 1.
        $slug = 'lc-geography-form-1';
        Subject::create(['name' => 'Geography', 'slug' => $slug, 'code' => 'LCGEO1', 'education_type' => 'secondary',
            'form_year' => 'Form 1', 'stage' => 'Lower Secondary', 'is_active' => true, 'monthly_fee' => 59]);
        $student = $this->student('Form 3', []);

        StudentRegistrationController::enrollInSubjects($student, ['Geography', 'English'], 'Form 3');

        $years = $student->enrollments()->with('course.subject')->get()->map(fn ($e) => $e->course->subject->name.'|'.$e->course->subject->form_year)->all();
        $this->assertSame(['English|Form 3'], $years);
    }
}
