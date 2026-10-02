<?php

namespace Database\Seeders\System;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FrameworkSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | CACHE
        |--------------------------------------------------------------------------
        */

        DB::table('cache')->insert([
            [
                'key' => 'collex:test:cache',
                'value' => 'test-value',
                'expiration' => now()->addHour()->timestamp,
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | CACHE LOCKS
        |--------------------------------------------------------------------------
        */

        DB::table('cache_locks')->insert([
            [
                'key' => 'collex:test:lock',
                'owner' => 'seed-owner',
                'expiration' => now()->addHour()->timestamp,
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | JOBS
        |--------------------------------------------------------------------------
        |
        | This is only test data.
        | It is NOT intended to execute a real queue job.
        |
        */

        DB::table('jobs')->insert([
            'queue' => 'default',
            'payload' => json_encode([
                'uuid' => (string) Str::uuid(),
                'displayName' => 'TestJob',
                'job' => 'Illuminate\Queue\CallQueuedHandler@call',
                'maxTries' => null,
                'timeout' => null,
                'data' => [],
            ]),
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => now()->timestamp,
            'created_at' => now()->timestamp,
        ]);

        /*
        |--------------------------------------------------------------------------
        | JOB BATCHES
        |--------------------------------------------------------------------------
        */

        DB::table('job_batches')->insert([
            'id' => (string) Str::uuid(),
            'name' => 'Test Collection Import',
            'total_jobs' => 10,
            'pending_jobs' => 0,
            'failed_jobs' => 0,
            'failed_job_ids' => json_encode([]),
            'options' => json_encode([
                'seeded' => true,
            ]),
            'cancelled_at' => null,
            'created_at' => now()->timestamp,
            'finished_at' => now()->timestamp,
        ]);

        /*
        |--------------------------------------------------------------------------
        | FAILED JOBS
        |--------------------------------------------------------------------------
        */

        DB::table('failed_jobs')->insert([
            'uuid' => (string) Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode([
                'displayName' => 'TestFailedJob',
            ]),
            'exception' => 'Seeded test exception.',
            'failed_at' => now(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | PASSWORD RESET TOKENS
        |--------------------------------------------------------------------------
        */

        DB::table('password_reset_tokens')->insert([
            'email' => 'test-reset@collex.test',
            'token' => hash('sha256', 'test-reset-token'),
            'created_at' => now(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | SESSIONS
        |--------------------------------------------------------------------------
        |
        | user_id will be filled after users exist.
        | Therefore we leave this table empty here.
        |
        */
    }
}