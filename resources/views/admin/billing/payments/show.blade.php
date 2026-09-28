<x-admin-layout>
    <x-slot name="title">Payment #{{ $payment->id }}</x-slot>

    <div class="space-y-6 max-w-4xl">
        <a href="{{ route('admin.billing.payments') }}" class="text-sm text-gray-500 hover:text-gray-900 font-medium">← All Payments</a>

        @if(session('success')) <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm font-medium">✓ {{ session('success') }}</div> @endif
        @if(session('error')) <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm font-medium">✗ {{ session('error') }}</div> @endif

        {{-- Payment Summary Card --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-gray-900 to-gray-700 px-8 py-6 text-white">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-gray-400 text-xs font-semibold uppercase tracking-wider mb-1">Payment Transaction</p>
                        <h1 class="text-2xl font-black">RM {{ number_format($payment->amount, 2) }}</h1>
                        <p class="text-gray-300 text-sm mt-1">{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? '—')) }} · {{ $payment->created_at->format('d M Y H:i') }}</p>
                    </div>
                    @php $pb = match($payment->status) { 'successful'=>'bg-green-400/20 text-green-300 border-green-400/30','failed'=>'bg-red-400/20 text-red-300 border-red-400/30','cancelled'=>'bg-gray-400/20 text-gray-300 border-gray-400/30',default=>'bg-amber-400/20 text-amber-300 border-amber-400/30' }; @endphp
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold border {{ $pb }}">{{ ucfirst($payment->status) }}</span>
                </div>
            </div>

            <div class="px-8 py-6 grid grid-cols-2 sm:grid-cols-3 gap-6 border-b border-gray-100">
                <div><p class="text-xs text-gray-400 font-semibold uppercase mb-1">Payer</p><p class="text-sm font-bold text-gray-900">{{ $payment->payer?->name ?? '—' }}</p><p class="text-xs text-gray-400 capitalize">{{ $payment->payer_type }}</p></div>
                <div><p class="text-xs text-gray-400 font-semibold uppercase mb-1">Reference No.</p><p class="text-sm font-mono text-gray-900">{{ $payment->reference_no ?? '—' }}</p></div>
                <div><p class="text-xs text-gray-400 font-semibold uppercase mb-1">Receipt No.</p><p class="text-sm font-mono text-gray-900">{{ $payment->receipt_number ?? '—' }}</p></div>
                @if($payment->verified_by) <div><p class="text-xs text-gray-400 font-semibold uppercase mb-1">Verified By</p><p class="text-sm text-gray-900">{{ $payment->verifier?->name ?? '—' }}</p></div> @endif
                @if($payment->payment_proof_notes) <div class="col-span-2"><p class="text-xs text-gray-400 font-semibold uppercase mb-1">Admin Notes</p><p class="text-sm text-gray-700">{{ $payment->payment_proof_notes }}</p></div> @endif
            </div>

            {{-- Proof of payment --}}
            @if($payment->proof_file_path)
            <div class="px-8 py-4 border-b border-gray-100">
                <p class="text-xs text-gray-400 font-semibold uppercase mb-2">Payment Proof</p>
                <a href="{{ asset('storage/' . $payment->proof_file_path) }}" target="_blank"
                   class="inline-flex items-center gap-2 text-sm text-indigo-600 hover:text-indigo-800 font-semibold">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    <span>View / Download Proof</span>
                </a>
            </div>
            @endif
        </div>

        {{-- Invoices Covered --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100"><h3 class="font-bold text-gray-900">Invoices Covered</h3></div>
            <div class="divide-y divide-gray-50">
                @foreach($payment->invoices as $invoice)
                <div class="px-6 py-4 flex items-center justify-between gap-4">
                    <div>
                        <p class="font-semibold text-sm text-gray-900">{{ $invoice->invoice_number }}</p>
                        <p class="text-xs text-gray-400">{{ $invoice->student?->user?->name ?? '—' }} · {{ $invoice->billing_period }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-sm text-gray-900">RM {{ number_format($invoice->pivot->amount_applied, 2) }} applied</p>
                        <p class="text-xs text-gray-400">Total: RM {{ number_format($invoice->total_amount, 2) }}</p>
                        <a href="{{ route('admin.billing.invoices.show', $invoice) }}" class="text-xs text-indigo-600 hover:underline font-semibold mt-0.5 block">View Invoice →</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Approve / Reject Actions --}}
        @if($payment->status === 'pending')
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            {{-- Approve --}}
            <div class="bg-green-50 border border-green-200 rounded-2xl p-6">
                <h3 class="font-bold text-green-800 mb-3">Approve Payment</h3>
                <p class="text-sm text-green-700 mb-4">Approving will mark this payment as successful, generate a receipt, and update all linked invoices.</p>
                <form action="{{ route('admin.billing.payments.verify', $payment) }}" method="POST">
                    @csrf
                    <textarea name="notes" rows="2" placeholder="Optional approval notes..."
                              class="w-full text-sm border border-green-200 rounded-xl px-3 py-2 mb-3 bg-white focus:ring-2 focus:ring-green-300 outline-none resize-none"></textarea>
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold text-sm py-2.5 rounded-xl transition-colors">
                        ✓ Approve Payment
                    </button>
                </form>
            </div>

            {{-- Reject --}}
            <div class="bg-red-50 border border-red-200 rounded-2xl p-6">
                <h3 class="font-bold text-red-800 mb-3">Reject Payment</h3>
                <p class="text-sm text-red-700 mb-4">Rejecting will mark this payment as failed. The invoice will remain unpaid.</p>
                <form action="{{ route('admin.billing.payments.reject', $payment) }}" method="POST"
                      onsubmit="return confirm('Reject this payment? The student will need to resubmit.')">
                    @csrf
                    <textarea name="reason" rows="2" placeholder="Reason for rejection (required)..." required
                              class="w-full text-sm border border-red-200 rounded-xl px-3 py-2 mb-3 bg-white focus:ring-2 focus:ring-red-300 outline-none resize-none"></textarea>
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold text-sm py-2.5 rounded-xl transition-colors">
                        ✗ Reject Payment
                    </button>
                </form>
            </div>
        </div>
        @endif

    </div>
</x-admin-layout>
