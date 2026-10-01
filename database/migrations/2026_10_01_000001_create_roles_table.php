<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ---------------------------------------------------------------------
// ROLES  =  "الرتبة النظامية"
// Used by: Users page (role badge + role filter) and the "إضافة رتبة نظامية" button.
// Examples: Owner, Super Visor, Call Center.
// This table is created FIRST because users.role_id points to it.
// ---------------------------------------------------------------------
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();                                   // primary key (auto-increment bigint)

            $table->string('name')->unique();               // machine name used in code: owner, super_visor, call_center
            $table->string('label');                        // text shown in the UI: "Super Visor"
            $table->string('description')->nullable();      // optional explanation of what this role can do

            $table->boolean('is_system')->default(false);   // true = built-in role (Owner): cannot be edited or deleted from the UI
            $table->unsignedSmallInteger('level')->default(0); // hierarchy: higher number = more authority (Owner = 100)

            $table->timestamps();                           // created_at / updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
