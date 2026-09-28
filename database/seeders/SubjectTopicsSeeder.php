<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\SubjectTopic;
use App\Models\SubjectSubtopic;
use App\Models\Student;
use App\Models\StudentSubtopicProgress;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SubjectTopicsSeeder extends Seeder
{
    public function run(): void
    {
        // ─────────────────────────────────────────────────────────────────────
        // 1. MALAYSIAN KPM OFFICIAL CURRICULUM DEFINITIONS
        // ─────────────────────────────────────────────────────────────────────
        $kpmCurriculum = [
            // ═════════════════════════════════════════════════════════════════
            // LEVEL 1: KINDERGARTEN (PRASEKOLAH / TADIKA - KSPK)
            // ═════════════════════════════════════════════════════════════════
            [
                'name'           => 'Literasi Awal (Early Literacy & Phonics)',
                'code'           => 'KPM-KSPK-LIT',
                'education_type' => 'kindergarten',
                'stage'          => 'Preschool',
                'form_year'      => 'Preschool',
                'category'       => 'Early Development',
                'stream_package' => 'General',
                'level'          => 'Kindergarten',
                'icon'           => 'pencil',
                'color'          => '#F59E0B',
                'description'    => 'Pengenalan abjad, fonik, bunyi vokal & konsonan, suku kata terbuka, dan kemahiran membaca awal.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Pengenalan Huruf & Fonik Asas (A–Z)',
                        'description'    => 'Mengenal bentuk huruf besar, huruf kecil, dan bunyi fonik asas.',
                        'subtopics'      => [
                            ['title' => 'Pengecaman Huruf Vokal (a, e, i, o, u)', 'default_status' => 'Mastered'],
                            ['title' => 'Bunyi Fonik Konsonan Asas (b, c, d, k, m, s, t)', 'default_status' => 'In Progress'],
                            ['title' => 'Nyanyian Lagu Abjad & Aktiviti Visual', 'default_status' => 'Mastered'],
                        ]
                    ],
                    [
                        'chapter_number' => 2,
                        'title'          => 'Pembinaan Suku Kata Terbuka (KV) & Perkataan Mudah',
                        'description'    => 'Menggabungkan huruf konsonan dan vokal untuk membina perkataan bermakna.',
                        'subtopics'      => [
                            ['title' => 'Gabungan Suku Kata Terbuka (ba, bi, bu, ca, ci, cu)', 'default_status' => 'In Progress'],
                            ['title' => 'Mengeja Perkataan 2 Suku Kata Mudah (bola, buku, mata)', 'default_status' => 'In Progress'],
                            ['title' => 'Latihan Memadankan Perkataan dengan Gambar', 'default_status' => 'Needs Practice'],
                        ]
                    ],
                    [
                        'chapter_number' => 3,
                        'title'          => 'Kemahiran Membaca Cerita Pendek & Sajak Berirama',
                        'description'    => 'Membaca ayat mudah berpandukan gambar dan buku cerita besar.',
                        'subtopics'      => [
                            ['title' => 'Membaca Ayat Tunggal Pendek', 'default_status' => 'In Progress'],
                            ['title' => 'Intonasi & Penghayatan Cerita Kanak-Kanak', 'default_status' => 'Not Started'],
                        ]
                    ],
                ]
            ],
            [
                'name'           => 'Numerasi Awal (Early Numeracy & Counting)',
                'code'           => 'KPM-KSPK-NUM',
                'education_type' => 'kindergarten',
                'stage'          => 'Preschool',
                'form_year'      => 'Preschool',
                'category'       => 'Early Development',
                'stream_package' => 'General',
                'level'          => 'Kindergarten',
                'icon'           => 'calculator',
                'color'          => '#D97706',
                'description'    => 'Konsep angka, membilang objek 1–20, operasi tambah/tolak asas melalui visual, corak dan bentuk.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Konsep Nombor & Membilang Objek 1 hingga 20',
                        'description'    => 'Pengecaman simbol angka, padanan kuantiti objek, dan turutan nombor.',
                        'subtopics'      => [
                            ['title' => 'Membilang Objek 1 hingga 10 secara Menaik & Menurun', 'default_status' => 'Mastered'],
                            ['title' => 'Pengecaman & Penulisan Angka 11 hingga 20', 'default_status' => 'In Progress'],
                            ['title' => 'Perbandingan Kuantiti (Banyak, Sedikit, Sama Banyak)', 'default_status' => 'Mastered'],
                        ]
                    ],
                    [
                        'chapter_number' => 2,
                        'title'          => 'Pengecaman Bentuk Geometri, Saiz & Pola Warna',
                        'description'    => 'Mengenal bulat, segi empat, segi tiga, dan membina pola corak berulang.',
                        'subtopics'      => [
                            ['title' => 'Bentuk Asas Geometri 2D di Persekitaran', 'default_status' => 'Mastered'],
                            ['title' => 'Penyusunan Objek Mengikut Saiz & Panjang', 'default_status' => 'In Progress'],
                            ['title' => 'Corak & Pola Berulang (AB, AAB, ABC)', 'default_status' => 'In Progress'],
                        ]
                    ],
                    [
                        'chapter_number' => 3,
                        'title'          => 'Asas Tambah & Tolak Konseptual Menggunakan Bahan Konkrit',
                        'description'    => 'Penggabungan dan pengasingan kumpulan objek dalam lingkungan 10.',
                        'subtopics'      => [
                            ['title' => 'Konsep Menggabung Kumpulan (Asas Tambah)', 'default_status' => 'In Progress'],
                            ['title' => 'Konsep Mengasing Kumpulan (Asas Tolak)', 'default_status' => 'Needs Practice'],
                        ]
                    ],
                ]
            ],
            [
                'name'           => 'Bahasa Melayu Awal',
                'code'           => 'KPM-KSPK-BM',
                'education_type' => 'kindergarten',
                'stage'          => 'Preschool',
                'form_year'      => 'Preschool',
                'category'       => 'Early Development',
                'stream_package' => 'General',
                'level'          => 'Kindergarten',
                'icon'           => 'book-open',
                'color'          => '#EA580C',
                'description'    => 'Kemahiran mendengar, bertutur, membaca perkataan mudah dan menulis huruf dengan koordinasi motor halus.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Kemahiran Mendengar & Bertutur Santun',
                        'description'    => 'Mendengar arahan mudah dan bertutur dengan sebutan jelas.',
                        'subtopics'      => [
                            ['title' => 'Ucapan Bertatasusila & Kata Sapaan Harian', 'default_status' => 'Mastered'],
                            ['title' => 'Bercerita Mengenai Diri, Keluarga & Rakan', 'default_status' => 'In Progress'],
                        ]
                    ],
                    [
                        'chapter_number' => 2,
                        'title'          => 'Pra-Tulisan & Pembentukan Huruf',
                        'description'    => 'Latihan koordinasi mata-tangan dan pegangan pensel yang betul.',
                        'subtopics'      => [
                            ['title' => 'Garisan Lurus, Lengkung & Bentuk Bebas', 'default_status' => 'Mastered'],
                            ['title' => 'Menulis Huruf Abjad dengan Cara yang Betul', 'default_status' => 'In Progress'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'English for Preschool',
                'code'           => 'KPM-KSPK-ENG',
                'education_type' => 'kindergarten',
                'stage'          => 'Preschool',
                'form_year'      => 'Preschool',
                'category'       => 'Early Development',
                'stream_package' => 'General',
                'level'          => 'Kindergarten',
                'icon'           => 'pencil',
                'color'          => '#2563EB',
                'description'    => 'Phonics sounds, simple sight words, nursery rhymes, conversational English and daily vocabulary.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Phonics Sounds & Sight Words',
                        'description'    => 'Recognising phonemes, blending simple CVC words (cat, dog, sun).',
                        'subtopics'      => [
                            ['title' => 'Single Letter Sounds & Jolly Phonics Actions', 'default_status' => 'Mastered'],
                            ['title' => 'Blending CVC Words (bat, pin, cup)', 'default_status' => 'In Progress'],
                            ['title' => 'High Frequency Sight Words (I, see, the, a)', 'default_status' => 'In Progress'],
                        ]
                    ],
                    [
                        'chapter_number' => 2,
                        'title'          => 'Everyday Vocabulary & Greetings',
                        'description'    => 'Colors, animals, family members and polite expressions in English.',
                        'subtopics'      => [
                            ['title' => 'Common Greetings & Self Introductions', 'default_status' => 'Mastered'],
                            ['title' => 'Naming Classroom Objects & Actions', 'default_status' => 'In Progress'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'Penerokaan Sains Awal',
                'code'           => 'KPM-KSPK-SCI',
                'education_type' => 'kindergarten',
                'stage'          => 'Preschool',
                'form_year'      => 'Preschool',
                'category'       => 'Early Development',
                'stream_package' => 'General',
                'level'          => 'Kindergarten',
                'icon'           => 'flask',
                'color'          => '#059669',
                'description'    => 'Pemerhatian alam sekitar, 5 deria manusia, haiwan, tumbuhan, kitaran cuaca dan daya tarikan graviti asas.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Lima Deria Manusia & Eksplorasi Alam',
                        'description'    => 'Meneroka fungsi mata, telinga, lidah, hidung dan kulit dalam kehidupan.',
                        'subtopics'      => [
                            ['title' => 'Fungsi 5 Deria & Pengecaman Tekstur/Bau/Rasa', 'default_status' => 'Mastered'],
                            ['title' => 'Membuat Pemerhatian & Pengelasan Mudah', 'default_status' => 'In Progress'],
                        ]
                    ],
                    [
                        'chapter_number' => 2,
                        'title'          => 'Dunia Haiwan, Tumbuhan & Fenomena Alam',
                        'description'    => 'Habitat haiwan, bahagian tumbuhan, kitaran air dan cuaca.',
                        'subtopics'      => [
                            ['title' => 'Bahagian Tubuh Haiwan & Cara Bergerak', 'default_status' => 'Mastered'],
                            ['title' => 'Keperluan Asas Benda Hidup (Air, Cahaya, Udara)', 'default_status' => 'In Progress'],
                            ['title' => 'Eksperimen Objek Tenggelam dan Timbul', 'default_status' => 'Needs Practice'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'Kreativiti & Seni Visual',
                'code'           => 'KPM-KSPK-ART',
                'education_type' => 'kindergarten',
                'stage'          => 'Preschool',
                'form_year'      => 'Preschool',
                'category'       => 'Early Development',
                'stream_package' => 'General',
                'level'          => 'Kindergarten',
                'icon'           => 'sparkles',
                'color'          => '#EC4899',
                'description'    => 'Lukisan, capan warna, kraf tangan origami, muzik, pergerakan kreatif dan apresiasi seni kanak-kanak.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Warna Asas, Lukisan Bebas & Capan',
                        'description'    => 'Mencampur warna asas dan menghasilkan karya kreatif capan jari/daun.',
                        'subtopics'      => [
                            ['title' => 'Eksplorasi Warna Asas & Campuran Warna', 'default_status' => 'Mastered'],
                            ['title' => 'Teknik Capan, Renjikan & Kolaj Bahan Alam', 'default_status' => 'In Progress'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'Perkembangan Fizikal & Kesihatan',
                'code'           => 'KPM-KSPK-PJK',
                'education_type' => 'kindergarten',
                'stage'          => 'Preschool',
                'form_year'      => 'Preschool',
                'category'       => 'Early Development',
                'stream_package' => 'General',
                'level'          => 'Kindergarten',
                'icon'           => 'heart',
                'color'          => '#10B981',
                'description'    => 'Koordinasi motor kasar dan halus, postur tubuh, amalan kebersihan diri dan pemakanan sihat.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Koordinasi Motor Kasar & Kebersihan Diri',
                        'description'    => 'Pergerakan lokomotor, imbangan badan, dan amalan mencuci tangan serta gigi.',
                        'subtopics'      => [
                            ['title' => 'Pergerakan Berlari, Melompat & Mengimbang', 'default_status' => 'Mastered'],
                            ['title' => 'Amalan 7 Langkah Basuh Tangan & Penjagaan Diri', 'default_status' => 'Mastered'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'Perkembangan Sosioemosi & Nilai Murni',
                'code'           => 'KPM-KSPK-PSE',
                'education_type' => 'kindergarten',
                'stage'          => 'Preschool',
                'form_year'      => 'Preschool',
                'category'       => 'Early Development',
                'stream_package' => 'General',
                'level'          => 'Kindergarten',
                'icon'           => 'smile',
                'color'          => '#8B5CF6',
                'description'    => 'Mengenal emosi diri, empati, perkongsian, bekerjasama dalam kumpulan dan kemahiran berinteraksi positif.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Pengurusan Emosi Diri & Hubungan Rakan Sebaya',
                        'description'    => 'Mengekspresikan perasaan secara selamat dan beradab sopan.',
                        'subtopics'      => [
                            ['title' => 'Mengenal Emosi (Gembira, Sedih, Takut, Marah)', 'default_status' => 'Mastered'],
                            ['title' => 'Berkongsi Permainan & Bekerjasama dalam Kumpulan', 'default_status' => 'Mastered'],
                        ]
                    ]
                ]
            ],

            // ═════════════════════════════════════════════════════════════════
            // LEVEL 2: PRIMARY SCHOOL (KSSR SEMAKAN - YEAR 1 TO YEAR 6)
            // ═════════════════════════════════════════════════════════════════
            [
                'name'           => 'Bahasa Melayu (KSSR)',
                'code'           => 'KPM-KSSR-BM',
                'education_type' => 'primary',
                'stage'          => 'Primary',
                'form_year'      => 'Year 1–6',
                'category'       => 'Core',
                'stream_package' => 'General',
                'level'          => 'Primary',
                'icon'           => 'book-open',
                'color'          => '#1E40AF',
                'description'    => 'Kurikulum Bahasa Melayu Sekolah Rendah KSSR Semakan berfokuskan Tatabahasa, Pemahaman, Penulisan Karangan dan KOMSAS.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Tatabahasa & Golongan Kata KSSR',
                        'description'    => 'Kata Nama, Kata Kerja, Kata Adjektif, Kata Tugas dan Imbuhan Awalan/Akhiran.',
                        'subtopics'      => [
                            ['title' => 'Kata Nama Am & Kata Nama Khas', 'default_status' => 'Mastered'],
                            ['title' => 'Kata Kerja Transitif & Tak Transitif', 'default_status' => 'In Progress'],
                            ['title' => 'Kata Adjektif & Kata Sendi Nama', 'default_status' => 'In Progress'],
                            ['title' => 'Imbuhan Awalan, Akhiran & Apitan', 'default_status' => 'Needs Practice'],
                        ]
                    ],
                    [
                        'chapter_number' => 2,
                        'title'          => 'Kemahiran Membaca & Pemahaman Petikan',
                        'description'    => 'Teknik menjawab soalan pemahaman petikan prosa dan puisi berpandukan kata kunci.',
                        'subtopics'      => [
                            ['title' => 'Mencari Idea Utama & Idea Sampingan', 'default_status' => 'Mastered'],
                            ['title' => 'Soalan Pemahaman Bertumpu & Bercapah (KBAT)', 'default_status' => 'In Progress'],
                        ]
                    ],
                    [
                        'chapter_number' => 3,
                        'title'          => 'Penulisan Karangan Berformat & Respons Terbuka',
                        'description'    => 'Format surat kiriman, catatan harian, laporan, berita dan karangan perbincangan/cerita.',
                        'subtopics'      => [
                            ['title' => 'Bina Ayat Berdasarkan Gambar & Kata Kunci', 'default_status' => 'Mastered'],
                            ['title' => 'Format Surat Kiriman Rasmi & Tidak Rasmi', 'default_status' => 'In Progress'],
                            ['title' => 'Penulisan Karangan Naratif & Fakta', 'default_status' => 'Needs Practice'],
                        ]
                    ],
                    [
                        'chapter_number' => 4,
                        'title'          => 'Kosa Kata, Peribahasa & Pembetulan Ayat',
                        'description'    => 'Simpulan bahasa, perumpamaan dan pembetulan kesalahan ejaan/tatabahasa.',
                        'subtopics'      => [
                            ['title' => 'Simpulan Bahasa & Peribahasa Popular', 'default_status' => 'In Progress'],
                            ['title' => 'Membetulkan Kesalahan Ejaan & Tanda Baca', 'default_status' => 'Mastered'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'English (KSSR / CEFR)',
                'code'           => 'KPM-KSSR-ENG',
                'education_type' => 'primary',
                'stage'          => 'Primary',
                'form_year'      => 'Year 1–6',
                'category'       => 'Core',
                'stream_package' => 'General',
                'level'          => 'Primary',
                'icon'           => 'pencil',
                'color'          => '#2563EB',
                'description'    => 'CEFR-aligned Primary English curriculum developing Reading, Writing, Grammar, Listening, and Speaking proficiency.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Grammar & Vocabulary Mastery',
                        'description'    => 'Nouns, pronouns, tenses, subject-verb agreement, and adjectives.',
                        'subtopics'      => [
                            ['title' => 'Simple Present & Past Tenses', 'default_status' => 'Mastered'],
                            ['title' => 'Subject-Verb Agreement Rules', 'default_status' => 'In Progress'],
                            ['title' => 'Prepositions of Place and Time', 'default_status' => 'Mastered'],
                            ['title' => 'Comparative & Superlative Adjectives', 'default_status' => 'In Progress'],
                        ]
                    ],
                    [
                        'chapter_number' => 2,
                        'title'          => 'Reading Comprehension & Critical Thinking',
                        'description'    => 'Linear and non-linear text comprehension, skimming, scanning, and vocabulary in context.',
                        'subtopics'      => [
                            ['title' => 'Factual Information Extraction & Scanning', 'default_status' => 'Mastered'],
                            ['title' => 'Contextual Meaning & Inference Questions', 'default_status' => 'In Progress'],
                        ]
                    ],
                    [
                        'chapter_number' => 3,
                        'title'          => 'Guided Writing & Creative Composition',
                        'description'    => 'Email/message writing, story writing with picture stimuli, and descriptive paragraphs.',
                        'subtopics'      => [
                            ['title' => 'Short Message / Email Writing (Part 6 Format)', 'default_status' => 'In Progress'],
                            ['title' => 'Guided Story Writing with Visual Cues', 'default_status' => 'Needs Practice'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'Matematik / Mathematics (KSSR)',
                'code'           => 'KPM-KSSR-MATH',
                'education_type' => 'primary',
                'stage'          => 'Primary',
                'form_year'      => 'Year 1–6',
                'category'       => 'Core',
                'stream_package' => 'General',
                'level'          => 'Primary',
                'icon'           => 'calculator',
                'color'          => '#0284C7',
                'description'    => 'Kurikulum Matematik Sekolah Rendah merangkumi Nombor & Operasi, Sukatan & Geometri, Perkaitan & Algebra, dan Statistik.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Nombor Bulat & Operasi Asas',
                        'description'    => 'Nilai tempat, nilai digit, pembundaran, operasi tambah, tolak, darab, bahagi dan operasi bergabung.',
                        'subtopics'      => [
                            ['title' => 'Nilai Tempat, Nilai Digit & Pembundaran Nombor', 'default_status' => 'Mastered'],
                            ['title' => 'Operasi Tambah, Tolak, Darab & Bahagi Nombor Bulat', 'default_status' => 'Mastered'],
                            ['title' => 'Operasi Bergabung & Masalah Berayat (KBAT)', 'default_status' => 'In Progress'],
                        ]
                    ],
                    [
                        'chapter_number' => 2,
                        'title'          => 'Pecahan, Perpuluhan & Peratus',
                        'description'    => 'Pecahan wajar, pecahan tak wajar, nombor bercampur, perpuluhan dan penukaran kepada peratusan.',
                        'subtopics'      => [
                            ['title' => 'Operasi Tambah & Tolak Pecahan Wajar', 'default_status' => 'In Progress'],
                            ['title' => 'Pendaraban & Pembahagian Pecahan dengan Nombor Bulat', 'default_status' => 'Needs Practice'],
                            ['title' => 'Operasi Perpuluhan & Penukaran Pecahan-Peratus', 'default_status' => 'In Progress'],
                        ]
                    ],
                    [
                        'chapter_number' => 3,
                        'title'          => 'Wang, Masa & Waktu',
                        'description'    => 'Pengurusan kewangan, untung rugi, faedah, tempoh masa, sistem 12 & 24 jam.',
                        'subtopics'      => [
                            ['title' => 'Penyelesaian Masalah Wang, Diskaun & Untung/Rugi', 'default_status' => 'In Progress'],
                            ['title' => 'Pertukaran Unit Masa & Pengiraan Tempoh', 'default_status' => 'Mastered'],
                        ]
                    ],
                    [
                        'chapter_number' => 4,
                        'title'          => 'Ukuran Panjang, Jisim & Isi Padu Cecair',
                        'description'    => 'Pertukaran unit metrik, operasi asas ukuran dan penyelesaian masalah harian.',
                        'subtopics'      => [
                            ['title' => 'Pertukaran Unit (mm, cm, m, km, g, kg, ml, l)', 'default_status' => 'In Progress'],
                            ['title' => 'Masalah Berayat Melibatkan Ukuran Campuran', 'default_status' => 'Needs Practice'],
                        ]
                    ],
                    [
                        'chapter_number' => 5,
                        'title'          => 'Ruang, Geometri & Pengendalian Data',
                        'description'    => 'Perimeter, luas, isi padu bentuk 3D, koordinat Cartes, piktograf dan carta palang.',
                        'subtopics'      => [
                            ['title' => 'Pengiraan Perimeter & Luas Bentuk 2D Gabungan', 'default_status' => 'In Progress'],
                            ['title' => 'Isi Padu Kubus & Kuboid', 'default_status' => 'Needs Practice'],
                            ['title' => 'Membaca & Mentafsir Carta Palang serta Carta Pai', 'default_status' => 'Mastered'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'Sains / Science (KSSR)',
                'code'           => 'KPM-KSSR-SCI',
                'education_type' => 'primary',
                'stage'          => 'Primary',
                'form_year'      => 'Year 1–6',
                'category'       => 'Core',
                'stream_package' => 'General',
                'level'          => 'Primary',
                'icon'           => 'flask',
                'color'          => '#0D9488',
                'description'    => 'Inkuiri saintifik, sains hayat, sains fizikal, sains bahan, bumi dan angkasa, serta teknologi dan kehidupan lestari.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Kemahiran Saintifik & Peraturan Bilik Sains',
                        'description'    => 'Kemahiran proses sains (memerhati, mengelas, mengukur, membuat inferens, meramal, mengeksperimen).',
                        'subtopics'      => [
                            ['title' => '12 Kemahiran Proses Sains (KPS) & Manipulatif', 'default_status' => 'Mastered'],
                            ['title' => 'Mengenal Pasti Pemboleh Ubah (DPM, DGB, DPU)', 'default_status' => 'In Progress'],
                        ]
                    ],
                    [
                        'chapter_number' => 2,
                        'title'          => 'Sains Hayat: Manusia, Haiwan & Tumbuhan',
                        'description'    => 'Organ pernafasan, sistem peredaran darah, rantai makanan, fotosintesis dan pembiakan.',
                        'subtopics'      => [
                            ['title' => 'Sistem Pernafasan & Peredaran Darah Manusia', 'default_status' => 'Mastered'],
                            ['title' => 'Rantai Makanan, Siratan Makanan & Habitat', 'default_status' => 'Mastered'],
                            ['title' => 'Fotosintesis & Kitaran Hidup Tumbuhan', 'default_status' => 'In Progress'],
                        ]
                    ],
                    [
                        'chapter_number' => 3,
                        'title'          => 'Sains Fizikal & Sains Bahan',
                        'description'    => 'Daya dan kesan daya, kelajuan, litar elektrik, pembiasan cahaya, sifat bahan dan pengaratan.',
                        'subtopics'      => [
                            ['title' => 'Daya Geseran, Graviti & Pengiraan Kelajuan', 'default_status' => 'In Progress'],
                            ['title' => 'Litar Bersiri, Litar Selari & Konduktor Elektrik', 'default_status' => 'In Progress'],
                            ['title' => 'Pantulan & Pembiasan Cahaya', 'default_status' => 'Needs Practice'],
                            ['title' => 'Sifat Bahan, Keadaan Jirim & Kitaran Air Semula Jadi', 'default_status' => 'Mastered'],
                        ]
                    ],
                    [
                        'chapter_number' => 4,
                        'title'          => 'Bumi, Angkasa Lepas & Teknologi',
                        'description'    => 'Sistem suria, gerhana bulan/matahari, fasa bulan, buruj dan mesin ringkas/kompleks.',
                        'subtopics'      => [
                            ['title' => 'Putaran & Peredaran Bumi (Siang Malam & Musim)', 'default_status' => 'In Progress'],
                            ['title' => 'Fenomena Gerhana Bulan & Gerhana Matahari', 'default_status' => 'Needs Practice'],
                            ['title' => 'Mesin Ringkas (Tuas, Takal, Gear, Baji, Satah Condong)', 'default_status' => 'Mastered'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'Sejarah (KSSR Tahun 4–6)',
                'code'           => 'KPM-KSSR-SEJ',
                'education_type' => 'primary',
                'stage'          => 'Primary',
                'form_year'      => 'Year 4–6',
                'category'       => 'Core',
                'stream_package' => 'General',
                'level'          => 'Primary',
                'icon'           => 'academic-cap',
                'color'          => '#B45309',
                'description'    => 'Mempelajari warisan sejarah Malaysia, zaman prasejarah, kerajaan Melayu awal, tokoh terbilang dan kemerdekaan negara.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Mari Belajar Sejarah & Zaman Air Batu',
                        'description'    => 'Pengertian sejarah, sumber primer/sekunder, dan perubahan bentuk fizikal bumi Zaman Air Batu.',
                        'subtopics'      => [
                            ['title' => 'Pengertian Sejarah & Kaedah Penyelidikan Sejarah', 'default_status' => 'Mastered'],
                            ['title' => 'Garis Masa Zaman Air Batu & Kesannya di Asia Tenggara', 'default_status' => 'In Progress'],
                        ]
                    ],
                    [
                        'chapter_number' => 2,
                        'title'          => 'Zaman Prasejarah & Kerajaan Melayu Awal',
                        'description'    => 'Tahap Paleolitik, Mesolitik, Neolitik, Logam dan kemasyhuran kerajaan Kedah Tua, Chitu, Gangga Nagara.',
                        'subtopics'      => [
                            ['title' => 'Ciri Kehidupan Zaman Prasejarah di Malaysia', 'default_status' => 'Mastered'],
                            ['title' => 'Kedudukan & Aktiviti Ekonomi Kerajaan Melayu Awal', 'default_status' => 'In Progress'],
                        ]
                    ],
                    [
                        'chapter_number' => 3,
                        'title'          => 'Tokoh Terbilang Melaka & Pembentukan Malaysia',
                        'description'    => 'Parameswara, Bendahara Tun Perak, Laksamana Hang Tuah, kemerdekaan 1957 dan pembentukan 1963.',
                        'subtopics'      => [
                            ['title' => 'Peranan Parameswara & Pengasasan Melaka', 'default_status' => 'Mastered'],
                            ['title' => 'Ketokohan Tun Perak & Hang Tuah', 'default_status' => 'In Progress'],
                            ['title' => 'Peristiwa Pengisytiharan Kemerdekaan 1957', 'default_status' => 'Needs Practice'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'Pendidikan Islam & Moral (KSSR)',
                'code'           => 'KPM-KSSR-PIM',
                'education_type' => 'primary',
                'stage'          => 'Primary',
                'form_year'      => 'Year 1–6',
                'category'       => 'Core',
                'stream_package' => 'General',
                'level'          => 'Primary',
                'icon'           => 'sun',
                'color'          => '#16A34A',
                'description'    => 'Penghayatan nilai akhlak Islam, tilawah al-Quran, sirah nabawiyyah serta 14 nilai universal Pendidikan Moral.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Asas Akidah, Ibadah & 14 Nilai Moral Sejagat',
                        'description'    => 'Rukun Iman/Islam, solat fardu, bersuci serta nilai kepercayaan kepada Tuhan, kasih sayang, hormat.',
                        'subtopics'      => [
                            ['title' => 'Asas Rukun Iman & Ibadah Bersuci/Solat', 'default_status' => 'Mastered'],
                            ['title' => 'Nilai Tanggungjawab Diri & Kasih Sayang Keluarga', 'default_status' => 'Mastered'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'Pendidikan Jasmani & Kesihatan (PJPK KSSR)',
                'code'           => 'KPM-KSSR-PJPK',
                'education_type' => 'primary',
                'stage'          => 'Primary',
                'form_year'      => 'Year 1–6',
                'category'       => 'Core',
                'stream_package' => 'General',
                'level'          => 'Primary',
                'icon'           => 'heart',
                'color'          => '#059669',
                'description'    => 'Gimnastik asas, permainan serangan/jaring/memadang, olahraga asas, pemakanan sihat dan pertolongan cemas.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Kemahiran Motor, Sukan Permainan & Kesihatan Diri',
                        'description'    => 'Pergerakan lokomotor, manipulasi alatan, pemakanan seimbang dan keselamatan diri.',
                        'subtopics'      => [
                            ['title' => 'Kemahiran Asas Bola Sepak, Bola Jaring & Badminton', 'default_status' => 'Mastered'],
                            ['title' => 'Piramid Makanan Malaysia & Pencegahan Penyakit', 'default_status' => 'Mastered'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'Reka Bentuk & Teknologi (RBT KSSR)',
                'code'           => 'KPM-KSSR-RBT',
                'education_type' => 'primary',
                'stage'          => 'Primary',
                'form_year'      => 'Year 4–6',
                'category'       => 'Elective',
                'stream_package' => 'General',
                'level'          => 'Primary',
                'icon'           => 'cpu-chip',
                'color'          => '#6366F1',
                'description'    => 'Pengenalan teknologi, keselamatan bengkel, asas pengaturcaraan Scratch, teknologi pertanian akuaponik dan kraf.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Asas Reka Bentuk & Pengaturcaraan Komputer',
                        'description'    => 'Prinsip reka bentuk produk, lakaran idea 2D/3D dan algoritma Scratch.',
                        'subtopics'      => [
                            ['title' => 'Peraturan Keselamatan Bengkel & Alatan Tangan', 'default_status' => 'Mastered'],
                            ['title' => 'Lakaran Reka Bentuk Produk & Bahan Gabungan', 'default_status' => 'In Progress'],
                            ['title' => 'Asas Pengaturcaraan Scratch & Carta Alir', 'default_status' => 'Needs Practice'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'Pendidikan Seni Visual (PSV KSSR)',
                'code'           => 'KPM-KSSR-PSV',
                'education_type' => 'primary',
                'stage'          => 'Primary',
                'form_year'      => 'Year 1–6',
                'category'       => 'Elective',
                'stream_package' => 'General',
                'level'          => 'Primary',
                'icon'           => 'sparkles',
                'color'          => '#A855F7',
                'description'    => 'Empat bidang seni visual: Menggambar, Membuat Corak & Rekaan, Membentuk & Membuat Binaan, serta Mengenal Kraf Tradisional.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Bahasa Seni Visual & Aplikasi 4 Bidang Seni',
                        'description'    => 'Unsur seni (garisan, rupa, bentuk, warna) dan prinsip rekaan (harmoni, kontra, penegasan).',
                        'subtopics'      => [
                            ['title' => 'Aplikasi Unsur Seni & Prinsip Rekaan dalam Lukisan', 'default_status' => 'Mastered'],
                            ['title' => 'Teknik Catan, Arca Timbul & Kraf Tradisional', 'default_status' => 'In Progress'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'Pendidikan Muzik (KSSR)',
                'code'           => 'KPM-KSSR-MUZ',
                'education_type' => 'primary',
                'stage'          => 'Primary',
                'form_year'      => 'Year 1–6',
                'category'       => 'Elective',
                'stream_package' => 'General',
                'level'          => 'Primary',
                'icon'           => 'musical-note',
                'color'          => '#F43F5E',
                'description'    => 'Bahasa muzik, ritma, notasi muzik, kemahiran nyanyian harmoni, rekoder dan apresiasi muzik tradisional.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Notasi Muzik, Nyanyian & Permainan Alat Perkusi',
                        'description'    => 'Membaca baluk not muzik, tempo, dinamik dan penjarian rekoder.',
                        'subtopics'      => [
                            ['title' => 'Pengecaman Not pada Baluk & Tanda Rehat', 'default_status' => 'Mastered'],
                            ['title' => 'Teknik Penjarian Asas Rekoder & Nyanyian Ensembel', 'default_status' => 'In Progress'],
                        ]
                    ]
                ]
            ],

            // ═════════════════════════════════════════════════════════════════
            // LEVEL 3A: SECONDARY - LOWER SECONDARY (KSSM FORM 1 TO 3)
            // ═════════════════════════════════════════════════════════════════
            [
                'name'           => 'Bahasa Melayu (KSSM Form 1–3)',
                'code'           => 'KPM-KSSM-BM-L',
                'education_type' => 'secondary',
                'stage'          => 'Lower Secondary',
                'form_year'      => 'Form 1–3',
                'category'       => 'Core',
                'stream_package' => 'General',
                'level'          => 'Lower Secondary',
                'icon'           => 'book-open',
                'color'          => '#1E40AF',
                'description'    => 'Kurikulum KSSM Menengah Rendah Tingkatan 1–3 merangkumi Sistem Bahasa, Morfologi, Sintaksis, Ulasan, Karangan dan KOMSAS.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Morfologi & Sintaksis Menengah Rendah',
                        'description'    => 'Golongan kata nama, kata kerja, kata tugas, pola ayat dasar dan ayat majmuk.',
                        'subtopics'      => [
                            ['title' => 'Golongan Kata & Pembentukan Kata Terbitan/Ganda/Majmuk', 'default_status' => 'Mastered'],
                            ['title' => 'Pola Ayat Dasar & Ayat Majmuk (Gabungan/Pancangan)', 'default_status' => 'In Progress'],
                            ['title' => 'Ayat Aktif, Ayat Pasif & Cakap Ajuk/Pindah', 'default_status' => 'In Progress'],
                            ['title' => 'Penyuntingan Kesalahan Bahasa & Ejaan', 'default_status' => 'Needs Practice'],
                        ]
                    ],
                    [
                        'chapter_number' => 2,
                        'title'          => 'Pemahaman Petikan & Antologi KOMSAS',
                        'description'    => 'Pantun, syair, sajak, cerpen, drama dan novel Tingkatan 1, 2, dan 3.',
                        'subtopics'      => [
                            ['title' => 'Tema, Persoalan & Nilai Pengajaran Puisi Tradisional/Moden', 'default_status' => 'Mastered'],
                            ['title' => 'Teknik Plot, Watak Perwatakan & Latar Cerpen/Drama', 'default_status' => 'In Progress'],
                            ['title' => 'Analisis Novel Menengah Rendah (Soalan KBAT)', 'default_status' => 'In Progress'],
                        ]
                    ],
                    [
                        'chapter_number' => 3,
                        'title'          => 'Pemindahan Maklumat (Ulasan) & Karangan Format',
                        'description'    => 'Menulis ulasan berpandukan bahan rangsangan dan penulisan karangan respons terbuka.',
                        'subtopics'      => [
                            ['title' => 'Teknik Menulis Ulasan Lengkap (Tersurat + Tersirat)', 'default_status' => 'Mastered'],
                            ['title' => 'Karangan Syarahan, Pidato, Ceramah & Wawancara', 'default_status' => 'In Progress'],
                            ['title' => 'Karangan Rencana, Surat Rasmi & Perbincangan Isu', 'default_status' => 'Needs Practice'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'English (KSSM Form 1–3 / CEFR)',
                'code'           => 'KPM-KSSM-ENG-L',
                'education_type' => 'secondary',
                'stage'          => 'Lower Secondary',
                'form_year'      => 'Form 1–3',
                'category'       => 'Core',
                'stream_package' => 'General',
                'level'          => 'Lower Secondary',
                'icon'           => 'pencil',
                'color'          => '#2563EB',
                'description'    => 'CEFR-aligned Lower Secondary English developing Advanced Grammar, Reading Comprehension, Essays, and Literature.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Advanced Grammar, Clauses & Cohesive Devices',
                        'description'    => 'Complex tenses, relative clauses, modals, passive voice and transitions.',
                        'subtopics'      => [
                            ['title' => 'Past Perfect & Continuous Tenses Mastery', 'default_status' => 'Mastered'],
                            ['title' => 'Relative Clauses & Conditional Sentences (Types 1–3)', 'default_status' => 'In Progress'],
                            ['title' => 'Direct vs Indirect Speech Conversions', 'default_status' => 'In Progress'],
                        ]
                    ],
                    [
                        'chapter_number' => 2,
                        'title'          => 'Critical Reading, Information Synthesis & Summary',
                        'description'    => 'Comprehending non-fiction articles, identifying implicit ideas, and summary writing.',
                        'subtopics'      => [
                            ['title' => 'Reading for Gist, Detail & Implicit Meaning', 'default_status' => 'Mastered'],
                            ['title' => 'Summary Writing: Point Selection & Paraphrasing', 'default_status' => 'Needs Practice'],
                        ]
                    ],
                    [
                        'chapter_number' => 3,
                        'title'          => 'Continuous Essay Writing & Formal Text Types',
                        'description'    => 'Narrative compositions, descriptive essays, formal emails and report writing.',
                        'subtopics'      => [
                            ['title' => 'Formal / Informal Letter & Email Compositions', 'default_status' => 'Mastered'],
                            ['title' => 'Article & School Magazine Report Formats', 'default_status' => 'In Progress'],
                            ['title' => 'Story Writing with Dynamic Vocabulary and Plot', 'default_status' => 'Needs Practice'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'Matematik / Mathematics (KSSM Form 1–3)',
                'code'           => 'KPM-KSSM-MATH-L',
                'education_type' => 'secondary',
                'stage'          => 'Lower Secondary',
                'form_year'      => 'Form 1–3',
                'category'       => 'Core',
                'stream_package' => 'General',
                'level'          => 'Lower Secondary',
                'icon'           => 'calculator',
                'color'          => '#0284C7',
                'description'    => 'Kurikulum Matematik Menengah Rendah KSSM: Nombor Nisbah, Ungkapan & Persamaan Algebra, Geometri, Trigonometri, dan Statistik.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Nombor Nisbah, Faktor, Gandaan & Kuasa',
                        'description'    => 'Integriti nombor nisbah, GSTK, FSTB, kuasa dua, punca kuasa dua, kuasa tiga dan punca kuasa tiga.',
                        'subtopics'      => [
                            ['title' => 'Operasi Nombor Nisbah & Garis Nombor', 'default_status' => 'Mastered'],
                            ['title' => 'Faktor Sepunya Terbesar (FSTB) & Gandaan Sepunya Terkecil (GSTK)', 'default_status' => 'Mastered'],
                            ['title' => 'Kuasa Dua, Kuasa Tiga & Punca Kuasa', 'default_status' => 'Mastered'],
                        ]
                    ],
                    [
                        'chapter_number' => 2,
                        'title'          => 'Ungkapan Algebra, Persamaan & Ketaksamaan Linear',
                        'description'    => 'Konsep pemboleh ubah, pengembangan/pemfaktoran algebra, persamaan linear serentak dua pemboleh ubah.',
                        'subtopics'      => [
                            ['title' => 'Operasi Ungkapan Algebra & Pecahan Algebra', 'default_status' => 'Mastered'],
                            ['title' => 'Penyelesaian Persamaan Linear Serentak (Penggantian & Penghapusan)', 'default_status' => 'In Progress'],
                            ['title' => 'Ketaksamaan Linear Serentak dalam Satu Pemboleh Ubah', 'default_status' => 'In Progress'],
                            ['title' => 'Rumus Algebra & Penukaran Perkara Rumus', 'default_status' => 'Needs Practice'],
                        ]
                    ],
                    [
                        'chapter_number' => 3,
                        'title'          => 'Geometri, Poligon, Bulatan & Teorem Pythagoras',
                        'description'    => 'Sudut pedalaman/peluaran poligon, lilitan dan luas bulatan, sudut pada lilitan bulatan, teorem Pythagoras.',
                        'subtopics'      => [
                            ['title' => 'Teorem Pythagoras & Akas Teorem Pythagoras', 'default_status' => 'Mastered'],
                            ['title' => 'Sudut Pedalaman & Sudut Peluaran Poligon Sekata', 'default_status' => 'In Progress'],
                            ['title' => 'Ciri Geometri Bulatan & Sudut dalam Bulatan', 'default_status' => 'Needs Practice'],
                        ]
                    ],
                    [
                        'chapter_number' => 4,
                        'title'          => 'Geometri Koordinat, Garis Lurus & Graf Fungsi',
                        'description'    => 'Jarak, titik tengah, kecerunan garis lurus, persamaan garis lurus y = mx + c, dan tafsiran graf.',
                        'subtopics'      => [
                            ['title' => 'Kecerunan Garis Lurus (Formula m = (y2-y1)/(x2-x1))', 'default_status' => 'Mastered'],
                            ['title' => 'Persamaan Garis Lurus & Titik Persilangan', 'default_status' => 'In Progress'],
                            ['title' => 'Melukis & Mentafsir Graf Fungsi (Linear, Kuadratik, Kubik, Salingan)', 'default_status' => 'Needs Practice'],
                        ]
                    ],
                    [
                        'chapter_number' => 5,
                        'title'          => 'Nisbah Trigonometri, Statistik & Kebarangkalian',
                        'description'    => 'Sinus, Kosinus, Tangen segitiga bersudut tegak, sukatan kecenderungan memusat (min, mod, median) dan kebarangkalian.',
                        'subtopics'      => [
                            ['title' => 'Nisbah Trigonometri (Sin, Kos, Tan) Sudut Tirus', 'default_status' => 'In Progress'],
                            ['title' => 'Min, Mod, Median Data Terkumpul & Tak Terkumpul', 'default_status' => 'Mastered'],
                            ['title' => 'Ruang Sampel & Kebarangkalian Peristiwa Mudah', 'default_status' => 'In Progress'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'Sains / Science (KSSM Form 1–3)',
                'code'           => 'KPM-KSSM-SCI-L',
                'education_type' => 'secondary',
                'stage'          => 'Lower Secondary',
                'form_year'      => 'Form 1–3',
                'category'       => 'Core',
                'stream_package' => 'General',
                'level'          => 'Lower Secondary',
                'icon'           => 'flask',
                'color'          => '#0D9488',
                'description'    => 'Kurikulum Sains Menengah Rendah merangkumi Sel, Jirim, Jadual Berkala, Daya, Tenaga, Elektrik, Kereaktifan Logam & Angkasa.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Biologi: Sel, Koordinasi, Nutrisi & Biodiversiti',
                        'description'    => 'Struktur sel haiwan/tumbuhan, homeostasis, sistem pencernaan, respirasi manusia dan pengelasan organisma.',
                        'subtopics'      => [
                            ['title' => 'Struktur Sel Haiwan & Tumbuhan serta Fungsinya', 'default_status' => 'Mastered'],
                            ['title' => 'Homeostasis dalam Benda Hidup (Suhu & Air)', 'default_status' => 'Mastered'],
                            ['title' => 'Sistem Pencernaan Manusia & Ujian Makanan', 'default_status' => 'In Progress'],
                            ['title' => 'Sistem Respirasi & Pertukaran Gas Alveolus', 'default_status' => 'In Progress'],
                        ]
                    ],
                    [
                        'chapter_number' => 2,
                        'title'          => 'Kimia: Jirim, Jadual Berkala, Udara & Sebatian',
                        'description'    => 'Tiga keadaan jirim, teori zarah, atom, unsur, sebatian, campuran, komposisi udara dan tindak balas pembakaran.',
                        'subtopics'      => [
                            ['title' => 'Susunan Zarah dalam Pepejal, Cecair & Gas', 'default_status' => 'Mastered'],
                            ['title' => 'Jadual Berkala Unsur (Logam, Bukan Logam, Separuh Logam)', 'default_status' => 'In Progress'],
                            ['title' => 'Kaedah Pengasingan Campuran (Penurasan, Penyulingan, Kromatografi)', 'default_status' => 'In Progress'],
                        ]
                    ],
                    [
                        'chapter_number' => 3,
                        'title'          => 'Fizik: Daya, Gerakan, Tekanan, Tenaga & Elektrik',
                        'description'    => 'Jenis-jenis daya, tuas, tekanan bendalir, prinsip keabadian tenaga, hukum Ohm dan kemagnetan.',
                        'subtopics'      => [
                            ['title' => 'Daya, Momen Daya & Prinsip Tuas', 'default_status' => 'Mastered'],
                            ['title' => 'Tekanan Atmosfera, Gas & Cecair', 'default_status' => 'In Progress'],
                            ['title' => 'Litar Elektrik, Hukum Ohm (V = IR) & Rintangan', 'default_status' => 'Needs Practice'],
                            ['title' => 'Keelektromagnetan & Penjanaan Tenaga Elektrik', 'default_status' => 'Needs Practice'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'Sejarah (KSSM Form 1–3)',
                'code'           => 'KPM-KSSM-SEJ-L',
                'education_type' => 'secondary',
                'stage'          => 'Lower Secondary',
                'form_year'      => 'Form 1–3',
                'category'       => 'Core',
                'stream_package' => 'General',
                'level'          => 'Lower Secondary',
                'icon'           => 'academic-cap',
                'color'          => '#B45309',
                'description'    => 'Sejarah Kerajaan Alam Melayu, Kesultanan Melayu Melaka, Johor-Riau, campur tangan British dan penentangan tokoh tempatan.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Kerajaan Alam Melayu & Kesultanan Melayu Melaka',
                        'description'    => 'Kemasyhuran kerajaan Funan, Champa, Srivijaya, Majapahit, sistem pentadbiran dan undang-undang Melaka.',
                        'subtopics'      => [
                            ['title' => 'Ciri-Ciri Kerajaan Alam Melayu (Pemerintahan, Ekonomi, Agama)', 'default_status' => 'Mastered'],
                            ['title' => 'Sistem Pembesar Empat Lipatan Kesultanan Melayu Melaka', 'default_status' => 'Mastered'],
                            ['title' => 'Hukum Kanun Melaka & Undang-Undang Laut Melaka', 'default_status' => 'In Progress'],
                        ]
                    ],
                    [
                        'chapter_number' => 2,
                        'title'          => 'Kesultanan Johor Riau & Kerajaan-Kerajaan Melayu',
                        'description'    => 'Pengasasan Johor Riau, perang tiga segi, pusat perdagangan dan kemasyhuran negeri-negeri Melayu.',
                        'subtopics'      => [
                            ['title' => 'Pengasasan & Cabaran Kesultanan Johor Riau', 'default_status' => 'Mastered'],
                            ['title' => 'Kemakmuran Pelabuhan Riau & Karya Agung Persuratan', 'default_status' => 'In Progress'],
                        ]
                    ],
                    [
                        'chapter_number' => 3,
                        'title'          => 'Kedatangan Kuasa Barat & Penentangan Tokoh Tempatan',
                        'description'    => 'Faktor kedatangan Barat, campur tangan di NNS, NNMB, NNMTB, Sarawak, Sabah dan perjuangan pejuang tanah air.',
                        'subtopics'      => [
                            ['title' => 'Strategi Kuasa Barat Meluaskan Kuasa (Perjanjian, Tipu Helah, Paksaan)', 'default_status' => 'In Progress'],
                            ['title' => 'Penentangan Dol Said, Rentap, Mat Salleh, Tok Janggut & Dato Maharajalela', 'default_status' => 'In Progress'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'Geografi (KSSM Form 1–3)',
                'code'           => 'KPM-KSSM-GEO-L',
                'education_type' => 'secondary',
                'stage'          => 'Lower Secondary',
                'form_year'      => 'Form 1–3',
                'category'       => 'Core',
                'stream_package' => 'General',
                'level'          => 'Lower Secondary',
                'icon'           => 'globe-alt',
                'color'          => '#0F766E',
                'description'    => 'Kemahiran geografi peta topografi, bentuk muka bumi, cuaca & iklim, taburan penduduk, sumber alam dan isu alam sekitar.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Kemahiran Geografi: Skala, Jarak, Bearing & Peta',
                        'description'    => 'Mengukur bearing sudutan, membaca kontur peta topografi dan simbol peta.',
                        'subtopics'      => [
                            ['title' => 'Bearing Sudutan & Pengiraan Jarak Sebenar di Peta', 'default_status' => 'Mastered'],
                            ['title' => 'Tafsiran Garisan Kontur & Keratan Rentas Topografi', 'default_status' => 'In Progress'],
                        ]
                    ],
                    [
                        'chapter_number' => 2,
                        'title'          => 'Geografi Fizikal & Manusia di Malaysia',
                        'description'    => 'Banjaran gunung, lembangan sungai, iklim Khatulistiwa, kepadatan penduduk dan jaringan pengangkutan.',
                        'subtopics'      => [
                            ['title' => 'Bentuk Muka Bumi Malaysia & Kepentingannya', 'default_status' => 'Mastered'],
                            ['title' => 'Pengaruh Cuaca & Iklim Terhadap Aktiviti Manusia', 'default_status' => 'In Progress'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'Pendidikan Islam & Moral (KSSM Form 1–3)',
                'code'           => 'KPM-KSSM-PIM-L',
                'education_type' => 'secondary',
                'stage'          => 'Lower Secondary',
                'form_year'      => 'Form 1–3',
                'category'       => 'Core',
                'stream_package' => 'General',
                'level'          => 'Lower Secondary',
                'icon'           => 'sun',
                'color'          => '#15803D',
                'description'    => 'Kefahaman Al-Quran & Hadis, Akidah, Fiqh Ibadah, Sirah Nabawiyyah serta pemupukan moral insan bermoral.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Tilawah Quran, Hadis, Fiqh & Moral Individu',
                        'description'    => 'Ayat bacaan dan kefahaman terpilih, hukum tajwid, solat berjemaah, zakat dan integriti moral.',
                        'subtopics'      => [
                            ['title' => 'Kefahaman Ayat Al-Quran & Pengajaran Hadis Terpilih', 'default_status' => 'Mastered'],
                            ['title' => 'Fiqh Bersuci, Solat Qasar/Jamak & Puasa', 'default_status' => 'Mastered'],
                            ['title' => 'Pendidikan Moral: Integriti Diri & Nilai Perpaduan', 'default_status' => 'In Progress'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'Pendidikan Jasmani & Kesihatan (PJPK Form 1–3)',
                'code'           => 'KPM-KSSM-PJPK-L',
                'education_type' => 'secondary',
                'stage'          => 'Lower Secondary',
                'form_year'      => 'Form 1–3',
                'category'       => 'Core',
                'stream_package' => 'General',
                'level'          => 'Lower Secondary',
                'icon'           => 'heart',
                'color'          => '#047857',
                'description'    => 'Standard Kecergasan Fizikal Kebangsaan (SEGAK), sukan olahraga, permainan taktikal dan kesihatan reproduktif.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Kecergasan Fizikal (SEGAK), Taktikal Sukan & PEERS',
                        'description'    => 'Ujian SEGAK, strategi permainan (bola sepak, hoki, bola tampar), pengurusan stres dan pertolongan cemas.',
                        'subtopics'      => [
                            ['title' => 'Ujian SEGAK & Komponen Kecergasan Berlandaskan Kesihatan', 'default_status' => 'Mastered'],
                            ['title' => 'Kemahiran & Strategi Sukan Berpasukan', 'default_status' => 'Mastered'],
                            ['title' => 'Pendidikan Kesihatan Reproduktif & Sosial (PEERS)', 'default_status' => 'In Progress'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'Reka Bentuk & Teknologi (RBT Form 1–3)',
                'code'           => 'KPM-KSSM-RBT-L',
                'education_type' => 'secondary',
                'stage'          => 'Lower Secondary',
                'form_year'      => 'Form 1–3',
                'category'       => 'Elective',
                'stream_package' => 'General',
                'level'          => 'Lower Secondary',
                'icon'           => 'wrench-screwdriver',
                'color'          => '#4F46E5',
                'description'    => 'Pengurusan projek reka bentuk, lakaran piktorial, aplikasi mekanikal, elektrikal, elektronik, akuaponik dan pembuatan.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Pengurusan Projek & Aplikasi Teknologi RBT',
                        'description'    => 'Perancangan bajet projek, lakaran isometrik/oblik, sistem mekanikal (gear, tuas), elektrik dan elektronik mikropengawal.',
                        'subtopics'      => [
                            ['title' => 'Lakaran Isometrik, Oblik & Perspektif Produk', 'default_status' => 'Mastered'],
                            ['title' => 'Sistem Mekanikal & Elektrikal dalam Reka Bentuk Produk', 'default_status' => 'In Progress'],
                            ['title' => 'Pengaturcaraan Papan Mikropengawal (Arduino/Microbit)', 'default_status' => 'Needs Practice'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'Asas Sains Komputer (ASK Form 1–3)',
                'code'           => 'KPM-KSSM-ASK-L',
                'education_type' => 'secondary',
                'stage'          => 'Lower Secondary',
                'form_year'      => 'Form 1–3',
                'category'       => 'Elective',
                'stream_package' => 'STEM Computing',
                'level'          => 'Lower Secondary',
                'icon'           => 'code-bracket',
                'color'          => '#0891B2',
                'description'    => 'Pemikiran komputasional, perwakilan data sistem perduaan/perenambelasan, algoritma pseudokod/carta alir dan pengaturcaraan Python & HTML.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Pemikiran Komputasional & Perwakilan Data',
                        'description'    => '4 teknik pemikiran komputasional, penukaran nombor perduaan, perenambelasan dan kod ASCII.',
                        'subtopics'      => [
                            ['title' => 'Teknik Leraian, Pengecaman Corak, Peniskalaan & Pengitlakan', 'default_status' => 'Mastered'],
                            ['title' => 'Penukaran Nombor Perduaan, Perpuluhan & Perenambelasan', 'default_status' => 'Mastered'],
                            ['title' => 'Perwakilan Imej Digital & Audio Digital', 'default_status' => 'In Progress'],
                        ]
                    ],
                    [
                        'chapter_number' => 2,
                        'title'          => 'Algoritma, Pengekodan Python & Pembangunan Web HTML',
                        'description'    => 'Struktur kawalan jujukan, pilihan, ulangan, sintaks Python dan pembinaan laman web asas HTML.',
                        'subtopics'      => [
                            ['title' => 'Membina Pseudokod & Carta Alir Struktur Kawalan Bersarang', 'default_status' => 'In Progress'],
                            ['title' => 'Pengaturcaraan Python (Pemboleh Ubah, Gelung for/while, Fungsi)', 'default_status' => 'In Progress'],
                            ['title' => 'Pembinaan Laman Web Menggunakan Tag HTML & CSS Asas', 'default_status' => 'Needs Practice'],
                        ]
                    ]
                ]
            ],

            // ═════════════════════════════════════════════════════════════════
            // LEVEL 3B: SECONDARY - UPPER SECONDARY (KSSM FORM 4 & 5 - SPM)
            // CORE SUBJECTS (TERAS)
            // ═════════════════════════════════════════════════════════════════
            [
                'name'           => 'Bahasa Melayu (KSSM SPM)',
                'code'           => 'KPM-SPM-BM',
                'education_type' => 'secondary',
                'stage'          => 'Upper Secondary',
                'form_year'      => 'Form 4–5',
                'category'       => 'Core',
                'stream_package' => 'General',
                'level'          => 'Upper Secondary',
                'icon'           => 'book-open',
                'color'          => '#1E40AF',
                'description'    => 'Subjek Teras wajib lulus SPM merangkumi Kertas 1 (Karangan), Kertas 2 (Tatabahasa, Pemahaman, KOMSAS & Rumusan), Kertas 3 & 4.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Penulisan Karangan Kertas 1 (Respons Terhad & Terbuka)',
                        'description'    => 'Domain Peribadi, Pendidikan, Awam dan Kerjaya dengan aplikasi ungkapan menarik dan wacana mantap.',
                        'subtopics'      => [
                            ['title' => 'Karangan Bahagian A: Respons Terhad Berdasarkan Bahan Rangsangan', 'default_status' => 'Mastered'],
                            ['title' => 'Karangan Bahagian B: Respons Terbuka (Format & Bukan Format)', 'default_status' => 'In Progress'],
                            ['title' => 'Penggunaan Kosa Kata Luas, Peribahasa & Wacana Menarik', 'default_status' => 'In Progress'],
                        ]
                    ],
                    [
                        'chapter_number' => 2,
                        'title'          => 'Morfologi, Sintaksis & Penyuntingan Kertas 2 (Bahagian A)',
                        'description'    => 'Membina ayat daripada perkataan berpasangan, pola ayat dasar, penukaran ayat pasif/aktif dan penyuntingan.',
                        'subtopics'      => [
                            ['title' => 'Morfologi: Membina Ayat Mengikut Golongan Kata', 'default_status' => 'Mastered'],
                            ['title' => 'Sintaksis: Pola Ayat, Ayat Majmuk & Susunan Songsang', 'default_status' => 'In Progress'],
                            ['title' => 'Penyuntingan Kesalahan Bahasa, Ejaan & Tanda Baca', 'default_status' => 'Mastered'],
                            ['title' => 'Penukaran Teks Bahasa Melayu Klasik kepada Standard', 'default_status' => 'Needs Practice'],
                        ]
                    ],
                    [
                        'chapter_number' => 3,
                        'title'          => 'Pemahaman Petikan, KOMSAS & Rumusan SPM (Kertas 2)',
                        'description'    => 'Pemahaman umum, prosa tradisional, puisi, analisis novel SPM dan teknik rumusan 30 markah.',
                        'subtopics'      => [
                            ['title' => 'Pemahaman Petikan Umum & Puisi Moden (Soalan KBAT)', 'default_status' => 'Mastered'],
                            ['title' => 'Kajian Novel Tingkatan 4 & 5 (Peristiwa, Nilai & Pengajaran)', 'default_status' => 'In Progress'],
                            ['title' => 'Format Rumusan SPM 8 Isi Berdasarkan Bahan 1 & Bahan 2', 'default_status' => 'Needs Practice'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'English (KSSM CEFR SPM)',
                'code'           => 'KPM-SPM-ENG',
                'education_type' => 'secondary',
                'stage'          => 'Upper Secondary',
                'form_year'      => 'Form 4–5',
                'category'       => 'Core',
                'stream_package' => 'General',
                'level'          => 'Upper Secondary',
                'icon'           => 'pencil',
                'color'          => '#2563EB',
                'description'    => 'CEFR C1/B2 aligned SPM English covering Paper 1 (Reading), Paper 2 (Writing Part 1–3), Paper 3 (Speaking), and Paper 4 (Listening).',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Reading Comprehension & Use of English (Paper 1)',
                        'description'    => 'Short Texts MCQ, Error Correction, Longer Texts, Gapped Texts, and Matching and Information Transfer.',
                        'subtopics'      => [
                            ['title' => 'Part 1: Short Texts Multiple Choice Questions', 'default_status' => 'Mastered'],
                            ['title' => 'Part 2: Rational Cloze & Lexical Cloze Grammar Test', 'default_status' => 'Mastered'],
                            ['title' => 'Part 3 & 4: Extended Reading & Gapped Text Techniques', 'default_status' => 'In Progress'],
                            ['title' => 'Part 5: Matching and Categorising Information', 'default_status' => 'Mastered'],
                        ]
                    ],
                    [
                        'chapter_number' => 2,
                        'title'          => 'Writing Strategies: Email, Essay, Article & Review (Paper 2)',
                        'description'    => 'Short communicative message, guided writing essays, articles, reports, and review formats.',
                        'subtopics'      => [
                            ['title' => 'Part 1: Short Communicative Message (Email/Letter)', 'default_status' => 'Mastered'],
                            ['title' => 'Part 2: Guided Continuous Essay Writing (Core Arguments)', 'default_status' => 'In Progress'],
                            ['title' => 'Part 3: Extended Writing Formats (Review, Article, Story, Report)', 'default_status' => 'Needs Practice'],
                        ]
                    ],
                    [
                        'chapter_number' => 3,
                        'title'          => 'Oral Speaking & Audio Listening Mastery (Papers 3 & 4)',
                        'description'    => 'Individual candidate response, collaborative task, discussion questions, and audio tracking.',
                        'subtopics'      => [
                            ['title' => 'Paper 3 Part 2 & 3: Collaborative Decision-Making Task', 'default_status' => 'In Progress'],
                            ['title' => 'Paper 4: Listening for Specific Details & Attitude', 'default_status' => 'Mastered'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'Matematik / Mathematics (KSSM SPM)',
                'code'           => 'KPM-SPM-MATH',
                'education_type' => 'secondary',
                'stage'          => 'Upper Secondary',
                'form_year'      => 'Form 4–5',
                'category'       => 'Core',
                'stream_package' => 'General',
                'level'          => 'Upper Secondary',
                'icon'           => 'calculator',
                'color'          => '#0284C7',
                'description'    => 'Subjek Teras SPM Kertas 1 (Objektif) dan Kertas 2 (Bahagian A, B, C) merangkumi DSKP Tingkatan 4 dan 5.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Fungsi Kuadratik, Asas Nombor & Penaakulan Logik',
                        'description'    => 'Penyelesaian punca kuadratik, asas nombor 2–10, pernyataan, implikasi dan hujah deduktif/induktif.',
                        'subtopics'      => [
                            ['title' => 'Fungsi Kuadratik & Graf Parabola (Bentuk Titik Min/Maks)', 'default_status' => 'Mastered'],
                            ['title' => 'Penukaran & Operasi Asas Nombor Pelbagai Asas', 'default_status' => 'Mastered'],
                            ['title' => 'Penaakulan Logik, Akas, Songsangan & Kontrapositif', 'default_status' => 'In Progress'],
                        ]
                    ],
                    [
                        'chapter_number' => 2,
                        'title'          => 'Operasi Set, Teori Graf & Ketaksamaan Linear 2 Pemboleh Ubah',
                        'description'    => 'Persilangan, kesatuan dan pelengkap set, graf berbilang tepi, pokok, serta lorekan rantau sepunya.',
                        'subtopics'      => [
                            ['title' => 'Operasi Set & Gambar Rajah Venn', 'default_status' => 'Mastered'],
                            ['title' => 'Rangkaian Teori Graf & Pembinaan Pokok Minimum', 'default_status' => 'In Progress'],
                            ['title' => 'Sistem Ketaksamaan Linear & Lorekan Rantau Sepunya', 'default_status' => 'In Progress'],
                        ]
                    ],
                    [
                        'chapter_number' => 3,
                        'title'          => 'Graf Gerakan, Sukatan Serakan & Kebarangkalian Bergabung',
                        'description'    => 'Graf jarak-masa, graf laju-masa, julat antara kuartil, sisihan piawai, dan peristiwa bersandar/tak bersandar.',
                        'subtopics'      => [
                            ['title' => 'Tafsiran Graf Jarak-Masa & Laju-Masa (Pecutan/Kadar Perubahan)', 'default_status' => 'Mastered'],
                            ['title' => 'Plot Kotak, Varians & Sisihan Piawai Data Tak Terkumpul', 'default_status' => 'In Progress'],
                            ['title' => 'Kebarangkalian Peristiwa Bersandar & Saling Eksklusif', 'default_status' => 'Needs Practice'],
                        ]
                    ],
                    [
                        'chapter_number' => 4,
                        'title'          => 'Matematik Pengguna: Kewangan, Insurans & Percukaian',
                        'description'    => 'Faedah kompaun, pengurusan kredit, pelan kewangan, insurans am/hayat, dan pengiraan cukai pendapatan.',
                        'subtopics'      => [
                            ['title' => 'Pengurusan Kewangan, Simpanan, Pelaburan & Faedah Kompaun', 'default_status' => 'Mastered'],
                            ['title' => 'Pengiraan Premium Insurans, Ko-insurans & Deduktibel', 'default_status' => 'In Progress'],
                            ['title' => 'Pengiraan Cukai Pendapatan, Cukai Jalan & Rebat Cukai', 'default_status' => 'Needs Practice'],
                        ]
                    ],
                    [
                        'chapter_number' => 5,
                        'title'          => 'Ubahan, Matriks, Transformasi Isometri & Sukatan Membulat',
                        'description'    => 'Ubahan langsung/songsang/bergabung, matriks songsang, translasi, pantulan, putaran, pembesaran.',
                        'subtopics'      => [
                            ['title' => 'Ubahan Langsung, Songsang & Bergabung', 'default_status' => 'Mastered'],
                            ['title' => 'Pendaraban Matriks & Matriks Songsang 2x2', 'default_status' => 'In Progress'],
                            ['title' => 'Gabungan Transformasi & Luas Imej di Bawah Pembesaran', 'default_status' => 'Needs Practice'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'Sejarah (KSSM SPM)',
                'code'           => 'KPM-SPM-SEJ',
                'education_type' => 'secondary',
                'stage'          => 'Upper Secondary',
                'form_year'      => 'Form 4–5',
                'category'       => 'Core',
                'stream_package' => 'General',
                'level'          => 'Upper Secondary',
                'icon'           => 'academic-cap',
                'color'          => '#B45309',
                'description'    => 'Subjek Teras wajib lulus SPM merangkumi Warisan Bangsa, Konflik Dunia, Persekutuan Tanah Melayu, Kemerdekaan, Pembentukan Malaysia & Dasar Awam.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Warisan Negara Bangsa & Kebangkitan Nasionalisme',
                        'description'    => 'Ciri-ciri negara bangsa Kesultanan Melayu Melaka, perkembangan nasionalisme di Asia Tenggara dan Tanah Melayu.',
                        'subtopics'      => [
                            ['title' => 'Ciri-Ciri Negara Bangsa Melaka (Wilayah, Kedaulatan, Rakyat, Undang-Undang)', 'default_status' => 'Mastered'],
                            ['title' => 'Faktor Kemunculan Gerakan Nasionalisme & Peranan Golongan Intelektual', 'default_status' => 'In Progress'],
                        ]
                    ],
                    [
                        'chapter_number' => 2,
                        'title'          => 'Konflik Dunia, Pendudukan Jepun & Era Peralihan Kuasa',
                        'description'    => 'Perang Dunia I & II, pendudukan tentera Jepun, penentangan Malayan Union dan penubuhan PTM 1948.',
                        'subtopics'      => [
                            ['title' => 'Perang Dunia Pertama & Perang Dunia Kedua di Asia Pasifik', 'default_status' => 'Mastered'],
                            ['title' => 'Penentangan Terhadap Malayan Union & Ciri Persekutuan Tanah Melayu 1948', 'default_status' => 'In Progress'],
                            ['title' => 'Ancaman Komunis & Usaha Menangani Darurat (Rancangan Briggs)', 'default_status' => 'In Progress'],
                        ]
                    ],
                    [
                        'chapter_number' => 3,
                        'title'          => 'Kemerdekaan 1957 & Pembentukan Malaysia 1963',
                        'description'    => 'Pilihan raya 1955, rombongan kemerdekaan London, Perlembagaan PTM 1957, gagasan Malaysia dan reaksi serantau.',
                        'subtopics'      => [
                            ['title' => 'Langkah Penggubalan Perlembagaan Persekutuan Tanah Melayu 1957', 'default_status' => 'Mastered'],
                            ['title' => 'Rasional Pembentukan Malaysia & Peranan Suruhanjaya Cobbold', 'default_status' => 'In Progress'],
                            ['title' => 'Menangani Konfrontasi Indonesia & Pemisahan Singapura', 'default_status' => 'Needs Practice'],
                        ]
                    ],
                    [
                        'chapter_number' => 4,
                        'title'          => 'Raja Berperlembagaan, Demokrasi Berparlimen & Hubungan Luar',
                        'description'    => 'Sistem pemerintahan Malaysia, Yang di-Pertuan Agong, badan eksekutif, perundangan, kehakiman, DEB, DPN dan ASEAN.',
                        'subtopics'      => [
                            ['title' => 'Institusi YDPA, Majlis Raja-Raja & Kuasa Pengasingan', 'default_status' => 'In Progress'],
                            ['title' => 'Dasar Ekonomi Baru (DEB) & Dasar Pembangunan Nasional (DPN)', 'default_status' => 'In Progress'],
                            ['title' => 'Penglibatan Malaysia dalam PBB, Komanwel, ASEAN & OIC', 'default_status' => 'Mastered'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'Pendidikan Islam & Moral (KSSM SPM)',
                'code'           => 'KPM-SPM-PIM',
                'education_type' => 'secondary',
                'stage'          => 'Upper Secondary',
                'form_year'      => 'Form 4–5',
                'category'       => 'Core',
                'stream_package' => 'General',
                'level'          => 'Upper Secondary',
                'icon'           => 'sun',
                'color'          => '#15803D',
                'description'    => 'Penghayatan ayat Al-Quran, hadis sahih, akidah Ahli Sunnah Wal Jamaah, fiqh perkahwinan/harta atau 18 nilai universal SPM.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Al-Quran, Hadis, Fiqh Munakahat & Muamalat SPM',
                        'description'    => 'Ayat bacaan dan kefahaman terpilih SPM, hadis larangan fitnah, perkahwinan dalam Islam dan faraid.',
                        'subtopics'      => [
                            ['title' => 'Ayat Kefahaman Terpilih & Hukum Tajwid SPM', 'default_status' => 'Mastered'],
                            ['title' => 'Fiqh Munakahat: Perkahwinan, Tanggungjawab Suami Isteri & Isu Rumahtangga', 'default_status' => 'In Progress'],
                            ['title' => 'Pendidikan Moral: Etika Global, Hak Asasi & Pemeliharaan Alam', 'default_status' => 'Mastered'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'Pendidikan Jasmani & Kesihatan (PJPK SPM)',
                'code'           => 'KPM-SPM-PJPK',
                'education_type' => 'secondary',
                'stage'          => 'Upper Secondary',
                'form_year'      => 'Form 4–5',
                'category'       => 'Core',
                'stream_package' => 'General',
                'level'          => 'Upper Secondary',
                'icon'           => 'heart',
                'color'          => '#047857',
                'description'    => 'Standard Kecergasan Fizikal Kebangsaan (SEGAK), biomekanik sukan, kecergasan kardiovaskular dan gaya hidup sihat.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Standard Kecergasan Fizikal & Pengurusan Kesihatan Diri',
                        'description'    => 'Latihan bebanan, litar kecergasan, fisiologi senam, pengurusan stres dan pertolongan cemas CPR.',
                        'subtopics'      => [
                            ['title' => 'Ujian SEGAK & Rancangan Latihan Kecergasan Individu', 'default_status' => 'Mastered'],
                            ['title' => 'Pertolongan Cemas: Prosedur CPR & Rawatan Kecemasan', 'default_status' => 'Mastered'],
                        ]
                    ]
                ]
            ],

            // ═════════════════════════════════════════════════════════════════
            // LEVEL 3B: SECONDARY - UPPER SECONDARY (KSSM FORM 4 & 5 - SPM)
            // ELECTIVE SUBJECTS (ELEKTIF KPM PACKAGES)
            // ═════════════════════════════════════════════════════════════════
            [
                'name'           => 'Additional Mathematics (Matematik Tambahan)',
                'code'           => 'KPM-SPM-ADDMATH',
                'education_type' => 'secondary',
                'stage'          => 'Upper Secondary',
                'form_year'      => 'Form 4–5',
                'category'       => 'Elective',
                'stream_package' => 'STEM Pure Science',
                'level'          => 'Upper Secondary',
                'icon'           => 'chart-bar',
                'color'          => '#4338CA',
                'description'    => 'Pakej STEM Sains Tulen merangkumi Fungsi Lanjutan, Kalkulus (Pembezaan & Pengamiran), Geometri Vektor dan Pengaturcaraan Linear.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Fungsi, Fungsi Kuadratik & Sistem Persamaan',
                        'description'    => 'Fungsi gubahan, fungsi songsang, punca persamaan kuadratik dan penyelesaian 3 pemboleh ubah linear.',
                        'subtopics'      => [
                            ['title' => 'Fungsi Gubahan & Menentukan Fungsi Songsang f^-1(x)', 'default_status' => 'Mastered'],
                            ['title' => 'Bentuk Verteks Kuadratik f(x) = a(x-h)^2 + k & Pembezalayan', 'default_status' => 'Mastered'],
                            ['title' => 'Sistem 3 Persamaan Linear Menggunakan Kaedah Penghapusan/Penggantian', 'default_status' => 'In Progress'],
                        ]
                    ],
                    [
                        'chapter_number' => 2,
                        'title'          => 'Indeks, Surd, Logaritma & Janjang',
                        'description'    => 'Hukum indeks, memudahkan surd, hukum logaritma, janjang aritmetik (Tn, Sn) dan janjang geometri (S_infinity).',
                        'subtopics'      => [
                            ['title' => 'Mempermudah Surd & Nisbahkan Penyebut', 'default_status' => 'Mastered'],
                            ['title' => 'Hukum Logaritma & Penukaran Asas Logaritma', 'default_status' => 'In Progress'],
                            ['title' => 'Janjang Aritmetik (AP) & Janjang Geometri (GP) Aplikasi Soalan KBAT', 'default_status' => 'In Progress'],
                        ]
                    ],
                    [
                        'chapter_number' => 3,
                        'title'          => 'Geometri Koordinat, Hukum Linear & Vektor',
                        'description'    => 'Garis selari/serenjang, pembahagian tembereng garis, penukaran graf tak linear ke linear Y = mX + c dan vektor 2D/3D.',
                        'subtopics'      => [
                            ['title' => 'Persamaan Garis Lurus Serenjang & Luas Poligon Menggunakan Kaedah Tali Kasut', 'default_status' => 'Mastered'],
                            ['title' => 'Aplikasi Hukum Linear: Memplot Garis Lurus Penyuaian Terbaik', 'default_status' => 'In Progress'],
                            ['title' => 'Vektor dalam Satah Cartes & Vektor Paduan', 'default_status' => 'In Progress'],
                        ]
                    ],
                    [
                        'chapter_number' => 4,
                        'title'          => 'Kalkulus: Pembezaan & Pengamiran',
                        'description'    => 'Prinsip pertama, petua hasil darab/bahagi/rantai, kadar perubahan, titik pegun, pengamiran tak tentu dan tentu.',
                        'subtopics'      => [
                            ['title' => 'Pembezaan Fungsi Algebra Menggunakan Petua Rantai & Darab/Bahagi', 'default_status' => 'In Progress'],
                            ['title' => 'Aplikasi Pembezaan: Titik Maksimum/Minimum & Kadar Perubahan Terkait', 'default_status' => 'Needs Practice'],
                            ['title' => 'Pengamiran Tentu, Luas di Bawah Lengkung & Isi Padu Kisaran', 'default_status' => 'Needs Practice'],
                        ]
                    ],
                    [
                        'chapter_number' => 5,
                        'title'          => 'Trigonometri, Pilih Atur, Gabungan & Taburan Kebarangkalian',
                        'description'    => 'Identiti asas trigonometri, rumus sudut berganda, nPr, nCr, taburan binomial dan taburan normal piawai.',
                        'subtopics'      => [
                            ['title' => 'Membuktikan Identiti Trigonometri & Menyelesaikan Persamaan', 'default_status' => 'Needs Practice'],
                            ['title' => 'Pilih Atur (Permutation) & Gabungan (Combination) Bersyarat', 'default_status' => 'In Progress'],
                            ['title' => 'Taburan Binomial P(X=r) & Skor Piawai Z Taburan Normal', 'default_status' => 'In Progress'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'Physics (Fizik)',
                'code'           => 'KPM-SPM-PHY',
                'education_type' => 'secondary',
                'stage'          => 'Upper Secondary',
                'form_year'      => 'Form 4–5',
                'category'       => 'Elective',
                'stream_package' => 'STEM Pure Science',
                'level'          => 'Upper Secondary',
                'icon'           => 'atom',
                'color'          => '#7C3AED',
                'description'    => 'Pakej STEM Sains Tulen merangkumi Pengukuran, Daya & Gerakan, Kegravitian, Haba, Gelombang, Elektrik, Keelektromagnetan, Elektronik & Nuklear.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Daya & Gerakan I & Kegravitian',
                        'description'    => 'Persamaan gerakan linear, inersia, momentum, daya impuls, hukum graviti semesta Newton dan hukum Kepler.',
                        'subtopics'      => [
                            ['title' => 'Persamaan Gerakan Linear dengan Pecutan Seragam', 'default_status' => 'Mastered'],
                            ['title' => 'Prinsip Keabadian Momentum & Pelanggaran Kenyal/Tak Kenyal', 'default_status' => 'Mastered'],
                            ['title' => 'Hukum Graviti Newton, Halaju Orbit & Halaju Lepas', 'default_status' => 'In Progress'],
                        ]
                    ],
                    [
                        'chapter_number' => 2,
                        'title'          => 'Haba, Gelombang, Cahaya & Optik',
                        'description'    => 'Keseimbangan terma, muatan haba tentu, hukum gas, pembelauan/interferens gelombang dan pembiasan kanta.',
                        'subtopics'      => [
                            ['title' => 'Muatan Haba Tentu (Q = mcθ) & Haba Pendam Tentu', 'default_status' => 'Mastered'],
                            ['title' => 'Hukum Boyle, Charles & Gay-Lussac', 'default_status' => 'In Progress'],
                            ['title' => 'Interferens Gelombang & Formula Dwikaca Young (λ = ax/D)', 'default_status' => 'In Progress'],
                            ['title' => 'Formula Kanta Nipis (1/f = 1/u + 1/v) & Pembesaran Linear', 'default_status' => 'Needs Practice'],
                        ]
                    ],
                    [
                        'chapter_number' => 3,
                        'title'          => 'Daya & Gerakan II, Tekanan, Elektrik & Keelektromagnetan',
                        'description'    => 'Leraian daya, prinsip Pascal/Archimedes/Bernoulli, daya gerak elektrik (d.g.e.), petua tangan kiri Fleming dan transformer.',
                        'subtopics'      => [
                            ['title' => 'Leraian Daya & Keseimbangan Daya', 'default_status' => 'In Progress'],
                            ['title' => 'Prinsip Archimedes & Daya Apungan', 'default_status' => 'In Progress'],
                            ['title' => 'D.g.e., Rintangan Dalam Litar & Hukum Ohm', 'default_status' => 'Needs Practice'],
                            ['title' => 'Aruhan Elektromagnet & Kecekapan Transformer', 'default_status' => 'Needs Practice'],
                        ]
                    ],
                    [
                        'chapter_number' => 4,
                        'title'          => 'Fizik Nuklear & Pengenalan Fizik Kuantum',
                        'description'    => 'Reputan radioaktif, separuh hayat, tindak balas pembelahan/pelakuran nuklear, teori foton dan kesan fotoelektrik.',
                        'subtopics'      => [
                            ['title' => 'Persamaan Reputan Radioaktif & Pengiraan Separuh Hayat', 'default_status' => 'In Progress'],
                            ['title' => 'Persamaan Tenaga Einstein E = mc^2 & Cacat Jisim', 'default_status' => 'Needs Practice'],
                            ['title' => 'Teori Kuantum Planck & Kesan Fotoelektrik Einstein (E = hf)', 'default_status' => 'In Progress'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'Chemistry (Kimia)',
                'code'           => 'KPM-SPM-CHEM',
                'education_type' => 'secondary',
                'stage'          => 'Upper Secondary',
                'form_year'      => 'Form 4–5',
                'category'       => 'Elective',
                'stream_package' => 'STEM Pure Science',
                'level'          => 'Upper Secondary',
                'icon'           => 'beaker',
                'color'          => '#059669',
                'description'    => 'Pakej STEM Sains Tulen merangkumi Struktur Atom, Konsep Mol, Jadual Berkala, Ikatan Kimia, Asid Bes & Garam, Termokimia, Redoks & Polimer.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Struktur Atom, Konsep Mol & Persamaan Kimia',
                        'description'    => 'Nombor proton/nukleon, isotop, pemalar Avogadro, formula empirik/molekul dan pengimbangan persamaan kimia.',
                        'subtopics'      => [
                            ['title' => 'Susunan Elektron & Rajah Struktur Atom', 'default_status' => 'Mastered'],
                            ['title' => 'Pengiraan Konsep Mol: Jisim Molar & Isi Padu Molar Gas', 'default_status' => 'Mastered'],
                            ['title' => 'Penentuan Formula Empirik & Formula Molekul', 'default_status' => 'In Progress'],
                            ['title' => 'Stoikiometri & Pengiraan Berdasarkan Persamaan Seimbang', 'default_status' => 'In Progress'],
                        ]
                    ],
                    [
                        'chapter_number' => 2,
                        'title'          => 'Jadual Berkala, Ikatan Kimia & Asid, Bes dan Garam',
                        'description'    => 'Trend Unsur Kumpulan 1, 17, 18, Kala 3, ikatan ion/kovalen, nilai pH, pene нейтраalan, penyediaan garam terlarutkan/tak terlarutkan.',
                        'subtopics'      => [
                            ['title' => 'Sifat Kimia Unsur Kumpulan 1 & Kumpulan 17', 'default_status' => 'Mastered'],
                            ['title' => 'Pembentukan Ikatan Ion & Ikatan Kovalen (Ikatan Tunggal/Ganda)', 'default_status' => 'In Progress'],
                            ['title' => 'Penyediaan Garam Terlarutkan (Kaedah Penghabluran Semula)', 'default_status' => 'Needs Practice'],
                            ['title' => 'Penyediaan Garam Tak Terlarutkan (Tindak Balas Pengendapan)', 'default_status' => 'In Progress'],
                            ['title' => 'Ujian Kation & Anion dalam Analisis Kualitatif Garam', 'default_status' => 'Needs Practice'],
                        ]
                    ],
                    [
                        'chapter_number' => 3,
                        'title'          => 'Kadar Tindak Balas, Termokimia & Tindak Balas Redoks',
                        'description'    => 'Teori perlanggaran, faktor kadar tindak balas, haba pemendakan/peneutralan/pembakaran, siri elektrokimia dan sel elektrolisis.',
                        'subtopics'      => [
                            ['title' => 'Faktor Mempengaruhi Kadar Tindak Balas & Teori Perlanggaran', 'default_status' => 'Mastered'],
                            ['title' => 'Pengiraan Haba Tindak Balas Termokimia (ΔH = mcθ / n)', 'default_status' => 'In Progress'],
                            ['title' => 'Tindak Balas Redoks dari Segi Pemindahan Elektron & Nombor Pengoksidaan', 'default_status' => 'Needs Practice'],
                            ['title' => 'Elektrolisis Sebatian Lebur & Larutan Akueus', 'default_status' => 'Needs Practice'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'Biology (Biologi)',
                'code'           => 'KPM-SPM-BIO',
                'education_type' => 'secondary',
                'stage'          => 'Upper Secondary',
                'form_year'      => 'Form 4–5',
                'category'       => 'Elective',
                'stream_package' => 'STEM Pure Science',
                'level'          => 'Upper Secondary',
                'icon'           => 'dna',
                'color'          => '#16A34A',
                'description'    => 'Pakej STEM Sains Tulen merangkumi Biologi Sel, Membran Plasma, Enzim, Respirasi, Fisiologi Manusia/Tumbuhan, Genetik & Bioteknologi.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Biologi Sel, Membran Plasma & Enzim',
                        'description'    => 'Organel sel eukariot, resapan ringkas, osmosis, pengangkutan aktif, hipotonik/hipertonik dan hipotesis mangga dan kunci enzim.',
                        'subtopics'      => [
                            ['title' => 'Struktur & Fungsi Organel Sel (Mitokondria, Kloroplas, Jasad Golgi)', 'default_status' => 'Mastered'],
                            ['title' => 'Pengangkutan Pasif (Osmosis) & Pengangkutan Aktif Merentasi Membran', 'default_status' => 'Mastered'],
                            ['title' => 'Kesan Larutan Hipotonik, Isotonik & Hipertonik Terhadap Sel', 'default_status' => 'In Progress'],
                            ['title' => 'Mekanisme Tindakan Enzim & Faktor pH, Suhu, Kepekatan Substrat', 'default_status' => 'In Progress'],
                        ]
                    ],
                    [
                        'chapter_number' => 2,
                        'title'          => 'Respirasi Sel, Fisiologi Manusia & Keimunan',
                        'description'    => 'Respirasi aerob, fermentasi asid laktik/alkohol, sistem peredaran darah, pembekuan darah dan keimunan aktif/pasif.',
                        'subtopics'      => [
                            ['title' => 'Respirasi Aerob & Fermentasi Asid Laktik dalam Otot Manusia', 'default_status' => 'Mastered'],
                            ['title' => 'Mekanisme Peredaran Darah & Pembekuan Darah (Trombin & Fibrin)', 'default_status' => 'In Progress'],
                            ['title' => 'Keimunan Aktif Semula Jadi/Buatan vs Keimunan Pasif', 'default_status' => 'In Progress'],
                            ['title' => 'Sistem Saraf, Transmisi Impuls Sinaps & Tindakan Refleks', 'default_status' => 'Needs Practice'],
                        ]
                    ],
                    [
                        'chapter_number' => 3,
                        'title'          => 'Pewarisan Genetik, Variasi & Bioteknologi Moden',
                        'description'    => 'Hukum Mendel I & II, kacukan monohibrid/dihibrid, kariotip, mutasi gen/kromosom dan teknologi DNA rekombinan.',
                        'subtopics'      => [
                            ['title' => 'Kacukan Monohibrid & Dihibrid Mengikut Hukum Mendel', 'default_status' => 'In Progress'],
                            ['title' => 'Pewarisan Terangkai Seks (Buta Warna & Hemofilia)', 'default_status' => 'Needs Practice'],
                            ['title' => 'Mutasi Kromosom (Sindrom Down, Turner, Klinefelter)', 'default_status' => 'In Progress'],
                            ['title' => 'Aplikasi DNA Rekombinan & Organisma Terubah Suai Genetik (GMO)', 'default_status' => 'Needs Practice'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'Prinsip Perakaunan (Accounting)',
                'code'           => 'KPM-SPM-ACC',
                'education_type' => 'secondary',
                'stage'          => 'Upper Secondary',
                'form_year'      => 'Form 4–5',
                'category'       => 'Elective',
                'stream_package' => 'Commerce & Accounting',
                'level'          => 'Upper Secondary',
                'icon'           => 'banknotes',
                'color'          => '#0E7490',
                'description'    => 'Pakej Perniagaan & Perakaunan merangkumi Kitaran Perakaunan, Catatan Bergu, Penyata Pendapatan, Kunci Kira-Kira, Perkongsian & Kos.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Dokumen Sumber, Buku Catatan Pertama & Lejar Catatan Bergu',
                        'description'    => 'Invois, resit, baucar, Buku Tunai, Jurnal Am, Lejar Am dan Sistem Catatan Bergu.',
                        'subtopics'      => [
                            ['title' => 'Aliran Dokumen Perniagaan & Perekodan Buku Catatan Pertama', 'default_status' => 'Mastered'],
                            ['title' => 'Sistem Catatan Bergu: Catatan Debit dan Kredit dalam Lejar', 'default_status' => 'Mastered'],
                            ['title' => 'Penyediaan Buku Tunai Runcit (Sistem Panjar)', 'default_status' => 'In Progress'],
                        ]
                    ],
                    [
                        'chapter_number' => 2,
                        'title'          => 'Imbangan Duga, Pelarasan Tarikh Imbangan & Penyata Kewangan',
                        'description'    => 'Susut nilai terkumpul, hutang lapuk, peruntukan hutang ragu, pelarasan belanja terdahulu/terakru.',
                        'subtopics'      => [
                            ['title' => 'Penyediaan Imbangan Duga & Pengesanan Kesalahan Imbangan', 'default_status' => 'Mastered'],
                            ['title' => 'Pelarasan Susut Nilai, Belanja Terakru & Hasil Terdahulu', 'default_status' => 'In Progress'],
                            ['title' => 'Penyata Pendapatan Komprehensif & Penyata Kedudukan Kewangan', 'default_status' => 'Needs Practice'],
                        ]
                    ],
                    [
                        'chapter_number' => 3,
                        'title'          => 'Perakaunan Perkongsian, Syarikat Berhad & Perakaunan Kos',
                        'description'    => 'Akaun Pengasingan Untung Rugi Perkongsian, Akaun Modal/Semasa, terbitan syer dan Analisis Titik Pulang Modal.',
                        'subtopics'      => [
                            ['title' => 'Akaun Pengasingan Untung Rugi & Akaun Semasa Pekongsi', 'default_status' => 'In Progress'],
                            ['title' => 'Penyata Kos Pengeluaran & Analisis Titik Pulang Modal (TPM)', 'default_status' => 'Needs Practice'],
                            ['title' => 'Analisis Nisbah Kewangan (Kecairan, Keberuntungan, Kecekapan)', 'default_status' => 'In Progress'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'Ekonomi (Economics)',
                'code'           => 'KPM-SPM-ECON',
                'education_type' => 'secondary',
                'stage'          => 'Upper Secondary',
                'form_year'      => 'Form 4–5',
                'category'       => 'Elective',
                'stream_package' => 'Commerce & Accounting',
                'level'          => 'Upper Secondary',
                'icon'           => 'chart-pie',
                'color'          => '#0369A1',
                'description'    => 'Pakej Ekonomi merangkumi Konsep Asas Ekonomi, Keluk Kemungkinan Pengeluaran, Mekanisme Pasaran, Dasar Fiskal/Kewangan dan Perdagangan Luar.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Konsep Asas Ekonomi, Pasaran & Keseimbangan Pasaran',
                        'description'    => 'Kekurangan, pilihan, kos lepas, keluk kemungkinan pengeluaran (KKP), hukum permintaan/penawaran dan keanjalan.',
                        'subtopics'      => [
                            ['title' => 'Konsep Kekurangan, Pilihan & Kos Lepas serta Keluk KKP', 'default_status' => 'Mastered'],
                            ['title' => 'Hukum Permintaan, Penawaran & Keseimbangan Pasaran (Qd = Qs)', 'default_status' => 'Mastered'],
                            ['title' => 'Pengiraan Keanjalan Harga Permintaan (Ed) & Keanjalan Penawaran (Es)', 'default_status' => 'In Progress'],
                        ]
                    ],
                    [
                        'chapter_number' => 2,
                        'title'          => 'Peranan Kerajaan, Dasar Ekonomi & Perdagangan Antarabangsa',
                        'description'    => 'Matlamat makroekonomi (Guna tenaga penuh, kestabilan harga, pertumbuhan KDNK), dasar fiskal dan kadar pertukaran asing.',
                        'subtopics'      => [
                            ['title' => 'Peranan Kerajaan Mengatasi Inflasi & Kemelesetan Ekonomi', 'default_status' => 'In Progress'],
                            ['title' => 'Dasar Belanjawan Negara (Fiskal) & Dasar Kewangan Bank Pusat', 'default_status' => 'In Progress'],
                            ['title' => 'Imbangan Pembayaran, Tarif, Kuota & Kadar Pertukaran Asing', 'default_status' => 'Needs Practice'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'Perniagaan (Business Studies)',
                'code'           => 'KPM-SPM-BUS',
                'education_type' => 'secondary',
                'stage'          => 'Upper Secondary',
                'form_year'      => 'Form 4–5',
                'category'       => 'Elective',
                'stream_package' => 'Commerce & Accounting',
                'level'          => 'Upper Secondary',
                'icon'           => 'briefcase',
                'color'          => '#B45309',
                'description'    => 'Pakej Perniagaan merangkumi Bentuk Pemilikan, Trend Pasaran, Pengurusan Sumber Manusia, Pemasaran dan Rancangan Perniagaan.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Tujuan Perniagaan, Pemilikan & Trend Persekitaran',
                        'description'    => 'Milikan tunggal, perkongsian, syarikat Sdn Bhd/Bhd, koperasi, analisis SWOT dan faktor ekonomi/teknologi.',
                        'subtopics'      => [
                            ['title' => 'Bentuk Pemilikan Perniagaan & Ciri-Ciri Perundangan', 'default_status' => 'Mastered'],
                            ['title' => 'Analisis Matrik SWOT (Kekuatan, Kelemahan, Peluang, Ancaman)', 'default_status' => 'Mastered'],
                            ['title' => 'Trend Pasaran & Kitaran Hayat Perniagaan', 'default_status' => 'In Progress'],
                        ]
                    ],
                    [
                        'chapter_number' => 2,
                        'title'          => 'Pengurusan Sumber, Pemasaran & Rancangan Perniagaan',
                        'description'    => 'Pengurusan sumber manusia, sumber pembiayaan bank/kerajaan, campuran pemasaran (4P) dan format rancangan perniagaan.',
                        'subtopics'      => [
                            ['title' => 'Pengurusan Sumber Manusia & Pembangunan Kompetensi Staf', 'default_status' => 'In Progress'],
                            ['title' => 'Campuran Pemasaran 4P (Produk, Harga, Saluran, Promosi)', 'default_status' => 'In Progress'],
                            ['title' => 'Penyediaan Rancangan Perniagaan Lengkap Format SPM', 'default_status' => 'Needs Practice'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'Sains Komputer (Computer Science)',
                'code'           => 'KPM-SPM-CS',
                'education_type' => 'secondary',
                'stage'          => 'Upper Secondary',
                'form_year'      => 'Form 4–5',
                'category'       => 'Elective',
                'stream_package' => 'STEM Computing',
                'level'          => 'Upper Secondary',
                'icon'           => 'code-bracket',
                'color'          => '#0284C7',
                'description'    => 'Pakej STEM Pengkomputeran merangkumi Pengaturcaraan Java / Python OOP, Pangkalan Data Relasi (SQL), Rangkaian & Keselamatan Siber.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Pengaturcaraan Berorientasikan Objek (OOP) & Algoritma',
                        'description'    => 'Konsep kelas, objek, enkapsulasi, pewarisan, polimorfisme, struktur kawalan dan tatasusunan (array).',
                        'subtopics'      => [
                            ['title' => 'Konsep Kelas, Objek, Atribut & Kaedah (Method)', 'default_status' => 'Mastered'],
                            ['title' => 'Struktur Kawalan Bersarang & Tatasusunan (1D dan 2D Array)', 'default_status' => 'In Progress'],
                            ['title' => 'Pengaturcaraan OOP: Pewarisan (Inheritance) & Enkapsulasi', 'default_status' => 'Needs Practice'],
                        ]
                    ],
                    [
                        'chapter_number' => 2,
                        'title'          => 'Pangkalan Data Relasi, Rajah ERD & Arahan SQL',
                        'description'    => 'Kunci primer/asing, hubungan 1:1, 1:M, M:N, penyusunan gambar rajah ERD dan pertanyaan SQL (SELECT, INSERT, UPDATE).',
                        'subtopics'      => [
                            ['title' => 'Konsep Entiti, Atribut & Gambar Rajah Hubungan Entiti (ERD)', 'default_status' => 'Mastered'],
                            ['title' => 'Penyusunan Normalisasi Pangkalan Data (1NF, 2NF, 3NF)', 'default_status' => 'In Progress'],
                            ['title' => 'Arahan SQL: CREATE TABLE, SELECT, JOIN, WHERE, ORDER BY', 'default_status' => 'Needs Practice'],
                        ]
                    ]
                ]
            ],
            [
                'name'           => 'Pendidikan Seni Visual (PSV SPM)',
                'code'           => 'KPM-SPM-PSV',
                'education_type' => 'secondary',
                'stage'          => 'Upper Secondary',
                'form_year'      => 'Form 4–5',
                'category'       => 'Elective',
                'stream_package' => 'Humanities & Arts',
                'level'          => 'Upper Secondary',
                'icon'           => 'sparkles',
                'color'          => '#9333EA',
                'description'    => 'Pakej Kemanusiaan & Seni Halus merangkumi Sejarah Seni, Seni Lukisan/Catan/Arca, Reka Bentuk Industri & Seni Kraf Tradisional.',
                'topics'         => [
                    [
                        'chapter_number' => 1,
                        'title'          => 'Sejarah & Apresiasi Seni Visual serta Seni Halus',
                        'description'    => 'Aliran seni Barat (Realisme, Impresionisme, Kubisme, Surealisme), tokoh seni tempatan dan teknik catan cat air/akrilik.',
                        'subtopics'      => [
                            ['title' => 'Aliran Seni Visual Barat & Tokoh Pelukis Tempatan', 'default_status' => 'Mastered'],
                            ['title' => 'Teknik Catan Cat Air, Akrilik, Minyak & Media Campuran', 'default_status' => 'In Progress'],
                            ['title' => 'Kajian Reka Bentuk Industri, Tipografi & Seni Kraf Batik/Ukiran', 'default_status' => 'In Progress'],
                        ]
                    ]
                ]
            ]
        ];

        // ─────────────────────────────────────────────────────────────────────
        // 2. SEED SUBJECTS, TOPICS, SUBTOPICS & STUDENT PROGRESS
        // ─────────────────────────────────────────────────────────────────────
        $students = Student::all();
        $orderCounter = 1;

        foreach ($kpmCurriculum as $item) {
            $subject = Subject::updateOrCreate(
                ['code' => $item['code']],
                [
                    'name'           => $item['name'],
                    'slug'           => Str::slug($item['name']),
                    'education_type' => $item['education_type'],
                    'stage'          => $item['stage'],
                    'form_year'      => $item['form_year'],
                    'category'       => $item['category'],
                    'stream_package' => $item['stream_package'],
                    'level'          => $item['level'],
                    'icon'           => $item['icon'],
                    'color'          => $item['color'],
                    'description'    => $item['description'],
                    'order'          => $orderCounter++,
                    'is_active'      => true,
                ]
            );

            if (!empty($item['topics'])) {
                foreach ($item['topics'] as $tData) {
                    $topic = SubjectTopic::updateOrCreate(
                        [
                            'subject_id'     => $subject->id,
                            'chapter_number' => $tData['chapter_number']
                        ],
                        [
                            'title'       => $tData['title'],
                            'description' => $tData['description'] ?? null,
                        ]
                    );

                    if (!empty($tData['subtopics'])) {
                        foreach ($tData['subtopics'] as $stOrder => $stData) {
                            $subtopic = SubjectSubtopic::updateOrCreate(
                                [
                                    'topic_id' => $topic->id,
                                    'title'    => $stData['title']
                                ],
                                [
                                    'default_status' => $stData['default_status'] ?? 'Not Started',
                                    'order'          => $stOrder + 1
                                ]
                            );

                            // Seed progress checkpoints for existing students
                            foreach ($students as $student) {
                                StudentSubtopicProgress::firstOrCreate(
                                    [
                                        'student_id'  => $student->id,
                                        'subtopic_id' => $subtopic->id,
                                    ],
                                    [
                                        'status' => $stData['default_status'] ?? 'Not Started'
                                    ]
                                );
                            }
                        }
                    }
                }
            }
        }
    }
}
