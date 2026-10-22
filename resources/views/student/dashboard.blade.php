<x-dashboard-layout>
    <x-slot name="title">Student Dashboard</x-slot>

    <x-slot name="sidebar">
        @include('student.partials.sidebar')
    </x-slot>

    <div x-data="{ activeModal: null }" class="space-y-6">

        @if($isRestricted ?? false)
            {{-- 🔒 PAYMENT RESTRICTION ALERT BANNER --}}
            <div class="bg-rose-50 border-2 border-rose-200 rounded-3xl p-6 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-5 relative overflow-hidden">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-rose-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-rose-600/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="font-heading font-black text-base sm:text-lg text-rose-950">Academic Access Restricted</h2>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-200 text-rose-800">
                                {{ $restrictionDetails['days_overdue'] ?? 1 }} Days Overdue
                            </span>
                        </div>
                        <p class="text-xs text-rose-800/90 font-medium mt-1 max-w-xl">
                            Your account is temporarily restricted due to an overdue payment of <strong class="font-mono font-bold text-rose-950">RM {{ number_format($restrictionDetails['outstanding_amount'] ?? 0, 2) }}</strong>. Please settle your invoice or inform your parent/guardian to restore full access.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2.5 shrink-0 w-full md:w-auto">
                    <a href="{{ route('student.fees') }}" class="flex-1 md:flex-none py-2.5 px-5 bg-rose-600 hover:bg-rose-700 text-white rounded-2xl text-xs font-black shadow-md shadow-rose-600/20 transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        <span>Settle Payment Now</span>
                    </a>
                </div>
            </div>
        @endif

        {{-- ── 1. PERSONALIZED WELCOME BANNER ── --}}
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
            <div class="space-y-1">
                <h1 class="font-heading font-black text-2xl sm:text-3xl text-gray-900">Welcome back, {{ explode(' ', auth()->user()->name ?? 'Muhammad')[0] }}</h1>
                <p class="text-xs sm:text-sm text-gray-500 font-medium">{{ $student->level ? $student->level . ' Student' : 'Student' }} • Olympian Scholars Learning Portal</p>
            </div>

            <div class="flex items-center gap-3 shrink-0 w-full sm:w-auto">
                <div class="py-3 px-4 rounded-2xl bg-amber-50 text-amber-700 font-extrabold text-xs flex items-center justify-center gap-1.5 border border-amber-200/60 shadow-sm shrink-0 whitespace-nowrap" title="{{ $studyStreakDays }}-Day Streak">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 3z"/></svg>
                    <span>{{ $studyStreakDays }}</span>
                </div>
                <a href="{{ route('student.tasks') }}" class="flex-1 sm:flex-none py-3 px-5 rounded-2xl bg-gray-100 hover:bg-gray-200 text-gray-800 font-extrabold text-xs flex items-center justify-center gap-2 transition-colors whitespace-nowrap">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Tasks
                </a>
            </div>
        </div>

        {{-- ── DIAGNOSTIC ASSESSMENT NOTICE ── --}}
        @php
            $pDiagnostics = isset($pendingDiagnostics) && $pendingDiagnostics->isNotEmpty() ? $pendingDiagnostics : (isset($pendingDiagnostic) && $pendingDiagnostic ? collect([$pendingDiagnostic]) : collect());
        @endphp
        @if($pDiagnostics->isNotEmpty())
            @if($pDiagnostics->count() === 1)
                @php $diagItem = $pDiagnostics->first(); @endphp
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white rounded-3xl p-5 sm:p-6 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-white/15 backdrop-blur-xs flex items-center justify-center text-white shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            @if($diagItem->assignment?->subject)
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white/20 text-white mb-1">
                                    {{ $diagItem->assignment->subject->display_name }}
                                </span>
                            @endif
                            <h2 class="font-heading font-black text-base sm:text-lg text-white">{{ $diagItem->assignment->title ?? 'Diagnostic Assessment' }}</h2>
                            <p class="text-xs text-blue-100 mt-0.5 font-medium">Please complete this diagnostic assessment to evaluate your academic level. Scores will be calculated immediately.</p>
                        </div>
                    </div>

                    <a href="{{ route('student.diagnostic.take', $diagItem->assignment_id) }}" class="shrink-0 w-full sm:w-auto py-2.5 px-5 bg-white text-blue-900 hover:bg-blue-50 rounded-2xl text-xs font-black shadow-md transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <span>Start Test Now</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            @else
                <div class="bg-gradient-to-br from-slate-900 to-indigo-950 text-white rounded-3xl p-5 sm:p-6 shadow-sm space-y-4 border border-indigo-900/50">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-white/10 pb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-indigo-500/20 text-indigo-300 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div>
                                <h2 class="font-heading font-black text-base sm:text-lg text-white">Subject Diagnostic Assessments</h2>
                                <p class="text-xs text-indigo-200/80 font-normal">Complete the diagnostic assessment for each of your enrolled subjects.</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 self-start sm:self-auto">
                            {{ $pDiagnostics->count() }} Assessments Pending
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                        @foreach($pDiagnostics as $diag)
                            <div class="bg-white/5 hover:bg-white/10 border border-white/10 rounded-2xl p-4 flex items-center justify-between gap-3 transition">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        @if($diag->assignment?->subject)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500/30 text-indigo-200 border border-indigo-400/20">
                                                {{ $diag->assignment->subject->name }}
                                            </span>
                                        @endif
                                        <span class="text-[10px] text-gray-400 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            {{ $diag->assignment?->subject?->diagnostic_duration ?? ($diag->assignment?->time_limit_minutes ?: 20) }} mins
                                        </span>
                                    </div>
                                    <h3 class="font-heading font-bold text-sm text-white truncate" title="{{ $diag->assignment->title }}">{{ $diag->assignment->title }}</h3>
                                    <p class="text-[11px] text-gray-400 mt-0.5">{{ $diag->assignment->total_questions ?? 0 }} Questions</p>
                                </div>
                                <a href="{{ route('student.diagnostic.take', $diag->assignment_id) }}" class="shrink-0 px-4 py-2 bg-indigo-500 hover:bg-indigo-400 text-white rounded-xl text-xs font-bold transition flex items-center gap-1 shadow-sm cursor-pointer">
                                    <span>Start</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif

        {{-- ── 2. TOP ROW: 3 KEY PERFORMANCE CARDS ── --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            
            {{-- Overall Progress --}}
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-all">
                <div class="relative w-16 h-16 shrink-0 flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-blue-100" stroke-width="3.5" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 1 1 0 31.831 a 15.9155 15.9155 0 1 1 0 -31.831" />
                        <path class="text-blue-600" stroke-dasharray="{{ $overallProgress }}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 1 1 0 31.831 a 15.9155 15.9155 0 1 1 0 -31.831" />
                    </svg>
                    <span class="absolute text-xs font-black text-blue-950">{{ $overallProgress }}%</span>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-gray-900 text-sm">Overall Progress</h3>
                    <p class="text-2xl font-black text-blue-900">{{ $overallProgress }}%</p>
                    <p class="text-xs text-gray-500 font-medium">Overall Completion</p>
                </div>
            </div>

            {{-- Attendance --}}
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-all">
                <div class="relative w-16 h-16 shrink-0 flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-emerald-100" stroke-width="3.5" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 1 1 0 31.831 a 15.9155 15.9155 0 1 1 0 -31.831" />
                        <path class="text-emerald-500" stroke-dasharray="{{ $attendanceRate }}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 1 1 0 31.831 a 15.9155 15.9155 0 1 1 0 -31.831" />
                    </svg>
                    <span class="absolute text-xs font-black text-emerald-900">{{ $attendanceRate }}%</span>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-gray-900 text-sm">Attendance</h3>
                    <p class="text-2xl font-black text-emerald-600">{{ $attendanceRate }}%</p>
                    <p class="text-xs text-gray-500 font-medium">Attended</p>
                </div>
            </div>

            {{-- Tasks Due --}}
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-all cursor-pointer" onclick="window.location.href='{{ route('student.tasks') }}'">
                <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-200/50">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-gray-900 text-sm">Tasks Due</h3>
                    <p class="text-2xl font-black text-amber-600">{{ $tasksDueCount }}</p>
                    <p class="text-xs text-gray-500 font-medium">Pending Completion</p>
                </div>
            </div>
        </div>

        {{-- ── 3. MIDDLE BANNER: NEXT LIVE CLASS HERO ── --}}
        @if($upcomingClass)
            @php
                $isLiveNow = now()->gte($upcomingClass->scheduled_at) && now()->lte($upcomingClass->scheduled_at->copy()->addMinutes($upcomingClass->duration_minutes ?? 60));
                $isStartingSoon = !$isLiveNow && now()->lt($upcomingClass->scheduled_at) && now()->diffInMinutes($upcomingClass->scheduled_at) <= 10;
                $minsLeft = now()->diffInMinutes($upcomingClass->scheduled_at);
            @endphp
            <div class="bg-white rounded-3xl p-6 sm:p-8 border {{ $isLiveNow ? 'border-red-400 ring-2 ring-red-400/30' : ($isStartingSoon ? 'border-amber-400 ring-2 ring-amber-400/30' : 'border-amber-200/80') }} shadow-md relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-5 w-full md:w-auto">
                    <div class="w-16 h-16 rounded-2xl {{ $isLiveNow ? 'bg-red-600 animate-pulse' : 'bg-primary-950' }} text-white flex items-center justify-center shrink-0 shadow-lg">
                        <svg class="w-8 h-8 {{ $isLiveNow ? 'text-white' : 'text-amber-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.882v6.236a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs font-extrabold uppercase tracking-wider text-slate-400">Next Live Class</span>
                            @if($isLiveNow)
                                <span class="px-2.5 py-0.5 rounded-full bg-red-100 text-red-700 text-[10px] font-black uppercase flex items-center gap-1 shadow-xs">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-600 animate-ping"></span> LIVE NOW
                                </span>
                            @elseif($isStartingSoon)
                                <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-black uppercase flex items-center gap-1 shadow-xs">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse"></span> STARTING IN {{ max(1, $minsLeft) }}M
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 text-[10px] font-black uppercase">
                                    UPCOMING
                                </span>
                            @endif
                        </div>
                        <h2 class="font-heading font-black text-2xl sm:text-3xl text-gray-900 leading-tight">
                            {{ $upcomingClass->title }}
                        </h2>
                        <p class="text-xs sm:text-sm text-gray-500 font-medium mt-1 flex flex-wrap items-center gap-2">
                            <span class="font-semibold text-gray-700">Teacher: {{ $upcomingClass->course->teacher->user->name ?? 'Instructor' }}</span>
                            <span>•</span>
                            <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>{{ $upcomingClass->scheduled_at ? ($upcomingClass->scheduled_at->isToday() ? 'Today, ' : '') . $upcomingClass->scheduled_at->format('d M Y') : 'Scheduled' }}</span>
                            <span>•</span>
                            <span>{{ $upcomingClass->scheduled_at ? $upcomingClass->scheduled_at->format('g:i A') : '' }} ({{ $upcomingClass->duration_minutes ?? 60 }} min)</span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 shrink-0 w-full md:w-auto">
                    <button @click="activeModal = 'upcoming-class'" class="py-3 px-5 rounded-2xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-extrabold text-xs transition-colors cursor-pointer">
                        Class Details
                    </button>
                    <a href="{{ $upcomingClass->meeting_url ?? '#' }}" {{ $upcomingClass->meeting_url ? 'target="_blank"' : '' }}
                       class="inline-flex items-center justify-center gap-2.5 {{ $isLiveNow ? 'bg-red-600 hover:bg-red-700 shadow-red-500/25' : 'bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 shadow-amber-500/25' }} text-white font-heading font-extrabold text-xs px-7 py-3.5 rounded-2xl shadow-lg transition-all transform hover:-translate-y-0.5 cursor-pointer">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 10l4.553-2.069A1 1 0 0121 8.882v6.236a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <span>{{ $isLiveNow ? 'Join Live Now' : 'Join Class' }}</span>
                    </a>
                </div>
            </div>
        @else
            {{-- Fallback when no live class is scheduled (Blue Theme) --}}
            <div class="bg-gradient-to-br from-blue-700 via-indigo-700 to-indigo-900 rounded-3xl p-6 sm:p-8 shadow-md relative overflow-hidden flex flex-col sm:flex-row items-center justify-between gap-6 text-white border border-blue-500/20">
                <div class="absolute -right-16 -top-16 w-48 h-48 rounded-full bg-blue-500/20 blur-2xl"></div>
                <div class="absolute -left-16 -bottom-16 w-48 h-48 rounded-full bg-indigo-500/20 blur-2xl"></div>

                <div class="flex items-center gap-4 relative z-10">
                    <div class="w-12 h-12 rounded-2xl bg-white/10 text-white flex items-center justify-center shrink-0 border border-white/10 backdrop-blur-xs">
                        <svg class="w-6 h-6 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.882v6.236a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-heading font-black text-lg text-white">No Live Classes Scheduled Today</h3>
                        <p class="text-xs text-blue-100/90 font-medium">Check your weekly schedule or explore recordings in My Learning.</p>
                    </div>
                </div>
                <a href="{{ route('student.classes') }}" class="py-2.5 px-6 rounded-full bg-white/15 hover:bg-white/25 text-white font-extrabold text-xs transition-all shrink-0 border border-white/20 relative z-10 backdrop-blur-xs">
                    View Schedule →
                </a>
            </div>
        @endif

        {{-- ── 4. STREAMLINED 2-COLUMN CORE GRID ── --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            
            {{-- Column 1: Continue Learning --}}
            <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            </div>
                            <h3 class="font-heading font-bold text-gray-900 text-base">Continue Learning</h3>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @if($enrollments->count() > 0)
                            @foreach($enrollments->take(3) as $e)
                                @php
                                    $pct = $e->progress_pct ?? 0;
                                    $topic = $topicMasteries->where('course_id', $e->course_id)->first()->topic_name ?? 'Chapter ' . min(12, max(1, ($e->completed_lessons + 1)));
                                @endphp
                                <div class="p-3 bg-gray-50 rounded-2xl border border-gray-100 hover:border-blue-200 transition-all cursor-pointer" @click="window.location.href='{{ route('student.learning') }}'">
                                    <div class="flex justify-between items-center text-xs font-bold mb-1.5">
                                        <span class="text-gray-900">{{ $e->course->title ?? 'Course' }}</span>
                                        <span class="text-blue-600 font-black">{{ $pct }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                        <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span class="text-[10px] text-gray-400 font-semibold mt-1 block">Topic: {{ $topic }}</span>
                                </div>
                            @endforeach
                        @else
                            <div class="p-6 text-center text-gray-400 text-xs bg-gray-50 rounded-2xl border border-gray-100">
                                <p>No enrolled courses yet.</p>
                                <a href="{{ route('student.courses') }}" class="text-blue-600 font-bold hover:underline mt-1 inline-block">Browse Available Courses →</a>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-50 mt-6">
                    <a href="{{ route('student.learning') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 inline-flex items-center gap-1">
                        View All Subjects <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>

            {{-- Column 2: Today's Tasks --}}
            <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            <h3 class="font-heading font-bold text-gray-900 text-base">Today's Tasks</h3>
                        </div>
                        <span class="w-6 h-6 rounded-full bg-amber-100 text-amber-800 text-xs font-black flex items-center justify-center">{{ $assignments->where('status', 'pending')->count() ?: $assignments->count() }}</span>
                    </div>

                    <ul class="space-y-3.5 text-xs text-gray-700 font-medium">
                        @if($assignments->count() > 0)
                            @foreach($assignments->take(3) as $sa)
                                @if(!$sa->assignment) @continue @endif
                                @php
                                    $isPending = $sa->status === 'pending';
                                    $badgeBg = $isPending ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800';
                                    $cardBg = $isPending ? 'bg-amber-50/50 border-amber-100 hover:border-amber-300' : 'bg-gray-50 border-gray-100 hover:border-gray-200';
                                    $dueText = $sa->assignment?->due_date ? 'Due ' . \Carbon\Carbon::parse($sa->assignment->due_date)->format('D, d M') : 'Assignment Task';
                                @endphp
                                <li class="p-3 {{ $cardBg }} rounded-2xl border cursor-pointer transition-all" @click="window.location.href='{{ route('student.tasks') }}'">
                                    <div class="flex justify-between items-start">
                                        <span class="font-bold text-gray-900">{{ $sa->assignment?->title ?? 'Homework Task' }}</span>
                                        <span class="px-2 py-0.5 rounded-md {{ $badgeBg }} font-black text-[10px] uppercase">{{ ucfirst($sa->status ?? 'Pending') }}</span>
                                    </div>
                                    <span class="text-gray-400 text-[11px] block mt-1">{{ $dueText }} • {{ $sa->assignment?->course?->subject?->name ?? ($sa->assignment?->type === 'Diagnostic Test' ? 'Diagnostic Test' : 'Course') }}</span>
                                </li>
                            @endforeach
                        @else
                            <li class="p-6 text-center text-gray-400 text-xs bg-gray-50 rounded-2xl border border-gray-100">
                                <p>All caught up! No pending tasks due today.</p>
                            </li>
                        @endif
                    </ul>
                </div>

                <div class="pt-6 border-t border-gray-50 mt-6">
                    <a href="{{ route('student.tasks') }}" class="text-xs font-bold text-amber-600 hover:text-amber-800 inline-flex items-center gap-1">
                        View All Tasks <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>


        </div>

        {{-- ── 5. MINI WEEKLY SCHEDULE STRIP ── --}}
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-heading font-extrabold text-base text-gray-900">Weekly Schedule at a Glance</h3>
                    <p class="text-xs text-gray-500 font-medium">Your live class timetable for the next 7 days</p>
                </div>
                <a href="{{ route('student.classes') }}" class="text-xs font-extrabold text-blue-600 hover:underline">Full Schedule →</a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3 text-center">
                @foreach($weekSchedule as $day)
                    @php
                        $hasClass = $day['classes']->count() > 0;
                        $firstClass = $day['classes']->first();
                    @endphp
                    @if($day['isToday'])
                        {{-- Today's Highlighted Card --}}
                        <div class="p-3 bg-blue-600 text-white rounded-2xl shadow-md ring-2 ring-blue-400/50 relative overflow-hidden flex flex-col justify-between min-h-[95px]">
                            <div>
                                <span class="text-[10px] font-black text-blue-200 uppercase tracking-widest block">{{ $day['dayName'] }}</span>
                                <span class="text-xs font-black text-white block mt-0.5">{{ $day['dayNumber'] }}</span>
                            </div>
                            @if($hasClass)
                                <div class="mt-2">
                                    <span class="text-[10px] font-black text-white block bg-blue-700/90 rounded-lg py-1 px-1 truncate" title="{{ $firstClass->title }}">
                                        {{ $firstClass->course->subject->display_name ?? $firstClass->course->subject->name ?? 'Class' }} {{ $firstClass->scheduled_at->format('g A') }}
                                    </span>
                                </div>
                            @else
                                <span class="text-[10px] font-medium text-blue-200 mt-2 block">No Class</span>
                            @endif
                        </div>
                    @elseif($hasClass)
                        {{-- Day with Live Class --}}
                        <div class="p-3 bg-blue-50/70 rounded-2xl border border-blue-200/80 flex flex-col justify-between min-h-[95px]">
                            <div>
                                <span class="text-[10px] font-black text-blue-600 uppercase tracking-widest block">{{ $day['dayName'] }}</span>
                                <span class="text-xs font-black text-gray-900 block mt-0.5">{{ $day['dayNumber'] }}</span>
                            </div>
                            <div class="mt-2">
                                <span class="text-[10px] font-bold text-blue-800 block bg-blue-100 rounded-lg py-1 px-1 truncate" title="{{ $firstClass->title }}">
                                    {{ $firstClass->course->subject->display_name ?? $firstClass->course->subject->name ?? 'Class' }} {{ $firstClass->scheduled_at->format('g A') }}
                                </span>
                            </div>
                        </div>
                    @else
                        {{-- Empty Day --}}
                        <div class="p-3 bg-gray-50 rounded-2xl border border-gray-100 flex flex-col justify-between min-h-[95px]">
                            <div>
                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest block">{{ $day['dayName'] }}</span>
                                <span class="text-xs font-black text-gray-900 block mt-0.5">{{ $day['dayNumber'] }}</span>
                            </div>
                            <span class="text-[10px] font-semibold text-gray-400 mt-2 block">No Class</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- ── 6. ALPINE MODALS LAYER ── --}}

        {{-- Upcoming Class Modal --}}
        <div x-show="activeModal === 'upcoming-class'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="activeModal = null" class="bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl space-y-4 relative">
                <button @click="activeModal = null" class="absolute top-5 right-5 text-gray-400 hover:text-gray-600 p-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.882v6.236a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="font-heading font-black text-xl text-gray-900">Live Class Details</h3>
                @if($upcomingClass)
                    <div class="space-y-2.5 text-sm text-gray-600">
                        <p><strong class="text-gray-900">Title:</strong> {{ $upcomingClass->title }}</p>
                        <p><strong class="text-gray-900">Course:</strong> {{ $upcomingClass->course->title ?? 'SPM Curriculum' }} ({{ $upcomingClass->course->subject->display_name ?? $upcomingClass->course->subject->name ?? 'General' }})</p>
                        <p><strong class="text-gray-900">Teacher:</strong> {{ $upcomingClass->course->teacher->user->name ?? 'Instructor' }}</p>
                        <p><strong class="text-gray-900">Date & Time:</strong> {{ $upcomingClass->scheduled_at ? $upcomingClass->scheduled_at->format('l, d F Y, g:i A') . ' (' . ($upcomingClass->duration_minutes ?? 60) . ' min)' : 'Scheduled' }}</p>
                        <p><strong class="text-gray-900">Platform:</strong> Olympian Interactive Live Room</p>
                    </div>
                    <div class="pt-4 flex gap-3">
                        <a href="{{ $upcomingClass->meeting_url ?? '#' }}" {{ $upcomingClass->meeting_url ? 'target="_blank"' : '' }} class="btn-primary w-full justify-center py-3">Join Class Now</a>
                    </div>
                @else
                    <div class="space-y-2.5 text-sm text-gray-600 py-2">
                        <p class="text-gray-500">No live class is currently scheduled for your enrolled subjects.</p>
                    </div>
                    <div class="pt-4 flex gap-3">
                        <a href="{{ route('student.classes') }}" class="btn-primary w-full justify-center py-3">View Class Timetable</a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Assignment Details Modal --}}
        <div x-show="activeModal === 'assignment-details'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="activeModal = null" class="bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl space-y-4 relative">
                <button @click="activeModal = null" class="absolute top-5 right-5 text-gray-400 hover:text-gray-600 p-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <h3 class="font-heading font-black text-xl text-gray-900">Assignment: Math Practice Chapter 3</h3>
                <div class="space-y-2 text-sm text-gray-600">
                    <p><strong class="text-gray-900">Due Date:</strong> 12 Aug 2026, 11:59 PM</p>
                    <p><strong class="text-gray-900">Total Marks:</strong> 20 marks</p>
                    <p><strong class="text-gray-900">Instructions:</strong> Solve questions 1 to 5 in your exercise book and upload your answers in PDF format.</p>
                </div>
                <div class="pt-4 flex gap-3">
                    <a href="{{ route('student.tasks') }}" class="btn-primary w-full justify-center py-3">Submit Assignment</a>
                </div>
            </div>
        </div>

        {{-- Quiz Result Modal --}}
        <div x-show="activeModal === 'assessment-result'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="activeModal = null" class="bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl space-y-4 relative">
                <button @click="activeModal = null" class="absolute top-5 right-5 text-gray-400 hover:text-gray-600 p-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="font-heading font-black text-xl text-gray-900">Quiz Result: Force & Motion</h3>
                <div class="space-y-2 text-sm text-gray-600">
                    <p><strong class="text-gray-900">Score:</strong> <span class="font-extrabold text-emerald-600 text-base">85% (17/20)</span></p>
                    <p><strong class="text-gray-900">Completed On:</strong> 28 July 2026</p>
                    <p><strong class="text-gray-900">Status:</strong> Passed • Grade A</p>
                </div>
                <div class="pt-4 flex gap-3">
                    <a href="{{ route('student.tasks') }}" class="btn-primary w-full justify-center py-3">View Detailed Breakdown</a>
                </div>
            </div>
        </div>

        {{-- Teacher Feedback Modal --}}
        <div x-show="activeModal === 'teacher-feedback'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="activeModal = null" class="bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl space-y-4 relative">
                <button @click="activeModal = null" class="absolute top-5 right-5 text-gray-400 hover:text-gray-600 p-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <h3 class="font-heading font-black text-xl text-gray-900">Teacher Feedback: Science Essay</h3>
                <div class="p-4 bg-purple-50/50 rounded-2xl border border-purple-100 text-sm text-purple-900 italic">
                    "Good idea! The use of scientific facts is very clear. Structure of the essay introduction and conclusion can be further improved."
                </div>
                <p class="text-xs text-gray-500">— Teacher Siti Rahimah, 02 Aug 2026</p>
                <div class="pt-4 flex gap-3">
                    <a href="{{ route('student.progress') }}" class="btn-primary w-full justify-center py-3">View Progress & Performance</a>
                </div>
            </div>
        </div>

    </div>
</x-dashboard-layout>
