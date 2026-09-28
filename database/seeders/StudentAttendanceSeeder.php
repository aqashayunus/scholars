
<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\LiveClass;
use App\Models\StudentAttendance;
use Illuminate\Database\Seeder;

class StudentAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::all();
        $pastClasses = LiveClass::where('scheduled_at', '<', now())->get();

        if ($pastClasses->isEmpty()) {
            // Create a few past classes for demonstration
            $course = \App\Models\Course::first();
            if ($course) {
                for ($i = 1; $i <= 8; $i++) {
                    LiveClass::create([
                        'course_id' => $course->id,
                        'title' => 'Live Session: Module ' . $i,
                        'scheduled_at' => now()->startOfMonth()->addDays($i * 2)->setHour(20),
                        'duration_minutes' => 90,
                        'meeting_url' => 'https://meet.google.com/xyz-demo',
                        'status' => 'completed',
                    ]);
                }
                $pastClasses = LiveClass::where('scheduled_at', '<', now())->get();
            }
        }

        foreach ($students as $student) {
            foreach ($pastClasses as $idx => $lc) {
                $isAbsent = ($idx === 0);
                StudentAttendance::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'live_class_id' => $lc->id,
                    ],
                    [
                        'status' => $isAbsent ? 'absent' : 'attended',
                        'joined_at' => $isAbsent ? null : $lc->scheduled_at->copy()->addMinutes(2),
                    ]
                );
            }
        }
    }
}
