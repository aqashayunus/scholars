<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsNotExpired
{
    /**
     * Redirect authenticated users flagged must_change_password to the forced
     * password-change screen before they can reach anything else. Applies to
     * accounts created by an admin, or with a temporary/shared password.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $exempt = $request->routeIs('password.force.*')
            || $request->routeIs('logout')
            || $request->routeIs('verification.*');

        if ($user && $user->must_change_password && !$exempt) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message'                => 'You must set a new password before continuing.',
                    'must_change_password'   => true,
                ], 403);
            }

            return redirect()->route('password.force.show');
        }

        return $next($request);
    }
}
