{{-- resources/views/banks/index.blade.php --}}
@extends('layouts.app')

@section('title', 'البنوك')

@php
    /*
    |--------------------------------------------------------------------------
    | SAFE COLLECTION
    |--------------------------------------------------------------------------
    */
    $bankCollection = collect($banks);

    /*
    |--------------------------------------------------------------------------
    | MAIN COUNTERS
    |--------------------------------------------------------------------------
    */
    $visibleBanks = $bankCollection->count();

    $totalBanks = method_exists($banks, 'total')
        ? $banks->total()
        : $visibleBanks;

    $activeBanks = $bankCollection
        ->filter(fn ($bank) => (bool) $bank->is_active)
        ->count();

    $inactiveBanks = $bankCollection
        ->filter(fn ($bank) => ! (bool) $bank->is_active)
        ->count();

    /*
    |--------------------------------------------------------------------------
    | OPERATING PERCENTAGE
    |--------------------------------------------------------------------------
    */
    $activePercent = $visibleBanks > 0
        ? (int) round(($activeBanks / $visibleBanks) * 100)
        : 0;

    $activePercent = min(100, max(0, $activePercent));

    /*
    |--------------------------------------------------------------------------
    | LOGO STATISTICS
    |--------------------------------------------------------------------------
    */
    $banksWithLogo = $bankCollection
        ->filter(fn ($bank) => !empty($bank->logo))
        ->count();

    $banksWithoutLogo = max(0, $visibleBanks - $banksWithLogo);

    /*
    |--------------------------------------------------------------------------
    | FILTERS
    |--------------------------------------------------------------------------
    */
    $currentSearch = request('search', '');
    $currentStatus = request('status', '');

    $hasFilters = $currentSearch !== '' || $currentStatus !== '';

    /*
    |--------------------------------------------------------------------------
    | PAGINATION
    |--------------------------------------------------------------------------
    */
    $hasPagination = method_exists($banks, 'links');

    $firstItem = method_exists($banks, 'firstItem')
        ? $banks->firstItem()
        : ($visibleBanks > 0 ? 1 : 0);

    $lastItem = method_exists($banks, 'lastItem')
        ? $banks->lastItem()
        : $visibleBanks;

    /*
    |--------------------------------------------------------------------------
    | STATUS LABEL
    |--------------------------------------------------------------------------
    */
    $statusLabel = match ($currentStatus) {
        'active' => 'البنوك النشطة',
        'inactive' => 'البنوك المتوقفة',
        default => 'كل البنوك',
    };
@endphp

