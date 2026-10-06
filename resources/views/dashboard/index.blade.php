{{-- resources/views/dashboard/index.blade.php --}}
{{-- =========================================================
    COLLEX - PROFESSIONAL COLLECTION DASHBOARD
    Existing design preserved.
    New sections added without changing the existing style system.
========================================================= --}}

@extends('layouts.app')

@section('title', 'لوحة التحكم - Collex')

@php

    /*
    |--------------------------------------------------------------------------
    | DESIGN TOKENS
    |--------------------------------------------------------------------------
    | Keep all existing colors/classes exactly as they are.
    */

    $tones = [
        'brand'   => 'bg-brand/15 text-brand',
        'info'    => 'bg-info/15 text-info',
        'accent'  => 'bg-accent/15 text-accent',
        'warning' => 'bg-warning/15 text-warning',
        'danger'  => 'bg-danger/15 text-danger',
        'cyan'    => 'bg-cyan/15 text-cyan',
        'orange'  => 'bg-orange/15 text-orange',
        'pink'    => 'bg-pink/15 text-pink',
    ];

    $alertTones = [
        'danger' => [
            'border-danger/20 bg-danger/[0.05]',
            'text-danger'
        ],

        'warning' => [
            'border-warning/20 bg-warning/[0.05]',
            'text-warning'
        ],
    ];

    $toneText = [
        'brand'   => 'text-brand',
        'warning' => 'text-warning',
        'danger'  => 'text-danger',
    ];

    $toneBar = [
        'brand'   => 'bg-brand',
        'warning' => 'bg-warning',
        'danger'  => 'bg-danger',
    ];


    /*
    |--------------------------------------------------------------------------
    | SAFE DEFAULTS
    |--------------------------------------------------------------------------
    | These allow the dashboard to render while the controller/database
    | is being connected to the new sections.
    */

    $collectionOverview = $collectionOverview ?? [
        'total_cases'      => 0,
        'active_cases'     => 0,
        'unassigned_cases' => 0,
        'overdue_cases'    => 0,
    ];

    $pipeline = $pipeline ?? [];

    $performance = $performance ?? [
        'target'          => 0,
        'collected'       => 0,
        'remaining'       => 0,
        'achievement'     => 0,
        'average_payment' => 0,
    ];

    $portfolio = $portfolio ?? [
        'total_debt'       => 0,
        'total_collected'  => 0,
        'total_remaining'  => 0,
        'collection_rate'  => 0,
        'average_case'    => 0,
    ];

    $employeeOverview = $employeeOverview ?? [
        'total'       => 0,
        'active'      => 0,
        'available'   => 0,
        'on_leave'    => 0,
        'avg_cases'   => 0,
        'avg_collected' => 0,
    ];

    $todayOperations = $todayOperations ?? [
        'calls'             => 0,
        'visits'            => 0,
        'scheduled_visits'  => 0,
        'completed_visits'  => 0,
        'failed_visits'     => 0,
        'due_ptp'           => 0,
        'overdue_ptp'       => 0,
        'followups'         => 0,
        'unassigned'        => 0,
    ];

    $ptpIntelligence = $ptpIntelligence ?? [
        'total'       => 0,
        'promised'    => 0,
        'kept'        => 0,
        'broken'      => 0,
        'upcoming'    => 0,
        'due_today'   => 0,
        'total_amount'=> 0,
        'kept_amount' => 0,
        'broken_amount' => 0,
        'achievement' => 0,
    ];

    $paymentOverview = $paymentOverview ?? [
        'today_amount'   => 0,
        'today_count'    => 0,
        'average'        => 0,
        'cash'           => 0,
        'bank'           => 0,
        'wallet'         => 0,
        'online'         => 0,
    ];

    $complaintsOverview = $complaintsOverview ?? [
        'open'       => 0,
        'processing' => 0,
        'closed'     => 0,
        'overdue'    => 0,
    ];

    $visitOverview = $visitOverview ?? [
        'today'      => 0,
        'scheduled'  => 0,
        'completed'  => 0,
        'failed'     => 0,
    ];

    $systemHealth = $systemHealth ?? [
        'last_import'        => null,
        'imported_records'   => 0,
        'failed_records'     => 0,
        'last_activity'      => null,
        'last_export'        => null,
        'unread_notifications' => 0,
    ];

    $managementSnapshot = $managementSnapshot ?? [
        'daily_reports'      => 0,
        'performance'       => 0,
        'monthly_archives'  => 0,
        'report_exports'    => 0,
        'activity_logs'     => 0,
    ];

    $riskOverview = $riskOverview ?? [
        'high'   => 0,
        'medium' => 0,
        'low'    => 0,
    ];

    $customerOverview = $customerOverview ?? [
        'total'       => 0,
        'individuals' => 0,
        'companies'   => 0,
        'with_guarantor' => 0,
        'without_guarantor' => 0,
    ];

    $contactOverview = $contactOverview ?? [
        'contacted_today'    => 0,
        'not_contacted'      => 0,
        'successful'         => 0,
        'unsuccessful'       => 0,
        'wrong_number'       => 0,
    ];

    $guarantorOverview = $guarantorOverview ?? [
        'total'       => 0,
        'active'      => 0,
        'contacted'   => 0,
        'uncontacted' => 0,
    ];

    $bankInsights = $bankInsights ?? [];

    $supervisorPerformance = $supervisorPerformance ?? [];

    $aging = $aging ?? [
        '0_30'    => 0,
        '31_60'   => 0,
        '61_90'   => 0,
        '91_180'  => 0,
        '181_365' => 0,
        '365_plus'=> 0,
    ];

    $recentImports = $recentImports ?? [];

    $recentComplaints = $recentComplaints ?? [];

    $upcomingPtp = $upcomingPtp ?? [];

    $upcomingVisits = $upcomingVisits ?? [];

    $criticalCases = $criticalCases ?? [];

@endphp


@section('content')


{{-- =========================================================
    HEADER
========================================================= --}}

<header class="mb-6 flex flex-wrap items-center justify-between gap-6">

    <div>

        <h1 class="flex items-center gap-3 text-2xl font-bold text-fg">

            <i class="fa-solid fa-gauge-high text-brand"></i>

            {{ $greeting }}، {{ $userName }}

        </h1>

        <p class="mt-2 text-sm text-muted">

            {{ $todayLabel }}

            ·

            نظرة شاملة على أداء التحصيل

        </p>

    </div>


    <div class="flex flex-wrap items-center gap-3">

        <div class="segmented">

            @foreach($periods as $key => $label)

                <a
                    href="{{ route('dashboard', ['period' => $key]) }}"
                    class="segmented-item {{ $period === $key ? 'segmented-item-active' : '' }}"
                >
                    {{ $label }}
                </a>

            @endforeach

        </div>


        <a
            href="{{ request()->fullUrl() }}"
            class="btn btn-secondary btn-sm"
            title="تحديث"
        >

            <i class="fa-solid fa-rotate"></i>

            <span class="text-[11px] text-muted">
                {{ $updatedAt }}
            </span>

        </a>

    </div>

</header>



{{-- =========================================================
    QUICK ACTIONS
========================================================= --}}

<section class="mb-6 grid grid-cols-2 gap-3 xl:grid-cols-4">

    @foreach($quick as [$title, $hint, $icon, $color, $url])

        <a
            href="{{ $url }}"
            class="card flex items-center gap-3 py-4 transition hover:-translate-y-0.5 hover:bg-white/[0.04]"
        >

            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $tones[$color] }}">

                <i class="fa-solid {{ $icon }}"></i>

            </span>


            <span>

                <span class="block text-sm font-bold text-fg">
                    {{ $title }}
                </span>

                <span class="block text-[11px] text-dim">
                    {{ $hint }}
                </span>

            </span>

        </a>

    @endforeach

</section>



{{-- =========================================================
    MONEY KPIs
========================================================= --}}

<section class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

    @foreach($kpis as $kpi)

        <x-kpi-card
            :label="$kpi['label']"
            :value="$kpi['value']"
            :unit="$kpi['unit']"
            :icon="$kpi['icon']"
            :color="$kpi['color']"
            :delta="$kpi['delta']"
            :good-up="$kpi['good_up']"
            :spark="$kpi['spark']"
            :hint="$kpi['hint']"
            :href="$kpi['href']"
        />

    @endforeach

