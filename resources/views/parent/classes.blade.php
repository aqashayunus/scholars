<x-dashboard-layout>
    <x-slot name="title">Parent Classes</x-slot>

    <x-slot name="sidebar">
        @include('parent.partials.sidebar')
    </x-slot>

    <div x-data="{ activeTab: 'upcoming', showModal: false, selectedClass: null }" class="space-y-6">

        {{-- ── HEADER BAR ── --}}
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="font-heading font-black text-2xl text-gray-900">Classes & Schedule</h1>
                <p class="text-xs text-gray-500 font-medium mt-0.5">{{ now()->format('l, j F Y') }}</p>
            </div>
            @if ($children->count() > 1)
                <div class="flex items-center gap-2 bg-gray-50 px-3.5 py-2.5 rounded-2xl border border-gray-200/70 w-full sm:w-auto min-w-0">
                    <span class="text-xs font-bold text-gray-500 shrink-0">Viewing:</span>
                    <select onchange="window.location.href='{{ route('parent.classes') }}?child_id=' + this.value" class="bg-transparent border-0 text-xs font-extrabold text-gray-900 focus:ring-0 cursor-pointer py-0 pl-1.5 pr-8 outline-none truncate w-full sm:max-w-[250px]">
                        @foreach ($children as $ch)
                            <option value="{{ $ch->id }}" {{ $child && $child->id === $ch->id ? 'selected' : '' }}>
                                {{ $ch->user->name ?? 'Student' }} – {{ $ch->level ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        {{-- ── 1. TOP 3 KPI CARDS ── --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            {{-- Next Class --}}
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Next Class</p>
                    @if ($nextClass)
                        <p class="text-sm font-black text-gray-900 mt-0.5">
                            {{ $nextClass->course->subject->display_name ?? $nextClass->course->subject->name ?? 'N/A' }} •
                            <span class="text-blue-600 font-extrabold">{{ $nextClass->scheduled_at->isToday() ? 'Today, ' : $nextClass->scheduled_at->format('j M, ') }}{{ $nextClass->scheduled_at->format('g:i A') }}</span>
                        </p>
                    @else
                        <p class="text-sm font-black text-gray-400 mt-0.5">No upcoming classes</p>
                    @endif
                </div>
            </div>

            {{-- Classes This Week --}}
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Classes This Week</p>
                    <p class="text-2xl font-black text-gray-900 mt-0.5">{{ $classesThisWeek }}</p>
                </div>
            </div>

            {{-- Attendance --}}
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Attendance</p>
                    <p class="text-2xl font-black text-gray-900 mt-0.5">{{ $attStats['pct'] }}%</p>
                    <p class="text-xs text-gray-400">{{ $attStats['attended'] }}/{{ $attStats['total'] }} this month</p>
                </div>
            </div>
        </div>

        {{-- ── 2. SUB-TABS SWITCHER ── --}}
        <div class="bg-white rounded-2xl p-1.5 border border-gray-100 shadow-sm flex flex-wrap gap-1">
            <button @click="activeTab = 'upcoming'"
                    :class="activeTab === 'upcoming' ? 'bg-blue-600 text-white shadow-sm font-extrabold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 font-bold'"
                    class="px-5 py-2.5 rounded-xl text-xs transition-all cursor-pointer">
                Upcoming
            </button>
            <button @click="activeTab = 'attendance'"
                    :class="activeTab === 'attendance' ? 'bg-blue-600 text-white shadow-sm font-extrabold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 font-bold'"
                    class="px-5 py-2.5 rounded-xl text-xs transition-all cursor-pointer">
                Attendance
            </button>
            <button @click="activeTab = 'past'"
                    :class="activeTab === 'past' ? 'bg-blue-600 text-white shadow-sm font-extrabold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 font-bold'"
                    class="px-5 py-2.5 rounded-xl text-xs transition-all cursor-pointer">
                Past Classes
            </button>
        </div>

        {{-- ── 3. UPCOMING TAB ── --}}
        <div x-show="activeTab === 'upcoming'" x-cloak class="space-y-4">
            @forelse ($upcomingClasses as $lc)
                @php
                    $isToday = $lc->scheduled_at->isToday();
                    $teacher = optional(optional($lc->course)->teacher)->user->name ?? null;
                @endphp
                <div class="bg-white rounded-3xl p-5 border {{ $isToday ? 'border-2 border-blue-300' : 'border-gray-100' }} shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 font-black text-sm border border-blue-100">
                            {{ $lc->scheduled_at->format('d') }}<br><span class="text-[9px] font-bold">{{ $lc->scheduled_at->format('M') }}</span>
                        </div>
                        <div>
                            <p class="text-sm font-black text-gray-900">{{ $lc->course?->subject?->display_name ?? $lc->title ?? ($lc->course?->subject?->name ?? 'N/A') }}</p>
                            @if ($teacher)
                                <p class="text-xs text-gray-500 font-medium">Teacher: <strong class="text-gray-700">{{ $teacher }}</strong></p>
                            @endif
                            <p class="text-xs text-gray-400 font-medium">{{ $lc->scheduled_at->format('D, j M Y • g:i A') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        @if ($isToday)
                            <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-700 font-extrabold text-[11px] border border-blue-100">Today</span>
                        @elseif ($lc->scheduled_at->isTomorrow())
                            <span class="px-3 py-1 rounded-full bg-amber-50 text-amber-700 font-extrabold text-[11px] border border-amber-100">Tomorrow</span>
                        @else
                            <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-600 font-extrabold text-[11px]">Upcoming</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-3xl p-10 border border-gray-100 shadow-sm text-center">
                    <p class="text-gray-400 text-sm font-medium">No upcoming classes scheduled.</p>
                </div>
            @endforelse
        </div>

        {{-- ── 4. ATTENDANCE TAB ── --}}
        <div x-show="activeTab === 'attendance'" x-cloak class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-heading font-extrabold text-lg text-gray-900">Attendance Log – {{ now()->format('F Y') }}</h3>
                <div class="flex items-center gap-4 text-xs font-bold">
                    <span class="text-emerald-600">✓ Attended: {{ $attStats['attended'] }}</span>
                    <span class="text-red-500">✗ Absent: {{ $attStats['absent'] }}</span>
                    <span class="text-blue-700 font-black">{{ $attStats['pct'] }}%</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-gray-200 text-gray-400 uppercase font-black">
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4">Subject</th>
                            <th class="py-3 px-4">Class</th>
                            <th class="py-3 px-4 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium">
                        @forelse ($attendanceLog as $att)
                            @php
                                $statusColor = match($att->status) {
                                    'attended' => 'text-emerald-600',
                                    'late'     => 'text-amber-600',
                                    'absent'   => 'text-red-600',
                                    default    => 'text-gray-500',
                                };
                            @endphp
                            <tr>
                                <td class="py-3 px-4">{{ $att->liveClass->scheduled_at->format('j M Y') }}</td>
                                <td class="py-3 px-4">{{ $att->liveClass->course->subject->display_name ?? $att->liveClass->course->subject->name ?? 'N/A' }}</td>
                                <td class="py-3 px-4">{{ $att->liveClass->title ?? '–' }}</td>
                                <td class="py-3 px-4 text-right font-black {{ $statusColor }}">{{ ucfirst($att->status) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-6 px-4 text-center text-gray-400">No attendance records this month.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── 5. PAST CLASSES TAB ── --}}
        <div x-show="activeTab === 'past'" x-cloak class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
            <h3 class="font-heading font-extrabold text-lg text-gray-900">Past Classes</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse ($pastClasses as $lc)
                    @php
                        $attended = $lc->attendances->where('student_id', optional($child)->id)->where('status', 'attended')->count();
                    @endphp
                    <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 flex items-center justify-between gap-3">
                        <div>
                            <h4 class="font-bold text-sm text-gray-900">{{ $lc->course?->subject?->display_name ?? $lc->title ?? ($lc->course?->subject?->name ?? 'N/A') }}</h4>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $lc->scheduled_at->format('j M Y') }} • {{ $lc->duration_minutes ?? '–' }} min</p>
                            <span class="text-[10px] font-black {{ $attended ? 'text-emerald-600' : 'text-red-500' }}">
                                {{ $attended ? '✓ Attended' : '✗ Absent' }}
                            </span>
                        </div>
                        @if ($lc->meeting_url)
                            <a href="{{ $lc->meeting_url }}" target="_blank" class="py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-xl transition-colors cursor-pointer whitespace-nowrap">Watch</a>
                        @else
                            <span class="py-2 px-4 bg-gray-200 text-gray-500 font-extrabold text-xs rounded-xl whitespace-nowrap">No Recording</span>
                        @endif
                    </div>
                @empty
                    <div class="col-span-2 py-8 text-center text-gray-400 text-sm">No past class records found.</div>
                @endforelse
            </div>
        </div>

    </div>
</x-dashboard-layout>
