<?php

// app/Models/Payment.php
// Every collection receipt ("إيصال سداد").
// Workflow:  the collector records it (pending)  ->  a supervisor confirms it (confirmed) or rejects it (rejected).
// ONLY confirmed payments count in debt_cases.collected_amount.
// Table: payments  (migration 2026_10_01_000020_create_payments_table.php)

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use HasFactory, SoftDeletes;   // money history is never really deleted

    // the values allowed in payments.status
    public const STATUS_PENDING   = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_REJECTED  = 'rejected';

    public const STATUSES = [
        self::STATUS_PENDING   => 'قيد المراجعة',
        self::STATUS_CONFIRMED => 'مؤكد',
        self::STATUS_REJECTED  => 'مرفوض',
    ];

    // the values allowed in payments.method
    public const METHODS = [
        'cash'          => 'نقدي',
        'e_wallet'      => 'محفظة إلكترونية',
        'bank_transfer' => 'تحويل بنكي',
        'card'          => 'بطاقة',
        'cheque'        => 'شيك',
    ];

    protected $fillable = [
        'receipt_number',     // "#4092": printed on the receipt, never repeated. Generated automatically when empty.
        'debt_case_id',
        'collector_id',       // "المحصل"
        'promise_id',         // the promise this payment fulfils (optional)
        'amount',
        'method',
        'reference',          // transfer / wallet transaction number
        'proof_path',         // photo or PDF of the receipt
        'paid_at',            // when the client actually paid
        'status',
        'confirmed_by',       // the supervisor who REVIEWED it (confirmed or rejected it)
        'confirmed_at',
        'rejection_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount'       => 'decimal:2',   // money is never a float
            'paid_at'      => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    /* ---------------------------------------------------------------
     | Relations
     * ------------------------------------------------------------- */

    public function debtCase(): BelongsTo
    {
        return $this->belongsTo(DebtCase::class);
    }

    // "المحصل": the employee who collected the money
    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collector_id');
    }

    // the supervisor who confirmed or rejected it
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function promise(): BelongsTo
    {
        return $this->belongsTo(PromiseToPay::class, 'promise_id');
    }

    /* ---------------------------------------------------------------
     | Accessors:  $payment->status_label / ->method_label / ->proof_url
     * ------------------------------------------------------------- */

    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn () => self::STATUSES[$this->status] ?? $this->status);
    }

    protected function methodLabel(): Attribute
    {
        return Attribute::get(fn () => self::METHODS[$this->method] ?? $this->method);
    }

    // public url of the photo / PDF of the receipt (null when there is none)
    protected function proofUrl(): Attribute
    {
        return Attribute::get(fn () => $this->proof_path ? Storage::disk('public')->url($this->proof_path) : null);
    }

    /* ---------------------------------------------------------------
     | Query scopes:  Payment::pending()->forBank(1)->latest('paid_at')->get()
     * ------------------------------------------------------------- */

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeForBank(Builder $query, int $bankId): Builder
    {
        return $query->whereHas('debtCase', fn (Builder $q) => $q->where('bank_id', $bankId));
    }

    public function scopeByCollector(Builder $query, int|User $user): Builder
    {
        return $query->where('collector_id', $user instanceof User ? $user->getKey() : $user);
    }

    public function scopePaidBetween(Builder $query, Carbon|string $from, Carbon|string $to): Builder
    {
        return $query->whereBetween('paid_at', [$from, $to]);
    }

    /* ---------------------------------------------------------------
     | Workflow  (the "تأكيد التحصيلات" page)
     * ------------------------------------------------------------- */

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    // The supervisor approves the receipt. All or nothing:
    //   1) mark it confirmed,  2) update "المحصل" of the case,  3) move the linked promise (kept / partial).
    public function confirm(User $by): void
    {
        $this->ensurePending();

        DB::transaction(function () use ($by) {
            $this->update(['status' => self::STATUS_CONFIRMED, 'confirmed_by' => $by->getKey(), 'confirmed_at' => now(), 'rejection_reason' => null]);

            $this->debtCase->refreshCollected();

            // a promise that is still waiting (or partly paid) receives this money
            if ($this->promise && in_array($this->promise->status, [PromiseToPay::STATUS_ACTIVE, PromiseToPay::STATUS_REVIEW, PromiseToPay::STATUS_PARTIAL], true)) {
                $this->promise->applyPayment((float) $this->amount);
            }
        });
    }

    // The supervisor refuses the receipt: it never counts in "المحصل". A reason is required.
    public function reject(User $by, string $reason): void
    {
        $this->ensurePending();

        $this->update([
            'status'           => self::STATUS_REJECTED,
            'confirmed_by'     => $by->getKey(),
            'confirmed_at'     => now(),
            'rejection_reason' => $reason,
        ]);
    }

    private function ensurePending(): void
    {
        if (! $this->isPending()) {
            throw new \LogicException('تمت مراجعة هذا الإيصال من قبل.');
        }
    }

    /* ---------------------------------------------------------------
     | Numbers of the pages
     * ------------------------------------------------------------- */

    // the "بانتظار التأكيد" cards: how many receipts and how much money wait for a supervisor
    //     Payment::pendingSummary()   ->  ['count' => 3, 'amount' => 4150.0]
    public static function pendingSummary(?int $bankId = null): array
    {
        $query = static::query()->pending()->when($bankId, fn (Builder $q) => $q->forBank($bankId));

        return ['count' => (clone $query)->count(), 'amount' => (float) $query->sum('amount')];
    }

    // the next receipt number: "#4093" (the highest number + 1, starting from #4000)
    // (CAST ... AS UNSIGNED is MySQL / MariaDB syntax. The unique index protects against duplicates.)
    public static function nextReceiptNumber(): string
    {
        $last = static::withTrashed()->select(DB::raw("MAX(CAST(REPLACE(receipt_number, '#', '') AS UNSIGNED)) as last_number"))->value('last_number');

        return '#' . (max((int) $last, 3999) + 1);
    }

    /* ---------------------------------------------------------------
     | Model events
     * ------------------------------------------------------------- */

    protected static function booted(): void
    {
        static::creating(function (Payment $payment) {
            $payment->receipt_number ??= static::nextReceiptNumber();
            $payment->paid_at        ??= now();
            $payment->status         ??= self::STATUS_PENDING;
        });

        // a confirmed receipt is part of the accounts: its amount and its case can no longer be changed
        static::updating(function (Payment $payment) {
            if ($payment->getOriginal('status') === self::STATUS_CONFIRMED && $payment->isDirty(['amount', 'debt_case_id'])) {
                throw new \LogicException('لا يمكن تعديل مبلغ إيصال مؤكد.');
            }
        });
    }
}