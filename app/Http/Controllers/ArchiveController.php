<?php

// app/Http/Controllers/ArchiveController.php  (static data)
// "المستودعات الشهرية": frozen monthly summaries of EVERY bank, newest first.
// (The archived scopes of ONE bank are on the bank's own page: BankArchiveController.)

namespace App\Http\Controllers;

use App\Support\StaticBanks;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class ArchiveController extends Controller
{
    private const MONTHS = [1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل', 5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس', 9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'];

    /** TODO (DB): MonthlyArchive::with('bank')->latest('archived_at') */
    private function archives(): Collection
    {
        // id, bank id, month, year, cases, total debt, collected, archived by, archived at
        // (ids of bank #1 are the same ones used by its own archive pages)
        $rows = [
            [4, 1, 8,  2026, 52, 301000, 118500, 'Test Account', '31/08/2026'],
            [6, 2, 8,  2026, 48, 280000, 99000,  'Test Account', '31/08/2026'],
            [3, 1, 7,  2026, 47, 266000, 140200, 'Beshoy nople', '31/07/2026'],
            [7, 3, 7,  2026, 31, 205000, 88400,  'Beshoy nople', '31/07/2026'],
            [1, 1, 12, 2025, 40, 210000, 120000, 'Test Account', '31/12/2025'],
        ];

        $banks = StaticBanks::all()->keyBy('id');

        return collect($rows)->map(function (array $r) use ($banks) {
            $a = (object) array_combine(['id', 'bank_id', 'month', 'year', 'cases', 'debt', 'collected', 'by', 'at'], $r);

            $a->bank        = $banks[$a->bank_id]->name;
            $a->month_label = self::MONTHS[$a->month] . ' ' . $a->year;
            $a->rate        = $a->debt > 0 ? (int) round($a->collected / $a->debt * 100) : 0;

            // only bank #1 has detail pages in this static version; the others open the bank's archive list
            $a->url = ! Route::has('banks.archives.show') ? '#' : ($a->bank_id === 1
                ? route('banks.archives.show', [$a->bank_id, $a->id])
                : route('banks.archives.index', $a->bank_id));
            $a->bank_url = route('banks.panel', $a->bank_id);

            return $a;
        });
    }

    // GET /archives?year=&bank=
    public function index(Request $request): View
    {
        $all     = $this->archives();
        $filters = ['year' => (string) $request->query('year', ''), 'bank' => (string) $request->query('bank', '')];

        $archives = $all
            ->when($filters['year'] !== '', fn (Collection $c) => $c->where('year', (int) $filters['year']))
            ->when($filters['bank'] !== '', fn (Collection $c) => $c->where('bank_id', (int) $filters['bank']))
            ->values();

        return view('archives.index', [
            'archives' => $archives,
            'filters'  => $filters,
            'years'    => $all->pluck('year')->unique()->sortDesc()->values(),
            'banks'    => StaticBanks::all()->pluck('name', 'id'),
            'stats'    => [
                ['label' => 'عدد الأرشيفات',      'value' => $archives->count(),                             'unit' => null,  'icon' => 'fa-box-archive',     'color' => 'info'],
                ['label' => 'إجمالي المديونية',   'value' => number_format($archives->sum('debt')),          'unit' => 'EGP', 'icon' => 'fa-wallet',          'color' => 'accent'],
                ['label' => 'إجمالي المحصل',      'value' => number_format($archives->sum('collected')),     'unit' => 'EGP', 'icon' => 'fa-money-bill-wave', 'color' => 'brand'],
                ['label' => 'متوسط نسبة التحصيل', 'value' => ($archives->isEmpty() ? 0 : (int) round($archives->avg('rate'))) . '%', 'unit' => null, 'icon' => 'fa-bullseye', 'color' => 'cyan'],
            ],
        ]);
    }
}