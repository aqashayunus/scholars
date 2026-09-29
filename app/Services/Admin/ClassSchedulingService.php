<?php

namespace App\Services\Admin;

use App\Models\Course;
use App\Models\LiveClass;
use App\Models\RecordedClass;
use App\Models\UserNotification;
use Illuminate\Support\Carbon;

class ClassSchedulingService
{
    // ── Live Class ───────────────────────────────────────────────────────────

    public function createLiveClass(array $validated): LiveClass
    {
        $validated = $this->fillLiveClassDefaults($validated);

        $liveClass = LiveClass::create($validated);

        $this->notifyLiveClass($liveClass, 'LIVE CLASS SCHEDULED', 'NEW LIVE CLASS SCHEDULED', function ($course, $timeStr) use ($liveClass) {
            return "A live session '{$liveClass->title}' for {$course->title} has been scheduled for {$timeStr}.";
        }, function ($course, $timeStr) use ($liveClass) {
            return "A new live session '{$liveClass->title}' for {$course->title} is scheduled for {$timeStr}.";
        });

        return $liveClass;
    }

    public function updateLiveClass(LiveClass $liveClass, array $validated): LiveClass
    {
        $validated = $this->fillLiveClassDefaults($validated);

        $liveClass->update($validated);

        $this->notifyLiveClass($liveClass, 'LIVE CLASS UPDATED', 'LIVE CLASS UPDATED', function ($course, $timeStr) use ($liveClass) {
            return "The live session '{$liveClass->title}' for {$course->title} has been updated ({$timeStr}).";
        }, function ($course, $timeStr) use ($liveClass) {
            return "The live session '{$liveClass->title}' for {$course->title} has been updated. Scheduled: {$timeStr}.";
        });

        return $liveClass;
    }

    public function deleteLiveClass(int $id): string
    {
        $liveClass = LiveClass::findOrFail($id);
        $title = $liveClass->title;
        $liveClass->delete();

        return $title;
    }

    private function fillLiveClassDefaults(array $validated): array
    {
        if (empty($validated['teacher_id'])) {
            $course = Course::find($validated['course_id']);
            if ($course && $course->teacher_id) {
                $validated['teacher_id'] = $course->teacher_id;
            }
        }

        if (empty($validated['target_age']) && !empty($validated['education_level'])) {
            $validated['target_age'] = $validated['education_level'];
        } elseif (empty($validated['education_level']) && !empty($validated['target_age'])) {
            $validated['education_level'] = $validated['target_age'];
        }

        $validated['duration_minutes'] = (int) ($validated['duration_minutes'] ?? 60);
        $start = Carbon::parse($validated['scheduled_at']);
        $end = (clone $start)->addMinutes($validated['duration_minutes']);
        $now = now();
        if ($now->lt($start)) {
            $validated['status'] = 'upcoming';
        } elseif ($now->between($start, $end)) {
            $validated['status'] = 'live';
        } else {
            $validated['status'] = 'completed';
        }

        return $validated;
    }

    private function notifyLiveClass(LiveClass $liveClass, string $teacherTitle, string $studentTitle, callable $teacherMessage, callable $studentMessage): void
    {
        try {
            $course = $liveClass->course;
            $teacher = $liveClass->teacher ?? $course?->teacher;
            if (!$course) {
                return;
            }

            $timeStr = Carbon::parse($liveClass->scheduled_at)->format('d M Y, h:i A');

            if ($teacher?->user_id) {
                UserNotification::create([
                    'user_id' => $teacher->user_id,
                    'type'    => 'live_class',
                    'title'   => $teacherTitle,
                    'message' => $teacherMessage($course, $timeStr),
                    'read_at' => null,
                ]);
            }

            foreach ($course->enrollments as $enrollment) {
                if ($enrollment->student?->user_id) {
                    UserNotification::create([
                        'user_id' => $enrollment->student->user_id,
                        'type'    => 'live_class',
                        'title'   => $studentTitle,
                        'message' => $studentMessage($course, $timeStr),
                        'read_at' => null,
                    ]);
                }
            }
        } catch (\Throwable $e) {
        }
    }

    // ── Recorded Class ───────────────────────────────────────────────────────

    public function createRecordedClass(array $validated): RecordedClass
    {
        $validated['views_count'] = $validated['views_count'] ?? 0;

        $recordedClass = RecordedClass::create($validated);

        try {
            if ($recordedClass->course) {
                foreach ($recordedClass->course->enrollments as $enrollment) {
                    if ($enrollment->student?->user_id) {
                        UserNotification::create([
                            'user_id' => $enrollment->student->user_id,
                            'type'    => 'course',
                            'title'   => 'NEW RECORDED LESSON AVAILABLE',
                            'message' => "A new recorded lesson '{$recordedClass->title}' ({$recordedClass->topic_name}) is now available in your course library.",
                            'read_at' => null,
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
        }

        return $recordedClass;
    }

    public function updateRecordedClass(RecordedClass $recordedClass, array $validated): RecordedClass
    {
        $recordedClass->update($validated);

        return $recordedClass;
    }

    public function deleteRecordedClass(int $id): string
    {
        $recordedClass = RecordedClass::findOrFail($id);
        $title = $recordedClass->title;
        $recordedClass->delete();

        return $title;
    }
}
