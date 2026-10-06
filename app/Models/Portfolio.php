<?php

// app/Models/Portfolio.php
// "النطاق" (scope): the batch of debts ONE bank gives us for ONE month.
// Flow:  import the Excel file (draft) -> activate (active) -> end of the month -> archive (archived, read-only).
// Named "portfolio" (not "scope") to avoid confusion with Eloquent query scopes.
// Table: portfolios  (migration 2026_10_01_000014_create_portfolios_table.php)

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

class Portfolio extends Model
{
    /** @use HasFactory<\Database\Factories\PortfolioFactory> */
    use HasFactory, SoftDeletes;

    // the values allowed in portfolios.status
    public const STATUS_DRAFT    = 'draft';      // imported, not open for work yet
    public const STATUS_ACTIVE   = 'active';     // the month being worked now (only ONE per bank)
    public const STATUS_ARCHIVED = 'archived';   // closed: read-only

    public const MONTHS = [
        1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل', 5 => 'مايو', 6 => 'يونيو',
        7 => 'يوليو', 8 => 'أغسطس', 9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
    ];

    protected $fillable = [
        'bank_id',
        'name',           // "Emirates NBD - September 2026"
        'period_year',    // 2026
        'period_month',   // 1..12
        'status',
        'cases_count',    // counters refreshed after every import: fast dashboard numbers (see refreshTotals())
        'total_debt',
        'created_by',
        'activated_at',
        'archived_at',
        'archived_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'period_year'  => 'integer',
            'period_month' => 'integer',
            'cases_count'  => 'integer',
            'total_debt'   => 'decimal:2',   // money is never a float
            'activated_at' => 'datetime',
            'archived_at'  => 'datetime',
        ];
    }

    /* ---------------------------------------------------------------
     | Relations
     * ------------------------------------------------------------- */

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    // who created it (usually who uploaded the file)
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // who closed it
    public function archiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    // the cases (debts) of this month
    public function debtCases(): HasMany
    {
        return $this->hasMany(DebtCase::class);
    }

    // the Excel uploads that fed this portfolio
    public function imports(): HasMany
    {
        return $this->hasMany(PortfolioImport::class);
    }

    // the frozen summary saved when it was archived ("المستودعات الشهرية")
    public function monthlyArchive(): HasOne
    {
        return $this->hasOne(MonthlyArchive::class);
    }

    /* ---------------------------------------------------------------
     | Accessor:  $portfolio->period_label   ->  "سبتمبر 2026"
     * ------------------------------------------------------------- */

    protected function periodLabel(): Attribute
    {
        return Attribute::get(fn () => (self::MONTHS[$this->period_month] ?? $this->period_month) . ' ' . $this->period_year);
    }

    /* ---------------------------------------------------------------
     | Query scopes:  Portfolio::active()->forBank(1)->first()
     * ------------------------------------------------------------- */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ARCHIVED);
    }

    public function scopeForBank(Builder $query, int $bankId): Builder
    {
        return $query->where('bank_id', $bankId);
    }

    public function scopeForPeriod(Builder $query, int $year, int $month): Builder
    {
        return $query->where('period_year', $year)->where('period_month', $month);
    }

    // newest month first
    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('period_year')->orderByDesc('period_month');
    }

    /* ---------------------------------------------------------------
     | Status helpers
     * ------------------------------------------------------------- */

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }

    // an archived portfolio is read-only: no edit, no distribution, no import
    public function isEditable(): bool
    {
        return ! $this->isArchived();
    }

    /* ---------------------------------------------------------------
     | Workflow
     * ------------------------------------------------------------- */

    // draft -> active.   Rule: a bank has only ONE active portfolio at a time (MySQL cannot enforce that with an index).
    public function activate(): void
    {
        DB::transaction(function () {
            $anotherIsActive = static::query()->active()->forBank($this->bank_id)->whereKeyNot($this->getKey())->exists();

            if ($anotherIsActive) {
                throw new \LogicException('هذا البنك لديه نطاق نشط بالفعل. قم بأرشفته أولاً.');
            }

            $this->update(['status' => self::STATUS_ACTIVE, 'activated_at' => now()]);
        });
    }

    // active -> archived, and save the frozen monthly summary in monthly_archives (all or nothing)
    public function archive(User $by): void
    {
        if (! $this->isActive()) {
            throw new \LogicException('يمكن أرشفة النطاق النشط فقط.');
        }

        DB::transaction(function () use ($by) {
            $this->refreshTotals();

            $this->update([
                'status'      => self::STATUS_ARCHIVED,
                'archived_at' => now(),
                'archived_by' => $by->getKey(),
            ]);

            MonthlyArchive::updateOrCreate(
                ['bank_id' => $this->bank_id, 'period_year' => $this->period_year, 'period_month' => $this->period_month],
                [
                    'portfolio_id'     => $this->getKey(),
                    'cases_count'      => $this->cases_count,
                    'total_debt'       => $this->total_debt,
                    'collected_amount' => $this->debtCases()->sum('collected_amount'),
                    'archived_by'      => $by->getKey(),
                    'archived_at'      => now(),
                ]
            );
        });
    }

    // recalculate the two counters from the cases (call it after an import or a bulk change)
    public function refreshTotals(): void
    {
        $this->update([
            'cases_count' => $this->debtCases()->count(),
            'total_debt'  => $this->debtCases()->sum('total_debt'),
        ]);
    }

    /* ---------------------------------------------------------------
     | Model events
     * ------------------------------------------------------------- */

    protected static function booted(): void
    {
        // an archived portfolio can never be changed again (archive() starts from "active", so it is not blocked)
        static::updating(function (Portfolio $portfolio) {
            if ($portfolio->getOriginal('status') === self::STATUS_ARCHIVED) {
                throw new \LogicException('النطاق المؤرشف للقراءة فقط ولا يمكن تعديله.');
            }
        });
    }
}