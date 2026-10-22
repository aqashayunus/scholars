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
    {{-- Client-side Word (.docx) renderer for non-PDF assessment uploads --}}
    <script src="https://cdn.jsdelivr.net/npm/docx-preview@0.3.3/dist/docx-preview.min.js"></script>

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

            {{-- Right: Autosave Status & Countdown Timer --}}
            <div class="flex items-center gap-2.5">
                {{-- Autosave Status Badge (Hidden per request) --}}
                {{--
                @if(!$isCompleted)
                <div class="px-3 py-1.5 rounded-xl border text-[11px] font-bold flex items-center gap-1.5 transition-all shadow-2xs"
                     :class="saveStatus === 'saving' ? 'bg-amber-50 border-amber-200 text-amber-700' : 'bg-emerald-50 border-emerald-200 text-emerald-700'">
                    <template x-if="saveStatus === 'saving'">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3 h-3 animate-spin text-amber-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span>Saving progress...</span>
                        </span>
                    </template>
                    <template x-if="saveStatus !== 'saving'">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span x-text="saveStatusText || 'Progress saved'"></span>
                        </span>
                    </template>
                </div>
                @endif
                --}}

                {{-- Countdown Timer --}}
                @if(!$isCompleted)
                <div class="px-3.5 py-1.5 rounded-xl border font-mono font-black text-xs flex items-center gap-1.5 transition-colors shadow-2xs"
                     :class="timerSeconds < 300 ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-slate-50 border-slate-200 text-slate-700'">
                    <svg class="w-3.5 h-3.5 text-amber-500 animate-pulse" :class="timerSeconds < 300 ? 'text-rose-600' : 'text-amber-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span x-text="formatTimer()"></span>
                </div>
                @endif
            </div>
        </div>

        {{-- Progress Restored Notice --}}
        <div x-show="hasRestoredProgress" x-cloak class="p-3 bg-blue-50/90 border border-blue-200 rounded-2xl flex items-center justify-between gap-3 text-xs text-blue-900 font-medium animate-in fade-in duration-300">
            <div class="flex items-center gap-2">
                <span class="w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-[10px] shrink-0">✓</span>
                <span><strong>Progress restored:</strong> Your previously filled answers have been recovered. You can continue without losing any work!</span>
            </div>
            <button type="button" @click="hasRestoredProgress = false" class="text-blue-500 hover:text-blue-800 font-bold text-xs cursor-pointer">✕</button>
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

        {{-- Mobile View Mode Switcher (Visible on mobile/tablet < lg) --}}
        <div class="lg:hidden flex items-center bg-slate-100 p-1.5 rounded-2xl border border-slate-200/80 shadow-xs text-xs font-bold mb-1">
            <button type="button"
                    @click="mobileActiveTab = 'module'"
                    :class="mobileActiveTab === 'module' ? 'bg-white text-blue-600 shadow-xs font-black' : 'text-slate-600 hover:text-slate-900'"
                    class="flex-1 py-2.5 rounded-xl transition-all flex items-center justify-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Assessment Paper</span>
            </button>
            <button type="button"
                    @click="mobileActiveTab = 'sheet'"
                    :class="mobileActiveTab === 'sheet' ? 'bg-white text-blue-600 shadow-xs font-black' : 'text-slate-600 hover:text-slate-900'"
                    class="flex-1 py-2.5 rounded-xl transition-all flex items-center justify-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                <span>Answer Sheet</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-mono"
                      :class="mobileActiveTab === 'sheet' ? 'bg-blue-50 text-blue-600 font-bold' : 'bg-slate-200 text-slate-700'"
                      x-text="`${answeredCount}/${totalQuestions}`"></span>
            </button>
        </div>

        {{-- ── 3. MAIN WORKSPACE (SIDE BY SIDE: ASSESSMENT MODULE + ANSWER SHEET) ── --}}
        <div x-show="!isExamFinished" class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

            {{-- ── LEFT PANEL: ASSESSMENT MODULE (PAGE ONLY VIEWER) (7 COLS) ── --}}
            <div id="assessmentModuleContainer"
                 class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col h-[78vh] sm:h-[82vh] lg:h-[calc(100vh-170px)] min-h-[480px] sm:min-h-[580px] lg:min-h-[680px] lg:sticky lg:top-4"
                 :class="mobileActiveTab === 'module' ? 'flex' : 'hidden lg:flex'">
                
                {{-- Module Header Bar --}}
                <div class="px-3.5 sm:px-5 py-3 bg-white border-b border-slate-100 flex flex-wrap items-center justify-between gap-2.5 shrink-0">
                    {{-- Left Title --}}
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center shrink-0 shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <h2 class="font-heading font-black text-sm text-slate-900 leading-tight">Assessment Module</h2>
                            <p class="text-[11px] text-slate-400 font-medium truncate max-w-[150px] sm:max-w-none">
                                {{ $assignment->topic ?? 'Bahasa Melayu' }} • {{ $assignment->target_level ?? 'Year 4' }} • Diagnostic Assessment
                            </p>
                        </div>
                    </div>

                    {{-- Middle: Page Navigation Buttons `< Page X of Y >` (Visible for PDF documents) --}}
                    <div class="flex items-center gap-1.5 shrink-0" x-show="documentType === 'pdf'">
                        <button type="button"
                                @click="prevPage()"
                                :disabled="currentPage <= 1 || isRenderingPage"
                                class="w-8 h-8 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 disabled:opacity-30 disabled:pointer-events-none transition flex items-center justify-center cursor-pointer shadow-2xs font-bold"
                                title="Previous Page (or Left Arrow key)">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                        </button>

                        <div class="px-2.5 py-1 rounded-xl bg-slate-50 border border-slate-200/70 text-xs font-bold text-slate-700 font-mono shadow-2xs">
                            <span>Page <strong class="text-blue-600 font-black" x-text="currentPage"></strong>/<span x-text="totalPages || 1"></span></span>
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
                        {{-- Fit Mode Toggle: Page vs Width --}}
                        <button type="button"
                                x-show="documentType === 'pdf'"
                                @click="toggleFitMode()"
                                :class="fitMode === 'page' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                                class="px-2.5 py-1.5 rounded-xl border text-[11px] font-bold transition flex items-center gap-1 cursor-pointer shadow-2xs"
                                :title="fitMode === 'page' ? 'Currently: Whole page fitted. Click for Fit Width.' : 'Currently: Fit Width. Click for Whole Page.'">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                            <span class="hidden sm:inline" x-text="fitMode === 'page' ? 'Fit Page' : 'Fit Width'"></span>
                        </button>

                        {{-- Zoom Controls (Supports PDF, Word & Image) --}}
                        <div x-show="hasPdfLoaded || hasDocxLoaded || hasImageLoaded" class="flex items-center bg-slate-50 border border-slate-200 rounded-xl p-0.5 text-xs shadow-2xs">
                            <button type="button" @click="zoomOut()" :disabled="pageZoom <= 0.5" class="w-7 h-7 rounded-lg hover:bg-white text-slate-600 hover:text-slate-900 font-bold transition flex items-center justify-center cursor-pointer disabled:opacity-30 disabled:pointer-events-none" title="Zoom Out (− key or Ctrl+Scroll Down)">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                            </button>
                            <button type="button" @click="resetZoom()" class="px-2 font-mono font-black text-xs text-slate-700 min-w-[42px] text-center hover:text-blue-600 cursor-pointer" x-text="`${Math.round(pageZoom * 100)}%`" title="Click to reset zoom (100% Fit, 0 key)"></button>
                            <button type="button" @click="zoomIn()" :disabled="pageZoom >= 3.5" class="w-7 h-7 rounded-lg hover:bg-white text-slate-600 hover:text-slate-900 font-bold transition flex items-center justify-center cursor-pointer disabled:opacity-30 disabled:pointer-events-none" title="Zoom In (+ key or Ctrl+Scroll Up)">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            </button>
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

                {{-- Center Viewport: Zoomable Document Viewport with Mouse Pan & Wheel Zoom --}}
                <div id="page-canvas-wrapper"
                     @touchstart="handleTouchStart($event)"
                     @touchmove="handleTouchMove($event)"
                     @touchend="handleTouchEnd($event)"
                     @mousedown="handleMouseDown($event)"
                     @mousemove="handleMouseMove($event)"
                     @mouseup="handleMouseUp($event)"
                     @mouseleave="handleMouseUp($event)"
                     @wheel="handleWheel($event)"
                     @dblclick="handleDoubleClick($event)"
                     @contextmenu.prevent
                     class="flex-1 w-full bg-[#eef2f6] relative select-none admin-scrollbar overflow-auto transition-all"
                     :class="isDragging ? 'cursor-grabbing select-none' : ((pageZoom > 1.05 || fitMode === 'width') ? 'cursor-grab' : 'cursor-default')"
                     :style="pageZoom > 1.05 ? 'touch-action: pan-x pan-y; -webkit-overflow-scrolling: touch;' : 'touch-action: pan-y;'">

                    {{-- Document Stage: Allows full scrollability to (0,0) without cutoff when zoomed in --}}
                    <div id="document-stage" class="min-w-full min-h-full w-max h-max flex p-3 sm:p-5">

                        @if($documentType === 'docx')
                        {{-- Word Document (.docx) Inline Viewer (Direct in-browser reading with zero download) --}}
                        <div x-show="hasDocxLoaded"
                             id="docx-viewer-card"
                             class="relative bg-white shadow-xl rounded-2xl border border-slate-300/80 p-6 sm:p-10 w-full max-w-4xl m-auto select-none admin-scrollbar transition-transform origin-top shrink-0"
                             :style="pageZoom !== 1.0 ? `transform: scale(${pageZoom}); transform-origin: top center;` : ''">
                            <div id="docx-render-container"></div>
                        </div>
                        @elseif($documentType === 'image')
                        {{-- Image Assessment Document Viewer (Zero download required) --}}
                        <div x-show="hasImageLoaded"
                             id="image-viewer-card"
                             class="relative bg-white shadow-xl rounded-2xl border border-slate-300/80 p-3 m-auto transition-transform origin-center shrink-0"
                             :style="pageZoom !== 1.0 ? `transform: scale(${pageZoom}); transform-origin: center center;` : ''">
                            <img :src="pdfUrl || directFileUrl" alt="Assessment Paper" class="max-w-full h-auto rounded-xl select-none pointer-events-none" @load="hasImageLoaded = true" />
                        </div>
                        @else
                        {{-- White Paper Page Card with Drop Shadow (Only shown when PDF is loaded) --}}
                        <div x-show="hasPdfLoaded"
                             id="pdf-canvas-card"
                             class="relative bg-white shadow-xl rounded-xs border border-slate-300/80 m-auto will-change-transform select-none shrink-0"
                             :style="isSwiping ? `transform: translateX(${touchDeltaX}px); transition: none;` : ''">
                            <canvas id="pdf-single-page-canvas" class="block max-w-none pointer-events-none select-none"></canvas>
                        </div>
                        @endif
                    </div>

                    {{-- Mobile Floating Previous Slide Button --}}
                    <button type="button"
                            x-show="currentPage > 1 && hasPdfLoaded && documentType === 'pdf'"
                            @click.stop="prevPage()"
                            class="sm:hidden absolute left-2 top-1/2 -translate-y-1/2 z-30 w-10 h-10 rounded-full bg-slate-900/60 hover:bg-slate-900/80 backdrop-blur-md text-white shadow-xl flex items-center justify-center active:scale-90 transition cursor-pointer"
                            aria-label="Previous Page">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </button>

                    {{-- Mobile Floating Next Slide Button --}}
                    <button type="button"
                            x-show="currentPage < totalPages && hasPdfLoaded && documentType === 'pdf'"
                            @click.stop="nextPage()"
                            class="sm:hidden absolute right-2 top-1/2 -translate-y-1/2 z-30 w-10 h-10 rounded-full bg-slate-900/60 hover:bg-slate-900/80 backdrop-blur-md text-white shadow-xl flex items-center justify-center active:scale-90 transition cursor-pointer"
                            aria-label="Next Page">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </button>

                    {{-- Mobile Slide & Page Indicator Pill (Bottom Left) --}}
                    <div x-show="hasPdfLoaded && totalPages > 1 && documentType === 'pdf'"
                         class="sm:hidden absolute bottom-3 left-3 z-30 px-3 py-1.5 rounded-full bg-slate-900/80 backdrop-blur-md text-white text-[11px] font-bold shadow-md flex items-center gap-1.5 pointer-events-none select-none">
                        <svg class="w-3.5 h-3.5 text-blue-300 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8m0 0l-4-4m4 4l-4 4m-4 5H4m0 0l4-4m-4 4l4 4"/></svg>
                        <span class="text-blue-300 font-mono ml-0.5" x-text="`Page ${currentPage}/${totalPages}`"></span>
                    </div>

                    {{-- Mobile Floating Switch to Answer Sheet Button --}}
                    <button type="button"
                            @click="mobileActiveTab = 'sheet'"
                            class="lg:hidden absolute top-3 right-3 z-30 px-3.5 py-1.5 rounded-full bg-blue-600 hover:bg-blue-700 text-white shadow-xl flex items-center gap-1.5 text-xs font-bold transition cursor-pointer">
                        <span>Answer Sheet</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>

                    {{-- Mobile Pinch & Zoom Hint (Fades out automatically) --}}
                    <div x-show="showZoomHint && (hasPdfLoaded || hasDocxLoaded || hasImageLoaded)"
                         x-transition:leave="transition ease-in duration-300"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="sm:hidden absolute top-3 left-3 z-30 px-3 py-1.5 rounded-full bg-slate-900/80 backdrop-blur-md text-white text-[10px] font-semibold shadow-md flex items-center gap-1.5 pointer-events-none select-none">
                        <svg class="w-3.5 h-3.5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                        <span>Pinch or double-tap to zoom</span>
                    </div>

                    {{-- Loading Spinner Overlay (Full overlay only on initial document fetch) --}}
                    <div x-show="isLoadingPdf || (isRenderingPage && !hasPdfLoaded)"
                         class="absolute inset-0 bg-[#eef2f6]/75 backdrop-blur-xs flex flex-col items-center justify-center gap-2 z-20"
                         style="display: none;">
                        <svg class="w-8 h-8 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span class="text-xs font-bold text-slate-700" x-text="isLoadingPdf ? 'Loading assessment document...' : `Rendering Page ${currentPage}...`"></span>
                    </div>

                    {{-- Non-intrusive rendering indicator badge for zoom/page changes --}}
                    <div x-show="isRenderingPage && hasPdfLoaded"
                         x-transition
                         class="absolute top-3 left-1/2 -translate-x-1/2 z-30 px-3 py-1 rounded-full bg-slate-900/80 backdrop-blur-md text-white text-[11px] font-semibold flex items-center gap-2 shadow-lg pointer-events-none select-none"
                         style="display: none;">
                        <svg class="w-3.5 h-3.5 animate-spin text-blue-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span>Rendering sharp view...</span>
                    </div>

                    {{-- Error / Unavailable Display (NO download button!) --}}
                    <div x-show="pdfError"
                         class="p-6 sm:p-8 text-center space-y-4 bg-white rounded-3xl border border-rose-200 shadow-sm m-auto max-w-md animate-in fade-in"
                         style="display: none;">
                        <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 mx-auto flex items-center justify-center font-bold text-2xl shadow-xs">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-heading font-black text-base text-slate-900">Document Could Not Be Loaded</h3>
                            <p class="text-xs text-slate-500 mt-1" x-text="pdfErrorMessage || 'The assessment document could not be rendered.'"></p>
                        </div>
                        @if(!empty($expectedFilePath))
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-left text-[11px] font-mono text-slate-600 break-all space-y-1">
                            <p class="font-bold text-slate-800">Target File on Server:</p>
                            <p>{{ $expectedFilePath }}</p>
                        </div>
                        @endif
                        <div class="pt-1 flex items-center justify-center gap-2">
                            <button type="button" @click="loadDocument()" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span>Retry Loading</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Bottom Strip: Page Jump Overview --}}
                <div x-show="documentType === 'pdf' && showPageOverview && totalPages > 1" x-transition class="border-t border-slate-100 bg-white px-4 py-2 shrink-0 flex items-center justify-between gap-2">
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
            <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 sm:p-6 space-y-4 h-[78vh] sm:h-[82vh] lg:h-[calc(100vh-170px)] min-h-[480px] sm:min-h-[580px] lg:min-h-[680px] lg:sticky lg:top-4 flex flex-col"
                 :class="mobileActiveTab === 'sheet' ? 'flex' : 'hidden lg:flex'">
                
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

                    {{-- Mobile Switch Back to Question Paper Button --}}
                    <button type="button"
                            @click="mobileActiveTab = 'module'"
                            class="lg:hidden px-3 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold border border-blue-200 flex items-center gap-1.5 transition cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        <span>View Paper</span>
                    </button>
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
                                   @input="updateCount(); saveProgress()"
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
            if (!window.pdfjsLib) {
                // Wait briefly or load from CDN if not ready
                await new Promise((resolve) => {
                    if (window.pdfjsLib) return resolve();
                    const s = document.createElement('script');
                    s.src = "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js";
                    s.onload = resolve;
                    s.onerror = resolve;
                    document.head.appendChild(s);
                });
            }
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
                directFileUrl:       '{{ $directFileUrl ?? "" }}',
                documentType:        '{{ $documentType ?? "pdf" }}',
                hasPdfLoaded:        false,
                hasDocxLoaded:       false,
                hasImageLoaded:      false,
                currentPage:         1,
                totalPages:          {{ $pdfTotalPages ?? 1 }},
                fitMode:             'page', // 'page' (no slider, whole page fitted) or 'width'
                pageZoom:            1.0,
                showPageOverview:    true,
                isRenderingPage:     false,
                isLoadingPdf:        false,
                pdfError:            false,
                pdfErrorMessage:     '',
                mobileActiveTab:     'module', // 'module' | 'sheet'
                showZoomHint:        true,
                isDragging:          false,
                dragStartX:          0,
                dragStartY:          0,
                scrollStartX:        0,
                scrollStartY:        0,
                isPinching:          false,
                pinchStartDist:      0,
                pinchStartZoom:      1.0,
                lastTapTime:         0,
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
                draftAnswers:        @json($draftAnswers ?? []),
                saveStatus:          'saved',
                saveStatusText:      'Progress saved',
                hasRestoredProgress: false,
                autoSaveTimeout:     null,

                initApp() {
                    // 1. Restore saved progress from Server draft or LocalStorage
                    if (!this.isExamFinished) {
                        let restoredCount = 0;
                        if (this.draftAnswers && typeof this.draftAnswers === 'object' && Object.keys(this.draftAnswers).length > 0) {
                            this.answers = Object.assign({}, this.draftAnswers);
                            restoredCount = Object.keys(this.answers).length;
                        }

                        try {
                            const localRaw = localStorage.getItem('diag_answers_' + this.assignmentId);
                            if (localRaw) {
                                const localParsed = JSON.parse(localRaw);
                                if (localParsed && typeof localParsed === 'object' && Object.keys(localParsed).length > 0) {
                                    this.answers = Object.assign({}, this.answers, localParsed);
                                    restoredCount = Math.max(restoredCount, Object.keys(this.answers).length);
                                }
                            }
                        } catch (e) {}

                        this.updateCount();
                        if (this.answeredCount > 0) {
                            this.hasRestoredProgress = true;
                        }
                    }

                    @if(!$isCompleted)
                    this.startTimer();
                    @endif

                    // 2. Load Assessment Document instantly in-browser
                    this.loadDocument();

                    // Auto-hide mobile zoom hint pill after 5 seconds
                    setTimeout(() => {
                        this.showZoomHint = false;
                    }, 5000);

                    // Keyboard shortcuts: Left/Right arrows flip pages, +/- zooms, 0 resets zoom
                    window.addEventListener('keydown', (e) => {
                        if (['INPUT', 'TEXTAREA'].includes(document.activeElement?.tagName)) return;
                        if (e.key === 'ArrowRight' || e.key === 'PageDown') {
                            this.nextPage();
                        } else if (e.key === 'ArrowLeft' || e.key === 'PageUp') {
                            this.prevPage();
                        } else if (e.key === '+' || e.key === '=') {
                            e.preventDefault();
                            this.zoomIn();
                        } else if (e.key === '-' || e.key === '_') {
                            e.preventDefault();
                            this.zoomOut();
                        } else if (e.key === '0') {
                            e.preventDefault();
                            this.resetZoom();
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

                loadDocument() {
                    if (this.documentType === 'docx') {
                        this.loadDocx();
                    } else if (this.documentType === 'image') {
                        this.hasImageLoaded = true;
                    } else if (this.pdfUrl || this.directFileUrl) {
                        this.loadPdf();
                    } else {
                        this.pdfError = true;
                        this.pdfErrorMessage = 'No assessment document is attached to this diagnostic test.';
                    }
                },

                async loadDocx() {
                    this.isLoadingPdf = true;
                    this.pdfError = false;
                    this.pdfErrorMessage = '';

                    try {
                        const targetUrl = this.pdfUrl || this.directFileUrl;
                        if (!targetUrl) throw new Error('Document URL not found.');

                        const res = await fetch(targetUrl, { credentials: 'same-origin' });
                        if (!res.ok) throw new Error(`Server returned ${res.status} when fetching document.`);
                        const blob = await res.blob();

                        const container = document.getElementById('docx-render-container');
                        if (container && window.docx) {
                            container.innerHTML = '';
                            await window.docx.renderAsync(blob, container, null, {
                                className: 'docx-preview-content',
                                inWrapper: false,
                                ignoreWidth: false,
                                ignoreHeight: false,
                            });
                            this.hasDocxLoaded = true;
                        } else {
                            throw new Error('Word preview container could not be initialized.');
                        }
                    } catch (e) {
                        console.error('[DOCX] Render error:', e);
                        this.pdfError = true;
                        this.pdfErrorMessage = 'Unable to render the assessment Word document. Please ensure the document is uploaded.';
                    } finally {
                        this.isLoadingPdf = false;
                    }
                },

                async loadPdf() {
                    if (!this.pdfUrl && !this.directFileUrl) {
                        this.pdfError = true;
                        this.pdfErrorMessage = 'The assessment document URL is missing.';
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

                        let doc = null;
                        let lastErr = null;

                        // 1. Attempt primary streaming route
                        if (this.pdfUrl) {
                            try {
                                const loadingTask = window.pdfjsLib.getDocument({
                                    url: this.pdfUrl,
                                    withCredentials: true,
                                });
                                doc = await loadingTask.promise;
                            } catch (err) {
                                lastErr = err;
                                console.warn('[PDF] Stream route load failed, trying direct storage fallback...', err);
                            }
                        }

                        // 2. Direct storage fallback if streaming route encountered a server/cPanel path issue
                        if (!doc && this.directFileUrl) {
                            try {
                                const fallbackTask = window.pdfjsLib.getDocument({
                                    url: this.directFileUrl,
                                    withCredentials: false,
                                });
                                doc = await fallbackTask.promise;
                            } catch (err2) {
                                lastErr = err2;
                                console.warn('[PDF] Direct storage URL fallback also failed:', err2);
                            }
                        }

                        if (!doc) {
                            throw lastErr || new Error('The assessment PDF document could not be loaded.');
                        }

                        rawPdfDoc = doc;
                        this.hasPdfLoaded = true;
                        this.totalPages = rawPdfDoc.numPages || {{ $pdfTotalPages ?? 1 }};
                        this.currentPage = 1;

                        this.$nextTick(() => {
                            this.renderPage(1);
                        });
                    } catch (e) {
                        console.error('[PDF] Failed to load PDF:', e);
                        this.pdfError = true;
                        let msg = (e && e.message) ? e.message : 'Unable to render assessment PDF document.';
                        if (msg.includes('Missing PDF') || msg.includes('404')) {
                            msg = 'The assessment PDF file was not found on the server. Please verify the document is uploaded.';
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

                zoomAtCenter(delta) {
                    const wrapper = document.getElementById('page-canvas-wrapper');
                    const oldZoom = this.pageZoom;
                    const newZoom = Math.max(0.5, Math.min(3.5, +(oldZoom + delta).toFixed(2)));
                    if (newZoom === oldZoom) return;

                    let ratioX = 0.5;
                    let ratioY = 0.5;
                    if (wrapper && wrapper.scrollWidth > 0 && wrapper.scrollHeight > 0) {
                        ratioX = (wrapper.scrollLeft + wrapper.clientWidth / 2) / wrapper.scrollWidth;
                        ratioY = (wrapper.scrollTop + wrapper.clientHeight / 2) / wrapper.scrollHeight;
                    }

                    this.pageZoom = newZoom;

                    if (this.documentType === 'pdf') {
                        this.renderPage(this.currentPage).then(() => {
                            if (wrapper && wrapper.scrollWidth > 0) {
                                wrapper.scrollLeft = ratioX * wrapper.scrollWidth - wrapper.clientWidth / 2;
                                wrapper.scrollTop  = ratioY * wrapper.scrollHeight - wrapper.clientHeight / 2;
                            }
                        });
                    }
                },

                zoomIn() {
                    this.zoomAtCenter(0.25);
                },

                zoomOut() {
                    this.zoomAtCenter(-0.25);
                },

                resetZoom() {
                    this.pageZoom = 1.0;
                    const wrapper = document.getElementById('page-canvas-wrapper');
                    if (this.documentType === 'pdf') {
                        this.renderPage(this.currentPage).then(() => {
                            if (wrapper) {
                                wrapper.scrollLeft = 0;
                                wrapper.scrollTop  = 0;
                            }
                        });
                    }
                },

                handleWheel(e) {
                    if (e.ctrlKey || e.metaKey) {
                        e.preventDefault();
                        const delta = e.deltaY < 0 ? 0.25 : -0.25;
                        this.zoomAtCenter(delta);
                    }
                },

                handleDoubleClick(e) {
                    if (this.pageZoom <= 1.05) {
                        this.zoomAtCenter(0.75); // double click expands to ~175%
                    } else {
                        this.resetZoom();
                    }
                },

                handleDoubleTap(e) {
                    if (this.pageZoom <= 1.05) {
                        this.pageZoom = 1.75;
                    } else {
                        this.pageZoom = 1.0;
                    }
                    if (this.documentType === 'pdf') {
                        this.renderPage(this.currentPage);
                    }
                },

                handleMouseDown(e) {
                    if (e.button !== 0) return; // left click only
                    const wrapper = document.getElementById('page-canvas-wrapper');
                    if (!wrapper) return;

                    const isScrollable = (wrapper.scrollWidth > wrapper.clientWidth + 5) || (wrapper.scrollHeight > wrapper.clientHeight + 5);
                    if (this.pageZoom > 1.05 || this.fitMode === 'width' || isScrollable) {
                        this.isDragging = true;
                        this.dragStartX = e.clientX;
                        this.dragStartY = e.clientY;
                        this.scrollStartX = wrapper.scrollLeft;
                        this.scrollStartY = wrapper.scrollTop;
                    }
                },

                handleMouseMove(e) {
                    if (!this.isDragging) return;
                    const wrapper = document.getElementById('page-canvas-wrapper');
                    if (!wrapper) return;

                    e.preventDefault();
                    const deltaX = e.clientX - this.dragStartX;
                    const deltaY = e.clientY - this.dragStartY;
                    wrapper.scrollLeft = this.scrollStartX - deltaX;
                    wrapper.scrollTop  = this.scrollStartY - deltaY;
                },

                handleMouseUp() {
                    this.isDragging = false;
                },

                handleTouchStart(e) {
                    if (!this.hasPdfLoaded && !this.hasDocxLoaded && !this.hasImageLoaded) return;

                    // 1. Two-finger pinch gesture start
                    if (e.touches.length === 2) {
                        this.isPinching = true;
                        this.isSwiping = false;
                        this.touchDeltaX = 0;
                        const x1 = e.touches[0].clientX;
                        const y1 = e.touches[0].clientY;
                        const x2 = e.touches[1].clientX;
                        const y2 = e.touches[1].clientY;
                        this.pinchStartDist = Math.hypot(x2 - x1, y2 - y1);
                        this.pinchStartZoom = this.pageZoom;
                        return;
                    }

                    // 2. Single-finger touch: handle double tap or swipe
                    if (e.touches.length === 1) {
                        const now = Date.now();
                        if (now - this.lastTapTime < 320) {
                            // Double tap detected! Toggle between 1.0x and 2.0x
                            this.handleDoubleTap(e);
                            this.lastTapTime = 0;
                            this.isSwiping = false;
                            this.touchDeltaX = 0;
                            return;
                        }
                        this.lastTapTime = now;

                        // When zoomed in (> 1.05), let the browser perform smooth 2-axis finger panning
                        if (this.pageZoom > 1.05) {
                            this.isSwiping = false;
                            return;
                        }

                        this.touchStartX = e.touches[0].clientX;
                        this.touchStartY = e.touches[0].clientY;
                        this.touchDeltaX = 0;
                        this.isSwiping   = false;
                    }
                },

                handleTouchMove(e) {
                    // 1. Active two-finger pinch-to-zoom
                    if (this.isPinching && e.touches.length === 2) {
                        if (e.cancelable) e.preventDefault();

                        const x1 = e.touches[0].clientX;
                        const y1 = e.touches[0].clientY;
                        const x2 = e.touches[1].clientX;
                        const y2 = e.touches[1].clientY;
                        const currentDist = Math.hypot(x2 - x1, y2 - y1);

                        if (this.pinchStartDist > 0) {
                            const factor = currentDist / this.pinchStartDist;
                            const targetZoom = Math.max(0.5, Math.min(3.5, +(this.pinchStartZoom * factor).toFixed(2)));
                            this.pageZoom = targetZoom;

                            // Apply smooth live GPU scale on the card during gesture for 60fps responsiveness
                            const card = document.getElementById('pdf-canvas-card') || document.getElementById('docx-viewer-card') || document.getElementById('image-viewer-card');
                            if (card) {
                                const liveScale = targetZoom / this.pinchStartZoom;
                                card.style.transform = `scale(${liveScale})`;
                                card.style.transformOrigin = 'center center';
                                card.style.transition = 'none';
                            }
                        }
                        return;
                    }

                    // When zoomed in (> 1.05), allow natural finger panning without triggering page transitions
                    if (this.pageZoom > 1.05) return;

                    if (!this.hasPdfLoaded || e.touches.length !== 1) return;

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
                    // 1. Pinch end: clean up live scale and re-render sharp canvas
                    if (this.isPinching) {
                        this.isPinching = false;
                        this.pinchStartDist = 0;
                        const card = document.getElementById('pdf-canvas-card') || document.getElementById('docx-viewer-card') || document.getElementById('image-viewer-card');
                        if (card) {
                            card.style.transform = '';
                            card.style.transition = '';
                        }
                        if (this.documentType === 'pdf') {
                            this.renderPage(this.currentPage);
                        }
                        return;
                    }

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
                    this.saveProgress();
                },

                saveProgress(debounce = true) {
                    if (this.isExamFinished) return;
                    this.saveStatus = 'saving';

                    // Save to localStorage immediately
                    try {
                        localStorage.setItem('diag_answers_' + this.assignmentId, JSON.stringify(this.answers));
                    } catch (e) {}

                    // Debounce saving to database via server API
                    if (debounce) {
                        clearTimeout(this.autoSaveTimeout);
                        this.autoSaveTimeout = setTimeout(() => {
                            this.sendDraftToServer();
                        }, 1200);
                    } else {
                        this.sendDraftToServer();
                    }
                },

                async sendDraftToServer() {
                    if (this.isExamFinished) return;
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                        if (!csrfToken) return;

                        const res = await fetch("{{ route('student.diagnostic.save-draft', $assignment->id) }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept':        'application/json'
                            },
                            body: JSON.stringify({
                                answers:            this.answers,
                                time_taken_seconds: {{ ($timeLimitMinutes ?? 20) * 60 }} - this.timerSeconds
                            })
                        });

                        const data = await res.json();
                        if (data.success) {
                            this.saveStatus = 'saved';
                            this.saveStatusText = 'Progress saved';
                        }
                    } catch (e) {
                        // Keep saved locally indication if network drops
                        this.saveStatus = 'saved';
                        this.saveStatusText = 'Saved locally';
                    }
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
                            try {
                                localStorage.removeItem('diag_answers_' + this.assignmentId);
                            } catch (e) {}
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
