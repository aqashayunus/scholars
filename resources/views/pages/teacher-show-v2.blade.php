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

    <div x-data="{ ratingModalOpen: false, selectedRating: 5, hoverRating: 0, reviewSubmitted: false, userComment: '' }">

    {{-- HERO HEADER --}}
    <section class="relative bg-primary-950 text-white pt-12 pb-8 md:pt-16 md:pb-10 overflow-hidden">
        <!-- Background Image & Overlay -->
        <div class="absolute inset-0 z-0">
            <img src="{{ asset('images/hero-students.jpg') }}" alt="Olympia Scholar Teachers" class="w-full h-full object-cover" />
            <div class="absolute inset-0 bg-primary-950/80 mix-blend-multiply"></div>
            <div class="absolute inset-0 bg-gradient-to-br from-primary-950/90 via-primary-900/80 to-primary-800/60"></div>
        </div>

        <!-- Dynamic Background Pattern Overlay -->
        <div class="absolute inset-0 z-0 opacity-20 pointer-events-none">
            <svg class="absolute w-full h-full" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                        <path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="1"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#grid)" />
            </svg>
        </div>
        
        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 z-10 flex justify-center">
            <div class="w-full md:w-fit">
                {{-- Back Navigation --}}
            <div class="mb-8 animate-on-scroll opacity-0 translate-y-4 transition-all duration-700 ease-out">
                <a href="{{ route('teachers') }}" class="inline-flex items-center gap-2 text-white/70 hover:text-white text-sm font-semibold transition-colors group">
                    <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center backdrop-blur-sm border border-white/10 group-hover:bg-white/20 transition-all">
                        <svg class="w-4 h-4 transform transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </div>
                    Back to Teachers
                </a>
            </div>

            <div class="flex flex-col md:flex-row items-center md:items-start gap-8 lg:gap-12 text-center md:text-left">
                {{-- Educator Avatar Photo --}}
                <div class="animate-on-scroll opacity-0 scale-95 transition-all duration-1000 ease-out relative w-40 h-40 md:w-48 md:h-48 rounded-[2.5rem] overflow-hidden border-4 border-white/20 shadow-[0_20px_40px_rgba(0,0,0,0.3)] flex-shrink-0 bg-slate-900 group">
                    <img src="{{ $photo }}" alt="{{ $teacherName }}" class="w-full h-full object-cover object-top transition-transform duration-700 group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-primary-950/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                </div>

                {{-- Educator Header Meta --}}
                <div class="flex-1 mt-2 md:mt-4">
                    <h1 class="animate-on-scroll opacity-0 translate-y-8 transition-all duration-1000 ease-out delay-200 font-heading font-black text-4xl md:text-5xl lg:text-6xl text-white mb-4 tracking-tight drop-shadow-lg">
                        {{ $teacherName }}
                    </h1>
                    
                    <p class="animate-on-scroll opacity-0 translate-y-8 transition-all duration-1000 ease-out delay-300 text-white/80 text-lg md:text-xl font-light mb-6 max-w-2xl">
                        {{ $qualification }} &bull; <span class="font-bold text-white/90">{{ $experience }}+ Years</span> Teaching Experience
                    </p>

                    {{-- Rating & Stats Pills --}}
                    <div class="animate-on-scroll opacity-0 translate-y-8 transition-all duration-1000 ease-out delay-400 flex flex-wrap items-center justify-center md:justify-start gap-3 text-xs sm:text-sm font-bold">
                        {{-- Rating Pill with Hover Rate Icon Button --}}
                        <div @click="ratingModalOpen = true" 
                             class="group/rating relative flex items-center h-11 bg-white/10 hover:bg-amber-500/10 rounded-xl backdrop-blur-md border border-white/15 hover:border-amber-400/30 transition-all duration-300 cursor-pointer shadow-sm hover:shadow-md hover:shadow-amber-500/10 overflow-hidden">
                            <div class="flex items-center gap-2 px-4 transition-transform duration-300 group-hover/rating:md:-translate-x-1">
                                <span class="text-amber-400 text-base leading-none transform transition-transform group-hover/rating:scale-125 group-hover/rating:rotate-12 duration-300">★</span>
                                <span class="text-white">{{ $rating }} / 5.0</span>
                                <span class="text-white/60 font-medium hidden sm:inline">({{ number_format($totalStudents) }} students)</span>
                            </div>

                            {{-- Desktop Hover Rate Button Badge ("Rate →") --}}
                            <div class="hidden md:flex items-center justify-center overflow-hidden transition-all duration-300 ease-out max-w-0 opacity-0 group-hover/rating:max-w-[100px] group-hover/rating:opacity-100 bg-amber-400 hover:bg-amber-300 h-full border-l border-amber-300/50">
                                <span class="inline-flex items-center gap-1.5 px-4 text-slate-950 font-black text-sm whitespace-nowrap shrink-0">
                                    Rate
                                    <svg class="w-4 h-4 transform transition-transform group-hover/rating:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </span>
                            </div>
                        </div>

                        <div class="inline-flex items-center gap-2 h-11 bg-white/10 px-4 rounded-xl backdrop-blur-md border border-white/10 hover:bg-white/20 transition-colors cursor-default">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            <span class="text-white">SPM & IGCSE Specialist</span>
                        </div>

                        <div class="inline-flex items-center gap-2 h-11 bg-white/10 px-4 rounded-xl backdrop-blur-md border border-white/10 hover:bg-white/20 transition-colors cursor-default">
                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                            <span class="text-white">98% Distinction Rate</span>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>

        {{-- Smooth Curved Divider --}}
        <div class="absolute -bottom-1 left-0 right-0 overflow-hidden leading-none pointer-events-none z-10">
            <svg viewBox="0 0 1440 80" fill="none" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-10 md:h-14 block">
                <path d="M0,25 Q720,75 1440,25 L1440,80 L0,80 Z" fill="#f8fafc"></path>
            </svg>
        </div>
    </section>

    {{-- MAIN CONTENT GRID --}}
    <section class="py-16 md:py-24 bg-slate-50 relative -mt-4">
        <div class="max-w-6xl mx-auto px-2 sm:px-4 lg:px-4">
            <div class="grid lg:grid-cols-3 gap-8 lg:gap-12">
                
                {{-- LEFT COLUMN: DETAILS & Q&A --}}
                <div class="lg:col-span-2 space-y-4 md:space-y-5">
                    
                    {{-- 1. Educator Biography --}}
                    <div class="animate-on-scroll opacity-0 translate-y-12 transition-all duration-700 ease-out bg-white rounded-2xl p-6 lg:p-8 border border-slate-200 shadow-sm">
                        <h2 class="font-heading font-black text-2xl text-slate-900 mb-6 pb-4 border-b border-slate-100 flex items-center gap-3">
                            <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            Executive Summary
                        </h2>
                        <div class="relative pl-6 border-l-2 border-slate-300">
                            <svg class="w-8 h-8 text-slate-200 absolute -top-2 -left-4 bg-white" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 18L14.017 10.609C14.017 4.905 17.748 1.039 23 0L23.995 2.151C21.563 3.068 20 5.789 20 8H24V18H14.017ZM0 18V10.609C0 4.905 3.748 1.039 9 0L9.996 2.151C7.563 3.068 6 5.789 6 8H9.983L9.983 18L0 18Z" /></svg>
                            <p class="text-slate-700 leading-relaxed text-lg font-medium pt-2">
                                {{ $bio }}
                            </p>
                        </div>
                    </div>

                    {{-- 2. Education & Academic Background --}}
                    <div class="animate-on-scroll opacity-0 translate-y-12 transition-all duration-700 ease-out delay-100 bg-white rounded-2xl p-6 lg:p-8 border border-slate-200 shadow-sm">
                        <h2 class="font-heading font-black text-2xl text-slate-900 mb-8 pb-4 border-b border-slate-100 flex items-center gap-3">
                            <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path></svg>
                            Education Background
                        </h2>
                        <div class="relative border-l border-slate-300 ml-3 space-y-8 pb-4">
                            @foreach ($educationList as $edu)
                            <div class="relative pl-8 group">
                                <!-- Minimal Node -->
                                <div class="absolute -left-[5px] top-1.5 w-[9px] h-[9px] rounded-full bg-white border-2 border-slate-400 group-hover:border-slate-800 transition-colors duration-300 ring-4 ring-white"></div>
                                
                                <div class="flex flex-col sm:flex-row sm:items-baseline gap-2 sm:gap-6 mb-1">
                                    <span class="text-sm font-black text-slate-500 uppercase tracking-widest sm:w-32 shrink-0">{{ $edu['year'] }}</span>
                                    <h4 class="font-heading font-bold text-slate-900 text-lg">{{ $edu['degree'] }}</h4>
                                </div>
                                <p class="text-base text-slate-600 font-medium sm:ml-[9.5rem]">{{ $edu['institution'] }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- 3. Work & Professional Experience Background --}}
                    <div class="animate-on-scroll opacity-0 translate-y-12 transition-all duration-700 ease-out delay-200 bg-white rounded-2xl p-6 lg:p-8 border border-slate-200 shadow-sm">
                        <h2 class="font-heading font-black text-2xl text-slate-900 mb-8 pb-4 border-b border-slate-100 flex items-center gap-3">
                            <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            Professional Experience
                        </h2>
                        <div class="relative border-l border-slate-300 ml-3 space-y-10 pb-4">
                            @foreach ($workList as $work)
                            <div class="relative pl-8 group">
                                <!-- Minimal Node -->
                                <div class="absolute -left-[5px] top-1.5 w-[9px] h-[9px] rounded-full bg-white border-2 border-slate-400 group-hover:border-slate-800 transition-colors duration-300 ring-4 ring-white"></div>
                                
                                <div class="flex flex-col md:flex-row md:items-baseline gap-2 md:gap-6 mb-2">
                                    <span class="text-sm font-black text-slate-500 uppercase tracking-widest md:w-32 shrink-0">{{ $work['period'] }}</span>
                                    <div>
                                        <h4 class="font-heading font-bold text-slate-900 text-lg">{{ $work['role'] }}</h4>
                                        <p class="text-sm font-semibold text-slate-800 mt-0.5">{{ $work['company'] }}</p>
                                    </div>
                                </div>
                                <p class="text-base text-slate-600 font-medium md:ml-[9.5rem] leading-relaxed">{{ $work['desc'] }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- 4. Teaching Methodology & Style --}}
                    <div class="animate-on-scroll opacity-0 translate-y-12 transition-all duration-700 ease-out delay-300 bg-white rounded-2xl p-6 lg:p-8 border border-slate-200 shadow-sm">
                        <h2 class="font-heading font-black text-2xl text-slate-900 mb-4 pb-4 border-b border-slate-100 flex items-center gap-3">
                            <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                            Teaching Style & Approach
                        </h2>
                        <p class="text-slate-700 leading-relaxed text-lg font-medium mb-8">
                            {{ $style }}
                        </p>

                        <div class="space-y-4 border-t border-slate-100 pt-6">
                            <div class="flex items-start gap-4 group">
                                <svg class="w-6 h-6 text-slate-400 mt-0.5 group-hover:text-slate-800 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <div>
                                    <h4 class="font-heading font-bold text-slate-900 text-base mb-1">Visual Concept Notes</h4>
                                    <p class="text-slate-600 text-base font-medium leading-relaxed">Mind maps and formula summaries for quick revision before exams.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4 group">
                                <svg class="w-6 h-6 text-slate-400 mt-0.5 group-hover:text-slate-800 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <div>
                                    <h4 class="font-heading font-bold text-slate-900 text-base mb-1">HOTS / KBAT Frameworks</h4>
                                    <p class="text-slate-600 text-base font-medium leading-relaxed">Step-by-step methods to tackle 4-mark and 10-mark examination questions.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4 group">
                                <svg class="w-6 h-6 text-slate-400 mt-0.5 group-hover:text-slate-800 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <div>
                                    <h4 class="font-heading font-bold text-slate-900 text-base mb-1">Topical Past-Paper Drills</h4>
                                    <p class="text-slate-600 text-base font-medium leading-relaxed">Practicing with actual trial papers and SPM/IGCSE marking criteria.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4 group">
                                <svg class="w-6 h-6 text-slate-400 mt-0.5 group-hover:text-slate-800 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <div>
                                    <h4 class="font-heading font-bold text-slate-900 text-base mb-1">Real-Time Q&A Support</h4>
                                    <p class="text-slate-600 text-base font-medium leading-relaxed">Direct teacher assistance during and after live classes.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 5. Educator Q&A Section --}}
                    <div class="animate-on-scroll opacity-0 translate-y-12 transition-all duration-700 ease-out delay-400 bg-white rounded-2xl p-6 lg:p-8 border border-slate-200 shadow-sm">
                        <h2 class="font-heading font-black text-2xl text-slate-900 mb-6 pb-4 border-b border-slate-100 flex items-center gap-3">
                            <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                            Frequently Asked Questions
                        </h2>
                        
                        <div class="divide-y divide-slate-100">
                            <div class="py-2" x-data="{ open: false }">
                                <button @click="open = !open" class="w-full text-left py-4 font-heading font-bold text-base text-slate-900 flex items-center justify-between cursor-pointer focus:outline-none group">
                                    <span class="group-hover:text-slate-600 transition-colors">How do you support students struggling with tough topics?</span>
                                    <svg class="w-5 h-5 text-slate-400 transform transition-transform duration-300" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                                <div x-show="open" x-collapse.duration.300ms class="pb-5 text-base text-slate-600 leading-relaxed font-medium">
                                    I break complex formulas and theories down into 3 simple steps: Understanding the Core Logic, Visualising with Real Examples, and Applying Mark-Allocation Templates.
                                </div>
                            </div>

                            <div class="py-2" x-data="{ open: false }">
                                <button @click="open = !open" class="w-full text-left py-4 font-heading font-bold text-base text-slate-900 flex items-center justify-between cursor-pointer focus:outline-none group">
                                    <span class="group-hover:text-slate-600 transition-colors">What study materials do students receive?</span>
                                    <svg class="w-5 h-5 text-slate-400 transform transition-transform duration-300" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                                <div x-show="open" x-collapse.duration.300ms class="pb-5 text-base text-slate-600 leading-relaxed font-medium">
                                    All students receive downloadable PDF formula workbooks, past-year topical question sets, and 24/7 access to HD class recording replays.
                                </div>
                            </div>

                            <div class="py-2" x-data="{ open: false }">
                                <button @click="open = !open" class="w-full text-left py-4 font-heading font-bold text-base text-slate-900 flex items-center justify-between cursor-pointer focus:outline-none group">
                                    <span class="group-hover:text-slate-600 transition-colors">Can students ask questions outside of class hours?</span>
                                    <svg class="w-5 h-5 text-slate-400 transform transition-transform duration-300" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                                <div x-show="open" x-collapse.duration.300ms class="pb-5 text-base text-slate-600 leading-relaxed font-medium">
                                    Yes! Students can post homework questions in our student portal or dedicated WhatsApp group for prompt guidance.
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- RIGHT COLUMN: STICKY BOOKING SIDEBAR --}}
                <div class="lg:col-span-1">
                    <div class="sticky top-28 z-30">
                        <div class="animate-on-scroll opacity-0 translate-y-12 transition-all duration-700 ease-out delay-300 bg-white rounded-[2.5rem] p-8 border border-slate-100 shadow-2xl shadow-slate-200/50 relative overflow-hidden group">
                            {{-- Subtle Low-Opacity Card Background Image --}}
                            <div class="absolute inset-0 z-0 pointer-events-none overflow-hidden">
                                <img src="{{ asset('images/hero-students.jpg') }}" alt="Background Pattern" class="w-full h-full object-cover opacity-[0.25] mix-blend-multiply filter grayscale group-hover:scale-105 transition-transform duration-1000" />
                                <div class="absolute inset-0 bg-gradient-to-b from-white/80 via-white/80 to-white/90"></div>
                            </div>

                            {{-- Background Glow --}}
                            <div class="absolute -top-16 -right-16 w-40 h-40 bg-amber-400/10 rounded-full blur-3xl group-hover:bg-amber-400/20 transition-colors duration-700"></div>

                            {{-- Attached Top-Left Tag --}}
                            <div class="absolute top-0 left-0 bg-gradient-to-br from-rose-500 to-rose-600 border-r border-b border-rose-400/50 pr-5 pl-8 py-3 rounded-br-[2rem] z-20 flex items-center gap-2 shadow-sm">
                                <svg class="w-4 h-4 text-white transform -rotate-12 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                                <span class="text-[11px] font-black text-white uppercase tracking-widest">Free Trial Available</span>
                            </div>

                            <div class="relative z-10 pt-6">
                                <h3 class="font-heading font-black text-3xl text-slate-800 mb-3 leading-tight">Book a Trial Class</h3>
                                <p class="text-slate-500 text-sm font-medium leading-relaxed mb-8">
                                    Join {{ $teacherName }}'s upcoming interactive live session. 100% free with no commitment required.
                                </p>

                                <div class="space-y-4 mb-8 bg-slate-50 p-6 rounded-3xl border border-slate-100">
                                    <div class="flex items-center gap-4">
                                        <div class="w-6 h-6 rounded-full bg-emerald-500 text-white shadow-sm shadow-emerald-500/30 flex items-center justify-center shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                        <span class="text-sm font-semibold text-slate-700 leading-snug">1-on-1 & Small Group Live Sessions</span>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <div class="w-6 h-6 rounded-full bg-emerald-500 text-white shadow-sm shadow-emerald-500/30 flex items-center justify-center shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                        <span class="text-sm font-semibold text-slate-700 leading-snug">Includes PDF Notes & Question Bank</span>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <div class="w-6 h-6 rounded-full bg-emerald-500 text-white shadow-sm shadow-emerald-500/30 flex items-center justify-center shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                        <span class="text-sm font-semibold text-slate-700 leading-snug">24/7 Unlimited Class Recording Replay</span>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <a href="{{ route('register') }}" class="flex items-center justify-center w-full py-4 px-6 bg-primary-950 text-white font-black text-sm rounded-2xl hover:bg-amber-500 hover:text-slate-900 transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5 group/btn">
                                        Enroll in Class Now
                                        <svg class="w-5 h-5 ml-2 transform transition-transform group-hover/btn:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </a>
                                    <a href="https://wa.me/60123456789" target="_blank" class="flex items-center justify-center w-full py-3.5 px-6 bg-white border border-slate-200 text-slate-700 font-bold text-sm rounded-2xl hover:bg-emerald-50 hover:border-emerald-200 transition-all group/wa shadow-sm hover:shadow">
                                        <svg class="w-5 h-5 mr-2 text-emerald-500 group-hover/wa:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
                                        WhatsApp Educator Team
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- RATING MODAL POPUP --}}
    <div x-show="ratingModalOpen" 
         x-cloak 
         @keydown.escape.window="ratingModalOpen = false"
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        {{-- Backdrop --}}
        <div @click="ratingModalOpen = false" class="fixed inset-0 bg-slate-950/80 backdrop-blur-md"></div>

        {{-- Modal Content Card --}}
        <div class="relative bg-white w-full max-w-lg rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-100 z-10 overflow-hidden transform transition-all"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4">
             
            <!-- Close Button -->
            <button @click="ratingModalOpen = false" class="absolute top-5 right-5 w-10 h-10 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-800 flex items-center justify-center transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <template x-if="!reviewSubmitted">
                <div>
                    {{-- Header --}}
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-14 h-14 rounded-2xl overflow-hidden border-2 border-slate-200 shrink-0 bg-slate-100">
                            <img src="{{ $photo }}" alt="{{ $teacherName }}" class="w-full h-full object-cover object-top" />
                        </div>
                        <div>
                            <h3 class="font-heading font-black text-2xl text-slate-900 leading-tight">Rate {{ $teacherName }}</h3>
                            <p class="text-slate-500 text-xs font-semibold uppercase tracking-wider mt-0.5">{{ $qualification }}</p>
                        </div>
                    </div>

                    <p class="text-slate-600 text-sm leading-relaxed mb-6 font-medium">
                        How would you rate your learning experience with {{ $teacherName }}? Your feedback helps guide fellow students.
                    </p>

                    {{-- Star Selection --}}
                    <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100 text-center mb-6">
                        <div class="flex items-center justify-center gap-2 mb-2">
                            <template x-for="i in 5" :key="i">
                                <button type="button" 
                                        @mouseenter="hoverRating = i" 
                                        @mouseleave="hoverRating = 0" 
                                        @click="selectedRating = i"
                                        class="p-1 transform transition-transform hover:scale-125 focus:outline-none">
                                    <svg class="w-9 h-9 transition-colors duration-200" 
                                         :class="(hoverRating ? i <= hoverRating : i <= selectedRating) ? 'text-amber-400 fill-amber-400' : 'text-slate-200 fill-slate-200'" 
                                         viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </button>
                            </template>
                        </div>
                        <span class="text-sm font-bold text-slate-800" x-text="[
                            '1 Star — Needs Improvement',
                            '2 Stars — Fair Experience',
                            '3 Stars — Good Teaching',
                            '4 Stars — Very Good Educator',
                            '5 Stars — Outstanding Master Educator!'
                        ][(hoverRating || selectedRating) - 1]"></span>
                    </div>

                    {{-- Feedback Input --}}
                    <div class="mb-6">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Write a Review (Optional)</label>
                        <textarea x-model="userComment" 
                                  rows="3" 
                                  placeholder="Share specific topics, teaching techniques, or moments that helped you understand the subject..." 
                                  class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all font-medium resize-none"></textarea>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-3">
                        <button type="button" @click="ratingModalOpen = false" class="w-1/3 py-3.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl transition-colors">
                            Cancel
                        </button>
                        <button type="button" @click="reviewSubmitted = true" class="w-2/3 py-3.5 px-4 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 font-black text-sm rounded-xl transition-all shadow-lg hover:shadow-xl hover:shadow-amber-500/20 flex items-center justify-center gap-2">
                            <span>Submit Rating</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>
                </div>
            </template>

            <template x-if="reviewSubmitted">
                <div class="text-center py-6">
                    <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4 animate-bounce">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <h3 class="font-heading font-black text-2xl text-slate-900 mb-2">Thank You for Rating!</h3>
                    <p class="text-slate-600 text-sm font-medium leading-relaxed mb-6 max-w-sm mx-auto">
                        Your <span class="font-bold text-slate-900" x-text="selectedRating + ' ★'"></span> review for <span class="font-bold text-slate-900">{{ $teacherName }}</span> has been recorded.
                    </p>
                    <button type="button" @click="ratingModalOpen = false; setTimeout(() => { reviewSubmitted = false; userComment = ''; }, 500)" class="py-3 px-8 bg-slate-900 text-white font-bold text-sm rounded-xl hover:bg-slate-800 transition-colors shadow-md">
                        Close
                    </button>
                </div>
            </template>
        </div>
    </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.remove('opacity-0', 'translate-y-12', 'translate-y-8', 'translate-y-4', 'scale-95');
                        entry.target.classList.add('opacity-100', 'translate-y-0', 'scale-100');
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            });

            document.querySelectorAll('.animate-on-scroll').forEach(el => {
                observer.observe(el);
            });
        });
    </script>
    @endpush
</x-app-layout>