</section>



{{-- =========================================================
    NEEDS ATTENTION
========================================================= --}}

<section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

    @foreach($attention as $item)

        <x-kpi-card
            :label="$item['label']"
            :value="$item['value']"
            :icon="$item['icon']"
            :color="$item['color']"
            :hint="$item['hint']"
            :href="$item['href']"
        />

    @endforeach

</section>



{{-- =========================================================
    COLLECTION OVERVIEW
========================================================= --}}

<section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

    <x-kpi-card
        label="إجمالي الحالات"
        :value="$collectionOverview['total_cases']"
        icon="fa-folder-open"
        color="info"
        hint="إجمالي الحالات في النظام"
        :href="$links['cases'] ?? '#'"
    />

    <x-kpi-card
        label="الحالات النشطة"
        :value="$collectionOverview['active_cases']"
        icon="fa-bolt"
        color="brand"
        hint="حالات قيد التحصيل"
        :href="$links['active_cases'] ?? '#'"
    />

    <x-kpi-card
        label="الحالات غير المعينة"
        :value="$collectionOverview['unassigned_cases']"
        icon="fa-user-slash"
        color="warning"
        hint="تحتاج إلى موظف تحصيل"
        :href="$links['unassigned_cases'] ?? '#'"
    />

    <x-kpi-card
        label="الحالات المتأخرة"
        :value="$collectionOverview['overdue_cases']"
        icon="fa-clock"
        color="danger"
        hint="تحتاج إلى متابعة"
        :href="$links['overdue_cases'] ?? '#'"
    />

</section>



{{-- =========================================================
    PORTFOLIO OVERVIEW
========================================================= --}}

<section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">

    <x-kpi-card
        label="إجمالي الديون"
        :value="'EGP ' . number_format($portfolio['total_debt'])"
        icon="fa-money-bill-wave"
        color="danger"
        hint="إجمالي قيمة المحفظة"
        :href="$links['portfolio'] ?? '#'"
    />

    <x-kpi-card
        label="إجمالي المحصل"
        :value="'EGP ' . number_format($portfolio['total_collected'])"
        icon="fa-circle-check"
        color="brand"
        hint="إجمالي المبالغ المحصلة"
        :href="$links['payments'] ?? '#'"
    />

    <x-kpi-card
        label="المتبقي"
        :value="'EGP ' . number_format($portfolio['total_remaining'])"
        icon="fa-wallet"
        color="warning"
        hint="الرصيد المتبقي للتحصيل"
        :href="$links['portfolio'] ?? '#'"
    />

    <x-kpi-card
        label="نسبة التحصيل"
        :value="$portfolio['collection_rate'] . '%'"
        icon="fa-percent"
        color="info"
        hint="نسبة المحصل من إجمالي الديون"
        :href="$links['reports'] ?? '#'"
    />

    <x-kpi-card
        label="متوسط الحالة"
        :value="'EGP ' . number_format($portfolio['average_case'])"
        icon="fa-calculator"
        color="accent"
        hint="متوسط قيمة الحالة"
        :href="$links['cases'] ?? '#'"
    />

</section>



{{-- =========================================================
    COLLECTION PIPELINE
========================================================= --}}

<section class="mb-6">

    <x-panel
        title="مسار التحصيل"
        icon="fa-diagram-project"
    >

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-7">

            @forelse($pipeline as $stage)

                <a
                    href="{{ $stage['url'] ?? '#' }}"
                    class="rounded-xl border border-line bg-white/[0.02] p-4 transition hover:bg-white/[0.05]"
                >

                    <div class="mb-3 flex items-center justify-between">

                        <span class="flex h-9 w-9 items-center justify-center rounded-lg {{ $tones[$stage['color'] ?? 'info'] ?? $tones['info'] }}">

                            <i class="fa-solid {{ $stage['icon'] ?? 'fa-circle' }}"></i>

                        </span>

                        <span class="text-lg font-bold text-fg">

                            {{ number_format($stage['count'] ?? 0) }}

                        </span>

                    </div>


                    <div class="text-xs font-semibold text-fg">

                        {{ $stage['label'] ?? '-' }}

                    </div>


                    <div class="mt-1 text-[11px] text-dim">

                        {{ $stage['amount'] ?? 'EGP 0' }}

                    </div>

                </a>

            @empty

                <div class="col-span-full">

                    <x-empty-state
                        icon="fa-diagram-project"
                        text="لا توجد بيانات لمسار التحصيل"
                    />

                </div>

            @endforelse

        </div>

    </x-panel>

</section>



{{-- =========================================================
    COLLECTION PERFORMANCE
========================================================= --}}

<section class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-3">

    <x-panel
        title="أداء التحصيل"
        icon="fa-chart-line"
        class="xl:col-span-2"
    >

        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">

            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="text-[11px] text-dim">
                    المستهدف
                </div>

                <div class="mt-2 text-xl font-bold text-fg">
                    EGP {{ number_format($performance['target']) }}
                </div>

            </div>


            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="text-[11px] text-dim">
                    المحصل
                </div>

                <div class="mt-2 text-xl font-bold text-brand">
                    EGP {{ number_format($performance['collected']) }}
                </div>

            </div>


            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="text-[11px] text-dim">
                    المتبقي
                </div>

                <div class="mt-2 text-xl font-bold text-warning">
                    EGP {{ number_format($performance['remaining']) }}
                </div>

            </div>


            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="text-[11px] text-dim">
                    نسبة الإنجاز
                </div>

                <div class="mt-2 text-xl font-bold text-info">
                    {{ $performance['achievement'] }}%
                </div>

            </div>

        </div>


        <div class="mt-5">

            <div class="mb-2 flex justify-between text-xs">

                <span class="text-muted">
                    نسبة تحقيق المستهدف
                </span>

                <span class="font-bold text-brand">
                    {{ $performance['achievement'] }}%
                </span>

            </div>


            <div class="progress">

                <div
                    class="h-full rounded-full bg-brand"
                    style="width: {{ min($performance['achievement'], 100) }}%"
                ></div>

            </div>

        </div>

    </x-panel>


    <x-panel
        title="متوسط التحصيل"
        icon="fa-calculator"
    >

        <div class="text-center">

            <div class="text-4xl font-bold text-brand">

                EGP {{ number_format($performance['average_payment']) }}

            </div>

            <div class="mt-2 text-xs text-muted">
                متوسط قيمة عملية التحصيل
            </div>

        </div>


        <div class="mt-6 grid grid-cols-2 gap-3">

            <div class="rounded-xl border border-line bg-white/[0.02] p-3 text-center">

                <div class="text-[11px] text-dim">
                    إجمالي المحصل
                </div>

                <div class="mt-1 font-bold text-fg">
                    EGP {{ number_format($performance['collected']) }}
                </div>

            </div>


            <div class="rounded-xl border border-line bg-white/[0.02] p-3 text-center">

                <div class="text-[11px] text-dim">
                    عدد العمليات
                </div>

                <div class="mt-1 font-bold text-fg">
                    {{ number_format($paymentOverview['today_count']) }}
                </div>

            </div>

        </div>

    </x-panel>

</section>



{{-- =========================================================
    MAIN CHARTS
========================================================= --}}

<section class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-3">

    <x-panel
        title="التحصيل مقابل المستهدف (ألف EGP)"
        class="xl:col-span-2"
    >

        <div class="relative h-[270px]">

            <canvas id="trendChart"></canvas>

        </div>

    </x-panel>


    <x-panel title="توزيع المحفظة (هذا الشهر)">

        <div class="relative flex h-[270px] justify-center">

            <canvas id="splitChart"></canvas>

        </div>

    </x-panel>

</section>



{{-- =========================================================
    AGING / DEBT AGE
========================================================= --}}

