<?php

// app/Http/Controllers/LoanTypeController.php  (static data)
// "أنواع القروض": the lookup list used in the clients forms and filters (قرض شخصي، تمويل عقاري ...).

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LoanTypeController extends Controller
{
    /** TODO (DB): LoanType::withCount('debtCases') */
    private function all(): Collection
    {
        // id, name, active, number of cases that use it
        return collect([[1, 'قرض شخصي', true, 54], [2, 'تمويل عقاري', true, 28], [3, 'قرض سلع معمرة', true, 15], [4, 'بطاقة ائتمان', false, 0]])
            ->map(fn (array $r) => (object) array_combine(['id', 'name', 'is_active', 'used'], $r));
    }

    private function find(int $id): object
    {
        return $this->all()->firstWhere('id', $id) ?? abort(404);
    }

    private function rules(?object $item = null): array
    {
        $taken = $this->all()->when($item, fn ($c) => $c->where('id', '!=', $item->id))->pluck('name')->all();

        return [
            'name'      => ['required', 'string', 'max:100', Rule::notIn($taken)],   // TODO (DB): Rule::unique('loan_types')->ignore($item?->id)
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    // GET /loan-types
    public function index(): View
    {
        return view('settings.lookup', [
            'title' => 'أنواع القروض', 'subtitle' => 'القائمة المستخدمة في بيانات العملاء والفلاتر', 'icon' => 'fa-hand-holding-dollar',
            'items' => $this->all(), 'search' => '',
            'hasEnglish' => false, 'hasActive' => true, 'usedLabel' => 'عدد الحالات',
            'storeUrl' => route('loan-types.store'),
            'updateUrl' => route('loan-types.update', '__ID__'),
            'destroyUrl' => route('loan-types.destroy', '__ID__'),
            'singular' => 'نوع قرض',
        ]);
    }

    // POST /loan-types
    public function store(Request $request): RedirectResponse
    {
        $request->validate($this->rules());
        // TODO (DB): LoanType::create($validated)
        return redirect()->route('loan-types.index')->with('success', 'تمت إضافة نوع القرض (بيانات تجريبية، لم يتم الحفظ).');
    }

    // PUT /loan-types/{type}
    public function update(Request $request, int $type): RedirectResponse
    {
        $request->validate($this->rules($this->find($type)));
        // TODO (DB): $loanType->update($validated)
        return redirect()->route('loan-types.index')->with('success', 'تم تعديل نوع القرض (بيانات تجريبية، لم يتم الحفظ).');
    }

    // DELETE /loan-types/{type}
    public function destroy(int $type): RedirectResponse
    {
        $item = $this->find($type);

        if ($item->used > 0) {
            return back()->withErrors(['delete' => "لا يمكن حذف «{$item->name}» لأنه مستخدم في {$item->used} حالة. عطّله بدلاً من حذفه."]);
        }

        // TODO (DB): $loanType->delete()
        return redirect()->route('loan-types.index')->with('success', 'تم حذف نوع القرض (بيانات تجريبية، لم يتم الحذف).');
    }
}