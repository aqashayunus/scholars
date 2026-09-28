<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Account | Olympian Scholars</title>
    <meta name="description" content="Join Olympian Scholars — choose your registration type to get started.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface min-h-screen flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-2xl">
        {{-- Logo --}}
        <div class="text-center mb-10">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                <div class="w-12 h-12 bg-gradient-brand rounded-2xl flex items-center justify-center">
                    <span class="text-white font-heading font-bold text-base">OS</span>
                </div>
                <div class="text-left">
                    <span class="font-heading font-bold text-primary text-xl block leading-none">Olympian</span>
                    <span class="font-heading font-bold text-accent text-sm block">Scholars</span>
                </div>
            </a>
            <h1 class="font-heading font-bold text-3xl text-charcoal mt-8 mb-2">How would you like to join?</h1>
            <p class="text-muted text-sm">Choose your registration type to get started.</p>
        </div>

        {{-- Role Selection Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            {{-- Student --}}
            <a href="{{ route('register.student') }}"
               class="card group hover:border-primary-400 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 flex flex-col items-center justify-center text-center gap-4 p-8 cursor-pointer">
                <div class="w-16 h-16 bg-primary-50 group-hover:bg-primary rounded-2xl flex items-center justify-center transition-colors duration-300">
                    <svg class="w-8 h-8 text-primary group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                    </svg>
                </div>
                <h2 class="font-heading font-bold text-lg text-charcoal group-hover:text-primary transition-colors">Student</h2>
            </a>

            {{-- Parent --}}
            <a href="{{ route('register.parent') }}"
               class="card group hover:border-primary-400 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 flex flex-col items-center justify-center text-center gap-4 p-8 cursor-pointer">
                <div class="w-16 h-16 bg-primary-50 group-hover:bg-primary rounded-2xl flex items-center justify-center transition-colors duration-300">
                    <svg class="w-8 h-8 text-primary group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h2 class="font-heading font-bold text-lg text-charcoal group-hover:text-primary transition-colors">Parent / Guardian</h2>
            </a>

            {{-- Teacher --}}
            <a href="{{ route('register.teacher') }}"
               class="card group hover:border-primary-400 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 flex flex-col items-center justify-center text-center gap-4 p-8 cursor-pointer">
                <div class="w-16 h-16 bg-primary-50 group-hover:bg-primary rounded-2xl flex items-center justify-center transition-colors duration-300">
                    <svg class="w-8 h-8 text-primary group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h2 class="font-heading font-bold text-lg text-charcoal group-hover:text-primary transition-colors">Teacher / Educator</h2>
            </a>
        </div>

        <p class="text-center text-sm text-muted mt-8">
            Already have an account?
            <a href="{{ route('login') }}" class="text-primary font-semibold hover:underline">Sign in</a>
        </p>
    </div>
</body>
</html>
