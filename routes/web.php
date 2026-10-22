<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Auth\ParentRegistrationController;
use App\Http\Controllers\Auth\StudentRegistrationController;
use App\Http\Controllers\Auth\TeacherApplicationController;
use App\Http\Controllers\Billing\InvoiceController;
use App\Http\Controllers\Billing\PaymentController;
use App\Http\Controllers\Billing\BillplzController;
use App\Http\Controllers\Admin\BillingController;
use App\Http\Controllers\Admin\AdminContentController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ParentDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\TeacherDashboardController;
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
Route::redirect('/free-trial', '/register', 301)->name('free-trial');
Route::post('/free-trial', fn () => redirect()->route('register', [], 301))->name('free-trial.submit');
Route::post('/api/check-registration-availability', [PageController::class, 'checkRegistrationAvailability'])->name('registration.check-availability');
Route::get('/learning-platform', [PageController::class, 'learningPlatform'])->name('learning-platform');

// ──────────────────────────────────────────────────
// ROLE-SPECIFIC REGISTRATION FLOWS
// ──────────────────────────────────────────────────

// Student Registration
Route::get('/register/student', [StudentRegistrationController::class, 'create'])->name('register.student')->middleware('guest');
Route::post('/register/student', [StudentRegistrationController::class, 'store'])->name('register.student.submit')->middleware('guest');
Route::get('/register/student/success', [StudentRegistrationController::class, 'success'])->name('register.student.success');

// Parent Registration + IC-based AJAX verification
Route::get('/register/parent', [ParentRegistrationController::class, 'create'])->name('register.parent')->middleware('guest');
Route::post('/register/parent', [ParentRegistrationController::class, 'store'])->name('register.parent.submit')->middleware('guest');
Route::post('/api/verify-student-ic', [ParentRegistrationController::class, 'verifyStudentIc'])->name('register.verify.ic');
Route::get('/register/parent/success', [ParentRegistrationController::class, 'success'])->name('register.parent.success');

