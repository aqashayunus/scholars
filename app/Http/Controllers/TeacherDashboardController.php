<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LearningMaterial;
use App\Models\LiveClass;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\RecordedClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentAssignment;
use App\Models\StudentQuiz;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherEarning;
use App\Models\TeachingGig;
use App\Models\ClassRequest;
use App\Models\UserNotification;
use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceEvent;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class TeacherDashboardController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // SHARED HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /** Load the Teacher record for the logged-in user. */
    private function getTeacher(bool $withEnrollments = true): Teacher
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
     * Compute attendance stats for all students across the given course IDs.
     * Returns ['total', 'attended', 'absent', 'late', 'pct', 'period_label']
     */
    private function getAttendanceStats(Collection $courseIds): array
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
        $pct      = $total > 0 ? (int) round($attended / $total * 100) : 0;
        $period_label = "{$attended}/{$total} this month";

        // If no records this month yet, check all-time so stats don't display a misleading 0%
        if ($total === 0) {
            $allRecords = StudentAttendance::whereHas('liveClass', fn ($q) =>
                $q->whereIn('course_id', $courseIds)
            )->get();
            if ($allRecords->count() > 0) {
                $allAtt = $allRecords->whereIn('status', ['attended', 'late'])->count();
                $pct = (int) round($allAtt / $allRecords->count() * 100);
                $period_label = "{$allAtt}/{$allRecords->count()} all-time";
                $attended = $allAtt;
                $total = $allRecords->count();
            } else {
                $pct = 100;
                $period_label = "No sessions recorded yet";
            }
        }

        return compact('total', 'attended', 'absent', 'late', 'pct', 'period_label');
    }

    /**
     * Build the student data array from real DB data for the students & monitoring pages.
     */
    private function buildStudentData(Teacher $teacher): array
    {
        $studentsData = [];
        $colors = ['bg-blue-600', 'bg-purple-600', 'bg-emerald-600', 'bg-amber-600', 'bg-rose-600', 'bg-indigo-600', 'bg-teal-600'];

        foreach ($teacher->courses as $course) {
            foreach ($course->enrollments as $enrollment) {
                $student = $enrollment->student;
                if (!$student || !$student->user) continue;
                $sUser   = $student->user;
                $sid     = $student->id;

                if (isset($studentsData[$sid])) continue;

                // Courses enrolled with this teacher
                $studentCourses = $teacher->courses->filter(function($c) use ($student) {
                    return $c->enrollments->contains('student_id', $student->id);
                });

                $subjectsList = $studentCourses->map(fn($c) => $c->subject->display_name ?? $c->subject->name ?? $c->title)->filter()->unique()->values()->join(', ');
                $levelsList   = $studentCourses->pluck('level')->filter()->unique()->values()->join(', ');

                // Attendance across student's enrolled courses with this teacher
                $pastClasses = LiveClass::whereIn('course_id', $studentCourses->pluck('id'))
                    ->where('scheduled_at', '<', now())
                    ->where('status', '!=', 'cancelled')
                    ->get();
                
                $pastClassIds = $pastClasses->pluck('id');
                $attRecords = StudentAttendance::where('student_id', $sid)
                    ->whereIn('live_class_id', $pastClassIds)
                    ->get();

                $attAttended = $attRecords->whereIn('status', ['attended', 'late'])->count();
                $attAbsent   = $attRecords->where('status', 'absent')->count();
                $counted     = $attAttended + $attAbsent;
                $attPct      = $counted > 0 ? (int) round(($attAttended / $counted) * 100) : 100;

                // Real tasks & progress
                $allCourseAssignments = Assignment::whereIn('course_id', $studentCourses->pluck('id'))->get();
                $totalCourseTasks     = $allCourseAssignments->count();

                $studentAssignments = StudentAssignment::where('student_id', $sid)
                    ->whereIn('assignment_id', $allCourseAssignments->pluck('id'))
                    ->get();

                $completedTasks = $studentAssignments->whereIn('status', ['submitted', 'graded'])->count();
                $pendingTasks   = $studentAssignments->where('status', 'pending')->count();
                $overdueTasks   = $studentAssignments->filter(function($sa) {
                    return $sa->status === 'pending' && $sa->assignment && $sa->assignment->due_date && $sa->assignment->due_date->lt(now());
                })->count();

                // Progress: average of enrollment progress or task completion
                $enrProgressAvg = (int) round($student->enrollments->whereIn('course_id', $studentCourses->pluck('id'))->avg('progress_pct') ?? 0);
                if ($enrProgressAvg === 0 && $totalCourseTasks > 0) {
                    $progressPct = (int) round(($completedTasks / $totalCourseTasks) * 100);
                } else {
                    $progressPct = max($enrProgressAvg, $totalCourseTasks > 0 ? (int) round(($completedTasks / $totalCourseTasks) * 100) : 0);
                }

                // Latest quiz or assessment score
                $latestQuiz = $student->quizResults()
                    ->whereHas('quiz', fn($q) => $q->whereIn('course_id', $studentCourses->pluck('id')))
                    ->latest('completed_at')
                    ->first();

                $latestScore = $latestQuiz ? $latestQuiz->percent . '%' : ($studentAssignments->whereNotNull('score')->first() ? $studentAssignments->whereNotNull('score')->first()->score . '%' : '–');
                $latestDate  = $latestQuiz ? $latestQuiz->completed_at->format('d M Y') : ($studentAssignments->whereNotNull('score')->first() && $studentAssignments->whereNotNull('score')->first()->submitted_at ? $studentAssignments->whereNotNull('score')->first()->submitted_at->format('d M Y') : '–');

                // Recent activities
                $recentActivities = [];
                foreach ($studentAssignments->take(3) as $sa) {
                    $recentActivities[] = [
                        'type'        => 'task',
                        'title'       => $sa->assignment->title ?? 'Assignment Task',
                        'badge'       => $sa->status === 'graded' ? 'Graded (' . ($sa->score ?? 0) . '%)' : ucfirst($sa->status),
                        'date'        => $sa->submitted_at ? $sa->submitted_at->format('d M Y') : $sa->created_at->format('d M Y'),
                        'color'       => $sa->status === 'graded' ? 'emerald' : ($sa->status === 'submitted' ? 'blue' : 'amber'),
                    ];
                }
                foreach ($attRecords->take(2) as $att) {
                    $recentActivities[] = [
                        'type'        => 'attendance',
                        'title'       => $att->liveClass->title ?? 'Live Class Session',
                        'badge'       => ucfirst($att->status),
                        'date'        => $att->liveClass && $att->liveClass->scheduled_at ? $att->liveClass->scheduled_at->format('d M Y') : $att->created_at->format('d M Y'),
                        'color'       => $att->status === 'attended' ? 'emerald' : ($att->status === 'late' ? 'amber' : 'red'),
                    ];
                }

                // Determine category & status
                $category = 'good';
                $status   = 'Good';
                $issue    = null;

                if ($attPct < 80 || $progressPct < 60) {
                    $category = 'attention';
                    $status   = 'Needs Attention';
                    $issue    = $attPct < 80 ? "Low Attendance ({$attPct}%)" : "Progress Below Target ({$progressPct}%)";
                }
                if ($progressPct < 55) {
                    $category = 'intervention';
                    $status   = 'Intervention';
                    $issue    = "Progress Critical ({$progressPct}%)";
                }
                if ($attAbsent > 0) {
                    $category = 'catchup';
                    $status   = 'Needs Attention';
                    $issue    = "{$attAbsent} Absent Session(s) — Catch-Up Pending";
                }
                if ($overdueTasks > 0) {
                    $category = 'attention';
                    $status   = 'Needs Attention';
                    $issue    = "{$overdueTasks} Overdue Task(s)";
                }

                // Initials & color
                $words = explode(' ', trim($sUser->name));
                $initials = strtoupper(substr($words[0] ?? 'S', 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
                $color = $colors[$sid % count($colors)];

                $studentsData[$sid] = [
                    'id'               => 'S24' . str_pad($sid, 3, '0', STR_PAD_LEFT),
                    'student_id'       => $sid,
                    'name'             => $sUser->name,
                    'initials'         => $initials,
                    'avatar_color'     => $color,
                    'class'            => $levelsList ?: $course->level,
                    'subject'          => $subjectsList ?: ($course->subject->display_name ?? $course->subject->name ?? 'General'),
                    'attendance'       => $attPct,
                    'attended_count'   => $attAttended,
                    'absent_count'     => $attAbsent,
                    'total_classes'    => $pastClassIds->count(),
                    'progress'         => $progressPct,
                    'completed_tasks'  => $completedTasks,
                    'total_tasks'      => $totalCourseTasks,
                    'pending_tasks'    => $pendingTasks,
                    'pendingTasks'     => "{$pendingTasks} Pending",
                    'overdue_tasks'    => $overdueTasks,
                    'latestAssessment' => $latestScore,
                    'latestDate'       => $latestDate,
                    'status'           => $status,
                    'category'         => $category,
                    'issue'            => $issue,
                    'strengths'        => $progressPct >= 70 ? 'Consistent submission & engagement' : 'Foundational concepts',
                    'weaknesses'       => $attPct < 85 ? 'Live class attendance regularity' : ($progressPct < 60 ? 'Homework task completion' : 'Advanced application'),
                    'catchup'          => $attAbsent > 0 ? "{$attAbsent} Session(s)" : 'None',
                    'intervention'     => $category === 'intervention' ? 'Active Support' : 'None',
                    'note'             => $category !== 'good' ? "Flagged for teacher follow-up: {$issue}." : 'Active learner, on track with course schedule.',
                    'noteDate'         => now()->format('d M Y'),
                    'recentActivities' => $recentActivities,
                ];
            }
        }

        return array_values($studentsData);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PAGES
    // ─────────────────────────────────────────────────────────────────────────

    public function dashboard()
    {
        $user    = auth()->user();
        $teacher = $this->getTeacher();

        $courses       = $teacher->courses;
        $courseIds     = $courses->pluck('id');
        $totalStudents = $courses->sum(fn($c) => $c->enrollments->count());

        $now = now();

        // All active live classes for this teacher (or teacher's courses), excluding cancelled sessions
        $allLiveClasses = LiveClass::where(function($q) use ($courseIds, $teacher) {
                                      $q->whereIn('course_id', $courseIds)->orWhere('teacher_id', $teacher->id);
                                  })
                                  ->where('status', '!=', 'cancelled')
                                  ->with('course.subject')
                                  ->orderBy('scheduled_at')
                                  ->get();

        // Today's active classes (running or upcoming today)
        $todaysClasses = $allLiveClasses->filter(function($lc) use ($now) {
            if (!$lc->scheduled_at) return false;
            $end = $lc->scheduled_at->copy()->addMinutes($lc->duration_minutes ?: 60);
            return $lc->scheduled_at->isToday() && $end->gte($now);
        });

        // Next upcoming class
        $upcomingClass = $allLiveClasses->first(function($lc) use ($now) {
            return $lc->scheduled_at && $lc->scheduled_at->gt($now);
        });

        // Build list for the restructured "Today's Classes" card:
        // Exclusively displays running and upcoming classes.
        // Shows "Join class" button if class is running or within 30 minutes before class start time.
        // Past classes (end_time < now) are strictly excluded.
        $activeLiveClasses = $allLiveClasses->filter(function($lc) use ($now) {
            if (!$lc->scheduled_at) return false;
            $end = $lc->scheduled_at->copy()->addMinutes($lc->duration_minutes ?: 60);
            return $end->gte($now);
        });

        $dashboardClasses = $activeLiveClasses->map(function($lc) use ($now) {
            $duration = (int) ($lc->duration_minutes ?: 60);
            $start = $lc->scheduled_at;
            $end = $start->copy()->addMinutes($duration);
            $joinWindowStart = $start->copy()->subMinutes(30);

            // Join button appears 30 minutes before class start time, and remains until class ends
            $canJoin = $now->gte($joinWindowStart) && $now->lte($end);
            $isLive = $now->gte($start) && $now->lte($end);
            $isStartingSoon = $now->gte($joinWindowStart) && $now->lt($start);

            if ($isLive) {
                $statusType   = 'live';
                $badgeText    = 'Live Now';
                $scheduleText = 'Started ' . $start->format('g:i A') . ' • Ends ' . $end->format('g:i A');
            } elseif ($isStartingSoon) {
                $statusType   = 'starting_soon';
                $minsLeft     = (int) ceil($now->diffInSeconds($start) / 60);
                $badgeText    = $minsLeft <= 1 ? 'Starts in 1 min' : "Starts in {$minsLeft} mins";
                $scheduleText = 'Starts at ' . $start->format('g:i A') . ' (' . $duration . ' mins)';
            } else {
                $statusType   = 'upcoming';
                $badgeText    = null;
                $scheduleText = $start->isToday() 
                    ? 'Today, ' . $start->format('g:i A') 
                    : ($start->isTomorrow() 
                        ? 'Tomorrow, ' . $start->format('g:i A') 
                        : $start->format('D, j M • g:i A'));
            }

            return [
                'id'               => $lc->id,
                'course_id'        => $lc->course_id,
                'title'            => $lc->course ? ($lc->course->subject->display_name ?? ($lc->course->level . ' ' . ($lc->course->subject->name ?? ''))) : $lc->title,
                'subtitle'         => $lc->title ?: ($lc->course->title ?? 'Session'),
                'subject_name'     => strtolower($lc->course->subject->name ?? 'general'),
                'can_join'         => $canJoin,
                'is_live'          => $isLive,
                'is_starting_soon' => $isStartingSoon,
                'status_type'      => $statusType,
                'badge_text'       => $badgeText,
                'schedule_text'    => $scheduleText,
                'meeting_url'      => $lc->course_id ? route('teacher.classes.show', $lc->course_id) : ($lc->meeting_url ?: '#'),
                'scheduled_at'     => $start,
                'join_window_ms'   => $joinWindowStart->timestamp * 1000,
                'start_ms'         => $start->timestamp * 1000,
                'end_ms'           => $end->timestamp * 1000,
                'duration'         => $duration,
            ];
        });

        // Float currently joinable / live classes to top, then sort chronologically
        $dashboardClasses = $dashboardClasses->sortBy([
            ['can_join', 'desc'],
            ['scheduled_at', 'asc'],
        ])->values();

        // Real attendance stats
        $attStats = $this->getAttendanceStats($courseIds);

        // Assignments pending review (submitted but not scored)
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

        // Students who missed classes this month (absent records)
        $catchUpStudents = StudentAttendance::where('status', 'absent')
            ->whereHas('liveClass', fn($q) =>
                $q->whereIn('course_id', $courseIds)
                  ->whereBetween('scheduled_at', [now()->startOfMonth(), now()])
            )
            ->with('student.user', 'liveClass.course.subject')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        // Students needing attention (progress < 60% or attendance < 80%)
        $allStudents = $this->buildStudentData($teacher);
        $attentionStudents = array_filter($allStudents, fn($s) => $s['category'] !== 'good');

        // Per-subject average progress (real from enrollments)
        $performanceBySubject = $courses->map(fn($c) => [
            'subject' => $c->subject->display_name ?? $c->subject->name ?? 'N/A',
            'avg'     => (int) round($c->enrollments->avg('progress_pct') ?? 0),
        ]);

        // Gig platform
        $openGigsCount = TeachingGig::where('status', 'open')->count();
        $claimedGigs   = TeachingGig::where('assigned_teacher_id', $teacher->id)
                                    ->whereIn('status', ['claimed', 'checked_in'])
                                    ->orderBy('scheduled_at')
                                    ->get();

        $recentEarnings = TeacherEarning::where('teacher_id', $teacher->id)
                                         ->orderByDesc('id')
                                         ->take(5)
                                         ->get();

        // Announcements (static – no db table)
        $announcements = [
            ['date' => now()->subDays(1)->format('d M'), 'text' => 'Aster Edu Dispatch: High-demand SPM substitute slots available.'],
            ['date' => now()->subDays(3)->format('d M'), 'text' => 'Monthly Aster Edu tutor schedules updated.'],
            ['date' => now()->subDays(5)->format('d M'), 'text' => 'Tutor Verification status: Active & Verified under Aster Edu.'],
        ];

        return view('teacher.dashboard', compact(
            'teacher', 'courses', 'totalStudents', 'todaysClasses', 'dashboardClasses',
            'upcomingClass', 'openGigsCount', 'claimedGigs', 'recentEarnings',
            'attStats', 'pendingReviews', 'pendingReviewCount',
            'catchUpStudents', 'attentionStudents', 'performanceBySubject', 'announcements'
        ));
    }

    public function classes()
    {
        $user    = auth()->user();
        $teacher = $this->getTeacher();

        $courseIds   = $teacher->courses->pluck('id');
        $attStats    = $this->getAttendanceStats($courseIds);

        // Upcoming & active live classes
        $upcomingClasses = LiveClass::where(function($q) use ($courseIds, $teacher) {
                $q->whereIn('course_id', $courseIds)->orWhere('teacher_id', $teacher->id);
            })
            ->activeOrUpcoming()
            ->with('course.subject')
            ->orderBy('scheduled_at')
            ->get();

        // Past classes
        $pastClasses = LiveClass::where(function($q) use ($courseIds, $teacher) {
                $q->whereIn('course_id', $courseIds)->orWhere('teacher_id', $teacher->id);
            })
            ->completedOrPast()
            ->with('course.subject')
            ->orderByDesc('scheduled_at')
            ->take(20)
            ->get();

        return view('teacher.classes', compact('teacher', 'attStats', 'upcomingClasses', 'pastClasses'));
    }

    public function manageClass($id)
    {
        $user    = auth()->user();
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
        $liveSession = $course->liveClasses->filter(function($lc) use ($now) {
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
        $nextClass = $liveSession ?? $course->liveClasses->filter(function($lc) use ($now) {
            if (in_array($lc->getRawOriginal('status'), ['cancelled', 'completed'])) {
                return false;
            }
            if (!$lc->scheduled_at) {
                return false;
            }
            $end = $lc->scheduled_at->copy()->addMinutes($lc->duration_minutes ?: 60);
            return $now->lt($end);
        })->sortBy('scheduled_at')->first() ?? $course->liveClasses->where('status', '!=', 'cancelled')->sortByDesc('scheduled_at')->first();

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

        return view('teacher.class-workspace', compact(
            'teacher', 'course', 'nextClass', 'activeSession', 'liveSession', 'isLiveRunning', 'avgAttendancePct', 'classAvgProgress', 'studentsBelowTarget', 'topics', 'correctionRequests'
        ));
    }

    public function updateMeetingUrl(Request $request, $id)
    {
        $teacher = $this->getTeacher(false);
        $courseIds = $teacher->courses->pluck('id')->toArray();
        $targetId = $request->input('live_class_id', $id);

        $liveClass = LiveClass::where(function($q) use ($courseIds, $teacher) {
                $q->whereIn('course_id', $courseIds)->orWhere('teacher_id', $teacher->id);
            })
            ->where(function($q) use ($targetId, $id) {
                $q->where('id', $targetId)
                  ->orWhere('id', $id)
                  ->orWhere('course_id', $id);
            })
            ->orderByRaw("CASE WHEN id = ? THEN 0 ELSE 1 END", [$targetId])
            ->latest('scheduled_at')
            ->firstOrFail();

        $validated = $request->validate([
            'meeting_url' => 'required|url|max:1000',
        ]);

        $liveClass->update([
            'meeting_url' => $validated['meeting_url'],
            'status'      => 'live',
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success'     => true,
                'meeting_url' => $liveClass->meeting_url,
                'message'     => 'Live meeting link saved successfully!',
            ]);
        }

        return redirect()->back()->with('success', 'Live meeting link saved successfully! Students can now join.');
    }

    /**
     * Save attendance records for a live class session from Teacher Class Workspace.
     */
    public function saveAttendance(LiveClass $liveClass, Request $request, AttendanceService $attendanceService)
    {
        $teacher = $this->getTeacher();

        // Authorization: verify teacher is assigned to this live class or course
        if ($liveClass->teacher_id !== $teacher->id && $liveClass->course?->teacher_id !== $teacher->id) {
            abort(403, 'You are not authorized to manage attendance for this class.');
        }

        $validated = $request->validate([
            'records'              => 'required|array',
            'records.*.student_id' => 'required|integer|exists:students,id',
            'records.*.status'     => 'required|string',
            'records.*.remarks'    => 'nullable|string|max:500',
        ]);

        $result = $attendanceService->saveTeacherAttendance($teacher, $liveClass, $validated['records'], auth()->user());

        return response()->json($result);
    }

    /**
     * Review an attendance correction request (approve or reject).
     */
    public function reviewCorrection(AttendanceCorrectionRequest $correctionRequest, Request $request, AttendanceService $attendanceService)
    {
        $teacher = $this->getTeacher();
        $liveClass = $correctionRequest->liveClass;

        // Authorization: verify teacher owns the live class
        if ($liveClass->teacher_id !== $teacher->id && $liveClass->course?->teacher_id !== $teacher->id) {
            abort(403, 'You are not authorized to review this request.');
        }

        $validated = $request->validate([
            'action'  => 'required|string|in:approved,rejected',
            'remarks' => 'nullable|string|max:500',
        ]);

        $attendanceService->reviewCorrectionRequest($correctionRequest, auth()->user(), $validated['action'], $validated['remarks'] ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Attendance correction request ' . $validated['action'] . ' successfully.',
        ]);
    }

    public function students()
    {
        $teacher        = $this->getTeacher();
        $mappedStudents = $this->buildStudentData($teacher);

        return view('teacher.students', compact('teacher', 'mappedStudents'));
    }

    public function learningMaterials()
    {
        $teacher = Teacher::where('user_id', auth()->id())
                          ->with(['courses.subject', 'courses.enrollments', 'courses.learningMaterials'])
                          ->firstOrFail();

        $courses = $teacher->courses;
        $courseIds = $courses->pluck('id');

        $realMaterials = LearningMaterial::whereIn('course_id', $courseIds)
                                         ->with(['course.subject', 'course.enrollments', 'subject', 'topicItem'])
                                         ->latest()
                                         ->get();

        $materials = [];

        foreach ($realMaterials as $mat) {
            $course = $mat->course;
            $enrollmentCount = $course ? $course->enrollments->count() : 0;
            $ext = strtoupper($mat->file_type ?: 'PDF');

            $typeBg = match($mat->type) {
                'Notes' => 'bg-blue-50 text-blue-700',
                'Worksheet' => 'bg-emerald-50 text-emerald-700',
                'Slides' => 'bg-amber-50 text-amber-700',
                'Document' => 'bg-purple-50 text-purple-700',
                default => 'bg-gray-100 text-gray-700',
            };

            $iconBg = match($mat->type) {
                'Notes' => 'bg-blue-100 text-blue-700',
                'Worksheet' => 'bg-emerald-100 text-emerald-700',
                'Slides' => 'bg-amber-100 text-amber-700',
                'Document' => 'bg-purple-100 text-purple-700',
                default => 'bg-gray-100 text-gray-700',
            };

            $iconSvg = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>';

            $materials[] = [
                'id'          => $mat->id,
                'name'        => $mat->title,
                'ext'         => $ext,
                'icon'        => $iconSvg,
                'iconBg'      => $iconBg,
                'class'       => $course->level ?? 'SPM',
                'subject'     => $mat->subject->display_name ?? $mat->subject->name ?? $course->subject->display_name ?? $course->subject->name ?? 'General',
                'topic'       => $mat->topicItem->title ?? $mat->topic ?? 'General',
                'type'        => $mat->type,
                'typeBg'      => $typeBg,
                'status'      => $mat->status,
                'statusBg'    => $mat->status === 'Published' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700',
                'updated'     => $mat->updated_at ? $mat->updated_at->format('d M Y') : 'Recent',
                'size'        => $mat->formatted_size,
                'course_id'   => $mat->course_id,
                'subject_id'  => $mat->subject_id,
                'topic_id'    => $mat->topic_id,
                'description' => $mat->description,
                'file_name'   => $mat->file_name,
                'download_url'=> route('teacher.materials.download', $mat->id),
                'delete_url'  => route('teacher.materials.destroy', $mat->id),
                'viewed'      => "{$enrollmentCount} / {$enrollmentCount}",
                'pct'         => 100,
                'is_real'     => true,
            ];
        }

        // Include default proxy assignment materials if teacher has no uploads yet
        if (empty($materials)) {
            foreach ($courses as $course) {
                $enrollmentCount = $course->enrollments->count() ?: 1;
                foreach ($course->assignments as $assignment) {
                    $submitted = StudentAssignment::where('assignment_id', $assignment->id)->count();
                    $pct = round(($submitted / $enrollmentCount) * 100);
                    $materials[] = [
                        'id'          => 'a_' . $assignment->id,
                        'name'        => $assignment->title,
                        'ext'         => 'PDF',
                        'icon'        => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                        'iconBg'      => 'bg-purple-50 text-purple-600',
                        'class'       => $course->level,
                        'subject'     => $course->subject->display_name ?? $course->subject->name ?? 'General',
                        'topic'       => $assignment->description ? substr($assignment->description, 0, 40) : 'General',
                        'type'        => 'Assignment',
                        'typeBg'      => 'bg-purple-50 text-purple-700',
                        'status'      => $assignment->due_date && $assignment->due_date->isPast() ? 'Completed' : 'Published',
                        'statusBg'    => 'bg-emerald-50 text-emerald-700',
                        'updated'     => $assignment->updated_at->format('d M Y'),
                        'size'        => '1.2 MB',
                        'file_name'   => $assignment->title . '.pdf',
                        'download_url'=> '#',
                        'delete_url'  => '#',
                        'viewed'      => "{$submitted} / {$enrollmentCount}",
                        'pct'         => $pct,
                        'is_real'     => false,
                    ];
                }
            }
        }

        $mappedMaterials = collect($materials)->values()->all();
        $subjects = Subject::where('is_active', true)->with('topics')->get();

        return view('teacher.learning-materials', compact('teacher', 'courses', 'mappedMaterials', 'subjects'));
    }

    public function tasks()
    {
        $teacher = $this->getTeacher(true);

        $tasks = [];

        foreach ($teacher->courses as $course) {
            $enrollments = $course->enrollments;
            $enrollmentCount = $enrollments->count();

            // Real assignments, assessments, exams
            foreach ($course->assignments as $assignment) {
                $itemType = in_array($assignment->type, ['Assignment', 'Assessment', 'Exam']) ? $assignment->type : 'Assignment';
                $isPast = $assignment->due_date && $assignment->due_date->isPast();
                $totalMarks = $assignment->total_marks ?? 100;

                $roster = [];
                $submittedCount = 0;
                $lateCount = 0;

                foreach ($enrollments as $enrollment) {
                    $student = $enrollment->student;
                    $studentName = $student?->user?->name ?? 'Student';
                    $studentEmail = $student?->user?->email ?? '';
                    $sa = $assignment->results->firstWhere('student_id', $enrollment->student_id);

                    if ($sa && in_array($sa->status, ['submitted', 'graded', 'late'])) {
                        $submittedCount++;
                        if ($sa->status === 'late') $lateCount++;
                        $roster[] = [
                            'student_id'      => $enrollment->student_id,
                            'assignment_id'   => $assignment->id,
                            'name'            => $studentName,
                            'email'           => $studentEmail,
                            'submittedDate'   => $sa->submitted_at ? $sa->submitted_at->format('d M Y g:i A') : ($sa->created_at ? $sa->created_at->format('d M Y g:i A') : '–'),
                            'rawMarks'        => $sa->score,
                            'marks'           => $sa->score !== null ? $sa->score . ' / ' . $totalMarks : 'Pending Review',
                            'status'          => $sa->status === 'graded' ? 'Reviewed' : ($sa->status === 'late' ? 'Late' : 'Submitted'),
                            'rawStatus'       => $sa->status,
                            'statusBg'        => $sa->status === 'graded' ? 'bg-emerald-50 text-emerald-700' : ($sa->status === 'late' ? 'bg-red-50 text-red-700' : 'bg-blue-50 text-blue-700'),
                            'notes'           => $sa->submission_notes,
                            'teacherFeedback' => $sa->teacher_feedback,
                            'fileName'                => $sa->file_name,
                            'fileUrl'                 => $sa->file_url,
                            'gradedFileName'          => $sa->graded_file_name,
                            'gradedFileUrl'           => $sa->graded_file_url,
                            'interactiveSubmissionId' => $sa->interactiveSubmission?->id,
                            'interactiveReviewUrl'    => $sa->interactiveSubmission ? route('teacher.submissions.review', $sa->interactiveSubmission->id) : null,
                            'hasSubmission'           => true,
                        ];
                    } else {
                        $roster[] = [
                            'student_id'      => $enrollment->student_id,
                            'assignment_id'   => $assignment->id,
                            'name'            => $studentName,
                            'email'           => $studentEmail,
                            'submittedDate'   => '–',
                            'rawMarks'        => null,
                            'marks'           => '–',
                            'status'          => 'Pending',
                            'rawStatus'       => 'pending',
                            'statusBg'        => 'bg-amber-50 text-amber-700',
                            'notes'           => null,
                            'teacherFeedback' => null,
                            'fileName'        => null,
                            'fileUrl'         => null,
                            'gradedFileName'  => null,
                            'gradedFileUrl'   => null,
                            'hasSubmission'   => false,
                        ];
                    }
                }

                $totalStudents = $enrollmentCount;
                $pendingCount = max(0, $totalStudents - $submittedCount);
                $pct = $totalStudents > 0 ? round(($submittedCount / $totalStudents) * 100) : 0;
                $hasOpen = $submittedCount < $totalStudents;

                $tasks[] = [
                    'id'               => $assignment->id,
                    'title'            => $assignment->title,
                    'name'             => $assignment->title,
                    'desc'             => $assignment->description ?? ($itemType === 'Exam' ? 'Formal examination paper.' : 'Complete the assigned work.'),
                    'class'            => $course->level,
                    'subject'          => $course->subject->display_name ?? $course->subject->name ?? 'General',
                    'type'             => $itemType,
                    'topic'            => $assignment->topic ?? 'General',
                    'total_marks'      => $totalMarks,
                    'instructions'     => $assignment->instructions,
                    'duration_minutes' => $assignment->duration_minutes,
                    'exam_date'        => $assignment->exam_date ? $assignment->exam_date->format('Y-m-d') : null,
                    'status'           => $isPast ? 'Completed' : ($hasOpen ? 'Active' : 'Completed'),
                    'statusClass'      => $isPast ? 'text-gray-500' : ($hasOpen ? 'text-emerald-600' : 'text-gray-500'),
                    'dueDate'          => $assignment->due_date ? $assignment->due_date->format('d M Y') : '–',
                    'dueTime'          => '11:59 PM',
                    'submissions'      => "{$submittedCount} / {$totalStudents}",
                    'pct'              => $pct,
                    'totalStudents'    => $totalStudents,
                    'submittedCount'   => $submittedCount,
                    'pendingCount'     => $pendingCount,
                    'lateCount'        => $lateCount,
                    'submissionRate'   => $pct . '%',
                    'attachmentUrl'    => $assignment->file_url,
                    'attachmentName'   => $assignment->file_name,
                    'course_id'        => $course->id,
                    'rawDueDate'       => $assignment->due_date ? $assignment->due_date->format('Y-m-d') : date('Y-m-d'),
                    'delete_url'       => route('teacher.tasks.destroy', $assignment->id),
                    'roster'           => $roster,
                ];
            }

            // Real quizzes
            foreach ($course->quizzes as $quiz) {
                $roster = [];
                $submittedCount = 0;
                $questionCount = $quiz->questions->count() ?: ($quiz->total_questions ?: 10);

                foreach ($enrollments as $enrollment) {
                    $student = $enrollment->student;
                    $studentName = $student?->user?->name ?? 'Student';
                    $sq = $quiz->results->firstWhere('student_id', $enrollment->student_id);

                    if ($sq && $sq->completed_at) {
                        $submittedCount++;
                        $roster[] = [
                            'name'          => $studentName,
                            'submittedDate' => $sq->completed_at->format('d M Y g:i A'),
                            'marks'         => $sq->score . ' / ' . ($sq->total_questions ?: $questionCount),
                            'status'        => 'Completed',
                            'statusBg'      => 'bg-emerald-50 text-emerald-700',
                        ];
                    } else {
                        $roster[] = [
                            'name'          => $studentName,
                            'submittedDate' => '–',
                            'marks'         => '–',
                            'status'        => 'Pending',
                            'statusBg'      => 'bg-amber-50 text-amber-700',
                        ];
                    }
                }

                $totalStudents = $enrollmentCount;
                $pendingCount = max(0, $totalStudents - $submittedCount);
                $pct = $totalStudents > 0 ? round(($submittedCount / $totalStudents) * 100) : 0;

                $tasks[] = [
                    'id'               => 'q' . $quiz->id,
                    'quiz_id'          => $quiz->id,
                    'title'            => $quiz->title,
                    'name'             => $quiz->title,
                    'desc'             => $quiz->description ?? 'Interactive practice quiz.',
                    'class'            => $course->level,
                    'subject'          => $course->subject->display_name ?? $course->subject->name ?? 'General',
                    'type'             => 'Quiz',
                    'total_marks'      => $questionCount,
                    'duration_minutes' => $quiz->time_limit_minutes,
                    'status'           => $submittedCount >= $totalStudents && $totalStudents > 0 ? 'Completed' : 'Active',
                    'statusClass'      => $submittedCount >= $totalStudents && $totalStudents > 0 ? 'text-gray-500' : 'text-emerald-600',
                    'dueDate'          => $quiz->due_date ? $quiz->due_date->format('d M Y') : ($quiz->created_at ? $quiz->created_at->addDays(7)->format('d M Y') : '–'),
                    'dueTime'          => '11:59 PM',
                    'submissions'      => "{$submittedCount} / {$totalStudents}",
                    'pct'              => $pct,
                    'totalStudents'    => $totalStudents,
                    'submittedCount'   => $submittedCount,
                    'pendingCount'     => $pendingCount,
                    'lateCount'        => 0,
                    'submissionRate'   => $pct . '%',
                    'course_id'        => $course->id,
                    'rawDueDate'       => $quiz->due_date ? $quiz->due_date->format('Y-m-d') : ($quiz->created_at ? $quiz->created_at->addDays(7)->format('Y-m-d') : date('Y-m-d')),
                    'delete_url'       => route('teacher.quizzes.destroy', $quiz->id),
                    'roster'           => $roster,
                ];
            }
        }

        $courses     = $teacher->courses;
        $mappedTasks = collect($tasks)->sortByDesc('id')->values()->all();

        return view('teacher.tasks', compact('teacher', 'courses', 'mappedTasks'));
    }

    public function monitoring()
    {
        $user    = auth()->user();
        $teacher = $this->getTeacher();

        $courseIds     = $teacher->courses->pluck('id');
        $totalStudents = $teacher->courses->sum(fn($c) => $c->enrollments->count());
        $attStats      = $this->getAttendanceStats($courseIds);

        // Per-subject performance
        $performanceBySubject = $teacher->courses->map(function($c) {
            $totalEnrollments = $c->enrollments->count();
            $taskCount = $c->assignments->count();
            $tasksCompleted = \App\Models\StudentAssignment::whereIn('assignment_id', $c->assignments->pluck('id'))
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
            $attRecords = \App\Models\StudentAttendance::whereIn('live_class_id', $liveClassIds)->get();
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
        $totalAssessments = \App\Models\Assignment::whereIn('course_id', $courseIds)->count()
            + \App\Models\Quiz::whereIn('course_id', $courseIds)->count();

        $totalSubmissions = \App\Models\StudentAssignment::whereHas('assignment', fn($q) => $q->whereIn('course_id', $courseIds))
            ->whereIn('status', ['submitted', 'graded'])
            ->count();

        $totalExpectedSubmissions = \App\Models\StudentAssignment::whereHas('assignment', fn($q) => $q->whereIn('course_id', $courseIds))->count();
        $tasksCompletedPct = $totalExpectedSubmissions > 0 ? (int) round(($totalSubmissions / $totalExpectedSubmissions) * 100) : 100;

        $totalLiveClassesHeld = \App\Models\LiveClass::where(function($q) use ($courseIds, $teacher) {
                $q->whereIn('course_id', $courseIds)->orWhere('teacher_id', $teacher->id);
            })
            ->where('scheduled_at', '<', now())
            ->where('status', '!=', 'cancelled')
            ->count();

        $attentionStudents = collect($allStudents)->filter(fn($s) => $s['category'] !== 'good')->values()->all();
        $interventionStudents = collect($allStudents)->filter(fn($s) => $s['category'] === 'intervention')->values()->all();

        return view('teacher.monitoring', compact(
            'teacher', 'totalStudents', 'attStats',
            'performanceBySubject', 'allStudents', 'catchUpStudents',
            'totalAssessments', 'tasksCompletedPct', 'totalLiveClassesHeld',
            'attentionStudents', 'interventionStudents'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ACTIONS
    // ─────────────────────────────────────────────────────────────────────────

    public function toggleDuty(Request $request)
    {
        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();
        $teacher->is_online = !$teacher->is_online;
        $teacher->save();

        $statusLabel = $teacher->is_online ? 'ON DUTY (Online for Gigs & Scheduled Sessions)' : 'OFF DUTY';
        return redirect()->back()->with('success', "Duty status updated to {$statusLabel}.");
    }

    public function gigsMarketplace()
    {
        $teacher  = Teacher::where('user_id', auth()->id())->firstOrFail();
        $openGigs = TeachingGig::where('status', 'open')->orderBy('scheduled_at')->get();
        $myGigs   = TeachingGig::where('assigned_teacher_id', $teacher->id)->orderByDesc('scheduled_at')->get();

        return view('teacher.gigs.index', compact('teacher', 'openGigs', 'myGigs'));
    }

    public function claimGig($id)
    {
        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();
        $gig     = TeachingGig::where('id', $id)->where('status', 'open')->firstOrFail();

        $gig->update([
            'status'              => 'claimed',
            'assigned_teacher_id' => $teacher->id,
            'claimed_at'          => now(),
        ]);

        return redirect()->route('teacher.gigs')->with('success', "Teaching Gig '{$gig->title}' claimed successfully!");
    }

    public function checkInGig($id)
    {
        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();
        $gig     = TeachingGig::where('id', $id)->where('assigned_teacher_id', $teacher->id)->firstOrFail();

        $gig->update(['status' => 'checked_in', 'checked_in_at' => now()]);

        return redirect()->back()->with('success', "Checked in live for '{$gig->title}'. Session room is open.");
    }

    public function checkOutGig($id)
    {
        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();
        $gig     = TeachingGig::where('id', $id)->where('assigned_teacher_id', $teacher->id)->firstOrFail();

        $gig->update(['status' => 'completed', 'completed_at' => now()]);
        $teacher->increment('total_gigs_completed');

        return redirect()->back()->with('success', "Session completed! Completion timestamp recorded for Aster Edu.");
    }

    public function walletIndex()
    {
        $teacher  = Teacher::where('user_id', auth()->id())->firstOrFail();
        $earnings = TeacherEarning::where('teacher_id', $teacher->id)->orderByDesc('created_at')->get();

        return view('teacher.wallet.index', compact('teacher', 'earnings'));
    }

    public function storeClass(Request $request)
    {
        $teacher = $this->getTeacher(false);
        $courseIds = $teacher->courses->pluck('id')->toArray();

        $validated = $request->validate([
            'course_id' => 'required|in:' . implode(',', $courseIds),
            'title' => 'required|string|max:255',
            'scheduled_at' => 'required|date',
            'duration_minutes' => 'nullable|integer|min:1|max:480',
            'meeting_url' => 'nullable|url|max:255',
        ]);

        $validated['teacher_id'] = $teacher->id;
        $validated['status'] = 'upcoming';

        LiveClass::create($validated);

        return redirect()->back()->with('success', 'New class session scheduled successfully!');
    }

    public function requestCancel(Request $request, $id)
    {
        $teacher = $this->getTeacher(false);
        $courseIds = $teacher->courses->pluck('id')->toArray();

        $liveClass = LiveClass::where('id', $id)
                              ->where(function($q) use ($courseIds, $teacher) {
                                  $q->whereIn('course_id', $courseIds)->orWhere('teacher_id', $teacher->id);
                              })
                              ->firstOrFail();

        $validated = $request->validate([
            'reason' => 'required|string|min:5|max:1000',
        ]);

        ClassRequest::create([
            'live_class_id' => $liveClass->id,
            'teacher_id' => $teacher->id,
            'type' => 'cancel',
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Cancellation request submitted to Administrator for approval.');
    }

    public function requestReschedule(Request $request, $id)
    {
        $teacher = $this->getTeacher(false);
        $courseIds = $teacher->courses->pluck('id')->toArray();

        $liveClass = LiveClass::where('id', $id)
                              ->where(function($q) use ($courseIds, $teacher) {
                                  $q->whereIn('course_id', $courseIds)->orWhere('teacher_id', $teacher->id);
                              })
                              ->firstOrFail();

        $validated = $request->validate([
            'reason' => 'required|string|min:5|max:1000',
            'requested_at' => 'required|date|after:now',
        ]);

        ClassRequest::create([
            'live_class_id' => $liveClass->id,
            'teacher_id' => $teacher->id,
            'type' => 'reschedule',
            'reason' => $validated['reason'],
            'requested_at' => $validated['requested_at'],
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Reschedule request submitted to Administrator for approval.');
    }

    public function storeTask(Request $request, $id)
    {
        $teacher = $this->getTeacher(true);
        $course = $teacher->courses->where('id', $id)->firstOrFail();

        $validated = $request->validate([
            'title'              => 'required|string|max:255',
            'topic'              => 'nullable|string|max:255',
            'description'        => 'nullable|string',
            'due_date'           => 'required|date|after_or_equal:today',
            'total_marks'        => 'nullable|integer|min:1|max:1000',
            'assign_target'      => 'nullable|in:all,selected',
            'student_ids'        => 'nullable|array',
            'student_ids.*'      => 'integer',
            'file'               => 'nullable|file|max:51200|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,png,jpg,jpeg,mp4',
            'is_interactive_pdf' => 'nullable|boolean',
        ]);

        $filePath = null;
        $fileName = null;
        $fileSize = null;

        $isPdf = false;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
            $filePath = $file->store('task_attachments', 'public');
            $isPdf = strtolower($file->getClientOriginalExtension()) === 'pdf';
        }

        $assignment = $course->assignments()->create([
            'title'              => $validated['title'],
            'topic'              => $validated['topic'] ?? null,
            'description'        => $validated['description'] ?? null,
            'due_date'           => $validated['due_date'],
            'total_marks'        => $validated['total_marks'] ?? 100,
            'file_path'          => $filePath,
            'file_name'          => $fileName,
            'file_size'          => $fileSize,
            'is_interactive_pdf' => $isPdf ? $request->boolean('is_interactive_pdf', false) : false,
        ]);

        // Filter target enrollments based on teacher choice (all vs selected)
        $targetEnrollments = $course->enrollments;
        if ($request->input('assign_target') === 'selected' && !empty($request->input('student_ids'))) {
            $selectedIds = collect($request->input('student_ids'))->map(fn($sid) => (int)$sid)->all();
            $targetEnrollments = $course->enrollments->whereIn('student_id', $selectedIds);
        }

        foreach ($targetEnrollments as $enrollment) {
            StudentAssignment::firstOrCreate([
                'student_id'    => $enrollment->student_id,
                'assignment_id' => $assignment->id,
            ], [
                'status' => 'pending',
            ]);

            if ($enrollment->student?->user_id) {
                UserNotification::create([
                    'user_id' => $enrollment->student->user_id,
                    'type'    => 'task',
                    'title'   => 'New Assignment Assigned',
                    'message' => "Your teacher assigned '{$validated['title']}' for " . ($course->subject->display_name ?? $course->subject->name ?? 'your class') . ". Due on " . Carbon::parse($validated['due_date'])->format('d M Y') . ".",
                ]);
            }
        }

        $assignedCount = $targetEnrollments->count();
        UserNotification::create([
            'user_id' => auth()->id(),
            'type'    => 'task',
            'title'   => 'Task Published & Assigned',
            'message' => "You successfully assigned '{$validated['title']}' to {$course->title} ({$assignedCount} student(s)).",
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "New task assigned to {$assignedCount} student(s) successfully!",
                'assignment' => $assignment,
            ]);
        }

        return redirect()->back()->with('success', "New task assigned to {$assignedCount} student(s) successfully!");
    }

    public function storeGeneralTask(Request $request)
    {
        $teacher = $this->getTeacher(false);
        $courseIds = $teacher->courses->pluck('id')->toArray();

        $validated = $request->validate([
            'course_id'        => 'required|in:' . implode(',', $courseIds),
            'type'             => 'nullable|string|in:Assignment,Assessment,Exam',
            'title'            => 'required|string|max:255',
            'topic'            => 'nullable|string|max:255',
            'description'      => 'nullable|string',
            'instructions'     => 'nullable|string',
            'total_marks'      => 'nullable|integer|min:1|max:1000',
            'duration_minutes' => 'nullable|integer|min:1|max:600',
            'exam_date'        => 'nullable|date',
            'due_date'         => 'required|date|after_or_equal:today',
            'file'             => 'nullable|file|max:51200|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,png,jpg,jpeg,mp4',
        ]);

        $course = Course::findOrFail($validated['course_id']);
        $itemType = $validated['type'] ?? 'Assignment';

        $filePath = null;
        $fileName = null;
        $fileSize = null;
        $isPdf = false;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
            $filePath = $file->store('task_attachments', 'public');
            $isPdf = strtolower($file->getClientOriginalExtension()) === 'pdf';
        }

        $assignment = $course->assignments()->create([
            'title'            => $validated['title'],
            'type'             => $itemType,
            'topic'            => $validated['topic'] ?? null,
            'description'      => $validated['description'] ?? null,
            'instructions'     => $validated['instructions'] ?? null,
            'total_marks'      => $validated['total_marks'] ?? 100,
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'exam_date'        => $validated['exam_date'] ?? null,
            'due_date'         => $validated['due_date'],
            'file_path'          => $filePath,
            'file_name'          => $fileName,
            'file_size'          => $fileSize,
            'is_interactive_pdf' => $isPdf ? $request->boolean('is_interactive_pdf', false) : false,
        ]);

        foreach ($course->enrollments as $enrollment) {
            StudentAssignment::firstOrCreate([
                'student_id'    => $enrollment->student_id,
                'assignment_id' => $assignment->id,
            ], [
                'status' => 'pending',
            ]);

            if ($enrollment->student?->user_id) {
                $notificationTitle = match($itemType) {
                    'Exam'       => 'Official Exam Scheduled',
                    'Assessment' => 'New Assessment Assigned',
                    default      => 'New Assignment Assigned',
                };
                UserNotification::create([
                    'user_id' => $enrollment->student->user_id,
                    'type'    => 'task',
                    'title'   => $notificationTitle,
                    'message' => "Your teacher assigned {$itemType} '{$validated['title']}' for " . ($course->subject->display_name ?? $course->subject->name ?? 'your class') . ". Due on " . Carbon::parse($validated['due_date'])->format('d M Y') . ".",
                ]);
            }
        }

        UserNotification::create([
            'user_id' => auth()->id(),
            'type'    => 'task',
            'title'   => "{$itemType} Published & Assigned",
            'message' => "You successfully assigned {$itemType} '{$validated['title']}' to {$course->title} ({$course->enrollments->count()} students).",
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "{$itemType} '{$validated['title']}' created and assigned successfully!",
                'assignment' => $assignment,
            ]);
        }

        return redirect()->back()->with('success', "{$itemType} '{$validated['title']}' created and assigned successfully!");
    }

    public function storeQuiz(Request $request)
    {
        $teacher = $this->getTeacher(false);
        $courseIds = $teacher->courses->pluck('id')->toArray();

        $validated = $request->validate([
            'course_id'          => 'required|in:' . implode(',', $courseIds),
            'title'              => 'required|string|max:255',
            'description'        => 'nullable|string',
            'time_limit_minutes' => 'nullable|integer|min:1|max:300',
            'passing_marks'      => 'nullable|integer|min:1|max:100',
            'due_date'           => 'nullable|date|after_or_equal:today',
            'questions'          => 'required|array|min:1',
            'questions.*.question_text'   => 'required|string',
            'questions.*.option_a'        => 'required|string',
            'questions.*.option_b'        => 'required|string',
            'questions.*.option_c'        => 'required|string',
            'questions.*.option_d'        => 'required|string',
            'questions.*.correct_option'  => 'required|string|in:A,B,C,D',
            'questions.*.explanation'     => 'nullable|string',
        ]);

        $course = Course::findOrFail($validated['course_id']);
        $totalQuestions = count($validated['questions']);

        $quiz = Quiz::create([
            'course_id'          => $course->id,
            'title'              => $validated['title'],
            'description'        => $validated['description'] ?? null,
            'total_questions'    => $totalQuestions,
            'time_limit_minutes' => $validated['time_limit_minutes'] ?? 15,
            'passing_marks'      => $validated['passing_marks'] ?? 60,
            'due_date'           => $validated['due_date'] ?? now()->addDays(7),
        ]);

        foreach ($validated['questions'] as $qData) {
            Question::create([
                'quiz_id'        => $quiz->id,
                'type'           => 'text',
                'question_text'  => $qData['question_text'],
                'option_a'       => $qData['option_a'],
                'option_b'       => $qData['option_b'],
                'option_c'       => $qData['option_c'],
                'option_d'       => $qData['option_d'],
                'correct_option' => strtoupper($qData['correct_option']),
                'explanation'    => $qData['explanation'] ?? null,
            ]);
        }

        // Notify enrolled students
        foreach ($course->enrollments as $enrollment) {
            if ($enrollment->student?->user_id) {
                UserNotification::create([
                    'user_id' => $enrollment->student->user_id,
                    'type'    => 'quiz',
                    'title'   => 'New Interactive Quiz Available',
                    'message' => "Your teacher published quiz '{$validated['title']}' ({$totalQuestions} questions) for " . ($course->subject->display_name ?? $course->subject->name ?? 'your class') . ".",
                ]);
            }
        }

        UserNotification::create([
            'user_id' => auth()->id(),
            'type'    => 'quiz',
            'title'   => 'Quiz Created',
            'message' => "You successfully created quiz '{$validated['title']}' with {$totalQuestions} questions for {$course->title}.",
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Quiz '{$quiz->title}' with {$totalQuestions} questions created successfully!",
                'quiz' => $quiz,
            ]);
        }

        return redirect()->back()->with('success', "Quiz '{$quiz->title}' with {$totalQuestions} questions created successfully!");
    }

    public function destroyQuiz($id)
    {
        $teacher = $this->getTeacher(false);
        $courseIds = $teacher->courses->pluck('id')->toArray();

        $quiz = Quiz::where('id', $id)
                    ->whereIn('course_id', $courseIds)
                    ->firstOrFail();

        $quiz->questions()->delete();
        $quiz->results()->delete();
        $quiz->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Quiz deleted successfully.'
            ]);
        }

        return redirect()->back()->with('success', 'Quiz deleted successfully.');
    }

    public function storeMaterial(Request $request)
    {
        $teacher = $this->getTeacher(false);
        $courseIds = $teacher->courses->pluck('id')->toArray();

        $validated = $request->validate([
            'course_id'   => 'required|in:' . implode(',', $courseIds),
            'title'       => 'required|string|max:255',
            'type'        => 'required|string|in:Notes,Worksheet,Slides,Document,Assignment,Past Paper',
            'topic'       => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'file'        => 'required|file|max:51200', // 50MB
            'visibility'  => 'nullable|string|in:all,selected',
            'status'      => 'nullable|string|in:Published,Draft,Scheduled',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $fileExt = strtolower($file->getClientOriginalExtension() ?: 'pdf');

        $filePath = $file->store('materials', 'public');

        $material = LearningMaterial::create([
            'course_id'   => $validated['course_id'],
            'teacher_id'  => $teacher->id,
            'title'       => $validated['title'],
            'type'        => $validated['type'],
            'topic'       => $validated['topic'] ?: 'General',
            'description' => $validated['description'] ?? null,
            'file_path'   => $filePath,
            'file_name'   => $originalName,
            'file_size'   => $fileSize,
            'file_type'   => $fileExt,
            'visibility'  => $validated['visibility'] ?? 'all',
            'status'      => $validated['status'] ?? 'Published',
        ]);

        $course = Course::find($validated['course_id']);
        if ($course) {
            foreach ($course->enrollments as $enrollment) {
                if ($enrollment->student?->user_id) {
                    UserNotification::create([
                        'user_id' => $enrollment->student->user_id,
                        'type'    => 'material',
                        'title'   => 'New Learning Material',
                        'message' => "Your teacher uploaded {$validated['type']}: '{$validated['title']}' for " . ($course->subject->display_name ?? $course->subject->name ?? 'your class') . ".",
                    ]);
                }
            }
        }

        UserNotification::create([
            'user_id' => auth()->id(),
            'type'    => 'material',
            'title'   => 'Material Uploaded',
            'message' => "You successfully uploaded {$validated['type']} '{$validated['title']}' for " . ($course->title ?? 'Class') . ".",
        ]);

        return redirect()->back()->with('success', "Learning material '{$material->title}' uploaded and saved successfully!");
    }

    public function destroyMaterial($id)
    {
        $teacher = $this->getTeacher(false);
        $courseIds = $teacher->courses->pluck('id')->toArray();

        $material = LearningMaterial::where('id', $id)
                                    ->whereIn('course_id', $courseIds)
                                    ->firstOrFail();

        if ($material->file_path && Storage::disk('public')->exists($material->file_path)) {
            Storage::disk('public')->delete($material->file_path);
        }

        $material->delete();

        return redirect()->back()->with('success', 'Learning material deleted successfully.');
    }

    public function downloadMaterial($id)
    {
        $material = LearningMaterial::findOrFail($id);

        $user = auth()->user();
        if (!$user) {
            abort(401, 'Please log in to download learning materials.');
        }

        $authorized = false;
        if ($user->isAdmin() || $user->isParent()) {
            $authorized = true;
        } elseif ($user->isTeacher()) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if ($teacher && ($material->teacher_id == $teacher->id || $teacher->courses()->where('id', $material->course_id)->exists())) {
                $authorized = true;
            }
        } elseif ($user->isStudent()) {
            $student = Student::where('user_id', $user->id)->first();
            if ($student) {
                $isEnrolled = $student->enrollments()->where('course_id', $material->course_id)->exists();
                if ($isEnrolled || $material->visibility === 'all') {
                    $authorized = true;
                }
            } else {
                $authorized = true;
            }
        }

        if (!$authorized) {
            abort(403, 'Unauthorized to download this learning material.');
        }

        if (!$material->file_path || !Storage::disk('public')->exists($material->file_path)) {
            abort(404, 'File not found on storage server.');
        }

        return Storage::disk('public')->download($material->file_path, $material->file_name);
    }

    /**
     * Download student assignment submission file securely with cPanel multi-path fallback.
     */
    public function downloadStudentSubmission($studentAssignmentId)
    {
        $sa = StudentAssignment::with(['assignment.course', 'student.user', 'interactiveSubmission'])->findOrFail($studentAssignmentId);

        $user = auth()->user();
        if (!$user) {
            abort(401, 'Please log in to download this submission.');
        }

        $authorized = false;
        if ($user->isAdmin() || $user->isParent()) {
            $authorized = true;
        } elseif ($user->isTeacher()) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if ($teacher) {
                if ($sa->assignment?->course?->teacher_id == $teacher->id || $sa->assignment?->teacher_id == $teacher->id) {
                    $authorized = true;
                } elseif ($teacher->courses()->where('id', $sa->assignment?->course_id)->exists()) {
                    $authorized = true;
                }
            }
        } elseif ($user->isStudent()) {
            $student = Student::where('user_id', $user->id)->first();
            if ($student && $sa->student_id == $student->id) {
                $authorized = true;
            }
        }

        if (!$authorized) {
            abort(403, 'Unauthorized to download this submission.');
        }

        $filePath = $sa->file_path;
        if (!$filePath && $sa->interactiveSubmission?->final_pdf_path) {
            $filePath = $sa->interactiveSubmission->final_pdf_path;
        }

        if (!$filePath) {
            abort(404, 'No submission file found for this record.');
        }

        // Multi-path fallback resolution for cPanel shared hosting
        $resolvedFullPath = null;
        if (Storage::disk('public')->exists($filePath)) {
            $resolvedFullPath = Storage::disk('public')->path($filePath);
        } elseif (file_exists(storage_path('app/public/' . $filePath))) {
            $resolvedFullPath = storage_path('app/public/' . $filePath);
        } elseif (file_exists(public_path('storage/' . $filePath))) {
            $resolvedFullPath = public_path('storage/' . $filePath);
        } elseif (file_exists(base_path('storage/app/public/' . $filePath))) {
            $resolvedFullPath = base_path('storage/app/public/' . $filePath);
        }

        if (!$resolvedFullPath || !file_exists($resolvedFullPath)) {
            abort(404, 'The submitted file was not found on the server storage. Please check storage permissions.');
        }

        $downloadName = $sa->file_name ?: ('submission_' . $sa->id . '.pdf');
        if (!str_ends_with(strtolower($downloadName), '.pdf') && str_ends_with(strtolower($resolvedFullPath), '.pdf')) {
            $downloadName .= '.pdf';
        }

        return response()->download($resolvedFullPath, $downloadName, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Download teacher's graded assignment file securely with cPanel multi-path fallback.
     */
    public function downloadGradedTask($studentAssignmentId)
    {
        $sa = StudentAssignment::with(['assignment.course', 'student.user'])->findOrFail($studentAssignmentId);

        $user = auth()->user();
        if (!$user) {
            abort(401, 'Please log in to download this graded file.');
        }

        $authorized = false;
        if ($user->isAdmin()) {
            $authorized = true;
        } elseif ($user->isParent()) {
            if ($user->studentChildren()->where('students.id', $sa->student_id)->exists() || $user->children()->where('id', $sa->student_id)->exists()) {
                $authorized = true;
            }
        } elseif ($user->isTeacher()) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if ($teacher) {
                if ($sa->assignment?->course?->teacher_id == $teacher->id || $sa->assignment?->teacher_id == $teacher->id) {
                    $authorized = true;
                } elseif ($teacher->courses()->where('id', $sa->assignment?->course_id)->exists()) {
                    $authorized = true;
                }
            }
        } elseif ($user->isStudent()) {
            $student = Student::where('user_id', $user->id)->first();
            if ($student && $sa->student_id == $student->id) {
                $authorized = true;
            }
        }

        if (!$authorized) {
            abort(403, 'Unauthorized to download this graded task.');
        }

        $filePath = $sa->graded_file_path;
        if (!$filePath) {
            abort(404, 'No graded file found for this task.');
        }

        // Multi-path fallback resolution for cPanel shared hosting
        $resolvedFullPath = null;
        if (Storage::disk('public')->exists($filePath)) {
            $resolvedFullPath = Storage::disk('public')->path($filePath);
        } elseif (file_exists(storage_path('app/public/' . $filePath))) {
            $resolvedFullPath = storage_path('app/public/' . $filePath);
        } elseif (file_exists(public_path('storage/' . $filePath))) {
            $resolvedFullPath = public_path('storage/' . $filePath);
        } elseif (file_exists(base_path('storage/app/public/' . $filePath))) {
            $resolvedFullPath = base_path('storage/app/public/' . $filePath);
        }

        if (!$resolvedFullPath || !file_exists($resolvedFullPath)) {
            abort(404, 'The graded task file was not found on the server storage.');
        }

        $ext = pathinfo($resolvedFullPath, PATHINFO_EXTENSION);
        $downloadName = $sa->graded_file_name ?: ('graded_' . $sa->id . ($ext ? ('.' . $ext) : ''));

        return response()->download($resolvedFullPath, $downloadName);
    }

    public function updateMaterial(Request $request, $id)
    {
        $teacher = $this->getTeacher(false);
        $courseIds = $teacher->courses->pluck('id')->toArray();

        $material = LearningMaterial::where('id', $id)
                                    ->whereIn('course_id', $courseIds)
                                    ->firstOrFail();

        $validated = $request->validate([
            'course_id'   => 'required|in:' . implode(',', $courseIds),
            'title'       => 'required|string|max:255',
            'type'        => 'required|string|in:Notes,Worksheet,Slides,Document,Assignment,Past Paper',
            'topic'       => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'file'        => 'nullable|file|max:51200',
            'visibility'  => 'nullable|string|in:all,selected',
            'status'      => 'nullable|string|in:Published,Draft,Scheduled',
        ]);

        $filePath = $material->file_path;
        $fileName = $material->file_name;
        $fileSize = $material->file_size;
        $fileType = $material->file_type;

        if ($request->hasFile('file')) {
            if ($material->file_path && Storage::disk('public')->exists($material->file_path)) {
                Storage::disk('public')->delete($material->file_path);
            }
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
            $fileType = strtolower($file->getClientOriginalExtension() ?: 'pdf');
            $filePath = $file->store('materials', 'public');
        }

        $material->update([
            'course_id'   => $validated['course_id'],
            'title'       => $validated['title'],
            'type'        => $validated['type'],
            'topic'       => $validated['topic'] ?: 'General',
            'description' => $validated['description'] ?? null,
            'file_path'   => $filePath,
            'file_name'   => $fileName,
            'file_size'   => $fileSize,
            'file_type'   => $fileType,
            'visibility'  => $validated['visibility'] ?? 'all',
            'status'      => $validated['status'] ?? 'Published',
        ]);

        $course = Course::find($validated['course_id']);
        if ($course) {
            foreach ($course->enrollments as $enrollment) {
                if ($enrollment->student?->user_id) {
                    UserNotification::create([
                        'user_id' => $enrollment->student->user_id,
                        'type'    => 'material',
                        'title'   => 'Learning Material Updated',
                        'message' => "Learning material '{$validated['title']}' was updated by your teacher.",
                    ]);
                }
            }
        }

        UserNotification::create([
            'user_id' => auth()->id(),
            'type'    => 'material',
            'title'   => 'Material Updated',
            'message' => "You updated learning material '{$validated['title']}'.",
        ]);

        return redirect()->back()->with('success', "Learning material '{$material->title}' updated successfully!");
    }

    public function updateTask(Request $request, $id)
    {
        $teacher = $this->getTeacher(false);
        $courseIds = $teacher->courses->pluck('id')->toArray();

        $assignment = Assignment::where('id', $id)
                                ->whereIn('course_id', $courseIds)
                                ->firstOrFail();

        $validated = $request->validate([
            'course_id'        => 'required|in:' . implode(',', $courseIds),
            'type'             => 'nullable|string|in:Assignment,Assessment,Exam',
            'title'            => 'required|string|max:255',
            'topic'            => 'nullable|string|max:255',
            'description'      => 'nullable|string',
            'instructions'     => 'nullable|string',
            'total_marks'      => 'nullable|integer|min:1|max:1000',
            'duration_minutes' => 'nullable|integer|min:1|max:600',
            'exam_date'        => 'nullable|date',
            'due_date'         => 'required|date',
            'file'             => 'nullable|file|max:51200|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,png,jpg,jpeg,mp4',
        ]);

        $filePath = $assignment->file_path;
        $fileName = $assignment->file_name;
        $fileSize = $assignment->file_size;

        if ($request->hasFile('file')) {
            if ($assignment->file_path && Storage::disk('public')->exists($assignment->file_path)) {
                Storage::disk('public')->delete($assignment->file_path);
            }
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
            $filePath = $file->store('task_attachments', 'public');
        }

        $itemType = $validated['type'] ?? ($assignment->type ?: 'Assignment');

        $assignment->update([
            'course_id'        => $validated['course_id'],
            'type'             => $itemType,
            'title'            => $validated['title'],
            'topic'            => $validated['topic'] ?? $assignment->topic,
            'description'      => $validated['description'] ?? null,
            'instructions'     => $validated['instructions'] ?? null,
            'total_marks'      => $validated['total_marks'] ?? ($assignment->total_marks ?: 100),
            'duration_minutes' => $validated['duration_minutes'] ?? $assignment->duration_minutes,
            'exam_date'        => $validated['exam_date'] ?? $assignment->exam_date,
            'due_date'         => $validated['due_date'],
            'file_path'        => $filePath,
            'file_name'        => $fileName,
            'file_size'        => $fileSize,
        ]);

        $course = $assignment->course;
        if ($course) {
            foreach ($course->enrollments as $enrollment) {
                if ($enrollment->student?->user_id) {
                    UserNotification::create([
                        'user_id' => $enrollment->student->user_id,
                        'type'    => 'task',
                        'title'   => "{$itemType} Updated",
                        'message' => "{$itemType} '{$assignment->title}' for " . ($course->subject->display_name ?? $course->subject->name ?? 'your class') . " was updated. Due on " . Carbon::parse($validated['due_date'])->format('d M Y') . ".",
                    ]);
                }
            }
        }

        UserNotification::create([
            'user_id' => auth()->id(),
            'type'    => 'task',
            'title'   => "{$itemType} Updated",
            'message' => "{$itemType} '{$assignment->title}' was successfully updated.",
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "{$itemType} '{$assignment->title}' updated successfully!",
                'assignment' => $assignment,
            ]);
        }

        return redirect()->back()->with('success', "{$itemType} '{$assignment->title}' updated successfully!");
    }

    public function destroyTask($id)
    {
        $teacher = $this->getTeacher(false);
        $courseIds = $teacher->courses->pluck('id')->toArray();

        $assignment = Assignment::where('id', $id)
                                ->whereIn('course_id', $courseIds)
                                ->firstOrFail();

        if ($assignment->file_path && Storage::disk('public')->exists($assignment->file_path)) {
            Storage::disk('public')->delete($assignment->file_path);
        }

        StudentAssignment::where('assignment_id', $assignment->id)->delete();

        $assignment->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Task/Assignment deleted successfully.'
            ]);
        }

        return redirect()->back()->with('success', 'Task/Assignment deleted successfully.');
    }

    public function gradeSubmission(Request $request, $assignmentId, $studentId)
    {
        $teacher = $this->getTeacher(false);
        $courseIds = $teacher->courses->pluck('id')->toArray();

        $assignment = Assignment::where('id', $assignmentId)
                                ->whereIn('course_id', $courseIds)
                                ->firstOrFail();

        $maxMarks = $assignment->total_marks ?? 100;

        $validated = $request->validate([
            'score'            => 'required|numeric|min:0|max:' . max(100, $maxMarks),
            'teacher_feedback' => 'nullable|string|max:3000',
            'status'           => 'required|in:graded,submitted,late',
            'graded_file'      => 'nullable|file|max:51200|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,png,jpg,jpeg',
        ]);

        $sub = StudentAssignment::firstOrNew([
            'student_id'    => $studentId,
            'assignment_id' => $assignment->id,
        ]);

        $sub->score = $validated['score'];
        $sub->teacher_feedback = $validated['teacher_feedback'] ?? null;
        $sub->status = $validated['status'];
        if (!$sub->submitted_at) {
            $sub->submitted_at = now();
        }

        // Handle uploaded graded task file from teacher
        if ($request->hasFile('graded_file')) {
            $file = $request->file('graded_file');
            $dir = 'graded_tasks/' . $assignment->id;
            $savedName = 'graded_' . $studentId . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs($dir, $savedName, 'public');

            $sub->graded_file_path = $path;
            $sub->graded_file_name = $file->getClientOriginalName();
        }

        $sub->save();

        $studentName = $sub->student?->user?->name ?? 'Student';
        $gradedDocNote = $sub->graded_file_path ? " A graded task document has been attached for your review." : "";

        // 1. Notify Student
        if ($sub->student?->user_id) {
            $feedbackMsg = !empty($validated['teacher_feedback']) ? " Feedback: \"{$validated['teacher_feedback']}\"" : "";
            UserNotification::create([
                'user_id' => $sub->student->user_id,
                'type'    => 'grade',
                'title'   => 'Assignment Graded & Reviewed',
                'message' => "Your teacher graded your submission for '{$assignment->title}' with a score of {$validated['score']}/{$maxMarks}.{$feedbackMsg}{$gradedDocNote}",
            ]);
        }

        // 2. Notify Teacher
        UserNotification::create([
            'user_id' => auth()->id(),
            'type'    => 'grade',
            'title'   => 'Submission Graded',
            'message' => "You reviewed and graded {$studentName}'s submission for '{$assignment->title}' ({$validated['score']}/{$maxMarks})." . ($sub->graded_file_path ? " (Graded task file uploaded)" : ""),
        ]);

        return redirect()->back()->with('success', "Submission for {$studentName} graded successfully ({$validated['score']}/{$maxMarks})!" . ($sub->graded_file_path ? " Graded document uploaded." : ""));
    }

    public function readAllNotifications()
    {
        UserNotification::where('user_id', auth()->id())
                        ->whereNull('read_at')
                        ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function recordings(Request $request)
    {
        $teacher = $this->getTeacher(true);
        $courses = $teacher->courses;
        $courseIds = $courses->pluck('id')->toArray();
        $subjectIds = $courses->pluck('subject_id')->unique()->toArray();

        $recordings = RecordedClass::where(function($q) use ($courseIds, $subjectIds) {
            $q->whereIn('course_id', $courseIds)
              ->orWhereIn('subject_id', $subjectIds);
        })->with(['course.subject', 'subject'])->latest()->get();

        $subjects = Subject::where('is_active', true)->get();

        return view('teacher.recordings', compact('teacher', 'courses', 'recordings', 'subjects'));
    }

    public function storeRecording(Request $request)
    {
        $teacher = $this->getTeacher(false);
        $courses = $teacher->courses;
        $courseIds = $courses->pluck('id')->toArray();

        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'topic_name'       => 'required|string|max:255',
            'course_id'        => 'required|in:' . implode(',', $courseIds),
            'video_url'        => 'nullable|string|max:1000',
            'video_file'       => 'nullable|file|max:204800|mimes:mp4,mov,avi,mkv,webm',
            'thumbnail_file'   => 'nullable|image|max:10240',
            'duration_minutes' => 'required|integer|min:1|max:480',
            'summary'          => 'nullable|string|max:3000',
        ]);

        $course = Course::findOrFail($validated['course_id']);

        $videoUrl = $validated['video_url'] ?? '';
        if ($request->hasFile('video_file')) {
            $path = $request->file('video_file')->store('recordings', 'public');
            $videoUrl = asset('storage/' . $path);
        }

        if (empty($videoUrl)) {
            $videoUrl = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
        }

        $thumbnail = null;
        if ($request->hasFile('thumbnail_file')) {
            $path = $request->file('thumbnail_file')->store('recording_thumbnails', 'public');
            $thumbnail = asset('storage/' . $path);
        }

        RecordedClass::create([
            'course_id'        => $course->id,
            'subject_id'       => $course->subject_id,
            'title'            => $validated['title'],
            'topic_name'       => $validated['topic_name'],
            'video_url'        => $videoUrl,
            'thumbnail'        => $thumbnail,
            'duration_minutes' => $validated['duration_minutes'],
            'views_count'      => 0,
            'summary'          => $validated['summary'] ?? null,
        ]);

        foreach ($course->enrollments as $enrollment) {
            if ($enrollment->student?->user_id) {
                UserNotification::create([
                    'user_id' => $enrollment->student->user_id,
                    'type'    => 'class_reminder',
                    'title'   => 'New Class Recording Available',
                    'message' => "A new class recording '{$validated['title']}' ({$validated['topic_name']}) has been uploaded to the Study Vault for " . ($course->subject->display_name ?? $course->subject->name ?? 'your subject') . ".",
                ]);
            }
        }

        UserNotification::create([
            'user_id' => auth()->id(),
            'type'    => 'class_reminder',
            'title'   => 'Recording Published',
            'message' => "Class recording '{$validated['title']}' was uploaded and published to the Study Vault.",
        ]);

        return redirect()->back()->with('success', "Class recording '{$validated['title']}' uploaded successfully and is now available in the Study Vault!");
    }

    public function updateRecording(Request $request, $id)
    {
        $teacher = $this->getTeacher(false);
        $courses = $teacher->courses;
        $courseIds = $courses->pluck('id')->toArray();

        $recording = RecordedClass::findOrFail($id);

        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'topic_name'       => 'required|string|max:255',
            'course_id'        => 'required|in:' . implode(',', $courseIds),
            'video_url'        => 'nullable|string|max:1000',
            'video_file'       => 'nullable|file|max:204800|mimes:mp4,mov,avi,mkv,webm',
            'thumbnail_file'   => 'nullable|image|max:10240',
            'duration_minutes' => 'required|integer|min:1|max:480',
            'summary'          => 'nullable|string|max:3000',
        ]);

        $course = Course::findOrFail($validated['course_id']);

        $videoUrl = $recording->video_url;
        if ($request->hasFile('video_file')) {
            $path = $request->file('video_file')->store('recordings', 'public');
            $videoUrl = asset('storage/' . $path);
        } elseif (!empty($validated['video_url'])) {
            $videoUrl = $validated['video_url'];
        }

        $thumbnail = $recording->thumbnail;
        if ($request->hasFile('thumbnail_file')) {
            $path = $request->file('thumbnail_file')->store('recording_thumbnails', 'public');
            $thumbnail = asset('storage/' . $path);
        }

        $recording->update([
            'course_id'        => $course->id,
            'subject_id'       => $course->subject_id,
            'title'            => $validated['title'],
            'topic_name'       => $validated['topic_name'],
            'video_url'        => $videoUrl,
            'thumbnail'        => $thumbnail,
            'duration_minutes' => $validated['duration_minutes'],
            'summary'          => $validated['summary'] ?? null,
        ]);

        UserNotification::create([
            'user_id' => auth()->id(),
            'type'    => 'class_reminder',
            'title'   => 'Recording Updated',
            'message' => "Class recording '{$validated['title']}' was updated successfully.",
        ]);

        return redirect()->back()->with('success', "Class recording '{$recording->title}' updated successfully!");
    }

    public function destroyRecording($id)
    {
        $recording = RecordedClass::findOrFail($id);
        $recording->delete();

        return redirect()->back()->with('success', 'Recording removed successfully.');
    }
}
