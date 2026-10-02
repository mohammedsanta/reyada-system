<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

// System
use Database\Seeders\System\FrameworkSeeder;
use Database\Seeders\System\RolePermissionSeeder;
use Database\Seeders\System\UserSeeder;

// Reference
use Database\Seeders\Reference\BankSeeder;
use Database\Seeders\Reference\InstallmentCompanySeeder;
use Database\Seeders\Reference\BankUserSeeder;
use Database\Seeders\Reference\InstallmentCompanyUserSeeder;
use Database\Seeders\Reference\GovernorateSeeder;
use Database\Seeders\Reference\LoanTypeSeeder;

// Clients
use Database\Seeders\Clients\ClientSeeder;
use Database\Seeders\Clients\ClientPhoneSeeder;

// Portfolios
use Database\Seeders\Portfolios\PortfolioSeeder;
use Database\Seeders\Portfolios\PortfolioImportSeeder;

// Collection
use Database\Seeders\Collection\DebtCaseSeeder;
use Database\Seeders\Collection\CaseAssignmentSeeder;
use Database\Seeders\Collection\CaseInteractionSeeder;
use Database\Seeders\Collection\PromiseToPaySeeder;
use Database\Seeders\Collection\PaymentSeeder;
use Database\Seeders\Collection\VisitSeeder;

// Complaints
use Database\Seeders\Complaints\ComplaintSeeder;

// Reports
use Database\Seeders\Reports\DailyCollectionReportSeeder;
use Database\Seeders\Reports\PerformanceSnapshotSeeder;
use Database\Seeders\Reports\MonthlyArchiveSeeder;
use Database\Seeders\Reports\ReportExportSeeder;

// Audit
use Database\Seeders\Audit\ActivityLogSeeder;
use Database\Seeders\Audit\NotificationSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Laravel framework tables
        |--------------------------------------------------------------------------
        */

        $this->call(
            FrameworkSeeder::class
        );

        /*
        |--------------------------------------------------------------------------
        | 2. Roles and permissions
        |--------------------------------------------------------------------------
        */

        $this->call(
            RolePermissionSeeder::class
        );

        /*
        |--------------------------------------------------------------------------
        | 3. Users
        |--------------------------------------------------------------------------
        */

        $this->call(
            UserSeeder::class
        );

        /*
        |--------------------------------------------------------------------------
        | 4. Reference data
        |--------------------------------------------------------------------------
        */

        $this->call([
            BankSeeder::class,
            InstallmentCompanySeeder::class,
            GovernorateSeeder::class,
            LoanTypeSeeder::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 5. User ↔ institution permissions
        |--------------------------------------------------------------------------
        */

        $this->call([
            BankUserSeeder::class,
            InstallmentCompanyUserSeeder::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 6. Clients
        |--------------------------------------------------------------------------
        */

        $this->call([
            ClientSeeder::class,
            ClientPhoneSeeder::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 7. Portfolios
        |--------------------------------------------------------------------------
        */

        $this->call([
            PortfolioSeeder::class,
            PortfolioImportSeeder::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 8. Debt collection
        |--------------------------------------------------------------------------
        */

        $this->call([
            DebtCaseSeeder::class,
            CaseAssignmentSeeder::class,
            CaseInteractionSeeder::class,
            PromiseToPaySeeder::class,
            PaymentSeeder::class,
            VisitSeeder::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 9. Complaints
        |--------------------------------------------------------------------------
        */

        $this->call(
            ComplaintSeeder::class
        );

        /*
        |--------------------------------------------------------------------------
        | 10. Reports
        |--------------------------------------------------------------------------
        */

        $this->call([
            DailyCollectionReportSeeder::class,
            PerformanceSnapshotSeeder::class,
            MonthlyArchiveSeeder::class,
            ReportExportSeeder::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 11. Audit and notifications
        |--------------------------------------------------------------------------
        */

        $this->call([
            ActivityLogSeeder::class,
            NotificationSeeder::class,
        ]);
    }
}