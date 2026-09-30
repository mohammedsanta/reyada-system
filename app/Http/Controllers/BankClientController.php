<?php

// app/Http/Controllers/BankClientController.php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BankClientController extends Controller
{
    // PUT /banks/{bank}/clients/{client}  (the edit modal posts here)
    public function update(Request $request, int $bank, int $client): RedirectResponse
    {
        $request->validate([
            // personal & contact
            'name'        => ['required', 'string', 'max:255'],
            'national_id' => ['required', 'digits:14'],
            'loan_type'   => ['required', Rule::in(BankController::LOAN_TYPES)],
            'status'      => ['required', Rule::in(BankController::STATUSES)],
            'phone'       => ['required', 'string', 'max:20'],
            'phone2'      => ['nullable', 'string', 'max:20'],
            'email'       => ['nullable', 'email', 'max:255'],
            'governorate' => ['nullable', 'string', 'max:100'],
            'address'     => ['nullable', 'string', 'max:500'],

            // debt
            'total_debt'           => ['required', 'numeric', 'min:0'],
            'overdue_amount'       => ['required', 'numeric', 'min:0'],
            'installment_value'    => ['nullable', 'numeric', 'min:0'],
            'bucket'               => ['required', 'integer', 'min:0'],
            'dpd'                  => ['nullable', 'integer', 'min:0'],
            'min_installment_diff' => ['nullable', 'numeric', 'min:0'],
            'next_due_date'        => ['nullable', 'date'],
            'late_fee'             => ['nullable', 'numeric', 'min:0'],

            // work
            'employer'     => ['nullable', 'string', 'max:255'],
            'job_title'    => ['nullable', 'string', 'max:255'],
            'work_phone'   => ['nullable', 'string', 'max:20'],
            'work_address' => ['nullable', 'string', 'max:500'],

            // dates & payments
            'loan_start_date'     => ['nullable', 'date'],
            'loan_end_date'       => ['nullable', 'date'],
            'last_payment_date'   => ['nullable', 'date'],
            'last_payment_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        // TODO (DB): $client->update($validated);

        return redirect()
            ->route('banks.show', $bank)
            ->with('success', 'تم حفظ بيانات العميل (بيانات تجريبية، لم يتم الحفظ).');
    }

    // DELETE /banks/{bank}/clients/{client}
    public function destroy(int $bank, int $client): RedirectResponse
    {
        // TODO (DB): $client->delete();

        return redirect()
            ->route('banks.show', $bank)
            ->with('success', 'تم حذف العميل (بيانات تجريبية، لم يتم الحذف).');
    }
}

// Method	URI	Name
// GET	banks	banks.index
// GET	banks/create	banks.create
// POST	banks	banks.store
// GET	banks/{bank}	banks.show
// GET	banks/{bank}/edit	banks.edit
// PUT/PATCH	banks/{bank}	banks.update
// DELETE	banks/{bank}	banks.destroy
// PUT/PATCH	banks/{bank}/clients/{client}	banks.clients.update
// DELETE	banks/{bank}/clients/{client}	banks.clients.destroy