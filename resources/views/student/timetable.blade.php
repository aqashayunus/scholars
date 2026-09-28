<x-dashboard-layout>
    <x-slot name="title">Academic Timetable</x-slot>

    <x-slot name="sidebar">
        @include('student.partials.sidebar')
    </x-slot>

    <div class="space-y-4 sm:space-y-6">
        {{-- ── PAGE HEADER ── --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="font-heading font-black text-xl sm:text-2xl md:text-3xl text-charcoal tracking-tight">Academic Timetable</h2>
                <p class="text-xs sm:text-sm text-muted font-medium mt-0.5">
                    Interactive Google Calendar schedule with enrolled live classes, nationwide public holidays, and exam windows.
                </p>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-auto">
                <a href="{{ route('student.classes') }}" class="btn-secondary py-2 px-3.5 text-xs font-bold rounded-xl flex items-center gap-1.5 shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    <span>Classes List</span>
                </a>
            </div>
        </div>

        {{-- ── GOOGLE CALENDAR COMPONENT ── --}}
        <x-calendar.google-calendar 
            role="student"
            :courses="$courses"
            :subjects="$subjects"
        />
    </div>
</x-dashboard-layout>
