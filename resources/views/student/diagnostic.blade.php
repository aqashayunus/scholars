<x-dashboard-layout>
    <x-slot name="title">{{ $assignment->title ?? 'Academic Diagnostic Assessment' }}</x-slot>
    <x-slot name="sidebar">
        @include('student.partials.sidebar')
    </x-slot>

    {{-- Mozilla PDF.js library with automatic CDN fallback for cPanel / production environments --}}
    <script src="{{ asset('vendor/pdfjs/pdf.min.js') }}"></script>
    <script>
        if (typeof window.pdfjsLib === 'undefined') {
            document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"><\/script>');
        }
    </script>

    <div x-data="diagnosticQuizApp()" x-init="initApp()" class="max-w-[1700px] mx-auto space-y-4 pb-16 text-slate-800">

        {{-- ── 1. TOP HEADER & METADATA BAR (Matching Screenshot) ── --}}
        <div class="bg-white rounded-2xl px-6 py-4 border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            {{-- Left: Brand & Assessment Title --}}
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-xs shrink-0">
                    {{-- Graduation Cap Logo --}}
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2-1.09V17h2V9L12 3zm6.82 6L12 12.72 5.18 9 12 5.28 18.82 9zM17 15.99l-5 2.73-5-2.73v-3.72l5 2.73 5-2.73v3.72z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="font-heading font-black text-lg sm:text-xl text-slate-900 leading-tight">Olympian Scholars</h1>
                    <p class="text-xs text-slate-500 font-semibold">{{ $assignment->title ?? 'Academic Diagnostic Assessment' }}</p>
                </div>
            </div>

            {{-- Right: Countdown Timer --}}
            <div class="flex items-center gap-2.5">
                {{-- Countdown Timer --}}
                @if(!$isCompleted)
                <div class="px-3.5 py-1.5 rounded-xl border font-mono font-black text-xs flex items-center gap-1.5 transition-colors"
                     :class="timerSeconds < 300 ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-slate-50 border-slate-200 text-slate-700'">
                    <svg class="w-3.5 h-3.5 text-amber-500 animate-pulse" :class="timerSeconds < 300 ? 'text-rose-600' : 'text-amber-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span x-text="formatTimer()"></span>
                </div>
                @endif
            </div>
        </div>

        {{-- ── 2. COMPLETED SUMMARY CARD (If already submitted) ── --}}
        @if($isCompleted)
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm text-center max-w-2xl mx-auto space-y-4">
            <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 mx-auto flex items-center justify-center font-black text-2xl shadow-sm">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Assessment Completed
                </span>
                <h2 class="font-heading font-black text-2xl text-slate-900 mt-2">Your Diagnostic Test Results</h2>
                <p class="text-xs text-slate-500 font-medium">Your answers have been submitted and automatically graded.</p>
            </div>

            <div class="p-5 bg-blue-50/50 rounded-2xl border border-blue-100 flex items-center justify-around">
                <div>
                    <span class="text-[11px] font-bold text-blue-700 uppercase tracking-wider block">Your Score</span>
                    <span class="text-3xl font-black text-blue-900">{{ $studentAssignment->score ?? 0 }}%</span>
                </div>
                <div class="h-10 w-px bg-blue-200"></div>
                <div>
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Date Completed</span>
                    <span class="text-sm font-bold text-slate-800">{{ $studentAssignment->submitted_at ? $studentAssignment->submitted_at->format('d M Y, h:i A') : 'Recent' }}</span>
                </div>
            </div>

            <div class="pt-2">
                <a href="{{ route('student.tasks') }}" class="inline-flex items-center gap-2 py-3 px-6 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs shadow-md transition-all">
                    <span>Back to Tasks</span>
                    <span>→</span>
                </a>
            </div>
        </div>
        @endif

        {{-- ── 3. MAIN WORKSPACE (SIDE BY SIDE: ASSESSMENT MODULE + ANSWER SHEET) ── --}}
        <div x-show="!isExamFinished" class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

            {{-- ── LEFT PANEL: ASSESSMENT MODULE (PAGE ONLY VIEWER) (7 COLS) ── --}}
            <div id="assessmentModuleContainer" class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col h-[74vh] sm:h-[80vh] lg:h-[calc(100vh-170px)] min-h-[460px] sm:min-h-[580px] lg:min-h-[680px] sticky top-4">
                
                {{-- Module Header Bar --}}
                <div class="px-4 sm:px-5 py-3 bg-white border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 shrink-0">
                    {{-- Left Title --}}
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center shrink-0 shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <h2 class="font-heading font-black text-sm text-slate-900 leading-tight">Assessment Module</h2>
                            <p class="text-[11px] text-slate-400 font-medium truncate max-w-[200px] sm:max-w-none">
                                {{ $assignment->topic ?? 'Bahasa Melayu' }} • {{ $assignment->target_level ?? 'Year 4' }} • Diagnostic Assessment
                            </p>
                        </div>
                    </div>

                    {{-- Middle: Page Navigation Buttons `< Page X of Y >` --}}
                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button"
                                @click="prevPage()"
                                :disabled="currentPage <= 1 || isRenderingPage"
                                class="w-8 h-8 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 disabled:opacity-30 disabled:pointer-events-none transition flex items-center justify-center cursor-pointer shadow-2xs font-bold"
                                title="Previous Page (or Left Arrow key)">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                        </button>

                        <div class="px-3 py-1 rounded-xl bg-slate-50 border border-slate-200/70 text-xs font-bold text-slate-700 font-mono shadow-2xs">
                            <span>Page <strong class="text-blue-600 font-black" x-text="currentPage"></strong> of <span x-text="totalPages || 1"></span></span>
                        </div>

                        <button type="button"
                                @click="nextPage()"
                                :disabled="currentPage >= totalPages || isRenderingPage"
                                class="w-8 h-8 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 disabled:opacity-30 disabled:pointer-events-none transition flex items-center justify-center cursor-pointer shadow-2xs font-bold"
                                title="Next Page (or Right Arrow key)">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>

                    {{-- Right: Fit Mode, Zoom & Fullscreen --}}
                    <div class="flex items-center gap-1.5 shrink-0">
                        {{-- Fit Mode Toggle: Page (No slider) vs Width --}}
                        <button type="button"
                                @click="toggleFitMode()"
                                :class="fitMode === 'page' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                                class="px-2.5 py-1.5 rounded-xl border text-[11px] font-bold transition flex items-center gap-1 cursor-pointer shadow-2xs"
                                :title="fitMode === 'page' ? 'Currently: Whole page fitted (no slider). Click for Fit Width.' : 'Currently: Fit Width. Click for Whole Page.'">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                            <span x-text="fitMode === 'page' ? 'Fit Page' : 'Fit Width'"></span>
                        </button>

                        {{-- Zoom Controls --}}
                        <div class="flex items-center bg-slate-50 border border-slate-200 rounded-xl p-0.5 text-xs">
                            <button type="button" @click="zoomOut()" :disabled="pageZoom <= 0.75" class="w-6 h-6 rounded-lg hover:bg-white text-slate-600 font-bold transition flex items-center justify-center cursor-pointer disabled:opacity-30">−</button>
                            <span class="px-1 font-mono font-bold text-[11px] text-slate-700 min-w-[36px] text-center" x-text="`${Math.round(pageZoom * 100)}%`"></span>
                            <button type="button" @click="zoomIn()" :disabled="pageZoom >= 1.75" class="w-6 h-6 rounded-lg hover:bg-white text-slate-600 font-bold transition flex items-center justify-center cursor-pointer disabled:opacity-30">+</button>
                        </div>

                        {{-- Fullscreen Toggle --}}
                        <button type="button"
                                @click="toggleFullscreen()"
                                class="w-8 h-8 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 transition flex items-center justify-center cursor-pointer shadow-2xs"
                                title="Toggle Fullscreen View">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Center Viewport: Page-by-Page Canvas Viewport with Touch Slide for Mobile --}}
                <div id="page-canvas-wrapper"
                     @touchstart="handleTouchStart($event)"
                     @touchmove="handleTouchMove($event)"
                     @touchend="handleTouchEnd($event)"
                     class="flex-1 w-full bg-[#eef2f6] relative p-2.5 sm:p-4 flex flex-col items-center justify-center select-none touch-pan-y overflow-hidden"
                     :class="fitMode === 'page' ? 'overflow-hidden' : 'overflow-y-auto admin-scrollbar'">

                    {{-- White Paper Page Card with Drop Shadow (Only shown when PDF is loaded) --}}
                    <div x-show="hasPdfLoaded"
                         id="pdf-canvas-card"
                         class="relative bg-white shadow-xl rounded-xs border border-slate-300/80 flex items-center justify-center m-auto will-change-transform"
                         :style="isSwiping ? `transform: translateX(${touchDeltaX}px); transition: none;` : ''"
                         style="display: none;">
                        <canvas id="pdf-single-page-canvas" class="block max-w-none pointer-events-none select-none"></canvas>
                    </div>

                    {{-- Mobile Floating Previous Slide Button --}}
                    <button type="button"
                            x-show="currentPage > 1 && hasPdfLoaded"
                            @click.stop="prevPage()"
                            class="sm:hidden absolute left-2 top-1/2 -translate-y-1/2 z-30 w-10 h-10 rounded-full bg-slate-900/60 hover:bg-slate-900/80 backdrop-blur-md text-white shadow-xl flex items-center justify-center active:scale-90 transition cursor-pointer"
                            aria-label="Previous Page">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </button>

                    {{-- Mobile Floating Next Slide Button --}}
                    <button type="button"
                            x-show="currentPage < totalPages && hasPdfLoaded"
                            @click.stop="nextPage()"
                            class="sm:hidden absolute right-2 top-1/2 -translate-y-1/2 z-30 w-10 h-10 rounded-full bg-slate-900/60 hover:bg-slate-900/80 backdrop-blur-md text-white shadow-xl flex items-center justify-center active:scale-90 transition cursor-pointer"
                            aria-label="Next Page">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </button>

                    {{-- Mobile Slide & Page Indicator Pill --}}
                    <div x-show="hasPdfLoaded && totalPages > 1"
                         class="sm:hidden absolute bottom-3 z-30 px-3 py-1 rounded-full bg-slate-900/75 backdrop-blur-md text-white text-[11px] font-bold shadow-md flex items-center gap-1.5 pointer-events-none select-none">
                        <svg class="w-3.5 h-3.5 text-blue-300 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8m0 0l-4-4m4 4l-4 4m-4 5H4m0 0l4-4m-4 4l4 4"/></svg>
                        <span>Swipe to slide</span>
                        <span class="text-blue-300 font-mono ml-0.5" x-text="`(${currentPage}/${totalPages})`"></span>
                    </div>

                    {{-- Loading Spinner Overlay --}}
                    <div x-show="isLoadingPdf || isRenderingPage"
                         class="absolute inset-0 bg-[#eef2f6]/75 backdrop-blur-xs flex flex-col items-center justify-center gap-2 z-20"
                         style="display: none;">
                        <svg class="w-8 h-8 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span class="text-xs font-bold text-slate-700" x-text="isLoadingPdf ? 'Loading assessment document...' : `Rendering Page ${currentPage}...`"></span>
                    </div>

                    {{-- Fallback Error / Missing File Display --}}
                    <div x-show="pdfError || !{{ $hasPdfFile ? 'true' : 'false' }}"
                         class="p-6 sm:p-8 text-center space-y-4 bg-white rounded-3xl border border-rose-200 shadow-sm m-auto max-w-md"
                         style="display: none;">
                        <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 mx-auto flex items-center justify-center font-bold text-2xl shadow-xs">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-heading font-black text-base text-slate-900">Document Could Not Be Loaded</h3>
                            <p class="text-xs text-slate-500 mt-1" x-text="pdfErrorMessage || 'The assessment PDF file could not be found or loaded from the server.'"></p>
                        </div>
                        @if(!empty($expectedFilePath))
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-left text-[11px] font-mono text-slate-600 break-all space-y-1">
                            <p class="font-bold text-slate-800">Target File on Server:</p>
                            <p>{{ $expectedFilePath }}</p>
                            <p class="text-[10px] text-slate-400 font-sans mt-1">Please ensure this file is uploaded on cPanel to <code>storage/app/public/{{ $expectedFilePath }}</code>.</p>
                        </div>
                        @endif
                        <div class="pt-1 flex items-center justify-center gap-2">
                            <button type="button" @click="loadPdf()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                                Retry Loading
                            </button>
                            @if(!empty($pdfUrl))
                            <a href="{{ $pdfUrl }}" target="_blank" class="px-4 py-2 border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-bold transition">
                                Open Direct Link ↗
                            </a>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Bottom Strip: Page Jump Overview --}}
                <div x-show="showPageOverview && totalPages > 1" x-transition class="border-t border-slate-100 bg-white px-4 py-2 shrink-0 flex items-center justify-between gap-2">
                    <div class="flex items-center gap-1.5 text-xs text-slate-500 font-bold shrink-0">
                        <svg class="w-3.5 h-3.5 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>Pages:</span>
                    </div>

                    {{-- Horizontal Page Badges Row --}}
                    <div class="flex items-center gap-1.5 overflow-x-auto py-0.5 no-scrollbar">
                        <template x-for="p in totalPages" :key="p">
                            <button type="button"
                                    @click="goToPage(p)"
                                    :class="currentPage === p ? 'border-blue-600 ring-2 ring-blue-500/30 bg-blue-600 text-white font-black' : 'border-slate-200 hover:border-slate-300 bg-slate-50 text-slate-700 font-bold'"
                                    class="h-7 min-w-[28px] px-2 rounded-lg border transition-all flex items-center justify-center shrink-0 cursor-pointer shadow-2xs text-xs font-mono"
                                    x-text="p">
                            </button>
                        </template>
                    </div>

                    <button type="button" @click="showPageOverview = false" class="text-slate-400 hover:text-slate-600 text-xs font-bold cursor-pointer shrink-0">✕ Hide</button>
                </div>

            </div>

            {{-- ── RIGHT PANEL: ANSWER SHEET (MATCHING SCREENSHOT) (5 COLS) ── --}}
            <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 sm:p-6 space-y-4 h-[calc(100vh-170px)] min-h-[680px] flex flex-col sticky top-4">
                
                {{-- Answer Sheet Header --}}
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 shrink-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center shrink-0 shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        </div>
                        <div>
                            <h3 class="font-heading font-black text-base text-slate-900 leading-tight">Answer Sheet</h3>
                            <p class="text-[11px] text-slate-400 font-medium">Select answers while viewing the full module</p>
                        </div>
                    </div>

                    {{-- Auto-saved Badge --}}
                    <div class="px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-[10px] font-bold flex items-center gap-1">
                        <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <span>Auto-saved</span>
                    </div>
                </div>

                {{-- Progress Bar --}}
                <div class="space-y-1.5 shrink-0">
                    <div class="flex items-center justify-between text-xs font-bold text-slate-500">
                        <span>Progress</span>
                        <span class="font-mono text-slate-700" x-text="`${answeredCount} / ${totalQuestions} answered`"></span>
                    </div>
                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-600 transition-all duration-300 rounded-full"
                             :style="'width: ' + Math.round((answeredCount / (totalQuestions || 1)) * 100) + '%'"></div>
                    </div>
                </div>

                {{-- Questions List (Scrollable Cards) --}}
                <div class="flex-1 overflow-y-auto pr-1 space-y-3 admin-scrollbar" id="answer_sheet_list">
                    @foreach($questionsList as $idx => $q)
                    @php
                        $isFillIn = ($q['type'] ?? 'mcq') === 'fill_in_blank';
                        $pageNo = $q['page_number'] ?? null;
                    @endphp
                    <div id="q_row_{{ $q['number'] }}"
                         class="p-3.5 rounded-2xl border transition-all space-y-2.5 bg-white"
                         :class="isQuestionAnswered('{{ $q['number'] }}') ? 'border-slate-200 shadow-2xs' : 'border-slate-100'">
                        
                        {{-- Question Item Top Header --}}
                        <div class="flex items-center gap-2.5">
                            {{-- Status Circle: Green Checkmark if answered, Gray circle if not --}}
                            <div class="w-5 h-5 rounded-full flex items-center justify-center shrink-0 transition"
                                 :class="isQuestionAnswered('{{ $q['number'] }}') ? 'bg-emerald-500 text-white' : 'border-2 border-slate-300'">
                                <template x-if="isQuestionAnswered('{{ $q['number'] }}')">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </template>
                            </div>
                            <span class="font-heading font-black text-xs text-slate-800">Question {{ $q['number'] }}</span>
                        </div>

                        {{-- Options Row (A, B, C, D) --}}
                        @if(!$isFillIn)
                        <div class="grid grid-cols-4 gap-2">
                            @foreach(['A', 'B', 'C', 'D'] as $opt)
                            <button type="button"
                                    @click="selectOption('{{ $q['number'] }}', '{{ $opt }}')"
                                    :class="answers['{{ $q['number'] }}'] === '{{ $opt }}'
                                        ? 'bg-blue-600 text-white font-black shadow-xs ring-2 ring-blue-400/40'
                                        : 'bg-slate-50/70 hover:bg-slate-100 text-slate-700 border border-slate-200/80 font-bold'"
                                    class="py-2.5 rounded-xl text-xs transition-all flex items-center justify-center cursor-pointer">
                                {{ $opt }}
                            </button>
                            @endforeach
                        </div>
                        @else
                        {{-- Fill in the blank input --}}
                        <div class="relative">
                            <input type="text"
                                   x-model="answers['{{ $q['number'] }}']"
                                   @input="updateCount()"
                                   placeholder="Type your answer here..."
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>

            </div>

        </div>

        {{-- ── 4. VERY BOTTOM ACTION BAR (MATCHING SCREENSHOT) ── --}}
        <div x-show="!isExamFinished" class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between gap-4">
            {{-- Left Navigation Buttons --}}
            <div class="flex items-center gap-2.5">
                <button type="button"
                        @click="prevPage()"
                        :disabled="currentPage <= 1 || isRenderingPage"
                        class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 disabled:opacity-30 disabled:pointer-events-none text-xs font-bold text-slate-700 transition flex items-center gap-1.5 cursor-pointer shadow-2xs">
                    <span>← Previous Page</span>
                </button>
            </div>

            {{-- Right Navigation / Submit Buttons --}}
            <div class="flex items-center gap-2.5">
                <button type="button"
                        x-show="currentPage < totalPages"
                        @click="nextPage()"
                        :disabled="isRenderingPage"
                        class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 disabled:opacity-30 text-white font-heading font-black text-xs transition flex items-center gap-1.5 cursor-pointer shadow-md shadow-blue-500/20">
                    <span>Next Page</span>
                    <span>→</span>
                </button>

                {{-- Submit Button --}}
                <button type="button"
                        @click="submitExam()"
                        :disabled="isSubmitting"
                        class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-heading font-black text-xs transition flex items-center gap-2 cursor-pointer shadow-md shadow-emerald-500/20">
                    <svg x-show="isSubmitting" class="w-3.5 h-3.5 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span x-text="isSubmitting ? 'Submitting...' : 'Submit Assessment'"></span>
                </button>
            </div>
        </div>

        {{-- ── 5. INSTANT RESULT POPUP MODAL ── --}}
        <div x-show="showResultModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-xs">
            <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full border border-slate-200/80 shadow-2xl text-center space-y-5 animate-in fade-in zoom-in-95 duration-200">
                <div class="w-16 h-16 rounded-3xl bg-emerald-100 text-emerald-600 mx-auto flex items-center justify-center font-black text-2xl shadow-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>

                <div class="space-y-1">
                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Diagnostic Assessment Submitted
                    </span>
                    <h3 class="font-heading font-black text-2xl text-slate-900">Grading Successful!</h3>
                    <p class="text-xs text-slate-500 font-medium">Your answers have been processed and scored instantly.</p>
                </div>

                <div class="p-6 bg-blue-50/60 rounded-2xl border border-blue-100 text-center space-y-1">
                    <span class="text-xs font-bold text-blue-700 uppercase tracking-wider">Your Score</span>
                    <p class="text-4xl font-black text-blue-900" x-text="finalScore + '%'"></p>
                    <p class="text-xs font-bold text-blue-600" x-text="`${finalCorrect} out of ${totalQuestions} questions correct`"></p>

                    <template x-if="fillTotal > 0">
                        <div class="mt-3 pt-3 border-t border-blue-200/50 grid grid-cols-2 gap-2 text-[11px]">
                            <div class="bg-blue-100/60 rounded-xl py-1.5 px-2">
                                <span class="font-bold text-blue-800 block">MCQ</span>
                                <span class="text-blue-600 font-semibold" x-text="`${mcqCorrect} / ${mcqTotal}`"></span>
                            </div>
                            <div class="bg-violet-100/60 rounded-xl py-1.5 px-2">
                                <span class="font-bold text-violet-800 block">Fill-in</span>
                                <span class="text-violet-600 font-semibold" x-text="`${fillCorrect} / ${fillTotal}`"></span>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="space-y-2 pt-2">
                    <a href="{{ route('student.tasks') }}" class="block w-full py-3.5 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs shadow-md transition-all">
                        Back to Tasks
                    </a>
                    <a href="{{ route('student.dashboard') }}" class="block w-full py-2.5 text-xs font-extrabold text-slate-500 hover:text-slate-800 transition">
                        Go to Student Dashboard
                    </a>
                </div>
            </div>
        </div>

    </div>

    {{-- ── 6. JAVASCRIPT APPLICATION LOGIC & PDF.JS PAGE-BY-PAGE RENDERER ── --}}
    <script>
        // Store raw PDF.js objects outside Alpine's reactive Proxy to prevent
        // "TypeError: Cannot read private member #d from an object whose class did not declare it"
        let rawPdfDoc = null;
        let rawRenderTask = null;

        // Configure PDF.js Worker with resilient fallback for cPanel
        async function setupPdfWorker() {
            if (!window.pdfjsLib) return;

            const localWorker = "{{ asset('vendor/pdfjs/pdf.worker.min.js') }}";
            const cdnWorker   = "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js";

            let useLocal = false;
            try {
                const res = await fetch(localWorker, { method: 'HEAD' });
                if (res.ok) {
                    useLocal = true;
                    window.pdfjsLib.GlobalWorkerOptions.workerSrc = localWorker;
                }
            } catch (e) {
                useLocal = false;
            }

            if (!useLocal) {
                // When local worker is missing on cPanel or blocked, wrap CDN worker in a local Blob
                // to completely bypass browser cross-origin Web Worker security blocks
                try {
                    const blob = new Blob([`importScripts("${cdnWorker}");`], { type: 'application/javascript' });
                    window.pdfjsLib.GlobalWorkerOptions.workerSrc = URL.createObjectURL(blob);
                } catch (e) {
                    window.pdfjsLib.GlobalWorkerOptions.workerSrc = cdnWorker;
                }
            }
        }

        function diagnosticQuizApp() {
            return {
                assignmentId:        {{ $assignment->id }},
                totalQuestions:      {{ $totalQuestions }},
                mcqTotal:            {{ $mcqCount ?? $totalQuestions }},
                fillTotal:           {{ $fillBlankCount ?? 0 }},
                pdfUrl:              '{{ $pdfUrl ?? "" }}',
                hasPdfLoaded:        false,
                currentPage:         1,
                totalPages:          {{ $pdfTotalPages ?? 1 }},
                fitMode:             'page', // 'page' (no slider, whole page fitted) or 'width'
                pageZoom:            1.0,
                showPageOverview:    true,
                isRenderingPage:     false,
                isLoadingPdf:        false,
                pdfError:            false,
                pdfErrorMessage:     '',
                touchStartX:         0,
                touchStartY:         0,
                touchDeltaX:         0,
                isSwiping:           false,
                answers:             {},
                reviewed:            {},
                answeredCount:       0,
                isSubmitting:        false,
                isExamFinished:      {{ $isCompleted ? 'true' : 'false' }},
                showResultModal:     false,
                finalScore:          {{ $studentAssignment->score ?? 0 }},
                finalCorrect:        0,
                mcqCorrect:          0,
                fillCorrect:         0,
                timerSeconds:        {{ ($timeLimitMinutes ?? 20) * 60 }},
                timerInterval:       null,

                initApp() {
                    @if(!$isCompleted)
                    this.startTimer();
                    @endif

                    if (this.pdfUrl) {
                        this.loadPdf();
                    } else {
                        this.pdfError = true;
                        this.pdfErrorMessage = 'No assessment PDF file is associated with this diagnostic test.';
                    }

                    // Keyboard shortcuts: Left/Right arrows flip pages
                    window.addEventListener('keydown', (e) => {
                        if (['INPUT', 'TEXTAREA'].includes(document.activeElement?.tagName)) return;
                        if (e.key === 'ArrowRight' || e.key === 'PageDown') {
                            this.nextPage();
                        } else if (e.key === 'ArrowLeft' || e.key === 'PageUp') {
                            this.prevPage();
                        }
                    });

                    // Window resize listener
                    let resizeTimer = null;
                    window.addEventListener('resize', () => {
                        clearTimeout(resizeTimer);
                        resizeTimer = setTimeout(() => {
                            if (rawPdfDoc && !this.isRenderingPage) {
                                this.renderPage(this.currentPage);
                            }
                        }, 200);
                    });
                },

                async loadPdf() {
                    if (!this.pdfUrl) {
                        this.pdfError = true;
                        this.pdfErrorMessage = 'The PDF URL is missing.';
                        return;
                    }
                    this.isLoadingPdf = true;
                    this.pdfError = false;
                    this.pdfErrorMessage = '';

                    try {
                        await setupPdfWorker();

                        if (!window.pdfjsLib) {
                            throw new Error('PDF rendering library (PDF.js) could not be loaded.');
                        }

                        const loadingTask = window.pdfjsLib.getDocument({
                            url: this.pdfUrl,
                            withCredentials: true,
                        });

                        rawPdfDoc = await loadingTask.promise;
                        this.hasPdfLoaded = true;
                        this.totalPages = rawPdfDoc.numPages || {{ $pdfTotalPages ?? 1 }};
                        this.currentPage = 1;

                        this.$nextTick(() => {
                            this.renderPage(1);
                        });
                    } catch (e) {
                        console.error('[PDF] Failed to load PDF:', e);
                        this.pdfError = true;
                        let msg = (e && e.message) ? e.message : 'Unable to load PDF document.';
                        if (msg.includes('Missing PDF') || msg.includes('404')) {
                            msg = 'The PDF file could not be found on the server (404). Please ensure the file was uploaded on your cPanel hosting.';
                        }
                        this.pdfErrorMessage = msg;
                    } finally {
                        this.isLoadingPdf = false;
                    }
                },

                async renderPage(pageNum) {
                    if (!rawPdfDoc) return;
                    this.isRenderingPage = true;

                    // Cancel previous render task if still in progress
                    if (rawRenderTask) {
                        try {
                            rawRenderTask.cancel();
                        } catch (e) {}
                        rawRenderTask = null;
                    }

                    try {
                        const targetPage = Math.max(1, Math.min(this.totalPages, parseInt(pageNum) || 1));
                        const page = await rawPdfDoc.getPage(targetPage);
                        const canvas = document.getElementById('pdf-single-page-canvas');
                        if (!canvas) {
                            this.isRenderingPage = false;
                            return;
                        }

                        const wrapper = document.getElementById('page-canvas-wrapper');
                        const availWidth  = wrapper ? Math.max(200, wrapper.clientWidth - 28) : 700;
                        const availHeight = wrapper ? Math.max(200, wrapper.clientHeight - 28) : 600;

                        const unscaled = page.getViewport({ scale: 1.0 });

                        let baseScale;
                        if (this.fitMode === 'page') {
                            // Fit both width and height inside the viewport so NO slider is needed
                            const scaleX = availWidth / unscaled.width;
                            const scaleY = availHeight / unscaled.height;
                            baseScale = Math.min(scaleX, scaleY);
                        } else {
                            // Fit to container width
                            baseScale = availWidth / unscaled.width;
                        }

                        const finalScale = baseScale * this.pageZoom;
                        const dpr = Math.min(window.devicePixelRatio || 1, 2);

                        // Scale physical canvas for HiDPI sharpness
                        const viewport = page.getViewport({ scale: finalScale * dpr });

                        canvas.width  = Math.floor(viewport.width);
                        canvas.height = Math.floor(viewport.height);
                        canvas.style.width  = Math.floor(viewport.width / dpr) + 'px';
                        canvas.style.height = Math.floor(viewport.height / dpr) + 'px';

                        const ctx = canvas.getContext('2d');
                        rawRenderTask = page.render({
                            canvasContext: ctx,
                            viewport: viewport
                        });
                        await rawRenderTask.promise;
                        rawRenderTask = null;
                    } catch (err) {
                        if (err && err.name !== 'RenderingCancelledException') {
                            console.error('[PDF] Render page error:', err);
                        }
                    } finally {
                        this.isRenderingPage = false;
                    }
                },

                toggleFitMode() {
                    this.fitMode = this.fitMode === 'page' ? 'width' : 'page';
                    this.pageZoom = 1.0;
                    this.renderPage(this.currentPage);
                },

                zoomIn() {
                    this.pageZoom = Math.min(1.75, +(this.pageZoom + 0.15).toFixed(2));
                    this.renderPage(this.currentPage);
                },

                zoomOut() {
                    this.pageZoom = Math.max(0.75, +(this.pageZoom - 0.15).toFixed(2));
                    this.renderPage(this.currentPage);
                },

                handleTouchStart(e) {
                    if (!this.hasPdfLoaded || e.touches.length !== 1) return;
                    if (this.pageZoom > 1.05) return; // Allow browser panning when zoomed in

                    this.touchStartX = e.touches[0].clientX;
                    this.touchStartY = e.touches[0].clientY;
                    this.touchDeltaX = 0;
                    this.isSwiping   = false;
                },

                handleTouchMove(e) {
                    if (!this.hasPdfLoaded || e.touches.length !== 1) return;
                    if (this.pageZoom > 1.05) return;

                    const deltaX = e.touches[0].clientX - this.touchStartX;
                    const deltaY = e.touches[0].clientY - this.touchStartY;

                    // Activate horizontal slide if movement is predominantly horizontal
                    if (!this.isSwiping) {
                        if (Math.abs(deltaX) > 10 && Math.abs(deltaX) > Math.abs(deltaY) * 1.2) {
                            this.isSwiping = true;
                        }
                    }

                    if (this.isSwiping) {
                        if (e.cancelable) {
                            e.preventDefault();
                        }

                        // Apply elastic drag resistance at first/last page
                        if ((this.currentPage <= 1 && deltaX > 0) || (this.currentPage >= this.totalPages && deltaX < 0)) {
                            this.touchDeltaX = deltaX * 0.25;
                        } else {
                            this.touchDeltaX = deltaX * 0.85;
                        }
                    }
                },

                handleTouchEnd(e) {
                    if (!this.isSwiping) {
                        this.touchDeltaX = 0;
                        return;
                    }
                    this.isSwiping = false;

                    const threshold = 40;
                    if (this.touchDeltaX < -threshold && this.currentPage < this.totalPages) {
                        this.slidePage('next');
                    } else if (this.touchDeltaX > threshold && this.currentPage > 1) {
                        this.slidePage('prev');
                    } else {
                        // Snap back smoothly
                        const card = document.getElementById('pdf-canvas-card');
                        if (card) {
                            card.style.transition = 'transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
                            card.style.transform  = 'translateX(0px)';
                        }
                        this.touchDeltaX = 0;
                    }
                },

                slidePage(direction) {
                    if (this.isRenderingPage) return;
                    if (direction === 'next' && this.currentPage >= this.totalPages) return;
                    if (direction === 'prev' && this.currentPage <= 1) return;

                    const card = document.getElementById('pdf-canvas-card');
                    const targetPage = direction === 'next' ? this.currentPage + 1 : this.currentPage - 1;

                    if (!card) {
                        this.goToPage(targetPage);
                        return;
                    }

                    // 1. Slide current card out smoothly
                    const exitX = direction === 'next' ? -80 : 80;
                    card.style.transition = 'transform 0.14s ease-in, opacity 0.14s';
                    card.style.transform  = `translateX(${exitX}px)`;
                    card.style.opacity    = '0.2';

                    setTimeout(() => {
                        // 2. Prep next page from incoming side
                        const enterX = direction === 'next' ? 80 : -80;
                        card.style.transition = 'none';
                        card.style.transform  = `translateX(${enterX}px)`;
                        card.style.opacity    = '0.2';
                        this.touchDeltaX      = 0;

                        // 3. Switch page & render
                        this.currentPage = targetPage;
                        const wrapper = document.getElementById('page-canvas-wrapper');
                        if (wrapper) wrapper.scrollTo({ top: 0, behavior: 'smooth' });

                        this.renderPage(targetPage).then(() => {
                            // 4. Slide into center
                            requestAnimationFrame(() => {
                                requestAnimationFrame(() => {
                                    card.style.transition = 'transform 0.22s cubic-bezier(0.2, 0.9, 0.3, 1), opacity 0.2s';
                                    card.style.transform  = 'translateX(0px)';
                                    card.style.opacity    = '1';
                                });
                            });
                        });
                    }, 140);
                },

                prevPage() {
                    if (this.currentPage > 1) {
                        this.slidePage('prev');
                    }
                },

                nextPage() {
                    if (this.currentPage < this.totalPages) {
                        this.slidePage('next');
                    }
                },

                goToPage(pageNum) {
                    const target = Math.max(1, Math.min(this.totalPages || 99, parseInt(pageNum) || 1));
                    if (target === this.currentPage) return;
                    const direction = target > this.currentPage ? 'next' : 'prev';
                    const card = document.getElementById('pdf-canvas-card');

                    if (!card) {
                        this.currentPage = target;
                        this.renderPage(target);
                        return;
                    }

                    const exitX = direction === 'next' ? -70 : 70;
                    card.style.transition = 'transform 0.12s ease-in, opacity 0.12s';
                    card.style.transform  = `translateX(${exitX}px)`;
                    card.style.opacity    = '0.2';

                    setTimeout(() => {
                        const enterX = direction === 'next' ? 70 : -70;
                        card.style.transition = 'none';
                        card.style.transform  = `translateX(${enterX}px)`;
                        this.currentPage      = target;

                        const wrapper = document.getElementById('page-canvas-wrapper');
                        if (wrapper) wrapper.scrollTo({ top: 0, behavior: 'smooth' });

                        this.renderPage(target).then(() => {
                            requestAnimationFrame(() => {
                                requestAnimationFrame(() => {
                                    card.style.transition = 'transform 0.2s cubic-bezier(0.2, 0.9, 0.3, 1), opacity 0.2s';
                                    card.style.transform  = 'translateX(0px)';
                                    card.style.opacity    = '1';
                                });
                            });
                        });
                    }, 120);
                },

                toggleFullscreen() {
                    const elem = document.getElementById('assessmentModuleContainer');
                    if (!document.fullscreenElement) {
                        elem?.requestFullscreen().then(() => {
                            setTimeout(() => this.renderPage(this.currentPage), 150);
                        }).catch(err => {
                            console.warn('Error attempting to enable fullscreen:', err.message);
                        });
                    } else {
                        document.exitFullscreen().then(() => {
                            setTimeout(() => this.renderPage(this.currentPage), 150);
                        });
                    }
                },

                selectOption(questionNum, option) {
                    if (this.isExamFinished) return;
                    this.answers[questionNum] = option;
                    this.updateCount();
                },

                isQuestionAnswered(questionNum) {
                    const ans = this.answers[questionNum];
                    return ans !== undefined && ans !== null && String(ans).trim() !== '';
                },

                toggleReview(questionNum) {
                    this.reviewed[questionNum] = !this.reviewed[questionNum];
                },

                isQuestionReviewed(questionNum) {
                    return !!this.reviewed[questionNum];
                },

                updateCount() {
                    this.answeredCount = Object.values(this.answers).filter(v => v !== undefined && v !== null && String(v).trim() !== '').length;
                },

                formatTimer() {
                    let m = Math.floor(this.timerSeconds / 60);
                    let s = this.timerSeconds % 60;
                    return (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
                },

                startTimer() {
                    this.timerInterval = setInterval(() => {
                        if (this.timerSeconds > 0) {
                            this.timerSeconds--;
                        } else {
                            clearInterval(this.timerInterval);
                            this.submitExam();
                        }
                    }, 1000);
                },

                async submitExam() {
                    if (this.answeredCount === 0) {
                        alert('Please answer at least one question before submitting.');
                        return;
                    }

                    if (!confirm(`You have answered ${this.answeredCount} of ${this.totalQuestions} questions. Are you sure you want to submit your assessment?`)) {
                        return;
                    }

                    this.isSubmitting = true;

                    try {
                        const res = await fetch("{{ route('student.diagnostic.submit', $assignment->id) }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept':        'application/json'
                            },
                            body: JSON.stringify({
                                answers:            this.answers,
                                time_taken_seconds: {{ ($timeLimitMinutes ?? 20) * 60 }} - this.timerSeconds
                            })
                        });

                        const data = await res.json();
                        if (data.success) {
                            clearInterval(this.timerInterval);
                            this.finalScore      = data.score;
                            this.finalCorrect    = data.correct_count;
                            this.mcqCorrect      = data.mcq_correct ?? 0;
                            this.fillCorrect     = data.fill_correct ?? 0;
                            this.mcqTotal        = data.mcq_total ?? this.mcqTotal;
                            this.fillTotal       = data.fill_total ?? this.fillTotal;
                            this.isExamFinished  = true;
                            this.showResultModal = true;
                        } else {
                            alert(data.message || 'An error occurred while submitting answers.');
                        }
                    } catch (e) {
                        alert('Connection error. Please try again.');
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            };
        }
    </script>
</x-dashboard-layout>
