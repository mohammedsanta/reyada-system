<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ---------------------------------------------------------------------
// PERFORMANCE_SNAPSHOTS  =  monthly KPI of every employee per bank.
// Used by: "أداء الموظفين" page (month/year filter, efficiency bar, ranking).
// A scheduled job calculates and saves the numbers, so old months stay fixed
// even if the live data changes later, and the page loads fast.
// ---------------------------------------------------------------------
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_snapshots', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('period_year');
            $table->unsignedTinyInteger('period_month');

            $table->unsignedInteger('cases_assigned')->default(0);   // "الحالات الموزعة"
            $table->unsignedInteger('cases_processed')->default(0);
            $table->unsignedInteger('promises_total')->default(0);
            $table->unsignedInteger('promises_kept')->default(0);    // "وعود الدفع الناجحة"
            $table->unsignedInteger('promises_broken')->default(0);

            $table->decimal('collected_amount', 15, 2)->default(0);  // "إجمالي التحصيل"
            $table->decimal('target_amount', 15, 2)->default(0);     // monthly target
            $table->decimal('efficiency', 5, 2)->default(0);         // "الكفاءة" 0.00 - 100.00
            $table->unsignedSmallInteger('rank_position')->nullable(); // place in the live ranking ("rank" is a reserved word in MySQL 8)

            $table->timestamp('calculated_at')->nullable();          // when the job last calculated it

            $table->timestamps();

            $table->unique(['user_id', 'bank_id', 'period_year', 'period_month'], 'perf_snapshot_unique'); // one row per employee/bank/month (short custom name: MySQL max identifier = 64 chars)
            $table->index(['period_year', 'period_month', 'efficiency']);          // ranking query
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_snapshots');
    }
};
