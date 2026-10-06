{{-- resources/views/ptp/index.blade.php --}}
@extends('layouts.app')

@section('title', 'مركز متابعة الوعود - ' . $bank->name)

@php
    // 'bank' is a required route parameter, so it must be part of every route() call below
    $query = ['bank' => $bank->id] + request()->query();

    // Full class names so Tailwind can detect them.
    $chipTones = [
        'warning' => 'bg-warning/15 text-warning',
        'cyan'    => 'bg-cyan/15 text-cyan',
        'brand'   => 'bg-brand/15 text-brand',
        'info'    => 'bg-info/15 text-info',
        'danger'  => 'bg-danger/15 text-danger',
    ];

    $weekdays = [
        'السبت',
        'الأحد',
        'الاثنين',
        'الثلاثاء',
        'الأربعاء',
        'الخميس',
        'الجمعة'
    ];


    /*
    |--------------------------------------------------------------------------
    | Safe Calendar Data
    |--------------------------------------------------------------------------
    | Calendar may be null when the backend does not provide calendar data.
    | Keep the page working without changing the existing backend structure.
    */

    $calendarData = is_array($calendar ?? null)
        ? $calendar
        : [];

    $calendarDays = collect(
        data_get($calendarData, 'days', [])
    );


    /*
    |--------------------------------------------------------------------------
    | Safe Board Data
    |--------------------------------------------------------------------------
    */

    $columnsData = collect(
        is_array($columns ?? null)
            ? $columns
            : []
    );


    /*
    |--------------------------------------------------------------------------
    | Frontend Summary
    |--------------------------------------------------------------------------
    */

    $boardTotal = $columnsData->sum(
        fn ($column) => collect(
            data_get($column, 'items', [])
        )->count()
    );


    $boardAmount = $columnsData->sum(
        fn ($column) => collect(
            data_get($column, 'items', [])
        )->sum(
            fn ($ptp) => (float) data_get($ptp, 'amount', 0)
        )
    );


    $calendarTotal = $calendarDays->sum(
        fn ($day) => collect(
            data_get($day, 'promises', [])
        )->count()
    );


    $calendarDaysWithPromises = $calendarDays->filter(
        fn ($day) => collect(
            data_get($day, 'promises', [])
        )->count() > 0
    )->count();


    $activeFiltersCount = collect([
        $filters['search'] ?? '',
        $filters['scope'] ?? '',
        $filters['month'] ?? '',
    ])->filter(
        fn ($value) => $value !== ''
            && $value !== null
    )->count();
@endphp

