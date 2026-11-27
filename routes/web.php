<?php

use App\Http\Controllers\Admin\AdminContentController;
use App\Http\Controllers\Admin\BillingController;
use App\Http\Controllers\Admin\BillingToolsController;
use App\Http\Controllers\Admin\ClassSchedulingController as AdminClassSchedulingController;
use App\Http\Controllers\Admin\CourseRosterController as AdminCourseRosterController;
use App\Http\Controllers\Admin\CurriculumController as AdminCurriculumController;
use App\Http\Controllers\Admin\EmailManagementController;
use App\Http\Controllers\Admin\FinanceController as AdminFinanceController;
use App\Http\Controllers\Admin\LevelCleanupController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Auth\ParentRegistrationController;
use App\Http\Controllers\Auth\StudentRegistrationController;
use App\Http\Controllers\Auth\TeacherApplicationController;
use App\Http\Controllers\Billing\BillplzController;
use App\Http\Controllers\Billing\InvoiceController;
use App\Http\Controllers\Billing\PaymentController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DiagnosticTestController;
use App\Http\Controllers\InteractiveAssignmentController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ParentDashboardController;
use App\Http\Controllers\ParentLinkRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecordedClassController;
use App\Http\Controllers\RenewalController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\TeacherDashboardController;
use App\Http\Controllers\TimetableController;
use Illuminate\Support\Facades\Route;

// ──────────────────────────────────────────────────
// PUBLIC MARKETING PAGES
// ──────────────────────────────────────────────────
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/programmes', [PageController::class, 'programmes'])->name('programmes');
Route::get('/subjects', [PageController::class, 'subjects'])->name('subjects');
Route::get('/subjects/{subject}', [PageController::class, 'subjectShow'])->name('subject.show');
Route::get('/teachers', [PageController::class, 'teachers'])->name('teachers');
Route::get('/teachers/{teacher}', [PageController::class, 'teacherShow'])->name('teacher.show');
Route::get('/pricing', [PageController::class, 'pricing'])->name('pricing');
Route::get('/success-stories', [PageController::class, 'successStories'])->name('success-stories');
Route::get('/blog', [PageController::class, 'blog'])->name('blog');
Route::get('/blog/{slug}', [PageController::class, 'blogShow'])->name('blog.show');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');
// Free-trial page retired upstream (301 to /register) — it was the third,
// independent "claim a student" implementation Phase 0 flagged as a latent
// hijack-chain risk; confirmed unreachable now that nothing routes to it.
Route::redirect('/free-trial', '/register', 301)->name('free-trial');
Route::post('/free-trial', fn () => redirect()->route('register', [], 301))->name('free-trial.submit');
// Throttle preserved from Phase 0's containment work — upstream dropped it
// when it stopped being the free-trial page's own endpoint, but the route
// itself is still a real unauthenticated lookup and stays rate-limited.
Route::post('/api/check-registration-availability', [PageController::class, 'checkRegistrationAvailability'])->name('registration.check-availability')->middleware('throttle:15,1');
Route::get('/learning-platform', [PageController::class, 'learningPlatform'])->name('learning-platform');

// Guardian link approval — clicked from an emailed signed URL, no auth required
// (the signature itself is the authorization). See app/Services/ParentLinkService.php.
Route::get('/parent-link-requests/{pivot}/approve', [ParentLinkRequestController::class, 'approve'])
    ->name('parent-link-requests.approve')
    ->middleware('signed');
Route::get('/parent-link-requests/{pivot}/deny', [ParentLinkRequestController::class, 'deny'])
    ->name('parent-link-requests.deny')
    ->middleware('signed');

// ──────────────────────────────────────────────────
// ROLE-SPECIFIC REGISTRATION FLOWS
// ──────────────────────────────────────────────────

// Student Registration
Route::get('/register/student', [StudentRegistrationController::class, 'create'])->name('register.student')->middleware('guest');
Route::post('/register/student', [StudentRegistrationController::class, 'store'])->name('register.student.submit')->middleware(['guest', 'throttle:6,1']);
Route::get('/register/student/success', [StudentRegistrationController::class, 'success'])->name('register.student.success');

// Parent Registration + IC-based AJAX verification
Route::get('/register/parent', [ParentRegistrationController::class, 'create'])->name('register.parent')->middleware('guest');
Route::post('/register/parent', [ParentRegistrationController::class, 'store'])->name('register.parent.submit')->middleware(['guest', 'throttle:6,1']);
Route::post('/api/verify-student-ic', [ParentRegistrationController::class, 'verifyStudentIc'])->name('register.verify.ic')->middleware('throttle:10,1');
Route::get('/register/parent/success', [ParentRegistrationController::class, 'success'])->name('register.parent.success');

// Teacher Application (Apply to Teach — no password, creates application record only)
Route::get('/register/teacher', [TeacherApplicationController::class, 'create'])->name('register.teacher')->middleware('guest');
Route::post('/register/teacher', [TeacherApplicationController::class, 'store'])->name('register.teacher.submit')->middleware(['guest', 'throttle:6,1']);
Route::get('/register/teacher/success', [TeacherApplicationController::class, 'success'])->name('register.teacher.success');

// ──────────────────────────────────────────────────
// BREEZE AUTH ROUTES
// ──────────────────────────────────────────────────
require __DIR__.'/auth.php';

