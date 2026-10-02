<?php

namespace Database\Seeders\Collection;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DebtCaseSeeder extends Seeder
{
    public function run(): void
    {
        $portfolios = DB::table('portfolios')
            ->where('status', 'active')
            ->get();

        $clients = DB::table('clients')
            ->pluck('id')
            ->values();

        $loanTypes = DB::table('loan_types')
            ->pluck('id')
            ->values();

        $collectors = DB::table('users')
            ->where('status', 'active')
            ->where('is_system_account', false)
            ->whereIn('role_id', function ($query) {
                $query->select('id')
                    ->from('roles')
                    ->where('name', 'call_center');
            })
            ->pluck('id')
            ->values();

        $caseId = 1;

        foreach ($portfolios as $portfolio) {

            $portfolioTotal = 0;
            $portfolioCases = 0;

            for ($i = 1; $i <= 40; $i++) {

                $clientId = $clients[
                    ($caseId - 1) % $clients->count()
                ];

                $assignedUserId = $collectors[
                    ($caseId - 1) % $collectors->count()
                ];

                $totalDebt = 5000 + (($caseId * 137) % 45000);

                $overdue = round($totalDebt * 0.65, 2);

                $collected = ($caseId % 5 === 0)
                    ? round($overdue * 0.25, 2)
                    : 0;

                $status = match ($caseId % 10) {
                    0 => 'paid',
                    1 => 'legal',
                    2 => 'inactive',
                    default => 'active',
                };

                $processed = $caseId % 3 === 0;

                DB::table('debt_cases')->insert([
                    'portfolio_id' => $portfolio->id,
                    'bank_id' => $portfolio->bank_id,
                    'client_id' => $clientId,
                    'loan_type_id' => $loanTypes[
                        ($caseId - 1) % $loanTypes->count()
                    ],
                    'assigned_user_id' => $assignedUserId,

                    'loan_number' => sprintf(
                        'LN-%06d',
                        $caseId
                    ),

                    'status' => $status,

                    'total_debt' => $totalDebt,
                    'overdue_amount' => $overdue,
                    'installment_value' => round($totalDebt / 24, 2),
                    'min_installment_diff' => round($totalDebt / 48, 2),
                    'late_fee' => round($totalDebt * 0.02, 2),
                    'collected_amount' => $collected,

                    'bucket' => $caseId % 5,
                    'dpd' => ($caseId % 5) * 30,

                    'next_due_date' => now()->addDays(
                        ($caseId % 30) + 1
                    )->toDateString(),

                    'loan_start_date' => now()->subMonths(12)->toDateString(),

                    'loan_end_date' => now()->addMonths(12)->toDateString(),

                    'last_payment_date' => $collected > 0
                        ? now()->subDays(10)->toDateString()
                        : null,

                    'last_payment_amount' => $collected > 0
                        ? $collected
                        : null,

                    'is_processed' => $processed,

                    'processed_at' => $processed
                        ? now()->subDays(2)
                        : null,

                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $portfolioCases++;
                $portfolioTotal += $totalDebt;
                $caseId++;
            }

            /*
            |--------------------------------------------------------------------------
            | Update portfolio counters
            |--------------------------------------------------------------------------
            */

            DB::table('portfolios')
                ->where('id', $portfolio->id)
                ->update([
                    'cases_count' => $portfolioCases,
                    'total_debt' => $portfolioTotal,
                    'updated_at' => now(),
                ]);
        }
    }
}