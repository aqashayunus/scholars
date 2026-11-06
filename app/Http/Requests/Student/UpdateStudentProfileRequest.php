<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'min:2', 'max:255'],
            'email'         => ['nullable', 'email', 'max:255', 'unique:users,email,' . $this->user()->id],
            'phone'         => ['nullable', 'string', 'max:50'],
            'school_name'   => ['nullable', 'string', 'max:255'],
            'avatar'        => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter your full name.',
            'email.email'   => 'Please enter a valid email address.',
            'email.unique'  => 'This email is already in use by another account.',
            'avatar.image'  => 'The profile picture must be an image (JPEG, PNG, JPG, or WebP).',
            'avatar.max'    => 'Profile picture size must not exceed 2MB.',
        ];
    }
}
