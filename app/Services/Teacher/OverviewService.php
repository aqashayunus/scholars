<?php

namespace App\Services\Teacher;

use App\Models\Assignment;
use App\Models\AttendanceCorrectionRequest;
use App\Models\LiveClass;
use App\Models\Quiz;
use App\Models\StudentAssignment;
use App\Models\StudentAttendance;
use App\Models\StudentQuiz;
use App\Models\Teacher;
use App\Models\TeacherEarning;
use App\Models\TeachingGig;
use Illuminate\Support\Collection;

class OverviewService
{
    /** Load the Teacher record for the logged-in user. */
    public function getTeacher(bool $withEnrollments = true): Teacher
    {
        $with = ['courses.subject'];
        if ($withEnrollments) {
            $with[] = 'courses.enrollments.student.user';
            $with[] = 'courses.assignments.results.student.user';
            $with[] = 'courses.quizzes.results.student.user';
        }

        return Teacher::where('user_id', auth()->id())
                      ->with($with)
                      ->firstOrFail();
    }

    /**
     * Compute this-month attendance stats for all students across the given course IDs.
     * Returns ['total_sessions', 'attended', 'absent', 'late', 'pct']
     */
    public function getAttendanceStats(Collection $courseIds): array
    {
        $start = now()->startOfMonth();
        $end   = now()->endOfMonth();

        $records = StudentAttendance::whereHas('liveClass', fn ($q) =>
            $q->whereIn('course_id', $courseIds)
              ->whereBetween('scheduled_at', [$start, $end])
        )->get();

        $total    = $records->count();
        $attended = $records->whereIn('status', ['attended', 'late'])->count();
        $absent   = $records->where('status', 'absent')->count();
        $late     = $records->where('status', 'late')->count();
        $pct      = $total > 0 ? round($attended / $total * 100) : 0;

        return compact('total', 'attended', 'absent', 'late', 'pct');
    }

    /**
     * Build the student data array from real DB data for the students page.
     */
    public function buildStudentData(Teacher $teacher): array
    {
        $studentsData = [];
        $start = now()->startOfMonth();
        $end   = now()->endOfMonth();

        $courseIds = $teacher->courses->pluck('id');
        $studentIds = $teacher->courses->flatMap(fn ($c) => $c->enrollments->pluck('student_id'))->unique();

        // Attendance this month, batched per (student_id, course_id) instead of
        // one query per student per course.
        $attByKey = StudentAttendance::whereIn('student_id', $studentIds)
            ->whereHas('liveClass', fn ($q) => $q->whereIn('course_id', $courseIds)->whereBetween('scheduled_at', [$start, $end]))
            ->with('liveClass:id,course_id')
            ->get()
            ->groupBy(fn ($a) => $a->student_id . ':' . $a->liveClass->course_id);

        // Pending task counts, batched per (student_id, course_id).
        $pendingByKey = StudentAssignment::whereIn('student_id', $studentIds)
            ->where('status', 'pending')
            ->whereHas('assignment', fn ($q) => $q->whereIn('course_id', $courseIds))
            ->with('assignment:id,course_id')
            ->get()
            ->groupBy(fn ($sa) => $sa->student_id . ':' . $sa->assignment->course_id)
            ->map(fn ($group) => $group->count());

        // Latest quiz result per (student_id, course_id) — fetch all, ordered
        // newest-first, and keep only the first occurrence of each key.
        $latestQuizByKey = StudentQuiz::whereIn('student_id', $studentIds)
            ->whereHas('quiz', fn ($q) => $q->whereIn('course_id', $courseIds))
            ->with('quiz:id,course_id')
            ->latest('completed_at')
            ->get()
            ->unique(fn ($sq) => $sq->student_id . ':' . $sq->quiz->course_id)
            ->keyBy(fn ($sq) => $sq->student_id . ':' . $sq->quiz->course_id);

        foreach ($teacher->courses as $course) {
            foreach ($course->enrollments as $enrollment) {
                $student = $enrollment->student;
                $sUser   = $student->user;
                $sid     = $student->id;

                if (isset($studentsData[$sid])) continue;

                $key = $sid . ':' . $course->id;

                // Real attendance this month
                $attRecords  = $attByKey->get($key, collect());
                $attTotal    = $attRecords->count();
                $attAttended = $attRecords->whereIn('status', ['attended', 'late'])->count();
                $attPct      = $attTotal > 0 ? round($attAttended / $attTotal * 100) : 0;

                // Real progress
                $progressPct = (int) ($enrollment->progress_pct ?? 0);

                // Real pending tasks
                $pendingTasks = $pendingByKey->get($key, 0);

                // Latest quiz score
                $latestQuiz  = $latestQuizByKey->get($key);
                $latestScore = $latestQuiz ? $latestQuiz->percent . '%' : '–';
                $latestDate  = $latestQuiz ? $latestQuiz->completed_at->format('d M Y') : '–';

                // Determine status
                $category = 'good';
                $status   = 'Good';
                if ($attPct < 80 || $progressPct < 60) {
                    $category = 'attention';
                    $status   = 'Needs Attention';
                }
                if ($progressPct < 55) {
                    $category = 'intervention';
                    $status   = 'Intervention';
                }
                if ($pendingTasks > 2) {
                    $category = 'catchup';
                    $status   = 'Needs Attention';
                }

                $studentsData[$sid] = [
                    'id'               => 'S24' . str_pad($sid, 3, '0', STR_PAD_LEFT),
                    'name'             => $sUser->name,
                    'class'            => $course->level,
                    'subject'          => $course->subject->name ?? 'General',
                    'attendance'       => $attPct,
                    'progress'         => $progressPct,
                    'pendingTasks'     => $pendingTasks > 0 ? "{$pendingTasks} Pending" : "0 Pending",
                    'latestAssessment' => $latestScore,
                    'latestDate'       => $latestDate,
                    'status'           => $status,
                    'category'         => $category,
                    'strengths'        => 'Basic concepts',
                    'weaknesses'       => 'Advanced application',
                    'catchup'          => $pendingTasks > 0 ? 'Pending Tasks' : 'None',
                    'intervention'     => $category === 'intervention' ? 'Active Support' : 'None',
                    'note'             => 'Progress tracked from enrollment data.',
                ];
            }
        }

        return array_values($studentsData);
    }

