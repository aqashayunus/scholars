<x-app-layout>
    <x-slot name="title">Home</x-slot>

    {{-- ══════════════════════════════════════════════
         SECTION 1: HERO
    ══════════════════════════════════════════════ --}}
    <section class="relative bg-gradient-brand text-white overflow-hidden min-h-[60vh] flex items-center"
             x-data="{ 
                 activeSlide: 0, 
                 autoplayInterval: null,
                 slides: 3,
                 startAutoplay() {
                     this.autoplayInterval = setInterval(() => { 
                         this.activeSlide = (this.activeSlide + 1) % this.slides; 
                     }, 7000);
                 },
                 resetAutoplay(index) {
                     this.activeSlide = index;
                     clearInterval(this.autoplayInterval);
                     this.startAutoplay();
                 }
             }"
             x-init="startAutoplay()">
        
        {{-- Background decorative elements --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-96 h-96 bg-white/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-accent/10 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-white/3 rounded-full blur-3xl"></div>
            {{-- Subtle grid pattern --}}
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(rgba(255,255,255,0.15) 1px, transparent 1px); background-size: 24px 24px;"></div>
        </div>

        @php
            $heroSlides = [
                [
                    'headline' => 'Achieve More With<br><span class="text-accent">Expert Guidance</span>',
                    'desc' => 'Master your subjects with Malaysia\'s top educators. Experience personalized learning, premium resources, and exam-focused programs built for your success.',
                    'image' => asset('images/hero-students.jpg'),
                    'position' => 'object-center'
                ],
                [
                    'headline' => 'Master Your Exams<br><span class="text-accent">With Confidence</span>',
                    'desc' => 'Access proven strategies, comprehensive mock papers, and targeted practice specifically designed for Malaysian national syllabuses.',
                    'image' => 'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?auto=format&fit=crop&w=1200&q=80',
                    'position' => 'object-center'
                ],
                [
                    'headline' => 'Interactive Learning<br><span class="text-accent">Made Simple</span>',
                    'desc' => 'Engage with interactive digital lessons, monitor your progress in real-time, and study anywhere on our cutting-edge digital platform.',
                    'image' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80',
                    'position' => 'object-center'
                ],
            ];
        @endphp

        {{-- Slide Background Images & Overlays --}}
        @foreach ($heroSlides as $index => $slide)
        <div class="absolute top-0 right-0 w-full lg:w-[60%] h-full overflow-hidden pointer-events-none z-0" 
             style="-webkit-mask-image: linear-gradient(to right, transparent 0%, black 35%, black 100%); mask-image: linear-gradient(to right, transparent 0%, black 35%, black 100%);"
             x-show="activeSlide === {{ $index }}"
             x-transition:enter="transition ease-out duration-[1500ms]"
             x-transition:enter-start="opacity-0 scale-105"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-1000"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-105">
            <img src="{{ $slide['image'] }}" alt="Hero Background" class="w-full h-full object-cover {{ $slide['position'] }} filter brightness-95 lg:brightness-105 contrast-105">
            <div class="absolute inset-0 bg-gradient-to-r from-primary-900 via-primary-900/90 to-primary-900/75 lg:bg-gradient-to-t lg:from-primary-900/80 lg:via-transparent lg:to-primary-900/30"></div>
        </div>
        @endforeach

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 pb-20 lg:pb-24 w-full">
            <div class="grid lg:grid-cols-2 gap-12 items-center min-h-[400px]">
                {{-- Left: Copy --}}
                <div class="relative min-h-[350px] grid grid-cols-1 grid-rows-1">
                    @foreach ($heroSlides as $index => $slide)
                    <div x-show="activeSlide === {{ $index }}"
                         x-transition:enter="transition ease-out duration-700 delay-300"
                         x-transition:enter-start="opacity-0 translate-y-8"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-300"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-8"
                         class="col-start-1 row-start-1 w-full flex flex-col justify-center z-10">
                        
                        <h1 class="font-heading font-bold text-[2.5rem] leading-[1.1] sm:text-5xl lg:text-[3.5rem] text-white mb-6 tracking-tight">
                            {!! $slide['headline'] !!}
                        </h1>

                        <p class="text-white/85 text-lg leading-relaxed mb-8 max-w-xl">
                            {{ $slide['desc'] }}
                        </p>

                        <div class="flex flex-wrap gap-4 mb-10">
                            <a href="{{ route('register') }}" class="btn-accent text-base px-8 py-4 shadow-lg shadow-accent/20 hover:scale-105 transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                Register Now
                            </a>
                            <a href="{{ route('subjects') }}" class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm border border-white/30 text-white font-semibold px-8 py-4 rounded-full hover:bg-white/20 transition-all duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                Explore Courses
                            </a>
                        </div>
                    </div>
                    @endforeach

                    {{-- Slider Controls (Dots) --}}
                    <div class="absolute -bottom-8 lg:bottom-0 left-0 pl-1 sm:pl-2 flex items-center gap-2.5 z-20">
                        <template x-for="i in slides" :key="i">
                            <button @click="resetAutoplay(i - 1)" 
                                    class="h-2 rounded-full transition-all duration-500 overflow-hidden relative cursor-pointer outline-none"
                                    :class="activeSlide === (i - 1) ? 'w-20 bg-white/10 ring-1 ring-white/20 shadow-lg' : 'w-2.5 bg-white/40 hover:bg-white/80 hover:w-6'">
                                <div class="absolute top-0 left-0 bottom-0 bg-gradient-to-r from-amber-400 to-amber-300 transition-transform ease-linear origin-left"
                                     :class="activeSlide === (i - 1) ? 'scale-x-100 duration-[7000ms]' : 'scale-x-0 duration-0'"></div>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Right side kept clear for background picture --}}
                <div class="hidden lg:block"></div>
            </div>
        </div>

        {{-- Smooth Curved Divider --}}
        <div class="absolute -bottom-1 left-0 right-0 overflow-hidden leading-none pointer-events-none z-10">
            <svg viewBox="0 0 1440 60" fill="none" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-10 md:h-14 block">
                <path d="M0,20 Q720,75 1440,20 L1440,60 L0,60 Z" fill="white"/>
            </svg>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════
         SECTION 2: CONTINUOUS INFINITE SUBJECT SLIDER (FLOATING OVER HERO BOTTOM)
    ══════════════════════════════════════════════ --}}
    <section class="-mt-14 sm:-mt-20 relative z-20 overflow-hidden py-3">
        <style>
            @keyframes marquee-slide {
                0% { transform: translateX(0%); }
                100% { transform: translateX(-50%); }
            }
            .animate-marquee-track {
                display: flex;
                width: max-content;
                animation: marquee-slide 35s linear infinite;
            }
            .animate-marquee-track:hover {
                animation-play-state: paused;
            }
        </style>

        <div class="relative w-full overflow-hidden py-4" style="-webkit-mask-image: linear-gradient(to right, black 0%, black 75%, transparent 100%); mask-image: linear-gradient(to right, black 0%, black 75%, transparent 100%);">
            {{-- Seamless CSS mask applied on parent container with a fade on the right side only --}}

            @php
                $bookIcon = 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253';
                $calcIcon = 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z';
                $scienceIcon = 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z';
                $langIcon = 'M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129';
                $starIcon = 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z';
                $historyIcon = 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222';
                $boltIcon = 'M13 10V3L4 14h7v7l9-11h-7z';

                $subjectIcons = [
                    'Phonics & Reading'           => $bookIcon,
                    'Early Mathematics & Counting'=> $calcIcon,
                    'Early Science Exploration'   => $scienceIcon,
                    'Bahasa Melayu Awal'          => $bookIcon,
                    'Arts & Creativity'           => $starIcon,
                    'Bahasa Melayu (Primary)'     => $bookIcon,
                    'English (Primary)'           => $langIcon,
                    'Mathematics (Primary)'       => $calcIcon,
                    'Science (Primary)'           => $scienceIcon,
                    'Sejarah (Primary)'           => $historyIcon,
                    'Mathematics'                 => $calcIcon,
                    'Additional Mathematics'      => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4',
                    'Science'                     => $scienceIcon,
                    'Physics'                     => $boltIcon,
                    'Chemistry'                   => $scienceIcon,
                    'Biology'                     => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
                    'Bahasa Melayu'               => $bookIcon,
                    'English'                     => $langIcon,
                    'Sejarah'                     => $historyIcon,
                    'Prinsip Perakaunan'          => $calcIcon,
                    'Perniagaan'                  => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
                    'Ekonomi'                     => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
                ];

                $subjectImages = [
                    // Kindergarten
                    'Phonics & Reading'           => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=600&q=80',
                    'Early Mathematics & Counting'=> 'https://images.unsplash.com/photo-1596495578065-6e0763fa1178?auto=format&fit=crop&w=600&q=80',
                    'Early Science Exploration'   => 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?auto=format&fit=crop&w=600&q=80',
                    'Bahasa Melayu Awal'          => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=600&q=80',
                    'Arts & Creativity'           => 'https://images.unsplash.com/photo-1513364776144-60967b0f800f?auto=format&fit=crop&w=600&q=80',

                    // Primary
                    'Bahasa Melayu (Primary)'     => 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?auto=format&fit=crop&w=600&q=80',
                    'English (Primary)'           => 'https://images.unsplash.com/photo-1455390582262-044cdead277a?auto=format&fit=crop&w=600&q=80',
                    'Mathematics (Primary)'       => 'https://images.unsplash.com/photo-1509228468518-180dd4864904?auto=format&fit=crop&w=600&q=80',
                    'Science (Primary)'           => 'https://images.unsplash.com/photo-1507668077129-56e32842fceb?auto=format&fit=crop&w=600&q=80',
                    'Sejarah (Primary)'           => 'https://images.unsplash.com/photo-1461360370896-922624d12aa1?auto=format&fit=crop&w=600&q=80',

                    // Lower Secondary
                    'Bahasa Melayu (Lower Sec)'   => 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?auto=format&fit=crop&w=600&q=80',
                    'English (Lower Sec)'         => 'https://images.unsplash.com/photo-1457369804613-52c61a468e7d?auto=format&fit=crop&w=600&q=80',
                    'Mathematics (Lower Sec)'     => 'https://images.unsplash.com/photo-1635070041078-e363dbe005cb?auto=format&fit=crop&w=600&q=80',
                    'Science (Lower Sec)'         => 'https://images.unsplash.com/photo-1532187863486-abf9dbad1b69?auto=format&fit=crop&w=600&q=80',
                    'Sejarah (Lower Sec)'         => 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=600&q=80',

                    // Upper Secondary / Core
                    'Mathematics'                 => 'https://images.unsplash.com/photo-1509228468518-180dd4864904?auto=format&fit=crop&w=600&q=80',
                    'Additional Mathematics'      => 'https://images.unsplash.com/photo-1635070041078-e363dbe005cb?auto=format&fit=crop&w=600&q=80',
                    'Science'                      => 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?auto=format&fit=crop&w=600&q=80',
                    'Physics'                      => 'https://images.unsplash.com/photo-1636466497217-26a8cbeaf0aa?auto=format&fit=crop&w=600&q=80',
                    'Chemistry'                    => 'https://images.unsplash.com/photo-1603126857599-f6e157fa2fe6?auto=format&fit=crop&w=600&q=80',
                    'Biology'                      => 'https://images.unsplash.com/photo-1530026405186-ed1f139313f8?auto=format&fit=crop&w=600&q=80',
                    'Bahasa Melayu'                => 'https://images.unsplash.com/photo-1457369804613-52c61a468e7d?auto=format&fit=crop&w=600&q=80',
                    'English'                      => 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?auto=format&fit=crop&w=600&q=80',
                    'Sejarah'                      => 'https://images.unsplash.com/photo-1461360370896-922624d12aa1?auto=format&fit=crop&w=600&q=80',
                    'Prinsip Perakaunan'           => 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?auto=format&fit=crop&w=600&q=80',
                    'Perniagaan'                   => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=600&q=80',
                    'Ekonomi'                      => 'https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?auto=format&fit=crop&w=600&q=80',
                ];

                $fallbackImages = [
                    'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=600&q=80',
                    'https://images.unsplash.com/photo-1513364776144-60967b0f800f?auto=format&fit=crop&w=600&q=80',
                    'https://images.unsplash.com/photo-1509228468518-180dd4864904?auto=format&fit=crop&w=600&q=80',
                    'https://images.unsplash.com/photo-1532094349884-543bc11b234d?auto=format&fit=crop&w=600&q=80',
                    'https://images.unsplash.com/photo-1603126857599-f6e157fa2fe6?auto=format&fit=crop&w=600&q=80',
                    'https://images.unsplash.com/photo-1530026405186-ed1f139313f8?auto=format&fit=crop&w=600&q=80',
                    'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?auto=format&fit=crop&w=600&q=80',
                ];

                $duplicatedSubjects = $subjects->concat($subjects);
            @endphp
            <div class="animate-marquee-track gap-4">
                @foreach ($duplicatedSubjects as $subject)
                @php
                    $icon = $subjectIcons[$subject->name] ?? $bookIcon;
                    $fallbackIdx = ($subject->id ?? 0) % count($fallbackImages);
                    $bgImg = $subjectImages[$subject->name] ?? $fallbackImages[$fallbackIdx];
                    $overlayClass = match($subject->level) {
                        'Kindergarten'    => 'from-amber-950 via-amber-900/50 to-transparent',
                        'Primary'         => 'from-emerald-950 via-emerald-900/50 to-transparent',
                        'Lower Secondary' => 'from-blue-950 via-blue-900/50 to-transparent',
                        default           => 'from-purple-950 via-purple-900/50 to-transparent',
                    };
                @endphp
                <div class="flex-none w-[240px] sm:w-[280px]">
                    <a href="{{ route('subject.show', $subject) }}" @click.prevent="openSubjectModal('{{ $subject->display_name ?? $subject->name }}')" class="group relative overflow-hidden rounded-3xl p-5 text-left flex flex-col justify-between h-[180px] sm:h-[210px] shadow-lg hover:shadow-2xl border border-white/20 hover:-translate-y-2 transition-all duration-300 block cursor-pointer">
                        {{-- Background Picture --}}
                        <img src="{{ $bgImg }}" alt="{{ $subject->display_name ?? $subject->name }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-out">
                        
                        {{-- Gradient Overlay (Darker at bottom for text readability, clear at top) --}}
                        <div class="absolute inset-0 bg-gradient-to-t {{ $overlayClass }} opacity-90 group-hover:opacity-100 transition-opacity duration-300"></div>

                        {{-- Top Section: Badge & Icon --}}
                        <div class="relative z-10 w-full flex justify-between items-start">
                            {{-- Badge --}}
                            <span class="inline-block bg-white/20 backdrop-blur-md border border-white/20 text-white text-[10px] sm:text-[11px] font-bold px-3 py-1.5 rounded-full uppercase tracking-wider shadow-sm">
                                {{ $subject->level }}
                            </span>

                            {{-- Icon --}}
                            <div class="w-10 h-10 bg-white/10 backdrop-blur-md border border-white/20 text-white rounded-full flex items-center justify-center group-hover:bg-amber-400 group-hover:text-primary-950 group-hover:border-transparent group-hover:rotate-12 transition-all duration-300 shadow-sm shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/></svg>
                            </div>
                        </div>

                        {{-- Bottom Section: Subject Name --}}
                        <div class="relative z-10 w-full mt-auto">
                            <h3 class="font-heading font-bold text-white text-lg sm:text-xl group-hover:text-amber-300 transition-colors tracking-tight line-clamp-2 leading-snug drop-shadow-md">
                                {{ $subject->display_name ?? $subject->name }}
                            </h3>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════
         SECTION 3: WHY OLYMPIAN SCHOLARS (WHY CHOOSE US)
    ══════════════════════════════════════════════ --}}
    <section class="pt-24 pb-12 bg-slate-50/50 relative overflow-hidden" 
             x-data="{ 
                 activeFeature: 0, 
                 autoplayInterval: null,
                 startAutoplay() {
                     this.autoplayInterval = setInterval(() => { 
                         this.activeFeature = (this.activeFeature + 1) % 6; 
                     }, 6000);
                 },
                 resetAutoplay(index) {
                     this.activeFeature = index;
                     clearInterval(this.autoplayInterval);
                     this.startAutoplay();
                 }
             }"
             x-init="startAutoplay()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div data-aos="fade-up" class="text-center mb-16 relative">
                <p class="section-label mb-2 text-primary-600 font-extrabold tracking-wider uppercase text-sm">Why Choose Us</p>
                <h2 class="section-title font-heading font-black text-4xl md:text-5xl text-slate-900 drop-shadow-sm">Why Olympian Scholars?</h2>
                <p class="font-heading font-bold text-lg sm:text-xl md:text-2xl text-[#b08736] tracking-tight mt-2">60 Strengths, Summarised into 6 Core Pillars</p>
                <p class="section-subtitle mt-2 text-slate-600 max-w-2xl mx-auto text-base">Learn Today. Excel Tomorrow.</p>
            </div>

            @php
                $whys = [
                    ['icon' => 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222', 'title' => 'Academic Excellence', 'desc' => 'Structured academic pathway, mastery-based learning support, and exam-focused progress development.', 'image' => 'https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&w=1200&q=80'],
                    ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', 'title' => 'Teacher Excellence', 'desc' => 'Qualified subject specialists offering mentoring, guided support, and an engaging teaching approach.', 'image' => 'https://images.unsplash.com/photo-1581078426770-6d336e5de7bf?auto=format&fit=crop&w=1200&q=80'],
                    ['icon' => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'title' => 'Learning Experience', 'desc' => 'Personalised learning journey, interactive online classes, and continuous confidence & skill building.', 'image' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80'],
                    ['icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', 'title' => 'Learning Resources', 'desc' => 'Exclusive learning modules, extensive revision materials and practice, and an expansive digital content library.', 'image' => 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?auto=format&fit=crop&w=1200&q=80'],
                    ['icon' => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'title' => 'Digital Innovation', 'desc' => 'Smart online learning ecosystem with AI-supported learning tools and flexible access anytime, anywhere.', 'image' => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=1200&q=80'],
                    ['icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'title' => 'Parent & Student Success', 'desc' => 'Ongoing progress communication, academic and motivational support, and measurable growth for future readiness.', 'image' => 'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?auto=format&fit=crop&w=1200&q=80'],
                ];
            @endphp

            <div class="flex flex-col lg:flex-row gap-6 lg:gap-10">
                {{-- LEFT SIDE: Navigation Tabs --}}
                <div class="w-full lg:w-[35%] flex flex-col gap-3 py-2 pl-3 pr-2 pb-4 lg:pb-0">
                    @foreach ($whys as $index => $why)
                    <button 
                        data-aos="fade-right" data-aos-delay="{{ $index * 100 }}"
                        @click="resetAutoplay({{ $index }})"
                        class="group relative w-full text-left p-3.5 lg:p-4 rounded-2xl transition-all duration-500 flex items-center gap-4 border outline-none overflow-hidden"
                        :class="activeFeature === {{ $index }} ? 'bg-white border-primary-200 shadow-lg scale-[1.02] z-10' : 'bg-transparent border-slate-200 hover:bg-white hover:border-slate-300 hover:shadow-md'"
                    >
                        {{-- Animated Progress Background for Active Tab --}}
                        <div class="absolute inset-0 bg-primary-50/50 origin-left transition-transform ease-linear"
                             :class="activeFeature === {{ $index }} ? 'scale-x-100 duration-[6000ms]' : 'scale-x-0 duration-0'">
                        </div>

                        <div class="relative z-10 w-12 h-12 rounded-xl flex items-center justify-center shrink-0 transition-all duration-500 shadow-sm"
                             :class="activeFeature === {{ $index }} ? 'bg-gradient-to-br from-amber-400 to-amber-500 text-slate-900 scale-110 shadow-amber-400/40 shadow-lg rotate-3' : 'bg-white text-primary-900 border border-slate-100 group-hover:bg-amber-50 group-hover:text-amber-600'">
                            <svg class="w-6 h-6 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $why['icon'] }}"/>
                            </svg>
                        </div>
                        <div class="relative z-10">
                            <h3 class="font-heading font-extrabold text-[14px] lg:text-[15px] leading-tight transition-colors duration-300"
                                :class="activeFeature === {{ $index }} ? 'text-primary-950' : 'text-slate-600 group-hover:text-primary-900'">
                                {!! $why['title'] !!}
                            </h3>
                        </div>
                        
                        {{-- Subtle watermark number --}}
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-4xl lg:text-5xl font-black italic transition-all duration-500 pointer-events-none"
                             :class="activeFeature === {{ $index }} ? 'text-primary-100 opacity-60' : 'text-slate-100 opacity-80 group-hover:text-primary-50'">
                            0{{ $index + 1 }}
                        </div>
                        
                        {{-- Active Indicator Line --}}
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-gradient-to-b from-amber-400 to-amber-500 transition-all duration-300"
                             :class="activeFeature === {{ $index }} ? 'opacity-100' : 'opacity-0'">
                        </div>
                    </button>
                    @endforeach
                </div>

                {{-- RIGHT SIDE: Spotlight Display --}}
                <div data-aos="fade-left" data-aos-delay="200" class="w-full lg:w-[65%] relative rounded-[2rem] overflow-hidden shadow-2xl min-h-[450px] bg-primary-950 group border border-slate-200/50">
                    @foreach ($whys as $index => $why)
                    <div 
                        x-show="activeFeature === {{ $index }}"
                        x-transition:enter="transition ease-out duration-700"
                        x-transition:enter-start="opacity-0 translate-y-8 lg:translate-y-0 lg:translate-x-8 scale-[0.98]"
                        x-transition:enter-end="opacity-100 translate-y-0 lg:translate-x-0 scale-100"
                        x-transition:leave="transition ease-in duration-500 absolute inset-0"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-105"
                        class="absolute inset-0 w-full h-full"
                    >
                        {{-- Background Image --}}
                        <img src="{{ $why['image'] }}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[12s] ease-out transform scale-100 group-hover:scale-110" alt="{!! strip_tags($why['title']) !!}">
                        
                        {{-- Gradient Overlay --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-primary-950 via-primary-900/40 to-transparent opacity-80 group-hover:opacity-90 transition-opacity duration-500"></div>

                        {{-- Floating Glassmorphism Content Card --}}
                        <div class="absolute bottom-6 left-6 right-6 lg:bottom-10 lg:left-10 lg:right-10 z-10">
                            <div 
                                x-show="activeFeature === {{ $index }}"
                                x-transition:enter="transition ease-out duration-700 delay-300"
                                x-transition:enter-start="opacity-0 translate-y-12"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="relative bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl p-6 lg:p-8 transform transition-all duration-500 hover:-translate-y-2 hover:bg-white/15 hover:shadow-[0_20px_40px_rgba(0,0,0,0.3)] shadow-2xl"
                            >
                                {{-- Edge Numbering Tag --}}
                                <div class="absolute -top-5 -right-5 lg:-top-6 lg:-right-6 w-14 h-14 lg:w-16 lg:h-16 rounded-xl lg:rounded-2xl bg-gradient-to-br from-amber-400 to-amber-500 text-slate-900 border-4 border-white/90 flex items-center justify-center shadow-[0_10px_30px_rgba(251,191,36,0.4)] rotate-6 group-hover:rotate-12 transition-transform duration-500 z-20">
                                    <span class="font-heading font-black text-xl lg:text-2xl">#{{ $index + 1 }}</span>
                                </div>

                                <div class="w-14 h-14 lg:w-16 lg:h-16 rounded-2xl bg-gradient-to-br from-amber-300 to-amber-500 text-slate-900 flex items-center justify-center mb-5 lg:mb-6 shadow-[0_8px_30px_rgba(251,191,36,0.4)] border border-white/40 group-hover:-rotate-6 transition-transform duration-500">
                                    <svg class="w-7 h-7 lg:w-8 lg:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $why['icon'] }}"/>
                                    </svg>
                                </div>
                                <h3 class="font-heading font-black text-2xl lg:text-4xl text-white mb-3 lg:mb-4 leading-tight drop-shadow-md">
                                    {!! $why['title'] !!}
                                </h3>
                                <p class="text-base lg:text-lg text-white/90 leading-relaxed max-w-2xl font-light">
                                    {{ $why['desc'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>



    {{-- ══════════════════════════════════════════════
         SECTION 5: ACHIEVEMENT STATS
    ══════════════════════════════════════════════ --}}
    <section class="pt-16 pb-32 bg-gradient-brand relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div data-aos="fade-up" class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
                @php
                    $stats = [
                        ['target' => 10000, 'suffix' => '+', 'prefix' => '', 'label' => 'Students Enrolled', 'animate' => true],
                        ['target' => 95,    'suffix' => '%', 'prefix' => '', 'label' => 'Improvement Rate', 'animate' => true],
                        ['target' => 8,     'suffix' => '',  'prefix' => '', 'label' => 'Core Subjects', 'animate' => true],
                        ['target' => 2015,  'suffix' => '',  'prefix' => 'Since ', 'label' => 'Proven Track Record', 'animate' => false, 'value' => 'Since 2015'],
                    ];
                @endphp
                @foreach ($stats as $stat)
                <div @if($stat['animate']) 
                        x-data="{ current: 0, target: {{ $stat['target'] }}, time: 2000 }"
                        x-init="
                            let observer = new IntersectionObserver(entries => {
                                if(entries[0].isIntersecting) {
                                    let step = Math.max(1, Math.ceil(target / (time / 30)));
                                    let interval = setInterval(() => {
                                        if (current + step >= target) {
                                            current = target;
                                            clearInterval(interval);
                                        } else {
                                            current += step;
                                        }
                                    }, 30);
                                    observer.disconnect();
                                }
                            });
                            observer.observe($el);
                        "
                     @endif>
                    <div class="font-stats font-bold text-4xl md:text-5xl text-accent mb-2">
                        @if($stat['animate'])
                            {{ $stat['prefix'] }}<span x-text="current.toLocaleString()">0</span>{{ $stat['suffix'] }}
                        @else
                            {{ $stat['value'] }}
                        @endif
                    </div>
                    <div class="text-white/80 text-sm font-medium">{{ $stat['label'] }}</div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- KL Skyline Silhouette --}}
        <svg viewBox="0 0 1000 100" preserveAspectRatio="none" class="absolute bottom-0 left-0 w-full h-24 md:h-32 text-white fill-current z-0 pointer-events-none opacity-[0.12] blur-[1px]">
            <!-- Generic Buildings (Left) -->
            <rect rx="2" x="-5" y="60" width="45" height="45" />
            <rect rx="2" x="35" y="45" width="50" height="55" />
            <rect rx="2" x="75" y="70" width="60" height="30" />
            <rect rx="2" x="120" y="35" width="40" height="65" />
            <rect rx="2" x="150" y="55" width="45" height="45" />
            <rect rx="2" x="185" y="25" width="30" height="75" />
            <rect rx="2" x="205" y="50" width="60" height="50" />
            <rect rx="2" x="250" y="65" width="40" height="35" />
            <rect rx="2" x="280" y="40" width="50" height="60" />
            <rect rx="2" x="320" y="75" width="60" height="25" />
            <rect rx="2" x="360" y="45" width="40" height="55" />
            <rect rx="2" x="390" y="20" width="35" height="80" />
            <rect rx="2" x="415" y="55" width="50" height="45" />
            
            <!-- KL Tower -->
            <rect rx="2" x="480" y="38" width="16" height="62" /> <!-- Base -->
            <rect rx="4" x="475" y="28" width="26" height="12" /> <!-- Bulb -->
            <rect rx="1" x="486" y="10" width="4" height="20" /> <!-- Spire -->
            
            <!-- Generic Buildings (Middle) -->
            <rect rx="2" x="520" y="50" width="45" height="50" />
            <rect rx="2" x="555" y="30" width="40" height="70" />
            <rect rx="2" x="585" y="65" width="50" height="35" />
            <rect rx="2" x="620" y="45" width="55" height="55" />
            <rect rx="2" x="660" y="70" width="40" height="30" />
            
            <!-- Petronas Twin Towers -->
            <!-- Tower Left -->
            <rect rx="1" x="730" y="28" width="16" height="72" />
            <rect rx="1" x="732" y="18" width="12" height="12" />
            <rect rx="1" x="734" y="8" width="8" height="12" />
            <rect rx="1" x="737" y="0" width="2" height="10" />
            <!-- Tower Right -->
            <rect rx="1" x="754" y="28" width="16" height="72" />
            <rect rx="1" x="756" y="18" width="12" height="12" />
            <rect rx="1" x="758" y="8" width="8" height="12" />
            <rect rx="1" x="761" y="0" width="2" height="10" />
            <!-- Bridge -->
            <rect rx="1" x="745" y="50" width="10" height="4" />
            
            <!-- Generic Buildings (Right) -->
            <rect rx="2" x="790" y="55" width="45" height="45" />
            <rect rx="2" x="825" y="35" width="40" height="65" />
            <rect rx="2" x="855" y="60" width="50" height="40" />
            <rect rx="2" x="890" y="40" width="60" height="60" />
            <rect rx="2" x="930" y="70" width="45" height="30" />
            <rect rx="2" x="965" y="45" width="40" height="55" />
        </svg>
    </section>

    <section class="py-12 relative bg-white overflow-hidden">
        {{-- Background decorative elements (Light Theme) --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -left-40 w-[500px] h-[500px] bg-primary-50 rounded-full blur-3xl opacity-60"></div>
            <div class="absolute top-1/2 right-0 w-[600px] h-[600px] bg-accent/5 rounded-full blur-3xl translate-x-1/2 -translate-y-1/2"></div>
            {{-- Subtle grid --}}
            <div class="absolute inset-0 opacity-[0.04]" style="background-image: radial-gradient(rgba(0,0,0,0.15) 1px, transparent 1px); background-size: 24px 24px;"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div data-aos="fade-up" class="text-center mb-8 relative">
                <p class="section-label mb-2 text-primary-600 font-extrabold tracking-wider uppercase text-sm">Success Stories</p>
                <h2 class="section-title font-heading font-black text-4xl md:text-5xl text-slate-900 drop-shadow-sm">What Our Students Say</h2>
                <p class="section-subtitle mt-4 text-slate-600 max-w-2xl mx-auto text-lg">Real results from real students and parents across Malaysia.</p>
            </div>
        </div>

        {{-- Infinite Marquee Container --}}
        <div class="relative w-full overflow-hidden marquee-container py-4 group">
            
            {{-- Fade Edges (White) --}}
            <div class="absolute inset-y-0 right-0 w-16 md:w-32 bg-gradient-to-l from-white to-transparent z-20 pointer-events-none"></div>

            {{-- Row 1: Scrolling Left (Text Testimonials) --}}
            <div class="flex w-max animate-marquee mb-10 mt-6">
                @php
                    $noteStyles = [
                        ['bg' => 'bg-yellow-50', 'border' => 'border-yellow-200', 'rot' => 'rotate-1'],
                        ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'rot' => '-rotate-1'],
                        ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'rot' => 'rotate-2'],
                        ['bg' => 'bg-rose-50', 'border' => 'border-rose-200', 'rot' => '-rotate-2'],
                    ];
                @endphp
                @for ($i = 0; $i < 6; $i++)
                    <div class="flex gap-6 pr-6">
                        @foreach(collect($testimonials) as $index => $t)
                            @php
                                $style = $noteStyles[$index % 4];
                            @endphp
                            <div class="w-[320px] md:w-[380px] shrink-0 {{ $style['bg'] }} border {{ $style['border'] }} rounded-sm p-8 hover:-translate-y-2 hover:rotate-0 hover:shadow-xl transition-all duration-300 shadow-[0_4px_15px_-3px_rgba(0,0,0,0.1)] cursor-default flex flex-col relative {{ $style['rot'] }} mt-2 mb-2">
                                {{-- Tape effect --}}
                                <div class="absolute -top-3 left-1/2 -translate-x-1/2 w-14 h-6 bg-white/70 shadow-sm border border-black/5 -rotate-2 z-20"></div>
                                
                                <div class="flex items-center gap-1 mb-6 relative z-10 mt-1">
                                    @for ($s = 0; $s < 5; $s++)
                                        <svg class="w-5 h-5 {{ $s < floor(data_get($t, 'rating', 5)) ? 'text-amber-400' : 'text-slate-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                </div>
                                <p class="text-charcoal/90 text-sm md:text-base leading-relaxed mb-8 italic flex-grow relative z-10">"{{ data_get($t, 'quote') }}"</p>
                                
                                <div class="flex items-center gap-4 mt-auto relative z-10 pt-4 border-t border-black/5">
                                    <div class="w-10 h-10 rounded-full bg-white/60 flex items-center justify-center text-charcoal font-bold text-sm border border-black/5 shadow-sm">
                                        {{ substr(data_get($t, 'name', 'A'), 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-heading font-bold text-charcoal text-sm">{{ data_get($t, 'name') }}</p>
                                        <p class="text-slate-500 text-xs">{{ data_get($t, 'role') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endfor
            </div>

            @php
                $successImages = [
                    'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=600&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1523240795612-9a054b0db644?q=80&w=600&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1509062522246-3755977927d7?q=80&w=600&auto=format&fit=crop',
                    'https://plus.unsplash.com/premium_photo-1714348661832-a0d4960c601e?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                    'https://plus.unsplash.com/premium_photo-1683887034146-c79058dbdcb1?q=80&w=1169&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                    'https://images.unsplash.com/photo-1498243691581-b145c3f54a5a?q=80&w=600&auto=format&fit=crop'
                ];
            @endphp

            {{-- Row 2: Scrolling Right (Images) --}}
            <div class="flex w-max animate-marquee-reverse">
                @for ($i = 0; $i < 6; $i++)
                    <div class="flex gap-6 pr-6">
                        @foreach($successImages as $img)
                            <div class="w-[280px] md:w-[350px] h-[200px] md:h-[240px] shrink-0 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 cursor-default group/image relative">
                                <img src="{{ $img }}" class="w-full h-full object-cover transition-transform duration-700 group-hover/image:scale-110" alt="Student Success">
                                <div class="absolute inset-0 bg-gradient-to-t from-primary-950/60 via-transparent to-transparent opacity-0 group-hover/image:opacity-100 transition-opacity duration-300"></div>
                            </div>
                        @endforeach
                    </div>
                @endfor
            </div>

        </div>

        <div class="text-center mt-16 relative z-10 flex flex-col sm:flex-row items-center justify-center gap-4 sm:gap-6">
            <a href="{{ route('success-stories') }}" class="btn-primary px-8 py-4 shadow-lg hover:shadow-xl hover:shadow-primary/30 transition-all text-sm md:text-base font-bold tracking-wide">
                Share Your Success Story
            </a>
            <a href="{{ route('success-stories') }}" class="btn-outline-accent px-8 py-4 shadow-md hover:shadow-lg transition-all text-sm md:text-base font-bold tracking-wide group flex items-center justify-center">
                Read all reviews
                <svg class="w-5 h-5 ml-2 transform transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
        </div>
    </section>



    {{-- ══════════════════════════════════════════════
         SECTION 8: LATEST ARTICLES
    ══════════════════════════════════════════════ --}}
    @if ($posts->count())
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14">
                <p class="section-label mb-2">From Our Blog</p>
                <h2 class="section-title">Latest Articles</h2>
                <p class="section-subtitle mt-4">Study tips, exam strategies, and education insights for Malaysian students.</p>
            </div>

            <div data-aos="fade-up" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ($posts as $post)
                <a href="{{ route('blog') }}" class="card group hover:-translate-y-1 cursor-pointer block">
                    <div class="w-full h-40 bg-gradient-brand rounded-2xl mb-4 flex items-center justify-center">
                        <svg class="w-12 h-12 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    @if ($post->category)
                    <span class="badge badge-blue mb-2">{{ $post->category }}</span>
                    @endif
                    <h3 class="font-heading font-bold text-charcoal text-base leading-snug mb-2 group-hover:text-primary transition-colors">{{ $post->title }}</h3>
                    <p class="text-muted text-sm leading-relaxed line-clamp-2">{{ $post->excerpt }}</p>
                    <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-xs text-muted">{{ $post->published_at?->format('d M Y') }}</span>
                        <span class="text-xs font-semibold text-primary group-hover:underline">Read More →</span>
                    </div>
                </a>
                @endforeach
            </div>

            <div class="text-center mt-10">
                <a href="{{ route('blog') }}" class="btn-secondary">View All Articles</a>
            </div>
        </div>
    </section>
    @endif



</x-app-layout>
