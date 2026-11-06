<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\LiveClass;
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
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Assignment;
use App\Services\Admin\AssignmentQuizService;
use App\Services\Admin\ClassSchedulingService;
use App\Services\Admin\CurriculumService;
use App\Services\Admin\DashboardMetricsService;
use App\Services\Admin\GigService;
use App\Services\Admin\TeacherApplicationService;
use App\Services\Admin\UserManagementService;
use App\Services\PdfDiagnosticParser;
use App\Http\Requests\Admin\BlastEngagementEmailRequest;
use App\Http\Requests\Admin\ResetUserPasswordRequest;
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

    public function showUser($id, UserManagementService $users)
    {
        $user = $users->findUserForJson((int) $id);

        if (request()->wantsJson() || request()->ajax()) {
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }
            return response()->json($user);
        }

        return redirect()->to(route('admin.dashboard') . '#users');
    }

    public function updateUser(UpdateUserRequest $request, $id, UserManagementService $users)
    {
        // Fallback if a client/proxy sent _method=DELETE via POST/PUT to this endpoint
        if ($request->input('_method') === 'DELETE' || $request->input('action') === 'delete') {
            return $this->destroyUser($id, $users);
        }

        $user = User::findOrFail($id);
        $user = $users->updateUser($user, $request->validated());

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "User '{$user->name}' updated successfully.",
                'user'    => $user,
            ]);
        }

        return redirect()->to(route('admin.dashboard') . '#users')
            ->with('success', "User '{$user->name}' updated successfully.");
    }

    public function destroyUser($id, UserManagementService $users)
    {
        if (auth()->id() == $id) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['error' => 'Cannot delete your own active administrator account.'], 403);
            }
            return redirect()->to(route('admin.dashboard') . '#users')
                ->with('error', 'Cannot delete your own active administrator account.');
        }

        $userName = $users->deleteUser($id);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "User '{$userName}' deleted successfully.",
            ]);
        }

        return redirect()->to(route('admin.dashboard') . '#users')
            ->with('success', "User '{$userName}' deleted successfully.");
    }

    public function resetUserPassword(ResetUserPasswordRequest $request, $id, UserManagementService $users)
    {
        $user = User::findOrFail($id);
        $users->resetPassword($user, $request->validated()['password'] ?? null);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Password for '{$user->name}' has been reset. They will be asked to set a new password on next login.",
            ]);
        }

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

    public function showSubject($id, CurriculumService $curriculum)
    {
        $subject = $curriculum->findSubjectForJson((int) $id);

        if (request()->wantsJson() || request()->ajax()) {
            if (!$subject) {
                return response()->json(['error' => 'Subject not found'], 404);
            }
            return response()->json($subject);
        }

        return redirect()->to(route('admin.dashboard') . '#courses-subjects');
    }

    // ── SUBJECT DIAGNOSTIC TEST MANAGEMENT ────────────────────────────────────

    public function setSubjectDiagnosticTest(Request $request, $id, CurriculumService $curriculum)
    {
        $result = $curriculum->setSubjectDiagnosticTest($request, (int) $id);

        return redirect()->to(route('admin.dashboard') . '#courses-subjects')
            ->with('success', "Diagnostic test \"{$result['assignment']->title}\" attached to {$result['subject']->display_name}.");
    }

    public function createSubjectDiagnosticTest(Request $request, $id, CurriculumService $curriculum)
    {
        $result = $curriculum->createSubjectDiagnosticTest($request, (int) $id);
        $message = "Diagnostic test \"{$result['assignment']->title}\" saved and attached to {$result['subject']->display_name}.";

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'       => true,
                'message'       => $message,
                'assignment_id' => $result['assignment']->id,
            ]);
        }

        return redirect()->to(route('admin.dashboard') . '#courses-subjects')->with('success', $message);
    }

    public function detachSubjectDiagnosticTest(Request $request, $id, CurriculumService $curriculum)
    {
        $result = $curriculum->detachSubjectDiagnosticTest((int) $id);

        return redirect()->to(route('admin.dashboard') . '#courses-subjects')
            ->with('success', "Diagnostic test \"{$result['testTitle']}\" detached from {$result['subject']->display_name}.");
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
        $recordedClass = $scheduling->createRecordedClass($request->validated(), $request);

        return redirect()->to(route('admin.dashboard') . '#courses-recorded')
            ->with('success', "Recorded Class '{$recordedClass->title}' added to vault.");
    }

    public function updateRecordedClass(UpdateRecordedClassRequest $request, $id, ClassSchedulingService $scheduling)
    {
        $recordedClass = RecordedClass::findOrFail($id);
        $recordedClass = $scheduling->updateRecordedClass($recordedClass, $request->validated(), $request);

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

    public function storeTask(Request $request, AssignmentQuizService $assignmentQuizzes)
    {
        $assignment = $assignmentQuizzes->createTask($request);
        $redirectTab = $request->input('redirect_tab', '#courses-tasks');

        if ($request->filled('student_id')) {
            return redirect()->to(route('admin.dashboard') . $redirectTab)
                ->with('success', 'Personal task/notes document "' . $assignment->title . '" uploaded and assigned to student successfully.');
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'       => true,
                'message'       => 'Task / Diagnostic Test "' . $assignment->title . '" created and assigned to all eligible students successfully.',
                'assignment_id' => $assignment->id,
            ]);
        }

        return redirect()->to(route('admin.dashboard') . $redirectTab)
            ->with('success', 'Task / Diagnostic Test "' . $assignment->title . '" created and assigned to all eligible students successfully.');
    }

    public function updateTask(Request $request, $id, AssignmentQuizService $assignmentQuizzes)
    {
        $assignment = Assignment::findOrFail($id);
        $assignment = $assignmentQuizzes->updateTask($assignment, $request);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'       => true,
                'message'       => 'Task "' . $assignment->title . '" updated successfully.',
                'assignment_id' => $assignment->id,
            ]);
        }

        return redirect()->to(route('admin.dashboard') . '#courses-tasks')
            ->with('success', 'Task "' . $assignment->title . '" updated successfully.');
    }

    public function destroyTask($id, AssignmentQuizService $assignmentQuizzes)
    {
        $title = $assignmentQuizzes->deleteTask($id);

        return redirect()->to(route('admin.dashboard') . '#courses-tasks')
            ->with('success', 'Task "' . $title . '" deleted successfully.');
    }

    /**
     * Auto-detect and parse questions, prompts, options, and page mapping from an uploaded PDF.
     */
    public function parsePdfQuestions(Request $request, PdfDiagnosticParser $pdfParser, AssignmentQuizService $assignmentQuizzes)
    {
        $result = $assignmentQuizzes->parsePdfQuestions($request, $pdfParser);
        $status = $result['status'] ?? 200;
        unset($result['status']);

        return response()->json($result, $status);
    }

    /**
     * AJAX endpoint for uploading or saving a cropped question image.
     */
    public function uploadQuestionImage(Request $request, AssignmentQuizService $assignmentQuizzes)
    {
        $result = $assignmentQuizzes->uploadQuestionImage($request);
        $status = $result['status'] ?? 200;
        unset($result['status']);

        return response()->json($result, $status);
    }

    public function gradeTask(Request $request, $assignmentId, $studentId, AssignmentQuizService $assignmentQuizzes)
    {
        $sub = $assignmentQuizzes->gradeTask($request, $assignmentId, $studentId);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'          => true,
                'message'          => 'Student submission graded successfully.',
                'graded_file_name' => $sub->graded_file_name,
                'graded_file_url'  => $sub->graded_file_url,
            ]);
        }

        return redirect()->to(route('admin.dashboard') . '#courses-tasks')
            ->with('success', 'Student submission graded successfully.');
    }

    public function getTaskSubmissions($id, AssignmentQuizService $assignmentQuizzes)
    {
        return response()->json($assignmentQuizzes->getTaskSubmissions($id));
    }

    // ── QUIZ & QUESTION CRUD ──────────────────────────────────────────────────

    public function storeQuiz(Request $request, AssignmentQuizService $assignmentQuizzes)
    {
        $quiz = $assignmentQuizzes->createQuiz($request);

        return redirect()->to(route('admin.dashboard') . '#courses-quizzes')
            ->with('success', "Quiz '{$quiz->title}' created successfully.");
    }

    public function updateQuiz(Request $request, $id, AssignmentQuizService $assignmentQuizzes)
    {
        $quiz = Quiz::findOrFail($id);
        $quiz = $assignmentQuizzes->updateQuiz($quiz, $request);

        return redirect()->to(route('admin.dashboard') . '#courses-quizzes')
            ->with('success', "Quiz '{$quiz->title}' updated successfully.");
    }

    public function destroyQuiz($id, AssignmentQuizService $assignmentQuizzes)
    {
        $title = $assignmentQuizzes->deleteQuiz($id);

        return redirect()->to(route('admin.dashboard') . '#courses-quizzes')
            ->with('success', "Quiz '{$title}' deleted.");
    }

    public function storeQuestion(Request $request, $quizId, AssignmentQuizService $assignmentQuizzes)
    {
        $question = $assignmentQuizzes->createQuestion($quizId, $request);

        return redirect()->to(route('admin.dashboard', ['manage_quiz' => $question->quiz_id]) . '#courses')
            ->with('success', "Question added to quiz '{$question->quiz->title}'.");
    }

    public function updateQuestion(Request $request, $id, AssignmentQuizService $assignmentQuizzes)
    {
        $question = Question::findOrFail($id);
        $question = $assignmentQuizzes->updateQuestion($question, $request);

        return redirect()->to(route('admin.dashboard', ['manage_quiz' => $question->quiz_id]) . '#courses')
            ->with('success', "Question updated successfully.");
    }

    public function destroyQuestion($id, AssignmentQuizService $assignmentQuizzes)
    {
        $quizId = $assignmentQuizzes->deleteQuestion($id);

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

