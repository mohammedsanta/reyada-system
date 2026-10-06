<?php

// app/Models/Bank.php
// "البنك": an institution whose debts we collect (Emirates NBD, بنك مصر ...).
// Table: banks  (migration 2026_10_01_000006_create_banks_table.php)

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Bank extends Model
{
    /** @use HasFactory<\Database\Factories\BankFactory> */
    use HasFactory, SoftDeletes;   // a deleted bank stays in the database, so its old portfolios and reports keep working

    protected $fillable = [
        'name',        // "Emirates NBD"  (unique)
        'code',        // short code: ENBD, CIB, BM ...  (unique)
        'logo_path',   // logo file inside storage/app/public
        'sector',      // subtitle under the name, e.g. "قطاع العملاء"
        'is_active',   // inactive banks are hidden from the work screens
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

    // The employees who may work on this bank (pivot: bank_user)
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'bank_user')
            ->withPivot('assigned_by')
            ->withTimestamps();
    }

    // Every monthly scope ("النطاق") of this bank, active or archived
    public function portfolios(): HasMany
    {
        return $this->hasMany(Portfolio::class);
    }

    // The scope that is open right now (a bank has only ONE active portfolio at a time)
    public function activePortfolio(): HasOne
    {
        return $this->hasOne(Portfolio::class)->where('status', 'active');
    }

    // All the cases (debts) of this bank, from every portfolio.
    // debt_cases.bank_id is a copy of portfolio.bank_id, so this needs no join.
    public function debtCases(): HasMany
    {
        return $this->hasMany(DebtCase::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    // frozen summaries of the closed months ("المستودعات الشهرية")
    public function monthlyArchives(): HasMany
    {
        return $this->hasMany(MonthlyArchive::class);
    }

    // daily collection reports (DCR) of this bank
    public function dailyReports(): HasMany
    {
        return $this->hasMany(DailyCollectionReport::class);
    }

    /* ---------------------------------------------------------------
     | Accessor:  $bank->logo
     * ------------------------------------------------------------- */

    // The public url of the logo, or null. It is named "logo" on purpose: the pages already use $bank->logo
    // (they check "if ($bank->logo ?? null)"), so they keep working unchanged when the static data is replaced by this model.
    protected function logo(): Attribute
    {
        return Attribute::get(fn () => $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null);
    }

    /* ---------------------------------------------------------------
     | Query scopes:  Bank::active()->search('nbd')->get()
     * ------------------------------------------------------------- */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    // only the banks a given employee may see (the Owner sees all of them)
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