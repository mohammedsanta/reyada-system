<?php

// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Support\StaticBanks;
use App\Support\StaticData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DASHBOARD CONSTANTS
    |--------------------------------------------------------------------------
    |
    | Everything is static for now.
    | Later each helper method can be replaced with real database queries.
    |
    */

    private const WEEKDAYS = [
        'الأحد',
        'الاثنين',
        'الثلاثاء',
        'الأربعاء',
        'الخميس',
        'الجمعة',
        'السبت',
    ];

    private const MONTHS = [
        1  => 'يناير',
        2  => 'فبراير',
        3  => 'مارس',
        4  => 'أبريل',
        5  => 'مايو',
        6  => 'يونيو',
        7  => 'يوليو',
        8  => 'أغسطس',
        9  => 'سبتمبر',
        10 => 'أكتوبر',
        11 => 'نوفمبر',
        12 => 'ديسمبر',
    ];


    /*
    |--------------------------------------------------------------------------
    | DASHBOARD PERIODS
    |--------------------------------------------------------------------------
    |
    | TODO (DB):
    | Replace these static numbers with calculations from payments,
    | debt cases, targets and portfolio tables.
    |
    */

    private const PERIODS = [

        'today' => [
            'label'      => 'اليوم',
            'collected'  => 9450,
            'target'     => 12000,
            'delta'      => 8.4,
            'rate_delta' => 2.1,
            'spark'      => [5.2, 7.8, 6.1, 8.9, 7.4, 10.2, 9.45],
            'vs'         => 'مقارنة بالأمس',
        ],

        'week' => [
            'label'      => 'الأسبوع',
            'collected'  => 52300,
            'target'     => 60000,
            'delta'      => 5.1,
            'rate_delta' => 1.4,
            'spark'      => [38, 44, 41, 47, 50, 49, 52.3],
            'vs'         => 'مقارنة بالأسبوع الماضي',
        ],

        'month' => [
            'label'      => 'الشهر',
            'collected'  => 195900,
            'target'     => 240000,
            'delta'      => 10.0,
            'rate_delta' => 3.2,
            'spark'      => [120, 138, 151, 164, 178, 195.9],
            'vs'         => 'مقارنة بالشهر الماضي',
        ],

        'year' => [
            'label'      => 'السنة',
            'collected'  => 1720000,
            'target'     => 2400000,
            'delta'      => 14.6,
            'rate_delta' => 4.8,
            'spark'      => [900, 1050, 1180, 1330, 1500, 1720],
            'vs'         => 'مقارنة بالسنة الماضية',
        ],

    ];


    /*
    |--------------------------------------------------------------------------
    | SAFE ROUTE HELPER
    |--------------------------------------------------------------------------
    |
    | The dashboard should never crash because another page/route has not
    | been created yet.
    |
    */

    private function link(string $name, array $params = []): string
    {
        return Route::has($name)
            ? route($name, $params)
            : '#';
    }


    /*
    |--------------------------------------------------------------------------
    | MAIN DASHBOARD
    |--------------------------------------------------------------------------
    */

    public function __invoke(Request $request): View
    {
        /*
        |--------------------------------------------------------------------------
        | BASIC DASHBOARD STATE
        |--------------------------------------------------------------------------
        */

        $period = $this->resolvePeriod($request);

        $periodData = $this->getPeriodData($period);

        $now = now();


        /*
        |--------------------------------------------------------------------------
        | BANKS
        |--------------------------------------------------------------------------
        |
        | TODO (DB):
        |
        | banks
        | debt_cases
        | portfolios
        | payments
        |
        */

        $banks = $this->getBanks();


        /*
        |--------------------------------------------------------------------------
        | FINANCIAL TOTALS
        |--------------------------------------------------------------------------
        */

        $financialTotals = $this->calculateFinancialTotals(
            $banks,
            $periodData
        );


        /*
        |--------------------------------------------------------------------------
        | DASHBOARD SECTIONS
        |--------------------------------------------------------------------------
        */

        $collectionOverview = $this->getCollectionOverview();

        $pipeline = $this->getPipeline();

        $performance = $this->getPerformance($periodData);

        $portfolio = $this->getPortfolio($banks);

        $employeeOverview = $this->getEmployeeOverview();

        $todayOperations = $this->getTodayOperations();

        $ptpIntelligence = $this->getPtpIntelligence();

        $paymentOverview = $this->getPaymentOverview();

        $complaintsOverview = $this->getComplaintsOverview();

        $visitOverview = $this->getVisitOverview();

        $systemHealth = $this->getSystemHealth();

        $managementSnapshot = $this->getManagementSnapshot();

        $riskOverview = $this->getRiskOverview();

        $customerOverview = $this->getCustomerOverview();

        $contactOverview = $this->getContactOverview();

        $guarantorOverview = $this->getGuarantorOverview();

        $bankInsights = $this->getBankInsights();

        $supervisorPerformance = $this->getSupervisorPerformance();

        $aging = $this->getAging();

        $recentImports = $this->getRecentImports();

        $recentComplaints = $this->getRecentComplaints();

        $upcomingPtp = $this->getUpcomingPtp();

        $upcomingVisits = $this->getUpcomingVisits();

        $criticalCases = $this->getCriticalCases();


        /*
        |--------------------------------------------------------------------------
        | EXISTING DASHBOARD DATA
        |--------------------------------------------------------------------------
        */

        $leaders = $this->getLeaders();

        $agenda = $this->getAgenda();

        $ptp = $this->getPtpSummary();

        $alerts = $this->getAlerts();

        $payments = $this->getLatestPayments();

        $methods = $this->getPaymentMethods();

        $activity = $this->getRecentActivity();


        /*
        |--------------------------------------------------------------------------
        | CHARTS
        |--------------------------------------------------------------------------
        */

        $trend = $this->getTrendChart();

        $split = $this->getCollectionSplitChart(
            $financialTotals
        );

        $buckets = $this->getDpdBuckets();


        /*
        |--------------------------------------------------------------------------
        | LINKS
        |--------------------------------------------------------------------------
        */

        $links = $this->getDashboardLinks();


        /*
        |--------------------------------------------------------------------------
        | RETURN DASHBOARD VIEW
        |--------------------------------------------------------------------------
        */

        return view('dashboard.index', [

            /*
            |--------------------------------------------------------------------------
            | HEADER
            |--------------------------------------------------------------------------
            */

            'period' => $period,

            'periods' => collect(self::PERIODS)
                ->map(fn ($item) => $item['label'])
                ->all(),

            'vs' => $periodData['vs'],

            'userName' => auth()->user()?->name ?? 'Test Account',

            'greeting' => $this->getGreeting($now),

            'todayLabel' => $this->getTodayLabel($now),

            'updatedAt' => $now->format('H:i'),


            /*
            |--------------------------------------------------------------------------
            | QUICK ACTIONS
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            | Keep numeric array structure because the Blade uses:
            |
            | @foreach($quick as [$title, $hint, $icon, $color, $url])
            |
            */

            'quick' => $this->getQuickActions(),


            /*
            |--------------------------------------------------------------------------
            | MONEY KPIs
            |--------------------------------------------------------------------------
            */

            'kpis' => $this->getKpis(
                $financialTotals,
                $periodData
            ),


            /*
            |--------------------------------------------------------------------------
            | NEEDS ATTENTION
            |--------------------------------------------------------------------------
            */

            'attention' => $this->getAttentionItems(),


            /*
            |--------------------------------------------------------------------------
            | COLLECTION OVERVIEW
            |--------------------------------------------------------------------------
            */

            'collectionOverview' => $collectionOverview,

            'pipeline' => $pipeline,

            'performance' => $performance,

            'portfolio' => $portfolio,

            'employeeOverview' => $employeeOverview,

            'todayOperations' => $todayOperations,

            'ptpIntelligence' => $ptpIntelligence,

            'paymentOverview' => $paymentOverview,

            'complaintsOverview' => $complaintsOverview,

            'visitOverview' => $visitOverview,

            'systemHealth' => $systemHealth,

            'managementSnapshot' => $managementSnapshot,

            'riskOverview' => $riskOverview,

            'customerOverview' => $customerOverview,

            'contactOverview' => $contactOverview,

            'guarantorOverview' => $guarantorOverview,

            'bankInsights' => $bankInsights,

            'supervisorPerformance' => $supervisorPerformance,

            'aging' => $aging,

            'recentImports' => $recentImports,

            'recentComplaints' => $recentComplaints,

            'upcomingPtp' => $upcomingPtp,

            'upcomingVisits' => $upcomingVisits,

            'criticalCases' => $criticalCases,


            /*
            |--------------------------------------------------------------------------
            | CHART DATA
            |--------------------------------------------------------------------------
            */

            'trend' => $trend,

            'split' => $split,

            'buckets' => $buckets,


            /*
            |--------------------------------------------------------------------------
            | BANK DATA
            |--------------------------------------------------------------------------
            */

            'banks' => $banks,

            'totals' => [
                'cases' => $financialTotals['cases'],
                'portfolio' => $financialTotals['portfolio'],
                'collected' => $financialTotals['collected'],
            ],


            /*
            |--------------------------------------------------------------------------
            | EXISTING DASHBOARD SECTIONS
            |--------------------------------------------------------------------------
            */

            'agenda' => $agenda,

            'ptp' => $ptp,

            'alerts' => $alerts,

            'leaders' => $leaders,

            'payments' => $payments,

            'methods' => $methods,

            'activity' => $activity,


            /*
            |--------------------------------------------------------------------------
            | ALL DASHBOARD LINKS
            |--------------------------------------------------------------------------
            */

            'links' => $links,

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | PERIOD
    |--------------------------------------------------------------------------
    */

    private function resolvePeriod(Request $request): string
    {
        $requestedPeriod = (string) $request->query('period');

        return array_key_exists(
            $requestedPeriod,
            self::PERIODS
        )
            ? $requestedPeriod
            : 'month';
    }


    private function getPeriodData(string $period): array
    {
        return self::PERIODS[$period] ?? self::PERIODS['month'];
    }


    /*
    |--------------------------------------------------------------------------
    | HEADER
    |--------------------------------------------------------------------------
    */

    private function getGreeting($now): string
    {
        return $now->hour < 12
            ? 'صباح الخير'
            : 'مساء الخير';
    }


    private function getTodayLabel($now): string
    {
        return self::WEEKDAYS[$now->dayOfWeek]
            . '، '
            . $now->day
            . ' '
            . self::MONTHS[$now->month]
            . ' '
            . $now->year;
    }


    /*
    |--------------------------------------------------------------------------
    | QUICK ACTIONS
    |--------------------------------------------------------------------------
    |
    | Keep this exact numeric structure.
    |
    */

    private function getQuickActions(): array
    {
        return [

            [
                'استيراد نطاق',
                'رفع ملف Excel جديد',
                'fa-file-arrow-up',
                'cyan',
                $this->link(
                    'banks.scope.import',
                    ['bank' => 1]
                ),
            ],

            [
                'توزيع الحالات',
                'على الموظفين',
                'fa-sitemap',
                'accent',
                $this->link(
                    'banks.distribution.index',
                    ['bank' => 1]
                ),
            ],

            [
                'تأكيد التحصيلات',
                'مراجعة الإيصالات',
                'fa-circle-check',
                'brand',
                $this->link('confirmations.index'),
            ],

            [
                'إضافة موظف',
                'حساب جديد',
                'fa-user-plus',
                'warning',
                $this->link('users.create'),
            ],

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | BANKS
    |--------------------------------------------------------------------------
    |
    | TODO (DB):
    |
    | Replace $figures with:
    |
    | Bank::query()
    | ->with(...)
    | ->withCount(...)
    | ->withSum(...)
    |
    */

    private function getBanks()
    {
        /*
        | Static:
        |
        | [cases, portfolio, collected]
        |
        */

        $figures = [

            1 => [
                6,
                42000,
                15400,
            ],

            2 => [
                48,
                310000,
                98000,
            ],

            3 => [
                31,
                205000,
                61500,
            ],

            4 => [
                0,
                0,
                0,
            ],

            5 => [
                12,
                95000,
                21000,
            ],

        ];


        return StaticBanks::all()
            ->map(function ($bank) use ($figures) {

                [
                    $bank->cases,
                    $bank->portfolio,
                    $bank->collected
                ] = $figures[$bank->id] ?? [
                    0,
                    0,
                    0,
                ];


                $bank->rate = $bank->portfolio > 0

                    ? (int) round(
                        $bank->collected
                        / $bank->portfolio
                        * 100
                    )

                    : 0;


                $bank->url = $this->link(
                    'banks.panel',
                    ['bank' => $bank->id]
                );


                return $bank;

            })
            ->sortByDesc('portfolio')
            ->values();
    }


    /*
    |--------------------------------------------------------------------------
    | FINANCIAL TOTALS
    |--------------------------------------------------------------------------
    */

    private function calculateFinancialTotals(
        $banks,
        array $periodData
    ): array {

        $portfolio = $banks->sum('portfolio');

        $monthCollected = $banks->sum('collected');

        $remaining = max(
            $portfolio - $monthCollected,
            0
        );


        $achievement = $periodData['target'] > 0

            ? round(
                $periodData['collected']
                / $periodData['target']
                * 100,
                1
            )

            : 0;


        return [

            'cases' => $banks->sum('cases'),

            'portfolio' => $portfolio,

            'collected' => $monthCollected,

            'remaining' => $remaining,

            'achievement' => $achievement,

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | MONEY KPIs
    |--------------------------------------------------------------------------
    */

    private function getKpis(
        array $totals,
        array $periodData
    ): array {

        return [

            [
                'label' => 'إجمالي المحفظة',

                'value' => number_format(
                    $totals['portfolio']
                ),

                'unit' => 'EGP',

                'icon' => 'fa-wallet',

                'color' => 'info',

                'delta' => 3.2,

                'good_up' => true,

                'spark' => [
                    540,
                    575,
                    590,
                    610,
                    630,
                    $totals['portfolio'] / 1000,
                ],

                'hint' => 'مقارنة بالشهر الماضي',

                'href' => null,
            ],


            [
                'label' => 'المحصل (' . $periodData['label'] . ')',

                'value' => number_format(
                    $periodData['collected']
                ),

                'unit' => 'EGP',

                'icon' => 'fa-money-bill-wave',

                'color' => 'brand',

                'delta' => $periodData['delta'],

                'good_up' => true,

                'spark' => $periodData['spark'],

                'hint' => $periodData['vs'],

                'href' => null,
            ],


            [
                'label' => 'تحقيق المستهدف',

                'value' => $totals['achievement'] . '%',

                'unit' => null,

                'icon' => 'fa-bullseye',

                'color' => 'accent',

                'delta' => $periodData['rate_delta'],

                'good_up' => true,

                'spark' => [
                    60,
                    66,
                    63,
                    70,
                    74,
                    $totals['achievement'],
                ],

                'hint' => 'المستهدف '
                    . number_format($periodData['target'])
                    . ' EGP',

                'href' => null,
            ],


            [
                'label' => 'المتبقي للتحصيل',

                'value' => number_format(
                    $totals['remaining']
                ),

                'unit' => 'EGP',

                'icon' => 'fa-hourglass-half',

                'color' => 'warning',

                'delta' => -4.1,

                'good_up' => false,

                'spark' => [
                    470,
                    480,
                    472,
                    466,
                    460,
                    $totals['remaining'] / 1000,
                ],

                'hint' => 'مقارنة بالشهر الماضي',

                'href' => null,
            ],

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | ATTENTION
    |--------------------------------------------------------------------------
    */

    private function getAttentionItems(): array
    {
        return [

            [
                'label' => 'تحصيلات بانتظار التأكيد',
                'value' => 3,
                'hint' => '4,150 EGP',
                'icon' => 'fa-circle-check',
                'color' => 'warning',
                'href' => $this->link(
                    'confirmations.index'
                ),
            ],


            [
                'label' => 'وعود مستحقة اليوم',
                'value' => 1,
                'hint' => '2,000 EGP',
                'icon' => 'fa-handshake',
                'color' => 'orange',
                'href' => $this->link(
                    'banks.ptp.index',
                    [
                        'bank' => 1,
                        'scope' => 'today',
                    ]
                ),
            ],


            [
                'label' => 'حالات غير موزعة',
                'value' => 2,
                'hint' => 'تحتاج توزيع',
                'icon' => 'fa-sitemap',
                'color' => 'accent',
                'href' => $this->link(
                    'banks.distribution.index',
                    ['bank' => 1]
                ),
            ],


            [
                'label' => 'شكاوى مفتوحة',
                'value' => 3,
                'hint' => 'منها 1 عاجلة',
                'icon' => 'fa-headset',
                'color' => 'pink',
                'href' => $this->link(
                    'banks.complaints.index',
                    ['bank' => 1]
                ),
            ],

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | COLLECTION OVERVIEW
    |--------------------------------------------------------------------------
    |
    | TODO (DB):
    | debt_cases
    | case_assignments
    |
    */

    private function getCollectionOverview(): array
    {
        return [

            'total_cases' => 97,

            'active_cases' => 84,

            'unassigned_cases' => 2,

            'overdue_cases' => 18,

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | COLLECTION PIPELINE
    |--------------------------------------------------------------------------
    */

    private function getPipeline(): array
    {
        return [

            [
                'label' => 'جديدة',
                'count' => 12,
                'amount' => 'EGP 95,000',
                'icon' => 'fa-folder-plus',
                'color' => 'info',
                'url' => $this->link('cases.index'),
            ],

            [
                'label' => 'غير معينة',
                'count' => 2,
                'amount' => 'EGP 18,000',
                'icon' => 'fa-user-slash',
                'color' => 'warning',
                'url' => $this->link(
                    'banks.distribution.index',
                    ['bank' => 1]
                ),
            ],

            [
                'label' => 'قيد المتابعة',
                'count' => 41,
                'amount' => 'EGP 280,000',
                'icon' => 'fa-phone-volume',
                'color' => 'cyan',
                'url' => $this->link('cases.index'),
            ],

            [
                'label' => 'وعد دفع',
                'count' => 13,
                'amount' => 'EGP 62,000',
                'icon' => 'fa-handshake',
                'color' => 'warning',
                'url' => $this->link(
                    'banks.ptp.index',
                    ['bank' => 1]
                ),
            ],

            [
                'label' => 'متعثر',
                'count' => 18,
                'amount' => 'EGP 135,000',
                'icon' => 'fa-triangle-exclamation',
                'color' => 'danger',
                'url' => $this->link('cases.index'),
            ],

            [
                'label' => 'مغلق',
                'count' => 9,
                'amount' => 'EGP 74,000',
                'icon' => 'fa-circle-check',
                'color' => 'brand',
                'url' => $this->link('cases.index'),
            ],

            [
                'label' => 'معلق',
                'count' => 2,
                'amount' => 'EGP 11,000',
                'icon' => 'fa-pause',
                'color' => 'orange',
                'url' => $this->link('cases.index'),
            ],

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | COLLECTION PERFORMANCE
    |--------------------------------------------------------------------------
    */

    private function getPerformance(array $periodData): array
    {
        $target = $periodData['target'];

        $collected = $periodData['collected'];

        $remaining = max(
            $target - $collected,
            0
        );

        $achievement = $target > 0

            ? round(
                $collected / $target * 100,
                1
            )

            : 0;


        return [

            'target' => $target,

            'collected' => $collected,

            'remaining' => $remaining,

            'achievement' => $achievement,

            'average_payment' => 1850,

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | PORTFOLIO
    |--------------------------------------------------------------------------
    */

    private function getPortfolio($banks): array
    {
        $totalDebt = $banks->sum('portfolio');

        $totalCollected = $banks->sum('collected');

        $totalRemaining = max(
            $totalDebt - $totalCollected,
            0
        );

        $collectionRate = $totalDebt > 0

            ? round(
                $totalCollected
                / $totalDebt
                * 100,
                1
            )

            : 0;

        $cases = $banks->sum('cases');

        $averageCase = $cases > 0

            ? round(
                $totalDebt / $cases
            )

            : 0;


        return [

            'total_debt' => $totalDebt,

            'total_collected' => $totalCollected,

            'total_remaining' => $totalRemaining,

            'collection_rate' => $collectionRate,

            'average_case' => $averageCase,

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | EMPLOYEE OVERVIEW
    |--------------------------------------------------------------------------
    |
    | TODO (DB):
    | employee
    | employee_assignments
    | payments
    |
    */

    private function getEmployeeOverview(): array
    {
        return [

            'total' => 18,

            'active' => 15,

            'available' => 11,

            'on_leave' => 3,

            'avg_cases' => 6,

            'avg_collected' => 12800,

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | TODAY OPERATIONS
    |--------------------------------------------------------------------------
    */

    private function getTodayOperations(): array
    {
        return [

            'calls' => 38,

            'visits' => 7,

            'scheduled_visits' => 9,

            'completed_visits' => 5,

            'failed_visits' => 1,

            'due_ptp' => 4,

            'overdue_ptp' => 2,

            'followups' => 16,

            'unassigned' => 2,

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | PTP INTELLIGENCE
    |--------------------------------------------------------------------------
    */

    private function getPtpIntelligence(): array
    {
        $total = 18;

        $kept = 9;

        $broken = 4;

        $upcoming = 5;

        $totalAmount = 84000;

        $keptAmount = 39000;

        $brokenAmount = 18000;


        $achievement = $total > 0

            ? round(
                $kept / $total * 100,
                1
            )

            : 0;


        return [

            'total' => $total,

            'promised' => 18,

            'kept' => $kept,

            'broken' => $broken,

            'upcoming' => $upcoming,

            'due_today' => 4,

            'total_amount' => $totalAmount,

            'kept_amount' => $keptAmount,

            'broken_amount' => $brokenAmount,

            'achievement' => $achievement,

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | PAYMENT OVERVIEW
    |--------------------------------------------------------------------------
    */

    private function getPaymentOverview(): array
    {
        $todayAmount = 9450;

        $todayCount = 7;

        $average = $todayCount > 0

            ? round(
                $todayAmount / $todayCount
            )

            : 0;


        return [

            'today_amount' => $todayAmount,

            'today_count' => $todayCount,

            'average' => $average,

            'cash' => 3450,

            'bank' => 2800,

            'wallet' => 1900,

            'online' => 1300,

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | COMPLAINTS
    |--------------------------------------------------------------------------
    */

    private function getComplaintsOverview(): array
    {
        return [

            'open' => 3,

            'processing' => 4,

            'closed' => 18,

            'overdue' => 1,

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | VISITS
    |--------------------------------------------------------------------------
    */

    private function getVisitOverview(): array
    {
        return [

            'today' => 7,

            'scheduled' => 9,

            'completed' => 5,

            'failed' => 1,

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | SYSTEM HEALTH
    |--------------------------------------------------------------------------
    */

    private function getSystemHealth(): array
    {
        return [

            'last_import' => 'اليوم 09:30',

            'imported_records' => 124,

            'failed_records' => 3,

            'last_activity' => 'اليوم 12:10',

            'last_export' => 'أمس 17:45',

            'unread_notifications' => 6,

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | MANAGEMENT SNAPSHOT
    |--------------------------------------------------------------------------
    */

    private function getManagementSnapshot(): array
    {
        return [

            'daily_reports' => 8,

            'performance' => 24,

            'monthly_archives' => 6,

            'report_exports' => 17,

            'activity_logs' => 1284,

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | RISK
    |--------------------------------------------------------------------------
    */

    private function getRiskOverview(): array
    {
        return [

            'high' => 18,

            'medium' => 37,

            'low' => 42,

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | CUSTOMER OVERVIEW
    |--------------------------------------------------------------------------
    */

    private function getCustomerOverview(): array
    {
        return [

            'total' => 91,

            'individuals' => 76,

            'companies' => 15,

            'with_guarantor' => 52,

            'without_guarantor' => 39,

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | CONTACT OVERVIEW
    |--------------------------------------------------------------------------
    */

    private function getContactOverview(): array
    {
        return [

            'contacted_today' => 38,

            'not_contacted' => 22,

            'successful' => 26,

            'unsuccessful' => 12,

            'wrong_number' => 4,

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | GUARANTOR OVERVIEW
    |--------------------------------------------------------------------------
    */

    private function getGuarantorOverview(): array
    {
        return [

            'total' => 52,

            'active' => 46,

            'contacted' => 38,

            'uncontacted' => 8,

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | BANK INSIGHTS
    |--------------------------------------------------------------------------
    |
    | Currently prepared for future sections.
    |
    */

    private function getBankInsights(): array
    {
        return [

            [
                'bank' => 'Bank Misr',
                'cases' => 48,
                'collected' => 98000,
                'rate' => 32,
            ],

            [
                'bank' => 'B.TECH',
                'cases' => 31,
                'collected' => 61500,
                'rate' => 30,
            ],

            [
                'bank' => 'Emirates NBD',
                'cases' => 12,
                'collected' => 21000,
                'rate' => 22,
            ],

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | SUPERVISOR PERFORMANCE
    |--------------------------------------------------------------------------
    */

    private function getSupervisorPerformance(): array
    {
        return [

            [
                'name' => 'Beshoy Nople',

                'employees' => 6,

                'cases' => 34,

                'collected' => 48000,

                'target' => 60000,

                'rate' => 80,

            ],

            [
                'name' => 'Ahmed Borlsy',

                'employees' => 5,

                'cases' => 28,

                'collected' => 36500,

                'target' => 50000,

                'rate' => 73,

            ],

            [
                'name' => 'Mohamed Hassan',

                'employees' => 4,

                'cases' => 21,

                'collected' => 19500,

                'target' => 35000,

                'rate' => 56,

            ],

            [
                'name' => 'Omar Ali',

                'employees' => 3,

                'cases' => 14,

                'collected' => 7200,

                'target' => 18000,

                'rate' => 40,

            ],

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | AGING
    |--------------------------------------------------------------------------
    */

    private function getAging(): array
    {
        return [

            '0_30' => 24,

            '31_60' => 21,

            '61_90' => 17,

            '91_180' => 14,

            '181_365' => 12,

            '365_plus' => 9,

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | RECENT IMPORTS
    |--------------------------------------------------------------------------
    */

    private function getRecentImports(): array
    {
        return [

            [
                'file' => 'Emirates-NBD-Scope.xlsx',

                'bank' => 'Emirates NBD',

                'records' => 124,

                'success' => 121,

                'failed' => 3,

                'user' => 'Test Account',

                'date' => 'اليوم 09:30',

                'status' => 'success',

                'status_label' => 'مكتمل',

            ],

            [
                'file' => 'BTECH-September.xlsx',

                'bank' => 'B.TECH',

                'records' => 86,

                'success' => 86,

                'failed' => 0,

                'user' => 'Beshoy nople',

                'date' => 'أمس 15:40',

                'status' => 'success',

                'status_label' => 'مكتمل',

            ],

            [
                'file' => 'BankMisr-Portfolio.xlsx',

                'bank' => 'Bank Misr',

                'records' => 210,

                'success' => 205,

                'failed' => 5,

                'user' => 'Ahmed Borlsy',

                'date' => 'أمس 11:20',

                'status' => 'success',

                'status_label' => 'مكتمل مع أخطاء',

            ],

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | RECENT COMPLAINTS
    |--------------------------------------------------------------------------
    */

    private function getRecentComplaints(): array
    {
        return [

            [
                'title' => 'اتصالات خارج المواعيد - CMP-000102',

                'status' => 'عاجلة',

                'time' => 'اليوم 11:45',

                'url' => $this->link(
                    'banks.complaints.index',
                    ['bank' => 1]
                ),
            ],

            [
                'title' => 'اعتراض على قيمة التحصيل',

                'status' => 'قيد المعالجة',

                'time' => 'اليوم 10:30',

                'url' => $this->link(
                    'banks.complaints.index',
                    ['bank' => 1]
                ),
            ],

            [
                'title' => 'طلب إعادة الاتصال',

                'status' => 'مفتوحة',

                'time' => 'أمس 16:10',

                'url' => $this->link(
                    'banks.complaints.index',
                    ['bank' => 1]
                ),
            ],

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | UPCOMING PTP
    |--------------------------------------------------------------------------
    */

    private function getUpcomingPtp(): array
    {
        return [

            [
                'client' => 'منى هبة صالح',

                'date' => 'اليوم',

                'amount' => 2000,

                'url' => $this->link(
                    'banks.ptp.index',
                    ['bank' => 1]
                ),
            ],

            [
                'client' => 'حسن زينب الهواري',

                'date' => 'غداً',

                'amount' => 3500,

                'url' => $this->link(
                    'banks.ptp.index',
                    ['bank' => 1]
                ),
            ],

            [
                'client' => 'إيمان هبة راضي',

                'date' => 'بعد غد',

                'amount' => 1500,

                'url' => $this->link(
                    'banks.ptp.index',
                    ['bank' => 1]
                ),
            ],

            [
                'client' => 'أحمد يوسف توفيق',

                'date' => 'الأسبوع القادم',

                'amount' => 4200,

                'url' => $this->link(
                    'banks.ptp.index',
                    ['bank' => 1]
                ),
            ],

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | UPCOMING VISITS
    |--------------------------------------------------------------------------
    */

    private function getUpcomingVisits(): array
    {
        return [

            [
                'client' => 'حسن زينب الهواري',

                'employee' => 'Beshoy nople',

                'date' => 'اليوم',

                'time' => '14:30',

                'status' => 'مجدولة',

                'url' => $this->link(
                    'banks.visits.index',
                    ['bank' => 1]
                ),
            ],

            [
                'client' => 'منى هبة صالح',

                'employee' => 'Ahmed Borlsy',

                'date' => 'غداً',

                'time' => '10:00',

                'status' => 'مجدولة',

                'url' => $this->link(
                    'banks.visits.index',
                    ['bank' => 1]
                ),
            ],

            [
                'client' => 'إيمان هبة راضي',

                'employee' => 'Mohamed Hassan',

                'date' => 'غداً',

                'time' => '12:30',

                'status' => 'مجدولة',

                'url' => $this->link(
                    'banks.visits.index',
                    ['bank' => 1]
                ),
            ],

            [
                'client' => 'فاطمة زينب السيد',

                'employee' => 'Omar Ali',

                'date' => 'بعد غد',

                'time' => '09:30',

                'status' => 'مجدولة',

                'url' => $this->link(
                    'banks.visits.index',
                    ['bank' => 1]
                ),
            ],

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | CRITICAL CASES
    |--------------------------------------------------------------------------
    */

    private function getCriticalCases(): array
    {
        return [

            [
                'client' => 'حسن زينب الهواري',

                'bank' => 'Emirates NBD',

                'employee' => 'Beshoy nople',

                'amount' => 18500,

                'last_followup' => 'منذ 10 أيام',

                'reason' => 'وعد دفع مكسور',

                'url' => $this->link(
                    'banks.ptp.index',
                    ['bank' => 1]
                ),
            ],

            [
                'client' => 'منى هبة صالح',

                'bank' => 'Bank Misr',

                'employee' => 'Ahmed Borlsy',

                'amount' => 12700,

                'last_followup' => 'منذ 8 أيام',

                'reason' => 'متابعة متأخرة',

                'url' => $this->link(
                    'cases.index'
                ),
            ],

            [
                'client' => 'إيمان هبة راضي',

                'bank' => 'B.TECH',

                'employee' => 'غير معين',

                'amount' => 9200,

                'last_followup' => 'لم تتم المتابعة',

                'reason' => 'حالة غير معينة',

                'url' => $this->link(
                    'banks.distribution.index',
                    ['bank' => 1]
                ),
            ],

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | MAIN CHART
    |--------------------------------------------------------------------------
    */

    private function getTrendChart(): array
    {
        return [

            'labels' => [
                'Apr',
                'May',
                'Jun',
                'Jul',
                'Aug',
                'Sep',
            ],

            'collected' => [
                120,
                138,
                151,
                164,
                178,
                195.9,
            ],

            'target' => [
                150,
                160,
                170,
                190,
                210,
                240,
            ],

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | PORTFOLIO SPLIT
    |--------------------------------------------------------------------------
    */

    private function getCollectionSplitChart(
        array $totals
    ): array {

        return [

            'labels' => [
                'محصل',
                'متبقي',
            ],

            'data' => [

                $totals['collected'],

                $totals['remaining'],

            ],

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | CASE BUCKETS
    |--------------------------------------------------------------------------
    */

    private function getDpdBuckets(): array
    {
        return [

            'labels' => [

                'الشريحة 0',

                'الشريحة 1',

                'الشريحة 2',

                'الشريحة 3+',

            ],

            'data' => [

                12,

                35,

                40,

                13,

            ],

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | TOP EMPLOYEES
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    | This is $leaders, NOT $performance.
    |
    | The Blade uses:
    |
    | $leaders as $leader
    |
    */

    private function getLeaders()
    {
        /*
        | TODO (DB):
        |
        | employee
        | payments
        | debt_cases
        | performance_snapshots
        |
        */

        $performance = [

            4 => [
                15400,
                68,
                3,
            ],

            3 => [
                4200,
                31,
                3,
            ],

            2 => [
                0,
                0,
                0,
            ],

        ];


        return StaticData::employees()

            ->map(function ($user) use ($performance) {

                [
                    $user->collected,
                    $user->efficiency,
                    $user->cases
                ] = $performance[$user->id] ?? [
                    0,
                    0,
                    0,
                ];


                $user->tone =
                    $user->efficiency >= 70

                    ? 'brand'

                    : (
                        $user->efficiency >= 40
                        ? 'warning'
                        : 'danger'
                    );


                $user->url = $this->link(
                    'employees.show',
                    ['code' => $user->code]
                );


                return $user;

            })

            ->sortByDesc('collected')

            ->values();
    }


    /*
    |--------------------------------------------------------------------------
    | TODAY AGENDA
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    | Keep numeric array structure.
    |
    | Blade:
    |
    | @foreach($agenda as [$text, $count, $icon, $toneClass, $url])
    |
    */

    private function getAgenda(): array
    {
        return [

            [
                'زيارات ميدانية اليوم',
                2,
                'fa-person-walking',
                'text-cyan',
                $this->link(
                    'banks.visits.index',
                    ['bank' => 1]
                ),
            ],

            [
                'تقارير DCR بانتظار الاعتماد',
                1,
                'fa-file-invoice',
                'text-danger',
                $this->link(
                    'banks.dcr.index',
                    ['bank' => 1]
                ),
            ],

            [
                'شكاوى ينتهي موعدها اليوم',
                1,
                'fa-headset',
                'text-pink',
                $this->link(
                    'banks.complaints.index',
                    [
                        'bank' => 1,
                        'status' => 'open',
                    ]
                ),
            ],

            [
                'متابعات هاتفية مجدولة',
                5,
                'fa-phone',
                'text-info',
                '#',
            ],

            [
                'وعود تحتاج مراجعة',
                1,
                'fa-hourglass-half',
                'text-warning',
                $this->link(
                    'banks.ptp.index',
                    ['bank' => 1]
                ),
            ],

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | PTP SUMMARY
    |--------------------------------------------------------------------------
    */

    private function getPtpSummary(): array
    {
        return [

            'rows' => [

                [
                    'وعود نشطة',
                    3,
                    'bg-warning',
                ],

                [
                    'قيد المراجعة',
                    1,
                    'bg-cyan',
                ],

                [
                    'محقق كلياً',
                    1,
                    'bg-brand',
                ],

                [
                    'محقق جزئياً',
                    1,
                    'bg-info',
                ],

                [
                    'مكسور',
                    1,
                    'bg-danger',
                ],

            ],

            'total' => 7,

            'rate' => 33,

            'url' => $this->link(
                'banks.ptp.index',
                ['bank' => 1]
            ),

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | ALERTS
    |--------------------------------------------------------------------------
    |
    | Keep numeric array structure.
    |
    */

    private function getAlerts(): array
    {
        return [

            [
                'fa-triangle-exclamation',
                'danger',
                'وعد دفع مكسور لعميل متعثر (حسن زينب الهواري) منذ 10 أيام',
                $this->link(
                    'banks.ptp.index',
                    ['bank' => 1]
                ),
            ],

            [
                'fa-headset',
                'danger',
                'شكوى عاجلة: اتصالات خارج المواعيد (CMP-000102)',
                $this->link(
                    'banks.complaints.index',
                    ['bank' => 1]
                ),
            ],

            [
                'fa-calendar-xmark',
                'warning',
                'وعد متأخر يوماً واحداً: منى هبة صالح',
                $this->link(
                    'banks.ptp.index',
                    [
                        'bank' => 1,
                        'scope' => 'overdue',
                    ]
                ),
            ],

            [
                'fa-user-xmark',
                'warning',
                'حالتان بدون موظف في Emirates NBD',
                $this->link(
                    'banks.distribution.index',
                    ['bank' => 1]
                ),
            ],

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | LATEST PAYMENTS
    |--------------------------------------------------------------------------
    |
    | Keep numeric array structure.
    |
    */

    private function getLatestPayments(): array
    {
        return [

            [
                '#4092',
                'حسن زينب الهواري',
                1500,
                'cash',
                'اليوم 10:20',
                'pending',
            ],

            [
                '#4091',
                'منى هبة صالح',
                450,
                'e_wallet',
                'اليوم 09:45',
                'pending',
            ],

            [
                '#4090',
                'إيمان هبة راضي',
                2200,
                'bank_transfer',
                'أمس 16:10',
                'pending',
            ],

            [
                '#4089',
                'أحمد يوسف توفيق',
                1500,
                'cash',
                'أمس 12:30',
                'confirmed',
            ],

            [
                '#4088',
                'فاطمة زينب السيد',
                400,
                'cash',
                'قبل يومين',
                'confirmed',
            ],

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | PAYMENT METHODS
    |--------------------------------------------------------------------------
    */

    private function getPaymentMethods(): array
    {
        return [

            'cash' => 'نقدي',

            'e_wallet' => 'محفظة',

            'bank_transfer' => 'تحويل',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | RECENT ACTIVITY
    |--------------------------------------------------------------------------
    |
    | Keep numeric array structure.
    |
    */

    private function getRecentActivity(): array
    {
        return [

            [
                'Beshoy nople',
                'اعتمد تقرير DCR الخاص بـ HOOL',
                'اليوم 12:10',
                'fa-file-circle-check',
                'text-brand',
            ],

            [
                'Beshoy nople',
                'وزّع 24 حالة على 3 موظفين',
                'اليوم 10:05',
                'fa-sitemap',
                'text-accent',
            ],

            [
                'Test Account',
                'استورد نطاق Emirates NBD (6 حالات)',
                'اليوم 09:30',
                'fa-file-arrow-up',
                'text-cyan',
            ],

            [
                'Ahmed Borlsy',
                'سجّل وعد دفع جديد لمنى هبة صالح',
                'أمس 15:12',
                'fa-handshake',
                'text-warning',
            ],

            [
                'HOOL',
                'عدّل بيانات العميل حسن زينب الهواري',
                'أمس 10:22',
                'fa-pen-to-square',
                'text-info',
            ],

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | DASHBOARD LINKS
    |--------------------------------------------------------------------------
    |
    | No route is invented here.
    | If a route doesn't exist yet, link() returns '#'.
    |
    */

    private function getDashboardLinks(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Main
            |--------------------------------------------------------------------------
            */

            'banks' => $this->link(
                'banks.index'
            ),

            'employees' => $this->link(
                'employees.index'
            ),

            'confirmations' => $this->link(
                'confirmations.index'
            ),

            'activity' => $this->link(
                'activity-logs.index'
            ),


            /*
            |--------------------------------------------------------------------------
            | Cases
            |--------------------------------------------------------------------------
            */

            'cases' => $this->link(
                'cases.index'
            ),

            'active_cases' => $this->link(
                'cases.index',
                ['status' => 'active']
            ),

            'unassigned_cases' => $this->link(
                'banks.distribution.index',
                ['bank' => 1]
            ),

            'overdue_cases' => $this->link(
                'cases.index',
                ['status' => 'overdue']
            ),


            /*
            |--------------------------------------------------------------------------
            | Financial
            |--------------------------------------------------------------------------
            */

            'portfolio' => $this->link(
                'reports.index'
            ),

            'payments' => $this->link(
                'confirmations.index'
            ),

            'reports' => $this->link(
                'reports.index'
            ),


            /*
            |--------------------------------------------------------------------------
            | Calls
            |--------------------------------------------------------------------------
            */

            'calls_today' => '#',


            /*
            |--------------------------------------------------------------------------
            | Visits
            |--------------------------------------------------------------------------
            */

            'visits_today' => $this->link(
                'banks.visits.index',
                ['bank' => 1]
            ),

            'scheduled_visits' => $this->link(
                'banks.visits.index',
                [
                    'bank' => 1,
                    'status' => 'scheduled',
                ]
            ),

            'completed_visits' => $this->link(
                'banks.visits.index',
                [
                    'bank' => 1,
                    'status' => 'completed',
                ]
            ),

            'failed_visits' => $this->link(
                'banks.visits.index',
                [
                    'bank' => 1,
                    'status' => 'failed',
                ]
            ),


            /*
            |--------------------------------------------------------------------------
            | PTP
            |--------------------------------------------------------------------------
            */

            'due_ptp' => $this->link(
                'banks.ptp.index',
                [
                    'bank' => 1,
                    'scope' => 'today',
                ]
            ),

            'overdue_ptp' => $this->link(
                'banks.ptp.index',
                [
                    'bank' => 1,
                    'scope' => 'overdue',
                ]
            ),


            /*
            |--------------------------------------------------------------------------
            | Followups
            |--------------------------------------------------------------------------
            */

            'followups' => '#',


            /*
            |--------------------------------------------------------------------------
            | Complaints
            |--------------------------------------------------------------------------
            */

            'complaints_open' => $this->link(
                'banks.complaints.index',
                [
                    'bank' => 1,
                    'status' => 'open',
                ]
            ),

            'complaints_processing' => $this->link(
                'banks.complaints.index',
                [
                    'bank' => 1,
                    'status' => 'processing',
                ]
            ),

            'complaints_closed' => $this->link(
                'banks.complaints.index',
                [
                    'bank' => 1,
                    'status' => 'closed',
                ]
            ),

            'complaints_overdue' => $this->link(
                'banks.complaints.index',
                [
                    'bank' => 1,
                    'status' => 'overdue',
                ]
            ),


            /*
            |--------------------------------------------------------------------------
            | Management / Reports
            |--------------------------------------------------------------------------
            */

            'daily_reports' => $this->link(
                'reports.index'
            ),

            'performance' => '#',

            'monthly_archives' => '#',

            'report_exports' => $this->link(
                'reports.exports'
            ),

        ];
    }
}