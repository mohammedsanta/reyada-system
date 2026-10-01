<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ---------------------------------------------------------------------
// DAILY_COLLECTION_REPORTS  =  "DCR": one report per employee, per bank, per day.
// Summary numbers of what the employee did that day (calls, visits, promises, collected).
// The numbers are calculated from case_interactions / promises / payments, then
// the employee submits the report and a supervisor approves it.
// ---------------------------------------------------------------------
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_collection_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bank_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();   // the employee
            $table->date('report_date');                                       // the day the report is about

            $table->unsignedInteger('cases_worked')->default(0);   // distinct cases touched
            $table->unsignedInteger('calls_count')->default(0);
            $table->unsignedInteger('visits_count')->default(0);
            $table->unsignedInteger('promises_count')->default(0);
            $table->decimal('promised_amount', 15, 2)->default(0); // total promised that day
            $table->decimal('collected_amount', 15, 2)->default(0); // total collected that day

            $table->string('status', 20)->default('draft');       // draft | submitted | approved | rejected
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['bank_id', 'user_id', 'report_date']); // one report per employee per bank per day
            $table->index('report_date');                          // daily overview across all banks
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_collection_reports');
    }
};
