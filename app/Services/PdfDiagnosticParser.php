<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;

class PdfDiagnosticParser
{
    protected Parser $parser;

    public function __construct()
    {
        $this->parser = new Parser();
    }

    /**
     * Parse a PDF file and extract question texts, prompts, options, shared passages, and page mapping.
     *
     * @param string $absolutePath Absolute path to the PDF on disk
     * @return array
     */
    public function parsePdf(string $absolutePath): array
    {
        if (!file_exists($absolutePath) || !is_readable($absolutePath)) {
            return [
                'total_pages' => 0,
                'questions'   => [],
                'passages'    => [],
            ];
        }

        // Cache the parsed result for 24 hours based on file hash and mtime
        $cacheKey = 'pdf_parsed_meta_v4_' . md5($absolutePath . '_' . filemtime($absolutePath));

        return Cache::remember($cacheKey, 86400, function () use ($absolutePath) {
            try {
                $pdf = $this->parser->parseFile($absolutePath);
                $pages = $pdf->getPages();
                return $this->parseFromPages($pages);
            } catch (\Throwable $e) {
                Log::warning('PdfDiagnosticParser failed to parse PDF: ' . $e->getMessage(), [
                    'path' => $absolutePath
                ]);
                return [
                    'total_pages' => 0,
                    'questions'   => [],
                    'passages'    => [],
                ];
            }
        });
    }

