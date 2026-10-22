<x-teacher-layout>
    <x-slot name="title">Tasks & Assessments - Teacher Portal</x-slot>

    <script id="tasks-data" type="application/json">
        {!! json_encode($mappedTasks, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
    </script>

    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('tasksManager', () => ({
            categoryTab: 'all',
            createTab: 'assignment',
            searchQuery: '',
            selectedClass: 'all',
            selectedSubject: 'all',
            selectedStatus: 'all',
            showCreateModal: false,
            showReviewModal: false,
            showEditModal: false,
            showGradingModal: false,
            selectedTask: null,
            editingTask: null,
            selectedSubmission: null,
            quizQuestions: [
                { question_text: '', option_a: '', option_b: '', option_c: '', option_d: '', correct_option: 'A', explanation: '' }
            ],
            toastMessage: '',
            showToastNotification: false,
            isDeletingId: null,
            createModalError: '',
            isSubmitting: false,
            quizDraftRestored: false,

            tasks: JSON.parse(document.getElementById('tasks-data')?.textContent || '[]'),

            init() {
                this.restoreQuizDraft();
            },

            saveQuizDraft() {
                try {
                    const draft = {
                        questions: this.quizQuestions,
                        updatedAt: new Date().toISOString()
                    };
                    localStorage.setItem('teacher_quiz_draft_data', JSON.stringify(draft));
                } catch (e) {
                    console.warn('Failed to save quiz draft', e);
                }
            },

            restoreQuizDraft() {
                try {
                    const raw = localStorage.getItem('teacher_quiz_draft_data');
                    if (!raw) return;
                    const draft = JSON.parse(raw);
                    if (draft && Array.isArray(draft.questions) && draft.questions.length > 0) {
                        const hasContent = draft.questions.some(q => (q.question_text || '').trim() !== '');
                        if (hasContent) {
                            this.quizQuestions = draft.questions;
                            this.quizDraftRestored = true;
                        }
                    }
                } catch (e) {
                    console.warn('Failed to restore quiz draft', e);
                }
            },

            clearQuizDraft() {
                localStorage.removeItem('teacher_quiz_draft_data');
                this.quizQuestions = [
                    { question_text: '', option_a: '', option_b: '', option_c: '', option_d: '', correct_option: 'A', explanation: '' }
                ];
                this.quizDraftRestored = false;
            },

            async submitTaskForm(event, isQuiz = false) {
                event.preventDefault();
                const form = event.target;
                this.isSubmitting = true;
                this.createModalError = '';

                try {
                    const formData = new FormData(form);
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    });

                    const data = await response.json().catch(() => ({}));

                    if (response.ok) {
                        if (isQuiz) {
                            this.clearQuizDraft();
                        }
                        this.showToast(data.message || 'Item created and assigned successfully!');
                        setTimeout(() => {
                            window.location.reload();
                        }, 600);
                    } else {
                        let errorMsg = data.message || 'Validation error occurred.';
                        if (data.errors) {
                            const errorList = Object.values(data.errors).flat();
                            if (errorList.length > 0) {
                                errorMsg = errorList.join(' • ');
                            }
                        }
                        this.createModalError = errorMsg;
                    }
                } catch (err) {
                    console.error(err);
                    this.createModalError = 'Network or server error while uploading. Your entered progress and questions remain safely intact on screen.';
                } finally {
                    this.isSubmitting = false;
                }
            },

            addQuizQuestion() {
                this.quizQuestions.push({
                    question_text: '',
                    option_a: '',
                    option_b: '',
                    option_c: '',
                    option_d: '',
                    correct_option: 'A',
                    explanation: ''
                });
                this.saveQuizDraft();
            },
            removeQuizQuestion(index) {
                if (this.quizQuestions.length > 1) {
                    this.quizQuestions.splice(index, 1);
                    this.saveQuizDraft();
                }
            },
            openGradingModal(st) {
                this.selectedSubmission = {
                    name: st.name || 'Student',
                    email: st.email || '',
                    student_id: st.student_id,
                    assignment_id: st.assignment_id || this.selectedTask?.id,
                    submittedDate: st.submittedDate || '–',
                    notes: st.notes || '',
                    fileName: st.fileName || '',
                    fileUrl: st.fileUrl || '',
                    interactiveReviewUrl: st.interactiveReviewUrl || '',
                    rawMarks: st.rawMarks !== null && st.rawMarks !== undefined ? st.rawMarks : '',
                    rawStatus: st.rawStatus || (st.status === 'Reviewed' ? 'graded' : 'graded'),
                    teacherFeedback: st.teacherFeedback || '',
                    gradedFileName: st.gradedFileName || '',
                    gradedFileUrl: st.gradedFileUrl || '',
                    hasSubmission: st.hasSubmission || false
                };
                this.showGradingModal = true;
            },
            showToast(msg) {
                this.toastMessage = msg;
                this.showToastNotification = true;
                setTimeout(() => {
                    this.showToastNotification = false;
                }, 3500);
            },
            async deleteTask(tk) {
                if (!confirm(`Are you sure you want to delete "${tk.name || tk.title}"? All student tracking and records will be permanently removed.`)) {
                    return;
                }
                this.isDeletingId = tk.id;
                try {
                    const response = await fetch(tk.delete_url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            _method: 'DELETE'
                        })
                    });

                    const data = await response.json().catch(() => ({}));

                    if (response.ok) {
                        this.tasks = this.tasks.filter(t => t.id !== tk.id);

                        if (this.selectedTask && this.selectedTask.id === tk.id) {
                            this.showReviewModal = false;
                            this.selectedTask = null;
                        }
                        if (this.editingTask && this.editingTask.id === tk.id) {
                            this.showEditModal = false;
                            this.editingTask = null;
                        }

                        this.showToast(data.message || `${tk.type || 'Item'} '${tk.name || tk.title}' deleted successfully.`);
                    } else {
                        alert(data.message || 'Failed to delete item. Please try again.');
                    }
                } catch (err) {
                    console.error(err);
                    alert('Network error while attempting to delete item.');
                } finally {
                    this.isDeletingId = null;
                }
            },
            get filteredTasks() {
                return this.tasks.filter(t => {
                    const matchesTab = this.categoryTab === 'all' || 
                                       (this.categoryTab === 'assignments' && t.type === 'Assignment') ||
                                       (this.categoryTab === 'quizzes' && t.type === 'Quiz') ||
                                       (this.categoryTab === 'assessments' && t.type === 'Assessment') ||
                                       (this.categoryTab === 'exams' && t.type === 'Exam');

                    const matchesQuery = !this.searchQuery || 
                                         (t.name && t.name.toLowerCase().includes(this.searchQuery.toLowerCase())) ||
                                         (t.topic && t.topic.toLowerCase().includes(this.searchQuery.toLowerCase()));
                    const matchesClass = !this.selectedClass || this.selectedClass === 'all' || t.class === this.selectedClass;
                    const matchesSubject = !this.selectedSubject || this.selectedSubject === 'all' || t.subject === this.selectedSubject;
                    const matchesStatus = !this.selectedStatus || this.selectedStatus === 'all' || t.status === this.selectedStatus;
                    return matchesTab && matchesQuery && matchesClass && matchesSubject && matchesStatus;
                });
            }
        }));
    });
    </script>

    <div x-data="tasksManager()" class="space-y-6">

        {{-- ── NOTIFICATIONS & FLASH MESSAGES ── --}}
        {{-- Dynamic AJAX Toast Notification --}}
        <div x-show="showToastNotification" 
             x-transition:enter="transition ease-out duration-300 transform" 
             x-transition:enter-start="-translate-y-4 opacity-0" 
             x-transition:enter-end="translate-y-0 opacity-100" 
             x-transition:leave="transition ease-in duration-200 transform" 
             x-transition:leave-start="translate-y-0 opacity-100" 
             x-transition:leave-end="-translate-y-4 opacity-0" 
             x-cloak 
             class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl flex items-center justify-between text-xs font-bold shadow-sm">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span x-text="toastMessage"></span>
            </div>
            <button type="button" @click="showToastNotification = false" class="text-emerald-500 hover:text-emerald-700 font-black cursor-pointer ml-4">✕</button>
        </div>

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
                <p class="font-black">Please fix the following errors:</p>
                <ul class="list-disc list-inside font-medium space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ── HEADER SECTION ── --}}
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="font-heading font-black text-2xl text-gray-900">Tasks & Assessments</h1>
                <p class="text-xs text-gray-500 font-medium mt-0.5">Create, schedule, assess, and monitor assignments, quizzes, diagnostic assessments, and exams.</p>
            </div>

            <button @click="showCreateModal = true" class="py-2.5 px-5 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl text-xs font-extrabold shadow-sm transition-all cursor-pointer shrink-0 inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>Create New Task</span>
            </button>
        </div>

        {{-- ── 1. TOP 5 KPI SUMMARY CARDS ── --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            
            {{-- Active Items --}}
            <div class="bg-white rounded-3xl p-4 border border-gray-100 shadow-sm flex items-center gap-3 hover:shadow-md transition-all">
                <div class="w-10 h-10 rounded-2xl bg-slate-900 text-white flex items-center justify-center shrink-0 shadow-md font-black text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div>
                    <p class="text-xl font-black text-gray-900" x-text="tasks.filter(t => t.status === 'Active').length"></p>
                    <p class="text-[11px] font-bold text-gray-400">Active Tasks</p>
                    <p class="text-[10px] text-gray-500 font-medium">Currently running</p>
                </div>
            </div>

            {{-- Pending Review --}}
            <div class="bg-white rounded-3xl p-4 border border-gray-100 shadow-sm flex items-center gap-3 hover:shadow-md transition-all">
                <div class="w-10 h-10 rounded-2xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-blue-500/20 font-black text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </div>
                <div>
                    <p class="text-xl font-black text-blue-600" x-text="tasks.reduce((sum, t) => sum + parseInt(t.submissions.split(' / ')[0] || 0), 0)"></p>
                    <p class="text-[11px] font-bold text-gray-400">Submissions</p>
                    <p class="text-[10px] text-gray-500 font-medium">To mark / reviewed</p>
                </div>
            </div>

            {{-- Class Average --}}
            <div class="bg-white rounded-3xl p-4 border border-gray-100 shadow-sm flex items-center gap-3 hover:shadow-md transition-all">
                <div class="w-10 h-10 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-emerald-500/20 font-black text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <p class="text-xl font-black text-emerald-600" x-text="(tasks.reduce((sum, t) => sum + t.pct, 0) / (tasks.length || 1)).toFixed(0) + '%'"></p>
                    <p class="text-[11px] font-bold text-gray-400">Class Average</p>
                    <p class="text-[10px] text-gray-500 font-medium">Across all classes</p>
                </div>
            </div>

            {{-- Overdue Tasks --}}
            <div class="bg-white rounded-3xl p-4 border border-gray-100 shadow-sm flex items-center gap-3 hover:shadow-md transition-all">
                <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-amber-500/20 font-black text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <p class="text-xl font-black text-gray-900" x-text="tasks.filter(t => t.status === 'Overdue').length"></p>
                    <p class="text-[11px] font-bold text-gray-400">Overdue Tasks</p>
                    <p class="text-[10px] text-gray-500 font-medium">Require attention</p>
                </div>
            </div>

            {{-- Total Academic Items --}}
            <div class="bg-white rounded-3xl p-4 border border-gray-100 shadow-sm flex items-center gap-3 hover:shadow-md transition-all">
                <div class="w-10 h-10 rounded-2xl bg-slate-700 text-white flex items-center justify-center shrink-0 shadow-md font-black text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <div>
                    <p class="text-xl font-black text-gray-900" x-text="tasks.length"></p>
                    <p class="text-[11px] font-bold text-gray-400">Total Items</p>
                    <p class="text-[10px] text-gray-500 font-medium">In your curriculum</p>
                </div>
            </div>
        </div>

        {{-- ── 2. CATEGORY TABS & FILTER BAR ── --}}
        <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm space-y-4">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                
                {{-- Category Sub-Tabs --}}
                <div class="flex flex-wrap gap-4 text-xs font-extrabold">
                    <button @click="categoryTab = 'all'" :class="categoryTab === 'all' ? 'text-blue-600 border-b-2 border-blue-600 pb-2' : 'text-gray-500 hover:text-gray-900 pb-2'" class="transition-colors cursor-pointer">
                        All Items (<span x-text="tasks.length"></span>)
                    </button>
                    <button @click="categoryTab = 'assignments'" :class="categoryTab === 'assignments' ? 'text-blue-600 border-b-2 border-blue-600 pb-2' : 'text-gray-500 hover:text-gray-900 pb-2'" class="transition-colors cursor-pointer">
                        Assignments (<span x-text="tasks.filter(t => t.type === 'Assignment').length"></span>)
                    </button>
                    <button @click="categoryTab = 'quizzes'" :class="categoryTab === 'quizzes' ? 'text-blue-600 border-b-2 border-blue-600 pb-2' : 'text-gray-500 hover:text-gray-900 pb-2'" class="transition-colors cursor-pointer">
                        Quizzes (<span x-text="tasks.filter(t => t.type === 'Quiz').length"></span>)
                    </button>
                    <button @click="categoryTab = 'assessments'" :class="categoryTab === 'assessments' ? 'text-blue-600 border-b-2 border-blue-600 pb-2' : 'text-gray-500 hover:text-gray-900 pb-2'" class="transition-colors cursor-pointer">
                        Assessments (<span x-text="tasks.filter(t => t.type === 'Assessment').length"></span>)
                    </button>
                    <button @click="categoryTab = 'exams'" :class="categoryTab === 'exams' ? 'text-blue-600 border-b-2 border-blue-600 pb-2' : 'text-gray-500 hover:text-gray-900 pb-2'" class="transition-colors cursor-pointer">
                        Exams (<span x-text="tasks.filter(t => t.type === 'Exam').length"></span>)
                    </button>
                </div>

                {{-- Search & Dropdown Filters --}}
                <div class="flex flex-wrap items-center gap-2">
                    <input type="text" x-model="searchQuery" placeholder="Search tasks or topics..." class="bg-gray-50 border border-gray-200/80 rounded-2xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                    
                    <select x-model="selectedClass" class="bg-gray-50 border border-gray-200/80 rounded-2xl pl-3.5 pr-8 py-2 text-xs font-bold text-gray-700 outline-none cursor-pointer min-w-[130px]">
                        <option value="all">All Classes</option>
                        @foreach($courses->pluck('level')->unique() as $lvl)
                            <option value="{{ $lvl }}">{{ $lvl }}</option>
                        @endforeach
                    </select>

                    <select x-model="selectedSubject" class="bg-gray-50 border border-gray-200/80 rounded-2xl pl-3.5 pr-8 py-2 text-xs font-bold text-gray-700 outline-none cursor-pointer min-w-[140px]">
                        <option value="all">All Subjects</option>
                        @foreach($courses->map(fn($c) => $c->subject->display_name ?? $c->subject->name ?? null)->filter()->unique() as $sName)
                            <option value="{{ $sName }}">{{ $sName }}</option>
                        @endforeach
                    </select>

                    <select x-model="selectedStatus" class="bg-gray-50 border border-gray-200/80 rounded-2xl pl-3.5 pr-8 py-2 text-xs font-bold text-gray-700 outline-none cursor-pointer min-w-[130px]">
                        <option value="all">All Status</option>
                        <option value="Active">Active</option>
                        <option value="Completed">Completed</option>
                        <option value="Overdue">Overdue</option>
                    </select>

                    <button @click="searchQuery = ''; selectedClass = 'all'; selectedSubject = 'all'; selectedStatus = 'all'" class="py-2 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-extrabold text-xs rounded-2xl transition-colors cursor-pointer shrink-0">
                        Reset
                    </button>
                </div>

            </div>
        </div>

        {{-- ── 3. MASTER TASKS & ASSESSMENTS TABLE ── --}}
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-gray-200 text-gray-400 uppercase font-black">
                            <th class="py-3 px-4">Title & Description</th>
                            <th class="py-3 px-4">Type</th>
                            <th class="py-3 px-4">Subject & Class</th>
                            <th class="py-3 px-4">Due Date / Schedule</th>
                            <th class="py-3 px-4">Submissions</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium">
                        <template x-for="tk in filteredTasks" :key="tk.id">
                            <tr @click="selectedTask = tk; showReviewModal = true" class="hover:bg-gray-50/80 transition-colors cursor-pointer">
                                
                                {{-- Title & Description --}}
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-gray-900 leading-snug" x-text="tk.name"></div>
                                    <div class="text-[11px] text-gray-400 font-normal line-clamp-1 mt-0.5" x-text="tk.topic ? (tk.topic + ' • ' + tk.desc) : tk.desc"></div>
                                </td>

                                {{-- Type (Clean Text Typography - No Color Badge) --}}
                                <td class="py-3.5 px-4 font-semibold text-gray-800 text-xs" x-text="tk.type"></td>

                                {{-- Subject & Class --}}
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-gray-900 text-xs" x-text="tk.subject"></div>
                                    <div class="text-[10px] text-gray-400 font-normal mt-0.5" x-text="tk.class"></div>
                                </td>

                                {{-- Due Date / Schedule --}}
                                <td class="py-3.5 px-4 font-medium text-gray-700 text-xs">
                                    <span x-text="tk.dueDate"></span>
                                    <template x-if="tk.duration_minutes">
                                        <div class="text-[10px] text-gray-400 font-normal mt-0.5" x-text="`${tk.duration_minutes} mins`"></div>
                                    </template>
                                </td>

                                {{-- Submissions --}}
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-gray-900" x-text="tk.submissions + ' (' + tk.pct + '%)'"></div>
                                    <div class="w-20 bg-gray-100 rounded-full h-1 mt-1">
                                        <div class="h-1 rounded-full bg-slate-700" :style="'width: ' + tk.pct + '%'"></div>
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td class="py-3.5 px-4 font-semibold text-gray-700 text-xs" x-text="tk.status"></td>

                                {{-- Actions --}}
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" @click.stop="selectedTask = tk; showReviewModal = true" class="w-8 h-8 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 inline-flex items-center justify-center transition-all cursor-pointer" title="Review Submissions">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                        <template x-if="tk.type !== 'Quiz'">
                                            <button type="button" @click.stop="editingTask = JSON.parse(JSON.stringify(tk)); showEditModal = true;" class="w-8 h-8 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 inline-flex items-center justify-center transition-all cursor-pointer" title="Edit Item">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                        </template>
                                        <template x-if="tk.delete_url">
                                            <button type="button" 
                                                    @click.stop="deleteTask(tk)" 
                                                    :disabled="isDeletingId === tk.id"
                                                    class="w-8 h-8 rounded-xl bg-gray-100 hover:bg-red-50 text-gray-400 hover:text-red-600 inline-flex items-center justify-center transition-all cursor-pointer disabled:opacity-50" 
                                                    title="Delete Item">
                                                <template x-if="isDeletingId === tk.id">
                                                    <svg class="w-4 h-4 animate-spin text-red-600" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                </template>
                                                <template x-if="isDeletingId !== tk.id">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </template>
                                            </button>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="filteredTasks.length === 0">
                            <td colspan="7" class="py-8 text-center text-gray-400 font-semibold">
                                No tasks found matching criteria. Click <strong>+ Create New Task</strong> to create one.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-between pt-3 border-t border-gray-100 text-xs">
                <span class="text-gray-400 font-semibold" x-text="`Showing ${filteredTasks.length} item(s)`"></span>
            </div>
        </div>

        {{-- ── 4. CREATE NEW TASK / ASSESSMENT MODAL ── --}}
        <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="showCreateModal = false" class="bg-white rounded-3xl shadow-2xl border border-gray-100 w-full max-w-2xl p-6 sm:p-7 space-y-5 my-auto max-h-[90vh] overflow-y-auto transform transition-all">
                
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div>
                        <h3 class="font-heading font-black text-lg text-gray-900">Create New Academic Item</h3>
                        <p class="text-xs text-gray-500 font-medium">Select type to configure assignments, interactive quizzes, assessments, or exams.</p>
                    </div>
                    <button type="button" @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-xl cursor-pointer">✕</button>
                </div>

                {{-- Type Switcher Sub-Tabs --}}
                <div class="flex gap-2 p-1 bg-gray-100 rounded-2xl text-xs font-extrabold">
                    <button type="button" @click="createTab = 'assignment'" :class="createTab === 'assignment' ? 'bg-white text-gray-900 shadow-2xs' : 'text-gray-500'" class="flex-1 py-2 rounded-xl transition-all cursor-pointer text-center">Assignment</button>
                    <button type="button" @click="createTab = 'quiz'" :class="createTab === 'quiz' ? 'bg-white text-gray-900 shadow-2xs' : 'text-gray-500'" class="flex-1 py-2 rounded-xl transition-all cursor-pointer text-center">Quiz</button>
                    <button type="button" @click="createTab = 'assessment'" :class="createTab === 'assessment' ? 'bg-white text-gray-900 shadow-2xs' : 'text-gray-500'" class="flex-1 py-2 rounded-xl transition-all cursor-pointer text-center">Assessment</button>
                    <button type="button" @click="createTab = 'exam'" :class="createTab === 'exam' ? 'bg-white text-gray-900 shadow-2xs' : 'text-gray-500'" class="flex-1 py-2 rounded-xl transition-all cursor-pointer text-center">Exam</button>
                </div>

                {{-- Form Error Banner --}}
                <div x-show="createModalError" x-cloak class="p-3 bg-red-50 border border-red-200 text-red-800 rounded-xl text-xs font-semibold flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span x-text="createModalError"></span>
                    </div>
                    <button type="button" @click="createModalError = ''" class="text-red-500 hover:text-red-700 font-bold">✕</button>
                </div>

                {{-- Quiz Draft Restored Banner --}}
                <div x-show="createTab === 'quiz' && quizDraftRestored" x-cloak class="p-2.5 bg-blue-50 border border-blue-200 text-blue-800 rounded-xl text-xs font-semibold flex items-center justify-between">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Restored unsaved quiz questions from your draft.</span>
                    </div>
                    <button type="button" @click="clearQuizDraft()" class="text-blue-600 hover:text-blue-800 underline font-bold cursor-pointer text-[11px]">Clear draft</button>
                </div>

                {{-- ── FORM 1: ASSIGNMENT ── --}}
                <form x-show="createTab === 'assignment'" @submit="submitTaskForm($event, false)" action="{{ route('teacher.tasks.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="type" value="Assignment">

                    <div class="space-y-3 text-xs">
                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Class & Subject *</label>
                            <select name="course_id" required class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-3.5 pr-9 py-2.5 text-xs font-bold outline-none cursor-pointer">
                                <option value="">Select class</option>
                                @foreach($courses as $c)
                                    <option value="{{ $c->id }}">{{ $c->title }} ({{ $c->subject->display_name ?? ($c->subject->name ?? 'Subject') }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Assignment Title *</label>
                            <input type="text" name="title" required placeholder="e.g. Chapter 3 Practice Problem Set" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="font-extrabold text-gray-900 block mb-1">Topic / Chapter Focus</label>
                                <input type="text" name="topic" placeholder="e.g. Quadratic Equations" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none">
                            </div>
                            <div>
                                <label class="font-extrabold text-gray-900 block mb-1">Total Marks *</label>
                                <input type="number" name="total_marks" value="100" min="1" max="1000" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs font-bold outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Instructions & Guidelines</label>
                            <textarea name="description" rows="3" placeholder="Provide submission instructions for students..." class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-xs outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Attach Worksheet / Material (Optional)</label>
                            <input type="file" name="file" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2 text-xs outline-none file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="text-[10px] text-gray-400 mt-0.5">Supports PDF, Word, Slides, Images, ZIP (Max: 50MB)</p>
                        </div>
                        <div class="p-3 bg-blue-50/70 border border-blue-200/80 rounded-xl flex items-center justify-between">
                            <div>
                                <label class="font-extrabold text-blue-950 block text-xs">Enable Interactive PDF Answering</label>
                                <p class="text-[10px] text-blue-700 font-medium">Students write & draw answers directly on this PDF using Apple Pencil or Touch (PDF only)</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_interactive_pdf" value="1" checked class="sr-only peer">
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="font-extrabold text-gray-900 block mb-1">Due Date *</label>
                                <input type="date" name="due_date" required min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d', strtotime('+7 days')) }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs outline-none">
                            </div>
                            <div>
                                <label class="font-extrabold text-gray-900 block mb-1">Due Time</label>
                                <input type="time" name="due_time" value="23:59" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs outline-none">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 text-xs font-extrabold">
                        <button type="button" @click="showCreateModal = false" class="py-2.5 px-5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl transition-colors cursor-pointer">Cancel</button>
                        <button type="submit" :disabled="isSubmitting" class="py-2.5 px-6 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors shadow-sm cursor-pointer hover:shadow-md disabled:opacity-50 inline-flex items-center gap-1.5">
                            <span x-text="isSubmitting ? 'Publishing...' : 'Publish Assignment'"></span>
                        </button>
                    </div>
                </form>

                {{-- ── FORM 2: QUIZ (WITH INTERACTIVE MCQ QUESTION BUILDER) ── --}}
                <form x-show="createTab === 'quiz'" @submit="submitTaskForm($event, true)" action="{{ route('teacher.quizzes.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div class="space-y-3 text-xs">
                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Class & Subject *</label>
                            <select name="course_id" required class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-3.5 pr-9 py-2.5 text-xs font-bold outline-none cursor-pointer">
                                <option value="">Select class</option>
                                @foreach($courses as $c)
                                    <option value="{{ $c->id }}">{{ $c->title }} ({{ $c->subject->display_name ?? ($c->subject->name ?? 'Subject') }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Quiz Title *</label>
                            <input type="text" name="title" required placeholder="e.g. Checkpoint Quiz: Form 4 Trigonometry" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Description / Practice Goal</label>
                            <textarea name="description" rows="2" placeholder="Brief overview of quiz content..." class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-xs outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="font-extrabold text-gray-900 block mb-1">Time Limit (Minutes)</label>
                                <input type="number" name="time_limit_minutes" value="15" min="1" max="180" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2 text-xs font-bold outline-none">
                            </div>
                            <div>
                                <label class="font-extrabold text-gray-900 block mb-1">Passing Mark (%)</label>
                                <input type="number" name="passing_marks" value="60" min="1" max="100" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2 text-xs font-bold outline-none">
                            </div>
                            <div>
                                <label class="font-extrabold text-gray-900 block mb-1">Due Date</label>
                                <input type="date" name="due_date" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d', strtotime('+7 days')) }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2 text-xs outline-none">
                            </div>
                        </div>

                        {{-- Dynamic Question Repeater --}}
                        <div class="pt-2 border-t border-gray-100 space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="font-heading font-black text-xs text-gray-900 uppercase tracking-wider">Quiz Questions (<span x-text="quizQuestions.length"></span>)</h4>
                                <button type="button" @click="addQuizQuestion()" class="py-1 px-3 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-xl text-[11px] font-bold inline-flex items-center gap-1 cursor-pointer transition-colors">
                                    + Add Question
                                </button>
                            </div>

                            <template x-for="(q, qIndex) in quizQuestions" :key="qIndex">
                                <div class="p-4 bg-gray-50/80 rounded-2xl border border-gray-200/80 space-y-3 relative">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-gray-900 text-xs" x-text="`Question ${qIndex + 1}`"></span>
                                        <button type="button" @click="removeQuizQuestion(qIndex)" x-show="quizQuestions.length > 1" class="text-red-500 hover:text-red-700 font-bold text-xs cursor-pointer">
                                            Remove
                                        </button>
                                    </div>

                                    <div>
                                        <label class="font-bold text-gray-700 text-[11px] block mb-1">Question Prompt *</label>
                                        <textarea :name="`questions[${qIndex}][question_text]`" x-model="q.question_text" @input="saveQuizDraft()" required rows="2" placeholder="Enter question or problem statement..." class="w-full bg-white border border-gray-200 rounded-xl p-2.5 text-xs outline-none focus:ring-1 focus:ring-blue-500"></textarea>
                                    </div>

                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="font-bold text-gray-600 text-[10px] block mb-0.5">Option A *</label>
                                            <input type="text" :name="`questions[${qIndex}][option_a]`" x-model="q.option_a" @input="saveQuizDraft()" required placeholder="Option A" class="w-full bg-white border border-gray-200 rounded-xl px-2.5 py-1.5 text-xs outline-none">
                                        </div>
                                        <div>
                                            <label class="font-bold text-gray-600 text-[10px] block mb-0.5">Option B *</label>
                                            <input type="text" :name="`questions[${qIndex}][option_b]`" x-model="q.option_b" @input="saveQuizDraft()" required placeholder="Option B" class="w-full bg-white border border-gray-200 rounded-xl px-2.5 py-1.5 text-xs outline-none">
                                        </div>
                                        <div>
                                            <label class="font-bold text-gray-600 text-[10px] block mb-0.5">Option C *</label>
                                            <input type="text" :name="`questions[${qIndex}][option_c]`" x-model="q.option_c" @input="saveQuizDraft()" required placeholder="Option C" class="w-full bg-white border border-gray-200 rounded-xl px-2.5 py-1.5 text-xs outline-none">
                                        </div>
                                        <div>
                                            <label class="font-bold text-gray-600 text-[10px] block mb-0.5">Option D *</label>
                                            <input type="text" :name="`questions[${qIndex}][option_d]`" x-model="q.option_d" @input="saveQuizDraft()" required placeholder="Option D" class="w-full bg-white border border-gray-200 rounded-xl px-2.5 py-1.5 text-xs outline-none">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-3 gap-2 pt-1">
                                        <div>
                                            <label class="font-bold text-gray-900 text-[11px] block mb-1">Correct Answer *</label>
                                            <select :name="`questions[${qIndex}][correct_option]`" x-model="q.correct_option" @change="saveQuizDraft()" required class="w-full bg-white border border-gray-200 rounded-xl pl-2.5 pr-8 py-1.5 text-xs font-bold text-gray-900 outline-none cursor-pointer">
                                                <option value="A">A</option>
                                                <option value="B">B</option>
                                                <option value="C">C</option>
                                                <option value="D">D</option>
                                            </select>
                                        </div>
                                        <div class="col-span-2">
                                            <label class="font-bold text-gray-700 text-[11px] block mb-1">Explanation / Solution Hint</label>
                                            <input type="text" :name="`questions[${qIndex}][explanation]`" x-model="q.explanation" @input="saveQuizDraft()" placeholder="Optional explanation displayed after submission" class="w-full bg-white border border-gray-200 rounded-xl px-2.5 py-1.5 text-xs outline-none">
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 text-xs font-extrabold">
                        <button type="button" @click="showCreateModal = false" class="py-2.5 px-5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl transition-colors cursor-pointer">Cancel</button>
                        <button type="submit" :disabled="isSubmitting" class="py-2.5 px-6 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors shadow-sm cursor-pointer hover:shadow-md disabled:opacity-50 inline-flex items-center gap-1.5">
                            <span x-text="isSubmitting ? 'Publishing...' : 'Publish Quiz'"></span>
                        </button>
                    </div>
                </form>

                {{-- ── FORM 3: ASSESSMENT (TOPICAL / DIAGNOSTIC) ── --}}
                <form x-show="createTab === 'assessment'" @submit="submitTaskForm($event, false)" action="{{ route('teacher.tasks.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="type" value="Assessment">

                    <div class="space-y-3 text-xs">
                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Class & Subject *</label>
                            <select name="course_id" required class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-3.5 pr-9 py-2.5 text-xs font-bold outline-none cursor-pointer">
                                <option value="">Select class</option>
                                @foreach($courses as $c)
                                    <option value="{{ $c->id }}">{{ $c->title }} ({{ $c->subject->display_name ?? ($c->subject->name ?? 'Subject') }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Assessment Title *</label>
                            <input type="text" name="title" required placeholder="e.g. Ujian Topikal: Bab 4 Termokimia" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="font-extrabold text-gray-900 block mb-1">DSKP Topic / Domain *</label>
                                <input type="text" name="topic" required placeholder="e.g. 4.1 Perubahan Haba Reaksi" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none">
                            </div>
                            <div>
                                <label class="font-extrabold text-gray-900 block mb-1">Total Marks *</label>
                                <input type="number" name="total_marks" value="50" min="1" max="1000" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs font-bold outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Assessment Scope & Rubric Criteria</label>
                            <textarea name="description" rows="3" placeholder="Specify grading rubric and learning criteria..." class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-xs outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Attach Assessment Paper / Rubric (Optional)</label>
                            <input type="file" name="file" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2 text-xs outline-none file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="text-[10px] text-gray-400 mt-0.5">Supports PDF, Word, Images, ZIP (Max: 50MB)</p>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="font-extrabold text-gray-900 block mb-1">Due Date *</label>
                                <input type="date" name="due_date" required min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d', strtotime('+5 days')) }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs outline-none">
                            </div>
                            <div>
                                <label class="font-extrabold text-gray-900 block mb-1">Due Time</label>
                                <input type="time" name="due_time" value="23:59" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs outline-none">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 text-xs font-extrabold">
                        <button type="button" @click="showCreateModal = false" class="py-2.5 px-5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl transition-colors cursor-pointer">Cancel</button>
                        <button type="submit" :disabled="isSubmitting" class="py-2.5 px-6 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors shadow-sm cursor-pointer hover:shadow-md disabled:opacity-50 inline-flex items-center gap-1.5">
                            <span x-text="isSubmitting ? 'Publishing...' : 'Publish Assessment'"></span>
                        </button>
                    </div>
                </form>

                {{-- ── FORM 4: EXAM ── --}}
                <form x-show="createTab === 'exam'" @submit="submitTaskForm($event, false)" action="{{ route('teacher.tasks.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="type" value="Exam">

                    <div class="space-y-3 text-xs">
                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Class & Subject *</label>
                            <select name="course_id" required class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-3.5 pr-9 py-2.5 text-xs font-bold outline-none cursor-pointer">
                                <option value="">Select class</option>
                                @foreach($courses as $c)
                                    <option value="{{ $c->id }}">{{ $c->title }} ({{ $c->subject->display_name ?? ($c->subject->name ?? 'Subject') }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Exam Title *</label>
                            <input type="text" name="title" required placeholder="e.g. Peperiksaan Percubaan SPM 2026: Kertas 1 & 2" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="font-extrabold text-gray-900 block mb-1">Syllabus Scope</label>
                                <input type="text" name="topic" placeholder="e.g. Form 4 & Form 5 All Chapters" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none">
                            </div>
                            <div>
                                <label class="font-extrabold text-gray-900 block mb-1">Exam Duration (Minutes) *</label>
                                <input type="number" name="duration_minutes" value="120" min="1" max="600" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs font-bold outline-none">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="font-extrabold text-gray-900 block mb-1">Total Marks *</label>
                                <input type="number" name="total_marks" value="100" min="1" max="1000" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs font-bold outline-none">
                            </div>
                            <div>
                                <label class="font-extrabold text-gray-900 block mb-1">Exam Submission Deadline *</label>
                                <input type="date" name="due_date" required min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d', strtotime('+10 days')) }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2 text-xs outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Exam Instructions & Regulations</label>
                            <textarea name="description" rows="3" placeholder="Provide examination rules, allowed tools (calculator, formula sheet), and strict timing..." class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-xs outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Attach Exam Paper & Formula Sheet (PDF)</label>
                            <input type="file" name="file" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2 text-xs outline-none file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="text-[10px] text-gray-400 mt-0.5">Supports PDF, Word, ZIP (Max: 50MB)</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 text-xs font-extrabold">
                        <button type="button" @click="showCreateModal = false" class="py-2.5 px-5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl transition-colors cursor-pointer">Cancel</button>
                        <button type="submit" :disabled="isSubmitting" class="py-2.5 px-6 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors shadow-sm cursor-pointer hover:shadow-md disabled:opacity-50 inline-flex items-center gap-1.5">
                            <span x-text="isSubmitting ? 'Publishing...' : 'Schedule & Publish Exam'"></span>
                        </button>
                    </div>
                </form>

            </div>
        </div>

        {{-- ── 5. REVIEW SUBMISSIONS MODAL WIDGET ── --}}
        <div x-show="showReviewModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs p-4 sm:p-6 flex justify-center items-start sm:items-center">
            <div @click.away="showReviewModal = false" class="relative bg-white rounded-3xl p-6 sm:p-8 max-w-4xl w-full border border-gray-100 shadow-2xl space-y-6 my-auto max-h-[90vh] overflow-y-auto">
                
                <div class="flex items-start justify-between border-b border-gray-100 pb-3">
                    <div>
                        <div class="flex items-center gap-2 text-xs font-bold">
                            <h3 class="font-heading font-black text-lg text-gray-900" x-text="selectedTask?.name || selectedTask?.title || 'Task Details'"></h3>
                            <span class="font-bold text-gray-700 text-xs" x-text="`• ${selectedTask?.type || 'Assignment'}`"></span>
                        </div>
                        <p class="text-xs text-gray-500 font-medium mt-0.5" x-text="(selectedTask?.class || 'Class') + ' • ' + (selectedTask?.subject || 'Subject') + ' • Due: ' + (selectedTask?.dueDate || '–')"></p>
                        <template x-if="selectedTask?.attachmentUrl">
                            <div class="mt-2">
                                <a :href="selectedTask?.attachmentUrl" target="_blank" class="inline-flex items-center gap-1.5 py-1 px-3 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-xl text-xs font-bold transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    <span x-text="`Attached Material (${selectedTask?.attachmentName || 'Download'})`"></span>
                                </a>
                            </div>
                        </template>
                    </div>
                    <button type="button" @click="showReviewModal = false" class="w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-400 hover:text-gray-700 font-bold text-lg transition-colors cursor-pointer" title="Close">✕</button>
                </div>

                {{-- Stats Widget Bar --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="p-4 bg-gray-50 rounded-2xl flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full border-4 border-slate-900 flex items-center justify-center font-black text-gray-900 text-base shrink-0" x-text="selectedTask?.submittedCount || 0">
                        </div>
                        <div class="text-xs space-y-1">
                            <p class="font-bold text-gray-900" x-text="`${selectedTask?.submittedCount || 0} Submitted`"></p>
                            <p class="text-gray-500 font-semibold" x-text="`${selectedTask?.pendingCount || 0} Pending`"></p>
                            <p class="text-gray-500 font-semibold" x-text="`${selectedTask?.lateCount || 0} Late`"></p>
                        </div>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-2xl text-center flex flex-col justify-center">
                        <span class="text-2xl font-black text-gray-900 block" x-text="selectedTask?.totalStudents || 0"></span>
                        <span class="text-xs font-bold text-gray-400">Total Enrolled Students</span>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-2xl text-center flex flex-col justify-center">
                        <span class="text-2xl font-black text-gray-900 block" x-text="selectedTask?.submissionRate || '0%'"></span>
                        <span class="text-xs font-bold text-gray-400">Completion Rate</span>
                    </div>
                </div>

                {{-- Submissions Roster Table --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-2 text-xs font-extrabold">
                        <div class="flex gap-4">
                            <span class="text-gray-900 border-b-2 border-gray-900 pb-2 -mb-2" x-text="`Submissions (${selectedTask?.submittedCount || 0})`"></span>
                            <span class="text-gray-400" x-text="`Pending (${selectedTask?.pendingCount || 0})`"></span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-gray-200 text-gray-400 uppercase font-black">
                                    <th class="py-2.5 px-3">Student</th>
                                    <th class="py-2.5 px-3">Submitted On</th>
                                    <th class="py-2.5 px-3">Submission Details</th>
                                    <th class="py-2.5 px-3">Marks</th>
                                    <th class="py-2.5 px-3">Status</th>
                                    <th class="py-2.5 px-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 font-medium">
                                <template x-for="(st, idx) in (selectedTask?.roster || [])" :key="idx">
                                    <tr class="hover:bg-gray-50/70 transition-colors">
                                        <td class="py-3 px-3">
                                            <p class="font-extrabold text-gray-900" x-text="st.name"></p>
                                            <p class="text-[10px] text-gray-400" x-text="st.email || ''"></p>
                                        </td>
                                        <td class="py-3 px-3 text-gray-500 font-semibold" x-text="st.submittedDate"></td>
                                        <td class="py-3 px-3">
                                            <template x-if="st.hasSubmission">
                                                <div class="space-y-1">
                                                    <template x-if="st.notes">
                                                        <p class="text-[11px] text-gray-700 italic line-clamp-1" x-text="`“${st.notes}”`"></p>
                                                    </template>
                                                    <template x-if="st.fileUrl">
                                                        <a :href="st.fileUrl" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-blue-600 font-bold hover:underline">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                            <span x-text="st.fileName || 'Download Attachment'"></span>
                                                        </a>
                                                    </template>
                                                    <template x-if="st.gradedFileUrl">
                                                        <a :href="st.gradedFileUrl" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-emerald-600 font-bold hover:underline">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                            <span x-text="`Graded Task: ${st.gradedFileName || 'Download'}`"></span>
                                                        </a>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="!st.hasSubmission">
                                                <span class="text-gray-400 italic text-[11px]">No submission yet</span>
                                            </template>
                                        </td>
                                        <td class="py-3 px-3 font-black text-gray-900" x-text="st.marks"></td>
                                        <td class="py-3 px-3 font-semibold text-gray-700" x-text="st.status"></td>
                                        <td class="py-3 px-3 text-right">
                                            <template x-if="selectedTask?.type !== 'Quiz'">
                                                <button type="button" @click.stop="openGradingModal(st)" class="w-8 h-8 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 inline-flex items-center justify-center transition-all cursor-pointer" title="Grade & Feedback">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </button>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="!selectedTask?.roster || selectedTask?.roster.length === 0">
                                    <td colspan="6" class="py-6 text-center text-gray-400 font-semibold">
                                        No students enrolled in this class yet.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        {{-- ── 5B. GRADE & FEEDBACK SUBMISSION MODAL ── --}}
        <div x-show="showGradingModal" x-cloak class="fixed inset-0 z-[70] overflow-y-auto bg-slate-900/70 backdrop-blur-xs p-4 sm:p-6 flex justify-center items-start sm:items-center">
            <template x-if="showGradingModal && selectedSubmission">
                <div @click.away="showGradingModal = false" class="relative bg-white rounded-3xl p-6 sm:p-8 max-w-xl w-full border border-gray-100 shadow-2xl space-y-5 my-auto max-h-[90vh] overflow-y-auto transform transition-all">
                    
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div>
                            <h3 class="font-heading font-black text-base text-gray-900" x-text="`Grade: ${selectedSubmission?.name || 'Student'}`"></h3>
                            <p class="text-[11px] text-gray-500 font-medium" x-text="selectedTask?.name || selectedTask?.title"></p>
                        </div>
                        <button type="button" @click="showGradingModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-xl cursor-pointer">✕</button>
                    </div>

                    {{-- Student Submission Info Box --}}
                    <div class="p-4 bg-gray-50 rounded-2xl border border-gray-200/80 space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400 font-bold uppercase tracking-wider text-[10px]">Student Submission</span>
                            <span class="font-semibold text-gray-500" x-text="selectedSubmission?.submittedDate"></span>
                        </div>

                        <div>
                            <label class="text-[11px] text-gray-400 font-bold block mb-0.5">Written Answer / Notes:</label>
                            <p class="text-xs text-gray-900 bg-white p-2.5 rounded-xl border border-gray-100 leading-relaxed font-medium whitespace-pre-wrap" x-text="selectedSubmission?.notes || 'No text notes provided by student.'"></p>
                        </div>

                        <template x-if="selectedSubmission?.fileUrl">
                            <div class="pt-1">
                                <label class="text-[11px] text-gray-400 font-bold block mb-1">Attached Homework File:</label>
                                <a :href="selectedSubmission.fileUrl" target="_blank" class="inline-flex items-center gap-2 py-1.5 px-3 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-xl font-bold transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    <span x-text="`Download ${selectedSubmission.fileName || 'File'}`"></span>
                                </a>
                            </div>
                        </template>

                        <template x-if="selectedSubmission?.interactiveReviewUrl">
                            <div class="pt-2">
                                <a :href="selectedSubmission.interactiveReviewUrl" target="_blank" class="w-full py-2.5 px-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-extrabold text-xs flex items-center justify-center gap-2 shadow-xs transition cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    <span>Open Student Handwritten Work & Annotations →</span>
                                </a>
                            </div>
                        </template>
                    </div>

                    {{-- Teacher Grade & Feedback Form --}}
                    <form :action="'/teacher/tasks-and-assessments/' + (selectedTask?.id || '') + '/grade/' + (selectedSubmission?.student_id || '')" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                        @csrf

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="font-extrabold text-gray-900 block mb-1" x-text="`Score / Marks (out of ${selectedTask?.total_marks || 100}) *`"></label>
                                <input type="number" name="score" min="0" :max="selectedTask?.total_marks || 100" required x-model="selectedSubmission.rawMarks" placeholder="e.g. 85" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs font-bold outline-none focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div>
                                <label class="font-extrabold text-gray-900 block mb-1">Marking Status *</label>
                                <select name="status" required x-model="selectedSubmission.rawStatus" class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-3.5 pr-9 py-2.5 text-xs font-bold outline-none cursor-pointer">
                                    <option value="graded">Graded & Reviewed</option>
                                    <option value="submitted">Needs Revision</option>
                                    <option value="late">Late Submission</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Teacher Feedback & Remarks</label>
                            <textarea name="teacher_feedback" rows="3" placeholder="Provide constructive feedback, corrections, or praise for this student..." x-model="selectedSubmission.teacherFeedback" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium"></textarea>
                        </div>

                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Upload Graded Student Task (Annotated PDF, Marked Worksheet, etc.)</label>
                            <template x-if="selectedSubmission?.gradedFileUrl">
                                <div class="mb-2 p-2.5 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between">
                                    <div class="flex items-center gap-2 overflow-hidden">
                                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <div class="truncate">
                                            <span class="text-[10px] text-emerald-700 font-extrabold uppercase tracking-wider block">Graded Task Attached</span>
                                            <a :href="selectedSubmission.gradedFileUrl" target="_blank" class="text-xs font-bold text-emerald-800 hover:underline truncate" x-text="selectedSubmission.gradedFileName || 'Download Graded File'"></a>
                                        </div>
                                    </div>
                                    <span class="text-[10px] bg-emerald-200/60 text-emerald-800 font-bold px-2 py-0.5 rounded-full shrink-0">Attached</span>
                                </div>
                            </template>
                            <input type="file" name="graded_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.png,.jpg,.jpeg" class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-extrabold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-200 rounded-xl bg-gray-50">
                            <p class="text-[10px] text-gray-400 font-medium mt-1">Upload an annotated PDF, marked worksheet, or rubric document (PDF, Word, Images, ZIP up to 50MB). Re-uploading replaces the previous graded file.</p>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 font-extrabold">
                            <button type="button" @click="showGradingModal = false" class="py-2 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl transition-colors cursor-pointer">Cancel</button>
                            <button type="submit" class="py-2 px-5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors shadow-sm cursor-pointer hover:shadow-md">Save & Publish Grade</button>
                        </div>
                    </form>

                </div>
            </template>
        </div>

        {{-- ── 6. EDIT TASK / ASSESSMENT MODAL ── --}}
        <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="showEditModal = false" class="bg-white rounded-3xl shadow-2xl border border-gray-100 w-full max-w-2xl p-6 sm:p-7 space-y-6 my-auto max-h-[90vh] overflow-y-auto transform transition-all">
                
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div>
                        <h3 class="font-heading font-black text-lg text-gray-900">Edit Task / Assessment</h3>
                        <p class="text-xs text-gray-500 font-medium" x-text="editingTask?.type || 'Task'"></p>
                    </div>
                    <button type="button" @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-xl cursor-pointer">✕</button>
                </div>

                <form :action="'/teacher/tasks-and-assessments/' + editingTask?.id" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="space-y-3 text-xs">
                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Academic Type *</label>
                            <select name="type" x-model="editingTask.type" required class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-3.5 pr-9 py-2.5 text-xs font-bold outline-none cursor-pointer">
                                <option value="Assignment">Assignment</option>
                                <option value="Assessment">Assessment</option>
                                <option value="Exam">Exam</option>
                            </select>
                        </div>

                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Title *</label>
                            <input type="text" name="title" required x-model="editingTask.name" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="font-extrabold text-gray-900 block mb-1">Topic / Domain</label>
                                <input type="text" name="topic" x-model="editingTask.topic" placeholder="e.g. Chapter 3" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none">
                            </div>
                            <div>
                                <label class="font-extrabold text-gray-900 block mb-1">Total Marks</label>
                                <input type="number" name="total_marks" x-model="editingTask.total_marks" min="1" max="1000" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs font-bold outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Description / Instructions</label>
                            <textarea name="description" rows="3" x-model="editingTask.desc" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-xs outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>

                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Replace Attached File (Optional)</label>
                            <div class="p-3 bg-gray-50 rounded-2xl border border-gray-200 space-y-2">
                                <template x-if="editingTask?.attachmentName">
                                    <p class="text-[11px] text-gray-500 font-medium">Current file: <strong class="text-gray-900" x-text="editingTask.attachmentName"></strong></p>
                                </template>
                                <input type="file" name="file" class="w-full bg-white border border-gray-200 rounded-xl p-2 text-xs outline-none file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                <p class="text-[10px] text-gray-400">Leave blank to keep existing attached material.</p>
                            </div>
                        </div>

                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Class & Subject *</label>
                            <select name="course_id" required x-model="editingTask.course_id" class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-3.5 pr-9 py-2.5 text-xs font-bold outline-none cursor-pointer">
                                @foreach($courses as $c)
                                    <option value="{{ $c->id }}">{{ $c->title }} ({{ $c->subject->display_name ?? ($c->subject->name ?? 'Subject') }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="font-extrabold text-gray-900 block mb-1">Due Date *</label>
                                <input type="date" name="due_date" required x-model="editingTask.rawDueDate" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs outline-none">
                            </div>
                            <div>
                                <label class="font-extrabold text-gray-900 block mb-1">Due Time</label>
                                <input type="time" name="due_time" value="23:59" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs outline-none">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 text-xs font-extrabold">
                        <button type="button" @click="showEditModal = false" class="py-2.5 px-5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl transition-colors cursor-pointer">Cancel</button>
                        <button type="submit" class="py-2.5 px-6 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors shadow-sm cursor-pointer hover:shadow-md">Update Item</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-teacher-layout>
