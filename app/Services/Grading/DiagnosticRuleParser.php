<?php

namespace App\Services\Grading;

/**
 * DiagnosticRuleParser
 *
 * Parses admin-defined answer rule strings into a normalized AST (array)
 * that the DiagnosticGradingEngine can evaluate against student answers.
 *
 * Supported syntax:
 *  [Answer]            — Single standard answer (case-insensitive, normalized)
 *  [A, B, C]           — Alternative answers / synonyms (any one accepted)
 *  [A];[B]             — Multiple required answers (all required, partial credit)
 *  + [Keyword] @marks  — Component marking criteria (awarded per matched part)
 *  [9.8] ±0.1          — Numerical tolerance (accepts value within range)
 *  =[Answer]           — Exact verbatim case-sensitive match
 *  ~[Answer]           — Fuzzy typo-tolerant match (Levenshtein similarity)
 *  [MANUAL]            — Flag question for manual teacher review
 *  plain text          — Legacy fallback (comma/pipe/slash delimited alternatives)
 */
class DiagnosticRuleParser
{
    /**
     * Detect the rule type from a rule string.
     */
    public static function detectRuleType(string $rule): string
    {
        $trimmed = trim($rule);

        if ($trimmed === '') {
            return 'empty';
        }

        // [MANUAL] — exact keyword
        if (preg_match('/^\[MANUAL\]$/i', $trimmed)) {
            return 'manual';
        }

        // =[Answer] — exact verbatim match
        if (preg_match('/^=\[.+\]$/u', $trimmed)) {
            return 'exact';
        }

        // ~[Answer] — fuzzy match
        if (preg_match('/^~\[.+\]$/u', $trimmed)) {
            return 'fuzzy';
        }

        // [Number] ±tolerance — numerical range
        if (preg_match('/^\[[\d\.,\s]+\]\s*[±\+\-][=]?\s*[\d\.]+/u', $trimmed)) {
            return 'numerical';
        }

        // + [Keyword] @marks — component criteria list
        if (preg_match('/^\+\s*\[.+\]\s*@[\d\.]+/u', $trimmed)) {
            return 'criteria';
        }

        // [A];[B] — multiple required answers
        if (preg_match('/\[.+\]\s*;\s*\[.+\]/u', $trimmed)) {
            return 'multi_required';
        }

        // [A, B, C] or [A] — single/alternative answers
        if (preg_match('/^\[.+\]$/u', $trimmed)) {
            return 'alternatives';
        }

        // Legacy plain text (comma/pipe/slash separated)
        return 'legacy';
    }

    /**
     * Parse a rule string into a normalized AST structure.
     *
     * @param  string $ruleString  Raw rule text configured by admin
     * @param  float  $maxMarks    Maximum marks for this question
     * @return array               Structured AST ready for grading
     */
    public static function parse(string $ruleString, float $maxMarks = 1.0): array
    {
        $trimmed  = trim($ruleString);
        $ruleType = self::detectRuleType($trimmed);

        return match ($ruleType) {
            'manual'       => self::parseManual($trimmed, $maxMarks),
            'exact'        => self::parseExact($trimmed, $maxMarks),
            'fuzzy'        => self::parseFuzzy($trimmed, $maxMarks),
            'numerical'    => self::parseNumerical($trimmed, $maxMarks),
            'criteria'     => self::parseCriteria($trimmed, $maxMarks),
            'multi_required'=> self::parseMultiRequired($trimmed, $maxMarks),
            'alternatives' => self::parseAlternatives($trimmed, $maxMarks),
            'empty'        => self::parseEmpty($maxMarks),
            default        => self::parseLegacy($trimmed, $maxMarks),
        };
    }

