<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Enrollment;
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
use App\Models\Quiz;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceEvent;
use App\Services\AttendanceService;
use App\Services\StudentAccessRestrictionService;

class StudentDashboardController extends Controller
{
    /**
     * Resilient live classes query builder for a student.
     * Matches via:
     *  1. Direct course enrollment
     *  2. Subject-matching courses (handles parallel course records for the same subject/curriculum)
     */
    public static function getStudentLiveClassesQuery(Student $student)
    {
        return LiveClass::where(function ($q) use ($student) {
            // 1. Direct course enrollment
            $q->whereHas('course.enrollments', function ($eq) use ($student) {
                $eq->where('student_id', $student->id);
            });

            // 2. Dual-layer: match courses with the same subject
            $enrolledSubjectIds = DB::table('enrollments')
                ->join('courses', 'courses.id', '=', 'enrollments.course_id')
                ->where('enrollments.student_id', $student->id)
                ->pluck('courses.subject_id')
                ->filter()
                ->unique()
                ->toArray();

            if (!empty($enrolledSubjectIds)) {
                $subjectNames = DB::table('subjects')->whereIn('id', $enrolledSubjectIds)->pluck('name')->toArray();
                $allMatchingSubjectIds = DB::table('subjects')->whereIn('name', $subjectNames)->pluck('id')->toArray();

                $q->orWhereHas('course', function ($cq) use ($allMatchingSubjectIds) {
                    $cq->whereIn('subject_id', $allMatchingSubjectIds);
                });
            }
        });
    }
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
        $this->syncStudentAssignments($student);

        $assignments = StudentAssignment::where('student_id', $student->id)
                                        ->whereHas('assignment')
                                        ->with('assignment.course.subject')
                                        ->latest()->take(6)->get();

        // Real-time live class: fetch next upcoming or ongoing live session
        $upcomingClass = self::getStudentLiveClassesQuery($student)
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
        $attStats = $this->getStudentAttendanceStats($student);
        $attendanceRate = $attStats['pct'];

        // Personal Growth & Study Streak Stats (Dynamic from Database)
        $studyStreakDays = $student->streak_days ?? 1;
        $totalQuestionsPracticed = $student->quizResults()->sum('total_questions') ?: 0;

        // Generate dynamic 7-day calendar schedule (Monday to Sunday)
        $startOfWeek = now()->startOfWeek();
        $weeklyLiveClasses = self::getStudentLiveClassesQuery($student)
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
        $recentQuizzes = StudentQuiz::where('student_id', $student->id)
            ->orderBy('completed_at', 'asc')
            ->take(6)
            ->get();

        if ($recentQuizzes->isNotEmpty()) {
            $performanceLabels = $recentQuizzes->map(fn($q) => $q->completed_at ? $q->completed_at->format('M') : 'Quiz')->toArray();
            $performanceData   = $recentQuizzes->map(fn($q) => $q->total_questions > 0 ? (int) round(($q->score / $q->total_questions) * 100) : 0)->toArray();
        } else {
            $performanceLabels = [now()->format('M')];
            $performanceData   = [0];
        }

        $restrictionService = app(\App\Services\StudentAccessRestrictionService::class);
        $isRestricted = $restrictionService->isStudentRestricted($student);
        $restrictionDetails = $isRestricted ? $restrictionService->getRestrictionDetails($student) : null;

        // Diagnostic Assessment Status
        $pendingDiagnostics = StudentAssignment::where('student_id', $student->id)
            ->whereHas('assignment', fn($q) => $q->where('type', 'Diagnostic Test'))
            ->whereIn('status', ['pending', 'overdue'])
            ->with(['assignment.subject', 'assignment'])
            ->get();
        $pendingDiagnostic = $pendingDiagnostics->first();

