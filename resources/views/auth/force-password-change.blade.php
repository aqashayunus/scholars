<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Set a New Password | Olympian Scholars</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface min-h-screen flex items-center justify-center py-12">
    <div class="w-full max-w-md px-4">
        <div class="text-center mb-8">
            <h1 class="font-heading font-bold text-2xl text-charcoal mt-4 mb-1">Set a New Password</h1>
            <p class="text-muted text-sm">Your account was created with a temporary password. Please set your own password to continue.</p>
        </div>

        <div class="card">
            @if ($errors->any())
                <div class="mb-4 text-sm text-red-600">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.force.update') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="password" class="block text-xs font-semibold text-muted uppercase tracking-wide mb-1">New Password</label>
                    <input id="password" type="password" name="password" required autofocus
                           class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-muted uppercase tracking-wide mb-1">Confirm New Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required
                           class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm">
                </div>

                <button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-primary text-white text-sm font-semibold">
                    Set Password
                </button>
            </form>
        </div>
    </div>
</body>
</html>
