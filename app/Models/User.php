<?php

// app/Models/User.php
// The employees who log in to Collex.
// Table: users

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    public const STATUS_ACTIVE    = 'active';
    public const STATUS_INACTIVE  = 'inactive';
    public const STATUS_SUSPENDED = 'suspended';

    /*
    |--------------------------------------------------------------------------
    | Supervisor level
    |--------------------------------------------------------------------------
    */

    public const SUPERVISOR_LEVEL = 50;

    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'employee_code',
        'name',
        'email',
        'phone',
        'password',
        'role_id',
        'supervisor_id',
        'status',
        'is_system_account',
        'avatar_path',
    ];

    /*
    |--------------------------------------------------------------------------
    | Hidden
    |--------------------------------------------------------------------------
    */

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'is_system_account' => 'boolean',
            'password'          => 'hashed',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Permission cache
    |--------------------------------------------------------------------------
    */

    protected ?Collection $permissionCache = null;

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    // Employee's role.
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    // Employee's supervisor.
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(self::class, 'supervisor_id');
    }

    // Employees working under this employee.
    public function team(): HasMany
    {
        return $this->hasMany(self::class, 'supervisor_id');
    }

    // Personal permission overrides.
    public function permissionOverrides(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_user')
            ->withPivot('granted')
            ->withTimestamps();
    }

    // Banks assigned to this employee.
    public function banks(): BelongsToMany
    {
        return $this->belongsToMany(Bank::class, 'bank_user')
            ->withPivot('assigned_by')
            ->withTimestamps();
    }

    // Installment companies assigned to this employee.
    public function installmentCompanies(): BelongsToMany
    {
        return $this->belongsToMany(
            InstallmentCompany::class,
            'installment_company_user'
        )
            ->withPivot('assigned_by')
            ->withTimestamps();
    }

// Promises to pay registered by this employee.
public function promisesToPay(): HasMany
{
    return $this->hasMany(PromiseToPay::class);
}

// Payments collected by this employee.
public function payments(): HasMany
{
    return $this->hasMany(Payment::class, 'collector_id');
}

    // Promises to pay registered by this employee.
    public function promises(): HasMany
    {
        return $this->hasMany(PromiseToPay::class);
    }

    // Payments collected by this employee.
    public function collectedPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'collector_id');
    }

    // Field visits assigned to this employee.
    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    // "Ahmed Borlsy" -> "AB"
    // "HOOL" -> "HO"
    protected function initials(): Attribute
    {
        return Attribute::get(function () {
            $words = preg_split('/\s+/u', trim($this->name));

            return mb_strtoupper(
                count($words) > 1
                    ? mb_substr($words[0], 0, 1)
                        . mb_substr($words[1], 0, 1)
                    : mb_substr($words[0], 0, 2)
            );
        });
    }

    // Public URL of employee avatar.
    protected function avatarUrl(): Attribute
    {
        return Attribute::get(
            fn () => $this->avatar_path
                ? Storage::disk('public')->url($this->avatar_path)
                : null
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    // Every real employee except system accounts.
    public function scopeEmployees(Builder $query): Builder
    {
        return $query->where('is_system_account', false);
    }

    // Employees whose role can supervise others.
    public function scopeSupervisors(Builder $query): Builder
    {
        return $query->whereHas(
            'role',
            fn (Builder $role) =>
                $role->where('level', '>=', self::SUPERVISOR_LEVEL)
        );
    }

    // Search employees by name/email/phone/code.
    public function scopeSearch(
        Builder $query,
        ?string $term
    ): Builder {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere(
                    'employee_code',
                    'like',
                    "%{$term}%"
                );
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Status Helpers
    |--------------------------------------------------------------------------
    */

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    // System account or Owner.
    public function isOwner(): bool
    {
        return $this->is_system_account
            || (bool) $this->role?->isOwner();
    }

    public function isSupervisor(): bool
    {
        return ($this->role?->level ?? 0)
            >= self::SUPERVISOR_LEVEL;
    }

/*
|--------------------------------------------------------------------------
| Employee Details Helpers
|--------------------------------------------------------------------------
|
| These functions are used by the Employee Details page/service.
|
*/

// Cases currently assigned to this employee.
//
// Keep both names because different parts of the application
// may use either cases() or assignedCases().
public function cases(): HasMany
{
    return $this->hasMany(DebtCase::class, 'assigned_user_id');
}

public function assignedCases(): HasMany
{
    return $this->cases();
}

// Number of cases currently assigned to the employee.
public function casesCount(): int
{
    return $this->cases()->count();
}

// Total amount of payments collected by the employee.
public function collectedAmount(): float
{
    return (float) $this->collectedPayments()->sum('amount');
}

// Number of promises registered by the employee.
public function promisesCount(): int
{
    return $this->promises()->count();
}

// Number of active promises.
public function activePromisesCount(): int
{
    return $this->promises()
        ->where('status', 'active')
        ->count();
}


    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    */

    // Final permissions:
    // role permissions + granted overrides - revoked overrides.
    public function permissionNames(): Collection
    {
        return $this->permissionCache
            ??= $this->calculatePermissionNames();
    }

    private function calculatePermissionNames(): Collection
    {
        $this->loadMissing(
            'role.permissions',
            'permissionOverrides'
        );

        $fromRole = $this->role?->permissions->pluck('name')
            ?? collect();

        $granted = $this->permissionOverrides
            ->filter(
                fn ($p) => (bool) $p->pivot->granted
            )
            ->pluck('name');

        $revoked = $this->permissionOverrides
            ->filter(
                fn ($p) => ! $p->pivot->granted
            )
            ->pluck('name');

        return $fromRole
            ->merge($granted)
            ->diff($revoked)
            ->unique()
            ->values();
    }

    public function hasPermission(string $permission): bool
    {
        return $this->isOwner()
            || $this->permissionNames()->contains($permission);
    }

    public function flushPermissionCache(): void
    {
        $this->permissionCache = null;

        $this->unsetRelation('role')
            ->unsetRelation('permissionOverrides');
    }

    /*
    |--------------------------------------------------------------------------
    | Employee Code
    |--------------------------------------------------------------------------
    */

    // Returns the next available employee code.
    public static function nextEmployeeCode(): string
    {
        $last = static::withTrashed()
            ->select(
                DB::raw(
                    'MAX(CAST(employee_code AS UNSIGNED)) as last_code'
                )
            )
            ->value('last_code');

        return (string) (
            max((int) $last, 85599) + 1
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Model Events
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        // Automatically generate employee code.
        static::creating(function (User $user) {
            $user->employee_code ??= static::nextEmployeeCode();
        });

        // System accounts cannot be deleted.
        static::deleting(function (User $user) {
            if ($user->is_system_account) {
                throw new \LogicException(
                    'لا يمكن حذف حساب النظام.'
                );
            }
        });
    }
}