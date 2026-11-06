<?php

namespace App\Http\Requests\Parent;

use Illuminate\Foundation\Http\FormRequest;

class ProcessPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_ids'    => 'required',
            'payment_method' => 'required|string|max:100',
            'bank_name'      => 'nullable|string|max:100',
        ];
    }
}
