<x-app-layout>
    <x-slot name="title">Our Programmes</x-slot>

    @php
        $allSubjects = \App\Models\Subject::where('is_active', true)->get();
    @endphp

    <div x-data="{ 
        activeProgramme: null, 
        toggle(id) { 
            const isOpeningFromClosed = (this.activeProgramme === null);
            if (this.activeProgramme === id) {
                this.activeProgramme = null; 
            } else {
                this.activeProgramme = id;
                if (isOpeningFromClosed) {
                    setTimeout(() => {
                        const el = document.getElementById('programme-breakdown-section');
                        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }, 100);
                }
            }
        } 
    }">
        {{-- ══════════════════════════════════════════════
             SECTION 1: HERO HEADER
        ══════════════════════════════════════════════ --}}
        <section class="bg-gradient-to-br from-primary-950 via-primary-900 to-navy text-white py-10 md:py-12 text-center relative overflow-hidden">
            <div class="absolute -bottom-1 left-0 right-0 overflow-hidden leading-none pointer-events-none z-10">
                <svg viewBox="0 0 1440 48" fill="none" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-8 md:h-10 block">
                    <path d="M0,20 Q720,48 1440,20 L1440,48 L0,48 Z" fill="#f8fafc"/>
                </svg>
            </div>
            <div class="relative max-w-3xl mx-auto px-4">
                <h1 class="font-heading font-bold text-3xl md:text-4xl text-white mb-2">Our Programmes</h1>
            </div>
        </section>

        {{-- ══════════════════════════════════════════════
             SECTION 2: EDUCATIONAL PATHWAYS (EXPANDING CARDS)
        ══════════════════════════════════════════════ --}}
        <section class="py-20 bg-slate-50 relative overflow-x-hidden">
            <div class="max-w-[96rem] mx-auto px-4 sm:px-6 lg:px-8 relative z-20">
                <!-- Section Header -->
                <div class="text-center max-w-3xl mx-auto mb-16 transition-all duration-700" :class="activeProgramme !== null ? 'opacity-0 h-0 overflow-hidden mb-0' : 'opacity-100 h-auto'">
                    <h2 class="font-heading text-3xl md:text-4xl font-bold text-primary-950 mb-4 tracking-tight">Our Educational Pathways</h2>
                </div>

                {{-- Skeleton placeholder shown before Alpine.js initializes (prevents FOUC) --}}
                <div id="prog-skeleton" class="flex flex-col lg:flex-row gap-6 md:gap-8 h-auto lg:h-[550px]">
                    <div class="rounded-[2rem] overflow-hidden bg-slate-200 lg:w-[33.33%] h-[480px] lg:h-auto animate-pulse relative">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-300 to-transparent"></div>
                        <div class="absolute bottom-8 left-8 space-y-3">
                            <div class="h-3 w-20 bg-slate-300 rounded-full"></div>
                            <div class="h-7 w-36 bg-slate-300 rounded-lg"></div>
                            <div class="h-7 w-28 bg-slate-300 rounded-lg"></div>
                        </div>
                    </div>
                    <div class="rounded-[2rem] overflow-hidden bg-slate-200 lg:w-[33.33%] h-[480px] lg:h-auto animate-pulse relative" style="animation-delay:150ms">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-300 to-transparent"></div>
                        <div class="absolute bottom-8 left-8 space-y-3">
                            <div class="h-3 w-20 bg-slate-300 rounded-full"></div>
                            <div class="h-7 w-36 bg-slate-300 rounded-lg"></div>
                            <div class="h-7 w-28 bg-slate-300 rounded-lg"></div>
                        </div>
                    </div>
                    <div class="rounded-[2rem] overflow-hidden bg-slate-200 lg:w-[33.33%] h-[480px] lg:h-auto animate-pulse relative" style="animation-delay:300ms">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-300 to-transparent"></div>
                        <div class="absolute bottom-8 left-8 space-y-3">
                            <div class="h-3 w-20 bg-slate-300 rounded-full"></div>
                            <div class="h-7 w-36 bg-slate-300 rounded-lg"></div>
                            <div class="h-7 w-28 bg-slate-300 rounded-lg"></div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row gap-6 md:gap-8 transition-all duration-500 ease-out"
                     :class="activeProgramme !== null ? 'h-auto' : 'h-auto lg:h-[550px]'"
                     x-cloak
                     x-init="$nextTick(() => { const s = document.getElementById('prog-skeleton'); if(s) s.style.display='none'; })">
                    
                    {{-- 1. PRIMARY EDUCATION CARD (4, 5, 6) --}}
                    <div 
                        class="group relative rounded-[2rem] overflow-hidden transition-all duration-500 ease-out transform-gpu flex flex-col border border-gray-200"
                        :class="activeProgramme === 'primary-education' 
                            ? 'h-auto lg:h-auto lg:w-[75%] ring-2 ring-amber-500/40 shadow-2xl border border-slate-700 cursor-default order-first lg:order-none' 
                            : (activeProgramme ? 'h-[120px] lg:h-auto lg:w-[12.5%] opacity-70 hover:opacity-100 grayscale hover:grayscale-0 cursor-pointer hover:shadow-lg' : 'h-[480px] lg:h-auto lg:w-[33.33%] hover:shadow-2xl hover:-translate-y-2 cursor-pointer')"
                        @click="if(activeProgramme !== 'primary-education') activeProgramme = 'primary-education'"
                    >
                        <!-- Background Image -->
                        <div class="absolute inset-0 z-0 bg-primary-950">
                            <img src="{{ asset('images/primary_prog.png') }}" class="w-full h-full object-cover transition-all duration-500 ease-out transform-gpu" 
                                 :class="activeProgramme === 'primary-education' ? 'scale-105 opacity-40' : (activeProgramme ? 'scale-100 opacity-60' : 'group-hover:scale-110 opacity-100')" alt="Primary Education (4,5,6)" />
                            <div class="absolute inset-0 transition-opacity duration-500" :class="activeProgramme === 'primary-education' ? 'bg-gradient-to-r from-primary-950/80 to-transparent' : 'bg-gradient-to-t from-primary-950/95 via-primary-900/40 to-transparent'"></div>
                        </div>

                        <!-- DEFAULT STATE: Front Content -->
                        <div class="z-10 p-6 md:p-8 flex flex-col justify-end h-full transition-all duration-300 ease-out w-full" :class="activeProgramme ? 'opacity-0 pointer-events-none absolute inset-0' : 'opacity-100 relative'">
                            <div class="absolute top-6 right-6 w-14 h-14 rounded-full bg-amber-500/90 backdrop-blur-md text-white flex items-center justify-center shadow-[0_4px_20px_rgba(245,158,11,0.5)] border border-white/20 transform transition-all duration-500 group-hover:scale-110 group-hover:rotate-12">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                            </div>
                            <div class="transform transition-all duration-500 group-hover:-translate-y-2">
                                <div class="mb-3 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-amber-400 shadow-[0_0_15px_rgba(251,191,36,0.5)]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-amber-950" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                                    <span class="text-amber-950 text-xs font-extrabold uppercase tracking-widest">Year 4 – Year 6</span>
                                </div>
                                <h2 class="font-heading font-extrabold text-3xl md:text-4xl text-white mb-3 leading-tight drop-shadow-lg">Primary Education</h2>
                                <p class="text-white/90 text-sm leading-relaxed mb-6 opacity-0 max-h-0 group-hover:opacity-100 group-hover:max-h-24 transition-all duration-500 ease-in-out">Mastering core subjects, building strong foundations in Mathematics and Science, and developing UASA examination readiness.</p>
                            </div>
                            <button @click.stop="toggle('primary-education')" class="w-full relative overflow-hidden group/btn font-semibold text-sm py-3.5 px-6 rounded-xl transition-all duration-300 flex items-center justify-between cursor-pointer border border-white/30 hover:border-white/80 bg-white/10 hover:bg-white/20 backdrop-blur-md">
                                <div class="absolute inset-0 bg-gradient-to-r from-amber-500 to-amber-600 opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <span class="relative z-10 text-white flex items-center gap-2">Explore Programme</span>
                                <div class="relative z-10 w-8 h-8 rounded-full bg-white/20 flex items-center justify-center transition-transform duration-300 group-hover/btn:translate-x-1"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg></div>
                            </button>
                        </div>

                        <!-- EXPANDED STATE: Detailed Content -->
                        <div class="z-30 flex flex-col transition-all duration-400 delay-75 ease-out transform-gpu" 
                             :class="activeProgramme === 'primary-education' ? 'relative opacity-100 translate-y-0 lg:translate-x-0' : 'absolute inset-0 opacity-0 translate-y-12 lg:translate-y-0 lg:translate-x-24 pointer-events-none'">
                             
                             <!-- Top Intro Row -->
                             <div class="w-full flex flex-row items-center justify-between p-6 md:p-8 lg:px-12 lg:pt-10 shrink-0 relative z-10 h-auto lg:h-[220px]">
                                 <div>
                                     <div class="mb-3 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-amber-400 shadow-[0_0_15px_rgba(251,191,36,0.5)]">
                                         <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-amber-950" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                                         <span class="text-amber-950 text-xs font-extrabold uppercase tracking-widest">Year 4 – Year 6</span>
                                     </div>
                                     <h2 class="font-heading font-black text-3xl sm:text-4xl lg:text-5xl text-white mb-4 leading-tight drop-shadow-xl">Primary Education (Year 4, 5, 6)</h2>
                                     <p class="text-white/90 text-sm sm:text-base leading-relaxed drop-shadow-md hidden sm:block">Building a robust foundation across Bahasa Melayu, English, Mathematics, and Science through KSSR mastery and UASA readiness.</p>
                                 </div>
                                 <button @click.stop="activeProgramme = null" class="w-12 h-12 rounded-full bg-white/20 hover:bg-white/30 border border-white/40 backdrop-blur-md flex items-center justify-center text-white transition-all transform hover:-translate-x-1 lg:hover:-translate-x-2 hidden lg:flex self-start">
                                     <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                                 </button>
                             </div>

                             <!-- Bottom Breakdown Section -->
                             <div class="w-full flex flex-col pt-4 pb-0" x-data="{ activeYear: null }">
                                 <div class="bg-white rounded-t-[2rem] p-5 md:p-8 pb-10 shadow-2xl relative border border-white/20 flex-1">
                                     <button @click.stop="activeProgramme = null" class="absolute top-4 right-4 w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 flex items-center justify-center transition-all lg:hidden z-50">
                                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                     </button>
                                     {{-- Header Bar --}}
                                <div class="flex flex-col mb-8">
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 w-full lg:w-[580px] shrink-0">
                                        {{-- Year 4 Card --}}
                                        <button 
                                            @click="activeYear = activeYear === 'year-4' ? null : 'year-4'" 
                                            class="relative overflow-hidden rounded-2xl p-4 transition-all duration-300 flex flex-col justify-center min-w-[160px] text-left cursor-pointer border group"
                                            :class="activeYear === 'year-4' ? 'bg-amber-500 border-amber-500 text-white shadow-xl scale-[1.02]' : 'bg-slate-50/80 border-slate-200/80 hover:bg-white hover:border-slate-300 hover:shadow-md text-slate-800'"
                                        >
                                            <div class="absolute inset-0 bg-gradient-to-br from-amber-400 to-amber-500 opacity-0 transition-opacity duration-300" :class="activeYear === 'year-4' ? 'opacity-100' : ''"></div>
                                            <div class="relative z-10 flex items-center justify-between w-full mb-2">
                                                <span class="inline-block text-[11px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider transition-colors duration-300 shadow-sm" :class="activeYear === 'year-4' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800'">Year 4</span>
                                                <svg class="w-3.5 h-3.5 transition-transform duration-300" :class="activeYear === 'year-4' ? 'rotate-180 text-white' : 'text-gray-300 group-hover:text-amber-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                            </div>
                                            <h4 class="relative z-10 font-heading font-extrabold text-sm sm:text-base leading-tight transition-colors duration-300 whitespace-nowrap" :class="activeYear === 'year-4' ? 'text-white' : 'text-gray-800 group-hover:text-slate-900'">Foundation Mastery</h4>
                                            <p class="relative z-10 text-[11px] font-semibold mt-1 transition-colors duration-300" :class="activeYear === 'year-4' ? 'text-amber-100' : 'text-gray-400'">Programme</p>
                                        </button>
                                        
                                        {{-- Year 5 Card --}}
                                        <button 
                                            @click="activeYear = activeYear === 'year-5' ? null : 'year-5'" 
                                            class="relative overflow-hidden rounded-2xl p-4 transition-all duration-300 flex flex-col justify-center min-w-[160px] text-left cursor-pointer border group"
                                            :class="activeYear === 'year-5' ? 'bg-amber-500 border-amber-500 text-white shadow-xl scale-[1.02]' : 'bg-slate-50/80 border-slate-200/80 hover:bg-white hover:border-slate-300 hover:shadow-md text-slate-800'"
                                        >
                                            <div class="absolute inset-0 bg-gradient-to-br from-amber-400 to-amber-500 opacity-0 transition-opacity duration-300" :class="activeYear === 'year-5' ? 'opacity-100' : ''"></div>
                                            <div class="relative z-10 flex items-center justify-between w-full mb-2">
                                                <span class="inline-block text-[11px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider transition-colors duration-300 shadow-sm" :class="activeYear === 'year-5' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800'">Year 5</span>
                                                <svg class="w-3.5 h-3.5 transition-transform duration-300" :class="activeYear === 'year-5' ? 'rotate-180 text-white' : 'text-gray-300 group-hover:text-amber-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                            </div>
                                            <h4 class="relative z-10 font-heading font-extrabold text-sm sm:text-base leading-tight transition-colors duration-300 whitespace-nowrap" :class="activeYear === 'year-5' ? 'text-white' : 'text-gray-800 group-hover:text-slate-900'">Concept Advancement</h4>
                                            <p class="relative z-10 text-[11px] font-semibold mt-1 transition-colors duration-300" :class="activeYear === 'year-5' ? 'text-amber-100' : 'text-gray-400'">Programme</p>
                                        </button>
                                        
                                        {{-- Year 6 Card --}}
                                        <button 
                                            @click="activeYear = activeYear === 'year-6' ? null : 'year-6'" 
                                            class="relative overflow-hidden rounded-2xl p-4 transition-all duration-300 flex flex-col justify-center min-w-[160px] text-left cursor-pointer border group"
                                            :class="activeYear === 'year-6' ? 'bg-amber-500 border-amber-500 text-white shadow-xl scale-[1.02]' : 'bg-slate-50/80 border-slate-200/80 hover:bg-white hover:border-slate-300 hover:shadow-md text-slate-800'"
                                        >
                                            <div class="absolute inset-0 bg-gradient-to-br from-amber-400 to-amber-500 opacity-0 transition-opacity duration-300" :class="activeYear === 'year-6' ? 'opacity-100' : ''"></div>
                                            <div class="relative z-10 flex items-center justify-between w-full mb-2">
                                                <span class="inline-block text-[11px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider transition-colors duration-300 shadow-sm" :class="activeYear === 'year-6' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800'">Year 6</span>
                                                <svg class="w-3.5 h-3.5 transition-transform duration-300" :class="activeYear === 'year-6' ? 'rotate-180 text-white' : 'text-gray-300 group-hover:text-amber-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                            </div>
                                            <h4 class="relative z-10 font-heading font-extrabold text-sm sm:text-base leading-tight transition-colors duration-300 whitespace-nowrap" :class="activeYear === 'year-6' ? 'text-white' : 'text-gray-800 group-hover:text-slate-900'">UASA Readiness</h4>
                                            <p class="relative z-10 text-[11px] font-semibold mt-1 transition-colors duration-300" :class="activeYear === 'year-6' ? 'text-amber-100' : 'text-gray-400'">Programme</p>
                                        </button>
                                    </div>
                                </div>

                            {{-- Interactive Speech Bubble Callout Dropdown Box --}}
                            <div 
                                x-show="activeYear !== null" 
                                x-transition:enter="transition-all ease-out duration-300"
                                x-transition:enter-start="opacity-0 -translate-y-2 scale-98"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                x-transition:leave="transition-all ease-in duration-200"
                                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                x-transition:leave-end="opacity-0 -translate-y-2 scale-98"
                                class="mb-8 relative z-20 w-full"
                            >
                                <div class="relative w-full">
                                    {{-- Speech Bubble Card Container --}}
                                    <div class="bg-gradient-to-br from-amber-600 via-amber-700 to-amber-800 text-white rounded-2xl p-5 md:p-6 shadow-xl border border-amber-400/30 relative">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="w-full">
                                                {{-- Year 4 Content --}}
                                                <template x-if="activeYear === 'year-4'">
                                                    <div>
                                                        <ul class="space-y-2 text-xs md:text-sm text-amber-50 font-medium">
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-amber-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Bahasa Melayu & English</strong> — Master grammar (tatabahasa), vocabulary expansion, comprehension answering, and creative sentence construction.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-amber-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Mathematics</strong> — Develop mastery in whole numbers up to 100,000, fractions, decimals, percentages, time, mass, and 2D/3D shapes.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-amber-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Science</strong> — Scientific investigation skills, inquiry experiments, living things, physical sciences, and materials.
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </template>

                                                {{-- Year 5 Content --}}
                                                <template x-if="activeYear === 'year-5'">
                                                    <div>
                                                        <ul class="space-y-2 text-xs md:text-sm text-amber-50 font-medium">
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-amber-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Bahasa Melayu & English</strong> — Structured essay writing (karangan berformat), complex comprehension passages, and literature appreciation.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-amber-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Mathematics</strong> — Advanced operations, ratios, proportions, coordinate geometry, data handling, and multi-step word problems.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-amber-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Science</strong> — Energy forms & sources, electricity circuits, states of matter, survival of species, and food chains.
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </template>

                                                {{-- Year 6 Content --}}
                                                <template x-if="activeYear === 'year-6'">
                                                    <div>
                                                        <ul class="space-y-2 text-xs md:text-sm text-amber-50 font-medium">
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-amber-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">UASA Exam Drilling</strong> — Intensive practice on UASA assessment formats (Bahagian A, B & C) and marking criteria.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-amber-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">KBAT Problem Solving</strong> — High-order thinking skills in Mathematics & Science, error analysis, and time-management strategies.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-amber-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Bahasa Melayu & English Mastery</strong> — Polished essay techniques, idiom usage (peribahasa), and critical reading comprehension.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-amber-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Secondary Transition Prep</strong> — Bridging primary concepts to Form 1 secondary subjects to build early confidence.
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </template>
                                            </div>

                                            {{-- Close Button --}}
                                            <button @click="activeYear = null" class="text-white/80 hover:text-white hover:bg-white/20 p-1.5 rounded-full transition-all cursor-pointer shrink-0">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Main Primary Learning Flowchart Process Section --}}
                            <div class="w-full mb-4">
                                <div class="rounded-3xl p-6 md:p-8 border border-amber-200/80 shadow-sm flex flex-col justify-between bg-amber-50/60 border-amber-200">
                                    <div>
                                        <div class="flex flex-wrap items-center justify-between gap-4 mb-6 pb-4 border-b border-amber-200/70">
                                            <div>
                                                <h3 class="font-heading font-black text-xl md:text-2xl text-amber-950">PRIMARY LEARNING PATHWAY (YEAR 4–6)</h3>
                                                <p class="text-xs md:text-sm font-semibold text-amber-700">8-Step Continuous Concept & UASA Mastery Pathway</p>
                                            </div>
                                            <span class="text-white text-xs font-bold px-3.5 py-1.5 rounded-full uppercase tracking-wider shadow-sm bg-amber-500">Primary Flowchart</span>
                                        </div>

                                        {{-- Step-by-Step Flowchart Grid --}}
                                        <div class="py-2 w-full">
                                            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-4 xl:grid-cols-4 gap-3 w-full items-stretch">
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-1 lg:row-start-1">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 1</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Diagnostic Evaluation</span>
                                                    <span class="hidden lg:block absolute -right-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">➔</span>
                                                </div>
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-2 lg:row-start-1">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 2</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Concept Foundation</span>
                                                    <span class="hidden lg:block absolute -right-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">➔</span>
                                                </div>
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-3 lg:row-start-1">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 3</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Guided Practice</span>
                                                    <span class="hidden lg:block absolute -right-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">➔</span>
                                                </div>
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-4 lg:row-start-1">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 4</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Skill Consolidation</span>
                                                    <span class="hidden lg:block absolute -bottom-3.5 left-1/2 -translate-x-1/2 z-10 text-gray-300 text-sm font-bold pointer-events-none">↓</span>
                                                </div>
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-4 lg:row-start-2">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 5</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Progress Tracking</span>
                                                    <span class="hidden lg:block absolute -left-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">←</span>
                                                </div>
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-3 lg:row-start-2">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 6</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Exam Answering Skills</span>
                                                    <span class="hidden lg:block absolute -left-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">←</span>
                                                </div>
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-2 lg:row-start-2">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 7</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Targeted Intervention</span>
                                                    <span class="hidden lg:block absolute -left-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">←</span>
                                                </div>
                                                <div class="relative bg-gradient-to-br from-amber-400 to-amber-600 rounded-xl text-center shadow-lg shadow-amber-500/30 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.05] transition-all duration-300 group border border-white/20 lg:col-start-1 lg:row-start-2">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest text-white/80 mb-0.5 drop-shadow-sm">Goal</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-white drop-shadow-sm">UASA Excellence</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        
                                 </div>
                             </div>
                        </div>
                        
                        <!-- SLIM TAB VERTICAL TEXT -->
                        <div class="absolute inset-0 z-20 flex items-center justify-center transition-all duration-400 ease-out pointer-events-none" :class="activeProgramme && activeProgramme !== 'primary-education' ? 'opacity-100' : 'opacity-0'">
                             <div class="bg-black/50 backdrop-blur-sm absolute inset-0"></div>
                             <h3 class="text-white font-heading font-black text-xl lg:text-3xl tracking-widest whitespace-nowrap lg:-rotate-90 origin-center drop-shadow-md transform transition-transform duration-400 ease-out" :class="activeProgramme !== 'primary-education' ? 'translate-y-0' : 'translate-y-10'">PRIMARY (4, 5, 6)</h3>
                        </div>
                    </div>


                    {{-- 2. LOWER SECONDARY EDUCATION CARD--}}
                    <div 
                        class="group relative rounded-[2rem] overflow-hidden transition-all duration-500 ease-out transform-gpu flex flex-col border border-gray-200"
                        :class="activeProgramme === 'lower-secondary-education' 
                            ? 'h-auto lg:h-auto lg:w-[75%] ring-2 ring-emerald-500/40 shadow-2xl border border-slate-700 cursor-default order-first lg:order-none' 
                            : (activeProgramme ? ('h-[120px] lg:h-auto lg:w-[12.5%] opacity-70 hover:opacity-100 grayscale hover:grayscale-0 cursor-pointer hover:shadow-lg ' + (activeProgramme === 'upper-secondary-education' ? 'lg:-ml-3.5' : '')) : 'h-[480px] lg:h-auto lg:w-[33.33%] hover:shadow-2xl hover:-translate-y-2 cursor-pointer')"
                        @click="if(activeProgramme !== 'lower-secondary-education') activeProgramme = 'lower-secondary-education'"
                    >
                        <!-- Background Image -->
                        <div class="absolute inset-0 z-0 bg-primary-950">
                            <img src="{{ asset('images/secondary_prog.png') }}" class="w-full h-full object-cover transition-all duration-500 ease-out transform-gpu" 
                                 :class="activeProgramme === 'lower-secondary-education' ? 'scale-105 opacity-40' : (activeProgramme ? 'scale-100 opacity-60' : 'group-hover:scale-110 opacity-100')" alt="Lower Secondary Education" />
                            <div class="absolute inset-0 transition-opacity duration-500" :class="activeProgramme === 'lower-secondary-education' ? 'bg-gradient-to-r from-primary-950/80 to-transparent' : 'bg-gradient-to-t from-primary-950/95 via-primary-900/40 to-transparent'"></div>
                        </div>

                        <!-- DEFAULT STATE: Front Content -->
                        <div class="z-10 p-6 md:p-8 flex flex-col justify-end h-full transition-all duration-300 ease-out w-full" :class="activeProgramme ? 'opacity-0 pointer-events-none absolute inset-0' : 'opacity-100 relative'">
                            <div class="absolute top-6 right-6 w-14 h-14 rounded-full bg-emerald-500/90 backdrop-blur-md text-white flex items-center justify-center shadow-[0_4px_20px_rgba(52,211,153,0.5)] border border-white/20 transform transition-all duration-500 group-hover:scale-110 group-hover:rotate-12">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            </div>
                            <div class="transform transition-all duration-500 group-hover:-translate-y-2">
                                <div class="mb-3 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-emerald-400 shadow-[0_0_15px_rgba(52,211,153,0.5)]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-emerald-950" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                                    <span class="text-emerald-950 text-xs font-extrabold uppercase tracking-widest">Form 1 – Form 3</span>
                                </div>
                                <h2 class="font-heading font-extrabold text-3xl md:text-4xl text-white mb-3 leading-tight drop-shadow-lg">Lower Secondary Education</h2>
                                <p class="text-white/90 text-sm leading-relaxed mb-6 opacity-0 max-h-0 group-hover:opacity-100 group-hover:max-h-24 transition-all duration-500 ease-in-out">Strengthening KSSM core competencies, cultivating critical thinking (KBAT), and preparing for upper secondary streaming.</p>
                            </div>
                            <button @click.stop="toggle('lower-secondary-education')" class="w-full relative overflow-hidden group/btn font-semibold text-sm py-3.5 px-6 rounded-xl transition-all duration-300 flex items-center justify-between cursor-pointer border border-white/30 hover:border-white/80 bg-white/10 hover:bg-white/20 backdrop-blur-md">
                                <div class="absolute inset-0 bg-gradient-to-r from-emerald-500 to-emerald-600 opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <span class="relative z-10 text-white flex items-center gap-2">Explore Programme</span>
                                <div class="relative z-10 w-8 h-8 rounded-full bg-white/20 flex items-center justify-center transition-transform duration-300 group-hover/btn:translate-x-1"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg></div>
                            </button>
                        </div>

                        <!-- EXPANDED STATE: Detailed Content -->
                        <div class="z-30 flex flex-col transition-all duration-400 delay-75 ease-out transform-gpu" 
                             :class="activeProgramme === 'lower-secondary-education' ? 'relative opacity-100 translate-y-0 lg:translate-x-0' : 'absolute inset-0 opacity-0 translate-y-12 lg:translate-y-0 lg:translate-x-24 pointer-events-none'">
                             
                             <!-- Top Intro Row -->
                             <div class="w-full flex flex-row items-center justify-between p-6 md:p-8 lg:px-12 lg:pt-10 shrink-0 relative z-10 h-auto lg:h-[220px]">
                                 <div>
                                     <div class="mb-3 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-emerald-400 shadow-[0_0_15px_rgba(52,211,153,0.5)]">
                                         <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-emerald-950" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                                         <span class="text-emerald-950 text-xs font-extrabold uppercase tracking-widest">Form 1 – Form 3</span>
                                     </div>
                                     <h2 class="font-heading font-black text-3xl sm:text-4xl lg:text-5xl text-white mb-4 leading-tight drop-shadow-xl">Lower Secondary Education (Form 1–3)</h2>
                                     <p class="text-white/90 text-sm sm:text-base leading-relaxed drop-shadow-md hidden sm:block">Solid KSSM secondary foundation across Bahasa Melayu, English, Mathematics, Science, and Sejarah with higher-order thinking (KBAT) mastery.</p>
                                 </div>
                                 <button @click.stop="activeProgramme = null" class="w-12 h-12 rounded-full bg-white/20 hover:bg-white/30 border border-white/40 backdrop-blur-md flex items-center justify-center text-white transition-all transform hover:-translate-x-1 lg:hover:-translate-x-2 hidden lg:flex self-start">
                                     <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                                 </button>
                             </div>

                             <!-- Bottom Breakdown Section -->
                             <div class="w-full flex flex-col pt-4 pb-0" x-data="{ activeLowerSec: null }">
                                 <div class="bg-white rounded-t-[2rem] p-5 md:p-8 pb-10 shadow-2xl relative border border-white/20 flex-1">
                                     <button @click.stop="activeProgramme = null" class="absolute top-4 right-4 w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 flex items-center justify-center transition-all lg:hidden z-50">
                                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                     </button>
                                     {{-- Header Bar --}}
                            <div class="flex flex-col mb-6">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 w-full lg:w-[580px] shrink-0">
                                    {{-- Form 1 Card --}}
                                    <button 
                                        @click="activeLowerSec = activeLowerSec === 'form-1' ? null : 'form-1'" 
                                        class="relative overflow-hidden rounded-2xl p-4 transition-all duration-300 flex flex-col justify-center min-w-[160px] text-left cursor-pointer border group"
                                        :class="activeLowerSec === 'form-1' ? 'bg-slate-900 border-slate-900 text-white shadow-xl scale-[1.02]' : 'bg-slate-50/80 border-slate-200/80 hover:bg-white hover:border-slate-300 hover:shadow-md text-slate-800'"
                                    >
                                        <div class="absolute inset-0 bg-gradient-to-br from-emerald-400 to-emerald-500 opacity-0 transition-opacity duration-300" :class="activeLowerSec === 'form-1' ? 'opacity-100' : ''"></div>
                                        <div class="relative z-10 flex items-center justify-between w-full mb-2">
                                            <span class="inline-block text-[11px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider transition-colors duration-300 shadow-sm" :class="activeLowerSec === 'form-1' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-700'">Form 1</span>
                                            <svg class="w-3.5 h-3.5 transition-transform duration-300" :class="activeLowerSec === 'form-1' ? 'rotate-180 text-white' : 'text-gray-300 group-hover:text-emerald-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                        </div>
                                        <h4 class="relative z-10 font-heading font-extrabold text-sm sm:text-base leading-tight transition-colors duration-300 whitespace-nowrap" :class="activeLowerSec === 'form-1' ? 'text-white' : 'text-gray-800 group-hover:text-slate-900'">Transition & Foundation</h4>
                                        <p class="relative z-10 text-[11px] font-semibold mt-1 transition-colors duration-300" :class="activeLowerSec === 'form-1' ? 'text-slate-400' : 'text-gray-400'">Programme</p>
                                    </button>

                                    {{-- Form 2 Card --}}
                                    <button 
                                        @click="activeLowerSec = activeLowerSec === 'form-2' ? null : 'form-2'" 
                                        class="relative overflow-hidden rounded-2xl p-4 transition-all duration-300 flex flex-col justify-center min-w-[160px] text-left cursor-pointer border group"
                                        :class="activeLowerSec === 'form-2' ? 'bg-slate-900 border-slate-900 text-white shadow-xl scale-[1.02]' : 'bg-slate-50/80 border-slate-200/80 hover:bg-white hover:border-slate-300 hover:shadow-md text-slate-800'"
                                    >
                                        <div class="absolute inset-0 bg-gradient-to-br from-emerald-400 to-emerald-500 opacity-0 transition-opacity duration-300" :class="activeLowerSec === 'form-2' ? 'opacity-100' : ''"></div>
                                        <div class="relative z-10 flex items-center justify-between w-full mb-2">
                                            <span class="inline-block text-[11px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider transition-colors duration-300 shadow-sm" :class="activeLowerSec === 'form-2' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-700'">Form 2</span>
                                            <svg class="w-3.5 h-3.5 transition-transform duration-300" :class="activeLowerSec === 'form-2' ? 'rotate-180 text-white' : 'text-gray-300 group-hover:text-emerald-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                        </div>
                                        <h4 class="relative z-10 font-heading font-extrabold text-sm sm:text-base leading-tight transition-colors duration-300 whitespace-nowrap" :class="activeLowerSec === 'form-2' ? 'text-white' : 'text-gray-800 group-hover:text-slate-900'">Core Strengthening</h4>
                                        <p class="relative z-10 text-[11px] font-semibold mt-1 transition-colors duration-300" :class="activeLowerSec === 'form-2' ? 'text-slate-400' : 'text-gray-400'">Programme</p>
                                    </button>

                                    {{-- Form 3 Card --}}
                                    <button 
                                        @click="activeLowerSec = activeLowerSec === 'form-3' ? null : 'form-3'" 
                                        class="relative overflow-hidden rounded-2xl p-4 transition-all duration-300 flex flex-col justify-center min-w-[160px] text-left cursor-pointer border group"
                                        :class="activeLowerSec === 'form-3' ? 'bg-slate-900 border-slate-900 text-white shadow-xl scale-[1.02]' : 'bg-slate-50/80 border-slate-200/80 hover:bg-white hover:border-slate-300 hover:shadow-md text-slate-800'"
                                    >
                                        <div class="absolute inset-0 bg-gradient-to-br from-emerald-400 to-emerald-500 opacity-0 transition-opacity duration-300" :class="activeLowerSec === 'form-3' ? 'opacity-100' : ''"></div>
                                        <div class="relative z-10 flex items-center justify-between w-full mb-2">
                                            <span class="inline-block text-[11px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider transition-colors duration-300 shadow-sm" :class="activeLowerSec === 'form-3' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-700'">Form 3</span>
                                            <svg class="w-3.5 h-3.5 transition-transform duration-300" :class="activeLowerSec === 'form-3' ? 'rotate-180 text-white' : 'text-gray-300 group-hover:text-emerald-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                        </div>
                                        <h4 class="relative z-10 font-heading font-extrabold text-sm sm:text-base leading-tight transition-colors duration-300 whitespace-nowrap" :class="activeLowerSec === 'form-3' ? 'text-white' : 'text-gray-800 group-hover:text-slate-900'">UASA & Stream Prep</h4>
                                        <p class="relative z-10 text-[11px] font-semibold mt-1 transition-colors duration-300" :class="activeLowerSec === 'form-3' ? 'text-slate-400' : 'text-gray-400'">Programme</p>
                                    </button>
                                </div>
                            </div>

                            {{-- Interactive Speech Bubble Callout Dropdown Box --}}
                            <div 
                                x-show="activeLowerSec !== null" 
                                x-transition:enter="transition-all ease-out duration-300"
                                x-transition:enter-start="opacity-0 -translate-y-2 scale-98"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                x-transition:leave="transition-all ease-in duration-200"
                                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                x-transition:leave-end="opacity-0 -translate-y-2 scale-98"
                                class="mb-8 relative z-20 w-full"
                            >
                                <div class="relative w-full">
                                    {{-- Speech Bubble Card Container --}}
                                    <div class="bg-gradient-to-br from-emerald-600 via-emerald-700 to-emerald-800 text-white rounded-2xl p-5 md:p-6 shadow-xl border border-emerald-400/30 relative">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="w-full">
                                                {{-- Form 1 Content --}}
                                                <template x-if="activeLowerSec === 'form-1'">
                                                    <div>
                                                        <ul class="space-y-2 text-xs md:text-sm text-emerald-50 font-medium">
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-emerald-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Bahasa Melayu & English</strong> — Transition to secondary formats, KOMSAS literature analysis, Tatabahasa, Karangan berformat, and CEFR speaking/listening.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-emerald-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Mathematics</strong> — Master integers, rational numbers, algebraic expressions, linear equations, geometry, and coordinates.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-emerald-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Science</strong> — Scientific investigation methodology, cell biology, the periodic table, matter, light, optics, and earth systems.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-emerald-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Sejarah</strong> — Ancient civilizations, early Malay Archipelago kingdoms, geographical skills, and physical environments.
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </template>

                                                {{-- Form 2 Content --}}
                                                <template x-if="activeLowerSec === 'form-2'">
                                                    <div>
                                                        <ul class="space-y-2 text-xs md:text-sm text-emerald-50 font-medium">
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-emerald-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Bahasa Melayu & English</strong> — Complex sentence structures, essay writing (argumentative & narrative), novel study, and CEFR writing drills.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-emerald-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Mathematics</strong> — Factorisation, algebraic fractions, Pythagoras' theorem, circles, geometric transformations, and data analysis.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-emerald-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Science</strong> — Human sensory organs, nutrition, electricity, magnetism, forces, motion, and ecosystems.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-emerald-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Sejarah</strong> — Understanding history, past events, and human civilisation to appreciate how the past shapes the present and future.
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </template>

                                                {{-- Form 3 Content --}}
                                                <template x-if="activeLowerSec === 'form-3'">
                                                    <div>
                                                        <ul class="space-y-2 text-xs md:text-sm text-emerald-50 font-medium">
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-emerald-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">UASA Lower Secondary Drilling</strong> — Complete Form 3 school assessment preparation, exam-condition trial papers, and marking scheme mastery.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-emerald-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Bahasa Melayu & English</strong> — Strengthening language proficiency, communication skills, reading comprehension, writing abilities, and cultural appreciation in Bahasa Melayu and English for effective communication and academic success.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-emerald-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Mathematics & Science</strong> — Indices, standard form, trigonometry, financial mathematics, space exploration, and radioactivity.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-emerald-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Sejarah</strong> — Exploring historical events, cultures, and traditions while developing critical thinking and a deeper understanding of the world.
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </template>
                                            </div>

                                            {{-- Close Button --}}
                                            <button @click="activeLowerSec = null" class="text-white/80 hover:text-white hover:bg-white/20 p-1.5 rounded-full transition-all cursor-pointer shrink-0">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Main Lower Secondary Flowchart Process Section --}}
                            <div class="w-full mb-4">
                                <div class="rounded-3xl p-6 md:p-8 border-2 shadow-sm flex flex-col justify-between bg-emerald-50/70 border-emerald-200">
                                    <div>
                                        <div class="flex flex-wrap items-center justify-between gap-4 mb-6 pb-4 border-b border-emerald-200/70">
                                            <div>
                                                <h3 class="font-heading font-black text-xl md:text-2xl text-emerald-950">LOWER SECONDARY LEARNING PATHWAY (FORM 1–3)</h3>
                                                <p class="text-xs md:text-sm font-semibold text-emerald-700">8-Step KSSM Subject & Analytical Mastery Pathway</p>
                                            </div>
                                            <span class="text-white text-xs font-bold px-3.5 py-1.5 rounded-full uppercase tracking-wider shadow-sm bg-emerald-600">Lower Secondary Flowchart</span>
                                        </div>

                                        {{-- Step-by-Step Flowchart Grid --}}
                                        <div class="py-2 w-full">
                                            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-4 xl:grid-cols-4 gap-3 w-full items-stretch">
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-1 lg:row-start-1">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 1</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Diagnostic Assessment</span>
                                                    <span class="hidden lg:block absolute -right-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">➔</span>
                                                </div>
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-2 lg:row-start-1">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 2</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">KSSM Concept Mastery</span>
                                                    <span class="hidden lg:block absolute -right-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">➔</span>
                                                </div>
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-3 lg:row-start-1">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 3</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Guided Inquiry Practice</span>
                                                    <span class="hidden lg:block absolute -right-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">➔</span>
                                                </div>
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-4 lg:row-start-1">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 4</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">KBAT Problem Solving</span>
                                                    <span class="hidden lg:block absolute -bottom-3.5 left-1/2 -translate-x-1/2 z-10 text-gray-300 text-sm font-bold pointer-events-none">↓</span>
                                                </div>
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-4 lg:row-start-2">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 5</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Continuous Evaluation</span>
                                                    <span class="hidden lg:block absolute -left-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">←</span>
                                                </div>
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-3 lg:row-start-2">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 6</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Trial Paper Answering</span>
                                                    <span class="hidden lg:block absolute -left-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">←</span>
                                                </div>
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-2 lg:row-start-2">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 7</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Targeted Remediation</span>
                                                    <span class="hidden lg:block absolute -left-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">←</span>
                                                </div>
                                                <div class="relative bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-xl text-center shadow-lg shadow-emerald-500/30 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.05] transition-all duration-300 group border border-white/20 lg:col-start-1 lg:row-start-2">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest text-white/80 mb-0.5 drop-shadow-sm">Goal</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-white drop-shadow-sm">Form 3 / Stream Excellence</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        
                                 </div>
                             </div>
                        </div>
                        
                        <!-- SLIM TAB VERTICAL TEXT -->
                        <div class="absolute inset-0 z-20 flex items-center justify-center transition-all duration-400 ease-out pointer-events-none" :class="activeProgramme && activeProgramme !== 'lower-secondary-education' ? 'opacity-100' : 'opacity-0'">
                             <div class="bg-black/50 backdrop-blur-sm absolute inset-0"></div>
                             <h3 class="text-white font-heading font-black text-xl lg:text-3xl tracking-widest whitespace-nowrap lg:-rotate-90 origin-center drop-shadow-md transform transition-transform duration-400 ease-out" :class="activeProgramme !== 'lower-secondary-education' ? 'translate-y-0' : 'translate-y-10'">LOWER SECONDARY (FORM 1–3)</h3>
                        </div>
                    </div>


                    {{-- 3. UPPER SECONDARY EDUCATION CARD (FORM 4–5) --}}
                    <div 
                        class="group relative rounded-[2rem] overflow-hidden transition-all duration-500 ease-out transform-gpu flex flex-col border border-gray-200"
                        :class="activeProgramme === 'upper-secondary-education' 
                            ? 'h-auto lg:h-auto lg:w-[75%] ring-2 ring-blue-500/40 shadow-2xl border border-slate-700 cursor-default order-first lg:order-none' 
                            : (activeProgramme ? ('h-[120px] lg:h-auto lg:w-[12.5%] opacity-70 hover:opacity-100 grayscale hover:grayscale-0 cursor-pointer hover:shadow-lg ' + (activeProgramme === 'primary-education' ? 'lg:-ml-3.5' : '')) : 'h-[480px] lg:h-auto lg:w-[33.33%] hover:shadow-2xl hover:-translate-y-2 cursor-pointer')"
                        @click="if(activeProgramme !== 'upper-secondary-education') activeProgramme = 'upper-secondary-education'"
                    >
                        <!-- Background Image -->
                        <div class="absolute inset-0 z-0 bg-primary-950">
                            <img src="{{ asset('images/upper_secondary_prog.jpg') }}" class="w-full h-full object-cover transition-all duration-500 ease-out transform-gpu" 
                                 :class="activeProgramme === 'upper-secondary-education' ? 'scale-105 opacity-40' : (activeProgramme ? 'scale-100 opacity-60' : 'group-hover:scale-110 opacity-100')" alt="Upper Secondary Education" />
                            <div class="absolute inset-0 transition-opacity duration-500" :class="activeProgramme === 'upper-secondary-education' ? 'bg-gradient-to-r from-primary-950/80 to-transparent' : 'bg-gradient-to-t from-primary-950/95 via-primary-900/40 to-transparent'"></div>
                        </div>

                        <!-- DEFAULT STATE: Front Content -->
                        <div class="z-10 p-6 md:p-8 flex flex-col justify-end h-full transition-all duration-300 ease-out w-full" :class="activeProgramme ? 'opacity-0 pointer-events-none absolute inset-0' : 'opacity-100 relative'">
                            <div class="absolute top-6 right-6 w-14 h-14 rounded-full bg-blue-500/90 backdrop-blur-md text-white flex items-center justify-center shadow-[0_4px_20px_rgba(96,165,250,0.5)] border border-white/20 transform transition-all duration-500 group-hover:scale-110 group-hover:rotate-12">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" /></svg>
                            </div>
                            <div class="transform transition-all duration-500 group-hover:-translate-y-2">
                                <div class="mb-3 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-blue-400 shadow-[0_0_15px_rgba(96,165,250,0.5)]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-blue-950" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                                    <span class="text-blue-950 text-xs font-extrabold uppercase tracking-widest">Form 4 – Form 5</span>
                                </div>
                                <h2 class="font-heading font-extrabold text-3xl md:text-4xl text-white mb-3 leading-tight drop-shadow-lg">Upper Secondary Education</h2>
                                <p class="text-white/90 text-sm leading-relaxed mb-6 opacity-0 max-h-0 group-hover:opacity-100 group-hover:max-h-24 transition-all duration-500 ease-in-out">Intensive SPM examination preparation with elective specialization, past-year drills, KBAT answering techniques, and A+ scoring strategies.</p>
                            </div>
                            <button @click.stop="toggle('upper-secondary-education')" class="w-full relative overflow-hidden group/btn font-semibold text-sm py-3.5 px-6 rounded-xl transition-all duration-300 flex items-center justify-between cursor-pointer border border-white/30 hover:border-white/80 bg-white/10 hover:bg-white/20 backdrop-blur-md">
                                <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-blue-600 opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <span class="relative z-10 text-white flex items-center gap-2">Explore Programme</span>
                                <div class="relative z-10 w-8 h-8 rounded-full bg-white/20 flex items-center justify-center transition-transform duration-300 group-hover/btn:translate-x-1"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg></div>
                            </button>
                        </div>

                        <!-- EXPANDED STATE: Detailed Content -->
                        <div class="z-30 flex flex-col transition-all duration-400 delay-75 ease-out transform-gpu" 
                             :class="activeProgramme === 'upper-secondary-education' ? 'relative opacity-100 translate-y-0 lg:translate-x-0' : 'absolute inset-0 opacity-0 translate-y-12 lg:translate-y-0 lg:translate-x-24 pointer-events-none'">
                             
                             <!-- Top Intro Row -->
                             <div class="w-full flex flex-row items-center justify-between p-6 md:p-8 lg:px-12 lg:pt-10 shrink-0 relative z-10 h-auto lg:h-[220px]">
                                 <div>
                                     <div class="mb-3 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-blue-400 shadow-[0_0_15px_rgba(96,165,250,0.5)]">
                                         <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-blue-950" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                                         <span class="text-blue-950 text-xs font-extrabold uppercase tracking-widest">Form 4 – Form 5</span>
                                     </div>
                                     <h2 class="font-heading font-black text-3xl sm:text-4xl lg:text-5xl text-white mb-4 leading-tight drop-shadow-xl">Upper Secondary Education</h2>
                                     <p class="text-white/90 text-sm sm:text-base leading-relaxed drop-shadow-md hidden sm:block">Intensive track for SPM examination excellence, focusing on KBAT answering skills, core & elective subjects, and high-scoring techniques.</p>
                                 </div>
                                 <button @click.stop="activeProgramme = null" class="w-12 h-12 rounded-full bg-white/20 hover:bg-white/30 border border-white/40 backdrop-blur-md flex items-center justify-center text-white transition-all transform hover:-translate-x-1 lg:hover:-translate-x-2 hidden lg:flex self-start">
                                     <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                                 </button>
                             </div>

                             <!-- Bottom Breakdown Section -->
                             <div class="w-full flex flex-col pt-4 pb-0" x-data="{ activeSecTrack: null }">
                                 <div class="bg-white rounded-t-[2rem] p-5 md:p-8 pb-10 shadow-2xl relative border border-white/20 flex-1">
                                     <button @click.stop="activeProgramme = null" class="absolute top-4 right-4 w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 flex items-center justify-center transition-all lg:hidden z-50">
                                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                     </button>
                                     {{-- Header Bar --}}
                            <div class="flex flex-col mb-6">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 w-full lg:w-[680px] shrink-0">
                                    {{-- Form 4 Card --}}
                                    <button 
                                        @click="activeSecTrack = activeSecTrack === 'form-4' ? null : 'form-4'" 
                                        class="relative overflow-hidden rounded-2xl p-4 transition-all duration-300 flex flex-col justify-center min-w-[160px] text-left cursor-pointer border group"
                                        :class="activeSecTrack === 'form-4' ? 'bg-slate-900 border-slate-900 text-white shadow-xl scale-[1.02]' : 'bg-slate-50/80 border-slate-200/80 hover:bg-white hover:border-slate-300 hover:shadow-md text-slate-800'"
                                    >
                                        <div class="absolute inset-0 bg-gradient-to-br from-blue-400 to-blue-500 opacity-0 transition-opacity duration-300" :class="activeSecTrack === 'form-4' ? 'opacity-100' : ''"></div>
                                        <div class="relative z-10 flex items-center justify-between w-full mb-2">
                                            <span class="inline-block text-[11px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider transition-colors duration-300 shadow-sm" :class="activeSecTrack === 'form-4' ? 'bg-white/20 text-white' : 'bg-blue-100 text-blue-700'">Form 4</span>
                                            <svg class="w-3.5 h-3.5 transition-transform duration-300" :class="activeSecTrack === 'form-4' ? 'rotate-180 text-white' : 'text-gray-300 group-hover:text-blue-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                        </div>
                                        <h4 class="relative z-10 font-heading font-extrabold text-sm sm:text-base leading-tight transition-colors duration-300 whitespace-nowrap" :class="activeSecTrack === 'form-4' ? 'text-white' : 'text-gray-800 group-hover:text-slate-900'">SPM Foundation</h4>
                                        <p class="relative z-10 text-[11px] font-semibold mt-1 transition-colors duration-300" :class="activeSecTrack === 'form-4' ? 'text-slate-400' : 'text-gray-400'">Syllabus Mastery</p>
                                    </button>

                                    {{-- Form 5 Card --}}
                                    <button 
                                        @click="activeSecTrack = activeSecTrack === 'form-5' ? null : 'form-5'" 
                                        class="relative overflow-hidden rounded-2xl p-4 transition-all duration-300 flex flex-col justify-center min-w-[160px] text-left cursor-pointer border group"
                                        :class="activeSecTrack === 'form-5' ? 'bg-slate-900 border-slate-900 text-white shadow-xl scale-[1.02]' : 'bg-slate-50/80 border-slate-200/80 hover:bg-white hover:border-slate-300 hover:shadow-md text-slate-800'"
                                    >
                                        <div class="absolute inset-0 bg-gradient-to-br from-blue-400 to-blue-500 opacity-0 transition-opacity duration-300" :class="activeSecTrack === 'form-5' ? 'opacity-100' : ''"></div>
                                        <div class="relative z-10 flex items-center justify-between w-full mb-2">
                                            <span class="inline-block text-[11px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider transition-colors duration-300 shadow-sm" :class="activeSecTrack === 'form-5' ? 'bg-white/20 text-white' : 'bg-blue-100 text-blue-700'">Form 5</span>
                                            <svg class="w-3.5 h-3.5 transition-transform duration-300" :class="activeSecTrack === 'form-5' ? 'rotate-180 text-white' : 'text-gray-300 group-hover:text-blue-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                        </div>
                                        <h4 class="relative z-10 font-heading font-extrabold text-sm sm:text-base leading-tight transition-colors duration-300 whitespace-nowrap" :class="activeSecTrack === 'form-5' ? 'text-white' : 'text-gray-800 group-hover:text-slate-900'">SPM Exam Drilling</h4>
                                        <p class="relative z-10 text-[11px] font-semibold mt-1 transition-colors duration-300" :class="activeSecTrack === 'form-5' ? 'text-slate-400' : 'text-gray-400'">Trial & Past-Year Drills</p>
                                    </button>
                                </div>
                            </div>

                            {{-- Interactive Speech Bubble Callout Dropdown Box --}}
                            <div 
                                x-show="activeSecTrack !== null" 
                                x-transition:enter="transition-all ease-out duration-300"
                                x-transition:enter-start="opacity-0 -translate-y-2 scale-98"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                x-transition:leave="transition-all ease-in duration-200"
                                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                x-transition:leave-end="opacity-0 -translate-y-2 scale-98"
                                class="mb-8 relative z-20 w-full"
                            >
                                <div class="relative w-full">
                                    {{-- Speech Bubble Card Container --}}
                                    <div class="bg-gradient-to-br from-blue-600 via-blue-700 to-blue-800 text-white rounded-2xl p-5 md:p-6 shadow-xl border border-blue-400/30 relative">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="w-full">
                                                {{-- Form 4 Content --}}
                                                <template x-if="activeSecTrack === 'form-4'">
                                                    <div>
                                                        <ul class="space-y-2 text-xs md:text-sm text-blue-50 font-medium">
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-blue-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Core Subjects (BM, BI, Math, Sejarah)</strong> — Master SPM format requirements, Kertas 1, 2, 3 & 4 answering techniques, and Sejarah KBAT essay structures.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-blue-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Additional Mathematics</strong> — Functions, quadratic equations, systems of equations, indices, logarithms, and coordinate geometry fundamentals.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-blue-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Pure Sciences (Physics, Chemistry, Biology)</strong> — Thorough concept mastery, formula derivations, chemical equations, and cellular biology processes.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-blue-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Sejarah</strong> — Building historical awareness, cultural appreciation, and national identity through the study of people, societies, and their experiences.
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </template>

                                                {{-- Form 5 Content --}}
                                                <template x-if="activeSecTrack === 'form-5'">
                                                    <div>
                                                        <ul class="space-y-2 text-xs md:text-sm text-blue-50 font-medium">
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-blue-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">SPM Trial & Past-Year Drills</strong> — Real SPM question trends, SBP and MRSM state trial paper drilling, and timed mock examinations.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-blue-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Advanced Additional Mathematics</strong> — Calculus (differentiation & integration), trigonometry, vectors, permutations, and probability distributions.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-blue-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Pure Science SPM Strategies</strong> — Paper 2 Section B/C high-mark essay formulas, experimental designs, and Paper 3 scientific practical test tips.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-blue-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Sejarah</strong> — Connecting the past with the present by understanding historical changes, human achievements, and the lessons that shape a better future.
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </template>
                                            </div>

                                            {{-- Close Button --}}
                                            <button @click="activeSecTrack = null" class="text-white/80 hover:text-white hover:bg-white/20 p-1.5 rounded-full transition-all cursor-pointer shrink-0">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Main Upper Secondary / SPM Flowchart Process Section --}}
                            <div class="w-full mb-4">
                                <div class="rounded-3xl p-6 md:p-8 border-2 shadow-sm flex flex-col justify-between bg-blue-50/70 border-blue-200">
                                    <div>
                                        <div class="flex flex-wrap items-center justify-between gap-4 mb-6 pb-4 border-b border-blue-200/70">
                                            <div>
                                                <h3 class="font-heading font-black text-xl md:text-2xl text-blue-950">UPPER SECONDARY LEARNING PATHWAY (SPM)</h3>
                                                <p class="text-xs md:text-sm font-semibold text-blue-700">8-Step Intensive SPM A+ Scoring Pathway</p>
                                            </div>
                                            <span class="text-white text-xs font-bold px-3.5 py-1.5 rounded-full uppercase tracking-wider shadow-sm bg-blue-600">SPM Flowchart</span>
                                        </div>

                                        {{-- Step-by-Step Flowchart Grid --}}
                                        <div class="py-2 w-full">
                                            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-4 xl:grid-cols-4 gap-3 w-full items-stretch">
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-1 lg:row-start-1">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 1</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">SPM Diagnostic</span>
                                                    <span class="hidden lg:block absolute -right-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">➔</span>
                                                </div>
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-2 lg:row-start-1">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 2</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Syllabus Mastery</span>
                                                    <span class="hidden lg:block absolute -right-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">➔</span>
                                                </div>
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-3 lg:row-start-1">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 3</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">KBAT Answering Tactics</span>
                                                    <span class="hidden lg:block absolute -right-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">➔</span>
                                                </div>
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-4 lg:row-start-1">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 4</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Topical Drilling</span>
                                                    <span class="hidden lg:block absolute -bottom-3.5 left-1/2 -translate-x-1/2 z-10 text-gray-300 text-sm font-bold pointer-events-none">↓</span>
                                                </div>
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-4 lg:row-start-2">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 5</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Trial Paper Simulation</span>
                                                    <span class="hidden lg:block absolute -left-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">←</span>
                                                </div>
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-3 lg:row-start-2">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 6</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Marking Scheme Breakdown</span>
                                                    <span class="hidden lg:block absolute -left-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">←</span>
                                                </div>
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-2 lg:row-start-2">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 7</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Error Correction Clinic</span>
                                                    <span class="hidden lg:block absolute -left-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">←</span>
                                                </div>
                                                <div class="relative bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl text-center shadow-lg shadow-blue-500/30 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.05] transition-all duration-300 group border border-white/20 lg:col-start-1 lg:row-start-2">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest text-white/80 mb-0.5 drop-shadow-sm">Goal</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-white drop-shadow-sm">SPM Straight A+ Goal</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        
                                 </div>
                             </div>
                        </div>
                        
                        <!-- SLIM TAB VERTICAL TEXT -->
                        <div class="absolute inset-0 z-20 flex items-center justify-center transition-all duration-400 ease-out pointer-events-none" :class="activeProgramme && activeProgramme !== 'upper-secondary-education' ? 'opacity-100' : 'opacity-0'">
                             <div class="bg-black/50 backdrop-blur-sm absolute inset-0"></div>
                             <h3 class="text-white font-heading font-black text-xl lg:text-3xl tracking-widest whitespace-nowrap lg:-rotate-90 origin-center drop-shadow-md transform transition-transform duration-400 ease-out" :class="activeProgramme !== 'upper-secondary-education' ? 'translate-y-0' : 'translate-y-10'">UPPER SECONDARY (FORM 4–5)</h3>
                        </div>
                    </div>


                </div>
            </div>
        </section>
    </div>
</x-app-layout>
