<?php

namespace App\Http\Requests\Parent;

use Illuminate\Foundation\Http\FormRequest;

class StoreChildRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'             => 'required|string|min:2|max:255',
            'child_ic_number'  => ['required', 'string', 'regex:/^[0-9]{6}-[0-9]{2}-[0-9]{4}$/'],
            'level'            => 'required|string|max:100',
            'relationship'     => 'nullable|string|max:30',
            'school_name'      => 'nullable|string|max:255',
            'password'         => 'nullable|string|min:6|max:100',
            'subjects'         => 'nullable|array',
            'subjects.*'       => 'string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'            => 'Please enter your child\'s full name.',
            'child_ic_number.required' => 'Please enter your child\'s MyKad / IC number.',
            'child_ic_number.regex'    => 'IC number must be in format XXXXXX-XX-XXXX.',
            'level.required'           => 'Please select your child\'s education level.',
        ];
    }
}
