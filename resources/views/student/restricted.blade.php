<x-dashboard-layout>
    <x-slot name="title">Account Access Restricted</x-slot>

    <x-slot name="sidebar">
        @include('student.partials.sidebar')
    </x-slot>

    <div class="space-y-6 max-w-4xl mx-auto">
        {{-- Header Bar --}}
        <div class="bg-white rounded-3xl p-4 sm:p-6 border border-gray-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('student.dashboard') }}" class="w-10 h-10 rounded-2xl bg-gray-50 border border-gray-200 text-gray-600 hover:text-gray-900 hover:bg-gray-100 flex items-center justify-center transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <h1 class="font-heading font-black text-xl sm:text-2xl text-gray-900">Academic Access Restricted</h1>
                    <p class="text-xs text-gray-500 font-medium mt-0.5">Please settle your outstanding tuition fees to resume classes and learning.</p>
                </div>
            </div>

            <a href="{{ route('student.fees') }}" class="py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl text-xs font-extrabold shadow-sm transition inline-flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                <span>Go to My Fees</span>
            </a>
        </div>

        {{-- 🔒 Restriction Notice Card --}}
        <div class="bg-white rounded-3xl border border-rose-100 shadow-md p-6 sm:p-10 relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-rose-500 via-amber-500 to-rose-600"></div>

            <div class="flex flex-col items-center text-center max-w-xl mx-auto">
                <div class="relative mb-6">
                    <div class="w-20 h-20 rounded-3xl bg-rose-50 border-2 border-rose-200 text-rose-600 flex items-center justify-center shadow-lg shadow-rose-500/10">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <span class="absolute -top-1 -right-1 flex h-4 w-4">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-4 w-4 bg-rose-500"></span>
                    </span>
                </div>

                <h2 class="font-heading font-black text-2xl sm:text-3xl text-gray-900 mb-2">
                    Account Access Restricted
                </h2>

                <p class="text-sm sm:text-base text-gray-600 font-medium leading-relaxed mb-8">
                    Your access to learning materials, classes, tasks, quizzes, and certificates is temporarily restricted due to an overdue payment.
                </p>

                {{-- Fee Breakdown Box --}}
                <div class="w-full bg-gray-50 rounded-2xl border border-gray-200 p-5 mb-8 text-left divide-y divide-gray-200/80">
                    <div class="flex items-center justify-between pb-3">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Outstanding Balance</span>
                        <span class="font-mono font-black text-xl sm:text-2xl text-rose-600">
                            RM {{ number_format($details['outstanding_amount'] ?? 0, 2) }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-3">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Payment Due Date</span>
                        <span class="text-xs sm:text-sm font-extrabold text-gray-800">
                            {{ isset($details['earliest_due_date']) ? \Carbon\Carbon::parse($details['earliest_due_date'])->format('d/m/Y') : '–' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between pt-3">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Days Overdue</span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-rose-100 text-rose-700">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $details['days_overdue'] ?? 1 }} {{ \Illuminate\Support\Str::plural('day', $details['days_overdue'] ?? 1) }} overdue
                        </span>
                    </div>
                </div>

                <p class="text-xs sm:text-sm text-gray-500 font-semibold mb-8">
                    Please settle the outstanding balance or inform your parent/guardian. Access will be automatically unlocked immediately once payment is confirmed.
                </p>

                {{-- Actions --}}
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3 w-full sm:w-auto">
                    @php
                        $primaryInvoice = $details['primary_invoice'] ?? null;
                        $viewInvoiceUrl = $primaryInvoice ? route('student.fees.show', $primaryInvoice->id) : route('student.fees');
                    @endphp

                    <a href="{{ $viewInvoiceUrl }}" class="w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-800 hover:text-gray-900 text-xs font-extrabold shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>View Outstanding Invoice</span>
                    </a>

                    <a href="{{ route('student.fees') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-extrabold shadow-md shadow-blue-500/25 transition flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        <span>Make Payment</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
