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
                return this.subjects.find(s => s.name === this.activeSubject) || (this.subjects.length > 0 ? this.subjects[0] : { name: 'Subject', teacher: 'Instructor', teachers: ['Instructor'], level: 'SPM', progress: 0, notesCount: 0, practiceCount: 0, videoCount: 0, topics: [], description: '' });
            },
            formatDescription(text) {
                if (!text) return '';

                const escapeHtml = (str) => {
                    return String(str)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                };

                // Helper to format inline syntax: bold, italic, strike, monospace
                const formatInline = (raw) => {
                    let str = escapeHtml(raw);
                    // Monospace: `text`
                    str = str.replace(/`([^`\n]+)`/g, '<code class="px-1.5 py-0.5 rounded bg-gray-100 text-blue-700 font-mono text-xs border border-gray-200/60">$1</code>');
                    // Bold: **text** or *text*
                    str = str.replace(/\*\*([^*\n]+)\*\*/g, '<strong class="font-bold text-gray-900">$1</strong>');
                    str = str.replace(/(?<!\*)\*([^*\n]+)\*(?!\*)/g, '<strong class="font-bold text-gray-900">$1</strong>');
                    // Italic: _text_
                    str = str.replace(/(?<!_)_([^_\n]+)_(?!_)/g, '<em class="italic">$1</em>');
                    // Strikethrough: ~~text~~ or ~text~
                    str = str.replace(/~~([^~\n]+)~~/g, '<del class="line-through text-gray-400">$1</del>');
                    str = str.replace(/(?<!~)~([^~\n]+)~(?!~)/g, '<del class="line-through text-gray-400">$1</del>');
                    return str;
                };

                // Process HTML <table>...</table> blocks before splitting
                let tablePlaceholders = {};
                let tableIdx = 0;

                let processed = text.replace(/<table\b[^>]*>([\s\S]*?)<\/table>/gi, (match, inner) => {
                    // Style <thead>
                    inner = inner.replace(/<thead\b[^>]*>/gi, '<thead class="bg-gray-100 text-gray-800 font-extrabold uppercase text-[11px] border-b border-gray-200">');
                    // Style <tbody>
                    inner = inner.replace(/<tbody\b[^>]*>/gi, '<tbody class="divide-y divide-gray-100 bg-white">');
                    // Style <th>
                    inner = inner.replace(/<th\b[^>]*>([\s\S]*?)<\/th>/gi, (m, c) => {
                        return `<th class="py-2.5 px-3.5 border-b border-gray-200 font-extrabold">${c.trim()}</th>`;
                    });
                    // Style <td>
                    inner = inner.replace(/<td\b[^>]*>([\s\S]*?)<\/td>/gi, (m, c) => {
                        return `<td class="py-2.5 px-3.5 border-b border-gray-100 text-gray-700 font-medium">${formatInline(c.trim())}</td>`;
                    });
                    // Style <tr>
                    inner = inner.replace(/<tr\b[^>]*>([\s\S]*?)<\/tr>/gi, (m, c) => {
                        return `<tr class="hover:bg-gray-50/60 transition">${c}</tr>`;
                    });

                    let tableBody = inner;
                    if (!/<tbody\b/i.test(inner)) {
                        tableBody = `<tbody class="divide-y divide-gray-100 bg-white">${inner}</tbody>`;
                    }

                    let styledTable = `<div class="overflow-x-auto my-3"><table class="w-full text-xs text-left border border-gray-200 rounded-xl overflow-hidden shadow-2xs">${tableBody}</table></div>`;
                    let key = `###TABLE_PLACEHOLDER_${tableIdx}###`;
                    tablePlaceholders[key] = styledTable;
                    tableIdx++;
                    return `\n\n${key}\n\n`;
                });

                let cleanText = processed.replace(/\r\n/g, '\n').replace(/\r/g, '\n').trim();
                let blocks = cleanText.split(/\n{2,}/);

                let htmlBlocks = blocks.map(block => {
                    let trimmed = block.trim();
                    if (!trimmed) return '';

                    if (tablePlaceholders[trimmed]) {
                        return tablePlaceholders[trimmed];
                    }

                    let lines = trimmed.split('\n').map(l => l.trim()).filter(l => l.length > 0);
                    if (lines.length === 0) return '';

                    // 1. Tab-delimited table (e.g., copied from Excel/Word with tabs)
                    let isTabTable = lines.length > 1 && lines.every(l => l.includes('\t'));
                    if (isTabTable) {
                        let tableHtml = '<div class="overflow-x-auto my-3"><table class="w-full text-xs text-left border border-gray-200 rounded-xl overflow-hidden shadow-2xs">';
                        lines.forEach((line, idx) => {
                            let cols = line.split('\t').map(c => c.trim());
                            if (idx === 0) {
                                tableHtml += '<thead class="bg-gray-100 text-gray-800 font-extrabold uppercase text-[11px] border-b border-gray-200"><tr>';
                                cols.forEach(c => tableHtml += `<th class="py-2.5 px-3.5">${formatInline(c)}</th>`);
                                tableHtml += '</tr></thead><tbody class="divide-y divide-gray-100 bg-white">';
                            } else {
                                tableHtml += '<tr class="hover:bg-gray-50/60 transition">';
                                cols.forEach((c, cIdx) => {
                                    let cellClass = cIdx === 0 ? 'font-bold text-gray-900' : 'text-gray-600 font-medium';
                                    tableHtml += `<td class="py-2.5 px-3.5 ${cellClass}">${formatInline(c)}</td>`;
                                });
                                tableHtml += '</tr>';
                            }
                        });
                        tableHtml += '</tbody></table></div>';
                        return tableHtml;
                    }

                    // 2. Section Heading (single line < 60 chars not ending with punctuation or list)
                    if (lines.length === 1 && lines[0].length < 60 && !['.', ',', ';', ':'].includes(lines[0].slice(-1)) && !/^[-•*\d]/.test(lines[0])) {
                        return `<h4 class="font-heading font-black text-gray-900 text-sm sm:text-base pt-2 mb-1">${formatInline(lines[0])}</h4>`;
                    }

                    // 3. Regular block (paragraphs, bullet lists, numbered lists)
                    let blockHtml = '<div class="space-y-1.5 text-sm text-gray-600 leading-relaxed font-normal">';
                    lines.forEach(l => {
                        let bulletMatch = l.match(/^[-•*]\s+(.*)$/);
                        let numMatch = l.match(/^(\d+)\.\s+(.*)$/);
                        if (bulletMatch) {
                            blockHtml += `<div class="flex items-start gap-2 ml-2 text-gray-700 font-medium"><span class="text-blue-600 font-bold shrink-0">•</span><span>${formatInline(bulletMatch[1])}</span></div>`;
                        } else if (numMatch) {
                            blockHtml += `<div class="flex items-start gap-2 ml-2 text-gray-700 font-medium"><span class="font-bold text-gray-900 shrink-0">${numMatch[1]}.</span><span>${formatInline(numMatch[2])}</span></div>`;
                        } else {
                            blockHtml += `<p>${formatInline(l)}</p>`;
                        }
                    });
                    blockHtml += '</div>';
                    return blockHtml;
                });

                return htmlBlocks.join('');
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
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="font-heading font-black text-2xl text-white tracking-tight" x-text="activeSubjectData.name"></h2>
                        </div>
                        <p class="text-xs text-gray-300 font-medium mt-0.5" x-text="`${(activeSubjectData.teachers && activeSubjectData.teachers.length > 1) ? 'Teachers: ' + activeSubjectData.teachers.join(', ') : 'Teacher: ' + (activeSubjectData.teacher || 'Instructor')} • KPM ${activeSubjectData.stage || 'Curriculum'} (${activeSubjectData.form_year || 'Standard'}) • ${activeSubjectData.category || 'Core'}${activeSubjectData.stream_package ? ' • ' + activeSubjectData.stream_package : ''}`"></p>
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
            <div x-show="activeTab === 'overview'" class="p-6 sm:p-8 space-y-6">
                
                {{-- Subject Explanation & Curriculum Scope --}}
                <div class="space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <h3 class="font-heading font-black text-gray-900 text-lg sm:text-xl" x-text="activeSubjectData.name"></h3>
                        <div class="flex items-center gap-2 text-xs font-bold text-gray-500">
                            <span x-show="activeSubjectData.stage" x-text="activeSubjectData.stage"></span>
                            <span x-show="activeSubjectData.stage && activeSubjectData.form_year">•</span>
                            <span x-show="activeSubjectData.form_year" x-text="activeSubjectData.form_year"></span>
                            <span x-show="activeSubjectData.category">•</span>
                            <span x-show="activeSubjectData.category" class="text-blue-700 bg-blue-50 px-2.5 py-0.5 rounded-full border border-blue-100 font-extrabold" x-text="activeSubjectData.category"></span>
                        </div>
                    </div>

                    <div class="space-y-3" 
                         x-html="formatDescription(activeSubjectData.description) || ('<p class=\'text-sm text-gray-600 leading-relaxed font-normal\'>Curriculum syllabus for ' + activeSubjectData.name + ' aligned with national curriculum standards, focused on core subject competencies and structured learning modules.</p>')">
                    </div>
                </div>

                {{-- Subject Instructor & Resource Overview --}}
                <div x-show="activeSubjectData.teacher || (activeSubjectData.teachers && activeSubjectData.teachers.length > 0)" class="pt-4 border-t border-gray-100 flex flex-wrap items-start justify-between gap-3 text-xs">
                    <div>
                        <span class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider block mb-1"
                              x-text="(activeSubjectData.teachers && activeSubjectData.teachers.length > 1) ? 'Assigned Instructors' : 'Assigned Instructor'">
                        </span>
                        
                        {{-- Single Teacher --}}
                        <template x-if="!activeSubjectData.teachers || activeSubjectData.teachers.length <= 1">
                            <span class="font-bold text-gray-800" x-text="activeSubjectData.teacher || (activeSubjectData.teachers && activeSubjectData.teachers[0]) || 'Instructor'"></span>
                        </template>

                        {{-- Multiple Teachers as Bullet Point List --}}
                        <template x-if="activeSubjectData.teachers && activeSubjectData.teachers.length > 1">
                            <ul class="space-y-1 mt-1">
                                <template x-for="tName in activeSubjectData.teachers" :key="tName">
                                    <li class="flex items-center gap-2 font-bold text-gray-800 text-xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600 shrink-0"></span>
                                        <span x-text="tName"></span>
                                    </li>
                                </template>
                            </ul>
                        </template>
                    </div>
                    <div class="flex items-center gap-4 text-gray-500 font-semibold self-start sm:self-center">
                        <span x-show="activeSubjectData.notesCount !== undefined" x-text="`${activeSubjectData.notesCount} Study Material${activeSubjectData.notesCount === 1 ? '' : 's'}`"></span>
                        <span x-show="activeSubjectData.practiceCount !== undefined">•</span>
                        <span x-show="activeSubjectData.practiceCount !== undefined" x-text="`${activeSubjectData.practiceCount} Practice Drill${activeSubjectData.practiceCount === 1 ? '' : 's'}`"></span>
                        <span x-show="activeSubjectData.videoCount !== undefined">•</span>
                        <span x-show="activeSubjectData.videoCount !== undefined" x-text="`${activeSubjectData.videoCount} Class Video${activeSubjectData.videoCount === 1 ? '' : 's'}`"></span>
                    </div>
                </div>

                {{-- Simple Progress Bar at the Bottom --}}
                <div class="pt-4 border-t border-gray-100 space-y-2">
                    <div class="flex justify-between items-center text-xs font-bold">
                        <span class="text-gray-600 uppercase tracking-wider text-[11px] font-extrabold">Subject Progress</span>
                        <span class="text-blue-600 font-black text-sm" x-text="`${activeSubjectData.progress || 0}%`"></span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                        <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-500" :style="`width: ${activeSubjectData.progress || 0}%`"></div>
                    </div>
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
