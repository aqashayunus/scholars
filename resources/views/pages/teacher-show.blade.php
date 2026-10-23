<x-app-layout>
    @php
        $teacherName = $teacher->user->name ?? 'Mr. Daniel Wong';
        $qualification = $teacher->qualification ?? 'MSc in Applied Mathematics (UM)';
        $experience = $teacher->experience_years ?? 12;
        $rating = number_format($teacher->rating ?? 4.9, 1);
        $totalStudents = $teacher->total_students ?? 5420;
        $bio = !empty($teacher->bio) ? $teacher->bio : 'Passionate educator with over a decade of experience guiding SPM and IGCSE students to achieve academic excellence. Specialising in converting complex theoretical concepts into simple, memorable frameworks.';
        $style = !empty($teacher->teaching_style) ? $teacher->teaching_style : 'Interactive problem-solving, visual whiteboard demonstrations, and step-by-step HOTS/KBAT question breakdowns.';
        
        $defaultPhotos = [
            asset('images/teacher-daniel.png'),
            asset('images/teacher-aisyah.png'),
            asset('images/teacher-chong.png'),
        ];
        $photo = !empty($teacher->photo_url) ? asset($teacher->photo_url) : $defaultPhotos[($teacher->id ?? 1) % count($defaultPhotos)];

        $teacherDetailsMap = [
            'mr. daniel wong' => [
                'education' => [
                    ['degree' => 'Master of Science (MSc) in Applied Mathematics', 'institution' => 'Universiti Malaya (UM)', 'year' => '2012 – 2014'],
                    ['degree' => 'B.Sc (Hons) in Pure & Applied Mathematics', 'institution' => 'Universiti Sains Malaysia (USM)', 'year' => '2008 – 2012'],
                    ['degree' => 'Cambridge IGCSE Mathematics Certified Educator', 'institution' => 'Cambridge Assessment International Education', 'year' => '2016'],
                ],
                'work' => [
                    ['role' => 'Head of Mathematics Department', 'company' => 'Olympian Scholars Academy', 'period' => '2018 – Present', 'desc' => 'Leads SPM & IGCSE Additional Mathematics & Modern Maths curriculum, authoring 12+ formula workbooks.'],
                    ['role' => 'Senior SPM Mathematics Paper Examiner', 'company' => 'National Examination Board Panel', 'period' => '2014 – 2018', 'desc' => 'Marked official SPM Paper 1 & 2 examinations and formulated KBAT scoring guidelines.'],
                    ['role' => 'Senior Secondary Mathematics Lecturer', 'company' => 'Sunway Academy', 'period' => '2012 – 2014', 'desc' => 'Guided over 2,000 students to achieve straight A/A+ results in SPM Mathematics.'],
                ]
            ],
            'cikgu aisyah' => [
                'education' => [
                    ['degree' => 'B.Sc (Hons) Biomedical Science', 'institution' => 'Universiti Kebangsaan Malaysia (UKM)', 'year' => '2011 – 2015'],
                    ['degree' => 'Postgraduate Diploma in Education (STEM Focus)', 'institution' => 'Universiti Pendidikan Sultan Idris (UPSI)', 'year' => '2016'],
                ],
                'work' => [
                    ['role' => 'Senior Science & Biology Educator Lead', 'company' => 'Olympian Scholars Academy', 'period' => '2019 – Present', 'desc' => 'Pioneered 3D visual biological process diagrams and mnemonic memory techniques for SPM Biology.'],
                    ['role' => 'High School Biology & Chemistry Teacher', 'company' => 'Sekolah Menengah Seri Bintang', 'period' => '2015 – 2019', 'desc' => 'Coached students in laboratory practical exams and SPM Paper 3 scientific investigation format.'],
                ]
            ],
            'mr. chong wei' => [
                'education' => [
                    ['degree' => 'Master of Arts in TESL (Teaching English as Second Language)', 'institution' => 'Universiti Malaya (UM)', 'year' => '2010 – 2013'],
                    ['degree' => 'Bachelor of Education (Hons) English Literature', 'institution' => 'Universiti Putra Malaysia (UPM)', 'year' => '2006 – 2010'],
                ],
                'work' => [
                    ['role' => 'Head of English CEFR & IGCSE Department', 'company' => 'Olympian Scholars Academy', 'period' => '2017 – Present', 'desc' => 'Specialises in CEFR C1/B2 essay structures, vocabulary mastery, and speaking test confidence.'],
                    ['role' => 'CEFR Curriculum Specialist & Workshop Speaker', 'company' => 'International Language Institute', 'period' => '2013 – 2017', 'desc' => 'Conducted nation-wide SPM English & CEFR writing workshops for over 10,000 secondary students.'],
                ]
            ]
        ];

        $lowerName = strtolower(trim($teacherName));
        $educationList = $teacherDetailsMap[$lowerName]['education'] ?? [
            ['degree' => $qualification, 'institution' => 'Premier National University', 'year' => '2010 – 2014'],
            ['degree' => 'MOE Certified Master Teaching License', 'institution' => 'Ministry of Education Malaysia', 'year' => '2015'],
        ];

        $workList = $teacherDetailsMap[$lowerName]['work'] ?? [
            ['role' => 'Senior Faculty Lead & Specialist Educator', 'company' => 'Olympian Scholars Academy', 'period' => '2018 – Present', 'desc' => 'Teaches SPM & IGCSE syllabus, developing structured question frameworks and exam preparation notes.'],
            ['role' => 'Secondary School Subject Educator', 'company' => 'National Secondary Institution', 'period' => '2012 – 2018', 'desc' => 'Guided thousands of secondary students to achieve A/A+ distinctions in nationwide examinations.'],
        ];
    @endphp

    <x-slot name="title">{{ $teacherName }} — Educator Profile</x-slot>

    {{-- HERO HEADER --}}
    <section class="bg-gradient-to-br from-primary-950 via-primary-900 to-navy text-white py-16 lg:py-20 relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -top-32 -right-32 w-96 h-96 bg-white/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-white/5 rounded-full blur-3xl"></div>
        </div>

        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-6 md:gap-8 text-center md:text-left">
                {{-- Educator Avatar Photo --}}
                <div class="relative w-32 h-32 md:w-40 md:h-40 rounded-3xl overflow-hidden border-4 border-white/20 shadow-2xl flex-shrink-0 bg-slate-900">
                    <img src="{{ $photo }}" alt="{{ $teacherName }}" class="w-full h-full object-cover object-top">
                    <div class="absolute bottom-2 right-2 w-4 h-4 bg-slate-400 border-2 border-white rounded-full"></div>
                </div>

                {{-- Educator Header Meta --}}
                <div class="flex-1">
                    <h1 class="font-heading font-black text-3xl md:text-4xl lg:text-5xl text-white mb-2 tracking-tight">
                        {{ $teacherName }}
                    </h1>
                    
                    <p class="text-white/80 text-base md:text-lg font-medium mb-4">
                        {{ $qualification }} • {{ $experience }}+ Years Teaching Experience
                    </p>

                    {{-- Rating & Stats Pills --}}
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 text-xs sm:text-sm font-bold">
                        <div class="flex items-center gap-1.5 bg-white/10 px-3.5 py-1.5 rounded-xl backdrop-blur-md">
                            <span class="text-slate-300">★</span>
                            <span class="text-white font-extrabold">{{ $rating }} / 5.0</span>
                            <span class="text-white/60 font-normal">({{ number_format($totalStudents) }} students)</span>
                        </div>
                        <div class="flex items-center gap-1.5 bg-white/10 px-3.5 py-1.5 rounded-xl backdrop-blur-md">
                            <span>SPM & IGCSE Specialist</span>
                        </div>
                        <div class="flex items-center gap-1.5 bg-white/10 px-3.5 py-1.5 rounded-xl backdrop-blur-md">
                            <span class="text-white">98% Distinction Rate</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Curved Bottom Divider --}}
        <div class="absolute -bottom-1 left-0 right-0 overflow-hidden leading-none pointer-events-none z-10">
            <svg viewBox="0 0 1440 48" fill="none" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-10 block">
                <path d="M0,20 Q720,48 1440,20 L1440,48 L0,48 Z" fill="#ffffff"/>
            </svg>
        </div>
    </section>

    {{-- MAIN CONTENT GRID --}}
    <section class="py-16 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-3 gap-8">
                
                {{-- LEFT COLUMN: DETAILS & Q&A --}}
                <div class="lg:col-span-2 space-y-8">
                    
                    {{-- 1. Educator Biography --}}
                    <div class="bg-surface rounded-3xl p-6 md:p-8 border border-gray-100 shadow-sm">
                        <h2 class="font-heading font-extrabold text-xl text-charcoal mb-4">
                            About {{ $teacherName }}
                        </h2>
                        <p class="text-muted leading-relaxed text-sm md:text-base">
                            {{ $bio }}
                        </p>
                    </div>

                    {{-- 2. Education & Academic Background --}}
                    <div class="bg-surface rounded-3xl p-6 md:p-8 border border-gray-100 shadow-sm">
                        <h2 class="font-heading font-extrabold text-xl text-charcoal mb-5">
                            Education Background
                        </h2>
                        <div class="space-y-4">
                            @foreach ($educationList as $edu)
                            <div class="bg-white p-4 md:p-5 rounded-2xl border border-gray-100 flex items-start gap-4">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
                                </div>
                                <div class="flex-1">
                                    <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
                                        <h4 class="font-heading font-bold text-charcoal text-sm md:text-base">{{ $edu['degree'] }}</h4>
                                        <span class="text-xs font-semibold text-gray-500 bg-slate-100 px-2.5 py-0.5 rounded-full">{{ $edu['year'] }}</span>
                                    </div>
                                    <p class="text-xs md:text-sm text-gray-600 font-medium">{{ $edu['institution'] }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- 3. Work & Professional Experience Background --}}
                    <div class="bg-surface rounded-3xl p-6 md:p-8 border border-gray-100 shadow-sm">
                        <h2 class="font-heading font-extrabold text-xl text-charcoal mb-5">
                            Work & Professional Background
                        </h2>
                        <div class="space-y-4">
                            @foreach ($workList as $work)
                            <div class="bg-white p-4 md:p-5 rounded-2xl border border-gray-100 flex items-start gap-4">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </div>
                                <div class="flex-1">
                                    <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
                                        <h4 class="font-heading font-bold text-charcoal text-sm md:text-base">{{ $work['role'] }}</h4>
                                        <span class="text-xs font-semibold text-gray-500 bg-slate-100 px-2.5 py-0.5 rounded-full">{{ $work['period'] }}</span>
                                    </div>
                                    <p class="text-xs font-bold text-primary mb-2">{{ $work['company'] }}</p>
                                    <p class="text-xs text-muted leading-relaxed">{{ $work['desc'] }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- 4. Teaching Methodology & Style --}}
                    <div class="bg-surface rounded-3xl p-6 md:p-8 border border-gray-100 shadow-sm">
                        <h2 class="font-heading font-extrabold text-xl text-charcoal mb-4">
                            Teaching Style & Approach
                        </h2>
                        <p class="text-muted leading-relaxed text-sm md:text-base mb-6">
                            {{ $style }}
                        </p>

                        <div class="grid sm:grid-cols-2 gap-4">
                            <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs">
                                <h4 class="font-heading font-bold text-charcoal text-sm mb-1">Visual Concept Notes</h4>
                                <p class="text-muted text-xs leading-relaxed">Mind maps and formula summaries for quick revision before exams.</p>
                            </div>
                            <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs">
                                <h4 class="font-heading font-bold text-charcoal text-sm mb-1">HOTS / KBAT Frameworks</h4>
                                <p class="text-muted text-xs leading-relaxed">Step-by-step methods to tackle 4-mark and 10-mark examination questions.</p>
                            </div>
                            <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs">
                                <h4 class="font-heading font-bold text-charcoal text-sm mb-1">Topical Past-Paper Drills</h4>
                                <p class="text-muted text-xs leading-relaxed">Practicing with actual trial papers and SPM/IGCSE marking criteria.</p>
                            </div>
                            <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs">
                                <h4 class="font-heading font-bold text-charcoal text-sm mb-1">Real-Time Q&A Support</h4>
                                <p class="text-muted text-xs leading-relaxed">Direct teacher assistance during and after live classes.</p>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Educator Q&A Section --}}
                    <div class="bg-surface rounded-3xl p-6 md:p-8 border border-gray-100 shadow-sm" x-data="{ activeFaq: null }">
                        <h2 class="font-heading font-extrabold text-xl text-charcoal mb-4">
                            Educator Q&A
                        </h2>
                        
                        <div class="space-y-3">
                            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                                <button @click="activeFaq = activeFaq === 1 ? null : 1" class="w-full text-left p-4 font-heading font-bold text-sm text-charcoal flex items-center justify-between cursor-pointer">
                                    <span>How do you support students struggling with tough topics?</span>
                                    <span class="text-charcoal font-bold" x-text="activeFaq === 1 ? '−' : '+'"></span>
                                </button>
                                <div x-show="activeFaq === 1" x-collapse class="px-4 pb-4 text-xs text-muted leading-relaxed border-t border-gray-100 pt-3">
                                    I break complex formulas and theories down into 3 simple steps: Understanding the Core Logic, Visualising with Real Examples, and Applying Mark-Allocation Templates.
                                </div>
                            </div>

                            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                                <button @click="activeFaq = activeFaq === 2 ? null : 2" class="w-full text-left p-4 font-heading font-bold text-sm text-charcoal flex items-center justify-between cursor-pointer">
                                    <span>What study materials do students receive?</span>
                                    <span class="text-charcoal font-bold" x-text="activeFaq === 2 ? '−' : '+'"></span>
                                </button>
                                <div x-show="activeFaq === 2" x-collapse class="px-4 pb-4 text-xs text-muted leading-relaxed border-t border-gray-100 pt-3">
                                    All students receive downloadable PDF formula workbooks, past-year topical question sets, and 24/7 access to HD class recording replays.
                                </div>
                            </div>

                            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                                <button @click="activeFaq = activeFaq === 3 ? null : 3" class="w-full text-left p-4 font-heading font-bold text-sm text-charcoal flex items-center justify-between cursor-pointer">
                                    <span>Can students ask questions outside of class hours?</span>
                                    <span class="text-charcoal font-bold" x-text="activeFaq === 3 ? '−' : '+'"></span>
                                </button>
                                <div x-show="activeFaq === 3" x-collapse class="px-4 pb-4 text-xs text-muted leading-relaxed border-t border-gray-100 pt-3">
                                    Yes! Students can post homework questions in our student portal or dedicated WhatsApp group for prompt guidance.
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- RIGHT COLUMN: STICKY BOOKING SIDEBAR --}}
                <div>
                    <div class="bg-surface rounded-3xl p-6 border border-gray-200 shadow-lg sticky top-24">
                        <span class="inline-block bg-gray-100 text-gray-800 text-xs font-semibold px-3 py-1 rounded-full mb-3 uppercase tracking-wider">
                            Free Trial Available
                        </span>
                        
                        <h3 class="font-heading font-extrabold text-xl text-charcoal mb-2">Book a Trial Class</h3>
                        <p class="text-muted text-xs leading-relaxed mb-6">
                            Join {{ $teacherName }}'s upcoming interactive live session. 100% free with no commitment required.
                        </p>

                        <div class="space-y-3 mb-6 bg-white p-4 rounded-2xl border border-gray-100">
                            <div class="flex items-center gap-3 text-xs font-semibold text-charcoal">
                                <span class="w-5 h-5 rounded-full bg-gray-100 text-gray-700 flex items-center justify-center font-bold text-xs">✓</span>
                                <span>1-on-1 & Small Group Live Sessions</span>
                            </div>
                            <div class="flex items-center gap-3 text-xs font-semibold text-charcoal">
                                <span class="w-5 h-5 rounded-full bg-gray-100 text-gray-700 flex items-center justify-center font-bold text-xs">✓</span>
                                <span>Includes PDF Notes & Question Bank</span>
                            </div>
                            <div class="flex items-center gap-3 text-xs font-semibold text-charcoal">
                                <span class="w-5 h-5 rounded-full bg-gray-100 text-gray-700 flex items-center justify-center font-bold text-xs">✓</span>
                                <span>24/7 Unlimited Class Recording Replay</span>
                            </div>
                        </div>

                        <a href="{{ route('register') }}" class="btn-primary w-full justify-center mb-3 py-3.5 text-sm font-extrabold shadow-md">
                            Enroll in Class Now
                        </a>
                        <a href="https://wa.me/60123456789" target="_blank" class="btn-secondary w-full justify-center py-3 text-xs font-bold flex items-center gap-2">
                            <span>WhatsApp Educator Team</span>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>
</x-app-layout>
