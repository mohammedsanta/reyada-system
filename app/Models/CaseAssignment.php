<?php

// app/Models/CaseAssignment.php
// The HISTORY of "توزيع الحالات": who had which case, from when to when, who moved it and why.
// debt_cases.assigned_user_id = the CURRENT employee (fast to read).  This table = every employee the case ever had.
// Table: case_assignments  (migration 2026_10_01_000017_create_case_assignments_table.php)

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class CaseAssignment extends Model
{
    /** @use HasFactory<\Database\Factories\CaseAssignmentFactory> */
    use HasFactory;

    protected $fillable = [
        'debt_case_id',
        'user_id',        // the employee who received the case
        'assigned_by',    // the supervisor who distributed it
        'assigned_at',    // when he received it
        'unassigned_at',  // NULL = the case is still his
        'reason',         // "reassigned", "employee left" ...
    ];

    protected function casts(): array
    {
        return [
            'assigned_at'   => 'datetime',
            'unassigned_at' => 'datetime',
        ];
    }

    /* ---------------------------------------------------------------
     | Relations
     * ------------------------------------------------------------- */

    public function debtCase(): BelongsTo
    {
        return $this->belongsTo(DebtCase::class);
    }

    // the employee who received the case
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // the supervisor who gave it to him
    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /* ---------------------------------------------------------------
     | Accessors:  $assignment->is_open  /  $assignment->days
     * ------------------------------------------------------------- */

    // true while the employee still has the case
    protected function isOpen(): Attribute
    {
        return Attribute::get(fn () => $this->unassigned_at === null);
    }

    // how many days he had the case (until today when it is still open)
    protected function days(): Attribute
    {
        return Attribute::get(fn () => $this->assigned_at ? $this->assigned_at->diffInDays($this->unassigned_at ?? now()) : 0);
    }

    /* ---------------------------------------------------------------
     | Query scopes:  CaseAssignment::open()->forEmployee($user)->get()
     * ------------------------------------------------------------- */

    // assignments that are valid right now
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNull('unassigned_at');
    }

    // assignments that already ended
    public function scopeClosed(Builder $query): Builder
    {
        return $query->whereNotNull('unassigned_at');
    }

    public function scopeForEmployee(Builder $query, int|User $user): Builder
    {
        return $query->where('user_id', $user instanceof User ? $user->getKey() : $user);
    }

    /* ---------------------------------------------------------------
     | Helpers
     * ------------------------------------------------------------- */

    // end this assignment (the case goes back to nobody, or a new assignment is opened right after)
    public function close(?string $reason = null): bool
    {
        return $this->update(['unassigned_at' => now(), 'reason' => $reason ?? $this->reason]);
    }

    // The current workload of every employee: [user id => number of open cases].
    // This is the "الحمل الحالي" column of the distribution page.
    //     CaseAssignment::workload(1)   // only the cases of bank 1
    public static function workload(?int $bankId = null): Collection
    {
        return static::query()
            ->open()
            ->when($bankId, fn (Builder $q) => $q->whereHas('debtCase', fn (Builder $c) => $c->where('bank_id', $bankId)))
            ->selectRaw('user_id, COUNT(*) as cases')
            ->groupBy('user_id')
            ->pluck('cases', 'user_id');
    }
}