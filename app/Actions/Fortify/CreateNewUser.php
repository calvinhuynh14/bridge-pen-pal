<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
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
        
        // Define validation rules based on user type
        $rules = [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'user_type' => ['required', 'string', 'in:resident,volunteer,admin'],
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ];

        // Add type-specific validation rules
        switch ($userType) {
            case 'resident':
                $rules['name'] = ['required', 'string', 'max:255'];
                break;
            case 'volunteer':
                $rules['first_name'] = ['required', 'string', 'max:255'];
                $rules['last_name'] = ['required', 'string', 'max:255'];
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

        return User::create([
            'name' => $name,
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'user_type' => $input['user_type'],
        ]);
    }
}
