{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- TAB: TEACHER CLASS FEE CLAIMS (#teacher-claims)                   --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
<div id="tab-teacher-claims" class="admin-tab-content hidden space-y-6">
    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
        {{-- Card Header & Filter Bar --}}
        <div class="px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gray-50/50">
            <div>
                <h2 class="font-semibold text-base text-gray-900">Teacher Class Claims</h2>
                <p class="text-xs text-gray-500 font-normal">Review and process class fee claim submissions from instructors</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-gray-500 font-normal" id="teacherClaimsTotalBadge">{{ $allTeacherClaims->count() }} Total Claims</span>
                @if(($pendingTeacherClaimsCount ?? 0) > 0)
                    <span class="text-xs text-gray-600 font-normal">({{ $pendingTeacherClaimsCount }} Pending Review)</span>
                @endif

                <div class="relative">
                    <svg class="w-3.5 h-3.5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text"
                           id="teacherClaimsSearch"
                           oninput="handleTeacherClaimsSearch(this.value)"
                           placeholder="Search claim, teacher, class..."
                           class="text-xs rounded-xl border border-gray-200 bg-white pl-8 pr-3 py-1.5 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-slate-400 w-64 shadow-2xs">
                </div>

                <select id="teacherClaimsStatusFilter" onchange="filterTeacherClaimsStatus(this.value)" class="text-xs rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-gray-700 focus:outline-none focus:border-slate-400 shadow-2xs">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending Review</option>
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
                    <tr class="border-b border-gray-200 text-gray-500 font-medium uppercase text-[10px] bg-gray-50/75">
                        <th class="py-3 px-4">Claim Ref</th>
                        <th class="py-3 px-4">Teacher Details</th>
                        <th class="py-3 px-4">Class &amp; Subject</th>
                        <th class="py-3 px-4">Class Date &amp; Duration</th>
                        <th class="py-3 px-4">Claim Amount</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Bank Account</th>
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
                            <td class="py-3 px-4 font-mono text-gray-500 font-normal">
                                {{ $claim->claim_number }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-gray-900">{{ $teacherName }}</div>
                                <div class="text-[11px] text-gray-500 font-normal">{{ $teacherEmail }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-normal text-gray-800">{{ $classTitle }}</div>
                                <div class="text-[11px] text-gray-500 font-normal">{{ $subjectName }}</div>
                            </td>
                            <td class="py-3 px-4 text-gray-600 font-normal whitespace-nowrap">
                                <div>{{ $claim->class_date ? \Carbon\Carbon::parse($claim->class_date)->format('d M Y') : '—' }}</div>
                                <div class="text-[11px] text-gray-400 font-normal">{{ $claim->duration_minutes ?: 60 }} mins</div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900">RM {{ number_format($claim->amount, 2) }}</div>
                                <div class="text-[11px] text-gray-400 font-normal">Rate: RM {{ number_format($claim->rate_per_class, 2) }}</div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($claim->status === 'pending')
                                    <div class="text-xs text-amber-700 font-medium">Pending Review</div>
                                @elseif($claim->status === 'approved')
                                    <div class="text-xs text-emerald-700 font-medium">Approved</div>
                                    @if($claim->approved_at)
                                        <div class="text-[11px] text-gray-400 font-normal">{{ \Carbon\Carbon::parse($claim->approved_at)->format('d M Y') }}</div>
                                    @endif
                                @elseif($claim->status === 'paid')
                                    <div class="text-xs text-gray-800 font-medium">Paid</div>
                                    @if($claim->payment_reference)
                                        <div class="text-[11px] text-gray-400 font-mono">{{ $claim->payment_reference }}</div>
                                    @endif
                                @elseif($claim->status === 'rejected')
                                    <div class="text-xs text-rose-700 font-medium">Rejected</div>
                                @else
                                    <div class="text-xs text-gray-600 font-medium">{{ ucfirst($claim->status) }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($bankAcc !== '—')
                                    <div class="text-xs text-gray-800 font-normal">{{ $bankName }} • {{ $bankAcc }}</div>
                                    @if($bankHolder)
                                        <div class="text-[11px] text-gray-400 font-normal uppercase">{{ $bankHolder }}</div>
                                    @endif
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    {{-- View Details Modal Button --}}
                                    <button type="button"
                                            onclick="openClaimDetailModalFromRow(this)"
                                            title="View Claim Details"
                                            aria-label="View Claim Details"
                                            class="w-8 h-8 rounded-lg border border-gray-200 text-gray-500 hover:text-gray-900 hover:border-gray-300 hover:bg-gray-50 flex items-center justify-center transition cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>

                                    @if($claim->status === 'pending')
                                        {{-- Approve Button --}}
                                        <form action="{{ route('admin.teacher-claims.approve', $claim->id) }}" method="POST" onsubmit="return confirm('Approve claim {{ addslashes($claim->claim_number) }} (RM {{ number_format($claim->amount, 2) }}) for {{ addslashes($teacherName) }}?');" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    title="Approve Claim"
                                                    aria-label="Approve Claim"
                                                    class="w-8 h-8 rounded-lg border border-gray-200 text-gray-500 hover:text-gray-900 hover:border-gray-300 hover:bg-gray-50 flex items-center justify-center transition cursor-pointer">
                                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                        </form>

                                        {{-- Reject Button --}}
                                        <button type="button"
                                                onclick="openRejectClaimModal({{ $claim->id }}, '{{ addslashes($claim->claim_number) }}', '{{ addslashes($teacherName) }}')"
                                                title="Reject Claim"
                                                aria-label="Reject Claim"
                                                class="w-8 h-8 rounded-lg border border-gray-200 text-gray-500 hover:text-gray-900 hover:border-gray-300 hover:bg-gray-50 flex items-center justify-center transition cursor-pointer">
                                            <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    @elseif($claim->status === 'approved')
                                        {{-- Mark as Paid Button --}}
                                        <button type="button"
                                                onclick="openMarkPaidClaimModal({{ $claim->id }}, '{{ addslashes($claim->claim_number) }}', '{{ number_format($claim->amount, 2) }}', '{{ addslashes($teacherName) }}', '{{ addslashes($bankName . ' • ' . $bankAcc) }}', '{{ addslashes($bankHolder) }}')"
                                                title="Record Payout / Mark Paid"
                                                aria-label="Record Payout / Mark Paid"
                                                class="w-8 h-8 rounded-lg border border-gray-200 text-gray-500 hover:text-gray-900 hover:border-gray-300 hover:bg-gray-50 flex items-center justify-center transition cursor-pointer">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
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
                </tbody>
            </table>
        </div>

        {{-- Footer Count --}}
        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between text-xs text-gray-500">
            <span id="teacherClaimsCountDisplay">Showing {{ $allTeacherClaims->count() }} claims</span>
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
function handleTeacherClaimsSearch(val) {
    const query = (val || '').toLowerCase().trim();
    const statusFilter = (document.getElementById('teacherClaimsStatusFilter')?.value || '').toLowerCase();
    const rows = document.querySelectorAll('#teacherClaimsTable tbody .teacher-claim-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        const matchesQuery = !query || text.includes(query);
        const rowStatus = (row.getAttribute('data-status') || '').toLowerCase();
        const matchesStatus = !statusFilter || rowStatus === statusFilter;

        if (matchesQuery && matchesStatus) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const countDisplay = document.getElementById('teacherClaimsCountDisplay');
    if (countDisplay) {
        countDisplay.innerText = `Showing ${visibleCount} claims`;
    }
}

function filterTeacherClaimsStatus(val) {
    const searchVal = document.getElementById('teacherClaimsSearch')?.value || '';
    handleTeacherClaimsSearch(searchVal);
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
        statusEl.className = 'font-medium text-sm mt-0.5 text-gray-900';
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
