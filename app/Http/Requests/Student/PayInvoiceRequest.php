<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class PayInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'proof_file' => ['required_if:payment_method,bank_transfer', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }
}
