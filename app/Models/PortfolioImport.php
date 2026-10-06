<?php

// app/Models/PortfolioImport.php
// The log of every Excel upload ("استيراد النطاق"): the original file, how many rows worked, which rows failed and why.
// Table: portfolio_imports  (migration 2026_10_01_000015_create_portfolio_imports_table.php)

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PortfolioImport extends Model
{
    /** @use HasFactory<\Database\Factories\PortfolioImportFactory> */
    use HasFactory;

    // the values allowed in portfolio_imports.status
    public const STATUS_PENDING    = 'pending';      // uploaded, waiting in the queue
    public const STATUS_PROCESSING = 'processing';   // the queued job is reading the file
    public const STATUS_COMPLETED  = 'completed';
    public const STATUS_FAILED     = 'failed';       // the whole file could not be processed

    // status => Arabic text (the history table on the import page)
    public const LABELS = [
        self::STATUS_PENDING    => 'في الانتظار',
        self::STATUS_PROCESSING => 'قيد المعالجة',
        self::STATUS_COMPLETED  => 'مكتمل',
        self::STATUS_FAILED     => 'فشل',
    ];

    protected $fillable = [
        'portfolio_id',
        'imported_by',
        'original_filename',   // the name of the file the user uploaded: scope_september.xlsx
        'stored_path',         // where we saved it (storage/app/...)
        'status',
        'total_rows',          // rows found in the file
        'success_rows',        // rows imported correctly
        'failed_rows',         // rows rejected
        'errors',              // [{row: 12, message: "..."}] shown to the user
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'total_rows'   => 'integer',
            'success_rows' => 'integer',
            'failed_rows'  => 'integer',
            'errors'       => 'array',      // the JSON column becomes a PHP array and back
            'started_at'   => 'datetime',
            'finished_at'  => 'datetime',
        ];
    }

    /* ---------------------------------------------------------------
     | Relations
     * ------------------------------------------------------------- */

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }

    // who uploaded the file
    public function importer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    /* ---------------------------------------------------------------
     | Accessors:  $import->status_label / ->success_rate / ->duration_seconds
     * ------------------------------------------------------------- */

    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn () => self::LABELS[$this->status] ?? $this->status);
    }

    // percentage of the rows that were imported (0 - 100)
    protected function successRate(): Attribute
    {
        return Attribute::get(fn () => $this->total_rows > 0 ? (int) round($this->success_rows / $this->total_rows * 100) : 0);
    }

    // how long the job ran, in seconds (null until it finishes)
    protected function durationSeconds(): Attribute
    {
        return Attribute::get(fn () => $this->started_at && $this->finished_at ? $this->started_at->diffInSeconds($this->finished_at) : null);
    }

    /* ---------------------------------------------------------------
     | Query scopes:  PortfolioImport::pending()->get()
     * ------------------------------------------------------------- */

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->latest();
    }

    /* ---------------------------------------------------------------
     | Workflow: the queued import job calls these three methods
     * ------------------------------------------------------------- */

    public function markProcessing(int $totalRows): void
    {
        $this->update(['status' => self::STATUS_PROCESSING, 'total_rows' => $totalRows, 'started_at' => now()]);
    }

    // $errors = [['row' => 12, 'message' => 'الرقم القومي غير صحيح'], ...]
    public function markCompleted(int $successRows, int $failedRows, array $errors = []): void
    {
        $this->update([
            'status'       => self::STATUS_COMPLETED,
            'success_rows' => $successRows,
            'failed_rows'  => $failedRows,
            'errors'       => $errors ?: null,
            'finished_at'  => now(),
        ]);
    }

    // the file itself could not be read (wrong format, missing columns ...)
    public function markFailed(string $message): void
    {
        $this->update([
            'status'      => self::STATUS_FAILED,
            'errors'      => [['row' => 0, 'message' => $message]],
            'finished_at' => now(),
        ]);
    }

    /* ---------------------------------------------------------------
     | Model events
     * ------------------------------------------------------------- */

    protected static function booted(): void
    {
        // deleting the log row also deletes the uploaded file from the disk
        static::deleting(function (PortfolioImport $import) {
            if ($import->stored_path) {
                Storage::delete($import->stored_path);
            }
        });
    }
}