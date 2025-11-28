<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Responses\FailedLoginResponse;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Contracts\FailedLoginResponse as FailedLoginResponseContract;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);
        
        // Customize failed login response for Inertia
        $this->app->singleton(FailedLoginResponseContract::class, FailedLoginResponse::class);

        // Customize the register view to pass user type
        Fortify::registerView(function (Request $request) {
            $userType = $request->query('type', 'resident');
            $organizations = [];
            
            \Log::info('Fortify registerView - Called', [
                'user_type' => $userType,
                'google_param' => $request->query('google'),
                'has_google_user_session' => session()->has('google_user'),
                'google_user_data' => session('google_user'),
                'session_id' => session()->getId(),
                'request_url' => $request->fullUrl(),
                'query_params' => $request->query()
            ]);
            
            // Fetch organizations for volunteer registration
            if ($userType === 'volunteer') {
                $organizations = \DB::select('SELECT id, name FROM organization ORDER BY name');
            }
            
            return inertia('Auth/Register', [
                'type' => $userType,
                'google' => $request->query('google'),
                'googleUser' => session('google_user'),
                'organizations' => $organizations,
                'canResetPassword' => \Route::has('password.request'),
                'status' => session('status'),
            ]);
        });

        // Customize the login view to pass user type
        Fortify::loginView(function (Request $request) {
            return inertia('Auth/Login', [
                'type' => $request->query('type', 'volunteer'),
                'canResetPassword' => \Route::has('password.request'),
                'status' => session('status'),
            ]);
        });

        // Customize authentication to support both email and username
        Fortify::authenticateUsing(function (Request $request) {
            if (!$request->filled('email') || !$request->filled('password')) {
                return null;
            }
            
            $loginValue = $request->email;
            
            // First, try to find user by email (for volunteers/admins)
            $user = \App\Models\User::where('email', $loginValue)->first();
            
            // If not found by email, try username (for residents)
            // This handles the case where username is sent as 'email' field
            if (!$user) {
                $user = \App\Models\User::where('username', $loginValue)->first();
            }
            
            // Verify password if user found
            if ($user && \Hash::check($request->password, $user->password)) {
                return $user;
            }
            
            return null;
        });

        // Customize the redirect after login based on user type
        Fortify::redirects('login', function () {
            $user = auth()->user();
            
            if ($user) {
                if ($user->isAdmin()) {
                    return route('admin.dashboard');
                }
                
                // Check if user is a resident (no email verification required)
                if ($user->isResident()) {
                    return route('dashboard'); // Resident dashboard
                }
                
                // For volunteers: check email verification first, then approval status
                if ($user->isVolunteer()) {
                    // If email not verified, redirect to verification page
                    if (!$user->hasVerifiedEmail()) {
                        return route('verification.notice');
                    }
                    
                    // Email is verified, check approval status
                    $volunteerStatus = \DB::select('SELECT status FROM volunteer WHERE user_id = ?', [$user->id]);
                    
                    if (empty($volunteerStatus) || $volunteerStatus[0]->status !== 'approved') {
                        // Volunteer not approved yet, show application submitted page
                        return route('application.submitted');
                    }
                }
            }
            
            return route('dashboard');
        });

        // Customize the redirect after registration based on user type
        Fortify::redirects('register', function (Request $request) {
            $user = auth()->user();
            
            // Ensure user type relationship is loaded
            if ($user && !$user->relationLoaded('userType')) {
                $user->load('userType');
            }
            
            \Log::info('Fortify register redirect - Called', [
                'user_id' => $user?->id,
                'user_email' => $user?->email,
                'user_type_id' => $user?->user_type_id,
                'user_type' => $user?->userType?->name,
                'is_admin' => $user?->isAdmin(),
                'is_volunteer' => $user?->isVolunteer(),
                'is_resident' => $user?->isResident(),
                'email_verified' => $user?->hasVerifiedEmail(),
                'session_id' => session()->getId(),
                'is_inertia_request' => $request->header('X-Inertia') !== null
            ]);
            
            if ($user) {
                // Check admin first - admins go directly to admin dashboard
                if ($user->isAdmin()) {
                    $redirectUrl = route('admin.dashboard');
                    \Log::info('Fortify register redirect - Redirecting admin', ['url' => $redirectUrl]);
                    if ($request->header('X-Inertia')) {
                        return url($redirectUrl);
                    }
                    return $redirectUrl;
                }
                
                // Check volunteer - volunteers need email verification and approval
                if ($user->isVolunteer()) {
                    // For volunteers: check email verification first
                    if (!$user->hasVerifiedEmail()) {
                        $redirectUrl = route('verification.notice');
                        \Log::info('Fortify register redirect - Redirecting volunteer to email verification', [
                            'url' => $redirectUrl
                        ]);
                        if ($request->header('X-Inertia')) {
                            return url($redirectUrl);
                        }
                        return $redirectUrl;
                    }
                    
                    // Email is verified, redirect to application submitted page
                    $redirectUrl = route('application.submitted');
                    \Log::info('Fortify register redirect - Redirecting volunteer to application submitted', [
                        'url' => $redirectUrl,
                        'full_url' => url($redirectUrl)
                    ]);
                    // For Inertia requests, return the full URL
                    if ($request->header('X-Inertia')) {
                        return url($redirectUrl);
                    }
                    return $redirectUrl;
                }
                
                // Residents and other types go to dashboard
                    $redirectUrl = route('dashboard');
                    \Log::info('Fortify register redirect - Redirecting other user type', ['url' => $redirectUrl]);
                if ($request->header('X-Inertia')) {
                    return url($redirectUrl);
                }
                return $redirectUrl;
            }
            
            \Log::warning('Fortify register redirect - No authenticated user, redirecting to dashboard');
            return route('dashboard');
        });
        
        // Customize the redirect after email verification
        Fortify::redirects('email-verification', function () {
            $user = auth()->user();
            
            if ($user && $user->isVolunteer()) {
                // Redirect volunteers to application submitted page with success message
                return route('application.submitted') . '?verified=1';
            }
            
            // For other user types, redirect to dashboard
            return route('dashboard');
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
