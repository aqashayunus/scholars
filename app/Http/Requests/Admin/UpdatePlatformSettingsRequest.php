<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlatformSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'platform_name'    => 'required|string|max:100',
            'platform_tagline' => 'nullable|string|max:150',
            'support_email'    => 'required|email|max:100',
            'support_phone'    => 'nullable|string|max:30',
            'teacher_cut_pct'  => 'required|numeric|min:0|max:100',
            'currency_code'    => 'required|string|max:10',
            'currency_symbol'  => 'required|string|max:10',
            'invoice_due_days' => 'nullable|integer|min:1|max:180',
        ];
    }
}