    /**
     * Parse questions and passages from an array of PDF page objects (or mock page objects).
     */
    public function parseFromPages(array $pages): array
    {
        $questions  = [];
        $passages   = [];
        $totalPages = count($pages);
        $fullText   = "";

        // Gather full text first to check notation patterns
        $pagesText = [];
        foreach ($pages as $p) {
                $pText = $p->getText();
                $pagesText[] = $pText;
                $fullText .= "\n" . $pText;
            }

            // Check if document uses standard dot / parenthesis notation (e.g. "1.", "2.", "24.")
            $hasDotNotation = preg_match_all('/(?:^|\n)\s*(?:(?:SOALAN\s+|QUESTION\s+|Q\s*)?\d+[\.\)])\s+/i', $fullText) >= 3;

            // ── 1. EXTRACT QUESTIONS PER PAGE ──
            foreach ($pages as $pageIndex => $page) {
                $pageNum = $pageIndex + 1;
                $text = $pagesText[$pageIndex];

                // Clean instruction/header lines from the page text before matching questions
                $lines = explode("\n", $text);
                $cleanLines = [];
                foreach ($lines as $l) {
                    if (!$this->isInstructionOrHeaderLine($l)) {
                        $cleanLines[] = $l;
                    }
                }
                $cleanText = implode("\n", $cleanLines);

                // Method 1: Standard numbered question regex requiring fullstop "24." or parenthesis "24)" or keywords "SOALAN 1"
                $pattern = '/(?:^|\n)\s*(?:(?:SOALAN\s+|QUESTION\s+|Q\s*)(\d+)[\.\:\)]?|(\d+)[\.\)])\s+([\s\S]*?)(?=(?:\n\s*(?:(?:SOALAN\s+|QUESTION\s+|Q\s*)\d+[\.\:\)]?|\d+[\.\)])\s+)|\z)/i';
                $pageQuestionsFound = 0;

                if (preg_match_all($pattern, $cleanText, $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $match) {
                        $qNum = !empty($match[1]) ? (int) $match[1] : (int) $match[2];
                        $rawBody = trim($match[3]);

                        // Skip if the body itself is an instruction header
                        if ($this->isInstructionOrHeaderLine($rawBody)) {
                            continue;
                        }

                        $options = $this->extractOptions($rawBody);
                        $prompt  = $this->cleanPrompt($rawBody, $options);

                        $targetNum = $qNum;
                        if (isset($questions[$targetNum])) {
                            $targetNum = count($questions) + 1;
                        }

                        $questions[$targetNum] = [
                            'number'   => $targetNum,
                            'page'     => $pageNum,
                            'prompt'   => $prompt,
                            'raw_text' => $rawBody,
                            'options'  => $options,
                            'is_mcq'   => !empty($options),
                        ];
                        $pageQuestionsFound++;
                    }
                }

                // Method 2: Fallback line-by-line parsing ONLY if Method 1 found NOTHING on this page AND the document does not use dot notation
                if ($pageQuestionsFound === 0 && !$hasDotNotation) {
                    $currentQ = null;
                    $currentBody = [];

                    foreach ($lines as $line) {
                        $trimmed = trim($line);
                        if (empty($trimmed) || $this->isInstructionOrHeaderLine($trimmed)) {
                            continue;
                        }

                        // Match keyword or bare number
                        if (preg_match('/^(?:(?:SOALAN\s+|Question\s+|Q\s*)(\d+)[\.\:\)]?|(\d+))\s*(.*)$/i', $trimmed, $m)) {
                            $num = !empty($m[1]) ? (int)$m[1] : (int)$m[2];
                            $rest = $m[3] ?? '';

                            // If bare number, do NOT match if followed by units or words like marks, sweets, biji, orang, etc.
                            if (empty($m[1]) && preg_match('/^(?:marks?|markah|minit|minutes?|sweets?|biji|orang|buah|kotak|ekor|cm|kg|m|g|%)\b/i', trim($rest))) {
                                if ($currentQ !== null) $currentBody[] = $trimmed;
                                continue;
                            }

                            if ($currentQ !== null) {
                                $rawBody = implode("\n", $currentBody);
                                $options = $this->extractOptions($rawBody);
                                $prompt  = $this->cleanPrompt($rawBody, $options);
                                $targetNum = $currentQ['number'];
                                if (isset($questions[$targetNum])) {
                                    $targetNum = count($questions) + 1;
                                }
                                $questions[$targetNum] = [
                                    'number'   => $targetNum,
                                    'page'     => $currentQ['page'],
                                    'prompt'   => $prompt,
                                    'raw_text' => $rawBody,
                                    'options'  => $options,
                                    'is_mcq'   => !empty($options),
                                ];
                            }

                            $currentQ = ['number' => $num, 'page' => $pageNum];
                            $currentBody = [];
                            if (!empty($rest)) $currentBody[] = $rest;
                        } else {
                            if ($currentQ !== null) {
                                $currentBody[] = $trimmed;
                            }
                        }
                    }

                    if ($currentQ !== null) {
                        $rawBody = implode("\n", $currentBody);
                        $options = $this->extractOptions($rawBody);
                        $prompt  = $this->cleanPrompt($rawBody, $options);
                        $targetNum = $currentQ['number'];
                        if (isset($questions[$targetNum])) {
                            $targetNum = count($questions) + 1;
                        }
                        $questions[$targetNum] = [
                            'number'   => $targetNum,
                            'page'     => $currentQ['page'],
                            'prompt'   => $prompt,
                            'raw_text' => $rawBody,
                            'options'  => $options,
                            'is_mcq'   => !empty($options),
                        ];
                    }
                }
            }

            ksort($questions);

            // Extract shared passages ("petikan" / situations) across full text with multi-section global question alignment
            $this->extractPassagesFromText($fullText, null, $passages, $questions);

            // ── 2. CLEAN QUESTIONS & ATTACH SHARED PASSAGES ("PETIKAN") ──
            foreach ($questions as $num => &$q) {
                // Clean any trailing passage header that leaked into previous question's prompt
                $q['prompt'] = $this->stripTrailingPassageHeaders($q['prompt']);

                // Find matching passage for this question number
                $matchedPassage = null;
                foreach ($passages as $p) {
                    if ($num >= $p['from'] && $num <= $p['to']) {
                        $matchedPassage = $p;
                        break;
                    }
                }

                if ($matchedPassage) {
                    $q['passage'] = [
                        'title' => $matchedPassage['title'],
                        'text'  => $matchedPassage['text'],
                        'from'  => $matchedPassage['from'],
                        'to'    => $matchedPassage['to'],
                        'page'  => $matchedPassage['page'] ?? $q['page'],
                    ];
                } else {
                    $q['passage'] = null;
                }
            }
            unset($q);

            return [
                'total_pages' => $totalPages,
                'questions'   => $questions,
                'passages'    => $passages,
            ];
    }

