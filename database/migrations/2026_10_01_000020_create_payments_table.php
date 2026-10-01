<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ---------------------------------------------------------------------
// PAYMENTS  =  every collection receipt ("إيصال سداد").
// Used by: dashboard "آخر المعاملات الميدانية", collections confirmation page
// ("تأكيد التحصيلات"), employee total collected, bank "المحصل".
// Workflow: collector records it (pending) -> a supervisor confirms (confirmed) or rejects.
// Only CONFIRMED payments count in debt_cases.collected_amount.
// ---------------------------------------------------------------------
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->string('receipt_number', 30)->unique();  // "#4092": printed on the receipt, never repeated

            $table->foreignId('debt_case_id')->constrained()->restrictOnDelete();  // money history must never disappear
            $table->foreignId('collector_id')->nullable()->constrained('users')->nullOnDelete(); // "المحصل"
            $table->foreignId('promise_id')->nullable()->constrained('promises_to_pay')->nullOnDelete(); // the PTP this payment fulfils (optional)

            $table->decimal('amount', 15, 2);                // paid amount
            $table->string('method', 30);                    // cash | e_wallet | bank_transfer | card | cheque
            $table->string('reference', 100)->nullable();    // transfer / wallet transaction number
            $table->string('proof_path')->nullable();        // photo or PDF of the receipt
            $table->timestamp('paid_at');                    // when the client actually paid

            // confirmation workflow ("تأكيد التحصيلات")
            $table->string('status', 20)->default('pending'); // pending | confirmed | rejected
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->string('rejection_reason')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['debt_case_id', 'status']);       // total collected per case
            $table->index(['collector_id', 'paid_at']);      // employee collections by date
            $table->index(['status', 'paid_at']);            // confirmation queue
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
