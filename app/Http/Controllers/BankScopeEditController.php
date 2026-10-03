<?php

// app/Http/Controllers/BankScopeEditController.php  (static data)
// "تعديل النطاق": change ONE field for MANY cases of the active scope at once.
// Step 1 (GET filters): choose which cases.  Step 2 (PUT): choose the field and its new value.

namespace App\Http\Controllers;

use App\Support\StaticBanks;
use App\Support\StaticData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BankScopeEditController extends Controller
{
    // field => label (the fields that may be edited in bulk)
    private const FIELDS = [
        'assigned_user_id' => 'الموظف المسؤول',
        'status'           => 'حالة العميل',
        'bucket'           => 'الشريحة (BUCKET)',
        'loan_type'        => 'نوع القرض',
        'next_due_date'    => 'تاريخ الاستحقاق القادم',
    ];

    /** Only bank #1 has an active scope here. TODO (DB): DebtCase::where('portfolio_id', activePortfolio)->with(...) */
    private function cases(int $bankId): Collection
    {
        if ($bankId !== 1) {
            return collect();
        }

        $clients = StaticData::clients()->keyBy('code');

        // code, loan type, bucket, status, assigned user id (null = no employee yet)
        $rows = [
            ['1000001', 'قرض شخصي',      2, 'ACTIVE', 3],
            ['1000002', 'تمويل عقاري',   2, 'ACTIVE', 3],
            ['1000004', 'قرض شخصي',      1, 'ACTIVE', 4],
            ['1000003', 'تمويل عقاري',   2, 'ACTIVE', 4],
            ['1000005', 'قرض شخصي',      1, 'ACTIVE', null],
            ['1000000', 'قرض سلع معمرة', 0, 'ACTIVE', null],
        ];

        return collect($rows)->map(fn (array $r) => (object) [
            'code'      => $r[0],
            'name'      => $clients[$r[0]]->name,
            'loan_type' => $r[1],
            'bucket'    => $r[2],
            'status'    => $r[3],
            'employee_id'   => $r[4],
            'employee_name' => $r[4] ? StaticData::user($r[4])->name : null,
        ]);
    }

    /** Apply the step-1 filters. employee: '' = everyone, '0' = cases without an employee. */
    private function matching(Collection $cases, array $filters): Collection
    {
        return $cases
            ->when($filters['bucket'] !== '',    fn (Collection $c) => $c->where('bucket', (int) $filters['bucket']))
            ->when($filters['loan_type'] !== '', fn (Collection $c) => $c->where('loan_type', $filters['loan_type']))
            ->when($filters['status'] !== '',    fn (Collection $c) => $c->where('status', $filters['status']))
            ->when($filters['employee'] !== '',  fn (Collection $c) => $c->filter(
                fn ($x) => (int) $filters['employee'] === 0 ? $x->employee_id === null : $x->employee_id === (int) $filters['employee']
            ))
            ->values();
    }

    private function filters(Request $request): array
    {
        return [
            'bucket'    => (string) $request->input('bucket', ''),
            'loan_type' => (string) $request->input('loan_type', ''),
            'status'    => (string) $request->input('status', ''),
            'employee'  => (string) $request->input('employee', ''),
        ];
    }

    // GET /banks/{bank}/scope/edit?bucket=&loan_type=&status=&employee=
    public function edit(Request $request, int $bank): View
    {
        $bank    = StaticBanks::find($bank);
        $all     = $this->cases($bank->id);
        $filters = $this->filters($request);

        return view('banks.scope-edit', [
            'bank'      => $bank,
            'filters'   => $filters,
            'matched'   => $this->matching($all, $filters),
            'total'     => $all->count(),
            'fields'    => self::FIELDS,
            'buckets'   => $all->pluck('bucket')->unique()->sort()->values(),
            'loanTypes' => BankController::LOAN_TYPES,
            'statuses'  => BankController::STATUSES,
            'employees' => StaticData::employees()->pluck('name', 'id')->all(),
        ]);
    }

    // PUT /banks/{bank}/scope/edit
    public function update(Request $request, int $bank): RedirectResponse
    {
        StaticBanks::find($bank);
        $employeeIds = StaticData::employees()->pluck('id')->all();

        $data = $request->validate([
            'field'               => ['required', Rule::in(array_keys(self::FIELDS))],
            'value_employee'      => ['required_if:field,assigned_user_id', 'nullable', Rule::in(array_merge([0], $employeeIds))],
            'value_status'        => ['required_if:field,status', 'nullable', Rule::in(BankController::STATUSES)],
            'value_bucket'        => ['required_if:field,bucket', 'nullable', 'integer', 'between:0,9'],
            'value_loan_type'     => ['required_if:field,loan_type', 'nullable', Rule::in(BankController::LOAN_TYPES)],
            'value_due_date'      => ['required_if:field,next_due_date', 'nullable', 'date'],
            'confirm'             => ['accepted'],
        ], [
            'required_if' => 'اختر القيمة الجديدة.',
            'confirm.accepted' => 'يجب تأكيد أنك تريد تطبيق التعديل.',
        ]);

        $count = $this->matching($this->cases($bank), $this->filters($request))->count();

        if ($count === 0) {
            return back()->withInput()->withErrors(['field' => 'لا توجد حالات مطابقة للفلاتر الحالية.']);
        }

        // TODO (DB): inside a transaction: DebtCase::whereIn('id', matching ids)->update([field => value]);
        //            write ONE activity_logs row with the old/new values, and a case_assignments row when the employee changes.
        return redirect()
            ->route('banks.scope.edit', $bank)
            ->with('success', "تم تحديث «" . self::FIELDS[$data['field']] . "» لعدد {$count} حالة (بيانات تجريبية، لم يتم الحفظ).");
    }
}