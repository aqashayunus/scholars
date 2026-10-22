<x-dashboard-layout>
    <x-slot name="title">Attendance & Learning Recovery</x-slot>

    <x-slot name="sidebar">
        @include('parent.partials.sidebar')
    </x-slot>

    <div class="space-y-6">

        {{-- ── HEADER ── --}}
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="font-heading font-black text-2xl text-gray-900">Attendance & Learning Recovery</h1>
                <p class="text-xs text-gray-500 font-medium mt-0.5">Monitor your children's attendance and catch up on missed classes.</p>
            </div>
        </div>

        {{-- ── 1. FAMILY OVERVIEW ── --}}
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-5">
            <div>
                <h3 class="font-heading font-black text-base text-gray-900">Family Overview</h3>
                <p class="text-xs text-gray-400 font-medium">Quick snapshot of your children – {{ now()->format('F Y') }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse ($childrenStats as $idx => $cs)
                    @php
                        $initials = collect(explode(' ', $cs['student']->user->name ?? 'S'))
                            ->map(fn($w) => strtoupper($w[0]))->take(2)->join('');
                        $bgColors = ['bg-blue-900','bg-pink-500','bg-amber-500','bg-teal-600','bg-purple-700'];
                        $bg = $bgColors[$idx % count($bgColors)];
                        $isFirst = $idx === 0;
                    @endphp
                    <div class="bg-white rounded-2xl p-4 border-{{ $isFirst ? '2 border-blue-600' : 'border-gray-100' }} shadow-sm space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full {{ $bg }} text-white font-black text-xs flex items-center justify-center shrink-0">
                                    {{ $initials }}
                                </div>
                                <div>
                                    <h4 class="text-xs font-black text-gray-900">{{ $cs['student']->user->name ?? 'Student' }}</h4>
                                    <p class="text-[10px] text-gray-400 font-semibold">{{ $cs['student']->level ?? 'N/A' }}</p>
                                </div>
                            </div>
                            @if ($isFirst)
                                <span class="w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold">✓</span>
                            @endif
                        </div>

                        <div class="grid grid-cols-3 gap-2 text-center text-xs pt-1 border-t border-gray-50">
                            <div>
                                <span class="text-[10px] text-gray-400 font-bold block">Progress</span>
                                <span class="font-black text-blue-600">{{ $cs['progressPct'] }}%</span>
                            </div>
                            <div class="border-x border-gray-100">
                                <span class="text-[10px] text-gray-400 font-bold block">Attendance</span>
                                <span class="font-black text-emerald-600">{{ $cs['attPct'] }}%</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-gray-400 font-bold block">Tasks</span>
                                <span class="font-black {{ $cs['pendingTasks'] > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                                    {{ $cs['pendingTasks'] > 0 ? $cs['pendingTasks'].' Pending' : 'All Done' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 py-8 text-center text-gray-400 text-sm font-medium">
                        No children linked to this account.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ── 2. ATTENDANCE STATS FOR FIRST CHILD ── --}}
        @if ($child)
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- LEFT: Stats (5 cols) --}}
            <div class="lg:col-span-5 space-y-6">
                <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-5">
                    <div class="flex items-center gap-3">
                        @php
                            $childInitials = collect(explode(' ', $child->user->name ?? 'S'))
                                ->map(fn($w) => strtoupper($w[0]))->take(2)->join('');
                        @endphp
                        <div class="w-9 h-9 rounded-full bg-blue-900 text-white font-black text-xs flex items-center justify-center shrink-0">
                            {{ $childInitials }}
                        </div>
                        <h3 class="font-heading font-black text-base text-gray-900">{{ $child->user->name ?? 'Student' }} – Attendance Summary</h3>
                    </div>

                    {{-- 4 KPI STATS --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3.5 bg-emerald-50/40 rounded-2xl border border-emerald-100/80">
                            <span class="text-[10px] font-extrabold text-gray-400 uppercase block">Attendance Rate</span>
                            <span class="text-2xl font-black text-emerald-600">{{ $attStats['pct'] }}%</span>
                            <span class="text-[10px] text-gray-400 font-semibold block mt-0.5">{{ now()->format('F Y') }}</span>
                        </div>

                        <div class="p-3.5 bg-blue-50/40 rounded-2xl border border-blue-100/80">
                            <span class="text-[10px] font-extrabold text-gray-400 uppercase block">Classes Attended</span>
                            <span class="text-2xl font-black text-blue-900">{{ $attStats['attended'] }}/{{ $attStats['total'] }}</span>
                            <span class="text-[10px] text-gray-400 font-semibold block mt-0.5">This month</span>
                        </div>

                        <div class="p-3.5 bg-red-50/40 rounded-2xl border border-red-100/80">
                            <span class="text-[10px] font-extrabold text-gray-400 uppercase block">Absent</span>
                            <span class="text-2xl font-black text-red-600">{{ $attStats['absent'] }}</span>
                            <span class="text-[10px] text-gray-400 font-semibold block mt-0.5">This month</span>
                        </div>

                        <div class="p-3.5 bg-amber-50/40 rounded-2xl border border-amber-100/80">
                            <span class="text-[10px] font-extrabold text-gray-400 uppercase block">Late</span>
                            <span class="text-2xl font-black text-amber-600">{{ $attStats['late'] }}</span>
                            <span class="text-[10px] text-gray-400 font-semibold block mt-0.5">This month</span>
                        </div>
                    </div>

                    {{-- ATTENDANCE LOG TABLE --}}
                    <div>
                        <h4 class="text-xs font-extrabold text-gray-900 mb-2">Attendance Records</h4>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="border-b border-gray-100 text-gray-400 font-extrabold uppercase text-[10px]">
                                        <th class="py-2">Date</th>
                                        <th class="py-2">Subject</th>
                                        <th class="py-2 text-right">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 font-medium">
                                    @forelse ($attendanceLog as $att)
                                        @php
                                            $statusColor = match($att->status) {
                                                'attended' => 'text-emerald-600',
                                                'late'     => 'text-amber-600',
                                                'absent'   => 'text-red-600',
                                                default    => 'text-gray-400',
                                            };
                                        @endphp
                                        <tr>
                                            <td class="py-2">{{ $att->liveClass->scheduled_at->format('j M') }}</td>
                                            <td class="py-2">{{ $att->liveClass->course->subject->display_name ?? $att->liveClass->course->subject->name ?? 'N/A' }}</td>
                                            <td class="py-2 text-right font-black {{ $statusColor }}">{{ ucfirst($att->status) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="py-4 text-center text-gray-400">No records found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Absence alert & recovery tips (7 cols) --}}
            <div class="lg:col-span-7 space-y-6">

                {{-- Absence Alert --}}
                @if ($attStats['absent'] > 0)
                <div class="bg-amber-50/60 rounded-3xl p-6 border border-amber-200/70 shadow-sm space-y-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <h3 class="font-heading font-extrabold text-base text-gray-900">Missed Classes</h3>
                    </div>
                    <p class="text-xs text-amber-800 font-medium">
                        {{ $child->user->name ?? 'Your child' }} missed <strong>{{ $attStats['absent'] }}</strong>
                        class{{ $attStats['absent'] !== 1 ? 'es' : '' }} this month. Please review the recordings to catch up.
                    </p>

                    <div class="space-y-3">
                        @foreach ($attendanceLog->where('status', 'absent') as $att)
                            <div class="p-4 bg-white rounded-2xl border border-amber-200/60 flex items-center justify-between gap-3">
                                <div>
                                    <h4 class="text-xs font-extrabold text-gray-900">{{ $att->liveClass->course->subject->display_name ?? $att->liveClass->course->subject->name ?? 'N/A' }}</h4>
                                    <p class="text-[11px] text-gray-500 font-medium">{{ $att->liveClass->scheduled_at->format('l, j M Y • g:i A') }}</p>
                                </div>
                                @if ($att->liveClass->meeting_url)
                                    <a href="{{ $att->liveClass->meeting_url }}" target="_blank"
                                       class="py-2 px-4 bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-xs rounded-xl transition-colors shrink-0">
                                        Watch Recording
                                    </a>
                                @else
                                    <span class="py-2 px-4 bg-gray-100 text-gray-500 font-extrabold text-xs rounded-xl shrink-0">No Recording</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="bg-emerald-50/60 rounded-3xl p-6 border border-emerald-200/70 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-heading font-extrabold text-base text-gray-900">Perfect Attendance!</h3>
                        <p class="text-xs text-emerald-800 font-medium mt-0.5">
                            {{ $child->user->name ?? 'Your child' }} attended all classes this month. Keep it up!
                        </p>
                    </div>
                </div>
                @endif

                {{-- Recovery Tips --}}
                <div class="bg-blue-50/40 rounded-3xl p-6 border border-blue-100/70 shadow-sm space-y-4">
                    <h3 class="font-heading font-extrabold text-base text-gray-900">Learning Recovery Tips</h3>
                    <div class="space-y-3 text-xs">
                        <div class="flex items-start gap-3 p-3 bg-white rounded-2xl border border-blue-100/70">
                            <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 font-black text-sm">1</span>
                            <p class="text-gray-700 font-medium">Review the recorded class sessions available in Past Classes.</p>
                        </div>
                        <div class="flex items-start gap-3 p-3 bg-white rounded-2xl border border-blue-100/70">
                            <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 font-black text-sm">2</span>
                            <p class="text-gray-700 font-medium">Encourage your child to complete the practice quizzes for missed topics.</p>
                        </div>
                        <div class="flex items-start gap-3 p-3 bg-white rounded-2xl border border-blue-100/70">
                            <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 font-black text-sm">3</span>
                            <p class="text-gray-700 font-medium">Contact the teacher for additional support materials if needed.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        @else
            <div class="bg-white rounded-3xl p-10 border border-gray-100 shadow-sm text-center">
                <p class="text-gray-400 text-sm font-medium">No child linked to your account. Please contact the administrator.</p>
            </div>
        @endif

    </div>
</x-dashboard-layout>
