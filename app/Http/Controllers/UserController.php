<?php

// app/Http/Controllers/UserController.php

namespace App\Http\Controllers;

use App\Support\StaticData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Constants
    |--------------------------------------------------------------------------
    */

    private const STATUSES = [
        'active'    => 'نشط',
        'inactive'  => 'معطل',
        'suspended' => 'موقوف',
    ];

    /*
    |--------------------------------------------------------------------------
    | Shared Form Options
    |--------------------------------------------------------------------------
    */

    /**
     * Dropdown options shared by create/edit forms.
     *
     * TODO (DB):
     * These values will eventually come from the real database.
     */
    private function formOptions(): array
    {
        $roles = StaticData::roles();

        return [
            /*
             * Available roles.
             */
            'roles' => $roles
                ->pluck('label', 'id')
                ->all(),

            /*
             * Only Owner and Supervisors can supervise employees.
             *
             * Role IDs:
             * 1 = Owner
             * 2 = Supervisor
             *
             * TODO (DB):
             * Replace with Role/Permission based query.
             */
            'supervisors' => StaticData::users()
                ->whereIn('role_id', [1, 2])
                ->pluck('name', 'id')
                ->all(),

            /*
             * Available account statuses.
             */
            'statuses' => self::STATUSES,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    private function rules(?object $user = null): array
    {
        $options = $this->formOptions();

        /*
         * Get all existing emails except the current user's email
         * when editing.
         *
         * TODO (DB):
         * Rule::unique('users', 'email')->ignore($user?->id)
         */
        $emails = StaticData::users()
            ->when(
                $user,
                fn (Collection $collection) =>
                    $collection->where('id', '!=', $user->id)
            )
            ->pluck('email')
            ->all();

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',

                // Static equivalent of unique email validation.
                Rule::notIn($emails),

                // TODO (DB):
                // Rule::unique('users', 'email')->ignore($user?->id),
            ],

            'phone' => [
                'required',

                // Egyptian mobile number:
                // 01 + 9 digits = 11 digits.
                'regex:/^01[0-9]{9}$/',
            ],

            'role_id' => [
                'required',
                Rule::in(array_keys($options['roles'])),
            ],

            'supervisor_id' => [
                'nullable',
                Rule::in(array_keys($options['supervisors'])),
            ],

            'status' => [
                'required',
                Rule::in(array_keys(self::STATUSES)),
            ],

            'password' => [
                $user ? 'nullable' : 'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | User Metadata
    |--------------------------------------------------------------------------
    */

    /**
     * Convert the status into presentation metadata.
     *
     * The Blade should not need to know which color belongs
     * to which status.
     */
    private function statusMeta(string $status): array
    {
        return match ($status) {
            'active' => [
                'label' => 'نشط',
                'tone'  => 'success',
                'icon'  => 'fa-circle-check',
            ],

            'inactive' => [
                'label' => 'معطل',
                'tone'  => 'neutral',
                'icon'  => 'fa-circle-pause',
            ],

            'suspended' => [
                'label' => 'موقوف',
                'tone'  => 'danger',
                'icon'  => 'fa-ban',
            ],

            default => [
                'label' => $status ?: 'غير معروف',
                'tone'  => 'neutral',
                'icon'  => 'fa-circle-question',
            ],
        };
    }

    /**
     * Get a safe role object for a user.
     */
    private function roleFor(object $user): ?object
    {
        return StaticData::roles()->firstWhere('id', $user->role_id);
    }

    /**
     * Get role label safely.
     */
    private function roleLabel(object $user): string
    {
        return $this->roleFor($user)?->label
            ?? data_get($user, 'role', 'غير محدد');
    }

    /**
     * Add presentation metadata to users.
     *
     * This does not modify the original StaticData objects.
     */
    private function decorateUsers(Collection $users): Collection
    {
        return $users->map(function ($user) {
            $copy = clone $user;

            $copy->role_label = $this->roleLabel($user);

            $copy->status_meta = $this->statusMeta(
                (string) data_get($user, 'status', '')
            );

            /*
             * Resolve supervisor name.
             */
            $supervisor = null;

            if (data_get($user, 'supervisor_id')) {
                $supervisor = StaticData::users()
                    ->firstWhere('id', $user->supervisor_id);
            }

            $copy->supervisor_name = $supervisor?->name ?? 'بدون مشرف';

            /*
             * Resolve company names.
             */
            $companyIds = collect(
                data_get($user, 'company_ids', [])
            );

            $copy->company_names = StaticData::installmentCompanies()
                ->whereIn('id', $companyIds->all())
                ->pluck('name')
                ->values()
                ->all();

            /*
             * Permission count.
             */
            $role = $this->roleFor($user);

            $copy->role_permission_count = count(
                $role?->permission_ids ?? []
            );

            /*
             * Personal overrides count.
             */
            $copy->override_count = count(
                data_get($user, 'overrides', [])
            );

            /*
             * System account flag.
             */
            $copy->account_type = data_get($user, 'is_system', false)
                ? 'system'
                : 'employee';

            return $copy;
        })->values();
    }

    /*
    |--------------------------------------------------------------------------
    | Index Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Calculate all dashboard-style statistics for the users page.
     */
    private function buildStats(Collection $allUsers): array
    {
        $total = $allUsers->count();

        $active = $allUsers->where('status', 'active')->count();

        $inactive = $allUsers->where('status', 'inactive')->count();

        $suspended = $allUsers->where('status', 'suspended')->count();

        $system = $allUsers
            ->where('is_system', true)
            ->count();

        $employees = $total - $system;

        $withSupervisor = $allUsers
            ->filter(fn ($user) => !empty($user->supervisor_id))
            ->count();

        $withoutSupervisor = $total - $withSupervisor;

        return [
            [
                'label'  => 'إجمالي المستخدمين',
                'value'  => $total,
                'icon'   => 'fa-users',
                'color'  => 'info',
                'hint'   => 'كل الحسابات المسجلة',
            ],

            [
                'label'  => 'حسابات نشطة',
                'value'  => $active,
                'icon'   => 'fa-user-check',
                'color'  => 'brand',
                'hint'   => 'يمكنها استخدام النظام',
            ],

            [
                'label'  => 'موقوفة',
                'value'  => $suspended,
                'icon'   => 'fa-user-lock',
                'color'  => 'warning',
                'hint'   => 'تحتاج إلى مراجعة',
            ],

            [
                'label'  => 'حسابات النظام',
                'value'  => $system,
                'icon'   => 'fa-shield-halved',
                'color'  => 'accent',
                'hint'   => 'لا يمكن تعديلها أو حذفها',
            ],

            [
                'label'  => 'الموظفون',
                'value'  => $employees,
                'icon'   => 'fa-user-tie',
                'color'  => 'info',
                'hint'   => 'الحسابات البشرية',
            ],

            [
                'label'  => 'بإشراف مباشر',
                'value'  => $withSupervisor,
                'icon'   => 'fa-user-group',
                'color'  => 'brand',
                'hint'   => 'مرتبطون بمشرف',
            ],

            [
                'label'  => 'بدون مشرف',
                'value'  => $withoutSupervisor,
                'icon'   => 'fa-user-minus',
                'color'  => 'warning',
                'hint'   => 'مراجعة الهيكل الإداري',
            ],

            [
                'label'  => 'معطل',
                'value'  => $inactive,
                'icon'   => 'fa-user-slash',
                'color'  => 'neutral',
                'hint'   => 'حسابات غير نشطة',
            ],
        ];
    }

    /**
     * Build role statistics.
     */
    private function buildRoleStats(Collection $users): Collection
    {
        return $users
            ->groupBy(fn ($user) => $user->role_id)
            ->map(function (Collection $group, $roleId) {
                $role = StaticData::roles()->firstWhere('id', $roleId);

                return (object) [
                    'id'    => $roleId,
                    'label' => $role?->label
                        ?? $group->first()->role
                        ?? 'غير محدد',
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('count')
            ->values();
    }

    /**
     * Build status statistics.
     */
    private function buildStatusStats(Collection $users): Collection
    {
        return collect(self::STATUSES)
            ->map(function (string $label, string $key) use ($users) {
                return (object) [
                    'key'   => $key,
                    'label' => $label,
                    'count' => $users->where('status', $key)->count(),
                    ...$this->statusMeta($key),
                ];
            })
            ->values();
    }

    /**
     * Build supervisor statistics.
     */
    private function buildSupervisorStats(Collection $users): Collection
    {
        $supervisors = StaticData::users()
            ->whereIn('role_id', [1, 2])
            ->keyBy('id');

        return $users
            ->groupBy('supervisor_id')
            ->map(function (Collection $group, $supervisorId) use ($supervisors) {
                $supervisor = $supervisors->get($supervisorId);

                return (object) [
                    'id'         => $supervisorId,
                    'name'       => $supervisor?->name ?? 'بدون مشرف',
                    'count'      => $group->count(),
                    'is_unassigned' => empty($supervisorId),
                ];
            })
            ->sortByDesc('count')
            ->values();
    }

    /**
     * Build company usage statistics.
     */
    private function buildCompanyStats(Collection $users): Collection
    {
        $companies = StaticData::installmentCompanies();

        return $companies
            ->map(function ($company) use ($users) {
                $count = $users
                    ->filter(function ($user) use ($company) {
                        return in_array(
                            $company->id,
                            data_get($user, 'company_ids', []),
                            true
                        );
                    })
                    ->count();

                return (object) [
                    'id'    => $company->id,
                    'name'  => $company->name,
                    'count' => $count,
                ];
            })
            ->sortByDesc('count')
            ->values();
    }

    /**
     * Build a compact overview for the Blade.
     */
    private function buildOverview(Collection $users): array
    {
        return [
            'total' => $users->count(),

            'active_percentage' => $users->count() > 0
                ? round(
                    ($users->where('status', 'active')->count() / $users->count()) * 100
                )
                : 0,

            'supervised_percentage' => $users->count() > 0
                ? round(
                    ($users->filter(fn ($user) => !empty($user->supervisor_id))->count()
                        / $users->count()) * 100
                )
                : 0,

            'companies_used' => $users
                ->pluck('company_ids')
                ->flatten()
                ->unique()
                ->count(),

            'roles_used' => $users
                ->pluck('role_id')
                ->unique()
                ->count(),
        ];
    }

    /**
     * Static activity examples used by the index page.
     *
     * TODO (DB):
     * Replace with ActivityLog/User relationships.
     */
    private function recentActivity(): Collection
    {
        return collect([
            [
                'user'  => 'Ahmed Mohamed',
                'text'  => 'تسجيل دخول إلى النظام',
                'time'  => 'منذ 5 دقائق',
                'icon'  => 'fa-right-to-bracket',
                'color' => 'text-info',
            ],

            [
                'user'  => 'Hassan Ali',
                'text'  => 'تعديل صلاحيات موظف',
                'time'  => 'منذ 18 دقيقة',
                'icon'  => 'fa-user-shield',
                'color' => 'text-warning',
            ],

            [
                'user'  => 'Sara Ahmed',
                'text'  => 'تسجيل وعد دفع جديد',
                'time'  => 'منذ 42 دقيقة',
                'icon'  => 'fa-handshake',
                'color' => 'text-brand',
            ],

            [
                'user'  => 'Mohamed Samir',
                'text'  => 'تصدير تقرير العملاء',
                'time'  => 'منذ ساعة',
                'icon'  => 'fa-file-export',
                'color' => 'text-accent',
            ],

            [
                'user'  => 'System',
                'text'  => 'تم إيقاف حساب بعد محاولات دخول متعددة',
                'time'  => 'منذ ساعتين',
                'icon'  => 'fa-shield-halved',
                'color' => 'text-danger',
            ],

            [
                'user'  => 'Nour Adel',
                'text'  => 'إضافة موظف جديد',
                'time'  => 'اليوم 11:20',
                'icon'  => 'fa-user-plus',
                'color' => 'text-brand',
            ],

            [
                'user'  => 'Omar Khaled',
                'text'  => 'تعديل بيانات الموظف',
                'time'  => 'اليوم 10:05',
                'icon'  => 'fa-user-pen',
                'color' => 'text-warning',
            ],

            [
                'user'  => 'Admin',
                'text'  => 'تغيير حالة موظف إلى موقوف',
                'time'  => 'أمس 16:40',
                'icon'  => 'fa-user-lock',
                'color' => 'text-danger',
            ],
        ])->map(fn (array $item) => (object) $item);
    }

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    // GET /users?search=&role=&status=
    public function index(Request $request): View
    {
        /*
         * Read filters.
         */
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'role'   => (string) $request->query('role', ''),
            'status' => (string) $request->query('status', ''),
        ];

        /*
         * Load all static users once.
         *
         * TODO (DB):
         * This becomes User::query().
         */
        $allUsers = StaticData::users()->values();

        /*
         * Decorate the users once.
         */
        $decoratedUsers = $this->decorateUsers($allUsers);

        /*
         * Apply search.
         */
        $users = $decoratedUsers
            ->when(
                $filters['search'] !== '',
                function (Collection $collection) use ($filters) {
                    $needle = mb_strtolower($filters['search']);

                    return $collection->filter(function ($user) use ($needle) {
                        $searchable = implode(' ', [
                            data_get($user, 'code', ''),
                            data_get($user, 'name', ''),
                            data_get($user, 'email', ''),
                            data_get($user, 'phone', ''),
                            data_get($user, 'role_label', ''),
                            data_get($user, 'supervisor_name', ''),
                        ]);

                        return str_contains(
                            mb_strtolower($searchable),
                            $needle
                        );
                    });
                }
            )

            /*
             * Role filter.
             */
            ->when(
                $filters['role'] !== '',
                function (Collection $collection) use ($filters) {
                    return $collection->filter(function ($user) use ($filters) {
                        return (string) data_get($user, 'role', '')
                            === $filters['role']
                            ||
                            (string) data_get($user, 'role_id', '')
                            === $filters['role'];
                    });
                }
            )

            /*
             * Status filter.
             */
            ->when(
                $filters['status'] !== '',
                fn (Collection $collection) =>
                    $collection->where('status', $filters['status'])
            )

            ->values();

        /*
         * Role dropdown.
         */
        $roles = StaticData::roles()
            ->pluck('label')
            ->all();

        /*
         * Build all page analytics from the complete dataset,
         * not only the filtered result.
         */
        $stats = $this->buildStats($allUsers);

        $roleStats = $this->buildRoleStats($allUsers);

        $statusStats = $this->buildStatusStats($allUsers);

        $supervisorStats = $this->buildSupervisorStats($allUsers);

        $companyStats = $this->buildCompanyStats($allUsers);

        $overview = $this->buildOverview($allUsers);

        /*
         * Extra values for the Blade.
         */
        $systemUsers = $allUsers
            ->where('is_system', true)
            ->values();

        $employeeUsers = $allUsers
            ->where('is_system', false)
            ->values();

        $supervisors = $allUsers
            ->whereIn('role_id', [1, 2])
            ->values();

        /*
         * Count filtered result by status.
         */
        $filteredStatusCounts = [
            'active' => $users->where('status', 'active')->count(),
            'inactive' => $users->where('status', 'inactive')->count(),
            'suspended' => $users->where('status', 'suspended')->count(),
        ];

        /*
         * Count filtered system/employee accounts.
         */
        $filteredAccountCounts = [
            'system' => $users->where('is_system', true)->count(),
            'employees' => $users->where('is_system', false)->count(),
        ];

        return view('users.index', [
            /*
             * Existing contract.
             */
            'users'    => $users,
            'filters'  => $filters,
            'roles'    => $roles,
            'statuses' => self::STATUSES,

            /*
             * New page data.
             */
            'stats' => $stats,

            'roleStats' => $roleStats,

            'statusStats' => $statusStats,

            'supervisorStats' => $supervisorStats,

            'companyStats' => $companyStats,

            'overview' => $overview,

            'allUsersCount' => $allUsers->count(),

            'filteredUsersCount' => $users->count(),

            'systemUsers' => $systemUsers,

            'employeeUsers' => $employeeUsers,

            'supervisors' => $supervisors,

            'filteredStatusCounts' => $filteredStatusCounts,

            'filteredAccountCounts' => $filteredAccountCounts,

            'recentActivity' => $this->recentActivity(),

            /*
             * Useful for future frontend filtering.
             */
            'roleOptions' => StaticData::roles()
                ->map(fn ($role) => (object) [
                    'id'    => $role->id,
                    'label' => $role->label,
                    'count' => $allUsers
                        ->where('role_id', $role->id)
                        ->count(),
                ])
                ->values(),

            /*
             * TODO (DB):
             * Replace with real last-login information.
             */
            'lastLogin' => [
                2 => 'اليوم 09:12',
                3 => 'اليوم 10:48',
                4 => 'أمس 18:21',
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    // GET /users/create
    public function create(): View
    {
        return view(
            'users.create',
            $this->formOptions() + [
                /*
                 * TODO (DB):
                 * Generate the next employee code.
                 */
                'nextCode' => '85600',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    // POST /users
    public function store(Request $request): RedirectResponse
    {
        /*
         * Validate all submitted fields.
         */
        $validated = $request->validate(
            $this->rules()
        );

        /*
         * TODO (DB):
         *
         * User::create([
         *     'name'          => $validated['name'],
         *     'email'         => $validated['email'],
         *     'phone'         => $validated['phone'],
         *     'role_id'       => $validated['role_id'],
         *     'supervisor_id' => $validated['supervisor_id'] ?? null,
         *     'status'        => $validated['status'],
         *     'password'      => Hash::make($validated['password']),
         *     'employee_code' => $nextCode,
         * ]);
         */

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'تمت إضافة الموظف (بيانات تجريبية، لم يتم الحفظ).'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    // GET /users/{user}
    public function show(int $user): View
    {
        /*
         * Find requested user.
         */
        $found = StaticData::user($user);

        /*
         * Resolve role.
         */
        $role = $this->roleFor($found);

        /*
         * All available permissions.
         */
        $permissions = StaticData::permissions();

        /*
         * Calculate final permissions:
         *
         * 1. Start with permissions assigned to the role.
         * 2. Apply personal overrides.
         *
         * true  = explicitly granted
         * false = explicitly revoked
         * null  = use role permission
         */
        $allowedIds = $permissions
            ->filter(function ($permission) use ($role, $found) {
                $override = data_get(
                    $found->overrides ?? [],
                    $permission->name
                );

                return $override === null
                    ? in_array(
                        $permission->id,
                        $role?->permission_ids ?? [],
                        true
                    )
                    : (bool) $override;
            })
            ->pluck('id')
            ->all();

        /*
         * Group permissions by module.
         */
        $modules = $permissions
            ->groupBy('group')
            ->map(fn ($items) => (object) [
                'label'   => $items->first()->group_label,
                'allowed' => $items->whereIn('id', $allowedIds)->count(),
                'total'   => $items->count(),
            ]);

        /*
         * Static performance numbers.
         *
         * TODO (DB):
         * Replace with real cases/payment/activity data.
         */
        $numbers = [
            2 => [0, 3, 19600],
            3 => [3, 1, 4200],
            4 => [3, 2, 15400],
        ][$found->id] ?? [0, 0, 0];

        /*
         * Companies assigned to this user.
         */
        $companies = StaticData::installmentCompanies()
            ->whereIn(
                'id',
                data_get($found, 'company_ids', [])
            )
            ->pluck('name')
            ->values()
            ->all();

        /*
         * User's supervisor.
         */
        $supervisor = null;

        if (!empty($found->supervisor_id)) {
            $supervisor = StaticData::users()
                ->firstWhere('id', $found->supervisor_id);
        }

        /*
         * Status metadata.
         */
        $statusMeta = $this->statusMeta(
            (string) data_get($found, 'status', '')
        );

        /*
         * Permission breakdown.
         */
        $permissionSummary = [
            'allowed' => count($allowedIds),

            'denied' => max(
                0,
                $permissions->count() - count($allowedIds)
            ),

            'total' => $permissions->count(),

            'overrides' => count(
                data_get($found, 'overrides', [])
            ),
        ];

        /*
         * Account metadata.
         */
        $account = [
            'is_system' => (bool) data_get($found, 'is_system', false),

            'type' => data_get($found, 'is_system', false)
                ? 'حساب نظام'
                : 'حساب موظف',

            'can_edit' => !data_get($found, 'is_system', false),

            'can_delete' => !data_get($found, 'is_system', false),

            'supervisor' => $supervisor?->name ?? 'بدون مشرف',
        ];

        return view('users.show', [
            /*
             * Existing values.
             */
            'user'         => $found,
            'role'         => $role,
            'allowedCount' => count($allowedIds),
            'totalCount'   => $permissions->count(),
            'modules'      => $modules,
            'companies'    => $companies,

            'numbers' => [
                'cases'     => $numbers[0],
                'kept'      => $numbers[1],
                'collected' => $numbers[2],
            ],

            /*
             * Existing activity structure preserved.
             */
            'activity' => [
                [
                    'تسجيل دخول إلى النظام',
                    'اليوم 09:12',
                    'fa-right-to-bracket',
                    'text-info',
                ],

                [
                    'تعديل بيانات العميل حسن زينب الهواري',
                    'اليوم 10:22',
                    'fa-pen-to-square',
                    'text-warning',
                ],

                [
                    'تسجيل وعد دفع جديد',
                    'أمس 15:12',
                    'fa-handshake',
                    'text-brand',
                ],

                [
                    'تصدير تقرير العملاء',
                    'قبل يومين',
                    'fa-file-export',
                    'text-accent',
                ],
            ],

            /*
             * New presentation data.
             */
            'statusMeta' => $statusMeta,

            'permissionSummary' => $permissionSummary,

            'account' => $account,

            'supervisor' => $supervisor,

            'roleLabel' => $this->roleLabel($found),

            'companyCount' => count($companies),

            'overrideCount' => count(
                data_get($found, 'overrides', [])
            ),

            /*
             * TODO (DB):
             * Real login/session statistics.
             */
            'loginStats' => [
                'last_login' => 'اليوم 09:12',
                'last_ip' => '10.20.20.145',
                'sessions' => 3,
                'failed_attempts' => 0,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    // GET /users/{user}/edit
    public function edit(int $user): View
    {
        /*
         * Find user.
         */
        $found = StaticData::user($user);

        /*
         * System accounts cannot be edited.
         */
        abort_if(
            $found->is_system,
            403,
            'لا يمكن تعديل حساب النظام.'
        );

        return view(
            'users.edit',
            $this->formOptions() + [
                'user' => $found,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    // PUT /users/{user}
    public function update(
        Request $request,
        int $user
    ): RedirectResponse {
        /*
         * Find user.
         */
        $found = StaticData::user($user);

        /*
         * Protect system accounts.
         */
        abort_if(
            $found->is_system,
            403,
            'لا يمكن تعديل حساب النظام.'
        );

        /*
         * Validate.
         */
        $validated = $request->validate(
            $this->rules($found)
        );

        /*
         * TODO (DB):
         *
         * $found->update([
         *     'name'          => $validated['name'],
         *     'email'         => $validated['email'],
         *     'phone'         => $validated['phone'],
         *     'role_id'       => $validated['role_id'],
         *     'supervisor_id' => $validated['supervisor_id'] ?? null,
         *     'status'        => $validated['status'],
         * ]);
         *
         * if (!empty($validated['password'])) {
         *     $found->update([
         *         'password' => Hash::make($validated['password']),
         *     ]);
         * }
         */

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'تم تعديل بيانات الموظف (بيانات تجريبية، لم يتم الحفظ).'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    // DELETE /users/{user}
    public function destroy(int $user): RedirectResponse
    {
        /*
         * Find user and prevent deleting system account.
         */
        $found = StaticData::user($user);

        abort_if(
            $found->is_system,
            403,
            'لا يمكن حذف حساب النظام.'
        );

        /*
         * TODO (DB):
         *
         * $found->delete();
         *
         * Prefer SoftDeletes for users so historical activity
         * remains available.
         */

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'تم حذف الموظف (بيانات تجريبية، لم يتم الحذف).'
            );
    }
}