<x-app-layout><x-slot name="title">Learning Platform</x-slot>
<section class="bg-gradient-to-br from-primary-950 via-primary-900 to-navy text-white py-20 text-center relative overflow-hidden">
    <div class="absolute -bottom-1 left-0 right-0 overflow-hidden leading-none pointer-events-none z-10">
        <svg viewBox="0 0 1440 48" fill="none" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-10 block">
            <path d="M0,20 Q720,48 1440,20 L1440,48 L0,48 Z" fill="#ffffff"/>
        </svg>
    </div>
    <div class="relative max-w-3xl mx-auto px-4">
        <h1 class="font-heading font-bold text-4xl md:text-5xl text-white mb-4">The Olympian Learning Platform</h1>
        <p class="text-white/80 text-lg">One platform, three powerful portals for students, parents, and teachers.</p>
    </div>
</section>
<section class="py-20 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-3 gap-6">
            @php
                $portals = [
                    ['role' => 'Student Portal',  'icon' => 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222', 'color' => 'bg-primary text-white',  'desc' => 'Access live classes, track progress, submit assignments, take quizzes, and view your performance charts.'],
                    ['role' => 'Parent Portal',   'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'color' => 'bg-accent text-white',  'desc' => 'Monitor your child attendance, homework, grades, and payment status all in one place.'],
                    ['role' => 'Teacher Portal',  'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'color' => 'bg-charcoal text-white', 'desc' => 'Manage classes, upload notes, track student performance, run live sessions, and communicate with parents.'],
                ];
            @endphp
            @foreach ($portals as $p)
            <div class="card text-center hover:-translate-y-1 transition-all">
                <div class="w-14 h-14 {{ $p['color'] }} rounded-3xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $p['icon'] }}"/></svg>
                </div>
                <h3 class="font-heading font-bold text-xl text-charcoal mb-2">{{ $p['role'] }}</h3>
                <p class="text-muted text-sm leading-relaxed mb-5">{{ $p['desc'] }}</p>
                <a href="{{ route('login') }}" class="btn-primary btn-sm w-full justify-center">Login to Portal</a>
            </div>
            @endforeach
        </div>
    </div>
</section>
</x-app-layout>