    public function dashboardData(): array
    {
        $teacher = $this->getTeacher();

        $courses       = $teacher->courses;
        $courseIds     = $courses->pluck('id');
        $totalStudents = $courses->sum(fn($c) => $c->enrollments->count());

        $todaysClasses = LiveClass::where(function($q) use ($courseIds, $teacher) {
                                      $q->whereIn('course_id', $courseIds)->orWhere('teacher_id', $teacher->id);
                                  })
                                  ->whereDate('scheduled_at', today())
                                  ->with('course.subject')
                                  ->orderBy('scheduled_at')
                                  ->get();

        $upcomingClass = LiveClass::where(function($q) use ($courseIds, $teacher) {
                                      $q->whereIn('course_id', $courseIds)->orWhere('teacher_id', $teacher->id);
                                  })
                                  ->where('scheduled_at', '>', now())
                                  ->with('course.subject')
                                  ->orderBy('scheduled_at')
                                  ->first();

        $attStats = $this->getAttendanceStats($courseIds);

        $pendingReviews = StudentAssignment::whereHas('assignment', fn($q) =>
                $q->whereIn('course_id', $courseIds)
            )
            ->where('status', 'submitted')
            ->with('assignment.course.subject', 'student.user')
            ->orderByDesc('submitted_at')
            ->take(5)
            ->get();
        $pendingReviewCount = StudentAssignment::whereHas('assignment', fn($q) =>
                $q->whereIn('course_id', $courseIds)
            )->where('status', 'submitted')->count();

        $catchUpStudents = StudentAttendance::where('status', 'absent')
            ->whereHas('liveClass', fn($q) =>
                $q->whereIn('course_id', $courseIds)
                  ->whereBetween('scheduled_at', [now()->startOfMonth(), now()])
            )
            ->with('student.user', 'liveClass.course.subject')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $allStudents = $this->buildStudentData($teacher);
        $attentionStudents = array_filter($allStudents, fn($s) => $s['category'] !== 'good');

        $performanceBySubject = $courses->map(fn($c) => [
            'subject' => $c->subject->name ?? 'N/A',
            'avg'     => (int) round($c->enrollments->avg('progress_pct') ?? 0),
        ]);

        $openGigsCount = TeachingGig::where('status', 'open')->count();
        $claimedGigs   = TeachingGig::where('assigned_teacher_id', $teacher->id)
                                    ->whereIn('status', ['claimed', 'checked_in'])
                                    ->orderBy('scheduled_at')
                                    ->get();

        $recentEarnings = TeacherEarning::where('teacher_id', $teacher->id)
                                         ->orderByDesc('id')
                                         ->take(5)
                                         ->get();

        $announcements = [
            ['date' => now()->subDays(1)->format('d M'), 'text' => 'Aster Edu Dispatch: High-demand SPM substitute slots available.'],
            ['date' => now()->subDays(3)->format('d M'), 'text' => 'Monthly Aster Edu tutor schedules updated.'],
            ['date' => now()->subDays(5)->format('d M'), 'text' => 'Tutor Verification status: Active & Verified under Aster Edu.'],
        ];

        return compact(
            'teacher', 'courses', 'totalStudents', 'todaysClasses',
            'upcomingClass', 'openGigsCount', 'claimedGigs', 'recentEarnings',
            'attStats', 'pendingReviews', 'pendingReviewCount',
            'catchUpStudents', 'attentionStudents', 'performanceBySubject', 'announcements'
        );
    }

