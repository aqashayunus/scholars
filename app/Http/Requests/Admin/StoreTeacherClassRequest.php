<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherClassRequest extends FormRequest
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
            'level'         => 'required|string|max:50',
            'schedule'      => 'nullable|string|max:255',
            'total_lessons' => 'nullable|integer|min:1',
            'status'        => 'required|in:published,draft,archived',
            'price'         => 'required|numeric|min:0',
            'description'   => 'nullable|string',
        ];
    }
}
