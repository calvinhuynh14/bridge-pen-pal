<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
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

        // Customize the register view to pass user type
        Fortify::registerView(function (Request $request) {
            $userType = $request->query('type', 'resident');
            $organizations = [];
            
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

        // Customize the redirect after login based on user type
        Fortify::redirects('login', function () {
            $user = auth()->user();
            
            if ($user && $user->user_type === 'admin') {
                return route('admin.dashboard');
            }
            
            return route('dashboard');
        });

        // Customize the redirect after registration based on user type
        Fortify::redirects('register', function () {
            $user = auth()->user();
            
            if ($user) {
                switch ($user->user_type) {
                    case 'admin':
                        return route('admin.dashboard');
                    case 'volunteer':
                        return route('application.submitted');
                    default:
                        return route('dashboard');
                }
            }
            
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
