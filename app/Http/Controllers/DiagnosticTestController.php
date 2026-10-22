<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Student;
use App\Models\StudentAssignment;
use App\Services\PdfDiagnosticParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DiagnosticTestController extends Controller
{
    /**
     * Display the diagnostic test taking interface for the authenticated student.
     */
    public function take($id, PdfDiagnosticParser $pdfParser)
    {
        self::ensureDiagnosticColumnsExist();

        $user = auth()->user();
        $student = Student::where('user_id', $user->id)->firstOrFail();

        $assignment = Assignment::findOrFail($id);
        if (!$assignment->isDiagnostic()) {
            return redirect()->route('student.tasks')->with('error', 'The requested assessment is not a diagnostic test.');
        }

        $studentAssignment = StudentAssignment::firstOrCreate([
            'student_id'    => $student->id,
            'assignment_id' => $assignment->id,
        ], [
            'status' => 'pending',
        ]);

        $questionsList  = $assignment->getQuestionsList();
        $totalQuestions = count($questionsList);

        $filePath = $assignment->file_path;
        $hasDocument = !empty($filePath);
        $ext = strtolower(pathinfo($filePath ?? '', PATHINFO_EXTENSION));
        $isDocx = in_array($ext, ['docx', 'doc']);
        $isImage = in_array($ext, ['png', 'jpg', 'jpeg', 'webp']);
        $isPdf = empty($ext) || $ext === 'pdf' || (!$isDocx && !$isImage && $hasDocument);
        $documentType = $isDocx ? 'docx' : ($isImage ? 'image' : ($hasDocument ? 'pdf' : 'none'));

        // Resolve PDF file path safely across local, VPS, and cPanel storage locations
        $resolvedPdfPath = self::resolvePdfPath($filePath);
        $parsedPdf       = ['total_pages' => 0, 'questions' => []];
        $pdfUrl          = null;

        if ($hasDocument) {
            $pdfUrl = route('student.diagnostic.pdf', $assignment->id);
            if ($resolvedPdfPath && $isPdf) {
                try {
                    $parsedPdf = $pdfParser->parsePdf($resolvedPdfPath);
                } catch (\Throwable $e) {}
            }
        }

        // Sanitize for student view (do not expose correct_answer before test completion)
        // and enrich with PDF parser data (page number, extracted prompt, MCQ options)
        $studentQuestions = array_map(function ($q) use ($parsedPdf) {
            $qNum = (int) $q['number'];
            $pdfQ = $parsedPdf['questions'][$qNum] ?? null;

            return [
                'number'         => $q['number'],
                'type'           => $q['type'],
                'title'          => $q['title'],
                'image'          => $q['image'] ?? ($pdfQ['image'] ?? null),
                'page_number'    => $pdfQ['page'] ?? ($q['page_number'] ?? 1),
                'prompt'         => $pdfQ['prompt'] ?? ($q['prompt'] ?? null),
                'options_labels' => $pdfQ['options'] ?? ($q['options_labels'] ?? []),
                'passage'        => $pdfQ['passage'] ?? ($q['passage'] ?? null),
                'has_pdf_data'   => !empty($pdfQ),
            ];
        }, $questionsList);

        $mcqCount       = count(array_filter($questionsList, fn($q) => $q['type'] === 'mcq'));
        $fillBlankCount = count(array_filter($questionsList, fn($q) => $q['type'] === 'fill_in_blank'));
        $isCompleted    = in_array($studentAssignment->status, ['submitted', 'graded', 'completed']);
        $draftAnswers   = (!$isCompleted && is_array($studentAssignment->answers_json)) ? $studentAssignment->answers_json : [];

        $cleanRelPath = $filePath ? ltrim(str_replace('\\', '/', $filePath), '/') : '';

        return view('student.diagnostic', [
            'student'                => $student,
            'assignment'             => $assignment,
            'studentAssignment'      => $studentAssignment,
            'questionsList'          => $studentQuestions,
            'totalQuestions'         => $totalQuestions,
            'mcqCount'               => $mcqCount,
            'fillBlankCount'         => $fillBlankCount,
            'timeLimitMinutes'       => $assignment->subject?->diagnostic_duration ?? ($assignment->time_limit_minutes ?: 20),
            'isCompleted'            => $isCompleted,
            'answersSummary'         => $studentAssignment->answers_json ?: [],
            'draftAnswers'           => $draftAnswers,
            'hasSectionB'            => $fillBlankCount > 0,
            'sectionBQuestions'      => $fillBlankCount,
            'pdfUrl'                 => $pdfUrl,
            'pdfTotalPages'          => $parsedPdf['total_pages'] ?? 1,
            'hasPdfFile'             => ($hasDocument && $documentType === 'pdf'),
            'hasDocument'            => $hasDocument,
            'documentType'           => $documentType,
            'directFileUrl'          => $cleanRelPath ? asset('storage/' . $cleanRelPath) : null,
            'expectedFilePath'       => $filePath,
        ]);
    }

    /**
     * Save student's in-progress draft answers so work is never lost.
     */
    public function saveDraft(Request $request, $id)
    {
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)->firstOrFail();

        $assignment = Assignment::findOrFail($id);
        if (!$assignment->isDiagnostic()) {
            return response()->json(['success' => false, 'message' => 'Invalid diagnostic test.'], 400);
        }

        $studentAssignment = StudentAssignment::firstOrCreate([
            'student_id'    => $student->id,
            'assignment_id' => $assignment->id,
        ], [
            'status' => 'pending',
        ]);

        if (!in_array($studentAssignment->status, ['submitted', 'graded', 'completed'])) {
            self::ensureDiagnosticColumnsExist();
            $answers = $request->input('answers', []);
            $timeTakenSeconds = (int) $request->input('time_taken_seconds', 0);

            $updatePayload = [];
            if (Schema::hasColumn('student_assignments', 'answers_json')) {
                $updatePayload['answers_json'] = $answers;
            }
            if (Schema::hasColumn('student_assignments', 'time_taken_seconds')) {
                $updatePayload['time_taken_seconds'] = $timeTakenSeconds;
            }
            if (!empty($updatePayload)) {
                $studentAssignment->update($updatePayload);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Progress saved successfully.'
        ]);
    }

    /**
     * Safely stream the diagnostic assessment PDF/document to the student browser.
     * Works on cPanel, shared hosting, and local without requiring a public/storage symlink.
     * Uses Content-Disposition: inline and X-Content-Type-Options: nosniff to prevent force-download.
     */
    public function streamPdf($id)
    {
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)->firstOrFail();

        $assignment = Assignment::findOrFail($id);
        if (!$assignment->isDiagnostic()) {
            abort(404, 'Assessment is not a diagnostic test.');
        }

        $resolvedPath = self::resolvePdfPath($assignment->file_path);

        // Fallback to Storage disk 'public' if direct filesystem path failed
        if (!$resolvedPath || !file_exists($resolvedPath)) {
            $clean = preg_replace('#^(public/|storage/)+#', '', ltrim(str_replace('\\', '/', $assignment->file_path ?? ''), '/'));
            try {
                if (Storage::disk('public')->exists($clean)) {
                    $ext = strtolower(pathinfo($clean, PATHINFO_EXTENSION));
                    $contentType = match ($ext) {
                        'pdf'         => 'application/pdf',
                        'png'         => 'image/png',
                        'jpg', 'jpeg' => 'image/jpeg',
                        'webp'        => 'image/webp',
                        'docx'        => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'doc'         => 'application/msword',
                        default       => 'application/pdf',
                    };
                    return Storage::disk('public')->response($clean, basename($clean), [
                        'Content-Type'           => $contentType,
                        'Content-Disposition'    => 'inline; filename="' . basename($clean) . '"',
                        'Cache-Control'          => 'private, max-age=86400',
                        'X-Content-Type-Options' => 'nosniff',
                    ]);
                }
            } catch (\Throwable $e) {}

            return response()->json([
                'error'          => 'Assessment document file not found on server.',
                'expected_file'  => $assignment->file_path,
                'searched_paths' => [
                    storage_path('app/public/' . ltrim(str_replace('\\', '/', $assignment->file_path ?? ''), '/')),
                    public_path('storage/' . ltrim(str_replace('\\', '/', $assignment->file_path ?? ''), '/')),
                ],
            ], 404);
        }

        $ext = strtolower(pathinfo($resolvedPath, PATHINFO_EXTENSION));
        $contentType = match ($ext) {
            'pdf'         => 'application/pdf',
            'png'         => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp'        => 'image/webp',
            'docx'        => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'doc'         => 'application/msword',
            default       => 'application/pdf',
        };

        return response()->file($resolvedPath, [
            'Content-Type'           => $contentType,
            'Content-Disposition'    => 'inline; filename="' . basename($resolvedPath) . '"',
            'Cache-Control'          => 'private, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Resolve the absolute filesystem path for an assignment PDF/document across multiple server layouts.
     * Checks Storage public disk, local disk, storage/app/public, public/storage, cPanel public_html,
     * document root, and performs case-insensitive filename matches on Linux.
     */
    public static function resolvePdfPath(?string $filePath): ?string
    {
        if (empty($filePath)) {
            return null;
        }

        // If a full URL was provided (e.g. https://domain.com/storage/assignments/xyz.pdf)
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

        // 1. Try Laravel Storage disk('public')
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

        // 2. Try Laravel Storage disk('local')
        try {
            if (Storage::disk('local')->exists($clean)) {
                $candidates[] = Storage::disk('local')->path($clean);
            }
        } catch (\Throwable $e) {}

        // 3. Candidate base directories across Local, VPS, cPanel shared hosting
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

        // Check exact candidates
        foreach (array_unique($candidates) as $candidate) {
            if (file_exists($candidate) && is_file($candidate)) {
                return $candidate;
            }
        }

        // 4. Case-insensitive lookup (crucial for Linux/cPanel where .PDF vs .pdf differs)
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
            $searchDirs[] = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/') . '/storage/assignments';
            $searchDirs[] = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/') . '/storage';
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

    /**
     * Submit and auto-grade the diagnostic test using the flexible grading engine.
     * Supports MCQ, fill-in-blank, partial marks, numerical tolerance, criteria,
     * fuzzy matching, multi-required answers, and MANUAL flagging.
     */
    public function submit(Request $request, $id)
    {
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)->firstOrFail();

        $assignment = Assignment::findOrFail($id);
        if (!$assignment->isDiagnostic()) {
            return response()->json(['success' => false, 'message' => 'Invalid diagnostic test.'], 400);
        }

        $studentAssignment = StudentAssignment::firstOrCreate([
            'student_id'    => $student->id,
            'assignment_id' => $assignment->id,
        ], [
            'status' => 'pending',
        ]);

        $submittedAnswers  = $request->input('answers', []);   // ['1' => 'A', '2' => 'photosynthesis', ...]
        $submittedSectionB = $request->input('section_b', []); // legacy fallback
        $timeTakenSeconds  = (int) $request->input('time_taken_seconds', 0);

        // Merge legacy section_b into answers keyed by question number
        foreach ($submittedSectionB as $k => $v) {
            if (!isset($submittedAnswers[$k])) {
                $submittedAnswers[$k] = $v;
            }
        }

        // ── Grade with flexible engine ─────────────────────────────────────
        $engine = new \App\Services\Grading\DiagnosticGradingEngine();
        $result = $engine->gradeSubmission($assignment, $submittedAnswers);

        // Use total_possible as base for percentage when it differs from question count
        $totalQuestions = count($result['breakdown']);
        if ($totalQuestions <= 0) {
            $totalQuestions = 10;
        }

        // Percentage is marks-based (not question-count-based)
        $score           = $result['percentage'];
        $totalScore      = $result['total_score'];
        $totalPossible   = $result['total_possible'];
        $correctCount    = $result['correct_count'];
        $mcqCorrect      = $result['mcq_correct'];
        $mcqTotal        = $result['mcq_total'];
        $fillCorrect     = $result['fill_correct'];
        $fillTotal       = $result['fill_total'];
        $hasManual       = $result['has_manual'];
        $allBreakdown    = $result['breakdown'];

        // Tahap Kepandaian based on marks percentage
        $tahapKepandaian = match (true) {
            $score >= 80 => 'Cemerlang',
            $score >= 60 => 'Baik',
            $score >= 40 => 'Sederhana',
            default      => 'Perlu Bimbingan',
        };

        // If any question needs manual review, mark as pending_review instead of graded
        $status = $hasManual ? 'pending_review' : 'graded';

        self::ensureDiagnosticColumnsExist();

        // Build dynamic update payload defensively
        $updatePayload = [
            'status'       => $status,
            'score'        => $score,
            'submitted_at' => now(),
        ];

        if (Schema::hasColumn('student_assignments', 'tahap_kepandaian')) {
            $updatePayload['tahap_kepandaian'] = $tahapKepandaian;
        }
        if (Schema::hasColumn('student_assignments', 'answers_json')) {
            $updatePayload['answers_json'] = array_values($allBreakdown);
        }
        if (Schema::hasColumn('student_assignments', 'time_taken_seconds')) {
            $updatePayload['time_taken_seconds'] = $timeTakenSeconds;
        }

        $studentAssignment->update($updatePayload);

        return response()->json([
            'success'         => true,
            'score'           => $score,
            'total_score'     => $totalScore,
            'total_possible'  => $totalPossible,
            'correct_count'   => $correctCount,
            'total_questions' => $totalQuestions,
            'mcq_correct'     => $mcqCorrect,
            'mcq_total'       => $mcqTotal,
            'fill_correct'    => $fillCorrect,
            'fill_total'      => $fillTotal,
            'has_manual'      => $hasManual,
            'message'         => $hasManual
                ? 'Ujian diagnostik dihantar. Sesetengah soalan memerlukan semakan guru.'
                : 'Ujian diagnostik berjaya dihantar dan dinilai secara automatik!',
        ]);
    }


    /**
     * Admin live preview: test a grading rule against a sample student answer.
     * POST /admin/diagnostic/test-rule
     */
    public function testGradingRule(Request $request)
    {
        $rule          = $request->input('rule', '');
        $studentAnswer = $request->input('student_answer', '');
        $maxMarks      = (float) $request->input('max_marks', 1.0);
        $questionType  = $request->input('question_type', 'fill_in_blank');

        // Parse & validate the rule
        $validation = \App\Services\Grading\DiagnosticRuleParser::validate($rule);

        // Grade the sample answer
        $engine = new \App\Services\Grading\DiagnosticGradingEngine();
        $result = $engine->gradeQuestion($rule, $studentAnswer, $maxMarks, $questionType);

        return response()->json([
            'success'       => true,
            'rule'          => $rule,
            'rule_type'     => $validation['type'],
            'is_valid_rule' => $validation['valid'],
            'rule_errors'   => $validation['errors'],
            'rule_warnings' => $validation['warnings'],
            'student_answer'=> $studentAnswer,
            'max_marks'     => $maxMarks,
            'score'         => $result['score'],
            'is_correct'    => $result['is_correct'],
            'is_partial'    => $result['is_partial'],
            'is_manual'     => $result['is_manual'],
            'matched'       => $result['matched_criteria'],
            'feedback'      => $result['feedback'],
        ]);
    }

    /**
     * Admin inspection API endpoint to view question-by-question student diagnostic breakdown.
     */
    public function getAdminDetails($id)
    {
        $studentAssignment = StudentAssignment::with(['student.user', 'assignment'])->findOrFail($id);

        $answers   = $studentAssignment->answers_json ?: [];
        $timeTaken = $studentAssignment->time_taken_seconds ?: 0;
        $minutes   = floor($timeTaken / 60);
        $seconds   = $timeTaken % 60;
        $formattedDuration = $minutes > 0 ? "{$minutes}m {$seconds}s" : "{$seconds}s";

        return response()->json([
            'success'            => true,
            'student_name'       => $studentAssignment->student?->user?->name ?? 'Student',
            'student_level'      => $studentAssignment->student?->level ?? 'Academic',
            'test_title'         => $studentAssignment->assignment?->title ?? 'Diagnostic Test',
            'score'              => $studentAssignment->score ?? 0,
            'tahap_kepandaian'   => $studentAssignment->tahap_kepandaian ?? 'Belum Diuji',
            'tahap_description'  => $studentAssignment->getTahapDescription(),
            'tahap_badge_class'  => $studentAssignment->getTahapBadgeClass(),
            'formatted_duration' => $formattedDuration,
            'submitted_at'       => $studentAssignment->submitted_at ? $studentAssignment->submitted_at->format('d M Y, h:i A') : 'Pending',
            'answers_breakdown'  => array_values($answers),
        ]);
    }

    /**
     * Ensure all required diagnostic columns exist in the database.
     * Automatically creates missing columns if migrations have not been run on cPanel/production.
     */
    public static function ensureDiagnosticColumnsExist(): void
    {
        try {
            if (!Schema::hasColumn('student_assignments', 'tahap_kepandaian') ||
                !Schema::hasColumn('student_assignments', 'answers_json') ||
                !Schema::hasColumn('student_assignments', 'time_taken_seconds')) {

                Schema::table('student_assignments', function (Blueprint $table) {
                    if (!Schema::hasColumn('student_assignments', 'tahap_kepandaian')) {
                        $table->string('tahap_kepandaian', 50)->nullable()->after('score');
                    }
                    if (!Schema::hasColumn('student_assignments', 'answers_json')) {
                        $table->longText('answers_json')->nullable()->after('submission_notes');
                    }
                    if (!Schema::hasColumn('student_assignments', 'time_taken_seconds')) {
                        $table->integer('time_taken_seconds')->nullable()->after('tahap_kepandaian');
                    }
                });
            }
        } catch (\Throwable $e) {
            Log::warning('Auto-migration for student_assignments failed: ' . $e->getMessage());
        }

        try {
            if (!Schema::hasColumn('assignments', 'answer_key') ||
                !Schema::hasColumn('assignments', 'total_questions') ||
                !Schema::hasColumn('assignments', 'time_limit_minutes')) {

                Schema::table('assignments', function (Blueprint $table) {
                    if (!Schema::hasColumn('assignments', 'answer_key')) {
                        $table->longText('answer_key')->nullable()->after('instructions');
                    }
                    if (!Schema::hasColumn('assignments', 'total_questions')) {
                        $table->integer('total_questions')->nullable()->default(10)->after('answer_key');
                    }
                    if (!Schema::hasColumn('assignments', 'time_limit_minutes')) {
                        $table->integer('time_limit_minutes')->nullable()->default(20)->after('total_questions');
                    }
                });
            }
        } catch (\Throwable $e) {
            Log::warning('Auto-migration for assignments failed: ' . $e->getMessage());
        }
    }
}
