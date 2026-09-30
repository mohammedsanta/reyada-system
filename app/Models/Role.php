<?php

namespace App\Models;

// Base Eloquent Model
use Illuminate\Database\Eloquent\Model;

// Used for the hasMany relationship
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{

    protected $fillable = ['name'];

    // One Role can have many Employees
    public function employees(): HasMany
    {
        // $this = the current Role
        // hasMany() = this Role is related to many Employees
        // Employee::class = the Model we want to relate to
        //
        // Laravel will use:
        // employees.role_id = roles.id
        return $this->hasMany(Employee::class);
    }
}