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
     * Compute this-month attendance stats for all students across the given course IDs.
     * Returns ['total_sessions', 'attended', 'absent', 'late', 'pct']
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
        $pct      = $total > 0 ? round($attended / $total * 100) : 0;

        return compact('total', 'attended', 'absent', 'late', 'pct');
    }

    /**
     * Build the student data array from real DB data for the students page.
     */
    private function buildStudentData(Teacher $teacher): array
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

        // Today's live classes
        $todaysClasses = LiveClass::where(function($q) use ($courseIds, $teacher) {
                                      $q->whereIn('course_id', $courseIds)->orWhere('teacher_id', $teacher->id);
                                  })
                                  ->whereDate('scheduled_at', today())
                                  ->with('course.subject')
                                  ->orderBy('scheduled_at')
                                  ->get();

        // Next upcoming class
        $upcomingClass = LiveClass::where(function($q) use ($courseIds, $teacher) {
                                      $q->whereIn('course_id', $courseIds)->orWhere('teacher_id', $teacher->id);
                                  })
                                  ->where('scheduled_at', '>', now())
                                  ->with('course.subject')
                                  ->orderBy('scheduled_at')
                                  ->first();

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
            'subject' => $c->subject->name ?? 'N/A',
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
            'teacher', 'courses', 'totalStudents', 'todaysClasses',
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

        // Upcoming classes
        $upcomingClasses = LiveClass::where(function($q) use ($courseIds, $teacher) {
                $q->whereIn('course_id', $courseIds)->orWhere('teacher_id', $teacher->id);
            })
            ->where('scheduled_at', '>=', now())
            ->with('course.subject')
            ->orderBy('scheduled_at')
            ->take(10)
            ->get();

        // Past classes
        $pastClasses = LiveClass::where(function($q) use ($courseIds, $teacher) {
                $q->whereIn('course_id', $courseIds)->orWhere('teacher_id', $teacher->id);
            })
            ->where('scheduled_at', '<', now())
            ->with('course.subject')
            ->orderByDesc('scheduled_at')
            ->take(10)
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

        // Real next upcoming live class
        $nextClass = $course->liveClasses->where('scheduled_at', '>=', now()->subMinutes(120))->first();

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

        return view('teacher.class-workspace', compact(
            'teacher', 'course', 'nextClass', 'avgAttendancePct', 'classAvgProgress', 'studentsBelowTarget', 'topics'
        ));
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
                'subject'     => $mat->subject->name ?? $course->subject->name ?? 'General',
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
                        'subject'     => $course->subject->name ?? 'General',
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
                            'fileName'        => $sa->file_name,
                            'fileUrl'         => $sa->file_url,
                            'hasSubmission'   => true,
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
                    'subject'          => $course->subject->name ?? 'General',
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
                    'subject'          => $course->subject->name ?? 'General',
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
        $performanceBySubject = $teacher->courses->map(fn($c) => [
            'subject'     => $c->subject->name ?? 'N/A',
            'avg_progress'=> (int) round($c->enrollments->avg('progress_pct') ?? 0),
            'enrolled'    => $c->enrollments->count(),
        ]);

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

        return view('teacher.monitoring', compact(
            'teacher', 'totalStudents', 'attStats',
            'performanceBySubject', 'allStudents', 'catchUpStudents'
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
            'scheduled_at' => 'required|date|after:now',
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
        $teacher = $this->getTeacher(false);
        $course = $teacher->courses->where('id', $id)->firstOrFail();

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date'    => 'required|date|after_or_equal:today',
            'file'        => 'nullable|file|max:51200|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,png,jpg,jpeg,mp4',
        ]);

        $filePath = null;
        $fileName = null;
        $fileSize = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
            $filePath = $file->store('task_attachments', 'public');
        }

        $assignment = $course->assignments()->create([
            'title'       => $validated['title'],
            'description' => $validated['description'],
            'due_date'    => $validated['due_date'],
            'file_path'   => $filePath,
            'file_name'   => $fileName,
            'file_size'   => $fileSize,
        ]);

        foreach ($course->enrollments as $enrollment) {
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
                    'message' => "Your teacher assigned '{$validated['title']}' for " . ($course->subject->name ?? 'your class') . ". Due on " . Carbon::parse($validated['due_date'])->format('d M Y') . ".",
                ]);
            }
        }

        UserNotification::create([
            'user_id' => auth()->id(),
            'type'    => 'task',
            'title'   => 'Task Published & Assigned',
            'message' => "You successfully assigned '{$validated['title']}' to {$course->title} ({$course->enrollments->count()} students).",
        ]);

        return redirect()->back()->with('success', 'New task/assignment with material attachment assigned to this class successfully!');
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

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
            $filePath = $file->store('task_attachments', 'public');
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
            'file_path'        => $filePath,
            'file_name'        => $fileName,
            'file_size'        => $fileSize,
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
                    'message' => "Your teacher assigned {$itemType} '{$validated['title']}' for " . ($course->subject->name ?? 'your class') . ". Due on " . Carbon::parse($validated['due_date'])->format('d M Y') . ".",
                ]);
            }
        }

        UserNotification::create([
            'user_id' => auth()->id(),
            'type'    => 'task',
            'title'   => "{$itemType} Published & Assigned",
            'message' => "You successfully assigned {$itemType} '{$validated['title']}' to {$course->title} ({$course->enrollments->count()} students).",
        ]);

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
                    'message' => "Your teacher published quiz '{$validated['title']}' ({$totalQuestions} questions) for " . ($course->subject->name ?? 'your class') . ".",
                ]);
            }
        }

        UserNotification::create([
            'user_id' => auth()->id(),
            'type'    => 'quiz',
            'title'   => 'Quiz Created',
            'message' => "You successfully created quiz '{$validated['title']}' with {$totalQuestions} questions for {$course->title}.",
        ]);

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
                        'message' => "Your teacher uploaded {$validated['type']}: '{$validated['title']}' for " . ($course->subject->name ?? 'your class') . ".",
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
                        'message' => "{$itemType} '{$assignment->title}' for " . ($course->subject->name ?? 'your class') . " was updated. Due on " . Carbon::parse($validated['due_date'])->format('d M Y') . ".",
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

        $validated = $request->validate([
            'score'            => 'required|integer|min:0|max:100',
            'teacher_feedback' => 'nullable|string|max:2000',
            'status'           => 'required|in:graded,submitted,late',
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
        $sub->save();

        $studentName = $sub->student?->user?->name ?? 'Student';

        // 1. Notify Student
        if ($sub->student?->user_id) {
            $feedbackMsg = !empty($validated['teacher_feedback']) ? " Feedback: \"{$validated['teacher_feedback']}\"" : "";
            UserNotification::create([
                'user_id' => $sub->student->user_id,
                'type'    => 'grade',
                'title'   => 'Assignment Graded & Reviewed',
                'message' => "Your teacher graded your submission for '{$assignment->title}' with a score of {$validated['score']}/100.{$feedbackMsg}",
            ]);
        }

        // 2. Notify Teacher
        UserNotification::create([
            'user_id' => auth()->id(),
            'type'    => 'grade',
            'title'   => 'Submission Graded',
            'message' => "You reviewed and graded {$studentName}'s submission for '{$assignment->title}' ({$validated['score']}/100).",
        ]);

        return redirect()->back()->with('success', "Submission for {$studentName} graded successfully ({$validated['score']}/100)!");
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
                    'message' => "A new class recording '{$validated['title']}' ({$validated['topic_name']}) has been uploaded to the Study Vault for " . ($course->subject->name ?? 'your subject') . ".",
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
