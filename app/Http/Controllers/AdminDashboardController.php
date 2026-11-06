<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Student;
use App\Models\StudentTopicMastery;
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
use App\Services\Admin\DashboardMetricsService;
use App\Services\Admin\GigService;
use App\Services\Admin\TeacherApplicationService;
use App\Services\Admin\UserManagementService;
use App\Services\PdfDiagnosticParser;
use App\Http\Requests\Admin\BlastEngagementEmailRequest;
use App\Http\Requests\Admin\ResetUserPasswordRequest;
use App\Http\Requests\Admin\StoreGigRequest;
use App\Http\Requests\Admin\StoreTeacherClassRequest;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateTeacherSubjectsRequest;
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

