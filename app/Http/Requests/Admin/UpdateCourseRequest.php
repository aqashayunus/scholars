<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'         => 'required|string|max:255',
            'subject_id'    => 'required|exists:subjects,id',
            'teacher_id'    => 'nullable|exists:teachers,id',
            'level'         => 'required|string|max:50',
            'monthly_fee'   => 'required|numeric|min:0',
            'status'        => 'required|in:published,draft,archived',
            'total_lessons' => 'nullable|integer|min:1',
            'description'   => 'nullable|string',
        ];
    }
}
