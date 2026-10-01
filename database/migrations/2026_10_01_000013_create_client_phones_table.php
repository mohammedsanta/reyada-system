<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ---------------------------------------------------------------------
// CLIENT_PHONES  =  every phone number of a client (instead of phone / phone2 columns)
// Why? A client can have 2, 3 or 5 numbers, and collectors need to mark a number
// as "wrong number". The UI's PHONE column = primary + alternate numbers of this table.
// ---------------------------------------------------------------------
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_phones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')->constrained()->cascadeOnDelete(); // phones die with the client

            $table->string('phone', 20);                       // the number, e.g. 01299928200
            $table->string('label', 20)->default('primary');   // primary | alternate | work | other
            $table->boolean('is_valid')->default(true);        // false after a collector marks it "wrong number"

            $table->timestamps();

            $table->unique(['client_id', 'phone']);            // same number cannot be saved twice for one client
            $table->index('phone');                            // search by phone number
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_phones');
    }
};
