<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ---------------------------------------------------------------------
// PERMISSIONS  =  one row for every single thing a user can be allowed to do
// Used by: the "الصلاحيات" (permissions) button in the users table.
// Naming convention:  <module>.<action>   e.g.  banks.view, ptp.manage, users.delete
// ---------------------------------------------------------------------
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique();   // machine name checked in code: $user->can('banks.view')
            $table->string('label');            // Arabic text shown in the permissions screen
            $table->string('group')->index();   // module name (banks, users, ptp...) to group checkboxes in the UI

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