        return view('student.dashboard', compact(
            'student', 'enrollments', 'enrolledCourses', 'completedLessons',
            'recordedCount', 'topicMasteries', 'quizResults', 'assignments',
            'upcomingClass', 'studyStreakDays', 'totalQuestionsPracticed',
            'performanceLabels', 'performanceData',
            'overallProgress', 'attendanceRate', 'tasksDueCount', 'weekSchedule',
            'isRestricted', 'restrictionDetails', 'pendingDiagnostic', 'pendingDiagnostics'
        ));
    }

    /**
     * Auto-dispatch notifications for classes starting in <= 10 mins or currently live.
     */
    private function checkLiveClassNotifications($user, $student): void
    {
        $now = now();
        
        $enrolledClasses = self::getStudentLiveClassesQuery($student)
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
        $recordings = RecordedClass::with(['subject', 'course'])->latest()->get();

        // Build dynamic subjects array from database
        $studentProgress = \App\Models\StudentSubtopicProgress::where('student_id', $student->id)->get()->keyBy('subtopic_id');

        $subjectsData = [];
        foreach ($enrollments as $enrollment) {
            $course = $enrollment->course;
            $subject = $course->subject;
            if (!$subject) continue;

            $teacherList = collect();
            if ($subject->teachers()->exists()) {
                foreach ($subject->teachers()->with('user')->get() as $t) {
                    if ($t->user && !empty($t->user->name)) {
                        $teacherList->push($t->user->name);
                    }
                }
            }
            if ($course->teacher && $course->teacher->user && !empty($course->teacher->user->name)) {
                $teacherList->push($course->teacher->user->name);
            }
            $teacherList = $teacherList->unique()->values();
            if ($teacherList->isEmpty()) {
                $teacherList = collect(['Instructor']);
            }
            $primaryTeacher = $teacherList->first();
            $allTeachers = $teacherList->all();
            
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
            $videoCount = $recordings->filter(function($r) use ($subject, $course) {
                return ($r->subject_id && $r->subject_id == $subject->id) || ($r->course_id && $r->course_id == $course->id);
            })->count();
            $courseMaterialsCount = \App\Models\LearningMaterial::where('course_id', $course->id)->where('status', 'Published')->count();
            $notesCount = $courseMaterialsCount > 0 ? $courseMaterialsCount : 0;

            $description = $subject->description ?: ($course->description ?: null);
            if (empty($description)) {
                $curriculumInfo = array_filter([$subject->stage, $subject->form_year, $subject->category]);
                $curriculumStr = !empty($curriculumInfo) ? ' (' . implode(' • ', $curriculumInfo) . ')' : '';
                $description = "Curriculum syllabus for {$subject->name}{$curriculumStr}, covering fundamental topics, learning modules, and structured assessments.";
            }

            $subjectsData[] = [
                'id'             => $subject->slug,
                'subject_id'     => $subject->id,
                'course_id'      => $course->id,
                'name'           => $subject->display_name ?? $subject->name,
                'code'           => $subject->code ?? 'KPM-' . $subject->id,
                'education_type' => $subject->education_type ?? 'secondary',
                'stage'          => $subject->stage ?? 'Upper Secondary',
                'form_year'      => $subject->form_year ?? 'Form 4–5',
                'category'       => $subject->category ?? 'Core',
                'stream_package' => $subject->stream_package,
                'description'    => $description,
                'teacher'        => $primaryTeacher,
                'teachers'       => $allTeachers,
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

    public function classes()
    {
        $student    = $this->getStudent();
        $now        = now();

        $liveClasses = self::getStudentLiveClassesQuery($student)
            ->with('course.subject', 'course.teacher.user')
            ->orderBy('scheduled_at')
            ->get();

        // ── Hero card logic (done in controller, not in blade) ─────────────
        // Number of minutes before a class is considered "starting soon".
        // Configurable via Admin Settings: live_class_starting_soon_minutes (default 15).
        $startingSoonWindow = (int) Setting::get('live_class_starting_soon_minutes', 15);

        // 1. A class is "live now" when now() falls between its start and end time.
        $currentlyLiveClass = $liveClasses->first(function ($lc) use ($now) {
            $start = $lc->scheduled_at;
            $end   = $start->copy()->addMinutes($lc->duration_minutes ?: 60);
            return $now->gte($start) && $now->lte($end);
        });

        // 2. "Starting soon" = not yet started, starts within the configured window.
        $startingSoonClass = $currentlyLiveClass
            ? null
            : $liveClasses->first(function ($lc) use ($now, $startingSoonWindow) {
                $start = $lc->scheduled_at;
                return $now->lt($start) && $now->diffInMinutes($start, false) <= $startingSoonWindow;
            });

        // 3. Hero class: running class first, then starting-soon, else null.
        $heroClass       = $currentlyLiveClass ?? $startingSoonClass;
        $isLiveNow       = $currentlyLiveClass !== null;
        $minutesUntilHero = ($heroClass && !$isLiveNow)
            ? (int) $now->diffInMinutes($heroClass->scheduled_at, false)
            : 0;
        // ───────────────────────────────────────────────────────────────────

        // 7-day week view
        $weekDays = collect();
        for ($i = 0; $i < 7; $i++) {
            $day = $now->copy()->addDays($i);
            $weekDays->push([
                'date'    => $day,
                'label'   => $day->format('D'),
                'full'    => $day->format('l'),
                'dateStr' => $day->format('d M'),
                'isToday' => $day->isToday(),
                'classes' => $liveClasses->filter(
                    fn($lc) => $lc->scheduled_at->format('Y-m-d') === $day->format('Y-m-d')
                )->values(),
            ]);
        }

        // All classes that are currently ongoing or scheduled for the future
        $upcomingClasses = $liveClasses->filter(fn($lc) => $lc->isOngoingOrUpcoming())->values();
        $pastClasses     = $liveClasses->filter(fn($lc) => $lc->isPastOrCompleted())->sortByDesc('scheduled_at')->values();

        // Attendance records
        $attendanceMap   = \App\Models\StudentAttendance::where('student_id', $student->id)
            ->pluck('status', 'live_class_id');

        $attendanceStats = $this->getStudentAttendanceStats($student);

        // Attendance correction requests history
        $correctionRequests = AttendanceCorrectionRequest::where('student_id', $student->id)
            ->with(['liveClass.course.subject', 'reviewer'])
            ->latest()
            ->get();

        return view('student.classes', compact(
            'student',
            'weekDays',
            'upcomingClasses',
            'pastClasses',
            'liveClasses',
            'attendanceStats',
            'attendanceMap',
            'correctionRequests',
            // hero-card variables (no business logic needed in blade)
            'heroClass',
            'isLiveNow',
            'minutesUntilHero',
            'startingSoonWindow'
        ));
    }

    public function tasks()
    {
        $student = $this->getStudent();
        $this->syncStudentAssignments($student);

        $enrollmentsMap = $student->enrollments()->pluck('enrolled_at', 'course_id')->toArray();

        $assignments = StudentAssignment::where('student_id', $student->id)
                                        ->whereHas('assignment')
                                        ->with('assignment.course.subject')
                                        ->get()
                                        ->sortByDesc(function ($sa) {
                                            return $sa->assignment?->updated_at 
                                                ?? $sa->assignment?->created_at 
                                                ?? $sa->updated_at 
                                                ?? $sa->created_at;
                                        })
                                        ->values();

        // Check if each assignment was assigned before the student enrolled
        $assignments->each(function($sa) use ($enrollmentsMap) {
            $courseId = $sa->assignment?->course_id;
            $enrolledAtStr = $enrollmentsMap[$courseId] ?? null;
            $enrolledAt = $enrolledAtStr ? \Carbon\Carbon::parse($enrolledAtStr) : ($sa->created_at ?? now());
            $dueDate = $sa->assignment?->due_date ? \Carbon\Carbon::parse($sa->assignment->due_date) : null;
            
            // True if assignment was due before the student enrolled in the course
            $sa->is_catch_up = $dueDate && $dueDate->lt($enrolledAt->startOfDay());
        });

        $totalTasks = $assignments->count();
        $completedCount = $assignments->whereIn('status', ['submitted', 'completed', 'graded'])->count();
        $pendingCount = $assignments->where('status', 'pending')->count();
        
        // Overdue ONLY applies to tasks due AFTER the student enrolled
        $overdueCount = $assignments->filter(function($sa) {
            return $sa->status === 'pending' 
                && !$sa->is_catch_up 
                && $sa->assignment 
                && $sa->assignment->due_date 
                && $sa->assignment->due_date->isPast();
        })->count();

        // Tasks assigned before enrollment are classified as Catch-up
        $catchUpCount = $assignments->filter(function($sa) {
            return $sa->status === 'pending' && ($sa->is_catch_up ?? false);
        })->count();

        $dueThisWeekCount = $assignments->filter(function($sa) {
            return $sa->status === 'pending' 
                && !$sa->is_catch_up 
                && $sa->assignment 
                && $sa->assignment->due_date 
                && $sa->assignment->due_date->between(now()->startOfWeek(), now()->endOfWeek());
        })->count() ?: ($pendingCount - $catchUpCount);

        $completionRate = $totalTasks > 0 ? round(($completedCount / $totalTasks) * 100) : 0;

        $upcomingDeadlines = $assignments->filter(function($sa) {
            return $sa->status === 'pending' && !$sa->is_catch_up && $sa->assignment && $sa->assignment->due_date;
        })->sortBy(fn($sa) => $sa->assignment->due_date)->take(5);

        $enrolledSubjects = $assignments->map(fn($sa) => $sa->assignment?->course?->subject?->display_name ?? $sa->assignment?->course?->subject?->name ?? ($sa->assignment?->type === 'Diagnostic Test' ? 'Diagnostic Test' : 'General'))->filter()->unique()->values();

        $diagnosticTasks = StudentAssignment::where('student_id', $student->id)
            ->whereHas('assignment', fn($q) => $q->where('type', 'Diagnostic Test'))
            ->with(['assignment.subject', 'assignment'])
            ->get();
        $diagnosticTask = $diagnosticTasks->first();

        return view('student.tasks', compact(
            'student', 'assignments', 'pendingCount', 'dueThisWeekCount', 'catchUpCount',
            'completedCount', 'overdueCount', 'completionRate', 'upcomingDeadlines', 'enrolledSubjects',
            'diagnosticTask', 'diagnosticTasks'
        ));
    }

    public function submitTask(Request $request, $id)
    {
        $student = $this->getStudent();
        $studentAssignment = StudentAssignment::where('student_id', $student->id)
                                             ->where('id', $id)
                                             ->firstOrFail();

        $validated = $request->validate([
            'submission_notes' => 'nullable|string|max:3000',
            'file'             => 'nullable|file|max:30720|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,png,jpg,jpeg',
        ]);

        $filePath = $studentAssignment->file_path;
        $fileName = $studentAssignment->file_name;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $storedPath = $file->store('student_submissions', 'public');
            $filePath = $storedPath;
        }

        $studentAssignment->update([
            'submission_notes' => $validated['submission_notes'] ?? null,
            'file_path'        => $filePath,
            'file_name'        => $fileName,
            'status'           => 'submitted',
            'submitted_at'     => now(),
        ]);

        // 1. Notify Course Teacher
        $course = $studentAssignment->assignment?->course;
        $teacherUserId = $course?->teacher?->user_id;
        $assignmentTitle = $studentAssignment->assignment?->title ?? 'Assignment';
        $studentName = $student->user?->name ?? 'Student';

        if ($teacherUserId) {
            UserNotification::create([
                'user_id' => $teacherUserId,
                'type'    => 'submission',
                'title'   => 'New Student Submission',
                'message' => "{$studentName} submitted the assignment '{$assignmentTitle}' for review.",
            ]);
        }

        // 2. Notify Student
        UserNotification::create([
            'user_id' => auth()->id(),
            'type'    => 'task',
            'title'   => 'Assignment Submitted',
            'message' => "You successfully submitted '{$assignmentTitle}'. Your teacher will review and grade your work.",
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Assignment submitted successfully! Your teacher will review and grade your work.'
            ]);
        }

        return redirect()->back()->with('success', 'Assignment submitted successfully! Your teacher will review and grade your work.');
    }

    public function quiz()
    {
        $student = $this->getStudent();
        $courseIds = Enrollment::where('student_id', $student->id)->pluck('course_id');
        $quiz = Quiz::whereIn('course_id', $courseIds)->first();
        if ($quiz) {
            return redirect()->route('student.quiz.show', $quiz->id);
        }
        return view('student.quiz', compact('student'));
    }

    public function quizShow($id)
    {
        $student = $this->getStudent();
        $quiz = Quiz::with(['course.subject', 'questions'])->findOrFail($id);
        return view('student.quiz', compact('student', 'quiz'));
    }

    public function quizSubmit(Request $request, $id)
    {
        $student = $this->getStudent();
        $quiz = Quiz::findOrFail($id);

        $validated = $request->validate([
            'score'           => 'required|integer',
            'total_questions' => 'required|integer',
        ]);

        StudentQuiz::updateOrCreate([
            'student_id' => $student->id,
            'quiz_id'    => $quiz->id,
        ], [
            'score'           => $validated['score'],
            'total_questions' => $validated['total_questions'],
            'completed_at'    => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function progress()
    {
        $student = $this->getStudent();
        $enrollments = $student->enrollments()->with('course.subject.topics.subtopics', 'course.teacher.user')->get();
        $topicMasteries = StudentTopicMastery::where('student_id', $student->id)->get();
        
        $overallProgress = $enrollments->count() > 0 ? (int) round($enrollments->avg('progress_pct') ?? 0) : 0;
        $attStats = $this->getStudentAttendanceStats($student);

        // Personal performance trajectory chart from actual student quiz records
        $recentQuizzes = StudentQuiz::where('student_id', $student->id)
            ->orderBy('completed_at', 'asc')
            ->take(6)
            ->get();

        if ($recentQuizzes->isNotEmpty()) {
            $performanceLabels = $recentQuizzes->map(fn($q) => $q->completed_at ? $q->completed_at->format('M') : 'Quiz')->toArray();
            $performanceData   = $recentQuizzes->map(fn($q) => $q->total_questions > 0 ? (int) round(($q->score / $q->total_questions) * 100) : 0)->toArray();
        } else {
            $performanceLabels = [now()->format('M')];
            $performanceData   = [0];
        }

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
        
        $submittedAssignmentsCount = $assignments->whereIn('status', ['submitted', 'graded'])->count();
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
                'subtitle'     => $cert->subtitle ?? ($cert->course?->subject?->display_name ?? ($cert->course?->subject?->name ? ($cert->course->subject->name . ' – ' . ($student->level ?? 'Academic')) : 'Olympian Scholars Academy')),
                'subject'      => $cert->course?->subject?->display_name ?? $cert->course?->subject?->name ?? 'Academic Achievement',
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
        $enrolledSubjects = $student ? $student->enrollments->map(fn($e) => $e->course->subject->display_name ?? $e->course->subject->name ?? null)->filter()->unique()->values() : collect();

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

        // Trigger automated official communication
        try {
            \App\Services\Email\EmailAutomationService::trigger(
                'parent_student_linked',
                [
                    'user'    => $parentUser,
                    'parent'  => $parentUser,
                    'student' => $student,
                ],
                "parent_link_{$parentUser->id}_{$student->id}"
            );
        } catch (\Throwable $emailErr) {
            \Illuminate\Support\Facades\Log::warning('[StudentDashboard] Parent linked email trigger failed: ' . $emailErr->getMessage());
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
        $isParentManaged = $student && ($student->parent_user_id || ($student->parents && $student->parents->isNotEmpty()));

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

    private function getStudentAttendanceStats(Student $student): array
    {
        return app(AttendanceService::class)->getStudentAttendanceStats($student);
    }

    /**
     * Secure Join Class Gateway.
     * Records initiated join attempt, calculates attendance status, logs event,
     * and redirects to authorized meeting link.
     */
    public function joinClass(LiveClass $liveClass, Request $request, AttendanceService $attendanceService)
    {
        $user = auth()->user();
        if ($user->role !== 'student') {
            abort(403, 'Unauthorized.');
        }

        $student = Student::where('user_id', $user->id)->firstOrFail();

        // 1. Enforce student billing restriction
        $restrictionService = app(StudentAccessRestrictionService::class);
        if ($restrictionService->isStudentRestricted($student)) {
            $details = $restrictionService->getRestrictionDetails($student);
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error'   => 'payment_restricted',
                    'message' => 'Your academic access is temporarily on hold due to an overdue payment balance.',
                    'details' => $details,
                ], 403);
            }
            return redirect()->route('student.fees')->with('error', 'Your academic access is temporarily on hold due to an overdue payment balance. Please settle your invoice to access live classes.');
        }

        // 2. Validate enrollment (direct or matching subject)
        $isEnrolled = self::getStudentLiveClassesQuery($student)
            ->where('live_classes.id', $liveClass->id)
            ->exists();

        if (!$isEnrolled) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error'   => 'not_enrolled',
                    'message' => 'You are not enrolled in this live class.',
                ], 403);
            }
            return redirect()->route('student.classes')->with('error', 'You are not enrolled in this live class.');
        }

        // 3. Delegate to AttendanceService to record join attempt idempotently
        $result = $attendanceService->recordStudentJoin($student, $liveClass, $request);

        if (!$result['success']) {
            if ($request->wantsJson()) {
                return response()->json($result, 422);
            }
            return redirect()->route('student.classes')->with('error', $result['message']);
        }

        $meetingUrl = $result['meeting_url'] ?: $liveClass->meeting_url;

        if (empty($meetingUrl)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error'   => 'missing_url',
                    'message' => 'The instructor has not provided a live meeting link for this session yet.',
                ], 422);
            }
            return redirect()->route('student.classes')->with('error', 'The instructor has not provided a live meeting link for this session yet.');
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success'      => true,
                'redirect_url' => $meetingUrl,
                'is_rejoin'    => $result['is_rejoin'] ?? false,
            ]);
        }

        return redirect()->away($meetingUrl);
    }

    /**
     * Submit an attendance correction request.
     */
    public function submitCorrectionRequest(Request $request, AttendanceService $attendanceService)
    {
        $user = auth()->user();
        if ($user->role !== 'student') {
            abort(403);
        }

        $validated = $request->validate([
            'live_class_id'    => 'required|exists:live_classes,id',
            'reason'           => 'required|string|max:1000',
            'requested_status' => 'nullable|string|in:attended,excused',
        ]);

        $student = Student::where('user_id', $user->id)->firstOrFail();
        $liveClass = LiveClass::findOrFail($validated['live_class_id']);

        $isEnrolled = self::getStudentLiveClassesQuery($student)
            ->where('live_classes.id', $liveClass->id)
            ->exists();

        if (!$isEnrolled) {
            return response()->json(['success' => false, 'message' => 'You are not enrolled in this class.'], 403);
        }

        $req = $attendanceService->submitCorrectionRequest(
            $student,
            $liveClass,
            $validated['reason'],
            $validated['requested_status'] ?? 'attended'
        );

        return response()->json([
            'success' => true,
            'message' => 'Attendance correction request submitted for teacher review.',
            'request' => $req,
        ]);
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

        $outstandingInvoices = $invoices->filter(fn($inv) => in_array($inv->status, ['outstanding', 'pending_verification']))->values();
        $paidInvoices        = $invoices->filter(fn($inv) => $inv->status === 'paid')->values();
        $overdueInvoices     = $invoices->filter(fn($inv) => $inv->status === 'overdue' || ($inv->status === 'outstanding' && $inv->due_date->isPast()))->values();

        $totalOutstandingAmount = $outstandingInvoices->where('status', 'outstanding')->sum('total_amount');
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
                'status'          => 'pending',
                'paid_at'         => now(),
            ]);

            $invoice->update(['status' => 'pending_verification']);

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
                'status'         => 'paid',
                'paid_at'        => now(),
            ]);

            $invoice->update(['status' => 'paid']);

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
        $this->syncStudentAssignments($student);

        return $student;
    }

    /**
     * Ensure all past and current course assignments + global diagnostic tasks have tracking records for the student.
     */
    private function syncStudentAssignments(Student $student): void
    {
        $student->syncGlobalAndCourseAssignments();
    }
}
