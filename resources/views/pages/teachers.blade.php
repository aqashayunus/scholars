<x-app-layout>
    <x-slot name="title">Our Teachers</x-slot>

    {{-- ══════════════════════════════════════════════
         SECTION 1: HERO HEADER (Synchronized with Programmes)
    ══════════════════════════════════════════════ --}}
    <section class="relative bg-primary-950 text-white py-12 md:py-16 text-center overflow-hidden">
        <!-- Background Image & Overlay -->
        <div class="absolute inset-0 z-0">
            <img src="{{ asset('images/hero-students.jpg') }}" alt="Olympia Scholar Teachers" class="w-full h-full object-cover" />
            <div class="absolute inset-0 bg-primary-950/60 mix-blend-multiply"></div>
            <div class="absolute inset-0 bg-gradient-to-br from-primary-950/80 via-primary-900/60 to-primary-800/40"></div>
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

        <div class="relative max-w-4xl mx-auto px-4 z-10 flex flex-col items-center">
            <h1 class="animate-on-scroll opacity-0 translate-y-12 transition-all duration-1000 ease-out font-heading font-black text-5xl md:text-6xl lg:text-7xl text-white mb-6 drop-shadow-lg leading-tight">Our <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-amber-200">Teachers</span></h1>
            <p class="animate-on-scroll opacity-0 translate-y-12 transition-all duration-1000 ease-out delay-200 text-white/80 text-lg md:text-xl max-w-2xl font-light leading-relaxed">
                Passionate educators dedicated to guiding your child towards academic excellence and personal growth.
            </p>
        </div>

        {{-- Smooth Curved Divider --}}
        <div class="absolute -bottom-1 left-0 right-0 overflow-hidden leading-none pointer-events-none z-10">
            <svg viewBox="0 0 1440 80" fill="none" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-10 md:h-14 block">
                <path d="M0,25 Q720,75 1440,25 L1440,80 L0,80 Z" fill="#f8fafc"></path>
            </svg>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════
         SECTION 2: TEACHERS DIRECTORY (Staggered Editorial)
    ══════════════════════════════════════════════ --}}
    <section class="pt-20 pb-12 bg-slate-50 w-full relative -mt-4 overflow-hidden" x-data="{ selectedLevel: 'all', selectedSubject: 'all' }">
        <div class="max-w-[85rem] mx-auto px-4 sm:px-6 lg:px-8 relative z-20">
            
            {{-- Ultra-Sleek Floating Filter Island (Refined) --}}
            <div class="animate-on-scroll opacity-0 translate-y-12 transition-all duration-[1000ms] ease-out delay-[300ms] w-full md:w-fit max-w-full mx-auto bg-white border border-slate-200/80 shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-[2rem] md:rounded-full p-1.5 relative z-30 mb-8 lg:mb-12 flex flex-col md:flex-row items-center gap-1">
                
                {{-- 1. Education Level: Interactive Segmented Tabs --}}
                <div class="flex items-center overflow-x-auto w-full md:w-auto scrollbar-hide px-1 md:px-0">
                    <button @click="selectedLevel = 'all'; selectedSubject = 'all'" 
                            :class="selectedLevel === 'all' ? 'bg-primary-950 text-white shadow-md' : 'text-slate-500 hover:text-primary-950 hover:bg-slate-50'"
                            class="px-5 py-2 rounded-full text-[13px] font-bold transition-all duration-300 ease-out whitespace-nowrap">
                        All Levels
                    </button>
                    
                    <button @click="selectedLevel = 'kindergarten'; selectedSubject = 'all'" 
                            :class="selectedLevel === 'kindergarten' ? 'bg-amber-500 text-white shadow-md' : 'text-slate-500 hover:text-amber-500 hover:bg-amber-50'"
                            class="px-5 py-2 rounded-full text-[13px] font-bold transition-all duration-300 ease-out whitespace-nowrap">
                        Kindergarten
                    </button>

                    <button @click="selectedLevel = 'primary'; selectedSubject = 'all'" 
                            :class="selectedLevel === 'primary' ? 'bg-emerald-500 text-white shadow-md' : 'text-slate-500 hover:text-emerald-600 hover:bg-emerald-50'"
                            class="px-5 py-2 rounded-full text-[13px] font-bold transition-all duration-300 ease-out whitespace-nowrap">
                        Primary
                    </button>

                    <button @click="selectedLevel = 'secondary'; selectedSubject = 'all'" 
                            :class="selectedLevel === 'secondary' ? 'bg-primary-600 text-white shadow-md' : 'text-slate-500 hover:text-primary-600 hover:bg-primary-50'"
                            class="px-5 py-2 rounded-full text-[13px] font-bold transition-all duration-300 ease-out whitespace-nowrap">
                        Secondary
                    </button>
                </div>

                {{-- Subtle Vertical Divider --}}
                <div class="hidden md:block w-px h-5 bg-slate-200 mx-1.5"></div>

                {{-- Horizontal Divider for Mobile --}}
                <div class="md:hidden w-full h-px bg-slate-100 my-1"></div>

                {{-- 2. Subject Filter: Minimalist Dropdown --}}
                <div class="relative w-full md:w-auto flex items-center group">
                    <div class="absolute left-4 pointer-events-none text-slate-400 group-hover:text-primary-950 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    
                    <select x-model="selectedSubject" class="w-full md:w-[200px] lg:w-[220px] bg-transparent !bg-none hover:bg-slate-50 border border-transparent rounded-full pl-10 pr-10 py-2 text-[13px] font-bold text-slate-700 focus:ring-2 focus:ring-primary-950/10 focus:bg-slate-50 transition-all cursor-pointer appearance-none outline-none">
                        <option value="all">All Subjects</option>
                        
                        {{-- Kindergarten Subjects --}}
                        <option x-show="selectedLevel === 'kindergarten'" value="english">Phonics & Reading</option>
                        <option x-show="selectedLevel === 'kindergarten'" value="mathematics">Early Maths & Counting</option>
                        <option x-show="selectedLevel === 'kindergarten'" value="science">Science Discovery</option>
                        <option x-show="selectedLevel === 'kindergarten'" value="bahasa melayu">Storytelling & Expression</option>

                        {{-- Primary Subjects --}}
                        <option x-show="selectedLevel === 'primary'" value="bahasa melayu">Bahasa Melayu (KSSR)</option>
                        <option x-show="selectedLevel === 'primary'" value="english">English (KSSR)</option>
                        <option x-show="selectedLevel === 'primary'" value="mathematics">Mathematics (KSSR)</option>
                        <option x-show="selectedLevel === 'primary'" value="science">Science (KSSR)</option>

                        {{-- Secondary Subjects --}}
                        <option x-show="selectedLevel === 'secondary'" value="mathematics">Mathematics (SPM)</option>
                        <option x-show="selectedLevel === 'secondary'" value="additional mathematics">Additional Mathematics (SPM)</option>
                        <option x-show="selectedLevel === 'secondary'" value="physics">Physics (SPM / IGCSE)</option>
                        <option x-show="selectedLevel === 'secondary'" value="chemistry">Chemistry (SPM / IGCSE)</option>
                        <option x-show="selectedLevel === 'secondary'" value="biology">Biology (SPM / IGCSE)</option>
                        <option x-show="selectedLevel === 'secondary'" value="science">General Science (SPM)</option>
                        <option x-show="selectedLevel === 'secondary'" value="bahasa melayu">Bahasa Melayu (SPM)</option>
                        <option x-show="selectedLevel === 'secondary'" value="english">English (CEFR / IGCSE)</option>

                        {{-- All Education Levels --}}
                        <option x-show="selectedLevel === 'all'" value="mathematics">Mathematics</option>
                        <option x-show="selectedLevel === 'all'" value="additional mathematics">Additional Mathematics</option>
                        <option x-show="selectedLevel === 'all'" value="science">General Science</option>
                        <option x-show="selectedLevel === 'all'" value="physics">Physics</option>
                        <option x-show="selectedLevel === 'all'" value="chemistry">Chemistry</option>
                        <option x-show="selectedLevel === 'all'" value="biology">Biology</option>
                        <option x-show="selectedLevel === 'all'" value="bahasa melayu">Bahasa Melayu</option>
                        <option x-show="selectedLevel === 'all'" value="english">English (CEFR / IGCSE)</option>
                    </select>
                    
                    <div class="absolute right-4 pointer-events-none text-slate-400 group-hover:text-primary-950 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>

            {{-- Spotlight Grid Container (Robust CSS to prevent empty-space flickering) --}}
            <style>
                .spotlight-grid:has(.spotlight-card:hover) .spotlight-card:not(:hover) {
                    filter: blur(4px);
                    opacity: 0.7;
                }
            </style>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-10 spotlight-grid">
                @php
                    $allTeachers = [
                        [
                            'name' => 'Mr. Daniel Wong',
                            'qualification' => 'MSc in Applied Mathematics',
                            'experience' => '12+ Years Exp.',
                            'rating' => '4.9',
                            'reviews' => '128',
                            'photo' => asset('images/teacher-daniel.png'),
                            'subject' => 'Additional Mathematics',
                            'subject_keys' => ['mathematics', 'additional mathematics'],
                            'levels' => ['secondary'],
                            'key' => 'daniel_wong'
                        ],
                        [
                            'name' => 'Cikgu Aisyah',
                            'qualification' => 'BSc (Hons) Biomedical Science',
                            'experience' => '10+ Years Exp.',
                            'rating' => '4.9',
                            'reviews' => '112',
                            'photo' => asset('images/teacher-aisyah.png'),
                            'subject' => 'Biology',
                            'subject_keys' => ['science', 'biology'],
                            'levels' => ['secondary', 'primary'],
                            'key' => 'aisyah'
                        ],
                        [
                            'name' => 'Mr. Chong Wei',
                            'qualification' => 'MA in TESL',
                            'experience' => '13+ Years Exp.',
                            'rating' => '4.8',
                            'reviews' => '96',
                            'photo' => asset('images/teacher-chong.png'),
                            'subject' => 'English',
                            'subject_keys' => ['english'],
                            'levels' => ['secondary', 'primary', 'kindergarten'],
                            'key' => 'chong_wei'
                        ],
                        [
                            'name' => 'Cikgu Ahmad Farid',
                            'qualification' => 'BSc (Hons) Mathematics',
                            'experience' => '9+ Years Exp.',
                            'rating' => '4.9',
                            'reviews' => '104',
                            'photo' => asset('images/teacher-daniel.png'),
                            'subject' => 'Mathematics',
                            'subject_keys' => ['mathematics'],
                            'levels' => ['primary', 'secondary', 'kindergarten'],
                            'key' => 'ahmad_farid'
                        ],
                        [
                            'name' => 'Cikgu Siti Rahimah',
                            'qualification' => 'MSc in Chemical Engineering',
                            'experience' => '11+ Years Exp.',
                            'rating' => '4.8',
                            'reviews' => '88',
                            'photo' => asset('images/teacher-aisyah.png'),
                            'subject' => 'Chemistry',
                            'subject_keys' => ['science', 'chemistry'],
                            'levels' => ['secondary'],
                            'key' => 'siti_rahimah'
                        ],
                        [
                            'name' => 'Cikgu Rajesh Kumar',
                            'qualification' => 'BEng (Hons) Electrical',
                            'experience' => '14+ Years Exp.',
                            'rating' => '4.9',
                            'reviews' => '120',
                            'photo' => asset('images/teacher-chong.png'),
                            'subject' => 'Physics',
                            'subject_keys' => ['science', 'physics'],
                            'levels' => ['secondary'],
                            'key' => 'rajesh_kumar'
                        ],
                        [
                            'name' => 'Cikgu Nurul Ain',
                            'qualification' => 'BA (Hons) Bahasa Melayu',
                            'experience' => '10+ Years Exp.',
                            'rating' => '4.8',
                            'reviews' => '92',
                            'photo' => asset('images/teacher-aisyah.png'),
                            'subject' => 'Bahasa Melayu',
                            'subject_keys' => ['bahasa melayu'],
                            'levels' => ['secondary', 'primary', 'kindergarten'],
                            'key' => 'nurul_ain'
                        ],
                    ];
                @endphp

                @foreach ($allTeachers as $index => $t)
                @php
                    // Create the staggered masonry effect (push middle column down on lg screens)
                    $staggerClass = ($index % 3 == 1) ? 'lg:translate-y-16' : '';
                @endphp
                <div x-show="(selectedLevel === 'all' || {{ json_encode($t['levels']) }}.includes(selectedLevel)) && (selectedSubject === 'all' || {{ json_encode($t['subject_keys']) }}.includes(selectedSubject))"
                     x-transition:enter="transition ease-out duration-700 delay-[{{ $index * 50 }}ms]"
                     x-transition:enter-start="opacity-0 translate-y-12"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="group spotlight-card relative w-full aspect-[4/5] sm:aspect-[3/4] {{ $staggerClass }} transition-all duration-[700ms] ease-out z-10 hover:z-20 cursor-pointer">
                    
                    {{-- 
                      Outer Pill Container 
                      Uses p-2 or p-2.5 to create an inner frame for the shrink effect 
                    --}}
                    <div class="animate-on-scroll opacity-0 translate-y-12 relative w-full h-full bg-white rounded-[2.5rem] lg:rounded-[3rem] p-2.5 shadow-sm transition-all duration-[700ms] ease-out delay-[{{ ($index % 3) * 150 }}ms] group-hover:delay-0 border border-gray-100 group-hover:-translate-y-2 group-hover:scale-[1.02] group-hover:shadow-[0_20px_40px_-15px_rgba(0,0,0,0.15)]">
                        
                        {{-- The Image Box (Shrinks on hover) --}}
                        <div class="relative w-full h-full rounded-[2rem] lg:rounded-[2.5rem] overflow-hidden transition-all duration-[700ms] ease-[cubic-bezier(0.23,1,0.32,1)] group-hover:h-[45%] group-hover:rounded-3xl shadow-inner bg-slate-100 z-20">
                            
                            {{-- Image (Full color by default, scales on hover) --}}
                            <img src="{{ $t['photo'] }}" alt="{{ $t['name'] }}" class="w-full h-full object-cover transition-all duration-[800ms] ease-out group-hover:scale-105">
                            
                            {{-- Gradient Overlay (Fades out slightly on hover) --}}
                            <div class="absolute inset-0 bg-gradient-to-t from-primary-950/90 via-primary-950/20 to-transparent transition-opacity duration-700 group-hover:opacity-60"></div>

                            {{-- Floating Default Info (Moves with the image) --}}
                            <div class="absolute bottom-0 left-0 p-6 md:p-8 w-full flex flex-col justify-end transition-all duration-[700ms] ease-[cubic-bezier(0.23,1,0.32,1)]">
                                <span class="text-amber-300 font-extrabold text-[10px] tracking-widest uppercase mb-1 drop-shadow-md transform transition-transform duration-500 group-hover:-translate-y-1">{{ $t['subject'] }}</span>
                                <h3 class="font-heading font-black text-white text-2xl lg:text-3xl leading-none drop-shadow-lg transform transition-transform duration-500 group-hover:-translate-y-1">{{ $t['name'] }}</h3>
                            </div>
                        </div>
                        
                        {{-- The Drawer Box (Reveals from below) --}}
                        <div class="absolute bottom-0 left-0 right-0 top-[46%] bg-white rounded-t-[2.5rem] p-6 lg:p-7 flex flex-col opacity-0 translate-y-12 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-[700ms] ease-[cubic-bezier(0.23,1,0.32,1)] z-20 pointer-events-none group-hover:pointer-events-auto shadow-[0_-10px_40px_rgba(0,0,0,0.08)]">
                            
                            {{-- Staggered Content Reveal --}}
                            <div class="flex-1 flex flex-col justify-center transform opacity-0 translate-y-6 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-500 delay-[100ms]">
                                
                                {{-- Credential Header --}}
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                                    </div>
                                    <p class="text-slate-800 text-sm font-bold leading-tight">{{ $t['qualification'] }}</p>
                                </div>

                                {{-- Teaches (Levels) --}}
                                <div class="mb-4 pl-1">
                                    <p class="text-slate-400 text-[9px] font-bold uppercase tracking-widest mb-2">Teaches</p>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($t['levels'] as $level)
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600">
                                                {{ $level }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Stats Grid --}}
                                <div class="grid grid-cols-2 gap-4 border-t border-slate-100 pt-4">
                                    <div class="pl-1">
                                        <p class="text-slate-400 text-[9px] font-bold uppercase tracking-widest mb-1">Experience</p>
                                        <p class="text-slate-800 font-black text-sm flex items-end gap-1">
                                            {{ explode(' ', $t['experience'])[0] }} <span class="text-slate-500 text-[10px] font-semibold pb-[2px]">Yrs</span>
                                        </p>
                                    </div>
                                    <div class="pl-4 border-l border-slate-100">
                                        <p class="text-slate-400 text-[9px] font-bold uppercase tracking-widest mb-1">Rating</p>
                                        <p class="text-slate-800 font-black text-sm flex items-center gap-1.5">
                                            <span class="text-amber-400 text-sm leading-none mt-[1px]">★</span> 
                                            {{ $t['rating'] }} 
                                            <span class="text-slate-400 text-[11px] font-semibold">({{ $t['reviews'] }})</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Buttons Reveal --}}
                            <div class="mt-5 flex gap-3 transform opacity-0 translate-y-6 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-500 delay-[150ms]">
                                <a href="{{ route('teacher.show', $loop->iteration) }}" class="flex-1 bg-primary-950 text-white text-[13px] font-bold py-3.5 rounded-2xl text-center hover:bg-amber-500 hover:text-amber-950 transition-colors shadow-md hover:shadow-lg flex items-center justify-center gap-2 group/btn">
                                    View Profile
                                    <svg class="w-4 h-4 transform transition-transform group-hover/btn:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </a>
                                <a href="{{ route('free-trial') }}" class="w-[52px] flex-shrink-0 flex items-center justify-center bg-white border border-slate-200 text-slate-700 rounded-2xl hover:bg-slate-50 hover:text-primary-950 hover:border-slate-300 transition-all shadow-sm group/btn2" title="Book Class">
                                    <svg class="w-5 h-5 text-slate-500 group-hover/btn2:text-primary-950 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            {{-- Empty State --}}
            <div x-show="!$el.previousElementSibling.innerText.trim()" style="display: none;" class="text-center py-32">
                <div class="w-24 h-24 bg-white shadow-sm rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="font-heading font-bold text-2xl text-slate-800 mb-2">No teachers found</h3>
                <p class="text-slate-500 mb-8 max-w-sm mx-auto">Try adjusting your filters or clearing them to see all our expert educators.</p>
                <button @click="selectedLevel = 'all'; selectedSubject = 'all'" class="px-8 py-3.5 bg-amber-500 text-white text-sm font-bold rounded-full hover:bg-amber-600 transition-all hover:shadow-lg hover:shadow-amber-500/30">Clear All Filters</button>
            </div>
            
        </div>
    </section>

    {{-- ══════════════════════════════════════════════
         SECTION 3: CALL TO ACTION
    ══════════════════════════════════════════════ --}}
    <section class="relative w-full bg-primary-950 text-white overflow-hidden border-t border-slate-200">
        
        {{-- Background Image with Elegant Overlay --}}
        <div class="absolute inset-0 z-0">
            <img src="{{ asset('images/hero-students.jpg') }}" alt="Olympia Scholars Education" class="w-full h-full object-cover object-center opacity-40 mix-blend-luminosity grayscale">
            <div class="absolute inset-0 bg-gradient-to-r from-primary-950 via-primary-950/95 to-primary-900/70"></div>
            {{-- Vibrant Light Leaks --}}
            <div class="absolute -right-20 -top-20 w-[600px] h-[600px] bg-amber-500/20 rounded-full blur-[100px] mix-blend-screen pointer-events-none"></div>
            <div class="absolute -left-20 -bottom-20 w-[500px] h-[500px] bg-emerald-500/15 rounded-full blur-[80px] mix-blend-screen pointer-events-none"></div>
        </div>

        <div class="max-w-[85rem] mx-auto relative z-10 flex flex-col lg:flex-row items-center justify-between px-4 sm:px-6 lg:px-8 py-12 lg:py-16 gap-10 lg:gap-16">
            
            {{-- Left Content: Text --}}
            <div class="flex-1 text-center lg:text-left py-4">
                <span class="animate-on-scroll opacity-0 translate-y-8 transition-all duration-[800ms] ease-out inline-block px-4 py-1.5 rounded-full bg-white/10 text-white/90 text-[11px] font-black tracking-widest uppercase mb-5 shadow-sm border border-white/20 backdrop-blur-md">
                    Start Your Journey
                </span>
                
                <h2 class="animate-on-scroll opacity-0 translate-y-8 transition-all duration-[800ms] ease-out delay-100 font-heading font-black text-3xl md:text-4xl lg:text-5xl text-white mb-5 tracking-tight leading-tight">
                    Learn With Our <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-amber-200">Expert Teachers</span>
                </h2>
                
                <p class="animate-on-scroll opacity-0 translate-y-8 transition-all duration-[800ms] ease-out delay-200 text-white/70 text-base md:text-lg font-light max-w-xl mx-auto lg:mx-0 leading-relaxed">
                    Join thousands of students scoring A+ in SPM & IGCSE. Book a session today and experience the Olympian Scholars difference.
                </p>
            </div>
            
            {{-- Right Content: Frosted CTA Widget --}}
            <div class="w-full max-w-sm lg:max-w-none lg:w-auto flex-shrink-0 animate-on-scroll opacity-0 translate-y-8 transition-all duration-[800ms] ease-out delay-300">
                <div class="bg-white/10 backdrop-blur-2xl border border-white/20 rounded-[2rem] p-8 flex flex-col items-center justify-center text-center shadow-[0_20px_40px_rgba(0,0,0,0.3)] relative overflow-hidden group hover:bg-white/[0.15] transition-colors duration-500">
                    
                    {{-- Avatar Cluster --}}
                    <div class="flex items-center justify-center mb-6 relative z-10">
                        <img src="https://ui-avatars.com/api/?name=D&background=f59e0b&color=fff&font-size=0.5" class="w-12 h-12 rounded-full border-2 border-primary-950 shadow-md">
                        <img src="https://ui-avatars.com/api/?name=S&background=10b981&color=fff&font-size=0.5" class="w-12 h-12 rounded-full border-2 border-primary-950 -ml-4 shadow-md relative z-10">
                        <img src="https://ui-avatars.com/api/?name=A&background=3b82f6&color=fff&font-size=0.5" class="w-12 h-12 rounded-full border-2 border-primary-950 -ml-4 shadow-md relative z-20">
                        <div class="w-12 h-12 rounded-full border-2 border-primary-950 -ml-4 shadow-md relative z-30 bg-slate-100 flex items-center justify-center text-[12px] font-black text-slate-800">
                            +50
                        </div>
                    </div>
                    
                    <h3 class="text-white font-bold text-xl mb-1.5 relative z-10">Ready to start?</h3>
                    <p class="text-white/60 text-sm mb-7 relative z-10 font-medium">Claim your free trial class now</p>
                    
                    <a href="{{ route('free-trial') }}" class="relative z-10 overflow-hidden inline-flex items-center justify-center w-full font-bold text-sm md:text-base py-3.5 px-8 rounded-full bg-amber-500 text-slate-900 transition-all hover:scale-105 hover:shadow-xl hover:shadow-amber-500/40">
                        <span class="flex items-center gap-2">
                            Book Free Trial
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </span>
                    </a>
                    
                    {{-- Inner Card Glow --}}
                    <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-[80%] h-12 bg-amber-500/20 blur-2xl rounded-full pointer-events-none"></div>
                </div>
            </div>
            
        </div>
    </section>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.remove('opacity-0', 'translate-y-12');
                        entry.target.classList.add('opacity-100', 'translate-y-0');
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
