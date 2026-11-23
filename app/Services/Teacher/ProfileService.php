<?php

namespace App\Services\Teacher;

use App\Models\Teacher;
use App\Models\User;
use App\Services\TeacherDocumentService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * The teacher's own profile page: personal/bank details, password, and the
 * credential documents (resume, certificate, transcript, additional) that
 * admins review on the teacher's application.
 */
class ProfileService
{
    /** Display copy per document type, in page order. */
    private const DOCUMENT_COPY = [
        'resume' => ['Resume / Curriculum Vitae (CV)', 'Comprehensive summary of your teaching background, credentials, and achievements.', true, '.pdf,.doc,.docx', 'PDF, DOC, DOCX (Max 10MB)'],
        'certificate' => ['Highest Academic Qualification Certificate', "Official Bachelor's, Master's, Diploma, or PhD graduation certificate.", false, '.pdf,.jpg,.jpeg,.png', 'PDF, JPG, PNG (Max 10MB)'],
        'transcript' => ['Academic Transcript', 'Complete statement of academic results or university grade transcripts.', false, '.pdf,.jpg,.jpeg,.png', 'PDF, JPG, PNG (Max 10MB)'],
        'additional_document' => ['Additional Supporting Document', 'Teaching licenses, professional certifications, awards, or letters of reference.', false, '.pdf,.doc,.docx,.jpg,.jpeg,.png', 'PDF, DOC, DOCX, JPG, PNG (Max 10MB)'],
    ];

    public function __construct(private TeacherDocumentService $documents) {}

    public function currentTeacher(): Teacher
    {
        return Teacher::where('user_id', auth()->id())->firstOrFail();
    }

    /**
     * @return array<string, mixed>
     */
    public function profileData(string $activeTab): array
    {
        $teacher = $this->currentTeacher();
        $teacher->load(['user', 'subjects', 'courses.subject', 'courses.enrollments']);
        $user = auth()->user();
        $application = $this->documents->applicationFor($user, $teacher);

        $documents = [];
        $uploadedCount = 0;
        foreach (self::DOCUMENT_COPY as $type => [$title, $description, $required, $accept, $mimesLabel]) {
            $path = $application->{$this->documents->column($type)};
            $resolved = $this->documents->resolvePath($path);
            $filename = $path ? basename($path) : null;

            $documents[$type] = [
                'type' => $type,
                'title' => $title,
                'description' => $description,
                'path' => $path,
                'required' => $required,
                'accept' => $accept,
                'mimes_label' => $mimesLabel,
                'exists_on_disk' => $resolved !== null,
                'resolved_path' => $resolved,
                'filename' => $filename,
                'filesize' => $resolved ? $this->humanFileSize((int) filesize($resolved)) : null,
                'extension' => $filename ? strtolower(pathinfo($filename, PATHINFO_EXTENSION)) : null,
            ];

            if (! empty($path)) {
                $uploadedCount++;
            }
        }

        return compact('teacher', 'user', 'application', 'documents', 'uploadedCount', 'activeTab');
    }

    /**
     * Store a new document, replacing (and deleting) any previous file of
     * the same type. Returns the document's display label.
     */
    public function uploadDocument(string $type, UploadedFile $file): string
    {
        $type = $this->documents->normalizeType($type) ?? abort(404, 'Invalid document type.');
        $application = $this->documents->applicationFor(auth()->user(), $this->currentTeacher());
        $column = $this->documents->column($type);

        $previous = $this->documents->resolvePath($application->{$column});
        $stored = $file->store($this->documents->folder($type), 'public')
            ?: abort(500, 'The document could not be saved. Please try again.');
        $application->update([$column => $stored]);

        if ($previous) {
            @unlink($previous);
        }

        return $this->documents->label($type);
    }

    /**
     * Remove a document and its file. Returns the document's display label.
     */
    public function deleteDocument(string $type): string
    {
        $type = $this->documents->normalizeType($type) ?? abort(404, 'Invalid document type.');
        $application = $this->documents->applicationFor(auth()->user(), $this->currentTeacher());
        $column = $this->documents->column($type);

        if ($application->{$column}) {
            $resolved = $this->documents->resolvePath($application->{$column});
            $application->update([$column => null]);
            if ($resolved) {
                @unlink($resolved);
            }
        }

        return $this->documents->label($type);
    }

    /**
     * Serve one of the signed-in teacher's own documents, inline or as a
     * download. Only ever reads the current teacher's application row.
     */
    public function documentResponse(string $type, bool $download): BinaryFileResponse
    {
        $type = $this->documents->normalizeType($type) ?? abort(404, 'Invalid document category.');
        $user = auth()->user();
        $application = $this->documents->applicationFor($user, $this->currentTeacher());

        $stored = $application->{$this->documents->column($type)} ?: abort(404, 'No document uploaded for this category.');
        $resolved = $this->documents->resolvePath($stored) ?? abort(404, 'The requested document file could not be found on server storage.');

        if ($download) {
            $name = str_replace('_', ' ', $type).'_'.($user->name ?? 'document').'.'.pathinfo($resolved, PATHINFO_EXTENSION);

            return response()->download($resolved, $name, ['Content-Type' => $this->documents->mimeType($resolved)]);
        }

        return response()->file($resolved, $this->documents->inlineHeaders($resolved));
    }

    /**
     * Update personal, professional and bank details, keeping the
     * application record's copies of name/phone/qualification in sync.
     *
     * @param  array<string, mixed>  $validated
     */
    public function updateProfile(array $validated, bool $removeAvatar, ?UploadedFile $avatar): void
    {
        $user = auth()->user();
        $teacher = $this->currentTeacher();

        DB::transaction(function () use ($user, $teacher, $validated, $removeAvatar, $avatar) {
            if ($removeAvatar || $avatar) {
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }
                $stored = $avatar?->store('avatars', 'public');
                $user->avatar = $stored ?: null;
                $teacher->photo = $stored ?: null;
            }

            $user->name = $validated['name'];
            foreach (['phone', 'address_line_1', 'address_line_2', 'city', 'state', 'postcode'] as $field) {
                $user->{$field} = $validated[$field] ?? $user->{$field};
            }
            $user->save();

            $teacher->qualification = $validated['qualification'];
            $teacher->experience_years = $validated['experience_years'];
            foreach (['specialization', 'teaching_style', 'bio', 'bank_name', 'bank_account_number', 'bank_account_holder'] as $field) {
                if (array_key_exists($field, $validated)) {
                    $teacher->{$field} = $validated[$field];
                }
            }
            $teacher->save();

            $this->documents->applicationFor($user, $teacher)->update([
                'full_name' => $validated['name'],
                'phone' => $validated['phone'] ?? $user->phone ?? '',
                'qualification' => $validated['qualification'],
                'experience_years' => $validated['experience_years'],
            ]);
        });
    }

    public function updatePassword(User $user, string $newPassword): void
    {
        $user->update(['password' => Hash::make($newPassword)]);
    }

    private function humanFileSize(int $bytes): string
    {
        return $bytes >= 1048576
            ? round($bytes / 1048576, 2).' MB'
            : round($bytes / 1024, 1).' KB';
    }
}
