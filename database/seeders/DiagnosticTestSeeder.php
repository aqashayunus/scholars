<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Student;
use App\Models\StudentAssignment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class DiagnosticTestSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure storage directory exists
        Storage::disk('public')->makeDirectory('assignments');

        $sourcePdf = storage_path('app/public/task_attachments/SjCGNuG45j4CHrpanmnLMlDvZ0NTbUjtjYAergTT.pdf');
        $targetRelativePath = 'assignments/Ujian_Diagnostik_Kemasukan_2026.pdf';
        $targetFullPath = storage_path('app/public/' . $targetRelativePath);

        if (File::exists($sourcePdf) && !File::exists($targetFullPath)) {
            File::copy($sourcePdf, $targetFullPath);
        }

        // Answer Key for 10 Questions
        $answerKey = [
            '1'  => 'A',
            '2'  => 'C',
            '3'  => 'B',
            '4'  => 'D',
            '5'  => 'A',
            '6'  => 'C',
            '7'  => 'D',
            '8'  => 'B',
            '9'  => 'A',
            '10' => 'C',
        ];

        $diagnostic = Assignment::updateOrCreate([
            'type'      => 'Diagnostic Test',
            'is_global' => true,
        ], [
            'title'              => 'Ujian Diagnostik Kemasukan 2026',
            'target_level'       => 'All Levels',
            'created_by_admin'   => true,
            'topic'              => 'Penilaian Tahap Penguasaan & Kecekapan Akademik Pelajar',
            'total_marks'        => 100,
            'total_questions'    => 10,
            'time_limit_minutes' => 20,
            'answer_key'         => $answerKey,
            'instructions'       => 'Sila baca soalan pada fail PDF yang dilampirkan dan tandakan pilihan jawapan (A, B, C atau D) pada borang jawapan digital. Markah anda akan diproses serta-merta.',
            'description'        => 'Ujian Diagnostik rasmi Olympia Scholars untuk menilai tahap penguasaan asas pelajar dalam subjek teras.',
            'file_path'          => File::exists($targetFullPath) ? $targetRelativePath : (File::exists($sourcePdf) ? 'task_attachments/SjCGNuG45j4CHrpanmnLMlDvZ0NTbUjtjYAergTT.pdf' : null),
            'file_name'          => 'Ujian_Diagnostik_Kemasukan_2026.pdf',
            'file_size'          => 245000,
        ]);

        // Sync to all existing students
        $students = Student::all();
        foreach ($students as $student) {
            StudentAssignment::firstOrCreate([
                'student_id'    => $student->id,
                'assignment_id' => $diagnostic->id,
            ], [
                'status' => 'pending',
            ]);
        }
    }
}
