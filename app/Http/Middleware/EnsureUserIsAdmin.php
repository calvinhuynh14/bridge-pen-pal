<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Check if user is authenticated and is an admin
        if (!$user || !$user->isAdmin()) {
            // Redirect non-admin users to their dashboard
            if ($user) {
                if ($user->isResident()) {
                    return redirect()->route('platform.home');
                } elseif ($user->isVolunteer()) {
                    return redirect()->route('platform.home');
                }
            }
            
            // If not authenticated or unknown user type, redirect to login
            return redirect()->route('login');
        }

        return $next($request);
    }
}
