<?php

// app/Http/Controllers/GovernorateController.php  (static data)
// "المحافظات": the 27 governorates of Egypt used in the clients' addresses.

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GovernorateController extends Controller
{
    /** TODO (DB): Governorate::withCount('clients') */
    private function all(): Collection
    {
        $names = [
            ['القاهرة', 'Cairo'], ['الجيزة', 'Giza'], ['الإسكندرية', 'Alexandria'], ['الدقهلية', 'Dakahlia'], ['البحر الأحمر', 'Red Sea'],
            ['البحيرة', 'Beheira'], ['الفيوم', 'Fayoum'], ['الغربية', 'Gharbia'], ['الإسماعيلية', 'Ismailia'], ['المنوفية', 'Monufia'],
            ['المنيا', 'Minya'], ['القليوبية', 'Qalyubia'], ['الوادي الجديد', 'New Valley'], ['السويس', 'Suez'], ['أسوان', 'Aswan'],
            ['أسيوط', 'Assiut'], ['بني سويف', 'Beni Suef'], ['بورسعيد', 'Port Said'], ['دمياط', 'Damietta'], ['الشرقية', 'Sharqia'],
            ['جنوب سيناء', 'South Sinai'], ['كفر الشيخ', 'Kafr El Sheikh'], ['مطروح', 'Matrouh'], ['الأقصر', 'Luxor'], ['قنا', 'Qena'],
            ['شمال سيناء', 'North Sinai'], ['سوهاج', 'Sohag'],
        ];

        // sample number of clients per governorate (the rest have none)
        $used = ['القاهرة' => 31, 'الجيزة' => 18, 'الإسكندرية' => 12, 'الدقهلية' => 9, 'أسيوط' => 6, 'سوهاج' => 4, 'الإسماعيلية' => 3, 'المنيا' => 2];

        return collect($names)->map(fn (array $n, int $i) => (object) [
            'id' => $i + 1, 'name' => $n[0], 'name_en' => $n[1], 'is_active' => true, 'used' => $used[$n[0]] ?? 0,
        ]);
    }

    private function find(int $id): object
    {
        return $this->all()->firstWhere('id', $id) ?? abort(404);
    }

    private function rules(?object $item = null): array
    {
        $taken = $this->all()->when($item, fn ($c) => $c->where('id', '!=', $item->id))->pluck('name')->all();

        return [
            'name'    => ['required', 'string', 'max:100', Rule::notIn($taken)],     // TODO (DB): Rule::unique('governorates')->ignore($item?->id)
            'name_en' => ['nullable', 'string', 'max:100'],
        ];
    }

    // GET /governorates?search=
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        return view('settings.lookup', [
            'title' => 'المحافظات', 'subtitle' => 'محافظات الجمهورية المستخدمة في عناوين العملاء', 'icon' => 'fa-map-location-dot',
            'items' => $this->all()->when($search !== '', fn (Collection $c) => $c->filter(
                fn ($g) => str_contains(mb_strtolower($g->name . ' ' . $g->name_en), mb_strtolower($search))
            ))->values(),
            'search' => $search,
            'hasEnglish' => true, 'hasActive' => false, 'usedLabel' => 'عدد العملاء',
            'storeUrl' => route('governorates.store'),
            'updateUrl' => route('governorates.update', '__ID__'),
            'destroyUrl' => route('governorates.destroy', '__ID__'),
            'singular' => 'محافظة',
        ]);
    }

    // POST /governorates
    public function store(Request $request): RedirectResponse
    {
        $request->validate($this->rules());
        // TODO (DB): Governorate::create($validated)
        return redirect()->route('governorates.index')->with('success', 'تمت إضافة المحافظة (بيانات تجريبية، لم يتم الحفظ).');
    }

    // PUT /governorates/{governorate}
    public function update(Request $request, int $governorate): RedirectResponse
    {
        $request->validate($this->rules($this->find($governorate)));
        // TODO (DB): $governorate->update($validated)
        return redirect()->route('governorates.index')->with('success', 'تم تعديل المحافظة (بيانات تجريبية، لم يتم الحفظ).');
    }

    // DELETE /governorates/{governorate}
    public function destroy(int $governorate): RedirectResponse
    {
        $item = $this->find($governorate);

        if ($item->used > 0) {
            return back()->withErrors(['delete' => "لا يمكن حذف «{$item->name}» لأن بها {$item->used} عميل."]);
        }

        // TODO (DB): $governorate->delete()
        return redirect()->route('governorates.index')->with('success', 'تم حذف المحافظة (بيانات تجريبية، لم يتم الحذف).');
    }
}