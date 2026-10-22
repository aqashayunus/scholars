<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AttendanceCorrectionRequest;
use App\Models\LiveClass;
use App\Models\UserNotification;
use App\Services\AttendanceService;
use App\Services\Teacher\AssignmentService;
use App\Services\Teacher\ClassScheduleService;
use App\Services\Teacher\GigService;
use App\Services\Teacher\MaterialService;
use App\Services\Teacher\OverviewService;
use App\Services\Teacher\RecordingService;
use Illuminate\Http\Request;

class TeacherDashboardController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // PAGES
    // ─────────────────────────────────────────────────────────────────────────

    public function dashboard(OverviewService $overview)
    {
        return view('teacher.dashboard', $overview->dashboardData());
    }

    public function classes(OverviewService $overview)
    {
        return view('teacher.classes', $overview->classesData());
    }

    public function manageClass($id, OverviewService $overview)
    {
        return view('teacher.class-workspace', $overview->manageClassData((int) $id));
    }

    public function students(OverviewService $overview)
    {
        return view('teacher.students', $overview->studentsData());
    }

    public function learningMaterials(MaterialService $materials)
    {
        return view('teacher.learning-materials', $materials->indexData());
    }

    public function tasks(AssignmentService $assignments)
    {
        return view('teacher.tasks', $assignments->tasksData());
    }

    public function monitoring(OverviewService $overview)
    {
        return view('teacher.monitoring', $overview->monitoringData());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ACTIONS
    // ─────────────────────────────────────────────────────────────────────────

    public function toggleDuty(Request $request, ClassScheduleService $schedule)
    {
        $teacher = $schedule->toggleDuty();

        $statusLabel = $teacher->is_online ? 'ON DUTY (Online for Gigs & Scheduled Sessions)' : 'OFF DUTY';
        return redirect()->back()->with('success', "Duty status updated to {$statusLabel}.");
    }

    public function gigsMarketplace(GigService $gigs)
    {
        return view('teacher.gigs.index', $gigs->marketplaceData());
    }

    public function claimGig($id, GigService $gigs)
    {
        $gig = $gigs->claim((int) $id);

        return redirect()->route('teacher.gigs')->with('success', "Teaching Gig '{$gig->title}' claimed successfully!");
    }

    public function checkInGig($id, GigService $gigs)
    {
        $gig = $gigs->checkIn((int) $id);

        return redirect()->back()->with('success', "Checked in live for '{$gig->title}'. Session room is open.");
    }

    public function checkOutGig($id, GigService $gigs)
    {
        $gigs->checkOut((int) $id);

        return redirect()->back()->with('success', "Session completed! Completion timestamp recorded for Aster Edu.");
    }

    public function walletIndex(GigService $gigs)
    {
        return view('teacher.wallet.index', $gigs->walletData());
    }

    public function storeClass(Request $request, ClassScheduleService $schedule)
    {
        $schedule->storeClass($request);

        return redirect()->back()->with('success', 'New class session scheduled successfully!');
    }

    public function requestCancel(Request $request, $id, ClassScheduleService $schedule)
    {
        $schedule->requestCancel((int) $id, $request);

        return redirect()->back()->with('success', 'Cancellation request submitted to Administrator for approval.');
    }

    public function requestReschedule(Request $request, $id, ClassScheduleService $schedule)
    {
        $schedule->requestReschedule((int) $id, $request);

        return redirect()->back()->with('success', 'Reschedule request submitted to Administrator for approval.');
    }

    public function updateMeetingUrl(Request $request, $id, ClassScheduleService $schedule)
    {
        $liveClass = $schedule->updateMeetingUrl($request, $id);

        if ($request->wantsJson()) {
            return response()->json([
                'success'     => true,
                'meeting_url' => $liveClass->meeting_url,
            ]);
        }

        return redirect()->back()->with('success', 'Meeting link updated and class marked live.');
    }

    public function saveAttendance(LiveClass $liveClass, Request $request, ClassScheduleService $schedule, AttendanceService $attendanceService)
    {
        $result = $schedule->saveAttendance($liveClass, $request, $attendanceService);

        return response()->json($result);
    }

    public function reviewCorrection(AttendanceCorrectionRequest $correctionRequest, Request $request, ClassScheduleService $schedule, AttendanceService $attendanceService)
    {
        $action = $schedule->reviewCorrection($correctionRequest, $request, $attendanceService);

        return response()->json([
            'success' => true,
            'message' => "Attendance correction request {$action} successfully.",
        ]);
    }

    public function storeTask(Request $request, $id, AssignmentService $assignments)
    {
        $assignments->storeTask((int) $id, $request);

        return redirect()->back()->with('success', 'New task/assignment with material attachment assigned to this class successfully!');
    }

    public function storeGeneralTask(Request $request, AssignmentService $assignments)
    {
        $result = $assignments->storeGeneralTask($request);
        $itemType = $result['itemType'];
        $title = $result['assignment']->title;

        return redirect()->back()->with('success', "{$itemType} '{$title}' created and assigned successfully!");
    }

    public function storeQuiz(Request $request, AssignmentService $assignments)
    {
        $result = $assignments->storeQuiz($request);

        return redirect()->back()->with('success', "Quiz '{$result['quiz']->title}' with {$result['totalQuestions']} questions created successfully!");
    }

    public function destroyQuiz($id, AssignmentService $assignments)
    {
        $assignments->destroyQuiz((int) $id);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Quiz deleted successfully.'
            ]);
        }

        return redirect()->back()->with('success', 'Quiz deleted successfully.');
    }

    public function storeMaterial(Request $request, MaterialService $materials)
    {
        $material = $materials->store($request);

        return redirect()->back()->with('success', "Learning material '{$material->title}' uploaded and saved successfully!");
    }

    public function destroyMaterial($id, MaterialService $materials)
    {
        $materials->destroy((int) $id);

        return redirect()->back()->with('success', 'Learning material deleted successfully.');
    }

    public function downloadMaterial($id, MaterialService $materials)
    {
        return $materials->download((int) $id);
    }

    public function updateMaterial(Request $request, $id, MaterialService $materials)
    {
        $material = $materials->update((int) $id, $request);

        return redirect()->back()->with('success', "Learning material '{$material->title}' updated successfully!");
    }

    public function updateTask(Request $request, $id, AssignmentService $assignments)
    {
        $assignment = Assignment::findOrFail($id);
        $result = $assignments->updateTask($assignment, $request);

        return redirect()->back()->with('success', "{$result['itemType']} '{$result['assignment']->title}' updated successfully!");
    }

    public function destroyTask($id, AssignmentService $assignments)
    {
        $assignments->destroyTask((int) $id);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Task/Assignment deleted successfully.'
            ]);
        }

        return redirect()->back()->with('success', 'Task/Assignment deleted successfully.');
    }

    public function gradeSubmission(Request $request, $assignmentId, $studentId, AssignmentService $assignments)
    {
        $sub = $assignments->gradeSubmission((int) $assignmentId, (int) $studentId, $request);
        $studentName = $sub->student?->user?->name ?? 'Student';

        return redirect()->back()->with('success', "Submission for {$studentName} graded successfully ({$sub->score}/100)!");
    }

    public function readAllNotifications()
    {
        UserNotification::where('user_id', auth()->id())
                        ->whereNull('read_at')
                        ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function recordings(Request $request, RecordingService $recordings)
    {
        return view('teacher.recordings', $recordings->indexData());
    }

    public function storeRecording(Request $request, RecordingService $recordings)
    {
        $recording = $recordings->store($request);

        return redirect()->back()->with('success', "Class recording '{$recording->title}' uploaded successfully and is now available in the Study Vault!");
    }

    public function updateRecording(Request $request, $id, RecordingService $recordings)
    {
        $recording = $recordings->update((int) $id, $request);

        return redirect()->back()->with('success', "Class recording '{$recording->title}' updated successfully!");
    }

    public function destroyRecording($id, RecordingService $recordings)
    {
        $recordings->destroy((int) $id);

        return redirect()->back()->with('success', 'Recording removed successfully.');
    }
}
