<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ---------------------------------------------------------------------
// REPORT_EXPORTS  =  "التقارير": every Excel/PDF a user generates (clients list, DCR...).
// Big exports run in a queue: the row starts as "pending" and the user downloads
// the file when it becomes "completed". Also an audit of who exported what data.
// ---------------------------------------------------------------------
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_exports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();   // who asked for the export

            $table->string('type', 50);                       // clients | dcr | performance | payments ...
            $table->string('format', 10);                     // xlsx | pdf | csv
            $table->json('filters')->nullable();              // the filters used (bank, month, bucket...) so it can be repeated

            $table->string('status', 20)->default('pending'); // pending | completed | failed
            $table->string('file_path')->nullable();          // generated file
            $table->unsignedInteger('row_count')->nullable();
            $table->text('error')->nullable();                // failure reason

            $table->timestamp('generated_at')->nullable();
            $table->timestamp('expires_at')->nullable();      // old files can be deleted automatically

            $table->timestamps();

            $table->index(['user_id', 'created_at']);         // "my recent exports"
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_exports');
    }
};
