<?php

namespace App\Services\Admin;

use App\Models\Course;
use App\Models\Setting;
use App\Models\Subject;
use App\Models\SubjectSubtopic;
use App\Models\SubjectTopic;
use App\Models\Teacher;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CurriculumService
{
    // ── Course ───────────────────────────────────────────────────────────────

    public function createCourse(array $validated): array
    {
        $monthlyFee = $validated['monthly_fee'];
        unset($validated['monthly_fee']);

        if (empty($validated['teacher_id'])) {
            $subject = Subject::find($validated['subject_id']);
            $validated['teacher_id'] = $subject?->teachers?->first()?->id ?? Teacher::first()?->id ?? null;
        }

        $course = Course::create($validated);

        // Write fee to the subject — this is the authoritative billing source
        Subject::where('id', $course->subject_id)->update(['monthly_fee' => $monthlyFee]);

        $this->notifyCourseAssignedTeacher($course, 'NEW COURSE ASSIGNED', "The course '{$course->title}' ({$course->level}) has been assigned to your educator profile.");

        return [$course, $monthlyFee];
    }

    public function updateCourse(Course $course, array $validated): array
    {
        $monthlyFee = $validated['monthly_fee'];
        unset($validated['monthly_fee']);

        if (!isset($validated['teacher_id']) || $validated['teacher_id'] === null) {
            unset($validated['teacher_id']);
        }

        $course->update($validated);

        Subject::where('id', $course->subject_id)->update(['monthly_fee' => $monthlyFee]);

        try {
            $this->notifyCourseAssignedTeacher($course, 'COURSE DETAILS UPDATED', "Details for course '{$course->title}' have been updated.");
            foreach ($course->enrollments as $enrollment) {
                if ($enrollment->student?->user_id) {
                    UserNotification::create([
                        'user_id' => $enrollment->student->user_id,
                        'type'    => 'course',
                        'title'   => 'COURSE DETAILS UPDATED',
                        'message' => "Details for your enrolled course '{$course->title}' have been updated.",
                        'read_at' => null,
                    ]);
                }
            }
        } catch (\Throwable $e) {
        }

        return [$course, $monthlyFee];
    }

    public function deleteCourse(int $id): string
    {
        $course = Course::findOrFail($id);
        $title = $course->title;
        $course->delete();

        return $title;
    }

    private function notifyCourseAssignedTeacher(Course $course, string $title, string $message): void
    {
        try {
            if ($course->teacher_id) {
                $teacher = Teacher::find($course->teacher_id);
                if ($teacher?->user_id) {
                    UserNotification::create([
                        'user_id' => $teacher->user_id,
                        'type'    => 'course',
                        'title'   => $title,
                        'message' => $message,
                        'read_at' => null,
                    ]);
                }
            }
        } catch (\Throwable $e) {
        }
    }

    // ── Subject ──────────────────────────────────────────────────────────────

    public function storeSubject(Request $request): array
    {
        if ($request->has('subjects') && is_array($request->subjects)) {
            $createdCount = 0;
            DB::transaction(function () use ($request, &$createdCount) {
                $maxOrder = Subject::max('order') ?? 0;
                foreach ($request->subjects as $item) {
                    if (empty($item['name'])) {
                        continue;
                    }

                    $educationType = $item['education_type'] ?? 'secondary';
                    $stage = $item['stage'] ?? ($educationType === 'primary' ? 'Primary' : 'Upper Secondary');
                    $formYear = $item['form_year'] ?? ($educationType === 'primary' ? 'Year 4' : 'Form 4');
                    $category = $item['category'] ?? 'Core';

                    $slugBase = !empty($item['slug']) ? $item['slug'] : ($item['name'] . ' ' . $formYear);
                    $slug = Str::slug($slugBase);
                    $originalSlug = $slug;
                    $counter = 1;
                    while (Subject::where('slug', $slug)->exists()) {
                        $slug = $originalSlug . '-' . $counter++;
                    }

                    Subject::create([
                        'name'           => $item['name'],
                        'slug'           => $slug,
                        'code'           => !empty($item['code']) ? $item['code'] : 'KPM-' . strtoupper(Str::random(6)),
                        'education_type' => $educationType,
                        'stage'          => $stage,
                        'form_year'      => $formYear,
                        'category'       => $category,
                        'stream_package' => $item['stream_package'] ?? null,
                        'level'          => $item['level'] ?? ($educationType === 'primary' ? 'Primary' : 'SPM'),
                        'color'          => $item['color'] ?? '#3b82f6',
                        'icon'           => $item['icon'] ?? 'academic-cap',
                        'description'    => $item['description'] ?? null,
                        'order'          => !empty($item['order']) ? (int) $item['order'] : (++$maxOrder),
                        'is_active'      => isset($item['is_active']) ? (bool) $item['is_active'] : true,
                    ]);
                    $createdCount++;
                }
            });

            return ['bulk' => true, 'count' => $createdCount];
        }

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'slug'           => 'nullable|string|max:255',
            'code'           => 'nullable|string|max:50',
            'education_type' => 'required|in:primary,secondary',
            'stage'          => 'required|string|max:50',
            'form_year'      => 'required|string|max:50',
            'category'       => 'required|string|max:50',
            'stream_package' => 'nullable|string|max:50',
            'description'    => 'nullable|string',
            'level'          => 'required|string|max:50',
            'color'          => 'required|string|max:20',
            'icon'           => 'nullable|string|max:50',
            'order'          => 'nullable|integer',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name'] . ' ' . $validated['form_year']);
        }
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (Subject::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter++;
        }

        if (empty($validated['code'])) {
            $validated['code'] = 'KPM-' . strtoupper(Str::random(6));
        }

        $maxOrder = Subject::max('order') ?? 0;
        $validated['order'] = !empty($validated['order']) ? (int) $validated['order'] : ($maxOrder + 1);
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $subject = Subject::create($validated);

        return ['bulk' => false, 'subject' => $subject];
    }

    public function updateSubject(Subject $subject, Request $request): Subject
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'slug'           => 'required|string|max:255|unique:subjects,slug,' . $subject->id,
            'code'           => 'nullable|string|max:50',
            'education_type' => 'required|in:kindergarten,primary,secondary',
            'stage'          => 'required|string|max:50',
            'form_year'      => 'required|string|max:50',
            'category'       => 'required|string|max:50',
            'stream_package' => 'nullable|string|max:50',
            'description'    => 'nullable|string',
            'level'          => 'required|string|max:50',
            'color'          => 'required|string|max:20',
            'icon'           => 'nullable|string|max:50',
            'order'          => 'nullable|integer',
        ]);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $subject->update($validated);

        return $subject;
    }

    public function deleteSubject(int $id): string
    {
        $subject = Subject::findOrFail($id);
        $name = $subject->name;
        $subject->delete();

        return $name;
    }

    // ── Topic ────────────────────────────────────────────────────────────────

    /**
     * @return array{error?: string, bulk?: bool, count?: int, topic?: SubjectTopic}
     */
    public function storeTopic(Request $request): array
    {
        $subjectId = $request->input('subject_id');
        if (!$subjectId || !Subject::where('id', $subjectId)->exists()) {
            return ['error' => 'Please select a valid subject.'];
        }

        if ($request->has('topics') && is_array($request->topics)) {
            $createdCount = 0;
            DB::transaction(function () use ($request, $subjectId, &$createdCount) {
                $maxChapter = SubjectTopic::where('subject_id', $subjectId)->max('chapter_number') ?? 0;

                foreach ($request->topics as $item) {
                    if (empty($item['title'])) {
                        continue;
                    }

                    $chNumber = !empty($item['chapter_number']) ? (int) $item['chapter_number'] : (++$maxChapter);
                    if ($chNumber > $maxChapter) {
                        $maxChapter = $chNumber;
                    }

                    SubjectTopic::create([
                        'subject_id'     => $subjectId,
                        'chapter_number' => $chNumber,
                        'title'          => $item['title'],
                        'description'    => $item['description'] ?? null,
                    ]);
                    $createdCount++;
                }
            });

            return ['bulk' => true, 'count' => $createdCount];
        }

        $validated = $request->validate([
            'subject_id'     => 'required|exists:subjects,id',
            'chapter_number' => 'nullable|integer|min:1',
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
        ]);

        if (empty($validated['chapter_number'])) {
            $validated['chapter_number'] = (SubjectTopic::where('subject_id', $validated['subject_id'])->max('chapter_number') ?? 0) + 1;
        }

        $topic = SubjectTopic::create($validated);

        return ['bulk' => false, 'topic' => $topic];
    }

    public function updateTopic(SubjectTopic $topic, Request $request): SubjectTopic
    {
        $validated = $request->validate([
            'subject_id'     => 'required|exists:subjects,id',
            'chapter_number' => 'required|integer|min:1',
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
        ]);

        $topic->update($validated);

        return $topic;
    }

    public function deleteTopic(int $id): string
    {
        $topic = SubjectTopic::findOrFail($id);
        $title = $topic->title;
        $topic->delete();

        return $title;
    }

    // ── Subtopic ─────────────────────────────────────────────────────────────

    public function storeSubtopic(int $topicId, Request $request): array
    {
        $topic = SubjectTopic::findOrFail($topicId);

        if ($request->has('subtopics') && is_array($request->subtopics)) {
            $createdCount = 0;
            DB::transaction(function () use ($request, $topic, &$createdCount) {
                $maxOrder = $topic->subtopics()->max('order') ?? 0;

                foreach ($request->subtopics as $item) {
                    if (empty($item['title'])) {
                        continue;
                    }

                    $order = !empty($item['order']) ? (int) $item['order'] : (++$maxOrder);
                    if ($order > $maxOrder) {
                        $maxOrder = $order;
                    }

                    $topic->subtopics()->create([
                        'title'          => $item['title'],
                        'default_status' => $item['default_status'] ?? 'Not Started',
                        'order'          => $order,
                    ]);
                    $createdCount++;
                }
            });

            return ['bulk' => true, 'count' => $createdCount, 'topic' => $topic];
        }

        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'default_status' => 'required|in:Mastered,In Progress,Needs Practice,Not Started',
            'order'          => 'nullable|integer|min:1',
        ]);

        $maxOrder = $topic->subtopics()->max('order') ?: 0;
        $validated['order'] = $validated['order'] ?? ($maxOrder + 1);

        $subtopic = $topic->subtopics()->create($validated);

        return ['bulk' => false, 'subtopic' => $subtopic, 'topic' => $topic];
    }

    public function updateSubtopic(SubjectSubtopic $subtopic, array $validated): SubjectSubtopic
    {
        $subtopic->update($validated);

        return $subtopic;
    }

    public function deleteSubtopic(int $id): string
    {
        $subtopic = SubjectSubtopic::findOrFail($id);
        $title = $subtopic->title;
        $subtopic->delete();

        return $title;
    }
}
