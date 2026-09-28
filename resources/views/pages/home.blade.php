<x-app-layout>
    <x-slot name="title">Home</x-slot>

    {{-- ══════════════════════════════════════════════
         SECTION 1: HERO
    ══════════════════════════════════════════════ --}}
    <section class="relative bg-gradient-to-br from-primary-950 via-primary-900 to-navy text-white overflow-hidden min-h-[90vh] flex items-center">
        {{-- Background decorative elements --}}
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 w-96 h-96 bg-white/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-accent/10 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-white/3 rounded-full blur-3xl"></div>
            {{-- Subtle grid pattern --}}
            <div class="absolute inset-0 opacity-5" style="background-image: url('data:image/svg+xml,<svg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"><g fill=\"none\" fill-rule=\"evenodd\"><g fill=\"%23ffffff\" fill-opacity=\"1\"><rect x=\"0\" y=\"0\" width=\"1\" height=\"60\"/><rect x=\"0\" y=\"0\" width=\"60\" height=\"1\"/></g></g></svg>');"></div>
        </div>

        {{-- Background picture strictly on the right side with left edge mask & mobile contrast overlay --}}
        <div class="absolute top-0 right-0 w-full lg:w-[55%] h-full overflow-hidden pointer-events-none z-0" style="-webkit-mask-image: linear-gradient(to right, transparent 0%, black 35%, black 100%); mask-image: linear-gradient(to right, transparent 0%, black 35%, black 100%);">
            <img src="{{ asset('images/hero-students.jpg') }}" alt="Olympian Scholars Students" class="w-full h-full object-cover object-center filter brightness-95 lg:brightness-105 contrast-105">
            <div class="absolute inset-0 bg-gradient-to-r from-primary-950 via-primary-950/90 to-primary-950/75 lg:bg-gradient-to-t lg:from-primary-950/80 lg:via-transparent lg:to-primary-950/30"></div>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                {{-- Left: Copy --}}
                <div>
                    

                    <h1 class="font-heading font-bold text-4xl sm:text-5xl lg:text-6xl text-white leading-tight mb-6">
                        Empowering Every<br>
                        <span class="text-accent">Learner to Learn,</span><br>
                        Excel & Achieve.
                    </h1>

                    <p class="text-white/85 text-lg leading-relaxed mb-8 max-w-xl">
                        Learn from subject specialist educators through personalised digital learning, premium learning resources, and examination focused programmes designed to unlock every student's full potential.
                    </p>

                    <div class="flex flex-wrap gap-4 mb-10">
                        <a href="{{ route('free-trial') }}" class="btn-accent text-base px-8 py-4 shadow-lg shadow-accent/20 hover:scale-105 transition-all">
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

        <div class="relative w-full overflow-hidden py-4">
            {{-- Gradient Fades on Edges for Seamless Blend --}}
            <div class="absolute left-0 top-0 bottom-0 w-20 bg-gradient-to-r from-surface to-transparent z-20 pointer-events-none"></div>
            <div class="absolute right-0 top-0 bottom-0 w-20 bg-gradient-to-l from-surface to-transparent z-20 pointer-events-none"></div>

            @php
                $defaultIcon = 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253';
                $subjectIcons = [
                    'Phonics & Reading'           => 'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 210.3H3v-3.572L16.732 3.732z',
                    'Early Mathematics & Counting'=> 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z',
                    'Early Science Exploration'   => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
                    'Bahasa Melayu Awal'          => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                    'Arts & Creativity'           => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z',

                    'Bahasa Melayu (Primary)'     => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13',
                    'English (Primary)'           => 'M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10',
                    'Mathematics (Primary)'       => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01',
                    'Science (Primary)'           => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158',
                    'Sejarah (Primary)'           => 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479',

                    'Mathematics'                 => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z',
                    'Additional Mathematics'       => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4',
                    'Science'                      => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
                    'Physics'                      => 'M13 10V3L4 14h7v7l9-11h-7z',
                    'Chemistry'                    => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z',
                    'Biology'                      => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
                    'Bahasa Melayu'                => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                    'English'                      => 'M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129',
                    'Prinsip Perakaunan'          => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01',
                    'Perniagaan'                  => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
                    'Ekonomi'                     => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
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
                    $icon = $subjectIcons[$subject->name] ?? $defaultIcon;
                    $fallbackIdx = ($subject->id ?? 0) % count($fallbackImages);
                    $bgImg = $subjectImages[$subject->name] ?? $fallbackImages[$fallbackIdx];
                    $overlayClass = match($subject->level) {
                        'Kindergarten'    => 'from-amber-950/85 via-amber-900/50 to-slate-900/30',
                        'Primary'         => 'from-emerald-950/85 via-emerald-900/50 to-slate-900/30',
                        'Lower Secondary' => 'from-blue-950/85 via-blue-900/50 to-slate-900/30',
                        default           => 'from-purple-950/85 via-purple-900/50 to-slate-900/30',
                    };
                @endphp
                <div class="flex-none w-[170px] sm:w-[190px]">
                    <a href="{{ route('subject.show', $subject) }}" @click.prevent="openSubjectModal('{{ $subject->name }}')" class="group relative overflow-hidden rounded-2xl p-3 text-center flex flex-col items-center justify-between h-[130px] sm:h-[140px] shadow-md hover:shadow-2xl border border-white/20 hover:-translate-y-1 transition-all duration-300 block cursor-pointer">
                        {{-- Background Picture --}}
                        <img src="{{ $bgImg }}" alt="{{ $subject->name }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        {{-- Original Gradient Overlay (Dark bottom for text clarity, transparent top for photo) --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/95 via-slate-950/60 to-slate-950/20 group-hover:opacity-80 transition-opacity duration-300"></div>

                        {{-- Icon --}}
                        <div class="relative z-10 w-9 h-9 bg-black/40 backdrop-blur-md border border-white/30 text-white rounded-xl flex items-center justify-center mb-1 group-hover:scale-110 group-hover:bg-amber-400 group-hover:text-slate-950 transition-all duration-300 shadow-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/></svg>
                        </div>

                        {{-- Subject Name --}}
                        <h3 class="relative z-10 font-heading font-black text-white text-xs sm:text-sm group-hover:text-amber-300 transition-colors tracking-tight line-clamp-1 drop-shadow-[0_2px_4px_rgba(0,0,0,0.9)]">
                            {{ $subject->name }}
                        </h3>

                        {{-- Bottom Badge --}}
                        <div class="relative z-10 mt-auto pt-1">
                            <span class="inline-block bg-black/60 backdrop-blur-md border border-white/30 text-white text-[9px] sm:text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wider shadow-md">
                                {{ $subject->level }}
                            </span>
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
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14">
                <p class="section-label mb-2">Why Choose Us</p>
                <h2 class="section-title">Why Olympian Scholars?</h2>
                <p class="section-subtitle mt-4">Empowering every learner through subject specialist educators, premium resources, and personalised learning.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @php
                    $whys = [
                        ['icon' => 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222', 'title' => 'Subject Specialist<br>Educators', 'desc' => 'Learn from highly qualified subject specialists committed to academic excellence and student success.'],
                        ['icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', 'title' => 'Premium Learning<br>Resources', 'desc' => 'Unlock exclusive digital resources designed to simplify learning and strengthen examination readiness.'],
                        ['icon' => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'title' => 'Personalised Digital<br>Learning', 'desc' => 'Experience flexible, engaging, and technology-enabled learning tailored to every student\'s needs.'],
                        ['icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6', 'title' => 'Personalised Learning<br>Journey', 'desc' => 'Follow a structured learning pathway with continuous progress monitoring and personalised academic support.'],
                        ['icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'title' => 'Examination<br>Excellence', 'desc' => 'Build confidence through strategic exam preparation, targeted practice, and performance-driven learning.'],
                        ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', 'title' => 'Parent<br>Partnership', 'desc' => 'Stay connected with your child\'s learning through transparent communication and real-time academic updates.'],
                        ['icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', 'title' => 'Future-Ready<br>Skills', 'desc' => 'Empower students with essential skills for lifelong learning, leadership, and future success.'],
                        ['icon' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z', 'title' => 'Proven<br>Results', 'desc' => 'Delivering measurable academic growth through quality education and continuous learner development.'],
                    ];
                @endphp
                @foreach ($whys as $why)
                <div class="card border border-gray-100 transition-all duration-300 hover:-translate-y-1 group text-center">
                    <div class="w-14 h-14 bg-primary-50 text-primary rounded-2xl flex items-center justify-center mx-auto mb-5 group-hover:bg-primary group-hover:text-white group-hover:scale-110 transition-all duration-300 shadow-2xs">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $why['icon'] }}"/>
                        </svg>
                    </div>
                    <h3 class="font-heading font-bold text-charcoal text-base mb-2 leading-snug group-hover:text-primary transition-colors">{!! $why['title'] !!}</h3>
                    <p class="text-muted text-sm leading-relaxed">{{ $why['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>



    {{-- ══════════════════════════════════════════════
         SECTION 5: ACHIEVEMENT STATS
    ══════════════════════════════════════════════ --}}
    <section class="py-16 bg-gradient-brand">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
                @php
                    $stats = [
                        ['value' => '10,000+', 'label' => 'Students Enrolled'],
                        ['value' => '95%',     'label' => 'Improvement Rate'],
                        ['value' => '8',       'label' => 'Core Subjects'],
                        ['value' => 'Since 2015', 'label' => 'Proven Track Record'],
                    ];
                @endphp
                @foreach ($stats as $stat)
                <div>
                    <div class="font-stats font-bold text-4xl md:text-5xl text-accent mb-2">{{ $stat['value'] }}</div>
                    <div class="text-white/80 text-sm font-medium">{{ $stat['label'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════
         SECTION 6: TESTIMONIALS
    ══════════════════════════════════════════════ --}}
    <section class="py-20 bg-surface">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14">
                <p class="section-label mb-2">Success Stories</p>
                <h2 class="section-title">What Our Students Say</h2>
                <p class="section-subtitle mt-4">Real results from real students and parents across Malaysia.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse ($testimonials as $t)
                <div class="card relative overflow-hidden">
                    {{-- Quote mark --}}
                    <div class="absolute top-4 right-5 text-8xl text-primary-50 font-serif leading-none select-none">"</div>
                    <div class="flex items-center gap-1 mb-4">
                        @for ($s = 0; $s < 5; $s++)
                            <svg class="w-4 h-4 {{ $s < floor($t->rating) ? 'text-accent' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
                    </div>
                    <p class="text-charcoal text-sm leading-relaxed mb-4 relative z-10 italic">"{{ $t->quote }}"</p>
                    <div class="flex items-center gap-3 mt-4 pt-4 border-t border-gray-100">
                        <div class="w-10 h-10 bg-gradient-brand rounded-full flex items-center justify-center text-white font-bold text-sm">
                            {{ substr($t->name, 0, 1) }}
                        </div>
                        <div>
                            <p class="font-heading font-bold text-charcoal text-sm">{{ $t->name }}</p>
                            <p class="text-muted text-xs">{{ $t->role }}</p>
                        </div>
                    </div>
                </div>
                @empty
                {{-- Fallback Card 1 --}}
                <div class="card relative overflow-hidden">
                    <div class="absolute top-4 right-5 text-8xl text-primary-50 font-serif leading-none select-none">"</div>
                    <div class="flex items-center gap-1 mb-4 text-amber-400">
                        ★★★★★
                    </div>
                    <p class="text-charcoal text-sm leading-relaxed mb-4 relative z-10 italic">"I went from a C to an A+ in Mathematics after just 3 months with Olympian Scholars. The teachers are patient, thorough, and truly care about your progress."</p>
                    <div class="flex items-center gap-3 mt-4 pt-4 border-t border-gray-100">
                        <div class="w-10 h-10 bg-gradient-brand rounded-full flex items-center justify-center text-white font-bold text-sm">M</div>
                        <div>
                            <p class="font-heading font-bold text-charcoal text-sm">Muhammad Hariz</p>
                            <p class="text-muted text-xs">SPM Student</p>
                        </div>
                    </div>
                </div>
                {{-- Fallback Card 2 --}}
                <div class="card relative overflow-hidden">
                    <div class="absolute top-4 right-5 text-8xl text-primary-50 font-serif leading-none select-none">"</div>
                    <div class="flex items-center gap-1 mb-4 text-amber-400">
                        ★★★★★
                    </div>
                    <p class="text-charcoal text-sm leading-relaxed mb-4 relative z-10 italic">"As a parent, I can see the real improvement in my daughter's confidence and grades. The portal makes it easy for me to track her progress weekly."</p>
                    <div class="flex items-center gap-3 mt-4 pt-4 border-t border-gray-100">
                        <div class="w-10 h-10 bg-gradient-brand rounded-full flex items-center justify-center text-white font-bold text-sm">P</div>
                        <div>
                            <p class="font-heading font-bold text-charcoal text-sm">Puan Rosmah</p>
                            <p class="text-muted text-xs">Parent</p>
                        </div>
                    </div>
                </div>
                {{-- Fallback Card 3 --}}
                <div class="card relative overflow-hidden">
                    <div class="absolute top-4 right-5 text-8xl text-primary-50 font-serif leading-none select-none">"</div>
                    <div class="flex items-center gap-1 mb-4 text-amber-400">
                        ★★★★★
                    </div>
                    <p class="text-charcoal text-sm leading-relaxed mb-4 relative z-10 italic">"The interactive lessons and live Q&A sessions made learning Chemistry so much less intimidating. I'm now consistently scoring above 90% in my practice papers."</p>
                    <div class="flex items-center gap-3 mt-4 pt-4 border-t border-gray-100">
                        <div class="w-10 h-10 bg-gradient-brand rounded-full flex items-center justify-center text-white font-bold text-sm">P</div>
                        <div>
                            <p class="font-heading font-bold text-charcoal text-sm">Priya Nair</p>
                            <p class="text-muted text-xs">IGCSE Student</p>
                        </div>
                    </div>
                </div>
                @endforelse
            </div>

            <div class="text-center mt-10">
                <a href="{{ route('success-stories') }}" class="btn-secondary">Read More Stories</a>
            </div>
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

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ($posts as $post)
                <a href="{{ route('blog.show', $post->slug) }}" class="card group hover:-translate-y-1 cursor-pointer block">
                    @if ($post->thumbnail && file_exists(public_path($post->thumbnail)))
                        <img src="{{ asset($post->thumbnail) }}" alt="{{ $post->title }}" class="w-full h-40 object-cover rounded-2xl mb-4 border border-gray-100">
                    @else
                        <div class="w-full h-40 bg-gradient-brand rounded-2xl mb-4 flex items-center justify-center">
                            <svg class="w-12 h-12 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    @endif
                    @if ($post->category)
                    <span class="badge badge-blue mb-2">{{ $post->category }}</span>
                    @endif
                    <h3 class="font-heading font-bold text-charcoal text-base leading-snug mb-2 group-hover:text-primary transition-colors">{{ $post->title }}</h3>
                    <p class="text-muted text-sm leading-relaxed line-clamp-2">{{ $post->excerpt }}</p>
                    <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-xs text-muted">{{ $post->published_at?->format('d M Y') }}</span>
                        <span class="text-xs font-semibold text-primary group-hover:underline">Read More &rarr;</span>
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
