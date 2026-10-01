<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ---------------------------------------------------------------------
// PORTFOLIO_IMPORTS  =  log of every Excel upload ("استيراد النطاق")
// Keeps the original file, how many rows worked, and which rows failed and why.
// ---------------------------------------------------------------------
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_imports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('portfolio_id')->constrained()->cascadeOnDelete();
            $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('original_filename');             // name of the file the user uploaded
            $table->string('stored_path');                   // where we saved it (storage/app/...)

            $table->string('status', 20)->default('pending'); // pending | processing | completed | failed

            $table->unsignedInteger('total_rows')->default(0);   // rows found in the file
            $table->unsignedInteger('success_rows')->default(0); // rows imported correctly
            $table->unsignedInteger('failed_rows')->default(0);  // rows rejected
            $table->json('errors')->nullable();                  // [{row: 12, message: "..."}] shown to the user

            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_imports');
    }
};
