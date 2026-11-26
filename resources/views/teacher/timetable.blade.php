<x-teacher-layout>
    <x-slot name="title">Teaching Timetable & Calendar</x-slot>

    <div class="space-y-6">
        {{-- ── HEADER SECTION (Tasks & Assessments style) ── --}}
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="font-heading font-black text-2xl text-gray-900">Teaching Timetable</h1>
                <p class="text-xs text-gray-500 font-medium mt-0.5">Manage class schedules, drag & drop rescheduling, public holidays and academic terms.</p>
            </div>

            <a href="{{ route('teacher.classes') }}" class="py-2.5 px-5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl text-xs font-extrabold transition-all shrink-0 inline-flex items-center gap-1.5 self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                <span>Classes Workspace</span>
            </a>
        </div>

        {{-- ── KPI SUMMARY CARDS ── --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-3xl p-4 border border-gray-100 shadow-sm flex items-center gap-3 hover:shadow-md transition-all">
                <div class="w-10 h-10 rounded-2xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-blue-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="text-xl font-black text-gray-900">{{ $classesThisWeek }}</p>
                    <p class="text-[11px] font-bold text-gray-400">This Week</p>
                    <p class="text-[10px] text-gray-500 font-medium">Classes scheduled</p>
                </div>
            </div>

            <div class="bg-white rounded-3xl p-4 border border-gray-100 shadow-sm flex items-center gap-3 hover:shadow-md transition-all">
                <div class="w-10 h-10 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-emerald-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xl font-black text-gray-900">{{ $upcomingCount }}</p>
                    <p class="text-[11px] font-bold text-gray-400">Upcoming</p>
                    <p class="text-[10px] text-gray-500 font-medium">Sessions ahead</p>
                </div>
            </div>

            <div class="bg-white rounded-3xl p-4 border border-gray-100 shadow-sm flex items-center gap-3 hover:shadow-md transition-all">
                <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-amber-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </div>
                <div>
                    <p class="text-xl font-black text-gray-900">{{ $rescheduledCount }}</p>
                    <p class="text-[11px] font-bold text-gray-400">Rescheduled</p>
                    <p class="text-[10px] text-gray-500 font-medium">Moved sessions</p>
                </div>
            </div>
        </div>

        {{-- ── CALENDAR (shared component, also used by the student, parent and admin portals) ── --}}
        <x-calendar.google-calendar
            role="teacher"
            :courses="$courses"
            :subjects="$subjects"
        />
    </div>
</x-teacher-layout>
