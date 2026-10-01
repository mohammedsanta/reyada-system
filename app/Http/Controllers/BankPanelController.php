<?php

// app/Http/Controllers/BankPanelController.php

namespace App\Http\Controllers;

use App\Support\StaticBanks;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class BankPanelController extends Controller
{
    /**
     * The tiles of the scope control panel, in visual order (first = right side in RTL).
     * `route` = route name that receives the bank id. Tiles whose route does not exist yet
     * are shown as "قريباً" (coming soon) and light up automatically once you add the route.
     */
    private function tiles(): array
    {
        return [
            ['title' => 'استيراد النطاق',     'description' => 'رفع ملفات Excel وتحديث البيانات',                'icon' => 'fa-file-arrow-up',   'color' => 'cyan',    'route' => 'banks.scope.import'],
            ['title' => 'عرض النطاق',         'description' => 'استعراض العملاء وبياناتهم مع البحث والتصفية',     'icon' => 'fa-eye',             'color' => 'brand',   'route' => 'banks.show'],
            ['title' => 'تعديل النطاق',       'description' => 'تعديل البيانات الحالية وتصحيح المدخلات',         'icon' => 'fa-pen-to-square',   'color' => 'orange',  'route' => 'banks.scope.edit'],

            ['title' => 'توزيع الحالات',      'description' => 'توزيع الحالات على الموظفين',                     'icon' => 'fa-sitemap',         'color' => 'accent',  'route' => 'banks.distribution.index'],
            ['title' => 'مركز الـ PTP',       'description' => 'متابعة وعود الدفع وإدارتها بشكل لحظي',           'icon' => 'fa-calendar-check',  'color' => 'warning', 'route' => 'banks.ptp.index'],
            ['title' => 'DCR',                'description' => 'متابعة تقارير التحصيل اليومية والأداء',          'icon' => 'fa-file-invoice',    'color' => 'danger',  'route' => 'banks.dcr.index'],

            ['title' => 'إدارة الشكاوى',      'description' => 'مراجعة وتقييم شكاوى العملاء',                    'icon' => 'fa-headset',         'color' => 'pink',    'route' => 'banks.complaints.index'],
            ['title' => 'إدارة الزيارات',     'description' => 'تكليف ومتابعة الزيارات الميدانية للمحصلين',      'icon' => 'fa-person-walking',  'color' => 'brand',   'route' => 'banks.visits.index'],
            ['title' => 'النطاقات المؤرشفة',  'description' => 'استعراض النطاقات السابقة (غير قابلة للتعديل)',   'icon' => 'fa-box-archive',     'color' => 'muted',   'route' => 'banks.archives.index'],
        ];
    }

    // GET /banks/{bank}/panel
    public function __invoke(int $bank): View
    {
        $bank = StaticBanks::find($bank);

        $tiles = collect($this->tiles())->map(function (array $tile) use ($bank) {
            $tile['href'] = Route::has($tile['route']) ? route($tile['route'], $bank->id) : null;

            return $tile;
        });

        return view('banks.panel', [
            'bank'  => $bank,
            'tiles' => $tiles,
        ]);
    }
}