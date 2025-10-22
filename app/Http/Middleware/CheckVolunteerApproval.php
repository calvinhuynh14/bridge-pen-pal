<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckVolunteerApproval
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        // Only check volunteers
        if ($user && $user->user_type === 'volunteer') {
            // Check volunteer approval status using raw SQL
            $volunteerStatus = DB::select('SELECT status FROM volunteer WHERE user_id = ?', [$user->id]);
            
            // If no volunteer record or status is not approved, redirect to application submitted page
            if (empty($volunteerStatus) || $volunteerStatus[0]->status !== 'approved') {
                return redirect()->route('application.submitted');
            }
        }
        
        return $next($request);
    }
}
