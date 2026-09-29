<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeacherSubjectsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject_ids'   => 'nullable|array',
            'subject_ids.*' => 'exists:subjects,id',
        ];
    }
}