@section('content')

    {{-- ============================================================
         HEADER
    ============================================================= --}}
    <header class="card mb-6 flex flex-wrap items-center justify-between gap-4">

        <div class="flex items-center gap-4">

            <a
                href="{{ route('banks.show', $bank->id) }}"
                class="btn btn-secondary btn-sm"
                title="العودة إلى صفحة البنك"
            >
                <i class="fa-solid fa-arrow-right text-xs"></i>
                رجوع
            </a>

            <span
                class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-xl bg-white text-black"
            >
                @if($bank->logo ?? null)
                    <img
                        src="{{ $bank->logo }}"
                        alt="{{ $bank->name }}"
                        class="h-full w-full object-contain p-1"
                    >
                @else
                    <i class="fa-solid fa-building-columns text-lg"></i>
                @endif
            </span>

            <div>

                <div class="flex flex-wrap items-center gap-2">

                    <h1 class="text-xl font-bold text-fg">
                        مركز متابعة الوعود
                    </h1>

                    <span class="text-muted">
                        (PTP Hub)
                    </span>

                </div>

                <p class="mt-1 flex flex-wrap items-center gap-2 text-xs text-muted">

                    <span>
                        {{ $bank->name }}
                    </span>

                    <span class="badge badge-warning">
                        {{ $active }} وعود نشطة
                    </span>

                    @if($activeFiltersCount > 0)
                        <span class="badge badge-info">
                            {{ $activeFiltersCount }} فلاتر نشطة
                        </span>
                    @endif

                </p>

            </div>

        </div>


        {{-- Add promise + Board / Calendar switch --}}
        <div class="flex flex-wrap items-center gap-3">

            <a
                href="{{ route('banks.ptp.create', $bank->id) }}"
                class="btn btn-primary btn-sm"
            >
                <i class="fa-solid fa-plus text-xs"></i>
                إضافة وعد
            </a>


            <div class="segmented">

                <a
                    href="{{ route('banks.ptp.index', array_merge($query, ['view' => 'board'])) }}"
                    class="segmented-item {{ $filters['view'] === 'board' ? 'segmented-item-active' : '' }}"
                >
                    <i class="fa-solid fa-table-columns"></i>
                    لوحة العمل
                </a>

                <a
                    href="{{ route('banks.ptp.index', array_merge($query, ['view' => 'calendar'])) }}"
                    class="segmented-item {{ $filters['view'] === 'calendar' ? 'segmented-item-active' : '' }}"
                >
                    <i class="fa-regular fa-calendar"></i>
                    التقويم
                </a>

            </div>


            {{-- Quick actions --}}
            <div class="flex items-center gap-2">

                <button
                    type="button"
                    class="btn btn-secondary btn-sm"
                    onclick="window.print()"
                    title="طباعة الصفحة"
                >
                    <i class="fa-solid fa-print text-xs"></i>
                </button>

                <button
                    type="button"
                    class="btn btn-secondary btn-sm"
                    onclick="exportPTPData()"
                    title="تصدير البيانات"
                >
                    <i class="fa-solid fa-file-csv text-xs"></i>
                </button>

            </div>

        </div>

    </header>


    {{-- ============================================================
         QUICK OVERVIEW
    ============================================================= --}}
    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="card group relative overflow-hidden">

            <div class="flex items-center justify-between">

                <div>

                    <p class="text-xs font-semibold text-muted">
                        إجمالي الوعود المعروضة
                    </p>

                    <p
                        id="ptp-total-count"
                        class="mt-2 text-2xl font-bold text-fg"
                    >
                        {{ $boardTotal }}
                    </p>

                    <p class="mt-1 text-[11px] text-dim">
                        حسب الفلاتر الحالية
                    </p>

                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-info/10">
                    <i class="fa-solid fa-handshake text-info"></i>
                </div>

            </div>

        </div>


        <div class="card group relative overflow-hidden">

            <div class="flex items-center justify-between">

                <div>

                    <p class="text-xs font-semibold text-muted">
                        إجمالي قيمة الوعود
                    </p>

                    <p class="mt-2 text-2xl font-bold text-fg">
                        {{ number_format($boardAmount) }}
                    </p>

                    <p class="mt-1 text-[11px] text-dim">
                        EGP
                    </p>

                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand/10">
                    <i class="fa-solid fa-money-bill-wave text-brand"></i>
                </div>

            </div>

        </div>


        <div class="card group relative overflow-hidden">

            <div class="flex items-center justify-between">

                <div>

                    <p class="text-xs font-semibold text-muted">
                        الوعود النشطة
                    </p>

                    <p class="mt-2 text-2xl font-bold text-fg">
                        {{ $active }}
                    </p>

                    <p class="mt-1 text-[11px] text-dim">
                        تحتاج إلى متابعة
                    </p>

                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-warning/10">
                    <i class="fa-solid fa-clock text-warning"></i>
                </div>

            </div>

        </div>


        <div class="card group relative overflow-hidden">

            <div class="flex items-center justify-between">

                <div>

                    <p class="text-xs font-semibold text-muted">
                        وعود التقويم
                    </p>

                    <p class="mt-2 text-2xl font-bold text-fg">
                        {{ $calendarTotal }}
                    </p>

                    <p class="mt-1 text-[11px] text-dim">
                        ضمن الشهر الحالي
                    </p>

                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan/10">
                    <i class="fa-regular fa-calendar-check text-cyan"></i>
                </div>

            </div>

        </div>

    </section>


    {{-- ============================================================
         ORIGINAL STATISTICS
    ============================================================= --}}
    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        @foreach($stats as $stat)

            <x-metric-card
                :label="$stat['label']"
                :value="$stat['value']"
                :icon="$stat['icon']"
                :color="$stat['color']"
            />

        @endforeach

    </section>


    {{-- ============================================================
         TOOLBAR
    ============================================================= --}}
    <form
        method="GET"
        action="{{ route('banks.ptp.index', $bank->id) }}"
        class="card filter-bar"
        id="ptp-filter-form"
    >

        <input
            type="hidden"
            name="view"
            value="{{ $filters['view'] }}"
        >

        @if($filters['month'] !== '')
            <input
                type="hidden"
                name="month"
                value="{{ $filters['month'] }}"
            >
        @endif


        {{-- Refresh --}}
        <a
            href="{{ request()->fullUrl() }}"
            class="btn btn-secondary"
            title="تحديث"
        >
            <i class="fa-solid fa-rotate"></i>
        </a>


        {{-- Search --}}
        <div class="min-w-[240px] flex-1">

            <label
                for="search"
                class="sr-only"
            >
                بحث
            </label>

            <div class="relative">

                <i
                    class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-dim"
                ></i>

                <input
                    id="search"
                    name="search"
                    type="search"
                    value="{{ $filters['search'] }}"
                    placeholder="بحث برقم/اسم العميل..."
                    class="form-input pl-9"
                    autocomplete="off"
                >

                <span
                    class="pointer-events-none absolute right-3 top-1/2 hidden -translate-y-1/2 text-[10px] text-dim sm:block"
                >
                    Ctrl + K
                </span>

            </div>

        </div>


        {{-- Scope --}}
        <div class="w-44">

            <label
                for="scope"
                class="sr-only"
            >
                تصفية الوعود
            </label>

            <select
                id="scope"
                name="scope"
                class="form-input"
                onchange="this.form.submit()"
            >

                @foreach($scopes as $value => $label)

                    <option
                        value="{{ $value }}"
                        @selected($filters['scope'] === $value)
                    >
                        {{ $label }}
                    </option>

                @endforeach

            </select>

        </div>


        {{-- Search --}}
        <button
            type="submit"
            class="btn btn-primary"
        >
            <i class="fa-solid fa-magnifying-glass text-xs"></i>
            بحث
        </button>


        {{-- Clear filters --}}
        @if($activeFiltersCount > 0)

            <a
                href="{{ route('banks.ptp.index', ['bank' => $bank->id, 'view' => $filters['view']]) }}"
                class="btn btn-secondary"
                title="مسح كل الفلاتر"
            >
                <i class="fa-solid fa-filter-circle-xmark text-xs"></i>
                مسح
            </a>

        @endif

    </form>


    {{-- ============================================================
         ACTIVE FILTERS
    ============================================================= --}}
    @if($activeFiltersCount > 0)

        <section class="mb-5 mt-3 flex flex-wrap items-center gap-2">

            <span class="text-xs font-semibold text-muted">
                الفلاتر الحالية:
            </span>


            @if(($filters['search'] ?? '') !== '')

                <span class="badge badge-info">
                    <i class="fa-solid fa-magnifying-glass ml-1"></i>
                    {{ $filters['search'] }}
                </span>

            @endif


            @if(($filters['scope'] ?? '') !== '')

                @php
                    $currentScopeLabel = $scopes[$filters['scope']] ?? $filters['scope'];
                @endphp

                <span class="badge badge-warning">
                    <i class="fa-solid fa-filter ml-1"></i>
                    {{ $currentScopeLabel }}
                </span>

            @endif


            @if(($filters['month'] ?? '') !== '')

                <span class="badge badge-brand">
                    <i class="fa-regular fa-calendar ml-1"></i>
                    {{ $filters['month'] }}
                </span>

            @endif

        </section>

    @endif


    {{-- ============================================================
         BOARD VIEW
    ============================================================= --}}
    @if($filters['view'] === 'board')


        {{-- Board toolbar --}}
        <section class="card mb-4">

            <div class="flex flex-wrap items-center justify-between gap-3">

                <div>

                    <div class="flex items-center gap-2">

                        <i class="fa-solid fa-table-columns text-brand"></i>

                        <h2 class="font-bold text-fg">
                            لوحة متابعة الوعود
                        </h2>

                    </div>

                    <p class="mt-1 text-xs text-muted">
                        متابعة حالة كل وعد وتنظيم الوعود حسب مرحلة التحصيل.
                    </p>

                </div>


                <div class="flex flex-wrap items-center gap-2">

                    <span class="badge badge-info">
                        {{ $boardTotal }} وعد
                    </span>

                    <span class="badge badge-brand">
                        {{ number_format($boardAmount) }} EGP
                    </span>

                </div>

            </div>

        </section>


        {{-- KANBAN BOARD --}}
        <section
            class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5"
            id="ptp-board"
        >

            @foreach($columns as $column)

                <div
                    class="min-w-0"
                    data-kanban-column
                    data-column-title="{{ $column['label'] }}"
                >

                    <x-kanban-column
                        :title="$column['label']"
                        :icon="$column['icon']"
                        :color="$column['color']"
                        :count="$column['items']->count()"
                    >

                        @if($column['items']->count() > 0)

                            @foreach($column['items'] as $ptp)

                                <div
                                    class="ptp-board-item"
                                    data-ptp-item
                                    data-client-name="{{ $ptp->client_name }}"
                                    data-amount="{{ $ptp->amount ?? 0 }}"
                                >

                                    <x-ptp-card
                                        :ptp="$ptp"
                                        :color="$column['color']"
                                    />

                                </div>

                            @endforeach

                        @else

                            <div class="py-8 text-center">

                                <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-xl bg-base/60">
                                    <i class="fa-solid fa-inbox text-muted"></i>
                                </div>

                                <p class="mt-3 text-xs font-semibold text-muted">
                                    لا توجد وعود
                                </p>

                                <p class="mt-1 text-[10px] text-dim">
                                    لا توجد بيانات في هذه المرحلة حاليًا
                                </p>

                            </div>

                        @endif

                    </x-kanban-column>

                </div>

            @endforeach

        </section>


    @else


        {{-- ========================================================
             CALENDAR VIEW
        ========================================================= --}}

        <section class="card">

            {{-- Calendar header --}}
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">

                <div class="flex items-center gap-2">

                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-info/10">
                        <i class="fa-regular fa-calendar text-info"></i>
                    </div>

                    <div>

                        <h2 class="text-base font-bold text-fg">
                            تقويم الوعود
                        </h2>

                        <p class="mt-1 text-[11px] text-muted">
                            {{ $calendar['title'] }}
                        </p>

                    </div>

                </div>


                <div class="flex items-center gap-2">

                    {{-- Previous --}}
                    <a
                        href="{{ route('banks.ptp.index', array_merge($query, ['view' => 'calendar', 'month' => $calendar['prev']])) }}"
                        class="btn btn-secondary btn-sm"
                        title="الشهر السابق"
                    >
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </a>


                    {{-- Current month --}}
                    <a
                        href="{{ route('banks.ptp.index', ['bank' => $bank->id, 'view' => 'calendar']) }}"
                        class="btn btn-secondary btn-sm"
                        title="العودة إلى الشهر الحالي"
                    >
                        <i class="fa-solid fa-calendar-day text-xs"></i>
                        اليوم
                    </a>


                    {{-- Next --}}
                    <a
                        href="{{ route('banks.ptp.index', array_merge($query, ['view' => 'calendar', 'month' => $calendar['next']])) }}"
                        class="btn btn-secondary btn-sm"
                        title="الشهر التالي"
                    >
                        <i class="fa-solid fa-chevron-left text-[10px]"></i>
                    </a>

                </div>

            </div>


            {{-- Calendar summary --}}
            <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-3">

                <div class="rounded-xl border border-line bg-surface p-3">

                    <div class="flex items-center justify-between">

                        <span class="text-xs text-muted">
                            وعود الشهر
                        </span>

                        <i class="fa-solid fa-handshake text-info"></i>

                    </div>

                    <div class="mt-2 text-lg font-bold text-fg">
                        {{ $calendarTotal }}
                    </div>

                </div>


                <div class="rounded-xl border border-line bg-surface p-3">

                    <div class="flex items-center justify-between">

                        <span class="text-xs text-muted">
                            أيام تحتوي على وعود
                        </span>

                        <i class="fa-solid fa-calendar-check text-brand"></i>

                    </div>

                    <div class="mt-2 text-lg font-bold text-fg">
                        {{ collect($calendar['days'])->filter(fn ($day) => $day['promises']->count() > 0)->count() }}
                    </div>

                </div>


                <div class="rounded-xl border border-line bg-surface p-3">

                    <div class="flex items-center justify-between">

                        <span class="text-xs text-muted">
                            اليوم
                        </span>

                        <i class="fa-solid fa-calendar-day text-warning"></i>

                    </div>

                    <div class="mt-2 text-sm font-bold text-fg">
                        {{ now()->format('Y-m-d') }}
                    </div>

                </div>

            </div>


            {{-- Calendar --}}
            <div class="overflow-x-auto">

                <div
                    class="grid min-w-[720px] grid-cols-7 gap-px overflow-hidden rounded-xl border border-line bg-line"
                    id="ptp-calendar"
                >

                    {{-- Weekdays --}}
                    @foreach($weekdays as $weekday)

                        <div class="bg-surface px-2 py-2 text-center text-[11px] font-bold text-dim">
                            {{ $weekday }}
                        </div>

                    @endforeach


                    {{-- Empty cells before day 1 --}}
                    @for($i = 0; $i < $calendar['offset']; $i++)

                        <div class="min-h-[110px] bg-base/60"></div>

                    @endfor


                    {{-- Days --}}
                    @foreach($calendar['days'] as $day)

                        <div
                            @class([
                                'min-h-[110px] bg-surface p-2 transition',
                                'bg-brand/[0.04] ring-1 ring-inset ring-brand/20' => $day['is_today'],
                                'hover:bg-white/[0.02]' => ! $day['is_today'],
                            ])
                            data-calendar-day="{{ $day['day'] }}"
                        >

                            {{-- Day header --}}
                            <div class="flex items-center justify-between">

                                <span
                                    @class([
                                        'text-xs font-semibold',
                                        'text-brand' => $day['is_today'],
                                        'text-muted' => ! $day['is_today'],
                                    ])
                                >
                                    {{ $day['day'] }}
                                </span>


                                @if($day['promises']->count() > 0)

                                    <span class="rounded-full bg-info/10 px-1.5 py-0.5 text-[9px] font-bold text-info">
                                        {{ $day['promises']->count() }}
                                    </span>

                                @endif

                            </div>


                            {{-- Promise chips --}}
                            @foreach($day['promises']->take(4) as $ptp)

                                <div
                                    class="mt-1.5 truncate rounded px-1.5 py-1 text-[10px] font-semibold transition hover:opacity-80 {{ $chipTones[$statuses[$ptp->status]['color']] ?? 'bg-info/15 text-info' }}"
                                    title="{{ $ptp->client_name }} - EGP {{ number_format($ptp->amount) }}"
                                >

                                    <div class="flex items-center gap-1">

                                        <i class="fa-solid fa-user text-[8px]"></i>

                                        <span class="truncate">
                                            {{ $ptp->client_name }}
                                        </span>

                                    </div>

                                    <div class="mt-0.5 text-[9px] opacity-80">
                                        EGP {{ number_format($ptp->amount) }}
                                    </div>

                                </div>

                            @endforeach


                            {{-- More promises --}}
                            @if($day['promises']->count() > 4)

                                <div class="mt-1.5 rounded bg-base/50 px-1.5 py-1 text-center text-[10px] font-semibold text-dim">

                                    +{{ $day['promises']->count() - 4 }}

                                    أخرى

                                </div>

                            @endif


                            {{-- Empty day --}}
                            @if($day['promises']->count() === 0)

                                <div class="mt-6 text-center text-[9px] text-dim/50">
                                    —
                                </div>

                            @endif

                        </div>

                    @endforeach

                </div>

            </div>

        </section>

    @endif


    {{-- ============================================================
         FLOATING BACK TO TOP
    ============================================================= --}}
    <button
        type="button"
        id="ptp-back-to-top"
        class="fixed bottom-6 left-6 z-40 hidden h-10 w-10 items-center justify-center rounded-xl border border-line bg-surface text-muted shadow-lg transition hover:text-fg"
        title="العودة إلى أعلى الصفحة"
        aria-label="العودة إلى أعلى الصفحة"
    >
        <i class="fa-solid fa-arrow-up text-xs"></i>
    </button>


