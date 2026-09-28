<x-dashboard-layout>
    <x-slot name="title">My Fees & Invoices</x-slot>
    <x-slot name="sidebar">
        @include('student.partials.sidebar')
    </x-slot>

    <div class="space-y-6">

        {{-- ── Header ── --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="font-heading font-black text-2xl text-gray-900">My Fees & Invoices</h2>
                <p class="text-xs text-gray-500 font-medium mt-0.5">View and pay your tuition fee invoices.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm font-medium">
                ✓ {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm font-medium">
                ✗ {{ session('error') }}
            </div>
        @endif

        {{-- ── Summary Cards ── --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">Total Invoices</p>
                <p class="text-2xl font-black text-gray-900">{{ $summary['total_invoices'] }}</p>
            </div>
            <div class="bg-amber-50 rounded-2xl border border-amber-100 shadow-sm p-4">
                <p class="text-xs text-amber-600 font-semibold uppercase tracking-wide mb-1">Outstanding</p>
                <p class="text-2xl font-black text-amber-700">{{ $summary['outstanding'] }}</p>
                <p class="text-xs text-amber-600 mt-1">RM {{ number_format($summary['total_owed'], 2) }}</p>
            </div>
            <div class="bg-green-50 rounded-2xl border border-green-100 shadow-sm p-4">
                <p class="text-xs text-green-600 font-semibold uppercase tracking-wide mb-1">Paid</p>
                <p class="text-2xl font-black text-green-700">{{ $summary['paid'] }}</p>
            </div>
            <div class="bg-red-50 rounded-2xl border border-red-100 shadow-sm p-4">
                <p class="text-xs text-red-600 font-semibold uppercase tracking-wide mb-1">Overdue</p>
                <p class="text-2xl font-black text-red-700">{{ $summary['overdue'] }}</p>
            </div>
        </div>

        {{-- ── Invoices Table ── --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-bold text-gray-900 text-base">Invoice History</h3>
            </div>

            @if($invoices->isEmpty())
                <div class="text-center py-16">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="text-gray-500 font-medium">No invoices yet.</p>
                    <p class="text-gray-400 text-sm mt-1">Invoices are generated when your subject enrollment is confirmed.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Invoice</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Billing Period</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Due Date</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Total</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Outstanding</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($invoices as $invoice)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-gray-900">{{ $invoice->invoice_number }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $invoice->items->count() }} subject(s)</p>
                                </td>
                                <td class="px-4 py-4 text-gray-600">{{ $invoice->billing_period ?? '—' }}</td>
                                <td class="px-4 py-4 text-gray-600">
                                    {{ $invoice->due_date?->format('d M Y') ?? '—' }}
                                    @if($invoice->isPastDue())
                                        <span class="ml-1 text-xs text-red-500 font-semibold">Overdue</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-right font-semibold text-gray-900">RM {{ number_format($invoice->total_amount, 2) }}</td>
                                <td class="px-4 py-4 text-right font-bold {{ $invoice->outstanding_amount > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    RM {{ number_format($invoice->outstanding_amount, 2) }}
                                </td>
                                <td class="px-4 py-4 text-center">
                                    @php
                                        $badge = match($invoice->status) {
                                            'paid' => 'bg-green-100 text-green-700',
                                            'overdue' => 'bg-red-100 text-red-700',
                                            'partially_paid' => 'bg-amber-100 text-amber-700',
                                            'cancelled' => 'bg-gray-100 text-gray-500',
                                            default => 'bg-blue-100 text-blue-700',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badge }}">
                                        {{ $invoice->status_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <a href="{{ route('student.fees.show', $invoice) }}"
                                       class="inline-flex items-center gap-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">
                                        View
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>
</x-dashboard-layout>
