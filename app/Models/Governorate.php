<?php

// app/Models/Governorate.php
// "المحافظة": lookup table (القاهرة، الجيزة، أسيوط ...), filled once by a seeder with the 27 governorates of Egypt.
// Why a table and not free text? Clean filters, no typos ("القاهره" / "القاهرة"), easy reports per governorate.
// Table: governorates  (migration 2026_10_01_000010_create_governorates_table.php)

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Governorate extends Model
{
    /** @use HasFactory<\Database\Factories\GovernorateFactory> */
    use HasFactory;

    protected $fillable = [
        'name',      // Arabic name (unique)
        'name_en',   // English name (optional, used in exports)
    ];

    /* ---------------------------------------------------------------
     | Relations
     * ------------------------------------------------------------- */

    // The clients who live in this governorate (clients.governorate_id)
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    /* ---------------------------------------------------------------
     | Query scopes
     * ------------------------------------------------------------- */

    // alphabetical order for the lists and dropdowns
    public function scopeAlphabetical(Builder $query): Builder
    {
        return $query->orderBy('name');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(fn (Builder $q) => $q->where('name', 'like', "%{$term}%")->orWhere('name_en', 'like', "%{$term}%"));
    }

    /* ---------------------------------------------------------------
     | Helpers
     * ------------------------------------------------------------- */

    // [id => name] for <select> lists:   Governorate::options()
    public static function options(): array
    {
        return static::query()->alphabetical()->pluck('name', 'id')->all();
    }

    /* ---------------------------------------------------------------
     | Model events
     * ------------------------------------------------------------- */

    protected static function booted(): void
    {
        // the database would only set clients.governorate_id to NULL (nullOnDelete); the business rule is stricter:
        // a governorate that still has clients cannot be deleted (the governorates page shows the same message)
        static::deleting(function (Governorate $governorate) {
            if ($governorate->clients()->exists()) {
                throw new \LogicException("لا يمكن حذف «{$governorate->name}» لأن بها عملاء.");
            }
        });
    }
}