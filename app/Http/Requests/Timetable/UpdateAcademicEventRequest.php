<?php

namespace App\Http\Requests\Timetable;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAcademicEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string|max:1000',
            'event_type'      => 'required|in:public_holiday,school_break,exam_period,academic_term',
            'start_date'      => 'required|date',
            'end_date'        => 'nullable|date|after_or_equal:start_date',
            'is_all_day'      => 'nullable|boolean',
            'color'           => 'nullable|string|max:30',
            'state_or_region' => 'nullable|string|max:100',
        ];
    }
}
