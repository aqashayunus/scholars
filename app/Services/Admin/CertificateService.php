<?php

namespace App\Services\Admin;

use App\Models\Certificate;
use App\Models\Student;
use App\Models\UserNotification;
use App\Services\BillingNotificationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CertificateService
{
    public function issue(array $validated, ?UploadedFile $file): Certificate
    {
        $filePath = null;
        $fileName = null;
        if ($file) {
            $fileName = $file->getClientOriginalName();
            $filePath = $file->store('certificates', 'public');
        }

        $certNumber = !empty($validated['cert_number'])
            ? $validated['cert_number']
            : ('OSC-' . date('Y') . '-' . strtoupper(substr(md5(uniqid()), 0, 6)));

        $certificate = Certificate::create([
            'student_id'  => $validated['student_id'],
            'course_id'   => $validated['course_id'] ?? null,
            'title'       => $validated['title'],
            'subtitle'    => $validated['subtitle'] ?? null,
            'cert_number' => $certNumber,
            'file_path'   => $filePath,
            'file_name'   => $fileName,
            'issued_at'   => $validated['issued_at'] ?? now(),
            'issued_by'   => $validated['issued_by'] ?? (auth()->user()->name ?? 'Academy Administration'),
            'description' => $validated['description'] ?? null,
        ]);

        $this->notifyRecipients($validated['student_id'], $validated['title']);

        return $certificate;
    }

    public function destroy(int $id): void
    {
        $certificate = Certificate::findOrFail($id);
        if ($certificate->file_path && Storage::disk('public')->exists($certificate->file_path)) {
            Storage::disk('public')->delete($certificate->file_path);
        }
        $certificate->delete();
    }

    private function notifyRecipients(int $studentId, string $title): void
    {
        $student = Student::find($studentId);
        if (!$student) {
            return;
        }

        if ($student->user_id) {
            UserNotification::create([
                'user_id' => $student->user_id,
                'type'    => 'certificate',
                'title'   => 'NEW CERTIFICATE AWARDED',
                'message' => "Congratulations! You have been awarded an official certificate: '{$title}'. View it in your Achievements tab.",
                'read_at' => null,
            ]);
        }

        $parents = BillingNotificationService::getStudentParents($student);
        $studentName = $student->user->name ?? 'Your student';
        foreach ($parents as $parent) {
            UserNotification::create([
                'user_id' => $parent->id,
                'type'    => 'certificate',
                'title'   => 'STUDENT CERTIFICATE AWARDED',
                'message' => "Congratulations! {$studentName} has been awarded an official certificate: '{$title}'.",
                'read_at' => null,
            ]);
        }
    }
}
