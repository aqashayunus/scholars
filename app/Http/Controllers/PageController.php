<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Registration;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    /**
     * Helper: build the pricing plans array from DB settings.
     */
    private function pricingPlansFromDb(): array
    {
        return [
            'monthly_price' => Setting::get('plan_monthly_price', 'RM 99'),
            'monthly_per' => Setting::get('plan_monthly_per', '/month'),
            'monthly_desc' => Setting::get('plan_monthly_desc', 'Access to 1 subject with live classes 4x/month'),
            'subject_price' => Setting::get('plan_subject_price', 'From RM 150'),
            'subject_per' => Setting::get('plan_subject_per', '/subject'),
            'subject_desc' => Setting::get('plan_subject_desc', '2-4 subjects bundled with priority support'),
            'premium_price' => Setting::get('plan_premium_price', 'From RM 199'),
            'premium_per' => Setting::get('plan_premium_per', '/month'),
            'premium_desc' => Setting::get('plan_premium_desc', 'Unlimited subjects, 1-on-1 tutoring and 24/7 support'),
        ];
    }

    public function home()
    {
        $teachers = Teacher::with('user', 'subjects')->take(5)->get();
        $testimonials = Testimonial::where('is_active', true)->take(3)->get();
        $subjects = Subject::where('is_active', true)->take(8)->get();
        $posts = BlogPost::whereNotNull('published_at')->latest('published_at')->take(3)->get();

        return view('pages.home', compact('teachers', 'testimonials', 'subjects', 'posts'));
    }

    public function about()
    {
        return view('pages.about');
    }

    public function programmes()
    {
        return view('pages.programmes');
    }

    public function pricing()
    {
        $pricingPlans = $this->pricingPlansFromDb();
        $platformName = Setting::get('platform_name', 'OlympiaScholar');
        $currencySymbol = Setting::get('currency_symbol', 'RM');

        return view('pages.pricing', compact('pricingPlans', 'platformName', 'currencySymbol'));
    }

    public function successStories()
    {
        return view('pages.success-stories');
    }

    public function blog()
    {
        $posts = BlogPost::whereNotNull('published_at')->latest('published_at')->paginate(9);

        return view('pages.blog', compact('posts'));
    }

    public function blogShow($slug)
    {
        $post = BlogPost::where('slug', $slug)
            ->whereNotNull('published_at')
            ->firstOrFail();

        $recentPosts = BlogPost::whereNotNull('published_at')
            ->where('id', '!=', $post->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('pages.blog-show', compact('post', 'recentPosts'));
    }

    public function faq()
    {
        $pricingPlans = $this->pricingPlansFromDb();

        return view('pages.faq', compact('pricingPlans'));
    }

    /**
     * Enroll student in courses corresponding to their registered subject names.
     */
    public static function enrollStudentInSubjects(Student $student, array $subjectNames, ?string $level = null): void
    {
        $defaultTeacher = Teacher::first();
        $effectiveLevel = $level ?: ($student->level ?? 'Form 5');

        foreach ($subjectNames as $subjectName) {
            $subjectName = trim($subjectName);
            if (empty($subjectName)) {
                continue;
            }

            // Prioritize matching exact form_year / level
            $subject = Subject::where('is_active', true)
                ->where(function ($q) use ($subjectName) {
                    $q->where('name', $subjectName)->orWhere('slug', Str::slug($subjectName));
                })
                ->where('form_year', $effectiveLevel)
                ->first();

            if (! $subject) {
                // Secondary check: match education type or stage
                $isPrimary = str_contains(strtolower($effectiveLevel), 'year') || str_contains(strtolower($effectiveLevel), 'primary');
                $subject = Subject::where('is_active', true)
                    ->where(function ($q) use ($subjectName) {
                        $q->where('name', $subjectName)->orWhere('slug', Str::slug($subjectName));
                    })
                    ->where('education_type', $isPrimary ? 'primary' : 'secondary')
                    ->first();
            }

            if (! $subject) {
                $subject = Subject::where('is_active', true)
                    ->where(function ($q) use ($subjectName) {
                        $q->where('name', $subjectName)->orWhere('slug', Str::slug($subjectName));
                    })
                    ->first();
            }

            if (! $subject) {
                $isPrimary = str_contains(strtolower($effectiveLevel), 'year') || str_contains(strtolower($effectiveLevel), 'primary');
                $subject = Subject::create([
                    'name' => $subjectName,
                    'slug' => Str::slug($subjectName.' '.$effectiveLevel) ?: 'subj-'.Str::random(5),
                    'education_type' => $isPrimary ? 'primary' : 'secondary',
                    'stage' => $isPrimary ? 'Primary' : 'Upper Secondary',
                    'form_year' => $effectiveLevel,
                    'category' => 'Core',
                    'is_active' => true,
                ]);
            }

            // Find or create Course for this subject
            $course = Course::where('subject_id', $subject->id)
                ->where(function ($q) use ($effectiveLevel) {
                    $q->where('level', $effectiveLevel)
                        ->orWhere('title', 'like', "%{$effectiveLevel}%");
                })
                ->first();

            if (! $course) {
                $course = Course::where('subject_id', $subject->id)->first();
            }

            if (! $course) {
                $course = Course::create([
                    'subject_id' => $subject->id,
                    // teacher_id is nullable — previously fell back to a
                    // hardcoded 1 when no teacher existed yet, which crashed
                    // with a FK violation in any environment where teacher
                    // id 1 doesn't exist (fresh DB, or that teacher deleted).
                    'teacher_id' => $defaultTeacher?->id,
                    'title' => $subject->name,
                    'description' => 'Comprehensive learning course for '.$subject->name,
                    'level' => $effectiveLevel,
                    'status' => 'published',
                    'total_lessons' => 12,
                ]);
            }

            // Create enrollment
            $enrollment = Enrollment::firstOrNew(['student_id' => $student->id, 'course_id' => $course->id]);
            if (! $enrollment->exists) {
                $enrollment->status = 'active';
                $enrollment->enrolled_at = now();
                $enrollment->progress_pct = 0;
                $enrollment->completed_lessons = 0;
                $enrollment->save();
            }
        }
    }

    /**
     * Synchronize enrollments for existing students who have a registration record or need curriculum enrollments.
     */
    public static function syncStudentEnrollments(Student $student): void
    {
        // If student already has enrollments, never inject arbitrary fallback subjects
        if ($student->enrollments()->exists()) {
            return;
        }

        // Find registration record by user_id or email
        $registration = Registration::where('user_id', $student->user_id)
            ->orWhere('email', $student->user?->email)
            ->latest()
            ->first();

        // Check parent registration if child
        if (! $registration && $student->parent_user_id) {
            $registration = Registration::where('user_id', $student->parent_user_id)->latest()->first();
        }

        $subjectNames = [];
        if ($registration && ! empty($registration->subjects)) {
            $subjectNames = is_array($registration->subjects) ? $registration->subjects : [$registration->subjects];
        }

        $effectiveLevel = $registration->level ?? $student->level ?? 'Form 5';

        // Fallback: Default to core curriculum subjects ONLY if student has zero enrollments
        if (empty($subjectNames)) {
            $isPrimary = str_contains(strtolower($effectiveLevel), 'year') || str_contains(strtolower($effectiveLevel), 'primary');
            if ($isPrimary) {
                $subjectNames = ['Bahasa Melayu', 'English', 'Mathematics', 'Science'];
            } else {
                $subjectNames = ['Bahasa Melayu', 'English', 'Mathematics', 'Sejarah'];
            }
        }

        static::enrollStudentInSubjects($student, $subjectNames, $effectiveLevel);
    }

    public function checkRegistrationAvailability(Request $request)
    {
        $email = trim($request->input('email', ''));
        $phone = trim($request->input('phone', ''));
        $studentName = trim($request->input('student_name', ''));
        $registerAs = trim($request->input('register_as', ''));

        $emailExists = false;
        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailExists = User::where('email', $email)->exists() || Registration::where('email', $email)->exists();
        }

        $phoneExists = false;
        if ($phone) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
            if (strlen($cleanPhone) >= 8) {
                $phoneExists = User::where(function ($q) use ($phone, $cleanPhone) {
                    $q->where('phone', $phone)
                        ->orWhere('phone', $cleanPhone)
                        ->orWhereRaw("REPLACE(REPLACE(REPLACE(COALESCE(phone, ''), ' ', ''), '-', ''), '+', '') = ?", [$cleanPhone]);
                })->exists() || Registration::where(function ($q) use ($phone, $cleanPhone) {
                    $q->where('phone', $phone)
                        ->orWhere('phone', $cleanPhone)
                        ->orWhereRaw("REPLACE(REPLACE(REPLACE(COALESCE(phone, ''), ' ', ''), '-', ''), '+', '') = ?", [$cleanPhone]);
                })->exists();
            }
        }

        $childLinkCode = trim($request->input('child_link_code', ''));
        $linkCodeChecked = false;
        $linkCodeFound = false;
        $linkStudentName = null;
        $linkSubjects = [];
        $linkLevel = null;

        if (! empty($childLinkCode)) {
            $linkCodeChecked = true;
            $studentByCode = Student::where('link_code', strtoupper($childLinkCode))->with(['user', 'enrollments.course.subject'])->first();
            if ($studentByCode && $studentByCode->user) {
                $linkCodeFound = true;
                $linkStudentName = $studentByCode->user->name;
                $linkLevel = $studentByCode->level;
                $linkSubjects = $studentByCode->enrollments->pluck('course.subject.name')->filter()->unique()->values()->all();
            }
        }

        return response()->json([
            'email_exists' => $emailExists,
            'phone_exists' => $phoneExists,
            'link_code_checked' => $linkCodeChecked,
            'link_code_found' => $linkCodeFound,
            'link_student_name' => $linkStudentName,
            'link_subjects' => $linkSubjects,
            'link_level' => $linkLevel,
        ]);
    }

    public function learningPlatform()
    {
        return view('pages.learning-platform');
    }

    public function subjects()
    {
        $subjects = Subject::where('is_active', true)->with(['topics'])->orderBy('order', 'asc')->orderBy('id', 'asc')->get();

        return view('pages.subjects', compact('subjects'));
    }

    public function subjectShow(Subject $subject)
    {
        $subject->load('teachers.user', 'courses.teacher.user');

        return view('pages.subject-show', compact('subject'));
    }

    public function teachers()
    {
        $teachers = Teacher::with('user', 'subjects')->get();

        return view('pages.teachers', compact('teachers'));
    }

    public function teacherShow(Teacher $teacher)
    {
        $teacher->load('user', 'subjects', 'courses.subject');

        return view('pages.teacher-show', compact('teacher'));
    }
}
