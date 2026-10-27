<?php

namespace App\Services\Parent;

use App\Models\Student;
use App\Models\StudentAttendance;
use App\Services\AttendanceService;
use App\Services\Student\ClassScheduleService;
use Illuminate\Support\Collection;

class AcademicProgressService
{
    /**
     * Compute this-month attendance stats for a given Student.
     * Returns ['total', 'attended', 'absent', 'late', 'pct']
     */
    public function getAttendanceStats(Student $student): array
    {
        return app(AttendanceService::class)->getStudentAttendanceStats($student);
    }

    /**
     * Return per-subject progress for a student from their enrollments.
     * [['subject'=>'Mathematics','emoji'=>'🧮','color'=>'blue','pct'=>78], ...]
     */
    public function getChildProgress(Student $student): array
    {
        $colorMap = [
            'Mathematics'          => 'blue',
            'Science'              => 'emerald',
            'English'              => 'purple',
            'Bahasa Melayu'        => 'amber',
            'Physics'              => 'indigo',
            'Chemistry'            => 'teal',
            'Biology'              => 'green',
            'Sejarah'              => 'rose',
            'Geography'            => 'orange',
            'Prinsip Perakaunan'   => 'emerald',
            'Sains Komputer'       => 'blue',
            'Ekonomi'              => 'amber',
            'Perniagaan'           => 'slate',
        ];

        $progress = [];
        foreach ($student->enrollments as $en) {
            $rawName = $en->course->subject->name ?? 'N/A';
            $displayName = $en->course->subject->display_name ?? $rawName;
            $color = $colorMap[$rawName] ?? 'blue';
            $pct = (int) ($en->progress_pct ?? 0);

            // Check if student has taken quizzes in this course
            $quizzes = $student->quizResults()->whereHas('quiz', fn($q) => $q->where('course_id', $en->course_id))->get();
            $quizAvg = $quizzes->isNotEmpty() ? (int) round($quizzes->avg('score')) : null;

            $progress[] = [
                'subject'           => $displayName,
                'emoji'             => '',
                'color'             => $color,
                'pct'               => $pct,
                'quiz_avg'          => $quizAvg,
                'completed_lessons' => (int) ($en->completed_lessons ?? 0),
            ];
        }
        return $progress;
    }

    /**
     * Subjects that need attention: only flag if quizzes indicate struggle
     * (< 70%) or active progress is lagging (< 70% after starting).
     */
    public function needsAttention(array $childProgress): array
    {
        return array_filter($childProgress, function ($s) {
            if ($s['quiz_avg'] !== null) {
                return $s['quiz_avg'] < 70;
            }
            return $s['pct'] > 0 && $s['pct'] < 70;
        });
    }

    /**
     * Get upcoming live classes for a student's enrolled courses.
     */
    public function getUpcomingClasses(Student $student, int $limit = 5): Collection
    {
        return ClassScheduleService::getStudentLiveClassesQuery($student)
            ->with('course.subject', 'course.teacher.user')
            ->activeOrUpcoming()
            ->orderBy('scheduled_at')
            ->take($limit)
            ->get();
    }

    /**
     * Get past / completed live classes for a student (with attendance status).
     */
    public function getPastClasses(Student $student, int $limit = 10): Collection
    {
        return ClassScheduleService::getStudentLiveClassesQuery($student)
            ->with('course.subject', 'attendances')
            ->completedOrPast()
            ->orderByDesc('scheduled_at')
            ->take($limit)
            ->get();
    }
}
