<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Residents don't need email verification
        if ($user && $user->isResident()) {
            return $next($request);
        }

        // Admins don't require email verification (but should be notified)
        if ($user && $user->isAdmin()) {
            // Allow access but we could add a notice here if needed
            return $next($request);
        }

        // For volunteers, check email verification
        if ($user && $user->isVolunteer() && !$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        // For other user types or if verified, continue
        return $next($request);
    }
}

