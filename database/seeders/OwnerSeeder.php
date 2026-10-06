<?php

// database/seeders/OwnerSeeder.php
// Creates the Owner role and the first user, so you can log in.
//   php artisan db:seed --class=OwnerSeeder
//   login: test@collex.com   password: password      (CHANGE IT after the first login)

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OwnerSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // updateOrInsert: running the seeder twice does not create duplicates
        DB::table('roles')->updateOrInsert(
            ['name' => 'owner'],
            ['label' => 'Owner', 'description' => 'صلاحيات كاملة على النظام', 'is_system' => true, 'level' => 100, 'created_at' => $now, 'updated_at' => $now]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'test@collex.com'],
            [
                'employee_code'     => '922885',
                'name'              => 'Test Account',
                'phone'             => '01276739380',
                'password'          => Hash::make('password'),
                'role_id'           => DB::table('roles')->where('name', 'owner')->value('id'),
                'status'            => 'active',
                'is_system_account' => true,
                'email_verified_at' => $now,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]
        );
    }
}