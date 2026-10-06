{{-- resources/views/reports/exports.blade.php : export history --}}
@extends('layouts.app')

@section('title', 'سجل التصدير')

@php
    /*
    |--------------------------------------------------------------------------
    | Safe normalization
    |--------------------------------------------------------------------------
    | Keep the existing backend contract untouched.
    */

    $exportCollection = collect($exports ?? []);
    $typeOptions = collect($types ?? []);
    $statusOptions = collect($statuses ?? []);
    $formatOptions = collect($formats ?? []);

    $safeFilters = array_merge([
        'type' => '',
        'status' => '',
        'format' => '',
    ], $filters ?? []);

    /*
    |--------------------------------------------------------------------------
    | Current page statistics
    |--------------------------------------------------------------------------
    */

    $pageTotal = $exportCollection->count();

    $pageCompleted = $exportCollection->filter(
        fn ($export) => ($export->status ?? null) === 'completed'
    )->count();

    $pagePending = $exportCollection->filter(
        fn ($export) => in_array(($export->status ?? null), [
            'pending',
            'processing',
            'queued',
        ], true)
    )->count();

    $pageFailed = $exportCollection->filter(
        fn ($export) => in_array(($export->status ?? null), [
            'failed',
            'error',
        ], true)
    )->count();

    $pageOther = max(
        0,
        $pageTotal - $pageCompleted - $pagePending - $pageFailed
    );

    /*
    |--------------------------------------------------------------------------
    | Format statistics
    |--------------------------------------------------------------------------
    */

    $xlsxCount = $exportCollection->filter(
        fn ($export) => strtolower((string) ($export->format ?? '')) === 'xlsx'
    )->count();

    $pdfCount = $exportCollection->filter(
        fn ($export) => strtolower((string) ($export->format ?? '')) === 'pdf'
    )->count();

    $csvCount = $exportCollection->filter(
        fn ($export) => strtolower((string) ($export->format ?? '')) === 'csv'
    )->count();

    /*
    |--------------------------------------------------------------------------
    | Report type metadata
    |--------------------------------------------------------------------------
    | Presentation-only. Does not introduce backend report types.
    */

    $reportTypeMeta = [
        'employees' => [
            'icon' => 'fa-users',
            'color' => 'info',
        ],
        'employee' => [
            'icon' => 'fa-user-tie',
            'color' => 'info',
        ],
        'clients' => [
            'icon' => 'fa-users-viewfinder',
            'color' => 'brand',
        ],
        'cases' => [
            'icon' => 'fa-folder-open',
            'color' => 'warning',
        ],
        'payments' => [
            'icon' => 'fa-money-bill-transfer',
            'color' => 'success',
        ],
        'promises' => [
            'icon' => 'fa-handshake',
            'color' => 'cyan',
        ],
        'complaints' => [
            'icon' => 'fa-headset',
            'color' => 'danger',
        ],
        'banks' => [
            'icon' => 'fa-building-columns',
            'color' => 'info',
        ],
        'distribution' => [
            'icon' => 'fa-sitemap',
            'color' => 'accent',
        ],
        'collection' => [
            'icon' => 'fa-chart-line',
            'color' => 'brand',
        ],
        'performance' => [
            'icon' => 'fa-chart-simple',
            'color' => 'warning',
        ],
    ];

    /*
    |--------------------------------------------------------------------------
    | JavaScript-safe export data
    |--------------------------------------------------------------------------
    */

    $exportJsData = $exportCollection->values()->map(function ($export) use ($types) {
        $typeKey = (string) ($export->type ?? '');

        return [
            'id' => $export->id ?? null,
            'type' => $typeKey,
            'type_name' => $types[$typeKey] ?? $typeKey ?: 'تقرير',
            'bank' => (string) ($export->bank ?? 'كل البنوك'),
            'scope' => (string) ($export->scope ?? '-'),
            'format' => strtolower((string) ($export->format ?? '')),
            'rows' => $export->rows ?? null,
            'status' => (string) ($export->status ?? ''),
            'error' => (string) ($export->error ?? ''),
            'user' => (string) ($export->user ?? '-'),
            'at' => (string) ($export->at ?? '-'),
        ];
    })->all();
@endphp

