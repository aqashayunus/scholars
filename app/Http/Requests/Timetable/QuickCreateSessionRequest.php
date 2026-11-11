<?php

namespace App\Http\Requests\Timetable;

use App\Services\TimetableService;
use Illuminate\Foundation\Http\FormRequest;

class QuickCreateSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $courseIds = app(TimetableService::class)->teacherCourseIds($this->user());

        return [
            'course_id' => 'required|in:'.implode(',', $courseIds),
            'title' => 'required|string|max:255',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'required|string',
            'duration_minutes' => 'required|integer|min:15|max:300',
            'meeting_url' => 'nullable|url|max:1000',
        ];
    }
}
