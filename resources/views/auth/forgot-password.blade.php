<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password | Olympian Scholars</title>
    
    {{-- Favicon --}}
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v={{ time() }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v={{ time() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') }}?v={{ time() }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ time() }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}?v={{ time() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface min-h-screen flex items-center justify-center py-12">
    <div class="w-full max-w-md px-4">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-flex flex-col items-center gap-3 group">
                <img src="{{ asset('images/logo-icon.png') }}" alt="Olympian Scholars" class="h-20 w-20 object-contain transition-transform group-hover:scale-105 drop-shadow-sm">
            </a>
            <h1 class="font-heading font-bold text-2xl text-charcoal mt-4 mb-1">Forgot password</h1>
            <p class="text-muted text-sm">Enter your email address to reset your password</p>
        </div>

        {{-- Form Card --}}
        <div class="card">
            <x-auth-session-status class="mb-4 p-3 bg-emerald-50 text-emerald-700 rounded-xl text-sm" :status="session('status')" />

            @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 text-red-600 rounded-xl text-sm">
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="form-label">Email Address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                           class="form-input" required autofocus autocomplete="username"
                           placeholder="Enter your registered email address">
                </div>

                <button type="submit" class="btn-primary w-full justify-center py-3.5">
                    Email Password Reset Link
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </button>
            </form>
        </div>

        <div class="flex items-center justify-between text-xs text-muted mt-5 px-1">
            <a href="{{ route('home') }}" class="hover:text-primary transition-colors">← Back to Homepage</a>
            <a href="{{ route('login') }}" class="text-gray-400 hover:text-primary font-medium transition-colors">Back to Login</a>
        </div>
    </div>
</body>
</html>
