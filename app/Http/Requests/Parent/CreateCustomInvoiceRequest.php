<?php

namespace App\Http\Requests\Parent;

use Illuminate\Foundation\Http\FormRequest;

class CreateCustomInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => 'required|string|max:255',
            'amount'      => 'required|numeric|min:1|max:10000',
            'course_id'   => 'nullable|exists:courses,id',
        ];
    }
}
