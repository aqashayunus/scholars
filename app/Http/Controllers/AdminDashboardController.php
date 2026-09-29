<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LiveClass;
use App\Models\Payment;
use App\Models\RecordedClass;
use App\Models\Setting;
use App\Models\Student;
use App\Models\StudentTopicMastery;
use App\Models\Subject;
use App\Models\SubjectTopic;
use App\Models\SubjectSubtopic;
use App\Models\Teacher;
use App\Models\TeacherApplication;
use App\Models\TeachingGig;
use App\Models\User;
use App\Models\AcademicEvent;
use App\Models\FinancialAuditLog;
use App\Models\Invoice;
use App\Models\InvoiceReminder;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Assignment;
use App\Models\StudentAssignment;
use App\Models\FeeStructure;
use App\Services\Admin\CertificateService;
use App\Services\Admin\ClassSchedulingService;
use App\Services\Admin\CurriculumService;
use App\Services\Admin\DashboardMetricsService;
use App\Services\Admin\GigService;
use App\Services\Admin\TeacherApplicationService;
use App\Services\Admin\UserManagementService;
use App\Services\PdfDiagnosticParser;
use App\Http\Requests\Admin\BlastEngagementEmailRequest;
use App\Http\Requests\Admin\ResetUserPasswordRequest;
use App\Http\Requests\Admin\StoreCertificateRequest;
use App\Http\Requests\Admin\StoreCourseRequest;
use App\Http\Requests\Admin\StoreGigRequest;
use App\Http\Requests\Admin\StoreLiveClassRequest;
use App\Http\Requests\Admin\StoreRecordedClassRequest;
use App\Http\Requests\Admin\StoreTeacherClassRequest;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateCourseRequest;
use App\Http\Requests\Admin\UpdateLiveClassRequest;
use App\Http\Requests\Admin\UpdateRecordedClassRequest;
use App\Http\Requests\Admin\UpdateSubtopicRequest;
use App\Http\Requests\Admin\UpdateTeacherSubjectsRequest;
use App\Http\Requests\Admin\UpdateTopicRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMail;
use App\Mail\EngagementBlastMail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminDashboardController extends Controller
{
    public function dashboard(Request $request, DashboardMetricsService $metrics)
    {
        return view('admin.dashboard', $metrics->build($request->input('diagnostic_id')));
    }


    // ── USER CRUD ─────────────────────────────────────────────────────────────

    public function storeUser(StoreUserRequest $request, UserManagementService $users)
    {
        $user = $users->createUser($request->validated());

        return redirect()->to(route('admin.dashboard') . '#users')
            ->with('success', "User '{$user->name}' created successfully. Welcome email dispatched to {$user->email}.");
    }

    public function updateUser(UpdateUserRequest $request, $id, UserManagementService $users)
    {
        $user = User::findOrFail($id);
        $user = $users->updateUser($user, $request->validated());

        return redirect()->to(route('admin.dashboard') . '#users')
            ->with('success', "User '{$user->name}' updated successfully.");
    }

    public function destroyUser($id, UserManagementService $users)
    {
        if (auth()->id() == $id) {
            return redirect()->to(route('admin.dashboard') . '#users')
                ->with('error', 'Cannot delete your own active administrator account.');
        }

        $userName = $users->deleteUser($id);

        return redirect()->to(route('admin.dashboard') . '#users')
            ->with('success', "User '{$userName}' deleted successfully.");
    }

    public function resetUserPassword(ResetUserPasswordRequest $request, $id, UserManagementService $users)
    {
        $user = User::findOrFail($id);
        $users->resetPassword($user, $request->validated()['password'] ?? null);

        return redirect()->to(route('admin.dashboard') . '#users')
            ->with('success', "Password for '{$user->name}' has been reset. They will be asked to set a new password on next login.");
    }

    // ── COURSE CRUD ───────────────────────────────────────────────────────────

    public function storeCourse(StoreCourseRequest $request, CurriculumService $curriculum)
    {
        [$course, $monthlyFee] = $curriculum->createCourse($request->validated());

        return redirect()->to(route('admin.dashboard') . '#courses-catalog')
            ->with('success', "Course '{$course->title}' created. Subject fee set to " . Setting::get('currency_symbol', 'RM') . ' ' . number_format($monthlyFee, 2) . "/month.");
    }

    public function updateCourse(UpdateCourseRequest $request, $id, CurriculumService $curriculum)
    {
        $course = Course::findOrFail($id);
        [$course, $monthlyFee] = $curriculum->updateCourse($course, $request->validated());

        return redirect()->to(route('admin.dashboard') . '#courses-catalog')
            ->with('success', "Course '{$course->title}' updated. Subject fee: " . Setting::get('currency_symbol', 'RM') . ' ' . number_format($monthlyFee, 2) . "/month.");
    }

    public function destroyCourse($id, CurriculumService $curriculum)
    {
        $title = $curriculum->deleteCourse($id);

        return redirect()->to(route('admin.dashboard') . '#courses-catalog')
            ->with('success', "Course '{$title}' removed from catalog.");
    }

    // ── SUBJECT CRUD ─────────────────────────────────────────────────────────

    public function storeSubject(Request $request, CurriculumService $curriculum)
    {
        $result = $curriculum->storeSubject($request);

        if ($result['bulk']) {
            return redirect()->to(route('admin.dashboard') . '#courses-subjects')
                ->with('success', "{$result['count']} KPM curriculum subject(s) created successfully.");
        }

        $subject = $result['subject'];

        return redirect()->to(route('admin.dashboard') . '#courses-subjects')
            ->with('success', "Subject '{$subject->name}' ({$subject->code}) created successfully.");
    }

    public function updateSubject(Request $request, $id, CurriculumService $curriculum)
    {
        $subject = Subject::findOrFail($id);
        $subject = $curriculum->updateSubject($subject, $request);

        return redirect()->to(route('admin.dashboard') . '#courses-subjects')
            ->with('success', "Subject '{$subject->name}' updated successfully.");
    }

    public function destroySubject($id, CurriculumService $curriculum)
    {
        $name = $curriculum->deleteSubject($id);

        return redirect()->to(route('admin.dashboard') . '#courses-subjects')
            ->with('success', "Subject '{$name}' deleted successfully.");
    }

    // ── TOPIC & SUBTOPIC CRUD (CURRICULUM SYLLABUS BUILDER) ───────────────────

    public function storeTopic(Request $request, CurriculumService $curriculum)
    {
        $result = $curriculum->storeTopic($request);

        if (isset($result['error'])) {
            return redirect()->to(route('admin.dashboard') . '#courses-topics')
                ->with('error', $result['error']);
        }

        if ($result['bulk']) {
            return redirect()->to(route('admin.dashboard') . '#courses-topics')
                ->with('success', "{$result['count']} curriculum topic(s) added successfully.");
        }

        $topic = $result['topic'];

        return redirect()->to(route('admin.dashboard') . '#courses-topics')
            ->with('success', "Curriculum Topic 'Chapter {$topic->chapter_number}: {$topic->title}' added successfully.");
    }

    public function updateTopic(UpdateTopicRequest $request, $id, CurriculumService $curriculum)
    {
        $topic = SubjectTopic::findOrFail($id);
        $topic = $curriculum->updateTopic($topic, $request);

        return redirect()->to(route('admin.dashboard') . '#courses-topics')
            ->with('success', "Curriculum Topic '{$topic->title}' updated successfully.");
    }

    public function destroyTopic($id, CurriculumService $curriculum)
    {
        $title = $curriculum->deleteTopic($id);

        return redirect()->to(route('admin.dashboard') . '#courses-topics')
            ->with('success', "Curriculum Topic '{$title}' and its subtopics deleted successfully.");
    }

    public function storeSubtopic(Request $request, $topicId, CurriculumService $curriculum)
    {
        $result = $curriculum->storeSubtopic($topicId, $request);
        $topic = $result['topic'];

        if ($result['bulk']) {
            return redirect()->to(route('admin.dashboard') . '#courses-topics')
                ->with('success', "{$result['count']} checkpoint(s) added under '{$topic->title}'.");
        }

        $subtopic = $result['subtopic'];

        return redirect()->to(route('admin.dashboard') . '#courses-topics')
            ->with('success', "Subtopic '{$subtopic->title}' added under {$topic->title}.");
    }

    public function updateSubtopic(UpdateSubtopicRequest $request, $id, CurriculumService $curriculum)
    {
        $subtopic = SubjectSubtopic::findOrFail($id);
        $subtopic = $curriculum->updateSubtopic($subtopic, $request->validated());

        return redirect()->to(route('admin.dashboard') . '#courses-topics')
            ->with('success', "Subtopic '{$subtopic->title}' updated successfully.");
    }

    public function destroySubtopic($id, CurriculumService $curriculum)
    {
        $title = $curriculum->deleteSubtopic($id);

        return redirect()->to(route('admin.dashboard') . '#courses-topics')
            ->with('success', "Subtopic '{$title}' deleted successfully.");
    }

    // ── LIVE CLASS CRUD ──────────────────────────────────────────────────────

    public function storeLiveClass(StoreLiveClassRequest $request, ClassSchedulingService $scheduling)
    {
        $liveClass = $scheduling->createLiveClass($request->validated());

        return redirect()->to(route('admin.dashboard') . '#courses-live')
            ->with('success', "Live Class '{$liveClass->title}' scheduled successfully.");
    }

    public function updateLiveClass(UpdateLiveClassRequest $request, $id, ClassSchedulingService $scheduling)
    {
        $liveClass = LiveClass::findOrFail($id);
        $liveClass = $scheduling->updateLiveClass($liveClass, $request->validated());

        return redirect()->to(route('admin.dashboard') . '#courses-live')
            ->with('success', "Live Class '{$liveClass->title}' updated successfully.");
    }

    public function destroyLiveClass($id, ClassSchedulingService $scheduling)
    {
        $title = $scheduling->deleteLiveClass($id);

        return redirect()->to(route('admin.dashboard') . '#courses-live')
            ->with('success', "Live Class '{$title}' removed.");
    }

    // ── RECORDED CLASS CRUD ──────────────────────────────────────────────────

    public function storeRecordedClass(StoreRecordedClassRequest $request, ClassSchedulingService $scheduling)
    {
        $recordedClass = $scheduling->createRecordedClass($request->validated());

        return redirect()->to(route('admin.dashboard') . '#courses-recorded')
            ->with('success', "Recorded Class '{$recordedClass->title}' added to vault.");
    }

    public function updateRecordedClass(UpdateRecordedClassRequest $request, $id, ClassSchedulingService $scheduling)
    {
        $recordedClass = RecordedClass::findOrFail($id);
        $recordedClass = $scheduling->updateRecordedClass($recordedClass, $request->validated());

        return redirect()->to(route('admin.dashboard') . '#courses-recorded')
            ->with('success', "Recorded Class '{$recordedClass->title}' updated successfully.");
    }

    public function destroyRecordedClass($id, ClassSchedulingService $scheduling)
    {
        $title = $scheduling->deleteRecordedClass($id);

        return redirect()->to(route('admin.dashboard') . '#courses-recorded')
            ->with('success', "Recorded Class '{$title}' deleted.");
    }

    // ── TASKS & DIAGNOSTIC TESTS CRUD ─────────────────────────────────────────

    public function storeTask(Request $request)
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
            $sectionBQuestions = (int) ($request->input('section_b_questions') ?: ($sectionBMap ? count($sectionBMap) : 0));
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
            'section_b_answer_key' => $sectionBMap,
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

            $redirectTab = $request->input('redirect_tab', '#courses-tasks');
            return redirect()->to(route('admin.dashboard') . $redirectTab)
                ->with('success', 'Personal task/notes document "' . $assignment->title . '" uploaded and assigned to student successfully.');
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

        $redirectTab = $request->input('redirect_tab', '#courses-tasks');
        return redirect()->to(route('admin.dashboard') . $redirectTab)
            ->with('success', 'Task / Diagnostic Test "' . $assignment->title . '" created and assigned to all eligible students successfully.');
    }

    public function updateTask(Request $request, $id)
    {
        $assignment = Assignment::findOrFail($id);

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

        return redirect()->to(route('admin.dashboard') . '#courses-tasks')
            ->with('success', 'Task "' . $assignment->title . '" updated successfully.');
    }

    public function destroyTask($id)
    {
        $assignment = Assignment::findOrFail($id);
        $title = $assignment->title;

        // Explicitly remove all student assignment records
        StudentAssignment::where('assignment_id', $assignment->id)->delete();

        if ($assignment->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($assignment->file_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($assignment->file_path);
        }

        $assignment->delete();

        return redirect()->to(route('admin.dashboard') . '#courses-tasks')
            ->with('success', 'Task "' . $title . '" deleted successfully.');
    }

    /**
     * Auto-detect and parse questions, prompts, options, and page mapping from an uploaded PDF.
     */
    public function parsePdfQuestions(Request $request, PdfDiagnosticParser $pdfParser)
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
                $tempPath = \App\Http\Controllers\DiagnosticTestController::resolvePdfPath($assignment->file_path);
            }
        }

        if (!$tempPath || !file_exists($tempPath)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid PDF file received to parse.',
            ], 400);
        }

        $parsed = $pdfParser->parsePdf($tempPath);

        if (empty($parsed['questions'])) {
            return response()->json([
                'success'        => false,
                'total_pages'    => $parsed['total_pages'] ?? 0,
                'detected_count' => 0,
                'message'        => 'No numbered questions could be automatically extracted from this PDF. You can still add questions manually.',
            ]);
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

        return response()->json([
            'success'        => true,
            'total_pages'    => $parsed['total_pages'] ?? 1,
            'detected_count' => count($formatted),
            'questions'      => $formatted,
            'message'        => 'Auto-detected ' . count($formatted) . ' questions from the PDF successfully!',
        ]);
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
    public function uploadQuestionImage(Request $request)
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
            return response()->json([
                'success' => true,
                'url'     => '/storage/' . $path,
            ]);
        }

        if ($request->filled('image_data')) {
            $url = $this->persistQuestionImage($request->input('image_data'), $taskId, $qNum);
            if ($url) {
                return response()->json([
                    'success' => true,
                    'url'     => $url,
                ]);
            }
        }

        return response()->json(['success' => false, 'message' => 'No valid image provided.'], 422);
    }

    public function gradeTask(Request $request, $assignmentId, $studentId)
    {
        $validated = $request->validate([
            'score'            => 'required|numeric|min:0|max:1000',
            'teacher_feedback' => 'nullable|string|max:3000',
        ]);

        $sub = StudentAssignment::firstOrNew([
            'assignment_id' => $assignmentId,
            'student_id'    => $studentId,
        ]);

        $sub->score            = $validated['score'];
        $sub->teacher_feedback = $validated['teacher_feedback'] ?? null;
        $sub->status           = 'graded';
        $sub->save();

        return redirect()->to(route('admin.dashboard') . '#courses-tasks')
            ->with('success', 'Student submission graded successfully.');
    }

    public function getTaskSubmissions($id)
    {
        $assignment = Assignment::with(['results.student.user'])->findOrFail($id);

        $submissions = $assignment->results->map(function ($sa) use ($assignment) {
            return [
                'student_id'       => $sa->student_id,
                'student_name'     => $sa->student?->user?->name ?? 'Student #' . $sa->student_id,
                'student_email'    => $sa->student?->user?->email ?? '-',
                'student_level'    => $sa->student?->level ?? '-',
                'status'           => $sa->status,
                'score'            => $sa->score,
                'total_marks'      => $assignment->total_marks ?? 100,
                'submitted_at'     => $sa->submitted_at ? $sa->submitted_at->format('d M Y, h:i A') : null,
                'submission_notes' => $sa->submission_notes,
                'teacher_feedback' => $sa->teacher_feedback,
                'file_name'        => $sa->file_name,
                'file_url'         => $sa->file_url,
            ];
        });

        return response()->json([
            'task'        => [
                'id'          => $assignment->id,
                'title'       => $assignment->title,
                'type'        => $assignment->type,
                'total_marks' => $assignment->total_marks ?? 100,
            ],
            'submissions' => $submissions,
        ]);
    }

    // ── QUIZ & QUESTION CRUD ──────────────────────────────────────────────────

    public function storeQuiz(Request $request)
    {
        $validated = $request->validate([
            'course_id'       => 'required|exists:courses,id',
            'title'           => 'required|string|max:255',
            'total_questions' => 'required|integer|min:1|max:100',
        ]);

        $quiz = Quiz::create($validated);
        $this->notifyEnrolledStudents($quiz);

        return redirect()->to(route('admin.dashboard') . '#courses-quizzes')
            ->with('success', "Quiz '{$quiz->title}' created successfully.");
    }

    public function updateQuiz(Request $request, $id)
    {
        $quiz = Quiz::findOrFail($id);

        $validated = $request->validate([
            'course_id'       => 'required|exists:courses,id',
            'title'           => 'required|string|max:255',
            'total_questions' => 'required|integer|min:1|max:100',
        ]);

        $quiz->update($validated);

        return redirect()->to(route('admin.dashboard') . '#courses-quizzes')
            ->with('success', "Quiz '{$quiz->title}' updated successfully.");
    }

    public function destroyQuiz($id)
    {
        $quiz = Quiz::findOrFail($id);
        $title = $quiz->title;
        $quiz->delete();

        return redirect()->to(route('admin.dashboard') . '#courses-quizzes')
            ->with('success', "Quiz '{$title}' deleted.");
    }

    public function storeQuestion(Request $request, $quizId)
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

        Question::create([
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

        return redirect()->to(route('admin.dashboard', ['manage_quiz' => $quiz->id]) . '#courses')
            ->with('success', "Question added to quiz '{$quiz->title}'.");
    }

    public function updateQuestion(Request $request, $id)
    {
        $question = Question::findOrFail($id);

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

        return redirect()->to(route('admin.dashboard', ['manage_quiz' => $question->quiz_id]) . '#courses')
            ->with('success', "Question updated successfully.");
    }

    public function destroyQuestion($id)
    {
        $question = Question::findOrFail($id);
        $imagePath = $question->image_path;
        if ($imagePath && file_exists(public_path($imagePath))) {
            @unlink(public_path($imagePath));
        }
        $quizId = $question->quiz_id;
        $question->delete();

        return redirect()->to(route('admin.dashboard', ['manage_quiz' => $quizId]) . '#courses')
            ->with('success', "Question deleted.");
    }

    // ── TEACHER APPLICATIONS ──────────────────────────────────────────────────

    public function approveTeacherApplication(Request $request, $id, TeacherApplicationService $teacherApplications)
    {
        $application = TeacherApplication::findOrFail($id);
        $teacherApplications->approve($application, $request->input('admin_notes'));

        return back()->with('success', "Teacher application for '{$application->full_name}' has been approved and their educator account is now active.");
    }

    public function rejectTeacherApplication(Request $request, $id, TeacherApplicationService $teacherApplications)
    {
        $application = TeacherApplication::findOrFail($id);
        $teacherApplications->reject($application, $request->input('admin_notes'));

        return back()->with('success', "Teacher application for '{$application->full_name}' has been marked as rejected.");
    }

    // ── TEACHING GIGS ─────────────────────────────────────────────────────────

    public function storeGig(StoreGigRequest $request, GigService $gigs)
    {
        $gig = $gigs->publish($request->validated());

        return redirect()->to(route('admin.dashboard') . '#records')
            ->with('success', "Teaching Gig '{$gig->title}' published with payout " . Setting::get('currency_symbol', 'RM') . ' ' . number_format($gig->payout_amount, 2) . ".");
    }

    public function destroyGig($id, GigService $gigs)
    {
        $title = $gigs->remove($id);

        return redirect()->to(route('admin.dashboard') . '#records')
            ->with('success', "Teaching Gig '{$title}' removed.");
    }

    // ── CSV EXPORTS ───────────────────────────────────────────────────────────

    public function exportPaymentsCsv(): StreamedResponse
    {
        $payments = Payment::with(['user', 'course'])->latest('id')->get();
        $symbol = Setting::get('currency_symbol', 'RM');
        $fileName = strtolower(str_replace(' ', '_', Setting::get('platform_name', 'olympiascholar'))) . '_payments_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($payments, $symbol) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Invoice ID', 'Payer Name', 'Payer Email', 'Course Item', 'Payment Method', "Amount ({$symbol})", 'Status', 'Payment Date']);

            foreach ($payments as $pay) {
                fputcsv($handle, [
                    'INV-' . str_pad($pay->id, 5, '0', STR_PAD_LEFT),
                    $pay->user->name ?? 'User #' . $pay->user_id,
                    $pay->user->email ?? 'N/A',
                    $pay->course->title ?? $pay->description ?? 'Tuition Fee',
                    $pay->payment_method ?? 'FPX Online',
                    number_format($pay->amount, 2, '.', ''),
                    strtoupper($pay->status?->value ?? 'PAID'),
                    $pay->paid_at ? $pay->paid_at->format('Y-m-d H:i') : ($pay->created_at ? $pay->created_at->format('Y-m-d H:i') : 'N/A'),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    public function exportEnrollmentsCsv(): StreamedResponse
    {
        $enrollments = Enrollment::with(['student.user', 'course.subject', 'course.teacher.user'])->latest('id')->get();
        $fileName = strtolower(str_replace(' ', '_', Setting::get('platform_name', 'olympiascholar'))) . '_enrollments_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($enrollments) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Enrollment ID', 'Student Name', 'Student Email', 'Course Title', 'Subject', 'Teacher Name', 'Progress (%)', 'Enrolled Date']);

            foreach ($enrollments as $enr) {
                fputcsv($handle, [
                    'ENR-' . str_pad($enr->id, 5, '0', STR_PAD_LEFT),
                    $enr->student?->user?->name ?? 'Student #' . $enr->student_id,
                    $enr->student?->user?->email ?? 'N/A',
                    $enr->course->title ?? 'Course #' . $enr->course_id,
                    $enr->course->subject->name ?? 'General',
                    $enr->course->teacher->user->name ?? 'Staff',
                    $enr->progress_pct ?? 0,
                    $enr->enrolled_at ? $enr->enrolled_at->format('Y-m-d H:i') : ($enr->created_at ? $enr->created_at->format('Y-m-d H:i') : 'N/A'),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    // ── SETTINGS & PLATFORM CONFIG ────────────────────────────────────────────

    public function settings()
    {
        $admin = auth()->user();

        // Load all settings from DB (not session)
        $platformSettings = [
            'platform_name'       => Setting::get('platform_name', 'OlympiaScholar'),
            'platform_tagline'    => Setting::get('platform_tagline', 'Excel Beyond Limits'),
            'support_email'       => Setting::get('support_email', 'support@olympiascholar.com'),
            'support_phone'       => Setting::get('support_phone', '+60 12-345 6789'),
            'teacher_cut_pct'     => Setting::get('teacher_cut_pct', 65),
            'currency_code'       => Setting::get('currency_code', 'MYR'),
            'currency_symbol'     => Setting::get('currency_symbol', 'RM'),
            'invoice_due_days'    => (int) Setting::get('billing.invoice_due_days', 7),
            'allow_registrations' => Setting::get('allow_registrations', 1),
            'allow_gig_claims'    => Setting::get('allow_gig_claims', 1),
            'maintenance_mode'    => Setting::get('maintenance_mode', 0),
        ];

        $pricingPlans = [
            'monthly_price'  => Setting::get('plan_monthly_price', 'RM 99'),
            'monthly_per'    => Setting::get('plan_monthly_per', '/month'),
            'monthly_desc'   => Setting::get('plan_monthly_desc', 'Access to 1 subject'),
            'subject_price'  => Setting::get('plan_subject_price', 'From RM 150'),
            'subject_per'    => Setting::get('plan_subject_per', '/subject'),
            'subject_desc'   => Setting::get('plan_subject_desc', '2-4 subjects bundled'),
            'premium_price'  => Setting::get('plan_premium_price', 'From RM 199'),
            'premium_per'    => Setting::get('plan_premium_per', '/month'),
            'premium_desc'   => Setting::get('plan_premium_desc', 'Unlimited subjects'),
        ];

        $securitySettings = [
            'default_temp_password' => Setting::get('default_temp_password', 'Temp@1234'),
        ];

        $diagnostics = [
            'php_version'       => PHP_VERSION,
            'laravel_version'   => app()->version(),
            'db_connection'     => config('database.default'),
            'db_host'           => config('database.connections.mysql.host', '127.0.0.1'),
            'db_name'           => config('database.connections.mysql.database', 'olympiascholar'),
            'cache_driver'      => config('cache.default', 'file'),
            'app_env'           => config('app.env', 'local'),
            'app_debug'         => config('app.debug') ? 'Enabled' : 'Disabled',
            'timezone'          => config('app.timezone', 'UTC'),
            'total_users'       => User::count(),
            'total_payments'    => Payment::count(),
            'total_courses'     => Course::count(),
            'total_enrollments' => Enrollment::count(),
            'total_settings'    => \App\Models\Setting::count(),
        ];

        return view('admin.settings', compact(
            'admin', 'diagnostics', 'platformSettings', 'pricingPlans', 'securitySettings'
        ));
    }

    public function updateProfile(Request $request)
    {
        $admin = auth()->user();

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email,' . $admin->id,
            'phone'    => 'nullable|string|max:30',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $admin->name  = $validated['name'];
        $admin->email = $validated['email'];
        $admin->phone = $validated['phone'] ?? null;

        if (!empty($validated['password'])) {
            $admin->password = Hash::make($validated['password']);
        }
        $admin->save();

        return back()->with('success', 'Admin profile and credentials updated successfully.');
    }

    public function updatePlatformSettings(Request $request)
    {
        $request->validate([
            'platform_name'    => 'required|string|max:100',
            'platform_tagline' => 'nullable|string|max:150',
            'support_email'    => 'required|email|max:100',
            'support_phone'    => 'nullable|string|max:30',
            'teacher_cut_pct'  => 'required|numeric|min:0|max:100',
            'currency_code'    => 'required|string|max:10',
            'currency_symbol'  => 'required|string|max:10',
            'invoice_due_days' => 'nullable|integer|min:1|max:180',
        ]);

        Setting::set('platform_name',       $request->platform_name,               'general',   'Platform Name');
        Setting::set('platform_tagline',    $request->platform_tagline ?? '',       'general',   'Platform Tagline');
        Setting::set('support_email',       $request->support_email,               'general',   'Support Email');
        Setting::set('support_phone',       $request->support_phone ?? '',          'general',   'Support Phone');
        Setting::set('teacher_cut_pct',     $request->teacher_cut_pct,             'financial', 'Teacher Revenue Cut (%)');
        Setting::set('currency_code',       $request->currency_code,               'financial', 'Currency Code');
        Setting::set('currency_symbol',     $request->currency_symbol,             'financial', 'Currency Symbol');
        if ($request->filled('invoice_due_days')) {
            Setting::set('billing.invoice_due_days', (string)$request->invoice_due_days, 'billing', 'Invoice Due Days (after issue date)');
        }
        Setting::set('allow_registrations', $request->has('allow_registrations') ? '1' : '0', 'features', 'Allow Public Registrations');
        Setting::set('allow_gig_claims',    $request->has('allow_gig_claims')    ? '1' : '0', 'features', 'Allow Teacher Gig Claiming');
        Setting::set('maintenance_mode',    $request->has('maintenance_mode')    ? '1' : '0', 'features', 'Maintenance Mode');

        Setting::clearCache();

        return back()->with('success', 'Platform configuration saved to database successfully.');
    }

    public function updatePricingPlans(Request $request)
    {
        $request->validate([
            'monthly_price' => 'required|string|max:50',
            'monthly_per'   => 'required|string|max:20',
            'subject_price' => 'required|string|max:50',
            'subject_per'   => 'required|string|max:20',
            'premium_price' => 'required|string|max:50',
            'premium_per'   => 'required|string|max:20',
        ]);

        Setting::set('plan_monthly_price', $request->monthly_price, 'pricing', 'Monthly Plan — Display Price');
        Setting::set('plan_monthly_per',   $request->monthly_per,   'pricing', 'Monthly Plan — Billing Period');
        Setting::set('plan_subject_price', $request->subject_price, 'pricing', 'Subject Package — Display Price');
        Setting::set('plan_subject_per',   $request->subject_per,   'pricing', 'Subject Package — Billing Period');
        Setting::set('plan_premium_price', $request->premium_price, 'pricing', 'Premium Plan — Display Price');
        Setting::set('plan_premium_per',   $request->premium_per,   'pricing', 'Premium Plan — Billing Period');

        Setting::clearCache();

        return back()->with('success', 'Public subscription plan pricing updated in database. Changes are now live on /pricing.');
    }

    public function updateSecuritySettings(Request $request)
    {
        $request->validate([
            'default_temp_password' => 'required|string|min:6|max:50',
        ]);

        Setting::set('default_temp_password', $request->default_temp_password, 'security', 'Default Temporary Password');
        Setting::clearCache();

        return back()->with('success', 'Security settings updated in database.');
    }

    protected function notifyEnrolledStudents(Quiz $quiz)
    {
        $tag = "[Quiz ID: {$quiz->id}]";
        $alreadyNotified = \App\Models\UserNotification::where('type', 'quiz')
            ->where('message', 'like', "%{$tag}%")
            ->exists();

        if ($alreadyNotified) {
            return;
        }

        $enrolledStudentIds = \App\Models\Enrollment::where('course_id', $quiz->course_id)->pluck('student_id');
        if ($enrolledStudentIds->isEmpty()) {
            return;
        }

        $userIds = \App\Models\Student::whereIn('id', $enrolledStudentIds)->pluck('user_id');
        if ($userIds->isEmpty()) {
            return;
        }

        $subjectName = $quiz->course->subject->name ?? 'General';

        foreach ($userIds as $userId) {
            \App\Models\UserNotification::create([
                'user_id' => $userId,
                'type'    => 'quiz',
                'title'   => 'NEW DRILL QUIZ ADDED',
                'message' => "A new practice drill '{$quiz->title}' has been added to your {$subjectName} workspace. {$tag}",
                'read_at' => null,
            ]);
        }
    }


    /**
     * Store and award an official certificate to a student.
     */
    public function storeCertificate(StoreCertificateRequest $request, CertificateService $certificates)
    {
        $certificates->issue($request->validated(), $request->file('file'));

        return back()->with('success', 'Official Certificate successfully awarded and issued to student!');
    }

    /**
     * Delete an issued certificate.
     */
    public function destroyCertificate($id, CertificateService $certificates)
    {
        $certificates->destroy($id);

        return back()->with('success', 'Certificate removed successfully.');
    }

    // ── FINANCIAL & FEE MANAGEMENT CRUD ─────────────────────────────────────────

    public function storeFeeStructure(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string',
            'amount'          => 'required|numeric|min:0',
            'education_level' => 'required|string|max:100',
            'frequency'       => 'required|in:monthly,term,one_time,yearly',
            'is_active'       => 'nullable',
            'student_id'      => 'nullable|exists:students,id',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        if (empty($validated['student_id'])) {
            $validated['student_id'] = null;
        }

        $fee = \App\Models\FeeStructure::create($validated);

        \App\Models\FinancialAuditLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'package_created',
            'target_type' => 'FeeStructure',
            'ip_address'  => $request->ip(),
            'details'     => "Created fee structure '{$fee->name}' at RM {$fee->amount}" . ($fee->student_id ? " (Targeted to student #{$fee->student_id})" : ""),
        ]);

        return back()->with('success', "Fee structure '{$fee->name}' created successfully!");
    }

    public function updateFeeStructure(Request $request, $id)
    {
        $fee = \App\Models\FeeStructure::findOrFail($id);
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string',
            'amount'          => 'required|numeric|min:0',
            'education_level' => 'required|string|max:100',
            'frequency'       => 'required|in:monthly,term,one_time,yearly',
            'is_active'       => 'nullable',
            'student_id'      => 'nullable|exists:students,id',
            // Package engine fields
            'package_type'     => 'nullable|in:individual,fixed_bundle,pick_n,tiered,custom',
            'minimum_subjects' => 'nullable|integer|min:1|max:20',
            'maximum_subjects' => 'nullable|integer|min:1|max:20|gte:minimum_subjects',
            'effective_from'   => 'nullable|date',
            'effective_until'  => 'nullable|date|after_or_equal:effective_from',
            'status'           => 'nullable|in:draft,active,inactive,archived',
        ]);

        $validated['is_active']  = $request->boolean('is_active', true);
        $validated['student_id'] = $request->filled('student_id') ? $validated['student_id'] : null;

        $oldValues = $fee->only(['name', 'amount', 'package_type', 'status']);
        $fee->update($validated);

        \App\Models\FinancialAuditLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'package_updated',
            'target_type' => 'FeeStructure',
            'target_id'   => $fee->id,
            'ip_address'  => $request->ip(),
            'details'     => json_encode([
                'old' => $oldValues,
                'new' => $fee->only(['name', 'amount', 'package_type', 'status']),
            ]),
        ]);

        return back()->with('success', "Fee structure '{$fee->name}' updated successfully.");
    }

    public function destroyFeeStructure($id)
    {
        $fee = \App\Models\FeeStructure::findOrFail($id);
        $name = $fee->name;
        // Soft delete (SoftDeletes trait on FeeStructure)
        // Invoice references (fee_structure_id) remain intact for history
        $fee->delete();
        return back()->with('success', "Fee structure '{$name}' removed.");
    }

    // ── PACKAGE ENGINE METHODS ────────────────────────────────────────────────

    /**
     * Replace all subject associations for a fee structure package.
     */
    public function updateFeeStructureSubjects(Request $request, $id)
    {
        $fee = \App\Models\FeeStructure::findOrFail($id);

        $validated = $request->validate([
            'subjects'                  => 'nullable|array',
            'subjects.*.subject_id'     => 'required|exists:subjects,id',
            'subjects.*.is_required'    => 'required|boolean',
            'subjects.*.sort_order'     => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($fee, $validated, $request) {
            // Sync subjects using the pivot table
            $syncData = [];
            foreach ($validated['subjects'] ?? [] as $idx => $sub) {
                $syncData[$sub['subject_id']] = [
                    'is_required' => (bool) $sub['is_required'],
                    'sort_order'  => (int) ($sub['sort_order'] ?? $idx),
                ];
            }
            $fee->subjects()->sync($syncData);

            \App\Models\FinancialAuditLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'subject_sync',
                'target_type' => 'FeeStructure',
                'target_id'   => $fee->id,
                'ip_address'  => $request->ip(),
                'details'     => json_encode(['subject_ids' => array_keys($syncData)]),
            ]);
        });

        return response()->json(['success' => true, 'message' => 'Subjects updated for "' . $fee->name . '".']);
    }

    /**
     * Replace all billing components (items) for a fee structure.
     */
    public function updateFeeStructureItems(Request $request, $id)
    {
        $fee = \App\Models\FeeStructure::findOrFail($id);

        $validated = $request->validate([
            'items'                      => 'nullable|array',
            'items.*.name'               => 'required|string|max:255',
            'items.*.description'        => 'nullable|string',
            'items.*.item_type'          => 'required|in:registration,tuition,material,resource,assessment,deposit,other',
            'items.*.amount'             => 'required|numeric|min:0',
            'items.*.quantity'           => 'nullable|integer|min:1',
            'items.*.billing_frequency'  => 'required|in:one_time,monthly,term,annual',
            'items.*.is_optional'        => 'nullable|boolean',
            'items.*.is_refundable'      => 'nullable|boolean',
            'items.*.sort_order'         => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($fee, $validated, $request) {
            $fee->items()->delete();

            foreach ($validated['items'] ?? [] as $idx => $item) {
                $fee->items()->create([
                    'name'              => $item['name'],
                    'description'       => $item['description'] ?? null,
                    'item_type'         => $item['item_type'],
                    'amount'            => $item['amount'],
                    'quantity'          => $item['quantity'] ?? 1,
                    'billing_frequency' => $item['billing_frequency'],
                    'is_optional'       => $item['is_optional'] ?? false,
                    'is_refundable'     => $item['is_refundable'] ?? false,
                    'sort_order'        => $item['sort_order'] ?? $idx,
                ]);
            }

            \App\Models\FinancialAuditLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'billing_component_sync',
                'target_type' => 'FeeStructure',
                'target_id'   => $fee->id,
                'ip_address'  => $request->ip(),
                'details'     => json_encode(['items_count' => count($validated['items'] ?? [])]),
            ]);
        });

        return response()->json(['success' => true, 'message' => 'Billing components updated.']);
    }

    /**
     * Replace all pricing tiers for a tiered fee structure.
     */
    public function updateFeeStructureTiers(Request $request, $id)
    {
        $fee = \App\Models\FeeStructure::findOrFail($id);

        $validated = $request->validate([
            'tiers'                       => 'required|array|min:1',
            'tiers.*.minimum_subjects'    => 'required|integer|min:1',
            'tiers.*.maximum_subjects'    => 'required|integer|min:1|gte:tiers.*.minimum_subjects',
            'tiers.*.price'               => 'required|numeric|min:0',
            'tiers.*.sort_order'          => 'nullable|integer|min:0',
        ]);

        // Validate non-overlapping tiers
        app(\App\Services\PackagePricingService::class)->validateTiers($validated['tiers']);

        DB::transaction(function () use ($fee, $validated, $request) {
            $fee->priceTiers()->delete();

            foreach ($validated['tiers'] as $idx => $tier) {
                $fee->priceTiers()->create([
                    'minimum_subjects' => $tier['minimum_subjects'],
                    'maximum_subjects' => $tier['maximum_subjects'],
                    'price'            => $tier['price'],
                    'sort_order'       => $tier['sort_order'] ?? $idx,
                ]);
            }

            \App\Models\FinancialAuditLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'pricing_changed',
                'target_type' => 'FeeStructure',
                'target_id'   => $fee->id,
                'ip_address'  => $request->ip(),
                'details'     => json_encode(['tiers' => $validated['tiers']]),
            ]);
        });

        return response()->json(['success' => true, 'message' => 'Pricing tiers updated.']);
    }

    /**
     * Duplicate an existing fee structure (copy config, subjects, items, tiers).
     * Does NOT copy enrollments, invoices, or payments.
     */
    public function duplicateFeeStructure(Request $request, $id)
    {
        $original = \App\Models\FeeStructure::with(['subjects', 'items', 'priceTiers'])->findOrFail($id);

        $new = DB::transaction(function () use ($original, $request) {
            $new = $original->replicate(['student_id', 'parent_fee_structure_id', 'version', 'deleted_at']);
            $new->name      = 'Copy of ' . $original->name;
            $new->status    = \App\Models\FeeStructure::STATUS_DRAFT;
            $new->is_active = false;
            $new->version   = 1;
            $new->parent_fee_structure_id = null;
            $new->save();

            // Copy subject associations
            foreach ($original->subjects as $subject) {
                $new->subjects()->attach($subject->id, [
                    'is_required' => $subject->pivot->is_required,
                    'sort_order'  => $subject->pivot->sort_order,
                ]);
            }

            // Copy billing items
            foreach ($original->items as $item) {
                $new->items()->create($item->only([
                    'name', 'description', 'item_type', 'amount',
                    'quantity', 'billing_frequency', 'is_optional', 'is_refundable', 'sort_order',
                ]));
            }

            // Copy price tiers
            foreach ($original->priceTiers as $tier) {
                $new->priceTiers()->create($tier->only(['minimum_subjects', 'maximum_subjects', 'price', 'sort_order']));
            }

            \App\Models\FinancialAuditLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'package_duplicated',
                'target_type' => 'FeeStructure',
                'target_id'   => $new->id,
                'ip_address'  => $request->ip(),
                'details'     => json_encode(['original_id' => $original->id, 'original_name' => $original->name]),
            ]);

            return $new;
        });

        return back()->with('success', "Package duplicated as \"Copy of {$original->name}\" (Draft). Edit it before activating.");
    }

    /**
     * Create a new version of an existing fee structure.
     * Existing students remain on the old version.
     */
    public function createFeeStructureVersion(Request $request, $id)
    {
        $original = \App\Models\FeeStructure::with(['subjects', 'items', 'priceTiers'])->findOrFail($id);

        $validated = $request->validate([
            'amount'          => 'nullable|numeric|min:0',
            'effective_from'  => 'nullable|date',
            'effective_until' => 'nullable|date|after_or_equal:effective_from',
            'notes'           => 'nullable|string|max:500',
        ]);

        $newVersion = DB::transaction(function () use ($original, $validated, $request) {
            // Deactivate the original
            $original->update(['status' => \App\Models\FeeStructure::STATUS_INACTIVE]);

            // Create new version
            $new = $original->replicate(['deleted_at', 'version']);
            $new->version = $original->version + 1;
            $new->name    = $original->name . ' v' . $new->version;
            $new->parent_fee_structure_id = $original->parent_fee_structure_id ?? $original->id;
            $new->status    = \App\Models\FeeStructure::STATUS_DRAFT;
            $new->is_active = false;

            if (isset($validated['amount']))          $new->amount         = $validated['amount'];
            if (isset($validated['effective_from']))  $new->effective_from = $validated['effective_from'];
            if (isset($validated['effective_until'])) $new->effective_until = $validated['effective_until'];

            $new->save();

            // Copy subject associations
            foreach ($original->subjects as $subject) {
                $new->subjects()->attach($subject->id, [
                    'is_required' => $subject->pivot->is_required,
                    'sort_order'  => $subject->pivot->sort_order,
                ]);
            }

            // Copy billing items
            foreach ($original->items as $item) {
                $new->items()->create($item->only([
                    'name', 'description', 'item_type', 'amount',
                    'quantity', 'billing_frequency', 'is_optional', 'is_refundable', 'sort_order',
                ]));
            }

            // Copy price tiers
            foreach ($original->priceTiers as $tier) {
                $new->priceTiers()->create($tier->only(['minimum_subjects', 'maximum_subjects', 'price', 'sort_order']));
            }

            \App\Models\FinancialAuditLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'package_version_created',
                'target_type' => 'FeeStructure',
                'target_id'   => $new->id,
                'ip_address'  => $request->ip(),
                'details'     => json_encode([
                    'original_id'  => $original->id,
                    'new_version'  => $new->version,
                    'notes'        => $validated['notes'] ?? null,
                ]),
            ]);

            return $new;
        });

        return back()->with('success', "Version {$newVersion->version} of \"{$original->name}\" created as Draft. Activate it when ready.");
    }

    /**
     * API: Get subjects for a fee structure (for JS live preview in registration).
     */
    public function apiFeeStructureSubjects($id)
    {
        $fee = \App\Models\FeeStructure::with('subjects')->findOrFail($id);
        return response()->json([
            'package_type'     => $fee->package_type ?? 'individual',
            'minimum_subjects' => $fee->minimum_subjects,
            'maximum_subjects' => $fee->maximum_subjects,
            'subjects'         => $fee->subjects->map(fn ($s) => [
                'id'          => $s->id,
                'name'        => $s->name,
                'is_required' => (bool) $s->pivot->is_required,
                'monthly_fee' => (float) ($s->monthly_fee ?? 0),
            ]),
        ]);
    }

    /**
     * API: Return a pricing preview for a fee structure + subject selection.
     * Used by the registration form's live price preview.
     * NOTE: This is for UX only — server always recalculates on final submit.
     */
    public function apiFeeStructurePreview(Request $request, $id)
    {
        $fee = \App\Models\FeeStructure::with(['subjects', 'items', 'priceTiers'])->findOrFail($id);

        if (!$fee->is_available) {
            return response()->json(['error' => 'Package is not available.'], 422);
        }

        $subjectIds = array_map('intval', array_filter((array) $request->query('subject_ids', [])));

        try {
            $pricing = app(\App\Services\PackagePricingService::class)->calculate($fee, $subjectIds);
            return response()->json($pricing->toArray());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function storeInvoice(Request $request)
    {
        $validated = $request->validate([
            'student_id'       => 'required|exists:students,id',
            'fee_structure_id' => 'nullable|exists:fee_structures,id',
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'subtotal'         => 'required|numeric|min:0',
            'discount_amount'  => 'nullable|numeric|min:0',
            'due_date'         => 'required|date',
        ]);

        $discount = (float) ($validated['discount_amount'] ?? 0);
        $subtotal = (float) $validated['subtotal'];
        $total    = max(0, $subtotal - $discount);

        $invNumber = 'INV-' . date('Y') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));

        $invoice = \App\Models\Invoice::create([
            'student_id'       => $validated['student_id'],
            'fee_structure_id' => $validated['fee_structure_id'] ?? null,
            'invoice_number'   => $invNumber,
            'title'            => $validated['title'],
            'description'      => $validated['description'] ?? null,
            'subtotal'         => $subtotal,
            'discount_amount'  => $discount,
            'total_amount'     => $total,
            'due_date'         => $validated['due_date'],
            'status'           => 'outstanding',
        ]);

        $student = \App\Models\Student::find($validated['student_id']);
        
        // In-app notifications for both student and parents without mentioning admin
        \App\Services\BillingNotificationService::notifyInvoiceIssued($invoice);

        \App\Models\FinancialAuditLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'issue_invoice',
            'target_type' => 'Invoice',
            'target_id'   => $invoice->id,
            'ip_address'  => $request->ip(),
            'details'     => "Issued invoice #{$invNumber} of RM {$total} to Student #" . ($student?->id ?? 'unknown'),
        ]);

        return back()->with('success', "Invoice #{$invNumber} issued to student successfully!");
    }

    public function updateInvoiceStatus(Request $request, $id)
    {
        // NOTE: unreachable — not registered in routes/web.php. The live
        // equivalent is the '/invoices/{id}/status' closure in that file.
        // Fixed anyway while touching it: this validation list previously
        // included the phantom values 'outstanding'/'pending_verification'
        // (never valid Invoice statuses) while excluding the real
        // 'draft'/'issued'/'partially_paid' — same fix as the live closure.
        $invoice = \App\Models\Invoice::findOrFail($id);
        $oldStatus = $invoice->status;
        $status = $request->validate(['status' => 'required|in:draft,issued,partially_paid,paid,overdue,cancelled'])['status'];

        $invoice->update(['status' => $status]);

        \App\Services\BillingNotificationService::notifyInvoiceStatusUpdated($invoice, $oldStatus);

        return back()->with('success', "Invoice #{$invoice->invoice_number} status updated to {$status}.");
    }

    public function verifyPayment(Request $request, $id)
    {
        $payment = \App\Models\Payment::findOrFail($id);
        $action = $request->validate(['action' => 'required|in:approve,reject'])['action'];

        if ($action === 'approve') {
            $receiptNo = 'RCP-' . date('Y') . '-' . str_pad($payment->id * 10 + rand(1, 9), 5, '0', STR_PAD_LEFT);
            $payment->update([
                // 'paid' is not a valid Payment status (payments.status enum is
                // pending/successful/failed/cancelled — 'paid' is Invoice's
                // vocabulary). This was throwing a DB error under strict SQL
                // mode on every approval before this fix.
                'status'         => \App\Enums\PaymentStatus::Successful,
                'receipt_number' => $receiptNo,
                'verified_by'    => auth()->id(),
                'paid_at'        => $payment->paid_at ?? now(),
            ]);

            if ($payment->invoice_id) {
                $payment->invoice->update(['status' => InvoiceStatus::Paid]);
            }

            // In-app alert for payer, student, and parents without mentioning admin
            \App\Services\BillingNotificationService::notifyPaymentVerified($payment->fresh(['invoice.student.user']), true);

            \App\Models\FinancialAuditLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'approve_payment',
                'target_type' => 'Payment',
                'target_id'   => $payment->id,
                'ip_address'  => $request->ip(),
                'details'     => "Approved payment #{$payment->reference_no} of RM {$payment->amount}. Generated Receipt #{$receiptNo}",
            ]);

            return back()->with('success', "Payment #{$payment->reference_no} approved and marked as Paid!");
        } else {
            $reason = $request->input('reason', 'Payment details could not be verified');
            $payment->update([
                // 'rejected' is not a valid Payment status either — same class
                // of bug as the approve branch above.
                'status'      => \App\Enums\PaymentStatus::Failed,
                'verified_by' => auth()->id(),
            ]);

            if ($payment->invoice_id) {
                // 'outstanding' has never been a valid Invoice status (see the
                // two other dead 'outstanding' whereIn() sites fixed earlier in
                // this same Phase 3 pass) — the invoice reverts to awaiting
                // payment, i.e. 'issued'.
                $payment->invoice->update(['status' => InvoiceStatus::Issued]);
            }

            // In-app alert for payer, student, and parents without mentioning admin
            \App\Services\BillingNotificationService::notifyPaymentVerified($payment->fresh(['invoice.student.user']), false, $reason);

            \App\Models\FinancialAuditLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'reject_payment',
                'target_type' => 'Payment',
                'target_id'   => $payment->id,
                'ip_address'  => $request->ip(),
                'details'     => "Rejected bank transfer payment #{$payment->reference_no}",
            ]);

            return back()->with('success', "Payment rejected. Invoice returned to outstanding.");
        }
    }

    public function updateFinanceSettings(Request $request)
    {
        $validated = $request->validate([
            'company_bank_name'           => 'required|string|max:255',
            'company_bank_account_name'   => 'required|string|max:255',
            'company_bank_account_number' => 'required|string|max:255',
            'company_bank_instructions'   => 'nullable|string',
            'gateway_provider'            => 'required|string|max:100',
            'gateway_merchant_id'         => 'nullable|string|max:255',
            'gateway_sandbox_mode'        => 'nullable|boolean',
        ]);

        \App\Models\Setting::set('company_bank_name', $validated['company_bank_name'], 'finance', 'Company Bank Name');
        \App\Models\Setting::set('company_bank_account_name', $validated['company_bank_account_name'], 'finance', 'Company Account Name');
        \App\Models\Setting::set('company_bank_account_number', $validated['company_bank_account_number'], 'finance', 'Company Account Number');
        \App\Models\Setting::set('company_bank_instructions', $validated['company_bank_instructions'] ?? '', 'finance', 'Transfer Instructions');
        \App\Models\Setting::set('gateway_provider', $validated['gateway_provider'], 'finance', 'Gateway Provider');
        if (!empty($validated['gateway_merchant_id'])) {
            \App\Models\Setting::set('gateway_merchant_id', $validated['gateway_merchant_id'], 'finance', 'Gateway Merchant ID');
        }
        \App\Models\Setting::set('gateway_sandbox_mode', $request->has('gateway_sandbox_mode') ? '1' : '0', 'finance', 'Gateway Sandbox Mode');

        \App\Models\FinancialAuditLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'update_settings',
            'target_type' => 'FinanceSettings',
            'ip_address'  => $request->ip(),
            'details'     => "Updated company banking & payment gateway settings.",
        ]);

        return back()->with('success', 'Finance and payment settings updated successfully.');
    }

    public function revealGatewayCredentials(Request $request)
    {
        \App\Models\FinancialAuditLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'view_credentials',
            'target_type' => 'GatewayCredentials',
            'ip_address'  => $request->ip(),
            'details'     => "Admin revealed sensitive payment gateway credentials.",
        ]);

        return response()->json([
            'success'     => true,
            'merchant_id' => \App\Models\Setting::get('gateway_merchant_id', 'OSC_MERCHANT_8892'),
            'api_key'     => 'sk_live_' . md5('osc_live_secret_key_prod'),
            'provider'    => \App\Models\Setting::get('gateway_provider', 'mock_fpx'),
        ]);
    }

    public function clearSystemCache()
    {
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Setting::clearCache();

        return back()->with('success', 'Application cache, compiled views, route cache, and settings cache cleared successfully.');
    }

    // ── INVOICE CRUD (Admin) ──────────────────────────────────────────────────

    public function updateInvoice(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        $oldDueDate = $invoice->due_date ? $invoice->due_date->toDateString() : null;

        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'billing_period' => 'nullable|string|max:100',
            'due_date'       => 'required|date',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $dueDateChanged = $oldDueDate !== Carbon::parse($validated['due_date'])->toDateString();
        
        if (isset($validated['notes']) && !\Illuminate\Support\Facades\Schema::hasColumn('invoices', 'notes')) {
            $validated['description'] = $validated['notes'];
            unset($validated['notes']);
        }

        $invoice->update($validated);

        if ($dueDateChanged) {
            $this->syncInvoiceDueDateChange($invoice, 'Updated via invoice editor', $oldDueDate);
        } else {
            // Alert student & parents of invoice modifications
            \App\Services\BillingNotificationService::notifyInvoiceUpdated($invoice);
        }

        return redirect()->to(route('admin.dashboard') . '#financials')
            ->with('success', "Invoice {$invoice->invoice_number} updated successfully.");
    }

    public function updateInvoiceDueDate(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        $oldDueDate = $invoice->due_date ? $invoice->due_date->toDateString() : null;

        $validated = $request->validate([
            'due_date' => 'required|date',
            'reason'   => 'nullable|string|max:255',
        ]);

        $invoice->due_date = $validated['due_date'];
        $invoice->save();

        $this->syncInvoiceDueDateChange($invoice, $validated['reason'] ?? null, $oldDueDate);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Due date for invoice {$invoice->invoice_number} updated to " . $invoice->due_date->format('d M Y') . ".",
                'invoice' => $invoice->fresh(),
            ]);
        }

        return redirect()->to(route('admin.dashboard') . '#financials')
            ->with('success', "Due date for invoice {$invoice->invoice_number} updated to " . $invoice->due_date->format('d M Y') . ".");
    }

    public function updateDefaultDueDays(Request $request)
    {
        $validated = $request->validate([
            'invoice_due_days'             => 'required|integer|min:1|max:180',
            'bundle_elective_discount_pct' => 'nullable|numeric|min:0|max:100',
        ]);

        Setting::set('billing.invoice_due_days', (string)$validated['invoice_due_days'], 'billing', 'Invoice Due Days (after issue date)');
        if (isset($validated['bundle_elective_discount_pct'])) {
            Setting::set('billing.bundle_elective_discount_pct', (string)$validated['bundle_elective_discount_pct'], 'billing', 'Bundle Elective Add-on Discount (%)');
        }
        Setting::clearCache();

        FinancialAuditLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'update_default_due_days',
            'target_type' => 'Setting',
            'ip_address'  => $request->ip(),
            'details'     => "Updated default invoice payment due timeline to {$validated['invoice_due_days']} days (bundle elective discount: " . ($validated['bundle_elective_discount_pct'] ?? '0') . "%).",
        ]);

        return redirect()->to(route('admin.dashboard') . '#financials')
            ->with('success', "Billing policies updated successfully.");
    }

    /**
     * Reconcile status, reminders, and student access restriction when an invoice due date changes.
     */
    protected function syncInvoiceDueDateChange(Invoice $invoice, ?string $reason = null, ?string $oldDueDate = null)
    {
        $newDueDate = Carbon::parse($invoice->due_date)->startOfDay();
        $today = now()->startOfDay();

        // 1. Reconcile status if invoice is active (not fully paid and not cancelled)
        if (!in_array($invoice->status, [InvoiceStatus::Paid, InvoiceStatus::Cancelled])) {
            if ($newDueDate->lt($today)) {
                $invoice->status = InvoiceStatus::Overdue;
            } else {
                // If it was overdue, lift overdue back to partially_paid or issued
                if ((float)$invoice->amount_paid > 0) {
                    $invoice->status = InvoiceStatus::PartiallyPaid;
                } else {
                    $invoice->status = InvoiceStatus::Issued;
                }
            }
            $invoice->save();
        }

        // 2. Sync student access restriction immediately
        if ($invoice->student) {
            app(\App\Services\StudentAccessRestrictionService::class)->syncStudentStatus($invoice->student);
        }

        // 3. Reschedule pending email reminders to match new due date
        InvoiceReminder::where('invoice_id', $invoice->id)->where('status', 'pending')->delete();
        if ($invoice->student && in_array($invoice->status, InvoiceStatus::unpaidStatuses())) {
            app(\App\Services\InvoiceService::class)->scheduleReminders($invoice, $invoice->student);
        }

        // 4. Record Financial Audit Log
        $newDueDateStr = $invoice->due_date ? $invoice->due_date->format('d M Y') : $newDueDate->toDateString();
        $oldDueDateStr = $oldDueDate ? Carbon::parse($oldDueDate)->format('d M Y') : 'N/A';

        FinancialAuditLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'update_invoice_due_date',
            'target_type' => 'Invoice',
            'target_id'   => $invoice->id,
            'ip_address'  => request()->ip(),
            'details'     => "Updated due date for invoice #{$invoice->invoice_number} from {$oldDueDateStr} to {$newDueDateStr}." .
                             ($reason ? " Reason: {$reason}" : ""),
        ]);

        // 5. Notify Users (Student & Parents) without mentioning admin
        \App\Services\BillingNotificationService::notifyInvoiceDueDateUpdated($invoice, $reason, $oldDueDateStr);
    }

    public function destroyInvoice($id)
    {
        $invoice = \App\Models\Invoice::findOrFail($id);
        $number  = $invoice->invoice_number;

        // 1. Collect linked payment IDs BEFORE deleting pivot rows
        $linkedPaymentIds = \App\Models\PaymentInvoice::where('invoice_id', $id)->pluck('payment_id');

        // 2. Remove related items, reminders, and payment_invoices pivot rows
        $invoice->items()->delete();
        $invoice->reminders()->delete();
        \App\Models\PaymentInvoice::where('invoice_id', $id)->delete();

        // 3. Sync orphaned / affected payment records so Ledger stays accurate
        foreach ($linkedPaymentIds as $paymentId) {
            $payment = \App\Models\Payment::find($paymentId);
            if (!$payment) continue;
            $remaining = \App\Models\PaymentInvoice::where('payment_id', $paymentId)->count();
            $adminNote = "Invoice {$number} deleted by admin.";
            if ($remaining === 0) {
                if ($payment->status === \App\Enums\PaymentStatus::Pending) {
                    $payment->update(['status' => \App\Enums\PaymentStatus::Failed, 'payment_proof_notes' => $adminNote]);
                } else {
                    $payment->update(['amount' => 0, 'payment_proof_notes' => $adminNote]);
                }
            } else {
                $newAmt = \App\Models\PaymentInvoice::where('payment_id', $paymentId)->sum('amount_applied');
                $payment->update(['amount' => $newAmt]);
            }
        }

        // 4. Delete the invoice
        $invoice->delete();

        return redirect()->to(route('admin.dashboard') . '#financials')
            ->with('success', "Invoice {$number} deleted. Linked pending payments have been reset.");
    }

    // ── TEACHER SUBJECT & CLASS MANAGEMENT ────────────────────────────────────

    public function updateTeacherSubjects(UpdateTeacherSubjectsRequest $request, $id, TeacherApplicationService $teacherApplications)
    {
        $teacher = Teacher::findOrFail($id);
        $teacherApplications->updateSubjects($teacher, $request->input('subject_ids', []));
        $name = $teacher->user->name ?? 'Teacher #' . $id;

        return redirect()->to(route('admin.dashboard') . '#teachers')
            ->with('success', "Subject assignments updated for {$name}.");
    }

    public function storeTeacherClass(StoreTeacherClassRequest $request, $id, TeacherApplicationService $teacherApplications)
    {
        $teacher = Teacher::findOrFail($id);
        $course = $teacherApplications->createClass($teacher, $request->validated());
        $name = $teacher->user->name ?? 'Teacher #' . $id;

        return redirect()->to(route('admin.dashboard') . '#teachers')
            ->with('success', "Class '{$course->title}' created and assigned to {$name}.");
    }

    public function toggleStudentStatus($id, UserManagementService $users)
    {
        [$name, $label] = $users->toggleStudentStatus($id);

        return redirect()->to(route('admin.dashboard') . '#student')
            ->with('success', "Student '{$name}' status updated to {$label}.");
    }

    public function toggleTeacherStatus($id, UserManagementService $users)
    {
        [$name, $label] = $users->toggleTeacherStatus($id);

        return redirect()->to(route('admin.dashboard') . '#teachers')
            ->with('success', "Teacher '{$name}' status updated to {$label} (Company record preserved).");
    }

    // ── OUTREACH & ENGAGEMENT HUB ─────────────────────────────────────────────

    /**
     * Toggle a user's is_active flag (admin account deactivation / reactivation).
     * Also syncs associated student.status or teacher.application_status.
     */
    public function toggleUserIsActive($id, UserManagementService $users)
    {
        $result = $users->toggleUserIsActive($id);

        if ($result === null) {
            return redirect()->back()->with('error', 'You cannot deactivate your own account.');
        }

        [$name, $label] = $result;

        return redirect()->to(route('admin.dashboard') . '#records')
            ->with('success', "User '{$name}' has been {$label}.");
    }

    /**
     * Send a personalised engagement blast email to selected non-active users.
     */
    public function blastEngagementEmail(BlastEngagementEmailRequest $request, UserManagementService $users)
    {
        [$sent, $failed] = $users->sendEngagementBlast($request->validated());

        $msg = "Engagement email sent to {$sent} recipient(s).";
        if ($failed > 0) $msg .= " ({$failed} failed — check mail configuration.)";

        return redirect()->to(route('admin.dashboard') . '#records')->with('success', $msg);
    }

    /**
     * Export non-active users as a CSV file for external outreach/CRM tools.
     */
    public function exportInactiveUsersCsv(UserManagementService $users): StreamedResponse
    {
        return $users->exportInactiveUsersCsv();
    }
}

