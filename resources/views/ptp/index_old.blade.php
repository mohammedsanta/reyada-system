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

    $weekdays = ['السبت', 'الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة'];
@endphp

@section('content')

    {{-- HEADER --}}
    <header class="card mb-6 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('banks.show', $bank->id) }}" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-arrow-right text-xs"></i> رجوع
            </a>

            <span class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-xl bg-white text-black">
                @if($bank->logo ?? null)
                    <img src="{{ $bank->logo }}" alt="{{ $bank->name }}" class="h-full w-full object-contain p-1">
                @else
                    <i class="fa-solid fa-building-columns text-lg"></i>
                @endif
            </span>

            <div>
                <h1 class="text-xl font-bold text-fg">
                    مركز متابعة الوعود <span class="text-muted">(PTP Hub)</span>
                </h1>
                <p class="mt-1 flex items-center gap-2 text-xs text-muted">
                    {{ $bank->name }}
                    <span class="badge badge-warning">{{ $active }} وعود نشطة</span>
                </p>
            </div>
        </div>

        {{-- Add promise + Board / Calendar switch --}}
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('banks.ptp.create', $bank->id) }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus text-xs"></i> إضافة وعد</a>
        <div class="segmented">
            <a href="{{ route('banks.ptp.index', array_merge($query, ['view' => 'board'])) }}"
               class="segmented-item {{ $filters['view'] === 'board' ? 'segmented-item-active' : '' }}">
                <i class="fa-solid fa-table-columns"></i> لوحة العمل
            </a>
            <a href="{{ route('banks.ptp.index', array_merge($query, ['view' => 'calendar'])) }}"
               class="segmented-item {{ $filters['view'] === 'calendar' ? 'segmented-item-active' : '' }}">
                <i class="fa-regular fa-calendar"></i> التقويم
            </a>
        </div>
        </div>
    </header>


    {{-- STATISTICS --}}
    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($stats as $stat)
            <x-metric-card :label="$stat['label']" :value="$stat['value']" :icon="$stat['icon']" :color="$stat['color']" />
        @endforeach
    </section>


    {{-- TOOLBAR (GET form: the URL keeps the filters) --}}
    <form method="GET" action="{{ route('banks.ptp.index', $bank->id) }}" class="card filter-bar">
        <input type="hidden" name="view" value="{{ $filters['view'] }}">
        @if($filters['month'] !== '')
            <input type="hidden" name="month" value="{{ $filters['month'] }}">
        @endif

        <a href="{{ request()->fullUrl() }}" class="btn btn-secondary" title="تحديث">
            <i class="fa-solid fa-rotate"></i>
        </a>

        <div class="min-w-[240px] flex-1">
            <label for="search" class="sr-only">بحث</label>
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-dim"></i>
                <input id="search" name="search" type="search" value="{{ $filters['search'] }}"
                       placeholder="بحث برقم/اسم العميل..." class="form-input pl-9">
            </div>
        </div>

        <div class="w-44">
            <label for="scope" class="sr-only">تصفية الوعود</label>
            <select id="scope" name="scope" class="form-input" onchange="this.form.submit()">
                @foreach($scopes as $value => $label)
                    <option value="{{ $value }}" @selected($filters['scope'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass text-xs"></i> بحث</button>
    </form>


    @if($filters['view'] === 'board')

        {{-- KANBAN BOARD --}}
        <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
            @foreach($columns as $column)
                <x-kanban-column
                    :title="$column['label']"
                    :icon="$column['icon']"
                    :color="$column['color']"
                    :count="$column['items']->count()"
                >
                    @foreach($column['items'] as $ptp)
                        <x-ptp-card :ptp="$ptp" :color="$column['color']" />
                    @endforeach
                </x-kanban-column>
            @endforeach
        </section>

    @else

        {{-- CALENDAR --}}
        <section class="card">
            <div class="mb-4 flex items-center justify-between">
                <a href="{{ route('banks.ptp.index', array_merge($query, ['view' => 'calendar', 'month' => $calendar['prev']])) }}"
                   class="btn btn-secondary btn-sm" title="الشهر السابق">
                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                </a>

                <h2 class="text-base font-bold text-fg">{{ $calendar['title'] }}</h2>

                <a href="{{ route('banks.ptp.index', array_merge($query, ['view' => 'calendar', 'month' => $calendar['next']])) }}"
                   class="btn btn-secondary btn-sm" title="الشهر التالي">
                    <i class="fa-solid fa-chevron-left text-[10px]"></i>
                </a>
            </div>

            <div class="overflow-x-auto">
                <div class="grid min-w-[720px] grid-cols-7 gap-px overflow-hidden rounded-xl border border-line bg-line">

                    @foreach($weekdays as $weekday)
                        <div class="bg-surface px-2 py-2 text-center text-[11px] font-bold text-dim">{{ $weekday }}</div>
                    @endforeach

                    {{-- empty cells before day 1 --}}
                    @for($i = 0; $i < $calendar['offset']; $i++)
                        <div class="min-h-[96px] bg-base/60"></div>
                    @endfor

                    @foreach($calendar['days'] as $day)
                        <div @class(['min-h-[96px] bg-surface p-2', 'bg-brand/[0.04]' => $day['is_today']])>
                            <span @class(['text-xs font-semibold', 'text-brand' => $day['is_today'], 'text-muted' => ! $day['is_today']])>
                                {{ $day['day'] }}
                            </span>

                            @foreach($day['promises']->take(3) as $ptp)
                                <div class="mt-1 truncate rounded px-1.5 py-0.5 text-[10px] font-semibold {{ $chipTones[$statuses[$ptp->status]['color']] }}"
                                     title="{{ $ptp->client_name }} - EGP {{ number_format($ptp->amount) }}">
                                    {{ $ptp->client_name }}
                                </div>
                            @endforeach

                            @if($day['promises']->count() > 3)
                                <div class="mt-1 text-[10px] text-dim">+{{ $day['promises']->count() - 3 }} أخرى</div>
                            @endif
                        </div>
                    @endforeach

                </div>
            </div>
        </section>

    @endif

@endsection