{{-- resources/views/reports/index.blade.php --}}
@extends('layouts.app')

@section('title', 'التقارير')

@php
    /*
    |--------------------------------------------------------------------------
    | Safe data normalization
    |--------------------------------------------------------------------------
    | Keep the existing backend contract untouched.
    | Everything below works with the variables already supplied by the page.
    */

    $reportTypes = collect($types ?? []);
    $reportBanks = collect($banks ?? []);
    $reportFormats = collect($formats ?? []);
    $reportExports = collect($exports ?? []);

    $totalExports = $reportExports->count();

    $completedExports = $reportExports->filter(
        fn ($export) => ($export->status ?? null) === 'completed'
    )->count();

    $pendingExports = $reportExports->filter(
        fn ($export) => in_array(($export->status ?? null), ['pending', 'processing', 'queued'])
    )->count();

    $failedExports = $reportExports->filter(
        fn ($export) => in_array(($export->status ?? null), ['failed', 'error'])
    )->count();

    $otherExports = max(
        0,
        $totalExports - $completedExports - $pendingExports - $failedExports
    );

    $xlsxExports = $reportExports->filter(
        fn ($export) => strtolower((string) ($export->format ?? '')) === 'xlsx'
    )->count();

    $pdfExports = $reportExports->filter(
        fn ($export) => strtolower((string) ($export->format ?? '')) === 'pdf'
    )->count();

    $csvExports = $reportExports->filter(
        fn ($export) => strtolower((string) ($export->format ?? '')) === 'csv'
    )->count();

    /*
    |--------------------------------------------------------------------------
    | Report type metadata
    |--------------------------------------------------------------------------
    | This is presentation-only. It does not create new backend report types.
    */
    $reportTypeMeta = [
        'employees' => [
            'icon' => 'fa-users',
            'color' => 'info',
            'description' => 'تقرير عن الموظفين وأداء التحصيل.',
        ],
        'employee' => [
            'icon' => 'fa-user-tie',
            'color' => 'info',
            'description' => 'تقرير تفصيلي عن أداء موظف محدد.',
        ],
        'clients' => [
            'icon' => 'fa-users-viewfinder',
            'color' => 'brand',
            'description' => 'تقرير عن العملاء والحالات المسجلة.',
        ],
        'clients' => [
            'icon' => 'fa-users-viewfinder',
            'color' => 'brand',
            'description' => 'تقرير عن العملاء والحالات المسجلة.',
        ],
        'cases' => [
            'icon' => 'fa-folder-open',
            'color' => 'warning',
            'description' => 'تقرير عن حالات التحصيل ومراحلها.',
        ],
        'payments' => [
            'icon' => 'fa-money-bill-transfer',
            'color' => 'success',
            'description' => 'تقرير عن المدفوعات والتحصيلات.',
        ],
        'promises' => [
            'icon' => 'fa-handshake',
            'color' => 'cyan',
            'description' => 'تقرير عن وعود السداد ومتابعتها.',
        ],
        'complaints' => [
            'icon' => 'fa-headset',
            'color' => 'danger',
            'description' => 'تقرير عن الشكاوى وحالات التعامل معها.',
        ],
        'banks' => [
            'icon' => 'fa-building-columns',
            'color' => 'info',
            'description' => 'تقرير تجميعي خاص بالبنوك.',
        ],
        'distribution' => [
            'icon' => 'fa-sitemap',
            'color' => 'accent',
            'description' => 'تقرير عن توزيع الحالات على الموظفين.',
        ],
        'collection' => [
            'icon' => 'fa-chart-line',
            'color' => 'brand',
            'description' => 'تقرير شامل عن عمليات التحصيل.',
        ],
        'performance' => [
            'icon' => 'fa-chart-simple',
            'color' => 'warning',
            'description' => 'تقرير أداء ومؤشرات الموظفين.',
        ],
    ];

    /*
    |--------------------------------------------------------------------------
    | Frontend report presets
    |--------------------------------------------------------------------------
    | These only populate the existing fields.
    */
    $reportPresets = [
        'today' => [
            'label' => 'تقرير اليوم',
            'icon' => 'fa-calendar-day',
            'description' => 'من بداية اليوم حتى اليوم.',
        ],
        'week' => [
            'label' => 'هذا الأسبوع',
            'icon' => 'fa-calendar-week',
            'description' => 'تقرير عن الفترة الحالية للأسبوع.',
        ],
        'month' => [
            'label' => 'هذا الشهر',
            'icon' => 'fa-calendar-days',
            'description' => 'تقرير عن الشهر الحالي.',
        ],
        'last_month' => [
            'label' => 'الشهر السابق',
            'icon' => 'fa-calendar-minus',
            'description' => 'تقرير عن الشهر السابق.',
        ],
    ];

    /*
    |--------------------------------------------------------------------------
    | Export format metadata
    |--------------------------------------------------------------------------
    */
    $formatMeta = [
        'xlsx' => [
            'icon' => 'fa-file-excel',
            'label' => 'Excel',
            'description' => 'مناسب للتحليل والجداول والمعالجة في Excel.',
        ],
        'pdf' => [
            'icon' => 'fa-file-pdf',
            'label' => 'PDF',
            'description' => 'مناسب للطباعة والمشاركة والتقارير الرسمية.',
        ],
        'csv' => [
            'icon' => 'fa-file-csv',
            'label' => 'CSV',
            'description' => 'مناسب للتكامل وتحليل البيانات الخام.',
        ],
    ];

    /*
    |--------------------------------------------------------------------------
    | Export data for JavaScript
    |--------------------------------------------------------------------------
    */
    $exportJsData = $reportExports->values()->map(function ($export) use ($types) {
        $typeKey = (string) ($export->type ?? '');
        $format = strtolower((string) ($export->format ?? ''));

        return [
            'type' => $typeKey,
            'type_name' => $types[$typeKey] ?? $typeKey ?: 'تقرير',
            'bank' => (string) ($export->bank ?? 'كل البنوك'),
            'format' => $format,
            'status' => (string) ($export->status ?? ''),
            'at' => (string) ($export->at ?? ''),
        ];
    })->all();
@endphp

