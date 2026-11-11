<x-teacher-layout>
    <x-slot name="title">Class Fee Claims - Teacher Portal</x-slot>

    @php
        $currency = 'RM';
        $mappedClaims = $claims->map(function($c) use ($teacher) {
            return [
                'id' => $c->id,
                'claim_number' => $c->claim_number,
                'subject_name' => $c->subject_name,
                'class_title' => $c->class_title,
                'class_date' => $c->class_date ? $c->class_date->format('d M Y') : '—',
                'class_date_formatted' => $c->class_date ? $c->class_date->format('d M Y') : '—',
                'created_at_formatted' => $c->created_at ? $c->created_at->format('d M Y') : '',
                'duration_minutes' => $c->duration_minutes,
                'amount' => (float) $c->amount,
                'amount_formatted' => number_format($c->amount, 2),
                'status' => $c->status,
                'bank_name' => $c->bank_name ?: ($teacher->bank_name ?? '—'),
                'bank_account_number' => $c->bank_account_number ?: ($teacher->bank_account_number ?? '—'),
                'bank_account_holder' => $c->bank_account_holder ?: ($teacher->bank_account_holder ?: ($teacher->user->name ?? 'Teacher')),
                'payment_method' => $c->payment_method,
                'payment_reference' => $c->payment_reference,
                'paid_at' => $c->paid_at ? (is_string($c->paid_at) ? $c->paid_at : $c->paid_at->format('d M Y')) : null,
                'admin_notes' => $c->admin_notes,
                'teacher_notes' => $c->teacher_notes,
                'live_class_id' => $c->live_class_id,
            ];
        });
    @endphp

    <div x-data="{
        showClaimModal: false,
        showDetailModal: false,
        activeClaim: null,
        filterStatus: 'all',
        searchQuery: '',
        claimType: 'live_class',
        selectedLiveClassId: '',
        claimAmount: '50.00',
        completedClasses: @js($completedClasses),
        claimedIds: @js($claimedLiveClassIds),
        claims: {{ \Illuminate\Support\Js::from($mappedClaims) }},
        perPage: 10,
        currentPage: 1,

        get filteredClaims() {
            return this.claims.filter(c => {
                const query = this.searchQuery.trim().toLowerCase();
                const matchesQuery = !query || 
                    (c.claim_number && c.claim_number.toLowerCase().includes(query)) ||
                    (c.subject_name && c.subject_name.toLowerCase().includes(query)) ||
                    (c.class_title && c.class_title.toLowerCase().includes(query)) ||
                    (c.status && c.status.toLowerCase().includes(query));

                const matchesStatus = this.filterStatus === 'all' || c.status.toLowerCase() === this.filterStatus.toLowerCase();

                return matchesQuery && matchesStatus;
            });
        },
        get totalPages() {
            return Math.max(1, Math.ceil(this.filteredClaims.length / this.perPage));
        },
        get paginatedClaims() {
            if (this.currentPage > this.totalPages) {
                this.currentPage = 1;
            }
            const start = (this.currentPage - 1) * this.perPage;
            return this.filteredClaims.slice(start, start + this.perPage);
        },
        get startIndex() {
            if (this.filteredClaims.length === 0) return 0;
            return (this.currentPage - 1) * this.perPage + 1;
        },
        get endIndex() {
            return Math.min(this.currentPage * this.perPage, this.filteredClaims.length);
        },
        goToPage(p) {
            if (p >= 1 && p <= this.totalPages) {
                this.currentPage = p;
            }
        },
        resetFilters() {
            this.searchQuery = '';
            this.filterStatus = 'all';
            this.currentPage = 1;
        },

        onLiveClassChange() {
            if (!this.selectedLiveClassId) return;
            const cls = this.completedClasses.find(c => c.id == this.selectedLiveClassId);
            if (cls) {
                const form = this.$refs.claimForm;
                const subjName = cls.course?.subject?.display_name || cls.course?.subject?.name || cls.course?.title || '';
                if (subjName && form.subject_name) {
                    let matched = false;
                    for (let i = 0; i < form.subject_name.options.length; i++) {
                        if (form.subject_name.options[i].value === subjName) {
                            form.subject_name.selectedIndex = i;
                            matched = true;
                            break;
                        }
                    }
                    if (!matched && subjName) {
                        const opt = new Option(subjName, subjName, true, true);
                        form.subject_name.add(opt);
                    }
                }
                form.class_title.value = cls.title || 'Live Class Session';
                if (cls.scheduled_at) {
                    form.class_date.value = cls.scheduled_at.split('T')[0] || cls.scheduled_at.split(' ')[0];
                }
                form.duration_minutes.value = cls.duration_minutes || 60;
                form.course_id.value = cls.course_id || '';
            }
        },

        viewClaimDetails(claim) {
            this.activeClaim = claim;
            this.showDetailModal = true;
        }
    }" class="space-y-6">

        {{-- Flash Alerts --}}
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-xs sm:text-sm font-semibold flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-xs sm:text-sm font-medium shadow-2xs space-y-1">
                <div class="flex items-center gap-2 font-bold text-rose-900">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Unable to submit claim:</span>
                </div>
                <ul class="list-disc list-inside text-xs text-rose-700 pl-1 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Bank Account Configuration Reminder (if not configured) --}}
        @if(empty($teacher->bank_name) || empty($teacher->bank_account_number))
            <div class="p-4 bg-amber-50 border border-amber-200 rounded-3xl text-amber-900 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-2xs">
                <div class="flex items-start sm:items-center gap-3">
                    <div class="w-9 h-9 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <div class="text-xs sm:text-sm font-extrabold text-amber-950">Bank Account Details Required for Claim Payouts</div>
                        <div class="text-[11px] text-amber-800 font-medium mt-0.5">Please add your bank name and account number in your profile so payments can be processed promptly upon admin authorization.</div>
                    </div>
                </div>
                <a href="{{ route('teacher.profile', ['tab' => 'profile']) }}" 
                   class="shrink-0 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition-colors shadow-2xs">
                    Update Profile Bank Details
                </a>
            </div>
        @endif

        {{-- ── HEADER SECTION (Same like other Teacher Portal pages) ── --}}
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="font-heading font-black text-2xl text-gray-900">Class Fee Claims</h1>
                <p class="text-xs text-gray-500 font-medium mt-0.5">Submit your completed teaching sessions to request approval and payout authorization from the admin team.</p>
            </div>

            <button type="button" 
                    @click="showClaimModal = true"
                    class="py-2.5 px-5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white rounded-2xl text-xs font-extrabold shadow-sm transition-all cursor-pointer shrink-0 inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>Submit New Claim</span>
            </button>
        </div>

        {{-- ── 1. TOP 4 KPI SUMMARY CARDS (Standardized matching Students & Classes) ── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total Claims --}}
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Claims</p>
                    <p class="text-2xl font-black text-gray-900 mt-0.5">RM {{ number_format($stats['total_amount'], 2) }}</p>
                    <p class="text-[11px] text-gray-500 font-medium">{{ $stats['total_claims'] }} submission{{ $stats['total_claims'] === 1 ? '' : 's' }} recorded</p>
                </div>
            </div>

            {{-- Pending Approval --}}
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-100 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Pending Approval</p>
                    <p class="text-2xl font-black text-amber-600 mt-0.5">RM {{ number_format($stats['pending_amount'], 2) }}</p>
                    <p class="text-[11px] text-gray-500 font-medium">{{ $stats['pending_count'] }} awaiting review</p>
                </div>
            </div>

            {{-- Approved (Queued) --}}
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 border border-purple-100 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Approved (Queued)</p>
                    <p class="text-2xl font-black text-purple-600 mt-0.5">RM {{ number_format($stats['approved_amount'], 2) }}</p>
                    <p class="text-[11px] text-gray-500 font-medium">{{ $stats['approved_count'] }} ready for payout</p>
                </div>
            </div>

            {{-- Paid Out --}}
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Paid Out</p>
                    <p class="text-2xl font-black text-emerald-600 mt-0.5">RM {{ number_format($stats['paid_amount'], 2) }}</p>
                    <p class="text-[11px] text-gray-500 font-medium">{{ $stats['paid_count'] }} transferred</p>
                </div>
            </div>
        </div>

        {{-- ── 2. SEARCH & FILTER BAR (Matching Students & Tasks) ── --}}
        <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                {{-- Search Bar --}}
                <div class="relative flex-1 min-w-[220px]">
                    <input type="text" x-model="searchQuery" @input="currentPage = 1" placeholder="Search claim ref, subject, class title..." class="w-full bg-gray-50 border border-gray-200/80 rounded-2xl pl-9 pr-4 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                {{-- Status Filter Dropdown & Reset Button --}}
                <div class="flex items-center gap-2.5">
                    <div class="w-full sm:w-auto sm:min-w-[160px]">
                        <select x-model="filterStatus" @change="currentPage = 1" class="w-full bg-gray-50 border border-gray-200/80 rounded-2xl pl-3.5 pr-9 py-2.5 text-xs font-bold text-gray-700 outline-none cursor-pointer">
                            <option value="all">Status: All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="paid">Paid</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>

                    <button type="button" @click="resetFilters()" class="py-2.5 px-3.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-extrabold text-xs rounded-2xl transition-colors cursor-pointer shrink-0 inline-flex items-center justify-center gap-1" title="Reset all filters">
                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Reset</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ── 3. CLAIMS ROSTER TABLE (Matching Students & Tasks tables) ── --}}
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-gray-200 text-gray-400 uppercase font-black text-[10px]">
                            <th class="py-3 px-4">Claim ID</th>
                            <th class="py-3 px-4">Subject &amp; Class</th>
                            <th class="py-3 px-4">Session Date</th>
                            <th class="py-3 px-4">Duration</th>
                            <th class="py-3 px-4">Fee Amount</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Remarks</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium">
                        <template x-for="claim in paginatedClaims" :key="claim.id">
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                {{-- Claim ID --}}
                                <td class="py-3.5 px-4 font-mono font-bold text-gray-900 whitespace-nowrap">
                                    <span x-text="'#' + claim.claim_number"></span>
                                    <span class="block text-[10px] text-gray-400 font-sans font-normal mt-0.5" x-text="'Submitted ' + claim.created_at_formatted"></span>
                                </td>

                                {{-- Subject & Class Title --}}
                                <td class="py-3.5 px-4 whitespace-nowrap max-w-[220px]">
                                    <div class="font-extrabold text-gray-900 truncate" :title="claim.class_title" x-text="claim.class_title"></div>
                                    <div class="text-[11px] font-semibold text-blue-600 mt-0.5" x-text="claim.subject_name"></div>
                                </td>

                                {{-- Session Date --}}
                                <td class="py-3.5 px-4 whitespace-nowrap text-gray-700 font-bold" x-text="claim.class_date_formatted"></td>

                                {{-- Duration --}}
                                <td class="py-3.5 px-4 whitespace-nowrap text-gray-500 font-medium" x-text="claim.duration_minutes + ' mins'"></td>

                                {{-- Fee Amount --}}
                                <td class="py-3.5 px-4 whitespace-nowrap font-black text-gray-900 text-xs sm:text-sm" x-text="'RM ' + claim.amount_formatted"></td>

                                {{-- Status --}}
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span :class="{
                                        'bg-amber-50 text-amber-700 border-amber-200/60': claim.status === 'pending',
                                        'bg-blue-50 text-blue-700 border-blue-200/60': claim.status === 'approved',
                                        'bg-emerald-50 text-emerald-700 border-emerald-200/60': claim.status === 'paid',
                                        'bg-rose-50 text-rose-700 border-rose-200/60': claim.status === 'rejected'
                                    }" class="px-2.5 py-1 rounded-full font-black text-[10px] border inline-flex items-center gap-1 uppercase tracking-wider" x-text="claim.status === 'pending' ? 'Pending Review' : (claim.status.charAt(0).toUpperCase() + claim.status.slice(1))"></span>
                                </td>

                                {{-- Remarks --}}
                                <td class="py-3.5 px-4 text-gray-600 max-w-[180px] truncate" :title="claim.admin_notes || (claim.status === 'paid' ? 'Transferred via ' + (claim.payment_method || 'Bank') : 'Under review by admin')">
                                    <template x-if="claim.status === 'paid'">
                                        <span class="text-xs font-semibold text-emerald-700" x-text="'Paid: ' + (claim.payment_reference || 'Direct')"></span>
                                    </template>
                                    <template x-if="claim.status === 'rejected'">
                                        <span class="text-xs font-semibold text-rose-700" x-text="claim.admin_notes || 'Rejected'"></span>
                                    </template>
                                    <template x-if="claim.status === 'approved'">
                                        <span class="text-xs font-semibold text-blue-700">Authorized for payout</span>
                                    </template>
                                    <template x-if="claim.status === 'pending'">
                                        <span class="text-xs text-gray-400 italic">Under review</span>
                                    </template>
                                </td>

                                {{-- Actions --}}
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <button type="button" @click="viewClaimDetails(claim)" class="w-8 h-8 rounded-xl bg-blue-50 hover:bg-blue-600 text-blue-600 hover:text-white inline-flex items-center justify-center transition-all cursor-pointer shadow-2xs" title="View Claim Details">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>

                        {{-- Empty States --}}
                        <tr x-show="claims.length === 0">
                            <td colspan="8" class="py-12 text-center text-gray-400 font-medium">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <div class="w-12 h-12 rounded-2xl bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-1">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    </div>
                                    <p class="font-heading font-black text-sm text-gray-700">No class fee claims submitted yet</p>
                                    <p class="text-xs text-gray-400">Click "Submit New Claim" to record your completed teaching sessions.</p>
                                </div>
                            </td>
                        </tr>
                        <tr x-show="claims.length > 0 && filteredClaims.length === 0">
                            <td colspan="8" class="py-10 text-center text-gray-400 font-semibold">
                                No claims found matching your search or filters. Click <button type="button" @click="resetFilters()" class="text-blue-600 underline font-bold cursor-pointer hover:text-blue-700">Reset Filters</button> to view all claims.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- ── PAGINATION FOOTER ── --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-gray-100 text-xs">
                <div class="flex flex-wrap items-center gap-3 text-gray-500 font-medium">
                    <span x-show="filteredClaims.length > 0"
                          x-text="'Showing ' + startIndex + ' to ' + endIndex + ' of ' + filteredClaims.length + ' claims' + (claims.length !== filteredClaims.length ? ' (filtered from ' + claims.length + ' total)' : '')"></span>
                    <span x-show="filteredClaims.length === 0" class="text-gray-400 font-semibold">
                        Showing 0 of 0 claims
                    </span>

                    {{-- Rows per page selector --}}
                    <div class="flex items-center gap-1.5 pl-3 border-l border-gray-200">
                        <span class="text-gray-400 text-[11px]">Show</span>
                        <select x-model.number="perPage" @change="currentPage = 1" class="bg-gray-50 border border-gray-200/80 rounded-xl px-2.5 py-1 text-xs font-bold text-gray-700 outline-none cursor-pointer">
                            <option value="10">10 rows</option>
                            <option value="25">25 rows</option>
                            <option value="50">50 rows</option>
                            <option value="100">100 rows</option>
                        </select>
                    </div>
                </div>

                {{-- Functional Pagination Controls --}}
                <div class="flex items-center gap-1" x-show="totalPages > 1">
                    <button type="button" @click="goToPage(currentPage - 1)" :disabled="currentPage === 1"
                            class="px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 disabled:opacity-40 disabled:cursor-not-allowed text-gray-600 font-bold transition-colors cursor-pointer" title="Previous Page">
                        ‹
                    </button>
                    <template x-for="p in totalPages" :key="p">
                        <button type="button" @click="goToPage(p)"
                                :class="currentPage === p ? 'bg-blue-600 text-white font-black shadow-2xs' : 'bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold'"
                                class="px-2.5 py-1 rounded-lg text-xs transition-colors cursor-pointer"
                                x-text="p">
                        </button>
                    </template>
                    <button type="button" @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages"
                            class="px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 disabled:opacity-40 disabled:cursor-not-allowed text-gray-600 font-bold transition-colors cursor-pointer" title="Next Page">
                        ›
                    </button>
                </div>
            </div>
        </div>

        {{-- ── 4. SUBMIT CLAIM MODAL ── --}}
        <div x-show="showClaimModal" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            
            <div @click.away="showClaimModal = false" 
                 class="bg-white rounded-3xl shadow-2xl border border-gray-100 w-full max-w-xl p-6 sm:p-7 space-y-6 my-auto max-h-[90vh] overflow-y-auto transform transition-all">
                
                {{-- Modal Header --}}
                <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                    <div>
                        <span class="text-[10px] font-black uppercase text-blue-600 tracking-wider">Teacher Claim Form</span>
                        <h3 class="font-heading font-black text-xl text-gray-900">Submit Class Fee Claim</h3>
                    </div>
                    <button type="button" @click="showClaimModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-xl cursor-pointer">✕</button>
                </div>

                {{-- Form --}}
                <form action="{{ route('teacher.claims.store') }}" 
                      method="POST" 
                      enctype="multipart/form-data" 
                      x-ref="claimForm"
                      class="space-y-4">
                    @csrf

                    <input type="hidden" name="course_id" value="">

                    {{-- Mode Selector (Live Class vs Manual) --}}
                    <div>
                        <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1.5">Claim Mode</label>
                        <div class="grid grid-cols-2 gap-2 p-1 bg-gray-100 rounded-2xl text-xs font-extrabold">
                            <button type="button" 
                                    @click="claimType = 'live_class'" 
                                    :class="claimType === 'live_class' ? 'bg-white text-blue-600 shadow-2xs' : 'text-gray-600 hover:text-gray-900'"
                                    class="py-2 text-center rounded-xl transition-all cursor-pointer">
                                From Completed Live Class
                            </button>
                            <button type="button" 
                                    @click="claimType = 'manual'" 
                                    :class="claimType === 'manual' ? 'bg-white text-blue-600 shadow-2xs' : 'text-gray-600 hover:text-gray-900'"
                                    class="py-2 text-center rounded-xl transition-all cursor-pointer">
                                Manual Class Entry
                            </button>
                        </div>
                        <input type="hidden" name="claim_type" :value="claimType">
                    </div>

                    {{-- Completed Class Dropdown (if live_class mode) --}}
                    <div x-show="claimType === 'live_class'" class="space-y-1">
                        <label class="block text-xs font-bold text-gray-700">Select Conducted Live Class Session <span class="text-rose-500">*</span></label>
                        <select name="live_class_id" 
                                x-model="selectedLiveClassId" 
                                @change="onLiveClassChange()"
                                class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-4 py-2.5 text-xs font-medium outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                            <option value="">Choose a completed class...</option>
                            <template x-for="cls in completedClasses" :key="cls.id">
                                <option :value="cls.id" 
                                        :disabled="claimedIds.includes(cls.id)"
                                        x-text="`${cls.title} — ${cls.course?.subject?.display_name || cls.course?.subject?.name || 'Class'} (${cls.scheduled_at ? cls.scheduled_at.split('T')[0] : ''})${claimedIds.includes(cls.id) ? ' [Already Claimed]' : ''}`">
                                </option>
                            </template>
                        </select>
                        <p class="text-[10px] text-gray-400">Selecting a live class will automatically populate subject, title, and session date.</p>
                    </div>

                    {{-- Class Title & Subject --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Subject Name <span class="text-rose-500">*</span></label>
                            <select name="subject_name" 
                                    required 
                                    class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-3.5 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium cursor-pointer">
                                <option value="">Select Assigned Subject...</option>
                                @foreach($assignedSubjects as $subj)
                                    @php
                                        $subjTitle = $subj->display_name ?? $subj->name;
                                    @endphp
                                    <option value="{{ $subjTitle }}">{{ $subjTitle }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Class Session Title <span class="text-rose-500">*</span></label>
                            <input type="text" 
                                   name="class_title" 
                                   required 
                                   placeholder="e.g. Chapter 3: Intensive Revision" 
                                   class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-3.5 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                        </div>
                    </div>

                    {{-- Date & Duration --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Session Date <span class="text-rose-500">*</span></label>
                            <input type="date" 
                                   name="class_date" 
                                   required 
                                   value="{{ date('Y-m-d') }}"
                                   class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-3.5 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Duration (Minutes) <span class="text-rose-500">*</span></label>
                            <input type="number" 
                                   name="duration_minutes" 
                                   required 
                                   min="15" 
                                   max="480" 
                                   value="60"
                                   class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-3.5 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                        </div>
                    </div>

                    {{-- Class Teaching Rate / Amount (Editable, preset 50) --}}
                    <div class="p-3.5 bg-blue-50/70 border border-blue-100 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                        <div>
                            <label for="rate_per_class_input" class="font-extrabold text-blue-900 block cursor-pointer">
                                Class Teaching Rate <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[11px] text-blue-700 font-medium">Standard compensation rate per class session (editable)</span>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <span class="text-xs font-bold text-blue-900">RM</span>
                            <input type="number" 
                                   id="rate_per_class_input" 
                                   name="rate_per_class" 
                                   step="0.01" 
                                   min="0.01" 
                                   required 
                                   x-model="claimAmount"
                                   placeholder="50.00"
                                   class="w-28 bg-white border border-blue-200 rounded-xl px-3 py-1.5 text-sm font-black text-blue-700 outline-none focus:ring-2 focus:ring-blue-500 text-right shadow-2xs font-mono">
                            <input type="hidden" name="amount" :value="claimAmount">
                        </div>
                    </div>

                    {{-- Teacher Notes / Summary --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Session Summary &amp; Attendance Notes <span class="text-gray-400 font-normal">(Optional)</span></label>
                        <textarea name="teacher_notes" 
                                  rows="2" 
                                  placeholder="e.g. Completed Chapter 3 syllabus; 15 students attended online via Zoom."
                                  class="w-full bg-gray-50 border border-gray-200 rounded-2xl p-3 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium"></textarea>
                    </div>

                    {{-- Payout Destination Notice (Managed in Teacher Profile) --}}
                    <div class="pt-3 border-t border-gray-100">
                        @if(!empty($teacher->bank_name) && !empty($teacher->bank_account_number))
                            <div class="p-3.5 bg-gray-50/80 rounded-2xl border border-gray-200/80 flex items-start justify-between gap-3 text-xs">
                                <div class="space-y-0.5">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-extrabold uppercase text-gray-500 tracking-wider">Payout Destination Account</span>
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">From Profile</span>
                                    </div>
                                    <div class="font-extrabold text-gray-900">
                                        {{ $teacher->bank_name }} &bull; <span class="font-mono text-gray-800">{{ $teacher->bank_account_number }}</span>
                                    </div>
                                    <div class="text-[11px] text-gray-500 font-medium">
                                        Account Holder: {{ $teacher->bank_account_holder ?: ($teacher->user->name ?? 'Teacher') }}
                                    </div>
                                </div>
                                <a href="{{ route('teacher.profile', ['tab' => 'profile']) }}" 
                                   target="_blank"
                                   class="shrink-0 text-[11px] font-bold text-blue-600 hover:text-blue-800 underline mt-0.5">
                                    Edit in Profile
                                </a>
                            </div>
                        @else
                            <div class="p-3.5 bg-amber-50 rounded-2xl border border-amber-200 text-amber-900 flex items-start justify-between gap-3 text-xs">
                                <div>
                                    <span class="font-extrabold block text-amber-950">Bank Account Details Required</span>
                                    <span class="text-[11px] text-amber-800 block mt-0.5">Please update your bank details in My Profile so approved claims can be disbursed directly to your account.</span>
                                </div>
                                <a href="{{ route('teacher.profile', ['tab' => 'profile']) }}" 
                                   target="_blank"
                                   class="shrink-0 px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs transition-colors shadow-2xs">
                                    Set Up Profile
                                </a>
                            </div>
                        @endif
                    </div>

                    {{-- Proof Attachment (Optional) --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Supporting Document / Screenshot (Optional)</label>
                        <input type="file" 
                               name="proof_attachment" 
                               accept=".jpg,.jpeg,.png,.pdf"
                               class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2 text-xs outline-none file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 cursor-pointer">
                        <p class="text-[10px] text-gray-400 mt-0.5">Upload attendance sheet or Zoom/Meet completion screenshot (JPG, PNG, PDF up to 10MB).</p>
                    </div>

                    {{-- Buttons --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" 
                                @click="showClaimModal = false" 
                                class="py-2.5 px-5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold transition-colors cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="py-2.5 px-6 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-extrabold transition-colors shadow-sm hover:shadow-md cursor-pointer">
                            Submit RM <span x-text="isNaN(parseFloat(claimAmount)) ? '50.00' : parseFloat(claimAmount).toFixed(2)">50.00</span> Claim
                        </button>
                    </div>

                </form>

            </div>
        </div>

        {{-- ── 5. VIEW CLAIM DETAILS MODAL ── --}}
        <div x-show="showDetailModal" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            
            <div @click.away="showDetailModal = false" 
                 class="bg-white rounded-3xl shadow-2xl border border-gray-100 w-full max-w-lg p-6 sm:p-7 space-y-5 my-auto max-h-[90vh] overflow-y-auto transform transition-all">
                
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div>
                        <span class="text-[10px] font-black uppercase text-gray-400 tracking-wider">Claim Details</span>
                        <h3 class="font-heading font-black text-lg text-gray-900" x-text="activeClaim?.claim_number"></h3>
                    </div>
                    <button type="button" @click="showDetailModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-xl cursor-pointer">✕</button>
                </div>

                <div class="space-y-4 text-xs">
                    <div class="grid grid-cols-2 gap-3 p-3.5 bg-gray-50 rounded-2xl border border-gray-100">
                        <div>
                            <span class="text-[10px] font-extrabold text-gray-400 uppercase block">Subject</span>
                            <span class="font-bold text-gray-900 text-sm" x-text="activeClaim?.subject_name"></span>
                        </div>
                        <div>
                            <span class="text-[10px] font-extrabold text-gray-400 uppercase block">Fee Amount</span>
                            <span class="font-black text-blue-700 text-base" x-text="`RM ${Number(activeClaim?.amount || 50).toFixed(2)}`"></span>
                        </div>
                        <div>
                            <span class="text-[10px] font-extrabold text-gray-400 uppercase block">Class Date</span>
                            <span class="font-bold text-gray-900" x-text="activeClaim?.class_date"></span>
                        </div>
                        <div>
                            <span class="text-[10px] font-extrabold text-gray-400 uppercase block">Duration</span>
                            <span class="font-bold text-gray-900" x-text="`${activeClaim?.duration_minutes || 60} Minutes`"></span>
                        </div>
                    </div>

                    <div>
                        <span class="text-[10px] font-extrabold text-gray-400 uppercase block mb-1">Class Title</span>
                        <p class="font-bold text-gray-900 bg-white p-3 rounded-xl border border-gray-200" x-text="activeClaim?.class_title"></p>
                    </div>

                    <div x-show="activeClaim?.teacher_notes">
                        <span class="text-[10px] font-extrabold text-gray-400 uppercase block mb-1">Teacher Session Notes</span>
                        <p class="text-gray-700 bg-gray-50 p-3 rounded-xl border border-gray-100" x-text="activeClaim?.teacher_notes"></p>
                    </div>

                    {{-- Bank Details --}}
                    <div class="p-3.5 bg-gray-50 rounded-2xl border border-gray-100 space-y-1">
                        <span class="text-[10px] font-extrabold text-gray-400 uppercase block">Payout Destination Account</span>
                        <div class="flex items-center justify-between font-bold text-gray-900">
                            <span x-text="activeClaim?.bank_name"></span>
                            <span class="font-mono" x-text="activeClaim?.bank_account_number"></span>
                        </div>
                        <div class="text-[11px] text-gray-500 font-medium" x-text="`Holder: ${activeClaim?.bank_account_holder || '-'}`"></div>
                    </div>

                    {{-- Status Card --}}
                    <div class="p-3.5 rounded-2xl border"
                         :class="{
                             'bg-amber-50 border-amber-200 text-amber-900': activeClaim?.status === 'pending',
                             'bg-blue-50 border-blue-200 text-blue-900': activeClaim?.status === 'approved',
                             'bg-emerald-50 border-emerald-200 text-emerald-900': activeClaim?.status === 'paid',
                             'bg-rose-50 border-rose-200 text-rose-900': activeClaim?.status === 'rejected'
                         }">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-black uppercase text-[10px] tracking-wider">Current Status</span>
                            <span class="font-black uppercase text-xs" x-text="activeClaim?.status"></span>
                        </div>
                        <div x-show="activeClaim?.status === 'paid'" class="text-[11px] font-medium space-y-0.5">
                            <div>Payment Method: <span class="font-bold" x-text="activeClaim?.payment_method || 'Bank Transfer'"></span></div>
                            <div>Transaction Ref: <span class="font-mono font-bold" x-text="activeClaim?.payment_reference || '-'"></span></div>
                            <div class="text-[10px] text-gray-500" x-text="`Paid on: ${activeClaim?.paid_at || '-'}`"></div>
                        </div>
                        <div x-show="activeClaim?.status === 'rejected'" class="text-[11px] font-medium">
                            <span>Reason for Rejection: </span>
                            <span class="font-bold text-rose-800" x-text="activeClaim?.admin_notes || 'No reason specified'"></span>
                        </div>
                        <div x-show="activeClaim?.status === 'approved'" class="text-[11px] font-medium">
                            Authorized by admin. Awaiting payout processing.
                        </div>
                        <div x-show="activeClaim?.status === 'pending'" class="text-[11px] font-medium">
                            Submission received and queued for admin authorization.
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-3 border-t border-gray-100">
                    <button type="button" @click="showDetailModal = false" class="py-2 px-5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl text-xs transition-colors cursor-pointer">
                        Close
                    </button>
                </div>

            </div>
        </div>

    </div>
</x-teacher-layout>
