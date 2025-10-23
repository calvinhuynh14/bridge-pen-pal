<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\GoogleAuthController;

/**
 * Welcome Route
 */
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

/**
 * Admin Login Route
 */
Route::get('/admin/login', function () {
    return Inertia::render('Auth/AdminLogin', [
        'canResetPassword' => Route::has('password.request'),
        'status' => session('status'),
    ]);
})->name('admin.login');

// Google OAuth routes
Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

    // Application submitted page for volunteers (public route)
    Route::get('/application/submitted', function () {
        return Inertia::render('Auth/ApplicationSubmitted');
    })->name('application.submitted');
    
    // Custom logout route that redirects to login
    Route::post('/logout-to-login', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login?type=volunteer');
    })->name('logout.to.login');


/**
 * Protected Routes
 */
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'volunteer.approved',
])->group(function () {
    // Main dashboard route - redirects based on user type
    Route::get('/dashboard', function () {
        $user = auth()->user();
        
        // Redirect admins to admin dashboard
        if ($user->user_type === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        
        // Volunteers and residents go to platform home (middleware ensures volunteers are approved)
        return redirect()->route('platform.home');
    })->name('dashboard');
    
    // Admin dashboard
    Route::get('/admin/dashboard', function () {
        $user = auth()->user();
        
        // Check if admin has an organization
        $hasOrganization = DB::select('SELECT id FROM admin WHERE user_id = ?', [$user->id]);
        $needsOrganizationSetup = empty($hasOrganization);
        
        // Get volunteer applications for this admin's organization
        $volunteerApplications = [];
        if (!$needsOrganizationSetup) {
            $adminRecord = DB::select('SELECT organization_id FROM admin WHERE user_id = ?', [$user->id]);
            if (!empty($adminRecord)) {
                $organizationId = $adminRecord[0]->organization_id;
                
                // Get volunteer applications using raw SQL
                $volunteerApplications = DB::select('
                    SELECT 
                        v.id,
                        v.status,
                        v.application_date,
                        v.application_notes,
                        u.name,
                        u.email,
                        o.name as organization_name
                    FROM volunteer v
                    JOIN users u ON v.user_id = u.id
                    JOIN organization o ON v.organization_id = o.id
                    WHERE v.organization_id = ?
                    ORDER BY v.application_date DESC
                ', [$organizationId]);
            }
        }
        
        return Inertia::render('AdminDashboard', [
            'needsOrganizationSetup' => $needsOrganizationSetup,
            'volunteerApplications' => $volunteerApplications
        ]);
    })->name('admin.dashboard');
    
    // Admin management routes
    Route::get('/admin/residents', function (Request $request) {
        $user = auth()->user();
        
        // Get residents for this admin's organization
        $residents = [];
        $adminRecord = DB::select('SELECT organization_id FROM admin WHERE user_id = ?', [$user->id]);
        if (!empty($adminRecord)) {
            $organizationId = $adminRecord[0]->organization_id;
            
            // Get total count for pagination
            $totalCount = DB::select('
                SELECT COUNT(*) as total
                FROM resident r
                JOIN users u ON r.user_id = u.id
                JOIN organization o ON r.organization_id = o.id
                WHERE r.organization_id = ?
            ', [$organizationId])[0]->total;
            
            // Get status counts for statistics
            $statusCounts = DB::select('
                SELECT 
                    status,
                    COUNT(*) as count
                FROM resident r
                JOIN users u ON r.user_id = u.id
                JOIN organization o ON r.organization_id = o.id
                WHERE r.organization_id = ?
                GROUP BY status
            ', [$organizationId]);
            
            // Convert to associative array for easy access
            $statusCountsArray = [];
            foreach ($statusCounts as $status) {
                $statusCountsArray[$status->status] = $status->count;
            }
            
            // Get paginated residents using raw SQL
            $page = $request->get('page', 1);
            $perPage = 10; // 10 residents per page
            $offset = ($page - 1) * $perPage;
            
            $residents = DB::select('
                SELECT 
                    r.id,
                    r.status,
                    r.application_date,
                    r.application_notes,
                    r.medical_notes,
                    u.name,
                    u.email,
                    o.name as organization_name
                FROM resident r
                JOIN users u ON r.user_id = u.id
                JOIN organization o ON r.organization_id = o.id
                WHERE r.organization_id = ?
                ORDER BY r.application_date DESC
                LIMIT ? OFFSET ?
            ', [$organizationId, $perPage, $offset]);
            
            // Calculate pagination data
            $totalPages = ceil($totalCount / $perPage);
            $hasNextPage = $page < $totalPages;
            $hasPrevPage = $page > 1;
            
            return Inertia::render('Admin/ResidentManagement', [
                'residents' => $residents,
                'pagination' => [
                    'currentPage' => (int) $page,
                    'totalPages' => $totalPages,
                    'perPage' => $perPage,
                    'total' => $totalCount,
                    'hasNextPage' => $hasNextPage,
                    'hasPrevPage' => $hasPrevPage,
                    'nextPage' => $hasNextPage ? $page + 1 : null,
                    'prevPage' => $hasPrevPage ? $page - 1 : null,
                ],
                'statusCounts' => [
                    'pending' => $statusCountsArray['pending'] ?? 0,
                    'approved' => $statusCountsArray['approved'] ?? 0,
                    'rejected' => $statusCountsArray['rejected'] ?? 0,
                ],
                'flash' => [
                    'success' => session('success'),
                    'error' => session('error'),
                ]
            ]);
        }
        
        return Inertia::render('Admin/ResidentManagement', [
            'residents' => [],
            'pagination' => [
                'currentPage' => 1,
                'totalPages' => 0,
                'perPage' => 10,
                'total' => 0,
                'hasNextPage' => false,
                'hasPrevPage' => false,
                'nextPage' => null,
                'prevPage' => null,
            ],
            'statusCounts' => [
                'pending' => 0,
                'approved' => 0,
                'rejected' => 0,
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ]
        ]);
    })->name('admin.residents');
    
    Route::get('/admin/volunteers', function (Request $request) {
        $user = auth()->user();
        
        // Get volunteer applications for this admin's organization
        $volunteerApplications = [];
        $adminRecord = DB::select('SELECT organization_id FROM admin WHERE user_id = ?', [$user->id]);
        if (!empty($adminRecord)) {
            $organizationId = $adminRecord[0]->organization_id;
            
            // Get total count for pagination
            $totalCount = DB::select('
                SELECT COUNT(*) as total
                FROM volunteer v
                JOIN users u ON v.user_id = u.id
                JOIN organization o ON v.organization_id = o.id
                WHERE v.organization_id = ?
            ', [$organizationId])[0]->total;
            
            // Get status counts for statistics
            $statusCounts = DB::select('
                SELECT 
                    status,
                    COUNT(*) as count
                FROM volunteer v
                JOIN users u ON v.user_id = u.id
                JOIN organization o ON v.organization_id = o.id
                WHERE v.organization_id = ?
                GROUP BY status
            ', [$organizationId]);
            
            // Convert to associative array for easy access
            $statusCountsArray = [];
            foreach ($statusCounts as $status) {
                $statusCountsArray[$status->status] = $status->count;
            }
            
            // Get paginated volunteer applications using raw SQL
            $page = $request->get('page', 1);
            $perPage = 10; // 10 applications per page
            $offset = ($page - 1) * $perPage;
            
            $volunteerApplications = DB::select('
                SELECT 
                    v.id,
                    v.status,
                    v.application_date,
                    v.application_notes,
                    u.name,
                    u.email,
                    o.name as organization_name
                FROM volunteer v
                JOIN users u ON v.user_id = u.id
                JOIN organization o ON v.organization_id = o.id
                WHERE v.organization_id = ?
                ORDER BY v.application_date DESC
                LIMIT ? OFFSET ?
            ', [$organizationId, $perPage, $offset]);
            
            // Calculate pagination data
            $totalPages = ceil($totalCount / $perPage);
            $hasNextPage = $page < $totalPages;
            $hasPrevPage = $page > 1;
            
            return Inertia::render('Admin/VolunteerManagement', [
                'volunteerApplications' => $volunteerApplications,
                'pagination' => [
                    'currentPage' => (int) $page,
                    'totalPages' => $totalPages,
                    'perPage' => $perPage,
                    'total' => $totalCount,
                    'hasNextPage' => $hasNextPage,
                    'hasPrevPage' => $hasPrevPage,
                    'nextPage' => $hasNextPage ? $page + 1 : null,
                    'prevPage' => $hasPrevPage ? $page - 1 : null,
                ],
                'statusCounts' => [
                    'pending' => $statusCountsArray['pending'] ?? 0,
                    'approved' => $statusCountsArray['approved'] ?? 0,
                    'rejected' => $statusCountsArray['rejected'] ?? 0,
                ],
                'flash' => [
                    'success' => session('success'),
                    'error' => session('error'),
                ]
            ]);
        }
        
        return Inertia::render('Admin/VolunteerManagement', [
            'volunteerApplications' => [],
            'pagination' => [
                'currentPage' => 1,
                'totalPages' => 0,
                'perPage' => 10,
                'total' => 0,
                'hasNextPage' => false,
                'hasPrevPage' => false,
                'nextPage' => null,
                'prevPage' => null,
            ],
            'statusCounts' => [
                'pending' => 0,
                'approved' => 0,
                'rejected' => 0,
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ]
        ]);
    })->name('admin.volunteers');
    
    Route::get('/admin/reports', function () {
        return Inertia::render('Admin/ReportManagement');
    })->name('admin.reports');
    
    // Platform home for residents and volunteers
    Route::get('/platform/home', function () {
        return Inertia::render('PlatformHome');
    })->name('platform.home');
    
    
    // Profile settings for all users
    Route::get('/profile/settings', function () {
        return Inertia::render('ProfileSettings');
    })->name('profile.settings');
    
    // Organization routes
    Route::post('/organization', [App\Http\Controllers\OrganizationController::class, 'store']);
    Route::get('/organization/check', [App\Http\Controllers\OrganizationController::class, 'check']);
    
    // Volunteer application actions
    Route::post('/admin/volunteers/{id}/approve', function ($id) {
        $user = auth()->user();
        
        // Verify admin has access to this volunteer
        $adminRecord = DB::select('SELECT organization_id FROM admin WHERE user_id = ?', [$user->id]);
        if (empty($adminRecord)) {
            return back()->withErrors(['error' => 'Unauthorized']);
        }
        
        $organizationId = $adminRecord[0]->organization_id;
        
        // Get volunteer name before updating
        $volunteer = DB::select('
            SELECT u.name 
            FROM volunteer v 
            JOIN users u ON v.user_id = u.id 
            WHERE v.id = ? AND v.organization_id = ?
        ', [$id, $organizationId]);
        
        if (empty($volunteer)) {
            return back()->withErrors(['error' => 'Volunteer application not found']);
        }
        
        $volunteerName = $volunteer[0]->name;
        
        // Update volunteer status to approved
        $updated = DB::update('
            UPDATE volunteer 
            SET status = ?, updated_at = ? 
            WHERE id = ? AND organization_id = ?
        ', ['approved', now(), $id, $organizationId]);
        
        if ($updated) {
            return redirect()->route('admin.volunteers')->with('success', "Volunteer application for {$volunteerName} approved successfully");
        } else {
            return back()->withErrors(['error' => 'Volunteer application not found or already processed']);
        }
    })->name('admin.volunteers.approve');
    
    Route::post('/admin/volunteers/{id}/reject', function ($id) {
        $user = auth()->user();
        
        // Verify admin has access to this volunteer
        $adminRecord = DB::select('SELECT organization_id FROM admin WHERE user_id = ?', [$user->id]);
        if (empty($adminRecord)) {
            return back()->withErrors(['error' => 'Unauthorized']);
        }
        
        $organizationId = $adminRecord[0]->organization_id;
        
        // Get volunteer name before updating
        $volunteer = DB::select('
            SELECT u.name 
            FROM volunteer v 
            JOIN users u ON v.user_id = u.id 
            WHERE v.id = ? AND v.organization_id = ?
        ', [$id, $organizationId]);
        
        if (empty($volunteer)) {
            return back()->withErrors(['error' => 'Volunteer application not found']);
        }
        
        $volunteerName = $volunteer[0]->name;
        
        // Update volunteer status to rejected
        $updated = DB::update('
            UPDATE volunteer 
            SET status = ?, updated_at = ? 
            WHERE id = ? AND organization_id = ?
        ', ['rejected', now(), $id, $organizationId]);
        
        if ($updated) {
            return redirect()->route('admin.volunteers')->with('success', "Volunteer application for {$volunteerName} rejected successfully");
        } else {
            return back()->withErrors(['error' => 'Volunteer application not found or already processed']);
        }
    })->name('admin.volunteers.reject');
    
    Route::delete('/admin/volunteers/{id}/delete', function ($id) {
        $user = auth()->user();
        
        // Verify admin has access to this volunteer
        $adminRecord = DB::select('SELECT organization_id FROM admin WHERE user_id = ?', [$user->id]);
        if (empty($adminRecord)) {
            return back()->withErrors(['error' => 'Unauthorized']);
        }
        
        $organizationId = $adminRecord[0]->organization_id;
        
        // Get volunteer name before deleting
        $volunteer = DB::select('
            SELECT u.name 
            FROM volunteer v 
            JOIN users u ON v.user_id = u.id 
            WHERE v.id = ? AND v.organization_id = ?
        ', [$id, $organizationId]);
        
        if (empty($volunteer)) {
            return back()->withErrors(['error' => 'Volunteer application not found']);
        }
        
        $volunteerName = $volunteer[0]->name;
        
        // Delete volunteer application
        $deleted = DB::delete('
            DELETE FROM volunteer 
            WHERE id = ? AND organization_id = ?
        ', [$id, $organizationId]);
        
        if ($deleted) {
            return redirect()->route('admin.volunteers')->with('success', "Volunteer application for {$volunteerName} deleted successfully");
        } else {
            return back()->withErrors(['error' => 'Volunteer application not found']);
        }
    })->name('admin.volunteers.delete');
});