    public function classesData(): array
    {
        $teacher = $this->getTeacher();

        $courseIds   = $teacher->courses->pluck('id');
        $attStats    = $this->getAttendanceStats($courseIds);

        $upcomingClasses = LiveClass::where(function($q) use ($courseIds, $teacher) {
                $q->whereIn('course_id', $courseIds)->orWhere('teacher_id', $teacher->id);
            })
            ->where('scheduled_at', '>=', now())
            ->with('course.subject')
            ->orderBy('scheduled_at')
            ->take(10)
            ->get();

        $pastClasses = LiveClass::where(function($q) use ($courseIds, $teacher) {
                $q->whereIn('course_id', $courseIds)->orWhere('teacher_id', $teacher->id);
            })
            ->where('scheduled_at', '<', now())
            ->with('course.subject')
            ->orderByDesc('scheduled_at')
            ->take(10)
            ->get();

        return compact('teacher', 'attStats', 'upcomingClasses', 'pastClasses');
    }

    public function manageClassData(int $id): array
    {
        $teacher = $this->getTeacher();

        $course = $teacher->courses()->where('id', $id)->with([
            'subject.topics',
            'learningMaterials',
            'assignments.results',
            'enrollments.student.user',
            'liveClasses' => fn($q) => $q->orderBy('scheduled_at', 'asc'),
        ])->first();

        if (!$course) {
            abort(404, 'Class not found or you do not have permission.');
        }

        $now = now();

        // 1. Dynamically identify any active live session currently running for this course:
        // Excludes cancelled or completed sessions; requires valid meeting_url; must be scheduled for today;
        // active window starts 15 minutes before scheduled start time and ends at scheduled end time.
        $liveSession = $course->liveClasses->filter(function ($lc) use ($now) {
            if (in_array($lc->getRawOriginal('status'), ['cancelled', 'completed'])) {
                return false;
            }
            if (empty($lc->meeting_url)) {
                return false;
            }
            if (!$lc->scheduled_at) {
                return false;
            }
            $start = $lc->scheduled_at;
            $duration = (int) ($lc->duration_minutes ?: 60);
            $end = $start->copy()->addMinutes($duration);

            if ($lc->getRawOriginal('status') === 'live') {
                return $start->isToday() && $now->lte($end->copy()->addMinutes(15));
            }

            return $start->isToday() && $now->gte($start->copy()->subMinutes(15)) && $now->lte($end);
        })->sortBy('scheduled_at')->first();

        $isLiveRunning = (bool) $liveSession;

        // 2. Next upcoming live class (fallback to liveSession or next chronologically)
        $nextClass = $liveSession ?? $course->liveClasses->filter(function ($lc) use ($now) {
            if (in_array($lc->getRawOriginal('status'), ['cancelled', 'completed'])) {
                return false;
            }
            if (!$lc->scheduled_at) {
                return false;
            }
            $end = $lc->scheduled_at->copy()->addMinutes($lc->duration_minutes ?: 60);
            return $now->lt($end);
        })->sortBy('scheduled_at')->first() ?? $course->liveClasses->filter(
            fn ($lc) => $lc->getRawOriginal('status') !== 'cancelled'
        )->sortByDesc('scheduled_at')->first();

        // Real attendance stats for this course
        $liveClassIds = $course->liveClasses->pluck('id');
        $totalAttendanceRecords = StudentAttendance::whereIn('live_class_id', $liveClassIds)->count();
        $presentRecords = StudentAttendance::whereIn('live_class_id', $liveClassIds)->whereIn('status', ['attended', 'late'])->count();
        $avgAttendancePct = $totalAttendanceRecords > 0 ? (int) round(($presentRecords / $totalAttendanceRecords) * 100) : 100;

        // Class Progress Average & Below Target Count
        $classAvgProgress = $course->enrollments->count() > 0 ? (int) round($course->enrollments->avg('progress_pct') ?? 0) : 0;
        $studentsBelowTarget = $course->enrollments->filter(fn($e) => ($e->progress_pct ?? 0) < 60 && ($e->progress_pct ?? 0) > 0)->count();

        // Real Subject Topics
        $topics = $course->subject?->topics ?? collect();

        // Eager-load liveClasses with attendances for session tracking
        $course->load(['liveClasses.attendances.student.user']);

        $activeSession = $liveSession
            ?? $nextClass
            ?? $course->liveClasses->where('scheduled_at', '<=', now())->sortByDesc('scheduled_at')->first()
            ?? $course->liveClasses->first();

        // Pending and recent correction requests for this course's live classes
        $correctionRequests = AttendanceCorrectionRequest::whereIn('live_class_id', $liveClassIds)
            ->with(['student.user', 'liveClass'])
            ->latest()
            ->get();

        return compact(
            'teacher', 'course', 'nextClass', 'activeSession', 'liveSession', 'isLiveRunning',
            'avgAttendancePct', 'classAvgProgress', 'studentsBelowTarget', 'topics', 'correctionRequests'
        );
    }