@section('content')

    {{-- ================================================================
        PAGE HEADER
    ================================================================= --}}
    <x-page-header
        title="البنوك"
        subtitle="اختر بنكاً لفتح لوحة تحكم النطاق الخاصة به"
        icon="fa-building-columns"
    >
        <x-slot:actions>
            <a
                href="{{ route('banks.create') }}"
                class="btn btn-primary"
                title="إضافة بنك جديد"
                aria-label="إضافة بنك جديد"
            >
                <i class="fa-solid fa-plus text-xs"></i>
                إضافة بنك
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-flash />

    {{-- ================================================================
        KPI CARDS
    ================================================================= --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-6">

        {{-- Total --}}
        <div class="card relative overflow-hidden">
            <div class="flex items-center justify-between gap-3">

                <div>
                    <div class="text-xs text-muted mb-1">
                        إجمالي البنوك
                    </div>

                    <div class="text-2xl font-bold text-fg">
                        {{ number_format($totalBanks) }}
                    </div>

                    <div class="mt-1 text-[11px] text-dim">
                        الجهات البنكية المسجلة
                    </div>
                </div>

                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-info/15 text-info">
                    <i class="fa-solid fa-building-columns"></i>
                </span>

            </div>
        </div>

        {{-- Active --}}
        <div class="card relative overflow-hidden">
            <div class="flex items-center justify-between gap-3">

                <div>
                    <div class="text-xs text-muted mb-1">
                        البنوك النشطة
                    </div>

                    <div class="text-2xl font-bold text-brand">
                        {{ number_format($activeBanks) }}
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

        {{-- Inactive --}}
        <div class="card relative overflow-hidden">
            <div class="flex items-center justify-between gap-3">

                <div>
                    <div class="text-xs text-muted mb-1">
                        البنوك المتوقفة
                    </div>

                    <div class="text-2xl font-bold text-danger">
                        {{ number_format($inactiveBanks) }}
                    </div>

                    <div class="mt-1 text-[11px] text-dim">
                        غير متاحة حاليًا
                    </div>
                </div>

                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-danger/15 text-danger">
                    <i class="fa-solid fa-circle-pause"></i>
                </span>

            </div>
        </div>

        {{-- Percentage --}}
        <div class="card relative overflow-hidden">
            <div class="flex items-center justify-between gap-3">

                <div>
                    <div class="text-xs text-muted mb-1">
                        نسبة التشغيل
                    </div>

                    <div class="text-2xl font-bold text-accent">
                        {{ $activePercent }}%
                    </div>

                    <div class="mt-1 text-[11px] text-dim">
                        من البنوك المعروضة نشطة
                    </div>
                </div>

                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-accent/15 text-accent">
                    <i class="fa-solid fa-chart-line"></i>
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
                        حالة البنوك
                    </div>

                    <div class="text-[11px] text-dim mt-1">
                        ملخص الحالة الحالية
                    </div>
                </div>

                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand/15 text-brand">
                    <i class="fa-solid fa-chart-pie"></i>
                </span>

            </div>

            <div class="space-y-3">

                <div class="flex items-center justify-between">

                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-brand"></span>
                        <span class="text-xs text-muted">
                            نشط
                        </span>
                    </div>

                    <span class="badge badge-success">
                        {{ number_format($activeBanks) }}
                    </span>

                </div>

                <div class="flex items-center justify-between">

                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-danger"></span>
                        <span class="text-xs text-muted">
                            متوقف
                        </span>
                    </div>

                    <span class="badge badge-danger">
                        {{ number_format($inactiveBanks) }}
                    </span>

                </div>

            </div>

        </div>


        {{-- Logos --}}
        <div class="card">

            <div class="flex items-center justify-between mb-4">

                <div>
                    <div class="text-sm font-bold text-fg">
                        هوية البنوك
                    </div>

                    <div class="text-[11px] text-dim mt-1">
                        حالة الشعارات المسجلة
                    </div>
                </div>

                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-info/15 text-info">
                    <i class="fa-solid fa-image"></i>
                </span>

            </div>

            <div class="grid grid-cols-2 gap-3">

                <div class="rounded-lg border border-white/5 bg-white/[0.02] p-3">

                    <div class="text-[11px] text-dim">
                        بشعار
                    </div>

                    <div class="mt-1 text-xl font-bold text-brand">
                        {{ number_format($banksWithLogo) }}
                    </div>

                </div>

                <div class="rounded-lg border border-white/5 bg-white/[0.02] p-3">

                    <div class="text-[11px] text-dim">
                        بدون شعار
                    </div>

                    <div class="mt-1 text-xl font-bold text-warning">
                        {{ number_format($banksWithoutLogo) }}
                    </div>

                </div>

            </div>

        </div>


        {{-- Operating --}}
        <div class="card">

            <div class="flex items-center justify-between mb-4">

                <div>
                    <div class="text-sm font-bold text-fg">
                        معدل التشغيل
                    </div>

                    <div class="text-[11px] text-dim mt-1">
                        نسبة البنوك النشطة
                    </div>
                </div>

                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-accent/15 text-accent">
                    <i class="fa-solid fa-gauge-high"></i>
                </span>

            </div>

            <div class="mb-2 flex items-center justify-between text-[11px] text-muted">

                <span>
                    البنوك النشطة
                </span>

                <span>
                    {{ $activeBanks }} / {{ $visibleBanks }}
                </span>

            </div>

            <div class="progress">
                <div
                    class="h-full rounded-full bg-brand"
                    style="width: {{ $activePercent }}%"
                ></div>
            </div>

            <div class="mt-2 text-right text-[11px]">
                <span class="font-semibold text-brand">
                    {{ $activePercent }}%
                </span>
            </div>

        </div>

    </div>


    {{-- ================================================================
        FILTER BAR
    ================================================================= --}}
    <form
        method="GET"
        action="{{ route('banks.index') }}"
        class="card filter-bar mb-6"
        id="banksFilterForm"
    >

        {{-- Search --}}
        <div class="min-w-[260px] flex-1">

            <label
                for="bankSearch"
                class="sr-only"
            >
                البحث في البنوك
            </label>

            <div class="relative">

                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-dim"></i>

                <input
                    id="bankSearch"
                    name="search"
                    type="search"
                    value="{{ $currentSearch }}"
                    placeholder="ابحث باسم البنك أو الكود..."
                    class="form-input pl-9"
                    autocomplete="off"
                >

            </div>

        </div>


        {{-- Status --}}
        <div class="w-48">

            <label
                for="bankStatus"
                class="form-label"
            >
                <i class="fa-solid fa-circle-dot ml-1 text-info"></i>
                الحالة
            </label>

            <select
                id="bankStatus"
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
                    نشط
                </option>

                <option
                    value="inactive"
                    @selected($currentStatus === 'inactive')
                >
                    متوقف
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
            href="{{ route('banks.index') }}"
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
        TABLE HEADER / INFORMATION
    ================================================================= --}}
    <div class="mb-3 flex flex-wrap items-center justify-between gap-3">

        <div class="flex items-center gap-3">

            <div class="flex items-center gap-2 text-sm font-bold text-fg">

                <i class="fa-solid fa-building-columns text-info"></i>

                قائمة البنوك

            </div>

            <span class="badge badge-outline-info">

                {{ number_format($visibleBanks) }}

                معروض

            </span>

        </div>


        <div class="flex flex-wrap items-center gap-3 text-[11px] text-dim">

            <span>

                <i class="fa-solid fa-circle-check text-brand ml-1"></i>

                {{ number_format($activeBanks) }}

                نشط

            </span>

            <span>

                <i class="fa-solid fa-circle-pause text-danger ml-1"></i>

                {{ number_format($inactiveBanks) }}

                متوقف

            </span>

        </div>

    </div>


    {{-- ================================================================
        TABLE
    ================================================================= --}}
    <div class="table-wrap overflow-x-auto">

        <table class="data-table min-w-[900px]">

            {{-- ========================================================
                TABLE HEAD
            ========================================================= --}}
            <thead>

                <tr>

                    {{-- Selection --}}
                    <th class="w-10 text-center">

                        <input
                            type="checkbox"
                            id="selectAllBanks"
                            class="h-4 w-4 rounded border-white/10 bg-white/5 text-brand focus:ring-brand"
                            title="تحديد جميع البنوك"
                            aria-label="تحديد جميع البنوك"
                        >

                    </th>

                    <th>
                        #
                    </th>

                    <th>
                        البنك
                    </th>

                    <th>
                        الكود
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


            {{-- ========================================================
                TABLE BODY
            ========================================================= --}}
            <tbody>

                @forelse($banks as $bank)

                    <tr
                        class="group transition-colors duration-150 hover:bg-white/[0.025]"
                        data-bank-row="{{ $bank->id }}"
                    >

                        {{-- =================================================
                            SELECT
                        ================================================== --}}
                        <td class="text-center">

                            <input
                                type="checkbox"
                                class="bank-row-checkbox h-4 w-4 rounded border-white/10 bg-white/5 text-brand focus:ring-brand"
                                value="{{ $bank->id }}"
                                title="تحديد {{ $bank->name }}"
                                aria-label="تحديد {{ $bank->name }}"
                            >

                        </td>


                        {{-- =================================================
                            ID
                        ================================================== --}}
                        <td>

                            <span
                                class="inline-flex items-center rounded-md bg-white/[0.03] px-2 py-1 font-mono text-[11px] text-dim"
                                title="معرف البنك"
                            >
                                #{{ $bank->id }}
                            </span>

                        </td>


                        {{-- =================================================
                            BANK IDENTITY
                        ================================================== --}}
                        <td>

                            <a
                                href="{{ route('banks.panel', $bank->id) }}"
                                class="group/bank inline-flex min-w-[220px] items-center gap-3"
                                title="فتح لوحة تحكم {{ $bank->name }}"
                            >

                                {{-- Logo --}}
                                <span
                                    class="relative flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-white/10 bg-white shadow-sm"
                                >

                                    @if($bank->logo ?? null)

                                        <img
                                            src="{{ $bank->logo }}"
                                            alt="{{ $bank->name }}"
                                            class="h-full w-full object-contain p-1.5 transition duration-200 group-hover/bank:scale-105"
                                            loading="lazy"
                                        >

                                    @else

                                        <i class="fa-solid fa-building-columns text-sm text-black"></i>

                                    @endif

                                    {{-- Status dot --}}
                                    <span
                                        class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-[#14191e] {{ $bank->is_active ? 'bg-brand' : 'bg-danger' }}"
                                        title="{{ $bank->is_active ? 'نشط' : 'متوقف' }}"
                                    ></span>

                                </span>


                                {{-- Name --}}
                                <span class="min-w-0">

                                    <span
                                        class="block truncate font-semibold text-fg transition group-hover/bank:text-brand"
                                    >
                                        {{ $bank->name }}
                                    </span>

                                    <span class="mt-1 flex items-center gap-2 text-[10px] text-dim">

                                        <span>
                                            <i class="fa-solid fa-building-columns ml-1"></i>
                                            بنك
                                        </span>

                                        <span class="text-white/10">
                                            •
                                        </span>

                                        <span>
                                            ID #{{ $bank->id }}
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
                                    title="كود البنك"
                                >
                                    {{ $bank->code ?: '-' }}
                                </span>

                                @if($bank->code)

                                    <button
                                        type="button"
                                        class="flex h-7 w-7 items-center justify-center rounded-md text-dim transition hover:bg-info/10 hover:text-info"
                                        title="نسخ كود البنك"
                                        aria-label="نسخ كود البنك"
                                        onclick="copyBankCode(@js($bank->code), this)"
                                    >
                                        <i class="fa-regular fa-copy text-[10px]"></i>
                                    </button>

                                @endif

                            </div>

                        </td>


                        {{-- =================================================
                            STATUS
                        ================================================== --}}
                        <td>

                            <span
                                class="badge {{ $bank->is_active ? 'badge-success' : 'badge-danger' }}"
                                title="{{ $bank->is_active ? 'البنك متاح ونشط' : 'البنك متوقف' }}"
                            >

                                @if($bank->is_active)

                                    <i class="fa-solid fa-circle-check text-[9px]"></i>

                                    نشط

                                @else

                                    <i class="fa-solid fa-circle-pause text-[9px]"></i>

                                    متوقف

                                @endif

                            </span>

                        </td>


                        {{-- =================================================
                            ACCESS
                        ================================================== --}}
                        <td>

                            <a
                                href="{{ route('banks.panel', $bank->id) }}"
                                class="inline-flex items-center gap-2 rounded-lg border border-info/10 bg-info/5 px-3 py-2 text-[11px] text-info transition hover:border-info/30 hover:bg-info/10"
                                title="فتح لوحة تحكم البنك"
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

                                {{-- Open --}}
                                <a
                                    href="{{ route('banks.panel', $bank->id) }}"
                                    class="btn btn-outline-info btn-sm"
                                    title="فتح لوحة تحكم البنك"
                                    aria-label="فتح لوحة تحكم البنك"
                                >

                                    <i class="fa-solid fa-table-cells-large text-xs"></i>

                                    لوحة التحكم

                                </a>


                                {{-- Edit --}}
                                <a
                                    href="{{ route('banks.edit', $bank->id) }}"
                                    class="btn btn-secondary btn-sm"
                                    title="تعديل بيانات البنك"
                                    aria-label="تعديل بيانات البنك"
                                >

                                    <i class="fa-solid fa-pen text-[10px]"></i>

                                    تعديل

                                </a>


                                {{-- Delete --}}
                                <form
                                    method="POST"
                                    action="{{ route('banks.destroy', $bank->id) }}"
                                    onsubmit="return confirmBankDelete(@js($bank->name))"
                                >

                                    @csrf

                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="btn btn-danger btn-sm"
                                        title="حذف البنك"
                                        aria-label="حذف البنك"
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
                            colspan="7"
                            class="py-10 text-center text-dim"
                        >

                            <div class="py-4">

                                <x-empty-state
                                    icon="fa-building-columns"
                                    text="{{ $hasFilters ? 'لا توجد بنوك مطابقة للفلاتر الحالية' : 'لا توجد بنوك' }}"
                                />

                                @if($hasFilters)

                                    <div class="mt-4">

                                        <a
                                            href="{{ route('banks.index') }}"
                                            class="btn btn-secondary btn-sm"
                                        >

                                            <i class="fa-solid fa-rotate-right"></i>

                                            عرض جميع البنوك

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
        TABLE SELECTION BAR
    ================================================================= --}}
    <div
        id="bankSelectionBar"
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
                        id="selectedBankCount"
                        class="text-brand"
                    >
                        0
                    </strong>

                    بنك

                </span>

            </div>


            <div class="flex items-center gap-2">

                <button
                    type="button"
                    class="btn btn-secondary btn-sm"
                    onclick="clearBankSelection()"
                    title="إلغاء تحديد البنوك"
                >

                    <i class="fa-solid fa-xmark"></i>

                    إلغاء التحديد

                </button>

            </div>

        </div>

    </div>


    {{-- ================================================================
        PAGINATION
    ================================================================= --}}
    @if($hasPagination)

        <div class="mt-5">

            {{ $banks->withQueryString()->links() }}

        </div>

    @endif


    {{-- ================================================================
        FOOTER SUMMARY
    ================================================================= --}}
    @if($visibleBanks > 0)

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
                            {{ number_format($totalBanks) }}

                        @else

                            {{ number_format($visibleBanks) }}

                            بنك معروض

                        @endif

                    </span>


                    {{-- Active --}}
                    <span>

                        <i class="fa-solid fa-circle-check text-brand ml-1"></i>

                        {{ number_format($activeBanks) }}

                        نشط

                    </span>


                    {{-- Inactive --}}
                    <span>

                        <i class="fa-solid fa-circle-pause text-danger ml-1"></i>

                        {{ number_format($inactiveBanks) }}

                        متوقف

                    </span>


                    {{-- Logo --}}
                    <span>

                        <i class="fa-solid fa-image text-info ml-1"></i>

                        {{ number_format($banksWithLogo) }}

                        بشعار

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
                            جميع البنوك المسجلة
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
        | COPY BANK CODE
        |--------------------------------------------------------------------------
        */
        function copyBankCode(value, button) {

            if (!navigator.clipboard) {
                return;
            }

            navigator.clipboard.writeText(value).then(function () {

                const icon = button.querySelector('i');

                if (!icon) {
                    return;
                }

                const originalClass = icon.className;

                icon.className = 'fa-solid fa-check text-brand';

                setTimeout(function () {
                    icon.className = originalClass;
                }, 1200);

            });

        }


        /*
        |--------------------------------------------------------------------------
        | DELETE CONFIRMATION
        |--------------------------------------------------------------------------
        */
        function confirmBankDelete(bankName) {

            return confirm(
                'هل أنت متأكد من حذف البنك "' +
                bankName +
                '"؟\n\n' +
                'سيتم تنفيذ الحذف نهائيًا إذا لم تكن هناك بيانات مرتبطة به.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE SELECTION BAR
        |--------------------------------------------------------------------------
        */
        function updateBankSelection() {

            const checkboxes = document.querySelectorAll(
                '.bank-row-checkbox'
            );

            const selected = document.querySelectorAll(
                '.bank-row-checkbox:checked'
            );

            const selectionBar = document.getElementById(
                'bankSelectionBar'
            );

            const selectedCount = document.getElementById(
                'selectedBankCount'
            );

            const selectAll = document.getElementById(
                'selectAllBanks'
            );

            if (!selectionBar || !selectedCount) {
                return;
            }

            const count = selected.length;

            selectedCount.textContent = count;

            if (count > 0) {

                selectionBar.classList.remove('hidden');

            } else {

                selectionBar.classList.add('hidden');

            }

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
        function clearBankSelection() {

            document
                .querySelectorAll('.bank-row-checkbox')
                .forEach(function (checkbox) {

                    checkbox.checked = false;

                });

            const selectAll = document.getElementById(
                'selectAllBanks'
            );

            if (selectAll) {
                selectAll.checked = false;
                selectAll.indeterminate = false;
            }

            updateBankSelection();

        }


        /*
        |--------------------------------------------------------------------------
        | SELECT ALL
        |--------------------------------------------------------------------------
        */
        document.addEventListener(
            'change',
            function (event) {

                if (event.target.id === 'selectAllBanks') {

                    const checked = event.target.checked;

                    document
                        .querySelectorAll('.bank-row-checkbox')
                        .forEach(function (checkbox) {

                            checkbox.checked = checked;

                        });

                    updateBankSelection();

                }


                if (
                    event.target.classList.contains(
                        'bank-row-checkbox'
                    )
                ) {

                    updateBankSelection();

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | KEYBOARD SHORTCUT
        | Ctrl + K / Cmd + K
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
                            'bankSearch'
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
                        'bankSearch'
                    );

                const form =
                    document.getElementById(
                        'banksFilterForm'
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
                | INITIALIZE TABLE SELECTION
                |--------------------------------------------------------------------------
                */
                updateBankSelection();

            }
        );

    </script>

@endsection