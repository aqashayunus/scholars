<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registration Successful | Olympian Scholars</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface min-h-screen flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md text-center">
        {{-- Celebration --}}
        <div class="w-20 h-20 bg-amber-500 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-lg">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        <h1 class="font-heading font-bold text-2xl text-charcoal mb-2">Account Created Successfully!</h1>
        <p class="text-muted text-sm mb-8">Your parent account and your child's account are ready.</p>

        {{-- Parent Info --}}
        <div class="card mb-4 text-left">
            <p class="text-xs font-semibold text-muted uppercase tracking-wide mb-3">Your Parent Account</p>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-muted">Name</span>
                    <span class="font-medium text-charcoal">{{ $data['parent_name'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted">Email</span>
                    <span class="font-medium text-charcoal">{{ $data['parent_email'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted">Relationship</span>
                    <span class="font-medium text-charcoal">{{ $data['relationship'] }}</span>
                </div>
            </div>
        </div>

        {{-- Child Info --}}
        <div class="card mb-6 text-left">
            <p class="text-xs font-semibold text-muted uppercase tracking-wide mb-3">Child Account</p>

            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-muted">Child's Name</span>
                    <span class="font-medium text-charcoal">{{ $data['child_name'] }}</span>
                </div>
                @if(!empty($data['child_level']))
                <div class="flex justify-between">
                    <span class="text-muted">Level</span>
                    <span class="font-medium text-charcoal">{{ $data['child_level'] }}</span>
                </div>
                @endif
                @if(!empty($data['child_school']))
                <div class="flex justify-between">
                    <span class="text-muted">School</span>
                    <span class="font-medium text-charcoal">{{ $data['child_school'] }}</span>
                </div>
                @endif
                @if(!empty($data['masked_ic']))
                <div class="flex justify-between">
                    <span class="text-muted">Child IC</span>
                    <span class="font-mono text-charcoal">{{ $data['masked_ic'] }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-muted">Account Type</span>
                    <span class="font-medium text-charcoal">{{ $data['is_new_child'] ? 'New Account Created' : 'Linked to Existing Account' }}</span>
                </div>
            </div>
        </div>

        {{-- Child Invoice & FPX Payment Option --}}
        @if(!empty($data['invoice_amount']))
        <div class="card mb-6 text-left border border-blue-200/80 bg-blue-50/30">
            <div class="flex items-center justify-between mb-3 border-b border-gray-100 pb-2.5">
                <div>
                    <span class="px-2 py-0.5 rounded-md bg-blue-100 text-primary text-[10px] font-bold uppercase tracking-wider">Tuition Invoice Issued</span>
                    <p class="font-mono font-bold text-xs text-gray-900 mt-1">#{{ $data['invoice_number'] }}</p>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-gray-400 block uppercase font-semibold">Total Due</span>
                    <span class="font-black text-primary text-base font-heading">RM {{ number_format($data['invoice_amount'], 2) }}</span>
                </div>
            </div>

            <p class="text-xs text-muted mb-3">An initial tuition invoice has been issued for {{ $data['child_name'] }}. You can pay now via FPX online banking or anytime within the grace period.</p>

            <a href="{{ route('parent.fees') }}"
               class="w-full py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold text-center transition-colors flex items-center justify-center gap-2 shadow-xs cursor-pointer">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Pay Child Tuition via FPX</span>
            </a>
        </div>
        @endif

        {{-- Login instructions --}}
        <div class="p-4 bg-primary-50 border border-primary-100 rounded-2xl text-left mb-6">
            <p class="text-sm font-semibold text-primary mb-2">How to log in:</p>
            <div class="space-y-1 text-xs text-muted">
                <p>✓ <strong>Your login:</strong> Use your email address + password</p>
                <p>✓ <strong>Your child's login:</strong> Use their email address + password</p>
                <p>✓ <strong>Login Portal:</strong> <a href="{{ route('login') }}" class="text-primary font-semibold underline">/login</a></p>
            </div>
        </div>

        <a href="{{ route('parent.dashboard') }}"
           class="block w-full py-3 px-4 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold text-center transition-colors">
            Go to Parent Dashboard →
        </a>
    </div>
</body>
</html>
