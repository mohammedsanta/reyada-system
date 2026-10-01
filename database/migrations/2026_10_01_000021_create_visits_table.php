<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ---------------------------------------------------------------------
// VISITS  =  "إدارة الزيارات": field visits assigned to collectors.
// ---------------------------------------------------------------------
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('debt_case_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();       // the field collector
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete(); // who ordered the visit

            $table->string('status', 20)->default('scheduled'); // scheduled | completed | missed | cancelled
            $table->timestamp('scheduled_at');                  // planned date and time
            $table->timestamp('visited_at')->nullable();        // real visit time

            $table->string('address', 500)->nullable();         // address visited (copy, in case the client address changes later)
            $table->decimal('latitude', 10, 7)->nullable();     // GPS proof that the collector was there
            $table->decimal('longitude', 10, 7)->nullable();

            $table->string('outcome', 30)->nullable();          // client_found | not_home | refused | promised | paid
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status', 'scheduled_at']); // a collector's visit schedule
            $table->index('debt_case_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
