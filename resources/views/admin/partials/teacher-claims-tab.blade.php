{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- TAB: TEACHER CLASS FEE CLAIMS (#teacher-claims)                   --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
<div id="tab-teacher-claims" class="admin-tab-content hidden space-y-6">
    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
        {{-- Card Header & Filter Bar --}}
        <div class="p-5 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-lg text-gray-900">Teacher Class Claims</h2>
                <p class="text-xs text-gray-500 font-normal">Review and process class fee claim submissions from instructors</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <input id="teacherClaimsSearch"
                       oninput="handleTeacherClaimsSearch(this.value)"
                       type="text"
                       placeholder="Search claim, teacher, class..."
                       class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 w-52 font-normal">

                <select id="teacherClaimsStatusFilter"
                        onchange="filterTeacherClaimsStatus(this.value)"
                        class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 text-xs text-gray-700 outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="paid">Paid</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto admin-scrollbar">
            <table class="w-full text-left text-xs" id="teacherClaimsTable">
                <thead>
                    <tr class="border-b border-gray-100 text-gray-400 font-medium uppercase text-[10px] bg-gray-50/50">
                        <th class="py-3 px-4">Claim Ref</th>
                        <th class="py-3 px-4">Teacher Name</th>
                        <th class="py-3 px-4">Class &amp; Subject</th>
                        <th class="py-3 px-4">Class Date &amp; Duration</th>
                        <th class="py-3 px-4">Claim Amount</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Bank Details</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($allTeacherClaims as $claim)
                        @php
                            $claimTeacherUser = $claim->teacher?->user;
                            $teacherName = $claimTeacherUser?->name ?? ('Teacher #' . $claim->teacher_id);
                            $teacherEmail = $claimTeacherUser?->email ?? '—';
                            $bankName = $claim->bank_name ?: ($claim->teacher?->bank_name ?? '—');
                            $bankAcc = $claim->bank_account_number ?: ($claim->teacher?->bank_account_number ?? '—');
                            $bankHolder = $claim->bank_account_holder ?: ($claim->teacher?->bank_account_holder ?? '');
                            $classTitle = $claim->class_title ?: ($claim->liveClass?->title ?? 'Class Session');
                            $subjectName = $claim->subject_name ?: ($claim->course?->subject?->name ?? 'Subject');

                            $claimPayload = [
                                'id' => $claim->id,
                                'claim_number' => $claim->claim_number,
                                'teacher_name' => $teacherName,
                                'teacher_email' => $teacherEmail,
                                'class_title' => $classTitle,
                                'subject_name' => $subjectName,
                                'class_date' => $claim->class_date ? \Carbon\Carbon::parse($claim->class_date)->format('d M Y') : '—',
                                'duration_minutes' => $claim->duration_minutes ?: 60,
                                'rate_per_class' => number_format($claim->rate_per_class, 2),
                                'amount' => number_format($claim->amount, 2),
                                'bank_name' => $bankName,
                                'bank_account_number' => $bankAcc,
                                'bank_account_holder' => $bankHolder ?: '—',
                                'teacher_notes' => $claim->teacher_notes ?: '—',
                                'proof_attachment_path' => $claim->proof_attachment_path ? asset('storage/' . $claim->proof_attachment_path) : null,
                                'status' => $claim->status,
                                'admin_notes' => $claim->admin_notes ?: '—',
                                'approved_at' => $claim->approved_at ? \Carbon\Carbon::parse($claim->approved_at)->format('d M Y, h:i A') : null,
                                'paid_at' => $claim->paid_at ? \Carbon\Carbon::parse($claim->paid_at)->format('d M Y, h:i A') : null,
                                'payment_method' => $claim->payment_method ?: 'Bank Transfer',
                                'payment_reference' => $claim->payment_reference ?: '—',
                            ];
                        @endphp
                        <tr class="hover:bg-gray-50/60 transition target-row teacher-claim-row"
                            data-status="{{ strtolower($claim->status) }}"
                            data-claim-id="{{ $claim->id }}"
                            data-claim-info="{{ json_encode($claimPayload) }}">
                            
                            {{-- 1. Claim Ref --}}
                            <td class="py-3.5 px-4 font-mono font-medium text-gray-900 whitespace-nowrap">
                                {{ $claim->claim_number }}
                            </td>

                            {{-- 2. Teacher Name & Email --}}
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-gray-900">{{ $teacherName }}</div>
                                <div class="text-[11px] text-gray-400 font-normal">{{ $teacherEmail }}</div>
                            </td>

                            {{-- 3. Class & Subject --}}
                            <td class="py-3.5 px-4">
                                <div class="font-normal text-gray-900">{{ $classTitle }}</div>
                                <div class="text-[11px] text-blue-600 font-normal">{{ $subjectName }}</div>
                            </td>

                            {{-- 4. Class Date & Duration --}}
                            <td class="py-3.5 px-4 text-gray-600 font-normal whitespace-nowrap">
                                <div>{{ $claim->class_date ? \Carbon\Carbon::parse($claim->class_date)->format('d M Y') : '—' }}</div>
                                <div class="text-[11px] text-gray-400 font-normal">{{ $claim->duration_minutes ?: 60 }} mins</div>
                            </td>

                            {{-- 5. Claim Amount --}}
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-semibold text-emerald-700">RM {{ number_format($claim->amount, 2) }}</div>
                                <div class="text-[10px] text-gray-400 font-normal">Rate: RM {{ number_format($claim->rate_per_class, 2) }}</div>
                            </td>

                            {{-- 6. Status --}}
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($claim->status === 'pending')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium uppercase bg-amber-50 text-amber-800">
                                        Pending Review
                                    </span>
                                @elseif($claim->status === 'approved')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium uppercase bg-emerald-50 text-emerald-700">
                                        Approved
                                    </span>
                                    @if($claim->approved_at)
                                        <div class="text-[10px] text-gray-400 font-normal mt-0.5">{{ \Carbon\Carbon::parse($claim->approved_at)->format('d M Y') }}</div>
                                    @endif
                                @elseif($claim->status === 'paid')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium uppercase bg-blue-50 text-blue-700">
                                        Paid
                                    </span>
                                    @if($claim->payment_reference)
                                        <div class="text-[10px] text-gray-400 font-mono mt-0.5">{{ $claim->payment_reference }}</div>
                                    @endif
                                @elseif($claim->status === 'rejected')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium uppercase bg-rose-50 text-rose-700">
                                        Rejected
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium uppercase bg-gray-100 text-gray-600">
                                        {{ $claim->status }}
                                    </span>
                                @endif
                            </td>

                            {{-- 7. Bank Details --}}
                            <td class="py-3.5 px-4">
                                @if($bankAcc !== '—')
                                    <div class="text-xs text-gray-800 font-normal">{{ $bankName }} • {{ $bankAcc }}</div>
                                    @if($bankHolder)
                                        <div class="text-[10px] text-gray-400 font-normal uppercase">{{ $bankHolder }}</div>
                                    @endif
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>

                            {{-- 8. Actions --}}
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button"
                                            onclick="openClaimDetailModalFromRow(this)"
                                            class="px-2.5 py-1 border border-gray-300 hover:bg-gray-100 text-gray-700 rounded text-[11px] font-normal transition cursor-pointer">
                                        Details
                                    </button>

                                    @if($claim->status === 'pending')
                                        <form action="{{ route('admin.teacher-claims.approve', $claim->id) }}" method="POST" onsubmit="return confirm('Approve claim {{ addslashes($claim->claim_number) }} (RM {{ number_format($claim->amount, 2) }}) for {{ addslashes($teacherName) }}?');" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[11px] font-medium transition cursor-pointer">
                                                Approve
                                            </button>
                                        </form>

                                        <button type="button"
                                                onclick="openRejectClaimModal({{ $claim->id }}, '{{ addslashes($claim->claim_number) }}', '{{ addslashes($teacherName) }}')"
                                                class="px-2.5 py-1 bg-gray-100 hover:bg-rose-50 text-rose-600 rounded text-[11px] font-medium transition cursor-pointer">
                                            Reject
                                        </button>
                                    @elseif($claim->status === 'approved')
                                        <button type="button"
                                                onclick="openMarkPaidClaimModal({{ $claim->id }}, '{{ addslashes($claim->claim_number) }}', '{{ number_format($claim->amount, 2) }}', '{{ addslashes($teacherName) }}', '{{ addslashes($bankName . ' • ' . $bankAcc) }}', '{{ addslashes($bankHolder) }}')"
                                                class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-[11px] font-medium transition cursor-pointer">
                                            Mark Paid
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-gray-400 font-normal">
                                No teacher class claims found in database.
                            </td>
                        </tr>
                    @endforelse

                    <tr id="teacherClaimsNoResultsRow" class="hidden">
                        <td colspan="8" class="text-center py-8 text-gray-400 font-normal">
                            No teacher class claims found matching your filter criteria.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Teacher Claims Pagination & Count Footer --}}
        <div id="teacherClaimsPaginationContainer" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 py-3.5 border-t border-gray-100 bg-gray-50/30 text-xs">
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative inline-flex items-center">
                    <select id="teacherClaimsPerPageSelect" onchange="setTeacherClaimsPerPage(this.value)" class="appearance-none !bg-none bg-white hover:bg-gray-50 border border-gray-200 rounded-lg pl-2.5 pr-6 py-1 text-xs font-normal text-gray-700 outline-none focus:outline-none focus:border-slate-400 transition cursor-pointer">
                        <option value="10" selected>10 rows</option>
                        <option value="25">25 rows</option>
                        <option value="50">50 rows</option>
                        <option value="all">All rows</option>
                    </select>
                    <svg class="w-3.5 h-3.5 text-gray-400 absolute right-2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>

                <div class="text-gray-500 font-normal">
                    Showing <span id="teacherClaimsShowingStart" class="font-semibold text-gray-800">{{ $allTeacherClaims->isEmpty() ? 0 : 1 }}</span> to <span id="teacherClaimsShowingEnd" class="font-semibold text-gray-800">{{ min(10, $allTeacherClaims->count()) }}</span> of <span id="teacherClaimsTotalFiltered" class="font-semibold text-gray-800">{{ $allTeacherClaims->count() }}</span> claims
                    <span id="teacherClaimsTotalUnfiltered" class="text-gray-400 text-[11px] hidden">(filtered from <span id="teacherClaimsGrandTotal">{{ $allTeacherClaims->count() }}</span> total)</span>
                </div>
            </div>

            <div class="flex items-center gap-1.5" id="teacherClaimsPaginationButtons">
                {{-- Dynamically generated pagination buttons --}}
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- CLAIM DETAIL MODAL                                                 --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
<div id="claimDetailModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto admin-scrollbar border border-gray-200">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <div>
                <h3 class="font-semibold text-base text-gray-900" id="claimDetailRef">Claim Details</h3>
                <p class="text-xs text-gray-500 font-normal">Class fee submission record</p>
            </div>
            <button type="button" onclick="closeClaimDetailModal()" class="text-gray-400 hover:text-gray-600 text-xl font-normal cursor-pointer">&times;</button>
        </div>

        <div class="space-y-4 text-xs">
            {{-- Teacher and Class Details --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3.5 bg-gray-50/70 rounded-xl border border-gray-200/80">
                <div>
                    <div class="text-[11px] text-gray-500 font-medium uppercase tracking-wider">Teacher</div>
                    <div class="font-medium text-gray-900 text-sm mt-0.5" id="claimDetailTeacherName">—</div>
                    <div class="text-gray-500 text-[11px]" id="claimDetailTeacherEmail">—</div>
                </div>
                <div>
                    <div class="text-[11px] text-gray-500 font-medium uppercase tracking-wider">Claim Status</div>
                    <div class="font-medium text-sm mt-0.5" id="claimDetailStatus">—</div>
                    <div class="text-gray-400 text-[11px]" id="claimDetailStatusSub">—</div>
                </div>
            </div>

            {{-- Class Information --}}
            <div class="space-y-2">
                <div class="text-[11px] text-gray-500 font-medium uppercase tracking-wider">Class Information</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-gray-700">
                    <div class="p-2.5 rounded-lg border border-gray-100 bg-white">
                        <span class="text-gray-400 text-[11px] block">Class Title</span>
                        <span class="font-medium text-gray-900" id="claimDetailClassTitle">—</span>
                    </div>
                    <div class="p-2.5 rounded-lg border border-gray-100 bg-white">
                        <span class="text-gray-400 text-[11px] block">Subject</span>
                        <span class="font-medium text-gray-900" id="claimDetailSubjectName">—</span>
                    </div>
                    <div class="p-2.5 rounded-lg border border-gray-100 bg-white">
                        <span class="text-gray-400 text-[11px] block">Class Date</span>
                        <span class="font-medium text-gray-900" id="claimDetailClassDate">—</span>
                    </div>
                    <div class="p-2.5 rounded-lg border border-gray-100 bg-white">
                        <span class="text-gray-400 text-[11px] block">Duration</span>
                        <span class="font-medium text-gray-900" id="claimDetailDuration">—</span>
                    </div>
                </div>
            </div>

            {{-- Payment and Bank Information --}}
            <div class="space-y-2">
                <div class="text-[11px] text-gray-500 font-medium uppercase tracking-wider">Payment &amp; Bank Details</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-gray-700">
                    <div class="p-2.5 rounded-lg border border-gray-100 bg-white">
                        <span class="text-gray-400 text-[11px] block">Claim Amount</span>
                        <span class="font-semibold text-gray-900 text-sm" id="claimDetailAmount">RM 0.00</span>
                        <span class="text-gray-400 text-[11px] block" id="claimDetailRate">Rate: RM 0.00</span>
                    </div>
                    <div class="p-2.5 rounded-lg border border-gray-100 bg-white">
                        <span class="text-gray-400 text-[11px] block">Bank Account</span>
                        <span class="font-medium text-gray-900" id="claimDetailBankName">—</span>
                        <span class="text-gray-600 block text-[11px]" id="claimDetailBankAcc">—</span>
                        <span class="text-gray-400 text-[10px] uppercase block" id="claimDetailBankHolder">—</span>
                    </div>
                </div>
            </div>

            {{-- Teacher Notes & Proof --}}
            <div class="space-y-2">
                <div class="text-[11px] text-gray-500 font-medium uppercase tracking-wider">Teacher Notes &amp; Attachment</div>
                <div class="p-3 rounded-lg border border-gray-100 bg-white space-y-2">
                    <div>
                        <span class="text-gray-400 text-[11px] block">Notes</span>
                        <p class="text-gray-700 whitespace-pre-wrap mt-0.5" id="claimDetailNotes">—</p>
                    </div>
                    <div id="claimDetailProofRow" class="hidden pt-2 border-t border-gray-100">
                        <span class="text-gray-400 text-[11px] block mb-1">Proof Attachment</span>
                        <a id="claimDetailProofLink" href="#" target="_blank" class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-800 underline font-medium text-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                            <span>Open Attachment in New Tab</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Audit & Notes (If rejected or paid) --}}
            <div id="claimDetailAuditBox" class="p-3 rounded-lg border border-gray-200 bg-gray-50/50 space-y-1 hidden">
                <div class="text-[11px] text-gray-500 font-medium uppercase tracking-wider" id="claimDetailAuditTitle">Record Info</div>
                <div class="text-gray-700 text-xs" id="claimDetailAuditText">—</div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
            <button type="button" onclick="closeClaimDetailModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 active:scale-95 text-gray-700 font-medium text-xs rounded-xl transition cursor-pointer">
                Close
            </button>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- REJECT CLAIM MODAL                                                 --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
