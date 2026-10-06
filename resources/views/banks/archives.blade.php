{{-- resources/views/banks/archives.blade.php --}}
@extends('layouts.app')

@section('title', 'النطاقات المؤرشفة - ' . $bank->name)

@php
    /*
    |--------------------------------------------------------------------------
    | Safe Front-end helpers
    |--------------------------------------------------------------------------
    | كل البيانات المستخدمة هنا موجودة بالفعل في الصفحة الأصلية.
    | الإضافات التالية لا تحتاج أي تعديل في الـ Controller.
    */

    $archiveItems = method_exists($archives, 'items')
        ? collect($archives->items())
        : collect($archives);

    $archiveCount = $archiveItems->count();

    $totalCases = $archiveItems->sum(fn ($archive) => (float) ($archive->cases ?? 0));
    $totalDebt = $archiveItems->sum(fn ($archive) => (float) ($archive->debt ?? 0));
    $totalCollected = $archiveItems->sum(fn ($archive) => (float) ($archive->collected ?? 0));
    $totalRemaining = $archiveItems->sum(fn ($archive) => (float) ($archive->remaining ?? 0));

    $averageRate = $archiveCount > 0
        ? $archiveItems->avg(fn ($archive) => (float) ($archive->rate ?? 0))
        : 0;

    $bestRate = $archiveCount > 0
        ? $archiveItems->max(fn ($archive) => (float) ($archive->rate ?? 0))
        : 0;

    $lowestRate = $archiveCount > 0
        ? $archiveItems->min(fn ($archive) => (float) ($archive->rate ?? 0))
        : 0;

    $latestArchive = $archiveItems->first();

    $archiveData = $archiveItems->map(function ($archive) use ($bank) {
    return [
        'id'        => $archive->id,
        'label'     => $archive->label ?? '',
        'cases'     => $archive->cases ?? 0,
        'debt'      => $archive->debt ?? 0,
        'collected' => $archive->collected ?? 0,
        'remaining' => $archive->remaining ?? 0,
        'rate'      => $archive->rate ?? 0,
        'by'        => $archive->by ?? '',
        'at'        => $archive->at ?? '',
        'file'      => $archive->file ?? '',
        'url'       => route('banks.archives.show', [$bank->id, $archive->id]),
    ];
})->values();

    $formatMoney = function ($value) {
        return number_format((float) $value);
    };

    $formatNumber = function ($value) {
        return number_format((float) $value);
    };

    $safeRate = function ($value) {
        return min(100, max(0, (float) $value));
    };
@endphp

