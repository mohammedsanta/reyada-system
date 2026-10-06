<?php

// app/Models/Complaint.php
// "الشكوى": a client complaint that must be reviewed, assigned to someone, and closed with a written resolution.
// Table: complaints  (migration 2026_10_01_000022_create_complaints_table.php)

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Complaint extends Model
{
    /** @use HasFactory<\Database\Factories\ComplaintFactory> */
    use HasFactory, SoftDeletes;

    // the values allowed in complaints.status
    public const STATUS_OPEN      = 'open';
    public const STATUS_IN_REVIEW = 'in_review';
    public const STATUS_RESOLVED  = 'resolved';
    public const STATUS_REJECTED  = 'rejected';
    public const STATUS_CLOSED    = 'closed';

    public const STATUSES = [
        self::STATUS_OPEN      => 'مفتوحة',
        self::STATUS_IN_REVIEW => 'قيد المراجعة',
        self::STATUS_RESOLVED  => 'تم الحل',
        self::STATUS_REJECTED  => 'مرفوضة',
        self::STATUS_CLOSED    => 'مغلقة',
    ];

    // the values allowed in complaints.priority, and how many days we have to answer (the SLA deadline)
    public const PRIORITIES = ['low' => 'منخفضة', 'medium' => 'متوسطة', 'high' => 'عالية', 'urgent' => 'عاجلة'];
    public const SLA_DAYS   = ['urgent' => 1, 'high' => 2, 'medium' => 3, 'low' => 5];

    public const SOURCES = ['phone' => 'مكالمة هاتفية', 'whatsapp' => 'واتساب', 'email' => 'بريد إلكتروني', 'bank' => 'من البنك', 'visit' => 'زيارة'];

    protected $fillable = [
        'reference_number',   // CMP-000123: generated automatically when empty
        'bank_id',
        'debt_case_id',       // the related case (if known)
        'client_id',          // the complaining client (if known)
        'logged_by',          // the employee who registered it
        'assigned_to',        // the employee who must handle it
        'subject',
        'description',
        'source',
        'priority',
        'status',
        'due_at',             // answer deadline (SLA): set from the priority when empty
        'resolution',         // how it was solved (or why it was rejected)
        'resolved_by',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'due_at'      => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    /* ---------------------------------------------------------------
     | Relations
     * ------------------------------------------------------------- */

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function debtCase(): BelongsTo
    {
        return $this->belongsTo(DebtCase::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    // who registered the complaint
    public function logger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by');
    }

    // "المسؤول": who must handle it
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // who closed it
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /* ---------------------------------------------------------------
     | Accessors
     * ------------------------------------------------------------- */

    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn () => self::STATUSES[$this->status] ?? $this->status);
    }

    protected function priorityLabel(): Attribute
    {
        return Attribute::get(fn () => self::PRIORITIES[$this->priority] ?? $this->priority);
    }

    protected function sourceLabel(): Attribute
    {
        return Attribute::get(fn () => $this->source ? (self::SOURCES[$this->source] ?? $this->source) : null);
    }

    // still waiting for an answer
    protected function isOpen(): Attribute
    {
        return Attribute::get(fn () => in_array($this->status, [self::STATUS_OPEN, self::STATUS_IN_REVIEW], true));
    }

    // open AND past its deadline
    protected function isOverdue(): Attribute
    {
        return Attribute::get(fn () => $this->is_open && $this->due_at && $this->due_at->isPast());
    }

    // the "الموعد النهائي" column: "اليوم" / "غداً" / "بعد 3 يوم" / "متأخرة 2 يوم" / "-" once it is closed
    protected function dueLabel(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->is_open || ! $this->due_at) {
                return '-';
            }

            $days = (int) now()->startOfDay()->diffInDays($this->due_at->copy()->startOfDay(), false);

            return match (true) {
                $days < 0   => 'متأخرة ' . abs($days) . ' يوم',
                $days === 0 => 'اليوم',
                $days === 1 => 'غداً',
                default     => 'بعد ' . $days . ' يوم',
            };
        });
    }

    // the "التسجيل" column: "اليوم 08:40" / "أمس 14:10" / "قبل 3 أيام"
    protected function createdLabel(): Attribute
    {
        return Attribute::get(fn () => match (true) {
            $this->created_at->isToday()     => 'اليوم ' . $this->created_at->format('H:i'),
            $this->created_at->isYesterday() => 'أمس ' . $this->created_at->format('H:i'),
            default                          => 'قبل ' . $this->created_at->diffInDays(now()) . ' أيام',
        });
    }

    /* ---------------------------------------------------------------
     | Query scopes:  Complaint::forBank(1)->open()->urgent()->get()
     * ------------------------------------------------------------- */

    public function scopeOfStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    // waiting for an answer (open or in review)
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_OPEN, self::STATUS_IN_REVIEW]);
    }

    public function scopeUrgent(Builder $query): Builder
    {
        return $query->where('priority', 'urgent');
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->open()->whereNotNull('due_at')->where('due_at', '<', now());
    }

    public function scopeForBank(Builder $query, int $bankId): Builder
    {
        return $query->where('bank_id', $bankId);
    }

    public function scopeAssignedTo(Builder $query, int|User $user): Builder
    {
        return $query->where('assigned_to', $user instanceof User ? $user->getKey() : $user);
    }

    // the search box: complaint number, subject or the client's name
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('reference_number', 'like', "%{$term}%")
              ->orWhere('subject', 'like', "%{$term}%")
              ->orWhereHas('client', fn (Builder $c) => $c->where('name', 'like', "%{$term}%"));
        });
    }

    /* ---------------------------------------------------------------
     | Workflow  (the complaint details page)
     * ------------------------------------------------------------- */

    public function assignTo(User $employee): void
    {
        $this->update(['assigned_to' => $employee->getKey(), 'status' => $this->status === self::STATUS_OPEN ? self::STATUS_IN_REVIEW : $this->status]);
    }

    // Close it as "تم الحل" (resolved) or "مرفوضة" (rejected). The written resolution is REQUIRED, like on the page.
    public function closeWith(string $status, User $by, string $resolution): void
    {
        if (! in_array($status, [self::STATUS_RESOLVED, self::STATUS_REJECTED], true)) {
            throw new \InvalidArgumentException('يمكن إغلاق الشكوى بحالة "تم الحل" أو "مرفوضة" فقط.');
        }

        if (blank($resolution)) {
            throw new \InvalidArgumentException('اكتب نتيجة الشكوى قبل إغلاقها.');
        }

        $this->update([
            'status'      => $status,
            'resolution'  => $resolution,
            'resolved_by' => $by->getKey(),
            'resolved_at' => now(),
        ]);
    }

    /* ---------------------------------------------------------------
     | Numbers of the pages
     * ------------------------------------------------------------- */

    // [status => how many]  (the tabs and the stat cards):  Complaint::countsByStatus(1)
    public static function countsByStatus(?int $bankId = null): array
    {
        return static::query()
            ->when($bankId, fn (Builder $q) => $q->forBank($bankId))
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();
    }

    // "عاجلة": urgent complaints that are still waiting
    public static function urgentOpenCount(?int $bankId = null): int
    {
        return static::query()->open()->urgent()->when($bankId, fn (Builder $q) => $q->forBank($bankId))->count();
    }

    // the next reference number: CMP-000105  (the highest number + 1)
    // (SUBSTRING / CAST ... AS UNSIGNED is MySQL / MariaDB syntax. The unique index protects against duplicates.)
    public static function nextReference(): string
    {
        $last = static::withTrashed()->select(DB::raw('MAX(CAST(SUBSTRING(reference_number, 5) AS UNSIGNED)) as last_number'))->value('last_number');

        return 'CMP-' . str_pad((string) ((int) $last + 1), 6, '0', STR_PAD_LEFT);
    }

    /* ---------------------------------------------------------------
     | Model events
     * ------------------------------------------------------------- */

    protected static function booted(): void
    {
        static::creating(function (Complaint $complaint) {
            $complaint->reference_number ??= static::nextReference();
            $complaint->status           ??= self::STATUS_OPEN;
            $complaint->priority         ??= 'medium';

            // the answer deadline follows the priority (urgent = 1 day ... low = 5 days)
            $complaint->due_at ??= now()->addDays(self::SLA_DAYS[$complaint->priority] ?? 3);
        });
    }
}