<x-admin-layout>
    <x-slot name="title">Institutional Attendance Hub - Olympia Scholars</x-slot>

    <div class="space-y-6">

        {{-- ── TOP TITLE & ACTION BAR ── --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs">
            <div>
                <h1 class="text-xl sm:text-2xl font-semibold text-gray-900 tracking-tight mt-1">Institutional Attendance Hub</h1>
                <p class="text-xs text-gray-400 font-normal mt-0.5">Audit student join events, verify teacher roll calls, and configure automated attendance policies.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.export.attendance', request()->query()) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-xs rounded-xl transition flex items-center gap-1.5 shadow-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Export CSV</span>
                </a>
                <a href="{{ route('admin.dashboard') }}" class="px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium text-xs rounded-xl transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Dashboard</span>
                </a>
            </div>
        </div>

        {{-- ── FLASH ALERTS ── --}}
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl text-xs flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-2.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 text-sm font-normal">×</button>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl text-xs flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-2.5">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800 text-sm font-normal">×</button>
            </div>
        @endif

        {{-- ── 1. KPI SUMMARY CARDS ── --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">
            <div class="admin-kpi-card">
                <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Total Records</span>
                <span class="text-2xl font-bold text-gray-900 my-1">{{ number_format($totalRecords) }}</span>
                <span class="text-[10px] text-gray-500 font-medium">Logged Sessions</span>
            </div>

            <div class="admin-kpi-card border-l-4 border-l-emerald-500">
                <span class="text-[10px] font-semibold text-emerald-600 uppercase tracking-wider">Present (On Time)</span>
                <span class="text-2xl font-bold text-emerald-700 my-1">{{ number_format($attendedCount) }}</span>
                <span class="text-[10px] text-gray-500 font-medium">{{ $totalRecords > 0 ? round(($attendedCount / $totalRecords) * 100, 1) : 0 }}% of total</span>
            </div>

            <div class="admin-kpi-card border-l-4 border-l-amber-500">
                <span class="text-[10px] font-semibold text-amber-600 uppercase tracking-wider">Late Arrivals</span>
                <span class="text-2xl font-bold text-amber-700 my-1">{{ number_format($lateCount) }}</span>
                <span class="text-[10px] text-gray-500 font-medium">> {{ $lateThreshold }}m threshold</span>
            </div>

            <div class="admin-kpi-card border-l-4 border-l-rose-500">
                <span class="text-[10px] font-semibold text-rose-600 uppercase tracking-wider">Absent</span>
                <span class="text-2xl font-bold text-rose-700 my-1">{{ number_format($absentCount) }}</span>
                <span class="text-[10px] text-gray-500 font-medium">Unexcused</span>
            </div>

            <div class="admin-kpi-card border-l-4 border-l-blue-500">
                <span class="text-[10px] font-semibold text-blue-600 uppercase tracking-wider">Excused</span>
                <span class="text-2xl font-bold text-blue-700 my-1">{{ number_format($excusedCount) }}</span>
                <span class="text-[10px] text-gray-500 font-medium">Medical / Official</span>
            </div>

            <div class="admin-kpi-card border-l-4 border-l-slate-400">
                <span class="text-[10px] font-semibold text-slate-600 uppercase tracking-wider">Pending Audit</span>
                <span class="text-2xl font-bold text-slate-800 my-1">{{ number_format($pendingCount) }}</span>
                <span class="text-[10px] text-gray-500 font-medium">Needs Verification</span>
            </div>
        </div>

        {{-- ── 2. PENDING CORRECTION REQUESTS ALERT ── --}}
        @if($pendingCorrections->isNotEmpty())
            <div class="bg-amber-50/70 border border-amber-200/80 rounded-2xl p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                        <h4 class="text-xs font-bold text-amber-900 uppercase tracking-wider">Student Attendance Appeals ({{ $pendingCorrections->count() }} Pending Review)</h4>
                    </div>
                    <span class="text-[10px] text-amber-700 font-medium">Requires instructor review in Teacher Workspace</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($pendingCorrections as $pc)
                        <div class="p-3 bg-white rounded-xl border border-amber-200 shadow-2xs space-y-1">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-gray-900">{{ $pc->student->user->name ?? 'Student' }}</span>
                                <span class="text-[10px] text-amber-600 font-semibold uppercase">{{ $pc->requested_status }}</span>
                            </div>
                            <p class="text-[11px] text-gray-600 truncate">{{ $pc->liveClass->title ?? 'Session' }}</p>
                            <p class="text-[10px] text-gray-500 italic truncate">"{{ $pc->reason }}"</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── 3. FILTER BAR ── --}}
        <div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-xs">
            <form method="GET" action="{{ route('admin.attendance') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Start Date</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full text-xs p-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none font-medium">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">End Date</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full text-xs p-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none font-medium">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Course</label>
                    <select name="course_id" class="w-full text-xs px-3.5 py-2.5 pr-9 bg-gray-50 border border-gray-200 rounded-xl outline-none font-medium cursor-pointer">
                        <option value="">All Courses</option>
                        @foreach($courses as $c)
                            <option value="{{ $c->id }}" {{ $courseId == $c->id ? 'selected' : '' }}>
                                {{ $c->subject->display_name ?? $c->subject->name ?? 'Course' }}: {{ Str::limit($c->title, 20) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Teacher</label>
                    <select name="teacher_id" class="w-full text-xs px-3.5 py-2.5 pr-9 bg-gray-50 border border-gray-200 rounded-xl outline-none font-medium cursor-pointer">
                        <option value="">All Teachers</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}" {{ $teacherId == $t->id ? 'selected' : '' }}>
                                {{ $t->user->name ?? 'Teacher' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Status</label>
                    <select name="status" class="w-full text-xs px-3.5 py-2.5 pr-9 bg-gray-50 border border-gray-200 rounded-xl outline-none font-medium cursor-pointer">
                        <option value="">All Statuses</option>
                        <option value="attended" {{ $status == 'attended' ? 'selected' : '' }}>Present (Attended)</option>
                        <option value="late" {{ $status == 'late' ? 'selected' : '' }}>Late</option>
                        <option value="absent" {{ $status == 'absent' ? 'selected' : '' }}>Absent</option>
                        <option value="excused" {{ $status == 'excused' ? 'selected' : '' }}>Excused</option>
                        <option value="pending_verification" {{ $status == 'pending_verification' ? 'selected' : '' }}>Pending Verification</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl transition shadow-xs">
                        Filter
                    </button>
                    <a href="{{ route('admin.attendance') }}" class="py-2.5 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs rounded-xl transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- ── 4. AUDIT TRAIL DATA TABLE ── --}}
        <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-sm text-gray-900">Session Attendance Records</h3>
                    <p class="text-[11px] text-gray-400">Showing page {{ $attendances->currentPage() }} of {{ $attendances->lastPage() }} ({{ $attendances->total() }} total)</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-gray-600">
                    <thead class="bg-gray-50/80 text-[10px] uppercase font-bold text-gray-400 tracking-wider border-b border-gray-100">
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
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 font-bold text-[10px] flex items-center justify-center shrink-0">
                                            {{ substr($att->student?->user?->name ?? 'S', 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900 leading-tight">{{ $att->student?->user?->name ?? 'Student #' . $att->student_id }}</p>
                                            <p class="text-[10px] text-gray-400">{{ $att->student?->user?->email ?? '' }}</p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Class Info --}}
                                <td class="py-3 px-4">
                                    <p class="font-medium text-gray-800 leading-tight">{{ $att->liveClass?->title ?? 'Session' }}</p>
                                    <p class="text-[10px] text-blue-600 font-medium">{{ $att->liveClass?->course?->subject?->name ?? 'General' }}</p>
                                </td>

                                {{-- Scheduled At --}}
                                <td class="py-3 px-4 text-gray-500 whitespace-nowrap">
                                    {{ $att->liveClass?->scheduled_at ? $att->liveClass->scheduled_at->format('d M Y, g:i A') : '—' }}
                                </td>

                                {{-- Joined At --}}
                                <td class="py-3 px-4 whitespace-nowrap">
                                    @if($att->joined_at)
                                        <span class="text-emerald-700 font-semibold">{{ $att->joined_at->format('g:i:s A') }}</span>
                                    @else
                                        <span class="text-gray-400 italic">Not joined</span>
                                    @endif
                                </td>

                                {{-- Status Badge --}}
                                <td class="py-3 px-4 whitespace-nowrap">
                                    @if($att->status === 'attended')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Present</span>
                                    @elseif($att->status === 'late')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Late</span>
                                    @elseif($att->status === 'absent')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Absent</span>
                                    @elseif($att->status === 'excused')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">Excused</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">Pending</span>
                                    @endif
                                </td>

                                {{-- Verifier --}}
                                <td class="py-3 px-4 whitespace-nowrap">
                                    @if($att->verified_at)
                                        <p class="font-medium text-gray-800 leading-tight">{{ $att->verifier?->name ?? 'Teacher' }}</p>
                                        <p class="text-[10px] text-gray-400">{{ $att->verified_at->format('d M, g:i A') }}</p>
                                    @else
                                        <span class="text-[10px] text-gray-400 italic">Unverified</span>
                                    @endif
                                </td>

                                {{-- Remarks --}}
                                <td class="py-3 px-4 text-gray-500 max-w-[180px] truncate" title="{{ $att->remarks }}">
                                    {{ $att->remarks ?: '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-gray-400 font-medium">
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
                    <h3 class="font-bold text-base text-gray-900">Institutional Attendance Policies</h3>
                    <p class="text-xs text-gray-400">Configure global parameters governing student check-ins and calculation rules.</p>
                </div>
                <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg">ADMIN POLICY</span>
            </div>

            <form action="{{ route('admin.settings.attendance') }}" method="POST" class="space-y-4 max-w-2xl">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Late Threshold (Minutes)</label>
                        <input type="number" name="late_threshold_minutes" min="1" max="120" value="{{ $lateThreshold }}" required
                               class="w-full text-xs p-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 font-semibold">
                        <p class="text-[10px] text-gray-400 mt-1">Students joining after this many minutes from scheduled start are flagged as Late.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Early Join Window (Minutes)</label>
                        <input type="number" name="early_join_window_minutes" min="0" max="120" value="{{ $earlyJoinWindow }}" required
                               class="w-full text-xs p-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 font-semibold">
                        <p class="text-[10px] text-gray-400 mt-1">Window prior to scheduled class time when students can click Join Class.</p>
                    </div>
                </div>

                <div class="pt-2">
                    <label class="flex items-start gap-3 p-3 bg-gray-50 rounded-xl border border-gray-200 cursor-pointer">
                        <input type="checkbox" name="excused_in_denominator" value="1" {{ $excusedInDenominator ? 'checked' : '' }} class="mt-0.5 rounded text-blue-600">
                        <div>
                            <span class="font-bold text-xs text-gray-800 block">Include Excused Absences in Attendance Denominator</span>
                            <span class="text-[11px] text-gray-500">If unchecked, officially excused absences are omitted from both the numerator and denominator so the student's rate is not penalized.</span>
                        </div>
                    </label>
                </div>

                <div class="pt-3">
                    <button type="submit" class="py-2.5 px-6 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                        Update Attendance Policies
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-admin-layout>
