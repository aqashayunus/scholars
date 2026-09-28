<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            abort(403, 'Access denied.');
        }

        // Gracefully route parents who hit student fee URLs directly to parent fee portal
        if ($request->user()->role === 'parent' && $request->is('student/fees*')) {
            if (preg_match('#student/fees/([0-9]+)#', $request->path(), $matches)) {
                return redirect()->route('parent.fees.show', $matches[1]);
            }
            return redirect()->route('parent.fees');
        }

        // Gracefully route students who hit parent fee URLs directly to student fee portal
        if ($request->user()->role === 'student' && $request->is('parent/fees*')) {
            if (preg_match('#parent/fees/([0-9]+)#', $request->path(), $matches)) {
                return redirect()->route('student.fees.show', $matches[1]);
            }
            return redirect()->route('student.fees');
        }

        if (! in_array($request->user()->role, $roles)) {
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}
