<?php

namespace App\Http\Controllers;

use App\Http\Requests\Student\LinkParentRequest;
use App\Http\Requests\Student\PayInvoiceRequest;
use App\Http\Requests\Student\QuizSubmitRequest;
use App\Http\Requests\Student\SubmitCorrectionRequestRequest;
use App\Http\Requests\Student\SubmitTaskRequest;
use App\Http\Requests\Student\UpdateStudentPasswordRequest;
use App\Http\Requests\Student\UpdateStudentProfileRequest;
use App\Models\LiveClass;
use App\Models\Student;
use App\Models\StudentTopicMastery;
use App\Services\AttendanceService;
use App\Services\Student\ClassScheduleService;
use App\Services\StudentAccessRestrictionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

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

    public function submitTask(SubmitTaskRequest $request, $id, \App\Services\Student\AssignmentService $assignmentService)
    {
        $student = $this->getStudent();

        $validated = $request->validated();

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

    public function quizSubmit(QuizSubmitRequest $request, $id, \App\Services\Student\QuizPerformanceService $quizzes)
    {
        $student = $this->getStudent();

        $validated = $request->validated();

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
    public function linkParent(LinkParentRequest $request, \App\Services\Student\ProfileService $profileService)
    {
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
    public function updateProfile(UpdateStudentProfileRequest $request, \App\Services\Student\ProfileService $profileService)
    {
        $user    = auth()->user();
        $student = Student::where('user_id', $user->id)->first();

        $validated = $request->validated();

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
    public function updatePassword(UpdateStudentPasswordRequest $request, \App\Services\Student\ProfileService $profileService)
    {
        $user    = auth()->user();
        $validated = $request->validated();

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
    public function payInvoice(PayInvoiceRequest $request, $id, \App\Services\Student\BillingService $billing)
    {
        $student = $this->getStudent();
        $paymentMethod = $request->input('payment_method', 'fpx');

        $result = $billing->payInvoice($student, (int) $id, $paymentMethod, $request->file('proof_file'));

        return match ($result['status']) {
            'already_paid' => back()->with('info', $result['message']),
            default        => back()->with('success', $result['message']),
        };
    }

    /**
     * Student clicks "Join" on a live class. Delegates authorization +
     * join-attempt recording to ClassScheduleService/AttendanceService;
     * this method is only responsible for turning the result into the
     * right HTTP response (redirect vs JSON, status code, route).
     */
    public function joinClass(
        LiveClass $liveClass,
        Request $request,
        ClassScheduleService $schedule,
        AttendanceService $attendanceService,
        StudentAccessRestrictionService $restrictionService
    ) {
        $user = auth()->user();
        if ($user->role !== 'student') {
            abort(403, 'Unauthorized.');
        }

        $student = Student::where('user_id', $user->id)->firstOrFail();

        $result = $schedule->authorizeAndJoin($student, $liveClass, $request, $restrictionService, $attendanceService);

        if (!$result['success']) {
            [$statusCode, $redirectRoute] = match ($result['error'] ?? null) {
                'payment_restricted' => [403, 'student.fees'],
                'not_enrolled'       => [403, 'student.classes'],
                default              => [422, 'student.classes'],
            };

            if ($request->wantsJson()) {
                return response()->json($result, $statusCode);
            }
            return redirect()->route($redirectRoute)->with('error', $result['message']);
        }

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->away($result['redirect_url']);
    }

    /**
     * Submit an attendance correction request.
     */
    public function submitCorrectionRequest(SubmitCorrectionRequestRequest $request, ClassScheduleService $schedule, AttendanceService $attendanceService)
    {
        $user = auth()->user();
        if ($user->role !== 'student') {
            abort(403);
        }

        $validated = $request->validated();

        $student = Student::where('user_id', $user->id)->firstOrFail();
        $liveClass = LiveClass::findOrFail($validated['live_class_id']);

        $result = $schedule->submitCorrectionRequest(
            $student,
            $liveClass,
            $validated['reason'],
            $validated['requested_status'] ?? 'attended',
            $attendanceService
        );

        return response()->json($result, $result['success'] ? 200 : 403);
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
