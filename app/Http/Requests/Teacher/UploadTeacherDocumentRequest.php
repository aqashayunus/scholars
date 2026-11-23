<?php

namespace App\Http\Requests\Teacher;

use App\Services\TeacherDocumentService;
use Illuminate\Foundation\Http\FormRequest;

class UploadTeacherDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $documents = app(TeacherDocumentService::class);
        $type = $documents->normalizeType((string) $this->input('document_type'));

        return [
            'document_type' => ['required', 'string', 'in:'.implode(',', array_keys(TeacherDocumentService::TYPES))],
            'file' => ['required', 'file', 'mimes:'.($type ? $documents->mimes($type) : 'pdf'), 'max:10240'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $documents = app(TeacherDocumentService::class);
        $type = $documents->normalizeType((string) $this->input('document_type'));
        $label = $type ? $documents->label($type) : 'document';

        return [
            'document_type.in' => 'Invalid document type selected.',
            'file.required' => "Please select a file to upload for your {$label}.",
            'file.mimes' => "The {$label} must be a valid file format.",
            'file.max' => "The {$label} file size must not exceed 10MB.",
        ];
    }
}