@section('content')

    {{-- =========================================================
         PAGE HEADER
         ========================================================= --}}
    <x-bank-header
        :bank="$bank"
        title="النطاقات المؤرشفة"
        subtitle="استعراض الأشهر المغلقة (للقراءة فقط)"
    />

    {{-- =========================================================
         ORIGINAL STATISTICS
         ========================================================= --}}
    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
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

    {{-- =========================================================
         ADDITIONAL OVERVIEW
         ========================================================= --}}
    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="card">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <div class="text-[11px] text-dim">الأرشيفات الظاهرة</div>
                    <div class="mt-1 text-xl font-bold text-fg" id="archive-visible-count">
                        {{ $archiveCount }}
                    </div>
                    <div class="mt-1 text-[10px] text-muted">
                        من النتائج الحالية
                    </div>
                </div>

                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-info/10 text-info">
                    <i class="fa-solid fa-box-archive"></i>
                </span>
            </div>
        </div>

        <div class="card">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <div class="text-[11px] text-dim">إجمالي الحالات</div>
                    <div class="mt-1 text-xl font-bold text-fg">
                        {{ $formatNumber($totalCases) }}
                    </div>
                    <div class="mt-1 text-[10px] text-muted">
                        الحالات الموجودة في الأرشيف الظاهر
                    </div>
                </div>

                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan/10 text-cyan">
                    <i class="fa-solid fa-users"></i>
                </span>
            </div>
        </div>

        <div class="card">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <div class="text-[11px] text-dim">إجمالي المديونية</div>
                    <div class="mt-1 text-xl font-bold text-fg">
                        EGP {{ $formatMoney($totalDebt) }}
                    </div>
                    <div class="mt-1 text-[10px] text-muted">
                        إجمالي النطاقات الظاهرة
                    </div>
                </div>

                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-warning/10 text-warning">
                    <i class="fa-solid fa-money-bill-trend-up"></i>
                </span>
            </div>
        </div>

        <div class="card">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <div class="text-[11px] text-dim">متوسط التحصيل</div>
                    <div class="mt-1 text-xl font-bold text-brand">
                        {{ number_format($averageRate, 1) }}%
                    </div>
                    <div class="mt-1 text-[10px] text-muted">
                        أفضل نسبة: {{ number_format($bestRate, 1) }}%
                    </div>
                </div>

                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand/10 text-brand">
                    <i class="fa-solid fa-chart-line"></i>
                </span>
            </div>
        </div>

    </section>

    {{-- =========================================================
         ORIGINAL FILTER
         ========================================================= --}}
    <form
        method="GET"
        action="{{ route('banks.archives.index', $bank->id) }}"
        class="card filter-bar"
        id="archiveFilterForm"
    >
        <div class="w-44">
            <label for="year" class="form-label">السنة</label>

            <select
                id="year"
                name="year"
                class="form-input"
                onchange="this.form.submit()"
            >
                <option value="">كل السنوات</option>

                @foreach($years as $y)
                    <option
                        value="{{ $y }}"
                        @selected($year === (string) $y)
                    >
                        {{ $y }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Front-end search only --}}
        <div class="w-full sm:w-72">
            <label for="archiveSearch" class="form-label">
                بحث داخل الأرشيف
            </label>

            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute right-3 top-1/2 -translate-y-1/2 text-dim"></i>

                <input
                    id="archiveSearch"
                    type="search"
                    class="form-input pr-9"
                    placeholder="ابحث بالشهر أو الملف أو الموظف..."
                    autocomplete="off"
                >
            </div>
        </div>

        <div class="flex items-end gap-2">

            <button
                type="button"
                id="archiveClearSearch"
                class="btn btn-secondary"
                title="مسح البحث"
            >
                <i class="fa-solid fa-xmark"></i>
            </button>

            <a
                href="{{ route('banks.archives.index', $bank->id) }}"
                class="btn btn-secondary"
                title="إعادة ضبط"
            >
                <i class="fa-solid fa-rotate-right"></i>
            </a>

        </div>
    </form>

    {{-- =========================================================
         ACTIVE FILTER / INFORMATION BAR
         ========================================================= --}}
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">

        <div class="flex flex-wrap items-center gap-2">

            @if($year !== '')
                <span class="badge badge-outline-info">
                    <i class="fa-solid fa-calendar-days ml-1 text-[9px]"></i>
                    {{ $year }}
                </span>
            @endif

            <span class="badge badge-neutral">
                <i class="fa-solid fa-lock ml-1 text-[9px]"></i>
                للقراءة فقط
            </span>

            <span class="text-xs text-muted">
                عدد النتائج:
                <b id="archive-result-count" class="text-fg">{{ $archiveCount }}</b>
            </span>

        </div>

        <div class="flex items-center gap-2 text-[11px] text-dim">
            <i class="fa-solid fa-circle-info"></i>
            لا يمكن تعديل الأرشيفات المغلقة
        </div>

    </div>

    {{-- =========================================================
         ARCHIVE TOOLBAR
         ========================================================= --}}
    <div class="mb-3 flex flex-wrap items-center justify-between gap-3">

        <div class="flex items-center gap-2">

            <button
                type="button"
                id="archiveCheckAll"
                class="icon-btn icon-btn-info"
                title="تحديد الكل"
                aria-label="تحديد الكل"
            >
                <i class="fa-solid fa-check-double"></i>
            </button>

            <button
                type="button"
                id="archiveClearSelection"
                class="icon-btn icon-btn-warning"
                title="إلغاء التحديد"
                aria-label="إلغاء التحديد"
            >
                <i class="fa-solid fa-square-xmark"></i>
            </button>

            <button
                type="button"
                id="archiveCopySelected"
                class="icon-btn icon-btn-cyan"
                title="نسخ الأرشيفات المحددة"
                aria-label="نسخ الأرشيفات المحددة"
            >
                <i class="fa-solid fa-copy"></i>
            </button>

            <span class="mr-2 text-xs text-muted">
                المحدد:
                <b id="archive-selected-count" class="text-fg">0</b>
            </span>

        </div>

        <div class="flex items-center gap-2">

            <button
                type="button"
                id="archiveExport"
                class="btn btn-outline-success btn-sm"
                title="تصدير الأرشيفات الظاهرة"
            >
                <i class="fa-solid fa-file-excel text-xs"></i>
                تصدير
            </button>

            <button
                type="button"
                id="archivePrint"
                class="btn btn-outline-info btn-sm"
                title="طباعة الأرشيفات"
            >
                <i class="fa-solid fa-print text-xs"></i>
                طباعة
            </button>

        </div>

    </div>

    {{-- =========================================================
         ORIGINAL TABLE
         ========================================================= --}}
    <div class="table-wrap">
        <table class="data-table whitespace-nowrap" id="archivesTable">

            <thead>
                <tr>
                    <th class="w-10">
                        <input
                            id="archiveMasterCheck"
                            type="checkbox"
                            class="accent-brand"
                            aria-label="تحديد كل الأرشيفات"
                        >
                    </th>

                    <th>الشهر</th>
                    <th>الحالات</th>
                    <th>المديونية</th>
                    <th>المحصل</th>
                    <th>المتبقي</th>
                    <th class="w-44">نسبة التحصيل</th>
                    <th>أرشفه</th>
                    <th>الملف</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>

            <tbody>

                @forelse($archives as $archive)

                    @php
                        $rate = $safeRate($archive->rate ?? 0);

                        $barClass = $rate >= 50
                            ? 'bg-brand'
                            : ($rate >= 30 ? 'bg-warning' : 'bg-danger');

                        $rateLabel = $rate >= 70
                            ? 'ممتاز'
                            : ($rate >= 50
                                ? 'جيد'
                                : ($rate >= 30 ? 'متوسط' : 'منخفض'));
                    @endphp

                    <tr
                        data-archive-row
                        data-archive-id="{{ $archive->id }}"
                        data-archive-search="{{ strtolower(($archive->label ?? '') . ' ' . ($archive->file ?? '') . ' ' . ($archive->by ?? '') . ' ' . ($archive->at ?? '')) }}"
                    >

                        {{-- Selection --}}
                        <td>
                            <input
                                type="checkbox"
                                class="archive-row-check accent-brand"
                                value="{{ $archive->id }}"
                                aria-label="تحديد {{ $archive->label }}"
                            >
                        </td>

                        {{-- Month --}}
                        <td>
                            <div class="flex items-center gap-2">

                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-info/10 text-info">
                                    <i class="fa-solid fa-calendar-days text-xs"></i>
                                </span>

                                <div>
                                    <span class="font-bold text-fg">
                                        {{ $archive->label }}
                                    </span>

                                    <span class="badge badge-neutral mr-2">
                                        <i class="fa-solid fa-lock ml-1 text-[9px]"></i>
                                        مؤرشف
                                    </span>
                                </div>

                            </div>
                        </td>

                        {{-- Cases --}}
                        <td>
                            <span class="font-semibold text-fg">
                                {{ number_format($archive->cases) }}
                            </span>
                        </td>

                        {{-- Debt --}}
                        <td>
                            <span class="text-fg">
                                EGP {{ number_format($archive->debt) }}
                            </span>
                        </td>

                        {{-- Collected --}}
                        <td>
                            <span class="font-semibold text-brand">
                                EGP {{ number_format($archive->collected) }}
                            </span>
                        </td>

                        {{-- Remaining --}}
                        <td>
                            <span class="font-semibold {{ (float) $archive->remaining > 0 ? 'text-warning' : 'text-brand' }}">
                                EGP {{ number_format($archive->remaining) }}
                            </span>
                        </td>

                        {{-- Rate --}}
                        <td>

                            <div class="mb-1 flex items-center justify-between gap-2 text-[11px] text-muted">
                                <span>
                                    {{ number_format($rate, 1) }}%
                                </span>

                                <span class="badge badge-neutral">
                                    {{ $rateLabel }}
                                </span>
                            </div>

                            <div class="progress">
                                <div
                                    class="h-full rounded-full {{ $barClass }}"
                                    style="width: {{ $rate }}%"
                                ></div>
                            </div>

                        </td>

                        {{-- Archived By --}}
                        <td>
                            <span class="text-fg">
                                {{ $archive->by }}
                            </span>

                            <br>

                            <span class="text-[11px] text-dim">
                                {{ $archive->at }}
                            </span>
                        </td>

                        {{-- File --}}
                        <td>

                            <button
                                type="button"
                                class="font-mono text-xs text-info transition hover:text-brand"
                                data-copy-file="{{ $archive->file }}"
                                title="نسخ اسم الملف"
                            >
                                {{ $archive->file }}
                            </button>

                        </td>

                        {{-- Actions --}}
                        <td>

                            <div class="flex items-center gap-2">

                                {{-- Existing route preserved --}}
                                <a
                                    href="{{ route('banks.archives.show', [$bank->id, $archive->id]) }}"
                                    class="btn btn-outline-info btn-sm"
                                >
                                    <i class="fa-solid fa-eye text-xs"></i>
                                    عرض
                                </a>

                                {{-- TODO: real download route --}}
                                <a
                                    href="#"
                                    class="btn btn-outline-success btn-sm"
                                    title="تحميل Excel"
                                    data-export-one="{{ $archive->id }}"
                                >
                                    <i class="fa-solid fa-file-excel text-xs"></i>
                                </a>

                                <button
                                    type="button"
                                    class="icon-btn icon-btn-warning"
                                    title="تفاصيل سريعة"
                                    data-archive-details="{{ $archive->id }}"
                                >
                                    <i class="fa-solid fa-circle-info"></i>
                                </button>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="10">
                            <x-empty-state
                                icon="fa-box-archive"
                                text="لا توجد نطاقات مؤرشفة"
                            />
                        </td>
                    </tr>

                @endforelse

                {{-- Front-end search empty state --}}
                <tr id="archiveSearchEmpty" class="hidden">
                    <td colspan="10" class="py-10 text-center">

                        <div class="flex flex-col items-center justify-center">

                            <span class="mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-info/10 text-info">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>

                            <div class="font-semibold text-fg">
                                لا توجد نتائج مطابقة
                            </div>

                            <div class="mt-1 text-xs text-muted">
                                جرّب البحث باسم الشهر أو اسم الملف أو الموظف.
                            </div>

                        </div>

                    </td>
                </tr>

            </tbody>

        </table>
    </div>

    {{-- =========================================================
         FOOTER SUMMARY
         ========================================================= --}}
    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-line pt-4">

        <div class="flex flex-wrap items-center gap-3 text-xs text-muted">

            <span>
                <i class="fa-solid fa-box-archive ml-1 text-info"></i>
                أرشيفات:
                <b class="text-fg">{{ $archiveCount }}</b>
            </span>

            <span>
                <i class="fa-solid fa-chart-line ml-1 text-brand"></i>
                متوسط التحصيل:
                <b class="text-brand">{{ number_format($averageRate, 1) }}%</b>
            </span>

            <span>
                <i class="fa-solid fa-arrow-trend-down ml-1 text-warning"></i>
                أقل نسبة:
                <b class="text-warning">{{ number_format($lowestRate, 1) }}%</b>
            </span>

        </div>

        <div class="text-[11px] text-dim">
            <i class="fa-solid fa-lock ml-1"></i>
            البيانات المؤرشفة للقراءة فقط
        </div>

    </div>

    {{-- =========================================================
         QUICK DETAILS MODAL
         ========================================================= --}}
    <x-modal id="archiveDetailsModal" title="تفاصيل الأرشيف" width="42rem">

        <div class="space-y-5">

            <div class="flex items-center gap-3 rounded-xl border border-line bg-surface p-4">

                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-info/10 text-info">
                    <i class="fa-solid fa-box-archive"></i>
                </span>

                <div class="min-w-0">

                    <div
                        id="archive-modal-label"
                        class="truncate text-lg font-bold text-fg"
                    >
                        -
                    </div>

                    <div
                        id="archive-modal-file"
                        class="mt-1 truncate font-mono text-xs text-muted"
                        dir="ltr"
                    >
                        -
                    </div>

                </div>

                <span class="mr-auto badge badge-neutral">
                    <i class="fa-solid fa-lock ml-1 text-[9px]"></i>
                    للقراءة فقط
                </span>

            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">

                <div class="rounded-xl border border-line bg-surface p-3 text-center">
                    <div class="text-[10px] text-dim">الحالات</div>
                    <div id="archive-modal-cases" class="mt-1 font-bold text-fg">-</div>
                </div>

                <div class="rounded-xl border border-line bg-surface p-3 text-center">
                    <div class="text-[10px] text-dim">المديونية</div>
                    <div id="archive-modal-debt" class="mt-1 font-bold text-fg">-</div>
                </div>

                <div class="rounded-xl border border-line bg-surface p-3 text-center">
                    <div class="text-[10px] text-dim">المحصل</div>
                    <div id="archive-modal-collected" class="mt-1 font-bold text-brand">-</div>
                </div>

                <div class="rounded-xl border border-line bg-surface p-3 text-center">
                    <div class="text-[10px] text-dim">المتبقي</div>
                    <div id="archive-modal-remaining" class="mt-1 font-bold text-warning">-</div>
                </div>

            </div>

            <div class="rounded-xl border border-line bg-surface p-4">

                <div class="mb-2 flex items-center justify-between gap-3">

                    <span class="text-xs text-muted">
                        نسبة التحصيل
                    </span>

                    <span
                        id="archive-modal-rate"
                        class="font-bold text-brand"
                    >
                        -
                    </span>

                </div>

                <div class="progress">
                    <div
                        id="archive-modal-progress"
                        class="h-full rounded-full bg-brand"
                        style="width: 0%"
                    ></div>
                </div>

            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

                <div class="rounded-xl border border-line bg-surface p-4">

                    <div class="mb-2 flex items-center gap-2 text-xs text-muted">
                        <i class="fa-solid fa-user text-info"></i>
                        تمت الأرشفة بواسطة
                    </div>

                    <div
                        id="archive-modal-by"
                        class="font-semibold text-fg"
                    >
                        -
                    </div>

                </div>

                <div class="rounded-xl border border-line bg-surface p-4">

                    <div class="mb-2 flex items-center gap-2 text-xs text-muted">
                        <i class="fa-solid fa-clock text-warning"></i>
                        وقت الأرشفة
                    </div>

                    <div
                        id="archive-modal-at"
                        class="font-semibold text-fg"
                    >
                        -
                    </div>

                </div>

            </div>

            <div class="flex flex-wrap gap-2">

                <button
                    type="button"
                    id="archiveModalCopy"
                    class="btn btn-outline-info btn-sm"
                >
                    <i class="fa-solid fa-copy text-xs"></i>
                    نسخ البيانات
                </button>

                <button
                    type="button"
                    id="archiveModalPrint"
                    class="btn btn-outline-success btn-sm"
                >
                    <i class="fa-solid fa-print text-xs"></i>
                    طباعة
                </button>

                <button
                    type="button"
                    id="archiveModalOpen"
                    class="btn btn-primary btn-sm"
                >
                    <i class="fa-solid fa-eye text-xs"></i>
                    فتح الأرشيف
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
(function () {
    'use strict';

    /*
    |--------------------------------------------------------------------------
    | Elements
    |--------------------------------------------------------------------------
    */

    const searchInput = document.getElementById('archiveSearch');
    const clearSearch = document.getElementById('archiveClearSearch');

    const masterCheck = document.getElementById('archiveMasterCheck');
    const checkAllButton = document.getElementById('archiveCheckAll');
    const clearSelectionButton = document.getElementById('archiveClearSelection');
    const copySelectedButton = document.getElementById('archiveCopySelected');

    const selectedCounter = document.getElementById('archive-selected-count');
    const resultCounter = document.getElementById('archive-result-count');
    const visibleCounter = document.getElementById('archive-visible-count');

    const searchEmpty = document.getElementById('archiveSearchEmpty');

    const exportButton = document.getElementById('archiveExport');
    const printButton = document.getElementById('archivePrint');

    const detailsModal = document.getElementById('archiveDetailsModal');

    const modalLabel = document.getElementById('archive-modal-label');
    const modalFile = document.getElementById('archive-modal-file');
    const modalCases = document.getElementById('archive-modal-cases');
    const modalDebt = document.getElementById('archive-modal-debt');
    const modalCollected = document.getElementById('archive-modal-collected');
    const modalRemaining = document.getElementById('archive-modal-remaining');
    const modalRate = document.getElementById('archive-modal-rate');
    const modalProgress = document.getElementById('archive-modal-progress');
    const modalBy = document.getElementById('archive-modal-by');
    const modalAt = document.getElementById('archive-modal-at');

    const modalCopy = document.getElementById('archiveModalCopy');
    const modalPrint = document.getElementById('archiveModalPrint');
    const modalOpen = document.getElementById('archiveModalOpen');

    /*
    |--------------------------------------------------------------------------
    | Cache rows once
    |--------------------------------------------------------------------------
    */

    const rows = Array.from(
        document.querySelectorAll('[data-archive-row]')
    );

    /*
    |--------------------------------------------------------------------------
    | Archive data
    |--------------------------------------------------------------------------
    */

    const archives = {{ Js::from($archiveData) }};

    let activeArchive = null;

    /*
    |--------------------------------------------------------------------------
    | Toast
    |--------------------------------------------------------------------------
    */

    function toast(message) {
        const existing = document.getElementById('archiveToast');

        if (existing) {
            existing.remove();
        }

        const element = document.createElement('div');

        element.id = 'archiveToast';

        element.className =
            'fixed bottom-6 left-6 z-[100] rounded-xl border border-line bg-surface px-4 py-3 text-sm text-fg shadow-2xl shadow-black/40';

        element.innerHTML =
            '<i class="fa-solid fa-circle-check ml-2 text-brand"></i>' +
            message;

        document.body.appendChild(element);

        setTimeout(() => {
            element.remove();
        }, 2200);
    }

    /*
    |--------------------------------------------------------------------------
    | Clipboard
    |--------------------------------------------------------------------------
    */

    async function copyText(value, message = 'تم النسخ') {
        if (!value) {
            toast('لا توجد بيانات للنسخ');
            return;
        }

        try {
            await navigator.clipboard.writeText(value);
        } catch (error) {
            const textarea = document.createElement('textarea');

            textarea.value = value;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';

            document.body.appendChild(textarea);

            textarea.focus();
            textarea.select();

            document.execCommand('copy');
            textarea.remove();
        }

        toast(message);
    }

    /*
    |--------------------------------------------------------------------------
    | Selection
    |--------------------------------------------------------------------------
    */

    function getChecks() {
        return rows
            .map(row => row.querySelector('.archive-row-check'))
            .filter(Boolean);
    }

    function getSelected() {
        return getChecks().filter(check => check.checked);
    }

    function refreshSelection() {
        const checks = getChecks();
        const selected = checks.filter(check => check.checked);

        selectedCounter.textContent = selected.length;

        if (!checks.length) {
            masterCheck.checked = false;
            masterCheck.indeterminate = false;
            return;
        }

        if (selected.length === checks.length) {
            masterCheck.checked = true;
            masterCheck.indeterminate = false;
        } else if (selected.length === 0) {
            masterCheck.checked = false;
            masterCheck.indeterminate = false;
        } else {
            masterCheck.checked = false;
            masterCheck.indeterminate = true;
        }
    }

    masterCheck?.addEventListener('change', function () {
        getChecks().forEach(check => {
            check.checked = masterCheck.checked;
        });

        refreshSelection();
    });

    checkAllButton?.addEventListener('click', function () {
        getChecks().forEach(check => {
            check.checked = true;
        });

        refreshSelection();
    });

    clearSelectionButton?.addEventListener('click', function () {
        getChecks().forEach(check => {
            check.checked = false;
        });

        refreshSelection();
    });

    document.addEventListener('change', function (event) {
        if (event.target.matches('.archive-row-check')) {
            refreshSelection();
        }
    });

    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */

    function filterRows() {
        const query = (searchInput?.value || '').trim().toLowerCase();

        let visible = 0;

        rows.forEach(row => {
            const haystack = row.dataset.archiveSearch || '';
            const matched = !query || haystack.includes(query);

            row.classList.toggle('hidden', !matched);

            if (matched) {
                visible++;
            }
        });

        resultCounter.textContent = visible;
        visibleCounter.textContent = visible;

        if (searchEmpty) {
            searchEmpty.classList.toggle(
                'hidden',
                visible !== 0 || rows.length === 0
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Selection remains independent from search.
        |--------------------------------------------------------------------------
        | We don't silently uncheck hidden rows because the user may want
        | to select/search in multiple steps.
        |--------------------------------------------------------------------------
        */

        refreshSelection();
    }

    searchInput?.addEventListener('input', filterRows);

    clearSearch?.addEventListener('click', function () {
        if (!searchInput) return;

        searchInput.value = '';
        filterRows();
        searchInput.focus();
    });

    /*
    |--------------------------------------------------------------------------
    | Copy selected archives
    |--------------------------------------------------------------------------
    */

    copySelectedButton?.addEventListener('click', async function () {

        const selectedIds = getSelected().map(
            checkbox => String(checkbox.value)
        );

        if (!selectedIds.length) {
            toast('اختر أرشيفاً واحداً على الأقل');
            return;
        }

        const selectedArchives = archives.filter(archive =>
            selectedIds.includes(String(archive.id))
        );

        const text = selectedArchives.map(archive =>
            [
                `الشهر: ${archive.label}`,
                `الحالات: ${archive.cases}`,
                `المديونية: EGP ${Number(archive.debt || 0).toLocaleString()}`,
                `المحصل: EGP ${Number(archive.collected || 0).toLocaleString()}`,
                `المتبقي: EGP ${Number(archive.remaining || 0).toLocaleString()}`,
                `نسبة التحصيل: ${archive.rate}%`,
                `تمت الأرشفة بواسطة: ${archive.by}`,
                `وقت الأرشفة: ${archive.at}`,
                `الملف: ${archive.file}`
            ].join(' | ')
        ).join('\n');

        await copyText(
            text,
            `تم نسخ ${selectedArchives.length} أرشيف`
        );
    });

    /*
    |--------------------------------------------------------------------------
    | Copy file name
    |--------------------------------------------------------------------------
    */

    document.addEventListener('click', function (event) {

        const button = event.target.closest('[data-copy-file]');

        if (!button) {
            return;
        }

        event.preventDefault();

        copyText(
            button.dataset.copyFile,
            'تم نسخ اسم الملف'
        );
    });

    /*
    |--------------------------------------------------------------------------
    | Find archive
    |--------------------------------------------------------------------------
    */

    function findArchive(id) {
        return archives.find(
            archive => String(archive.id) === String(id)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Open details
    |--------------------------------------------------------------------------
    */

    function openDetails(id) {

        const archive = findArchive(id);

        if (!archive || !detailsModal) {
            return;
        }

        activeArchive = archive;

        modalLabel.textContent = archive.label || '-';
        modalFile.textContent = archive.file || '-';

        modalCases.textContent =
            Number(archive.cases || 0).toLocaleString();

        modalDebt.textContent =
            'EGP ' + Number(archive.debt || 0).toLocaleString();

        modalCollected.textContent =
            'EGP ' + Number(archive.collected || 0).toLocaleString();

        modalRemaining.textContent =
            'EGP ' + Number(archive.remaining || 0).toLocaleString();

        const rate = Math.min(
            100,
            Math.max(0, Number(archive.rate || 0))
        );

        modalRate.textContent = rate.toFixed(1) + '%';
        modalProgress.style.width = rate + '%';

        modalBy.textContent = archive.by || '-';
        modalAt.textContent = archive.at || '-';

        detailsModal.showModal();
    }

    document.addEventListener('click', function (event) {

        const button = event.target.closest('[data-archive-details]');

        if (!button) {
            return;
        }

        openDetails(button.dataset.archiveDetails);
    });

    /*
    |--------------------------------------------------------------------------
    | Modal copy
    |--------------------------------------------------------------------------
    */

    modalCopy?.addEventListener('click', function () {

        if (!activeArchive) {
            return;
        }

        const text = [
            `البنك: {{ addslashes($bank->name) }}`,
            `الشهر: ${activeArchive.label}`,
            `الحالات: ${activeArchive.cases}`,
            `المديونية: EGP ${Number(activeArchive.debt || 0).toLocaleString()}`,
            `المحصل: EGP ${Number(activeArchive.collected || 0).toLocaleString()}`,
            `المتبقي: EGP ${Number(activeArchive.remaining || 0).toLocaleString()}`,
            `نسبة التحصيل: ${activeArchive.rate}%`,
            `تمت الأرشفة بواسطة: ${activeArchive.by}`,
            `وقت الأرشفة: ${activeArchive.at}`,
            `الملف: ${activeArchive.file}`
        ].join('\n');

        copyText(text, 'تم نسخ تفاصيل الأرشيف');
    });

    /*
    |--------------------------------------------------------------------------
    | Open archive
    |--------------------------------------------------------------------------
    */

    modalOpen?.addEventListener('click', function () {

        if (!activeArchive) {
            return;
        }

        window.location.href = activeArchive.url;
    });

    /*
    |--------------------------------------------------------------------------
    | Print one archive
    |--------------------------------------------------------------------------
    */

    function printArchive(archive) {

        if (!archive) {
            return;
        }

        const popup = window.open('', '_blank', 'width=900,height=700');

        if (!popup) {
            toast('اسمح بالنوافذ المنبثقة للطباعة');
            return;
        }

        const rate = Number(archive.rate || 0);

        popup.document.write(`
            <!doctype html>
            <html lang="ar" dir="rtl">
            <head>
                <meta charset="utf-8">
                <title>أرشيف ${escapeHtml(archive.label)}</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        padding: 40px;
                        color: #111;
                        direction: rtl;
                    }

                    h1 {
                        margin-bottom: 4px;
                    }

                    .muted {
                        color: #666;
                        margin-bottom: 30px;
                    }

                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 25px;
                    }

                    th,
                    td {
                        border: 1px solid #ddd;
                        padding: 12px;
                        text-align: right;
                    }

                    th {
                        background: #f3f3f3;
                    }

                    .brand {
                        font-weight: bold;
                    }

                    .footer {
                        margin-top: 30px;
                        color: #666;
                        font-size: 12px;
                    }
                </style>
            </head>

            <body>

                <h1>{{ addslashes($bank->name) }}</h1>

                <div class="muted">
                    تقرير أرشيفي — ${escapeHtml(archive.label)}
                </div>

                <table>
                    <tr>
                        <th>الشهر</th>
                        <td>${escapeHtml(archive.label)}</td>
                    </tr>

                    <tr>
                        <th>عدد الحالات</th>
                        <td>${Number(archive.cases || 0).toLocaleString()}</td>
                    </tr>

                    <tr>
                        <th>المديونية</th>
                        <td>EGP ${Number(archive.debt || 0).toLocaleString()}</td>
                    </tr>

                    <tr>
                        <th>المحصل</th>
                        <td class="brand">
                            EGP ${Number(archive.collected || 0).toLocaleString()}
                        </td>
                    </tr>

                    <tr>
                        <th>المتبقي</th>
                        <td>
                            EGP ${Number(archive.remaining || 0).toLocaleString()}
                        </td>
                    </tr>

                    <tr>
                        <th>نسبة التحصيل</th>
                        <td>${rate.toFixed(1)}%</td>
                    </tr>

                    <tr>
                        <th>تمت الأرشفة بواسطة</th>
                        <td>${escapeHtml(archive.by || '-')}</td>
                    </tr>

                    <tr>
                        <th>وقت الأرشفة</th>
                        <td>${escapeHtml(archive.at || '-')}</td>
                    </tr>

                    <tr>
                        <th>الملف</th>
                        <td>${escapeHtml(archive.file || '-')}</td>
                    </tr>
                </table>

                <div class="footer">
                    هذا التقرير للقراءة فقط.
                </div>

            </body>
            </html>
        `);

        popup.document.close();

        setTimeout(() => {
            popup.focus();
            popup.print();
        }, 250);
    }

    /*
    |--------------------------------------------------------------------------
    | Escape HTML
    |--------------------------------------------------------------------------
    */

    function escapeHtml(value) {

        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    /*
    |--------------------------------------------------------------------------
    | Modal print
    |--------------------------------------------------------------------------
    */

    modalPrint?.addEventListener('click', function () {
        printArchive(activeArchive);
    });

    /*
    |--------------------------------------------------------------------------
    | Print all visible archives
    |--------------------------------------------------------------------------
    */

    printButton?.addEventListener('click', function () {

        const visibleArchives = archives.filter(archive => {

            const row = document.querySelector(
                `[data-archive-id="${CSS.escape(String(archive.id))}"]`
            );

            return row && !row.classList.contains('hidden');
        });

        if (!visibleArchives.length) {
            toast('لا توجد أرشيفات للطباعة');
            return;
        }

        const popup = window.open('', '_blank', 'width=1100,height=800');

        if (!popup) {
            toast('اسمح بالنوافذ المنبثقة للطباعة');
            return;
        }

        const rowsHtml = visibleArchives.map(archive => `
            <tr>
                <td>${escapeHtml(archive.label)}</td>
                <td>${Number(archive.cases || 0).toLocaleString()}</td>
                <td>EGP ${Number(archive.debt || 0).toLocaleString()}</td>
                <td>EGP ${Number(archive.collected || 0).toLocaleString()}</td>
                <td>EGP ${Number(archive.remaining || 0).toLocaleString()}</td>
                <td>${Number(archive.rate || 0).toFixed(1)}%</td>
                <td>${escapeHtml(archive.by || '-')}</td>
                <td>${escapeHtml(archive.at || '-')}</td>
            </tr>
        `).join('');

        popup.document.write(`
            <!doctype html>
            <html lang="ar" dir="rtl">

            <head>
                <meta charset="utf-8">

                <title>
                    الأرشيفات - {{ addslashes($bank->name) }}
                </title>

                <style>

                    body {
                        font-family: Arial, sans-serif;
                        padding: 30px;
                        color: #111;
                    }

                    h1 {
                        margin-bottom: 5px;
                    }

                    p {
                        color: #666;
                    }

                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 25px;
                    }

                    th,
                    td {
                        border: 1px solid #ddd;
                        padding: 9px;
                        text-align: right;
                    }

                    th {
                        background: #f3f3f3;
                    }

                </style>
            </head>

            <body>

                <h1>
                    {{ addslashes($bank->name) }}
                </h1>

                <p>
                    تقرير النطاقات المؤرشفة
                </p>

                <table>

                    <thead>
                        <tr>
                            <th>الشهر</th>
                            <th>الحالات</th>
                            <th>المديونية</th>
                            <th>المحصل</th>
                            <th>المتبقي</th>
                            <th>التحصيل</th>
                            <th>الأرشفة بواسطة</th>
                            <th>وقت الأرشفة</th>
                        </tr>
                    </thead>

                    <tbody>
                        ${rowsHtml}
                    </tbody>

                </table>

            </body>

            </html>
        `);

        popup.document.close();

        setTimeout(() => {
            popup.focus();
            popup.print();
        }, 250);
    });

    /*
    |--------------------------------------------------------------------------
    | Export CSV
    |--------------------------------------------------------------------------
    */

    exportButton?.addEventListener('click', function () {

        const visibleArchives = archives.filter(archive => {

            const row = document.querySelector(
                `[data-archive-id="${CSS.escape(String(archive.id))}"]`
            );

            return row && !row.classList.contains('hidden');
        });

        if (!visibleArchives.length) {
            toast('لا توجد بيانات للتصدير');
            return;
        }

        const header = [
            'البنك',
            'الشهر',
            'الحالات',
            'المديونية',
            'المحصل',
            'المتبقي',
            'نسبة التحصيل',
            'تمت الأرشفة بواسطة',
            'وقت الأرشفة',
            'الملف'
        ];

        const rowsData = visibleArchives.map(archive => [
            '{{ addslashes($bank->name) }}',
            archive.label,
            archive.cases,
            archive.debt,
            archive.collected,
            archive.remaining,
            archive.rate,
            archive.by,
            archive.at,
            archive.file
        ]);

        const csvRows = [
            header,
            ...rowsData
        ];

        const csv = csvRows
            .map(row =>
                row.map(value => {
                    const safeValue = String(value ?? '')
                        .replaceAll('"', '""');

                    return `"${safeValue}"`;
                }).join(',')
            )
            .join('\r\n');

        const blob = new Blob(
            ['\uFEFF' + csv],
            {
                type: 'text/csv;charset=utf-8;'
            }
        );

        const url = URL.createObjectURL(blob);

        const link = document.createElement('a');

        link.href = url;
        link.download =
            'archives-{{ $bank->id }}-{{ $year !== '' ? $year : 'all' }}.csv';

        document.body.appendChild(link);

        link.click();

        link.remove();

        URL.revokeObjectURL(url);

        toast(`تم تصدير ${visibleArchives.length} أرشيف`);
    });

    /*
    |--------------------------------------------------------------------------
    | Existing Excel buttons
    |--------------------------------------------------------------------------
    |
    | No real download route is added.
    | Instead, the existing # buttons export the individual archive data.
    |--------------------------------------------------------------------------
    */

    document.addEventListener('click', function (event) {

        const button = event.target.closest('[data-export-one]');

        if (!button) {
            return;
        }

        event.preventDefault();

        const archive = findArchive(button.dataset.exportOne);

        if (!archive) {
            return;
        }

        const rowsData = [
            [
                'البنك',
                'الشهر',
                'الحالات',
                'المديونية',
                'المحصل',
                'المتبقي',
                'نسبة التحصيل',
                'تمت الأرشفة بواسطة',
                'وقت الأرشفة',
                'الملف'
            ],
            [
                '{{ addslashes($bank->name) }}',
                archive.label,
                archive.cases,
                archive.debt,
                archive.collected,
                archive.remaining,
                archive.rate,
                archive.by,
                archive.at,
                archive.file
            ]
        ];

        const csv = rowsData
            .map(row =>
                row.map(value => {
                    const safeValue = String(value ?? '')
                        .replaceAll('"', '""');

                    return `"${safeValue}"`;
                }).join(',')
            )
            .join('\r\n');

        const blob = new Blob(
            ['\uFEFF' + csv],
            {
                type: 'text/csv;charset=utf-8;'
            }
        );

        const url = URL.createObjectURL(blob);

        const link = document.createElement('a');

        link.href = url;

        link.download =
            `archive-${archive.id}.csv`;

        document.body.appendChild(link);

        link.click();

        link.remove();

        URL.revokeObjectURL(url);

        toast('تم تجهيز ملف الأرشيف');
    });

    /*
    |--------------------------------------------------------------------------
    | Keyboard shortcuts
    |--------------------------------------------------------------------------
    */

    document.addEventListener('keydown', function (event) {

        /*
        | Ctrl + K
        | Focus search
        */

        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {

            event.preventDefault();

            searchInput?.focus();

            searchInput?.select();

            return;
        }

        /*
        | Escape
        | Clear search
        */

        if (event.key === 'Escape' && document.activeElement === searchInput) {

            if (searchInput.value) {
                searchInput.value = '';
                filterRows();
            }

            searchInput.blur();
        }

    });

    /*
    |--------------------------------------------------------------------------
    | Initial state
    |--------------------------------------------------------------------------
    */

    refreshSelection();
    filterRows();

})();
</script>
@endpush