<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TeacherApplication;
use Illuminate\Http\Request;

class TeacherApplicationController extends Controller
{
    /**
     * Show the teacher application form (Apply to Teach — no password required).
     */
    public function create()
    {
        return view('auth.apply-teacher');
    }

    /**
     * Handle teacher application submission.
     * This does NOT create a User account. Creates a teacher_applications record with status=pending.
     * Aster Edu verifies and approves externally. Admin activates the teacher account.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name'          => ['required', 'string', 'min:2', 'max:255'],
            'email'              => ['required', 'email', 'max:255'],
            'phone'              => ['required', 'string', 'min:8', 'max:50', 'regex:/^[0-9\s\-\+\(\)]+$/'],
            'institution'        => ['nullable', 'string', 'max:255'],
            'qualification'      => ['required', 'string', 'max:255'],
            'experience_years'   => ['required', 'integer', 'min:0', 'max:50'],
            'preferred_subjects' => ['required', 'string', 'max:500'],
            'teaching_level'     => ['nullable'],
            'teaching_level.*'   => ['string', 'max:100'],
            'resume'             => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'certificate'        => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'transcript'         => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'additional_document'=> ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
        ], [
            'full_name.required'          => 'Please enter your full name.',
            'email.required'              => 'Please enter your email address.',
            'email.email'                 => 'Please provide a valid email format.',
            'phone.required'              => 'Please provide your phone number.',
            'qualification.required'      => 'Please enter your highest qualification.',
            'experience_years.required'   => 'Please indicate your years of teaching experience.',
            'preferred_subjects.required' => 'Please indicate the subjects you wish to teach.',
            'resume.required'             => 'Please upload your Resume or Curriculum Vitae (CV).',
            'resume.mimes'                => 'Resume must be a file of type: PDF, DOC, DOCX.',
            'resume.max'                  => 'Resume file size cannot exceed 10MB.',
            'certificate.mimes'           => 'Qualification certificate must be a file of type: PDF, JPG, PNG.',
            'certificate.max'             => 'Qualification certificate cannot exceed 10MB.',
            'transcript.mimes'            => 'Academic transcript must be a file of type: PDF, JPG, PNG.',
            'transcript.max'              => 'Academic transcript cannot exceed 10MB.',
            'additional_document.mimes'   => 'Additional document must be a file of type: PDF, DOC, DOCX, JPG, PNG.',
            'additional_document.max'     => 'Additional document cannot exceed 10MB.',
        ]);

        // Check for duplicate application by email
        $existing = TeacherApplication::where('email', $validated['email'])
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existing) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['email' => 'An application already exists for this email address. Please contact us if you need assistance.']);
        }

        $teachingLevels = $request->input('teaching_level');
        $teachingLevelStr = null;
        if (is_array($teachingLevels)) {
            $teachingLevelStr = implode(', ', array_filter($teachingLevels));
        } elseif (is_string($teachingLevels)) {
            $teachingLevelStr = trim($teachingLevels);
        }

        // Store uploaded documents
        $resumePath = null;
        if ($request->hasFile('resume')) {
            $resumePath = $request->file('resume')->store('teacher_applications/resumes', 'public');
        }

        $certificatePath = null;
        if ($request->hasFile('certificate')) {
            $certificatePath = $request->file('certificate')->store('teacher_applications/certificates', 'public');
        }

        $transcriptPath = null;
        if ($request->hasFile('transcript')) {
            $transcriptPath = $request->file('transcript')->store('teacher_applications/transcripts', 'public');
        }

        $additionalDocPath = null;
        if ($request->hasFile('additional_document')) {
            $additionalDocPath = $request->file('additional_document')->store('teacher_applications/additional_documents', 'public');
        }

        TeacherApplication::create([
            'full_name'                => $validated['full_name'],
            'email'                    => $validated['email'],
            'phone'                    => $validated['phone'],
            'qualification'            => $validated['qualification'] . (!empty($validated['institution']) ? ' — ' . $validated['institution'] : ''),
            'experience_years'         => $validated['experience_years'],
            'preferred_subjects'       => $validated['preferred_subjects'],
            'teaching_level'           => $teachingLevelStr,
            'resume_path'              => $resumePath,
            'certificate_path'         => $certificatePath,
            'transcript_path'          => $transcriptPath,
            'additional_document_path' => $additionalDocPath,
            'status'                   => 'pending',
        ]);

        return redirect()->route('register.teacher.success')
            ->with('teacher_app_email', $validated['email'])
            ->with('teacher_app_name', $validated['full_name']);
    }

    /**
     * Show application received confirmation page.
     */
    public function success()
    {
        $name  = session('teacher_app_name', 'Applicant');
        $email = session('teacher_app_email', '');

        return view('auth.teacher-application-success', compact('name', 'email'));
    }
}
