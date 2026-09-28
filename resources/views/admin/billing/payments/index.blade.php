<x-admin-layout>
    <x-slot name="title">Payments</x-slot>

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="font-heading font-black text-2xl text-gray-900">All Payments</h2>
                <p class="text-xs text-gray-500 font-medium mt-0.5">{{ $payments->total() }} total payment records</p>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" class="flex flex-wrap gap-3">
            <select name="status" class="text-sm border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-300 outline-none bg-white">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Verification</option>
                <option value="successful" {{ request('status') === 'successful' ? 'selected' : '' }}>Successful</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            <button type="submit" class="text-sm bg-gray-900 text-white font-semibold px-4 py-2 rounded-xl hover:bg-gray-700 transition-colors">Filter</button>
            @if(request('status')) <a href="{{ route('admin.billing.payments') }}" class="text-sm text-gray-500 hover:text-gray-700 px-3 py-2 font-medium">Clear</a> @endif
        </form>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Payer</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Method</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Invoices</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-400 uppercase">Amount</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-400 uppercase">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-400 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($payments as $payment)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-3 text-gray-500 text-xs">{{ $payment->created_at->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-sm text-gray-900">{{ $payment->payer?->name ?? $payment->student?->user?->name ?? '—' }}</p>
                                <p class="text-xs text-gray-400 capitalize">{{ $payment->payer_type }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-600 text-xs capitalize">{{ str_replace('_', ' ', $payment->payment_method ?? '—') }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $payment->invoices->pluck('invoice_number')->implode(', ') ?: '—' }}</td>
                            <td class="px-4 py-3 text-right font-bold text-gray-900">RM {{ number_format($payment->amount, 2) }}</td>
                            <td class="px-4 py-3 text-center">
                                @php $pb = match($payment->status) { 'successful'=>'bg-green-100 text-green-700','failed'=>'bg-red-100 text-red-700','cancelled'=>'bg-gray-100 text-gray-500',default=>'bg-amber-100 text-amber-700' }; @endphp
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $pb }}">{{ ucfirst($payment->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('admin.billing.payment.show', $payment) }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold">
                                    {{ $payment->status === 'pending' ? 'Review' : 'View' }}
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-6 py-12 text-center text-gray-400">No payments found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($payments->hasPages())<div class="px-6 py-4 border-t border-gray-100">{{ $payments->links() }}</div>@endif
        </div>
    </div>
</x-admin-layout>
