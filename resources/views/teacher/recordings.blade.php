<x-teacher-layout>
    <x-slot name="title">Class Recordings - Teacher Portal</x-slot>

    <div x-data="{
        searchQuery: '',
        selectedClass: 'all',
        selectedSubject: 'all',
        showUploadModal: false,
        showEditModal: false,
        editingRecording: null,
        videoUploadType: 'url',
        editVideoUploadType: 'url',
        recordings: {{ \Illuminate\Support\Js::from($recordings) }},
        get filteredRecordings() {
            return this.recordings.filter(r => {
                const matchesQuery = !this.searchQuery || 
                                     r.title.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                                     (r.topic_name && r.topic_name.toLowerCase().includes(this.searchQuery.toLowerCase()));
                const matchesClass = this.selectedClass === 'all' || (r.course && r.course.level === this.selectedClass);
                const matchesSubject = this.selectedSubject === 'all' || 
                                       (r.subject && r.subject.name === this.selectedSubject) || 
                                       (r.course && r.course.subject && r.course.subject.name === this.selectedSubject);
                return matchesQuery && matchesClass && matchesSubject;
            });
        }
    }" class="space-y-6">

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
                <p class="font-black">Please fix the following issues:</p>
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
                <h1 class="font-heading font-black text-2xl text-gray-900">Class Recordings & Study Vault</h1>
                <p class="text-xs text-gray-500 font-medium mt-0.5">Upload and manage replay videos so your students can catch up anytime.</p>
            </div>

            <button @click="showUploadModal = true" class="py-3 px-5 rounded-2xl bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-extrabold text-xs shadow-md shadow-blue-500/20 transition-all flex items-center justify-center gap-2 cursor-pointer shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>Upload Recording</span>
            </button>
        </div>

        {{-- ── 1. KPI STATS CARDS ── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-md shadow-blue-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Recordings</p>
                    <p class="text-2xl font-black text-gray-900" x-text="filteredRecordings.length"></p>
                    <p class="text-[11px] text-gray-500 font-medium">Available in vault</p>
                </div>
            </div>

            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-md shadow-emerald-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Views</p>
                    <p class="text-2xl font-black text-emerald-700" x-text="filteredRecordings.reduce((sum, r) => sum + (r.views_count || 0), 0)"></p>
                    <p class="text-[11px] text-gray-500 font-medium">Student replays</p>
                </div>
            </div>

            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-purple-600 text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-md shadow-purple-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Assigned Classes</p>
                    <p class="text-2xl font-black text-purple-700">{{ $courses->count() }}</p>
                    <p class="text-[11px] text-gray-500 font-medium">Active courses</p>
                </div>
            </div>

            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-md shadow-amber-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Avg Duration</p>
                    <p class="text-2xl font-black text-gray-900" x-text="filteredRecordings.length ? Math.round(filteredRecordings.reduce((sum, r) => sum + (r.duration_minutes || 0), 0) / filteredRecordings.length) + ' min' : '0 min'"></p>
                    <p class="text-[11px] text-gray-500 font-medium">Per class video</p>
                </div>
            </div>
        </div>

        {{-- ── 2. SEARCH & FILTER BAR ── --}}
        <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="relative">
                    <input type="text" x-model="searchQuery" placeholder="Search recording title or topic..." class="w-full bg-gray-50 border border-gray-200 rounded-2xl pl-9 pr-4 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                <div>
                    <select x-model="selectedClass" class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-3 py-2.5 text-xs font-bold text-gray-700 outline-none cursor-pointer">
                        <option value="all">Class: All Levels</option>
                        @foreach($courses->pluck('level')->unique() as $lvl)
                            <option value="{{ $lvl }}">{{ $lvl }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <select x-model="selectedSubject" class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-3 py-2.5 text-xs font-bold text-gray-700 outline-none cursor-pointer">
                        <option value="all">Subject: All Subjects</option>
                        @foreach($subjects as $s)
                            <option value="{{ $s->name }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- ── 3. RECORDINGS GRID ── --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <template x-for="rec in filteredRecordings" :key="rec.id">
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition-all flex flex-col justify-between group">
                    <div>
                        {{-- Video Thumbnail Area --}}
                        <div class="relative aspect-video bg-slate-900 overflow-hidden">
                            <template x-if="rec.thumbnail">
                                <img :src="rec.thumbnail" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </template>
                            <template x-if="!rec.thumbnail">
                                <div class="w-full h-full bg-linear-to-br from-slate-900 to-indigo-950 flex items-center justify-center p-4">
                                    <div class="text-center">
                                        <div class="w-12 h-12 rounded-full bg-white/10 flex items-center justify-center mx-auto text-white mb-2">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </div>
                                        <p class="text-xs font-bold text-indigo-200" x-text="rec.course?.title || rec.topic_name || 'Class Replay'"></p>
                                    </div>
                                </div>
                            </template>

                            {{-- Duration Pill --}}
                            <div class="absolute bottom-2.5 right-2.5 px-2 py-1 rounded-lg bg-black/70 backdrop-blur-xs text-white text-[10px] font-black flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span x-text="rec.duration_minutes ? `${rec.duration_minutes} mins` : 'No Video'"></span>
                            </div>

                            {{-- Subject Tag --}}
                            <div class="absolute top-2.5 left-2.5 px-2.5 py-1 rounded-lg bg-blue-600 text-white text-[10px] font-black uppercase tracking-wider shadow-sm" x-text="rec.course?.subject?.name || rec.subject?.name || 'Class'"></div>
                        </div>

                        {{-- Card Details --}}
                        <div class="p-5 space-y-2">
                            <h4 class="font-heading font-extrabold text-sm text-gray-900 group-hover:text-blue-600 transition-colors line-clamp-2" x-text="rec.title"></h4>
                            <p class="text-xs text-gray-400 font-medium" x-text="`Topic: ${rec.topic_name || 'General'}`"></p>
                            <template x-if="rec.summary">
                                <p class="text-xs text-gray-600 line-clamp-2 mt-1 leading-relaxed" x-text="rec.summary"></p>
                            </template>
                            <template x-if="!rec.video_url">
                                <span class="inline-block px-2.5 py-0.5 rounded-md bg-amber-50 text-amber-800 text-[10px] font-bold border border-amber-200">No Video Attached</span>
                            </template>
                        </div>
                    </div>

                    {{-- Footer Card Actions --}}
                    <div class="p-5 pt-0 border-t border-gray-50 flex items-center justify-between mt-2">
                        <div class="flex items-center gap-1.5 text-xs text-gray-400 font-bold">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span x-text="`${rec.views_count || 0} views`"></span>
                        </div>

                        <div class="flex items-center gap-2">
                            {{-- View / Watch --}}
                            <a :href="'/student/recordings/' + rec.id" target="_blank" class="w-8 h-8 rounded-xl bg-blue-50 hover:bg-blue-600 text-blue-600 hover:text-white inline-flex items-center justify-center transition-all shadow-2xs" title="Watch in Vault">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
                            </a>

                            {{-- Edit Recording --}}
                            <button type="button" @click="editingRecording = JSON.parse(JSON.stringify(rec)); showEditModal = true;" class="w-8 h-8 rounded-xl bg-amber-50 hover:bg-amber-600 text-amber-600 hover:text-white inline-flex items-center justify-center transition-all shadow-2xs cursor-pointer" title="Edit Recording">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>

                            {{-- Delete Recording --}}
                            <form :action="'/teacher/recordings/' + rec.id" method="POST" onsubmit="return confirm('Are you sure you want to delete this class recording?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-xl bg-red-50 hover:bg-red-600 text-red-500 hover:text-white inline-flex items-center justify-center transition-all cursor-pointer shadow-2xs" title="Delete Recording">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <template x-if="filteredRecordings.length === 0">
            <div class="bg-white rounded-3xl p-12 text-center border border-gray-100 space-y-3">
                <div class="w-16 h-16 rounded-3xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                </div>
                <h4 class="font-heading font-black text-base text-gray-900">No Class Recordings Available</h4>
                <p class="text-xs text-gray-500 max-w-md mx-auto">There are currently no recorded class videos or replays available for your assigned classes. Click below to upload your first recording.</p>
                <button @click="showUploadModal = true" class="py-2.5 px-5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-xl shadow-xs transition-colors cursor-pointer inline-flex items-center gap-1.5 mx-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Upload Recording Now</span>
                </button>
            </div>
        </template>

        {{-- ── 4. UPLOAD RECORDING DRAWER MODAL ── --}}
        <div x-show="showUploadModal" x-cloak class="fixed inset-0 z-50 flex justify-end bg-slate-900/40 backdrop-blur-xs">
            <div @click.away="showUploadModal = false" class="w-full max-w-xl bg-white h-full p-6 space-y-6 shadow-2xl overflow-y-auto transform transition-all">
                
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="font-heading font-black text-lg text-gray-900">Upload New Class Recording</h3>
                    <button @click="showUploadModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-xl cursor-pointer">✕</button>
                </div>

                <form action="{{ route('teacher.recordings.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    {{-- Basic Details --}}
                    <div class="space-y-3 text-xs">
                        <h4 class="font-heading font-black text-xs text-gray-400 uppercase tracking-wider">1. Recording Details</h4>
                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Recording Title *</label>
                            <input type="text" name="title" required placeholder="e.g. SPM Physics: Newton's Laws of Motion Intensive Replay" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="font-extrabold text-gray-900 block mb-1">Topic / Chapter *</label>
                                <input type="text" name="topic_name" required placeholder="e.g. Chapter 2: Forces & Motion" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                            </div>

                            <div>
                                <label class="font-extrabold text-gray-900 block mb-1">Assign to Class *</label>
                                <select name="course_id" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-bold outline-none cursor-pointer">
                                    <option value="">Select class</option>
                                    @foreach($courses as $c)
                                        <option value="{{ $c->id }}">{{ $c->title }} ({{ $c->subject->name ?? 'Subject' }} - {{ $c->level }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Duration (Minutes) *</label>
                            <input type="number" name="duration_minutes" required min="1" max="480" value="45" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs outline-none font-medium">
                        </div>
                    </div>

                    {{-- Video Source --}}
                    <div class="space-y-3 text-xs">
                        <h4 class="font-heading font-black text-xs text-gray-400 uppercase tracking-wider">2. Video Source</h4>
                        <div class="flex items-center gap-2 p-1 bg-gray-100 rounded-xl">
                            <button type="button" @click="videoUploadType = 'url'" :class="videoUploadType === 'url' ? 'bg-white text-blue-600 shadow-2xs font-extrabold' : 'text-gray-600'" class="flex-1 py-1.5 text-center rounded-lg transition-all text-xs cursor-pointer">Video Link (YouTube / Zoom / Vimeo)</button>
                            <button type="button" @click="videoUploadType = 'file'" :class="videoUploadType === 'file' ? 'bg-white text-blue-600 shadow-2xs font-extrabold' : 'text-gray-600'" class="flex-1 py-1.5 text-center rounded-lg transition-all text-xs cursor-pointer">Upload MP4 Video File</button>
                        </div>

                        <div x-show="videoUploadType === 'url'">
                            <label class="font-extrabold text-gray-900 block mb-1">Video Stream URL</label>
                            <input type="url" name="video_url" placeholder="https://www.youtube.com/watch?v=... or https://vimeo.com/..." class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs outline-none">
                        </div>

                        <div x-show="videoUploadType === 'file'">
                            <label class="font-extrabold text-gray-900 block mb-1">Select Video File</label>
                            <input type="file" name="video_file" accept="video/*" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2 text-xs outline-none file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700">
                            <p class="text-[10px] text-gray-400 mt-0.5">MP4, WebM, MOV, AVI (Max: 200MB)</p>
                        </div>
                    </div>

                    {{-- Thumbnail & Summary --}}
                    <div class="space-y-3 text-xs">
                        <h4 class="font-heading font-black text-xs text-gray-400 uppercase tracking-wider">3. Cover & Summary</h4>
                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Cover Thumbnail Image (Optional)</label>
                            <input type="file" name="thumbnail_file" accept="image/*" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2 text-xs outline-none file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700">
                        </div>

                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Lesson Key Points & Summary (Optional)</label>
                            <textarea name="summary" rows="3" placeholder="List key points covered in this recorded class..." class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-xs outline-none font-medium"></textarea>
                        </div>
                    </div>

                    {{-- Footer Buttons --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 text-xs font-extrabold">
                        <button type="button" @click="showUploadModal = false" class="py-2.5 px-5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl transition-colors cursor-pointer">Cancel</button>
                        <button type="submit" class="py-2.5 px-6 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors shadow-sm cursor-pointer hover:shadow-md">Publish Recording</button>
                    </div>

                </form>

            </div>
        </div>

        {{-- ── 5. EDIT RECORDING DRAWER MODAL ── --}}
        <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex justify-end bg-slate-900/40 backdrop-blur-xs">
            <div @click.away="showEditModal = false" class="w-full max-w-xl bg-white h-full p-6 space-y-6 shadow-2xl overflow-y-auto transform transition-all">
                
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="font-heading font-black text-lg text-gray-900">Edit Class Recording</h3>
                    <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-xl cursor-pointer">✕</button>
                </div>

                <form :action="'/teacher/recordings/' + editingRecording?.id" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')

                    {{-- Basic Details --}}
                    <div class="space-y-3 text-xs">
                        <h4 class="font-heading font-black text-xs text-gray-400 uppercase tracking-wider">1. Recording Details</h4>
                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Recording Title *</label>
                            <input type="text" name="title" required x-model="editingRecording.title" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="font-extrabold text-gray-900 block mb-1">Topic / Chapter *</label>
                                <input type="text" name="topic_name" required x-model="editingRecording.topic_name" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                            </div>

                            <div>
                                <label class="font-extrabold text-gray-900 block mb-1">Assign to Class *</label>
                                <select name="course_id" required x-model="editingRecording.course_id" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-bold outline-none cursor-pointer">
                                    @foreach($courses as $c)
                                        <option value="{{ $c->id }}">{{ $c->title }} ({{ $c->subject->name ?? 'Subject' }} - {{ $c->level }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Duration (Minutes) *</label>
                            <input type="number" name="duration_minutes" required min="1" max="480" x-model="editingRecording.duration_minutes" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs outline-none font-medium">
                        </div>
                    </div>

                    {{-- Video Source --}}
                    <div class="space-y-3 text-xs">
                        <h4 class="font-heading font-black text-xs text-gray-400 uppercase tracking-wider">2. Video Source</h4>
                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Video Stream URL</label>
                            <input type="text" name="video_url" x-model="editingRecording.video_url" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs outline-none">
                        </div>

                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Replace Video File (Optional)</label>
                            <input type="file" name="video_file" accept="video/*" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2 text-xs outline-none file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700">
                        </div>
                    </div>

                    {{-- Cover & Summary --}}
                    <div class="space-y-3 text-xs">
                        <h4 class="font-heading font-black text-xs text-gray-400 uppercase tracking-wider">3. Cover & Summary</h4>
                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Replace Cover Thumbnail (Optional)</label>
                            <input type="file" name="thumbnail_file" accept="image/*" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2 text-xs outline-none file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700">
                        </div>

                        <div>
                            <label class="font-extrabold text-gray-900 block mb-1">Lesson Key Points & Summary (Optional)</label>
                            <textarea name="summary" rows="3" x-model="editingRecording.summary" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-xs outline-none font-medium"></textarea>
                        </div>
                    </div>

                    {{-- Footer Buttons --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 text-xs font-extrabold">
                        <button type="button" @click="showEditModal = false" class="py-2.5 px-5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl transition-colors cursor-pointer">Cancel</button>
                        <button type="submit" class="py-2.5 px-6 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors shadow-sm cursor-pointer hover:shadow-md">Update Recording</button>
                    </div>

                </form>

            </div>
        </div>

    </div>
</x-teacher-layout>
