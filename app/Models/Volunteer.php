<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Volunteer extends Model
{
    protected $table = 'volunteer';
    
    protected $fillable = [
        'user_id',
        'organization_id',
        'status',
        'application_date'
    ];

    protected $casts = [
        'application_date' => 'datetime',
    ];

    /**
     * Get the user that owns the volunteer record
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the organization that owns the volunteer record
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
