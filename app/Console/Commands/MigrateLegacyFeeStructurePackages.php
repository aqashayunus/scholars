<?php

namespace App\Console\Commands;

use App\Models\FeeStructure;
use App\Models\Subject;
use Illuminate\Console\Command;

class MigrateLegacyFeeStructurePackages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fee-structures:migrate-packages {--force : Apply migrations to database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely migrate legacy fee structures to the package engine without guessing ambiguous subjects';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting Fee Structure Package Migration...');
        $force = $this->option('force');

        $feeStructures = FeeStructure::with('subjects')->get();

        $rows = [];
        $migratedCount = 0;
        $skippedCount = 0;
        $needsReviewCount = 0;

        foreach ($feeStructures as $fee) {
            // If already has explicit subjects mapped, skip
            if ($fee->subjects->isNotEmpty()) {
                $rows[] = [
                    $fee->id,
                    $fee->name,
                    $fee->package_type ?? 'individual',
                    $fee->subjects->pluck('name')->implode(', '),
                    '<comment>Skipped</comment>',
                    'Already has explicit subject mappings (' . $fee->subjects->count() . ' subjects)',
                ];
                $skippedCount++;
                continue;
            }

            // Check if package is targeted to a single student
            if ($fee->student_id !== null) {
                $rows[] = [
                    $fee->id,
                    $fee->name,
                    $fee->package_type ?? 'individual',
                    '-',
                    '<comment>Skipped</comment>',
                    'Student-specific fee structure (targeted to student #' . $fee->student_id . ')',
                ];
                $skippedCount++;
                continue;
            }

            // Evaluate unambiguous safe mappings vs ambiguous packages
            $safeSubjectIds = $this->resolveSafeSubjectMapping($fee);

            if ($safeSubjectIds === null) {
                // Ambiguous mapping — DO NOT GUESS
                $rows[] = [
                    $fee->id,
                    $fee->name,
                    $fee->package_type ?? 'individual',
                    '-',
                    '<error>Needs Review</error>',
                    'Ambiguous subject scope (' . ($fee->education_level ?: 'No level') . '). Requires manual admin review.',
                ];
                $needsReviewCount++;
                continue;
            }

            // Safe mapping found
            $subjects = Subject::whereIn('id', $safeSubjectIds)->get();
            $subjectNames = $subjects->pluck('name')->implode(', ');

            if ($force) {
                $fee->update([
                    'package_type' => FeeStructure::TYPE_FIXED_BUNDLE,
                ]);

                $attachData = [];
                foreach ($safeSubjectIds as $idx => $sId) {
                    $attachData[$sId] = [
                        'is_required' => true,
                        'sort_order'  => $idx,
                    ];
                }
                $fee->subjects()->sync($attachData);

                $rows[] = [
                    $fee->id,
                    $fee->name,
                    FeeStructure::TYPE_FIXED_BUNDLE,
                    $subjectNames,
                    '<info>Migrated</info>',
                    'Successfully mapped to ' . count($safeSubjectIds) . ' core subjects as Fixed Bundle',
                ];
            } else {
                $rows[] = [
                    $fee->id,
                    $fee->name,
                    'individual -> ' . FeeStructure::TYPE_FIXED_BUNDLE,
                    $subjectNames,
                    '<info>Can Migrate</info>',
                    'Ready to map ' . count($safeSubjectIds) . ' subjects (run with --force to apply)',
                ];
            }

            $migratedCount++;
        }

        $this->table(
            ['ID', 'Fee Structure Name', 'Package Type', 'Subjects', 'Status', 'Details'],
            $rows
        );

        $this->newLine();
        $this->line("Summary:");
        $this->line("  <info>Migrated / Can Migrate:</info> {$migratedCount}");
        $this->line("  <comment>Skipped:</comment>              {$skippedCount}");
        $this->line("  <error>Needs Review:</error>         {$needsReviewCount}");
        $this->newLine();

        if (!$force && $migratedCount > 0) {
            $this->warn('DRY RUN: No database changes were written. Run with --force to apply migrations.');
        } elseif ($force) {
            $this->info('Migration completed successfully.');
        }

        return Command::SUCCESS;
    }

    /**
     * Determine if a legacy fee structure can be safely mapped to subjects without guessing.
     * Returns array of subject IDs if safe and unambiguous, or null if ambiguous.
     *
     * @param  FeeStructure $fee
     * @return int[]|null
     */
    private function resolveSafeSubjectMapping(FeeStructure $fee): ?array
    {
        $nameLower  = strtolower($fee->name);
        $levelLower = strtolower($fee->education_level ?? '');

        // Case 1: SPM Core Monthly Tuition with Form 4–5 education level
        if (str_contains($nameLower, 'spm') && str_contains($nameLower, 'core')) {
            $subjects = Subject::where('is_active', true)
                ->where('category', 'Core')
                ->where(function ($q) {
                    $q->where('form_year', 'like', '%Form 4%')
                      ->orWhere('form_year', 'like', '%Form 5%')
                      ->orWhere('form_year', 'Form 4–5')
                      ->orWhere('stage', 'Upper Secondary');
                })
                ->get();

            if ($subjects->isNotEmpty()) {
                // Ensure unique subject names so we don't duplicate Form 4 and Form 5 of the same name
                return $subjects->unique('name')->pluck('id')->values()->toArray();
            }
        }

        // Case 2: PT3 Lower Secondary Monthly Tuition with Form 1–3 education level
        if ((str_contains($nameLower, 'pt3') || str_contains($nameLower, 'lower secondary')) && str_contains($nameLower, 'core') || str_contains($nameLower, 'tuition')) {
            if ($levelLower === 'form 1–3' || $levelLower === 'form 1-3') {
                $subjects = Subject::where('is_active', true)
                    ->where('category', 'Core')
                    ->where(function ($q) {
                        $q->where('form_year', 'like', '%Form 1%')
                          ->orWhere('form_year', 'like', '%Form 2%')
                          ->orWhere('form_year', 'like', '%Form 3%')
                          ->orWhere('form_year', 'Form 1–3')
                          ->orWhere('stage', 'Lower Secondary');
                    })
                    ->get();

                if ($subjects->isNotEmpty()) {
                    return $subjects->unique('name')->pluck('id')->values()->toArray();
                }
            }
        }

        // All other cases (e.g. "Annual Digital Resource & Exam Drill Access", "Primary UPSR", "All" levels)
        // are ambiguous and must NOT be guessed.
        return null;
    }
}
