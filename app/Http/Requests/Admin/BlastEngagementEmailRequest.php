<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BlastEngagementEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'blast_subject'   => 'required|string|max:255',
            'blast_body'      => 'required|string|max:5000',
            'blast_audience'  => 'required|string|in:all_inactive,students_only,parents_only,teachers_only',
            'blast_cta_link'  => 'nullable|url|max:500',
            'blast_cta_label' => 'nullable|string|max:100',
        ];
    }
}
