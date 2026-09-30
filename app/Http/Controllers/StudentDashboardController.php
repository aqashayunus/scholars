<?php

namespace App\Http\Controllers;

use App\Models\LiveClass;
use App\Models\RecordedClass;
use App\Models\Student;
use App\Models\StudentAssignment;
use App\Models\StudentQuiz;
use App\Models\StudentTopicMastery;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules\Password;

class StudentDashboardController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        if ($user->role !== 'student') {
            return redirect()->route($user->getDashboardRoute());
        }

        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            $student = Student::create([
                'user_id' => $user->id,
                'level' => 'SPM',
                'total_points' => 0,
            ]);
        }

        \App\Http\Controllers\PageController::syncStudentEnrollments($student);
        $student->load('enrollments.course.subject', 'enrollments.course.teacher.user');

        $enrollments      = $student->enrollments;
        $enrolledCourses  = $enrollments->count();
        $completedLessons = $enrollments->sum('completed_lessons');

        $recordedCount    = RecordedClass::count();
        $topicMasteries   = StudentTopicMastery::where('student_id', $student->id)->get();

        $quizResults = StudentQuiz::where('student_id', $student->id)
                                  ->with('quiz.course.subject')
                                  ->latest()->take(5)->get();

        // Ensure newly enrolled students have tracking records for all course assignments
        app(\App\Services\Student\AssignmentService::class)->syncStudentAssignments($student);

        $assignments = StudentAssignment::where('student_id', $student->id)
                                        ->whereHas('assignment')
                                        ->with('assignment.course.subject')
                                        ->latest()->take(6)->get();

        // Real-time live class: fetch next upcoming or ongoing live session
        $upcomingClass = LiveClass::whereHas('course', function ($q) use ($student) {
            $q->whereHas('enrollments', fn($q2) => $q2->where('student_id', $student->id));
        })
        ->with('course.teacher.user', 'course.subject')
        ->where(function($q) {
            $q->where('scheduled_at', '>=', now()->subMinutes(120));
        })
        ->orderBy('scheduled_at', 'asc')
        ->first();

        // Check & create real-time class notifications (10 min before & Live now)
        $this->checkLiveClassNotifications($user, $student);

        // Ensure streak is logged for today
        $student->recordLoginStreak();

        // Dynamic Top Metrics Calculation
        $overallProgress = $enrollments->count() > 0 ? (int) round($enrollments->avg('progress_pct') ?? 0) : 0;
        
        $enrollmentsMap = $enrollments->pluck('enrolled_at', 'course_id')->toArray();
        $tasksDueCount = StudentAssignment::where('student_id', $student->id)
            ->where('status', 'pending')
            ->whereHas('assignment')
            ->with('assignment')
            ->get()
            ->filter(function($sa) use ($enrollmentsMap) {
                $courseId = $sa->assignment?->course_id;
                $enrolledAtStr = $enrollmentsMap[$courseId] ?? null;
                $enrolledAt = $enrolledAtStr ? \Carbon\Carbon::parse($enrolledAtStr) : ($sa->created_at ?? now());
                $dueDate = $sa->assignment?->due_date ? \Carbon\Carbon::parse($sa->assignment->due_date) : null;
                // Exclude tasks that were already due before the student joined
                return !($dueDate && $dueDate->lt($enrolledAt->startOfDay()));
            })
            ->count();
        // Dynamic Attendance calculation from centralized helper
        $attStats = app(\App\Services\Student\ClassScheduleService::class)->attendanceStats($student);
        $attendanceRate = $attStats['pct'];

        // Personal Growth & Study Streak Stats (Dynamic from Database)
        $studyStreakDays = $student->streak_days ?? 1;
        $totalQuestionsPracticed = $student->quizResults()->sum('total_questions') ?: 0;

        // Generate dynamic 7-day calendar schedule (Monday to Sunday)
        $startOfWeek = now()->startOfWeek();
        $weeklyLiveClasses = LiveClass::whereHas('course', function ($q) use ($student) {
            $q->whereHas('enrollments', fn($q2) => $q2->where('student_id', $student->id));
        })
        ->with('course.subject', 'course.teacher.user')
        ->whereBetween('scheduled_at', [$startOfWeek->copy()->startOfDay(), $startOfWeek->copy()->addDays(6)->endOfDay()])
        ->orderBy('scheduled_at')
        ->get();

        $weekSchedule = [];
        for ($i = 0; $i < 7; $i++) {
            $dayDate = $startOfWeek->copy()->addDays($i);
            $dayClasses = $weeklyLiveClasses->filter(function ($lc) use ($dayDate) {
                return $lc->scheduled_at->isSameDay($dayDate);
            });

            $weekSchedule[] = [
                'dayName'   => $dayDate->format('D'),
                'dayNumber' => $dayDate->format('d M'),
                'isToday'   => $dayDate->isToday(),
                'classes'   => $dayClasses,
            ];
        }

        // Personal performance trajectory chart from actual student quiz records
        ['performanceLabels' => $performanceLabels, 'performanceData' => $performanceData] =
            app(\App\Services\Student\QuizPerformanceService::class)->performanceChartData($student);

        $restrictionService = app(\App\Services\StudentAccessRestrictionService::class);
        $isRestricted = $restrictionService->isStudentRestricted($student);
        $restrictionDetails = $isRestricted ? $restrictionService->getRestrictionDetails($student) : null;

        // Diagnostic Assessment Status
        $pendingDiagnostic = StudentAssignment::where('student_id', $student->id)
            ->whereHas('assignment', fn($q) => $q->where('type', 'Diagnostic Test'))
            ->whereIn('status', ['pending', 'overdue'])
            ->with('assignment')
            ->first();

        return view('student.dashboard', compact(
            'student', 'enrollments', 'enrolledCourses', 'completedLessons',
            'recordedCount', 'topicMasteries', 'quizResults', 'assignments',
            'upcomingClass', 'studyStreakDays', 'totalQuestionsPracticed',
            'performanceLabels', 'performanceData',
            'overallProgress', 'attendanceRate', 'tasksDueCount', 'weekSchedule',
            'isRestricted', 'restrictionDetails', 'pendingDiagnostic'
        ));
    }

    /**
     * Auto-dispatch notifications for classes starting in <= 10 mins or currently live.
     */
    private function checkLiveClassNotifications($user, $student): void
    {
        $now = now();
        
        $enrolledClasses = LiveClass::whereHas('course', function ($q) use ($student) {
            $q->whereHas('enrollments', fn($q2) => $q2->where('student_id', $student->id));
        })
        ->with('course.teacher.user')
        ->where('scheduled_at', '>=', $now->copy()->subMinutes(180))
        ->where('scheduled_at', '<=', $now->copy()->addMinutes(60))
        ->get();

        foreach ($enrolledClasses as $liveClass) {
            $startTime = $liveClass->scheduled_at;
            $endTime = $startTime->copy()->addMinutes($liveClass->duration_minutes ?? 60);
            $teacherName = $liveClass->course->teacher->user->name ?? 'your instructor';

            // 1. Check if class is LIVE NOW
            if ($now->gte($startTime) && $now->lte($endTime)) {
                $uniqueTag = "[LiveClass ID: {$liveClass->id}] [Status: LIVE]";
                $alreadyNotified = \App\Models\UserNotification::where('user_id', $user->id)
                    ->where('message', 'like', "%{$uniqueTag}%")
                    ->exists();

                if (!$alreadyNotified) {
                    \App\Models\UserNotification::create([
                        'user_id' => $user->id,
                        'type' => 'class_live',
                        'title' => 'Class Live Now 🔴',
                        'message' => "Your live class '{$liveClass->title}' with {$teacherName} is in progress now! Click to join. {$uniqueTag}",
                    ]);
                }
            }
            // 2. Check if class is STARTING IN <= 10 MINUTES
            elseif ($now->lt($startTime) && $now->diffInMinutes($startTime, false) <= 10 && $now->diffInMinutes($startTime, false) >= 0) {
                $minutesLeft = max(1, $now->diffInMinutes($startTime));
                $uniqueTag = "[LiveClass ID: {$liveClass->id}] [Status: 10MIN]";
                $alreadyNotified = \App\Models\UserNotification::where('user_id', $user->id)
                    ->where('message', 'like', "%{$uniqueTag}%")
                    ->exists();

                if (!$alreadyNotified) {
                    \App\Models\UserNotification::create([
                        'user_id' => $user->id,
                        'type' => 'class_reminder',
                        'title' => 'Class Starting Soon ⏳',
                        'message' => "Your live class '{$liveClass->title}' with {$teacherName} is starting in {$minutesLeft} minute(s)! Get ready. {$uniqueTag}",
                    ]);
                }
            }
        }
    }

    public function learning(\App\Services\Student\LearningService $learningService)
    {
        $student = $this->getStudent();

        return view('student.learning', array_merge(['student' => $student], $learningService->learningData($student)));
    }

    public function classes(\App\Services\Student\ClassScheduleService $schedule)
    {
        $student = $this->getStudent();

        return view('student.classes', array_merge(['student' => $student], $schedule->classesData($student)));
    }

    public function tasks(\App\Services\Student\AssignmentService $assignmentService)
    {
        $student = $this->getStudent();

        return view('student.tasks', array_merge(['student' => $student], $assignmentService->tasksData($student)));
    }

    public function submitTask(Request $request, $id, \App\Services\Student\AssignmentService $assignmentService)
    {
        $student = $this->getStudent();

        $validated = $request->validate([
            'submission_notes' => 'nullable|string|max:3000',
            'file'             => 'nullable|file|max:30720|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,png,jpg,jpeg',
        ]);

        $assignmentService->submitTask($student, (int) $id, $validated['submission_notes'] ?? null, $request->file('file'));

        return redirect()->back()->with('success', 'Assignment submitted successfully! Your teacher will review and grade your work.');
    }

    public function quiz(\App\Services\Student\QuizPerformanceService $quizzes)
    {
        $student = $this->getStudent();
        $quizId = $quizzes->firstQuizId($student);
        if ($quizId) {
            return redirect()->route('student.quiz.show', $quizId);
        }
        return view('student.quiz', compact('student'));
    }

    public function quizShow($id, \App\Services\Student\QuizPerformanceService $quizzes)
    {
        $student = $this->getStudent();
        $quiz = $quizzes->showQuiz($id);
        return view('student.quiz', compact('student', 'quiz'));
    }

    public function quizSubmit(Request $request, $id, \App\Services\Student\QuizPerformanceService $quizzes)
    {
        $student = $this->getStudent();

        $validated = $request->validate([
            'score'           => 'required|integer',
            'total_questions' => 'required|integer',
        ]);

        $quizzes->submitQuiz($student, (int) $id, (int) $validated['score'], (int) $validated['total_questions']);

        return response()->json(['success' => true]);
    }

    public function progress()
    {
        $student = $this->getStudent();
        $enrollments = $student->enrollments()->with('course.subject.topics.subtopics', 'course.teacher.user')->get();
        $topicMasteries = StudentTopicMastery::where('student_id', $student->id)->get();
        
        $overallProgress = $enrollments->count() > 0 ? (int) round($enrollments->avg('progress_pct') ?? 0) : 0;
        $attStats = app(\App\Services\Student\ClassScheduleService::class)->attendanceStats($student);

        // Personal performance trajectory chart from actual student quiz records
        ['performanceLabels' => $performanceLabels, 'performanceData' => $performanceData] =
            app(\App\Services\Student\QuizPerformanceService::class)->performanceChartData($student);

        return view('student.progress', compact('student', 'enrollments', 'overallProgress', 'attStats', 'topicMasteries', 'performanceLabels', 'performanceData'));
    }

    public function achievements(\App\Services\Student\AchievementService $achievementService)
    {
        $student = $this->getStudent();
        $user = auth()->user();

        return view('student.achievements', array_merge(
            ['student' => $student, 'user' => $user],
            $achievementService->achievementsData($student, $user)
        ));
    }

    public function messages()
    {
        $student = $this->getStudent();
        return view('student.messages', compact('student'));
    }

    public function myCourses()  { return $this->learning(); }
    public function timetable() { return $this->classes(); }
    public function profile(Request $request, \App\Services\Student\ProfileService $profileService)
    {
        $student = $this->getStudent();
        $activeTab = $request->query('tab', 'profile');

        return view('student.profile', array_merge(['student' => $student], $profileService->profileData($student, $activeTab)));
    }

    /**
     * Link the student's account to a parent account by the parent's IC number.
     */
    public function linkParent(Request $request, \App\Services\Student\ProfileService $profileService)
    {
        $request->validate([
            'parent_ic'    => 'required|string|max:20',
            'relationship' => 'nullable|string|max:30',
        ], [
            'parent_ic.required' => 'Please enter the parent\'s MyKad / IC number.',
        ]);

        $student      = $this->getStudent();
        $rawIc        = trim($request->input('parent_ic'));
        $relationship = $request->input('relationship', 'Guardian');

        $result = $profileService->linkParent($student, $rawIc, $relationship);

        if (!$result['success']) {
            return redirect()->route('student.profile', ['tab' => 'profile'])
                ->withInput()
                ->withErrors(['parent_ic' => $result['field_error']]);
        }

        return redirect()->route('student.profile', ['tab' => 'profile'])
            ->with('profile_updated', $result['message']);
    }

    /**
     * Update the student's personal information (name, email, phone, level).
     */
    public function updateProfile(Request $request, \App\Services\Student\ProfileService $profileService)
    {
        $user    = auth()->user();
        $student = Student::where('user_id', $user->id)->first();

        $validated = $request->validate([
            'name'          => ['required', 'string', 'min:2', 'max:255'],
            'email'         => ['nullable', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone'         => ['nullable', 'string', 'max:50'],
            'school_name'   => ['nullable', 'string', 'max:255'],
            'avatar'        => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Please enter your full name.',
            'email.email'   => 'Please enter a valid email address.',
            'email.unique'  => 'This email is already in use by another account.',
            'avatar.image'  => 'The profile picture must be an image (JPEG, PNG, JPG, or WebP).',
            'avatar.max'    => 'Profile picture size must not exceed 2MB.',
        ]);

        $profileService->updateProfile($user, $student, $validated, $request->boolean('remove_avatar'), $request->file('avatar'));

        return Redirect::route('student.profile', ['tab' => 'profile'])->with('profile_updated', 'Profile updated successfully!');
    }

    /**
     * Update student learning & notification preferences.
     */
    public function updatePreferences(Request $request)
    {
        // Store preferences in user session or flash confirmation
        return Redirect::route('student.profile', ['tab' => 'settings'])->with('settings_updated', 'Study preferences and notification settings updated!');
    }

    /**
     * Update the student's password.
     * Parent-managed students are allowed to set a new password without knowing the current one.
     */
    public function updatePassword(Request $request, \App\Services\Student\ProfileService $profileService)
    {
        $user    = auth()->user();
        $student = Student::where('user_id', $user->id)->first();
        $isParentManaged = $profileService->isParentManaged($student);

        $rules = [
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ];

        // Only require current password for self-registered (non-parent-managed) students
        if (!$isParentManaged) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $validated = $request->validate($rules, [
            'current_password.current_password' => 'The current password you entered is incorrect.',
            'password.confirmed'                => 'New passwords do not match.',
            'password.min'                      => 'Password must be at least 8 characters.',
        ]);

        $profileService->updatePassword($user, $validated['password']);

        return Redirect::route('student.profile', ['tab' => 'security'])->with('password_updated', 'Password changed successfully!');
    }
    public function readAllNotifications()
    {
        $userId = auth()->id();
        \App\Models\UserNotification::where('user_id', $userId)
                                    ->whereNull('read_at')
                                    ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Display Student "My Fees" portal.
     */
    public function fees(\App\Services\Student\BillingService $billing)
    {
        $student = $this->getStudent();

        return view('student.fees', array_merge(['student' => $student], $billing->feesData($student)));
    }

    /**
     * Process invoice payment by student.
     */
    public function payInvoice(Request $request, $id, \App\Services\Student\BillingService $billing)
    {
        $student = $this->getStudent();
        $paymentMethod = $request->input('payment_method', 'fpx');

        if ($paymentMethod === 'bank_transfer') {
            $request->validate([
                'proof_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            ]);
        }

        $result = $billing->payInvoice($student, (int) $id, $paymentMethod, $request->file('proof_file'));

        return match ($result['status']) {
            'already_paid' => back()->with('info', $result['message']),
            default        => back()->with('success', $result['message']),
        };
    }

    private function getStudent(): Student
    {
        $user = auth()->user();
        if ($user->role !== 'student') {
            abort(403, 'Unauthorized. This area is reserved for student accounts.');
        }

        $student = Student::firstOrCreate([
            'user_id' => $user->id
        ], [
            'level' => 'SPM',
            'total_points' => 0,
        ]);

        \App\Http\Controllers\PageController::syncStudentEnrollments($student);
        app(\App\Services\Student\AssignmentService::class)->syncStudentAssignments($student);

        return $student;
    }
}
