<?php

namespace Database\Seeders\Collection;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VisitSeeder extends Seeder
{
    public function run(): void
    {
        $cases = DB::table('debt_cases')
            ->whereNotNull('assigned_user_id')
            ->limit(50)
            ->get();

        $supervisorId = DB::table('users')
            ->where('employee_code', 'SUP-0001')
            ->value('id');

        $statuses = [
            'scheduled',
            'completed',
            'missed',
            'cancelled',
        ];

        $outcomes = [
            'client_found',
            'not_home',
            'refused',
            'promised',
            'paid',
        ];

        foreach ($cases as $index => $case) {

            $status = $statuses[
                $index % count($statuses)
            ];

            DB::table('visits')->insert([
                'debt_case_id' => $case->id,

                'user_id' => $case->assigned_user_id,

                'assigned_by' => $supervisorId,

                'status' => $status,

                'scheduled_at' => now()->addDays(
                    $index % 7
                ),

                'visited_at' =>
                    $status === 'completed'
                        ? now()->subDays(1)
                        : null,

                'address' =>
                    'Client address - seeded test data',

                'latitude' => 30.0444000,

                'longitude' => 31.2357000,

                'outcome' =>
                    $status === 'completed'
                        ? $outcomes[$index % count($outcomes)]
                        : null,

                'notes' => 'Seeded visit.',

                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}