<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Deactivate Kindergarten subjects (held temporarily)
        DB::table('subjects')
            ->where('education_type', 'kindergarten')
            ->update(['is_active' => false]);

        // 2. Define standard curriculum subjects per individual year/form
        $order = (DB::table('subjects')->max('order') ?? 0) + 1;

        // Primary: Year 4, Year 5, Year 6
        $primarySubjects = [
            ['name' => 'Bahasa Melayu', 'category' => 'Core', 'stream_package' => 'General', 'fee' => 60.00],
            ['name' => 'English',       'category' => 'Core', 'stream_package' => 'General', 'fee' => 60.00],
            ['name' => 'Mathematics',   'category' => 'Core', 'stream_package' => 'General', 'fee' => 60.00],
            ['name' => 'Science',       'category' => 'Core', 'stream_package' => 'General', 'fee' => 60.00],
            ['name' => 'Sejarah',       'category' => 'Core', 'stream_package' => 'General', 'fee' => 60.00],
        ];

        foreach (['Year 4', 'Year 5', 'Year 6'] as $year) {
            foreach ($primarySubjects as $subj) {
                $slug = Str::slug($subj['name'] . ' ' . $year);
                $codeYearNum = filter_var($year, FILTER_SANITIZE_NUMBER_INT);
                $code = 'KPM-P' . $codeYearNum . '-' . strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $subj['name']), 0, 3));

                $existing = DB::table('subjects')
                    ->where('education_type', 'primary')
                    ->where('name', $subj['name'])
                    ->where('form_year', $year)
                    ->first();

                if (!$existing) {
                    DB::table('subjects')->insert([
                        'name'           => $subj['name'],
                        'slug'           => $slug,
                        'education_type' => 'primary',
                        'stage'          => 'Primary',
                        'form_year'      => $year,
                        'category'       => $subj['category'],
                        'stream_package' => $subj['stream_package'],
                        'code'           => $code,
                        'description'    => "{$subj['name']} curriculum syllabus for {$year} (KSSR Semakan).",
                        'level'          => 'Primary',
                        'color'          => '#3b82f6',
                        'icon'           => 'academic-cap',
                        'is_active'      => true,
                        'monthly_fee'    => $subj['fee'],
                        'order'          => $order++,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                } else {
                    DB::table('subjects')->where('id', $existing->id)->update([
                        'is_active'   => true,
                        'monthly_fee' => $subj['fee'],
                    ]);
                }
            }
        }

        // Lower Secondary: Form 1, Form 2, Form 3
        $lowerSecSubjects = [
            ['name' => 'Bahasa Melayu', 'category' => 'Core', 'stream_package' => 'General', 'fee' => 70.00],
            ['name' => 'English',       'category' => 'Core', 'stream_package' => 'General', 'fee' => 70.00],
            ['name' => 'Mathematics',   'category' => 'Core', 'stream_package' => 'General', 'fee' => 70.00],
            ['name' => 'Science',       'category' => 'Core', 'stream_package' => 'General', 'fee' => 70.00],
            ['name' => 'Sejarah',       'category' => 'Core', 'stream_package' => 'General', 'fee' => 70.00],
        ];

        foreach (['Form 1', 'Form 2', 'Form 3'] as $form) {
            foreach ($lowerSecSubjects as $subj) {
                $slug = Str::slug($subj['name'] . ' ' . $form);
                $formNum = filter_var($form, FILTER_SANITIZE_NUMBER_INT);
                $code = 'KPM-F' . $formNum . '-' . strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $subj['name']), 0, 3));

                $existing = DB::table('subjects')
                    ->where('education_type', 'secondary')
                    ->where('stage', 'Lower Secondary')
                    ->where('name', $subj['name'])
                    ->where('form_year', $form)
                    ->first();

                if (!$existing) {
                    DB::table('subjects')->insert([
                        'name'           => $subj['name'],
                        'slug'           => $slug,
                        'education_type' => 'secondary',
                        'stage'          => 'Lower Secondary',
                        'form_year'      => $form,
                        'category'       => $subj['category'],
                        'stream_package' => $subj['stream_package'],
                        'code'           => $code,
                        'description'    => "{$subj['name']} curriculum syllabus for {$form} (KSSM).",
                        'level'          => 'Lower Secondary',
                        'color'          => '#3b82f6',
                        'icon'           => 'academic-cap',
                        'is_active'      => true,
                        'monthly_fee'    => $subj['fee'],
                        'order'          => $order++,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                } else {
                    DB::table('subjects')->where('id', $existing->id)->update([
                        'is_active'   => true,
                        'monthly_fee' => $subj['fee'],
                    ]);
                }
            }
        }

        // Upper Secondary: Form 4, Form 5 (SPM)
        $upperSecSubjects = [
            // Core
            ['name' => 'Bahasa Melayu',          'category' => 'Core',     'stream_package' => 'General',               'fee' => 80.00],
            ['name' => 'English',                'category' => 'Core',     'stream_package' => 'General',               'fee' => 80.00],
            ['name' => 'Mathematics',            'category' => 'Core',     'stream_package' => 'General',               'fee' => 80.00],
            ['name' => 'Sejarah',                'category' => 'Core',     'stream_package' => 'General',               'fee' => 80.00],
            // Electives
            ['name' => 'Physics',                'category' => 'Elective', 'stream_package' => 'STEM Pure Science',     'fee' => 90.00],
            ['name' => 'Chemistry',              'category' => 'Elective', 'stream_package' => 'STEM Pure Science',     'fee' => 90.00],
            ['name' => 'Biology',                'category' => 'Elective', 'stream_package' => 'STEM Pure Science',     'fee' => 90.00],
            ['name' => 'Additional Mathematics', 'category' => 'Elective', 'stream_package' => 'STEM Pure Science',     'fee' => 90.00],
            ['name' => 'Computer Science',       'category' => 'Elective', 'stream_package' => 'STEM Computing',        'fee' => 90.00],
            ['name' => 'Accounting',             'category' => 'Elective', 'stream_package' => 'Commerce & Accounting', 'fee' => 80.00],
            ['name' => 'Economics',              'category' => 'Elective', 'stream_package' => 'Commerce & Accounting', 'fee' => 80.00],
        ];

        foreach (['Form 4', 'Form 5'] as $form) {
            foreach ($upperSecSubjects as $subj) {
                $slug = Str::slug($subj['name'] . ' ' . $form);
                $formNum = filter_var($form, FILTER_SANITIZE_NUMBER_INT);
                $code = 'KPM-SPM' . $formNum . '-' . strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $subj['name']), 0, 3));

                $existing = DB::table('subjects')
                    ->where('education_type', 'secondary')
                    ->where('stage', 'Upper Secondary')
                    ->where('name', $subj['name'])
                    ->where('form_year', $form)
                    ->first();

                if (!$existing) {
                    DB::table('subjects')->insert([
                        'name'           => $subj['name'],
                        'slug'           => $slug,
                        'education_type' => 'secondary',
                        'stage'          => 'Upper Secondary',
                        'form_year'      => $form,
                        'category'       => $subj['category'],
                        'stream_package' => $subj['stream_package'],
                        'code'           => $code,
                        'description'    => "{$subj['name']} curriculum syllabus for {$form} SPM (KSSM).",
                        'level'          => 'SPM',
                        'color'          => '#3b82f6',
                        'icon'           => 'academic-cap',
                        'is_active'      => true,
                        'monthly_fee'    => $subj['fee'],
                        'order'          => $order++,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                } else {
                    DB::table('subjects')->where('id', $existing->id)->update([
                        'is_active'   => true,
                        'monthly_fee' => $subj['fee'],
                    ]);
                }
            }
        }

        // 3. Mark old combined range subjects (Year 1–6, Form 1–3, Form 4–5) as inactive so they don't appear in new selections,
        // while preserving historical references in courses/invoices
        DB::table('subjects')
            ->whereIn('form_year', ['Year 1–6', 'Form 1–3', 'Form 4–5'])
            ->update(['is_active' => false]);
    }

    public function down(): void
    {
        // Re-enable combined ranges if rolled back
        DB::table('subjects')
            ->whereIn('form_year', ['Year 1–6', 'Form 1–3', 'Form 4–5'])
            ->update(['is_active' => true]);

        DB::table('subjects')
            ->where('education_type', 'kindergarten')
            ->update(['is_active' => true]);
    }
};
