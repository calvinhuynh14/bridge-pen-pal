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
        
        // Log all middleware calls
        \Log::info('CheckVolunteerApproval middleware called', [
            'url' => $request->url(),
            'method' => $request->method(),
            'user_id' => $user ? $user->id : null,
            'user_type' => $user ? $user->user_type : null,
            'authenticated' => Auth::check(),
            'session_id' => session()->getId()
        ]);
        
        // Only check volunteers
        if ($user && $user->user_type === 'volunteer') {
            // Check volunteer approval status using raw SQL
            $volunteerStatus = DB::select('SELECT status FROM volunteer WHERE user_id = ?', [$user->id]);
            
            \Log::info('Volunteer status check', [
                'user_id' => $user->id,
                'volunteer_status' => $volunteerStatus,
                'is_approved' => !empty($volunteerStatus) && $volunteerStatus[0]->status === 'approved'
            ]);
            
            // If no volunteer record or status is not approved, redirect to application submitted page
            if (empty($volunteerStatus) || $volunteerStatus[0]->status !== 'approved') {
                \Log::info('Redirecting volunteer to application submitted page', [
                    'user_id' => $user->id,
                    'reason' => empty($volunteerStatus) ? 'no_volunteer_record' : 'not_approved'
                ]);
                
                return redirect()->route('application.submitted');
            }
        }
        
        \Log::info('CheckVolunteerApproval middleware passed', [
            'user_id' => $user ? $user->id : null,
            'user_type' => $user ? $user->user_type : null
        ]);
        
        return $next($request);
    }
}
