<?php

declare(strict_types=1);

namespace App\Domain\Reports\Queries;

use App\Models\User;
use Illuminate\Support\Collection;

final class GetEmployeePerformance
{
    private const MONTHS = [
        1  => 'يناير',
        2  => 'فبراير',
        3  => 'مارس',
        4  => 'أبريل',
        5  => 'مايو',
        6  => 'يونيو',
        7  => 'يوليو',
        8  => 'أغسطس',
        9  => 'سبتمبر',
        10 => 'أكتوبر',
        11 => 'نوفمبر',
        12 => 'ديسمبر',
    ];

    public function execute(
        User $user,
        array $filters = [],
    ): array {
        /*
        |--------------------------------------------------------------------------
        | Employee source
        |--------------------------------------------------------------------------
        |
        | This temporarily preserves the data source used by the old
        | EmployeeDetailsService.
        |
        | The old service reads these performance values from the employee
        | model. Later, when the real collection/payment reporting queries
        | are connected, this section can be replaced without changing
        | the controller or Blade page.
        |
        */

        $profile = $this->buildProfile($user);

        $banks = $this->buildBankData(
            employee: $user,
            profile: $profile,
        );

        return $this->buildReport(
            user: $user,
            profile: $profile,
            banks: $banks,
            filters: $filters,
        );
    }

    /**
     * Build the employee profile from the existing performance source.
     */
    private function buildProfile(User $employee): array
    {
        $cases = $this->number($employee->cases);

        $collected = $this->money(
            $employee->amount
        );

        $efficiency = $this->percentage(
            $employee->efficiency
        );

        $ptp = $this->number(
            $employee->ptp
        );

        $target = $this->calculateTarget(
            employee: $employee,
            collected: $collected,
            efficiency: $efficiency,
        );

        $achievement = $this->calculateAchievement(
            collected: $collected,
            target: $target,
            efficiency: $efficiency,
        );

        $promises = $this->buildPromiseMetrics(
            employee: $employee,
            totalPromises: $ptp,
        );

        $trend = $this->buildTrend(
            employee: $employee,
            collected: $collected,
            target: $target,
        );

        return [
            'cases' => $cases,
            'collected' => $collected,
            'target' => $target,
            'efficiency' => $efficiency,
            'ptp' => $ptp,
            'achievement' => $achievement,
            'promises' => $promises,
            'trend' => $trend,
            'recent' => $this->recentPromises($employee),
        ];
    }

    /**
     * Build bank performance data.
     *
     * The old implementation uses the employee's assigned banks and
     * attaches the employee-level performance metrics to each bank.
     */
    private function buildBankData(
        User $employee,
        array $profile,
    ): Collection {
        if (!$employee->relationLoaded('banks')) {
            return collect();
        }

        return $employee->banks
            ->map(function ($bank) use ($profile): object {
                return (object) [
                    'id' => $bank->id,
                    'name' => $bank->name,
                    'cases' => $profile['cases'],
                    'collected' => $profile['collected'],
                    'kept' => $profile['promises']['kept'],
                    'broken' => $profile['promises']['broken'],
                    'efficiency' => $profile['efficiency'],
                ];
            })
            ->values();
    }

