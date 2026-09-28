<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Application Received | Olympian Scholars</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface min-h-screen flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md text-center">
        {{-- Icon --}}
        <div class="w-20 h-20 bg-emerald-500 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-lg">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>

        <h1 class="font-heading font-bold text-2xl text-charcoal mb-2">Application Received!</h1>
        <p class="text-muted text-sm mb-2">Thank you for applying to teach with Olympian Scholars, <strong>{{ $name }}</strong>.</p>

        {{-- Status Card --}}
        <div class="card mb-6 text-left">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-muted uppercase tracking-wide">Application Status</p>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 mt-0.5">
                        ● Pending Verification
                    </span>
                </div>
            </div>

            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-muted">Name</span>
                    <span class="font-medium text-charcoal">{{ $name }}</span>
                </div>
                @if($email)
                <div class="flex justify-between">
                    <span class="text-muted">Email</span>
                    <span class="font-medium text-charcoal">{{ $email }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- What's next --}}
        <div class="p-4 bg-emerald-50 border border-emerald-100 rounded-2xl text-left mb-6">
            <p class="text-sm font-semibold text-emerald-700 mb-2">What happens next?</p>
            <div class="space-y-2 text-xs text-muted">
                <div class="flex items-start gap-2">
                    <span class="text-emerald-500 font-bold shrink-0">1</span>
                    <p>Our team will review your application within 3–5 business days.</p>
                </div>
                <div class="flex items-start gap-2">
                    <span class="text-emerald-500 font-bold shrink-0">2</span>
                    <p>You will receive an email notification once your application has been processed.</p>
                </div>
                <div class="flex items-start gap-2">
                    <span class="text-emerald-500 font-bold shrink-0">3</span>
                    <p>Upon approval, you will receive your account credentials to access the Teacher Dashboard.</p>
                </div>
            </div>
        </div>

        <a href="{{ route('home') }}"
           class="block w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold text-center transition-colors">
            Back to Home
        </a>
    </div>
</body>
</html>
