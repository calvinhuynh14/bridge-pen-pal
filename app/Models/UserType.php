<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserType extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'requires_email',
        'requires_username',
        'authentication_method',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'requires_email' => 'boolean',
        'requires_username' => 'boolean',
    ];

    /**
     * Get the users for this user type.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if this user type requires email authentication.
     */
    public function requiresEmail(): bool
    {
        return $this->requires_email;
    }

    /**
     * Check if this user type requires username authentication.
     */
    public function requiresUsername(): bool
    {
        return $this->requires_username;
    }

    /**
     * Get the authentication method for this user type.
     */
    public function getAuthenticationMethod(): string
    {
        return $this->authentication_method;
    }

    /**
     * Scope to get user type by name.
     */
    public function scopeByName($query, string $name)
    {
        return $query->where('name', $name);
    }
}
