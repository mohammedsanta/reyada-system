<?php

namespace Database\Seeders\Collection;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PromiseToPaySeeder extends Seeder
{
    public function run(): void
    {
        $cases = DB::table('debt_cases')
            ->whereNotNull('assigned_user_id')
            ->limit(80)
            ->get();

        foreach ($cases as $index => $case) {

            $promisedAmount = 1000 + (($index * 250) % 5000);

            $status = match ($index % 5) {
                0 => 'kept',
                1 => 'partial',
                2 => 'broken',
                3 => 'review',
                default => 'active',
            };

            $paidAmount = match ($status) {
                'kept' => $promisedAmount,
                'partial' => round($promisedAmount / 2, 2),
                default => 0,
            };

            DB::table('promises_to_pay')->insert([
                'debt_case_id' => $case->id,
                'user_id' => $case->assigned_user_id,

                'promised_amount' => $promisedAmount,
                'paid_amount' => $paidAmount,

                'promise_date' => now()
                    ->subDays(5 - ($index % 10))
                    ->toDateString(),

                'status' => $status,

                'notes' => 'Seeded promise to pay.',

                'reviewed_by' => $status !== 'active'
                    ? DB::table('users')
                        ->where('employee_code', 'SUP-0001')
                        ->value('id')
                    : null,

                'reviewed_at' => $status !== 'active'
                    ? now()->subDays(1)
                    : null,

                'closed_at' => in_array($status, [
                    'kept',
                    'partial',
                    'broken',
                ])
                    ? now()->subDays(1)
                    : null,

                'created_at' => now()->subDays(5),
                'updated_at' => now(),
            ]);
        }
    }
}