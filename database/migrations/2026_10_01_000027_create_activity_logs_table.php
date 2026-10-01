<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ---------------------------------------------------------------------
// ACTIVITY_LOGS  =  "سجل النشاط": who did what, when, from where.
// Rows are only ever INSERTED (never updated), so there is no updated_at column.
// Examples: user X edited client Y (old/new values), login, export, import, distribution.
// ---------------------------------------------------------------------
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            // who did it. NULL for system/automatic actions or if the user was deleted.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('event', 50);                // created | updated | deleted | login | logout | imported | exported | assigned ...
            $table->string('description', 500)->nullable(); // readable sentence shown in the log page

            // WHAT it was done to (polymorphic): any model: Bank, DebtCase, Payment...
            // creates subject_type + subject_id (+ an index on both)
            $table->nullableMorphs('subject');

            $table->json('properties')->nullable();     // {"old": {...}, "new": {...}} changed values

            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable(); // browser / device

            $table->timestamp('created_at')->useCurrent(); // logs are immutable: only created_at

            $table->index(['user_id', 'created_at']);   // activity of one user
            $table->index('event');                     // filter by event type
            $table->index('created_at');                // newest first
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
