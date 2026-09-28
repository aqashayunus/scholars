<x-teacher-layout>
    <x-slot name="title">Learning Materials - Teacher Portal</x-slot>

    <div x-data="{ 
        searchQuery: '',
        selectedClass: 'all',
        selectedSubject: 'all',
        selectedTopic: 'all',
        selectedStatus: 'all',
        showUploadModal: false,
        showEditModal: false,
        editingMaterial: null,
        showAccessModal: false,
        selectedFileName: '',
        activeActionMenu: null,
        materials: {{ \Illuminate\Support\Js::from($mappedMaterials) }},
        get filteredMaterials() {
            return this.materials.filter(m => {
                const matchesQuery = !this.searchQuery || m.name.toLowerCase().includes(this.searchQuery.toLowerCase());
                const matchesClass = this.selectedClass === 'all' || m.class === this.selectedClass;
                const matchesSubject = this.selectedSubject === 'all' || m.subject === this.selectedSubject;
                const matchesTopic = this.selectedTopic === 'all' || m.topic === this.selectedTopic;
                const matchesStatus = this.selectedStatus === 'all' || m.status === this.selectedStatus;
                return matchesQuery && matchesClass && matchesSubject && matchesTopic && matchesStatus;
            });
        },
        resetFilters() {
            this.searchQuery = '';
            this.selectedClass = 'all';
            this.selectedSubject = 'all';
            this.selectedTopic = 'all';
            this.selectedStatus = 'all';
        }
    }" class="space-y-6">

        {{-- ── NOTIFICATIONS & FLASH MESSAGES ── --}}
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl flex items-center justify-between text-xs font-bold shadow-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-black">✕</button>
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
                <h1 class="font-heading font-black text-2xl text-gray-900">Learning Materials</h1>
                <p class="text-xs text-gray-500 font-medium mt-0.5">Upload notes, worksheets, slides, and study documents for your enrolled classes.</p>
            </div>

            <button @click="showUploadModal = true" class="py-2.5 px-5 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl text-xs font-extrabold shadow-sm transition-all cursor-pointer shrink-0 inline-flex items-center gap-1.5 hover:shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>Upload Material</span>
            </button>
        </div>

        {{-- ── 1. TOP 3 KPI SUMMARY CARDS ── --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            
            {{-- Total Materials --}}
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-purple-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-purple-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-black text-gray-900" x-text="materials.length"></p>
                    <p class="text-xs font-bold text-gray-400">Total Materials</p>
                    <p class="text-[11px] text-gray-500 font-medium">All materials uploaded</p>
                </div>
            </div>

            {{-- Published --}}
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-emerald-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-black text-emerald-600" x-text="materials.filter(m => m.status === 'Published').length"></p>
                    <p class="text-xs font-bold text-gray-400">Published</p>
                    <p class="text-[11px] text-gray-500 font-medium">Visible to students</p>
                </div>
            </div>

            {{-- Draft / Scheduled --}}
            <div class="bg-amber-50/40 rounded-3xl p-5 border border-amber-200/70 shadow-sm flex items-center gap-4 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-amber-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-black text-amber-900" x-text="materials.filter(m => m.status === 'Draft' || m.status === 'Scheduled').length"></p>
                    <p class="text-xs font-bold text-amber-800/80">Draft / Scheduled</p>
                    <p class="text-[11px] text-amber-700 font-medium">Not yet published</p>
                </div>
            </div>
        </div>

        {{-- ── 2. SEARCH & MULTI-DROPDOWN FILTER BAR ── --}}
        <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                {{-- Search Bar --}}
                <div class="lg:col-span-2 relative">
                    <input type="text" x-model="searchQuery" placeholder="Search material..." class="w-full bg-gray-50 border border-gray-200/80 rounded-2xl pl-9 pr-4 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                {{-- Class Dropdown --}}
                <div>
                    <select x-model="selectedClass" class="w-full bg-gray-50 border border-gray-200/80 rounded-2xl px-3 py-2.5 text-xs font-bold text-gray-700 outline-none cursor-pointer">
                        <option value="all">Class: All Classes</option>
                        @foreach($courses->pluck('level')->unique() as $lvl)
                            <option value="{{ $lvl }}">{{ $lvl }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Subject Dropdown --}}
                <div>
                    <select x-model="selectedSubject" class="w-full bg-gray-50 border border-gray-200/80 rounded-2xl px-3 py-2.5 text-xs font-bold text-gray-700 outline-none cursor-pointer">
                        <option value="all">Subject: All Subjects</option>
                        @foreach($courses->map(fn($c) => $c->subject->name ?? null)->filter()->unique() as $sName)
                            <option value="{{ $sName }}">{{ $sName }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Topic Dropdown --}}
                <div>
                    <select x-model="selectedTopic" class="w-full bg-gray-50 border border-gray-200/80 rounded-2xl px-3 py-2.5 text-xs font-bold text-gray-700 outline-none cursor-pointer">
                        <option value="all">Topic: All Topics</option>
                        <template x-for="top in [...new Set(materials.map(m => m.topic))]" :key="top">
                            <option :value="top" x-text="top"></option>
                        </template>
                    </select>
                </div>

                {{-- Status & Reset --}}
                <div class="flex items-center gap-2">
                    <select x-model="selectedStatus" class="w-full bg-gray-50 border border-gray-200/80 rounded-2xl px-3 py-2.5 text-xs font-bold text-gray-700 outline-none cursor-pointer">
                        <option value="all">Status: All Status</option>
                        <option value="Published">Published</option>
                        <option value="Draft">Draft</option>
                        <option value="Scheduled">Scheduled</option>
                    </select>
                    <button @click="resetFilters()" class="py-2.5 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-extrabold text-xs rounded-2xl transition-colors cursor-pointer shrink-0 inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Reset</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ── 3. LEARNING MATERIALS TABLE ── --}}
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-gray-200 text-gray-400 uppercase font-black">
                            <th class="py-3 px-4">Material</th>
                            <th class="py-3 px-4">Class</th>
                            <th class="py-3 px-4">Subject</th>
                            <th class="py-3 px-4">Topic</th>
                            <th class="py-3 px-4">Type</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Updated</th>
                            <th class="py-3 px-4">File Size</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium">
                        <template x-for="mat in filteredMaterials" :key="mat.id">
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div :class="mat.iconBg" class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 shadow-2xs" x-html="mat.icon"></div>
                                        <div>
                                            <h5 class="font-extrabold text-gray-900 leading-snug" x-text="mat.name"></h5>
                                            <p class="text-[10px] text-gray-400" x-text="mat.file_name || mat.ext"></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-gray-900" x-text="mat.class"></td>
                                <td class="py-3.5 px-4 text-gray-600" x-text="mat.subject"></td>
                                <td class="py-3.5 px-4 font-semibold text-gray-800" x-text="mat.topic"></td>
                                <td class="py-3.5 px-4">
                                    <span :class="mat.typeBg" class="px-2.5 py-0.5 rounded-md font-extrabold text-[10px]" x-text="mat.type"></span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span :class="mat.statusBg" class="px-2.5 py-0.5 rounded-full font-black text-[10px] inline-flex items-center gap-1" x-text="'● ' + mat.status"></span>
                                </td>
                                <td class="py-3.5 px-4 text-gray-500 font-semibold" x-text="mat.updated"></td>
                                <td class="py-3.5 px-4 font-semibold text-gray-600" x-text="mat.size || '-'"></td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <template x-if="mat.is_real">
                                            <button type="button" @click="editingMaterial = JSON.parse(JSON.stringify(mat)); editFileName = ''; showEditModal = true;" class="w-8 h-8 rounded-xl bg-amber-50 hover:bg-amber-600 text-amber-600 hover:text-white inline-flex items-center justify-center transition-all shadow-2xs cursor-pointer" title="Edit Material">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                        </template>

                                        <template x-if="mat.is_real">
                                            <a :href="mat.download_url" target="_blank" class="w-8 h-8 rounded-xl bg-blue-50 hover:bg-blue-600 text-blue-600 hover:text-white inline-flex items-center justify-center transition-all shadow-2xs" title="Download Material">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            </a>
                                        </template>

                                        <template x-if="mat.is_real">
                                            <form :action="mat.delete_url" method="POST" onsubmit="return confirm('Are you sure you want to delete this material?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-8 h-8 rounded-xl bg-red-50 hover:bg-red-600 text-red-500 hover:text-white inline-flex items-center justify-center transition-all cursor-pointer shadow-2xs" title="Delete Material">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="filteredMaterials.length === 0">
                            <td colspan="9" class="py-8 text-center text-gray-400 font-semibold">
                                No learning materials found. Click <strong>+ Upload Material</strong> to add your first notes or worksheet!
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-between pt-3 border-t border-gray-100 text-xs">
                <span class="text-gray-400 font-semibold" x-text="`Showing ${filteredMaterials.length} material(s)`"></span>
            </div>
        </div>

        {{-- ── 4. UPLOAD NEW MATERIAL SIDE DRAWER FORM MODAL ── --}}
        <div x-show="showUploadModal" x-cloak class="fixed inset-0 z-50 flex justify-end bg-slate-900/40 backdrop-blur-xs">
            <div @click.away="showUploadModal = false" class="w-full max-w-xl bg-white h-full p-6 space-y-6 shadow-2xl overflow-y-auto transform transition-all">
                
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="font-heading font-black text-lg text-gray-900">Upload New Material</h3>
                    <button @click="showUploadModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-xl cursor-pointer">✕</button>
                </div>

                {{-- REAL FORM SUBMISSION --}}
                <form action="{{ route('teacher.materials.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    {{-- 1. Basic Information --}}
                    <div class="space-y-3 text-xs">
                        <h4 class="font-heading font-black text-xs text-gray-400 uppercase tracking-wider">1. Basic Information</h4>
                        
                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Title *</label>
                            <input type="text" name="title" required placeholder="e.g. Chapter 3 Quadratic Equations Complete Guide" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="font-extrabold text-gray-900 block mb-1">Material Type *</label>
                                <select name="type" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-bold outline-none cursor-pointer">
                                    <option value="Notes">Notes / Handout</option>
                                    <option value="Worksheet">Worksheet / Exercises</option>
                                    <option value="Slides">Presentation Slides</option>
                                    <option value="Document">Document / Reference</option>
                                    <option value="Past Paper">Past Year Exam Paper</option>
                                </select>
                            </div>
                            <div>
                                <label class="font-extrabold text-gray-900 block mb-1">Assign to Class / Subject *</label>
                                <select name="course_id" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-bold outline-none cursor-pointer">
                                    <option value="">Select class</option>
                                    @foreach($courses as $c)
                                        <option value="{{ $c->id }}">{{ $c->title }} ({{ $c->subject->name ?? 'Subject' }} - {{ $c->level }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Topic / Chapter</label>
                            <input type="text" name="topic" placeholder="e.g. Quadratic Functions & Equations" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                        </div>

                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Short Description (Optional)</label>
                            <textarea name="description" rows="3" placeholder="Add instructions or overview for students..." class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium"></textarea>
                        </div>
                    </div>

                    {{-- 2. Upload File Dropzone --}}
                    <div class="space-y-2 text-xs">
                        <h4 class="font-heading font-black text-xs text-gray-400 uppercase tracking-wider">2. Upload File *</h4>
                        
                        <div class="border-2 border-dashed border-blue-200 rounded-3xl p-6 text-center bg-blue-50/30 space-y-2 relative hover:bg-blue-50/60 transition-colors">
                            <input type="file" name="file" required 
                                   @change="selectedFileName = $event.target.files[0] ? $event.target.files[0].name : ''"
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                            
                            <div class="w-10 h-10 rounded-2xl bg-blue-600 text-white font-black text-sm flex items-center justify-center mx-auto shadow-md shadow-blue-500/20">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            </div>
                            <p class="font-extrabold text-gray-900" x-text="selectedFileName ? selectedFileName : 'Drag & drop your file here or click to browse'"></p>
                            <span class="px-3 py-1.5 rounded-xl bg-white border border-gray-200 text-blue-700 font-extrabold text-xs inline-block shadow-2xs pointer-events-none">Choose File</span>
                            <p class="text-[10px] text-gray-400 font-medium">Supported formats: PDF, PPTX, DOCX, XLSX, MP4, ZIP (Max: 50MB)</p>
                        </div>
                    </div>

                    {{-- 3. Visibility --}}
                    <div class="space-y-2 text-xs">
                        <h4 class="font-heading font-black text-xs text-gray-400 uppercase tracking-wider">3. Student Access</h4>
                        <div class="space-y-2 font-bold text-gray-800">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="visibility" value="all" checked class="text-blue-600"> 
                                <span>All students enrolled in this class</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="visibility" value="selected" class="text-blue-600"> 
                                <span>Selected students only</span>
                            </label>
                        </div>
                    </div>

                    {{-- 4. Publishing --}}
                    <div class="space-y-2 text-xs">
                        <h4 class="font-heading font-black text-xs text-gray-400 uppercase tracking-wider">4. Publishing Status</h4>
                        <div class="space-y-2 font-bold text-gray-800">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="status" value="Published" checked class="text-blue-600"> 
                                <span>Publish immediately (Students can view & download)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="status" value="Draft" class="text-blue-600"> 
                                <span>Save as draft</span>
                            </label>
                        </div>
                    </div>

                    {{-- Footer Buttons --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 text-xs font-extrabold">
                        <button type="button" @click="showUploadModal = false" class="py-2.5 px-5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl transition-colors cursor-pointer">Cancel</button>
                        <button type="submit" class="py-2.5 px-6 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors shadow-sm cursor-pointer hover:shadow-md">Upload & Save Material</button>
                    </div>

                </form>

            </div>
        </div>

        {{-- ── 5. EDIT MATERIAL SIDE DRAWER FORM MODAL ── --}}
        <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex justify-end bg-slate-900/40 backdrop-blur-xs">
            <div @click.away="showEditModal = false" class="w-full max-w-xl bg-white h-full p-6 space-y-6 shadow-2xl overflow-y-auto transform transition-all">
                
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="font-heading font-black text-lg text-gray-900">Edit Learning Material / Notes</h3>
                    <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-xl cursor-pointer">✕</button>
                </div>

                <form :action="'/teacher/learning-materials/' + editingMaterial?.id" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- 1. Basic Information --}}
                    <div class="space-y-3 text-xs">
                        <h4 class="font-heading font-black text-xs text-gray-400 uppercase tracking-wider">1. Basic Information</h4>
                        
                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Title *</label>
                            <input type="text" name="title" required x-model="editingMaterial.name" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="font-extrabold text-gray-900 block mb-1">Material Type *</label>
                                <select name="type" required x-model="editingMaterial.type" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-bold outline-none cursor-pointer">
                                    <option value="Notes">Notes / Handout</option>
                                    <option value="Worksheet">Worksheet / Exercises</option>
                                    <option value="Slides">Presentation Slides</option>
                                    <option value="Document">Document / Reference</option>
                                    <option value="Past Paper">Past Year Exam Paper</option>
                                </select>
                            </div>
                            <div>
                                <label class="font-extrabold text-gray-900 block mb-1">Assign to Class / Subject *</label>
                                <select name="course_id" required x-model="editingMaterial.course_id" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-bold outline-none cursor-pointer">
                                    @foreach($courses as $c)
                                        <option value="{{ $c->id }}">{{ $c->title }} ({{ $c->subject->name ?? 'Subject' }} - {{ $c->level }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Topic / Chapter</label>
                            <input type="text" name="topic" x-model="editingMaterial.topic" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                        </div>

                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Short Description (Optional)</label>
                            <textarea name="description" rows="3" x-model="editingMaterial.description" placeholder="Add instructions or overview for students..." class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium"></textarea>
                        </div>
                    </div>

                    {{-- 2. Replace Attached File --}}
                    <div class="space-y-2 text-xs">
                        <h4 class="font-heading font-black text-xs text-gray-400 uppercase tracking-wider">2. Replace Attached File (Optional)</h4>
                        <div class="p-3 bg-gray-50 rounded-2xl border border-gray-200 space-y-2">
                            <p class="text-[11px] text-gray-500 font-medium">Current file: <strong class="text-gray-900" x-text="editingMaterial?.file_name || 'Attached file'"></strong></p>
                            <input type="file" name="file" class="w-full bg-white border border-gray-200 rounded-xl p-2 text-xs outline-none file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="text-[10px] text-gray-400">Leave blank to keep current uploaded file.</p>
                        </div>
                    </div>

                    {{-- 3. Publishing Status --}}
                    <div class="space-y-2 text-xs">
                        <h4 class="font-heading font-black text-xs text-gray-400 uppercase tracking-wider">3. Publishing Status</h4>
                        <div class="space-y-2 font-bold text-gray-800">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="status" value="Published" x-model="editingMaterial.status" class="text-blue-600"> 
                                <span>Publish immediately (Students can view & download)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="status" value="Draft" x-model="editingMaterial.status" class="text-blue-600"> 
                                <span>Save as draft</span>
                            </label>
                        </div>
                    </div>

                    {{-- Footer Buttons --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 text-xs font-extrabold">
                        <button type="button" @click="showEditModal = false" class="py-2.5 px-5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl transition-colors cursor-pointer">Cancel</button>
                        <button type="submit" class="py-2.5 px-6 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors shadow-sm cursor-pointer hover:shadow-md">Update Material</button>
                    </div>

                </form>

            </div>
        </div>

    </div>
</x-teacher-layout>
