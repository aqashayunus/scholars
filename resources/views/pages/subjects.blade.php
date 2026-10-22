<x-app-layout>
    <x-slot name="title">Academic Subjects &amp; Curriculum</x-slot>

    {{-- ══════════════════════════════════════════════════════════════════════════════ --}}
    {{-- HERO SECTION: CLEAN, PROFESSIONAL BLUE THEME                                    --}}
    {{-- ══════════════════════════════════════════════════════════════════════════════ --}}
    <section class="bg-gradient-to-br from-blue-950 via-blue-900 to-indigo-950 text-white py-14 sm:py-16 relative overflow-hidden">
        {{-- Subtle radial grid background --}}
        <div class="absolute inset-0 opacity-15 bg-[radial-gradient(#93c5fd_1px,transparent_1px)] [background-size:24px_24px]"></div>

        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 text-center space-y-4">
            <h1 class="font-heading font-black text-2xl sm:text-3xl md:text-4xl text-white tracking-tight leading-tight">
                Academic Subjects &amp; Curriculum
            </h1>

            <p class="text-blue-100/85 text-xs sm:text-sm md:text-base max-w-2xl mx-auto font-normal leading-relaxed">
                Structured across 8 standardized academic year partitions from Primary Standard 4–6 through Secondary Form 1–5 SPM.
            </p>

            {{-- Summary Stats Pills by Year Tiers --}}
            <div class="pt-2 flex flex-wrap items-center justify-center gap-2.5 max-w-2xl mx-auto text-xs">
                <div class="px-3.5 py-1.5 rounded-xl bg-blue-900/50 border border-blue-400/25 text-blue-100 backdrop-blur-xs">
                    <strong class="text-white font-bold">{{ $subjects->where('education_type', 'primary')->count() }}</strong> Primary (Year 4–6)
                </div>
                <div class="px-3.5 py-1.5 rounded-xl bg-blue-900/50 border border-blue-400/25 text-blue-100 backdrop-blur-xs">
                    <strong class="text-white font-bold">{{ $subjects->where('stage', 'Lower Secondary')->count() }}</strong> Lower Secondary (Form 1–3)
                </div>
                <div class="px-3.5 py-1.5 rounded-xl bg-blue-900/50 border border-blue-400/25 text-blue-100 backdrop-blur-xs">
                    <strong class="text-white font-bold">{{ $subjects->where('stage', 'Upper Secondary')->count() }}</strong> Upper Secondary (Form 4–5 SPM)
                </div>
                <div class="px-3.5 py-1.5 rounded-xl bg-blue-900/50 border border-blue-400/25 text-blue-100 backdrop-blur-xs">
                    <strong class="text-white font-bold">{{ $subjects->count() }}</strong> Total Active
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════════════════════ --}}
    {{-- YEAR PARTITION DIRECTORY                                                        --}}
    {{-- ══════════════════════════════════════════════════════════════════════════════ --}}
    @php
        $years = [
            'Year 4' => [
                'stage'      => 'Primary School (KSSR Semakan)',
                'desc'       => 'Tahap 2 Core Foundation Curriculum',
                'code'       => 'Y4',
                'badgeColor' => 'bg-blue-50 text-blue-700 border-blue-200/80',
            ],
            'Year 5' => [
                'stage'      => 'Primary School (KSSR Semakan)',
                'desc'       => 'Tahap 2 Intermediate Conceptual Mastery',
                'code'       => 'Y5',
                'badgeColor' => 'bg-blue-50 text-blue-700 border-blue-200/80',
            ],
            'Year 6' => [
                'stage'      => 'Primary School (KSSR Semakan)',
                'desc'       => 'Primary Graduation & High School Transition Readiness',
                'code'       => 'Y6',
                'badgeColor' => 'bg-blue-50 text-blue-700 border-blue-200/80',
            ],
            'Form 1' => [
                'stage'      => 'Lower Secondary (KSSM)',
                'desc'       => 'Tingkatan 1 Secondary Foundation & Exploratory Core',
                'code'       => 'F1',
                'badgeColor' => 'bg-blue-50 text-blue-700 border-blue-200/80',
            ],
            'Form 2' => [
                'stage'      => 'Lower Secondary (KSSM)',
                'desc'       => 'Tingkatan 2 Progressive Curriculum & Skill Development',
                'code'       => 'F2',
                'badgeColor' => 'bg-blue-50 text-blue-700 border-blue-200/80',
            ],
            'Form 3' => [
                'stage'      => 'Lower Secondary (KSSM)',
                'desc'       => 'Tingkatan 3 Assessment & SPM Stream Pathway Preparation',
                'code'       => 'F3',
                'badgeColor' => 'bg-blue-50 text-blue-700 border-blue-200/80',
            ],
            'Form 4' => [
                'stage'      => 'Upper Secondary (SPM)',
                'desc'       => 'SPM Core & Specialized Electives (STEM, Commerce & Arts)',
                'code'       => 'F4',
                'badgeColor' => 'bg-blue-50 text-blue-700 border-blue-200/80',
            ],
            'Form 5' => [
                'stage'      => 'Upper Secondary (SPM)',
                'desc'       => 'Final SPM Examination Intensive Mastery & Past-Year Preparation',
                'code'       => 'F5',
                'badgeColor' => 'bg-blue-50 text-blue-700 border-blue-200/80',
            ],
        ];

        $bookIcon = 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253';
        $mathIcon = 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z';
        $scienceIcon = 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z';
        $langIcon = 'M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129';
        $lightningIcon = 'M13 10V3L4 14h7v7l9-11h-7z';
        $flaskIcon = 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z';
        $dnaIcon = 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z';
        $chartIcon = 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6';
        $codeIcon = 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4';
    @endphp

    <section class="py-8 sm:py-10 bg-slate-50/80 min-h-screen" 
             x-data="{ 
                 selectedYear: 'all', 
                 searchQuery: '',
                 allSubjects: {{ Js::from($subjects->map(fn($s) => [
                     'id'             => $s->id,
                     'name'           => $s->display_name ?? $s->name,
                     'code'           => $s->code,
                     'education_type' => $s->education_type,
                     'stage'          => $s->stage,
                     'form_year'      => $s->form_year,
                     'category'       => $s->category,
                     'stream_package' => $s->stream_package,
                     'description'    => $s->description,
                 ])) }},
                 matchesSubject(sub) {
                     if (this.searchQuery.trim() === '') return true;
                     const q = this.searchQuery.toLowerCase().trim();
                     const text = (sub.name + ' ' + (sub.code || '') + ' ' + (sub.stage || '') + ' ' + (sub.stream_package || '') + ' ' + (sub.description || '')).toLowerCase();
                     return text.includes(q);
                 },
                 yearHasMatches(yearName) {
                     const yearSubs = this.allSubjects.filter(s => s.form_year === yearName);
                     return yearSubs.some(s => this.matchesSubject(s));
                 },
                 get hasAnyResults() {
                     return this.allSubjects.some(s => {
                         const matchYear = (this.selectedYear === 'all' || this.selectedYear === s.form_year);
                         return matchYear && this.matchesSubject(s);
                     });
                 }
             }">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- ── YEAR PARTITION SELECTOR BAR (CLEAN, MINIMAL & PROFESSIONAL) ── --}}
            <div class="sticky top-20 z-20 bg-white/95 backdrop-blur-md rounded-2xl p-3 sm:p-4 border border-blue-100 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3">
                
                {{-- Year Partition Pills --}}
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
                    <button @click="selectedYear = 'all'"
                            :class="selectedYear === 'all' ? 'bg-blue-600 text-white font-bold shadow-xs' : 'bg-slate-50 text-slate-700 hover:text-blue-600 hover:bg-blue-50/70 font-medium border border-slate-200/70'"
                            class="px-3.5 py-1.5 rounded-xl text-xs whitespace-nowrap transition-all cursor-pointer">
                        All Years ({{ $subjects->count() }})
                    </button>

                    @foreach($years as $yearKey => $yearMeta)
                        @php $yearCount = $subjects->where('form_year', $yearKey)->count(); @endphp
                        @if($yearCount > 0)
                            <button @click="selectedYear = '{{ $yearKey }}'"
                                    :class="selectedYear === '{{ $yearKey }}' ? 'bg-blue-600 text-white font-bold shadow-xs' : 'bg-slate-50 text-slate-700 hover:text-blue-600 hover:bg-blue-50/70 font-medium border border-slate-200/70'"
                                    class="px-3 py-1.5 rounded-xl text-xs whitespace-nowrap transition-all cursor-pointer">
                                {{ $yearKey }} ({{ $yearCount }})
                            </button>
                        @endif
                    @endforeach
                </div>

                {{-- Compact Search Input --}}
                <div class="relative w-full md:w-64 shrink-0">
                    <input x-model="searchQuery" type="text" placeholder="Search subject or code..." 
                           class="w-full bg-blue-50/30 border border-blue-200/80 rounded-xl pl-9 pr-8 py-1.5 text-xs text-slate-800 outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 focus:bg-white transition">
                    <svg class="w-3.5 h-3.5 text-blue-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <button x-show="searchQuery.length > 0" @click="searchQuery = ''" class="absolute right-2.5 top-2 text-slate-400 hover:text-blue-600 text-xs">✕</button>
                </div>
            </div>

            {{-- ── YEAR PARTITIONED SECTIONS ── --}}
            <div class="space-y-10 sm:space-y-12">
                @foreach($years as $yearKey => $yearMeta)
                    @php
                        $yearSubjects = $subjects->where('form_year', $yearKey);
                    @endphp

                    @if($yearSubjects->isNotEmpty())
                        <div x-show="(selectedYear === 'all' || selectedYear === '{{ $yearKey }}') && yearHasMatches('{{ $yearKey }}')"
                             x-cloak
                             id="partition-{{ Str::slug($yearKey) }}"
                             class="space-y-4">
                            
                            {{-- Partition Header Banner --}}
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white px-5 py-3.5 rounded-2xl border border-blue-100 shadow-2xs">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white font-heading font-black text-xs sm:text-sm flex items-center justify-center shadow-xs shrink-0">
                                        {{ $yearMeta['code'] }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h2 class="font-heading font-bold text-base sm:text-lg text-slate-900 leading-none">
                                                {{ $yearKey }}
                                            </h2>
                                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $yearMeta['badgeColor'] }}">
                                                {{ $yearMeta['stage'] }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-500 font-normal mt-0.5">
                                            {{ $yearMeta['desc'] }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-semibold text-blue-700 bg-blue-50 px-3 py-1 rounded-xl border border-blue-100">
                                        {{ $yearSubjects->count() }} Subjects
                                    </span>
                                </div>
                            </div>

                            {{-- Partition Subject Cards Grid --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 {{ $yearSubjects->count() > 5 ? 'lg:grid-cols-4' : 'lg:grid-cols-5' }} gap-4">
                                @foreach($yearSubjects as $subject)
                                    @php
                                        $jsonSubject = json_encode([
                                            'id'             => $subject->id,
                                            'name'           => $subject->display_name ?? $subject->name,
                                            'code'           => $subject->code,
                                            'education_type' => $subject->education_type,
                                            'stage'          => $subject->stage,
                                            'form_year'      => $subject->form_year,
                                            'category'       => $subject->category,
                                            'stream_package' => $subject->stream_package,
                                            'description'    => $subject->description,
                                        ]);

                                        $svgD = match(true) {
                                            str_contains(strtolower($subject->name), 'math') => $mathIcon,
                                            str_contains(strtolower($subject->name), 'fizik') || str_contains(strtolower($subject->name), 'physics') => $lightningIcon,
                                            str_contains(strtolower($subject->name), 'kimia') || str_contains(strtolower($subject->name), 'chemistry') => $flaskIcon,
                                            str_contains(strtolower($subject->name), 'biologi') || str_contains(strtolower($subject->name), 'biology') => $dnaIcon,
                                            str_contains(strtolower($subject->name), 'sains') || str_contains(strtolower($subject->name), 'science') => $scienceIcon,
                                            str_contains(strtolower($subject->name), 'akaun') || str_contains(strtolower($subject->name), 'perakaunan') || str_contains(strtolower($subject->name), 'perniagaan') || str_contains(strtolower($subject->name), 'ekonomi') => $chartIcon,
                                            str_contains(strtolower($subject->name), 'komputer') || str_contains(strtolower($subject->name), 'ask') || str_contains(strtolower($subject->name), 'rbt') => $codeIcon,
                                            str_contains(strtolower($subject->name), 'bahasa') || str_contains(strtolower($subject->name), 'english') || str_contains(strtolower($subject->name), 'melayu') => $langIcon,
                                            default => $bookIcon
                                        };
                                    @endphp

                                    <div x-show="matchesSubject({{ $jsonSubject }})" 
                                         class="bg-white rounded-2xl p-4 sm:p-5 border border-blue-100 shadow-2xs hover:shadow-md hover:border-blue-300 hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between group">
                                        
                                        {{-- Top Content Area --}}
                                        <div class="space-y-3">
                                            {{-- Top Metadata Row --}}
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="text-[11px] font-semibold {{ $subject->category === 'Core' ? 'text-blue-700 bg-blue-50/80 border border-blue-200/60' : 'text-slate-600 bg-slate-50 border border-slate-200/60' }} px-2.5 py-0.5 rounded-full">
                                                    {{ $subject->category === 'Core' ? 'Core' : ($subject->category === 'Elective' ? 'Elective' : $subject->category) }}
                                                </span>
                                                <span class="font-mono text-[10px] font-semibold text-blue-600/90 bg-blue-50/60 px-2 py-0.5 rounded border border-blue-100">
                                                    {{ $subject->code ?? 'KPM-' . $subject->id }}
                                                </span>
                                            </div>

                                            {{-- Subject Icon + Title --}}
                                            <div class="flex items-start gap-2.5 pt-0.5">
                                                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 border border-blue-200/80 group-hover:bg-blue-600 group-hover:text-white group-hover:border-blue-600 transition-all duration-200 flex items-center justify-center shrink-0 shadow-2xs">
                                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $svgD }}"/></svg>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <h3 class="font-heading font-bold text-sm text-slate-900 group-hover:text-blue-600 transition-colors leading-snug">
                                                        {{ $subject->display_name ?? $subject->name }}
                                                    </h3>
                                                    @if($subject->stream_package && $subject->stream_package !== 'General')
                                                        <span class="inline-block text-[10px] text-blue-800 font-medium truncate max-w-full mt-0.5">
                                                            {{ $subject->stream_package }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- Syllabus Description --}}
                                            <p class="text-xs text-slate-500 font-normal leading-relaxed line-clamp-2">
                                                {{ $subject->description ?? 'Structured syllabus aligned with Malaysian KPM curriculum standards.' }}
                                            </p>
                                        </div>

                                        {{-- Card Bottom Area --}}
                                        <div class="pt-3.5 mt-3.5 border-t border-slate-100 flex items-center justify-between gap-2">
                                            <span class="text-xs text-slate-500 font-medium flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                                <span>{{ $subject->topics->count() }} Ch.</span>
                                            </span>

                                            <button type="button" @click="openSubjectModal('{{ $subject->display_name ?? $subject->name }}')" class="px-3 py-1 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-semibold rounded-lg shadow-xs hover:shadow transition-all flex items-center gap-1 cursor-pointer">
                                                <span>Preview</span>
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Empty State (Clean & Minimalist Blue Theme) --}}
            <div x-cloak class="bg-white rounded-2xl p-10 text-center border border-blue-100 shadow-2xs max-w-md mx-auto space-y-3"
                 x-show="!hasAnyResults">
                <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mx-auto border border-blue-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="font-heading font-bold text-sm text-slate-900">No matching subjects found</h3>
                <p class="text-xs text-slate-500">Try adjusting your year selection or search query.</p>
                <button @click="selectedYear = 'all'; searchQuery = '';" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-semibold hover:bg-blue-700 transition cursor-pointer shadow-xs">
                    Show All Years
                </button>
            </div>

        </div>
    </section>

    {{-- Subject Preview Modal Component --}}
    <x-subject-modal />

</x-app-layout>
