<?php

namespace App\Services\Email;

use App\Models\Course;
use App\Models\EmailSuppression;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\LiveClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AudienceResolverService
{
    /**
     * Supported audience types with human-readable labels and descriptions.
     */
    public static function supportedAudiences(): array
    {
        return [
            'all_parents' => [
                'label'       => 'All Parents',
                'description' => 'Every registered parent account',
                'category'    => 'General Audiences',
            ],
            'all_students' => [
                'label'       => 'All Students',
                'description' => 'Every registered student account',
                'category'    => 'General Audiences',
            ],
            'all_teachers' => [
                'label'       => 'All Teachers & Mentors',
                'description' => 'All active educators and teachers',
                'category'    => 'General Audiences',
            ],
            'parents_with_outstanding_invoices' => [
                'label'       => 'Parents with Outstanding Invoices',
                'description' => 'Parents linked to students who have unpaid invoices',
                'category'    => 'Financial Audiences',
            ],
            'parents_with_overdue_invoices' => [
                'label'       => 'Parents with Overdue Invoices',
                'description' => 'Parents of students with overdue accounts',
                'category'    => 'Financial Audiences',
            ],
            'students_active' => [
                'label'       => 'Active Enrolled Students',
                'description' => 'Students actively enrolled without restrictions',
                'category'    => 'Academic Audiences',
            ],
            'students_restricted' => [
                'label'       => 'Restricted Students',
                'description' => 'Students whose access is restricted due to overdue payments',
                'category'    => 'Academic Audiences',
            ],
            'students_by_programme' => [
                'label'       => 'Students by Programme',
                'description' => 'Students enrolled in a specific level / programme',
                'category'    => 'Segmented Audiences',
            ],
            'students_by_subject' => [
                'label'       => 'Students by Subject',
                'description' => 'Students currently enrolled in a specific subject',
                'category'    => 'Segmented Audiences',
            ],
            'students_by_class' => [
                'label'       => 'Students by Live Class',
                'description' => 'Students participating in a specific live class schedule',
                'category'    => 'Segmented Audiences',
            ],
        ];
    }

    /**
     * Count recipients matching audience type and filters without loading all records.
     */
    public static function countRecipients(string $audienceType, array $filters = []): int
    {
        return self::resolve($audienceType, $filters)->count();
    }

    /**
     * Resolve audience into a deduplicated collection of recipient items:
     * [
     *   'email' => string,
     *   'name' => string,
     *   'user_id' => int|null,
     *   'recipient_type' => string ('parent'|'student'|'teacher'|'custom'),
     *   'related_student_id' => int|null,
     * ]
     */
    public static function resolve(string $audienceType, array $filters = []): Collection
    {
        $suppressed = EmailSuppression::pluck('email')->map(fn($e) => strtolower(trim($e)))->toArray();
        $recipients = collect();

        switch ($audienceType) {
            case 'all_parents':
                $users = User::where('role', 'parent')
                    ->where('is_active', true)
                    ->whereNotNull('email')
                    ->select('id', 'name', 'email', 'role')
                    ->get();

                foreach ($users as $u) {
                    $recipients->push([
                        'email'              => strtolower(trim($u->email)),
                        'name'               => $u->name,
                        'user_id'            => $u->id,
                        'recipient_type'     => 'parent',
                        'related_student_id' => null,
                    ]);
                }
                break;

            case 'all_students':
                $students = Student::with('user')
                    ->whereHas('user', function ($q) {
                        $q->where('is_active', true)->whereNotNull('email');
                    })
                    ->get();

                foreach ($students as $s) {
                    if ($s->user && $s->user->email) {
                        $recipients->push([
                            'email'              => strtolower(trim($s->user->email)),
                            'name'               => $s->user->name,
                            'user_id'            => $s->user->id,
                            'recipient_type'     => 'student',
                            'related_student_id' => $s->id,
                        ]);
                    }
                }
                break;

            case 'all_teachers':
                $teachers = Teacher::with('user')
                    ->where(function($q) {
                        $q->where('application_status', 'active')
                          ->orWhere('status', 'active');
                    })
                    ->whereHas('user', function ($q) {
                        $q->where('is_active', true)->whereNotNull('email');
                    })
                    ->get();

                $foundUserIds = [];
                foreach ($teachers as $t) {
                    if ($t->user && $t->user->email) {
                        $foundUserIds[] = $t->user->id;
                        $recipients->push([
                            'email'              => strtolower(trim($t->user->email)),
                            'name'               => $t->user->name,
                            'user_id'            => $t->user->id,
                            'recipient_type'     => 'teacher',
                            'related_student_id' => null,
                        ]);
                    }
                }

                // Also include users with role 'teacher'
                $directTeachers = User::where('role', 'teacher')
                    ->where('is_active', true)
                    ->whereNotNull('email')
                    ->whereNotIn('id', $foundUserIds)
                    ->get();

                foreach ($directTeachers as $dt) {
                    $recipients->push([
                        'email'              => strtolower(trim($dt->email)),
                        'name'               => $dt->name,
                        'user_id'            => $dt->id,
                        'recipient_type'     => 'teacher',
                        'related_student_id' => null,
                    ]);
                }
                break;

            case 'parents_with_outstanding_invoices':
                $studentIds = Invoice::whereIn('status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PARTIALLY_PAID, Invoice::STATUS_OVERDUE])
                    ->pluck('student_id')
                    ->unique();

                $parentUserIds = DB::table('parent_student')
                    ->whereIn('student_id', $studentIds)
                    ->pluck('parent_user_id')
                    ->merge(Student::whereIn('id', $studentIds)->whereNotNull('parent_user_id')->pluck('parent_user_id'))
                    ->unique();

                $parents = User::whereIn('id', $parentUserIds)
                    ->where('is_active', true)
                    ->whereNotNull('email')
                    ->get();

                foreach ($parents as $p) {
                    $recipients->push([
                        'email'              => strtolower(trim($p->email)),
                        'name'               => $p->name,
                        'user_id'            => $p->id,
                        'recipient_type'     => 'parent',
                        'related_student_id' => null,
                    ]);
                }
                break;

            case 'parents_with_overdue_invoices':
                $overdueStudentIds = Invoice::where('status', Invoice::STATUS_OVERDUE)
                    ->orWhere(function ($q) {
                        $q->whereIn('status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PARTIALLY_PAID])
                            ->whereNotNull('due_date')
                            ->where('due_date', '<', now()->toDateString());
                    })
                    ->pluck('student_id')
                    ->unique();

                $parentUserIds = DB::table('parent_student')
                    ->whereIn('student_id', $overdueStudentIds)
                    ->pluck('parent_user_id')
                    ->merge(Student::whereIn('id', $overdueStudentIds)->whereNotNull('parent_user_id')->pluck('parent_user_id'))
                    ->unique();

                $parents = User::whereIn('id', $parentUserIds)
                    ->where('is_active', true)
                    ->whereNotNull('email')
                    ->get();

                foreach ($parents as $p) {
                    $recipients->push([
                        'email'              => strtolower(trim($p->email)),
                        'name'               => $p->name,
                        'user_id'            => $p->id,
                        'recipient_type'     => 'parent',
                        'related_student_id' => null,
                    ]);
                }
                break;

            case 'students_active':
                $overdueStudentIds = Invoice::where('status', Invoice::STATUS_OVERDUE)
                    ->orWhere(function ($q) {
                        $q->whereIn('status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PARTIALLY_PAID])
                            ->whereNotNull('due_date')
                            ->where('due_date', '<', now()->toDateString());
                    })
                    ->pluck('student_id')
                    ->unique();

                $activeStudents = Student::with('user')
                    ->whereNotIn('id', $overdueStudentIds)
                    ->whereHas('user', function ($q) {
                        $q->where('is_active', true)->whereNotNull('email');
                    })
                    ->get();

                foreach ($activeStudents as $s) {
                    if ($s->user && $s->user->email) {
                        $recipients->push([
                            'email'              => strtolower(trim($s->user->email)),
                            'name'               => $s->user->name,
                            'user_id'            => $s->user->id,
                            'recipient_type'     => 'student',
                            'related_student_id' => $s->id,
                        ]);
                    }
                }
                break;

            case 'students_restricted':
                $restrictedStudentIds = Invoice::where('status', Invoice::STATUS_OVERDUE)
                    ->orWhere(function ($q) {
                        $q->whereIn('status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PARTIALLY_PAID])
                            ->whereNotNull('due_date')
                            ->where('due_date', '<', now()->toDateString());
                    })
                    ->pluck('student_id')
                    ->unique();

                $restrictedStudents = Student::with('user')
                    ->whereIn('id', $restrictedStudentIds)
                    ->whereHas('user', function ($q) {
                        $q->where('is_active', true)->whereNotNull('email');
                    })
                    ->get();

                foreach ($restrictedStudents as $s) {
                    if ($s->user && $s->user->email) {
                        $recipients->push([
                            'email'              => strtolower(trim($s->user->email)),
                            'name'               => $s->user->name,
                            'user_id'            => $s->user->id,
                            'recipient_type'     => 'student',
                            'related_student_id' => $s->id,
                        ]);
                    }
                }
                break;

            case 'students_by_programme':
                $programme = $filters['programme'] ?? null;
                $query = Student::with('user')
                    ->whereHas('user', function ($q) {
                        $q->where('is_active', true)->whereNotNull('email');
                    });

                if ($programme) {
                    $query->where(function ($q) use ($programme) {
                        $q->where('programme', 'like', "%{$programme}%")
                          ->orWhere('level', 'like', "%{$programme}%");
                    });
                }

                foreach ($query->get() as $s) {
                    if ($s->user && $s->user->email) {
                        $recipients->push([
                            'email'              => strtolower(trim($s->user->email)),
                            'name'               => $s->user->name,
                            'user_id'            => $s->user->id,
                            'recipient_type'     => 'student',
                            'related_student_id' => $s->id,
                        ]);
                    }
                }
                break;

            case 'students_by_subject':
                $subjectId = $filters['subject_id'] ?? null;
                $query = Student::with('user')
                    ->whereHas('user', function ($q) {
                        $q->where('is_active', true)->whereNotNull('email');
                    });

                if ($subjectId) {
                    $courseIds = Course::where('subject_id', $subjectId)->pluck('id');
                    $studentIds = Enrollment::whereIn('course_id', $courseIds)->pluck('student_id');
                    $query->whereIn('id', $studentIds);
                }

                foreach ($query->get() as $s) {
                    if ($s->user && $s->user->email) {
                        $recipients->push([
                            'email'              => strtolower(trim($s->user->email)),
                            'name'               => $s->user->name,
                            'user_id'            => $s->user->id,
                            'recipient_type'     => 'student',
                            'related_student_id' => $s->id,
                        ]);
                    }
                }
                break;

            case 'students_by_class':
                $classId = $filters['class_id'] ?? null;
                if ($classId) {
                    $liveClass = LiveClass::find($classId);
                    if ($liveClass && $liveClass->course_id) {
                        $studentIds = Enrollment::where('course_id', $liveClass->course_id)->pluck('student_id');
                        $students = Student::with('user')->whereIn('id', $studentIds)->get();

                        foreach ($students as $s) {
                            if ($s->user && $s->user->email) {
                                $recipients->push([
                                    'email'              => strtolower(trim($s->user->email)),
                                    'name'               => $s->user->name,
                                    'user_id'            => $s->user->id,
                                    'recipient_type'     => 'student',
                                    'related_student_id' => $s->id,
                                ]);
                            }
                        }
                    }
                }
                break;

            default:
                // Handle fallback or explicit custom list
                if (!empty($filters['custom_emails']) && is_array($filters['custom_emails'])) {
                    foreach ($filters['custom_emails'] as $em) {
                        if (filter_var($em, FILTER_VALIDATE_EMAIL)) {
                            $recipients->push([
                                'email'              => strtolower(trim($em)),
                                'name'               => explode('@', $em)[0],
                                'user_id'            => null,
                                'recipient_type'     => 'custom',
                                'related_student_id' => null,
                            ]);
                        }
                    }
                }
                break;
        }

        // Deduplicate by email and remove suppressed addresses
        return $recipients
            ->filter(fn($r) => !empty($r['email']) && filter_var($r['email'], FILTER_VALIDATE_EMAIL))
            ->reject(fn($r) => in_array($r['email'], $suppressed, true))
            ->unique('email')
            ->values();
    }
}
