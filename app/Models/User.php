<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'user_type_id',
        'avatar',
        'is_anonymous',
        'anonymous_name',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
        'avatar_url',
    ];
    
    /**
     * Get the avatar URL if an avatar is selected.
     *
     * @return string|null
     */
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return '/images/avatars/' . $this->avatar;
        }
        return null;
    }
    
    /**
     * Override the profile photo URL to use avatar if no profile photo exists.
     *
     * @return string|null
     */
    public function getProfilePhotoUrlAttribute()
    {
        // Use avatar if available
        if ($this->avatar) {
            return '/images/avatars/' . $this->avatar;
        }
        
        return null;
    }
    
    /**
     * Get the display name (anonymous name if anonymous mode is enabled, otherwise real name).
     *
     * @return string
     */
    public function getDisplayNameAttribute()
    {
        if ($this->is_anonymous && $this->anonymous_name) {
            return $this->anonymous_name;
        }
        return $this->name;
    }
    
    /**
     * Get the display avatar (generic avatar if anonymous mode is enabled, otherwise user's avatar).
     *
     * @return string|null
     */
    public function getDisplayAvatarAttribute()
    {
        if ($this->is_anonymous) {
            // Return a generic/default avatar when anonymous
            return null; // Will use initials from anonymous name
        }
        return $this->avatar;
    }
    
    /**
     * Generate a random anonymous name.
     *
     * @return string
     */
    public static function generateAnonymousName()
    {
        $adjectives = ['Quiet', 'Mysterious', 'Thoughtful', 'Curious', 'Gentle', 'Wise', 'Kind', 'Peaceful', 'Calm', 'Serene'];
        $nouns = ['Writer', 'Reader', 'Friend', 'PenPal', 'Traveler', 'Dreamer', 'Explorer', 'Learner', 'Storyteller', 'Listener'];
        
        $adjective = $adjectives[array_rand($adjectives)];
        $noun = $nouns[array_rand($nouns)];
        $number = rand(100, 999);
        
        return $adjective . $noun . $number;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user type for this user.
     */
    public function userType(): BelongsTo
    {
        return $this->belongsTo(UserType::class);
    }

    /**
     * Check if user is a resident
     */
    public function isResident(): bool
    {
        return $this->userType?->name === 'resident';
    }

    /**
     * Check if user is a volunteer
     */
    public function isVolunteer(): bool
    {
        return $this->userType?->name === 'volunteer';
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->userType?->name === 'admin';
    }

    /**
     * Get the user type name
     */
    public function getUserTypeName(): ?string
    {
        return $this->userType?->name;
    }

    /**
     * Check if user requires email authentication
     */
    public function requiresEmail(): bool
    {
        return $this->userType?->requires_email ?? false;
    }

    /**
     * Check if user requires username authentication
     */
    public function requiresUsername(): bool
    {
        return $this->userType?->requires_username ?? false;
    }

    /**
     * Get the authentication method for this user
     */
    public function getAuthenticationMethod(): ?string
    {
        return $this->userType?->authentication_method;
    }

    /**
     * Get the admin record associated with this user
     */
    public function admin()
    {
        return $this->hasOne(Admin::class);
    }

    /**
     * Get the organization through the admin relationship
     */
    public function organization()
    {
        return $this->hasOneThrough(Organization::class, Admin::class);
    }

    /**
     * Get the resident record associated with this user
     */
    public function resident()
    {
        return $this->hasOne(Resident::class);
    }

    /**
     * Get the volunteer record associated with this user
     */
    public function volunteer()
    {
        return $this->hasOne(Volunteer::class);
    }

    /**
     * Get the user's interests
     */
    public function interests()
    {
        return $this->belongsToMany(Interest::class, 'user_interests');
    }

    /**
     * Get the user's languages
     */
    public function languages()
    {
        return $this->belongsToMany(Language::class, 'user_languages')
            ->withPivot('proficiency_level')
            ->withTimestamps();
    }
}
