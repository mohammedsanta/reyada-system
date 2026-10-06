{{-- resources/views/archives/index.blade.php --}}
@extends('layouts.app')

@section('title', 'المستودعات الشهرية')

@section('content')

    @php
        /*
        |--------------------------------------------------------------------------
        | Safe Front-End Helpers
        |--------------------------------------------------------------------------
        */

        $archiveCollection = collect($archives);

        $totalArchives = $archiveCollection->count();

        $totalCases = $archiveCollection->sum(function ($archive) {
            return (int) ($archive->cases ?? 0);
        });

        $totalDebt = $archiveCollection->sum(function ($archive) {
            return (float) ($archive->debt ?? 0);
        });

        $totalCollected = $archiveCollection->sum(function ($archive) {
            return (float) ($archive->collected ?? 0);
        });

        $averageRate = $totalDebt > 0
            ? round(($totalCollected / $totalDebt) * 100, 1)
            : 0;

        $activeYear = $filters['year'] ?? '';
        $activeBank = $filters['bank'] ?? '';

        $selectedBankName = '';

        if ($activeBank !== '' && isset($banks[$activeBank])) {
            $selectedBankName = $banks[$activeBank];
        }

        /*
        |--------------------------------------------------------------------------
        | Front-End JSON
        |--------------------------------------------------------------------------
        */

        $archivesJson = $archiveCollection->map(function ($archive) {
            return [
                'month_label' => $archive->month_label ?? '',
                'bank' => $archive->bank ?? '',
                'cases' => (int) ($archive->cases ?? 0),
                'debt' => (float) ($archive->debt ?? 0),
                'collected' => (float) ($archive->collected ?? 0),
                'rate' => (float) ($archive->rate ?? 0),
                'by' => $archive->by ?? '',
                'at' => $archive->at ?? '',
                'url' => $archive->url ?? '#',
                'bank_url' => $archive->bank_url ?? '#',
            ];
        })->values();
    @endphp

    <x-page-header
        title="المستودعات الشهرية"
        subtitle="ملخصات الأشهر المغلقة لكل البنوك (للقراءة فقط)"
        icon="fa-box-archive"
    />

    {{-- =========================================================
         TOP STATISTICS
    ========================================================== --}}

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
         EXTRA OVERVIEW
    ========================================================== --}}

    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="card">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <div class="text-[11px] text-dim">الأرشيفات المعروضة</div>
                    <div id="archive-result-count" class="mt-1 text-xl font-bold text-fg">
                        {{ $totalArchives }}
                    </div>
                </div>

                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-info/10 text-info">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </span>
            </div>
        </div>

        <div class="card">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <div class="text-[11px] text-dim">إجمالي الحالات</div>
                    <div class="mt-1 text-xl font-bold text-fg">
                        {{ number_format($totalCases) }}
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
                        {{ number_format($totalDebt / 1000) }}k
                    </div>
                    <div class="mt-1 text-[10px] text-dim">EGP</div>
                </div>

                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-warning/10 text-warning">
                    <i class="fa-solid fa-money-bill-trend-up"></i>
                </span>
            </div>
        </div>

        <div class="card">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <div class="text-[11px] text-dim">متوسط نسبة التحصيل</div>
                    <div class="mt-1 text-xl font-bold text-brand">
                        {{ $averageRate }}%
                    </div>
                </div>

                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand/10 text-brand">
                    <i class="fa-solid fa-chart-line"></i>
                </span>
            </div>
        </div>

    </section>

    {{-- =========================================================
         FILTERS
    ========================================================== --}}

    <form
        id="archiveFilters"
        method="GET"
        action="{{ route('archives.index') }}"
        class="card filter-bar"
    >

        {{-- Search --}}
        <div class="min-w-[220px] flex-1">
            <label for="archiveSearch" class="form-label">
                بحث سريع
            </label>

            <div class="relative">
                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-dim"></i>

                <input
                    id="archiveSearch"
                    type="search"
                    class="form-input pr-9"
                    placeholder="ابحث باسم البنك أو الشهر..."
                    autocomplete="off"
                >
            </div>
        </div>

        {{-- Year --}}
        <div class="w-40">
            <label for="year" class="form-label">
                السنة
            </label>

            <select
                id="year"
                name="year"
                class="form-input"
                onchange="this.form.submit()"
            >
                <option value="">الكل</option>

                @foreach($years as $year)
                    <option
                        value="{{ $year }}"
                        @selected($filters['year'] === (string) $year)
                    >
                        {{ $year }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Bank --}}
        <div class="w-56">
            <label for="bank" class="form-label">
                البنك
            </label>

            <select
                id="bank"
                name="bank"
                class="form-input"
                onchange="this.form.submit()"
            >
                <option value="">
                    كل البنوك
                </option>

                @foreach($banks as $id => $name)
                    <option
                        value="{{ $id }}"
                        @selected($filters['bank'] === (string) $id)
                    >
                        {{ $name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Actions --}}
        <div class="flex items-end gap-2">

            <button
                type="submit"
                class="btn btn-primary"
                title="بحث"
            >
                <i class="fa-solid fa-filter text-xs"></i>
                تطبيق
            </button>

            <a
                href="{{ route('archives.index') }}"
                class="btn btn-secondary"
                title="إعادة ضبط"
            >
                <i class="fa-solid fa-rotate-right"></i>
            </a>

        </div>

    </form>

    {{-- =========================================================
         ACTIVE FILTERS
    ========================================================== --}}

    @if($activeYear !== '' || $activeBank !== '')

        <div class="mb-6 flex flex-wrap items-center gap-2">

            <span class="text-[11px] text-dim">
                الفلاتر الحالية:
            </span>

            @if($activeYear !== '')
                <span class="badge badge-outline-info">
                    <i class="fa-solid fa-calendar-days ml-1 text-[9px]"></i>
                    {{ $activeYear }}
                </span>
            @endif

            @if($activeBank !== '')
                <span class="badge badge-outline-info">
                    <i class="fa-solid fa-building-columns ml-1 text-[9px]"></i>
                    {{ $selectedBankName ?: 'البنك المحدد' }}
                </span>
            @endif

            <a
                href="{{ route('archives.index') }}"
                class="text-[11px] text-danger transition hover:text-danger/80"
            >
                إزالة الفلاتر
            </a>

        </div>

    @endif

    {{-- =========================================================
         TOOLBAR
    ========================================================== --}}

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">

        <div class="flex items-center gap-2">

            <span class="flex h-8 items-center rounded-lg border border-line bg-surface px-3 text-[11px] text-muted">
                <i class="fa-solid fa-box-archive ml-2 text-info"></i>

                <span>
                    النتائج:
                    <b id="toolbar-result-count" class="text-fg">
                        {{ $totalArchives }}
                    </b>
                </span>
            </span>

            <span class="hidden text-[11px] text-dim sm:inline">
                المستودعات للقراءة فقط
            </span>

        </div>

        <div class="flex items-center gap-2">

            <button
                type="button"
                id="exportArchives"
                class="btn btn-outline-success btn-sm"
                title="تصدير البيانات"
            >
                <i class="fa-solid fa-file-export text-xs"></i>
                تصدير
            </button>

            <button
                type="button"
                id="printArchives"
                class="btn btn-secondary btn-sm"
                title="طباعة"
            >
                <i class="fa-solid fa-print text-xs"></i>
                طباعة
            </button>

        </div>

    </div>

    {{-- =========================================================
         ARCHIVES
    ========================================================== --}}

    <section
        id="archivesGrid"
        class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3"
    >

        @forelse($archives as $archive)

            @php
                $rate = (float) ($archive->rate ?? 0);

                $barClass = $rate >= 50
                    ? 'bg-brand'
                    : ($rate >= 30 ? 'bg-warning' : 'bg-danger');

                $rateTextClass = $rate >= 50
                    ? 'text-brand'
                    : ($rate >= 30 ? 'text-warning' : 'text-danger');

                $debtValue = (float) ($archive->debt ?? 0);
                $collectedValue = (float) ($archive->collected ?? 0);

                $remainingValue = $debtValue - $collectedValue;

                $searchText = strtolower(
                    ($archive->month_label ?? '') . ' ' .
                    ($archive->bank ?? '') . ' ' .
                    ($archive->by ?? '') . ' ' .
                    ($archive->at ?? '')
                );
            @endphp

            <div
                class="archive-card card group transition duration-200 hover:-translate-y-0.5 hover:border-info/30 hover:shadow-lg hover:shadow-black/20"
                data-search="{{ $searchText }}"
                data-month="{{ $archive->month_label }}"
                data-bank="{{ $archive->bank }}"
                data-cases="{{ $archive->cases }}"
                data-debt="{{ $debtValue }}"
                data-collected="{{ $collectedValue }}"
                data-rate="{{ $rate }}"
                data-by="{{ $archive->by }}"
                data-at="{{ $archive->at }}"
                data-url="{{ $archive->url }}"
            >

                {{-- Card Header --}}
                <div class="mb-4 flex items-start justify-between gap-3">

                    <div class="min-w-0">

                        <div class="flex items-center gap-2">

                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-info/10 text-info">
                                <i class="fa-solid fa-box-archive text-sm"></i>
                            </span>

                            <div class="min-w-0">

                                <h3 class="truncate text-sm font-bold text-fg">
                                    {{ $archive->month_label }}
                                </h3>

                                <a
                                    href="{{ $archive->bank_url }}"
                                    class="mt-1 inline-flex max-w-full items-center gap-1 truncate text-xs text-muted transition hover:text-brand"
                                >
                                    <i class="fa-solid fa-building-columns text-info"></i>

                                    <span class="truncate">
                                        {{ $archive->bank }}
                                    </span>
                                </a>

                            </div>

                        </div>

                    </div>

                    <div class="flex shrink-0 items-center gap-2">

                        <button
                            type="button"
                            class="icon-btn icon-btn-info archive-details"
                            title="عرض التفاصيل"
                            aria-label="عرض التفاصيل"
                        >
                            <i class="fa-solid fa-eye"></i>
                        </button>

                        <span class="badge badge-neutral">
                            <i class="fa-solid fa-lock ml-1 text-[9px]"></i>
                            مؤرشف
                        </span>

                    </div>

                </div>

                {{-- Main Numbers --}}
                <div class="grid grid-cols-3 gap-2 text-center">

                    <div class="rounded-lg border border-line bg-surface/40 px-2 py-3">
                        <div class="text-[10px] text-dim">
                            الحالات
                        </div>

                        <div class="mt-1 text-sm font-bold text-fg">
                            {{ number_format($archive->cases ?? 0) }}
                        </div>
                    </div>

                    <div class="rounded-lg border border-line bg-surface/40 px-2 py-3">
                        <div class="text-[10px] text-dim">
                            المديونية
                        </div>

                        <div class="mt-1 text-sm font-bold text-fg">
                            {{ number_format($debtValue / 1000) }}k
                        </div>
                    </div>

                    <div class="rounded-lg border border-line bg-surface/40 px-2 py-3">
                        <div class="text-[10px] text-dim">
                            المحصل
                        </div>

                        <div class="mt-1 text-sm font-bold text-brand">
                            {{ number_format($collectedValue / 1000) }}k
                        </div>
                    </div>

                </div>

                {{-- Collection Progress --}}
                <div class="mt-4">

                    <div class="mb-1 flex items-center justify-between text-[11px] text-muted">

                        <span>
                            نسبة التحصيل
                        </span>

                        <span class="font-bold {{ $rateTextClass }}">
                            {{ $rate }}%
                        </span>

                    </div>

                    <div class="progress overflow-hidden">

                        <div
                            class="h-full rounded-full {{ $barClass }} transition-all duration-500"
                            style="width: {{ max(0, min(100, $rate)) }}%"
                        ></div>

                    </div>

                </div>

                {{-- Secondary Metrics --}}
                <div class="mt-4 grid grid-cols-2 gap-2">

                    <div class="rounded-lg border border-line px-3 py-2">

                        <div class="text-[10px] text-dim">
                            المتبقي
                        </div>

                        <div class="mt-1 text-xs font-bold {{ $remainingValue > 0 ? 'text-warning' : 'text-brand' }}">
                            EGP {{ number_format($remainingValue) }}
                        </div>

                    </div>

                    <div class="rounded-lg border border-line px-3 py-2">

                        <div class="text-[10px] text-dim">
                            الحالة
                        </div>

                        <div class="mt-1 text-xs font-bold {{ $rate >= 50 ? 'text-brand' : ($rate >= 30 ? 'text-warning' : 'text-danger') }}">

                            @if($rate >= 50)
                                أداء جيد
                            @elseif($rate >= 30)
                                أداء متوسط
                            @else
                                يحتاج متابعة
                            @endif

                        </div>

                    </div>

                </div>

                {{-- Footer --}}
                <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-line pt-3">

                    <div class="min-w-0">

                        <div class="flex items-center gap-1 text-[11px] text-dim">
                            <i class="fa-solid fa-user text-[9px]"></i>

                            <span class="truncate">
                                {{ $archive->by }}
                            </span>
                        </div>

                        <div class="mt-1 flex items-center gap-1 text-[10px] text-dim">
                            <i class="fa-regular fa-clock text-[9px]"></i>
                            {{ $archive->at }}
                        </div>

                    </div>

                    <div class="flex items-center gap-2">

                        <a
                            href="{{ $archive->url }}"
                            class="btn btn-outline-info btn-sm"
                        >
                            <i class="fa-solid fa-eye text-xs"></i>
                            عرض
                        </a>

                        <button
                            type="button"
                            class="btn btn-outline-success btn-sm archive-export-one"
                            title="تصدير هذا الأرشيف"
                        >
                            <i class="fa-solid fa-file-excel text-xs"></i>
                        </button>

                        <button
                            type="button"
                            class="icon-btn icon-btn-info archive-copy"
                            title="نسخ بيانات الأرشيف"
                            aria-label="نسخ بيانات الأرشيف"
                        >
                            <i class="fa-regular fa-copy"></i>
                        </button>

                    </div>

                </div>

            </div>

        @empty

            <div class="card md:col-span-2 xl:col-span-3">
                <x-empty-state
                    icon="fa-box-archive"
                    text="لا توجد مستودعات مطابقة"
                />
            </div>

        @endforelse

    </section>

    {{-- =========================================================
         FRONT-END EMPTY SEARCH STATE
    ========================================================== --}}

    <div
        id="searchEmptyState"
        class="card mt-4 hidden"
    >

        <div class="py-8 text-center">

            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-info/10 text-info">
                <i class="fa-solid fa-magnifying-glass"></i>
            </span>

            <h3 class="mt-4 text-sm font-bold text-fg">
                لا توجد نتائج
            </h3>

            <p class="mt-1 text-xs text-muted">
                لم يتم العثور على أرشيف يطابق البحث الحالي.
            </p>

            <button
                type="button"
                id="clearArchiveSearch"
                class="btn btn-secondary btn-sm mt-4"
            >
                <i class="fa-solid fa-rotate-left text-xs"></i>
                مسح البحث
            </button>

        </div>

    </div>

    {{-- =========================================================
         ARCHIVE DETAILS MODAL
    ========================================================== --}}

    <x-modal
        id="archiveDetailsModal"
        title="تفاصيل الأرشيف"
        width="42rem"
    >

        <div id="archiveDetailsContent" class="space-y-5">

            <div class="rounded-xl border border-line bg-surface p-4">

                <div class="flex items-center gap-3">

                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-info/10 text-info">
                        <i class="fa-solid fa-box-archive text-lg"></i>
                    </span>

                    <div class="min-w-0">

                        <h3
                            id="detail-month"
                            class="text-lg font-bold text-fg"
                        >
                            -
                        </h3>

                        <p
                            id="detail-bank"
                            class="mt-1 text-xs text-muted"
                        >
                            -
                        </p>

                    </div>

                </div>

            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">

                <div class="rounded-xl border border-line bg-surface p-3 text-center">
                    <div class="text-[10px] text-dim">الحالات</div>
                    <div id="detail-cases" class="mt-1 text-sm font-bold text-fg">-</div>
                </div>

                <div class="rounded-xl border border-line bg-surface p-3 text-center">
                    <div class="text-[10px] text-dim">المديونية</div>
                    <div id="detail-debt" class="mt-1 text-sm font-bold text-fg">-</div>
                </div>

                <div class="rounded-xl border border-line bg-surface p-3 text-center">
                    <div class="text-[10px] text-dim">المحصل</div>
                    <div id="detail-collected" class="mt-1 text-sm font-bold text-brand">-</div>
                </div>

                <div class="rounded-xl border border-line bg-surface p-3 text-center">
                    <div class="text-[10px] text-dim">النسبة</div>
                    <div id="detail-rate" class="mt-1 text-sm font-bold text-info">-</div>
                </div>

            </div>

            <div class="rounded-xl border border-line bg-surface p-4">

                <div class="mb-3 text-xs font-bold text-fg">
                    معلومات الأرشفة
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

                    <div>
                        <div class="text-[10px] text-dim">تم الأرشفة بواسطة</div>
                        <div id="detail-by" class="mt-1 text-xs text-muted">-</div>
                    </div>

                    <div>
                        <div class="text-[10px] text-dim">وقت الأرشفة</div>
                        <div id="detail-at" class="mt-1 text-xs text-muted">-</div>
                    </div>

                </div>

            </div>

            <div class="flex flex-wrap gap-2">

                <button
                    type="button"
                    id="detailCopy"
                    class="btn btn-secondary btn-sm"
                >
                    <i class="fa-regular fa-copy text-xs"></i>
                    نسخ البيانات
                </button>

                <button
                    type="button"
                    id="detailPrint"
                    class="btn btn-secondary btn-sm"
                >
                    <i class="fa-solid fa-print text-xs"></i>
                    طباعة
                </button>

                <a
                    id="detailOpen"
                    href="#"
                    class="btn btn-primary btn-sm"
                >
                    <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                    فتح الأرشيف
                </a>

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
    const grid = document.getElementById('archivesGrid');
    const resultCount = document.getElementById('archive-result-count');
    const toolbarCount = document.getElementById('toolbar-result-count');
    const searchEmptyState = document.getElementById('searchEmptyState');
    const clearSearchButton = document.getElementById('clearArchiveSearch');

    const detailsModal = document.getElementById('archiveDetailsModal');

    if (!grid) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Cache archive cards
    |--------------------------------------------------------------------------
    */

    const cards = Array.from(
        grid.querySelectorAll('.archive-card')
    );

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatNumber(value) {
        const number = Number(value || 0);

        return new Intl.NumberFormat('en-US', {
            maximumFractionDigits: 0
        }).format(number);
    }

    function formatMoney(value) {
        return 'EGP ' + formatNumber(value);
    }

    function csvEscape(value) {
        return '"' + String(value ?? '')
            .replace(/"/g, '""')
            .replace(/\r?\n/g, ' ') + '"';
    }

    function downloadFile(content, filename, type) {

        const blob = new Blob(
            [content],
            { type: type + ';charset=utf-8;' }
        );

        const url = URL.createObjectURL(blob);

        const link = document.createElement('a');

        link.href = url;
        link.download = filename;

        document.body.appendChild(link);

        link.click();

        link.remove();

        setTimeout(function () {
            URL.revokeObjectURL(url);
        }, 500);

    }

    function showToast(message) {

        if (typeof window.showToast === 'function') {
            window.showToast(message);
            return;
        }

        const toast = document.createElement('div');

        toast.className =
            'fixed bottom-5 left-5 z-[100] rounded-xl border border-line bg-surface px-4 py-3 text-xs text-fg shadow-xl';

        toast.textContent = message;

        document.body.appendChild(toast);

        setTimeout(function () {
            toast.remove();
        }, 2200);

    }

    function copyText(text, message) {

        if (!text) {
            showToast('لا توجد بيانات للنسخ');
            return;
        }

        if (
            navigator.clipboard &&
            window.isSecureContext
        ) {

            navigator.clipboard.writeText(text)
                .then(function () {
                    showToast(message || 'تم النسخ');
                })
                .catch(function () {
                    fallbackCopy(text, message);
                });

            return;
        }

        fallbackCopy(text, message);
    }

    function fallbackCopy(text, message) {

        const textarea = document.createElement('textarea');

        textarea.value = text;

        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';

        document.body.appendChild(textarea);

        textarea.select();

        try {
            document.execCommand('copy');
            showToast(message || 'تم النسخ');
        } catch (error) {
            showToast('تعذر نسخ البيانات');
        }

        textarea.remove();
    }

    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */

    function filterArchives() {

        const query = (
            searchInput?.value || ''
        ).trim().toLowerCase();

        let visible = 0;

        cards.forEach(function (card) {

            const text = (
                card.dataset.search || ''
            ).toLowerCase();

            const match = !query || text.includes(query);

            card.classList.toggle('hidden', !match);

            if (match) {
                visible++;
            }

        });

        if (resultCount) {
            resultCount.textContent = visible;
        }

        if (toolbarCount) {
            toolbarCount.textContent = visible;
        }

        if (searchEmptyState) {

            const showEmpty =
                visible === 0 &&
                cards.length > 0;

            searchEmptyState.classList.toggle(
                'hidden',
                !showEmpty
            );

        }

    }

    if (searchInput) {

        searchInput.addEventListener(
            'input',
            filterArchives
        );

    }

    if (clearSearchButton) {

        clearSearchButton.addEventListener(
            'click',
            function () {

                if (searchInput) {
                    searchInput.value = '';
                }

                filterArchives();

                searchInput?.focus();

            }
        );

    }

    /*
    |--------------------------------------------------------------------------
    | Details Modal
    |--------------------------------------------------------------------------
    */

    let activeCard = null;

    function openDetails(card) {

        if (!card || !detailsModal) {
            return;
        }

        activeCard = card;

        const data = card.dataset;

        const month = document.getElementById('detail-month');
        const bank = document.getElementById('detail-bank');
        const cases = document.getElementById('detail-cases');
        const debt = document.getElementById('detail-debt');
        const collected = document.getElementById('detail-collected');
        const rate = document.getElementById('detail-rate');
        const by = document.getElementById('detail-by');
        const at = document.getElementById('detail-at');
        const open = document.getElementById('detailOpen');

        if (month) {
            month.textContent = data.month || '-';
        }

        if (bank) {
            bank.textContent = data.bank || '-';
        }

        if (cases) {
            cases.textContent = formatNumber(data.cases);
        }

        if (debt) {
            debt.textContent = formatMoney(data.debt);
        }

        if (collected) {
            collected.textContent = formatMoney(data.collected);
        }

        if (rate) {
            rate.textContent = (data.rate || 0) + '%';
        }

        if (by) {
            by.textContent = data.by || '-';
        }

        if (at) {
            at.textContent = data.at || '-';
        }

        if (open) {
            open.href = data.url || '#';
        }

        detailsModal.showModal();

    }

    /*
    |--------------------------------------------------------------------------
    | Event Delegation
    |--------------------------------------------------------------------------
    */

    document.addEventListener('click', function (event) {

        const detailsButton =
            event.target.closest('.archive-details');

        if (detailsButton) {

            const card =
                detailsButton.closest('.archive-card');

            openDetails(card);

            return;
        }

        const copyButton =
            event.target.closest('.archive-copy');

        if (copyButton) {

            const card =
                copyButton.closest('.archive-card');

            if (!card) {
                return;
            }

            const data = card.dataset;

            const text = [
                'الأرشيف: ' + (data.month || '-'),
                'البنك: ' + (data.bank || '-'),
                'الحالات: ' + formatNumber(data.cases),
                'المديونية: ' + formatMoney(data.debt),
                'المحصل: ' + formatMoney(data.collected),
                'نسبة التحصيل: ' + (data.rate || 0) + '%',
                'بواسطة: ' + (data.by || '-'),
                'وقت الأرشفة: ' + (data.at || '-')
            ].join('\n');

            copyText(
                text,
                'تم نسخ بيانات الأرشيف'
            );

            return;
        }

        const exportButton =
            event.target.closest('.archive-export-one');

        if (exportButton) {

            const card =
                exportButton.closest('.archive-card');

            if (!card) {
                return;
            }

            exportSingleArchive(card);

        }

    });

    /*
    |--------------------------------------------------------------------------
    | Copy From Details
    |--------------------------------------------------------------------------
    */

    const detailCopy =
        document.getElementById('detailCopy');

    if (detailCopy) {

        detailCopy.addEventListener(
            'click',
            function () {

                if (!activeCard) {
                    return;
                }

                const data = activeCard.dataset;

                const text = [
                    'الأرشيف: ' + (data.month || '-'),
                    'البنك: ' + (data.bank || '-'),
                    'الحالات: ' + formatNumber(data.cases),
                    'المديونية: ' + formatMoney(data.debt),
                    'المحصل: ' + formatMoney(data.collected),
                    'نسبة التحصيل: ' + (data.rate || 0) + '%',
                    'بواسطة: ' + (data.by || '-'),
                    'وقت الأرشفة: ' + (data.at || '-')
                ].join('\n');

                copyText(
                    text,
                    'تم نسخ بيانات الأرشيف'
                );

            }
        );

    }

    /*
    |--------------------------------------------------------------------------
    | Export Single Archive
    |--------------------------------------------------------------------------
    */

    function exportSingleArchive(card) {

        const data = card.dataset;

        const rows = [
            [
                'الأرشيف',
                'البنك',
                'الحالات',
                'المديونية',
                'المحصل',
                'نسبة التحصيل',
                'بواسطة',
                'وقت الأرشفة'
            ],

            [
                data.month || '',
                data.bank || '',
                data.cases || 0,
                data.debt || 0,
                data.collected || 0,
                data.rate || 0,
                data.by || '',
                data.at || ''
            ]
        ];

        const csv = rows
            .map(row => row.map(csvEscape).join(','))
            .join('\r\n');

        downloadFile(
            '\uFEFF' + csv,
            'archive-' + (data.month || 'export') + '.csv',
            'text/csv'
        );

        showToast('تم تصدير الأرشيف');

    }

    /*
    |--------------------------------------------------------------------------
    | Export All Visible Archives
    |--------------------------------------------------------------------------
    */

    const exportButton =
        document.getElementById('exportArchives');

    if (exportButton) {

        exportButton.addEventListener(
            'click',
            function () {

                const visibleCards =
                    cards.filter(function (card) {
                        return !card.classList.contains('hidden');
                    });

                if (!visibleCards.length) {
                    showToast('لا توجد بيانات للتصدير');
                    return;
                }

                const rows = [
                    [
                        'الأرشيف',
                        'البنك',
                        'الحالات',
                        'المديونية',
                        'المحصل',
                        'نسبة التحصيل',
                        'بواسطة',
                        'وقت الأرشفة'
                    ]
                ];

                visibleCards.forEach(function (card) {

                    const data = card.dataset;

                    rows.push([
                        data.month || '',
                        data.bank || '',
                        data.cases || 0,
                        data.debt || 0,
                        data.collected || 0,
                        data.rate || 0,
                        data.by || '',
                        data.at || ''
                    ]);

                });

                const csv = rows
                    .map(row => row.map(csvEscape).join(','))
                    .join('\r\n');

                downloadFile(
                    '\uFEFF' + csv,
                    'monthly-archives.csv',
                    'text/csv'
                );

                showToast(
                    'تم تصدير ' +
                    visibleCards.length +
                    ' أرشيف'
                );

            }
        );

    }

    /*
    |--------------------------------------------------------------------------
    | Print
    |--------------------------------------------------------------------------
    */

    const printButton =
        document.getElementById('printArchives');

    if (printButton) {

        printButton.addEventListener(
            'click',
            function () {

                const visibleCards =
                    cards.filter(function (card) {
                        return !card.classList.contains('hidden');
                    });

                if (!visibleCards.length) {
                    showToast('لا توجد بيانات للطباعة');
                    return;
                }

                const rows = visibleCards.map(function (card) {

                    const data = card.dataset;

                    return `
                        <tr>
                            <td>${escapeHtml(data.month)}</td>
                            <td>${escapeHtml(data.bank)}</td>
                            <td>${escapeHtml(formatNumber(data.cases))}</td>
                            <td>${escapeHtml(formatMoney(data.debt))}</td>
                            <td>${escapeHtml(formatMoney(data.collected))}</td>
                            <td>${escapeHtml((data.rate || 0) + '%')}</td>
                            <td>${escapeHtml(data.by)}</td>
                            <td>${escapeHtml(data.at)}</td>
                        </tr>
                    `;

                }).join('');

                const printWindow =
                    window.open('', '_blank', 'width=1200,height=800');

                if (!printWindow) {
                    showToast('يرجى السماح بفتح نافذة الطباعة');
                    return;
                }

                printWindow.document.write(`
                    <!DOCTYPE html>
                    <html lang="ar" dir="rtl">
                    <head>
                        <meta charset="UTF-8">

                        <title>المستودعات الشهرية</title>

                        <style>

                            body {
                                font-family: Arial, sans-serif;
                                direction: rtl;
                                padding: 30px;
                                color: #111;
                            }

                            h1 {
                                margin-bottom: 6px;
                            }

                            p {
                                color: #666;
                                margin-top: 0;
                            }

                            table {
                                width: 100%;
                                border-collapse: collapse;
                                margin-top: 25px;
                            }

                            th,
                            td {
                                border: 1px solid #ddd;
                                padding: 10px;
                                text-align: right;
                                font-size: 12px;
                            }

                            th {
                                background: #f3f3f3;
                                font-weight: bold;
                            }

                            .footer {
                                margin-top: 25px;
                                font-size: 11px;
                                color: #777;
                            }

                        </style>
                    </head>

                    <body>

                        <h1>المستودعات الشهرية</h1>

                        <p>
                            تقرير الأرشيفات الشهرية
                        </p>

                        <table>

                            <thead>
                                <tr>
                                    <th>الأرشيف</th>
                                    <th>البنك</th>
                                    <th>الحالات</th>
                                    <th>المديونية</th>
                                    <th>المحصل</th>
                                    <th>نسبة التحصيل</th>
                                    <th>بواسطة</th>
                                    <th>وقت الأرشفة</th>
                                </tr>
                            </thead>

                            <tbody>
                                ${rows}
                            </tbody>

                        </table>

                        <div class="footer">
                            إجمالي النتائج: ${visibleCards.length}
                        </div>

                    </body>
                    </html>
                `);

                printWindow.document.close();

                printWindow.focus();

                setTimeout(function () {
                    printWindow.print();
                }, 250);

            }
        );

    }

    /*
    |--------------------------------------------------------------------------
    | Print Current Archive
    |--------------------------------------------------------------------------
    */

    const detailPrint =
        document.getElementById('detailPrint');

    if (detailPrint) {

        detailPrint.addEventListener(
            'click',
            function () {

                if (!activeCard) {
                    return;
                }

                const data = activeCard.dataset;

                const printWindow =
                    window.open('', '_blank', 'width=900,height=700');

                if (!printWindow) {
                    showToast('يرجى السماح بفتح نافذة الطباعة');
                    return;
                }

                printWindow.document.write(`
                    <!DOCTYPE html>

                    <html lang="ar" dir="rtl">

                    <head>

                        <meta charset="UTF-8">

                        <title>${escapeHtml(data.month || 'الأرشيف')}</title>

                        <style>

                            body {
                                font-family: Arial, sans-serif;
                                padding: 40px;
                                color: #111;
                            }

                            h1 {
                                margin-bottom: 5px;
                            }

                            .muted {
                                color: #777;
                            }

                            .grid {
                                display: grid;
                                grid-template-columns: repeat(2, 1fr);
                                gap: 12px;
                                margin-top: 25px;
                            }

                            .box {
                                border: 1px solid #ddd;
                                padding: 15px;
                                border-radius: 8px;
                            }

                            .label {
                                font-size: 11px;
                                color: #777;
                            }

                            .value {
                                margin-top: 5px;
                                font-weight: bold;
                            }

                        </style>

                    </head>

                    <body>

                        <h1>
                            ${escapeHtml(data.month || '-')}
                        </h1>

                        <div class="muted">
                            ${escapeHtml(data.bank || '-')}
                        </div>

                        <div class="grid">

                            <div class="box">
                                <div class="label">الحالات</div>
                                <div class="value">
                                    ${escapeHtml(formatNumber(data.cases))}
                                </div>
                            </div>

                            <div class="box">
                                <div class="label">المديونية</div>
                                <div class="value">
                                    ${escapeHtml(formatMoney(data.debt))}
                                </div>
                            </div>

                            <div class="box">
                                <div class="label">المحصل</div>
                                <div class="value">
                                    ${escapeHtml(formatMoney(data.collected))}
                                </div>
                            </div>

                            <div class="box">
                                <div class="label">نسبة التحصيل</div>
                                <div class="value">
                                    ${escapeHtml((data.rate || 0) + '%')}
                                </div>
                            </div>

                            <div class="box">
                                <div class="label">تم بواسطة</div>
                                <div class="value">
                                    ${escapeHtml(data.by || '-')}
                                </div>
                            </div>

                            <div class="box">
                                <div class="label">وقت الأرشفة</div>
                                <div class="value">
                                    ${escapeHtml(data.at || '-')}
                                </div>
                            </div>

                        </div>

                    </body>

                    </html>
                `);

                printWindow.document.close();

                printWindow.focus();

                setTimeout(function () {
                    printWindow.print();
                }, 250);

            }
        );

    }

    /*
    |--------------------------------------------------------------------------
    | Keyboard Shortcuts
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'keydown',
        function (event) {

            if (
                (event.ctrlKey || event.metaKey) &&
                event.key.toLowerCase() === 'k'
            ) {

                event.preventDefault();

                searchInput?.focus();

            }

            if (
                event.key === 'Escape' &&
                document.activeElement === searchInput
            ) {

                if (searchInput) {
                    searchInput.value = '';
                }

                filterArchives();

            }

        }
    );

    /*
    |--------------------------------------------------------------------------
    | Initial State
    |--------------------------------------------------------------------------
    */

    filterArchives();

})();
</script>
@endpush