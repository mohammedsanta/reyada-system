<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ---------------------------------------------------------------------
// BANKS  =  "البنوك": the institutions whose debts we collect
// Used by: Banks list, bank control panel (/banks/{id}/panel), clients page, PTP Hub.
// ---------------------------------------------------------------------
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banks', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique();       // "Emirates NBD"
            $table->string('code', 20)->unique();   // short code: ENBD, CIB, BM...
            $table->string('logo_path')->nullable(); // logo file shown in the header and tiles
            $table->string('sector')->nullable();   // e.g. "قطاع العملاء" (subtitle under the bank name)

            $table->boolean('is_active')->default(true); // inactive banks are hidden from work screens
            $table->text('notes')->nullable();      // internal notes

            $table->timestamps();
            $table->softDeletes();                  // soft delete keeps old portfolios and reports readable
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};
