<?php

namespace App\Http\Controllers;

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
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Assignment;
use App\Models\FeeStructure;
use App\Services\Admin\AssignmentQuizService;
use App\Services\Admin\BillingAdminService;
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

    public function storeTask(Request $request, AssignmentQuizService $assignmentQuizzes)
    {
        $assignment = $assignmentQuizzes->createTask($request);
        $redirectTab = $request->input('redirect_tab', '#courses-tasks');

        if ($request->filled('student_id')) {
            return redirect()->to(route('admin.dashboard') . $redirectTab)
                ->with('success', 'Personal task/notes document "' . $assignment->title . '" uploaded and assigned to student successfully.');
        }

        return redirect()->to(route('admin.dashboard') . $redirectTab)
            ->with('success', 'Task / Diagnostic Test "' . $assignment->title . '" created and assigned to all eligible students successfully.');
    }

    public function updateTask(Request $request, $id, AssignmentQuizService $assignmentQuizzes)
    {
        $assignment = Assignment::findOrFail($id);
        $assignment = $assignmentQuizzes->updateTask($assignment, $request);

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

    // ── CSV EXPORTS ───────────────────────────────────────────────────────────

    public function exportPaymentsCsv(BillingAdminService $billing): StreamedResponse
    {
        return $billing->exportPaymentsCsv();
    }

    public function exportEnrollmentsCsv(BillingAdminService $billing): StreamedResponse
    {
        return $billing->exportEnrollmentsCsv();
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

    public function storeFeeStructure(Request $request, BillingAdminService $billing)
    {
        $fee = $billing->createFeeStructure($request);

        return back()->with('success', "Fee structure '{$fee->name}' created successfully!");
    }

    public function updateFeeStructure(Request $request, $id, BillingAdminService $billing)
    {
        $fee = FeeStructure::findOrFail($id);
        $fee = $billing->updateFeeStructure($fee, $request);

        return back()->with('success', "Fee structure '{$fee->name}' updated successfully.");
    }

    public function destroyFeeStructure($id, BillingAdminService $billing)
    {
        $name = $billing->deleteFeeStructure($id);

        return back()->with('success', "Fee structure '{$name}' removed.");
    }

    // ── PACKAGE ENGINE METHODS ────────────────────────────────────────────────

    /**
     * Replace all subject associations for a fee structure package.
     */
    public function updateFeeStructureSubjects(Request $request, $id, BillingAdminService $billing)
    {
        $fee = FeeStructure::findOrFail($id);
        $billing->syncFeeStructureSubjects($fee, $request);

        return response()->json(['success' => true, 'message' => 'Subjects updated for "' . $fee->name . '".']);
    }

    /**
     * Replace all billing components (items) for a fee structure.
     */
    public function updateFeeStructureItems(Request $request, $id, BillingAdminService $billing)
    {
        $fee = FeeStructure::findOrFail($id);
        $billing->syncFeeStructureItems($fee, $request);

        return response()->json(['success' => true, 'message' => 'Billing components updated.']);
    }

    /**
     * Replace all pricing tiers for a tiered fee structure.
     */
    public function updateFeeStructureTiers(Request $request, $id, BillingAdminService $billing)
    {
        $fee = FeeStructure::findOrFail($id);
        $billing->syncFeeStructureTiers($fee, $request);

        return response()->json(['success' => true, 'message' => 'Pricing tiers updated.']);
    }

    /**
     * Duplicate an existing fee structure (copy config, subjects, items, tiers).
     * Does NOT copy enrollments, invoices, or payments.
     */
    public function duplicateFeeStructure(Request $request, $id, BillingAdminService $billing)
    {
        $original = FeeStructure::findOrFail($id);
        $new = $billing->duplicateFeeStructure($id, $request);

        return back()->with('success', "Package duplicated as \"Copy of {$original->name}\" (Draft). Edit it before activating.");
    }

    /**
     * Create a new version of an existing fee structure.
     * Existing students remain on the old version.
     */
    public function createFeeStructureVersion(Request $request, $id, BillingAdminService $billing)
    {
        $original = FeeStructure::findOrFail($id);
        $newVersion = $billing->createFeeStructureVersion($id, $request);

        return back()->with('success', "Version {$newVersion->version} of \"{$original->name}\" created as Draft. Activate it when ready.");
    }

    /**
     * API: Get subjects for a fee structure (for JS live preview in registration).
     */
    public function apiFeeStructureSubjects($id, BillingAdminService $billing)
    {
        return response()->json($billing->feeStructureSubjectsForApi($id));
    }

    /**
     * API: Return a pricing preview for a fee structure + subject selection.
     * Used by the registration form's live price preview.
     * NOTE: This is for UX only — server always recalculates on final submit.
     */
    public function apiFeeStructurePreview(Request $request, $id, BillingAdminService $billing)
    {
        $result = $billing->previewFeeStructurePricing($id, $request);

        return response()->json($result['pricing'] ?? ['error' => $result['error']], $result['status']);
    }

    public function updateFinanceSettings(Request $request, BillingAdminService $billing)
    {
        $billing->updateFinanceSettings($request);

        return back()->with('success', 'Finance and payment settings updated successfully.');
    }

    public function revealGatewayCredentials(Request $request, BillingAdminService $billing)
    {
        return response()->json($billing->revealGatewayCredentials($request));
    }

    public function testBillplzConnection(Request $request, BillingAdminService $billing)
    {
        return response()->json($billing->testBillplzConnection($request));
    }

    /**
     * Admin Attendance Hub: redirect to the unified dashboard Attendance tab.
     */
    public function attendance(Request $request)
    {
        return redirect('/admin/dashboard#attendance');
    }

    public function updateAttendanceSettings(Request $request, \App\Services\Admin\AttendanceService $attendance)
    {
        $attendance->updateAttendanceSettings($request);

        return back()->with('success', 'Attendance policies updated successfully.');
    }

    public function exportAttendanceCsv(Request $request, \App\Services\Admin\AttendanceService $attendance)
    {
        return $attendance->exportAttendanceCsv($request);
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

    public function updateInvoice(Request $request, $id, BillingAdminService $billing)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice = $billing->updateInvoice($invoice, $request);

        return redirect()->to(route('admin.dashboard') . '#financials')
            ->with('success', "Invoice {$invoice->invoice_number} updated successfully.");
    }

    public function updateInvoiceDueDate(Request $request, $id, BillingAdminService $billing)
    {
        $invoice = Invoice::findOrFail($id);
        $result = $billing->updateInvoiceDueDate($invoice, $request);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'invoice' => $result['invoice'],
            ]);
        }

        return redirect()->to(route('admin.dashboard') . '#financials')
            ->with('success', $result['message']);
    }

    public function updateDefaultDueDays(Request $request, BillingAdminService $billing)
    {
        $billing->updateDefaultDueDays($request);

        return redirect()->to(route('admin.dashboard') . '#financials')
            ->with('success', "Billing policies updated successfully.");
    }

    public function destroyInvoice($id, BillingAdminService $billing)
    {
        $number = $billing->deleteInvoice($id);

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

