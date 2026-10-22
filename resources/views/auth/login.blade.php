<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Olympian Scholars</title>
    
    {{-- Favicon --}}
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v={{ time() }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v={{ time() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') }}?v={{ time() }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ time() }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}?v={{ time() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface min-h-screen flex items-center justify-center py-12" x-data="{ role: 'student' }">
    <div class="w-full max-w-md px-4">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-flex flex-col items-center gap-3 group">
                <img src="{{ asset('images/logo-icon.png') }}" alt="Olympian Scholars" class="h-20 w-20 object-contain transition-transform group-hover:scale-105 drop-shadow-sm">
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

                <div>
                    <label for="email" class="form-label" x-text="role === 'student' ? 'Email Address' : 'Email Address'"></label>
                    <input id="email" type="text" name="email" value="{{ old('email') }}"
                           class="form-input" required autofocus autocomplete="username"
                           :placeholder="role === 'student' ? 'Enter your email address' : 'Enter your email address'">
                </div>

                <div>
                    <label for="password" class="form-label">Password</label>
                    <div class="relative" x-data="{ show: false }">
                        <input id="password" :type="show ? 'text' : 'password'" name="password"
                               class="form-input pr-10" required autocomplete="current-password"
                               placeholder="Enter your password">
                        <button type="button" @click="show = !show"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-charcoal focus:outline-none transition-colors"
                                :title="show ? 'Hide password' : 'Show password'"
                                aria-label="Toggle password visibility">
                            <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
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
