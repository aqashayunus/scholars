<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateTeacherPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.current_password' => 'Your current password is incorrect.',
            'password.confirmed' => 'The new password confirmation does not match.',
            'password.min' => 'Password must be at least 8 characters long.',
        ];
    }
}
