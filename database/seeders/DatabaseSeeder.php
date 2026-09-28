<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\BlogPost;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LiveClass;
use App\Models\Payment;
use App\Models\Quiz;
use App\Models\Student;
use App\Models\StudentAssignment;
use App\Models\StudentQuiz;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─────────────────────────────────────────────────
        // 1. ADMIN
        // ─────────────────────────────────────────────────
        $admin = User::create([
            'name'     => 'Admin Olympian',
            'email'    => 'admin@olympianscholars.my',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        // ─────────────────────────────────────────────────
        // 2. SUBJECTS
        // ─────────────────────────────────────────────────
        $subjects = [
            ['name' => 'Mathematics',            'icon' => 'calculator',   'color' => '#1E40AF'],
            ['name' => 'Additional Mathematics', 'icon' => 'chart',        'color' => '#1E40AF'],
            ['name' => 'Science',                'icon' => 'flask',        'color' => '#1E40AF'],
            ['name' => 'Physics',                'icon' => 'atom',         'color' => '#1E40AF'],
            ['name' => 'Chemistry',              'icon' => 'beaker',       'color' => '#1E40AF'],
            ['name' => 'Biology',                'icon' => 'dna',          'color' => '#1E40AF'],
            ['name' => 'Bahasa Melayu',          'icon' => 'book-open',    'color' => '#1E40AF'],
            ['name' => 'English',                'icon' => 'pencil',       'color' => '#1E40AF'],
        ];

        $subjectModels = [];
        foreach ($subjects as $s) {
            $subjectModels[] = Subject::create([
                'name'        => $s['name'],
                'slug'        => Str::slug($s['name']),
                'description' => 'Master '.$s['name'].' with our expert-led, structured SPM/IGCSE curriculum designed for top results.',
                'icon'        => $s['icon'],
                'color'       => $s['color'],
                'level'       => 'SPM',
                'is_active'   => true,
            ]);
        }

        // ─────────────────────────────────────────────────
        // 3. TEACHERS (5)
        // ─────────────────────────────────────────────────
        $teacherData = [
            ['name' => 'Cikgu Ahmad Farid',  'email' => 'teacher1@olympianscholars.my', 'qual' => 'B.Sc Mathematics, UM',                  'exp' => 12, 'spec' => 'Mathematics & Additional Mathematics', 'style' => 'Conceptual + Problem-Based Learning', 'rating' => 4.9, 'students' => 342],
            ['name' => 'Cikgu Siti Rahimah', 'email' => 'teacher2@olympianscholars.my', 'qual' => 'B.Sc Chemistry, UTM',                   'exp' => 9,  'spec' => 'Chemistry & Science',                  'style' => 'Visual + Mnemonics',                    'rating' => 4.8, 'students' => 289],
            ['name' => 'Cikgu Rajesh Kumar', 'email' => 'teacher3@olympianscholars.my', 'qual' => 'B.Ed Physics, UPM',                    'exp' => 11, 'spec' => 'Physics & Mathematics',                 'style' => 'Socratic + Whiteboard-Heavy',            'rating' => 4.7, 'students' => 310],
            ['name' => 'Cikgu Nurul Ain',    'email' => 'teacher4@olympianscholars.my', 'qual' => 'B.A. Bahasa Melayu, UKM',              'exp' => 8,  'spec' => 'Bahasa Melayu & English',               'style' => 'Structured + Essay Practice',            'rating' => 4.8, 'students' => 275],
            ['name' => 'Cikgu Lee Wei Ming', 'email' => 'teacher5@olympianscholars.my', 'qual' => 'B.Sc Biology & M.Sc Genetics, UM',     'exp' => 14, 'spec' => 'Biology',                               'style' => 'Research-Based + Storytelling',          'rating' => 4.9, 'students' => 398],
        ];

        $teacherModels = [];
        foreach ($teacherData as $idx => $td) {
            $user = User::create([
                'name'     => $td['name'],
                'email'    => $td['email'],
                'password' => Hash::make('password'),
                'role'     => 'teacher',
            ]);
            $teacher = Teacher::create([
                'user_id'          => $user->id,
                'qualification'    => $td['qual'],
                'experience_years' => $td['exp'],
                'bio'              => 'A passionate educator with '.$td['exp'].' years of teaching experience, specialising in '.$td['spec'].'. Known for making complex topics easy to grasp and helping students achieve A+ results in public examinations.',
                'teaching_style'   => $td['style'],
                'rating'           => $td['rating'],
                'total_students'   => $td['students'],
                'specialization'   => $td['spec'],
                'photo'            => 'teacher'.($idx+1).'.jpg',
            ]);
            $teacherModels[] = $teacher;
        }

        // Assign subjects to teachers
        $teacherModels[0]->subjects()->attach([$subjectModels[0]->id, $subjectModels[1]->id]);
        $teacherModels[1]->subjects()->attach([$subjectModels[4]->id, $subjectModels[2]->id]);
        $teacherModels[2]->subjects()->attach([$subjectModels[3]->id, $subjectModels[0]->id]);
        $teacherModels[3]->subjects()->attach([$subjectModels[6]->id, $subjectModels[7]->id]);
        $teacherModels[4]->subjects()->attach([$subjectModels[5]->id]);

        // ─────────────────────────────────────────────────
        // 4. COURSES
        // ─────────────────────────────────────────────────
        $courses = [];
        $courseAssignments = [
            [$teacherModels[0], $subjectModels[0], 'Mathematics SPM Mastery',            99.00,  'Mon & Wed 7–9 PM'],
            [$teacherModels[0], $subjectModels[1], 'Additional Mathematics SPM Pro',     109.00, 'Tue & Thu 7–9 PM'],
            [$teacherModels[1], $subjectModels[4], 'Chemistry SPM Excellence',            99.00, 'Sat 9 AM–12 PM'],
            [$teacherModels[1], $subjectModels[2], 'Science Form 3 & 4',                  89.00, 'Sun 9 AM–11 AM'],
            [$teacherModels[2], $subjectModels[3], 'Physics SPM Expert',                  99.00, 'Mon & Fri 8–10 PM'],
            [$teacherModels[3], $subjectModels[6], 'Bahasa Melayu SPM A+',                89.00, 'Sat 2–4 PM'],
            [$teacherModels[3], $subjectModels[7], 'English SPM Band 6',                  89.00, 'Sun 2–4 PM'],
            [$teacherModels[4], $subjectModels[5], 'Biology SPM Complete',                99.00, 'Wed & Sat 8–10 PM'],
        ];
        foreach ($courseAssignments as $ca) {
            $courses[] = Course::create([
                'subject_id'    => $ca[1]->id,
                'teacher_id'    => $ca[0]->id,
                'title'         => $ca[2],
                'description'   => 'A comprehensive SPM course covering all topics with exam strategies and past-year question practice.',
                'level'         => 'SPM',
                'price'         => $ca[3],
                'status'        => 'published',
                'total_lessons' => rand(24, 40),
                'schedule'      => $ca[4],
            ]);
        }

        // ─────────────────────────────────────────────────
        // 5. PARENT USERS (3)
        // ─────────────────────────────────────────────────
        $parents = [];
        $parentData = [
            ['name' => 'Encik Hassan Ismail',  'email' => 'parent1@olympianscholars.my'],
            ['name' => 'Puan Rosmah Abdullah', 'email' => 'parent2@olympianscholars.my'],
            ['name' => 'Mr. David Tan',        'email' => 'parent3@olympianscholars.my'],
        ];
        foreach ($parentData as $pd) {
            $parents[] = User::create([
                'name'     => $pd['name'],
                'email'    => $pd['email'],
                'password' => Hash::make('password'),
                'role'     => 'parent',
            ]);
        }

        // ─────────────────────────────────────────────────
        // 6. STUDENTS (20)
        // ─────────────────────────────────────────────────
        $studentNames = [
            ['Muhammad Hariz',   'student1@olympianscholars.my'],
            ['Aishah Nur',       'student2@olympianscholars.my'],
            ['Ravi Shankar',     'student3@olympianscholars.my'],
            ['Lim Xin Yi',       'student4@olympianscholars.my'],
            ['Nurul Izzah',      'student5@olympianscholars.my'],
            ['Ahmad Syafiq',     'student6@olympianscholars.my'],
            ['Priya Nair',       'student7@olympianscholars.my'],
            ['Tan Wei Jie',      'student8@olympianscholars.my'],
            ['Farhana Aziz',     'student9@olympianscholars.my'],
            ['Daniel Raj',       'student10@olympianscholars.my'],
            ['Zulaikha Hassan',  'student11@olympianscholars.my'],
            ['Kevin Wong',       'student12@olympianscholars.my'],
            ['Nur Syafiqah',     'student13@olympianscholars.my'],
            ['Arjun Pillai',     'student14@olympianscholars.my'],
            ['Sharifah Amirah',  'student15@olympianscholars.my'],
            ['Bryan Loh',        'student16@olympianscholars.my'],
            ['Siti Nabilah',     'student17@olympianscholars.my'],
            ['Vikram Nair',      'student18@olympianscholars.my'],
            ['Chong Mei Ling',   'student19@olympianscholars.my'],
            ['Haziq Azlan',      'student20@olympianscholars.my'],
        ];

        $studentModels = [];
        foreach ($studentNames as $idx => $sn) {
            $parentUserId = $idx < 10 ? $parents[intdiv($idx, 4)]->id : null;
            $user = User::create([
                'name'     => $sn[0],
                'email'    => $sn[1],
                'password' => Hash::make('password'),
                'role'     => 'student',
            ]);
            $studentModels[] = Student::create([
                'user_id'        => $user->id,
                'parent_user_id' => $parentUserId,
                'level'          => 'SPM',
                'total_points'   => rand(500, 3000),
            ]);
        }

        // ─────────────────────────────────────────────────
        // 7. ENROLLMENTS
        // ─────────────────────────────────────────────────
        foreach ($studentModels as $student) {
            $courseCount = rand(2, 4);
            $enrolled = collect($courses)->random($courseCount);
            foreach ($enrolled as $course) {
                $progress = rand(10, 95);
                Enrollment::create([
                    'student_id'        => $student->id,
                    'course_id'         => $course->id,
                    'progress_pct'      => $progress,
                    'completed_lessons' => (int)round($course->total_lessons * $progress / 100),
                    'enrolled_at'       => now()->subDays(rand(10, 120)),
                ]);
            }
        }

        // ─────────────────────────────────────────────────
        // 8. ASSIGNMENTS + QUIZZES
        // ─────────────────────────────────────────────────
        $assignmentTitles = ['Chapter Review', 'Past Year Practice', 'Formula Sheet Exercise', 'Essay Writing', 'Problem Set'];
        foreach ($courses as $course) {
            for ($i = 0; $i < 4; $i++) {
                $assignment = Assignment::create([
                    'course_id'   => $course->id,
                    'title'       => $assignmentTitles[$i % 5].' '.($i + 1),
                    'description' => 'Complete all questions and submit before the deadline.',
                    'due_date'    => now()->addDays(rand(-7, 14)),
                ]);
            }
            for ($q = 0; $q < 3; $q++) {
                Quiz::create([
                    'course_id'       => $course->id,
                    'title'           => 'Quiz '.($q + 1).' — Chapter '.($q + 1),
                    'total_questions' => 20,
                ]);
            }
        }

        // ─────────────────────────────────────────────────
        // 9. LIVE CLASSES
        // ─────────────────────────────────────────────────
        foreach ($courses as $course) {
            LiveClass::create([
                'course_id'        => $course->id,
                'title'            => $course->title.' — Live Session',
                'scheduled_at'     => now()->addDays(rand(1, 7))->setTime(19, 0),
                'duration_minutes' => 90,
                'meeting_url'      => 'https://meet.google.com/abc-defg-hij',
                'status'           => 'upcoming',
            ]);
        }

        // ─────────────────────────────────────────────────
        // 10. PAYMENTS
        // ─────────────────────────────────────────────────
        foreach ($studentModels as $student) {
            $statuses = ['paid', 'paid', 'paid', 'pending', 'overdue'];
            Payment::create([
                'user_id'        => $student->user_id,
                'description'    => 'Monthly Tuition Fee — July 2026',
                'amount'         => 99.00,
                'status'         => $statuses[array_rand($statuses)],
                'payment_method' => 'Online Banking',
                'paid_at'        => now()->subDays(rand(0, 30)),
            ]);
        }

        // ─────────────────────────────────────────────────
        // 10. TEACHING GIGS & SHIFTS
        // ─────────────────────────────────────────────────
        $gigsData = [
            [
                'subject_id' => 1,
                'title' => 'SPM Add Maths Paper 2 Intensive Revision Cover',
                'gig_type' => 'substitute_sos',
                'scheduled_at' => now()->addDays(1)->setHour(19)->setMinute(30),
                'duration_minutes' => 90,
                'payout_amount' => 60.00,
                'surge_bonus' => 20.00,
                'status' => 'open',
            ],
            [
                'subject_id' => 2,
                'title' => 'Form 5 Physics Electromagnetism Workshop',
                'gig_type' => 'scheduled_class',
                'scheduled_at' => now()->addDays(2)->setHour(20)->setMinute(0),
                'duration_minutes' => 60,
                'payout_amount' => 50.00,
                'surge_bonus' => 0.00,
                'status' => 'open',
            ],
            [
                'subject_id' => 3,
                'title' => 'IGCSE Chemistry Past Paper Drill Session',
                'gig_type' => 'scheduled_class',
                'scheduled_at' => now()->addDays(3)->setHour(18)->setMinute(30),
                'duration_minutes' => 60,
                'payout_amount' => 55.00,
                'surge_bonus' => 10.00,
                'status' => 'open',
            ],
        ];

        foreach ($gigsData as $gd) {
            \App\Models\TeachingGig::create($gd);
        }

        // ─────────────────────────────────────────────────
        // 11. BLOG POSTS
        // ─────────────────────────────────────────────────
        $posts = [
            [
                'title'    => '10 Proven Study Techniques to Ace SPM Mathematics',
                'slug'     => '10-proven-study-techniques-spm-mathematics',
                'category' => 'Study Tips',
                'excerpt'  => 'Discover evidence-backed strategies that top students use to conquer SPM Add Maths and score straight A+.',
            ],
            [
                'title'    => 'How to Write a Perfect Karangan for SPM Bahasa Melayu',
                'slug'     => 'perfect-karangan-spm-bahasa-melayu',
                'category' => 'Writing',
                'excerpt'  => 'From choosing the right format to polishing your vocabulary — a step-by-step guide for Band 6 essays.',
            ],
            [
                'title'    => 'IGCSE vs SPM: Which Path is Right for Your Child?',
                'slug'     => 'igcse-vs-spm-which-path',
                'category' => 'Parents Guide',
                'excerpt'  => 'We break down the key differences, costs, and future prospects of both major Malaysian exam systems.',
            ],
        ];
        foreach ($posts as $p) {
            BlogPost::create([
                'user_id'      => $admin->id,
                'title'        => $p['title'],
                'slug'         => $p['slug'],
                'excerpt'      => $p['excerpt'],
                'body'         => '<p>'.$p['excerpt'].'</p><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>',
                'category'     => $p['category'],
                'published_at' => now()->subDays(rand(1, 30)),
            ]);
        }

        // ─────────────────────────────────────────────────
        // 12. TESTIMONIALS
        // ─────────────────────────────────────────────────
        $testimonials = [
            ['name' => 'Muhammad Hariz', 'role' => 'SPM Student', 'quote' => 'I went from a C to an A+ in Mathematics after just 3 months with Olympian Scholars. The teachers are patient, thorough, and truly care about your progress.', 'rating' => 5.0],
            ['name' => 'Puan Rosmah', 'role' => 'Parent', 'quote' => 'As a parent, I can see the real improvement in my daughter\'s confidence and grades. The portal makes it easy for me to track her progress weekly.', 'rating' => 5.0],
            ['name' => 'Priya Nair', 'role' => 'IGCSE Student', 'quote' => 'The interactive lessons and live Q&A sessions made learning Chemistry so much less intimidating. I\'m now consistently scoring above 90% in my practice papers.', 'rating' => 4.8],
        ];
        foreach ($testimonials as $t) {
            Testimonial::create($t);
        }

        // ─────────────────────────────────────────────────
        // 13. RECORDED CLASS VAULT ("STUDY ASAP")
        // ─────────────────────────────────────────────────
        $recordings = [
            [
                'title' => 'SPM Add Maths: Quadratic Functions & Integration Shortcuts',
                'topic_name' => 'Additional Mathematics',
                'video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                'thumbnail' => 'record1.jpg',
                'duration_minutes' => 55,
                'views_count' => 342,
                'summary' => 'Comprehensive walkthrough of Paper 2 Integration questions with step-by-step exam techniques.',
            ],
            [
                'title' => 'SPM Physics: Electromagnetism & Transformer Calculations',
                'topic_name' => 'Physics',
                'video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                'thumbnail' => 'record2.jpg',
                'duration_minutes' => 60,
                'views_count' => 289,
                'summary' => 'Master Fleming\'s Left Hand Rule and transformer efficiency calculations with real past-year examples.',
            ],
            [
                'title' => 'SPM Chemistry: Redox Reactions & Electrochemistry Drills',
                'topic_name' => 'Chemistry',
                'video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                'thumbnail' => 'record3.jpg',
                'duration_minutes' => 45,
                'views_count' => 412,
                'summary' => 'In-depth explanation of oxidation states, half-equations, and voltaic cell problems.',
            ],
            [
                'title' => 'Form 5 Biology: Inheritance & Mendelian Genetics Exam Prep',
                'topic_name' => 'Biology',
                'video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                'thumbnail' => 'record4.jpg',
                'duration_minutes' => 50,
                'views_count' => 198,
                'summary' => 'Demystifying Punnett squares, dihybrid crosses, and pedigree chart analysis.',
            ],
        ];

        foreach ($recordings as $rec) {
            \App\Models\RecordedClass::create($rec);
        }

        // ─────────────────────────────────────────────────
        // 14. STUDENT PERSONAL TOPIC MASTERY (NON-COMPETITIVE)
        // ─────────────────────────────────────────────────
        $students = \App\Models\Student::all();
        $topics = [
            ['topic' => 'Integration & Calculus', 'pct' => 88],
            ['topic' => 'Quadratics & Functions', 'pct' => 92],
            ['topic' => 'Trigonometric Identities', 'pct' => 74],
            ['topic' => 'Vectors & Coordinate Geometry', 'pct' => 81],
        ];

        foreach ($students as $stu) {
            foreach ($topics as $tp) {
                \App\Models\StudentTopicMastery::create([
                    'student_id' => $stu->id,
                    'subject_id' => 1,
                    'topic_name' => $tp['topic'],
                    'mastery_percentage' => $tp['pct'],
                    'quizzes_attempted' => rand(4, 12),
                    'last_practiced_at' => now()->subDays(rand(0, 5)),
                ]);
            }
        }

        // 15. KPM OFFICIAL CURRICULUM SUBJECTS & DSKP TOPICS SEEDER
        $this->call(SubjectTopicsSeeder::class);

        // 16. KPM OFFICIAL COURSE CATALOG SEEDER
        $this->call(CourseCatalogSeeder::class);
    }
}
