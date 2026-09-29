<?php

namespace App\Services\Teacher;

use App\Models\LiveClass;
use App\Models\StudentAssignment;
use App\Models\StudentAttendance;
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

        foreach ($teacher->courses as $course) {
            foreach ($course->enrollments as $enrollment) {
                $student = $enrollment->student;
                $sUser   = $student->user;
                $sid     = $student->id;

                if (isset($studentsData[$sid])) continue;

                // Real attendance this month
                $attRecords = StudentAttendance::where('student_id', $sid)
                    ->whereHas('liveClass', fn($q) =>
                        $q->where('course_id', $course->id)
                          ->whereBetween('scheduled_at', [$start, $end])
                    )->get();

                $attTotal    = $attRecords->count();
                $attAttended = $attRecords->whereIn('status', ['attended', 'late'])->count();
                $attPct      = $attTotal > 0 ? round($attAttended / $attTotal * 100) : 0;

                // Real progress
                $progressPct = (int) ($enrollment->progress_pct ?? 0);

                // Real pending tasks
                $pendingTasks = StudentAssignment::where('student_id', $sid)
                    ->whereHas('assignment', fn($q) => $q->where('course_id', $course->id))
                    ->where('status', 'pending')
                    ->count();

                // Latest quiz score
                $latestQuiz = $student->quizResults()
                    ->whereHas('quiz', fn($q) => $q->where('course_id', $course->id))
                    ->latest('completed_at')
                    ->first();
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

        $nextClass = $course->liveClasses->where('scheduled_at', '>=', now()->subMinutes(120))->first();

        $liveClassIds = $course->liveClasses->pluck('id');
        $totalAttendanceRecords = StudentAttendance::whereIn('live_class_id', $liveClassIds)->count();
        $presentRecords = StudentAttendance::whereIn('live_class_id', $liveClassIds)->whereIn('status', ['attended', 'late'])->count();
        $avgAttendancePct = $totalAttendanceRecords > 0 ? (int) round(($presentRecords / $totalAttendanceRecords) * 100) : 100;

        $classAvgProgress = $course->enrollments->count() > 0 ? (int) round($course->enrollments->avg('progress_pct') ?? 0) : 0;
        $studentsBelowTarget = $course->enrollments->filter(fn($e) => ($e->progress_pct ?? 0) < 60 && ($e->progress_pct ?? 0) > 0)->count();

        $topics = $course->subject?->topics ?? collect();

        return compact(
            'teacher', 'course', 'nextClass', 'avgAttendancePct', 'classAvgProgress', 'studentsBelowTarget', 'topics'
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

        $performanceBySubject = $teacher->courses->map(fn($c) => [
            'subject'     => $c->subject->name ?? 'N/A',
            'avg_progress'=> (int) round($c->enrollments->avg('progress_pct') ?? 0),
            'enrolled'    => $c->enrollments->count(),
        ]);

        $allStudents = $this->buildStudentData($teacher);

        $catchUpStudents = StudentAttendance::where('status', 'absent')
            ->whereHas('liveClass', fn($q) =>
                $q->whereIn('course_id', $courseIds)
                  ->whereBetween('scheduled_at', [now()->startOfMonth(), now()])
            )
            ->with('student.user', 'liveClass.course.subject')
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        return compact(
            'teacher', 'totalStudents', 'attStats',
            'performanceBySubject', 'allStudents', 'catchUpStudents'
        );
    }
}
