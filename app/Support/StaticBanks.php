<?php

// app/Support/StaticBanks.php
// ONE shared source of static banks until the database is ready.
// TODO (DB): delete this file and use the Bank model.

namespace App\Support;

use Illuminate\Support\Collection;

class StaticBanks
{
    public static function all(): Collection
    {
        return collect([
            ['id' => 1, 'name' => 'Emirates NBD',        'code' => 'ENBD', 'is_active' => true,  'logo' => null],
            ['id' => 2, 'name' => 'بنك مصر',             'code' => 'BM',   'is_active' => true,  'logo' => null],
            ['id' => 3, 'name' => 'البنك التجاري الدولي', 'code' => 'CIB',  'is_active' => true,  'logo' => null],
            ['id' => 4, 'name' => 'بنك القاهرة',          'code' => 'BDC',  'is_active' => false, 'logo' => null],
            ['id' => 5, 'name' => 'بنك فيصل الإسلامي',    'code' => 'FIB',  'is_active' => true,  'logo' => null],
        ])->map(fn (array $bank) => (object) $bank);
    }

    public static function find(int $id): object
    {
        return static::all()->firstWhere('id', $id) ?? abort(404);
    }
}