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
    Route::get('/admin/residents', function () {
        return Inertia::render('Admin/ResidentManagement');
    })->name('admin.residents');
    
    Route::get('/admin/volunteers', function () {
        return Inertia::render('Admin/VolunteerManagement');
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
});
