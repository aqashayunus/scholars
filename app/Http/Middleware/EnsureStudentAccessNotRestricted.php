<?php

namespace App\Http\Middleware;

use App\Services\StudentAccessRestrictionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentAccessNotRestricted
{
    public function __construct(private StudentAccessRestrictionService $restrictionService) {}

    /**
     * Handle an incoming request for student academic routes.
     * Enforces per-student payment restriction on the server side.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->role !== 'student') {
            return $next($request);
        }

        $student = $user->student;

        if (!$student) {
            return $next($request);
        }

        // Whitelisted routes that restricted students can always access
        $allowedPatterns = [
            'student/fees*',
            'student/profile*',
            'student/settings*',
            'student/notifications*',
            'student/messages*',
        ];

        foreach ($allowedPatterns as $pattern) {
            if ($request->is($pattern)) {
                return $next($request);
            }
        }

        if ($this->restrictionService->isStudentRestricted($student)) {
            $details = $this->restrictionService->getRestrictionDetails($student);

            if ($request->expectsJson()) {
                return response()->json([
                    'status'      => 'restricted',
                    'message'     => "This student's access is temporarily restricted due to an outstanding payment. Please settle the outstanding balance to restore access.",
                    'restriction' => $details,
                ], 403);
            }

            return response()->view('student.restricted', [
                'student' => $student,
                'details' => $details,
            ], 403);
        }

        return $next($request);
    }
}
