<?php

namespace App\Services\Teacher;

use App\Models\Course;
use App\Models\LearningMaterial;
use App\Models\Student;
use App\Models\StudentAssignment;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MaterialService
{
    private function getTeacher(bool $withEnrollments = true): Teacher
    {
        if (!$withEnrollments) {
            return Teacher::where('user_id', auth()->id())->firstOrFail();
        }

        return Teacher::where('user_id', auth()->id())
                      ->with(['courses.subject', 'courses.enrollments', 'courses.learningMaterials'])
                      ->firstOrFail();
    }

    public function indexData(): array
    {
        $teacher = $this->getTeacher();

        $courses = $teacher->courses;
        $courseIds = $courses->pluck('id');

        $realMaterials = LearningMaterial::whereIn('course_id', $courseIds)
                                         ->with(['course.subject', 'course.enrollments', 'subject', 'topicItem'])
                                         ->latest()
                                         ->get();

        $materials = [];

        foreach ($realMaterials as $mat) {
            $course = $mat->course;
            $enrollmentCount = $course ? $course->enrollments->count() : 0;
            $ext = strtoupper($mat->file_type ?: 'PDF');

            $typeBg = match($mat->type) {
                'Notes' => 'bg-blue-50 text-blue-700',
                'Worksheet' => 'bg-emerald-50 text-emerald-700',
                'Slides' => 'bg-amber-50 text-amber-700',
                'Document' => 'bg-purple-50 text-purple-700',
                default => 'bg-gray-100 text-gray-700',
            };

            $iconBg = match($mat->type) {
                'Notes' => 'bg-blue-100 text-blue-700',
                'Worksheet' => 'bg-emerald-100 text-emerald-700',
                'Slides' => 'bg-amber-100 text-amber-700',
                'Document' => 'bg-purple-100 text-purple-700',
                default => 'bg-gray-100 text-gray-700',
            };

            $iconSvg = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>';

            $materials[] = [
                'id'          => $mat->id,
                'name'        => $mat->title,
                'ext'         => $ext,
                'icon'        => $iconSvg,
                'iconBg'      => $iconBg,
                'class'       => $course->level ?? 'SPM',
                'subject'     => $mat->subject->name ?? $course->subject->name ?? 'General',
                'topic'       => $mat->topicItem->title ?? $mat->topic ?? 'General',
                'type'        => $mat->type,
                'typeBg'      => $typeBg,
                'status'      => $mat->status,
                'statusBg'    => $mat->status === 'Published' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700',
                'updated'     => $mat->updated_at ? $mat->updated_at->format('d M Y') : 'Recent',
                'size'        => $mat->formatted_size,
                'course_id'   => $mat->course_id,
                'subject_id'  => $mat->subject_id,
                'topic_id'    => $mat->topic_id,
                'description' => $mat->description,
                'file_name'   => $mat->file_name,
                'download_url'=> route('teacher.materials.download', $mat->id),
                'delete_url'  => route('teacher.materials.destroy', $mat->id),
                'viewed'      => "{$enrollmentCount} / {$enrollmentCount}",
                'pct'         => 100,
                'is_real'     => true,
            ];
        }

        if (empty($materials)) {
            foreach ($courses as $course) {
                $enrollmentCount = $course->enrollments->count() ?: 1;
                foreach ($course->assignments as $assignment) {
                    $submitted = StudentAssignment::where('assignment_id', $assignment->id)->count();
                    $pct = round(($submitted / $enrollmentCount) * 100);
                    $materials[] = [
                        'id'          => 'a_' . $assignment->id,
                        'name'        => $assignment->title,
                        'ext'         => 'PDF',
                        'icon'        => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                        'iconBg'      => 'bg-purple-50 text-purple-600',
                        'class'       => $course->level,
                        'subject'     => $course->subject->name ?? 'General',
                        'topic'       => $assignment->description ? substr($assignment->description, 0, 40) : 'General',
                        'type'        => 'Assignment',
                        'typeBg'      => 'bg-purple-50 text-purple-700',
                        'status'      => $assignment->due_date && $assignment->due_date->isPast() ? 'Completed' : 'Published',
                        'statusBg'    => 'bg-emerald-50 text-emerald-700',
                        'updated'     => $assignment->updated_at->format('d M Y'),
                        'size'        => '1.2 MB',
                        'file_name'   => $assignment->title . '.pdf',
                        'download_url'=> '#',
                        'delete_url'  => '#',
                        'viewed'      => "{$submitted} / {$enrollmentCount}",
                        'pct'         => $pct,
                        'is_real'     => false,
                    ];
                }
            }
        }

        $mappedMaterials = collect($materials)->values()->all();
        $subjects = Subject::where('is_active', true)->with('topics')->get();

        return compact('teacher', 'courses', 'mappedMaterials', 'subjects');
    }

    public function store(Request $request): LearningMaterial
    {
        $teacher = $this->getTeacher(false);
        $courseIds = $teacher->courses->pluck('id')->toArray();

        $validated = $request->validate([
            'course_id'   => 'required|in:' . implode(',', $courseIds),
            'title'       => 'required|string|max:255',
            'type'        => 'required|string|in:Notes,Worksheet,Slides,Document,Assignment,Past Paper',
            'topic'       => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'file'        => 'required|file|max:51200', // 50MB
            'visibility'  => 'nullable|string|in:all,selected',
            'status'      => 'nullable|string|in:Published,Draft,Scheduled',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $fileExt = strtolower($file->getClientOriginalExtension() ?: 'pdf');

        $filePath = $file->store('materials', 'public');

        $material = LearningMaterial::create([
            'course_id'   => $validated['course_id'],
            'teacher_id'  => $teacher->id,
            'title'       => $validated['title'],
            'type'        => $validated['type'],
            'topic'       => ($validated['topic'] ?? null) ?: 'General',
            'description' => $validated['description'] ?? null,
            'file_path'   => $filePath,
            'file_name'   => $originalName,
            'file_size'   => $fileSize,
            'file_type'   => $fileExt,
            'visibility'  => $validated['visibility'] ?? 'all',
            'status'      => $validated['status'] ?? 'Published',
        ]);

        $course = Course::find($validated['course_id']);
        if ($course) {
            foreach ($course->enrollments as $enrollment) {
                if ($enrollment->student?->user_id) {
                    UserNotification::create([
                        'user_id' => $enrollment->student->user_id,
                        'type'    => 'material',
                        'title'   => 'New Learning Material',
                        'message' => "Your teacher uploaded {$validated['type']}: '{$validated['title']}' for " . ($course->subject->name ?? 'your class') . ".",
                    ]);
                }
            }
        }

        UserNotification::create([
            'user_id' => auth()->id(),
            'type'    => 'material',
            'title'   => 'Material Uploaded',
            'message' => "You successfully uploaded {$validated['type']} '{$validated['title']}' for " . ($course->title ?? 'Class') . ".",
        ]);

        return $material;
    }

    public function destroy(int $id): void
    {
        $teacher = $this->getTeacher(false);
        $courseIds = $teacher->courses->pluck('id')->toArray();

        $material = LearningMaterial::where('id', $id)
                                    ->whereIn('course_id', $courseIds)
                                    ->firstOrFail();

        if ($material->file_path && Storage::disk('public')->exists($material->file_path)) {
            Storage::disk('public')->delete($material->file_path);
        }

        $material->delete();
    }

    public function download(int $id)
    {
        $material = LearningMaterial::findOrFail($id);

        $user = auth()->user();
        if (!$user) {
            abort(401, 'Please log in to download learning materials.');
        }

        $authorized = false;
        if ($user->isAdmin() || $user->isParent()) {
            $authorized = true;
        } elseif ($user->isTeacher()) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if ($teacher && ($material->teacher_id == $teacher->id || $teacher->courses()->where('id', $material->course_id)->exists())) {
                $authorized = true;
            }
        } elseif ($user->isStudent()) {
            $student = Student::where('user_id', $user->id)->first();
            if ($student) {
                $isEnrolled = $student->enrollments()->where('course_id', $material->course_id)->exists();
                if ($isEnrolled || $material->visibility === 'all') {
                    $authorized = true;
                }
            } else {
                $authorized = true;
            }
        }

        if (!$authorized) {
            abort(403, 'Unauthorized to download this learning material.');
        }

        if (!$material->file_path || !Storage::disk('public')->exists($material->file_path)) {
            abort(404, 'File not found on storage server.');
        }

        return Storage::disk('public')->download($material->file_path, $material->file_name);
    }

    public function update(int $id, Request $request): LearningMaterial
    {
        $teacher = $this->getTeacher(false);
        $courseIds = $teacher->courses->pluck('id')->toArray();

        $material = LearningMaterial::where('id', $id)
                                    ->whereIn('course_id', $courseIds)
                                    ->firstOrFail();

        $validated = $request->validate([
            'course_id'   => 'required|in:' . implode(',', $courseIds),
            'title'       => 'required|string|max:255',
            'type'        => 'required|string|in:Notes,Worksheet,Slides,Document,Assignment,Past Paper',
            'topic'       => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'file'        => 'nullable|file|max:51200',
            'visibility'  => 'nullable|string|in:all,selected',
            'status'      => 'nullable|string|in:Published,Draft,Scheduled',
        ]);

        $filePath = $material->file_path;
        $fileName = $material->file_name;
        $fileSize = $material->file_size;
        $fileType = $material->file_type;

        if ($request->hasFile('file')) {
            if ($material->file_path && Storage::disk('public')->exists($material->file_path)) {
                Storage::disk('public')->delete($material->file_path);
            }
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
            $fileType = strtolower($file->getClientOriginalExtension() ?: 'pdf');
            $filePath = $file->store('materials', 'public');
        }

        $material->update([
            'course_id'   => $validated['course_id'],
            'title'       => $validated['title'],
            'type'        => $validated['type'],
            'topic'       => ($validated['topic'] ?? null) ?: 'General',
            'description' => $validated['description'] ?? null,
            'file_path'   => $filePath,
            'file_name'   => $fileName,
            'file_size'   => $fileSize,
            'file_type'   => $fileType,
            'visibility'  => $validated['visibility'] ?? 'all',
            'status'      => $validated['status'] ?? 'Published',
        ]);

        $course = Course::find($validated['course_id']);
        if ($course) {
            foreach ($course->enrollments as $enrollment) {
                if ($enrollment->student?->user_id) {
                    UserNotification::create([
                        'user_id' => $enrollment->student->user_id,
                        'type'    => 'material',
                        'title'   => 'Learning Material Updated',
                        'message' => "Learning material '{$validated['title']}' was updated by your teacher.",
                    ]);
                }
            }
        }

        UserNotification::create([
            'user_id' => auth()->id(),
            'type'    => 'material',
            'title'   => 'Material Updated',
            'message' => "You updated learning material '{$validated['title']}'.",
        ]);

        return $material;
    }
}
