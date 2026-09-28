<x-teacher-layout>
    <x-slot name="title">Student Monitoring - Teacher Portal</x-slot>

    <div x-data="{ 
        tab: 'overview',
        selectedStudentKey: null,
        studentsData: {{ \Illuminate\Support\Js::from(collect($allStudents ?? [])->keyBy('id')->toArray()) }},
        get currentStudent() {
            return this.selectedStudentKey ? this.studentsData[this.selectedStudentKey] : null;
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
                    <p class="text-xs text-gray-500 font-medium mt-0.5">Monitor attendance, academic progress and student performance in one place.</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <div class="py-2 px-3 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 inline-flex items-center gap-1.5">
                    <span>5 Aug 2026 – 12 Aug 2026</span>
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <button class="py-2 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-extrabold transition-colors cursor-pointer inline-flex items-center gap-1.5">
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
                    <p class="text-2xl font-black text-gray-900">{{ $attStats['pct'] ?? 0 }}%</p>
                    <p class="text-xs font-bold text-gray-400">Overall Attendance</p>
                    <p class="text-[11px] text-gray-500 font-medium">{{ $attStats['attended'] ?? 0 }}/{{ $attStats['total'] ?? 0 }} this month</p>
                </div>
            </div>

            {{-- Class Average --}}
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-blue-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <div>
                    @php $avgProgress = count($allStudents ?? []) > 0 ? (int) round(collect($allStudents)->avg('progress')) : 0; @endphp
                    <p class="text-2xl font-black text-gray-900">{{ $avgProgress }}%</p>
                    <p class="text-xs font-bold text-gray-400">Avg. Student Progress</p>
                    <p class="text-[11px] text-gray-500 font-medium">{{ count($allStudents ?? []) }} students total</p>
                </div>
            </div>

            {{-- Students Need Attention --}}
            <div class="bg-amber-50/40 rounded-3xl p-5 border border-amber-200/70 shadow-sm flex items-center gap-4 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-amber-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    @php $needAttention = collect($allStudents ?? [])->filter(fn($s) => $s['category'] !== 'good')->count(); @endphp
                    <p class="text-2xl font-black text-amber-900">{{ $needAttention }}</p>
                    <p class="text-xs font-bold text-amber-800/80">Students Need Attention</p>
                    <p class="text-[11px] text-amber-700 font-semibold">Low progress or attendance</p>
                </div>
            </div>

            {{-- Intervention Active --}}
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-purple-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-purple-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    @php $interventionCount = collect($allStudents ?? [])->filter(fn($s) => $s['category'] === 'intervention')->count(); @endphp
                    <p class="text-2xl font-black text-purple-700">{{ $interventionCount }}</p>
                    <p class="text-xs font-bold text-gray-400">Intervention Active</p>
                    <p class="text-[11px] text-gray-500 font-medium">Progress below 55%</p>
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
                    class="px-6 py-2.5 rounded-xl text-xs transition-all cursor-pointer">
                Intervention
            </button>
        </div>

        {{-- ── 3. MIDDLE SECTION GRID (2 COLUMNS) ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- LEFT SECTION: 8 COLUMNS --}}
            <div class="lg:col-span-8 space-y-6">
                
                {{-- Card 1: Students Needing Attention --}}
                <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <h3 class="font-heading font-black text-base text-gray-900">Students Needing Attention</h3>
                        <a href="{{ route('teacher.students') }}" class="text-xs font-extrabold text-blue-600 hover:underline">View All (5)</a>
                    </div>

                    <div class="space-y-3">
                        
                        {{-- Student Item 1 --}}
                        <div @click="selectedStudentKey = 'hariz'" class="p-3.5 bg-gray-50/80 rounded-2xl border border-gray-100 flex items-center justify-between gap-3 hover:bg-gray-100/60 transition-colors cursor-pointer">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-900 text-white font-black text-xs flex items-center justify-center shrink-0">MH</div>
                                <div>
                                    <h4 class="font-extrabold text-xs text-gray-900">Muhammad Hariz</h4>
                                    <p class="text-[10px] text-gray-400 font-semibold">Form 4A • Mathematics</p>
                                </div>
                            </div>

                            <div class="text-right">
                                <span class="px-2.5 py-0.5 rounded-full bg-red-50 text-red-700 font-black text-[10px]">Absent</span>
                                <p class="text-[10px] text-amber-700 font-bold mt-0.5">Catch-Up Pending</p>
                            </div>
                        </div>

                        {{-- Student Item 2 --}}
                        <div @click="selectedStudentKey = 'nur'" class="p-3.5 bg-gray-50/80 rounded-2xl border border-gray-100 flex items-center justify-between gap-3 hover:bg-gray-100/60 transition-colors cursor-pointer">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-emerald-700 text-white font-black text-xs flex items-center justify-center shrink-0">AN</div>
                                <div>
                                    <h4 class="font-extrabold text-xs text-gray-900">Aishah Nur</h4>
                                    <p class="text-[10px] text-gray-400 font-semibold">Form 4A • Mathematics</p>
                                </div>
                            </div>

                            <div class="text-right">
                                <span class="px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 font-black text-[10px]">Assessment Below Target</span>
                                <p class="text-[10px] text-gray-500 font-bold mt-0.5">Algebra (54%)</p>
                            </div>
                        </div>

                        {{-- Student Item 3 --}}
                        <div @click="selectedStudentKey = 'ravi'" class="p-3.5 bg-gray-50/80 rounded-2xl border border-gray-100 flex items-center justify-between gap-3 hover:bg-gray-100/60 transition-colors cursor-pointer">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-purple-700 text-white font-black text-xs flex items-center justify-center shrink-0">RS</div>
                                <div>
                                    <h4 class="font-extrabold text-xs text-gray-900">Ravi Shankar</h4>
                                    <p class="text-[10px] text-gray-400 font-semibold">Form 4B • Physics</p>
                                </div>
                            </div>

                            <div class="text-right">
                                <span class="px-2.5 py-0.5 rounded-full bg-orange-50 text-orange-700 font-black text-[10px]">Intervention Active</span>
                                <p class="text-[10px] text-gray-500 font-bold mt-0.5">Problem Solving</p>
                            </div>
                        </div>

                        {{-- Student Item 4 --}}
                        <div class="p-3.5 bg-gray-50/80 rounded-2xl border border-gray-100 flex items-center justify-between gap-3 hover:bg-gray-100/60 transition-colors cursor-pointer">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-indigo-700 text-white font-black text-xs flex items-center justify-center shrink-0">LX</div>
                                <div>
                                    <h4 class="font-extrabold text-xs text-gray-900">Lim Xin Yi</h4>
                                    <p class="text-[10px] text-gray-400 font-semibold">Form 4B • Mathematics</p>
                                </div>
                            </div>

                            <div class="text-right">
                                <span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 font-black text-[10px]">1 Task Overdue</span>
                                <p class="text-[10px] text-gray-400 font-bold mt-0.5">Due 12 Aug 2026</p>
                            </div>
                        </div>

                        {{-- Student Item 5 --}}
                        <div class="p-3.5 bg-gray-50/80 rounded-2xl border border-gray-100 flex items-center justify-between gap-3 hover:bg-gray-100/60 transition-colors cursor-pointer">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-teal-700 text-white font-black text-xs flex items-center justify-center shrink-0">AS</div>
                                <div>
                                    <h4 class="font-extrabold text-xs text-gray-900">Aisyah Sofia</h4>
                                    <p class="text-[10px] text-gray-400 font-semibold">Year 6A • Science</p>
                                </div>
                            </div>

                            <div class="text-right">
                                <span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 font-black text-[10px]">Low Attendance</span>
                                <p class="text-[10px] text-gray-500 font-bold mt-0.5">82% this month</p>
                            </div>
                        </div>

                    </div>

                    <div class="pt-2 text-center">
                        <a href="{{ route('teacher.students') }}" class="py-2.5 px-6 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-extrabold text-xs rounded-xl inline-block transition-colors">
                            View All Students
                        </a>
                    </div>
                </div>

                {{-- Card 2: Class Performance (This Week) --}}
                <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-5">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <h3 class="font-heading font-black text-base text-gray-900">Class Performance (This Week)</h3>
                        <a href="#" class="text-xs font-extrabold text-blue-600 hover:underline">View Full Report</a>
                    </div>

                    <div class="space-y-4 text-xs font-bold">
                        <div>
                            <div class="flex justify-between mb-1"><span>Algebra</span><span class="text-emerald-600 font-black">82%</span></div>
                            <div class="w-full bg-gray-100 rounded-full h-2"><div class="bg-emerald-500 h-2 rounded-full" style="width: 82%"></div></div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-1"><span>Linear Equations</span><span class="text-emerald-600 font-black">76%</span></div>
                            <div class="w-full bg-gray-100 rounded-full h-2"><div class="bg-emerald-500 h-2 rounded-full" style="width: 76%"></div></div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-1"><span>Functions</span><span class="text-blue-600 font-black">68%</span></div>
                            <div class="w-full bg-gray-100 rounded-full h-2"><div class="bg-blue-600 h-2 rounded-full" style="width: 68%"></div></div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-1"><span>Word Problems</span><span class="text-amber-600 font-black">62%</span></div>
                            <div class="w-full bg-gray-100 rounded-full h-2"><div class="bg-amber-500 h-2 rounded-full" style="width: 62%"></div></div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-1"><span>Problem Solving</span><span class="text-red-600 font-black">58% (Low)</span></div>
                            <div class="w-full bg-gray-100 rounded-full h-2"><div class="bg-red-500 h-2 rounded-full" style="width: 58%"></div></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-3 border-t border-gray-100 text-center text-xs">
                        <div class="p-3 bg-gray-50 rounded-2xl">
                            <span class="font-black text-gray-900 text-base block">24</span>
                            <span class="text-[10px] text-gray-400 font-bold">Total Students</span>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-2xl">
                            <span class="font-black text-blue-600 text-base block">6</span>
                            <span class="text-[10px] text-gray-400 font-bold">Assessments Taken</span>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-2xl">
                            <span class="font-black text-emerald-600 text-base block">87%</span>
                            <span class="text-[10px] text-gray-400 font-bold">Tasks Completed</span>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-2xl">
                            <span class="font-black text-purple-600 text-base block">72%</span>
                            <span class="text-[10px] text-gray-400 font-bold">Catch-Up Completed</span>
                        </div>
                    </div>
                </div>

            </div>

            {{-- RIGHT SECTION: STUDENT QUICK PROFILE SIDE CARD (4 COLUMNS) --}}
            <div class="lg:col-span-4 space-y-6">
                
                <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-5">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <h3 class="font-heading font-black text-base text-gray-900">Student Quick Profile</h3>
                        
                        <select x-model="selectedStudentKey" class="bg-gray-50 border border-gray-200 rounded-xl px-2.5 py-1 text-[11px] font-bold text-gray-700 outline-none">
                            <option value="hariz">Muhammad Hariz</option>
                            <option value="nur">Aishah Nur</option>
                            <option value="ravi">Ravi Shankar</option>
                        </select>
                    </div>

                    {{-- Student Header --}}
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-full bg-blue-900 text-white font-black text-sm flex items-center justify-center shrink-0">
                            MH
                        </div>
                        <div>
                            <h4 class="font-heading font-black text-base text-gray-900" x-text="currentStudent.name"></h4>
                            <p class="text-xs text-gray-500 font-semibold" x-text="'ID: ' + currentStudent.id + ' • ' + currentStudent.form"></p>
                        </div>
                    </div>

                    {{-- 4 Stat Pills --}}
                    <div class="grid grid-cols-4 gap-2 text-center text-xs">
                        <div class="p-2.5 bg-gray-50 rounded-2xl">
                            <span class="font-black text-emerald-600 text-sm block" x-text="currentStudent.attendance"></span>
                            <span class="text-[9px] text-gray-400 font-bold">Attendance</span>
                        </div>
                        <div class="p-2.5 bg-gray-50 rounded-2xl">
                            <span class="font-black text-blue-600 text-sm block" x-text="currentStudent.progress"></span>
                            <span class="text-[9px] text-gray-400 font-bold">Overall Progress</span>
                        </div>
                        <div class="p-2.5 bg-gray-50 rounded-2xl">
                            <span class="font-black text-amber-600 text-sm block" x-text="currentStudent.pending"></span>
                            <span class="text-[9px] text-gray-400 font-bold">Pending Task</span>
                        </div>
                        <div class="p-2.5 bg-gray-50 rounded-2xl">
                            <span class="font-black text-purple-700 text-sm block" x-text="currentStudent.latest"></span>
                            <span class="text-[9px] text-gray-400 font-bold">Latest Assessment</span>
                        </div>
                    </div>

                    {{-- Recent Activity Timeline --}}
                    <div class="space-y-3 pt-2">
                        <h4 class="font-heading font-black text-xs text-gray-400 uppercase tracking-wider">Recent Activity</h4>

                        <div class="space-y-2 text-xs">
                            <div class="flex items-center justify-between p-2.5 bg-red-50/50 rounded-xl border border-red-100">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-red-600"></span>
                                    <div>
                                        <strong class="text-gray-900 block font-bold">Absent</strong>
                                        <span class="text-[10px] text-gray-500 font-semibold">12 Aug 2026 • Catch-Up Pending</span>
                                    </div>
                                </div>
                                <a href="#" class="text-[10px] text-blue-600 font-bold hover:underline">View</a>
                            </div>

                            <div class="flex items-center justify-between p-2.5 bg-amber-50/50 rounded-xl border border-amber-100">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                    <div>
                                        <strong class="text-gray-900 block font-bold">Assessment Below Target</strong>
                                        <span class="text-[10px] text-gray-500 font-semibold">Algebra (54%)</span>
                                    </div>
                                </div>
                                <a href="#" class="text-[10px] text-blue-600 font-bold hover:underline">View</a>
                            </div>

                            <div class="flex items-center justify-between p-2.5 bg-blue-50/50 rounded-xl border border-blue-100">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                                    <div>
                                        <strong class="text-gray-900 block font-bold">Task Submitted</strong>
                                        <span class="text-[10px] text-gray-500 font-semibold">Linear Equation Quiz</span>
                                    </div>
                                </div>
                                <a href="#" class="text-[10px] text-blue-600 font-bold hover:underline">View</a>
                            </div>

                            <div class="flex items-center justify-between p-2.5 bg-emerald-50/50 rounded-xl border border-emerald-100">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <div>
                                        <strong class="text-gray-900 block font-bold">Catch-Up Completed</strong>
                                        <span class="text-[10px] text-gray-500 font-semibold">Science – Energy Notes</span>
                                    </div>
                                </div>
                                <a href="#" class="text-[10px] text-blue-600 font-bold hover:underline">View</a>
                            </div>
                        </div>
                    </div>

                    {{-- Teacher Note (Internal) --}}
                    <div class="space-y-2 pt-2 border-t border-gray-100">
                        <div class="flex items-center justify-between">
                            <h4 class="font-heading font-black text-xs text-gray-400 uppercase tracking-wider">Teacher Note (Internal)</h4>
                            <a href="#" class="text-[11px] font-extrabold text-blue-600 hover:underline">Edit</a>
                        </div>

                        <div class="p-3.5 bg-amber-50/40 rounded-2xl border border-amber-200/60 text-xs space-y-1">
                            <p class="text-gray-800 italic leading-relaxed" x-text="currentStudent.note"></p>
                            <span class="text-[9px] text-gray-400 font-bold block" x-text="'Last updated: ' + currentStudent.noteDate"></span>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="grid grid-cols-2 gap-2 text-xs font-extrabold pt-2">
                        <a href="{{ route('teacher.students') }}" class="py-2.5 bg-white hover:bg-gray-50 text-blue-700 rounded-xl border border-blue-200 transition-colors shadow-2xs cursor-pointer text-center">
                            View Full Profile
                        </a>
                        <button class="py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors shadow-sm cursor-pointer">
                            Add Note
                        </button>
                    </div>

                </div>

            </div>

        </div>

    </div>
</x-teacher-layout>
