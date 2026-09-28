<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Portal Login | Olympian Scholars</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface min-h-screen flex items-center justify-center py-12">
    <div class="w-full max-w-md px-4">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-flex flex-col items-center gap-3 group">
                <img src="{{ asset('images/logo.png') }}" alt="Olympian Scholars" class="h-20 w-auto object-contain transition-transform group-hover:scale-105">
            </a>
            <h1 class="font-heading font-bold text-2xl text-charcoal mt-4 mb-1">Administrator Portal</h1>
            <p class="text-muted text-sm">Sign in to Admin Dashboard</p>
        </div>

        {{-- Login Form Card --}}
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
                <div class="p-3 bg-primary-50 border border-primary-100 rounded-xl text-xs text-primary">
                    <p class="font-semibold mb-1">Admin Credentials:</p>
                    <p><strong>Username:</strong> admin &nbsp;|&nbsp; <strong>Password:</strong> admin123</p>
                </div>

                <div>
                    <label for="email" class="form-label">Username or Email</label>
                    <input id="email" type="text" name="email" value="{{ old('email') }}"
                           class="form-input" required autofocus autocomplete="username"
                           placeholder="admin">
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
                    Sign In to Dashboard
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </button>
            </form>
        </div>

        <div class="flex items-center justify-between text-xs text-muted mt-5 px-1">
            <a href="{{ route('home') }}" class="hover:text-primary transition-colors">← Back to Homepage</a>
            <a href="{{ route('login') }}" class="hover:text-primary transition-colors font-medium">User Login →</a>
        </div>
    </div>
</body>
</html>
