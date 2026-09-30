<?php

namespace App\Models;

// Base Eloquent Model
use Illuminate\Database\Eloquent\Model;

// Used when Account belongs to another Model
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Used when Account has many Collections
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    protected $fillable = [
        'customer_id',
        'organization_id',
        'account_number',
        'original_amount',
        'remaining_amount',
        'loan_opened_at',
        'last_payment_date',
        'last_payment_amount',
        'loan_expiry_date',
        'status',
        'assigned_collector_id',
    ];

    protected $casts = [
        'original_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'last_payment_amount' => 'decimal:2',
        'loan_opened_at' => 'date',
        'last_payment_date' => 'date',
        'loan_expiry_date' => 'date',
    ];

    // Every Account belongs to one Customer
    public function customer(): BelongsTo
    {
        // Account contains:
        // customer_id
        //
        // customer_id points to:
        // customers.id
        return $this->belongsTo(Customer::class);
    }

    // Every Account belongs to one Organization
    public function organization(): BelongsTo
    {
        // Account contains:
        // organization_id
        //
        // organization_id points to:
        // organizations.id
        return $this->belongsTo(Organization::class);
    }

    // An Account can be assigned to one Collector
    public function assignedCollector(): BelongsTo
    {
        // The Collector is actually an Employee
        //
        // That's why we use:
        // Employee::class
        //
        // The Foreign Key is:
        // assigned_collector_id
        //
        // It points to:
        // employees.id
        return $this->belongsTo(
            Employee::class,
            'assigned_collector_id'
        );
    }

    // One Account can have many Collection records
    public function collections(): HasMany
    {
        // Laravel automatically uses:
        // collections.account_id
        //
        // and matches it with:
        // accounts.id
        return $this->hasMany(Collection::class);
    }
}