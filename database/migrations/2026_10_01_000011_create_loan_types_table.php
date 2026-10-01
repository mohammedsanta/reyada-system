<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ---------------------------------------------------------------------
// LOAN_TYPES  =  "نوع القرض": قرض شخصي / تمويل عقاري / قرض سلع معمرة ...
// A lookup table (not a fixed enum) so an admin can add a new loan type
// without changing code or running a new migration.
// ---------------------------------------------------------------------
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_types', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique();            // "قرض شخصي"
            $table->boolean('is_active')->default(true); // hide a type without deleting old data

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_types');
    }
};
