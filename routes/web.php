<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\LetterController;

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
        if ($user->isAdmin()) {
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
        
        // Get volunteer applications and resident count for this admin's organization
        $volunteerApplications = [];
        $totalResidents = 0;
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
                
                // Get total residents count
                $residentsCount = DB::select('
                    SELECT COUNT(*) as total
                    FROM resident r
                    JOIN users u ON r.user_id = u.id
                    JOIN organization o ON r.organization_id = o.id
                    WHERE r.organization_id = ?
                ', [$organizationId]);
                $totalResidents = $residentsCount[0]->total ?? 0;
            }
        }
        
        return Inertia::render('AdminDashboard', [
            'needsOrganizationSetup' => $needsOrganizationSetup,
            'volunteerApplications' => $volunteerApplications,
            'totalResidents' => $totalResidents
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
            
            // Get all residents (client-side pagination)
            $residents = DB::select('
                SELECT 
                    r.id,
                    r.status,
                    r.application_date,
                    r.room_number,
                    r.floor_number,
                    r.date_of_birth,
                    r.pin_code,
                    u.name,
                    u.username,
                    u.email,
                    o.name as organization_name
                FROM resident r
                JOIN users u ON r.user_id = u.id
                JOIN organization o ON r.organization_id = o.id
                WHERE r.organization_id = ?
                ORDER BY r.application_date DESC
            ', [$organizationId]);
            
            return Inertia::render('Admin/ResidentManagement', [
                'residents' => $residents,
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
    
    // Update resident route
    Route::put('/admin/residents/{id}', function (Request $request, $id) {
        $user = auth()->user();
        
        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
            'room_number' => 'nullable|string|max:10',
            'floor_number' => 'nullable|string|max:10',
            'pin_code' => 'required|string|size:6|regex:/^[0-9]{6}$/',
        ]);
        
        // Get the resident
        $resident = DB::table('resident')
            ->join('users', 'resident.user_id', '=', 'users.id')
            ->where('resident.id', $id)
            ->select('resident.*', 'users.id as user_id')
            ->first();
            
        if (!$resident) {
            return response()->json(['error' => 'Resident not found'], 404);
        }
        
        // Update user name
        DB::table('users')
            ->where('id', $resident->user_id)
            ->update([
                'name' => $request->name,
                'password' => bcrypt($request->pin_code), // Update password with new PIN
                'updated_at' => now()
            ]);
        
        // Update resident details
        DB::table('resident')
            ->where('id', $id)
            ->update([
                'room_number' => $request->room_number,
                'floor_number' => $request->floor_number,
                'pin_code' => $request->pin_code, // Store plain PIN for admin viewing
                'updated_at' => now()
            ]);
        
        return redirect()->route('admin.residents')->with('success', 'Resident updated successfully!');
    })->name('admin.residents.update');
    
    // Delete resident route
    Route::delete('/admin/residents/{id}', function (Request $request, $id) {
        $user = auth()->user();
        
        // Get the resident
        $resident = DB::table('resident')
            ->join('users', 'resident.user_id', '=', 'users.id')
            ->where('resident.id', $id)
            ->select('resident.*', 'users.id as user_id', 'users.name as user_name')
            ->first();
            
        if (!$resident) {
            return response()->json(['error' => 'Resident not found'], 404);
        }
        
        $residentName = $resident->user_name;
        
        // Delete the resident record
        DB::table('resident')->where('id', $id)->delete();
        
        // Delete the associated user record
        DB::table('users')->where('id', $resident->user_id)->delete();
        
        return redirect()->route('admin.residents')->with('success', "Resident '{$residentName}' has been deleted successfully!");
    })->name('admin.residents.destroy');
    
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
    
    // Discover page - for finding open letters
    Route::get('/platform/discover', function (Request $request) {
        $user = auth()->user();
        $userType = $user->user_type;

        // Base query for open letters
        $query = "
            SELECT 
                l.id,
                l.content,
                l.sent_at,
                l.created_at,
                l.claimed_by,
                l.is_open_letter,
                sender.id as sender_id,
                sender.name as sender_name,
                ut.name as sender_type,
                receiver.id as receiver_id,
                receiver.name as receiver_name
            FROM letters l
            JOIN users sender ON l.sender_id = sender.id
            JOIN user_types ut ON sender.user_type_id = ut.id
            LEFT JOIN users receiver ON l.receiver_id = receiver.id
            WHERE l.is_open_letter = 1
            AND l.status IN ('sent', 'delivered')
            AND l.deleted_at IS NULL
        ";

        $params = [];

        // Filter based on user type
        if ($userType === 'volunteer') {
            // Volunteers can only see open letters from residents
            $query .= " AND ut.name = 'resident'";
        } elseif ($userType === 'resident') {
            // Residents can see open letters from other residents
            $query .= " AND ut.name = 'resident'";
        }

        // Exclude letters already claimed by current user
        $query .= " AND (l.claimed_by IS NULL OR l.claimed_by != ?)";
        $params[] = $user->id;

        // Exclude letters sent by current user
        $query .= " AND l.sender_id != ?";
        $params[] = $user->id;

        // Order by most recent first
        $query .= " ORDER BY l.sent_at DESC, l.created_at DESC";

        $openLetters = DB::select($query, $params);

        // TODO: Fetch story of the week from database
        // For now, using placeholder data that can be set by admin
        // Example structure:
        $storyOfTheWeek = [
            'name' => 'Margaret Randy',
            'profile_photo_url' => null, // or URL to profile photo
            'bio' => "At 82, Margaret has a way with plants—and people. A former flower shop owner, she knows that even a small bouquet can brighten someone's day.\n\nNow, at Willow Creek Retirement Home, she shares her love of nature through the pen pal platform. Her letters are filled with gardening tips, seasonal stories, and hand-drawn flower sketches. In return, volunteers send her pictures of their own plants—some thriving, some... still learning!\n\nFor Margaret, letters are like seeds—small connections that blossom into something beautiful. Would you like to be her next pen pal?"
        ];
        // Set to null to hide: $storyOfTheWeek = null;

        return Inertia::render('Platform/Discover', [
            'openLetters' => $openLetters,
            'letterCount' => count($openLetters),
            'storyOfTheWeek' => $storyOfTheWeek
        ]);
    })->name('platform.discover');
    
    // Letter routes
    Route::post('/platform/letters/{id}/claim', [LetterController::class, 'claim'])->name('letters.claim');
    Route::post('/platform/letters/{id}/report', [LetterController::class, 'report'])->name('letters.report');
    Route::post('/api/letters', [LetterController::class, 'store'])->name('api.letters.store');
    Route::get('/api/letters/received', [LetterController::class, 'getReceived'])->name('api.letters.received');
    Route::get('/api/letters/{id}', [LetterController::class, 'show'])->name('api.letters.show');
    Route::get('/api/correspondence/{penPalId}', [LetterController::class, 'getCorrespondence'])->name('api.correspondence');
    Route::get('/api/pen-pals', [LetterController::class, 'getPenPals'])->name('api.pen-pals');
    
    // Write page - for writing letters
    Route::get('/platform/write', function () {
        return Inertia::render('Platform/Write');
    })->name('platform.write');
    
    // Profile settings for all users
    Route::get('/profile/settings', function () {
        return Inertia::render('ProfileSettings');
    })->name('profile.settings');
    
    // Organization routes
    Route::post('/organization', [App\Http\Controllers\OrganizationController::class, 'store']);
    Route::get('/organization/check', [App\Http\Controllers\OrganizationController::class, 'check']);
    
    // Resident batch management routes
    Route::get('/admin/residents/batch', [App\Http\Controllers\ResidentBatchController::class, 'index'])->name('admin.residents.batch');
    Route::post('/admin/residents/batch/upload', [App\Http\Controllers\ResidentBatchController::class, 'upload'])->name('admin.residents.batch.upload');
    Route::get('/admin/residents/batch/template', [App\Http\Controllers\ResidentBatchController::class, 'downloadTemplate'])->name('admin.residents.batch.template');
    
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
