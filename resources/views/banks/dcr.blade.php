{{-- resources/views/banks/dcr.blade.php --}}
@extends('layouts.app')

@section('title', 'التقرير اليومي DCR - ' . $bank->name)

@php
    /*
    |--------------------------------------------------------------------------
    | Safe frontend calculations
    |--------------------------------------------------------------------------
    | These values are for display only.
    | They do not change your backend structure.
    |--------------------------------------------------------------------------
    */

    $rowsCollection = collect($rows ?? []);

    $totalRows = $rowsCollection->count();

    $submittedRows = $rowsCollection->filter(
        fn ($row) => ($row->status ?? null) === 'submitted'
    )->count();

    $approvedRows = $rowsCollection->filter(
        fn ($row) => ($row->status ?? null) === 'approved'
    )->count();

    $rejectedRows = $rowsCollection->filter(
        fn ($row) => ($row->status ?? null) === 'rejected'
    )->count();

    $draftRows = $rowsCollection->filter(
        fn ($row) => ($row->status ?? null) === 'draft'
    )->count();

    $promisedTotal = (float) ($totals['promised'] ?? 0);
    $collectedTotal = (float) ($totals['collected'] ?? 0);

    /*
    | Do not normalize negative values.
    | Only calculate the percentage when the promised amount is positive.
    */
    $collectionPercent = $promisedTotal > 0
        ? (($collectedTotal / $promisedTotal) * 100)
        : 0;

    $collectionPercent = min(100, max(0, $collectionPercent));

    $averageCollected = $totalRows > 0
        ? ($collectedTotal / $totalRows)
        : 0;

    $averagePromised = $totalRows > 0
        ? ($promisedTotal / $totalRows)
        : 0;

    $averageCalls = $totalRows > 0
        ? (($totals['calls'] ?? 0) / $totalRows)
        : 0;

    $averageVisits = $totalRows > 0
        ? (($totals['visits'] ?? 0) / $totalRows)
        : 0;

    $averagePromises = $totalRows > 0
        ? (($totals['promises'] ?? 0) / $totalRows)
        : 0;

    /*
    |--------------------------------------------------------------------------
    | Status labels for frontend filtering
    |--------------------------------------------------------------------------
    */

    $statusLabels = [
        'draft'     => 'مسودة',
        'submitted' => 'مرسل',
        'approved'  => 'معتمد',
        'rejected'  => 'مرفوض',
    ];
@endphp

