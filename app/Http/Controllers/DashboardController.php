<?php

// app/Http/Controllers/DashboardController.php  (REPLACES the old one; static data)
// The main screen: money KPIs, today's work, charts, banks, PTP, alerts, top employees, latest payments and activity.

namespace App\Http\Controllers;

use App\Support\StaticBanks;
use App\Support\StaticData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const WEEKDAYS = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
    private const MONTHS   = [1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل', 5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس', 9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'];

    // Numbers that change with the period switch.  TODO (DB): sum(payments.amount) between the period dates
    private const PERIODS = [
        'today' => ['label' => 'اليوم',   'collected' => 9450,    'target' => 12000,   'delta' => 8.4,  'rate_delta' => 2.1, 'spark' => [5.2, 7.8, 6.1, 8.9, 7.4, 10.2, 9.45], 'vs' => 'مقارنة بالأمس'],
        'week'  => ['label' => 'الأسبوع', 'collected' => 52300,   'target' => 60000,   'delta' => 5.1,  'rate_delta' => 1.4, 'spark' => [38, 44, 41, 47, 50, 49, 52.3],         'vs' => 'مقارنة بالأسبوع الماضي'],
        'month' => ['label' => 'الشهر',   'collected' => 195900,  'target' => 240000,  'delta' => 10.0, 'rate_delta' => 3.2, 'spark' => [120, 138, 151, 164, 178, 195.9],       'vs' => 'مقارنة بالشهر الماضي'],
        'year'  => ['label' => 'السنة',   'collected' => 1720000, 'target' => 2400000, 'delta' => 14.6, 'rate_delta' => 4.8, 'spark' => [900, 1050, 1180, 1330, 1500, 1720],   'vs' => 'مقارنة بالسنة الماضية'],
    ];

    /** Link to a page only if its route already exists, so the dashboard never breaks while pages are added one by one. */
    private function link(string $name, array $params = []): string
    {
        return Route::has($name) ? route($name, $params) : '#';
    }

    // GET /dashboard?period=today|week|month|year
    public function __invoke(Request $request): View
    {
        $period = array_key_exists((string) $request->query('period'), self::PERIODS) ? $request->query('period') : 'month';
        $p      = self::PERIODS[$period];
        $now    = now();

        // ---- banks (month to date). TODO (DB): group debt_cases / payments by bank ----
        $figures = [1 => [6, 42000, 15400], 2 => [48, 310000, 98000], 3 => [31, 205000, 61500], 4 => [0, 0, 0], 5 => [12, 95000, 21000]];   // bank id => cases, portfolio, collected

        $banks = StaticBanks::all()->map(function ($bank) use ($figures) {
            [$bank->cases, $bank->portfolio, $bank->collected] = $figures[$bank->id];
            $bank->rate = $bank->portfolio > 0 ? (int) round($bank->collected / $bank->portfolio * 100) : 0;
            $bank->url  = route('banks.panel', $bank->id);

            return $bank;
        })->sortByDesc('portfolio')->values();

        $portfolio      = $banks->sum('portfolio');
        $monthCollected = $banks->sum('collected');
        $remaining      = $portfolio - $monthCollected;
        $achievement    = $p['target'] > 0 ? round($p['collected'] / $p['target'] * 100, 1) : 0;

        // ---- top employees. TODO (DB): performance_snapshots ordered by collected_amount ----
        $performance = [4 => [15400, 68, 3], 3 => [4200, 31, 3], 2 => [0, 0, 0]];   // user id => collected, efficiency %, cases

        $leaders = StaticData::employees()->map(function ($user) use ($performance) {
            [$user->collected, $user->efficiency, $user->cases] = $performance[$user->id] ?? [0, 0, 0];
            $user->tone = $user->efficiency >= 70 ? 'brand' : ($user->efficiency >= 40 ? 'warning' : 'danger');
            $user->url  = $this->link('employees.show', ['code' => $user->code]);

            return $user;
        })->sortByDesc('collected')->values();

        return view('dashboard.index', [
            'period'     => $period,
            'periods'    => collect(self::PERIODS)->map(fn ($x) => $x['label'])->all(),
            'vs'         => $p['vs'],
            'userName'   => auth()->user()?->name ?? 'Test Account',
            'greeting'   => $now->hour < 12 ? 'صباح الخير' : 'مساء الخير',
            'todayLabel' => self::WEEKDAYS[$now->dayOfWeek] . '، ' . $now->day . ' ' . self::MONTHS[$now->month] . ' ' . $now->year,
            'updatedAt'  => $now->format('H:i'),

            // [title, hint, icon, color, url]
            'quick' => [
                ['استيراد نطاق',    'رفع ملف Excel جديد', 'fa-file-arrow-up', 'cyan',    $this->link('banks.scope.import', ['bank' => 1])],
                ['توزيع الحالات',   'على الموظفين',       'fa-sitemap',       'accent',  $this->link('banks.distribution.index', ['bank' => 1])],
                ['تأكيد التحصيلات', 'مراجعة الإيصالات',   'fa-circle-check',  'brand',   $this->link('confirmations.index')],
                ['إضافة موظف',      'حساب جديد',          'fa-user-plus',     'warning', $this->link('users.create')],
            ],

            // money cards: value + trend badge + small trend line
            'kpis' => [
                ['label' => 'إجمالي المحفظة', 'value' => number_format($portfolio), 'unit' => 'EGP', 'icon' => 'fa-wallet', 'color' => 'info',
                 'delta' => 3.2, 'good_up' => true, 'spark' => [540, 575, 590, 610, 630, $portfolio / 1000], 'hint' => 'مقارنة بالشهر الماضي', 'href' => null],

                ['label' => 'المحصل (' . $p['label'] . ')', 'value' => number_format($p['collected']), 'unit' => 'EGP', 'icon' => 'fa-money-bill-wave', 'color' => 'brand',
                 'delta' => $p['delta'], 'good_up' => true, 'spark' => $p['spark'], 'hint' => $p['vs'], 'href' => null],

                ['label' => 'تحقيق المستهدف', 'value' => $achievement . '%', 'unit' => null, 'icon' => 'fa-bullseye', 'color' => 'accent',
                 'delta' => $p['rate_delta'], 'good_up' => true, 'spark' => [60, 66, 63, 70, 74, $achievement], 'hint' => 'المستهدف ' . number_format($p['target']) . ' EGP', 'href' => null],

                // a DECREASE is good news here, so good_up = false
                ['label' => 'المتبقي للتحصيل', 'value' => number_format($remaining), 'unit' => 'EGP', 'icon' => 'fa-hourglass-half', 'color' => 'warning',
                 'delta' => -4.1, 'good_up' => false, 'spark' => [470, 480, 472, 466, 460, $remaining / 1000], 'hint' => 'مقارنة بالشهر الماضي', 'href' => null],
            ],

            // things that need attention now: each card opens the page that solves it
            'attention' => [
                ['label' => 'تحصيلات بانتظار التأكيد', 'value' => 3, 'hint' => '4,150 EGP',     'icon' => 'fa-circle-check', 'color' => 'warning', 'href' => $this->link('confirmations.index')],
                ['label' => 'وعود مستحقة اليوم',        'value' => 1, 'hint' => '2,000 EGP',     'icon' => 'fa-handshake',    'color' => 'orange',  'href' => $this->link('banks.ptp.index', ['bank' => 1, 'scope' => 'today'])],
                ['label' => 'حالات غير موزعة',          'value' => 2, 'hint' => 'تحتاج توزيع',   'icon' => 'fa-sitemap',      'color' => 'accent',  'href' => $this->link('banks.distribution.index', ['bank' => 1])],
                ['label' => 'شكاوى مفتوحة',             'value' => 3, 'hint' => 'منها 1 عاجلة',  'icon' => 'fa-headset',      'color' => 'pink',    'href' => $this->link('banks.complaints.index', ['bank' => 1])],
            ],

            // charts
            'trend'   => ['labels' => ['Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'], 'collected' => [120, 138, 151, 164, 178, 195.9], 'target' => [150, 160, 170, 190, 210, 240]],
            'split'   => ['labels' => ['محصل', 'متبقي'], 'data' => [$monthCollected, $remaining]],
            'buckets' => ['labels' => ['الشريحة 0', 'الشريحة 1', 'الشريحة 2', 'الشريحة 3+'], 'data' => [12, 35, 40, 13]],

            'banks'  => $banks,
            'totals' => ['cases' => $banks->sum('cases'), 'portfolio' => $portfolio, 'collected' => $monthCollected],

            // today's work: [text, count, icon, icon color class, url]
            'agenda' => [
                ['زيارات ميدانية اليوم',        2, 'fa-person-walking', 'text-cyan',    $this->link('banks.visits.index', ['bank' => 1])],
                ['تقارير DCR بانتظار الاعتماد', 1, 'fa-file-invoice',   'text-danger',  $this->link('banks.dcr.index', ['bank' => 1])],
                ['شكاوى ينتهي موعدها اليوم',    1, 'fa-headset',        'text-pink',    $this->link('banks.complaints.index', ['bank' => 1, 'status' => 'open'])],
                ['متابعات هاتفية مجدولة',       5, 'fa-phone',          'text-info',    '#'],
                ['وعود تحتاج مراجعة',           1, 'fa-hourglass-half', 'text-warning', $this->link('banks.ptp.index', ['bank' => 1])],
            ],

            // PTP summary: rows = [label, count, bar color class]
            'ptp' => [
                'rows'  => [['وعود نشطة', 3, 'bg-warning'], ['قيد المراجعة', 1, 'bg-cyan'], ['محقق كلياً', 1, 'bg-brand'], ['محقق جزئياً', 1, 'bg-info'], ['مكسور', 1, 'bg-danger']],
                'total' => 7,
                'rate'  => 33,
                'url'   => $this->link('banks.ptp.index', ['bank' => 1]),
            ],

            // alerts: [icon, tone (danger|warning), text, url]
            'alerts' => [
                ['fa-triangle-exclamation', 'danger',  'وعد دفع مكسور لعميل متعثر (حسن زينب الهواري) منذ 10 أيام', $this->link('banks.ptp.index', ['bank' => 1])],
                ['fa-headset',              'danger',  'شكوى عاجلة: اتصالات خارج المواعيد (CMP-000102)',            $this->link('banks.complaints.index', ['bank' => 1])],
                ['fa-calendar-xmark',       'warning', 'وعد متأخر يوماً واحداً: منى هبة صالح',                       $this->link('banks.ptp.index', ['bank' => 1, 'scope' => 'overdue'])],
                ['fa-user-xmark',           'warning', 'حالتان بدون موظف في Emirates NBD',                          $this->link('banks.distribution.index', ['bank' => 1])],
            ],

            'leaders' => $leaders,

            // latest payments: [receipt, client, amount, method, time, status]
            'payments' => [
                ['#4092', 'حسن زينب الهواري', 1500, 'cash',          'اليوم 10:20', 'pending'],
                ['#4091', 'منى هبة صالح',     450,  'e_wallet',      'اليوم 09:45', 'pending'],
                ['#4090', 'إيمان هبة راضي',   2200, 'bank_transfer', 'أمس 16:10',   'pending'],
                ['#4089', 'أحمد يوسف توفيق',  1500, 'cash',          'أمس 12:30',   'confirmed'],
                ['#4088', 'فاطمة زينب السيد', 400,  'cash',          'قبل يومين',   'confirmed'],
            ],
            'methods' => ['cash' => 'نقدي', 'e_wallet' => 'محفظة', 'bank_transfer' => 'تحويل'],

            // activity: [user, text, time, icon, icon color class]
            'activity' => [
                ['Beshoy nople', 'اعتمد تقرير DCR الخاص بـ HOOL',        'اليوم 12:10', 'fa-file-circle-check', 'text-brand'],
                ['Beshoy nople', 'وزّع 24 حالة على 3 موظفين',            'اليوم 10:05', 'fa-sitemap',           'text-accent'],
                ['Test Account', 'استورد نطاق Emirates NBD (6 حالات)',   'اليوم 09:30', 'fa-file-arrow-up',     'text-cyan'],
                ['Ahmed Borlsy', 'سجّل وعد دفع جديد لمنى هبة صالح',      'أمس 15:12',   'fa-handshake',         'text-warning'],
                ['HOOL',         'عدّل بيانات العميل حسن زينب الهواري',  'أمس 10:22',   'fa-pen-to-square',     'text-info'],
            ],

            'links' => [
                'banks'         => $this->link('banks.index'),
                'employees'     => $this->link('employees.index'),
                'confirmations' => $this->link('confirmations.index'),
                'activity'      => $this->link('activity-logs.index'),
            ],
        ]);
    }
}