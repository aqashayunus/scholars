{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- TAB: PARENT ACCOUNTS (#parent-accounts)                             --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
<div id="tab-parent-accounts" class="admin-tab-content hidden space-y-6">
    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gray-50/50">
            <div>
                <h2 class="font-semibold text-base text-gray-900">Parent Accounts</h2>
                <p class="text-xs text-gray-500 font-normal">Registered guardian accounts and profiles</p>
            </div>
            <div class="flex items-center gap-3">

                <div class="relative">
                    <svg class="w-3.5 h-3.5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text"
                           id="parentAccountsSearch"
                           oninput="handleParentAccountsSearch(this.value)"
                           placeholder="Search parent name, email, or phone..."
                           class="text-xs rounded-xl border border-gray-200 bg-white pl-8 pr-3 py-1.5 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-slate-400 w-64 shadow-2xs">
                </div>

                <select id="parentAccountsFilter" onchange="filterParentAccountsChildren(this.value)" class="text-xs rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-gray-700 focus:outline-none focus:border-slate-400 shadow-2xs">
                    <option value="">All Parents</option>
                    <option value="has_children">With Linked Children</option>
                    <option value="no_children">No Children Linked</option>
                </select>

                <button onclick="openCreateParentUserModal()" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-medium text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 shrink-0 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Add Parent Account</span>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto admin-scrollbar">
            <table class="w-full text-left text-xs" id="parentAccountsTable">
                <thead>
                    <tr class="border-b border-gray-200 text-gray-500 font-medium uppercase text-[10px] bg-gray-50/75">
                        <th class="py-3 px-4">Ref</th>
                        <th class="py-3 px-4">Parent Name</th>
                        <th class="py-3 px-4">Email &amp; Contact</th>
                        <th class="py-3 px-4">Nationality / Passport</th>
                        <th class="py-3 px-4">Linked Children</th>
                        <th class="py-3 px-4">Joined Date</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($allParentsList as $p)
                        @php
                            $linkedChildren = $p->studentChildren;
                            if ($linkedChildren->isEmpty() && $p->children) {
                                $linkedChildren = $p->children;
                            }
                            $hasChildren = $linkedChildren->isNotEmpty();
                        @endphp
                        <tr class="hover:bg-gray-50/60 transition target-row parent-account-row"
                            data-has-children="{{ $hasChildren ? 'yes' : 'no' }}">
                            <td class="py-3 px-4 font-mono text-gray-500 font-normal">
                                PAR-{{ str_pad($p->id, 4, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-gray-900">{{ $p->name }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="text-gray-900 font-normal">{{ $p->email }}</div>
                                @if(!empty($p->phone))
                                    <div class="text-[11px] text-gray-400 font-normal">{{ $p->phone }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <div class="text-gray-800 font-normal">{{ ucfirst($p->nationality_type ?? 'local') }}</div>
                                @if(!empty($p->passport_number))
                                    <div class="font-mono text-[11px] text-gray-400">{{ $p->passport_number }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($hasChildren)
                                    <div class="space-y-1">
                                        @foreach($linkedChildren as $ch)
                                            @php
                                                $chName = $ch->user->name ?? 'Student #' . $ch->id;
                                                $chRel = isset($ch->pivot->relationship) ? $ch->pivot->relationship : null;
                                            @endphp
                                            <div class="text-gray-800 font-normal">
                                                <span>{{ $chName }}</span>
                                                @if($chRel)
                                                    <span class="text-gray-400 text-[11px]">({{ $chRel }})</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400 font-normal italic">No children linked</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-gray-500 font-normal whitespace-nowrap">
                                {{ $p->created_at ? $p->created_at->format('d M Y') : '—' }}
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button"
                                            onclick="openAssignLinkModal({{ $p->id }})"
                                            title="Link Child"
                                            aria-label="Link Child"
                                            class="w-8 h-8 rounded-lg border border-gray-200 text-gray-500 hover:text-gray-900 hover:border-gray-300 hover:bg-gray-50 flex items-center justify-center transition cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </button>
                                    <button type="button"
                                            onclick="openEditUserModal({{ json_encode($p) }})"
                                            title="Edit Profile"
                                            aria-label="Edit Profile"
                                            class="w-8 h-8 rounded-lg border border-gray-200 text-gray-500 hover:text-gray-900 hover:border-gray-300 hover:bg-gray-50 flex items-center justify-center transition cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button type="button"
                                            onclick="openResetPasswordModal({{ $p->id }}, '{{ addslashes($p->name) }}')"
                                            title="Reset Password"
                                            aria-label="Reset Password"
                                            class="w-8 h-8 rounded-lg border border-gray-200 text-gray-500 hover:text-gray-900 hover:border-gray-300 hover:bg-gray-50 flex items-center justify-center transition cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                    </button>
                                    <button type="button"
                                            onclick="openDeleteUserModal({{ $p->id }}, '{{ addslashes($p->name) }}')"
                                            title="Delete Account"
                                            aria-label="Delete Account"
                                            class="w-8 h-8 rounded-lg border border-gray-200 text-gray-400 hover:text-rose-600 hover:border-rose-300 hover:bg-rose-50 flex items-center justify-center transition cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-gray-400">No parent accounts found.</td>
                        </tr>
                    @endforelse

                    <tr id="parentAccountsNoResultsRow" class="hidden">
                        <td colspan="7" class="text-center py-8 text-gray-400 font-normal">
                            No parent accounts found matching your filter criteria.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Parent Accounts Pagination & Count Footer --}}
        <div id="parentAccountsPaginationContainer" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 py-3.5 border-t border-gray-100 bg-gray-50/30 text-xs">
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative inline-flex items-center">
                    <select id="parentAccountsPerPageSelect" onchange="setParentAccountsPerPage(this.value)" class="appearance-none !bg-none bg-white hover:bg-gray-50 border border-gray-200 rounded-lg pl-2.5 pr-6 py-1 text-xs font-normal text-gray-700 outline-none focus:outline-none focus:border-slate-400 transition cursor-pointer">
                        <option value="10" selected>10 rows</option>
                        <option value="25">25 rows</option>
                        <option value="50">50 rows</option>
                        <option value="all">All rows</option>
                    </select>
                    <svg class="w-3.5 h-3.5 text-gray-400 absolute right-2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>

                <div class="text-gray-500 font-normal">
                    <span id="parentAccountsShowingStart" class="font-semibold text-gray-800">{{ $allParentsList->isEmpty() ? 0 : 1 }}</span>-<span id="parentAccountsShowingEnd" class="font-semibold text-gray-800">{{ min(10, $allParentsList->count()) }}</span> of <span id="parentAccountsTotalFiltered" class="font-semibold text-gray-800">{{ $allParentsList->count() }}</span> rows
                    <span id="parentAccountsTotalUnfiltered" class="text-gray-400 text-[11px] hidden">(filtered from <span id="parentAccountsGrandTotal">{{ $allParentsList->count() }}</span> total)</span>
                </div>
            </div>

            <div class="flex items-center gap-1.5" id="parentAccountsPaginationButtons">
                {{-- Dynamically generated pagination buttons --}}
            </div>
        </div>
    </div>
</div>

{{-- ASSIGN / EDIT PARENT & STUDENT LINK MODAL --}}
<div id="assignLinkModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto admin-scrollbar border border-gray-200">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <div>
                <h3 class="font-semibold text-base text-gray-900" id="assignLinkModalTitle">Link Parent to Student</h3>
                <p class="text-xs text-gray-500 font-normal">Establish or update guardian-student association</p>
            </div>
            <button type="button" onclick="closeAssignLinkModal()" class="text-gray-400 hover:text-gray-600 text-xl font-normal cursor-pointer">&times;</button>
        </div>

        <form action="{{ route('admin.parents.link') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Parent Account <span class="text-rose-500">*</span></label>
                <select name="parent_user_id" id="assign_parent_user_id" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs text-gray-900 outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white font-normal">
                    <option value="">-- Select Parent Account --</option>
                    @if(isset($allParentsList))
                        @foreach($allParentsList->sortBy('name') as $p)
                            <option value="{{ $p->id }}">
                                {{ $p->name }} ({{ $p->email }}) {{ $p->phone ? '• ' . $p->phone : '' }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Student <span class="text-rose-500">*</span></label>
                <select name="student_id" id="assign_student_id" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs text-gray-900 outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white font-normal">
                    <option value="">-- Select Student --</option>
                    @if(isset($allStudentsList))
                        @foreach($allStudentsList->sortBy(fn($s) => $s->user->name ?? '') as $st)
                            <option value="{{ $st->id }}">
                                {{ $st->user->name ?? 'Student #' . $st->id }} ({{ $st->user->email ?? '-' }}) — {{ $st->level ?: 'No Level' }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Relationship to Student</label>
                <select name="relationship" id="assign_relationship" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs text-gray-900 outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white font-normal">
                    <option value="Father">Father</option>
                    <option value="Mother">Mother</option>
                    <option value="Guardian">Guardian</option>
                    <option value="Grandparent">Grandparent</option>
                    <option value="Relative">Relative</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="border border-gray-200 rounded-xl p-3 bg-gray-50/50 space-y-1">
                <label class="flex items-start gap-2.5 cursor-pointer">
                    <input type="checkbox" name="is_primary" id="assign_is_primary" value="1" class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <div class="text-xs">
                        <span class="font-semibold text-gray-800">Set as Primary Guardian</span>
                        <p class="text-gray-500 text-[11px] font-normal leading-relaxed">Designated recipient for billing, invoices, and academic reports.</p>
                    </div>
                </label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                <button type="button" onclick="closeAssignLinkModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 active:scale-95 text-gray-700 font-medium text-xs rounded-xl transition cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-semibold text-xs rounded-xl shadow-xs transition cursor-pointer">
                    Save Linkage
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// ── PARENT & CHILD LINKS & ACCOUNTS LOGIC ───────────────────────────────────────
function openAssignLinkModal(parentUserId = null, studentId = null, relationship = '', isPrimary = false) {
    const modal = document.getElementById('assignLinkModal');
    if (!modal) return;
    const title = document.getElementById('assignLinkModalTitle');
    const parentSelect = document.getElementById('assign_parent_user_id');
    const studentSelect = document.getElementById('assign_student_id');
    const relSelect = document.getElementById('assign_relationship');
    const primaryCheck = document.getElementById('assign_is_primary');

    if (parentSelect) {
        if (parentUserId) {
            parentSelect.value = parentUserId;
        } else {
            parentSelect.selectedIndex = 0;
        }
    }

    if (studentSelect) {
        if (studentId) {
            studentSelect.value = studentId;
        } else {
            studentSelect.selectedIndex = 0;
        }
    }

    if (relSelect) {
        if (relationship) {
            relSelect.value = relationship;
        } else {
            relSelect.value = 'Father';
        }
    }

    if (primaryCheck) {
        primaryCheck.checked = Boolean(isPrimary);
    }

    if (title) {
        title.innerText = (parentUserId && studentId) ? 'Edit Parent-Student Link' : 'Link Parent to Student';
    }

    modal.classList.remove('hidden');
}

function closeAssignLinkModal() {
    const modal = document.getElementById('assignLinkModal');
    if (modal) modal.classList.add('hidden');
}

function openCreateParentUserModal() {
    if (typeof openCreateUserModal === 'function') {
        openCreateUserModal();
        const roleSelect = document.querySelector('#createUserModal select[name="role"]');
        if (roleSelect) {
            roleSelect.value = 'parent';
            roleSelect.dispatchEvent(new Event('change'));
        }
    }
}

let parentAccountsCurrentPage = 1;
let parentAccountsPerPage = 10;
let parentAccountsSearchQuery = '';
let parentAccountsFilterVal = '';

function setParentAccountsPerPage(val) {
    parentAccountsPerPage = val === 'all' ? 999999 : parseInt(val, 10);
    parentAccountsCurrentPage = 1;

    const select = document.getElementById('parentAccountsPerPageSelect');
    if (select && select.value !== String(val)) select.value = String(val);

    renderParentAccountsPagination();
}

function handleParentAccountsSearch(val) {
    parentAccountsSearchQuery = (val || '').toLowerCase().trim();
    parentAccountsCurrentPage = 1;
    renderParentAccountsPagination();
}

function filterParentAccountsChildren(val) {
    parentAccountsFilterVal = (val || '').trim();
    parentAccountsCurrentPage = 1;
    renderParentAccountsPagination();
}

function setParentAccountsPage(pageNum) {
    parentAccountsCurrentPage = pageNum;
    renderParentAccountsPagination();
    const table = document.getElementById('parentAccountsTable');
    if (table) {
        const rect = table.getBoundingClientRect();
        if (rect.top < 0) {
            table.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
}

function renderParentAccountsPagination() {
    const table = document.getElementById('parentAccountsTable');
    if (!table) return;

    const rows = Array.from(table.querySelectorAll('tbody tr.parent-account-row'));
    const grandTotal = rows.length;

    const matchedRows = [];
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        const matchesQuery = !parentAccountsSearchQuery || text.includes(parentAccountsSearchQuery);

        let matchesFilter = true;
        const hasChildren = row.getAttribute('data-has-children');
        if (parentAccountsFilterVal === 'has_children') {
            matchesFilter = hasChildren === 'yes';
        } else if (parentAccountsFilterVal === 'no_children') {
            matchesFilter = hasChildren === 'no';
        }

        if (matchesQuery && matchesFilter) {
            matchedRows.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    const totalMatched = matchedRows.length;
    const totalPages = Math.max(1, Math.ceil(totalMatched / parentAccountsPerPage));

    if (parentAccountsCurrentPage > totalPages) parentAccountsCurrentPage = totalPages;
    if (parentAccountsCurrentPage < 1) parentAccountsCurrentPage = 1;

    const startIndex = (parentAccountsCurrentPage - 1) * parentAccountsPerPage;
    const endIndex = Math.min(startIndex + parentAccountsPerPage, totalMatched);

    matchedRows.forEach((row, idx) => {
        if (idx >= startIndex && idx < endIndex) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });

    const noResultsRow = document.getElementById('parentAccountsNoResultsRow');
    if (noResultsRow) {
        noResultsRow.classList.toggle('hidden', totalMatched > 0 || grandTotal === 0);
    }

    const showingStartEl = document.getElementById('parentAccountsShowingStart');
    const showingEndEl = document.getElementById('parentAccountsShowingEnd');
    const totalFilteredEl = document.getElementById('parentAccountsTotalFiltered');
    const unfilteredWrapper = document.getElementById('parentAccountsTotalUnfiltered');
    const grandTotalEl = document.getElementById('parentAccountsGrandTotal');

    if (showingStartEl) showingStartEl.innerText = totalMatched === 0 ? 0 : (startIndex + 1);
    if (showingEndEl) showingEndEl.innerText = endIndex;
    if (totalFilteredEl) totalFilteredEl.innerText = totalMatched;
    if (grandTotalEl) grandTotalEl.innerText = grandTotal;
    if (unfilteredWrapper) {
        unfilteredWrapper.classList.toggle('hidden', totalMatched === grandTotal);
    }

    renderParentAccountsPaginationControls(totalPages, totalMatched);
}

function renderParentAccountsPaginationControls(totalPages, totalMatched) {
    const container = document.getElementById('parentAccountsPaginationButtons');
    if (!container) return;

    if (totalMatched === 0 || totalPages <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = '';

    // Previous Button
    const prevDisabled = parentAccountsCurrentPage === 1;
    html += `
        <button type="button" onclick="setParentAccountsPage(${parentAccountsCurrentPage - 1})" ${prevDisabled ? 'disabled' : ''}
            class="px-2.5 py-1 text-xs rounded-lg border transition cursor-pointer flex items-center gap-1 ${prevDisabled ? 'border-gray-200 text-gray-300 cursor-not-allowed bg-gray-50/50' : 'border-gray-200 text-gray-700 bg-white hover:bg-gray-50 active:scale-95'}">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            <span>Prev</span>
        </button>
    `;

    // Page numbers
    const maxVisible = 5;
    let startPage = Math.max(1, parentAccountsCurrentPage - Math.floor(maxVisible / 2));
    let endPage = Math.min(totalPages, startPage + maxVisible - 1);
    if (endPage - startPage + 1 < maxVisible) {
        startPage = Math.max(1, endPage - maxVisible + 1);
    }

    if (startPage > 1) {
        html += `<button type="button" onclick="setParentAccountsPage(1)" class="w-7 h-7 flex items-center justify-center rounded-lg border border-gray-200 text-xs text-gray-700 bg-white hover:bg-gray-50 transition cursor-pointer">1</button>`;
        if (startPage > 2) {
            html += `<span class="px-1 text-gray-400 text-xs">...</span>`;
        }
    }

    for (let p = startPage; p <= endPage; p++) {
        const isActive = p === parentAccountsCurrentPage;
        html += `
            <button type="button" onclick="setParentAccountsPage(${p})"
                class="w-7 h-7 flex items-center justify-center rounded-lg border text-xs font-medium transition cursor-pointer ${isActive ? 'bg-slate-900 text-white border-slate-900 shadow-2xs' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'}">
                ${p}
            </button>
        `;
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            html += `<span class="px-1 text-gray-400 text-xs">...</span>`;
        }
        html += `<button type="button" onclick="setParentAccountsPage(${totalPages})" class="w-7 h-7 flex items-center justify-center rounded-lg border border-gray-200 text-xs text-gray-700 bg-white hover:bg-gray-50 transition cursor-pointer">${totalPages}</button>`;
    }

    // Next Button
    const nextDisabled = parentAccountsCurrentPage === totalPages;
    html += `
        <button type="button" onclick="setParentAccountsPage(${parentAccountsCurrentPage + 1})" ${nextDisabled ? 'disabled' : ''}
            class="px-2.5 py-1 text-xs rounded-lg border transition cursor-pointer flex items-center gap-1 ${nextDisabled ? 'border-gray-200 text-gray-300 cursor-not-allowed bg-gray-50/50' : 'border-gray-200 text-gray-700 bg-white hover:bg-gray-50 active:scale-95'}">
            <span>Next</span>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
    `;

    container.innerHTML = html;
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', renderParentAccountsPagination);
} else {
    renderParentAccountsPagination();
}
</script>
