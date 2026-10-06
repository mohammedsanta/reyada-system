<?php

// app/Models/ClientPhone.php
// One phone number of a client. A client can have several (primary, alternate, work ...), and a collector can mark a
// number as a "wrong number" without losing it.
// Table: client_phones  (migration 2026_10_01_000013_create_client_phones_table.php)

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientPhone extends Model
{
    /** @use HasFactory<\Database\Factories\ClientPhoneFactory> */
    use HasFactory;

    // the values allowed in client_phones.label  (Client::phone / phone2 / workPhone read these)
    public const PRIMARY   = 'primary';
    public const ALTERNATE = 'alternate';
    public const WORK      = 'work';
    public const OTHER     = 'other';

    // label => Arabic text
    public const LABELS = [
        self::PRIMARY   => 'أساسي',
        self::ALTERNATE => 'بديل',
        self::WORK      => 'عمل',
        self::OTHER     => 'آخر',
    ];

    protected $fillable = [
        'client_id',
        'phone',      // 01299928200 (digits only, see the phone() mutator)
        'label',      // primary | alternate | work | other
        'is_valid',   // false after a collector marks it "wrong number"
    ];

    protected function casts(): array
    {
        return [
            'is_valid' => 'boolean',
        ];
    }

    /* ---------------------------------------------------------------
     | Relations
     * ------------------------------------------------------------- */

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    // the calls / messages made to this number  (case_interactions.client_phone_id)
    public function interactions(): HasMany
    {
        return $this->hasMany(CaseInteraction::class);
    }

    /* ---------------------------------------------------------------
     | Mutator + accessors
     * ------------------------------------------------------------- */

    // Whatever is typed is cleaned before it is saved: "٠١٢٩ 992-8200" becomes "01299928200"
    // (so the unique key [client_id, phone] and the search always compare clean digits).
    protected function phone(): Attribute
    {
        return Attribute::set(fn ($value) => self::normalize((string) $value));
    }

    // "primary" -> "أساسي"
    protected function labelText(): Attribute
    {
        return Attribute::get(fn () => self::LABELS[$this->label] ?? $this->label);
    }

    // "01299928200" -> "+201299928200": the format that WhatsApp and international dialing need
    protected function international(): Attribute
    {
        return Attribute::get(fn () => preg_match('/^0\d{10}$/', $this->phone) ? '+20' . substr($this->phone, 1) : $this->phone);
    }

    /* ---------------------------------------------------------------
     | Query scopes:  $client->phones()->valid()->ofLabel('primary')->first()
     * ------------------------------------------------------------- */

    // numbers that are still worth calling
    public function scopeValid(Builder $query): Builder
    {
        return $query->where('is_valid', true);
    }

    public function scopeOfLabel(Builder $query, string $label): Builder
    {
        return $query->where('label', $label);
    }

    /* ---------------------------------------------------------------
     | Helpers
     * ------------------------------------------------------------- */

    // Arabic-Indic digits -> normal digits, then everything that is not a digit is removed
    public static function normalize(string $number): string
    {
        $number = strtr($number, ['٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9']);

        return preg_replace('/\D+/', '', $number);
    }

    // a collector reports a "wrong number": it stays in the database but is no longer valid
    public function markInvalid(): bool
    {
        return $this->update(['is_valid' => false]);
    }
}