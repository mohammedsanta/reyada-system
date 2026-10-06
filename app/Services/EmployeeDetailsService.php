<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class EmployeeDetailsService
{
    private const MONTHS = [
        1 => 'يناير',
        2 => 'فبراير',
        3 => 'مارس',
        4 => 'أبريل',
        5 => 'مايو',
        6 => 'يونيو',
        7 => 'يوليو',
        8 => 'أغسطس',
        9 => 'سبتمبر',
        10 => 'أكتوبر',
        11 => 'نوفمبر',
        12 => 'ديسمبر',
    ];

    public function getDetails(string $code, array $filters = []): array
    {
        $employee = User::query()
            ->with(['role', 'supervisor', 'banks'])
            ->where('employee_code', $code)
            ->where('is_system_account', false)
            ->firstOrFail();

        $cases = $this->cases($employee);
        $ptp = $this->ptp($employee);
        $collected = $this->collected($employee);

        $efficiency = $cases > 0
            ? round(($ptp / $cases) * 100, 1)
            : 0;

        return [
            'user' => $employee,

            'profile' => [
                'cases' => $cases,
                'collected' => $collected,
                'target' => 0,
                'efficiency' => $efficiency,
                'promises' => [
                    'total' => $ptp,
                    'kept' => 0,
                    'partial' => 0,
                    'broken' => 0,
                    'active' => $ptp,
                ],
                'trend' => [
                    'labels' => ['الحالي'],
                    'collected' => [$collected],
                    'target' => [0],
                    'real' => true,
                ],
                'recent' => [],
            ],

            'stats' => $this->stats(
                $cases,
                $ptp,
                $collected,
                $efficiency
            ),

            'banks' => $this->banks(
                $employee,
                $cases,
                $ptp,
                $collected,
                $efficiency
            ),

            'tone' => $this->tone($efficiency),

            'months' => self::MONTHS,

            'years' => range(
                now()->year - 2,
                now()->year
            ),

            'filters' => [
                'month' => $this->month($filters['month'] ?? null),
                'year' => $this->year($filters['year'] ?? null),
            ],

            'labels' => ['الحالي'],
        ];
    }

    private function cases(User $employee): int
    {
        return $employee->cases()
            ->count();
    }

    private function ptp(User $employee): int
    {
        return $employee->promisesToPay()
            ->count();
    }

    private function collected(User $employee): float
    {
        return (float) $employee->payments()
            ->sum('amount');
    }

    private function banks(
        User $employee,
        int $cases,
        int $ptp,
        float $collected,
        float $efficiency
    ): Collection {
        return $employee->banks
            ->map(fn ($bank) => (object) [
                'id' => $bank->id,
                'name' => $bank->name,
                'cases' => $cases,
                'collected' => $collected,
                'kept' => 0,
                'broken' => 0,
                'efficiency' => $efficiency,
            ])
            ->values();
    }

    private function stats(
        int $cases,
        int $ptp,
        float $collected,
        float $efficiency
    ): array {
        return [
            [
                'label' => 'الحالات',
                'value' => number_format($cases),
                'unit' => 'حالة',
                'icon' => 'fa-briefcase',
                'color' => 'info',
            ],
            [
                'label' => 'وعود الدفع',
                'value' => number_format($ptp),
                'unit' => 'وعد',
                'icon' => 'fa-handshake',
                'color' => 'warning',
            ],
            [
                'label' => 'إجمالي التحصيل',
                'value' => number_format($collected),
                'unit' => 'EGP',
                'icon' => 'fa-money-bill-wave',
                'color' => 'brand',
            ],
            [
                'label' => 'تحقيق المستهدف',
                'value' => '0%',
                'unit' => null,
                'icon' => 'fa-bullseye',
                'color' => 'danger',
            ],
            [
                'label' => 'مؤشر الكفاءة',
                'value' => $efficiency . '%',
                'unit' => null,
                'icon' => 'fa-bolt',
                'color' => $this->tone($efficiency),
            ],
        ];
    }

    private function tone(float $value): string
    {
        return match (true) {
            $value >= 90 => 'brand',
            $value >= 70 => 'warning',
            default => 'danger',
        };
    }

    private function month(mixed $month): int
    {
        $month = (int) $month;

        return array_key_exists($month, self::MONTHS)
            ? $month
            : now()->month;
    }

    private function year(mixed $year): int
    {
        $year = (int) $year;

        return in_array(
            $year,
            range(now()->year - 2, now()->year),
            true
        )
            ? $year
            : now()->year;
    }
}