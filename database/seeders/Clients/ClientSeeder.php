<?php

namespace Database\Seeders\Clients;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('en_US');

        $governorates = DB::table('governorates')
            ->pluck('id')
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Create 150 clients
        |--------------------------------------------------------------------------
        */

        for ($i = 1; $i <= 150; $i++) {
            DB::table('clients')->insert([
                'code' => sprintf('CLI-%06d', $i),

                // Exactly 14 digits.
                'national_id' => '2900101' . str_pad($i, 7, '0', STR_PAD_LEFT),

                'name' => $faker->name(),

                'email' => "client{$i}@example.test",

                'governorate_id' => $governorates[
                    ($i - 1) % $governorates->count()
                ],

                'address' => $faker->streetAddress(),

                'employer_name' => $faker->company(),

                'job_title' => $faker->jobTitle(),

                'work_address' => $faker->streetAddress(),

                'notes' => 'Seeded test client.',

                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}