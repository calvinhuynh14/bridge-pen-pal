<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resident extends Model
{
    protected $table = 'resident';
    
    protected $fillable = [
        'user_id',
        'organization_id',
        'status',
        'application_date',
        'room_number',
        'floor_number',
        'date_of_birth',
        'pin_code'
    ];

    protected $casts = [
        'application_date' => 'datetime',
        'date_of_birth' => 'date',
    ];

    /**
     * Get the user that owns the resident record
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the organization that owns the resident record
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
