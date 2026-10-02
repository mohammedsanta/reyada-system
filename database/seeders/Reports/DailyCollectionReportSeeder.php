<?php

namespace Database\Seeders\Reports;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DailyCollectionReportSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')
            ->where('status', 'active')
            ->where('is_system_account', false)
            ->whereIn('role_id', function ($query) {
                $query->select('id')
                    ->from('roles')
                    ->where('name', 'call_center');
            })
            ->pluck('id')
            ->values();

        $banks = DB::table('banks')
            ->pluck('id')
            ->values();

        $supervisorId = DB::table('users')
            ->where('employee_code', 'SUP-0001')
            ->value('id');

        $counter = 1;

        foreach ($users as $userId) {

            $bankId = $banks[
                ($counter - 1) % $banks->count()
            ];

            $status = match ($counter % 3) {
                0 => 'approved',
                1 => 'submitted',
                default => 'draft',
            };

            DB::table('daily_collection_reports')->insert([
                'bank_id' => $bankId,
                'user_id' => $userId,
                'report_date' => now()
                    ->subDays($counter % 7)
                    ->toDateString(),

                'cases_worked' => 15 + $counter,
                'calls_count' => 20 + $counter,
                'visits_count' => 3 + ($counter % 5),
                'promises_count' => 2 + ($counter % 6),
                'promised_amount' => 5000 + ($counter * 250),
                'collected_amount' => 2500 + ($counter * 150),

                'status' => $status,

                'submitted_at' =>
                    $status !== 'draft'
                        ? now()->subHours(5)
                        : null,

                'approved_by' =>
                    $status === 'approved'
                        ? $supervisorId
                        : null,

                'approved_at' =>
                    $status === 'approved'
                        ? now()->subHours(3)
                        : null,

                'notes' => 'Seeded DCR.',

                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $counter++;
        }
    }
}