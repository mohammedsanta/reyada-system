<?php

// app/Models/PromiseToPay.php
// "PTP": the client promises to pay X on date Y. The PTP hub (kanban board + calendar) is built from these rows.
// Table: promises_to_pay  (migration 2026_10_01_000019_create_promises_to_pay_table.php)

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class PromiseToPay extends Model
{
    /** @use HasFactory<\Database\Factories\PromiseToPayFactory> */
    use HasFactory, SoftDeletes;

    // Laravel would guess "promise_to_pays": the real table name is written here
    protected $table = 'promises_to_pay';

    // the values allowed in promises_to_pay.status  (= the five columns of the kanban board)
    public const STATUS_ACTIVE  = 'active';    // وعود نشطة
    public const STATUS_REVIEW  = 'review';    // قيد المراجعة
    public const STATUS_KEPT    = 'kept';      // محقق كلياً
    public const STATUS_PARTIAL = 'partial';   // محقق جزئياً
    public const STATUS_BROKEN  = 'broken';    // مكسور

    public const STATUSES = [
        self::STATUS_ACTIVE  => 'وعود نشطة',
        self::STATUS_REVIEW  => 'قيد المراجعة',
        self::STATUS_KEPT    => 'محقق كلياً',
        self::STATUS_PARTIAL => 'محقق جزئياً',
        self::STATUS_BROKEN  => 'مكسور',
    ];

    protected $fillable = [
        'debt_case_id',
        'user_id',          // the employee who got the promise
        'promised_amount',  // what the client promised
        'paid_amount',      // how much he really paid so far (drives the partial progress bar)
        'promise_date',     // the day he promised to pay
        'status',
        'notes',
        'reviewed_by',
        'reviewed_at',
        'closed_at',        // when it became kept / partial / broken
    ];

    protected function casts(): array
    {
        return [
            'promised_amount' => 'decimal:2',   // money is never a float
            'paid_amount'     => 'decimal:2',
            'promise_date'    => 'date',
            'reviewed_at'     => 'datetime',
            'closed_at'       => 'datetime',
        ];
    }

    /* ---------------------------------------------------------------
     | Relations
     * ------------------------------------------------------------- */

    public function debtCase(): BelongsTo
    {
        return $this->belongsTo(DebtCase::class);
    }

    // the employee who took the promise
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // who checked it
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // the payments that fulfil this promise (payments.promise_id)
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'promise_id');
    }

    /* ---------------------------------------------------------------
     | Accessors
     * ------------------------------------------------------------- */

    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn () => self::STATUSES[$this->status] ?? $this->status);
    }

    // what is still unpaid of the promise (never negative)
    protected function remainingAmount(): Attribute
    {
        return Attribute::get(fn () => max(0, (float) $this->promised_amount - (float) $this->paid_amount));
    }

    // paid / promised as a percentage (0 - 100): the progress bar of the partial cards
    protected function paidPercent(): Attribute
    {
        return Attribute::get(fn () => (float) $this->promised_amount > 0
            ? min(100, (int) round((float) $this->paid_amount / (float) $this->promised_amount * 100))
            : 0);
    }

    // days until the promise date: positive = in the future, 0 = today, negative = late
    protected function daysLeft(): Attribute
    {
        return Attribute::get(fn () => (int) now()->startOfDay()->diffInDays($this->promise_date->startOfDay(), false));
    }

    // still waiting for the client (not closed yet)
    protected function isOpen(): Attribute
    {
        return Attribute::get(fn () => in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_REVIEW], true));
    }

    protected function isOverdue(): Attribute
    {
        return Attribute::get(fn () => $this->is_open && $this->days_left < 0);
    }

    // the text under every card: ["بعد 3 يوم", "muted"] / ["متأخر 2 يوم", "danger"] ...  (same rule as the PTP hub)
    protected function dueLabel(): Attribute
    {
        return Attribute::get(function () {
            return match (true) {
                $this->status === self::STATUS_KEPT    => 'تم السداد',
                $this->status === self::STATUS_PARTIAL => 'سُدد جزئياً',
                $this->days_left < 0                   => 'متأخر ' . abs($this->days_left) . ' يوم',
                $this->days_left === 0                 => 'اليوم',
                default                                => 'بعد ' . $this->days_left . ' يوم',
            };
        });
    }

    // the color of that text:  muted | warning | danger | brand | info
    protected function dueTone(): Attribute
    {
        return Attribute::get(function () {
            return match (true) {
                $this->status === self::STATUS_KEPT    => 'brand',
                $this->status === self::STATUS_PARTIAL => 'info',
                $this->days_left < 0                   => 'danger',
                $this->days_left === 0                 => 'warning',
                default                                => 'muted',
            };
        });
    }

    /* ---------------------------------------------------------------
     | Query scopes:  PromiseToPay::forBank(1)->overdue()->get()
     * ------------------------------------------------------------- */

    public function scopeOfStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    // active or under review: the client has not paid yet
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_REVIEW]);
    }

    public function scopeDueToday(Builder $query): Builder
    {
        return $query->open()->whereDate('promise_date', today());
    }

    // open promises whose date has passed (the "متأخرة" filter)
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->open()->whereDate('promise_date', '<', today());
    }

    // open promises in the next N days (the "خلال 7 أيام" filter)
    public function scopeDueWithin(Builder $query, int $days): Builder
    {
        return $query->open()->whereBetween('promise_date', [today(), today()->addDays($days)]);
    }

    public function scopeForBank(Builder $query, int $bankId): Builder
    {
        return $query->whereHas('debtCase', fn (Builder $q) => $q->where('bank_id', $bankId));
    }

    public function scopeForEmployee(Builder $query, int|User $user): Builder
    {
        return $query->where('user_id', $user instanceof User ? $user->getKey() : $user);
    }

    /* ---------------------------------------------------------------
     | Workflow: how a promise moves between the kanban columns.
     | The rules are the same ones the promise details page validates.
     * ------------------------------------------------------------- */

    public function sendToReview(User $by): void
    {
        $this->update(['status' => self::STATUS_REVIEW, 'reviewed_by' => $by->getKey(), 'reviewed_at' => now()]);
    }

    // the client paid everything he promised
    public function markKept(): void
    {
        $this->update(['status' => self::STATUS_KEPT, 'paid_amount' => $this->promised_amount, 'closed_at' => now()]);
    }

    // he paid a part: more than zero and less than the promise
    public function markPartial(float $paid): void
    {
        if ($paid <= 0 || $paid >= (float) $this->promised_amount) {
            throw new \InvalidArgumentException('المبلغ المسدد يجب أن يكون أكبر من صفر وأقل من مبلغ الوعد.');
        }

        $this->update(['status' => self::STATUS_PARTIAL, 'paid_amount' => $paid, 'closed_at' => now()]);
    }

    // he did not pay
    public function markBroken(): void
    {
        $this->update(['status' => self::STATUS_BROKEN, 'closed_at' => now()]);
    }

    // a confirmed payment arrived: add it and move the promise to kept / partial by itself
    public function applyPayment(float $amount): void
    {
        $paid = (float) $this->paid_amount + $amount;

        $paid >= (float) $this->promised_amount ? $this->markKept() : $this->markPartial($paid);
    }

    /* ---------------------------------------------------------------
     | Numbers of the PTP hub and the dashboard
     * ------------------------------------------------------------- */

    // [status => how many]   PromiseToPay::countsByStatus(1)  ->  ['active' => 3, 'kept' => 1, ...]
    public static function countsByStatus(?int $bankId = null): array
    {
        return static::query()
            ->when($bankId, fn (Builder $q) => $q->forBank($bankId))
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();
    }

    // "نسبة التحقيق": fully kept / every closed promise (kept + partial + broken), as a whole percentage
    public static function fulfillmentRate(?int $bankId = null): int
    {
        $counts   = static::countsByStatus($bankId);
        $kept     = $counts[self::STATUS_KEPT] ?? 0;
        $resolved = $kept + ($counts[self::STATUS_PARTIAL] ?? 0) + ($counts[self::STATUS_BROKEN] ?? 0);

        return $resolved > 0 ? (int) round($kept / $resolved * 100) : 0;
    }

    // For a nightly scheduled job: promises that are still "active" N days after their date become "broken".
    // Returns how many were changed.   (N = the "تنبيه الوعد المتأخر بعد (أيام)" setting)
    public static function breakOverdue(int $graceDays = 1): int
    {
        return static::query()
            ->where('status', self::STATUS_ACTIVE)
            ->whereDate('promise_date', '<', today()->subDays($graceDays))
            ->update(['status' => self::STATUS_BROKEN, 'closed_at' => now()]);
    }
}