<?php

// app/Http/Controllers/BankClientAssignController.php  (static data)
// "تعيين لموظف": give one or many selected cases of a bank to one employee.
// Opened from the clients page: the cyan toolbar button (selected rows) and the row menu (one row).

namespace App\Http\Controllers;

use App\Support\StaticBanks;
use App\Support\StaticData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BankClientAssignController extends Controller
{
    // POST /banks/{bank}/clients/assign
    public function __invoke(Request $request, int $bank): RedirectResponse
    {
        StaticBanks::find($bank);

        $data = $request->validate([
            'client_ids'      => ['required', 'array', 'min:1'],
            'client_ids.*'    => ['integer'],
            'employee_id'     => ['required', Rule::in(StaticData::employees()->pluck('id')->all())],
            'only_unassigned' => ['nullable', 'boolean'],
            'reason'          => ['nullable', 'string', 'max:255'],
        ], [
            'client_ids.required' => 'اختر حالة واحدة على الأقل.',
            'client_ids.min'      => 'اختر حالة واحدة على الأقل.',
            'employee_id.required' => 'اختر الموظف.',
        ]);

        $employee = StaticData::user($data['employee_id']);
        $count    = count($data['client_ids']);

        // TODO (DB), inside a transaction:
        //   1) close the current row in case_assignments (unassigned_at = now) for each case that already has an employee
        //      (skip those cases when only_unassigned is ticked),
        //   2) insert a new case_assignments row (user_id, assigned_by = auth()->id(), reason),
        //   3) update debt_cases.assigned_user_id,
        //   4) write one activity_logs row.
        $note = ! empty($data['only_unassigned']) ? ' (الحالات غير المسندة فقط)' : '';

        // back() keeps the filters and the page number the user was on
        return back()->with('success', "تم تعيين {$count} حالة للموظف {$employee->name}{$note} (بيانات تجريبية، لم يتم الحفظ).");
    }

    
}