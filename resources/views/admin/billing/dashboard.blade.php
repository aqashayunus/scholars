<x-admin-layout>
    <x-slot name="title">Billing Dashboard</x-slot>

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="font-heading font-black text-2xl text-gray-900">Billing Dashboard</h2>
                <p class="text-xs text-gray-500 font-medium mt-0.5">Overview of invoices, payments, and outstanding amounts.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.billing.invoices.create') }}"
                   class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm px-4 py-2.5 rounded-xl transition-colors shadow-sm">
                    + New Invoice
                </a>
                <a href="{{ route('admin.billing.subject-fees') }}"
                   class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 font-semibold text-sm px-4 py-2.5 rounded-xl transition-colors">
                    Subject Fees
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm font-medium">✓ {{ session('success') }}</div>
        @endif

        {{-- ── Stats Grid ── --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-2">Total Invoices</p>
                <p class="text-3xl font-black text-gray-900">{{ number_format($stats['total_invoices']) }}</p>
            </div>
            <div class="bg-blue-50 rounded-2xl border border-blue-100 shadow-sm p-5">
                <p class="text-xs text-blue-500 font-semibold uppercase tracking-wide mb-2">Issued (Active)</p>
                <p class="text-3xl font-black text-blue-700">{{ number_format($stats['issued_invoices']) }}</p>
            </div>
            <div class="bg-green-50 rounded-2xl border border-green-100 shadow-sm p-5">
                <p class="text-xs text-green-500 font-semibold uppercase tracking-wide mb-2">Paid</p>
                <p class="text-3xl font-black text-green-700">{{ number_format($stats['paid_invoices']) }}</p>
            </div>
            <div class="bg-red-50 rounded-2xl border border-red-100 shadow-sm p-5">
                <p class="text-xs text-red-500 font-semibold uppercase tracking-wide mb-2">Overdue</p>
                <p class="text-3xl font-black text-red-700">{{ number_format($stats['overdue_invoices']) }}</p>
            </div>
            <div class="bg-amber-50 rounded-2xl border border-amber-100 shadow-sm p-5">
                <p class="text-xs text-amber-500 font-semibold uppercase tracking-wide mb-2">Partial Paid</p>
                <p class="text-3xl font-black text-amber-700">{{ number_format($stats['partial_invoices']) }}</p>
            </div>
            <div class="bg-red-50 rounded-2xl border border-red-100 shadow-sm p-5">
                <p class="text-xs text-red-500 font-semibold uppercase tracking-wide mb-2">Outstanding Amount</p>
                <p class="text-xl font-black text-red-700">RM {{ number_format($stats['outstanding_amount'], 2) }}</p>
            </div>
            <div class="bg-green-50 rounded-2xl border border-green-100 shadow-sm p-5">
                <p class="text-xs text-green-500 font-semibold uppercase tracking-wide mb-2">Total Collected</p>
                <p class="text-xl font-black text-green-700">RM {{ number_format($stats['total_collected'], 2) }}</p>
            </div>
            <div class="bg-amber-50 rounded-2xl border border-amber-100 shadow-sm p-5">
                <p class="text-xs text-amber-500 font-semibold uppercase tracking-wide mb-2">Pending Payments</p>
                <p class="text-3xl font-black text-amber-700">{{ number_format($stats['pending_payments']) }}</p>
                @if($stats['pending_payments'] > 0)
                    <a href="{{ route('admin.billing.payments', ['status' => 'pending']) }}" class="text-xs text-amber-600 font-semibold hover:underline mt-1 block">Review →</a>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- ── Recent Invoices ── --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-bold text-gray-900">Recent Invoices</h3>
                    <a href="{{ route('admin.billing.invoices') }}" class="text-xs text-indigo-600 hover:text-indigo-700 font-semibold">View All →</a>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($recentInvoices as $invoice)
                    <div class="px-6 py-3 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="font-semibold text-sm text-gray-900 truncate">{{ $invoice->invoice_number }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ $invoice->student?->user?->name ?? '—' }} · {{ $invoice->billing_period }}</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-sm font-bold text-gray-900">RM {{ number_format($invoice->total_amount, 2) }}</p>
                            @php $b = match($invoice->status) { 'paid'=>'bg-green-100 text-green-700','overdue'=>'bg-red-100 text-red-700','partially_paid'=>'bg-amber-100 text-amber-700','cancelled'=>'bg-gray-100 text-gray-500',default=>'bg-blue-100 text-blue-700' }; @endphp
                            <span class="inline-flex mt-0.5 px-2 py-0.5 rounded-full text-xs font-semibold {{ $b }}">{{ $invoice->status_label }}</span>
                        </div>
                    </div>
                    @empty
                        <div class="px-6 py-8 text-center text-sm text-gray-400">No invoices yet.</div>
                    @endforelse
                </div>
            </div>

            {{-- ── Pending Payments ── --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-bold text-gray-900">Pending Verifications</h3>
                    <a href="{{ route('admin.billing.payments', ['status' => 'pending']) }}" class="text-xs text-indigo-600 hover:text-indigo-700 font-semibold">View All →</a>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($pendingPayments as $payment)
                    <div class="px-6 py-3 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="font-semibold text-sm text-gray-900">{{ $payment->payer?->name ?? $payment->student?->user?->name ?? '—' }}</p>
                            <p class="text-xs text-gray-400">{{ $payment->created_at->format('d M Y H:i') }} · {{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? '—')) }}</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-sm font-bold text-amber-700">RM {{ number_format($payment->amount, 2) }}</p>
                            <a href="{{ route('admin.billing.payment.show', $payment) }}" class="text-xs text-indigo-600 hover:underline font-semibold mt-0.5 block">Review →</a>
                        </div>
                    </div>
                    @empty
                        <div class="px-6 py-8 text-center text-sm text-gray-400">No pending payments.</div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- Quick Links --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <a href="{{ route('admin.billing.invoices') }}" class="bg-white border border-gray-100 rounded-2xl px-5 py-4 hover:shadow-md transition-shadow text-center flex flex-col items-center">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <p class="text-sm font-bold text-gray-900">All Invoices</p>
            </a>
            <a href="{{ route('admin.billing.payments') }}" class="bg-white border border-gray-100 rounded-2xl px-5 py-4 hover:shadow-md transition-shadow text-center flex flex-col items-center">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
                <p class="text-sm font-bold text-gray-900">All Payments</p>
            </a>
            <a href="{{ route('admin.billing.subject-fees') }}" class="bg-white border border-gray-100 rounded-2xl px-5 py-4 hover:shadow-md transition-shadow text-center flex flex-col items-center">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-sm font-bold text-gray-900">Subject Fees</p>
            </a>
            <a href="{{ route('admin.billing.email-settings') }}" class="bg-white border border-gray-100 rounded-2xl px-5 py-4 hover:shadow-md transition-shadow text-center flex flex-col items-center">
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <p class="text-sm font-bold text-gray-900">Email Settings</p>
            </a>
        </div>

    </div>
</x-admin-layout>
