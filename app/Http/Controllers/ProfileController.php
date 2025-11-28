<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\Interest;
use App\Models\Language;

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
        
        // Get all available interests
        $availableInterests = Interest::orderBy('name')->get(['id', 'name']);
        
        // Get user's current interests (only for volunteers and residents)
        $userInterests = [];
        if ($user->isVolunteer() || $user->isResident()) {
            $userInterests = $user->interests()->pluck('interests.id')->toArray();
        }
        
        // Get all available languages
        $availableLanguages = Language::orderBy('name')->get(['id', 'name']);
        
        // Get user's current languages (only for volunteers and residents)
        $userLanguages = [];
        if ($user->isVolunteer() || $user->isResident()) {
            $userLanguages = $user->languages()->pluck('languages.id')->toArray();
        }
        
        $isAnonymous = (bool)($user->is_anonymous ?? false);
        
        \Log::info('ProfileController - show method', [
            'user_id' => $user->id,
            'db_is_anonymous' => $user->is_anonymous,
            'db_is_anonymous_type' => gettype($user->is_anonymous),
            'computed_isAnonymous' => $isAnonymous,
            'anonymous_name' => $user->anonymous_name,
        ]);
        
        return Inertia::render('Profile/Show', [
            'organizationName' => $organizationName,
            'availableAvatars' => $availableAvatars,
            'currentAvatar' => $user->avatar,
            'availableInterests' => $availableInterests,
            'userInterests' => $userInterests,
            'availableLanguages' => $availableLanguages,
            'userLanguages' => $userLanguages,
            'isAnonymous' => $isAnonymous,
            'anonymousName' => $user->anonymous_name,
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
    
    /**
     * Update the user's interests.
     */
    public function updateInterests(Request $request)
    {
        $user = Auth::user();
        
        // Only allow volunteers and residents to update interests
        if (!$user->isVolunteer() && !$user->isResident()) {
            abort(403, 'Only volunteers and residents can update interests.');
        }
        
        $request->validate([
            'interests' => ['nullable', 'array'],
            'interests.*' => ['exists:interests,id'],
        ]);
        
        // Sync user interests (removes old ones, adds new ones)
        $user->interests()->sync($request->interests ?? []);
        
        return redirect()->back()->with('success', 'Interests updated successfully.');
    }
    
    /**
     * Update the user's languages.
     */
    public function updateLanguages(Request $request)
    {
        $user = Auth::user();
        
        // Only allow volunteers and residents to update languages
        if (!$user->isVolunteer() && !$user->isResident()) {
            abort(403, 'Only volunteers and residents can update languages.');
        }
        
        $request->validate([
            'languages' => ['nullable', 'array'],
            'languages.*' => ['exists:languages,id'],
        ]);
        
        // Sync user languages (removes old ones, adds new ones)
        // Note: We're not setting proficiency_level here, it will use the default 'intermediate'
        $syncData = [];
        foreach ($request->languages ?? [] as $languageId) {
            $syncData[$languageId] = ['proficiency_level' => 'intermediate'];
        }
        $user->languages()->sync($syncData);
        
        return redirect()->back()->with('success', 'Languages updated successfully.');
    }
    
    /**
     * Update the user's anonymous mode setting.
     */
    public function updateAnonymousMode(Request $request)
    {
        $user = Auth::user();
        
        \Log::info('ProfileController - updateAnonymousMode called', [
            'user_id' => $user->id,
            'request_is_anonymous' => $request->is_anonymous,
            'request_is_anonymous_type' => gettype($request->is_anonymous),
            'current_db_value' => $user->is_anonymous,
        ]);
        
        // Only allow volunteers and residents to update anonymous mode
        if (!$user->isVolunteer() && !$user->isResident()) {
            abort(403, 'Only volunteers and residents can update anonymous mode.');
        }
        
        $request->validate([
            'is_anonymous' => ['required', 'boolean'],
        ]);
        
        $user->is_anonymous = (bool)$request->is_anonymous;
        
        // Generate anonymous name if enabling anonymous mode and name doesn't exist
        if ($request->is_anonymous && !$user->anonymous_name) {
            $user->anonymous_name = \App\Models\User::generateAnonymousName();
        }
        
        // Clear anonymous name if disabling anonymous mode
        if (!$request->is_anonymous) {
            $user->anonymous_name = null;
        }
        
        $user->save();
        
        \Log::info('ProfileController - updateAnonymousMode saved', [
            'user_id' => $user->id,
            'saved_is_anonymous' => $user->is_anonymous,
            'saved_anonymous_name' => $user->anonymous_name,
        ]);
        
        return redirect()->back()->with('success', 'Privacy settings updated successfully.');
    }
}
