<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|max:255|unique:users,email',
            'role'           => 'required|in:admin,teacher,student,parent',
            'phone'          => 'nullable|string|max:30',
            'ic_number'      => 'required|string|min:6',
            'parent_user_id' => 'nullable|exists:users,id',
            'courses'        => 'nullable|array',
            'courses.*'      => 'exists:courses,id',
        ];
    }
}
