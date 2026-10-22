<x-teacher-layout>
    <x-slot name="title">Students - Teacher Portal</x-slot>

    <div x-data="{ 
        tab: 'all',
        searchQuery: '',
        selectedClass: 'all',
        selectedForm: 'all',
        selectedSubject: 'all',
        selectedStatus: 'all',
        selectedStudent: null,
        students: {{ \Illuminate\Support\Js::from($mappedStudents) }},
        get filteredStudents() {
            return this.students.filter(s => {
                const matchesTab = this.tab === 'all' || 
                                   (this.tab === 'attention' && s.category === 'attention') ||
                                   (this.tab === 'catchup' && s.catchup !== 'None') ||
                                   (this.tab === 'intervention' && s.category === 'intervention') ||
                                   (this.tab === 'good' && s.category === 'good');

                const matchesQuery = !this.searchQuery || 
                                     s.name.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                                     s.id.toLowerCase().includes(this.searchQuery.toLowerCase());

                const matchesClass = this.selectedClass === 'all' || s.class === this.selectedClass;
                const matchesSubject = this.selectedSubject === 'all' || s.subject === this.selectedSubject;
                const matchesStatus = this.selectedStatus === 'all' || s.status === this.selectedStatus;

                return matchesTab && matchesQuery && matchesClass && matchesSubject && matchesStatus;
            });
        },
        resetFilters() {
            this.searchQuery = '';
            this.selectedClass = 'all';
            this.selectedForm = 'all';
            this.selectedSubject = 'all';
            this.selectedStatus = 'all';
            this.tab = 'all';
        }
    }" class="space-y-6">

        {{-- ── HEADER SECTION ── --}}
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="font-heading font-black text-2xl text-gray-900">Students</h1>
                <p class="text-xs text-gray-500 font-medium mt-0.5">View and monitor students across all the classes you teach.</p>
            </div>

            <button class="py-2.5 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl text-xs font-extrabold shadow-2xs transition-colors cursor-pointer shrink-0 inline-flex items-center gap-1.5">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Export</span>
            </button>
        </div>

        {{-- ── 1. TOP 3 KPI SUMMARY CARDS ── --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            
            {{-- Total Students --}}
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-blue-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-black text-gray-900" x-text="students.length"></p>
                    <p class="text-xs font-bold text-gray-400">Total Students</p>
                    <p class="text-[11px] text-gray-500 font-medium">Across all classes</p>
                </div>
            </div>

            {{-- Need Attention --}}
            <div class="bg-amber-50/40 rounded-3xl p-5 border border-amber-200/70 shadow-sm flex items-center gap-4 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-amber-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-black text-amber-900" x-text="students.filter(s => s.category === 'attention').length"></p>
                    <p class="text-xs font-bold text-amber-800/80">Need Attention</p>
                    <p class="text-[11px] text-amber-700 font-medium">Require your action</p>
                </div>
            </div>

            {{-- Intervention Active --}}
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-emerald-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-black text-emerald-600" x-text="students.filter(s => s.category === 'intervention').length"></p>
                    <p class="text-xs font-bold text-gray-400">Intervention Active</p>
                    <p class="text-[11px] text-gray-500 font-medium">Students under support</p>
                </div>
            </div>
        </div>

        {{-- ── 2. SEARCH & MULTI-DROPDOWN FILTER BAR ── --}}
        <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm space-y-4">
            <div class="flex flex-col xl:flex-row items-stretch xl:items-center gap-3">
                {{-- Search Bar --}}
                <div class="relative flex-1 min-w-[220px]">
                    <input type="text" x-model="searchQuery" placeholder="Search student by name or ID..." class="w-full bg-gray-50 border border-gray-200/80 rounded-2xl pl-9 pr-4 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                {{-- Filter Dropdowns & Reset Button --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 xl:flex xl:items-center gap-2.5">
                    {{-- Class Dropdown --}}
                    <div class="w-full xl:w-auto xl:min-w-[145px]">
                        <select x-model="selectedClass" class="w-full bg-gray-50 border border-gray-200/80 rounded-2xl pl-3.5 pr-9 py-2.5 text-xs font-bold text-gray-700 outline-none cursor-pointer">
                            <option value="all">Class: All Classes</option>
                            <option value="Form 4A">Form 4A</option>
                            <option value="Form 4B">Form 4B</option>
                            <option value="Year 6A">Year 6A</option>
                        </select>
                    </div>

                    {{-- Form Dropdown --}}
                    <div class="w-full xl:w-auto xl:min-w-[135px]">
                        <select x-model="selectedForm" class="w-full bg-gray-50 border border-gray-200/80 rounded-2xl pl-3.5 pr-9 py-2.5 text-xs font-bold text-gray-700 outline-none cursor-pointer">
                            <option value="all">Year / Form: All</option>
                            <option value="Form 4">Form 4</option>
                            <option value="Year 6">Year 6</option>
                        </select>
                    </div>

                    {{-- Subject Dropdown --}}
                    <div class="w-full xl:w-auto xl:min-w-[155px]">
                        <select x-model="selectedSubject" class="w-full bg-gray-50 border border-gray-200/80 rounded-2xl pl-3.5 pr-9 py-2.5 text-xs font-bold text-gray-700 outline-none cursor-pointer">
                            <option value="all">Subject: All Subjects</option>
                            <option value="Mathematics">Mathematics</option>
                            <option value="Physics">Physics</option>
                            <option value="Science">Science</option>
                        </select>
                    </div>

                    {{-- Status Dropdown --}}
                    <div class="w-full xl:w-auto xl:min-w-[155px]">
                        <select x-model="selectedStatus" class="w-full bg-gray-50 border border-gray-200/80 rounded-2xl pl-3.5 pr-9 py-2.5 text-xs font-bold text-gray-700 outline-none cursor-pointer">
                            <option value="all">Status: All Status</option>
                            <option value="Needs Attention">Needs Attention</option>
                            <option value="Good">Good</option>
                            <option value="Intervention">Intervention</option>
                        </select>
                    </div>

                    {{-- Reset --}}
                    <button @click="resetFilters()" class="col-span-2 sm:col-span-4 xl:col-auto py-2.5 px-3.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-extrabold text-xs rounded-2xl transition-colors cursor-pointer shrink-0 inline-flex items-center justify-center gap-1">
                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Reset</span>
                    </button>
                </div>
            </div>

            {{-- 5 Sub-Tabs Filter Bar --}}
            <div class="flex flex-wrap gap-4 text-xs font-extrabold border-t border-gray-100 pt-4">
                <button @click="tab = 'all'" :class="tab === 'all' ? 'text-blue-600 border-b-2 border-blue-600 pb-2 -mb-4' : 'text-gray-500 hover:text-gray-900 pb-2'" class="transition-colors cursor-pointer">
                    <span x-text="'All Students (' + students.length + ')'"></span>
                </button>
                <button @click="tab = 'attention'" :class="tab === 'attention' ? 'text-blue-600 border-b-2 border-blue-600 pb-2 -mb-4' : 'text-gray-500 hover:text-gray-900 pb-2'" class="transition-colors cursor-pointer">
                    <span x-text="'Needs Attention (' + students.filter(s => s.category === 'attention').length + ')'"></span>
                </button>
                <button @click="tab = 'catchup'" :class="tab === 'catchup' ? 'text-blue-600 border-b-2 border-blue-600 pb-2 -mb-4' : 'text-gray-500 hover:text-gray-900 pb-2'" class="transition-colors cursor-pointer">
                    <span x-text="'Catch-Up Pending (' + students.filter(s => s.catchup !== 'None').length + ')'"></span>
                </button>
                <button @click="tab = 'intervention'" :class="tab === 'intervention' ? 'text-blue-600 border-b-2 border-blue-600 pb-2 -mb-4' : 'text-gray-500 hover:text-gray-900 pb-2'" class="transition-colors cursor-pointer">
                    <span x-text="'Intervention (' + students.filter(s => s.category === 'intervention').length + ')'"></span>
                </button>
                <button @click="tab = 'good'" :class="tab === 'good' ? 'text-blue-600 border-b-2 border-blue-600 pb-2 -mb-4' : 'text-gray-500 hover:text-gray-900 pb-2'" class="transition-colors cursor-pointer">
                    <span x-text="'Good Progress (' + students.filter(s => s.category === 'good').length + ')'"></span>
                </button>
            </div>
        </div>

        {{-- ── 3. STUDENT ROSTER TABLE ── --}}
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-gray-200 text-gray-400 uppercase font-black">
                            <th class="py-3 px-4">Student</th>
                            <th class="py-3 px-4">Class</th>
                            <th class="py-3 px-4">Attendance</th>
                            <th class="py-3 px-4">Progress</th>
                            <th class="py-3 px-4">Pending Tasks</th>
                            <th class="py-3 px-4">Latest Assessment</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium">
                        <template x-for="st in filteredStudents" :key="st.id">
                            <tr @click="selectedStudent = st" class="hover:bg-gray-50/80 cursor-pointer transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-blue-900 text-white font-black text-xs flex items-center justify-center shrink-0">
                                            <span x-text="st.name.split(' ').map(n=>n[0]).join('')"></span>
                                        </div>
                                        <div>
                                            <h5 class="font-extrabold text-gray-900" x-text="st.name"></h5>
                                            <p class="text-[10px] text-gray-400" x-text="'ID: ' + st.id"></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <p class="font-bold text-gray-900" x-text="st.class"></p>
                                    <p class="text-[10px] text-gray-400" x-text="st.subject"></p>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-black text-emerald-600" x-text="st.attendance + '%'"></span>
                                    <div class="w-16 bg-gray-100 rounded-full h-1 mt-1"><div class="bg-emerald-500 h-1 rounded-full" :style="'width: ' + st.attendance + '%'"></div></div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-black text-blue-600" x-text="st.progress + '%'"></span>
                                    <div class="w-16 bg-gray-100 rounded-full h-1 mt-1"><div class="bg-blue-600 h-1 rounded-full" :style="'width: ' + st.progress + '%'"></div></div>
                                </td>
                                <td class="py-3.5 px-4 font-bold" :class="st.pendingTasks.includes('Pending') ? 'text-amber-600' : 'text-emerald-600'" x-text="st.pendingTasks"></td>
                                <td class="py-3.5 px-4">
                                    <p class="font-black text-gray-900" x-text="st.latestAssessment"></p>
                                    <p class="text-[10px] text-gray-400" x-text="st.latestDate"></p>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span :class="{
                                        'bg-amber-50 text-amber-700 border-amber-200/60': st.status === 'Needs Attention',
                                        'bg-emerald-50 text-emerald-700 border-emerald-200/60': st.status === 'Good',
                                        'bg-red-50 text-red-700 border-red-200/60': st.status === 'Intervention'
                                    }" class="px-2.5 py-1 rounded-full font-black text-[10px] border inline-flex items-center gap-1" x-text="st.status"></span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <button type="button" @click.stop="selectedStudent = st" class="w-8 h-8 rounded-xl bg-blue-50 hover:bg-blue-600 text-blue-600 hover:text-white inline-flex items-center justify-center transition-all cursor-pointer shadow-2xs" title="View Student Profile">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-between pt-3 border-t border-gray-100 text-xs">
                <span class="text-gray-400 font-semibold" x-text="'Showing 1 to ' + filteredStudents.length + ' of 86 students'"></span>
                
                <div class="flex items-center gap-1">
                    <button class="px-2.5 py-1 rounded-lg bg-gray-100 text-gray-600 font-bold">‹</button>
                    <button class="px-2.5 py-1 rounded-lg bg-blue-600 text-white font-bold">1</button>
                    <button class="px-2.5 py-1 rounded-lg bg-gray-100 text-gray-600 font-bold">2</button>
                    <button class="px-2.5 py-1 rounded-lg bg-gray-100 text-gray-600 font-bold">3</button>
                    <span class="px-1 text-gray-400 font-bold">...</span>
                    <button class="px-2.5 py-1 rounded-lg bg-gray-100 text-gray-600 font-bold">18</button>
                    <button class="px-2.5 py-1 rounded-lg bg-gray-100 text-gray-600 font-bold">›</button>
                </div>
            </div>
        </div>

        {{-- ── 4. QUICK STUDENT PROFILE MODAL ── --}}
        <div x-show="selectedStudent" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="selectedStudent = null" class="bg-white rounded-3xl shadow-2xl border border-gray-100 w-full max-w-2xl p-6 sm:p-7 space-y-6 my-auto max-h-[90vh] overflow-y-auto transform transition-all">
                
                {{-- Drawer Header --}}
                <div class="flex items-start justify-between border-b border-gray-100 pb-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-full bg-blue-900 text-white font-black text-sm flex items-center justify-center shrink-0 shadow-sm">
                            <span x-text="selectedStudent?.name.split(' ').map(n=>n[0]).join('')"></span>
                        </div>
                        <div>
                            <h3 class="font-heading font-black text-lg text-gray-900" x-text="selectedStudent?.name"></h3>
                            <p class="text-xs text-gray-500 font-semibold" x-text="'ID: ' + selectedStudent?.id + ' • ' + selectedStudent?.class + ' • ' + selectedStudent?.subject"></p>
                        </div>
                    </div>
                    <button @click="selectedStudent = null" class="text-gray-400 hover:text-gray-600 font-bold text-xl cursor-pointer">✕</button>
                </div>

                {{-- 4 Mini KPI Cards --}}
                <div class="grid grid-cols-4 gap-2 text-center text-xs">
                    <div class="p-3 bg-gray-50 rounded-2xl">
                        <span class="font-black text-emerald-600 text-base block" x-text="selectedStudent?.attendance + '%'"></span>
                        <span class="text-[10px] text-gray-400 font-bold">Attendance</span>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-2xl">
                        <span class="font-black text-blue-600 text-base block" x-text="selectedStudent?.progress + '%'"></span>
                        <span class="text-[10px] text-gray-400 font-bold">Overall Progress</span>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-2xl">
                        <span class="font-black text-amber-600 text-base block" x-text="selectedStudent?.pendingTasks.includes('Pending') ? '1' : '0'"></span>
                        <span class="text-[10px] text-gray-400 font-bold">Pending Tasks</span>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-2xl">
                        <span class="font-black text-purple-700 text-base block" x-text="selectedStudent?.latestAssessment"></span>
                        <span class="text-[10px] text-gray-400 font-bold" x-text="'Latest Assessment (' + selectedStudent?.latestDate + ')'"></span>
                    </div>
                </div>

                {{-- Overview List --}}
                <div class="space-y-3">
                    <h4 class="font-heading font-black text-xs text-gray-400 uppercase tracking-wider">Overview</h4>

                    <div class="p-3.5 bg-emerald-50/50 rounded-2xl border border-emerald-100 flex items-center justify-between text-xs cursor-pointer">
                        <div class="flex items-center gap-2.5">
                            <span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </span>
                            <div>
                                <strong class="text-gray-900 block font-bold">Strengths</strong>
                                <span class="text-gray-600 font-medium" x-text="selectedStudent?.strengths"></span>
                            </div>
                        </div>
                        <span class="text-gray-400 font-bold">›</span>
                    </div>

                    <div class="p-3.5 bg-amber-50/50 rounded-2xl border border-amber-100 flex items-center justify-between text-xs cursor-pointer">
                        <div class="flex items-center gap-2.5">
                            <span class="w-6 h-6 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-xs shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </span>
                            <div>
                                <strong class="text-gray-900 block font-bold">Needs Attention</strong>
                                <span class="text-gray-600 font-medium" x-text="selectedStudent?.weaknesses"></span>
                            </div>
                        </div>
                        <span class="text-gray-400 font-bold">›</span>
                    </div>

                    <div class="p-3.5 bg-blue-50/50 rounded-2xl border border-blue-100 flex items-center justify-between text-xs cursor-pointer">
                        <div class="flex items-center gap-2.5">
                            <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </span>
                            <div>
                                <strong class="text-gray-900 block font-bold">Catch-Up Status</strong>
                                <span class="text-gray-600 font-medium" x-text="selectedStudent?.catchup"></span>
                            </div>
                        </div>
                        <span class="text-gray-400 font-bold">›</span>
                    </div>

                    <div class="p-3.5 bg-red-50/50 rounded-2xl border border-red-100 flex items-center justify-between text-xs cursor-pointer">
                        <div class="flex items-center gap-2.5">
                            <span class="w-6 h-6 rounded-lg bg-red-100 text-red-700 flex items-center justify-center font-bold text-xs shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <div>
                                <strong class="text-gray-900 block font-bold">Intervention</strong>
                                <span class="text-gray-600 font-medium" x-text="selectedStudent?.intervention"></span>
                            </div>
                        </div>
                        <span class="text-gray-400 font-bold">›</span>
                    </div>
                </div>

                {{-- Latest Teacher Note --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <h4 class="font-heading font-black text-xs text-gray-400 uppercase tracking-wider">Latest Teacher Note</h4>
                        <a href="#" class="text-[11px] font-extrabold text-blue-600 hover:underline">View All</a>
                    </div>

                    <div class="p-4 bg-amber-50/40 rounded-2xl border border-amber-200/60 space-y-2 text-xs">
                        <span class="text-[10px] text-gray-400 font-bold block">12 Aug 2026</span>
                        <p class="text-gray-800 italic leading-relaxed" x-text="selectedStudent?.note"></p>
                        <span class="inline-block px-2 py-0.5 rounded-md bg-white text-gray-500 font-extrabold text-[10px] border border-gray-200/60">Internal Note</span>
                    </div>
                </div>

                {{-- 3 Bottom Action Buttons --}}
                <div class="grid grid-cols-3 gap-2 pt-2 text-xs font-extrabold">
                    <button class="py-2.5 px-3 bg-white hover:bg-gray-50 text-blue-700 rounded-xl border border-blue-200 transition-colors shadow-2xs cursor-pointer text-center">
                        View Full Progress
                    </button>
                    <button class="py-2.5 px-3 bg-white hover:bg-gray-50 text-blue-700 rounded-xl border border-blue-200 transition-colors shadow-2xs cursor-pointer text-center">
                        Add Note
                    </button>
                    <button class="py-2.5 px-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors shadow-sm cursor-pointer text-center">
                        Assign Support
                    </button>
                </div>

            </div>
            </div>
        </div>

    </div>
</x-teacher-layout>
