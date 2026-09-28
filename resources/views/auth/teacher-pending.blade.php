<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teacher Application Pending | Olympian Scholars</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface min-h-screen flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-lg text-center">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-brand rounded-xl flex items-center justify-center">
                    <span class="text-white font-heading font-bold text-sm">OS</span>
                </div>
                <div class="text-left">
                    <span class="font-heading font-bold text-primary text-lg block leading-none">Olympian</span>
                    <span class="font-heading font-bold text-accent text-xs block">Scholars</span>
                </div>
            </a>
        </div>

        {{-- Status Icon --}}
        <div class="w-20 h-20 bg-amber-100 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-sm">
            <svg class="w-10 h-10 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        <h1 class="font-heading font-bold text-2xl text-charcoal mb-2">Teacher Application Under Review</h1>
        <p class="text-muted text-sm mb-6">
            Hello <span class="font-semibold text-charcoal">{{ auth()->user()->name ?? 'Educator' }}</span>, your educator account is currently awaiting Aster Edu verification and admin approval.
        </p>

        {{-- Status Card --}}
        <div class="card mb-6 text-left border border-amber-200 bg-amber-50/50">
            <div class="flex items-center justify-between pb-3 border-b border-amber-100 mb-3">
                <span class="text-xs font-semibold text-muted uppercase tracking-wide">Account Status</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    Pending Verification
                </span>
            </div>

            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-muted">Registered Email:</span>
                    <span class="font-medium text-charcoal">{{ auth()->user()->email ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted">Verification Channel:</span>
                    <span class="font-medium text-charcoal">Aster Edu Quality & Compliance</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted">Estimated Review Time:</span>
                    <span class="font-medium text-charcoal">1 – 3 Business Days</span>
                </div>
            </div>
        </div>

        {{-- Explanatory Notice --}}
        <div class="p-4 bg-white border border-gray-200 rounded-2xl text-left mb-6 shadow-sm">
            <h3 class="text-sm font-semibold text-charcoal mb-2 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Next Steps
            </h3>
            <p class="text-xs text-muted leading-relaxed">
                Full access to the Teacher Dashboard, Live Classes, Teaching Gigs, and Course Management will be unlocked automatically once your educator credentials have been verified by Aster Edu. You will also receive an email notification once activated.
            </p>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto">
                @csrf
                <button type="submit"
                        class="w-full sm:w-auto py-2.5 px-6 rounded-xl border border-gray-300 hover:bg-gray-50 text-charcoal text-sm font-semibold transition-colors">
                    Log Out
                </button>
            </form>
            <a href="{{ route('home') }}"
               class="py-2.5 px-6 rounded-xl bg-primary hover:bg-primary-700 text-white text-sm font-semibold transition-colors inline-block">
                Return to Homepage
            </a>
        </div>
    </div>
</body>
</html>
