<?php

// app/Http/Controllers/BankArchiveController.php  (static data)
// "النطاقات المؤرشفة": closed monthly scopes (portfolios) of ONE bank. Read-only: nothing can be edited here.

namespace App\Http\Controllers;

use App\Support\StaticBanks;
use App\Support\StaticData;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class BankArchiveController extends Controller
{
    private const MONTHS = [1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل', 5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس', 9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'];

    /** Only bank #1 has archived scopes. TODO (DB): Portfolio::where('bank_id', ...)->where('status', 'archived') */
    private function archives(int $bankId): Collection
    {
        if ($bankId !== 1) {
            return collect();
        }

        // id, month, year, cases, total debt, collected, archived by, archived at, source file
        $rows = [
            [4, 8,  2026, 52, 301000, 118500, 'Test Account', '31/08/2026', 'scope_august.xlsx'],
            [3, 7,  2026, 47, 266000, 140200, 'Beshoy nople', '31/07/2026', 'scope_july.xlsx'],
            [2, 6,  2026, 41, 233000, 119000, 'Beshoy nople', '30/06/2026', 'scope_june.xlsx'],
            [1, 12, 2025, 40, 210000, 120000, 'Test Account', '31/12/2025', 'scope_december.xlsx'],
        ];

        return collect($rows)->map(function (array $r) {
            $a = (object) array_combine(['id', 'month', 'year', 'cases', 'debt', 'collected', 'by', 'at', 'file'], $r);

            $a->label     = self::MONTHS[$a->month] . ' ' . $a->year;
            $a->remaining = $a->debt - $a->collected;
            $a->rate      = $a->debt > 0 ? (int) round($a->collected / $a->debt * 100) : 0;

            return $a;
        });
    }

    // GET /banks/{bank}/archives?year=
    public function index(Request $request, int $bank): View
    {
        $bank = StaticBanks::find($bank);
        $all  = $this->archives($bank->id);
        $year = (string) $request->query('year', '');

        return view('banks.archives', [
            'bank'     => $bank,
            'archives' => $all->when($year !== '', fn (Collection $c) => $c->where('year', (int) $year))->values(),
            'year'     => $year,
            'years'    => $all->pluck('year')->unique()->sortDesc()->values(),
            'stats'    => [
                ['label' => 'أشهر مؤرشفة',        'value' => $all->count(),                                'unit' => null,  'icon' => 'fa-box-archive',      'color' => 'info'],
                ['label' => 'إجمالي المديونية',   'value' => number_format($all->sum('debt')),             'unit' => 'EGP', 'icon' => 'fa-wallet',           'color' => 'accent'],
                ['label' => 'إجمالي المحصل',      'value' => number_format($all->sum('collected')),        'unit' => 'EGP', 'icon' => 'fa-money-bill-wave',  'color' => 'brand'],
                ['label' => 'متوسط نسبة التحصيل', 'value' => ($all->isEmpty() ? 0 : (int) round($all->avg('rate'))) . '%', 'unit' => null, 'icon' => 'fa-bullseye', 'color' => 'cyan'],
            ],
        ]);
    }

    // GET /banks/{bank}/archives/{archive}?search=
    public function show(Request $request, int $bank, int $archive): View
    {
        $bank    = StaticBanks::find($bank);
        $current = $this->archives($bank->id)->firstWhere('id', $archive) ?? abort(404);
        $search  = trim((string) $request->query('search', ''));

        // final state of the cases when the scope was closed.  TODO (DB): debt_cases of this portfolio
        // client code => [collected, final status, employee]
        $final = [
            '1000001' => [1500, 'active', 'HOOL'],
            '1000002' => [0,    'legal',  'HOOL'],
            '1000003' => [3000, 'active', 'Ahmed Borlsy'],
            '1000004' => [1500, 'active', 'Ahmed Borlsy'],
            '1000005' => [7000, 'paid',   'Ahmed Borlsy'],
            '1000000' => [1200, 'active', 'HOOL'],
        ];

        $cases = StaticData::clients()->map(function ($client) use ($final) {
            [$collected, $status, $employee] = $final[$client->code];

            return (object) [
                'code' => $client->code, 'name' => $client->name, 'phone' => $client->phone,
                'debt' => 7000, 'collected' => $collected, 'remaining' => 7000 - $collected,
                'status' => $status, 'employee' => $employee,
            ];
        })->when($search !== '', function (Collection $c) use ($search) {
            $needle = mb_strtolower($search);

            return $c->filter(fn ($x) => str_contains(mb_strtolower($x->name . ' ' . $x->code), $needle));
        })->values();

        return view('banks.archive', [
            'bank'    => $bank,
            'archive' => $current,
            'cases'   => $cases,
            'search'  => $search,
        ]);
    }
}