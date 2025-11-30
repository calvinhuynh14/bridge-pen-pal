<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\FeaturedStory;
use Inertia\Inertia;
use Inertia\Response;

class FeaturedStoryController extends Controller
{
    /**
     * Get the current featured story for the admin's organization
     */
    public function index()
    {
        $user = Auth::user();
        
        if (!$user || !$user->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $adminRecord = DB::selectOne('SELECT organization_id FROM admin WHERE user_id = ?', [$user->id]);
        
        if (!$adminRecord) {
            return response()->json(['featured_story' => null]);
        }
        
        $organizationId = $adminRecord->organization_id;
        
        $featuredStory = DB::selectOne('
            SELECT 
                fs.id,
                fs.organization_id,
                fs.resident_id,
                fs.bio,
                u.name as resident_name,
                u.avatar as resident_avatar,
                fs.created_at,
                fs.updated_at
            FROM featured_story fs
            JOIN users u ON fs.resident_id = u.id
            WHERE fs.organization_id = ?
        ', [$organizationId]);
        
        return response()->json([
            'featured_story' => $featuredStory ? (array)$featuredStory : null
        ]);
    }
    
    /**
     * Get residents for the admin's organization (for dropdown)
     */
    public function getResidents()
    {
        $user = Auth::user();
        
        if (!$user || !$user->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $adminRecord = DB::selectOne('SELECT organization_id FROM admin WHERE user_id = ?', [$user->id]);
        
        if (!$adminRecord) {
            return response()->json(['residents' => []]);
        }
        
        $organizationId = $adminRecord->organization_id;
        
        $residents = DB::select('
            SELECT 
                u.id,
                u.name,
                u.avatar,
                r.status
            FROM resident r
            JOIN users u ON r.user_id = u.id
            WHERE r.organization_id = ? AND r.status = ?
            ORDER BY u.name ASC
        ', [$organizationId, 'approved']);
        
        return response()->json([
            'residents' => array_map(function($r) {
                return [
                    'id' => $r->id,
                    'name' => $r->name,
                    'avatar' => $r->avatar,
                ];
            }, $residents)
        ]);
    }
    
    /**
     * Store or update the featured story
     */
    public function store(Request $request)
    {
        $request->validate([
            'resident_id' => 'required|exists:users,id',
            'bio' => 'required|string|min:20|max:2000',
        ]);
        
        $user = Auth::user();
        
        if (!$user || !$user->isAdmin()) {
            return back()->withErrors(['error' => 'Unauthorized']);
        }
        
        $adminRecord = DB::selectOne('SELECT organization_id FROM admin WHERE user_id = ?', [$user->id]);
        
        if (!$adminRecord) {
            return back()->withErrors(['error' => 'Organization not found']);
        }
        
        $organizationId = $adminRecord->organization_id;
        
        // Verify the resident belongs to this organization
        $resident = DB::selectOne('
            SELECT r.id
            FROM resident r
            WHERE r.user_id = ? AND r.organization_id = ? AND r.status = ?
        ', [$request->resident_id, $organizationId, 'approved']);
        
        if (!$resident) {
            return back()->withErrors(['resident_id' => 'Selected resident is not valid for your organization']);
        }
        
        try {
            // Check if featured story already exists
            $existing = DB::selectOne('SELECT id FROM featured_story WHERE organization_id = ?', [$organizationId]);
            
            if ($existing) {
                // Update existing
                DB::update('
                    UPDATE featured_story 
                    SET resident_id = ?, bio = ?, updated_at = ?
                    WHERE organization_id = ?
                ', [$request->resident_id, $request->bio, now(), $organizationId]);
            } else {
                // Create new
                DB::insert('
                    INSERT INTO featured_story (organization_id, resident_id, bio, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?)
                ', [$organizationId, $request->resident_id, $request->bio, now(), now()]);
            }
            
            return redirect()->back()->with('success', 'Featured story updated successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to save featured story: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Delete the featured story
     */
    public function destroy()
    {
        $user = Auth::user();
        
        if (!$user || !$user->isAdmin()) {
            return back()->withErrors(['error' => 'Unauthorized']);
        }
        
        $adminRecord = DB::selectOne('SELECT organization_id FROM admin WHERE user_id = ?', [$user->id]);
        
        if (!$adminRecord) {
            return back()->withErrors(['error' => 'Organization not found']);
        }
        
        $organizationId = $adminRecord->organization_id;
        
        DB::delete('DELETE FROM featured_story WHERE organization_id = ?', [$organizationId]);
        
        return redirect()->back()->with('success', 'Featured story removed successfully!');
    }
    
    /**
     * Get featured story for platform pages (public endpoint)
     */
    public function getForPlatform(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['featured_story' => null]);
        }
        
        // Get user's organization ID
        $organizationId = null;
        
        if ($user->isResident()) {
            $resident = DB::selectOne('SELECT organization_id FROM resident WHERE user_id = ?', [$user->id]);
            $organizationId = $resident?->organization_id;
        } elseif ($user->isVolunteer()) {
            $volunteer = DB::selectOne('SELECT organization_id FROM volunteer WHERE user_id = ?', [$user->id]);
            $organizationId = $volunteer?->organization_id;
        }
        
        if (!$organizationId) {
            return response()->json(['featured_story' => null]);
        }
        
        $featuredStory = DB::selectOne('
            SELECT 
                u.name,
                u.avatar,
                fs.bio
            FROM featured_story fs
            JOIN users u ON fs.resident_id = u.id
            WHERE fs.organization_id = ?
        ', [$organizationId]);
        
        if (!$featuredStory) {
            return response()->json(['featured_story' => null]);
        }
        
        // Build profile photo URL if avatar exists
        $profilePhotoUrl = null;
        if ($featuredStory->avatar) {
            $profilePhotoUrl = '/images/avatars/' . $featuredStory->avatar;
        }
        
        return response()->json([
            'featured_story' => [
                'name' => $featuredStory->name,
                'profile_photo_url' => $profilePhotoUrl,
                'bio' => $featuredStory->bio,
            ]
        ]);
    }
}
