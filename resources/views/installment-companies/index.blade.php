{{-- resources/views/installment-companies/index.blade.php --}}
@extends('layouts.app')

@section('title', 'شركات التقسيط')

@php

    /*
    |--------------------------------------------------------------------------
    | SAFE COLLECTION
    |--------------------------------------------------------------------------
    */
    $companyCollection = collect($companies);

    /*
    |--------------------------------------------------------------------------
    | BASIC COUNTERS
    |--------------------------------------------------------------------------
    */
    $visibleCompanies = $companyCollection->count();

    $totalCompanies = method_exists($companies, 'total')
        ? $companies->total()
        : $visibleCompanies;

    $activeCompanies = $companyCollection
        ->filter(fn ($company) => (bool) $company->is_active)
        ->count();

    $inactiveCompanies = $companyCollection
        ->filter(fn ($company) => ! (bool) $company->is_active)
        ->count();

    /*
    |--------------------------------------------------------------------------
    | CASES
    |--------------------------------------------------------------------------
    */
    $totalCases = $companyCollection->sum(function ($company) {
        return (int) ($company->cases_count ?? 0);
    });

    /*
    |--------------------------------------------------------------------------
    | DEBT
    |--------------------------------------------------------------------------
    */
    $totalDebt = $companyCollection->sum(function ($company) {
        return (float) ($company->total_debt ?? 0);
    });

    /*
    |--------------------------------------------------------------------------
    | ACTIVE PERCENTAGE
    |--------------------------------------------------------------------------
    */
    $activePercent = $visibleCompanies > 0
        ? (int) round(($activeCompanies / $visibleCompanies) * 100)
        : 0;

    $activePercent = min(100, max(0, $activePercent));

    /*
    |--------------------------------------------------------------------------
    | AVERAGES
    |--------------------------------------------------------------------------
    */
    $averageDebt = $visibleCompanies > 0
        ? $totalDebt / $visibleCompanies
        : 0;

    $averageCases = $visibleCompanies > 0
        ? $totalCases / $visibleCompanies
        : 0;

    /*
    |--------------------------------------------------------------------------
    | MAX VALUES FOR VISUALIZATION
    |--------------------------------------------------------------------------
    */
    $maxCompanyDebt = $companyCollection->max(function ($company) {
        return (float) ($company->total_debt ?? 0);
    });

    $maxCompanyCases = $companyCollection->max(function ($company) {
        return (int) ($company->cases_count ?? 0);
    });

    /*
    |--------------------------------------------------------------------------
    | FILTERS
    |--------------------------------------------------------------------------
    */
    $currentSearch = request('search', '');
    $currentStatus = request('status', '');

    $hasFilters =
        $currentSearch !== '' ||
        $currentStatus !== '';

    /*
    |--------------------------------------------------------------------------
    | PAGINATION
    |--------------------------------------------------------------------------
    */
    $hasPagination = method_exists($companies, 'links');

    $firstItem = method_exists($companies, 'firstItem')
        ? $companies->firstItem()
        : ($visibleCompanies > 0 ? 1 : 0);

    $lastItem = method_exists($companies, 'lastItem')
        ? $companies->lastItem()
        : $visibleCompanies;

    /*
    |--------------------------------------------------------------------------
    | STATUS LABEL
    |--------------------------------------------------------------------------
    */
    $statusLabel = match ($currentStatus) {
        'active' => 'الشركات النشطة',
        'inactive' => 'الشركات المتوقفة',
        default => 'كل الشركات',
    };

@endphp

