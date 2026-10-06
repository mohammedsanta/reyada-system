{{-- resources/views/overview/index.blade.php --}}
@extends('layouts.app')

@section('title', 'نظرة عامة')

@section('content')

    @php
        /*
        |--------------------------------------------------------------------------
        | Safe calculated values
        |--------------------------------------------------------------------------
        | Everything below is calculated from the existing data already
        | provided by the controller. No new controller variables are required.
        */

        $selectedBankName = $selected !== 0
            ? ($bankOptions[$selected] ?? 'البنك المحدد')
            : 'كل البنوك';

        $totalCases = (int) ($totals['cases'] ?? 0);
        $totalPortfolio = (float) ($totals['portfolio'] ?? 0);
        $totalCollected = (float) ($totals['collected'] ?? 0);

        $totalRemaining = max($totalPortfolio - $totalCollected, 0);

        $collectionRate = $totalPortfolio > 0
            ? min(($totalCollected / $totalPortfolio) * 100, 100)
            : 0;

        $remainingRate = max(100 - $collectionRate, 0);

        $bankCount = is_countable($banks) ? count($banks) : 0;

        $trendLabels = $trend['labels'] ?? [];
        $trendCollected = $trend['collected'] ?? [];
        $trendTarget = $trend['target'] ?? [];

        $bucketLabels = $bucket['labels'] ?? [];
        $bucketData = $bucket['data'] ?? [];

        $loanLabels = $loanTypes['labels'] ?? [];
        $loanData = $loanTypes['data'] ?? [];

        $govLabels = $govs['labels'] ?? [];
        $govData = $govs['data'] ?? [];

        $agingRows = $aging ?? collect();

        $lastTrendCollected = !empty($trendCollected)
            ? (float) end($trendCollected)
            : 0;

        $lastTrendTarget = !empty($trendTarget)
            ? (float) end($trendTarget)
            : 0;

        $trendAchievement = $lastTrendTarget > 0
            ? min(($lastTrendCollected / $lastTrendTarget) * 100, 100)
            : 0;
    @endphp


    {{-- ================================================================
         PAGE HEADER
         ================================================================ --}}
    <x-page-header
        title="نظرة عامة"
        subtitle="تحليل تكوين المحفظة وأداء البنوك"
        icon="fa-chart-pie"
    />


    {{-- ================================================================
         PAGE STATUS / CONTEXT
         ================================================================ --}}
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

        <div class="flex flex-wrap items-center gap-2">

            <span class="inline-flex items-center gap-2 rounded-full border border-border bg-card px-3 py-1.5 text-xs text-muted">
                <span class="h-2 w-2 rounded-full bg-brand"></span>
                البيانات الحالية:
                <span class="font-semibold text-fg">
                    {{ $selectedBankName }}
                </span>
            </span>

            @if($selected !== 0)
                <span class="inline-flex items-center gap-2 rounded-full border border-info/20 bg-info/5 px-3 py-1.5 text-xs text-info">
                    <i class="fa-solid fa-filter text-[10px]"></i>
                    فلتر البنك مفعل
                </span>
            @endif

        </div>


        <div class="flex items-center gap-2">

            <span
                id="overviewLastUpdated"
                class="hidden text-[11px] text-muted sm:inline-flex"
            >
                <i class="fa-regular fa-clock ml-1"></i>
                تم العرض الآن
            </span>

            <button
                type="button"
                id="refreshOverview"
                class="btn btn-secondary btn-sm"
                title="تحديث الصفحة"
                aria-label="تحديث الصفحة"
            >
                <i class="fa-solid fa-rotate-right"></i>
            </button>

        </div>

    </div>


    {{-- ================================================================
         FILTER
         ================================================================ --}}
    <form
        method="GET"
        action="{{ route('overview.index') }}"
        class="card filter-bar"
        id="overviewFilter"
    >

        <div class="w-full max-w-xs">

            <label for="bank" class="form-label">
                <i class="fa-solid fa-building-columns ml-1 text-info"></i>
                البنك
            </label>

            <select
                id="bank"
                name="bank"
                class="form-input"
                onchange="this.form.submit()"
            >
                <option value="0">كل البنوك</option>

                @foreach($bankOptions as $id => $name)
                    <option
                        value="{{ $id }}"
                        @selected($selected === $id)
                    >
                        {{ $name }}
                    </option>
                @endforeach

            </select>

        </div>


        <div class="flex flex-wrap items-end gap-2">

            <div class="hidden min-w-[180px] sm:block">
                <div class="text-[11px] text-muted">
                    النطاق الحالي
                </div>

                <div class="mt-1 truncate text-sm font-semibold text-fg">
                    {{ $selectedBankName }}
                </div>
            </div>

            <a
                href="{{ route('overview.index') }}"
                class="btn btn-secondary"
                title="إعادة ضبط"
                aria-label="إعادة ضبط الفلتر"
            >
                <i class="fa-solid fa-rotate-right"></i>
            </a>

        </div>

    </form>


    {{-- ================================================================
         QUICK SUMMARY
         ================================================================ --}}
    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        {{-- Portfolio --}}
        <div class="card group transition hover:border-info/30">

            <div class="flex items-center justify-between gap-3">

                <div>

                    <p class="text-xs text-muted">
                        إجمالي المحفظة
                    </p>

                    <p class="mt-2 text-xl font-bold text-fg">
                        EGP {{ number_format($totalPortfolio) }}
                    </p>

                    <p class="mt-1 text-[11px] text-muted">
                        إجمالي المديونية الحالية
                    </p>

                </div>

                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-info/10 text-info">
                    <i class="fa-solid fa-wallet"></i>
                </span>

            </div>

        </div>


        {{-- Collected --}}
        <div class="card group transition hover:border-brand/30">

            <div class="flex items-center justify-between gap-3">

                <div>

                    <p class="text-xs text-muted">
                        إجمالي المحصل
                    </p>

                    <p class="mt-2 text-xl font-bold text-brand">
                        EGP {{ number_format($totalCollected) }}
                    </p>

                    <p class="mt-1 text-[11px] text-muted">
                        {{ number_format($collectionRate, 1) }}% من المحفظة
                    </p>

                </div>

                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand/10 text-brand">
                    <i class="fa-solid fa-money-bill-transfer"></i>
                </span>

            </div>

        </div>


        {{-- Remaining --}}
        <div class="card group transition hover:border-warning/30">

            <div class="flex items-center justify-between gap-3">

                <div>

                    <p class="text-xs text-muted">
                        المتبقي
                    </p>

                    <p class="mt-2 text-xl font-bold text-warning">
                        EGP {{ number_format($totalRemaining) }}
                    </p>

                    <p class="mt-1 text-[11px] text-muted">
                        {{ number_format($remainingRate, 1) }}% من المحفظة
                    </p>

                </div>

                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-warning/10 text-warning">
                    <i class="fa-solid fa-hourglass-half"></i>
                </span>

            </div>

        </div>


        {{-- Banks --}}
        <div class="card group transition hover:border-accent/30">

            <div class="flex items-center justify-between gap-3">

                <div>

                    <p class="text-xs text-muted">
                        البنوك المعروضة
                    </p>

                    <p class="mt-2 text-xl font-bold text-fg">
                        {{ number_format($bankCount) }}
                    </p>

                    <p class="mt-1 text-[11px] text-muted">
                        ضمن النطاق الحالي
                    </p>

                </div>

                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-accent/10 text-accent">
                    <i class="fa-solid fa-building-columns"></i>
                </span>

            </div>

        </div>

    </section>


    {{-- ================================================================
         COLLECTION PROGRESS
         ================================================================ --}}
    <section class="mb-6">

        <x-panel
            title="مؤشر التحصيل"
            icon="fa-chart-line"
        >

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

                {{-- Main progress --}}
                <div class="lg:col-span-2">

                    <div class="mb-2 flex items-center justify-between gap-3">

                        <div>
                            <p class="text-sm font-semibold text-fg">
                                نسبة التحصيل من إجمالي المحفظة
                            </p>

                            <p class="mt-1 text-[11px] text-muted">
                                EGP {{ number_format($totalCollected) }}
                                من
                                EGP {{ number_format($totalPortfolio) }}
                            </p>
                        </div>

                        <span class="text-lg font-bold text-brand">
                            {{ number_format($collectionRate, 1) }}%
                        </span>

                    </div>

                    <div class="h-3 overflow-hidden rounded-full bg-white/5">
                        <div
                            class="h-full rounded-full bg-brand transition-all duration-700"
                            style="width: {{ min(max($collectionRate, 0), 100) }}%"
                        ></div>
                    </div>

                    <div class="mt-2 flex items-center justify-between text-[11px] text-muted">
                        <span>المحصل</span>
                        <span>المتبقي EGP {{ number_format($totalRemaining) }}</span>
                    </div>

                </div>


                {{-- Current trend achievement --}}
                <div class="rounded-xl border border-border bg-black/10 p-4">

                    <div class="flex items-center justify-between">

                        <div>
                            <p class="text-xs text-muted">
                                آخر فترة
                            </p>

                            <p class="mt-1 text-sm font-bold text-fg">
                                مقابل المستهدف
                            </p>
                        </div>

                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-info/10 text-info">
                            <i class="fa-solid fa-bullseye"></i>
                        </span>

                    </div>

                    <div class="mt-4 flex items-end justify-between gap-3">

                        <div>
                            <p class="text-lg font-bold text-info">
                                {{ number_format($trendAchievement, 1) }}%
                            </p>

                            <p class="mt-1 text-[10px] text-muted">
                                EGP {{ number_format($lastTrendCollected) }}
                                /
                                EGP {{ number_format($lastTrendTarget) }}
                            </p>
                        </div>

                        <div class="h-12 w-12">

                            <svg
                                viewBox="0 0 36 36"
                                class="h-full w-full -rotate-90"
                            >

                                <path
                                    d="M18 2.0845
                                       a 15.9155 15.9155 0 0 1 0 31.831
                                       a 15.9155 15.9155 0 0 1 0 -31.831"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-opacity=".08"
                                    stroke-width="4"
                                />

                                <path
                                    d="M18 2.0845
                                       a 15.9155 15.9155 0 0 1 0 31.831
                                       a 15.9155 15.9155 0 0 1 0 -31.831"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="4"
                                    stroke-linecap="round"
                                    stroke-dasharray="{{ min(max($trendAchievement, 0), 100) }}, 100"
                                    class="text-info"
                                />

                            </svg>

                        </div>

                    </div>

                </div>

            </div>

        </x-panel>

    </section>


    {{-- ================================================================
         KPIs
         ================================================================ --}}
    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">

        @foreach($stats as $stat)

            <x-metric-card
                :label="$stat['label']"
                :value="$stat['value']"
                :unit="$stat['unit']"
                :icon="$stat['icon']"
                :color="$stat['color']"
            />

        @endforeach

    </section>


    {{-- ================================================================
         ROW 1: TREND + BUCKET
         ================================================================ --}}
    <section class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-3">

        <x-panel
            title="التحصيل مقابل المستهدف (ألف EGP)"
            icon="fa-chart-column"
            class="xl:col-span-2"
        >

            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">

                <div class="flex items-center gap-3 text-[11px] text-muted">

                    <span class="inline-flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full bg-brand"></span>
                        المحصل
                    </span>

                    <span class="inline-flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full bg-info"></span>
                        المستهدف
                    </span>

                </div>

                <span class="text-[11px] text-muted">
                    {{ count($trendLabels) }} فترة
                </span>

            </div>

            <div class="relative h-[270px]">
                <canvas id="trendChart"></canvas>
            </div>

        </x-panel>


        <x-panel
            title="توزيع الحالات حسب الشريحة"
            icon="fa-chart-pie"
        >

            <div class="relative flex h-[270px] justify-center">
                <canvas id="bucketChart"></canvas>
            </div>

        </x-panel>

    </section>


    {{-- ================================================================
         ROW 2: LOAN TYPES + GOVERNORATES
         ================================================================ --}}
    <section class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-3">

        <x-panel
            title="المحفظة حسب نوع القرض"
            icon="fa-layer-group"
        >

            <div class="relative flex h-[270px] justify-center">
                <canvas id="loanChart"></canvas>
            </div>

        </x-panel>


        <x-panel
            title="المحفظة حسب المحافظة (EGP)"
            icon="fa-location-dot"
            class="xl:col-span-2"
        >

            <div class="mb-2 flex items-center justify-between">

                <span class="text-[11px] text-muted">
                    توزيع المحفظة جغرافيًا
                </span>

                <span class="text-[11px] text-muted">
                    {{ count($govLabels) }} محافظة
                </span>

            </div>

            <div class="relative h-[270px]">
                <canvas id="govChart"></canvas>
            </div>

        </x-panel>

    </section>


    {{-- ================================================================
         ROW 3: BANKS + AGING
         ================================================================ --}}
    <section class="grid grid-cols-1 gap-4 xl:grid-cols-3">


        {{-- ============================================================
             BANK PERFORMANCE
             ============================================================ --}}
        <x-panel
            title="أداء البنوك"
            icon="fa-building-columns"
            class="xl:col-span-2"
        >

            {{-- Table toolbar --}}
            <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">

                <div class="flex items-center gap-2">

                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-info/10 text-info">
                        <i class="fa-solid fa-building-columns text-xs"></i>
                    </span>

                    <div>
                        <p class="text-xs font-semibold text-fg">
                            {{ number_format($bankCount) }} بنك
                        </p>

                        <p class="text-[10px] text-muted">
                            ضمن النطاق المحدد
                        </p>
                    </div>

                </div>


                <div class="text-[11px] text-muted">
                    المحفظة:
                    <span class="font-semibold text-fg">
                        EGP {{ number_format($totalPortfolio) }}
                    </span>
                </div>

            </div>


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

                        @forelse($banks as $bank)

                            @php

                                $bankRate = min(max((float) ($bank->rate ?? 0), 0), 100);

                                $barClass = $bankRate >= 40
                                    ? 'bg-brand'
                                    : ($bankRate >= 20
                                        ? 'bg-warning'
                                        : 'bg-danger');

                                $bankPortfolio = (float) ($bank->portfolio ?? 0);
                                $bankCollected = (float) ($bank->collected ?? 0);

                                $bankRemaining = max(
                                    $bankPortfolio - $bankCollected,
                                    0
                                );

                                $portfolioShare = $totalPortfolio > 0
                                    ? min(
                                        ($bankPortfolio / $totalPortfolio) * 100,
                                        100
                                    )
                                    : 0;

                            @endphp


                            <tr class="group">


                                {{-- Bank --}}
                                <td>

                                    <a
                                        href="{{ $bank->url }}"
                                        class="group inline-flex items-center gap-3"
                                    >

                                        <span class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-black">

                                            <i class="fa-solid fa-building-columns text-xs"></i>

                                            <span class="absolute -bottom-0.5 -left-0.5 h-2 w-2 rounded-full {{ $bankRate >= 40 ? 'bg-brand' : ($bankRate >= 20 ? 'bg-warning' : 'bg-danger') }}"></span>

                                        </span>

                                        <span class="min-w-0">

                                            <span class="block max-w-[180px] truncate font-semibold text-fg transition group-hover:text-brand">
                                                {{ $bank->name }}
                                            </span>

                                            <span class="mt-0.5 block text-[10px] text-muted">
                                                {{ $bank->cases ?: 0 }} حالة
                                            </span>

                                        </span>

                                    </a>

                                </td>


                                {{-- Cases --}}
                                <td>

                                    <span class="font-semibold text-fg">
                                        {{ $bank->cases ?: '-' }}
                                    </span>

                                </td>


                                {{-- Portfolio --}}
                                <td>

                                    <div>

                                        <span class="font-semibold text-fg">
                                            {{ $bank->portfolio ? 'EGP ' . number_format($bank->portfolio) : '-' }}
                                        </span>

                                        @if($bankPortfolio > 0)
                                            <div class="mt-1 text-[10px] text-muted">
                                                {{ number_format($portfolioShare, 1) }}% من المحفظة
                                            </div>
                                        @endif

                                    </div>

                                </td>


                                {{-- Collected --}}
                                <td>

                                    <div>

                                        <span class="font-semibold text-brand">
                                            {{ $bank->collected ? 'EGP ' . number_format($bank->collected) : '-' }}
                                        </span>

                                        @if($bankPortfolio > 0)
                                            <div class="mt-1 text-[10px] text-muted">
                                                المتبقي:
                                                EGP {{ number_format($bankRemaining) }}
                                            </div>
                                        @endif

                                    </div>

                                </td>


                                {{-- Rate --}}
                                <td>

                                    <div class="mb-1 flex items-center justify-between gap-2">

                                        <span class="text-[11px] text-muted">
                                            نسبة التحصيل
                                        </span>

                                        <span class="font-bold {{ $bankRate >= 40 ? 'text-brand' : ($bankRate >= 20 ? 'text-warning' : 'text-danger') }}">
                                            {{ number_format($bankRate, 1) }}%
                                        </span>

                                    </div>

                                    <div
                                        class="progress"
                                        title="{{ number_format($bankRate, 1) }}%"
                                    >
                                        <div
                                            class="h-full rounded-full {{ $barClass }} transition-all duration-500"
                                            style="width: {{ $bankRate }}%"
                                        ></div>
                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="5"
                                    class="px-4 py-12 text-center"
                                >

                                    <div class="flex flex-col items-center justify-center">

                                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/5 text-muted">
                                            <i class="fa-solid fa-building-circle-exclamation"></i>
                                        </span>

                                        <p class="mt-3 font-semibold text-fg">
                                            لا توجد بيانات بنوك
                                        </p>

                                        <p class="mt-1 text-xs text-muted">
                                            لا توجد بيانات متاحة ضمن الفلتر الحالي.
                                        </p>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>


                    {{-- Totals --}}
                    <tfoot>

                        <tr class="bg-white/[0.03] font-bold text-fg">

                            <td class="px-4 py-3">
                                الإجمالي
                            </td>

                            <td class="px-4 py-3">
                                {{ number_format($totals['cases'] ?? 0) }}
                            </td>

                            <td class="px-4 py-3">
                                EGP {{ number_format($totals['portfolio'] ?? 0) }}
                            </td>

                            <td class="px-4 py-3 text-brand">
                                EGP {{ number_format($totals['collected'] ?? 0) }}
                            </td>

                            <td class="px-4 py-3">

                                <div class="flex items-center gap-2">

                                    <span class="font-bold text-brand">
                                        {{ number_format($collectionRate, 1) }}%
                                    </span>

                                    <div class="progress flex-1">
                                        <div
                                            class="h-full rounded-full bg-brand"
                                            style="width: {{ min(max($collectionRate, 0), 100) }}%"
                                        ></div>
                                    </div>

                                </div>

                            </td>

                        </tr>

                    </tfoot>

                </table>

            </div>

        </x-panel>


        {{-- ============================================================
             AGING
             ============================================================ --}}
        <x-panel
            title="أعمار الديون (DPD)"
            icon="fa-hourglass-half"
        >

            <div class="mb-4 rounded-xl border border-border bg-black/10 p-3">

                <div class="flex items-center justify-between">

                    <div class="flex items-center gap-2">

                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-warning/10 text-warning">
                            <i class="fa-solid fa-clock-rotate-left text-xs"></i>
                        </span>

                        <div>
                            <p class="text-xs font-semibold text-fg">
                                توزيع التأخر
                            </p>

                            <p class="text-[10px] text-muted">
                                حسب مدة الاستحقاق
                            </p>
                        </div>

                    </div>

                    <span class="text-[10px] text-muted">
                        {{ is_countable($agingRows) ? count($agingRows) : 0 }} شرائح
                    </span>

                </div>

            </div>


            <ul class="space-y-4">

                @forelse($agingRows as $row)

                    @php
                        $agingPercent = min(max((float) ($row->percent ?? 0), 0), 100);
                        $agingAmount = (float) ($row->amount ?? 0);
                    @endphp

                    <li>

                        <div class="mb-1 flex items-center justify-between text-xs">

                            <span class="font-semibold text-fg">
                                {{ $row->label }}
                            </span>

                            <span class="font-semibold text-muted">
                                {{ number_format($agingPercent, 1) }}%
                            </span>

                        </div>


                        <div class="progress">

                            <div
                                class="h-full rounded-full {{ $row->bar }}"
                                style="width: {{ $agingPercent }}%"
                            ></div>

                        </div>


                        <div class="mt-1 flex justify-between text-[11px] text-dim">

                            <span>
                                {{ number_format($row->cases ?? 0) }} حالة
                            </span>

                            <span>
                                EGP {{ number_format($agingAmount) }}
                            </span>

                        </div>

                    </li>

                @empty

                    <li class="py-8 text-center">

                        <span class="mx-auto flex h-11 w-11 items-center justify-center rounded-xl bg-white/5 text-muted">
                            <i class="fa-solid fa-hourglass-empty"></i>
                        </span>

                        <p class="mt-3 text-sm font-semibold text-fg">
                            لا توجد بيانات أعمار ديون
                        </p>

                        <p class="mt-1 text-[11px] text-muted">
                            لا توجد بيانات متاحة للنطاق الحالي.
                        </p>

                    </li>

                @endforelse

            </ul>

        </x-panel>

    </section>


    {{-- ================================================================
         FOOTER INFORMATION
         ================================================================ --}}
    <div class="mt-6 flex flex-col gap-2 border-t border-border pt-4 sm:flex-row sm:items-center sm:justify-between">

        <div class="flex flex-wrap items-center gap-3 text-[11px] text-muted">

            <span class="inline-flex items-center gap-1.5">
                <i class="fa-solid fa-database text-[10px]"></i>
                نطاق البيانات:
                <strong class="text-fg">
                    {{ $selectedBankName }}
                </strong>
            </span>

            <span class="hidden text-border sm:inline">
                •
            </span>

            <span class="inline-flex items-center gap-1.5">
                <i class="fa-solid fa-building-columns text-[10px]"></i>
                {{ number_format($bankCount) }} بنك
            </span>

            <span class="hidden text-border sm:inline">
                •
            </span>

            <span class="inline-flex items-center gap-1.5">
                <i class="fa-solid fa-users text-[10px]"></i>
                {{ number_format($totalCases) }} حالة
            </span>

        </div>


        <button
            type="button"
            id="scrollToTop"
            class="text-[11px] text-muted transition hover:text-brand"
        >
            <i class="fa-solid fa-arrow-up ml-1"></i>
            العودة للأعلى
        </button>

    </div>

