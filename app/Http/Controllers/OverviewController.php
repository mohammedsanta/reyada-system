<?php

// app/Http/Controllers/OverviewController.php  (static data)
// "نظرة عامة": analysis of the PORTFOLIO (what we hold and how it is made up).
// The dashboard shows what needs doing today; this page shows how the portfolio looks.

namespace App\Http\Controllers;

use App\Support\StaticBanks;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OverviewController extends Controller
{
    // GET /overview?bank=
    public function __invoke(Request $request): View
    {
        // bank id => cases, portfolio, collected (this month).  TODO (DB): group debt_cases / payments by bank
        $figures = [1 => [6, 42000, 15400], 2 => [48, 310000, 98000], 3 => [31, 205000, 61500], 4 => [0, 0, 0], 5 => [12, 95000, 21000]];

        $allBanks = StaticBanks::all()->map(function ($bank) use ($figures) {
            [$bank->cases, $bank->portfolio, $bank->collected] = $figures[$bank->id];
            $bank->rate = $bank->portfolio > 0 ? (int) round($bank->collected / $bank->portfolio * 100) : 0;
            $bank->url  = route('banks.panel', $bank->id);

            return $bank;
        });

        // optional filter: one bank only
        $selected = (int) $request->query('bank', 0);
        $banks    = $selected > 0 ? $allBanks->where('id', $selected)->values() : $allBanks->sortByDesc('portfolio')->values();

        $cases     = $banks->sum('cases');
        $portfolio = $banks->sum('portfolio');
        $collected = $banks->sum('collected');
        $rate      = $portfolio > 0 ? round($collected / $portfolio * 100, 1) : 0;

        // the chosen bank's share of the whole portfolio: used to scale the sample breakdowns below
        $share = $allBanks->sum('portfolio') > 0 ? $portfolio / $allBanks->sum('portfolio') : 0;

        // sample breakdowns (percentages). TODO (DB): group by loan_type_id / governorate_id / bucket / dpd
        $loanTypes = [['قرض شخصي', 45], ['تمويل عقاري', 35], ['قرض سلع معمرة', 20]];
        $governorates = [['القاهرة', 24], ['الجيزة', 14], ['الإسكندرية', 11], ['الدقهلية', 9], ['أسيوط', 7], ['أخرى', 35]];
        $aging = [['0 - 30 يوم', 38, 'bg-brand'], ['31 - 60 يوم', 29, 'bg-info'], ['61 - 90 يوم', 20, 'bg-warning'], ['أكثر من 90 يوم', 13, 'bg-danger']];

        return view('overview.index', [
            'selected' => $selected,
            'bankOptions' => $allBanks->pluck('name', 'id')->all(),
            'banks'    => $banks,
            'stats'    => [
                ['label' => 'إجمالي المحفظة',   'value' => number_format($portfolio),                                       'unit' => 'EGP', 'icon' => 'fa-wallet',          'color' => 'info'],
                ['label' => 'إجمالي المحصل',    'value' => number_format($collected),                                       'unit' => 'EGP', 'icon' => 'fa-money-bill-wave', 'color' => 'brand'],
                ['label' => 'نسبة التحصيل',     'value' => $rate . '%',                                                     'unit' => null,  'icon' => 'fa-bullseye',        'color' => 'accent'],
                ['label' => 'عدد الحالات',      'value' => $cases,                                                          'unit' => null,  'icon' => 'fa-briefcase',       'color' => 'cyan'],
                ['label' => 'متوسط المديونية',  'value' => $cases > 0 ? number_format($portfolio / $cases) : 0,             'unit' => 'EGP', 'icon' => 'fa-scale-balanced',  'color' => 'orange'],
            ],
            'trend' => [
                'labels'    => ['Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'],
                'collected' => array_map(fn ($v) => round($v * $share, 1), [120, 138, 151, 164, 178, 195.9]),
                'target'    => array_map(fn ($v) => round($v * $share, 1), [150, 160, 170, 190, 210, 240]),
            ],
            'bucket'    => ['labels' => ['الشريحة 0', 'الشريحة 1', 'الشريحة 2', 'الشريحة 3+'], 'data' => [12, 35, 40, 13]],
            'loanTypes' => ['labels' => array_column($loanTypes, 0), 'data' => array_map(fn ($r) => (int) round($portfolio * $r[1] / 100), $loanTypes)],
            'govs'      => ['labels' => array_column($governorates, 0), 'data' => array_map(fn ($r) => (int) round($portfolio * $r[1] / 100), $governorates)],
            'aging'     => array_map(fn ($r) => (object) [
                'label' => $r[0], 'percent' => $r[1], 'amount' => (int) round($portfolio * $r[1] / 100),
                'cases' => (int) round($cases * $r[1] / 100), 'bar' => $r[2],
            ], $aging),
            'totals' => ['cases' => $cases, 'portfolio' => $portfolio, 'collected' => $collected],
        ]);
    }
}