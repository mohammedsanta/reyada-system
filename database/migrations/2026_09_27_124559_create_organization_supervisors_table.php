<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_supervisors', function (Blueprint $table) {
            $table->foreignId('organization_id')
                ->constrained('organizations')
                ->cascadeOnDelete();

            $table->foreignId('supervisor_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            $table->primary(['organization_id', 'supervisor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_supervisors');
    }
};