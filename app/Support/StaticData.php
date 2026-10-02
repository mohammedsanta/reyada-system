<?php

// app/Support/StaticData.php
// One shared place for sample data (users, roles, permissions, companies, clients).
// TODO (DB): delete this file and use the Eloquent models.

namespace App\Support;

use Illuminate\Support\Collection;

class StaticData
{
    private const ROLE_LABELS = [1 => 'Owner', 2 => 'Super Visor', 3 => 'Call Center'];

    /** Permission groups: group => [Arabic label, actions]. Final name = "group.action" (e.g. banks.view). */
    public static function permissionGroups(): array
    {
        return [
            'users'      => ['label' => 'المستخدمون',       'actions' => ['view' => 'عرض', 'create' => 'إضافة', 'edit' => 'تعديل', 'delete' => 'حذف']],
            'roles'      => ['label' => 'الرتب والصلاحيات', 'actions' => ['view' => 'عرض', 'manage' => 'إدارة']],
            'banks'      => ['label' => 'البنوك',            'actions' => ['view' => 'عرض', 'manage' => 'إدارة', 'import' => 'استيراد النطاق']],
            'clients'    => ['label' => 'العملاء',           'actions' => ['view' => 'عرض', 'edit' => 'تعديل', 'assign' => 'توزيع الحالات']],
            'ptp'        => ['label' => 'الوعود (PTP)',      'actions' => ['view' => 'عرض', 'manage' => 'إدارة']],
            'payments'   => ['label' => 'التحصيلات',         'actions' => ['view' => 'عرض', 'confirm' => 'تأكيد']],
            'complaints' => ['label' => 'الشكاوى',           'actions' => ['view' => 'عرض', 'manage' => 'إدارة']],
            'visits'     => ['label' => 'الزيارات',          'actions' => ['view' => 'عرض', 'manage' => 'إدارة']],
            'dcr'        => ['label' => 'تقارير DCR',        'actions' => ['view' => 'عرض', 'approve' => 'اعتماد']],
            'reports'    => ['label' => 'التقارير',          'actions' => ['view' => 'عرض', 'export' => 'تصدير']],
            'activity'   => ['label' => 'سجل النشاط',        'actions' => ['view' => 'عرض']],
        ];
    }

    public static function permissions(): Collection
    {
        $id   = 0;
        $rows = [];

        foreach (self::permissionGroups() as $group => $config) {
            foreach ($config['actions'] as $action => $label) {
                $rows[] = (object) [
                    'id' => ++$id, 'name' => "$group.$action", 'label' => $label,
                    'group' => $group, 'group_label' => $config['label'],
                ];
            }
        }

        return collect($rows);
    }

    private static function rawUsers(): array
    {
        // id, code, name, phone, email, role_id, status, supervisor_id, bank_ids, company_ids, is_system, overrides [permission name => granted]
        return [
            [1, '922885', 'Test Account', '0127673938',  'test@collex.com',        1, 'active', null, [],  [], true,  []],
            [2, '85577',  'Beshoy nople', '01256987456', 'ggglgogo@example.com',   2, 'active', 1,    [1], [], false, []],
            [3, '85515',  'HOOL',         '01236987445', 'joniur@managhfgger.com', 3, 'active', 2,    [1], [], false, ['reports.view' => true]],
            [4, '85392',  'Ahmed Borlsy', '01270008000', 'berolsy@collex.com',     3, 'active', 2,    [1], [], false, []],
        ];
    }

    public static function roles(): Collection
    {
        $all = self::permissions();
        $ids = fn (array $names) => $all->whereIn('name', $names)->pluck('id')->values()->all();

        $roles = [
            ['id' => 1, 'name' => 'owner',       'label' => 'Owner',       'description' => 'صلاحيات كاملة على النظام',           'is_system' => true,  'level' => 100,
             'permission_ids' => $all->pluck('id')->all()],
            ['id' => 2, 'name' => 'super_visor', 'label' => 'Super Visor', 'description' => 'يشرف على فريق التحصيل ويوزع الحالات', 'is_system' => false, 'level' => 50,
             'permission_ids' => $all->reject(fn ($p) => in_array($p->name, ['users.delete', 'roles.manage', 'banks.manage']))->pluck('id')->values()->all()],
            ['id' => 3, 'name' => 'call_center', 'label' => 'Call Center', 'description' => 'يتابع العملاء ويسجل الوعود',         'is_system' => false, 'level' => 10,
             'permission_ids' => $ids(['clients.view', 'clients.edit', 'ptp.view', 'ptp.manage', 'complaints.view', 'visits.view', 'dcr.view'])],
        ];

        $usersByRole = collect(self::rawUsers())->groupBy(fn ($u) => $u[5])->map->count();

        return collect($roles)->map(function (array $role) use ($usersByRole) {
            $role['users_count']       = $usersByRole[$role['id']] ?? 0;
            $role['permissions_count'] = count($role['permission_ids']);

            return (object) $role;
        });
    }

