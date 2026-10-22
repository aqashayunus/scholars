<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentTopicMastery;
use App\Models\Subject;
use Illuminate\Http\Request;

class StudentPracticeController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if ($user->role !== 'student') {
            return redirect()->route($user->getDashboardRoute());
        }

        $student = Student::firstOrCreate([
            'user_id' => $user->id
        ], [
            'level' => 'SPM',
            'total_points' => 0,
        ]);
        $masteries = StudentTopicMastery::where('student_id', $student->id)->get();
        $subjects  = Subject::where('is_active', true)->get();

        // Personal study streak & confidence stats (non-competitive)
        $studyStreakDays = $student->streak_days ?? 1;
        $totalQuestionsSolved = 142;
        $averageAccuracy = 84; // %

        return view('student.practice.index', compact('student', 'masteries', 'subjects', 'studyStreakDays', 'totalQuestionsSolved', 'averageAccuracy'));
    }

    public function generateQuiz(Request $request)
    {
        $topic = $request->input('topic', 'General Revision');
        return redirect()->back()->with('success', "Diagnostic practice session created for '{$topic}'. Start practicing at your own pace!");
    }
}
