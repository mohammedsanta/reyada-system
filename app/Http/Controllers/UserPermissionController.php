<?php

// app/Http/Controllers/UserPermissionController.php  (static data)
// Extra permissions for ONE employee on top of his role.
// Final permission = (role has it AND not revoked) OR granted.

namespace App\Http\Controllers;

use App\Support\StaticData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserPermissionController extends Controller
{
    private function employee(int $id): object
    {
        $user = StaticData::user($id);
        abort_if($user->is_system, 403, 'صلاحيات حساب النظام كاملة ولا يمكن تعديلها.');

        return $user;
    }

    private function role(object $user): object
    {
        return StaticData::roles()->firstWhere('id', $user->role_id);
    }

    // GET /users/{user}/permissions
    public function edit(int $user): View
    {
        $employee = $this->employee($user);
        $role     = $this->role($employee);

        $groups = StaticData::permissions()
            ->map(function ($permission) use ($role, $employee) {
                $permission->from_role = in_array($permission->id, $role->permission_ids, true);
                $permission->override  = ! array_key_exists($permission->name, $employee->overrides)
                    ? 'inherit'
                    : ($employee->overrides[$permission->name] ? 'grant' : 'revoke');

                return $permission;
            })
            ->groupBy('group')
            ->map(fn ($items) => (object) ['label' => $items->first()->group_label, 'items' => $items]);

        return view('users.permissions', ['user' => $employee, 'role' => $role, 'groups' => $groups]);
    }

    // PUT /users/{user}/permissions
    public function update(Request $request, int $user): RedirectResponse
    {
        $employee = $this->employee($user);
        $role     = $this->role($employee);

        $request->validate([
            'override'   => ['required', 'array'],
            'override.*' => ['in:inherit,grant,revoke'],
        ]);

        $grants = $revokes = 0;

        foreach (StaticData::permissions() as $permission) {
            $choice  = $request->input("override.{$permission->id}", 'inherit');
            $hasRole = in_array($permission->id, $role->permission_ids, true);

            if ($choice === 'grant' && ! $hasRole)  $grants++;    // an extra permission
            if ($choice === 'revoke' && $hasRole)   $revokes++;   // a permission taken away
        }

        // TODO (DB): rebuild permission_user for this user: only rows that differ from the role (granted = true / false)
        return redirect()
            ->route('users.index')
            ->with('success', "تم تحديث صلاحيات {$employee->name}: منح {$grants}، سحب {$revokes} (بيانات تجريبية، لم يتم الحفظ).");
    }
}