    /**
     * Check if a line is a standalone title (e.g. "A Day at the Farm", "Di Dusun Datuk").
     */
    protected function isStandaloneTitle(string $line): bool
    {
        $line = trim($line);
        if (empty($line) || strlen($line) > 50) return false;
        if (preg_match('/[\.\?\!\,\:\;]$/', $line)) return false;
        if (str_contains($line, ',')) return false;
        if (preg_match('/\b(?:dan|yang|di|ke|pada|dengan|kerana|when|and|the|of|to|in|on|at|is|are|was|were|a|an)\s*$/i', $line)) return false;
        if (preg_match('/^(?:Read|Baca|Questions?|Soalan|Part|Bahagian|Arahan|Instructions?)/i', $line)) return false;
        if (preg_match('/^(?:Tajuk|Title|Story)\s*:/i', $line)) return true;

        $words = array_filter(explode(' ', $line), fn($w) => strlen($w) > 0);
        if (empty($words)) return false;
        $minorWords = ['a', 'an', 'the', 'and', 'but', 'or', 'for', 'nor', 'on', 'at', 'to', 'from', 'by', 'over', 'of', 'in', 'dan', 'di', 'ke'];
        $validCount = 0;
        foreach ($words as $w) {
            if (in_array(strtolower($w), $minorWords) || ctype_upper($w[0])) {
                $validCount++;
            }
        }
        return ($validCount / count($words)) >= 0.8;
    }

