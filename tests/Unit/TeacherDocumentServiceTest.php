<?php

namespace Tests\Unit;

use App\Services\TeacherDocumentService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeacherDocumentServiceTest extends TestCase
{
    public function test_normalizes_known_types_and_the_additional_alias(): void
    {
        $documents = new TeacherDocumentService;

        $this->assertSame('resume', $documents->normalizeType('resume'));
        $this->assertSame('additional_document', $documents->normalizeType('additional'));
        $this->assertNull($documents->normalizeType('passport'));
    }

    public function test_resolves_legacy_path_shapes_on_the_public_disk(): void
    {
        Storage::fake('public');
        UploadedFile::fake()->create('cv.pdf', 10)->storeAs('teacher_applications/resumes', 'cv.pdf', 'public');
        $documents = new TeacherDocumentService;

        $expected = Storage::disk('public')->path('teacher_applications/resumes/cv.pdf');
        $this->assertSame($expected, $documents->resolvePath('teacher_applications/resumes/cv.pdf'));
        $this->assertSame($expected, $documents->resolvePath('storage/teacher_applications/resumes/cv.pdf'));
        $this->assertSame($expected, $documents->resolvePath('public/teacher_applications/resumes/cv.pdf'));
        $this->assertNull($documents->resolvePath('teacher_applications/resumes/missing.pdf'));
    }

    public function test_rejects_paths_that_try_to_leave_storage(): void
    {
        $documents = new TeacherDocumentService;

        $this->assertNull($documents->resolvePath('../../.env'));
        $this->assertNull($documents->resolvePath('teacher_applications/../../../.env'));
        $this->assertNull($documents->resolvePath(null));
    }
}
