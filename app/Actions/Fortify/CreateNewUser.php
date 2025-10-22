<?php

namespace App\Actions\Fortify;

use App\Models\User;
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
        $userType = $input['user_type'];
        
        // Check if this is a Google OAuth user
        $isGoogleUser = session()->has('google_user');
        
        // Debug logging
        \Log::info('CreateNewUser called', [
            'user_type' => $userType,
            'is_google_user' => $isGoogleUser,
            'input_keys' => array_keys($input)
        ]);
        
        // Define validation rules based on user type
        $rules = [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'user_type' => ['required', 'string', 'in:resident,volunteer,admin'],
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ];
        
        // Only require password for non-Google users
        if (!$isGoogleUser) {
            $rules['password'] = $this->passwordRules();
        }

        // Add type-specific validation rules
        switch ($userType) {
            case 'resident':
                $rules['name'] = ['required', 'string', 'max:255'];
                break;
            case 'volunteer':
                $rules['first_name'] = ['required', 'string', 'max:255'];
                $rules['last_name'] = ['required', 'string', 'max:255'];
                $rules['organization_id'] = ['required', 'integer', 'exists:organization,id'];
                $rules['application_notes'] = ['nullable', 'string', 'max:1000'];
                break;
            case 'admin':
                $rules['organization_name'] = ['required', 'string', 'max:255'];
                break;
        }

        Validator::make($input, $rules)->validate();

        // Determine the name field based on user type
        $name = match ($userType) {
            'resident' => $input['name'],
            'volunteer' => $input['first_name'] . ' ' . $input['last_name'],
            'admin' => $input['organization_name'],
            default => $input['name'] ?? 'User',
        };
        
        // Create the user
        $user = User::create([
            'name' => $name,
            'email' => $input['email'],
            'password' => $isGoogleUser ? Hash::make(Str::random(16)) : Hash::make($input['password']),
            'user_type' => $input['user_type'],
        ]);

        /**
         * Use raw SQL queries
         */
        // Handle user type specific logic
        if ($userType === 'admin') {
            // Create organization and admin record
            DB::transaction(function () use ($user, $input) {
                // Create organization using raw SQL
                DB::insert(
                    'INSERT INTO organization (name, created_at, updated_at) VALUES (?, ?, ?)',
                    [
                        $input['organization_name'],
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
        } elseif ($userType === 'volunteer') {
            // Create volunteer application record
            DB::transaction(function () use ($user, $input) {
                DB::insert(
                    'INSERT INTO volunteer (user_id, organization_id, status, application_date, application_notes, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)',
                    [
                        $user->id,
                        $input['organization_id'],
                        'pending',
                        now(),
                        $input['application_notes'] ?? null,
                        now(),
                        now()
                    ]
                );
            });
        }

        // Clear Google user session if it exists
        if ($isGoogleUser) {
            session()->forget('google_user');
        }

        return $user;
    }
}
