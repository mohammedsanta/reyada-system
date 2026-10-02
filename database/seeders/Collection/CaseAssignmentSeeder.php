<?php

namespace Database\Seeders\Collection;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CaseAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $supervisorId = DB::table('users')
            ->where('employee_code', 'SUP-0001')
            ->value('id');

        $cases = DB::table('debt_cases')
            ->whereNotNull('assigned_user_id')
            ->get();

        foreach ($cases as $case) {
            DB::table('case_assignments')->insert([
                'debt_case_id' => $case->id,
                'user_id' => $case->assigned_user_id,
                'assigned_by' => $supervisorId,
                'assigned_at' => now()->subDays(5),
                'unassigned_at' => null,
                'reason' => 'Initial portfolio assignment.',
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ]);
        }
    }
}