<section class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-3">

    <x-panel
        title="عمر الديون"
        icon="fa-clock-rotate-left"
        class="xl:col-span-2"
    >

        <div class="grid grid-cols-2 gap-3 md:grid-cols-3">

            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="text-xs text-muted">
                    0 - 30 يوم
                </div>

                <div class="mt-2 text-xl font-bold text-brand">
                    {{ number_format($aging['0_30']) }}
                </div>

            </div>


            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="text-xs text-muted">
                    31 - 60 يوم
                </div>

                <div class="mt-2 text-xl font-bold text-info">
                    {{ number_format($aging['31_60']) }}
                </div>

            </div>


            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="text-xs text-muted">
                    61 - 90 يوم
                </div>

                <div class="mt-2 text-xl font-bold text-warning">
                    {{ number_format($aging['61_90']) }}
                </div>

            </div>


            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="text-xs text-muted">
                    91 - 180 يوم
                </div>

                <div class="mt-2 text-xl font-bold text-orange">
                    {{ number_format($aging['91_180']) }}
                </div>

            </div>


            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="text-xs text-muted">
                    181 - 365 يوم
                </div>

                <div class="mt-2 text-xl font-bold text-danger">
                    {{ number_format($aging['181_365']) }}
                </div>

            </div>


            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="text-xs text-muted">
                    أكثر من سنة
                </div>

                <div class="mt-2 text-xl font-bold text-danger">
                    {{ number_format($aging['365_plus']) }}
                </div>

            </div>

        </div>

    </x-panel>


    <x-panel
        title="مستوى المخاطر"
        icon="fa-shield-halved"
    >

        <div class="space-y-4">

            <div>

                <div class="mb-2 flex justify-between text-xs">

                    <span class="text-muted">
                        مرتفع
                    </span>

                    <span class="font-bold text-danger">
                        {{ number_format($riskOverview['high']) }}
                    </span>

                </div>

                <div class="progress">

                    <div
                        class="h-full rounded-full bg-danger"
                        style="width: {{ min($collectionOverview['total_cases'] > 0 ? ($riskOverview['high'] / $collectionOverview['total_cases']) * 100 : 0, 100) }}%"
                    ></div>

                </div>

            </div>


            <div>

                <div class="mb-2 flex justify-between text-xs">

                    <span class="text-muted">
                        متوسط
                    </span>

                    <span class="font-bold text-warning">
                        {{ number_format($riskOverview['medium']) }}
                    </span>

                </div>

                <div class="progress">

                    <div
                        class="h-full rounded-full bg-warning"
                        style="width: {{ min($collectionOverview['total_cases'] > 0 ? ($riskOverview['medium'] / $collectionOverview['total_cases']) * 100 : 0, 100) }}%"
                    ></div>

                </div>

            </div>


            <div>

                <div class="mb-2 flex justify-between text-xs">

                    <span class="text-muted">
                        منخفض
                    </span>

                    <span class="font-bold text-brand">
                        {{ number_format($riskOverview['low']) }}
                    </span>

                </div>

                <div class="progress">

                    <div
                        class="h-full rounded-full bg-brand"
                        style="width: {{ min($collectionOverview['total_cases'] > 0 ? ($riskOverview['low'] / $collectionOverview['total_cases']) * 100 : 0, 100) }}%"
                    ></div>

                </div>

            </div>

        </div>

    </x-panel>

</section>



{{-- =========================================================
    BANK PERFORMANCE + CASE BUCKETS
========================================================= --}}

<section class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-3">

    <x-panel
        title="أداء البنوك (هذا الشهر)"
        icon="fa-building-columns"
        class="xl:col-span-2"
    >

        <div class="table-wrap">

            <table class="data-table whitespace-nowrap">

                <thead>

                    <tr>
                        <th>البنك</th>
                        <th>الحالات</th>
                        <th>المحفظة</th>
                        <th>المحصل</th>
                        <th class="w-44">نسبة التحصيل</th>
                    </tr>

                </thead>


                <tbody>

                    @foreach($banks as $bank)

                        @php

                            $barClass =
                                $bank->rate >= 40
                                ? 'bg-brand'
                                : ($bank->rate >= 20 ? 'bg-warning' : 'bg-danger');

                        @endphp

                        <tr>

                            <td>

                                <a
                                    href="{{ $bank->url }}"
                                    class="group inline-flex items-center gap-3"
                                >

                                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white text-black">

                                        <i class="fa-solid fa-building-columns text-xs"></i>

                                    </span>


                                    <span class="font-semibold text-fg transition group-hover:text-brand">

                                        {{ $bank->name }}

                                    </span>


                                    @unless($bank->is_active)

                                        <span class="badge badge-danger">
                                            متوقف
                                        </span>

                                    @endunless

                                </a>

                            </td>


                            <td>
                                {{ $bank->cases ?: '-' }}
                            </td>


                            <td>
                                {{ $bank->portfolio ? 'EGP ' . number_format($bank->portfolio) : '-' }}
                            </td>


                            <td class="font-semibold text-brand">

                                {{ $bank->collected ? 'EGP ' . number_format($bank->collected) : '-' }}

                            </td>


                            <td>

                                <div class="mb-1 text-[11px] text-muted">
                                    {{ $bank->rate }}%
                                </div>

                                <div class="progress">

                                    <div
                                        class="h-full rounded-full {{ $barClass }}"
                                        style="width: {{ $bank->rate }}%"
                                    ></div>

                                </div>

                            </td>

                        </tr>

                    @endforeach

                </tbody>


                <tfoot>

                    <tr class="bg-white/[0.03] font-bold text-fg">

                        <td class="px-4 py-3">
                            الإجمالي
                        </td>

                        <td class="px-4 py-3">
                            {{ $totals['cases'] }}
                        </td>

                        <td class="px-4 py-3">
                            EGP {{ number_format($totals['portfolio']) }}
                        </td>

                        <td class="px-4 py-3 text-brand">
                            EGP {{ number_format($totals['collected']) }}
                        </td>

                        <td class="px-4 py-3">

                            <a
                                href="{{ $links['banks'] }}"
                                class="text-xs font-semibold text-info hover:underline"
                            >

                                كل البنوك

                                <i class="fa-solid fa-chevron-left text-[9px]"></i>

                            </a>

                        </td>

                    </tr>

                </tfoot>

            </table>

        </div>

    </x-panel>


    <x-panel title="توزيع الحالات حسب الشريحة">

        <div class="relative flex h-[270px] justify-center">

            <canvas id="bucketChart"></canvas>

        </div>

    </x-panel>

</section>



{{-- =========================================================
    CUSTOMER / GUARANTOR OVERVIEW
========================================================= --}}

<section class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-3">

    <x-panel
        title="ملخص العملاء"
        icon="fa-users"
        class="xl:col-span-2"
    >

        <div class="grid grid-cols-2 gap-3 md:grid-cols-5">

            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="text-[11px] text-dim">
                    إجمالي العملاء
                </div>

                <div class="mt-2 text-xl font-bold text-fg">
                    {{ number_format($customerOverview['total']) }}
                </div>

            </div>


            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="text-[11px] text-dim">
                    أفراد
                </div>

                <div class="mt-2 text-xl font-bold text-info">
                    {{ number_format($customerOverview['individuals']) }}
                </div>

            </div>


            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="text-[11px] text-dim">
                    شركات
                </div>

                <div class="mt-2 text-xl font-bold text-accent">
                    {{ number_format($customerOverview['companies']) }}
                </div>

            </div>


            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="text-[11px] text-dim">
                    لديهم ضامن
                </div>

                <div class="mt-2 text-xl font-bold text-brand">
                    {{ number_format($customerOverview['with_guarantor']) }}
                </div>

            </div>


            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="text-[11px] text-dim">
                    بدون ضامن
                </div>

                <div class="mt-2 text-xl font-bold text-warning">
                    {{ number_format($customerOverview['without_guarantor']) }}
                </div>

            </div>

        </div>

    </x-panel>


    <x-panel
        title="الضامنين"
        icon="fa-user-shield"
    >

        <div class="space-y-3">

            <div class="flex items-center justify-between rounded-xl border border-line bg-white/[0.02] p-3">

                <span class="text-xs text-muted">
                    إجمالي الضامنين
                </span>

                <span class="font-bold text-fg">
                    {{ number_format($guarantorOverview['total']) }}
                </span>

            </div>


            <div class="flex items-center justify-between rounded-xl border border-line bg-white/[0.02] p-3">

                <span class="text-xs text-muted">
                    نشط
                </span>

                <span class="font-bold text-brand">
                    {{ number_format($guarantorOverview['active']) }}
                </span>

            </div>


            <div class="flex items-center justify-between rounded-xl border border-line bg-white/[0.02] p-3">

                <span class="text-xs text-muted">
                    تم التواصل معه
                </span>

                <span class="font-bold text-info">
                    {{ number_format($guarantorOverview['contacted']) }}
                </span>

            </div>


            <div class="flex items-center justify-between rounded-xl border border-line bg-white/[0.02] p-3">

                <span class="text-xs text-muted">
                    لم يتم التواصل
                </span>

                <span class="font-bold text-warning">
                    {{ number_format($guarantorOverview['uncontacted']) }}
                </span>

            </div>

        </div>

    </x-panel>

