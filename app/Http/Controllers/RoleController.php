<?php

// app/Http/Controllers/RoleController.php  (static data)
// "الرتب النظامية": create roles and choose which permissions each role has.

namespace App\Http\Controllers;

use App\Support\StaticData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    private function find(int $id): object
    {
        return StaticData::roles()->firstWhere('id', $id) ?? abort(404);
    }

    /** Permissions grouped by module, ready for the checkbox grid. */
    private function groups()
    {
        return StaticData::permissions()->groupBy('group')->map(fn ($items) => (object) [
            'label' => $items->first()->group_label,
            'items' => $items,
        ]);
    }

    private function rules(?object $role = null): array
    {
        $takenNames = StaticData::roles()->when($role, fn ($c) => $c->where('id', '!=', $role->id))->pluck('name')->all();

        return [
            'label'         => ['required', 'string', 'max:100'],
            'name'          => ['required', 'regex:/^[a-z][a-z_]*$/', 'max:50', Rule::notIn($takenNames)],   // TODO (DB): Rule::unique('roles')->ignore($role?->id)
            'description'   => ['nullable', 'string', 'max:255'],
            'level'         => ['required', 'integer', 'between:1,99'],
            'permissions'   => ['required', 'array', 'min:1'],
            'permissions.*' => [Rule::in(StaticData::permissions()->pluck('id')->all())],
        ];
    }

    private function messages(): array
    {
        return [
            'name.regex'           => 'الاسم البرمجي بحروف إنجليزية صغيرة وشرطة سفلية فقط (مثال: call_center).',
            'name.not_in'          => 'هذا الاسم البرمجي مستخدم في رتبة أخرى.',
            'permissions.required' => 'اختر صلاحية واحدة على الأقل.',
        ];
    }

    // GET /roles
    public function index(): View
    {
        return view('roles.index', [
            'roles'            => StaticData::roles(),
            'totalPermissions' => StaticData::permissions()->count(),
        ]);
    }

    // GET /roles/create
    public function create(): View
    {
        return view('roles.create', ['groups' => $this->groups()]);
    }

    // POST /roles
    public function store(Request $request): RedirectResponse
    {
        $request->validate($this->rules(), $this->messages());

        // TODO (DB): $role = Role::create([...]); $role->permissions()->sync($request->permissions);
        return redirect()->route('roles.index')->with('success', 'تمت إضافة الرتبة (بيانات تجريبية، لم يتم الحفظ).');
    }

    // GET /roles/{role}/edit
    public function edit(int $role): View
    {
        $found = $this->find($role);
        abort_if($found->is_system, 403, 'لا يمكن تعديل رتبة النظام.');

        return view('roles.edit', ['role' => $found, 'groups' => $this->groups()]);
    }

    // PUT /roles/{role}
    public function update(Request $request, int $role): RedirectResponse
    {
        $found = $this->find($role);
        abort_if($found->is_system, 403, 'لا يمكن تعديل رتبة النظام.');

        $request->validate($this->rules($found), $this->messages());

        // TODO (DB): $role->update([...]); $role->permissions()->sync($request->permissions);
        return redirect()->route('roles.index')->with('success', 'تم تعديل الرتبة (بيانات تجريبية، لم يتم الحفظ).');
    }

    // DELETE /roles/{role}
    public function destroy(int $role): RedirectResponse
    {
        $found = $this->find($role);
        abort_if($found->is_system, 403, 'لا يمكن حذف رتبة النظام.');

        // same rule as the database: a role that still has users cannot be deleted (restrictOnDelete)
        if ($found->users_count > 0) {
            return back()->withErrors(['role' => "لا يمكن حذف الرتبة ({$found->label}) لأن بها {$found->users_count} موظفين. انقلهم إلى رتبة أخرى أولاً."]);
        }

        // TODO (DB): $role->delete()
        return redirect()->route('roles.index')->with('success', 'تم حذف الرتبة (بيانات تجريبية، لم يتم الحذف).');
    }
}