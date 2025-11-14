<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Inertia\Inertia;

class EnsureAdminHasOrganization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Only check for admin users
        if ($user && $user->isAdmin()) {
            // Check if admin has an organization
            $hasOrganization = DB::select('SELECT id FROM admin WHERE user_id = ?', [$user->id]);
            
            if (empty($hasOrganization)) {
                // Admin doesn't have organization, redirect to dashboard
                return redirect()->route('admin.dashboard');
            }
        }

        return $next($request);
    }
}
