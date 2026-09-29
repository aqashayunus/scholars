<?php

namespace App\Services\Teacher;

use App\Models\ClassRequest;
use App\Models\LiveClass;
use App\Models\Teacher;
use Illuminate\Http\Request;

class ClassScheduleService
{
    private function getTeacher(): Teacher
    {
        return Teacher::where('user_id', auth()->id())->firstOrFail();
    }

    public function toggleDuty(): Teacher
    {
        $teacher = $this->getTeacher();
        $teacher->is_online = !$teacher->is_online;
        $teacher->save();

        return $teacher;
    }

    public function storeClass(Request $request): LiveClass
    {
        $teacher = $this->getTeacher();
        $courseIds = $teacher->courses->pluck('id')->toArray();

        $validated = $request->validate([
            'course_id' => 'required|in:' . implode(',', $courseIds),
            'title' => 'required|string|max:255',
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'nullable|integer|min:1|max:480',
            'meeting_url' => 'nullable|url|max:255',
        ]);

        $validated['teacher_id'] = $teacher->id;
        $validated['status'] = 'upcoming';

        return LiveClass::create($validated);
    }

    public function requestCancel(int $id, Request $request): void
    {
        $teacher = $this->getTeacher();
        $courseIds = $teacher->courses->pluck('id')->toArray();

        $liveClass = LiveClass::where('id', $id)
                              ->where(function($q) use ($courseIds, $teacher) {
                                  $q->whereIn('course_id', $courseIds)->orWhere('teacher_id', $teacher->id);
                              })
                              ->firstOrFail();

        $validated = $request->validate([
            'reason' => 'required|string|min:5|max:1000',
        ]);

        ClassRequest::create([
            'live_class_id' => $liveClass->id,
            'teacher_id' => $teacher->id,
            'type' => 'cancel',
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);
    }

    public function requestReschedule(int $id, Request $request): void
    {
        $teacher = $this->getTeacher();
        $courseIds = $teacher->courses->pluck('id')->toArray();

        $liveClass = LiveClass::where('id', $id)
                              ->where(function($q) use ($courseIds, $teacher) {
                                  $q->whereIn('course_id', $courseIds)->orWhere('teacher_id', $teacher->id);
                              })
                              ->firstOrFail();

        $validated = $request->validate([
            'reason' => 'required|string|min:5|max:1000',
            'requested_at' => 'required|date|after:now',
        ]);

        ClassRequest::create([
            'live_class_id' => $liveClass->id,
            'teacher_id' => $teacher->id,
            'type' => 'reschedule',
            'reason' => $validated['reason'],
            'requested_at' => $validated['requested_at'],
            'status' => 'pending',
        ]);
    }
}
