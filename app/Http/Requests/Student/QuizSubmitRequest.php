<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class QuizSubmitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'score'           => 'required|integer',
            'total_questions' => 'required|integer',
        ];
    }
}
