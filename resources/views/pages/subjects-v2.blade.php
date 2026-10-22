<x-app-layout>
    <x-slot name="title">Subjects</x-slot>
    <section class="bg-gradient-to-br from-primary-950 via-primary-900 to-navy text-white py-20 text-center relative overflow-hidden">
        <div class="absolute -bottom-1 left-0 right-0 overflow-hidden leading-none pointer-events-none z-10">
            <svg viewBox="0 0 1440 48" fill="none" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-10 block">
                <path d="M0,20 Q720,48 1440,20 L1440,48 L0,48 Z" fill="#ffffff"/>
            </svg>
        </div>
        <div class="relative max-w-3xl mx-auto px-4">
            <h1 class="font-heading font-bold text-4xl md:text-5xl text-white mb-4">All Subjects</h1>
            <p class="text-white/80 text-lg">Expert-led courses for every SPM & IGCSE subject.</p>
        </div>
    </section>

    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
                @php
                    $bookIcon = 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253';
                    $mathIcon = 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z';
                    $scienceIcon = 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z';
                    $langIcon = 'M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129';
                    $artIcon = 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z';
                    $historyIcon = 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479M12 14l-6.16-3.422a12.083 12.083 0 00-.665 6.479M12 14v6.5';
                    $calcIcon = 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4';
                    $lightningIcon = 'M13 10V3L4 14h7v7l9-11h-7z';
                    $flaskIcon = 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z';
                    $dnaIcon = 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z';
                    $pencilIcon = 'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z';
                    $chartIcon = 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6';
                    $moneyIcon = 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1';

                    $subjectIcons = [
                        'Phonics & Reading'           => $pencilIcon,
                        'Early Mathematics & Counting'=> $mathIcon,
                        'Early Science Exploration'   => $scienceIcon,
                        'Bahasa Melayu Awal'          => $bookIcon,
                        'Arts & Creativity'           => $artIcon,

                        'Bahasa Melayu (Primary)'     => $bookIcon,
                        'English (Primary)'           => $langIcon,
                        'Mathematics (Primary)'       => $mathIcon,
                        'Science (Primary)'           => $scienceIcon,
                        'Sejarah (Primary)'           => $historyIcon,

                        'Bahasa Melayu (Lower Sec)'   => $bookIcon,
                        'English (Lower Sec)'         => $langIcon,
                        'Mathematics (Lower Sec)'     => $mathIcon,
                        'Science (Lower Sec)'         => $scienceIcon,
                        'Sejarah (Lower Sec)'         => $historyIcon,

                        'Mathematics'                 => $mathIcon,
                        'Additional Mathematics'      => $calcIcon,
                        'Science'                      => $scienceIcon,
                        'Physics'                      => $lightningIcon,
                        'Chemistry'                    => $flaskIcon,
                        'Biology'                      => $dnaIcon,
                        'Bahasa Melayu'                => $bookIcon,
                        'English'                      => $langIcon,
                        'Sejarah'                      => $historyIcon,
                        'Prinsip Perakaunan'           => $mathIcon,
                        'Perniagaan'                   => $chartIcon,
                        'Ekonomi'                      => $moneyIcon,
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
                @endphp
                @foreach ($subjects as $subject)
                @php
                    $icon = $subjectIcons[$subject->name] ?? $bookIcon;
                    $fallbackIdx = ($subject->id ?? 0) % count($fallbackImages);
                    $bgImg = $subjectImages[$subject->name] ?? $fallbackImages[$fallbackIdx];

                    // Tier-tailored overlay gradients
                    $overlayClass = match($subject->level) {
                        'Kindergarten'    => 'from-amber-950/95 via-amber-900/75 to-slate-900/60',
                        'Primary'         => 'from-emerald-950/95 via-emerald-900/75 to-slate-900/60',
                        'Lower Secondary' => 'from-blue-950/95 via-blue-900/75 to-slate-900/60',
                        default           => 'from-purple-950/95 via-purple-900/75 to-slate-900/60',
                    };
                @endphp
                <a x-show="selectedLevel === 'all' || selectedLevel === '{{ $subject->level }}'" href="{{ route('subject.show', $subject) }}" @click.prevent="openSubjectModal('{{ $subject->display_name ?? $subject->name }}')" class="group relative overflow-hidden rounded-3xl p-6 text-center flex flex-col items-center justify-between min-h-[250px] transition-all duration-300 hover:-translate-y-2 shadow-lg hover:shadow-2xl border border-white/25 cursor-pointer">
                    {{-- Subject Background Picture (Blurred) --}}
                    <img src="{{ $bgImg }}" alt="{{ $subject->display_name ?? $subject->name }}" class="absolute inset-0 w-full h-full object-cover object-center filter blur-[2px] brightness-90 group-hover:scale-110 group-hover:blur-none transition-all duration-500">
                    
                    {{-- Tier Color Overlay for crisp contrast & distinct visual identity --}}
                    <div class="absolute inset-0 bg-gradient-to-t {{ $overlayClass }} group-hover:opacity-75 transition-opacity duration-300"></div>

                    {{-- Top Content --}}
                    <div class="relative z-10 w-full flex flex-col items-center">
                        <div class="w-16 h-16 bg-white/20 backdrop-blur-md border border-white/35 rounded-2xl flex items-center justify-center mb-4 text-white group-hover:scale-110 group-hover:bg-white/30 transition-all duration-300 shadow-xl">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $icon }}"/></svg>
                        </div>
                        <h3 class="font-heading font-extrabold text-white text-lg md:text-xl mb-2 group-hover:text-amber-300 transition-colors drop-shadow-lg tracking-wide">{{ $subject->display_name ?? $subject->name }}</h3>
                        <p class="text-white/90 text-xs leading-relaxed line-clamp-2 mb-2 font-medium drop-shadow">{{ Str::limit($subject->description, 75) }}</p>
                    </div>

                    {{-- Bottom Badge --}}
                    <div class="relative z-10 mt-auto pt-2">
                        <span class="inline-block bg-white/25 backdrop-blur-md border border-white/40 text-white text-xs font-bold px-3.5 py-1 rounded-full shadow-md uppercase tracking-wider">{{ $subject->level }}</span>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </section>
</x-app-layout>