<div id="rejectClaimModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto admin-scrollbar border border-gray-200">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <div>
                <h3 class="font-semibold text-base text-gray-900">Reject Class Claim</h3>
                <p class="text-xs text-gray-500 font-normal" id="rejectClaimModalSubtitle">Provide reason for rejection</p>
            </div>
            <button type="button" onclick="closeRejectClaimModal()" class="text-gray-400 hover:text-gray-600 text-xl font-normal cursor-pointer">&times;</button>
        </div>

        <form id="rejectClaimForm" method="POST" action="" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Reason for Rejection <span class="text-rose-500">*</span></label>
                <textarea name="admin_notes" id="reject_admin_notes" rows="3" required
                          placeholder="e.g. Class session was not conducted or duplicate claim submitted..."
                          class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs text-gray-900 outline-none focus:ring-2 focus:ring-rose-500 focus:bg-white font-normal"></textarea>
                <p class="text-[11px] text-gray-400 mt-1">This explanation will be sent to the instructor via their notification portal.</p>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                <button type="button" onclick="closeRejectClaimModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 active:scale-95 text-gray-700 font-medium text-xs rounded-xl transition cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 active:scale-95 text-white font-medium text-xs rounded-xl shadow-xs transition cursor-pointer">
                    Confirm Rejection
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- MARK AS PAID MODAL                                                 --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
<div id="markPaidClaimModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto admin-scrollbar border border-gray-200">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <div>
                <h3 class="font-semibold text-base text-gray-900">Record Payout</h3>
                <p class="text-xs text-gray-500 font-normal" id="markPaidModalSubtitle">Confirm payout transaction</p>
            </div>
            <button type="button" onclick="closeMarkPaidClaimModal()" class="text-gray-400 hover:text-gray-600 text-xl font-normal cursor-pointer">&times;</button>
        </div>

        <form id="markPaidClaimForm" method="POST" action="" class="space-y-4">
            @csrf
            {{-- Summary Box --}}
            <div class="p-3 bg-gray-50 rounded-xl border border-gray-200/80 space-y-1 text-xs">
                <div class="flex justify-between items-center">
                    <span class="text-gray-500">Teacher:</span>
                    <span class="font-medium text-gray-900" id="markPaidTeacherName">—</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-500">Payout Amount:</span>
                    <span class="font-semibold text-gray-900 text-sm" id="markPaidAmount">RM 0.00</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-500">Bank Account:</span>
                    <span class="text-gray-800 text-right" id="markPaidBankInfo">—</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-500">Account Holder:</span>
                    <span class="text-gray-800 uppercase text-right" id="markPaidBankHolder">—</span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Payment Method</label>
                <select name="payment_method" id="mark_paid_payment_method" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs text-gray-900 outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white font-normal">
                    <option value="Bank Transfer">Bank Transfer (IBG / DuitNow)</option>
                    <option value="DuitNow QR">DuitNow QR</option>
                    <option value="Online Banking">Online Banking</option>
                    <option value="Cash">Cash</option>
                    <option value="Cheque">Cheque</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Transaction Reference</label>
                <input type="text" name="payment_reference" id="mark_paid_payment_reference"
                       placeholder="e.g. MBB-98213821 or auto-generated if blank"
                       class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs text-gray-900 outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white font-normal">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Payment Date</label>
                <input type="date" name="paid_at" id="mark_paid_paid_at" value="{{ date('Y-m-d') }}"
                       class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs text-gray-900 outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white font-normal">
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                <button type="button" onclick="closeMarkPaidClaimModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 active:scale-95 text-gray-700 font-medium text-xs rounded-xl transition cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-medium text-xs rounded-xl shadow-xs transition cursor-pointer">
                    Confirm Payout Recorded
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- JAVASCRIPT LOGIC FOR TEACHER CLAIMS                                --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
<script>
let teacherClaimsPerPage = 10;
let teacherClaimsCurrentPage = 1;
let teacherClaimsSearchQuery = '';
let teacherClaimsStatus = '';

