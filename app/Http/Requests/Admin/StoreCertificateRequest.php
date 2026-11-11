<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => 'required|exists:students,id',
            'course_id' => 'nullable|exists:courses,id',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'cert_number' => 'nullable|string|max:100|unique:certificates,cert_number',
            'issued_at' => 'nullable|date',
            'issued_by' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ];
    }
}
