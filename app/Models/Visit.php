<?php

// app/Models/Visit.php
// "الزيارة الميدانية": a visit scheduled for a collector, and its result.
// Table: visits  (migration 2026_10_01_000021_create_visits_table.php)

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Visit extends Model
{
    /** @use HasFactory<\Database\Factories\VisitFactory> */
    use HasFactory, SoftDeletes;

    // the values allowed in visits.status
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_MISSED    = 'missed';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_SCHEDULED => 'مجدولة',
        self::STATUS_COMPLETED => 'تمت',
        self::STATUS_MISSED    => 'فائتة',
        self::STATUS_CANCELLED => 'ملغاة',
    ];

    // the values allowed in visits.outcome  (the result dialog of the visits page)
    public const OUTCOMES = [
        'client_found' => 'تمت: تم لقاء العميل',
        'promised'     => 'تمت: العميل وعد بالدفع',
        'paid'         => 'تمت: تم السداد',
        'refused'      => 'تمت: العميل رفض السداد',
        'not_home'     => 'فائتة: العميل غير موجود',
    ];

    // the same result written in the collector's work log (case_interactions.outcome)
    private const LOG_OUTCOME = [
        'client_found' => CaseInteraction::OUTCOME_ANSWERED,
        'promised'     => CaseInteraction::OUTCOME_PROMISED,
        'paid'         => CaseInteraction::OUTCOME_PAID,
        'refused'      => CaseInteraction::OUTCOME_REFUSED,
        'not_home'     => CaseInteraction::OUTCOME_NO_ANSWER,
    ];

    protected $fillable = [
        'debt_case_id',
        'user_id',        // the field collector
        'assigned_by',    // who ordered the visit
        'status',
        'scheduled_at',   // planned date and time
        'visited_at',     // real visit time
        'address',        // address visited (a copy, in case the client's address changes later)
        'latitude',       // GPS proof that the collector was there
        'longitude',
        'outcome',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'visited_at'   => 'datetime',
            'latitude'     => 'decimal:7',
            'longitude'    => 'decimal:7',
        ];
    }

    /* ---------------------------------------------------------------
     | Relations
     * ------------------------------------------------------------- */

    public function debtCase(): BelongsTo
    {
        return $this->belongsTo(DebtCase::class);
    }

    // the field collector
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // who ordered the visit
    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /* ---------------------------------------------------------------
     | Accessors
     * ------------------------------------------------------------- */

    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn () => self::STATUSES[$this->status] ?? $this->status);
    }

    protected function outcomeLabel(): Attribute
    {
        return Attribute::get(fn () => $this->outcome ? (self::OUTCOMES[$this->outcome] ?? $this->outcome) : null);
    }

    // "اليوم 11:00" / "غداً 10:00" / "أمس 13:00" / "03/10 12:00": how the visits table prints the time
    protected function dayLabel(): Attribute
    {
        return Attribute::get(function () {
            $day = match (true) {
                $this->scheduled_at->isToday()     => 'اليوم',
                $this->scheduled_at->isTomorrow()  => 'غداً',
                $this->scheduled_at->isYesterday() => 'أمس',
                default                            => $this->scheduled_at->format('d/m'),
            };

            return $day . ' ' . $this->scheduled_at->format('H:i');
        });
    }

    // a link that opens the GPS position of the visit in Google Maps (null without coordinates)
    protected function mapUrl(): Attribute
    {
        return Attribute::get(fn () => $this->latitude && $this->longitude
            ? "https://www.google.com/maps?q={$this->latitude},{$this->longitude}"
            : null);
    }

    /* ---------------------------------------------------------------
     | Query scopes:  Visit::scheduled()->forCollector($user)->today()->get()
     * ------------------------------------------------------------- */

    public function scopeOfStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    public function scopeForCollector(Builder $query, int|User $user): Builder
    {
        return $query->where('user_id', $user instanceof User ? $user->getKey() : $user);
    }

    public function scopeForBank(Builder $query, int $bankId): Builder
    {
        return $query->whereHas('debtCase', fn (Builder $q) => $q->where('bank_id', $bankId));
    }

    // the visits planned for today ("زيارات اليوم")
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('scheduled_at', today());
    }

    /* ---------------------------------------------------------------
     | Workflow
     * ------------------------------------------------------------- */

    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_SCHEDULED;
    }

    // The collector records the result. "not_home" makes the visit "missed", every other result makes it "completed".
    // It also writes the visit into his work log (it feeds the DCR report). All or nothing.
    public function recordResult(string $outcome, ?string $notes = null, ?float $latitude = null, ?float $longitude = null): void
    {
        $this->ensureScheduled();

        if (! array_key_exists($outcome, self::OUTCOMES)) {
            throw new \InvalidArgumentException('نتيجة الزيارة غير معروفة.');
        }

        DB::transaction(function () use ($outcome, $notes, $latitude, $longitude) {
            $this->update([
                'status'     => $outcome === 'not_home' ? self::STATUS_MISSED : self::STATUS_COMPLETED,
                'outcome'    => $outcome,
                'notes'      => $notes ?? $this->notes,
                'visited_at' => now(),
                'latitude'   => $latitude,
                'longitude'  => $longitude,
            ]);

            CaseInteraction::create([
                'debt_case_id' => $this->debt_case_id,
                'user_id'      => $this->user_id,
                'type'         => CaseInteraction::TYPE_VISIT,
                'outcome'      => self::LOG_OUTCOME[$outcome],
                'notes'        => $notes,
                'occurred_at'  => now(),
            ]);
        });
    }

    public function cancel(?string $reason = null): void
    {
        $this->ensureScheduled();

        $this->update(['status' => self::STATUS_CANCELLED, 'notes' => $reason ?? $this->notes]);
    }

    public function reschedule(Carbon $when): void
    {
        $this->ensureScheduled();

        $this->update(['scheduled_at' => $when]);
    }

    private function ensureScheduled(): void
    {
        if (! $this->isScheduled()) {
            throw new \LogicException('لا يمكن تعديل زيارة غير مجدولة.');
        }
    }

    // For a scheduled job (every hour or every night): visits that are still "scheduled" N hours after their time become "missed".
    // Returns how many were changed.
    public static function markMissed(int $afterHours = 12): int
    {
        return static::query()
            ->scheduled()
            ->where('scheduled_at', '<', now()->subHours($afterHours))
            ->update(['status' => self::STATUS_MISSED]);
    }

    /* ---------------------------------------------------------------
     | Model events
     * ------------------------------------------------------------- */

    protected static function booted(): void
    {
        static::creating(function (Visit $visit) {
            $visit->status ??= self::STATUS_SCHEDULED;

            // no address typed: use the client's own address
            $visit->address ??= $visit->debtCase?->client?->address;
        });
    }
}