<x-teacher-layout>
    <x-slot name="title">Teacher Dashboard</x-slot>

    @php
        // Same visual language as Tasks & Assessments: white cards, solid
        // icon tiles on the KPIs, neutral rows, colour only where it means something.
        $firstName = \Illuminate\Support\Str::title(strtolower(explode(' ', auth()->user()->name ?? 'Teacher')[0]));
        $recentAssignments = $teacher->courses->flatMap(fn($c) => $c->assignments)->sortByDesc('updated_at')->take(3);
        $initialsOf = fn ($name) => collect(explode(' ', trim((string) $name)))->filter()->map(fn($w) => strtoupper($w[0]))->take(2)->join('');
    @endphp

    <div class="space-y-6">

        {{-- ── HEADER SECTION ── --}}
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="font-heading font-black text-2xl text-gray-900">Welcome back, {{ $firstName }}</h1>
                <p class="text-xs text-gray-500 font-medium mt-0.5">{{ now()->format('l, d F Y') }} • Manage your classes, learning materials and student progress in one place.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <a href="{{ route('teacher.classes') }}" class="py-2.5 px-5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl text-xs font-extrabold transition-all inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Schedule Class</span>
                </a>
                <a href="{{ route('teacher.tasks') }}" class="py-2.5 px-5 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl text-xs font-extrabold shadow-sm transition-all inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Create New Task</span>
                </a>
            </div>
        </div>

        {{-- ── 1. KPI SUMMARY CARDS ── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            {{-- Classes Today --}}
            <a href="{{ route('teacher.classes') }}" class="bg-white rounded-3xl p-4 border border-gray-100 shadow-sm flex items-center gap-3 hover:shadow-md transition-all">
                <div class="w-10 h-10 rounded-2xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-blue-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="text-xl font-black text-gray-900">{{ count($todaysClasses ?? []) }}</p>
                    <p class="text-[11px] font-bold text-gray-400">Classes Today</p>
                    <p class="text-[10px] text-gray-500 font-medium">Scheduled for today</p>
                </div>
            </a>

            {{-- Pending Reviews --}}
            <a href="{{ route('teacher.tasks') }}" class="bg-white rounded-3xl p-4 border border-gray-100 shadow-sm flex items-center gap-3 hover:shadow-md transition-all">
                <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-amber-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </div>
                <div>
                    <p class="text-xl font-black {{ ($pendingReviewCount ?? 0) > 0 ? 'text-amber-600' : 'text-gray-900' }}">{{ $pendingReviewCount ?? 0 }}</p>
                    <p class="text-[11px] font-bold text-gray-400">Pending Reviews</p>
                    <p class="text-[10px] text-gray-500 font-medium">Submissions to mark</p>
                </div>
            </a>

            {{-- Total Students --}}
            <a href="{{ route('teacher.students') }}" class="bg-white rounded-3xl p-4 border border-gray-100 shadow-sm flex items-center gap-3 hover:shadow-md transition-all">
                <div class="w-10 h-10 rounded-2xl bg-slate-900 text-white flex items-center justify-center shrink-0 shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <div>
                    <p class="text-xl font-black text-gray-900">{{ $totalStudents ?? 0 }}</p>
                    <p class="text-[11px] font-bold text-gray-400">Total Students</p>
                    <p class="text-[10px] text-gray-500 font-medium">Across your classes</p>
                </div>
            </a>

            {{-- Attendance Rate --}}
            <a href="{{ route('teacher.monitoring') }}" class="bg-white rounded-3xl p-4 border border-gray-100 shadow-sm flex items-center gap-3 hover:shadow-md transition-all">
                <div class="w-10 h-10 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-emerald-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <p class="text-xl font-black text-emerald-600">{{ $attStats['pct'] ?? 0 }}%</p>
                    <p class="text-[11px] font-bold text-gray-400">Attendance Rate</p>
                    <p class="text-[10px] text-gray-500 font-medium">{{ $attStats['attended'] ?? 0 }} / {{ $attStats['total'] ?? 0 }} this month</p>
                </div>
            </a>
        </div>

        {{-- ── 2. MIDDLE ROW ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Today's Classes --}}
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
                    <div>
                        <h3 class="font-heading font-extrabold text-base text-gray-900">Today's Classes</h3>
                        <p class="text-[11px] text-gray-400 font-medium">Running and upcoming sessions</p>
                    </div>
                    <a href="{{ route('teacher.classes') }}" class="py-1.5 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-extrabold transition-colors shrink-0">View All</a>
                </div>

                <div class="divide-y divide-gray-100 text-xs">
                    @forelse($dashboardClasses ?? [] as $item)
                    <div x-data="{
                            canJoin: {{ $item['can_join'] ? 'true' : 'false' }},
                            isLive: {{ $item['is_live'] ? 'true' : 'false' }},
                            isStartingSoon: {{ $item['is_starting_soon'] ? 'true' : 'false' }},
                            joinWindowMs: {{ $item['join_window_ms'] ?? 0 }},
                            startMs: {{ $item['start_ms'] ?? 0 }},
                            endMs: {{ $item['end_ms'] ?? 0 }},
                            isPast: false
                        }"
                        x-init="
                            if (endMs > 0) {
                                let timer = setInterval(() => {
                                    let now = Date.now();
                                    if (now >= endMs) {
                                        canJoin = false;
                                        isLive = false;
                                        isStartingSoon = false;
                                        isPast = true;
                                        clearInterval(timer);
                                    } else if (now >= startMs) {
                                        canJoin = true;
                                        isLive = true;
                                        isStartingSoon = false;
                                    } else if (now >= joinWindowMs) {
                                        canJoin = true;
                                        isLive = false;
                                        isStartingSoon = true;
                                    } else {
                                        canJoin = false;
                                        isLive = false;
                                        isStartingSoon = false;
                                    }
                                }, 1000);
                            }
                        "
                        x-show="!isPast"
                        class="px-6 py-3.5 flex items-center justify-between gap-3 hover:bg-gray-50/80 transition-colors"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-gray-100 text-gray-700 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.882v6.236a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-bold text-gray-900 truncate">{{ $item['title'] }}</h4>
                                    <template x-if="isLive">
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800">
                                            <span class="relative flex h-1.5 w-1.5">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                                            </span>
                                            Live
                                        </span>
                                    </template>
                                    <template x-if="isStartingSoon && !isLive">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider bg-amber-100 text-amber-800">
                                            {{ $item['badge_text'] ?? 'Starts Soon' }}
                                        </span>
                                    </template>
                                </div>
                                <p class="text-[11px] text-gray-400 font-normal truncate mt-0.5">{{ $item['subtitle'] }}</p>
                            </div>
                        </div>

                        <div class="shrink-0">
                            <template x-if="canJoin">
                                <a href="{{ $item['course_id'] ? route('teacher.classes.show', $item['course_id']) : $item['meeting_url'] }}"
                                   class="inline-flex items-center gap-1.5 py-1.5 px-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-extrabold text-xs shadow-sm transition-all">
                                    <span>Join class</span>
                                </a>
                            </template>
                            <template x-if="!canJoin">
                                <span class="font-semibold text-gray-700">{{ $item['schedule_text'] }}</span>
                            </template>
                        </div>
                    </div>
                    @empty
                    <div class="px-6 py-8 text-center space-y-3">
                        <p class="text-gray-400 font-semibold">No running or upcoming classes today.</p>
                        <a href="{{ route('teacher.classes') }}" class="inline-flex items-center gap-1.5 py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl text-xs font-extrabold shadow-sm transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            <span>Schedule Live Class</span>
                        </a>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Pending Reviews --}}
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
                    <div>
                        <h3 class="font-heading font-extrabold text-base text-gray-900">Pending Reviews</h3>
                        <p class="text-[11px] text-gray-400 font-medium">Latest submissions waiting for marks</p>
                    </div>
                    <a href="{{ route('teacher.tasks') }}" class="py-1.5 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-extrabold transition-colors shrink-0">View All</a>
                </div>

                <div class="divide-y divide-gray-100 text-xs">
                    @forelse($pendingReviews ?? [] as $sub)
                    <a href="{{ route('teacher.tasks') }}" class="px-6 py-3.5 flex items-center justify-between gap-3 hover:bg-gray-50/80 transition-colors">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-gray-100 text-gray-700 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-bold text-gray-900 truncate">{{ $sub->assignment->title ?? 'Assignment' }}</h4>
                                <p class="text-[11px] text-gray-400 font-normal truncate mt-0.5">by {{ $sub->student->user->name ?? 'Student' }}</p>
                            </div>
                        </div>
                        <span class="text-gray-500 font-medium shrink-0">{{ $sub->submitted_at?->diffForHumans() ?? '' }}</span>
                    </a>
                    @empty
                    <div class="px-6 py-8 text-center text-gray-400 font-semibold">No pending submissions to review.</div>
                    @endforelse
                </div>
            </div>

            {{-- Students Needing Attention --}}
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
                    <div>
                        <h3 class="font-heading font-extrabold text-base text-gray-900">Students Needing Attention</h3>
                        <p class="text-[11px] text-gray-400 font-medium">Low scores or attendance</p>
                    </div>
                    <a href="{{ route('teacher.monitoring') }}" class="py-1.5 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-extrabold transition-colors shrink-0">View All</a>
                </div>

                <div class="divide-y divide-gray-100 text-xs">
                    @forelse(array_slice($attentionStudents ?? [], 0, 4) as $st)
                    <a href="{{ route('teacher.monitoring') }}" class="px-6 py-3.5 flex items-center justify-between gap-3 hover:bg-gray-50/80 transition-colors">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-700 font-black text-xs flex items-center justify-center shrink-0">{{ $initialsOf($st['name']) }}</div>
                            <div class="min-w-0">
                                <h4 class="font-bold text-gray-900 truncate">{{ $st['name'] }}</h4>
                                <p class="text-[11px] text-gray-400 font-normal truncate mt-0.5">{{ $st['subject'] }} • {{ $st['class'] }}</p>
                            </div>
                        </div>
                        <span class="font-bold text-red-600 shrink-0">{{ $st['status'] }}</span>
                    </a>
                    @empty
                    <div class="px-6 py-8 text-center text-gray-400 font-semibold">All students are on track.</div>
                    @endforelse
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-heading font-extrabold text-base text-gray-900">Quick Actions</h3>
                    <p class="text-[11px] text-gray-400 font-medium">Shortcuts to everyday tasks</p>
                </div>

                <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <a href="{{ route('teacher.materials') }}" class="p-3 rounded-2xl border border-gray-100 hover:bg-gray-50 hover:shadow-sm flex items-center gap-3 transition-all">
                        <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-blue-500/20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        </div>
                        <div>
                            <p class="font-bold text-gray-900">Upload Notes</p>
                            <p class="text-[10px] text-gray-500 font-medium">Learning materials</p>
                        </div>
                    </a>

                    <a href="{{ route('teacher.tasks') }}" class="p-3 rounded-2xl border border-gray-100 hover:bg-gray-50 hover:shadow-sm flex items-center gap-3 transition-all">
                        <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-amber-500/20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <div>
                            <p class="font-bold text-gray-900">Create Assignment</p>
                            <p class="text-[10px] text-gray-500 font-medium">Tasks & assessments</p>
                        </div>
                    </a>

                    <a href="{{ route('teacher.monitoring') }}" class="p-3 rounded-2xl border border-gray-100 hover:bg-gray-50 hover:shadow-sm flex items-center gap-3 transition-all">
                        <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-emerald-500/20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <p class="font-bold text-gray-900">Mark Attendance</p>
                            <p class="text-[10px] text-gray-500 font-medium">Monitoring</p>
                        </div>
                    </a>

                    <a href="{{ route('teacher.students') }}" class="p-3 rounded-2xl border border-gray-100 hover:bg-gray-50 hover:shadow-sm flex items-center gap-3 transition-all">
                        <div class="w-9 h-9 rounded-xl bg-slate-900 text-white flex items-center justify-center shrink-0 shadow-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        </div>
                        <div>
                            <p class="font-bold text-gray-900">Add Feedback</p>
                            <p class="text-[10px] text-gray-500 font-medium">My students</p>
                        </div>
                    </a>
                </div>
            </div>

        </div>

        {{-- ── 3. BOTTOM ROW ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Recent Assignments --}}
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
                    <div>
                        <h3 class="font-heading font-extrabold text-base text-gray-900">Recent Assignments</h3>
                        <p class="text-[11px] text-gray-400 font-medium">Last updated across your classes</p>
                    </div>
                    <a href="{{ route('teacher.tasks') }}" class="py-1.5 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-extrabold transition-colors shrink-0">View All</a>
                </div>

                <div class="divide-y divide-gray-100 text-xs">
                    @forelse($recentAssignments as $asgn)
                    @php $isClosed = $asgn->due_date && $asgn->due_date->isPast(); @endphp
                    <div class="px-6 py-3.5 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-gray-100 text-gray-700 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-bold text-gray-900 truncate">{{ $asgn->title }}</h4>
                                <p class="text-[11px] text-gray-400 font-normal mt-0.5">Due {{ $asgn->due_date?->format('d M Y') ?? 'No due date' }}</p>
                            </div>
                        </div>
                        <span class="font-semibold shrink-0 {{ $isClosed ? 'text-gray-400' : 'text-gray-700' }}">{{ $isClosed ? 'Closed' : 'Active' }}</span>
                    </div>
                    @empty
                    <div class="px-6 py-8 text-center text-gray-400 font-semibold">No assignments created yet.</div>
                    @endforelse
                </div>
            </div>

            {{-- Catch-Up / Absent Students --}}
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
                    <div>
                        <h3 class="font-heading font-extrabold text-base text-gray-900">Catch-Up / Absent Students</h3>
                        <p class="text-[11px] text-gray-400 font-medium">Missed classes this month</p>
                    </div>
                    <a href="{{ route('teacher.monitoring') }}" class="py-1.5 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-extrabold transition-colors shrink-0">View All</a>
                </div>

                <div class="divide-y divide-gray-100 text-xs">
                    @forelse($catchUpStudents ?? [] as $abs)
                    @php $cName = $abs->student->user->name ?? 'Student'; @endphp
                    <div class="px-6 py-3.5 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-700 font-black text-xs flex items-center justify-center shrink-0">{{ $initialsOf($cName) }}</div>
                            <div class="min-w-0">
                                <h4 class="font-bold text-gray-900 truncate">{{ $cName }}</h4>
                                <p class="text-[11px] text-gray-400 font-normal truncate mt-0.5">Missed {{ $abs->liveClass->scheduled_at->format('d M Y') }} • {{ $abs->liveClass->course->subject->display_name ?? $abs->liveClass->course->subject->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <span class="font-semibold text-gray-700 shrink-0">Absent</span>
                    </div>
                    @empty
                    <div class="px-6 py-8 text-center text-gray-400 font-semibold">No absent students this month.</div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>
</x-teacher-layout>
