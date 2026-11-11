<x-admin-layout>
    <x-slot name="title">Institutional Attendance Hub - Olympia Scholars</x-slot>

    <div class="space-y-6">

        {{-- ── TOP TITLE & ACTION BAR ── --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs">
            <div>
                <h1 class="text-xl sm:text-2xl font-semibold text-gray-900 tracking-tight mt-1">Institutional Attendance Hub</h1>
                <p class="text-xs text-gray-500 font-normal mt-0.5">Audit student join events, verify teacher roll calls, and configure automated attendance policies.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.export.attendance', request()->query()) }}" class="px-4 py-2 bg-gray-900 hover:bg-gray-800 text-white font-medium text-xs rounded-xl transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Export CSV</span>
                </a>
                <a href="{{ route('admin.dashboard') }}" class="px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium text-xs rounded-xl transition flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Dashboard</span>
                </a>
            </div>
        </div>

        {{-- ── FLASH ALERTS ── --}}
        @if(session('success'))
            <div class="bg-gray-50 border border-gray-200 text-gray-800 px-4 py-3 rounded-2xl text-xs flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-2.5">
                    <span class="w-2 h-2 rounded-full bg-gray-500"></span>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-gray-400 hover:text-gray-700 text-sm font-normal cursor-pointer">×</button>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-gray-50 border border-gray-300 text-gray-900 px-4 py-3 rounded-2xl text-xs flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-2.5">
                    <span class="w-2 h-2 rounded-full bg-gray-800"></span>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-gray-400 hover:text-gray-700 text-sm font-normal cursor-pointer">×</button>
            </div>
        @endif
        {{-- ── 2. PENDING CORRECTION REQUESTS ALERT ── --}}
        @if($pendingCorrections->isNotEmpty())
            <div class="bg-gray-50 border border-gray-200/80 rounded-2xl p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                        <h4 class="text-xs font-semibold text-gray-800 uppercase tracking-wider">Student Attendance Appeals ({{ $pendingCorrections->count() }} Pending Review)</h4>
                    </div>
                    <span class="text-[11px] text-gray-500 font-normal">Requires instructor review in Teacher Workspace</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($pendingCorrections as $pc)
                        <div class="p-3 bg-white rounded-xl border border-gray-200/80 shadow-2xs space-y-1">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-medium text-gray-900">{{ $pc->student->user->name ?? 'Student' }}</span>
                                <span class="text-[10px] text-gray-700 font-medium uppercase px-2 py-0.5 rounded bg-gray-100 border border-gray-200">{{ $pc->requested_status }}</span>
                            </div>
                            <p class="text-[11px] text-gray-600 truncate font-normal">{{ $pc->liveClass->title ?? 'Session' }}</p>
                            <p class="text-[10px] text-gray-400 italic truncate font-normal">"{{ $pc->reason }}"</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── 3. FILTER BAR ── --}}
        <div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-xs">
            <form method="GET" action="{{ route('admin.attendance') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
                <div>
                    <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wider mb-1">Start Date</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full text-xs p-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:border-slate-400 font-normal">
                </div>

                <div>
                    <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wider mb-1">End Date</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full text-xs p-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:border-slate-400 font-normal">
                </div>

                <div>
                    <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wider mb-1">Course</label>
                    <select name="course_id" class="w-full text-xs px-3.5 py-2.5 pr-9 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:border-slate-400 font-normal cursor-pointer">
                        <option value="">All Courses</option>
                        @foreach($courses as $c)
                            <option value="{{ $c->id }}" {{ $courseId == $c->id ? 'selected' : '' }}>
                                {{ $c->subject->display_name ?? $c->subject->name ?? 'Course' }}: {{ Str::limit($c->title, 20) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wider mb-1">Teacher</label>
                    <select name="teacher_id" class="w-full text-xs px-3.5 py-2.5 pr-9 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:border-slate-400 font-normal cursor-pointer">
                        <option value="">All Teachers</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}" {{ $teacherId == $t->id ? 'selected' : '' }}>
                                {{ $t->user->name ?? 'Teacher' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wider mb-1">Status</label>
                    <select name="status" class="w-full text-xs px-3.5 py-2.5 pr-9 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:border-slate-400 font-normal cursor-pointer">
                        <option value="">All Statuses</option>
                        <option value="attended" {{ $status == 'attended' ? 'selected' : '' }}>Present</option>
                        <option value="late" {{ $status == 'late' ? 'selected' : '' }}>Late</option>
                        <option value="absent" {{ $status == 'absent' ? 'selected' : '' }}>Absent</option>
                        <option value="excused" {{ $status == 'excused' ? 'selected' : '' }}>Excused</option>
                        <option value="pending_verification" {{ $status == 'pending_verification' ? 'selected' : '' }}>Pending Audit</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2.5 px-4 bg-gray-900 hover:bg-gray-800 text-white font-medium text-xs rounded-xl transition shadow-xs cursor-pointer">
                        Filter
                    </button>
                    <a href="{{ route('admin.attendance') }}" class="py-2.5 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium text-xs rounded-xl transition cursor-pointer">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- ── 4. AUDIT TRAIL DATA TABLE ── --}}
        <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <div>
                    <h3 class="font-semibold text-base text-gray-900">Session Attendance Records</h3>
                    <p class="text-xs text-gray-500 font-normal">Showing page {{ $attendances->currentPage() }} of {{ $attendances->lastPage() }} ({{ $attendances->total() }} total)</p>
                </div>
            </div>

            <div class="overflow-x-auto admin-scrollbar">
                <table class="w-full text-left text-xs text-gray-600">
                    <thead class="bg-gray-50/50 text-[10px] uppercase font-medium text-gray-400 tracking-wider border-b border-gray-100">
                        <tr>
                            <th class="py-3 px-4">Student</th>
                            <th class="py-3 px-4">Class / Course</th>
                            <th class="py-3 px-4">Scheduled</th>
                            <th class="py-3 px-4">Joined At</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Verification</th>
                            <th class="py-3 px-4">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($attendances as $att)
                            <tr class="hover:bg-gray-50/60 transition">
                                {{-- Student Info --}}
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-full bg-gray-100 text-gray-700 font-medium text-[11px] flex items-center justify-center shrink-0 border border-gray-200/60">
                                            {{ substr($att->student?->user?->name ?? 'S', 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 leading-tight">{{ $att->student?->user?->name ?? 'Student #' . $att->student_id }}</p>
                                            <p class="text-[10px] text-gray-400 font-mono">{{ $att->student?->user?->email ?? '' }}</p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Class Info --}}
                                <td class="py-3.5 px-4">
                                    <p class="font-medium text-gray-800 leading-tight">{{ $att->liveClass?->title ?? 'Session' }}</p>
                                    <p class="text-[10px] text-gray-500 font-normal">{{ $att->liveClass?->course?->subject?->name ?? 'General' }}</p>
                                </td>

                                {{-- Scheduled At --}}
                                <td class="py-3.5 px-4 text-gray-600 whitespace-nowrap font-normal">
                                    {{ $att->liveClass?->scheduled_at ? $att->liveClass->scheduled_at->format('d M Y, g:i A') : '—' }}
                                </td>

                                {{-- Joined At --}}
                                <td class="py-3.5 px-4 whitespace-nowrap font-normal">
                                    @if($att->joined_at)
                                        <span class="text-gray-800 font-medium">{{ $att->joined_at->format('g:i:s A') }}</span>
                                    @else
                                        <span class="text-gray-400 italic">Not joined</span>
                                    @endif
                                </td>

                                {{-- Status Badge --}}
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-gray-100 text-gray-700 border border-gray-200 capitalize">
                                        @if($att->status === 'attended')
                                            Present
                                        @elseif($att->status === 'late')
                                            Late
                                        @elseif($att->status === 'absent')
                                            Absent
                                        @elseif($att->status === 'excused')
                                            Excused
                                        @else
                                            Pending
                                        @endif
                                    </span>
                                </td>

                                {{-- Verifier --}}
                                <td class="py-3.5 px-4 whitespace-nowrap font-normal">
                                    @if($att->verified_at)
                                        <p class="font-medium text-gray-800 leading-tight">{{ $att->verifier?->name ?? 'Teacher' }}</p>
                                        <p class="text-[10px] text-gray-400">{{ $att->verified_at->format('d M, g:i A') }}</p>
                                    @else
                                        <span class="text-[10px] text-gray-400 italic">Unverified</span>
                                    @endif
                                </td>

                                {{-- Remarks --}}
                                <td class="py-3.5 px-4 text-gray-500 max-w-[180px] truncate font-normal" title="{{ $att->remarks }}">
                                    {{ $att->remarks ?: '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-gray-400 font-normal">
                                    No attendance records found matching your filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($attendances->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $attendances->links() }}
                </div>
            @endif
        </div>

        {{-- ── 5. INSTITUTIONAL POLICY SETTINGS ── --}}
        <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-6">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-5">
                <div>
                    <h3 class="font-semibold text-base text-gray-900">Institutional Attendance Policies</h3>
                    <p class="text-xs text-gray-500 font-normal">Configure global parameters governing student check-ins and calculation rules.</p>
                </div>
                <span class="text-[10px] font-medium text-gray-600 bg-gray-100 border border-gray-200 px-2.5 py-0.5 rounded">Admin Policy</span>
            </div>

            <form action="{{ route('admin.settings.attendance') }}" method="POST" class="space-y-4 max-w-2xl">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Late Threshold (Minutes)</label>
                        <input type="number" name="late_threshold_minutes" min="1" max="120" value="{{ $lateThreshold }}" required
                               class="w-full text-xs p-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:border-slate-400 focus:bg-white text-gray-900 font-normal">
                        <p class="text-[10px] text-gray-400 mt-1 font-normal">Students joining after this many minutes from scheduled start are flagged as Late.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Early Join Window (Minutes)</label>
                        <input type="number" name="early_join_window_minutes" min="0" max="120" value="{{ $earlyJoinWindow }}" required
                               class="w-full text-xs p-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:border-slate-400 focus:bg-white text-gray-900 font-normal">
                        <p class="text-[10px] text-gray-400 mt-1 font-normal">Window prior to scheduled class time when students can click Join Class.</p>
                    </div>
                </div>

                <div class="pt-2">
                    <label class="flex items-start gap-3 p-3 bg-gray-50 rounded-xl border border-gray-200 cursor-pointer">
                        <input type="checkbox" name="excused_in_denominator" value="1" {{ $excusedInDenominator ? 'checked' : '' }} class="mt-0.5 rounded text-gray-900 focus:ring-0">
                        <div>
                            <span class="font-medium text-xs text-gray-800 block">Include Excused Absences in Attendance Denominator</span>
                            <span class="text-[11px] text-gray-500 font-normal">If unchecked, officially excused absences are omitted from both the numerator and denominator so the student's rate is not penalized.</span>
                        </div>
                    </label>
                </div>

                <div class="pt-3">
                    <button type="submit" class="py-2.5 px-6 bg-gray-900 hover:bg-gray-800 text-white font-medium text-xs rounded-xl shadow-xs transition cursor-pointer">
                        Update Attendance Policies
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-admin-layout>
