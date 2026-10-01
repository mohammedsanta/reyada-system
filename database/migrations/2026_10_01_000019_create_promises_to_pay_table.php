<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ---------------------------------------------------------------------
// PROMISES_TO_PAY  =  "PTP": the client promises to pay X on date Y.
// Used by: PTP Hub (/banks/{id}/ptp): 5 kanban columns, calendar, stat cards.
// Created BEFORE payments because a payment can point to the promise it fulfils.
// ---------------------------------------------------------------------
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promises_to_pay', function (Blueprint $table) {
            $table->id();

            $table->foreignId('debt_case_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();   // the employee who got the promise

            $table->decimal('promised_amount', 15, 2);        // what the client promised
            $table->decimal('paid_amount', 15, 2)->default(0); // how much he really paid so far (drives the partial progress bar)
            $table->date('promise_date');                     // the day he promised to pay

            // kanban column:  active | review | kept | partial | broken
            //  active  = وعود نشطة      review = قيد المراجعة   kept   = محقق كلياً
            //  partial = محقق جزئياً    broken = مكسور
            $table->string('status', 20)->default('active');

            $table->text('notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete(); // who checked it
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('closed_at')->nullable();       // when it became kept / partial / broken

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'promise_date']);        // board columns + calendar + "overdue" filter
            $table->index(['user_id', 'status']);             // performance page (kept vs broken per employee)
            $table->index('debt_case_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promises_to_pay');
    }
};