    /**
     * Extract shared passages ("petikan" / situations) with question ranges and multi-section question alignment.
     */
    protected function extractPassagesFromText(string $text, ?int $pageNum, array &$passages, array $questions = []): void
    {
        // 1. Explicit range passages (e.g., "Baca petikan berikut untuk Soalan 11–15." or "Read the passage below and answer Questions 11–15.")
        $passageRegex = '/(?:(?:Baca\s+petikan\s+berikut\s+untuk\s+Soalan|Read\s+the\s+passage\s+below\s+and\s+answer\s+Questions?)\s*(\d+)\s*[–\-]\s*(\d+)[\.:]?\s*)([\s\S]*?)(?=(?:\n\s*(?:(?:SOALAN\s+|Question\s+|Q\s*)?(\d+)[\.\)]\s*([^\n]+))|\z))/iu';
        if (preg_match_all($passageRegex, $text, $pMatches, PREG_SET_ORDER)) {
            foreach ($pMatches as $pm) {
                $from    = (int) $pm[1];
                $to      = (int) $pm[2];
                $body    = trim($pm[3]);
                $nextNum = !empty($pm[4]) ? (int) $pm[4] : null;
                $nextTxt = !empty($pm[5]) ? trim($pm[5]) : null;

                $actualFrom   = $from;
                $actualTo     = $to;
                $detectedPage = $pageNum;

                // Multi-section alignment: map relative section question numbers to actual global question numbers
                if (!empty($questions) && $nextTxt !== null) {
                    $cleanSearch = strtolower(preg_replace('/[^a-z0-9\s]/i', '', $nextTxt));
                    $searchWords = array_filter(explode(' ', $cleanSearch), fn($w) => strlen($w) >= 4);

                    $bestMatchScore = 0;
                    $matchedNum = null;
                    foreach ($questions as $qNum => $qItem) {
                        $qPrompt = strtolower(preg_replace('/[^a-z0-9\s]/i', '', $qItem['prompt'] ?? ''));
                        $score = 0;
                        foreach ($searchWords as $w) {
                            if (str_contains($qPrompt, $w)) {
                                $score++;
                            }
                        }
                        if ($score > $bestMatchScore && $score >= 2) {
                            $bestMatchScore = $score;
                            $matchedNum = $qNum;
                        }
                    }

                    if ($matchedNum !== null) {
                        $actualFrom   = $matchedNum;
                        $actualTo     = $matchedNum + ($to - $from);
                        $detectedPage = $questions[$matchedNum]['page'] ?? $pageNum;
                    }
                }

                $body = preg_replace('/={5,}\s*(?:PAGE\s*)?\d*\s*={5,}/i', '', $body);
                $rawBodyLines = array_values(array_filter(array_map('trim', explode("\n", $body)), fn($l) => !empty($l)));
                $title = "Petikan / Reading Passage (Questions {$actualFrom}–{$actualTo})";

                if (!empty($rawBodyLines)) {
                    $firstLine = $rawBodyLines[0];
                    if ($this->isStandaloneTitle($firstLine)) {
                        $title = $firstLine;
                        array_shift($rawBodyLines);
                    }
                }

                $cleanBodyLines = [];
                foreach ($rawBodyLines as $l) {
                    if (preg_match('/^=+\s*(?:PAGE\s*)?\d*\s*=+$/i', $l) || str_contains($l, '====')) continue;
                    if (preg_match('/^\d+\s*={4,}/', $l)) continue;
                    if (preg_match('/^\d+$/', $l)) continue; // standalone page number
                    if ($this->isInstructionOrHeaderLine($l)) continue;
                    $cleanBodyLines[] = $l;
                }
                $body = implode(" ", $cleanBodyLines);

                if (!empty($body)) {
                    $exists = false;
                    foreach ($passages as &$existing) {
                        if ($existing['from'] === $actualFrom && $existing['to'] === $actualTo) {
                            $exists = true;
                            if (strlen($body) > strlen($existing['text'])) {
                                $existing['text']  = $body;
                                $existing['title'] = $title;
                                if (!empty($detectedPage)) $existing['page'] = $detectedPage;
                            }
                            break;
                        }
                    }
                    unset($existing);

                    if (!$exists) {
                        $passages[] = [
                            'from'  => $actualFrom,
                            'to'    => $actualTo,
                            'title' => $title,
                            'text'  => $body,
                            'page'  => $detectedPage,
                        ];
                    }
                }
            }
        }

        // 2. Situations (e.g., "Baca situasi berikut: ..." or "Look at the situation below: ...")
        $sitRegex = '/(?:(?:Baca\s+situasi\s+berikut|Look\s+at\s+the\s+situation\s+below)[\.:]?\s*)([\s\S]*?)(?=(?:\n\s*(?:(?:SOALAN\s+|Question\s+|Q\s*)?(\d+)[\.\)]\s*([^\n]+))|\z))/iu';
        if (preg_match_all($sitRegex, $text, $sMatches, PREG_SET_ORDER)) {
            foreach ($sMatches as $sm) {
                $body    = trim($sm[1]);
                $nextNum = !empty($sm[2]) ? (int) $sm[2] : null;
                $nextTxt = !empty($sm[3]) ? trim($sm[3]) : null;

                $actualFrom   = $nextNum ?? 1;
                $actualTo     = $actualFrom + 1;
                $detectedPage = $pageNum;

                // Multi-section alignment for situation
                if (!empty($questions) && $nextTxt !== null) {
                    $cleanSearch = strtolower(preg_replace('/[^a-z0-9\s]/i', '', $nextTxt));
                    $searchWords = array_filter(explode(' ', $cleanSearch), fn($w) => strlen($w) >= 4);

                    $bestMatchScore = 0;
                    $matchedNum = null;
                    foreach ($questions as $qNum => $qItem) {
                        $qPrompt = strtolower(preg_replace('/[^a-z0-9\s]/i', '', $qItem['prompt'] ?? ''));
                        $score = 0;
                        foreach ($searchWords as $w) {
                            if (str_contains($qPrompt, $w)) {
                                $score++;
                            }
                        }
                        if ($score > $bestMatchScore && $score >= 2) {
                            $bestMatchScore = $score;
                            $matchedNum = $qNum;
                        }
                    }

                    if ($matchedNum !== null) {
                        $actualFrom   = $matchedNum;
                        $actualTo     = $matchedNum + 1;
                        $detectedPage = $questions[$matchedNum]['page'] ?? $pageNum;
                    }
                }

                $body = preg_replace('/={5,}\s*(?:PAGE\s*)?\d*\s*={5,}/i', '', $body);
                $rawSitLines = array_values(array_filter(array_map('trim', explode("\n", $body)), fn($l) => !empty($l)));
                $cleanSitLines = [];
                foreach ($rawSitLines as $l) {
                    if (preg_match('/^=+\s*(?:PAGE\s*)?\d*\s*=+$/i', $l) || str_contains($l, '====')) continue;
                    if (preg_match('/^\d+\s*={4,}/', $l)) continue;
                    if (preg_match('/^\d+$/', $l)) continue;
                    if ($this->isInstructionOrHeaderLine($l)) continue;
                    $cleanSitLines[] = $l;
                }
                $body = preg_replace('/\s+/', ' ', implode(" ", $cleanSitLines));

                if (!empty($body)) {
                    $exists = false;
                    foreach ($passages as &$existing) {
                        if ($existing['from'] === $actualFrom && $existing['to'] === $actualTo) {
                            $exists = true;
                            if (strlen($body) > strlen($existing['text'])) {
                                $existing['text'] = $body;
                                if (!empty($detectedPage)) $existing['page'] = $detectedPage;
                            }
                            break;
                        }
                    }
                    unset($existing);

                    if (!$exists) {
                        $passages[] = [
                            'from'  => $actualFrom,
                            'to'    => $actualTo,
                            'title' => "Situasi / Situation (Questions {$actualFrom}–{$actualTo})",
                            'text'  => $body,
                            'page'  => $detectedPage,
                        ];
                    }
                }
            }
        }
    }