@endsection


@push('scripts')

    {{-- ================================================================
         CHART.JS
         ================================================================ --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>

        document.addEventListener('DOMContentLoaded', function () {

            /*
            |--------------------------------------------------------------------------
            | CSS TOKENS
            |--------------------------------------------------------------------------
            */

            const css = getComputedStyle(document.documentElement);

            const token = (name, fallback) => {
                return css.getPropertyValue(`--color-${name}`).trim() || fallback;
            };


            const brand = token(
                'brand',
                '#00ff66'
            );

            const info = token(
                'info',
                '#00aaff'
            );

            const warning = token(
                'warning',
                '#facc15'
            );

            const danger = token(
                'danger',
                '#f87171'
            );

            const accent = token(
                'accent',
                '#a855f7'
            );

            const muted = token(
                'muted',
                '#8c98a5'
            );

            const line = token(
                'line',
                'rgba(255,255,255,0.06)'
            );


            /*
            |--------------------------------------------------------------------------
            | Chart defaults
            |--------------------------------------------------------------------------
            */

            Chart.defaults.font.family = 'Cairo';
            Chart.defaults.color = muted;

            Chart.defaults.animation.duration = 700;

            Chart.defaults.plugins.tooltip.backgroundColor = '#111418';
            Chart.defaults.plugins.tooltip.borderColor = line;
            Chart.defaults.plugins.tooltip.borderWidth = 1;
            Chart.defaults.plugins.tooltip.titleColor = '#ffffff';
            Chart.defaults.plugins.tooltip.bodyColor = muted;
            Chart.defaults.plugins.tooltip.padding = 12;
            Chart.defaults.plugins.tooltip.displayColors = true;


            /*
            |--------------------------------------------------------------------------
            | Helpers
            |--------------------------------------------------------------------------
            */

            const formatNumber = (value) => {
                return new Intl.NumberFormat('en-US', {
                    maximumFractionDigits: 0
                }).format(value || 0);
            };


            const formatMoney = (value) => {
                return 'EGP ' + formatNumber(value);
            };


            const legendBottom = {

                legend: {
                    position: 'bottom',

                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 18,
                        boxWidth: 7,
                        boxHeight: 7,
                        font: {
                            family: 'Cairo',
                            size: 11
                        }
                    }
                }

            };


            /*
            |--------------------------------------------------------------------------
            | Trend Chart
            |--------------------------------------------------------------------------
            */

            const trendCanvas =
                document.getElementById('trendChart');

            if (trendCanvas) {

                new Chart(trendCanvas, {

                    type: 'bar',

                    data: {

                        labels: @json($trendLabels),

                        datasets: [

                            {
                                label: 'المحصل',

                                data: @json($trendCollected),

                                backgroundColor: brand,

                                borderRadius: 6,

                                maxBarThickness: 38,

                                hoverBackgroundColor: brand,

                                categoryPercentage: 0.72,

                                barPercentage: 0.82
                            },


                            {
                                type: 'line',

                                label: 'المستهدف',

                                data: @json($trendTarget),

                                borderColor: info,

                                backgroundColor: info,

                                borderWidth: 2,

                                borderDash: [6, 6],

                                pointRadius: 3,

                                pointHoverRadius: 5,

                                pointBackgroundColor: info,

                                pointBorderWidth: 0,

                                tension: 0.35
                            }

                        ]

                    },


                    options: {

                        responsive: true,

                        maintainAspectRatio: false,

                        interaction: {
                            mode: 'index',
                            intersect: false
                        },

                        scales: {

                            x: {

                                grid: {
                                    color: line,
                                    display: true
                                },

                                ticks: {
                                    font: {
                                        family: 'Cairo',
                                        size: 10
                                    }
                                }

                            },


                            y: {

                                grid: {
                                    color: line
                                },

                                beginAtZero: true,

                                ticks: {

                                    font: {
                                        family: 'Cairo',
                                        size: 10
                                    },

                                    callback: function (value) {
                                        return formatNumber(value);
                                    }

                                }

                            }

                        },


                        plugins: {

                            ...legendBottom,

                            tooltip: {

                                callbacks: {

                                    label: function (context) {

                                        return (
                                            context.dataset.label +
                                            ': ' +
                                            formatMoney(context.raw)
                                        );

                                    }

                                }

                            }

                        }

                    }

                });

            }


            /*
            |--------------------------------------------------------------------------
            | Bucket Doughnut
            |--------------------------------------------------------------------------
            */

            const bucketCanvas =
                document.getElementById('bucketChart');

            if (bucketCanvas) {

                new Chart(bucketCanvas, {

                    type: 'doughnut',

                    data: {

                        labels: @json($bucketLabels),

                        datasets: [

                            {

                                data: @json($bucketData),

                                backgroundColor: [
                                    brand,
                                    info,
                                    warning,
                                    danger
                                ],

                                borderWidth: 0,

                                hoverOffset: 6
                            }

                        ]

                    },


                    options: {

                        responsive: true,

                        maintainAspectRatio: false,

                        cutout: '65%',

                        plugins: {

                            ...legendBottom,

                            tooltip: {

                                callbacks: {

                                    label: function (context) {

                                        return (
                                            context.label +
                                            ': ' +
                                            formatNumber(context.raw)
                                        );

                                    }

                                }

                            }

                        }

                    }

                });

            }


            /*
            |--------------------------------------------------------------------------
            | Loan Type Doughnut
            |--------------------------------------------------------------------------
            */

            const loanCanvas =
                document.getElementById('loanChart');

            if (loanCanvas) {

                new Chart(loanCanvas, {

                    type: 'doughnut',

                    data: {

                        labels: @json($loanLabels),

                        datasets: [

                            {

                                data: @json($loanData),

                                backgroundColor: [
                                    info,
                                    accent,
                                    warning
                                ],

                                borderWidth: 0,

                                hoverOffset: 6
                            }

                        ]

                    },


                    options: {

                        responsive: true,

                        maintainAspectRatio: false,

                        cutout: '65%',

                        plugins: {

                            ...legendBottom,

                            tooltip: {

                                callbacks: {

                                    label: function (context) {

                                        return (
                                            context.label +
                                            ': ' +
                                            formatMoney(context.raw)
                                        );

                                    }

                                }

                            }

                        }

                    }

                });

            }


            /*
            |--------------------------------------------------------------------------
            | Governorates
            |--------------------------------------------------------------------------
            */

            const govCanvas =
                document.getElementById('govChart');

            if (govCanvas) {

                new Chart(govCanvas, {

                    type: 'bar',

                    data: {

                        labels: @json($govLabels),

                        datasets: [

                            {

                                label: 'المحفظة',

                                data: @json($govData),

                                backgroundColor: info,

                                borderRadius: 6,

                                maxBarThickness: 22,

                                hoverBackgroundColor: info
                            }

                        ]

                    },


                    options: {

                        indexAxis: 'y',

                        responsive: true,

                        maintainAspectRatio: false,

                        interaction: {
                            mode: 'index',
                            intersect: false
                        },

                        scales: {

                            x: {

                                grid: {
                                    color: line
                                },

                                beginAtZero: true,

                                ticks: {

                                    font: {
                                        family: 'Cairo',
                                        size: 10
                                    },

                                    callback: function (value) {
                                        return formatNumber(value);
                                    }

                                }

                            },


                            y: {

                                grid: {
                                    display: false
                                },

                                ticks: {

                                    font: {
                                        family: 'Cairo',
                                        size: 10
                                    }

                                }

                            }

                        },


                        plugins: {

                            legend: {
                                display: false
                            },

                            tooltip: {

                                callbacks: {

                                    label: function (context) {

                                        return (
                                            'المحفظة: ' +
                                            formatMoney(context.raw)
                                        );

                                    }

                                }

                            }

                        }

                    }

                });

            }


            /*
            |--------------------------------------------------------------------------
            | Refresh
            |--------------------------------------------------------------------------
            */

            const refreshButton =
                document.getElementById('refreshOverview');

            if (refreshButton) {

                refreshButton.addEventListener(
                    'click',
                    function () {

                        const icon =
                            refreshButton.querySelector('i');

                        if (icon) {
                            icon.classList.add('fa-spin');
                        }

                        setTimeout(function () {
                            window.location.reload();
                        }, 250);

                    }
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Last updated indicator
            |--------------------------------------------------------------------------
            */

            const lastUpdated =
                document.getElementById('overviewLastUpdated');

            if (lastUpdated) {

                const now = new Date();

                lastUpdated.innerHTML =
                    '<i class="fa-regular fa-clock ml-1"></i>' +
                    ' آخر عرض: ' +
                    now.toLocaleTimeString(
                        'ar-EG',
                        {
                            hour: '2-digit',
                            minute: '2-digit'
                        }
                    );

            }


            /*
            |--------------------------------------------------------------------------
            | Scroll to top
            |--------------------------------------------------------------------------
            */

            const scrollToTop =
                document.getElementById('scrollToTop');

            if (scrollToTop) {

                scrollToTop.addEventListener(
                    'click',
                    function () {

                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });

                    }
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Keyboard shortcuts
            |--------------------------------------------------------------------------
            */

            document.addEventListener(
                'keydown',
                function (event) {

                    /*
                    | Ctrl + K
                    | Focus bank filter
                    */

                    if (
                        (event.ctrlKey || event.metaKey) &&
                        event.key.toLowerCase() === 'k'
                    ) {

                        event.preventDefault();

                        const bank =
                            document.getElementById('bank');

                        if (bank) {
                            bank.focus();
                        }

                    }


                    /*
                    | R
                    | Refresh when not typing
                    */

                    if (
                        event.key.toLowerCase() === 'r' &&
                        !event.ctrlKey &&
                        !event.metaKey &&
                        !event.altKey
                    ) {

                        const active =
                            document.activeElement;

                        const isTyping =
                            active &&
                            (
                                active.tagName === 'INPUT' ||
                                active.tagName === 'TEXTAREA' ||
                                active.tagName === 'SELECT'
                            );

                        if (!isTyping) {

                            event.preventDefault();

                            window.location.reload();

                        }

                    }

                }
            );

        });

    </script>

@endpush