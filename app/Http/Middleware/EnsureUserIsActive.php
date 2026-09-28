<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     * Ensure authenticated user accounts are active. If inactive or deactivated,
     * immediately terminate session and redirect to login with notification.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && method_exists($user, 'isAccountInactive') && $user->isAccountInactive()) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'message'          => 'Akaun anda telah dinyahaktifkan atau tidak aktif. Sila hubungi pihak pentadbir.',
                    'account_inactive' => true,
                ], 403);
            }

            return redirect()->route('login')->withErrors([
                'email' => 'Akaun anda telah dinyahaktifkan atau tidak aktif. Sila hubungi pihak pentadbir untuk maklumat lanjut.',
            ]);
        }

        return $next($request);
    }
}
