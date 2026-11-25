<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\RecordedClass;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class RecordedClassController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->visibleRecordings()->with('subject');

        if ($request->has('subject') && $request->subject !== 'all') {
            $query->whereHas('subject', function ($q) use ($request) {
                $q->where('slug', $request->subject);
            });
        }

        $recordings = $query->orderByDesc('created_at')->get();
        $subjects = Subject::where('is_active', true)->get();

        return view('student.recordings.index', compact('recordings', 'subjects'));
    }

    public function show(int $id)
    {
        $recording = $this->visibleRecordings()->with('subject', 'course')->findOrFail($id);
        $recording->increment('views_count');
        $related = $this->visibleRecordings()->where('id', '!=', $recording->id)->latest()->take(3)->get();

        return view('student.recordings.show', compact('recording', 'related'));
    }

    /**
     * Recordings of the classes the student is enrolled in, plus subject-wide
     * recordings (no class) for those classes' subjects. Previously every
     * student could see every recording.
     *
     * @return Builder<RecordedClass>
     */
    private function visibleRecordings(): Builder
    {
        $studentId = Student::where('user_id', auth()->id())->value('id');
        $courseIds = $studentId
            ? Enrollment::where('student_id', $studentId)->pluck('course_id')
            : collect();
        $subjectIds = Course::whereIn('id', $courseIds)->pluck('subject_id')->filter()->unique();

        return RecordedClass::query()->where(function (Builder $q) use ($courseIds, $subjectIds) {
            $q->whereIn('course_id', $courseIds)
                ->orWhere(fn (Builder $q) => $q->whereNull('course_id')->whereIn('subject_id', $subjectIds));
        });
    }
}
