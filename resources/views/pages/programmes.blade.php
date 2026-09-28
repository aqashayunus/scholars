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
                    <p class="text-slate-600 font-light leading-relaxed">
                        From the first day of kindergarten to graduation, we provide a continuous, enriching environment tailored to every stage of your child's growth.
                    </p>
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
                    
                    {{-- 1. KINDERGARTEN CARD --}}
                    <div 
                        class="group relative rounded-[2rem] overflow-hidden transition-all duration-500 ease-out transform-gpu flex flex-col border border-gray-200"
                        :class="activeProgramme === 'kindergarten-education' 
                            ? 'h-auto lg:h-auto lg:w-[75%] ring-2 ring-amber-500/40 shadow-2xl border border-slate-700 cursor-default order-first lg:order-none' 
                            : (activeProgramme ? 'h-[120px] lg:h-auto lg:w-[12.5%] opacity-70 hover:opacity-100 grayscale hover:grayscale-0 cursor-pointer hover:shadow-lg' : 'h-[480px] lg:h-auto lg:w-[33.33%] hover:shadow-2xl hover:-translate-y-2 cursor-pointer')"
                        @click="if(activeProgramme !== 'kindergarten-education') activeProgramme = 'kindergarten-education'"
                    >
                        <!-- Background Image -->
                        <div class="absolute inset-0 z-0 bg-primary-950">
                            <img src="{{ asset('images/kindergarten_prog.png') }}" class="w-full h-full object-cover transition-all duration-500 ease-out transform-gpu" 
                                 :class="activeProgramme === 'kindergarten-education' ? 'scale-105 opacity-40' : (activeProgramme ? 'scale-100 opacity-60' : 'group-hover:scale-110 opacity-100')" alt="Kindergarten Education" />
                            <div class="absolute inset-0 transition-opacity duration-500" :class="activeProgramme === 'kindergarten-education' ? 'bg-gradient-to-r from-primary-950/80 to-transparent' : 'bg-gradient-to-t from-primary-950/95 via-primary-900/40 to-transparent'"></div>
                        </div>

                        <!-- DEFAULT STATE: Front Content -->
                        <div class="z-10 p-6 md:p-8 flex flex-col justify-end h-full transition-all duration-300 ease-out w-full" :class="activeProgramme ? 'opacity-0 pointer-events-none absolute inset-0' : 'opacity-100 relative'">
                            <div class="absolute top-6 right-6 w-14 h-14 rounded-full bg-amber-500/90 backdrop-blur-md text-white flex items-center justify-center shadow-[0_4px_20px_rgba(245,158,11,0.5)] border border-white/20 transform transition-all duration-500 group-hover:scale-110 group-hover:rotate-12">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 01-6.364 0M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm3.675 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75z" /></svg>
                            </div>
                            <div class="transform transition-all duration-500 group-hover:-translate-y-2">
                                <div class="mb-3 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-amber-400 shadow-[0_0_15px_rgba(251,191,36,0.5)]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-amber-950" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                                    <span class="text-amber-950 text-xs font-extrabold uppercase tracking-widest">Ages 4 – Ages 6</span>
                                </div>
                                <h2 class="font-heading font-extrabold text-3xl md:text-4xl text-white mb-3 leading-tight drop-shadow-lg">Kindergarten<br>Education</h2>
                                <p class="text-white/90 text-sm leading-relaxed mb-6 opacity-0 max-h-0 group-hover:opacity-100 group-hover:max-h-24 transition-all duration-500 ease-in-out">Interactive early learning building phonics, literacy, numbers & curiosity in a nurturing environment.</p>
                            </div>
                            <button @click.stop="toggle('kindergarten-education')" class="w-full relative overflow-hidden group/btn font-semibold text-sm py-3.5 px-6 rounded-xl transition-all duration-300 flex items-center justify-between cursor-pointer border border-white/30 hover:border-white/80 bg-white/10 hover:bg-white/20 backdrop-blur-md">
                                <div class="absolute inset-0 bg-gradient-to-r from-amber-500 to-amber-600 opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <span class="relative z-10 text-white flex items-center gap-2">Explore Programme</span>
                                <div class="relative z-10 w-8 h-8 rounded-full bg-white/20 flex items-center justify-center transition-transform duration-300 group-hover/btn:translate-x-1"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg></div>
                            </button>
                        </div>

                        <!-- EXPANDED STATE: Detailed Content -->
                        <div class="z-30 flex flex-col transition-all duration-400 delay-75 ease-out transform-gpu" 
                             :class="activeProgramme === 'kindergarten-education' ? 'relative opacity-100 translate-y-0 lg:translate-x-0' : 'absolute inset-0 opacity-0 translate-y-12 lg:translate-y-0 lg:translate-x-24 pointer-events-none'">
                             
                             <!-- Top Intro Row -->
                             <div class="w-full flex flex-row items-center justify-between p-6 md:p-8 lg:px-12 lg:pt-10 shrink-0 relative z-10 h-auto lg:h-[220px]">
                                 <div>
                                     <div class="mb-3 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-amber-400 shadow-[0_0_15px_rgba(251,191,36,0.5)]">
                                         <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-amber-950" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                                         <span class="text-amber-950 text-xs font-extrabold uppercase tracking-widest">Ages 4 – Ages 6</span>
                                     </div>
                                     <h2 class="font-heading font-black text-3xl sm:text-4xl lg:text-5xl text-white mb-4 leading-tight drop-shadow-xl">Kindergarten Education</h2>
                                     <p class="text-white/90 text-sm sm:text-base leading-relaxed drop-shadow-md hidden sm:block">Building a strong foundation through interactive early learning, phonics, literacy, and curiosity in a nurturing environment.</p>
                                 </div>
                                 <button @click.stop="activeProgramme = null" class="w-12 h-12 rounded-full bg-white/20 hover:bg-white/30 border border-white/40 backdrop-blur-md flex items-center justify-center text-white transition-all transform hover:-translate-x-1 lg:hover:-translate-x-2 hidden lg:flex self-start">
                                     <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                                 </button>
                             </div>

                             <!-- Bottom Breakdown Section -->
                             <div class="w-full flex flex-col pt-4 pb-0" x-data="{ activeAge: null }">
                                 <div class="bg-white rounded-t-[2rem] p-5 md:p-8 pb-10 shadow-2xl relative border border-white/20 flex-1">
                                     <button @click.stop="activeProgramme = null" class="absolute top-4 right-4 w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 flex items-center justify-center transition-all lg:hidden z-50">
                                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                     </button>
                                     {{-- Header Bar --}}
                                <div class="flex flex-col mb-8">
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 w-full lg:w-[580px] shrink-0">
                                        {{-- Age 4 Card --}}
                                        <button 
                                            @click="activeAge = activeAge === 'age-4' ? null : 'age-4'" 
                                            class="relative overflow-hidden rounded-2xl p-4 transition-all duration-300 flex flex-col justify-center min-w-[160px] text-left cursor-pointer border group"
                                            :class="activeAge === 'age-4' ? 'bg-amber-500 border-amber-500 text-white shadow-xl scale-[1.02]' : 'bg-slate-50/80 border-slate-200/80 hover:bg-white hover:border-slate-300 hover:shadow-md text-slate-800'"
                                        >
                                            <div class="absolute inset-0 bg-gradient-to-br from-amber-400 to-amber-500 opacity-0 transition-opacity duration-300" :class="activeAge === 'age-4' ? 'opacity-100' : ''"></div>
                                            <div class="relative z-10 flex items-center justify-between w-full mb-2">
                                                <span class="inline-block text-[11px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider transition-colors duration-300 shadow-sm" :class="activeAge === 'age-4' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800'">Age 4</span>
                                                <svg class="w-3.5 h-3.5 transition-transform duration-300" :class="activeAge === 'age-4' ? 'rotate-180 text-white' : 'text-gray-300 group-hover:text-amber-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                            </div>
                                            <h4 class="relative z-10 font-heading font-extrabold text-sm sm:text-base leading-tight transition-colors duration-300 whitespace-nowrap" :class="activeAge === 'age-4' ? 'text-white' : 'text-gray-800 group-hover:text-slate-900'">Early Exploration</h4>
                                            <p class="relative z-10 text-[11px] font-semibold mt-1 transition-colors duration-300" :class="activeAge === 'age-4' ? 'text-amber-100' : 'text-gray-400'">Programme</p>
                                        </button>
                                        
                                        {{-- Age 5 Card --}}
                                        <button 
                                            @click="activeAge = activeAge === 'age-5' ? null : 'age-5'" 
                                            class="relative overflow-hidden rounded-2xl p-4 transition-all duration-300 flex flex-col justify-center min-w-[160px] text-left cursor-pointer border group"
                                            :class="activeAge === 'age-5' ? 'bg-amber-500 border-amber-500 text-white shadow-xl scale-[1.02]' : 'bg-slate-50/80 border-slate-200/80 hover:bg-white hover:border-slate-300 hover:shadow-md text-slate-800'"
                                        >
                                            <div class="absolute inset-0 bg-gradient-to-br from-amber-400 to-amber-500 opacity-0 transition-opacity duration-300" :class="activeAge === 'age-5' ? 'opacity-100' : ''"></div>
                                            <div class="relative z-10 flex items-center justify-between w-full mb-2">
                                                <span class="inline-block text-[11px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider transition-colors duration-300 shadow-sm" :class="activeAge === 'age-5' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800'">Age 5</span>
                                                <svg class="w-3.5 h-3.5 transition-transform duration-300" :class="activeAge === 'age-5' ? 'rotate-180 text-white' : 'text-gray-300 group-hover:text-amber-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                            </div>
                                            <h4 class="relative z-10 font-heading font-extrabold text-sm sm:text-base leading-tight transition-colors duration-300 whitespace-nowrap" :class="activeAge === 'age-5' ? 'text-white' : 'text-gray-800 group-hover:text-slate-900'">Skill Building</h4>
                                            <p class="relative z-10 text-[11px] font-semibold mt-1 transition-colors duration-300" :class="activeAge === 'age-5' ? 'text-amber-100' : 'text-gray-400'">Programme</p>
                                        </button>
                                        
                                        {{-- Age 6 Card --}}
                                        <button 
                                            @click="activeAge = activeAge === 'age-6' ? null : 'age-6'" 
                                            class="relative overflow-hidden rounded-2xl p-4 transition-all duration-300 flex flex-col justify-center min-w-[160px] text-left cursor-pointer border group"
                                            :class="activeAge === 'age-6' ? 'bg-amber-500 border-amber-500 text-white shadow-xl scale-[1.02]' : 'bg-slate-50/80 border-slate-200/80 hover:bg-white hover:border-slate-300 hover:shadow-md text-slate-800'"
                                        >
                                            <div class="absolute inset-0 bg-gradient-to-br from-amber-400 to-amber-500 opacity-0 transition-opacity duration-300" :class="activeAge === 'age-6' ? 'opacity-100' : ''"></div>
                                            <div class="relative z-10 flex items-center justify-between w-full mb-2">
                                                <span class="inline-block text-[11px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider transition-colors duration-300 shadow-sm" :class="activeAge === 'age-6' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800'">Age 6</span>
                                                <svg class="w-3.5 h-3.5 transition-transform duration-300" :class="activeAge === 'age-6' ? 'rotate-180 text-white' : 'text-gray-300 group-hover:text-amber-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                            </div>
                                            <h4 class="relative z-10 font-heading font-extrabold text-sm sm:text-base leading-tight transition-colors duration-300 whitespace-nowrap" :class="activeAge === 'age-6' ? 'text-white' : 'text-gray-800 group-hover:text-slate-900'">School Readiness</h4>
                                            <p class="relative z-10 text-[11px] font-semibold mt-1 transition-colors duration-300" :class="activeAge === 'age-6' ? 'text-amber-100' : 'text-gray-400'">Programme</p>
                                        </button>
                                    </div>
                                </div>

                            {{-- Interactive Speech Bubble Callout Dropdown Box --}}
                            <div 
                                x-show="activeAge !== null" 
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
                                                {{-- Age 4 Content --}}
                                                <template x-if="activeAge === 'age-4'">
                                                    <div>
                                                        <ul class="space-y-2 text-xs md:text-sm text-amber-50 font-medium">
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-amber-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Language & Sound Exploration</strong> — Explore letters, phonics sounds, simple words, songs, rhymes and storytelling.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-amber-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Numbers, Shapes & Patterns</strong> — Explore counting, colours, shapes, matching, sorting and simple patterns through play.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-amber-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Nature & Science Discovery</strong> — Discover animals, plants, weather, the five senses and the world around them through simple activities.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-amber-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Creative & Sensory Play</strong> — Explore art, music, movement, textures, crafts and hands-on sensory activities.
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </template>

                                                {{-- Age 5 Content --}}
                                                <template x-if="activeAge === 'age-5'">
                                                    <div>
                                                        <ul class="space-y-2 text-xs md:text-sm text-amber-50 font-medium">
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-amber-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">English & Phonics</strong> — Build sound recognition, vocabulary, simple reading and confident speaking skills.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-amber-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Bahasa Melayu Basics</strong> — Explore suku kata, simple words, early reading and language understanding.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-amber-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Early Mathematics</strong> — Develop counting, number sense, basic operations, shapes and pattern recognition.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-amber-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Science & Discovery</strong> — Learn through observation, nature topics and simple hands-on experiments.
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </template>

                                                {{-- Age 6 Content --}}
                                                <template x-if="activeAge === 'age-6'">
                                                    <div>
                                                        <ul class="space-y-2 text-xs md:text-sm text-amber-50 font-medium">
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-amber-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Bahasa Melayu</strong> — Develop reading, basic writing, vocabulary and simple sentence understanding.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-amber-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">English Language</strong> — Build phonics, reading confidence, speaking skills and sentence construction.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-amber-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Mathematics Readiness</strong> — Strengthen number operations, shapes, patterns and early problem-solving skills.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-amber-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Science & Thinking Skills</strong> — Explore science topics, observation, reasoning and simple hands-on learning.
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </template>
                                            </div>

                                            {{-- Close Button --}}
                                            <button @click="activeAge = null" class="text-white/80 hover:text-white hover:bg-white/20 p-1.5 rounded-full transition-all cursor-pointer shrink-0">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Main Early Childhood Flowchart Process Section --}}
                            <div class="w-full mb-4">
                                <div class="rounded-3xl p-6 md:p-8 border border-amber-200/80 shadow-sm flex flex-col justify-between bg-amber-50/60 border-amber-200">
                                    <div>
                                        <div class="flex flex-wrap items-center justify-between gap-4 mb-6 pb-4 border-b border-amber-200/70">
                                            <div>
                                                <h3 class="font-heading font-black text-xl md:text-2xl text-amber-950">EARLY CHILDHOOD LEARNING PATHWAY</h3>
                                                <p class="text-xs md:text-sm font-semibold text-amber-700">8-Step Continuous Mastery & Growth Process</p>
                                            </div>
                                            <span class="text-white text-xs font-bold px-3.5 py-1.5 rounded-full uppercase tracking-wider shadow-sm bg-amber-500">Preschool Flowchart</span>
                                        </div>

                                        {{-- Step-by-Step Flowchart Grid (Fits 100% on screen without slider) --}}
                                        <div class="py-2 w-full">
                                            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-4 xl:grid-cols-4 gap-3 w-full items-stretch">

                                                {{-- Step 1 --}}
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-1 lg:row-start-1">
    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 1</span>
    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Diagnostic Observation</span>
    <span class="hidden lg:block absolute -right-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">➔</span>
