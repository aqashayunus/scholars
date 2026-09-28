<x-teacher-layout>
    <x-slot name="title">My Classes & Schedule - Teacher Portal</x-slot>

    <div x-data="{ 
        activeTab: 'courses',
        
        // Add Class Modal State
        showAddClassModal: false,
        
        // Add Task Modal State
        showAddTaskModal: false,
        addTaskActionUrl: '',
        selectedCourseName: '',
        
        // Cancel Class Modal State
        showCancelModal: false,
        cancelActionUrl: '',
        selectedClassTitle: '',
        
        // Reschedule Class Modal State
        showRescheduleModal: false,
        rescheduleActionUrl: '',
        selectedClassTitleReschedule: '',
        
        openAddTask(courseId, courseName) {
            this.addTaskActionUrl = `/teacher/classes/${courseId}/tasks`;
            this.selectedCourseName = courseName;
            this.showAddTaskModal = true;
        },
        
        openCancel(classId, classTitle) {
            this.cancelActionUrl = `/teacher/classes/${classId}/request-cancel`;
            this.selectedClassTitle = classTitle;
            this.showCancelModal = true;
        },
        
        openReschedule(classId, classTitle) {
            this.rescheduleActionUrl = `/teacher/classes/${classId}/request-reschedule`;
            this.selectedClassTitleReschedule = classTitle;
            this.showRescheduleModal = true;
        }
    }" class="space-y-6">

        {{-- ── ALERT MESSAGES ── --}}
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-3xl text-xs font-semibold text-emerald-800 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button class="text-emerald-600 hover:text-emerald-800 font-bold ml-4">✕</button>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 rounded-3xl text-xs font-semibold text-rose-800 space-y-1 shadow-xs">
                <div class="flex items-center gap-2 mb-1">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <strong>Whoops! Please fix the errors below:</strong>
                </div>
                <ul class="list-disc list-inside pl-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ── HEADER SECTION ── --}}
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="font-heading font-black text-2xl text-gray-900">My Classes & Schedules</h1>
                <p class="text-xs text-gray-500 font-medium mt-0.5">Add classes, manage assignments, and submit rescheduling or cancellation requests.</p>
            </div>

            <button @click="showAddClassModal = true" class="py-2.5 px-5 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl text-xs font-extrabold shadow-sm transition-all cursor-pointer shrink-0 inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add Live Session</span>
            </button>
        </div>

        {{-- ── 1. TOP 3 KPI SUMMARY CARDS ── --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            
            {{-- Active Course Sections --}}
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-black text-gray-900">{{ $teacher->courses->count() }}</p>
                    <p class="text-xs font-bold text-gray-400">Course Sections</p>
                </div>
            </div>

            {{-- Total Students --}}
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-black text-gray-900">{{ $teacher->courses->sum(fn($c) => $c->enrollments->count()) }}</p>
                    <p class="text-xs font-bold text-gray-400">Total Enrolled Students</p>
                </div>
            </div>

            {{-- Upcoming Classes count --}}
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 border border-purple-100 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-black text-gray-900">{{ $upcomingClasses->count() }}</p>
                    <p class="text-xs font-bold text-gray-400">Scheduled Live Sessions</p>
                </div>
            </div>
        </div>

        {{-- ── 2. SUB-TABS SWITCHER ── --}}
        <div class="bg-white rounded-2xl p-1.5 border border-gray-100 shadow-sm flex flex-wrap gap-1">
            <button @click="activeTab = 'courses'"
                    :class="activeTab === 'courses' ? 'bg-blue-600 text-white shadow-sm font-extrabold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 font-bold'"
                    class="px-5 py-2.5 rounded-xl text-xs transition-all cursor-pointer">
                Course Sections & Tasks
            </button>
            <button @click="activeTab = 'upcoming'"
                    :class="activeTab === 'upcoming' ? 'bg-blue-600 text-white shadow-sm font-extrabold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 font-bold'"
                    class="px-5 py-2.5 rounded-xl text-xs transition-all cursor-pointer">
                Scheduled Live Sessions ({{ $upcomingClasses->count() }})
            </button>
            <button @click="activeTab = 'past'"
                    :class="activeTab === 'past' ? 'bg-blue-600 text-white shadow-sm font-extrabold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 font-bold'"
                    class="px-5 py-2.5 rounded-xl text-xs transition-all cursor-pointer">
                Past Sessions
            </button>
        </div>

        {{-- ── 3. COURSE SECTIONS TAB ── --}}
        <div x-show="activeTab === 'courses'" class="space-y-4">
            <div class="space-y-3">
                @forelse($teacher->courses as $course)
                    <div class="p-5 bg-white rounded-3xl border border-gray-100 shadow-sm flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div class="flex items-start sm:items-center gap-4">
                            @php
                                $subjectName = strtolower($course->subject->name ?? 'general');
                                $iconColor = 'bg-blue-600';
                                if (str_contains($subjectName, 'physics') || str_contains($subjectName, 'science')) { 
                                    $iconColor = 'bg-purple-600';
                                } elseif (str_contains($subjectName, 'english')) { 
                                    $iconColor = 'bg-emerald-500';
                                }
                            @endphp
                            <div class="w-12 h-12 rounded-2xl {{ $iconColor }} text-white font-black text-lg flex items-center justify-center shrink-0 shadow-sm">
                                {{ strtoupper(substr($course->subject->name ?? 'G', 0, 2)) }}
                            </div>
                            <div>
                                <h4 class="font-heading font-black text-base text-gray-900">{{ $course->level }} {{ $course->subject->name ?? '' }} – {{ $course->title }}</h4>
                                <p class="text-xs text-gray-400 font-medium mt-0.5">{{ $course->enrollments->count() }} Students • {{ $course->schedule ?? 'TBA' }}</p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-lg bg-blue-50 text-blue-700">
                                        {{ $course->assignments->count() }} Tasks Assigned
                                    </span>
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-lg bg-purple-50 text-purple-700">
                                        {{ $course->liveClasses->count() }} Total Sessions
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0 sm:justify-end border-t lg:border-t-0 pt-3 lg:pt-0 border-gray-50">
                            <button @click="openAddTask({{ $course->id }}, '{{ $course->level }} {{ $course->subject->name ?? '' }}')" class="py-2 px-3.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-extrabold text-xs rounded-xl border border-emerald-200/50 transition-colors cursor-pointer">
                                + Assign Task
                            </button>
                            <a href="{{ route('teacher.classes.show', $course->id) }}" class="py-2 px-3.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-xl transition-colors cursor-pointer">
                                Class Workspace
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center bg-white rounded-3xl border border-gray-100 text-gray-500 font-semibold">No assigned courses found.</div>
                @endforelse
            </div>
        </div>

        {{-- ── 4. UPCOMING SESSIONS TAB ── --}}
        <div x-show="activeTab === 'upcoming'" x-cloak class="space-y-4">
            <div class="space-y-3">
                @forelse($upcomingClasses as $lc)
                    <div class="p-5 bg-white rounded-3xl border border-gray-100 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-start sm:items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex flex-col items-center justify-center shrink-0 font-black text-sm border border-blue-100 shadow-3xs">
                                <span>{{ $lc->scheduled_at->format('d') }}</span>
                                <span class="text-[9px] uppercase tracking-wider font-extrabold mt-0.5">{{ $lc->scheduled_at->format('M') }}</span>
                            </div>
                            <div>
                                <h4 class="font-heading font-black text-base text-gray-900">{{ $lc->title }}</h4>
                                <p class="text-xs text-gray-500 font-semibold mt-0.5">{{ $lc->course->level }} {{ $lc->course->subject->name ?? '' }}</p>
                                <p class="text-xs text-gray-400 font-medium mt-0.5">{{ $lc->scheduled_at->format('l, g:i A') }} ({{ $lc->duration_minutes ?? '60' }} mins)</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 border-t md:border-t-0 pt-3 md:pt-0 border-gray-50">
                            @if($lc->meeting_url)
                                <a href="{{ $lc->meeting_url }}" target="_blank" class="py-1.5 px-3 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200/50 rounded-xl font-bold text-xs">
                                    Start Meeting
                                </a>
                            @endif

                            {{-- Reschedule Action --}}
                            <button @click="openReschedule({{ $lc->id }}, '{{ $lc->title }}')" class="py-1.5 px-3 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200/50 rounded-xl font-bold text-xs cursor-pointer">
                                Reschedule
                            </button>

                            {{-- Cancel Action --}}
                            <button @click="openCancel({{ $lc->id }}, '{{ $lc->title }}')" class="py-1.5 px-3 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200/50 rounded-xl font-bold text-xs cursor-pointer">
                                Request Cancel
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center bg-white rounded-3xl border border-gray-100 text-gray-500 font-semibold">No scheduled live sessions ahead.</div>
                @endforelse
            </div>
        </div>

        {{-- ── 5. PAST SESSIONS TAB ── --}}
        <div x-show="activeTab === 'past'" x-cloak class="space-y-4">
            <div class="space-y-3">
                @forelse($pastClasses as $lc)
                    <div class="p-5 bg-white rounded-3xl border border-gray-100 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-start sm:items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-gray-50 text-gray-500 flex flex-col items-center justify-center shrink-0 font-bold text-xs border border-gray-200">
                                <span>{{ $lc->scheduled_at->format('d') }}</span>
                                <span class="text-[8px] uppercase tracking-wider font-extrabold">{{ $lc->scheduled_at->format('M') }}</span>
                            </div>
                            <div>
                                <h4 class="font-heading font-black text-base text-gray-700">{{ $lc->title }}</h4>
                                <p class="text-xs text-gray-400 font-semibold">{{ $lc->course->level }} {{ $lc->course->subject->name ?? '' }}</p>
                                <p class="text-xs text-gray-400 font-medium">{{ $lc->scheduled_at->format('j F Y, g:i A') }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            @if($lc->meeting_url)
                                <a href="{{ $lc->meeting_url }}" target="_blank" class="py-1.5 px-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl font-bold text-xs">
                                    Watch Playback
                                </a>
                            @else
                                <span class="py-1.5 px-3 bg-gray-50 text-gray-400 rounded-xl text-xs font-semibold">No playback</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center bg-white rounded-3xl border border-gray-100 text-gray-500 font-semibold">No past classes recorded.</div>
                @endforelse
            </div>
        </div>

        {{-- ── 6. ADD LIVE SESSION MODAL ── --}}
        <div x-show="showAddClassModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 w-full max-w-lg p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="font-heading font-black text-lg text-gray-900">Schedule Live Class Session</h3>
                    <button @click="showAddClassModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg">✕</button>
                </div>

                <form method="POST" action="{{ route('teacher.classes.store') }}" class="space-y-4 text-xs font-semibold">
                    @csrf

                    <div class="space-y-1">
                        <label class="text-gray-500 block">Class / Course Section</label>
                        <select name="course_id" required class="w-full rounded-2xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 p-3">
                            <option value="">Select a Course Section...</option>
                            @foreach($teacher->courses as $course)
                                <option value="{{ $course->id }}">{{ $course->level }} {{ $course->subject->name ?? '' }} ({{ $course->title }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-gray-500 block">Session Title / Topic</label>
                        <input type="text" name="title" required placeholder="e.g. Algebra Chapter 3 Revision" class="w-full rounded-2xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 p-3">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-gray-500 block">Scheduled Date & Time</label>
                            <input type="datetime-local" name="scheduled_at" required class="w-full rounded-2xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 p-3">
                        </div>
                        <div class="space-y-1">
                            <label class="text-gray-500 block">Duration (Minutes)</label>
                            <input type="number" name="duration_minutes" value="60" min="15" max="480" class="w-full rounded-2xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 p-3">
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-gray-500 block">Virtual Meeting Link (Zoom, Meet, Teams)</label>
                        <input type="url" name="meeting_url" placeholder="https://zoom.us/j/..." class="w-full rounded-2xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 p-3">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-50">
                        <button type="button" @click="showAddClassModal = false" class="py-2.5 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-bold">Cancel</button>
                        <button type="submit" class="py-2.5 px-5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-extrabold shadow-xs">Schedule Class</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── 7. ASSIGN TASK / ASSIGNMENT MODAL ── --}}
        <div x-show="showAddTaskModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 w-full max-w-lg p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div>
                        <h3 class="font-heading font-black text-lg text-gray-900">Assign Subject Task</h3>
                        <p class="text-[10px] text-gray-400 mt-0.5">Course: <strong class="text-gray-600" x-text="selectedCourseName"></strong></p>
                    </div>
                    <button @click="showAddTaskModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg">✕</button>
                </div>

                <form method="POST" :action="addTaskActionUrl" class="space-y-4 text-xs font-semibold">
                    @csrf

                    <div class="space-y-1">
                        <label class="text-gray-500 block">Task Title</label>
                        <input type="text" name="title" required placeholder="e.g. Chapter 3 Calculus Practice Sheet" class="w-full rounded-2xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 p-3">
                    </div>

                    <div class="space-y-1">
                        <label class="text-gray-500 block">Task Instructions / Description</label>
                        <textarea name="description" rows="4" placeholder="Detail the questions, reading pages, or format requirements..." class="w-full rounded-2xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 p-3"></textarea>
                    </div>

                    <div class="space-y-1">
                        <label class="text-gray-500 block">Due Date</label>
                        <input type="date" name="due_date" required class="w-full rounded-2xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 p-3">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-50">
                        <button type="button" @click="showAddTaskModal = false" class="py-2.5 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-bold">Cancel</button>
                        <button type="submit" class="py-2.5 px-5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-extrabold shadow-xs">Assign Task</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── 8. REQUEST CANCEL CLASS MODAL ── --}}
        <div x-show="showCancelModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 w-full max-w-lg p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div>
                        <h3 class="font-heading font-black text-lg text-gray-900">Request Class Cancellation</h3>
                        <p class="text-[10px] text-gray-400 mt-0.5">Session: <strong class="text-gray-600" x-text="selectedClassTitle"></strong></p>
                    </div>
                    <button @click="showCancelModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg">✕</button>
                </div>

                <form method="POST" :action="cancelActionUrl" class="space-y-4 text-xs font-semibold">
                    @csrf

                    <div class="p-3 bg-rose-50 border border-rose-100 rounded-2xl text-[11px] text-rose-800">
                        ⚠️ Cancellation requests are routed to the Admin Desk for confirmation. You will be notified once reviewed.
                    </div>

                    <div class="space-y-1">
                        <label class="text-gray-500 block">Reason for Cancellation</label>
                        <textarea name="reason" required rows="4" placeholder="Provide details (medical leave, emergency, overlapping commitments...)" class="w-full rounded-2xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 p-3"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-50">
                        <button type="button" @click="showCancelModal = false" class="py-2.5 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-bold">Cancel</button>
                        <button type="submit" class="py-2.5 px-5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-extrabold shadow-xs">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── 9. REQUEST RESCHEDULE CLASS MODAL ── --}}
        <div x-show="showRescheduleModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 w-full max-w-lg p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div>
                        <h3 class="font-heading font-black text-lg text-gray-900">Request Reschedule</h3>
                        <p class="text-[10px] text-gray-400 mt-0.5">Session: <strong class="text-gray-600" x-text="selectedClassTitleReschedule"></strong></p>
                    </div>
                    <button @click="showRescheduleModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg">✕</button>
                </div>

                <form method="POST" :action="rescheduleActionUrl" class="space-y-4 text-xs font-semibold">
                    @csrf

                    <div class="space-y-1">
                        <label class="text-gray-500 block">Proposed Date & Time</label>
                        <input type="datetime-local" name="requested_at" required class="w-full rounded-2xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 p-3">
                    </div>

                    <div class="space-y-1">
                        <label class="text-gray-500 block">Reason for Rescheduling</label>
                        <textarea name="reason" required rows="4" placeholder="Explain the reason for proposing a new time slot..." class="w-full rounded-2xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 p-3"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-50">
                        <button type="button" @click="showRescheduleModal = false" class="py-2.5 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-bold">Cancel</button>
                        <button type="submit" class="py-2.5 px-5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-extrabold shadow-xs">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-teacher-layout>
