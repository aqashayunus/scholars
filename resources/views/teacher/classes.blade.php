<x-teacher-layout>
    <x-slot name="title">My Classes & Schedule - Teacher Portal</x-slot>

    @php
        $coursesStudentsMap = $teacher->courses->mapWithKeys(function($c) {
            return [$c->id => $c->enrollments->map(function($enr) use ($c) {
                $student = $enr->student;
                $name = $student?->user?->name ?? ('Student #' . $enr->student_id);
                $words = explode(' ', trim($name));
                $initials = strtoupper(substr($words[0] ?? 'S', 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
                return [
                    'id'       => $enr->student_id,
                    'name'     => $name,
                    'initials' => $initials,
                    'email'    => $student?->user?->email ?? '',
                    'level'    => $student?->level ?? $c->level,
                ];
            })->values()];
        });
    @endphp

    <script>
        function teacherClassesPage() {
            return {
                activeTab: (new URLSearchParams(window.location.search).get('tab')) || (window.location.hash ? window.location.hash.replace('#', '') : 'courses'),
                
                // Add Class Modal State
                showAddClassModal: false,
                addClassDate: '{{ date('Y-m-d') }}',
                addClassTime: '10:00',
                
                // Add Task Modal State
                showAddTaskModal: false,
                addTaskActionUrl: '',
                selectedCourseId: null,
                selectedCourseName: '',
                coursesMap: @json($coursesStudentsMap),
                assignTarget: 'all',
                selectedStudentIds: [],
                studentSearch: '',
                
                get currentCourseStudents() {
                    if (!this.selectedCourseId || !this.coursesMap[this.selectedCourseId]) return [];
                    return this.coursesMap[this.selectedCourseId];
                },
                
                get filteredStudents() {
                    const list = this.currentCourseStudents;
                    if (!this.studentSearch) return list;
                    const q = this.studentSearch.toLowerCase();
                    return list.filter(function(s) {
                        return s.name.toLowerCase().includes(q) || (s.email && s.email.toLowerCase().includes(q));
                    });
                },
                
                // Cancel Class Modal State
                showCancelModal: false,
                cancelActionUrl: '',
                selectedClassTitle: '',
                
                // Reschedule Class Modal State
                showRescheduleModal: false,
                rescheduleActionUrl: '',
                selectedClassTitleReschedule: '',
                rescheduleDate: '{{ date('Y-m-d', strtotime('+1 day')) }}',
                rescheduleTime: '10:00',
                
                openAddTask(courseId, courseName) {
                    this.addTaskActionUrl = `/teacher/classes/${courseId}/tasks`;
                    this.selectedCourseId = courseId;
                    this.selectedCourseName = courseName;
                    this.assignTarget = 'all';
                    this.studentSearch = '';
                    const students = this.coursesMap[courseId] || [];
                    this.selectedStudentIds = students.map(function(s) { return s.id; });
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
                    this.rescheduleDate = '{{ date('Y-m-d', strtotime('+1 day')) }}';
                    this.rescheduleTime = '10:00';
                    this.showRescheduleModal = true;
                }
            };
        }
    </script>

    <div x-data="teacherClassesPage()" class="space-y-6">

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
                                <h4 class="font-heading font-black text-base text-gray-900">{{ $course->subject->display_name ?? ($course->level . ' ' . ($course->subject->name ?? '')) }} – {{ $course->title }}</h4>
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
                            <button @click="openAddTask({{ $course->id }}, '{{ $course->subject->display_name ?? ($course->level . ' ' . ($course->subject->name ?? '')) }}')" class="py-2 px-3.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-extrabold text-xs rounded-xl border border-emerald-200/50 transition-colors cursor-pointer">
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
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h4 class="font-heading font-black text-base text-gray-900">{{ $lc->title }}</h4>
                                    @if($lc->is_rescheduled)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">
                                            Rescheduled
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 font-semibold mt-0.5">{{ $lc->course?->subject?->display_name ?? (($lc->course?->level ?? '') . ' ' . ($lc->course?->subject?->name ?? '')) }}</p>
                                <p class="text-xs text-gray-400 font-medium mt-0.5">{{ $lc->scheduled_at->format('l, g:i A') }} ({{ $lc->duration_minutes ?? '60' }} mins)</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 border-t md:border-t-0 pt-3 md:pt-0 border-gray-50 flex-wrap">
                            @if($lc->meeting_url)
                                <a href="{{ $lc->meeting_url }}" target="_blank" class="py-1.5 px-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs shadow-xs transition-colors">
                                    Start Meeting
                                </a>
                            @else
                                <a href="{{ route('teacher.classes.show', $lc->course_id ?? 0) }}" class="py-1.5 px-3 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200/50 rounded-xl font-bold text-xs transition-colors">
                                    Class Workspace
                                </a>
                            @endif

                            {{-- Reschedule Action --}}
                            <button @click="openReschedule({{ $lc->id }}, '{{ addslashes($lc->title) }}')" class="py-1.5 px-3 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200/50 rounded-xl font-bold text-xs cursor-pointer transition-colors">
                                Reschedule
                            </button>

                            {{-- Cancel Action --}}
                            <button @click="openCancel({{ $lc->id }}, '{{ addslashes($lc->title) }}')" class="py-1.5 px-3 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200/50 rounded-xl font-bold text-xs cursor-pointer transition-colors">
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
                                <p class="text-xs text-gray-400 font-semibold">{{ $lc->course?->subject?->display_name ?? (($lc->course?->level ?? '') . ' ' . ($lc->course?->subject?->name ?? '')) }}</p>
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
            <div @click.away="showAddClassModal = false" class="bg-white rounded-3xl shadow-2xl border border-gray-100 w-full max-w-lg p-6 sm:p-7 space-y-5 my-auto max-h-[90vh] overflow-y-auto transform transition-all">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div>
                        <h3 class="font-heading font-black text-lg text-gray-900">Schedule Live Class Session</h3>
                        <p class="text-xs text-gray-500 font-medium mt-0.5">Create a live online lesson and sync with class schedule.</p>
                    </div>
                    <button type="button" @click="showAddClassModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-xl cursor-pointer">✕</button>
                </div>

                <form method="POST" action="{{ route('teacher.classes.store') }}" class="space-y-4 text-xs font-semibold">
                    @csrf
                    <input type="hidden" name="scheduled_at" :value="addClassDate && addClassTime ? `${addClassDate} ${addClassTime}:00` : ''">

                    <div>
                        <label class="font-extrabold text-gray-900 block mb-1">Class / Course Section *</label>
                        <select name="course_id" required class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-3.5 pr-9 py-2.5 text-xs font-semibold outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                            <option value="">Select a Course Section...</option>
                            @foreach($teacher->courses as $course)
                                <option value="{{ $course->id }}">{{ $course->subject->display_name ?? ($course->level . ' ' . ($course->subject->name ?? '')) }} ({{ $course->title }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="font-extrabold text-gray-900 block mb-1">Session Title / Topic *</label>
                        <input type="text" name="title" required placeholder="e.g. Algebra Chapter 3 Revision & Practice" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Date *</label>
                            <input type="date" x-model="addClassDate" required min="{{ date('Y-m-d') }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                        </div>
                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Start Time *</label>
                            <input type="time" x-model="addClassTime" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                        </div>
                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Duration (Min)</label>
                            <input type="number" name="duration_minutes" value="60" min="15" max="480" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-bold">
                        </div>
                    </div>

                    <div>
                        <label class="font-extrabold text-gray-900 block mb-1">Virtual Meeting Link (Zoom, Google Meet, Teams)</label>
                        <input type="url" name="meeting_url" placeholder="https://meet.google.com/... or https://zoom.us/j/..." class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 text-xs font-extrabold">
                        <button type="button" @click="showAddClassModal = false" class="py-2.5 px-5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl transition-colors cursor-pointer">Cancel</button>
                        <button type="submit" class="py-2.5 px-6 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors shadow-sm cursor-pointer hover:shadow-md">Schedule Class</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── 7. ASSIGN TASK / ASSIGNMENT MODAL ── --}}
        <div x-show="showAddTaskModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="showAddTaskModal = false" class="bg-white rounded-3xl shadow-2xl border border-gray-100 w-full max-w-xl p-6 sm:p-7 space-y-5 my-auto max-h-[92vh] overflow-y-auto transform transition-all">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div>
                        <h3 class="font-heading font-black text-lg text-gray-900">Assign Subject Task</h3>
                        <p class="text-xs text-gray-500 font-medium mt-0.5">
                            Course: <strong class="text-gray-800" x-text="selectedCourseName"></strong>
                            <span class="text-gray-300 mx-1">•</span>
                            <span class="text-blue-600 font-bold" x-text="`${currentCourseStudents.length} Enrolled Student(s)`"></span>
                        </p>
                    </div>
                    <button type="button" @click="showAddTaskModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-xl cursor-pointer">✕</button>
                </div>

                <form method="POST" :action="addTaskActionUrl" enctype="multipart/form-data" class="space-y-4 text-xs font-semibold">
                    @csrf
                    <input type="hidden" name="assign_target" :value="assignTarget">

                    <div>
                        <label class="font-extrabold text-gray-900 block mb-1">Task Title *</label>
                        <input type="text" name="title" required placeholder="e.g. Chapter 3 Exercises & Past Year Questions" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Topic / Chapter Focus</label>
                            <input type="text" name="topic" placeholder="e.g. Quadratic Equations & Graphs" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                        </div>
                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Total Marks</label>
                            <input type="number" name="total_marks" value="100" min="1" max="1000" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-bold">
                        </div>
                    </div>

                    <div>
                        <label class="font-extrabold text-gray-900 block mb-1">Task Instructions / Description</label>
                        <textarea name="description" rows="3" placeholder="Detail the questions, reading pages, or format requirements..." class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Due Date *</label>
                            <input type="date" name="due_date" required min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d', strtotime('+7 days')) }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                        </div>
                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Attach Worksheet (Optional)</label>
                            <input type="file" name="file" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-1.5 text-xs outline-none file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>
                    </div>

                    {{-- ── STUDENT ASSIGNMENT TARGET (All vs Selected) ── --}}
                    <div class="space-y-2.5 pt-2 border-t border-gray-100">
                        <label class="font-extrabold text-gray-900 block text-xs">Assign Target *</label>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <label class="relative flex flex-col p-3 rounded-2xl border-2 cursor-pointer transition-all"
                                   :class="assignTarget === 'all' ? 'border-blue-600 bg-blue-50/40 shadow-2xs' : 'border-gray-200 bg-gray-50/70 hover:bg-gray-100/70'">
                                <div class="flex items-center gap-2 mb-1">
                                    <input type="radio" value="all" x-model="assignTarget" class="text-blue-600 focus:ring-blue-500">
                                    <span class="font-black text-gray-900 text-xs">All Students</span>
                                </div>
                                <p class="text-[11px] text-gray-500 font-medium pl-5" x-text="`Assign to all ${currentCourseStudents.length} enrolled student(s)`"></p>
                            </label>

                            <label class="relative flex flex-col p-3 rounded-2xl border-2 cursor-pointer transition-all"
                                   :class="assignTarget === 'selected' ? 'border-blue-600 bg-blue-50/40 shadow-2xs' : 'border-gray-200 bg-gray-50/70 hover:bg-gray-100/70'">
                                <div class="flex items-center gap-2 mb-1">
                                    <input type="radio" value="selected" x-model="assignTarget" class="text-blue-600 focus:ring-blue-500">
                                    <span class="font-black text-gray-900 text-xs">Selected Students</span>
                                </div>
                                <p class="text-[11px] text-gray-500 font-medium pl-5">Choose specific students (remedial, catch-up)</p>
                            </label>
                        </div>

                        {{-- Interactive Student Checklist when "Selected Students" is active --}}
                        <div x-show="assignTarget === 'selected'" x-transition class="space-y-2 p-3 bg-gray-50/80 border border-gray-200 rounded-2xl">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-extrabold text-gray-800 text-[11px]" x-text="`${selectedStudentIds.length} of ${currentCourseStudents.length} student(s) selected`"></span>
                                <div class="flex items-center gap-2 text-[11px]">
                                    <button type="button" @click="selectedStudentIds = currentCourseStudents.map(s => s.id)" class="text-blue-600 hover:text-blue-800 font-bold cursor-pointer">Select All</button>
                                    <span class="text-gray-300">•</span>
                                    <button type="button" @click="selectedStudentIds = []" class="text-gray-500 hover:text-gray-700 font-bold cursor-pointer">Clear</button>
                                </div>
                            </div>

                            {{-- Filter search input if course has more than 3 students --}}
                            <div x-show="currentCourseStudents.length > 3">
                                <input type="text" x-model="studentSearch" placeholder="Type student name to search..." class="w-full bg-white border border-gray-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:ring-1 focus:ring-blue-500 font-medium">
                            </div>

                            <div class="max-h-48 overflow-y-auto space-y-1.5 pr-1">
                                <template x-for="st in filteredStudents" :key="st.id">
                                    <label class="flex items-center justify-between p-2 rounded-xl bg-white border hover:bg-blue-50/40 cursor-pointer transition-colors"
                                           :class="selectedStudentIds.includes(st.id) ? 'border-blue-300 shadow-2xs' : 'border-gray-200'">
                                        <div class="flex items-center gap-2.5">
                                            <input type="checkbox" name="student_ids[]" :value="st.id" x-model="selectedStudentIds" class="rounded text-blue-600 focus:ring-blue-500">
                                            <div class="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-black text-[10px]" x-text="st.initials"></div>
                                            <span class="font-bold text-gray-900 text-xs" x-text="st.name"></span>
                                        </div>
                                        <span class="text-[10px] text-gray-400 font-semibold" x-text="st.level || ''"></span>
                                    </label>
                                </template>
                                <template x-if="currentCourseStudents.length === 0">
                                    <p class="text-center py-3 text-xs text-gray-400 font-semibold">No students currently enrolled in this course section.</p>
                                </template>
                            </div>

                            <p x-show="selectedStudentIds.length === 0 && currentCourseStudents.length > 0" class="text-[11px] text-amber-700 font-bold flex items-center gap-1 mt-1">
                                ⚠️ Please select at least one student before assigning.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 text-xs font-extrabold">
                        <button type="button" @click="showAddTaskModal = false" class="py-2.5 px-5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl transition-colors cursor-pointer">Cancel</button>
                        <button type="submit" 
                                :disabled="assignTarget === 'selected' && selectedStudentIds.length === 0"
                                :class="assignTarget === 'selected' && selectedStudentIds.length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:shadow-md cursor-pointer'"
                                class="py-2.5 px-6 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-all shadow-sm font-extrabold flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span x-text="assignTarget === 'selected' ? `Assign to ${selectedStudentIds.length} Student(s)` : 'Assign to All Students'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── 8. REQUEST CANCEL CLASS MODAL ── --}}
        <div x-show="showCancelModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="showCancelModal = false" class="bg-white rounded-3xl shadow-2xl border border-gray-100 w-full max-w-lg p-6 sm:p-7 space-y-5 my-auto max-h-[90vh] overflow-y-auto transform transition-all">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div>
                        <h3 class="font-heading font-black text-lg text-gray-900">Request Class Cancellation</h3>
                        <p class="text-xs text-gray-500 font-medium mt-0.5">Session: <strong class="text-gray-800" x-text="selectedClassTitle"></strong></p>
                    </div>
                    <button type="button" @click="showCancelModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-xl cursor-pointer">✕</button>
                </div>

                <form method="POST" :action="cancelActionUrl" class="space-y-4 text-xs font-semibold">
                    @csrf

                    <div class="p-3.5 bg-rose-50/80 border border-rose-100 rounded-2xl flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div class="text-xs text-rose-900 leading-relaxed">
                            <strong class="font-extrabold block mb-0.5">Cancellation Policy</strong>
                            <span class="text-rose-700/90 text-[11px]">Cancellation requests are routed to the Admin Desk for confirmation. You and your students will be notified once reviewed.</span>
                        </div>
                    </div>

                    <div>
                        <label class="font-extrabold text-gray-900 block mb-1">Reason for Cancellation *</label>
                        <textarea name="reason" required rows="3" placeholder="Provide details (medical leave, emergency, overlapping commitments...)" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-xs outline-none focus:ring-2 focus:ring-rose-500 font-medium"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 text-xs font-extrabold">
                        <button type="button" @click="showCancelModal = false" class="py-2.5 px-5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl transition-colors cursor-pointer">Cancel</button>
                        <button type="submit" class="py-2.5 px-6 bg-rose-600 hover:bg-rose-700 text-white rounded-xl transition-colors shadow-sm cursor-pointer hover:shadow-md">Submit Cancellation</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── 9. REQUEST RESCHEDULE CLASS MODAL ── --}}
        <div x-show="showRescheduleModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="showRescheduleModal = false" class="bg-white rounded-3xl shadow-2xl border border-gray-100 w-full max-w-lg p-6 sm:p-7 space-y-5 my-auto max-h-[90vh] overflow-y-auto transform transition-all">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div>
                        <h3 class="font-heading font-black text-lg text-gray-900">Request Reschedule</h3>
                        <p class="text-xs text-gray-500 font-medium mt-0.5">Session: <strong class="text-gray-800" x-text="selectedClassTitleReschedule"></strong></p>
                    </div>
                    <button type="button" @click="showRescheduleModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-xl cursor-pointer">✕</button>
                </div>

                <div class="p-3.5 bg-blue-50/70 border border-blue-100 rounded-2xl flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="text-xs text-blue-900 leading-relaxed">
                        <strong class="font-extrabold block mb-0.5">Reschedule Request Policy</strong>
                        <span class="text-blue-700/90 text-[11px]">Proposed time slots are submitted to the Admin Desk for review. Upon confirmation, enrolled students and parents will be notified automatically.</span>
                    </div>
                </div>

                <form method="POST" :action="rescheduleActionUrl" class="space-y-4 text-xs font-semibold">
                    @csrf
                    <input type="hidden" name="requested_at" :value="rescheduleDate && rescheduleTime ? `${rescheduleDate} ${rescheduleTime}:00` : ''">

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Proposed Date *</label>
                            <input type="date" x-model="rescheduleDate" required min="{{ date('Y-m-d') }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                        </div>
                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Proposed Time *</label>
                            <input type="time" x-model="rescheduleTime" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                        </div>
                    </div>

                    <div>
                        <label class="font-extrabold text-gray-900 block mb-1">Reason for Rescheduling *</label>
                        <textarea name="reason" required rows="3" placeholder="Explain the reason for proposing a new time slot (e.g. Schedule clash, emergency, exam preparation shift)..." class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium"></textarea>
                        <p class="text-[10px] text-gray-400 mt-1 font-medium">Provide clear details for the administration desk.</p>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 text-xs font-extrabold">
                        <button type="button" @click="showRescheduleModal = false" class="py-2.5 px-5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl transition-colors cursor-pointer">Cancel</button>
                        <button type="submit" class="py-2.5 px-6 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors shadow-sm cursor-pointer hover:shadow-md">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-teacher-layout>
