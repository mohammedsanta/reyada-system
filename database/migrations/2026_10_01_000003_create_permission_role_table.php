<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ---------------------------------------------------------------------
// PERMISSION_ROLE  =  pivot (many-to-many) between roles and permissions
// "Super Visor" has these permissions, "Call Center" has those permissions...
// ---------------------------------------------------------------------
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_role', function (Blueprint $table) {
            // if a role is deleted, its permission links are deleted too (cascade)
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            // if a permission is deleted, it disappears from every role (cascade)
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();

            // composite primary key: the same permission cannot be attached twice to one role
            $table->primary(['role_id', 'permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_role');
    }
};