    /**
     * Build the final report consumed by the Blade view.
     */
    private function buildReport(
        User $user,
        array $profile,
        Collection $banks,
        array $filters,
    ): array {
        $promises = $profile['promises'];
        $trend = $profile['trend'];

        $trendCollected = collect(
            $trend['collected'] ?? []
        )->map(
            fn ($value): float => $this->money($value)
        )->values();

        $trendTarget = collect(
            $trend['target'] ?? []
        )->map(
            fn ($value): float => $this->money($value)
        )->values();

        $totalCases = (int) ($profile['cases'] ?? 0);
        $totalCollected = (float) ($profile['collected'] ?? 0);
        $totalTarget = (float) ($profile['target'] ?? 0);
        $efficiency = (float) ($profile['efficiency'] ?? 0);

        $totalPromises = (int) ($promises['total'] ?? 0);
        $keptPromises = (int) ($promises['kept'] ?? 0);
        $partialPromises = (int) ($promises['partial'] ?? 0);
        $brokenPromises = (int) ($promises['broken'] ?? 0);
        $activePromises = (int) ($promises['active'] ?? 0);

        $resolvedPromises =
            $keptPromises +
            $partialPromises +
            $brokenPromises;

        $promiseSuccessRate = $resolvedPromises > 0
            ? round(
                (($keptPromises + $partialPromises) / $resolvedPromises) * 100
            )
            : 0;

        $keptRate = $totalPromises > 0
            ? round(($keptPromises / $totalPromises) * 100)
            : 0;

        $brokenRate = $totalPromises > 0
            ? round(($brokenPromises / $totalPromises) * 100)
            : 0;

        $trendCollectedTotal = $trendCollected->sum();
        $trendTargetTotal = $trendTarget->sum();

        if ($trendCollected->count() === 1) {
            $trendCollectedTotal = $totalCollected;
            $trendTargetTotal = $totalTarget;
        }

        $targetAchievement = $totalTarget > 0
            ? round(($totalCollected / $totalTarget) * 100)
            : $efficiency;

        $averageMonthlyCollection = $trendCollected->count() > 0
            ? round(
                $trendCollectedTotal /
                $trendCollected->count()
            )
            : $totalCollected;

        $latestCollected =
            $trendCollected->last() ??
            $totalCollected;

        $latestTarget =
            $trendTarget->last() ??
            $totalTarget;

        $latestAchievement = $latestTarget > 0
            ? round(
                ($latestCollected / $latestTarget) * 100
            )
            : $efficiency;

        $bankCount = $banks->count();

        $averageBankCollection = $bankCount > 0
            ? round($totalCollected / $bankCount)
            : 0;

        $highestBank = $banks
            ->sortByDesc(
                fn ($bank): float =>
                    (float) ($bank->collected ?? 0)
            )
            ->first();

        $highestEfficiencyBank = $banks
            ->sortByDesc(
                fn ($bank): float =>
                    (float) ($bank->efficiency ?? 0)
            )
            ->first();

        $performance = $this->performanceStatus(
            $targetAchievement
        );

        $selectedMonth = $filters['month'] ?? null;
        $selectedYear = $filters['year'] ?? null;

        $monthName = $selectedMonth !== null
            ? self::MONTHS[(int) $selectedMonth] ?? null
            : null;

        return [
            'employee' => [
                'id' => $user->id,
                'name' => $user->name,
                'code' => $user->employee_code,
                'employee_code' => $user->employee_code,
                'email' => $user->email,
                'phone' => $user->phone,
                'status' => $user->status,
                'role' => $user->role?->name ?? 'غير محدد',
                'supervisor' => $user->supervisor
                    ? [
                        'id' => $user->supervisor->id,
                        'name' => $user->supervisor->name,
                        'code' => $user->supervisor->employee_code,
                    ]
                    : null,
            ],

            'filters' => [
                'month' => $selectedMonth,
                'year' => $selectedYear,
                'month_name' => $monthName,
            ],

            'performance' => [
                'total_cases' => $totalCases,
                'total_collected' => $totalCollected,
                'total_target' => $totalTarget,
                'efficiency' => $efficiency,
                'target_achievement' => $targetAchievement,

                'performance_tone' =>
                    $performance['tone'],

                'performance_label' =>
                    $performance['label'],

                'performance_icon' =>
                    $performance['icon'],

                'score' => $targetAchievement,
                'percentage' => $targetAchievement,
            ],

            'trend' => [
                'labels' => $trend['labels'] ?? [],
                'collected' => $trendCollected,
                'target' => $trendTarget,

                'collected_total' =>
                    $trendCollectedTotal,

                'target_total' =>
                    $trendTargetTotal,

                'average_monthly_collection' =>
                    $averageMonthlyCollection,

                'latest_collected' =>
                    $latestCollected,

                'latest_target' =>
                    $latestTarget,

                'latest_achievement' =>
                    $latestAchievement,
            ],

            'promises' => [
                'kept' => $keptPromises,
                'partial' => $partialPromises,
                'broken' => $brokenPromises,
                'active' => $activePromises,
                'total' => $totalPromises,

                'success_rate' =>
                    $promiseSuccessRate,

                'kept_rate' =>
                    $keptRate,

                'broken_rate' =>
                    $brokenRate,

                'chart' => [
                    $keptPromises,
                    $partialPromises,
                    $brokenPromises,
                    $activePromises,
                ],
            ],

            'banks' => [
                'items' => $banks,
                'count' => $bankCount,
                'average_collection' =>
                    $averageBankCollection,
                'highest' => $highestBank,
                'highest_efficiency' =>
                    $highestEfficiencyBank,
            ],

            'recent' =>
                $profile['recent'] ?? [],

            /*
            |--------------------------------------------------------------------------
            | Compatibility data
            |--------------------------------------------------------------------------
            |
            | These fields allow the current Blade to continue working while
            | we migrate it to the final report structure.
            |
            */

            'stats' => $this->buildStats($profile),

            'labels' =>
                $trend['labels'] ?? [],
        ];
    }

