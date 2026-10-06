<?php

namespace App\Models;

// Base Eloquent Model
use Illuminate\Database\Eloquent\Model;

// Used because Collection belongs to Account and Employee
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Collection extends Model
{
    protected $fillable = [
        'account_id',
        'collector_id',
        'amount',
        'payment_method',
        'reference_number',
        'collected_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'collected_at' => 'datetime',
    ];

    // Every Collection belongs to one Account
    public function account(): BelongsTo
    {
        // collections table contains:
        // account_id
        //
        // account_id points to:
        // accounts.id
        return $this->belongsTo(Account::class);
    }

    // Every Collection belongs to one Collector
    public function collector(): BelongsTo
    {
        // The Collector is an Employee
        //
        // collections table contains:
        // collector_id
        //
        // collector_id points to:
        // employees.id
        return $this->belongsTo(
            Employee::class,
            'collector_id'
        );
    }
}