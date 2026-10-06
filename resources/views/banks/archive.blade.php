{{-- resources/views/banks/archive.blade.php : one archived scope (read-only) --}}
@extends('layouts.app')

@section('title', 'نطاق ' . $archive->label . ' - ' . $bank->name)

@php
    $caseStatus = [
        'active' => ['نشط', 'badge-info'],
        'paid'   => ['مسدد', 'badge-success'],
        'legal'  => ['قضائي', 'badge-danger'],
    ];

    /*
    |--------------------------------------------------------------------------
    | Safe archive calculations
    |--------------------------------------------------------------------------
    */

    $caseCollection = method_exists($cases, 'items')
        ? collect($cases->items())
        : collect($cases);

    $visibleCaseCount = $caseCollection->count();

    $totalCaseDebt = $caseCollection->sum(
        fn ($case) => (float) ($case->debt ?? 0)
    );

    $totalCaseCollected = $caseCollection->sum(
        fn ($case) => (float) ($case->collected ?? 0)
    );

    $totalCaseRemaining = $caseCollection->sum(
        fn ($case) => (float) ($case->remaining ?? 0)
    );

    $activeCases = $caseCollection->where('status', 'active')->count();
    $paidCases = $caseCollection->where('status', 'paid')->count();
    $legalCases = $caseCollection->where('status', 'legal')->count();

    $sampleCollectionRate = $totalCaseDebt > 0
        ? ($totalCaseCollected / $totalCaseDebt) * 100
        : 0;

    $remainingRate = $totalCaseDebt > 0
        ? ($totalCaseRemaining / $totalCaseDebt) * 100
        : 0;

    $archiveRate = min(100, max(0, (float) ($archive->rate ?? 0)));

    /*
    |--------------------------------------------------------------------------
    | Data prepared outside JavaScript
    |--------------------------------------------------------------------------
    | This avoids Blade parser problems with nested @json() closures.
    */

    $caseData = $caseCollection->map(function ($case) use ($caseStatus) {
        $status = $caseStatus[$case->status] ?? ['غير محدد', 'badge-neutral'];

        return [
            'id'        => $case->id ?? null,
            'code'      => $case->code ?? '',
            'name'      => $case->name ?? '',
            'phone'     => $case->phone ?? '',
            'debt'      => $case->debt ?? 0,
            'collected' => $case->collected ?? 0,
            'remaining' => $case->remaining ?? 0,
            'status'    => $case->status ?? '',
            'statusText' => $status[0],
            'employee'  => $case->employee ?? '',
        ];
    })->values();
@endphp

