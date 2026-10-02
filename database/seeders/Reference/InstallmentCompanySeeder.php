<?php

namespace Database\Seeders\Reference;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InstallmentCompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            [
                'name' => 'B.TECH',
                'code' => 'BTECH',
            ],
            [
                'name' => 'ValU',
                'code' => 'VALU',
            ],
            [
                'name' => 'Contact',
                'code' => 'CONTACT',
            ],
            [
                'name' => 'Sympl',
                'code' => 'SYMPL',
            ],
        ];

        foreach ($companies as $company) {
            DB::table('installment_companies')->insert([
                ...$company,
                'logo_path' => null,
                'is_active' => true,
                'notes' => 'Seeded installment company.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}