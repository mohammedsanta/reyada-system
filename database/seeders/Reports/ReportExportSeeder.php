<?php

namespace Database\Seeders\Reports;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReportExportSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')
            ->where('status', 'active')
            ->pluck('id')
            ->values();

        $types = [
            'clients',
            'dcr',
            'performance',
            'payments',
            'cases',
        ];

        $formats = [
            'xlsx',
            'pdf',
            'csv',
        ];

        for ($i = 1; $i <= 15; $i++) {

            $status = match ($i % 3) {
                0 => 'completed',
                1 => 'pending',
                default => 'failed',
            };

            DB::table('report_exports')->insert([
                'user_id' => $users[
                    ($i - 1) % $users->count()
                ],

                'type' => $types[
                    ($i - 1) % count($types)
                ],

                'format' => $formats[
                    ($i - 1) % count($formats)
                ],

                'filters' => json_encode([
                    'bank_id' => null,
                    'period_year' => 2026,
                    'period_month' => 9,
                ]),

                'status' => $status,

                'file_path' =>
                    $status === 'completed'
                        ? "exports/report-{$i}.xlsx"
                        : null,

                'row_count' =>
                    $status === 'completed'
                        ? 100 + $i
                        : null,

                'error' =>
                    $status === 'failed'
                        ? 'Seeded test export failure.'
                        : null,

                'generated_at' =>
                    $status === 'completed'
                        ? now()->subHour()
                        : null,

                'expires_at' =>
                    $status === 'completed'
                        ? now()->addDays(7)
                        : null,

                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}