</section>



{{-- =========================================================
    TODAY OPERATIONS
========================================================= --}}

<section class="mb-6">

    <x-panel
        title="عمليات اليوم"
        icon="fa-calendar-day"
    >

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-9">

            <a href="{{ $links['calls_today'] ?? '#' }}" class="rounded-xl border border-line bg-white/[0.02] p-4 hover:bg-white/[0.05]">

                <i class="fa-solid fa-phone text-info"></i>

                <div class="mt-3 text-xl font-bold text-fg">
                    {{ number_format($todayOperations['calls']) }}
                </div>

                <div class="mt-1 text-[11px] text-dim">
                    اتصالات
                </div>

            </a>


            <a href="{{ $links['visits_today'] ?? '#' }}" class="rounded-xl border border-line bg-white/[0.02] p-4 hover:bg-white/[0.05]">

                <i class="fa-solid fa-location-dot text-accent"></i>

                <div class="mt-3 text-xl font-bold text-fg">
                    {{ number_format($todayOperations['visits']) }}
                </div>

                <div class="mt-1 text-[11px] text-dim">
                    زيارات
                </div>

            </a>


            <a href="{{ $links['scheduled_visits'] ?? '#' }}" class="rounded-xl border border-line bg-white/[0.02] p-4 hover:bg-white/[0.05]">

                <i class="fa-solid fa-calendar-check text-info"></i>

                <div class="mt-3 text-xl font-bold text-fg">
                    {{ number_format($todayOperations['scheduled_visits']) }}
                </div>

                <div class="mt-1 text-[11px] text-dim">
                    زيارات مجدولة
                </div>

            </a>


            <a href="{{ $links['completed_visits'] ?? '#' }}" class="rounded-xl border border-line bg-white/[0.02] p-4 hover:bg-white/[0.05]">

                <i class="fa-solid fa-check text-brand"></i>

                <div class="mt-3 text-xl font-bold text-fg">
                    {{ number_format($todayOperations['completed_visits']) }}
                </div>

                <div class="mt-1 text-[11px] text-dim">
                    زيارات مكتملة
                </div>

            </a>


            <a href="{{ $links['failed_visits'] ?? '#' }}" class="rounded-xl border border-line bg-white/[0.02] p-4 hover:bg-white/[0.05]">

                <i class="fa-solid fa-xmark text-danger"></i>

                <div class="mt-3 text-xl font-bold text-fg">
                    {{ number_format($todayOperations['failed_visits']) }}
                </div>

                <div class="mt-1 text-[11px] text-dim">
                    زيارات فاشلة
                </div>

            </a>


            <a href="{{ $links['due_ptp'] ?? '#' }}" class="rounded-xl border border-line bg-white/[0.02] p-4 hover:bg-white/[0.05]">

                <i class="fa-solid fa-handshake text-warning"></i>

                <div class="mt-3 text-xl font-bold text-fg">
                    {{ number_format($todayOperations['due_ptp']) }}
                </div>

                <div class="mt-1 text-[11px] text-dim">
                    وعود اليوم
                </div>

            </a>


            <a href="{{ $links['overdue_ptp'] ?? '#' }}" class="rounded-xl border border-line bg-white/[0.02] p-4 hover:bg-white/[0.05]">

                <i class="fa-solid fa-triangle-exclamation text-danger"></i>

                <div class="mt-3 text-xl font-bold text-fg">
                    {{ number_format($todayOperations['overdue_ptp']) }}
                </div>

                <div class="mt-1 text-[11px] text-dim">
                    وعود متأخرة
                </div>

            </a>


            <a href="{{ $links['followups'] ?? '#' }}" class="rounded-xl border border-line bg-white/[0.02] p-4 hover:bg-white/[0.05]">

                <i class="fa-solid fa-list-check text-info"></i>

                <div class="mt-3 text-xl font-bold text-fg">
                    {{ number_format($todayOperations['followups']) }}
                </div>

                <div class="mt-1 text-[11px] text-dim">
                    متابعات
                </div>

            </a>


            <a href="{{ $links['unassigned_cases'] ?? '#' }}" class="rounded-xl border border-line bg-white/[0.02] p-4 hover:bg-white/[0.05]">

                <i class="fa-solid fa-user-slash text-warning"></i>

                <div class="mt-3 text-xl font-bold text-fg">
                    {{ number_format($todayOperations['unassigned']) }}
                </div>

                <div class="mt-1 text-[11px] text-dim">
                    غير معينة
                </div>

            </a>

        </div>

    </x-panel>

</section>



{{-- =========================================================
    PTP INTELLIGENCE
========================================================= --}}

<section class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-3">

    <x-panel
        title="تحليل وعود الدفع PTP"
        icon="fa-handshake"
        class="xl:col-span-2"
    >

        <div class="grid grid-cols-2 gap-3 md:grid-cols-4">

            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="text-[11px] text-dim">
                    إجمالي الوعود
                </div>

                <div class="mt-2 text-xl font-bold text-fg">
                    {{ number_format($ptpIntelligence['total']) }}
                </div>

            </div>


            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="text-[11px] text-dim">
                    الوعود المحققة
                </div>

                <div class="mt-2 text-xl font-bold text-brand">
                    {{ number_format($ptpIntelligence['kept']) }}
                </div>

            </div>


            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="text-[11px] text-dim">
                    الوعود المكسورة
                </div>

                <div class="mt-2 text-xl font-bold text-danger">
                    {{ number_format($ptpIntelligence['broken']) }}
                </div>

            </div>


            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="text-[11px] text-dim">
                    الوعود القادمة
                </div>

                <div class="mt-2 text-xl font-bold text-info">
                    {{ number_format($ptpIntelligence['upcoming']) }}
                </div>

            </div>

        </div>


        <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-3">

            <div>

                <div class="mb-2 flex justify-between text-xs">

                    <span class="text-muted">
                        نسبة التحقيق
                    </span>

                    <span class="font-bold text-brand">
                        {{ $ptpIntelligence['achievement'] }}%
                    </span>

                </div>

                <div class="progress">

                    <div
                        class="h-full rounded-full bg-brand"
                        style="width: {{ min($ptpIntelligence['achievement'], 100) }}%"
                    ></div>

                </div>

            </div>


            <div class="rounded-xl border border-line bg-white/[0.02] p-3">

                <div class="text-[11px] text-dim">
                    قيمة الوعود
                </div>

                <div class="mt-1 font-bold text-fg">
                    EGP {{ number_format($ptpIntelligence['total_amount']) }}
                </div>

            </div>


            <div class="rounded-xl border border-line bg-white/[0.02] p-3">

                <div class="text-[11px] text-dim">
                    قيمة الوعود المحققة
                </div>

                <div class="mt-1 font-bold text-brand">
                    EGP {{ number_format($ptpIntelligence['kept_amount']) }}
                </div>

            </div>

        </div>

    </x-panel>


    <x-panel
        title="وعود الدفع القادمة"
        icon="fa-calendar-check"
    >

        <ul class="space-y-2">

            @forelse($upcomingPtp as $item)

                <li>

                    <a
                        href="{{ $item['url'] ?? '#' }}"
                        class="flex items-center gap-3 rounded-xl border border-line bg-white/[0.02] p-3 hover:bg-white/[0.05]"
                    >

                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-warning/15 text-warning">

                            <i class="fa-solid fa-handshake"></i>

                        </span>


                        <span class="min-w-0 flex-1">

                            <span class="block truncate text-xs font-semibold text-fg">
                                {{ $item['client'] ?? '-' }}
                            </span>

                            <span class="mt-1 block text-[11px] text-dim">
                                {{ $item['date'] ?? '-' }}
                            </span>

                        </span>


                        <span class="text-xs font-bold text-brand">
                            EGP {{ number_format($item['amount'] ?? 0) }}
                        </span>

                    </a>

                </li>

            @empty

                <x-empty-state
                    icon="fa-handshake"
                    text="لا توجد وعود قادمة"
                />

            @endforelse

        </ul>

    </x-panel>

