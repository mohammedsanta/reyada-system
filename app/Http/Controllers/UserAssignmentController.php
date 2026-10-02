<?php

// app/Http/Controllers/UserAssignmentController.php  (static data)
// "البنوك والشركات": which banks and installment companies an employee may work on.

namespace App\Http\Controllers;

use App\Support\StaticBanks;
use App\Support\StaticData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserAssignmentController extends Controller
{
    private function employee(int $id): object
    {
        $user = StaticData::user($id);
        abort_if($user->is_system, 403, 'حساب النظام يرى كل البنوك والشركات تلقائياً.');

        return $user;
    }

    // GET /users/{user}/assignments
    public function edit(int $user): View
    {
        return view('users.assignments', [
            'user'      => $this->employee($user),
            'banks'     => StaticBanks::all(),
            'companies' => StaticData::installmentCompanies(),
        ]);
    }

    // PUT /users/{user}/assignments
    public function update(Request $request, int $user): RedirectResponse
    {
        $employee = $this->employee($user);

        $data = $request->validate([
            'banks'       => ['nullable', 'array'],
            'banks.*'     => [Rule::in(StaticBanks::all()->pluck('id')->all())],
            'companies'   => ['nullable', 'array'],
            'companies.*' => [Rule::in(StaticData::installmentCompanies()->pluck('id')->all())],
        ]);

        $banks     = count($data['banks'] ?? []);
        $companies = count($data['companies'] ?? []);

        // TODO (DB): $user->banks()->sync($data['banks'] ?? []); $user->installmentCompanies()->sync($data['companies'] ?? []);
        return redirect()
            ->route('users.index')
            ->with('success', "تم تحديث نطاق عمل {$employee->name}: {$banks} بنوك و {$companies} شركات (بيانات تجريبية، لم يتم الحفظ).");
    }
}