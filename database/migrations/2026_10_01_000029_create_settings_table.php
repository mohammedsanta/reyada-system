<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ---------------------------------------------------------------------
// SETTINGS  =  "إعدادات النظام": a simple key / value table.
// One row per setting, so adding a new setting never needs a new migration.
// Read them with Setting::pluck('value', 'key') and cache the result.
// ---------------------------------------------------------------------
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            $table->string('key', 100)->unique();   // machine name: company_name, max_cases_per_employee, notify_payment ...
            $table->text('value')->nullable();      // always saved as text; the code casts it (int / bool) when reading
            $table->string('group', 50)->index();   // company | collection | security | notifications (to load one card at a time)

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};