<?php

namespace App\Services\Admin;

use App\Enums\InvoiceStatus;
use App\Enums\StudentAssignmentStatus;
use App\Enums\StudentStatus;
use App\Models\AcademicEvent;
use App\Models\Assignment;
use App\Models\BlogPost;
use App\Models\ChatMessage;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\FeeStructure;
use App\Models\FinancialAuditLog;
use App\Models\Invoice;
use App\Models\LiveClass;
use App\Models\Payment;
use App\Models\Quiz;
use App\Models\RecordedClass;
use App\Models\Setting;
use App\Models\Student;
use App\Models\StudentAssignment;
use App\Models\StudentTopicMastery;
use App\Models\Subject;
use App\Models\SubjectTopic;
use App\Models\Teacher;
use App\Models\TeacherApplication;
use App\Models\TeachingGig;
use App\Models\User;
use App\Services\Admin\ParentAccountService;
use App\Services\Admin\TeacherClaimService;
use App\Services\StudentAccessRestrictionService;
use Illuminate\Support\Carbon;

class DashboardMetricsService
{
    public function __construct(
        protected ParentAccountService $parentAccounts,
        protected TeacherClaimService $teacherClaims
    ) {
    }

    /**
     * Builds the full view-model for the admin dashboard. Pure aggregation —
     * no writes are persisted; the only mutation is decorating in-memory
     * collections (e.g. Student::is_restricted) for the view to read.
     */
    public function build(?int $selectedDiagnosticId): array
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
        $allFeeStructures     = FeeStructure::with(['student.user', 'subjects'])->latest('id')->get();
        $allInvoices          = Invoice::with(['student.user', 'student.parents', 'student.parent', 'feeStructure', 'items', 'reminders', 'paymentTransactions.payer', 'paymentTransactions.user'])->latest('id')->get();
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

        $restrictionService = app(StudentAccessRestrictionService::class);
        foreach ($allStudentsList as $st) {
            $st->is_restricted = $restrictionService->isStudentRestricted($st);
            $st->payment_status = $restrictionService->getStudentPaymentStatus($st);
            $st->restriction_details = $st->is_restricted ? $restrictionService->getRestrictionDetails($st) : null;
        }

        foreach ($allInvoices as $inv) {
            $isOverdue = $inv->status === InvoiceStatus::Overdue || ($inv->isPastDue() && $inv->requiresPayment());
            $inv->is_overdue = $isOverdue;
            if ($isOverdue && $inv->due_date) {
                $inv->days_overdue = max(1, (int) now()->startOfDay()->diffInDays(Carbon::parse($inv->due_date)->startOfDay()));
                $inv->restriction_date = Carbon::parse($inv->due_date)->addDay();
                $inv->restriction_status = 'Restricted';
            } else {
                $inv->days_overdue = 0;
                $inv->restriction_date = null;
                $inv->restriction_status = 'Normal';
            }
        }
        $restrictedStudentsCount = $allStudentsList->where('is_restricted', true)->count();

        $allCertificates      = \App\Models\Certificate::with(['student.user', 'course.subject'])->latest('id')->get();
        $financialLogs        = FinancialAuditLog::with('user')->latest('id')->take(30)->get();
        $pendingProofPayments     = Payment::where('payment_method', 'bank_transfer')->where('status', 'pending')->with(['student.user', 'payer', 'invoice'])->get();
        $allVerificationPayments  = Payment::where(function ($q) {
            $q->where('payment_method', 'bank_transfer')->orWhereNotNull('proof_file_path');
        })->with(['student.user', 'payer', 'invoice', 'invoices'])->latest('id')->get();
        $allEnrollments       = Enrollment::with(['student.user', 'course.subject', 'course.teacher.user'])->latest('id')->get();
        $allTeachers          = Teacher::with(['user', 'courses.enrollments', 'courses.subject', 'subjects'])->get();
        $allCourses           = Course::with(['teacher.user', 'subject.topics.subtopics'])->withCount('enrollments')->get();
        $allUsers             = User::latest('id')->get();
        $allGigs              = TeachingGig::with(['teacher.user', 'subject'])->latest('id')->get();
        $allMasteries         = StudentTopicMastery::with(['student.user', 'subject'])->latest('id')->take(40)->get();
        $allSubjects          = Subject::with(['topics.subtopics', 'diagnosticTest'])->orderBy('order', 'asc')->orderBy('id', 'asc')->get();
        $missingSubjectDiagnosticCount = $allSubjects->where('is_active', true)->whereNull('diagnostic_assignment_id')->count();
        $allTopics            = SubjectTopic::with(['subject', 'subtopics'])->orderBy('subject_id')->orderBy('chapter_number')->get();
        $allLiveClasses       = LiveClass::with(['course.subject', 'course.teacher.user', 'teacher.user'])->latest('id')->get();
        $allRecordedClasses   = RecordedClass::with(['course.subject', 'subject'])->latest('id')->get();
        $allQuizzes           = Quiz::with(['course.subject', 'questions'])->latest('id')->get();
        $teacherApplications  = TeacherApplication::with('user')->latest('id')->get();