// Teacher Application (Apply to Teach — no password, creates application record only)
Route::get('/register/teacher', [TeacherApplicationController::class, 'create'])->name('register.teacher')->middleware('guest');
Route::post('/register/teacher', [TeacherApplicationController::class, 'store'])->name('register.teacher.submit')->middleware('guest');
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
        Route::get('/timetable', [\App\Http\Controllers\TimetableController::class, 'studentIndex'])->name('timetable');
        Route::get('/tasks', [StudentDashboardController::class, 'tasks'])->name('tasks');
        Route::post('/tasks/{id}/submit', [StudentDashboardController::class, 'submitTask'])->name('tasks.submit');
        Route::get('/assignments/{assignment}/interactive', [\App\Http\Controllers\InteractiveAssignmentController::class, 'show'])->name('assignments.interactive');
        Route::get('/assignments/{assignment}/pdf-stream', [\App\Http\Controllers\InteractiveAssignmentController::class, 'streamSourcePdf'])->name('assignments.pdf-stream');
        Route::get('/submissions/{submission}/init', [\App\Http\Controllers\InteractiveAssignmentController::class, 'loadState'])->name('submissions.init');
        Route::put('/submissions/{submission}/pages/{pageNumber}', [\App\Http\Controllers\InteractiveAssignmentController::class, 'savePage'])->middleware('throttle:60,1')->name('submissions.pages.save');
        Route::post('/submissions/{submission}/submit', [\App\Http\Controllers\InteractiveAssignmentController::class, 'submit'])->name('submissions.submit');
        Route::get('/submissions/{submission}/download-final', [\App\Http\Controllers\InteractiveAssignmentController::class, 'downloadFinalPdf'])->name('submissions.download-final');
        Route::get('/diagnostic/{id}', [\App\Http\Controllers\DiagnosticTestController::class, 'take'])->name('diagnostic.take');
        Route::get('/diagnostic/{id}/pdf', [\App\Http\Controllers\DiagnosticTestController::class, 'streamPdf'])->name('diagnostic.pdf');
        Route::post('/diagnostic/{id}/save-draft', [\App\Http\Controllers\DiagnosticTestController::class, 'saveDraft'])->name('diagnostic.save-draft');
        Route::post('/diagnostic/{id}/submit', [\App\Http\Controllers\DiagnosticTestController::class, 'submit'])->name('diagnostic.submit');
        Route::get('/quiz', [StudentDashboardController::class, 'quiz'])->name('quiz');
        Route::get('/quiz/{id}', [StudentDashboardController::class, 'quizShow'])->name('quiz.show');
        Route::post('/quiz/{id}/submit', [StudentDashboardController::class, 'quizSubmit'])->name('quiz.submit');
        Route::get('/progress', [StudentDashboardController::class, 'progress'])->name('progress');
        Route::get('/achievements', [StudentDashboardController::class, 'achievements'])->name('achievements');
        Route::get('/recordings', [\App\Http\Controllers\RecordedClassController::class, 'index'])->name('recordings');
        Route::get('/recordings/{id}', [\App\Http\Controllers\RecordedClassController::class, 'show'])->name('recordings.show');
        Route::match(['get', 'post'], '/classes/{liveClass}/join', [StudentDashboardController::class, 'joinClass'])->name('classes.join');
        Route::post('/attendance/correction-request', [StudentDashboardController::class, 'submitCorrectionRequest'])->name('attendance.correction.submit');
    });

    // Unrestricted Billing & Profile Routes (always accessible to student)
    Route::get('/fees', [InvoiceController::class, 'studentIndex'])->name('fees');
    Route::get('/fees/{invoice}', [InvoiceController::class, 'show'])->name('fees.show');
    Route::post('/fees/{invoice}/proof', [InvoiceController::class, 'uploadProof'])->name('fees.proof');
    Route::post('/fees/pay', [PaymentController::class, 'store'])->name('fees.pay');
    Route::get('/messages', [MessageController::class, 'studentIndex'])->name('messages');
    Route::get('/messages/conversation/{userId}', [MessageController::class, 'conversation'])->name('messages.conversation');
    Route::post('/messages/send', [MessageController::class, 'send'])->name('messages.send');
    Route::post('/messages/contacts-preview', [MessageController::class, 'contactsPreview'])->name('messages.contacts.preview');
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
    Route::post('/connect-child', [ParentDashboardController::class, 'connectChild'])->name('children.connect');

    // Query-param style routes (?child_id=X)
    Route::get('/progress', [ParentDashboardController::class, 'progress'])->name('progress');
    Route::get('/classes', [ParentDashboardController::class, 'classes'])->name('classes');
    Route::get('/timetable', [\App\Http\Controllers\TimetableController::class, 'parentIndex'])->name('timetable');
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
    Route::post('/payments/pay', [ParentDashboardController::class, 'processPayment'])->name('payments.pay');
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
    Route::get('/classes', [TeacherDashboardController::class, 'classes'])->name('classes');
    Route::get('/timetable', [\App\Http\Controllers\TimetableController::class, 'teacherIndex'])->name('timetable');
    Route::post('/classes', [TeacherDashboardController::class, 'storeClass'])->name('classes.store');
    Route::post('/classes/quick-create', [\App\Http\Controllers\TimetableController::class, 'quickCreateSession'])->name('classes.quick-create');
    Route::post('/classes/{id}/request-cancel', [TeacherDashboardController::class, 'requestCancel'])->name('classes.cancel');
    Route::post('/classes/{id}/request-reschedule', [TeacherDashboardController::class, 'requestReschedule'])->name('classes.reschedule');
    Route::post('/classes/{id}/reschedule-direct', [\App\Http\Controllers\TimetableController::class, 'teacherReschedule'])->name('classes.reschedule-direct');
    Route::post('/classes/{id}/tasks', [TeacherDashboardController::class, 'storeTask'])->name('classes.tasks.store');
    Route::post('/classes/{liveClass}/attendance', [TeacherDashboardController::class, 'saveAttendance'])->name('classes.attendance.save');
    Route::post('/attendance/corrections/{correctionRequest}/review', [TeacherDashboardController::class, 'reviewCorrection'])->name('attendance.corrections.review');
    Route::get('/classes/{id}', [TeacherDashboardController::class, 'manageClass'])->name('classes.show');
    Route::post('/classes/{id}/meeting-url', [TeacherDashboardController::class, 'updateMeetingUrl'])->name('classes.meeting-url');
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
    Route::get('/submissions/{submission}/review', [\App\Http\Controllers\InteractiveAssignmentController::class, 'teacherReview'])->name('submissions.review');
    Route::get('/submissions/{submission}/final-pdf', [\App\Http\Controllers\InteractiveAssignmentController::class, 'downloadFinalPdf'])->name('submissions.final-pdf');
    Route::post('/quizzes', [TeacherDashboardController::class, 'storeQuiz'])->name('quizzes.store');
    Route::delete('/quizzes/{id}', [TeacherDashboardController::class, 'destroyQuiz'])->name('quizzes.destroy');

    // Class Recordings
    Route::get('/recordings', [TeacherDashboardController::class, 'recordings'])->name('recordings');
    Route::post('/recordings', [TeacherDashboardController::class, 'storeRecording'])->name('recordings.store');
    Route::put('/recordings/{id}', [TeacherDashboardController::class, 'updateRecording'])->name('recordings.update');
    Route::delete('/recordings/{id}', [TeacherDashboardController::class, 'destroyRecording'])->name('recordings.destroy');

    Route::get('/student-monitoring', [TeacherDashboardController::class, 'monitoring'])->name('monitoring');
    Route::get('/progress', [TeacherDashboardController::class, 'monitoring'])->name('progress');
    Route::get('/messages', [MessageController::class, 'teacherIndex'])->name('messages');
    Route::get('/messages/conversation/{userId}', [MessageController::class, 'conversation'])->name('messages.conversation');
    Route::post('/messages/send', [MessageController::class, 'send'])->name('messages.send');
    Route::post('/messages/contacts-preview', [MessageController::class, 'contactsPreview'])->name('messages.contacts.preview');
    Route::post('/notifications/read-all', [TeacherDashboardController::class, 'readAllNotifications'])->name('notifications.readAll');

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

    Route::get('/settings', [AdminDashboardController::class, 'settings'])->name('settings');
    Route::post('/settings/profile', [AdminDashboardController::class, 'updateProfile'])->name('settings.profile');
    Route::post('/settings/platform', [AdminDashboardController::class, 'updatePlatformSettings'])->name('settings.platform');
    Route::post('/settings/pricing-plans', [AdminDashboardController::class, 'updatePricingPlans'])->name('settings.pricing-plans');
    Route::post('/settings/security', [AdminDashboardController::class, 'updateSecuritySettings'])->name('settings.security');
    Route::post('/settings/cache-clear', [AdminDashboardController::class, 'clearSystemCache'])->name('settings.cache-clear');
    Route::post('/settings/attendance', [AdminDashboardController::class, 'updateAttendanceSettings'])->name('settings.attendance');

    // Attendance Management & Export
    Route::get('/attendance', [AdminDashboardController::class, 'attendance'])->name('attendance');
    Route::get('/export/attendance', [AdminDashboardController::class, 'exportAttendanceCsv'])->name('export.attendance');

    // Export CSV routes
    Route::get('/export/payments', [AdminDashboardController::class, 'exportPaymentsCsv'])->name('export.payments');
    Route::get('/export/enrollments', [AdminDashboardController::class, 'exportEnrollmentsCsv'])->name('export.enrollments');

    // Course CRUD & Pricing Setup
    Route::post('/courses', [AdminDashboardController::class, 'storeCourse'])->name('courses.store');
    Route::put('/courses/{id}', [AdminDashboardController::class, 'updateCourse'])->name('courses.update');
    Route::delete('/courses/{id}', [AdminDashboardController::class, 'destroyCourse'])->name('courses.destroy');

    // Invoice Admin CRUD (View/Edit/Delete — separate from billing/store)
    Route::put('/invoices/{id}/admin-update', [AdminDashboardController::class, 'updateInvoice'])->name('invoices.admin-update');
    Route::put('/invoices/{id}/due-date', [AdminDashboardController::class, 'updateInvoiceDueDate'])->name('invoices.due-date.update');
    Route::post('/billing/default-due-days', [AdminDashboardController::class, 'updateDefaultDueDays'])->name('billing.default-due-days.update');
    Route::delete('/invoices/{id}', [AdminDashboardController::class, 'destroyInvoice'])->name('invoices.destroy');

    // Teacher Subject Assignment & Class Creation
    Route::post('/teachers/{id}/subjects', [AdminDashboardController::class, 'updateTeacherSubjects'])->name('teachers.subjects.update');
    Route::post('/teachers/{id}/classes', [AdminDashboardController::class, 'storeTeacherClass'])->name('teachers.classes.store');
    Route::post('/teachers/{id}/toggle-status', [AdminDashboardController::class, 'toggleTeacherStatus'])->name('teachers.toggle-status');
    Route::post('/students/{id}/toggle-status', [AdminDashboardController::class, 'toggleStudentStatus'])->name('students.toggle-status');

    // Subject CRUD
    Route::post('/subjects', [AdminDashboardController::class, 'storeSubject'])->name('subjects.store');
    Route::match(['put', 'post'], '/subjects/{id}', [AdminDashboardController::class, 'updateSubject'])->name('subjects.update');
    Route::delete('/subjects/{id}', [AdminDashboardController::class, 'destroySubject'])->name('subjects.destroy');
    Route::get('/subjects/{id}', [AdminDashboardController::class, 'showSubject'])->name('subjects.show');

    // Subject Diagnostic Test Management
    Route::post('/subjects/{id}/diagnostic-test', [AdminDashboardController::class, 'setSubjectDiagnosticTest'])->name('subjects.diagnostic.set');
    Route::post('/subjects/{id}/diagnostic-test/create', [AdminDashboardController::class, 'createSubjectDiagnosticTest'])->name('subjects.diagnostic.create');
    Route::delete('/subjects/{id}/diagnostic-test', [AdminDashboardController::class, 'detachSubjectDiagnosticTest'])->name('subjects.diagnostic.detach');

    // Topic & Subtopic CRUD (Curriculum Syllabus Builder)
    Route::post('/topics', [AdminDashboardController::class, 'storeTopic'])->name('topics.store');
    Route::match(['put', 'post'], '/topics/{id}', [AdminDashboardController::class, 'updateTopic'])->name('topics.update');
    Route::delete('/topics/{id}', [AdminDashboardController::class, 'destroyTopic'])->name('topics.destroy');
    Route::post('/topics/{topicId}/subtopics', [AdminDashboardController::class, 'storeSubtopic'])->name('topics.subtopics.store');
    Route::match(['put', 'post'], '/subtopics/{id}', [AdminDashboardController::class, 'updateSubtopic'])->name('subtopics.update');
    Route::delete('/subtopics/{id}', [AdminDashboardController::class, 'destroySubtopic'])->name('subtopics.destroy');

    // Live Class CRUD
    Route::post('/live-classes', [AdminDashboardController::class, 'storeLiveClass'])->name('live-classes.store');
    Route::match(['put', 'post'], '/live-classes/{id}', [AdminDashboardController::class, 'updateLiveClass'])->name('live-classes.update');
    Route::delete('/live-classes/{id}', [AdminDashboardController::class, 'destroyLiveClass'])->name('live-classes.destroy');

    // Recorded Class CRUD
    Route::post('/recorded-classes', [AdminDashboardController::class, 'storeRecordedClass'])->name('recorded-classes.store');
    Route::match(['put', 'post'], '/recorded-classes/{id}', [AdminDashboardController::class, 'updateRecordedClass'])->name('recorded-classes.update');
    Route::delete('/recorded-classes/{id}', [AdminDashboardController::class, 'destroyRecordedClass'])->name('recorded-classes.destroy');

    // Tasks & Diagnostic Tests Management
    Route::post('/tasks', [AdminDashboardController::class, 'storeTask'])->name('tasks.store');
    Route::post('/tasks/parse-pdf', [AdminDashboardController::class, 'parsePdfQuestions'])->name('tasks.parse-pdf');
    Route::post('/tasks/upload-question-image', [AdminDashboardController::class, 'uploadQuestionImage'])->name('tasks.upload-question-image');
    Route::match(['put', 'post'], '/tasks/{id}', [AdminDashboardController::class, 'updateTask'])->name('tasks.update');
    Route::delete('/tasks/{id}', [AdminDashboardController::class, 'destroyTask'])->name('tasks.destroy');
    Route::post('/tasks/{assignmentId}/grade/{studentId}', [AdminDashboardController::class, 'gradeTask'])->name('tasks.grade');
    Route::get('/tasks/{id}/submissions', [AdminDashboardController::class, 'getTaskSubmissions'])->name('tasks.submissions');
    Route::get('/submissions/{submission}/review', [\App\Http\Controllers\InteractiveAssignmentController::class, 'adminReview'])->name('submissions.review');
    Route::get('/submissions/{submission}/final-pdf', [\App\Http\Controllers\InteractiveAssignmentController::class, 'downloadFinalPdf'])->name('submissions.final-pdf');
    Route::get('/diagnostic/{id}/details', [\App\Http\Controllers\DiagnosticTestController::class, 'getAdminDetails'])->name('diagnostic.details');
    Route::post('/diagnostic/test-rule', [\App\Http\Controllers\DiagnosticTestController::class, 'testGradingRule'])->name('diagnostic.test-rule');


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

    // Teaching Gigs & Masterclasses
    Route::post('/gigs', [AdminDashboardController::class, 'storeGig'])->name('gigs.store');
    Route::delete('/gigs/{id}', [AdminDashboardController::class, 'destroyGig'])->name('gigs.destroy');

    Route::get('/users', fn() => redirect()->to(route('admin.dashboard') . '#users'))->name('users.index');
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
    Route::post('/certificates', [AdminDashboardController::class, 'storeCertificate'])->name('certificates.store');
    Route::delete('/certificates/{id}', [AdminDashboardController::class, 'destroyCertificate'])->name('certificates.destroy');

    // Topic & Subtopic CRUD
    Route::post('/topics', [AdminDashboardController::class, 'storeTopic'])->name('topics.store');
    Route::put('/topics/{id}', [AdminDashboardController::class, 'updateTopic'])->name('topics.update');
    Route::delete('/topics/{id}', [AdminDashboardController::class, 'destroyTopic'])->name('topics.destroy');
    Route::post('/topics/{topicId}/subtopics', [AdminDashboardController::class, 'storeSubtopic'])->name('topics.subtopics.store');
    Route::put('/subtopics/{id}', [AdminDashboardController::class, 'updateSubtopic'])->name('subtopics.update');
    Route::delete('/subtopics/{id}', [AdminDashboardController::class, 'destroySubtopic'])->name('subtopics.destroy');

    // Financial & Payment Management (Fee Structures, Invoices, Payment Verification, Gateway Settings)
    Route::post('/fee-structures', [AdminDashboardController::class, 'storeFeeStructure'])->name('fee-structures.store');
    Route::put('/fee-structures/{id}', [AdminDashboardController::class, 'updateFeeStructure'])->name('fee-structures.update');
    Route::delete('/fee-structures/{id}', [AdminDashboardController::class, 'destroyFeeStructure'])->name('fee-structures.destroy');
    // Package engine — subjects, items, tiers, versioning, duplication
    Route::put('/fee-structures/{id}/subjects', [AdminDashboardController::class, 'updateFeeStructureSubjects'])->name('fee-structures.subjects.update');
    Route::put('/fee-structures/{id}/items', [AdminDashboardController::class, 'updateFeeStructureItems'])->name('fee-structures.items.update');
    Route::put('/fee-structures/{id}/tiers', [AdminDashboardController::class, 'updateFeeStructureTiers'])->name('fee-structures.tiers.update');
    Route::post('/fee-structures/{id}/duplicate', [AdminDashboardController::class, 'duplicateFeeStructure'])->name('fee-structures.duplicate');
    Route::post('/fee-structures/{id}/version', [AdminDashboardController::class, 'createFeeStructureVersion'])->name('fee-structures.version');
    Route::get('/api/fee-structures/{id}/subjects', [AdminDashboardController::class, 'apiFeeStructureSubjects'])->name('api.fee-structures.subjects');
    Route::get('/api/fee-structures/{id}/preview', [AdminDashboardController::class, 'apiFeeStructurePreview'])->name('api.fee-structures.preview');

    Route::post('/settings/finance', [AdminDashboardController::class, 'updateFinanceSettings'])->name('settings.finance');
    Route::post('/settings/finance/reveal', [AdminDashboardController::class, 'revealGatewayCredentials'])->name('settings.finance.reveal');
    Route::post('/settings/billplz/test-connection', [AdminDashboardController::class, 'testBillplzConnection'])->name('settings.billplz.test');
    Route::post('/invoices/{id}/status', function (\Illuminate\Http\Request $request, $id) {
        $request->validate([
            'status'      => 'required|in:draft,issued,partially_paid,paid,overdue,cancelled',
            'amount_paid' => 'nullable|numeric|min:0',
            'notes'       => 'nullable|string|max:500',
        ]);
        $invoice = \App\Models\Invoice::findOrFail($id);
        $invoice->status = $request->status;

        if ($request->status === 'paid') {
            $invoice->amount_paid = $invoice->total_amount;
            app(\App\Services\ReminderService::class)->skipRemainingReminders($invoice);
        } elseif ($request->status === 'partially_paid') {
            if ($request->filled('amount_paid') && (float) $request->amount_paid > 0) {
                $invoice->amount_paid = min((float) $invoice->total_amount, (float) $request->amount_paid);
            } elseif ($invoice->amount_paid <= 0) {
                $invoice->amount_paid = round($invoice->total_amount / 2, 2);
            }
        } elseif ($request->status === 'issued') {
            if ($request->input('reset_amount') == '1' || $request->amount_paid === '0') {
                $invoice->amount_paid = 0;
            }
        }

        if ($request->filled('notes')) {
            $invoice->notes = $request->notes;
        }

        $invoice->save();

        // ── Sync linked Payment record so the Payment Ledger shows the correct amount ──
        // When admin manually overrides status via dropdown, also update the pending
        // payment record so payments.amount reflects what was actually approved.
        if (in_array($request->status, ['paid', 'partially_paid'])) {
            $pendingPayment = $invoice->paymentTransactions()
                ->where('payments.status', \App\Models\Payment::STATUS_PENDING)
                ->first();

            if ($pendingPayment) {
                $amountToApply = (float) $invoice->amount_paid;

                // Correct the amount on the payment record
                $pendingPayment->amount = $amountToApply;

                if ($request->status === 'paid') {
                    // Mark payment successful and generate receipt if missing
                    if (!$pendingPayment->receipt_number) {
                        $pendingPayment->receipt_number = \App\Models\Payment::generateReceiptNumber();
                    }
                    $pendingPayment->status      = \App\Models\Payment::STATUS_SUCCESSFUL;
                    $pendingPayment->verified_by = auth()->id();
                    $pendingPayment->paid_at     = now();
                }
                // For partially_paid: keep pending status but correct the amount

                $pendingPayment->save();

                // Sync the payment_invoices pivot amount_applied
                \App\Models\PaymentInvoice::where('payment_id', $pendingPayment->id)
                    ->where('invoice_id', $invoice->id)
                    ->update(['amount_applied' => $amountToApply]);
            }
        }

        if ($invoice->student) {
            app(\App\Services\StudentAccessRestrictionService::class)->syncStudentStatus($invoice->student);
        }

        return back()->with('success', "Invoice {$invoice->invoice_number} status updated to {$invoice->status_label}.");
    })->name('invoices.status');

    // Quick invoice store
    Route::post('/invoices', function (\Illuminate\Http\Request $request) {
        if ($request->has('items') && is_array($request->items)) {
            return app(\App\Http\Controllers\Admin\BillingController::class)->invoiceStore($request);
        }

        $request->validate([
            'student_id'      => 'required|exists:students,id',
            'title'           => 'required|string|max:255',
            'subtotal'        => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'due_date'        => 'required|date',
        ]);

        $student = \App\Models\Student::with('user')->findOrFail($request->student_id);
        $subtotal = (float) $request->subtotal;
        $discount = (float) ($request->discount_amount ?? 0);
        $total = max(0, $subtotal - $discount);

        $feeType = 'Tuition Fee';
        if ($request->fee_structure_id) {
            $fs = \App\Models\FeeStructure::find($request->fee_structure_id);
            if ($fs) {
                $feeType = $fs->name;
            }
        }
        $monthYear = now()->format('F Y');
        $studentName = $student->user->name ?? "Student #{$student->id}";
        $title = $request->filled('title') ? $request->title : "{$feeType} {$monthYear} {$studentName}";

        $invoice = \App\Models\Invoice::create([
            'student_id'       => $student->id,
            'fee_structure_id' => $request->fee_structure_id ?: null,
            'invoice_number'   => app(\App\Services\InvoiceService::class)->generateInvoiceNumber(),
            'title'            => $title,
            'billing_period'   => $monthYear,
            'subtotal'         => $subtotal,
            'discount_amount'  => $discount,
            'total_amount'     => $total,
            'amount_paid'      => 0,
            'issued_date'      => now()->toDateString(),
            'due_date'         => $request->due_date,
            'status'           => \App\Models\Invoice::STATUS_ISSUED,
        ]);

        \App\Models\InvoiceItem::create([
            'invoice_id'  => $invoice->id,
            'description' => $title,
            'unit_price'  => $subtotal,
            'quantity'    => 1,
            'amount'      => $subtotal,
        ]);

        app(\App\Services\InvoiceService::class)->scheduleReminders($invoice, $student);

        // In-app alert for student & parent
        \App\Services\BillingNotificationService::notifyInvoiceIssued($invoice);

        return back()->with('success', "Invoice {$invoice->invoice_number} created successfully.");
    })->name('invoices.store');

    // Authorize / Record Payment (Supports Full & Partial Payments)
    Route::post('/invoices/{id}/record-payment', function (\Illuminate\Http\Request $request, $id) {
        $invoice = \App\Models\Invoice::with('student.user')->findOrFail($id);

        $request->validate([
            'amount'         => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'string'],
            'reference_no'   => ['nullable', 'string', 'max:100'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ]);

        $amount = (float) $request->amount;
        $admin = auth()->user();
        $receiptNumber = null;

        \Illuminate\Support\Facades\DB::transaction(function () use ($invoice, $amount, $request, $admin, &$receiptNumber) {
            $receiptNumber = \App\Models\Payment::generateReceiptNumber();

            $payerId = $invoice->student->user_id ?? $admin->id;

            $payment = \App\Models\Payment::create([
                'user_id'             => $payerId,
                'reference_no'        => $request->reference_no ?: ('ADM-' . strtoupper(\Illuminate\Support\Str::random(8))),
                'payer_id'            => $payerId,
                'payer_type'          => 'student',
                'student_id'          => $invoice->student_id,
                'description'         => "Payment for Invoice #{$invoice->invoice_number}",
                'amount'              => $amount,
                'payment_method'      => $request->payment_method,
                'status'              => \App\Models\Payment::STATUS_SUCCESSFUL,
                'receipt_number'      => $receiptNumber,
                'verified_by'         => $admin->id,
                'paid_at'             => now(),
                'payment_proof_notes' => $request->notes,
            ]);

            \App\Models\PaymentInvoice::create([
                'payment_id'     => $payment->id,
                'invoice_id'     => $invoice->id,
                'amount_applied' => $amount,
            ]);

            app(\App\Services\InvoiceService::class)->recalculateAmountPaid($invoice);
        });

        $invoice->refresh();
        $typeLabel = $invoice->status === \App\Models\Invoice::STATUS_PAID ? 'full' : 'partial';
        $isPartial = $invoice->status !== \App\Models\Invoice::STATUS_PAID;

        // In-app alert for student & parent
        \App\Services\BillingNotificationService::notifyPaymentRecorded($invoice, $amount, $receiptNumber, $isPartial);

        return back()->with('success', "Authorized {$typeLabel} payment of RM " . number_format($amount, 2) . " for Invoice {$invoice->invoice_number}. Current status: {$invoice->status_label}.");
    })->name('invoices.record-payment');

    // Quick payment verify
    Route::post('/payments/{payment}/verify', function (\Illuminate\Http\Request $request, $payment) {
        $p = \App\Models\Payment::findOrFail($payment);

        if ($request->input('action') === 'reject') {
            app(\App\Services\PaymentService::class)->rejectManualPayment($p, auth()->user(), $request->input('reason', 'Payment could not be verified'));
            return back()->with('success', 'Payment rejected successfully.');
        }

        if ($request->input('action') === 'pending') {
            $p->status = 'pending';
            $p->verified_by = null;
            $p->save();
            return back()->with('success', "Payment {$p->reference_no} has been reset to Pending Review.");
        }

        if ($request->filled('approved_amount') && (float) $request->approved_amount > 0) {
            $approvedAmt = (float) $request->approved_amount;
            $p->amount = $approvedAmt;
            $p->save();
            \App\Models\PaymentInvoice::where('payment_id', $p->id)->update(['amount_applied' => $approvedAmt]);
        }

        app(\App\Services\PaymentService::class)->approveManualPayment($p, auth()->user(), $request->input('notes'));
        return back()->with('success', "Payment {$p->reference_no} of RM " . number_format($p->amount, 2) . " approved and verified successfully.");
    })->name('payments.verify');

    // ── Billing Module ────────────────────────────────────────────────────────
    Route::prefix('billing')->name('billing.')->group(function () {
        Route::get('/', [BillingController::class, 'index'])->name('dashboard');

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
        Route::get('/payments', [BillingController::class, 'payments'])->name('payments');
        Route::get('/payments/{payment}', function () {
            return redirect()->to(route('admin.dashboard') . '#financials');
        })->name('payment.show');
        Route::post('/payments/{payment}/verify', [BillingController::class, 'verifyPayment'])->name('payments.verify');
        Route::post('/payments/{payment}/reject', [BillingController::class, 'rejectPayment'])->name('payments.reject');

        // Subject Fees
        Route::get('/subject-fees', [BillingController::class, 'subjectFees'])->name('subject-fees');
        Route::put('/subject-fees/{subject}', [BillingController::class, 'updateSubjectFee'])->name('subject-fees.update');

        // Email Settings (redirects to unified email management hub)
        Route::get('/email-settings', function () {
            return redirect('/admin/dashboard#emailsettings');
        })->name('email-settings');
        Route::post('/email-settings', [BillingController::class, 'updateEmailSettings'])->name('email-settings.update');
    });

    // ── Communication & Enterprise Email Management ──
    Route::prefix('email-management')->name('email.')->group(function () {
        Route::post('/audience-preview', [\App\Http\Controllers\Admin\EmailManagementController::class, 'previewAudience'])->name('audience.preview');
        Route::post('/template-preview', [\App\Http\Controllers\Admin\EmailManagementController::class, 'previewTemplate'])->name('template.preview');
        Route::post('/templates', [\App\Http\Controllers\Admin\EmailManagementController::class, 'storeTemplate'])->name('templates.store');
        Route::put('/templates/{template}', [\App\Http\Controllers\Admin\EmailManagementController::class, 'updateTemplate'])->name('templates.update');
        Route::post('/templates/{template}/duplicate', [\App\Http\Controllers\Admin\EmailManagementController::class, 'duplicateTemplate'])->name('templates.duplicate');
        Route::post('/templates/{template}/toggle', [\App\Http\Controllers\Admin\EmailManagementController::class, 'toggleTemplate'])->name('templates.toggle');
        Route::delete('/templates/{template}', [\App\Http\Controllers\Admin\EmailManagementController::class, 'archiveTemplate'])->name('templates.destroy');
        Route::post('/send-test', [\App\Http\Controllers\Admin\EmailManagementController::class, 'sendTestEmail'])->name('send-test');
        Route::post('/campaigns', [\App\Http\Controllers\Admin\EmailManagementController::class, 'storeCampaign'])->name('campaigns.store');
        Route::post('/campaigns/{campaign}/cancel', [\App\Http\Controllers\Admin\EmailManagementController::class, 'cancelCampaign'])->name('campaigns.cancel');
        Route::post('/automations/{automation}/toggle', [\App\Http\Controllers\Admin\EmailManagementController::class, 'toggleAutomation'])->name('automations.toggle');
        Route::put('/automations/{automation}', [\App\Http\Controllers\Admin\EmailManagementController::class, 'updateAutomation'])->name('automations.update');
        Route::post('/automations/{automation}/test', [\App\Http\Controllers\Admin\EmailManagementController::class, 'testAutomation'])->name('automations.test');
        Route::post('/logs/{log}/retry', [\App\Http\Controllers\Admin\EmailManagementController::class, 'retryLog'])->name('logs.retry');
        Route::get('/logs/export-csv', [\App\Http\Controllers\Admin\EmailManagementController::class, 'exportLogsCsv'])->name('logs.export-csv');
        Route::post('/settings', [\App\Http\Controllers\Admin\EmailManagementController::class, 'updateSettings'])->name('settings.update');
    });

    // ── Admin Master Timetable & Academic Calendar Management ──
    Route::get('/timetable', [\App\Http\Controllers\TimetableController::class, 'adminIndex'])->name('timetable');
    Route::post('/classes/quick-create', [\App\Http\Controllers\TimetableController::class, 'adminQuickCreateSession'])->name('classes.quick-create');
    Route::post('/classes/{id}/reschedule-direct', [\App\Http\Controllers\TimetableController::class, 'adminReschedule'])->name('classes.reschedule-direct');
    Route::post('/academic-events', [\App\Http\Controllers\TimetableController::class, 'storeAcademicEvent'])->name('academic-events.store');
    Route::put('/academic-events/{id}', [\App\Http\Controllers\TimetableController::class, 'updateAcademicEvent'])->name('academic-events.update');
    Route::delete('/academic-events/{id}', [\App\Http\Controllers\TimetableController::class, 'destroyAcademicEvent'])->name('academic-events.destroy');
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
// TIMETABLE & CALENDAR API (Authenticated)
// ──────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/api/timetable/events', [\App\Http\Controllers\TimetableController::class, 'getEventsJson'])->name('timetable.events');
    Route::get('/timetable/export-ics/{id}', [\App\Http\Controllers\TimetableController::class, 'exportIcs'])->name('timetable.export-ics');
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

    // Read receipts (blue tick)
    Route::post('/conversations/{conversation}/read', [ChatController::class, 'markRead'])->name('conversations.read');

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