</section>



{{-- =========================================================
    EMPLOYEE OVERVIEW
========================================================= --}}

<section class="mb-6">

    <x-panel
        title="نظرة عامة على الموظفين"
        icon="fa-users-gear"
    >

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-6">

            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="text-[11px] text-dim">
                    إجمالي الموظفين
                </div>

                <div class="mt-2 text-xl font-bold text-fg">
                    {{ number_format($employeeOverview['total']) }}
                </div>

            </div>


            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="text-[11px] text-dim">
                    الموظفون النشطون
                </div>

                <div class="mt-2 text-xl font-bold text-brand">
                    {{ number_format($employeeOverview['active']) }}
                </div>

            </div>


            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="text-[11px] text-dim">
                    المتاحون
                </div>

                <div class="mt-2 text-xl font-bold text-info">
                    {{ number_format($employeeOverview['available']) }}
                </div>

            </div>


            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="text-[11px] text-dim">
                    في إجازة
                </div>

                <div class="mt-2 text-xl font-bold text-warning">
                    {{ number_format($employeeOverview['on_leave']) }}
                </div>

            </div>


            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="text-[11px] text-dim">
                    متوسط الحالات
                </div>

                <div class="mt-2 text-xl font-bold text-fg">
                    {{ number_format($employeeOverview['avg_cases']) }}
                </div>

            </div>


            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="text-[11px] text-dim">
                    متوسط التحصيل
                </div>

                <div class="mt-2 text-xl font-bold text-brand">
                    EGP {{ number_format($employeeOverview['avg_collected']) }}
                </div>

            </div>

        </div>

    </x-panel>

</section>



{{-- =========================================================
    SUPERVISOR PERFORMANCE
========================================================= --}}

<section class="mb-6">

    <x-panel
        title="أداء المشرفين"
        icon="fa-user-tie"
    >

        <div class="table-wrap">

            <table class="data-table whitespace-nowrap">

                <thead>

                    <tr>
                        <th>المشرف</th>
                        <th>الموظفون</th>
                        <th>الحالات</th>
                        <th>المحصل</th>
                        <th>المستهدف</th>
                        <th>الإنجاز</th>
                    </tr>

                </thead>


                <tbody>

                    @forelse($supervisorPerformance as $supervisor)

                        <tr>

                            <td class="font-semibold text-fg">
                                {{ $supervisor['name'] ?? '-' }}
                            </td>

                            <td>
                                {{ number_format($supervisor['employees'] ?? 0) }}
                            </td>

                            <td>
                                {{ number_format($supervisor['cases'] ?? 0) }}
                            </td>

                            <td class="font-semibold text-brand">
                                EGP {{ number_format($supervisor['collected'] ?? 0) }}
                            </td>

                            <td>
                                EGP {{ number_format($supervisor['target'] ?? 0) }}
                            </td>

                            <td>

                                <div class="mb-1 text-[11px] text-muted">
                                    {{ $supervisor['rate'] ?? 0 }}%
                                </div>

                                <div class="progress">

                                    <div
                                        class="h-full rounded-full {{ ($supervisor['rate'] ?? 0) >= 70 ? 'bg-brand' : (($supervisor['rate'] ?? 0) >= 40 ? 'bg-warning' : 'bg-danger') }}"
                                        style="width: {{ min($supervisor['rate'] ?? 0, 100) }}%"
                                    ></div>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="6" class="text-center">

                                <x-empty-state
                                    icon="fa-user-tie"
                                    text="لا توجد بيانات للمشرفين"
                                />

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </x-panel>

</section>



{{-- =========================================================
    TODAY TASKS / ALERTS / CONTACT
========================================================= --}}

<section class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-3">


    {{-- TODAY AGENDA --}}

    <x-panel
        title="مهام اليوم"
        icon="fa-list-check"
    >

        <ul class="space-y-2">

            @foreach($agenda as [$text, $count, $icon, $toneClass, $url])

                <li>

                    <a
                        href="{{ $url }}"
                        class="flex items-center gap-3 rounded-xl border border-line bg-white/[0.02] px-3 py-2.5 transition hover:bg-white/[0.05]"
                    >

                        <i class="fa-solid {{ $icon }} w-4 text-center text-sm {{ $toneClass }}"></i>

                        <span class="flex-1 text-sm text-fg">
                            {{ $text }}
                        </span>

                        <span class="badge badge-neutral">
                            {{ $count }}
                        </span>

                        <i class="fa-solid fa-chevron-left text-[10px] text-dim"></i>

                    </a>

                </li>

            @endforeach

        </ul>

    </x-panel>



    {{-- CONTACT PERFORMANCE --}}

    <x-panel
        title="حالة التواصل"
        icon="fa-phone"
    >

        <div class="space-y-3">

            <div class="flex items-center justify-between rounded-xl border border-line bg-white/[0.02] p-3">

                <span class="text-xs text-muted">
                    تم التواصل اليوم
                </span>

                <span class="font-bold text-brand">
                    {{ number_format($contactOverview['contacted_today']) }}
                </span>

            </div>


            <div class="flex items-center justify-between rounded-xl border border-line bg-white/[0.02] p-3">

                <span class="text-xs text-muted">
                    لم يتم التواصل
                </span>

                <span class="font-bold text-warning">
                    {{ number_format($contactOverview['not_contacted']) }}
                </span>

            </div>


            <div class="flex items-center justify-between rounded-xl border border-line bg-white/[0.02] p-3">

                <span class="text-xs text-muted">
                    تواصل ناجح
                </span>

                <span class="font-bold text-brand">
                    {{ number_format($contactOverview['successful']) }}
                </span>

            </div>


            <div class="flex items-center justify-between rounded-xl border border-line bg-white/[0.02] p-3">

                <span class="text-xs text-muted">
                    تواصل غير ناجح
                </span>

                <span class="font-bold text-danger">
                    {{ number_format($contactOverview['unsuccessful']) }}
                </span>

            </div>


            <div class="flex items-center justify-between rounded-xl border border-line bg-white/[0.02] p-3">

                <span class="text-xs text-muted">
                    رقم خاطئ
                </span>

                <span class="font-bold text-warning">
                    {{ number_format($contactOverview['wrong_number']) }}
                </span>

            </div>

        </div>

    </x-panel>



    {{-- ALERTS --}}

    <x-panel
        title="تنبيهات تحتاج إجراء"
        icon="fa-triangle-exclamation"
        tone="danger"
    >

        <ul class="space-y-2">

            @forelse($alerts as [$icon, $tone, $text, $url])

                <li>

                    <a
                        href="{{ $url }}"
                        class="flex items-start gap-3 rounded-xl border p-3 transition hover:brightness-125 {{ $alertTones[$tone][0] }}"
                    >

                        <i class="fa-solid {{ $icon }} mt-0.5 text-sm {{ $alertTones[$tone][1] }}"></i>

                        <span class="text-xs leading-5 text-fg">
                            {{ $text }}
                        </span>

                    </a>

                </li>

            @empty

                <x-empty-state
                    icon="fa-circle-check"
                    text="لا توجد تنبيهات، كل شيء على ما يرام"
                />

            @endforelse

        </ul>

    </x-panel>

</section>



{{-- =========================================================
    VISITS
========================================================= --}}

