{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- TAB: PARENT & CHILD LINKS (#parent-links)                           --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
<div id="tab-parent-links" class="admin-tab-content hidden space-y-6">
    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gray-50/50">
            <div>
                <h2 class="font-semibold text-base text-gray-900">Parent &amp; Child Links</h2>
                <p class="text-xs text-gray-500 font-normal">Assigned parent and student relationships</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-gray-500 font-normal">{{ $allParentStudentLinks->count() }} Total Links</span>

                <div class="relative">
                    <svg class="w-3.5 h-3.5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text"
                           id="parentLinksSearch"
                           oninput="handleParentLinksSearch(this.value)"
                           placeholder="Search parent, student, or phone..."
                           class="text-xs rounded-xl border border-gray-200 bg-white pl-8 pr-3 py-1.5 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-slate-400 w-64 shadow-2xs">
                </div>

                <select id="parentLinksRelFilter" onchange="filterParentLinksRelation(this.value)" class="text-xs rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-gray-700 focus:outline-none focus:border-slate-400 shadow-2xs">
                    <option value="">All Relationships</option>
                    <option value="Father">Father</option>
                    <option value="Mother">Mother</option>
                    <option value="Guardian">Guardian</option>
                    <option value="primary">Primary Guardians</option>
                </select>

                <button onclick="openAssignLinkModal()" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-medium text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 shrink-0 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Link Student to Parent</span>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto admin-scrollbar">
            <table class="w-full text-left text-xs" id="parentLinksTable">
                <thead>
                    <tr class="border-b border-gray-200 text-gray-500 font-medium uppercase text-[10px] bg-gray-50/75">
                        <th class="py-3 px-4">Ref</th>
                        <th class="py-3 px-4">Parent Details</th>
                        <th class="py-3 px-4">Relationship</th>
                        <th class="py-3 px-4">Linked Student</th>
                        <th class="py-3 px-4">Level &amp; School</th>
                        <th class="py-3 px-4">Linked Date</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($allParentStudentLinks as $link)
                        <tr class="hover:bg-gray-50/60 transition target-row parent-link-row"
                            data-relationship="{{ $link->relationship ?? 'Guardian' }}"
                            data-is-primary="{{ $link->is_primary ? '1' : '0' }}">
                            <td class="py-3 px-4 font-mono text-gray-500 font-normal">
                                LNK-{{ str_pad($link->id, 4, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-gray-900">{{ $link->parent_name }}</div>
                                <div class="text-[11px] text-gray-500 font-normal">{{ $link->parent_email }}</div>
                                @if(!empty($link->parent_phone))
                                    <div class="text-[11px] text-gray-400 font-normal">{{ $link->parent_phone }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-normal text-gray-800">{{ $link->relationship ?: 'Guardian' }}</div>
                                @if($link->is_primary)
                                    <div class="text-[11px] text-gray-500 font-normal">Primary Guardian</div>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-gray-900">{{ $link->student_name }}</div>
                                <div class="text-[11px] text-gray-500 font-normal">{{ $link->student_email }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-normal text-gray-800">{{ $link->student_level ?: '—' }}</div>
                                @if(!empty($link->student_school))
                                    <div class="text-[11px] text-gray-500 font-normal">{{ $link->student_school }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-gray-500 font-normal whitespace-nowrap">
                                {{ !empty($link->created_at) ? \Carbon\Carbon::parse($link->created_at)->format('d M Y') : '—' }}
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button"
                                            onclick="openAssignLinkModal({{ $link->parent_user_id }}, {{ $link->student_id }}, '{{ addslashes($link->relationship ?? 'Guardian') }}', {{ $link->is_primary ? 'true' : 'false' }})"
                                            title="Edit Linkage"
                                            aria-label="Edit Linkage"
                                            class="w-8 h-8 rounded-lg border border-gray-200 text-gray-500 hover:text-gray-900 hover:border-gray-300 hover:bg-gray-50 flex items-center justify-center transition cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <form action="{{ route('admin.parents.unlink') }}" method="POST" onsubmit="return confirm('Are you sure you want to unlink {{ addslashes($link->parent_name) }} from {{ addslashes($link->student_name) }}?');" class="inline">
                                        @csrf
                                        <input type="hidden" name="parent_user_id" value="{{ $link->parent_user_id }}">
                                        <input type="hidden" name="student_id" value="{{ $link->student_id }}">
                                        <button type="submit"
                                                title="Unlink"
                                                aria-label="Unlink"
                                                class="w-8 h-8 rounded-lg border border-gray-200 text-gray-400 hover:text-rose-600 hover:border-rose-300 hover:bg-rose-50 flex items-center justify-center transition cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-gray-400">No parent &amp; child linkages found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between text-xs text-gray-500">
            <span id="parentLinksCountDisplay">Showing {{ $allParentStudentLinks->count() }} records</span>
        </div>
    </div>
</div>

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
                <span class="text-xs text-gray-500 font-normal">{{ $allParentsList->count() }} Total Parents</span>

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
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between text-xs text-gray-500">
            <span id="parentAccountsCountDisplay">Showing {{ $allParentsList->count() }} parent accounts</span>
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

function handleParentLinksSearch(val) {
    const query = (val || '').toLowerCase().trim();
    const relFilter = (document.getElementById('parentLinksRelFilter')?.value || '').toLowerCase();
    const rows = document.querySelectorAll('#parentLinksTable tbody .parent-link-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        const matchesQuery = !query || text.includes(query);
        
        let matchesRel = true;
        if (relFilter === 'primary') {
            matchesRel = row.getAttribute('data-is-primary') === '1';
        } else if (relFilter) {
            const rowRel = (row.getAttribute('data-relationship') || '').toLowerCase();
            matchesRel = rowRel.includes(relFilter);
        }

        if (matchesQuery && matchesRel) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const countDisplay = document.getElementById('parentLinksCountDisplay');
    if (countDisplay) {
        countDisplay.innerText = `Showing ${visibleCount} records`;
    }
}

function filterParentLinksRelation(val) {
    const searchVal = document.getElementById('parentLinksSearch')?.value || '';
    handleParentLinksSearch(searchVal);
}

function handleParentAccountsSearch(val) {
    const query = (val || '').toLowerCase().trim();
    const filterVal = document.getElementById('parentAccountsFilter')?.value || '';
    const rows = document.querySelectorAll('#parentAccountsTable tbody .parent-account-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        const matchesQuery = !query || text.includes(query);

        let matchesFilter = true;
        const hasChildren = row.getAttribute('data-has-children');
        if (filterVal === 'has_children') {
            matchesFilter = hasChildren === 'yes';
        } else if (filterVal === 'no_children') {
            matchesFilter = hasChildren === 'no';
        }

        if (matchesQuery && matchesFilter) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const countDisplay = document.getElementById('parentAccountsCountDisplay');
    if (countDisplay) {
        countDisplay.innerText = `Showing ${visibleCount} parent accounts`;
    }
}

function filterParentAccountsChildren(val) {
    const searchVal = document.getElementById('parentAccountsSearch')?.value || '';
    handleParentAccountsSearch(searchVal);
}
</script>
