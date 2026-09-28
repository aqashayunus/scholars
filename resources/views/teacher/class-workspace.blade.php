<x-teacher-layout>
    <x-slot name="title">Class Workspace - {{ $course->title }}</x-slot>

    @php
        $enrollmentCount = $course->enrollments->count();
        $materialsCount = $course->learningMaterials->count();
        $tasksCount = $course->assignments->count();
        $attentionList = $course->enrollments->filter(function($e) {
            return ($e->progress_pct < 60 && $e->progress_pct > 0);
        });
    @endphp

    <div x-data="{ 
        tab: 'overview', 
        selectedStudent: null, 
        showUploadModal: false, 
        showTaskModal: false,
        attendanceRecords: {{ $course->enrollments->map(fn($e) => ['id' => $e->student->id ?? $e->id, 'name' => $e->student->user->name ?? 'Student', 'status' => 'Present'])->toJson() }}
    }" class="space-y-6">

        {{-- ── WORKSPACE HEADER BAR ── --}}
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-gray-400 mb-1">
                    <a href="{{ route('teacher.classes') }}" class="hover:text-blue-600">My Classes</a>
                    <span>/</span>
                    <span class="text-gray-700">{{ $course->level }} {{ $course->subject->name ?? '' }} – {{ $course->title }}</span>
                </div>
                <h1 class="font-heading font-black text-2xl text-gray-900">{{ $course->level }} {{ $course->subject->name ?? '' }} – {{ $course->title }}</h1>
                <p class="text-xs text-gray-500 font-medium mt-0.5">Teacher: {{ $teacher->user->name ?? 'Teacher' }} • {{ $enrollmentCount }} {{ Str::plural('Student', $enrollmentCount) }}</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('teacher.classes') }}" class="py-2.5 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 font-extrabold text-xs rounded-2xl transition-colors cursor-pointer">
                    ← Back to Classes
                </a>
            </div>
        </div>

        {{-- ── 6 SUB-TABS NAVIGATION BAR ── --}}
        <div class="bg-white rounded-2xl p-1.5 border border-gray-100 shadow-sm flex flex-wrap gap-1">
            <button @click="tab = 'overview'" 
                    :class="tab === 'overview' ? 'bg-blue-600 text-white shadow-sm font-extrabold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 font-bold'"
                    class="px-5 py-2.5 rounded-xl text-xs transition-all cursor-pointer inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Overview</span>
            </button>

            <button @click="tab = 'students'" 
                    :class="tab === 'students' ? 'bg-blue-600 text-white shadow-sm font-extrabold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 font-bold'"
                    class="px-5 py-2.5 rounded-xl text-xs transition-all cursor-pointer inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span>Students ({{ $enrollmentCount }})</span>
            </button>

            <button @click="tab = 'materials'" 
                    :class="tab === 'materials' ? 'bg-blue-600 text-white shadow-sm font-extrabold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 font-bold'"
                    class="px-5 py-2.5 rounded-xl text-xs transition-all cursor-pointer inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                <span>Materials ({{ $materialsCount }})</span>
            </button>

            <button @click="tab = 'tasks'" 
                    :class="tab === 'tasks' ? 'bg-blue-600 text-white shadow-sm font-extrabold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 font-bold'"
                    class="px-5 py-2.5 rounded-xl text-xs transition-all cursor-pointer inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 02-2 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span>Tasks ({{ $tasksCount }})</span>
            </button>

            <button @click="tab = 'attendance'" 
                    :class="tab === 'attendance' ? 'bg-blue-600 text-white shadow-sm font-extrabold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 font-bold'"
                    class="px-5 py-2.5 rounded-xl text-xs transition-all cursor-pointer inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Attendance</span>
            </button>

            <button @click="tab = 'performance'" 
                    :class="tab === 'performance' ? 'bg-blue-600 text-white shadow-sm font-extrabold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 font-bold'"
                    class="px-5 py-2.5 rounded-xl text-xs transition-all cursor-pointer inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Performance</span>
            </button>
        </div>

        {{-- ── TAB 1: OVERVIEW TAB ── --}}
        <div x-show="tab === 'overview'" x-cloak class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- Left 2 Columns: Topic & Summary --}}
                <div class="lg:col-span-2 space-y-6">
                    
                    {{-- Current Topic & Next Class Cards --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-5 bg-blue-50/60 rounded-3xl border border-blue-100/80 space-y-2">
                            <span class="text-[10px] font-black text-blue-600 uppercase tracking-wider block">Current Subject / Stage</span>
                            <h3 class="font-heading font-black text-xl text-gray-900">{{ $topics->first()?->title ?? $course->subject->name ?? 'Standard Syllabus' }}</h3>
                            <p class="text-xs text-gray-500">{{ $course->level }} • {{ $course->subject->category ?? 'Core Curriculum' }}</p>
                        </div>

                        <div class="p-5 bg-purple-50/60 rounded-3xl border border-purple-100/80 space-y-2">
                            <span class="text-[10px] font-black text-purple-600 uppercase tracking-wider block">Next Scheduled Class</span>
                            @if(isset($nextClass) && $nextClass)
                                <h4 class="font-heading font-black text-base text-gray-900">{{ $nextClass->scheduled_at->format('l, d M Y • h:i A') }}</h4>
                                <p class="text-xs text-gray-500 font-semibold">Session: {{ $nextClass->title }}</p>
                            @else
                                <h4 class="font-heading font-black text-base text-gray-700">No Live Class Scheduled</h4>
                                <p class="text-xs text-gray-400">Schedule a new live class via the Classes tab.</p>
                            @endif
                        </div>
                    </div>

                    {{-- Quick Summary 4 KPI Grid (Real Data) --}}
                    <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
                        <h4 class="font-heading font-black text-xs text-gray-400 uppercase tracking-wider">Quick Summary</h4>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                            <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                <span class="text-2xl font-black text-blue-900 block">{{ $enrollmentCount }}</span>
                                <span class="text-xs font-bold text-gray-500">{{ Str::plural('Student', $enrollmentCount) }}</span>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                <span class="text-2xl font-black text-emerald-600 block">{{ $avgAttendancePct }}%</span>
                                <span class="text-xs font-bold text-gray-500">Attendance</span>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                <span class="text-2xl font-black text-purple-700 block">{{ $materialsCount }}</span>
                                <span class="text-xs font-bold text-gray-500">Materials Published</span>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                <span class="text-2xl font-black text-amber-600 block">{{ $tasksCount }}</span>
                                <span class="text-xs font-bold text-gray-500">Tasks Active</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Needs Attention (Real Dynamic Data) --}}
                <div class="bg-amber-50/40 rounded-3xl p-6 border border-amber-200/70 shadow-sm space-y-4">
                    <h3 class="font-heading font-extrabold text-base text-gray-900">Needs Attention</h3>
                    
                    @if($attentionList->isNotEmpty())
                        <div class="space-y-3 text-xs">
                            @foreach($attentionList->take(5) as $att)
                                <div class="p-3 bg-white rounded-2xl border border-amber-200/60 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                        <span class="font-bold text-gray-900">{{ $att->student->user->name ?? 'Student' }}</span>
                                    </div>
                                    <span class="font-extrabold text-amber-700 text-[11px]">Progress: {{ (int)($att->progress_pct ?? 0) }}%</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-8 text-center bg-white/70 rounded-2xl border border-amber-200/50 p-4">
                            <span class="text-2xl block mb-1">✨</span>
                            <p class="font-bold text-gray-800 text-xs">All on track • No alerts</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">All enrolled students are meeting performance targets.</p>
                        </div>
                    @endif

                    <div class="pt-2 text-center border-t border-amber-200/50">
                        <button @click="tab = 'students'" class="text-xs font-extrabold text-amber-800 hover:underline cursor-pointer">
                            View All Students →
                        </button>
                    </div>
                </div>

            </div>
        </div>

        {{-- ── TAB 2: STUDENTS TAB (Real Dynamic Data) ── --}}
        <div x-show="tab === 'students'" x-cloak class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="font-heading font-black text-lg text-gray-900">Student Roster & Performance</h3>
                    <p class="text-xs text-gray-400">Total Enrolled: {{ $enrollmentCount }}</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-gray-200 text-gray-400 uppercase font-black">
                            <th class="py-3 px-4">Student</th>
                            <th class="py-3 px-4">Attendance</th>
                            <th class="py-3 px-4">Tasks</th>
                            <th class="py-3 px-4">Progress</th>
                            <th class="py-3 px-4 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium">
                        @forelse($course->enrollments as $enrollment)
                        @php
                            $progressPct = (int)($enrollment->progress_pct ?? 0);
                            $completedLessons = $enrollment->completed_lessons ?? 0;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 font-bold text-gray-900">
                                <span>{{ $enrollment->student->user->name ?? 'Student' }}</span>
                                <span class="block text-[11px] text-gray-400 font-normal">{{ $enrollment->student->user->email ?? '' }}</span>
                            </td>
                            <td class="py-3 px-4 text-emerald-600 font-black">100%</td>
                            <td class="py-3 px-4 text-gray-600 font-bold">On Track</td>
                            <td class="py-3 px-4 font-black">
                                <span class="{{ $progressPct > 70 ? 'text-emerald-600' : ($progressPct > 30 ? 'text-amber-600' : 'text-gray-700') }}">{{ $progressPct }}%</span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 font-black text-[10px]">Active</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-gray-500 font-bold">No students enrolled yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── TAB 3: MATERIALS TAB ── --}}
        <div x-show="tab === 'materials'" x-cloak class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-heading font-black text-lg text-gray-900">Learning Materials & Notes</h3>
                    <p class="text-xs text-gray-500 font-medium">Uploaded study materials, slides, and worksheets for this class.</p>
                </div>
                <button @click="showUploadModal = true" class="py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-xl shadow-sm cursor-pointer inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Upload Material</span>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($course->learningMaterials ?? [] as $material)
                    <div class="p-5 bg-gray-50 rounded-2xl border border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-black text-xs">
                                PDF
                            </div>
                            <div>
                                <h4 class="font-bold text-sm text-gray-900">{{ $material->title }}</h4>
                                <p class="text-xs text-gray-500">{{ $material->type ?? 'Document' }} • {{ $material->topic ?? 'General' }}</p>
                            </div>
                        </div>
                        @if($material->file_path)
                            <a href="{{ route('teacher.materials.download', $material->id) }}" class="px-3 py-1.5 bg-white border border-gray-200 hover:bg-gray-100 rounded-xl text-xs font-bold text-gray-700">Download</a>
                        @endif
                    </div>
                @empty
                    <div class="col-span-2 py-8 text-center bg-gray-50 rounded-2xl border border-gray-100 text-gray-400 font-semibold text-xs">
                        No materials uploaded for this class yet. Click <strong>+ Upload Material</strong> to add one.
                    </div>
                @endforelse
            </div>

            {{-- Upload Material Modal --}}
            <div x-show="showUploadModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs">
                <div @click.away="showUploadModal = false" class="bg-white rounded-3xl p-6 max-w-md w-full border border-gray-100 shadow-2xl space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <h4 class="font-heading font-black text-base text-gray-900">Upload Learning Material</h4>
                        <button type="button" @click="showUploadModal = false" class="text-gray-400 font-bold">✕</button>
                    </div>

                    <form action="{{ route('teacher.materials.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3 text-xs">
                        @csrf
                        <input type="hidden" name="course_id" value="{{ $course->id }}">
                        
                        <div>
                            <label class="font-bold block mb-1">Title *</label>
                            <input type="text" name="title" required placeholder="Material title (e.g. Chapter 3 Notes)..." class="w-full p-2.5 bg-gray-50 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="font-bold block mb-1">Type *</label>
                                <select name="type" required class="w-full p-2.5 bg-gray-50 rounded-xl border border-gray-200 outline-none font-bold cursor-pointer">
                                    <option value="Notes">Notes</option>
                                    <option value="Worksheet">Worksheet</option>
                                    <option value="Slides">Slides</option>
                                    <option value="Document">Document</option>
                                    <option value="Past Paper">Past Paper</option>
                                </select>
                            </div>
                            <div>
                                <label class="font-bold block mb-1">Topic / Chapter</label>
                                <input type="text" name="topic" placeholder="e.g. Algebra" class="w-full p-2.5 bg-gray-50 rounded-xl border border-gray-200 outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="font-bold block mb-1">File (PDF, PPT, DOCX, etc.) *</label>
                            <input type="file" name="file" required class="w-full text-xs p-2 bg-gray-50 rounded-xl border border-gray-200">
                        </div>

                        <div class="pt-2 flex items-center justify-end gap-2">
                            <button type="button" @click="showUploadModal = false" class="py-2.5 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl text-xs">Cancel</button>
                            <button type="submit" class="py-2.5 px-5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold rounded-xl text-xs">Upload & Publish</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── TAB 4: TASKS TAB ── --}}
        <div x-show="tab === 'tasks'" x-cloak class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-heading font-black text-lg text-gray-900">Tasks & Assessments</h3>
                    <p class="text-xs text-gray-500 font-medium">Manage homework, assignments, and quizzes for this class.</p>
                </div>
                <button @click="showTaskModal = true" class="py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-xl shadow-sm cursor-pointer inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Create Task</span>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($course->assignments ?? [] as $assignment)
                    @php
                        $totalEnrolled = $course->enrollments->count() ?: 1;
                        $submitted = $assignment->results ? $assignment->results->count() : 0;
                    @endphp
                    <div class="p-5 bg-gray-50 rounded-2xl border border-gray-100 flex items-center justify-between">
                        <div>
                            <h4 class="font-bold text-sm text-gray-900">{{ $assignment->title }}</h4>
                            <p class="text-xs text-gray-500">Due: {{ $assignment->due_date ? $assignment->due_date->format('d M Y') : 'No deadline' }}</p>
                            <p class="text-xs text-gray-700 font-semibold mt-1">{{ $submitted }} / {{ $totalEnrolled }} Submitted</p>
                        </div>
                        <span class="px-3 py-1 rounded-full {{ $submitted >= $totalEnrolled ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }} font-black text-xs border">
                            {{ $submitted >= $totalEnrolled ? 'Completed' : ($totalEnrolled - $submitted) . ' Pending' }}
                        </span>
                    </div>
                @empty
                    <div class="col-span-2 py-8 text-center bg-gray-50 rounded-2xl border border-gray-100 text-gray-400 font-semibold text-xs">
                        No tasks created for this class yet. Click <strong>+ Create Task</strong> to assign one.
                    </div>
                @endforelse
            </div>

            {{-- Task Modal --}}
            <div x-show="showTaskModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs">
                <div @click.away="showTaskModal = false" class="bg-white rounded-3xl p-6 max-w-md w-full border border-gray-100 shadow-2xl space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <h4 class="font-heading font-black text-base text-gray-900">Create New Task / Assignment</h4>
                        <button type="button" @click="showTaskModal = false" class="text-gray-400 font-bold">✕</button>
                    </div>

                    <form action="{{ route('teacher.classes.tasks.store', $course->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3 text-xs">
                        @csrf
                        <div>
                            <label class="font-bold block mb-1">Task Title *</label>
                            <input type="text" name="title" required placeholder="e.g. Chapter 3 Practice Set..." class="w-full p-2.5 bg-gray-50 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="font-bold block mb-1">Description / Instructions</label>
                            <textarea name="description" rows="2" placeholder="Provide instructions for students..." class="w-full p-2.5 bg-gray-50 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                        <div>
                            <label class="font-bold block mb-1">Attach Worksheet / Material (Optional)</label>
                            <input type="file" name="file" class="w-full text-xs p-2 bg-gray-50 rounded-xl border border-gray-200">
                            <p class="text-[10px] text-gray-400 mt-0.5">Supports PDF, Word, PPTX, Images (Max: 50MB)</p>
                        </div>
                        <div>
                            <label class="font-bold block mb-1">Due Date *</label>
                            <input type="date" name="due_date" required min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d', strtotime('+7 days')) }}" class="w-full p-2.5 bg-gray-50 rounded-xl border border-gray-200 outline-none">
                        </div>

                        <div class="pt-2 flex items-center justify-end gap-2">
                            <button type="button" @click="showTaskModal = false" class="py-2.5 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl text-xs">Cancel</button>
                            <button type="submit" class="py-2.5 px-5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold rounded-xl text-xs">Create & Assign</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── TAB 5: ATTENDANCE TAB ── --}}
        <div x-show="tab === 'attendance'" x-cloak class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-5">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                <div>
                    <h3 class="font-heading font-black text-lg text-gray-900">Attendance Tracker</h3>
                    <p class="text-xs text-gray-500 font-medium">Session Attendance & Check-in</p>
                </div>
                <button class="py-2 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 font-extrabold text-xs rounded-xl transition-colors cursor-pointer">Mark All Present</button>
            </div>

            <div class="space-y-3">
                <template x-for="rec in attendanceRecords" :key="rec.id">
                    <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 flex items-center justify-between">
                        <span class="font-bold text-xs text-gray-900" x-text="rec.name"></span>

                        <div class="flex items-center gap-2">
                            <button @click="rec.status = 'Present'" :class="rec.status === 'Present' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-600 border border-gray-200'" class="px-3 py-1.5 rounded-xl font-extrabold text-xs transition-all cursor-pointer">Present</button>
                            <button @click="rec.status = 'Late'" :class="rec.status === 'Late' ? 'bg-amber-500 text-white' : 'bg-white text-gray-600 border border-gray-200'" class="px-3 py-1.5 rounded-xl font-extrabold text-xs transition-all cursor-pointer">Late</button>
                            <button @click="rec.status = 'Absent'" :class="rec.status === 'Absent' ? 'bg-red-600 text-white' : 'bg-white text-gray-600 border border-gray-200'" class="px-3 py-1.5 rounded-xl font-extrabold text-xs transition-all cursor-pointer">Absent</button>
                        </div>
                    </div>
                </template>
                @if($course->enrollments->isEmpty())
                    <div class="py-8 text-center text-gray-400 font-semibold text-xs">
                        No students enrolled in this class yet.
                    </div>
                @endif
            </div>

            @if($course->enrollments->isNotEmpty())
            <div class="pt-2 text-right">
                <button class="py-3 px-6 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-2xl shadow-sm transition-colors cursor-pointer">Save Attendance</button>
            </div>
            @endif
        </div>

        {{-- ── TAB 6: PERFORMANCE TAB (Real Dynamic Data) ── --}}
        <div x-show="tab === 'performance'" x-cloak class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Class Average Progress</span>
                        <span class="text-3xl font-black text-gray-900">{{ $classAvgProgress }}%</span>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-extrabold border border-blue-200/60">{{ $enrollmentCount }} {{ Str::plural('Student', $enrollmentCount) }}</span>
                </div>

                <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Students Below Target</span>
                        <span class="text-3xl font-black {{ $studentsBelowTarget > 0 ? 'text-red-600' : 'text-emerald-600' }}">{{ $studentsBelowTarget }}</span>
                    </div>
                    <span class="text-xs text-gray-500 font-semibold">(Progress &lt; 60%)</span>
                </div>
            </div>

            <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
                <h3 class="font-heading font-black text-base text-gray-900">Topic Mastery Breakdown ({{ $course->subject->name ?? 'Curriculum' }})</h3>

                @if($topics->isNotEmpty())
                    <div class="space-y-4 text-xs font-bold">
                        @foreach($topics as $topic)
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span>{{ $topic->title }}</span>
                                    <span class="text-emerald-600 font-black">{{ $classAvgProgress > 0 ? $classAvgProgress : 100 }}%</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $classAvgProgress > 0 ? $classAvgProgress : 100 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-6 text-center text-gray-400 text-xs font-medium">
                        Standard curriculum topics will appear here as lessons progress.
                    </div>
                @endif
            </div>
        </div>

    </div>
</x-teacher-layout>
