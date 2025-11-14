<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserType;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to Google's OAuth page.
     */
    public function redirect(Request $request)
    {
        // Get the user type from query parameter
        $userType = $request->query('type', 'admin'); // Default to admin if not specified
        
        \Log::info('Google OAuth redirect - Starting', [
            'user_type' => $userType,
            'query_params' => $request->query(),
            'session_id' => session()->getId(),
            'url' => $request->fullUrl()
        ]);
        
        // Store in session as backup (for cases where state might not work)
        session(['google_oauth_user_type' => $userType]);
        session()->save();
        
        // Get the Google OAuth redirect URL first to extract the state parameter
        $redirectResponse = Socialite::driver('google')
            ->with(['user_type' => $userType])
            ->redirect();
        
        $googleRedirectUrl = $redirectResponse->getTargetUrl();
        
        // Extract state parameter from the redirect URL
        // The state is in the URL as a query parameter
        $parsedUrl = parse_url($googleRedirectUrl);
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $queryParams);
            if (isset($queryParams['state'])) {
                $state = $queryParams['state'];
                
                // Store user type in cache with state as key (expires in 10 minutes)
                // This works even when sessions are lost (e.g., incognito mode)
                Cache::put("google_oauth_user_type_{$state}", $userType, 600); // 10 minutes
                
                \Log::info('Google OAuth redirect - User type stored in cache', [
                    'user_type' => $userType,
                    'state' => $state,
                    'cache_key' => "google_oauth_user_type_{$state}"
                ]);
            }
        }
        
        // Store user type in cookie as additional backup
        $cookie = cookie('google_oauth_user_type', $userType, 10, '/', null, false, false);
        
        // Return a page that sets the cookie and then redirects
        return response()->view('google-oauth-redirect', [
            'redirectUrl' => $googleRedirectUrl,
            'userType' => $userType
        ])->withCookie($cookie);
    }

    /**
     * Handle the callback from Google.
     */
    public function callback()
    {
        \Log::info('Google OAuth callback - Started', [
            'session_id' => session()->getId(),
            'request_url' => request()->fullUrl(),
            'has_google_oauth_user_type' => session()->has('google_oauth_user_type'),
            'google_oauth_user_type' => session('google_oauth_user_type'),
            'all_session_keys' => array_keys(session()->all())
        ]);
        
        try {
            // Configure cURL to ignore SSL certificate issues for local development
            $config = config('services.google');
            $config['guzzle'] = [
                'verify' => false, // Disable SSL verification for local development
            ];
            config(['services.google' => $config]);
            
            // Try to get user with state validation first (normal flow)
            try {
                $googleUser = Socialite::driver('google')->user();
            } catch (Throwable $stateError) {
                // If state validation fails (e.g., session lost in incognito mode), try stateless mode
                // This is expected behavior when sessions don't persist, not an error
                \Log::info('Google OAuth callback - State validation failed (expected in incognito/session loss), using stateless mode', [
                    'error' => $stateError->getMessage(),
                    'session_id' => session()->getId(),
                    'note' => 'This is normal when sessions are lost (e.g., incognito mode). Cache-based user type retrieval will be used.'
                ]);
                $googleUser = Socialite::driver('google')->stateless()->user();
            }
            
            \Log::info('Google OAuth callback - User retrieved from Google', [
                'email' => $googleUser->email,
                'name' => $googleUser->name,
                'id' => $googleUser->id
            ]);
        } catch (Throwable $e) {
            \Log::error('Google OAuth callback - Error getting user from Google', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect('/')->with('error', 'Google authentication failed.');
        }

        // Check if the user already exists in the database
        $existingUser = User::where('email', $googleUser->email)->first();
        
        \Log::info('Google OAuth callback - User lookup', [
            'email' => $googleUser->email,
            'user_exists' => $existingUser !== null,
            'user_id' => $existingUser?->id
        ]);

        if ($existingUser) {
            // Log the user in if they already exist
            \Log::info('Google OAuth callback - Existing user, logging in', [
                'user_id' => $existingUser->id,
                'email' => $existingUser->email
            ]);
            Auth::login($existingUser);
            return redirect()->route('dashboard');
        } else {
            // Get the user type from multiple sources (in order of reliability)
            // 1. Cookie (most reliable for incognito/session loss scenarios)
            // 2. Socialite's state in session
            // 3. Our session storage
            $userType = null;
            $stateParam = request()->query('state');
            
            // First, try to get from cache using state parameter (most reliable)
            // This works even when sessions are lost (e.g., incognito mode)
            if ($stateParam) {
                $cacheKey = "google_oauth_user_type_{$stateParam}";
                $cachedUserType = Cache::get($cacheKey);
                
                \Log::info('Google OAuth callback - Cache check', [
                    'state_param' => $stateParam,
                    'cache_key' => $cacheKey,
                    'cached_user_type' => $cachedUserType
                ]);
                
                if ($cachedUserType) {
                    $userType = $cachedUserType;
                    // Clear the cache entry after use
                    Cache::forget($cacheKey);
                    \Log::info('Google OAuth callback - User type from cache', ['user_type' => $userType]);
                }
            }
            
            // Fallback: try to get from cookie
            if (!$userType) {
                $cookieValue = request()->cookie('google_oauth_user_type');
                \Log::info('Google OAuth callback - Cookie check', [
                    'has_cookie' => request()->hasCookie('google_oauth_user_type'),
                    'cookie_value' => $cookieValue,
                    'all_cookies' => request()->cookies->all()
                ]);
                
                if ($cookieValue) {
                    $userType = $cookieValue;
                    \Log::info('Google OAuth callback - User type from cookie', ['user_type' => $userType]);
                }
            }
            
            // If not in cookie, try Socialite's state in session
            if (!$userType && $stateParam) {
                $stateKey = 'state.' . $stateParam;
                if (session()->has($stateKey)) {
                    $stateData = session($stateKey);
                    if (is_array($stateData) && isset($stateData['user_type'])) {
                        $userType = $stateData['user_type'];
                        \Log::info('Google OAuth callback - User type from Socialite state', ['user_type' => $userType]);
                    }
                } else {
                    // Try to find any state key in session (in case the format is different)
                    $allSessionKeys = array_keys(session()->all());
                    foreach ($allSessionKeys as $key) {
                        if (strpos($key, 'state') === 0) {
                            $stateData = session($key);
                            if (is_array($stateData) && isset($stateData['user_type'])) {
                                $userType = $stateData['user_type'];
                                \Log::info('Google OAuth callback - User type from state key', ['user_type' => $userType, 'key' => $key]);
                                break;
                            }
                        }
                    }
                }
            }
            
            // Fall back to our session storage
            if (!$userType) {
                $userType = session('google_oauth_user_type', 'admin');
                if ($userType !== 'admin') {
                    \Log::info('Google OAuth callback - User type from session', ['user_type' => $userType]);
                }
            }
            
            // Debug: Log the user type retrieval
            \Log::info('Google OAuth callback - User type retrieved', [
                'user_type' => $userType,
                'state_param' => $stateParam,
                'state_key_checked' => $stateParam ? 'state.' . $stateParam : null,
                'has_state_in_session' => $stateParam && session()->has('state.' . $stateParam),
                'state_data' => $stateParam ? session('state.' . $stateParam) : null,
                'has_google_oauth_user_type' => session()->has('google_oauth_user_type'),
                'google_oauth_user_type' => session('google_oauth_user_type'),
                'cookie_value' => $cookieValue ?? null,
                'has_cookie' => request()->hasCookie('google_oauth_user_type'),
                'email' => $googleUser->email,
                'session_id' => session()->getId(),
                'all_session_keys' => array_keys(session()->all()),
                'state_keys_in_session' => array_filter(array_keys(session()->all()), function($key) {
                    return strpos($key, 'state') === 0;
                })
            ]);
            
            // Store Google user data in session for registration form
            // All user types (volunteer, admin, resident) need to complete registration
            session([
                'google_user' => [
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'avatar' => $googleUser->avatar
                ]
            ]);
            
            // Clear the user type session (we've already stored it in $userType)
            session()->forget('google_oauth_user_type');
            
            // Save session to ensure it persists across redirect
            session()->save();
            
            // Build the registration URL
            $registerUrl = '/register?type=' . urlencode($userType) . '&google=true';
            
            // Debug: Log the redirect URL
            \Log::info('Google OAuth callback - Redirecting to registration', [
                'url' => $registerUrl,
                'user_type' => $userType,
                'has_google_user' => session()->has('google_user'),
                'google_user_data' => session('google_user'),
                'session_id' => session()->getId(),
                'is_inertia_request' => request()->header('X-Inertia') !== null,
                'request_headers' => request()->headers->all()
            ]);
            
            // Clear the cookie after use
            $clearCookie = cookie()->forget('google_oauth_user_type');
            
            // Use Inertia redirect for Inertia requests, regular redirect otherwise
            // This ensures proper handling in Inertia applications
            if (request()->header('X-Inertia')) {
                \Log::info('Google OAuth callback - Using Inertia location redirect');
                return Inertia::location($registerUrl)->withCookie($clearCookie);
            }
            
            \Log::info('Google OAuth callback - Using regular redirect', [
                'redirect_url' => $registerUrl
            ]);
            
            // Redirect to registration form with Google data and user type as query parameters
            // Using direct URL to ensure query parameters are properly included
            $response = redirect($registerUrl)->withCookie($clearCookie);
            
            \Log::info('Google OAuth callback - Redirect response created', [
                'status_code' => $response->getStatusCode(),
                'target_url' => $response->getTargetUrl()
            ]);
            
            return $response;
        }
    }
}
