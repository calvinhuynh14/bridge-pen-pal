<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Admin extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'organization_id',
    ];

    /**
     * Get the user that owns this admin record
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the organization that this admin belongs to
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