    /**
     * Validate a rule string. Returns array with 'valid', 'type', 'errors', 'warnings'.
     */
    public static function validate(string $ruleString): array
    {
        $trimmed  = trim($ruleString);
        $ruleType = self::detectRuleType($trimmed);
        $errors   = [];
        $warnings = [];

        if ($trimmed === '') {
            return [
                'valid'    => true,
                'type'     => 'empty',
                'errors'   => [],
                'warnings' => ['Empty rule: will accept any non-empty student answer.'],
            ];
        }

        // Validate bracket pairs
        $openCount  = substr_count($trimmed, '[');
        $closeCount = substr_count($trimmed, ']');
        if ($openCount !== $closeCount) {
            $errors[] = "Mismatched brackets: found {$openCount} '[' and {$closeCount} ']'.";
        }

        // Criteria: each component must have @marks
        if ($ruleType === 'criteria') {
            preg_match_all('/\+\s*\[(.+?)\](\s*@([\d\.]+))?/u', $trimmed, $criteriaMatches, PREG_SET_ORDER);
            foreach ($criteriaMatches as $match) {
                if (empty($match[2])) {
                    $errors[] = "Criteria component '[{$match[1]}]' is missing @marks (e.g. + [{$match[1]}] @1).";
                }
            }
        }

        // Numerical: tolerance must be positive
        if ($ruleType === 'numerical') {
            if (preg_match('/\[(.+?)\]\s*[±\+\-][=]?\s*([\d\.]+)/u', $trimmed, $m)) {
                if ((float) $m[2] < 0) {
                    $errors[] = "Tolerance value must be positive. Got: {$m[2]}";
                }
            }
        }

        // Warn if MANUAL mixed with other syntax
        if (preg_match('/\[MANUAL\]/i', $trimmed) && $ruleType !== 'manual') {
            $warnings[] = '[MANUAL] should be used alone, not mixed with other syntax.';
        }

        return [
            'valid'    => empty($errors),
            'type'     => $ruleType,
            'errors'   => $errors,
            'warnings' => $warnings,
        ];
    }

    // ── Parser Helpers ────────────────────────────────────────────────────────

    protected static function parseManual(string $rule, float $maxMarks): array
    {
        return [
            'type'      => 'manual',
            'max_marks' => $maxMarks,
            'raw'       => $rule,
        ];
    }

    protected static function parseEmpty(float $maxMarks): array
    {
        return [
            'type'      => 'empty',
            'max_marks' => $maxMarks,
            'raw'       => '',
        ];
    }

    protected static function parseExact(string $rule, float $maxMarks): array
    {
        // =[Answer] — strip =[ and ]
        preg_match('/^=\[(.+)\]$/su', $rule, $m);
        $answer = $m[1] ?? '';

        return [
            'type'      => 'exact',
            'answer'    => $answer,           // case-sensitive, verbatim
            'max_marks' => $maxMarks,
            'raw'       => $rule,
        ];
    }

    protected static function parseFuzzy(string $rule, float $maxMarks): array
    {
        // ~[Answer] — strip ~[ and ]
        preg_match('/^~\[(.+)\]$/su', $rule, $m);
        $answer = $m[1] ?? '';

        return [
            'type'          => 'fuzzy',
            'answer'        => $answer,
            'similarity'    => 0.80,    // default: require ≥80% Levenshtein similarity
            'max_distance'  => 2,       // or max 2 character edits
            'max_marks'     => $maxMarks,
            'raw'           => $rule,
        ];
    }

    protected static function parseNumerical(string $rule, float $maxMarks): array
    {
        // [9.8] ±0.1 or [9.8] +-0.1
        preg_match('/^\[([\d\.,\s]+)\]\s*[±\+\-][=]?\s*([\d\.]+)/u', $rule, $m);
        $target    = isset($m[1]) ? (float) str_replace(',', '.', trim($m[1])) : 0.0;
        $tolerance = isset($m[2]) ? (float) $m[2] : 0.0;

        return [
            'type'      => 'numerical',
            'target'    => $target,
            'tolerance' => $tolerance,
            'min'       => $target - $tolerance,
            'max'       => $target + $tolerance,
            'max_marks' => $maxMarks,
            'raw'       => $rule,
        ];
    }

    protected static function parseCriteria(string $rule, float $maxMarks): array
    {
        // + [Keyword] @marks + [Keyword2] @marks2 ...
        preg_match_all('/\+\s*\[(.+?)\]\s*@([\d\.]+)/u', $rule, $matches, PREG_SET_ORDER);
        $criteria = [];
        foreach ($matches as $match) {
            $keywords = array_map(
                fn($k) => self::normalizeText(trim($k)),
                explode(',', $match[1])
            );
            $criteria[] = [
                'keywords'  => array_filter($keywords, fn($k) => $k !== ''),
                'marks'     => (float) $match[2],
            ];
        }

        // Cap total criteria marks at maxMarks
        $totalCriteriaMarks = array_sum(array_column($criteria, 'marks'));

        return [
            'type'                 => 'criteria',
            'criteria'             => $criteria,
            'total_criteria_marks' => $totalCriteriaMarks,
            'max_marks'            => $maxMarks,
            'raw'                  => $rule,
        ];
    }

