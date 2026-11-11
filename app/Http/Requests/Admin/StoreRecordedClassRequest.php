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
            'course_id' => 'nullable|exists:courses,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'title' => 'required|string|max:255',
            'topic_name' => 'required|string|max:255',
            'video_url' => 'nullable|string|max:1000',
            'video_file' => 'nullable|file|max:512000|mimes:mp4,mov,avi,mkv,webm,ogg',
            'summary' => 'nullable|string',
        ];
    }
}
