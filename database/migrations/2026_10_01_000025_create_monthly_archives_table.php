<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ---------------------------------------------------------------------
// MONTHLY_ARCHIVES  =  "المستودعات الشهرية": a frozen summary of a closed month per bank.
// When a portfolio is archived we save its final totals + an exported file here.
// ---------------------------------------------------------------------
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_archives', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bank_id')->constrained()->restrictOnDelete();
            $table->foreignId('portfolio_id')->nullable()->constrained()->nullOnDelete(); // the portfolio that was archived

            $table->unsignedSmallInteger('period_year');
            $table->unsignedTinyInteger('period_month');

            // final numbers at the moment of archiving
            $table->unsignedInteger('cases_count')->default(0);
            $table->decimal('total_debt', 15, 2)->default(0);
            $table->decimal('collected_amount', 15, 2)->default(0);

            $table->string('snapshot_path')->nullable();     // exported Excel/PDF of the whole month
            $table->foreignId('archived_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('archived_at');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['bank_id', 'period_year', 'period_month']); // one archive per bank per month
            $table->index(['period_year', 'period_month']);             // list archives by month
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_archives');
    }
};
