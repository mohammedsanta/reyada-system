<?php

namespace Database\Seeders\System;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $ownerRole = DB::table('roles')
            ->where('name', 'owner')
            ->value('id');

        $supervisorRole = DB::table('roles')
            ->where('name', 'super_visor')
            ->value('id');

        $collectorRole = DB::table('roles')
            ->where('name', 'call_center')
            ->value('id');

        /*
        |--------------------------------------------------------------------------
        | OWNER
        |--------------------------------------------------------------------------
        */

        $ownerId = DB::table('users')->insertGetId([
            'employee_code' => 'EMP-0001',
            'name' => 'System Owner',
            'email' => 'owner@collex.test',
            'phone' => '01000000001',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role_id' => $ownerRole,
            'supervisor_id' => null,
            'status' => 'active',
            'is_system_account' => true,
            'avatar_path' => null,
            'last_login_at' => now()->subMinutes(30),
            'last_login_ip' => '127.0.0.1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | SUPERVISORS
        |--------------------------------------------------------------------------
        */

        $supervisors = [];

        for ($i = 1; $i <= 3; $i++) {
            $id = DB::table('users')->insertGetId([
                'employee_code' => sprintf('SUP-%04d', $i),
                'name' => "Supervisor {$i}",
                'email' => "supervisor{$i}@collex.test",
                'phone' => '01000000' . str_pad($i + 10, 3, '0', STR_PAD_LEFT),
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role_id' => $supervisorRole,
                'supervisor_id' => $ownerId,
                'status' => 'active',
                'is_system_account' => false,
                'last_login_at' => now()->subHours($i),
                'last_login_ip' => '127.0.0.1',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $supervisors[] = $id;
        }

        /*
        |--------------------------------------------------------------------------
        | CALL CENTER EMPLOYEES
        |--------------------------------------------------------------------------
        */

        for ($i = 1; $i <= 15; $i++) {
            $supervisorId = $supervisors[($i - 1) % count($supervisors)];

            DB::table('users')->insert([
                'employee_code' => sprintf('COL-%04d', $i),
                'name' => "Collector {$i}",
                'email' => "collector{$i}@collex.test",
                'phone' => '01100000' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role_id' => $collectorRole,
                'supervisor_id' => $supervisorId,
                'status' => $i === 15 ? 'inactive' : 'active',
                'is_system_account' => false,
                'last_login_at' => now()->subDays($i % 5),
                'last_login_ip' => '127.0.0.1',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | DIRECT USER PERMISSION OVERRIDE
        |--------------------------------------------------------------------------
        |
        | Give Supervisor 1 report export permission directly.
        |
        */

        $exportPermission = DB::table('permissions')
            ->where('name', 'reports.export')
            ->value('id');

        DB::table('permission_user')->insert([
            'user_id' => $supervisors[0],
            'permission_id' => $exportPermission,
            'granted' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | TEST SESSION
        |--------------------------------------------------------------------------
        */

        DB::table('sessions')->insert([
            'id' => 'seed-test-session',
            'user_id' => $ownerId,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Collex Seeder Test Session',
            'payload' => base64_encode(serialize([
                'seeded' => true,
            ])),
            'last_activity' => now()->timestamp,
        ]);
    }
}