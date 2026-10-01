<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ---------------------------------------------------------------------
// INSTALLMENT_COMPANY_USER  =  same as bank_user but for installment companies.
// Two separate pivots (instead of one polymorphic table) keep real foreign keys,
// so the database itself guarantees the data is valid.
// ---------------------------------------------------------------------
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installment_company_user', function (Blueprint $table) {
            $table->foreignId('installment_company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // MySQL limits identifier length, so the key names are written short on purpose
            $table->primary(['installment_company_id', 'user_id'], 'inst_company_user_primary');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installment_company_user');
    }
};
