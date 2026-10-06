<?php

namespace App\Models;

// Base Eloquent Model
use Illuminate\Database\Eloquent\Model;

// Used when one Customer has many Accounts
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'national_id',
        'phone',
        'email',
        'governorate',
        'address',
        'employer_name',
        'job_title',
        'work_phone',
        'work_address',
    ];

    // One Customer can have many Accounts
    public function accounts(): HasMany
    {
        // Laravel automatically uses:
        // accounts.customer_id
        //
        // and matches it with:
        // customers.id
        return $this->hasMany(Account::class);
    }
}