</div>

                                                {{-- Step 2 --}}
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-2 lg:row-start-1">
    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 2</span>
    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Play-Based Learning</span>
    <span class="hidden lg:block absolute -right-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">➔</span>
</div>

                                                {{-- Step 3 --}}
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-3 lg:row-start-1">
    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 3</span>
    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Guided Activities</span>
    <span class="hidden lg:block absolute -right-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">➔</span>
</div>

                                                {{-- Step 4 --}}
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-4 lg:row-start-1">
    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 4</span>
    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Early Skill Practice</span>
    <span class="hidden lg:block absolute -bottom-3.5 left-1/2 -translate-x-1/2 z-10 text-gray-300 text-sm font-bold pointer-events-none">↓</span>
</div>

                                                {{-- Step 5 --}}
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-4 lg:row-start-2">
    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 5</span>
    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Progress Assessment</span>
    <span class="hidden lg:block absolute -left-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">←</span>
</div>

                                                {{-- Step 6 --}}
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-3 lg:row-start-2">
    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 6</span>
    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Performance Review</span>
    <span class="hidden lg:block absolute -left-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">←</span>
</div>

                                                {{-- Step 7 --}}
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-2 lg:row-start-2">
    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 7</span>
    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Targeted Support</span>
    <span class="hidden lg:block absolute -left-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">←</span>
