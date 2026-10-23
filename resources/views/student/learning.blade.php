<x-dashboard-layout>
    <x-slot name="title">My Learning</x-slot>
    <x-slot name="sidebar">
        @include('student.partials.sidebar')
    </x-slot>

    <script id="learning-subjects-data" type="application/json">
        {!! json_encode($subjectsData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
    </script>

    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('learningWorkspace', () => ({
            subjects: JSON.parse(document.getElementById('learning-subjects-data').textContent || '[]'),
            activeSubject: '{{ $subjectsData[0]['name'] ?? 'Mathematics' }}', 
            activeTab: 'overview',
            get activeSubjectData() {
                return this.subjects.find(s => s.name === this.activeSubject) || (this.subjects.length > 0 ? this.subjects[0] : { name: 'Subject', teacher: 'Instructor', level: 'SPM', progress: 0, notesCount: 0, practiceCount: 0, videoCount: 0, topics: [] });
            }
        }));
    });
    </script>

    <div x-data="learningWorkspace()" class="space-y-6">



        {{-- ── 2. SUBJECT DROPDOWN SELECTOR BAR (BEFORE DETAIL CARD) ── --}}
        <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center justify-between gap-4">
            <div class="flex items-center gap-3.5 flex-1">
                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <div class="flex-1 max-w-md">
                    <label for="subject-select" class="block text-[10px] font-extrabold text-gray-400 uppercase tracking-wider mb-1">Select Enrolled Subject Workspace</label>
                    <div class="relative">
                        <select id="subject-select" 
                                x-model="activeSubject" 
                                @change="activeTab = 'overview'"
                                class="bg-gray-50 !bg-none border border-gray-200 text-gray-900 font-heading font-extrabold text-sm sm:text-base rounded-2xl px-4 py-2.5 pr-10 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none cursor-pointer appearance-none w-full shadow-2xs transition-all">
                            <template x-for="subj in subjects" :key="subj.id">
                                <option :value="subj.name" x-text="subj.name"></option>
                            </template>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── 3. ACTIVE SUBJECT DETAIL WORKSPACE CARD ── --}}
        <div id="active-subject-workspace" class="bg-white rounded-3xl border border-gray-100 shadow-md overflow-hidden space-y-0">
            
            {{-- Subject Header Bar --}}
            <div class="bg-gradient-to-r from-primary-950 via-primary-900 to-navy text-white p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 text-white flex items-center justify-center font-heading font-black text-xl shrink-0 shadow-sm">
                        <span x-text="activeSubjectData.name.substr(0, 1)"></span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="font-heading font-black text-2xl text-white tracking-tight" x-text="activeSubjectData.name"></h2>
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[10px] font-black uppercase">Active Workspace</span>
                        </div>
                        <p class="text-xs text-gray-300 font-medium mt-0.5" x-text="`Teacher: ${activeSubjectData.teacher} • KPM ${activeSubjectData.stage || 'Curriculum'} (${activeSubjectData.form_year || 'Standard'}) • ${activeSubjectData.category || 'Core'}${activeSubjectData.stream_package ? ' • ' + activeSubjectData.stream_package : ''}`"></p>
                    </div>
                </div>
            </div>

            {{-- Segmented Tab Navigation Bar --}}
            <div class="bg-gray-100/90 p-2.5 border-b border-gray-200/80">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs font-black">
                    
                    {{-- Overview Tab --}}
                    <button @click="activeTab = 'overview'" 
                            :class="activeTab === 'overview' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-50 shadow-2xs'"
                            class="py-3 px-4 rounded-2xl transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span>Overview</span>
                    </button>

                    {{-- Lessons & Notes Tab --}}
                    <button @click="activeTab = 'lessons'" 
                            :class="activeTab === 'lessons' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-50 shadow-2xs'"
                            class="py-3 px-4 rounded-2xl transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        <span>Lessons & Notes</span>
                        <span :class="activeTab === 'lessons' ? 'bg-blue-700 text-white' : 'bg-blue-50 text-blue-700'" class="px-2 py-0.5 rounded-full text-[10px] font-black" x-text="activeSubjectData.notesCount"></span>
                    </button>

                    {{-- Practice Modules Tab --}}
                    <button @click="activeTab = 'practice'" 
                            :class="activeTab === 'practice' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-50 shadow-2xs'"
                            class="py-3 px-4 rounded-2xl transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        <span>Practice Drills</span>
                        <span :class="activeTab === 'practice' ? 'bg-blue-700 text-white' : 'bg-amber-50 text-amber-700'" class="px-2 py-0.5 rounded-full text-[10px] font-black" x-text="activeSubjectData.practiceCount"></span>
                    </button>

                    {{-- Recorded Videos Tab --}}
                    <button @click="activeTab = 'recordings'" 
                            :class="activeTab === 'recordings' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-50 shadow-2xs'"
                            class="py-3 px-4 rounded-2xl transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.882v6.236a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <span>Recorded Classes</span>
                        <span :class="activeTab === 'recordings' ? 'bg-blue-700 text-white' : 'bg-purple-50 text-purple-700'" class="px-2 py-0.5 rounded-full text-[10px] font-black" x-text="activeSubjectData.videoCount"></span>
                    </button>

                </div>
            </div>

            {{-- Workspace Tab Content Panels --}}

            {{-- 1. OVERVIEW PANEL --}}
            <div x-show="activeTab === 'overview'" class="p-6 space-y-8">
                
                <template x-for="(topic, index) in activeSubjectData.topics" :key="index">
                    <div class="space-y-6 pb-6 border-b border-gray-100 last:border-0 last:pb-0">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="p-5 bg-blue-50/60 rounded-2xl border border-blue-100">
                                <span class="text-[10px] font-extrabold text-blue-600 uppercase tracking-wider block">Topic</span>
                                <h4 class="font-heading font-black text-gray-900 text-lg" x-text="topic.name"></h4>
                            </div>

                            <div class="p-5 bg-gray-50 rounded-2xl border border-gray-100">
                                <div class="flex justify-between items-center text-xs font-bold mb-1.5">
                                    <span class="text-gray-500">Topic Completion</span>
                                    <span class="text-blue-600 font-black text-sm" x-text="`${topic.progress}%`"></span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" :style="`width: ${topic.progress}%`"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Topic Mastery --}}
                        <div class="space-y-3">
                            <h4 class="font-heading font-extrabold text-sm text-gray-900">Topic Progress Breakdown</h4>
                            <div class="space-y-2.5">
                                <template x-for="(breakdown, bIndex) in topic.breakdowns" :key="bIndex">
                                    <div class="p-3 bg-gray-50/80 rounded-2xl border border-gray-100 flex items-center justify-between gap-3">
                                        <span class="text-xs font-bold text-gray-900" x-text="breakdown.name"></span>
                                        <span :class="breakdown.badgeClass" class="px-3 py-1 rounded-xl font-extrabold text-xs border shrink-0" x-text="breakdown.status"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Teacher Feedback --}}
                <div class="p-5 bg-blue-50/80 rounded-2xl border border-blue-100 space-y-1">
                    <span class="text-[10px] font-black text-blue-800 uppercase tracking-wider block">Teacher Recommendation</span>
                    <p class="text-xs text-blue-950 italic leading-relaxed">
                        "Great consistency in reviewing notes! Keep practicing structured questions before next class."
                    </p>
                    <p class="text-right text-xs font-extrabold text-blue-800" x-text="`— ${activeSubjectData.teacher}`"></p>
                </div>
            </div>

            {{-- 2. LESSONS & NOTES PANEL --}}
            <div x-show="activeTab === 'lessons'" x-cloak class="p-6 space-y-3">
                @php
                    $renderedCount = 0;
                @endphp
                @foreach($allMaterials ?? [] as $mat)
                    @php
                        $subjName = $mat->course->subject->display_name ?? $mat->course->subject->name ?? 'General';
                        $ext = strtoupper($mat->file_type ?: 'PDF');
                    @endphp
                    <div x-show="activeSubject === '{{ $subjName }}'" class="p-4 bg-gray-50 rounded-2xl border border-gray-100 flex items-center justify-between gap-4 hover:bg-gray-100/70 transition-all">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div>
                                <h4 class="font-heading font-extrabold text-sm text-gray-900">{{ $mat->title }}</h4>
                                <p class="text-xs text-gray-500">
                                    {{ $mat->type }} • {{ $mat->formatted_size }} • Topic: {{ $mat->topic ?: 'General' }} • Teacher: {{ $mat->teacher->user->name ?? 'Teacher' }}
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('materials.download', $mat->id) }}" target="_blank" class="py-2 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs transition-colors shrink-0 inline-flex items-center gap-1.5 shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Download {{ $ext }}</span>
                        </a>
                    </div>
                    @php $renderedCount++; @endphp
                @endforeach

                {{-- Default sample lesson note if no uploads yet for the subject --}}
                <div x-show="activeSubjectData.notesCount === 0 || {{ count($allMaterials ?? []) === 0 ? 'true' : 'false' }}" class="p-4 bg-gray-50 rounded-2xl border border-gray-100 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <h4 class="font-heading font-extrabold text-sm text-gray-900" x-text="`${activeSubjectData.name} — Comprehensive Study Guide & Formula Notes`"></h4>
                            <p class="text-xs text-gray-500">Official Curriculum Notes • Downloadable PDF</p>
                        </div>
                    </div>
                    <span class="py-2 px-4 rounded-xl bg-gray-200 text-gray-600 font-extrabold text-xs">Standard Notes</span>
                </div>
            </div>

            {{-- 3. PRACTICE DRILLS PANEL --}}
            <div x-show="activeTab === 'practice'" x-cloak class="p-6 space-y-3">
                @foreach($allQuizzes ?? [] as $qz)
                    @if($qz->questions->count() > 0)
                        @php
                            $subjName = $qz->course->subject->display_name ?? $qz->course->subject->name ?? 'General';
                        @endphp
                        <div x-show="activeSubject === '{{ $subjName }}'" class="p-4 bg-amber-50/60 rounded-2xl border border-amber-100 flex items-center justify-between gap-4">
                            <div>
                                <h4 class="font-heading font-extrabold text-sm text-gray-900">{{ $qz->title }}</h4>
                                <p class="text-xs text-amber-900 font-medium">
                                    {{ $qz->questions->count() }} Questions • {{ $qz->questions->count() }} Minutes
                                </p>
                            </div>
                            <a href="{{ route('student.quiz.show', $qz->id) }}" class="py-2 px-4 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-xs transition-colors shrink-0 inline-block">Start Drill</a>
                        </div>
                    @endif
                @endforeach

                {{-- Empty state if no drills are assigned to the selected subject workspace --}}
                <div x-show="activeSubjectData.practiceCount === 0" class="p-8 text-center bg-gray-50 rounded-2xl border border-gray-100">
                    <p class="font-heading font-black text-sm text-gray-700">No practice drills available yet.</p>
                    <p class="text-xs text-gray-500">Check back later for new updates from your teacher.</p>
                </div>
            </div>

            {{-- 4. RECORDED CLASSES PANEL --}}
            <div x-show="activeTab === 'recordings'" x-cloak class="p-6 space-y-3">
                @forelse($recordings as $rec)
                    @php
                        $recSubjId = $rec->subject_id;
                        $recCourseId = $rec->course_id;
                        $recSubjName = $rec->subject->display_name ?? $rec->subject->name ?? '';
                        $isMp4 = str_contains(strtolower($rec->video_url), '.mp4') || str_contains($rec->video_url, 'storage/recordings');
                    @endphp
                    <div x-show="activeSubjectData.subject_id == {{ (int) $recSubjId }} || (activeSubjectData.course_id && activeSubjectData.course_id == {{ (int) ($recCourseId ?? 0) }}) || activeSubject === @js($recSubjName)" 
                         class="p-4 bg-gray-50 hover:bg-blue-50/40 rounded-2xl border border-gray-100 hover:border-blue-200 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4 group">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm group-hover:scale-105 transition-transform">
                                <svg class="w-5 h-5 fill-current ml-0.5" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap mb-0.5">
                                    <h4 class="font-heading font-extrabold text-sm text-gray-900 group-hover:text-blue-600 transition-colors">{{ $rec->title }}</h4>
                                    @if($rec->topic_name)
                                        <span class="px-2 py-0.5 rounded-md bg-white border border-gray-200 text-gray-600 font-bold text-[10px]">{{ $rec->topic_name }}</span>
                                    @endif
                                    @if($isMp4)
                                        <span class="px-1.5 py-0.5 rounded bg-purple-50 text-purple-700 font-bold text-[9px] uppercase tracking-wider">MP4 Video</span>
                                    @else
                                        <span class="px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 font-bold text-[9px] uppercase tracking-wider">Web Link</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500">
                                    Uploaded {{ $rec->created_at ? $rec->created_at->format('d M Y') : 'Recently' }}
                                    @if($rec->summary)
                                        • <span class="text-gray-400">{{ Str::limit($rec->summary, 80) }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('student.recordings.show', $rec->id) }}" class="py-2 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs transition-colors shrink-0 inline-flex items-center gap-1.5 shadow-2xs">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            <span>Play Video</span>
                        </a>
                    </div>
                @empty
                @endforelse

                {{-- Empty state when no videos for the selected subject --}}
                <div x-show="activeSubjectData.videoCount === 0" class="p-8 text-center bg-gray-50 rounded-2xl border border-gray-100">
                    <div class="w-12 h-12 rounded-2xl bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.882v6.236a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    </div>
                    <p class="font-heading font-black text-sm text-gray-700">No recorded classes uploaded yet for this subject.</p>
                    <p class="text-xs text-gray-400 mt-0.5">When new recorded sessions or replays are uploaded, they will appear here.</p>
                </div>
            </div>

        </div>

    </div>
</x-dashboard-layout>
