<?php

namespace App\Services\Student;

use App\Enums\StudentAssignmentStatus;
use App\Models\Student;
use App\Models\StudentSubtopicProgress;

class AchievementService
{
    public function achievementsData(Student $student, $user): array
    {
        $student->recordLoginStreak();

        // Load relationships
        $student->load([
            'enrollments.course.subject',
            'enrollments.course.teacher.user',
            'assignments.assignment.course.subject',
            'quizResults.quiz.course.subject',
            'attendances.liveClass',
        ]);

        $enrollments = $student->enrollments;
        $quizResults = $student->quizResults;
        $assignments = $student->assignments;
        $attendances = $student->attendances;

        // 1. Stats Calculations
        $completedQuizzesCount = $quizResults->count();
        $perfectQuizzesCount = $quizResults->filter(fn ($q) => $q->total_questions > 0 && $q->score >= $q->total_questions)->count();
        $highScoreQuizzesCount = $quizResults->filter(fn ($q) => $q->percent >= 80)->count();
        $avgQuizScore = $completedQuizzesCount > 0 ? (int) round($quizResults->avg(fn ($q) => $q->percent)) : 0;

        $submittedAssignmentsCount = $assignments->whereIn('status', [StudentAssignmentStatus::Submitted, StudentAssignmentStatus::Graded])->count();
        $attendedClassesCount = $attendances->whereIn('status', ['attended', 'late'])->count();
        $streakDays = max(1, $student->streak_days ?? 1);

        // Completed lessons count
        $completedLessonsCount = StudentSubtopicProgress::where('student_id', $student->id)->where('status', 'completed')->count();
        if ($completedLessonsCount === 0 && $enrollments->count() > 0) {
            $completedLessonsCount = (int) round($enrollments->sum(fn ($e) => round((($e->progress_pct ?? 0) / 100) * 8)));
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
            ['activity' => 'Active Study Streak', 'rate' => '+15 pts / day', 'count' => $streakDays.' days', 'earned' => $streakDays * 15],
            ['activity' => 'Welcome Onboarding Bonus', 'rate' => 'One-time', 'count' => '1', 'earned' => 150],
        ];

        // 3. Dynamic Badges
        $mathPassed = $quizResults->filter(function ($q) {
            $subj = strtolower($q->quiz?->course?->subject?->name ?? '');

            return str_contains($subj, 'math') && $q->percent >= 70;
        })->isNotEmpty() || $enrollments->contains(fn ($e) => str_contains(strtolower($e->course?->subject?->name ?? ''), 'math'));

        $sciencePassed = $quizResults->filter(function ($q) {
            $subj = strtolower($q->quiz?->course?->subject?->name ?? '');

            return str_contains($subj, 'science') && $q->percent >= 70;
        })->isNotEmpty() || $enrollments->contains(fn ($e) => str_contains(strtolower($e->course?->subject?->name ?? ''), 'science'));

        $langPassed = $quizResults->filter(function ($q) {
            $subj = strtolower($q->quiz?->course?->subject?->name ?? '');

            return (str_contains($subj, 'english') || str_contains($subj, 'bahasa')) && $q->percent >= 70;
        })->isNotEmpty() || $enrollments->contains(fn ($e) => str_contains(strtolower($e->course?->subject?->name ?? ''), 'english') || str_contains(strtolower($e->course?->subject?->name ?? ''), 'bahasa'));

        $badges = [
            [
                'id' => 'math-master',
                'name' => 'Mathematics Master',
                'category' => 'academic',
                'color' => 'purple',
                'unlocked' => $mathPassed,
                'earned_at' => $mathPassed ? $student->created_at->format('d M Y') : null,
                'description' => 'Enrolled and actively participating in Mathematics modules.',
                'progress' => $mathPassed ? 'Unlocked' : '0/1 Quiz',
                'icon' => 'book',
            ],
            [
                'id' => 'science-explorer',
                'name' => 'Science Explorer',
                'category' => 'academic',
                'color' => 'emerald',
                'unlocked' => $sciencePassed,
                'earned_at' => $sciencePassed ? $student->created_at->format('d M Y') : null,
                'description' => 'Enrolled and exploring Science conceptual experiments.',
                'progress' => $sciencePassed ? 'Unlocked' : '0/1 Quiz',
                'icon' => 'beaker',
            ],
            [
                'id' => 'language-achiever',
                'name' => 'Language Achiever',
                'category' => 'academic',
                'color' => 'blue',
                'unlocked' => $langPassed,
                'earned_at' => $langPassed ? $student->created_at->format('d M Y') : null,
                'description' => 'Active in English or Bahasa Melayu mastery lessons.',
                'progress' => $langPassed ? 'Unlocked' : '0/1 Quiz',
                'icon' => 'pencil',
            ],
            [
                'id' => 'consistent-learner',
                'name' => 'Consistent Learner',
                'category' => 'habit',
                'color' => 'orange',
                'unlocked' => $streakDays >= 3,
                'earned_at' => $streakDays >= 3 ? now()->subDays(max(0, $streakDays - 3))->format('d M Y') : null,
                'description' => 'Maintained an active 3-day daily study streak.',
                'progress' => min($streakDays, 3).'/3 Days',
                'icon' => 'calendar',
            ],
            [
                'id' => 'streak-champion',
                'name' => 'Streak Champion',
                'category' => 'habit',
                'color' => 'amber',
                'unlocked' => $streakDays >= 7,
                'earned_at' => $streakDays >= 7 ? now()->subDays(max(0, $streakDays - 7))->format('d M Y') : null,
                'description' => 'Maintained a 7-day continuous study habit.',
                'progress' => min($streakDays, 7).'/7 Days',
                'icon' => 'flame',
            ],
            [
                'id' => 'attendance-star',
                'name' => 'Attendance Star',
                'category' => 'habit',
                'color' => 'teal',
                'unlocked' => $attendedClassesCount >= 1,
                'earned_at' => $attendedClassesCount >= 1 ? now()->format('d M Y') : null,
                'description' => 'Attended live online classes on time.',
                'progress' => min($attendedClassesCount, 1).'/1 Class',
                'icon' => 'check-badge',
            ],
            [
                'id' => 'problem-solver',
                'name' => 'Problem Solver',
                'category' => 'improvement',
                'color' => 'red',
                'unlocked' => $completedQuizzesCount >= 1,
                'earned_at' => $completedQuizzesCount >= 1 ? ($quizResults->first()?->completed_at?->format('d M Y') ?? now()->format('d M Y')) : null,
                'description' => 'Completed quiz assessments and practice challenges.',
                'progress' => min($completedQuizzesCount, 3).'/3 Quizzes',
                'icon' => 'puzzle',
            ],
            [
                'id' => 'quiz-whiz',
                'name' => 'Quiz Whiz',
                'category' => 'improvement',
                'color' => 'indigo',
                'unlocked' => ($perfectQuizzesCount >= 1 || $highScoreQuizzesCount >= 1),
                'earned_at' => ($perfectQuizzesCount >= 1 || $highScoreQuizzesCount >= 1) ? now()->format('d M Y') : null,
                'description' => 'Scored 80% or higher in a quiz assessment.',
                'progress' => min($highScoreQuizzesCount, 1).'/1 High Score',
                'icon' => 'trophy',
            ],
            [
                'id' => 'assignment-pro',
                'name' => 'Assignment Pro',
                'category' => 'special',
                'color' => 'purple',
                'unlocked' => $submittedAssignmentsCount >= 1,
                'earned_at' => $submittedAssignmentsCount >= 1 ? now()->format('d M Y') : null,
                'description' => 'Completed and submitted assignments on time.',
                'progress' => min($submittedAssignmentsCount, 3).'/3 Submitted',
                'icon' => 'clipboard',
            ],
            [
                'id' => 'olympian-scholar',
                'name' => 'Olympian Scholar',
                'category' => 'special',
                'color' => 'amber',
                'unlocked' => $totalPoints >= 500,
                'earned_at' => $totalPoints >= 500 ? now()->format('d M Y') : null,
                'description' => 'Accumulated 500+ points across all learning activities.',
                'progress' => min($totalPoints, 500).'/500 Points',
                'icon' => 'sparkles',
            ],
        ];

        // 4. Certificates (Only Admin-Issued / Uploaded Certificates)
        $certificates = $student->certificates()->with('course.subject')->latest()->get()->map(function ($cert) use ($student, $user) {
            return [
                'id' => $cert->id,
                'title' => $cert->title,
                'subtitle' => $cert->subtitle ?? ($cert->course?->subject?->name ? ($cert->course->subject->name.' – '.($student->level ?? 'Academic')) : 'Olympian Scholars Academy'),
                'subject' => $cert->course?->subject?->name ?? 'Academic Achievement',
                'teacher' => $cert->issued_by ?? 'Olympian Scholars Administration',
                'date' => $cert->issued_at ? $cert->issued_at->format('d M Y') : $cert->created_at->format('d M Y'),
                'is_unlocked' => true,
                'student_name' => $user->name,
                'cert_no' => $cert->cert_number,
                'file_url' => $cert->file_url,
                'file_name' => $cert->file_name,
                'description' => $cert->description,
            ];
        });

        // 5. Dynamic Milestones
        $milestones = [
            [
                'title' => 'First 10 Lessons Completed',
                'subtitle' => $completedLessonsCount >= 10 ? 'Great milestone reached!' : 'Complete your course lessons',
                'current' => min($completedLessonsCount, 10),
                'target' => 10,
                'completed' => $completedLessonsCount >= 10,
                'progress_pct' => min(100, (int) round(($completedLessonsCount / 10) * 100)),
            ],
            [
                'title' => '3 Assignments Submitted',
                'subtitle' => $submittedAssignmentsCount >= 3 ? 'Excellent discipline!' : 'Submit your teacher homework',
                'current' => min($submittedAssignmentsCount, 3),
                'target' => 3,
                'completed' => $submittedAssignmentsCount >= 3,
                'progress_pct' => min(100, (int) round(($submittedAssignmentsCount / 3) * 100)),
            ],
            [
                'title' => '7-Day Study Streak',
                'subtitle' => $streakDays >= 7 ? 'Amazing consistency!' : 'Keep logging in daily',
                'current' => min($streakDays, 7),
                'target' => 7,
                'completed' => $streakDays >= 7,
                'progress_pct' => min(100, (int) round(($streakDays / 7) * 100)),
            ],
            [
                'title' => 'Subject Mastery (80%+ Quiz Score)',
                'subtitle' => $avgQuizScore >= 80 ? 'Mastery achieved!' : 'Aim for 80% average quiz score',
                'current' => min($avgQuizScore, 80),
                'target' => 80,
                'completed' => $avgQuizScore >= 80,
                'progress_pct' => min(100, (int) round(($avgQuizScore / 80) * 100)),
            ],
            [
                'title' => 'Complete 3 Practice Quizzes',
                'subtitle' => $completedQuizzesCount >= 3 ? 'Practice makes perfect!' : 'Test your knowledge with quizzes',
                'current' => min($completedQuizzesCount, 3),
                'target' => 3,
                'completed' => $completedQuizzesCount >= 3,
                'progress_pct' => min(100, (int) round(($completedQuizzesCount / 3) * 100)),
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
            if ($isMe) {
                $foundCurrent = true;
            }
            $rankedList[] = [
                'rank' => $idx + 1,
                'name' => $isMe ? ($user->name.' (You)') : ($ts->user->name ?? 'Student #'.$ts->id),
                'points' => number_format($ts->total_points ?: ($isMe ? $totalPoints : 250)),
                'is_me' => $isMe,
            ];
        }

        if (! $foundCurrent) {
            $betterCount = Student::where('total_points', '>', $totalPoints)->count();
            $myRank = $betterCount + 1;
            $rankedList[] = [
                'rank' => $myRank,
                'name' => $user->name.' (You)',
                'points' => number_format($totalPoints),
                'is_me' => true,
            ];
        }

        return compact(
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
        );
    }
}
