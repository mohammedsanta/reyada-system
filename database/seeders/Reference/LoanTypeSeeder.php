<?php

namespace Database\Seeders\Reference;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoanTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            'Personal Loan',
            'Credit Card',
            'Auto Loan',
            'Consumer Finance',
            'Home Loan',
            'Business Loan',
            'Salary Loan',
        ];

        foreach ($types as $type) {
            DB::table('loan_types')->insert([
                'name' => $type,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}