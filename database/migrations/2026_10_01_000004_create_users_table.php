<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ---------------------------------------------------------------------
// USERS  =  employees who log in to Collex
// IMPORTANT: delete Laravel's default "0001_01_01_000000_create_users_table.php"
// (keep the default cache and jobs migrations). This file replaces it and also
// creates password_reset_tokens and sessions, exactly like the default one does.
// Used by: Users page, employee performance, supervisor column, login.
// ---------------------------------------------------------------------
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('employee_code', 20)->unique();  // "كود المستخدم" (e.g. 85577) shown in the users table
            $table->string('name');                         // full name
            $table->string('email')->unique();              // login + contact email
            $table->string('phone', 20)->nullable()->unique(); // mobile number (NULL allowed more than once)

            $table->timestamp('email_verified_at')->nullable(); // set when the email is verified
            $table->string('password');                     // always hashed (never plain text)

            // the system role (Owner / Super Visor / Call Center).
            // restrictOnDelete: a role that still has users cannot be deleted.
            $table->foreignId('role_id')->constrained()->restrictOnDelete();

            // "المشرف": the user's supervisor. Points to another row of this same table.
            // nullOnDelete: if the supervisor is removed, the employee simply has no supervisor.
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('status', 20)->default('active'); // active | inactive | suspended  (app-level PHP enum)
            $table->boolean('is_system_account')->default(false); // "حساب نظام": hides row actions, cannot be edited

            $table->string('avatar_path')->nullable();      // profile picture file path
            $table->timestamp('last_login_at')->nullable(); // security: when the user last logged in
            $table->string('last_login_ip', 45)->nullable(); // 45 chars = enough for IPv6

            $table->rememberToken();                        // "remember me" token
            $table->timestamps();
            $table->softDeletes();                          // deleted users stay in the DB (history keeps working)

            // speeds up the users page filters (role + status)
            $table->index(['role_id', 'status']);
        });

        // Laravel: password reset links
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Laravel: database sessions (SESSION_DRIVER=database)
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
