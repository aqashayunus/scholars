<x-dashboard-layout>
    <x-slot name="title">My Fees</x-slot>
    <x-slot name="sidebar">
        @include('student.partials.sidebar')
    </x-slot>

    @php
        $enrolledCourses = $student->enrollments->map(fn($e) => $e->course)->filter();
        $enrolledSubjectNames = $enrolledCourses->map(fn($c) => $c->subject->display_name ?? $c->subject->name ?? $c->title)->unique()->values();
    @endphp

    <div x-data="{ 
        tab: 'all',
        searchQuery: '',
        selectedInvoice: null,
        showPayModal: false,
        paymentMethod: 'fpx',
        selectedReceipt: null,
        showReceiptModal: false
    }" class="space-y-6">

        {{-- ── TOP HEADER ── --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="font-heading font-black text-2xl text-gray-900">My Fees & Invoices</h2>
                <p class="text-xs text-gray-500 font-medium">Manage and view your tuition fees statements and official payment receipts.</p>
            </div>

            <div class="flex items-center gap-4 text-xs font-semibold">
                <div class="bg-white border border-gray-200 px-4 py-2 rounded-xl shadow-2xs">
                    <span class="text-gray-400 font-medium">Total Outstanding:</span>
                    <strong class="text-gray-900 ml-1">RM {{ number_format($totalOutstandingAmount, 2) }}</strong>
                </div>
                <div class="bg-white border border-gray-200 px-4 py-2 rounded-xl shadow-2xs">
                    <span class="text-gray-400 font-medium">Total Paid:</span>
                    <strong class="text-gray-900 ml-1">RM {{ number_format($totalPaidAmount, 2) }}</strong>
                </div>
            </div>
        </div>

        {{-- ── DETAILED TABLE CONTAINER ── --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-2xs overflow-hidden">
            
            {{-- Toolbar: Clean Tabs & Search --}}
            <div class="p-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2 overflow-x-auto text-xs font-semibold">
                    <button type="button" @click="tab = 'all'" :class="tab === 'all' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'" class="py-1.5 px-3 rounded-lg transition cursor-pointer">
                        All ({{ $invoices->count() }})
                    </button>
                    <button type="button" @click="tab = 'outstanding'" :class="tab === 'outstanding' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'" class="py-1.5 px-3 rounded-lg transition cursor-pointer">
                        Outstanding ({{ $outstandingInvoices->count() }})
                    </button>
                    <button type="button" @click="tab = 'paid'" :class="tab === 'paid' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'" class="py-1.5 px-3 rounded-lg transition cursor-pointer">
                        Paid ({{ $paidInvoices->count() }})
                    </button>
                    <button type="button" @click="tab = 'overdue'" :class="tab === 'overdue' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'" class="py-1.5 px-3 rounded-lg transition cursor-pointer">
                        Overdue ({{ $overdueInvoices->count() }})
                    </button>
                    <button type="button" @click="tab = 'history'" :class="tab === 'history' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'" class="py-1.5 px-3 rounded-lg transition cursor-pointer">
                        Transactions ({{ $payments->count() }})
                    </button>
                </div>

                <div class="relative w-full sm:w-56">
                    <input type="text" x-model="searchQuery" placeholder="Search invoice..." class="w-full bg-gray-50 border border-gray-200 text-xs rounded-lg px-3 py-1.5 text-gray-800 placeholder-gray-400 focus:bg-white focus:outline-none focus:border-gray-400 transition font-normal">
                </div>
            </div>

            {{-- ── INVOICES TABLE WITH DETAILED COLUMNS & ICON ACTIONS ── --}}
            <div x-show="tab !== 'history'" class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-[11px] font-bold text-gray-600">
                            <th class="py-3 px-4 font-semibold">Invoice No</th>
                            <th class="py-3 px-4 font-semibold">Item & Subject</th>
                            <th class="py-3 px-4 font-semibold">Level</th>
                            <th class="py-3 px-4 font-semibold">Billing Period</th>
                            <th class="py-3 px-4 font-semibold">Due Date</th>
                            <th class="py-3 px-4 font-semibold">Amount</th>
                            <th class="py-3 px-4 font-semibold">Status</th>
                            <th class="py-3 px-4 font-semibold">Receipt No</th>
                            <th class="py-3 px-4 text-center font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-gray-800 font-normal">
                        @forelse($invoices as $inv)
                            <tr x-show="(
                                (tab === 'all') ||
                                (tab === 'outstanding' && ('{{ $inv->status }}' === 'outstanding' || '{{ $inv->status }}' === 'pending_verification')) ||
                                (tab === 'paid' && '{{ $inv->status }}' === 'paid') ||
                                (tab === 'overdue' && ('{{ $inv->status }}' === 'overdue' || ('{{ $inv->status }}' === 'outstanding' && {{ $inv->is_overdue ? 'true' : 'false' }})))
                            ) && (
                                !searchQuery || 
                                '{{ strtolower($inv->invoice_number) }}'.includes(searchQuery.toLowerCase()) || 
                                '{{ strtolower($inv->title) }}'.includes(searchQuery.toLowerCase()) ||
                                '{{ strtolower($inv->status) }}'.includes(searchQuery.toLowerCase())
                            )" class="hover:bg-gray-50 transition-colors">
                                
                                {{-- 1. Invoice No --}}
                                <td class="py-3.5 px-4 font-mono font-medium text-gray-900 whitespace-nowrap">
                                    {{ $inv->invoice_number }}
                                </td>

                                {{-- 2. Item & Subject --}}
                                <td class="py-3.5 px-4 font-medium text-gray-900">
                                    {{ $inv->title }}
                                </td>

                                {{-- 3. Level --}}
                                <td class="py-3.5 px-4 text-gray-600 whitespace-nowrap">
                                    {{ $student->level ?? 'Academic' }}
                                </td>

                                {{-- 4. Billing Period --}}
                                <td class="py-3.5 px-4 text-gray-600 whitespace-nowrap">
                                    {{ $inv->created_at ? $inv->created_at->format('M Y') : $inv->due_date->format('M Y') }}
                                </td>

                                {{-- 5. Due Date --}}
                                <td class="py-3.5 px-4 text-gray-600 whitespace-nowrap">
                                    {{ $inv->due_date->format('d M Y') }}
                                </td>

                                {{-- 6. Amount --}}
                                <td class="py-3.5 px-4 font-semibold text-gray-900 whitespace-nowrap">
                                    RM {{ number_format($inv->total_amount, 2) }}
                                </td>

                                {{-- 7. Status --}}
                                <td class="py-3.5 px-4 whitespace-nowrap text-gray-700">
                                    @if($inv->status === 'paid')
                                        Paid
                                    @elseif($inv->status === 'pending_verification')
                                        Pending Verification
                                    @elseif($inv->status === 'overdue' || $inv->is_overdue)
                                        Overdue
                                    @else
                                        Outstanding
                                    @endif
                                </td>

                                {{-- 8. Receipt No --}}
                                <td class="py-3.5 px-4 font-mono text-gray-500 whitespace-nowrap">
                                    {{ $inv->latestPayment?->receipt_number ?? '—' }}
                                </td>

                                {{-- 9. Action with Clean Icons --}}
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    @if($inv->status === 'paid')
                                        <button type="button" 
                                                @click="selectedReceipt = {{ json_encode([
                                                    'invoice_number' => $inv->invoice_number,
                                                    'title'          => $inv->title,
                                                    'level'          => $student->level ?? 'Academic',
                                                    'subjects'       => $enrolledSubjectNames->implode(', '),
                                                    'amount'         => $inv->total_amount,
                                                    'receipt_number' => $inv->latestPayment?->receipt_number ?? ('RCP-' . $inv->id),
                                                    'date'           => $inv->latestPayment?->paid_at ? $inv->latestPayment->paid_at->format('d M Y') : $inv->updated_at->format('d M Y'),
                                                    'method'         => strtoupper($inv->latestPayment?->payment_method ?? 'FPX Online'),
                                                    'student_name'   => auth()->user()->name,
                                                ]) }}; showReceiptModal = true" 
                                                title="View Official Receipt"
                                                class="p-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition cursor-pointer inline-flex items-center justify-center">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </button>
                                    @elseif($inv->status === 'pending_verification')
                                        <span title="Payment slip under review" class="p-2 text-gray-400 inline-flex items-center justify-center cursor-help">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </span>
                                    @else
                                        <button type="button" 
                                                @click="selectedInvoice = {{ json_encode($inv) }}; showPayModal = true" 
                                                title="Pay Now"
                                                class="p-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition cursor-pointer inline-flex items-center justify-center shadow-xs">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="p-8 text-center text-xs text-gray-400">
                                    No invoices found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ── TRANSACTION HISTORY TABLE ── --}}
            <div x-show="tab === 'history'" x-cloak class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-[11px] font-bold text-gray-600">
                            <th class="py-3 px-4 font-semibold">Reference No</th>
                            <th class="py-3 px-4 font-semibold">Description</th>
                            <th class="py-3 px-4 font-semibold">Payer</th>
                            <th class="py-3 px-4 font-semibold">Method</th>
                            <th class="py-3 px-4 font-semibold">Amount</th>
                            <th class="py-3 px-4 font-semibold">Status</th>
                            <th class="py-3 px-4 font-semibold">Receipt No</th>
                            <th class="py-3 px-4 text-right font-semibold">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-gray-800 font-normal">
                        @forelse($payments as $p)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="py-3.5 px-4 font-mono font-medium text-gray-900">
                                    {{ $p->reference_no }}
                                </td>
                                <td class="py-3.5 px-4 text-gray-900 font-medium">{{ $p->description }}</td>
                                <td class="py-3.5 px-4 text-gray-600">
                                    {{ ucfirst($p->payer_type) }}: {{ $p->payer->name ?? 'Student' }}
                                </td>
                                <td class="py-3.5 px-4 uppercase text-gray-600 text-[11px]">{{ $p->payment_method }}</td>
                                <td class="py-3.5 px-4 font-semibold text-gray-900">RM {{ number_format($p->amount, 2) }}</td>
                                <td class="py-3.5 px-4 text-gray-700 capitalize">
                                    {{ $p->status }}
                                </td>
                                <td class="py-3.5 px-4 font-mono text-gray-500">
                                    {{ $p->receipt_number ?? '—' }}
                                </td>
                                <td class="py-3.5 px-4 text-right text-gray-500">{{ $p->paid_at ? $p->paid_at->format('d M Y, g:i A') : $p->created_at->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-8 text-center text-xs text-gray-400">
                                    No transactions recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

        {{-- ── PAY NOW MODAL ── --}}
        <div x-show="showPayModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl border border-gray-200 w-full max-w-lg p-6 space-y-4"
                 @click.away="showPayModal = false">
                
                <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                    <h3 class="font-bold text-base text-gray-900" x-text="selectedInvoice?.title"></h3>
                    <button @click="showPayModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg cursor-pointer">✕</button>
                </div>

                {{-- Invoice Summary Box --}}
                <div class="p-3 bg-gray-50 rounded-xl flex items-center justify-between border border-gray-200">
                    <div>
                        <p class="text-[11px] text-gray-500">Invoice Number</p>
                        <p class="font-mono text-xs font-bold text-gray-800" x-text="selectedInvoice?.invoice_number"></p>
                    </div>
                    <div class="text-right">
                        <p class="text-[11px] text-gray-500">Total Amount</p>
                        <p class="text-lg font-bold text-gray-900">RM <span x-text="Number(selectedInvoice?.total_amount).toFixed(2)"></span></p>
                    </div>
                </div>

                <form :action="'/student/fees/' + selectedInvoice?.id + '/pay'" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-2">Payment Method</label>
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <label :class="paymentMethod === 'fpx' ? 'border-gray-900 bg-gray-50' : 'border-gray-200 bg-white'" class="border rounded-xl p-3 flex items-center gap-2 cursor-pointer transition">
                                <input type="radio" name="payment_method" value="fpx" x-model="paymentMethod" class="text-gray-900 focus:ring-0">
                                <div>
                                    <p class="font-bold text-gray-900">Online Banking</p>
                                    <p class="text-[10px] text-gray-500">FPX Payment</p>
                                </div>
                            </label>

                            <label :class="paymentMethod === 'bank_transfer' ? 'border-gray-900 bg-gray-50' : 'border-gray-200 bg-white'" class="border rounded-xl p-3 flex items-center gap-2 cursor-pointer transition">
                                <input type="radio" name="payment_method" value="bank_transfer" x-model="paymentMethod" class="text-gray-900 focus:ring-0">
                                <div>
                                    <p class="font-bold text-gray-900">Bank Transfer</p>
                                    <p class="text-[10px] text-gray-500">Upload Transfer Slip</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Bank Transfer Details Box --}}
                    <div x-show="paymentMethod === 'bank_transfer'" class="p-3 bg-gray-50 border border-gray-200 rounded-xl space-y-2 text-xs">
                        <p class="font-bold text-gray-900">Company Bank Details:</p>
                        <div class="space-y-1 text-gray-700 text-[11px]">
                            <p><strong>Bank:</strong> {{ $bankDetails['bank_name'] }}</p>
                            <p><strong>Account Name:</strong> {{ $bankDetails['account_name'] }}</p>
                            <p><strong>Account No:</strong> <span class="font-mono font-bold text-gray-900">{{ $bankDetails['account_number'] }}</span></p>
                            <p class="text-gray-500 mt-1"><em>Note: {{ $bankDetails['instructions'] }}</em></p>
                        </div>

                        <div class="pt-1">
                            <label class="block text-xs font-bold text-gray-800 mb-1">Upload Slip / Receipt</label>
                            <input type="file" name="proof_file" accept=".jpg,.jpeg,.png,.pdf" class="w-full text-xs text-gray-600 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-gray-800 file:text-white hover:file:bg-gray-900 cursor-pointer">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-200">
                        <button type="button" @click="showPayModal = false" class="py-2 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium text-xs cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" class="py-2 px-5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold text-xs transition cursor-pointer">
                            <span x-text="paymentMethod === 'fpx' ? 'Pay with FPX' : 'Submit Proof'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── OFFICIAL RECEIPT VIEW MODAL ── --}}
        <div x-show="showReceiptModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl border border-gray-200 w-full max-w-md p-6 space-y-4"
                 @click.away="showReceiptModal = false">
                
                <div class="border border-gray-200 p-5 rounded-xl space-y-3 text-left text-xs">
                    <div class="text-center border-b border-gray-200 pb-3 space-y-0.5">
                        <h4 class="font-bold text-sm uppercase tracking-wider text-gray-900">Olympia Scholars Academy</h4>
                        <p class="text-xs text-gray-500 font-medium">Official Payment Receipt</p>
                    </div>

                    <div class="space-y-2 pt-1">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Receipt No:</span>
                            <span class="font-mono font-bold text-gray-900" x-text="selectedReceipt?.receipt_number"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Invoice Ref:</span>
                            <span class="font-mono font-bold text-gray-900" x-text="selectedReceipt?.invoice_number"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Student Name:</span>
                            <strong class="text-gray-900" x-text="selectedReceipt?.student_name"></strong>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Item:</span>
                            <span class="text-gray-800" x-text="selectedReceipt?.title"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Payment Date:</span>
                            <span class="text-gray-800" x-text="selectedReceipt?.date"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Payment Method:</span>
                            <span class="text-gray-800" x-text="selectedReceipt?.method"></span>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-gray-200 text-sm font-bold">
                            <span>Total Paid:</span>
                            <span class="text-gray-900">RM <span x-text="Number(selectedReceipt?.amount).toFixed(2)"></span></span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2">
                    <button type="button" @click="showReceiptModal = false" class="py-1.5 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium text-xs cursor-pointer">
                        Close
                    </button>
                    <button type="button" @click="window.print()" class="py-1.5 px-4 bg-gray-900 hover:bg-black text-white rounded-lg font-bold text-xs cursor-pointer inline-flex items-center gap-1.5">
                        <span>Print Receipt</span>
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-dashboard-layout>
