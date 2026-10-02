<?php

namespace Database\Seeders\Collection;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CaseInteractionSeeder extends Seeder
{
    public function run(): void
    {
        $cases = DB::table('debt_cases')
            ->whereNotNull('assigned_user_id')
            ->limit(150)
            ->get();

        $types = [
            'call',
            'whatsapp',
            'sms',
            'email',
            'visit',
            'note',
        ];

        $outcomes = [
            'answered',
            'no_answer',
            'wrong_number',
            'refused',
            'promised',
            'paid',
        ];

        foreach ($cases as $index => $case) {

            $phoneId = DB::table('client_phones')
                ->where('client_id', $case->client_id)
                ->value('id');

            DB::table('case_interactions')->insert([
                'debt_case_id' => $case->id,
                'user_id' => $case->assigned_user_id,
                'client_phone_id' => $phoneId,

                'type' => $types[$index % count($types)],

                'outcome' => $outcomes[
                    $index % count($outcomes)
                ],

                'notes' => 'Seeded collection interaction.',

                'duration_seconds' =>
                    $index % 2 === 0
                        ? 120 + ($index * 5)
                        : null,

                'occurred_at' => now()->subDays($index % 7),

                'followup_at' =>
                    $index % 4 === 0
                        ? now()->addDays(2)
                        : null,

                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}