<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/admin/login', function () {
    return Inertia::render('Auth/AdminLogin', [
        'canResetPassword' => Route::has('password.request'),
        'status' => session('status'),
    ]);
})->name('admin.login');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Main dashboard route - redirects based on user type
    Route::get('/dashboard', function () {
        $user = auth()->user();
        
        // Redirect admins to admin dashboard
        if ($user->user_type === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        
        // Redirect residents and volunteers to platform home
        return redirect()->route('platform.home');
    })->name('dashboard');
    
    // Admin dashboard
    Route::get('/admin/dashboard', function () {
        return Inertia::render('AdminDashboard');
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
});
