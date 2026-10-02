<?php

namespace Database\Seeders\Audit;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')
            ->where('status', 'active')
            ->pluck('id')
            ->values();

        for ($i = 1; $i <= 10; $i++) {

            DB::table('notifications')->insert([
                'id' => (string) Str::uuid(),

                'type' =>
                    'App\\Notifications\\CollectionNotification',

                'notifiable_type' =>
                    'App\\Models\\User',

                'notifiable_id' =>
                    $users[
                        ($i - 1) % $users->count()
                    ],

                'data' => json_encode([
                    'title' => 'New Collection Activity',
                    'message' =>
                        "Test notification #{$i}",
                    'link' => '/notifications',
                ]),

                'read_at' =>
                    $i % 3 === 0
                        ? now()->subHour()
                        : null,

                'created_at' =>
                    now()->subMinutes($i),

                'updated_at' =>
                    now()->subMinutes($i),
            ]);
        }
    }
}