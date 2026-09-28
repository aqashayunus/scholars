<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CourseCatalogSeeder extends Seeder
{
    public function run(): void
    {
        // ─────────────────────────────────────────────────────────────────
        // 1. ENSURE QUALIFIED SPECIALIST TEACHERS EXIST IN DATABASE
        // ─────────────────────────────────────────────────────────────────
        $extraTeachersData = [
            [
                'name'           => 'Cikgu Aisyah Sofia',
                'email'          => 'aisyah.sofia@olympianscholars.my',
                'qualification'  => 'B.Ed Early Childhood Education, UM',
                'experience'     => 7,
                'specialization' => 'Preschool Literacy & Early Childhood Development',
                'style'          => 'Montessori & Play-Based Interactive Learning',
                'rating'         => 4.95,
                'students'       => 180,
                'photo'          => 'images/teacher-aisyah.png',
            ],
            [
                'name'           => 'Cikgu Melissa Tan',
                'email'          => 'melissa.tan@olympianscholars.my',
                'qualification'  => 'Dip. Early Childhood & Phonics, OUM',
                'experience'     => 6,
                'specialization' => 'Early Numeracy & Phonics English/BM',
                'style'          => 'Multisensory & Storytelling Method',
                'rating'         => 4.88,
                'students'       => 165,
                'photo'          => 'images/teacher-melissa.png',
            ],
            [
                'name'           => 'Cikgu Hafiz Razak',
                'email'          => 'hafiz.razak@olympianscholars.my',
                'qualification'  => 'B.Ed Primary Education (KSSR), UPSI',
                'experience'     => 10,
                'specialization' => 'Primary Mathematics & Science KSSR UASA',
                'style'          => 'Inquiry-Based & Problem Solving',
                'rating'         => 4.92,
                'students'       => 240,
                'photo'          => 'images/teacher-daniel.png',
            ],
            [
                'name'           => 'Mr. Raymond Chew',
                'email'          => 'raymond.chew@olympianscholars.my',
                'qualification'  => 'B.Acc (Hons) & M.Econ, UM (Chartered Accountant)',
                'experience'     => 13,
                'specialization' => 'Prinsip Perakaunan & Ekonomi SPM',
                'style'          => 'Step-by-Step Ledger Analysis & Real Business Case Studies',
                'rating'         => 4.96,
                'students'       => 310,
                'photo'          => 'images/teacher-daniel.png',
            ],
            [
                'name'           => 'Mr. Daniel Wong',
                'email'          => 'daniel.wong@olympianscholars.my',
                'qualification'  => 'B.CompSc Software Engineering, UTM',
                'experience'     => 8,
                'specialization' => 'Sains Komputer SPM & Asas Sains Komputer (ASK)',
                'style'          => 'Hands-on Coding Python, SQL & Algorithms',
                'rating'         => 4.91,
                'students'       => 225,
                'photo'          => 'images/teacher-daniel.png',
            ],
            [
                'name'           => 'Ustaz Luqman Hakim',
                'email'          => 'luqman.hakim@olympianscholars.my',
                'qualification'  => 'B.A. Syariah & Islamic Studies, UKM / Al-Azhar',
                'experience'     => 9,
                'specialization' => 'Pendidikan Islam KSSR, KSSM & SPM',
                'style'          => 'Tadabbur, Sirah Nabawiyyah & Exam Answering Technique',
                'rating'         => 4.89,
                'students'       => 210,
                'photo'          => 'images/teacher-daniel.png',
            ],
            [
                'name'           => 'Puan Azlina Mansor',
                'email'          => 'azlina.mansor@olympianscholars.my',
                'qualification'  => 'B.A. Art & Design Education, UiTM',
                'experience'     => 11,
                'specialization' => 'Pendidikan Seni Visual & Reka Bentuk Teknologi (RBT)',
                'style'          => 'Creative Studio Workshops & Technical Portfolio Building',
                'rating'         => 4.87,
                'students'       => 195,
                'photo'          => 'images/teacher-aisyah.png',
            ],
        ];

        foreach ($extraTeachersData as $td) {
            $user = User::firstOrCreate(
                ['email' => $td['email']],
                [
                    'name'     => $td['name'],
                    'password' => Hash::make('password'),
                    'role'     => 'teacher',
                ]
            );

            Teacher::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'qualification'    => $td['qualification'],
                    'experience_years' => $td['experience'],
                    'bio'              => 'Dedicated specialist educator with '.$td['experience'].' years of teaching experience, specialising in '.$td['specialization'].'.',
                    'teaching_style'   => $td['style'],
                    'rating'           => $td['rating'],
                    'total_students'   => $td['students'],
                    'specialization'   => $td['specialization'],
                    'photo'            => $td['photo'],
                    'is_online'        => true,
                ]
            );
        }

        // Cache teacher models by email or name
        $teachers = Teacher::with('user')->get()->keyBy(fn($t) => $t->user->email);
        $teacherByRole = [
            'early_childhood' => $teachers['aisyah.sofia@olympianscholars.my'] ?? $teachers->first(),
            'phonics'         => $teachers['melissa.tan@olympianscholars.my'] ?? $teachers->first(),
            'primary_stem'    => $teachers['hafiz.razak@olympianscholars.my'] ?? $teachers['teacher1@olympianscholars.my'] ?? $teachers->first(),
            'math_expert'     => $teachers['teacher1@olympianscholars.my'] ?? $teachers->first(), // Cikgu Ahmad Farid
            'science_chem'    => $teachers['teacher2@olympianscholars.my'] ?? $teachers->first(), // Cikgu Siti Rahimah
            'physics'         => $teachers['teacher3@olympianscholars.my'] ?? $teachers->first(), // Cikgu Rajesh Kumar
            'languages'       => $teachers['teacher4@olympianscholars.my'] ?? $teachers->first(), // Cikgu Nurul Ain
            'biology'         => $teachers['teacher5@olympianscholars.my'] ?? $teachers->first(), // Cikgu Lee Wei Ming
            'accounting_econ' => $teachers['raymond.chew@olympianscholars.my'] ?? $teachers->first(),
            'computing_cs'    => $teachers['daniel.wong@olympianscholars.my'] ?? $teachers->first(),
            'islamic_moral'   => $teachers['luqman.hakim@olympianscholars.my'] ?? $teachers->first(),
            'arts_rbt'        => $teachers['azlina.mansor@olympianscholars.my'] ?? $teachers->first(),
        ];

        // ─────────────────────────────────────────────────────────────────
        // 2. RETRIEVE ALL KPM SUBJECTS
        // ─────────────────────────────────────────────────────────────────
        $subjectsByCode = Subject::all()->keyBy('code');
        $subjectsBySlug = Subject::all()->keyBy('slug');

        $findSubject = function($code, $fallbackSlug) use ($subjectsByCode, $subjectsBySlug) {
            return $subjectsByCode[$code] ?? $subjectsBySlug[$fallbackSlug] ?? Subject::first();
        };

        // ─────────────────────────────────────────────────────────────────
        // 3. MASTER CATALOG OF AUTHENTIC MALAYSIAN KPM COURSES
        // ─────────────────────────────────────────────────────────────────
        $catalogCourses = [
            // ── LEVEL 1: KINDERGARTEN (PRASEKOLAH / KSPK) ────────────────
            [
                'subject_code'  => 'KSPK-LIT-01',
                'fallback_slug' => 'literasi-awal-fonik',
                'teacher'       => $teacherByRole['early_childhood'],
                'title'         => 'Tadika Cemerlang: Literasi Awal & Fonik BM/English',
                'description'   => 'Program asas membaca, fonik bunyi huruf, dan penguasaan perbendaharaan kata dwibahasa untuk kanak-kanak prasekolah umur 4 hingga 6 tahun.',
                'level'         => 'Kindergarten',
                'price'         => 69.00,
                'total_lessons' => 24,
                'schedule'      => 'Mon & Wed 9:00–10:30 AM',
                'status'        => 'published',
            ],
            [
                'subject_code'  => 'KSPK-NUM-02',
                'fallback_slug' => 'awal-matematik-nombor',
                'teacher'       => $teacherByRole['phonics'],
                'title'         => 'Penerokaan Sains & Nombor Awal Prasekolah',
                'description'   => 'Konsep nombor 1-100, operasi tambah tolak bergambar, bentuk geometri asas, dan inkuiri penerokaan alam sekitar secara interaktif.',
                'level'         => 'Kindergarten',
                'price'         => 69.00,
                'total_lessons' => 24,
                'schedule'      => 'Tue & Thu 9:00–10:30 AM',
                'status'        => 'published',
            ],
            [
                'subject_code'  => 'KSPK-ART-04',
                'fallback_slug' => 'kreativiti-seni-visual',
                'teacher'       => $teacherByRole['arts_rbt'],
                'title'         => 'Kreativiti Seni & Perkembangan Sosioemosi Prasekolah',
                'description'   => 'Aktiviti seni kraf tangan, lukisan bebas, muzik berirama, dan modul sosioemosi membina keyakinan diri serta kemahiran motor halus.',
                'level'         => 'Kindergarten',
                'price'         => 59.00,
                'total_lessons' => 16,
                'schedule'      => 'Sat 10:00 AM–12:00 PM',
                'status'        => 'published',
            ],
            [
                'subject_code'  => 'KSPK-ISL-07',
                'fallback_slug' => 'pendidikan-islam-moral-prasekolah',
                'teacher'       => $teacherByRole['islamic_moral'],
                'title'         => 'Pendidikan Islam & Pembentukan Sahsiah Prasekolah',
                'description'   => 'Asas huruf hijaiyyah, hafazan surah lazim, adab harian, dan pendidikan moral berteraskan nilai murni untuk si cilik.',
                'level'         => 'Kindergarten',
                'price'         => 59.00,
                'total_lessons' => 16,
                'schedule'      => 'Sun 10:00 AM–12:00 PM',
                'status'        => 'published',
            ],

            // ── LEVEL 2: PRIMARY SCHOOL (KSSR SEMAKAN YEAR 1–6) ──────────
            [
                'subject_code'  => 'KSSR-BM-01',
                'fallback_slug' => 'bahasa-melayu-primary',
                'teacher'       => $teacherByRole['languages'],
                'title'         => 'Bahasa Melayu KSSR Tahun 1–3: Asas Membaca & Tatabahasa',
                'description'   => 'Kuasai tatabahasa asas, suku kata, membina ayat tunggal dan ayat majmuk, serta pemahaman teks cerita mudah format KSSR.',
                'level'         => 'Primary',
                'price'         => 79.00,
                'total_lessons' => 32,
                'schedule'      => 'Mon & Wed 3:00–4:30 PM',
                'status'        => 'published',
            ],
            [
                'subject_code'  => 'KSSR-BM-01',
                'fallback_slug' => 'bahasa-melayu-primary',
                'teacher'       => $teacherByRole['languages'],
                'title'         => 'Bahasa Melayu KSSR Tahun 4–6: Pemantapan Penulisan & UASA',
                'description'   => 'Teknik penulisan karangan Bahagian A, B, dan C, analisis petikan komsas, dan tatabahasa mantap persediaan Ujian Akhir Sesi Akademik (UASA).',
                'level'         => 'Primary',
                'price'         => 89.00,
                'total_lessons' => 36,
                'schedule'      => 'Tue & Thu 3:00–4:30 PM',
                'status'        => 'published',
            ],
            [
                'subject_code'  => 'KSSR-ENG-02',
                'fallback_slug' => 'english-primary',
                'teacher'       => $teacherByRole['languages'],
                'title'         => 'English Primary CEFR Starter (Year 1–3)',
                'description'   => 'Build solid CEFR A1 foundation: phonics, vocabulary expansion, listening comprehension, and guided writing drills.',
                'level'         => 'Primary',
                'price'         => 79.00,
                'total_lessons' => 32,
                'schedule'      => 'Sat 9:00–10:30 AM',
                'status'        => 'published',
            ],
            [
                'subject_code'  => 'KSSR-ENG-02',
                'fallback_slug' => 'english-primary',
                'teacher'       => $teacherByRole['languages'],
                'title'         => 'English Primary CEFR Masterclass (Year 4–6)',
                'description'   => 'Targeted CEFR A2 mastery with essay writing frameworks, reading comprehension analysis, and UASA exam preparation.',
                'level'         => 'Primary',
                'price'         => 89.00,
                'total_lessons' => 36,
                'schedule'      => 'Sat 11:00 AM–12:30 PM',
                'status'        => 'published',
            ],
            [
                'subject_code'  => 'KSSR-MAT-03',
                'fallback_slug' => 'mathematics-primary',
                'teacher'       => $teacherByRole['primary_stem'],
                'title'         => 'Matematik KSSR Tahun 1–3: Operasi Asas & Masalah Harian',
                'description'   => 'Pemahaman nilai tempat, operasi 4 asas (tambah, tolak, darab, bahagi), pecahan mudah, wang, masa dan penyelesaian masalah mudah.',
                'level'         => 'Primary',
                'price'         => 79.00,
                'total_lessons' => 32,
                'schedule'      => 'Mon & Wed 5:00–6:30 PM',
                'status'        => 'published',
            ],
            [
                'subject_code'  => 'KSSR-MAT-03',
                'fallback_slug' => 'mathematics-primary',
                'teacher'       => $teacherByRole['math_expert'],
                'title'         => 'Matematik KSSR Tahun 4–6: Penguasaan KBAT & Skor UASA',
                'description'   => 'Kuasai perpuluhan, peratus, nisbah, koordinat, bentuk 2D/3D, dan teknik menjawab soalan penyelesaian masalah KBAT bertaraf tinggi.',
                'level'         => 'Primary',
                'price'         => 89.00,
                'total_lessons' => 36,
                'schedule'      => 'Tue & Thu 5:00–6:30 PM',
                'status'        => 'published',
            ],
            [
                'subject_code'  => 'KSSR-SN-04',
                'fallback_slug' => 'science-primary',
                'teacher'       => $teacherByRole['science_chem'],
                'title'         => 'Sains KSSR Tahun 1–3: Inkuiri & Dunia Sains Awal',
                'description'   => 'Meneroka manusia, haiwan, tumbuhan, sifat bahan, dan daya melalui visual menarik dan pendekatan eksperimen mudah.',
                'level'         => 'Primary',
                'price'         => 79.00,
                'total_lessons' => 32,
                'schedule'      => 'Fri 3:00–4:30 PM',
                'status'        => 'published',
            ],
            [
                'subject_code'  => 'KSSR-SN-04',
                'fallback_slug' => 'science-primary',
                'teacher'       => $teacherByRole['science_chem'],
                'title'         => 'Sains KSSR Tahun 4–6: Kemahiran Saintifik & UASA',
                'description'   => 'Kemahiran Proses Sains (KPS), pemboleh ubah, hipotesis, tenaga, bumi & angkasa lepas, dan teknologi berpandukan format soalan UASA.',
                'level'         => 'Primary',
                'price'         => 89.00,
                'total_lessons' => 36,
                'schedule'      => 'Fri 5:00–6:30 PM',
                'status'        => 'published',
            ],
            [
                'subject_code'  => 'KSSR-SEJ-05',
                'fallback_slug' => 'sejarah-primary',
                'teacher'       => $teacherByRole['primary_stem'],
                'title'         => 'Sejarah KSSR Tahun 4–6: Warisan Negara & Kedaulatan',
                'description'   => 'Memahami zaman prasejarah, kerajaan melayu awal, tokoh terbilang, pejuang kemerdekaan, dan lambang kedaulatan Malaysia.',
                'level'         => 'Primary',
                'price'         => 75.00,
                'total_lessons' => 24,
                'schedule'      => 'Sun 9:00–10:30 AM',
                'status'        => 'published',
            ],
            [
                'subject_code'  => 'KSSR-RBT-09',
                'fallback_slug' => 'reka-bentuk-dan-teknologi-primary',
                'teacher'       => $teacherByRole['arts_rbt'],
                'title'         => 'RBT & Seni Visual KSSR: Reka Cipta & Projek Kreatif',
                'description'   => 'Bengkel praktikal mereka cipta pengaturcaraan blok Scratch asas, sistem akuaponik mini, jahitan, dan aplikasi asas teknologi.',
                'level'         => 'Primary',
                'price'         => 69.00,
                'total_lessons' => 24,
                'schedule'      => 'Sun 11:00 AM–12:30 PM',
                'status'        => 'published',
            ],

            // ── LEVEL 3: LOWER SECONDARY (KSSM FORM 1–3) ─────────────────
            [
                'subject_code'  => 'KSSM-BM-F13',
                'fallback_slug' => 'bahasa-melayu-lower-sec',
                'teacher'       => $teacherByRole['languages'],
                'title'         => 'Bahasa Melayu KSSM Tingkatan 1–3: Karangan & KOMSAS',
                'description'   => 'Pemantapan morfologi, sintaksis, peribahasa, novel KOMSAS (F1–F3), dan penulisan karangan respons terbuka format UASA.',
                'level'         => 'Lower Secondary',
                'price'         => 89.00,
                'total_lessons' => 36,
                'schedule'      => 'Mon & Wed 5:00–6:30 PM',
                'status'        => 'published',
            ],
            [
                'subject_code'  => 'KSSM-ENG-F13',
                'fallback_slug' => 'english-lower-sec',
                'teacher'       => $teacherByRole['languages'],
                'title'         => 'English KSSM Form 1–3: CEFR B1 Fluency & Writing',
                'description'   => 'CEFR B1 syllabus mastery: reading comprehension, summary writing, email/essay writing, and literature components.',
                'level'         => 'Lower Secondary',
                'price'         => 89.00,
                'total_lessons' => 36,
                'schedule'      => 'Tue & Thu 5:00–6:30 PM',
                'status'        => 'published',
            ],
            [
                'subject_code'  => 'KSSM-MAT-F13',
                'fallback_slug' => 'matematik-lower-sec',
                'teacher'       => $teacherByRole['math_expert'],
                'title'         => 'Matematik KSSM Tingkatan 1–3: Algebra & Geometri UASA',
                'description'   => 'Kuasai Ungkapan Algebra, Persamaan Linear, Teorem Pythagoras, Nisbah Trigonometri, Garis Lurus, dan Bulatan dengan teknik pantas.',
                'level'         => 'Lower Secondary',
                'price'         => 99.00,
                'total_lessons' => 36,
                'schedule'      => 'Mon & Wed 8:00–9:30 PM',
                'status'        => 'published',
            ],
            [
                'subject_code'  => 'KSSM-SN-F13',
                'fallback_slug' => 'sains-lower-sec',
                'teacher'       => $teacherByRole['physics'],
                'title'         => 'Sains KSSM Tingkatan 1–3: Penyiasatan Saintifik & KBAT',
                'description'   => 'Modul sains bersepadu merangkumi Sel & Sistem Badan, Jadual Berkala Unsur, Elektrik & Kemagnetan, Haba, dan Angkasa Lepas.',
                'level'         => 'Lower Secondary',
                'price'         => 99.00,
                'total_lessons' => 36,
                'schedule'      => 'Tue & Thu 8:00–9:30 PM',
                'status'        => 'published',
            ],
            [
                'subject_code'  => 'KSSM-SEJ-F13',
                'fallback_slug' => 'sejarah-lower-sec',
                'teacher'       => $teacherByRole['primary_stem'],
                'title'         => 'Sejarah KSSM Tingkatan 1–3: Rentetan Sejarah Malaysia',
                'description'   => 'Analisis peristiwa bersejarah Kesultanan Melayu Melaka, Negeri-negeri Melayu Bersekutu, Pentadbiran Barat, dan Kebangkitan Nasionalisme.',
                'level'         => 'Lower Secondary',
                'price'         => 79.00,
                'total_lessons' => 24,
                'schedule'      => 'Fri 8:00–9:30 PM',
                'status'        => 'published',
            ],
            [
                'subject_code'  => 'KSSM-GEO-F13',
                'fallback_slug' => 'geografi-lower-sec',
                'teacher'       => $teacherByRole['science_chem'],
                'title'         => 'Geografi KSSM Tingkatan 1–3: Kemahiran Geografi & Isu Global',
                'description'   => 'Kemahiran membaca peta topografi, graf, cuaca & iklim, bentuk muka bumi Malaysia, serta isu pemanasan global.',
                'level'         => 'Lower Secondary',
                'price'         => 79.00,
                'total_lessons' => 24,
                'schedule'      => 'Sat 3:00–4:30 PM',
                'status'        => 'published',
            ],
            [
                'subject_code'  => 'KSSM-ASK-F13',
                'fallback_slug' => 'asas-sains-komputer-lower-sec',
                'teacher'       => $teacherByRole['computing_cs'],
                'title'         => 'Asas Sains Komputer (ASK) Tingkatan 1–3: Python & Algoritma',
                'description'   => 'Konsep pemikiran komputasional, algoritma pseudokod & carta alir, pangkalan data, dan pengaturcaraan praktikal bahasa Python & HTML.',
                'level'         => 'Lower Secondary',
                'price'         => 95.00,
                'total_lessons' => 28,
                'schedule'      => 'Sat 5:00–6:30 PM',
                'status'        => 'published',
            ],
            [
                'subject_code'  => 'KSSM-RBT-F13',
                'fallback_slug' => 'reka-bentuk-dan-teknologi-lower-sec',
                'teacher'       => $teacherByRole['arts_rbt'],
                'title'         => 'Reka Bentuk dan Teknologi (RBT) Tingkatan 1–3: Mekatronik',
                'description'   => 'Prinsip reka bentuk sistem mekanikal, elektrik, elektronik, mikropengawal (Arduino), dan dokumentasi lakaran 3D.',
                'level'         => 'Lower Secondary',
                'price'         => 85.00,
                'total_lessons' => 28,
                'schedule'      => 'Sun 3:00–4:30 PM',
                'status'        => 'published',
            ],
            [
                'subject_code'  => 'KSSM-ISL-F13',
                'fallback_slug' => 'pendidikan-islam-moral-lower-sec',
                'teacher'       => $teacherByRole['islamic_moral'],
                'title'         => 'Pendidikan Islam & Moral KSSM Tingkatan 1–3',
                'description'   => 'Bidang al-Quran, Hadis, Akidah, Fiqah Ibadat, Sirah Tokoh Islam, dan Akhlak Islamiah format peperiksaan UASA.',
                'level'         => 'Lower Secondary',
                'price'         => 75.00,
                'total_lessons' => 24,
                'schedule'      => 'Sun 5:00–6:30 PM',
                'status'        => 'published',
            ],

            // ── LEVEL 4: UPPER SECONDARY KSSM SPM CORE ───────────────────
            [
                'subject_code'  => '1103',
                'fallback_slug' => 'bahasa-melayu-spm',
                'teacher'       => $teacherByRole['languages'],
                'title'         => 'Bahasa Melayu SPM 1103: Bengkel Karangan Cemerlang & Komsas A+',
                'description'   => 'Kuasai format Kertas 1 (Karangan Bahagian A & B), Kertas 2 (Morfologi, Sintaksis, Penyuntingan, Rumusan), dan Ujian Bertutur SPM.',
                'level'         => 'SPM',
                'price'         => 99.00,
                'total_lessons' => 40,
                'schedule'      => 'Sat 2:00–4:00 PM',
                'status'        => 'published',
            ],
            [
                'subject_code'  => '1119',
                'fallback_slug' => 'english-cefr-spm',
                'teacher'       => $teacherByRole['languages'],
                'title'         => 'English SPM 1119: CEFR C1/B2 Essay & Speaking Mastery',
                'description'   => 'Comprehensive CEFR SPM training covering Reading Comprehension (Paper 1), Guided & Extended Essay Writing (Paper 2), and Speaking (Paper 3).',
                'level'         => 'SPM',
                'price'         => 99.00,
                'total_lessons' => 40,
                'schedule'      => 'Sun 2:00–4:00 PM',
                'status'        => 'published',
            ],
            [
                'subject_code'  => '1449',
                'fallback_slug' => 'matematik-spm',
                'teacher'       => $teacherByRole['math_expert'],
                'title'         => 'Matematik SPM 1449: Skor A+ Kertas 1 & Kertas 2',
                'description'   => 'Fungsi Kuadratik, Penaakulan Logik, Matematik Pengguna (Insurans & Percukaian), Matriks, Sukatan Serakan, dan Vektor Kertas 2.',
                'level'         => 'SPM',
                'price'         => 109.00,
                'total_lessons' => 40,
                'schedule'      => 'Mon & Wed 8:00–10:00 PM',
                'status'        => 'published',
            ],
            [
                'subject_code'  => '1249',
                'fallback_slug' => 'sejarah-spm',
                'teacher'       => $teacherByRole['primary_stem'],
                'title'         => 'Sejarah SPM 1249: Teknik Menjawab KBAT & Struktur Sejarah',
                'description'   => 'Analisis tajuk fokus Tingkatan 4 & 5, formula menjawab soalan struktur dan esei KBAT berfakta tepat untuk jaminan skor lulus Cemerlang.',
                'level'         => 'SPM',
                'price'         => 99.00,
                'total_lessons' => 36,
                'schedule'      => 'Fri 8:00–10:00 PM',
                'status'        => 'published',
            ],
            [
                'subject_code'  => '1223',
                'fallback_slug' => 'pendidikan-islam-spm',
                'teacher'       => $teacherByRole['islamic_moral'],
                'title'         => 'Pendidikan Islam SPM 1223: Skor A+ Kertas Peperiksaan',
                'description'   => 'Penguasaan lengkap Ayat Kefahaman, Hadis Pilihan, Akidah, Fiqah Muamalat & Munakahat, serta Sejarah Tamadun Islam SPM.',
                'level'         => 'SPM',
                'price'         => 89.00,
                'total_lessons' => 32,
                'schedule'      => 'Thu 8:00–10:00 PM',
                'status'        => 'published',
            ],

            // ── LEVEL 5: UPPER SECONDARY KSSM SPM ELECTIVES (PACKAGES) ───
            [
                'subject_code'  => '4572',
                'fallback_slug' => 'matematik-tambahan-add-math',
                'teacher'       => $teacherByRole['math_expert'],
                'title'         => 'Matematik Tambahan SPM 4572: Kalkulus & Geometri Koordinat Pro',
                'description'   => 'Fungsi, Pembezaan, Pengamiran, Geometri Koordinat, Vektor, Fungsi Trigonometri, dan Taburan Kebarangkalian Kertas 1 & 2.',
                'level'         => 'SPM',
                'price'         => 119.00,
                'total_lessons' => 44,
                'schedule'      => 'Tue & Thu 8:00–10:00 PM',
                'status'        => 'published',
            ],
            [
                'subject_code'  => '4531',
                'fallback_slug' => 'fizik-physics',
                'teacher'       => $teacherByRole['physics'],
                'title'         => 'Fizik SPM 4531: Daya, Gelombang & Elektrik Kertas 1, 2 & 3',
                'description'   => 'Daya & Gerakan II, Tekanan, Haba, Gelombang, Cahaya & Optik, Keelektrikan, Kemagnetan, dan Fizik Nuklear & Kuantum KSSM.',
                'level'         => 'SPM',
                'price'         => 119.00,
                'total_lessons' => 40,
                'schedule'      => 'Mon & Fri 8:00–10:00 PM',
                'status'        => 'published',
            ],
            [
                'subject_code'  => '4541',
                'fallback_slug' => 'kimia-chemistry',
                'teacher'       => $teacherByRole['science_chem'],
                'title'         => 'Kimia SPM 4541: Asid Bes, Termokimia & Stoikiometri A+',
                'description'   => 'Konsep Mol, Jadual Berkala, Ikatan Kimia, Asid Bes & Garam, Kadar Tindak Balas, Sebatian Karbon, Termokimia, dan Kimia Polimer.',
                'level'         => 'SPM',
                'price'         => 119.00,
                'total_lessons' => 40,
                'schedule'      => 'Sat 9:00 AM–12:00 PM',
                'status'        => 'published',
            ],
            [
                'subject_code'  => '4551',
                'fallback_slug' => 'biologi-biology',
                'teacher'       => $teacherByRole['biology'],
                'title'         => 'Biologi SPM 4551: Fisiologi, Genetik & Ekosistem Lengkap',
                'description'   => 'Biologi Sel, Enzim, Respirasi Sel, Nutrisi, Pengangkutan dalam Manusia, Koordinasi & Gerak Balas, Pewarisan Mendel, dan Bioteknologi.',
                'level'         => 'SPM',
                'price'         => 119.00,
                'total_lessons' => 40,
                'schedule'      => 'Wed & Sat 8:00–10:00 PM',
                'status'        => 'published',
            ],
            [
                'subject_code'  => '3756',
                'fallback_slug' => 'prinsip-perakaunan',
                'teacher'       => $teacherByRole['accounting_econ'],
                'title'         => 'Prinsip Perakaunan SPM 3756: Lejar, Imbangan Duga & Penyata Kewangan',
                'description'   => 'Kitaran Perakaunan, Lejar, Buku Tunai Runcit, Penyata Kewangan Milikan Tunggal, Perkongsian, Kelab & Persatuan, dan Kos Pengeluaran.',
                'level'         => 'SPM',
                'price'         => 109.00,
                'total_lessons' => 36,
                'schedule'      => 'Sun 9:00 AM–12:00 PM',
                'status'        => 'published',
            ],
            [
                'subject_code'  => '3767',
                'fallback_slug' => 'ekonomi',
                'teacher'       => $teacherByRole['accounting_econ'],
                'title'         => 'Ekonomi SPM 3767: Mikroekonomi, Makroekonomi & Perdagangan',
                'description'   => 'Konsep Asas Ekonomi, Pasaran & Keanjalan Harga, Wang & Bank, Pendapatan Individu, Inflasi, Imbangan Pembayaran, dan Perdagangan Luar.',
                'level'         => 'SPM',
                'price'         => 99.00,
                'total_lessons' => 32,
                'schedule'      => 'Sat 4:00–6:00 PM',
                'status'        => 'published',
            ],
            [
                'subject_code'  => '3766',
                'fallback_slug' => 'perniagaan',
                'teacher'       => $teacherByRole['accounting_econ'],
                'title'         => 'Perniagaan SPM 3766: Pengurusan Strategik & Rancangan Perniagaan',
                'description'   => 'Tujuan Perniagaan, Trend Semasa, Pengurusan Sumber Manusia, Sumber Pembiayaan, Pemasaran, dan Penyediaan Rancangan Perniagaan.',
                'level'         => 'SPM',
                'price'         => 99.00,
                'total_lessons' => 32,
                'schedule'      => 'Sun 4:00–6:00 PM',
                'status'        => 'published',
            ],
            [
                'subject_code'  => '3770',
                'fallback_slug' => 'sains-komputer',
                'teacher'       => $teacherByRole['computing_cs'],
                'title'         => 'Sains Komputer SPM 3770: Python, Database SQL & Sistem Komputer',
                'description'   => 'Pengaturcaraan Lanjutan Python/Java, Pangkalan Data Hubungan (SQL, Normalisasi 1NF–3NF), Interaksi Manusia-Komputer, dan Etika Siber.',
                'level'         => 'SPM',
                'price'         => 119.00,
                'total_lessons' => 40,
                'schedule'      => 'Tue & Thu 6:00–8:00 PM',
                'status'        => 'published',
            ],
            [
                'subject_code'  => '2611',
                'fallback_slug' => 'pendidikan-seni-visual-spm',
                'teacher'       => $teacherByRole['arts_rbt'],
                'title'         => 'Pendidikan Seni Visual SPM 2611: Teori Seni & Folio Kerja Kursus',
                'description'   => 'Sejarah & Apresiasi Seni Visual, Reka Bentuk Grafik, Seni Halus (Catan, Cetakan, Arca), Kraf Tradisional, dan bimbingan Kerja Kursus Kertas 3.',
                'level'         => 'SPM',
                'price'         => 89.00,
                'total_lessons' => 30,
                'schedule'      => 'Sun 10:00 AM–12:00 PM',
                'status'        => 'published',
            ],
        ];

        // ─────────────────────────────────────────────────────────────────
        // 4. SEED / UPDATE REAL COURSES IN DATABASE
        // ─────────────────────────────────────────────────────────────────
        $createdCourses = [];
        foreach ($catalogCourses as $cc) {
            $subject = $findSubject($cc['subject_code'], $cc['fallback_slug']);
            $teacher = $cc['teacher'];

            // Ensure teacher is linked to this subject in teacher_subject pivot table
            if ($subject && $teacher && !$teacher->subjects()->where('subject_id', $subject->id)->exists()) {
                $teacher->subjects()->attach($subject->id);
            }

            $course = Course::updateOrCreate(
                [
                    'title' => $cc['title'],
                ],
                [
                    'subject_id'    => $subject ? $subject->id : 1,
                    'teacher_id'    => $teacher ? $teacher->id : 1,
                    'description'   => $cc['description'],
                    'level'         => $cc['level'],
                    'price'         => $cc['price'],
                    'status'        => $cc['status'],
                    'total_lessons' => $cc['total_lessons'],
                    'schedule'      => $cc['schedule'],
                ]
            );

            $createdCourses[] = $course;
        }

        // ─────────────────────────────────────────────────────────────────
        // 5. DISTRIBUTE REAL STUDENT ENROLLMENTS ACROSS COURSES
        // ─────────────────────────────────────────────────────────────────
        $students = Student::all();
        if ($students->isNotEmpty()) {
            foreach ($createdCourses as $course) {
                // Randomly enroll 4 to 12 students in each course if not already enrolled
                $sampledStudents = $students->random(min($students->count(), rand(4, 12)));
                foreach ($sampledStudents as $stu) {
                    Enrollment::firstOrCreate(
                        [
                            'student_id' => $stu->id,
                            'course_id'  => $course->id,
                        ],
                        [
                            'enrolled_at'       => now()->subDays(rand(10, 90)),
                            'progress_pct'      => rand(45, 95),
                            'completed_lessons' => rand(6, $course->total_lessons ?: 20),
                        ]
                    );
                }
            }
        }
    }
}
