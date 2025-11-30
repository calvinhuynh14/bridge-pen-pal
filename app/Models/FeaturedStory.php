<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeaturedStory extends Model
{
    protected $table = 'featured_story';
    
    protected $fillable = [
        'organization_id',
        'resident_id',
        'bio',
    ];

    /**
     * Get the organization that owns the featured story
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the resident (user) being featured
     */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resident_id');
    }
}
