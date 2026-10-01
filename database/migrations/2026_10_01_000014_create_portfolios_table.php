<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ---------------------------------------------------------------------
// PORTFOLIOS  =  "النطاق" (scope): the batch of debts a bank gives us for ONE month.
// Flow: import Excel (استيراد النطاق) -> status "active" (عرض / تعديل النطاق)
//       -> end of month -> status "archived" (النطاقات المؤرشفة).
// Every debt case belongs to exactly one portfolio.
// Named "portfolios" (not "scopes") to avoid confusion with Eloquent query scopes.
// ---------------------------------------------------------------------
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolios', function (Blueprint $table) {
            $table->id();

            // restrictOnDelete: a bank that still has portfolios cannot be hard-deleted
            $table->foreignId('bank_id')->constrained()->restrictOnDelete();

            $table->string('name');                          // "Emirates NBD - September 2026"
            $table->unsignedSmallInteger('period_year');     // 2026
            $table->unsignedTinyInteger('period_month');     // 1..12

            $table->string('status', 20)->default('draft');  // draft | active | archived
            // Rule (enforced in the app): a bank has only ONE "active" portfolio at a time.

            // denormalized counters, refreshed after every import (fast dashboard numbers)
            $table->unsignedInteger('cases_count')->default(0);
            $table->decimal('total_debt', 15, 2)->default(0);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('activated_at')->nullable();   // when it became active
            $table->timestamp('archived_at')->nullable();    // when it was archived
            $table->foreignId('archived_by')->nullable()->constrained('users')->nullOnDelete();

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['bank_id', 'period_year', 'period_month']); // one portfolio per bank per month (re-import updates it)
            $table->index(['bank_id', 'status']);                       // "give me the active portfolio of bank X"
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolios');
    }
};
