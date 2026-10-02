<?php

namespace Database\Seeders\Audit;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')
            ->pluck('id')
            ->values();

        $banks = DB::table('banks')
            ->pluck('id')
            ->values();

        $events = [
            'created',
            'updated',
            'deleted',
            'login',
            'logout',
            'imported',
            'exported',
            'assigned',
        ];

        for ($i = 1; $i <= 50; $i++) {

            DB::table('activity_logs')->insert([
                'user_id' => $users[
                    ($i - 1) % $users->count()
                ],

                'event' => $events[
                    ($i - 1) % count($events)
                ],

                'description' =>
                    "Seeded activity log #{$i}.",

                'subject_type' =>
                    'App\\Models\\Bank',

                'subject_id' =>
                    $banks[
                        ($i - 1) % $banks->count()
                    ],

                'properties' => json_encode([
                    'seeded' => true,
                    'test_id' => $i,
                ]),

                'ip_address' => '127.0.0.1',

                'user_agent' =>
                    'Collex Test Seeder',

                'created_at' =>
                    now()->subMinutes($i),
            ]);
        }
    }
}