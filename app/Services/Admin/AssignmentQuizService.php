<?php

namespace App\Services\Admin;

use App\Http\Controllers\DiagnosticTestController;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Student;
use App\Models\StudentAssignment;
use App\Models\UserNotification;
use App\Services\PdfDiagnosticParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssignmentQuizService
{
    // ── Tasks & Diagnostic Tests ────────────────────────────────────────────────

    public function createTask(Request $request): Assignment
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'type'             => 'required|string|max:50',
            'target_level'     => 'nullable|string|max:100',
            'course_id'        => 'nullable|exists:courses,id',
            'topic'            => 'nullable|string|max:255',
            'total_marks'      => 'nullable|integer|min:1|max:1000',
            'due_date'         => 'nullable|date',
            'description'      => 'nullable|string|max:5000',
            'instructions'     => 'nullable|string|max:5000',
            'file'             => 'nullable|file|max:30720|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,png,jpg,jpeg',
        ]);

        $filePath = null;
        $fileName = null;
        $fileSize = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
            $filePath = $file->store('assignments', 'public');
        }

        $isGlobal = empty($validated['course_id']);

        $answerKeyRaw = $request->input('answer_key');
        $answerKeyMap = null;
        if (!empty($answerKeyRaw)) {
            if (is_array($answerKeyRaw)) {
                $answerKeyMap = $answerKeyRaw;
            } else {
                $parts = array_map('trim', explode(',', $answerKeyRaw));
                $answerKeyMap = [];
                foreach ($parts as $idx => $opt) {
                    if (!empty($opt)) {
                        $answerKeyMap[(string)($idx + 1)] = strtoupper($opt);
                    }
                }
            }
        }

        // Process question-by-question builder data (if provided)
        $questionsData = [];
        if ($request->filled('questions_json')) {
            $decoded = json_decode($request->input('questions_json'), true);
            if (is_array($decoded)) {
                $questionsData = $decoded;
            }
        } elseif ($request->has('questions') && is_array($request->input('questions'))) {
            $questionsData = $request->input('questions');
        }

        if (!empty($questionsData)) {
            $normalizedQuestions = [];
            $answerKeyMap = [];
            $sectionBMap = [];
            $mcqCount = 0;
            $sectionBCount = 0;

            foreach ($questionsData as $idx => $item) {
                $qNum  = (int) ($item['number'] ?? ($idx + 1));
                $type  = isset($item['type']) && in_array($item['type'], ['mcq', 'fill_in_blank']) ? $item['type'] : 'mcq';
                $ans   = trim((string)($item['correct_answer'] ?? ''));
                $title = !empty($item['title']) ? trim($item['title']) : "Soalan {$qNum}";

                if ($type === 'mcq') {
                    $mcqCount++;
                    $ans = strtoupper($ans);
                    $answerKeyMap[(string)$qNum] = $ans;
                } else {
                    $sectionBCount++;
                    $sectionBMap[(string)$qNum] = $ans;
                }

                $img = $this->persistQuestionImage($item['image'] ?? null, 0, $qNum);
                $normalizedQuestions[] = [
                    'number'         => $qNum,
                    'type'           => $type,
                    'title'          => $title,
                    'correct_answer' => $ans,
                    'image'          => $img,
                    'prompt'         => $item['prompt'] ?? null,
                    'page_number'    => $item['page_number'] ?? ($item['page'] ?? 1),
                    'options_labels' => $item['options'] ?? ($item['options_labels'] ?? []),
                    'passage'        => $item['passage'] ?? null,
                ];
            }

            $totalQuestions    = count($normalizedQuestions);
            $sectionBQuestions = $sectionBCount;
        } else {
            // Fallback for legacy comma-separated Section A & B inputs
            $totalQuestions    = (int) ($request->input('total_questions') ?: ($answerKeyMap ? count($answerKeyMap) : 10));
            $sectionBQuestions = (int) ($request->input('section_b_questions') ?: ($sectionBMap ?? [] ? count($sectionBMap) : 0));
            $normalizedQuestions = null;
        }

        $assignment = Assignment::create([
            'course_id'            => $validated['course_id'] ?? null,
            'is_global'            => $isGlobal,
            'target_level'         => $validated['target_level'] ?? 'All Levels',
            'created_by_admin'     => true,
            'title'                => $validated['title'],
            'type'                 => $validated['type'],
            'topic'                => $validated['topic'] ?? ($validated['type'] === 'Diagnostic Test' ? 'General Diagnostic' : null),
            'total_marks'          => $validated['total_marks'] ?? 100,
            'due_date'             => $validated['due_date'] ?? null,
            'description'          => $validated['description'] ?? null,
            'instructions'         => $validated['instructions'] ?? null,
            'answer_key'           => $answerKeyMap,
            'total_questions'      => $totalQuestions,
            'time_limit_minutes'   => (int) ($request->input('time_limit_minutes') ?: 20),
            'section_b_answer_key' => $sectionBMap ?? null,
            'section_b_questions'  => $sectionBQuestions,
            'questions_data'       => $normalizedQuestions,
            'file_path'            => $filePath,
            'file_name'            => $fileName,
            'file_size'            => $fileSize,
        ]);

        // If a specific student_id is provided, link ONLY to that student
        if ($request->filled('student_id')) {
            $studentId = $request->input('student_id');
            StudentAssignment::firstOrCreate([
                'student_id'    => $studentId,
                'assignment_id' => $assignment->id,
            ], [
                'status' => 'pending',
            ]);

            return $assignment;
        }

        // Automatically link to all targeted students immediately!
        if ($isGlobal) {
            $targetLevel = $validated['target_level'] ?? 'All Levels';
            $studentsQuery = Student::query();
            if (!empty($targetLevel) && $targetLevel !== 'All Levels') {
                $studentsQuery->where('level', $targetLevel);
            }
            $students = $studentsQuery->get();

            foreach ($students as $student) {
                StudentAssignment::firstOrCreate([
                    'student_id'    => $student->id,
                    'assignment_id' => $assignment->id,
                ], [
                    'status' => 'pending',
                ]);
            }
        } elseif (!empty($validated['course_id'])) {
            $course = Course::with('enrollments')->find($validated['course_id']);
            if ($course) {
                foreach ($course->enrollments as $enr) {
                    StudentAssignment::firstOrCreate([
                        'student_id'    => $enr->student_id,
                        'assignment_id' => $assignment->id,
                    ], [
                        'status' => 'pending',
                    ]);
                }
            }
        }

        return $assignment;
    }

    public function updateTask(Assignment $assignment, Request $request): Assignment
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'type'             => 'required|string|max:50',
            'target_level'     => 'nullable|string|max:100',
            'total_marks'      => 'nullable|integer|min:1|max:1000',
            'due_date'         => 'nullable|date',
            'description'      => 'nullable|string|max:5000',
            'instructions'     => 'nullable|string|max:5000',
            'file'             => 'nullable|file|max:30720|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,png,jpg,jpeg',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $assignment->file_name = $file->getClientOriginalName();
            $assignment->file_size = $file->getSize();
            $assignment->file_path = $file->store('assignments', 'public');
        }

        // Process question-by-question builder data (if provided)
        $questionsData = [];
        if ($request->filled('questions_json')) {
            $decoded = json_decode($request->input('questions_json'), true);
            if (is_array($decoded)) {
                $questionsData = $decoded;
            }
        } elseif ($request->has('questions') && is_array($request->input('questions'))) {
            $questionsData = $request->input('questions');
        }

        if (!empty($questionsData)) {
            $normalizedQuestions = [];
            $answerKeyMap = [];
            $sectionBMap = [];
            $mcqCount = 0;
            $sectionBCount = 0;

            foreach ($questionsData as $idx => $item) {
                $qNum  = (int) ($item['number'] ?? ($idx + 1));
                $type  = isset($item['type']) && in_array($item['type'], ['mcq', 'fill_in_blank']) ? $item['type'] : 'mcq';
                $ans   = trim((string)($item['correct_answer'] ?? ''));
                $title = !empty($item['title']) ? trim($item['title']) : "Soalan {$qNum}";

                if ($type === 'mcq') {
                    $mcqCount++;
                    $ans = strtoupper($ans);
                    $answerKeyMap[(string)$qNum] = $ans;
                } else {
                    $sectionBCount++;
                    $sectionBMap[(string)$qNum] = $ans;
                }

                $img = $this->persistQuestionImage($item['image'] ?? null, $assignment->id, $qNum);
                $normalizedQuestions[] = [
                    'number'         => $qNum,
                    'type'           => $type,
                    'title'          => $title,
                    'correct_answer' => $ans,
                    'image'          => $img,
                    'prompt'         => $item['prompt'] ?? null,
                    'page_number'    => $item['page_number'] ?? ($item['page'] ?? 1),
                    'options_labels' => $item['options'] ?? ($item['options_labels'] ?? []),
                    'passage'        => $item['passage'] ?? null,
                ];
            }

            $assignment->questions_data       = $normalizedQuestions;
            $assignment->total_questions      = count($normalizedQuestions);
            $assignment->answer_key           = $answerKeyMap;
            $assignment->section_b_questions  = $sectionBCount;
            $assignment->section_b_answer_key = $sectionBMap;
        } else {
            // Legacy answer_key update
            $answerKeyRaw = $request->input('answer_key');
            if ($answerKeyRaw !== null) {
                if (is_array($answerKeyRaw)) {
                    $assignment->answer_key = $answerKeyRaw;
                } else {
                    $parts = array_map('trim', explode(',', $answerKeyRaw));
                    $map = [];
                    foreach ($parts as $idx => $opt) {
                        if (!empty($opt)) {
                            $map[(string)($idx + 1)] = strtoupper($opt);
                        }
                    }
                    $assignment->answer_key = $map;
                }
            }

            $sectionBRaw = $request->input('section_b_answer_key');
            if ($sectionBRaw !== null) {
                $parts = array_map('trim', explode(',', $sectionBRaw));
                $sectionBMap = [];
                foreach ($parts as $idx => $ans) {
                    if ($ans !== '') {
                        $sectionBMap[(string)($idx + 1)] = $ans;
                    }
                }
                $assignment->section_b_answer_key = $sectionBMap;
            }

            if ($request->filled('total_questions')) {
                $assignment->total_questions = (int) $request->input('total_questions');
            }
            if ($request->filled('section_b_questions')) {
                $assignment->section_b_questions = (int) $request->input('section_b_questions');
            }
        }

        if ($request->filled('time_limit_minutes')) {
            $assignment->time_limit_minutes = (int) $request->input('time_limit_minutes');
        }

        $assignment->title        = $validated['title'];
        $assignment->type         = $validated['type'];
        $assignment->target_level = $validated['target_level'] ?? 'All Levels';
        $assignment->total_marks  = $validated['total_marks'] ?? 100;
        $assignment->due_date     = $validated['due_date'] ?? null;
        $assignment->description  = $validated['description'] ?? null;
        $assignment->instructions = $validated['instructions'] ?? null;
        $assignment->save();

        return $assignment;
    }

    public function deleteTask(int $id): string
    {
        $assignment = Assignment::findOrFail($id);
        $title = $assignment->title;

        // Explicitly remove all student assignment records
        StudentAssignment::where('assignment_id', $assignment->id)->delete();

        if ($assignment->file_path && Storage::disk('public')->exists($assignment->file_path)) {
            Storage::disk('public')->delete($assignment->file_path);
        }

        $assignment->delete();

        return $title;
    }

    /**
     * Auto-detect and parse questions, prompts, options, and page mapping from an uploaded PDF.
     */
    public function parsePdfQuestions(Request $request, PdfDiagnosticParser $pdfParser): array
    {
        $tempPath = null;

        if ($request->hasFile('file')) {
            $request->validate([
                'file' => 'required|file|mimes:pdf|max:30720',
            ]);
            $tempPath = $request->file('file')->getRealPath();
        } elseif ($request->filled('assignment_id')) {
            $assignment = Assignment::findOrFail($request->input('assignment_id'));
            if ($assignment->file_path) {
                $tempPath = DiagnosticTestController::resolvePdfPath($assignment->file_path);
            }
        }

        if (!$tempPath || !file_exists($tempPath)) {
            return [
                'success' => false,
                'message' => 'No valid PDF file received to parse.',
                'status'  => 400,
            ];
        }

        $parsed = $pdfParser->parsePdf($tempPath);

        if (empty($parsed['questions'])) {
            return [
                'success'        => false,
                'total_pages'    => $parsed['total_pages'] ?? 0,
                'detected_count' => 0,
                'message'        => 'No numbered questions could be automatically extracted from this PDF. You can still add questions manually.',
                'status'         => 200,
            ];
        }

        $formatted = [];
        foreach ($parsed['questions'] as $q) {
            $type = !empty($q['options']) ? 'mcq' : 'fill_in_blank';
            $firstChoice = !empty($q['options']) ? array_key_first($q['options']) : '';

            $formatted[] = [
                'number'         => (int) $q['number'],
                'type'           => $type,
                'title'          => "Question " . $q['number'],
                'page'           => $q['page'] ?? 1,
                'page_number'    => $q['page'] ?? 1,
                'prompt'         => $q['prompt'] ?? '',
                'options'        => $q['options'] ?? [],
                'options_labels' => $q['options'] ?? [],
                'correct_answer' => $firstChoice,
                'passage'        => $q['passage'] ?? null,
            ];
        }

        return [
            'success'        => true,
            'total_pages'    => $parsed['total_pages'] ?? 1,
            'detected_count' => count($formatted),
            'questions'      => $formatted,
            'message'        => 'Auto-detected ' . count($formatted) . ' questions from the PDF successfully!',
            'status'         => 200,
        ];
    }

    /**
     * Persist base64 image data URL or handle image storage for a question.
     * Returns the public URL (e.g. /storage/diagnostic_images/...) or null.
     */
    protected function persistQuestionImage(?string $imageRaw, int $assignmentId = 0, int $qNum = 1): ?string
    {
        if (empty($imageRaw)) {
            return null;
        }

        $imageRaw = trim($imageRaw);

        // If it's already a saved public path or external URL, keep it
        if (str_starts_with($imageRaw, '/storage/') || str_starts_with($imageRaw, 'http://') || str_starts_with($imageRaw, 'https://')) {
            return $imageRaw;
        }

        // If it's a base64 Data URL: data:image/png;base64,...
        if (preg_match('/^data:image\/(\w+);base64,(.+)$/is', $imageRaw, $matches)) {
            $ext = strtolower($matches[1]);
            if ($ext === 'jpeg') $ext = 'jpg';
            $data = base64_decode($matches[2]);
            if ($data === false) {
                return null;
            }

            $dir = 'diagnostic_images';
            Storage::disk('public')->makeDirectory($dir);
            $prefix = $assignmentId > 0 ? "task_{$assignmentId}_q_{$qNum}" : "q_{$qNum}";
            $fileName = "{$prefix}_" . uniqid() . ".{$ext}";
            $path = "{$dir}/{$fileName}";
            Storage::disk('public')->put($path, $data);

            return '/storage/' . $path;
        }

        return $imageRaw;
    }

    /**
     * AJAX endpoint for uploading or saving a cropped question image.
     */
    public function uploadQuestionImage(Request $request): array
    {
        $request->validate([
            'image_file' => 'nullable|image|max:15360',
            'image_data' => 'nullable|string',
            'task_id'    => 'nullable|integer',
            'q_number'   => 'nullable|integer',
        ]);

        $taskId = (int) $request->input('task_id', 0);
        $qNum   = (int) $request->input('q_number', 1);

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $path = $file->store('diagnostic_images', 'public');
            return ['success' => true, 'url' => '/storage/' . $path, 'status' => 200];
        }

        if ($request->filled('image_data')) {
            $url = $this->persistQuestionImage($request->input('image_data'), $taskId, $qNum);
            if ($url) {
                return ['success' => true, 'url' => $url, 'status' => 200];
            }
        }

        return ['success' => false, 'message' => 'No valid image provided.', 'status' => 422];
    }

    public function gradeTask(Request $request, int $assignmentId, int $studentId): StudentAssignment
    {
        $validated = $request->validate([
            'score'            => 'required|numeric|min:0|max:1000',
            'teacher_feedback' => 'nullable|string|max:3000',
            'graded_file'      => 'nullable|file|max:51200|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,png,jpg,jpeg',
        ]);

        $sub = StudentAssignment::firstOrNew([
            'assignment_id' => $assignmentId,
            'student_id'    => $studentId,
        ]);

        $sub->score            = $validated['score'];
        $sub->teacher_feedback = $validated['teacher_feedback'] ?? null;
        $sub->status           = 'graded';

        if ($request->hasFile('graded_file')) {
            $file = $request->file('graded_file');
            $dir = 'graded_tasks/' . $assignmentId;
            $savedName = 'graded_' . $studentId . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs($dir, $savedName, 'public');

            $sub->graded_file_path = $path;
            $sub->graded_file_name = $file->getClientOriginalName();
        }

        $sub->save();

        return $sub;
    }

    public function getTaskSubmissions(int $id): array
    {
        $assignment = Assignment::with(['results.student.user', 'results.interactiveSubmission'])->findOrFail($id);

        $submissions = $assignment->results->map(function ($sa) use ($assignment) {
            return [
                'student_id'                => $sa->student_id,
                'student_name'              => $sa->student?->user?->name ?? 'Student #' . $sa->student_id,
                'student_email'             => $sa->student?->user?->email ?? '-',
                'student_level'             => $sa->student?->level ?? '-',
                'status'                    => $sa->status,
                'score'                     => $sa->score,
                'total_marks'               => $assignment->total_marks ?? 100,
                'submitted_at'              => $sa->submitted_at ? $sa->submitted_at->format('d M Y, h:i A') : null,
                'submission_notes'          => $sa->submission_notes,
                'teacher_feedback'          => $sa->teacher_feedback,
                'file_name'                 => $sa->file_name,
                'file_url'                  => $sa->file_url,
                'graded_file_name'          => $sa->graded_file_name,
                'graded_file_url'           => $sa->graded_file_url,
                'interactive_submission_id' => $sa->interactiveSubmission?->id,
                'interactive_review_url'    => $sa->interactiveSubmission ? route('admin.submissions.review', $sa->interactiveSubmission->id) : null,
            ];
        });

        return [
            'task'        => [
                'id'          => $assignment->id,
                'title'       => $assignment->title,
                'type'        => $assignment->type,
                'total_marks' => $assignment->total_marks ?? 100,
            ],
            'submissions' => $submissions,
        ];
    }

    // ── Quiz & Question CRUD ─────────────────────────────────────────────────────

    public function createQuiz(Request $request): Quiz
    {
        $validated = $request->validate([
            'course_id'       => 'required|exists:courses,id',
            'title'           => 'required|string|max:255',
            'total_questions' => 'required|integer|min:1|max:100',
        ]);

        $quiz = Quiz::create($validated);
        $this->notifyEnrolledStudents($quiz);

        return $quiz;
    }

    public function updateQuiz(Quiz $quiz, Request $request): Quiz
    {
        $validated = $request->validate([
            'course_id'       => 'required|exists:courses,id',
            'title'           => 'required|string|max:255',
            'total_questions' => 'required|integer|min:1|max:100',
        ]);

        $quiz->update($validated);

        return $quiz;
    }

    public function deleteQuiz(int $id): string
    {
        $quiz = Quiz::findOrFail($id);
        $title = $quiz->title;
        $quiz->delete();

        return $title;
    }

    public function createQuestion(int $quizId, Request $request): Question
    {
        $quiz = Quiz::findOrFail($quizId);

        $validated = $request->validate([
            'type'            => 'required|in:text,image',
            'question_text'   => 'required_if:type,text|nullable|string',
            'image'           => 'required_if:type,image|nullable|image|max:2048', // 2MB Max
            'option_a'        => 'required|string|max:255',
            'option_b'        => 'required|string|max:255',
            'option_c'        => 'required|string|max:255',
            'option_d'        => 'required|string|max:255',
            'correct_option'  => 'required|in:A,B,C,D',
            'explanation'     => 'nullable|string',
        ]);

        $imagePath = null;
        if ($request->hasFile('image') && $validated['type'] === 'image') {
            $filename = time() . '_' . uniqid() . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move(public_path('uploads/questions'), $filename);
            $imagePath = 'uploads/questions/' . $filename;
        }

        $question = Question::create([
            'quiz_id'        => $quiz->id,
            'type'           => $validated['type'],
            'question_text'  => $validated['question_text'] ?? null,
            'image_path'     => $imagePath,
            'option_a'       => $validated['option_a'],
            'option_b'       => $validated['option_b'],
            'option_c'       => $validated['option_c'],
            'option_d'       => $validated['option_d'],
            'correct_option' => $validated['correct_option'],
            'explanation'    => $validated['explanation'] ?? null,
        ]);

        $this->notifyEnrolledStudents($quiz);

        return $question;
    }

    public function updateQuestion(Question $question, Request $request): Question
    {
        $validated = $request->validate([
            'type'            => 'required|in:text,image',
            'question_text'   => 'required_if:type,text|nullable|string',
            'image'           => 'nullable|image|max:2048',
            'option_a'        => 'required|string|max:255',
            'option_b'        => 'required|string|max:255',
            'option_c'        => 'required|string|max:255',
            'option_d'        => 'required|string|max:255',
            'correct_option'  => 'required|in:A,B,C,D',
            'explanation'     => 'nullable|string',
        ]);

        $imagePath = $question->image_path;
        if ($request->hasFile('image') && $validated['type'] === 'image') {
            // Delete old image if it exists
            if ($imagePath && file_exists(public_path($imagePath))) {
                @unlink(public_path($imagePath));
            }
            $filename = time() . '_' . uniqid() . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move(public_path('uploads/questions'), $filename);
            $imagePath = 'uploads/questions/' . $filename;
        } elseif ($validated['type'] === 'text') {
            // If switched to text, delete image
            if ($imagePath && file_exists(public_path($imagePath))) {
                @unlink(public_path($imagePath));
            }
            $imagePath = null;
        }

        $question->update([
            'type'           => $validated['type'],
            'question_text'  => $validated['type'] === 'text' ? $validated['question_text'] : null,
            'image_path'     => $imagePath,
            'option_a'       => $validated['option_a'],
            'option_b'       => $validated['option_b'],
            'option_c'       => $validated['option_c'],
            'option_d'       => $validated['option_d'],
            'correct_option' => $validated['correct_option'],
            'explanation'    => $validated['explanation'] ?? null,
        ]);

        return $question;
    }

    public function deleteQuestion(int $id): int
    {
        $question = Question::findOrFail($id);
        $imagePath = $question->image_path;
        if ($imagePath && file_exists(public_path($imagePath))) {
            @unlink(public_path($imagePath));
        }
        $quizId = $question->quiz_id;
        $question->delete();

        return $quizId;
    }

    private function notifyEnrolledStudents(Quiz $quiz): void
    {
        $tag = "[Quiz ID: {$quiz->id}]";
        $alreadyNotified = UserNotification::where('type', 'quiz')
            ->where('message', 'like', "%{$tag}%")
            ->exists();

        if ($alreadyNotified) {
            return;
        }

        $enrolledStudentIds = Enrollment::where('course_id', $quiz->course_id)->pluck('student_id');
        if ($enrolledStudentIds->isEmpty()) {
            return;
        }

        $userIds = Student::whereIn('id', $enrolledStudentIds)->pluck('user_id');
        if ($userIds->isEmpty()) {
            return;
        }

        $subjectName = $quiz->course->subject->name ?? 'General';

        foreach ($userIds as $userId) {
            UserNotification::create([
                'user_id' => $userId,
                'type'    => 'quiz',
                'title'   => 'NEW DRILL QUIZ ADDED',
                'message' => "A new practice drill '{$quiz->title}' has been added to your {$subjectName} workspace. {$tag}",
                'read_at' => null,
            ]);
        }
    }
}
