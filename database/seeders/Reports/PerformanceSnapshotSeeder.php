<?php

namespace Database\Seeders\Reports;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PerformanceSnapshotSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')
            ->where('status', 'active')
            ->where('is_system_account', false)
            ->pluck('id')
            ->values();

        $banks = DB::table('banks')
            ->pluck('id')
            ->values();

        foreach ($users as $index => $userId) {

            $bankId = $banks[
                $index % $banks->count()
            ];

            $efficiency = 50 + ($index % 50);

            DB::table('performance_snapshots')->insert([
                'user_id' => $userId,
                'bank_id' => $bankId,

                'period_year' => 2026,
                'period_month' => 9,

                'cases_assigned' => 30 + $index,
                'cases_processed' => 20 + $index,
                'promises_total' => 10 + ($index % 10),
                'promises_kept' => 5 + ($index % 5),
                'promises_broken' => 1 + ($index % 3),

                'collected_amount' => 10000 + ($index * 750),

                'target_amount' => 20000,

                'efficiency' => $efficiency,

                'rank_position' => $index + 1,

                'calculated_at' => now(),

                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}