</div>

                                                {{-- Step 8 (Goal - Gold/Amber) --}}
                                                <div class="relative bg-gradient-to-br from-amber-400 to-amber-600 rounded-xl text-center shadow-lg shadow-amber-500/30 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.05] transition-all duration-300 group border border-white/20 lg:col-start-1 lg:row-start-2">
    <span class="text-[9px] font-extrabold uppercase tracking-widest text-white/80 mb-0.5 drop-shadow-sm">Goal</span>
    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-white drop-shadow-sm">School Readiness</span>
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
                        <div class="absolute inset-0 z-20 flex items-center justify-center transition-all duration-400 ease-out pointer-events-none" :class="activeProgramme && activeProgramme !== 'kindergarten-education' ? 'opacity-100' : 'opacity-0'">
                             <div class="bg-black/50 backdrop-blur-sm absolute inset-0"></div>
                             <h3 class="text-white font-heading font-black text-xl lg:text-3xl tracking-widest whitespace-nowrap lg:-rotate-90 origin-center drop-shadow-md transform transition-transform duration-400 ease-out" :class="activeProgramme !== 'kindergarten-education' ? 'translate-y-0' : 'translate-y-10'">KINDERGARTEN</h3>
                        </div>
                    </div>


                    {{-- 2. PRIMARY CARD --}}
                    <div 
                        class="group relative rounded-[2rem] overflow-hidden transition-all duration-500 ease-out transform-gpu flex flex-col border border-gray-200"
                        :class="activeProgramme === 'primary-education' 
                            ? 'h-auto lg:h-auto lg:w-[75%] ring-2 ring-emerald-500/40 shadow-2xl border border-slate-700 cursor-default order-first lg:order-none' 
                            : (activeProgramme ? ('h-[120px] lg:h-auto lg:w-[12.5%] opacity-70 hover:opacity-100 grayscale hover:grayscale-0 cursor-pointer hover:shadow-lg ' + (activeProgramme === 'secondary-education' ? 'lg:-ml-3.5' : '')) : 'h-[480px] lg:h-auto lg:w-[33.33%] hover:shadow-2xl hover:-translate-y-2 cursor-pointer')"
                        @click="if(activeProgramme !== 'primary-education') activeProgramme = 'primary-education'"
                    >
                        <!-- Background Image -->
                        <div class="absolute inset-0 z-0 bg-primary-950">
                            <img src="{{ asset('images/primary_prog.png') }}" class="w-full h-full object-cover transition-all duration-500 ease-out transform-gpu" 
                                 :class="activeProgramme === 'primary-education' ? 'scale-105 opacity-40' : (activeProgramme ? 'scale-100 opacity-60' : 'group-hover:scale-110 opacity-100')" alt="Primary Education" />
                            <div class="absolute inset-0 transition-opacity duration-500" :class="activeProgramme === 'primary-education' ? 'bg-gradient-to-r from-primary-950/80 to-transparent' : 'bg-gradient-to-t from-primary-950/95 via-primary-900/40 to-transparent'"></div>
                        </div>

                        <!-- DEFAULT STATE: Front Content -->
                        <div class="z-10 p-6 md:p-8 flex flex-col justify-end h-full transition-all duration-300 ease-out w-full" :class="activeProgramme ? 'opacity-0 pointer-events-none absolute inset-0' : 'opacity-100 relative'">
                            <div class="absolute top-6 right-6 w-14 h-14 rounded-full bg-emerald-500/90 backdrop-blur-md text-white flex items-center justify-center shadow-[0_4px_20px_rgba(52,211,153,0.5)] border border-white/20 transform transition-all duration-500 group-hover:scale-110 group-hover:rotate-12">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                            </div>
                            <div class="transform transition-all duration-500 group-hover:-translate-y-2">
                                <div class="mb-3 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-emerald-400 shadow-[0_0_15px_rgba(52,211,153,0.5)]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-emerald-950" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                                    <span class="text-emerald-950 text-xs font-extrabold uppercase tracking-widest">Year 1 - Year 6</span>
                                </div>
                                <h2 class="font-heading font-extrabold text-3xl md:text-4xl text-white mb-3 leading-tight drop-shadow-lg">Primary<br>Education</h2>
                                <p class="text-white/90 text-sm leading-relaxed mb-6 opacity-0 max-h-0 group-hover:opacity-100 group-hover:max-h-24 transition-all duration-500 ease-in-out">Comprehensive primary curriculum blending rigorous academics with character development and critical thinking.</p>
                            </div>
                            <button @click.stop="toggle('primary-education')" class="w-full relative overflow-hidden group/btn font-semibold text-sm py-3.5 px-6 rounded-xl transition-all duration-300 flex items-center justify-between cursor-pointer border border-white/30 hover:border-white/80 bg-white/10 hover:bg-white/20 backdrop-blur-md">
                                <div class="absolute inset-0 bg-gradient-to-r from-emerald-500 to-emerald-600 opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
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
                                     <div class="mb-3 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-emerald-400 shadow-[0_0_15px_rgba(52,211,153,0.5)]">
                                         <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-emerald-950" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                                         <span class="text-emerald-950 text-xs font-extrabold uppercase tracking-widest">Year 1 - Year 6</span>
                                     </div>
                                     <h2 class="font-heading font-black text-3xl sm:text-4xl lg:text-5xl text-white mb-4 leading-tight drop-shadow-xl">Primary Education</h2>
                                     <p class="text-white/90 text-sm sm:text-base leading-relaxed drop-shadow-md hidden sm:block">Empowering students through KSSR and UASA readiness, building a robust academic foundation.</p>
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
                            <div class="flex flex-col mb-6">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 w-full lg:w-[580px] shrink-0">
                                    {{-- Year 1-2 Card --}}
                                    <button 
    @click="activeYear = activeYear === 'year-1-2' ? null : 'year-1-2'" 
    class="relative overflow-hidden rounded-2xl p-4 transition-all duration-300 flex flex-col justify-center min-w-[160px] text-left cursor-pointer border group"
    :class="activeYear === 'year-1-2' ? 'bg-slate-900 border-slate-900 text-white shadow-xl scale-[1.02]' : 'bg-slate-50/80 border-slate-200/80 hover:bg-white hover:border-slate-300 hover:shadow-md text-slate-800'"
