<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ---------------------------------------------------------------------
// INSTALLMENT_COMPANIES  =  "شركات التقسيط"
// Same idea as banks, kept in its own table because it has its own sidebar section.
// Users are assigned to them through installment_company_user (see migration 09).
// ---------------------------------------------------------------------
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installment_companies', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique();
            $table->string('code', 20)->unique();
            $table->string('logo_path')->nullable();

            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installment_companies');
    }
};
