<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to Google's OAuth page.
     */
    public function redirect(Request $request)
    {
        // Store the user type in session for later use
        $userType = $request->query('type', 'admin'); // Default to admin if not specified
        session(['google_oauth_user_type' => $userType]);
        
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the callback from Google.
     */
    public function callback()
    {
        try {
            // Configure cURL to ignore SSL certificate issues for local development
            $config = config('services.google');
            $config['guzzle'] = [
                'verify' => false, // Disable SSL verification for local development
            ];
            config(['services.google' => $config]);
            
            // Get the user information from Google
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            return redirect('/')->with('error', 'Google authentication failed.');
        }

        // Check if the user already exists in the database
        $existingUser = User::where('email', $googleUser->email)->first();

        if ($existingUser) {
            // Log the user in if they already exist
            Auth::login($existingUser);
            return redirect()->route('dashboard');
        } else {
            // Create new user directly with Google data
            try {
                // Get the user type from session
                $userType = session('google_oauth_user_type', 'admin');
                
                $newUser = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'password' => bcrypt(Str::random(16)), // Set a random password
                    'email_verified_at' => now(),
                    'user_type' => $userType // Use the stored user type
                ]);

                // Clear the session data
                session()->forget('google_oauth_user_type');

                // Log the user in
                Auth::login($newUser);

                // Redirect to dashboard (we'll add the modal later)
                return redirect()->route('dashboard');
            } catch (Throwable $e) {
                return redirect('/')->with('error', 'Failed to create user account.');
            }
        }
    }
}