    public static function users(): Collection
    {
        $raw   = collect(self::rawUsers());
        $banks = StaticBanks::all()->pluck('name', 'id');

        return $raw->map(function (array $u) use ($raw, $banks) {
            [$id, $code, $name, $phone, $email, $roleId, $status, $supervisorId, $bankIds, $companyIds, $isSystem, $overrides] = $u;

            $supervisor = null;
            if ($supervisorId) {
                $boss       = $raw->first(fn ($r) => $r[0] === $supervisorId);
                $bossIsOwner = $boss[5] === 1;
                $supervisor = ['name' => $bossIsOwner ? 'المالك' : $boss[2], 'is_owner' => $bossIsOwner];
            }

            return (object) [
                'id' => $id, 'code' => $code, 'name' => $name, 'phone' => $phone, 'email' => $email,
                'role_id' => $roleId, 'role' => self::ROLE_LABELS[$roleId], 'status' => $status,
                'supervisor_id' => $supervisorId, 'supervisor' => $supervisor,
                'bank_ids' => $bankIds, 'company_ids' => $companyIds,
                'banks' => collect($bankIds)->map(fn ($b) => $banks[$b])->values()->all(),
                'is_system' => $isSystem,
                'overrides' => $overrides,
                'last_login' => $isSystem ? 'اليوم 09:12' : 'أمس 18:40',
                // icon buttons shown in the users table (Super Visor also gets the team button)
                'actions' => $isSystem ? [] : ($roleId === 2
                    ? ['profile', 'team', 'banks', 'permissions', 'edit']
                    : ['profile', 'banks', 'permissions', 'edit']),
            ];
        });
    }

    public static function user(int $id): object
    {
        return self::users()->firstWhere('id', $id) ?? abort(404);
    }

    /** Everyone except the system account (used in selects: employee, collector...). */
    public static function employees(): Collection
    {
        return self::users()->reject(fn ($u) => $u->is_system)->values();
    }

    public static function installmentCompanies(): Collection
    {
        return collect([
            ['id' => 1, 'name' => 'فاليو',  'code' => 'VALU', 'is_active' => true,  'cases_count' => 120, 'total_debt' => 840000],
            ['id' => 2, 'name' => 'سهولة',  'code' => 'SOHL', 'is_active' => true,  'cases_count' => 64,  'total_debt' => 410000],
            ['id' => 3, 'name' => 'كونتكت', 'code' => 'CNTC', 'is_active' => false, 'cases_count' => 0,   'total_debt' => 0],
        ])->map(fn (array $c) => (object) $c);
    }

    public static function clients(): Collection
    {
        return collect([
            ['id' => 1000001, 'code' => '1000001', 'name' => 'مصطفى خالد حسن',  'phone' => '01080672586', 'address' => '86 شارع عباس العقاد - فيصل - أسيوط'],
            ['id' => 1000002, 'code' => '1000002', 'name' => 'حسن زينب الهواري', 'phone' => '01299928200', 'address' => '82 شارع الهرم - الدقي - سوهاج'],
            ['id' => 1000003, 'code' => '1000003', 'name' => 'منى هبة صالح',     'phone' => '01097389540', 'address' => '58 شارع رمسيس - المعادي - القاهرة'],
            ['id' => 1000004, 'code' => '1000004', 'name' => 'إيمان هبة راضي',   'phone' => '01174820823', 'address' => '123 شارع البطل أحمد عبدالعزيز - شبرا - الإسماعيلية'],
            ['id' => 1000005, 'code' => '1000005', 'name' => 'أحمد يوسف توفيق',  'phone' => '01096103142', 'address' => '147 شارع الهرم - المهندسين - الدقهلية'],
            ['id' => 1000000, 'code' => '1000000', 'name' => 'فاطمة زينب السيد', 'phone' => '01055111338', 'address' => '117 شارع التسعين - المعادي - المنيا'],
        ])->map(fn (array $c) => (object) $c);
    }
}