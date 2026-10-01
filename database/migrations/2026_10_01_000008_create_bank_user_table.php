<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ---------------------------------------------------------------------
// BANK_USER  =  which banks each employee may work on
// Used by: the "البنوك والشركات" column in the users table.
// A user only sees the banks listed here (except the Owner).
// ---------------------------------------------------------------------
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_user', function (Blueprint $table) {
            $table->foreignId('bank_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // who gave this access (audit); NULL if that user was deleted
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->primary(['bank_id', 'user_id']); // one link per bank/user pair
            $table->index('user_id');                // fast "which banks does this user have?"
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_user');
    }
};
