<?php

namespace Database\Seeders\Complaints;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComplaintSeeder extends Seeder
{
    public function run(): void
    {
        $banks = DB::table('banks')
            ->pluck('id')
            ->values();

        $cases = DB::table('debt_cases')
            ->get()
            ->values();

        $users = DB::table('users')
            ->where('status', 'active')
            ->where('is_system_account', false)
            ->pluck('id')
            ->values();

        $sources = [
            'phone',
            'whatsapp',
            'email',
            'bank',
            'visit',
        ];

        $priorities = [
            'low',
            'medium',
            'high',
            'urgent',
        ];

        $statuses = [
            'open',
            'in_review',
            'resolved',
            'rejected',
            'closed',
        ];

        for ($i = 1; $i <= 25; $i++) {

            $case = $cases[($i - 1) % $cases->count()];

            $status = $statuses[
                ($i - 1) % count($statuses)
            ];

            DB::table('complaints')->insert([
                'reference_number' =>
                    sprintf('CMP-%06d', $i),

                'bank_id' => $case->bank_id,

                'debt_case_id' => $case->id,

                'client_id' => $case->client_id,

                'logged_by' => $users[
                    ($i - 1) % $users->count()
                ],

                'assigned_to' => $users[
                    $i % $users->count()
                ],

                'subject' => "Test Complaint {$i}",

                'description' =>
                    'This is a seeded test complaint.',

                'source' => $sources[
                    ($i - 1) % count($sources)
                ],

                'priority' => $priorities[
                    ($i - 1) % count($priorities)
                ],

                'status' => $status,

                'due_at' => now()->addDays(3),

                'resolution' =>
                    in_array($status, [
                        'resolved',
                        'closed',
                    ])
                        ? 'Complaint resolved during test seeding.'
                        : null,

                'resolved_by' =>
                    in_array($status, [
                        'resolved',
                        'closed',
                    ])
                        ? $users[0]
                        : null,

                'resolved_at' =>
                    in_array($status, [
                        'resolved',
                        'closed',
                    ])
                        ? now()->subDay()
                        : null,

                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}