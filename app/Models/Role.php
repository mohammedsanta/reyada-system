<?php

// app/Models/Role.php
// "الرتبة النظامية": Owner / Super Visor / Call Center ... and the permissions each role has.
// Table: roles  (migration 2026_10_01_000001_create_roles_table.php)

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    /** @use HasFactory<\Database\Factories\RoleFactory> */
    use HasFactory;

    // machine name of the built-in role that can do everything (roles.name)
    public const OWNER = 'owner';

    // columns that can be filled with Role::create([...]) / $role->update([...])  (mass assignment protection)
    protected $fillable = [
        'name',          // machine name used in code: owner, super_visor, call_center
        'label',         // text shown in the UI: "Super Visor"
        'description',
        'is_system',     // true = built-in role: cannot be edited or deleted from the UI
        'level',         // hierarchy: higher number = more authority (Owner = 100)
    ];

    // how database values are converted to PHP values
    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',   // 1 / 0  ->  true / false
            'level'     => 'integer',
        ];
    }

    /* ---------------------------------------------------------------
     | Relations
     * ------------------------------------------------------------- */

    // The employees who have this role  (users.role_id -> roles.id)
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // The permissions of this role (pivot table: permission_role, it has no timestamps)
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role');
    }

    /* ---------------------------------------------------------------
     | Query scopes:  Role::editable()->get()
     * ------------------------------------------------------------- */

    // roles that an administrator may edit or delete (everything except the system roles)
    public function scopeEditable(Builder $query): Builder
    {
        return $query->where('is_system', false);
    }

    /* ---------------------------------------------------------------
     | Helpers
     * ------------------------------------------------------------- */

    public function isOwner(): bool
    {
        return $this->name === self::OWNER;
    }

    // does this role have the permission?   $role->hasPermission('banks.view')
    public function hasPermission(string $permission): bool
    {
        // the Owner can do everything, without reading the pivot table
        if ($this->isOwner()) {
            return true;
        }

        // uses the already loaded relation when there is one (no extra query), otherwise asks the database
        return $this->relationLoaded('permissions')
            ? $this->permissions->contains('name', $permission)
            : $this->permissions()->where('name', $permission)->exists();
    }

    // replace ALL the permissions of the role:  $role->syncPermissions([1, 2, 5])
    public function syncPermissions(array $permissionIds): array
    {
        return $this->permissions()->sync($permissionIds);
    }

    /* ---------------------------------------------------------------
     | Model events
     * ------------------------------------------------------------- */

    protected static function booted(): void
    {
        // a second line of defense: even code that skips the controller cannot delete a system role
        static::deleting(function (Role $role) {
            if ($role->is_system) {
                throw new \LogicException('لا يمكن حذف رتبة النظام.');
            }
        });
    }
}