@endsection


{{-- ================================================================
     FRONTEND ENHANCEMENTS
================================================================ --}}

@push('scripts')

<script>

    document.addEventListener('DOMContentLoaded', function () {

        /*
        |--------------------------------------------------------------------------
        | Search shortcut
        |--------------------------------------------------------------------------
        */

        document.addEventListener('keydown', function (event) {

            if (
                (event.ctrlKey || event.metaKey) &&
                event.key.toLowerCase() === 'k'
            ) {

                event.preventDefault();

                const search = document.getElementById('search');

                if (search) {

                    search.focus();
                    search.select();

                }

            }

        });


        /*
        |--------------------------------------------------------------------------
        | Back to top
        |--------------------------------------------------------------------------
        */

        const backToTop = document.getElementById('ptp-back-to-top');

        if (backToTop) {

            const toggleBackToTop = function () {

                if (window.scrollY > 400) {

                    backToTop.classList.remove('hidden');
                    backToTop.classList.add('flex');

                } else {

                    backToTop.classList.add('hidden');
                    backToTop.classList.remove('flex');

                }

            };

            window.addEventListener(
                'scroll',
                toggleBackToTop,
                { passive: true }
            );

            toggleBackToTop();


            backToTop.addEventListener('click', function () {

                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });

            });

        }


        /*
        |--------------------------------------------------------------------------
        | Search input visual state
        |--------------------------------------------------------------------------
        */

        const searchInput = document.getElementById('search');

        if (searchInput) {

            searchInput.addEventListener('input', function () {

                if (this.value.trim() !== '') {

                    this.classList.add('ring-1');
                    this.classList.add('ring-info/30');

                } else {

                    this.classList.remove('ring-1');
                    this.classList.remove('ring-info/30');

                }

            });

        }


        /*
        |--------------------------------------------------------------------------
        | Calendar today highlight
        |--------------------------------------------------------------------------
        */

        const todayCells = document.querySelectorAll(
            '[data-calendar-day]'
        );

        todayCells.forEach(function (cell) {

            cell.addEventListener('mouseenter', function () {

                this.classList.add('ring-1');
                this.classList.add('ring-info/20');

            });

            cell.addEventListener('mouseleave', function () {

                if (!this.classList.contains('bg-brand/[0.04]')) {

                    this.classList.remove('ring-1');
                    this.classList.remove('ring-info/20');

                }

            });

        });

    });


    /*
    |--------------------------------------------------------------------------
    | Export visible PTP data
    |--------------------------------------------------------------------------
    | Frontend only.
    | No backend route is required.
    */

    function exportPTPData() {

        const rows = [];

        rows.push([
            'العميل',
            'المبلغ',
            'الحالة'
        ]);


        /*
        |--------------------------------------------------------------------------
        | Board
        |--------------------------------------------------------------------------
        */

        const boardItems = document.querySelectorAll(
            '[data-ptp-item]'
        );


        boardItems.forEach(function (item) {

            const clientName =
                item.dataset.clientName || '';

            const amount =
                item.dataset.amount || '';

            const column =
                item.closest('[data-kanban-column]');

            const status =
                column
                    ? column.dataset.columnTitle || ''
                    : '';


            rows.push([
                clientName,
                amount,
                status
            ]);

        });


        /*
        |--------------------------------------------------------------------------
        | Calendar fallback
        |--------------------------------------------------------------------------
        */

        if (boardItems.length === 0) {

            const calendarItems =
                document.querySelectorAll(
                    '[data-calendar-day]'
                );


            calendarItems.forEach(function (day) {

                const dayNumber =
                    day.dataset.calendarDay || '';

                const promises =
                    day.querySelectorAll(
                        '.truncate'
                    );


                promises.forEach(function (promise) {

                    rows.push([
                        promise.innerText
                            .replace(/\s+/g, ' ')
                            .trim(),

                        '',

                        'اليوم ' + dayNumber
                    ]);

                });

            });

        }


        /*
        |--------------------------------------------------------------------------
        | Nothing to export
        |--------------------------------------------------------------------------
        */

        if (rows.length <= 1) {

            if (typeof showFrontendMessage === 'function') {

                showFrontendMessage(
                    'لا توجد بيانات متاحة للتصدير.'
                );

            } else {

                alert(
                    'لا توجد بيانات متاحة للتصدير.'
                );

            }

            return;

        }


        /*
        |--------------------------------------------------------------------------
        | CSV
        |--------------------------------------------------------------------------
        */

        const csv = rows
            .map(function (row) {

                return row
                    .map(function (value) {

                        const text =
                            String(value ?? '')
                                .replace(/"/g, '""');

                        return '"' + text + '"';

                    })
                    .join(',');

            })
            .join('\n');


        /*
        |--------------------------------------------------------------------------
        | UTF-8 BOM for Arabic Excel support
        |--------------------------------------------------------------------------
        */

        const blob = new Blob(
            [
                '\uFEFF',
                csv
            ],
            {
                type: 'text/csv;charset=utf-8;'
            }
        );


        const url =
            URL.createObjectURL(blob);

        const link =
            document.createElement('a');

        link.href = url;

        link.download =
            'ptp-' +
            new Date()
                .toISOString()
                .slice(0, 10) +
            '.csv';

        document.body.appendChild(link);

        link.click();

        link.remove();

        URL.revokeObjectURL(url);

    }

</script>

@endpush