// ──────────────────────────────────────────────────
// STUDENT DASHBOARD (7 MAIN MENU STRUCTURE)
// ──────────────────────────────────────────────────
Route::prefix('student')->name('student.')->middleware(['auth', 'role:student'])->group(function () {
    Route::get('/dashboard', [StudentDashboardController::class, 'dashboard'])->name('dashboard');

    // Protected Academic Routes (blocked if student has overdue payment)
    Route::middleware('student.not_restricted')->group(function () {
        Route::get('/learning', [StudentDashboardController::class, 'learning'])->name('learning');
        Route::get('/courses', [StudentDashboardController::class, 'learning'])->name('courses'); // Alias for My Learning
        Route::get('/classes', [StudentDashboardController::class, 'classes'])->name('classes');
        Route::match(['get', 'post'], '/classes/{liveClass}/join', [StudentDashboardController::class, 'joinClass'])->name('classes.join');
        Route::post('/attendance/correction-request', [StudentDashboardController::class, 'submitCorrectionRequest'])->name('attendance.correction.submit');
        Route::get('/timetable', [TimetableController::class, 'studentIndex'])->name('timetable');
        Route::get('/tasks', [StudentDashboardController::class, 'tasks'])->name('tasks');
        Route::post('/tasks/{id}/submit', [StudentDashboardController::class, 'submitTask'])->name('tasks.submit');
        Route::get('/assignments/{assignment}/interactive', [InteractiveAssignmentController::class, 'show'])->name('assignments.interactive');
        Route::get('/assignments/{assignment}/pdf-stream', [InteractiveAssignmentController::class, 'streamSourcePdf'])->name('assignments.pdf-stream');
        Route::get('/submissions/{submission}/init', [InteractiveAssignmentController::class, 'loadState'])->name('submissions.init');
        Route::put('/submissions/{submission}/pages/{pageNumber}', [InteractiveAssignmentController::class, 'savePage'])->middleware('throttle:60,1')->name('submissions.pages.save');
        Route::post('/submissions/{submission}/submit', [InteractiveAssignmentController::class, 'submit'])->name('submissions.submit');
        Route::get('/submissions/{submission}/download-final', [InteractiveAssignmentController::class, 'downloadFinalPdf'])->name('submissions.download-final');
        Route::get('/diagnostic/{id}', [DiagnosticTestController::class, 'take'])->name('diagnostic.take');
        Route::get('/diagnostic/{id}/pdf', [DiagnosticTestController::class, 'streamPdf'])->name('diagnostic.pdf');
        Route::post('/diagnostic/{id}/save-draft', [DiagnosticTestController::class, 'saveDraft'])->name('diagnostic.save-draft');
        Route::post('/diagnostic/{id}/submit', [DiagnosticTestController::class, 'submit'])->name('diagnostic.submit');
        Route::post('/diagnostic/{id}/save-draft', [DiagnosticTestController::class, 'saveDraft'])->name('diagnostic.save-draft');
        Route::get('/assignments/{assignment}/interactive', [InteractiveAssignmentController::class, 'show'])->name('assignments.interactive');
        Route::get('/assignments/{assignment}/pdf-stream', [InteractiveAssignmentController::class, 'streamSourcePdf'])->name('assignments.pdf-stream');
        Route::get('/submissions/{submission}/init', [InteractiveAssignmentController::class, 'loadState'])->name('submissions.init');
        Route::put('/submissions/{submission}/pages/{pageNumber}', [InteractiveAssignmentController::class, 'savePage'])->middleware('throttle:60,1')->name('submissions.pages.save');
        Route::post('/submissions/{submission}/submit', [InteractiveAssignmentController::class, 'submit'])->name('submissions.submit');
        Route::get('/submissions/{submission}/download-final', [InteractiveAssignmentController::class, 'downloadFinalPdf'])->name('submissions.download-final');
        Route::get('/quiz', [StudentDashboardController::class, 'quiz'])->name('quiz');
        Route::get('/quiz/{id}', [StudentDashboardController::class, 'quizShow'])->name('quiz.show');
        Route::post('/quiz/{id}/submit', [StudentDashboardController::class, 'quizSubmit'])->name('quiz.submit');
        Route::get('/progress', [StudentDashboardController::class, 'progress'])->name('progress');
        Route::get('/achievements', [StudentDashboardController::class, 'achievements'])->name('achievements');
        Route::get('/recordings', [RecordedClassController::class, 'index'])->name('recordings');
        Route::get('/recordings/{id}', [RecordedClassController::class, 'show'])->name('recordings.show');
        Route::match(['get', 'post'], '/classes/{liveClass}/join', [StudentDashboardController::class, 'joinClass'])->name('classes.join');
        Route::post('/attendance/correction-request', [StudentDashboardController::class, 'submitCorrectionRequest'])->name('attendance.correction.submit');
    });

    // Unrestricted Billing & Profile Routes (always accessible to student)
    Route::get('/fees', [InvoiceController::class, 'studentIndex'])->name('fees');
    Route::get('/fees/{invoice}', [InvoiceController::class, 'show'])->name('fees.show');
    Route::post('/fees/{invoice}/proof', [InvoiceController::class, 'uploadProof'])->name('fees.proof');
    Route::post('/fees/pay', [PaymentController::class, 'store'])->name('fees.pay');
    Route::get('/profile', [StudentDashboardController::class, 'profile'])->name('profile');
    Route::patch('/profile', [StudentDashboardController::class, 'updateProfile'])->name('profile.update');
    Route::patch('/profile/password', [StudentDashboardController::class, 'updatePassword'])->name('profile.password');
    Route::get('/settings', [StudentDashboardController::class, 'profile'])->name('settings');
    Route::patch('/settings/preferences', [StudentDashboardController::class, 'updatePreferences'])->name('settings.preferences');
    Route::post('/profile/link-parent', [StudentDashboardController::class, 'linkParent'])->name('profile.link-parent');
    Route::post('/notifications/read-all', [StudentDashboardController::class, 'readAllNotifications'])->name('notifications.readAll');
});

