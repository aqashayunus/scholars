<?php

namespace App\Http\Requests\Timetable;

use Illuminate\Foundation\Http\FormRequest;

class AdminQuickCreateSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id'        => 'required|exists:courses,id',
            'teacher_id'       => 'nullable|exists:teachers,id',
            'title'            => 'required|string|max:255',
            'scheduled_date'   => 'required|date',
            'scheduled_time'   => 'required|string',
            'duration_minutes' => 'required|integer|min:15|max:300',
            'meeting_url'      => 'nullable|url|max:1000',
        ];
    }
}
