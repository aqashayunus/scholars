<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'parent_user_id', 'level', 'status', 'school_name', 'programme',
        'total_points', 'streak_days', 'last_active_at', 'parent_phone',
        'ic_number_hash', 'gender', 'nationality', 'state', 'date_of_birth',
    ];

    // ──────────────────────────────────────────────────
    // IC (MyKad) Helpers
    // ──────────────────────────────────────────────────

    /**
     * Normalise a Malaysian IC string to 12 digits (strip dashes/spaces).
     */
    public static function normaliseIc(string $ic): string
    {
        return preg_replace('/[^0-9]/', '', $ic);
    }

    /**
     * Hash an IC number for secure storage (SHA-256).
     */
    public static function hashIc(string $ic): string
    {
        return hash('sha256', self::normaliseIc($ic));
    }

    /**
     * Store a hashed IC number on this student.
     */
    public function setIcNumber(string $ic): void
    {
        $this->ic_number_hash = self::hashIc($ic);
    }

    /**
     * Check if the given IC matches the stored hash.
     */
    public function matchesIc(string $ic): bool
    {
        return $this->ic_number_hash && hash_equals($this->ic_number_hash, self::hashIc($ic));
    }

    /**
     * Return a masked IC string (e.g. ******-**-1234) from the raw IC.
     * Only call this with the raw IC at the moment of registration — we do not store plaintext.
     */
    public static function maskedIc(string $ic): string
    {
        $digits = self::normaliseIc($ic);
        if (strlen($digits) !== 12) return '****-**-****';
        return '******-**-' . substr($digits, -4);
    }

    /**
     * Derive Date of Birth from IC (YYMMDD).
     */
    public static function dobFromIc(string $ic): ?string
    {
        $digits = self::normaliseIc($ic);
        if (strlen($digits) < 6) return null;
        $yy = substr($digits, 0, 2);
        $mm = substr($digits, 2, 2);
        $dd = substr($digits, 4, 2);
        // Determine century: if yy > current 2-digit year, it's 1900s, else 2000s
        $currentYY = (int) date('y');
        $year = ((int)$yy > $currentYY) ? '19' . $yy : '20' . $yy;
        return $year . '-' . $mm . '-' . $dd;
    }

    /**
     * Derive Gender from IC last digit (odd = Male, even = Female).
     */
    public static function genderFromIc(string $ic): ?string
    {
        $digits = self::normaliseIc($ic);
        if (strlen($digits) < 12) return null;
        $lastDigit = (int) substr($digits, -1);
        return ($lastDigit % 2 === 1) ? 'Male' : 'Female';
    }

    // ──────────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────────

    /**
     * Returns all parents linked to this student via the parent_student pivot.
     */
    public function parents()
    {
        return $this->belongsToMany(User::class, 'parent_student', 'student_id', 'parent_user_id')
                    ->withPivot('relationship', 'is_primary', 'status')
                    ->withTimestamps();
    }

    /**
     * Returns the primary parent linked via pivot (is_primary = true).
     */
    public function primaryParent()
    {
        return $this->belongsToMany(User::class, 'parent_student', 'student_id', 'parent_user_id')
                    ->withPivot('relationship', 'is_primary', 'status')
                    ->wherePivot('is_primary', true)
                    ->withTimestamps();
    }

    protected $casts = [
        'last_active_at' => 'datetime',
        'date_of_birth'  => 'date',
    ];

    public function user()        { return $this->belongsTo(User::class); }
    /** Legacy: single parent via parent_user_id — kept for backward compatibility */
    public function parent()      { return $this->belongsTo(User::class, 'parent_user_id'); }
    public function enrollments() { return $this->hasMany(Enrollment::class); }
    public function courses()     { return $this->hasManyThrough(Course::class, Enrollment::class, 'student_id', 'id', 'id', 'course_id'); }
    public function assignments() { return $this->hasMany(StudentAssignment::class); }
    public function studentAssignments() { return $this->hasMany(StudentAssignment::class); }
    public function quizResults() { return $this->hasMany(StudentQuiz::class); }
    public function attendances() { return $this->hasMany(StudentAttendance::class); }
    public function attendanceEvents() { return $this->hasMany(AttendanceEvent::class); }
    public function attendanceCorrectionRequests() { return $this->hasMany(AttendanceCorrectionRequest::class); }
    public function certificates() { return $this->hasMany(Certificate::class); }
    public function invoices()     { return $this->hasMany(Invoice::class); }
    public function payments()     { return $this->hasMany(Payment::class); }
    public function feeStructures() { return $this->hasMany(FeeStructure::class); }

    /**
     * Only active enrollments (confirmed/activated — the billing trigger).
     */
    public function activeEnrollments()
    {
        return $this->hasMany(Enrollment::class)->where('status', 'active');
    }

    /**
     * Subjects the student is currently enrolled in (via active enrollments → courses → subjects).
     * Used by InvoiceService when generating invoice line items.
     */
    public function enrolledSubjects()
    {
        return $this->hasManyThrough(
            \App\Models\Subject::class,
            \App\Models\Course::class,
            'id',          // courses.id
            'id',          // subjects.id
            'id',          // students.id (PK)
            'subject_id'   // courses.subject_id
        )->whereIn(
            'courses.id',
            $this->activeEnrollments()->select('course_id')
        );
    }

    public function getNameAttribute()  { return $this->user->name; }
    public function getEmailAttribute() { return $this->user->email; }

    /**
     * Get the student's study streak days (increases daily since registration day).
     */
    public function getStreakDaysAttribute($value): int
    {
        $registrationDate = $this->created_at ?? $this->user?->created_at;
        if ($registrationDate) {
            $regDay = \Carbon\Carbon::parse($registrationDate)->startOfDay();
            $today = now()->startOfDay();
            if ($regDay->lte($today)) {
                return max(1, (int) $regDay->diffInDays($today) + 1);
            }
            return 1;
        }

        return max(1, (int) ($value ?? 1));
    }

    /**
     * Update the student's study streak and last active timestamp.
     * Streak increases every day since registration day.
     */
    public function recordLoginStreak(): void
    {
        $registrationDate = $this->created_at ?? $this->user?->created_at;
        $days = 1;

        if ($registrationDate) {
            $regDay = \Carbon\Carbon::parse($registrationDate)->startOfDay();
            $today = now()->startOfDay();
            if ($regDay->lte($today)) {
                $days = max(1, (int) $regDay->diffInDays($today) + 1);
            }
        } elseif (!empty($this->attributes['streak_days'])) {
            $days = max(1, (int) $this->attributes['streak_days']);
        }

        $this->attributes['streak_days'] = $days;
        $this->last_active_at = now();
        $this->save();
    }

    /**
     * Synchronize all relevant assignments for this student:
     * 1. Global / Diagnostic tests (intended for all students or matching this student's level)
     * 2. Course-specific assignments for all enrolled courses
     */
    public function syncGlobalAndCourseAssignments(): void
    {
        // 0. Automatically clean up any orphaned records where assignment was deleted
        StudentAssignment::where('student_id', $this->id)
            ->whereDoesntHave('assignment')
            ->delete();

        // 1. Global / Diagnostic tasks
        $globalTasks = Assignment::where(function ($query) {
            $query->where('is_global', true)
                  ->orWhereNull('course_id');
        })->where(function ($query) {
            $query->whereNull('target_level')
                  ->orWhere('target_level', '')
                  ->orWhere('target_level', 'All Levels')
                  ->orWhere('target_level', $this->level);
        })->get();

        foreach ($globalTasks as $task) {
            StudentAssignment::firstOrCreate([
                'student_id'    => $this->id,
                'assignment_id' => $task->id,
            ], [
                'status' => 'pending',
            ]);
        }

        // 2. Enrolled Course assignments
        $courseIds = Enrollment::where('student_id', $this->id)->pluck('course_id')->toArray();
        if (!empty($courseIds)) {
            $courseAssignments = Assignment::whereIn('course_id', $courseIds)->get();
            foreach ($courseAssignments as $ca) {
                StudentAssignment::firstOrCreate([
                    'student_id'    => $this->id,
                    'assignment_id' => $ca->id,
                ], [
                    'status' => 'pending',
                ]);
            }
        }

        // 3. Subject-specific diagnostic tests for all enrolled subjects
        if (!empty($courseIds)) {
            $subjectIds = \App\Models\Course::whereIn('id', $courseIds)
                ->whereNotNull('subject_id')
                ->pluck('subject_id')
                ->unique();
            $subjectDiagnostics = \App\Models\Subject::whereIn('id', $subjectIds)
                ->whereNotNull('diagnostic_assignment_id')
                ->pluck('diagnostic_assignment_id');
            foreach ($subjectDiagnostics as $diagId) {
                StudentAssignment::firstOrCreate([
                    'student_id'    => $this->id,
                    'assignment_id' => $diagId,
                ], [
                    'status' => 'pending',
                ]);
            }
        }
    }
}
