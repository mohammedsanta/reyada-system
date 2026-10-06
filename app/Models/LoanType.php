<?php

// app/Models/LoanType.php
// "نوع القرض": قرض شخصي / تمويل عقاري / قرض سلع معمرة ...
// A lookup table (not a fixed list in the code) so an administrator can add a new type without a new migration.
// Table: loan_types  (migration 2026_10_01_000011_create_loan_types_table.php)

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanType extends Model
{
    /** @use HasFactory<\Database\Factories\LoanTypeFactory> */
    use HasFactory;

    protected $fillable = [
        'name',        // "قرض شخصي"  (unique)
        'is_active',   // a stopped type disappears from the dropdowns but old cases keep it
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /* ---------------------------------------------------------------
     | Relations
     * ------------------------------------------------------------- */

    // The cases (debts) of this type  (debt_cases.loan_type_id)
    public function debtCases(): HasMany
    {
        return $this->hasMany(DebtCase::class);
    }

    /* ---------------------------------------------------------------
     | Query scopes
     * ------------------------------------------------------------- */

    // only the types that may be chosen in new forms
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return blank($term) ? $query : $query->where('name', 'like', "%{$term}%");
    }

    /* ---------------------------------------------------------------
     | Helpers
     * ------------------------------------------------------------- */

    // [id => name] of the ACTIVE types, for <select> lists:   LoanType::options()
    public static function options(): array
    {
        return static::query()->active()->orderBy('name')->pluck('name', 'id')->all();
    }

    /* ---------------------------------------------------------------
     | Model events
     * ------------------------------------------------------------- */

    protected static function booted(): void
    {
        // a type that is used by cases cannot be deleted: stop it instead (the settings page shows the same message)
        static::deleting(function (LoanType $type) {
            if ($type->debtCases()->exists()) {
                throw new \LogicException("لا يمكن حذف «{$type->name}» لأنه مستخدم في حالات. عطّله بدلاً من حذفه.");
            }
        });
    }
}