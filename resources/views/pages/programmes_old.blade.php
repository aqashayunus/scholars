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
            <div class="relative max-w-4xl mx-auto px-4">
                <h1 class="font-heading font-bold text-3xl md:text-4xl text-white mb-2">Our Programmes</h1>
                <p class="text-white/80 text-sm md:text-base">
                    Choose the right learning pathway for your child.
                </p>
            </div>

            {{-- Smooth Curved Divider --}}
            <div class="absolute -bottom-1 left-0 right-0 overflow-hidden leading-none pointer-events-none z-10">
                <svg viewBox="0 0 1440 60" fill="none" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-8 md:h-10 block">
                    <path d="M0,20 Q720,75 1440,20 L1440,60 L0,60 Z" fill="white"></path>
                </svg>
            </div>
        </section>

        {{-- ══════════════════════════════════════════════
             SECTION 2: 3 MAIN PROGRAMME CARDS (KINDERGARTEN, PRIMARY, SECONDARY)
        ══════════════════════════════════════════════ --}}
        <section class="py-16 bg-white">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
                    
                    {{-- 1. Kindergarten Education --}}
                    <div class="group rounded-3xl p-6 border-2 transition-all duration-500 ease-out flex flex-col justify-between shadow-sm hover:shadow-xl relative" :class="activeProgramme === 'kindergarten-education' ? 'ring-4 ring-amber-400/80 scale-[1.03] shadow-lg' : 'hover:-translate-y-1'" style="border-color: #fde68a; background-color: #fffbe6;">
                        <div>
                            <div class="w-16 h-16 rounded-2xl text-white flex items-center justify-center mx-auto mb-5 shadow-lg group-hover:scale-110 transition-transform duration-300" style="background-color: #f59e0b;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h2 class="font-heading font-bold text-xl text-center mb-2" style="color: #92400e;">Kindergarten Education</h2>
                            <p class="text-xs text-center font-semibold mb-3 uppercase tracking-wide" style="color: #d97706;">Ages 4 – Ages 6</p>
                            <p class="text-gray-600 text-sm text-center leading-relaxed mb-6">Interactive early learning building phonics, literacy, numbers & curiosity.</p>
                        </div>
                        <button 
                            @click="toggle('kindergarten-education')" 
                            class="w-full text-center font-semibold text-sm py-3 px-4 rounded-xl transition-all duration-300 shadow-md flex items-center justify-center gap-2 cursor-pointer hover:opacity-95 active:scale-95" 
                            style="background-color: #f59e0b; color: #ffffff;"
                        >
                            <span x-text="activeProgramme === 'kindergarten-education' ? 'Hide Details' : 'Explore Programmes'">Explore Programmes</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 transition-transform duration-500 ease-in-out" :class="activeProgramme === 'kindergarten-education' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                    </div>

                    {{-- 2. Primary Education --}}
                    <div class="group rounded-3xl p-6 border-2 transition-all duration-500 ease-out flex flex-col justify-between shadow-sm hover:shadow-xl relative" :class="activeProgramme === 'primary-education' ? 'ring-4 ring-emerald-400/80 scale-[1.03] shadow-lg' : 'hover:-translate-y-1'" style="border-color: #a7f3d0; background-color: #f0fdf4;">
                        <div>
                            <div class="w-16 h-16 rounded-2xl text-white flex items-center justify-center mx-auto mb-5 shadow-lg group-hover:scale-110 transition-transform duration-300" style="background-color: #059669;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>
                            <h2 class="font-heading font-bold text-xl text-center mb-2" style="color: #065f46;">Primary Education</h2>
                            <p class="text-xs text-center font-semibold mb-3 uppercase tracking-wide" style="color: #047857;">Year 1 - Year 6</p>
                            <p class="text-gray-600 text-sm text-center leading-relaxed mb-6">Strong foundations for young learners in key core subjects.</p>
                        </div>
                        <button 
                            @click="toggle('primary-education')" 
                            class="w-full text-center font-semibold text-sm py-3 px-4 rounded-xl transition-all duration-300 shadow-md flex items-center justify-center gap-2 cursor-pointer hover:opacity-95 active:scale-95" 
                            style="background-color: #059669; color: #ffffff;"
                        >
                            <span x-text="activeProgramme === 'primary-education' ? 'Hide Details' : 'Explore Programmes'">Explore Programmes</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 transition-transform duration-500 ease-in-out" :class="activeProgramme === 'primary-education' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                    </div>

                    {{-- 3. Secondary Education --}}
                    <div class="group rounded-3xl p-6 border-2 transition-all duration-500 ease-out flex flex-col justify-between shadow-sm hover:shadow-xl relative" :class="activeProgramme === 'secondary-education' ? 'ring-4 ring-blue-400/80 scale-[1.03] shadow-lg' : 'hover:-translate-y-1'" style="border-color: #bfdbfe; background-color: #eff6ff;">
                        <div>
                            <div class="w-16 h-16 rounded-2xl text-white flex items-center justify-center mx-auto mb-5 shadow-lg group-hover:scale-110 transition-transform duration-300" style="background-color: #2563eb;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path>
                                </svg>
                            </div>
                            <h2 class="font-heading font-bold text-xl text-center mb-2" style="color: #1e3a8a;">Secondary Education</h2>
                            <p class="text-xs text-center font-semibold mb-3 uppercase tracking-wide" style="color: #1d4ed8;">Form 1 - Form 6</p>
                            <p class="text-gray-600 text-sm text-center leading-relaxed mb-6">Comprehensive preparation for SPM and IGCSE excellence.</p>
                        </div>
                        <button 
                            @click="toggle('secondary-education')" 
                            class="w-full text-center font-semibold text-sm py-3 px-4 rounded-xl transition-all duration-300 shadow-md flex items-center justify-center gap-2 cursor-pointer hover:opacity-95 active:scale-95" 
                            style="background-color: #2563eb; color: #ffffff;"
                        >
                            <span x-text="activeProgramme === 'secondary-education' ? 'Hide Details' : 'Explore Programmes'">Explore Programmes</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 transition-transform duration-500 ease-in-out" :class="activeProgramme === 'secondary-education' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                    </div>

                </div>
            </div>
        </section>

        {{-- ══════════════════════════════════════════════
             SECTION 3: DETAILED PROGRAMME BREAKDOWN (SMOOTH CROSS-FADE STACK)
        ══════════════════════════════════════════════ --}}
        <div id="programme-breakdown-section" class="grid transition-all duration-500 ease-in-out bg-surface overflow-hidden"
             :class="activeProgramme !== null ? 'grid-rows-[1fr] opacity-100 py-16 border-t border-gray-100' : 'grid-rows-[0fr] opacity-0 py-0 border-t-0 pointer-events-none'">
            <div class="overflow-hidden min-h-0">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    
                    {{-- OVERLAPPING GRID CONTAINER FOR STABLE CROSS-FADE --}}
                    <div class="grid grid-cols-1 grid-rows-1 relative items-start">

                        {{-- 0. KINDERGARTEN EDUCATION BREAKDOWN --}}
                        <div 
                            x-show="activeProgramme === 'kindergarten-education'"
                            x-data="{ activeAge: null }"
                            x-transition:enter="transition-all ease-out duration-400"
                            x-transition:enter-start="opacity-0 scale-[0.98] translate-y-2"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition-all ease-in duration-300"
                            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                            x-transition:leave-end="opacity-0 scale-[0.98] -translate-y-2"
                            id="kindergarten-education" 
                            class="col-start-1 row-start-1 w-full bg-white rounded-3xl p-6 md:p-10 border-2 border-amber-200 shadow-2xl"
                        >
                            {{-- Header Bar --}}
                            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-8 pb-6 border-b border-gray-200">
                                <div class="flex items-center gap-4 md:gap-5">
                                    <div class="w-14 h-14 md:w-16 md:h-16 rounded-2xl text-white flex items-center justify-center shadow-lg flex-shrink-0" style="background-color: #f59e0b; color: #ffffff;">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 md:w-8 md:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="font-heading font-black text-2xl md:text-3xl lg:text-4xl tracking-tight text-gray-900">KINDERGARTEN EDUCATION</h2>
                                        <p class="font-extrabold text-sm md:text-lg text-amber-600">Ages 4 – Ages 6</p>
                                        <p class="text-gray-500 text-xs md:text-sm font-medium">Click any card to view detailed stage description.</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 w-full lg:w-[580px] shrink-0">
                                    {{-- Age 4 Card --}}
                                    <button 
                                        @click="activeAge = activeAge === 'age-4' ? null : 'age-4'" 
                                        class="bg-amber-50/90 border-2 rounded-2xl p-4 shadow-sm hover:shadow-md transition-all flex flex-col justify-center min-w-[160px] text-left cursor-pointer relative group"
                                        :class="activeAge === 'age-4' ? 'border-amber-500 bg-amber-100/90 ring-2 ring-amber-400/50 shadow-md scale-[1.02]' : 'border-amber-300/70 hover:border-amber-400'"
                                    >
                                        <div class="flex items-center justify-between w-full mb-1.5">
                                            <span class="inline-block bg-amber-500 text-white text-[11px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider shadow-xs">Age 4</span>
                                            <span class="text-[11px] text-amber-700 font-bold transition-transform" :class="activeAge === 'age-4' ? 'rotate-180' : ''">▼</span>
                                        </div>
                                        <h4 class="font-heading font-extrabold text-gray-900 text-xs sm:text-sm leading-tight group-hover:text-amber-800 whitespace-nowrap">Early Exploration</h4>
                                        <p class="text-xs text-amber-800 font-semibold mt-0.5">Programme</p>
                                    </button>

                                    {{-- Age 5 Card --}}
                                    <button 
                                        @click="activeAge = activeAge === 'age-5' ? null : 'age-5'" 
                                        class="bg-amber-50/90 border-2 rounded-2xl p-4 shadow-sm hover:shadow-md transition-all flex flex-col justify-center min-w-[160px] text-left cursor-pointer relative group"
                                        :class="activeAge === 'age-5' ? 'border-amber-500 bg-amber-100/90 ring-2 ring-amber-400/50 shadow-md scale-[1.02]' : 'border-amber-300/70 hover:border-amber-400'"
                                    >
                                        <div class="flex items-center justify-between w-full mb-1.5">
                                            <span class="inline-block bg-amber-500 text-white text-[11px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider shadow-xs">Age 5</span>
                                            <span class="text-[11px] text-amber-700 font-bold transition-transform" :class="activeAge === 'age-5' ? 'rotate-180' : ''">▼</span>
                                        </div>
                                        <h4 class="font-heading font-extrabold text-gray-900 text-xs sm:text-sm leading-tight group-hover:text-amber-800 whitespace-nowrap">Skill Building</h4>
                                        <p class="text-xs text-amber-800 font-semibold mt-0.5">Programme</p>
                                    </button>

                                    {{-- Age 6 Card --}}
                                    <button 
                                        @click="activeAge = activeAge === 'age-6' ? null : 'age-6'" 
                                        class="bg-amber-50/90 border-2 rounded-2xl p-4 shadow-sm hover:shadow-md transition-all flex flex-col justify-center min-w-[160px] text-left cursor-pointer relative group"
                                        :class="activeAge === 'age-6' ? 'border-amber-500 bg-amber-100/90 ring-2 ring-amber-400/50 shadow-md scale-[1.02]' : 'border-amber-300/70 hover:border-amber-400'"
                                    >
                                        <div class="flex items-center justify-between w-full mb-1.5">
                                            <span class="inline-block bg-amber-500 text-white text-[11px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider shadow-xs">Age 6</span>
                                            <span class="text-[11px] text-amber-700 font-bold transition-transform" :class="activeAge === 'age-6' ? 'rotate-180' : ''">▼</span>
                                        </div>
                                        <h4 class="font-heading font-extrabold text-gray-900 text-xs sm:text-sm leading-tight group-hover:text-amber-800 whitespace-nowrap">School Readiness</h4>
                                        <p class="text-xs text-amber-800 font-semibold mt-0.5">Programme</p>
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
                                    {{-- Speech Bubble Pointer Arrow --}}
                                    <div 
                                        class="w-4 h-4 bg-gradient-to-r from-amber-600 to-amber-700 rotate-45 border-l border-t border-amber-300 absolute -top-2 transition-all duration-300 shadow-xs"
                                        :class="{
                                            'left-[15%] sm:left-[16%] lg:left-[70%]': activeAge === 'age-4',
                                            'left-[50%] sm:left-[50%] lg:left-[83%]': activeAge === 'age-5',
                                            'left-[85%] sm:left-[84%] lg:left-[95%]': activeAge === 'age-6'
                                        }"
                                    ></div>

                                    {{-- Speech Bubble Card Container --}}
                                    <div class="bg-gradient-to-br from-amber-600 via-amber-700 to-amber-800 text-white rounded-2xl p-5 md:p-6 shadow-xl border border-amber-300/40 relative">
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
                                                                    <strong class="text-white font-bold">Bahasa Melayu Basics</strong> — Explore suku kata, simple words, early reading and language understanding.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-amber-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">English & Phonics</strong> — Build sound recognition, vocabulary, simple reading and confident speaking skills.
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
                                <div class="rounded-3xl p-6 md:p-8 border-2 shadow-sm flex flex-col justify-between bg-amber-50/70 border-amber-200">
                                    <div>
                                        <div class="flex flex-wrap items-center justify-between gap-4 mb-6 pb-4 border-b border-amber-200/70">
                                            <div>
                                                <h3 class="font-heading font-black text-xl md:text-2xl text-amber-900">EARLY CHILDHOOD LEARNING PATHWAY</h3>
                                                <p class="text-xs md:text-sm font-semibold text-amber-700">8-Step Continuous Mastery & Growth Process</p>
                                            </div>
                                            <span class="text-white text-xs font-bold px-3.5 py-1.5 rounded-full uppercase tracking-wider shadow-sm bg-amber-500">Preschool Flowchart</span>
                                        </div>

                                        {{-- Step-by-Step Flowchart Grid (Fits 100% on screen without slider) --}}
                                        <div class="py-2 w-full">
                                            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2 w-full items-stretch">

                                                {{-- Step 1 --}}
                                                <div class="relative rounded-2xl text-center shadow-md flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] transition-transform group" style="background-color: #0f172a; border: 1px solid #1e293b; color: #ffffff;">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest mb-0.5" style="color: #fbbf24;">Step 1</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-bold leading-tight text-white group-hover:text-amber-300">Diagnostic Observation</span>
                                                    <span class="hidden lg:block absolute -right-2.5 top-1/2 -translate-y-1/2 z-10 text-amber-500 text-xs font-bold pointer-events-none">➔</span>
                                                </div>

                                                {{-- Step 2 --}}
                                                <div class="relative rounded-2xl text-center shadow-md flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] transition-transform group" style="background-color: #0f172a; border: 1px solid #1e293b; color: #ffffff;">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest mb-0.5" style="color: #fbbf24;">Step 2</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-bold leading-tight text-white group-hover:text-amber-300">Play-Based Learning</span>
                                                    <span class="hidden lg:block absolute -right-2.5 top-1/2 -translate-y-1/2 z-10 text-amber-500 text-xs font-bold pointer-events-none">➔</span>
                                                </div>

                                                {{-- Step 3 --}}
                                                <div class="relative rounded-2xl text-center shadow-md flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] transition-transform group" style="background-color: #0f172a; border: 1px solid #1e293b; color: #ffffff;">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest mb-0.5" style="color: #fbbf24;">Step 3</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-bold leading-tight text-white group-hover:text-amber-300">Guided Activities</span>
                                                    <span class="hidden lg:block absolute -right-2.5 top-1/2 -translate-y-1/2 z-10 text-amber-500 text-xs font-bold pointer-events-none">➔</span>
                                                </div>

                                                {{-- Step 4 --}}
                                                <div class="relative rounded-2xl text-center shadow-md flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] transition-transform group" style="background-color: #0f172a; border: 1px solid #1e293b; color: #ffffff;">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest mb-0.5" style="color: #fbbf24;">Step 4</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-bold leading-tight text-white group-hover:text-amber-300">Early Skill Practice</span>
                                                    <span class="hidden lg:block absolute -right-2.5 top-1/2 -translate-y-1/2 z-10 text-amber-500 text-xs font-bold pointer-events-none">➔</span>
                                                </div>

                                                {{-- Step 5 --}}
                                                <div class="relative rounded-2xl text-center shadow-md flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] transition-transform group" style="background-color: #0f172a; border: 1px solid #1e293b; color: #ffffff;">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest mb-0.5" style="color: #fbbf24;">Step 5</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-bold leading-tight text-white group-hover:text-amber-300">Progress Assessment</span>
                                                    <span class="hidden lg:block absolute -right-2.5 top-1/2 -translate-y-1/2 z-10 text-amber-500 text-xs font-bold pointer-events-none">➔</span>
                                                </div>

                                                {{-- Step 6 --}}
                                                <div class="relative rounded-2xl text-center shadow-md flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] transition-transform group" style="background-color: #0f172a; border: 1px solid #1e293b; color: #ffffff;">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest mb-0.5" style="color: #fbbf24;">Step 6</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-bold leading-tight text-white group-hover:text-amber-300">Performance Review</span>
                                                    <span class="hidden lg:block absolute -right-2.5 top-1/2 -translate-y-1/2 z-10 text-amber-500 text-xs font-bold pointer-events-none">➔</span>
                                                </div>

                                                {{-- Step 7 --}}
                                                <div class="relative rounded-2xl text-center shadow-md flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] transition-transform group" style="background-color: #0f172a; border: 1px solid #1e293b; color: #ffffff;">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest mb-0.5" style="color: #fbbf24;">Step 7</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-bold leading-tight text-white group-hover:text-amber-300">Targeted Support</span>
                                                    <span class="hidden lg:block absolute -right-2.5 top-1/2 -translate-y-1/2 z-10 text-amber-500 text-xs font-bold pointer-events-none">➔</span>
                                                </div>

                                                {{-- Step 8 (Goal - Gold/Amber) --}}
                                                <div class="rounded-2xl text-center shadow-lg shadow-amber-500/20 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] transition-transform group ring-2 ring-amber-400/50" style="background-color: #d97706; border: 1px solid #f59e0b; color: #ffffff;">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest mb-0.5" style="color: #fef3c7;">Goal</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-white group-hover:text-amber-100">School Readiness</span>
                                                </div>

                                            </div>
                                        </div>


                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 1. PRIMARY EDUCATION --}}
                        <div 
                            x-show="activeProgramme === 'primary-education'"
                            x-data="{ activeYear: null }"
                            x-transition:enter="transition-all ease-out duration-400"
                            x-transition:enter-start="opacity-0 scale-[0.98] translate-y-2"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition-all ease-in duration-300"
                            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                            x-transition:leave-end="opacity-0 scale-[0.98] -translate-y-2"
                            id="primary-education" 
                            class="col-start-1 row-start-1 w-full bg-white rounded-3xl p-6 md:p-10 border-2 border-emerald-200/80 shadow-2xl"
                        >
                            {{-- Header Bar --}}
                            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-8 pb-6 border-b border-gray-200">
                                <div class="flex items-center gap-4 md:gap-5">
                                    <div class="w-14 h-14 md:w-16 md:h-16 rounded-2xl text-white flex items-center justify-center shadow-lg flex-shrink-0" style="background-color: #059669; color: #ffffff;">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 md:w-8 md:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="font-heading font-black text-2xl md:text-3xl lg:text-4xl tracking-tight text-gray-900 leading-tight">PRIMARY<br>EDUCATION</h2>
                                        <p class="font-extrabold text-sm md:text-lg text-emerald-600">Year 1 – Year 6</p>
                                        <p class="text-gray-500 text-xs md:text-sm font-medium">Click any card to view detailed stage description.</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 w-full lg:w-[580px] shrink-0">
                                    {{-- Year 1 - 2 Card --}}
                                    <button 
                                        @click="activeYear = activeYear === 'year-1-2' ? null : 'year-1-2'"
                                        class="bg-emerald-50/90 border-2 rounded-2xl p-4 shadow-sm hover:shadow-md transition-all flex flex-col justify-center min-w-[160px] text-left cursor-pointer relative group"
                                        :class="activeYear === 'year-1-2' ? 'border-emerald-500 bg-emerald-100/90 ring-2 ring-emerald-400/50 shadow-md scale-[1.02]' : 'border-emerald-300/70 hover:border-emerald-400'"
                                    >
                                        <div class="flex items-center justify-between w-full mb-1.5">
                                            <span class="inline-block bg-emerald-600 text-white text-[11px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider shadow-xs whitespace-nowrap">Year 1 – 2</span>
                                            <span class="text-[11px] text-emerald-700 font-bold transition-transform" :class="activeYear === 'year-1-2' ? 'rotate-180' : ''">▼</span>
                                        </div>
                                        <h4 class="font-heading font-extrabold text-gray-900 text-xs sm:text-sm leading-tight group-hover:text-emerald-800 whitespace-nowrap">Early Primary</h4>
                                        <p class="text-xs text-emerald-800 font-semibold mt-0.5">Programme</p>
                                    </button>

                                    {{-- Year 3 - 4 Card --}}
                                    <button 
                                        @click="activeYear = activeYear === 'year-3-4' ? null : 'year-3-4'"
                                        class="bg-emerald-50/90 border-2 rounded-2xl p-4 shadow-sm hover:shadow-md transition-all flex flex-col justify-center min-w-[160px] text-left cursor-pointer relative group"
                                        :class="activeYear === 'year-3-4' ? 'border-emerald-500 bg-emerald-100/90 ring-2 ring-emerald-400/50 shadow-md scale-[1.02]' : 'border-emerald-300/70 hover:border-emerald-400'"
                                    >
                                        <div class="flex items-center justify-between w-full mb-1.5">
                                            <span class="inline-block bg-emerald-600 text-white text-[11px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider shadow-xs whitespace-nowrap">Year 3 – 4</span>
                                            <span class="text-[11px] text-emerald-700 font-bold transition-transform" :class="activeYear === 'year-3-4' ? 'rotate-180' : ''">▼</span>
                                        </div>
                                        <h4 class="font-heading font-extrabold text-gray-900 text-xs sm:text-sm leading-tight group-hover:text-emerald-800 whitespace-nowrap">Core Building</h4>
                                        <p class="text-xs text-emerald-800 font-semibold mt-0.5">Programme</p>
                                    </button>

                                    {{-- Year 5 - 6 Card --}}
                                    <button 
                                        @click="activeYear = activeYear === 'year-5-6' ? null : 'year-5-6'"
                                        class="bg-emerald-50/90 border-2 rounded-2xl p-4 shadow-sm hover:shadow-md transition-all flex flex-col justify-center min-w-[160px] text-left cursor-pointer relative group"
                                        :class="activeYear === 'year-5-6' ? 'border-emerald-500 bg-emerald-100/90 ring-2 ring-emerald-400/50 shadow-md scale-[1.02]' : 'border-emerald-300/70 hover:border-emerald-400'"
                                    >
                                        <div class="flex items-center justify-between w-full mb-1.5">
                                            <span class="inline-block bg-emerald-600 text-white text-[11px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider shadow-xs whitespace-nowrap">Year 5 – 6</span>
                                            <span class="text-[11px] text-emerald-700 font-bold transition-transform" :class="activeYear === 'year-5-6' ? 'rotate-180' : ''">▼</span>
                                        </div>
                                        <h4 class="font-heading font-extrabold text-gray-900 text-xs sm:text-sm leading-tight group-hover:text-emerald-800 whitespace-nowrap">UASA Readiness</h4>
                                        <p class="text-xs text-emerald-800 font-semibold mt-0.5">Programme</p>
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
                                    {{-- Speech Bubble Pointer Arrow --}}
                                    <div 
                                        class="w-4 h-4 bg-emerald-600 rotate-45 border-l border-t border-emerald-300 absolute -top-2 transition-all duration-300 shadow-xs -ml-2 z-30"
                                        :class="{
                                            'left-[16.6%] sm:left-[66.5%] lg:left-[66.5%]': activeYear === 'year-1-2',
                                            'left-[50%] sm:left-[81.2%] lg:left-[81.2%]': activeYear === 'year-3-4',
                                            'left-[83.3%] sm:left-[95.8%] lg:left-[95.8%]': activeYear === 'year-5-6'
                                        }"
                                    ></div>

                                    {{-- Speech Bubble Card Container --}}
                                    <div class="bg-gradient-to-br from-emerald-600 via-emerald-700 to-emerald-800 text-white rounded-2xl p-5 md:p-6 shadow-xl border border-emerald-300/40 relative">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="w-full">
                                                {{-- Year 1 - 2 Content --}}
                                                <template x-if="activeYear === 'year-1-2'">
                                                    <div>
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

                                                {{-- Year 3 - 4 Content --}}
                                                <template x-if="activeYear === 'year-3-4'">
                                                    <div class="flex flex-col sm:flex-row items-start justify-between gap-6">
                                                        <div class="w-full">
                                                            <ul class="space-y-2 text-xs md:text-sm text-emerald-50 font-medium">
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
                                                        </div>

                                                        {{-- Official MPT4 Seal Stamp --}}
                                                        <div class="shrink-0 self-center sm:self-center pr-2">
                                                            <div class="relative border-2 border-dashed border-amber-300/80 rounded-2xl p-4 bg-emerald-950/60 backdrop-blur-md shadow-2xl transform -rotate-3 hover:rotate-0 transition-transform duration-300 flex flex-col items-center justify-center text-center min-w-[170px] select-none group">
                                                                <div class="absolute -top-2.5 bg-amber-400 text-emerald-950 text-[9px] font-black uppercase px-2.5 py-0.5 rounded-full tracking-widest shadow-xs">
                                                                    OFFICIAL SYLLABUS
                                                                </div>
                                                                <div class="text-2xl font-black text-amber-300 tracking-wider font-heading leading-tight mt-1">
                                                                    MPT4
                                                                </div>
                                                                <div class="text-[10px] font-extrabold text-white uppercase tracking-tight leading-tight mt-0.5 max-w-[130px]">
                                                                    Matriks Pembelajaran Tahun 4
                                                                </div>
                                                                <div class="mt-2 text-[9px] font-bold text-emerald-200 border-t border-amber-300/30 pt-1.5 w-full flex items-center justify-center gap-1">
                                                                    <span class="text-amber-300">★</span> KSSR SEMAKAN <span class="text-amber-300">★</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>

                                                {{-- Year 5 - 6 Content --}}
                                                <template x-if="activeYear === 'year-5-6'">
                                                    <div class="flex flex-col sm:flex-row items-start justify-between gap-6">
                                                        <div class="w-full">
                                                            <ul class="space-y-2 text-xs md:text-sm text-emerald-50 font-medium">
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
                                                        </div>

                                                        {{-- Official UASA Seal Stamp --}}
                                                        <div class="shrink-0 self-center sm:self-center pr-2">
                                                            <div class="relative border-2 border-dashed border-amber-300/80 rounded-2xl p-4 bg-emerald-950/60 backdrop-blur-md shadow-2xl transform rotate-3 hover:rotate-0 transition-transform duration-300 flex flex-col items-center justify-center text-center min-w-[170px] select-none group">
                                                                <div class="absolute -top-2.5 bg-amber-400 text-emerald-950 text-[9px] font-black uppercase px-2.5 py-0.5 rounded-full tracking-widest shadow-xs">
                                                                    EXAM READINESS
                                                                </div>
                                                                <div class="text-2xl font-black text-amber-300 tracking-wider font-heading leading-tight mt-1">
                                                                    UASA
                                                                </div>
                                                                <div class="text-[10px] font-extrabold text-white uppercase tracking-tight leading-tight mt-0.5 max-w-[130px]">
                                                                    Ujian Akhir Sesi Akademik
                                                                </div>
                                                                <div class="mt-2 text-[9px] font-bold text-emerald-200 border-t border-amber-300/30 pt-1.5 w-full flex items-center justify-center gap-1">
                                                                    <span class="text-amber-300">★</span> YEAR 5 & 6 MASTERY <span class="text-amber-300">★</span>
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
                                            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2 w-full items-stretch">

                                                {{-- Step 1 --}}
                                                <div class="relative rounded-2xl text-center shadow-md flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] transition-transform group" style="background-color: #0f172a; border: 1px solid #1e293b; color: #ffffff;">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest mb-0.5" style="color: #34d399;">Step 1</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-bold leading-tight text-white group-hover:text-emerald-300">Diagnostic Evaluation</span>
                                                    <span class="hidden lg:block absolute -right-2.5 top-1/2 -translate-y-1/2 z-10 text-emerald-500 text-xs font-bold pointer-events-none">➔</span>
                                                </div>

                                                {{-- Step 2 --}}
                                                <div class="relative rounded-2xl text-center shadow-md flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] transition-transform group" style="background-color: #0f172a; border: 1px solid #1e293b; color: #ffffff;">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest mb-0.5" style="color: #34d399;">Step 2</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-bold leading-tight text-white group-hover:text-emerald-300">Concept Foundation</span>
                                                    <span class="hidden lg:block absolute -right-2.5 top-1/2 -translate-y-1/2 z-10 text-emerald-500 text-xs font-bold pointer-events-none">➔</span>
                                                </div>

                                                {{-- Step 3 --}}
                                                <div class="relative rounded-2xl text-center shadow-md flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] transition-transform group" style="background-color: #0f172a; border: 1px solid #1e293b; color: #ffffff;">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest mb-0.5" style="color: #34d399;">Step 3</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-bold leading-tight text-white group-hover:text-emerald-300">Guided Practice</span>
                                                    <span class="hidden lg:block absolute -right-2.5 top-1/2 -translate-y-1/2 z-10 text-emerald-500 text-xs font-bold pointer-events-none">➔</span>
                                                </div>

                                                {{-- Step 4 --}}
                                                <div class="relative rounded-2xl text-center shadow-md flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] transition-transform group" style="background-color: #0f172a; border: 1px solid #1e293b; color: #ffffff;">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest mb-0.5" style="color: #34d399;">Step 4</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-bold leading-tight text-white group-hover:text-emerald-300">Skill Consolidation</span>
                                                    <span class="hidden lg:block absolute -right-2.5 top-1/2 -translate-y-1/2 z-10 text-emerald-500 text-xs font-bold pointer-events-none">➔</span>
                                                </div>

                                                {{-- Step 5 --}}
                                                <div class="relative rounded-2xl text-center shadow-md flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] transition-transform group" style="background-color: #0f172a; border: 1px solid #1e293b; color: #ffffff;">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest mb-0.5" style="color: #34d399;">Step 5</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-bold leading-tight text-white group-hover:text-emerald-300">Progress Tracking</span>
                                                    <span class="hidden lg:block absolute -right-2.5 top-1/2 -translate-y-1/2 z-10 text-emerald-500 text-xs font-bold pointer-events-none">➔</span>
                                                </div>

                                                {{-- Step 6 --}}
                                                <div class="relative rounded-2xl text-center shadow-md flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] transition-transform group" style="background-color: #0f172a; border: 1px solid #1e293b; color: #ffffff;">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest mb-0.5" style="color: #34d399;">Step 6</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-bold leading-tight text-white group-hover:text-emerald-300">Exam Answering Skills</span>
                                                    <span class="hidden lg:block absolute -right-2.5 top-1/2 -translate-y-1/2 z-10 text-emerald-500 text-xs font-bold pointer-events-none">➔</span>
                                                </div>

                                                {{-- Step 7 --}}
                                                <div class="relative rounded-2xl text-center shadow-md flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] transition-transform group" style="background-color: #0f172a; border: 1px solid #1e293b; color: #ffffff;">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest mb-0.5" style="color: #34d399;">Step 7</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-bold leading-tight text-white group-hover:text-emerald-300">Targeted Intervention</span>
                                                    <span class="hidden lg:block absolute -right-2.5 top-1/2 -translate-y-1/2 z-10 text-emerald-500 text-xs font-bold pointer-events-none">➔</span>
                                                </div>

                                                {{-- Step 8 (Goal - Emerald / Green) --}}
                                                <div class="rounded-2xl text-center shadow-lg shadow-emerald-500/20 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] transition-transform group ring-2 ring-emerald-400/50" style="background-color: #059669; border: 1px solid #10b981; color: #ffffff;">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest mb-0.5" style="color: #a7f3d0;">Goal</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-white group-hover:text-emerald-100">UASA Excellence</span>
                                                </div>

                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            </div>
                        </div>


                        {{-- 2. SECONDARY EDUCATION --}}
                        <div 
                            x-show="activeProgramme === 'secondary-education'"
                            x-data="{ activeSecTrack: null }"
                            x-transition:enter="transition-all ease-out duration-400"
                            x-transition:enter-start="opacity-0 scale-[0.98] translate-y-2"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition-all ease-in duration-300"
                            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                            x-transition:leave-end="opacity-0 scale-[0.98] -translate-y-2"
                            id="secondary-education" 
                            class="col-start-1 row-start-1 w-full bg-white rounded-3xl p-6 md:p-10 border-2 border-blue-200/90 shadow-2xl"
                        >
                            {{-- Header Bar --}}
                            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-8 pb-6 border-b border-gray-200">
                                <div class="flex items-center gap-4 md:gap-5">
                                    <div class="w-14 h-14 md:w-16 md:h-16 rounded-2xl text-white flex items-center justify-center shadow-lg flex-shrink-0" style="background-color: #2563eb; color: #ffffff;">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 md:w-8 md:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path></svg>
                                    </div>
                                    <div>
                                        <h2 class="font-heading font-black text-2xl md:text-3xl lg:text-4xl tracking-tight text-gray-900 leading-tight">SECONDARY<br>EDUCATION</h2>
                                        <p class="font-extrabold text-sm md:text-lg text-blue-600">Form 1 – Form 6</p>
                                        <p class="text-gray-500 text-xs md:text-sm font-medium">Click any card to view detailed stage description.</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 w-full lg:w-[580px] shrink-0">
                                    {{-- Lower Secondary Card --}}
                                    <button 
                                        @click="activeSecTrack = activeSecTrack === 'lower-sec' ? null : 'lower-sec'"
                                        class="bg-blue-50/90 border-2 rounded-2xl p-4 shadow-sm hover:shadow-md transition-all flex flex-col justify-center min-w-[170px] text-left cursor-pointer relative group"
                                        :class="activeSecTrack === 'lower-sec' ? 'border-blue-500 bg-blue-100/90 ring-2 ring-blue-400/50 shadow-md scale-[1.02]' : 'border-blue-300/70 hover:border-blue-400'"
                                    >
                                        <div class="flex items-center justify-between w-full mb-1.5">
                                            <span class="inline-block bg-blue-600 text-white text-[11px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider shadow-xs">Form 1 – 3</span>
                                            <span class="text-[11px] text-blue-700 font-bold transition-transform" :class="activeSecTrack === 'lower-sec' ? 'rotate-180' : ''">▼</span>
                                        </div>
                                        <h4 class="font-heading font-extrabold text-gray-900 text-xs sm:text-sm leading-tight group-hover:text-blue-800">Lower Secondary Mastery</h4>
                                        <p class="text-xs text-blue-800 font-semibold mt-0.5">KSSM Foundation Track</p>
                                    </button>

                                    {{-- Upper Secondary Card --}}
                                    <button 
                                        @click="activeSecTrack = activeSecTrack === 'upper-sec' ? null : 'upper-sec'"
                                        class="bg-blue-50/90 border-2 rounded-2xl p-4 shadow-sm hover:shadow-md transition-all flex flex-col justify-center min-w-[170px] text-left cursor-pointer relative group"
                                        :class="activeSecTrack === 'upper-sec' ? 'border-blue-500 bg-blue-100/90 ring-2 ring-blue-400/50 shadow-md scale-[1.02]' : 'border-blue-300/70 hover:border-blue-400'"
                                    >
                                        <div class="flex items-center justify-between w-full mb-1.5">
                                            <span class="inline-block bg-blue-600 text-white text-[11px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider shadow-xs">Form 4 – 6</span>
                                            <span class="text-[11px] text-blue-700 font-bold transition-transform" :class="activeSecTrack === 'upper-sec' ? 'rotate-180' : ''">▼</span>
                                        </div>
                                        <h4 class="font-heading font-extrabold text-gray-900 text-xs sm:text-sm leading-tight group-hover:text-blue-800">SPM & Upper Secondary</h4>
                                        <p class="text-xs text-blue-800 font-semibold mt-0.5">Exam Intensive Track</p>
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
                                    {{-- Speech Bubble Pointer Arrow --}}
                                    <div 
                                        class="w-4 h-4 bg-blue-600 rotate-45 border-l border-t border-blue-300 absolute -top-2 transition-all duration-300 shadow-xs -ml-2 z-30"
                                        :class="{
                                            'left-[25%] sm:left-[25%] lg:left-[64.5%]': activeSecTrack === 'lower-sec',
                                            'left-[75%] sm:left-[75%] lg:left-[89.5%]': activeSecTrack === 'upper-sec'
                                        }"
                                    ></div>

                                    {{-- Speech Bubble Card Container --}}
                                    <div class="bg-gradient-to-br from-blue-600 via-blue-700 to-blue-800 text-white rounded-2xl p-5 md:p-6 shadow-xl border border-blue-300/40 relative">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="w-full">
                                                {{-- Form 1-3 Content --}}
                                                <template x-if="activeSecTrack === 'lower-sec'">
                                                    <div>
                                                        <ul class="space-y-2 text-xs md:text-sm text-blue-50 font-medium">
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-blue-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Bahasa Melayu</strong> — Strengthen comprehension, grammar, structured writing and literature-based understanding.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-blue-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">English Language</strong> — Build vocabulary, reading comprehension, writing skills and confident communication.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-blue-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Mathematics</strong> — Develop algebra, number skills, problem-solving strategies and logical reasoning.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-blue-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Science</strong> — Deepen understanding of scientific concepts through inquiry, observation and practical learning.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-blue-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Sejarah</strong> — Understand key historical topics, timeline knowledge, structured notes and exam-style responses.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-blue-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Geografi</strong> — Develop map-reading skills, physical and human geography knowledge, and understanding of the environment and places.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-blue-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Pendidikan Islam</strong> — Strengthen aqidah, ibadah, akhlak and Islamic understanding through guided learning and values-based practice.
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </template>

                                                {{-- Form 4-6 Content --}}
                                                <template x-if="activeSecTrack === 'upper-sec'">
                                                    <div>
                                                        <ul class="space-y-2 text-xs md:text-sm text-blue-50 font-medium">
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-blue-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Bahasa Melayu</strong> — Strengthen comprehension, essay writing, grammar and confident exam-style responses.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-blue-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">English Language</strong> — Enhance reading, writing, vocabulary, language accuracy and communication confidence.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-blue-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Mathematics</strong> — Master algebra, formulas, problem-solving methods and structured answering techniques.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-blue-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Science</strong> — Build deeper conceptual understanding through analysis, application and practical learning.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-blue-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Sejarah</strong> — Strengthen content mastery, chronology, source-based understanding and structured responses.
                                                                </div>
                                                            </li>
                                                            <li class="flex items-start gap-2.5">
                                                                <span class="text-blue-300 font-black text-base leading-none mt-0.5">•</span>
                                                                <div>
                                                                    <strong class="text-white font-bold">Pendidikan Islam</strong> — Deepen understanding of aqidah, ibadah, akhlak and Islamic teachings.
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
                                            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2 w-full items-stretch">

                                                {{-- Step 1 --}}
                                                <div class="relative rounded-2xl text-center shadow-md flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] transition-transform group" style="background-color: #0f172a; border: 1px solid #1e293b; color: #ffffff;">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest mb-0.5" style="color: #60a5fa;">Step 1</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-bold leading-tight text-white group-hover:text-blue-300">Diagnostic Assessment</span>
                                                    <span class="hidden lg:block absolute -right-2.5 top-1/2 -translate-y-1/2 z-10 text-blue-400 text-xs font-bold pointer-events-none">➔</span>
                                                </div>

                                                {{-- Step 2 --}}
                                                <div class="relative rounded-2xl text-center shadow-md flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] transition-transform group" style="background-color: #0f172a; border: 1px solid #1e293b; color: #ffffff;">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest mb-0.5" style="color: #60a5fa;">Step 2</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-bold leading-tight text-white group-hover:text-blue-300">Core Concept Mastery</span>
                                                    <span class="hidden lg:block absolute -right-2.5 top-1/2 -translate-y-1/2 z-10 text-blue-400 text-xs font-bold pointer-events-none">➔</span>
                                                </div>

                                                {{-- Step 3 --}}
                                                <div class="relative rounded-2xl text-center shadow-md flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] transition-transform group" style="background-color: #0f172a; border: 1px solid #1e293b; color: #ffffff;">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest mb-0.5" style="color: #60a5fa;">Step 3</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-bold leading-tight text-white group-hover:text-blue-300">KBAT Problem Solving</span>
                                                    <span class="hidden lg:block absolute -right-2.5 top-1/2 -translate-y-1/2 z-10 text-blue-400 text-xs font-bold pointer-events-none">➔</span>
                                                </div>

                                                {{-- Step 4 --}}
                                                <div class="relative rounded-2xl text-center shadow-md flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] transition-transform group" style="background-color: #0f172a; border: 1px solid #1e293b; color: #ffffff;">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest mb-0.5" style="color: #60a5fa;">Step 4</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-bold leading-tight text-white group-hover:text-blue-300">Structured Practice</span>
                                                    <span class="hidden lg:block absolute -right-2.5 top-1/2 -translate-y-1/2 z-10 text-blue-400 text-xs font-bold pointer-events-none">➔</span>
                                                </div>

                                                {{-- Step 5 --}}
                                                <div class="relative rounded-2xl text-center shadow-md flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] transition-transform group" style="background-color: #0f172a; border: 1px solid #1e293b; color: #ffffff;">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest mb-0.5" style="color: #60a5fa;">Step 5</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-bold leading-tight text-white group-hover:text-blue-300">Performance Tracking</span>
                                                    <span class="hidden lg:block absolute -right-2.5 top-1/2 -translate-y-1/2 z-10 text-blue-400 text-xs font-bold pointer-events-none">➔</span>
                                                </div>

                                                {{-- Step 6 --}}
                                                <div class="relative rounded-2xl text-center shadow-md flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] transition-transform group" style="background-color: #0f172a; border: 1px solid #1e293b; color: #ffffff;">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest mb-0.5" style="color: #60a5fa;">Step 6</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-bold leading-tight text-white group-hover:text-blue-300">Exam Paper Answering</span>
                                                    <span class="hidden lg:block absolute -right-2.5 top-1/2 -translate-y-1/2 z-10 text-blue-400 text-xs font-bold pointer-events-none">➔</span>
                                                </div>

                                                {{-- Step 7 --}}
                                                <div class="relative rounded-2xl text-center shadow-md flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] transition-transform group" style="background-color: #0f172a; border: 1px solid #1e293b; color: #ffffff;">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest mb-0.5" style="color: #60a5fa;">Step 7</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-bold leading-tight text-white group-hover:text-blue-300">Targeted Revision</span>
                                                    <span class="hidden lg:block absolute -right-2.5 top-1/2 -translate-y-1/2 z-10 text-blue-400 text-xs font-bold pointer-events-none">➔</span>
                                                </div>

                                                {{-- Step 8 (Goal - Blue) --}}
                                                <div class="rounded-2xl text-center shadow-lg shadow-blue-500/20 flex flex-col justify-center items-center min-h-[74px] p-2.5 hover:scale-[1.03] transition-transform group ring-2 ring-blue-400/50" style="background-color: #2563eb; border: 1px solid #3b82f6; color: #ffffff;">
                                                    <span class="text-[9px] font-extrabold uppercase tracking-widest mb-0.5" style="color: #bfdbfe;">Goal</span>
                                                    <span class="text-[11px] sm:text-xs font-heading font-black leading-tight text-white group-hover:text-blue-100">SPM & IGCSE A* Goal</span>
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
            </div>
        </div>
    </div>

</x-app-layout>
