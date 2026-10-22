<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\FeeStructure;
use App\Models\Invoice;
use App\Models\LiveClass;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\User;
use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use App\Services\StudentAccessRestrictionService;
use Illuminate\Support\Str;

class ParentDashboardController extends Controller
{
    public function __construct(private StudentAccessRestrictionService $restrictionService) {}

    // ─────────────────────────────────────────────────────────────────────────
    // SECURITY HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Get all students that belong to the authenticated parent.
     * Uses BOTH the legacy parent_user_id AND the new parent_student pivot
     * so existing relationships continue to work after the pivot migration.
     */
    private function getParentChildren(\App\Models\User $user, array $with = []): \Illuminate\Database\Eloquent\Collection
    {
        $defaultWith = ['user', 'enrollments.course.subject'];
        $with = array_unique(array_merge($defaultWith, $with));

        // IDs via legacy column (backward compat)
        $legacyIds = Student::where('parent_user_id', $user->id)->pluck('id')->toArray();

        // IDs via pivot
        $pivotIds = \Illuminate\Support\Facades\DB::table('parent_student')
            ->where('parent_user_id', $user->id)
            ->where('status', 'active')
            ->pluck('student_id')
            ->toArray();

        $allIds = array_unique(array_merge($legacyIds, $pivotIds));

        $children = Student::whereIn('id', $allIds)->with($with)->get();

        // Attach per-child restriction & payment status
        foreach ($children as $ch) {
            $ch->is_restricted = $this->restrictionService->isStudentRestricted($ch);
            $ch->payment_status = $this->restrictionService->getStudentPaymentStatus($ch);
            $ch->restriction_details = $ch->is_restricted ? $this->restrictionService->getRestrictionDetails($ch) : null;
        }

        return $children;
    }

