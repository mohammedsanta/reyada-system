<?php

namespace Database\Seeders\Portfolios;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PortfolioImportSeeder extends Seeder
{
    public function run(): void
    {
        $ownerId = DB::table('users')
            ->where('employee_code', 'EMP-0001')
            ->value('id');

        $portfolios = DB::table('portfolios')->get();

        foreach ($portfolios as $portfolio) {
            DB::table('portfolio_imports')->insert([
                'portfolio_id' => $portfolio->id,
                'imported_by' => $ownerId,
                'original_filename' => "portfolio_{$portfolio->period_year}_{$portfolio->period_month}.xlsx",
                'stored_path' => "portfolio-imports/portfolio_{$portfolio->id}.xlsx",
                'status' => 'completed',
                'total_rows' => 100,
                'success_rows' => 98,
                'failed_rows' => 2,
                'errors' => json_encode([
                    [
                        'row' => 17,
                        'message' => 'Invalid phone number.',
                    ],
                    [
                        'row' => 64,
                        'message' => 'Missing loan number.',
                    ],
                ]),
                'started_at' => now()->subMinutes(20),
                'finished_at' => now()->subMinutes(15),
                'created_at' => now()->subMinutes(20),
                'updated_at' => now()->subMinutes(15),
            ]);
        }
    }
}