    protected static function parseMultiRequired(string $rule, float $maxMarks): array
    {
        // [A];[B];[C] — extract each segment between [ ]
        preg_match_all('/\[(.+?)\]/u', $rule, $matches);
        $parts = [];
        foreach ($matches[1] as $segment) {
            // Each segment may itself contain alternatives (comma separated)
            $alts = array_values(array_filter(
                array_map(fn($a) => self::normalizeText(trim($a)), explode(',', $segment)),
                fn($a) => $a !== ''
            ));
            if (!empty($alts)) {
                $parts[] = $alts;
            }
        }

        $marksPerPart = count($parts) > 0 ? ($maxMarks / count($parts)) : $maxMarks;

        return [
            'type'          => 'multi_required',
            'parts'         => $parts,          // [[alt1, alt2], [alt1], ...]
            'marks_per_part'=> $marksPerPart,
            'max_marks'     => $maxMarks,
            'raw'           => $rule,
        ];
    }

    protected static function parseAlternatives(string $rule, float $maxMarks): array
    {
        // [A, B, C] — extract the inner content and split by comma
        preg_match('/^\[(.+)\]$/su', $rule, $m);
        $inner = $m[1] ?? $rule;
        $alts  = array_values(array_filter(
            array_map(fn($a) => self::normalizeText(trim($a)), explode(',', $inner)),
            fn($a) => $a !== ''
        ));

        return [
            'type'         => 'alternatives',
            'alternatives' => $alts,
            'max_marks'    => $maxMarks,
            'raw'          => $rule,
        ];
    }

    protected static function parseLegacy(string $rule, float $maxMarks): array
    {
        // Backward-compatible: plain comma/slash/pipe-separated alternatives
        $alts = array_values(array_filter(
            array_map(
                fn($a) => self::normalizeText(trim(preg_replace('/[.\,!?]+$/u', '', $a))),
                preg_split('/[,|\/]+/u', $rule)
            ),
            fn($a) => $a !== ''
        ));

        return [
            'type'         => 'legacy',
            'alternatives' => $alts,
            'max_marks'    => $maxMarks,
            'raw'          => $rule,
        ];
    }

    // ── Utility ───────────────────────────────────────────────────────────────

    /**
     * Normalize text for comparison: lowercase, trim, collapse whitespace,
     * strip trailing punctuation.
     */
    public static function normalizeText(string $text, bool $preserveCase = false): string
    {
        // Normalize whitespace (tabs, multiple spaces → single space)
        $normalized = preg_replace('/\s+/u', ' ', trim($text));
        // Strip trailing punctuation
        $normalized = preg_replace('/[\.\,\!\?\;]+$/u', '', $normalized);
        // Trim again after stripping
        $normalized = trim($normalized);

        return $preserveCase ? $normalized : mb_strtolower($normalized, 'UTF-8');
    }

    /**
     * Calculate Levenshtein similarity as a ratio 0.0–1.0.
     * Handles multibyte strings via character array conversion.
     */
    public static function similarity(string $a, string $b): float
    {
        if ($a === $b) {
            return 1.0;
        }
        $lenA = mb_strlen($a, 'UTF-8');
        $lenB = mb_strlen($b, 'UTF-8');
        if ($lenA === 0 && $lenB === 0) {
            return 1.0;
        }
        if ($lenA === 0 || $lenB === 0) {
            return 0.0;
        }

        // Use PHP's native levenshtein() (only works on ASCII, but fine for typical answers)
        $dist = levenshtein(mb_strtolower($a, 'UTF-8'), mb_strtolower($b, 'UTF-8'));
        $maxLen = max($lenA, $lenB);

        return 1.0 - ($dist / $maxLen);
    }

    /**
     * Extract a numeric value from a student answer string,
     * stripping units, commas, and other non-numeric characters.
     */
    public static function extractNumber(string $text): ?float
    {
        // Match the first decimal/integer number (possibly with leading -)
        if (preg_match('/[-]?[\d]+(?:[,\.][\d]+)?/', $text, $m)) {
            // Normalize comma-as-decimal (European notation)
            $val = str_replace(',', '.', $m[0]);
            return (float) $val;
        }
        return null;
    }
}
