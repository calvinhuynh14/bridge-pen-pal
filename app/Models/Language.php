<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Language extends Model
{
    protected $fillable = [
        'name',
        'code'
    ];

    /**
     * Get the users that speak this language
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_languages')
            ->withPivot('proficiency_level')
            ->withTimestamps();
    }
}
