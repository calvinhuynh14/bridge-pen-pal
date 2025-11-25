<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\LetterController;
use App\Http\Controllers\ProfileController;

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
    
    // Admin routes (require admin user type)
    Route::middleware(['admin'])->group(function () {
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
    
    // Admin management routes (require organization setup)
    Route::middleware(['admin.has.organization'])->group(function () {
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
                'organizationId' => $organizationId,
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
            'organizationId' => null,
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
    
    // API endpoint to get next sequential resident ID
    Route::get('/api/admin/residents/next-id', function (Request $request) {
        $user = auth()->user();
        $organizationId = $request->query('organization_id');
        
        if (!$organizationId) {
            return response()->json(['error' => 'Organization ID is required'], 400);
        }
        
        // Convert to integer for comparison
        $organizationId = (int)$organizationId;
        
        // Verify the admin belongs to this organization
        $adminRecord = DB::selectOne('SELECT organization_id FROM admin WHERE user_id = ?', [$user->id]);
        if (!$adminRecord) {
            return response()->json(['error' => 'Admin not found'], 403);
        }
        
        // Compare as integers
        if ((int)$adminRecord->organization_id !== $organizationId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // Find all existing resident usernames for this organization
        // Format: {orgId}XXXXX (6 digits total, where first digit(s) are orgId and rest are sequential)
        $existingResidents = DB::select("
            SELECT u.username
            FROM users u
            JOIN resident r ON u.id = r.user_id
            WHERE r.organization_id = ?
            AND u.username IS NOT NULL
            AND u.username != ''
        ", [$organizationId]);
        
        // Extract the numeric part from existing usernames and find the maximum
        // Username format: {orgId}XXXXX (6 digits total, where first digit(s) are orgId)
        $maxNumber = 0;
        $orgIdStr = (string)$organizationId;
        $orgIdLength = strlen($orgIdStr);
        
        // Ensure organization ID doesn't exceed 5 digits (to leave room for sequential part)
        if ($orgIdLength >= 6) {
            return response()->json(['error' => 'Organization ID is too long for 6-digit format'], 400);
        }
        
        foreach ($existingResidents as $resident) {
            $username = $resident->username;
            // Check if username is numeric, exactly 6 digits, and starts with organization ID
            if ($username && is_numeric($username) && strlen($username) == 6) {
                // Check if it starts with the organization ID
                $usernamePrefix = substr($username, 0, $orgIdLength);
                if ($usernamePrefix === $orgIdStr) {
                    // Extract the sequential part (everything after org ID)
                    $sequentialPart = substr($username, $orgIdLength);
                    if ($sequentialPart !== '') {
                        $number = (int)$sequentialPart;
                        if ($number > $maxNumber) {
                            $maxNumber = $number;
                        }
                    }
                }
            }
        }
        
        // Generate next sequential ID (increment by 1)
        $nextNumber = $maxNumber + 1;
        // Pad the sequential part to maintain 6-digit total
        $sequentialPadded = str_pad($nextNumber, 6 - $orgIdLength, '0', STR_PAD_LEFT);
        $nextId = $orgIdStr . $sequentialPadded;
        
        return response()->json(['next_id' => $nextId]);
    });
    
    // Update resident route
    // Create resident route
    Route::post('/admin/residents', function (Request $request) {
        $user = auth()->user();
        
        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|size:6|regex:/^[0-9]{6}$/',
            'date_of_birth' => 'required|date|date_format:Y-m-d',
            'room_number' => 'nullable|string|max:10',
            'floor_number' => 'nullable|string|max:10',
            'pin_code' => 'required|string|size:6|regex:/^[0-9]{6}$/',
        ]);
        
        // Get admin's organization
        $adminRecord = DB::selectOne('SELECT organization_id FROM admin WHERE user_id = ?', [$user->id]);
        if (!$adminRecord) {
            return response()->json(['error' => 'Admin organization not found'], 404);
        }
        $organizationId = $adminRecord->organization_id;
        
        // Check if username already exists
        $existingUser = DB::table('users')->where('username', $request->username)->first();
        if ($existingUser) {
            return back()->withErrors(['username' => 'This resident ID is already in use. Please regenerate.'])->withInput();
        }
        
        // Get resident user type ID
        $residentType = DB::selectOne('SELECT id FROM user_types WHERE name = ?', ['resident']);
        if (!$residentType) {
            return response()->json(['error' => 'Resident user type not found'], 500);
        }
        
        // Create user record
        $userId = DB::table('users')->insertGetId([
            'name' => $request->name,
            'username' => $request->username, // Store 6-digit ID as username
            'email' => null, // Residents don't have email
            'password' => bcrypt($request->pin_code),
            'user_type_id' => $residentType->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Create resident record
        DB::table('resident')->insert([
            'user_id' => $userId,
            'organization_id' => $organizationId,
            'date_of_birth' => $request->date_of_birth,
            'room_number' => $request->room_number,
            'floor_number' => $request->floor_number,
            'pin_code' => $request->pin_code, // Store plain PIN for admin viewing
            'status' => 'approved', // New residents are approved by default
            'application_date' => now(), // Set application date to current date and time
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return redirect()->route('admin.residents')->with('success', 'Resident created successfully!');
    })->name('admin.residents.store');
    
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
    
    Route::get('/admin/reports', function (Request $request) {
        $user = Auth::user();
        $admin = DB::selectOne('SELECT organization_id FROM admin WHERE user_id = ?', [$user->id]);
        
        if (!$admin) {
            return redirect()->route('admin.dashboard');
        }
        
        // Get filter and search parameters
        $statusFilter = $request->query('status', 'all');
        $search = $request->query('search', '');
        
        // Build query to get reports for this admin's organization
        // Reports are linked through users who belong to the organization
        $query = "
            SELECT 
                r.id,
                r.reason,
                r.status,
                r.created_at,
                r.resolved_at,
                r.admin_notes,
                reporter.name as reporter_name,
                reporter.id as reporter_id,
                reported_user.name as reported_user_name,
                reported_user.id as reported_user_id,
                l.id as reported_letter_id,
                resolved_by.name as resolved_by_name
            FROM reports r
            LEFT JOIN users reporter ON r.reporter_id = reporter.id
            LEFT JOIN users reported_user ON r.reported_user_id = reported_user.id
            LEFT JOIN letters l ON r.reported_letter_id = l.id
            LEFT JOIN users resolved_by ON r.resolved_by = resolved_by.id
            WHERE 1=1
        ";
        
        $params = [];
        
        // Filter by organization (through reporter or reported user)
        $query .= " AND (
            reporter.id IN (
                SELECT user_id FROM volunteer WHERE organization_id = ?
                UNION
                SELECT user_id FROM resident WHERE organization_id = ?
                UNION
                SELECT user_id FROM admin WHERE organization_id = ?
            )
            OR reported_user.id IN (
                SELECT user_id FROM volunteer WHERE organization_id = ?
                UNION
                SELECT user_id FROM resident WHERE organization_id = ?
                UNION
                SELECT user_id FROM admin WHERE organization_id = ?
            )
        )";
        $params = array_merge($params, [$admin->organization_id, $admin->organization_id, $admin->organization_id, $admin->organization_id, $admin->organization_id, $admin->organization_id]);
        
        // Apply status filter
        if ($statusFilter !== 'all') {
            $query .= " AND r.status = ?";
            $params[] = $statusFilter;
        }
        
        // Apply search filter
        if (!empty($search)) {
            $query .= " AND (
                reporter.name LIKE ? 
                OR reported_user.name LIKE ?
                OR r.reason LIKE ?
            )";
            $searchParam = "%{$search}%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
        }
        
        // Order by date (newest first)
        $query .= " ORDER BY r.created_at DESC";
        
        $reports = DB::select($query, $params);
        
        // Get statistics
        $totalReports = DB::selectOne("
            SELECT COUNT(*) as count
            FROM reports r
            LEFT JOIN users reporter ON r.reporter_id = reporter.id
            LEFT JOIN users reported_user ON r.reported_user_id = reported_user.id
            WHERE (
                reporter.id IN (
                    SELECT user_id FROM volunteer WHERE organization_id = ?
                    UNION
                    SELECT user_id FROM resident WHERE organization_id = ?
                    UNION
                    SELECT user_id FROM admin WHERE organization_id = ?
                )
                OR reported_user.id IN (
                    SELECT user_id FROM volunteer WHERE organization_id = ?
                    UNION
                    SELECT user_id FROM resident WHERE organization_id = ?
                    UNION
                    SELECT user_id FROM admin WHERE organization_id = ?
                )
            )
        ", [$admin->organization_id, $admin->organization_id, $admin->organization_id, $admin->organization_id, $admin->organization_id, $admin->organization_id]);
        
        $pendingReports = DB::selectOne("
            SELECT COUNT(*) as count
            FROM reports r
            LEFT JOIN users reporter ON r.reporter_id = reporter.id
            LEFT JOIN users reported_user ON r.reported_user_id = reported_user.id
            WHERE r.status = 'pending'
            AND (
                reporter.id IN (
                    SELECT user_id FROM volunteer WHERE organization_id = ?
                    UNION
                    SELECT user_id FROM resident WHERE organization_id = ?
                    UNION
                    SELECT user_id FROM admin WHERE organization_id = ?
                )
                OR reported_user.id IN (
                    SELECT user_id FROM volunteer WHERE organization_id = ?
                    UNION
                    SELECT user_id FROM resident WHERE organization_id = ?
                    UNION
                    SELECT user_id FROM admin WHERE organization_id = ?
                )
            )
        ", [$admin->organization_id, $admin->organization_id, $admin->organization_id, $admin->organization_id, $admin->organization_id, $admin->organization_id]);
        
        $resolvedReports = DB::selectOne("
            SELECT COUNT(*) as count
            FROM reports r
            LEFT JOIN users reporter ON r.reporter_id = reporter.id
            LEFT JOIN users reported_user ON r.reported_user_id = reported_user.id
            WHERE r.status = 'resolved'
            AND (
                reporter.id IN (
                    SELECT user_id FROM volunteer WHERE organization_id = ?
                    UNION
                    SELECT user_id FROM resident WHERE organization_id = ?
                    UNION
                    SELECT user_id FROM admin WHERE organization_id = ?
                )
                OR reported_user.id IN (
                    SELECT user_id FROM volunteer WHERE organization_id = ?
                    UNION
                    SELECT user_id FROM resident WHERE organization_id = ?
                    UNION
                    SELECT user_id FROM admin WHERE organization_id = ?
                )
            )
        ", [$admin->organization_id, $admin->organization_id, $admin->organization_id, $admin->organization_id, $admin->organization_id, $admin->organization_id]);
        
        return Inertia::render('Admin/ReportManagement', [
            'reports' => $reports,
            'statistics' => [
                'total' => $totalReports->count ?? 0,
                'pending' => $pendingReports->count ?? 0,
                'resolved' => $resolvedReports->count ?? 0,
            ],
            'filters' => [
                'status' => $statusFilter,
                'search' => $search,
            ],
        ]);
    })->name('admin.reports');
    
    // Report action routes
    Route::post('/admin/reports/{id}/resolve', function (Request $request, $id) {
        $user = Auth::user();
        $admin = DB::selectOne('SELECT organization_id FROM admin WHERE user_id = ?', [$user->id]);
        
        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // Verify report belongs to admin's organization
        $report = DB::selectOne("
            SELECT r.id, r.status
            FROM reports r
            LEFT JOIN users reporter ON r.reporter_id = reporter.id
            LEFT JOIN users reported_user ON r.reported_user_id = reported_user.id
            WHERE r.id = ?
            AND (
                reporter.id IN (
                    SELECT user_id FROM volunteer WHERE organization_id = ?
                    UNION
                    SELECT user_id FROM resident WHERE organization_id = ?
                    UNION
                    SELECT user_id FROM admin WHERE organization_id = ?
                )
                OR reported_user.id IN (
                    SELECT user_id FROM volunteer WHERE organization_id = ?
                    UNION
                    SELECT user_id FROM resident WHERE organization_id = ?
                    UNION
                    SELECT user_id FROM admin WHERE organization_id = ?
                )
            )
        ", [$id, $admin->organization_id, $admin->organization_id, $admin->organization_id, $admin->organization_id, $admin->organization_id, $admin->organization_id]);
        
        if (!$report) {
            return response()->json(['error' => 'Report not found'], 404);
        }
        
        DB::update(
            'UPDATE reports SET status = ?, resolved_by = ?, resolved_at = ?, updated_at = ? WHERE id = ?',
            ['resolved', $user->id, now(), now(), $id]
        );
        
        return redirect()->route('admin.reports')->with('success', 'Report resolved successfully');
    })->name('admin.reports.resolve');
    
    Route::post('/admin/reports/{id}/dismiss', function (Request $request, $id) {
        $user = Auth::user();
        $admin = DB::selectOne('SELECT organization_id FROM admin WHERE user_id = ?', [$user->id]);
        
        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // Verify report belongs to admin's organization
        $report = DB::selectOne("
            SELECT r.id, r.status
            FROM reports r
            LEFT JOIN users reporter ON r.reporter_id = reporter.id
            LEFT JOIN users reported_user ON r.reported_user_id = reported_user.id
            WHERE r.id = ?
            AND (
                reporter.id IN (
                    SELECT user_id FROM volunteer WHERE organization_id = ?
                    UNION
                    SELECT user_id FROM resident WHERE organization_id = ?
                    UNION
                    SELECT user_id FROM admin WHERE organization_id = ?
                )
                OR reported_user.id IN (
                    SELECT user_id FROM volunteer WHERE organization_id = ?
                    UNION
                    SELECT user_id FROM resident WHERE organization_id = ?
                    UNION
                    SELECT user_id FROM admin WHERE organization_id = ?
                )
            )
        ", [$id, $admin->organization_id, $admin->organization_id, $admin->organization_id, $admin->organization_id, $admin->organization_id, $admin->organization_id]);
        
        if (!$report) {
            return response()->json(['error' => 'Report not found'], 404);
        }
        
        DB::update(
            'UPDATE reports SET status = ?, resolved_by = ?, resolved_at = ?, updated_at = ? WHERE id = ?',
            ['dismissed', $user->id, now(), now(), $id]
        );
        
        return redirect()->route('admin.reports')->with('success', 'Report dismissed successfully');
    })->name('admin.reports.dismiss');
    
    Route::post('/admin/reports/{id}/ban', function (Request $request, $id) {
        $user = Auth::user();
        $admin = DB::selectOne('SELECT organization_id FROM admin WHERE user_id = ?', [$user->id]);
        
        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // Verify report belongs to admin's organization and get reported user
        $report = DB::selectOne("
            SELECT r.id, r.status, r.reported_user_id
            FROM reports r
            LEFT JOIN users reporter ON r.reporter_id = reporter.id
            LEFT JOIN users reported_user ON r.reported_user_id = reported_user.id
            WHERE r.id = ?
            AND (
                reporter.id IN (
                    SELECT user_id FROM volunteer WHERE organization_id = ?
                    UNION
                    SELECT user_id FROM resident WHERE organization_id = ?
                    UNION
                    SELECT user_id FROM admin WHERE organization_id = ?
                )
                OR reported_user.id IN (
                    SELECT user_id FROM volunteer WHERE organization_id = ?
                    UNION
                    SELECT user_id FROM resident WHERE organization_id = ?
                    UNION
                    SELECT user_id FROM admin WHERE organization_id = ?
                )
            )
        ", [$id, $admin->organization_id, $admin->organization_id, $admin->organization_id, $admin->organization_id, $admin->organization_id, $admin->organization_id]);
        
        if (!$report) {
            return response()->json(['error' => 'Report not found'], 404);
        }
        
        if (!$report->reported_user_id) {
            return response()->json(['error' => 'No user to ban'], 400);
        }
        
        // TODO: Implement ban functionality (create user_blocks table entry or similar)
        // For now, just mark report as resolved
        DB::update(
            'UPDATE reports SET status = ?, resolved_by = ?, resolved_at = ?, updated_at = ? WHERE id = ?',
            ['resolved', $user->id, now(), now(), $id]
        );
        
        return redirect()->route('admin.reports')->with('success', 'User banned and report resolved');
    })->name('admin.reports.ban');
    });
    
    // Organization routes (only accessible to admins)
    Route::post('/organization', [App\Http\Controllers\OrganizationController::class, 'store']);
    Route::get('/organization/check', [App\Http\Controllers\OrganizationController::class, 'check']);
    
    // Admin management routes (require organization setup) - Volunteer application actions
    Route::middleware(['admin.has.organization'])->group(function () {
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
    
        // Resident batch management routes
        Route::get('/admin/residents/batch', [App\Http\Controllers\ResidentBatchController::class, 'index'])->name('admin.residents.batch');
        Route::post('/admin/residents/batch/upload', [App\Http\Controllers\ResidentBatchController::class, 'upload'])->name('admin.residents.batch.upload');
        Route::get('/admin/residents/batch/template', [App\Http\Controllers\ResidentBatchController::class, 'downloadTemplate'])->name('admin.residents.batch.template');
    });
    });
    
    // Platform home for residents and volunteers
    Route::get('/platform/home', function () {
        $user = auth()->user();
        
        // TODO: Fetch story of the week from database
        $storyOfTheWeek = [
            'name' => 'Margaret Randy',
            'profile_photo_url' => null,
            'bio' => "At 82, Margaret has a way with plants—and people. A former flower shop owner, she knows that even a small bouquet can brighten someone's day.\n\nNow, at Willow Creek Retirement Home, she shares her love of nature through the pen pal platform. Her letters are filled with gardening tips, seasonal stories, and hand-drawn flower sketches. In return, volunteers send her pictures of their own plants—some thriving, some... still learning!\n\nFor Margaret, letters are like seeds—small connections that blossom into something beautiful. Would you like to be her next pen pal?"
        ];
        // Set to null to hide story: $storyOfTheWeek = null;
        
        // Unread letters will be fetched via API call on the frontend
        // This allows for pagination without reloading the page
        $unreadLetters = [];
        
        return Inertia::render('PlatformHome', [
            'storyOfTheWeek' => $storyOfTheWeek,
            'unreadLetters' => $unreadLetters,
        ]);
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
    // IMPORTANT: Specific routes must come before parameterized routes
    Route::get('/api/letters/received', [LetterController::class, 'getReceived'])->name('api.letters.received');
    Route::get('/api/letters/unread', [LetterController::class, 'getUnreadLetters'])->name('api.letters.unread');
    Route::get('/api/letters/{id}', [LetterController::class, 'show'])->name('api.letters.show');
    Route::get('/api/correspondence/{penPalId}', [LetterController::class, 'getCorrespondence'])->name('api.correspondence');
    Route::get('/api/pen-pals', [LetterController::class, 'getPenPals'])->name('api.pen-pals');
    
    // Write page - for writing letters
    Route::get('/platform/write', function () {
        return Inertia::render('Platform/Write');
    })->name('platform.write');
    
    // Override Jetstream's profile route to use our custom Profile/Show.vue component
    // This route must be registered before Jetstream's service provider registers its routes
    // Our route will take precedence and include organization data
    Route::get('/user/profile', [ProfileController::class, 'show'])->name('profile.show');
});
