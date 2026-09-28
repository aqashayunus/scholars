<x-app-layout>
    <x-slot name="title">{{ $subject->name }}</x-slot>

    @php
        $subjectName = $subject->name;
        $subjectLevel = $subject->level ?? 'SPM & IGCSE';
        
        // Educators query
        $educators = $subject->teachers->count() > 0 
            ? $subject->teachers 
            : \App\Models\Teacher::with('user')->take(3)->get();
            
        $nameLower = strtolower($subjectName);

        // Comprehensive Subject Theme Matrix (Background images, colors, graphics, floating symbols)
        $subjectMeta = [
            'bg_image' => 'https://images.unsplash.com/photo-1509228468518-180dd4864904?auto=format&fit=crop&w=1600&q=80',
            'gradient'  => 'from-slate-950 via-blue-950/95 to-slate-900',
            'accent'    => 'text-amber-400',
            'glow1'     => 'bg-blue-600/35',
            'glow2'     => 'bg-amber-500/25',
            'badge'     => 'Mathematics Hub',
            'type'      => 'math',
            'tags'      => ['∑ (x - μ)²', 'π ≈ 3.14159', 'f(x) = dx/dy', 'A = ½ab sin C', '√x² + y²'],
            'pill_bg'   => 'bg-blue-500/20 text-blue-300 border-blue-400/30',
        ];

        if (Str::contains($nameLower, ['add', 'tambahan'])) {
            $subjectMeta = [
                'bg_image' => 'https://images.unsplash.com/photo-1635070041078-e363dbe005cb?auto=format&fit=crop&w=1600&q=80',
                'gradient'  => 'from-slate-950 via-indigo-950/95 to-purple-950',
                'accent'    => 'text-purple-300',
                'glow1'     => 'bg-purple-600/35',
                'glow2'     => 'bg-pink-500/25',
                'badge'     => 'Add Maths Hub',
                'type'      => 'addmath',
                'tags'      => ['∫ sin(x) dx', 'e^(iπ) + 1 = 0', 'dy/dx = f\'(x)', 'logₐ(b) = c', 'b² - 4ac ≥ 0'],
                'pill_bg'   => 'bg-purple-500/20 text-purple-300 border-purple-400/30',
            ];
        } elseif (Str::contains($nameLower, ['physic', 'fizik'])) {
            $subjectMeta = [
                'bg_image' => 'https://images.unsplash.com/photo-1636466497217-26a8cbeaf0aa?auto=format&fit=crop&w=1600&q=80',
                'gradient'  => 'from-slate-950 via-sky-950/95 to-blue-950',
                'accent'    => 'text-sky-300',
                'glow1'     => 'bg-cyan-500/35',
                'glow2'     => 'bg-blue-600/25',
                'badge'     => 'Physics Hub',
                'type'      => 'physics',
                'tags'      => ['E = mc²', 'F = ma', 'v = fλ', 'PV = nRT', 'F = G(m₁m₂)/r²'],
                'pill_bg'   => 'bg-sky-500/20 text-sky-300 border-sky-400/30',
            ];
        } elseif (Str::contains($nameLower, ['chem', 'kimia'])) {
            $subjectMeta = [
                'bg_image' => 'https://images.unsplash.com/photo-1603126857599-f6e157fa2fe6?auto=format&fit=crop&w=1600&q=80',
                'gradient'  => 'from-slate-950 via-purple-950/95 to-fuchsia-950',
                'accent'    => 'text-fuchsia-300',
                'glow1'     => 'bg-fuchsia-600/35',
                'glow2'     => 'bg-purple-600/25',
                'badge'     => 'Chemistry Hub',
                'type'      => 'chemistry',
                'tags'      => ['H₂O + CO₂', 'pH = -log[H+]', '2Na + Cl₂ → 2NaCl', 'Mol = M × V', 'C₆H₁₂O₆'],
                'pill_bg'   => 'bg-fuchsia-500/20 text-fuchsia-300 border-fuchsia-400/30',
            ];
        } elseif (Str::contains($nameLower, ['biol', 'biologi'])) {
            $subjectMeta = [
                'bg_image' => 'https://images.unsplash.com/photo-1530026405186-ed1f139313f8?auto=format&fit=crop&w=1600&q=80',
                'gradient'  => 'from-slate-950 via-emerald-950/95 to-teal-950',
                'accent'    => 'text-emerald-300',
                'glow1'     => 'bg-emerald-600/35',
                'glow2'     => 'bg-teal-500/25',
                'badge'     => 'Biology Hub',
                'type'      => 'biology',
                'tags'      => ['DNA Helix', 'ATP Synthesis', 'Photosynthesis', 'Mitosis & Meiosis', 'Enzyme Action'],
                'pill_bg'   => 'bg-emerald-500/20 text-emerald-300 border-emerald-400/30',
            ];
        } elseif (Str::contains($nameLower, ['scien', 'sains'])) {
            $subjectMeta = [
                'bg_image' => 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?auto=format&fit=crop&w=1600&q=80',
                'gradient'  => 'from-slate-950 via-teal-950/95 to-cyan-950',
                'accent'    => 'text-teal-300',
                'glow1'     => 'bg-teal-600/35',
                'glow2'     => 'bg-sky-500/25',
                'badge'     => 'Science Hub',
                'type'      => 'science',
                'tags'      => ['Scientific Method', 'Ecosystems', 'Chemical Reactions', 'Force & Motion', 'Energy Conservation'],
                'pill_bg'   => 'bg-teal-500/20 text-teal-300 border-teal-400/30',
            ];
        } elseif (Str::contains($nameLower, ['melayu', 'bm'])) {
            $subjectMeta = [
                'bg_image' => 'https://images.unsplash.com/photo-1457369804613-52c61a468e7d?auto=format&fit=crop&w=1600&q=80',
                'gradient'  => 'from-stone-950 via-amber-950/95 to-slate-950',
                'accent'    => 'text-amber-300',
                'glow1'     => 'bg-amber-600/35',
                'glow2'     => 'bg-orange-500/25',
                'badge'     => 'Bahasa Melayu Hub',
                'type'      => 'melayu',
                'tags'      => ['Subjek & Predikat', 'Peribahasa', 'Karangan Cemerlang', 'KOMSAS SPM', 'Rumusan & Ringkasan'],
                'pill_bg'   => 'bg-amber-500/20 text-amber-300 border-amber-400/30',
            ];
        } elseif (Str::contains($nameLower, ['english', 'inggeris'])) {
            $subjectMeta = [
                'bg_image' => 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?auto=format&fit=crop&w=1600&q=80',
                'gradient'  => 'from-slate-950 via-blue-950/95 to-slate-900',
                'accent'    => 'text-yellow-300',
                'glow1'     => 'bg-blue-600/35',
                'glow2'     => 'bg-amber-500/25',
                'badge'     => 'English Hub',
                'type'      => 'english',
                'tags'      => ['Essay Writing', 'Advanced Grammar', 'CEFR C1/B2', 'Reading Comprehension', 'Oral Fluency'],
                'pill_bg'   => 'bg-yellow-500/20 text-yellow-300 border-yellow-400/30',
            ];
        } elseif (Str::contains($nameLower, ['sejarah', 'histor'])) {
            $subjectMeta = [
                'bg_image' => 'https://images.unsplash.com/photo-1461360370896-922624d12aa1?auto=format&fit=crop&w=1600&q=80',
                'gradient'  => 'from-stone-950 via-amber-950/95 to-stone-900',
                'accent'    => 'text-amber-300',
                'glow1'     => 'bg-amber-700/35',
                'glow2'     => 'bg-yellow-600/25',
                'badge'     => 'Sejarah Hub',
                'type'      => 'history',
                'tags'      => ['Pembentukan Malaysia', 'Kertas 2 KBAT', 'Fakta & Kronologi', 'Perlembagaan', 'Sejarah SPM'],
                'pill_bg'   => 'bg-amber-500/20 text-amber-300 border-amber-400/30',
            ];
        }
    @endphp

    <div class="bg-gray-50/60 py-6 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

            {{-- ══════════════════════════════════════════════
                 BREADCRUMBS
            ══════════════════════════════════════════════ --}}
            <nav class="flex items-center gap-2 text-xs sm:text-sm text-gray-500 font-medium">
                <a href="{{ route('home') }}" class="hover:text-primary transition-colors">Home</a>
                <span>&gt;</span>
                <a href="{{ route('subjects') }}" class="hover:text-primary transition-colors">Subjects</a>
                <span>&gt;</span>
                <span class="text-gray-900 font-semibold">{{ $subjectName }}</span>
            </nav>

            {{-- ══════════════════════════════════════════════
                 SECTION 1: HERO BANNER WITH FULL SUBJECT PICTURE BACKGROUND
            ══════════════════════════════════════════════ --}}
            <section class="text-white rounded-3xl relative overflow-hidden p-8 sm:p-12 lg:p-14 shadow-2xl border border-white/10">
                
                {{-- Full Subject Background Picture --}}
                <div class="absolute inset-0 z-0">
                    <img src="{{ $subjectMeta['bg_image'] }}" alt="{{ $subjectName }}" class="w-full h-full object-cover object-center scale-105 filter brightness-90 sm:brightness-100">
                    {{-- Dark Overlay for contrast: dark backdrop on mobile for readability, lighter on desktop --}}
                    <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-950/90 to-slate-950/80 sm:to-slate-950/40"></div>
                </div>

                {{-- Subtle Ambient Glow --}}
                <div class="absolute -top-24 -right-24 w-96 h-96 {{ $subjectMeta['glow1'] }} rounded-full blur-3xl pointer-events-none opacity-40"></div>

                {{-- Banner Content --}}
                <div class="relative z-10 max-w-3xl">
                    <div class="flex flex-wrap items-center gap-3 mb-4">
                        <span class="inline-block bg-white/15 backdrop-blur-md {{ $subjectMeta['accent'] }} border border-white/20 text-xs font-extrabold px-3.5 py-1.5 rounded-full uppercase tracking-wider">
                            {{ strtoupper($subjectName) }} HUB
                        </span>
                        <span class="inline-block bg-white/10 text-white/90 border border-white/15 text-xs font-bold px-3 py-1.5 rounded-full">
                            {{ $subjectLevel }}
                        </span>
                    </div>

                    <h1 class="font-heading font-extrabold text-3xl sm:text-4xl lg:text-5xl text-white leading-tight mb-4 tracking-tight">
                        Master <span class="{{ $subjectMeta['accent'] }}">{{ $subjectName }}</span><br class="hidden sm:inline"> with Confidence
                    </h1>

                    <p class="text-white/90 text-base sm:text-lg leading-relaxed mb-6 font-normal">
                        Learn from Subject Specialist Educators through structured digital lessons, curated practice sets, and examination-proven strategies.
                    </p>

                    {{-- Quick Features Pill Ribbon --}}
                    <div class="flex flex-wrap gap-4 text-xs font-semibold text-white/90 mb-8 pt-3 border-t border-white/15">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            100% MOE-Aligned Syllabus
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Live Classes + 24/7 Replays
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Exam Technique Mastery
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-4">
                        <a href="{{ route('free-trial') }}" class="btn-accent text-base px-8 py-3.5 shadow-lg shadow-accent/20 hover:scale-105 transition-all">
                            Book Free Trial <span class="ml-1.5">&rarr;</span>
                        </a>
                        <a href="#class-schedule" class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-md border border-white/30 text-white font-semibold px-7 py-3.5 rounded-full hover:bg-white/20 transition-all">
                            View Class Schedule
                        </a>
                    </div>
                </div>
            </section>



            {{-- ══════════════════════════════════════════════
                 SECTION 3: EDUCATORS & WHY LEARN WITH US
            ══════════════════════════════════════════════ --}}
            <div class="grid lg:grid-cols-12 gap-8">
                
                {{-- Meet Educators (7 Cols) --}}
                <div class="lg:col-span-7 bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <span class="text-xs font-extrabold text-primary uppercase tracking-wider">Expert Guidance</span>
                                <h2 class="font-heading font-extrabold text-charcoal text-xl sm:text-2xl mt-0.5">Meet Your {{ $subjectName }} Educators</h2>
                            </div>
                            <a href="{{ route('teachers') }}" class="text-xs sm:text-sm font-bold text-primary hover:underline flex items-center gap-1">
                                View All &rarr;
                            </a>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                            @foreach ($educators->take(3) as $index => $t)
                            @php
                                $defaultTeacherPhotos = [
                                    asset('images/teacher-daniel.png'),
                                    asset('images/teacher-aisyah.png'),
                                    asset('images/teacher-chong.png'),
                                ];
                                $defaultTeacherNames = [
                                    'Mr. Daniel Wong',
                                    'Cikgu Aisyah',
                                    'Mr. Chong Wei',
                                ];
                                $defaultQualifications = [
                                    'MSc in Mathematics',
                                    'BSc (Hons) Mathematics',
                                    'MSc in Applied Mathematics',
                                ];
                                $photo = !empty($t->photo_url) ? asset($t->photo_url) : ($defaultTeacherPhotos[$index % count($defaultTeacherPhotos)]);
                                $teacherName = $t->user->name ?? ($defaultTeacherNames[$index % count($defaultTeacherNames)]);
                                $qualification = $t->qualification ?? ($defaultQualifications[$index % count($defaultQualifications)]);
                            @endphp
                            <div class="bg-white rounded-3xl p-4 border border-gray-100 shadow-sm hover:shadow-card hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group text-center">
                                <div>
                                    {{-- Photo Header --}}
                                    <div class="w-full h-44 rounded-2xl overflow-hidden mb-4 bg-slate-50 relative">
                                        <img src="{{ $photo }}" alt="{{ $teacherName }}" class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-300">
                                    </div>
                                    
                                    {{-- Name --}}
                                    <h3 class="font-heading font-extrabold text-charcoal text-base mb-1 group-hover:text-primary transition-colors">{{ $teacherName }}</h3>
                                    
                                    {{-- Qualification & Experience --}}
                                    <p class="text-muted text-xs font-medium leading-tight mb-1">{{ $qualification }}</p>
                                    <p class="text-muted text-xs font-medium mb-3">{{ $t->experience_years ?? (10 - $index * 2) }}+ Years Experience</p>
                                    
                                    {{-- Rating --}}
                                    <div class="flex items-center justify-center gap-1 text-xs font-extrabold mb-4">
                                        <span class="text-amber-400">★</span>
                                        <span class="text-charcoal">{{ number_format($t->rating ?? (4.9 - $index * 0.1), 1) }}</span>
                                        <span class="text-muted font-normal text-xs">({{ 128 - $index * 16 }})</span>
                                    </div>
                                </div>

                                {{-- Link --}}
                                <div>
                                    <a href="{{ route('teacher.show', $t) }}" class="text-xs sm:text-sm font-extrabold text-primary hover:underline inline-flex items-center gap-1 group-hover:gap-2 transition-all">
                                        View Profile &rarr;
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Why Learn With Us (5 Cols) --}}
                <div class="lg:col-span-5 bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-sm flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-extrabold text-primary uppercase tracking-wider">The Olympian Advantage</span>
                        <h2 class="font-heading font-extrabold text-charcoal text-xl sm:text-2xl mb-6 mt-0.5">Why Learn {{ $subjectName }} with Us?</h2>

                        <div class="grid grid-cols-2 gap-4">
                            @php
                                $whyFeatures = [
                                    ['icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'title' => 'Subject Specialist Educators'],
                                    ['icon' => 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z', 'title' => 'Live & Interactive Classes'],
                                    ['icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', 'title' => "Olympian {$subjectName} Notes"],
                                    ['icon' => 'M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'title' => '24/7 Recorded Replays'],
                                    ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'title' => 'Weekly Quiz & Practice'],
                                    ['icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6', 'title' => 'AI Progress Tracking'],
                                ];
                            @endphp

                            @foreach ($whyFeatures as $f)
                            <div class="flex items-start gap-3 p-3.5 rounded-2xl bg-surface border border-gray-100">
                                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $f['icon'] }}"/></svg>
                                </div>
                                <span class="text-charcoal font-bold text-xs leading-snug">{{ $f['title'] }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════
                 SECTION 4: LEARNING FRAMEWORK & OUTCOMES
            ══════════════════════════════════════════════ --}}
            <div class="grid lg:grid-cols-12 gap-8">
                
                {{-- Olympian Learning Framework (7 Cols) --}}
                <div class="lg:col-span-7 bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-sm flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-extrabold text-primary uppercase tracking-wider">Methodology</span>
                        <h2 class="font-heading font-extrabold text-charcoal text-xl sm:text-2xl mb-6 mt-0.5">Olympian Learning Framework™</h2>
                        
                        {{-- 7 Step Nodes --}}
                        <div class="grid grid-cols-4 sm:grid-cols-7 gap-2 mb-6 text-center">
                            @php
                                $steps = [
                                    ['icon' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z', 'label' => 'Discover'],
                                    ['icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z', 'label' => 'Understand'],
                                    ['icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 012.828 0L20.17 4.172a2 2 0 010 2.828l-8.486 8.486L7 16l1.514-4.514 8.486-8.486z', 'label' => 'Practice'],
                                    ['icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z', 'label' => 'Apply'],
                                    ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'label' => 'Evaluate'],
                                    ['icon' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z', 'label' => 'Master'],
                                    ['icon' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z', 'label' => 'Excel'],
                                ];
                            @endphp

                            @foreach ($steps as $st)
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-1.5 border border-blue-100 shadow-2xs">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $st['icon'] }}"/></svg>
                                </div>
                                <span class="text-[11px] font-extrabold text-charcoal">{{ $st['label'] }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <p class="text-muted text-xs text-center leading-relaxed">
                        A structured approach that ensures every student builds strong foundations and achieves academic excellence.
                    </p>
                </div>

                {{-- Learning Outcomes (5 Cols) --}}
                <div class="lg:col-span-5 bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-sm flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-extrabold text-primary uppercase tracking-wider">Key Objectives</span>
                        <h2 class="font-heading font-extrabold text-charcoal text-xl sm:text-2xl mb-6 mt-0.5">Learning Outcomes</h2>

                        <div class="space-y-3">
                            @foreach ([
                                'Strong Analytical & Conceptual Thinking',
                                'Advanced Problem Solving Techniques',
                                'Examination Confidence & Time Management',
                                'Higher Order Thinking Skills (HOTS / KBAT)'
                            ] as $outcome)
                            <div class="flex items-center gap-3 p-3 bg-surface rounded-2xl border border-gray-100">
                                <div class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center flex-shrink-0 text-xs font-bold shadow-2xs">
                                    &check;
                                </div>
                                <span class="text-charcoal font-bold text-xs sm:text-sm">{{ $outcome }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════
                 SECTION 5: CLASS INFO, TRACKING & SUCCESS
            ══════════════════════════════════════════════ --}}
            <div class="grid lg:grid-cols-3 gap-8" id="class-schedule">
                
                {{-- Class Information --}}
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-sm flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-extrabold text-primary uppercase tracking-wider">Format</span>
                        <h2 class="font-heading font-extrabold text-charcoal text-xl sm:text-2xl mb-6 mt-0.5">Class Information</h2>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                                <div class="flex items-center gap-2.5 text-xs font-bold text-muted">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    Live Classes
                                </div>
                                <span class="bg-blue-50 text-blue-700 text-xs font-bold px-3 py-1 rounded-full">Interactive</span>
                            </div>

                            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                                <div class="flex items-center gap-2.5 text-xs font-bold text-muted">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Recorded Classes
                                </div>
                                <span class="bg-blue-50 text-blue-700 text-xs font-bold px-3 py-1 rounded-full">On-Demand</span>
                            </div>

                            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                                <div class="flex items-center gap-2.5 text-xs font-bold text-muted">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Duration
                                </div>
                                <span class="text-xs font-bold text-charcoal">90 Minutes</span>
                            </div>

                            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                                <div class="flex items-center gap-2.5 text-xs font-bold text-muted">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    Frequency
                                </div>
                                <span class="text-xs font-bold text-charcoal">2 - 3 Classes / Week</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2.5 text-xs font-bold text-muted">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    Class Size
                                </div>
                                <span class="text-xs font-bold text-charcoal">Small & Focused</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Assessment & Tracking --}}
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-sm flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-extrabold text-primary uppercase tracking-wider">Evaluation</span>
                        <h2 class="font-heading font-extrabold text-charcoal text-xl sm:text-2xl mb-6 mt-0.5">Assessment & Tracking</h2>

                        <div class="space-y-3.5">
                            @foreach ([
                                ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'title' => 'Weekly Quiz'],
                                ['icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'title' => 'Monthly Assessment'],
                                ['icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 012.828 0L20.17 4.172a2 2 0 010 2.828l-8.486 8.486L7 16l1.514-4.514 8.486-8.486z', 'title' => 'Mock Examination'],
                                ['icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6', 'title' => 'AI-Powered Progress Analytics'],
                                ['icon' => 'M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z', 'title' => 'Personalised Mentor Feedback'],
                            ] as $ass)
                            <div class="flex items-center gap-3 p-3 bg-surface rounded-2xl border border-gray-100">
                                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $ass['icon'] }}"/></svg>
                                </div>
                                <span class="text-charcoal font-bold text-xs sm:text-sm">{{ $ass['title'] }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Student Success --}}
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-sm flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-extrabold text-primary uppercase tracking-wider">Results</span>
                        <h2 class="font-heading font-extrabold text-charcoal text-xl sm:text-2xl mb-6 mt-0.5">Student Success</h2>

                        <div class="bg-blue-50/60 rounded-2xl p-4 border border-blue-100 mb-6 text-center">
                            <p class="text-charcoal italic text-xs leading-relaxed mb-3">
                                "Olympian Scholars helped me understand concepts better and improved my confidence in solving difficult questions."
                            </p>
                            <p class="text-blue-900 font-extrabold text-xs">&mdash; Lim Wei Xuan, SPM Student</p>
                        </div>

                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div class="p-2.5 bg-surface rounded-2xl border border-gray-100">
                                <div class="font-stats font-bold text-lg text-primary">95%</div>
                                <div class="text-muted text-[10px] leading-tight font-medium">Students Improved Grades</div>
                            </div>
                            <div class="p-2.5 bg-surface rounded-2xl border border-gray-100">
                                <div class="font-stats font-bold text-lg text-accent">A / A+</div>
                                <div class="text-muted text-[10px] leading-tight font-medium">SPM Top Achievers</div>
                            </div>
                            <div class="p-2.5 bg-surface rounded-2xl border border-gray-100">
                                <div class="font-stats font-bold text-lg text-primary">1000+</div>
                                <div class="text-muted text-[10px] leading-tight font-medium">Students Empowered</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════
                 SECTION 6: FREQUENTLY ASKED QUESTIONS (PREMIUM DROPDOWNS)
            ══════════════════════════════════════════════ --}}
            <section class="bg-white rounded-3xl p-6 sm:p-10 border border-gray-100 shadow-sm">
                <div class="max-w-3xl mx-auto text-center mb-10">
                    <span class="inline-block bg-primary-50 text-primary text-xs font-extrabold px-3.5 py-1 rounded-full uppercase tracking-wider mb-2">
                        Got Questions?
                    </span>
                    <h2 class="font-heading font-extrabold text-charcoal text-2xl sm:text-3xl tracking-tight">
                        Frequently Asked Questions
                    </h2>
                    <p class="text-muted text-xs sm:text-sm mt-2">
                        Everything you need to know about our {{ $subjectName }} tuition programme.
                    </p>
                </div>

                @php
                    $faqs = [
                        ['q' => 'Is this programme suitable for beginners?', 'a' => 'Yes! Our structured approach starts from fundamental concepts before progressing step-by-step to advanced examination topics.'],
                        ['q' => 'What is the class schedule?', 'a' => 'Classes are held 2-3 times per week in the evenings and weekends. Recorded replays are available on-demand 24/7 on your student portal.'],
                        ['q' => 'What materials will I receive?', 'a' => 'You receive complete digital access to Olympian Notes, Formula Books, Question Banks, and Exam Worksheets.'],
                        ['q' => 'Are the classes recorded?', 'a' => 'Yes, all live interactive sessions are recorded HD and uploaded to your dashboard within 24 hours.'],
                        ['q' => 'How long can I access the recordings?', 'a' => 'You have unlimited 24/7 access to all class recordings throughout your active subscription period.'],
                        ['q' => 'How can I book a free trial?', 'a' => 'Simply click the "Book Free Trial" button, select your preferred subject slot, and join your first live class completely free!'],
                    ];
                @endphp

                <div class="max-w-4xl mx-auto space-y-4">
                    @foreach ($faqs as $index => $faq)
                    <div x-data="{ open: {{ $index === 0 ? 'true' : 'false' }} }" 
                         class="rounded-2xl border transition-all duration-300 overflow-hidden"
                         :class="open ? 'bg-blue-50/40 border-primary/30 shadow-md ring-1 ring-primary/10' : 'bg-surface border-gray-100 hover:border-gray-200 hover:bg-white'">
                        
                        <button @click="open = !open" 
                                type="button" 
                                class="w-full p-5 sm:p-6 text-left flex items-center justify-between gap-4 focus:outline-none select-none">
                            
                            <div class="flex items-center gap-3.5">
                                {{-- Number Badge --}}
                                <span class="w-7 h-7 rounded-xl text-xs font-bold flex items-center justify-center flex-shrink-0 transition-colors"
                                      :class="open ? 'bg-primary text-white shadow-sm' : 'bg-gray-100 text-gray-500'">
                                    0{{ $index + 1 }}
                                </span>

                                <h3 class="font-heading font-extrabold text-charcoal text-sm sm:text-base transition-colors"
                                    :class="open ? 'text-primary' : 'text-charcoal'">
                                    {{ $faq['q'] }}
                                </h3>
                            </div>

                            {{-- Chevron Icon Button --}}
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 transition-all duration-300"
                                 :class="open ? 'bg-primary text-white rotate-180 shadow-sm' : 'bg-white text-gray-400 border border-gray-200'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </button>

                        <div x-show="open" 
                             x-collapse 
                             x-cloak 
                             class="px-5 sm:px-6 pb-6 pt-0">
                            <div class="flex gap-4 pt-3 border-t border-blue-100/60">
                                <div class="w-1 rounded-full bg-gradient-to-b from-primary to-accent flex-shrink-0"></div>
                                <p class="text-charcoal/80 text-xs sm:text-sm leading-relaxed font-normal">
                                    {{ $faq['a'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>

        </div>
    </div>
</x-app-layout>
