<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class QuizSubmitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The browser sends only the chosen options; the server grades them.
     */
    public function rules(): array
    {
        return [
            'answers' => 'present|array',
            'answers.*' => 'nullable|string|in:A,B,C,D,a,b,c,d',
        ];
    }
}