>
    <div class="absolute inset-0 bg-gradient-to-br from-emerald-400 to-emerald-500 opacity-0 transition-opacity duration-300" :class="activeYear === 'year-1-2' ? 'opacity-100' : ''"></div>
    <div class="relative z-10 flex items-center justify-between w-full mb-2">
        <span class="inline-block text-[11px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider transition-colors duration-300 shadow-sm" :class="activeYear === 'year-1-2' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-700'">Year 1 - 2</span>
        <svg class="w-3.5 h-3.5 transition-transform duration-300" :class="activeYear === 'year-1-2' ? 'rotate-180 text-white' : 'text-gray-300 group-hover:text-emerald-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
    </div>
    <h4 class="relative z-10 font-heading font-extrabold text-sm sm:text-base leading-tight transition-colors duration-300 whitespace-nowrap" :class="activeYear === 'year-1-2' ? 'text-white' : 'text-gray-800 group-hover:text-slate-900'">Early Primary</h4>
    <p class="relative z-10 text-[11px] font-semibold mt-1 transition-colors duration-300" :class="activeYear === 'year-1-2' ? 'text-slate-400' : 'text-gray-400'">Programme</p>
</button>

                                    {{-- Year 3-4 Card --}}
                                    <button 
    @click="activeYear = activeYear === 'year-3-4' ? null : 'year-3-4'" 
    class="relative overflow-hidden rounded-2xl p-4 transition-all duration-300 flex flex-col justify-center min-w-[160px] text-left cursor-pointer border group"
    :class="activeYear === 'year-3-4' ? 'bg-slate-900 border-slate-900 text-white shadow-xl scale-[1.02]' : 'bg-slate-50/80 border-slate-200/80 hover:bg-white hover:border-slate-300 hover:shadow-md text-slate-800'"
>
    <div class="absolute inset-0 bg-gradient-to-br from-emerald-400 to-emerald-500 opacity-0 transition-opacity duration-300" :class="activeYear === 'year-3-4' ? 'opacity-100' : ''"></div>
    <div class="relative z-10 flex items-center justify-between w-full mb-2">
        <span class="inline-block text-[11px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider transition-colors duration-300 shadow-sm" :class="activeYear === 'year-3-4' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-700'">Year 3 - 4</span>
        <svg class="w-3.5 h-3.5 transition-transform duration-300" :class="activeYear === 'year-3-4' ? 'rotate-180 text-white' : 'text-gray-300 group-hover:text-emerald-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
    </div>
    <h4 class="relative z-10 font-heading font-extrabold text-sm sm:text-base leading-tight transition-colors duration-300 whitespace-nowrap" :class="activeYear === 'year-3-4' ? 'text-white' : 'text-gray-800 group-hover:text-slate-900'">Core Building</h4>
    <p class="relative z-10 text-[11px] font-semibold mt-1 transition-colors duration-300" :class="activeYear === 'year-3-4' ? 'text-slate-400' : 'text-gray-400'">Programme</p>
</button>

                                    {{-- Year 5-6 Card --}}
                                    <button 
    @click="activeYear = activeYear === 'year-5-6' ? null : 'year-5-6'" 
    class="relative overflow-hidden rounded-2xl p-4 transition-all duration-300 flex flex-col justify-center min-w-[160px] text-left cursor-pointer border group"
    :class="activeYear === 'year-5-6' ? 'bg-slate-900 border-slate-900 text-white shadow-xl scale-[1.02]' : 'bg-slate-50/80 border-slate-200/80 hover:bg-white hover:border-slate-300 hover:shadow-md text-slate-800'"
