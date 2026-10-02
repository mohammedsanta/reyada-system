<?php

// app/Http/Controllers/DistributionController.php  (static data)
// "توزيع الحالات": split a bank's cases between employees.

namespace App\Http\Controllers;

use App\Support\StaticBanks;
use App\Support\StaticData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DistributionController extends Controller
{
    private const SCOPES = [
        'unassigned' => ['label' => 'الحالات غير الموزعة فقط', 'hint' => 'لا يتم المساس بالحالات المسندة حالياً'],
        'all'        => ['label' => 'إعادة توزيع كل الحالات',   'hint' => 'تُسحب الحالات من الجميع وتوزع من جديد'],
    ];

    private const MODES = [
        'equal'    => ['label' => 'بالتساوي',          'hint' => 'نفس العدد تقريباً لكل موظف'],
        'capacity' => ['label' => 'حسب السعة المتاحة', 'hint' => 'من لديه سعة أكبر يستلم أكثر'],
        'manual'   => ['label' => 'يدوي',              'hint' => 'حدد عدد الحالات لكل موظف بنفسك'],
    ];

    // static numbers for the demo: 6 cases in total, 2 of them without an employee
    private const TOTAL_CASES = 6;
    private const UNASSIGNED  = 2;

    /** Employees with their current workload. TODO (DB): users + withCount('assignedCases') */
    private function employees(): Collection
    {
        $current = [2 => 0, 3 => 2, 4 => 2];   // user id => cases right now

        return StaticData::employees()->map(function ($user) use ($current) {
            $user->current  = $current[$user->id] ?? 0;
            $user->capacity = $user->role_id === 2 ? 30 : 50;   // max cases per employee

            return $user;
        });
    }

    // GET /banks/{bank}/distribution
    public function index(int $bank): View
    {
        $employees = $this->employees();

        return view('banks.distribution', [
            'bank'      => StaticBanks::find($bank),
            'employees' => $employees,
            'scopes'    => self::SCOPES,
            'modes'     => self::MODES,
            'stats'     => [
                'total'      => self::TOTAL_CASES,
                'assigned'   => self::TOTAL_CASES - self::UNASSIGNED,
                'unassigned' => self::UNASSIGNED,
                'employees'  => $employees->count(),
            ],
            'buckets'   => [0, 1, 2],
            'loanTypes' => BankController::LOAN_TYPES,
            'history'   => [
                ['اليوم 10:05', 'Beshoy nople', 24, 3, 'بالتساوي'],
                ['01/10/2026',  'Beshoy nople', 6,  2, 'حسب السعة المتاحة'],
                ['28/09/2026',  'Test Account', 12, 3, 'يدوي'],
            ],
        ]);
    }

    // POST /banks/{bank}/distribution
    public function store(Request $request, int $bank): RedirectResponse
    {
        StaticBanks::find($bank);
        $ids = $this->employees()->pluck('id')->all();

        $data = $request->validate([
            'scope'          => ['required', Rule::in(array_keys(self::SCOPES))],
            'mode'           => ['required', Rule::in(array_keys(self::MODES))],
            'employee_ids'   => ['required', 'array', 'min:1'],
            'employee_ids.*' => [Rule::in($ids)],
            'counts'         => ['required_if:mode,manual', 'array'],
            'counts.*'       => ['nullable', 'integer', 'min:0'],
            'bucket'         => ['nullable', 'integer'],
            'loan_type'      => ['nullable', Rule::in(BankController::LOAN_TYPES)],
        ], [
            'employee_ids.required' => 'اختر موظفاً واحداً على الأقل.',
            'counts.required_if'    => 'حدد عدد الحالات لكل موظف.',
        ]);

        $pool = $data['scope'] === 'all' ? self::TOTAL_CASES : self::UNASSIGNED;

        if ($data['mode'] === 'manual') {
            $sum = collect($data['counts'] ?? [])->only($data['employee_ids'])->sum();

            if ($sum > $pool) {
                throw ValidationException::withMessages(['counts' => "مجموع الحالات ({$sum}) أكبر من المتاح ({$pool})."]);
            }
        }

        // TODO (DB): inside a transaction: update debt_cases.assigned_user_id and insert case_assignments rows
        return back()->with('success', "تم توزيع {$pool} حالة على " . count($data['employee_ids']) . ' موظفين (بيانات تجريبية، لم يتم الحفظ).');
    }
}