<section class="mb-6">

    <x-panel
        title="الزيارات الميدانية"
        icon="fa-location-dot"
    >

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">

            <a
                href="{{ $links['visits_today'] ?? '#' }}"
                class="rounded-xl border border-line bg-white/[0.02] p-4 hover:bg-white/[0.05]"
            >

                <div class="flex items-center justify-between">

                    <i class="fa-solid fa-calendar-day text-info"></i>

                    <span class="text-xl font-bold text-fg">
                        {{ number_format($visitOverview['today']) }}
                    </span>

                </div>

                <div class="mt-3 text-xs text-dim">
                    زيارات اليوم
                </div>

            </a>


            <a
                href="{{ $links['scheduled_visits'] ?? '#' }}"
                class="rounded-xl border border-line bg-white/[0.02] p-4 hover:bg-white/[0.05]"
            >

                <div class="flex items-center justify-between">

                    <i class="fa-solid fa-calendar-check text-warning"></i>

                    <span class="text-xl font-bold text-fg">
                        {{ number_format($visitOverview['scheduled']) }}
                    </span>

                </div>

                <div class="mt-3 text-xs text-dim">
                    مجدولة
                </div>

            </a>


            <a
                href="{{ $links['completed_visits'] ?? '#' }}"
                class="rounded-xl border border-line bg-white/[0.02] p-4 hover:bg-white/[0.05]"
            >

                <div class="flex items-center justify-between">

                    <i class="fa-solid fa-circle-check text-brand"></i>

                    <span class="text-xl font-bold text-fg">
                        {{ number_format($visitOverview['completed']) }}
                    </span>

                </div>

                <div class="mt-3 text-xs text-dim">
                    مكتملة
                </div>

            </a>


            <a
                href="{{ $links['failed_visits'] ?? '#' }}"
                class="rounded-xl border border-line bg-white/[0.02] p-4 hover:bg-white/[0.05]"
            >

                <div class="flex items-center justify-between">

                    <i class="fa-solid fa-circle-xmark text-danger"></i>

                    <span class="text-xl font-bold text-fg">
                        {{ number_format($visitOverview['failed']) }}
                    </span>

                </div>

                <div class="mt-3 text-xs text-dim">
                    فاشلة
                </div>

            </a>

        </div>

    </x-panel>

</section>



{{-- =========================================================
    UPCOMING VISITS
========================================================= --}}

<section class="mb-6">

    <x-panel
        title="الزيارات القادمة"
        icon="fa-map-location-dot"
    >

        <div class="table-wrap">

            <table class="data-table whitespace-nowrap">

                <thead>

                    <tr>
                        <th>العميل</th>
                        <th>الموظف</th>
                        <th>التاريخ</th>
                        <th>الوقت</th>
                        <th>الحالة</th>
                        <th></th>
                    </tr>

                </thead>


                <tbody>

                    @forelse($upcomingVisits as $visit)

                        <tr>

                            <td class="font-semibold text-fg">
                                {{ $visit['client'] ?? '-' }}
                            </td>

                            <td>
                                {{ $visit['employee'] ?? '-' }}
                            </td>

                            <td>
                                {{ $visit['date'] ?? '-' }}
                            </td>

                            <td>
                                {{ $visit['time'] ?? '-' }}
                            </td>

                            <td>

                                <span class="badge badge-info">
                                    {{ $visit['status'] ?? 'مجدولة' }}
                                </span>

                            </td>

                            <td>

                                <a
                                    href="{{ $visit['url'] ?? '#' }}"
                                    class="text-info hover:underline"
                                >

                                    <i class="fa-solid fa-arrow-left"></i>

                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="6">

                                <x-empty-state
                                    icon="fa-location-dot"
                                    text="لا توجد زيارات قادمة"
                                />

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </x-panel>

</section>



{{-- =========================================================
    COMPLAINTS
========================================================= --}}

<section class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-3">

    <x-panel
        title="الشكاوى"
        icon="fa-comments"
        class="xl:col-span-2"
    >

        <div class="grid grid-cols-2 gap-3 md:grid-cols-4">

            <a
                href="{{ $links['complaints_open'] ?? '#' }}"
                class="rounded-xl border border-line bg-white/[0.02] p-4 hover:bg-white/[0.05]"
            >

                <div class="text-[11px] text-dim">
                    مفتوحة
                </div>

                <div class="mt-2 text-2xl font-bold text-danger">
                    {{ number_format($complaintsOverview['open']) }}
                </div>

            </a>


            <a
                href="{{ $links['complaints_processing'] ?? '#' }}"
                class="rounded-xl border border-line bg-white/[0.02] p-4 hover:bg-white/[0.05]"
            >

                <div class="text-[11px] text-dim">
                    قيد المعالجة
                </div>

                <div class="mt-2 text-2xl font-bold text-warning">
                    {{ number_format($complaintsOverview['processing']) }}
                </div>

            </a>


            <a
                href="{{ $links['complaints_closed'] ?? '#' }}"
                class="rounded-xl border border-line bg-white/[0.02] p-4 hover:bg-white/[0.05]"
            >

                <div class="text-[11px] text-dim">
                    مغلقة
                </div>

                <div class="mt-2 text-2xl font-bold text-brand">
                    {{ number_format($complaintsOverview['closed']) }}
                </div>

            </a>


            <a
                href="{{ $links['complaints_overdue'] ?? '#' }}"
                class="rounded-xl border border-line bg-white/[0.02] p-4 hover:bg-white/[0.05]"
            >

                <div class="text-[11px] text-dim">
                    متأخرة
                </div>

                <div class="mt-2 text-2xl font-bold text-danger">
                    {{ number_format($complaintsOverview['overdue']) }}
                </div>

            </a>

        </div>

    </x-panel>


    <x-panel
        title="أحدث الشكاوى"
        icon="fa-message"
    >

        <ul class="space-y-2">

            @forelse($recentComplaints as $complaint)

                <li>

                    <a
                        href="{{ $complaint['url'] ?? '#' }}"
                        class="block rounded-xl border border-line bg-white/[0.02] p-3 hover:bg-white/[0.05]"
                    >

                        <div class="flex items-center justify-between gap-2">

                            <span class="truncate text-xs font-semibold text-fg">
                                {{ $complaint['title'] ?? '-' }}
                            </span>

                            <span class="badge badge-warning">
                                {{ $complaint['status'] ?? '-' }}
                            </span>

                        </div>


                        <div class="mt-2 text-[11px] text-dim">
                            {{ $complaint['time'] ?? '-' }}
                        </div>

                    </a>

                </li>

            @empty

                <x-empty-state
                    icon="fa-comments"
                    text="لا توجد شكاوى حديثة"
                />

            @endforelse

        </ul>

    </x-panel>

</section>



{{-- =========================================================
    PAYMENT OVERVIEW
========================================================= --}}

<section class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-3">

    <x-panel
        title="تحصيلات اليوم"
        icon="fa-money-bill-transfer"
        class="xl:col-span-2"
    >

        <div class="grid grid-cols-2 gap-3 md:grid-cols-4">

            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="text-[11px] text-dim">
                    إجمالي اليوم
                </div>

                <div class="mt-2 text-xl font-bold text-brand">
                    EGP {{ number_format($paymentOverview['today_amount']) }}
                </div>

            </div>


            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="text-[11px] text-dim">
                    عدد العمليات
                </div>

                <div class="mt-2 text-xl font-bold text-fg">
                    {{ number_format($paymentOverview['today_count']) }}
                </div>

            </div>


            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="text-[11px] text-dim">
                    متوسط العملية
                </div>

                <div class="mt-2 text-xl font-bold text-info">
                    EGP {{ number_format($paymentOverview['average']) }}
                </div>

            </div>


            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="text-[11px] text-dim">
                    نقدي
                </div>

                <div class="mt-2 text-xl font-bold text-fg">
                    EGP {{ number_format($paymentOverview['cash']) }}
                </div>

            </div>

        </div>

    </x-panel>


    <x-panel
        title="طرق الدفع"
        icon="fa-credit-card"
    >

        <div class="space-y-3">

            <div class="flex justify-between">

                <span class="text-xs text-muted">
                    بنك
                </span>

                <span class="font-bold text-fg">
                    EGP {{ number_format($paymentOverview['bank']) }}
                </span>

            </div>


            <div class="flex justify-between">

                <span class="text-xs text-muted">
                    محافظ إلكترونية
                </span>

                <span class="font-bold text-info">
                    EGP {{ number_format($paymentOverview['wallet']) }}
                </span>

            </div>


            <div class="flex justify-between">

                <span class="text-xs text-muted">
                    Online
                </span>

                <span class="font-bold text-brand">
                    EGP {{ number_format($paymentOverview['online']) }}
                </span>

            </div>


            <div class="flex justify-between">

                <span class="text-xs text-muted">
                    نقدي
                </span>

                <span class="font-bold text-warning">
                    EGP {{ number_format($paymentOverview['cash']) }}
                </span>

            </div>

        </div>

    </x-panel>

