<x-app-layout>
    <x-slot name="title">Our Teachers</x-slot>

    {{-- HERO SECTION --}}
    <section class="bg-gradient-to-br from-primary-950 via-primary-900 to-navy text-white py-10 md:py-12 text-center relative overflow-hidden">
        <div class="absolute -bottom-1 left-0 right-0 overflow-hidden leading-none pointer-events-none z-10">
            <svg viewBox="0 0 1440 48" fill="none" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-8 md:h-10 block">
                <path d="M0,20 Q720,48 1440,20 L1440,48 L0,48 Z" fill="#ffffff"/>
            </svg>
        </div>
        <div class="relative max-w-3xl mx-auto px-4">
            <h1 class="font-heading font-bold text-3xl md:text-4xl text-white mb-2">Meet Our Teachers</h1>
        </div>
    </section>

    {{-- TEACHERS GRID SECTION --}}
    <section class="py-20 bg-white" x-data="{ selectedLevel: 'all', selectedSubject: 'all' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- 2-Dropdown Filter Bar --}}
            <div class="max-w-3xl mx-auto bg-surface border border-gray-200/70 rounded-3xl p-4 md:p-6 mb-12 shadow-sm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Dropdown 1: Education Level / Type --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-charcoal mb-2">Education Level</label>
                        <select x-model="selectedLevel" @change="selectedSubject = 'all'" class="w-full bg-white border border-gray-200 rounded-2xl px-4 py-3 text-xs sm:text-sm font-semibold text-charcoal focus:ring-2 focus:ring-primary focus:border-primary transition-all cursor-pointer outline-none">
                            <option value="all">All Education Levels</option>
                            <option value="kindergarten">Kindergarten (Ages 4–6)</option>
                            <option value="primary">Primary Education (Year 4–6)</option>
                            <option value="secondary">Secondary Education (Form 1–5 • SPM & IGCSE)</option>
                        </select>
                    </div>

                    {{-- Dropdown 2: Subject (Filtered by Education Level) --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-charcoal mb-2">Subject</label>
                        <select x-model="selectedSubject" class="w-full bg-white border border-gray-200 rounded-2xl px-4 py-3 text-xs sm:text-sm font-semibold text-charcoal focus:ring-2 focus:ring-primary focus:border-primary transition-all cursor-pointer outline-none">
                            <option value="all">All Subjects</option>
                            
                            {{-- Kindergarten Subjects --}}
                            <option x-show="selectedLevel === 'kindergarten'" value="english">Phonics & Reading</option>
                            <option x-show="selectedLevel === 'kindergarten'" value="mathematics">Early Maths & Counting</option>
                            <option x-show="selectedLevel === 'kindergarten'" value="science">Science Discovery</option>
                            <option x-show="selectedLevel === 'kindergarten'" value="bahasa melayu">Storytelling & Expression</option>

                            {{-- Primary Subjects --}}
                            <option x-show="selectedLevel === 'primary'" value="bahasa melayu">Bahasa Melayu (KSSR)</option>
                            <option x-show="selectedLevel === 'primary'" value="english">English (KSSR)</option>
                            <option x-show="selectedLevel === 'primary'" value="mathematics">Mathematics (KSSR)</option>
                            <option x-show="selectedLevel === 'primary'" value="science">Science (KSSR)</option>

                            {{-- Secondary Subjects --}}
                            <option x-show="selectedLevel === 'secondary'" value="mathematics">Mathematics (SPM)</option>
                            <option x-show="selectedLevel === 'secondary'" value="additional mathematics">Additional Mathematics (SPM)</option>
                            <option x-show="selectedLevel === 'secondary'" value="physics">Physics (SPM / IGCSE)</option>
                            <option x-show="selectedLevel === 'secondary'" value="chemistry">Chemistry (SPM / IGCSE)</option>
                            <option x-show="selectedLevel === 'secondary'" value="biology">Biology (SPM / IGCSE)</option>
                            <option x-show="selectedLevel === 'secondary'" value="science">General Science (SPM)</option>
                            <option x-show="selectedLevel === 'secondary'" value="bahasa melayu">Bahasa Melayu (SPM)</option>
                            <option x-show="selectedLevel === 'secondary'" value="english">English (CEFR / IGCSE)</option>

                            {{-- All Education Levels --}}
                            <option x-show="selectedLevel === 'all'" value="mathematics">Mathematics</option>
                            <option x-show="selectedLevel === 'all'" value="additional mathematics">Additional Mathematics</option>
                            <option x-show="selectedLevel === 'all'" value="science">General Science</option>
                            <option x-show="selectedLevel === 'all'" value="physics">Physics</option>
                            <option x-show="selectedLevel === 'all'" value="chemistry">Chemistry</option>
                            <option x-show="selectedLevel === 'all'" value="biology">Biology</option>
                            <option x-show="selectedLevel === 'all'" value="bahasa melayu">Bahasa Melayu</option>
                            <option x-show="selectedLevel === 'all'" value="english">English (CEFR / IGCSE)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @php
                    $allTeachers = [
                        [
                            'name' => 'Mr. Daniel Wong',
                            'qualification' => 'MSc in Applied Mathematics',
                            'experience' => '12+ Years Experience',
                            'rating' => '4.9',
                            'reviews' => '128',
                            'photo' => asset('images/teacher-daniel.png'),
                            'subject' => 'Additional Mathematics',
                            'subject_keys' => ['mathematics', 'additional mathematics'],
                            'levels' => ['secondary'],
                            'key' => 'daniel_wong'
                        ],
                        [
                            'name' => 'Cikgu Aisyah',
                            'qualification' => 'BSc (Hons) Biomedical Science',
                            'experience' => '10+ Years Experience',
                            'rating' => '4.9',
                            'reviews' => '112',
                            'photo' => asset('images/teacher-aisyah.png'),
                            'subject' => 'Biology',
                            'subject_keys' => ['science', 'biology'],
                            'levels' => ['secondary', 'primary'],
                            'key' => 'aisyah'
                        ],
                        [
                            'name' => 'Mr. Chong Wei',
                            'qualification' => 'MA in TESL',
                            'experience' => '13+ Years Experience',
                            'rating' => '4.8',
                            'reviews' => '96',
                            'photo' => asset('images/teacher-chong.png'),
                            'subject' => 'English',
                            'subject_keys' => ['english'],
                            'levels' => ['secondary', 'primary', 'kindergarten'],
                            'key' => 'chong_wei'
                        ],
                        [
                            'name' => 'Cikgu Ahmad Farid',
                            'qualification' => 'BSc (Hons) Mathematics',
                            'experience' => '9+ Years Experience',
                            'rating' => '4.9',
                            'reviews' => '104',
                            'photo' => asset('images/teacher-daniel.png'),
                            'subject' => 'Mathematics',
                            'subject_keys' => ['mathematics'],
                            'levels' => ['primary', 'secondary', 'kindergarten'],
                            'key' => 'ahmad_farid'
                        ],
                        [
                            'name' => 'Cikgu Siti Rahimah',
                            'qualification' => 'MSc in Chemical Engineering',
                            'experience' => '11+ Years Experience',
                            'rating' => '4.8',
                            'reviews' => '88',
                            'photo' => asset('images/teacher-aisyah.png'),
                            'subject' => 'Chemistry',
                            'subject_keys' => ['science', 'chemistry'],
                            'levels' => ['secondary'],
                            'key' => 'siti_rahimah'
                        ],
                        [
                            'name' => 'Cikgu Rajesh Kumar',
                            'qualification' => 'BEng (Hons) Electrical Engineering',
                            'experience' => '14+ Years Experience',
                            'rating' => '4.9',
                            'reviews' => '120',
                            'photo' => asset('images/teacher-chong.png'),
                            'subject' => 'Physics',
                            'subject_keys' => ['science', 'physics'],
                            'levels' => ['secondary'],
                            'key' => 'rajesh_kumar'
                        ],
                        [
                            'name' => 'Cikgu Nurul Ain',
                            'qualification' => 'BA (Hons) Bahasa Melayu',
                            'experience' => '10+ Years Experience',
                            'rating' => '4.8',
                            'reviews' => '92',
                            'photo' => asset('images/teacher-aisyah.png'),
                            'subject' => 'Bahasa Melayu',
                            'subject_keys' => ['bahasa melayu'],
                            'levels' => ['secondary', 'primary', 'kindergarten'],
                            'key' => 'nurul_ain'
                        ],
                    ];
                @endphp

                @foreach ($allTeachers as $t)
                <div x-show="(selectedLevel === 'all' || {{ json_encode($t['levels']) }}.includes(selectedLevel)) && (selectedSubject === 'all' || {{ json_encode($t['subject_keys']) }}.includes(selectedSubject))"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm hover:shadow-card hover:-translate-y-1.5 transition-all duration-300 flex flex-col justify-between group text-center">
                    <div>
                        {{-- Photo Header --}}
                        <div class="w-full h-52 rounded-2xl overflow-hidden mb-4 bg-slate-50 relative">
                            <img src="{{ $t['photo'] }}" alt="{{ $t['name'] }}" class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-300">
                        </div>

                        {{-- Name --}}
                        <h3 class="font-heading font-extrabold text-charcoal text-lg mb-1 group-hover:text-primary transition-colors">{{ $t['name'] }}</h3>

                        {{-- Qualification & Experience --}}
                        <p class="text-muted text-xs font-medium leading-tight mb-1">{{ $t['qualification'] }}</p>
                        <p class="text-muted text-xs font-medium mb-3">{{ $t['experience'] }}</p>

                        {{-- Rating --}}
                        <div class="flex items-center justify-center gap-1 text-xs font-extrabold mb-4">
                            <span class="text-amber-400">★</span>
                            <span class="text-charcoal">{{ $t['rating'] }}</span>
                            <span class="text-muted font-normal text-xs">({{ $t['reviews'] }})</span>
                        </div>

                        {{-- Single Subject Specialist Badge --}}
                        <div class="mb-5">
                            <span class="text-xs font-bold px-3.5 py-1 rounded-full bg-slate-100 text-charcoal border border-slate-200">
                                {{ $t['subject'] }}
                            </span>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-center gap-2 pt-3 border-t border-gray-50">
                        <a href="{{ route('teacher.show', $loop->iteration) }}" class="btn-secondary btn-sm text-xs cursor-pointer">
                            View Detail
                        </a>
                        <a href="{{ route('free-trial') }}" class="btn-primary btn-sm text-xs">
                            Book Class
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA SECTION --}}
    <section class="py-16 bg-surface">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h2 class="font-heading font-bold text-3xl text-charcoal mb-3">Start Learning With Our Expert Teachers</h2>
            <p class="text-muted text-base mb-6">Join thousands of students scoring A+ in SPM & IGCSE with Olympian Scholars.</p>
            <a href="{{ route('free-trial') }}" class="btn-primary btn-lg inline-flex">Book Your Free Trial Class</a>
        </div>
    </section>
</x-app-layout>
