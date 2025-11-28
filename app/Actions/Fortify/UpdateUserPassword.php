<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;

class UpdateUserPassword implements UpdatesUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and update the user's password.
     *
     * @param  array<string, string>  $input
     */
    public function update(User $user, array $input): void
    {
        // First validate current password
        Validator::make($input, [
            'current_password' => ['required', 'string', 'current_password:web'],
        ], [
            'current_password.current_password' => __('The provided password does not match your current password.'),
        ])->validateWithBag('updatePassword');

        // Check if new password is the same as current password
        if (Hash::check($input['password'], $user->password)) {
            $validator = Validator::make($input, [
                'password' => [
                    function ($attribute, $value, $fail) {
                        $fail('The new password must be different from your current password.');
                    },
                ],
            ]);
            $validator->validateWithBag('updatePassword');
            return; // Stop here if passwords match
        }

        // Validate password requirements
        Validator::make($input, [
            'password' => $this->passwordRules(),
        ])->validateWithBag('updatePassword');

        $user->forceFill([
            'password' => Hash::make($input['password']),
        ])->save();
    }
}
