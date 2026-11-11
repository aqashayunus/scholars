<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePricingPlansRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'monthly_price' => 'required|string|max:50',
            'monthly_per' => 'required|string|max:20',
            'subject_price' => 'required|string|max:50',
            'subject_per' => 'required|string|max:20',
            'premium_price' => 'required|string|max:50',
            'premium_per' => 'required|string|max:20',
        ];
    }
}