</section>



{{-- =========================================================
    TOP EMPLOYEES + LATEST PAYMENTS + ACTIVITY
========================================================= --}}

<section class="grid grid-cols-1 gap-4 xl:grid-cols-3">


    {{-- TOP EMPLOYEES --}}

    <x-panel
        title="أفضل الموظفين"
        icon="fa-trophy"
    >

        <ul class="space-y-4">

            @forelse($leaders as $leader)

                <li>

                    <a
                        href="{{ $leader->url }}"
                        class="block"
                    >

                        <div class="flex items-center gap-3">

                            <span class="w-4 text-center text-xs font-bold text-dim">
                                {{ $loop->iteration }}
                            </span>


                            <x-avatar
                                :name="$leader->name"
                                :color="$loop->first ? 'warning' : 'info'"
                            />


                            <div class="min-w-0 flex-1">

                                <div class="flex items-center justify-between gap-2">

                                    <span class="truncate text-sm font-semibold text-fg">
                                        {{ $leader->name }}
                                    </span>

                                    <span class="text-xs font-bold text-brand">
                                        EGP {{ number_format($leader->collected) }}
                                    </span>

                                </div>


                                <div class="mt-1.5 flex items-center gap-2">

                                    <div class="progress flex-1">

                                        <div
                                            class="h-full rounded-full {{ $toneBar[$leader->tone] }}"
                                            style="width: {{ $leader->efficiency }}%"
                                        ></div>

                                    </div>


                                    <span class="text-[11px] font-bold {{ $toneText[$leader->tone] }}">
                                        {{ $leader->efficiency }}%
                                    </span>

                                </div>

                            </div>

                        </div>

                    </a>

                </li>

            @empty

                <x-empty-state
                    text="لا توجد بيانات"
                />

            @endforelse

        </ul>


        <a
            href="{{ $links['employees'] }}"
            class="btn btn-outline-info btn-sm mt-5 w-full"
        >

            كل الموظفين

            <i class="fa-solid fa-chevron-left text-[9px]"></i>

        </a>

    </x-panel>



    {{-- LATEST PAYMENTS --}}

    <x-panel
        title="آخر التحصيلات"
        icon="fa-receipt"
    >

        <ul class="divide-y divide-line">

            @foreach($payments as [$receipt, $client, $amount, $method, $time, $status])

                <li class="flex items-center justify-between gap-3 py-3 first:pt-0">

                    <div class="min-w-0">

                        <div class="flex items-center gap-2">

                            <span class="badge badge-info font-mono">
                                {{ $receipt }}
                            </span>

                            <span class="truncate text-xs font-semibold text-fg">
                                {{ $client }}
                            </span>

                        </div>


                        <div class="mt-1 text-[11px] text-dim">

                            {{ $methods[$method] }}

                            ·

                            {{ $time }}

                        </div>

                    </div>


                    <div class="shrink-0 text-left">

                        <div class="text-sm font-bold text-brand">
                            +{{ number_format($amount) }}
                        </div>

                        <x-status-badge
                            type="payment"
                            :status="$status"
                        />

                    </div>

                </li>

            @endforeach

        </ul>


        <a
            href="{{ $links['confirmations'] }}"
            class="btn btn-outline-info btn-sm mt-5 w-full"
        >

            تأكيد التحصيلات

            <i class="fa-solid fa-chevron-left text-[9px]"></i>

        </a>

    </x-panel>



    {{-- ACTIVITY --}}

    <x-panel
        title="آخر النشاط"
        icon="fa-clock-rotate-left"
    >

        <ol class="space-y-4">

            @foreach($activity as [$user, $text, $time, $icon, $toneClass])

                <li class="flex items-start gap-3">

                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/[0.05] {{ $toneClass }}">

                        <i class="fa-solid {{ $icon }} text-xs"></i>

                    </span>


                    <div>

                        <p class="text-xs leading-5 text-fg">

                            <b>{{ $user }}</b>

                            {{ $text }}

                        </p>


                        <p class="mt-0.5 text-[11px] text-dim">
                            {{ $time }}
                        </p>

                    </div>

                </li>

            @endforeach

        </ol>


        <a
            href="{{ $links['activity'] }}"
            class="btn btn-outline-info btn-sm mt-5 w-full"
        >

            سجل النشاط الكامل

            <i class="fa-solid fa-chevron-left text-[9px]"></i>

        </a>

    </x-panel>

</section>



{{-- =========================================================
    CRITICAL CASES
========================================================= --}}

<section class="mt-6">

    <x-panel
        title="حالات تحتاج تدخل فوري"
        icon="fa-triangle-exclamation"
        tone="danger"
    >

        <div class="table-wrap">

            <table class="data-table whitespace-nowrap">

                <thead>

                    <tr>

                        <th>العميل</th>
                        <th>البنك</th>
                        <th>الموظف</th>
                        <th>قيمة الدين</th>
                        <th>آخر متابعة</th>
                        <th>سبب التنبيه</th>
                        <th></th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($criticalCases as $case)

                        <tr>

                            <td class="font-semibold text-fg">
                                {{ $case['client'] ?? '-' }}
                            </td>

                            <td>
                                {{ $case['bank'] ?? '-' }}
                            </td>

                            <td>
                                {{ $case['employee'] ?? 'غير معين' }}
                            </td>

                            <td class="font-semibold text-warning">
                                EGP {{ number_format($case['amount'] ?? 0) }}
                            </td>

                            <td>
                                {{ $case['last_followup'] ?? '-' }}
                            </td>

                            <td>

                                <span class="badge badge-danger">
                                    {{ $case['reason'] ?? 'تحتاج متابعة' }}
                                </span>

                            </td>

                            <td>

                                <a
                                    href="{{ $case['url'] ?? '#' }}"
                                    class="text-info hover:underline"
                                >

                                    فتح

                                    <i class="fa-solid fa-arrow-left text-[9px]"></i>

                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="7">

                                <x-empty-state
                                    icon="fa-circle-check"
                                    text="لا توجد حالات حرجة حالياً"
                                />

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </x-panel>

</section>



{{-- =========================================================
    SYSTEM HEALTH
========================================================= --}}

