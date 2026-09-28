<x-dashboard-layout>
    <x-slot name="title">Parent Payments & Invoices</x-slot>

    <x-slot name="sidebar">
        @include('parent.partials.sidebar')
    </x-slot>

    @php
        $pendingIds = $pendingPayments->pluck('id')->implode(',');
    @endphp

    <div x-data="{
        activeTab: 'all',
        searchQuery: '',
        showPayModal: false,
        showReceiptModal: false,
        showNewInvoiceModal: false,
        payAllMode: false,
        currentInvoice: null,
        paymentMethod: 'FPX Online Banking',
        selectedBank: 'Maybank2u',
        isSubmitting: false,

        openPayModal(invoice) {
            this.payAllMode = false;
            this.currentInvoice = invoice;
            this.showPayModal = true;
        },

        openPayAllModal() {
            this.payAllMode = true;
            this.currentInvoice = {
                id: '{{ $pendingIds }}',
                description: 'All Pending Tuition Invoices ({{ $pendingCount }} items)',
                amount: '{{ $totalOutstanding }}',
                reference_no: 'BATCH-PAY-{{ date('Ym') }}'
            };
            this.showPayModal = true;
        },

        openReceiptModal(invoice) {
            this.currentInvoice = invoice;
            this.showReceiptModal = true;
        },

        matchesSearch(item) {
            if (!this.searchQuery) return true;
            var q = this.searchQuery.toLowerCase();
            return (item.description && item.description.toLowerCase().indexOf(q) !== -1) ||
                   (item.reference_no && item.reference_no.toLowerCase().indexOf(q) !== -1) ||
                   (item.status && item.status.toLowerCase().indexOf(q) !== -1);
        }
    }" class="space-y-6">

        {{-- ── FLASH ALERTS ── --}}
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-3xl text-xs font-semibold text-emerald-800 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 font-bold ml-4">✕</button>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 rounded-3xl text-xs font-semibold text-rose-800 space-y-1 shadow-xs">
                <div class="flex items-center gap-2 mb-1">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <strong>Please review the following:</strong>
                </div>
                <ul class="list-disc list-inside pl-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ── TOP HEADER SECTION ── --}}
        <div class="bg-white rounded-3xl p-4 sm:p-6 border border-gray-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="font-heading font-black text-xl sm:text-2xl text-gray-900">Payments & Invoices</h1>
                <p class="text-xs text-gray-500 font-medium mt-0.5">Manage monthly tuition fee payments, download official receipts, and track billing.</p>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center gap-2.5 sm:gap-3 w-full sm:w-auto">
                @if ($children->count() > 1)
                    <div class="flex items-center gap-2 bg-gray-50 px-3.5 py-2.5 rounded-2xl border border-gray-200/70 w-full sm:w-auto min-w-0">
                        <span class="text-xs font-bold text-gray-500 shrink-0">Viewing:</span>
                        <select onchange="window.location.href='{{ route('parent.payments') }}?child_id=' + this.value" class="bg-transparent border-0 text-xs font-extrabold text-gray-900 focus:ring-0 cursor-pointer py-0 pl-1 pr-6 outline-none truncate w-full sm:max-w-[200px]">
                            <option value="">All Children</option>
                            @foreach ($children as $ch)
                                <option value="{{ $ch->id }}" {{ $child && $child->id === $ch->id ? 'selected' : '' }}>
                                    {{ $ch->user->name ?? 'Student' }} – {{ $ch->level ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if ($totalOutstanding > 0)
                    <button @click="openPayAllModal()" class="py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-xs font-extrabold shadow-sm transition-all cursor-pointer inline-flex items-center justify-center gap-1.5 shrink-0 w-full sm:w-auto">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Pay Outstanding (RM {{ number_format($totalOutstanding, 2) }})</span>
                    </button>
                @endif
            </div>
        </div>

        {{-- ── 1. FINANCIAL METRIC CARDS ── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5 lg:gap-4">

            {{-- 1. Outstanding --}}
            <div class="bg-white rounded-3xl p-4 sm:p-5 border border-gray-100 shadow-sm flex items-center gap-3.5 hover:shadow-md transition-all">
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl {{ $totalOutstanding > 0 ? 'bg-amber-500 shadow-amber-500/20' : 'bg-blue-600 shadow-blue-500/20' }} text-white flex items-center justify-center shrink-0 shadow-md">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] sm:text-[11px] font-bold text-gray-400 uppercase tracking-wider">Outstanding</p>
                    <p class="text-lg sm:text-2xl font-black {{ $totalOutstanding > 0 ? 'text-amber-600' : 'text-gray-900' }} leading-tight mt-0.5">
                        RM {{ number_format($totalOutstanding, 2) }}
                    </p>
                    <div class="mt-1">
                        @if ($totalOutstanding > 0)
                            <span class="inline-flex items-center gap-1 text-[10px] sm:text-xs text-amber-700 font-semibold bg-amber-50 px-2 py-0.5 rounded-md truncate max-w-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                                {{ $pendingCount }} pending invoice{{ $pendingCount !== 1 ? 's' : '' }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-[10px] sm:text-xs text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded-md truncate max-w-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                All settled
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- 2. Last Payment Made --}}
            <div class="bg-white rounded-3xl p-4 sm:p-5 border border-gray-100 shadow-sm flex items-center gap-3.5 hover:shadow-md transition-all">
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-emerald-500/20">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] sm:text-[11px] font-bold text-gray-400 uppercase tracking-wider">Last Payment Made</p>
                    <p class="text-lg sm:text-2xl font-black text-gray-900 leading-tight mt-0.5">
                        @if ($lastPaymentMade)
                            RM {{ number_format((float) $lastPaymentMade->amount, 2) }}
                        @else
                            RM 0.00
                        @endif
                    </p>
                    <div class="mt-1">
                        @if ($lastPaymentMade)
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="text-[10px] sm:text-xs text-gray-600 font-medium">
                                    {{ $lastPaymentMade->paid_at ? $lastPaymentMade->paid_at->format('d M Y') : ($lastPaymentMade->created_at ? $lastPaymentMade->created_at->format('d M Y') : 'Completed') }}
                                </span>
                                @if ($lastPaymentMade->payment_method)
                                    <span class="text-[10px] font-bold bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded uppercase">
                                        {{ str_replace('_', ' ', $lastPaymentMade->payment_method) }}
                                    </span>
                                @endif
                            </div>
                        @else
                            <span class="text-[10px] sm:text-xs text-gray-400 font-medium">No payments recorded</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- 3. Next Payment Due --}}
            <div class="bg-white rounded-3xl p-4 sm:p-5 border border-gray-100 shadow-sm flex items-center gap-3.5 hover:shadow-md transition-all">
                @php
                    $isOverdue = $nextPaymentDue?->is_overdue ?? false;
                    $hasDue = $nextPaymentDue && ($nextPaymentDue->amount > 0);
                @endphp
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl {{ $hasDue ? ($isOverdue ? 'bg-rose-500 shadow-rose-500/20' : 'bg-indigo-600 shadow-indigo-500/20') : 'bg-teal-600 shadow-teal-500/20' }} text-white flex items-center justify-center shrink-0 shadow-md">
                    @if ($hasDue && $isOverdue)
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    @elseif ($hasDue)
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    @else
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] sm:text-[11px] font-bold text-gray-400 uppercase tracking-wider">Next Payment Due</p>
                    <p class="text-lg sm:text-2xl font-black {{ $hasDue ? ($isOverdue ? 'text-rose-600' : 'text-gray-900') : 'text-gray-900' }} leading-tight mt-0.5">
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
                                    <span class="inline-flex items-center gap-1 text-[10px] sm:text-xs font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded-md">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0"></span>
                                        {{ $nextPaymentDue->status_label }}
                                    </span>
                                @elseif ($nextPaymentDue->is_today)
                                    <span class="inline-flex items-center gap-1 text-[10px] sm:text-xs font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                                        Due today
                                    </span>
                                @elseif ($nextPaymentDue->due_date)
                                    <span class="inline-flex items-center gap-1 text-[10px] sm:text-xs font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md">
                                        Due {{ $nextPaymentDue->due_date->format('d M Y') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[10px] sm:text-xs font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md">
                                        Pending
                                    </span>
                                @endif
                                @if (!empty($nextPaymentDue->subtext))
                                    <span class="text-[10px] text-gray-400 font-medium truncate max-w-[120px]" title="{{ $nextPaymentDue->subtext }}">
                                        {{ $nextPaymentDue->subtext }}
                                    </span>
                                @endif
                            </div>
                        @else
                            <span class="inline-flex items-center gap-1 text-[10px] sm:text-xs text-emerald-600 font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                All settled • No upcoming dues
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ── OUTSTANDING INVOICES BANNER (IF ANY) ── --}}
        @if ($totalOutstanding > 0)
            <div class="p-5 bg-gradient-to-r from-amber-500/10 via-amber-50 to-orange-50 border border-amber-200/80 rounded-3xl shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-amber-500/20 mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-heading font-extrabold text-sm text-gray-900">You have {{ $pendingCount }} pending tuition fee invoice(s)</h3>
                        <p class="text-xs text-gray-600 font-medium mt-0.5">Total outstanding amount is <strong class="text-amber-700 font-bold">RM {{ number_format($totalOutstanding, 2) }}</strong>. Please complete payment to maintain uninterrupted access to live classes and study materials.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2.5 shrink-0">
                    <button @click="openPayAllModal()" class="py-2.5 px-5 bg-amber-600 hover:bg-amber-700 text-white rounded-2xl text-xs font-black shadow-md shadow-amber-600/20 transition-all cursor-pointer inline-flex items-center gap-1.5">
                        <span>Pay Total (RM {{ number_format($totalOutstanding, 2) }})</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </div>
        @endif

        {{-- ── 2. TRANSACTIONS & INVOICES TABLE ── --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            
            {{-- Toolbar: Tabs & Search Filter --}}
            <div class="p-4 sm:p-6 border-b border-gray-100 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                {{-- Filter Tabs --}}
                <div class="flex items-center gap-1.5 p-1.5 bg-gray-100/80 rounded-2xl w-fit">
                    <button @click="activeTab = 'all'" :class="activeTab === 'all' ? 'bg-white text-gray-900 shadow-xs font-extrabold' : 'text-gray-500 hover:text-gray-700 font-bold'" class="py-1.5 px-3.5 rounded-xl text-xs transition-all cursor-pointer">
                        All ({{ count($payments) }})
                    </button>
                    <button @click="activeTab = 'pending'" :class="activeTab === 'pending' ? 'bg-white text-amber-700 shadow-xs font-extrabold' : 'text-gray-500 hover:text-gray-700 font-bold'" class="py-1.5 px-3.5 rounded-xl text-xs transition-all cursor-pointer">
                        Pending / Due ({{ $pendingCount }})
                    </button>
                    <button @click="activeTab = 'paid'" :class="activeTab === 'paid' ? 'bg-white text-emerald-700 shadow-xs font-extrabold' : 'text-gray-500 hover:text-gray-700 font-bold'" class="py-1.5 px-3.5 rounded-xl text-xs transition-all cursor-pointer">
                        Paid / Receipts ({{ $paidCount }})
                    </button>
                </div>

                {{-- Search Box --}}
                <div class="relative w-full sm:w-72">
                    <input type="text" x-model="searchQuery" placeholder="Search by subject, ref, invoice..." class="w-full bg-gray-50 border border-gray-200/70 text-xs rounded-2xl pl-9 pr-3.5 py-2 text-gray-800 placeholder-gray-400 focus:bg-white focus:outline-none focus:border-blue-500 transition-all font-medium">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            {{-- Table View (Desktop & Tablet) --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50 text-[11px] font-extrabold text-gray-400 uppercase tracking-wider">
                            <th class="py-3.5 px-6">Invoice / Ref #</th>
                            <th class="py-3.5 px-6">Description & Subject</th>
                            <th class="py-3.5 px-6">Date</th>
                            <th class="py-3.5 px-6">Amount</th>
                            <th class="py-3.5 px-6">Payment Method</th>
                            <th class="py-3.5 px-6">Status</th>
                            <th class="py-3.5 px-6 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-xs font-medium text-gray-700">
                        @forelse ($payments as $p)
                            @php
                                $itemJson = json_encode([
                                    'id'             => $p->id,
                                    'reference_no'   => $p->reference_no ?? 'OS-INV-' . str_pad($p->id, 5, '0', STR_PAD_LEFT),
                                    'description'    => $p->description,
                                    'amount'         => number_format((float) $p->amount, 2),
                                    'amount_raw'     => (float) $p->amount,
                                    'status'         => $p->status,
                                    'payment_method' => $p->payment_method ?? 'Not specified',
                                    'date'           => $p->created_at ? $p->created_at->format('j M Y') : 'N/A',
                                    'paid_at'        => $p->paid_at ? $p->paid_at->format('j M Y, g:i A') : 'Pending',
                                    'subject'        => $p->course->subject->name ?? 'Tuition Fee',
                                    'parent_name'    => auth()->user()->name,
                                    'parent_email'   => auth()->user()->email,
                                ]);
                            @endphp
                            <tr x-show="(activeTab === 'all' || activeTab === '{{ $p->status }}') && matchesSearch({{ $itemJson }})" class="hover:bg-blue-50/20 transition-colors">
                                <td class="py-4 px-6 font-bold text-gray-900">
                                    <span class="font-mono text-xs text-blue-600 bg-blue-50/70 border border-blue-100 px-2 py-0.5 rounded-lg">
                                        {{ $p->reference_no ?? 'OS-INV-' . str_pad($p->id, 5, '0', STR_PAD_LEFT) }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="font-extrabold text-gray-900">{{ $p->description }}</div>
                                    @if ($p->course && $p->course->subject)
                                        <span class="text-[10px] font-bold text-gray-500 bg-gray-100 px-2 py-0.5 rounded-md mt-1 inline-block">
                                            {{ $p->course->subject->name }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-gray-600">
                                    {{ $p->created_at ? $p->created_at->format('j M Y') : '—' }}
                                    @if ($p->paid_at)
                                        <div class="text-[10px] text-gray-400 font-normal">Paid: {{ $p->paid_at->format('j M Y') }}</div>
                                    @endif
                                </td>
                                <td class="py-4 px-6 font-black text-gray-900 text-sm">
                                    RM {{ number_format((float) $p->amount, 2) }}
                                </td>
                                <td class="py-4 px-6 text-gray-500 text-[11px]">
                                    {{ $p->payment_method ?? '—' }}
                                </td>
                                <td class="py-4 px-6">
                                    @if ($p->status === 'paid')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200/60">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Paid
                                        </span>
                                    @elseif ($p->status === 'overdue')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-rose-50 text-rose-700 text-xs font-bold border border-rose-200/60">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Overdue
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-amber-50 text-amber-700 text-xs font-bold border border-amber-200/60">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-right">
                                    @if ($p->status === 'paid')
                                        <button @click="openReceiptModal({{ $itemJson }})" class="py-1.5 px-3 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-xl text-xs font-bold transition-all cursor-pointer inline-flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Receipt</span>
                                        </button>
                                    @else
                                        <button @click="openPayModal({{ $itemJson }})" class="py-1.5 px-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-black shadow-xs transition-all cursor-pointer inline-flex items-center gap-1">
                                            <span>Pay Now</span>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-gray-400 text-xs">
                                    No invoices or payment records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile Cards View --}}
            <div class="block md:hidden p-4 space-y-3">
                @forelse ($payments as $p)
                    @php
                        $itemJson = json_encode([
                            'id'             => $p->id,
                            'reference_no'   => $p->reference_no ?? 'OS-INV-' . str_pad($p->id, 5, '0', STR_PAD_LEFT),
                            'description'    => $p->description,
                            'amount'         => number_format((float) $p->amount, 2),
                            'amount_raw'     => (float) $p->amount,
                            'status'         => $p->status,
                            'payment_method' => $p->payment_method ?? 'Not specified',
                            'date'           => $p->created_at ? $p->created_at->format('j M Y') : 'N/A',
                            'paid_at'        => $p->paid_at ? $p->paid_at->format('j M Y, g:i A') : 'Pending',
                            'subject'        => $p->course->subject->name ?? 'Tuition Fee',
                            'parent_name'    => auth()->user()->name,
                            'parent_email'   => auth()->user()->email,
                        ]);
                    @endphp
                    <div x-show="(activeTab === 'all' || activeTab === '{{ $p->status }}') && matchesSearch({{ $itemJson }})" class="p-4 bg-gray-50/70 border border-gray-100 rounded-2xl space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <span class="font-mono text-[10px] text-blue-600 bg-blue-50 border border-blue-100 px-2 py-0.5 rounded-md">
                                    {{ $p->reference_no ?? 'OS-INV-' . str_pad($p->id, 5, '0', STR_PAD_LEFT) }}
                                </span>
                                <h4 class="font-extrabold text-xs text-gray-900 mt-1.5">{{ $p->description }}</h4>
                            </div>
                            @if ($p->status === 'paid')
                                <span class="px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 text-[10px] font-bold border border-emerald-200">Paid</span>
                            @elseif ($p->status === 'overdue')
                                <span class="px-2 py-0.5 rounded-lg bg-rose-50 text-rose-700 text-[10px] font-bold border border-rose-200">Overdue</span>
                            @else
                                <span class="px-2 py-0.5 rounded-lg bg-amber-50 text-amber-700 text-[10px] font-bold border border-amber-200">Pending</span>
                            @endif
                        </div>

                        <div class="flex items-center justify-between text-xs pt-1 border-t border-gray-200/50">
                            <div>
                                <span class="text-[10px] text-gray-400 block font-medium">Amount</span>
                                <span class="font-black text-gray-900 text-sm">RM {{ number_format((float) $p->amount, 2) }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] text-gray-400 block font-medium">Issue Date</span>
                                <span class="font-bold text-gray-700 text-xs">{{ $p->created_at ? $p->created_at->format('j M Y') : '—' }}</span>
                            </div>
                        </div>

                        <div class="pt-2">
                            @if ($p->status === 'paid')
                                <button @click="openReceiptModal({{ $itemJson }})" class="w-full py-2 bg-white hover:bg-gray-100 border border-gray-200 text-gray-800 rounded-xl text-xs font-bold transition-all inline-flex items-center justify-center gap-1.5 shadow-2xs">
                                    <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>View Receipt</span>
                                </button>
                            @else
                                <button @click="openPayModal({{ $itemJson }})" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-black shadow-sm transition-all inline-flex items-center justify-center gap-1.5">
                                    <span>Pay Now (RM {{ number_format((float) $p->amount, 2) }})</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-gray-400 text-xs">
                        No invoices or payment records found.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ── 3. OFFICIAL BANK & BILLING INQUIRY CARD ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            
            {{-- Official Bank Account Details --}}
            <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm flex flex-col justify-between space-y-4">
                <div>
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <div>
                            <h3 class="font-heading font-extrabold text-base text-gray-900">Direct Bank Transfer</h3>
                            <p class="text-xs text-gray-400 font-medium">Official Olympia Education Sdn Bhd Account</p>
                        </div>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-2xl border border-gray-200/70 space-y-2.5 text-xs font-semibold text-gray-700">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400 font-bold">Bank:</span>
                            <span class="text-gray-900 font-extrabold">Malayan Banking Berhad (Maybank)</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400 font-bold">Account Name:</span>
                            <span class="text-gray-900 font-extrabold">Olympian Scholars Sdn Bhd</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400 font-bold">Account Number:</span>
                            <span class="text-blue-700 font-black font-mono text-sm">5140 8892 3410</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400 font-bold">Reference:</span>
                            <span class="text-gray-900 font-mono text-xs">Student Full Name / Invoice Ref</span>
                        </div>
                    </div>
                </div>

                <p class="text-[11px] text-gray-400 font-medium leading-relaxed">
                    * For manual bank counter/ATM transfers, please keep your deposit slip and forward it to our billing team or WhatsApp hotline for quick manual confirmation.
                </p>
            </div>

            {{-- Billing Support Helpdesk --}}
            <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm flex flex-col justify-between space-y-4">
                <div>
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-heading font-extrabold text-base text-gray-900">Billing Helpdesk & Support</h3>
                            <p class="text-xs text-gray-400 font-medium">Need help with fee invoices, refunds, or payment plans?</p>
                        </div>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div class="p-3 bg-blue-50/50 rounded-2xl border border-blue-100 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <span class="font-bold text-gray-900 block">Billing Email Support</span>
                                <span class="text-blue-700 font-semibold text-[11px]">billing@olympia-education.com</span>
                            </div>
                        </div>

                        <div class="p-3 bg-emerald-50/50 rounded-2xl border border-emerald-100 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            </div>
                            <div>
                                <span class="font-bold text-gray-900 block">WhatsApp Tuition Hotline</span>
                                <span class="text-emerald-700 font-semibold text-[11px]">+60 12-345 6789 (Mon - Fri, 9am - 6pm)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-2">
                    <span class="text-[11px] text-gray-400 font-medium">All online transactions are securely encrypted via 256-bit SSL Gateway.</span>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════
             MODAL 1: CHECKOUT / PAY INVOICE MODAL
             ══════════════════════════════════════════════════════════════════ --}}
        <div x-show="showPayModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-xs" style="display: none;" x-transition>
            <div @click.away="showPayModal = false" class="bg-white rounded-3xl p-6 sm:p-7 max-w-lg w-full shadow-2xl border border-gray-100 relative space-y-5 max-h-[90vh] overflow-y-auto">
                
                {{-- Modal Header --}}
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-heading font-black text-lg text-gray-900">Tuition Fee Checkout</h3>
                            <p class="text-xs text-gray-400 font-medium" x-text="currentInvoice ? 'Invoice: ' + currentInvoice.reference_no : 'Secure Checkout'"></p>
                        </div>
                    </div>
                    <button @click="showPayModal = false" class="text-gray-400 hover:text-gray-700 w-8 h-8 rounded-full flex items-center justify-center bg-gray-50 hover:bg-gray-100 transition-colors">✕</button>
                </div>

                {{-- Invoice Summary Box --}}
                <div class="p-4 bg-blue-50/60 rounded-2xl border border-blue-100/80 space-y-2">
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-gray-500 font-medium">Item / Purpose:</span>
                        <span class="font-bold text-gray-900 text-right truncate max-w-[240px]" x-text="currentInvoice ? currentInvoice.description : 'Monthly Tuition'"></span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-gray-500 font-medium">Payer Name:</span>
                        <span class="font-bold text-gray-900">{{ auth()->user()->name }}</span>
                    </div>
                    <div class="pt-2 border-t border-blue-100 flex justify-between items-center">
                        <span class="text-xs font-black text-gray-700 uppercase tracking-wider">Total Payable Amount:</span>
                        <span class="text-xl font-black text-blue-700" x-text="currentInvoice ? 'RM ' + currentInvoice.amount : 'RM 0.00'"></span>
                    </div>
                </div>

                <form method="POST" action="{{ route('parent.payments.pay') }}" @submit="isSubmitting = true">
                    @csrf
                    <input type="hidden" name="payment_ids" :value="currentInvoice ? currentInvoice.id : ''">
                    <input type="hidden" name="payment_method" :value="paymentMethod">
                    <input type="hidden" name="bank_name" :value="selectedBank">

                    {{-- Payment Method Tabs --}}
                    <div class="space-y-3">
                        <label class="text-xs font-black text-gray-700 block uppercase tracking-wider">Select Payment Method</label>
                        
                        <div class="grid grid-cols-2 gap-2.5">
                            {{-- FPX Online Banking --}}
                            <div @click="paymentMethod = 'FPX Online Banking'" :class="paymentMethod === 'FPX Online Banking' ? 'border-blue-600 bg-blue-50/40 ring-1 ring-blue-600' : 'border-gray-200 hover:border-gray-300 bg-white'" class="p-3 border rounded-2xl cursor-pointer transition-all flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <span class="text-xs font-bold text-gray-900 block leading-tight">FPX Banking</span>
                                    <span class="text-[10px] text-gray-400 font-medium">Maybank, CIMB, etc</span>
                                </div>
                            </div>

                            {{-- Credit / Debit Card --}}
                            <div @click="paymentMethod = 'Credit / Debit Card'" :class="paymentMethod === 'Credit / Debit Card' ? 'border-blue-600 bg-blue-50/40 ring-1 ring-blue-600' : 'border-gray-200 hover:border-gray-300 bg-white'" class="p-3 border rounded-2xl cursor-pointer transition-all flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-xl bg-purple-600 text-white flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <span class="text-xs font-bold text-gray-900 block leading-tight">Card (Visa/MC)</span>
                                    <span class="text-[10px] text-gray-400 font-medium">Credit / Debit</span>
                                </div>
                            </div>

                            {{-- DuitNow QR --}}
                            <div @click="paymentMethod = 'DuitNow QR'" :class="paymentMethod === 'DuitNow QR' ? 'border-blue-600 bg-blue-50/40 ring-1 ring-blue-600' : 'border-gray-200 hover:border-gray-300 bg-white'" class="p-3 border rounded-2xl cursor-pointer transition-all flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <span class="text-xs font-bold text-gray-900 block leading-tight">DuitNow QR</span>
                                    <span class="text-[10px] text-gray-400 font-medium">Instant QR Pay</span>
                                </div>
                            </div>

                            {{-- Bank Transfer --}}
                            <div @click="paymentMethod = 'Direct Bank Transfer'" :class="paymentMethod === 'Direct Bank Transfer' ? 'border-blue-600 bg-blue-50/40 ring-1 ring-blue-600' : 'border-gray-200 hover:border-gray-300 bg-white'" class="p-3 border rounded-2xl cursor-pointer transition-all flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <span class="text-xs font-bold text-gray-900 block leading-tight">Manual Transfer</span>
                                    <span class="text-[10px] text-gray-400 font-medium">Maybank account</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Conditional Details per Payment Method --}}
                    {{-- 1. FPX Banks --}}
                    <div x-show="paymentMethod === 'FPX Online Banking'" class="pt-3 space-y-2">
                        <label class="text-xs font-bold text-gray-600 block">Select Your Bank</label>
                        <select x-model="selectedBank" class="w-full bg-gray-50 border border-gray-200 rounded-2xl p-2.5 text-xs font-bold text-gray-900 focus:bg-white outline-none">
                            <option value="Maybank2u">Maybank2u</option>
                            <option value="CIMB Clicks">CIMB Clicks</option>
                            <option value="Public Bank (PBE)">Public Bank (PBE)</option>
                            <option value="RHB Now">RHB Now</option>
                            <option value="Bank Islam">Bank Islam</option>
                            <option value="Hong Leong Connect">Hong Leong Connect</option>
                            <option value="AmBank">AmOnline</option>
                            <option value="Affin Bank">Affin Always</option>
                            <option value="Touch n Go eWallet">Touch 'n Go eWallet</option>
                        </select>
                    </div>

                    {{-- 2. Credit Card --}}
                    <div x-show="paymentMethod === 'Credit / Debit Card'" class="pt-3 space-y-3">
                        <div>
                            <label class="text-[11px] font-bold text-gray-500 block mb-1">Card Number</label>
                            <input type="text" placeholder="4123 •••• •••• 8890" value="4123 4567 8901 8890" class="w-full bg-gray-50 border border-gray-200 rounded-2xl p-2.5 text-xs font-mono font-bold text-gray-900 focus:bg-white outline-none">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[11px] font-bold text-gray-500 block mb-1">Expiry (MM/YY)</label>
                                <input type="text" placeholder="08/28" value="12/28" class="w-full bg-gray-50 border border-gray-200 rounded-2xl p-2.5 text-xs font-mono font-bold text-gray-900 focus:bg-white outline-none">
                            </div>
                            <div>
                                <label class="text-[11px] font-bold text-gray-500 block mb-1">CVV</label>
                                <input type="password" placeholder="•••" value="889" class="w-full bg-gray-50 border border-gray-200 rounded-2xl p-2.5 text-xs font-mono font-bold text-gray-900 focus:bg-white outline-none">
                            </div>
                        </div>
                    </div>

                    {{-- 3. DuitNow QR --}}
                    <div x-show="paymentMethod === 'DuitNow QR'" class="pt-3 flex flex-col items-center justify-center p-4 bg-gray-50 rounded-2xl border border-gray-200/70 text-center space-y-2">
                        <div class="p-2.5 bg-white rounded-2xl shadow-xs border border-gray-100">
                            <svg class="w-28 h-28 text-gray-800" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M2 2h8v8H2V2zm2 2v4h4V4H4zm10-2h8v8h-8V2zm2 2v4h4V4h-4zM2 14h8v8H2v-8zm2 2v4h4v-4H4zm14-2h4v2h-4v-2zm-4 0h2v4h-2v-4zm2 4h4v4h-4v-4zm-2 2h2v2h-2v-2zm-6-6h2v2h-2v-2zm4-4h2v2h-2V8zm-2 2h2v2h-2v-2zm-2-2h2v2h-2V8z"/>
                            </svg>
                        </div>
                        <span class="text-xs font-extrabold text-gray-900">Scan with any Malaysian Banking / eWallet App</span>
                        <span class="text-[11px] text-gray-500 font-medium">Payment will be automatically detected and verified</span>
                    </div>

                    {{-- 4. Manual Bank Transfer Details --}}
                    <div x-show="paymentMethod === 'Direct Bank Transfer'" class="pt-3 p-3.5 bg-gray-50 rounded-2xl border border-gray-200 text-xs space-y-1.5 font-medium text-gray-700">
                        <p class="font-bold text-gray-900">Transfer to: <strong>Maybank 5140 8892 3410</strong></p>
                        <p class="text-[11px] text-gray-500">Account: <strong>Olympian Scholars Sdn Bhd</strong></p>
                        <p class="text-[11px] text-emerald-700 font-bold">Click Confirm below after initiating your online transfer.</p>
                    </div>

                    {{-- Submit Button --}}
                    <div class="pt-4">
                        <button type="submit" :disabled="isSubmitting" class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl text-xs font-black shadow-md shadow-blue-600/20 transition-all cursor-pointer flex items-center justify-center gap-2">
                            <span x-text="isSubmitting ? 'Processing Payment...' : 'Confirm & Complete Payment'"></span>
                            <svg x-show="!isSubmitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════
             MODAL 2: OFFICIAL TAX INVOICE & RECEIPT MODAL
             ══════════════════════════════════════════════════════════════════ --}}
        <div x-show="showReceiptModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-xs" style="display: none;" x-transition>
            <div @click.away="showReceiptModal = false" class="bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl border border-gray-100 relative space-y-6 max-h-[90vh] overflow-y-auto print:p-0 print:border-none print:shadow-none" id="printable-receipt">
                
                {{-- Receipt Top Brand Header --}}
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20 font-black text-sm">
                            OS
                        </div>
                        <div>
                            <h3 class="font-heading font-black text-base text-gray-900">Olympia Scholars</h3>
                            <p class="text-[10px] text-gray-400 font-medium">Olympia Education Sdn Bhd • Co. Reg: 202401089823</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="px-2.5 py-1 rounded-xl bg-emerald-50 text-emerald-700 text-xs font-black border border-emerald-200">
                            ✓ PAID RECEIPT
                        </span>
                    </div>
                </div>

                {{-- Receipt Metadata --}}
                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-gray-400 font-bold block text-[10px] uppercase">Receipt / Ref No</span>
                        <span class="font-mono font-bold text-gray-900" x-text="currentInvoice ? currentInvoice.reference_no : 'OS-REC-001'"></span>
                    </div>
                    <div>
                        <span class="text-gray-400 font-bold block text-[10px] uppercase">Payment Date</span>
                        <span class="font-bold text-gray-900" x-text="currentInvoice ? currentInvoice.paid_at : 'N/A'"></span>
                    </div>
                    <div>
                        <span class="text-gray-400 font-bold block text-[10px] uppercase">Billed To (Parent)</span>
                        <span class="font-bold text-gray-900" x-text="currentInvoice ? currentInvoice.parent_name : 'Parent'"></span>
                        <span class="text-gray-500 text-[10px] block" x-text="currentInvoice ? currentInvoice.parent_email : ''"></span>
                    </div>
                    <div>
                        <span class="text-gray-400 font-bold block text-[10px] uppercase">Payment Method</span>
                        <span class="font-bold text-gray-900" x-text="currentInvoice ? currentInvoice.payment_method : 'FPX Banking'"></span>
                    </div>
                </div>

                {{-- Itemized Table --}}
                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-200/70 space-y-3">
                    <div class="flex justify-between items-center text-xs pb-2 border-b border-gray-200 font-bold text-gray-400 uppercase tracking-wider text-[10px]">
                        <span>Description</span>
                        <span>Amount (MYR)</span>
                    </div>
                    <div class="flex justify-between items-start text-xs">
                        <div>
                            <span class="font-extrabold text-gray-900 block" x-text="currentInvoice ? currentInvoice.description : 'Monthly Tuition Fee'"></span>
                            <span class="text-[10px] text-gray-500 font-medium">Standard Class Tutoring, Materials & Platform Access</span>
                        </div>
                        <span class="font-black text-gray-900 shrink-0" x-text="currentInvoice ? 'RM ' + currentInvoice.amount : 'RM 0.00'"></span>
                    </div>
                    <div class="pt-3 border-t border-gray-200 flex justify-between items-center">
                        <span class="text-xs font-black text-gray-800 uppercase tracking-wider">Total Paid:</span>
                        <span class="text-lg font-black text-emerald-700" x-text="currentInvoice ? 'RM ' + currentInvoice.amount : 'RM 0.00'"></span>
                    </div>
                </div>

                {{-- Footer Note --}}
                <div class="text-center pt-2 text-[10px] text-gray-400 space-y-1">
                    <p>This is a computer-generated official receipt. No signature is required.</p>
                    <p>Thank you for choosing Olympia Scholars for your child's education journey!</p>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-3 pt-2 print:hidden">
                    <button onclick="window.print()" class="flex-1 py-3 bg-gray-900 hover:bg-black text-white rounded-2xl text-xs font-extrabold transition-all inline-flex items-center justify-center gap-2 cursor-pointer shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span>Print / Save Receipt</span>
                    </button>
                    <button @click="showReceiptModal = false" class="py-3 px-5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl text-xs font-bold transition-all cursor-pointer">
                        Close
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-dashboard-layout>