@section('content')

    {{-- ================================================================
        PAGE HEADER
    ================================================================= --}}
    <x-page-header
        title="شركات التقسيط"
        subtitle="إدارة شركات التقسيط المسجلة"
        icon="fa-building"
    >

        <x-slot:actions>

            <a
                href="{{ route('installment-companies.create') }}"
                class="btn btn-primary"
                title="إضافة شركة تقسيط جديدة"
                aria-label="إضافة شركة تقسيط جديدة"
            >

                <i class="fa-solid fa-plus text-xs"></i>

                إضافة شركة

            </a>

        </x-slot:actions>

    </x-page-header>


    <x-flash />


    {{-- ================================================================
        KPI CARDS
    ================================================================= --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-6">

        {{-- Total companies --}}
        <div class="card relative overflow-hidden">

            <div class="flex items-center justify-between gap-3">

                <div>

                    <div class="text-xs text-muted mb-1">
                        إجمالي الشركات
                    </div>

                    <div class="text-2xl font-bold text-fg">
                        {{ number_format($totalCompanies) }}
                    </div>

                    <div class="mt-1 text-[11px] text-dim">
                        شركات التقسيط المسجلة
                    </div>

                </div>

                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-info/15 text-info">

                    <i class="fa-solid fa-building"></i>

                </span>

            </div>

        </div>


        {{-- Active --}}
        <div class="card relative overflow-hidden">

            <div class="flex items-center justify-between gap-3">

                <div>

                    <div class="text-xs text-muted mb-1">
                        الشركات النشطة
                    </div>

                    <div class="text-2xl font-bold text-brand">
                        {{ number_format($activeCompanies) }}
                    </div>

                    <div class="mt-1 text-[11px] text-dim">
                        متاحة للعمل داخل النظام
                    </div>

                </div>

                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand/15 text-brand">

                    <i class="fa-solid fa-circle-check"></i>

                </span>

            </div>

        </div>


        {{-- Cases --}}
        <div class="card relative overflow-hidden">

            <div class="flex items-center justify-between gap-3">

                <div>

                    <div class="text-xs text-muted mb-1">
                        إجمالي الحالات
                    </div>

                    <div class="text-2xl font-bold text-info">
                        {{ number_format($totalCases) }}
                    </div>

                    <div class="mt-1 text-[11px] text-dim">
                        الحالات المرتبطة بالشركات المعروضة
                    </div>

                </div>

                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-info/15 text-info">

                    <i class="fa-solid fa-folder-open"></i>

                </span>

            </div>

        </div>


        {{-- Debt --}}
        <div class="card relative overflow-hidden">

            <div class="flex items-center justify-between gap-3">

                <div>

                    <div class="text-xs text-muted mb-1">
                        إجمالي المديونية
                    </div>

                    <div class="text-2xl font-bold text-accent">
                        {{ number_format($totalDebt, 0) }}
                    </div>

                    <div class="mt-1 text-[11px] text-dim">
                        EGP للشركات المعروضة
                    </div>

                </div>

                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-accent/15 text-accent">

                    <i class="fa-solid fa-money-bill-wave"></i>

                </span>

            </div>

        </div>

    </div>


    {{-- ================================================================
        SUMMARY CARDS
    ================================================================= --}}
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3 mb-6">

        {{-- Status --}}
        <div class="card">

            <div class="flex items-center justify-between mb-4">

                <div>

                    <div class="text-sm font-bold text-fg">
                        حالة الشركات
                    </div>

                    <div class="text-[11px] text-dim mt-1">
                        توزيع الشركات حسب الحالة
                    </div>

                </div>

                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand/15 text-brand">

                    <i class="fa-solid fa-chart-pie"></i>

                </span>

            </div>


            <div class="space-y-4">

                {{-- Active --}}
                <div>

                    <div class="mb-2 flex items-center justify-between">

                        <div class="flex items-center gap-2">

                            <span class="h-2 w-2 rounded-full bg-brand"></span>

                            <span class="text-xs text-muted">
                                نشطة
                            </span>

                        </div>

                        <span class="badge badge-success">
                            {{ number_format($activeCompanies) }}
                        </span>

                    </div>

                    <div class="progress">

                        <div
                            class="h-full rounded-full bg-brand"
                            style="width: {{ $activePercent }}%"
                        ></div>

                    </div>

                </div>


                {{-- Inactive --}}
                <div>

                    <div class="mb-2 flex items-center justify-between">

                        <div class="flex items-center gap-2">

                            <span class="h-2 w-2 rounded-full bg-danger"></span>

                            <span class="text-xs text-muted">
                                متوقفة
                            </span>

                        </div>

                        <span class="badge badge-danger">
                            {{ number_format($inactiveCompanies) }}
                        </span>

                    </div>

                    <div class="progress">

                        <div
                            class="h-full rounded-full bg-danger"
                            style="width: {{ 100 - $activePercent }}%"
                        ></div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Debt summary --}}
        <div class="card">

            <div class="flex items-center justify-between mb-4">

                <div>

                    <div class="text-sm font-bold text-fg">
                        ملخص المديونية
                    </div>

                    <div class="text-[11px] text-dim mt-1">
                        إجمالي ومتوسط المديونية
                    </div>

                </div>

                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-accent/15 text-accent">

                    <i class="fa-solid fa-chart-column"></i>

                </span>

            </div>


            <div class="space-y-3">

                <div class="flex items-center justify-between">

                    <span class="text-xs text-dim">
                        الإجمالي
                    </span>

                    <span class="font-semibold text-accent">
                        EGP {{ number_format($totalDebt, 0) }}
                    </span>

                </div>


                <div class="flex items-center justify-between">

                    <span class="text-xs text-dim">
                        المتوسط / شركة
                    </span>

                    <span class="font-semibold text-fg">
                        EGP {{ number_format($averageDebt, 0) }}
                    </span>

                </div>


                <div class="flex items-center justify-between">

                    <span class="text-xs text-dim">
                        الحالات
                    </span>

                    <span class="font-semibold text-info">
                        {{ number_format($totalCases) }}
                    </span>

                </div>

            </div>

        </div>


        {{-- Cases summary --}}
        <div class="card">

            <div class="flex items-center justify-between mb-4">

                <div>

                    <div class="text-sm font-bold text-fg">
                        توزيع الحالات
                    </div>

                    <div class="text-[11px] text-dim mt-1">
                        متوسط الحالات لكل شركة
                    </div>

                </div>

                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-info/15 text-info">

                    <i class="fa-solid fa-folder-tree"></i>

                </span>

            </div>


            <div class="flex items-center justify-between">

                <div>

                    <div class="text-[11px] text-dim">
                        إجمالي الحالات
                    </div>

                    <div class="mt-1 text-xl font-bold text-info">
                        {{ number_format($totalCases) }}
                    </div>

                </div>


                <div class="text-left">

                    <div class="text-[11px] text-dim">
                        المتوسط
                    </div>

                    <div class="mt-1 text-xl font-bold text-fg">
                        {{ number_format($averageCases, 1) }}
                    </div>

                    <div class="text-[10px] text-dim">
                        حالة / شركة
                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ================================================================
        FILTER BAR
    ================================================================= --}}
    <form
        method="GET"
        action="{{ route('installment-companies.index') }}"
        class="card filter-bar mb-6"
        id="companiesFilterForm"
    >

        {{-- Search --}}
        <div class="min-w-[260px] flex-1">

            <label
                for="companySearch"
                class="sr-only"
            >
                البحث في شركات التقسيط
            </label>

            <div class="relative">

                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-dim"></i>

                <input
                    id="companySearch"
                    name="search"
                    type="search"
                    value="{{ $currentSearch }}"
                    placeholder="ابحث باسم الشركة أو الكود..."
                    class="form-input pl-9"
                    autocomplete="off"
                >

            </div>

        </div>


        {{-- Status --}}
        <div class="w-48">

            <label
                for="companyStatus"
                class="form-label"
            >

                <i class="fa-solid fa-circle-dot ml-1 text-info"></i>

                الحالة

            </label>

            <select
                id="companyStatus"
                name="status"
                class="form-input"
                onchange="this.form.submit()"
            >

                <option value="">
                    كل الحالات
                </option>

                <option
                    value="active"
                    @selected($currentStatus === 'active')
                >
                    نشطة
                </option>

                <option
                    value="inactive"
                    @selected($currentStatus === 'inactive')
                >
                    متوقفة
                </option>

            </select>

        </div>


        {{-- Apply --}}
        <button
            type="submit"
            class="btn btn-primary"
            title="تطبيق البحث والفلاتر"
            aria-label="تطبيق البحث والفلاتر"
        >

            <i class="fa-solid fa-magnifying-glass"></i>

        </button>


        {{-- Reset --}}
        <a
            href="{{ route('installment-companies.index') }}"
            class="btn btn-secondary"
            title="إعادة ضبط الفلاتر"
            aria-label="إعادة ضبط الفلاتر"
        >

            <i class="fa-solid fa-rotate-right"></i>

        </a>

    </form>


    {{-- ================================================================
        ACTIVE FILTERS
    ================================================================= --}}
    @if($hasFilters)

        <div class="mb-4 flex flex-wrap items-center gap-2">

            <span class="text-[11px] text-dim">

                <i class="fa-solid fa-filter ml-1"></i>

                الفلاتر الحالية:

            </span>


            @if($currentSearch !== '')

                <span class="tag">

                    <i class="fa-solid fa-magnifying-glass text-[10px] text-info"></i>

                    {{ $currentSearch }}

                </span>

            @endif


            @if($currentStatus !== '')

                <span class="tag">

                    <i class="fa-solid fa-circle-dot text-[10px] text-brand"></i>

                    {{ $statusLabel }}

                </span>

            @endif

        </div>

    @endif


    {{-- ================================================================
        TABLE INFORMATION BAR
    ================================================================= --}}
    <div class="mb-3 flex flex-wrap items-center justify-between gap-3">

        <div class="flex items-center gap-3">

            <div class="flex items-center gap-2 text-sm font-bold text-fg">

                <i class="fa-solid fa-building text-info"></i>

                قائمة شركات التقسيط

            </div>

            <span class="badge badge-outline-info">

                {{ number_format($visibleCompanies) }}

                معروض

            </span>

        </div>


        <div class="flex flex-wrap items-center gap-3 text-[11px] text-dim">

            <span>

                <i class="fa-solid fa-folder-open text-info ml-1"></i>

                {{ number_format($totalCases) }}

                حالة

            </span>

            <span>

                <i class="fa-solid fa-money-bill-wave text-accent ml-1"></i>

                EGP {{ number_format($totalDebt, 0) }}

            </span>

        </div>

    </div>


    {{-- ================================================================
        TABLE
    ================================================================= --}}
    <div class="table-wrap overflow-x-auto">

        <table class="data-table min-w-[1150px]">

            <thead>

                <tr>

                    {{-- Select --}}
                    <th class="w-10 text-center">

                        <input
                            type="checkbox"
                            id="selectAllCompanies"
                            class="h-4 w-4 rounded border-white/10 bg-white/5 text-brand focus:ring-brand"
                            title="تحديد جميع الشركات"
                            aria-label="تحديد جميع الشركات"
                        >

                    </th>

                    <th>
                        #
                    </th>

                    <th>
                        الشركة
                    </th>

                    <th>
                        الكود
                    </th>

                    <th>
                        الحالات
                    </th>

                    <th>
                        إجمالي المديونية
                    </th>

                    <th>
                        الحالة
                    </th>

                    <th>
                        الوصول
                    </th>

                    <th>
                        إجراءات
                    </th>

                </tr>

            </thead>


            <tbody>

                @forelse($companies as $company)

                    @php

                        $companyCases =
                            (int) ($company->cases_count ?? 0);

                        $companyDebt =
                            (float) ($company->total_debt ?? 0);

                        $debtPercent =
                            $maxCompanyDebt > 0
                                ? (int) round(($companyDebt / $maxCompanyDebt) * 100)
                                : 0;

                        $casesPercent =
                            $maxCompanyCases > 0
                                ? (int) round(($companyCases / $maxCompanyCases) * 100)
                                : 0;

                        $debtShare =
                            $totalDebt > 0
                                ? ($companyDebt / $totalDebt) * 100
                                : 0;

                    @endphp


                    <tr
                        class="group transition-colors duration-150 hover:bg-white/[0.025]"
                        data-company-row="{{ $company->id }}"
                    >

                        {{-- =================================================
                            SELECT
                        ================================================== --}}
                        <td class="text-center">

                            <input
                                type="checkbox"
                                class="company-row-checkbox h-4 w-4 rounded border-white/10 bg-white/5 text-brand focus:ring-brand"
                                value="{{ $company->id }}"
                                title="تحديد {{ $company->name }}"
                                aria-label="تحديد {{ $company->name }}"
                            >

                        </td>


                        {{-- =================================================
                            ID
                        ================================================== --}}
                        <td>

                            <span
                                class="inline-flex items-center rounded-md bg-white/[0.03] px-2 py-1 font-mono text-[11px] text-dim"
                                title="معرف الشركة"
                            >

                                #{{ $company->id }}

                            </span>

                        </td>


                        {{-- =================================================
                            COMPANY
                        ================================================== --}}
                        <td>

                            <a
                                href="{{ route('installment-companies.panel', $company->id) }}"
                                class="group/company inline-flex min-w-[220px] items-center gap-3"
                                title="فتح لوحة تحكم {{ $company->name }}"
                            >

                                {{-- Icon --}}
                                <span class="relative flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-white/5 bg-info/10 text-info transition group-hover/company:border-info/20">

                                    <i class="fa-solid fa-building text-sm"></i>

                                    {{-- Status indicator --}}
                                    <span
                                        class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-[#14191e] {{ $company->is_active ? 'bg-brand' : 'bg-danger' }}"
                                        title="{{ $company->is_active ? 'نشطة' : 'متوقفة' }}"
                                    ></span>

                                </span>


                                {{-- Company information --}}
                                <span class="min-w-0">

                                    <span class="block truncate font-semibold text-fg transition group-hover/company:text-brand">

                                        {{ $company->name }}

                                    </span>

                                    <span class="mt-1 flex items-center gap-2 text-[10px] text-dim">

                                        <span>

                                            <i class="fa-solid fa-building ml-1"></i>

                                            شركة تقسيط

                                        </span>

                                        <span class="text-white/10">
                                            •
                                        </span>

                                        <span>
                                            ID #{{ $company->id }}
                                        </span>

                                    </span>

                                </span>

                            </a>

                        </td>


                        {{-- =================================================
                            CODE
                        ================================================== --}}
                        <td>

                            <div class="flex items-center gap-2">

                                <span
                                    class="rounded-md border border-white/5 bg-white/[0.02] px-2 py-1 font-mono text-xs text-muted"
                                    dir="ltr"
                                    title="كود الشركة"
                                >

                                    {{ $company->code ?: '-' }}

                                </span>


                                @if($company->code)

                                    <button
                                        type="button"
                                        class="flex h-7 w-7 items-center justify-center rounded-md text-dim transition hover:bg-info/10 hover:text-info"
                                        title="نسخ كود الشركة"
                                        aria-label="نسخ كود الشركة"
                                        onclick="copyCompanyCode(@js($company->code), this)"
                                    >

                                        <i class="fa-regular fa-copy text-[10px]"></i>

                                    </button>

                                @endif

                            </div>

                        </td>


                        {{-- =================================================
                            CASES
                        ================================================== --}}
                        <td>

                            <div class="min-w-[125px]">

                                <div class="flex items-center justify-between gap-3">

                                    <span class="font-semibold text-fg">

                                        {{ number_format($companyCases) }}

                                    </span>

                                    <span class="text-[10px] text-dim">
                                        حالة
                                    </span>

                                </div>


                                <div class="mt-2 progress">

                                    <div
                                        class="h-full rounded-full bg-info"
                                        style="width: {{ $casesPercent }}%"
                                    ></div>

                                </div>

                            </div>

                        </td>


                        {{-- =================================================
                            DEBT
                        ================================================== --}}
                        <td>

                            <div class="min-w-[180px]">

                                <div class="flex items-center justify-between gap-3">

                                    <span class="font-semibold text-accent">
                                        EGP {{ number_format($companyDebt, 0) }}
                                    </span>

                                </div>


                                <div class="mt-1 flex items-center justify-between">

                                    <span class="text-[10px] text-dim">
                                        {{ number_format($debtShare, 1) }}% من الإجمالي
                                    </span>

                                    <span class="text-[10px] text-dim">
                                        {{ number_format($companyDebt, 0) }}
                                    </span>

                                </div>


                                <div class="mt-2 progress">

                                    <div
                                        class="h-full rounded-full bg-accent"
                                        style="width: {{ $debtPercent }}%"
                                    ></div>

                                </div>

                            </div>

                        </td>


                        {{-- =================================================
                            STATUS
                        ================================================== --}}
                        <td>

                            <span
                                class="badge {{ $company->is_active ? 'badge-success' : 'badge-danger' }}"
                                title="{{ $company->is_active ? 'الشركة متاحة للعمل' : 'الشركة متوقفة' }}"
                            >

                                @if($company->is_active)

                                    <i class="fa-solid fa-circle-check text-[9px]"></i>

                                    نشطة

                                @else

                                    <i class="fa-solid fa-circle-pause text-[9px]"></i>

                                    متوقفة

                                @endif

                            </span>

                        </td>


                        {{-- =================================================
                            ACCESS
                        ================================================== --}}
                        <td>

                            <a
                                href="{{ route('installment-companies.panel', $company->id) }}"
                                class="inline-flex items-center gap-2 rounded-lg border border-info/10 bg-info/5 px-3 py-2 text-[11px] text-info transition hover:border-info/30 hover:bg-info/10"
                                title="فتح لوحة تحكم الشركة"
                            >

                                <i class="fa-solid fa-table-cells-large"></i>

                                <span>
                                    فتح اللوحة
                                </span>

                            </a>

                        </td>


                        {{-- =================================================
                            ACTIONS
                        ================================================== --}}
                        <td>

                            <div class="flex items-center gap-2">

                                {{-- Panel --}}
                                <a
                                    href="{{ route('installment-companies.panel', $company->id) }}"
                                    class="btn btn-outline-info btn-sm"
                                    title="فتح لوحة تحكم الشركة"
                                    aria-label="فتح لوحة تحكم الشركة"
                                >

                                    <i class="fa-solid fa-table-cells-large text-xs"></i>

                                    لوحة التحكم

                                </a>


                                {{-- Edit --}}
                                <a
                                    href="{{ route('installment-companies.edit', $company->id) }}"
                                    class="btn btn-secondary btn-sm"
                                    title="تعديل بيانات الشركة"
                                    aria-label="تعديل بيانات الشركة"
                                >

                                    <i class="fa-solid fa-pen text-[10px]"></i>

                                    تعديل

                                </a>


                                {{-- Delete --}}
                                <form
                                    method="POST"
                                    action="{{ route('installment-companies.destroy', $company->id) }}"
                                    onsubmit="return confirmCompanyDelete(@js($company->name))"
                                >

                                    @csrf

                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="btn btn-danger btn-sm"
                                        title="حذف الشركة"
                                        aria-label="حذف الشركة"
                                    >

                                        <i class="fa-solid fa-trash text-[10px]"></i>

                                        حذف

                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>

                @empty

                    {{-- =================================================
                        EMPTY STATE
                    ================================================== --}}
                    <tr>

                        <td
                            colspan="9"
                            class="py-10 text-center text-dim"
                        >

                            <div class="py-4">

                                <x-empty-state
                                    icon="fa-building"
                                    text="{{ $hasFilters ? 'لا توجد شركات مطابقة للفلاتر الحالية' : 'لا توجد شركات تقسيط' }}"
                                />


                                @if($hasFilters)

                                    <div class="mt-4">

                                        <a
                                            href="{{ route('installment-companies.index') }}"
                                            class="btn btn-secondary btn-sm"
                                        >

                                            <i class="fa-solid fa-rotate-right"></i>

                                            عرض جميع الشركات

                                        </a>

                                    </div>

                                @endif

                            </div>

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>


    {{-- ================================================================
        SELECTION BAR
    ================================================================= --}}
    <div
        id="companySelectionBar"
        class="mt-3 hidden rounded-xl border border-brand/10 bg-brand/[0.03] px-4 py-3"
    >

        <div class="flex flex-wrap items-center justify-between gap-3">

            <div class="flex items-center gap-2 text-xs text-muted">

                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand/10 text-brand">

                    <i class="fa-solid fa-check"></i>

                </span>

                <span>

                    تم تحديد

                    <strong
                        id="selectedCompanyCount"
                        class="text-brand"
                    >
                        0
                    </strong>

                    شركة

                </span>

            </div>


            <button
                type="button"
                class="btn btn-secondary btn-sm"
                onclick="clearCompanySelection()"
                title="إلغاء تحديد الشركات"
            >

                <i class="fa-solid fa-xmark"></i>

                إلغاء التحديد

            </button>

        </div>

    </div>


    {{-- ================================================================
        PAGINATION
    ================================================================= --}}
    @if($hasPagination)

        <div class="mt-5">

            {{ $companies->withQueryString()->links() }}

        </div>

    @endif


    {{-- ================================================================
        FOOTER SUMMARY
    ================================================================= --}}
    @if($visibleCompanies > 0)

        <div class="mt-5 card">

            <div class="flex flex-wrap items-center justify-between gap-4">

                <div class="flex flex-wrap items-center gap-4 text-[11px] text-dim">

                    {{-- Results --}}
                    <span>

                        <i class="fa-solid fa-list text-info ml-1"></i>

                        @if($hasPagination && $firstItem)

                            عرض
                            {{ number_format($firstItem) }}
                            -
                            {{ number_format($lastItem) }}
                            من
                            {{ number_format($totalCompanies) }}

                        @else

                            {{ number_format($visibleCompanies) }}

                            شركة معروضة

                        @endif

                    </span>


                    {{-- Active --}}
                    <span>

                        <i class="fa-solid fa-circle-check text-brand ml-1"></i>

                        {{ number_format($activeCompanies) }}

                        نشطة

                    </span>


                    {{-- Inactive --}}
                    <span>

                        <i class="fa-solid fa-circle-pause text-danger ml-1"></i>

                        {{ number_format($inactiveCompanies) }}

                        متوقفة

                    </span>


                    {{-- Cases --}}
                    <span>

                        <i class="fa-solid fa-folder-open text-info ml-1"></i>

                        {{ number_format($totalCases) }}

                        حالة

                    </span>


                    {{-- Debt --}}
                    <span>

                        <i class="fa-solid fa-money-bill-wave text-accent ml-1"></i>

                        EGP {{ number_format($totalDebt, 0) }}

                    </span>

                </div>


                <div class="text-[11px] text-dim">

                    @if($hasFilters)

                        <span>

                            <i class="fa-solid fa-filter ml-1"></i>

                            النتائج مفلترة حسب البحث الحالي

                        </span>

                    @else

                        <span>

                            <i class="fa-solid fa-database ml-1"></i>

                            جميع شركات التقسيط المسجلة

                        </span>

                    @endif

                </div>

            </div>

        </div>

    @endif


    {{-- ================================================================
        JAVASCRIPT
    ================================================================= --}}
    <script>

        /*
        |--------------------------------------------------------------------------
        | COPY COMPANY CODE
        |--------------------------------------------------------------------------
        */
        function copyCompanyCode(value, button) {

            if (!navigator.clipboard) {
                return;
            }

            navigator.clipboard.writeText(value).then(function () {

                const icon =
                    button.querySelector('i');

                if (!icon) {
                    return;
                }

                const originalClass =
                    icon.className;

                icon.className =
                    'fa-solid fa-check text-brand';

                setTimeout(function () {

                    icon.className =
                        originalClass;

                }, 1200);

            });

        }


        /*
        |--------------------------------------------------------------------------
        | DELETE CONFIRMATION
        |--------------------------------------------------------------------------
        */
        function confirmCompanyDelete(companyName) {

            return confirm(
                'هل أنت متأكد من حذف شركة "' +
                companyName +
                '"؟\n\n' +
                'سيتم تنفيذ الحذف نهائيًا إذا لم تكن هناك بيانات مرتبطة بها.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE SELECTION
        |--------------------------------------------------------------------------
        */
        function updateCompanySelection() {

            const checkboxes =
                document.querySelectorAll(
                    '.company-row-checkbox'
                );

            const selected =
                document.querySelectorAll(
                    '.company-row-checkbox:checked'
                );

            const selectionBar =
                document.getElementById(
                    'companySelectionBar'
                );

            const selectedCount =
                document.getElementById(
                    'selectedCompanyCount'
                );

            const selectAll =
                document.getElementById(
                    'selectAllCompanies'
                );

            if (!selectionBar || !selectedCount) {
                return;
            }

            const count =
                selected.length;

            selectedCount.textContent =
                count;


            /*
            |--------------------------------------------------------------------------
            | Show / hide selection bar
            |--------------------------------------------------------------------------
            */
            if (count > 0) {

                selectionBar.classList.remove(
                    'hidden'
                );

            } else {

                selectionBar.classList.add(
                    'hidden'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Select all state
            |--------------------------------------------------------------------------
            */
            if (selectAll) {

                selectAll.checked =
                    checkboxes.length > 0 &&
                    selected.length === checkboxes.length;

                selectAll.indeterminate =
                    selected.length > 0 &&
                    selected.length < checkboxes.length;

            }

        }


        /*
        |--------------------------------------------------------------------------
        | CLEAR SELECTION
        |--------------------------------------------------------------------------
        */
        function clearCompanySelection() {

            document
                .querySelectorAll(
                    '.company-row-checkbox'
                )
                .forEach(function (checkbox) {

                    checkbox.checked = false;

                });


            const selectAll =
                document.getElementById(
                    'selectAllCompanies'
                );

            if (selectAll) {

                selectAll.checked = false;

                selectAll.indeterminate = false;

            }


            updateCompanySelection();

        }


        /*
        |--------------------------------------------------------------------------
        | TABLE CHECKBOX EVENTS
        |--------------------------------------------------------------------------
        */
        document.addEventListener(
            'change',
            function (event) {

                /*
                |--------------------------------------------------------------------------
                | Select all
                |--------------------------------------------------------------------------
                */
                if (
                    event.target.id ===
                    'selectAllCompanies'
                ) {

                    const checked =
                        event.target.checked;

                    document
                        .querySelectorAll(
                            '.company-row-checkbox'
                        )
                        .forEach(function (checkbox) {

                            checkbox.checked =
                                checked;

                        });

                    updateCompanySelection();

                }


                /*
                |--------------------------------------------------------------------------
                | Individual checkbox
                |--------------------------------------------------------------------------
                */
                if (
                    event.target.classList.contains(
                        'company-row-checkbox'
                    )
                ) {

                    updateCompanySelection();

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | CTRL + K SEARCH
        |--------------------------------------------------------------------------
        */
        document.addEventListener(
            'keydown',
            function (event) {

                if (
                    (event.ctrlKey || event.metaKey) &&
                    event.key.toLowerCase() === 'k'
                ) {

                    const search =
                        document.getElementById(
                            'companySearch'
                        );

                    if (!search) {
                        return;
                    }

                    event.preventDefault();

                    search.focus();

                    search.select();

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | ESCAPE SEARCH
        |--------------------------------------------------------------------------
        */
        document.addEventListener(
            'DOMContentLoaded',
            function () {

                const search =
                    document.getElementById(
                        'companySearch'
                    );

                const form =
                    document.getElementById(
                        'companiesFilterForm'
                    );

                if (!search) {
                    return;
                }


                search.addEventListener(
                    'keydown',
                    function (event) {

                        if (
                            event.key === 'Escape' &&
                            search.value !== ''
                        ) {

                            search.value = '';

                            if (form) {

                                form.submit();

                            }

                        }

                    }
                );


                /*
                |--------------------------------------------------------------------------
                | INITIAL SELECTION STATE
                |--------------------------------------------------------------------------
                */
                updateCompanySelection();

            }
        );

    </script>

@endsection