<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ---------------------------------------------------------------------
// DEBT_CASES  =  "الحالات": ONE debt of ONE client inside ONE portfolio. THE core table.
// Every row of the bank clients table and the whole edit modal (debt + dates sections)
// come from here (joined with clients). "6 حالات" in the page header = 6 rows here.
// Named "debt_cases" because "case" is a reserved word in both SQL and PHP.
// ---------------------------------------------------------------------
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debt_cases', function (Blueprint $table) {
            $table->id();

            // ---- relations ----
            $table->foreignId('portfolio_id')->constrained()->cascadeOnDelete();   // the month/batch it belongs to
            $table->foreignId('bank_id')->constrained()->restrictOnDelete();       // copy of portfolio.bank_id: avoids a join in the most common query
            $table->foreignId('client_id')->constrained()->restrictOnDelete();     // the debtor
            $table->foreignId('loan_type_id')->nullable()->constrained()->nullOnDelete(); // "نوع القرض"
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete(); // "الموظف" who works this case now

            $table->string('loan_number', 50)->nullable(); // the bank's own loan/contract number
            $table->string('status', 20)->default('active'); // active | inactive | paid | legal  (shown as ACTIVE badge)

            // ---- money (decimal(15,2): exact, never use float for money) ----
            $table->decimal('total_debt', 15, 2)->default(0);        // "إجمالي المديونية"
            $table->decimal('overdue_amount', 15, 2)->default(0);    // "المبلغ المتأخر"
            $table->decimal('installment_value', 15, 2)->nullable(); // "قيمة القسط"
            $table->decimal('min_installment_diff', 15, 2)->nullable(); // "أقل قسط (DIFF)"
            $table->decimal('late_fee', 15, 2)->default(0);          // "غرامة التأخر"
            $table->decimal('collected_amount', 15, 2)->default(0);  // "المحصل": sum of confirmed payments (remaining = overdue - collected)

            // ---- delinquency ----
            $table->unsignedTinyInteger('bucket')->default(0);       // "الشريحة (BUCKET)": 0,1,2,3...
            $table->unsignedInteger('dpd')->default(0);              // days past due

            // ---- dates ----
            $table->date('next_due_date')->nullable();               // "تاريخ الاستحقاق القادم"
            $table->date('loan_start_date')->nullable();             // "تاريخ منح القرض"
            $table->date('loan_end_date')->nullable();               // "تاريخ انتهاء القرض"
            $table->date('last_payment_date')->nullable();           // "تاريخ آخر دفعة"
            $table->decimal('last_payment_amount', 15, 2)->nullable(); // "قيمة آخر دفعة"

            // ---- processing ("حالات تمت معالجتها" card) ----
            $table->boolean('is_processed')->default(false);
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // The same client cannot appear twice with the same loan number in one portfolio
            $table->unique(['portfolio_id', 'client_id', 'loan_number']);

            // ---- indexes for the filters used on the clients page ----
            $table->index(['portfolio_id', 'status']);          // list of one portfolio by status
            $table->index(['bank_id', 'assigned_user_id']);     // "my cases in bank X" + employee filter
            $table->index('bucket');                            // bucket filter
            $table->index('dpd');                               // sorting/filtering by days past due
            $table->index('next_due_date');                     // due-soon reports
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debt_cases');
    }
};
