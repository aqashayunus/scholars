<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLiveClassRequest extends FormRequest
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
            'education_level'  => 'nullable|string|max:100',
            'target_age'       => 'nullable|string|max:100',
            'topic'            => 'required|string|max:255',
            'subtopic'         => 'nullable|string|max:255',
            'title'            => 'required|string|max:255',
            'scheduled_at'     => 'required|date',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'meeting_url'      => 'nullable|url|max:255',
        ];
    }
}
