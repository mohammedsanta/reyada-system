{{-- resources/views/banks/complaints.blade.php --}}
@extends('layouts.app')

@section('title', 'إدارة الشكاوى - ' . $bank->name)

@section('content')

    @php
        /*
        |--------------------------------------------------------------------------
        | Safe frontend calculations
        |--------------------------------------------------------------------------
        | No new controller variables are required.
        | Everything here is calculated from the existing data.
        */

        $complaintsCollection = collect($complaints ?? []);

        $totalComplaints = (int) ($counts['all'] ?? $complaintsCollection->count());

        $openCount = (int) ($counts['open'] ?? 0);
        $reviewCount = (int) ($counts['in_review'] ?? 0);
        $resolvedCount = (int) ($counts['resolved'] ?? 0);
        $urgentCount = (int) ($urgent ?? 0);

        $closedCount = $complaintsCollection->filter(function ($complaint) {
            return in_array($complaint->status ?? '', ['resolved', 'rejected']);
        })->count();

        $unassignedCount = $complaintsCollection->filter(function ($complaint) {
            return trim((string) ($complaint->assigned_name ?? '')) === '';
        })->count();

        $todayDueCount = $complaintsCollection->filter(function ($complaint) {
            return ($complaint->due ?? '') === 'اليوم';
        })->count();

        $overdueCount = $complaintsCollection->filter(function ($complaint) {
            $due = trim((string) ($complaint->due ?? ''));

            if ($due === '' || $due === 'اليوم') {
                return false;
            }

            try {
                return \Carbon\Carbon::parse($due)->isPast()
                    && !in_array($complaint->status ?? '', ['resolved', 'rejected']);
            } catch (\Throwable $e) {
                return false;
            }
        })->count();

        $assignedCount = max(0, $complaintsCollection->count() - $unassignedCount);

        $resolutionRate = $totalComplaints > 0
            ? round(($resolvedCount / $totalComplaints) * 100, 1)
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Status labels for frontend summary only
        |--------------------------------------------------------------------------
        */

        $statusLabels = collect($statuses ?? [])
            ->mapWithKeys(function ($label, $key) {
                return [$key => $label];
            });

        /*
        |--------------------------------------------------------------------------
        | Current filters
        |--------------------------------------------------------------------------
        */

        $activeFilters = collect([
            $status ?? '',
            $search ?? '',
        ])->filter(function ($value) {
            return $value !== null && trim((string) $value) !== '';
        })->count();

        /*
        |--------------------------------------------------------------------------
        | Query helper
        |--------------------------------------------------------------------------
        */

        $clearFiltersUrl = route('banks.complaints.index', [
            'bank' => $bank->id,
        ]);
    @endphp


    {{-- ================================================================
         PAGE HEADER
         ================================================================ --}}

    <x-bank-header
        :bank="$bank"
        title="إدارة الشكاوى"
        subtitle="تسجيل ومتابعة شكاوى العملاء"
    >
        <x-slot:actions>

            <button
                type="button"
                class="btn btn-primary btn-sm"
                data-open-modal="complaintModal"
            >
                <i class="fa-solid fa-plus text-xs"></i>
                تسجيل شكوى
            </button>

        </x-slot:actions>
    </x-bank-header>

    <x-flash />


    {{-- ================================================================
         QUICK OVERVIEW
         ================================================================ --}}

    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <x-metric-card
            label="شكاوى مفتوحة"
            :value="$openCount"
            icon="fa-envelope-open-text"
            color="warning"
        />

        <x-metric-card
            label="قيد المراجعة"
            :value="$reviewCount"
            icon="fa-hourglass-half"
            color="info"
        />

        <x-metric-card
            label="تم حلها"
            :value="$resolvedCount"
            icon="fa-circle-check"
            color="brand"
        />

        <x-metric-card
            label="عاجلة"
            :value="$urgentCount"
            icon="fa-triangle-exclamation"
            color="danger"
        />

    </section>


    {{-- ================================================================
         OPERATIONAL SUMMARY
         ================================================================ --}}

    <section class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

        {{-- Resolution rate --}}
        <div class="card">

            <div class="flex items-start justify-between gap-3">

                <div>
                    <p class="text-xs text-dim">معدل الحل</p>

                    <p class="mt-1 text-xl font-bold text-fg">
                        {{ number_format($resolutionRate, 1) }}%
                    </p>
                </div>

                <div class="flex h-9 w-9 items-center justify-center rounded-xl border border-brand/20 bg-brand/10 text-brand">
                    <i class="fa-solid fa-chart-line text-sm"></i>
                </div>

            </div>

            <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-white/[0.06]">

                <div
                    class="h-full rounded-full bg-brand transition-all"
                    style="width: {{ min(100, max(0, $resolutionRate)) }}%"
                ></div>

            </div>

            <p class="mt-2 text-[10px] text-dim">
                نسبة الشكاوى المحلولة من إجمالي الشكاوى
            </p>

        </div>


        {{-- Due today --}}
        <div class="card">

            <div class="flex items-start justify-between gap-3">

                <div>
                    <p class="text-xs text-dim">مستحقة اليوم</p>

                    <p class="mt-1 text-xl font-bold text-fg">
                        {{ number_format($todayDueCount) }}
                    </p>
                </div>

                <div class="flex h-9 w-9 items-center justify-center rounded-xl border border-warning/20 bg-warning/10 text-warning">
                    <i class="fa-solid fa-calendar-day text-sm"></i>
                </div>

            </div>

            <p class="mt-3 text-[10px] text-dim">
                شكاوى تحتاج متابعة في موعدها اليوم
            </p>

        </div>


        {{-- Overdue --}}
        <div class="card">

            <div class="flex items-start justify-between gap-3">

                <div>
                    <p class="text-xs text-dim">متأخرة</p>

                    <p class="mt-1 text-xl font-bold text-fg">
                        {{ number_format($overdueCount) }}
                    </p>
                </div>

                <div class="flex h-9 w-9 items-center justify-center rounded-xl border border-danger/20 bg-danger/10 text-danger">
                    <i class="fa-solid fa-clock text-sm"></i>
                </div>

            </div>

            <p class="mt-3 text-[10px] text-dim">
                شكاوى تجاوزت موعد الرد ولم تغلق
            </p>

        </div>


        {{-- Assignment --}}
        <div class="card">

            <div class="flex items-start justify-between gap-3">

                <div>
                    <p class="text-xs text-dim">بدون إسناد</p>

                    <p class="mt-1 text-xl font-bold text-fg">
                        {{ number_format($unassignedCount) }}
                    </p>
                </div>

                <div class="flex h-9 w-9 items-center justify-center rounded-xl border border-info/20 bg-info/10 text-info">
                    <i class="fa-solid fa-user-slash text-sm"></i>
                </div>

            </div>

            <p class="mt-3 text-[10px] text-dim">
                شكاوى لم يتم تعيين مسؤول لها
            </p>

        </div>

    </section>


    {{-- ================================================================
         FILTERS / TABS
         ================================================================ --}}

    <div class="mb-4 flex flex-wrap items-center justify-between gap-4">

        <div class="segmented">

            {{-- All --}}
            <a
                href="{{ route('banks.complaints.index', [
                    'bank' => $bank->id,
                    'search' => $search
                ]) }}"
                class="segmented-item {{ $status === '' ? 'segmented-item-active' : '' }}"
            >
                الكل
                <span class="text-[10px] opacity-70">
                    ({{ $counts['all'] ?? 0 }})
                </span>
            </a>

            {{-- Existing statuses --}}
            @foreach($statuses as $key => $label)

                <a
                    href="{{ route('banks.complaints.index', [
                        'bank' => $bank->id,
                        'status' => $key,
                        'search' => $search
                    ]) }}"
                    class="segmented-item {{ $status === $key ? 'segmented-item-active' : '' }}"
                >
                    {{ $label }}

                    <span class="text-[10px] opacity-70">
                        ({{ $counts[$key] ?? 0 }})
                    </span>

                </a>

            @endforeach

        </div>


        {{-- Search --}}
        <form
            method="GET"
            action="{{ route('banks.complaints.index', $bank->id) }}"
            class="relative w-72"
            id="complaintsSearchForm"
        >

            <input
                type="hidden"
                name="status"
                value="{{ $status }}"
            >

            <i
                class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-dim"
            ></i>

            <input
                id="complaintsSearch"
                name="search"
                type="search"
                value="{{ $search }}"
                placeholder="رقم الشكوى أو اسم العميل..."
                class="form-input pl-9"
                aria-label="بحث"
                autocomplete="off"
            >

        </form>

    </div>


    {{-- ================================================================
         ACTIVE FILTERS / QUICK TOOLS
         ================================================================ --}}

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">

        <div class="flex flex-wrap items-center gap-2">

            @if($activeFilters > 0)

                <span class="text-[11px] text-dim">
                    الفلاتر النشطة:
                </span>

                @if($status !== '')

                    <span class="badge badge-info">
                        الحالة:
                        {{ $statusLabels->get($status, $status) }}
                    </span>

                @endif

                @if(trim((string) $search) !== '')

                    <span class="badge badge-neutral">
                        البحث:
                        {{ $search }}
                    </span>

                @endif

                <a
                    href="{{ $clearFiltersUrl }}"
                    class="text-[11px] font-semibold text-info hover:underline"
                >
                    مسح الفلاتر
                </a>

            @else

                <span class="text-[11px] text-dim">
                    عرض جميع الشكاوى
                </span>

            @endif

        </div>


        <div class="flex flex-wrap items-center gap-2">

            <span class="text-[11px] text-dim">
                {{ number_format($complaintsCollection->count()) }} سجل ظاهر
            </span>

            <span class="hidden text-dim sm:inline">•</span>

            <button
                type="button"
                id="refreshComplaints"
                class="btn btn-secondary btn-sm"
                title="تحديث الصفحة"
            >
                <i class="fa-solid fa-rotate text-xs"></i>
                تحديث
            </button>

            <button
                type="button"
                id="printComplaints"
                class="btn btn-secondary btn-sm"
                title="طباعة قائمة الشكاوى"
            >
                <i class="fa-solid fa-print text-xs"></i>
                طباعة
            </button>

            <button
                type="button"
                id="exportComplaints"
                class="btn btn-secondary btn-sm"
                title="تصدير CSV"
            >
                <i class="fa-solid fa-file-csv text-xs"></i>
                CSV
            </button>

        </div>

    </div>


    {{-- ================================================================
         TABLE
         ================================================================ --}}

    <div class="table-wrap">

        <table
            class="data-table whitespace-nowrap"
            id="complaintsTable"
        >

            <thead>

                <tr>

                    <th>
                        <button
                            type="button"
                            class="complaint-sort-btn"
                            data-sort-column="0"
                        >
                            رقم الشكوى
                            <i class="fa-solid fa-sort mr-1 text-[9px] text-dim"></i>
                        </button>
                    </th>

                    <th>
                        <button
                            type="button"
                            class="complaint-sort-btn"
                            data-sort-column="1"
                        >
                            العميل
                            <i class="fa-solid fa-sort mr-1 text-[9px] text-dim"></i>
                        </button>
                    </th>

                    <th>
                        <button
                            type="button"
                            class="complaint-sort-btn"
                            data-sort-column="2"
                        >
                            الموضوع
                            <i class="fa-solid fa-sort mr-1 text-[9px] text-dim"></i>
                        </button>
                    </th>

                    <th>
                        <button
                            type="button"
                            class="complaint-sort-btn"
                            data-sort-column="3"
                        >
                            الأولوية
                            <i class="fa-solid fa-sort mr-1 text-[9px] text-dim"></i>
                        </button>
                    </th>

                    <th>
                        <button
                            type="button"
                            class="complaint-sort-btn"
                            data-sort-column="4"
                        >
                            الحالة
                            <i class="fa-solid fa-sort mr-1 text-[9px] text-dim"></i>
                        </button>
                    </th>

                    <th>المسؤول</th>

                    <th>
                        <button
                            type="button"
                            class="complaint-sort-btn"
                            data-sort-column="6"
                        >
                            التسجيل
                            <i class="fa-solid fa-sort mr-1 text-[9px] text-dim"></i>
                        </button>
                    </th>

                    <th>
                        <button
                            type="button"
                            class="complaint-sort-btn"
                            data-sort-column="7"
                        >
                            الموعد النهائي
                            <i class="fa-solid fa-sort mr-1 text-[9px] text-dim"></i>
                        </button>
                    </th>

                    <th></th>

                </tr>

            </thead>


            <tbody id="complaintsTableBody">

                @forelse($complaints as $complaint)

                    @php
                        $rowAssigned = trim((string) ($complaint->assigned_name ?? ''));

                        $rowIsClosed = in_array(
                            $complaint->status ?? '',
                            ['resolved', 'rejected']
                        );

                        $rowIsDueToday = ($complaint->due ?? '') === 'اليوم';

                        $rowPriority = $complaint->priority ?? '';
                        $rowStatus = $complaint->status ?? '';
                    @endphp

                    <tr
                        data-search="{{ strtolower(
                            ($complaint->reference ?? '') . ' ' .
                            ($complaint->client_name ?? '') . ' ' .
                            ($complaint->client_code ?? '') . ' ' .
                            ($complaint->subject ?? '') . ' ' .
                            ($rowAssigned ?? '')
                        ) }}"
                        data-status="{{ $rowStatus }}"
                        data-priority="{{ $rowPriority }}"
                        data-closed="{{ $rowIsClosed ? '1' : '0' }}"
                        data-due-today="{{ $rowIsDueToday ? '1' : '0' }}"
                    >

                        {{-- Reference --}}
                        <td>

                            <div class="flex items-center gap-2">

                                <span class="badge badge-info font-mono">
                                    {{ $complaint->reference }}
                                </span>

                                <button
                                    type="button"
                                    class="copy-row-value hidden text-dim hover:text-brand sm:inline-flex"
                                    data-copy="{{ $complaint->reference }}"
                                    title="نسخ رقم الشكوى"
                                    aria-label="نسخ رقم الشكوى"
                                >
                                    <i class="fa-regular fa-copy text-[10px]"></i>
                                </button>

                            </div>

                        </td>


                        {{-- Client --}}
                        <td>

                            <div class="flex items-center gap-2">

                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-info/20 bg-info/10 text-info">
                                    <i class="fa-solid fa-user text-[11px]"></i>
                                </div>

                                <div class="min-w-0">

                                    <div class="font-semibold text-fg">
                                        {{ $complaint->client_name }}
                                    </div>

                                    <div class="text-[11px] text-dim">
                                        {{ $complaint->client_code }}
                                    </div>

                                </div>

                            </div>

                        </td>


                        {{-- Subject --}}
                        <td class="whitespace-normal min-w-[220px]">

                            <div class="max-w-[360px]">

                                <div class="font-semibold text-fg">
                                    {{ $complaint->subject }}
                                </div>

                                @if(!empty($complaint->description))

                                    <div class="mt-1 line-clamp-2 text-[11px] leading-5 text-dim">
                                        {{ $complaint->description }}
                                    </div>

                                @endif

                            </div>

                        </td>


                        {{-- Priority --}}
                        <td>
                            <x-status-badge
                                type="priority"
                                :status="$complaint->priority"
                            />
                        </td>


                        {{-- Status --}}
                        <td>
                            <x-status-badge
                                type="complaint"
                                :status="$complaint->status"
                            />
                        </td>


                        {{-- Assigned --}}
                        <td>

                            @if($rowAssigned !== '')

                                <div class="flex items-center gap-2">

                                    <div class="flex h-7 w-7 items-center justify-center rounded-lg border border-white/10 bg-white/[0.03] text-[10px] text-dim">
                                        <i class="fa-solid fa-user-check"></i>
                                    </div>

                                    <span>
                                        {{ $complaint->assigned_name }}
                                    </span>

                                </div>

                            @else

                                <span class="text-xs text-dim">
                                    <i class="fa-solid fa-user-slash ml-1"></i>
                                    بدون إسناد
                                </span>

                            @endif

                        </td>


                        {{-- Created --}}
                        <td>
                            <span class="text-xs">
                                {{ $complaint->created }}
                            </span>
                        </td>


                        {{-- Due --}}
                        <td
                            @class([
                                'text-danger font-semibold' => $rowIsDueToday,
                            ])
                        >

                            <div class="flex items-center gap-2">

                                @if($rowIsDueToday)

                                    <i class="fa-solid fa-triangle-exclamation text-[10px]"></i>

                                @endif

                                <span>
                                    {{ $complaint->due }}
                                </span>

                            </div>

                        </td>


                        {{-- Actions --}}
                        <td>

                            <div class="flex items-center justify-end gap-1">

                                <a
                                    href="{{ route('banks.complaints.show', [$bank->id, $complaint->id]) }}"
                                    class="btn btn-secondary btn-sm"
                                    title="عرض الشكوى"
                                >
                                    <i class="fa-solid fa-eye text-xs"></i>
                                    <span class="hidden sm:inline">عرض</span>
                                </a>

                                <button
                                    type="button"
                                    class="copy-row-value btn btn-secondary btn-sm"
                                    data-copy="{{ $complaint->reference }}"
                                    title="نسخ المرجع"
                                    aria-label="نسخ المرجع"
                                >
                                    <i class="fa-regular fa-copy text-xs"></i>
                                </button>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="9">
                            <x-empty-state
                                icon="fa-headset"
                                text="لا توجد شكاوى مطابقة"
                            />
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

        {{-- Frontend no-result state --}}
        <div
            id="clientNoResults"
            class="hidden px-6 py-10 text-center"
        >

            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl border border-white/10 bg-white/[0.03] text-dim">
                <i class="fa-solid fa-filter-circle-xmark"></i>
            </div>

            <p class="mt-3 text-sm font-semibold text-fg">
                لا توجد نتائج مطابقة
            </p>

            <p class="mt-1 text-xs text-dim">
                جرّب تغيير البحث أو ترتيب البيانات.
            </p>

            <button
                type="button"
                id="clearClientSearch"
                class="mt-3 text-xs font-semibold text-info hover:underline"
            >
                مسح البحث
            </button>

        </div>

    </div>


    {{-- ================================================================
         TABLE SUMMARY
         ================================================================ --}}

    @if($complaintsCollection->isNotEmpty())

        <div class="mt-3 flex flex-wrap items-center justify-between gap-3 text-[11px] text-dim">

            <div class="flex flex-wrap items-center gap-3">

                <span>
                    إجمالي السجلات:
                    <strong class="text-fg">
                        {{ number_format($totalComplaints) }}
                    </strong>
                </span>

                <span>•</span>

                <span>
                    مسندة:
                    <strong class="text-fg">
                        {{ number_format($assignedCount) }}
                    </strong>
                </span>

                <span>•</span>

                <span>
                    بدون إسناد:
                    <strong class="text-warning">
                        {{ number_format($unassignedCount) }}
                    </strong>
                </span>

            </div>

            <div class="flex items-center gap-2">

                <span>
                    محلولة:
                    <strong class="text-brand">
                        {{ number_format($resolvedCount) }}
                    </strong>
                </span>

                <span>•</span>

                <span>
                    عاجلة:
                    <strong class="text-danger">
                        {{ number_format($urgentCount) }}
                    </strong>
                </span>

            </div>

        </div>

    @endif


