<?php

// app/Models/DailyCollectionReport.php
// "DCR": ONE report per employee, per bank, per day: what he did that day (calls, visits, promises, money).
// The numbers are calculated from his work log, then he submits the report and a supervisor approves or rejects it.
// Table: daily_collection_reports  (migration 2026_10_01_000023_create_daily_collection_reports_table.php)

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class DailyCollectionReport extends Model
{
    /** @use HasFactory<\Database\Factories\DailyCollectionReportFactory> */
    use HasFactory;

    // the values allowed in daily_collection_reports.status
    public const STATUS_DRAFT     = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_APPROVED  = 'approved';
    public const STATUS_REJECTED  = 'rejected';

    public const STATUSES = [
        self::STATUS_DRAFT     => 'مسودة',
        self::STATUS_SUBMITTED => 'مُرسل',
        self::STATUS_APPROVED  => 'معتمد',
        self::STATUS_REJECTED  => 'مرفوض',
    ];

    protected $fillable = [
        'bank_id',
        'user_id',            // the employee
        'report_date',        // the day the report is about
        'cases_worked',       // distinct cases touched
        'calls_count',
        'visits_count',
        'promises_count',
        'promised_amount',    // total promised that day
        'collected_amount',   // total collected that day
        'status',
        'submitted_at',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'report_date'      => 'date',
            'cases_worked'     => 'integer',
            'calls_count'      => 'integer',
            'visits_count'     => 'integer',
            'promises_count'   => 'integer',
            'promised_amount'  => 'decimal:2',   // money is never a float
            'collected_amount' => 'decimal:2',
            'submitted_at'     => 'datetime',
            'approved_at'      => 'datetime',
        ];
    }

    /* ---------------------------------------------------------------
     | Relations
     * ------------------------------------------------------------- */

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    // the employee the report is about
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // the supervisor who approved or rejected it
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /* ---------------------------------------------------------------
     | Accessors
     * ------------------------------------------------------------- */

    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn () => self::STATUSES[$this->status] ?? $this->status);
    }

    // an approved report is locked
    protected function isLocked(): Attribute
    {
        return Attribute::get(fn () => $this->status === self::STATUS_APPROVED);
    }

    /* ---------------------------------------------------------------
     | Query scopes:  DailyCollectionReport::forBank(1)->forDate(today())->get()
     * ------------------------------------------------------------- */

    public function scopeForBank(Builder $query, int $bankId): Builder
    {
        return $query->where('bank_id', $bankId);
    }

    public function scopeForDate(Builder $query, Carbon|string $date): Builder
    {
        return $query->whereDate('report_date', $date);
    }

    public function scopeForUser(Builder $query, int|User $user): Builder
    {
        return $query->where('user_id', $user instanceof User ? $user->getKey() : $user);
    }

    public function scopeOfStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    // reports a supervisor still has to answer (the "تقارير DCR بانتظار الاعتماد" task on the dashboard)
    public function scopeAwaitingApproval(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }

    /* ---------------------------------------------------------------
     | Workflow  (the DCR page buttons)
     * ------------------------------------------------------------- */

    // draft / rejected -> submitted ("إرسال للاعتماد" and "إعادة الإرسال")
    public function submit(): void
    {
        if (! in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REJECTED], true)) {
            throw new \LogicException('يمكن إرسال المسودة أو التقرير المرفوض فقط.');
        }

        $this->update(['status' => self::STATUS_SUBMITTED, 'submitted_at' => now()]);
    }

    // submitted -> approved ("اعتماد")
    public function approve(User $by): void
    {
        $this->ensureSubmitted();

        $this->update(['status' => self::STATUS_APPROVED, 'approved_by' => $by->getKey(), 'approved_at' => now()]);
    }

    // submitted -> rejected ("رفض"): it goes back to the employee, with the reason written in the notes
    public function reject(User $by, ?string $reason = null): void
    {
        $this->ensureSubmitted();

        $this->update([
            'status'      => self::STATUS_REJECTED,
            'approved_by' => $by->getKey(),
            'approved_at' => now(),
            'notes'       => $reason ?: $this->notes,
        ]);
    }

    private function ensureSubmitted(): void
    {
        if ($this->status !== self::STATUS_SUBMITTED) {
            throw new \LogicException('يمكن اعتماد أو رفض التقرير المُرسل فقط.');
        }
    }

    /* ---------------------------------------------------------------
     | Calculating the numbers from the work log
     * ------------------------------------------------------------- */

    // Create (or refresh) the report of one employee for one day, with the numbers calculated from his work:
    //   calls, visits, cases        <- case_interactions
    //   promises + their amount     <- promises_to_pay registered that day
    //   money collected             <- payments recorded that day (rejected receipts are not counted)
    // A report that is already submitted or approved is returned untouched.
    public static function generateFor(int $bankId, int|User $user, Carbon $date): self
    {
        $userId = $user instanceof User ? $user->getKey() : $user;

        $report = static::firstOrNew(['bank_id' => $bankId, 'user_id' => $userId, 'report_date' => $date->toDateString()]);

        if ($report->exists && ! in_array($report->status, [self::STATUS_DRAFT, self::STATUS_REJECTED], true)) {
            return $report;
        }

        $work     = CaseInteraction::dailySummary($userId, $date);
        $promises = PromiseToPay::query()->forBank($bankId)->forEmployee($userId)->whereDate('created_at', $date);
        $money    = Payment::query()->byCollector($userId)->forBank($bankId)->whereDate('paid_at', $date)->where('status', '!=', Payment::STATUS_REJECTED);

        $report->fill([
            'cases_worked'     => $work['cases_worked'],
            'calls_count'      => $work['calls'],
            'visits_count'     => $work['visits'],
            'promises_count'   => (clone $promises)->count(),
            'promised_amount'  => (clone $promises)->sum('promised_amount'),
            'collected_amount' => $money->sum('amount'),
            'status'           => $report->status ?? self::STATUS_DRAFT,
        ])->save();

        return $report;
    }

    // For a nightly scheduled job: one report for every active employee of the bank. Returns how many reports were handled.
    public static function generateForBank(int $bankId, Carbon $date): int
    {
        $employees = User::query()->employees()->active()->whereHas('banks', fn (Builder $q) => $q->whereKey($bankId))->get();

        foreach ($employees as $employee) {
            static::generateFor($bankId, $employee, $date);
        }

        return $employees->count();
    }

    // the totals row of the DCR page:  DailyCollectionReport::totalsFor(1, today())
    public static function totalsFor(int $bankId, Carbon|string $date): array
    {
        $day = static::query()->forBank($bankId)->forDate($date);

        return [
            'cases'     => (int) (clone $day)->sum('cases_worked'),
            'calls'     => (int) (clone $day)->sum('calls_count'),
            'visits'    => (int) (clone $day)->sum('visits_count'),
            'promises'  => (int) (clone $day)->sum('promises_count'),
            'promised'  => (float) (clone $day)->sum('promised_amount'),
            'collected' => (float) $day->sum('collected_amount'),
        ];
    }

    /* ---------------------------------------------------------------
     | Model events
     * ------------------------------------------------------------- */

    protected static function booted(): void
    {
        // an approved report is part of the records: nothing may change it any more
        static::updating(function (DailyCollectionReport $report) {
            if ($report->getOriginal('status') === self::STATUS_APPROVED) {
                throw new \LogicException('التقرير المعتمد لا يمكن تعديله.');
            }
        });
    }
}