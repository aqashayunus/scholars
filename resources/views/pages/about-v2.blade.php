<x-app-layout>
    <x-slot name="title">About Us</x-slot>

    {{-- ══════════════════════════════════════════════
         SECTION 1: HERO HEADER (Synchronized with Programmes & Teachers)
    ══════════════════════════════════════════════ --}}
    <section class="relative bg-primary-950 text-white py-12 md:py-16 text-center overflow-hidden">
        <!-- Background Image & Overlay -->
        <div class="absolute inset-0 z-0">
            <img src="{{ asset('images/hero-students.jpg') }}" alt="Olympia Scholar About Us" class="w-full h-full object-cover" />
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
            <h1 class="font-heading font-black text-5xl md:text-6xl lg:text-7xl text-white drop-shadow-lg leading-tight">
                About <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-amber-200">Us</span>
            </h1>
        </div>

        {{-- Smooth Curved Divider --}}
        <div class="absolute -bottom-1 left-0 right-0 overflow-hidden leading-none pointer-events-none z-10">
            <svg viewBox="0 0 1440 80" fill="none" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-10 md:h-14 block">
                <path d="M0,25 Q720,75 1440,25 L1440,80 L0,80 Z" fill="#f8fafc"></path>
            </svg>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════
         SECTION 2: OUR STORY (ACCORDION GRID)
    ══════════════════════════════════════════════ --}}
    <section class="py-24 bg-slate-50 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <!-- Header -->
            <div data-aos="fade-up" class="text-center mb-16 relative">
                <p class="section-label mb-2 text-primary-600 font-extrabold tracking-wider uppercase text-sm">Our Origin</p>
                <h2 class="section-title font-heading font-black text-4xl md:text-5xl text-slate-900 drop-shadow-sm">The Olympian Story</h2>
                <p class="section-subtitle mt-4 text-slate-600 max-w-2xl mx-auto text-lg">A simple belief that every student has the potential to achieve academic excellence.</p>
            </div>

            @php
                $stories = [
                    ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', 'title' => 'Every student has the potential to succeed.', 'desc' => 'Every student learns differently. Some require additional guidance, others need greater confidence, while many simply need the right opportunity to unlock their abilities.'],
                    ['icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 01-2 2h-4a2 2 0 01-2-2v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z', 'title' => 'We believe education is more than preparing students for examinations.', 'desc' => 'It should inspire curiosity, build confidence, strengthen understanding, and empower students for the real world.'],
                    ['icon' => 'M12 2a10 10 0 100 20 10 10 0 000-20zm0 18a8 8 0 110-16 8 8 0 010 16zm0-13a5 5 0 100 10 5 5 0 000-10zm0 8a3 3 0 110-6 3 3 0 010 6z', 'title' => 'Olympian Scholars was founded on a simple belief', 'desc' => 'That every student has the potential to achieve academic excellence when given the right guidance and continuous support.'],
                    ['icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'title' => 'Through experienced subject specialist educators...', 'desc' => 'We create an environment where students are encouraged to learn, grow, and excel every day.'],
                    ['icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z', 'title' => 'Driven by this belief, Olympian Scholars was established', 'desc' => 'To provide a premium tuition experience that combines innovative digital learning, and premium resources.'],
                    ['icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6', 'title' => 'Today, we continue our commitment to helping students build strong academic foundations', 'desc' => 'While preparing them for future success beyond the classroom.']
                ];
            @endphp

            <div class="flex flex-col lg:flex-row gap-4 sm:gap-6 max-w-6xl mx-auto items-start" x-data="{ activeAccordion: null }">
                
                {{-- Left Column --}}
                <div class="flex flex-col gap-4 sm:gap-6 w-full lg:w-1/2">
                    @foreach ($stories as $index => $story)
                    @if($index % 2 == 0)
                    <div class="bg-white rounded-3xl p-2 sm:p-3 shadow-sm border border-slate-100 hover:shadow-md transition-all duration-300 relative overflow-hidden" data-aos="fade-up" data-aos-delay="{{ $index * 50 }}">
                        <button @click="activeAccordion = activeAccordion === {{ $index }} ? null : {{ $index }}" class="w-full flex items-center justify-between text-left focus:outline-none group p-2">
                            <div class="flex items-center gap-4 sm:gap-6">
                                <div class="w-14 h-14 sm:w-16 sm:h-16 shrink-0 rounded-2xl bg-[#284387] text-white flex items-center justify-center shadow-md group-hover:scale-105 transition-transform duration-300">
                                    <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $story['icon'] }}"/></svg>
                                </div>
                                <h3 class="font-heading font-bold text-[#1e293b] text-base sm:text-lg leading-snug group-hover:text-[#284387] transition-colors pr-2">
                                    {{ $story['title'] }}
                                </h3>
                            </div>
                            <div class="w-10 h-10 shrink-0 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-slate-100 group-hover:text-slate-600 transition-colors duration-300 ml-2">
                                <svg class="w-5 h-5 transform transition-transform duration-300" :class="{ 'rotate-180': activeAccordion === {{ $index }} }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </button>
                        <div x-show="activeAccordion === {{ $index }}" 
                             x-transition:enter="transition ease-out duration-200" 
                             x-transition:enter-start="opacity-0 -translate-y-2" 
                             x-transition:enter-end="opacity-100 translate-y-0" 
                             x-transition:leave="transition ease-in duration-150" 
                             x-transition:leave-start="opacity-100 translate-y-0" 
                             x-transition:leave-end="opacity-0 -translate-y-2" 
                             style="display: none;">
                            <div class="px-6 sm:px-8 pb-6 pt-5 text-slate-600 text-sm sm:text-base leading-relaxed font-light border-t border-slate-100 mt-4 mx-2 text-left">
                                {{ $story['desc'] }}
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>

                {{-- Right Column --}}
                <div class="flex flex-col gap-4 sm:gap-6 w-full lg:w-1/2">
                    @foreach ($stories as $index => $story)
                    @if($index % 2 == 1)
                    <div class="bg-white rounded-3xl p-2 sm:p-3 shadow-sm border border-slate-100 hover:shadow-md transition-all duration-300 relative overflow-hidden" data-aos="fade-up" data-aos-delay="{{ $index * 50 }}">
                        <button @click="activeAccordion = activeAccordion === {{ $index }} ? null : {{ $index }}" class="w-full flex items-center justify-between text-left focus:outline-none group p-2">
                            <div class="flex items-center gap-4 sm:gap-6">
                                <div class="w-14 h-14 sm:w-16 sm:h-16 shrink-0 rounded-2xl bg-[#284387] text-white flex items-center justify-center shadow-md group-hover:scale-105 transition-transform duration-300">
                                    <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $story['icon'] }}"/></svg>
                                </div>
                                <h3 class="font-heading font-bold text-[#1e293b] text-base sm:text-lg leading-snug group-hover:text-[#284387] transition-colors pr-2">
                                    {{ $story['title'] }}
                                </h3>
                            </div>
                            <div class="w-10 h-10 shrink-0 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-slate-100 group-hover:text-slate-600 transition-colors duration-300 ml-2">
                                <svg class="w-5 h-5 transform transition-transform duration-300" :class="{ 'rotate-180': activeAccordion === {{ $index }} }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </button>
                        <div x-show="activeAccordion === {{ $index }}" 
                             x-transition:enter="transition ease-out duration-200" 
                             x-transition:enter-start="opacity-0 -translate-y-2" 
                             x-transition:enter-end="opacity-100 translate-y-0" 
                             x-transition:leave="transition ease-in duration-150" 
                             x-transition:leave-start="opacity-100 translate-y-0" 
                             x-transition:leave-end="opacity-0 -translate-y-2" 
                             style="display: none;">
                            <div class="px-6 sm:px-8 pb-6 pt-5 text-slate-600 text-sm sm:text-base leading-relaxed font-light border-t border-slate-100 mt-4 mx-2 text-left">
                                {{ $story['desc'] }}
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    

    {{-- ══════════════════════════════════════════════
         SECTION 4: PURPOSE, PASSION, PROMISE
    ══════════════════════════════════════════════ --}}
    <section class="pt-24 pb-12 md:pb-16 bg-white relative">
        {{-- Subtle Background Elements --}}
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <div class="absolute -top-40 -right-40 w-[500px] h-[500px] bg-slate-50 rounded-full blur-3xl opacity-80"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center mb-16" data-aos="fade-up">
                <div class="flex items-center justify-center gap-4 mb-4">
                    <p class="section-label text-primary-600 font-extrabold tracking-wider uppercase text-sm m-0">Our Core Values</p>
                </div>
                <h2 class="section-title font-heading font-black text-4xl md:text-5xl text-slate-900 drop-shadow-sm">Purpose. Passion. Promise.</h2>
            </div>

            <div class="grid lg:grid-cols-3 gap-8 md:gap-10">
                {{-- Card 1: OUR PURPOSE --}}
                <div class="group relative rounded-[2.5rem] overflow-hidden shadow-[0_10px_40px_-15px_rgba(0,0,0,0.15)] hover:shadow-2xl transition-all duration-500 min-h-[480px] flex flex-col justify-end" data-aos="fade-up" data-aos-delay="0">
                    <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=800&q=80" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-[10s] ease-out" alt="Our Purpose">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/95 via-slate-900/60 to-transparent"></div>
                    
                    {{-- Floating Number --}}
                    <div class="absolute top-8 right-8 text-6xl font-black italic text-white/10 group-hover:text-white/20 transition-colors duration-500 pointer-events-none">
                        01
                    </div>

                    <div class="relative z-10 p-8 md:p-10 transform transition-transform duration-500">
                        <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-amber-400 mb-6 group-hover:-translate-y-2 transition-transform duration-300 shadow-lg">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15a3 3 0 100-6 3 3 0 000 6z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 0011 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 11a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
                        </div>
                        
                        <h3 class="font-heading font-black text-3xl text-white mb-4 tracking-tight group-hover:text-amber-300 transition-colors">Our Purpose</h3>
                        <div class="w-12 h-1 bg-amber-400 mb-5 rounded-full group-hover:w-24 transition-all duration-500 ease-out"></div>
                        <p class="text-white/85 text-lg leading-relaxed font-light">
                            To make quality education accessible, engaging, and meaningful for every student.
                        </p>
                    </div>
                </div>

                {{-- Card 2: OUR PASSION --}}
                <div class="group relative rounded-[2.5rem] overflow-hidden shadow-[0_10px_40px_-15px_rgba(0,0,0,0.15)] hover:shadow-2xl transition-all duration-500 min-h-[480px] flex flex-col justify-end" data-aos="fade-up" data-aos-delay="100">
                    <img src="https://images.unsplash.com/photo-1511629091441-ee46146481b6?auto=format&fit=crop&w=800&q=80" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-[10s] ease-out" alt="Our Passion">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/95 via-slate-900/60 to-transparent"></div>
                    
                    {{-- Floating Number --}}
                    <div class="absolute top-8 right-8 text-6xl font-black italic text-white/10 group-hover:text-white/20 transition-colors duration-500 pointer-events-none">
                        02
                    </div>

                    <div class="relative z-10 p-8 md:p-10 transform transition-transform duration-500">
                        <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-amber-400 mb-6 group-hover:-translate-y-2 transition-transform duration-300 shadow-lg">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        </div>
                        
                        <h3 class="font-heading font-black text-3xl text-white mb-4 tracking-tight group-hover:text-amber-300 transition-colors">Our Passion</h3>
                        <div class="w-12 h-1 bg-amber-400 mb-5 rounded-full group-hover:w-24 transition-all duration-500 ease-out"></div>
                        <p class="text-white/85 text-lg leading-relaxed font-light">
                            To inspire every student to learn with confidence and achieve academic excellence.
                        </p>
                    </div>
                </div>

                {{-- Card 3: OUR PROMISE --}}
                <div class="group relative rounded-[2.5rem] overflow-hidden shadow-[0_10px_40px_-15px_rgba(0,0,0,0.15)] hover:shadow-2xl transition-all duration-500 min-h-[480px] flex flex-col justify-end" data-aos="fade-up" data-aos-delay="200">
                    <img src="https://images.unsplash.com/photo-1434030216411-0b793f4b4173?auto=format&fit=crop&w=800&q=80" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-[10s] ease-out" alt="Our Promise">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/95 via-slate-900/60 to-transparent"></div>
                    
                    {{-- Floating Number --}}
                    <div class="absolute top-8 right-8 text-6xl font-black italic text-white/10 group-hover:text-white/20 transition-colors duration-500 pointer-events-none">
                        03
                    </div>

                    <div class="relative z-10 p-8 md:p-10 transform transition-transform duration-500">
                        <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-amber-400 mb-6 group-hover:-translate-y-2 transition-transform duration-300 shadow-lg">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        
                        <h3 class="font-heading font-black text-3xl text-white mb-4 tracking-tight group-hover:text-amber-300 transition-colors">Our Promise</h3>
                        <div class="w-12 h-1 bg-amber-400 mb-5 rounded-full group-hover:w-24 transition-all duration-500 ease-out"></div>
                        <p class="text-white/85 text-lg leading-relaxed font-light">
                            To provide exceptional tuition through expert educators, personalised support, and continuous academic guidance.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════
         SECTION 3: OUR JOURNEY TIMELINE
    ══════════════════════════════════════════════ --}}
    <section class="pt-12 md:pt-16 pb-24 md:pb-32 bg-white relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center mb-24" data-aos="fade-up">
                <div class="flex items-center justify-center gap-4 mb-4">
                    <p class="section-label text-primary-600 font-extrabold tracking-wider uppercase text-sm m-0">Our History</p>
                </div>
                <h2 class="section-title font-heading font-black text-4xl md:text-5xl lg:text-6xl text-slate-900 drop-shadow-sm mb-6">Our Journey</h2>
                <p class="section-subtitle text-slate-600 max-w-2xl mx-auto text-lg md:text-xl font-light">A timeline of our commitment to educational excellence and continuous growth.</p>
            </div>

            @php
                $steps = [
                    [
                        'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', 
                        'title' => 'A Vision is Born', 
                        'desc' => 'A simple belief that every student can achieve excellence with the right support.',
                        'image' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1200&q=80'
                    ],
                    [
                        'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 
                        'title' => 'Olympian Scholars Established', 
                        'desc' => 'Committed to providing high-quality, engaging, and personalised tuition for every student.',
                        'image' => 'https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&w=1200&q=80'
                    ],
                    [
                        'icon' => 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222', 
                        'title' => 'Building Excellence', 
                        'desc' => 'Carefully selected subject specialist educators and structured premium learning resources.',
                        'image' => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=1200&q=80'
                    ],
                    [
                        'icon' => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 
                        'title' => 'Innovating Learning', 
                        'desc' => 'Embracing technology to create effective and interactive digital learning experiences.',
                        'image' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80'
                    ],
                    [
                        'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', 
                        'title' => 'Growing Together', 
                        'desc' => 'Supporting more students and families across Malaysia in their academic journeys.',
                        'image' => 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=1200&q=80'
                    ],
                    [
                        'icon' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z', 
                        'title' => 'Empowering Success', 
                        'desc' => 'Continuing our mission to build confident learners and brighter futures.',
                        'image' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1200&q=80'
                    ],
                ];
            @endphp

            <div class="flex flex-col lg:flex-row items-center justify-center gap-2 lg:gap-3 xl:gap-4 w-full">
                @foreach ($steps as $index => $step)
                {{-- Card --}}
                <div class="bg-white rounded-[2rem] p-4 lg:p-5 xl:p-6 flex flex-col items-center justify-center text-center shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 hover:shadow-[0_20px_40px_rgb(0,0,0,0.08)] hover:-translate-y-2 transition-all duration-500 w-full lg:w-40 xl:w-44 shrink-0 min-h-[200px] group" data-aos="fade-up" data-aos-delay="{{ $index * 50 }}">
                    <div class="w-14 h-14 xl:w-16 xl:h-16 rounded-[1.25rem] bg-[#284387] text-white flex items-center justify-center shadow-[0_8px_20px_rgba(40,67,135,0.25)] group-hover:scale-110 group-hover:shadow-[0_12px_25px_rgba(40,67,135,0.35)] transition-all duration-300 mb-5 relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-tr from-white/0 via-white/10 to-white/0 group-hover:translate-x-full duration-700 transition-transform -translate-x-full"></div>
                        <svg class="w-6 h-6 xl:w-7 xl:h-7 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $step['icon'] }}"/></svg>
                    </div>
                    <h3 class="font-heading font-bold text-slate-800 text-[14px] xl:text-[16px] leading-snug group-hover:text-[#284387] transition-colors">{{ $step['title'] }}</h3>
                </div>

                @if($index < count($steps) - 1)
                    {{-- Desktop Arrow --}}
                    <div class="hidden lg:flex items-center justify-center shrink-0" data-aos="fade-in" data-aos-delay="{{ $index * 50 + 25 }}">
                        <div class="w-8 h-8 rounded-full bg-[#284387] text-white flex items-center justify-center shadow-md">
                            <svg class="w-4 h-4 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </div>
                    {{-- Mobile Arrow --}}
                    <div class="lg:hidden flex items-center justify-center py-2 shrink-0">
                        <div class="w-8 h-8 rounded-full bg-[#284387] text-white flex items-center justify-center shadow-md">
                            <svg class="w-4 h-4 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                @endif
                @endforeach
            </div>

            {{-- Quote Badge Banner --}}
            <div class="mt-20 lg:mt-28 text-center relative z-20" data-aos="zoom-in" data-aos-delay="200">
                <div class="inline-flex items-center justify-center bg-gradient-to-b from-[#ffd739] to-[#f9b514] text-slate-900 rounded-full px-10 sm:px-16 py-6 shadow-[0_15px_40px_rgba(249,181,20,0.35)] border border-[#f4ac05]/50 hover:-translate-y-1 hover:shadow-[0_20px_50px_rgba(249,181,20,0.45)] transition-all duration-500 text-center relative overflow-hidden group">
                    <div class="absolute inset-0 bg-white/30 transform -skew-x-12 -translate-x-full group-hover:translate-x-full duration-700 ease-out transition-transform z-0"></div>
                    <div class="text-[#1e293b] text-lg sm:text-xl md:text-[22px] font-bold tracking-tight leading-relaxed text-center relative z-10">
                        <div class="mb-1">At Olympian Scholars,</div>
                        <div class="relative inline-block mt-1">
                            <span class="relative z-10">every student's success is our greatest achievement.</span>
                            {{-- Highlight Pen Effect --}}
                            <span class="absolute left-[-1%] bottom-1.5 w-[102%] h-2.5 bg-white/50 rounded-sm -rotate-1 -z-10"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

</x-app-layout>
