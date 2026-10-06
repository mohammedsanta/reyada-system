<?php

// app/Models/CaseInteraction.php
// Every call / WhatsApp / SMS / visit / note a collector makes on a case: his daily work log.
// It feeds the DCR report and the employee performance page.
// Table: case_interactions  (migration 2026_10_01_000018_create_case_interactions_table.php)

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class CaseInteraction extends Model
{
    /** @use HasFactory<\Database\Factories\CaseInteractionFactory> */
    use HasFactory;

    // the values allowed in case_interactions.type
    public const TYPE_CALL     = 'call';
    public const TYPE_WHATSAPP = 'whatsapp';
    public const TYPE_SMS      = 'sms';
    public const TYPE_EMAIL    = 'email';
    public const TYPE_VISIT    = 'visit';
    public const TYPE_NOTE     = 'note';

    public const TYPES = [
        self::TYPE_CALL     => 'مكالمة',
        self::TYPE_WHATSAPP => 'واتساب',
        self::TYPE_SMS      => 'رسالة نصية',
        self::TYPE_EMAIL    => 'بريد إلكتروني',
        self::TYPE_VISIT    => 'زيارة',
        self::TYPE_NOTE     => 'ملاحظة',
    ];

    // the values allowed in case_interactions.outcome
    public const OUTCOME_ANSWERED     = 'answered';
    public const OUTCOME_NO_ANSWER    = 'no_answer';
    public const OUTCOME_WRONG_NUMBER = 'wrong_number';
    public const OUTCOME_REFUSED      = 'refused';
    public const OUTCOME_PROMISED     = 'promised';
    public const OUTCOME_PAID         = 'paid';

    public const OUTCOMES = [
        self::OUTCOME_ANSWERED     => 'رد على المكالمة',
        self::OUTCOME_NO_ANSWER    => 'لم يرد',
        self::OUTCOME_WRONG_NUMBER => 'رقم خاطئ',
        self::OUTCOME_REFUSED      => 'رفض السداد',
        self::OUTCOME_PROMISED     => 'وعد بالسداد',
        self::OUTCOME_PAID         => 'تم السداد',
    ];

    protected $fillable = [
        'debt_case_id',
        'user_id',            // the collector who did it
        'client_phone_id',    // which number was called (optional)
        'type',
        'outcome',
        'notes',              // what was said
        'duration_seconds',   // call length
        'occurred_at',        // when it happened (can differ from created_at)
        'followup_at',        // reminder: "call him again at ..."
    ];

    protected function casts(): array
    {
        return [
            'duration_seconds' => 'integer',
            'occurred_at'      => 'datetime',
            'followup_at'      => 'datetime',
        ];
    }

    /* ---------------------------------------------------------------
     | Relations
     * ------------------------------------------------------------- */

    public function debtCase(): BelongsTo
    {
        return $this->belongsTo(DebtCase::class);
    }

    // the collector
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // the number that was called
    public function clientPhone(): BelongsTo
    {
        return $this->belongsTo(ClientPhone::class);
    }

    /* ---------------------------------------------------------------
     | Accessors:  $log->type_label / ->outcome_label / ->duration_label
     * ------------------------------------------------------------- */

    protected function typeLabel(): Attribute
    {
        return Attribute::get(fn () => self::TYPES[$this->type] ?? $this->type);
    }

    protected function outcomeLabel(): Attribute
    {
        return Attribute::get(fn () => $this->outcome ? (self::OUTCOMES[$this->outcome] ?? $this->outcome) : null);
    }

    // 155 seconds -> "2:35"
    protected function durationLabel(): Attribute
    {
        return Attribute::get(fn () => $this->duration_seconds === null
            ? null
            : intdiv($this->duration_seconds, 60) . ':' . str_pad((string) ($this->duration_seconds % 60), 2, '0', STR_PAD_LEFT));
    }

    /* ---------------------------------------------------------------
     | Query scopes:  CaseInteraction::forUser($user)->onDate(today())->ofType('call')->count()
     * ------------------------------------------------------------- */

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeOfOutcome(Builder $query, string $outcome): Builder
    {
        return $query->where('outcome', $outcome);
    }

    public function scopeForUser(Builder $query, int|User $user): Builder
    {
        return $query->where('user_id', $user instanceof User ? $user->getKey() : $user);
    }

    // everything that happened on one day
    public function scopeOnDate(Builder $query, Carbon|string $date): Builder
    {
        return $query->whereDate('occurred_at', $date);
    }

    // follow-ups that are due now or already late (the "متابعات هاتفية مجدولة" task on the dashboard)
    public function scopeFollowUpsDue(Builder $query): Builder
    {
        return $query->whereNotNull('followup_at')->where('followup_at', '<=', now());
    }

    /* ---------------------------------------------------------------
     | Helpers
     * ------------------------------------------------------------- */

    // The three numbers of one employee's DCR for one day: calls, visits, distinct cases he touched.
    //     CaseInteraction::dailySummary($user, today())  ->  ['calls' => 35, 'visits' => 2, 'cases_worked' => 12]
    public static function dailySummary(int|User $user, Carbon|string $date): array
    {
        $day = static::query()->forUser($user)->onDate($date);

        return [
            'calls'        => (clone $day)->ofType(self::TYPE_CALL)->count(),
            'visits'       => (clone $day)->ofType(self::TYPE_VISIT)->count(),
            'cases_worked' => (clone $day)->distinct()->count('debt_case_id'),
        ];
    }

    /* ---------------------------------------------------------------
     | Model events
     * ------------------------------------------------------------- */

    protected static function booted(): void
    {
        // when nobody says otherwise, it happened just now
        static::creating(function (CaseInteraction $log) {
            $log->occurred_at ??= now();
        });

        // a collector reports a "wrong number": that number is marked invalid automatically
        static::created(function (CaseInteraction $log) {
            if ($log->outcome === self::OUTCOME_WRONG_NUMBER) {
                $log->clientPhone?->markInvalid();
            }
        });
    }
}