<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, mixed>  $input
     */
    public function update(User $user, array $input): void
    {
        // Determine validation rules based on user type
        $rules = [
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
        ];
        
        // Residents cannot update any profile information
        if ($user->isResident()) {
            // Residents cannot update their profile information
            // Return early without making any changes
            return;
        } else {
            // Volunteers can only update name (email is read-only)
            if ($user->isVolunteer()) {
                $rules['name'] = ['required', 'string', 'max:255'];
            }
            
            // Admins can only update organization name (no name or email fields for admins)
            if ($user->isAdmin()) {
                $rules['organization_name'] = ['required', 'string', 'max:255'];
            }
            
            Validator::make($input, $rules)->validateWithBag('updateProfileInformation');

            if (isset($input['photo'])) {
                $user->updateProfilePhoto($input['photo']);
            }

            // Update organization name for admins
            if ($user->isAdmin() && isset($input['organization_name'])) {
                // Get the admin's organization ID
                $admin = DB::selectOne('SELECT organization_id FROM admin WHERE user_id = ?', [$user->id]);
                
                if ($admin) {
                    // Update organization name using raw SQL
                    DB::update(
                        'UPDATE organization SET name = ?, updated_at = ? WHERE id = ?',
                        [
                            strip_tags(trim($input['organization_name'])), // Sanitize input
                            now(),
                            $admin->organization_id
                        ]
                    );
                }
            }

            // Update user fields based on user type
            $updateData = [];
            
            // Volunteers can only update name (email is read-only)
            if ($user->isVolunteer() && isset($input['name'])) {
                $updateData['name'] = strip_tags(trim($input['name'])); // Sanitize input
            }
            
            // Admins don't update user fields (only organization name is updated separately)
            if (!empty($updateData)) {
                $user->forceFill($updateData)->save();
            }
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'name' => $input['name'],
            'email' => $input['email'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