@section('content')

    {{-- ================================================================ --}}
    {{-- HEADER --}}
    {{-- ================================================================ --}}

    <x-bank-header
        :bank="$bank"
        title="التقرير اليومي DCR"
        subtitle="متابعة عمل الموظفين يوم بيوم"
    />

    <x-flash />

    {{-- ================================================================ --}}
    {{-- DATE NAVIGATION --}}
    {{-- ================================================================ --}}

    <div class="card mb-6 flex flex-wrap items-center justify-between gap-4">

        <div class="flex flex-wrap items-center gap-2">

            {{-- Previous day --}}
            <a
                href="{{ route('banks.dcr.index', ['bank' => $bank->id, 'date' => $prev]) }}"
                class="btn btn-secondary btn-sm"
                title="اليوم السابق"
            >
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </a>

            {{-- Date picker --}}
            <form
                method="GET"
                action="{{ route('banks.dcr.index', $bank->id) }}"
            >
                <label
                    for="date"
                    class="sr-only"
                >
                    التاريخ
                </label>

                <input
                    id="date"
                    name="date"
                    type="date"
                    value="{{ $date->toDateString() }}"
                    class="form-input py-1.5"
                    onchange="this.form.submit()"
                >
            </form>

            {{-- Next day --}}
            <a
                href="{{ route('banks.dcr.index', ['bank' => $bank->id, 'date' => $next]) }}"
                class="btn btn-secondary btn-sm"
                title="اليوم التالي"
            >
                <i class="fa-solid fa-chevron-left text-[10px]"></i>
            </a>

            {{-- Today --}}
            @unless($isToday)

                <a
                    href="{{ route('banks.dcr.index', $bank->id) }}"
                    class="btn btn-outline-info btn-sm"
                >
                    <i class="fa-solid fa-calendar-day text-xs"></i>
                    اليوم
                </a>

            @endunless

        </div>

        <div class="flex flex-wrap items-center gap-3">

            <p class="text-sm font-bold text-fg">
                {{ $dayName }}
                <span class="text-muted">·</span>
                {{ $date->format('d/m/Y') }}
            </p>

            @if($isToday)

                <span class="badge badge-info">
                    <i class="fa-solid fa-circle text-[6px] ml-1"></i>
                    اليوم
                </span>

            @endif

            {{-- Print --}}
            <button
                type="button"
                onclick="window.print()"
                class="btn btn-secondary btn-sm"
                title="طباعة التقرير"
            >
                <i class="fa-solid fa-print text-xs"></i>
            </button>

        </div>

    </div>

    {{-- ================================================================ --}}
    {{-- MAIN TOTALS --}}
    {{-- ================================================================ --}}

    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <x-metric-card
            label="المكالمات"
            :value="$totals['calls']"
            icon="fa-phone"
            color="info"
        />

        <x-metric-card
            label="الزيارات"
            :value="$totals['visits']"
            icon="fa-person-walking"
            color="cyan"
        />

        <x-metric-card
            label="الوعود المسجلة"
            :value="$totals['promises']"
            icon="fa-handshake"
            color="warning"
        />

        <x-metric-card
            label="المحصل اليوم"
            :value="number_format($totals['collected'])"
            unit="EGP"
            icon="fa-money-bill-wave"
            color="brand"
        />

    </section>

    {{-- ================================================================ --}}
    {{-- DAILY PERFORMANCE OVERVIEW --}}
    {{-- ================================================================ --}}

    <section class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-3">

        {{-- Collection --}}
        <div class="card">

            <div class="flex items-start justify-between gap-3">

                <div>

                    <p class="text-[11px] text-dim">
                        نسبة التحصيل من الوعود
                    </p>

                    <p class="mt-1 text-2xl font-bold text-brand">
                        {{ number_format($collectionPercent, 1) }}%
                    </p>

                </div>

                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand/10 text-brand">
                    <i class="fa-solid fa-chart-line"></i>
                </span>

            </div>

            <div class="mt-4 progress">

                <div
                    class="h-full rounded-full bg-brand"
                    style="width: {{ $collectionPercent }}%"
                ></div>

            </div>

            <div class="mt-2 flex justify-between text-[10px] text-dim">

                <span>
                    وعود:
                    EGP {{ number_format($promisedTotal) }}
                </span>

                <span>
                    محصل:
                    EGP {{ number_format($collectedTotal) }}
                </span>

            </div>

        </div>

        {{-- Activity --}}
        <div class="card">

            <div class="flex items-center justify-between gap-3">

                <div>

                    <p class="text-[11px] text-dim">
                        النشاط اليومي
                    </p>

                    <p class="mt-1 text-2xl font-bold text-fg">
                        {{ number_format($totalRows) }}
                    </p>

                    <p class="mt-1 text-[10px] text-muted">
                        موظف في التقرير
                    </p>

                </div>

                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-info/10 text-info">
                    <i class="fa-solid fa-users"></i>
                </span>

            </div>

            <div class="mt-4 grid grid-cols-3 gap-2 text-center">

                <div>
                    <p class="text-sm font-bold text-info">
                        {{ number_format($averageCalls, 1) }}
                    </p>

                    <p class="text-[9px] text-dim">
                        مكالمات
                    </p>
                </div>

                <div>
                    <p class="text-sm font-bold text-cyan">
                        {{ number_format($averageVisits, 1) }}
                    </p>

                    <p class="text-[9px] text-dim">
                        زيارات
                    </p>
                </div>

                <div>
                    <p class="text-sm font-bold text-warning">
                        {{ number_format($averagePromises, 1) }}
                    </p>

                    <p class="text-[9px] text-dim">
                        وعود
                    </p>
                </div>

            </div>

        </div>

        {{-- Workflow --}}
        <div class="card">

            <div class="flex items-center justify-between gap-3">

                <div>

                    <p class="text-[11px] text-dim">
                        دورة اعتماد التقارير
                    </p>

                    <p class="mt-1 text-2xl font-bold text-fg">
                        {{ number_format($approvedRows) }}
                    </p>

                    <p class="mt-1 text-[10px] text-muted">
                        تقرير معتمد
                    </p>

                </div>

                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand/10 text-brand">
                    <i class="fa-solid fa-file-circle-check"></i>
                </span>

            </div>

            <div class="mt-4 grid grid-cols-4 gap-1 text-center">

                <div>
                    <p class="text-sm font-bold text-dim">
                        {{ $draftRows }}
                    </p>

                    <p class="text-[8px] text-dim">
                        مسودة
                    </p>
                </div>

                <div>
                    <p class="text-sm font-bold text-info">
                        {{ $submittedRows }}
                    </p>

                    <p class="text-[8px] text-dim">
                        مرسل
                    </p>
                </div>

                <div>
                    <p class="text-sm font-bold text-brand">
                        {{ $approvedRows }}
                    </p>

                    <p class="text-[8px] text-dim">
                        معتمد
                    </p>
                </div>

                <div>
                    <p class="text-sm font-bold text-danger">
                        {{ $rejectedRows }}
                    </p>

                    <p class="text-[8px] text-dim">
                        مرفوض
                    </p>
                </div>

            </div>

        </div>

    </section>

    {{-- ================================================================ --}}
    {{-- SECONDARY METRICS --}}
    {{-- ================================================================ --}}

    <section class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-4">

        <div class="card">

            <p class="text-[10px] text-dim">
                إجمالي الحالات
            </p>

            <p class="mt-1 text-lg font-bold text-fg">
                {{ number_format($totals['cases']) }}
            </p>

        </div>

        <div class="card">

            <p class="text-[10px] text-dim">
                إجمالي قيمة الوعود
            </p>

            <p class="mt-1 text-lg font-bold text-warning">
                EGP {{ number_format($totals['promised']) }}
            </p>

        </div>

        <div class="card">

            <p class="text-[10px] text-dim">
                متوسط التحصيل / موظف
            </p>

            <p class="mt-1 text-lg font-bold text-brand">
                EGP {{ number_format($averageCollected, 2) }}
            </p>

        </div>

        <div class="card">

            <p class="text-[10px] text-dim">
                متوسط قيمة الوعود / موظف
            </p>

            <p class="mt-1 text-lg font-bold text-info">
                EGP {{ number_format($averagePromised, 2) }}
            </p>

        </div>

    </section>

    {{-- ================================================================ --}}
    {{-- TABLE TOOLBAR --}}
    {{-- ================================================================ --}}

    <div class="card mb-4">

        <div class="flex flex-wrap items-center justify-between gap-3">

            <div>

                <h2 class="text-sm font-bold text-fg">
                    تفاصيل الموظفين
                </h2>

                <p class="mt-1 text-[10px] text-dim">
                    {{ number_format($totalRows) }}
                    سجل في تقرير
                    {{ $date->format('d/m/Y') }}
                </p>

            </div>

            <div class="flex flex-wrap items-center gap-2">

                {{-- Search --}}
                <div class="relative min-w-[220px]">

                    <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-dim"></i>

                    <input
                        id="dcrSearch"
                        type="search"
                        placeholder="بحث عن موظف..."
                        class="form-input pl-9"
                        autocomplete="off"
                    >

                </div>

                {{-- Status filter --}}
                <select
                    id="dcrStatusFilter"
                    class="form-input w-40"
                >

                    <option value="">كل الحالات</option>
                    <option value="draft">مسودة</option>
                    <option value="submitted">مرسل</option>
                    <option value="approved">معتمد</option>
                    <option value="rejected">مرفوض</option>

                </select>

                {{-- Export --}}
                <button
                    type="button"
                    id="exportDcr"
                    class="btn btn-secondary btn-sm"
                    title="تصدير CSV"
                >
                    <i class="fa-solid fa-file-csv text-xs"></i>
                    تصدير
                </button>

            </div>

        </div>

        {{-- Table result summary --}}
        <div
            id="dcrFilterSummary"
            class="mt-3 hidden rounded-xl border border-line bg-white/[0.02] px-3 py-2 text-[11px] text-muted"
        ></div>

    </div>

    {{-- ================================================================ --}}
    {{-- TABLE --}}
    {{-- ================================================================ --}}

    <div class="table-wrap">

        <table
            id="dcrTable"
            class="data-table whitespace-nowrap"
        >

            <thead>

                <tr>

                    <th
                        data-sortable
                        data-column="0"
                        class="cursor-pointer select-none"
                    >
                        الموظف
                        <i class="fa-solid fa-sort mr-1 text-[9px] text-dim"></i>
                    </th>

                    <th data-sortable data-column="1" class="cursor-pointer select-none">
                        الحالات
                        <i class="fa-solid fa-sort mr-1 text-[9px] text-dim"></i>
                    </th>

                    <th data-sortable data-column="2" class="cursor-pointer select-none">
                        مكالمات
                        <i class="fa-solid fa-sort mr-1 text-[9px] text-dim"></i>
                    </th>

                    <th data-sortable data-column="3" class="cursor-pointer select-none">
                        زيارات
                        <i class="fa-solid fa-sort mr-1 text-[9px] text-dim"></i>
                    </th>

                    <th data-sortable data-column="4" class="cursor-pointer select-none">
                        وعود
                        <i class="fa-solid fa-sort mr-1 text-[9px] text-dim"></i>
                    </th>

                    <th data-sortable data-column="5" class="cursor-pointer select-none">
                        قيمة الوعود
                        <i class="fa-solid fa-sort mr-1 text-[9px] text-dim"></i>
                    </th>

                    <th data-sortable data-column="6" class="cursor-pointer select-none">
                        المحصل
                        <i class="fa-solid fa-sort mr-1 text-[9px] text-dim"></i>
                    </th>

                    <th>
                        الحالة
                    </th>

                    <th>
                        الإجراءات
                    </th>

                </tr>

            </thead>

            <tbody>

                @forelse($rows as $row)

                    @php
                        $rowStatus = $row->status ?? 'draft';

                        $rowPromised = (float) ($row->promised ?? 0);
                        $rowCollected = (float) ($row->collected ?? 0);

                        $rowCollectionPercent = $rowPromised > 0
                            ? (($rowCollected / $rowPromised) * 100)
                            : 0;

                        $rowCollectionPercent = min(
                            100,
                            max(0, $rowCollectionPercent)
                        );

                        $rowUserName = $row->user->name ?? 'غير محدد';
                        $rowUserRole = $row->user->role ?? 'غير محدد';
                    @endphp

                    <tr
                        data-dcr-row
                        data-status="{{ $rowStatus }}"
                        data-employee="{{ $rowUserName }}"
                    >

                        {{-- Employee --}}
                        <td>

                            <div class="flex items-center gap-3">

                                <x-avatar :name="$rowUserName" />

                                <div>

                                    <div class="font-semibold text-fg">
                                        {{ $rowUserName }}
                                    </div>

                                    <div class="text-[11px] text-dim">
                                        {{ $rowUserRole }}
                                    </div>

                                </div>

                            </div>

                        </td>

                        {{-- Cases --}}
                        <td>
                            <span class="font-semibold">
                                {{ $row->cases }}
                            </span>
                        </td>

                        {{-- Calls --}}
                        <td>

                            <div class="flex items-center gap-2">

                                <span>
                                    {{ $row->calls }}
                                </span>

                                @if(($row->calls ?? 0) > 0)

                                    <i class="fa-solid fa-phone text-[9px] text-info"></i>

                                @endif

                            </div>

                        </td>

                        {{-- Visits --}}
                        <td>

                            <div class="flex items-center gap-2">

                                <span>
                                    {{ $row->visits }}
                                </span>

                                @if(($row->visits ?? 0) > 0)

                                    <i class="fa-solid fa-person-walking text-[9px] text-cyan"></i>

                                @endif

                            </div>

                        </td>

                        {{-- Promises --}}
                        <td>

                            <span class="font-semibold">
                                {{ $row->promises }}
                            </span>

                        </td>

                        {{-- Promised --}}
                        <td>

                            <div>

                                <div>
                                    EGP {{ number_format($rowPromised) }}
                                </div>

                                @if($rowPromised > 0)

                                    <div class="mt-1 flex items-center gap-2">

                                        <div class="w-16 progress">

                                            <div
                                                class="h-full rounded-full bg-warning"
                                                style="width: {{ $rowCollectionPercent }}%"
                                            ></div>

                                        </div>

                                        <span class="text-[9px] text-dim">
                                            {{ number_format($rowCollectionPercent, 0) }}%
                                        </span>

                                    </div>

                                @endif

                            </div>

                        </td>

                        {{-- Collected --}}
                        <td>

                            <div>

                                <div class="font-semibold text-brand">
                                    EGP {{ number_format($rowCollected) }}
                                </div>

                                @if($rowPromised > 0)

                                    <div class="mt-1 text-[9px] text-dim">
                                        من EGP {{ number_format($rowPromised) }}
                                    </div>

                                @endif

                            </div>

                        </td>

                        {{-- Status --}}
                        <td>

                            <x-status-badge
                                type="dcr"
                                :status="$rowStatus"
                            />

                            @if($rowStatus === 'rejected')

                                <div class="mt-1 text-[9px] text-danger">
                                    يحتاج مراجعة
                                </div>

                            @elseif($rowStatus === 'submitted')

                                <div class="mt-1 text-[9px] text-info">
                                    بانتظار الاعتماد
                                </div>

                            @elseif($rowStatus === 'approved')

                                <div class="mt-1 text-[9px] text-brand">
                                    مكتمل
                                </div>

                            @endif

                        </td>

                        {{-- Actions --}}
                        <td>

                            <form
                                method="POST"
                                action="{{ route('banks.dcr.update', [$bank->id, $row->user->id]) }}"
                                class="dcr-action-form flex items-center gap-2"
                            >

                                @csrf
                                @method('PUT')

                                <input
                                    type="hidden"
                                    name="date"
                                    value="{{ $date->toDateString() }}"
                                >

                                @if($rowStatus === 'draft')

                                    <button
                                        type="submit"
                                        name="status"
                                        value="submitted"
                                        class="btn btn-outline-info btn-sm"
                                        data-confirm="هل تريد إرسال تقرير {{ $rowUserName }} للاعتماد؟"
                                    >
                                        <i class="fa-solid fa-paper-plane text-xs"></i>
                                        إرسال للاعتماد
                                    </button>

                                @elseif($rowStatus === 'submitted')

                                    <button
                                        type="submit"
                                        name="status"
                                        value="approved"
                                        class="btn btn-outline-success btn-sm"
                                        data-confirm="هل تريد اعتماد تقرير {{ $rowUserName }}؟"
                                    >
                                        <i class="fa-solid fa-check text-xs"></i>
                                        اعتماد
                                    </button>

                                    <button
                                        type="submit"
                                        name="status"
                                        value="rejected"
                                        class="btn btn-danger btn-sm"
                                        data-confirm="هل تريد رفض تقرير {{ $rowUserName }}؟"
                                    >
                                        <i class="fa-solid fa-xmark text-xs"></i>
                                        رفض
                                    </button>

                                @elseif($rowStatus === 'rejected')

                                    <button
                                        type="submit"
                                        name="status"
                                        value="submitted"
                                        class="btn btn-secondary btn-sm"
                                        data-confirm="هل تريد إعادة إرسال تقرير {{ $rowUserName }} للاعتماد؟"
                                    >
                                        <i class="fa-solid fa-rotate-right text-xs"></i>
                                        إعادة الإرسال
                                    </button>

                                @else

                                    <span class="text-xs text-dim">
                                        <i class="fa-solid fa-lock ml-1"></i>
                                        معتمد
                                    </span>

                                @endif

                            </form>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="9">

                            <x-empty-state
                                icon="fa-calendar-xmark"
                                text="لا توجد تقارير لهذا التاريخ"
                            />

                        </td>

                    </tr>

                @endforelse

            </tbody>

            @if($rows->isNotEmpty())

                <tfoot>

                    <tr class="bg-white/[0.03] font-bold text-fg">

                        <td class="px-4 py-3">
                            الإجمالي
                        </td>

                        <td class="px-4 py-3">
                            {{ $totals['cases'] }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $totals['calls'] }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $totals['visits'] }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $totals['promises'] }}
                        </td>

                        <td class="px-4 py-3">
                            EGP {{ number_format($totals['promised']) }}
                        </td>

                        <td class="px-4 py-3 text-brand">
                            EGP {{ number_format($totals['collected']) }}
                        </td>

                        <td colspan="2"></td>

                    </tr>

                </tfoot>

            @endif

        </table>

    </div>

    {{-- ================================================================ --}}
    {{-- FILTER EMPTY STATE --}}
    {{-- ================================================================ --}}

    <div
        id="dcrNoResults"
        class="card mt-4 hidden"
    >

        <div class="py-8 text-center">

            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-white/5 text-dim">
                <i class="fa-solid fa-filter-circle-xmark"></i>
            </div>

            <p class="mt-3 text-sm font-bold text-fg">
                لا توجد نتائج مطابقة
            </p>

            <p class="mt-1 text-xs text-muted">
                جرّب تغيير البحث أو فلتر الحالة.
            </p>

        </div>

    </div>

    {{-- ================================================================ --}}
    {{-- BOTTOM SUMMARY --}}
    {{-- ================================================================ --}}

    <section class="mt-4 card">

        <div class="flex flex-wrap items-center justify-between gap-3">

            <div>

                <p class="text-sm font-bold text-fg">
                    ملخص التقرير
                </p>

                <p class="mt-1 text-[10px] text-dim">
                    {{ $dayName }} · {{ $date->format('d/m/Y') }}
                </p>

            </div>

            <div class="flex flex-wrap items-center gap-4 text-xs">

                <div class="flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-brand"></span>
                    <span class="text-muted">
                        معتمد: {{ $approvedRows }}
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-info"></span>
                    <span class="text-muted">
                        مرسل: {{ $submittedRows }}
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-danger"></span>
                    <span class="text-muted">
                        مرفوض: {{ $rejectedRows }}
                    </span>
                </div>

            </div>

        </div>

    </section>

    {{-- ================================================================ --}}
    {{-- BACK TO TOP --}}
    {{-- ================================================================ --}}

    <button
        id="dcrBackToTop"
        type="button"
        class="fixed bottom-6 left-6 z-50 hidden h-10 w-10 items-center justify-center rounded-xl border border-line bg-surface text-muted shadow-lg transition hover:text-fg"
        title="العودة للأعلى"
        aria-label="العودة للأعلى"
    >
        <i class="fa-solid fa-arrow-up text-xs"></i>
    </button>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            /*
            |--------------------------------------------------------------------------
            | Elements
            |--------------------------------------------------------------------------
            */

            const searchInput =
                document.getElementById('dcrSearch');

            const statusFilter =
                document.getElementById('dcrStatusFilter');

            const table =
                document.getElementById('dcrTable');

            const noResults =
                document.getElementById('dcrNoResults');

            const filterSummary =
                document.getElementById('dcrFilterSummary');

            const exportButton =
                document.getElementById('exportDcr');

            const backToTop =
                document.getElementById('dcrBackToTop');

            if (!table) {
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Table rows
            |--------------------------------------------------------------------------
            */

            const getRows = function () {

                return Array.from(
                    table.querySelectorAll(
                        'tbody tr[data-dcr-row]'
                    )
                );

            };

            /*
            |--------------------------------------------------------------------------
            | Filter table
            |--------------------------------------------------------------------------
            */

            const filterTable = function () {

                const search =
                    (searchInput?.value || '')
                        .trim()
                        .toLowerCase();

                const selectedStatus =
                    statusFilter?.value || '';

                const rows = getRows();

                let visible = 0;

                rows.forEach(function (row) {

                    const employee =
                        (
                            row.dataset.employee || ''
                        ).toLowerCase();

                    const status =
                        row.dataset.status || '';

                    const matchesSearch =
                        !search ||
                        employee.includes(search);

                    const matchesStatus =
                        !selectedStatus ||
                        status === selectedStatus;

                    const show =
                        matchesSearch &&
                        matchesStatus;

                    row.style.display =
                        show ? '' : 'none';

                    if (show) {
                        visible++;
                    }

                });

                if (noResults) {

                    if (
                        rows.length > 0 &&
                        visible === 0
                    ) {

                        noResults.classList.remove(
                            'hidden'
                        );

                    } else {

                        noResults.classList.add(
                            'hidden'
                        );
                    }
                }

                if (filterSummary) {

                    const filtering =
                        search ||
                        selectedStatus;

                    if (filtering) {

                        filterSummary.classList.remove(
                            'hidden'
                        );

                        let statusText =
                            selectedStatus
                                ? (
                                    @json($statusLabels)
                                )[selectedStatus] || selectedStatus
                                : 'كل الحالات';

                        filterSummary.innerHTML =
                            '<i class="fa-solid fa-filter ml-1"></i>' +
                            ' عرض ' +
                            visible +
                            ' من ' +
                            rows.length +
                            ' موظف · الحالة: ' +
                            statusText;

                    } else {

                        filterSummary.classList.add(
                            'hidden'
                        );

                    }
                }

            };

            /*
            |--------------------------------------------------------------------------
            | Search
            |--------------------------------------------------------------------------
            */

            if (searchInput) {

                searchInput.addEventListener(
                    'input',
                    filterTable
                );

            }

            /*
            |--------------------------------------------------------------------------
            | Status filter
            |--------------------------------------------------------------------------
            */

            if (statusFilter) {

                statusFilter.addEventListener(
                    'change',
                    filterTable
                );

            }

            /*
            |--------------------------------------------------------------------------
            | Keyboard shortcut
            |--------------------------------------------------------------------------
            */

            document.addEventListener(
                'keydown',
                function (event) {

                    if (
                        event.ctrlKey &&
                        event.key.toLowerCase() === 'k'
                    ) {

                        event.preventDefault();

                        if (searchInput) {

                            searchInput.focus();
                            searchInput.select();

                        }

                    }

                    if (
                        event.key === 'Escape' &&
                        document.activeElement === searchInput
                    ) {

                        searchInput.value = '';

                        if (statusFilter) {
                            statusFilter.value = '';
                        }

                        filterTable();

                    }

                }
            );

            /*
            |--------------------------------------------------------------------------
            | Sorting
            |--------------------------------------------------------------------------
            */

            const sortableHeaders =
                table.querySelectorAll(
                    'thead th[data-sortable]'
                );

            sortableHeaders.forEach(
                function (header) {

                    header.addEventListener(
                        'click',
                        function () {

                            const columnIndex =
                                Number(
                                    header.dataset.column
                                );

                            const tbody =
                                table.querySelector('tbody');

                            if (!tbody) {
                                return;
                            }

                            const rows =
                                Array.from(
                                    tbody.querySelectorAll(
                                        'tr[data-dcr-row]'
                                    )
                                );

                            const currentDirection =
                                header.dataset.direction === 'asc'
                                    ? 'desc'
                                    : 'asc';

                            header.dataset.direction =
                                currentDirection;

                            rows.sort(
                                function (a, b) {

                                    const aCell =
                                        a.children[columnIndex];

                                    const bCell =
                                        b.children[columnIndex];

                                    const aText =
                                        aCell
                                            ? aCell.textContent.trim()
                                            : '';

                                    const bText =
                                        bCell
                                            ? bCell.textContent.trim()
                                            : '';

                                    const aNumber =
                                        parseFloat(
                                            aText
                                                .replace(
                                                    /[^0-9.-]/g,
                                                    ''
                                                )
                                        );

                                    const bNumber =
                                        parseFloat(
                                            bText
                                                .replace(
                                                    /[^0-9.-]/g,
                                                    ''
                                                )
                                        );

                                    const bothNumbers =
                                        !Number.isNaN(aNumber) &&
                                        !Number.isNaN(bNumber);

                                    if (bothNumbers) {

                                        return currentDirection === 'asc'
                                            ? aNumber - bNumber
                                            : bNumber - aNumber;

                                    }

                                    return currentDirection === 'asc'
                                        ? aText.localeCompare(
                                            bText,
                                            'ar'
                                        )
                                        : bText.localeCompare(
                                            aText,
                                            'ar'
                                        );

                                }
                            );

                            rows.forEach(
                                function (row) {
                                    tbody.appendChild(row);
                                }
                            );

                            /*
                            | Reset all sorting icons.
                            */
                            sortableHeaders.forEach(
                                function (otherHeader) {

                                    const icon =
                                        otherHeader.querySelector(
                                            'i'
                                        );

                                    if (icon) {

                                        icon.className =
                                            'fa-solid fa-sort mr-1 text-[9px] text-dim';

                                    }

                                }
                            );

                            const icon =
                                header.querySelector('i');

                            if (icon) {

                                icon.className =
                                    currentDirection === 'asc'
                                        ? 'fa-solid fa-sort-up mr-1 text-[9px] text-brand'
                                        : 'fa-solid fa-sort-down mr-1 text-[9px] text-brand';

                            }

                        }
                    );

                }
            );

            /*
            |--------------------------------------------------------------------------
            | Confirm DCR actions
            |--------------------------------------------------------------------------
            */

            const actionButtons =
                document.querySelectorAll(
                    '.dcr-action-form button[data-confirm]'
                );

            actionButtons.forEach(
                function (button) {

                    button.addEventListener(
                        'click',
                        function (event) {

                            const message =
                                button.dataset.confirm;

                            if (
                                message &&
                                !window.confirm(message)
                            ) {

                                event.preventDefault();

                            }

                        }
                    );

                }
            );

            /*
            |--------------------------------------------------------------------------
            | Prevent double action submission
            |--------------------------------------------------------------------------
            */

            const actionForms =
                document.querySelectorAll(
                    '.dcr-action-form'
                );

            actionForms.forEach(
                function (actionForm) {

                    actionForm.addEventListener(
                        'submit',
                        function (event) {

                            if (
                                actionForm.dataset.submitting ===
                                'true'
                            ) {

                                event.preventDefault();
                                return;

                            }

                            actionForm.dataset.submitting =
                                'true';

                            const clickedButton =
                                document.activeElement;

                            if (
                                clickedButton &&
                                clickedButton.tagName ===
                                'BUTTON'
                            ) {

                                clickedButton.disabled =
                                    true;

                                clickedButton.classList.add(
                                    'opacity-70',
                                    'cursor-not-allowed'
                                );

                            }

                        }
                    );

                }
            );

            /*
            |--------------------------------------------------------------------------
            | CSV export
            |--------------------------------------------------------------------------
            */

            if (exportButton) {

                exportButton.addEventListener(
                    'click',
                    function () {

                        const rows =
                            getRows().filter(
                                function (row) {

                                    return row.style.display !==
                                        'none';

                                }
                            );

                        if (!rows.length) {
                            return;
                        }

                        const headers = [
                            'الموظف',
                            'الحالات',
                            'مكالمات',
                            'زيارات',
                            'وعود',
                            'قيمة الوعود',
                            'المحصل',
                            'الحالة'
                        ];

                        const data = [
                            headers
                        ];

                        rows.forEach(
                            function (row) {

                                const cells =
                                    row.children;

                                const values = [];

                                for (
                                    let i = 0;
                                    i < 8;
                                    i++
                                ) {

                                    values.push(
                                        cells[i]
                                            ? cells[i]
                                                .innerText
                                                .trim()
                                                .replace(
                                                    /\s+/g,
                                                    ' '
                                                )
                                            : ''
                                    );

                                }

                                data.push(values);

                            }
                        );

                        const csv =
                            data.map(
                                function (row) {

                                    return row.map(
                                        function (value) {

                                            return '"' +
                                                String(value)
                                                    .replace(
                                                        /"/g,
                                                        '""'
                                                    ) +
                                                '"';

                                        }
                                    ).join(',');

                                }
                            ).join('\n');

                        /*
                        | BOM ensures Arabic opens correctly in Excel.
                        */
                        const blob =
                            new Blob(
                                [
                                    '\uFEFF' + csv
                                ],
                                {
                                    type:
                                        'text/csv;charset=utf-8;'
                                }
                            );

                        const url =
                            URL.createObjectURL(blob);

                        const link =
                            document.createElement('a');

                        link.href = url;

                        link.download =
                            'DCR-' +
                            @json($bank->name) +
                            '-' +
                            @json($date->toDateString()) +
                            '.csv';

                        document.body.appendChild(link);

                        link.click();

                        link.remove();

                        URL.revokeObjectURL(url);

                    }
                );

            }

            /*
            |--------------------------------------------------------------------------
            | Back to top
            |--------------------------------------------------------------------------
            */

            if (backToTop) {

                window.addEventListener(
                    'scroll',
                    function () {

                        if (window.scrollY > 500) {

                            backToTop.classList.remove(
                                'hidden'
                            );

                            backToTop.classList.add(
                                'flex'
                            );

                        } else {

                            backToTop.classList.add(
                                'hidden'
                            );

                            backToTop.classList.remove(
                                'flex'
                            );

                        }

                    },
                    {
                        passive: true
                    }
                );

                backToTop.addEventListener(
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
            | Initial filter state
            |--------------------------------------------------------------------------
            */

            filterTable();

        });
    </script>
@endpush