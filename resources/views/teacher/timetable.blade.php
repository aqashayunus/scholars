<x-teacher-layout>
    <x-slot name="title">Teaching Timetable & Calendar</x-slot>

    <div class="space-y-4 sm:space-y-6">
        {{-- ── TOP HEADER & STATS ── --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="font-heading font-black text-xl sm:text-2xl md:text-3xl text-charcoal tracking-tight">Teaching Timetable</h2>
                <p class="text-xs sm:text-sm text-muted font-medium mt-0.5">
                    Manage class schedules, interactive drag & drop rescheduling, public holidays, and academic terms.
                </p>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-auto">
                <a href="{{ route('teacher.classes') }}" class="btn-secondary py-2 px-3.5 text-xs font-bold rounded-xl flex items-center gap-1.5 shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    <span>Classes Workspace</span>
                </a>
            </div>
        </div>

        {{-- Top Metric Summary Cards --}}
        <div class="grid grid-cols-3 gap-2.5 sm:gap-4">
            <div class="p-3 sm:p-4 rounded-2xl bg-white border border-gray-100 shadow-2xs flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-blue-50 text-primary flex items-center justify-center font-bold shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="text-[9px] sm:text-[11px] font-bold text-gray-400 uppercase tracking-wider truncate">This Week</p>
                    <p class="font-heading font-black text-base sm:text-xl text-charcoal">{{ $classesThisWeek }}</p>
                </div>
            </div>

            <div class="p-3 sm:p-4 rounded-2xl bg-white border border-gray-100 shadow-2xs flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-[9px] sm:text-[11px] font-bold text-gray-400 uppercase tracking-wider truncate">Upcoming</p>
                    <p class="font-heading font-black text-base sm:text-xl text-charcoal">{{ $upcomingCount }}</p>
                </div>
            </div>

            <div class="p-3 sm:p-4 rounded-2xl bg-white border border-gray-100 shadow-2xs flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </div>
                <div>
                    <p class="text-[9px] sm:text-[11px] font-bold text-gray-400 uppercase tracking-wider truncate">Rescheduled</p>
                    <p class="font-heading font-black text-base sm:text-xl text-charcoal">{{ $rescheduledCount }}</p>
                </div>
            </div>
        </div>

        {{-- ── GOOGLE CALENDAR COMPONENT ── --}}
        <x-calendar.google-calendar 
            role="teacher"
            :courses="$courses"
            :subjects="$subjects"
        />
    </div>
</x-teacher-layout>