        // ── 3c. Student Diagnostic Test Insights ─────────────────────────────
        $diagnosticTests = Assignment::where('type', 'Diagnostic Test')->with('subject')->latest('id')->get();
        $activeDiagnostic = $selectedDiagnosticId ? $diagnosticTests->firstWhere('id', $selectedDiagnosticId) : $diagnosticTests->first();
        $activeDiagId    = $activeDiagnostic?->id;

        // Narrow the roster to students who are actually eligible for the active
        // diagnostic: explicitly assigned, enrolled in a course of its subject, or
        // level-matched when the test is global+level-targeted. An unfiltered
        // "all active students" roster let unrelated students appear here.
        $targetStudentIds = collect();
        if ($activeDiagnostic) {
            $assignedIds = StudentAssignment::where('assignment_id', $activeDiagnostic->id)->pluck('student_id');

            $enrolledIds = collect();
            if ($activeDiagnostic->subject_id) {
                $courseIds = Course::where('subject_id', $activeDiagnostic->subject_id)->pluck('id');
                $enrolledIds = Enrollment::whereIn('course_id', $courseIds)->pluck('student_id');
            }

            $levelIds = collect();
            if ($activeDiagnostic->is_global && $activeDiagnostic->target_level && $activeDiagnostic->target_level !== 'All Levels') {
                $levelIds = Student::where('level', $activeDiagnostic->target_level)->pluck('id');
            }

            $targetStudentIds = $assignedIds->merge($enrolledIds)->merge($levelIds)->unique()->filter();
        }