@section('content')

    <x-page-header title="التقارير" subtitle="إنشاء وتصدير التقارير بصيغة Excel أو PDF" icon="fa-file-lines">
        <x-slot:actions>
            <button
                type="button"
                id="printReports"
                class="btn btn-outline-secondary btn-sm"
                title="طباعة سجل التقارير"
            >
                <i class="fa-solid fa-print text-xs"></i>
                طباعة
            </button>

            <button
                type="button"
                id="refreshReports"
                class="btn btn-outline-info btn-sm"
                title="تحديث واجهة التقارير"
            >
                <i class="fa-solid fa-arrows-rotate text-xs"></i>
                تحديث
            </button>

            <a href="{{ route('reports.exports') }}" class="btn btn-outline-info btn-sm">
                <i class="fa-solid fa-clock-rotate-left text-xs"></i>
                سجل التصدير
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-flash />

    {{-- ================================================================
         REPORT CENTER OVERVIEW
    ================================================================= --}}
    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <x-kpi-card
            label="إجمالي التقارير"
            :value="$totalExports"
            icon="fa-file-lines"
            color="info"
        />

        <x-kpi-card
            label="مكتملة"
            :value="$completedExports"
            icon="fa-circle-check"
            color="brand"
        />

        <x-kpi-card
            label="قيد المعالجة"
            :value="$pendingExports"
            icon="fa-spinner"
            color="warning"
        />

        <x-kpi-card
            label="فاشلة"
            :value="$failedExports"
            icon="fa-circle-xmark"
            color="danger"
        />

    </section>

    {{-- ================================================================
         REPORT HEALTH / FORMAT SUMMARY
    ================================================================= --}}
    <section class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-3">

        <x-section-card title="حالة التقارير" icon="fa-chart-pie" color="brand">

            <div class="grid grid-cols-2 gap-3">

                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs text-muted">مكتملة</span>
                        <i class="fa-solid fa-circle-check text-brand"></i>
                    </div>

                    <div class="mt-2 text-xl font-bold text-fg">
                        {{ $completedExports }}
                    </div>
                </div>

                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs text-muted">قيد التنفيذ</span>
                        <i class="fa-solid fa-spinner text-warning"></i>
                    </div>

                    <div class="mt-2 text-xl font-bold text-fg">
                        {{ $pendingExports }}
                    </div>
                </div>

                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs text-muted">فاشلة</span>
                        <i class="fa-solid fa-circle-xmark text-danger"></i>
                    </div>

                    <div class="mt-2 text-xl font-bold text-fg">
                        {{ $failedExports }}
                    </div>
                </div>

                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs text-muted">أخرى</span>
                        <i class="fa-solid fa-layer-group text-info"></i>
                    </div>

                    <div class="mt-2 text-xl font-bold text-fg">
                        {{ $otherExports }}
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
                            <div class="text-sm font-semibold text-fg">Excel</div>
                            <div class="text-[11px] text-muted">XLSX</div>
                        </div>
                    </div>

                    <span class="text-sm font-bold text-fg">{{ $xlsxExports }}</span>
                </div>

                <div class="flex items-center justify-between rounded-xl border border-white/10 bg-white/5 px-4 py-3">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-danger/10 text-danger">
                            <i class="fa-solid fa-file-pdf"></i>
                        </span>
                        <div>
                            <div class="text-sm font-semibold text-fg">PDF</div>
                            <div class="text-[11px] text-muted">PDF</div>
                        </div>
                    </div>

                    <span class="text-sm font-bold text-fg">{{ $pdfExports }}</span>
                </div>

                <div class="flex items-center justify-between rounded-xl border border-white/10 bg-white/5 px-4 py-3">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-info/10 text-info">
                            <i class="fa-solid fa-file-csv"></i>
                        </span>
                        <div>
                            <div class="text-sm font-semibold text-fg">CSV</div>
                            <div class="text-[11px] text-muted">CSV</div>
                        </div>
                    </div>

                    <span class="text-sm font-bold text-fg">{{ $csvExports }}</span>
                </div>

            </div>

        </x-section-card>

        <x-section-card title="معلومات مركز التقارير" icon="fa-circle-info" color="cyan">

            <div class="space-y-3 text-xs text-muted">

                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-wand-magic-sparkles mt-0.5 text-brand"></i>
                    <span>اختر نوع التقرير ثم حدد البنك والفترة الزمنية والصيغة المطلوبة.</span>
                </div>

                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-calendar-days mt-0.5 text-info"></i>
                    <span>يمكن استخدام الاختصارات الزمنية لتعبئة الفترة بسرعة.</span>
                </div>

                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-clock-rotate-left mt-0.5 text-warning"></i>
                    <span>يمكن مراجعة التقارير السابقة من سجل التصدير.</span>
                </div>

                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-shield-halved mt-0.5 text-cyan"></i>
                    <span>التقارير تعتمد على الصلاحيات والبيانات المتاحة للمستخدم.</span>
                </div>

            </div>

        </x-section-card>

    </section>

    {{-- ================================================================
         QUICK REPORT PRESETS
    ================================================================= --}}
    <section class="mb-6">

        <x-panel title="اختصارات التقارير" icon="fa-bolt">

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">

                @foreach($reportPresets as $presetKey => $preset)

                    <button
                        type="button"
                        class="report-preset group rounded-xl border border-white/10 bg-white/5 p-4 text-right transition hover:border-brand/40 hover:bg-brand/5"
                        data-preset="{{ $presetKey }}"
                    >
                        <div class="flex items-center gap-3">

                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand/10 text-brand transition group-hover:bg-brand/15">
                                <i class="fa-solid {{ $preset['icon'] }}"></i>
                            </span>

                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-fg">
                                    {{ $preset['label'] }}
                                </div>

                                <div class="mt-1 text-[11px] leading-5 text-muted">
                                    {{ $preset['description'] }}
                                </div>
                            </div>

                        </div>
                    </button>

                @endforeach

            </div>

        </x-panel>

    </section>

    {{-- ================================================================
         MAIN REPORT CREATION + LATEST EXPORTS
    ================================================================= --}}
    <section class="grid grid-cols-1 gap-4 xl:grid-cols-3">

        {{-- New report --}}
        <x-section-card title="تقرير جديد" icon="fa-wand-magic-sparkles" color="brand">

            <form
                id="reportForm"
                method="POST"
                action="{{ route('reports.store') }}"
                class="space-y-4"
            >
                @csrf

                {{-- Selected report summary --}}
                <div
                    id="reportSummary"
                    class="hidden rounded-xl border border-brand/20 bg-brand/5 p-4"
                >
                    <div class="mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-circle-info text-brand"></i>
                        <span class="text-xs font-bold text-fg">ملخص التقرير</span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-[11px]">
                        <div>
                            <span class="text-muted">النوع:</span>
                            <b id="summaryType" class="text-fg">-</b>
                        </div>

                        <div>
                            <span class="text-muted">البنك:</span>
                            <b id="summaryBank" class="text-fg">كل البنوك</b>
                        </div>

                        <div>
                            <span class="text-muted">من:</span>
                            <b id="summaryFrom" class="text-fg">-</b>
                        </div>

                        <div>
                            <span class="text-muted">إلى:</span>
                            <b id="summaryTo" class="text-fg">-</b>
                        </div>

                        <div class="col-span-2">
                            <span class="text-muted">الصيغة:</span>
                            <b id="summaryFormat" class="text-fg">-</b>
                        </div>
                    </div>
                </div>

                <x-select-field
                    name="type"
                    label="نوع التقرير"
                    :options="$types"
                    placeholder="اختر التقرير..."
                />

                {{-- Dynamic type information --}}
                <div
                    id="reportTypeInfo"
                    class="hidden rounded-xl border border-white/10 bg-white/5 p-3"
                >
                    <div class="flex items-start gap-3">
                        <span
                            id="reportTypeIcon"
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-info/10 text-info"
                        >
                            <i class="fa-solid fa-file-lines"></i>
                        </span>

                        <div>
                            <div id="reportTypeName" class="text-xs font-semibold text-fg">
                                -
                            </div>

                            <div id="reportTypeDescription" class="mt-1 text-[11px] leading-5 text-muted">
                                -
                            </div>
                        </div>
                    </div>
                </div>

                <x-select-field
                    name="bank"
                    label="البنك"
                    :options="$banks"
                    placeholder="كل البنوك"
                />

                <div class="grid grid-cols-2 gap-3">
                    <x-form-field name="from" label="من تاريخ" type="date" />
                    <x-form-field name="to" label="إلى تاريخ" type="date" />
                </div>

                {{-- Date shortcuts --}}
                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <span class="text-[11px] font-semibold text-fg">اختيار سريع للفترة</span>

                        <button
                            type="button"
                            id="clearDates"
                            class="text-[10px] text-muted transition hover:text-fg"
                        >
                            مسح
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-2">

                        <button
                            type="button"
                            data-date-shortcut="today"
                            class="date-shortcut btn btn-secondary btn-sm"
                        >
                            اليوم
                        </button>

                        <button
                            type="button"
                            data-date-shortcut="week"
                            class="date-shortcut btn btn-secondary btn-sm"
                        >
                            هذا الأسبوع
                        </button>

                        <button
                            type="button"
                            data-date-shortcut="month"
                            class="date-shortcut btn btn-secondary btn-sm"
                        >
                            هذا الشهر
                        </button>

                        <button
                            type="button"
                            data-date-shortcut="last_month"
                            class="date-shortcut btn btn-secondary btn-sm"
                        >
                            الشهر السابق
                        </button>

                    </div>
                </div>

                <x-select-field
                    name="format"
                    label="الصيغة"
                    :options="$formats"
                    value="xlsx"
                />

                {{-- Format information --}}
                <div
                    id="formatInfo"
                    class="rounded-xl border border-white/10 bg-white/5 p-3"
                >
                    <div class="flex items-start gap-3">

                        <span
                            id="formatIcon"
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-success/10 text-success"
                        >
                            <i class="fa-solid fa-file-excel"></i>
                        </span>

                        <div>
                            <div id="formatName" class="text-xs font-semibold text-fg">
                                Excel
                            </div>

                            <div id="formatDescription" class="mt-1 text-[11px] leading-5 text-muted">
                                مناسب للتحليل والجداول والمعالجة في Excel.
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Validation hint --}}
                <div
                    id="dateValidation"
                    class="hidden rounded-lg border border-danger/20 bg-danger/5 px-3 py-2 text-[11px] text-danger"
                >
                    <i class="fa-solid fa-triangle-exclamation ml-1"></i>
                    تاريخ البداية يجب أن يكون قبل أو مساويًا لتاريخ النهاية.
                </div>

                <button
                    id="generateReportBtn"
                    type="submit"
                    class="btn btn-primary w-full"
                >
                    <i class="fa-solid fa-file-export text-xs"></i>
                    <span id="generateReportText">إنشاء التقرير</span>
                </button>

                <div class="text-center text-[10px] leading-5 text-dim">
                    <i class="fa-solid fa-keyboard ml-1"></i>
                    Ctrl + Enter لإنشاء التقرير
                </div>

            </form>

        </x-section-card>

        {{-- Latest exports --}}
        <div class="xl:col-span-2">

            <x-panel title="آخر التقارير" icon="fa-clock-rotate-left">

                {{-- Export statistics toolbar --}}
                <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">

                    <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                        <div class="text-[10px] text-muted">الإجمالي</div>
                        <div id="visibleExportsCount" class="mt-1 text-lg font-bold text-fg">
                            {{ $totalExports }}
                        </div>
                    </div>

                    <div class="rounded-xl border border-brand/10 bg-brand/5 p-3">
                        <div class="text-[10px] text-muted">مكتملة</div>
                        <div id="visibleCompletedCount" class="mt-1 text-lg font-bold text-brand">
                            {{ $completedExports }}
                        </div>
                    </div>

                    <div class="rounded-xl border border-warning/10 bg-warning/5 p-3">
                        <div class="text-[10px] text-muted">قيد التنفيذ</div>
                        <div id="visiblePendingCount" class="mt-1 text-lg font-bold text-warning">
                            {{ $pendingExports }}
                        </div>
                    </div>

                    <div class="rounded-xl border border-danger/10 bg-danger/5 p-3">
                        <div class="text-[10px] text-muted">فاشلة</div>
                        <div id="visibleFailedCount" class="mt-1 text-lg font-bold text-danger">
                            {{ $failedExports }}
                        </div>
                    </div>

                </div>

                {{-- Search/filter toolbar --}}
                <div class="mb-4 rounded-xl border border-white/10 bg-white/5 p-3">

                    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">

                        <div class="relative md:col-span-1">
                            <i class="fa-solid fa-magnifying-glass pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-dim"></i>

                            <input
                                id="exportSearch"
                                type="search"
                                class="form-input pr-9"
                                placeholder="بحث في التقارير..."
                                autocomplete="off"
                            >
                        </div>

                        <select
                            id="exportStatusFilter"
                            class="form-input"
                            aria-label="فلترة الحالة"
                        >
                            <option value="">كل الحالات</option>
                            <option value="completed">مكتمل</option>
                            <option value="pending">قيد التنفيذ</option>
                            <option value="processing">قيد المعالجة</option>
                            <option value="queued">في الانتظار</option>
                            <option value="failed">فاشل</option>
                            <option value="error">خطأ</option>
                        </select>

                        <select
                            id="exportFormatFilter"
                            class="form-input"
                            aria-label="فلترة الصيغة"
                        >
                            <option value="">كل الصيغ</option>
                            <option value="xlsx">XLSX</option>
                            <option value="pdf">PDF</option>
                            <option value="csv">CSV</option>
                        </select>

                    </div>

                    <div class="mt-3 flex flex-wrap items-center justify-between gap-2">

                        <div class="flex items-center gap-2 text-[11px] text-muted">
                            <i class="fa-solid fa-filter text-info"></i>
                            <span id="exportFilterStatus">عرض جميع التقارير</span>
                        </div>

                        <button
                            type="button"
                            id="clearExportFilters"
                            class="btn btn-secondary btn-sm"
                        >
                            <i class="fa-solid fa-filter-circle-xmark text-xs"></i>
                            مسح الفلاتر
                        </button>

                    </div>

                </div>

                <div class="table-wrap">

                    <table class="data-table whitespace-nowrap">

                        <thead>
                            <tr>
                                <th>التقرير</th>
                                <th>البنك</th>
                                <th>الصيغة</th>
                                <th>الحالة</th>
                                <th>الوقت</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody id="exportsTableBody">

                            @forelse($exports as $export)

                                @php
                                    $exportType = (string) ($export->type ?? '');
                                    $exportFormat = strtolower((string) ($export->format ?? ''));
                                    $exportStatus = (string) ($export->status ?? '');

                                    $meta = $reportTypeMeta[$exportType] ?? [
                                        'icon' => 'fa-file-lines',
                                        'color' => 'info',
                                        'description' => 'تقرير من تقارير النظام.',
                                    ];

                                    $formatInfo = $formatMeta[$exportFormat] ?? [
                                        'icon' => 'fa-file',
                                        'label' => strtoupper($exportFormat ?: 'FILE'),
                                        'description' => 'صيغة التقرير.',
                                    ];
                                @endphp

                                <tr
                                    class="export-row"
                                    data-type="{{ strtolower($exportType) }}"
                                    data-type-name="{{ strtolower($types[$exportType] ?? $exportType) }}"
                                    data-bank="{{ strtolower((string) ($export->bank ?? '')) }}"
                                    data-format="{{ $exportFormat }}"
                                    data-status="{{ strtolower($exportStatus) }}"
                                    data-time="{{ strtolower((string) ($export->at ?? '')) }}"
                                >

                                    <td>

                                        <div class="flex items-center gap-3">

                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-info/10 text-info">
                                                <i class="fa-solid {{ $meta['icon'] }}"></i>
                                            </span>

                                            <div class="min-w-0">

                                                <div class="max-w-[240px] truncate font-semibold text-fg">
                                                    {{ $types[$exportType] ?? $exportType ?: 'تقرير' }}
                                                </div>

                                                <div class="mt-1 text-[10px] text-dim">
                                                    {{ $exportType ?: 'غير محدد' }}
                                                </div>

                                            </div>

                                        </div>

                                    </td>

                                    <td>
                                        <span class="text-xs">
                                            {{ $export->bank ?? 'كل البنوك' }}
                                        </span>
                                    </td>

                                    <td>

                                        <span class="inline-flex items-center gap-2 rounded-lg border border-white/10 bg-white/5 px-2.5 py-1.5 text-[10px] font-bold uppercase text-fg">

                                            <i class="fa-solid {{ $formatInfo['icon'] }}"></i>

                                            {{ $exportFormat ?: 'FILE' }}

                                        </span>

                                    </td>

                                    <td>
                                        <x-status-badge
                                            type="export"
                                            :status="$exportStatus"
                                        />
                                    </td>

                                    <td>
                                        <span class="text-xs text-muted">
                                            {{ $export->at ?? '-' }}
                                        </span>
                                    </td>

                                    <td>

                                        <div class="flex items-center justify-end gap-1">

                                            <button
                                                type="button"
                                                class="icon-btn icon-btn-info view-export"
                                                title="عرض التفاصيل"
                                                aria-label="عرض تفاصيل التقرير"
                                            >
                                                <i class="fa-solid fa-eye"></i>
                                            </button>

                                            <button
                                                type="button"
                                                class="icon-btn icon-btn-secondary copy-export"
                                                title="نسخ اسم التقرير"
                                                aria-label="نسخ اسم التقرير"
                                                data-copy="{{ $types[$exportType] ?? $exportType ?: 'تقرير' }}"
                                            >
                                                <i class="fa-regular fa-copy"></i>
                                            </button>

                                            @if($exportStatus === 'completed')
                                                {{-- TODO: real download route --}}
                                                <a
                                                    href="#"
                                                    class="btn btn-outline-success btn-sm"
                                                    title="تحميل التقرير"
                                                >
                                                    <i class="fa-solid fa-download text-xs"></i>
                                                    تحميل
                                                </a>
                                            @endif

                                        </div>

                                    </td>

                                </tr>

                            @empty

                                <tr id="exportsEmptyRow">

                                    <td colspan="6" class="py-12 text-center">

                                        <div class="mx-auto flex max-w-sm flex-col items-center">

                                            <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/5 text-dim">
                                                <i class="fa-solid fa-file-circle-xmark text-xl"></i>
                                            </span>

                                            <h3 class="mt-4 text-sm font-bold text-fg">
                                                لا توجد تقارير بعد
                                            </h3>

                                            <p class="mt-2 text-xs leading-6 text-muted">
                                                قم بإنشاء أول تقرير باستخدام النموذج الموجود بجانب سجل التقارير.
                                            </p>

                                        </div>

                                    </td>

                                </tr>

                            @endforelse

                            <tr id="noFilterResults" class="hidden">

                                <td colspan="6" class="py-10 text-center">

                                    <div class="mx-auto flex max-w-sm flex-col items-center">

                                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/5 text-dim">
                                            <i class="fa-solid fa-magnifying-glass-minus"></i>
                                        </span>

                                        <h3 class="mt-3 text-sm font-bold text-fg">
                                            لا توجد نتائج
                                        </h3>

                                        <p class="mt-1 text-xs text-muted">
                                            لم يتم العثور على تقارير تطابق الفلاتر الحالية.
                                        </p>

                                    </div>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

                <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-3">

                    <button
                        type="button"
                        id="printFilteredReports"
                        class="btn btn-secondary btn-sm"
                    >
                        <i class="fa-solid fa-print text-xs"></i>
                        طباعة النتائج
                    </button>

                    <button
                        type="button"
                        id="copyVisibleReports"
                        class="btn btn-secondary btn-sm"
                    >
                        <i class="fa-regular fa-copy text-xs"></i>
                        نسخ النتائج
                    </button>

                    <a
                        href="{{ route('reports.exports') }}"
                        class="btn btn-secondary btn-sm"
                    >
                        عرض سجل التصدير الكامل
                        <i class="fa-solid fa-chevron-left text-[9px]"></i>
                    </a>

                </div>

            </x-panel>

        </div>

    </section>

    {{-- ================================================================
         REPORT CAPABILITIES
    ================================================================= --}}
    <section class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-3">

        <x-section-card title="تقارير التشغيل" icon="fa-gears" color="info">

            <div class="space-y-2">

                <div class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/5">
                    <i class="fa-solid fa-users text-info"></i>
                    <span class="text-xs text-fg">الموظفين وأداء التحصيل</span>
                </div>

                <div class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/5">
                    <i class="fa-solid fa-building-columns text-info"></i>
                    <span class="text-xs text-fg">تقارير البنوك</span>
                </div>

                <div class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/5">
                    <i class="fa-solid fa-sitemap text-info"></i>
                    <span class="text-xs text-fg">التوزيع والتعيينات</span>
                </div>

            </div>

        </x-section-card>

        <x-section-card title="تقارير التحصيل" icon="fa-money-bill-trend-up" color="brand">

            <div class="space-y-2">

                <div class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/5">
                    <i class="fa-solid fa-money-bill-transfer text-brand"></i>
                    <span class="text-xs text-fg">المدفوعات والتحصيلات</span>
                </div>

                <div class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/5">
                    <i class="fa-solid fa-handshake text-brand"></i>
                    <span class="text-xs text-fg">وعود السداد</span>
                </div>

                <div class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/5">
                    <i class="fa-solid fa-chart-line text-brand"></i>
                    <span class="text-xs text-fg">مؤشرات التحصيل</span>
                </div>

            </div>

        </x-section-card>

        <x-section-card title="مستقبل التقارير" icon="fa-chart-column" color="warning">

            <div class="space-y-2">

                <div class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/5">
                    <i class="fa-solid fa-clock text-warning"></i>
                    <span class="text-xs text-fg">تقارير مجدولة</span>
                </div>

                <div class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/5">
                    <i class="fa-solid fa-wand-magic-sparkles text-warning"></i>
                    <span class="text-xs text-fg">منشئ تقارير متقدم</span>
                </div>

                <div class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/5">
                    <i class="fa-solid fa-chart-area text-warning"></i>
                    <span class="text-xs text-fg">تحليلات واتجاهات التحصيل</span>
                </div>

            </div>

        </x-section-card>

    </section>

    {{-- ================================================================
         EXPORT DETAILS MODAL
    ================================================================= --}}
    <x-modal id="exportDetailsModal" title="تفاصيل التقرير" width="34rem">

        <div class="space-y-4">

            <div class="flex items-center gap-3 rounded-xl border border-white/10 bg-white/5 p-4">

                <span
                    id="modalExportIcon"
                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-info/10 text-info"
                >
                    <i class="fa-solid fa-file-lines"></i>
                </span>

                <div class="min-w-0">
                    <div id="modalExportName" class="truncate text-sm font-bold text-fg">
                        تقرير
                    </div>

                    <div id="modalExportType" class="mt-1 text-[10px] text-muted">
                        -
                    </div>
                </div>

            </div>

            <div class="grid grid-cols-2 gap-3">

                <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                    <div class="text-[10px] text-muted">البنك</div>
                    <div id="modalExportBank" class="mt-1 text-xs font-semibold text-fg">
                        -
                    </div>
                </div>

                <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                    <div class="text-[10px] text-muted">الصيغة</div>
                    <div id="modalExportFormat" class="mt-1 text-xs font-semibold uppercase text-fg">
                        -
                    </div>
                </div>

                <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                    <div class="text-[10px] text-muted">الحالة</div>
                    <div id="modalExportStatus" class="mt-1 text-xs font-semibold text-fg">
                        -
                    </div>
                </div>

                <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                    <div class="text-[10px] text-muted">الوقت</div>
                    <div id="modalExportAt" class="mt-1 text-xs font-semibold text-fg">
                        -
                    </div>
                </div>

            </div>

            <div class="rounded-xl border border-info/10 bg-info/5 p-3 text-[11px] leading-6 text-muted">
                <i class="fa-solid fa-circle-info ml-1 text-info"></i>
                هذه النافذة تعرض المعلومات المتاحة في سجل التصدير الحالي دون تغيير أي بيانات.
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
    | Cached elements
    |--------------------------------------------------------------------------
    | Cache DOM references once to avoid repeated document-wide searches.
    */
    const form = document.getElementById('reportForm');

    const typeSelect = form?.querySelector('[name="type"]');
    const bankSelect = form?.querySelector('[name="bank"]');
    const fromInput = form?.querySelector('[name="from"]');
    const toInput = form?.querySelector('[name="to"]');
    const formatSelect = form?.querySelector('[name="format"]');

    const reportSummary = document.getElementById('reportSummary');
    const summaryType = document.getElementById('summaryType');
    const summaryBank = document.getElementById('summaryBank');
    const summaryFrom = document.getElementById('summaryFrom');
    const summaryTo = document.getElementById('summaryTo');
    const summaryFormat = document.getElementById('summaryFormat');

    const reportTypeInfo = document.getElementById('reportTypeInfo');
    const reportTypeIcon = document.getElementById('reportTypeIcon');
    const reportTypeName = document.getElementById('reportTypeName');
    const reportTypeDescription = document.getElementById('reportTypeDescription');

    const formatIcon = document.getElementById('formatIcon');
    const formatName = document.getElementById('formatName');
    const formatDescription = document.getElementById('formatDescription');

    const dateValidation = document.getElementById('dateValidation');

    const generateButton = document.getElementById('generateReportBtn');
    const generateText = document.getElementById('generateReportText');

    const exportSearch = document.getElementById('exportSearch');
    const exportStatusFilter = document.getElementById('exportStatusFilter');
    const exportFormatFilter = document.getElementById('exportFormatFilter');

    const clearExportFilters = document.getElementById('clearExportFilters');

    const visibleExportsCount = document.getElementById('visibleExportsCount');
    const visibleCompletedCount = document.getElementById('visibleCompletedCount');
    const visiblePendingCount = document.getElementById('visiblePendingCount');
    const visibleFailedCount = document.getElementById('visibleFailedCount');

    const exportFilterStatus = document.getElementById('exportFilterStatus');
    const noFilterResults = document.getElementById('noFilterResults');

    const rows = Array.from(document.querySelectorAll('.export-row'));

    const exportData = {{ Js::from($exportJsData) }};

    const typeMeta = {{ Js::from($reportTypeMeta) }};
    const formatMeta = {{ Js::from($formatMeta) }};

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    const normalize = (value) => String(value ?? '').trim().toLowerCase();

    const getSelectText = (select) => {
        if (!select || select.selectedIndex < 0) {
            return '';
        }

        return select.options[select.selectedIndex]?.textContent?.trim() || '';
    };

    const escapeHtml = (value) => {
        const div = document.createElement('div');
        div.textContent = String(value ?? '');
        return div.innerHTML;
    };

    const todayDate = () => {
        const date = new Date();

        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    };

    const formatDate = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    };

    const startOfWeek = () => {
        const date = new Date();
        const day = date.getDay();

        /*
         * Convert Sunday=0 to Monday-based week.
         */
        const diff = day === 0 ? -6 : 1 - day;

        date.setDate(date.getDate() + diff);

        return formatDate(date);
    };

    const startOfMonth = () => {
        const date = new Date(
            new Date().getFullYear(),
            new Date().getMonth(),
            1
        );

        return formatDate(date);
    };

    const startOfLastMonth = () => {
        const now = new Date();

        const date = new Date(
            now.getFullYear(),
            now.getMonth() - 1,
            1
        );

        return formatDate(date);
    };

    const endOfLastMonth = () => {
        const now = new Date();

        const date = new Date(
            now.getFullYear(),
            now.getMonth(),
            0
        );

        return formatDate(date);
    };

    /*
    |--------------------------------------------------------------------------
    | Report type information
    |--------------------------------------------------------------------------
    */

    const updateTypeInfo = () => {
        if (!typeSelect) {
            return;
        }

        const key = normalize(typeSelect.value);

        if (!key) {
            reportTypeInfo?.classList.add('hidden');

            if (summaryType) {
                summaryType.textContent = '-';
            }

            updateSummary();
            return;
        }

        const meta = typeMeta[key] || {
            icon: 'fa-file-lines',
            color: 'info',
            description: 'تقرير من تقارير النظام.',
        };

        const name = getSelectText(typeSelect);

        if (reportTypeName) {
            reportTypeName.textContent = name || key;
        }

        if (reportTypeDescription) {
            reportTypeDescription.textContent = meta.description;
        }

        if (reportTypeIcon) {
            reportTypeIcon.innerHTML = `<i class="fa-solid ${escapeHtml(meta.icon)}"></i>`;
        }

        reportTypeInfo?.classList.remove('hidden');

        updateSummary();
    };

    /*
    |--------------------------------------------------------------------------
    | Format information
    |--------------------------------------------------------------------------
    */

    const updateFormatInfo = () => {
        if (!formatSelect) {
            return;
        }

        const key = normalize(formatSelect.value);

        const meta = formatMeta[key] || {
            icon: 'fa-file',
            label: key ? key.toUpperCase() : 'FILE',
            description: 'صيغة التقرير.',
        };

        if (formatName) {
            formatName.textContent = meta.label;
        }

        if (formatDescription) {
            formatDescription.textContent = meta.description;
        }

        if (formatIcon) {
            formatIcon.innerHTML = `<i class="fa-solid ${escapeHtml(meta.icon)}"></i>`;
        }

        updateSummary();
    };

    /*
    |--------------------------------------------------------------------------
    | Report summary
    |--------------------------------------------------------------------------
    */

    const updateSummary = () => {
        if (!reportSummary) {
            return;
        }

        const hasType = Boolean(typeSelect?.value);
        const hasBank = Boolean(bankSelect?.value);
        const hasFrom = Boolean(fromInput?.value);
        const hasTo = Boolean(toInput?.value);
        const hasFormat = Boolean(formatSelect?.value);

        if (summaryType) {
            summaryType.textContent = hasType
                ? getSelectText(typeSelect)
                : '-';
        }

        if (summaryBank) {
            summaryBank.textContent = hasBank
                ? getSelectText(bankSelect)
                : 'كل البنوك';
        }

        if (summaryFrom) {
            summaryFrom.textContent = hasFrom
                ? fromInput.value
                : '-';
        }

        if (summaryTo) {
            summaryTo.textContent = hasTo
                ? toInput.value
                : '-';
        }

        if (summaryFormat) {
            summaryFormat.textContent = hasFormat
                ? getSelectText(formatSelect)
                : '-';
        }

        reportSummary.classList.toggle(
            'hidden',
            !(hasType || hasBank || hasFrom || hasTo || hasFormat)
        );
    };

    /*
    |--------------------------------------------------------------------------
    | Date validation
    |--------------------------------------------------------------------------
    */

    const validateDates = () => {
        if (!fromInput || !toInput || !dateValidation) {
            return true;
        }

        const from = fromInput.value;
        const to = toInput.value;

        const invalid = Boolean(from && to && from > to);

        dateValidation.classList.toggle('hidden', !invalid);

        if (invalid) {
            fromInput.setAttribute('aria-invalid', 'true');
            toInput.setAttribute('aria-invalid', 'true');
        } else {
            fromInput.removeAttribute('aria-invalid');
            toInput.removeAttribute('aria-invalid');
        }

        return !invalid;
    };

    /*
    |--------------------------------------------------------------------------
    | Date shortcuts
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('.date-shortcut').forEach((button) => {
        button.addEventListener('click', () => {
            const shortcut = button.dataset.dateShortcut;

            const today = todayDate();

            if (!fromInput || !toInput) {
                return;
            }

            if (shortcut === 'today') {
                fromInput.value = today;
                toInput.value = today;
            }

            if (shortcut === 'week') {
                fromInput.value = startOfWeek();
                toInput.value = today;
            }

            if (shortcut === 'month') {
                fromInput.value = startOfMonth();
                toInput.value = today;
            }

            if (shortcut === 'last_month') {
                fromInput.value = startOfLastMonth();
                toInput.value = endOfLastMonth();
            }

            validateDates();
            updateSummary();
        });
    });

    document.getElementById('clearDates')?.addEventListener('click', () => {
        if (fromInput) {
            fromInput.value = '';
        }

        if (toInput) {
            toInput.value = '';
        }

        validateDates();
        updateSummary();
    });

    /*
    |--------------------------------------------------------------------------
    | Form events
    |--------------------------------------------------------------------------
    */

    typeSelect?.addEventListener('change', updateTypeInfo);
    bankSelect?.addEventListener('change', updateSummary);
    formatSelect?.addEventListener('change', updateFormatInfo);
    fromInput?.addEventListener('change', () => {
        validateDates();
        updateSummary();
    });
    toInput?.addEventListener('change', () => {
        validateDates();
        updateSummary();
    });

    /*
    |--------------------------------------------------------------------------
    | Generate report
    |--------------------------------------------------------------------------
    */

    let formSubmitting = false;

    form?.addEventListener('submit', (event) => {
        if (!validateDates()) {
            event.preventDefault();

            fromInput?.focus();

            return;
        }

        if (formSubmitting) {
            event.preventDefault();

            return;
        }

        formSubmitting = true;

        if (generateButton) {
            generateButton.disabled = true;
            generateButton.classList.add('opacity-70', 'cursor-not-allowed');
        }

        if (generateText) {
            generateText.textContent = 'جاري إنشاء التقرير...';
        }
    });

    /*
    |--------------------------------------------------------------------------
    | Export table filtering
    |--------------------------------------------------------------------------
    */

    const applyExportFilters = () => {
        const query = normalize(exportSearch?.value);
        const status = normalize(exportStatusFilter?.value);
        const format = normalize(exportFormatFilter?.value);

        let visible = 0;
        let completed = 0;
        let pending = 0;
        let failed = 0;

        rows.forEach((row) => {
            const searchable = [
                row.dataset.type,
                row.dataset.typeName,
                row.dataset.bank,
                row.dataset.format,
                row.dataset.status,
                row.dataset.time,
            ].join(' ');

            const matchesSearch = !query || searchable.includes(query);
            const matchesStatus = !status || normalize(row.dataset.status) === status;
            const matchesFormat = !format || normalize(row.dataset.format) === format;

            const show = matchesSearch && matchesStatus && matchesFormat;

            row.classList.toggle('hidden', !show);

            if (!show) {
                return;
            }

            visible++;

            const rowStatus = normalize(row.dataset.status);

            if (rowStatus === 'completed') {
                completed++;
            }

            if (['pending', 'processing', 'queued'].includes(rowStatus)) {
                pending++;
            }

            if (['failed', 'error'].includes(rowStatus)) {
                failed++;
            }
        });

        if (visibleExportsCount) {
            visibleExportsCount.textContent = visible;
        }

        if (visibleCompletedCount) {
            visibleCompletedCount.textContent = completed;
        }

        if (visiblePendingCount) {
            visiblePendingCount.textContent = pending;
        }

        if (visibleFailedCount) {
            visibleFailedCount.textContent = failed;
        }

        noFilterResults?.classList.toggle(
            'hidden',
            visible !== 0 || rows.length === 0
        );

        const activeFilters = [];

        if (query) {
            activeFilters.push(`بحث: ${query}`);
        }

        if (status) {
            activeFilters.push(`الحالة: ${status}`);
        }

        if (format) {
            activeFilters.push(`الصيغة: ${format.toUpperCase()}`);
        }

        if (exportFilterStatus) {
            exportFilterStatus.textContent = activeFilters.length
                ? activeFilters.join(' • ')
                : 'عرض جميع التقارير';
        }
    };

    exportSearch?.addEventListener('input', applyExportFilters);
    exportStatusFilter?.addEventListener('change', applyExportFilters);
    exportFormatFilter?.addEventListener('change', applyExportFilters);

    clearExportFilters?.addEventListener('click', () => {
        if (exportSearch) {
            exportSearch.value = '';
        }

        if (exportStatusFilter) {
            exportStatusFilter.value = '';
        }

        if (exportFormatFilter) {
            exportFormatFilter.value = '';
        }

        applyExportFilters();
    });

    /*
    |--------------------------------------------------------------------------
    | Copy report name
    |--------------------------------------------------------------------------
    */

    const copyText = async (value) => {
        const text = String(value ?? '');

        if (!text) {
            return false;
        }

        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(text);
                return true;
            }
        } catch (error) {
            // Fallback below.
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

    document.addEventListener('click', async (event) => {
        const copyButton = event.target.closest('.copy-export');

        if (!copyButton) {
            return;
        }

        const value = copyButton.dataset.copy || '';

        const success = await copyText(value);

        const original = copyButton.innerHTML;

        copyButton.innerHTML = success
            ? '<i class="fa-solid fa-check"></i>'
            : '<i class="fa-solid fa-xmark"></i>';

        setTimeout(() => {
            copyButton.innerHTML = original;
        }, 1200);
    });

    /*
    |--------------------------------------------------------------------------
    | Export details modal
    |--------------------------------------------------------------------------
    */

    const detailsModal = document.getElementById('exportDetailsModal');

    const modalExportIcon = document.getElementById('modalExportIcon');
    const modalExportName = document.getElementById('modalExportName');
    const modalExportType = document.getElementById('modalExportType');
    const modalExportBank = document.getElementById('modalExportBank');
    const modalExportFormat = document.getElementById('modalExportFormat');
    const modalExportStatus = document.getElementById('modalExportStatus');
    const modalExportAt = document.getElementById('modalExportAt');

    const openDetailsModal = () => {
        if (!detailsModal) {
            return;
        }

        if (typeof detailsModal.showModal === 'function') {
            detailsModal.showModal();
        } else {
            detailsModal.classList.remove('hidden');
        }
    };

    document.addEventListener('click', (event) => {
        const button = event.target.closest('.view-export');

        if (!button) {
            return;
        }

        const row = button.closest('.export-row');

        if (!row) {
            return;
        }

        const typeKey = row.dataset.type || '';
        const typeName = row.dataset.typeName || 'تقرير';
        const bank = row.dataset.bank || 'كل البنوك';
        const format = row.dataset.format || '-';
        const status = row.dataset.status || '-';
        const at = row.dataset.time || '-';

        const meta = typeMeta[typeKey] || {
            icon: 'fa-file-lines',
        };

        if (modalExportName) {
            modalExportName.textContent = typeName;
        }

        if (modalExportType) {
            modalExportType.textContent = typeKey || 'غير محدد';
        }

        if (modalExportBank) {
            modalExportBank.textContent = bank;
        }

        if (modalExportFormat) {
            modalExportFormat.textContent = format.toUpperCase();
        }

        if (modalExportStatus) {
            modalExportStatus.textContent = status;
        }

        if (modalExportAt) {
            modalExportAt.textContent = at;
        }

        if (modalExportIcon) {
            modalExportIcon.innerHTML = `<i class="fa-solid ${escapeHtml(meta.icon)}"></i>`;
        }

        openDetailsModal();
    });

    /*
    |--------------------------------------------------------------------------
    | Print reports
    |--------------------------------------------------------------------------
    */

    const printReports = (filteredOnly = false) => {
        const printableRows = filteredOnly
            ? rows.filter(row => !row.classList.contains('hidden'))
            : rows;

        const rowsHtml = printableRows.map((row) => {
            const cells = Array.from(row.querySelectorAll('td'));

            return `
                <tr>
                    <td>${escapeHtml(cells[0]?.innerText || '-')}</td>
                    <td>${escapeHtml(cells[1]?.innerText || '-')}</td>
                    <td>${escapeHtml(cells[2]?.innerText || '-')}</td>
                    <td>${escapeHtml(cells[3]?.innerText || '-')}</td>
                    <td>${escapeHtml(cells[4]?.innerText || '-')}</td>
                </tr>
            `;
        }).join('');

        const printWindow = window.open('', '_blank', 'width=1200,height=800');

        if (!printWindow) {
            return;
        }

        printWindow.document.write(`
            <!doctype html>
            <html lang="ar" dir="rtl">
            <head>
                <meta charset="utf-8">
                <title>سجل التقارير</title>

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
                    }

                    th {
                        background: #f3f3f3;
                    }

                    .meta {
                        margin-top: 15px;
                        font-size: 12px;
                        color: #666;
                    }
                </style>
            </head>

            <body>

                <h1>سجل التقارير</h1>

                <p>
                    مركز التقارير
                </p>

                <div class="meta">
                    إجمالي النتائج: ${printableRows.length}
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>التقرير</th>
                            <th>البنك</th>
                            <th>الصيغة</th>
                            <th>الحالة</th>
                            <th>الوقت</th>
                        </tr>
                    </thead>

                    <tbody>
                        ${rowsHtml || `
                            <tr>
                                <td colspan="5">لا توجد نتائج</td>
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

    document.getElementById('printReports')?.addEventListener('click', () => {
        printReports(false);
    });

    document.getElementById('printFilteredReports')?.addEventListener('click', () => {
        printReports(true);
    });

    /*
    |--------------------------------------------------------------------------
    | Copy visible reports
    |--------------------------------------------------------------------------
    */

    document.getElementById('copyVisibleReports')?.addEventListener('click', async () => {
        const visibleRows = rows.filter(
            row => !row.classList.contains('hidden')
        );

        const text = visibleRows.map((row) => {
            const cells = Array.from(row.querySelectorAll('td'));

            return [
                cells[0]?.innerText?.trim() || '',
                cells[1]?.innerText?.trim() || '',
                cells[2]?.innerText?.trim() || '',
                cells[3]?.innerText?.trim() || '',
                cells[4]?.innerText?.trim() || '',
            ].join('\t');
        }).join('\n');

        if (!text) {
            return;
        }

        const success = await copyText(text);

        const button = document.getElementById('copyVisibleReports');

        if (!button) {
            return;
        }

        const original = button.innerHTML;

        button.innerHTML = success
            ? '<i class="fa-solid fa-check text-xs"></i> تم النسخ'
            : '<i class="fa-solid fa-xmark text-xs"></i> فشل النسخ';

        setTimeout(() => {
            button.innerHTML = original;
        }, 1400);
    });

    /*
    |--------------------------------------------------------------------------
    | Refresh
    |--------------------------------------------------------------------------
    | Reloads the current page without changing the current backend contract.
    */

    document.getElementById('refreshReports')?.addEventListener('click', () => {
        const button = document.getElementById('refreshReports');

        if (button) {
            button.disabled = true;
            button.classList.add('opacity-70');

            button.innerHTML = `
                <i class="fa-solid fa-spinner fa-spin text-xs"></i>
                تحديث...
            `;
        }

        window.location.reload();
    });

    /*
    |--------------------------------------------------------------------------
    | Keyboard shortcuts
    |--------------------------------------------------------------------------
    */

    document.addEventListener('keydown', (event) => {
        /*
         * Ctrl + K → focus report type.
         */
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();

            typeSelect?.focus();
        }

        /*
         * Ctrl + Enter → submit report form.
         */
        if ((event.ctrlKey || event.metaKey) && event.key === 'Enter') {
            event.preventDefault();

            if (form) {
                form.requestSubmit();
            }
        }

        /*
         * Escape → clear export search/filter.
         */
        if (event.key === 'Escape') {
            const activeElement = document.activeElement;

            if (
                activeElement === exportSearch ||
                activeElement === exportStatusFilter ||
                activeElement === exportFormatFilter
            ) {
                clearExportFilters?.click();
            }
        }
    });

    /*
    |--------------------------------------------------------------------------
    | Initial state
    |--------------------------------------------------------------------------
    */

    updateTypeInfo();
    updateFormatInfo();
    validateDates();
    updateSummary();
    applyExportFilters();
});
</script>
@endpush