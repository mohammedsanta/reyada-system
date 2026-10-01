<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ---------------------------------------------------------------------
// CASE_INTERACTIONS  =  every call / WhatsApp / SMS / note a collector makes on a case.
// This is the daily work log. It feeds the DCR report and the employee performance page.
// ---------------------------------------------------------------------
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_interactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('debt_case_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();       // the collector who did it
            $table->foreignId('client_phone_id')->nullable()->constrained('client_phones')->nullOnDelete(); // which number was called

            $table->string('type', 20);                  // call | whatsapp | sms | email | visit | note
            $table->string('outcome', 30)->nullable();   // answered | no_answer | wrong_number | refused | promised | paid
            $table->text('notes')->nullable();           // what was said
            $table->unsignedInteger('duration_seconds')->nullable(); // call length

            $table->timestamp('occurred_at');            // when it happened (can differ from created_at)
            $table->timestamp('followup_at')->nullable(); // reminder: "call him again at..."

            $table->timestamps();

            $table->index(['debt_case_id', 'occurred_at']); // timeline of one case
            $table->index(['user_id', 'occurred_at']);      // daily work of one employee
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_interactions');
    }
};
