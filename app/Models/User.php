<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
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
    ];

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
