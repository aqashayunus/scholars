<?php

namespace App\Services\Teacher;

use App\Models\Course;
use App\Models\RecordedClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\UserNotification;
use Illuminate\Http\Request;

class RecordingService
{
    private function getTeacher(bool $withEnrollments = true): Teacher
    {
        $with = ['courses.subject'];
        if ($withEnrollments) {
            $with[] = 'courses.enrollments.student.user';
            $with[] = 'courses.assignments.results.student.user';
            $with[] = 'courses.quizzes.results.student.user';
        }

        return Teacher::where('user_id', auth()->id())->with($with)->firstOrFail();
    }

    public function indexData(): array
    {
        $teacher = $this->getTeacher(true);
        $courses = $teacher->courses;
        $courseIds = $courses->pluck('id')->toArray();
        $subjectIds = $courses->pluck('subject_id')->unique()->toArray();

        $recordings = RecordedClass::where(function ($q) use ($courseIds, $subjectIds) {
            $q->whereIn('course_id', $courseIds)
                ->orWhereIn('subject_id', $subjectIds);
        })->with(['course.subject', 'subject'])->latest()->get();

        $subjects = Subject::where('is_active', true)->get();

        return compact('teacher', 'courses', 'recordings', 'subjects');
    }

    public function store(Request $request): RecordedClass
    {
        $teacher = $this->getTeacher(false);
        $courses = $teacher->courses;
        $courseIds = $courses->pluck('id')->toArray();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'topic_name' => 'required|string|max:255',
            'course_id' => 'required|in:'.implode(',', $courseIds),
            'video_url' => 'nullable|string|max:1000',
            'video_file' => 'nullable|file|max:204800|mimes:mp4,mov,avi,mkv,webm',
            'thumbnail_file' => 'nullable|image|max:10240',
            'duration_minutes' => 'required|integer|min:1|max:480',
            'summary' => 'nullable|string|max:3000',
        ]);

        $course = Course::findOrFail($validated['course_id']);

        $videoUrl = $validated['video_url'] ?? '';
        if ($request->hasFile('video_file')) {
            $path = $request->file('video_file')->store('recordings', 'public');
            $videoUrl = asset('storage/'.$path);
        }

        if (empty($videoUrl)) {
            $videoUrl = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
        }

        $thumbnail = null;
        if ($request->hasFile('thumbnail_file')) {
            $path = $request->file('thumbnail_file')->store('recording_thumbnails', 'public');
            $thumbnail = asset('storage/'.$path);
        }

        $recording = RecordedClass::create([
            'course_id' => $course->id,
            'subject_id' => $course->subject_id,
            'title' => $validated['title'],
            'topic_name' => $validated['topic_name'],
            'video_url' => $videoUrl,
            'thumbnail' => $thumbnail,
            'duration_minutes' => $validated['duration_minutes'],
            'views_count' => 0,
            'summary' => $validated['summary'] ?? null,
        ]);

        foreach ($course->enrollments as $enrollment) {
            if ($enrollment->student?->user_id) {
                UserNotification::create([
                    'user_id' => $enrollment->student->user_id,
                    'type' => 'class_reminder',
                    'title' => 'New Class Recording Available',
                    'message' => "A new class recording '{$validated['title']}' ({$validated['topic_name']}) has been uploaded to the Study Vault for ".($course->subject->name ?? 'your subject').'.',
                ]);
            }
        }

        UserNotification::create([
            'user_id' => auth()->id(),
            'type' => 'class_reminder',
            'title' => 'Recording Published',
            'message' => "Class recording '{$validated['title']}' was uploaded and published to the Study Vault.",
        ]);

        return $recording;
    }

    public function update(int $id, Request $request): RecordedClass
    {
        $teacher = $this->getTeacher(false);
        $courses = $teacher->courses;
        $courseIds = $courses->pluck('id')->toArray();

        $recording = RecordedClass::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'topic_name' => 'required|string|max:255',
            'course_id' => 'required|in:'.implode(',', $courseIds),
            'video_url' => 'nullable|string|max:1000',
            'video_file' => 'nullable|file|max:204800|mimes:mp4,mov,avi,mkv,webm',
            'thumbnail_file' => 'nullable|image|max:10240',
            'duration_minutes' => 'required|integer|min:1|max:480',
            'summary' => 'nullable|string|max:3000',
        ]);

        $course = Course::findOrFail($validated['course_id']);

        $videoUrl = $recording->video_url;
        if ($request->hasFile('video_file')) {
            $path = $request->file('video_file')->store('recordings', 'public');
            $videoUrl = asset('storage/'.$path);
        } elseif (! empty($validated['video_url'])) {
            $videoUrl = $validated['video_url'];
        }

        $thumbnail = $recording->thumbnail;
        if ($request->hasFile('thumbnail_file')) {
            $path = $request->file('thumbnail_file')->store('recording_thumbnails', 'public');
            $thumbnail = asset('storage/'.$path);
        }

        $recording->update([
            'course_id' => $course->id,
            'subject_id' => $course->subject_id,
            'title' => $validated['title'],
            'topic_name' => $validated['topic_name'],
            'video_url' => $videoUrl,
            'thumbnail' => $thumbnail,
            'duration_minutes' => $validated['duration_minutes'],
            'summary' => $validated['summary'] ?? null,
        ]);

        UserNotification::create([
            'user_id' => auth()->id(),
            'type' => 'class_reminder',
            'title' => 'Recording Updated',
            'message' => "Class recording '{$validated['title']}' was updated successfully.",
        ]);

        return $recording;
    }

    public function destroy(int $id): RecordedClass
    {
        $recording = RecordedClass::findOrFail($id);
        $recording->delete();

        return $recording;
    }
}
