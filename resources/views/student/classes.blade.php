@php
    // All variables ($heroClass, $isLiveNow, $minutesUntilHero, $upcomingClasses,
    // $pastClasses, $attendanceMap) are pre-computed in StudentDashboardController::classes().
    $upcomingToRender = $upcomingClasses ?? collect();
    $pastToRender     = $pastClasses     ?? collect();
    $attendanceMap    = $attendanceMap   ?? collect();
    $heroClass        = $heroClass       ?? null;
    $isLiveNow        = $isLiveNow       ?? false;
    $minutesUntilHero = $minutesUntilHero ?? 0;

    // Display-only helpers (safe to derive in blade from already-resolved model)
    $heroStart = $heroClass ? $heroClass->scheduled_at : null;
    $heroEnd   = $heroClass ? $heroClass->scheduled_at->copy()->addMinutes($heroClass->duration_minutes ?: 60) : null;
@endphp
<x-dashboard-layout>
    <x-slot name="title">Classes</x-slot>
    <x-slot name="sidebar">
        @include('student.partials.sidebar')
    </x-slot>

    <div x-data="{ 
        tab: 'upcoming', 
        showDetailsModal: false, 
        selectedClass: '',
        showRescheduleAlert: true,
        checklist: {
            notes: true,
            practice: false,
            worksheet: false
        },
        showCorrectionModal: false,
        correctionClassId: null,
        correctionClassTitle: '',
        correctionReason: '',
        correctionStatus: 'attended',
        isSubmittingCorrection: false,
        correctionError: '',
        correctionSuccess: '',
        openCorrectionModal(classId, title) {
            this.correctionClassId = classId;
            this.correctionClassTitle = title;
            this.correctionReason = '';
            this.correctionStatus = 'attended';
            this.correctionError = '';
            this.correctionSuccess = '';
            this.showCorrectionModal = true;
        },
        async submitCorrection() {
            if (!this.correctionReason.trim()) {
                this.correctionError = 'Please provide a reason for the correction request.';
                return;
            }
            this.isSubmittingCorrection = true;
            this.correctionError = '';
            try {
                const res = await fetch('{{ route('student.attendance.correction.submit') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        live_class_id: this.correctionClassId,
                        reason: this.correctionReason,
                        requested_status: this.correctionStatus
                    })
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    this.correctionSuccess = 'Correction request submitted! Your teacher will review it.';
                    setTimeout(() => {
                        this.showCorrectionModal = false;
                        window.location.reload();
                    }, 1400);
                } else {
                    this.correctionError = data.message || 'Failed to submit request.';
                }
            } catch (err) {
                this.correctionError = 'Network error. Please try again.';
            } finally {
                this.isSubmittingCorrection = false;
            }
        }
    }" class="space-y-6">

        {{-- ── TOP HEADER ── --}}
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <h2 class="font-heading font-black text-2xl text-gray-900">My Classes</h2>
                <p class="text-xs text-gray-500 font-medium">Manage your live classes, timetable and recordings.</p>
            </div>
        </div>

        {{-- ── MAIN WORKSPACE CONTENT ── --}}
        <div class="space-y-8">

            {{-- TOP SUMMARY ROW: CURRENTLY LIVE CLASS & ATTENDANCE --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- 1. LIVE NOW HERO CARD (only shows when a class is actually running or starting soon) --}}
                @if($heroClass)
                    <div class="lg:col-span-2 bg-gradient-to-br from-blue-700 via-indigo-700 to-indigo-900 rounded-3xl p-6 sm:p-8 shadow-lg relative overflow-hidden text-white border border-blue-500/20 flex flex-col justify-center">
                        {{-- Decorative background glows --}}
                        <div class="absolute -right-16 -top-16 w-56 h-56 rounded-full bg-blue-500/20 blur-3xl pointer-events-none"></div>
                        <div class="absolute -left-16 -bottom-16 w-56 h-56 rounded-full bg-indigo-500/20 blur-3xl pointer-events-none"></div>

                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
                            {{-- Left side: Icon + Class Info --}}
                            <div class="flex items-center gap-5 min-w-0">
                                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-white/10 text-white flex items-center justify-center shrink-0 border border-white/15 backdrop-blur-xs">
                                    <svg class="w-7 h-7 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.882v6.236a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                </div>

                                <div class="space-y-1.5 min-w-0">
                                    <h3 class="font-heading font-black text-xl sm:text-2xl text-white tracking-tight leading-tight truncate">
                                        {{ $heroClass->title }}
                                    </h3>
                                    <p class="text-xs sm:text-sm text-white/80 font-medium truncate">
                                        Teacher: <span class="text-white font-bold">{{ $heroClass->course->teacher->user->name ?? 'Instructor' }}</span>
                                        @if(!empty($heroClass->course->title) || !empty($heroClass->course->subject))
                                            <span class="text-white/40 mx-1.5">•</span>
                                            <span class="text-white/90 font-semibold">{{ $heroClass->course->subject->display_name ?? $heroClass->course->title }}</span>
                                        @endif
                                    </p>
                                    <div class="flex items-center gap-x-2 text-xs text-white/70 font-medium">
                                        <span class="flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-white/60 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            {{ $heroStart->isToday() ? 'Today, ' : '' }}{{ $heroStart->format('d M Y') }}
                                        </span>
                                        <span class="text-white/40">•</span>
                                        <span class="flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-white/60 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            {{ $heroStart->format('g:i A') }} – {{ $heroEnd->format('g:i A') }} ({{ $heroClass->duration_minutes }} min)
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Right side: Actions --}}
                            <div class="shrink-0 flex flex-col sm:flex-row md:flex-col gap-2.5 w-full md:w-36 justify-center">
                                <form action="{{ route('student.classes.join', $heroClass->id) }}" method="POST" target="_blank" class="w-full">
                                    @csrf
                                    <button type="submit" class="w-full py-2.5 px-5 rounded-full text-xs font-bold tracking-wide justify-center shadow-md flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 border border-emerald-400/30 text-white transition-all transform hover:scale-[1.02] cursor-pointer">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.882v6.236a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        <span>{{ $isLiveNow ? 'Join Now' : 'Join Class' }}</span>
                                    </button>
                                </form>
                                <button @click="showDetailsModal = true; selectedClass = '{{ addslashes($heroClass->title) }}'" class="w-full py-2.5 px-5 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold text-xs shadow-xs transition-all backdrop-blur-xs cursor-pointer text-center">
                                    Class Details
                                </button>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- NO LIVE CLASS RUNNING --}}
                    <div class="lg:col-span-2 bg-gradient-to-br from-blue-700 via-indigo-700 to-indigo-900 rounded-3xl p-6 sm:p-8 shadow-lg relative overflow-hidden flex flex-col justify-center items-center text-center py-12 min-h-[220px] text-white border border-blue-500/20">
                        <div class="absolute -right-16 -top-16 w-48 h-48 rounded-full bg-blue-500/20 blur-2xl"></div>
                        <div class="absolute -left-16 -bottom-16 w-48 h-48 rounded-full bg-indigo-500/20 blur-2xl"></div>

                        <div class="w-14 h-14 rounded-2xl bg-white/10 text-white flex items-center justify-center mb-3 backdrop-blur-xs border border-white/10 relative z-10">
                            <svg class="w-7 h-7 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.882v6.236a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        </div>
                        <h3 class="font-heading font-black text-xl text-white relative z-10">No Live Class Running</h3>
                        <p class="text-xs text-blue-100/90 mt-1 max-w-sm font-medium relative z-10">No class is in session right now. Check your upcoming schedule below or explore study materials!</p>
                        <a href="{{ route('student.learning') }}" class="mt-4 px-6 py-2.5 rounded-full bg-white/15 hover:bg-white/25 text-white font-extrabold text-xs transition-all border border-white/20 relative z-10 backdrop-blur-xs">
                            Explore Study Materials →
                        </a>
                    </div>
                @endif

                {{-- THIS MONTH ATTENDANCE WIDGET --}}
                <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-6">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block mb-1">THIS MONTH ATTENDANCE</span>
                        <h4 class="font-heading font-black text-xl text-gray-900">
                            @if(($attendanceStats['past'] ?? 0) > 0)
                                {{ $attendanceStats['pct'] ?? 0 }}% Attendance
                            @else
                                No Classes Held Yet
                            @endif
                        </h4>
                        <div class="mt-3 space-y-1 text-xs text-gray-600 font-medium">
                            <p>Total This Month: <strong class="text-gray-900">{{ $attendanceStats['total'] ?? 0 }}</strong>
                            </p>
                            <p>Classes Held: <strong class="text-gray-900">{{ $attendanceStats['past'] ?? 0 }}</strong></p>
                            <p>Attended: <strong class="text-emerald-600">{{ $attendanceStats['attended'] ?? 0 }}</strong></p>
                            <p>Absent / Unmarked: <strong class="text-red-500">{{ $attendanceStats['absent'] ?? 0 }}</strong></p>
                        </div>
                        <a href="{{ route('student.progress') }}" class="inline-block mt-4 text-xs font-extrabold text-blue-600 hover:text-blue-800">
                            View Attendance →
                        </a>
                    </div>

                    {{-- Donut Graphic --}}
                    <div class="relative w-28 h-28 shrink-0 flex items-center justify-center">
                        <svg class="w-28 h-28" viewBox="0 0 36 36">
                            {{-- Base track --}}
                            <path class="text-gray-100" stroke-width="3.5" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 1 1 0 31.831 a 15.9155 15.9155 0 1 1 0 -31.831" />

                            @if(($attendanceStats['past'] ?? 0) > 0)
                                {{-- Attended Segment (Emerald Green) --}}
                                @if(($attendanceStats['attended_pct'] ?? 0) > 0)
                                    <path class="text-emerald-500 transition-all duration-500"
                                          stroke-dasharray="{{ $attendanceStats['attended_pct'] }}, 100"
                                          stroke-dashoffset="0"
                                          stroke-width="3.5"
                                          stroke="currentColor" fill="none"
                                          d="M18 2.0845 a 15.9155 15.9155 0 1 1 0 31.831 a 15.9155 15.9155 0 1 1 0 -31.831" />
                                @endif

                                {{-- Absent / Missed Segment (Rose Red) --}}
                                @if(($attendanceStats['absent_pct'] ?? 0) > 0)
                                    <path class="text-rose-400 transition-all duration-500"
                                          stroke-dasharray="{{ $attendanceStats['absent_pct'] }}, 100"
                                          stroke-dashoffset="-{{ $attendanceStats['attended_pct'] ?? 0 }}"
                                          stroke-width="3.5"
                                          stroke="currentColor" fill="none"
                                          d="M18 2.0845 a 15.9155 15.9155 0 1 1 0 31.831 a 15.9155 15.9155 0 1 1 0 -31.831" />
                                @endif
                            @endif
                        </svg>

                        {{-- Center Metric Text --}}
                        <div class="absolute flex flex-col items-center justify-center text-center">
                            @if(($attendanceStats['past'] ?? 0) > 0)
                                @php
                                    $ratePct = $attendanceStats['pct'] ?? 0;
                                    $rateColorClass = $ratePct >= 75 ? 'text-emerald-600' : ($ratePct >= 50 ? 'text-amber-600' : 'text-rose-600');
                                @endphp
                                <span class="text-base font-black {{ $rateColorClass }} leading-none">
                                    {{ $ratePct }}%
                                </span>
                                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-tight mt-0.5">Attended</span>
                            @else
                                <span class="text-base font-black text-gray-400 leading-none">—</span>
                                <span class="text-[8px] font-bold text-gray-400 uppercase tracking-tight mt-0.5">No classes</span>
                            @endif
                        </div>
                    </div>
                </div>

            </div>

            {{-- 2. OTHER UPCOMING CLASSES (CARD SLIDER) --}}
            <div x-data="{
                slideLeft() { this.$refs.slider.scrollBy({ left: -340, behavior: 'smooth' }) },
                slideRight() { this.$refs.slider.scrollBy({ left: 340, behavior: 'smooth' }) }
            }" class="space-y-4">
                <div class="flex items-center justify-between">
                    <h4 class="font-heading font-extrabold text-base text-gray-900">Other Upcoming Classes</h4>
                    @if($upcomingToRender->isNotEmpty())
                        <div class="flex items-center gap-2">
                            <button @click="slideLeft()" class="w-8 h-8 rounded-full border border-gray-200 hover:bg-gray-50 flex items-center justify-center text-gray-500 cursor-pointer transition-colors shadow-2xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <button @click="slideRight()" class="w-8 h-8 rounded-full border border-gray-200 hover:bg-gray-50 flex items-center justify-center text-gray-500 cursor-pointer transition-colors shadow-2xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    @endif
                </div>

                @if($upcomingToRender->isNotEmpty())
                    <div x-ref="slider" class="flex gap-4 overflow-x-auto no-scrollbar scroll-smooth snap-x snap-mandatory pb-4">
                        @foreach($upcomingToRender as $uc)
                            @php
                                $subjectName = $uc->course->subject->display_name ?? $uc->course->subject->name ?? 'General';
                                $teacherName = $uc->course->teacher->user->name ?? 'Instructor';
                                $scheduledDate = $uc->scheduled_at->format('D, d M Y');
                                $scheduledTime = $uc->scheduled_at->format('g:i A') . ' – ' . $uc->scheduled_at->copy()->addMinutes($uc->duration_minutes)->format('g:i A');
                            @endphp
                            <div class="w-80 shrink-0 bg-white rounded-3xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-all flex flex-col justify-between space-y-4 snap-start relative overflow-hidden">
                                <span class="absolute top-0 right-0 bg-blue-600 text-white font-heading font-black text-[9px] uppercase tracking-wider px-3.5 py-1.5 rounded-bl-2xl shadow-xs">Upcoming</span>
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 pr-14">
                                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.882v6.236a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        </div>
                                        <div class="min-w-0">
                                            <span class="text-[10px] font-black text-blue-600 tracking-wider uppercase block truncate">{{ $subjectName }}</span>
                                            <h5 class="font-heading font-black text-sm text-gray-900 mt-0.5 truncate leading-snug">{{ $uc->title }}</h5>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-2 border-t border-b border-gray-100 py-3 text-xs text-gray-600">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <span class="font-semibold text-gray-800 truncate">Teacher: {{ $teacherName }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span>{{ $scheduledDate }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>{{ $scheduledTime }} ({{ $uc->duration_minutes }} min)</span>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between gap-3">
                                    @if($uc->scheduled_at->copy()->subMinutes(15)->isPast())
                                        <form action="{{ route('student.classes.join', $uc->id) }}" method="POST" target="_blank" class="flex-1">
                                            @csrf
                                            <button type="submit" class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-xs transition-all cursor-pointer flex items-center justify-center gap-1.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.882v6.236a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                Join
                                            </button>
                                        </form>
                                    @endif
                                    <button @click="showDetailsModal = true; selectedClass = '{{ addslashes($uc->title) }}'" class="{{ $uc->scheduled_at->copy()->subMinutes(15)->isPast() ? 'flex-1' : 'w-full' }} py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs shadow-xs transition-all cursor-pointer">
                                        Class Details
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center bg-white rounded-3xl border border-gray-100 text-xs text-gray-400 font-medium">
                        No other upcoming live classes scheduled at the moment.
                    </div>
                @endif
            </div>

            {{-- 3. AFTER CLASS / PAST CLASSES (CARD SLIDER) --}}
            <div x-data="{
                slideLeft() { this.$refs.pastSlider.scrollBy({ left: -340, behavior: 'smooth' }) },
                slideRight() { this.$refs.pastSlider.scrollBy({ left: 340, behavior: 'smooth' }) }
            }" class="space-y-4">
                <div class="flex items-center justify-between">
                    <h4 class="font-heading font-extrabold text-base text-gray-900">Class Recording</h4>
                    @if($pastToRender->isNotEmpty())
                        <div class="flex items-center gap-2">
                            <button @click="slideLeft()" class="w-8 h-8 rounded-full border border-gray-200 hover:bg-gray-50 flex items-center justify-center text-gray-500 cursor-pointer transition-colors shadow-2xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <button @click="slideRight()" class="w-8 h-8 rounded-full border border-gray-200 hover:bg-gray-50 flex items-center justify-center text-gray-500 cursor-pointer transition-colors shadow-2xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    @endif
                </div>

                @if($pastToRender->isNotEmpty())
                    <div x-ref="pastSlider" class="flex gap-4 overflow-x-auto no-scrollbar scroll-smooth snap-x snap-mandatory pb-4">
                        @foreach($pastToRender as $pc)
                            @php
                                $statusVal = $attendanceMap[$pc->id] ?? null;
                                $teacherName = $pc->course->teacher->user->name ?? 'Instructor';
                                $subjectName = $pc->course->subject->display_name ?? $pc->course->subject->name ?? 'Course';
                                $scheduledDate = $pc->scheduled_at->format('D, d M Y');
                                $scheduledTime = $pc->scheduled_at->format('g:i A') . ' – ' . $pc->scheduled_at->copy()->addMinutes($pc->duration_minutes)->format('g:i A');
                                $existingCorrection = $correctionRequests->firstWhere('live_class_id', $pc->id);
                            @endphp
                            <div class="w-80 shrink-0 bg-white rounded-3xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-all flex flex-col justify-between space-y-4 snap-start relative overflow-hidden">
                                @if($statusVal === 'attended')
                                    <span class="absolute top-0 right-0 bg-emerald-50 text-emerald-700 font-heading font-black text-[9px] uppercase tracking-wider px-3.5 py-1.5 rounded-bl-2xl shadow-xs">Attended</span>
                                @elseif($statusVal === 'late')
                                    <span class="absolute top-0 right-0 bg-amber-50 text-amber-700 font-heading font-black text-[9px] uppercase tracking-wider px-3.5 py-1.5 rounded-bl-2xl shadow-xs">Attended (Late)</span>
                                @elseif($statusVal === 'excused')
                                    <span class="absolute top-0 right-0 bg-blue-50 text-blue-700 font-heading font-black text-[9px] uppercase tracking-wider px-3.5 py-1.5 rounded-bl-2xl shadow-xs">Excused</span>
                                @elseif($statusVal === 'absent')
                                    <span class="absolute top-0 right-0 bg-red-50 text-red-700 font-heading font-black text-[9px] uppercase tracking-wider px-3.5 py-1.5 rounded-bl-2xl shadow-xs">Absent</span>
                                @else
                                    <span class="absolute top-0 right-0 bg-slate-100 text-slate-600 font-heading font-black text-[9px] uppercase tracking-wider px-3.5 py-1.5 rounded-bl-2xl shadow-xs">Pending Verification</span>
                                @endif

                                <div class="flex items-center gap-3 pr-14">
                                    <div class="w-10 h-10 rounded-full {{ in_array($statusVal, ['attended', 'late']) ? 'bg-emerald-100 text-emerald-700' : ($statusVal === 'excused' ? 'bg-blue-100 text-blue-700' : ($statusVal === 'absent' ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-600')) }} flex items-center justify-center shrink-0">
                                        @if(in_array($statusVal, ['attended', 'late']))
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        @elseif($statusVal === 'excused')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @elseif($statusVal === 'absent')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <span class="text-[10px] font-black text-blue-600 tracking-wider uppercase block truncate">{{ $subjectName }}</span>
                                        <h4 class="font-heading font-extrabold text-sm text-gray-900 mt-0.5 truncate leading-snug">{{ $pc->title }}</h4>
                                    </div>
                                </div>

                                <div class="space-y-2 border-t border-b border-gray-100 py-3 text-xs text-gray-600">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <span class="font-semibold text-gray-800 truncate">Teacher: {{ $teacherName }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span>{{ $scheduledDate }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>{{ $scheduledTime }}</span>
                                    </div>
                                </div>

                                <div class="flex gap-2 text-xs">
                                    <a href="{{ route('student.recordings') }}" class="flex-1 py-2.5 px-3 rounded-xl border border-gray-200 text-gray-700 hover:bg-gray-50 font-bold text-center flex items-center justify-center gap-1 transition-colors">
                                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Watch Recording
                                    </a>
                                    <a href="{{ route('student.learning') }}" class="flex-1 py-2.5 px-3 rounded-xl border border-gray-200 text-gray-700 hover:bg-gray-50 font-bold text-center flex items-center justify-center gap-1 transition-colors">
                                        <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        View Materials
                                    </a>
                                </div>

                                {{-- Attendance Correction Request Status / Trigger --}}
                                @if($existingCorrection)
                                    <div class="pt-1 flex items-center justify-between text-[11px] px-3 py-2 rounded-xl {{ $existingCorrection->status === 'approved' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($existingCorrection->status === 'rejected' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-amber-50 text-amber-700 border border-amber-200') }}">
                                        <span class="font-bold">Correction: {{ ucfirst($existingCorrection->status) }}</span>
                                        <span class="text-[10px]">{{ $existingCorrection->created_at->format('d M') }}</span>
                                    </div>
                                @elseif(in_array($statusVal, ['absent', 'pending_verification', null]))
                                    <div class="pt-1">
                                        <button type="button" @click="openCorrectionModal({{ $pc->id }}, '{{ addslashes($pc->title) }}')" class="w-full py-2 px-3 rounded-xl border border-amber-300 bg-amber-50/70 hover:bg-amber-100 text-amber-800 font-bold text-xs flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            Request Attendance Correction
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center bg-white rounded-3xl border border-gray-100 text-xs text-gray-400 font-medium">
                        No past class recordings available yet.
                    </div>
                @endif
            </div>

        </div>

        {{-- ── CLASS DETAILS MODAL ── --}}
        <div x-show="showDetailsModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 w-full max-w-lg p-6 space-y-5"
                 @click.away="showDetailsModal = false">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-wider text-blue-600">LIVE CLASS DETAILS</span>
                        <h3 class="font-heading font-black text-lg text-gray-900" x-text="selectedClass"></h3>
                    </div>
                    <button @click="showDetailsModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg cursor-pointer">✕</button>
                </div>

                <div class="space-y-3 text-xs text-gray-600">
                    <p class="font-medium text-gray-700">Get ready for your live class with your teacher and classmates:</p>
                    <div class="p-3 bg-blue-50/70 border border-blue-100 rounded-2xl space-y-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="checklist.notes" class="w-4 h-4 text-blue-600 rounded">
                            <span class="text-gray-800 font-bold">Review syllabus topic notes & formulas</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="checklist.practice" class="w-4 h-4 text-blue-600 rounded">
                            <span class="text-gray-800 font-bold">Prepare pen, notebook, and calculator</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="checklist.worksheet" class="w-4 h-4 text-blue-600 rounded">
                            <span class="text-gray-800 font-bold">Test your microphone and webcam</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="showDetailsModal = false" class="py-2.5 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-bold text-xs cursor-pointer">
                        Close
                    </button>
                </div>
            </div>
        </div>

        {{-- ── ATTENDANCE CORRECTION REQUEST MODAL ── --}}
        <div x-show="showCorrectionModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 w-full max-w-lg p-6 space-y-4"
                 @click.away="showCorrectionModal = false">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-wider text-amber-600">ATTENDANCE VERIFICATION</span>
                        <h3 class="font-heading font-black text-lg text-gray-900">Request Attendance Correction</h3>
                    </div>
                    <button @click="showCorrectionModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg cursor-pointer">✕</button>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <label class="block text-gray-400 text-[10px] font-bold uppercase tracking-wider mb-1">Class Session</label>
                        <div class="p-3 bg-gray-50 rounded-2xl border border-gray-100 font-bold text-gray-800" x-text="correctionClassTitle"></div>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-bold mb-1">Requested Status</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer transition-all"
                                   :class="correctionStatus === 'attended' ? 'border-blue-600 bg-blue-50/50 text-blue-700 font-bold' : 'border-gray-200 text-gray-600'">
                                <input type="radio" value="attended" x-model="correctionStatus" class="text-blue-600">
                                <span>Attended (Joined)</span>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer transition-all"
                                   :class="correctionStatus === 'excused' ? 'border-blue-600 bg-blue-50/50 text-blue-700 font-bold' : 'border-gray-200 text-gray-600'">
                                <input type="radio" value="excused" x-model="correctionStatus" class="text-blue-600">
                                <span>Excused Absence</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-bold mb-1">Reason / Explanation *</label>
                        <textarea x-model="correctionReason" rows="3" placeholder="Explain why you were marked absent (e.g. joined via phone, technical glitch, submitted medical cert to teacher)..."
                                  class="w-full p-3 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 text-xs font-medium"></textarea>
                    </div>

                    <template x-if="correctionError">
                        <div class="p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-xs font-semibold" x-text="correctionError"></div>
                    </template>
                    <template x-if="correctionSuccess">
                        <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs font-semibold" x-text="correctionSuccess"></div>
                    </template>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="showCorrectionModal = false" class="py-2.5 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-bold text-xs cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" @click="submitCorrection()" :disabled="isSubmittingCorrection"
                            class="py-2.5 px-5 bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white rounded-xl font-extrabold text-xs shadow-xs transition-colors cursor-pointer inline-flex items-center gap-1.5">
                        <span x-show="!isSubmittingCorrection">Submit Request</span>
                        <span x-show="isSubmittingCorrection">Submitting...</span>
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-dashboard-layout>
