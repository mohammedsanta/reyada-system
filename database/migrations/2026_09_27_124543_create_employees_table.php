<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            // User account
            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            // Personal information
            $table->string('name');
            $table->string('national_id', 14)->unique();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();

            // Social information
            $table->string('marital_status')->nullable();

            // Job information
            $table->foreignId('role_id')
                ->constrained('roles')
                ->restrictOnDelete();

            $table->date('hire_date')->nullable();
            $table->string('job_title')->nullable();
            $table->decimal('salary', 12, 2)->nullable();

            // Employee status
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};