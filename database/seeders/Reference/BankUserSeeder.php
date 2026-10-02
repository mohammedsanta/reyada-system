<?php

namespace Database\Seeders\Reference;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankUserSeeder extends Seeder
{
    public function run(): void
    {
        $ownerId = DB::table('users')
            ->where('employee_code', 'EMP-0001')
            ->value('id');

        $users = DB::table('users')
            ->where('is_system_account', false)
            ->where('status', 'active')
            ->pluck('id')
            ->values();

        $banks = DB::table('banks')
            ->pluck('id')
            ->values();

        foreach ($users as $index => $userId) {
            // Every employee gets at least one bank.
            $bankId = $banks[$index % $banks->count()];

            DB::table('bank_user')->insert([
                'bank_id' => $bankId,
                'user_id' => $userId,
                'assigned_by' => $ownerId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Give some employees a second bank.
            if ($index % 3 === 0 && $banks->count() > 1) {
                $secondBank = $banks[($index + 1) % $banks->count()];

                if ($secondBank !== $bankId) {
                    DB::table('bank_user')->insert([
                        'bank_id' => $secondBank,
                        'user_id' => $userId,
                        'assigned_by' => $ownerId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}