<?php

namespace App\Models;

// Base Eloquent Model
use Illuminate\Database\Eloquent\Model;

// Used for many-to-many relationships
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

// Used when one Organization has many Accounts
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    // An Organization can have many Supervisors
    public function supervisors(): BelongsToMany
    {
        // Organization <-> Employee
        //
        // The relationship is stored in:
        // organization_supervisors
        //
        // organization_id = current Organization
        //
        // supervisor_id = related Employee
        return $this->belongsToMany(
            Employee::class,
            'organization_supervisors',
            'organization_id',
            'supervisor_id'
        );
    }

    // One Organization can have many Accounts
    public function accounts(): HasMany
    {
        // Laravel automatically expects:
        // accounts.organization_id
        //
        // to point to:
        // organizations.id
        return $this->hasMany(Account::class);
    }
}