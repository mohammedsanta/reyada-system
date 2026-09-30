<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supervisor_collectors', function (Blueprint $table) {
            $table->foreignId('supervisor_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            $table->foreignId('collector_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            $table->primary(['supervisor_id', 'collector_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supervisor_collectors');
    }
};