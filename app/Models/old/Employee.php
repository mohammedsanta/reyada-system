<?php

namespace App\Models;

// Base Eloquent Model
use Illuminate\Database\Eloquent\Model;

// Used when the current Employee belongs to another Model
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Used for many-to-many relationships
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

// Used when the current Employee has many records
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'national_id',
        'date_of_birth',
        'gender',
        'phone',
        'email',
        'address',
        'marital_status',
        'role_id',
        'hire_date',
        'job_title',
        'salary',
        'is_active',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'salary' => 'decimal:2',
        'is_active' => 'boolean',
    ];



    // Every Employee belongs to one Role
    public function role(): BelongsTo
    {
        // Employee has role_id
        // role_id points to roles.id
        //
        // Laravel will understand:
        // employees.role_id = roles.id
        return $this->belongsTo(Role::class);
    }

    // A Supervisor can belong to many Organizations
    public function organizations(): BelongsToMany
    {
        // Employee <-> Organization
        // This is a many-to-many relationship
        //
        // organization_supervisors is the Pivot Table
        //
        // supervisor_id = Employee's ID in the Pivot Table
        //
        // organization_id = Organization's ID in the Pivot Table
        return $this->belongsToMany(
            Organization::class,
            'organization_supervisors',
            'supervisor_id',
            'organization_id'
        );
    }

    // A Supervisor can have many Collectors
    public function collectors(): BelongsToMany
    {
        // This is a self many-to-many relationship
        //
        // Both Supervisor and Collector are Employees
        // That's why we use Employee::class
        //
        // supervisor_collectors is the Pivot Table
        //
        // supervisor_id = current Employee (Supervisor)
        //
        // collector_id = the Employee who is the Collector
        return $this->belongsToMany(
            Employee::class,
            'supervisor_collectors',
            'supervisor_id',
            'collector_id'
        );
    }

    // A Collector can belong to one or more Supervisors
    public function supervisors(): BelongsToMany
    {
        // This is the opposite direction of collectors()
        //
        // Here the current Employee is the Collector
        //
        // collector_id = current Employee
        //
        // supervisor_id = related Employee
        return $this->belongsToMany(
            Employee::class,
            'supervisor_collectors',
            'collector_id',
            'supervisor_id'
        );
    }

    // Get all Accounts assigned to this Employee
    public function assignedAccounts(): HasMany
    {
        // One Collector can have many Accounts
        //
        // Account uses assigned_collector_id
        // instead of the default employee_id
        //
        // So we explicitly tell Laravel the Foreign Key
        return $this->hasMany(
            Account::class,
            'assigned_collector_id'
        );
    }

    // Get all Collections made by this Employee
    public function collections(): HasMany
    {
        // One Collector can make many Collections
        //
        // collections table contains:
        // collector_id
        //
        // That column points to employees.id
        return $this->hasMany(
            Collection::class,
            'collector_id'
        );
    }
}