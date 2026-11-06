<?php

namespace App\Http\Requests\Timetable;

use Illuminate\Foundation\Http\FormRequest;

class TeacherRescheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'scheduled_date'   => 'required|date|after_or_equal:today',
            'scheduled_time'   => 'required|string',
            'duration_minutes' => 'nullable|integer|min:15|max:300',
            'reason'           => 'required|string|min:5|max:1000',
            'meeting_url'      => 'nullable|url|max:1000',
        ];
    }
}
