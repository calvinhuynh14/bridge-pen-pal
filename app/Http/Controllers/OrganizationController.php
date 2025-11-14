<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrganizationController extends Controller
{
    /**
     * Create organization and admin records for the authenticated user
     */
    public function store(Request $request)
    {
        $request->validate([
            'organization_name' => 'required|string|max:255'
        ]);

        $user = Auth::user();

        // Ensure user is an admin
        if (!$user || !$user->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if user already has an organization
        $existingAdmin = DB::select('SELECT id FROM admin WHERE user_id = ?', [$user->id]);
        
        if (!empty($existingAdmin)) {
            return response()->json(['error' => 'User already has an organization'], 400);
        }

        try {
            DB::transaction(function () use ($user, $request) {
                // Create organization using raw SQL
                DB::insert(
                    'INSERT INTO organization (name, created_at, updated_at) VALUES (?, ?, ?)',
                    [
                        $request->organization_name,
                        now(),
                        now()
                    ]
                );

                // Get the organization ID
                $organizationId = DB::getPdo()->lastInsertId();

                // Create admin record using raw SQL
                DB::insert(
                    'INSERT INTO admin (user_id, organization_id, created_at, updated_at) VALUES (?, ?, ?, ?)',
                    [
                        $user->id,
                        $organizationId,
                        now(),
                        now()
                    ]
                );
            });

            return redirect()->back();

        } catch (\Exception $e) {
            return back()->withErrors(['organization_name' => 'Failed to create organization: ' . $e->getMessage()]);
        }
    }

    /**
     * Check if the authenticated user has an organization
     */
    public function check()
    {
        $user = Auth::user();
        
        // Ensure user is an admin
        if (!$user || !$user->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $admin = DB::select('SELECT id FROM admin WHERE user_id = ?', [$user->id]);
        
        return response()->json([
            'has_organization' => !empty($admin)
        ]);
    }
}
