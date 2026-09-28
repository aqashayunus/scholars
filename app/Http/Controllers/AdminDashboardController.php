<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LiveClass;
use App\Models\Message;
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
use App\Services\PdfDiagnosticParser;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMail;
use App\Mail\EngagementBlastMail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminDashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        // ── 1. Core Platform Counts ──────────────────────────────────────────
        $totalUsers        = User::count();
        $totalStudents     = User::where('role', 'student')->count();
        $totalTeachers     = User::where('role', 'teacher')->count();
        $totalParents      = User::where('role', 'parent')->count();
        $totalAdmins       = User::where('role', 'admin')->count();

        $totalCourses      = Course::count();
        $publishedCourses  = Course::where('status', 'published')->count();
        $draftCourses      = Course::where('status', 'draft')->count();
        $archivedCourses   = Course::where('status', 'archived')->count();

        $activeEnrollments = Enrollment::count();
        $totalSubjects     = Subject::count();
        $totalLiveClasses  = LiveClass::count();
        $totalGigs         = TeachingGig::count();
        $totalRecordings   = RecordedClass::count();
        $totalMasteries    = StudentTopicMastery::count();

        // Timetable Metrics
        $now = Carbon::now();
        $startOfWeek = $now->copy()->startOfWeek();
        $endOfWeek = $now->copy()->endOfWeek();
        $totalClassesThisWeek = LiveClass::whereBetween('scheduled_at', [$startOfWeek, $endOfWeek])->count();
        $classesToday         = LiveClass::whereDate('scheduled_at', $now->toDateString())->count();
        $rescheduledCount     = LiveClass::where('is_rescheduled', true)->count();
        $academicEventsCount  = AcademicEvent::count();

        // ── 2. Financial Metrics (pulled from DB settings) ───────────────────
        $teacherCutPct        = (float) Setting::get('teacher_cut_pct', 65);
        $currencySymbol       = Setting::get('currency_symbol', 'RM');

        $paidStatuses         = ['paid', 'successful', 'approved', 'completed', 'success'];
        $totalRevenue         = (float) Payment::whereIn('status', $paidStatuses)->sum('amount');
        $totalInvoicesCount   = Payment::count();
        $paidInvoicesCount    = Payment::whereIn('status', $paidStatuses)->count();
        $pendingInvoicesCount = Payment::whereIn('status', ['pending', 'unpaid', 'under_review'])->count();
        $teacherPayoutEst     = $totalRevenue * ($teacherCutPct / 100);
        $netPlatformMargin    = $totalRevenue * ((100 - $teacherCutPct) / 100);
        $avgTeacherRating     = round((float) Teacher::avg('rating') ?: 4.85, 2);

        // ── 3. Full Database Collections ─────────────────────────────────────
        $allPayments          = Payment::with(['user', 'course.subject', 'student.user', 'payer', 'invoice'])->latest('id')->get();
        $allFeeStructures     = \App\Models\FeeStructure::with('student.user')->latest('id')->get();
        $allInvoices          = \App\Models\Invoice::with(['student.user', 'student.parents', 'student.parent', 'feeStructure', 'items', 'reminders', 'paymentTransactions.payer', 'paymentTransactions.user'])->latest('id')->get();
        $allStudentsList      = Student::whereHas('user', function ($q) {
            $q->where('role', 'student');
        })->with([
            'user',
            'parents',
            'parent',
            'enrollments.course.teacher.user',
            'enrollments.course.subject',
            'invoices',
        ])->latest('id')->get();

        $restrictionService = app(\App\Services\StudentAccessRestrictionService::class);
        foreach ($allStudentsList as $st) {
            $st->is_restricted = $restrictionService->isStudentRestricted($st);
            $st->payment_status = $restrictionService->getStudentPaymentStatus($st);
            $st->restriction_details = $st->is_restricted ? $restrictionService->getRestrictionDetails($st) : null;
        }

        foreach ($allInvoices as $inv) {
            $isOverdue = $inv->status === 'overdue' || ($inv->isPastDue() && $inv->requiresPayment());
            $inv->is_overdue = $isOverdue;
            if ($isOverdue && $inv->due_date) {
                $inv->days_overdue = max(1, (int) now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($inv->due_date)->startOfDay()));
                $inv->restriction_date = \Carbon\Carbon::parse($inv->due_date)->addDay();
                $inv->restriction_status = 'Restricted';
            } else {
                $inv->days_overdue = 0;
                $inv->restriction_date = null;
                $inv->restriction_status = 'Normal';
            }
        }
        $restrictedStudentsCount = $allStudentsList->where('is_restricted', true)->count();

        $allCertificates      = \App\Models\Certificate::with(['student.user', 'course.subject'])->latest('id')->get();
        $financialLogs        = \App\Models\FinancialAuditLog::with('user')->latest('id')->take(30)->get();
        $pendingProofPayments     = Payment::where('payment_method', 'bank_transfer')->where('status', 'pending')->with(['student.user', 'payer', 'invoice'])->get();
        $allVerificationPayments  = Payment::where(function($q) {
            $q->where('payment_method', 'bank_transfer')->orWhereNotNull('proof_file_path');
        })->with(['student.user', 'payer', 'invoice', 'invoices'])->latest('id')->get();
        $allEnrollments       = Enrollment::with(['student.user', 'course.subject', 'course.teacher.user'])->latest('id')->get();
        $allTeachers          = Teacher::with(['user', 'courses.enrollments', 'courses.subject', 'subjects'])->get();
        $allCourses           = Course::with(['teacher.user', 'subject.topics.subtopics'])->withCount('enrollments')->get();
        $allUsers             = User::latest('id')->get();
        $allGigs              = TeachingGig::with(['teacher.user', 'subject'])->latest('id')->get();
        $allMasteries         = StudentTopicMastery::with(['student.user', 'subject'])->latest('id')->take(40)->get();
        $allSubjects          = Subject::with(['topics.subtopics'])->orderBy('order', 'asc')->orderBy('id', 'asc')->get();
        $allTopics            = SubjectTopic::with(['subject', 'subtopics'])->orderBy('subject_id')->orderBy('chapter_number')->get();
        $allLiveClasses       = LiveClass::with(['course.subject', 'course.teacher.user', 'teacher.user'])->latest('id')->get();
        $allRecordedClasses   = RecordedClass::with(['course.subject', 'subject'])->latest('id')->get();
        $allQuizzes           = Quiz::with(['course.subject', 'questions'])->latest('id')->get();
        $teacherApplications  = TeacherApplication::with('user')->latest('id')->get();

        // ── 3c. Student Diagnostic Test Insights ─────────────────────────────
        $diagnosticTests = Assignment::where('type', 'Diagnostic Test')->latest('id')->get();
        $selectedDiagId  = $request->input('diagnostic_id');
        $activeDiagnostic = $selectedDiagId ? $diagnosticTests->firstWhere('id', $selectedDiagId) : $diagnosticTests->first();
        $activeDiagId    = $activeDiagnostic?->id;

        $allDiagnosticStudents = Student::where('status', 'active')
            ->whereHas('user', function($qu) {
                $qu->where('is_active', true);
            })
            ->with(['user', 'studentAssignments' => function($q) use ($activeDiagId) {
                if ($activeDiagId) {
                    $q->where('assignment_id', $activeDiagId);
                } else {
                    $q->whereHas('assignment', fn($qa) => $qa->where('type', 'Diagnostic Test'));
                }
                $q->with('assignment');
            }])->get()->map(function($st) {
            $sa = $st->studentAssignments->first();
            $isCompleted = $sa && in_array($sa->status, ['submitted', 'graded', 'completed']);
            $score = $isCompleted ? (int)$sa->score : null;
            $tahap = $isCompleted ? ($sa->tahap_kepandaian ?: ($score >= 80 ? 'Cemerlang' : ($score >= 60 ? 'Baik' : ($score >= 40 ? 'Sederhana' : 'Perlu Bimbingan')))) : 'Belum Diuji';
            $timeTaken = $sa?->time_taken_seconds ?: 0;
            $minutes = floor($timeTaken / 60);
            $seconds = $timeTaken % 60;
            $formattedTime = $timeTaken > 0 ? ($minutes > 0 ? "{$minutes}m {$seconds}s" : "{$seconds}s") : '-';

            return (object) [
                'id'                    => $st->id,
                'student_id'            => $st->id,
                'student_assignment_id' => $sa?->id,
                'name'                  => $st->user?->name ?? 'Student #' . $st->id,
                'email'                 => $st->user?->email ?? '-',
                'level'                 => $st->level ?: 'All Levels',
                'is_completed'          => $isCompleted,
                'status'                => $isCompleted ? 'Completed' : 'Pending',
                'score'                 => $score,
                'tahap_kepandaian'      => $tahap,
                'formatted_time'        => $formattedTime,
                'submitted_at'          => $sa?->submitted_at,
                'badge_class'           => match($tahap) {
                    'Cemerlang'       => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'Baik'            => 'bg-blue-50 text-blue-700 border-blue-200',
                    'Sederhana'       => 'bg-amber-50 text-amber-700 border-amber-200',
                    'Perlu Bimbingan' => 'bg-rose-50 text-rose-700 border-rose-200',
                    default           => 'bg-gray-100 text-gray-700 border-gray-200',
                },
                'answers_breakdown'     => $sa?->answers_json ?: [],
            ];
        });

        $diagnosticTotalStudents       = $allDiagnosticStudents->count();
        $diagnosticCompletedCount       = $allDiagnosticStudents->where('is_completed', true)->count();
        $diagnosticPendingCount         = $diagnosticTotalStudents - $diagnosticCompletedCount;
        $diagnosticCompletionRate       = $diagnosticTotalStudents > 0 ? round(($diagnosticCompletedCount / $diagnosticTotalStudents) * 100) : 0;
        $diagnosticAvgScore             = $diagnosticCompletedCount > 0 ? round($allDiagnosticStudents->where('is_completed', true)->avg('score')) : 0;
        $diagnosticCemerlangCount       = $allDiagnosticStudents->where('tahap_kepandaian', 'Cemerlang')->count();
        $diagnosticBaikCount            = $allDiagnosticStudents->where('tahap_kepandaian', 'Baik')->count();
        $diagnosticSederhanaCount       = $allDiagnosticStudents->where('tahap_kepandaian', 'Sederhana')->count();
        $diagnosticPerluBimbinganCount  = $allDiagnosticStudents->where('tahap_kepandaian', 'Perlu Bimbingan')->count();

        // ── 3b. Non-Active & Engagement Roster ──────────────────────────────
        // Criteria:
        //   (A) Admin-deactivated accounts: users.is_active = false
        //   (B) Inactive students from students table (status != 'active')
        //   (C) Payment-restricted students: those with overdue invoices
        //   (D) Inactive / deactivated teachers: application_status in (inactive, deactivated)

        // (A) Admin-deactivated accounts (any role)
        $deactivatedUserIds = User::where('is_active', false)->pluck('id');

        // (B) Inactive students from students table
        $inactiveStudentUserIds = Student::where('status', '!=', 'active')
            ->pluck('user_id')
            ->filter();

        // (C) Payment-restricted students — reuse restriction service logic
        $restrictedStudentUserIds = $allStudentsList
            ->filter(fn ($s) => $s->is_restricted === true)
            ->pluck('user_id')
            ->filter();

        // (D) Inactive / deactivated teachers
        $inactiveTeacherUserIds = Teacher::whereIn('application_status', ['inactive', 'deactivated'])
            ->pluck('user_id')
            ->filter();

        // Merge all non-active user IDs (union, no duplicates)
        $nonActiveUserIds = $deactivatedUserIds
            ->merge($inactiveStudentUserIds)
            ->merge($restrictedStudentUserIds)
            ->merge($inactiveTeacherUserIds)
            ->unique()
            ->values();

        $nonActiveUsers = User::with(['studentProfile', 'teacherProfile'])
            ->whereIn('id', $nonActiveUserIds)
            ->latest('id')
            ->get()
            ->map(function ($u) use ($deactivatedUserIds, $inactiveStudentUserIds, $restrictedStudentUserIds, $inactiveTeacherUserIds) {
                // Determine inactivity reason(s)
                $reasons = [];
                if ($u->is_active === false || $deactivatedUserIds->contains($u->id)) {
                    $reasons[] = 'Account Deactivated';
                }
                if ($inactiveStudentUserIds->contains($u->id)) {
                    $reasons[] = 'Student Inactive';
                }
                if ($restrictedStudentUserIds->contains($u->id)) {
                    $reasons[] = 'Payment Restricted';
                }
                if ($inactiveTeacherUserIds->contains($u->id)) {
                    $reasons[] = 'Teacher Deactivated';
                }
                $u->inactivity_reason  = implode(', ', $reasons) ?: 'Inactive';
                $u->inactivity_reasons = $reasons;
                return $u;
            });

        $nonActiveStudentsCount = $nonActiveUsers->where('role', 'student')->count();
        $nonActiveTeachersCount = $nonActiveUsers->where('role', 'teacher')->count();
        $nonActiveParentsCount  = $nonActiveUsers->where('role', 'parent')->count();
        $nonActiveUsersCount    = $nonActiveUsers->count();


        // ── 4. Charts & Analytics ─────────────────────────────────────────────
        $subjectEnrollments = Course::withCount('enrollments')
            ->with('subject')
            ->orderByDesc('enrollments_count')
            ->get();

        $subjectLabels = $subjectEnrollments->pluck('title')->take(6)->toArray();
        $subjectData   = $subjectEnrollments->pluck('enrollments_count')->take(6)->toArray();

        // Real revenue breakdown by streams: Course Fees, Enrolled Tuition, Assessments, Materials, Mastery Subscriptions
        $streamTotals = [
            'Course Fees'           => 0.0,
            'Enrolled Tuition'      => 0.0,
            'Assessments'           => 0.0,
            'Materials'             => 0.0,
            'Mastery Subscriptions' => 0.0,
        ];

        $paidPaymentsList = Payment::whereIn('status', $paidStatuses)
            ->with(['course', 'invoices.items'])
            ->get();

        foreach ($paidPaymentsList as $payment) {
            $amt = (float) $payment->amount;
            if ($amt <= 0) continue;

            $categorized = false;
            $linkedInvoices = $payment->invoices;

            if ($linkedInvoices && $linkedInvoices->isNotEmpty()) {
                $totalAllocated = $linkedInvoices->sum(fn($i) => (float) ($i->pivot->amount_applied ?? $i->total_amount));
                foreach ($linkedInvoices as $inv) {
                    $invAllocated = (float) ($inv->pivot->amount_applied ?? $inv->total_amount);
                    $invRatio = $totalAllocated > 0 ? ($invAllocated / $totalAllocated) : 1;
                    $invShare = $amt * $invRatio;
                    $invItems = $inv->items;

                    if ($invItems && $invItems->isNotEmpty()) {
                        $invSubtotal = (float) ($inv->subtotal > 0 ? $inv->subtotal : $invItems->sum('amount'));
                        foreach ($invItems as $item) {
                            $itemRatio = $invSubtotal > 0 ? ((float) $item->amount / $invSubtotal) : (1 / $invItems->count());
                            $itemAmt = $invShare * $itemRatio;
                            $desc = strtolower($item->description ?? '');

                            if (str_contains($desc, 'course') || $inv->course_id) {
                                $streamTotals['Course Fees'] += $itemAmt;
                            } elseif (str_contains($desc, 'assessment') || str_contains($desc, 'exam') || str_contains($desc, 'quiz') || str_contains($desc, 'test')) {
                                $streamTotals['Assessments'] += $itemAmt;
                            } elseif (str_contains($desc, 'material') || str_contains($desc, 'book') || str_contains($desc, 'notes') || str_contains($desc, 'workbook')) {
                                $streamTotals['Materials'] += $itemAmt;
                            } elseif (str_contains($desc, 'subscription') || str_contains($desc, 'mastery')) {
                                $streamTotals['Mastery Subscriptions'] += $itemAmt;
                            } else {
                                $streamTotals['Enrolled Tuition'] += $itemAmt;
                            }
                            $categorized = true;
                        }
                    } else {
                        $desc = strtolower(($inv->title ?? '') . ' ' . ($inv->description ?? ''));
                        if (str_contains($desc, 'course') || $inv->course_id) {
                            $streamTotals['Course Fees'] += $invShare;
                        } elseif (str_contains($desc, 'assessment') || str_contains($desc, 'exam')) {
                            $streamTotals['Assessments'] += $invShare;
                        } elseif (str_contains($desc, 'material') || str_contains($desc, 'book')) {
                            $streamTotals['Materials'] += $invShare;
                        } elseif (str_contains($desc, 'subscription') || str_contains($desc, 'mastery')) {
                            $streamTotals['Mastery Subscriptions'] += $invShare;
                        } else {
                            $streamTotals['Enrolled Tuition'] += $invShare;
                        }
                        $categorized = true;
                    }
                }
            }

            if (!$categorized) {
                $desc = strtolower(($payment->description ?? '') . ' ' . ($payment->course->title ?? ''));
                if ($payment->course_id || str_contains($desc, 'course')) {
                    $streamTotals['Course Fees'] += $amt;
                } elseif (str_contains($desc, 'assessment') || str_contains($desc, 'exam') || str_contains($desc, 'quiz')) {
                    $streamTotals['Assessments'] += $amt;
                } elseif (str_contains($desc, 'material') || str_contains($desc, 'book')) {
                    $streamTotals['Materials'] += $amt;
                } elseif (str_contains($desc, 'subscription') || str_contains($desc, 'mastery')) {
                    $streamTotals['Mastery Subscriptions'] += $amt;
                } else {
                    $streamTotals['Enrolled Tuition'] += $amt;
                }
            }
        }

        if (array_sum($streamTotals) <= 0 && $totalRevenue > 0) {
            $streamTotals['Enrolled Tuition'] = $totalRevenue;
        }

        $revenueLabels = array_keys($streamTotals);
        $revenueData   = array_map(fn($v) => round($v, 2), array_values($streamTotals));

        // ── 5. Audit Logs ─────────────────────────────────────────────────────
        $payLogs = Payment::with(['user', 'course'])->latest('id')->take(10)->get()->map(function ($p) {
            return [
                'id'        => 'PAY-' . str_pad($p->id, 5, '0', STR_PAD_LEFT),
                'user'      => $p->user->name ?? 'User #' . $p->user_id,
                'role'      => ucfirst($p->user->role ?? 'Student'),
                'action'    => 'Payment of RM ' . number_format($p->amount, 2) . ' for ' . ($p->course->title ?? $p->description ?? 'Course Fee'),
                'module'    => 'Financials',
                'status'    => strtoupper($p->status ?? 'PAID'),
                'date'      => $p->paid_at ? $p->paid_at->format('d M Y, H:i') : ($p->created_at ? $p->created_at->format('d M Y, H:i') : 'Recent'),
                'timestamp' => $p->paid_at ? $p->paid_at->timestamp : ($p->created_at ? $p->created_at->timestamp : 0),
            ];
        });

        $enrollLogs = Enrollment::with(['student.user', 'course'])->latest('id')->take(10)->get()->map(function ($e) {
            return [
                'id'        => 'ENR-' . str_pad($e->id, 5, '0', STR_PAD_LEFT),
                'user'      => $e->student?->user?->name ?? 'Student #' . $e->student_id,
                'role'      => 'Student',
                'action'    => 'Enrolled into ' . ($e->course->title ?? 'Course #' . $e->course_id) . ' (' . ($e->progress_pct ?? 0) . '% Progress)',
                'module'    => 'Enrollments',
                'status'    => 'ACTIVE',
                'date'      => $e->created_at ? $e->created_at->format('d M Y, H:i') : 'Recent',
                'timestamp' => $e->created_at ? $e->created_at->timestamp : 0,
            ];
        });

        $msgLogs = Message::with(['sender', 'receiver'])->latest('id')->take(10)->get()->map(function ($m) {
            return [
                'id'        => 'MSG-' . str_pad($m->id, 5, '0', STR_PAD_LEFT),
                'user'      => $m->sender->name ?? 'User #' . $m->sender_id,
                'role'      => ucfirst($m->sender->role ?? 'User'),
                'action'    => 'Sent live communication message to ' . ($m->receiver->name ?? 'User #' . $m->receiver_id),
                'module'    => 'Messenger',
                'status'    => 'SENT',
                'date'      => $m->created_at ? $m->created_at->format('d M Y, H:i') : 'Recent',
                'timestamp' => $m->created_at ? $m->created_at->timestamp : 0,
            ];
        });

        $userLogs = User::latest('id')->take(10)->get()->map(function ($u) {
            return [
                'id'        => 'USR-' . str_pad($u->id, 5, '0', STR_PAD_LEFT),
                'user'      => $u->name,
                'role'      => ucfirst($u->role),
                'action'    => 'Registered account profile (' . $u->email . ')',
                'module'    => 'Auth / Profile',
                'status'    => 'VERIFIED',
                'date'      => $u->created_at ? $u->created_at->format('d M Y, H:i') : 'Recent',
                'timestamp' => $u->created_at ? $u->created_at->timestamp : 0,
            ];
        });

        $recentLogs = $payLogs->concat($enrollLogs)->concat($msgLogs)->concat($userLogs)
            ->sortByDesc('timestamp')
            ->values()
            ->take(30);

        $popupSettings = [
            'enabled' => Setting::get('parent_greeting_enabled', '1'),
            'title'   => Setting::get('parent_greeting_title', 'Welcome to Olympia Scholars'),
            'body'    => Setting::get('parent_greeting_body', "We are thrilled to partner with you in nurturing your child's academic growth, curiosity, and confidence. Here is everything you need to stay connected with their learning journey."),
            'image'   => Setting::get('parent_greeting_image', 'images/welcome-parent-greeting.jpg'),
        ];
        $allBlogPosts = \App\Models\BlogPost::with('author')->latest('created_at')->get();
        $allTasks = Assignment::with(['course.subject', 'results.student.user'])
            ->latest('id')
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers', 'totalStudents', 'totalTeachers', 'totalParents', 'totalAdmins',
            'totalCourses', 'publishedCourses', 'draftCourses', 'archivedCourses',
            'activeEnrollments', 'totalSubjects', 'totalLiveClasses', 'totalGigs',
            'totalRecordings', 'totalMasteries',
            'totalRevenue', 'totalInvoicesCount', 'paidInvoicesCount', 'pendingInvoicesCount',
            'teacherPayoutEst', 'netPlatformMargin', 'avgTeacherRating',
            'teacherCutPct', 'currencySymbol',
            'allPayments', 'allFeeStructures', 'allInvoices', 'allStudentsList', 'allCertificates', 'financialLogs', 'pendingProofPayments', 'allVerificationPayments',
            'allEnrollments', 'allTeachers', 'allCourses', 'allUsers', 'allGigs', 'allMasteries',
            'allSubjects', 'allTopics', 'allLiveClasses', 'allRecordedClasses', 'teacherApplications', 'allQuizzes', 'allTasks',
            'revenueLabels', 'revenueData',
            'subjectEnrollments', 'subjectLabels', 'subjectData',
            'recentLogs', 'restrictedStudentsCount',
            'nonActiveUsers', 'nonActiveUsersCount', 'nonActiveStudentsCount', 'nonActiveTeachersCount', 'nonActiveParentsCount',
            'totalClassesThisWeek', 'classesToday', 'rescheduledCount', 'academicEventsCount',
            'popupSettings', 'allBlogPosts',
            'allDiagnosticStudents', 'diagnosticTotalStudents', 'diagnosticCompletedCount', 'diagnosticPendingCount',
            'diagnosticCompletionRate', 'diagnosticAvgScore', 'diagnosticCemerlangCount', 'diagnosticBaikCount',
            'diagnosticSederhanaCount', 'diagnosticPerluBimbinganCount',
            'diagnosticTests', 'activeDiagnostic'
        ));
    }

    // ── USER CRUD ─────────────────────────────────────────────────────────────

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|max:255|unique:users,email',
            'role'           => 'required|in:admin,teacher,student,parent',
            'phone'          => 'nullable|string|max:30',
            'ic_number'      => 'required|string|min:6',
            'parent_user_id' => 'nullable|exists:users,id',
            'courses'        => 'nullable|array',
            'courses.*'      => 'exists:courses,id',
        ]);

        // Auto-generate password: 2 characters of Name (uppercase) + last 6 digits of IC
        $cleanName    = preg_replace('/[^A-Za-z]/', '', $validated['name']);
        $firstTwoName = strtoupper(substr($cleanName, 0, 2));

        $cleanIc      = preg_replace('/[^0-9]/', '', $validated['ic_number']);
        $lastSixIc    = substr($cleanIc, -6);

        $passwordText = $firstTwoName . $lastSixIc;

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'role'     => $validated['role'],
            'phone'    => $validated['phone'] ?? null,
            'password' => Hash::make($passwordText),
        ]);

        if ($user->role === 'student') {
            $student = Student::create([
                'user_id'        => $user->id,
                'parent_user_id' => $validated['parent_user_id'] ?? null,
                'level'          => 'SPM',
                'total_points'   => 0,
            ]);

            // Enroll in selected courses
            if (!empty($validated['courses'])) {
                foreach ($validated['courses'] as $courseId) {
                    Enrollment::create([
                        'student_id'        => $student->id,
                        'course_id'         => $courseId,
                        'status'            => 'active',
                        'progress_pct'      => 0,
                        'completed_lessons' => 0,
                    ]);
                }

                try {
                    app(\App\Services\InvoiceService::class)->generateForStudent($student);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::info("Admin auto-invoice note for student #{$student->id}: " . $e->getMessage());
                }
            }
        } elseif ($user->role === 'teacher') {
            Teacher::firstOrCreate(['user_id' => $user->id], ['specialization' => 'General Academic', 'rating' => 5.0, 'experience_years' => 1]);
        }

        // Send Welcome Email containing credentials details
        try {
            Mail::to($user->email)->send(new WelcomeMail($user, $passwordText));
        } catch (\Exception $e) {
            report($e);
        }

        return redirect()->to(route('admin.dashboard') . '#users')
            ->with('success', "User '{$user->name}' created successfully. Welcome email dispatched to {$user->email}.");
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email,' . $user->id,
            'role'     => 'required|in:admin,teacher,student,parent',
            'phone'    => 'nullable|string|max:30',
            'password' => 'nullable|string|min:6',
        ]);

        $user->name  = $validated['name'];
        $user->email = $validated['email'];
        $user->role  = $validated['role'];
        $user->phone = $validated['phone'] ?? null;

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();

        if ($user->role === 'student' && !$user->studentProfile) {
            Student::create(['user_id' => $user->id, 'level' => 'SPM', 'total_points' => 0]);
        } elseif ($user->role === 'teacher' && !$user->teacherProfile) {
            Teacher::create(['user_id' => $user->id, 'specialization' => 'General Academic', 'rating' => 5.0, 'experience_years' => 1]);
        }

        try {
            \App\Models\UserNotification::create([
                'user_id' => $user->id,
                'type'    => 'profile',
                'title'   => 'ACCOUNT PROFILE UPDATED',
                'message' => "Your profile details have been updated.",
                'read_at' => null,
            ]);
        } catch (\Throwable $e) {}

        return redirect()->to(route('admin.dashboard') . '#users')
            ->with('success', "User '{$user->name}' updated successfully.");
    }

    public function destroyUser($id)
    {
        if (auth()->id() == $id) {
            return redirect()->to(route('admin.dashboard') . '#users')
                ->with('error', 'Cannot delete your own active administrator account.');
        }

        $user = User::findOrFail($id);
        $userName = $user->name;

        if ($user->studentProfile) $user->studentProfile()->delete();
        if ($user->teacherProfile) $user->teacherProfile()->delete();
        $user->delete();

        return redirect()->to(route('admin.dashboard') . '#users')
            ->with('success', "User '{$userName}' deleted successfully.");
    }

    public function resetUserPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);
        // Use DB-configured default temp password instead of hardcoded 'secret123'
        $newPassword = $request->input('password', Setting::get('default_temp_password', 'Temp@1234'));

        $user->password = Hash::make($newPassword);
        $user->save();

        try {
            \App\Models\UserNotification::create([
                'user_id' => $user->id,
                'type'    => 'security',
                'title'   => 'PASSWORD UPDATED',
                'message' => "Your account password has been updated. Please ensure your credentials remain secure.",
                'read_at' => null,
            ]);
        } catch (\Throwable $e) {}

        return redirect()->to(route('admin.dashboard') . '#users')
            ->with('success', "Password for '{$user->name}' has been reset to '{$newPassword}'.");
    }

    // ── COURSE CRUD ───────────────────────────────────────────────────────────

    public function storeCourse(Request $request)
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'subject_id'   => 'required|exists:subjects,id',
            'teacher_id'   => 'nullable|exists:teachers,id',
            'level'        => 'required|string|max:50',
            'monthly_fee'  => 'required|numeric|min:0',
            'status'       => 'required|in:published,draft,archived',
            'total_lessons'=> 'nullable|integer|min:1',
            'description'  => 'nullable|string',
        ]);

        $monthlyFee = $validated['monthly_fee'];
        unset($validated['monthly_fee']);

        if (empty($validated['teacher_id'])) {
            $subject = \App\Models\Subject::find($validated['subject_id']);
            $validated['teacher_id'] = $subject?->teachers?->first()?->id ?? \App\Models\Teacher::first()?->id ?? null;
        }

        $course = Course::create($validated);

        // Write fee to the subject — this is the authoritative billing source
        \App\Models\Subject::where('id', $course->subject_id)->update(['monthly_fee' => $monthlyFee]);

        try {
            if ($course->teacher_id) {
                $teacher = Teacher::find($course->teacher_id);
                if ($teacher?->user_id) {
                    \App\Models\UserNotification::create([
                        'user_id' => $teacher->user_id,
                        'type'    => 'course',
                        'title'   => 'NEW COURSE ASSIGNED',
                        'message' => "The course '{$course->title}' ({$course->level}) has been assigned to your educator profile.",
                        'read_at' => null,
                    ]);
                }
            }
        } catch (\Throwable $e) {}

        return redirect()->to(route('admin.dashboard') . '#courses-catalog')
            ->with('success', "Course '{$course->title}' created. Subject fee set to " . Setting::get('currency_symbol', 'RM') . ' ' . number_format($monthlyFee, 2) . "/month.");
    }

    public function updateCourse(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'subject_id'   => 'required|exists:subjects,id',
            'teacher_id'   => 'nullable|exists:teachers,id',
            'level'        => 'required|string|max:50',
            'monthly_fee'  => 'required|numeric|min:0',
            'status'       => 'required|in:published,draft,archived',
            'total_lessons'=> 'nullable|integer|min:1',
            'description'  => 'nullable|string',
        ]);

        $monthlyFee = $validated['monthly_fee'];
        unset($validated['monthly_fee']);

        if (!isset($validated['teacher_id']) || $validated['teacher_id'] === null) {
            unset($validated['teacher_id']);
        }

        $course->update($validated);

        // Write fee to the subject — this is the authoritative billing source
        \App\Models\Subject::where('id', $course->subject_id)->update(['monthly_fee' => $monthlyFee]);

        try {
            if ($course->teacher_id) {
                $teacher = Teacher::find($course->teacher_id);
                if ($teacher?->user_id) {
                    \App\Models\UserNotification::create([
                        'user_id' => $teacher->user_id,
                        'type'    => 'course',
                        'title'   => 'COURSE DETAILS UPDATED',
                        'message' => "Details for course '{$course->title}' have been updated.",
                        'read_at' => null,
                    ]);
                }
            }
            foreach ($course->enrollments as $enrollment) {
                if ($enrollment->student?->user_id) {
                    \App\Models\UserNotification::create([
                        'user_id' => $enrollment->student->user_id,
                        'type'    => 'course',
                        'title'   => 'COURSE DETAILS UPDATED',
                        'message' => "Details for your enrolled course '{$course->title}' have been updated.",
                        'read_at' => null,
                    ]);
                }
            }
        } catch (\Throwable $e) {}

        return redirect()->to(route('admin.dashboard') . '#courses-catalog')
            ->with('success', "Course '{$course->title}' updated. Subject fee: " . Setting::get('currency_symbol', 'RM') . ' ' . number_format($monthlyFee, 2) . "/month.");
    }

    public function destroyCourse($id)
    {
        $course = Course::findOrFail($id);
        $title = $course->title;
        $course->delete();

        return redirect()->to(route('admin.dashboard') . '#courses-catalog')
            ->with('success', "Course '{$title}' removed from catalog.");
    }

    // ── SUBJECT CRUD ─────────────────────────────────────────────────────────

    public function storeSubject(Request $request)
    {
        // Support bulk/multiple subjects in a single form
        if ($request->has('subjects') && is_array($request->subjects)) {
            $createdCount = 0;
            DB::transaction(function () use ($request, &$createdCount) {
                $maxOrder = Subject::max('order') ?? 0;
                foreach ($request->subjects as $item) {
                    if (empty($item['name'])) continue;
                    
                    $educationType = $item['education_type'] ?? 'secondary';
                    $stage = $item['stage'] ?? ($educationType === 'primary' ? 'Primary' : 'Upper Secondary');
                    $formYear = $item['form_year'] ?? ($educationType === 'primary' ? 'Year 4' : 'Form 4');
                    $category = $item['category'] ?? 'Core';

                    $slugBase = !empty($item['slug']) ? $item['slug'] : ($item['name'] . ' ' . $formYear);
                    $slug = \Illuminate\Support\Str::slug($slugBase);
                    $originalSlug = $slug;
                    $counter = 1;
                    while (Subject::where('slug', $slug)->exists()) {
                        $slug = $originalSlug . '-' . $counter++;
                    }

                    Subject::create([
                        'name'           => $item['name'],
                        'slug'           => $slug,
                        'code'           => !empty($item['code']) ? $item['code'] : 'KPM-' . strtoupper(\Illuminate\Support\Str::random(6)),
                        'education_type' => $educationType,
                        'stage'          => $stage,
                        'form_year'      => $formYear,
                        'category'       => $category,
                        'stream_package' => $item['stream_package'] ?? null,
                        'level'          => $item['level'] ?? ($educationType === 'primary' ? 'Primary' : 'SPM'),
                        'color'          => $item['color'] ?? '#3b82f6',
                        'icon'           => $item['icon'] ?? 'academic-cap',
                        'description'    => $item['description'] ?? null,
                        'order'          => !empty($item['order']) ? (int)$item['order'] : (++$maxOrder),
                        'is_active'      => isset($item['is_active']) ? (bool)$item['is_active'] : true,
                    ]);
                    $createdCount++;
                }
            });

            return redirect()->to(route('admin.dashboard') . '#courses-subjects')
                ->with('success', "{$createdCount} KPM curriculum subject(s) created successfully.");
        }

        // Fallback for single subject
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'slug'           => 'nullable|string|max:255',
            'code'           => 'nullable|string|max:50',
            'education_type' => 'required|in:primary,secondary',
            'stage'          => 'required|string|max:50',
            'form_year'      => 'required|string|max:50',
            'category'       => 'required|string|max:50',
            'stream_package' => 'nullable|string|max:50',
            'description'    => 'nullable|string',
            'level'          => 'required|string|max:50',
            'color'          => 'required|string|max:20',
            'icon'           => 'nullable|string|max:50',
            'order'          => 'nullable|integer',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['name'] . ' ' . $validated['form_year']);
        }
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (Subject::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter++;
        }

        if (empty($validated['code'])) {
            $validated['code'] = 'KPM-' . strtoupper(\Illuminate\Support\Str::random(6));
        }

        $maxOrder = Subject::max('order') ?? 0;
        $validated['order'] = !empty($validated['order']) ? (int)$validated['order'] : ($maxOrder + 1);
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $subject = Subject::create($validated);

        return redirect()->to(route('admin.dashboard') . '#courses-subjects')
            ->with('success', "Subject '{$subject->name}' ({$subject->code}) created successfully.");
    }

    public function updateSubject(Request $request, $id)
    {
        $subject = Subject::findOrFail($id);

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'slug'           => 'required|string|max:255|unique:subjects,slug,' . $subject->id,
            'code'           => 'nullable|string|max:50',
            'education_type' => 'required|in:kindergarten,primary,secondary',
            'stage'          => 'required|string|max:50',
            'form_year'      => 'required|string|max:50',
            'category'       => 'required|string|max:50',
            'stream_package' => 'nullable|string|max:50',
            'description'    => 'nullable|string',
            'level'          => 'required|string|max:50',
            'color'          => 'required|string|max:20',
            'icon'           => 'nullable|string|max:50',
            'order'          => 'nullable|integer',
        ]);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $subject->update($validated);

        return redirect()->to(route('admin.dashboard') . '#courses-subjects')
            ->with('success', "Subject '{$subject->name}' updated successfully.");
    }

    public function destroySubject($id)
    {
        $subject = Subject::findOrFail($id);
        $name = $subject->name;
        $subject->delete();

        return redirect()->to(route('admin.dashboard') . '#courses-subjects')
            ->with('success', "Subject '{$name}' deleted successfully.");
    }

    // ── TOPIC & SUBTOPIC CRUD (CURRICULUM SYLLABUS BUILDER) ───────────────────

    public function storeTopic(Request $request)
    {
        $subjectId = $request->input('subject_id');
        if (!$subjectId || !Subject::where('id', $subjectId)->exists()) {
            return redirect()->to(route('admin.dashboard') . '#courses-topics')
                ->with('error', 'Please select a valid subject.');
        }

        // Support bulk/multiple topics in a single form
        if ($request->has('topics') && is_array($request->topics)) {
            $createdCount = 0;
            DB::transaction(function () use ($request, $subjectId, &$createdCount) {
                $maxChapter = SubjectTopic::where('subject_id', $subjectId)->max('chapter_number') ?? 0;
                
                foreach ($request->topics as $item) {
                    if (empty($item['title'])) continue;

                    $chNumber = !empty($item['chapter_number']) ? (int)$item['chapter_number'] : (++$maxChapter);
                    if ($chNumber > $maxChapter) {
                        $maxChapter = $chNumber;
                    }

                    SubjectTopic::create([
                        'subject_id'     => $subjectId,
                        'chapter_number' => $chNumber,
                        'title'          => $item['title'],
                        'description'    => $item['description'] ?? null,
                    ]);
                    $createdCount++;
                }
            });

            return redirect()->to(route('admin.dashboard') . '#courses-topics')
                ->with('success', "{$createdCount} curriculum topic(s) added successfully.");
        }

        // Fallback for single topic
        $validated = $request->validate([
            'subject_id'     => 'required|exists:subjects,id',
            'chapter_number' => 'nullable|integer|min:1',
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
        ]);

        if (empty($validated['chapter_number'])) {
            $validated['chapter_number'] = (SubjectTopic::where('subject_id', $validated['subject_id'])->max('chapter_number') ?? 0) + 1;
        }

        $topic = SubjectTopic::create($validated);

        return redirect()->to(route('admin.dashboard') . '#courses-topics')
            ->with('success', "Curriculum Topic 'Chapter {$topic->chapter_number}: {$topic->title}' added successfully.");
    }

    public function updateTopic(Request $request, $id)
    {
        $topic = SubjectTopic::findOrFail($id);

        $validated = $request->validate([
            'subject_id'     => 'required|exists:subjects,id',
            'chapter_number' => 'required|integer|min:1',
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
        ]);

        $topic->update($validated);

        return redirect()->to(route('admin.dashboard') . '#courses-topics')
            ->with('success', "Curriculum Topic '{$topic->title}' updated successfully.");
    }

    public function destroyTopic($id)
    {
        $topic = SubjectTopic::findOrFail($id);
        $title = $topic->title;
        $topic->delete();

        return redirect()->to(route('admin.dashboard') . '#courses-topics')
            ->with('success', "Curriculum Topic '{$title}' and its subtopics deleted successfully.");
    }

    public function storeSubtopic(Request $request, $topicId)
    {
        $topic = SubjectTopic::findOrFail($topicId);

        // Support bulk/multiple subtopics in a single form
        if ($request->has('subtopics') && is_array($request->subtopics)) {
            $createdCount = 0;
            DB::transaction(function () use ($request, $topic, &$createdCount) {
                $maxOrder = $topic->subtopics()->max('order') ?? 0;

                foreach ($request->subtopics as $item) {
                    if (empty($item['title'])) continue;

                    $order = !empty($item['order']) ? (int)$item['order'] : (++$maxOrder);
                    if ($order > $maxOrder) {
                        $maxOrder = $order;
                    }

                    $topic->subtopics()->create([
                        'title'          => $item['title'],
                        'default_status' => $item['default_status'] ?? 'Not Started',
                        'order'          => $order,
                    ]);
                    $createdCount++;
                }
            });

            return redirect()->to(route('admin.dashboard') . '#courses-topics')
                ->with('success', "{$createdCount} checkpoint(s) added under '{$topic->title}'.");
        }

        // Fallback for single subtopic
        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'default_status' => 'required|in:Mastered,In Progress,Needs Practice,Not Started',
            'order'          => 'nullable|integer|min:1',
        ]);

        $maxOrder = $topic->subtopics()->max('order') ?: 0;
        $validated['order'] = $validated['order'] ?? ($maxOrder + 1);

        $subtopic = $topic->subtopics()->create($validated);

        return redirect()->to(route('admin.dashboard') . '#courses-topics')
            ->with('success', "Subtopic '{$subtopic->title}' added under {$topic->title}.");
    }

    public function updateSubtopic(Request $request, $id)
    {
        $subtopic = SubjectSubtopic::findOrFail($id);

        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'default_status' => 'required|in:Mastered,In Progress,Needs Practice,Not Started',
            'order'          => 'required|integer|min:1',
        ]);

        $subtopic->update($validated);

        return redirect()->to(route('admin.dashboard') . '#courses-topics')
            ->with('success', "Subtopic '{$subtopic->title}' updated successfully.");
    }

    public function destroySubtopic($id)
    {
        $subtopic = SubjectSubtopic::findOrFail($id);
        $title = $subtopic->title;
        $subtopic->delete();

        return redirect()->to(route('admin.dashboard') . '#courses-topics')
            ->with('success', "Subtopic '{$title}' deleted successfully.");
    }

    // ── LIVE CLASS CRUD ──────────────────────────────────────────────────────

    public function storeLiveClass(Request $request)
    {
        $validated = $request->validate([
            'course_id'        => 'required|exists:courses,id',
            'teacher_id'       => 'nullable|exists:teachers,id',
            'education_level'  => 'nullable|string|max:100',
            'target_age'       => 'nullable|string|max:100',
            'topic'            => 'required|string|max:255',
            'subtopic'         => 'nullable|string|max:255',
            'title'            => 'required|string|max:255',
            'scheduled_at'     => 'required|date',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'meeting_url'      => 'nullable|url|max:255',
        ]);

        if (empty($validated['teacher_id'])) {
            $courseObj = Course::find($validated['course_id']);
            if ($courseObj && $courseObj->teacher_id) {
                $validated['teacher_id'] = $courseObj->teacher_id;
            }
        }

        if (empty($validated['target_age']) && !empty($validated['education_level'])) {
            $validated['target_age'] = $validated['education_level'];
        } elseif (empty($validated['education_level']) && !empty($validated['target_age'])) {
            $validated['education_level'] = $validated['target_age'];
        }

        $validated['duration_minutes'] = (int) ($validated['duration_minutes'] ?? 60);
        $start = \Carbon\Carbon::parse($validated['scheduled_at']);
        $end   = (clone $start)->addMinutes($validated['duration_minutes']);
        $now   = now();
        if ($now->lt($start)) {
            $validated['status'] = 'upcoming';
        } elseif ($now->between($start, $end)) {
            $validated['status'] = 'live';
        } else {
            $validated['status'] = 'completed';
        }

        $liveClass = LiveClass::create($validated);

        try {
            $course = $liveClass->course;
            $teacher = $liveClass->teacher ?? $course?->teacher;
            if ($course) {
                $timeStr = \Carbon\Carbon::parse($liveClass->scheduled_at)->format('d M Y, h:i A');
                if ($teacher?->user_id) {
                    \App\Models\UserNotification::create([
                        'user_id' => $teacher->user_id,
                        'type'    => 'live_class',
                        'title'   => 'LIVE CLASS SCHEDULED',
                        'message' => "A live session '{$liveClass->title}' for {$course->title} has been scheduled for {$timeStr}.",
                        'read_at' => null,
                    ]);
                }
                foreach ($course->enrollments as $enrollment) {
                    if ($enrollment->student?->user_id) {
                        \App\Models\UserNotification::create([
                            'user_id' => $enrollment->student->user_id,
                            'type'    => 'live_class',
                            'title'   => 'NEW LIVE CLASS SCHEDULED',
                            'message' => "A new live session '{$liveClass->title}' for {$course->title} is scheduled for {$timeStr}.",
                            'read_at' => null,
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {}

        return redirect()->to(route('admin.dashboard') . '#courses-live')
            ->with('success', "Live Class '{$liveClass->title}' scheduled successfully.");
    }

    public function updateLiveClass(Request $request, $id)
    {
        $liveClass = LiveClass::findOrFail($id);

        $validated = $request->validate([
            'course_id'        => 'required|exists:courses,id',
            'teacher_id'       => 'nullable|exists:teachers,id',
            'education_level'  => 'nullable|string|max:100',
            'target_age'       => 'nullable|string|max:100',
            'topic'            => 'required|string|max:255',
            'subtopic'         => 'nullable|string|max:255',
            'title'            => 'required|string|max:255',
            'scheduled_at'     => 'required|date',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'meeting_url'      => 'nullable|url|max:255',
        ]);

        if (empty($validated['teacher_id'])) {
            $courseObj = Course::find($validated['course_id']);
            if ($courseObj && $courseObj->teacher_id) {
                $validated['teacher_id'] = $courseObj->teacher_id;
            }
        }

        if (empty($validated['target_age']) && !empty($validated['education_level'])) {
            $validated['target_age'] = $validated['education_level'];
        } elseif (empty($validated['education_level']) && !empty($validated['target_age'])) {
            $validated['education_level'] = $validated['target_age'];
        }

        $validated['duration_minutes'] = (int) ($validated['duration_minutes'] ?? 60);
        $start = \Carbon\Carbon::parse($validated['scheduled_at']);
        $end   = (clone $start)->addMinutes($validated['duration_minutes']);
        $now   = now();
        if ($now->lt($start)) {
            $validated['status'] = 'upcoming';
        } elseif ($now->between($start, $end)) {
            $validated['status'] = 'live';
        } else {
            $validated['status'] = 'completed';
        }

        $liveClass->update($validated);

        try {
            $course = $liveClass->course;
            $teacher = $liveClass->teacher ?? $course?->teacher;
            if ($course) {
                $timeStr = \Carbon\Carbon::parse($liveClass->scheduled_at)->format('d M Y, h:i A');
                if ($teacher?->user_id) {
                    \App\Models\UserNotification::create([
                        'user_id' => $teacher->user_id,
                        'type'    => 'live_class',
                        'title'   => 'LIVE CLASS UPDATED',
                        'message' => "The live session '{$liveClass->title}' for {$course->title} has been updated ({$timeStr}).",
                        'read_at' => null,
                    ]);
                }
                foreach ($course->enrollments as $enrollment) {
                    if ($enrollment->student?->user_id) {
                        \App\Models\UserNotification::create([
                            'user_id' => $enrollment->student->user_id,
                            'type'    => 'live_class',
                            'title'   => 'LIVE CLASS UPDATED',
                            'message' => "The live session '{$liveClass->title}' for {$course->title} has been updated. Scheduled: {$timeStr}.",
                            'read_at' => null,
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {}

        return redirect()->to(route('admin.dashboard') . '#courses-live')
            ->with('success', "Live Class '{$liveClass->title}' updated successfully.");
    }

    public function destroyLiveClass($id)
    {
        $liveClass = LiveClass::findOrFail($id);
        $title = $liveClass->title;
        $liveClass->delete();

        return redirect()->to(route('admin.dashboard') . '#courses-live')
            ->with('success', "Live Class '{$title}' removed.");
    }

    // ── RECORDED CLASS CRUD ──────────────────────────────────────────────────

    public function storeRecordedClass(Request $request)
    {
        $validated = $request->validate([
            'course_id'        => 'nullable|exists:courses,id',
            'subject_id'       => 'nullable|exists:subjects,id',
            'title'            => 'required|string|max:255',
            'topic_name'       => 'required|string|max:255',
            'video_url'        => 'required|url|max:255',
            'thumbnail'        => 'nullable|string|max:255',
            'duration_minutes' => 'required|integer|min:1|max:480',
            'views_count'      => 'nullable|integer|min:0',
            'summary'          => 'nullable|string',
        ]);

        $validated['views_count'] = $validated['views_count'] ?? 0;

        $recordedClass = RecordedClass::create($validated);

        try {
            if ($recordedClass->course) {
                foreach ($recordedClass->course->enrollments as $enrollment) {
                    if ($enrollment->student?->user_id) {
                        \App\Models\UserNotification::create([
                            'user_id' => $enrollment->student->user_id,
                            'type'    => 'course',
                            'title'   => 'NEW RECORDED LESSON AVAILABLE',
                            'message' => "A new recorded lesson '{$recordedClass->title}' ({$recordedClass->topic_name}) is now available in your course library.",
                            'read_at' => null,
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {}

        return redirect()->to(route('admin.dashboard') . '#courses-recorded')
            ->with('success', "Recorded Class '{$recordedClass->title}' added to vault.");
    }

    public function updateRecordedClass(Request $request, $id)
    {
        $recordedClass = RecordedClass::findOrFail($id);

        $validated = $request->validate([
            'course_id'        => 'nullable|exists:courses,id',
            'subject_id'       => 'nullable|exists:subjects,id',
            'title'            => 'required|string|max:255',
            'topic_name'       => 'required|string|max:255',
            'video_url'        => 'required|url|max:255',
            'thumbnail'        => 'nullable|string|max:255',
            'duration_minutes' => 'required|integer|min:1|max:480',
            'views_count'      => 'nullable|integer|min:0',
            'summary'          => 'nullable|string',
        ]);

        $recordedClass->update($validated);

        return redirect()->to(route('admin.dashboard') . '#courses-recorded')
            ->with('success', "Recorded Class '{$recordedClass->title}' updated successfully.");
    }

    public function destroyRecordedClass($id)
    {
        $recordedClass = RecordedClass::findOrFail($id);
        $title = $recordedClass->title;
        $recordedClass->delete();

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

    public function approveTeacherApplication(Request $request, $id)
    {
        $application = TeacherApplication::findOrFail($id);

        DB::transaction(function () use ($application, $request) {
            // 1. Find or create user account for teacher
            $user = User::where('email', $application->email)->first();
            $tempPassword = Setting::get('default_temp_password', 'AsterEdu2026!');

            if (!$user) {
                $user = User::create([
                    'name'     => $application->full_name,
                    'email'    => $application->email,
                    'phone'    => $application->phone,
                    'role'     => 'teacher',
                    'password' => Hash::make($tempPassword),
                ]);
            } else {
                $user->update(['role' => 'teacher']);
            }

            // 2. Create or activate Teacher profile
            Teacher::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'application_status' => 'active',
                    'qualification'      => $application->qualification,
                    'experience_years'   => $application->experience_years,
                    'bio'                => $application->preferred_subjects ? "Specialist educator in: {$application->preferred_subjects}" : 'Certified Aster Edu Educator',
                ]
            );

            // 3. Update application status
            $application->update([
                'user_id'     => $user->id,
                'status'      => 'approved',
                'admin_notes' => $request->input('admin_notes', 'Application approved.'),
                'approved_at' => now(),
            ]);

            try {
                \App\Models\UserNotification::create([
                    'user_id' => $user->id,
                    'type'    => 'application',
                    'title'   => 'TEACHER APPLICATION APPROVED',
                    'message' => "Congratulations! Your educator application has been approved. Your educator account is now active.",
                    'read_at' => null,
                ]);
            } catch (\Throwable $e) {}
        });

        return back()->with('success', "Teacher application for '{$application->full_name}' has been approved and their educator account is now active.");
    }

    public function rejectTeacherApplication(Request $request, $id)
    {
        $application = TeacherApplication::findOrFail($id);

        $application->update([
            'status'      => 'rejected',
            'admin_notes' => $request->input('admin_notes', 'Application not approved at this time.'),
        ]);

        if ($application->user_id) {
            try {
                \App\Models\UserNotification::create([
                    'user_id' => $application->user_id,
                    'type'    => 'application',
                    'title'   => 'APPLICATION STATUS UPDATE',
                    'message' => "Your educator application has been reviewed. Status: Not approved at this time.",
                    'read_at' => null,
                ]);
            } catch (\Throwable $e) {}
        }

        return back()->with('success', "Teacher application for '{$application->full_name}' has been marked as rejected.");
    }

    // ── TEACHING GIGS ─────────────────────────────────────────────────────────

    public function storeGig(Request $request)
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'subject_id'       => 'required|exists:subjects,id',
            'scheduled_at'     => 'required|date',
            'duration_minutes' => 'required|integer|min:15|max:240',
            'payout_amount'    => 'required|numeric|min:0',
            'surge_bonus'      => 'nullable|numeric|min:0',
            'gig_type'         => 'nullable|string|max:50',
            'notes'            => 'nullable|string',
        ]);

        $validated['status'] = 'open';
        $validated['surge_bonus'] = $validated['surge_bonus'] ?? 0;

        $gig = TeachingGig::create($validated);

        return redirect()->to(route('admin.dashboard') . '#records')
            ->with('success', "Teaching Gig '{$gig->title}' published with payout " . Setting::get('currency_symbol', 'RM') . ' ' . number_format($gig->payout_amount, 2) . ".");
    }

    public function destroyGig($id)
    {
        $gig = TeachingGig::findOrFail($id);
        $title = $gig->title;
        $gig->delete();

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
                    strtoupper($pay->status ?? 'PAID'),
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
    public function storeCertificate(Request $request)
    {
        $validated = $request->validate([
            'student_id'   => 'required|exists:students,id',
            'course_id'    => 'nullable|exists:courses,id',
            'title'        => 'required|string|max:255',
            'subtitle'     => 'nullable|string|max:255',
            'cert_number'  => 'nullable|string|max:100|unique:certificates,cert_number',
            'issued_at'    => 'nullable|date',
            'issued_by'    => 'nullable|string|max:255',
            'description'  => 'nullable|string',
            'file'         => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $filePath = null;
        $fileName = null;
        if ($request->hasFile('file')) {
            $uploadedFile = $request->file('file');
            $fileName     = $uploadedFile->getClientOriginalName();
            $filePath     = $uploadedFile->store('certificates', 'public');
        }

        $certNumber = !empty($validated['cert_number']) 
            ? $validated['cert_number'] 
            : ('OSC-' . date('Y') . '-' . strtoupper(substr(md5(uniqid()), 0, 6)));

        \App\Models\Certificate::create([
            'student_id'   => $validated['student_id'],
            'course_id'    => $validated['course_id'] ?? null,
            'title'        => $validated['title'],
            'subtitle'     => $validated['subtitle'] ?? null,
            'cert_number'  => $certNumber,
            'file_path'    => $filePath,
            'file_name'    => $fileName,
            'issued_at'    => $validated['issued_at'] ?? now(),
            'issued_by'    => $validated['issued_by'] ?? (auth()->user()->name ?? 'Academy Administration'),
            'description'  => $validated['description'] ?? null,
        ]);

        $student = \App\Models\Student::find($validated['student_id']);
        if ($student && $student->user_id) {
            \App\Models\UserNotification::create([
                'user_id' => $student->user_id,
                'type'    => 'certificate',
                'title'   => 'NEW CERTIFICATE AWARDED',
                'message' => "Congratulations! You have been awarded an official certificate: '{$validated['title']}'. View it in your Achievements tab.",
                'read_at' => null,
            ]);
        }
        if ($student) {
            $parents = \App\Services\BillingNotificationService::getStudentParents($student);
            $studentName = $student->user->name ?? 'Your student';
            foreach ($parents as $parent) {
                \App\Models\UserNotification::create([
                    'user_id' => $parent->id,
                    'type'    => 'certificate',
                    'title'   => 'STUDENT CERTIFICATE AWARDED',
                    'message' => "Congratulations! {$studentName} has been awarded an official certificate: '{$validated['title']}'.",
                    'read_at' => null,
                ]);
            }
        }

        return back()->with('success', 'Official Certificate successfully awarded and issued to student!');
    }

    /**
     * Delete an issued certificate.
     */
    public function destroyCertificate($id)
    {
        $certificate = \App\Models\Certificate::findOrFail($id);
        if ($certificate->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($certificate->file_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($certificate->file_path);
        }
        $certificate->delete();

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
        $invoice = \App\Models\Invoice::findOrFail($id);
        $oldStatus = $invoice->status;
        $status = $request->validate(['status' => 'required|in:outstanding,pending_verification,paid,overdue,cancelled'])['status'];
        
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
                'status'         => 'paid',
                'receipt_number' => $receiptNo,
                'verified_by'    => auth()->id(),
                'paid_at'        => $payment->paid_at ?? now(),
            ]);

            if ($payment->invoice_id) {
                $payment->invoice->update(['status' => 'paid']);
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
                'status'      => 'rejected',
                'verified_by' => auth()->id(),
            ]);

            if ($payment->invoice_id) {
                $payment->invoice->update(['status' => 'outstanding']);
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
        if (!in_array($invoice->status, [Invoice::STATUS_PAID, Invoice::STATUS_CANCELLED])) {
            if ($newDueDate->lt($today)) {
                $invoice->status = Invoice::STATUS_OVERDUE;
            } else {
                // If it was overdue, lift overdue back to partially_paid or issued
                if ((float)$invoice->amount_paid > 0) {
                    $invoice->status = Invoice::STATUS_PARTIALLY_PAID;
                } else {
                    $invoice->status = Invoice::STATUS_ISSUED;
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
        if ($invoice->student && in_array($invoice->status, Invoice::UNPAID_STATUSES)) {
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
                if ($payment->status === \App\Models\Payment::STATUS_PENDING) {
                    $payment->update(['status' => \App\Models\Payment::STATUS_FAILED, 'payment_proof_notes' => $adminNote]);
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

    public function updateTeacherSubjects(Request $request, $id)
    {
        $teacher = Teacher::findOrFail($id);
        $request->validate([
            'subject_ids'   => 'nullable|array',
            'subject_ids.*' => 'exists:subjects,id',
        ]);
        $teacher->subjects()->sync($request->input('subject_ids', []));
        $name = $teacher->user->name ?? 'Teacher #' . $id;

        try {
            if ($teacher->user_id) {
                \App\Models\UserNotification::create([
                    'user_id' => $teacher->user_id,
                    'type'    => 'subject',
                    'title'   => 'TEACHING SUBJECTS UPDATED',
                    'message' => "Your assigned teaching subjects have been updated.",
                    'read_at' => null,
                ]);
            }
        } catch (\Throwable $e) {}

        return redirect()->to(route('admin.dashboard') . '#teachers')
            ->with('success', "Subject assignments updated for {$name}.");
    }

    public function storeTeacherClass(Request $request, $id)
    {
        $teacher = Teacher::findOrFail($id);
        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'subject_id'    => 'required|exists:subjects,id',
            'level'         => 'required|string|max:50',
            'schedule'      => 'nullable|string|max:255',
            'total_lessons' => 'nullable|integer|min:1',
            'status'        => 'required|in:published,draft,archived',
            'price'         => 'required|numeric|min:0',
            'description'   => 'nullable|string',
        ]);

        $course = Course::create([
            'teacher_id'    => $teacher->id,
            'subject_id'    => $validated['subject_id'],
            'title'         => $validated['title'],
            'level'         => $validated['level'],
            'schedule'      => $validated['schedule'] ?? null,
            'total_lessons' => $validated['total_lessons'] ?? 12,
            'status'        => $validated['status'],
            'price'         => $validated['price'],
            'description'   => $validated['description'] ?? null,
        ]);

        // Link teacher to this subject in pivot if not already
        if (!$teacher->subjects()->where('subject_id', $validated['subject_id'])->exists()) {
            $teacher->subjects()->attach($validated['subject_id']);
        }

        try {
            if ($teacher->user_id) {
                \App\Models\UserNotification::create([
                    'user_id' => $teacher->user_id,
                    'type'    => 'course',
                    'title'   => 'NEW CLASS ASSIGNED',
                    'message' => "A new class '{$course->title}' ({$course->level}) has been assigned to your educator profile.",
                    'read_at' => null,
                ]);
            }
        } catch (\Throwable $e) {}

        $name = $teacher->user->name ?? 'Teacher #' . $id;
        return redirect()->to(route('admin.dashboard') . '#teachers')
            ->with('success', "Class '{$course->title}' created and assigned to {$name}.");
    }

    public function toggleStudentStatus($id)
    {
        $student = Student::findOrFail($id);
        $newStatus = ($student->status === 'inactive') ? 'active' : 'inactive';
        $student->update(['status' => $newStatus]);
        if ($student->user) {
            $student->user->update(['is_active' => ($newStatus === 'active')]);
        }

        $name = $student->user->name ?? 'Student #' . $id;
        $label = ucfirst($newStatus);

        try {
            if ($student->user_id) {
                \App\Models\UserNotification::create([
                    'user_id' => $student->user_id,
                    'type'    => 'account',
                    'title'   => 'ACCOUNT STATUS UPDATED',
                    'message' => "Your student portal account status has been updated to {$label}.",
                    'read_at' => null,
                ]);
            }
            $parents = \App\Services\BillingNotificationService::getStudentParents($student);
            foreach ($parents as $parent) {
                \App\Models\UserNotification::create([
                    'user_id' => $parent->id,
                    'type'    => 'account',
                    'title'   => 'STUDENT ACCOUNT STATUS UPDATED',
                    'message' => "The account status for student {$name} has been updated to {$label}.",
                    'read_at' => null,
                ]);
            }
        } catch (\Throwable $e) {}

        try {
            FinancialAuditLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'student_status_changed',
                'target_type' => Student::class,
                'target_id'   => $student->id,
                'ip_address'  => request()->ip(),
                'details'     => "Student '{$name}' (ID: {$student->id}) status set to {$newStatus} by Admin.",
            ]);
        } catch (\Throwable $e) {
            // Ignore audit fail silently to prevent blocking admin action
        }

        return redirect()->to(route('admin.dashboard') . '#student')
            ->with('success', "Student '{$name}' status updated to {$label}.");
    }

    public function toggleTeacherStatus($id)
    {
        $teacher = Teacher::findOrFail($id);
        $newStatus = ($teacher->application_status === 'inactive' || $teacher->application_status === 'deactivated') ? 'active' : 'inactive';
        
        $updateData = ['application_status' => $newStatus];
        if ($newStatus === 'inactive') {
            $updateData['is_online'] = false;
        }
        $teacher->update($updateData);
        if ($teacher->user) {
            $teacher->user->update(['is_active' => ($newStatus === 'active')]);
        }

        $name = $teacher->user->name ?? 'Teacher #' . $id;
        $label = ucfirst($newStatus);

        try {
            if ($teacher->user_id) {
                \App\Models\UserNotification::create([
                    'user_id' => $teacher->user_id,
                    'type'    => 'account',
                    'title'   => 'ACCOUNT STATUS UPDATED',
                    'message' => "Your educator account status has been updated to {$label}.",
                    'read_at' => null,
                ]);
            }
        } catch (\Throwable $e) {}

        try {
            FinancialAuditLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'teacher_status_changed',
                'target_type' => Teacher::class,
                'target_id'   => $teacher->id,
                'ip_address'  => request()->ip(),
                'details'     => "Teacher '{$name}' (ID: {$teacher->id}) status set to {$newStatus} by Admin for company record archiving.",
            ]);
        } catch (\Throwable $e) {
            // Ignore audit fail silently to prevent blocking admin action
        }

        return redirect()->to(route('admin.dashboard') . '#teachers')
            ->with('success', "Teacher '{$name}' status updated to {$label} (Company record preserved).");
    }

    // ── OUTREACH & ENGAGEMENT HUB ─────────────────────────────────────────────

    /**
     * Toggle a user's is_active flag (admin account deactivation / reactivation).
     * Also syncs associated student.status or teacher.application_status.
     */
    public function toggleUserIsActive($id)
    {
        $user = User::with(['studentProfile', 'teacherProfile'])->findOrFail($id);

        // Do not allow admin to deactivate themselves
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot deactivate your own account.');
        }

        $newActive = !$user->is_active;
        $user->update(['is_active' => $newActive]);

        // Sync child profiles
        if ($user->studentProfile) {
            $user->studentProfile->update(['status' => $newActive ? 'active' : 'inactive']);
        }
        if ($user->teacherProfile) {
            $user->teacherProfile->update(['application_status' => $newActive ? 'active' : 'inactive']);
        }

        $label = $newActive ? 'Reactivated' : 'Deactivated';

        try {
            \App\Models\UserNotification::create([
                'user_id' => $user->id,
                'type'    => 'account',
                'title'   => 'ACCOUNT STATUS UPDATED',
                'message' => "Your account has been {$label} by the administrator.",
                'read_at' => null,
            ]);
        } catch (\Throwable $e) {}

        try {
            FinancialAuditLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'user_is_active_toggled',
                'target_type' => User::class,
                'target_id'   => $user->id,
                'ip_address'  => request()->ip(),
                'details'     => "User '{$user->name}' (ID: {$user->id}, Role: {$user->role}) {$label} by Admin.",
            ]);
        } catch (\Throwable $e) {}

        return redirect()->to(route('admin.dashboard') . '#records')
            ->with('success', "User '{$user->name}' has been {$label}.");
    }

    /**
     * Send a personalised engagement blast email to selected non-active users.
     */
    public function blastEngagementEmail(Request $request)
    {
        $request->validate([
            'blast_subject'  => 'required|string|max:255',
            'blast_body'     => 'required|string|max:5000',
            'blast_audience' => 'required|string|in:all_inactive,students_only,parents_only,teachers_only',
            'blast_cta_link' => 'nullable|url|max:500',
            'blast_cta_label'=> 'nullable|string|max:100',
        ]);

        // Determine the base recipient query (non-active users)
        $restrictedStudentUserIds = Student::whereHas('invoices', function ($q) {
            $q->whereIn('status', ['overdue', 'outstanding'])->whereDate('due_date', '<', now());
        })->pluck('user_id')->filter();

        $inactiveTeacherUserIds = Teacher::whereIn('application_status', ['inactive', 'deactivated'])
            ->pluck('user_id')->filter();

        $deactivatedUserIds = User::where('is_active', false)->pluck('id');

        $nonActiveUserIds = $deactivatedUserIds
            ->merge($restrictedStudentUserIds)
            ->merge($inactiveTeacherUserIds)
            ->unique()->values();

        $query = User::whereIn('id', $nonActiveUserIds);

        // Filter by audience
        switch ($request->blast_audience) {
            case 'students_only': $query->where('role', 'student'); break;
            case 'parents_only':  $query->where('role', 'parent'); break;
            case 'teachers_only': $query->where('role', 'teacher'); break;
        }

        $recipients = $query->get();
        $sent = 0;
        $failed = 0;

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email)->send(
                    new EngagementBlastMail(
                        $recipient,
                        $request->blast_subject,
                        $request->blast_body,
                        $request->blast_cta_link ?? '',
                        $request->blast_cta_label ?? 'Return to Platform',
                    )
                );
                $sent++;
            } catch (\Throwable $e) {
                $failed++;
            }
        }

        try {
            FinancialAuditLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'engagement_blast_sent',
                'target_type' => User::class,
                'target_id'   => auth()->id(),
                'ip_address'  => request()->ip(),
                'details'     => "Engagement blast '{$request->blast_subject}' sent to {$sent} recipient(s) (audience: {$request->blast_audience}). Failed: {$failed}.",
            ]);
        } catch (\Throwable $e) {}

        $msg = "Engagement email sent to {$sent} recipient(s).";
        if ($failed > 0) $msg .= " ({$failed} failed — check mail configuration.)";

        return redirect()->to(route('admin.dashboard') . '#records')->with('success', $msg);
    }

    /**
     * Export non-active users as a CSV file for external outreach/CRM tools.
     */
    public function exportInactiveUsersCsv(): StreamedResponse
    {
        $restrictedStudentUserIds = Student::whereHas('invoices', function ($q) {
            $q->whereIn('status', ['overdue', 'outstanding'])->whereDate('due_date', '<', now());
        })->pluck('user_id')->filter();

        $inactiveTeacherUserIds = Teacher::whereIn('application_status', ['inactive', 'deactivated'])
            ->pluck('user_id')->filter();

        $deactivatedUserIds = User::where('is_active', false)->pluck('id');

        $nonActiveUserIds = $deactivatedUserIds
            ->merge($restrictedStudentUserIds)
            ->merge($inactiveTeacherUserIds)
            ->unique()->values();

        $users = User::whereIn('id', $nonActiveUserIds)->latest('id')->get();

        return response()->streamDownload(function () use ($users, $restrictedStudentUserIds, $inactiveTeacherUserIds) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['User ID', 'Full Name', 'Role', 'Email', 'Phone', 'Inactivity Reason', 'Registration Date']);
            foreach ($users as $u) {
                $reasons = [];
                if ($u->is_active === false) $reasons[] = 'Account Deactivated';
                if ($restrictedStudentUserIds->contains($u->id)) $reasons[] = 'Payment Restricted';
                if ($inactiveTeacherUserIds->contains($u->id)) $reasons[] = 'Teacher Deactivated';
                fputcsv($handle, [
                    '#' . $u->id,
                    $u->name,
                    ucfirst($u->role),
                    $u->email,
                    $u->phone ?? '',
                    implode('; ', $reasons) ?: 'Deactivated',
                    $u->created_at ? $u->created_at->format('d M Y') : '',
                ]);
            }
            fclose($handle);
        }, 'inactive-users-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}

