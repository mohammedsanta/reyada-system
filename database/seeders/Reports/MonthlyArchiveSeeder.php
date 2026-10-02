<?php

namespace Database\Seeders\Reports;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MonthlyArchiveSeeder extends Seeder
{
    public function run(): void
    {
        $ownerId = DB::table('users')
            ->where('employee_code', 'EMP-0001')
            ->value('id');

        $banks = DB::table('banks')
            ->get();

        foreach ($banks as $bank) {

            $portfolio = DB::table('portfolios')
                ->where('bank_id', $bank->id)
                ->where('period_year', 2026)
                ->where('period_month', 8)
                ->first();

            DB::table('monthly_archives')->insert([
                'bank_id' => $bank->id,

                'portfolio_id' => $portfolio?->id,

                'period_year' => 2026,
                'period_month' => 8,

                'cases_count' => 40,

                'total_debt' => 500000,

                'collected_amount' => 125000,

                'snapshot_path' =>
                    "archives/2026/08/bank-{$bank->id}.xlsx",

                'archived_by' => $ownerId,

                'archived_at' => now()->subMonth(),

                'notes' => 'Seeded monthly archive.',

                'created_at' => now()->subMonth(),
                'updated_at' => now(),
            ]);
        }
    }
}