>
    <div class="absolute inset-0 bg-gradient-to-br from-emerald-400 to-emerald-500 opacity-0 transition-opacity duration-300" :class="activeYear === 'year-5-6' ? 'opacity-100' : ''"></div>
    <div class="relative z-10 flex items-center justify-between w-full mb-2">
        <span class="inline-block text-[11px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider transition-colors duration-300 shadow-sm" :class="activeYear === 'year-5-6' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-700'">Year 5 - 6</span>
        <svg class="w-3.5 h-3.5 transition-transform duration-300" :class="activeYear === 'year-5-6' ? 'rotate-180 text-white' : 'text-gray-300 group-hover:text-emerald-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
    </div>
    <h4 class="relative z-10 font-heading font-extrabold text-sm sm:text-base leading-tight transition-colors duration-300 whitespace-nowrap" :class="activeYear === 'year-5-6' ? 'text-white' : 'text-gray-800 group-hover:text-slate-900'">UASA Readiness</h4>
    <p class="relative z-10 text-[11px] font-semibold mt-1 transition-colors duration-300" :class="activeYear === 'year-5-6' ? 'text-slate-400' : 'text-gray-400'">Programme</p>
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
                                    <div class="bg-gradient-to-br from-emerald-600 via-emerald-700 to-emerald-800 text-white rounded-2xl p-5 md:p-6 shadow-xl border border-emerald-400/30 relative">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="w-full">
                                                {{-- Year 1-2 Content --}}
                                                <template x-if="activeYear === 'year-1-2'">
                                                    <div class="w-full">
                                                        <ul class="space-y-2 text-xs md:text-sm text-emerald-50 font-medium">
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-emerald-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Bahasa Melayu</strong> — Develop reading, vocabulary, comprehension and basic writing skills.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-emerald-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">English Language</strong> — Strengthen phonics, reading, vocabulary and simple sentence building.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-emerald-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Mathematics</strong> — Build number sense, basic operations, shapes and early problem-solving skills.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-emerald-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Science Discovery</strong> — Explore basic science concepts through observation, inquiry and guided activities.
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </template>

                                                {{-- Year 3-4 Content --}}
                                                <template x-if="activeYear === 'year-3-4'">
                                                    <div class="flex flex-col md:flex-row items-start justify-between gap-6 w-full">
                                                        <ul class="space-y-2 text-xs md:text-sm text-emerald-50 font-medium flex-1">
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-emerald-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Bahasa Melayu</strong> — Strengthen reading comprehension, grammar, vocabulary and structured writing skills.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-emerald-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">English Language</strong> — Build reading fluency, vocabulary, sentence construction and communication confidence.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-emerald-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Mathematics</strong> — Develop problem-solving, multiplication, division, fractions, shapes and logical thinking.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-emerald-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Science</strong> — Deepen understanding of scientific concepts through inquiry, observation and practical learning.
                                                                </div>
                                                            </li>
                                                        </ul>
                                                        
                                                        <div class="shrink-0 relative mt-6 md:mt-0 lg:ml-4 self-center">
                                                            <div class="relative flex flex-col items-center justify-center pt-7 pb-4 px-4 rounded-[1.25rem] bg-[#093F28] shadow-[0_10px_25px_-5px_rgba(0,0,0,0.4)] w-full md:w-44 transform rotate-2 hover:rotate-0 hover:scale-105 transition-all duration-300 ease-out cursor-pointer">
                                                                <div class="absolute inset-1.5 border border-dashed border-amber-400/80 rounded-[0.95rem] pointer-events-none"></div>
                                                                <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-amber-400 text-[#093F28] font-black text-[9px] px-3.5 py-1 rounded-full uppercase tracking-[0.18em] whitespace-nowrap shadow-md z-10">
                                                                    Official Syllabus
                                                                </div>
                                                                <div class="text-3xl font-heading font-black text-amber-400 leading-none mt-1 drop-shadow-md tracking-tight z-10">MPT4</div>
                                                                <div class="text-[8.5px] font-extrabold text-white uppercase text-center mt-2 leading-[1.35] tracking-widest z-10">Matriks Pembelajaran<br>Tahun 4</div>
                                                                <div class="w-3/5 h-px bg-amber-400/30 my-2.5 z-10"></div>
                                                                <div class="flex items-center justify-center gap-1 text-[8.5px] font-black text-amber-400 tracking-widest uppercase z-10">
                                                                    <svg class="w-2.5 h-2.5 fill-current shrink-0" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                                                    <span>KSSR Semakan</span>
                                                                    <svg class="w-2.5 h-2.5 fill-current shrink-0" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>

                                                {{-- Year 5-6 Content --}}
                                                <template x-if="activeYear === 'year-5-6'">
                                                    <div class="flex flex-col md:flex-row items-start justify-between gap-6 w-full">
                                                        <ul class="space-y-2 text-xs md:text-sm text-emerald-50 font-medium flex-1">
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-emerald-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Bahasa Melayu</strong> — Strengthen comprehension, grammar, structured writing and confident language use.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-emerald-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">English Language</strong> — Enhance reading fluency, vocabulary, writing skills and effective communication.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-emerald-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Mathematics</strong> — Master operations, fractions, problem-solving, logical reasoning and applied thinking.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-emerald-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Science</strong> — Build deeper conceptual understanding through inquiry, analysis and practical learning.
                                                                </div>
                                                            </li>
                                                        </ul>

                                                        <div class="shrink-0 relative mt-6 md:mt-0 lg:ml-4 self-center">
                                                            <div class="relative flex flex-col items-center justify-center pt-7 pb-4 px-4 rounded-[1.25rem] bg-[#093F28] shadow-[0_10px_25px_-5px_rgba(0,0,0,0.4)] w-full md:w-44 transform -rotate-2 hover:rotate-0 hover:scale-105 transition-all duration-300 ease-out cursor-pointer">
                                                                <div class="absolute inset-1.5 border border-dashed border-amber-400/80 rounded-[0.95rem] pointer-events-none"></div>
                                                                <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-amber-400 text-[#093F28] font-black text-[9px] px-3.5 py-1 rounded-full uppercase tracking-[0.18em] whitespace-nowrap shadow-md z-10">
                                                                    Exam Readiness
                                                                </div>
                                                                <div class="text-3xl font-heading font-black text-amber-400 leading-none mt-1 drop-shadow-md tracking-tight z-10">UASA</div>
                                                                <div class="text-[8.5px] font-extrabold text-white uppercase text-center mt-2 leading-[1.35] tracking-widest z-10">Ujian Akhir Sesi<br>Akademik</div>
                                                                <div class="w-3/5 h-px bg-amber-400/30 my-2.5 z-10"></div>
                                                                <div class="flex items-center justify-center gap-1 text-[8.5px] font-black text-amber-400 tracking-widest uppercase z-10">
                                                                    <svg class="w-2.5 h-2.5 fill-current shrink-0" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                                                    <span class="text-center">Year 5 & 6 Mastery</span>
                                                                    <svg class="w-2.5 h-2.5 fill-current shrink-0" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                                                </div>
                                                            </div>
                                                        </div>
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

                            {{-- Main Curriculum Column --}}
                            <div class="w-full mb-4">

                            {{-- Main Primary Education Flowchart Process Section --}}
                            <div class="w-full mb-4">
                                <div class="rounded-3xl p-6 md:p-8 border-2 shadow-sm flex flex-col justify-between bg-emerald-50/70 border-emerald-200">
                                    <div>
                                        <div class="flex flex-wrap items-center justify-between gap-4 mb-6 pb-4 border-b border-emerald-200/70">
                                            <div>
                                                <h3 class="font-heading font-black text-xl md:text-2xl text-emerald-950">PRIMARY LEARNING PATHWAY (KSSR & UASA)</h3>
                                                <p class="text-xs md:text-sm font-semibold text-emerald-700">8-Step Continuous Concept & Exam Mastery Pathway</p>
                                            </div>
                                            <span class="text-white text-xs font-bold px-3.5 py-1.5 rounded-full uppercase tracking-wider shadow-sm bg-emerald-600">Primary Flowchart</span>
                                        </div>

                                        {{-- Step-by-Step Flowchart Grid (Fits 100% on screen without slider) --}}
                                        <div class="py-2 w-full">
                                            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-4 xl:grid-cols-4 gap-3 w-full items-stretch">

                                                {{-- Step 1 --}}
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-1 lg:row-start-1">
    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 1</span>
    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Diagnostic Evaluation</span>
    <span class="hidden lg:block absolute -right-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">➔</span>
</div>

                                                {{-- Step 2 --}}
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-2 lg:row-start-1">
    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 2</span>
    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Concept Foundation</span>
    <span class="hidden lg:block absolute -right-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">➔</span>
</div>

                                                {{-- Step 3 --}}
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-3 lg:row-start-1">
    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 3</span>
    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Guided Practice</span>
    <span class="hidden lg:block absolute -right-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">➔</span>
</div>

                                                {{-- Step 4 --}}
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-4 lg:row-start-1">
    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 4</span>
    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Skill Consolidation</span>
    <span class="hidden lg:block absolute -bottom-3.5 left-1/2 -translate-x-1/2 z-10 text-gray-300 text-sm font-bold pointer-events-none">↓</span>
</div>

                                                {{-- Step 5 --}}
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-4 lg:row-start-2">
    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 5</span>
    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Progress Tracking</span>
    <span class="hidden lg:block absolute -left-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">←</span>
</div>

                                                {{-- Step 6 --}}
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-3 lg:row-start-2">
    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 6</span>
    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Exam Answering Skills</span>
    <span class="hidden lg:block absolute -left-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">←</span>
</div>

                                                {{-- Step 7 --}}
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-2 lg:row-start-2">
    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 7</span>
    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Targeted Intervention</span>
    <span class="hidden lg:block absolute -left-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">←</span>
</div>

                                                {{-- Step 8 (Goal - Emerald / Green) --}}
                                                <div class="relative bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-xl text-center shadow-lg shadow-emerald-500/30 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.05] transition-all duration-300 group border border-white/20 lg:col-start-1 lg:row-start-2">
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
                        </div>
                        
                        <!-- SLIM TAB VERTICAL TEXT -->
                        <div class="absolute inset-0 z-20 flex items-center justify-center transition-all duration-400 ease-out pointer-events-none" :class="activeProgramme && activeProgramme !== 'primary-education' ? 'opacity-100' : 'opacity-0'">
                             <div class="bg-black/50 backdrop-blur-sm absolute inset-0"></div>
                             <h3 class="text-white font-heading font-black text-xl lg:text-3xl tracking-widest whitespace-nowrap lg:-rotate-90 origin-center drop-shadow-md transform transition-transform duration-400 ease-out" :class="activeProgramme !== 'primary-education' ? 'translate-y-0' : 'translate-y-10'">PRIMARY</h3>
                        </div>
                    </div>

                    {{-- 3. SECONDARY CARD --}}
                    <div 
                        class="group relative rounded-[2rem] overflow-hidden transition-all duration-500 ease-out transform-gpu flex flex-col border border-gray-200"
                        :class="activeProgramme === 'secondary-education' 
                            ? 'h-auto lg:h-auto lg:w-[75%] ring-2 ring-blue-500/40 shadow-2xl border border-slate-700 cursor-default order-first lg:order-none' 
                            : (activeProgramme ? ('h-[120px] lg:h-auto lg:w-[12.5%] opacity-70 hover:opacity-100 grayscale hover:grayscale-0 cursor-pointer hover:shadow-lg ' + (activeProgramme === 'kindergarten-education' ? 'lg:-ml-3.5' : '')) : 'h-[480px] lg:h-auto lg:w-[33.33%] hover:shadow-2xl hover:-translate-y-2 cursor-pointer')"
                        @click="if(activeProgramme !== 'secondary-education') activeProgramme = 'secondary-education'"
                    >
                        <!-- Background Image -->
                        <div class="absolute inset-0 z-0 bg-primary-950">
                            <img src="{{ asset('images/secondary_prog.png') }}" class="w-full h-full object-cover transition-all duration-500 ease-out transform-gpu" 
                                 :class="activeProgramme === 'secondary-education' ? 'scale-105 opacity-40' : (activeProgramme ? 'scale-100 opacity-60' : 'group-hover:scale-110 opacity-100')" alt="Secondary Education" />
                            <div class="absolute inset-0 transition-opacity duration-500" :class="activeProgramme === 'secondary-education' ? 'bg-gradient-to-r from-primary-950/80 to-transparent' : 'bg-gradient-to-t from-primary-950/95 via-primary-900/40 to-transparent'"></div>
                        </div>

                        <!-- DEFAULT STATE: Front Content -->
                        <div class="z-10 p-6 md:p-8 flex flex-col justify-end h-full transition-all duration-300 ease-out w-full" :class="activeProgramme ? 'opacity-0 pointer-events-none absolute inset-0' : 'opacity-100 relative'">
                            <div class="absolute top-6 right-6 w-14 h-14 rounded-full bg-blue-500/90 backdrop-blur-md text-white flex items-center justify-center shadow-[0_4px_20px_rgba(96,165,250,0.5)] border border-white/20 transform transition-all duration-500 group-hover:scale-110 group-hover:rotate-12">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" /></svg>
                            </div>
                            <div class="transform transition-all duration-500 group-hover:-translate-y-2">
                                <div class="mb-3 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-blue-400 shadow-[0_0_15px_rgba(96,165,250,0.5)]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-blue-950" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                                    <span class="text-blue-950 text-xs font-extrabold uppercase tracking-widest">Form 1 - Form 5</span>
                                </div>
                                <h2 class="font-heading font-extrabold text-3xl md:text-4xl text-white mb-3 leading-tight drop-shadow-lg">Secondary<br>Education</h2>
                                <p class="text-white/90 text-sm leading-relaxed mb-6 opacity-0 max-h-0 group-hover:opacity-100 group-hover:max-h-24 transition-all duration-500 ease-in-out">Advanced preparation for SPM and IGCSE focusing on mastery, problem solving and exam excellence.</p>
                            </div>
                            <button @click.stop="toggle('secondary-education')" class="w-full relative overflow-hidden group/btn font-semibold text-sm py-3.5 px-6 rounded-xl transition-all duration-300 flex items-center justify-between cursor-pointer border border-white/30 hover:border-white/80 bg-white/10 hover:bg-white/20 backdrop-blur-md">
                                <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-blue-600 opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <span class="relative z-10 text-white flex items-center gap-2">Explore Programme</span>
                                <div class="relative z-10 w-8 h-8 rounded-full bg-white/20 flex items-center justify-center transition-transform duration-300 group-hover/btn:translate-x-1"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg></div>
                            </button>
                        </div>

                        <!-- EXPANDED STATE: Detailed Content -->
                        <div class="z-30 flex flex-col transition-all duration-400 delay-75 ease-out transform-gpu" 
                             :class="activeProgramme === 'secondary-education' ? 'relative opacity-100 translate-y-0 lg:translate-x-0' : 'absolute inset-0 opacity-0 translate-y-12 lg:translate-y-0 lg:translate-x-24 pointer-events-none'">
                             
                             <!-- Top Intro Row -->
                             <div class="w-full flex flex-row items-center justify-between p-6 md:p-8 lg:px-12 lg:pt-10 shrink-0 relative z-10 h-auto lg:h-[220px]">
                                 <div>
                                     <div class="mb-3 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-blue-400 shadow-[0_0_15px_rgba(96,165,250,0.5)]">
                                         <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-blue-950" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                                         <span class="text-blue-950 text-xs font-extrabold uppercase tracking-widest">Form 1 - Form 5</span>
                                     </div>
                                     <h2 class="font-heading font-black text-3xl sm:text-4xl lg:text-5xl text-white mb-4 leading-tight drop-shadow-xl">Secondary Education</h2>
                                     <p class="text-white/90 text-sm sm:text-base leading-relaxed drop-shadow-md hidden sm:block">Intensive track for SPM & IGCSE excellence, focusing on KBAT, core concepts, and high-scoring exam techniques.</p>
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
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 w-full lg:w-[580px] shrink-0">
                                    {{-- Lower Secondary Card --}}
                                    <button 
    @click="activeSecTrack = activeSecTrack === 'lower-sec' ? null : 'lower-sec'" 
    class="relative overflow-hidden rounded-2xl p-4 transition-all duration-300 flex flex-col justify-center min-w-[160px] text-left cursor-pointer border group"
    :class="activeSecTrack === 'lower-sec' ? 'bg-slate-900 border-slate-900 text-white shadow-xl scale-[1.02]' : 'bg-slate-50/80 border-slate-200/80 hover:bg-white hover:border-slate-300 hover:shadow-md text-slate-800'"
>
    <div class="absolute inset-0 bg-gradient-to-br from-blue-400 to-blue-500 opacity-0 transition-opacity duration-300" :class="activeSecTrack === 'lower-sec' ? 'opacity-100' : ''"></div>
    <div class="relative z-10 flex items-center justify-between w-full mb-2">
        <span class="inline-block text-[11px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider transition-colors duration-300 shadow-sm" :class="activeSecTrack === 'lower-sec' ? 'bg-white/20 text-white' : 'bg-blue-100 text-blue-700'">Form 1 – 3</span>
        <svg class="w-3.5 h-3.5 transition-transform duration-300" :class="activeSecTrack === 'lower-sec' ? 'rotate-180 text-white' : 'text-gray-300 group-hover:text-blue-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
    </div>
    <h4 class="relative z-10 font-heading font-extrabold text-sm sm:text-base leading-tight transition-colors duration-300 whitespace-nowrap" :class="activeSecTrack === 'lower-sec' ? 'text-white' : 'text-gray-800 group-hover:text-slate-900'">Lower Secondary Mastery</h4>
    <p class="relative z-10 text-[11px] font-semibold mt-1 transition-colors duration-300" :class="activeSecTrack === 'lower-sec' ? 'text-slate-400' : 'text-gray-400'">KSSM Foundation Track</p>
</button>

                                    {{-- Upper Secondary Card --}}
                                    <button 
    @click="activeSecTrack = activeSecTrack === 'upper-sec' ? null : 'upper-sec'" 
    class="relative overflow-hidden rounded-2xl p-4 transition-all duration-300 flex flex-col justify-center min-w-[160px] text-left cursor-pointer border group"
    :class="activeSecTrack === 'upper-sec' ? 'bg-slate-900 border-slate-900 text-white shadow-xl scale-[1.02]' : 'bg-slate-50/80 border-slate-200/80 hover:bg-white hover:border-slate-300 hover:shadow-md text-slate-800'"
>
    <div class="absolute inset-0 bg-gradient-to-br from-blue-400 to-blue-500 opacity-0 transition-opacity duration-300" :class="activeSecTrack === 'upper-sec' ? 'opacity-100' : ''"></div>
    <div class="relative z-10 flex items-center justify-between w-full mb-2">
        <span class="inline-block text-[11px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider transition-colors duration-300 shadow-sm" :class="activeSecTrack === 'upper-sec' ? 'bg-white/20 text-white' : 'bg-blue-100 text-blue-700'">Form 4 – 5</span>
        <svg class="w-3.5 h-3.5 transition-transform duration-300" :class="activeSecTrack === 'upper-sec' ? 'rotate-180 text-white' : 'text-gray-300 group-hover:text-blue-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
    </div>
    <h4 class="relative z-10 font-heading font-extrabold text-sm sm:text-base leading-tight transition-colors duration-300 whitespace-nowrap" :class="activeSecTrack === 'upper-sec' ? 'text-white' : 'text-gray-800 group-hover:text-slate-900'">SPM & IGCSE Excellence</h4>
    <p class="relative z-10 text-[11px] font-semibold mt-1 transition-colors duration-300" :class="activeSecTrack === 'upper-sec' ? 'text-slate-400' : 'text-gray-400'">Exam Intensive Track</p>
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
                                                {{-- Form 1-3 Content --}}
                                                <template x-if="activeSecTrack === 'lower-sec'">
                                                    <div>
                                                        <ul class="space-y-2 text-xs md:text-sm text-blue-50 font-medium">
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-blue-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Bahasa Melayu & English</strong> — Master KSSM Karangan, Tatabahasa, literature (KOMSAS), and CEFR listening & speaking.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-blue-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Mathematics</strong> — Build solid fundamentals in Algebra, Linear Equations, Geometry, Statistics, and Pythagoras Theorem.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-blue-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Science</strong> — Explore Biology, Chemistry, and Physics foundations through inquiry-based learning and lab principles.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-blue-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">KSSM Assessment Readiness</strong> — Structured school exam preparation, KBAT problem-solving skills, and time management.
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </template>

                                                {{-- Form 4-5 Content --}}
                                                <template x-if="activeSecTrack === 'upper-sec'">
                                                    <div>
                                                        <ul class="space-y-2 text-xs md:text-sm text-blue-50 font-medium">
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-blue-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Additional Mathematics & Maths</strong> — Master Calculus, Trigonometry, Vectors, Functions, and high-scoring KBAT techniques.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-blue-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Pure Sciences (Phys / Chem / Bio)</strong> — Intensive topic mastery, Section B/C essay strategies, and experimental paper techniques.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-blue-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Bahasa Melayu & English CEFR</strong> — High-scoring Karangan SPM guidelines, Ujian Lisan/Mendengar, and CEFR C1 essay writing.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-blue-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">SPM & IGCSE Exam Drilling</strong> — Past-year paper revisions, marking scheme breakdowns, model paper drills, and time strategy.
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

                            {{-- Main Curriculum Column --}}
                            <div class="w-full mb-4">

                            {{-- Main Secondary Education Flowchart Process Section --}}
                            <div class="w-full mb-4">
                                <div class="rounded-3xl p-6 md:p-8 border-2 shadow-sm flex flex-col justify-between bg-blue-50/70 border-blue-200">
                                    <div>
                                        <div class="flex flex-wrap items-center justify-between gap-4 mb-6 pb-4 border-b border-blue-200/70">
                                            <div>
                                                <h3 class="font-heading font-black text-xl md:text-2xl text-blue-950">SECONDARY LEARNING PATHWAY (KSSM, SPM & IGCSE)</h3>
                                                <p class="text-xs md:text-sm font-semibold text-blue-700">8-Step Exam Excellence & KBAT Mastery Pathway</p>
                                            </div>
                                            <span class="text-white text-xs font-bold px-3.5 py-1.5 rounded-full uppercase tracking-wider shadow-sm bg-blue-600">Secondary Flowchart</span>
                                        </div>

                                        {{-- Step-by-Step Flowchart Grid (Fits 100% on screen without slider) --}}
                                        <div class="py-2 w-full">
                                            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-4 xl:grid-cols-4 gap-3 w-full items-stretch">

                                                {{-- Step 1 --}}
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-1 lg:row-start-1">
    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 1</span>
    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Diagnostic Assessment</span>
    <span class="hidden lg:block absolute -right-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">➔</span>
</div>

                                                {{-- Step 2 --}}
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-2 lg:row-start-1">
    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 2</span>
    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Core Concept Mastery</span>
    <span class="hidden lg:block absolute -right-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">➔</span>
</div>

                                                {{-- Step 3 --}}
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-3 lg:row-start-1">
    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 3</span>
    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">KBAT Problem Solving</span>
    <span class="hidden lg:block absolute -right-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">➔</span>
</div>

                                                {{-- Step 4 --}}
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-4 lg:row-start-1">
    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 4</span>
    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Structured Practice</span>
    <span class="hidden lg:block absolute -bottom-3.5 left-1/2 -translate-x-1/2 z-10 text-gray-300 text-sm font-bold pointer-events-none">↓</span>
</div>

                                                {{-- Step 5 --}}
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-4 lg:row-start-2">
    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 5</span>
    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Performance Tracking</span>
    <span class="hidden lg:block absolute -left-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">←</span>
</div>

                                                {{-- Step 6 --}}
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-3 lg:row-start-2">
    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 6</span>
    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Exam Paper Answering</span>
    <span class="hidden lg:block absolute -left-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">←</span>
</div>

                                                {{-- Step 7 --}}
                                                <div class="relative bg-white rounded-xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] hover:shadow-[0_8px_25px_rgba(0,0,0,0.08)] transition-all duration-300 group hover:border-gray-200 lg:col-start-2 lg:row-start-2">
    <span class="text-[9px] font-extrabold uppercase tracking-widest text-gray-400 mb-0.5">Step 7</span>
    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-gray-800 group-hover:text-gray-900 transition-colors">Targeted Revision</span>
    <span class="hidden lg:block absolute -left-3 top-1/2 -translate-y-1/2 z-10 text-gray-300 text-sm font-light pointer-events-none">←</span>
</div>

                                                {{-- Step 8 (Goal - Blue) --}}
                                                <div class="relative bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl text-center shadow-lg shadow-blue-500/30 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.05] transition-all duration-300 group border border-white/20 lg:col-start-1 lg:row-start-2">
    <span class="text-[9px] font-extrabold uppercase tracking-widest text-white/80 mb-0.5 drop-shadow-sm">Goal</span>
    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-white drop-shadow-sm">SPM & IGCSE A* Goal</span>
</div>

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
                        <div class="absolute inset-0 z-20 flex items-center justify-center transition-all duration-400 ease-out pointer-events-none" :class="activeProgramme && activeProgramme !== 'secondary-education' ? 'opacity-100' : 'opacity-0'">
                             <div class="bg-black/50 backdrop-blur-sm absolute inset-0"></div>
                             <h3 class="text-white font-heading font-black text-xl lg:text-3xl tracking-widest whitespace-nowrap lg:-rotate-90 origin-center drop-shadow-md transform transition-transform duration-400 ease-out" :class="activeProgramme !== 'secondary-education' ? 'translate-y-0' : 'translate-y-10'">SECONDARY</h3>
                        </div>
                    </div>


                </div>
            </div>
        </section>
    </div>
</x-app-layout>
