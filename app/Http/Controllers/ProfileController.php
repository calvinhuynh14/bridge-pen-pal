<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProfileController extends Controller
{
    /**
     * Show the user profile page.
     * This overrides Jetstream's default profile route to include organization data.
     */
    public function show(Request $request)
    {
        $user = Auth::user();
        $organizationName = null;
        
        // Get organization name for volunteers and residents
        if ($user->isVolunteer()) {
            $volunteer = DB::selectOne('
                SELECT o.name 
                FROM volunteer v
                JOIN organization o ON v.organization_id = o.id
                WHERE v.user_id = ?
            ', [$user->id]);
            $organizationName = $volunteer?->name;
        } elseif ($user->isResident()) {
            $resident = DB::selectOne('
                SELECT o.name 
                FROM resident r
                JOIN organization o ON r.organization_id = o.id
                WHERE r.user_id = ?
            ', [$user->id]);
            $organizationName = $resident?->name;
        } elseif ($user->isAdmin()) {
            $admin = DB::selectOne('
                SELECT o.name 
                FROM admin a
                JOIN organization o ON a.organization_id = o.id
                WHERE a.user_id = ?
            ', [$user->id]);
            $organizationName = $admin?->name;
        }
        
        return Inertia::render('Profile/Show', [
            'organizationName' => $organizationName,
        ]);
    }
}
