<x-teacher-layout>
    <x-slot name="title">Teacher Dashboard</x-slot>

    <div class="space-y-6">



        {{-- ── WELCOME BANNER ── --}}
        <div>
            <h2 class="font-heading font-black text-2xl text-gray-900">Welcome back, {{ explode(' ', auth()->user()->name ?? 'Teacher')[0] }}</h2>
            <p class="text-xs text-gray-500 font-medium mt-0.5">Manage your classes, learning materials, and student progress all in one place.</p>
        </div>

        {{-- ── 1. TOP 4 KPI SUMMARY CARDS ── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            {{-- Classes Today --}}
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Classes Today</p>
                    <p class="text-2xl font-black text-blue-900 mt-0.5">{{ count($todaysClasses ?? []) }}</p>
                </div>
            </div>

            {{-- Pending Reviews --}}
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Pending Reviews</p>
                    <p class="text-2xl font-black text-amber-600 mt-0.5">{{ $pendingReviewCount ?? 0 }}</p>
                </div>
            </div>

            {{-- Students Need Attention --}}
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center shrink-0 border border-red-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Students</p>
                    <p class="text-2xl font-black text-red-600 mt-0.5">{{ $totalStudents ?? 0 }}</p>
                </div>
            </div>

            {{-- Attendance Rate --}}
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Attendance Rate</p>
                    <p class="text-2xl font-black text-emerald-600 mt-0.5">{{ $attStats['pct'] ?? 0 }}%</p>
                    <p class="text-[10px] text-gray-400 font-medium mt-0.5">{{ $attStats['attended'] ?? 0 }}/{{ $attStats['total'] ?? 0 }} this month</p>
                </div>
            </div>
        </div>

        {{-- ── 2. MIDDLE ROW (2 COLUMNS) ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- LEFT COLUMN: TODAY'S CLASSES & STUDENTS NEEDING ATTENTION --}}
            <div class="space-y-6">
                
                {{-- Today's Classes --}}
                <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <h3 class="font-heading font-extrabold text-base text-gray-900">Today's Classes</h3>
                        </div>
                        <a href="{{ route('teacher.classes') }}" class="text-xs font-extrabold text-blue-600 hover:underline">View All</a>
                    </div>

                    <div class="space-y-3">
                        @forelse($dashboardClasses ?? [] as $item)
                        @php
                            $subjectName = $item['subject_name'] ?? 'general';
                            $iconColor = 'bg-gray-500'; 
                            $icon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>';
                            
                            if (str_contains($subjectName, 'math')) { 
                                $iconColor = 'bg-blue-600'; 
                                $icon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>';
                            }
                            elseif (str_contains($subjectName, 'physics') || str_contains($subjectName, 'science')) { 
                                $iconColor = 'bg-purple-600'; 
                                $icon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L5.6 15.12a2 2 0 01-1.077-1.636l-.32-3.2A2 2 0 015.2 8.243l2.387.477a6 6 0 003.86-.517l.318-.158a6 6 0 013.86-.517l2.387.477a2 2 0 011.636 1.077l.32 3.2a2 2 0 01-1.066 2.052z"/></svg>';
                            }
                            elseif (str_contains($subjectName, 'chemistry')) { 
                                $iconColor = 'bg-slate-600'; 
                                $icon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>';
                            }
                            elseif (str_contains($subjectName, 'english')) { 
                                $iconColor = 'bg-emerald-500'; 
                                $icon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 3l8.5 9-8.5 9-8.5-9L12 3z"/></svg>';
                            }
                        @endphp
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
                            class="p-3 bg-gray-50 rounded-2xl border border-gray-100 flex items-center justify-between gap-3 transition-all hover:bg-gray-100/50"
                        >
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-lg {{ $iconColor }} text-white font-black text-xs flex items-center justify-center shrink-0">
                                    {!! $icon !!}
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-xs font-bold text-gray-900 truncate">{{ $item['title'] }}</h4>
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
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider bg-amber-100 text-amber-800">
                                                {{ $item['badge_text'] ?? 'Starts Soon' }}
                                            </span>
                                        </template>
                                    </div>
                                    <p class="text-[11px] text-gray-400 font-medium truncate">{{ $item['subtitle'] }}</p>
                                </div>
                            </div>

                            <div class="shrink-0 flex items-center gap-2">
                                <template x-if="canJoin">
                                    <a href="{{ $item['course_id'] ? route('teacher.classes.show', $item['course_id']) : $item['meeting_url'] }}" 
                                       class="inline-flex items-center gap-1.5 py-1.5 px-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs shadow-xs hover:shadow transition-all cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.882v6.236a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        <span>Join class</span>
                                    </a>
                                </template>
                                <template x-if="!canJoin">
                                    <span class="text-xs font-extrabold text-blue-600">{{ $item['schedule_text'] }}</span>
                                </template>
                            </div>
                        </div>
                        @empty
                        <div class="p-6 text-center bg-gray-50/70 rounded-2xl border border-dashed border-gray-200 space-y-2">
                            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-gray-800">No Running or Upcoming Classes</h4>
                                <p class="text-[11px] text-gray-400 font-medium">All sessions for today have concluded or no new classes are scheduled.</p>
                            </div>
                            <div class="pt-1">
                                <a href="{{ route('teacher.classes') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span>Schedule Live Class</span>
                                </a>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>

                {{-- Students Needing Attention --}}
                <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-xl bg-red-50 text-red-600 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <h3 class="font-heading font-extrabold text-base text-gray-900">Students Needing Attention</h3>
                        </div>
                        <a href="{{ route('teacher.monitoring') }}" class="text-xs font-extrabold text-blue-600 hover:underline">View All</a>
                    </div>

                    <div class="space-y-3 text-xs">
                        @forelse(array_slice($attentionStudents ?? [], 0, 4) as $st)
                        @php
                            $initials = collect(explode(' ', $st['name']))->map(fn($w) => strtoupper($w[0]))->take(2)->join('');
                            $bgColors = ['bg-red-100 text-red-700', 'bg-amber-100 text-amber-700', 'bg-purple-100 text-purple-700', 'bg-blue-100 text-blue-700'];
                            $bg = $bgColors[array_search($st, array_values($attentionStudents ?? [])) % count($bgColors)];
                        @endphp
                        <a href="{{ route('teacher.monitoring') }}" class="p-3 bg-gray-50 rounded-2xl border border-gray-100 flex items-center justify-between cursor-pointer hover:bg-gray-100/70 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full {{ $bg }} font-black text-xs flex items-center justify-center shrink-0">{{ $initials }}</div>
                                <div>
                                    <h4 class="font-extrabold text-gray-900">{{ $st['name'] }}</h4>
                                    <p class="text-[10px] text-gray-400 font-medium">{{ $st['subject'] }} • {{ $st['class'] }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-red-600">{{ $st['status'] }}</span>
                                <span class="text-gray-400 font-bold">›</span>
                            </div>
                        </a>
                        @empty
                        <div class="p-3 text-center text-xs text-emerald-600 font-bold bg-emerald-50 rounded-2xl">✓ All students are performing well!</div>
                        @endforelse
                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN: PENDING REVIEWS & QUICK ACTIONS --}}
            <div class="space-y-6">
                
                {{-- Pending Reviews --}}
                <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            <h3 class="font-heading font-extrabold text-base text-gray-900">Pending Reviews</h3>
                        </div>
                        <a href="{{ route('teacher.tasks') }}" class="text-xs font-extrabold text-blue-600 hover:underline">View All</a>
                    </div>

                    <div class="space-y-3 text-xs">
                        @forelse($pendingReviews ?? [] as $sub)
                        <a href="{{ route('teacher.tasks') }}" class="p-3 bg-gray-50 rounded-2xl border border-gray-100 flex items-center justify-between cursor-pointer hover:bg-gray-100/70 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 font-bold text-xs">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div>
                                    <h4 class="font-extrabold text-gray-900">{{ $sub->assignment->title ?? 'Assignment' }}</h4>
                                    <p class="text-[10px] text-gray-400 font-medium">by {{ $sub->student->user->name ?? 'Student' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-gray-500 font-medium">submitted {{ $sub->submitted_at?->diffForHumans() ?? '' }}</span>
                                <span class="text-gray-400 font-bold">›</span>
                            </div>
                        </a>
                        @empty
                        <div class="p-3 text-center text-xs text-gray-400 font-bold">No pending submissions to review.</div>
                        @endforelse
                    </div>
                </div>

                {{-- Quick Actions Grid --}}
                <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
                    <div class="flex items-center gap-2 border-b border-gray-100 pb-3">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <h3 class="font-heading font-extrabold text-base text-gray-900">Quick Actions</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs font-extrabold">
                        {{-- Upload Notes --}}
                        <a href="{{ route('teacher.materials') }}" class="p-3.5 bg-blue-50/60 hover:bg-blue-600 hover:text-white text-blue-700 rounded-2xl border border-blue-200/80 flex items-center gap-2.5 transition-all cursor-pointer shadow-2xs group">
                            <svg class="w-4 h-4 text-blue-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            <span>Upload Notes</span>
                        </a>

                        {{-- Create Assignment --}}
                        <a href="{{ route('teacher.tasks') }}" class="p-3.5 bg-amber-50/60 hover:bg-amber-500 hover:text-white text-amber-800 rounded-2xl border border-amber-200/80 flex items-center gap-2.5 transition-all cursor-pointer shadow-2xs group">
                            <svg class="w-4 h-4 text-amber-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Create Assignment</span>
                        </a>

                        {{-- Mark Attendance --}}
                        <a href="{{ route('teacher.monitoring') }}" class="p-3.5 bg-emerald-50/60 hover:bg-emerald-600 hover:text-white text-emerald-800 rounded-2xl border border-emerald-200/80 flex items-center gap-2.5 transition-all cursor-pointer shadow-2xs group">
                            <svg class="w-4 h-4 text-emerald-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Mark Attendance</span>
                        </a>

                        {{-- Add Feedback --}}
                        <a href="{{ route('teacher.students') }}" class="p-3.5 bg-purple-50/60 hover:bg-purple-600 hover:text-white text-purple-800 rounded-2xl border border-purple-200/80 flex items-center gap-2.5 transition-all cursor-pointer shadow-2xs group">
                            <svg class="w-4 h-4 text-purple-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                            <span>Add Feedback</span>
                        </a>
                    </div>
                </div>

            </div>

        </div>

        {{-- ── 3. BOTTOM ROW (2 COLUMNS) ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Recent Learning Materials --}}
            <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                        <h3 class="font-heading font-extrabold text-base text-gray-900">Recent Learning Materials</h3>
                    </div>
                    <a href="{{ route('teacher.materials') }}" class="text-xs font-extrabold text-blue-600 hover:underline">View All</a>
                </div>

                <div class="space-y-3 text-xs">
                    @php
                        $recentAssignments = $teacher->courses->flatMap(fn($c) => $c->assignments)->sortByDesc('updated_at')->take(3);
                    @endphp
                    @forelse($recentAssignments as $asgn)
                    <div class="p-3 bg-gray-50 rounded-2xl border border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 font-bold text-xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div>
                                <h4 class="font-extrabold text-gray-900">{{ $asgn->title }}</h4>
                                <p class="text-[11px] text-gray-400 font-medium">Due {{ $asgn->due_date?->format('d M Y') ?? 'No due date' }}</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full {{ $asgn->due_date && $asgn->due_date->isPast() ? 'bg-gray-100 text-gray-600' : 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' }} font-extrabold text-[10px]">{{ $asgn->due_date && $asgn->due_date->isPast() ? 'Closed' : 'Active' }}</span>
                    </div>
                    @empty
                    <div class="p-3 text-center text-xs text-gray-400 font-bold">No assignments created yet.</div>
                    @endforelse
                </div>
            </div>

            {{-- Catch-Up / Absent Students --}}
            <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <h3 class="font-heading font-extrabold text-base text-gray-900">Catch-Up / Absent Students</h3>
                    </div>
                    <a href="{{ route('teacher.monitoring') }}" class="text-xs font-extrabold text-blue-600 hover:underline">View All</a>
                </div>

                <div class="space-y-3 text-xs">
                    @forelse($catchUpStudents ?? [] as $abs)
                    @php
                        $cName = $abs->student->user->name ?? 'Student';
                        $cInit = collect(explode(' ', $cName))->map(fn($w) => strtoupper($w[0]))->take(2)->join('');
                    @endphp
                    <div class="p-3 bg-gray-50 rounded-2xl border border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 font-black text-xs flex items-center justify-center shrink-0">{{ $cInit }}</div>
                            <div>
                                <h4 class="font-extrabold text-gray-900">{{ $cName }}</h4>
                                <p class="text-[11px] text-gray-400 font-medium">Missed: {{ $abs->liveClass->scheduled_at->format('d M Y') }} ({{ $abs->liveClass->course->subject->display_name ?? $abs->liveClass->course->subject->name ?? 'N/A' }})</p>
                            </div>
                        </div>
                        <span class="py-1.5 px-3.5 bg-white text-blue-700 font-extrabold text-xs rounded-xl border border-blue-200/80">Absent</span>
                    </div>
                    @empty
                    <div class="p-3 text-center text-xs text-emerald-600 font-bold bg-emerald-50 rounded-2xl">✓ No absent students this month!</div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>
</x-teacher-layout>
