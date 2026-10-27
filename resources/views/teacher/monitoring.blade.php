<x-teacher-layout>
    <x-slot name="title">Student Monitoring - Teacher Portal</x-slot>

    @php
        $avgProgress = count($allStudents ?? []) > 0 ? (int) round(collect($allStudents)->avg('progress')) : 0;
        $needAttentionCount = count($attentionStudents ?? []);
        $interventionCount = count($interventionStudents ?? []);
    @endphp

    <div x-data="{ 
        tab: 'overview',
        attendanceFilter: 'all',
        progressFilter: 'all',
        interventionFilter: 'all',
        selectedStudentKey: '{{ $allStudents[0]['id'] ?? '' }}',
        studentsData: {{ \Illuminate\Support\Js::from(collect($allStudents ?? [])->keyBy('id')->toArray()) }},
        noteModalOpen: false,
        noteText: '',

        attendancePerPage: 10,
        attendanceCurrentPage: 1,
        get filteredAttendanceList() {
            return Object.values(this.studentsData).filter(s => {
                if (this.attendanceFilter === 'good') return (s.attendance || 0) >= 80;
                if (this.attendanceFilter === 'attention') return (s.attendance || 0) < 80;
                if (this.attendanceFilter === 'absent') return (s.absent_count || 0) > 0;
                return true;
            });
        },
        get paginatedAttendanceList() {
            const total = this.filteredAttendanceList.length;
            const maxPage = Math.max(1, Math.ceil(total / this.attendancePerPage));
            if (this.attendanceCurrentPage > maxPage) this.attendanceCurrentPage = 1;
            const start = (this.attendanceCurrentPage - 1) * this.attendancePerPage;
            return this.filteredAttendanceList.slice(start, start + this.attendancePerPage);
        },
        get attendanceTotalPages() {
            return Math.max(1, Math.ceil(this.filteredAttendanceList.length / this.attendancePerPage));
        },
        get attendanceStartIndex() {
            if (this.filteredAttendanceList.length === 0) return 0;
            return (this.attendanceCurrentPage - 1) * this.attendancePerPage + 1;
        },
        get attendanceEndIndex() {
            return Math.min(this.attendanceCurrentPage * this.attendancePerPage, this.filteredAttendanceList.length);
        },

        progressPerPage: 10,
        progressCurrentPage: 1,
        get filteredProgressList() {
            return Object.values(this.studentsData).filter(s => {
                if (this.progressFilter === 'high') return (s.progress || 0) >= 75;
                if (this.progressFilter === 'low') return (s.progress || 0) < 60;
                return true;
            });
        },
        get paginatedProgressList() {
            const total = this.filteredProgressList.length;
            const maxPage = Math.max(1, Math.ceil(total / this.progressPerPage));
            if (this.progressCurrentPage > maxPage) this.progressCurrentPage = 1;
            const start = (this.progressCurrentPage - 1) * this.progressPerPage;
            return this.filteredProgressList.slice(start, start + this.progressPerPage);
        },
        get progressTotalPages() {
            return Math.max(1, Math.ceil(this.filteredProgressList.length / this.progressPerPage));
        },
        get progressStartIndex() {
            if (this.filteredProgressList.length === 0) return 0;
            return (this.progressCurrentPage - 1) * this.progressPerPage + 1;
        },
        get progressEndIndex() {
            return Math.min(this.progressCurrentPage * this.progressPerPage, this.filteredProgressList.length);
        },
        get currentStudent() {
            if (this.selectedStudentKey && this.studentsData[this.selectedStudentKey]) {
                return this.studentsData[this.selectedStudentKey];
            }
            const first = Object.values(this.studentsData)[0];
            return first || null;
        },
        selectStudent(id) {
            this.selectedStudentKey = id;
            if (this.tab !== 'overview') {
                this.tab = 'overview';
            }
        },
        openNoteModal() {
            this.noteText = this.currentStudent ? (this.currentStudent.note || '') : '';
            this.noteModalOpen = true;
        },
        saveNote() {
            if (this.currentStudent) {
                this.currentStudent.note = this.noteText;
                this.currentStudent.noteDate = '{{ now()->format('d M Y') }}';
            }
            this.noteModalOpen = false;
        },
        exportReport() {
            let rows = [
                ['Student ID', 'Student Name', 'Level', 'Subject', 'Attendance (%)', 'Progress (%)', 'Completed Tasks', 'Pending Tasks', 'Latest Assessment', 'Status', 'Intervention Flag']
            ];
            Object.values(this.studentsData).forEach(s => {
                rows.push([
                    s.id,
                    '&quot;' + (s.name || '').replace(/&quot;/g, '&quot;&quot;') + '&quot;',
                    '&quot;' + (s.class || '') + '&quot;',
                    '&quot;' + (s.subject || '') + '&quot;',
                    s.attendance + '%',
                    s.progress + '%',
                    s.completed_tasks ?? 0,
                    s.pending_tasks ?? 0,
                    s.latestAssessment || '–',
                    s.status || 'Good',
                    '&quot;' + (s.issue || 'None') + '&quot;'
                ]);
            });
            let csvContent = 'data:text/csv;charset=utf-8,' + rows.map(e => e.join(',')).join('\n');
            let encodedUri = encodeURI(csvContent);
            let link = document.createElement('a');
            link.setAttribute('href', encodedUri);
            link.setAttribute('download', 'student_monitoring_report_' + new Date().toISOString().slice(0, 10) + '.csv');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    }" class="space-y-6">

        {{-- ── HEADER SECTION ── --}}
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 font-black text-base flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div>
                    <h1 class="font-heading font-black text-2xl text-gray-900">Student Monitoring</h1>
                    <p class="text-xs text-gray-500 font-medium mt-0.5">Monitor attendance, academic progress and student performance in real time.</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <div class="py-2 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 inline-flex items-center gap-1.5 shadow-3xs">
                    <span>{{ now()->startOfMonth()->format('j M Y') }} – {{ now()->format('j M Y') }}</span>
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <button @click="exportReport()" class="py-2 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-extrabold transition-colors cursor-pointer inline-flex items-center gap-1.5 shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Export Report</span>
                </button>
            </div>
        </div>

        {{-- ── 1. TOP 4 KPI SUMMARY CARDS ── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            {{-- Overall Attendance --}}
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-emerald-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-black text-gray-900">{{ $attStats['pct'] ?? 100 }}%</p>
                    <p class="text-xs font-bold text-gray-400">Overall Attendance</p>
                    <p class="text-[11px] text-gray-500 font-medium">{{ $attStats['period_label'] ?? '0/0' }}</p>
                </div>
            </div>

            {{-- Class Average Progress --}}
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-blue-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-black text-gray-900">{{ $avgProgress }}%</p>
                    <p class="text-xs font-bold text-gray-400">Avg. Student Progress</p>
                    <p class="text-[11px] text-gray-500 font-medium">{{ count($allStudents ?? []) }} enrolled students</p>
                </div>
            </div>

            {{-- Students Need Attention --}}
            <div class="{{ $needAttentionCount > 0 ? 'bg-amber-50/50 border-amber-200/80' : 'bg-white border-gray-100' }} rounded-3xl p-5 border shadow-sm flex items-center gap-4 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-amber-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-black text-amber-900">{{ $needAttentionCount }}</p>
                    <p class="text-xs font-bold text-amber-800/80">Students Need Attention</p>
                    <p class="text-[11px] text-amber-700 font-semibold">{{ $needAttentionCount > 0 ? 'Requires teacher review' : 'All students on track' }}</p>
                </div>
            </div>

            {{-- Intervention Active --}}
            <div class="{{ $interventionCount > 0 ? 'bg-purple-50/40 border-purple-200/80' : 'bg-white border-gray-100' }} rounded-3xl p-5 border shadow-sm flex items-center gap-4 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-purple-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-purple-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-black text-purple-700">{{ $interventionCount }}</p>
                    <p class="text-xs font-bold text-gray-400">Intervention Active</p>
                    <p class="text-[11px] text-gray-500 font-medium">Critical support required</p>
                </div>
            </div>
        </div>

        {{-- ── 2. SUB-TAB NAVIGATION BAR ── --}}
        <div class="bg-white rounded-2xl p-1.5 border border-gray-100 shadow-sm flex flex-wrap gap-1">
            <button @click="tab = 'overview'" 
                    :class="tab === 'overview' ? 'bg-blue-600 text-white shadow-sm font-extrabold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 font-bold'"
                    class="px-6 py-2.5 rounded-xl text-xs transition-all cursor-pointer">
                Overview
            </button>

            <button @click="tab = 'attendance'" 
                    :class="tab === 'attendance' ? 'bg-blue-600 text-white shadow-sm font-extrabold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 font-bold'"
                    class="px-6 py-2.5 rounded-xl text-xs transition-all cursor-pointer">
                Attendance
            </button>

            <button @click="tab = 'progress'" 
                    :class="tab === 'progress' ? 'bg-blue-600 text-white shadow-sm font-extrabold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 font-bold'"
                    class="px-6 py-2.5 rounded-xl text-xs transition-all cursor-pointer">
                Progress
            </button>

            <button @click="tab = 'intervention'" 
                    :class="tab === 'intervention' ? 'bg-blue-600 text-white shadow-sm font-extrabold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 font-bold'"
                    class="px-6 py-2.5 rounded-xl text-xs transition-all cursor-pointer inline-flex items-center gap-1.5">
                <span>Intervention</span>
                @if($interventionCount > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black bg-purple-100 text-purple-700" :class="tab === 'intervention' ? 'bg-white text-blue-600' : ''">
                        {{ $interventionCount }}
                    </span>
                @endif
            </button>
        </div>

        {{-- ── 3. OVERVIEW TAB ── --}}
        <div x-show="tab === 'overview'" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                {{-- LEFT SECTION: 8 COLUMNS --}}
                <div class="lg:col-span-8 space-y-6">
                    
                    {{-- Card 1: Students Needing Attention --}}
                    <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <div>
                                <h3 class="font-heading font-black text-base text-gray-900">Students Needing Attention</h3>
                                <p class="text-[11px] text-gray-400 font-semibold mt-0.5">Learners requiring catch-up, assignment reminders, or intervention support.</p>
                            </div>
                            <a href="{{ route('teacher.students') }}" class="text-xs font-extrabold text-blue-600 hover:underline">
                                View All ({{ count($allStudents) }})
                            </a>
                        </div>

                        <div class="space-y-3">
                            @forelse($attentionStudents as $st)
                                <div @click="selectStudent('{{ $st['id'] }}')" 
                                     :class="selectedStudentKey === '{{ $st['id'] }}' ? 'border-blue-500 bg-blue-50/30' : 'border-gray-100 bg-gray-50/80 hover:bg-gray-100/60'"
                                     class="p-3.5 rounded-2xl border flex items-center justify-between gap-3 transition-all cursor-pointer">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full {{ $st['avatar_color'] ?? 'bg-blue-600' }} text-white font-black text-xs flex items-center justify-center shrink-0 shadow-2xs">
                                            {{ $st['initials'] ?? 'ST' }}
                                        </div>
                                        <div>
                                            <h4 class="font-extrabold text-xs text-gray-900">{{ $st['name'] }}</h4>
                                            <p class="text-[10px] text-gray-500 font-semibold">{{ $st['class'] }} • {{ $st['subject'] }}</p>
                                        </div>
                                    </div>

                                    <div class="text-right shrink-0">
                                        @if($st['category'] === 'intervention')
                                            <span class="px-2.5 py-0.5 rounded-full bg-purple-50 text-purple-700 font-black text-[10px] border border-purple-200/50">Intervention Active</span>
                                        @elseif($st['category'] === 'catchup')
                                            <span class="px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 font-black text-[10px] border border-amber-200/50">Catch-Up Pending</span>
                                        @elseif(($st['attendance'] ?? 100) < 80)
                                            <span class="px-2.5 py-0.5 rounded-full bg-rose-50 text-rose-700 font-black text-[10px] border border-rose-200/50">Low Attendance</span>
                                        @elseif(($st['overdue_tasks'] ?? 0) > 0)
                                            <span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 font-black text-[10px] border border-blue-200/50">Overdue Task</span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 font-black text-[10px] border border-amber-200/50">Needs Review</span>
                                        @endif
                                        <p class="text-[10px] text-gray-500 font-bold mt-0.5">{{ $st['issue'] ?? 'Academic progress below target' }}</p>
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center bg-gray-50/50 rounded-2xl border border-gray-100 text-gray-500 space-y-2">
                                    <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto font-black text-sm">✓</div>
                                    <h4 class="font-bold text-xs text-gray-800">All Students On Track</h4>
                                    <p class="text-[11px] text-gray-400">No students are currently falling behind in attendance or task submissions.</p>
                                </div>
                            @endforelse
                        </div>

                        <div class="pt-2 text-center">
                            <a href="{{ route('teacher.students') }}" class="py-2.5 px-6 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-extrabold text-xs rounded-xl inline-block transition-colors shadow-3xs">
                                View All Students
                            </a>
                        </div>
                    </div>

                    {{-- Card 2: Class Performance Across Subjects --}}
                    <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-5">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <div>
                                <h3 class="font-heading font-black text-base text-gray-900">Class Performance & Syllabus Mastery</h3>
                                <p class="text-[11px] text-gray-400 font-semibold mt-0.5">Average student completion rates across your teaching assignments.</p>
                            </div>
                            <span class="text-xs font-extrabold text-blue-600">{{ count($performanceBySubject) }} Sections</span>
                        </div>

                        <div class="space-y-4 text-xs font-bold">
                            @forelse($performanceBySubject as $c)
                                @php
                                    $progress = $c['avg_progress'] ?? 0;
                                    $barColor = 'bg-blue-600';
                                    $textColor = 'text-blue-600';
                                    if ($progress >= 75) {
                                        $barColor = 'bg-emerald-500';
                                        $textColor = 'text-emerald-600';
                                    } elseif ($progress < 50 && $progress > 0) {
                                        $barColor = 'bg-amber-500';
                                        $textColor = 'text-amber-600';
                                    } elseif ($progress === 0) {
                                        $barColor = 'bg-gray-300';
                                        $textColor = 'text-gray-400';
                                    }
                                @endphp
                                <div>
                                    <div class="flex justify-between mb-1.5">
                                        <span class="text-gray-800">{{ $c['level'] }} {{ $c['subject'] }} ({{ $c['title'] }})</span>
                                        <span class="{{ $textColor }} font-black">{{ $progress }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                                        <div class="{{ $barColor }} h-2 rounded-full transition-all duration-500" style="width: {{ max(4, $progress) }}%"></div>
                                    </div>
                                    <div class="flex justify-between mt-1 text-[10px] text-gray-400 font-semibold">
                                        <span>{{ $c['enrolled'] }} enrolled students</span>
                                        <span>{{ $c['tasks_count'] }} task(s) assigned • {{ $c['att_pct'] }}% attendance</span>
                                    </div>
                                </div>
                            @empty
                                <div class="p-6 text-center text-gray-400 text-xs">No active course performance records found.</div>
                            @endforelse
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-3 border-t border-gray-100 text-center text-xs">
                            <div class="p-3 bg-gray-50 rounded-2xl">
                                <span class="font-black text-gray-900 text-base block">{{ count($allStudents) }}</span>
                                <span class="text-[10px] text-gray-400 font-bold">Total Students</span>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-2xl">
                                <span class="font-black text-blue-600 text-base block">{{ $totalAssessments }}</span>
                                <span class="text-[10px] text-gray-400 font-bold">Tasks Assigned</span>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-2xl">
                                <span class="font-black text-emerald-600 text-base block">{{ $tasksCompletedPct }}%</span>
                                <span class="text-[10px] text-gray-400 font-bold">Tasks Submitted</span>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-2xl">
                                <span class="font-black text-purple-600 text-base block">{{ $totalLiveClassesHeld }}</span>
                                <span class="text-[10px] text-gray-400 font-bold">Sessions Held</span>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- RIGHT SECTION: STUDENT QUICK PROFILE SIDE CARD (4 COLUMNS) --}}
                <div class="lg:col-span-4 space-y-6">
                    
                    <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-5">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <h3 class="font-heading font-black text-base text-gray-900">Student Quick Profile</h3>
                            
                            <select x-model="selectedStudentKey" class="bg-gray-50 border border-gray-200 rounded-xl pl-2.5 pr-8 py-1 text-[11px] font-bold text-gray-700 outline-none max-w-[170px] truncate">
                                <template x-for="st in Object.values(studentsData)" :key="st.id">
                                    <option :value="st.id" x-text="st.name"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Student Header --}}
                        <div class="flex items-center gap-3.5" x-show="currentStudent">
                            <div :class="currentStudent?.avatar_color || 'bg-blue-600'" 
                                 class="w-12 h-12 rounded-full text-white font-black text-sm flex items-center justify-center shrink-0 shadow-xs"
                                 x-text="currentStudent?.initials || 'ST'">
                            </div>
                            <div class="overflow-hidden">
                                <h4 class="font-heading font-black text-base text-gray-900 truncate" x-text="currentStudent?.name || 'Select a student'"></h4>
                                <p class="text-xs text-gray-500 font-semibold truncate" x-text="(currentStudent?.id || '') + ' • ' + (currentStudent?.class || '')"></p>
                            </div>
                        </div>

                        {{-- 4 Stat Pills --}}
                        <div class="grid grid-cols-4 gap-2 text-center text-xs" x-show="currentStudent">
                            <div class="p-2.5 bg-gray-50 rounded-2xl">
                                <span class="font-black text-emerald-600 text-sm block" x-text="(currentStudent?.attendance ?? 100) + '%'"></span>
                                <span class="text-[9px] text-gray-400 font-bold">Attendance</span>
                            </div>
                            <div class="p-2.5 bg-gray-50 rounded-2xl">
                                <span class="font-black text-blue-600 text-sm block" x-text="(currentStudent?.progress ?? 0) + '%'"></span>
                                <span class="text-[9px] text-gray-400 font-bold">Progress</span>
                            </div>
                            <div class="p-2.5 bg-gray-50 rounded-2xl">
                                <span class="font-black text-amber-600 text-sm block" x-text="currentStudent?.pending_tasks ?? 0"></span>
                                <span class="text-[9px] text-gray-400 font-bold">Pending</span>
                            </div>
                            <div class="p-2.5 bg-gray-50 rounded-2xl">
                                <span class="font-black text-purple-700 text-sm block" x-text="currentStudent?.latestAssessment || '–'"></span>
                                <span class="text-[9px] text-gray-400 font-bold">Assessment</span>
                            </div>
                        </div>

                        {{-- Recent Activity Timeline --}}
                        <div class="space-y-3 pt-2" x-show="currentStudent">
                            <h4 class="font-heading font-black text-xs text-gray-400 uppercase tracking-wider">Recent Activity</h4>

                            <div class="space-y-2 text-xs">
                                <template x-for="(act, idx) in (currentStudent?.recentActivities || [])" :key="idx">
                                    <div class="flex items-center justify-between p-2.5 rounded-xl border"
                                         :class="act.color === 'emerald' ? 'bg-emerald-50/40 border-emerald-100' : (act.color === 'red' ? 'bg-red-50/40 border-red-100' : (act.color === 'amber' ? 'bg-amber-50/40 border-amber-100' : 'bg-blue-50/40 border-blue-100'))">
                                        <div class="flex items-center gap-2 overflow-hidden">
                                            <span class="w-2 h-2 rounded-full shrink-0"
                                                  :class="act.color === 'emerald' ? 'bg-emerald-500' : (act.color === 'red' ? 'bg-red-500' : (act.color === 'amber' ? 'bg-amber-500' : 'bg-blue-500'))"></span>
                                            <div class="truncate">
                                                <strong class="text-gray-900 block font-bold truncate" x-text="act.title"></strong>
                                                <span class="text-[10px] text-gray-500 font-semibold" x-text="act.date"></span>
                                            </div>
                                        </div>
                                        <span class="text-[10px] font-extrabold shrink-0 ml-2"
                                              :class="act.color === 'emerald' ? 'text-emerald-700' : (act.color === 'red' ? 'text-rose-700' : (act.color === 'amber' ? 'text-amber-700' : 'text-blue-700'))"
                                              x-text="act.badge"></span>
                                    </div>
                                </template>

                                <div x-show="!(currentStudent?.recentActivities && currentStudent.recentActivities.length)" class="p-3 text-center text-gray-400 text-[11px] bg-gray-50 rounded-xl">
                                    No recent activity recorded yet.
                                </div>
                            </div>
                        </div>

                        {{-- Teacher Note (Internal) --}}
                        <div class="space-y-2 pt-2 border-t border-gray-100" x-show="currentStudent">
                            <div class="flex items-center justify-between">
                                <h4 class="font-heading font-black text-xs text-gray-400 uppercase tracking-wider">Teacher Note (Internal)</h4>
                                <button @click="openNoteModal()" class="text-[11px] font-extrabold text-blue-600 hover:underline cursor-pointer">
                                    Edit Note
                                </button>
                            </div>

                            <div class="p-3.5 bg-amber-50/40 rounded-2xl border border-amber-200/60 text-xs space-y-1">
                                <p class="text-gray-800 italic leading-relaxed" x-text="currentStudent?.note || 'No internal notes added yet.'"></p>
                                <span class="text-[9px] text-gray-400 font-bold block" x-text="'Last updated: ' + (currentStudent?.noteDate || '{{ now()->format('d M Y') }}')"></span>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="grid grid-cols-2 gap-2 text-xs font-extrabold pt-2">
                            <a href="{{ route('teacher.students') }}" class="py-2.5 bg-white hover:bg-gray-50 text-blue-700 rounded-xl border border-blue-200 transition-colors shadow-2xs cursor-pointer text-center">
                                View Full Profile
                            </a>
                            <a href="{{ route('teacher.messages') }}" class="py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors shadow-sm cursor-pointer text-center">
                                Message Student
                            </a>
                        </div>

                    </div>

                </div>

            </div>
        </div>

        {{-- ── 4. ATTENDANCE TAB ── --}}
        <div x-show="tab === 'attendance'" x-cloak class="space-y-6">
            <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 pb-4">
                    <div>
                        <h3 class="font-heading font-black text-base text-gray-900">Student Attendance Roster</h3>
                        <p class="text-xs text-gray-400 font-medium mt-0.5">Real-time attendance tracking for students across all scheduled live sessions.</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <select x-model="attendanceFilter" class="bg-gray-50 border border-gray-200 rounded-xl pl-3 pr-8 py-1.5 text-xs font-bold text-gray-700 outline-none min-w-[150px]">
                            <option value="all">All Students ({{ count($allStudents) }})</option>
                            <option value="good">Good Attendance (≥80%)</option>
                            <option value="attention">Needs Attention (&lt;80%)</option>
                            <option value="absent">Catch-Up Pending</option>
                        </select>
                    </div>
                </div>

                {{-- Attendance Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs font-semibold">
                        <thead>
                            <tr class="text-gray-400 uppercase text-[10px] tracking-wider border-b border-gray-100">
                                <th class="pb-3">Student</th>
                                <th class="pb-3">Enrolled Course</th>
                                <th class="pb-3 text-center">Sessions Held</th>
                                <th class="pb-3 text-center">Attended</th>
                                <th class="pb-3">Attendance Rate</th>
                                <th class="pb-3">Catch-Up Status</th>
                                <th class="pb-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <template x-for="st in paginatedAttendanceList" :key="st.id">
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="py-3.5 flex items-center gap-3">
                                        <div :class="st.avatar_color || 'bg-blue-600'" class="w-8 h-8 rounded-full text-white font-black text-xs flex items-center justify-center shrink-0">
                                            <span x-text="st.initials"></span>
                                        </div>
                                        <div>
                                            <strong class="text-gray-900 block font-extrabold text-xs" x-text="st.name"></strong>
                                            <span class="text-[10px] text-gray-400" x-text="st.id"></span>
                                        </div>
                                    </td>
                                    <td class="py-3.5">
                                        <span class="text-gray-800 font-bold block" x-text="st.subject"></span>
                                        <span class="text-[10px] text-gray-400" x-text="st.class"></span>
                                    </td>
                                    <td class="py-3.5 text-center font-bold text-gray-700" x-text="st.total_classes"></td>
                                    <td class="py-3.5 text-center font-bold text-emerald-600" x-text="st.attended_count"></td>
                                    <td class="py-3.5">
                                        <div class="w-32 space-y-1">
                                            <div class="flex justify-between text-[11px] font-black">
                                                <span :class="st.attendance >= 80 ? 'text-emerald-700' : 'text-rose-700'" x-text="st.attendance + '%'"></span>
                                            </div>
                                            <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                                <div class="h-1.5 rounded-full" 
                                                     :class="st.attendance >= 80 ? 'bg-emerald-500' : 'bg-rose-500'" 
                                                     :style="'width: ' + st.attendance + '%'"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5">
                                        <template x-if="st.absent_count > 0">
                                            <span class="px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200/50 text-[10px] font-extrabold" x-text="st.absent_count + ' Catch-Up Pending'"></span>
                                        </template>
                                        <template x-if="!st.absent_count || st.absent_count === 0">
                                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-extrabold">All Sessions Clear</span>
                                        </template>
                                    </td>
                                    <td class="py-3.5 text-right">
                                        <button @click="selectStudent(st.id)" class="py-1.5 px-3 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-xs font-bold transition-colors cursor-pointer">
                                            View Profile
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-gray-100 text-xs">
                    <div class="flex flex-wrap items-center gap-3 text-gray-500 font-medium">
                        <span x-show="filteredAttendanceList.length > 0"
                              x-text="`Showing ${attendanceStartIndex} to ${attendanceEndIndex} of ${filteredAttendanceList.length} student(s)` + (Object.values(studentsData).length !== filteredAttendanceList.length ? ` (filtered from ${Object.values(studentsData).length} total)` : '')"></span>
                        <span x-show="filteredAttendanceList.length === 0" class="text-gray-400 font-semibold">
                            Showing 0 of 0 students
                        </span>

                        <div class="flex items-center gap-1.5 pl-3 border-l border-gray-200">
                            <span class="text-gray-400 text-[11px]">Show</span>
                            <select x-model.number="attendancePerPage" @change="attendanceCurrentPage = 1" class="bg-gray-50 border border-gray-200/80 rounded-xl px-2.5 py-1 text-xs font-bold text-gray-700 outline-none cursor-pointer">
                                <option value="10">10 rows</option>
                                <option value="25">25 rows</option>
                                <option value="50">50 rows</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center gap-1" x-show="attendanceTotalPages > 1">
                        <button type="button" @click="attendanceCurrentPage > 1 && attendanceCurrentPage--" :disabled="attendanceCurrentPage === 1"
                                class="px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 disabled:opacity-40 disabled:cursor-not-allowed text-gray-600 font-bold transition-colors cursor-pointer" title="Previous Page">
                            ‹
                        </button>
                        <template x-for="p in attendanceTotalPages" :key="p">
                            <button type="button" @click="attendanceCurrentPage = p"
                                    :class="attendanceCurrentPage === p ? 'bg-blue-600 text-white font-black shadow-2xs' : 'bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold'"
                                    class="px-2.5 py-1 rounded-lg text-xs transition-colors cursor-pointer"
                                    x-text="p">
                            </button>
                        </template>
                        <button type="button" @click="attendanceCurrentPage < attendanceTotalPages && attendanceCurrentPage++" :disabled="attendanceCurrentPage === attendanceTotalPages"
                                class="px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 disabled:opacity-40 disabled:cursor-not-allowed text-gray-600 font-bold transition-colors cursor-pointer" title="Next Page">
                            ›
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── 5. PROGRESS TAB ── --}}
        <div x-show="tab === 'progress'" x-cloak class="space-y-6">
            <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 pb-4">
                    <div>
                        <h3 class="font-heading font-black text-base text-gray-900">Academic Progress & Mastery Roster</h3>
                        <p class="text-xs text-gray-400 font-medium mt-0.5">Syllabus progression, task completions, and quiz performance.</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <select x-model="progressFilter" class="bg-gray-50 border border-gray-200 rounded-xl pl-3 pr-8 py-1.5 text-xs font-bold text-gray-700 outline-none min-w-[150px]">
                            <option value="all">All Students ({{ count($allStudents) }})</option>
                            <option value="high">High Progress (≥75%)</option>
                            <option value="low">Needs Attention (&lt;60%)</option>
                        </select>
                    </div>
                </div>

                {{-- Progress Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs font-semibold">
                        <thead>
                            <tr class="text-gray-400 uppercase text-[10px] tracking-wider border-b border-gray-100">
                                <th class="pb-3">Student</th>
                                <th class="pb-3">Course / Level</th>
                                <th class="pb-3">Overall Progress</th>
                                <th class="pb-3 text-center">Tasks Completed</th>
                                <th class="pb-3">Latest Assessment</th>
                                <th class="pb-3">Status</th>
                                <th class="pb-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <template x-for="st in paginatedProgressList" :key="st.id">
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="py-3.5 flex items-center gap-3">
                                        <div :class="st.avatar_color || 'bg-blue-600'" class="w-8 h-8 rounded-full text-white font-black text-xs flex items-center justify-center shrink-0">
                                            <span x-text="st.initials"></span>
                                        </div>
                                        <div>
                                            <strong class="text-gray-900 block font-extrabold text-xs" x-text="st.name"></strong>
                                            <span class="text-[10px] text-gray-400" x-text="st.id"></span>
                                        </div>
                                    </td>
                                    <td class="py-3.5">
                                        <span class="text-gray-800 font-bold block" x-text="st.subject"></span>
                                        <span class="text-[10px] text-gray-400" x-text="st.class"></span>
                                    </td>
                                    <td class="py-3.5">
                                        <div class="w-36 space-y-1">
                                            <div class="flex justify-between text-[11px] font-black">
                                                <span :class="st.progress >= 70 ? 'text-emerald-700' : (st.progress < 50 ? 'text-rose-700' : 'text-blue-700')" x-text="st.progress + '%'"></span>
                                            </div>
                                            <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                                <div class="h-1.5 rounded-full" 
                                                     :class="st.progress >= 70 ? 'bg-emerald-500' : (st.progress < 50 ? 'bg-rose-500' : 'bg-blue-600')" 
                                                     :style="'width: ' + Math.max(4, st.progress) + '%'"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 text-center font-bold text-gray-700" x-text="(st.completed_tasks ?? 0) + ' / ' + (st.total_tasks ?? 0)"></td>
                                    <td class="py-3.5">
                                        <span class="font-extrabold text-gray-800 block" x-text="st.latestAssessment"></span>
                                        <span class="text-[10px] text-gray-400" x-text="st.latestDate"></span>
                                    </td>
                                    <td class="py-3.5">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black"
                                              :class="st.category === 'intervention' ? 'bg-purple-50 text-purple-700 border border-purple-200' : (st.category === 'attention' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200')"
                                              x-text="st.status"></span>
                                    </td>
                                    <td class="py-3.5 text-right">
                                        <button @click="selectStudent(st.id)" class="py-1.5 px-3 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-xs font-bold transition-colors cursor-pointer">
                                            Inspect
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-gray-100 text-xs">
                    <div class="flex flex-wrap items-center gap-3 text-gray-500 font-medium">
                        <span x-show="filteredProgressList.length > 0"
                              x-text="`Showing ${progressStartIndex} to ${progressEndIndex} of ${filteredProgressList.length} student(s)` + (Object.values(studentsData).length !== filteredProgressList.length ? ` (filtered from ${Object.values(studentsData).length} total)` : '')"></span>
                        <span x-show="filteredProgressList.length === 0" class="text-gray-400 font-semibold">
                            Showing 0 of 0 students
                        </span>

                        <div class="flex items-center gap-1.5 pl-3 border-l border-gray-200">
                            <span class="text-gray-400 text-[11px]">Show</span>
                            <select x-model.number="progressPerPage" @change="progressCurrentPage = 1" class="bg-gray-50 border border-gray-200/80 rounded-xl px-2.5 py-1 text-xs font-bold text-gray-700 outline-none cursor-pointer">
                                <option value="10">10 rows</option>
                                <option value="25">25 rows</option>
                                <option value="50">50 rows</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center gap-1" x-show="progressTotalPages > 1">
                        <button type="button" @click="progressCurrentPage > 1 && progressCurrentPage--" :disabled="progressCurrentPage === 1"
                                class="px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 disabled:opacity-40 disabled:cursor-not-allowed text-gray-600 font-bold transition-colors cursor-pointer" title="Previous Page">
                            ‹
                        </button>
                        <template x-for="p in progressTotalPages" :key="p">
                            <button type="button" @click="progressCurrentPage = p"
                                    :class="progressCurrentPage === p ? 'bg-blue-600 text-white font-black shadow-2xs' : 'bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold'"
                                    class="px-2.5 py-1 rounded-lg text-xs transition-colors cursor-pointer"
                                    x-text="p">
                            </button>
                        </template>
                        <button type="button" @click="progressCurrentPage < progressTotalPages && progressCurrentPage++" :disabled="progressCurrentPage === progressTotalPages"
                                class="px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 disabled:opacity-40 disabled:cursor-not-allowed text-gray-600 font-bold transition-colors cursor-pointer" title="Next Page">
                            ›
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── 6. INTERVENTION TAB ── --}}
        <div x-show="tab === 'intervention'" x-cloak class="space-y-6">
            <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 pb-4">
                    <div>
                        <h3 class="font-heading font-black text-base text-gray-900">Student Intervention Desk</h3>
                        <p class="text-xs text-gray-400 font-medium mt-0.5">Identify struggling students, log teacher interventions, and assign catch-up assistance.</p>
                    </div>

                    <span class="px-3 py-1 rounded-xl bg-purple-50 text-purple-700 font-extrabold text-xs border border-purple-200/50">
                        {{ $needAttentionCount }} Active Follow-up Case(s)
                    </span>
                </div>

                {{-- Intervention Cards Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse($attentionStudents as $st)
                        <div class="p-5 rounded-3xl border border-gray-200/70 bg-gradient-to-br from-white to-gray-50/50 shadow-sm space-y-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full {{ $st['avatar_color'] ?? 'bg-blue-600' }} text-white font-black text-xs flex items-center justify-center shrink-0 shadow-2xs">
                                        {{ $st['initials'] ?? 'ST' }}
                                    </div>
                                    <div>
                                        <h4 class="font-extrabold text-sm text-gray-900">{{ $st['name'] }}</h4>
                                        <p class="text-[11px] text-gray-500 font-semibold">{{ $st['class'] }} • {{ $st['subject'] }}</p>
                                    </div>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black {{ $st['category'] === 'intervention' ? 'bg-purple-100 text-purple-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $st['status'] }}
                                </span>
                            </div>

                            <div class="p-3 bg-rose-50/60 rounded-2xl border border-rose-100 text-xs text-rose-800 font-bold flex items-center gap-2">
                                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span>{{ $st['issue'] ?? 'Action required' }}</span>
                            </div>

                            <div class="grid grid-cols-3 gap-2 text-center text-xs">
                                <div class="p-2.5 bg-white rounded-xl border border-gray-100">
                                    <span class="font-black text-gray-900 block text-xs">{{ $st['attendance'] }}%</span>
                                    <span class="text-[9px] text-gray-400 font-bold">Attendance</span>
                                </div>
                                <div class="p-2.5 bg-white rounded-xl border border-gray-100">
                                    <span class="font-black text-gray-900 block text-xs">{{ $st['progress'] }}%</span>
                                    <span class="text-[9px] text-gray-400 font-bold">Progress</span>
                                </div>
                                <div class="p-2.5 bg-white rounded-xl border border-gray-100">
                                    <span class="font-black text-gray-900 block text-xs">{{ $st['latestAssessment'] }}</span>
                                    <span class="text-[9px] text-gray-400 font-bold">Score</span>
                                </div>
                            </div>

                            <div class="text-xs text-gray-600 bg-white p-3 rounded-xl border border-gray-100 space-y-1">
                                <p class="font-bold text-gray-400 text-[10px] uppercase tracking-wider">Teacher Guidance & Note</p>
                                <p class="italic text-gray-700 leading-relaxed">{{ $st['note'] }}</p>
                            </div>

                            <div class="flex items-center gap-2 pt-1">
                                <button @click="selectStudent('{{ $st['id'] }}')" class="flex-1 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-colors cursor-pointer text-center">
                                    Open Quick Profile
                                </button>
                                <a href="{{ route('teacher.messages') }}" class="py-2 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold transition-colors">
                                    Message
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 p-12 text-center bg-gray-50/50 rounded-3xl border border-gray-100 text-gray-500 space-y-3">
                            <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto font-black text-lg">✓</div>
                            <h4 class="font-heading font-black text-base text-gray-900">Zero Critical Interventions Required</h4>
                            <p class="text-xs text-gray-400 max-w-md mx-auto">Every enrolled student is meeting their attendance targets and completing assignments on schedule.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ── 7. EDIT NOTE MODAL ── --}}
        <div x-show="noteModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 w-full max-w-md p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="font-heading font-black text-base text-gray-900">Internal Teacher Note</h3>
                    <button @click="noteModalOpen = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg cursor-pointer">✕</button>
                </div>

                <div class="space-y-2">
                    <p class="text-xs text-gray-500">Add an internal observation or pedagogical guidance for <strong class="text-gray-800" x-text="currentStudent?.name"></strong>.</p>
                    <textarea x-model="noteText" rows="4" class="w-full rounded-2xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 p-3 text-xs font-medium text-gray-800" placeholder="Type internal observation, follow-up reminders..."></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button @click="noteModalOpen = false" class="py-2 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold transition-colors cursor-pointer">
                        Cancel
                    </button>
                    <button @click="saveNote()" class="py-2 px-5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-extrabold transition-colors cursor-pointer shadow-xs">
                        Save Note
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-teacher-layout>
