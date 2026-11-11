<?php

namespace App\Http\Requests\Parent;

use Illuminate\Foundation\Http\FormRequest;

class ConnectChildRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'identifier' => 'required|string|max:255',
            'relationship' => 'nullable|string|max:30',
        ];
    }

    public function messages(): array
    {
        return [
            'identifier.required' => 'Please enter the student\'s MyKad / IC number (e.g. 123456-78-9012) or their registered email address.',
        ];
    }
}
