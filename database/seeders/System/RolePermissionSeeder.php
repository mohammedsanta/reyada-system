<?php

namespace Database\Seeders\System;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | PERMISSIONS
        |--------------------------------------------------------------------------
        */

        $permissions = [

            // Users
            ['users.view', 'View Users', 'users'],
            ['users.create', 'Create Users', 'users'],
            ['users.update', 'Update Users', 'users'],
            ['users.delete', 'Delete Users', 'users'],

            // Roles
            ['roles.view', 'View Roles', 'roles'],
            ['roles.create', 'Create Roles', 'roles'],
            ['roles.update', 'Update Roles', 'roles'],
            ['roles.delete', 'Delete Roles', 'roles'],

            // Permissions
            ['permissions.view', 'View Permissions', 'permissions'],
            ['permissions.manage', 'Manage Permissions', 'permissions'],

            // Banks
            ['banks.view', 'View Banks', 'banks'],
            ['banks.create', 'Create Banks', 'banks'],
            ['banks.update', 'Update Banks', 'banks'],
            ['banks.delete', 'Delete Banks', 'banks'],

            // Installment companies
            ['installment_companies.view', 'View Installment Companies', 'installment_companies'],
            ['installment_companies.create', 'Create Installment Companies', 'installment_companies'],
            ['installment_companies.update', 'Update Installment Companies', 'installment_companies'],
            ['installment_companies.delete', 'Delete Installment Companies', 'installment_companies'],

            // Clients
            ['clients.view', 'View Clients', 'clients'],
            ['clients.create', 'Create Clients', 'clients'],
            ['clients.update', 'Update Clients', 'clients'],
            ['clients.delete', 'Delete Clients', 'clients'],

            // Portfolios
            ['portfolios.view', 'View Portfolios', 'portfolios'],
            ['portfolios.create', 'Create Portfolios', 'portfolios'],
            ['portfolios.update', 'Update Portfolios', 'portfolios'],
            ['portfolios.delete', 'Delete Portfolios', 'portfolios'],
            ['portfolios.import', 'Import Portfolio', 'portfolios'],
            ['portfolios.archive', 'Archive Portfolio', 'portfolios'],

            // Cases
            ['cases.view', 'View Debt Cases', 'cases'],
            ['cases.create', 'Create Debt Cases', 'cases'],
            ['cases.update', 'Update Debt Cases', 'cases'],
            ['cases.delete', 'Delete Debt Cases', 'cases'],
            ['cases.assign', 'Assign Debt Cases', 'cases'],

            // Collection
            ['interactions.manage', 'Manage Interactions', 'collection'],
            ['ptp.view', 'View Promises To Pay', 'collection'],
            ['ptp.manage', 'Manage Promises To Pay', 'collection'],
            ['payments.view', 'View Payments', 'collection'],
            ['payments.create', 'Create Payments', 'collection'],
            ['payments.confirm', 'Confirm Payments', 'collection'],
            ['payments.reject', 'Reject Payments', 'collection'],
            ['visits.view', 'View Visits', 'collection'],
            ['visits.manage', 'Manage Visits', 'collection'],

            // Complaints
            ['complaints.view', 'View Complaints', 'complaints'],
            ['complaints.manage', 'Manage Complaints', 'complaints'],
            ['complaints.resolve', 'Resolve Complaints', 'complaints'],

            // Reports
            ['reports.view', 'View Reports', 'reports'],
            ['reports.export', 'Export Reports', 'reports'],
            ['reports.approve', 'Approve Reports', 'reports'],

            // Activity
            ['activity.view', 'View Activity Logs', 'activity'],
        ];

        foreach ($permissions as [$name, $label, $group]) {
            DB::table('permissions')->insert([
                'name' => $name,
                'label' => $label,
                'group' => $group,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | ROLES
        |--------------------------------------------------------------------------
        */

        $owner = DB::table('roles')->insertGetId([
            'name' => 'owner',
            'label' => 'Owner',
            'description' => 'Full system access.',
            'is_system' => true,
            'level' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $supervisor = DB::table('roles')->insertGetId([
            'name' => 'super_visor',
            'label' => 'Super Visor',
            'description' => 'Supervises collection employees.',
            'is_system' => true,
            'level' => 50,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $callCenter = DB::table('roles')->insertGetId([
            'name' => 'call_center',
            'label' => 'Call Center',
            'description' => 'Collection employee.',
            'is_system' => true,
            'level' => 20,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | ROLE PERMISSIONS
        |--------------------------------------------------------------------------
        */

        $allPermissions = DB::table('permissions')
            ->pluck('id', 'name');

        // Owner gets everything.
        foreach ($allPermissions as $permissionId) {
            DB::table('permission_role')->insert([
                'role_id' => $owner,
                'permission_id' => $permissionId,
            ]);
        }

        // Supervisor.
        $supervisorPermissions = [
            'users.view',
            'clients.view',
            'clients.create',
            'clients.update',
            'banks.view',
            'installment_companies.view',
            'portfolios.view',
            'portfolios.import',
            'cases.view',
            'cases.create',
            'cases.update',
            'cases.assign',
            'interactions.manage',
            'ptp.view',
            'ptp.manage',
            'payments.view',
            'payments.confirm',
            'payments.reject',
            'visits.view',
            'visits.manage',
            'complaints.view',
            'complaints.manage',
            'complaints.resolve',
            'reports.view',
            'reports.approve',
            'activity.view',
        ];

        foreach ($supervisorPermissions as $name) {
            DB::table('permission_role')->insert([
                'role_id' => $supervisor,
                'permission_id' => $allPermissions[$name],
            ]);
        }

        // Call center.
        $collectorPermissions = [
            'clients.view',
            'clients.update',
            'banks.view',
            'installment_companies.view',
            'portfolios.view',
            'cases.view',
            'cases.update',
            'interactions.manage',
            'ptp.view',
            'ptp.manage',
            'payments.view',
            'payments.create',
            'visits.view',
            'visits.manage',
            'complaints.view',
        ];

        foreach ($collectorPermissions as $name) {
            DB::table('permission_role')->insert([
                'role_id' => $callCenter,
                'permission_id' => $allPermissions[$name],
            ]);
        }
    }
}