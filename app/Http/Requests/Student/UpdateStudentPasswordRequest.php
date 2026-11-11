<?php

namespace App\Http\Requests\Student;

use App\Models\Student;
use App\Services\Student\ProfileService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateStudentPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $student = Student::where('user_id', $this->user()->id)->first();
        $isParentManaged = app(ProfileService::class)->isParentManaged($student);

        $rules = [
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ];

        if (! $isParentManaged) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'current_password.current_password' => 'The current password you entered is incorrect.',
            'password.confirmed' => 'New passwords do not match.',
            'password.min' => 'Password must be at least 8 characters.',
        ];
    }
}
