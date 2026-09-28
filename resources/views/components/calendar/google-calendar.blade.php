@props([
    'role' => 'student', // 'student', 'teacher', 'parent', 'admin'
    'initialEvents' => [],
    'courses' => collect(),
    'subjects' => collect(),
    'teachers' => collect(),
    'children' => collect(),
])

<style>
    /* Strictly enforce Inter typography matching dashboard#teachers (Active Educator Directory) */
    .olympia-timetable-wrapper,
    .olympia-timetable-wrapper * {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }
    .olympia-timetable-wrapper h1,
    .olympia-timetable-wrapper h2,
    .olympia-timetable-wrapper h3,
    .olympia-timetable-wrapper h4,
    .olympia-timetable-wrapper h5,
    .olympia-timetable-wrapper h6 {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        letter-spacing: -0.01em;
    }
</style>

<div x-data="googleCalendar({
    role: '{{ $role }}',
    csrfToken: '{{ csrf_token() }}',
    eventsApiUrl: '{{ route('timetable.events') }}',
    rescheduleUrlTemplate: '{{ $role === 'admin' ? url('/admin/classes') : url('/teacher/classes') }}/:id/reschedule-direct',
    quickCreateUrl: '{{ $role === 'admin' ? route('admin.classes.quick-create') : route('teacher.classes.quick-create') }}',
    academicEventsUrl: '{{ route('admin.academic-events.store') }}',
    academicEventsDeleteTemplate: '{{ url('/admin/academic-events') }}/:id',
    exportIcsUrlTemplate: '{{ url('/timetable/export-ics') }}/:id',
})" 
x-init="initCalendar()"
class="olympia-timetable-wrapper space-y-6">

    {{-- ── TOP CONTROLS & HEADER BAR ── --}}
    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-5 space-y-4">
        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-3 sm:gap-4">
            
            {{-- Top Row: Date Navigation & Current Date Title --}}
            <div class="flex items-center justify-between sm:justify-start gap-2 sm:gap-3">
                <div class="flex items-center gap-1 bg-gray-50 border border-gray-200/70 rounded-xl p-1 shadow-2xs">
                    <button @click="navigate('prev')" type="button" class="p-1.5 text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg transition-all" title="Previous">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button @click="navigate('today')" type="button" class="px-2.5 py-1 text-xs font-medium text-gray-700 hover:text-gray-900 hover:bg-white rounded-lg transition-all">
                        Today
                    </button>
                    <button @click="navigate('next')" type="button" class="p-1.5 text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg transition-all" title="Next">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>

                {{-- Month & Year Label with loading spinner --}}
                <div class="flex items-center gap-2">
                    <h2 class="font-semibold text-lg text-gray-900 select-none truncate" x-text="calendarTitle"></h2>
                    <span x-show="isLoading" class="inline-flex items-center">
                        <svg class="animate-spin h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </span>
                </div>
            </div>

            {{-- Right: View Mode Selector & Role-Specific Actions --}}
            <div class="flex flex-wrap items-center justify-between sm:justify-end gap-2.5 sm:gap-3">
                
                {{-- If Teacher: Quick Schedule Button (Admin schedules by clicking date cards) --}}
                @if($role === 'teacher')
                <button @click="openQuickCreateModal()" type="button" 
                        class="btn-primary py-1.5 sm:py-2 px-3 sm:px-4 text-xs font-medium shadow-xs flex items-center gap-1.5 rounded-2xl">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Schedule Class</span>
                </button>
                @endif

                {{-- View Tabs: Month, Week, Day, Annual (Year), Agenda (Horizontally scrollable on mobile) --}}
                <div class="w-full sm:w-auto overflow-x-auto no-scrollbar -mx-1 px-1 sm:p-0">
                    <div class="inline-flex bg-gray-100 p-1 rounded-xl text-xs font-medium text-gray-600 shadow-2xs shrink-0">
                        <button @click="setView('month')" 
                                :class="activeView === 'month' ? 'bg-white text-gray-900 shadow-xs font-medium' : 'hover:text-gray-900 font-normal'"
                                class="px-3 py-1 sm:py-1.5 rounded-lg transition-all whitespace-nowrap">Month</button>
                        <button @click="setView('week')" 
                                :class="activeView === 'week' ? 'bg-white text-gray-900 shadow-xs font-medium' : 'hover:text-gray-900 font-normal'"
                                class="px-3 py-1 sm:py-1.5 rounded-lg transition-all whitespace-nowrap">Week</button>
                        <button @click="setView('day')" 
                                :class="activeView === 'day' ? 'bg-white text-gray-900 shadow-xs font-medium' : 'hover:text-gray-900 font-normal'"
                                class="px-3 py-1 sm:py-1.5 rounded-lg transition-all whitespace-nowrap">Day</button>
                        <button @click="setView('annual')" 
                                :class="activeView === 'annual' ? 'bg-white text-gray-900 shadow-xs font-medium' : 'hover:text-gray-900 font-normal'"
                                class="px-3 py-1 sm:py-1.5 rounded-lg transition-all whitespace-nowrap">Annual</button>
                        <button @click="setView('agenda')" 
                                :class="activeView === 'agenda' ? 'bg-white text-gray-900 shadow-xs font-medium' : 'hover:text-gray-900 font-normal'"
                                class="px-3 py-1 sm:py-1.5 rounded-lg transition-all whitespace-nowrap">Agenda</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- If Admin: Teacher & Course Filter Bar --}}
        @if($role === 'admin')
        <div class="pt-3 border-t border-gray-100 flex flex-wrap items-center gap-3 text-xs">
            <div class="flex items-center gap-2">
                <span class="font-medium text-gray-500 text-xs shrink-0">Teacher:</span>
                <select x-model="selectedTeacherId" @change="fetchEvents()" class="rounded-xl border-gray-200 text-xs py-1.5 px-3 bg-white focus:ring-gray-900 focus:border-gray-900 font-normal text-gray-700 shadow-2xs">
                    <option value="">All Teachers</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}">{{ $t->user->name ?? 'Teacher #' . $t->id }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <span class="font-medium text-gray-500 text-xs shrink-0">Course:</span>
                <select x-model="selectedCourseId" @change="fetchEvents()" class="rounded-xl border-gray-200 text-xs py-1.5 px-3 bg-white focus:ring-gray-900 focus:border-gray-900 font-normal text-gray-700 shadow-2xs">
                    <option value="">All Courses</option>
                    @foreach($courses as $c)
                        <option value="{{ $c->id }}">{{ $c->title }} ({{ $c->subject->name ?? '' }})</option>
                    @endforeach
                </select>
            </div>

            <template x-if="selectedTeacherId || selectedCourseId">
                <button @click="selectedTeacherId = ''; selectedCourseId = ''; fetchEvents()" type="button" class="text-gray-500 hover:text-gray-800 font-medium text-xs underline cursor-pointer">
                    Clear filters
                </button>
            </template>
        </div>
        @endif

        {{-- If Parent: Children Color Legend (All children always displayed together with distinct color coding) --}}
        @if($role === 'parent' && $children->count() > 0)
        <div class="pt-2.5 sm:pt-3 border-t border-gray-100 flex items-center gap-2 overflow-x-auto no-scrollbar py-0.5 -mx-1 px-1">
            <span class="font-medium text-gray-500 uppercase tracking-wider text-[10px] shrink-0">Children Color Code:</span>
            @foreach($children as $ch)
            <div class="px-3 py-1 rounded-full border {{ $ch->theme['bg'] ?? 'bg-blue-50' }} {{ $ch->theme['border'] ?? 'border-blue-200' }} {{ $ch->theme['text'] ?? 'text-blue-800' }} font-medium text-xs flex items-center gap-1.5 shadow-2xs shrink-0 select-none">
                <span class="w-2.5 h-2.5 rounded-full {{ $ch->theme['dot'] ?? 'bg-blue-600' }} shadow-2xs"></span>
                <span>{{ $ch->user->name ?? 'Child' }}</span>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Filters & Legend Row (Pill Bar for mobile & desktop) --}}
        <div class="pt-2.5 sm:pt-3 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
            <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-0.5 -mx-1 px-1">
                <span class="font-medium text-gray-500 text-xs shrink-0">{{ $role === 'admin' ? 'Filter:' : 'Filters:' }}</span>
                
                @if($role === 'admin')
                    <button @click="filters.liveClasses = !filters.liveClasses" type="button"
                            :class="filters.liveClasses ? 'bg-gray-900 text-white border-gray-900 font-medium' : 'bg-white border-gray-200 text-gray-500 hover:border-gray-300 font-normal'"
                            class="px-3 py-1 rounded-lg border text-xs flex items-center gap-1.5 shrink-0 transition select-none">
                        <span>Classes</span>
                    </button>

                    <button @click="filters.publicHolidays = !filters.publicHolidays" type="button"
                            :class="filters.publicHolidays ? 'bg-gray-900 text-white border-gray-900 font-medium' : 'bg-white border-gray-200 text-gray-500 hover:border-gray-300 font-normal'"
                            class="px-3 py-1 rounded-lg border text-xs flex items-center gap-1.5 shrink-0 transition select-none">
                        <span>Holidays</span>
                    </button>

                    <button @click="filters.schoolBreaks = !filters.schoolBreaks" type="button"
                            :class="filters.schoolBreaks ? 'bg-gray-900 text-white border-gray-900 font-medium' : 'bg-white border-gray-200 text-gray-500 hover:border-gray-300 font-normal'"
                            class="px-3 py-1 rounded-lg border text-xs flex items-center gap-1.5 shrink-0 transition select-none">
                        <span>Breaks</span>
                    </button>

                    <button @click="filters.examPeriods = !filters.examPeriods" type="button"
                            :class="filters.examPeriods ? 'bg-gray-900 text-white border-gray-900 font-medium' : 'bg-white border-gray-200 text-gray-500 hover:border-gray-300 font-normal'"
                            class="px-3 py-1 rounded-lg border text-xs flex items-center gap-1.5 shrink-0 transition select-none">
                        <span>Exams</span>
                    </button>
                @else
                    <button @click="filters.liveClasses = !filters.liveClasses" type="button"
                            :class="filters.liveClasses ? 'bg-blue-50 border-blue-200 text-blue-800 font-medium' : 'bg-gray-50 border-gray-200 text-gray-400 opacity-60 font-normal'"
                            class="px-2.5 py-1 rounded-full border text-[11px] flex items-center gap-1.5 shrink-0 transition-all select-none">
                        <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                        <span>Live Classes</span>
                    </button>

                    <button @click="filters.publicHolidays = !filters.publicHolidays" type="button"
                            :class="filters.publicHolidays ? 'bg-rose-50 border-rose-200 text-rose-800 font-medium' : 'bg-gray-50 border-gray-200 text-gray-400 opacity-60 font-normal'"
                            class="px-2.5 py-1 rounded-full border text-[11px] flex items-center gap-1.5 shrink-0 transition-all select-none">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        <span>Holidays</span>
                    </button>

                    <button @click="filters.schoolBreaks = !filters.schoolBreaks" type="button"
                            :class="filters.schoolBreaks ? 'bg-amber-50 border-amber-200 text-amber-800 font-medium' : 'bg-gray-50 border-gray-200 text-gray-400 opacity-60 font-normal'"
                            class="px-2.5 py-1 rounded-full border text-[11px] flex items-center gap-1.5 shrink-0 transition-all select-none">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span>Breaks</span>
                    </button>

                    <button @click="filters.examPeriods = !filters.examPeriods" type="button"
                            :class="filters.examPeriods ? 'bg-purple-50 border-purple-200 text-purple-800 font-medium' : 'bg-gray-50 border-gray-200 text-gray-400 opacity-60 font-normal'"
                            class="px-2.5 py-1 rounded-full border text-[11px] flex items-center gap-1.5 shrink-0 transition-all select-none">
                        <span class="w-2 h-2 rounded-full bg-purple-600"></span>
                        <span>Exams</span>
                    </button>
                @endif
            </div>

            {{-- Timezone Notice --}}
            <div class="text-[10px] sm:text-[11px] text-gray-400 flex items-center gap-1.5 font-normal shrink-0">
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>GMT+8 (Malaysia Time)</span>
            </div>
        </div>
    </div>

    {{-- ── 1. MONTH VIEW ── --}}
    <div x-show="activeView === 'month'" x-cloak>
        
        {{-- ── 1A. MOBILE-OPTIMIZED MONTH VIEW (Compact Calendar + Day Agenda Sheet) ── --}}
        <div class="block md:hidden bg-white rounded-2xl border border-gray-200/80 shadow-xs p-4 space-y-4">
            {{-- Days of week header --}}
            <div class="grid grid-cols-7 text-center text-[10px] font-medium text-gray-500 uppercase py-2 border-b border-gray-200 bg-gray-50/75 tracking-wider rounded-t-xl">
                <div>Mon</div>
                <div>Tue</div>
                <div>Wed</div>
                <div>Thu</div>
                <div>Fri</div>
                <div>Sat</div>
                <div>Sun</div>
            </div>

            {{-- Compact Month Grid (42 cells) --}}
            <div class="grid grid-cols-7 gap-1">
                <template x-for="(day, index) in monthDays" :key="'mob_' + index">
                    <button @click="selectMobileDate(day.dateStr)"
                            type="button"
                            class="h-10 sm:h-11 rounded-xl flex flex-col items-center justify-center relative transition-all"
                            :class="{
                                'bg-gray-900 text-white font-medium shadow-sm': selectedMobileDateStr === day.dateStr,
                                'bg-gray-100 text-gray-900 font-medium': day.isToday && selectedMobileDateStr !== day.dateStr,
                                'text-gray-800 font-normal hover:bg-gray-50': day.isCurrentMonth && selectedMobileDateStr !== day.dateStr && !day.isToday,
                                'text-gray-300 pointer-events-none opacity-40 font-normal': !day.isCurrentMonth
                            }">
                        <span class="text-xs leading-none" x-text="day.dayNumber"></span>

                        {{-- Indicator Dots below day number --}}
                        <div class="flex items-center gap-0.5 mt-1 h-1.5">
                            {{-- Class dot --}}
                            <template x-if="day.events.some(e => e.type === 'live_class')">
                                <span class="w-1 h-1 rounded-full" :class="selectedMobileDateStr === day.dateStr ? 'bg-white' : 'bg-gray-600'"></span>
                            </template>
                            {{-- Holiday dot --}}
                            <template x-if="day.events.some(e => e.type === 'public_holiday')">
                                <span class="w-1 h-1 rounded-full" :class="selectedMobileDateStr === day.dateStr ? 'bg-white' : 'bg-gray-400'"></span>
                            </template>
                            {{-- Break dot --}}
                            <template x-if="day.events.some(e => e.type === 'school_break')">
                                <span class="w-1 h-1 rounded-full" :class="selectedMobileDateStr === day.dateStr ? 'bg-white' : 'bg-gray-400'"></span>
                            </template>
                            {{-- Exam dot --}}
                            <template x-if="day.events.some(e => e.type === 'exam_period')">
                                <span class="w-1 h-1 rounded-full" :class="selectedMobileDateStr === day.dateStr ? 'bg-white' : 'bg-gray-400'"></span>
                            </template>
                        </div>
                    </button>
                </template>
            </div>

            {{-- ── SELECTED DAY SCHEDULE SHEET (Directly below compact month grid) ── --}}
            <div class="pt-4 border-t border-gray-100 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-gray-900"></span>
                        <h4 class="font-semibold text-sm text-gray-900" x-text="formattedSelectedMobileDate"></h4>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-600 text-[10px] font-normal"
                          x-text="selectedMobileDayEvents.length + ' event' + (selectedMobileDayEvents.length === 1 ? '' : 's')"></span>
                </div>

                {{-- If Admin: Button to schedule or add milestone for this selected day --}}
                @if($role === 'admin')
                <button type="button" @click="openAdminDateModal(selectedMobileDateStr)"
                        class="w-full py-2 px-3 rounded-xl border border-gray-200 hover:border-gray-900 bg-white hover:bg-gray-50 text-gray-800 text-xs font-medium flex items-center justify-center gap-1.5 transition-colors shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Add Class or Milestone to this Date</span>
                </button>
                @endif

                {{-- Public Holiday Banner Card if holiday active on this date --}}
                <template x-for="h in selectedMobileDayHolidays" :key="h.id">
                    <div class="p-3 rounded-xl bg-gray-50 border border-gray-200 text-xs flex items-start gap-2.5">
                        <span class="text-base shrink-0">🗓️</span>
                        <div class="space-y-0.5">
                            <h5 class="font-semibold text-gray-900 text-xs" x-text="h.title"></h5>
                            <p class="text-[11px] text-gray-500 font-normal" x-text="h.description || 'Official Academic Event / Holiday'"></p>
                        </div>
                    </div>
                </template>

                {{-- Classes List for this selected day --}}
                <template x-if="selectedMobileDayClasses.length === 0 && selectedMobileDayHolidays.length === 0">
                    <div class="text-center py-7 bg-gray-50/60 rounded-xl border border-dashed border-gray-200">
                        <div class="w-10 h-10 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center mx-auto mb-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <p class="text-xs font-medium text-gray-700">No sessions scheduled on this day</p>
                        <p class="text-[10px] text-gray-400 font-normal mt-0.5">No classes or events are planned for this date.</p>
                        @if($role === 'teacher')
                        <button @click="quickCreateForm.scheduled_date = selectedMobileDateStr; showQuickCreateModal = true;" class="mt-3 btn-secondary py-1.5 px-3 rounded-xl text-xs font-medium">
                            + Schedule Class on This Day
                        </button>
                        @endif
                    </div>
                </template>

                <div class="space-y-2.5">
                    <template x-for="evt in selectedMobileDayClasses" :key="evt.id">
                        <div @click="openEventDetails(evt)" 
                             class="p-4 rounded-xl border border-gray-200/80 bg-white shadow-2xs hover:border-gray-400 transition-all flex flex-col gap-2 cursor-pointer"
                             :style="evt.child_theme ? ('border-left-width: 3px; border-left-color: ' + evt.child_theme.color) : ''">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <template x-if="evt.child_name">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-medium border shadow-2xs flex items-center gap-1"
                                              :style="evt.child_theme ? ('background-color: ' + evt.child_theme.hex_bg + '; border-color: ' + evt.child_theme.hex_border + '; color: ' + evt.child_theme.hex_text) : 'background-color: #f1f5f9; color: #1e293b; border-color: #cbd5e1;'">
                                            <span class="w-1.5 h-1.5 rounded-full" :style="'background-color: ' + (evt.child_theme?.color || '#475569')"></span>
                                            <span x-text="evt.child_name"></span>
                                        </span>
                                    </template>
                                    <span class="px-2 py-0.5 rounded-md bg-gray-100 text-gray-700 text-[10px] font-medium" x-text="evt.subject_name"></span>
                                    <template x-if="evt.is_rescheduled">
                                        <span class="px-1.5 py-0.5 rounded-md bg-amber-100 text-amber-800 text-[9px] font-medium flex items-center gap-1">
                                            <span>⚠️ Rescheduled</span>
                                        </span>
                                    </template>
                                    <template x-if="evt.status === 'live'">
                                        <span class="px-2 py-0.5 rounded-md bg-gray-900 text-white text-[9px] font-medium">LIVE</span>
                                    </template>
                                </div>
                                <span class="text-xs font-mono font-medium text-gray-800 shrink-0" x-text="evt.time_str"></span>
                            </div>

                            <div>
                                <h5 class="font-medium text-gray-900 text-xs sm:text-sm leading-snug" x-text="evt.title"></h5>
                                <p class="text-[11px] text-gray-500 font-normal mt-0.5">
                                    <span x-text="evt.teacher_name || 'Assigned Instructor'"></span> • <span x-text="evt.duration_minutes"></span> min
                                </p>
                            </div>

                            {{-- Reschedule Notice detail --}}
                            <template x-if="evt.is_rescheduled">
                                <div class="p-2 rounded-xl bg-amber-50 text-[10px] text-amber-900 border border-amber-200 space-y-0.5">
                                    <p class="font-medium">Original: <span class="font-normal" x-text="evt.original_scheduled_at"></span></p>
                                    <p class="font-medium">Reason: <span class="font-normal italic" x-text="evt.reschedule_reason"></span></p>
                                </div>
                            </template>

                            <div class="flex items-center justify-between pt-1 border-t border-gray-50">
                                <span class="text-[11px] text-gray-500 font-normal">Click for details →</span>
                                <template x-if="evt.meeting_url">
                                    <a :href="evt.meeting_url" target="_blank" @click.stop class="bg-gray-900 hover:bg-black text-white py-1.5 px-3 rounded-lg text-xs font-medium shadow-2xs">
                                        Join
                                    </a>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- ── 1B. DESKTOP MONTH VIEW (Clean 7-Column Grid matching Teachers Directory) ── --}}
        <div class="hidden md:block bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
            {{-- Days of week header (matching Active Educator table headers) --}}
            <div class="grid grid-cols-7 border-b border-gray-200 text-gray-500 font-medium uppercase text-[10px] bg-gray-50/75 text-center py-2.5 tracking-wider">
                <div>Mon</div>
                <div>Tue</div>
                <div>Wed</div>
                <div>Thu</div>
                <div>Fri</div>
                <div>Sat</div>
                <div>Sun</div>
            </div>

            {{-- Month Day Cells Grid --}}
            <div class="grid grid-cols-7 divide-x divide-y divide-gray-200/70">
                <template x-for="(day, index) in monthDays" :key="'desk_' + index">
                    <div class="min-h-[110px] lg:min-h-[130px] p-2 transition-colors relative flex flex-col justify-between group cursor-pointer"
                         :class="{
                             'bg-white hover:bg-gray-50/70': day.isCurrentMonth,
                             'bg-gray-50/40 text-gray-300': !day.isCurrentMonth,
                             'bg-gray-50/75': day.isToday
                         }"
                         @click="dayCellClicked(day)">
                        
                        {{-- Day Number & Badges Header --}}
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs rounded-full w-6 h-6 flex items-center justify-center transition-all"
                                  :class="{
                                      'bg-gray-900 text-white shadow-xs font-semibold': day.isToday,
                                      'text-gray-900 font-medium': day.isCurrentMonth && !day.isToday,
                                      'text-gray-400 font-normal': !day.isCurrentMonth
                                  }"
                                  x-text="day.dayNumber">
                            </span>

                            <div class="flex items-center gap-1">
                                @if($role === 'admin')
                                <span class="opacity-0 group-hover:opacity-100 transition-opacity text-[10px] font-medium text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 px-1.5 py-0.5 rounded-md flex items-center gap-0.5 shadow-2xs"
                                      title="Click date to schedule class or add milestone">
                                    + Add
                                </span>
                                @endif

                                {{-- Holiday indicator dot --}}
                                <template x-if="day.events.some(e => e.type === 'public_holiday')">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400" title="Public Holiday / Event"></span>
                                </template>
                            </div>
                        </div>

                        {{-- Event Chips List inside cell --}}
                        <div class="space-y-1 flex-1 overflow-hidden">
                            <template x-for="(evt, evtIdx) in day.events.slice(0, 3)" :key="evt.id">
                                <div @click.stop="openEventDetails(evt)"
                                     class="px-2 py-1 rounded-lg text-[10px] font-medium truncate cursor-pointer transition-all shadow-2xs border flex items-center gap-1"
                                     :class="getEventChipClass(evt)"
                                     :style="getEventChipStyle(evt)"
                                     :title="(evt.child_name ? ('[' + evt.child_name + '] ') : '') + evt.title">
                                    
                                    <template x-if="evt.type === 'public_holiday' && role !== 'admin'">
                                        <span class="text-[9px]">🇲🇾</span>
                                    </template>
                                    <template x-if="evt.type === 'live_class'">
                                        <span class="text-[9px] font-mono shrink-0 text-gray-600" x-text="evt.time_str"></span>
                                    </template>

                                    <template x-if="evt.child_name">
                                        <span class="px-1 py-0.2 rounded text-[9px] font-medium uppercase shrink-0 border"
                                              :style="'background-color: #ffffff; border-color: ' + (evt.child_theme?.hex_border || '#cbd5e1') + '; color: ' + (evt.child_theme?.hex_text || '#1e293b')"
                                              x-text="evt.child_name.split(' ')[0]"></span>
                                    </template>

                                    <span class="truncate flex-1" x-text="evt.title"></span>

                                    <template x-if="evt.is_rescheduled">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse shrink-0"></span>
                                    </template>
                                </div>
                            </template>

                            {{-- More than 3 events pill --}}
                            <template x-if="day.events.length > 3">
                                <button @click.stop="openDayModal(day)" class="w-full text-left text-[10px] font-medium text-gray-500 hover:text-gray-900 px-1">
                                    +<span x-text="day.events.length - 3"></span> more...
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- ── 2. WEEK VIEW (TIME GRID 08:00 - 22:00) ── --}}
    <div x-show="activeView === 'week'" x-cloak class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto no-scrollbar">
            <div class="min-w-[620px]">
                {{-- Week Header (matching Active Educator table headers) --}}
                <div class="grid grid-cols-8 border-b border-gray-200 text-gray-500 font-medium uppercase text-[10px] bg-gray-50/75 text-center py-2.5 tracking-wider">
                    <div class="py-1">Time</div>
                    <template x-for="day in weekDays" :key="day.dateStr">
                        <div class="flex flex-col items-center">
                            <span class="uppercase tracking-wider text-[10px] font-medium text-gray-500" x-text="day.dayName"></span>
                            <span class="w-6 h-6 rounded-full flex items-center justify-center font-medium text-xs mt-0.5"
                                  :class="day.isToday ? 'bg-gray-900 text-white shadow-xs font-semibold' : 'text-gray-800'"
                                  x-text="day.dayNumber"></span>
                        </div>
                    </template>
                </div>

                {{-- All-Day Events Banner Section (Holidays / Breaks) --}}
                <div class="grid grid-cols-8 border-b border-gray-200 bg-gray-50/50 min-h-[36px] items-center text-xs">
                    <div class="p-2 text-[10px] font-medium text-gray-500 text-center uppercase tracking-wider">All-Day</div>
                    <template x-for="day in weekDays" :key="'allday_' + day.dateStr">
                        <div class="p-1 border-l border-gray-200 min-h-[36px] space-y-1">
                            <template x-for="evt in day.allDayEvents" :key="evt.id">
                                <div @click="openEventDetails(evt)" 
                                     class="px-2 py-0.5 rounded text-[10px] font-medium truncate cursor-pointer {{ $role === 'admin' ? 'bg-gray-100 text-gray-800 border border-gray-200' : 'bg-rose-500 text-white font-medium' }} shadow-2xs"
                                     :title="evt.title" x-text="(role === 'admin' ? '' : '🇲🇾 ') + evt.title"></div>
                            </template>
                        </div>
                    </template>
                </div>

                {{-- Hourly Slots (08:00 to 22:00) --}}
                <div class="overflow-y-auto max-h-[680px]">
                    <div class="grid grid-cols-8 divide-y divide-gray-200/70 relative">
                        <template x-for="hour in timeSlots" :key="hour">
                            <div class="col-span-8 grid grid-cols-8 min-h-[56px] group">
                                <div class="p-2 text-[10px] font-medium text-gray-400 text-right pr-3 -mt-2.5 select-none" x-text="formatHour(hour)"></div>
                                
                                <template x-for="day in weekDays" :key="day.dateStr + '_' + hour">
                                    <div class="border-l border-gray-200/70 p-1 relative hover:bg-gray-50/60 transition-colors cursor-pointer"
                                         @click="timeSlotClicked(day.dateStr, hour)">
                                        
                                        <template x-for="evt in getEventsForHour(day.events, hour)" :key="evt.id">
                                            <div @click.stop="openEventDetails(evt)"
                                                 class="p-2 rounded-xl text-xs font-medium cursor-pointer shadow-2xs border transition-all mb-1"
                                                 :class="getEventChipClass(evt)"
                                                 :style="getEventChipStyle(evt)">
                                                <div class="flex items-center justify-between gap-1 mb-0.5">
                                                    <span class="font-medium text-[11px] truncate text-gray-900" x-text="evt.title"></span>
                                                    <span class="text-[10px] text-gray-500 shrink-0 font-normal" x-text="evt.time_str"></span>
                                                </div>
                                                <div class="flex items-center gap-1 flex-wrap">
                                                    <template x-if="evt.child_name">
                                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-medium uppercase bg-white/80 border border-black/10"
                                                              x-text="evt.child_name.split(' ')[0]"></span>
                                                    </template>
                                                    <p class="text-[10px] text-gray-500 font-normal truncate" x-text="evt.subject_name"></p>
                                                </div>
                                                <template x-if="evt.is_rescheduled">
                                                    <span class="inline-block mt-1 px-1.5 py-0.2 rounded bg-amber-100 text-amber-800 text-[9px] font-medium">Rescheduled</span>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 3. DAY VIEW ── --}}
    <div x-show="activeView === 'day'" x-cloak class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-5 space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-gray-100">
            <div>
                <h3 class="font-semibold text-lg text-gray-900" x-text="dayViewTitle"></h3>
                <p class="text-xs text-gray-500 font-normal mt-0.5">Detailed daily timeline and session breakdown.</p>
            </div>
            
            <div class="flex items-center gap-2">
                @if($role === 'admin')
                <button @click="openAdminDateModal(formatDateYMD(currentDate))" type="button" class="btn-secondary px-3 py-1.5 rounded-xl text-xs font-medium flex items-center gap-1 text-gray-800">
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Add to this Day</span>
                </button>
                @endif
                <button @click="navigate('prev')" class="btn-ghost p-2 rounded-xl text-xs font-medium">← Prev Day</button>
                <button @click="navigate('today')" class="btn-secondary px-3 py-1.5 rounded-xl text-xs font-medium">Today</button>
                <button @click="navigate('next')" class="btn-ghost p-2 rounded-xl text-xs font-medium">Next Day →</button>
            </div>
        </div>

        {{-- If Day has public holiday --}}
        <template x-if="dayHolidays.length > 0">
            <div class="rounded-xl p-4 {{ $role === 'admin' ? 'bg-gray-50 border border-gray-200 text-gray-800' : 'bg-rose-50 border border-rose-200 text-rose-800' }} flex items-start gap-3">
                <span class="text-lg">{{ $role === 'admin' ? '🗓️' : '🎉' }}</span>
                <div>
                    <h4 class="font-semibold text-sm {{ $role === 'admin' ? 'text-gray-900' : 'text-rose-800' }}" x-text="dayHolidays.map(h => h.title).join(', ')"></h4>
                    <p class="text-xs {{ $role === 'admin' ? 'text-gray-500' : 'text-rose-600' }} font-normal mt-0.5" x-text="dayHolidays[0].description || 'Official Academic Milestone / Public Holiday.'"></p>
                </div>
            </div>
        </template>

        {{-- Daily Sessions List --}}
        <div class="space-y-3">
            <template x-if="dayClasses.length === 0">
                <div class="text-center py-10 bg-gray-50/60 rounded-xl border border-dashed border-gray-200">
                    <div class="w-10 h-10 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center mx-auto mb-2.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <h4 class="font-semibold text-gray-800 text-sm">No classes scheduled for this day</h4>
                    <p class="text-xs text-gray-400 font-normal mt-0.5 max-w-sm mx-auto">No sessions have been scheduled on this date.</p>
                </div>
            </template>

            <template x-for="evt in dayClasses" :key="evt.id">
                <div @click="openEventDetails(evt)" 
                     class="p-4 rounded-xl border border-gray-200/80 bg-white shadow-2xs hover:border-gray-400 transition-all cursor-pointer flex flex-col md:flex-row md:items-center justify-between gap-4"
                     :style="evt.child_theme ? ('border-left-width: 4px; border-left-color: ' + evt.child_theme.color) : ''">
                    <div class="flex items-start gap-3.5">
                        <div class="w-12 h-12 rounded-xl font-medium text-center flex flex-col items-center justify-center shrink-0 border bg-gray-50 border-gray-200 text-gray-900">
                            <span class="text-[10px] uppercase font-medium text-gray-500" x-text="evt.time_str.split(' ')[1]"></span>
                            <span class="text-xs font-semibold leading-none text-gray-900" x-text="evt.time_str.split(' ')[0]"></span>
                        </div>
                        <div class="space-y-0.5">
                            <div class="flex flex-wrap items-center gap-2">
                                <template x-if="evt.child_name">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium border shadow-2xs flex items-center gap-1"
                                          :style="evt.child_theme ? ('background-color: ' + evt.child_theme.hex_bg + '; border-color: ' + evt.child_theme.hex_border + '; color: ' + evt.child_theme.hex_text) : 'background-color: #f1f5f9; color: #1e293b; border-color: #cbd5e1;'">
                                        <span class="w-1.5 h-1.5 rounded-full" :style="'background-color: ' + (evt.child_theme?.color || '#475569')"></span>
                                        <span x-text="evt.child_name"></span>
                                    </span>
                                </template>
                                <span class="px-2 py-0.5 rounded-md bg-gray-100 text-gray-700 text-[10px] font-medium" x-text="evt.subject_name"></span>
                                <template x-if="evt.is_rescheduled">
                                    <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 text-[10px] font-medium flex items-center gap-1">
                                        <span>⚠️ Rescheduled</span>
                                    </span>
                                </template>
                                <template x-if="evt.status === 'live'">
                                    <span class="px-2 py-0.5 rounded-md bg-gray-900 text-white text-[10px] font-medium">LIVE NOW</span>
                                </template>
                            </div>
                            <h4 class="font-semibold text-gray-900 text-sm" x-text="evt.title"></h4>
                            <p class="text-xs text-gray-500 font-normal">Instructor: <span class="text-gray-900 font-medium" x-text="evt.teacher_name || 'Assigned Instructor'"></span> • Duration: <span x-text="evt.duration_minutes"></span> min</p>
                            
                            {{-- Reschedule Notice Box --}}
                            <template x-if="evt.is_rescheduled">
                                <div class="mt-2 p-2.5 rounded-xl bg-amber-50/80 border border-amber-200/70 text-xs text-amber-900 space-y-0.5">
                                    <p class="font-medium">Original Schedule: <span class="font-normal" x-text="evt.original_scheduled_at"></span></p>
                                    <p class="font-medium">Reason: <span class="font-normal italic" x-text="evt.reschedule_reason"></span></p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0 self-end md:self-auto">
                        <template x-if="evt.meeting_url">
                            <a :href="evt.meeting_url" target="_blank" @click.stop class="bg-gray-900 hover:bg-black text-white py-1.5 px-4 rounded-xl text-xs font-medium shadow-2xs">
                                Join Session
                            </a>
                        </template>
                        <button type="button" class="btn-secondary py-1.5 px-3 rounded-xl text-xs font-medium">Details</button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ── 4. ANNUAL / YEAR VIEW (12-MONTH MATRIX & ACADEMIC PLANNER) ── --}}
    <div x-show="activeView === 'annual'" x-cloak class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-5 space-y-6">
        {{-- Annual Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-gray-100">
            <div>
                <h3 class="font-semibold text-lg text-gray-900">
                    Academic Calendar <span class="text-gray-900" x-text="currentYear"></span>
                </h3>
                <p class="text-xs text-gray-500 font-normal mt-0.5">Full 12-month bird's-eye view of school terms, national holidays, and exam seasons.</p>
            </div>

            <div class="flex items-center gap-2">
                <button @click="changeYear(-1)" class="btn-secondary px-3 py-1.5 rounded-xl text-xs font-medium">← <span x-text="currentYear - 1"></span></button>
                <button @click="currentYear = 2026; fetchEvents()" class="btn-ghost px-3 py-1.5 rounded-xl text-xs font-medium">Current Year</button>
                <button @click="changeYear(1)" class="btn-secondary px-3 py-1.5 rounded-xl text-xs font-medium"><span x-text="currentYear + 1"></span> →</button>
            </div>
        </div>

        {{-- 12-Month Mini Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5">
            <template x-for="monthIdx in 12" :key="monthIdx">
                <div class="bg-gray-50/50 rounded-xl p-3.5 border border-gray-200/70 hover:border-gray-400 transition-all">
                    <div class="flex items-center justify-between mb-2.5">
                        <button @click="jumpToMonth(monthIdx - 1)" class="font-semibold text-xs text-gray-900 hover:text-blue-600 transition-colors text-left"
                                x-text="getMonthName(monthIdx - 1)"></button>
                        <span class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-white text-gray-500 border border-gray-200 shadow-2xs"
                              x-text="getEventCountForMonth(monthIdx - 1) + ' events'"></span>
                    </div>

                    <div class="grid grid-cols-7 gap-1 text-center text-[10px] border-b border-gray-200/60 pb-1 mb-1 font-medium text-gray-400 uppercase">
                        <div>M</div>
                        <div>T</div>
                        <div>W</div>
                        <div>T</div>
                        <div>F</div>
                        <div>S</div>
                        <div>S</div>
                    </div>

                    <div class="grid grid-cols-7 gap-1 text-center text-[10px]">
                        <template x-for="d in getMiniMonthDays(currentYear, monthIdx - 1)" :key="d.dateStr">
                            <div @click="jumpToDate(d.dateStr)"
                                 class="w-6 h-6 rounded-md flex flex-col items-center justify-center cursor-pointer transition-all mx-auto relative select-none"
                                 :class="{
                                     'hover:bg-gray-200': d.isCurrentMonth,
                                     'text-gray-300 pointer-events-none font-normal': !d.isCurrentMonth,
                                     'font-semibold bg-gray-900 text-white': d.isToday,
                                     'font-normal text-gray-700': d.isCurrentMonth && !d.isToday
                                 }">
                                <span x-text="d.dayNumber"></span>
                                <template x-if="d.hasHoliday">
                                    <span class="w-1 h-1 rounded-full bg-gray-400 absolute bottom-0.5"></span>
                                </template>
                                <template x-if="d.hasClass && !d.hasHoliday">
                                    <span class="w-1 h-1 rounded-full bg-gray-800 absolute bottom-0.5"></span>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        {{-- Annual Academic Milestones & Holidays Summary Section --}}
        <div class="pt-5 border-t border-gray-100 space-y-3">
            <h4 class="font-semibold text-sm text-gray-900">Official Holidays &amp; Term Dates <span x-text="currentYear"></span></h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
                <template x-for="h in yearHolidays" :key="h.id">
                    <div class="p-3.5 rounded-xl bg-white border border-gray-200/80 shadow-2xs hover:border-gray-400 transition-all flex items-start gap-3">
                        <div class="w-10 h-10 rounded-lg bg-gray-100 text-gray-800 font-medium flex items-center justify-center shrink-0 text-xs">
                            <span x-text="formatDateShort(h.start)"></span>
                        </div>
                        <div class="space-y-0.5">
                            <span class="px-2 py-0.2 rounded-full text-[9px] font-medium capitalize bg-gray-100 text-gray-700"
                                  x-text="h.type.replace('_', ' ')"></span>
                            <h5 class="font-medium text-xs text-gray-900 truncate" x-text="h.title"></h5>
                            <p class="text-[11px] text-gray-500 font-normal line-clamp-1" x-text="h.description || 'Nationwide Event'"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- ── 5. AGENDA / LIST VIEW ── --}}
    <div x-show="activeView === 'agenda'" x-cloak class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-5 space-y-5">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100">
            <div>
                <h3 class="font-semibold text-lg text-gray-900">Upcoming Schedule Agenda</h3>
                <p class="text-xs text-gray-500 font-normal mt-0.5">Sequential chronological feed of your classes and key events.</p>
            </div>
        </div>

        <div class="space-y-4">
            <template x-for="grp in agendaGrouped" :key="grp.dateStr">
                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 rounded-xl bg-gray-100 text-xs font-medium text-gray-700" x-text="grp.formattedDate"></span>
                        <div class="h-px flex-1 bg-gray-100"></div>
                    </div>

                    <div class="space-y-2">
                        <template x-for="evt in grp.events" :key="evt.id">
                            <div @click="openEventDetails(evt)"
                                 class="p-3.5 rounded-xl border border-gray-200/80 bg-white shadow-2xs hover:border-gray-400 transition-all cursor-pointer flex items-center justify-between gap-3"
                                 :style="evt.child_theme ? ('border-left-width: 3px; border-left-color: ' + evt.child_theme.color) : ''">
                                <div class="flex items-center gap-3">
                                    <span class="w-2 h-2 rounded-full shrink-0 shadow-2xs" :style="'background-color: ' + (role === 'admin' ? '#475569' : (evt.color || (evt.child_theme ? evt.child_theme.color : '#2563eb')))"></span>
                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h4 class="font-medium text-xs sm:text-sm text-gray-900 leading-snug" x-text="evt.title"></h4>
                                            <template x-if="evt.child_name">
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-medium border shadow-2xs flex items-center gap-1"
                                                      :style="evt.child_theme ? ('background-color: ' + evt.child_theme.hex_bg + '; border-color: ' + evt.child_theme.hex_border + '; color: ' + evt.child_theme.hex_text) : 'background-color: #f1f5f9; color: #1e293b; border-color: #cbd5e1;'">
                                                    <span class="w-1.5 h-1.5 rounded-full" :style="'background-color: ' + (evt.child_theme?.color || '#475569')"></span>
                                                    <span x-text="evt.child_name"></span>
                                                </span>
                                            </template>
                                        </div>
                                        <p class="text-[11px] text-gray-500 font-normal mt-0.5">
                                            <span x-text="evt.time_str || 'All Day'"></span>
                                            <template x-if="evt.subject_name">
                                                <span> • <span class="font-medium text-gray-700" x-text="evt.subject_name"></span></span>
                                            </template>
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <template x-if="evt.is_rescheduled">
                                        <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 text-[10px] font-medium">Rescheduled</span>
                                    </template>
                                    <template x-if="evt.meeting_url">
                                        <a :href="evt.meeting_url" target="_blank" @click.stop class="bg-gray-900 hover:bg-black text-white py-1.5 px-3 rounded-lg text-xs font-medium shadow-2xs">Join</a>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ── EVENT DETAILS MODAL ── --}}
    <div x-show="showDetailsModal" x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showDetailsModal = false"
             class="bg-white rounded-2xl max-w-lg w-full p-5 sm:p-6 shadow-2xl border border-gray-200/80 space-y-4 transform transition-all">
            
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1">
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-medium uppercase tracking-wider bg-gray-100 text-gray-700"
                          x-text="selectedEvent?.type?.replace('_', ' ')"></span>
                    <h3 class="font-semibold text-lg text-gray-900 leading-tight" x-text="selectedEvent?.title"></h3>
                </div>
                <button @click="showDetailsModal = false" class="p-1 text-gray-400 hover:text-gray-700 rounded-lg text-base">✕</button>
            </div>

            <div class="space-y-2 text-xs bg-gray-50/70 p-3.5 rounded-xl border border-gray-200/70">
                <div class="flex items-center justify-between">
                    <span class="text-gray-500 font-normal">Date:</span>
                    <span class="font-medium text-gray-900" x-text="selectedEvent?.date_str || selectedEvent?.start"></span>
                </div>
                <template x-if="selectedEvent?.time_str">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 font-normal">Time:</span>
                        <span class="font-medium text-gray-900" x-text="selectedEvent?.time_str + ' (' + selectedEvent?.duration_minutes + ' min)'"></span>
                    </div>
                </template>
                <template x-if="selectedEvent?.subject_name">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 font-normal">Subject:</span>
                        <span class="font-medium text-gray-900" x-text="selectedEvent?.subject_name"></span>
                    </div>
                </template>
                <template x-if="selectedEvent?.teacher_name">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 font-normal">Instructor:</span>
                        <span class="font-medium text-gray-900" x-text="selectedEvent?.teacher_name"></span>
                    </div>
                </template>
                <template x-if="selectedEvent?.child_name">
                    <div class="flex items-center justify-between p-2 rounded-lg border bg-white border-gray-200">
                        <span class="text-gray-500 font-normal flex items-center gap-1.5">
                            <span>Student (Child):</span>
                        </span>
                        <span class="font-medium text-xs px-2.5 py-0.5 rounded-full bg-gray-50 text-gray-800 border border-gray-200 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full" :style="'background-color: ' + (selectedEvent?.child_theme?.color || '#475569')"></span>
                            <span x-text="selectedEvent?.child_name"></span>
                        </span>
                    </div>
                </template>
                <template x-if="selectedEvent?.attendance_status">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 font-normal">Attendance Status:</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-medium uppercase bg-gray-100 text-gray-700"
                              x-text="selectedEvent?.attendance_status"></span>
                    </div>
                </template>
            </div>

            <template x-if="selectedEvent?.is_rescheduled">
                <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900 space-y-1">
                    <div class="flex items-center gap-1.5 font-medium">
                        <span>⚠️ Class Rescheduled by Instructor</span>
                    </div>
                    <p>Previous Time: <span class="font-medium" x-text="selectedEvent?.original_scheduled_at"></span></p>
                    <p>Reason: <span class="italic font-normal" x-text="selectedEvent?.reschedule_reason"></span></p>
                </div>
            </template>

            <template x-if="selectedEvent?.type === 'public_holiday' && selectedEvent?.description">
                <p class="text-xs text-gray-600 bg-gray-50 p-3 rounded-xl border border-gray-200 font-normal" x-text="selectedEvent?.description"></p>
            </template>

            <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-gray-100">
                <div class="flex items-center gap-2">
                    <template x-if="selectedEvent?.type === 'live_class'">
                        <a :href="getGoogleCalendarLink(selectedEvent)" target="_blank" class="btn-ghost py-1.5 px-3 text-xs font-medium rounded-lg flex items-center gap-1 text-gray-600 hover:text-gray-900">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20a2 2 0 002 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z"/></svg>
                            <span>Google Cal</span>
                        </a>
                    </template>
                    <template x-if="selectedEvent?.class_id">
                        <a :href="exportIcsUrlTemplate.replace(':id', selectedEvent.class_id)" class="btn-ghost py-1.5 px-3 text-xs font-medium rounded-lg flex items-center gap-1 text-gray-600 hover:text-gray-900">
                            <span>Export .ics</span>
                        </a>
                    </template>
                </div>

                <div class="flex items-center gap-2">
                    <template x-if="(role === 'teacher' || role === 'admin') && selectedEvent?.can_edit && selectedEvent?.type === 'live_class'">
                        <button @click="openRescheduleModal(selectedEvent)" type="button" class="btn-secondary py-1.5 px-3 rounded-xl text-xs font-medium bg-amber-50 text-amber-800 border-amber-200 hover:bg-amber-100">
                            Reschedule
                        </button>
                    </template>

                    <template x-if="role === 'admin' && selectedEvent?.academic_id">
                        <button @click="deleteAcademicEvent(selectedEvent.academic_id)" type="button" class="btn-secondary py-1.5 px-3 text-xs font-medium text-rose-600 border-rose-200 hover:bg-rose-50 rounded-xl">
                            Delete Event
                        </button>
                    </template>

                    <template x-if="selectedEvent?.meeting_url">
                        <a :href="selectedEvent.meeting_url" target="_blank" class="bg-gray-900 hover:bg-black text-white py-1.5 px-4 rounded-xl text-xs font-medium shadow-2xs">
                            Join Class
                        </a>
                    </template>
                </div>
            </div>
        </div>
    </div>

    {{-- ── TEACHER / ADMIN RESCHEDULE MODAL ── --}}
    <template x-if="role === 'teacher' || role === 'admin'">
        <div x-show="showRescheduleModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="showRescheduleModal = false" class="bg-white rounded-2xl max-w-lg w-full p-5 sm:p-6 shadow-2xl border border-gray-200/80 space-y-4">
                <div class="flex items-start justify-between gap-4 pb-3 border-b border-gray-100">
                    <div>
                        <h3 class="font-semibold text-lg text-gray-900">Reschedule Class Session</h3>
                        <p class="text-xs text-gray-500 font-normal mt-0.5" x-text="rescheduleTarget?.title"></p>
                    </div>
                    <button @click="showRescheduleModal = false" class="text-gray-400 hover:text-gray-700 text-base p-1">✕</button>
                </div>

                <form @submit.prevent="submitReschedule()" class="space-y-4 text-xs font-medium">
                    <div>
                        <label class="block text-gray-700 mb-1 font-medium">New Date *</label>
                        <input type="date" x-model="rescheduleForm.scheduled_date" required min="{{ now()->format('Y-m-d') }}"
                               class="w-full rounded-xl border-gray-200 text-xs font-normal focus:ring-gray-900 focus:border-gray-900 p-2.5">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-gray-700 mb-1 font-medium">New Start Time *</label>
                            <input type="time" x-model="rescheduleForm.scheduled_time" required
                                   class="w-full rounded-xl border-gray-200 text-xs font-normal focus:ring-gray-900 focus:border-gray-900 p-2.5">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1 font-medium">Duration (Minutes)</label>
                            <input type="number" x-model="rescheduleForm.duration_minutes" min="15" max="300"
                                   class="w-full rounded-xl border-gray-200 text-xs font-normal focus:ring-gray-900 focus:border-gray-900 p-2.5">
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-1 font-medium">Reason for Rescheduling *</label>
                        <textarea x-model="rescheduleForm.reason" rows="3" required placeholder="Explain reason (e.g., Medical leave, urgent departmental commitment)..."
                                  class="w-full rounded-xl border-gray-200 text-xs font-normal focus:ring-gray-900 focus:border-gray-900 p-2.5"></textarea>
                        <p class="text-[10px] text-gray-400 font-normal mt-1">Direct notifications will be dispatched to teacher, enrolled students, and parents.</p>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                        <button type="button" @click="showRescheduleModal = false" class="btn-secondary py-2 px-4 rounded-xl text-xs font-medium">Cancel</button>
                        <button type="submit" :disabled="isSubmitting" class="bg-gray-900 hover:bg-black text-white py-2 px-5 rounded-xl text-xs font-medium shadow-xs">
                            <span x-show="!isSubmitting">Confirm &amp; Notify</span>
                            <span x-show="isSubmitting">Processing...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    {{-- ── QUICK CREATE SESSION MODAL (TEACHER) ── --}}
    @if($role === 'teacher')
    <div x-show="showQuickCreateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showQuickCreateModal = false" class="bg-white rounded-2xl max-w-lg w-full p-5 sm:p-6 shadow-2xl border border-gray-200/80 space-y-4">
            <div class="flex items-start justify-between gap-4 pb-3 border-b border-gray-100">
                <div>
                    <h3 class="font-semibold text-lg text-gray-900">Schedule Live Session</h3>
                    <p class="text-xs text-gray-500 font-normal mt-0.5">Add a new class to the academic calendar.</p>
                </div>
                <button @click="showQuickCreateModal = false" class="text-gray-400 hover:text-gray-700 text-base p-1">✕</button>
            </div>

            <form @submit.prevent="submitQuickCreate()" class="space-y-4 text-xs font-medium">
                <div>
                    <label class="block text-gray-700 mb-1 font-medium">Course *</label>
                    <select x-model="quickCreateForm.course_id" required class="w-full rounded-xl border-gray-200 text-xs font-normal p-2.5 focus:ring-gray-900 focus:border-gray-900">
                        <option value="">Select Course...</option>
                        @foreach($courses as $c)
                            <option value="{{ $c->id }}">{{ $c->title }} ({{ $c->subject->name ?? '' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700 mb-1 font-medium">Session Title *</label>
                    <input type="text" x-model="quickCreateForm.title" required placeholder="e.g. Masterclass: Trigonometric Identities"
                           class="w-full rounded-xl border-gray-200 text-xs font-normal p-2.5 focus:ring-gray-900 focus:border-gray-900">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-gray-700 mb-1 font-medium">Date *</label>
                        <input type="date" x-model="quickCreateForm.scheduled_date" required min="{{ now()->format('Y-m-d') }}"
                               class="w-full rounded-xl border-gray-200 text-xs font-normal p-2.5 focus:ring-gray-900 focus:border-gray-900">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1 font-medium">Start Time *</label>
                        <input type="time" x-model="quickCreateForm.scheduled_time" required
                               class="w-full rounded-xl border-gray-200 text-xs font-normal p-2.5 focus:ring-gray-900 focus:border-gray-900">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-gray-700 mb-1 font-medium">Duration (Minutes)</label>
                        <input type="number" x-model="quickCreateForm.duration_minutes" value="60" min="15" max="300"
                               class="w-full rounded-xl border-gray-200 text-xs font-normal p-2.5 focus:ring-gray-900 focus:border-gray-900">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1 font-medium">Meeting URL (Optional)</label>
                        <input type="url" x-model="quickCreateForm.meeting_url" placeholder="https://zoom.us/..."
                               class="w-full rounded-xl border-gray-200 text-xs font-normal p-2.5 focus:ring-gray-900 focus:border-gray-900">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                    <button type="button" @click="showQuickCreateModal = false" class="btn-secondary py-2 px-4 rounded-xl text-xs font-medium">Cancel</button>
                    <button type="submit" :disabled="isSubmitting" class="bg-gray-900 hover:bg-black text-white py-2 px-5 rounded-xl text-xs font-medium shadow-xs">
                        <span x-show="!isSubmitting">Schedule Session</span>
                        <span x-show="isSubmitting">Scheduling...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- ── UNIFIED ADMIN DATE CARD ACTION MODAL (CLICK ON ANY DATE CARD) ── --}}
    @if($role === 'admin')
    <div x-show="showAdminDateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showAdminDateModal = false" class="bg-white rounded-2xl max-w-lg w-full p-5 sm:p-6 shadow-2xl border border-gray-200/80 space-y-4">
            {{-- Modal Header with Clicked Date --}}
            <div class="flex items-start justify-between gap-4 pb-3 border-b border-gray-100">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-gray-100 text-gray-700 text-xs font-medium mb-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span x-text="formattedAdminSelectedDate"></span>
                    </div>
                    <h3 class="font-semibold text-lg text-gray-900">Manage Timetable for Date</h3>
                    <p class="text-xs text-gray-500 font-normal mt-0.5">Schedule a live class or publish an academic milestone / holiday for this day.</p>
                </div>
                <button @click="showAdminDateModal = false" class="text-gray-400 hover:text-gray-700 text-base p-1">✕</button>
            </div>

            {{-- Tab Switcher: Schedule Class vs Add Milestone --}}
            <div class="grid grid-cols-2 p-1 bg-gray-100 rounded-xl text-xs">
                <button type="button" @click="adminDateModalTab = 'class'"
                        :class="adminDateModalTab === 'class' ? 'bg-white text-gray-900 shadow-xs font-medium' : 'text-gray-600 hover:text-gray-900 font-normal'"
                        class="py-2 rounded-lg transition-all flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    <span>Schedule Class</span>
                </button>
                <button type="button" @click="adminDateModalTab = 'milestone'"
                        :class="adminDateModalTab === 'milestone' ? 'bg-white text-gray-900 shadow-xs font-medium' : 'text-gray-600 hover:text-gray-900 font-normal'"
                        class="py-2 rounded-lg transition-all flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Add Milestone / Holiday</span>
                </button>
            </div>

            {{-- 1. TAB: SCHEDULE CLASS --}}
            <div x-show="adminDateModalTab === 'class'">
                <form @submit.prevent="submitQuickCreate()" class="space-y-4 text-xs font-medium">
                    <div>
                        <label class="block text-gray-700 mb-1 font-medium">Course *</label>
                        <select x-model="quickCreateForm.course_id" required class="w-full rounded-xl border-gray-200 text-xs font-normal p-2.5 focus:ring-gray-900 focus:border-gray-900">
                            <option value="">Select Course...</option>
                            @foreach($courses as $c)
                                <option value="{{ $c->id }}">{{ $c->title }} ({{ $c->subject->name ?? '' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-1 font-medium">Assign Teacher (Optional)</label>
                        <select x-model="quickCreateForm.teacher_id" class="w-full rounded-xl border-gray-200 text-xs font-normal p-2.5 focus:ring-gray-900 focus:border-gray-900">
                            <option value="">Auto-assign (Course Teacher)</option>
                            @foreach($teachers as $t)
                                <option value="{{ $t->id }}">{{ $t->user->name ?? 'Teacher #' . $t->id }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-1 font-medium">Session Title *</label>
                        <input type="text" x-model="quickCreateForm.title" required placeholder="e.g. Masterclass: Advanced Trigonometry"
                               class="w-full rounded-xl border-gray-200 text-xs font-normal p-2.5 focus:ring-gray-900 focus:border-gray-900">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-gray-700 mb-1 font-medium">Date *</label>
                            <input type="date" x-model="quickCreateForm.scheduled_date" required min="{{ now()->format('Y-m-d') }}"
                                   class="w-full rounded-xl border-gray-200 text-xs font-normal p-2.5 focus:ring-gray-900 focus:border-gray-900">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1 font-medium">Start Time *</label>
                            <input type="time" x-model="quickCreateForm.scheduled_time" required
                                   class="w-full rounded-xl border-gray-200 text-xs font-normal p-2.5 focus:ring-gray-900 focus:border-gray-900">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-gray-700 mb-1 font-medium">Duration (Minutes)</label>
                            <input type="number" x-model="quickCreateForm.duration_minutes" value="60" min="15" max="300"
                                   class="w-full rounded-xl border-gray-200 text-xs font-normal p-2.5 focus:ring-gray-900 focus:border-gray-900">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1 font-medium">Meeting URL (Optional)</label>
                            <input type="url" x-model="quickCreateForm.meeting_url" placeholder="https://zoom.us/..."
                                   class="w-full rounded-xl border-gray-200 text-xs font-normal p-2.5 focus:ring-gray-900 focus:border-gray-900">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                        <button type="button" @click="showAdminDateModal = false" class="btn-secondary py-2 px-4 rounded-xl text-xs font-medium">Cancel</button>
                        <button type="submit" :disabled="isSubmitting" class="bg-gray-900 hover:bg-black text-white py-2 px-5 rounded-xl text-xs font-medium shadow-xs">
                            <span x-show="!isSubmitting">Schedule Live Class</span>
                            <span x-show="isSubmitting">Scheduling...</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- 2. TAB: ADD MILESTONE / HOLIDAY --}}
            <div x-show="adminDateModalTab === 'milestone'">
                <form @submit.prevent="submitAcademicEvent()" class="space-y-4 text-xs font-medium">
                    <div>
                        <label class="block text-gray-700 mb-1 font-medium">Event Type *</label>
                        <select x-model="academicEventForm.event_type" required class="w-full rounded-xl border-gray-200 text-xs font-normal p-2.5 focus:ring-gray-900 focus:border-gray-900">
                            <option value="public_holiday">Public Holiday (National / State)</option>
                            <option value="school_break">School Holiday / Mid-Term Break</option>
                            <option value="exam_period">Examination Period</option>
                            <option value="academic_term">Academic Term / Semester Date</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-1 font-medium">Event Title *</label>
                        <input type="text" x-model="academicEventForm.title" required placeholder="e.g. Hari Kebangsaan (National Day)"
                               class="w-full rounded-xl border-gray-200 text-xs font-normal p-2.5 focus:ring-gray-900 focus:border-gray-900">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-gray-700 mb-1 font-medium">Start Date *</label>
                            <input type="date" x-model="academicEventForm.start_date" required
                                   class="w-full rounded-xl border-gray-200 text-xs font-normal p-2.5 focus:ring-gray-900 focus:border-gray-900">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1 font-medium">End Date (Optional)</label>
                            <input type="date" x-model="academicEventForm.end_date" :min="academicEventForm.start_date"
                                   class="w-full rounded-xl border-gray-200 text-xs font-normal p-2.5 focus:ring-gray-900 focus:border-gray-900">
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-1 font-medium">Description / Notes (Optional)</label>
                        <textarea x-model="academicEventForm.description" rows="2" placeholder="e.g. Official nationwide holiday. Classes will be rescheduled."
                                  class="w-full rounded-xl border-gray-200 text-xs font-normal p-2.5 focus:ring-gray-900 focus:border-gray-900"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                        <button type="button" @click="showAdminDateModal = false" class="btn-secondary py-2 px-4 rounded-xl text-xs font-medium">Cancel</button>
                        <button type="submit" :disabled="isSubmitting" class="bg-gray-900 hover:bg-black text-white py-2 px-5 rounded-xl text-xs font-medium shadow-xs">
                            <span x-show="!isSubmitting">Publish Milestone</span>
                            <span x-show="isSubmitting">Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Alpine.js Engine --}}
<script>
function googleCalendar(config) {
    return {
        role: config.role,
        csrfToken: config.csrfToken,
        eventsApiUrl: config.eventsApiUrl,
        rescheduleUrlTemplate: config.rescheduleUrlTemplate,
        quickCreateUrl: config.quickCreateUrl,
        academicEventsUrl: config.academicEventsUrl,
        academicEventsDeleteTemplate: config.academicEventsDeleteTemplate,
        exportIcsUrlTemplate: config.exportIcsUrlTemplate,

        // State
        activeView: 'month', // 'month', 'week', 'day', 'annual', 'agenda'
        currentDate: new Date(),
        currentYear: new Date().getFullYear(),
        selectedMobileDateStr: (new Date()).toISOString().slice(0, 10),
        events: [],
        isLoading: false,
        isSubmitting: false,

        // Admin Filters
        selectedTeacherId: '',
        selectedCourseId: '',

        // Filters
        filters: {
            liveClasses: true,
            publicHolidays: true,
            schoolBreaks: true,
            examPeriods: true,
        },

        // Modals
        showDetailsModal: false,
        selectedEvent: null,
        showRescheduleModal: false,
        rescheduleTarget: null,
        rescheduleForm: {
            scheduled_date: '',
            scheduled_time: '',
            duration_minutes: 60,
            reason: '',
        },
        showQuickCreateModal: false,
        quickCreateForm: {
            course_id: '',
            teacher_id: '',
            title: '',
            scheduled_date: '',
            scheduled_time: '10:00',
            duration_minutes: 60,
            meeting_url: '',
        },
        showAcademicEventModal: false,
        academicEventForm: {
            title: '',
            description: '',
            event_type: 'public_holiday',
            start_date: '',
            end_date: '',
            is_all_day: true,
            color: '#ef4444',
        },

        // Admin Unified Date Action Modal
        showAdminDateModal: false,
        adminDateModalTab: 'class', // 'class' or 'milestone'
        adminSelectedDate: '',
        adminSelectedTime: '10:00',

        // Hourly slots for week view
        timeSlots: [8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21],

        initCalendar() {
            this.fetchEvents();
        },

        async fetchEvents() {
            this.isLoading = true;
            try {
                const year = this.currentYear;
                let url = `${this.eventsApiUrl}?year=${year}&role=${this.role}`;
                if (this.role === 'admin') {
                    if (this.selectedTeacherId) url += `&teacher_id=${this.selectedTeacherId}`;
                    if (this.selectedCourseId) url += `&course_id=${this.selectedCourseId}`;
                }

                const res = await fetch(url, { credentials: 'same-origin' });
                const json = await res.json();
                if (json.success) {
                    this.events = json.events || [];
                }
            } catch (err) {
                console.error('Failed to load events:', err);
            } finally {
                this.isLoading = false;
            }
        },

        // Navigation
        navigate(dir) {
            const cur = new Date(this.currentDate);
            if (dir === 'today') {
                this.currentDate = new Date();
                this.currentYear = this.currentDate.getFullYear();
                this.selectedMobileDateStr = this.formatDateYMD(this.currentDate);
            } else {
                const step = dir === 'next' ? 1 : -1;
                if (this.activeView === 'month') {
                    cur.setMonth(cur.getMonth() + step);
                } else if (this.activeView === 'week') {
                    cur.setDate(cur.getDate() + (step * 7));
                } else if (this.activeView === 'day') {
                    cur.setDate(cur.getDate() + step);
                } else if (this.activeView === 'annual') {
                    this.changeYear(step);
                    return;
                } else if (this.activeView === 'agenda') {
                    cur.setMonth(cur.getMonth() + step);
                }
                this.currentDate = cur;
                this.selectedMobileDateStr = this.formatDateYMD(cur);
                if (cur.getFullYear() !== this.currentYear) {
                    this.currentYear = cur.getFullYear();
                    this.fetchEvents();
                }
            }
        },

        changeYear(step) {
            this.currentYear += step;
            this.currentDate.setFullYear(this.currentYear);
            this.selectedMobileDateStr = this.formatDateYMD(this.currentDate);
            this.fetchEvents();
        },

        setView(v) {
            this.activeView = v;
        },

        selectMobileDate(dStr) {
            this.selectedMobileDateStr = dStr;
            this.currentDate = new Date(dStr + 'T00:00:00');
        },

        // Mobile Selected Day Helpers
        get selectedMobileDayEvents() {
            const dStr = this.selectedMobileDateStr;
            return this.filteredEvents.filter(evt => {
                const evtStart = (evt.date_str || evt.start || '').slice(0, 10);
                const evtEnd = (evt.end || evt.start || '').slice(0, 10);
                return dStr >= evtStart && dStr <= evtEnd;
            });
        },

        get selectedMobileDayClasses() {
            return this.selectedMobileDayEvents.filter(e => e.type === 'live_class');
        },

        get selectedMobileDayHolidays() {
            return this.selectedMobileDayEvents.filter(e => e.type === 'public_holiday' || e.type === 'school_break');
        },

        get formattedSelectedMobileDate() {
            if (!this.selectedMobileDateStr) return '';
            const d = new Date(this.selectedMobileDateStr + 'T00:00:00');
            return d.toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });
        },

        // Month computation (42 days)
        get monthDays() {
            const year = this.currentDate.getFullYear();
            const month = this.currentDate.getMonth();
            const firstDayOfMonth = new Date(year, month, 1);
            
            let startDay = firstDayOfMonth.getDay() - 1;
            if (startDay === -1) startDay = 6;

            const days = [];
            const calStart = new Date(year, month, 1 - startDay);

            for (let i = 0; i < 42; i++) {
                const d = new Date(calStart);
                d.setDate(calStart.getDate() + i);

                const dateStr = this.formatDateYMD(d);
                const isCurrentMonth = d.getMonth() === month;
                const isToday = this.isSameDay(d, new Date());

                const dayEvents = this.filteredEvents.filter(evt => {
                    const evtStart = (evt.date_str || evt.start || '').slice(0, 10);
                    const evtEnd = (evt.end || evt.start || '').slice(0, 10);
                    return dateStr >= evtStart && dateStr <= evtEnd;
                });

                days.push({
                    date: d,
                    dateStr: dateStr,
                    dayNumber: d.getDate(),
                    isCurrentMonth: isCurrentMonth,
                    isToday: isToday,
                    events: dayEvents,
                });
            }
            return days;
        },

        // Week computation (7 days)
        get weekDays() {
            const cur = new Date(this.currentDate);
            let dayOfWeek = cur.getDay() - 1;
            if (dayOfWeek === -1) dayOfWeek = 6;

            const monday = new Date(cur);
            monday.setDate(cur.getDate() - dayOfWeek);

            const dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            const days = [];

            for (let i = 0; i < 7; i++) {
                const d = new Date(monday);
                d.setDate(monday.getDate() + i);
                const dateStr = this.formatDateYMD(d);

                const dEvents = this.filteredEvents.filter(evt => {
                    const evtStart = (evt.date_str || evt.start || '').slice(0, 10);
                    const evtEnd = (evt.end || evt.start || '').slice(0, 10);
                    return dateStr >= evtStart && dateStr <= evtEnd;
                });

                const allDayEvts = dEvents.filter(e => e.is_all_day || e.type === 'public_holiday' || e.type === 'school_break');

                days.push({
                    date: d,
                    dateStr: dateStr,
                    dayName: dayNames[i],
                    dayNumber: d.getDate(),
                    isToday: this.isSameDay(d, new Date()),
                    events: dEvents,
                    allDayEvents: allDayEvts,
                });
            }
            return days;
        },

        get filteredEvents() {
            return this.events.filter(evt => {
                if (evt.type === 'live_class' && !this.filters.liveClasses) return false;
                if (evt.type === 'public_holiday' && !this.filters.publicHolidays) return false;
                if (evt.type === 'school_break' && !this.filters.schoolBreaks) return false;
                if ((evt.type === 'exam_period' || evt.type === 'assignment') && !this.filters.examPeriods) return false;
                return true;
            });
        },

        getEventsForHour(dayEvts, hour) {
            return dayEvts.filter(e => {
                if (e.is_all_day || e.type === 'public_holiday') return false;
                if (!e.start) return false;
                const d = new Date(e.start);
                return d.getHours() === hour;
            });
        },

        get calendarTitle() {
            if (this.activeView === 'annual') {
                return `Year ${this.currentYear}`;
            }
            const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            return `${months[this.currentDate.getMonth()]} ${this.currentDate.getFullYear()}`;
        },

        get dayViewTitle() {
            const opts = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
            return this.currentDate.toLocaleDateString('en-GB', opts);
        },

        get formattedAdminSelectedDate() {
            if (!this.adminSelectedDate) return '';
            const parts = this.adminSelectedDate.split('-');
            const d = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
            return d.toLocaleDateString('en-US', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });
        },

        get dayClasses() {
            const dateStr = this.formatDateYMD(this.currentDate);
            return this.filteredEvents.filter(e => {
                const s = (e.date_str || e.start || '').slice(0, 10);
                return s === dateStr && e.type === 'live_class';
            });
        },

        get dayHolidays() {
            const dateStr = this.formatDateYMD(this.currentDate);
            return this.filteredEvents.filter(e => {
                const s = (e.date_str || e.start || '').slice(0, 10);
                const end = (e.end || e.start || '').slice(0, 10);
                return dateStr >= s && dateStr <= end && (e.type === 'public_holiday' || e.type === 'school_break');
            });
        },

        get yearHolidays() {
            return this.events.filter(e => e.type === 'public_holiday' || e.type === 'school_break' || e.type === 'exam_period');
        },

        get agendaGrouped() {
            const groups = {};
            const sorted = [...this.filteredEvents].sort((a, b) => (a.start > b.start ? 1 : -1));

            sorted.forEach(evt => {
                const dStr = (evt.date_str || evt.start || '').slice(0, 10);
                if (!groups[dStr]) {
                    const d = new Date(dStr + 'T00:00:00');
                    groups[dStr] = {
                        dateStr: dStr,
                        formattedDate: d.toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' }),
                        events: [],
                    };
                }
                groups[dStr].events.push(evt);
            });

            return Object.values(groups).slice(0, 25);
        },

        getEventChipClass(evt) {
            if (this.role === 'admin') {
                if (evt.is_rescheduled) {
                    return 'bg-amber-50/80 border-amber-200 text-amber-900 font-medium';
                }
                if (evt.type === 'public_holiday' || evt.type === 'school_break' || evt.type === 'exam_period') {
                    return 'bg-gray-100 border-gray-200 text-gray-700 font-medium';
                }
                return 'bg-white border-gray-200 text-gray-800 hover:bg-gray-50 font-medium shadow-2xs';
            }
            if (evt.type === 'public_holiday') {
                return 'bg-rose-50 border-rose-200 text-rose-700';
            }
            if (evt.type === 'school_break') {
                return 'bg-amber-50 border-amber-200 text-amber-800';
            }
            if (evt.type === 'exam_period') {
                return 'bg-purple-50 border-purple-200 text-purple-800';
            }
            if (evt.type === 'assignment') {
                return 'bg-amber-50 border-amber-200 text-amber-700';
            }
            if (evt.is_rescheduled) {
                return 'bg-amber-50 border-amber-300 text-amber-900 font-medium';
            }
            if (evt.child_theme && evt.child_theme.bg) {
                return `${evt.child_theme.bg} ${evt.child_theme.border} ${evt.child_theme.text}`;
            }
            return 'bg-blue-50 border-blue-200 text-blue-800';
        },

        getEventChipStyle(evt) {
            if (evt.child_theme && evt.child_theme.hex_bg) {
                return {
                    backgroundColor: evt.child_theme.hex_bg,
                    borderColor: evt.child_theme.hex_border,
                    color: evt.child_theme.hex_text,
                };
            }
            return {};
        },

        openDayModal(day) {
            this.jumpToDate(day.dateStr);
        },

        getMiniMonthDays(year, month) {
            const first = new Date(year, month, 1);
            let startDay = first.getDay() - 1;
            if (startDay === -1) startDay = 6;

            const days = [];
            const calStart = new Date(year, month, 1 - startDay);

            for (let i = 0; i < 35; i++) {
                const d = new Date(calStart);
                d.setDate(calStart.getDate() + i);

                const dateStr = this.formatDateYMD(d);
                const isCurrentMonth = d.getMonth() === month;

                const hasHoliday = this.events.some(e => {
                    const s = (e.date_str || e.start || '').slice(0, 10);
                    const end = (e.end || e.start || '').slice(0, 10);
                    return (e.type === 'public_holiday' || e.type === 'school_break') && dateStr >= s && dateStr <= end;
                });

                const hasClass = this.events.some(e => {
                    const s = (e.date_str || e.start || '').slice(0, 10);
                    return e.type === 'live_class' && s === dateStr;
                });

                days.push({
                    dateStr: dateStr,
                    dayNumber: d.getDate(),
                    isCurrentMonth: isCurrentMonth,
                    isToday: this.isSameDay(d, new Date()),
                    hasHoliday: hasHoliday,
                    hasClass: hasClass,
                });
            }
            return days;
        },

        getMonthName(idx) {
            const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            return months[idx];
        },

        getEventCountForMonth(mIdx) {
            return this.events.filter(e => {
                const s = (e.date_str || e.start || '').slice(0, 7);
                const target = `${this.currentYear}-${String(mIdx + 1).padStart(2, '0')}`;
                return s === target;
            }).length;
        },

        jumpToMonth(mIdx) {
            this.currentDate = new Date(this.currentYear, mIdx, 1);
            this.selectedMobileDateStr = this.formatDateYMD(this.currentDate);
            this.activeView = 'month';
        },

        jumpToDate(dStr) {
            this.currentDate = new Date(dStr + 'T00:00:00');
            this.selectedMobileDateStr = dStr;
            this.activeView = 'day';
        },

        dayCellClicked(day) {
            if (this.role === 'admin') {
                this.openAdminDateModal(day.dateStr, '10:00', 'class');
            } else if (this.role === 'teacher') {
                this.quickCreateForm.scheduled_date = day.dateStr;
                this.showQuickCreateModal = true;
            } else {
                this.currentDate = new Date(day.date);
                this.selectedMobileDateStr = day.dateStr;
                this.activeView = 'day';
            }
        },

        timeSlotClicked(dateStr, hour) {
            const timeStr = `${String(hour).padStart(2, '0')}:00`;
            if (this.role === 'admin') {
                this.openAdminDateModal(dateStr, timeStr, 'class');
            } else if (this.role === 'teacher') {
                this.quickCreateForm.scheduled_date = dateStr;
                this.quickCreateForm.scheduled_time = timeStr;
                this.showQuickCreateModal = true;
            }
        },

        openAdminDateModal(dateStr, timeStr = '10:00', tab = 'class') {
            this.adminSelectedDate = dateStr;
            this.adminSelectedTime = timeStr;
            this.adminDateModalTab = tab;

            this.quickCreateForm.course_id = '';
            this.quickCreateForm.teacher_id = '';
            this.quickCreateForm.title = '';
            this.quickCreateForm.scheduled_date = dateStr;
            this.quickCreateForm.scheduled_time = timeStr;
            this.quickCreateForm.duration_minutes = 60;
            this.quickCreateForm.meeting_url = '';

            this.academicEventForm = {
                title: '',
                description: '',
                event_type: 'public_holiday',
                start_date: dateStr,
                end_date: dateStr,
                is_all_day: true,
                color: '#ef4444',
            };

            this.showAdminDateModal = true;
        },

        openEventDetails(evt) {
            this.selectedEvent = evt;
            this.showDetailsModal = true;
        },

        openRescheduleModal(evt) {
            this.showDetailsModal = false;
            this.rescheduleTarget = evt;
            this.rescheduleForm.scheduled_date = (evt.date_str || evt.start || '').slice(0, 10);
            this.rescheduleForm.scheduled_time = evt.time_str ? this.convertTo24h(evt.time_str) : '10:00';
            this.rescheduleForm.duration_minutes = evt.duration_minutes || 60;
            this.rescheduleForm.reason = '';
            this.showRescheduleModal = true;
        },

        openQuickCreateModal() {
            this.quickCreateForm.course_id = '';
            this.quickCreateForm.teacher_id = '';
            this.quickCreateForm.title = '';
            this.quickCreateForm.scheduled_date = this.formatDateYMD(new Date());
            this.quickCreateForm.scheduled_time = '10:00';
            this.quickCreateForm.duration_minutes = 60;
            this.quickCreateForm.meeting_url = '';
            this.showQuickCreateModal = true;
        },

        openAcademicEventModal() {
            this.academicEventForm = {
                title: '',
                description: '',
                event_type: 'public_holiday',
                start_date: this.formatDateYMD(new Date()),
                end_date: '',
                is_all_day: true,
                color: '#ef4444',
            };
            this.showAcademicEventModal = true;
        },

        async submitReschedule() {
            if (!this.rescheduleTarget) return;
            this.isSubmitting = true;

            const url = this.rescheduleUrlTemplate.replace(':id', this.rescheduleTarget.class_id);
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify(this.rescheduleForm),
                });

                const json = await res.json();
                if (json.success) {
                    this.showRescheduleModal = false;
                    alert(json.message || 'Session rescheduled successfully!');
                    this.fetchEvents();
                } else {
                    alert(json.message || 'Could not reschedule. Check date or permissions.');
                }
            } catch (err) {
                console.error('Error submitting reschedule:', err);
                alert('An error occurred while rescheduling the class.');
            } finally {
                this.isSubmitting = false;
            }
        },

        async submitQuickCreate() {
            this.isSubmitting = true;
            try {
                const res = await fetch(this.quickCreateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify(this.quickCreateForm),
                });

                const json = await res.json();
                if (json.success) {
                    this.showQuickCreateModal = false;
                    this.showAdminDateModal = false;
                    alert('Live class scheduled successfully!');
                    this.fetchEvents();
                } else {
                    alert(json.message || 'Could not schedule class.');
                }
            } catch (err) {
                console.error(err);
                alert('Error creating class.');
            } finally {
                this.isSubmitting = false;
            }
        },

        async submitAcademicEvent() {
            this.isSubmitting = true;
            try {
                const res = await fetch(this.academicEventsUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify(this.academicEventForm),
                });

                const json = await res.json();
                if (json.success) {
                    this.showAcademicEventModal = false;
                    this.showAdminDateModal = false;
                    alert('Academic milestone published successfully!');
                    this.fetchEvents();
                } else {
                    alert(json.message || 'Failed to save milestone.');
                }
            } catch (err) {
                console.error(err);
                alert('Error saving academic event.');
            } finally {
                this.isSubmitting = false;
            }
        },

        async deleteAcademicEvent(id) {
            if (!confirm('Are you sure you want to delete this academic milestone? It will be removed from all student, teacher, and parent calendars.')) {
                return;
            }

            try {
                const url = this.academicEventsDeleteTemplate.replace(':id', id);
                const res = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                });

                const json = await res.json();
                if (json.success) {
                    this.showDetailsModal = false;
                    alert('Event deleted successfully.');
                    this.fetchEvents();
                } else {
                    alert(json.message || 'Failed to delete event.');
                }
            } catch (err) {
                console.error('Error deleting event:', err);
                alert('Error deleting event.');
            }
        },

        getGoogleCalendarLink(evt) {
            if (!evt) return '#';
            const title = encodeURIComponent(evt.title || 'Olympian Scholars Class');
            const details = encodeURIComponent(
                `Course: ${evt.course_title || evt.subject_name}\nInstructor: ${evt.teacher_name || ''}\nJoin: ${evt.meeting_url || 'https://olympianscholars.com'}`
            );
            
            let startIso = '';
            let endIso = '';
            try {
                startIso = new Date(evt.start).toISOString().replace(/-|:|\.\d\d\d/g, '');
                endIso = new Date(evt.end).toISOString().replace(/-|:|\.\d\d\d/g, '');
            } catch (e) {
                startIso = '';
                endIso = '';
            }

            return `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${title}&dates=${startIso}/${endIso}&details=${details}&location=Online`;
        },

        formatDateYMD(d) {
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${y}-${m}-${day}`;
        },

        formatDateShort(dStr) {
            if (!dStr) return '';
            const d = new Date(dStr + 'T00:00:00');
            return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
        },

        formatHour(h) {
            const ampm = h >= 12 ? 'PM' : 'AM';
            const displayHour = h % 12 === 0 ? 12 : h % 12;
            return `${displayHour} ${ampm}`;
        },

        convertTo24h(timeStr) {
            const parts = timeStr.trim().split(' ');
            if (parts.length < 2) return timeStr;
            let [h, m] = parts[0].split(':').map(Number);
            const modifier = parts[1].toUpperCase();
            if (modifier === 'PM' && h < 12) h += 12;
            if (modifier === 'AM' && h === 12) h = 0;
            return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
        },

        isSameDay(d1, d2) {
            return d1.getFullYear() === d2.getFullYear() &&
                   d1.getMonth() === d2.getMonth() &&
                   d1.getDate() === d2.getDate();
        }
    };
}
</script>
