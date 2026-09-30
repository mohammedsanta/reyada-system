<?php

// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Single-action controller: GET /dashboard
     * All data below is STATIC until the database is ready.
     */
    public function __invoke(): View
    {
        return view('dashboard.index', [
            'userName'     => auth()->user()?->name ?? 'Test Account',
            'stats'        => $this->stats(),
            'monthlyChart' => $this->monthlyChart(),
            'portfolio'    => $this->portfolio(),
            'alerts'       => $this->brokenPromises(),
            'transactions' => $this->recentTransactions(),
        ]);
    }

    private function stats(): array
    {
        return [
            ['label' => 'إجمالي الفواتير المدفوعة', 'value' => '0.0M',  'unit' => 'EGP'],
            ['label' => 'محفظة الفواتير المفتوحة',  'value' => '0.00M', 'unit' => 'EGP'],
            ['label' => 'المحصلين النشطين',         'value' => '6',     'unit' => 'موظف'],
            ['label' => 'المعاملات المعلقة',        'value' => '1',     'unit' => 'معاملة'],
        ];
    }

    private function monthlyChart(): array
    {
        return [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'data'   => [10, 15, 12, 18, 20, 85],
        ];
    }

    private function portfolio(): array
    {
        return [
            'labels' => ['محصلة مغلقة', 'مفتوحة نشطة'],
            'data'   => [65, 35],
        ];
    }

    private function brokenPromises(): array
    {
        return [
            ['message' => 'إشعار قيد الأكشن لعميل متعثر', 'badge' => 'متأخر'],
        ];
    }

    private function recentTransactions(): array
    {
        return [
            ['title' => 'إيصال سداد نقدي رقم #4092',   'collector' => 'المحصل: عميل ميداني - فرع القاهرة', 'amount' => 1500],
            ['title' => 'إيصال سداد محفظة إلكترونية', 'collector' => 'المحصل: محفظة ذكية نظامية',         'amount' => 450],
        ];
    }
}