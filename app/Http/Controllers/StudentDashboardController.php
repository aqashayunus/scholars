<?php

namespace App\Http\Controllers;

use App\Http\Requests\Student\LinkParentRequest;
use App\Http\Requests\Student\QuizSubmitRequest;
use App\Http\Requests\Student\SubmitCorrectionRequestRequest;
use App\Http\Requests\Student\SubmitTaskRequest;
use App\Http\Requests\Student\UpdateStudentPasswordRequest;
use App\Http\Requests\Student\UpdateStudentProfileRequest;
use App\Models\LiveClass;
use App\Models\Student;
use App\Models\StudentTopicMastery;
use App\Models\UserNotification;
use App\Services\AttendanceService;
use App\Services\RenewalService;
use App\Services\Student\AchievementService;
use App\Services\Student\AssignmentService;
use App\Services\Student\BillingService;
use App\Services\Student\ClassScheduleService;
use App\Services\Student\DashboardMetricsService;
use App\Services\Student\LearningService;
use App\Services\Student\ProfileService;
use App\Services\Student\QuizPerformanceService;
use App\Services\StudentAccessRestrictionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class StudentDashboardController extends Controller
{
    public function dashboard(DashboardMetricsService $metrics)
    {
        $user = auth()->user();
        if ($user->role !== 'student') {
            return redirect()->route($user->getDashboardRoute());
        }

        return view('student.dashboard', $metrics->build($user));
    }

    /**
     * ?tab=practice&course=12 opens a given tab on a given class, so quiz
     * links (notifications, "Back" from a quiz) land on Practice drills.
     */
    public function learning(Request $request, LearningService $learningService)
    {
        $student = $this->getStudent();
        $data = $learningService->learningData($student);

        $tab = $request->query('tab');
        $initialTab = in_array($tab, ['overview', 'lessons', 'practice', 'recordings'], true) ? $tab : 'overview';
        $courseId = (int) $request->query('course');
        $initialSubject = $data['subjectsData'][0]['name'] ?? 'Mathematics';
        foreach ($data['subjectsData'] as $subjectRow) {
            if ((int) $subjectRow['course_id'] === $courseId) {
                $initialSubject = $subjectRow['name'];
                break;
            }
        }

        return view('student.learning', array_merge(['student' => $student, 'initialTab' => $initialTab, 'initialSubject' => $initialSubject], $data));
    }

    public function classes(ClassScheduleService $schedule)
    {
        $student = $this->getStudent();

        return view('student.classes', array_merge(['student' => $student], $schedule->classesData($student)));
    }

    public function tasks(AssignmentService $assignmentService)
    {
        $student = $this->getStudent();

        return view('student.tasks', array_merge(['student' => $student], $assignmentService->tasksData($student)));
    }

    public function submitTask(SubmitTaskRequest $request, $id, AssignmentService $assignmentService)
    {
        $student = $this->getStudent();

        $validated = $request->validated();

        $assignmentService->submitTask($student, (int) $id, $validated['submission_notes'] ?? null, $request->file('file'));

        return redirect()->back()->with('success', 'Assignment submitted successfully! Your teacher will review and grade your work.');
    }

    public function quiz(QuizPerformanceService $quizzes)
    {
        // Quizzes live in My Learning > Practice drills. Notifications carry
        // no link, so open the class of the newest quiz (the likely subject).
        $student = $this->getStudent();
        $courseId = $quizzes->visibleQuizzes($student)->latest('id')->value('course_id');

        return redirect()->route('student.learning', array_filter(['tab' => 'practice', 'course' => $courseId]));
    }

    public function quizShow($id, QuizPerformanceService $quizzes)
    {
        $student = $this->getStudent();
        $quiz = $quizzes->showQuiz($student, (int) $id);

        if ($quiz->questions->isEmpty()) {
            return redirect()->route('student.learning', ['tab' => 'practice', 'course' => $quiz->course_id])
                ->with('quiz_notice', "\"{$quiz->title}\" has no questions yet. Your teacher is still preparing it.");
        }

        return view('student.quiz', compact('student', 'quiz'));
    }

    public function quizSubmit(QuizSubmitRequest $request, $id, QuizPerformanceService $quizzes)
    {
        $student = $this->getStudent();

        $result = $quizzes->submitQuiz($student, (int) $id, $request->validated()['answers'] ?? []);

        return response()->json(['success' => true] + $result);
    }

    public function progress()
    {
        $student = $this->getStudent();
        $enrollments = $student->enrollments()->with('course.subject.topics.subtopics', 'course.teacher.user')->get();
        $topicMasteries = StudentTopicMastery::where('student_id', $student->id)->get();

        $overallProgress = $enrollments->count() > 0 ? (int) round($enrollments->avg('progress_pct') ?? 0) : 0;
        $attStats = app(ClassScheduleService::class)->attendanceStats($student);

        // Personal performance trajectory chart from actual student quiz records
        ['performanceLabels' => $performanceLabels, 'performanceData' => $performanceData] =
            app(QuizPerformanceService::class)->performanceChartData($student);

        return view('student.progress', compact('student', 'enrollments', 'overallProgress', 'attStats', 'topicMasteries', 'performanceLabels', 'performanceData'));
    }

    public function achievements(AchievementService $achievementService)
    {
        $student = $this->getStudent();
        $user = auth()->user();

        return view('student.achievements', array_merge(
            ['student' => $student, 'user' => $user],
            $achievementService->achievementsData($student, $user)
        ));
    }

    public function timetable()
    {
        return $this->classes();
    }

    public function profile(Request $request, ProfileService $profileService)
    {
        $student = $this->getStudent();
        $activeTab = $request->query('tab', 'profile');

        return view('student.profile', array_merge(
            ['student' => $student, 'subscription' => app(RenewalService::class)->panel($student)],
            $profileService->profileData($student, $activeTab)
        ));
    }

    /**
     * Link the student's account to a parent account by the parent's IC number.
     */
    public function linkParent(LinkParentRequest $request, ProfileService $profileService)
    {
        $student = $this->getStudent();
        $rawIc = trim($request->input('parent_ic'));
        $relationship = $request->input('relationship', 'Guardian');

        $result = $profileService->linkParent($student, $rawIc, $relationship);

        if (! $result['success']) {
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
    public function updateProfile(UpdateStudentProfileRequest $request, ProfileService $profileService)
    {
        $user = auth()->user();
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
    public function updatePassword(UpdateStudentPasswordRequest $request, ProfileService $profileService)
    {
        $user = auth()->user();
        $validated = $request->validated();

        $profileService->updatePassword($user, $validated['password']);

        return Redirect::route('student.profile', ['tab' => 'security'])->with('password_updated', 'Password changed successfully!');
    }

    public function readAllNotifications()
    {
        $userId = auth()->id();
        UserNotification::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Display Student "My Fees" portal.
     */
    public function fees(BillingService $billing)
    {
        $student = $this->getStudent();

        return view('student.fees', array_merge(['student' => $student], $billing->feesData($student)));
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

        if (! $result['success']) {
            [$statusCode, $redirectRoute] = match ($result['error'] ?? null) {
                'payment_restricted' => [403, 'student.fees'],
                'not_enrolled' => [403, 'student.classes'],
                default => [422, 'student.classes'],
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
            'user_id' => $user->id,
        ], [
            'level' => 'SPM',
            'total_points' => 0,
        ]);

        PageController::syncStudentEnrollments($student);
        app(AssignmentService::class)->syncStudentAssignments($student);

        return $student;
    }
}
