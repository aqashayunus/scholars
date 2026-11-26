<?php

namespace App\Services\Student;

use App\Http\Controllers\PageController;
use App\Models\LiveClass;
use App\Models\RecordedClass;
use App\Models\Student;
use App\Models\StudentAssignment;
use App\Models\StudentQuiz;
use App\Models\StudentTopicMastery;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\StudentAccessRestrictionService;
use Carbon\Carbon;

class DashboardMetricsService
{
    public function __construct(
        private readonly AssignmentService $assignmentService,
        private readonly ClassScheduleService $classSchedule,
        private readonly QuizPerformanceService $quizPerformance,
    ) {}

    /**
     * Builds the full view-model for the student dashboard.
     */
    public function build(User $user): array
    {
        $student = Student::where('user_id', $user->id)->first();

        if (! $student) {
            $student = Student::create([
                'user_id' => $user->id,
                'level' => 'SPM',
                'total_points' => 0,
            ]);
        }

        PageController::syncStudentEnrollments($student);
        $student->load('enrollments.course.subject', 'enrollments.course.teacher.user');

        $enrollments = $student->enrollments;
        $enrolledCourses = $enrollments->count();
        $completedLessons = $enrollments->sum('completed_lessons');

        $recordedCount = RecordedClass::count();
        $topicMasteries = StudentTopicMastery::where('student_id', $student->id)->get();

        $quizResults = StudentQuiz::where('student_id', $student->id)
            ->with('quiz.course.subject')
            ->latest()->take(5)->get();

        // Ensure newly enrolled students have tracking records for all course assignments
        $this->assignmentService->syncStudentAssignments($student);

        $assignments = StudentAssignment::where('student_id', $student->id)
            ->whereHas('assignment')
            ->with('assignment.course.subject')
            ->latest()->take(6)->get();

        // Next live class: the earliest one that hasn't ended yet (ongoing
        // or upcoming). The end time is start + duration, so it's checked in
        // PHP; the 12h look-back only bounds how long a class can be.
        // Previously any class that started in the last 2h was shown, so an
        // ended class stayed on the card as "UPCOMING" and hid the next one.
        $upcomingClass = LiveClass::whereHas('course', function ($q) use ($student) {
            $q->whereHas('enrollments', fn ($q2) => $q2->where('student_id', $student->id));
        })
            ->with('course.teacher.user', 'course.subject')
            ->where('status', '!=', 'cancelled')
            ->where('scheduled_at', '>=', now()->subHours(12))
            ->orderBy('scheduled_at', 'asc')
            ->limit(20)
            ->get()
            ->first(fn (LiveClass $lc) => $lc->scheduled_at->copy()->addMinutes($lc->duration_minutes ?? 60)->gte(now()));

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
            ->filter(function ($sa) use ($enrollmentsMap) {
                $courseId = $sa->assignment?->course_id;
                $enrolledAtStr = $enrollmentsMap[$courseId] ?? null;
                $enrolledAt = $enrolledAtStr ? Carbon::parse($enrolledAtStr) : ($sa->created_at ?? now());
                $dueDate = $sa->assignment?->due_date ? Carbon::parse($sa->assignment->due_date) : null;

                // Exclude tasks that were already due before the student joined
                return ! ($dueDate && $dueDate->lt($enrolledAt->startOfDay()));
            })
            ->count();
        // Dynamic Attendance calculation from centralized helper
        $attStats = $this->classSchedule->attendanceStats($student);
        $attendanceRate = $attStats['pct'];

        // Personal Growth & Study Streak Stats (Dynamic from Database)
        $studyStreakDays = $student->streak_days ?? 1;
        $totalQuestionsPracticed = $student->quizResults()->sum('total_questions') ?: 0;

        // Generate dynamic 7-day calendar schedule (Monday to Sunday)
        $startOfWeek = now()->startOfWeek();
        $weeklyLiveClasses = LiveClass::whereHas('course', function ($q) use ($student) {
            $q->whereHas('enrollments', fn ($q2) => $q2->where('student_id', $student->id));
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
                'dayName' => $dayDate->format('D'),
                'dayNumber' => $dayDate->format('d M'),
                'isToday' => $dayDate->isToday(),
                'classes' => $dayClasses,
            ];
        }

        // Personal performance trajectory chart from actual student quiz records
        ['performanceLabels' => $performanceLabels, 'performanceData' => $performanceData] =
            $this->quizPerformance->performanceChartData($student);

        $restrictionService = app(StudentAccessRestrictionService::class);
        $isRestricted = $restrictionService->isStudentRestricted($student);
        $restrictionDetails = $isRestricted ? $restrictionService->getRestrictionDetails($student) : null;

        // Diagnostic Assessment Status
        $pendingDiagnostic = StudentAssignment::where('student_id', $student->id)
            ->whereHas('assignment', fn ($q) => $q->where('type', 'Diagnostic Test'))
            ->whereIn('status', ['pending', 'overdue'])
            ->with('assignment')
            ->first();

        return compact(
            'student', 'enrollments', 'enrolledCourses', 'completedLessons',
            'recordedCount', 'topicMasteries', 'quizResults', 'assignments',
            'upcomingClass', 'studyStreakDays', 'totalQuestionsPracticed',
            'performanceLabels', 'performanceData',
            'overallProgress', 'attendanceRate', 'tasksDueCount', 'weekSchedule',
            'isRestricted', 'restrictionDetails', 'pendingDiagnostic'
        );
    }

