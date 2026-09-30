<?php

namespace App\Services\Student;

use App\Models\LiveClass;
use App\Models\Student;
use App\Models\StudentAttendance;

class ClassScheduleService
{
    /**
     * Builds the full view-model for the student's Classes/Timetable page.
     */
    public function classesData(Student $student): array
    {
        $liveClasses = LiveClass::whereHas('course', function ($q) use ($student) {
                $q->whereHas('enrollments', fn ($q2) => $q2->where('student_id', $student->id));
            })
            ->with('course.subject', 'course.teacher.user')
            ->orderBy('scheduled_at')
            ->get();

        $now = now();
        $weekDays = collect();
        for ($i = 0; $i < 7; $i++) {
            $day = $now->copy()->addDays($i);
            $weekDays->push([
                'date'    => $day,
                'label'   => $day->format('D'),
                'full'    => $day->format('l'),
                'dateStr' => $day->format('d M'),
                'isToday' => $day->isToday(),
                'classes' => $liveClasses->filter(fn ($lc) =>
                    $lc->scheduled_at->format('Y-m-d') === $day->format('Y-m-d')
                )->values(),
            ]);
        }

        $upcomingClasses = $liveClasses->filter(fn ($lc) => $lc->scheduled_at->gte($now))->values();
        $pastClasses     = $liveClasses->filter(fn ($lc) => $lc->scheduled_at->lt($now))->sortByDesc('scheduled_at')->values();

        // Student's recorded attendances for live classes
        $attendanceMap = StudentAttendance::where('student_id', $student->id)
            ->pluck('status', 'live_class_id');

        // Dynamic Month Attendance Analytics from unified helper
        $attendanceStats = $this->attendanceStats($student);

        return [
            'weekDays'         => $weekDays,
            'upcomingClasses'  => $upcomingClasses,
            'pastClasses'      => $pastClasses,
            'liveClasses'      => $liveClasses,
            'attendanceStats'  => $attendanceStats,
            'attendanceMap'    => $attendanceMap,
        ];
    }

    /**
     * Shared attendance-rate calculation used by the dashboard, classes, and
     * progress pages — kept as one method so all three stay consistent.
     */
    public function attendanceStats(Student $student): array
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth   = now()->endOfMonth();

        // All live classes this month for student's enrolled courses
        $monthLiveClasses = LiveClass::whereHas('course', function ($q) use ($student) {
            $q->whereHas('enrollments', fn ($q2) => $q2->where('student_id', $student->id));
        })
        ->whereBetween('scheduled_at', [$startOfMonth, $endOfMonth])
        ->get();

        $totalClasses = $monthLiveClasses->count();

        // Past classes this month
        $pastClassIds = $monthLiveClasses->where('scheduled_at', '<', now())->pluck('id');

        $attendanceRecords = StudentAttendance::where('student_id', $student->id)
            ->whereIn('live_class_id', $pastClassIds)
            ->get();

        $attended = $attendanceRecords->whereIn('status', ['attended', 'late'])->count();
        $absent   = $attendanceRecords->where('status', 'absent')->count();
        $late     = $attendanceRecords->where('status', 'late')->count();

        $totalPast = $pastClassIds->count();
        $rate = $totalPast > 0 ? (int) round(($attended / $totalPast) * 100) : 0;

        return [
            'total'    => $totalClasses,
            'attended' => $attended,
            'absent'   => $absent,
            'late'     => $late,
            'pct'      => $rate,
        ];
    }
}
