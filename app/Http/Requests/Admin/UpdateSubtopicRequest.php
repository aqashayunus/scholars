<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubtopicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'default_status' => 'required|in:Mastered,In Progress,Needs Practice,Not Started',
            'order' => 'required|integer|min:1',
        ];
    }
}
