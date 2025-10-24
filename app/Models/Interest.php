<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Interest extends Model
{
    protected $fillable = [
        'name',
        'category_id'
    ];

    /**
     * Get the category that owns the interest
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(InterestCategory::class);
    }

    /**
     * Get the users that have this interest
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_interests');
    }
}
