<?php

declare(strict_types=1);

namespace App\Http\Controllers\Employee;

use App\Domain\Reports\Queries\GetEmployeePerformance;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class EmployeeDetailsController extends Controller
{
    public function __construct(
        private readonly GetEmployeePerformance $performanceQuery,
    ) {
    }

public function __invoke(
    Request $request,
    string $code
): View {
$user = User::query()
    ->with([
        'role',
        'supervisor',
        'banks',
        'promises.debtCase.client',
    ])
    ->where('employee_code', $code)
    ->employees()
    ->firstOrFail();

    $report = $this->performanceQuery->execute(
        user: $user,
        filters: [
            'month' => $request->input('month'),
            'year' => $request->input('year'),
        ],
    );

    $months = [
        1 => 'January',
        2 => 'February',
        3 => 'March',
        4 => 'April',
        5 => 'May',
        6 => 'June',
        7 => 'July',
        8 => 'August',
        9 => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December',
    ];

    $years = range(
        now()->year - 2,
        now()->year + 1
    );

    return view('employees.show', [
        'report' => $report,
        'user' => $user,
        'months' => $months,
        'years' => $years,
        'filters' => $report['filters'],
    ]);
}
    private function months(): array
    {
        return [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];
    }

    private function buildStats(array $report): array
    {
        return [
            [
                'label' => 'إجمالي التحصيل',
                'value' => number_format(
                    $report['performance']['total_collected']
                ),
                'unit' => 'EGP',
                'icon' => 'fa-money-bill-trend-up',
                'color' => 'brand',
            ],

            [
                'label' => 'تحقيق المستهدف',
                'value' => $report['performance']['target_achievement'] . '%',
                'unit' => 'إجمالي الفترة',
                'icon' => 'fa-bullseye',
                'color' => $report['performance']['performance_tone'],
            ],

            [
                'label' => 'إجمالي الحالات',
                'value' => number_format(
                    $report['performance']['total_cases']
                ),
                'unit' => 'حالة',
                'icon' => 'fa-folder-open',
                'color' => 'info',
            ],

            [
                'label' => 'نجاح الوعود',
                'value' => $report['promises']['success_rate'] . '%',
                'unit' => 'من الوعود المحسومة',
                'icon' => 'fa-handshake',
                'color' => 'warning',
            ],

            [
                'label' => 'الكفاءة',
                'value' => round(
                    $report['performance']['efficiency']
                ) . '%',
                'unit' => 'كفاءة التحصيل',
                'icon' => 'fa-gauge-high',
                'color' => $report['performance']['performance_tone'],
            ],
        ];
    }

    private function buildLabels(array $report): array
    {
        $count = $report['trend']['collected']->count();

        return collect(range(1, $count))
            ->map(
                fn (int $index): string => 'الفترة ' . $index
            )
            ->values()
            ->all();
    }
}