// ──────────────────────────────────────────────────
// PARENT DASHBOARD
// ──────────────────────────────────────────────────
Route::prefix('parent')->name('parent.')->middleware(['auth', 'role:parent'])->group(function () {
    Route::get('/dashboard', [ParentDashboardController::class, 'dashboard'])->name('dashboard');
    Route::post('/children', [ParentDashboardController::class, 'storeChild'])->name('children.store');
    Route::post('/connect-child', [ParentDashboardController::class, 'connectChild'])->name('children.connect')->middleware('throttle:10,1');

    // Query-param style routes (?child_id=X)
    Route::get('/progress', [ParentDashboardController::class, 'progress'])->name('progress');
    Route::get('/classes', [ParentDashboardController::class, 'classes'])->name('classes');
    Route::get('/timetable', [TimetableController::class, 'parentIndex'])->name('timetable');
    Route::get('/attendance', [ParentDashboardController::class, 'attendance'])->name('attendance');

    // RESTful child-specific academic endpoints (enforce parent authorization & child payment restriction)
    Route::get('/children/{child}/progress', [ParentDashboardController::class, 'childProgress'])->name('children.progress');
    Route::get('/children/{child}/results', [ParentDashboardController::class, 'childResults'])->name('children.results');
    Route::get('/children/{child}/attendance', [ParentDashboardController::class, 'childAttendance'])->name('children.attendance');
    Route::get('/children/{child}/classes', [ParentDashboardController::class, 'childClasses'])->name('children.classes');
    Route::get('/children/{child}/assignments', [ParentDashboardController::class, 'childAssignments'])->name('children.assignments');

    // Parent Billing & Payments (always accessible to parent)
    Route::get('/fees', [InvoiceController::class, 'parentIndex'])->name('fees');
    Route::get('/fees/{invoice}', [InvoiceController::class, 'show'])->name('fees.show');
    Route::post('/fees/{invoice}/proof', [InvoiceController::class, 'uploadProof'])->name('fees.proof');
    Route::post('/fees/checkout', [PaymentController::class, 'storeMulti'])->name('fees.checkout');
    Route::post('/fees/pay', [PaymentController::class, 'store'])->name('fees.pay');
    Route::post('/fees/{id}/pay', [ParentDashboardController::class, 'payChildInvoice'])->name('fees.pay.legacy');
    Route::get('/payments', [ParentDashboardController::class, 'payments'])->name('payments');
    Route::post('/payments/create-invoice', [ParentDashboardController::class, 'createCustomInvoice'])->name('payments.create-invoice');
});

// ──────────────────────────────────────────────────
// TEACHER DASHBOARD
// ──────────────────────────────────────────────────

// Teacher pending page — accessible to all authenticated teachers regardless of status
Route::get('/teacher/pending', function () {
    return view('auth.teacher-pending');
})->name('teacher.pending')->middleware(['auth', 'role:teacher']);

