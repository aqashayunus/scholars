<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Student;
use App\Models\StudentAssignment;
use App\Models\StudentAssignmentSubmission;
use App\Models\SubmissionPage;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class InteractiveAssignmentController extends Controller
{
    /**
     * Display the student interactive assignment answering workspace.
     */
    public function show(Request $request, $assignmentId)
    {
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)->firstOrFail();

        $assignment = Assignment::with(['course.subject'])->findOrFail($assignmentId);

        // Verify PDF attachment exists
        $resolvedPdfPath = self::resolvePdfPath($assignment->file_path);
        if (!$resolvedPdfPath || !file_exists($resolvedPdfPath)) {
            return redirect()->route('student.tasks')->with('error', 'The assignment PDF document is currently unavailable.');
        }

        // Find or create the parent student_assignment record
        $studentAssignment = StudentAssignment::firstOrCreate([
            'student_id'    => $student->id,
            'assignment_id' => $assignment->id,
        ], [
            'status' => 'pending',
        ]);

        // Find or create the interactive submission record
        $submission = StudentAssignmentSubmission::firstOrCreate([
            'student_assignment_id' => $studentAssignment->id,
        ], [
            'assignment_id' => $assignment->id,
            'student_id'    => $student->id,
            'status'        => 'draft',
            'current_page'  => 1,
            'zoom_level'    => 1.00,
            'total_pages'   => 1,
            'version'       => 1,
            'started_at'    => now(),
        ]);

        // Authorization check
        Gate::authorize('view', $submission);

        // Preload existing pages
        $pages = $submission->pages()->get()->keyBy('page_number');

        return view('student.assignments.interactive', [
            'assignment'        => $assignment,
            'student'           => $student,
            'studentAssignment' => $studentAssignment,
            'submission'        => $submission,
            'pagesData'         => $pages,
            'pdfStreamUrl'      => route('student.assignments.pdf-stream', $assignment->id),
            'isLocked'          => $submission->isLocked(),
        ]);
    }

    /**
     * Safely stream the immutable source assignment PDF to authorized users.
     */
    public function streamSourcePdf(Request $request, $assignmentId)
    {
        $assignment = Assignment::findOrFail($assignmentId);
        $user = auth()->user();

        // Verify authorization: Admin, Teacher, or Student
        if ($user->isStudent()) {
            $student = Student::where('user_id', $user->id)->first();
            if (!$student) {
                abort(403, 'Unauthorized student profile.');
            }
        } elseif ($user->isTeacher()) {
            $teacher = $user->teacherProfile;
            if (!$teacher) {
                abort(403, 'Unauthorized teacher profile.');
            }
        } elseif (!$user->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $resolvedPath = self::resolvePdfPath($assignment->file_path);
        if (!$resolvedPath || !file_exists($resolvedPath)) {
            abort(404, 'PDF document file not found on server.');
        }

        return response()->file($resolvedPath, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($resolvedPath) . '"',
            'Cache-Control'       => 'private, max-age=86400',
        ]);
    }

    /**
     * Fetch existing submission state as JSON.
     */
    public function loadState(Request $request, $submissionId)
    {
        $submission = StudentAssignmentSubmission::with('pages')->findOrFail($submissionId);
        Gate::authorize('view', $submission);

        $pagesMap = [];
        foreach ($submission->pages as $page) {
            $pagesMap[(string)$page->page_number] = [
                'page_number'     => $page->page_number,
                'annotation_json' => $page->annotation_json,
                'stroke_count'    => $page->stroke_count,
                'page_version'    => $page->page_version,
            ];
        }

        return response()->json([
            'submission_id' => $submission->id,
            'status'        => $submission->status,
            'current_page'  => $submission->current_page,
            'zoom_level'    => (float) $submission->zoom_level,
            'total_pages'   => $submission->total_pages,
            'is_locked'     => $submission->isLocked(),
            'last_saved_at' => $submission->last_saved_at ? $submission->last_saved_at->toIso8601String() : null,
            'pages'         => $pagesMap,
        ]);
    }

    /**
     * Autosave page annotation strokes.
     */
    public function savePage(Request $request, $submissionId, $pageNumber)
    {
        $submission = StudentAssignmentSubmission::findOrFail($submissionId);
        Gate::authorize('update', $submission);

        if ($submission->isLocked()) {
            return response()->json([
                'success' => false,
                'message' => 'This assignment is already submitted and locked for editing.',
            ], 403);
        }

        $validated = $request->validate([
            'annotation_json'  => 'required|string',
            'stroke_count'     => 'nullable|integer|min:0|max:10000',
            'client_version'   => 'nullable|integer',
            'current_zoom'     => 'nullable|numeric|min:0.2|max:5.0',
            'total_pages'      => 'nullable|integer|min:1|max:500',
        ]);

        $pageNum = (int) $pageNumber;
        if ($pageNum < 1 || $pageNum > 500) {
            return response()->json(['success' => false, 'message' => 'Invalid page number.'], 422);
        }

        // Validate payload size (5MB max)
        if (strlen($validated['annotation_json']) > 5242880) {
            return response()->json(['success' => false, 'message' => 'Annotation payload exceeds maximum allowed size (5MB).'], 422);
        }

        // Validate JSON structure
        $decoded = json_decode($validated['annotation_json'], true);
        if (!is_array($decoded) || !isset($decoded['strokes'])) {
            return response()->json(['success' => false, 'message' => 'Malformed annotation data payload.'], 422);
        }

        // Stroke count protection
        $strokeCount = count($decoded['strokes']);
        if ($strokeCount > 10000) {
            return response()->json(['success' => false, 'message' => 'Maximum stroke count per page exceeded.'], 422);
        }

        $savedVersion = 1;

        DB::transaction(function () use ($submission, $pageNum, $validated, $strokeCount, &$savedVersion) {
            $page = SubmissionPage::where('submission_id', $submission->id)
                ->where('page_number', $pageNum)
                ->lockForUpdate()
                ->first();

            if ($page) {
                // Concurrency optimistic locking check: if client sent version older than database
                if (isset($validated['client_version']) && $validated['client_version'] < $page->page_version) {
                    // Stale tab save attempt
                    abort(409, 'Conflict: newer annotations exist on server for this page.');
                }

                $page->annotation_json = $validated['annotation_json'];
                $page->stroke_count    = $strokeCount;
                $page->page_version   += 1;
                $page->save();

                $savedVersion = $page->page_version;
            } else {
                $page = SubmissionPage::create([
                    'submission_id'   => $submission->id,
                    'page_number'     => $pageNum,
                    'annotation_json' => $validated['annotation_json'],
                    'stroke_count'    => $strokeCount,
                    'page_version'    => 1,
                ]);

                $savedVersion = 1;
            }

            // Update submission metadata
            $submission->current_page  = $pageNum;
            $submission->last_saved_at = now();
            if (!empty($validated['current_zoom'])) {
                $submission->zoom_level = (float) $validated['current_zoom'];
            }
            if (!empty($validated['total_pages'])) {
                $submission->total_pages = (int) $validated['total_pages'];
            }
            $submission->save();
        });

        return response()->json([
            'success'       => true,
            'page_number'   => $pageNum,
            'page_version'  => $savedVersion,
            'last_saved_at' => now()->format('h:i:s A'),
        ]);
    }

    /**
     * Final submission lock and optional merged PDF upload.
     */
    public function submit(Request $request, $submissionId)
    {
        $submission = StudentAssignmentSubmission::with(['assignment.course.teacher', 'student.user'])->findOrFail($submissionId);
        Gate::authorize('submit', $submission);

        if ($submission->isLocked()) {
            return response()->json([
                'success' => false,
                'message' => 'Assignment has already been submitted.',
            ], 422);
        }

        $request->validate([
            'final_pdf' => 'nullable|file|mimes:pdf|max:51200',
        ]);

        $finalPdfPath = null;
        if ($request->hasFile('final_pdf')) {
            $pdfFile = $request->file('final_pdf');
            $dir = 'submissions/' . $submission->student_assignment_id;
            $fileName = 'final_submission_' . time() . '.pdf';
            $finalPdfPath = $pdfFile->storeAs($dir, $fileName, 'public');
        }

        DB::transaction(function () use ($submission, $finalPdfPath) {
            $now = now();

            // Lock the interactive submission
            $submission->status        = 'submitted';
            $submission->submitted_at  = $now;
            if ($finalPdfPath) {
                $submission->final_pdf_path = $finalPdfPath;
            }
            $submission->save();

            // Synchronize parent student_assignments record for 100% backward compatibility
            $studentAssignment = $submission->studentAssignment;
            if ($studentAssignment) {
                $studentAssignment->status       = 'submitted';
                $studentAssignment->submitted_at = $now;
                if ($finalPdfPath) {
                    $studentAssignment->file_path = $finalPdfPath;
                    $studentAssignment->file_name = 'interactive_worksheet.pdf';
                }
                $studentAssignment->save();
            }
        });

        // Send teacher notification if applicable
        $teacherUserId = $submission->assignment?->course?->teacher?->user_id;
        $studentName = $submission->student?->user?->name ?? 'Student';
        $assignmentTitle = $submission->assignment?->title ?? 'Assignment';

        if ($teacherUserId) {
            UserNotification::create([
                'user_id' => $teacherUserId,
                'type'    => 'submission',
                'title'   => 'Interactive Assignment Submitted',
                'message' => "{$studentName} submitted handwritten answers for '{$assignmentTitle}'.",
            ]);
        }

        // Student confirmation notification
        UserNotification::create([
            'user_id' => auth()->id(),
            'type'    => 'task',
            'title'   => 'Assignment Submitted',
            'message' => "You successfully submitted answers for '{$assignmentTitle}'.",
        ]);

        return response()->json([
            'success'      => true,
            'message'      => 'Your assignment answers have been successfully submitted!',
            'redirect_url' => route('student.tasks'),
        ]);
    }

    /**
     * Download or view the final submission PDF.
     */
    public function downloadFinalPdf(Request $request, $submissionId)
    {
        $submission = StudentAssignmentSubmission::findOrFail($submissionId);
        Gate::authorize('view', $submission);

        $filePath = $submission->final_pdf_path;
        if (!$filePath) {
            abort(404, 'Final submitted PDF is not available.');
        }

        $fullPath = null;
        if (Storage::disk('public')->exists($filePath)) {
            $fullPath = Storage::disk('public')->path($filePath);
        } elseif (file_exists(storage_path('app/public/' . $filePath))) {
            $fullPath = storage_path('app/public/' . $filePath);
        } elseif (file_exists(public_path('storage/' . $filePath))) {
            $fullPath = public_path('storage/' . $filePath);
        } elseif (file_exists(base_path('storage/app/public/' . $filePath))) {
            $fullPath = base_path('storage/app/public/' . $filePath);
        }

        if (!$fullPath || !file_exists($fullPath)) {
            abort(404, 'Final submitted PDF is not available on server disk.');
        }

        return response()->file($fullPath, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="submission_' . $submission->id . '.pdf"',
        ]);
    }

    /**
     * Teacher view of student submission (Read-only interactive viewer).
     */
    public function teacherReview(Request $request, $submissionId)
    {
        $submission = StudentAssignmentSubmission::with([
            'assignment.course.subject',
            'student.user',
            'studentAssignment',
            'pages',
        ])->findOrFail($submissionId);

        Gate::authorize('view', $submission);

        $pagesMap = $submission->pages->keyBy('page_number');

        return view('teacher.submissions.review', [
            'submission'        => $submission,
            'assignment'        => $submission->assignment,
            'student'           => $submission->student,
            'studentAssignment' => $submission->studentAssignment,
            'pagesData'         => $pagesMap,
            'pdfStreamUrl'      => route('student.assignments.pdf-stream', $submission->assignment_id),
            'finalPdfUrl'       => $submission->final_pdf_path ? route('teacher.submissions.final-pdf', $submission->id) : null,
            'role'              => 'teacher',
        ]);
    }

    /**
     * Admin view of student submission (Read-only interactive viewer).
     */
    public function adminReview(Request $request, $submissionId)
    {
        $submission = StudentAssignmentSubmission::with([
            'assignment.course.subject',
            'student.user',
            'studentAssignment',
            'pages',
        ])->findOrFail($submissionId);

        Gate::authorize('view', $submission);

        $pagesMap = $submission->pages->keyBy('page_number');

        return view('teacher.submissions.review', [
            'submission'        => $submission,
            'assignment'        => $submission->assignment,
            'student'           => $submission->student,
            'studentAssignment' => $submission->studentAssignment,
            'pagesData'         => $pagesMap,
            'pdfStreamUrl'      => route('student.assignments.pdf-stream', $submission->assignment_id),
            'finalPdfUrl'       => $submission->final_pdf_path ? route('admin.submissions.final-pdf', $submission->id) : null,
            'role'              => 'admin',
        ]);
    }

    /**
     * Cross-platform path resolver for assignment PDF documents across local, VPS, and cPanel environments.
     */
    public static function resolvePdfPath(?string $filePath): ?string
    {
        if (empty($filePath)) {
            return null;
        }

        if (filter_var($filePath, FILTER_VALIDATE_URL)) {
            $parsed = parse_url($filePath, PHP_URL_PATH);
            if ($parsed) {
                $filePath = $parsed;
            }
        }

        $normalized = str_replace('\\', '/', $filePath);
        $clean = preg_replace('#^(public/|storage/|public_html/)+#', '', ltrim($normalized, '/'));
        $filename = basename($normalized);

        $candidates = [];

        try {
            if (Storage::disk('public')->exists($clean)) {
                $candidates[] = Storage::disk('public')->path($clean);
            }
            if (Storage::disk('public')->exists($normalized)) {
                $candidates[] = Storage::disk('public')->path($normalized);
            }
            if (Storage::disk('public')->exists('assignments/' . $filename)) {
                $candidates[] = Storage::disk('public')->path('assignments/' . $filename);
            }
        } catch (\Throwable $e) {}

        try {
            if (Storage::disk('local')->exists($clean)) {
                $candidates[] = Storage::disk('local')->path($clean);
            }
        } catch (\Throwable $e) {}

        $baseDirs = [
            storage_path('app/public'),
            storage_path('app'),
            public_path('storage'),
            public_path(),
            base_path('storage/app/public'),
            base_path('storage'),
            base_path('public_html/storage'),
            base_path('public_html'),
            dirname(base_path()) . '/public_html/storage',
            dirname(base_path()) . '/public_html',
        ];

        if (!empty($_SERVER['DOCUMENT_ROOT'])) {
            $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
            $baseDirs[] = $docRoot . '/storage';
            $baseDirs[] = $docRoot;
        }

        $subPaths = array_unique([
            $clean,
            $normalized,
            ltrim($normalized, '/'),
            'assignments/' . $filename,
            $filename,
        ]);

        foreach ($baseDirs as $dir) {
            $dir = rtrim(str_replace('\\', '/', $dir), '/');
            foreach ($subPaths as $sub) {
                $candidates[] = $dir . '/' . ltrim($sub, '/');
            }
        }

        foreach (array_unique($candidates) as $candidate) {
            if (file_exists($candidate) && is_file($candidate)) {
                return $candidate;
            }
        }

        $lowerFilename = strtolower($filename);
        $searchDirs = [
            storage_path('app/public/assignments'),
            storage_path('app/public'),
            public_path('storage/assignments'),
            public_path('storage'),
            base_path('public_html/storage/assignments'),
            base_path('public_html/storage'),
            base_path('storage/assignments'),
        ];
        if (!empty($_SERVER['DOCUMENT_ROOT'])) {
            $searchDirs[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/storage/assignments';
            $searchDirs[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/storage';
        }

        foreach (array_unique($searchDirs) as $searchDir) {
            if (is_dir($searchDir)) {
                $files = @scandir($searchDir);
                if (is_array($files)) {
                    foreach ($files as $f) {
                        if ($f !== '.' && $f !== '..' && strtolower($f) === $lowerFilename) {
                            $full = rtrim(str_replace('\\', '/', $searchDir), '/') . '/' . $f;
                            if (is_file($full)) {
                                return $full;
                            }
                        }
                    }
                }
            }
        }

        return null;
    }
}
