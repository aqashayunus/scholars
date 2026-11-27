<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Models\Course;
use App\Models\FeeStructure;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeeStructureRegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test registration page renders successfully with dynamic fee structures.
     */
    public function test_student_registration_page_loads_with_fee_structures(): void
    {
        $response = $this->get(route('register.student'));
        $response->assertStatus(200);
        $response->assertSee('Tuition Package &amp; Subjects', false);
        $response->assertSee('Choose Enrollment Plan');
    }

    /**
     * Test registration with dynamic fee structure creates linked invoice with bundle calculation.
     */
    public function test_student_registration_with_package_creates_invoice(): void
    {
        $this->withoutExceptionHandling();
        // 1. Create teacher for course foreign key constraint
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'qualification' => 'Degree in Education',
            'specialization' => 'Academic',
            'rating' => 5.0,
            'experience_years' => 5,
        ]);

        // 2. Create or get dynamic fee structure
        $fee = FeeStructure::firstOrCreate(
            ['name' => 'SPM Core Monthly Tuition'],
            [
                'description' => 'Monthly standard tuition covering core subjects.',
                'amount' => 250.00,
                'education_level' => 'Form 4–5',
                'frequency' => 'monthly',
                'is_active' => true,
            ]
        );

        // 2. Ensure subjects exist
        $bm = Subject::firstOrCreate(
            ['name' => 'Bahasa Melayu'],
            [
                'education_type' => 'secondary',
                'form_year' => 'Form 5',
                'category' => 'Core',
                'monthly_fee' => 80.00,
                'is_active' => true,
            ]
        );

        $math = Subject::firstOrCreate(
            ['name' => 'Mathematics'],
            [
                'education_type' => 'secondary',
                'form_year' => 'Form 5',
                'category' => 'Core',
                'monthly_fee' => 80.00,
                'is_active' => true,
            ]
        );

        $icNumber = '080512-14-5566';

        $payload = [
            'name' => 'Aiman Bundler',
            'ic_number' => $icNumber,
            'email' => 'aiman_bundle_test@example.com',
            'phone' => '+60 12-888 9999',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'nationality' => 'Malaysian',
            'state' => 'Selangor',
            'programme' => 'secondary',
            'level' => 'Form 5',
            'school_name' => 'SMK Taman Melawati',
            'fee_structure_id' => $fee->id,
            'subjects' => ['Bahasa Melayu', 'Mathematics'],
        ];

        $response = $this->post(route('register.student.submit'), $payload);
        if (session('errors')) {
            dump(session('errors')->all());
        }
        $user = User::where('email', 'aiman_bundle_test@example.com')->first();
        $this->assertNotNull($user);

        $student = Student::where('user_id', $user->id)->first();
        $this->assertNotNull($student);

        // Verify invoice was created and linked to fee structure
        $invoice = Invoice::where('student_id', $student->id)->latest('id')->first();
        $this->assertNotNull($invoice);
        $this->assertEquals($fee->id, $invoice->fee_structure_id);
        $this->assertEquals(InvoiceStatus::Issued, $invoice->status);
        $this->assertGreaterThan(0, $invoice->total_amount);

        // Verify redirect to success page
        $response->assertRedirect(route('register.student.success', ['id' => $student->id]));

        // Verify success page shows invoice and FPX payment option
        $successResponse = $this->actingAs($user)->get(route('register.student.success', ['id' => $student->id]));
        $successResponse->assertStatus(200);
        $successResponse->assertSee($invoice->invoice_number);
        $successResponse->assertSee('Pay Tuition Now via FPX');

        // Cleanup test data
        $invoice->items()->delete();
        $invoice->delete();
        $student->enrollments()->delete();
        $student->delete();
        $user->delete();
    }

    /**
     * Registering for several levels at once (Form 4 & Form 5) is refused:
     * one level per registration, renew for the next year.
     */
    public function test_student_registration_with_multi_year_selection_is_refused(): void
    {
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        Teacher::firstOrCreate(['user_id' => $teacherUser->id], [
            'qualification' => 'Degree in Education',
            'specialization' => 'Academic',
            'rating' => 5.0,
            'experience_years' => 5,
        ]);

        $bmF4 = Subject::firstOrCreate(
            ['name' => 'Bahasa Melayu', 'form_year' => 'Form 4'],
            [
                'education_type' => 'secondary',
                'category' => 'Core',
                'monthly_fee' => 80.00,
                'is_active' => true,
            ]
        );

        $bmF5 = Subject::firstOrCreate(
            ['name' => 'Bahasa Melayu', 'form_year' => 'Form 5'],
            [
                'education_type' => 'secondary',
                'category' => 'Core',
                'monthly_fee' => 80.00,
                'is_active' => true,
            ]
        );

        $payload = [
            'name' => 'Multi Year Student',
            'ic_number' => '090303-10-1234',
            'email' => 'multi_year_test@example.com',
            'phone' => '+60 12-777 8888',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'nationality' => 'Malaysian',
            'state' => 'Selangor',
            'programme' => 'secondary',
            'levels' => ['Form 4', 'Form 5'],
            'school_name' => 'SMK Taman Melawati',
            'subjects' => ['Bahasa Melayu - Form 4', 'Bahasa Melayu - Form 5'],
        ];

        $response = $this->post(route('register.student.submit'), $payload);
        $response->assertSessionHasErrors(['levels']);

        $this->assertNull(User::where('email', 'multi_year_test@example.com')->first());
        $this->assertSame(0, Course::whereIn('subject_id', [$bmF4->id, $bmF5->id])->count());
    }
}
