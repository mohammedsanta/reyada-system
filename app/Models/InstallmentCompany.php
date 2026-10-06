<?php

// app/Models/InstallmentCompany.php
// "شركة التقسيط": same idea as a bank, kept in its own table because it has its own section in the sidebar.
// Table: installment_companies  (migration 2026_10_01_000007_create_installment_companies_table.php)

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class InstallmentCompany extends Model
{
    /** @use HasFactory<\Database\Factories\InstallmentCompanyFactory> */
    use HasFactory, SoftDeletes;   // a deleted company stays in the database

    protected $fillable = [
        'name',        // "فاليو"  (unique)
        'code',        // short code: VALU, SOHL ...  (unique)
        'logo_path',   // logo file inside storage/app/public
        'is_active',
        'notes',
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

    // The employees who may work on this company (pivot: installment_company_user)
    // A separate pivot from bank_user (instead of one polymorphic table) keeps real foreign keys.
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'installment_company_user')
            ->withPivot('assigned_by')
            ->withTimestamps();
    }

    // NOTE: in the current database design portfolios and debt cases belong to BANKS only.
    // When installment companies get their own monthly scopes, add an installment_company_id column to
    // `portfolios` (and `debt_cases`) and the relations portfolios() / debtCases() here.

    /* ---------------------------------------------------------------
     | Accessor:  $company->logo   (same name as on Bank, so the pages treat them alike)
     * ------------------------------------------------------------- */

    protected function logo(): Attribute
    {
        return Attribute::get(fn () => $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null);
    }

    /* ---------------------------------------------------------------
     | Query scopes:  InstallmentCompany::active()->visibleTo($user)->get()
     * ------------------------------------------------------------- */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    // only the companies a given employee may see (the Owner sees all of them)
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $user->isOwner()
            ? $query
            : $query->whereHas('users', fn (Builder $q) => $q->whereKey($user->getKey()));
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(fn (Builder $q) => $q->where('name', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"));
    }
}