{{-- resources/views/settings/lookup.blade.php
     ONE view for the simple "list + add / edit / delete" settings pages
     (loan types, governorates, and similar lookup/reference data).

     Existing controller contract:
     title, subtitle, icon, items, search, hasEnglish, hasActive,
     usedLabel, storeUrl, updateUrl, destroyUrl (with __ID__) and singular.

     This view intentionally keeps the existing structure, components,
     routes, styles and backend contract intact.
--}}

@extends('layouts.app')

@section('title', $title)

@php

    /*
    |--------------------------------------------------------------------------
    | Safe collection normalization
    |--------------------------------------------------------------------------
    */

    $lookupItems = collect($items);

    /*
    |--------------------------------------------------------------------------
    | Safe totals
    |--------------------------------------------------------------------------
    */

    $totalItems = method_exists($items, 'total')
        ? (int) $items->total()
        : $lookupItems->count();

    $currentPageCount = $lookupItems->count();

    /*
    |--------------------------------------------------------------------------
    | Active / inactive counts
    |--------------------------------------------------------------------------
    */

    $activeCount = 0;
    $inactiveCount = 0;

    if ($hasActive) {
        $activeCount = $lookupItems->filter(function ($item) {
            return (bool) ($item->is_active ?? false);
        })->count();

        $inactiveCount = $lookupItems->filter(function ($item) {
            return ! (bool) ($item->is_active ?? false);
        })->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Used count
    |--------------------------------------------------------------------------
    */

    $usedCount = $lookupItems->filter(function ($item) {
        return (int) ($item->used ?? 0) > 0;
    })->count();

    $unusedCount = $lookupItems->filter(function ($item) {
        return (int) ($item->used ?? 0) <= 0;
    })->count();

    /*
    |--------------------------------------------------------------------------
    | Maximum usage
    |--------------------------------------------------------------------------
    */

    $maxUsed = $lookupItems->max(function ($item) {
        return (int) ($item->used ?? 0);
    }) ?? 0;

    /*
    |--------------------------------------------------------------------------
    | Average usage
    |--------------------------------------------------------------------------
    */

    $averageUsed = $lookupItems->count()
        ? round(
            $lookupItems->avg(function ($item) {
                return (int) ($item->used ?? 0);
            }),
            1
        )
        : 0;

    /*
    |--------------------------------------------------------------------------
    | Current search state
    |--------------------------------------------------------------------------
    */

    $hasSearch = trim((string) $search) !== '';

    /*
    |--------------------------------------------------------------------------
    | Existing page links
    |--------------------------------------------------------------------------
    */

    $hasPagination = method_exists($items, 'links');

    /*
    |--------------------------------------------------------------------------
    | Old modal state
    |--------------------------------------------------------------------------
    */

    $oldMode = old('_mode');
    $oldId = old('_id');

    /*
    |--------------------------------------------------------------------------
    | Table colspan
    |--------------------------------------------------------------------------
    */

    $columnCount = 4;

    if ($hasEnglish) {
        $columnCount++;
    }

    if ($hasActive) {
        $columnCount++;
    }

@endphp

@section('content')

    {{-- ================================================================
         PAGE HEADER
    ================================================================= --}}
    <x-page-header
        :title="$title"
        :subtitle="$subtitle"
        :icon="$icon"
    >
        <x-slot:actions>

            <button
                type="button"
                id="addBtn"
                class="btn btn-primary btn-sm"
            >
                <i class="fa-solid fa-plus text-xs"></i>
                إضافة {{ $singular }}
            </button>

        </x-slot:actions>
    </x-page-header>

    <x-flash />

    {{-- ================================================================
         VALIDATION SUMMARY
    ================================================================= --}}
    @if($errors->any())

        <div class="mb-6 rounded-xl border border-danger/30 bg-danger/[0.05] p-4">

            <div class="flex items-start gap-3">

                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-danger/10 text-danger">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </span>

                <div class="min-w-0">

                    <h3 class="font-bold text-fg">
                        تعذر حفظ البيانات
                    </h3>

                    <p class="mt-1 text-xs text-muted">
                        راجع الأخطاء التالية ثم حاول مرة أخرى.
                    </p>

                    <ul class="mt-3 space-y-1 text-xs text-danger">

                        @foreach($errors->all() as $error)

                            <li class="flex items-start gap-2">
                                <i class="fa-solid fa-circle text-[4px] pt-1.5"></i>
                                <span>{{ $error }}</span>
                            </li>

                        @endforeach

                    </ul>

                </div>

            </div>

        </div>

    @endif

    {{-- ================================================================
         PAGE OVERVIEW / KPI
    ================================================================= --}}
    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        {{-- Total --}}
        <div class="card relative overflow-hidden">

            <div class="flex items-start justify-between gap-3">

                <div>
                    <p class="text-xs text-muted">
                        إجمالي {{ $singular }}
                    </p>

                    <p
                        id="totalCount"
                        class="mt-2 text-2xl font-bold text-fg"
                    >
                        {{ number_format($totalItems) }}
                    </p>

                    <p class="mt-1 text-[11px] text-dim">
                        {{ $currentPageCount }} ظاهر حالياً
                    </p>
                </div>

                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-info/10 text-info">
                    <i class="fa-solid {{ $icon }}"></i>
                </span>

            </div>

        </div>

        {{-- Active --}}
        @if($hasActive)

            <div class="card relative overflow-hidden">

                <div class="flex items-start justify-between gap-3">

                    <div>
                        <p class="text-xs text-muted">
                            النشط
                        </p>

                        <p
                            id="activeCount"
                            class="mt-2 text-2xl font-bold text-brand"
                        >
                            {{ number_format($activeCount) }}
                        </p>

                        <p class="mt-1 text-[11px] text-dim">
                            متاح للاستخدام
                        </p>
                    </div>

                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand/10 text-brand">
                        <i class="fa-solid fa-circle-check"></i>
                    </span>

                </div>

            </div>

        @else

            {{-- Used --}}
            <div class="card relative overflow-hidden">

                <div class="flex items-start justify-between gap-3">

                    <div>
                        <p class="text-xs text-muted">
                            المستخدم
                        </p>

                        <p class="mt-2 text-2xl font-bold text-fg">
                            {{ number_format($usedCount) }}
                        </p>

                        <p class="mt-1 text-[11px] text-dim">
                            عناصر مستخدمة فعلياً
                        </p>
                    </div>

                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand/10 text-brand">
                        <i class="fa-solid fa-link"></i>
                    </span>

                </div>

            </div>

        @endif

        {{-- Usage --}}
        <div class="card relative overflow-hidden">

            <div class="flex items-start justify-between gap-3">

                <div>
                    <p class="text-xs text-muted">
                        مستخدم فعلياً
                    </p>

                    <p class="mt-2 text-2xl font-bold text-fg">
                        {{ number_format($usedCount) }}
                    </p>

                    <p class="mt-1 text-[11px] text-dim">
                        {{ number_format($unusedCount) }} غير مستخدم
                    </p>
                </div>

                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-warning/10 text-warning">
                    <i class="fa-solid fa-chart-simple"></i>
                </span>

            </div>

        </div>

        {{-- Max usage --}}
        <div class="card relative overflow-hidden">

            <div class="flex items-start justify-between gap-3">

                <div>
                    <p class="text-xs text-muted">
                        أعلى استخدام
                    </p>

                    <p class="mt-2 text-2xl font-bold text-fg">
                        {{ number_format($maxUsed) }}
                    </p>

                    <p class="mt-1 text-[11px] text-dim">
                        المتوسط {{ number_format($averageUsed, 1) }}
                    </p>
                </div>

                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan/10 text-cyan">
                    <i class="fa-solid fa-arrow-trend-up"></i>
                </span>

            </div>

        </div>

    </section>

    {{-- ================================================================
         QUICK INFO
    ================================================================= --}}
    <div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-3">

        <div class="card lg:col-span-2">

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div class="flex items-center gap-3">

                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand/10 text-brand">
                        <i class="fa-solid fa-database"></i>
                    </span>

                    <div>
                        <h3 class="font-bold text-fg">
                            إدارة {{ $singular }}
                        </h3>

                        <p class="text-[11px] text-dim">
                            هذه البيانات تُستخدم كمرجع داخل أجزاء النظام المختلفة.
                        </p>
                    </div>

                </div>

                <div class="flex flex-wrap gap-2 text-[10px]">

                    @if($hasEnglish)
                        <span class="badge badge-info">
                            عربي + English
                        </span>
                    @endif

                    @if($hasActive)
                        <span class="badge badge-success">
                            يدعم الحالة
                        </span>
                    @endif

                    <span class="badge badge-secondary">
                        CRUD
                    </span>

                </div>

            </div>

        </div>

        <div class="card">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-[10px] text-dim">
                        حالة القائمة
                    </p>

                    <p class="mt-1 font-bold text-brand">
                        جاهزة للاستخدام
                    </p>
                </div>

                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand/10 text-brand">
                    <i class="fa-solid fa-circle-check"></i>
                </span>

            </div>

            <p class="mt-3 text-[10px] leading-5 text-dim">
                يمكن البحث والتعديل والإضافة والحذف من نفس الصفحة.
            </p>

        </div>

    </div>

    {{-- ================================================================
         FILTERS
    ================================================================= --}}
    @if($hasEnglish)

        <form
            id="filterForm"
            method="GET"
            action="{{ url()->current() }}"
            class="card filter-bar mb-4"
        >

            <div class="min-w-[240px] flex-1">

                <label
                    for="search"
                    class="sr-only"
                >
                    بحث
                </label>

                <div class="relative">

                    <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-dim"></i>

                    <input
                        id="search"
                        name="search"
                        type="search"
                        value="{{ $search }}"
                        placeholder="ابحث بالاسم أو الاسم الإنجليزي..."
                        class="form-input pl-9"
                        autocomplete="off"
                    >

                    <kbd class="absolute right-2 top-1/2 hidden -translate-y-1/2 rounded border border-white/10 bg-white/5 px-2 py-0.5 text-[9px] text-dim sm:block">
                        Ctrl K
                    </kbd>

                </div>

            </div>

            <button
                type="submit"
                class="btn btn-primary"
            >
                <i class="fa-solid fa-magnifying-glass text-xs"></i>
                بحث
            </button>

            <a
                href="{{ url()->current() }}"
                class="btn btn-secondary"
                title="إعادة ضبط"
                aria-label="إعادة ضبط البحث"
            >
                <i class="fa-solid fa-rotate-right"></i>
            </a>

        </form>

    @endif

    {{-- ================================================================
         ACTIVE FILTER / TOOLBAR
    ================================================================= --}}
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

        <div class="flex flex-wrap items-center gap-2">

            @if($hasSearch)

                <span class="inline-flex items-center gap-2 rounded-lg border border-info/20 bg-info/[0.05] px-3 py-2 text-[10px] text-info">

                    <i class="fa-solid fa-filter"></i>

                    <span>
                        البحث:
                    </span>

                    <b>
                        {{ $search }}
                    </b>

                    <a
                        href="{{ url()->current() }}"
                        class="mr-1 text-info hover:text-fg"
                        title="إزالة البحث"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </a>

                </span>

            @endif

            <span
                id="selectionSummary"
                class="hidden items-center gap-2 rounded-lg border border-brand/20 bg-brand/[0.05] px-3 py-2 text-[10px] text-brand"
            >
                <i class="fa-solid fa-check-double"></i>

                <b id="selectedCount">
                    0
                </b>

                <span>
                    محدد
                </span>
            </span>

        </div>

        <div class="flex items-center gap-2">

            <button
                type="button"
                id="copySelectedBtn"
                class="icon-btn icon-btn-info"
                title="نسخ المحدد"
                aria-label="نسخ المحدد"
                disabled
            >
                <i class="fa-solid fa-copy"></i>
            </button>

            <button
                type="button"
                id="clearSelectionBtn"
                class="icon-btn icon-btn-warning"
                title="إلغاء التحديد"
                aria-label="إلغاء التحديد"
                disabled
            >
                <i class="fa-solid fa-xmark"></i>
            </button>

            <button
                type="button"
                id="refreshPageBtn"
                class="icon-btn icon-btn-cyan"
                title="تحديث"
                aria-label="تحديث"
            >
                <i class="fa-solid fa-arrows-rotate"></i>
            </button>

        </div>

    </div>

    {{-- ================================================================
         TABLE
    ================================================================= --}}
    <div class="table-wrap">

        <table
            class="data-table whitespace-nowrap"
            id="lookupTable"
        >

            <thead>

                <tr>

                    <th class="w-10">
                        <input
                            type="checkbox"
                            id="checkAll"
                            class="accent-brand"
                            aria-label="تحديد الكل"
                        >
                    </th>

                    <th>
                        #
                    </th>

                    <th>
                        <span class="inline-flex items-center gap-2">
                            الاسم
                            <i class="fa-solid fa-arrow-down-a-z text-[9px] text-dim"></i>
                        </span>
                    </th>

                    @if($hasEnglish)
                        <th>
                            بالإنجليزية
                        </th>
                    @endif

                    <th>
                        {{ $usedLabel }}
                    </th>

                    @if($hasActive)
                        <th>
                            الحالة
                        </th>
                    @endif

                    <th>
                        الإجراءات
                    </th>

                </tr>

            </thead>

            <tbody>

                @forelse($items as $item)

                    @php
                        $itemName = (string) ($item->name ?? '');
                        $itemNameEn = (string) ($item->name_en ?? '');
                        $itemUsed = (int) ($item->used ?? 0);
                        $itemActive = (bool) ($item->is_active ?? true);
                    @endphp

                    <tr
                        data-row
                        data-id="{{ $item->id }}"
                        data-name="{{ $itemName }}"
                        data-name-en="{{ $itemNameEn }}"
                        data-used="{{ $itemUsed }}"
                        data-active="{{ $itemActive ? 1 : 0 }}"
                    >

                        {{-- Checkbox --}}
                        <td>

                            <input
                                type="checkbox"
                                class="accent-brand row-check"
                                value="{{ $item->id }}"
                                data-name="{{ $itemName }}"
                                data-name-en="{{ $itemNameEn }}"
                                aria-label="تحديد {{ $itemName }}"
                            >

                        </td>

                        {{-- ID --}}
                        <td>

                            <button
                                type="button"
                                class="font-mono text-xs text-muted hover:text-brand"
                                data-copy-value="{{ $item->id }}"
                                title="نسخ رقم السجل"
                            >
                                #{{ $item->id }}
                            </button>

                        </td>

                        {{-- Arabic name --}}
                        <td class="font-semibold text-fg">

                            <div class="flex items-center gap-3">

                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand/10 text-brand">
                                    <i class="fa-solid {{ $icon }} text-xs"></i>
                                </span>

                                <div class="min-w-0">

                                    <button
                                        type="button"
                                        class="block max-w-[260px] truncate text-right font-semibold text-fg hover:text-brand"
                                        data-copy-value="{{ $itemName }}"
                                        title="نسخ الاسم"
                                    >
                                        {{ $itemName }}
                                    </button>

                                    <span class="mt-0.5 block text-[9px] text-dim">
                                        ID: {{ $item->id }}
                                    </span>

                                </div>

                            </div>

                        </td>

                        {{-- English --}}
                        @if($hasEnglish)

                            <td
                                dir="ltr"
                                class="text-right"
                            >

                                <button
                                    type="button"
                                    class="max-w-[220px] truncate text-left text-muted hover:text-info"
                                    data-copy-value="{{ $itemNameEn }}"
                                    title="نسخ الاسم الإنجليزي"
                                >
                                    {{ $itemNameEn ?: '—' }}
                                </button>

                            </td>

                        @endif

                        {{-- Used --}}
                        <td>

                            @if($itemUsed > 0)

                                <div class="min-w-[120px]">

                                    <div class="flex items-center justify-between gap-3">

                                        <span class="text-xs font-semibold text-fg">
                                            {{ number_format($itemUsed) }}
                                        </span>

                                        <span class="text-[9px] text-dim">
                                            استخدام
                                        </span>

                                    </div>

                                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-white/5">

                                        @php
                                            $usageWidth = $maxUsed > 0
                                                ? min(100, round(($itemUsed / $maxUsed) * 100))
                                                : 0;
                                        @endphp

                                        <div
                                            class="h-full rounded-full bg-brand transition-all"
                                            style="width: {{ $usageWidth }}%"
                                        ></div>

                                    </div>

                                </div>

                            @else

                                <span class="text-muted">
                                    -
                                </span>

                            @endif

                        </td>

                        {{-- Status --}}
                        @if($hasActive)

                            <td>

                                <span
                                    class="badge {{ $itemActive ? 'badge-success' : 'badge-danger' }}"
                                >
                                    <i class="fa-solid {{ $itemActive ? 'fa-circle-check' : 'fa-circle-pause' }} ml-1 text-[8px]"></i>
                                    {{ $itemActive ? 'نشط' : 'متوقف' }}
                                </span>

                            </td>

                        @endif

                        {{-- Actions --}}
                        <td>

                            <div class="flex items-center gap-2">

                                {{-- View --}}
                                <button
                                    type="button"
                                    class="icon-btn icon-btn-info"
                                    title="عرض التفاصيل"
                                    aria-label="عرض التفاصيل"
                                    data-view
                                    data-id="{{ $item->id }}"
                                    data-name="{{ $itemName }}"
                                    data-name-en="{{ $itemNameEn }}"
                                    data-used="{{ $itemUsed }}"
                                    data-active="{{ $itemActive ? 1 : 0 }}"
                                >
                                    <i class="fa-solid fa-eye"></i>
                                </button>

                                {{-- Copy --}}
                                <button
                                    type="button"
                                    class="icon-btn icon-btn-cyan"
                                    title="نسخ الاسم"
                                    aria-label="نسخ الاسم"
                                    data-copy-value="{{ $itemName }}"
                                >
                                    <i class="fa-solid fa-copy"></i>
                                </button>

                                {{-- Edit --}}
                                <button
                                    type="button"
                                    class="icon-btn icon-btn-warning"
                                    title="تعديل"
                                    aria-label="تعديل"
                                    data-edit
                                    data-id="{{ $item->id }}"
                                    data-name="{{ $itemName }}"
                                    data-name-en="{{ $itemNameEn }}"
                                    data-active="{{ $itemActive ? 1 : 0 }}"
                                >
                                    <i class="fa-solid fa-pen"></i>
                                </button>

                                {{-- Delete --}}
                                <form
                                    method="POST"
                                    action="{{ str_replace('__ID__', $item->id, $destroyUrl) }}"
                                    data-delete-form
                                >

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="icon-btn icon-btn-danger"
                                        title="{{ $itemUsed > 0 ? 'حذف - مستخدم حالياً' : 'حذف' }}"
                                        aria-label="حذف"
                                        data-delete
                                        data-name="{{ $itemName }}"
                                        data-used="{{ $itemUsed }}"
                                    >
                                        <i class="fa-solid fa-trash"></i>
                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="{{ $columnCount }}" class="py-12">

                            <x-empty-state text="لا توجد بيانات" />

                            @if($hasSearch)

                                <div class="mt-4 text-center">

                                    <a
                                        href="{{ url()->current() }}"
                                        class="btn btn-secondary btn-sm"
                                    >
                                        <i class="fa-solid fa-rotate-left text-xs"></i>
                                        إزالة البحث
                                    </a>

                                </div>

                            @endif

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    {{-- ================================================================
         TABLE FOOTER
    ================================================================= --}}
    <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

        <div class="text-[10px] text-dim">

            عرض
            <b class="text-fg">
                {{ $currentPageCount }}
            </b>
            من
            <b class="text-fg">
                {{ number_format($totalItems) }}
            </b>

            @if($hasSearch)
                <span class="mr-1">
                    نتيجة للبحث الحالي
                </span>
            @else
                <span class="mr-1">
                    سجل
                </span>
            @endif

        </div>

        @if($hasPagination)

            <div>
                {{ $items->links() }}
            </div>

        @endif

    </div>

    {{-- ================================================================
         ADD / EDIT MODAL
    ================================================================= --}}
    <x-modal
        id="lookupModal"
        :title="'إضافة ' . $singular"
        width="30rem"
    >

        <form
            id="lookupForm"
            method="POST"
            action="{{ $storeUrl }}"
            class="space-y-4"
        >

            @csrf

            <input
                type="hidden"
                name="_method"
                value="PUT"
                id="lookup-method"
                disabled
            >

            <input
                type="hidden"
                name="_mode"
                id="lookup-mode"
                value="add"
            >

            <input
                type="hidden"
                name="_id"
                id="lookup-id"
                value=""
            >

            {{-- Modal header --}}
            <div
                id="modalModeBanner"
                class="rounded-xl border border-brand/15 bg-brand/[0.03] p-4"
            >

                <div class="flex items-center gap-3">

                    <span
                        id="modalModeIcon"
                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand/10 text-brand"
                    >
                        <i class="fa-solid fa-plus"></i>
                    </span>

                    <div>

                        <p
                            id="modalModeTitle"
                            class="font-bold text-fg"
                        >
                            إضافة {{ $singular }}
                        </p>

                        <p
                            id="modalModeHint"
                            class="text-[10px] text-dim"
                        >
                            أضف قيمة جديدة إلى قائمة النظام.
                        </p>

                    </div>

                </div>

            </div>

            {{-- Name --}}
            <div>

                <x-form-field
                    name="name"
                    label="الاسم"
                />

                <div class="mt-1 flex items-center justify-between">

                    <span
                        id="nameValidationHint"
                        class="text-[9px] text-dim"
                    >
                        أدخل اسماً واضحاً ومميزاً.
                    </span>

                    <span
                        id="nameCounter"
                        class="text-[9px] text-dim"
                    >
                        0
                    </span>

                </div>

            </div>

            {{-- English --}}
            @if($hasEnglish)

                <div>

                    <x-form-field
                        name="name_en"
                        label="الاسم بالإنجليزية"
                        dir="ltr"
                    />

                    <div class="mt-1 flex items-center justify-between">

                        <span class="text-[9px] text-dim">
                            استخدم اسماً إنجليزياً واضحاً ومتناسقاً.
                        </span>

                        <span
                            id="nameEnCounter"
                            class="text-[9px] text-dim"
                        >
                            0
                        </span>

                    </div>

                </div>

            @endif

            {{-- Active --}}
            @if($hasActive)

                <label class="check-row">

                    <input
                        type="hidden"
                        name="is_active"
                        value="0"
                    >

                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        class="accent-brand"
                        checked
                        id="lookup-active"
                    >

                    <span>

                        <b class="text-fg">
                            نشط
                        </b>

                        <span class="block text-[11px] text-dim">
                            يظهر في القوائم التي تعتمد على البيانات النشطة.
                        </span>

                    </span>

                </label>

            @endif

            {{-- Duplicate warning --}}
            <div
                id="duplicateWarning"
                class="hidden rounded-xl border border-warning/20 bg-warning/[0.05] p-3"
            >

                <div class="flex items-start gap-2 text-warning">

                    <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>

                    <div>
                        <p class="text-xs font-bold">
                            تحقق من الاسم
                        </p>

                        <p class="mt-1 text-[10px] leading-5">
                            يوجد عنصر مشابه في البيانات الحالية.
                            تأكد أنك لا تضيف قيمة مكررة.
                        </p>
                    </div>

                </div>

            </div>

            {{-- Existing usage when editing --}}
            <div
                id="editUsageInfo"
                class="hidden rounded-xl border border-info/15 bg-info/[0.03] p-3"
            >

                <div class="flex items-center justify-between">

                    <div class="flex items-center gap-2">

                        <i class="fa-solid fa-chart-simple text-info"></i>

                        <span class="text-[11px] text-muted">
                            الاستخدام الحالي
                        </span>

                    </div>

                    <b
                        id="editUsageValue"
                        class="text-sm text-fg"
                    >
                        0
                    </b>

                </div>

            </div>

            {{-- Form actions --}}
            <div class="flex flex-wrap gap-2 pt-2">

                <button
                    type="submit"
                    id="lookupSubmit"
                    class="btn btn-primary"
                >
                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                    <span id="lookupSubmitText">
                        حفظ
                    </span>
                </button>

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-close-modal
                >
                    إلغاء
                </button>

            </div>

        </form>

    </x-modal>

    {{-- ================================================================
         DETAILS MODAL
    ================================================================= --}}
    <x-modal
        id="lookupDetailsModal"
        title="تفاصيل {{ $singular }}"
        width="34rem"
    >

        <div class="space-y-4">

            <div class="flex items-center gap-3 rounded-xl border border-info/15 bg-info/[0.03] p-4">

                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-info/10 text-info">
                    <i class="fa-solid {{ $icon }}"></i>
                </span>

                <div class="min-w-0">

                    <p
                        id="detailsName"
                        class="truncate text-lg font-bold text-fg"
                    >
                        -
                    </p>

                    <p class="mt-1 text-[10px] text-dim">
                        <span id="detailsId">
                            ID: -
                        </span>
                    </p>

                </div>

            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

                <div class="rounded-xl border border-white/5 bg-white/[0.02] p-4">

                    <p class="text-[10px] text-dim">
                        الاسم
                    </p>

                    <p
                        id="detailsArabicName"
                        class="mt-1 text-sm font-semibold text-fg"
                    >
                        -
                    </p>

                </div>

                @if($hasEnglish)

                    <div class="rounded-xl border border-white/5 bg-white/[0.02] p-4">

                        <p class="text-[10px] text-dim">
                            English
                        </p>

                        <p
                            id="detailsEnglishName"
                            dir="ltr"
                            class="mt-1 text-left text-sm font-semibold text-fg"
                        >
                            -
                        </p>

                    </div>

                @endif

                <div class="rounded-xl border border-white/5 bg-white/[0.02] p-4">

                    <p class="text-[10px] text-dim">
                        الاستخدام
                    </p>

                    <p
                        id="detailsUsed"
                        class="mt-1 text-sm font-semibold text-fg"
                    >
                        -
                    </p>

                </div>

                @if($hasActive)

                    <div class="rounded-xl border border-white/5 bg-white/[0.02] p-4">

                        <p class="text-[10px] text-dim">
                            الحالة
                        </p>

                        <p
                            id="detailsStatus"
                            class="mt-1 text-sm font-semibold text-fg"
                        >
                            -
                        </p>

                    </div>

                @endif

            </div>

            <div class="flex flex-wrap gap-2">

                <button
                    type="button"
                    id="detailsCopyName"
                    class="btn btn-secondary btn-sm"
                >
                    <i class="fa-solid fa-copy text-xs"></i>
                    نسخ الاسم
                </button>

                <button
                    type="button"
                    id="detailsEditBtn"
                    class="btn btn-primary btn-sm"
                >
                    <i class="fa-solid fa-pen text-xs"></i>
                    تعديل
                </button>

                <button
                    type="button"
                    class="btn btn-secondary btn-sm"
                    data-close-modal
                >
                    إغلاق
                </button>

            </div>

        </div>

    </x-modal>

@endsection

@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | Configuration from Laravel
    |--------------------------------------------------------------------------
    */

    const storeUrl = @json($storeUrl);
    const updateUrl = @json($updateUrl);
    const singular = @json($singular);

    /*
    |--------------------------------------------------------------------------
    | Cached DOM elements
    |--------------------------------------------------------------------------
    */

    const dialog = document.getElementById('lookupModal');
    const detailsDialog = document.getElementById('lookupDetailsModal');

    const form = document.getElementById('lookupForm');
    const method = document.getElementById('lookup-method');

    const nameField = document.getElementById('name');
    const nameEnField = document.getElementById('name_en');

    const activeField = form
        ? form.querySelector('input[type="checkbox"][name="is_active"]')
        : null;

    const addBtn = document.getElementById('addBtn');

    const table = document.getElementById('lookupTable');

    const checkAll = document.getElementById('checkAll');

    const selectedCount = document.getElementById('selectedCount');
    const selectionSummary = document.getElementById('selectionSummary');

    const copySelectedBtn = document.getElementById('copySelectedBtn');
    const clearSelectionBtn = document.getElementById('clearSelectionBtn');

    const refreshPageBtn = document.getElementById('refreshPageBtn');

    const lookupSubmit = document.getElementById('lookupSubmit');
    const lookupSubmitText = document.getElementById('lookupSubmitText');

    const lookupMode = document.getElementById('lookup-mode');
    const lookupId = document.getElementById('lookup-id');

    const nameCounter = document.getElementById('nameCounter');
    const nameEnCounter = document.getElementById('nameEnCounter');

    const duplicateWarning = document.getElementById('duplicateWarning');

    const editUsageInfo = document.getElementById('editUsageInfo');
    const editUsageValue = document.getElementById('editUsageValue');

    const detailsName = document.getElementById('detailsName');
    const detailsId = document.getElementById('detailsId');
    const detailsArabicName = document.getElementById('detailsArabicName');
    const detailsEnglishName = document.getElementById('detailsEnglishName');
    const detailsUsed = document.getElementById('detailsUsed');
    const detailsStatus = document.getElementById('detailsStatus');
    const detailsCopyName = document.getElementById('detailsCopyName');
    const detailsEditBtn = document.getElementById('detailsEditBtn');

    /*
    |--------------------------------------------------------------------------
    | Cached rows
    |--------------------------------------------------------------------------
    */

    const rows = table
        ? Array.from(table.querySelectorAll('[data-row]'))
        : [];

    /*
    |--------------------------------------------------------------------------
    | Selected state
    |--------------------------------------------------------------------------
    */

    function getRowCheckboxes() {

        return rows
            .map(function (row) {
                return row.querySelector('.row-check');
            })
            .filter(Boolean);

    }

    function updateSelectionUI() {

        const checkboxes = getRowCheckboxes();

        const selected = checkboxes.filter(function (checkbox) {
            return checkbox.checked;
        });

        const count = selected.length;

        if (selectedCount) {
            selectedCount.textContent = count;
        }

        if (selectionSummary) {
            selectionSummary.classList.toggle(
                'hidden',
                count === 0
            );

            selectionSummary.classList.toggle(
                'inline-flex',
                count > 0
            );
        }

        if (copySelectedBtn) {
            copySelectedBtn.disabled = count === 0;
        }

        if (clearSelectionBtn) {
            clearSelectionBtn.disabled = count === 0;
        }

        if (checkAll) {

            if (!checkboxes.length) {

                checkAll.checked = false;
                checkAll.indeterminate = false;

            } else if (selected.length === checkboxes.length) {

                checkAll.checked = true;
                checkAll.indeterminate = false;

            } else if (selected.length === 0) {

                checkAll.checked = false;
                checkAll.indeterminate = false;

            } else {

                checkAll.checked = false;
                checkAll.indeterminate = true;

            }

        }

    }

    /*
    |--------------------------------------------------------------------------
    | Master checkbox
    |--------------------------------------------------------------------------
    */

    if (checkAll) {

        checkAll.addEventListener('change', function () {

            const shouldCheck = checkAll.checked;

            getRowCheckboxes().forEach(function (checkbox) {
                checkbox.checked = shouldCheck;
            });

            updateSelectionUI();

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Row checkbox changes
    |--------------------------------------------------------------------------
    */

    document.addEventListener('change', function (event) {

        if (
            event.target &&
            event.target.classList &&
            event.target.classList.contains('row-check')
        ) {
            updateSelectionUI();
        }

    });

    /*
    |--------------------------------------------------------------------------
    | Clear selection
    |--------------------------------------------------------------------------
    */

    if (clearSelectionBtn) {

        clearSelectionBtn.addEventListener('click', function () {

            getRowCheckboxes().forEach(function (checkbox) {
                checkbox.checked = false;
            });

            updateSelectionUI();

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Clipboard helper
    |--------------------------------------------------------------------------
    */

    async function copyText(value) {

        const text = String(value ?? '');

        if (!text) {
            return false;
        }

        try {

            if (
                navigator.clipboard &&
                window.isSecureContext
            ) {

                await navigator.clipboard.writeText(text);
                return true;

            }

        } catch (error) {
            // fallback below
        }

        const textarea = document.createElement('textarea');

        textarea.value = text;
        textarea.setAttribute('readonly', '');
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';

        document.body.appendChild(textarea);

        textarea.select();

        let copied = false;

        try {
            copied = document.execCommand('copy');
        } catch (error) {
            copied = false;
        }

        textarea.remove();

        return copied;

    }

    /*
    |--------------------------------------------------------------------------
    | Copy buttons
    |--------------------------------------------------------------------------
    */

    document.addEventListener('click', async function (event) {

        const button = event.target.closest('[data-copy-value]');

        if (!button) {
            return;
        }

        const value = button.dataset.copyValue || '';

        const copied = await copyText(value);

        if (!copied) {
            return;
        }

        const originalHtml = button.innerHTML;

        button.innerHTML =
            '<i class="fa-solid fa-check"></i>';

        button.classList.add('text-brand');

        window.setTimeout(function () {

            button.innerHTML = originalHtml;
            button.classList.remove('text-brand');

        }, 1000);

    });

    /*
    |--------------------------------------------------------------------------
    | Copy selected
    |--------------------------------------------------------------------------
    */

    if (copySelectedBtn) {

        copySelectedBtn.addEventListener('click', async function () {

            const selected = getRowCheckboxes()
                .filter(function (checkbox) {
                    return checkbox.checked;
                })
                .map(function (checkbox) {
                    return {
                        id: checkbox.value,
                        name: checkbox.dataset.name || '',
                        nameEn: checkbox.dataset.nameEn || '',
                    };
                });

            if (!selected.length) {
                return;
            }

            const text = selected
                .map(function (item) {

                    if (item.nameEn) {
                        return (
                            item.id +
                            '\t' +
                            item.name +
                            '\t' +
                            item.nameEn
                        );
                    }

                    return (
                        item.id +
                        '\t' +
                        item.name
                    );

                })
                .join('\n');

            const copied = await copyText(text);

            if (copied) {

                const original = copySelectedBtn.innerHTML;

                copySelectedBtn.innerHTML =
                    '<i class="fa-solid fa-check"></i>';

                window.setTimeout(function () {
                    copySelectedBtn.innerHTML = original;
                }, 1000);

            }

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Modal title helpers
    |--------------------------------------------------------------------------
    */

    function getModalTitleElement() {

        if (!dialog) {
            return null;
        }

        return dialog.querySelector('h3');
    }

    function setModalTitle(title) {

        const titleElement = getModalTitleElement();

        if (titleElement) {
            titleElement.textContent = title;
        }

    }

    /*
    |--------------------------------------------------------------------------
    | Mode
    |--------------------------------------------------------------------------
    */

    function setMode(mode, id) {

        const editing = mode === 'edit';

        form.action = editing
            ? updateUrl.replace('__ID__', id)
            : storeUrl;

        method.disabled = !editing;

        lookupMode.value = mode;
        lookupId.value = id ?? '';

        setModalTitle(
            (editing ? 'تعديل ' : 'إضافة ') + singular
        );

        const modeTitle = document.getElementById('modalModeTitle');
        const modeHint = document.getElementById('modalModeHint');
        const modeIcon = document.getElementById('modalModeIcon');
        const modeBanner = document.getElementById('modalModeBanner');

        if (modeTitle) {
            modeTitle.textContent =
                (editing ? 'تعديل ' : 'إضافة ') + singular;
        }

        if (modeHint) {

            modeHint.textContent = editing
                ? 'تعديل البيانات الحالية مع الحفاظ على السجل.'
                : 'أضف قيمة جديدة إلى قائمة النظام.';

        }

        if (modeIcon) {

            modeIcon.innerHTML = editing
                ? '<i class="fa-solid fa-pen"></i>'
                : '<i class="fa-solid fa-plus"></i>';

        }

        if (modeBanner) {

            modeBanner.className = editing
                ? 'rounded-xl border border-warning/15 bg-warning/[0.03] p-4'
                : 'rounded-xl border border-brand/15 bg-brand/[0.03] p-4';

        }

        if (modeIcon) {

            modeIcon.className = editing
                ? 'flex h-10 w-10 items-center justify-center rounded-lg bg-warning/10 text-warning'
                : 'flex h-10 w-10 items-center justify-center rounded-lg bg-brand/10 text-brand';

        }

        if (lookupSubmitText) {

            lookupSubmitText.textContent =
                editing ? 'حفظ التعديل' : 'حفظ';

        }

        if (editUsageInfo) {
            editUsageInfo.classList.toggle(
                'hidden',
                !editing
            );
        }

    }

    /*
    |--------------------------------------------------------------------------
    | Open add
    |--------------------------------------------------------------------------
    */

    if (addBtn) {

        addBtn.addEventListener('click', function () {

            form.reset();

            setMode('add');

            if (nameField) {
                nameField.value = '';
            }

            if (nameEnField) {
                nameEnField.value = '';
            }

            if (activeField) {
                activeField.checked = true;
            }

            if (lookupId) {
                lookupId.value = '';
            }

            if (editUsageValue) {
                editUsageValue.textContent = '0';
            }

            updateCounters();
            updateDuplicateWarning();

            if (dialog) {
                dialog.showModal();
            }

            window.setTimeout(function () {

                if (nameField) {
                    nameField.focus();
                }

            }, 50);

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Open edit
    |--------------------------------------------------------------------------
    */

    document.addEventListener('click', function (event) {

        const button = event.target.closest('[data-edit]');

        if (!button) {
            return;
        }

        setMode(
            'edit',
            button.dataset.id
        );

        if (nameField) {
            nameField.value =
                button.dataset.name || '';
        }

        if (nameEnField) {
            nameEnField.value =
                button.dataset.nameEn || '';
        }

        if (activeField) {
            activeField.checked =
                button.dataset.active === '1';
        }

        if (editUsageValue) {
            editUsageValue.textContent =
                button.closest('[data-row]')?.dataset.used || '0';
        }

        updateCounters();
        updateDuplicateWarning();

        if (dialog) {
            dialog.showModal();
        }

        window.setTimeout(function () {

            if (nameField) {
                nameField.focus();
                nameField.select();
            }

        }, 50);

    });

    /*
    |--------------------------------------------------------------------------
    | Details modal
    |--------------------------------------------------------------------------
    */

    let currentDetails = {
        id: '',
        name: '',
        nameEn: '',
        used: 0,
        active: 1,
    };

    document.addEventListener('click', function (event) {

        const button = event.target.closest('[data-view]');

        if (!button) {
            return;
        }

        currentDetails = {
            id: button.dataset.id || '',
            name: button.dataset.name || '',
            nameEn: button.dataset.nameEn || '',
            used: parseInt(button.dataset.used || '0', 10) || 0,
            active: button.dataset.active === '1',
        };

        if (detailsName) {
            detailsName.textContent =
                currentDetails.name || '-';
        }

        if (detailsId) {
            detailsId.textContent =
                'ID: ' + (currentDetails.id || '-');
        }

        if (detailsArabicName) {
            detailsArabicName.textContent =
                currentDetails.name || '-';
        }

        if (detailsEnglishName) {
            detailsEnglishName.textContent =
                currentDetails.nameEn || '-';
        }

        if (detailsUsed) {
            detailsUsed.textContent =
                currentDetails.used.toLocaleString();
        }

        if (detailsStatus) {

            detailsStatus.textContent =
                currentDetails.active
                    ? 'نشط'
                    : 'متوقف';

            detailsStatus.className =
                currentDetails.active
                    ? 'mt-1 text-sm font-semibold text-brand'
                    : 'mt-1 text-sm font-semibold text-danger';

        }

        if (detailsDialog) {
            detailsDialog.showModal();
        }

    });

    /*
    |--------------------------------------------------------------------------
    | Details copy
    |--------------------------------------------------------------------------
    */

    if (detailsCopyName) {

        detailsCopyName.addEventListener('click', async function () {

            const copied = await copyText(
                currentDetails.name
            );

            if (!copied) {
                return;
            }

            const original = detailsCopyName.innerHTML;

            detailsCopyName.innerHTML =
                '<i class="fa-solid fa-check text-xs"></i> تم النسخ';

            window.setTimeout(function () {
                detailsCopyName.innerHTML = original;
            }, 1000);

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Details -> Edit
    |--------------------------------------------------------------------------
    */

    if (detailsEditBtn) {

        detailsEditBtn.addEventListener('click', function () {

            if (detailsDialog) {
                detailsDialog.close();
            }

            setMode(
                'edit',
                currentDetails.id
            );

            if (nameField) {
                nameField.value =
                    currentDetails.name;
            }

            if (nameEnField) {
                nameEnField.value =
                    currentDetails.nameEn;
            }

            if (activeField) {
                activeField.checked =
                    currentDetails.active;
            }

            if (editUsageValue) {
                editUsageValue.textContent =
                    currentDetails.used;
            }

            updateCounters();
            updateDuplicateWarning();

            if (dialog) {
                dialog.showModal();
            }

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Character counters
    |--------------------------------------------------------------------------
    */

    function updateCounters() {

        if (nameCounter && nameField) {
            nameCounter.textContent =
                nameField.value.length;
        }

        if (nameEnCounter && nameEnField) {
            nameEnCounter.textContent =
                nameEnField.value.length;
        }

    }

    if (nameField) {
        nameField.addEventListener(
            'input',
            function () {
                updateCounters();
                updateDuplicateWarning();
            }
        );
    }

    if (nameEnField) {
        nameEnField.addEventListener(
            'input',
            function () {
                updateCounters();
                updateDuplicateWarning();
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Duplicate warning
    |--------------------------------------------------------------------------
    | Frontend warning only.
    | The backend should still enforce uniqueness if required.
    */

    function normalize(value) {

        return String(value || '')
            .trim()
            .toLowerCase()
            .replace(/\s+/g, ' ');

    }

    function updateDuplicateWarning() {

        if (!duplicateWarning || !nameField) {
            return;
        }

        const currentName =
            normalize(nameField.value);

        const currentId =
            lookupId ? String(lookupId.value || '') : '';

        if (!currentName) {

            duplicateWarning.classList.add('hidden');
            return;

        }

        const duplicate = rows.some(function (row) {

            const rowId =
                String(row.dataset.id || '');

            if (
                currentId &&
                rowId === currentId
            ) {
                return false;
            }

            return normalize(row.dataset.name) === currentName;

        });

        duplicateWarning.classList.toggle(
            'hidden',
            !duplicate
        );

    }

    /*
    |--------------------------------------------------------------------------
    | Delete protection
    |--------------------------------------------------------------------------
    */

    document.addEventListener('submit', function (event) {

        const deleteForm =
            event.target.closest('[data-delete-form]');

        if (!deleteForm) {
            return;
        }

        const button =
            deleteForm.querySelector('[data-delete]');

        if (!button) {
            return;
        }

        const name =
            button.dataset.name || singular;

        const used =
            parseInt(button.dataset.used || '0', 10) || 0;

        let message =
            'هل أنت متأكد من حذف "' +
            name +
            '"؟';

        if (used > 0) {

            message +=
                '\n\nهذا العنصر مستخدم حالياً في ' +
                used.toLocaleString() +
                ' سجل. تأكد من أن الحذف مسموح قبل المتابعة.';

        }

        if (!window.confirm(message)) {

            event.preventDefault();
            return;

        }

        button.disabled = true;

        button.innerHTML =
            '<i class="fa-solid fa-spinner fa-spin"></i>';

    });

    /*
    |--------------------------------------------------------------------------
    | Form submit protection
    |--------------------------------------------------------------------------
    */

    let submitting = false;

    if (form) {

        form.addEventListener('submit', function (event) {

            if (submitting) {

                event.preventDefault();
                return;

            }

            submitting = true;

            if (lookupSubmit) {

                lookupSubmit.disabled = true;

                lookupSubmit.innerHTML =
                    '<i class="fa-solid fa-spinner fa-spin text-xs"></i> جارٍ الحفظ...';

            }

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Refresh
    |--------------------------------------------------------------------------
    */

    if (refreshPageBtn) {

        refreshPageBtn.addEventListener('click', function () {

            refreshPageBtn.disabled = true;

            refreshPageBtn.innerHTML =
                '<i class="fa-solid fa-spinner fa-spin"></i>';

            window.location.reload();

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Keyboard shortcuts
    |--------------------------------------------------------------------------
    */

    document.addEventListener('keydown', function (event) {

        /*
         * Ctrl + K
         * Focus search.
         */

        if (
            (event.ctrlKey || event.metaKey) &&
            event.key.toLowerCase() === 'k'
        ) {

            const search =
                document.getElementById('search');

            if (search) {

                event.preventDefault();

                search.focus();
                search.select();

            }

        }

        /*
         * Ctrl + N
         * New record.
         */

        if (
            (event.ctrlKey || event.metaKey) &&
            event.key.toLowerCase() === 'n'
        ) {

            event.preventDefault();

            if (addBtn) {
                addBtn.click();
            }

        }

        /*
         * Escape
         */

        if (event.key === 'Escape') {

            const search =
                document.getElementById('search');

            if (
                search &&
                document.activeElement === search &&
                search.value
            ) {

                search.value = '';

            }

        }

    });

    /*
    |--------------------------------------------------------------------------
    | Prevent accidental double click on add
    |--------------------------------------------------------------------------
    */

    let openingModal = false;

    if (addBtn) {

        addBtn.addEventListener('click', function () {

            if (openingModal) {
                return;
            }

            openingModal = true;

            window.setTimeout(function () {
                openingModal = false;
            }, 250);

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Validation feedback
    |--------------------------------------------------------------------------
    */

    function updateNameValidation() {

        const hint =
            document.getElementById('nameValidationHint');

        if (!hint || !nameField) {
            return;
        }

        const value =
            nameField.value.trim();

        if (!value) {

            hint.textContent =
                'أدخل اسماً واضحاً ومميزاً.';

            hint.className =
                'text-[9px] text-dim';

            return;

        }

        if (value.length < 2) {

            hint.textContent =
                'الاسم قصير جداً.';

            hint.className =
                'text-[9px] text-warning';

            return;

        }

        hint.textContent =
            'الاسم يبدو صالحاً.';

        hint.className =
            'text-[9px] text-brand';

    }

    if (nameField) {

        nameField.addEventListener(
            'input',
            updateNameValidation
        );

    }

    /*
    |--------------------------------------------------------------------------
    | Initial state
    |--------------------------------------------------------------------------
    */

    updateSelectionUI();
    updateCounters();
    updateDuplicateWarning();
    updateNameValidation();

    /*
    |--------------------------------------------------------------------------
    | Validation failed:
    | reopen the same modal with old data.
    |--------------------------------------------------------------------------
    */

    @if($errors->any() && old('_mode'))

        setMode(
            @json(old('_mode')),
            @json(old('_id'))
        );

        if (nameField) {
            nameField.value =
                @json(old('name', ''));
        }

        @if($hasEnglish)
            if (nameEnField) {
                nameEnField.value =
                    @json(old('name_en', ''));
            }
        @endif

        @if($hasActive)
            if (activeField) {
                activeField.checked =
                    @json((bool) old('is_active', true));
            }
        @endif

        updateCounters();
        updateDuplicateWarning();
        updateNameValidation();

        if (dialog) {
            dialog.showModal();
        }

    @endif

});
</script>

@endpush