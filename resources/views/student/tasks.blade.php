<x-dashboard-layout>
    <x-slot name="title">Tasks</x-slot>
    <x-slot name="sidebar">
        @include('student.partials.sidebar')
    </x-slot>

    <div x-data="tasksData()" class="space-y-6">

        {{-- ── NOTIFICATIONS & FLASH MESSAGES ── --}}
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl flex items-center justify-between text-xs font-bold shadow-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-black cursor-pointer">✕</button>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 p-4 rounded-2xl text-xs font-bold shadow-sm space-y-1">
                <p class="font-black">Please check your submission:</p>
                <ul class="list-disc list-inside font-medium space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ── TOP HEADER BAR ── --}}
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="font-heading font-black text-2xl text-gray-900">Tasks & Assignments</h1>
                <p class="text-xs text-gray-500 font-medium mt-0.5">Click on any task to view instructions and submit your work.</p>
            </div>
        </div>

        {{-- ── 1. TOP 4 KPI METRIC CARDS ── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            {{-- Pending Tasks --}}
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-3.5 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-blue-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Pending Tasks</p>
                    <p class="text-2xl font-black text-gray-900">{{ $pendingCount ?? 0 }}</p>
                    <p class="text-xs text-gray-500 font-medium">Need attention</p>
                </div>
            </div>

            {{-- Due This Week --}}
            <div class="bg-amber-50/50 rounded-3xl p-5 border border-amber-200/70 shadow-sm flex items-center gap-3.5 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-amber-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-amber-800/80 uppercase tracking-wider">Due This Week</p>
                    <p class="text-2xl font-black text-gray-900">{{ $dueThisWeekCount ?? 0 }}</p>
                    <p class="text-xs text-amber-800/80 font-medium">Tasks to be completed</p>
                </div>
            </div>

            {{-- Completed --}}
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-3.5 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-emerald-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Completed</p>
                    <p class="text-2xl font-black text-gray-900">{{ $completedCount ?? 0 }}</p>
                    <p class="text-xs text-gray-500 font-medium">This month</p>
                </div>
            </div>

            {{-- Completion Rate --}}
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-3.5 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-purple-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-purple-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Completion Rate</p>
                    <p class="text-2xl font-black text-purple-700">{{ $completionRate ?? 0 }}%</p>
                    <p class="text-xs text-gray-500 font-medium">Overall progress</p>
                </div>
            </div>
        </div>

        {{-- ── 2. MAIN WORKSPACE GRID ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- LEFT COLUMN: TASK LIST (2 COLS) --}}
            <div class="lg:col-span-2 space-y-6">

                <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-5">
                    
                    {{-- Sub-Tabs & Subject Filter Header --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 pb-4">
                        
                        {{-- Task Filter Tabs --}}
                        <div class="flex flex-wrap gap-4 text-xs font-extrabold">
                            <button @click="tab = 'all'" 
                                    :class="tab === 'all' ? 'text-blue-600 border-b-2 border-blue-600 pb-3 -mb-4' : 'text-gray-500 hover:text-gray-900 pb-3'"
                                    class="transition-colors cursor-pointer">
                                All Tasks
                            </button>
                            <button @click="tab = 'week'" 
                                    :class="tab === 'week' ? 'text-blue-600 border-b-2 border-blue-600 pb-3 -mb-4' : 'text-gray-500 hover:text-gray-900 pb-3'"
                                    class="transition-colors cursor-pointer">
                                Due This Week
                            </button>
                            <button @click="tab = 'catch-up'" 
                                    :class="tab === 'catch-up' ? 'text-purple-600 border-b-2 border-purple-600 pb-3 -mb-4' : 'text-gray-500 hover:text-gray-900 pb-3'"
                                    class="transition-colors cursor-pointer flex items-center gap-1">
                                Catch-up
                                @if(($catchUpCount ?? 0) > 0)
                                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-purple-100 text-purple-700 font-black">{{ $catchUpCount }}</span>
                                @endif
                            </button>
                            <button @click="tab = 'completed'" 
                                    :class="tab === 'completed' ? 'text-blue-600 border-b-2 border-blue-600 pb-3 -mb-4' : 'text-gray-500 hover:text-gray-900 pb-3'"
                                    class="transition-colors cursor-pointer">
                                Completed
                            </button>
                            <button @click="tab = 'overdue'" 
                                    :class="tab === 'overdue' ? 'text-blue-600 border-b-2 border-blue-600 pb-3 -mb-4' : 'text-gray-500 hover:text-gray-900 pb-3'"
                                    class="transition-colors cursor-pointer">
                                Overdue
                            </button>
                        </div>

                        {{-- Subject Dropdown Filter --}}
                        <div class="flex items-center gap-2 shrink-0">
                            <select x-model="selectedSubject" class="bg-gray-50 border border-gray-200/80 text-gray-900 text-xs font-extrabold rounded-xl px-3 py-2 outline-none cursor-pointer focus:ring-2 focus:ring-blue-500">
                                <option value="all">All Subjects</option>
                                @foreach($enrolledSubjects ?? [] as $subj)
                                    <option value="{{ $subj }}">{{ $subj }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- PINNED DIAGNOSTIC TEST CARD --}}
                    @if(isset($diagnosticTask) && $diagnosticTask->assignment)
                    @php
                        $isDiagDone = in_array($diagnosticTask->status, ['submitted', 'graded', 'completed']);
                    @endphp
                    <div class="mb-4 p-4 rounded-2xl border transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3 {{ $isDiagDone ? 'bg-emerald-50/40 border-emerald-200' : 'bg-gradient-to-r from-blue-50/80 to-indigo-50/60 border-blue-200 shadow-2xs' }}">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-xs shrink-0 {{ $isDiagDone ? 'bg-emerald-600 text-white' : 'bg-blue-600 text-white shadow-xs' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    @if($isDiagDone)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800">
                                        Completed
                                    </span>
                                    @endif
                                    <h4 class="font-heading font-black text-sm text-gray-900">{{ $diagnosticTask->assignment->title }}</h4>
                                </div>
                                <p class="text-xs text-gray-500 font-medium mt-0.5">
                                    {{ $isDiagDone ? 'Your score: ' . ($diagnosticTask->score ?? 0) . '% (Automatically graded)' : 'Diagnostic entry assessment to evaluate your academic level.' }}
                                </p>
                            </div>
                        </div>

                        <div class="shrink-0 flex items-center gap-2">
                            @if($isDiagDone)
                            <span class="px-3 py-1 rounded-xl bg-emerald-100 text-emerald-800 font-black text-xs">
                                Score: {{ $diagnosticTask->score }}%
                            </span>
                            <a href="{{ route('student.diagnostic.take', $diagnosticTask->assignment_id) }}" class="py-2 px-3.5 rounded-xl border border-gray-200 text-gray-700 hover:bg-gray-100 text-xs font-bold transition">
                                Review
                            </a>
                            @else
                            <a href="{{ route('student.diagnostic.take', $diagnosticTask->assignment_id) }}" class="py-2 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-extrabold shadow-sm transition inline-flex items-center gap-1.5 cursor-pointer">
                                <span>Take Test</span>
                                <span>→</span>
                            </a>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- FILTERED TASK LIST (SEAMLESS HIDDEN SCROLLBAR) --}}
                    <div class="space-y-3 pt-1 max-h-[460px] overflow-y-auto no-scrollbar">
                        
                        <template x-if="filteredTasks.length === 0">
                            <div class="p-8 text-center bg-gray-50 rounded-2xl border border-gray-100 space-y-2">
                                <p class="font-heading font-black text-sm text-gray-700">No tasks found matching this filter.</p>
                                <p class="text-xs text-gray-500">Try switching tabs or selecting all subjects above.</p>
                            </div>
                        </template>

                        <template x-for="task in filteredTasks" :key="task.id">
                            <div @click="openTaskModal(task)" class="p-4 bg-white rounded-2xl border border-gray-100 hover:border-blue-300 hover:shadow-md flex items-center justify-between gap-4 transition-all shadow-2xs cursor-pointer group">
                                <div class="flex items-center gap-3.5">
                                    <div :class="task.status === 'completed' || task.status === 'submitted' || task.status === 'graded' ? 'bg-emerald-50 text-emerald-600' : (task.status === 'overdue' ? 'bg-red-50 text-red-600' : (task.status === 'catch-up' ? 'bg-purple-50 text-purple-600' : 'bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white'))" class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-xs shrink-0 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <div>
                                        <h5 class="font-heading font-extrabold text-sm text-gray-900 group-hover:text-blue-600 transition-colors" x-text="task.title"></h5>
                                        <p class="text-xs text-gray-400 font-medium" x-text="`${task.subject} • ${task.classLevel || 'Course'}`"></p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4 text-xs shrink-0">
                                    <div class="text-right hidden sm:block">
                                        <p class="font-bold text-gray-900" x-text="task.due"></p>
                                        <p class="text-[11px] font-medium" :class="task.isCatchUp ? 'text-purple-600 font-bold' : 'text-gray-400'" x-text="task.isCatchUp ? 'Pre-enrollment' : task.time"></p>
                                    </div>
                                    
                                    <span :class="task.status === 'overdue' ? 'bg-red-50 text-red-700 border-red-200' : (task.status === 'catch-up' ? 'bg-purple-50 text-purple-700 border-purple-200 font-bold' : (task.status === 'completed' || task.status === 'submitted' || task.status === 'graded' ? 'bg-emerald-50 text-emerald-700 border-emerald-200/60' : 'bg-amber-50 text-amber-700 border-amber-200/60'))" 
                                          class="px-3 py-1 rounded-full font-black text-[11px] capitalize border" 
                                          x-text="task.status === 'catch-up' ? 'Catch-up' : task.status"></span>

                                    <button type="button" :title="task.status === 'pending' || task.status === 'overdue' || task.status === 'catch-up' ? 'Do Task' : 'View Submission'" class="w-8 h-8 rounded-xl bg-blue-50 group-hover:bg-blue-600 text-blue-600 group-hover:text-white inline-flex items-center justify-center transition-all shadow-2xs">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                </div>
                            </div>
                        </template>

                    </div>

                    <div class="pt-2 text-center border-t border-gray-50">
                        <button @click="tab = 'all'; selectedSubject = 'all'" class="text-xs font-extrabold text-blue-600 hover:text-blue-800 cursor-pointer">
                            View All Tasks
                        </button>
                    </div>

                </div>

            </div>

            {{-- RIGHT COLUMN: SIDEBAR PANELS (1 COL) --}}
            <div class="space-y-6">

                {{-- Widget 1: Upcoming Deadlines --}}
                <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <h4 class="font-heading font-extrabold text-sm text-gray-900">Upcoming Deadlines</h4>
                        </div>
                        <a href="{{ route('student.classes') }}" class="text-[11px] font-extrabold text-blue-600 hover:underline">View Calendar</a>
                    </div>

                    <div class="space-y-3 max-h-64 overflow-y-auto no-scrollbar">
                        @forelse($upcomingDeadlines ?? [] as $sa)
                            @if(!$sa->assignment) @continue @endif
                            <div class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-gray-50 transition-colors">
                                <div>
                                    <h5 class="text-xs font-bold text-gray-900 leading-tight">{{ $sa->assignment?->title ?? 'Assignment' }}</h5>
                                    <p class="text-[11px] text-gray-400 font-medium">
                                        {{ $sa->assignment?->course?->subject?->name ?? 'Subject' }} • {{ $sa->assignment?->due_date ? \Carbon\Carbon::parse($sa->assignment?->due_date)->format('d M Y') : 'No Due Date' }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 font-medium italic p-2">No pending upcoming deadlines.</p>
                        @endforelse
                    </div>

                    <div class="pt-2 border-t border-gray-50">
                        <a href="{{ route('student.classes') }}" class="text-xs font-extrabold text-blue-600 hover:text-blue-800 inline-flex items-center gap-1">
                            View Calendar <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                </div>

            </div>

        </div>

        {{-- ── 3. DO / SUBMIT TASK MODAL ── --}}
        <div x-show="showTaskModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs p-4 sm:p-6 flex justify-center items-start sm:items-center">
            <div @click.away="showTaskModal = false" class="relative bg-white rounded-3xl p-6 sm:p-8 max-w-2xl w-full border border-gray-100 shadow-2xl space-y-6 my-auto max-h-[90vh] overflow-y-auto">
                
                {{-- Modal Header --}}
                <div class="flex items-start justify-between border-b border-gray-100 pb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-heading font-black text-xl text-gray-900" x-text="selectedTask?.title"></h3>
                            <span :class="selectedTask?.status === 'completed' || selectedTask?.status === 'submitted' || selectedTask?.status === 'graded' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : (selectedTask?.status === 'catch-up' ? 'bg-purple-50 text-purple-700 border-purple-200 font-bold' : (selectedTask?.status === 'overdue' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-amber-50 text-amber-700 border-amber-200'))"
                                  class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border"
                                  x-text="selectedTask?.status === 'catch-up' ? 'Catch-up' : selectedTask?.status"></span>
                        </div>
                        <p class="text-xs text-gray-500 font-medium mt-1" x-text="`${selectedTask?.subject} • ${selectedTask?.classLevel || 'Course'} • Due: ${selectedTask?.due}`"></p>
                    </div>
                    <button type="button" @click="showTaskModal = false" class="w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-400 hover:text-gray-700 font-bold text-lg cursor-pointer">✕</button>
                </div>

                {{-- Catch-up Notice for Tasks Assigned Prior to Enrollment --}}
                <template x-if="selectedTask?.isCatchUp">
                    <div class="p-3.5 bg-purple-50 border border-purple-200 rounded-2xl flex items-start gap-3 text-purple-900">
                        <div class="w-7 h-7 rounded-xl bg-purple-200/70 text-purple-800 flex items-center justify-center shrink-0 font-bold text-xs mt-0.5">
                            💡
                        </div>
                        <div class="text-xs">
                            <p class="font-black text-purple-950">Assigned Prior to Your Enrollment</p>
                            <p class="text-purple-800 text-[11px] mt-0.5 font-medium leading-relaxed">This task was issued before you joined this subject. You can review the materials and submit your solution at your own pace without any late penalty!</p>
                        </div>
                    </div>
                </template>

                {{-- Task Instructions / Description --}}
                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 space-y-2">
                    <h4 class="font-heading font-black text-xs text-gray-500 uppercase tracking-wider">Teacher Instructions</h4>
                    <p class="text-xs text-gray-800 leading-relaxed whitespace-pre-line" x-text="selectedTask?.description || 'Please complete the assigned exercise and submit your answer or file attachment.'"></p>
                </div>

                {{-- Teacher Attached Material / Worksheet File --}}
                <template x-if="selectedTask?.attachmentUrl">
                    <div class="p-4 bg-blue-50/80 border border-blue-200 rounded-2xl flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div>
                                <h5 class="font-heading font-black text-[10px] text-blue-800 uppercase tracking-wider">Teacher's Attached Worksheet / Material</h5>
                                <p class="text-xs font-extrabold text-blue-950" x-text="selectedTask?.attachmentName || 'Download Question Material'"></p>
                            </div>
                        </div>
                        <a :href="selectedTask?.attachmentUrl" target="_blank" class="py-2 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs transition-colors shrink-0 shadow-2xs inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Download Worksheet</span>
                        </a>
                    </div>
                </template>

                {{-- Already Submitted State --}}
                <template x-if="selectedTask?.status === 'submitted' || selectedTask?.status === 'graded' || selectedTask?.status === 'completed'">
                    <div class="space-y-4">
                        <div class="p-4 bg-emerald-50/70 border border-emerald-200 rounded-2xl space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-heading font-black text-xs text-emerald-800 uppercase tracking-wider flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Your Submission
                                </span>
                                <span class="text-[11px] text-emerald-700 font-bold" x-text="`Submitted on ${selectedTask?.submittedAt || 'Recently'}`"></span>
                            </div>
                            
                            <template x-if="selectedTask?.score !== null && selectedTask?.score !== undefined">
                                <div class="pt-2 border-t border-emerald-200/60 flex items-center justify-between text-xs">
                                    <span class="font-bold text-emerald-900">Score / Grade:</span>
                                    <span class="font-black text-base text-emerald-700" x-text="`${selectedTask?.score} / 100`"></span>
                                </div>
                            </template>

                            <template x-if="selectedTask?.teacherFeedback">
                                <div class="pt-2 p-3 bg-white rounded-xl border border-emerald-200 text-xs">
                                    <span class="font-extrabold block text-[10px] uppercase text-emerald-800">Teacher's Feedback & Comments:</span>
                                    <p class="text-gray-800 mt-1 font-medium leading-relaxed" x-text="selectedTask?.teacherFeedback"></p>
                                </div>
                            </template>

                            <template x-if="selectedTask?.submissionNotes">
                                <p class="text-xs text-gray-800 whitespace-pre-line pt-1" x-text="selectedTask?.submissionNotes"></p>
                            </template>

                            <template x-if="selectedTask?.fileUrl">
                                <div class="pt-2">
                                    <a :href="selectedTask?.fileUrl" target="_blank" class="inline-flex items-center gap-1.5 py-1.5 px-3 bg-white text-emerald-800 border border-emerald-200 rounded-xl text-xs font-bold hover:bg-emerald-100 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span x-text="`View Attachment (${selectedTask?.fileName || 'File'})`"></span>
                                    </a>
                                </div>
                            </template>
                        </div>

                        <div class="text-right">
                            <button type="button" @click="isEditingSubmission = true" class="text-xs font-bold text-blue-600 hover:underline cursor-pointer">
                                Resubmit or update answer →
                            </button>
                        </div>
                    </div>
                </template>

                {{-- Submission Form (Shown when pending, catch-up, overdue, or when editing) --}}
                <div x-show="selectedTask?.status === 'pending' || selectedTask?.status === 'overdue' || selectedTask?.status === 'catch-up' || isEditingSubmission" class="space-y-4">
                    <form :action="selectedTask?.submitUrl" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf

                        <div>
                            <label class="font-extrabold text-xs text-gray-900 block mb-1">Your Solution / Written Answer</label>
                            <textarea name="submission_notes" rows="4" placeholder="Type your answers, explanation or comments here..." class="w-full bg-gray-50 border border-gray-200 rounded-2xl p-3 text-xs outline-none focus:ring-2 focus:ring-blue-500" x-text="selectedTask?.submissionNotes || ''"></textarea>
                        </div>

                        <div>
                            <label class="font-extrabold text-xs text-gray-900 block mb-1">Attach Completed Homework File (Optional)</label>
                            <input type="file" name="file" class="w-full bg-gray-50 border border-gray-200 rounded-2xl p-2.5 text-xs outline-none file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="text-[10px] text-gray-400 mt-1">Supports PDF, Word, Excel, Slides, Images, ZIP (Max: 30MB)</p>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 font-extrabold text-xs">
                            <template x-if="isEditingSubmission">
                                <button type="button" @click="isEditingSubmission = false" class="py-2.5 px-4 rounded-xl bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">Cancel</button>
                            </template>
                            <button type="submit" class="py-2.5 px-6 rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition-all hover:shadow-md cursor-pointer">
                                Submit Task
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>

    </div>

    <script>
        function tasksData() {
            return {
                tab: 'all', 
                selectedSubject: 'all',
                showTaskModal: false,
                selectedTask: null,
                isEditingSubmission: false,
                openTaskModal(task) {
                    this.selectedTask = task;
                    this.isEditingSubmission = false;
                    this.showTaskModal = true;
                },
                tasks: [
                    @foreach($assignments as $sa)
                    @if(!$sa->assignment) @continue @endif
                    @php
                        $isCatchUp = (bool) ($sa->is_catch_up ?? false);
                        $isOverdue = !$isCatchUp && $sa->status === 'pending' && $sa->assignment?->due_date && \Carbon\Carbon::parse($sa->assignment->due_date)->isPast();
                        $displayStatus = $isCatchUp ? 'catch-up' : ($isOverdue ? 'overdue' : ($sa->status === 'submitted' || $sa->status === 'graded' ? 'completed' : $sa->status));
                        $dueCat = $isCatchUp ? 'catch-up' : ($isOverdue ? 'overdue' : ($sa->status === 'pending' ? 'week' : 'completed'));
                        $updatedTs = $sa->assignment?->updated_at?->timestamp 
                            ?? $sa->assignment?->created_at?->timestamp 
                            ?? $sa->updated_at?->timestamp 
                            ?? $sa->created_at?->timestamp 
                            ?? 0;
                    @endphp
                    {
                        id: {{ $sa->id }},
                        assignmentId: {{ $sa->assignment_id }},
                        title: {!! json_encode($sa->assignment?->title ?? 'Assignment') !!},
                        description: {!! json_encode($sa->assignment?->instructions ?: ($sa->assignment?->description ?? '')) !!},
                        instructions: {!! json_encode($sa->assignment?->instructions ?? '') !!},
                        type: {!! json_encode($sa->assignment?->type ?? 'Assignment') !!},
                        subject: {!! json_encode($sa->assignment?->course?->subject?->name ?? ($sa->assignment?->type === 'Diagnostic Test' ? 'Diagnostic Test' : 'General')) !!},
                        classLevel: {!! json_encode($sa->assignment?->course?->level ?? ($sa->assignment?->target_level ?: 'Platform-wide')) !!},
                        due: "{{ $sa->assignment?->due_date ? \Carbon\Carbon::parse($sa->assignment->due_date)->format('d M Y') : 'No Due Date' }}",
                        time: '11:59 PM',
                        isCatchUp: {{ $isCatchUp ? 'true' : 'false' }},
                        status: "{{ $displayStatus }}",
                        dueCategory: "{{ $dueCat }}",
                        score: {{ $sa->score !== null ? $sa->score : 'null' }},
                        attachmentUrl: {!! json_encode($sa->assignment?->file_url ?? '') !!},
                        attachmentName: {!! json_encode($sa->assignment?->file_name ?? '') !!},
                        submissionNotes: {!! json_encode($sa->submission_notes ?? '') !!},
                        teacherFeedback: {!! json_encode($sa->teacher_feedback ?? '') !!},
                        fileName: {!! json_encode($sa->file_name ?? '') !!},
                        fileUrl: {!! json_encode($sa->file_url ?? '') !!},
                        submittedAt: "{{ $sa->submitted_at ? $sa->submitted_at->format('d M Y, h:i A') : '' }}",
                        submitUrl: "{{ route('student.tasks.submit', $sa->id) }}",
                        updatedTimestamp: {{ $updatedTs }}
                    },
                    @endforeach
                ],
                get filteredTasks() {
                    return this.tasks.filter(task => {
                        const matchesTab = this.tab === 'all' || 
                                           (this.tab === 'week' && (task.dueCategory === 'week' || task.status === 'pending')) ||
                                           (this.tab === 'catch-up' && (task.isCatchUp || task.status === 'catch-up')) ||
                                           (this.tab === 'completed' && (task.status === 'completed' || task.status === 'submitted' || task.status === 'graded')) ||
                                           (this.tab === 'overdue' && (task.status === 'overdue' || task.dueCategory === 'overdue'));
                        const matchesSubject = this.selectedSubject === 'all' || task.subject === this.selectedSubject;
                        return matchesTab && matchesSubject;
                    }).sort((a, b) => (b.updatedTimestamp || 0) - (a.updatedTimestamp || 0) || b.id - a.id);
                }
            };
        }
    </script>
</x-dashboard-layout>