<section class="mt-6 grid grid-cols-1 gap-4 xl:grid-cols-3">

    <x-panel
        title="حالة النظام"
        icon="fa-server"
        class="xl:col-span-2"
    >

        <div class="grid grid-cols-2 gap-3 md:grid-cols-3">

            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="flex items-center gap-2">

                    <i class="fa-solid fa-file-import text-info"></i>

                    <span class="text-[11px] text-dim">
                        آخر استيراد
                    </span>

                </div>

                <div class="mt-2 text-sm font-bold text-fg">
                    {{ $systemHealth['last_import'] ?? 'لا يوجد' }}
                </div>

            </div>


            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="flex items-center gap-2">

                    <i class="fa-solid fa-database text-brand"></i>

                    <span class="text-[11px] text-dim">
                        سجلات مستوردة
                    </span>

                </div>

                <div class="mt-2 text-xl font-bold text-brand">
                    {{ number_format($systemHealth['imported_records']) }}
                </div>

            </div>


            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="flex items-center gap-2">

                    <i class="fa-solid fa-database text-danger"></i>

                    <span class="text-[11px] text-dim">
                        سجلات فاشلة
                    </span>

                </div>

                <div class="mt-2 text-xl font-bold text-danger">
                    {{ number_format($systemHealth['failed_records']) }}
                </div>

            </div>


            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="flex items-center gap-2">

                    <i class="fa-solid fa-clock text-info"></i>

                    <span class="text-[11px] text-dim">
                        آخر نشاط
                    </span>

                </div>

                <div class="mt-2 text-sm font-bold text-fg">
                    {{ $systemHealth['last_activity'] ?? 'لا يوجد' }}
                </div>

            </div>


            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="flex items-center gap-2">

                    <i class="fa-solid fa-file-export text-warning"></i>

                    <span class="text-[11px] text-dim">
                        آخر تصدير
                    </span>

                </div>

                <div class="mt-2 text-sm font-bold text-fg">
                    {{ $systemHealth['last_export'] ?? 'لا يوجد' }}
                </div>

            </div>


            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="flex items-center gap-2">

                    <i class="fa-solid fa-bell text-warning"></i>

                    <span class="text-[11px] text-dim">
                        إشعارات غير مقروءة
                    </span>

                </div>

                <div class="mt-2 text-xl font-bold text-warning">
                    {{ number_format($systemHealth['unread_notifications']) }}
                </div>

            </div>

        </div>

    </x-panel>


    <x-panel
        title="الإدارة والتقارير"
        icon="fa-chart-pie"
    >

        <div class="space-y-2">

            <a
                href="{{ $links['daily_reports'] ?? '#' }}"
                class="flex items-center justify-between rounded-xl border border-line bg-white/[0.02] p-3 hover:bg-white/[0.05]"
            >

                <span class="flex items-center gap-3">

                    <i class="fa-solid fa-file-lines text-info"></i>

                    <span class="text-xs text-fg">
                        التقارير اليومية
                    </span>

                </span>

                <span class="badge badge-info">
                    {{ number_format($managementSnapshot['daily_reports']) }}
                </span>

            </a>


            <a
                href="{{ $links['performance'] ?? '#' }}"
                class="flex items-center justify-between rounded-xl border border-line bg-white/[0.02] p-3 hover:bg-white/[0.05]"
            >

                <span class="flex items-center gap-3">

                    <i class="fa-solid fa-chart-line text-brand"></i>

                    <span class="text-xs text-fg">
                        لقطات الأداء
                    </span>

                </span>

                <span class="badge badge-info">
                    {{ number_format($managementSnapshot['performance']) }}
                </span>

            </a>


            <a
                href="{{ $links['monthly_archives'] ?? '#' }}"
                class="flex items-center justify-between rounded-xl border border-line bg-white/[0.02] p-3 hover:bg-white/[0.05]"
            >

                <span class="flex items-center gap-3">

                    <i class="fa-solid fa-box-archive text-warning"></i>

                    <span class="text-xs text-fg">
                        الأرشيف الشهري
                    </span>

                </span>

                <span class="badge badge-info">
                    {{ number_format($managementSnapshot['monthly_archives']) }}
                </span>

            </a>


            <a
                href="{{ $links['report_exports'] ?? '#' }}"
                class="flex items-center justify-between rounded-xl border border-line bg-white/[0.02] p-3 hover:bg-white/[0.05]"
            >

                <span class="flex items-center gap-3">

                    <i class="fa-solid fa-file-export text-accent"></i>

                    <span class="text-xs text-fg">
                        تصديرات التقارير
                    </span>

                </span>

                <span class="badge badge-info">
                    {{ number_format($managementSnapshot['report_exports']) }}
                </span>

            </a>


            <a
                href="{{ $links['activity'] ?? '#' }}"
                class="flex items-center justify-between rounded-xl border border-line bg-white/[0.02] p-3 hover:bg-white/[0.05]"
            >

                <span class="flex items-center gap-3">

                    <i class="fa-solid fa-clock-rotate-left text-info"></i>

                    <span class="text-xs text-fg">
                        سجل النشاط
                    </span>

                </span>

                <span class="badge badge-info">
                    {{ number_format($managementSnapshot['activity_logs']) }}
                </span>

            </a>

        </div>

    </x-panel>

</section>



{{-- =========================================================
    RECENT IMPORTS
========================================================= --}}

<section class="mt-6">

    <x-panel
        title="آخر عمليات استيراد المحافظ"
        icon="fa-file-import"
    >

        <div class="table-wrap">

            <table class="data-table whitespace-nowrap">

                <thead>

                    <tr>

                        <th>الملف</th>
                        <th>البنك</th>
                        <th>السجلات</th>
                        <th>ناجح</th>
                        <th>فاشل</th>
                        <th>المستخدم</th>
                        <th>التاريخ</th>
                        <th>الحالة</th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($recentImports as $import)

                        <tr>

                            <td class="font-semibold text-fg">
                                {{ $import['file'] ?? '-' }}
                            </td>

                            <td>
                                {{ $import['bank'] ?? '-' }}
                            </td>

                            <td>
                                {{ number_format($import['records'] ?? 0) }}
                            </td>

                            <td class="font-semibold text-brand">
                                {{ number_format($import['success'] ?? 0) }}
                            </td>

                            <td class="font-semibold text-danger">
                                {{ number_format($import['failed'] ?? 0) }}
                            </td>

                            <td>
                                {{ $import['user'] ?? '-' }}
                            </td>

                            <td>
                                {{ $import['date'] ?? '-' }}
                            </td>

                            <td>

                                <span class="badge {{ ($import['status'] ?? '') === 'success' ? 'badge-info' : 'badge-danger' }}">

                                    {{ $import['status_label'] ?? 'غير معروف' }}

                                </span>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="8">

                                <x-empty-state
                                    icon="fa-file-import"
                                    text="لا توجد عمليات استيراد حديثة"
                                />

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </x-panel>

</section>



@endsection



{{-- =========================================================
    CHARTS JAVASCRIPT
========================================================= --}}

@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

    /*
    |--------------------------------------------------------------------------
    | DESIGN TOKENS
    |--------------------------------------------------------------------------
    */

    const css = getComputedStyle(document.documentElement);

    const token = (name, fallback) =>
        css.getPropertyValue(`--color-${name}`).trim() || fallback;


    const brand   = token('brand',   '#00ff66');
    const info    = token('info',    '#00aaff');
    const warning = token('warning', '#facc15');
    const danger  = token('danger',  '#f87171');
    const muted   = token('muted',   '#8c98a5');
    const line    = token('line',    'rgba(255,255,255,0.06)');
    const track   = token('track',   '#1e252b');


    Chart.defaults.font.family = 'Cairo';

    Chart.defaults.color = muted;



    /*
    |--------------------------------------------------------------------------
    | 1. COLLECTION TREND
    |--------------------------------------------------------------------------
    */

    new Chart(
        document.getElementById('trendChart'),
        {
            type: 'bar',

            data: {

                labels: @json($trend['labels']),

                datasets: [

                    {
                        label: 'المحصل',

                        data: @json($trend['collected']),

                        backgroundColor: brand,

                        borderRadius: 6,

                        maxBarThickness: 38,
                    },

                    {
                        type: 'line',

                        label: 'المستهدف',

                        data: @json($trend['target']),

                        borderColor: info,

                        borderWidth: 2,

                        borderDash: [6, 6],

                        pointRadius: 3,

                        pointBackgroundColor: info,

                        tension: 0.35,
                    },

                ],
            },


            options: {

                responsive: true,

                maintainAspectRatio: false,

                scales: {

                    x: {
                        grid: {
                            color: line
                        }
                    },

                    y: {

                        grid: {
                            color: line
                        },

                        beginAtZero: true

                    },

                },

                plugins: {

                    legend: {
                        position: 'bottom'
                    },

                },

            },

        }
    );



    /*
    |--------------------------------------------------------------------------
    | 2. PORTFOLIO SPLIT
    |--------------------------------------------------------------------------
    */

    new Chart(
        document.getElementById('splitChart'),
        {

            type: 'doughnut',

            data: {

                labels: @json($split['labels']),

                datasets: [

                    {

                        data: @json($split['data']),

                        backgroundColor: [
                            brand,
                            track
                        ],

                        borderWidth: 0,

                    },

                ],

            },


            options: {

                responsive: true,

                maintainAspectRatio: false,

                cutout: '72%',

                plugins: {

                    legend: {
                        position: 'bottom'
                    },

                },

            },

        }
    );



    /*
    |--------------------------------------------------------------------------
    | 3. CASE BUCKETS
    |--------------------------------------------------------------------------
    */

    new Chart(
        document.getElementById('bucketChart'),
        {

            type: 'doughnut',

            data: {

                labels: @json($buckets['labels']),

                datasets: [

                    {

                        data: @json($buckets['data']),

                        backgroundColor: [
                            brand,
                            info,
                            warning,
                            danger
                        ],

                        borderWidth: 0,

                    },

                ],

            },


            options: {

                responsive: true,

                maintainAspectRatio: false,

                cutout: '65%',

                plugins: {

                    legend: {
                        position: 'bottom'
                    },

                },

            },

        }
    );

</script>

@endpush