<?php

// app/Models/Permission.php
// One row for every single thing a user can be allowed to do, named  <module>.<action>  (banks.view, ptp.manage ...).
// Table: permissions  (migration 2026_10_01_000002_create_permissions_table.php)
// Permissions are created by developers / seeders (when a new feature is added), not from the screens.

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    /** @use HasFactory<\Database\Factories\PermissionFactory> */
    use HasFactory;

    protected $fillable = [
        'name',    // machine name checked in code: $user->can('banks.view')
        'label',   // Arabic text shown in the permissions screens: "عرض"
        'group',   // module name (banks, users, ptp ...): used to group the checkboxes
    ];

    /* ---------------------------------------------------------------
     | Relations
     * ------------------------------------------------------------- */

    // The roles that have this permission (pivot: permission_role)
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'permission_role');
    }

    // The employees with a PERSONAL exception for this permission (pivot: permission_user).
    // pivot "granted": true = an extra permission, false = a permission taken away from him.
    // Read it with $user->pivot->granted  (it comes as 1 / 0 from the pivot table).
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'permission_user')
            ->withPivot('granted')
            ->withTimestamps();
    }

    /* ---------------------------------------------------------------
     | Accessors:  $permission->module  /  $permission->action
     * ------------------------------------------------------------- */

    // "banks.view"  ->  "banks"
    protected function module(): Attribute
    {
        return Attribute::get(fn () => explode('.', $this->name, 2)[0]);
    }

    // "banks.view"  ->  "view"
    protected function action(): Attribute
    {
        return Attribute::get(fn () => explode('.', $this->name, 2)[1] ?? '');
    }

    /* ---------------------------------------------------------------
     | Query scopes:  Permission::inGroup('banks')->get()
     * ------------------------------------------------------------- */

    public function scopeInGroup(Builder $query, string $group): Builder
    {
        return $query->where('group', $group);
    }

    /* ---------------------------------------------------------------
     | Helpers
     * ------------------------------------------------------------- */

    // All permissions grouped by module, ready for the checkbox grids of the roles and the user-permissions pages:
    //   Permission::grouped()  ->  ['banks' => [view, manage, import], 'users' => [...], ...]
    public static function grouped(): Collection
    {
        return static::query()->orderBy('group')->orderBy('id')->get()->groupBy('group');
    }
}