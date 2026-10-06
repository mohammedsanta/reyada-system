<?php

// app/Models/DebtCase.php
// "الحالة": ONE debt of ONE client inside ONE monthly portfolio.  THE core model of Collex.
// Every row of the bank clients table and the whole edit dialog (debt + dates sections) come from here, joined with the Client.
// Named DebtCase because "case" is a reserved word in PHP and SQL.
// Table: debt_cases  (migration 2026_10_01_000016_create_debt_cases_table.php)

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

class DebtCase extends Model
{
    /** @use HasFactory<\Database\Factories\DebtCaseFactory> */
    use HasFactory, SoftDeletes;

    // the values allowed in debt_cases.status.  The pages show them in capitals (ACTIVE): use $case->status_code for that.
    public const STATUS_ACTIVE   = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_PAID     = 'paid';
    public const STATUS_LEGAL    = 'legal';

    public const STATUSES = [
        self::STATUS_ACTIVE   => 'نشط',
        self::STATUS_INACTIVE => 'متوقف',
        self::STATUS_PAID     => 'مسدد',
        self::STATUS_LEGAL    => 'قضائي',
    ];

    protected $fillable = [
        // relations
        'portfolio_id', 'bank_id', 'client_id', 'loan_type_id', 'assigned_user_id',
        'loan_number',            // the bank's own loan / contract number
        'status',
        // money
        'total_debt',             // "إجمالي المديونية"
        'overdue_amount',         // "المبلغ المتأخر"
        'installment_value',      // "قيمة القسط"
        'min_installment_diff',   // "أقل قسط (DIFF)"
        'late_fee',               // "غرامة التأخر"
        'collected_amount',       // "المحصل": the sum of the CONFIRMED payments (see refreshCollected())
        // delinquency
        'bucket',                 // "الشريحة (BUCKET)": 0, 1, 2, 3 ...
        'dpd',                    // days past due
        // dates
        'next_due_date', 'loan_start_date', 'loan_end_date', 'last_payment_date', 'last_payment_amount',
        // processing ("حالات تمت معالجتها")
        'is_processed', 'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'total_debt'           => 'decimal:2',   // money is never a float
            'overdue_amount'       => 'decimal:2',
            'installment_value'    => 'decimal:2',
            'min_installment_diff' => 'decimal:2',
            'late_fee'             => 'decimal:2',
            'collected_amount'     => 'decimal:2',
            'last_payment_amount'  => 'decimal:2',
            'bucket'               => 'integer',
            'dpd'                  => 'integer',
            'next_due_date'        => 'date',
            'loan_start_date'      => 'date',
            'loan_end_date'        => 'date',
            'last_payment_date'    => 'date',
            'is_processed'         => 'boolean',
            'processed_at'         => 'datetime',
        ];
    }

    /* ---------------------------------------------------------------
     | Relations
     * ------------------------------------------------------------- */

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    // the debtor
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function loanType(): BelongsTo
    {
        return $this->belongsTo(LoanType::class);
    }

    // "الموظف": the employee who works this case now
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    // the whole history of who had this case (توزيع الحالات)
    public function assignments(): HasMany
    {
        return $this->hasMany(CaseAssignment::class);
    }

    // the assignment that is open right now (unassigned_at is still empty)
    public function currentAssignment(): HasOne
    {
        return $this->hasOne(CaseAssignment::class)->whereNull('unassigned_at')->latestOfMany('assigned_at');
    }

    // calls / messages / notes of the collectors on this case
    public function interactions(): HasMany
    {
        return $this->hasMany(CaseInteraction::class);
    }

    public function promises(): HasMany
    {
        return $this->hasMany(PromiseToPay::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    /* ---------------------------------------------------------------
     | Accessors
     * ------------------------------------------------------------- */

    // "المتبقي": what is still unpaid of the overdue amount (never negative)
    protected function remainingAmount(): Attribute
    {
        return Attribute::get(fn () => max(0, (float) $this->overdue_amount - (float) $this->collected_amount));
    }

    // collected / overdue as a percentage (0 - 100)
    protected function collectionRate(): Attribute
    {
        return Attribute::get(fn () => (float) $this->overdue_amount > 0
            ? min(100, (int) round((float) $this->collected_amount / (float) $this->overdue_amount * 100))
            : 0);
    }

    // "active" -> "ACTIVE": how the clients table prints the status badge
    protected function statusCode(): Attribute
    {
        return Attribute::get(fn () => strtoupper((string) $this->status));
    }

    // "active" -> "نشط"
    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn () => self::STATUSES[$this->status] ?? $this->status);
    }

    /* ---------------------------------------------------------------
     | Query scopes
     * ------------------------------------------------------------- */

    public function scopeOfStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeInBucket(Builder $query, int|string|null $bucket): Builder
    {
        return blank($bucket) && $bucket !== 0 && $bucket !== '0' ? $query : $query->where('bucket', (int) $bucket);
    }

    public function scopeAssignedTo(Builder $query, int|User $user): Builder
    {
        return $query->where('assigned_user_id', $user instanceof User ? $user->getKey() : $user);
    }

    // cases that have no employee yet (the "حالات غير موزعة" counter)
    public function scopeUnassigned(Builder $query): Builder
    {
        return $query->whereNull('assigned_user_id');
    }

    public function scopeProcessed(Builder $query): Builder
    {
        return $query->where('is_processed', true);
    }

    // the search box of the clients pages: it looks inside the client (code, name, national ID, phones)
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return blank($term) ? $query : $query->whereHas('client', fn (Builder $q) => $q->search($term));
    }

    // ONE call for every filter of the bank clients page:
    //   DebtCase::forBank(1)->filter($request->only('search', 'loan_type_id', 'bucket', 'employee', 'status'))->paginate(50)
    // "employee": an employee id, or "0" for the cases that have no employee
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->search($filters['search'] ?? null)
            ->when(filled($filters['loan_type_id'] ?? null), fn (Builder $q) => $q->where('loan_type_id', $filters['loan_type_id']))
            ->when(filled($filters['bucket'] ?? null),       fn (Builder $q) => $q->where('bucket', (int) $filters['bucket']))
            ->when(filled($filters['status'] ?? null),       fn (Builder $q) => $q->where('status', strtolower($filters['status'])))
            ->when(filled($filters['employee'] ?? null),     fn (Builder $q) => (string) $filters['employee'] === '0'
                ? $q->whereNull('assigned_user_id')
                : $q->where('assigned_user_id', $filters['employee']));
    }

    public function scopeForBank(Builder $query, int $bankId): Builder
    {
        return $query->where('bank_id', $bankId);
    }

    /* ---------------------------------------------------------------
     | Workflow
     * ------------------------------------------------------------- */

    // Give the case to an employee (one case of the distribution / "تعيين لموظف" pages). All or nothing:
    //   1) close the current assignment, 2) open a new one (the history), 3) update the quick column on the case.
    public function assignTo(User $employee, ?User $by = null, ?string $reason = null): void
    {
        DB::transaction(function () use ($employee, $by, $reason) {
            $this->assignments()->whereNull('unassigned_at')->update(['unassigned_at' => now()]);

            $this->assignments()->create([
                'user_id'     => $employee->getKey(),
                'assigned_by' => $by?->getKey(),
                'assigned_at' => now(),
                'reason'      => $reason,
            ]);

            $this->update(['assigned_user_id' => $employee->getKey()]);
        });
    }

    // Take the case away from its employee (it goes back to the "unassigned" list)
    public function unassign(?string $reason = null): void
    {
        DB::transaction(function () use ($reason) {
            $this->assignments()->whereNull('unassigned_at')->update(['unassigned_at' => now(), 'reason' => $reason]);
            $this->update(['assigned_user_id' => null]);
        });
    }

    // Recalculate "المحصل" from the CONFIRMED payments only, and mark the case as paid when nothing is left.
    // Call it when a payment is confirmed (the collections confirmation page).
    public function refreshCollected(): void
    {
        $confirmed = $this->payments()->where('status', 'confirmed');
        $last      = (clone $confirmed)->latest('paid_at')->first();
        $collected = (float) $confirmed->sum('amount');

        $this->update([
            'collected_amount'    => $collected,
            'last_payment_date'   => $last?->paid_at,
            'last_payment_amount' => $last?->amount,
            'status'              => $collected >= (float) $this->total_debt && (float) $this->total_debt > 0 ? self::STATUS_PAID : $this->status,
        ]);
    }

    public function markProcessed(): void
    {
        $this->update(['is_processed' => true, 'processed_at' => now()]);
    }

    /* ---------------------------------------------------------------
     | Model events
     * ------------------------------------------------------------- */

    protected static function booted(): void
    {
        // the cases of an archived portfolio are frozen: nothing may change them
        // (this protects saves through the model; a raw query-builder update would skip it)
        static::saving(function (DebtCase $case) {
            if ($case->exists && $case->portfolio?->isArchived()) {
                throw new \LogicException('حالات النطاق المؤرشف للقراءة فقط.');
            }
        });
    }
}