// Full teacher dashboard — requires active application_status
Route::prefix('teacher')->name('teacher.')->middleware(['auth', 'role:teacher', 'teacher.active'])->group(function () {
    Route::get('/dashboard', [TeacherDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/claims', [TeacherDashboardController::class, 'claimsIndex'])->name('claims');
    Route::post('/claims', [TeacherDashboardController::class, 'storeClaim'])->name('claims.store');
    Route::post('/claims/bank-details', [TeacherDashboardController::class, 'updateClaimBankDetails'])->name('claims.bank-details');
    Route::get('/classes', [TeacherDashboardController::class, 'classes'])->name('classes');
    Route::get('/timetable', [TimetableController::class, 'teacherIndex'])->name('timetable');
    Route::post('/classes', [TeacherDashboardController::class, 'storeClass'])->name('classes.store');
    Route::post('/classes/quick-create', [TimetableController::class, 'quickCreateSession'])->name('classes.quick-create');
    Route::post('/classes/{id}/request-cancel', [TeacherDashboardController::class, 'requestCancel'])->name('classes.cancel');
    Route::post('/classes/{id}/request-reschedule', [TeacherDashboardController::class, 'requestReschedule'])->name('classes.reschedule');
    Route::post('/classes/{id}/reschedule-direct', [TimetableController::class, 'teacherReschedule'])->name('classes.reschedule-direct');
    Route::post('/classes/{id}/meeting-url', [TeacherDashboardController::class, 'updateMeetingUrl'])->name('classes.meeting-url');
    Route::post('/classes/{liveClass}/attendance', [TeacherDashboardController::class, 'saveAttendance'])->name('classes.attendance.save');
    Route::post('/attendance/corrections/{correctionRequest}/review', [TeacherDashboardController::class, 'reviewCorrection'])->name('attendance.corrections.review');
    Route::get('/submissions/{submission}/review', [InteractiveAssignmentController::class, 'teacherReview'])->name('submissions.review');
    Route::get('/submissions/{submission}/final-pdf', [InteractiveAssignmentController::class, 'downloadFinalPdf'])->name('submissions.final-pdf');
    Route::post('/classes/{id}/tasks', [TeacherDashboardController::class, 'storeTask'])->name('classes.tasks.store');
    Route::get('/classes/{id}', [TeacherDashboardController::class, 'manageClass'])->name('classes.show');
    Route::get('/students', [TeacherDashboardController::class, 'students'])->name('students');
    Route::get('/learning-materials', [TeacherDashboardController::class, 'learningMaterials'])->name('materials');
    Route::post('/learning-materials', [TeacherDashboardController::class, 'storeMaterial'])->name('materials.store');
    Route::put('/learning-materials/{id}', [TeacherDashboardController::class, 'updateMaterial'])->name('materials.update');
    Route::delete('/learning-materials/{id}', [TeacherDashboardController::class, 'destroyMaterial'])->name('materials.destroy');
    Route::get('/learning-materials/{id}/download', [TeacherDashboardController::class, 'downloadMaterial'])->name('materials.download');

    Route::get('/tasks-and-assessments', [TeacherDashboardController::class, 'tasks'])->name('tasks');
    Route::post('/tasks-and-assessments', [TeacherDashboardController::class, 'storeGeneralTask'])->name('tasks.store');
    Route::put('/tasks-and-assessments/{id}', [TeacherDashboardController::class, 'updateTask'])->name('tasks.update');
    Route::delete('/tasks-and-assessments/{id}', [TeacherDashboardController::class, 'destroyTask'])->name('tasks.destroy');
    Route::post('/tasks-and-assessments/{assignmentId}/grade/{studentId}', [TeacherDashboardController::class, 'gradeSubmission'])->name('tasks.grade');
    Route::post('/quizzes', [TeacherDashboardController::class, 'storeQuiz'])->name('quizzes.store');
    Route::delete('/quizzes/{id}', [TeacherDashboardController::class, 'destroyQuiz'])->name('quizzes.destroy');

    // Class Recordings
    Route::get('/recordings', [TeacherDashboardController::class, 'recordings'])->name('recordings');
    Route::post('/recordings', [TeacherDashboardController::class, 'storeRecording'])->name('recordings.store');
    Route::put('/recordings/{id}', [TeacherDashboardController::class, 'updateRecording'])->name('recordings.update');
    Route::delete('/recordings/{id}', [TeacherDashboardController::class, 'destroyRecording'])->name('recordings.destroy');

    Route::get('/student-monitoring', [TeacherDashboardController::class, 'monitoring'])->name('monitoring');
    Route::get('/progress', [TeacherDashboardController::class, 'monitoring'])->name('progress');
    Route::post('/notifications/read-all', [TeacherDashboardController::class, 'readAllNotifications'])->name('notifications.readAll');

    // Profile & credential documents
    Route::get('/profile', [TeacherDashboardController::class, 'profile'])->name('profile');
    Route::put('/profile', [TeacherDashboardController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [TeacherDashboardController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/documents', [TeacherDashboardController::class, 'uploadDocument'])->name('profile.documents.upload');
    Route::delete('/profile/documents/{type}', [TeacherDashboardController::class, 'deleteDocument'])->name('profile.documents.delete');
    Route::get('/profile/documents/{type}', [TeacherDashboardController::class, 'viewDocument'])->name('profile.documents.view');
    Route::get('/profile/documents/{type}/download', [TeacherDashboardController::class, 'downloadDocument'])->name('profile.documents.download');

    // Gig routes
    Route::get('/gigs', [TeacherDashboardController::class, 'gigsMarketplace'])->name('gigs');
    Route::post('/gigs/{id}/claim', [TeacherDashboardController::class, 'claimGig'])->name('gigs.claim');
    Route::post('/gigs/{id}/checkin', [TeacherDashboardController::class, 'checkInGig'])->name('gigs.checkin');
    Route::post('/gigs/{id}/checkout', [TeacherDashboardController::class, 'checkOutGig'])->name('gigs.checkout');
    Route::post('/toggle-duty', [TeacherDashboardController::class, 'toggleDuty'])->name('toggle-duty');
    Route::get('/wallet', [TeacherDashboardController::class, 'walletIndex'])->name('wallet');
});

// ──────────────────────────────────────────────────
// ADMIN DASHBOARD
// ──────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'dashboard'])->name('dashboard');

    // Content & Media (Parent Greeting Popup & Homepage/Blog Articles)
    Route::get('/content', [AdminContentController::class, 'index'])->name('content.index');
    Route::post('/content/popup', [AdminContentController::class, 'updatePopup'])->name('content.popup.update');
    Route::post('/content/blog', [AdminContentController::class, 'storeBlog'])->name('content.blog.store');
    Route::put('/content/blog/{id}', [AdminContentController::class, 'updateBlog'])->name('content.blog.update');
    Route::delete('/content/blog/{id}', [AdminContentController::class, 'destroyBlog'])->name('content.blog.destroy');

    Route::get('/settings', [AdminSettingsController::class, 'settings'])->name('settings');
    Route::post('/settings/profile', [AdminSettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::post('/settings/platform', [AdminSettingsController::class, 'updatePlatformSettings'])->name('settings.platform');
    Route::post('/settings/pricing-plans', [AdminSettingsController::class, 'updatePricingPlans'])->name('settings.pricing-plans');
    Route::post('/settings/security', [AdminSettingsController::class, 'updateSecuritySettings'])->name('settings.security');
    Route::post('/settings/cache-clear', [AdminSettingsController::class, 'clearSystemCache'])->name('settings.cache-clear');
    Route::post('/settings/attendance', [AdminSettingsController::class, 'updateAttendanceSettings'])->name('settings.attendance');

    // Attendance Management & Export
    Route::get('/attendance', [AdminSettingsController::class, 'attendance'])->name('attendance');
    Route::get('/export/attendance', [AdminSettingsController::class, 'exportAttendanceCsv'])->name('export.attendance');

    // Export CSV routes
    Route::get('/export/payments', [AdminFinanceController::class, 'exportPaymentsCsv'])->name('export.payments');
    Route::get('/export/enrollments', [AdminFinanceController::class, 'exportEnrollmentsCsv'])->name('export.enrollments');

    // Course CRUD & Pricing Setup
    Route::post('/courses', [AdminCurriculumController::class, 'storeCourse'])->name('courses.store');
    Route::put('/courses/{id}', [AdminCurriculumController::class, 'updateCourse'])->name('courses.update');
    Route::delete('/courses/{id}', [AdminCurriculumController::class, 'destroyCourse'])->name('courses.destroy');

    // Class roster (Course Catalog > Students): add / remove existing students
    Route::get('/courses/{id}/students', [AdminCourseRosterController::class, 'index'])->whereNumber('id')->name('courses.students');
    Route::post('/courses/{id}/students', [AdminCourseRosterController::class, 'store'])->whereNumber('id')->name('courses.students.store');
    Route::delete('/courses/{id}/students/{studentId}', [AdminCourseRosterController::class, 'destroy'])->whereNumber(['id', 'studentId'])->name('courses.students.destroy');

    // Level clean-up: students who registered for several levels -> one level
    Route::get('/students/level-cleanup', [LevelCleanupController::class, 'index'])->name('students.level-cleanup.index');
    Route::get('/students/{student}/level-cleanup', [LevelCleanupController::class, 'preview'])->whereNumber('student')->name('students.level-cleanup.preview');
    Route::post('/students/{student}/level-cleanup', [LevelCleanupController::class, 'apply'])->whereNumber('student')->name('students.level-cleanup.apply');

    // Invoice Admin CRUD (View/Edit/Delete — separate from billing/store)
    Route::put('/invoices/{id}/admin-update', [AdminFinanceController::class, 'updateInvoice'])->name('invoices.admin-update');
    Route::put('/invoices/{id}/due-date', [AdminFinanceController::class, 'updateInvoiceDueDate'])->name('invoices.due-date.update');
    Route::post('/billing/default-due-days', [AdminFinanceController::class, 'updateDefaultDueDays'])->name('billing.default-due-days.update');
    Route::delete('/invoices/{id}', [AdminFinanceController::class, 'destroyInvoice'])->name('invoices.destroy');

    // Teacher Subject Assignment & Class Creation
    Route::post('/teachers/{id}/subjects', [AdminDashboardController::class, 'updateTeacherSubjects'])->name('teachers.subjects.update');
    Route::post('/teachers/{id}/classes', [AdminDashboardController::class, 'storeTeacherClass'])->name('teachers.classes.store');
    Route::post('/teachers/{id}/toggle-status', [AdminDashboardController::class, 'toggleTeacherStatus'])->name('teachers.toggle-status');
    Route::post('/students/{id}/toggle-status', [AdminDashboardController::class, 'toggleStudentStatus'])->name('students.toggle-status');

    // Subject CRUD
    Route::post('/subjects', [AdminCurriculumController::class, 'storeSubject'])->name('subjects.store');
    Route::match(['put', 'post'], '/subjects/{id}', [AdminCurriculumController::class, 'updateSubject'])->name('subjects.update');
    Route::delete('/subjects/{id}', [AdminCurriculumController::class, 'destroySubject'])->name('subjects.destroy');
    Route::get('/subjects/{id}', [AdminCurriculumController::class, 'showSubject'])->name('subjects.show');
    Route::post('/subjects/{id}/diagnostic-test', [AdminCurriculumController::class, 'setSubjectDiagnosticTest'])->name('subjects.diagnostic.set');
    Route::post('/subjects/{id}/diagnostic-test/create', [AdminCurriculumController::class, 'createSubjectDiagnosticTest'])->name('subjects.diagnostic.create');
    Route::delete('/subjects/{id}/diagnostic-test', [AdminCurriculumController::class, 'detachSubjectDiagnosticTest'])->name('subjects.diagnostic.detach');

    // Topic & Subtopic CRUD (Curriculum Syllabus Builder)
    Route::post('/topics', [AdminCurriculumController::class, 'storeTopic'])->name('topics.store');
    Route::match(['put', 'post'], '/topics/{id}', [AdminCurriculumController::class, 'updateTopic'])->name('topics.update');
    Route::delete('/topics/{id}', [AdminCurriculumController::class, 'destroyTopic'])->name('topics.destroy');
    Route::post('/topics/{topicId}/subtopics', [AdminCurriculumController::class, 'storeSubtopic'])->name('topics.subtopics.store');
    Route::match(['put', 'post'], '/subtopics/{id}', [AdminCurriculumController::class, 'updateSubtopic'])->name('subtopics.update');
    Route::delete('/subtopics/{id}', [AdminCurriculumController::class, 'destroySubtopic'])->name('subtopics.destroy');

    // Live Class CRUD
    Route::post('/live-classes', [AdminClassSchedulingController::class, 'storeLiveClass'])->name('live-classes.store');
    Route::match(['put', 'post'], '/live-classes/{id}', [AdminClassSchedulingController::class, 'updateLiveClass'])->name('live-classes.update');
    Route::delete('/live-classes/{id}', [AdminClassSchedulingController::class, 'destroyLiveClass'])->name('live-classes.destroy');

    // Recorded Class CRUD
    Route::post('/recorded-classes', [AdminClassSchedulingController::class, 'storeRecordedClass'])->name('recorded-classes.store');
    Route::match(['put', 'post'], '/recorded-classes/{id}', [AdminClassSchedulingController::class, 'updateRecordedClass'])->name('recorded-classes.update');
    Route::delete('/recorded-classes/{id}', [AdminClassSchedulingController::class, 'destroyRecordedClass'])->name('recorded-classes.destroy');

    // Tasks & Diagnostic Tests Management
    Route::post('/tasks', [AdminDashboardController::class, 'storeTask'])->name('tasks.store');
    Route::post('/tasks/parse-pdf', [AdminDashboardController::class, 'parsePdfQuestions'])->name('tasks.parse-pdf');
    Route::post('/tasks/upload-question-image', [AdminDashboardController::class, 'uploadQuestionImage'])->name('tasks.upload-question-image');
    Route::match(['put', 'post'], '/tasks/{id}', [AdminDashboardController::class, 'updateTask'])->name('tasks.update');
    Route::delete('/tasks/{id}', [AdminDashboardController::class, 'destroyTask'])->name('tasks.destroy');
    Route::post('/tasks/{assignmentId}/grade/{studentId}', [AdminDashboardController::class, 'gradeTask'])->name('tasks.grade');
    Route::get('/tasks/{id}/submissions', [AdminDashboardController::class, 'getTaskSubmissions'])->name('tasks.submissions');
    Route::get('/submissions/{submission}/review', [InteractiveAssignmentController::class, 'adminReview'])->name('submissions.review');
    Route::get('/submissions/{submission}/final-pdf', [InteractiveAssignmentController::class, 'downloadFinalPdf'])->name('submissions.final-pdf');
    Route::get('/diagnostic/{id}/details', [DiagnosticTestController::class, 'getAdminDetails'])->name('diagnostic.details');
    Route::post('/diagnostic/{id}/grade', [DiagnosticTestController::class, 'saveAdminGrade'])->name('diagnostic.grade');
    Route::post('/diagnostic/test-rule', [DiagnosticTestController::class, 'testGradingRule'])->name('diagnostic.test-rule');

    // Parent Accounts Directory
    Route::post('/parents/link', [AdminDashboardController::class, 'linkParentStudent'])->name('parents.link');
    Route::post('/parents/unlink', [AdminDashboardController::class, 'unlinkParentStudent'])->name('parents.unlink');

    // Teacher Class Fee Claims
    Route::post('/teacher-claims/{id}/approve', [AdminDashboardController::class, 'approveTeacherClaim'])->name('teacher-claims.approve');
    Route::post('/teacher-claims/{id}/reject', [AdminDashboardController::class, 'rejectTeacherClaim'])->name('teacher-claims.reject');
    Route::post('/teacher-claims/{id}/mark-paid', [AdminDashboardController::class, 'markPaidTeacherClaim'])->name('teacher-claims.mark-paid');

    // Teacher Class Requests (reschedule & cancellation approvals)
    Route::post('/class-requests/{id}/approve', [AdminDashboardController::class, 'approveClassRequest'])->name('class-requests.approve');
    Route::post('/class-requests/{id}/reject', [AdminDashboardController::class, 'rejectClassRequest'])->name('class-requests.reject');

    // Quiz CRUD
    Route::post('/quizzes', [AdminDashboardController::class, 'storeQuiz'])->name('quizzes.store');
    Route::put('/quizzes/{id}', [AdminDashboardController::class, 'updateQuiz'])->name('quizzes.update');
    Route::delete('/quizzes/{id}', [AdminDashboardController::class, 'destroyQuiz'])->name('quizzes.destroy');

    // Question CRUD
    Route::post('/quizzes/{quizId}/questions', [AdminDashboardController::class, 'storeQuestion'])->name('quizzes.questions.store');
    Route::put('/questions/{id}', [AdminDashboardController::class, 'updateQuestion'])->name('questions.update');
    Route::delete('/questions/{id}', [AdminDashboardController::class, 'destroyQuestion'])->name('questions.destroy');

    // Teacher Applications Review & Approvals
    Route::post('/teacher-applications/{id}/approve', [AdminDashboardController::class, 'approveTeacherApplication'])->name('teacher-applications.approve');
    Route::post('/teacher-applications/{id}/reject', [AdminDashboardController::class, 'rejectTeacherApplication'])->name('teacher-applications.reject');
    Route::get('/teacher-applications/{id}/document/{type}', [AdminDashboardController::class, 'viewTeacherApplicationDocument'])->name('teacher-applications.document');

    // Teaching Gigs & Masterclasses
    Route::post('/gigs', [AdminDashboardController::class, 'storeGig'])->name('gigs.store');
    Route::delete('/gigs/{id}', [AdminDashboardController::class, 'destroyGig'])->name('gigs.destroy');

    Route::get('/users', fn () => redirect()->to(route('admin.dashboard').'#users'))->name('users.index');
    Route::get('/users/{id}', [AdminDashboardController::class, 'showUser'])->name('users.show');
    Route::post('/users', [AdminDashboardController::class, 'storeUser'])->name('users.store');
    Route::match(['put', 'post'], '/users/{id}', [AdminDashboardController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{id}', [AdminDashboardController::class, 'destroyUser'])->name('users.destroy');
    Route::post('/users/{id}/delete', [AdminDashboardController::class, 'destroyUser'])->name('users.destroy.post');
    Route::post('/users/{id}/reset-password', [AdminDashboardController::class, 'resetUserPassword'])->name('users.reset-password');
    Route::post('/students/{id}/toggle-status', [AdminDashboardController::class, 'toggleStudentStatus'])->name('students.toggle-status');

    // Outreach & Engagement Hub (Company Records — Non-Active Users)
    Route::post('/users/{id}/toggle-is-active', [AdminDashboardController::class, 'toggleUserIsActive'])->name('users.toggle-is-active');
    Route::post('/outreach/blast-email', [AdminDashboardController::class, 'blastEngagementEmail'])->name('outreach.blast-email');
    Route::get('/outreach/export-inactive-csv', [AdminDashboardController::class, 'exportInactiveUsersCsv'])->name('outreach.export-inactive-csv');

    // Official Certificates Management
    Route::post('/certificates', [AdminSettingsController::class, 'storeCertificate'])->name('certificates.store');
    Route::delete('/certificates/{id}', [AdminSettingsController::class, 'destroyCertificate'])->name('certificates.destroy');

    // Financial & Payment Management (Fee Structures, Invoices, Payment Verification, Gateway Settings)
    Route::post('/fee-structures', [AdminFinanceController::class, 'storeFeeStructure'])->name('fee-structures.store');
    Route::put('/fee-structures/{id}', [AdminFinanceController::class, 'updateFeeStructure'])->name('fee-structures.update');
    Route::delete('/fee-structures/{id}', [AdminFinanceController::class, 'destroyFeeStructure'])->name('fee-structures.destroy');
    // Package engine — subjects, items, tiers, versioning, duplication
    Route::put('/fee-structures/{id}/subjects', [AdminFinanceController::class, 'updateFeeStructureSubjects'])->name('fee-structures.subjects.update');
    Route::put('/fee-structures/{id}/items', [AdminFinanceController::class, 'updateFeeStructureItems'])->name('fee-structures.items.update');
    Route::put('/fee-structures/{id}/tiers', [AdminFinanceController::class, 'updateFeeStructureTiers'])->name('fee-structures.tiers.update');
    Route::post('/fee-structures/{id}/duplicate', [AdminFinanceController::class, 'duplicateFeeStructure'])->name('fee-structures.duplicate');
    Route::post('/fee-structures/{id}/version', [AdminFinanceController::class, 'createFeeStructureVersion'])->name('fee-structures.version');
    Route::get('/api/fee-structures/{id}/subjects', [AdminFinanceController::class, 'apiFeeStructureSubjects'])->name('api.fee-structures.subjects');
    Route::get('/api/fee-structures/{id}/preview', [AdminFinanceController::class, 'apiFeeStructurePreview'])->name('api.fee-structures.preview');

    Route::post('/settings/finance', [AdminFinanceController::class, 'updateFinanceSettings'])->name('settings.finance');
    Route::post('/settings/finance/reveal', [AdminFinanceController::class, 'revealGatewayCredentials'])->name('settings.finance.reveal');
    Route::post('/settings/billplz/test-connection', [AdminFinanceController::class, 'testBillplzConnection'])->name('settings.billplz.test');
    Route::post('/invoices/{id}/status', [BillingController::class, 'updateInvoiceStatusQuick'])->name('invoices.status');

    // Quick invoice store
    Route::post('/invoices', [BillingController::class, 'invoiceStoreQuick'])->name('invoices.store');

    // Authorize / Record Payment (Supports Full & Partial Payments)
    Route::post('/invoices/{id}/record-payment', [BillingController::class, 'recordInvoicePaymentQuick'])->name('invoices.record-payment');

    // Quick payment verify
    Route::post('/payments/{payment}/verify', [BillingController::class, 'verifyPaymentQuick'])->name('payments.verify');

    // ── Billing Module ────────────────────────────────────────────────────────
    Route::prefix('billing')->name('billing.')->group(function () {
        // The standalone Billing page was retired 2026-11-27: everything lives
        // in Admin Dashboard > Financials & Sales. Old links land there.
        Route::get('/', fn () => redirect()->to(route('admin.dashboard').'#financials'))->name('dashboard');

        // Financials tools: free-trial switch and bulk cancel of unpaid invoices
        Route::post('/free-trial', [BillingToolsController::class, 'updateFreeTrial'])->name('free-trial.update');
        Route::post('/invoices/cancel-unpaid', [BillingToolsController::class, 'cancelUnpaid'])->name('invoices.cancel-unpaid');

        // Invoices (Redirect all standalone views to unified admin dashboard table)
        Route::get('/invoices', function () {
            return redirect('/admin/dashboard#invoices');
        })->name('invoices');
        Route::get('/invoices/create', function () {
            return redirect('/admin/dashboard#invoices');
        })->name('invoices.create');
        Route::post('/invoices', [BillingController::class, 'invoiceStore'])->name('invoices.store');
        Route::get('/invoices/{invoice}', function () {
            return redirect('/admin/dashboard#invoices');
        })->name('invoices.show');
        Route::post('/invoices/{invoice}/cancel', [BillingController::class, 'invoiceCancel'])->name('invoices.cancel');
        Route::get('/invoices/{invoice}/reminders', function () {
            return redirect('/admin/dashboard#invoices');
        })->name('invoices.reminders');

        // Payments
        // Standalone All Payments page retired 2026-11-27: Financials > Payments Ledger.
        Route::get('/payments', fn () => redirect()->to(route('admin.dashboard').'#financials'))->name('payments');
        Route::get('/payments/{payment}', function () {
            return redirect()->to(route('admin.dashboard').'#financials');
        })->name('payment.show');
        Route::post('/payments/{payment}/verify', [BillingController::class, 'verifyPayment'])->name('payments.verify');
        Route::post('/payments/{payment}/reject', [BillingController::class, 'rejectPayment'])->name('payments.reject');

        // Subject fees are edited on the subject itself (Curriculum > Subjects > Edit).
        // Old bookmarks to the removed Billing > Subject Fees page land there.
        Route::redirect('/subject-fees', '/admin/dashboard#courses-subjects', 301);

        // Email Settings (redirects to unified email management hub)
        Route::get('/email-settings', function () {
            return redirect('/admin/dashboard#emailsettings');
        })->name('email-settings');
        Route::post('/email-settings', [BillingController::class, 'updateEmailSettings'])->name('email-settings.update');
    });

    // ── Communication & Enterprise Email Management ──
    Route::prefix('email-management')->name('email.')->group(function () {
        Route::post('/audience-preview', [EmailManagementController::class, 'previewAudience'])->name('audience.preview');
        Route::post('/template-preview', [EmailManagementController::class, 'previewTemplate'])->name('template.preview');
        Route::post('/templates', [EmailManagementController::class, 'storeTemplate'])->name('templates.store');
        Route::put('/templates/{template}', [EmailManagementController::class, 'updateTemplate'])->name('templates.update');
        Route::post('/templates/{template}/duplicate', [EmailManagementController::class, 'duplicateTemplate'])->name('templates.duplicate');
        Route::post('/templates/{template}/toggle', [EmailManagementController::class, 'toggleTemplate'])->name('templates.toggle');
        Route::delete('/templates/{template}', [EmailManagementController::class, 'archiveTemplate'])->name('templates.destroy');
        Route::post('/send-test', [EmailManagementController::class, 'sendTestEmail'])->name('send-test');
        Route::post('/campaigns', [EmailManagementController::class, 'storeCampaign'])->name('campaigns.store');
        Route::post('/campaigns/{campaign}/cancel', [EmailManagementController::class, 'cancelCampaign'])->name('campaigns.cancel');
        Route::post('/automations/{automation}/toggle', [EmailManagementController::class, 'toggleAutomation'])->name('automations.toggle');
        Route::put('/automations/{automation}', [EmailManagementController::class, 'updateAutomation'])->name('automations.update');
        Route::post('/automations/{automation}/test', [EmailManagementController::class, 'testAutomation'])->name('automations.test');
        Route::post('/logs/{log}/retry', [EmailManagementController::class, 'retryLog'])->name('logs.retry');
        Route::get('/logs/export-csv', [EmailManagementController::class, 'exportLogsCsv'])->name('logs.export-csv');
        Route::post('/settings', [EmailManagementController::class, 'updateSettings'])->name('settings.update');
    });

    // ── Admin Master Timetable & Academic Calendar Management ──
    Route::get('/timetable', [TimetableController::class, 'adminIndex'])->name('timetable');
    Route::post('/classes/quick-create', [TimetableController::class, 'adminQuickCreateSession'])->name('classes.quick-create');
    Route::post('/classes/{id}/reschedule-direct', [TimetableController::class, 'adminReschedule'])->name('classes.reschedule-direct');
    Route::post('/academic-events', [TimetableController::class, 'storeAcademicEvent'])->name('academic-events.store');
    Route::put('/academic-events/{id}', [TimetableController::class, 'updateAcademicEvent'])->name('academic-events.update');
    Route::delete('/academic-events/{id}', [TimetableController::class, 'destroyAcademicEvent'])->name('academic-events.destroy');
});

// ──────────────────────────────────────────────────
// PROFILE (Breeze)
// ──────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/materials/{id}/download', [TeacherDashboardController::class, 'downloadMaterial'])->name('materials.download');
    Route::get('/student-assignments/{studentAssignment}/download', [TeacherDashboardController::class, 'downloadStudentSubmission'])->name('student-assignments.download');
    Route::get('/student-assignments/{studentAssignment}/download-graded', [TeacherDashboardController::class, 'downloadGradedTask'])->name('student-assignments.download-graded');
});

// ──────────────────────────────────────────────────
// PAYMENT GATEWAY WEBHOOK (public, CSRF-exempt)
// Exclude this route from CSRF in bootstrap/app.php:
//   ->withMiddleware(function (Middleware $middleware) {
//       $middleware->validateCsrfTokens(except: ['webhooks/*']);
//   })
// ──────────────────────────────────────────────────
Route::post('/webhooks/payment-gateway', [PaymentController::class, 'gatewayWebhook'])
    ->name('webhooks.payment-gateway');

// ──────────────────────────────────────────────────
// BILLPLZ PAYMENT GATEWAY (FPX ONLINE BANKING)
// ──────────────────────────────────────────────────
Route::prefix('billing/billplz')->name('billing.billplz.')->group(function () {
    Route::post('/checkout/{invoice}', [BillplzController::class, 'checkout'])->middleware(['auth'])->name('checkout');
    Route::post('/checkout-multi', [BillplzController::class, 'checkoutMulti'])->middleware(['auth'])->name('checkout-multi');
    Route::match(['get', 'post'], '/return', [BillplzController::class, 'handleReturn'])->name('return');
    Route::post('/webhook', [BillplzController::class, 'handleWebhook'])->name('webhook');
});

// ──────────────────────────────────────────────────
// RENEWAL: "Continue in {school year}?" (student, linked parent or admin)
// ──────────────────────────────────────────────────
Route::middleware('auth')->prefix('renew')->name('renew.')->group(function () {
    Route::get('/{token}', [RenewalController::class, 'show'])->name('show');
    Route::post('/{token}', [RenewalController::class, 'confirm'])->name('confirm');
    Route::post('/{token}/decline', [RenewalController::class, 'decline'])->name('decline');
    Route::post('/{token}/stop', [RenewalController::class, 'stop'])->name('stop');
});

// TIMETABLE & CALENDAR API (Authenticated)
// ──────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/api/timetable/events', [TimetableController::class, 'getEventsJson'])->name('timetable.events');
    Route::get('/timetable/export-ics/{id}', [TimetableController::class, 'exportIcs'])->name('timetable.export-ics');
});

// ──────────────────────────────────────────────────
// FLOATING CHATBOX API  (all authenticated users)
// ──────────────────────────────────────────────────
Route::middleware(['auth'])->prefix('chat')->name('chat.')->group(function () {

    // Global unread badge count (polls every 15s when minimised)
    Route::get('/unread-count', [ChatController::class, 'getGlobalUnreadCount'])->name('unread-count');

    // Contact / conversation list & Directory (new chat)
    Route::get('/contacts', [ChatController::class, 'getContacts'])->name('contacts');
    Route::get('/directory', [ChatController::class, 'getDirectory'])->name('directory');

    // Find or create a 1-to-1 conversation
    Route::post('/conversations', [ChatController::class, 'findOrCreateConversation'])->name('conversations.find-or-create');

    // Messages: delta fetch (since_id) and paginated load
    Route::get('/conversations/{conversation}/messages', [ChatController::class, 'getMessages'])->name('conversations.messages');
    Route::get('/conversations/{conversation}/messages/older', [ChatController::class, 'getOlderMessages'])->name('conversations.messages.older');
    Route::post('/conversations/{conversation}/messages', [ChatController::class, 'sendMessage'])->name('conversations.messages.send');
    // Clear all conversations (wipes chat blocks and messages)
    Route::delete('/conversations/clear-all', [ChatController::class, 'clearAllChats'])->name('conversations.clear-all');
    Route::delete('/conversations/{conversation}', [ChatController::class, 'deleteConversation'])->name('conversations.delete');
    Route::delete('/conversations/{conversation}/clear', [ChatController::class, 'clearChat'])->name('conversations.clear');

    // Attachments
    Route::post('/conversations/{conversation}/attachments', [ChatController::class, 'uploadAttachment'])->name('conversations.attachments');
    Route::get('/attachments/{attachment}/download', [ChatController::class, 'downloadAttachment'])->name('attachment.download');

    // Typing indicators
    Route::post('/conversations/{conversation}/typing', [ChatController::class, 'setTyping'])->name('conversations.typing');
    Route::get('/conversations/{conversation}/typing', [ChatController::class, 'getTyping'])->name('conversations.typing.get');

    // Mute / Archive
    Route::patch('/conversations/{conversation}/mute', [ChatController::class, 'muteConversation'])->name('conversations.mute');
    Route::patch('/conversations/{conversation}/unmute', [ChatController::class, 'unmuteConversation'])->name('conversations.unmute');
    Route::patch('/conversations/{conversation}/archive', [ChatController::class, 'archiveConversation'])->name('conversations.archive');

    // Parental monitoring (parent role only)
    Route::middleware(['role:parent'])->prefix('monitor')->name('monitor.')->group(function () {
        Route::get('/children', [ChatController::class, 'getMonitoredChildren'])->name('children');
        Route::get('/children/{studentId}/conversations', [ChatController::class, 'getChildConversations'])->name('child-conversations');
        Route::get('/children/{studentId}/conversations/{conversation}', [ChatController::class, 'getChildConversation'])->name('child-conversation');
    });

    // Admin: all conversations with filters & user context
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/contacts', [ChatController::class, 'adminGetContacts'])->name('contacts');
        Route::patch('/conversations/{conversation}/status', [ChatController::class, 'adminUpdateStatus'])->name('conversations.status');
        Route::get('/user-context/{userId}', [ChatController::class, 'adminGetUserContext'])->name('user-context');
    });
});
