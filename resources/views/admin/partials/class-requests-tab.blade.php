{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- TAB: TEACHER CLASS REQUESTS (Reschedule & Cancellation Approvals)  --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
<div id="tab-class-requests" class="admin-tab-content hidden space-y-6"
     x-data="{
         searchQuery: '',
         statusFilter: '',
         typeFilter: '',
         
         // Modal State
         approveModalOpen: false,
         rejectModalOpen: false,
         viewModalOpen: false,
         
         selectedReq: null,
         adminNote: '',
         actionUrl: '',

         openApproveModal(req) {
             this.selectedReq = req;
             this.adminNote = 'Approved by administration.';
             this.actionUrl = '/admin/class-requests/' + req.id + '/approve';
             this.approveModalOpen = true;
         },

         openRejectModal(req) {
             this.selectedReq = req;
             this.adminNote = '';
             this.actionUrl = '/admin/class-requests/' + req.id + '/reject';
             this.rejectModalOpen = true;
         },

         openViewModal(req) {
             this.selectedReq = req;
             this.viewModalOpen = true;
         },

         matchRow(status, type, text) {
             if (this.statusFilter && status !== this.statusFilter) return false;
             if (this.typeFilter && type !== this.typeFilter) return false;
             if (this.searchQuery && !text.toLowerCase().includes(this.searchQuery.toLowerCase())) return false;
             return true;
         }
     }">

    {{-- Top Metric Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Requests --}}
        <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider block">Total Requests</span>
                <span class="text-xl font-bold text-gray-900 leading-tight">{{ $allClassRequests->count() }}</span>
            </div>
        </div>

        {{-- Pending Approval --}}
        <div class="bg-white p-4 rounded-2xl border {{ $pendingClassRequestsCount > 0 ? 'border-amber-300 bg-amber-50/20' : 'border-gray-200/80' }} shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl {{ $pendingClassRequestsCount > 0 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600' }} flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="flex items-center gap-1.5">
                    <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider block">Pending Review</span>
                    @if($pendingClassRequestsCount > 0)
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    @endif
                </div>
                <span class="text-xl font-bold {{ $pendingClassRequestsCount > 0 ? 'text-amber-700' : 'text-gray-900' }} leading-tight">{{ $pendingClassRequestsCount }}</span>
            </div>
        </div>

        {{-- Reschedule Requests --}}
        <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </div>
            <div>
                <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider block">Reschedule Requests</span>
                <span class="text-xl font-bold text-gray-900 leading-tight">{{ $rescheduleRequestsCount }}</span>
            </div>
        </div>

        {{-- Cancellation Requests --}}
        <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider block">Cancellation Requests</span>
                <span class="text-xl font-bold text-gray-900 leading-tight">{{ $cancelRequestsCount }}</span>
            </div>
        </div>
    </div>

    {{-- Main Table Container --}}
    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
        {{-- Card Header & Filter Bar --}}
        <div class="p-5 pb-4 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-100">
            <div>
                <h2 class="font-semibold text-lg text-gray-900 flex items-center gap-2">
                    <span>Class Reschedule &amp; Cancellation Requests</span>
                    @if($pendingClassRequestsCount > 0)
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">
                            {{ $pendingClassRequestsCount }} Action Needed
                        </span>
                    @endif
                </h2>
                <p class="text-xs text-gray-500 font-normal mt-0.5">
                    Review and authorize instructor requests to shift or cancel scheduled virtual class sessions
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                {{-- Search Box --}}
                <div class="relative">
                    <input type="text"
                           x-model="searchQuery"
                           placeholder="Search teacher, class, reason..."
                           class="bg-gray-50 border border-gray-200 rounded-xl pl-8 pr-3 py-1.5 text-xs text-gray-800 placeholder-gray-400 outline-none focus:ring-2 focus:ring-blue-500 w-52 md:w-60 font-normal transition">
                    <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                {{-- Status Filter --}}
                <select x-model="statusFilter"
                        class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 text-xs text-gray-700 outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                    <option value="">All Statuses ({{ $allClassRequests->count() }})</option>
                    <option value="pending">Pending ({{ $pendingClassRequestsCount }})</option>
                    <option value="approved">Approved ({{ $approvedClassRequestsCount }})</option>
                    <option value="rejected">Rejected ({{ $rejectedClassRequestsCount }})</option>
                </select>

                {{-- Type Filter --}}
                <select x-model="typeFilter"
                        class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 text-xs text-gray-700 outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                    <option value="">All Request Types</option>
                    <option value="reschedule">Reschedule</option>
                    <option value="cancel">Cancellation</option>
                </select>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto admin-scrollbar">
            <table class="w-full text-left text-xs" id="classRequestsTable">
                <thead>
                    <tr class="border-b border-gray-100 text-gray-400 font-medium uppercase text-[10px] bg-gray-50/50">
                        <th class="py-3 px-4">Request Ref</th>
                        <th class="py-3 px-4">Instructor</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4">Session &amp; Subject</th>
                        <th class="py-3 px-4">Original Scheduled Time</th>
                        <th class="py-3 px-4">Requested Time</th>
                        <th class="py-3 px-4">Teacher's Reason</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($allClassRequests as $req)
                        @php
                            $teacherUser = $req->teacher?->user;
                            $teacherName = $teacherUser?->name ?? ('Teacher #' . $req->teacher_id);
                            $teacherEmail = $teacherUser?->email ?? '';
                            $liveClass = $req->liveClass;
                            $classTitle = $liveClass?->title ?? 'Session #' . $req->live_class_id;
                            $subjectName = $liveClass?->course?->subject?->display_name ?? ($liveClass?->course?->subject?->name ?? 'Course Class');
                            $courseTitle = $liveClass?->course?->title ?? '';

                            $reqPayload = [
                                'id'               => $req->id,
                                'type'             => $req->type,
                                'status'           => $req->status,
                                'reason'           => $req->reason,
                                'admin_note'       => $req->admin_note ?? '',
                                'teacher_name'     => $teacherName,
                                'teacher_email'    => $teacherEmail,
                                'class_title'      => $classTitle,
                                'subject_name'     => $subjectName,
                                'course_title'     => $courseTitle,
                                'original_time'    => $liveClass?->scheduled_at ? $liveClass->scheduled_at->format('d M Y, h:i A') : 'TBD',
                                'requested_time'   => $req->requested_at ? \Carbon\Carbon::parse($req->requested_at)->format('d M Y, h:i A') : 'N/A',
                                'created_at'       => $req->created_at ? $req->created_at->format('d M Y, h:i A') : '',
                            ];

                            $searchBlob = strtolower($req->id . ' ' . $teacherName . ' ' . $teacherEmail . ' ' . $classTitle . ' ' . $subjectName . ' ' . $req->reason . ' ' . $req->type . ' ' . $req->status);
                        @endphp

                        <tr class="hover:bg-gray-50/70 transition"
                            x-show="matchRow('{{ $req->status }}', '{{ $req->type }}', '{{ addslashes($searchBlob) }}')">
                            
                            {{-- 1. Ref --}}
                            <td class="py-3.5 px-4 font-mono font-medium text-gray-800 whitespace-nowrap">
                                #REQ-{{ str_pad($req->id, 4, '0', STR_PAD_LEFT) }}
                                <div class="text-[10px] text-gray-400 font-sans font-normal">{{ $req->created_at?->diffForHumans() }}</div>
                            </td>

                            {{-- 2. Instructor --}}
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs shrink-0">
                                        {{ strtoupper(substr($teacherName, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900 leading-tight">{{ $teacherName }}</div>
                                        <div class="text-[11px] text-gray-400 font-normal leading-tight">{{ $teacherEmail }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- 3. Type --}}
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($req->type === 'reschedule')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/60">
                                        <svg class="w-3 h-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        <span>Reschedule</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200/60">
                                        <svg class="w-3 h-3 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        <span>Cancellation</span>
                                    </span>
                                @endif
                            </td>

                            {{-- 4. Session & Subject --}}
                            <td class="py-3.5 px-4 max-w-[200px]">
                                <div class="font-medium text-gray-900 truncate" title="{{ $classTitle }}">{{ $classTitle }}</div>
                                <div class="text-[11px] text-gray-500 truncate" title="{{ $subjectName }}">{{ $subjectName }}</div>
                            </td>

                            {{-- 5. Original Time --}}
                            <td class="py-3.5 px-4 whitespace-nowrap font-normal text-gray-700">
                                @if($liveClass?->scheduled_at)
                                    <div>{{ $liveClass->scheduled_at->format('d M Y') }}</div>
                                    <div class="text-[11px] text-gray-400">{{ $liveClass->scheduled_at->format('h:i A') }} ({{ $liveClass->duration_minutes ?? 60 }}m)</div>
                                @else
                                    <span class="text-gray-400">TBD</span>
                                @endif
                            </td>

                            {{-- 6. Requested Time --}}
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($req->type === 'reschedule' && $req->requested_at)
                                    <div class="font-semibold text-blue-700">{{ \Carbon\Carbon::parse($req->requested_at)->format('d M Y') }}</div>
                                    <div class="text-[11px] text-blue-600 font-medium">{{ \Carbon\Carbon::parse($req->requested_at)->format('h:i A') }}</div>
                                @elseif($req->type === 'cancel')
                                    <span class="text-[11px] font-semibold text-rose-600 bg-rose-50 px-2 py-0.5 rounded border border-rose-100">Session Void</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>

                            {{-- 7. Reason --}}
                            <td class="py-3.5 px-4 max-w-[220px]">
                                <div class="text-gray-700 text-xs line-clamp-2" title="{{ $req->reason }}">
                                    {{ $req->reason }}
                                </div>
                            </td>

                            {{-- 8. Status --}}
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($req->status === 'pending')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-amber-100 text-amber-800 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        <span>Pending Review</span>
                                    </span>
                                @elseif($req->status === 'approved')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Approved</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-rose-100 text-rose-800 border border-rose-200">
                                        <svg class="w-3 h-3 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        <span>Rejected</span>
                                    </span>
                                @endif

                                @if($req->admin_note)
                                    <div class="text-[10px] text-gray-400 font-normal mt-0.5 truncate max-w-[130px]" title="Admin Note: {{ $req->admin_note }}">
                                        Note: {{ $req->admin_note }}
                                    </div>
                                @endif
                            </td>

                            {{-- 9. Actions --}}
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                @if($req->status === 'pending')
                                    <div class="flex items-center justify-end gap-1.5">
                                        {{-- Approve Button --}}
                                        <button type="button"
                                                @click="openApproveModal({{ json_encode($reqPayload) }})"
                                                class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-2xs transition flex items-center gap-1 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span>Approve</span>
                                        </button>

                                        {{-- Reject Button --}}
                                        <button type="button"
                                                @click="openRejectModal({{ json_encode($reqPayload) }})"
                                                class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg text-xs font-semibold transition flex items-center gap-1 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            <span>Reject</span>
                                        </button>
                                    </div>
                                @else
                                    <button type="button"
                                            @click="openViewModal({{ json_encode($reqPayload) }})"
                                            class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-medium transition cursor-pointer">
                                        View Details
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <p class="font-medium text-sm text-gray-500">No class requests recorded.</p>
                                    <p class="text-xs text-gray-400">Rescheduling and cancellation requests submitted by teachers will appear here.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── APPROVE MODAL ── --}}
    <div x-show="approveModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="approveModalOpen = false"
             class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-lg p-6 space-y-4 my-auto">
            
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-gray-900">Approve Class Request</h3>
                        <p class="text-xs text-gray-500" x-text="`Action: Authorize ${selectedReq ? selectedReq.type : ''} request`"></p>
                    </div>
                </div>
                <button type="button" @click="approveModalOpen = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg">✕</button>
            </div>

            {{-- Summary of Request --}}
            <template x-if="selectedReq">
                <div class="p-3.5 bg-gray-50 rounded-xl space-y-2 text-xs border border-gray-100">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Instructor:</span>
                        <span class="font-bold text-gray-900" x-text="selectedReq.teacher_name"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Session:</span>
                        <span class="font-medium text-gray-800" x-text="selectedReq.class_title"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Original Time:</span>
                        <span class="font-medium text-gray-700" x-text="selectedReq.original_time"></span>
                    </div>
                    <template x-if="selectedReq.type === 'reschedule'">
                        <div class="flex justify-between items-center bg-blue-50/70 p-2 rounded-lg text-blue-900 font-semibold border border-blue-100">
                            <span>New Requested Time:</span>
                            <span class="text-blue-700 font-bold" x-text="selectedReq.requested_time"></span>
                        </div>
                    </template>
                    <template x-if="selectedReq.type === 'cancel'">
                        <div class="flex justify-between items-center bg-rose-50/70 p-2 rounded-lg text-rose-900 font-semibold border border-rose-100">
                            <span>Action Effect:</span>
                            <span class="text-rose-700 font-bold">Class session will be marked cancelled</span>
                        </div>
                    </template>
                    <div class="pt-1 border-t border-gray-200/60">
                        <span class="text-gray-500 block mb-0.5">Teacher's Reason:</span>
                        <p class="text-gray-800 font-medium italic bg-white p-2 rounded border border-gray-200" x-text="selectedReq.reason"></p>
                    </div>
                </div>
            </template>

            <form method="POST" :action="actionUrl" class="space-y-4 text-xs font-semibold">
                @csrf
                <div>
                    <label class="block font-bold text-gray-800 mb-1">Administrative Note / Message to Instructor &amp; Students</label>
                    <textarea name="admin_note" x-model="adminNote" rows="2" placeholder="e.g. Approved. Students and parents will be notified automatically." class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs outline-none focus:ring-2 focus:ring-emerald-500 font-normal"></textarea>
                    <p class="text-[10px] text-gray-400 mt-1 font-normal">All enrolled students and linked parents will receive a system notification regarding this schedule update.</p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="approveModalOpen = false" class="py-2 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl transition cursor-pointer">Cancel</button>
                    <button type="submit" class="py-2 px-5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold transition shadow-sm cursor-pointer flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Confirm Approval</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── REJECT MODAL ── --}}
    <div x-show="rejectModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="rejectModalOpen = false"
             class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-lg p-6 space-y-4 my-auto">
            
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-gray-900">Reject Class Request</h3>
                        <p class="text-xs text-gray-500">Decline teacher's requested schedule modification</p>
                    </div>
                </div>
                <button type="button" @click="rejectModalOpen = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg">✕</button>
            </div>

            {{-- Summary of Request --}}
            <template x-if="selectedReq">
                <div class="p-3.5 bg-gray-50 rounded-xl space-y-1.5 text-xs border border-gray-100">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Instructor:</span>
                        <span class="font-bold text-gray-900" x-text="selectedReq.teacher_name"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Session:</span>
                        <span class="font-medium text-gray-800" x-text="selectedReq.class_title"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Reason:</span>
                        <span class="font-normal italic text-gray-700" x-text="selectedReq.reason"></span>
                    </div>
                </div>
            </template>

            <form method="POST" :action="actionUrl" class="space-y-4 text-xs font-semibold">
                @csrf
                <div>
                    <label class="block font-bold text-gray-800 mb-1">Reason for Rejection *</label>
                    <textarea name="admin_note" x-model="adminNote" required rows="3" placeholder="Provide clear feedback on why this request cannot be approved (e.g. Schedule clash with major exams, insufficient notice)..." class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs outline-none focus:ring-2 focus:ring-rose-500 font-normal"></textarea>
                    <p class="text-[10px] text-gray-400 mt-1 font-normal">This note will be sent directly to the instructor in their system notifications.</p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="rejectModalOpen = false" class="py-2 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl transition cursor-pointer">Cancel</button>
                    <button type="submit" class="py-2 px-5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold transition shadow-sm cursor-pointer flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Confirm Rejection</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── VIEW DETAILS MODAL ── --}}
    <div x-show="viewModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="viewModalOpen = false"
             class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-lg p-6 space-y-4 my-auto text-xs">
            
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="font-bold text-base text-gray-900">Class Request Details</h3>
                <button type="button" @click="viewModalOpen = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg">✕</button>
            </div>

            <template x-if="selectedReq">
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3 p-3 bg-gray-50 rounded-xl">
                        <div>
                            <span class="text-gray-400 text-[10px] uppercase font-bold block">Status</span>
                            <span class="font-bold text-sm capitalize"
                                  :class="selectedReq.status === 'approved' ? 'text-emerald-700' : (selectedReq.status === 'rejected' ? 'text-rose-700' : 'text-amber-700')"
                                  x-text="selectedReq.status"></span>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[10px] uppercase font-bold block">Request Type</span>
                            <span class="font-bold text-sm capitalize text-gray-900" x-text="selectedReq.type"></span>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[10px] uppercase font-bold block">Instructor</span>
                            <span class="font-semibold text-gray-900" x-text="selectedReq.teacher_name"></span>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[10px] uppercase font-bold block">Submitted On</span>
                            <span class="font-medium text-gray-700" x-text="selectedReq.created_at"></span>
                        </div>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl space-y-1.5">
                        <span class="text-gray-400 text-[10px] uppercase font-bold block">Target Class Session</span>
                        <div class="font-bold text-gray-900 text-sm" x-text="selectedReq.class_title"></div>
                        <div class="text-gray-500 font-medium" x-text="selectedReq.subject_name"></div>
                        <div class="text-gray-600 font-medium pt-1">
                            Original Schedule: <strong class="text-gray-800" x-text="selectedReq.original_time"></strong>
                        </div>
                        <template x-if="selectedReq.type === 'reschedule'">
                            <div class="text-blue-700 font-bold">
                                Proposed Reschedule Time: <span x-text="selectedReq.requested_time"></span>
                            </div>
                        </template>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl space-y-1">
                        <span class="text-gray-400 text-[10px] uppercase font-bold block">Teacher's Reason</span>
                        <p class="text-gray-800 font-medium italic" x-text="selectedReq.reason"></p>
                    </div>

                    <template x-if="selectedReq.admin_note">
                        <div class="p-3 bg-blue-50/50 border border-blue-100 rounded-xl space-y-1">
                            <span class="text-blue-600 text-[10px] uppercase font-bold block">Admin Note / Feedback</span>
                            <p class="text-blue-950 font-medium" x-text="selectedReq.admin_note"></p>
                        </div>
                    </template>
                </div>
            </template>

            <div class="pt-3 border-t border-gray-100 text-right">
                <button type="button" @click="viewModalOpen = false" class="py-2 px-5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-bold transition cursor-pointer">Close</button>
            </div>
        </div>
    </div>

</div>
