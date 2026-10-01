<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ---------------------------------------------------------------------
// COMPLAINTS  =  "إدارة الشكاوى": client complaints to review and answer.
// ---------------------------------------------------------------------
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();

            $table->string('reference_number', 30)->unique(); // human friendly number: CMP-000123

            $table->foreignId('bank_id')->constrained()->restrictOnDelete();
            $table->foreignId('debt_case_id')->nullable()->constrained()->nullOnDelete(); // related case (if known)
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();    // complaining client (if known)

            $table->foreignId('logged_by')->nullable()->constrained('users')->nullOnDelete();   // employee who registered it
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete(); // employee who must handle it

            $table->string('subject');                          // short title
            $table->text('description');                        // full complaint text
            $table->string('source', 30)->nullable();           // phone | whatsapp | email | bank | visit
            $table->string('priority', 20)->default('medium');  // low | medium | high | urgent
            $table->string('status', 20)->default('open');      // open | in_review | resolved | rejected | closed

            $table->timestamp('due_at')->nullable();            // answer deadline (SLA)
            $table->text('resolution')->nullable();             // how it was solved
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['bank_id', 'status']);               // complaints board per bank
            $table->index(['assigned_to', 'status']);           // "my open complaints"
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