        $allDiagnosticStudents = $targetStudentIds->isEmpty() ? collect() : Student::whereIn('id', $targetStudentIds)
            ->where('status', StudentStatus::Active)
            ->whereHas('user', function ($qu) {
                $qu->where('is_active', true);
            })
            ->with(['user', 'studentAssignments' => function ($q) use ($activeDiagId) {
                if ($activeDiagId) {
                    $q->where('assignment_id', $activeDiagId);
                } else {
                    $q->whereHas('assignment', fn ($qa) => $qa->where('type', 'Diagnostic Test'));
                }
                $q->with('assignment');
            }])->get()->map(function ($st) {
                $sa = $st->studentAssignments->first();
                // 'completed' was never a valid StudentAssignment status (enum:
                // pending/submitted/graded/late) — dropped, same phantom-value
                // pattern fixed elsewhere in this Phase 3 pass.
                $isCompleted = $sa && in_array($sa->status, [StudentAssignmentStatus::Submitted, StudentAssignmentStatus::Graded], true);
                $score = $isCompleted ? (int) $sa->score : null;
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
                    'raw_status'            => $sa?->status ?? 'pending',
                    'score'                 => $score,
                    'tahap_kepandaian'      => $tahap,
                    'formatted_time'        => $formattedTime,
                    'submitted_at'          => $sa?->submitted_at,
                    'badge_class'           => match ($tahap) {
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

        $deactivatedUserIds = User::where('is_active', false)->pluck('id');

        $inactiveStudentUserIds = Student::where('status', '!=', StudentStatus::Active)
            ->pluck('user_id')
            ->filter();

        $restrictedStudentUserIds = $allStudentsList
            ->filter(fn ($s) => $s->is_restricted === true)
            ->pluck('user_id')
            ->filter();

        $inactiveTeacherUserIds = Teacher::whereIn('application_status', ['inactive', 'deactivated'])
            ->pluck('user_id')
            ->filter();

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
            if ($amt <= 0) {
                continue;
            }

            $categorized = false;
            $linkedInvoices = $payment->invoices;

            if ($linkedInvoices && $linkedInvoices->isNotEmpty()) {
                $totalAllocated = $linkedInvoices->sum(fn ($i) => (float) ($i->pivot->amount_applied ?? $i->total_amount));
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
        $revenueData   = array_map(fn ($v) => round($v, 2), array_values($streamTotals));

        // ── 5. Audit Logs ─────────────────────────────────────────────────────
        $payLogs = Payment::with(['user', 'course'])->latest('id')->take(10)->get()->map(function ($p) {
            return [
                'id'        => 'PAY-' . str_pad($p->id, 5, '0', STR_PAD_LEFT),
                'user'      => $p->user->name ?? 'User #' . $p->user_id,
                'role'      => ucfirst($p->user->role ?? 'Student'),
                'action'    => 'Payment of RM ' . number_format($p->amount, 2) . ' for ' . ($p->course->title ?? $p->description ?? 'Course Fee'),
                'module'    => 'Financials',
                'status'    => strtoupper($p->status?->value ?? 'PAID'),
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

        $msgLogs = ChatMessage::with(['sender', 'conversation.participants.user'])
            ->latest('id')->take(10)->get()->map(function ($m) {
                $receiver = $m->conversation?->otherParticipant($m->sender_id);
                return [
                    'id'        => 'MSG-' . str_pad($m->id, 5, '0', STR_PAD_LEFT),
                    'user'      => $m->sender->name ?? 'User #' . $m->sender_id,
                    'role'      => ucfirst($m->sender->role ?? 'User'),
                    'action'    => 'Sent live communication message to ' . ($receiver->name ?? 'a conversation'),
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
        $allBlogPosts = BlogPost::with('author')->latest('created_at')->get();
        $allTasks = Assignment::with(['course.subject', 'results.student.user'])
            ->latest('id')
            ->get();

        // ── Email Management & Blast System ───────────────────────────────
        $emailStats = [
            'total_sent'          => \App\Models\EmailLog::whereIn('status', [\App\Models\EmailLog::STATUS_SENT, \App\Models\EmailLog::STATUS_DELIVERED])->count(),
            'processed'           => \App\Models\EmailLog::count(),
            'failed'              => \App\Models\EmailLog::where('status', \App\Models\EmailLog::STATUS_FAILED)->count(),
            'pending'             => \App\Models\EmailLog::where('status', \App\Models\EmailLog::STATUS_QUEUED)->count(),
            'scheduled_campaigns' => \App\Models\EmailCampaign::where('status', \App\Models\EmailCampaign::STATUS_SCHEDULED)->count(),
            'active_automations'  => \App\Models\EmailAutomation::where('is_enabled', true)->count(),
        ];

        $emailTemplates     = \App\Models\EmailTemplate::with('creator')->notArchived()->orderBy('category')->orderBy('name')->get();
        $emailCampaigns     = \App\Models\EmailCampaign::with(['template', 'creator'])->latest('id')->take(30)->get();
        $emailAutomations   = \App\Models\EmailAutomation::with('template')->get();
        $emailLogs          = \App\Models\EmailLog::with(['template', 'user'])->latest('id')->take(60)->get();
        $emailCategories    = \App\Models\EmailTemplate::categories();
        $availableVariables = \App\Services\Email\EmailVariableRegistry::flatList();
        $supportedAudiences = \App\Services\Email\AudienceResolverService::supportedAudiences();

        $emailSettings = [
            'sender_display_name'    => Setting::get('sender_display_name', config('mail.from.name', 'Olympia Scholars')),
            'sender_email_address'   => Setting::get('sender_email_address', config('mail.from.address', 'noreply@olympiascholars.edu.my')),
            'reply_to_email'         => Setting::get('reply_to_email', config('mail.from.address', 'support@olympiascholars.edu.my')),
            'official_support_email' => Setting::get('official_support_email', 'support@olympiascholars.edu.my'),
            'official_website_url'   => Setting::get('official_website_url', config('app.url', 'https://olympiascholars.edu.my')),
            'official_email_footer'  => Setting::get('official_email_footer', 'Olympia Scholars • Dedicated to Educational Excellence • Kuala Lumpur, Malaysia'),
            'email_banner_url'       => Setting::get('email_banner_url', asset('images/email-banner.png')),
            'mail_mailer'            => config('mail.default', 'smtp'),
            'mail_host'              => config('mail.mailers.smtp.host', 'smtp.gmail.com'),
            'mail_port'              => config('mail.mailers.smtp.port', 587),
            'mail_encryption'        => config('mail.mailers.smtp.encryption', 'tls'),
        ];

        // 7-day sending trend data
        $emailTrendsLabels = [];
        $emailTrendsSent   = [];
        $emailTrendsFailed = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $dayStr = $day->format('Y-m-d');
            $emailTrendsLabels[] = $day->format('d M');
            $emailTrendsSent[]   = \App\Models\EmailLog::whereDate('created_at', $dayStr)->whereIn('status', [\App\Models\EmailLog::STATUS_SENT, \App\Models\EmailLog::STATUS_DELIVERED])->count();
            $emailTrendsFailed[] = \App\Models\EmailLog::whereDate('created_at', $dayStr)->where('status', \App\Models\EmailLog::STATUS_FAILED)->count();
        }

        // Category breakdown
        $catCounts = \App\Models\EmailLog::select('category', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();
        $emailCategoryLabels = [];
        $emailCategoryData   = [];
        foreach ($emailCategories as $ckey => $clabel) {
            $emailCategoryLabels[] = $clabel;
            $emailCategoryData[]   = $catCounts[$ckey] ?? 0;
        }

        // ── Attendance Hub Data ─────────────────────────────────────────────
        $allAttendances = \App\Models\StudentAttendance::with([
            'student.user',
            'liveClass.course.subject',
            'liveClass.teacher.user',
            'verifier',
        ])->latest('id')->take(500)->get();

        $attendanceTotalRecords  = \App\Models\StudentAttendance::count();
        $attendanceAttendedCount = \App\Models\StudentAttendance::where('status', 'attended')->count();
        $attendanceLateCount     = \App\Models\StudentAttendance::where('status', 'late')->count();
        $attendanceAbsentCount   = \App\Models\StudentAttendance::where('status', 'absent')->count();
        $attendanceExcusedCount  = \App\Models\StudentAttendance::where('status', 'excused')->count();
        $attendancePendingCount  = \App\Models\StudentAttendance::where('status', 'pending_verification')->count();

        $pendingAttendanceCorrections = \App\Models\AttendanceCorrectionRequest::where('status', 'pending')
            ->with(['student.user', 'liveClass.course.subject'])
            ->latest()
            ->take(10)
            ->get();

        $attendanceLateThreshold        = (int) Setting::get('attendance.late_threshold_minutes', 15);
        $attendanceEarlyJoinWindow      = (int) Setting::get('attendance.early_join_window_minutes', 15);
        $attendanceExcusedInDenominator = (bool) Setting::get('attendance.excused_in_denominator', 0);

        // ── Parents & Linkages Directory ──────────────────────────────────────
        $parentsDirectory = $this->parentAccounts->parentsDirectoryData();
        $allParentsList = $parentsDirectory['allParentsList'];
        $allParentStudentLinks = $parentsDirectory['allParentStudentLinks'];

        // ── Teacher Class Fee Claims ────────────────────────────────────────────
        $allTeacherClaims = $this->teacherClaims->dashboardData()['allTeacherClaims'];

        return [
            'totalUsers' => $totalUsers, 'totalStudents' => $totalStudents, 'totalTeachers' => $totalTeachers, 'totalParents' => $totalParents, 'totalAdmins' => $totalAdmins,
            'totalCourses' => $totalCourses, 'publishedCourses' => $publishedCourses, 'draftCourses' => $draftCourses, 'archivedCourses' => $archivedCourses,
            'activeEnrollments' => $activeEnrollments, 'totalSubjects' => $totalSubjects, 'totalLiveClasses' => $totalLiveClasses, 'totalGigs' => $totalGigs,
            'totalRecordings' => $totalRecordings, 'totalMasteries' => $totalMasteries,
            'totalRevenue' => $totalRevenue, 'totalInvoicesCount' => $totalInvoicesCount, 'paidInvoicesCount' => $paidInvoicesCount, 'pendingInvoicesCount' => $pendingInvoicesCount,
            'teacherPayoutEst' => $teacherPayoutEst, 'netPlatformMargin' => $netPlatformMargin, 'avgTeacherRating' => $avgTeacherRating,
            'teacherCutPct' => $teacherCutPct, 'currencySymbol' => $currencySymbol,
            'allPayments' => $allPayments, 'allFeeStructures' => $allFeeStructures, 'allInvoices' => $allInvoices, 'allStudentsList' => $allStudentsList, 'allCertificates' => $allCertificates, 'financialLogs' => $financialLogs, 'pendingProofPayments' => $pendingProofPayments, 'allVerificationPayments' => $allVerificationPayments,
            'allEnrollments' => $allEnrollments, 'allTeachers' => $allTeachers, 'allCourses' => $allCourses, 'allUsers' => $allUsers, 'allGigs' => $allGigs, 'allMasteries' => $allMasteries,
            'allSubjects' => $allSubjects, 'missingSubjectDiagnosticCount' => $missingSubjectDiagnosticCount, 'allTopics' => $allTopics, 'allLiveClasses' => $allLiveClasses, 'allRecordedClasses' => $allRecordedClasses, 'teacherApplications' => $teacherApplications, 'allQuizzes' => $allQuizzes, 'allTasks' => $allTasks,
            'revenueLabels' => $revenueLabels, 'revenueData' => $revenueData,
            'subjectEnrollments' => $subjectEnrollments, 'subjectLabels' => $subjectLabels, 'subjectData' => $subjectData,
            'recentLogs' => $recentLogs, 'restrictedStudentsCount' => $restrictedStudentsCount,
            'nonActiveUsers' => $nonActiveUsers, 'nonActiveUsersCount' => $nonActiveUsersCount, 'nonActiveStudentsCount' => $nonActiveStudentsCount, 'nonActiveTeachersCount' => $nonActiveTeachersCount, 'nonActiveParentsCount' => $nonActiveParentsCount,
            'totalClassesThisWeek' => $totalClassesThisWeek, 'classesToday' => $classesToday, 'rescheduledCount' => $rescheduledCount, 'academicEventsCount' => $academicEventsCount,
            'popupSettings' => $popupSettings, 'allBlogPosts' => $allBlogPosts,
            'allDiagnosticStudents' => $allDiagnosticStudents, 'diagnosticTotalStudents' => $diagnosticTotalStudents, 'diagnosticCompletedCount' => $diagnosticCompletedCount, 'diagnosticPendingCount' => $diagnosticPendingCount,
            'diagnosticCompletionRate' => $diagnosticCompletionRate, 'diagnosticAvgScore' => $diagnosticAvgScore, 'diagnosticCemerlangCount' => $diagnosticCemerlangCount, 'diagnosticBaikCount' => $diagnosticBaikCount,
            'diagnosticSederhanaCount' => $diagnosticSederhanaCount, 'diagnosticPerluBimbinganCount' => $diagnosticPerluBimbinganCount,
            'diagnosticTests' => $diagnosticTests, 'activeDiagnostic' => $activeDiagnostic,
            'emailStats' => $emailStats, 'emailTemplates' => $emailTemplates, 'emailCampaigns' => $emailCampaigns, 'emailAutomations' => $emailAutomations, 'emailLogs' => $emailLogs,
            'emailCategories' => $emailCategories, 'availableVariables' => $availableVariables, 'supportedAudiences' => $supportedAudiences, 'emailSettings' => $emailSettings,
            'emailTrendsLabels' => $emailTrendsLabels, 'emailTrendsSent' => $emailTrendsSent, 'emailTrendsFailed' => $emailTrendsFailed,
            'emailCategoryLabels' => $emailCategoryLabels, 'emailCategoryData' => $emailCategoryData,
            'allAttendances' => $allAttendances, 'attendanceTotalRecords' => $attendanceTotalRecords, 'attendanceAttendedCount' => $attendanceAttendedCount, 'attendanceLateCount' => $attendanceLateCount,
            'attendanceAbsentCount' => $attendanceAbsentCount, 'attendanceExcusedCount' => $attendanceExcusedCount, 'attendancePendingCount' => $attendancePendingCount,
            'pendingAttendanceCorrections' => $pendingAttendanceCorrections, 'attendanceLateThreshold' => $attendanceLateThreshold, 'attendanceEarlyJoinWindow' => $attendanceEarlyJoinWindow, 'attendanceExcusedInDenominator' => $attendanceExcusedInDenominator,
            'allParentsList' => $allParentsList, 'allParentStudentLinks' => $allParentStudentLinks,
            'allTeacherClaims' => $allTeacherClaims,
        ];
    }
}
