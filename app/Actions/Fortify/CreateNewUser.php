<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\UserType;
use App\Models\Organization;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $userTypeId = $input['user_type_id'];
        
        // Get user type name for validation
        $userType = UserType::find($userTypeId);
        if (!$userType) {
            throw new \InvalidArgumentException('Invalid user type ID');
        }
        $userTypeName = $userType->name;
        
        // Check if this is a Google OAuth user
        $isGoogleUser = session()->has('google_user');
        
        // Define validation rules based on user type
        $rules = [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'user_type_id' => ['required', 'integer', 'exists:user_types,id'],
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ];
        
        // Password is only required for non-Google users
        if (!$isGoogleUser) {
            $rules['password'] = $this->passwordRules();
        }

        // Add type-specific validation rules
        switch ($userTypeName) {
            case 'resident':
                $rules['name'] = ['required', 'string', 'max:255'];
                break;
            case 'volunteer':
                $rules['first_name'] = ['required', 'string', 'max:255'];
                $rules['last_name'] = ['required', 'string', 'max:255'];
                $rules['organization_id'] = ['required', 'integer', 'exists:organization,id'];
                $rules['application_notes'] = ['nullable', 'string', 'max:2000'];
                break;
            case 'admin':
                $rules['organization_name'] = ['required', 'string', 'max:255'];
                break;
        }

        Validator::make($input, $rules)->validate();

        // Determine the name field based on user type
        // Sanitize all text inputs to prevent XSS attacks
        $sanitizeText = function($text) {
            if (empty($text)) {
                return $text;
            }
            // Strip HTML tags and trim whitespace
            return trim(strip_tags($text));
        };
        
        // Sanitize name fields based on user type
        $name = match ($userTypeName) {
            'resident' => $sanitizeText($input['name']),
            'volunteer' => $sanitizeText($input['first_name']) . ' ' . $sanitizeText($input['last_name']),
            'admin' => $sanitizeText($input['organization_name']),
            default => $sanitizeText($input['name'] ?? 'User'),
        };
        
        // Create the user
        $user = User::create([
            'name' => $name,
            'email' => $input['email'],
            'password' => $isGoogleUser ? Hash::make(Str::random(16)) : Hash::make($input['password']),
            'user_type_id' => $userTypeId,
        ]);

        /**
         * Use raw SQL queries
         */
        
        // Handle user type specific logic
        if ($userTypeName === 'admin') {
            // Create organization and admin record
            DB::transaction(function () use ($user, $input, $sanitizeText) {
                // Sanitize organization name
                $organizationName = $sanitizeText($input['organization_name']);
                
                // Create organization using raw SQL
                DB::insert(
                    'INSERT INTO organization (name, created_at, updated_at) VALUES (?, ?, ?)',
                    [
                        $organizationName,
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
        } elseif ($userTypeName === 'volunteer') {
            // Create volunteer application record
            $organizationName = null;
            DB::transaction(function () use ($user, $input, $sanitizeText, &$organizationName) {
                // Sanitize application_notes to prevent XSS attacks
                $applicationNotes = null;
                if (!empty($input['application_notes'])) {
                    $applicationNotes = $sanitizeText($input['application_notes']);
                    // If empty after sanitization, set to null
                    if (empty($applicationNotes)) {
                        $applicationNotes = null;
                    }
                }
                
                // Get organization name for email
                $organization = DB::selectOne('SELECT name FROM organization WHERE id = ?', [$input['organization_id']]);
                $organizationName = $organization ? $organization->name : 'the organization';
                
                DB::insert(
                    'INSERT INTO volunteer (user_id, organization_id, status, application_date, application_notes, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)',
                    [
                        $user->id,
                        $input['organization_id'],
                        'pending',
                        now(),
                        $applicationNotes,
                        now(),
                        now()
                    ]
                );
            });
            
            // Send application submitted email notification
            if ($organizationName) {
                $user->notify(new \App\Notifications\ApplicationSubmittedNotification($organizationName));
            }
        }

        // Clear Google user session if it exists
        if ($isGoogleUser) {
            session()->forget('google_user');
        }

        \Log::info('CreateNewUser - User created successfully', [
            'user_id' => $user->id,
            'email' => $user->email,
            'user_type' => $userTypeName,
            'is_google_user' => $isGoogleUser,
            'volunteer_created' => $userTypeName === 'volunteer'
        ]);

        return $user;
    }
}
