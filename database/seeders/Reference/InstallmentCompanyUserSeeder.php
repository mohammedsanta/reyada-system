<?php

namespace Database\Seeders\Reference;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InstallmentCompanyUserSeeder extends Seeder
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

        $companies = DB::table('installment_companies')
            ->pluck('id')
            ->values();

        foreach ($users as $index => $userId) {
            $companyId = $companies[$index % $companies->count()];

            DB::table('installment_company_user')->insert([
                'installment_company_id' => $companyId,
                'user_id' => $userId,
                'assigned_by' => $ownerId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}