@section('content')

    {{-- =========================================================
         HEADER
         ========================================================= --}}
    <x-bank-header
        :bank="$bank"
        :title="'نطاق ' . $archive->label"
        subtitle="مؤرشف (للقراءة فقط)"
        :back="route('banks.archives.index', $bank->id)"
    >
        <x-slot:actions>

            {{-- Existing button preserved --}}
            <a
                href="#"
                class="btn btn-outline-success btn-sm"
                id="archiveDownload"
            >
                <i class="fa-solid fa-file-excel"></i>
                تحميل Excel
            </a>

            <button
                type="button"
                class="btn btn-outline-info btn-sm"
                id="archivePrint"
            >
                <i class="fa-solid fa-print"></i>
                طباعة
            </button>

        </x-slot:actions>
    </x-bank-header>

    {{-- =========================================================
         READ ONLY NOTICE
         ========================================================= --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-warning/20 bg-warning/[0.05] px-4 py-3 text-sm text-warning">

        <div class="flex items-center gap-3">
            <i class="fa-solid fa-lock"></i>

            <span>
                هذا النطاق مؤرشف منذ {{ $archive->at }}
                بواسطة {{ $archive->by }}،
                ولا يمكن تعديل بياناته أو توزيعه.
            </span>
        </div>

        <span class="badge badge-neutral">
            <i class="fa-solid fa-eye ml-1 text-[9px]"></i>
            قراءة فقط
        </span>

    </div>

    {{-- =========================================================
         ORIGINAL MAIN METRICS
         ========================================================= --}}
    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <x-metric-card
            label="عدد الحالات"
            :value="$archive->cases"
            icon="fa-briefcase"
            color="info"
        />

        <x-metric-card
            label="إجمالي المديونية"
            :value="number_format($archive->debt)"
            unit="EGP"
            icon="fa-wallet"
            color="accent"
        />

        <x-metric-card
            label="المحصل"
            :value="number_format($archive->collected)"
            unit="EGP"
            icon="fa-money-bill-wave"
            color="brand"
        />

        <x-metric-card
            label="نسبة التحصيل"
            :value="$archive->rate . '%'"
            icon="fa-bullseye"
            color="cyan"
        />

    </section>

    {{-- =========================================================
         SECONDARY METRICS
         ========================================================= --}}
    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="card">
            <div class="flex items-center justify-between gap-3">

                <div>
                    <div class="text-[11px] text-dim">
                        الحالات المعروضة
                    </div>

                    <div class="mt-1 text-xl font-bold text-fg">
                        {{ number_format($visibleCaseCount) }}
                    </div>

                    <div class="mt-1 text-[10px] text-muted">
                        من العينة الحالية
                    </div>
                </div>

                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-info/10 text-info">
                    <i class="fa-solid fa-list-check"></i>
                </span>

            </div>
        </div>

        <div class="card">
            <div class="flex items-center justify-between gap-3">

                <div>
                    <div class="text-[11px] text-dim">
                        الحالات المسددة
                    </div>

                    <div class="mt-1 text-xl font-bold text-brand">
                        {{ number_format($paidCases) }}
                    </div>

                    <div class="mt-1 text-[10px] text-muted">
                        من الحالات المعروضة
                    </div>
                </div>

                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand/10 text-brand">
                    <i class="fa-solid fa-circle-check"></i>
                </span>

            </div>
        </div>

        <div class="card">
            <div class="flex items-center justify-between gap-3">

                <div>
                    <div class="text-[11px] text-dim">
                        حالات قضائية
                    </div>

                    <div class="mt-1 text-xl font-bold text-danger">
                        {{ number_format($legalCases) }}
                    </div>

                    <div class="mt-1 text-[10px] text-muted">
                        تحتاج متابعة قانونية
                    </div>
                </div>

                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-danger/10 text-danger">
                    <i class="fa-solid fa-gavel"></i>
                </span>

            </div>
        </div>

        <div class="card">
            <div class="flex items-center justify-between gap-3">

                <div>
                    <div class="text-[11px] text-dim">
                        المتبقي
                    </div>

                    <div class="mt-1 text-xl font-bold text-warning">
                        EGP {{ number_format($archive->remaining ?? $totalCaseRemaining) }}
                    </div>

                    <div class="mt-1 text-[10px] text-muted">
                        من إجمالي النطاق
                    </div>
                </div>

                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-warning/10 text-warning">
                    <i class="fa-solid fa-money-bill-transfer"></i>
                </span>

            </div>
        </div>

    </section>

    {{-- =========================================================
         ARCHIVE PERFORMANCE
         ========================================================= --}}
    <section class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-3">

        <div class="card lg:col-span-2">

            <div class="mb-4 flex items-center justify-between gap-3">

                <div>
                    <h2 class="text-sm font-bold text-fg">
                        أداء النطاق المؤرشف
                    </h2>

                    <p class="mt-1 text-[11px] text-muted">
                        ملخص سريع لأداء التحصيل داخل هذا الأرشيف.
                    </p>
                </div>

                <span class="badge badge-neutral">
                    {{ number_format($archiveRate, 1) }}%
                </span>

            </div>

            <div class="mb-2 flex items-center justify-between text-[11px] text-muted">
                <span>نسبة التحصيل</span>
                <span class="font-semibold text-brand">
                    {{ number_format($archiveRate, 1) }}%
                </span>
            </div>

            <div class="progress">
                <div
                    class="h-full rounded-full bg-brand"
                    style="width: {{ $archiveRate }}%"
                ></div>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">

                <div class="rounded-xl border border-line bg-surface p-3 text-center">
                    <div class="text-[10px] text-dim">المديونية</div>
                    <div class="mt-1 text-sm font-bold text-fg">
                        EGP {{ number_format($archive->debt) }}
                    </div>
                </div>

                <div class="rounded-xl border border-line bg-surface p-3 text-center">
                    <div class="text-[10px] text-dim">المحصل</div>
                    <div class="mt-1 text-sm font-bold text-brand">
                        EGP {{ number_format($archive->collected) }}
                    </div>
                </div>

                <div class="rounded-xl border border-line bg-surface p-3 text-center">
                    <div class="text-[10px] text-dim">المتبقي</div>
                    <div class="mt-1 text-sm font-bold text-warning">
                        EGP {{ number_format($archive->remaining ?? 0) }}
                    </div>
                </div>

                <div class="rounded-xl border border-line bg-surface p-3 text-center">
                    <div class="text-[10px] text-dim">نسبة المتبقي</div>
                    <div class="mt-1 text-sm font-bold text-warning">
                        {{ number_format(max(0, 100 - $archiveRate), 1) }}%
                    </div>
                </div>

            </div>

        </div>

        <div class="card">

            <div class="mb-4">
                <h2 class="text-sm font-bold text-fg">
                    توزيع الحالات
                </h2>

                <p class="mt-1 text-[11px] text-muted">
                    حسب الحالة النهائية المحفوظة في الأرشيف.
                </p>
            </div>

            <div class="space-y-3">

                <div>
                    <div class="mb-1 flex items-center justify-between text-[11px]">
                        <span class="text-muted">نشط</span>
                        <span class="font-semibold text-info">
                            {{ $activeCases }}
                        </span>
                    </div>

                    <div class="progress">
                        <div
                            class="h-full rounded-full bg-info"
                            style="width: {{ $visibleCaseCount > 0 ? min(100, ($activeCases / $visibleCaseCount) * 100) : 0 }}%"
                        ></div>
                    </div>
                </div>

                <div>
                    <div class="mb-1 flex items-center justify-between text-[11px]">
                        <span class="text-muted">مسدد</span>
                        <span class="font-semibold text-brand">
                            {{ $paidCases }}
                        </span>
                    </div>

                    <div class="progress">
                        <div
                            class="h-full rounded-full bg-brand"
                            style="width: {{ $visibleCaseCount > 0 ? min(100, ($paidCases / $visibleCaseCount) * 100) : 0 }}%"
                        ></div>
                    </div>
                </div>

                <div>
                    <div class="mb-1 flex items-center justify-between text-[11px]">
                        <span class="text-muted">قضائي</span>
                        <span class="font-semibold text-danger">
                            {{ $legalCases }}
                        </span>
                    </div>

                    <div class="progress">
                        <div
                            class="h-full rounded-full bg-danger"
                            style="width: {{ $visibleCaseCount > 0 ? min(100, ($legalCases / $visibleCaseCount) * 100) : 0 }}%"
                        ></div>
                    </div>
                </div>

            </div>

        </div>

    </section>

    {{-- =========================================================
         ORIGINAL SEARCH
         ========================================================= --}}
    <form
        method="GET"
        action="{{ route('banks.archives.show', [$bank->id, $archive->id]) }}"
        class="card filter-bar"
        id="archiveCasesFilter"
    >

        <div class="min-w-[240px] flex-1">

            <label for="search" class="sr-only">
                بحث
            </label>

            <div class="relative">

                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-dim"></i>

                <input
                    id="search"
                    name="search"
                    type="search"
                    value="{{ $search }}"
                    placeholder="ابحث بالاسم أو كود العميل..."
                    class="form-input pl-9"
                    autocomplete="off"
                >

            </div>

        </div>

        <div class="w-full sm:w-44">

            <label for="statusFilter" class="form-label">
                الحالة
            </label>

            <select
                id="statusFilter"
                class="form-input"
            >
                <option value="">كل الحالات</option>
                <option value="active">نشط</option>
                <option value="paid">مسدد</option>
                <option value="legal">قضائي</option>
            </select>

        </div>

        <button
            type="submit"
            class="btn btn-primary"
        >
            <i class="fa-solid fa-magnifying-glass text-xs"></i>
            بحث
        </button>

        <button
            type="button"
            class="btn btn-secondary"
            id="clearLocalFilters"
            title="مسح البحث المحلي"
        >
            <i class="fa-solid fa-xmark"></i>
        </button>

        <a
            href="{{ route('banks.archives.show', [$bank->id, $archive->id]) }}"
            class="btn btn-secondary"
            title="إعادة ضبط"
        >
            <i class="fa-solid fa-rotate-right"></i>
        </a>

    </form>

    {{-- =========================================================
         RESULT / TOOLBAR
         ========================================================= --}}
    <div class="mb-3 flex flex-wrap items-center justify-between gap-3">

        <div class="flex flex-wrap items-center gap-2">

            <span class="badge badge-neutral">
                <i class="fa-solid fa-box-archive ml-1 text-[9px]"></i>
                {{ $archive->label }}
            </span>

            <span class="text-xs text-muted">
                النتائج:
                <b id="visibleCaseCount" class="text-fg">
                    {{ $visibleCaseCount }}
                </b>
            </span>

            <span class="text-xs text-muted">
                المحدد:
                <b id="selectedCaseCount" class="text-fg">
                    0
                </b>
            </span>

        </div>

        <div class="flex items-center gap-2">

            <button
                type="button"
                id="selectAllCases"
                class="icon-btn icon-btn-info"
                title="تحديد الكل"
            >
                <i class="fa-solid fa-check-double"></i>
            </button>

            <button
                type="button"
                id="clearCaseSelection"
                class="icon-btn icon-btn-warning"
                title="إلغاء التحديد"
            >
                <i class="fa-solid fa-square-xmark"></i>
            </button>

            <button
                type="button"
                id="copySelectedCases"
                class="icon-btn icon-btn-cyan"
                title="نسخ المحدد"
            >
                <i class="fa-solid fa-copy"></i>
            </button>

            <button
                type="button"
                id="exportCases"
                class="btn btn-outline-success btn-sm"
            >
                <i class="fa-solid fa-file-excel text-xs"></i>
                تصدير
            </button>

            <button
                type="button"
                id="printCases"
                class="btn btn-outline-info btn-sm"
            >
                <i class="fa-solid fa-print text-xs"></i>
                طباعة
            </button>

        </div>

    </div>

    {{-- =========================================================
         TABLE
         ========================================================= --}}
    <div class="table-wrap">

        <table
            class="data-table whitespace-nowrap"
            id="archiveCasesTable"
        >

            <thead>

                <tr>

                    <th class="w-10">
                        <input
                            id="masterCaseCheck"
                            type="checkbox"
                            class="accent-brand"
                            aria-label="تحديد كل الحالات"
                        >
                    </th>

                    <th>كود العميل</th>
                    <th>اسم العميل</th>
                    <th>الهاتف</th>
                    <th>المديونية</th>
                    <th>المحصل</th>
                    <th>المتبقي</th>
                    <th>الحالة النهائية</th>
                    <th>الموظف</th>
                    <th>الإجراءات</th>

                </tr>

            </thead>

            <tbody>

                @forelse($cases as $case)

                    @php
                        $status = $caseStatus[$case->status] ?? ['غير محدد', 'badge-neutral'];
                    @endphp

                    <tr
                        data-case-row
                        data-case-id="{{ $case->id ?? '' }}"
                        data-case-search="{{ strtolower(($case->code ?? '') . ' ' . ($case->name ?? '') . ' ' . ($case->phone ?? '') . ' ' . ($case->employee ?? '')) }}"
                        data-case-status="{{ $case->status ?? '' }}"
                    >

                        {{-- Selection --}}
                        <td>

                            <input
                                type="checkbox"
                                class="case-row-check accent-brand"
                                value="{{ $case->id ?? $case->code }}"
                                aria-label="تحديد {{ $case->name }}"
                            >

                        </td>

                        {{-- Code --}}
                        <td>

                            <button
                                type="button"
                                class="badge badge-info font-mono"
                                data-copy-code="{{ $case->code }}"
                                title="نسخ كود العميل"
                            >
                                {{ $case->code }}
                            </button>

                        </td>

                        {{-- Name --}}
                        <td>

                            <button
                                type="button"
                                class="font-semibold text-fg transition hover:text-brand"
                                data-case-details="{{ $case->id ?? $case->code }}"
                            >
                                {{ $case->name }}
                            </button>

                        </td>

                        {{-- Phone --}}
                        <td>

                            <button
                                type="button"
                                dir="ltr"
                                class="text-info transition hover:text-brand"
                                data-copy-phone="{{ $case->phone }}"
                            >
                                {{ $case->phone }}
                            </button>

                        </td>

                        {{-- Debt --}}
                        <td>
                            EGP {{ number_format($case->debt) }}
                        </td>

                        {{-- Collected --}}
                        <td class="font-semibold text-brand">
                            EGP {{ number_format($case->collected) }}
                        </td>

                        {{-- Remaining --}}
                        <td
                            @class([
                                'font-semibold' => $case->remaining > 0,
                                'text-danger' => $case->remaining > 0,
                                'text-brand' => $case->remaining <= 0,
                            ])
                        >
                            EGP {{ number_format($case->remaining) }}
                        </td>

                        {{-- Status --}}
                        <td>

                            <span class="badge {{ $status[1] }}">
                                {{ $status[0] }}
                            </span>

                        </td>

                        {{-- Employee --}}
                        <td>
                            {{ $case->employee }}
                        </td>

                        {{-- Actions --}}
                        <td>

                            <div class="flex items-center gap-2">

                                <button
                                    type="button"
                                    class="icon-btn icon-btn-info"
                                    title="تفاصيل الحالة"
                                    data-case-details="{{ $case->id ?? $case->code }}"
                                >
                                    <i class="fa-solid fa-eye"></i>
                                </button>

                                <button
                                    type="button"
                                    class="icon-btn icon-btn-cyan"
                                    title="نسخ بيانات الحالة"
                                    data-copy-case="{{ $case->id ?? $case->code }}"
                                >
                                    <i class="fa-solid fa-copy"></i>
                                </button>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="10">
                            <x-empty-state text="لا توجد حالات مطابقة" />
                        </td>
                    </tr>

                @endforelse

                <tr id="localSearchEmpty" class="hidden">

                    <td colspan="10" class="py-10 text-center">

                        <div class="flex flex-col items-center justify-center">

                            <span class="mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-info/10 text-info">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>

                            <div class="font-semibold text-fg">
                                لا توجد نتائج مطابقة
                            </div>

                            <div class="mt-1 text-xs text-muted">
                                جرّب تغيير كلمة البحث أو الحالة.
                            </div>

                        </div>

                    </td>

                </tr>

            </tbody>

        </table>

    </div>

    {{-- =========================================================
         FOOTER
         ========================================================= --}}
    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-line pt-4">

        <p class="text-[11px] text-dim">

            عرض
            <b id="footerVisibleCases" class="text-fg">
                {{ $visibleCaseCount }}
            </b>
            حالات

            من أصل
            <b class="text-fg">
                {{ number_format($archive->cases) }}
            </b>

            حالة مؤرشفة.

        </p>

        <div class="flex flex-wrap items-center gap-3 text-[11px] text-dim">

            <span>
                <i class="fa-solid fa-user-check ml-1 text-info"></i>
                نشط: {{ $activeCases }}
            </span>

            <span>
                <i class="fa-solid fa-circle-check ml-1 text-brand"></i>
                مسدد: {{ $paidCases }}
            </span>

            <span>
                <i class="fa-solid fa-gavel ml-1 text-danger"></i>
                قضائي: {{ $legalCases }}
            </span>

        </div>

    </div>

    {{-- =========================================================
         CASE DETAILS MODAL
         ========================================================= --}}
    <x-modal
        id="caseDetailsModal"
        title="تفاصيل الحالة المؤرشفة"
        width="40rem"
    >

        <div class="space-y-5">

            <div class="flex items-center gap-3 rounded-xl border border-line bg-surface p-4">

                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-info/10 text-info">
                    <i class="fa-solid fa-user"></i>
                </span>

                <div class="min-w-0">

                    <div
                        id="modalCaseName"
                        class="truncate text-lg font-bold text-fg"
                    >
                        -
                    </div>

                    <div
                        id="modalCaseCode"
                        class="mt-1 font-mono text-xs text-info"
                        dir="ltr"
                    >
                        -
                    </div>

                </div>

                <span
                    id="modalCaseStatus"
                    class="mr-auto badge badge-neutral"
                >
                    -
                </span>

            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">

                <div class="rounded-xl border border-line bg-surface p-3 text-center">

                    <div class="text-[10px] text-dim">
                        المديونية
                    </div>

                    <div
                        id="modalCaseDebt"
                        class="mt-1 text-sm font-bold text-fg"
                    >
                        -
                    </div>

                </div>

                <div class="rounded-xl border border-line bg-surface p-3 text-center">

                    <div class="text-[10px] text-dim">
                        المحصل
                    </div>

                    <div
                        id="modalCaseCollected"
                        class="mt-1 text-sm font-bold text-brand"
                    >
                        -
                    </div>

                </div>

                <div class="rounded-xl border border-line bg-surface p-3 text-center">

                    <div class="text-[10px] text-dim">
                        المتبقي
                    </div>

                    <div
                        id="modalCaseRemaining"
                        class="mt-1 text-sm font-bold text-warning"
                    >
                        -
                    </div>

                </div>

                <div class="rounded-xl border border-line bg-surface p-3 text-center">

                    <div class="text-[10px] text-dim">
                        الموظف
                    </div>

                    <div
                        id="modalCaseEmployee"
                        class="mt-1 truncate text-sm font-bold text-fg"
                    >
                        -
                    </div>

                </div>

            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

                <div class="rounded-xl border border-line bg-surface p-4">

                    <div class="mb-2 flex items-center gap-2 text-xs text-muted">
                        <i class="fa-solid fa-phone text-info"></i>
                        الهاتف
                    </div>

                    <div
                        id="modalCasePhone"
                        class="font-semibold text-fg"
                        dir="ltr"
                    >
                        -
                    </div>

                </div>

                <div class="rounded-xl border border-line bg-surface p-4">

                    <div class="mb-2 flex items-center gap-2 text-xs text-muted">
                        <i class="fa-solid fa-box-archive text-warning"></i>
                        حالة البيانات
                    </div>

                    <div class="font-semibold text-fg">
                        Snapshot مؤرشف
                    </div>

                </div>

            </div>

            <div class="flex flex-wrap gap-2">

                <button
                    type="button"
                    id="modalCopyCase"
                    class="btn btn-outline-info btn-sm"
                >
                    <i class="fa-solid fa-copy text-xs"></i>
                    نسخ البيانات
                </button>

                <button
                    type="button"
                    id="modalCopyPhone"
                    class="btn btn-outline-info btn-sm"
                >
                    <i class="fa-solid fa-phone text-xs"></i>
                    نسخ الهاتف
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

    const searchInput = document.getElementById('search');
    const statusFilter = document.getElementById('statusFilter');

    const clearLocalFilters = document.getElementById('clearLocalFilters');

    const masterCheck = document.getElementById('masterCaseCheck');

    const selectAllButton = document.getElementById('selectAllCases');
    const clearSelectionButton = document.getElementById('clearCaseSelection');
    const copySelectedButton = document.getElementById('copySelectedCases');

    const selectedCounter = document.getElementById('selectedCaseCount');
    const visibleCounter = document.getElementById('visibleCaseCount');
    const footerVisibleCases = document.getElementById('footerVisibleCases');

    const localSearchEmpty = document.getElementById('localSearchEmpty');

    const exportButton = document.getElementById('exportCases');
    const printButton = document.getElementById('printCases');

    const downloadButton = document.getElementById('archiveDownload');

    /*
    |--------------------------------------------------------------------------
    | Case rows
    |--------------------------------------------------------------------------
    */

    const rows = Array.from(
        document.querySelectorAll('[data-case-row]')
    );

    /*
    |--------------------------------------------------------------------------
    | Case data
    |--------------------------------------------------------------------------
    */

    const cases = {{ Js::from($caseData) }};

    let activeCase = null;

    /*
    |--------------------------------------------------------------------------
    | Modal
    |--------------------------------------------------------------------------
    */

    const caseModal = document.getElementById('caseDetailsModal');

    const modalCaseName = document.getElementById('modalCaseName');
    const modalCaseCode = document.getElementById('modalCaseCode');
    const modalCaseStatus = document.getElementById('modalCaseStatus');
    const modalCaseDebt = document.getElementById('modalCaseDebt');
    const modalCaseCollected = document.getElementById('modalCaseCollected');
    const modalCaseRemaining = document.getElementById('modalCaseRemaining');
    const modalCaseEmployee = document.getElementById('modalCaseEmployee');
    const modalCasePhone = document.getElementById('modalCasePhone');

    const modalCopyCase = document.getElementById('modalCopyCase');
    const modalCopyPhone = document.getElementById('modalCopyPhone');

    /*
    |--------------------------------------------------------------------------
    | Toast
    |--------------------------------------------------------------------------
    */

    function toast(message) {

        const oldToast = document.getElementById('archiveCaseToast');

        if (oldToast) {
            oldToast.remove();
        }

        const element = document.createElement('div');

        element.id = 'archiveCaseToast';

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
            .map(row => row.querySelector('.case-row-check'))
            .filter(Boolean);
    }

    function getSelectedChecks() {

        return getChecks().filter(
            checkbox => checkbox.checked
        );
    }

    function refreshSelection() {

        const checks = getChecks();

        const selected = checks.filter(
            checkbox => checkbox.checked
        );

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

        getChecks().forEach(checkbox => {
            checkbox.checked = masterCheck.checked;
        });

        refreshSelection();
    });

    selectAllButton?.addEventListener('click', function () {

        getChecks().forEach(checkbox => {
            checkbox.checked = true;
        });

        refreshSelection();
    });

    clearSelectionButton?.addEventListener('click', function () {

        getChecks().forEach(checkbox => {
            checkbox.checked = false;
        });

        refreshSelection();
    });

    document.addEventListener('change', function (event) {

        if (event.target.matches('.case-row-check')) {
            refreshSelection();
        }

    });

    /*
    |--------------------------------------------------------------------------
    | Local filtering
    |--------------------------------------------------------------------------
    */

    function filterCases() {

        const query = (searchInput?.value || '')
            .trim()
            .toLowerCase();

        const selectedStatus =
            statusFilter?.value || '';

        let visible = 0;

        rows.forEach(row => {

            const searchData =
                row.dataset.caseSearch || '';

            const rowStatus =
                row.dataset.caseStatus || '';

            const matchesSearch =
                !query || searchData.includes(query);

            const matchesStatus =
                !selectedStatus ||
                rowStatus === selectedStatus;

            const visibleRow =
                matchesSearch && matchesStatus;

            row.classList.toggle(
                'hidden',
                !visibleRow
            );

            if (visibleRow) {
                visible++;
            }
        });

        visibleCounter.textContent = visible;

        footerVisibleCases.textContent = visible;

        if (localSearchEmpty) {

            localSearchEmpty.classList.toggle(
                'hidden',
                visible !== 0 || rows.length === 0
            );

        }
    }

    searchInput?.addEventListener(
        'input',
        filterCases
    );

    statusFilter?.addEventListener(
        'change',
        filterCases
    );

    clearLocalFilters?.addEventListener(
        'click',
        function () {

            if (searchInput) {
                searchInput.value = '';
            }

            if (statusFilter) {
                statusFilter.value = '';
            }

            filterCases();

            searchInput?.focus();
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Find case
    |--------------------------------------------------------------------------
    */

    function findCase(id) {

        return cases.find(
            item => String(item.id) === String(id)
        ) || cases.find(
            item => String(item.code) === String(id)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Open case details
    |--------------------------------------------------------------------------
    */

    function openCaseDetails(id) {

        const item = findCase(id);

        if (!item || !caseModal) {
            return;
        }

        activeCase = item;

        modalCaseName.textContent =
            item.name || '-';

        modalCaseCode.textContent =
            item.code || '-';

        modalCaseStatus.textContent =
            item.statusText || '-';

        modalCaseDebt.textContent =
            'EGP ' +
            Number(item.debt || 0).toLocaleString();

        modalCaseCollected.textContent =
            'EGP ' +
            Number(item.collected || 0).toLocaleString();

        modalCaseRemaining.textContent =
            'EGP ' +
            Number(item.remaining || 0).toLocaleString();

        modalCaseEmployee.textContent =
            item.employee || '-';

        modalCasePhone.textContent =
            item.phone || '-';

        caseModal.showModal();
    }

    document.addEventListener(
        'click',
        function (event) {

            const button =
                event.target.closest(
                    '[data-case-details]'
                );

            if (!button) {
                return;
            }

            openCaseDetails(
                button.dataset.caseDetails
            );
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Copy code
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'click',
        function (event) {

            const button =
                event.target.closest(
                    '[data-copy-code]'
                );

            if (!button) {
                return;
            }

            event.preventDefault();

            copyText(
                button.dataset.copyCode,
                'تم نسخ كود العميل'
            );
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Copy phone
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'click',
        function (event) {

            const button =
                event.target.closest(
                    '[data-copy-phone]'
                );

            if (!button) {
                return;
            }

            event.preventDefault();

            copyText(
                button.dataset.copyPhone,
                'تم نسخ رقم الهاتف'
            );
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Copy complete case
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'click',
        function (event) {

            const button =
                event.target.closest(
                    '[data-copy-case]'
                );

            if (!button) {
                return;
            }

            const item =
                findCase(button.dataset.copyCase);

            if (!item) {
                return;
            }

            const text = [
                `البنك: {{ addslashes($bank->name) }}`,
                `الأرشيف: {{ addslashes($archive->label) }}`,
                `كود العميل: ${item.code}`,
                `اسم العميل: ${item.name}`,
                `الهاتف: ${item.phone}`,
                `المديونية: EGP ${Number(item.debt || 0).toLocaleString()}`,
                `المحصل: EGP ${Number(item.collected || 0).toLocaleString()}`,
                `المتبقي: EGP ${Number(item.remaining || 0).toLocaleString()}`,
                `الحالة: ${item.statusText}`,
                `الموظف: ${item.employee || '-'}`
            ].join('\n');

            copyText(
                text,
                'تم نسخ بيانات الحالة'
            );
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Modal copy
    |--------------------------------------------------------------------------
    */

    modalCopyCase?.addEventListener(
        'click',
        function () {

            if (!activeCase) {
                return;
            }

            const text = [
                `البنك: {{ addslashes($bank->name) }}`,
                `الأرشيف: {{ addslashes($archive->label) }}`,
                `كود العميل: ${activeCase.code}`,
                `اسم العميل: ${activeCase.name}`,
                `الهاتف: ${activeCase.phone}`,
                `المديونية: EGP ${Number(activeCase.debt || 0).toLocaleString()}`,
                `المحصل: EGP ${Number(activeCase.collected || 0).toLocaleString()}`,
                `المتبقي: EGP ${Number(activeCase.remaining || 0).toLocaleString()}`,
                `الحالة: ${activeCase.statusText}`,
                `الموظف: ${activeCase.employee || '-'}`
            ].join('\n');

            copyText(
                text,
                'تم نسخ بيانات الحالة'
            );
        }
    );

    modalCopyPhone?.addEventListener(
        'click',
        function () {

            if (!activeCase) {
                return;
            }

            copyText(
                activeCase.phone,
                'تم نسخ رقم الهاتف'
            );
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Copy selected cases
    |--------------------------------------------------------------------------
    */

    copySelectedButton?.addEventListener(
        'click',
        function () {

            const selectedIds =
                getSelectedChecks().map(
                    checkbox => String(checkbox.value)
                );

            if (!selectedIds.length) {

                toast(
                    'اختر حالة واحدة على الأقل'
                );

                return;
            }

            const selectedCases =
                cases.filter(item =>
                    selectedIds.includes(
                        String(item.id)
                    ) ||
                    selectedIds.includes(
                        String(item.code)
                    )
                );

            const text =
                selectedCases
                    .map(item => [
                        `كود العميل: ${item.code}`,
                        `الاسم: ${item.name}`,
                        `الهاتف: ${item.phone}`,
                        `المديونية: EGP ${Number(item.debt || 0).toLocaleString()}`,
                        `المحصل: EGP ${Number(item.collected || 0).toLocaleString()}`,
                        `المتبقي: EGP ${Number(item.remaining || 0).toLocaleString()}`,
                        `الحالة: ${item.statusText}`,
                        `الموظف: ${item.employee || '-'}`
                    ].join(' | '))
                    .join('\n');

            copyText(
                text,
                `تم نسخ ${selectedCases.length} حالة`
            );
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Export cases
    |--------------------------------------------------------------------------
    */

    exportButton?.addEventListener(
        'click',
        function () {

            const visibleCases =
                cases.filter(item => {

                    const row =
                        rows.find(
                            currentRow =>
                                String(
                                    currentRow.dataset.caseId
                                ) === String(item.id)
                        );

                    return row &&
                        !row.classList.contains(
                            'hidden'
                        );
                });

            if (!visibleCases.length) {

                toast(
                    'لا توجد بيانات للتصدير'
                );

                return;
            }

            const data = [
                [
                    'البنك',
                    'الأرشيف',
                    'كود العميل',
                    'اسم العميل',
                    'الهاتف',
                    'المديونية',
                    'المحصل',
                    'المتبقي',
                    'الحالة',
                    'الموظف'
                ],
                ...visibleCases.map(item => [
                    '{{ addslashes($bank->name) }}',
                    '{{ addslashes($archive->label) }}',
                    item.code,
                    item.name,
                    item.phone,
                    item.debt,
                    item.collected,
                    item.remaining,
                    item.statusText,
                    item.employee
                ])
            ];

            downloadCsv(
                data,
                'archive-cases-{{ $archive->id }}.csv'
            );

            toast(
                `تم تصدير ${visibleCases.length} حالة`
            );
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Existing Excel button
    |--------------------------------------------------------------------------
    |
    | No backend download route is invented.
    | Front-end CSV is generated instead.
    |--------------------------------------------------------------------------
    */

    downloadButton?.addEventListener(
        'click',
        function (event) {

            event.preventDefault();

            exportButton?.click();
        }
    );

    /*
    |--------------------------------------------------------------------------
    | CSV helper
    |--------------------------------------------------------------------------
    */

    function downloadCsv(data, filename) {

        const csv = data
            .map(row =>
                row.map(value => {

                    const safeValue =
                        String(value ?? '')
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

        const url =
            URL.createObjectURL(blob);

        const link =
            document.createElement('a');

        link.href = url;
        link.download = filename;

        document.body.appendChild(link);

        link.click();

        link.remove();

        URL.revokeObjectURL(url);
    }

    /*
    |--------------------------------------------------------------------------
    | Print
    |--------------------------------------------------------------------------
    */

    printButton?.addEventListener(
        'click',
        function () {

            const visibleCases =
                cases.filter(item => {

                    const row =
                        rows.find(
                            currentRow =>
                                String(
                                    currentRow.dataset.caseId
                                ) === String(item.id)
                        );

                    return row &&
                        !row.classList.contains(
                            'hidden'
                        );
                });

            if (!visibleCases.length) {

                toast(
                    'لا توجد حالات للطباعة'
                );

                return;
            }

            const popup =
                window.open(
                    '',
                    '_blank',
                    'width=1200,height=800'
                );

            if (!popup) {

                toast(
                    'اسمح بالنوافذ المنبثقة للطباعة'
                );

                return;
            }

            const rowsHtml =
                visibleCases
                    .map(item => `
                        <tr>
                            <td>${escapeHtml(item.code)}</td>
                            <td>${escapeHtml(item.name)}</td>
                            <td>${escapeHtml(item.phone)}</td>
                            <td>EGP ${Number(item.debt || 0).toLocaleString()}</td>
                            <td>EGP ${Number(item.collected || 0).toLocaleString()}</td>
                            <td>EGP ${Number(item.remaining || 0).toLocaleString()}</td>
                            <td>${escapeHtml(item.statusText)}</td>
                            <td>${escapeHtml(item.employee || '-')}</td>
                        </tr>
                    `)
                    .join('');

            popup.document.write(`
                <!doctype html>

                <html lang="ar" dir="rtl">

                <head>

                    <meta charset="utf-8">

                    <title>
                        {{ addslashes($archive->label) }}
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

                        .subtitle {
                            color: #666;
                            margin-bottom: 25px;
                        }

                        .summary {
                            display: grid;
                            grid-template-columns: repeat(4, 1fr);
                            gap: 10px;
                            margin-bottom: 25px;
                        }

                        .summary-item {
                            border: 1px solid #ddd;
                            padding: 12px;
                        }

                        .summary-label {
                            color: #666;
                            font-size: 12px;
                        }

                        .summary-value {
                            margin-top: 5px;
                            font-weight: bold;
                        }

                        table {
                            width: 100%;
                            border-collapse: collapse;
                        }

                        th,
                        td {
                            border: 1px solid #ddd;
                            padding: 8px;
                            text-align: right;
                        }

                        th {
                            background: #f3f3f3;
                        }

                        .footer {
                            margin-top: 25px;
                            color: #666;
                            font-size: 11px;
                        }

                    </style>

                </head>

                <body>

                    <h1>
                        {{ addslashes($bank->name) }}
                    </h1>

                    <div class="subtitle">
                        نطاق {{ addslashes($archive->label) }}
                        — تقرير أرشيفي للقراءة فقط
                    </div>

                    <div class="summary">

                        <div class="summary-item">
                            <div class="summary-label">الحالات</div>
                            <div class="summary-value">
                                {{ number_format($archive->cases) }}
                            </div>
                        </div>

                        <div class="summary-item">
                            <div class="summary-label">المديونية</div>
                            <div class="summary-value">
                                EGP {{ number_format($archive->debt) }}
                            </div>
                        </div>

                        <div class="summary-item">
                            <div class="summary-label">المحصل</div>
                            <div class="summary-value">
                                EGP {{ number_format($archive->collected) }}
                            </div>
                        </div>

                        <div class="summary-item">
                            <div class="summary-label">التحصيل</div>
                            <div class="summary-value">
                                {{ $archive->rate }}%
                            </div>
                        </div>

                    </div>

                    <table>

                        <thead>

                            <tr>
                                <th>كود العميل</th>
                                <th>اسم العميل</th>
                                <th>الهاتف</th>
                                <th>المديونية</th>
                                <th>المحصل</th>
                                <th>المتبقي</th>
                                <th>الحالة</th>
                                <th>الموظف</th>
                            </tr>

                        </thead>

                        <tbody>
                            ${rowsHtml}
                        </tbody>

                    </table>

                    <div class="footer">
                        تمت الأرشفة بواسطة
                        {{ addslashes($archive->by) }}
                        في
                        {{ addslashes($archive->at) }}.
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
    );

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
    | Keyboard shortcuts
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'keydown',
        function (event) {

            /*
            | Ctrl + K = focus search
            */

            if (
                (event.ctrlKey || event.metaKey) &&
                event.key.toLowerCase() === 'k'
            ) {

                event.preventDefault();

                searchInput?.focus();
                searchInput?.select();

                return;
            }

            /*
            | Escape = clear local filters
            */

            if (
                event.key === 'Escape' &&
                document.activeElement === searchInput
            ) {

                if (searchInput.value) {

                    searchInput.value = '';

                    filterCases();
                }

                searchInput.blur();
            }

        }
    );

    /*
    |--------------------------------------------------------------------------
    | Initial state
    |--------------------------------------------------------------------------
    */

    refreshSelection();
    filterCases();

})();
</script>
@endpush