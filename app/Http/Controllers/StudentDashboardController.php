<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\StudentAssignmentStatus;
use App\Models\Assignment;
use App\Models\FeeStructure;
use App\Models\Invoice;
use App\Models\LiveClass;
use App\Models\Payment;
use App\Models\RecordedClass;
use App\Models\Setting;
use App\Models\Student;
use App\Models\StudentAssignment;
use App\Models\StudentQuiz;
use App\Models\StudentTopicMastery;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
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

    public function learning()
    {
        $student = $this->getStudent();
        $enrollments = $student->enrollments()->with('course.subject.topics.subtopics', 'course.teacher.user')->get();
        $recordings = RecordedClass::latest()->get();

        // Build dynamic subjects array from database
        $studentProgress = \App\Models\StudentSubtopicProgress::where('student_id', $student->id)->get()->keyBy('subtopic_id');

        $subjectsData = [];
        foreach ($enrollments as $enrollment) {
            $course = $enrollment->course;
            $subject = $course->subject;
            if (!$subject) continue;

            $teacherName = $course->teacher->user->name ?? 'Instructor';
            
            // Build topics and subtopic breakdowns
            $topicsData = [];
            $totalSubtopics = 0;
            $masteredSubtopics = 0;

            foreach ($subject->topics as $topic) {
                $breakdowns = [];
                $topicTotal = 0;
                $topicMastered = 0;

                foreach ($topic->subtopics as $subtopic) {
                    $prog = $studentProgress->get($subtopic->id);
                    $status = $prog ? $prog->status : $subtopic->default_status;

                    $badgeClass = match($status) {
                        'Mastered' => 'bg-emerald-50 text-emerald-700 border-emerald-200/60',
                        'In Progress' => 'bg-blue-50 text-blue-700 border-blue-200/60',
                        'Needs Practice' => 'bg-amber-50 text-amber-700 border-amber-200/60',
                        default => 'bg-gray-50 text-gray-700 border-gray-200/60',
                    };

                    if ($status === 'Mastered') {
                        $topicMastered += 100;
                        $masteredSubtopics += 100;
                    } elseif ($status === 'In Progress') {
                        $topicMastered += 50;
                        $masteredSubtopics += 50;
                    } elseif ($status === 'Needs Practice') {
                        $topicMastered += 25;
                        $masteredSubtopics += 25;
                    }

                    $topicTotal++;
                    $totalSubtopics++;

                    $breakdowns[] = [
                        'name' => $subtopic->title,
                        'status' => $status,
                        'badgeClass' => $badgeClass,
                    ];
                }

                $topicProgress = $topicTotal > 0 ? (int) round($topicMastered / $topicTotal) : 0;

                $topicsData[] = [
                    'name' => $topic->title,
                    'progress' => $topicProgress,
                    'breakdowns' => $breakdowns,
                ];
            }

            $overallSubjectProgress = $totalSubtopics > 0 
                ? (int) round($masteredSubtopics / $totalSubtopics) 
                : ($enrollment->progress_pct ?? 0);

            // Dynamic tab counts from database
            $practiceCount = \App\Models\Quiz::where('course_id', $course->id)->has('questions')->count();
            $videoCount = $recordings->where('subject_id', $subject->id)->count();
            $courseMaterialsCount = \App\Models\LearningMaterial::where('course_id', $course->id)->where('status', 'Published')->count();
            $notesCount = $courseMaterialsCount > 0 ? $courseMaterialsCount : 0;

            $subjectsData[] = [
                'id'             => $subject->slug,
                'name'           => $subject->name,
                'code'           => $subject->code ?? 'KPM-' . $subject->id,
                'education_type' => $subject->education_type ?? 'secondary',
                'stage'          => $subject->stage ?? 'Upper Secondary',
                'form_year'      => $subject->form_year ?? 'Form 4–5',
                'category'       => $subject->category ?? 'Core',
                'stream_package' => $subject->stream_package,
                'teacher'        => $teacherName,
                'level'          => $course->level ?? $subject->level ?? 'SPM',
                'progress'       => $overallSubjectProgress,
                'notesCount'     => $notesCount,
                'practiceCount'  => $practiceCount,
                'videoCount'     => $videoCount,
                'topics'         => $topicsData,
            ];
        }

        // If student has no subjects enrolled, leave subjectsData empty
        if (empty($subjectsData)) {
            $subjectsData = [];
        }

        // All quizzes and learning materials for student's enrolled courses
        $courseIds = $enrollments->pluck('course_id');
        $allQuizzes = \App\Models\Quiz::whereIn('course_id', $courseIds)->with(['course.subject', 'questions'])->get();
        $allMaterials = \App\Models\LearningMaterial::whereIn('course_id', $courseIds)
                                                    ->where('status', 'Published')
                                                    ->with('course.subject', 'teacher.user')
                                                    ->latest()
                                                    ->get();

        return view('student.learning', compact('student', 'enrollments', 'recordings', 'subjectsData', 'allQuizzes', 'allMaterials'));
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

    public function achievements()
    {
        $student = $this->getStudent();
        $user = auth()->user();
        $student->recordLoginStreak();

        // Load relationships
        $student->load([
            'enrollments.course.subject',
            'enrollments.course.teacher.user',
            'assignments.assignment.course.subject',
            'quizResults.quiz.course.subject',
            'attendances.liveClass'
        ]);

        $enrollments = $student->enrollments;
        $quizResults = $student->quizResults;
        $assignments = $student->assignments;
        $attendances = $student->attendances;

        // 1. Stats Calculations
        $completedQuizzesCount = $quizResults->count();
        $perfectQuizzesCount   = $quizResults->filter(fn($q) => $q->total_questions > 0 && $q->score >= $q->total_questions)->count();
        $highScoreQuizzesCount = $quizResults->filter(fn($q) => $q->percent >= 80)->count();
        $avgQuizScore          = $completedQuizzesCount > 0 ? (int) round($quizResults->avg(fn($q) => $q->percent)) : 0;
        
        $submittedAssignmentsCount = $assignments->whereIn('status', [StudentAssignmentStatus::Submitted, StudentAssignmentStatus::Graded])->count();
        $attendedClassesCount      = $attendances->whereIn('status', ['attended', 'late'])->count();
        $streakDays                = max(1, $student->streak_days ?? 1);

        // Completed lessons count
        $completedLessonsCount = \App\Models\StudentSubtopicProgress::where('student_id', $student->id)->where('status', 'completed')->count();
        if ($completedLessonsCount === 0 && $enrollments->count() > 0) {
            $completedLessonsCount = (int) round($enrollments->sum(fn($e) => round((($e->progress_pct ?? 0) / 100) * 8)));
        }

        // 2. Dynamic Points Calculation
        $calculatedPoints = ($attendedClassesCount * 10)
            + ($submittedAssignmentsCount * 20)
            + ($completedQuizzesCount * 15)
            + ($highScoreQuizzesCount * 30)
            + ($streakDays * 15)
            + 150; // Welcome onboarding bonus

        if ($student->total_points < $calculatedPoints) {
            $student->update(['total_points' => $calculatedPoints]);
        }
        $totalPoints = $student->total_points ?: $calculatedPoints;

        // Points breakdown history items
        $pointsBreakdown = [
            ['activity' => 'Live Class Attendance', 'rate' => '+10 pts each', 'count' => $attendedClassesCount, 'earned' => $attendedClassesCount * 10],
            ['activity' => 'Submitted Assignments', 'rate' => '+20 pts each', 'count' => $submittedAssignmentsCount, 'earned' => $submittedAssignmentsCount * 20],
            ['activity' => 'Completed Quizzes', 'rate' => '+15 pts each', 'count' => $completedQuizzesCount, 'earned' => $completedQuizzesCount * 15],
            ['activity' => 'High Quiz Scores (80%+)', 'rate' => '+30 pts each', 'count' => $highScoreQuizzesCount, 'earned' => $highScoreQuizzesCount * 30],
            ['activity' => 'Active Study Streak', 'rate' => '+15 pts / day', 'count' => $streakDays . ' days', 'earned' => $streakDays * 15],
            ['activity' => 'Welcome Onboarding Bonus', 'rate' => 'One-time', 'count' => '1', 'earned' => 150],
        ];

        // 3. Dynamic Badges
        $mathPassed = $quizResults->filter(function($q) {
            $subj = strtolower($q->quiz?->course?->subject?->name ?? '');
            return str_contains($subj, 'math') && $q->percent >= 70;
        })->isNotEmpty() || $enrollments->contains(fn($e) => str_contains(strtolower($e->course?->subject?->name ?? ''), 'math'));

        $sciencePassed = $quizResults->filter(function($q) {
            $subj = strtolower($q->quiz?->course?->subject?->name ?? '');
            return str_contains($subj, 'science') && $q->percent >= 70;
        })->isNotEmpty() || $enrollments->contains(fn($e) => str_contains(strtolower($e->course?->subject?->name ?? ''), 'science'));

        $langPassed = $quizResults->filter(function($q) {
            $subj = strtolower($q->quiz?->course?->subject?->name ?? '');
            return (str_contains($subj, 'english') || str_contains($subj, 'bahasa')) && $q->percent >= 70;
        })->isNotEmpty() || $enrollments->contains(fn($e) => str_contains(strtolower($e->course?->subject?->name ?? ''), 'english') || str_contains(strtolower($e->course?->subject?->name ?? ''), 'bahasa'));

        $badges = [
            [
                'id'          => 'math-master',
                'name'        => 'Mathematics Master',
                'category'    => 'academic',
                'color'       => 'purple',
                'unlocked'    => $mathPassed,
                'earned_at'   => $mathPassed ? $student->created_at->format('d M Y') : null,
                'description' => 'Enrolled and actively participating in Mathematics modules.',
                'progress'    => $mathPassed ? 'Unlocked' : '0/1 Quiz',
                'icon'        => 'book'
            ],
            [
                'id'          => 'science-explorer',
                'name'        => 'Science Explorer',
                'category'    => 'academic',
                'color'       => 'emerald',
                'unlocked'    => $sciencePassed,
                'earned_at'   => $sciencePassed ? $student->created_at->format('d M Y') : null,
                'description' => 'Enrolled and exploring Science conceptual experiments.',
                'progress'    => $sciencePassed ? 'Unlocked' : '0/1 Quiz',
                'icon'        => 'beaker'
            ],
            [
                'id'          => 'language-achiever',
                'name'        => 'Language Achiever',
                'category'    => 'academic',
                'color'       => 'blue',
                'unlocked'    => $langPassed,
                'earned_at'   => $langPassed ? $student->created_at->format('d M Y') : null,
                'description' => 'Active in English or Bahasa Melayu mastery lessons.',
                'progress'    => $langPassed ? 'Unlocked' : '0/1 Quiz',
                'icon'        => 'pencil'
            ],
            [
                'id'          => 'consistent-learner',
                'name'        => 'Consistent Learner',
                'category'    => 'habit',
                'color'       => 'orange',
                'unlocked'    => $streakDays >= 3,
                'earned_at'   => $streakDays >= 3 ? now()->subDays(max(0, $streakDays - 3))->format('d M Y') : null,
                'description' => 'Maintained an active 3-day daily study streak.',
                'progress'    => min($streakDays, 3) . '/3 Days',
                'icon'        => 'calendar'
            ],
            [
                'id'          => 'streak-champion',
                'name'        => 'Streak Champion',
                'category'    => 'habit',
                'color'       => 'amber',
                'unlocked'    => $streakDays >= 7,
                'earned_at'   => $streakDays >= 7 ? now()->subDays(max(0, $streakDays - 7))->format('d M Y') : null,
                'description' => 'Maintained a 7-day continuous study habit.',
                'progress'    => min($streakDays, 7) . '/7 Days',
                'icon'        => 'flame'
            ],
            [
                'id'          => 'attendance-star',
                'name'        => 'Attendance Star',
                'category'    => 'habit',
                'color'       => 'teal',
                'unlocked'    => $attendedClassesCount >= 1,
                'earned_at'   => $attendedClassesCount >= 1 ? now()->format('d M Y') : null,
                'description' => 'Attended live online classes on time.',
                'progress'    => min($attendedClassesCount, 1) . '/1 Class',
                'icon'        => 'check-badge'
            ],
            [
                'id'          => 'problem-solver',
                'name'        => 'Problem Solver',
                'category'    => 'improvement',
                'color'       => 'red',
                'unlocked'    => $completedQuizzesCount >= 1,
                'earned_at'   => $completedQuizzesCount >= 1 ? ($quizResults->first()?->completed_at?->format('d M Y') ?? now()->format('d M Y')) : null,
                'description' => 'Completed quiz assessments and practice challenges.',
                'progress'    => min($completedQuizzesCount, 3) . '/3 Quizzes',
                'icon'        => 'puzzle'
            ],
            [
                'id'          => 'quiz-whiz',
                'name'        => 'Quiz Whiz',
                'category'    => 'improvement',
                'color'       => 'indigo',
                'unlocked'    => ($perfectQuizzesCount >= 1 || $highScoreQuizzesCount >= 1),
                'earned_at'   => ($perfectQuizzesCount >= 1 || $highScoreQuizzesCount >= 1) ? now()->format('d M Y') : null,
                'description' => 'Scored 80% or higher in a quiz assessment.',
                'progress'    => min($highScoreQuizzesCount, 1) . '/1 High Score',
                'icon'        => 'trophy'
            ],
            [
                'id'          => 'assignment-pro',
                'name'        => 'Assignment Pro',
                'category'    => 'special',
                'color'       => 'purple',
                'unlocked'    => $submittedAssignmentsCount >= 1,
                'earned_at'   => $submittedAssignmentsCount >= 1 ? now()->format('d M Y') : null,
                'description' => 'Completed and submitted assignments on time.',
                'progress'    => min($submittedAssignmentsCount, 3) . '/3 Submitted',
                'icon'        => 'clipboard'
            ],
            [
                'id'          => 'olympian-scholar',
                'name'        => 'Olympian Scholar',
                'category'    => 'special',
                'color'       => 'amber',
                'unlocked'    => $totalPoints >= 500,
                'earned_at'   => $totalPoints >= 500 ? now()->format('d M Y') : null,
                'description' => 'Accumulated 500+ points across all learning activities.',
                'progress'    => min($totalPoints, 500) . '/500 Points',
                'icon'        => 'sparkles'
            ],
        ];

        // 4. Certificates (Only Admin-Issued / Uploaded Certificates)
        $certificates = $student->certificates()->with('course.subject')->latest()->get()->map(function ($cert) use ($student, $user) {
            return [
                'id'           => $cert->id,
                'title'        => $cert->title,
                'subtitle'     => $cert->subtitle ?? ($cert->course?->subject?->name ? ($cert->course->subject->name . ' – ' . ($student->level ?? 'Academic')) : 'Olympian Scholars Academy'),
                'subject'      => $cert->course?->subject?->name ?? 'Academic Achievement',
                'teacher'      => $cert->issued_by ?? 'Olympian Scholars Administration',
                'date'         => $cert->issued_at ? $cert->issued_at->format('d M Y') : $cert->created_at->format('d M Y'),
                'is_unlocked'  => true,
                'student_name' => $user->name,
                'cert_no'      => $cert->cert_number,
                'file_url'     => $cert->file_url,
                'file_name'    => $cert->file_name,
                'description'  => $cert->description,
            ];
        });

        // 5. Dynamic Milestones
        $milestones = [
            [
                'title'       => 'First 10 Lessons Completed',
                'subtitle'    => $completedLessonsCount >= 10 ? 'Great milestone reached!' : 'Complete your course lessons',
                'current'     => min($completedLessonsCount, 10),
                'target'      => 10,
                'completed'   => $completedLessonsCount >= 10,
                'progress_pct'=> min(100, (int) round(($completedLessonsCount / 10) * 100)),
            ],
            [
                'title'       => '3 Assignments Submitted',
                'subtitle'    => $submittedAssignmentsCount >= 3 ? 'Excellent discipline!' : 'Submit your teacher homework',
                'current'     => min($submittedAssignmentsCount, 3),
                'target'      => 3,
                'completed'   => $submittedAssignmentsCount >= 3,
                'progress_pct'=> min(100, (int) round(($submittedAssignmentsCount / 3) * 100)),
            ],
            [
                'title'       => '7-Day Study Streak',
                'subtitle'    => $streakDays >= 7 ? 'Amazing consistency!' : 'Keep logging in daily',
                'current'     => min($streakDays, 7),
                'target'      => 7,
                'completed'   => $streakDays >= 7,
                'progress_pct'=> min(100, (int) round(($streakDays / 7) * 100)),
            ],
            [
                'title'       => 'Subject Mastery (80%+ Quiz Score)',
                'subtitle'    => $avgQuizScore >= 80 ? 'Mastery achieved!' : 'Aim for 80% average quiz score',
                'current'     => min($avgQuizScore, 80),
                'target'      => 80,
                'completed'   => $avgQuizScore >= 80,
                'progress_pct'=> min(100, (int) round(($avgQuizScore / 80) * 100)),
            ],
            [
                'title'       => 'Complete 3 Practice Quizzes',
                'subtitle'    => $completedQuizzesCount >= 3 ? 'Practice makes perfect!' : 'Test your knowledge with quizzes',
                'current'     => min($completedQuizzesCount, 3),
                'target'      => 3,
                'completed'   => $completedQuizzesCount >= 3,
                'progress_pct'=> min(100, (int) round(($completedQuizzesCount / 3) * 100)),
            ],
        ];

        // 6. Dynamic Leaderboard
        $topStudents = Student::with('user')
            ->whereNotNull('user_id')
            ->orderByDesc('total_points')
            ->take(10)
            ->get();

        $rankedList = [];
        $foundCurrent = false;

        foreach ($topStudents as $idx => $ts) {
            $isMe = ($ts->id === $student->id);
            if ($isMe) $foundCurrent = true;
            $rankedList[] = [
                'rank'   => $idx + 1,
                'name'   => $isMe ? ($user->name . ' (You)') : ($ts->user->name ?? 'Student #' . $ts->id),
                'points' => number_format($ts->total_points ?: ($isMe ? $totalPoints : 250)),
                'is_me'  => $isMe,
            ];
        }

        if (!$foundCurrent) {
            $betterCount = Student::where('total_points', '>', $totalPoints)->count();
            $myRank = $betterCount + 1;
            $rankedList[] = [
                'rank'   => $myRank,
                'name'   => $user->name . ' (You)',
                'points' => number_format($totalPoints),
                'is_me'  => true,
            ];
        }

        return view('student.achievements', compact(
            'student',
            'user',
            'totalPoints',
            'pointsBreakdown',
            'badges',
            'certificates',
            'milestones',
            'rankedList',
            'streakDays',
            'completedQuizzesCount',
            'submittedAssignmentsCount',
            'attendedClassesCount'
        ));
    }

    public function messages()
    {
        $student = $this->getStudent();
        return view('student.messages', compact('student'));
    }

    public function myCourses()  { return $this->learning(); }
    public function timetable() { return $this->classes(); }
    public function profile(Request $request)
    {
        $student = $this->getStudent();
        if ($student) {
            $student->load(['user', 'parent', 'parents', 'enrollments.course.subject']);
        }
        
        $user = auth()->user();
        $parent = $student?->parent ?? $student?->parents?->first();
        $enrolledSubjects = $student ? $student->enrollments->map(fn($e) => $e->course->subject->name ?? null)->filter()->unique()->values() : collect();

        $activeTab = $request->query('tab', 'profile');

        return view('student.profile', compact('user', 'student', 'parent', 'enrolledSubjects', 'activeTab'));
    }

    /**
     * Link the student's account to a parent account by the parent's IC number.
     */
    public function linkParent(Request $request)
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

        // Only digits must be 12 characters
        $cleanDigits = preg_replace('/\D/', '', $rawIc);
        if (strlen($cleanDigits) !== 12) {
            return redirect()->route('student.profile', ['tab' => 'profile'])
                ->withInput()
                ->withErrors(['parent_ic' => 'Please enter a valid 12-digit MyKad number (e.g. 901231-14-5678).']);
        }

        // Look up parent by hashed IC
        $icHash     = \App\Models\User::hashIc($rawIc);
        $parentUser = \App\Models\User::where('ic_number_hash', $icHash)
                                      ->where('role', 'parent')
                                      ->first();

        if (!$parentUser) {
            return redirect()->route('student.profile', ['tab' => 'profile'])
                ->withInput()
                ->withErrors(['parent_ic' => 'No parent account found with that IC number. Please check and try again.']);
        }

        // Check if already linked
        $alreadyLinked = \Illuminate\Support\Facades\DB::table('parent_student')
            ->where('parent_user_id', $parentUser->id)
            ->where('student_id', $student->id)
            ->exists() || $student->parent_user_id === $parentUser->id;

        if ($alreadyLinked) {
            return redirect()->route('student.profile', ['tab' => 'profile'])
                ->with('profile_updated', "Your account is already linked to {$parentUser->name}.");
        }

        // Create the link
        \Illuminate\Support\Facades\DB::table('parent_student')->insertOrIgnore([
            'parent_user_id' => $parentUser->id,
            'student_id'     => $student->id,
            'relationship'   => $relationship,
            'is_primary'     => true,
            'status'         => 'active',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        // Backfill legacy column if empty
        if (!$student->parent_user_id) {
            $student->update(['parent_user_id' => $parentUser->id]);
        }

        return redirect()->route('student.profile', ['tab' => 'profile'])
            ->with('profile_updated', "Successfully linked to parent account: {$parentUser->name}.");
    }


    /**
     * Update the student's personal information (name, email, phone, level).
     */
    public function updateProfile(Request $request)
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

        // Handle Avatar Removal
        if ($request->boolean('remove_avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = null;
        }

        // Handle New Avatar Upload
        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->name = $validated['name'];
        if (!empty($validated['email'])) {
            if ($user->email !== $validated['email']) {
                $user->email_verified_at = null;
            }
            $user->email = $validated['email'];
        }
        if (isset($validated['phone'])) {
            $user->phone = $validated['phone'];
        }
        $user->save();

        if ($student) {
            $student->update([
                'school_name' => $validated['school_name'] ?? $student->school_name,
            ]);
        }

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
    public function updatePassword(Request $request)
    {
        $user    = auth()->user();
        $student = Student::where('user_id', $user->id)->first();
        $isParentManaged = $student && $student->parent_user_id;

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

        $user->update(['password' => Hash::make($validated['password'])]);

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
    public function fees()
    {
        $student = $this->getStudent();
        Invoice::syncStudentInvoices($student);
        
        $invoices = Invoice::where('student_id', $student->id)
            ->with(['feeStructure', 'course.subject', 'payments'])
            ->latest('due_date')
            ->get();

        $outstandingInvoices = $invoices->filter(fn($inv) => in_array($inv->status, InvoiceStatus::unpaidStatuses(), true))->values();
        $paidInvoices        = $invoices->filter(fn($inv) => $inv->status === InvoiceStatus::Paid)->values();
        $overdueInvoices     = $invoices->filter(fn($inv) => $inv->status === InvoiceStatus::Overdue)->values();

        $totalOutstandingAmount = $outstandingInvoices->sum('total_amount');
        $totalPaidAmount        = $paidInvoices->sum('total_amount');

        $payments = Payment::where('student_id', $student->id)
            ->with(['invoice', 'payer'])
            ->latest('id')
            ->get();

        // Public Company Bank details for manual bank transfer
        $bankDetails = [
            'bank_name'      => Setting::get('company_bank_name', 'Maybank Islamic Berhad'),
            'account_name'   => Setting::get('company_bank_account_name', 'Olympia Scholars Academy Sdn Bhd'),
            'account_number' => Setting::get('company_bank_account_number', '5642 8901 2345'),
            'instructions'   => Setting::get('company_bank_instructions', 'Please include your Student Name & Invoice No in the transfer reference.'),
        ];

        return view('student.fees', compact(
            'student',
            'invoices',
            'outstandingInvoices',
            'paidInvoices',
            'overdueInvoices',
            'totalOutstandingAmount',
            'totalPaidAmount',
            'payments',
            'bankDetails'
        ));
    }

    /**
     * Process invoice payment by student.
     */
    public function payInvoice(Request $request, $id)
    {
        $student = $this->getStudent();
        $invoice = Invoice::where('student_id', $student->id)->findOrFail($id);

        if ($invoice->status === InvoiceStatus::Paid) {
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
                'user_id'         => auth()->id(),
                'invoice_id'      => $invoice->id,
                'student_id'      => $student->id,
                'payer_id'        => auth()->id(),
                'payer_type'      => 'student',
                'description'     => $invoice->title . ' (Bank Transfer Slip Uploaded)',
                'amount'          => $invoice->total_amount,
                'payment_method'  => 'bank_transfer',
                'proof_file_path' => $proofPath,
                'reference_no'    => $refNo,
                'status'          => \App\Enums\PaymentStatus::Pending,
                'paid_at'         => now(),
            ]);

            // 'pending_verification' was never a valid Invoice status (enum:
            // draft/issued/partially_paid/paid/overdue/cancelled) — would
            // throw under strict SQL mode. The Payment record just created
            // above already correctly carries PaymentStatus::Pending, which
            // is the real "awaiting verification" signal; the invoice stays
            // at its current status (issued/overdue) until an admin approves
            // the payment via AdminDashboardController::verifyPayment().

            return back()->with('success', 'Payment proof submitted successfully! Your payment is pending verification.');
        } else {
            $refNo = 'TXN-FPX-' . strtoupper(substr(md5(uniqid()), 0, 8));
            $receiptNo = 'RCP-' . date('Y') . '-' . str_pad($invoice->id * 10 + rand(1, 9), 5, '0', STR_PAD_LEFT);

            Payment::create([
                'user_id'        => auth()->id(),
                'invoice_id'     => $invoice->id,
                'student_id'     => $student->id,
                'payer_id'       => auth()->id(),
                'payer_type'     => 'student',
                'description'    => $invoice->title,
                'amount'         => $invoice->total_amount,
                'payment_method' => $paymentMethod,
                'reference_no'   => $refNo,
                'receipt_number' => $receiptNo,
                'status'         => \App\Enums\PaymentStatus::Successful,
                'paid_at'        => now(),
            ]);

            $invoice->update(['status' => InvoiceStatus::Paid]);

            \App\Models\UserNotification::create([
                'user_id' => auth()->id(),
                'type'    => 'payment',
                'title'   => 'PAYMENT SUCCESSFUL',
                'message' => "Payment of RM " . number_format($invoice->total_amount, 2) . " for '{$invoice->title}' has been processed. Receipt No: {$receiptNo}.",
                'read_at' => null,
            ]);

            return back()->with('success', "Payment successful! Receipt #{$receiptNo} has been generated.");
        }
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
