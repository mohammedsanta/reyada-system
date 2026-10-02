<?php

namespace Database\Seeders\Clients;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientPhoneSeeder extends Seeder
{
    public function run(): void
    {
        $clients = DB::table('clients')
            ->select('id')
            ->get();

        foreach ($clients as $index => $client) {
            $base = 1000000000 + ($index * 10);

            DB::table('client_phones')->insert([
                [
                    'client_id' => $client->id,
                    'phone' => '010' . str_pad($base, 8, '0', STR_PAD_LEFT),
                    'label' => 'primary',
                    'is_valid' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'client_id' => $client->id,
                    'phone' => '011' . str_pad($base + 1, 8, '0', STR_PAD_LEFT),
                    'label' => 'alternate',
                    'is_valid' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'client_id' => $client->id,
                    'phone' => '012' . str_pad($base + 2, 8, '0', STR_PAD_LEFT),
                    'label' => 'work',
                    'is_valid' => $index % 10 !== 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }
}