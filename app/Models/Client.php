<?php

// app/Models/Client.php
// "العميل": the PERSON (the debtor). One person = one row, even if he owes money to several banks.
// The debt itself is in DebtCase. His phone numbers are in ClientPhone.
// Table: clients  (migration 2026_10_01_000012_create_clients_table.php)

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Client extends Model
{
    /** @use HasFactory<\Database\Factories\ClientFactory> */
    use HasFactory, SoftDeletes;   // a deleted client stays in the database, so old cases and payments keep their debtor

    protected $fillable = [
        'code',            // "كود العميل" (1000001). Generated automatically when empty (see booted())
        'national_id',     // Egyptian national ID = exactly 14 digits (unique: it also prevents duplicate clients on import)
        'name',
        'email',
        'governorate_id',  // "المحافظة"
        'address',         // "العنوان بالتفصيل"
        'employer_name',   // work data: "جهة العمل"
        'job_title',       // "المسمى الوظيفي"
        'work_address',    // "عنوان العمل"  (the work PHONE is in client_phones with label = work)
        'notes',
    ];

    /* ---------------------------------------------------------------
     | Relations
     * ------------------------------------------------------------- */

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    // every phone number of the client (primary, alternate, work ...)
    public function phones(): HasMany
    {
        return $this->hasMany(ClientPhone::class);
    }

    // his debts: one row per loan per monthly scope
    public function debtCases(): HasMany
    {
        return $this->hasMany(DebtCase::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    /* ---------------------------------------------------------------
     | Accessors:  $client->phone  /  $client->phone2  /  $client->full_address
     |
     | The pages show "PHONE = 01299928200 / 01054355112". Those two come from client_phones.
     | They read the loaded `phones` relation: load it first to avoid one extra query per client:
     |       Client::with('phones')->get()
     * ------------------------------------------------------------- */

    // the primary number
    protected function phone(): Attribute
    {
        return Attribute::get(fn () => $this->phones->firstWhere('label', ClientPhone::PRIMARY)?->phone);
    }

    // the alternate number
    protected function phone2(): Attribute
    {
        return Attribute::get(fn () => $this->phones->firstWhere('label', ClientPhone::ALTERNATE)?->phone);
    }

    // the work number
    protected function workPhone(): Attribute
    {
        return Attribute::get(fn () => $this->phones->firstWhere('label', ClientPhone::WORK)?->phone);
    }

    // "82 شارع الهرم - الدقي - سوهاج / سوهاج": how the clients table shows the address
    protected function fullAddress(): Attribute
    {
        return Attribute::get(fn () => collect([$this->address, $this->governorate?->name])->filter()->implode(' / '));
    }

    /* ---------------------------------------------------------------
     | Query scopes:  Client::search('1000002')->inGovernorate(5)->get()
     * ------------------------------------------------------------- */

    // the search box: code, name, national ID or ANY of his phone numbers
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('code', 'like', "%{$term}%")
              ->orWhere('name', 'like', "%{$term}%")
              ->orWhere('national_id', 'like', "%{$term}%")
              ->orWhereHas('phones', fn (Builder $phones) => $phones->where('phone', 'like', "%{$term}%"));
        });
    }

    public function scopeInGovernorate(Builder $query, int|string|null $governorateId): Builder
    {
        return blank($governorateId) ? $query : $query->where('governorate_id', $governorateId);
    }

    /* ---------------------------------------------------------------
     | Helpers
     * ------------------------------------------------------------- */

    // find a client by national ID: the key used by the Excel import to update an existing client instead of duplicating him
    public static function findByNationalId(string $nationalId): ?self
    {
        return static::where('national_id', $nationalId)->first();
    }

    // save his phone numbers in one call (the edit dialog sends the primary and the alternate number).
    // A blank number removes that label.     $client->syncPhones('01299928200', '01054355112')
    public function syncPhones(?string $primary, ?string $alternate = null, ?string $work = null): void
    {
        $numbers = [ClientPhone::PRIMARY => $primary, ClientPhone::ALTERNATE => $alternate, ClientPhone::WORK => $work];

        foreach ($numbers as $label => $number) {
            if (blank($number)) {
                $this->phones()->where('label', $label)->delete();
                continue;
            }

            $this->phones()->updateOrCreate(['label' => $label], ['phone' => $number, 'is_valid' => true]);
        }

        $this->unsetRelation('phones');   // the next read sees the new numbers
    }

    // the next free code: the highest existing number + 1, starting from 1000000
    // (CAST ... AS UNSIGNED is MySQL / MariaDB syntax. The unique index on code protects against two clients getting the same number.)
    public static function nextCode(): string
    {
        $last = static::withTrashed()->select(DB::raw('MAX(CAST(code AS UNSIGNED)) as last_code'))->value('last_code');

        return (string) (max((int) $last, 999999) + 1);
    }

    /* ---------------------------------------------------------------
     | Model events
     * ------------------------------------------------------------- */

    protected static function booted(): void
    {
        // a new client without a code gets the next one automatically
        static::creating(function (Client $client) {
            $client->code ??= static::nextCode();
        });
    }
}