    /**
     * Build the existing statistic cards.
     */
    private function buildStats(array $profile): array
    {
        return [
            [
                'label' => 'الحالات',
                'value' => number_format(
                    $profile['cases']
                ),
                'unit' => 'حالة',
                'icon' => 'fa-briefcase',
                'color' => 'info',
            ],
            [
                'label' => 'وعود الدفع',
                'value' => number_format(
                    $profile['ptp']
                ),
                'unit' => 'وعد',
                'icon' => 'fa-handshake',
                'color' => 'warning',
            ],
            [
                'label' => 'إجمالي التحصيل',
                'value' => number_format(
                    $profile['collected']
                ),
                'unit' => 'EGP',
                'icon' => 'fa-money-bill-wave',
                'color' => 'brand',
            ],
            [
                'label' => 'تحقيق المستهدف',
                'value' =>
                    $profile['achievement'] . '%',
                'unit' => null,
                'icon' => 'fa-bullseye',
                'color' =>
                    $this->getTone(
                        $profile['achievement']
                    ),
            ],
            [
                'label' => 'مؤشر الكفاءة',
                'value' =>
                    $profile['efficiency'] . '%',
                'unit' => null,
                'icon' => 'fa-bolt',
                'color' =>
                    $this->getTone(
                        $profile['efficiency']
                    ),
            ],
        ];
    }

    /**
     * Build promise metrics.
     */
    private function buildPromiseMetrics(
        User $employee,
        int $totalPromises,
    ): array {
        $kept = $this->number(
            $employee->promises_kept
        );

        $partial = $this->number(
            $employee->promises_partial
        );

        $broken = $this->number(
            $employee->promises_broken
        );

        $active = $this->number(
            $employee->promises_active
        );

        $hasBreakdown =
            ($kept + $partial + $broken + $active) > 0;

        if (!$hasBreakdown) {
            return [
                'total' => $totalPromises,
                'kept' => 0,
                'partial' => 0,
                'broken' => 0,
                'active' => $totalPromises,
                'real' => false,
            ];
        }

        $total =
            $kept +
            $partial +
            $broken +
            $active;

        if (
            $total > $totalPromises &&
            $totalPromises > 0
        ) {
            $ratio = $totalPromises / $total;

            $kept = (int) floor(
                $kept * $ratio
            );

            $partial = (int) floor(
                $partial * $ratio
            );

            $broken = (int) floor(
                $broken * $ratio
            );

            $active = max(
                0,
                $totalPromises -
                $kept -
                $partial -
                $broken
            );
        }

        return [
            'total' => $totalPromises,
            'kept' => $kept,
            'partial' => $partial,
            'broken' => $broken,
            'active' => $active,
            'real' => true,
        ];
    }

