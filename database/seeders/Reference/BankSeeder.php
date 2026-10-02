<?php

namespace Database\Seeders\Reference;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankSeeder extends Seeder
{
    public function run(): void
    {
        $banks = [
            [
                'name' => 'Banque Misr',
                'code' => 'BM',
                'sector' => 'قطاع العملاء',
            ],
            [
                'name' => 'National Bank of Egypt',
                'code' => 'NBE',
                'sector' => 'قطاع العملاء',
            ],
            [
                'name' => 'Commercial International Bank',
                'code' => 'CIB',
                'sector' => 'قطاع العملاء',
            ],
            [
                'name' => 'QNB Alahli',
                'code' => 'QNB',
                'sector' => 'قطاع العملاء',
            ],
            [
                'name' => 'AlexBank',
                'code' => 'ALEX',
                'sector' => 'قطاع العملاء',
            ],
        ];

        foreach ($banks as $bank) {
            DB::table('banks')->insert([
                ...$bank,
                'logo_path' => null,
                'is_active' => true,
                'notes' => 'Seeded test bank.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}