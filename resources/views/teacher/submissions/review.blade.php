<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Review: {{ $student->user->name ?? 'Student' }} - {{ $assignment->title }} | Olympia Scholars</title>

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
        .pdf-text-box, .pdf-text-input {
            user-select: text !important;
            -webkit-user-select: text !important;
        }
    </style>
</head>
<body class="h-full bg-slate-100 flex flex-col overflow-hidden select-none">

    {{-- ── 1. TOP HEADER BAR ── --}}
    <header class="bg-white border-b border-gray-200/80 px-4 py-2.5 flex items-center justify-between shrink-0 shadow-xs z-30">
        
        {{-- Left: Back & Student Details --}}
        <div class="flex items-center gap-3">
            <a href="{{ $role === 'admin' ? route('admin.dashboard') . '#courses-tasks' : route('teacher.tasks') }}" class="p-2 rounded-xl text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition-colors" title="Back to Tasks">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="font-extrabold text-sm sm:text-base text-gray-900">
                        {{ $student->user->name ?? 'Student' }}
                    </h1>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200">
                        {{ $submission->status === 'graded' ? 'Graded' : 'Submitted' }}
                    </span>
                </div>
                <p class="text-[11px] text-gray-500 font-medium truncate max-w-xs sm:max-w-md">
                    {{ $assignment->title }} • Submitted: {{ $submission->submitted_at ? $submission->submitted_at->format('d M Y, h:i A') : 'Recently' }}
                </p>
            </div>
        </div>

        {{-- Center: Page Navigation --}}
        <div class="flex items-center gap-1.5 sm:gap-2">
            <button id="btn-prev-page" type="button" class="p-2 rounded-xl text-gray-600 hover:bg-gray-100 transition cursor-pointer" title="Previous Page">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <div class="flex items-center gap-1 text-xs font-bold text-gray-700 bg-gray-50 border border-gray-200 px-2.5 py-1.5 rounded-xl">
                <span>Page</span>
                <input id="page-jump-input" type="number" min="1" max="1" value="1" class="w-10 text-center font-black bg-white border border-gray-300 rounded-md py-0.5 text-xs outline-none focus:ring-1 focus:ring-blue-500">
                <span>/</span>
                <span id="total-pages-display" class="font-black">1</span>
            </div>
            <button id="btn-next-page" type="button" class="p-2 rounded-xl text-gray-600 hover:bg-gray-100 transition cursor-pointer" title="Next Page">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>

        {{-- Right: Actions (Download Final PDF & Toggle Grading Panel) --}}
        <div class="flex items-center gap-2">
            @if($finalPdfUrl)
                <a href="{{ $finalPdfUrl }}" target="_blank" class="py-2 px-3 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs transition flex items-center gap-1.5 cursor-pointer" title="Download Completed PDF">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span class="hidden sm:inline">Download PDF</span>
                </a>
            @endif

            <button id="btn-toggle-grading" type="button" class="py-2 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs shadow-md shadow-blue-500/20 transition cursor-pointer flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Grade Work</span>
            </button>
        </div>

    </header>

    {{-- ── 2. MAIN LAYOUT (VIEWPORT + SLIDE-OVER GRADING DRAWER) ── --}}
    <div class="flex-1 flex overflow-hidden relative">
        
        {{-- Viewport Container --}}
        <main id="pdf-viewport-container" class="flex-1 overflow-auto viewport-scroll relative p-3 sm:p-8 flex items-start">
            <div id="pdf-page-card" class="m-auto relative bg-white shadow-2xl rounded-lg overflow-hidden border border-gray-300">
                {{-- Layer 0: Mozilla PDF.js Canvas --}}
                <canvas id="pdf-render-canvas" class="block max-w-none select-none pointer-events-none relative z-0"></canvas>

                {{-- Layer 1: Vector Drawing Canvas (Read-Only) --}}
                <canvas id="pdf-annotation-canvas" class="absolute inset-0 block max-w-none touch-none pointer-events-none z-10"></canvas>

                {{-- Layer 2: Text Annotations Layer (Read-Only) --}}
                <div id="pdf-text-layer" class="absolute inset-0 pointer-events-none select-text z-20"></div>
            </div>
        </main>

        {{-- Grading Drawer --}}
        <aside id="grading-drawer" class="w-80 sm:w-96 bg-white border-l border-gray-200/80 p-5 shrink-0 flex flex-col justify-between overflow-y-auto z-20 shadow-xl transition-all">
            <div class="space-y-5">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="font-extrabold text-sm text-gray-900 flex items-center gap-2">
                        <span>Assessment Marking</span>
                    </h3>
                    <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg">
                        {{ $studentAssignment->score !== null ? "Current: {$studentAssignment->score} / {$assignment->total_marks}" : 'Ungraded' }}
                    </span>
                </div>

                {{-- Student Information Card --}}
                <div class="p-3.5 bg-gray-50 rounded-2xl border border-gray-100 space-y-1 text-xs">
                    <p class="font-black text-gray-900">{{ $student->user->name ?? 'Student' }}</p>
                    <p class="text-gray-500 font-medium">{{ $student->user->email ?? '-' }}</p>
                    <p class="text-gray-500 font-medium">Academic Level: {{ $student->level ?? 'Standard' }}</p>
                </div>

                {{-- Grading Form --}}
                @php
                    $gradeAction = $role === 'admin'
                        ? route('admin.tasks.grade', ['assignmentId' => $assignment->id, 'studentId' => $student->id])
                        : route('teacher.tasks.grade', ['assignmentId' => $assignment->id, 'studentId' => $student->id]);
                @endphp
                <form action="{{ $gradeAction }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                    @csrf

                    <div>
                        <label class="font-black text-gray-800 block mb-1">
                            Marks Awarded (out of {{ $assignment->total_marks ?? 100 }}) *
                        </label>
                        <input type="number" name="score" min="0" max="{{ $assignment->total_marks ?? 100 }}" required value="{{ $studentAssignment->score ?? '' }}" placeholder="e.g. 85" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs font-bold outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="font-black text-gray-800 block mb-1">
                            Marking Status *
                        </label>
                        <select name="status" required class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-3.5 pr-9 py-2.5 text-xs font-bold outline-none cursor-pointer">
                            <option value="graded" {{ ($studentAssignment->status === 'graded') ? 'selected' : '' }}>Graded & Reviewed</option>
                            <option value="submitted" {{ ($studentAssignment->status === 'submitted') ? 'selected' : '' }}>Needs Revision</option>
                            <option value="late" {{ ($studentAssignment->status === 'late') ? 'selected' : '' }}>Late Submission</option>
                        </select>
                    </div>

                    <div>
                        <label class="font-black text-gray-800 block mb-1">
                            Teacher Feedback & Guidance
                        </label>
                        <textarea name="teacher_feedback" rows="4" placeholder="Provide constructive feedback, praise, and tips for improvement..." class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">{{ $studentAssignment->teacher_feedback ?? '' }}</textarea>
                    </div>

                    <div>
                        <label class="font-black text-gray-800 block mb-1">
                            Upload Graded Student Task (Annotated PDF, Marked File)
                        </label>
                        @if(!empty($studentAssignment->graded_file_path))
                            <div class="mb-2 p-2.5 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between">
                                <div class="flex items-center gap-2 overflow-hidden">
                                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <div class="truncate">
                                        <span class="text-[10px] text-emerald-700 font-extrabold uppercase tracking-wider block">Graded Task Attached</span>
                                        <a href="{{ route('student-assignments.download-graded', $studentAssignment->id) }}" target="_blank" class="text-xs font-bold text-emerald-800 hover:underline truncate block">
                                            {{ $studentAssignment->graded_file_name ?? 'Download Graded Task' }}
                                        </a>
                                    </div>
                                </div>
                                <a href="{{ route('student-assignments.download-graded', $studentAssignment->id) }}" target="_blank" class="p-1.5 bg-emerald-100 hover:bg-emerald-200 text-emerald-800 rounded-lg shrink-0 transition" title="Download Graded File">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                </a>
                            </div>
                        @endif
                        <input type="file" name="graded_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.png,.jpg,.jpeg" class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-extrabold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-200 rounded-xl bg-gray-50">
                        <p class="text-[10px] text-gray-400 font-medium mt-1">Upload marked document, annotated PDF, or rubric (up to 50MB).</p>
                    </div>

                    <button type="submit" class="w-full py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs shadow-md shadow-blue-500/20 transition cursor-pointer">
                        Save Marks & Feedback
                    </button>
                </form>
            </div>

            <div class="pt-4 border-t border-gray-100 text-center">
                <p class="text-[11px] text-gray-400 font-medium">Changes trigger instant notification to student.</p>
            </div>
        </aside>

    </div>

    {{-- ── 3. BOTTOM ZOOM CONTROLS ── --}}
    <div class="fixed bottom-4 left-6 bg-white/95 backdrop-blur-md border border-gray-200/90 rounded-2xl shadow-xl p-1.5 flex items-center gap-1.5 z-30">
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

    {{-- Loading Overlay --}}
    <div id="loading-overlay" class="fixed inset-0 z-50 bg-white/80 backdrop-blur-xs flex flex-col items-center justify-center p-4 gap-3">
        <div class="w-10 h-10 border-3 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
        <p id="loading-text" class="text-xs font-black text-gray-700 tracking-wide uppercase">Loading Submission Work...</p>
    </div>

    {{-- Initialization Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const rawPages = @json($pagesData ?? []);
            
            window.appInstance = window.initInteractivePdfAssignment({
                submissionId: {{ $submission->id }},
                assignmentId: {{ $assignment->id }},
                isLocked: true, // Read-only review mode
                pdfStreamUrl: "{{ $pdfStreamUrl }}",
                savePageUrl: "#",
                submitUrl: "#",
                csrfToken: "{{ csrf_token() }}",
                initialPage: 1,
                initialZoom: 1.0,
                pagesData: rawPages,
            });

            // Toggle grading drawer on mobile/tablet
            const toggleBtn = document.getElementById('btn-toggle-grading');
            const drawer = document.getElementById('grading-drawer');
            if (toggleBtn && drawer) {
                toggleBtn.addEventListener('click', () => {
                    drawer.classList.toggle('hidden');
                });
            }
        });
    </script>

</body>
</html>
