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
        
        // Define validation rules based on user type
        $rules = [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'user_type_id' => ['required', 'integer', 'exists:user_types,id'],
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ];

        // Add type-specific validation rules
        switch ($userTypeName) {
            case 'resident':
                $rules['name'] = ['required', 'string', 'max:255'];
                break;
            case 'volunteer':
                $rules['first_name'] = ['required', 'string', 'max:255'];
                $rules['last_name'] = ['required', 'string', 'max:255'];
                $rules['organization_id'] = ['required', 'integer', 'exists:organization,id'];
                // Removed application_notes validation as column was dropped
                break;
            case 'admin':
                $rules['organization_name'] = ['required', 'string', 'max:255'];
                break;
        }

        Validator::make($input, $rules)->validate();

        // Determine the name field based on user type
        $name = match ($userTypeName) {
            'resident' => $input['name'],
            'volunteer' => $input['first_name'] . ' ' . $input['last_name'],
            'admin' => $input['organization_name'],
            default => $input['name'] ?? 'User',
        };

        // Check if this is a Google OAuth user
        $isGoogleUser = session()->has('google_user');
        
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
        } elseif ($userTypeName === 'volunteer') {
            // Create volunteer application record
            DB::transaction(function () use ($user, $input) {
                DB::insert(
                    'INSERT INTO volunteer (user_id, organization_id, status, application_date, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)',
                    [
                        $user->id,
                        $input['organization_id'],
                        'pending',
                        now(),
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
