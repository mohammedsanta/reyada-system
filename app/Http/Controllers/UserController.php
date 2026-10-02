<?php

// app/Http/Controllers/UserController.php  (REPLACES the old one; static data from StaticData)
// Methods: index, create, store (now) + show, edit, update, destroy (their pages come next).

namespace App\Http\Controllers;

use App\Support\StaticData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    private const STATUSES = ['active' => 'نشط', 'inactive' => 'معطل', 'suspended' => 'موقوف'];

    /** Dropdown options shared by the create and edit forms. */
    private function formOptions(): array
    {
        return [
            'roles'       => StaticData::roles()->pluck('label', 'id')->all(),
            // only Owner and Super Visors can supervise someone
            'supervisors' => StaticData::users()->whereIn('role_id', [1, 2])->pluck('name', 'id')->all(),
            'statuses'    => self::STATUSES,
        ];
    }

    private function rules(?object $user = null): array
    {
        $options = $this->formOptions();
        $emails  = StaticData::users()->when($user, fn ($c) => $c->where('id', '!=', $user->id))->pluck('email')->all();

        return [
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:255', Rule::notIn($emails)],      // TODO (DB): Rule::unique('users')->ignore($user?->id)
            'phone'         => ['required', 'regex:/^01[0-9]{9}$/'],                        // Egyptian mobile: 11 digits starting with 01
            'role_id'       => ['required', Rule::in(array_keys($options['roles']))],
            'supervisor_id' => ['nullable', Rule::in(array_keys($options['supervisors']))],
            'status'        => ['required', Rule::in(array_keys(self::STATUSES))],
            'password'      => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ];
    }

    // GET /users?search=&role=&status=
    public function index(Request $request): View
    {
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'role'   => (string) $request->query('role', ''),
            'status' => (string) $request->query('status', ''),
        ];

        $users = StaticData::users()
            ->when($filters['search'] !== '', function (Collection $c) use ($filters) {
                $needle = mb_strtolower($filters['search']);

                return $c->filter(fn ($u) => str_contains(mb_strtolower(implode(' ', [$u->code, $u->name, $u->email, $u->phone])), $needle));
            })
            ->when($filters['role'] !== '',   fn (Collection $c) => $c->where('role', $filters['role']))
            ->when($filters['status'] !== '', fn (Collection $c) => $c->where('status', $filters['status']))
            ->values();

        return view('users.index', [
            'users'    => $users,
            'filters'  => $filters,
            'roles'    => StaticData::roles()->pluck('label')->all(),
            'statuses' => self::STATUSES,
        ]);
    }

    // GET /users/create
    public function create(): View
    {
        return view('users.create', $this->formOptions() + ['nextCode' => '85600']);   // TODO (DB): generate the next employee_code
    }

    // POST /users
    public function store(Request $request): RedirectResponse
    {
        $request->validate($this->rules());

        // TODO (DB): User::create([...validated, 'password' => Hash::make(...), 'employee_code' => next code])
        return redirect()->route('users.index')->with('success', 'تمت إضافة الموظف (بيانات تجريبية، لم يتم الحفظ).');
    }

    // GET /users/{user}
    public function show(int $user): View
    {
        $found = StaticData::user($user);
        $role  = StaticData::roles()->firstWhere('id', $found->role_id);

        // final permissions = role permissions, then the personal overrides (true = granted, false = revoked)
        $permissions = StaticData::permissions();
        $allowedIds  = $permissions->filter(function ($permission) use ($role, $found) {
            $override = $found->overrides[$permission->name] ?? null;

            return $override === null ? in_array($permission->id, $role->permission_ids, true) : $override;
        })->pluck('id')->all();

        $modules = $permissions->groupBy('group')->map(fn ($items) => (object) [
            'label'   => $items->first()->group_label,
            'allowed' => $items->whereIn('id', $allowedIds)->count(),
            'total'   => $items->count(),
        ]);

        // TODO (DB): real numbers and the user's own activity_logs
        $numbers = [2 => [0, 3, 19600], 3 => [3, 1, 4200], 4 => [3, 2, 15400]][$found->id] ?? [0, 0, 0];

        return view('users.show', [
            'user'         => $found,
            'role'         => $role,
            'allowedCount' => count($allowedIds),
            'totalCount'   => $permissions->count(),
            'modules'      => $modules,
            'companies'    => StaticData::installmentCompanies()->whereIn('id', $found->company_ids)->pluck('name')->all(),
            'numbers'      => ['cases' => $numbers[0], 'kept' => $numbers[1], 'collected' => $numbers[2]],
            'activity'     => [
                ['تسجيل دخول إلى النظام', 'اليوم 09:12', 'fa-right-to-bracket', 'text-info'],
                ['تعديل بيانات العميل حسن زينب الهواري', 'اليوم 10:22', 'fa-pen-to-square', 'text-warning'],
                ['تسجيل وعد دفع جديد', 'أمس 15:12', 'fa-handshake', 'text-brand'],
                ['تصدير تقرير العملاء', 'قبل يومين', 'fa-file-export', 'text-accent'],
            ],
        ]);
    }

    // GET /users/{user}/edit
    public function edit(int $user): View
    {
        $found = StaticData::user($user);
        abort_if($found->is_system, 403, 'لا يمكن تعديل حساب النظام.');

        return view('users.edit', $this->formOptions() + ['user' => $found]);
    }

    // PUT /users/{user}
    public function update(Request $request, int $user): RedirectResponse
    {
        $found = StaticData::user($user);
        abort_if($found->is_system, 403, 'لا يمكن تعديل حساب النظام.');

        $request->validate($this->rules($found));

        // TODO (DB): $user->update([...]) and only hash the password when it was filled
        return redirect()->route('users.index')->with('success', 'تم تعديل بيانات الموظف (بيانات تجريبية، لم يتم الحفظ).');
    }

    // DELETE /users/{user}
    public function destroy(int $user): RedirectResponse
    {
        abort_if(StaticData::user($user)->is_system, 403, 'لا يمكن حذف حساب النظام.');

        // TODO (DB): $user->delete()  (soft delete)
        return redirect()->route('users.index')->with('success', 'تم حذف الموظف (بيانات تجريبية، لم يتم الحذف).');
    }
}