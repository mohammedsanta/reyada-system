<?php

// app/Http/Controllers/BankClientAssignController.php

namespace App\Http\Controllers;

use App\Support\StaticBanks;
use App\Support\StaticData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BankClientAssignController extends Controller
{
    /**
     * تنفيذ تعيين حالة أو أكثر لموظف.
     *
     * POST /banks/{bank}/clients/assign
     */
    public function __invoke(Request $request, int $bank): RedirectResponse
    {
        // نتأكد أن البنك موجود في البيانات التجريبية.
        StaticBanks::find($bank);

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $employeeIds = StaticData::employees()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $data = $request->validate([
            'client_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'client_ids.*' => [
                'integer',
            ],

            'employee_id' => [
                'required',
                Rule::in($employeeIds),
            ],

            'only_unassigned' => [
                'nullable',
                'boolean',
            ],

            'reason' => [
                'nullable',
                'string',
                'max:255',
            ],

        ], [
            'client_ids.required' => 'اختر حالة واحدة على الأقل.',
            'client_ids.array' => 'بيانات الحالات غير صحيحة.',
            'client_ids.min' => 'اختر حالة واحدة على الأقل.',

            'client_ids.*.integer' => 'يوجد رقم حالة غير صحيح.',

            'employee_id.required' => 'اختر الموظف.',
            'employee_id.in' => 'الموظف المحدد غير موجود.',

            'reason.max' => 'سبب التعيين يجب ألا يتجاوز 255 حرفًا.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Employee
        |--------------------------------------------------------------------------
        */

        $employee = StaticData::user((int) $data['employee_id']);

        if (! $employee) {
            return back()
                ->withInput()
                ->with('error', 'الموظف المحدد غير موجود.');
        }

        /*
        |--------------------------------------------------------------------------
        | Selected Clients
        |--------------------------------------------------------------------------
        */

        $clientIds = collect($data['client_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $count = $clientIds->count();

        /*
        |--------------------------------------------------------------------------
        | Static response
        |--------------------------------------------------------------------------
        |
        | لا يوجد حفظ فعلي في Database حاليًا.
        | لاحقًا سنضع هنا transaction + case_assignments
        | + debt_cases.assigned_user_id + activity_logs.
        |
        */

        $note = ! empty($data['only_unassigned'])
            ? ' (الحالات غير المسندة فقط)'
            : '';

        $reason = filled($data['reason'])
            ? ' السبب: ' . $data['reason']
            : '';

        return back()->with(
            'success',
            "تم تعيين {$count} حالة للموظف {$employee->name}{$note}{$reason} (بيانات تجريبية، لم يتم الحفظ)."
        );
    }

    /**
     * البحث عن الموظفين من البيانات التجريبية.
     *
     * GET /banks/{bank}/clients/assign/employees/search?q=
     *
     * البحث يعمل بواسطة:
     * - اسم الموظف
     * - ID
     * - employee_id
     * - employee_code
     * - code
     */
    public function searchEmployees(Request $request, int $bank): JsonResponse
    {
        // التأكد من وجود البنك.
        StaticBanks::find($bank);

        /*
        |--------------------------------------------------------------------------
        | Search term
        |--------------------------------------------------------------------------
        */

        $query = trim((string) $request->query('q', ''));

        /*
        |--------------------------------------------------------------------------
        | Static employees
        |--------------------------------------------------------------------------
        */

        $employees = StaticData::employees();

        /*
        |--------------------------------------------------------------------------
        | Empty search
        |--------------------------------------------------------------------------
        |
        | لا نرجع كل الموظفين مباشرة.
        | الهدف أن يبدأ المستخدم بالبحث أولاً.
        |
        */

        if ($query === '') {
            return response()->json([
                'success' => true,
                'query' => '',
                'count' => 0,
                'employees' => [],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Normalize search
        |--------------------------------------------------------------------------
        */

        $normalizedQuery = mb_strtolower($query);

        /*
        |--------------------------------------------------------------------------
        | Filter
        |--------------------------------------------------------------------------
        */

        $results = $employees
            ->filter(function ($employee) use ($normalizedQuery) {

                /*
                | الاسم
                */
                $name = mb_strtolower(
                    (string) data_get($employee, 'name', '')
                );

                /*
                | ID
                */
                $id = mb_strtolower(
                    (string) data_get($employee, 'id', '')
                );

                /*
                | employee_id
                */
                $employeeId = mb_strtolower(
                    (string) data_get($employee, 'employee_id', '')
                );

                /*
                | employee_code
                */
                $employeeCode = mb_strtolower(
                    (string) data_get($employee, 'employee_code', '')
                );

                /*
                | code
                */
                $code = mb_strtolower(
                    (string) data_get($employee, 'code', '')
                );

                return str_contains($name, $normalizedQuery)
                    || str_contains($id, $normalizedQuery)
                    || str_contains($employeeId, $normalizedQuery)
                    || str_contains($employeeCode, $normalizedQuery)
                    || str_contains($code, $normalizedQuery);
            })
            ->take(20)
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        |
        | نرسل فقط البيانات التي يحتاجها الـ modal.
        | data_get يحميك لو بعض الحقول غير موجودة.
        |
        */

        $payload = $results->map(function ($employee) {

            return [
                'id' => data_get($employee, 'id'),

                'name' => data_get(
                    $employee,
                    'name',
                    '-'
                ),

                'employee_id' => data_get(
                    $employee,
                    'employee_id'
                ),

                'employee_code' => data_get(
                    $employee,
                    'employee_code',
                    data_get($employee, 'code')
                ),

                'email' => data_get(
                    $employee,
                    'email'
                ),

                'phone' => data_get(
                    $employee,
                    'phone'
                ),

                'status' => data_get(
                    $employee,
                    'status',
                    '-'
                ),

                'role' => data_get(
                    $employee,
                    'role'
                ),

                'supervisor' => data_get(
                    $employee,
                    'supervisor'
                ),
            ];
        });

        return response()->json([
            'success' => true,
            'query' => $query,
            'count' => $payload->count(),
            'employees' => $payload,
        ]);
    }
}