    /**
     * Resolve a child that is AUTHORIZED to the authenticated parent.
     * Aborts with 403 if the child_id is provided but doesn't belong to this parent.
     * Falls back to the first child if no child_id is provided.
     */
    private function getAuthorizedChild(\Illuminate\Database\Eloquent\Collection $children, ?string $childId): ?Student
    {
        if ($childId) {
            $child = $children->firstWhere('id', (int) $childId);
            if (!$child) {
                abort(403, 'You are not authorized to access this student\'s data.');
            }
            return $child;
        }
        return $children->first();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SHARED HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Compute this-month attendance stats for a given Student.
     * Returns ['total', 'attended', 'absent', 'late', 'pct']
     */
    private function getAttendanceStats(Student $student): array
    {
        return app(\App\Services\AttendanceService::class)->getStudentAttendanceStats($student);
    }

    /**
     * Return per-subject progress for a student from their enrollments.
     * [['subject'=>'Mathematics','emoji'=>'🧮','color'=>'blue','pct'=>78], ...]
     */
    private function getChildProgress(Student $student): array
    {
        $colorMap = [
            'Mathematics'          => 'blue',
            'Science'              => 'emerald',
            'English'              => 'purple',
            'Bahasa Melayu'        => 'amber',
            'Physics'              => 'indigo',
            'Chemistry'            => 'teal',
            'Biology'              => 'green',
            'Sejarah'              => 'rose',
            'Geography'            => 'orange',
            'Prinsip Perakaunan'   => 'emerald',
            'Sains Komputer'       => 'blue',
            'Ekonomi'              => 'amber',
            'Perniagaan'           => 'slate',
        ];

        $progress = [];
        foreach ($student->enrollments as $en) {
            $rawName = $en->course->subject->name ?? 'N/A';
            $displayName = $en->course->subject->display_name ?? $rawName;
            $color = $colorMap[$rawName] ?? 'blue';
            $pct = (int) ($en->progress_pct ?? 0);

            // Check if student has taken quizzes in this course
            $quizzes = $student->quizResults()->whereHas('quiz', fn($q) => $q->where('course_id', $en->course_id))->get();
            $quizAvg = $quizzes->isNotEmpty() ? (int) round($quizzes->avg('score')) : null;

            $progress[] = [
                'subject'           => $displayName,
                'emoji'             => '',
                'color'             => $color,
                'pct'               => $pct,
                'quiz_avg'          => $quizAvg,
                'completed_lessons' => (int) ($en->completed_lessons ?? 0),
            ];
        }
        return $progress;
    }

    /**
     * Get upcoming live classes for a student's enrolled courses.
     */
    private function getUpcomingClasses(Student $student, int $limit = 5): Collection
    {
        return StudentDashboardController::getStudentLiveClassesQuery($student)
            ->with('course.subject', 'course.teacher.user')
            ->activeOrUpcoming()
            ->orderBy('scheduled_at')
            ->take($limit)
            ->get();
    }

    /**
     * Get past / completed live classes for a student (with attendance status).
     */
    private function getPastClasses(Student $student, int $limit = 10): Collection
    {
        return StudentDashboardController::getStudentLiveClassesQuery($student)
            ->with('course.subject', 'attendances')
            ->completedOrPast()
            ->orderByDesc('scheduled_at')
            ->take($limit)
            ->get();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PAGES
    // ─────────────────────────────────────────────────────────────────────────

    public function dashboard(Request $request)
    {
        $user     = auth()->user();
        $children = $this->getParentChildren($user);

        $child = $this->getAuthorizedChild($children, $request->query('child_id'));

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
            $childProgress   = $child ? $this->getChildProgress($child) : [];
            $attStats        = $child ? $this->getAttendanceStats($child) : ['total'=>0,'attended'=>0,'absent'=>0,'late'=>0,'pct'=>0];
            $overallProgress = count($childProgress) > 0
                ? (int) round(collect($childProgress)->avg('pct'))
                : 0;

            // Subjects that need attention: only flag if quizzes indicate struggle (< 70%) or active progress is lagging (< 70% after starting)
            $needsAttention = array_filter($childProgress, function($s) {
                if ($s['quiz_avg'] !== null) {
                    return $s['quiz_avg'] < 70;
                }
                return $s['pct'] > 0 && $s['pct'] < 70;
            });

            // Upcoming classes
            $upcomingClasses = $child ? $this->getUpcomingClasses($child, 3) : collect();
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

        $totalOutstanding = (float) $payments->whereIn('status', ['pending', 'overdue'])->sum('amount');
        $pendingCount     = $payments->whereIn('status', ['pending', 'overdue'])->count();
        $outstanding      = $totalOutstanding;

        // ── Last payment made ──
        $lastPaymentMade = $payments->first(function ($p) {
            return in_array($p->status, ['paid', 'successful']);
        });

        // ── Next payment due ──
        $relevantChildIds = $child ? [$child->id] : $children->pluck('id')->filter()->all();
        $nextInvoiceDue   = null;
        if (!empty($relevantChildIds)) {
            $nextInvoiceDue = Invoice::whereIn('student_id', $relevantChildIds)
                ->whereIn('status', Invoice::UNPAID_STATUSES)
                ->with('student.user')
                ->orderBy('due_date', 'asc')
                ->first();

            // Sync total outstanding and pending count if child invoices exist outside payments table
            $childInvoiceOutstanding = (float) Invoice::whereIn('student_id', $relevantChildIds)
                ->whereIn('status', Invoice::UNPAID_STATUSES)
                ->sum(DB::raw('total_amount - amount_paid'));

            if ($totalOutstanding <= 0 && $childInvoiceOutstanding > 0) {
                $totalOutstanding = $childInvoiceOutstanding;
                $outstanding      = $totalOutstanding;
                $pendingCount     = Invoice::whereIn('student_id', $relevantChildIds)
                    ->whereIn('status', Invoice::UNPAID_STATUSES)
                    ->count();
            }
        }

        $nextPaymentDue = null;
        if ($nextInvoiceDue) {
            $isOverdue = $nextInvoiceDue->due_date ? ($nextInvoiceDue->due_date->isPast() && !$nextInvoiceDue->due_date->isToday()) : false;
            $isToday   = $nextInvoiceDue->due_date ? $nextInvoiceDue->due_date->isToday() : false;
            $daysDiff  = $nextInvoiceDue->due_date ? (int) abs(now()->startOfDay()->diffInDays($nextInvoiceDue->due_date->startOfDay())) : null;

            if ($isOverdue) {
                $statusLabel = 'Overdue (' . $daysDiff . ' ' . Str::plural('day', $daysDiff) . ')';
            } elseif ($isToday) {
                $statusLabel = 'Due today';
            } elseif ($daysDiff !== null) {
                $statusLabel = 'Due in ' . $daysDiff . ' ' . Str::plural('day', $daysDiff);
            } else {
                $statusLabel = 'Pending';
            }

            $nextPaymentDue = (object) [
                'type'           => 'invoice',
                'amount'         => (float) $nextInvoiceDue->outstanding_amount,
                'title'          => $nextInvoiceDue->title,
                'invoice_number' => $nextInvoiceDue->invoice_number,
                'due_date'       => $nextInvoiceDue->due_date,
                'student_name'   => $nextInvoiceDue->student?->user?->name,
                'is_overdue'     => $isOverdue,
                'is_today'       => $isToday,
                'status_label'   => $statusLabel,
                'subtext'        => $nextInvoiceDue->student?->user?->name ?? $nextInvoiceDue->invoice_number,
            ];
        } elseif ($pendingP = $payments->whereIn('status', ['pending', 'overdue'])->first()) {
            $isOverdue = $pendingP->status === 'overdue';
            $nextPaymentDue = (object) [
                'type'           => 'payment',
                'amount'         => (float) $pendingP->amount,
                'title'          => $pendingP->description,
                'invoice_number' => $pendingP->reference_no,
                'due_date'       => null,
                'student_name'   => $pendingP->student?->user?->name,
                'is_overdue'     => $isOverdue,
                'is_today'       => false,
                'status_label'   => $isOverdue ? 'Overdue' : 'Pending',
                'subtext'        => $pendingP->reference_no ?? 'Tuition Fee',
            ];
        }

        $latestPayment = $lastPaymentMade;

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
        $children = $this->getParentChildren($user);
        $child    = $this->getAuthorizedChild($children, $request->query('child_id'));

        if ($child && $this->restrictionService->isStudentRestricted($child)) {
            $details = $this->restrictionService->getRestrictionDetails($child);
            return response()->view('parent.child-restricted', compact('children', 'child', 'details'), 403);
        }

        $childProgress   = $child ? $this->getChildProgress($child) : [];
        $overallProgress = count($childProgress) > 0
            ? (int) round(collect($childProgress)->avg('pct'))
            : 0;

        // Subjects that need attention: only flag if quizzes indicate struggle (< 70%) or active progress is lagging (< 70% after starting)
        $needsAttention = array_filter($childProgress, function($s) {
            if ($s['quiz_avg'] !== null) {
                return $s['quiz_avg'] < 70;
            }
            return $s['pct'] > 0 && $s['pct'] < 70;
        });

        return view('parent.progress', compact(
            'children', 'child', 'childProgress', 'overallProgress', 'needsAttention'
        ));
    }

    public function classes(Request $request)
    {
        $user     = auth()->user();
        $children = $this->getParentChildren($user);
        $child    = $this->getAuthorizedChild($children, $request->query('child_id'));

        if ($child && $this->restrictionService->isStudentRestricted($child)) {
            $details = $this->restrictionService->getRestrictionDetails($child);
            return response()->view('parent.child-restricted', compact('children', 'child', 'details'), 403);
        }

        $upcomingClasses = $child ? $this->getUpcomingClasses($child, 10) : collect();
        $nextClass       = $upcomingClasses->first();
        $pastClasses     = $child ? $this->getPastClasses($child, 10) : collect();
        $attStats        = $child ? $this->getAttendanceStats($child) : ['total'=>0,'attended'=>0,'absent'=>0,'late'=>0,'pct'=>0];

        $classesThisWeek = $child
            ? StudentDashboardController::getStudentLiveClassesQuery($child)
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
        $children = $this->getParentChildren($user, ['attendances.liveClass']);
        $child    = $this->getAuthorizedChild($children, $request->query('child_id'));

        if ($child && $this->restrictionService->isStudentRestricted($child)) {
            $details = $this->restrictionService->getRestrictionDetails($child);
            return response()->view('parent.child-restricted', compact('children', 'child', 'details'), 403);
        }

        $attStats = $child ? $this->getAttendanceStats($child) : ['total'=>0,'attended'=>0,'absent'=>0,'late'=>0,'pct'=>0];

        $attendanceLog = $child
            ? StudentAttendance::where('student_id', $child->id)
                ->with('liveClass.course.subject')
                ->orderByDesc('created_at')
                ->take(20)
                ->get()
            : collect();

        $childrenStats = $children->map(function (Student $c) {
            $att = $this->getAttendanceStats($c);
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
        $children = $this->getParentChildren($user);
        $child    = $this->getAuthorizedChild($children, (string) $childId);

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
        $children = $this->getParentChildren($user);
        $child    = $this->getAuthorizedChild($children, (string) $childId);

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
        $children = $this->getParentChildren($user);
        $child    = $this->getAuthorizedChild($children, (string) $childId);

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
        $children = $this->getParentChildren($user);
        $child    = $this->getAuthorizedChild($children, (string) $childId);

        if ($this->restrictionService->isStudentRestricted($child)) {
            $details = $this->restrictionService->getRestrictionDetails($child);
            return response()->view('parent.child-restricted', compact('children', 'child', 'details'), 403);
        }

        return redirect()->route('parent.dashboard', ['child_id' => $child->id]);
    }

    public function storeChild(Request $request)
    {
        $parent = auth()->user();

        $validated = $request->validate([
            'name'             => 'required|string|min:2|max:255',
            'child_ic_number'  => ['required', 'string', 'regex:/^[0-9]{6}-[0-9]{2}-[0-9]{4}$/'],
            'level'            => 'required|string|max:100',
            'relationship'     => 'nullable|string|max:30',
            'school_name'      => 'nullable|string|max:255',
            'password'         => 'nullable|string|min:6|max:100',
            'subjects'         => 'nullable|array',
            'subjects.*'       => 'string|max:255',
        ], [
            'name.required'            => 'Please enter your child\'s full name.',
            'child_ic_number.required' => 'Please enter your child\'s MyKad / IC number.',
            'child_ic_number.regex'    => 'IC number must be in format XXXXXX-XX-XXXX.',
            'level.required'           => 'Please select your child\'s education level.',
        ]);

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
    public function connectChild(Request $request)
    {
        $parent = auth()->user();

        $request->validate([
            'identifier'   => 'required|string|max:255',
            'relationship' => 'nullable|string|max:30',
        ], [
            'identifier.required' => 'Please enter the student\'s MyKad / IC number (e.g. 123456-78-9012) or their registered email address.',
        ]);

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

        // Check if student is already linked via pivot
        $alreadyLinked = DB::table('parent_student')
            ->where('parent_user_id', $parent->id)
            ->where('student_id', $student->id)
            ->exists() || $student->parent_user_id === $parent->id;

        if ($alreadyLinked) {
            return redirect()->back()
                ->with('success', "{$student->user->name} is already linked to your account.");
        }

        // Link the student to this parent
        DB::table('parent_student')->insertOrIgnore([
            'parent_user_id' => $parent->id,
            'student_id'     => $student->id,
            'relationship'   => $relationship,
            'is_primary'     => true,
            'status'         => 'active',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        if (!$student->parent_user_id) {
            $student->update(['parent_user_id' => $parent->id]);
        }

        // Trigger automated official communication
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

        return redirect()->route('parent.dashboard', ['child_id' => $student->id])
            ->with('success', "✓ Successfully linked {$student->user->name} to your parent account!");
    }

    /**
     * Display parent payments, invoices, billing overview and receipts.
     */
    public function payments(Request $request)
    {
        $user     = auth()->user();
        $children = $this->getParentChildren($user);
        $child    = $this->getAuthorizedChild($children, $request->query('child_id'));

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

        $totalPaid         = (float) $payments->whereIn('status', ['paid', 'successful'])->sum('amount');
        $totalOutstanding  = (float) $payments->whereIn('status', ['pending', 'overdue'])->sum('amount');
        $pendingCount      = $payments->whereIn('status', ['pending', 'overdue'])->count();
        $paidCount         = $payments->whereIn('status', ['paid', 'successful'])->count();
        $latestPayment     = $payments->whereIn('status', ['paid', 'successful'])->first();

        // ── Last payment made ──
        $lastPaymentMade = $payments->first(function ($p) {
            return in_array($p->status, ['paid', 'successful']);
        });

        // ── Next payment due ──
        $relevantChildIds = $child ? [$child->id] : $children->pluck('id')->filter()->all();
        $nextInvoiceDue   = null;
        if (!empty($relevantChildIds)) {
            $nextInvoiceDue = Invoice::whereIn('student_id', $relevantChildIds)
                ->whereIn('status', Invoice::UNPAID_STATUSES)
                ->with('student.user')
                ->orderBy('due_date', 'asc')
                ->first();

            // Sync total outstanding and pending count if child invoices exist outside payments table
            $childInvoiceOutstanding = (float) Invoice::whereIn('student_id', $relevantChildIds)
                ->whereIn('status', Invoice::UNPAID_STATUSES)
                ->sum(DB::raw('total_amount - amount_paid'));

            if ($totalOutstanding <= 0 && $childInvoiceOutstanding > 0) {
                $totalOutstanding = $childInvoiceOutstanding;
                $pendingCount     = Invoice::whereIn('student_id', $relevantChildIds)
                    ->whereIn('status', Invoice::UNPAID_STATUSES)
                    ->count();
            }
        }

        $nextPaymentDue = null;
        if ($nextInvoiceDue) {
            $isOverdue = $nextInvoiceDue->due_date ? ($nextInvoiceDue->due_date->isPast() && !$nextInvoiceDue->due_date->isToday()) : false;
            $isToday   = $nextInvoiceDue->due_date ? $nextInvoiceDue->due_date->isToday() : false;
            $daysDiff  = $nextInvoiceDue->due_date ? (int) abs(now()->startOfDay()->diffInDays($nextInvoiceDue->due_date->startOfDay())) : null;

            if ($isOverdue) {
                $statusLabel = 'Overdue (' . $daysDiff . ' ' . Str::plural('day', $daysDiff) . ')';
            } elseif ($isToday) {
                $statusLabel = 'Due today';
            } elseif ($daysDiff !== null) {
                $statusLabel = 'Due in ' . $daysDiff . ' ' . Str::plural('day', $daysDiff);
            } else {
                $statusLabel = 'Pending';
            }

            $nextPaymentDue = (object) [
                'type'           => 'invoice',
                'amount'         => (float) $nextInvoiceDue->outstanding_amount,
                'title'          => $nextInvoiceDue->title,
                'invoice_number' => $nextInvoiceDue->invoice_number,
                'due_date'       => $nextInvoiceDue->due_date,
                'student_name'   => $nextInvoiceDue->student?->user?->name,
                'is_overdue'     => $isOverdue,
                'is_today'       => $isToday,
                'status_label'   => $statusLabel,
                'subtext'        => $nextInvoiceDue->student?->user?->name ?? $nextInvoiceDue->invoice_number,
            ];
        } elseif ($pendingP = $payments->whereIn('status', ['pending', 'overdue'])->first()) {
            $isOverdue = $pendingP->status === 'overdue';
            $nextPaymentDue = (object) [
                'type'           => 'payment',
                'amount'         => (float) $pendingP->amount,
                'title'          => $pendingP->description,
                'invoice_number' => $pendingP->reference_no,
                'due_date'       => null,
                'student_name'   => $pendingP->student?->user?->name,
                'is_overdue'     => $isOverdue,
                'is_today'       => false,
                'status_label'   => $isOverdue ? 'Overdue' : 'Pending',
                'subtext'        => $pendingP->reference_no ?? 'Tuition Fee',
            ];
        }

        // Pending invoices list for quick checkout
        $pendingPayments = $payments->whereIn('status', ['pending', 'overdue'])->values();

        return view('parent.payments', compact(
            'children', 'child', 'payments', 'totalPaid', 'totalOutstanding',
            'pendingCount', 'paidCount', 'latestPayment', 'pendingPayments',
            'lastPaymentMade', 'nextPaymentDue'
        ));
    }

    /**
     * Process simulated / gateway payment for invoices.
     */
    public function processPayment(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'payment_ids'    => 'required',
            'payment_method' => 'required|string|max:100',
            'bank_name'      => 'nullable|string|max:100',
        ]);

        $paymentIds = is_array($validated['payment_ids'])
            ? $validated['payment_ids']
            : explode(',', (string) $validated['payment_ids']);

        $paymentsToPay = Payment::where('user_id', $user->id)
            ->whereIn('id', $paymentIds)
            ->whereIn('status', ['pending', 'overdue'])
            ->get();

        if ($paymentsToPay->isEmpty()) {
            return redirect()->back()->withErrors(['payment_ids' => 'No eligible pending invoices found for payment.']);
        }

        $methodTitle = $validated['payment_method'];
        if (!empty($validated['bank_name'])) {
            $methodTitle .= ' (' . $validated['bank_name'] . ')';
        }

        $totalPaidAmount = 0;
        foreach ($paymentsToPay as $p) {
            $txnRef = 'OS-TXN-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            $p->update([
                'status'         => 'paid',
                'payment_method' => $methodTitle,
                'reference_no'   => $txnRef,
                'paid_at'        => now(),
            ]);
            $totalPaidAmount += (float) $p->amount;
        }

        $formattedTotal = number_format($totalPaidAmount, 2);

        return redirect()->route('parent.payments')
            ->with('success', "✓ Payment of RM {$formattedTotal} completed successfully via {$methodTitle}! Official receipt generated.");
    }

    /**
     * Parent Children Fees & Billing Hub.
     */
    public function fees(Request $request)
    {
        $parent = auth()->user();
        $children = $this->getParentChildren($parent);

        if ($children->isEmpty()) {
            return view('parent.fees', [
                'children'               => collect(),
                'selectedChild'          => null,
                'invoices'               => collect(),
                'outstandingInvoices'    => collect(),
                'paidInvoices'           => collect(),
                'payments'               => collect(),
                'totalFamilyOutstanding' => 0,
                'bankDetails'            => [],
            ]);
        }

        $selectedChildId = $request->query('child_id', $children->first()->id);
        $selectedChild = $children->firstWhere('id', $selectedChildId) ?? $children->first();

        // Sync and ensure dynamic invoices for each child
        foreach ($children as $child) {
            Invoice::syncStudentInvoices($child);
        }

        // All child IDs linked to this parent
        $allChildStudentIds = $children->pluck('id');

        $allFamilyInvoices = Invoice::whereIn('student_id', $allChildStudentIds)
            ->with(['student.user', 'feeStructure', 'course.subject'])
            ->latest('due_date')
            ->get();

        $totalFamilyOutstanding = $allFamilyInvoices->where('status', 'outstanding')->sum('total_amount');

        // Current selected child's invoices
        $invoices = $allFamilyInvoices->where('student_id', $selectedChild->id)->values();
        $outstandingInvoices = $invoices->filter(fn($inv) => in_array($inv->status, ['outstanding', 'pending_verification']))->values();
        $paidInvoices        = $invoices->filter(fn($inv) => $inv->status === 'paid')->values();

        $payments = Payment::where('student_id', $selectedChild->id)
            ->with(['invoice', 'payer'])
            ->latest('id')
            ->get();

        $bankDetails = [
            'bank_name'      => Setting::get('company_bank_name', 'Maybank Islamic Berhad'),
            'account_name'   => Setting::get('company_bank_account_name', 'Olympia Scholars Academy Sdn Bhd'),
            'account_number' => Setting::get('company_bank_account_number', '5642 8901 2345'),
            'instructions'   => Setting::get('company_bank_instructions', 'Please include your Child Name & Invoice No in the transfer reference.'),
        ];

        return view('parent.fees', compact(
            'children',
            'selectedChild',
            'invoices',
            'outstandingInvoices',
            'paidInvoices',
            'payments',
            'totalFamilyOutstanding',
            'bankDetails'
        ));
    }

    /**
     * Process child invoice payment by parent.
     */
    public function payChildInvoice(Request $request, $id)
    {
        $parent = auth()->user();
        $children = $this->getParentChildren($parent);
        $childIds = $children->pluck('id')->toArray();

        $invoice = Invoice::whereIn('student_id', $childIds)->with('student.user')->findOrFail($id);

        if ($invoice->status === 'paid') {
            return back()->with('info', 'This invoice has already been fully paid.');
        }

        $paymentMethod = $request->input('payment_method', 'fpx');

        if ($paymentMethod === 'bank_transfer') {
            $request->validate([
                'proof_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            ]);

            $proofPath = $request->file('proof_file')->store('payment_proofs', 'public');
            $refNo = 'TXN-TRF-' . strtoupper(substr(md5(uniqid()), 0, 8));

            Payment::create([
                'user_id'         => $parent->id,
                'invoice_id'      => $invoice->id,
                'student_id'      => $invoice->student_id,
                'payer_id'        => $parent->id,
                'payer_type'      => 'parent',
                'description'     => "Payment for {$invoice->student->user->name} – {$invoice->title} (Bank Transfer Slip Uploaded)",
                'amount'          => $invoice->total_amount,
                'payment_method'  => 'bank_transfer',
                'proof_file_path' => $proofPath,
                'reference_no'    => $refNo,
                'status'          => 'pending',
                'paid_at'         => now(),
            ]);

            $invoice->update(['status' => 'pending_verification']);

            return back()->with('success', "Payment proof for {$invoice->student->user->name} submitted! Your payment is pending verification.");
        } else {
            // Billplz Online Banking Gateway
            $billplzService = new \App\Services\Billplz\BillplzService();
            if ($billplzService->getApiKey()) {
                try {
                    /** @var \App\Services\PaymentService $paymentService */
                    $paymentService = app(\App\Services\PaymentService::class);
                    $result = $paymentService->initiateBillplzPayment([$invoice->id], $parent);
                    return redirect()->away($result['bill_url']);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('[ParentDashboardController] Billplz checkout error: ' . $e->getMessage());
                    return back()->with('error', 'Online payment gateway error: ' . $e->getMessage());
                }
            }

            // Mock fallback if gateway credentials not set
            $refNo = 'TXN-FPX-' . strtoupper(substr(md5(uniqid()), 0, 8));
            $receiptNo = 'RCP-' . date('Y') . '-' . str_pad($invoice->id * 10 + rand(1, 9), 5, '0', STR_PAD_LEFT);

            Payment::create([
                'user_id'        => $parent->id,
                'invoice_id'     => $invoice->id,
                'student_id'     => $invoice->student_id,
                'payer_id'       => $parent->id,
                'payer_type'     => 'parent',
                'description'    => "Payment for {$invoice->student->user->name} – {$invoice->title}",
                'amount'         => $invoice->total_amount,
                'payment_method' => $paymentMethod,
                'reference_no'   => $refNo,
                'receipt_number' => $receiptNo,
                'status'         => 'successful',
                'paid_at'        => now(),
            ]);

            $invoice->update(['status' => 'paid']);

            \App\Models\UserNotification::create([
                'user_id' => $parent->id,
                'type'    => 'payment',
                'title'   => 'PAYMENT SUCCESSFUL',
                'message' => "Payment of RM " . number_format($invoice->total_amount, 2) . " for {$invoice->student->user->name} ('{$invoice->title}') has been processed. Receipt No: {$receiptNo}.",
                'read_at' => null,
            ]);

            if ($invoice->student->user_id) {
                \App\Models\UserNotification::create([
                    'user_id' => $invoice->student->user_id,
                    'type'    => 'payment',
                    'title'   => 'TUITION FEE PAID BY PARENT',
                    'message' => "Your parent ({$parent->name}) has completed the payment for '{$invoice->title}'. Receipt No: {$receiptNo}.",
                    'read_at' => null,
                ]);
            }

            return back()->with('success', "Payment for {$invoice->student->user->name} was successful! Receipt #{$receiptNo} has been generated.");
        }
    }

    /**
     * Create a manual custom tuition or materials fee invoice.
     */
    public function createCustomInvoice(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount'      => 'required|numeric|min:1|max:10000',
            'course_id'   => 'nullable|exists:courses,id',
        ]);

        $invoiceRef = 'OS-INV-' . date('Ym') . '-' . strtoupper(Str::random(5));

        Payment::create([
            'user_id'      => $user->id,
            'course_id'    => $validated['course_id'] ?? null,
            'description'  => $validated['description'],
            'amount'       => $validated['amount'],
            'status'       => 'pending',
            'reference_no' => $invoiceRef,
            'created_at'   => now(),
        ]);

        return redirect()->route('parent.payments')
            ->with('success', "✓ New tuition invoice #{$invoiceRef} created successfully.");
    }
}