function setTeacherClaimsPerPage(val) {
    teacherClaimsPerPage = val === 'all' ? 999999 : parseInt(val, 10);
    teacherClaimsCurrentPage = 1;
    renderTeacherClaimsPagination();
}

function handleTeacherClaimsSearch(val) {
    teacherClaimsSearchQuery = (val || '').toLowerCase().trim();
    teacherClaimsCurrentPage = 1;
    renderTeacherClaimsPagination();
}

function filterTeacherClaimsStatus(val) {
    teacherClaimsStatus = (val || '').toLowerCase().trim();
    teacherClaimsCurrentPage = 1;
    renderTeacherClaimsPagination();
}

function setTeacherClaimsPage(pageNum) {
    teacherClaimsCurrentPage = pageNum;
    renderTeacherClaimsPagination();
    const table = document.getElementById('teacherClaimsTable');
    if (table) {
        const rect = table.getBoundingClientRect();
        if (rect.top < 0) {
            table.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
}

function renderTeacherClaimsPagination() {
    const table = document.getElementById('teacherClaimsTable');
    if (!table) return;

    const rows = Array.from(table.querySelectorAll('tbody tr.teacher-claim-row'));
    const grandTotal = rows.length;

    const matchedRows = [];
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        const matchesQuery = !teacherClaimsSearchQuery || text.includes(teacherClaimsSearchQuery);
        const rowStatus = (row.getAttribute('data-status') || '').toLowerCase();
        const matchesStatus = !teacherClaimsStatus || rowStatus === teacherClaimsStatus;

        if (matchesQuery && matchesStatus) {
            matchedRows.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    const totalMatched = matchedRows.length;
    const totalPages = Math.max(1, Math.ceil(totalMatched / teacherClaimsPerPage));

    if (teacherClaimsCurrentPage > totalPages) teacherClaimsCurrentPage = totalPages;
    if (teacherClaimsCurrentPage < 1) teacherClaimsCurrentPage = 1;

    const startIndex = (teacherClaimsCurrentPage - 1) * teacherClaimsPerPage;
    const endIndex = Math.min(startIndex + teacherClaimsPerPage, totalMatched);

    matchedRows.forEach((row, idx) => {
        if (idx >= startIndex && idx < endIndex) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });

    const noResultsRow = document.getElementById('teacherClaimsNoResultsRow');
    if (noResultsRow) {
        noResultsRow.classList.toggle('hidden', totalMatched > 0 || grandTotal === 0);
    }

    const showingStartEl = document.getElementById('teacherClaimsShowingStart');
    const showingEndEl = document.getElementById('teacherClaimsShowingEnd');
    const totalFilteredEl = document.getElementById('teacherClaimsTotalFiltered');
    const unfilteredWrapper = document.getElementById('teacherClaimsTotalUnfiltered');
    const grandTotalEl = document.getElementById('teacherClaimsGrandTotal');

    if (showingStartEl) showingStartEl.innerText = totalMatched === 0 ? 0 : (startIndex + 1);
    if (showingEndEl) showingEndEl.innerText = endIndex;
    if (totalFilteredEl) totalFilteredEl.innerText = totalMatched;
    if (grandTotalEl) grandTotalEl.innerText = grandTotal;
    if (unfilteredWrapper) {
        unfilteredWrapper.classList.toggle('hidden', totalMatched === grandTotal);
    }

    renderTeacherClaimsPaginationControls(totalPages, totalMatched);
}

function renderTeacherClaimsPaginationControls(totalPages, totalMatched) {
    const container = document.getElementById('teacherClaimsPaginationButtons');
    if (!container) return;

    if (totalMatched === 0 || totalPages <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = '';

    // Previous Button
    const prevDisabled = teacherClaimsCurrentPage === 1;
    html += `
        <button type="button" onclick="setTeacherClaimsPage(${teacherClaimsCurrentPage - 1})" ${prevDisabled ? 'disabled' : ''}
            class="px-2.5 py-1 text-xs rounded-lg border transition cursor-pointer flex items-center gap-1 ${prevDisabled ? 'border-gray-200 text-gray-300 cursor-not-allowed bg-gray-50/50' : 'border-gray-200 text-gray-700 bg-white hover:bg-gray-50 active:scale-95'}">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            <span>Prev</span>
        </button>
    `;

    // Page numbers
    const maxVisible = 5;
    let startPage = Math.max(1, teacherClaimsCurrentPage - Math.floor(maxVisible / 2));
    let endPage = Math.min(totalPages, startPage + maxVisible - 1);
    if (endPage - startPage + 1 < maxVisible) {
        startPage = Math.max(1, endPage - maxVisible + 1);
    }

    if (startPage > 1) {
        html += `<button type="button" onclick="setTeacherClaimsPage(1)" class="w-7 h-7 flex items-center justify-center rounded-lg border border-gray-200 text-xs text-gray-700 bg-white hover:bg-gray-50 transition cursor-pointer">1</button>`;
        if (startPage > 2) {
            html += `<span class="px-1 text-gray-400 text-xs">...</span>`;
        }
    }

    for (let p = startPage; p <= endPage; p++) {
        const isActive = p === teacherClaimsCurrentPage;
        html += `
            <button type="button" onclick="setTeacherClaimsPage(${p})"
                class="w-7 h-7 flex items-center justify-center rounded-lg border text-xs font-semibold transition cursor-pointer ${isActive ? 'bg-blue-600 text-white border-blue-600 shadow-2xs' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'}">
                ${p}
            </button>
        `;
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            html += `<span class="px-1 text-gray-400 text-xs">...</span>`;
        }
        html += `<button type="button" onclick="setTeacherClaimsPage(${totalPages})" class="w-7 h-7 flex items-center justify-center rounded-lg border border-gray-200 text-xs text-gray-700 bg-white hover:bg-gray-50 transition cursor-pointer">${totalPages}</button>`;
    }

    // Next Button
    const nextDisabled = teacherClaimsCurrentPage === totalPages;
    html += `
        <button type="button" onclick="setTeacherClaimsPage(${teacherClaimsCurrentPage + 1})" ${nextDisabled ? 'disabled' : ''}
            class="px-2.5 py-1 text-xs rounded-lg border transition cursor-pointer flex items-center gap-1 ${nextDisabled ? 'border-gray-200 text-gray-300 cursor-not-allowed bg-gray-50/50' : 'border-gray-200 text-gray-700 bg-white hover:bg-gray-50 active:scale-95'}">
            <span>Next</span>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
    `;

    container.innerHTML = html;
}

// Initialize on DOM load or immediately if ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', renderTeacherClaimsPagination);
} else {
    renderTeacherClaimsPagination();
}

function openClaimDetailModalFromRow(button) {
    const row = button.closest('.teacher-claim-row');
    if (!row) return;
    try {
        const data = JSON.parse(row.getAttribute('data-claim-info'));
        openClaimDetailModal(data);
    } catch(e) {
        console.error('Failed to parse claim info', e);
    }
}

function openClaimDetailModal(data) {
    const modal = document.getElementById('claimDetailModal');
    if (!modal) return;

    document.getElementById('claimDetailRef').innerText = `Claim ${data.claim_number || ''}`;
    document.getElementById('claimDetailTeacherName').innerText = data.teacher_name || '—';
    document.getElementById('claimDetailTeacherEmail').innerText = data.teacher_email || '—';
    document.getElementById('claimDetailClassTitle').innerText = data.class_title || '—';
    document.getElementById('claimDetailSubjectName').innerText = data.subject_name || '—';
    document.getElementById('claimDetailClassDate').innerText = data.class_date || '—';
    document.getElementById('claimDetailDuration').innerText = `${data.duration_minutes || 60} minutes`;
    document.getElementById('claimDetailAmount').innerText = `RM ${data.amount || '0.00'}`;
    document.getElementById('claimDetailRate').innerText = `Rate: RM ${data.rate_per_class || '0.00'}`;
    document.getElementById('claimDetailBankName').innerText = data.bank_name || '—';
    document.getElementById('claimDetailBankAcc').innerText = data.bank_account_number || '—';
    document.getElementById('claimDetailBankHolder').innerText = data.bank_account_holder || '—';
    document.getElementById('claimDetailNotes').innerText = data.teacher_notes || '—';

    // Status styling
    const statusEl = document.getElementById('claimDetailStatus');
    const statusSubEl = document.getElementById('claimDetailStatusSub');
    const status = (data.status || '').toLowerCase();

    if (status === 'pending') {
        statusEl.className = 'font-medium text-sm mt-0.5 text-amber-700';
        statusEl.innerText = 'Pending Review';
        statusSubEl.innerText = 'Awaiting administrative approval';
    } else if (status === 'approved') {
        statusEl.className = 'font-medium text-sm mt-0.5 text-emerald-700';
        statusEl.innerText = 'Approved';
        statusSubEl.innerText = data.approved_at ? `Approved on ${data.approved_at}` : 'Ready for payment release';
    } else if (status === 'paid') {
        statusEl.className = 'font-medium text-sm mt-0.5 text-blue-700';
        statusEl.innerText = 'Paid Out';
        statusSubEl.innerText = data.paid_at ? `Paid on ${data.paid_at}` : 'Payment recorded';
    } else if (status === 'rejected') {
        statusEl.className = 'font-medium text-sm mt-0.5 text-rose-700';
        statusEl.innerText = 'Rejected';
        statusSubEl.innerText = 'Claim submission declined';
    } else {
        statusEl.className = 'font-medium text-sm mt-0.5 text-gray-700';
        statusEl.innerText = data.status || '—';
        statusSubEl.innerText = '';
    }

    // Attachment
    const proofRow = document.getElementById('claimDetailProofRow');
    const proofLink = document.getElementById('claimDetailProofLink');
    if (data.proof_attachment_path) {
        proofLink.href = data.proof_attachment_path;
        proofRow.classList.remove('hidden');
    } else {
        proofRow.classList.add('hidden');
    }

    // Audit Info Box
    const auditBox = document.getElementById('claimDetailAuditBox');
    const auditTitle = document.getElementById('claimDetailAuditTitle');
    const auditText = document.getElementById('claimDetailAuditText');
    if (status === 'rejected' && data.admin_notes && data.admin_notes !== '—') {
        auditTitle.innerText = 'Rejection Reason';
        auditText.innerText = data.admin_notes;
        auditBox.classList.remove('hidden');
    } else if (status === 'paid') {
        auditTitle.innerText = 'Payout Reference';
        auditText.innerText = `Method: ${data.payment_method || 'Bank Transfer'} | Ref: ${data.payment_reference || '—'} | Date: ${data.paid_at || '—'}`;
        auditBox.classList.remove('hidden');
    } else {
        auditBox.classList.add('hidden');
    }

    modal.classList.remove('hidden');
}

function closeClaimDetailModal() {
    const modal = document.getElementById('claimDetailModal');
    if (modal) modal.classList.add('hidden');
}

function openRejectClaimModal(claimId, claimNumber, teacherName) {
    const modal = document.getElementById('rejectClaimModal');
    const form = document.getElementById('rejectClaimForm');
    const subtitle = document.getElementById('rejectClaimModalSubtitle');
    if (!modal || !form) return;

    form.action = `{{ url('admin/teacher-claims') }}/${claimId}/reject`;
    if (subtitle) {
        subtitle.innerText = `Reject claim ${claimNumber} for ${teacherName}`;
    }
    const notes = document.getElementById('reject_admin_notes');
    if (notes) notes.value = '';

    modal.classList.remove('hidden');
}

function closeRejectClaimModal() {
    const modal = document.getElementById('rejectClaimModal');
    if (modal) modal.classList.add('hidden');
}

function openMarkPaidClaimModal(claimId, claimNumber, amount, teacherName, bankInfo, bankHolder) {
    const modal = document.getElementById('markPaidClaimModal');
    const form = document.getElementById('markPaidClaimForm');
    const subtitle = document.getElementById('markPaidModalSubtitle');
    if (!modal || !form) return;

    form.action = `{{ url('admin/teacher-claims') }}/${claimId}/mark-paid`;
    if (subtitle) subtitle.innerText = `Record payout for claim ${claimNumber}`;

    document.getElementById('markPaidTeacherName').innerText = teacherName || '—';
    document.getElementById('markPaidAmount').innerText = `RM ${amount || '0.00'}`;
    document.getElementById('markPaidBankInfo').innerText = bankInfo || '—';
    document.getElementById('markPaidBankHolder').innerText = bankHolder || '—';

    const refInput = document.getElementById('mark_paid_payment_reference');
    if (refInput) refInput.value = '';

    modal.classList.remove('hidden');
}

function closeMarkPaidClaimModal() {
    const modal = document.getElementById('markPaidClaimModal');
    if (modal) modal.classList.add('hidden');
}
</script>
