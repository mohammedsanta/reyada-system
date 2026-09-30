<?php

// app/Http/Controllers/EmployeePerformanceController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class EmployeePerformanceController extends Controller
{
    /**
     * STATIC DATA (temporary), replace with real queries when the DB is ready.
     * `efficiency` is a percentage (0-100). `color` is the avatar ring color.
     */
    private function employees(): Collection
    {
        return collect([
            ['code' => '85392', 'name' => 'Ahmed Borlsy',  'bank' => 'بنك الإمارات دبي الوطني', 'cases' => 3, 'ptp' => 2, 'amount' => 15400, 'efficiency' => 68, 'color' => 'warning'],
            ['code' => '85577', 'name' => 'Beshoy nople',  'bank' => 'بنك الإمارات دبي الوطني', 'cases' => 0, 'ptp' => 0, 'amount' => 0,     'efficiency' => 0,  'color' => 'brand'],
            ['code' => '85515', 'name' => 'HOOL',          'bank' => 'بنك الإمارات دبي الوطني', 'cases' => 3, 'ptp' => 1, 'amount' => 4200,  'efficiency' => 31, 'color' => 'orange'],
        ])->map(function (array $employee) {
            $employee['initials'] = $this->initials($employee['name']);
            $employee['tone']     = $this->tone($employee['efficiency']);

            return (object) $employee;
        });
    }

    // "Ahmed Borlsy" => "AB", "HOOL" => "HO"
    private function initials(string $name): string
    {
        $words = preg_split('/\s+/u', trim($name));

        $letters = count($words) >= 2
            ? mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1)
            : mb_substr($words[0], 0, 2);

        return mb_strtoupper($letters);
    }

    // Efficiency color: 70+ green, 40-69 yellow, below 40 red
    private function tone(float $efficiency): string
    {
        return match (true) {
            $efficiency >= 70 => 'brand',
            $efficiency >= 40 => 'warning',
            default           => 'danger',
        };
    }

    // GET /employees?institution=&search=&month=&year=
    public function index(Request $request): View
    {
        $filters = [
            'institution' => (string) $request->query('institution', ''),
            'search'      => trim((string) $request->query('search', '')),
            'month'       => (int) $request->query('month', now()->month),
            'year'        => (int) $request->query('year', now()->year),
        ];

        $all = $this->employees();

        // NOTE: month/year are accepted but do not change the static data yet.
        $employees = $all
            ->when($filters['institution'] !== '', fn (Collection $c) => $c->where('bank', $filters['institution']))
            ->when($filters['search'] !== '', function (Collection $c) use ($filters) {
                $needle = mb_strtolower($filters['search']);

                return $c->filter(fn ($e) => str_contains(mb_strtolower($e->name . ' ' . $e->code), $needle));
            })
            ->sort(fn ($a, $b) => [$b->efficiency, $b->amount] <=> [$a->efficiency, $a->amount])
            ->values()
            ->map(function ($employee, int $index) {
                $employee->rank = $index + 1;

                return $employee;
            });

        return view('employees.index', [
            'employees'    => $employees,
            'totalCount'   => $all->count(),
            'stats'        => $this->stats($employees),
            'filters'      => $filters,
            'institutions' => $all->pluck('bank')->unique()->values(),
            'months'       => $this->months(),
            'years'        => range(now()->year - 2, now()->year),
        ]);
    }

    private function stats(Collection $employees): array
    {
        $average = $employees->isEmpty() ? 0 : $employees->avg('efficiency');

        return [
            ['label' => 'الموظفون النشطون',   'value' => $employees->count(),                     'unit' => null,   'icon' => 'fa-users',            'color' => 'info'],
            ['label' => 'الحالات الموزعة',    'value' => $employees->sum('cases'),                'unit' => null,   'icon' => 'fa-briefcase',        'color' => 'accent'],
            ['label' => 'إجمالي التحصيل',     'value' => number_format($employees->sum('amount')), 'unit' => 'ج.م', 'icon' => 'fa-money-bill-wave',  'color' => 'brand'],
            ['label' => 'وعود الدفع الناجحة', 'value' => $employees->sum('ptp'),                  'unit' => null,   'icon' => 'fa-circle-check',     'color' => 'orange'],
            ['label' => 'مؤشر الكفاءة',       'value' => number_format($average, 1) . '%',        'unit' => null,   'icon' => 'fa-bolt',             'color' => 'cyan'],
        ];
    }

    private function months(): array
    {
        return [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس',    4 => 'أبريل',
            5 => 'مايو',  6 => 'يونيو',  7 => 'يوليو',   8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
        ];
    }
}