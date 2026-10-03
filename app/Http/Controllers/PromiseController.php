<?php

// app/Http/Controllers/PromiseController.php  (static data)
// One promise to pay (PTP): register a new one, see its details, and close it (kept / partial / broken).

namespace App\Http\Controllers;

use App\Support\StaticBanks;
use App\Support\StaticData;
use App\Support\StaticPromises;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PromiseController extends Controller
{
    private const METHODS = ['cash' => 'نقدي', 'e_wallet' => 'محفظة إلكترونية', 'bank_transfer' => 'تحويل بنكي'];

    private function statuses(): array
    {
        return collect(PtpController::STATUSES)->map(fn ($s) => $s['label'])->all();
    }

    // GET /banks/{bank}/ptp/create?client=1000002
    public function create(Request $request, int $bank): View
    {
        return view('ptp.create', [
            'bank'      => StaticBanks::find($bank),
            'clients'   => StaticData::clients()->mapWithKeys(fn ($c) => [$c->code => $c->name . ' (' . $c->code . ')'])->all(),
            'employees' => StaticData::employees()->pluck('name', 'id')->all(),
            'methods'   => self::METHODS,
            'selected'  => (string) $request->query('client', ''),
            'tomorrow'  => now()->addDay()->toDateString(),
        ]);
    }

    // POST /banks/{bank}/ptp
    public function store(Request $request, int $bank): RedirectResponse
    {
        StaticBanks::find($bank);

        $request->validate([
            'client_code'  => ['required', Rule::in(StaticData::clients()->pluck('code')->all())],
            'amount'       => ['required', 'numeric', 'min:1'],
            'promise_date' => ['required', 'date', 'after_or_equal:today'],
            'employee_id'  => ['required', Rule::in(StaticData::employees()->pluck('id')->all())],
            'method'       => ['nullable', Rule::in(array_keys(self::METHODS))],
            'notes'        => ['nullable', 'string', 'max:1000'],
        ], ['promise_date.after_or_equal' => 'موعد الوعد لا يمكن أن يكون في الماضي.']);

        // TODO (DB): PromiseToPay::create([... 'status' => 'active'])
        return redirect()->route('banks.ptp.index', $bank)->with('success', 'تم تسجيل الوعد (بيانات تجريبية، لم يتم الحفظ).');
    }

    // GET /banks/{bank}/ptp/{promise}
    public function show(int $bank, int $promise): View
    {
        $found  = StaticPromises::find($bank, $promise);
        $client = StaticData::clients()->firstWhere('code', $found->client_code);

        return view('ptp.show', [
            'bank'      => StaticBanks::find($bank),
            'promise'   => $found,
            'client'    => $client,
            'remaining' => max(0, $found->amount - $found->paid),
            'statuses'  => $this->statuses(),
            'color'     => PtpController::STATUSES[$found->status]['color'],
        ]);
    }

    // PUT /banks/{bank}/ptp/{promise}
    public function update(Request $request, int $bank, int $promise): RedirectResponse
    {
        $found = StaticPromises::find($bank, $promise);

        $data = $request->validate([
            'status'      => ['required', Rule::in(array_keys(PtpController::STATUSES))],
            'paid_amount' => ['nullable', 'numeric', 'min:0', 'max:' . $found->amount],
            'notes'       => ['nullable', 'string', 'max:1000'],
        ], ['paid_amount.max' => 'المبلغ المسدد لا يمكن أن يكون أكبر من مبلغ الوعد (' . number_format($found->amount) . ').']);

        // the paid amount must agree with the status
        $paid = (float) ($data['paid_amount'] ?? 0);

        if ($data['status'] === 'kept' && $paid < $found->amount) {
            throw ValidationException::withMessages(['paid_amount' => 'الوعد "محقق كلياً" يتطلب سداد كامل المبلغ.']);
        }

        if ($data['status'] === 'partial' && ($paid <= 0 || $paid >= $found->amount)) {
            throw ValidationException::withMessages(['paid_amount' => 'الوعد "محقق جزئياً" يتطلب مبلغاً أكبر من صفر وأقل من مبلغ الوعد.']);
        }

        // TODO (DB): $promise->update([...]); set closed_at when the status is kept / partial / broken; add a payments row for the paid amount
        return redirect()->route('banks.ptp.show', [$bank, $promise])->with('success', 'تم تحديث الوعد (بيانات تجريبية، لم يتم الحفظ).');
    }
}