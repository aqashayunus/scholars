<?php

namespace App\Services;

use App\Enums\TeacherApplicationStatus;
use App\Models\Teacher;
use App\Models\TeacherApplication;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

/**
 * Teacher credential documents (resume, certificate, transcript, additional),
 * stored on the TeacherApplication row. Shared by the teacher's own profile
 * page and the admin's application review, so both resolve, label and serve
 * files identically.
 */
class TeacherDocumentService
{
    /**
     * Document type => [column, folder on the public disk, allowed mimes, label].
     * 'additional' is accepted as an alias of 'additional_document' because
     * older dashboard links use the short form.
     */
    public const TYPES = [
        'resume' => ['resume_path', 'teacher_applications/resumes', 'pdf,doc,docx', 'Resume / CV'],
        'certificate' => ['certificate_path', 'teacher_applications/certificates', 'pdf,jpg,jpeg,png', 'Qualification Certificate'],
        'transcript' => ['transcript_path', 'teacher_applications/transcripts', 'pdf,jpg,jpeg,png', 'Academic Transcript'],
        'additional_document' => ['additional_document_path', 'teacher_applications/additional_documents', 'pdf,doc,docx,jpg,jpeg,png', 'Additional Document'],
    ];

    private const MIME_BY_EXTENSION = [
        'pdf' => 'application/pdf',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    /**
     * Canonical type key, or null if the type is unknown.
     */
    public function normalizeType(string $type): ?string
    {
        $type = $type === 'additional' ? 'additional_document' : $type;

        return array_key_exists($type, self::TYPES) ? $type : null;
    }

    public function column(string $type): string
    {
        return self::TYPES[$type][0];
    }

    public function folder(string $type): string
    {
        return self::TYPES[$type][1];
    }

    public function mimes(string $type): string
    {
        return self::TYPES[$type][2];
    }

    public function label(string $type): string
    {
        return self::TYPES[$type][3];
    }

    /**
     * The teacher's application row, linking a pre-account application by
     * email, or creating an approved one for teachers onboarded without an
     * application so their documents have somewhere to live.
     */
    public function applicationFor(User $user, Teacher $teacher): TeacherApplication
    {
        $application = TeacherApplication::where('user_id', $user->id)->first();

        if (! $application && ! empty($user->email)) {
            $application = TeacherApplication::where('email', $user->email)->first();
            $application?->update(['user_id' => $user->id]);
        }

        return $application ?? TeacherApplication::create([
            'user_id' => $user->id,
            'full_name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?? '',
            'qualification' => $teacher->qualification ?? 'Certified Educator',
            'experience_years' => $teacher->experience_years ?? 0,
            'preferred_subjects' => $teacher->specialization ?? 'General Education',
            'status' => TeacherApplicationStatus::Approved,
        ]);
    }

    /**
     * Absolute path of a stored document, or null if it is not on disk.
     *
     * Paths saved over the platform's history come in several shapes
     * ('storage/…', 'public/…', bare filenames moved between folders), so
     * this checks the known layouts. Only locations under the app's own
     * storage and public/storage trees are ever returned, and '..' segments
     * are rejected, so a stored path can never resolve outside them.
     */
    public function resolvePath(?string $storedPath): ?string
    {
        if (empty($storedPath) || str_contains($storedPath, '..')) {
            return null;
        }

        $clean = ltrim(str_replace('\\', '/', $storedPath), '/');
        foreach (['storage/', 'public/'] as $prefix) {
            if (str_starts_with($clean, $prefix)) {
                $clean = substr($clean, strlen($prefix));
            }
        }

        $filename = basename($clean);
        $candidates = [
            Storage::disk('public')->path($clean),
            public_path('storage/'.$clean),
            storage_path('app/private/'.$clean),
            storage_path('app/'.$clean),
        ];
        foreach (self::TYPES as [, $folder]) {
            $candidates[] = Storage::disk('public')->path($folder.'/'.$filename);
        }

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    public function mimeType(string $absolutePath): string
    {
        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        if (isset(self::MIME_BY_EXTENSION[$extension])) {
            return self::MIME_BY_EXTENSION[$extension];
        }

        $detected = @mime_content_type($absolutePath);

        return ($detected && $detected !== 'application/x-empty') ? $detected : 'application/octet-stream';
    }

    /**
     * Response headers for serving a document inline (in the browser tab).
     *
     * @return array<string, string>
     */
    public function inlineHeaders(string $absolutePath): array
    {
        return [
            'Content-Type' => $this->mimeType($absolutePath),
            'Content-Disposition' => 'inline; filename="'.basename($absolutePath).'"',
            'Cache-Control' => 'no-cache, private',
            'X-Content-Type-Options' => 'nosniff',
        ];
    }
}