    /**
     * Build collection trend.
     */
    private function buildTrend(
        User $employee,
        float $collected,
        float $target,
    ): array {
        $trend = $employee->trend ?? null;

        if (
            is_array($trend) &&
            !empty($trend['collected'])
        ) {
            $collectedValues = array_map(
                fn ($value): float =>
                    $this->money($value),
                $trend['collected']
            );

            $targetValues = !empty($trend['target'])
                ? array_map(
                    fn ($value): float =>
                        $this->money($value),
                    $trend['target']
                )
                : array_fill(
                    0,
                    count($collectedValues),
                    $target
                );

            $labels = !empty($trend['labels'])
                ? array_values($trend['labels'])
                : $this->generatePeriodLabels(
                    count($collectedValues)
                );

            return [
                'labels' => $labels,
                'collected' => $collectedValues,
                'target' => $targetValues,
                'real' => true,
            ];
        }

        return [
            'labels' => ['الحالي'],
            'collected' => [$collected],
            'target' => [$target],
            'real' => false,
        ];
    }

    /**
     * Get recent promises.
     */
private function recentPromises(User $employee): array
{
    if (! $employee->relationLoaded('promises')) {
        return [];
    }

    return $employee->promises
        ->sortByDesc('promise_date')
        ->take(10)
        ->map(function ($promise) {
            return [
                'client' => $promise->debtCase?->client?->name ?? 'غير معروف',
                'amount' => $this->money($promise->promised_amount),
                'date' => $promise->promise_date?->format('Y-m-d'),
                'status' => $promise->status,
                'status_label' => $promise->status_label,
            ];
        })
        ->values()
        ->all();
}

    /**
     * Calculate target.
     */
    private function calculateTarget(
        User $employee,
        float $collected,
        float $efficiency,
    ): float {
        $target = $this->money(
            $employee->target
        );

        if ($target > 0) {
            return $target;
        }

        if (
            $efficiency <= 0 ||
            $collected <= 0
        ) {
            return 0;
        }

        return round(
            $collected /
            ($efficiency / 100),
            2
        );
    }

    /**
     * Calculate target achievement.
     */
    private function calculateAchievement(
        float $collected,
        float $target,
        float $efficiency,
    ): int {
        if ($target <= 0) {
            return (int) max(
                0,
                round($efficiency)
            );
        }

        return (int) max(
            0,
            round(
                ($collected / $target) * 100
            )
        );
    }

    /**
     * Determine performance presentation state.
     */
    private function performanceStatus(
        float|int $achievement
    ): array {
        return match (true) {
            $achievement >= 100 => [
                'tone' => 'brand',
                'label' => 'متجاوز للمستهدف',
                'icon' => 'fa-arrow-trend-up',
            ],

            $achievement >= 80 => [
                'tone' => 'warning',
                'label' => 'قريب من المستهدف',
                'icon' => 'fa-chart-line',
            ],

            default => [
                'tone' => 'danger',
                'label' => 'أقل من المستهدف',
                'icon' => 'fa-arrow-trend-down',
            ],
        };
    }

    /**
     * Get visual tone.
     */
    private function getTone(
        float|int $value
    ): string {
        return match (true) {
            $value >= 90 => 'brand',
            $value >= 70 => 'warning',
            default => 'danger',
        };
    }

    /**
     * Generate fallback trend labels.
     */
    private function generatePeriodLabels(
        int $count
    ): array {
        return collect(
            range(1, $count)
        )->map(
            fn ($index) =>
                "فترة {$index}"
        )->all();
    }

    /**
     * Convert to positive integer.
     */
    private function number(
        mixed $value
    ): int {
        if (!is_numeric($value)) {
            return 0;
        }

        return max(
            0,
            (int) round(
                (float) $value
            )
        );
    }

    /**
     * Convert to positive money amount.
     */
    private function money(
        mixed $value
    ): float {
        if (is_numeric($value)) {
            return max(
                0,
                (float) $value
            );
        }

        if (!is_string($value)) {
            return 0;
        }

        $value = str_replace(
            [
                ',',
                '٬',
                'EGP',
                'جنيه',
            ],
            '',
            $value
        );

        $value = preg_replace(
            '/[^\d.\-]/',
            '',
            $value
        );

        return is_numeric($value)
            ? max(0, (float) $value)
            : 0;
    }

    /**
     * Convert to percentage.
     */
    private function percentage(
        mixed $value
    ): float {
        return min(
            100,
            $this->money($value)
        );
    }
}