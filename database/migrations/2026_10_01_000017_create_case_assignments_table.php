<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ---------------------------------------------------------------------
// CASE_ASSIGNMENTS  =  history of "توزيع الحالات" (who worked which case, and when)
// debt_cases.assigned_user_id = the CURRENT employee (fast to read).
// This table = the full HISTORY (who had it before, who moved it, why).
// ---------------------------------------------------------------------
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('debt_case_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();       // the employee who received the case
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete(); // the supervisor who distributed it

            $table->timestamp('assigned_at')->useCurrent(); // when he received it
            $table->timestamp('unassigned_at')->nullable(); // NULL = still assigned to him
            $table->string('reason')->nullable();           // "reassigned", "employee left"...

            $table->timestamps();

            $table->index(['debt_case_id', 'unassigned_at']); // "who has this case right now?"
            $table->index(['user_id', 'assigned_at']);        // "all cases this employee received"
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_assignments');
    }
};
