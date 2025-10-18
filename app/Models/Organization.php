<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * Get the admins for this organization
     */
    public function admins()
    {
        return $this->hasMany(Admin::class);
    }

    /**
     * Get the users through the admins relationship
     */
    public function users()
    {
        return $this->hasManyThrough(User::class, Admin::class);
    }
}
