<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $assignment->title }} - Interactive Assignment | Olympia Scholars</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    {{-- PDF.js Vendor Script with CDN fallback --}}
    <script src="{{ asset('vendor/pdfjs/pdf.min.js') }}"></script>
    <script>
        if (typeof window.pdfjsLib === 'undefined') {
            document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"><\/script>');
        }
    </script>

    {{-- Vite Assets --}}
    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/pdf/interactive-app.js'
    ])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            touch-action: pan-x pan-y;
            -webkit-tap-highlight-color: transparent;
        }
        .active-tool {
            background-color: #2563EB !important;
            color: #FFFFFF !important;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
        }
        .active-width {
            border-color: #2563EB !important;
            background-color: #EFF6FF !important;
            color: #1D4ED8 !important;
        }
        /* Custom scrollbars */
        .viewport-scroll::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        .viewport-scroll::-webkit-scrollbar-track {
            background: #E2E8F0;
        }
        .viewport-scroll::-webkit-scrollbar-thumb {
            background: #94A3B8;
            border-radius: 4px;
        }
        .viewport-scroll::-webkit-scrollbar-thumb:hover {
            background: #64748B;
        }
        /* Interactive Text Box Styles - Clean, Transparent, No Bubble */
        .pdf-text-box {
            user-select: text !important;
            -webkit-user-select: text !important;
            background: transparent !important;
            box-shadow: none !important;
        }
        .pdf-text-input {
            user-select: text !important;
            -webkit-user-select: text !important;
            cursor: text !important;
            background: transparent !important;
        }
        .pdf-text-box.active-text-box {
            border: 1px dashed #94A3B8 !important;
            background: transparent !important;
            box-shadow: none !important;
            z-index: 50 !important;
        }
        .pdf-text-box:hover:not(.active-text-box) {
            border: 1px dashed #CBD5E1 !important;
            background: transparent !important;
        }
    </style>
