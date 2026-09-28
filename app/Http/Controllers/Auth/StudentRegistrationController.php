<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\FeeStructure;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentRegistrationController extends Controller
{
    /**
     * Show the student registration form.
     */
    public function create(Request $request)
    {
        $subjects = Subject::where('is_active', true)
            ->where('education_type', '!=', 'kindergarten')
            ->orderBy('order', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        $feeStructures = FeeStructure::where('is_active', true)
            ->orderBy('amount', 'desc')
            ->get();

        $bundleElectiveDiscountPct = (float) Setting::get('billing.bundle_elective_discount_pct', 25);
        $invoiceDueDays = (int) Setting::get('billing.invoice_due_days', 7);

        $selectedSubject   = $request->query('subject', '');
        $selectedProgramme = $request->query('programme', 'secondary');

        $malaysianStates = [
            'Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan',
            'Pahang', 'Perak', 'Perlis', 'Pulau Pinang', 'Sabah',
            'Sarawak', 'Selangor', 'Terengganu',
            'W.P. Kuala Lumpur', 'W.P. Labuan', 'W.P. Putrajaya',
        ];

        return view('auth.register-student', compact(
            'subjects',
            'feeStructures',
            'bundleElectiveDiscountPct',
            'invoiceDueDays',
            'selectedSubject',
            'selectedProgramme',
            'malaysianStates'
        ));
    }

    /**
     * Handle student registration submission.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'min:2', 'max:255'],
            'ic_number'        => ['required', 'string', 'regex:/^[0-9]{6}-[0-9]{2}-[0-9]{4}$/'],
            'email'            => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'            => ['required', 'string', 'min:8', 'max:50', 'regex:/^[0-9\s\-\+\(\)]+$/'],
            'password'         => ['required', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).+$/'],
            'nationality'      => ['required', 'string', 'max:50'],
            'state'            => ['required', 'string', 'max:50'],
            'programme'        => ['required', 'string', 'max:50'],
            'level'            => ['nullable', 'string', 'max:100'],
            'school_name'      => ['nullable', 'string', 'max:255'],
            'fee_structure_id' => ['nullable', 'integer', 'exists:fee_structures,id'],
            'subjects'         => ['nullable', 'array'],
            'subjects.*'       => ['string', 'max:255'],
        ], [
            'name.required'           => 'Please enter your full name.',
            'name.min'                => 'Full name must be at least 2 characters.',
            'ic_number.required'      => 'Please enter your MyKad / IC number.',
            'ic_number.regex'         => 'IC number must be in the format XXXXXX-XX-XXXX (e.g. 990101-10-1234).',
            'email.required'          => 'Please enter a valid email address.',
            'email.email'             => 'Please provide a valid email format (e.g. name@example.com).',
            'email.unique'            => 'This email address is already registered. Please log in or use another email address.',
            'phone.required'          => 'Please provide your phone number.',
            'phone.min'               => 'Please enter a valid phone number with at least 8 digits.',
            'phone.regex'             => 'The phone number contains invalid characters. Use digits, +, or -.',
            'nationality.required'    => 'Please select your nationality.',
            'state.required'          => 'Please select your state.',
            'password.required'       => 'Please create a password.',
            'password.min'            => 'Password must be at least 8 characters.',
            'password.confirmed'      => 'Passwords do not match. Please re-enter.',
            'password.regex'          => 'Password must contain at least one uppercase letter, one number, and one special character.',
            'programme.required'      => 'Please select an education level.',
        ]);

        // Normalise IC and check uniqueness
        $icHash = Student::hashIc($validated['ic_number']);
        if (Student::where('ic_number_hash', $icHash)->exists()) {
            return back()->withInput()->withErrors([
                'ic_number' => 'A student account with this IC number is already registered.',
            ]);
        }

        $selectedSubjectNames = !empty($validated['subjects']) ? $validated['subjects'] : [];
        $feeStructureId       = !empty($validated['fee_structure_id']) ? (int) $validated['fee_structure_id'] : null;

        // Derive DOB and gender from IC
        $dob    = Student::dobFromIc($validated['ic_number']);
        $gender = Student::genderFromIc($validated['ic_number']);

        try {
            $student = DB::transaction(function () use ($validated, $selectedSubjectNames, $feeStructureId, $icHash, $dob, $gender) {
                // 1. Create User account
                $user = User::create([
                    'name'     => $validated['name'],
                    'email'    => $validated['email'],
                    'phone'    => $validated['phone'],
                    'role'     => 'student',
                    'password' => Hash::make($validated['password']),
                ]);

                // 2. Create Student profile
                $student = Student::create([
                    'user_id'        => $user->id,
                    'ic_number_hash' => $icHash,
                    'gender'         => $gender,
                    'nationality'    => $validated['nationality'],
                    'state'          => $validated['state'],
                    'date_of_birth'  => $dob,
                    'level'          => $validated['level'] ?? 'Form 5',
                    'school_name'    => $validated['school_name'] ?? null,
                    'total_points'   => 0,
                    'streak_days'    => 0,
                ]);

                // 3. Enroll student in selected subjects
                if (!empty($selectedSubjectNames)) {
                    self::enrollInSubjects($student, $selectedSubjectNames, $validated['level'] ?? null, $feeStructureId);
                }

                // Auto-login
                Auth::login($user);

                return $student;
            });

            // Flash masked IC for success page
            session()->flash('reg_ic_masked', Student::maskedIc($validated['ic_number']));

            return redirect()->route('register.student.success', ['id' => $student->id]);
        } catch (\Exception $e) {
            if (app()->environment('testing')) { throw $e; }
            report($e);
            return redirect()->back()
                ->withInput()
                ->withErrors(['general' => 'Registration failed due to a technical issue. Please try again.']);
        }
    }

    /**
     * Show the success page after student registration.
     */
    public function success(Request $request)
    {
        $id = $request->query('id');
        $student = Student::find($id)?->load('user');

        if (!$student || !auth()->check() || auth()->id() !== $student->user_id) {
            return redirect()->route('student.dashboard');
        }

        $maskedIc = session('reg_ic_masked');
        $invoice  = $student->invoices()->with(['items', 'feeStructure'])->latest('id')->first();
        return view('auth.register-success-student', compact('student', 'maskedIc', 'invoice'));
    }

    /**
     * Enroll a student in courses corresponding to the given subject names.
     */
    public static function enrollInSubjects(Student $student, array $subjectNames, ?string $level = null, ?int $feeStructureId = null): void
    {
        $defaultTeacher = \App\Models\Teacher::first();

        // Determine education type and stage from level or student
        $effectiveLevel = $level ?: ($student->level ?? null);
        $educationType = null;
        $isLowerSecondary = false;
        $isUpperSecondary = false;

        if ($effectiveLevel) {
            if (str_contains($effectiveLevel, 'Year') || str_contains($effectiveLevel, 'Tahun')) {
                $educationType = 'primary';
            } elseif (str_contains($effectiveLevel, 'Form') || str_contains($effectiveLevel, 'Tingkatan')) {
                $educationType = 'secondary';
                if (in_array($effectiveLevel, ['Form 1', 'Form 2', 'Form 3'])) {
                    $isLowerSecondary = true;
                } else {
                    $isUpperSecondary = true;
                }
            } elseif (str_contains($effectiveLevel, 'Kindergarten') || str_contains($effectiveLevel, 'Tadika') || str_contains($effectiveLevel, 'Preschool')) {
                $educationType = 'kindergarten';
            }
        }

        foreach ($subjectNames as $subjectName) {
            $subjectName = trim($subjectName);
            if (empty($subjectName)) continue;

            $concatExpr = DB::connection()->getDriverName() === 'sqlite'
                ? "name || ' (' || form_year || ')'"
                : "CONCAT(name, ' (', form_year, ')')";

            // Match by name, slug, or formatted "Name (Form Year)"
            $query = Subject::where('is_active', true)
                ->where(function($q) use ($subjectName, $concatExpr) {
                    $q->where('name', $subjectName)
                      ->orWhere('slug', Str::slug($subjectName))
                      ->orWhere(DB::raw($concatExpr), $subjectName);
                });

            // Priority 1: Match exact year/form (e.g. Year 4, Form 1)
            if ($effectiveLevel) {
                $query->where(function($q) use ($effectiveLevel, $educationType, $isLowerSecondary, $isUpperSecondary) {
                    $q->where('form_year', $effectiveLevel);
                    if ($isLowerSecondary) {
                        $q->orWhere('form_year', 'Form 1–3')->orWhere('stage', 'Lower Secondary');
                    } elseif ($isUpperSecondary) {
                        $q->orWhere('form_year', 'Form 4–5')->orWhere('stage', 'Upper Secondary');
                    }
                });
            }

            if ($educationType) {
                $query->where('education_type', $educationType);
            }

            $subject = $query->first();

            // Fallback if not found with specific level filter
            if (!$subject) {
                $subject = Subject::where('is_active', true)
                    ->where(function($q) use ($subjectName, $concatExpr) {
                        $q->where('name', $subjectName)
                          ->orWhere('slug', Str::slug($subjectName))
                          ->orWhere(DB::raw($concatExpr), $subjectName);
                    })
                    ->first();
            }

            if (!$subject) {
                $subject = Subject::create([
                    'name'           => $subjectName,
                    'slug'           => Str::slug($subjectName . ' ' . ($effectiveLevel ?: 'Form 5')) ?: 'subj-' . Str::random(5),
                    'education_type' => $educationType ?: 'secondary',
                    'stage'          => $isLowerSecondary ? 'Lower Secondary' : 'Upper Secondary',
                    'form_year'      => $effectiveLevel ?: 'Form 5',
                    'category'       => 'Core',
                    'is_active'      => true,
                ]);
            }

            $course = null;
            if ($level) {
                $course = Course::where('subject_id', $subject->id)
                    ->where(function($q) use ($level) {
                        $q->where('level', $level)
                          ->orWhere('title', 'like', "%{$level}%");
                    })
                    ->first();
            }
            if (!$course) {
                $course = Course::where('subject_id', $subject->id)->latest('id')->first();
            }
            if (!$course) {
                $course = Course::create([
                    'subject_id'    => $subject->id,
                    'teacher_id'    => $defaultTeacher?->id ?? 1,
                    'title'         => $subject->name,
                    'description'   => 'Comprehensive learning course for ' . $subject->name,
                    'level'         => $level ?: 'SPM',
                    'status'        => 'published',
                    'total_lessons' => 12,
                ]);
            }

            Enrollment::firstOrCreate(
                ['student_id' => $student->id, 'course_id' => $course->id],
                ['status' => 'active', 'enrolled_at' => now(), 'progress_pct' => 0, 'completed_lessons' => 0]
            );
        }

        // Automatically link all global diagnostic tasks + course assignments to the student
        $student->syncGlobalAndCourseAssignments();

        // Automatically generate tuition fee invoice for the enrolled subjects
        try {
            app(\App\Services\InvoiceService::class)->generateForStudent($student, $feeStructureId);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::info("Auto-invoice note for student #{$student->id}: " . $e->getMessage());
        }
    }
}

