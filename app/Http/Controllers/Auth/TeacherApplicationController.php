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
        ], [
            'full_name.required'          => 'Please enter your full name.',
            'email.required'              => 'Please enter your email address.',
            'email.email'                 => 'Please provide a valid email format.',
            'phone.required'              => 'Please provide your phone number.',
            'qualification.required'      => 'Please enter your highest qualification.',
            'experience_years.required'   => 'Please indicate your years of teaching experience.',
            'preferred_subjects.required' => 'Please indicate the subjects you wish to teach.',
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

        TeacherApplication::create([
            'full_name'          => $validated['full_name'],
            'email'              => $validated['email'],
            'phone'              => $validated['phone'],
            'qualification'      => $validated['qualification'] . ($validated['institution'] ? ' — ' . $validated['institution'] : ''),
            'experience_years'   => $validated['experience_years'],
            'preferred_subjects' => $validated['preferred_subjects'],
            'status'             => 'pending',
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
