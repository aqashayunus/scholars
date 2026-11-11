<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class LinkParentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_ic' => 'required|string|max:20',
            'relationship' => 'nullable|string|max:30',
        ];
    }

    public function messages(): array
    {
        return [
            'parent_ic.required' => 'Please enter the parent\'s MyKad / IC number.',
        ];
    }
}