    public function studentsData(): array
    {
        $teacher        = $this->getTeacher();
        $mappedStudents = $this->buildStudentData($teacher);

        return compact('teacher', 'mappedStudents');
    }

    public function monitoringData(): array
    {
        $teacher = $this->getTeacher();

        $courseIds     = $teacher->courses->pluck('id');
        $totalStudents = $teacher->courses->sum(fn($c) => $c->enrollments->count());
        $attStats      = $this->getAttendanceStats($courseIds);

        // Per-subject performance
        $performanceBySubject = $teacher->courses->map(function ($c) {
            $totalEnrollments = $c->enrollments->count();
            $taskCount = $c->assignments->count();
            $tasksCompleted = StudentAssignment::whereIn('assignment_id', $c->assignments->pluck('id'))
                ->whereIn('status', ['submitted', 'graded'])
                ->count();
            $totalExpectedTasks = $totalEnrollments * max(1, $taskCount);
            $taskRate = $totalExpectedTasks > 0 ? (int) round(($tasksCompleted / $totalExpectedTasks) * 100) : 0;

            $avgProgress = (int) round($c->enrollments->avg('progress_pct') ?? 0);
            if ($avgProgress === 0 && $taskRate > 0) {
                $avgProgress = $taskRate;
            }

            // Attendance for this course
            $liveClassIds = $c->liveClasses->pluck('id');
            $attRecords = StudentAttendance::whereIn('live_class_id', $liveClassIds)->get();
            $courseAttPct = $attRecords->count() > 0
                ? (int) round($attRecords->whereIn('status', ['attended', 'late'])->count() / $attRecords->count() * 100)
                : 100;

            return [
                'course_id'    => $c->id,
                'title'        => $c->title,
                'level'        => $c->level,
                'subject'      => $c->subject->display_name ?? $c->subject->name ?? 'General',
                'avg_progress' => $avgProgress,
                'enrolled'     => $totalEnrollments,
                'tasks_count'  => $taskCount,
                'att_pct'      => $courseAttPct,
            ];
        });

        // All students with real data
        $allStudents = $this->buildStudentData($teacher);

        // Absent students this month
        $catchUpStudents = StudentAttendance::where('status', 'absent')
            ->whereHas('liveClass', fn($q) =>
                $q->whereIn('course_id', $courseIds)
                  ->whereBetween('scheduled_at', [now()->startOfMonth(), now()])
            )
            ->with('student.user', 'liveClass.course.subject')
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        // Additional stats for monitoring page
        $totalAssessments = Assignment::whereIn('course_id', $courseIds)->count()
            + Quiz::whereIn('course_id', $courseIds)->count();

        $totalSubmissions = StudentAssignment::whereHas('assignment', fn($q) => $q->whereIn('course_id', $courseIds))
            ->whereIn('status', ['submitted', 'graded'])
            ->count();

        $totalExpectedSubmissions = StudentAssignment::whereHas('assignment', fn($q) => $q->whereIn('course_id', $courseIds))->count();
        $tasksCompletedPct = $totalExpectedSubmissions > 0 ? (int) round(($totalSubmissions / $totalExpectedSubmissions) * 100) : 100;

        $totalLiveClassesHeld = LiveClass::where(function ($q) use ($courseIds, $teacher) {
                $q->whereIn('course_id', $courseIds)->orWhere('teacher_id', $teacher->id);
            })
            ->where('scheduled_at', '<', now())
            ->where('status', '!=', 'cancelled')
            ->count();

        $attentionStudents = collect($allStudents)->filter(fn($s) => $s['category'] !== 'good')->values()->all();
        $interventionStudents = collect($allStudents)->filter(fn($s) => $s['category'] === 'intervention')->values()->all();

        return compact(
            'teacher', 'totalStudents', 'attStats',
            'performanceBySubject', 'allStudents', 'catchUpStudents',
            'totalAssessments', 'tasksCompletedPct', 'totalLiveClassesHeld',
            'attentionStudents', 'interventionStudents'
        );
    }
}