    /**
     * Remove passage headers that accidentally leaked into a previous question's prompt.
     */
    protected function stripTrailingPassageHeaders(string $prompt): string
    {
        $headers = [
            'Baca petikan berikut',
            'Read the passage below',
            'Baca situasi berikut',
            'Look at the situation below',
            'Part B: Reading Comprehension',
        ];

        foreach ($headers as $h) {
            $pos = stripos($prompt, $h);
            if ($pos !== false) {
                $prompt = trim(substr($prompt, 0, $pos));
            }
        }

        return $prompt;
    }

    /**
     * Extract A, B, C, D options from question text if present.
     */
    protected function extractOptions(string $text): array
    {
        $options = [];
        $optPattern = '/(?:^|\n|\s+)([A-D])[\.\)]\s+([\s\S]*?)(?=(?:\n\s*[A-D][\.\)]\s+|\z))/i';
        if (preg_match_all($optPattern, $text, $optMatches, PREG_SET_ORDER)) {
            foreach ($optMatches as $opt) {
                $letter = strtoupper($opt[1]);
                if (!isset($options[$letter])) {
                    $val = preg_replace('/\s+/', ' ', trim($opt[2]));
                    $val = $this->stripTrailingPassageHeaders($val);
                    $options[$letter] = $val;
                }
            }
        }
        return $options;
    }

    /**
     * Clean prompt by removing option choices and excess whitespace.
     */
    protected function cleanPrompt(string $rawBody, array $options): string
    {
        $prompt = $rawBody;
        if (!empty($options)) {
            // Match uppercase option A boundary only (e.g. "\nA.", "\nA)", "\n  A.", or "  A. ")
            // Never match lowercase mid-word letters like "berbeza." or "kaca."
            $optPatterns = [
                '/(?:^|\n)\s*A[\.\)]\s+/u',
                '/(?:^|\n|\s{2,})A[\.\)]\s+/u',
                '/(?:\s+)A[\.\)]\s+/u',
                '/(?:^|\n)\s*A\s+[A-Z0-9]/u',
            ];

            $firstOptPos = false;
            foreach ($optPatterns as $pattern) {
                if (preg_match($pattern, $rawBody, $m, PREG_OFFSET_CAPTURE)) {
                    $pos = $m[0][1];
                    if ($firstOptPos === false || $pos < $firstOptPos) {
                        $firstOptPos = $pos;
                    }
                }
            }
            if ($firstOptPos !== false) {
                $prompt = trim(substr($rawBody, 0, $firstOptPos));
            }
        }
        return preg_replace('/\s+/', ' ', trim($prompt));
    }

    /**
     * Check if a line is an instruction, mark allocation, duration, or section header.
     * These should NEVER be treated as questions.
     */
    public function isInstructionOrHeaderLine(string $line): bool
    {
        $trimmed = trim($line);
        if (empty($trimmed)) return false;

        // Marks / Markah allocation (e.g. "25 marks / 25 markah", "25 markah | Cadangan masa: 35 minit")
        if (preg_match('/^\s*\(?\s*\d+\s*(?:marks?|markah)\b/i', $trimmed)) return true;

        // Time / Duration (e.g. "Suggested Time / Cadangan Masa: 35 minutes", "35 minit")
        if (preg_match('/^(?:Suggested\s+Time|Cadangan\s+Masa|\d+\s*(?:minutes?|minit))\b/i', $trimmed)) return true;

        // Instructions headers
        if (preg_match('/^(?:Arahan|Instructions?)\s*:/i', $trimmed)) return true;
        if (preg_match('/^(?:Jawab\s+semua\s+soalan|Answer\s+all\s+questions)\b/i', $trimmed)) return true;
        if (preg_match('/^(?:Pilih\s+jawapan\s+yang\s+paling\s+tepat|Choose\s+the\s+best\s+answer)\b/i', $trimmed)) return true;

        // Section / Module headers
        if (preg_match('/^(?:SECTION|BAHAGIAN|Part)\s+[A-Z]\b/i', $trimmed)) return true;
        if (preg_match('/^(?:BAB\s+\d+|Maths\s+TAHUN|W\d+|Penulisan\s+Ringkas)/i', $trimmed)) return true;

        return false;
    }
}
