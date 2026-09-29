<?php

namespace App\Services\Teacher;

use App\Enums\StudentAssignmentStatus;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\StudentAssignment;
use App\Models\Teacher;
use App\Models\UserNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssignmentService
{
    private function getTeacher(bool $withEnrollments = true): Teacher
    {
        $with = ['courses.subject'];
        if ($withEnrollments) {
            $with[] = 'courses.enrollments.student.user';
            $with[] = 'courses.assignments.results.student.user';
            $with[] = 'courses.quizzes.results.student.user';
        }

        return Teacher::where('user_id', auth()->id())->with($with)->firstOrFail();
    }

    public function tasksData(): array
    {
        $teacher = $this->getTeacher(true);

        $tasks = [];

        foreach ($teacher->courses as $course) {
            $enrollments = $course->enrollments;
            $enrollmentCount = $enrollments->count();

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

                    if ($sa && in_array($sa->status, [StudentAssignmentStatus::Submitted, StudentAssignmentStatus::Graded, StudentAssignmentStatus::Late], true)) {
                        $submittedCount++;
                        if ($sa->status === StudentAssignmentStatus::Late) $lateCount++;
                        $roster[] = [
                            'student_id'      => $enrollment->student_id,
                            'assignment_id'   => $assignment->id,
                            'name'            => $studentName,
                            'email'           => $studentEmail,
                            'submittedDate'   => $sa->submitted_at ? $sa->submitted_at->format('d M Y g:i A') : ($sa->created_at ? $sa->created_at->format('d M Y g:i A') : '–'),
                            'rawMarks'        => $sa->score,
                            'marks'           => $sa->score !== null ? $sa->score . ' / ' . $totalMarks : 'Pending Review',
                            'status'          => $sa->status === StudentAssignmentStatus::Graded ? 'Reviewed' : ($sa->status === StudentAssignmentStatus::Late ? 'Late' : 'Submitted'),
                            'rawStatus'       => $sa->status,
                            'statusBg'        => $sa->status === StudentAssignmentStatus::Graded ? 'bg-emerald-50 text-emerald-700' : ($sa->status === StudentAssignmentStatus::Late ? 'bg-red-50 text-red-700' : 'bg-blue-50 text-blue-700'),
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

        return compact('teacher', 'courses', 'mappedTasks');
    }

    public function storeTask(int $courseId, Request $request): Assignment
    {
        $teacher = $this->getTeacher(false);
        $course = $teacher->courses->where('id', $courseId)->firstOrFail();

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
            'description' => $validated['description'] ?? null,
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

        return $assignment;
    }

    public function storeGeneralTask(Request $request): array
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

        return ['assignment' => $assignment, 'itemType' => $itemType];
    }

    public function updateTask(Assignment $assignment, Request $request): array
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

        return ['assignment' => $assignment, 'itemType' => $itemType];
    }

    public function destroyTask(int $id): void
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
    }

    public function gradeSubmission(int $assignmentId, int $studentId, Request $request): StudentAssignment
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

        if ($sub->student?->user_id) {
            $feedbackMsg = !empty($validated['teacher_feedback']) ? " Feedback: \"{$validated['teacher_feedback']}\"" : "";
            UserNotification::create([
                'user_id' => $sub->student->user_id,
                'type'    => 'grade',
                'title'   => 'Assignment Graded & Reviewed',
                'message' => "Your teacher graded your submission for '{$assignment->title}' with a score of {$validated['score']}/100.{$feedbackMsg}",
            ]);
        }

        UserNotification::create([
            'user_id' => auth()->id(),
            'type'    => 'grade',
            'title'   => 'Submission Graded',
            'message' => "You reviewed and graded {$studentName}'s submission for '{$assignment->title}' ({$validated['score']}/100).",
        ]);

        return $sub;
    }

    public function storeQuiz(Request $request): array
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

        return ['quiz' => $quiz, 'totalQuestions' => $totalQuestions];
    }

    public function destroyQuiz(int $id): void
    {
        $teacher = $this->getTeacher(false);
        $courseIds = $teacher->courses->pluck('id')->toArray();

        $quiz = Quiz::where('id', $id)
                    ->whereIn('course_id', $courseIds)
                    ->firstOrFail();

        $quiz->questions()->delete();
        $quiz->results()->delete();
        $quiz->delete();
    }
}
