<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class SubmitCorrectionRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'live_class_id' => 'required|exists:live_classes,id',
            'reason' => 'required|string|max:1000',
            'requested_status' => 'nullable|string|in:attended,excused',
        ];
    }
}