{{-- NEW COMPLAINT --}}
@include('banks.partials._create-complaint-form', [
    'bank' => $bank,
    'clients' => $clients,
    'employees' => $employees,
    'priorities' => $priorities,
    'sources' => $sources,
])


    {{-- ================================================================
         BACK TO TOP
         ================================================================ --}}

    <button
        type="button"
        id="backToTop"
        class="fixed bottom-5 left-5 z-40 hidden h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-[#111418] text-dim shadow-lg transition hover:text-brand"
        title="العودة للأعلى"
        aria-label="العودة للأعلى"
    >
        <i class="fa-solid fa-arrow-up text-xs"></i>
    </button>


@endsection


@push('scripts')

    @if($errors->any())

        <script>
            document.addEventListener('DOMContentLoaded', function () {

                const modal = document.getElementById('complaintModal');

                if (modal && typeof modal.showModal === 'function') {
                    modal.showModal();
                }

            });
        </script>

    @endif


    <script>
        document.addEventListener('DOMContentLoaded', function () {

            /*
            |--------------------------------------------------------------------------
            | Elements
            |--------------------------------------------------------------------------
            */

            const table = document.getElementById('complaintsTable');
            const tableBody = document.getElementById('complaintsTableBody');
            const searchInput = document.getElementById('complaintsSearch');

            const noResults = document.getElementById('clientNoResults');
            const clearClientSearch = document.getElementById('clearClientSearch');

            const refreshButton = document.getElementById('refreshComplaints');
            const printButton = document.getElementById('printComplaints');
            const exportButton = document.getElementById('exportComplaints');

            const newComplaintForm = document.getElementById('newComplaintForm');
            const createComplaintButton = document.getElementById('createComplaintButton');

            const backToTop = document.getElementById('backToTop');


            /*
            |--------------------------------------------------------------------------
            | Search
            |--------------------------------------------------------------------------
            */

            function normalizeSearchValue(value) {

                return String(value || '')
                    .toLowerCase()
                    .trim();

            }


            function filterRows() {

                if (!tableBody) {
                    return;
                }

                const query = normalizeSearchValue(
                    searchInput ? searchInput.value : ''
                );

                const rows = Array.from(
                    tableBody.querySelectorAll('tr[data-search]')
                );

                let visibleCount = 0;

                rows.forEach(function (row) {

                    const searchable = normalizeSearchValue(
                        row.dataset.search || ''
                    );

                    const matches = !query || searchable.includes(query);

                    row.style.display = matches ? '' : 'none';

                    if (matches) {
                        visibleCount++;
                    }

                });


                if (noResults) {

                    if (rows.length > 0 && visibleCount === 0) {

                        noResults.classList.remove('hidden');

                    } else {

                        noResults.classList.add('hidden');

                    }

                }

            }


            if (searchInput) {

                searchInput.addEventListener('input', filterRows);

            }


            if (clearClientSearch) {

                clearClientSearch.addEventListener('click', function () {

                    if (searchInput) {

                        searchInput.value = '';

                        searchInput.focus();

                        filterRows();

                    }

                });

            }


            /*
            |--------------------------------------------------------------------------
            | Ctrl + K => search
            |--------------------------------------------------------------------------
            */

            document.addEventListener('keydown', function (event) {

                if (
                    (event.ctrlKey || event.metaKey) &&
                    event.key.toLowerCase() === 'k'
                ) {

                    event.preventDefault();

                    if (searchInput) {
                        searchInput.focus();
                        searchInput.select();
                    }

                }


                /*
                |--------------------------------------------------------------------------
                | Escape => clear client-side search
                |--------------------------------------------------------------------------
                */

                if (
                    event.key === 'Escape' &&
                    document.activeElement === searchInput
                ) {

                    if (searchInput && searchInput.value !== '') {

                        searchInput.value = '';

                        filterRows();

                    }

                }

            });


            /*
            |--------------------------------------------------------------------------
            | Refresh
            |--------------------------------------------------------------------------
            */

            if (refreshButton) {

                refreshButton.addEventListener('click', function () {

                    refreshButton.disabled = true;

                    refreshButton.classList.add(
                        'opacity-70',
                        'cursor-not-allowed'
                    );

                    refreshButton.innerHTML =
                        '<i class="fa-solid fa-rotate fa-spin text-xs"></i>' +
                        ' تحديث...';

                    window.location.reload();

                });

            }


            /*
            |--------------------------------------------------------------------------
            | Print
            |--------------------------------------------------------------------------
            */

            if (printButton) {

                printButton.addEventListener('click', function () {

                    window.print();

                });

            }


            /*
            |--------------------------------------------------------------------------
            | Copy helper
            |--------------------------------------------------------------------------
            */

            async function copyValue(value, button) {

                if (!value) {
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

                    textarea.select();

                    try {
                        document.execCommand('copy');
                    } catch (e) {
                        // Clipboard fallback failed.
                    }

                    textarea.remove();

                }


                if (button) {

                    const originalHTML = button.innerHTML;

                    button.innerHTML =
                        '<i class="fa-solid fa-check text-xs"></i>';

                    button.classList.add('text-brand');

                    setTimeout(function () {

                        button.innerHTML = originalHTML;

                        button.classList.remove('text-brand');

                    }, 1200);

                }

            }


            /*
            |--------------------------------------------------------------------------
            | Copy complaint references
            |--------------------------------------------------------------------------
            */

            document.querySelectorAll('.copy-row-value').forEach(function (button) {

                button.addEventListener('click', function () {

                    copyValue(
                        this.dataset.copy || '',
                        this
                    );

                });

            });


            /*
            |--------------------------------------------------------------------------
            | CSV export
            |--------------------------------------------------------------------------
            */

            if (exportButton && table) {

                exportButton.addEventListener('click', function () {

                    const rows = Array.from(
                        table.querySelectorAll('tbody tr[data-search]')
                    ).filter(function (row) {

                        return row.style.display !== 'none';

                    });


                    if (!rows.length) {

                        return;

                    }


                    const headers = [
                        'رقم الشكوى',
                        'العميل',
                        'الموضوع',
                        'الأولوية',
                        'الحالة',
                        'المسؤول',
                        'التسجيل',
                        'الموعد النهائي'
                    ];


                    const csvRows = [headers];


                    rows.forEach(function (row) {

                        const cells = row.querySelectorAll('td');

                        if (cells.length < 8) {
                            return;
                        }


                        const values = [

                            cells[0]?.innerText.trim() || '',

                            cells[1]?.innerText.trim() || '',

                            cells[2]?.innerText.trim() || '',

                            cells[3]?.innerText.trim() || '',

                            cells[4]?.innerText.trim() || '',

                            cells[5]?.innerText.trim() || '',

                            cells[6]?.innerText.trim() || '',

                            cells[7]?.innerText.trim() || ''

                        ];


                        csvRows.push(values);

                    });


                    const csv = csvRows
                        .map(function (row) {

                            return row.map(function (value) {

                                const cleanValue = String(value)
                                    .replace(/"/g, '""');

                                return '"' + cleanValue + '"';

                            }).join(',');

                        })
                        .join('\n');


                    /*
                     * UTF-8 BOM keeps Arabic readable in Excel.
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

                    link.download =
                        'complaints-{{ $bank->id }}-{{ now()->format('Y-m-d') }}.csv';

                    document.body.appendChild(link);

                    link.click();

                    link.remove();

                    URL.revokeObjectURL(url);

                });

            }


            /*
            |--------------------------------------------------------------------------
            | Table sorting
            |--------------------------------------------------------------------------
            */

            let currentSortColumn = null;
            let currentSortDirection = 'asc';


            function getCellSortValue(cell) {

                if (!cell) {
                    return '';
                }

                return cell.innerText
                    .replace(/\s+/g, ' ')
                    .trim()
                    .toLowerCase();

            }


            document.querySelectorAll('.complaint-sort-btn').forEach(function (button) {

                button.addEventListener('click', function () {

                    if (!tableBody) {
                        return;
                    }


                    const columnIndex =
                        Number(this.dataset.sortColumn);


                    if (
                        currentSortColumn === columnIndex
                    ) {

                        currentSortDirection =
                            currentSortDirection === 'asc'
                                ? 'desc'
                                : 'asc';

                    } else {

                        currentSortColumn = columnIndex;
                        currentSortDirection = 'asc';

                    }


                    const rows = Array.from(
                        tableBody.querySelectorAll('tr[data-search]')
                    );


                    rows.sort(function (a, b) {

                        const aCells = a.children;
                        const bCells = b.children;

                        const aValue = getCellSortValue(
                            aCells[columnIndex]
                        );

                        const bValue = getCellSortValue(
                            bCells[columnIndex]
                        );


                        const aNumber = parseFloat(
                            aValue.replace(/[^0-9.-]/g, '')
                        );

                        const bNumber = parseFloat(
                            bValue.replace(/[^0-9.-]/g, '')
                        );


                        let comparison;


                        if (
                            !Number.isNaN(aNumber) &&
                            !Number.isNaN(bNumber)
                        ) {

                            comparison = aNumber - bNumber;

                        } else {

                            comparison =
                                aValue.localeCompare(
                                    bValue,
                                    'ar'
                                );

                        }


                        return currentSortDirection === 'asc'
                            ? comparison
                            : -comparison;

                    });


                    rows.forEach(function (row) {

                        tableBody.appendChild(row);

                    });


                    /*
                    |--------------------------------------------------------------------------
                    | Update sort icons
                    |--------------------------------------------------------------------------
                    */

                    document
                        .querySelectorAll('.complaint-sort-btn i')
                        .forEach(function (icon) {

                            icon.className =
                                'fa-solid fa-sort mr-1 text-[9px] text-dim';

                        });


                    const icon = this.querySelector('i');

                    if (icon) {

                        icon.className =
                            currentSortDirection === 'asc'
                                ? 'fa-solid fa-sort-up mr-1 text-[9px] text-brand'
                                : 'fa-solid fa-sort-down mr-1 text-[9px] text-brand';

                    }

                });

            });


            /*
            |--------------------------------------------------------------------------
            | New complaint form protection
            |--------------------------------------------------------------------------
            */

            let complaintSubmitting = false;


            if (newComplaintForm) {

                newComplaintForm.addEventListener('submit', function (event) {

                    if (complaintSubmitting) {

                        event.preventDefault();

                        return;

                    }


                    const subjectField =
                        newComplaintForm.querySelector(
                            '[name="subject"]'
                        );

                    const descriptionField =
                        newComplaintForm.querySelector(
                            '[name="description"]'
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Basic frontend validation
                    |--------------------------------------------------------------------------
                    */

                    if (
                        subjectField &&
                        subjectField.value.trim() === ''
                    ) {

                        event.preventDefault();

                        subjectField.focus();

                        return;

                    }


                    if (
                        descriptionField &&
                        descriptionField.value.trim() === ''
                    ) {

                        event.preventDefault();

                        descriptionField.focus();

                        return;

                    }


                    complaintSubmitting = true;


                    if (createComplaintButton) {

                        createComplaintButton.disabled = true;

                        createComplaintButton.classList.add(
                            'opacity-70',
                            'cursor-not-allowed'
                        );

                        createComplaintButton.innerHTML =
                            '<i class="fa-solid fa-spinner fa-spin text-xs"></i>' +
                            ' جاري التسجيل...';

                    }

                });

            }


            /*
            |--------------------------------------------------------------------------
            | Back to top
            |--------------------------------------------------------------------------
            */

            function updateBackToTop() {

                if (!backToTop) {
                    return;
                }


                if (window.scrollY > 350) {

                    backToTop.classList.remove('hidden');
                    backToTop.classList.add('flex');

                } else {

                    backToTop.classList.add('hidden');
                    backToTop.classList.remove('flex');

                }

            }


            window.addEventListener(
                'scroll',
                updateBackToTop,
                { passive: true }
            );


            updateBackToTop();


            if (backToTop) {

                backToTop.addEventListener('click', function () {

                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });

                });

            }


            /*
            |--------------------------------------------------------------------------
            | Initial filtering
            |--------------------------------------------------------------------------
            */

            filterRows();

        });
    </script>

@endpush