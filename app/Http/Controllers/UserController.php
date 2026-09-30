<?php

// app/Http/Controllers/UserController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * STATIC DATA (temporary), replace with User::query() when the DB is ready.
     * `actions` = which icon buttons appear in the row (see the map in the view).
     */
    private function users(): Collection
    {
        return collect([
            [
                'code' => '922885', 'name' => 'Test Account', 'phone' => '0127673938',
                'email' => 'test@collex.com', 'role' => 'Owner', 'status' => 'active',
                'supervisor' => null, 'banks' => [], 'is_system' => true, 'actions' => [],
            ],
            [
                'code' => '85577', 'name' => 'Beshoy nople', 'phone' => '01256987456',
                'email' => 'ggglgogo@example.com', 'role' => 'Super Visor', 'status' => 'active',
                'supervisor' => ['name' => 'المالك', 'is_owner' => true],
                'banks' => ['Emirates NBD'], 'is_system' => false,
                'actions' => ['profile', 'banks', 'permissions', 'edit'],
            ],
            [
                'code' => '85515', 'name' => 'HOOL', 'phone' => '01236987445',
                'email' => 'joniur@managhfgger.com', 'role' => 'Call Center', 'status' => 'active',
                'supervisor' => ['name' => 'Beshoy nople', 'is_owner' => false],
                'banks' => ['Emirates NBD'], 'is_system' => false,
                'actions' => ['profile', 'team', 'banks', 'permissions', 'edit'],
            ],
            [
                'code' => '85392', 'name' => 'Ahmed Borlsy', 'phone' => '01270008000',
                'email' => 'berolsy@collex.com', 'role' => 'Call Center', 'status' => 'active',
                'supervisor' => ['name' => 'Beshoy nople', 'is_owner' => false],
                'banks' => ['Emirates NBD'], 'is_system' => false,
                'actions' => ['profile', 'team', 'banks', 'permissions', 'edit'],
            ],
        ])->map(fn (array $user) => (object) $user);
    }

    // GET /users?search=&role=&status=
    public function index(Request $request): View
    {
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'role'   => (string) $request->query('role', ''),
            'status' => (string) $request->query('status', ''),
        ];

        $users = $this->users()
            ->when($filters['search'] !== '', function (Collection $users) use ($filters) {
                $needle = mb_strtolower($filters['search']);

                return $users->filter(fn ($u) => str_contains(
                    mb_strtolower(implode(' ', [$u->code, $u->name, $u->email, $u->phone])),
                    $needle
                ));
            })
            ->when($filters['role'] !== '',   fn (Collection $users) => $users->where('role', $filters['role']))
            ->when($filters['status'] !== '', fn (Collection $users) => $users->where('status', $filters['status']))
            ->values();

        return view('users.index', [
            'users'    => $users,
            'filters'  => $filters,
            'roles'    => ['Super Visor', 'Call Center'],
            'statuses' => ['active' => 'نشط', 'inactive' => 'معطل'],
        ]);
    }
}