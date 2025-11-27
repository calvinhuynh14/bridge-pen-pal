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
        
        // Get available avatars
        $availableAvatars = [
            'avatar_1_1.png',
            'avatar_1_2.png',
            'avatar_1_3.png',
            'avatar_1_4.png',
            'avatar_1_5.png',
            'avatar_1_6.png',
            'avatar_1_7.png',
            'avatar_1_8.png',
            'avatar_1_9.png',
            'avatar_1_10.png',
            'avatar_1_11.png',
            'avatar_1_12.png',
        ];
        
        return Inertia::render('Profile/Show', [
            'organizationName' => $organizationName,
            'availableAvatars' => $availableAvatars,
            'currentAvatar' => $user->avatar,
        ]);
    }
    
    /**
     * Update the user's avatar.
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['nullable', 'string', 'in:avatar_1_1.png,avatar_1_2.png,avatar_1_3.png,avatar_1_4.png,avatar_1_5.png,avatar_1_6.png,avatar_1_7.png,avatar_1_8.png,avatar_1_9.png,avatar_1_10.png,avatar_1_11.png,avatar_1_12.png'],
        ]);
        
        $user = Auth::user();
        $user->avatar = $request->avatar;
        $user->save();
        
        // Refresh the user model to ensure accessors are recalculated
        $user->refresh();
        
        // Clear any cached user data to ensure fresh data is returned
        Auth::setUser($user);
        
        return redirect()->back()->with('success', 'Avatar updated successfully.');
    }
}
