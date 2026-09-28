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

        // Resolve PDF file path safely across local, VPS, and cPanel storage locations
        $resolvedPdfPath = self::resolvePdfPath($assignment->file_path);
        $parsedPdf       = ['total_pages' => 0, 'questions' => []];
        $pdfUrl          = null;

        if ($resolvedPdfPath) {
            $parsedPdf = $pdfParser->parsePdf($resolvedPdfPath);
            $pdfUrl    = route('student.diagnostic.pdf', $assignment->id);
        } elseif ($assignment->file_path && str_ends_with(strtolower($assignment->file_path), '.pdf')) {
            // Still route through streamPdf so client receives a clear error/response
            $pdfUrl    = route('student.diagnostic.pdf', $assignment->id);
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

        return view('student.diagnostic', [
            'student'           => $student,
            'assignment'        => $assignment,
            'studentAssignment' => $studentAssignment,
            'questionsList'     => $studentQuestions,
            'totalQuestions'    => $totalQuestions,
            'mcqCount'          => $mcqCount,
            'fillBlankCount'    => $fillBlankCount,
            'timeLimitMinutes'  => $assignment->time_limit_minutes ?: 20,
            'isCompleted'       => in_array($studentAssignment->status, ['submitted', 'graded', 'completed']),
            'answersSummary'    => $studentAssignment->answers_json ?: [],
            'hasSectionB'       => $fillBlankCount > 0,
            'sectionBQuestions' => $fillBlankCount,
            'pdfUrl'            => $pdfUrl,
            'pdfTotalPages'     => $parsedPdf['total_pages'] ?? 1,
            'hasPdfFile'        => ($resolvedPdfPath !== null),
            'expectedFilePath'  => $assignment->file_path,
        ]);
    }

    /**
     * Safely stream the diagnostic assessment PDF to the student browser.
     * Works on cPanel and shared hosting without requiring a public/storage symlink.
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

        if (!$resolvedPath || !file_exists($resolvedPath)) {
            return response()->json([
                'error'          => 'PDF document file not found on server.',
                'expected_file'  => $assignment->file_path,
                'searched_paths' => [
                    storage_path('app/public/' . ltrim(str_replace('\\', '/', $assignment->file_path ?? ''), '/')),
                    public_path('storage/' . ltrim(str_replace('\\', '/', $assignment->file_path ?? ''), '/')),
                ],
            ], 404);
        }

        return response()->file($resolvedPath, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($resolvedPath) . '"',
            'Cache-Control'       => 'public, max-age=86400',
        ]);
    }

    /**
     * Resolve the absolute filesystem path for an assignment PDF across multiple server layouts.
     * Checks storage/app/public, storage/app, public/storage, and public_path so it works
     * identically on localhost, VPS, and cPanel shared hosting without needing symlinks.
     */
    public static function resolvePdfPath(?string $filePath): ?string
    {
        if (empty($filePath)) {
            return null;
        }

        $normalized = str_replace('\\', '/', $filePath);
        $clean = preg_replace('#^(public/|storage/)+#', '', ltrim($normalized, '/'));

        $candidates = [
            storage_path('app/public/' . $clean),
            storage_path('app/' . $clean),
            public_path('storage/' . $clean),
            public_path($clean),
            base_path('storage/app/public/' . $clean),
            storage_path('app/public/' . ltrim($normalized, '/')),
            public_path('storage/' . ltrim($normalized, '/')),
            public_path(ltrim($normalized, '/')),
        ];

        foreach (array_unique($candidates) as $candidate) {
            if (file_exists($candidate) && is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Submit and auto-grade the diagnostic test instantly.
     * Grades both MCQ and Fill-in-the-blank questions (case-insensitive for text).
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

        $submittedAnswers  = $request->input('answers', []);      // ['1' => 'A', '2' => 'photosynthesis', ...]
        $submittedSectionB = $request->input('section_b', []);    // legacy fallback
        $timeTakenSeconds  = (int) $request->input('time_taken_seconds', 0);

        $questionsList  = $assignment->getQuestionsList();
        $totalQuestions = count($questionsList);
        if ($totalQuestions <= 0) {
            $totalQuestions = 10;
        }

        $correctCount   = 0;
        $mcqCorrect     = 0;
        $fillCorrect    = 0;
        $mcqTotal       = 0;
        $fillTotal      = 0;
        $allBreakdown   = [];

        foreach ($questionsList as $q) {
            $qNum       = (string) $q['number'];
            $type       = $q['type'] ?? 'mcq';
            $correctAns = $q['correct_answer'] ?? '';

            if ($type === 'mcq') {
                $mcqTotal++;
            } else {
                $fillTotal++;
            }

            // Retrieve student's answer
            $rawStudentAns = null;
            if (isset($submittedAnswers[$qNum])) {
                $rawStudentAns = $submittedAnswers[$qNum];
            } elseif (isset($submittedAnswers[(int)$qNum])) {
                $rawStudentAns = $submittedAnswers[(int)$qNum];
            } elseif ($type === 'fill_in_blank' && isset($submittedSectionB[$qNum])) {
                $rawStudentAns = $submittedSectionB[$qNum];
            }

            $isCorrect = false;
            if ($type === 'mcq') {
                $studentChoice = $rawStudentAns !== null ? strtoupper(trim($rawStudentAns)) : null;
                $cleanCorrect  = strtoupper(trim($correctAns));
                if (!empty($cleanCorrect) && $studentChoice === $cleanCorrect) {
                    $isCorrect = true;
                    $correctCount++;
                    $mcqCorrect++;
                }
                $displayChoice  = $studentChoice;
                $displayCorrect = $cleanCorrect;
            } else {
                $studentChoice = $rawStudentAns !== null ? trim($rawStudentAns) : null;
                $cleanCorrect  = trim($correctAns);

                if (empty($cleanCorrect)) {
                    // Open-ended / subjective question: if left blank by admin, accept any non-empty answer
                    if ($studentChoice !== null && $studentChoice !== '') {
                        $isCorrect = true;
                        $correctCount++;
                        $fillCorrect++;
                    }
                } elseif ($studentChoice !== null && $studentChoice !== '') {
                    // Split expected answer by comma, slash, or pipe into alternative acceptable answers (either/or)
                    $acceptable = array_map('trim', preg_split('/[,\|\/]+/', $cleanCorrect));
                    $acceptable = array_filter($acceptable, fn($a) => $a !== '');

                    $cleanStudent = trim(preg_replace('/[\.\,\!\?]+$/u', '', $studentChoice));
                    $matched = false;
                    foreach ($acceptable as $target) {
                        $cleanTarget = trim(preg_replace('/[\.\,\!\?]+$/u', '', $target));
                        if ($cleanTarget === '') continue;

                        // 1. Exact match (case-insensitive)
                        if (strcasecmp($cleanStudent, $cleanTarget) === 0) {
                            $matched = true;
                            break;
                        }

                        // 2. Keyword containment (e.g. Student writes full sentence "Aiman membantu murid" and target is "membantu")
                        $pattern = '/(?:\b|^)' . preg_quote($cleanTarget, '/') . '(?:\b|$)/iu';
                        if (preg_match($pattern, $cleanStudent)) {
                            $matched = true;
                            break;
                        }
                    }

                    if ($matched) {
                        $isCorrect = true;
                        $correctCount++;
                        $fillCorrect++;
                    }
                }

                $displayChoice  = $studentChoice;
                $displayCorrect = $cleanCorrect ?: '(Any answer accepted)';
            }

            $allBreakdown[$qNum] = [
                'section'         => $type === 'mcq' ? 'A' : 'B',
                'type'            => $type,
                'question_number' => (int) $qNum,
                'title'           => $q['title'] ?? "Soalan {$qNum}",
                'student_choice'  => $displayChoice,
                'correct_option'  => $displayCorrect,
                'is_correct'      => $isCorrect,
            ];
        }

        // Compute percentage score
        $score = $totalQuestions > 0 ? (int) round(($correctCount / $totalQuestions) * 100) : 0;

        // Determine Tahap Kepandaian for Admin intelligence
        $tahapKepandaian = match (true) {
            $score >= 80 => 'Cemerlang',
            $score >= 60 => 'Baik',
            $score >= 40 => 'Sederhana',
            default      => 'Perlu Bimbingan',
        };

        self::ensureDiagnosticColumnsExist();

        // Build dynamic update payload defensively so it never fails even if a column is missing on cPanel
        $updatePayload = [
            'status'       => 'graded',
            'score'        => $score,
            'submitted_at' => now(),
        ];

        if (Schema::hasColumn('student_assignments', 'tahap_kepandaian')) {
            $updatePayload['tahap_kepandaian'] = $tahapKepandaian;
        }
        if (Schema::hasColumn('student_assignments', 'answers_json')) {
            $updatePayload['answers_json'] = $allBreakdown;
        }
        if (Schema::hasColumn('student_assignments', 'time_taken_seconds')) {
            $updatePayload['time_taken_seconds'] = $timeTakenSeconds;
        }

        $studentAssignment->update($updatePayload);

        return response()->json([
            'success'         => true,
            'score'           => $score,
            'correct_count'   => $correctCount,
            'total_questions' => $totalQuestions,
            'mcq_correct'     => $mcqCorrect,
            'mcq_total'       => $mcqTotal,
            'fill_correct'    => $fillCorrect,
            'fill_total'      => $fillTotal,
            'message'         => 'Ujian diagnostik berjaya dihantar dan dinilai secara automatik!',
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
