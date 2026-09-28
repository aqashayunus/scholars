<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Olympian Scholars</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface min-h-screen flex items-center justify-center py-12" x-data="{ role: 'student' }">
    <div class="w-full max-w-md px-4">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-flex flex-col items-center gap-3 group">
                <img src="{{ asset('images/logo.png') }}" alt="Olympian Scholars" class="h-20 w-auto object-contain transition-transform group-hover:scale-105">
            </a>
            <h1 class="font-heading font-bold text-2xl text-charcoal mt-4 mb-1">Welcome back</h1>
            <p class="text-muted text-sm">Sign in to your dashboard</p>
        </div>

        {{-- Role Picker --}}
        <div class="card mb-4">
            <p class="text-xs font-semibold text-muted uppercase tracking-wide mb-3">I am a:</p>
            <div class="grid grid-cols-3 gap-2">
                {{-- Student --}}
                <button type="button" @click="role = 'student'"
                        :class="role === 'student' ? 'bg-primary text-white shadow-md' : 'bg-surface text-charcoal hover:bg-primary-50'"
                        class="flex flex-col items-center gap-1.5 px-2 py-3 rounded-2xl text-xs font-semibold transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                    </svg>
                    Student
                </button>
                {{-- Parent --}}
                <button type="button" @click="role = 'parent'"
                        :class="role === 'parent' ? 'bg-primary text-white shadow-md' : 'bg-surface text-charcoal hover:bg-primary-50'"
                        class="flex flex-col items-center gap-1.5 px-2 py-3 rounded-2xl text-xs font-semibold transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Parent
                </button>
                {{-- Teacher --}}
                <button type="button" @click="role = 'teacher'"
                        :class="role === 'teacher' ? 'bg-primary text-white shadow-md' : 'bg-surface text-charcoal hover:bg-primary-50'"
                        class="flex flex-col items-center gap-1.5 px-2 py-3 rounded-2xl text-xs font-semibold transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Teacher
                </button>
            </div>
        </div>

        {{-- Login Form --}}
        <div class="card">
            <x-auth-session-status class="mb-4 p-3 bg-emerald-50 text-emerald-700 rounded-xl text-sm" :status="session('status')" />

            @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 text-red-600 rounded-xl text-sm">
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4" onsubmit="try { localStorage.removeItem('adminActiveTab'); localStorage.removeItem('adminActiveSubTab'); } catch(e) {}">
                @csrf
                <script>
                    try {
                        localStorage.removeItem('adminActiveTab');
                        localStorage.removeItem('adminActiveSubTab');
                    } catch(e) {}
                </script>

                {{-- Demo hint --}}
                <div class="p-3 bg-primary-50 border border-primary-100 rounded-xl text-xs text-primary" x-show="true">
                    <p class="font-semibold mb-1">Demo Login Credentials:</p>
                    <p x-show="role === 'student'">student1@olympianscholars.my / password</p>
                    <p x-show="role === 'parent'">parent1@olympianscholars.my / password</p>
                    <p x-show="role === 'teacher'">teacher1@olympianscholars.my / password</p>
                </div>

                <div>
                    <label for="email" class="form-label" x-text="role === 'student' ? 'Email Address or Student ID (OS-XXXXXX)' : 'Email Address'"></label>
                    <input id="email" type="text" name="email" value="{{ old('email') }}"
                           class="form-input" required autofocus autocomplete="username"
                           :placeholder="role === 'student' ? 'student@email.com or OS-ABC123' : 'your@email.com'">
                </div>

                <div>
                    <label for="password" class="form-label">Password</label>
                    <input id="password" type="password" name="password"
                           class="form-input" required autocomplete="current-password"
                           placeholder="Enter your password">
                </div>

                <div class="flex items-center justify-between">
                    <label for="remember_me" class="flex items-center gap-2 cursor-pointer">
                        <input id="remember_me" type="checkbox" name="remember"
                               class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary">
                        <span class="text-sm text-charcoal">Remember me</span>
                    </label>
                    @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm text-primary font-semibold hover:underline">Forgot password?</a>
                    @endif
                </div>

                <button type="submit" class="btn-primary w-full justify-center py-3.5">
                    Sign In
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </button>
            </form>

            <p class="text-center text-sm text-muted mt-4">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-primary font-semibold hover:underline">Register</a>
            </p>
        </div>

        <div class="flex items-center justify-between text-xs text-muted mt-5 px-1">
            <a href="{{ route('home') }}" class="hover:text-primary transition-colors">← Back to Homepage</a>
            <a href="{{ route('admin.login') }}" class="text-gray-400 hover:text-primary font-medium transition-colors">Admin Portal</a>
        </div>
    </div>
</body>
</html>
