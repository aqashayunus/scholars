<x-dashboard-layout>
    <x-slot name="title">Parent Dashboard</x-slot>

    <x-slot name="sidebar">
        @include('parent.partials.sidebar')
    </x-slot>

    <div x-data="addChildModalData()" class="space-y-6">

        {{-- ── FLASH ALERTS ── --}}
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-3xl text-xs font-semibold text-emerald-800 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button class="text-emerald-600 hover:text-emerald-800 font-bold ml-4">✕</button>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 rounded-3xl text-xs font-semibold text-rose-800 space-y-1 shadow-xs">
                <div class="flex items-center gap-2 mb-1">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <strong>Please review the errors below:</strong>
                </div>
                <ul class="list-disc list-inside pl-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ── TOP BAR ── --}}
        <div class="bg-white rounded-3xl p-4 sm:p-6 border border-gray-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="font-heading font-black text-xl sm:text-2xl text-gray-900">Parent Dashboard</h1>
                <p class="text-xs text-gray-500 font-medium mt-0.5">{{ now()->format('l, j F Y') }}</p>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center gap-2.5 sm:gap-3 w-full sm:w-auto">
                <div class="flex items-center gap-2 bg-gray-50 px-3.5 py-2.5 rounded-2xl border border-gray-200/70 w-full sm:w-auto min-w-0">
                    <span class="text-xs font-bold text-gray-500 shrink-0">Viewing:</span>
                    <select onchange="window.location.href='{{ route('parent.dashboard') }}?child_id=' + this.value" class="bg-transparent border-0 text-xs font-extrabold text-gray-900 focus:ring-0 cursor-pointer py-0 pl-1 pr-6 outline-none truncate w-full sm:max-w-[240px]">
                        @forelse ($children as $ch)
                            <option value="{{ $ch->id }}" {{ $child && $child->id === $ch->id ? 'selected' : '' }}>
                                {{ $ch->user->name ?? 'Student' }} – {{ $ch->level ?? 'N/A' }} ({{ ($ch->is_restricted ?? false) ? 'Overdue 🔒' : 'Paid ✓' }})
                            </option>
                        @empty
                            <option>No child linked</option>
                        @endforelse
                    </select>
                </div>

                {{-- Add Child Button --}}
                <button @click="showAddChildModal = true" class="py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl text-xs font-extrabold shadow-sm transition-all cursor-pointer inline-flex items-center justify-center gap-1.5 shrink-0 w-full sm:w-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    <span>Add Child</span>
                </button>
            </div>
        </div>



        @if($isChildRestricted)
            {{-- 🔒 PAYMENT RESTRICTION CARD (Displayed when selected child is restricted) --}}
            <div class="bg-white rounded-3xl border border-rose-200 shadow-md p-6 sm:p-10 relative overflow-hidden">
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

                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold mb-4">
                        <span>Student: {{ $child->user->name ?? 'Student #' . ($child->id ?? '') }}</span>
                        <span>•</span>
                        <span>Level: {{ $child->level ?? 'Academic' }}</span>
                    </div>

                    <p class="text-sm sm:text-base text-gray-600 font-medium leading-relaxed mb-6">
                        Your access to this student's academic information is temporarily restricted because there is an outstanding payment.
                    </p>

                    {{-- Overdue Details Box --}}
                    <div class="w-full bg-gray-50 rounded-2xl border border-gray-200 p-5 mb-6 text-left divide-y divide-gray-200/80">
                        <div class="flex items-center justify-between pb-3">
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Outstanding Amount</span>
                            <span class="font-mono font-black text-xl sm:text-2xl text-rose-600">
                                RM {{ number_format($restrictionDetails['outstanding_amount'] ?? 0, 2) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between py-3">
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Due Date</span>
                            <span class="text-xs sm:text-sm font-extrabold text-gray-800">
                                {{ isset($restrictionDetails['earliest_due_date']) ? \Carbon\Carbon::parse($restrictionDetails['earliest_due_date'])->format('d/m/Y') : '–' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between pt-3">
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Days Overdue</span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-rose-100 text-rose-700">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $restrictionDetails['days_overdue'] ?? 1 }} {{ \Illuminate\Support\Str::plural('day', $restrictionDetails['days_overdue'] ?? 1) }} overdue
                            </span>
                        </div>
                    </div>

                    <p class="text-xs sm:text-sm text-gray-500 font-semibold mb-8">
                        Please settle the outstanding payment to restore access.
                    </p>

                    {{-- Buttons --}}
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 w-full sm:w-auto">
                        @php
                            $primaryInvoice = $restrictionDetails['primary_invoice'] ?? null;
                            $viewInvoiceUrl = $primaryInvoice ? route('parent.fees.show', $primaryInvoice->id) : route('parent.fees');
                        @endphp

                        <a href="{{ $viewInvoiceUrl }}" class="w-full sm:w-auto px-6 py-3 rounded-2xl bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-800 hover:text-gray-900 text-xs font-extrabold shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>View Outstanding Invoice</span>
                        </a>

                        <a href="{{ route('parent.fees') }}" class="w-full sm:w-auto px-8 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-extrabold shadow-md shadow-blue-500/25 transition flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            <span>Make Payment</span>
                        </a>
                    </div>
                </div>
            </div>
        @else
        {{-- ── 1. FINANCIAL SUMMARY CARDS ── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

            {{-- 1. Outstanding --}}
            <a href="{{ route('parent.payments') }}" class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-3.5 hover:shadow-md hover:border-amber-200 transition-all cursor-pointer group">
                <div class="w-12 h-12 rounded-2xl {{ $totalOutstanding > 0 ? 'bg-amber-500 shadow-amber-500/20' : 'bg-blue-600 shadow-blue-500/20' }} group-hover:scale-105 text-white flex items-center justify-center shrink-0 shadow-md transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Outstanding</p>
                        <svg class="w-3.5 h-3.5 text-gray-300 group-hover:text-amber-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                    <p class="text-2xl font-black {{ $totalOutstanding > 0 ? 'text-amber-600' : 'text-gray-900' }} leading-tight mt-0.5">
                        RM {{ number_format($totalOutstanding, 2) }}
                    </p>
                    <div class="mt-1">
                        @if ($totalOutstanding > 0)
                            <span class="inline-flex items-center gap-1 text-xs text-amber-700 font-semibold bg-amber-50 px-2 py-0.5 rounded-md truncate max-w-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                                {{ $pendingCount }} pending invoice{{ $pendingCount !== 1 ? 's' : '' }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-xs text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded-md truncate max-w-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                All settled
                            </span>
                        @endif
                    </div>
                </div>
            </a>

            {{-- 2. Last Payment Made --}}
            <a href="{{ route('parent.payments') }}" class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-3.5 hover:shadow-md hover:border-emerald-200 transition-all cursor-pointer group">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500 group-hover:scale-105 text-white flex items-center justify-center shrink-0 shadow-md shadow-emerald-500/20 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Last Payment Made</p>
                        <svg class="w-3.5 h-3.5 text-gray-300 group-hover:text-emerald-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                    <p class="text-2xl font-black text-gray-900 leading-tight mt-0.5">
                        @if ($lastPaymentMade)
                            RM {{ number_format((float) $lastPaymentMade->amount, 2) }}
                        @else
                            RM 0.00
                        @endif
                    </p>
                    <div class="mt-1">
                        @if ($lastPaymentMade)
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="text-xs text-gray-600 font-medium">
                                    {{ $lastPaymentMade->paid_at ? $lastPaymentMade->paid_at->format('d M Y') : ($lastPaymentMade->created_at ? $lastPaymentMade->created_at->format('d M Y') : 'Completed') }}
                                </span>
                                @if ($lastPaymentMade->payment_method)
                                    <span class="text-[10px] font-bold bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded uppercase">
                                        {{ str_replace('_', ' ', $lastPaymentMade->payment_method) }}
                                    </span>
                                @endif
                            </div>
                        @else
                            <span class="text-xs text-gray-400 font-medium">No payments recorded</span>
                        @endif
                    </div>
                </div>
            </a>

            {{-- 3. Next Payment Due --}}
            @php
                $isOverdue = $nextPaymentDue?->is_overdue ?? false;
                $hasDue = $nextPaymentDue && ($nextPaymentDue->amount > 0);
            @endphp
            <a href="{{ route('parent.payments') }}" class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-3.5 hover:shadow-md hover:border-indigo-200 transition-all cursor-pointer group">
                <div class="w-12 h-12 rounded-2xl {{ $hasDue ? ($isOverdue ? 'bg-rose-500 shadow-rose-500/20' : 'bg-indigo-600 shadow-indigo-500/20') : 'bg-teal-600 shadow-teal-500/20' }} group-hover:scale-105 text-white flex items-center justify-center shrink-0 shadow-md transition-transform">
                    @if ($hasDue && $isOverdue)
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    @elseif ($hasDue)
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    @else
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Next Payment Due</p>
                        <svg class="w-3.5 h-3.5 text-gray-300 group-hover:text-indigo-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                    <p class="text-2xl font-black {{ $hasDue ? ($isOverdue ? 'text-rose-600' : 'text-gray-900') : 'text-gray-900' }} leading-tight mt-0.5">
                        @if ($hasDue)
                            RM {{ number_format((float) $nextPaymentDue->amount, 2) }}
                        @else
                            RM 0.00
                        @endif
                    </p>
                    <div class="mt-1">
                        @if ($hasDue)
                            <div class="flex items-center gap-1.5 flex-wrap">
                                @if ($nextPaymentDue->is_overdue)
                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded-md">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0"></span>
                                        {{ $nextPaymentDue->status_label }}
                                    </span>
                                @elseif ($nextPaymentDue->is_today)
                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                                        Due today
                                    </span>
                                @elseif ($nextPaymentDue->due_date)
                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md">
                                        Due {{ $nextPaymentDue->due_date->format('d M Y') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md">
                                        Pending
                                    </span>
                                @endif
                                @if (!empty($nextPaymentDue->subtext))
                                    <span class="text-xs text-gray-400 font-medium truncate max-w-[120px]" title="{{ $nextPaymentDue->subtext }}">
                                        {{ $nextPaymentDue->subtext }}
                                    </span>
                                @endif
                            </div>
                        @else
                            <span class="inline-flex items-center gap-1 text-xs text-emerald-600 font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                All settled • No upcoming dues
                            </span>
                        @endif
                    </div>
                </div>
            </a>
        </div>

        {{-- ── 2. MIDDLE ROW: Academic + Needs Attention side-by-side on mobile, 3-col on desktop ── --}}
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 lg:gap-5">

            {{-- Academic Performance --}}
            <div class="bg-white rounded-3xl p-3 lg:p-6 border border-gray-100 shadow-sm flex flex-col justify-between space-y-3 lg:space-y-4">
                <div>
                    <div class="mb-3 lg:mb-4">
                        <h3 class="font-heading font-extrabold text-xs lg:text-base text-gray-900">Academic Performance</h3>
                        <p class="text-[10px] lg:text-xs text-gray-400 font-medium">Performance by subject</p>
                    </div>

                    <div class="space-y-3.5 text-xs font-bold">
                        @forelse ($childProgress as $sub)
                            @php
                                $dot = $sub['pct'] >= 70 ? 'bg-emerald-500' : ($sub['pct'] >= 50 ? 'bg-amber-500' : 'bg-red-500');
                            @endphp
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-gray-800 font-bold">{{ $sub['subject'] }}</span>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-gray-900 font-extrabold">{{ $sub['pct'] }}%</span>
                                        <span class="w-2 h-2 rounded-full {{ $dot }}"></span>
                                    </div>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-1.5">
                                    <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $sub['pct'] }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-400 text-xs">No enrolled subjects found.</p>
                        @endforelse
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-50">
                    <a href="{{ route('parent.progress') }}" class="text-xs font-extrabold text-blue-600 hover:text-blue-800 inline-flex items-center gap-1">
                        View all subjects <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>

            {{-- Needs Attention --}}
            <div class="bg-amber-50/40 rounded-3xl p-3 lg:p-6 border border-amber-200/70 shadow-sm flex flex-col justify-between space-y-3 lg:space-y-4">
                <div>
                    <div class="flex items-center gap-2 mb-3 lg:mb-4">
                        <div class="w-6 h-6 lg:w-7 lg:h-7 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5 lg:w-4 lg:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-heading font-extrabold text-xs lg:text-base text-gray-900">Needs Attention</h3>
                            <p class="text-[10px] lg:text-xs text-amber-800/80 font-medium">Subjects below 70%</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        @forelse ($needsAttention as $sub)
                            <div class="p-3.5 bg-white rounded-2xl border border-amber-200/60 flex items-center justify-between">
                                <div>
                                    <h4 class="text-xs font-extrabold text-gray-900">{{ $sub['subject'] }}</h4>
                                    <p class="text-[11px] text-amber-700 font-semibold mt-0.5">Low score alert</p>
                                </div>
                                <span class="text-xs font-black text-amber-600 bg-amber-50 px-2.5 py-1 rounded-xl border border-amber-200/60">{{ $sub['pct'] }}%</span>
                            </div>
                        @empty
                            <div class="p-3.5 bg-white rounded-2xl border border-emerald-200/60 flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="text-xs font-bold text-gray-700">All subjects above 70% — great job!</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Coming Up — spans full width on mobile (col-span-2), 1 col on desktop ── --}}
            <div class="col-span-2 lg:col-span-1 bg-white rounded-3xl p-3 lg:p-6 border border-gray-100 shadow-sm flex flex-col justify-between space-y-3 lg:space-y-4">
                <div>
                    <div class="flex items-center gap-2.5 mb-3 lg:mb-4">
                        <div class="w-7 h-7 lg:w-8 lg:h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5 lg:w-4 lg:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-heading font-extrabold text-xs lg:text-base text-gray-900">Coming Up</h3>
                            <p class="text-[10px] lg:text-xs text-gray-400 font-medium">Upcoming classes</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-2.5 lg:gap-3">
                        @forelse ($upcomingClasses as $lc)
                            @php
                                $isToday = $lc->scheduled_at->isToday();
                                $label   = $isToday ? 'Today' : $lc->scheduled_at->format('j M');
                                $badgeCls = $isToday ? 'bg-blue-50 text-blue-700 border border-blue-100' : 'bg-gray-100 text-gray-700';
                            @endphp
                            <div class="p-2.5 lg:p-3.5 bg-gray-50 rounded-2xl border border-gray-100 flex items-center justify-between gap-2">
                                <div>
                                    <h4 class="text-[11px] lg:text-xs font-extrabold text-gray-900">{{ $lc->course->subject->name ?? 'N/A' }}</h4>
                                    <p class="text-[10px] lg:text-[11px] text-gray-500 font-medium mt-0.5">
                                        {{ $isToday ? 'Today' : $lc->scheduled_at->format('D, j M') }} • {{ $lc->scheduled_at->format('g:i A') }}
                                    </p>
                                </div>
                                <span class="px-2 py-0.5 lg:px-2.5 lg:py-1 rounded-xl {{ $badgeCls }} font-black text-[9px] lg:text-[10px] uppercase shrink-0">{{ $label }}</span>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 font-medium">No upcoming classes scheduled.</p>
                        @endforelse
                    </div>
                </div>

                <div class="pt-3 lg:pt-4 border-t border-gray-50">
                    <a href="{{ route('parent.classes') }}" class="text-xs font-extrabold text-blue-600 hover:text-blue-800 inline-flex items-center gap-1">
                        View full schedule <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>
        </div>

        {{-- ── 3. BOTTOM 2-COLUMN ROW ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            {{-- Monthly Attendance Summary --}}
            <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm flex flex-col justify-between space-y-4">
                <div>
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-heading font-extrabold text-base text-gray-900">Attendance – {{ now()->format('F Y') }}</h3>
                            <p class="text-xs text-gray-400 font-medium">This month's attendance log</p>
                        </div>
                    </div>

                    <div class="p-4 bg-blue-50/50 rounded-2xl border border-blue-100/70">
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <span class="text-[10px] text-gray-400 font-bold block">Attended</span>
                                <span class="text-xl font-black text-emerald-600">{{ $attStats['attended'] }}</span>
                            </div>
                            <div class="border-x border-blue-100">
                                <span class="text-[10px] text-gray-400 font-bold block">Absent</span>
                                <span class="text-xl font-black text-red-500">{{ $attStats['absent'] }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-gray-400 font-bold block">Rate</span>
                                <span class="text-xl font-black text-blue-700">{{ $attStats['pct'] }}%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-50">
                    <a href="{{ route('parent.attendance') }}" class="text-xs font-extrabold text-blue-600 hover:text-blue-800 inline-flex items-center gap-1">
                        View attendance details <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>

            {{-- Payment --}}
            <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm flex flex-col justify-between space-y-4">
                <div>
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-heading font-extrabold text-base text-gray-900">Payment</h3>
                            <p class="text-xs text-gray-400 font-medium">Latest payment status</p>
                        </div>
                    </div>

                    @if ($latestPayment)
                        @php $isPaid = $latestPayment->status === 'paid'; @endphp
                        <div class="p-4 {{ $isPaid ? 'bg-emerald-50/70 border-emerald-100' : 'bg-amber-50/70 border-amber-100' }} rounded-2xl border flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-full {{ $isPaid ? 'bg-emerald-500' : 'bg-amber-500' }} text-white flex items-center justify-center shrink-0 shadow-sm">
                                @if ($isPaid)
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                @else
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/></svg>
                                @endif
                            </div>
                            <div>
                                <h4 class="text-xs font-black {{ $isPaid ? 'text-emerald-950' : 'text-amber-950' }}">
                                    {{ $latestPayment->description ?? 'Payment' }}
                                </h4>
                                <p class="text-[11px] {{ $isPaid ? 'text-emerald-700' : 'text-amber-700' }} font-medium mt-0.5">
                                    RM {{ number_format($latestPayment->amount, 2) }} •
                                    {{ $latestPayment->paid_at ? $latestPayment->paid_at->format('j M Y') : ucfirst($latestPayment->status) }}
                                </p>
                            </div>
                        </div>

                        @if ($outstanding > 0)
                            <div class="p-3 bg-red-50 rounded-xl border border-red-100 text-xs font-bold text-red-700">
                                ⚠ Outstanding balance: RM {{ number_format($outstanding, 2) }}
                            </div>
                        @endif
                    @else
                        <p class="text-xs text-gray-400 font-medium">No payment records found.</p>
                    @endif
                </div>

                <div class="pt-4 border-t border-gray-50">
                    <a href="#" class="text-xs font-extrabold text-blue-600 hover:text-blue-800 inline-flex items-center gap-1">
                        View payment history <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>
        </div>
        @endif

        {{-- ── ADD / CONNECT CHILD MODAL ── --}}
        <div x-show="showAddChildModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 w-full max-w-lg p-6 space-y-5"
                 @click.away="showAddChildModal = false">

                {{-- Modal Header --}}
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-heading font-black text-lg text-gray-900">Link Child Account</h3>
                            <p class="text-[11px] text-gray-400 font-medium">Connect an existing student by IC or register a new child.</p>
                        </div>
                    </div>
                    <button @click="showAddChildModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg cursor-pointer">✕</button>
                </div>

                {{-- Tabs --}}
                <div class="grid grid-cols-2 gap-2 bg-gray-100 p-1 rounded-2xl text-xs font-bold">
                    <button type="button" @click="tab = 'connect'"
                        class="py-2.5 rounded-xl transition-all cursor-pointer"
                        :class="tab === 'connect' ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-500 hover:text-gray-700'">
                        Connect Existing Student
                    </button>
                    <button type="button" @click="tab = 'create'"
                        class="py-2.5 rounded-xl transition-all cursor-pointer"
                        :class="tab === 'create' ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-500 hover:text-gray-700'">
                        Register New Child
                    </button>
                </div>

                {{-- ═══ TAB A: CONNECT by IC or Email ═══ --}}
                <div x-show="tab === 'connect'" class="space-y-4">
                    <form method="POST" action="{{ route('parent.children.connect') }}" class="space-y-4 text-xs font-semibold">
                        @csrf

                        @if($errors->has('identifier'))
                            <p class="text-xs text-red-600 font-semibold bg-red-50 border border-red-200 rounded-xl p-3">{{ $errors->first('identifier') }}</p>
                        @endif

                        <div class="space-y-1.5">
                            <label class="text-gray-700 block font-bold text-xs">
                                Student's MyKad / IC Number or Email <span class="text-red-500">*</span>
                            </label>
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <input type="text"
                                           name="identifier"
                                           x-model="connectInput"
                                           @input="onConnectInput($event)"
                                           required
                                           placeholder="e.g. 123456-78-9012 or student@email.com"
                                           class="w-full rounded-2xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 p-3 text-xs tracking-wide">
                                </div>
                                <button type="button"
                                        @click="verifyStudent()"
                                        :disabled="connectInput.replace(/\D/g, '').length !== 12"
                                        :class="connectInput.replace(/\D/g, '').length === 12 ? 'bg-blue-600 hover:bg-blue-700 text-white cursor-pointer' : 'bg-gray-200 text-gray-400 cursor-not-allowed'"
                                        class="px-4 py-2.5 rounded-2xl font-bold text-xs shrink-0 transition-colors">
                                    Verify
                                </button>
                            </div>
                        </div>

                        {{-- Checking state --}}
                        <div x-show="connectStatus === 'checking'" class="flex items-center gap-2 text-xs text-muted">
                            <svg class="w-4 h-4 animate-spin text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Searching for student...
                        </div>

                        {{-- Found Student Card --}}
                        <div x-show="connectStatus === 'found'" class="p-3 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-start gap-3">
                            <div class="w-7 h-7 bg-emerald-500 rounded-lg flex items-center justify-center shrink-0 text-white font-bold text-xs">✓</div>
                            <div>
                                <p class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider">Student Found</p>
                                <p class="text-xs font-bold text-charcoal" x-text="connectStudentName"></p>
                                <p class="text-[11px] text-muted" x-text="[connectStudentLevel, connectStudentSchool].filter(Boolean).join(' · ')"></p>
                            </div>
                        </div>

                        {{-- Not Found --}}
                        <div x-show="connectStatus === 'not_found'" class="p-3 bg-red-50 border border-red-200 rounded-2xl text-xs text-red-600 font-medium">
                            No student found with this IC number. Check the number or use <strong>Register New Child</strong>.
                        </div>

                        <div class="space-y-1">
                            <label class="text-gray-700 block font-bold text-xs">Relationship to Child <span class="text-red-500">*</span></label>
                            <select name="relationship" required class="w-full rounded-2xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 p-3 text-xs">
                                <option value="Father">Father</option>
                                <option value="Mother">Mother</option>
                                <option value="Guardian" selected>Guardian</option>
                                <option value="Others">Others</option>
                            </select>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                            <button type="button" @click="showAddChildModal = false" class="py-2.5 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-bold text-xs cursor-pointer">Cancel</button>
                            <button type="submit" class="py-2.5 px-5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-extrabold shadow-sm text-xs cursor-pointer">Connect Student</button>
                        </div>
                    </form>
                </div>

                {{-- ═══ TAB B: CREATE new child account ═══ --}}
                <div x-show="tab === 'create'">
                    <form method="POST" action="{{ route('parent.children.store') }}" class="space-y-3.5 text-xs font-semibold">
                        @csrf

                        <div class="space-y-1">
                            <label class="text-gray-700 block font-bold text-xs">Child's Full Name (as per IC) <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required value="{{ old('name') }}" placeholder="e.g. Muhammad Adam bin Yunus" class="w-full rounded-2xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 p-2.5 text-xs">
                        </div>

                        <div class="space-y-1">
                            <label class="text-gray-700 block font-bold text-xs">Child's MyKad / IC Number <span class="text-red-500">*</span></label>
                            <input type="text"
                                   name="child_ic_number"
                                   x-model="newChildIc"
                                   @input="onNewChildIcInput($event)"
                                   required
                                   maxlength="14"
                                   placeholder="e.g. 123456-78-9012"
                                   class="w-full rounded-2xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 p-2.5 text-xs">
                        </div>

                        {{-- Auto-detected DOB and Gender text boxes --}}
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-gray-700 block text-xs font-bold">Date of Birth</label>
                                <input type="text" x-model="newChildDob" placeholder="YYYY-MM-DD" readonly class="w-full rounded-xl border-gray-200 bg-gray-50/70 p-2 text-xs text-gray-700">
                            </div>
                            <div>
                                <label class="text-gray-700 block text-xs font-bold">Gender</label>
                                <input type="text" x-model="newChildGender" placeholder="Male / Female" readonly class="w-full rounded-xl border-gray-200 bg-gray-50/70 p-2 text-xs text-gray-700">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div class="space-y-1">
                                <label class="text-gray-700 block font-bold text-xs">Education Level <span class="text-red-500">*</span></label>
                                <select name="level" required class="w-full rounded-2xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 p-2.5 text-xs">
                                    <optgroup label="Secondary School (Form 1–5)">
                                        <option value="Form 5">Form 5</option>
                                        <option value="Form 4">Form 4</option>
                                        <option value="Form 3">Form 3</option>
                                        <option value="Form 2">Form 2</option>
                                        <option value="Form 1">Form 1</option>
                                    </optgroup>
                                    <optgroup label="Primary School (Year 1–6)">
                                        <option value="Year 6">Year 6</option>
                                        <option value="Year 5">Year 5</option>
                                        <option value="Year 4">Year 4</option>
                                        <option value="Year 3">Year 3</option>
                                        <option value="Year 2">Year 2</option>
                                        <option value="Year 1">Year 1</option>
                                    </optgroup>
                                    <optgroup label="Kindergarten">
                                        <option value="Kindergarten (Age 4-6)">Kindergarten (Age 4–6)</option>
                                        <option value="Tadika (Age 5-6)">Tadika (Age 5–6)</option>
                                        <option value="Preschool">Preschool</option>
                                    </optgroup>
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="text-gray-700 block font-bold text-xs">Relationship <span class="text-red-500">*</span></label>
                                <select name="relationship" required class="w-full rounded-2xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 p-2.5 text-xs">
                                    <option value="Father">Father</option>
                                    <option value="Mother">Mother</option>
                                    <option value="Guardian" selected>Guardian</option>
                                    <option value="Others">Others</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-gray-700 block font-bold text-xs">School Name <span class="text-gray-400 font-normal">(Optional)</span></label>
                            <input type="text" name="school_name" value="{{ old('school_name') }}" placeholder="e.g. SMK Taman Melawati" class="w-full rounded-2xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 p-2.5 text-xs">
                        </div>

                        <div class="space-y-1">
                            <label class="text-gray-700 block font-bold text-xs">Child Password <span class="text-gray-400 font-normal">(Optional)</span></label>
                            <input type="text" name="password" placeholder="Leave blank to auto-generate" class="w-full rounded-2xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 p-2.5 text-xs">
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                            <button type="button" @click="showAddChildModal = false" class="py-2.5 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-bold text-xs cursor-pointer">Cancel</button>
                            <button type="submit" class="py-2.5 px-5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-extrabold shadow-sm text-xs cursor-pointer">Register & Link Child</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>

        {{-- ── WELCOME GREETING POPUP MODAL ── --}}
        @include('parent.partials.greeting-modal')

    </div>

<script>
function addChildModalData() {
    return {
        showAddChildModal: {{ $errors->has('identifier') || $errors->has('child_ic_number') || $errors->has('name') ? 'true' : 'false' }},
        tab: '{{ $errors->has('name') || $errors->has('child_ic_number') ? 'create' : 'connect' }}',

        // Connect Tab
        connectInput: '{{ old('identifier', '') }}',
        connectStatus: 'idle',
        connectFound: false,
        connectStudentName: '',
        connectStudentLevel: '',
        connectStudentSchool: '',

        // Create Tab
        newChildIc: '{{ old('child_ic_number', '') }}',
        newChildDob: '',
        newChildGender: '',

        formatIC(raw) {
            let digits = raw.replace(/\D/g, '').substring(0, 12);
            if (digits.length <= 6) return digits;
            if (digits.length <= 8) return digits.substring(0, 6) + '-' + digits.substring(6);
            return digits.substring(0, 6) + '-' + digits.substring(6, 8) + '-' + digits.substring(8);
        },

        onConnectInput(event) {
            let val = event.target.value;
            if (/^\d/.test(val)) {
                let formatted = this.formatIC(val);
                event.target.value = formatted;
                this.connectInput = formatted;
            } else {
                this.connectInput = val;
            }
            this.connectStatus = 'idle';
            this.connectFound = false;
        },

        onNewChildIcInput(event) {
            let formatted = this.formatIC(event.target.value);
            event.target.value = formatted;
            this.newChildIc = formatted;
            let digits = formatted.replace(/\D/g, '');
            if (digits.length === 12) {
                let yy = digits.substring(0, 2);
                let mm = digits.substring(2, 4);
                let dd = digits.substring(4, 6);
                let currentYY = new Date().getFullYear() % 100;
                let year = parseInt(yy) > currentYY ? '19' + yy : '20' + yy;
                this.newChildDob = year + '-' + mm + '-' + dd;
                let lastDigit = parseInt(digits.charAt(11));
                this.newChildGender = lastDigit % 2 === 1 ? 'Male' : 'Female';
            } else {
                this.newChildDob = '';
                this.newChildGender = '';
            }
        },

        async verifyStudent() {
            let digits = this.connectInput.replace(/\D/g, '');
            if (digits.length !== 12) return;
            this.connectStatus = 'checking';
            this.connectFound = false;
            try {
                const res = await fetch('{{ route('register.verify.ic') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify({ ic_number: this.connectInput })
                });
                const data = await res.json();
                if (data.found) {
                    this.connectStatus = 'found';
                    this.connectFound = true;
                    this.connectStudentName = data.name;
                    this.connectStudentLevel = data.level || '';
                    this.connectStudentSchool = data.school || '';
                } else {
                    this.connectStatus = 'not_found';
                    this.connectFound = false;
                }
            } catch(e) {
                this.connectStatus = 'not_found';
            }
        }
    };
}
</script>
</x-dashboard-layout>

