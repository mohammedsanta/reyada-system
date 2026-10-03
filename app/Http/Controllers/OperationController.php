<?php

// app/Http/Controllers/OperationController.php  (static data)
// "مركز العمليات": the work queues of the day, shortcuts for the daily bank operations, and a log of recent operations.

namespace App\Http\Controllers;

use App\Support\StaticBanks;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class OperationController extends Controller
{
    // type => [label, icon, color]
    private const TYPES = [
        'import'       => ['استيراد نطاق',  'fa-file-arrow-up', 'cyan'],
        'distribution' => ['توزيع حالات',   'fa-sitemap',       'accent'],
        'bulk'         => ['تعديل جماعي',   'fa-pen-to-square', 'orange'],
        'confirmation' => ['تأكيد تحصيل',   'fa-circle-check',  'brand'],
        'dcr'          => ['اعتماد DCR',    'fa-file-invoice',  'danger'],
    ];

    /** Link to a page only if its route exists, so this page never breaks while pages are added one by one. */
    private function link(string $name, array $params = []): string
    {
        return Route::has($name) ? route($name, $params) : '#';
    }

    /** TODO (DB): activity_logs filtered by operation events, with the user and the bank. */
    private function operations(): Collection
    {
        // type, text, bank, user, time, status
        $rows = [
            ['confirmation', 'تأكيد إيصال #4089 بمبلغ 1,500 EGP',   'Emirates NBD',        'Beshoy nople', 'اليوم 12:30',  'completed'],
            ['dcr',          'اعتماد تقرير DCR الخاص بـ HOOL',       'Emirates NBD',        'Beshoy nople', 'اليوم 12:10',  'completed'],
            ['distribution', 'توزيع 24 حالة على 3 موظفين',            'Emirates NBD',        'Beshoy nople', 'اليوم 10:05',  'completed'],
            ['import',       'استيراد scope_september.xlsx (6 حالات)', 'Emirates NBD',       'Test Account', 'اليوم 09:30',  'completed'],
            ['bulk',         'تغيير الموظف المسؤول لـ 2 حالة',        'Emirates NBD',        'Beshoy nople', 'أمس 16:40',    'completed'],
            ['import',       'استيراد scope_sept_bm.xlsx',            'بنك مصر',             'Test Account', 'أمس 15:05',    'processing'],
            ['confirmation', 'رفض إيصال #4087 (بيانات غير مطابقة)',   'Emirates NBD',        'Beshoy nople', 'قبل يومين',    'failed'],
            ['distribution', 'توزيع 6 حالات حسب السعة المتاحة',       'البنك التجاري الدولي', 'Test Account', 'قبل يومين',    'completed'],
        ];

        return collect($rows)->map(fn (array $r) => (object) array_combine(['type', 'text', 'bank', 'user', 'time', 'status'], $r));
    }

    // GET /operations?bank=&type=
    public function __invoke(Request $request): View
    {
        $banks = StaticBanks::all();
        $bank  = $banks->firstWhere('id', (int) $request->query('bank', 1)) ?? $banks->first();
        $type  = array_key_exists((string) $request->query('type'), self::TYPES) ? $request->query('type') : '';

        $all = $this->operations();

        return view('operations.index', [
            'bank'        => $bank,
            'bankOptions' => $banks->pluck('name', 'id')->all(),
            'type'        => $type,
            'types'       => self::TYPES,
            'counts'      => $all->countBy('type')->all() + ['all' => $all->count()],
            'operations'  => $all->when($type !== '', fn (Collection $c) => $c->where('type', $type))->values(),

            // work waiting right now: each card opens the page that solves it
            'queues' => [
                ['label' => 'تحصيلات بانتظار التأكيد', 'count' => 3, 'icon' => 'fa-circle-check',   'color' => 'brand',   'url' => $this->link('confirmations.index')],
                ['label' => 'حالات غير موزعة',         'count' => 2, 'icon' => 'fa-sitemap',        'color' => 'accent',  'url' => $this->link('banks.distribution.index', ['bank' => $bank->id])],
                ['label' => 'وعود مستحقة اليوم',       'count' => 1, 'icon' => 'fa-handshake',      'color' => 'warning', 'url' => $this->link('banks.ptp.index', ['bank' => $bank->id, 'scope' => 'today'])],
                ['label' => 'شكاوى مفتوحة',            'count' => 3, 'icon' => 'fa-headset',        'color' => 'pink',    'url' => $this->link('banks.complaints.index', ['bank' => $bank->id])],
                ['label' => 'زيارات اليوم',            'count' => 2, 'icon' => 'fa-person-walking', 'color' => 'cyan',    'url' => $this->link('banks.visits.index', ['bank' => $bank->id])],
            ],

            // shortcuts for the daily operations of the selected bank: [title, hint, icon, color, url]
            'shortcuts' => [
                ['استيراد النطاق',    'رفع ملف Excel الشهري',      'fa-file-arrow-up', 'cyan',    $this->link('banks.scope.import', ['bank' => $bank->id])],
                ['توزيع الحالات',     'على الموظفين',              'fa-sitemap',       'accent',  $this->link('banks.distribution.index', ['bank' => $bank->id])],
                ['تعديل النطاق',      'تعديل جماعي للحالات',       'fa-pen-to-square', 'orange',  $this->link('banks.scope.edit', ['bank' => $bank->id])],
                ['التقرير اليومي DCR', 'اعتماد تقارير الموظفين',   'fa-file-invoice',  'danger',  $this->link('banks.dcr.index', ['bank' => $bank->id])],
                ['إضافة وعد دفع',     'تسجيل وعد جديد',            'fa-handshake',     'warning', $this->link('banks.ptp.create', ['bank' => $bank->id])],
                ['جدولة زيارة',       'زيارة ميدانية لمحصل',       'fa-person-walking', 'brand',  $this->link('banks.visits.index', ['bank' => $bank->id])],
            ],
        ]);
    }
}