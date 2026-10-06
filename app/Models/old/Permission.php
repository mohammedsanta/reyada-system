<?php

namespace App\Models;

// Base Eloquent Model
use Illuminate\Database\Eloquent\Model;

// Used for many-to-many relationships
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = ['name'];

    // One Permission can belong to many Roles
    public function roles(): BelongsToMany
    {
        // belongsToMany() = many-to-many relationship
        //
        // Role::class
        // The other Model involved in the relationship
        //
        // role_permissions
        // The Pivot Table that connects Roles and Permissions
        return $this->belongsToMany(
            Role::class,
            'role_permissions'
        );
    }
}