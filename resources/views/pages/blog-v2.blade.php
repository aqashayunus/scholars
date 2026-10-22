<x-app-layout>
    <x-slot name="title">Our Blog</x-slot>

    {{-- ══════════════════════════════════════════════
         SECTION 1: HERO HEADER (Synchronized with Programmes, About, Teachers & Pricing)
    ══════════════════════════════════════════════ --}}
    <section class="relative bg-primary-950 text-white py-12 md:py-16 text-center overflow-hidden">
        <!-- Background Image & Overlay -->
        <div class="absolute inset-0 z-0">
            <img src="{{ asset('images/hero-students.jpg') }}" alt="Olympia Scholar Blog" class="w-full h-full object-cover" />
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
                Our <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-amber-200">Blog</span>
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
         SECTION 2: FEATURED POST & BLOG DIRECTORY
    ══════════════════════════════════════════════ --}}
    <section class="py-16 md:py-24 bg-slate-50 relative -mt-4" x-data="{ selectedCategory: 'all', searchQuery: '' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-20">

            @php
                // Default fallback articles if DB posts are sparse
                $samplePosts = [
                    [
                        'title' => '10 Proven Study Techniques to Ace SPM Mathematics',
                        'slug' => '10-proven-study-techniques-spm-mathematics',
                        'category' => 'Study Tips',
                        'excerpt' => 'Discover evidence-backed strategies that top Malaysian students use to conquer SPM Add Maths and score straight A+.',
                        'image' => 'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?auto=format&fit=crop&w=1200&q=80',
                        'date' => '14 Aug 2026',
                        'read_time' => '5 min read',
                        'is_featured' => true,
                    ],
                    [
                        'title' => 'How to Write a Perfect Karangan for SPM Bahasa Melayu',
                        'slug' => 'perfect-karangan-spm-bahasa-melayu',
                        'category' => 'Writing',
                        'excerpt' => 'From choosing the right essay format to polishing your vocabulary — a step-by-step guide for Band 6 essays.',
                        'image' => 'https://images.unsplash.com/photo-1455390582262-044cdead277a?auto=format&fit=crop&w=800&q=80',
                        'date' => '10 Aug 2026',
                        'read_time' => '4 min read',
                        'is_featured' => false,
                    ],
                    [
                        'title' => 'IGCSE vs SPM: Which Path is Right for Your Child?',
                        'slug' => 'igcse-vs-spm-which-path',
                        'category' => 'Parents Guide',
                        'excerpt' => 'We break down the key differences, curriculum structures, costs, and university prospects of both major examination systems.',
                        'image' => 'https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&w=800&q=80',
                        'date' => '05 Aug 2026',
                        'read_time' => '7 min read',
                        'is_featured' => false,
                    ],
                    [
                        'title' => 'Mastering SPM Physics: Formula Memorization vs Conceptual Understanding',
                        'slug' => 'mastering-spm-physics-formulas-vs-concepts',
                        'category' => 'Exam Prep',
                        'excerpt' => 'Why memorizing physics equations is not enough for Paper 2 structured questions and how to apply core principles.',
                        'image' => 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?auto=format&fit=crop&w=800&q=80',
                        'date' => '28 Jul 2026',
                        'read_time' => '6 min read',
                        'is_featured' => false,
                    ],
                    [
                        'title' => 'Building Effective Study Timetables During Trial Exam Season',
                        'slug' => 'building-effective-study-timetables-trial-exams',
                        'category' => 'Study Tips',
                        'excerpt' => 'Avoid burnout with spaced repetition, active recall schedules, and balanced daily study routines.',
                        'image' => 'https://images.unsplash.com/photo-1506784983877-45594efa4cbe?auto=format&fit=crop&w=800&q=80',
                        'date' => '20 Jul 2026',
                        'read_time' => '4 min read',
                        'is_featured' => false,
                    ],
                    [
                        'title' => 'The Role of Digital Learning Platforms in Modern Tuition',
                        'slug' => 'role-of-digital-learning-platforms-in-tuition',
                        'category' => 'STEM',
                        'excerpt' => 'How recorded class vaults, interactive quizzes, and instant teacher Q&A desks empower students outside the classroom.',
                        'image' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=800&q=80',
                        'date' => '12 Jul 2026',
                        'read_time' => '5 min read',
                        'is_featured' => false,
                    ]
                ];

                $featuredPost = $samplePosts[0];
            @endphp

            {{-- 1. FEATURED ARTICLE SPOTLIGHT --}}
            <div class="mb-16">
                <div class="bg-white rounded-3xl overflow-hidden border border-slate-200/80 shadow-lg hover:shadow-xl transition-all duration-500 grid grid-cols-1 lg:grid-cols-12 group">
                    {{-- Image Side --}}
                    <div class="lg:col-span-7 relative overflow-hidden min-h-[320px] lg:min-h-[420px]">
                        <img src="{{ $featuredPost['image'] }}" alt="{{ $featuredPost['title'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out" />
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/60 via-transparent to-transparent lg:hidden"></div>
                        <div class="absolute top-6 left-6 flex items-center gap-2">
                            <span class="bg-amber-400 text-slate-950 font-black text-xs uppercase px-3.5 py-1.5 rounded-full shadow-md tracking-wider">
                                Featured Article
                            </span>
                            <span class="bg-white/90 backdrop-blur-md text-slate-800 text-xs font-semibold px-3 py-1 rounded-full shadow-sm">
                                {{ $featuredPost['category'] }}
                            </span>
                        </div>
                    </div>

                    {{-- Content Side --}}
                    <div class="lg:col-span-5 p-8 lg:p-12 flex flex-col justify-between bg-white">
                        <div>
                            <div class="flex items-center gap-3 text-xs text-slate-400 mb-4 font-light">
                                <span>{{ $featuredPost['date'] }}</span>
                                <span>•</span>
                                <span>{{ $featuredPost['read_time'] }}</span>
                            </div>
                            
                            <h2 class="font-heading font-black text-2xl lg:text-3xl text-slate-900 leading-tight mb-4 group-hover:text-primary-600 transition-colors">
                                {{ $featuredPost['title'] }}
                            </h2>

                            <p class="text-slate-600 text-base leading-relaxed font-light mb-8">
                                {{ $featuredPost['excerpt'] }}
                            </p>
                        </div>

                        <div class="flex items-center justify-between pt-6 border-t border-slate-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-primary-950 text-white font-bold text-xs flex items-center justify-center shadow-sm">
                                    OS
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-900">Olympia Academic Team</p>
                                    <p class="text-[11px] text-slate-400 font-light">Education Specialist</p>
                                </div>
                            </div>

                            <a href="#" class="inline-flex items-center gap-2 text-sm font-heading font-bold text-primary-600 hover:text-primary-800 transition-colors group-hover:translate-x-1 duration-300">
                                Read Article
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. CATEGORY & SEARCH FILTER BAR --}}
            <div class="w-full max-w-5xl mx-auto bg-white border border-slate-200/80 shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-2xl md:rounded-full p-2 mb-12 flex flex-col md:flex-row items-center justify-between gap-3">
                
                {{-- Category Segmented Buttons --}}
                <div class="flex items-center gap-1 overflow-x-auto w-full md:w-auto px-2 py-1 no-scrollbar">
                    <button @click="selectedCategory = 'all'" 
                            :class="selectedCategory === 'all' ? 'bg-primary-950 text-white shadow-md' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                            class="px-4 py-2 rounded-full text-xs font-bold transition-all duration-300 whitespace-nowrap">
                        All Articles
                    </button>
                    <button @click="selectedCategory = 'Study Tips'" 
                            :class="selectedCategory === 'Study Tips' ? 'bg-primary-950 text-white shadow-md' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                            class="px-4 py-2 rounded-full text-xs font-bold transition-all duration-300 whitespace-nowrap">
                        Study Tips
                    </button>
                    <button @click="selectedCategory = 'Writing'" 
                            :class="selectedCategory === 'Writing' ? 'bg-primary-950 text-white shadow-md' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                            class="px-4 py-2 rounded-full text-xs font-bold transition-all duration-300 whitespace-nowrap">
                        Writing
                    </button>
                    <button @click="selectedCategory = 'Parents Guide'" 
                            :class="selectedCategory === 'Parents Guide' ? 'bg-primary-950 text-white shadow-md' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                            class="px-4 py-2 rounded-full text-xs font-bold transition-all duration-300 whitespace-nowrap">
                        Parents Guide
                    </button>
                    <button @click="selectedCategory = 'Exam Prep'" 
                            :class="selectedCategory === 'Exam Prep' ? 'bg-primary-950 text-white shadow-md' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                            class="px-4 py-2 rounded-full text-xs font-bold transition-all duration-300 whitespace-nowrap">
                        Exam Prep
                    </button>
                </div>

                {{-- Search Box --}}
                <div class="relative w-full md:w-64 pr-2">
                    <input type="text" x-model="searchQuery" placeholder="Search articles..." class="w-full bg-slate-50 border border-slate-200 rounded-full pl-9 pr-4 py-2 text-xs font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-950/20 focus:bg-white transition-all" />
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            {{-- 3. ARTICLES GRID --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                @if (isset($posts) && $posts->count() > 0)
                    @foreach ($posts as $index => $post)
                    <div class="bg-white rounded-3xl overflow-hidden border border-slate-200/80 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group"
                         x-show="(selectedCategory === 'all' || selectedCategory === '{{ $post->category }}') && ('{{ strtolower($post->title) }}'.includes(searchQuery.toLowerCase()) || searchQuery === '')">
                        <div>
                            {{-- Image --}}
                            <div class="w-full h-52 relative overflow-hidden bg-slate-100">
                                <img src="{{ $post->thumbnail ? asset('storage/'.$post->thumbnail) : ($samplePosts[$index % count($samplePosts)]['image']) }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out" />
                                @if ($post->category)
                                <div class="absolute top-4 left-4">
                                    <span class="bg-white/90 backdrop-blur-md text-primary-950 text-xs font-bold px-3 py-1 rounded-full shadow-sm">
                                        {{ $post->category }}
                                    </span>
                                </div>
                                @endif
                            </div>

                            {{-- Card Body --}}
                            <div class="p-6">
                                <p class="text-xs text-slate-400 mb-2 font-light">{{ $post->published_at ? $post->published_at->format('d M Y') : 'Recent' }}</p>
                                <h3 class="font-heading font-bold text-slate-900 text-lg leading-snug mb-3 group-hover:text-primary-600 transition-colors line-clamp-2">
                                    {{ $post->title }}
                                </h3>
                                <p class="text-slate-500 text-sm leading-relaxed font-light line-clamp-3 mb-6">
                                    {{ $post->excerpt }}
                                </p>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="px-6 pb-6 pt-2 border-t border-slate-100 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-primary-950 text-white font-bold text-[10px] flex items-center justify-center">
                                    OS
                                </div>
                                <span class="text-xs font-medium text-slate-600">Academic Team</span>
                            </div>

                            <a href="#" class="text-xs font-heading font-bold text-primary-600 hover:text-primary-800 transition-colors inline-flex items-center gap-1 group-hover:translate-x-0.5 duration-200">
                                Read More →
                            </a>
                        </div>
                    </div>
                    @endforeach
                @else
                    {{-- Render fallback curated posts when no database posts are present --}}
                    @foreach ($samplePosts as $index => $item)
                    @if ($index > 0)
                    <div class="bg-white rounded-3xl overflow-hidden border border-slate-200/80 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group"
                         x-show="(selectedCategory === 'all' || selectedCategory === '{{ $item['category'] }}') && ('{{ strtolower($item['title']) }}'.includes(searchQuery.toLowerCase()) || searchQuery === '')">
                        <div>
                            {{-- Image --}}
                            <div class="w-full h-52 relative overflow-hidden bg-slate-100">
                                <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out" />
                                <div class="absolute top-4 left-4">
                                    <span class="bg-white/90 backdrop-blur-md text-primary-950 text-xs font-bold px-3 py-1 rounded-full shadow-sm">
                                        {{ $item['category'] }}
                                    </span>
                                </div>
                            </div>

                            {{-- Card Body --}}
                            <div class="p-6">
                                <div class="flex items-center justify-between text-xs text-slate-400 mb-2 font-light">
                                    <span>{{ $item['date'] }}</span>
                                    <span>{{ $item['read_time'] }}</span>
                                </div>
                                <h3 class="font-heading font-bold text-slate-900 text-lg leading-snug mb-3 group-hover:text-primary-600 transition-colors line-clamp-2">
                                    {{ $item['title'] }}
                                </h3>
                                <p class="text-slate-500 text-sm leading-relaxed font-light line-clamp-3 mb-6">
                                    {{ $item['excerpt'] }}
                                </p>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="px-6 pb-6 pt-2 border-t border-slate-100 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-primary-950 text-white font-bold text-[10px] flex items-center justify-center">
                                    OS
                                </div>
                                <span class="text-xs font-medium text-slate-600">Academic Team</span>
                            </div>

                            <a href="#" class="text-xs font-heading font-bold text-primary-600 hover:text-primary-800 transition-colors inline-flex items-center gap-1 group-hover:translate-x-0.5 duration-200">
                                Read More →
                            </a>
                        </div>
                    </div>
                    @endif
                    @endforeach
                @endif
            </div>



            {{-- Pagination Links --}}
            @if (isset($posts) && method_exists($posts, 'links'))
            <div class="mt-12">
                {{ $posts->links() }}
            </div>
            @endif

        </div>
    </section>
</x-app-layout>
