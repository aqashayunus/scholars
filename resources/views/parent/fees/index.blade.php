<x-dashboard-layout>
    <x-slot name="title">Children's Fees & Invoices</x-slot>
    <x-slot name="sidebar">
        @include('parent.partials.sidebar')
    </x-slot>

    <div class="space-y-6" x-data="{ selectedInvoices: [], toggleInvoice(id) { if(this.selectedInvoices.includes(id)) { this.selectedInvoices = this.selectedInvoices.filter(i => i !== id) } else { this.selectedInvoices.push(id) } } }">

        {{-- ── Header ── --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="font-heading font-black text-2xl text-gray-900">Children's Fees & Invoices</h2>
                <p class="text-xs text-gray-500 font-medium mt-0.5">View and pay invoices for your registered children.</p>
            </div>
            {{-- Multi-invoice Checkout Button --}}
            <div x-show="selectedInvoices.length > 0" x-cloak>
                <form action="{{ route('parent.fees.checkout') }}" method="POST">
                    @csrf
                    <template x-for="id in selectedInvoices">
                        <input type="hidden" name="invoice_ids[]" :value="id">
                    </template>
                    <button type="submit"
                        class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm px-5 py-2.5 rounded-xl shadow-sm transition-colors">
                        Pay Selected (<span x-text="selectedInvoices.length"></span> invoices)
                    </button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm font-medium">✓ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm font-medium">✗ {{ session('error') }}</div>
        @endif

        {{-- ── Per-child Invoice Sections ── --}}
        @forelse($children as $child)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            {{-- Child Header --}}
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center font-bold text-indigo-700 text-sm">
                        {{ strtoupper(substr($child->user->name ?? 'S', 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-bold text-gray-900 text-sm">{{ $child->user->name ?? 'Student' }}</p>
                        <p class="text-xs text-gray-400">{{ $child->level ?? '' }}</p>
                    </div>
                </div>
                @php
                    $childOutstanding = $child->invoices->whereIn('status', \App\Models\Invoice::UNPAID_STATUSES)->sum('outstanding_amount');
                @endphp
                @if($childOutstanding > 0)
                    <span class="text-sm font-bold text-red-600">RM {{ number_format($childOutstanding, 2) }} outstanding</span>
                @else
                    <span class="text-sm font-semibold text-green-600">All paid ✓</span>
                @endif
            </div>

            @if($child->invoices->isEmpty())
                <div class="px-6 py-8 text-center text-gray-400 text-sm">No invoices yet for this student.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left w-10"></th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Invoice</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Period</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Due Date</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-400 uppercase">Total</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-400 uppercase">Outstanding</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-400 uppercase">Status</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-400 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($child->invoices->sortByDesc('created_at') as $invoice)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-4 py-3 text-center">
                                    @if($invoice->requiresPayment())
                                    <input type="checkbox"
                                           @change="toggleInvoice({{ $invoice->id }})"
                                           :checked="selectedInvoices.includes({{ $invoice->id }})"
                                           class="w-4 h-4 rounded text-indigo-600 border-gray-300 cursor-pointer">
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-bold text-gray-900 text-xs">{{ $invoice->invoice_number }}</p>
                                    <p class="text-xs text-gray-400">{{ $invoice->items->count() }} subject(s)</p>
                                </td>
                                <td class="px-4 py-3 text-gray-600 text-xs">{{ $invoice->billing_period ?? '—' }}</td>
                                <td class="px-4 py-3 text-xs text-gray-600">
                                    {{ $invoice->due_date?->format('d M Y') ?? '—' }}
                                    @if($invoice->isPastDue())
                                        <span class="block text-red-500 font-semibold">Overdue</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-900 text-xs">RM {{ number_format($invoice->total_amount, 2) }}</td>
                                <td class="px-4 py-3 text-right font-bold text-xs {{ $invoice->outstanding_amount > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    RM {{ number_format($invoice->outstanding_amount, 2) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $badge = match($invoice->status) {
                                            'paid' => 'bg-green-100 text-green-700',
                                            'overdue' => 'bg-red-100 text-red-700',
                                            'partially_paid' => 'bg-amber-100 text-amber-700',
                                            'cancelled' => 'bg-gray-100 text-gray-500',
                                            default => 'bg-blue-100 text-blue-700',
                                        };
                                    @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $badge }}">
                                        {{ $invoice->status_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('parent.fees.show', $invoice) }}"
                                       class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">
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
        @empty
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-8 py-16 text-center">
                <p class="text-gray-500 font-medium">No children linked to your account yet.</p>
                <p class="text-gray-400 text-sm mt-1">Link a child to your account from your dashboard.</p>
            </div>
        @endforelse

    </div>
</x-dashboard-layout>
