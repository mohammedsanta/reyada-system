<?php

// app/Http/Controllers/EmployeeDetailsController.php  (static data)
// Details of ONE employee's performance (opened from the ranking page and from the team page).

namespace App\Http\Controllers;

use App\Support\StaticData;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeDetailsController extends Controller
{
    private const MONTHS = [
        1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل', 5 => 'مايو', 6 => 'يونيو',
        7 => 'يوليو', 8 => 'أغسطس', 9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
    ];

    // GET /employees/{code}?month=&year=      ({code} = employee code, e.g. 85392)
    public function __invoke(Request $request, string $code): View
    {
        $user = StaticData::users()->firstWhere('code', $code) ?? abort(404);
        abort_if($user->is_system, 404);

        // TODO (DB): performance_snapshots + promises_to_pay + payments of this user
        $profiles = [
            3 => [ // HOOL
                'cases' => 3, 'collected' => 4200, 'target' => 15000, 'efficiency' => 31,
                'promises' => ['kept' => 1, 'partial' => 1, 'broken' => 1, 'active' => 1],
                'trend' => ['collected' => [3, 2, 4, 3, 5, 4.2], 'target' => [10, 10, 12, 12, 15, 15]],
                'recent' => [['حسن زينب الهواري', 2000, 'اليوم', 'active'], ['فاطمة زينب السيد', 1000, 'قبل 6 أيام', 'partial'], ['حسن زينب الهواري', 3000, 'قبل 10 أيام', 'broken']],
            ],
            4 => [ // Ahmed Borlsy
                'cases' => 3, 'collected' => 15400, 'target' => 20000, 'efficiency' => 68,
                'promises' => ['kept' => 2, 'partial' => 0, 'broken' => 0, 'active' => 1],
                'trend' => ['collected' => [8, 10, 12, 9, 14, 15.4], 'target' => [12, 12, 15, 15, 18, 20]],
                'recent' => [['منى هبة صالح', 1500, 'أمس', 'active'], ['إيمان هبة راضي', 1500, 'بعد 5 أيام', 'review'], ['أحمد يوسف توفيق', 1500, 'قبل 3 أيام', 'kept']],
            ],
        ];

        $profile = $profiles[$user->id] ?? [
            'cases' => 0, 'collected' => 0, 'target' => 0, 'efficiency' => 0,
            'promises' => ['kept' => 0, 'partial' => 0, 'broken' => 0, 'active' => 0],
            'trend' => ['collected' => [0, 0, 0, 0, 0, 0], 'target' => [0, 0, 0, 0, 0, 0]],
            'recent' => [],
        ];

        $achievement = $profile['target'] > 0 ? (int) round($profile['collected'] / $profile['target'] * 100) : 0;
        $tone        = $profile['efficiency'] >= 70 ? 'brand' : ($profile['efficiency'] >= 40 ? 'warning' : 'danger');

        return view('employees.show', [
            'user'    => $user,
            'profile' => $profile,
            'tone'    => $tone,
            'stats'   => [
                ['label' => 'الحالات',               'value' => $profile['cases'],                              'unit' => null,  'icon' => 'fa-briefcase',       'color' => 'info'],
                ['label' => 'وعود الدفع الناجحة',    'value' => $profile['promises']['kept'],                   'unit' => null,  'icon' => 'fa-circle-check',    'color' => 'orange'],
                ['label' => 'إجمالي التحصيل',        'value' => number_format($profile['collected']),           'unit' => 'EGP', 'icon' => 'fa-money-bill-wave', 'color' => 'brand'],
                ['label' => 'تحقيق المستهدف',        'value' => $achievement . '%',                             'unit' => null,  'icon' => 'fa-bullseye',        'color' => 'accent'],
                ['label' => 'مؤشر الكفاءة',          'value' => $profile['efficiency'] . '%',                   'unit' => null,  'icon' => 'fa-bolt',            'color' => 'cyan'],
            ],
            'banks'   => collect($user->banks)->map(fn ($name) => (object) [
                'name' => $name, 'cases' => $profile['cases'], 'collected' => $profile['collected'],
                'kept' => $profile['promises']['kept'], 'broken' => $profile['promises']['broken'], 'efficiency' => $profile['efficiency'],
            ]),
            'months'  => self::MONTHS,
            'years'   => array_combine(range(now()->year - 2, now()->year), range(now()->year - 2, now()->year)),
            'filters' => ['month' => (int) $request->query('month', now()->month), 'year' => (int) $request->query('year', now()->year)],
            'labels'  => ['Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'],
        ]);
    }
}