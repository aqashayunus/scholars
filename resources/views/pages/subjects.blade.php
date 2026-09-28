<x-app-layout>
    <x-slot name="title">KPM Curriculum &amp; Academic Subjects</x-slot>

    {{-- ══════════════════════════════════════════════════════════════════════════════ --}}
    {{-- HERO SECTION: MALAYSIAN KPM CURRICULUM OVERVIEW                                --}}
    {{-- ══════════════════════════════════════════════════════════════════════════════ --}}
    <section class="bg-gradient-to-br from-slate-950 via-blue-950 to-indigo-950 text-white py-20 relative overflow-hidden">
        {{-- Background Glow & Geometric Elements --}}
        <div class="absolute inset-0 opacity-25 bg-[radial-gradient(#38bdf8_1px,transparent_1px)] [background-size:24px_24px]"></div>
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative max-w-5xl mx-auto px-4 sm:px-6 text-center space-y-5">
            {{-- Official Ministry Badge --}}
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-semibold text-sky-300 shadow-inner">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Malaysian National Curriculum Standard • KPM / KSSR / KSSM</span>
            </div>

            <h1 class="font-heading font-black text-3xl sm:text-4xl md:text-5xl lg:text-6xl text-white tracking-tight leading-tight">
                Academic Subjects &amp; <span class="bg-clip-text text-transparent bg-gradient-to-r from-sky-400 via-teal-300 to-amber-300">Curriculum Structure</span>
            </h1>
            
            <p class="text-white/80 text-sm sm:text-base md:text-lg max-w-3xl mx-auto font-normal leading-relaxed">
                Structured in strict accordance with Kementerian Pendidikan Malaysia (KPM) guidelines across <strong>Preschool (KSPK)</strong>, <strong>Primary (KSSR Semakan)</strong>, and <strong>Secondary (KSSM Tingkatan 1–5 Core &amp; Electives)</strong>.
            </p>

            {{-- Curriculum Stats Counter --}}
            <div class="pt-4 grid grid-cols-2 sm:grid-cols-4 gap-3 max-w-3xl mx-auto">
                <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-3.5 text-center">
                    <div class="text-2xl font-black text-amber-400">8 Domains</div>
                    <div class="text-[11px] text-white/70 font-medium">Kindergarten (KSPK)</div>
                </div>
                <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-3.5 text-center">
                    <div class="text-2xl font-black text-emerald-400">10 Subjects</div>
                    <div class="text-[11px] text-white/70 font-medium">Primary (KSSR Yr 1–6)</div>
                </div>
                <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-3.5 text-center">
                    <div class="text-2xl font-black text-sky-400">10 Subjects</div>
                    <div class="text-[11px] text-white/70 font-medium">Lower Sec (KSSM F1–3)</div>
                </div>
                <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-3.5 text-center">
                    <div class="text-2xl font-black text-purple-400">6 Core + 9 Elec</div>
                    <div class="text-[11px] text-white/70 font-medium">Upper Sec (KSSM SPM)</div>
                </div>
            </div>
        </div>

        {{-- Curved bottom divider --}}
        <div class="absolute -bottom-1 left-0 right-0 overflow-hidden leading-none pointer-events-none z-10">
            <svg viewBox="0 0 1440 48" fill="none" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-8 block">
                <path d="M0,20 Q720,48 1440,20 L1440,48 L0,48 Z" fill="#f8fafc"/>
            </svg>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════════════════════ --}}
    {{-- MAIN SUBJECTS DIRECTORY WITH INTERACTIVE ALPINE.JS FILTERS                     --}}
    {{-- ══════════════════════════════════════════════════════════════════════════════ --}}
    <section class="py-14 bg-slate-50 min-h-screen" 
             x-data="{ 
                 selectedLevel: 'all', 
                 selectedCategory: 'all',
                 selectedStream: 'all',
                 searchQuery: '',
                 matchesSubject(sub) {
                     // 1. Level Filter
                     let levelMatch = false;
                     if (this.selectedLevel === 'all') levelMatch = true;
                     else if (this.selectedLevel === 'kindergarten') levelMatch = (sub.education_type === 'kindergarten');
                     else if (this.selectedLevel === 'primary') levelMatch = (sub.education_type === 'primary');
                     else if (this.selectedLevel === 'lower-secondary') levelMatch = (sub.stage === 'Lower Secondary');
                     else if (this.selectedLevel === 'upper-secondary') levelMatch = (sub.stage === 'Upper Secondary');

                     // 2. Category Filter
                     let catMatch = false;
                     if (this.selectedCategory === 'all') catMatch = true;
                     else if (this.selectedCategory === 'Core') catMatch = (sub.category === 'Core');
                     else if (this.selectedCategory === 'Elective') catMatch = (sub.category === 'Elective');
                     else if (this.selectedCategory === 'Early Development') catMatch = (sub.category === 'Early Development');

                     // 3. Stream Package Filter
                     let streamMatch = false;
                     if (this.selectedStream === 'all') streamMatch = true;
                     else streamMatch = (sub.stream_package === this.selectedStream);

                     // 4. Search text
                     let searchMatch = true;
                     if (this.searchQuery.trim() !== '') {
                         const q = this.searchQuery.toLowerCase().trim();
                         const text = (sub.name + ' ' + (sub.code || '') + ' ' + (sub.stage || '') + ' ' + (sub.stream_package || '') + ' ' + (sub.description || '')).toLowerCase();
                         searchMatch = text.includes(q);
                     }

                     return levelMatch && catMatch && streamMatch && searchMatch;
                 }
             }">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- ── FILTER CONTROLS BAR ── --}}
            <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-xs space-y-5">
                
                {{-- Education Level Selector (Tier 1) --}}
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Step 1: Select Education Level</h2>
                        <div class="flex flex-wrap items-center gap-2 mt-2">
                            <button @click="selectedLevel = 'all'; selectedStream = 'all';"
                                    :class="selectedLevel === 'all' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                    class="px-4 py-2 rounded-2xl text-xs font-bold transition-all cursor-pointer">
                                All Levels ({{ $subjects->count() }})
                            </button>

                            <button @click="selectedLevel = 'kindergarten'; selectedStream = 'all';"
                                    :class="selectedLevel === 'kindergarten' ? 'bg-amber-500 text-white shadow-md shadow-amber-500/20' : 'bg-amber-50 text-amber-900 hover:bg-amber-100'"
                                    class="px-4 py-2 rounded-2xl text-xs font-bold transition-all cursor-pointer">
                                Kindergarten (KSPK) ({{ $subjects->where('education_type', 'kindergarten')->count() }})
                            </button>

                            <button @click="selectedLevel = 'primary'; selectedStream = 'all';"
                                    :class="selectedLevel === 'primary' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'bg-emerald-50 text-emerald-900 hover:bg-emerald-100'"
                                    class="px-4 py-2 rounded-2xl text-xs font-bold transition-all cursor-pointer">
                                Primary School (KSSR) ({{ $subjects->where('education_type', 'primary')->count() }})
                            </button>

                            <button @click="selectedLevel = 'lower-secondary'; selectedStream = 'all';"
                                    :class="selectedLevel === 'lower-secondary' ? 'bg-sky-600 text-white shadow-md shadow-sky-600/20' : 'bg-sky-50 text-sky-900 hover:bg-sky-100'"
                                    class="px-4 py-2 rounded-2xl text-xs font-bold transition-all cursor-pointer">
                                Lower Secondary (Form 1–3) ({{ $subjects->where('stage', 'Lower Secondary')->count() }})
                            </button>

                            <button @click="selectedLevel = 'upper-secondary'"
                                    :class="selectedLevel === 'upper-secondary' ? 'bg-purple-600 text-white shadow-md shadow-purple-600/20' : 'bg-purple-50 text-purple-900 hover:bg-purple-100'"
                                    class="px-4 py-2 rounded-2xl text-xs font-bold transition-all cursor-pointer">
                                Upper Secondary (Form 4–5 / SPM) ({{ $subjects->where('stage', 'Upper Secondary')->count() }})
                            </button>
                        </div>
                    </div>

                    {{-- Search Input --}}
                    <div class="relative w-full md:w-72 shrink-0">
                        <input x-model="searchQuery" type="text" placeholder="Search subject, code..." 
                               class="w-full bg-slate-50 border border-slate-200 rounded-2xl pl-10 pr-4 py-2.5 text-xs text-slate-800 outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                {{-- Secondary Filters: Category & Elective Stream (Tier 2) --}}
                <div class="pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-4">
                    {{-- Category Tabs --}}
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-500">Category:</span>
                        <div class="flex flex-wrap items-center gap-1.5">
                            <button @click="selectedCategory = 'all'" 
                                    :class="selectedCategory === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                    class="px-3 py-1 rounded-xl text-xs font-semibold transition cursor-pointer">
                                All Categories
                            </button>
                            <button @click="selectedCategory = 'Core'" 
                                    :class="selectedCategory === 'Core' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-800 hover:bg-blue-100'"
                                    class="px-3 py-1 rounded-xl text-xs font-semibold transition cursor-pointer">
                                Core Subjects (Teras)
                            </button>
                            <button @click="selectedCategory = 'Elective'" 
                                    :class="selectedCategory === 'Elective' ? 'bg-indigo-600 text-white' : 'bg-indigo-50 text-indigo-800 hover:bg-indigo-100'"
                                    class="px-3 py-1 rounded-xl text-xs font-semibold transition cursor-pointer">
                                Elective Packages (Elektif)
                            </button>
                            <button @click="selectedCategory = 'Early Development'" 
                                    :class="selectedCategory === 'Early Development' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-800 hover:bg-amber-100'"
                                    class="px-3 py-1 rounded-xl text-xs font-semibold transition cursor-pointer">
                                Early Childhood
                            </button>
                        </div>
                    </div>

                    {{-- Elective Stream Filter (Available for Upper Secondary & All) --}}
                    <div class="flex items-center gap-2" x-show="selectedLevel === 'all' || selectedLevel === 'upper-secondary'">
                        <span class="text-xs font-bold text-slate-500">SPM Elective Package:</span>
                        <select x-model="selectedStream" class="bg-slate-100 border border-slate-200 text-slate-700 rounded-xl px-3 py-1 text-xs font-semibold outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                            <option value="all">All Packages</option>
                            <option value="STEM Pure Science">STEM Pure Science (Sains Tulen)</option>
                            <option value="Commerce & Accounting">Commerce &amp; Accounting (Perniagaan &amp; Perakaunan)</option>
                            <option value="STEM Computing">STEM Computing (Sains Komputer)</option>
                            <option value="Humanities & Arts">Humanities &amp; Arts (Seni Visual)</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- ── SUBJECT CARDS GRID ── --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @php
                    $bookIcon = 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253';
                    $mathIcon = 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z';
                    $scienceIcon = 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z';
                    $langIcon = 'M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129';
                    $artIcon = 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z';
                    $calcIcon = 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4';
                    $lightningIcon = 'M13 10V3L4 14h7v7l9-11h-7z';
                    $flaskIcon = 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z';
                    $dnaIcon = 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z';
                    $chartIcon = 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6';
                    $codeIcon = 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4';
                @endphp

                @foreach($subjects as $subject)
                    @php
                        $jsonSubject = json_encode([
                            'id'             => $subject->id,
                            'name'           => $subject->name,
                            'code'           => $subject->code,
                            'education_type' => $subject->education_type,
                            'stage'          => $subject->stage,
                            'form_year'      => $subject->form_year,
                            'category'       => $subject->category,
                            'stream_package' => $subject->stream_package,
                            'description'    => $subject->description,
                        ]);

                        // Header Theme Styling
                        $tierTheme = match($subject->education_type) {
                            'kindergarten' => [
                                'cardBorder'  => 'hover:border-amber-400',
                                'badgeBg'     => 'bg-amber-50 text-amber-800 border-amber-200',
                                'iconBg'      => 'bg-gradient-to-tr from-amber-500 to-orange-400 text-white',
                                'stageTag'    => 'Kindergarten',
                            ],
                            'primary' => [
                                'cardBorder'  => 'hover:border-emerald-400',
                                'badgeBg'     => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                'iconBg'      => 'bg-gradient-to-tr from-emerald-600 to-teal-500 text-white',
                                'stageTag'    => 'Primary KSSR',
                            ],
                            default => [
                                'cardBorder'  => ($subject->stage === 'Lower Secondary' ? 'hover:border-sky-400' : 'hover:border-purple-400'),
                                'badgeBg'     => ($subject->stage === 'Lower Secondary' ? 'bg-sky-50 text-sky-800 border-sky-200' : 'bg-purple-50 text-purple-800 border-purple-200'),
                                'iconBg'      => ($subject->stage === 'Lower Secondary' ? 'bg-gradient-to-tr from-sky-600 to-blue-500 text-white' : 'bg-gradient-to-tr from-purple-600 to-indigo-600 text-white'),
                                'stageTag'    => ($subject->stage === 'Lower Secondary' ? 'Lower Sec (F1–3)' : 'Upper Sec (SPM)'),
                            ]
                        };

                        $svgD = match(true) {
                            str_contains(strtolower($subject->name), 'math') => $mathIcon,
                            str_contains(strtolower($subject->name), 'fizik') || str_contains(strtolower($subject->name), 'physics') => $lightningIcon,
                            str_contains(strtolower($subject->name), 'kimia') || str_contains(strtolower($subject->name), 'chemistry') => $flaskIcon,
                            str_contains(strtolower($subject->name), 'biologi') || str_contains(strtolower($subject->name), 'biology') => $dnaIcon,
                            str_contains(strtolower($subject->name), 'sains') || str_contains(strtolower($subject->name), 'science') => $scienceIcon,
                            str_contains(strtolower($subject->name), 'seni') || str_contains(strtolower($subject->name), 'art') || str_contains(strtolower($subject->name), 'kreativiti') => $artIcon,
                            str_contains(strtolower($subject->name), 'akaun') || str_contains(strtolower($subject->name), 'perakaunan') || str_contains(strtolower($subject->name), 'perniagaan') || str_contains(strtolower($subject->name), 'ekonomi') => $chartIcon,
                            str_contains(strtolower($subject->name), 'komputer') || str_contains(strtolower($subject->name), 'ask') || str_contains(strtolower($subject->name), 'rbt') => $codeIcon,
                            default => $bookIcon
                        };
                    @endphp

                    <div x-show="matchesSubject({{ $jsonSubject }})" 
                         class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs {{ $tierTheme['cardBorder'] }} hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
                        
                        {{-- Card Header --}}
                        <div class="space-y-4">
                            {{-- Stage Badge & Code Bar --}}
                            <div class="flex items-center justify-between gap-2">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $tierTheme['badgeBg'] }}">
                                    {{ $tierTheme['stageTag'] }}
                                </span>
                                <span class="font-mono text-[10px] font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $subject->code ?? 'KPM-' . $subject->id }}
                                </span>
                            </div>

                            {{-- Subject Icon & Name --}}
                            <div class="flex items-start gap-3.5 pt-1">
                                <div class="w-12 h-12 rounded-2xl {{ $tierTheme['iconBg'] }} flex items-center justify-center shrink-0 shadow-md group-hover:scale-105 transition-transform">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $svgD }}"/></svg>
                                </div>
                                <div>
                                    <h3 class="font-heading font-extrabold text-base text-slate-900 group-hover:text-blue-600 transition-colors leading-snug">
                                        {{ $subject->name }}
                                    </h3>
                                    <div class="flex items-center gap-1.5 mt-1">
                                        <span class="text-[11px] font-semibold text-slate-500">{{ $subject->form_year }}</span>
                                        <span class="text-slate-300">•</span>
                                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ $subject->category === 'Core' ? 'bg-blue-50 text-blue-700' : ($subject->category === 'Elective' ? 'bg-indigo-50 text-indigo-700' : 'bg-amber-50 text-amber-700') }}">
                                            {{ $subject->category === 'Core' ? 'Core (Teras)' : ($subject->category === 'Elective' ? 'Elective' : $subject->category) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Stream Package Tag if Applicable --}}
                            @if($subject->stream_package && $subject->stream_package !== 'General')
                                <div class="bg-indigo-50/70 border border-indigo-100 rounded-xl px-3 py-1.5 flex items-center gap-2">
                                    <span class="text-indigo-600 text-xs">📦</span>
                                    <span class="text-[11px] font-bold text-indigo-800 truncate">{{ $subject->stream_package }}</span>
                                </div>
                            @endif

                            {{-- DSKP Syllabus Objectives Description --}}
                            <p class="text-xs text-slate-600 font-normal leading-relaxed line-clamp-3">
                                {{ $subject->description ?? 'Comprehensive syllabus based on Malaysian KPM curriculum guidelines with chapter-by-chapter mastery checkpoints.' }}
                            </p>
                        </div>

                        {{-- Card Footer Actions --}}
                        <div class="pt-5 mt-4 border-t border-slate-100 space-y-2.5">
                            {{-- Topic Count Badge --}}
                            <div class="flex items-center justify-between text-xs text-slate-500 font-medium">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                    <span>{{ $subject->topics->count() }} Chapters</span>
                                </span>
                                <span class="text-[11px] font-bold text-emerald-600">Active Syllabus</span>
                            </div>

                            {{-- Action Button: Trigger Modal --}}
                            <button type="button" @click="openSubjectModal('{{ $subject->name }}')" class="w-full py-2.5 px-4 bg-slate-900 hover:bg-blue-600 text-white text-xs font-bold rounded-xl transition-all shadow-xs flex items-center justify-center gap-1.5 cursor-pointer group-hover:bg-blue-600">
                                <span>Explore Syllabus &amp; Preview</span>
                                <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Empty State --}}
            <div x-cloak class="bg-white rounded-3xl p-12 text-center border border-slate-200/80 shadow-xs max-w-md mx-auto space-y-3"
                 x-show="!document.querySelectorAll('.grid > div[style*=\'display: none\']').length === 0">
                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="font-heading font-bold text-base text-slate-800">No matching subjects found</h3>
                <p class="text-xs text-slate-500">Try adjusting your education level, category, or search filters above.</p>
                <button @click="selectedLevel = 'all'; selectedCategory = 'all'; selectedStream = 'all'; searchQuery = '';" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 transition cursor-pointer">
                    Reset All Filters
                </button>
            </div>

        </div>
    </section>

    {{-- Subject Preview Modal Component --}}
    <x-subject-modal />

</x-app-layout>