    /**
     * Auto-dispatch notifications for classes starting in <= 10 mins or currently live.
     */
    private function checkLiveClassNotifications(User $user, Student $student): void
    {
        $now = now();

        $enrolledClasses = LiveClass::whereHas('course', function ($q) use ($student) {
            $q->whereHas('enrollments', fn ($q2) => $q2->where('student_id', $student->id));
        })
            ->with('course.teacher.user')
            ->where('scheduled_at', '>=', $now->copy()->subMinutes(180))
            ->where('scheduled_at', '<=', $now->copy()->addMinutes(60))
            ->get();

        if ($enrolledClasses->isEmpty()) {
            return;
        }

        // One query for every "[LiveClass ID: ...]" tag already sent to this
        // user in the relevant window, instead of one exists() query per
        // candidate class inside the loop below.
        $alreadyNotifiedTags = UserNotification::where('user_id', $user->id)
            ->whereIn('type', ['class_live', 'class_reminder'])
            ->pluck('message')
            ->filter(fn ($message) => preg_match('/(\[LiveClass ID: \d+\] \[Status: \w+\])/', $message, $m) ? $m[1] : false)
            ->flip();

        foreach ($enrolledClasses as $liveClass) {
            $startTime = $liveClass->scheduled_at;
            $endTime = $startTime->copy()->addMinutes($liveClass->duration_minutes ?? 60);
            $teacherName = $liveClass->course->teacher->user->name ?? 'your instructor';

            // 1. Check if class is LIVE NOW
            if ($now->gte($startTime) && $now->lte($endTime)) {
                $uniqueTag = "[LiveClass ID: {$liveClass->id}] [Status: LIVE]";

                if (! isset($alreadyNotifiedTags[$uniqueTag])) {
                    UserNotification::create([
                        'user_id' => $user->id,
                        'type' => 'class_live',
                        'title' => 'Class Live Now 🔴',
                        'message' => "Your live class '{$liveClass->title}' with {$teacherName} is in progress now! Click to join. {$uniqueTag}",
                    ]);
                }
            }
            // 2. Check if class is STARTING IN <= 10 MINUTES
            elseif ($now->lt($startTime) && $now->diffInMinutes($startTime, false) <= 10 && $now->diffInMinutes($startTime, false) >= 0) {
                $minutesLeft = max(1, (int) ceil($now->diffInMinutes($startTime)));
                $uniqueTag = "[LiveClass ID: {$liveClass->id}] [Status: 10MIN]";

                if (! isset($alreadyNotifiedTags[$uniqueTag])) {
                    UserNotification::create([
                        'user_id' => $user->id,
                        'type' => 'class_reminder',
                        'title' => 'Class Starting Soon ⏳',
                        'message' => "Your live class '{$liveClass->title}' with {$teacherName} is starting in {$minutesLeft} minute(s)! Get ready. {$uniqueTag}",
                    ]);
                }
            }
        }
    }
}
