<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ---------------------------------------------------------------------
// NOTIFICATIONS  =  "الإشعارات" (bell icon + red badge in the sidebar).
// This is Laravel's standard database-notifications table
// (same as: php artisan make:notifications-table). Used with $user->notify(...)
// and read with $user->unreadNotifications()->count() for the badge number.
// ---------------------------------------------------------------------
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();          // Laravel uses a UUID here
            $table->string('type');                 // the Notification class name
            $table->morphs('notifiable');           // notifiable_type + notifiable_id (the user that receives it)
            $table->text('data');                   // JSON payload: title, message, link
            $table->timestamp('read_at')->nullable(); // NULL = unread (counts in the badge)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