@section('content')

    <x-page-header title="سجل التصدير" subtitle="كل التقارير التي قمت بإنشائها" icon="fa-clock-rotate-left">
        <x-slot:actions>

            <button
                type="button"
                id="printExports"
                class="btn btn-outline-secondary btn-sm"
                title="طباعة سجل التصدير"
            >
                <i class="fa-solid fa-print text-xs"></i>
                طباعة
            </button>

            <button
                type="button"
                id="refreshExports"
                class="btn btn-outline-info btn-sm"
                title="تحديث السجل"
            >
                <i class="fa-solid fa-arrows-rotate text-xs"></i>
                تحديث
            </button>

            <a href="{{ route('reports.index') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus text-xs"></i>
                تقرير جديد
            </a>

        </x-slot:actions>
    </x-page-header>

    <x-flash />

    {{-- ================================================================
         MAIN METRICS
    ================================================================= --}}
    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <x-metric-card
            label="كل التقارير"
            :value="$counts['all'] ?? $pageTotal"
            icon="fa-file-lines"
            color="info"
        />

        <x-metric-card
            label="جاهزة"
            :value="$counts['completed'] ?? $pageCompleted"
            icon="fa-circle-check"
            color="brand"
        />

        <x-metric-card
            label="قيد التجهيز"
            :value="$counts['pending'] ?? $pagePending"
            icon="fa-hourglass-half"
            color="warning"
        />

        <x-metric-card
            label="فشلت"
            :value="$counts['failed'] ?? $pageFailed"
            icon="fa-circle-xmark"
            color="danger"
        />

    </section>

    {{-- ================================================================
         EXPORT CENTER SUMMARY
    ================================================================= --}}
    <section class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-3">

        <x-section-card title="حالة التصدير" icon="fa-chart-pie" color="brand">

            <div class="grid grid-cols-2 gap-3">

                <div class="rounded-xl border border-brand/10 bg-brand/5 p-4">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[11px] text-muted">جاهزة</span>
                        <i class="fa-solid fa-circle-check text-brand"></i>
                    </div>

                    <div class="mt-2 text-xl font-bold text-fg">
                        {{ $pageCompleted }}
                    </div>
                </div>

                <div class="rounded-xl border border-warning/10 bg-warning/5 p-4">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[11px] text-muted">قيد التجهيز</span>
                        <i class="fa-solid fa-hourglass-half text-warning"></i>
                    </div>

                    <div class="mt-2 text-xl font-bold text-fg">
                        {{ $pagePending }}
                    </div>
                </div>

                <div class="rounded-xl border border-danger/10 bg-danger/5 p-4">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[11px] text-muted">فاشلة</span>
                        <i class="fa-solid fa-circle-xmark text-danger"></i>
                    </div>

                    <div class="mt-2 text-xl font-bold text-fg">
                        {{ $pageFailed }}
                    </div>
                </div>

                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[11px] text-muted">أخرى</span>
                        <i class="fa-solid fa-layer-group text-info"></i>
                    </div>

                    <div class="mt-2 text-xl font-bold text-fg">
                        {{ $pageOther }}
                    </div>
                </div>

            </div>

        </x-section-card>

        <x-section-card title="الصيغ المستخدمة" icon="fa-file-export" color="info">

            <div class="space-y-3">

                <div class="flex items-center justify-between rounded-xl border border-white/10 bg-white/5 px-4 py-3">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-success/10 text-success">
                            <i class="fa-solid fa-file-excel"></i>
                        </span>

                        <div>
                            <div class="text-xs font-semibold text-fg">Excel</div>
                            <div class="text-[10px] text-muted">XLSX</div>
                        </div>
                    </div>

                    <span class="text-sm font-bold text-fg">
                        {{ $xlsxCount }}
                    </span>
                </div>

                <div class="flex items-center justify-between rounded-xl border border-white/10 bg-white/5 px-4 py-3">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-danger/10 text-danger">
                            <i class="fa-solid fa-file-pdf"></i>
                        </span>

                        <div>
                            <div class="text-xs font-semibold text-fg">PDF</div>
                            <div class="text-[10px] text-muted">PDF</div>
                        </div>
                    </div>

                    <span class="text-sm font-bold text-fg">
                        {{ $pdfCount }}
                    </span>
                </div>

                <div class="flex items-center justify-between rounded-xl border border-white/10 bg-white/5 px-4 py-3">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-info/10 text-info">
                            <i class="fa-solid fa-file-csv"></i>
                        </span>

                        <div>
                            <div class="text-xs font-semibold text-fg">CSV</div>
                            <div class="text-[10px] text-muted">CSV</div>
                        </div>
                    </div>

                    <span class="text-sm font-bold text-fg">
                        {{ $csvCount }}
                    </span>
                </div>

            </div>

        </x-section-card>

        <x-section-card title="معلومات السجل" icon="fa-circle-info" color="cyan">

            <div class="space-y-3 text-[11px] leading-5 text-muted">

                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-clock-rotate-left mt-0.5 text-info"></i>
                    <span>
                        يعرض هذا السجل التقارير التي تم إنشاؤها وعمليات التصدير السابقة.
                    </span>
                </div>

                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-filter mt-0.5 text-brand"></i>
                    <span>
                        استخدم الفلاتر للوصول إلى التقارير حسب النوع أو الحالة أو الصيغة.
                    </span>
                </div>

                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-circle-exclamation mt-0.5 text-warning"></i>
                    <span>
                        التقارير الفاشلة يمكن إعادة تشغيلها من خلال زر إعادة المحاولة.
                    </span>
                </div>

                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-trash-can mt-0.5 text-danger"></i>
                    <span>
                        حذف سجل التصدير لا يعني حذف البيانات الأصلية التي تم إنشاء التقرير منها.
                    </span>
                </div>

            </div>

        </x-section-card>

    </section>

    {{-- ================================================================
         FILTERS
    ================================================================= --}}
    <form
        id="exportFilters"
        method="GET"
        action="{{ route('reports.exports') }}"
        class="card filter-bar"
    >

        <div class="w-56">
            <label for="type" class="form-label">التقرير</label>

            <select
                id="type"
                name="type"
                class="form-input"
                onchange="this.form.submit()"
            >
                <option value="">الكل</option>

                @foreach($types as $key => $label)
                    <option
                        value="{{ $key }}"
                        @selected(($safeFilters['type'] ?? '') === $key)
                    >
                        {{ $label }}
                    </option>
                @endforeach

            </select>
        </div>

        <div class="w-40">
            <label for="status" class="form-label">الحالة</label>

            <select
                id="status"
                name="status"
                class="form-input"
                onchange="this.form.submit()"
            >
                <option value="">الكل</option>

                @foreach($statuses as $key => $label)
                    <option
                        value="{{ $key }}"
                        @selected(($safeFilters['status'] ?? '') === $key)
                    >
                        {{ $label }}
                    </option>
                @endforeach

            </select>
        </div>

        <div class="w-40">
            <label for="format" class="form-label">الصيغة</label>

            <select
                id="format"
                name="format"
                class="form-input"
                onchange="this.form.submit()"
            >
                <option value="">الكل</option>

                @foreach($formats as $key => $label)
                    <option
                        value="{{ $key }}"
                        @selected(($safeFilters['format'] ?? '') === $key)
                    >
                        {{ $label }}
                    </option>
                @endforeach

            </select>
        </div>

        {{-- Frontend search only: does not require backend changes --}}
        <div class="w-full xl:flex-1">
            <label for="historySearch" class="form-label">
                بحث داخل النتائج الحالية
            </label>

            <div class="relative">
                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-dim"></i>

                <input
                    id="historySearch"
                    type="search"
                    class="form-input pr-9"
                    placeholder="اسم التقرير، البنك، المستخدم، الفترة..."
                    autocomplete="off"
                >
            </div>
        </div>

        <div class="flex items-end gap-2">

            <button
                type="button"
                id="clearHistorySearch"
                class="btn btn-secondary"
                title="مسح البحث"
            >
                <i class="fa-solid fa-eraser"></i>
            </button>

            <a
                href="{{ route('reports.exports') }}"
                class="btn btn-secondary"
                title="إعادة ضبط"
            >
                <i class="fa-solid fa-rotate-right"></i>
            </a>

        </div>

    </form>

    {{-- ================================================================
         ACTIVE FILTER SUMMARY
    ================================================================= --}}
    @php
        $hasServerFilters = filled($safeFilters['type'] ?? null)
            || filled($safeFilters['status'] ?? null)
            || filled($safeFilters['format'] ?? null);
    @endphp

    <section class="mb-4">

        <div class="flex flex-wrap items-center justify-between gap-3">

            <div class="flex flex-wrap items-center gap-2">

                <span class="text-[11px] text-muted">
                    <i class="fa-solid fa-filter ml-1 text-info"></i>
                    الفلاتر الحالية:
                </span>

                @if(filled($safeFilters['type'] ?? null))
                    <span class="badge badge-neutral">
                        التقرير:
                        {{ $types[$safeFilters['type']] ?? $safeFilters['type'] }}
                    </span>
                @endif

                @if(filled($safeFilters['status'] ?? null))
                    <span class="badge badge-neutral">
                        الحالة:
                        {{ $statuses[$safeFilters['status']] ?? $safeFilters['status'] }}
                    </span>
                @endif

                @if(filled($safeFilters['format'] ?? null))
                    <span class="badge badge-neutral uppercase">
                        الصيغة:
                        {{ $formats[$safeFilters['format']] ?? $safeFilters['format'] }}
                    </span>
                @endif

                @unless($hasServerFilters)
                    <span class="text-[11px] text-dim">
                        لا توجد فلاتر خادمية
                    </span>
                @endunless

            </div>

            <div class="text-[11px] text-muted">
                النتائج الحالية:
                <b id="resultsCounter" class="text-fg">{{ $pageTotal }}</b>
            </div>

        </div>

    </section>

    {{-- ================================================================
         BULK / TABLE TOOLBAR
    ================================================================= --}}
    <section class="mb-3">

        <div class="flex flex-wrap items-center justify-between gap-3">

            <div class="flex flex-wrap items-center gap-2">

                <button
                    type="button"
                    id="selectVisible"
                    class="btn btn-secondary btn-sm"
                >
                    <i class="fa-solid fa-check-double text-xs"></i>
                    تحديد الظاهر
                </button>

                <button
                    type="button"
                    id="clearSelection"
                    class="btn btn-secondary btn-sm"
                >
                    <i class="fa-solid fa-xmark text-xs"></i>
                    إلغاء التحديد
                </button>

                <button
                    type="button"
                    id="copySelected"
                    class="btn btn-secondary btn-sm"
                    disabled
                >
                    <i class="fa-regular fa-copy text-xs"></i>
                    نسخ المحدد
                </button>

            </div>

            <div class="flex items-center gap-2 text-[11px] text-muted">

                <span>
                    المحدد:
                </span>

                <b
                    id="selectedCount"
                    class="rounded-lg bg-brand/10 px-2 py-1 text-brand"
                >
                    0
                </b>

            </div>

        </div>

    </section>

    {{-- ================================================================
         TABLE
    ================================================================= --}}
    <div class="table-wrap">

        <table class="data-table whitespace-nowrap">

            <thead>

                <tr>

                    <th class="w-10">
                        <input
                            id="checkAll"
                            type="checkbox"
                            class="accent-brand"
                            aria-label="تحديد كل التقارير الظاهرة"
                        >
                    </th>

                    <th>#</th>
                    <th>التقرير</th>
                    <th>البنك</th>
                    <th>الفترة</th>
                    <th>الصيغة</th>
                    <th>الصفوف</th>
                    <th>الحالة</th>
                    <th>أنشأه</th>
                    <th>الوقت</th>
                    <th>الإجراءات</th>

                </tr>

            </thead>

            <tbody id="exportsTableBody">

                @forelse($exports as $export)

                    @php
                        $exportType = (string) ($export->type ?? '');
                        $exportStatus = (string) ($export->status ?? '');
                        $exportFormat = strtolower((string) ($export->format ?? ''));

                        $typeMeta = $reportTypeMeta[$exportType] ?? [
                            'icon' => 'fa-file-lines',
                            'color' => 'info',
                        ];

                        $typeLabel = $types[$exportType] ?? $exportType ?: 'تقرير';

                        $statusLabel = $statuses[$exportStatus] ?? $exportStatus ?: 'غير محدد';

                        $formatLabel = $formats[$exportFormat]
                            ?? strtoupper($exportFormat ?: 'FILE');

                        $isPending = in_array($exportStatus, [
                            'pending',
                            'processing',
                            'queued',
                        ], true);

                        $isCompleted = $exportStatus === 'completed';

                        $isFailed = in_array($exportStatus, [
                            'failed',
                            'error',
                        ], true);
                    @endphp

                    <tr
                        class="export-row"
                        data-id="{{ $export->id }}"
                        data-type="{{ strtolower($exportType) }}"
                        data-type-name="{{ strtolower($typeLabel) }}"
                        data-bank="{{ strtolower((string) ($export->bank ?? '')) }}"
                        data-scope="{{ strtolower((string) ($export->scope ?? '')) }}"
                        data-format="{{ $exportFormat }}"
                        data-status="{{ strtolower($exportStatus) }}"
                        data-status-label="{{ strtolower($statusLabel) }}"
                        data-user="{{ strtolower((string) ($export->user ?? '')) }}"
                        data-time="{{ strtolower((string) ($export->at ?? '')) }}"
                        data-rows="{{ $export->rows ?? '' }}"
                        data-error="{{ $export->error ?? '' }}"
                    >

                        {{-- Selection --}}
                        <td>

                            <input
                                type="checkbox"
                                class="export-check accent-brand"
                                value="{{ $export->id }}"
                                aria-label="تحديد التقرير {{ $export->id }}"
                            >

                        </td>

                        {{-- ID --}}
                        <td>

                            <span
                                class="cursor-pointer rounded-lg px-2 py-1 text-xs text-muted transition hover:bg-white/5 hover:text-fg"
                                title="نسخ رقم التقرير"
                                data-copy="{{ $export->id }}"
                            >
                                #{{ $export->id }}
                            </span>

                        </td>

                        {{-- Report --}}
                        <td>

                            <div class="flex items-center gap-3">

                                <span
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-info/10 text-info"
                                >
                                    <i class="fa-solid {{ $typeMeta['icon'] }}"></i>
                                </span>

                                <div class="min-w-0">

                                    <div class="max-w-[220px] truncate font-semibold text-fg">
                                        {{ $typeLabel }}
                                    </div>

                                    <div class="mt-1 text-[10px] text-dim">
                                        {{ $exportType ?: 'غير محدد' }}
                                    </div>

                                </div>

                            </div>

                        </td>

                        {{-- Bank --}}
                        <td>

                            <span class="text-xs">
                                {{ $export->bank ?? 'كل البنوك' }}
                            </span>

                        </td>

                        {{-- Scope --}}
                        <td>

                            <span
                                class="inline-flex max-w-[220px] truncate text-xs text-muted"
                                title="{{ $export->scope ?? '-' }}"
                            >
                                {{ $export->scope ?? '-' }}
                            </span>

                        </td>

                        {{-- Format --}}
                        <td>

                            <span
                                class="inline-flex items-center gap-2 rounded-lg border border-white/10 bg-white/5 px-2.5 py-1.5 text-[10px] font-bold uppercase text-fg"
                            >

                                @if($exportFormat === 'xlsx')
                                    <i class="fa-solid fa-file-excel text-success"></i>
                                @elseif($exportFormat === 'pdf')
                                    <i class="fa-solid fa-file-pdf text-danger"></i>
                                @elseif($exportFormat === 'csv')
                                    <i class="fa-solid fa-file-csv text-info"></i>
                                @else
                                    <i class="fa-solid fa-file text-muted"></i>
                                @endif

                                {{ $formatLabel }}

                            </span>

                        </td>

                        {{-- Rows --}}
                        <td>

                            @if($export->rows !== null)
                                <span class="font-semibold text-fg">
                                    {{ number_format((int) $export->rows) }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif

                        </td>

                        {{-- Status --}}
                        <td>

                            <x-status-badge
                                type="export"
                                :status="$exportStatus"
                            />

                            @if($export->error)

                                <button
                                    type="button"
                                    class="mt-1 block max-w-[220px] truncate text-right text-[11px] text-danger hover:underline view-error"
                                    title="عرض سبب الخطأ"
                                >
                                    {{ $export->error }}
                                </button>

                            @endif

                        </td>

                        {{-- User --}}
                        <td>

                            <span
                                class="cursor-pointer text-xs text-muted hover:text-fg"
                                title="نسخ اسم المستخدم"
                                data-copy="{{ $export->user ?? '' }}"
                            >
                                {{ $export->user ?? '-' }}
                            </span>

                        </td>

                        {{-- Time --}}
                        <td>

                            <span class="text-xs text-muted">
                                {{ $export->at ?? '-' }}
                            </span>

                        </td>

                        {{-- Actions --}}
                        <td>

                            <div class="flex items-center justify-end gap-1">

                                {{-- Details --}}
                                <button
                                    type="button"
                                    class="icon-btn icon-btn-info view-export"
                                    title="عرض التفاصيل"
                                    aria-label="عرض تفاصيل التقرير"
                                >
                                    <i class="fa-solid fa-eye"></i>
                                </button>

                                {{-- Copy --}}
                                <button
                                    type="button"
                                    class="icon-btn icon-btn-secondary copy-export"
                                    title="نسخ معلومات التقرير"
                                    aria-label="نسخ معلومات التقرير"
                                >
                                    <i class="fa-regular fa-copy"></i>
                                </button>

                                @if($isCompleted)

                                    {{-- TODO: real download route --}}
                                    <a
                                        href="#"
                                        class="btn btn-outline-success btn-sm"
                                        title="تحميل التقرير"
                                    >
                                        <i class="fa-solid fa-download text-xs"></i>
                                        تحميل
                                    </a>

                                @elseif($isFailed)

                                    <form
                                        method="POST"
                                        action="{{ route('reports.exports.retry', $export->id) }}"
                                        class="retry-form"
                                    >
                                        @csrf

                                        <button
                                            type="submit"
                                            class="btn btn-outline-info btn-sm retry-button"
                                        >
                                            <i class="fa-solid fa-rotate-right text-xs"></i>
                                            إعادة المحاولة
                                        </button>

                                    </form>

                                @endif

                                @unless($isPending)

                                    <form
                                        method="POST"
                                        action="{{ route('reports.exports.destroy', $export->id) }}"
                                        class="delete-form"
                                        data-report-name="{{ $typeLabel }}"
                                    >

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="btn btn-danger btn-sm delete-export"
                                            title="حذف"
                                        >
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>

                                    </form>

                                @endunless

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr id="serverEmptyRow">

                        <td colspan="11">

                            <x-empty-state
                                icon="fa-file-circle-xmark"
                                text="لا توجد تقارير مطابقة"
                            />

                        </td>

                    </tr>

                @endforelse

                <tr id="clientEmptyRow" class="hidden">

                    <td colspan="11">

                        <div class="py-12 text-center">

                            <div class="mx-auto flex max-w-sm flex-col items-center">

                                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/5 text-dim">
                                    <i class="fa-solid fa-magnifying-glass-minus text-xl"></i>
                                </span>

                                <h3 class="mt-4 text-sm font-bold text-fg">
                                    لا توجد نتائج
                                </h3>

                                <p class="mt-2 text-xs leading-6 text-muted">
                                    لا توجد تقارير تطابق البحث الحالي.
                                </p>

                                <button
                                    type="button"
                                    id="resetClientSearch"
                                    class="btn btn-secondary btn-sm mt-4"
                                >
                                    <i class="fa-solid fa-rotate-right text-xs"></i>
                                    إعادة ضبط البحث
                                </button>

                            </div>

                        </div>

                    </td>

                </tr>

            </tbody>

        </table>

    </div>

    {{-- ================================================================
         TABLE FOOTER
    ================================================================= --}}
    <section class="mt-4">

        <div class="flex flex-wrap items-center justify-between gap-3">

            <div class="flex flex-wrap items-center gap-3 text-[11px] text-muted">

                <span>
                    <i class="fa-solid fa-table-list ml-1 text-info"></i>
                    النتائج الظاهرة:
                    <b id="visibleResults" class="text-fg">{{ $pageTotal }}</b>
                </span>

                <span>
                    المحددة:
                    <b id="footerSelectedCount" class="text-brand">0</b>
                </span>

            </div>

            <div class="flex flex-wrap gap-2">

                <button
                    type="button"
                    id="printFiltered"
                    class="btn btn-secondary btn-sm"
                >
                    <i class="fa-solid fa-print text-xs"></i>
                    طباعة النتائج
                </button>

                <button
                    type="button"
                    id="exportCsv"
                    class="btn btn-secondary btn-sm"
                >
                    <i class="fa-solid fa-file-csv text-xs"></i>
                    CSV
                </button>

            </div>

        </div>

    </section>

    {{-- ================================================================
         EXPORT DETAILS MODAL
    ================================================================= --}}
    <x-modal id="exportDetailsModal" title="تفاصيل التقرير" width="36rem">

        <div class="space-y-4">

            <div class="flex items-center gap-3 rounded-xl border border-white/10 bg-white/5 p-4">

                <span
                    id="detailsIcon"
                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-info/10 text-info"
                >
                    <i class="fa-solid fa-file-lines"></i>
                </span>

                <div class="min-w-0">

                    <div
                        id="detailsTitle"
                        class="truncate text-sm font-bold text-fg"
                    >
                        تقرير
                    </div>

                    <div
                        id="detailsType"
                        class="mt-1 text-[10px] text-muted"
                    >
                        -
                    </div>

                </div>

            </div>

            <div class="grid grid-cols-2 gap-3">

                <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                    <div class="text-[10px] text-muted">رقم التقرير</div>
                    <div id="detailsId" class="mt-1 text-xs font-bold text-fg">-</div>
                </div>

                <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                    <div class="text-[10px] text-muted">البنك</div>
                    <div id="detailsBank" class="mt-1 text-xs font-bold text-fg">-</div>
                </div>

                <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                    <div class="text-[10px] text-muted">الفترة</div>
                    <div id="detailsScope" class="mt-1 text-xs font-bold text-fg">-</div>
                </div>

                <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                    <div class="text-[10px] text-muted">الصيغة</div>
                    <div id="detailsFormat" class="mt-1 text-xs font-bold uppercase text-fg">-</div>
                </div>

                <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                    <div class="text-[10px] text-muted">عدد الصفوف</div>
                    <div id="detailsRows" class="mt-1 text-xs font-bold text-fg">-</div>
                </div>

                <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                    <div class="text-[10px] text-muted">أنشأه</div>
                    <div id="detailsUser" class="mt-1 text-xs font-bold text-fg">-</div>
                </div>

                <div class="col-span-2 rounded-xl border border-white/10 bg-white/5 p-3">
                    <div class="text-[10px] text-muted">وقت الإنشاء</div>
                    <div id="detailsAt" class="mt-1 text-xs font-bold text-fg">-</div>
                </div>

            </div>

            <div
                id="detailsErrorBox"
                class="hidden rounded-xl border border-danger/20 bg-danger/5 p-4"
            >
                <div class="flex items-start gap-3">

                    <i class="fa-solid fa-triangle-exclamation mt-0.5 text-danger"></i>

                    <div class="min-w-0">

                        <div class="text-xs font-bold text-danger">
                            سبب الخطأ
                        </div>

                        <div
                            id="detailsError"
                            class="mt-1 break-words text-[11px] leading-5 text-muted"
                        >
                            -
                        </div>

                    </div>

                </div>
            </div>

            <div class="rounded-xl border border-info/10 bg-info/5 p-3 text-[11px] leading-6 text-muted">
                <i class="fa-solid fa-circle-info ml-1 text-info"></i>
                هذه التفاصيل مأخوذة من سجل التصدير الحالي ولا تقوم بتعديل أي بيانات.
            </div>

            <button
                type="button"
                class="btn btn-secondary w-full"
                data-close-modal
            >
                إغلاق
            </button>

        </div>

    </x-modal>

    {{-- ================================================================
         ERROR MODAL
    ================================================================= --}}
    <x-modal id="exportErrorModal" title="تفاصيل الخطأ" width="32rem">

        <div class="space-y-4">

            <div class="rounded-xl border border-danger/20 bg-danger/5 p-4">

                <div class="flex items-start gap-3">

                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-danger/10 text-danger">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </span>

                    <div>

                        <div class="text-sm font-bold text-fg">
                            فشل إنشاء التقرير
                        </div>

                        <p
                            id="modalErrorText"
                            class="mt-2 break-words text-xs leading-6 text-muted"
                        >
                            -
                        </p>

                    </div>

                </div>

            </div>

            <button
                type="button"
                class="btn btn-secondary w-full"
                data-close-modal
            >
                إغلاق
            </button>

        </div>

    </x-modal>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    /*
    |--------------------------------------------------------------------------
    | Cached DOM references
    |--------------------------------------------------------------------------
    */

    const rows = Array.from(
        document.querySelectorAll('.export-row')
    );

    const searchInput = document.getElementById('historySearch');

    const resultsCounter = document.getElementById('resultsCounter');
    const visibleResults = document.getElementById('visibleResults');

    const checkAll = document.getElementById('checkAll');

    const selectedCount = document.getElementById('selectedCount');
    const footerSelectedCount = document.getElementById('footerSelectedCount');

    const selectVisible = document.getElementById('selectVisible');
    const clearSelection = document.getElementById('clearSelection');
    const copySelected = document.getElementById('copySelected');

    const clientEmptyRow = document.getElementById('clientEmptyRow');

    const detailsModal = document.getElementById('exportDetailsModal');
    const errorModal = document.getElementById('exportErrorModal');

    const detailsIcon = document.getElementById('detailsIcon');
    const detailsTitle = document.getElementById('detailsTitle');
    const detailsType = document.getElementById('detailsType');
    const detailsId = document.getElementById('detailsId');
    const detailsBank = document.getElementById('detailsBank');
    const detailsScope = document.getElementById('detailsScope');
    const detailsFormat = document.getElementById('detailsFormat');
    const detailsRows = document.getElementById('detailsRows');
    const detailsUser = document.getElementById('detailsUser');
    const detailsAt = document.getElementById('detailsAt');

    const detailsErrorBox = document.getElementById('detailsErrorBox');
    const detailsError = document.getElementById('detailsError');

    const modalErrorText = document.getElementById('modalErrorText');

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    const normalize = (value) => {
        return String(value ?? '')
            .trim()
            .toLowerCase();
    };

    const escapeHtml = (value) => {
        const div = document.createElement('div');

        div.textContent = String(value ?? '');

        return div.innerHTML;
    };

    const formatNumber = (value) => {
        const number = Number(value);

        if (!Number.isFinite(number)) {
            return '-';
        }

        return number.toLocaleString('en-US');
    };

    /*
    |--------------------------------------------------------------------------
    | Modal helper
    |--------------------------------------------------------------------------
    */

    const openModal = (modal) => {
        if (!modal) {
            return;
        }

        if (typeof modal.showModal === 'function') {
            modal.showModal();
            return;
        }

        modal.classList.remove('hidden');
    };

    /*
    |--------------------------------------------------------------------------
    | Clipboard
    |--------------------------------------------------------------------------
    */

    const copyText = async (value) => {

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
            // Use fallback below.
        }

        try {

            const textarea = document.createElement('textarea');

            textarea.value = text;

            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';

            document.body.appendChild(textarea);

            textarea.focus();
            textarea.select();

            const success = document.execCommand('copy');

            textarea.remove();

            return success;

        } catch (error) {

            return false;

        }
    };

    /*
    |--------------------------------------------------------------------------
    | Visible rows
    |--------------------------------------------------------------------------
    */

    const getVisibleRows = () => {
        return rows.filter(
            row => !row.classList.contains('hidden')
        );
    };

    /*
    |--------------------------------------------------------------------------
    | Selection
    |--------------------------------------------------------------------------
    */

    const getCheckboxes = () => {
        return rows
            .map(row => row.querySelector('.export-check'))
            .filter(Boolean);
    };

    const getSelectedCheckboxes = () => {
        return getCheckboxes()
            .filter(checkbox => checkbox.checked);
    };

    const refreshSelection = () => {

        const allCheckboxes = getCheckboxes();
        const selectedCheckboxes = getSelectedCheckboxes();

        const selected = selectedCheckboxes.length;

        if (selectedCount) {
            selectedCount.textContent = selected;
        }

        if (footerSelectedCount) {
            footerSelectedCount.textContent = selected;
        }

        if (copySelected) {
            copySelected.disabled = selected === 0;

            copySelected.classList.toggle(
                'opacity-50',
                selected === 0
            );

            copySelected.classList.toggle(
                'cursor-not-allowed',
                selected === 0
            );
        }

        if (!checkAll) {
            return;
        }

        const visibleCheckboxes = getVisibleRows()
            .map(row => row.querySelector('.export-check'))
            .filter(Boolean);

        const visibleSelected = visibleCheckboxes
            .filter(checkbox => checkbox.checked);

        if (!visibleCheckboxes.length) {

            checkAll.checked = false;
            checkAll.indeterminate = false;

            return;
        }

        if (visibleSelected.length === visibleCheckboxes.length) {

            checkAll.checked = true;
            checkAll.indeterminate = false;

        } else if (visibleSelected.length === 0) {

            checkAll.checked = false;
            checkAll.indeterminate = false;

        } else {

            checkAll.checked = false;
            checkAll.indeterminate = true;

        }
    };

    getCheckboxes().forEach((checkbox) => {

        checkbox.addEventListener('change', () => {
            refreshSelection();
        });

    });

    checkAll?.addEventListener('change', () => {

        const visibleCheckboxes = getVisibleRows()
            .map(row => row.querySelector('.export-check'))
            .filter(Boolean);

        visibleCheckboxes.forEach((checkbox) => {
            checkbox.checked = checkAll.checked;
        });

        refreshSelection();
    });

    selectVisible?.addEventListener('click', () => {

        getVisibleRows()
            .map(row => row.querySelector('.export-check'))
            .filter(Boolean)
            .forEach(checkbox => {
                checkbox.checked = true;
            });

        refreshSelection();
    });

    clearSelection?.addEventListener('click', () => {

        getCheckboxes().forEach(checkbox => {
            checkbox.checked = false;
        });

        refreshSelection();
    });

    /*
    |--------------------------------------------------------------------------
    | Search / client-side filtering
    |--------------------------------------------------------------------------
    */

    const applySearch = () => {

        const query = normalize(searchInput?.value);

        let visible = 0;

        rows.forEach((row) => {

            const searchable = [
                row.dataset.id,
                row.dataset.type,
                row.dataset.typeName,
                row.dataset.bank,
                row.dataset.scope,
                row.dataset.format,
                row.dataset.status,
                row.dataset.statusLabel,
                row.dataset.user,
                row.dataset.time,
                row.dataset.rows,
                row.dataset.error,
            ]
                .join(' ')
                .toLowerCase();

            const show = !query || searchable.includes(query);

            row.classList.toggle('hidden', !show);

            if (show) {
                visible++;
            }
        });

        if (resultsCounter) {
            resultsCounter.textContent = visible;
        }

        if (visibleResults) {
            visibleResults.textContent = visible;
        }

        clientEmptyRow?.classList.toggle(
            'hidden',
            visible !== 0 || rows.length === 0
        );

        refreshSelection();
    };

    searchInput?.addEventListener(
        'input',
        applySearch
    );

    document.getElementById('clearHistorySearch')?.addEventListener(
        'click',
        () => {

            if (searchInput) {
                searchInput.value = '';
            }

            applySearch();

            searchInput?.focus();
        }
    );

    document.getElementById('resetClientSearch')?.addEventListener(
        'click',
        () => {

            if (searchInput) {
                searchInput.value = '';
            }

            applySearch();
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Copy selected
    |--------------------------------------------------------------------------
    */

    copySelected?.addEventListener('click', async () => {

        const selectedRows = rows.filter((row) => {

            const checkbox = row.querySelector('.export-check');

            return checkbox?.checked;

        });

        if (!selectedRows.length) {
            return;
        }

        const text = selectedRows.map((row) => {

            return [
                `#${row.dataset.id || ''}`,
                row.dataset.typeName || row.dataset.type || '',
                row.dataset.bank || 'كل البنوك',
                row.dataset.scope || '-',
                (row.dataset.format || '').toUpperCase(),
                row.dataset.rows || '-',
                row.dataset.statusLabel || row.dataset.status || '',
                row.dataset.user || '-',
                row.dataset.time || '-',
            ].join('\t');

        }).join('\n');

        const success = await copyText(text);

        const original = copySelected.innerHTML;

        copySelected.innerHTML = success
            ? '<i class="fa-solid fa-check text-xs"></i> تم النسخ'
            : '<i class="fa-solid fa-xmark text-xs"></i> فشل النسخ';

        setTimeout(() => {
            copySelected.innerHTML = original;
        }, 1400);
    });

    /*
    |--------------------------------------------------------------------------
    | Row-level copy
    |--------------------------------------------------------------------------
    */

    document.addEventListener('click', async (event) => {

        const copyTarget = event.target.closest('[data-copy]');

        if (!copyTarget) {
            return;
        }

        /*
         * Avoid treating action buttons as generic copy elements.
         */
        if (
            copyTarget.classList.contains('copy-export')
        ) {
            return;
        }

        const value = copyTarget.dataset.copy || '';

        if (!value) {
            return;
        }

        const success = await copyText(value);

        const original = copyTarget.innerHTML;

        if (success) {

            copyTarget.innerHTML = `
                <i class="fa-solid fa-check text-brand"></i>
            `;

            setTimeout(() => {
                copyTarget.innerHTML = original;
            }, 1000);

        }
    });

    /*
    |--------------------------------------------------------------------------
    | Copy complete row
    |--------------------------------------------------------------------------
    */

    document.addEventListener('click', async (event) => {

        const button = event.target.closest('.copy-export');

        if (!button) {
            return;
        }

        const row = button.closest('.export-row');

        if (!row) {
            return;
        }

        const text = [
            `#${row.dataset.id || ''}`,
            row.dataset.typeName || row.dataset.type || '',
            row.dataset.bank || 'كل البنوك',
            row.dataset.scope || '-',
            (row.dataset.format || '').toUpperCase(),
            row.dataset.rows || '-',
            row.dataset.statusLabel || row.dataset.status || '',
            row.dataset.user || '-',
            row.dataset.time || '-',
        ].join(' | ');

        const success = await copyText(text);

        const original = button.innerHTML;

        button.innerHTML = success
            ? '<i class="fa-solid fa-check"></i>'
            : '<i class="fa-solid fa-xmark"></i>';

        setTimeout(() => {
            button.innerHTML = original;
        }, 1100);
    });

    /*
    |--------------------------------------------------------------------------
    | Details modal
    |--------------------------------------------------------------------------
    */

    document.addEventListener('click', (event) => {

        const button = event.target.closest('.view-export');

        if (!button) {
            return;
        }

        const row = button.closest('.export-row');

        if (!row) {
            return;
        }

        const type = row.dataset.type || '';

        const iconMap = {
            employees: 'fa-users',
            employee: 'fa-user-tie',
            clients: 'fa-users-viewfinder',
            cases: 'fa-folder-open',
            payments: 'fa-money-bill-transfer',
            promises: 'fa-handshake',
            complaints: 'fa-headset',
            banks: 'fa-building-columns',
            distribution: 'fa-sitemap',
            collection: 'fa-chart-line',
            performance: 'fa-chart-simple',
        };

        if (detailsIcon) {

            detailsIcon.innerHTML = `
                <i class="fa-solid ${escapeHtml(
                    iconMap[type] || 'fa-file-lines'
                )}"></i>
            `;

        }

        if (detailsTitle) {
            detailsTitle.textContent =
                row.dataset.typeName || 'تقرير';
        }

        if (detailsType) {
            detailsType.textContent =
                row.dataset.type || 'غير محدد';
        }

        if (detailsId) {
            detailsId.textContent =
                `#${row.dataset.id || '-'}`;
        }

        if (detailsBank) {
            detailsBank.textContent =
                row.dataset.bank || 'كل البنوك';
        }

        if (detailsScope) {
            detailsScope.textContent =
                row.dataset.scope || '-';
        }

        if (detailsFormat) {
            detailsFormat.textContent =
                (row.dataset.format || '-').toUpperCase();
        }

        if (detailsRows) {
            detailsRows.textContent =
                row.dataset.rows
                    ? formatNumber(row.dataset.rows)
                    : '-';
        }

        if (detailsUser) {
            detailsUser.textContent =
                row.dataset.user || '-';
        }

        if (detailsAt) {
            detailsAt.textContent =
                row.dataset.time || '-';
        }

        const error = row.dataset.error || '';

        if (detailsErrorBox) {
            detailsErrorBox.classList.toggle(
                'hidden',
                !error
            );
        }

        if (detailsError) {
            detailsError.textContent =
                error || '-';
        }

        openModal(detailsModal);
    });

    /*
    |--------------------------------------------------------------------------
    | Error modal
    |--------------------------------------------------------------------------
    */

    document.addEventListener('click', (event) => {

        const button = event.target.closest('.view-error');

        if (!button) {
            return;
        }

        const row = button.closest('.export-row');

        if (!row) {
            return;
        }

        if (modalErrorText) {
            modalErrorText.textContent =
                row.dataset.error || button.textContent.trim() || '-';
        }

        openModal(errorModal);
    });

    /*
    |--------------------------------------------------------------------------
    | Retry protection
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('.retry-form').forEach((form) => {

        form.addEventListener('submit', (event) => {

            const button = form.querySelector('.retry-button');

            if (!button) {
                return;
            }

            if (button.dataset.submitting === '1') {
                event.preventDefault();

                return;
            }

            button.dataset.submitting = '1';
            button.disabled = true;

            button.classList.add(
                'opacity-70',
                'cursor-not-allowed'
            );

            button.innerHTML = `
                <i class="fa-solid fa-spinner fa-spin text-xs"></i>
                جاري الإعادة...
            `;

        });

    });

    /*
    |--------------------------------------------------------------------------
    | Delete protection
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('.delete-form').forEach((form) => {

        form.addEventListener('submit', (event) => {

            if (form.dataset.submitting === '1') {
                event.preventDefault();

                return;
            }

            const reportName =
                form.dataset.reportName || 'هذا التقرير';

            const confirmed = window.confirm(
                `هل تريد حذف "${reportName}" من سجل التصدير؟\n\nهذا الإجراء سيحذف سجل التصدير فقط ولن يحذف البيانات الأصلية.`
            );

            if (!confirmed) {
                event.preventDefault();

                return;
            }

            form.dataset.submitting = '1';

            const button =
                form.querySelector('.delete-export');

            if (button) {

                button.disabled = true;

                button.classList.add(
                    'opacity-70',
                    'cursor-not-allowed'
                );

                button.innerHTML = `
                    <i class="fa-solid fa-spinner fa-spin text-xs"></i>
                `;

            }

        });

    });

    /*
    |--------------------------------------------------------------------------
    | Print current results
    |--------------------------------------------------------------------------
    */

    const printExports = (filteredOnly = false) => {

        const printableRows = filteredOnly
            ? getVisibleRows()
            : rows;

        const htmlRows = printableRows.map((row) => {

            return `
                <tr>
                    <td>${escapeHtml(row.dataset.id || '-')}</td>
                    <td>${escapeHtml(row.dataset.typeName || '-')}</td>
                    <td>${escapeHtml(row.dataset.bank || 'كل البنوك')}</td>
                    <td>${escapeHtml(row.dataset.scope || '-')}</td>
                    <td>${escapeHtml((row.dataset.format || '-').toUpperCase())}</td>
                    <td>${escapeHtml(row.dataset.rows || '-')}</td>
                    <td>${escapeHtml(row.dataset.statusLabel || row.dataset.status || '-')}</td>
                    <td>${escapeHtml(row.dataset.user || '-')}</td>
                    <td>${escapeHtml(row.dataset.time || '-')}</td>
                </tr>
            `;

        }).join('');

        const printWindow = window.open(
            '',
            '_blank',
            'width=1400,height=900'
        );

        if (!printWindow) {
            return;
        }

        printWindow.document.write(`
            <!doctype html>

            <html lang="ar" dir="rtl">

            <head>

                <meta charset="utf-8">

                <title>سجل التصدير</title>

                <style>

                    body {
                        font-family: Arial, sans-serif;
                        padding: 30px;
                        color: #111;
                    }

                    h1 {
                        margin: 0;
                    }

                    .subtitle {
                        color: #666;
                        margin-top: 6px;
                    }

                    .meta {
                        margin-top: 15px;
                        font-size: 12px;
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
                        font-size: 12px;
                    }

                    th {
                        background: #f3f3f3;
                        font-weight: bold;
                    }

                    tr {
                        page-break-inside: avoid;
                    }

                    @media print {
                        body {
                            padding: 10px;
                        }
                    }

                </style>

            </head>

            <body>

                <h1>سجل التصدير</h1>

                <div class="subtitle">
                    سجل التقارير التي تم إنشاؤها
                </div>

                <div class="meta">
                    عدد النتائج: ${printableRows.length}
                </div>

                <table>

                    <thead>

                        <tr>
                            <th>#</th>
                            <th>التقرير</th>
                            <th>البنك</th>
                            <th>الفترة</th>
                            <th>الصيغة</th>
                            <th>الصفوف</th>
                            <th>الحالة</th>
                            <th>أنشأه</th>
                            <th>الوقت</th>
                        </tr>

                    </thead>

                    <tbody>

                        ${htmlRows || `
                            <tr>
                                <td colspan="9">
                                    لا توجد نتائج
                                </td>
                            </tr>
                        `}

                    </tbody>

                </table>

            </body>

            </html>
        `);

        printWindow.document.close();

        printWindow.focus();

        setTimeout(() => {
            printWindow.print();
        }, 250);
    };

    document.getElementById('printExports')
        ?.addEventListener('click', () => {
            printExports(false);
        });

    document.getElementById('printFiltered')
        ?.addEventListener('click', () => {
            printExports(true);
        });

    /*
    |--------------------------------------------------------------------------
    | Client-side CSV export
    |--------------------------------------------------------------------------
    | This exports the currently visible records without requiring
    | a new backend route.
    */

    document.getElementById('exportCsv')
        ?.addEventListener('click', () => {

            const exportRows = getVisibleRows();

            if (!exportRows.length) {
                return;
            }

            const header = [
                'ID',
                'Report',
                'Bank',
                'Period',
                'Format',
                'Rows',
                'Status',
                'Created By',
                'Created At',
            ];

            const csvRows = [
                header,
                ...exportRows.map((row) => [
                    row.dataset.id || '',
                    row.dataset.typeName || '',
                    row.dataset.bank || '',
                    row.dataset.scope || '',
                    (row.dataset.format || '').toUpperCase(),
                    row.dataset.rows || '',
                    row.dataset.statusLabel || row.dataset.status || '',
                    row.dataset.user || '',
                    row.dataset.time || '',
                ]),
            ];

            const csv = csvRows
                .map((row) => row.map((value) => {

                    const text = String(value ?? '');

                    return `"${text.replace(/"/g, '""')}"`;

                }).join(','))
                .join('\r\n');

            /*
             * UTF-8 BOM makes Arabic CSV open correctly in Excel.
             */
            const blob = new Blob(
                ['\uFEFF' + csv],
                {
                    type: 'text/csv;charset=utf-8;'
                }
            );

            const url = URL.createObjectURL(blob);

            const link = document.createElement('a');

            link.href = url;
            link.download = `reports-export-history-${Date.now()}.csv`;

            document.body.appendChild(link);

            link.click();

            link.remove();

            URL.revokeObjectURL(url);
        });

    /*
    |--------------------------------------------------------------------------
    | Refresh
    |--------------------------------------------------------------------------
    */

    document.getElementById('refreshExports')
        ?.addEventListener('click', () => {

            const button =
                document.getElementById('refreshExports');

            if (!button) {
                return;
            }

            button.disabled = true;

            button.classList.add('opacity-70');

            button.innerHTML = `
                <i class="fa-solid fa-spinner fa-spin text-xs"></i>
                تحديث...
            `;

            window.location.reload();
        });

    /*
    |--------------------------------------------------------------------------
    | Keyboard shortcuts
    |--------------------------------------------------------------------------
    */

    document.addEventListener('keydown', (event) => {

        /*
         * Ctrl + K → search.
         */
        if (
            (event.ctrlKey || event.metaKey) &&
            event.key.toLowerCase() === 'k'
        ) {

            event.preventDefault();

            searchInput?.focus();

        }

        /*
         * Escape → clear client search.
         */
        if (event.key === 'Escape') {

            const active =
                document.activeElement;

            if (active === searchInput) {

                if (searchInput) {
                    searchInput.value = '';
                }

                applySearch();

            }

        }

    });

    /*
    |--------------------------------------------------------------------------
    | Initial state
    |--------------------------------------------------------------------------
    */

    refreshSelection();
    applySearch();

});
</script>
@endpush