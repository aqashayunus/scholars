<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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

class ParentRegistrationController extends Controller
{
    /**
     * Show the parent registration form.
     */
    public function create(Request $request)
    {
        $subjects = Subject::where('is_active', true)
            ->where('education_type', '!=', 'kindergarten')
            ->orderBy('order', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        $feeStructures = FeeStructure::with('subjects')
            ->availableForRegistration()
            ->whereNull('student_id')
            ->orderBy('amount', 'desc')
            ->get();

        $bundleElectiveDiscountPct = (float) Setting::get('billing.bundle_elective_discount_pct', 25);
        $invoiceDueDays = (int) Setting::get('billing.invoice_due_days', 7);

        $malaysianStates = [
            'Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan',
            'Pahang', 'Perak', 'Perlis', 'Pulau Pinang', 'Sabah',
            'Sarawak', 'Selangor', 'Terengganu',
            'W.P. Kuala Lumpur', 'W.P. Labuan', 'W.P. Putrajaya',
        ];

        $relationships = [
            'Father', 'Mother', 'Guardian', 'Others',
        ];

        return view('auth.register-parent', compact(
            'subjects',
            'feeStructures',
            'bundleElectiveDiscountPct',
            'invoiceDueDays',
            'malaysianStates',
            'relationships'
        ));
    }

    /**
     * Verify a Student by IC number — returns JSON for AJAX lookup.
     * Only returns name + level + masked IC info (no email/phone exposed).
     */
    public function verifyStudentIc(Request $request)
    {
        $ic = trim($request->input('ic_number', ''));
        $normalised = Student::normaliseIc($ic);

        if (strlen($normalised) !== 12) {
            return response()->json(['found' => false]);
        }

        $icHash  = Student::hashIc($ic);
        $student = Student::where('ic_number_hash', $icHash)->with('user')->first();

        if (!$student || !$student->user) {
            return response()->json([
                'found'   => false,
                'message' => 'No student found with this IC number. Please check and try again.',
            ]);
        }

        return response()->json([
            'found'       => true,
            'name'        => $student->user->name,
            'level'       => $student->level,
            'school'      => $student->school_name,
            'student_id'  => $student->id,
            'masked_ic'   => Student::maskedIc($ic),
        ]);
    }

    /**
     * Handle parent registration form submission.
     */
    public function store(Request $request)
    {
        $hasExistingChild = $request->input('child_has_account') === 'yes';

        // Base validation rules for parent info
        $isLocal = $request->input('nationality_type', 'local') === 'local';

        $rules = [
            'nationality_type'  => ['required', 'in:local,international'],
            'parent_name'       => ['required', 'string', 'min:2', 'max:255'],
            'email'             => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'             => ['required', 'string', 'min:8', 'max:50', 'regex:/^[0-9\s\-\+\(\)]+$/'],
            'password'          => ['required', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).+$/'],
            'relationship'      => ['required', 'string', 'max:30'],
            'child_has_account' => ['required', 'in:yes,no'],

            // Address fields
            'address_line_1'    => ['required', 'string', 'max:255'],
            'address_line_2'    => ['nullable', 'string', 'max:255'],
            'postcode'          => ['required', 'string', 'max:20'],
            'city'              => ['required', 'string', 'max:100'],
            'state'             => ['required', 'string', 'max:100'],
            'country'           => ['nullable', 'string', 'max:100'],
        ];

        if ($isLocal) {
            $rules['parent_ic_number'] = ['required', 'string', 'regex:/^[0-9]{6}-[0-9]{2}-[0-9]{4}$/'];
        } else {
            $rules['passport_number'] = ['required', 'string', 'max:50'];
        }

        if ($hasExistingChild) {
            // Linking existing student → need their IC
            $rules['child_ic_number'] = ['required', 'string', 'regex:/^[0-9]{6}-[0-9]{2}-[0-9]{4}$/'];
        } else {
            // New child account → need name + education details
            $rules['child_name']   = ['required', 'string', 'min:2', 'max:255'];
            $rules['child_ic_number'] = ['required', 'string', 'regex:/^[0-9]{6}-[0-9]{2}-[0-9]{4}$/'];
            $rules['programme']    = ['required', 'string', 'max:50'];
            $rules['level']        = ['nullable', 'string', 'max:100'];
            $rules['school_name']      = ['nullable', 'string', 'max:255'];
            $rules['fee_structure_id'] = ['nullable', 'integer', 'exists:fee_structures,id'];
            $rules['subjects']         = ['nullable', 'array'];
            $rules['subjects.*']       = ['string', 'max:255'];
        }

        $messages = [
            'nationality_type.required'  => 'Please indicate if you are a Local (Malaysian) or International parent.',
            'parent_name.required'       => 'Please enter your full name.',
            'parent_ic_number.required'  => 'Please enter your MyKad / IC number.',
            'parent_ic_number.regex'     => 'Parent IC must be in format XXXXXX-XX-XXXX.',
            'passport_number.required'   => 'Please enter your Passport / National ID number.',
            'address_line_1.required'    => 'Please enter your residential address (Address Line 1).',
            'postcode.required'          => 'Please enter your postcode.',
            'city.required'              => 'Please enter your city.',
            'state.required'             => 'Please select your state.',
            'email.required'             => 'Please enter a valid email address.',
            'email.unique'               => 'This email address is already registered. Please log in or use another email address.',
            'phone.required'             => 'Please provide your phone number.',
            'password.required'          => 'Please create a password.',
            'password.min'               => 'Password must be at least 8 characters.',
            'password.confirmed'         => 'Passwords do not match. Please re-enter.',
            'password.regex'             => 'Password must contain at least one uppercase letter, one number, and one special character.',
            'relationship.required'      => 'Please select your relationship to the child.',
            'child_ic_number.required'   => 'Please enter your child\'s IC number.',
            'child_ic_number.regex'      => 'Child IC must be in format XXXXXX-XX-XXXX.',
            'child_name.required'        => 'Please enter your child\'s full name.',
            'programme.required'         => 'Please select a programme.',
        ];

        $validated = $request->validate($rules, $messages);

        // Check parent IC uniqueness if local
        $parentIcHash = null;
        if ($isLocal && !empty($validated['parent_ic_number'])) {
            $parentIcHash = User::hashIc($validated['parent_ic_number']);
            if (User::where('ic_number_hash', $parentIcHash)->exists()) {
                return back()->withInput()->withErrors([
                    'parent_ic_number' => 'An account with this IC number is already registered.',
                ]);
            }
        }

        // Check child IC uniqueness if creating new child
        if (!$hasExistingChild) {
            $childIcHash = Student::hashIc($validated['child_ic_number']);
            if (Student::where('ic_number_hash', $childIcHash)->exists()) {
                return back()->withInput()->withErrors([
                    'child_ic_number' => 'A student account with this IC number already exists. Use "Link Existing Child" instead.',
                ]);
            }
        }

        try {
            $result = DB::transaction(function () use ($validated, $hasExistingChild, $parentIcHash, $request) {
                // --- 1. Create Parent User ---
                $parentUser = User::create([
                    'name'             => $validated['parent_name'],
                    'email'            => $validated['email'],
                    'phone'            => $validated['phone'],
                    'nationality_type' => $validated['nationality_type'] ?? 'local',
                    'passport_number'  => $validated['passport_number'] ?? null,
                    'address_line_1'   => $validated['address_line_1'],
                    'address_line_2'   => $validated['address_line_2'] ?? null,
                    'postcode'         => $validated['postcode'],
                    'city'             => $validated['city'],
                    'state'            => $validated['state'],
                    'country'          => $validated['country'] ?? 'Malaysia',
                    'ic_number_hash'   => $parentIcHash,
                    'role'             => 'parent',
                    'password'         => Hash::make($validated['password']),
                ]);

                if ($hasExistingChild) {
                    // --- 2a. Link existing student via IC hash ---
                    $childIcHash = Student::hashIc($validated['child_ic_number']);
                    $student     = Student::where('ic_number_hash', $childIcHash)->with('user')->first();

                    if (!$student || !$student->user) {
                        throw new \RuntimeException('student_not_found');
                    }

                    // Attach via pivot (safe — unique constraint prevents duplicates)
                    $alreadyLinked = DB::table('parent_student')
                        ->where('parent_user_id', $parentUser->id)
                        ->where('student_id', $student->id)
                        ->exists();

                    if (!$alreadyLinked) {
                        DB::table('parent_student')->insert([
                            'parent_user_id' => $parentUser->id,
                            'student_id'     => $student->id,
                            'relationship'   => $validated['relationship'],
                            'is_primary'     => true,
                            'status'         => 'active',
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ]);

                        // Also set parent_user_id for backward compat if not already set
                        if (!$student->parent_user_id) {
                            $student->update(['parent_user_id' => $parentUser->id]);
                        }
                    }

                    return ['parent' => $parentUser, 'student' => $student, 'isNew' => false];
                } else {
                    // --- 2b. Create new child student ---
                    $selectedSubjectNames = !empty($validated['subjects']) ? $validated['subjects'] : [];
                    $feeStructureId       = !empty($validated['fee_structure_id']) ? (int) $validated['fee_structure_id'] : null;

                    // Auto-generate student email
                    $firstName     = strtolower(explode(' ', trim($validated['child_name']))[0] ?? 'student');
                    $firstNameSlug = Str::slug($firstName, '');
                    $prefix        = strlen($firstNameSlug) >= 2 ? $firstNameSlug : 'student';

                    $childEmail = $prefix . '@olympianscholars.my';
                    $counter = 1;
                    while (User::where('email', $childEmail)->exists()) {
                        $counter++;
                        $childEmail = $prefix . $counter . '@olympianscholars.my';
                    }

                    $childUser = User::create([
                        'name'     => $validated['child_name'],
                        'email'    => $childEmail,
                        'phone'    => '',
                        'role'     => 'student',
                        'password' => Hash::make($validated['password']),
                    ]);

                    $childIcHash = Student::hashIc($validated['child_ic_number']);

                    // Derive DOB and gender from child IC
                    $dob    = Student::dobFromIc($validated['child_ic_number']);
                    $gender = Student::genderFromIc($validated['child_ic_number']);

                    $student = Student::create([
                        'user_id'        => $childUser->id,
                        'parent_user_id' => $parentUser->id,
                        'ic_number_hash' => $childIcHash,
                        'gender'         => $gender,
                        'date_of_birth'  => $dob,
                        'level'          => $validated['level'] ?? 'Form 5',
                        'school_name'    => $validated['school_name'] ?? null,
                        'total_points'   => 0,
                        'streak_days'    => 1,
                    ]);

                    DB::table('parent_student')->insert([
                        'parent_user_id' => $parentUser->id,
                        'student_id'     => $student->id,
                        'relationship'   => $validated['relationship'],
                        'is_primary'     => true,
                        'status'         => 'active',
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);

                    if (!empty($selectedSubjectNames)) {
                        StudentRegistrationController::enrollInSubjects($student, $selectedSubjectNames, $validated['level'] ?? null, $feeStructureId);
                    }

                    $childPassword = $validated['password'];
                    return ['parent' => $parentUser, 'student' => $student, 'isNew' => true, 'childPassword' => $childPassword];
                }
            });

            Auth::login($result['parent']);

            // Trigger automated email communication (sent synchronously for immediate greeting delivery)
            try {
                if ($result['isNew']) {
                    \App\Services\Email\EmailAutomationService::trigger(
                        'registration_received',
                        [
                            'user'         => $result['parent'],
                            'parent'       => $result['parent'],
                            'student'      => $result['student'],
                            'parent_name'  => $result['parent']->name,
                            'student_name' => $result['student']->user?->name ?? 'Student',
                        ],
                        "parent_reg_{$result['parent']->id}",
                        true
                    );
                } else {
                    \App\Services\Email\EmailAutomationService::trigger(
                        'parent_student_linked',
                        [
                            'user'         => $result['parent'],
                            'parent'       => $result['parent'],
                            'student'      => $result['student'],
                            'parent_name'  => $result['parent']->name,
                            'student_name' => $result['student']->user?->name ?? 'Student',
                        ],
                        "parent_link_{$result['parent']->id}_{$result['student']->id}",
                        true
                    );
                }
            } catch (\Throwable $emailErr) {
                \Illuminate\Support\Facades\Log::warning('[ParentRegistration] Email trigger failed: ' . $emailErr->getMessage());
            }

            $latestInvoice = $result['student']->invoices()->latest('id')->first();

            session()->flash('reg_success', [
                'parent_name'    => $result['parent']->name,
                'parent_email'   => $result['parent']->email,
                'child_name'     => $result['student']->user->name ?? $validated['child_name'] ?? '',
                'child_level'    => $result['student']->level,
                'child_school'   => $result['student']->school_name,
                'is_new_child'   => $result['isNew'],
                'masked_ic'      => Student::maskedIc($validated['child_ic_number']),
                'relationship'   => $validated['relationship'],
                'invoice_id'     => $latestInvoice?->id,
                'invoice_number' => $latestInvoice?->invoice_number,
                'invoice_amount' => $latestInvoice?->total_amount,
            ]);

            return redirect()->route('register.parent.success');
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'student_not_found') {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['child_ic_number' => 'No student account found with this IC number. Please check the IC and try again.']);
            }
            throw $e;
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()
                ->withInput()
                ->withErrors(['general' => 'Registration failed due to a technical issue. Please try again.']);
        }
    }

    /**
     * Show success page after parent registration.
     */
    public function success(Request $request)
    {
        $data = session('reg_success');

        if (!$data || !auth()->check()) {
            return redirect()->route('parent.dashboard');
        }

        return view('auth.register-success-parent', compact('data'));
    }
}
