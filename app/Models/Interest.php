<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Interest extends Model
{
    protected $fillable = [
        'name',
    ];

    /**
     * Get the users that have this interest
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_interests');
    }
}
