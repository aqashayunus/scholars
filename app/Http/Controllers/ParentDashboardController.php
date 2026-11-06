<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Http\Requests\Parent\ConnectChildRequest;
use App\Http\Requests\Parent\CreateCustomInvoiceRequest;
use App\Http\Requests\Parent\PayChildInvoiceRequest;
use App\Http\Requests\Parent\ProcessPaymentRequest;
use App\Http\Requests\Parent\StoreChildRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\User;
use App\Services\Parent\AcademicProgressService;
use App\Services\Parent\BillingService;
use App\Services\Parent\ChildAccessService;
use App\Services\ParentLinkService;
use App\Services\Student\ClassScheduleService;
use App\Services\StudentAccessRestrictionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ParentDashboardController extends Controller
{
    public function __construct(
        private StudentAccessRestrictionService $restrictionService,
        private ParentLinkService $parentLinkService,
        private ChildAccessService $access,
        private AcademicProgressService $academic,
        private BillingService $billing,
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // PAGES
    // ─────────────────────────────────────────────────────────────────────────

    public function dashboard(Request $request)
    {
        $user     = auth()->user();
        $children = $this->access->getParentChildren($user);
        $child    = $this->access->getAuthorizedChild($children, $request->query('child_id'));

        $isChildRestricted = $child ? $this->restrictionService->isStudentRestricted($child) : false;
        $restrictionDetails = $isChildRestricted ? $this->restrictionService->getRestrictionDetails($child) : null;

        if ($isChildRestricted) {
            $childProgress   = [];
            $attStats        = ['total'=>0,'attended'=>0,'absent'=>0,'late'=>0,'pct'=>0];
            $overallProgress = 0;
            $needsAttention  = [];
            $upcomingClasses = collect();
            $nextClass       = null;
        } else {
            $childProgress   = $child ? $this->academic->getChildProgress($child) : [];
            $attStats        = $child ? $this->academic->getAttendanceStats($child) : ['total'=>0,'attended'=>0,'absent'=>0,'late'=>0,'pct'=>0];
            $overallProgress = count($childProgress) > 0
                ? (int) round(collect($childProgress)->avg('pct'))
                : 0;
            $needsAttention  = $this->academic->needsAttention($childProgress);
            $upcomingClasses = $child ? $this->academic->getUpcomingClasses($child, 3) : collect();
            $nextClass       = $upcomingClasses->first();
        }

        // Financial metric cards data
        $paymentsQuery = Payment::where(function($q) use ($user, $children) {
            $q->where('user_id', $user->id)
              ->orWhere('payer_id', $user->id);
            if ($children->isNotEmpty()) {
                $q->orWhereIn('student_id', $children->pluck('id'));
            }
        })->with('course.subject');

        if ($child) {
            $childCourseIds = $child->enrollments->pluck('course_id')->filter()->all();
            $paymentsQuery->where(function($q) use ($child, $childCourseIds) {
                $q->where('student_id', $child->id);
                if (!empty($childCourseIds)) {
                    $q->orWhereIn('course_id', $childCourseIds);
                }
            });
        }

        $payments = $paymentsQuery->orderByDesc('id')->get();

        $summary          = $this->billing->outstandingSummary($payments, $children, $child);
        $totalOutstanding = $summary['totalOutstanding'];
        $pendingCount     = $summary['pendingCount'];
        $outstanding      = $totalOutstanding;
        $lastPaymentMade  = $summary['lastPaymentMade'];
        $nextPaymentDue   = $summary['nextPaymentDue'];
        $latestPayment    = $lastPaymentMade;

        return view('parent.dashboard', compact(
            'children', 'child', 'childProgress', 'attStats', 'overallProgress',
            'needsAttention', 'upcomingClasses', 'nextClass',
            'latestPayment', 'outstanding', 'totalOutstanding', 'pendingCount',
            'lastPaymentMade', 'nextPaymentDue', 'isChildRestricted', 'restrictionDetails'
        ));
    }

    public function progress(Request $request)
    {
        $user     = auth()->user();
        $children = $this->access->getParentChildren($user);
        $child    = $this->access->getAuthorizedChild($children, $request->query('child_id'));

        if ($child && $this->restrictionService->isStudentRestricted($child)) {
            $details = $this->restrictionService->getRestrictionDetails($child);
            return response()->view('parent.child-restricted', compact('children', 'child', 'details'), 403);
        }

        $childProgress   = $child ? $this->academic->getChildProgress($child) : [];
        $overallProgress = count($childProgress) > 0
            ? (int) round(collect($childProgress)->avg('pct'))
            : 0;
        $needsAttention  = $this->academic->needsAttention($childProgress);

        return view('parent.progress', compact(
            'children', 'child', 'childProgress', 'overallProgress', 'needsAttention'
        ));
    }

    public function classes(Request $request)
    {
        $user     = auth()->user();
        $children = $this->access->getParentChildren($user);
        $child    = $this->access->getAuthorizedChild($children, $request->query('child_id'));

        if ($child && $this->restrictionService->isStudentRestricted($child)) {
            $details = $this->restrictionService->getRestrictionDetails($child);
            return response()->view('parent.child-restricted', compact('children', 'child', 'details'), 403);
        }

        $upcomingClasses = $child ? $this->academic->getUpcomingClasses($child, 10) : collect();
        $nextClass       = $upcomingClasses->first();
        $pastClasses     = $child ? $this->academic->getPastClasses($child, 10) : collect();
        $attStats        = $child ? $this->academic->getAttendanceStats($child) : ['total'=>0,'attended'=>0,'absent'=>0,'late'=>0,'pct'=>0];

        $classesThisWeek = $child
            ? ClassScheduleService::getStudentLiveClassesQuery($child)
                ->whereBetween('scheduled_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count()
            : 0;

        $attendanceLog = $child
            ? StudentAttendance::where('student_id', $child->id)
                ->with('liveClass.course.subject')
                ->whereHas('liveClass', fn($q) =>
                    $q->whereBetween('scheduled_at', [now()->startOfMonth(), now()->endOfMonth()])
                )
                ->orderByDesc('created_at')
                ->get()
            : collect();

        return view('parent.classes', compact(
            'children', 'child',
            'upcomingClasses', 'nextClass', 'pastClasses',
            'attStats', 'classesThisWeek', 'attendanceLog'
        ));
    }

    public function attendance(Request $request)
    {
        $user     = auth()->user();
        $children = $this->access->getParentChildren($user, ['attendances.liveClass']);
        $child    = $this->access->getAuthorizedChild($children, $request->query('child_id'));

        if ($child && $this->restrictionService->isStudentRestricted($child)) {
            $details = $this->restrictionService->getRestrictionDetails($child);
            return response()->view('parent.child-restricted', compact('children', 'child', 'details'), 403);
        }

        $attStats = $child ? $this->academic->getAttendanceStats($child) : ['total'=>0,'attended'=>0,'absent'=>0,'late'=>0,'pct'=>0];

        $attendanceLog = $child
            ? StudentAttendance::where('student_id', $child->id)
                ->with('liveClass.course.subject')
                ->orderByDesc('created_at')
                ->take(20)
                ->get()
            : collect();

        $childrenStats = $children->map(function (Student $c) {
            $att = $this->academic->getAttendanceStats($c);
            $pgs = $c->enrollments->avg('progress_pct') ?? 0;
            $pendingTasks = $c->assignments()->where('status', 'pending')->count();
            return [
                'student'      => $c,
                'attPct'       => $att['pct'],
                'progressPct'  => (int) round($pgs),
                'pendingTasks' => $pendingTasks,
            ];
        });

        return view('parent.attendance', compact(
            'children', 'child', 'childrenStats', 'attStats', 'attendanceLog'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CHILD-SPECIFIC PROTECTED ACADEMIC ROUTES (RESTful)
    // ─────────────────────────────────────────────────────────────────────────

    public function childProgress(Request $request, $childId)
    {
        $user     = auth()->user();
        $children = $this->access->getParentChildren($user);
        $child    = $this->access->getAuthorizedChild($children, (string) $childId);

        if ($this->restrictionService->isStudentRestricted($child)) {
            $details = $this->restrictionService->getRestrictionDetails($child);
            return response()->view('parent.child-restricted', compact('children', 'child', 'details'), 403);
        }

        $request->merge(['child_id' => $child->id]);
        return $this->progress($request);
    }

    public function childResults(Request $request, $childId)
    {
        return $this->childProgress($request, $childId);
    }

    public function childAttendance(Request $request, $childId)
    {
        $user     = auth()->user();
        $children = $this->access->getParentChildren($user);
        $child    = $this->access->getAuthorizedChild($children, (string) $childId);

        if ($this->restrictionService->isStudentRestricted($child)) {
            $details = $this->restrictionService->getRestrictionDetails($child);
            return response()->view('parent.child-restricted', compact('children', 'child', 'details'), 403);
        }

        $request->merge(['child_id' => $child->id]);
        return $this->attendance($request);
    }

    public function childClasses(Request $request, $childId)
    {
        $user     = auth()->user();
        $children = $this->access->getParentChildren($user);
        $child    = $this->access->getAuthorizedChild($children, (string) $childId);

        if ($this->restrictionService->isStudentRestricted($child)) {
            $details = $this->restrictionService->getRestrictionDetails($child);
            return response()->view('parent.child-restricted', compact('children', 'child', 'details'), 403);
        }

        $request->merge(['child_id' => $child->id]);
        return $this->classes($request);
    }

    public function childAssignments(Request $request, $childId)
    {
        $user     = auth()->user();
        $children = $this->access->getParentChildren($user);
        $child    = $this->access->getAuthorizedChild($children, (string) $childId);

        if ($this->restrictionService->isStudentRestricted($child)) {
            $details = $this->restrictionService->getRestrictionDetails($child);
            return response()->view('parent.child-restricted', compact('children', 'child', 'details'), 403);
        }

        return redirect()->route('parent.dashboard', ['child_id' => $child->id]);
    }

    public function storeChild(StoreChildRequest $request)
    {
        $parent = auth()->user();

        $validated = $request->validated();

        // Check if student with this IC already exists
        $childIcHash = Student::hashIc($validated['child_ic_number']);
        if (Student::where('ic_number_hash', $childIcHash)->exists()) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['child_ic_number' => 'A student account with this IC number already exists. Please use "Connect Existing Student" to link them.']);
        }

        try {
            $student = DB::transaction(function () use ($parent, $validated, $childIcHash) {
                // Auto-generate student email: <first name>@olympianscholars.my
                $firstName = strtolower(explode(' ', trim($validated['name']))[0] ?? 'student');
                $firstNameSlug = Str::slug($firstName, '');
                $prefix = strlen($firstNameSlug) >= 2 ? $firstNameSlug : 'student';

                $childEmail = $prefix . '@olympianscholars.my';
                $counter = 1;
                while (User::where('email', $childEmail)->exists()) {
                    $counter++;
                    $childEmail = $prefix . $counter . '@olympianscholars.my';
                }

                $childPassword = !empty($validated['password'])
                    ? $validated['password']
                    : Str::random(8) . rand(10, 99) . '!';

                $childUser = User::create([
                    'name'     => $validated['name'],
                    'email'    => $childEmail,
                    'phone'    => '',
                    'role'     => 'student',
                    'password' => Hash::make($childPassword),
                ]);

                $dob    = Student::dobFromIc($validated['child_ic_number']);
                $gender = Student::genderFromIc($validated['child_ic_number']);

                $student = Student::create([
                    'user_id'        => $childUser->id,
                    'parent_user_id' => $parent->id,
                    'ic_number_hash' => $childIcHash,
                    'gender'         => $gender,
                    'date_of_birth'  => $dob,
                    'level'          => $validated['level'],
                    'school_name'    => $validated['school_name'] ?? null,
                    'total_points'   => 0,
                    'streak_days'    => 1,
                ]);

                // Link via pivot table
                DB::table('parent_student')->insertOrIgnore([
                    'parent_user_id' => $parent->id,
                    'student_id'     => $student->id,
                    'relationship'   => $validated['relationship'] ?? 'Guardian',
                    'is_primary'     => true,
                    'status'         => 'active',
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);

                // Enroll in selected subjects if provided
                $subjectNames = $validated['subjects'] ?? [];
                if (!empty($subjectNames)) {
                    \App\Http\Controllers\Auth\StudentRegistrationController::enrollInSubjects(
                        $student, $subjectNames, $validated['level'] ?? null
                    );
                }

                return $student;
            });

            return redirect()->route('parent.dashboard', ['child_id' => $student->id])->with('success',
                "✓ Child account for {$student->user->name} created and linked successfully!"
            );
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->withErrors(['name' => 'Failed to create child account. Please try again.']);
        }
    }

    /**
     * Connect an existing student to this parent using the student's MyKad IC number or email.
     */
    public function connectChild(ConnectChildRequest $request)
    {
        $parent = auth()->user();

        $identifier   = trim($request->input('identifier'));
        $relationship = $request->input('relationship', 'Guardian');

        // Check if identifier is an IC number (12 digits)
        $cleanDigits = preg_replace('/\D/', '', $identifier);
        $student     = null;

        if (strlen($cleanDigits) === 12) {
            $icHash  = Student::hashIc($identifier);
            $student = Student::where('ic_number_hash', $icHash)->with('user')->first();
        }

        if (!$student) {
            // Try by student's user email
            $studentUser = User::where('email', $identifier)->where('role', 'student')->first();
            if ($studentUser) {
                $student = Student::where('user_id', $studentUser->id)->with('user')->first();
            }
        }

        if (!$student) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['identifier' => 'No student found with that IC number or email. Please check and try again.']);
        }

        $result = $this->parentLinkService->requestLink($parent, $student, $relationship);

        if ($result['status'] === 'active') {
            // Trigger automated official communication — only fires on an actual
            // successful link, never on the pending-approval chain path (Phase 0's
            // ParentLinkService already decides whether this link activates
            // immediately or requires the existing guardian's approval first).
            try {
                \App\Services\Email\EmailAutomationService::trigger(
                    'parent_student_linked',
                    [
                        'user'    => $parent,
                        'parent'  => $parent,
                        'student' => $student,
                    ],
                    "parent_link_{$parent->id}_{$student->id}"
                );
            } catch (\Throwable $emailErr) {
                \Illuminate\Support\Facades\Log::warning('[ParentDashboard] Child linked email trigger failed: ' . $emailErr->getMessage());
            }
        }

        return match ($result['status']) {
            'already_linked' => redirect()->back()
                ->with('success', "{$student->user->name} is already linked to your account."),
            'active' => redirect()->route('parent.dashboard', ['child_id' => $student->id])
                ->with('success', "✓ Successfully linked {$student->user->name} to your parent account!"),
            'pending' => redirect()->back()
                ->with('success', 'This student already has a guardian on file. A request has been sent to the existing guardian for approval — you will gain access once they approve it.'),
        };
    }

    /**
     * Display parent payments, invoices, billing overview and receipts.
     */
    public function payments(Request $request)
    {
        $user     = auth()->user();
        $children = $this->access->getParentChildren($user);
        $child    = $this->access->getAuthorizedChild($children, $request->query('child_id'));

        $paymentsQuery = Payment::where('user_id', $user->id)->with('course.subject');

        // Optional filter by child's courses if specific child is selected and has courses
        if ($child) {
            $childCourseIds = $child->enrollments->pluck('course_id')->filter()->all();
            if (!empty($childCourseIds)) {
                $paymentsQuery->where(function($q) use ($childCourseIds) {
                    $q->whereIn('course_id', $childCourseIds)
                      ->orWhereNull('course_id');
                });
            }
        }

        $payments = $paymentsQuery->orderByDesc('id')->get();

        $totalPaid     = (float) $payments->where('status', PaymentStatus::Successful)->sum('amount');
        $paidCount     = $payments->where('status', PaymentStatus::Successful)->count();
        $latestPayment = $payments->where('status', PaymentStatus::Successful)->first();

        $summary          = $this->billing->outstandingSummary($payments, $children, $child);
        $totalOutstanding = $summary['totalOutstanding'];
        $pendingCount     = $summary['pendingCount'];
        $lastPaymentMade  = $summary['lastPaymentMade'];
        $nextPaymentDue   = $summary['nextPaymentDue'];

        // Pending invoices list for quick checkout
        $pendingPayments = $payments->where('status', PaymentStatus::Pending)->values();

        return view('parent.payments', compact(
            'children', 'child', 'payments', 'totalPaid', 'totalOutstanding',
            'pendingCount', 'paidCount', 'latestPayment', 'pendingPayments',
            'lastPaymentMade', 'nextPaymentDue'
        ));
    }

    /**
     * Process simulated / gateway payment for invoices.
     */
    public function processPayment(ProcessPaymentRequest $request)
    {
        $user = auth()->user();

        $validated = $request->validated();

        $paymentIds = is_array($validated['payment_ids'])
            ? $validated['payment_ids']
            : explode(',', (string) $validated['payment_ids']);

        $result = $this->billing->processPayment($user, $paymentIds, $validated['payment_method'], $validated['bank_name'] ?? null);

        if (!$result['success']) {
            return redirect()->back()->withErrors(['payment_ids' => $result['message']]);
        }

        return redirect()->route('parent.payments')->with('success', $result['message']);
    }

    /**
     * Process child invoice payment by parent.
     */
    public function payChildInvoice(PayChildInvoiceRequest $request, $id)
    {
        $parent   = auth()->user();
        $children = $this->access->getParentChildren($parent);
        $childIds = $children->pluck('id')->toArray();

        // Short-circuit before touching the payment flow, same order as
        // before extraction — an already-paid invoice must not go through
        // the bank-transfer proof_file requirement (the FormRequest itself
        // skips that rule entirely once it sees the invoice is Paid).
        $invoice = Invoice::whereIn('student_id', $childIds)->findOrFail($id);
        if ($invoice->status === InvoiceStatus::Paid) {
            return back()->with('info', 'This invoice has already been fully paid.');
        }

        $paymentMethod = $request->input('payment_method', 'fpx');
        $proofFile = $paymentMethod === 'bank_transfer' ? $request->file('proof_file') : null;

        $result = $this->billing->payInvoice($parent, $childIds, (int) $id, $paymentMethod, $proofFile);

        return match ($result['status']) {
            'redirect_away' => redirect()->away($result['url']),
            'gateway_error' => back()->with('error', $result['message']),
            default         => back()->with('success', $result['message']),
        };
    }

    /**
     * Create a manual custom tuition or materials fee invoice.
     */
    public function createCustomInvoice(CreateCustomInvoiceRequest $request)
    {
        $user = auth()->user();

        $validated = $request->validated();

        $invoiceRef = $this->billing->createCustomInvoice(
            $user,
            $validated['description'],
            (float) $validated['amount'],
            $validated['course_id'] ?? null
        );

        return redirect()->route('parent.payments')
            ->with('success', "✓ New tuition invoice #{$invoiceRef} created successfully.");
    }
}
