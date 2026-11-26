<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\Admin\CourseRosterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * JSON endpoints behind the Course Catalog "Students" roster modal.
 */
class CourseRosterController extends Controller
{
    public function __construct(private CourseRosterService $roster) {}

    public function index(int $id): JsonResponse
    {
        $course = Course::findOrFail($id);

        return response()->json(['course' => ['id' => $course->id, 'title' => $course->title]] + $this->roster->roster($course));
    }

    public function store(Request $request, int $id): JsonResponse
    {
        $course = Course::findOrFail($id);
        $validated = $request->validate(['student_id' => 'required|integer|exists:students,id']);

        $this->roster->enrol($course, (int) $validated['student_id']);

        return response()->json([
            'message' => 'Student added to the class. No invoice was created; bill the new subject from Billing if needed.',
        ] + $this->roster->roster($course));
    }

    public function destroy(int $id, int $studentId): JsonResponse
    {
        $course = Course::findOrFail($id);

        $this->roster->remove($course, $studentId);

        return response()->json(['message' => 'Student removed from the class.'] + $this->roster->roster($course));
    }
}
