<?php

namespace Database\Seeders\Portfolios;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PortfolioSeeder extends Seeder
{
    public function run(): void
    {
        $ownerId = DB::table('users')
            ->where('employee_code', 'EMP-0001')
            ->value('id');

        $banks = DB::table('banks')
            ->select('id', 'name')
            ->get();

        foreach ($banks as $bank) {

            /*
            |--------------------------------------------------------------------------
            | August = archived
            |--------------------------------------------------------------------------
            */

            DB::table('portfolios')->insert([
                'bank_id' => $bank->id,
                'name' => "{$bank->name} - August 2026",
                'period_year' => 2026,
                'period_month' => 8,
                'status' => 'archived',
                'cases_count' => 0,
                'total_debt' => 0,
                'created_by' => $ownerId,
                'activated_at' => now()->subMonths(2),
                'archived_at' => now()->subMonth(),
                'archived_by' => $ownerId,
                'notes' => 'Seeded archived portfolio.',
                'created_at' => now()->subMonths(2),
                'updated_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | September = active
            |--------------------------------------------------------------------------
            */

            DB::table('portfolios')->insert([
                'bank_id' => $bank->id,
                'name' => "{$bank->name} - September 2026",
                'period_year' => 2026,
                'period_month' => 9,
                'status' => 'active',
                'cases_count' => 0,
                'total_debt' => 0,
                'created_by' => $ownerId,
                'activated_at' => now()->subMonth(),
                'archived_at' => null,
                'archived_by' => null,
                'notes' => 'Seeded active portfolio.',
                'created_at' => now()->subMonth(),
                'updated_at' => now(),
            ]);
        }
    }
}