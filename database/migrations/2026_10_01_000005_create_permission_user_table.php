<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ---------------------------------------------------------------------
// PERMISSION_USER  =  per-user exceptions on top of the role's permissions
// Example: a Call Center employee gets ONE extra permission (granted = true),
// or loses one permission his role normally has (granted = false).
// Final permissions = role permissions + granted overrides - revoked overrides.
// ---------------------------------------------------------------------
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();

            $table->boolean('granted')->default(true); // true = extra permission, false = permission taken away

            $table->timestamps();

            $table->primary(['user_id', 'permission_id']); // one override per user per permission
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_user');
    }
};
