<x-dashboard-layout>
    <x-slot name="title">Invoice {{ $invoice->invoice_number }}</x-slot>
    <x-slot name="sidebar">
        @if(auth()->check() && auth()->user()->isParent())
            @include('parent.partials.sidebar')
        @else
            @include('student.partials.sidebar')
        @endif
    </x-slot>

    <div class="space-y-6 max-w-4xl mx-auto">

        {{-- ── Back Link ── --}}
        <a href="{{ auth()->check() && auth()->user()->isParent() ? route('parent.fees') : route('student.fees') }}"
           class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-900 font-medium transition-colors">
            ← Back to Invoices
        </a>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm font-medium">✓ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm font-medium">✗ {{ session('error') }}</div>
        @endif

        {{-- ── Invoice Header ── --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-900 to-indigo-600 px-8 py-6 text-white">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-indigo-300 text-xs font-semibold uppercase tracking-wider mb-1">Olympia Scholars</p>
                        <h1 class="text-2xl font-black">{{ $invoice->invoice_number }}</h1>
                        <p class="text-indigo-200 text-sm mt-1">{{ $invoice->billing_period }}</p>
                    </div>
                    <div class="text-right">
                        @php
                            $badge = match($invoice->status) {
                                'paid'           => 'bg-green-400/20 text-green-300 border-green-400/30',
                                'overdue'        => 'bg-red-400/20 text-red-300 border-red-400/30',
                                'partially_paid' => 'bg-amber-400/20 text-amber-300 border-amber-400/30',
                                'cancelled'      => 'bg-gray-400/20 text-gray-300 border-gray-400/30',
                                default          => 'bg-blue-400/20 text-blue-200 border-blue-400/30',
                            };
                        @endphp
                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold border {{ $badge }}">
                            {{ $invoice->status_label }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="px-8 py-6 grid grid-cols-2 sm:grid-cols-4 gap-6 border-b border-gray-100">
                <div>
                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">Student</p>
                    <p class="text-sm font-bold text-gray-900">{{ $invoice->student->user->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">Issue Date</p>
                    <p class="text-sm font-bold text-gray-900">{{ $invoice->issued_date?->format('d M Y') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">Due Date</p>
                    <p class="text-sm font-bold {{ $invoice->isPastDue() ? 'text-red-600' : 'text-gray-900' }}">
                        {{ $invoice->due_date?->format('d M Y') ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">Billing Period</p>
                    <p class="text-sm font-bold text-gray-900">{{ $invoice->billing_period ?? '—' }}</p>
                </div>
            </div>

            {{-- Items Table --}}
            <div class="px-8 py-6">
                <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-4">Invoice Items</h3>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="pb-3 text-left text-xs font-semibold text-gray-400 uppercase">Subject / Item</th>
                            <th class="pb-3 text-right text-xs font-semibold text-gray-400 uppercase">Unit Price</th>
                            <th class="pb-3 text-right text-xs font-semibold text-gray-400 uppercase">Qty</th>
                            <th class="pb-3 text-right text-xs font-semibold text-gray-400 uppercase">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($invoice->items as $item)
                        <tr>
                            <td class="py-3 font-medium text-gray-900">{{ $item->description }}</td>
                            <td class="py-3 text-right text-gray-600">RM {{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-3 text-right text-gray-600">{{ $item->quantity }}</td>
                            <td class="py-3 text-right font-semibold text-gray-900">RM {{ number_format($item->amount, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-gray-200">
                            <td colspan="3" class="pt-4 text-right font-bold text-gray-700">Subtotal</td>
                            <td class="pt-4 text-right font-bold text-gray-900">RM {{ number_format($invoice->subtotal, 2) }}</td>
                        </tr>
                        @if($invoice->amount_paid > 0)
                        <tr>
                            <td colspan="3" class="pt-2 text-right font-semibold text-green-600">Amount Paid</td>
                            <td class="pt-2 text-right font-semibold text-green-600">- RM {{ number_format($invoice->amount_paid, 2) }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td colspan="3" class="pt-2 text-right font-black text-lg text-gray-900">Outstanding</td>
                            <td class="pt-2 text-right font-black text-lg {{ $invoice->outstanding_amount > 0 ? 'text-red-600' : 'text-green-600' }}">
                                RM {{ number_format($invoice->outstanding_amount, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Payment Actions --}}
            @if($invoice->requiresPayment())
            <div class="px-8 py-6 bg-gray-50 border-t border-gray-100">
                <h3 class="text-sm font-bold text-gray-700 mb-4">Make Payment</h3>

                {{-- Bank Transfer Section --}}
                @php
                    $bankName     = \App\Models\Setting::get('billing.bank_name');
                    $bankAccNum   = \App\Models\Setting::get('billing.bank_account_number');
                    $bankAccName  = \App\Models\Setting::get('billing.bank_account_name');
                    $bankInstr    = \App\Models\Setting::get('billing.payment_instructions');
                @endphp

                @if($bankName || $bankAccNum)
                <div class="bg-white border border-gray-200 rounded-xl p-5 mb-5">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">Bank Transfer Details</p>
                    @if($bankName)    <p class="text-sm text-gray-700 mb-1"><span class="font-semibold text-gray-500 w-36 inline-block">Bank:</span> {{ $bankName }}</p> @endif
                    @if($bankAccName) <p class="text-sm text-gray-700 mb-1"><span class="font-semibold text-gray-500 w-36 inline-block">Account Name:</span> {{ $bankAccName }}</p> @endif
                    @if($bankAccNum)  <p class="text-sm text-gray-700 mb-1"><span class="font-semibold text-gray-500 w-36 inline-block">Account Number:</span> <strong class="text-gray-900 font-mono">{{ $bankAccNum }}</strong></p> @endif
                    @if($bankInstr)   <p class="text-sm text-gray-500 mt-3 pt-3 border-t border-gray-100">{{ $bankInstr }}</p> @endif
                </div>
                @endif

                {{-- Upload Proof Form --}}
                @php $pendingPayment = $invoice->paymentTransactions->where('status','pending')->first(); @endphp

                @if($pendingPayment && !$pendingPayment->proof_file_path)
                <form action="{{ route(auth()->check() && auth()->user()->isParent() ? 'parent.fees.proof' : 'student.fees.proof', $invoice) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Upload Payment Proof</label>
                        <input type="file" name="proof_file" accept=".jpg,.jpeg,.png,.pdf"
                               class="block w-full text-sm text-gray-600 border border-gray-200 rounded-xl px-3 py-2.5 bg-white file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-100 file:text-indigo-700 file:font-semibold">
                        <p class="text-xs text-gray-400 mt-1">JPG, PNG or PDF, max 5MB.</p>
                    </div>
                    <input type="hidden" name="reference_no" value="{{ $pendingPayment->reference_no }}">
                    <button type="submit" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-colors">
                        Upload Proof
                    </button>
                </form>
                @elseif($pendingPayment && $pendingPayment->proof_file_path)
                    <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-4 text-sm text-amber-800">
                        <strong>Payment Pending Verification</strong> — Your payment proof has been submitted. Our admin team will verify it shortly.
                    </div>
                @else
                    {{-- Initiate payment first --}}
                    <form action="{{ route(auth()->check() && auth()->user()->isParent() ? 'parent.fees.pay' : 'student.fees.pay') }}" method="POST">
                        @csrf
                        <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                        <input type="hidden" name="payment_method" value="bank_transfer">
                        <button type="submit" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm px-6 py-3 rounded-xl transition-colors shadow-sm">
                            Pay Now — RM {{ number_format($invoice->outstanding_amount, 2) }}
                        </button>
                    </form>
                @endif
            </div>
            @endif
        </div>

        {{-- ── Payment History ── --}}
        @if($invoice->paymentTransactions->isNotEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-bold text-gray-900">Payment History</h3>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($invoice->paymentTransactions->sortByDesc('pivot.created_at') as $payment)
                <div class="px-6 py-4 flex items-center justify-between gap-4">
                    <div>
                        <p class="font-semibold text-sm text-gray-900">
                            {{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'Payment')) }}
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $payment->paid_at?->format('d M Y H:i') ?? $payment->created_at->format('d M Y H:i') }}
                            @if($payment->receipt_number)
                                · Receipt: <strong>{{ $payment->receipt_number }}</strong>
                            @endif
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-sm text-gray-900">RM {{ number_format($payment->pivot->amount_applied ?? $payment->amount, 2) }}</p>
                        @php
                            $pBadge = match($payment->status) {
                                'successful' => 'bg-green-100 text-green-700',
                                'failed'     => 'bg-red-100 text-red-700',
                                'cancelled'  => 'bg-gray-100 text-gray-500',
                                default      => 'bg-amber-100 text-amber-700',
                            };
                        @endphp
                        <span class="inline-flex mt-1 px-2 py-0.5 rounded-full text-xs font-semibold {{ $pBadge }}">
                            {{ ucfirst($payment->status) }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</x-dashboard-layout>