</head>
<body class="h-full bg-slate-100 flex flex-col overflow-hidden select-none">

    {{-- ── 1. TOP HEADER NAVIGATION BAR ── --}}
    <header class="bg-white border-b border-gray-200/80 px-4 py-2.5 flex items-center justify-between shrink-0 shadow-xs z-30">
        
        {{-- Left: Back Button & Assignment Title --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('student.tasks') }}" class="p-2 rounded-xl text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition-colors" title="Back to Tasks">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="font-extrabold text-sm sm:text-base text-gray-900 truncate max-w-xs sm:max-w-md">
                        {{ $assignment->title }}
                    </h1>
                    @if($isLocked)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-purple-100 text-purple-800 border border-purple-200">
                            Submitted
                        </span>
                    @endif
                </div>
                <p class="text-[11px] text-gray-500 font-medium">
                    {{ $assignment->course?->subject?->name ?? 'Course Task' }} • Total: {{ $assignment->total_marks ?? 100 }} Marks
                </p>
            </div>
        </div>

        {{-- Center: Page Navigation Navigator --}}
        <div class="flex items-center gap-1.5 sm:gap-2">
            <button id="btn-prev-page" type="button" class="p-2 rounded-xl text-gray-600 hover:bg-gray-100 transition disabled:opacity-30 cursor-pointer" title="Previous Page">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <div class="flex items-center gap-1 text-xs font-bold text-gray-700 bg-gray-50 border border-gray-200 px-2.5 py-1.5 rounded-xl">
                <span>Page</span>
                <input id="page-jump-input" type="number" min="1" max="1" value="1" class="w-10 text-center font-black bg-white border border-gray-300 rounded-md py-0.5 text-xs outline-none focus:ring-1 focus:ring-blue-500">
                <span>/</span>
                <span id="total-pages-display" class="font-black">1</span>
            </div>
            <button id="btn-next-page" type="button" class="p-2 rounded-xl text-gray-600 hover:bg-gray-100 transition disabled:opacity-30 cursor-pointer" title="Next Page">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>

        {{-- Right: Save Status & Submit Action --}}
        <div class="flex items-center gap-2 sm:gap-3">
            {{-- Autosave Indicator (Clean text, no bubble or pill background) --}}
            <div id="save-status-badge" class="text-xs font-semibold text-gray-500 flex items-center gap-1.5 select-none">
                <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                <span>Saved</span>
            </div>

            @if(!$isLocked)
                <button id="btn-submit-open" type="button" class="py-2 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs shadow-md shadow-blue-500/20 transition cursor-pointer flex items-center gap-1.5">
                    <span>Submit</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </button>
            @else
                <a href="{{ route('student.submissions.download-final', $submission->id) }}" class="py-2 px-3 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs transition flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span class="hidden sm:inline">Download PDF</span>
                </a>
            @endif
        </div>

    </header>

    {{-- ── 2. CENTER SCROLLABLE VIEWPORT CONTAINER ── --}}
    <main id="pdf-viewport-container" class="flex-1 overflow-auto viewport-scroll relative p-3 sm:p-8 flex items-start">
        
        {{-- PDF Page Card --}}
        <div id="pdf-page-card" class="m-auto relative bg-white shadow-2xl rounded-lg overflow-hidden border border-gray-300 transition-shadow">
            
            {{-- Layer 0: Mozilla PDF.js Render Canvas --}}
            <canvas id="pdf-render-canvas" class="block max-w-none select-none pointer-events-none relative z-0"></canvas>

            {{-- Layer 1: Vector Drawing & Annotation Canvas --}}
            <canvas id="pdf-annotation-canvas" class="absolute inset-0 block max-w-none touch-none cursor-crosshair z-10"></canvas>

            {{-- Layer 2: Interactive Text Annotations Layer --}}
            <div id="pdf-text-layer" class="absolute inset-0 pointer-events-none z-20"></div>

        </div>

    </main>

    {{-- ── 3. BOTTOM ERGONOMIC FLOATING TOOLBAR ── --}}
    @if(!$isLocked)
    <div class="fixed bottom-4 left-1/2 -translate-x-1/2 bg-white/95 backdrop-blur-md border border-gray-200/90 rounded-2xl shadow-2xl p-2 flex items-center gap-1.5 sm:gap-2 z-40 max-w-[95vw] overflow-x-auto no-scrollbar">
        
        {{-- Pen Tool --}}
        <button type="button" data-tool="pen" class="tool-btn active-tool p-2.5 rounded-xl text-gray-700 hover:bg-gray-100 font-bold text-xs flex items-center gap-1.5 transition cursor-pointer" title="Pen Tool (P)">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
            <span class="hidden sm:inline">Pen</span>
        </button>

        {{-- Eraser Tool --}}
        <button type="button" data-tool="eraser" class="tool-btn p-2.5 rounded-xl text-gray-700 hover:bg-gray-100 font-bold text-xs flex items-center gap-1.5 transition cursor-pointer" title="Eraser Tool (E)">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            <span class="hidden sm:inline">Eraser</span>
        </button>

        {{-- Text Tool --}}
        <button type="button" data-tool="text" id="btn-tool-text" class="tool-btn p-2.5 rounded-xl text-gray-700 hover:bg-gray-100 font-bold text-xs flex items-center gap-1.5 transition cursor-pointer" title="Add Text (T)">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M12 6v13m-3 0h6"/>
            </svg>
            <span class="hidden sm:inline">Text</span>
        </button>

        <div class="h-6 w-px bg-gray-200 my-auto"></div>

        {{-- Pen Color Presets --}}
        <div class="flex items-center gap-1.5 px-1">
            <button type="button" data-color="#111827" class="color-dot w-6 h-6 rounded-full bg-gray-900 ring-2 ring-offset-2 ring-gray-900 transition hover:scale-110 cursor-pointer" title="Black"></button>
            <button type="button" data-color="#2563EB" class="color-dot w-6 h-6 rounded-full bg-blue-600 transition hover:scale-110 cursor-pointer" title="Blue"></button>
            <button type="button" data-color="#DC2626" class="color-dot w-6 h-6 rounded-full bg-red-600 transition hover:scale-110 cursor-pointer" title="Red"></button>
            <button type="button" data-color="#059669" class="color-dot w-6 h-6 rounded-full bg-emerald-600 transition hover:scale-110 cursor-pointer" title="Green"></button>
        </div>

        <div class="h-6 w-px bg-gray-200 my-auto"></div>

        {{-- Pen Stroke Widths --}}
        <div class="flex items-center gap-1">
            <button type="button" data-width="1.5" class="width-btn px-2 py-1 rounded-lg border border-gray-200 text-[10px] font-bold text-gray-700 hover:bg-gray-100 transition cursor-pointer" title="Fine (1.5px)">Fine</button>
            <button type="button" data-width="3" class="width-btn active-width px-2 py-1 rounded-lg border border-blue-600 text-[10px] font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 transition cursor-pointer" title="Medium (3px)">Med</button>
            <button type="button" data-width="5" class="width-btn px-2 py-1 rounded-lg border border-gray-200 text-[10px] font-bold text-gray-700 hover:bg-gray-100 transition cursor-pointer" title="Bold (5px)">Bold</button>
        </div>

        <div class="h-6 w-px bg-gray-200 my-auto"></div>

        {{-- Undo / Redo --}}
        <button id="btn-undo" type="button" disabled class="p-2 rounded-xl text-gray-600 hover:bg-gray-100 disabled:opacity-30 transition cursor-pointer" title="Undo (Ctrl+Z)">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a5 5 0 015 5v2M3 10l6 6m-6-6l6-6"/></svg>
        </button>
        <button id="btn-redo" type="button" disabled class="p-2 rounded-xl text-gray-600 hover:bg-gray-100 disabled:opacity-30 transition cursor-pointer" title="Redo (Ctrl+Y)">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10H11a5 5 0 00-5 5v2m15-7l-6-6m6 6l-6 6"/></svg>
        </button>

        <div class="h-6 w-px bg-gray-200 my-auto"></div>

        {{-- Clear Page --}}
        <button id="btn-clear" type="button" class="p-2 rounded-xl text-rose-600 hover:bg-rose-50 transition cursor-pointer" title="Clear Page">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        </button>

        <div class="h-6 w-px bg-gray-200 my-auto"></div>

        {{-- Zoom & Fit Width --}}
        <button id="btn-zoom-out" type="button" class="p-2 rounded-xl text-gray-600 hover:bg-gray-100 transition cursor-pointer" title="Zoom Out">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
        </button>
        <span id="zoom-level-display" class="text-xs font-black text-gray-600 min-w-[38px] text-center cursor-pointer hover:text-blue-600 transition-colors" title="Click to reset to 100%">100%</span>
        <button id="btn-zoom-in" type="button" class="p-2 rounded-xl text-gray-600 hover:bg-gray-100 transition cursor-pointer" title="Zoom In">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        </button>
        <button id="btn-fit-width" type="button" class="px-2.5 py-1.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition cursor-pointer" title="Fit to Page Width (Click to toggle 100%)">
            Fit
        </button>

    </div>
    @endif

    {{-- ── 4. CONFIRMATION MODAL FOR SUBMIT ── --}}
    <div id="submit-confirm-modal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full border border-gray-100 shadow-2xl space-y-5 animate-in fade-in zoom-in-95 duration-200">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h3 class="font-extrabold text-lg text-gray-900">Submit Your Assignment?</h3>
                <p class="text-xs text-gray-500 font-medium mt-1 leading-relaxed">
                    Are you sure you want to submit your handwritten work for <span class="font-black text-gray-800">{{ $assignment->title }}</span>?
                </p>
                <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-xl text-[11px] text-amber-800 font-medium">
                    ⚠️ You will no longer be able to edit your drawings or answers after submitting.
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 pt-2">
                <button id="btn-submit-cancel" type="button" class="py-2.5 px-4 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs transition cursor-pointer">
                    Keep Editing
                </button>
                <button id="btn-submit-confirm" type="button" class="py-2.5 px-6 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs shadow-md shadow-blue-500/20 transition cursor-pointer">
                    Confirm & Submit
                </button>
            </div>
        </div>
    </div>

    {{-- ── 5. FULL-SCREEN LOADING OVERLAY ── --}}
    <div id="loading-overlay" class="fixed inset-0 z-50 bg-white/80 backdrop-blur-xs flex flex-col items-center justify-center p-4 gap-3">
        <div class="w-10 h-10 border-3 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
        <p id="loading-text" class="text-xs font-black text-gray-700 tracking-wide uppercase">Loading Assignment...</p>
    </div>

    {{-- ── 6. INITIALIZATION SCRIPT ── --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const rawPages = @json($pagesData ?? []);
            
            window.appInstance = window.initInteractivePdfAssignment({
                submissionId: {{ $submission->id }},
                assignmentId: {{ $assignment->id }},
                isLocked: {{ $isLocked ? 'true' : 'false' }},
                pdfStreamUrl: "{{ $pdfStreamUrl }}",
                savePageUrl: "{{ route('student.submissions.pages.save', ['submission' => $submission->id, 'pageNumber' => ':page']) }}",
                submitUrl: "{{ route('student.submissions.submit', $submission->id) }}",
                csrfToken: "{{ csrf_token() }}",
                initialPage: {{ $submission->current_page ?? 1 }},
                initialZoom: {{ $submission->zoom_level ?? 1.0 }},
                pagesData: rawPages,
            });
        });
    </script>

</body>
</html>
