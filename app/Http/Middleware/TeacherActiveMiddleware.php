<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirect teacher accounts that are still pending Aster Edu verification.
 * Only teachers with application_status = 'active' on their Teacher profile
 * get full access to the Teacher Dashboard.
 */
class TeacherActiveMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->role !== 'teacher') {
            return $next($request);
        }

        $teacher = $user->teacherProfile;

        // If teacher profile doesn't exist or status is not active, redirect to pending page
        if (!$teacher || $teacher->application_status !== 'active') {
            return redirect()->route('teacher.pending');
        }

        return $next($request);
    }
}
