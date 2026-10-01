<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ---------------------------------------------------------------------
// GOVERNORATES  =  "المحافظة": lookup table (Cairo, Giza, Assiut...)
// Why a table and not free text? Clean filters, no typos ("القاهره" vs "القاهرة"),
// and easy reports per governorate. Fill it with a seeder (27 rows).
// ---------------------------------------------------------------------
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('governorates', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique();        // Arabic name
            $table->string('name_en')->nullable();   // English name (optional, for exports)

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('governorates');
    }
};
