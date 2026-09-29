<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecordedClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id'        => 'nullable|exists:courses,id',
            'subject_id'       => 'nullable|exists:subjects,id',
            'title'            => 'required|string|max:255',
            'topic_name'       => 'required|string|max:255',
            'video_url'        => 'required|url|max:255',
            'thumbnail'        => 'nullable|string|max:255',
            'duration_minutes' => 'required|integer|min:1|max:480',
            'views_count'      => 'nullable|integer|min:0',
            'summary'          => 'nullable|string',
        ];
    }
}
