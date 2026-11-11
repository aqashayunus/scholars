<?php

namespace App\Services\Student;

use App\Models\LearningMaterial;
use App\Models\Quiz;
use App\Models\RecordedClass;
use App\Models\Student;
use App\Models\StudentSubtopicProgress;

class LearningService
{
    public function learningData(Student $student): array
    {
        $enrollments = $student->enrollments()->with('course.subject.topics.subtopics', 'course.teacher.user')->get();
        $recordings = RecordedClass::latest()->get();

        // Build dynamic subjects array from database
        $studentProgress = StudentSubtopicProgress::where('student_id', $student->id)->get()->keyBy('subtopic_id');

        // Batched per-course tab counts, instead of 2 queries per enrollment
        // inside the loop below.
        $courseIds = $enrollments->pluck('course_id');
        $practiceCountByCourseId = Quiz::whereIn('course_id', $courseIds)->has('questions')->get(['id', 'course_id'])
            ->groupBy('course_id')->map(fn ($g) => $g->count());
        $materialCountByCourseId = LearningMaterial::whereIn('course_id', $courseIds)->where('status', 'Published')->get(['id', 'course_id'])
            ->groupBy('course_id')->map(fn ($g) => $g->count());

        $subjectsData = [];
        foreach ($enrollments as $enrollment) {
            $course = $enrollment->course;
            $subject = $course->subject;
            if (! $subject) {
                continue;
            }

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

                    $badgeClass = match ($status) {
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
            $practiceCount = $practiceCountByCourseId->get($course->id, 0);
            $videoCount = $recordings->where('subject_id', $subject->id)->count();
            $courseMaterialsCount = $materialCountByCourseId->get($course->id, 0);
            $notesCount = $courseMaterialsCount > 0 ? $courseMaterialsCount : 0;

            $subjectsData[] = [
                'id' => $subject->slug,
                'name' => $subject->name,
                'code' => $subject->code ?? 'KPM-'.$subject->id,
                'education_type' => $subject->education_type ?? 'secondary',
                'stage' => $subject->stage ?? 'Upper Secondary',
                'form_year' => $subject->form_year ?? 'Form 4–5',
                'category' => $subject->category ?? 'Core',
                'stream_package' => $subject->stream_package,
                'teacher' => $teacherName,
                'level' => $course->level ?? $subject->level ?? 'SPM',
                'progress' => $overallSubjectProgress,
                'notesCount' => $notesCount,
                'practiceCount' => $practiceCount,
                'videoCount' => $videoCount,
                'topics' => $topicsData,
            ];
        }

        // All quizzes and learning materials for student's enrolled courses
        $allQuizzes = Quiz::whereIn('course_id', $courseIds)->with(['course.subject', 'questions'])->get();
        $allMaterials = LearningMaterial::whereIn('course_id', $courseIds)
            ->where('status', 'Published')
            ->with('course.subject', 'teacher.user')
            ->latest()
            ->get();

        return compact('enrollments', 'recordings', 'subjectsData', 'allQuizzes', 'allMaterials');
    }
}
