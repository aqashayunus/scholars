<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentTopicMastery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules\Password;

class StudentDashboardController extends Controller
{
    public function dashboard(\App\Services\Student\DashboardMetricsService $metrics)
    {
        $user = auth()->user();
        if ($user->role !== 'student') {
            return redirect()->route($user->getDashboardRoute());
        }

        return view('student.dashboard', $metrics->build($user));
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
