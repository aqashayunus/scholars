<?php

namespace App\Services\Admin;

use App\Models\Setting;
use App\Models\StudentAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceService
{
    /**
     * Update institutional attendance policy settings.
     */
    public function updateAttendanceSettings(Request $request): void
    {
        $validated = $request->validate([
            'late_threshold_minutes'    => 'required|integer|min:1|max:120',
            'early_join_window_minutes' => 'required|integer|min:0|max:120',
            'excused_in_denominator'    => 'nullable',
        ]);

        Setting::set('attendance.late_threshold_minutes', $validated['late_threshold_minutes'], 'attendance', 'Late threshold in minutes');
        Setting::set('attendance.early_join_window_minutes', $validated['early_join_window_minutes'], 'attendance', 'Early join window in minutes');
        Setting::set('attendance.excused_in_denominator', $request->has('excused_in_denominator') ? '1' : '0', 'attendance', 'Include excused in attendance rate denominator');
    }

    /**
     * Stream CSV export of attendance records.
     */
    public function exportAttendanceCsv(Request $request): StreamedResponse
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', now()->endOfMonth()->toDateString());
        $status    = $request->input('status');
        $courseId  = $request->input('course_id');
        $teacherId = $request->input('teacher_id');

        $query = StudentAttendance::with([
            'student.user',
            'liveClass.course.subject',
            'liveClass.teacher.user',
            'verifier',
        ])->whereHas('liveClass', function ($q) use ($startDate, $endDate, $courseId, $teacherId) {
            $q->whereBetween('scheduled_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ]);
            if ($courseId) {
                $q->where('course_id', $courseId);
            }
            if ($teacherId) {
                $q->where('teacher_id', $teacherId);
            }
        });

        if ($status) {
            $query->where('status', $status);
        }

        $records = $query->orderBy('created_at', 'desc')->get();

        return response()->streamDownload(function () use ($records) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Attendance ID',
                'Student Name',
                'Student Email',
                'Subject',
                'Course',
                'Live Class Title',
                'Scheduled At',
                'Joined At',
                'Status',
                'Verified By',
                'Verified At',
                'Remarks',
            ]);

            foreach ($records as $r) {
                fputcsv($handle, [
                    '#' . $r->id,
                    $r->student?->user?->name ?? 'Student #' . $r->student_id,
                    $r->student?->user?->email ?? '',
                    $r->liveClass?->course?->subject?->name ?? 'General',
                    $r->liveClass?->course?->title ?? '',
                    $r->liveClass?->title ?? 'Session',
                    $r->liveClass?->scheduled_at ? $r->liveClass->scheduled_at->format('Y-m-d H:i') : '',
                    $r->joined_at ? $r->joined_at->format('Y-m-d H:i:s') : 'Not Joined',
                    ucfirst(str_replace('_', ' ', $r->status)),
                    $r->verifier?->name ?? ($r->verified_at ? 'System' : 'Unverified'),
                    $r->verified_at ? $r->verified_at->format('Y-m-d H:i') : '',
                    $r->remarks ?? '',
                ]);
            }

            fclose($handle);
        }, 'attendance-export-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
