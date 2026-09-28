<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to Olympian Scholars!</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface min-h-screen flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md text-center">
        {{-- Celebration --}}
        <div class="w-20 h-20 bg-gradient-brand rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-lg">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        <h1 class="font-heading font-bold text-2xl text-charcoal mb-2">Welcome to Olympian Scholars!</h1>
        <p class="text-muted text-sm mb-8">Your student account has been created successfully.</p>

        {{-- Account Info Card --}}
        <div class="card mb-6 text-left">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-muted uppercase tracking-wide">Your Account</p>
                    <p class="font-heading font-bold text-lg text-charcoal">{{ $student->user->name }}</p>
                </div>
            </div>

            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-muted">Email</span>
                    <span class="font-medium text-charcoal">{{ $student->user->email }}</span>
                </div>
                @if($maskedIc)
                <div class="flex justify-between">
                    <span class="text-muted">IC Number</span>
                    <span class="font-mono text-charcoal">{{ $maskedIc }}</span>
                </div>
                @endif
                @if($student->level)
                <div class="flex justify-between">
                    <span class="text-muted">Level</span>
                    <span class="font-medium text-charcoal">{{ $student->level }}</span>
                </div>
                @endif
                @if($student->school_name)
                <div class="flex justify-between">
                    <span class="text-muted">School</span>
                    <span class="font-medium text-charcoal">{{ $student->school_name }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Issued Invoice & Payment Option --}}
        @if(!empty($invoice))
        <div class="card mb-6 text-left border border-blue-200/80 bg-blue-50/30">
            <div class="flex items-center justify-between mb-3 border-b border-gray-100 pb-2.5">
                <div>
                    <span class="px-2 py-0.5 rounded-md bg-blue-100 text-primary text-[10px] font-bold uppercase tracking-wider">Tuition Invoice Issued</span>
                    <p class="font-mono font-bold text-xs text-gray-900 mt-1">#{{ $invoice->invoice_number }}</p>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-gray-400 block uppercase font-semibold">Total Due</span>
                    <span class="font-black text-primary text-base font-heading">RM {{ number_format($invoice->total_amount, 2) }}</span>
                </div>
            </div>

            <div class="space-y-1.5 text-xs text-gray-600">
                <div class="flex justify-between">
                    <span class="text-muted">Package / Plan</span>
                    <span class="font-semibold text-gray-800">{{ $invoice->feeStructure?->name ?? 'Enrolled Subjects' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted">Payment Window</span>
                    <span class="font-medium text-gray-700">Due by {{ $invoice->due_date ? $invoice->due_date->format('d M Y') : 'in ' . \App\Models\Setting::get('billing.invoice_due_days', 7) . ' days' }}</span>
                </div>
                @if($invoice->discount_amount > 0)
                <div class="flex justify-between text-emerald-700 font-semibold">
                    <span>Bundle Discount Applied</span>
                    <span>- RM {{ number_format($invoice->discount_amount, 2) }}</span>
                </div>
                @endif
            </div>

            <div class="pt-3 mt-3 border-t border-blue-100/80">
                <a href="{{ route('student.fees') }}"
                   class="w-full py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold text-center transition-colors flex items-center justify-center gap-2 shadow-xs cursor-pointer">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Pay Tuition Now via FPX / Bank Transfer</span>
                </a>
            </div>
        </div>
        @endif

        {{-- How to login --}}
        <div class="p-4 bg-amber-50 border border-amber-100 rounded-2xl text-left mb-6">
            <p class="text-sm font-semibold text-amber-700 mb-2">How to log in next time:</p>
            <div class="space-y-1 text-xs text-muted">
                <p>✓ Use your <strong>email address</strong></p>
                <p>✓ With your <strong>password</strong></p>
                <p>✓ Login at: <a href="{{ route('login') }}" class="text-primary font-semibold underline">/login</a></p>
            </div>
        </div>

        <a href="{{ route('student.dashboard') }}"
           class="block w-full py-3 px-4 rounded-xl bg-gradient-brand text-white text-sm font-semibold text-center hover:opacity-90 transition-opacity">
            Go to My Dashboard →
        </a>
    </div>
</body>
</html>
