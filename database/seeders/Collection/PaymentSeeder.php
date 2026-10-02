<?php

namespace Database\Seeders\Collection;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $cases = DB::table('debt_cases')
            ->whereNotNull('assigned_user_id')
            ->limit(60)
            ->get();

        $promises = DB::table('promises_to_pay')
            ->orderBy('id')
            ->get()
            ->values();

        $supervisorId = DB::table('users')
            ->where('employee_code', 'SUP-0001')
            ->value('id');

        $methods = [
            'cash',
            'e_wallet',
            'bank_transfer',
            'card',
            'cheque',
        ];

        foreach ($cases as $index => $case) {

            $status = match ($index % 5) {
                0 => 'pending',
                1, 2, 3 => 'confirmed',
                default => 'rejected',
            };

            $amount = 500 + (($index * 300) % 4000);

            $promiseId = $promises->count()
                ? $promises[$index % $promises->count()]->id
                : null;

            DB::table('payments')->insert([
                'receipt_number' => sprintf(
                    'RCT-%06d',
                    $index + 1
                ),

                'debt_case_id' => $case->id,

                'collector_id' => $case->assigned_user_id,

                'promise_id' => $promiseId,

                'amount' => $amount,

                'method' => $methods[
                    $index % count($methods)
                ],

                'reference' =>
                    $index % 2 === 0
                        ? 'REF-' . str_pad($index + 1, 8, '0', STR_PAD_LEFT)
                        : null,

                'proof_path' => null,

                'paid_at' => now()->subDays($index % 10),

                'status' => $status,

                'confirmed_by' =>
                    $status === 'confirmed'
                        ? $supervisorId
                        : null,

                'confirmed_at' =>
                    $status === 'confirmed'
                        ? now()->subDays(1)
                        : null,

                'rejection_reason' =>
                    $status === 'rejected'
                        ? 'Test rejected payment.'
                        : null,

                'notes' => 'Seeded payment.',

                'created_at' => now(),
                'updated_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Only confirmed payments affect the debt case.
            |--------------------------------------------------------------------------
            */

            if ($status === 'confirmed') {
                DB::table('debt_cases')
                    ->where('id', $case->id)
                    ->increment(
                        'collected_amount',
                        $amount
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | Update PTP progress
            |--------------------------------------------------------------------------
            */

            if ($status === 'confirmed' && $promiseId) {
                DB::table('promises_to_pay')
                    ->where('id', $promiseId)
                    ->increment(
                        'paid_amount',
                        $amount
                    );
            }
        }
    }
}