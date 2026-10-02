{{-- resources/views/employees/index.blade.php --}}
@extends('layouts.app')

@section('title', 'أداء الموظفين')

@php
    // Full class names are written out so Tailwind can detect them.
    $rankStyles = [
        1 => ['fa-trophy', 'bg-warning/15 text-warning'],
        2 => ['fa-medal',  'bg-white/[0.06] text-muted'],
        3 => ['fa-award',  'bg-orange/15 text-orange'],
    ];

    $ringStyles = [
        'warning' => 'border-warning/60 bg-warning/10 text-warning',
        'brand'   => 'border-brand/60 bg-brand/10 text-brand',
        'orange'  => 'border-orange/60 bg-orange/10 text-orange',
        'info'    => 'border-info/60 bg-info/10 text-info',
        'accent'  => 'border-accent/60 bg-accent/10 text-accent',
        'cyan'    => 'border-cyan/60 bg-cyan/10 text-cyan',
    ];

    $toneText = ['brand' => 'text-brand', 'warning' => 'text-warning', 'danger' => 'text-danger'];
    $toneBar  = ['brand' => 'bg-brand',   'warning' => 'bg-warning',   'danger' => 'bg-danger'];
@endphp

@section('content')

    {{-- HEADER --}}
    <x-page-header title="أداء الموظفين" subtitle="مركز القياس التحليلي الذكي" icon="fa-rocket">
        <x-slot:actions>
            <a href="{{ request()->fullUrl() }}" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-rotate"></i> تحديث فوري
            </a>
        </x-slot:actions>
    </x-page-header>


    {{-- STATISTICS --}}
    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
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


    {{-- ADVANCED FILTERS (GET form: the URL keeps the filters) --}}
    <form method="GET" action="{{ route('employees.index') }}" class="card mb-8">

        <div class="mb-5 flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-info/15 text-info">
                <i class="fa-solid fa-filter"></i>
            </span>
            <div>
                <h2 class="text-sm font-bold text-fg">فلاتر الأداء المتقدمة</h2>
                <p class="mt-0.5 text-[11px] text-muted">تحليل الأداء الشهري ومقارنة الاتجاهات</p>
            </div>
        </div>

        <div class="flex flex-wrap items-end gap-4">

            {{-- Institution pills --}}
            <div>
                <span class="form-label"><i class="fa-solid fa-building-columns ml-1 text-info"></i> المؤسسة</span>
                <div class="flex flex-wrap gap-2">
                    <label>
                        <input type="radio" name="institution" value="" class="pill-radio sr-only"
                               @checked($filters['institution'] === '') onchange="this.form.submit()">
                        <span class="pill">الكل</span>
                    </label>

                    @foreach($institutions as $institution)
                        <label>
                            <input type="radio" name="institution" value="{{ $institution }}" class="pill-radio sr-only"
                                   @checked($filters['institution'] === $institution) onchange="this.form.submit()">
                            <span class="pill">{{ $institution }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Search --}}
            <div class="min-w-[220px] flex-1">
                <label for="search" class="sr-only">بحث</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-dim"></i>
                    <input id="search" name="search" type="search" value="{{ $filters['search'] }}"
                           placeholder="ابحث بالاسم أو كود الموظف..." class="form-input pl-9">
                </div>
            </div>

            {{-- Month --}}
            <div class="w-36">
                <label for="month" class="form-label">الشهر</label>
                <select id="month" name="month" class="form-input">
                    @foreach($months as $number => $name)
                        <option value="{{ $number }}" @selected($filters['month'] === $number)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Year --}}
            <div class="w-28">
                <label for="year" class="form-label">السنة</label>
                <select id="year" name="year" class="form-input">
                    @foreach($years as $year)
                        <option value="{{ $year }}" @selected($filters['year'] === $year)>{{ $year }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-magnifying-glass text-xs"></i> تطبيق
            </button>
        </div>
    </form>


    {{-- LIVE RANKING --}}
    <section>
        <div class="mb-5 flex items-center gap-3">
            <h2 class="flex items-center gap-2 text-lg font-bold text-fg">
                <i class="fa-solid fa-chart-simple text-info"></i>
                التصنيف المباشر
            </h2>
            <span class="badge badge-outline-info">{{ $employees->count() }}/{{ $totalCount }}</span>
        </div>
        <p class="-mt-3 mb-5 text-xs text-muted">ترتيب الموظفين بناءً على مقاييس الكفاءة</p>

        <div class="space-y-3">
            @forelse($employees as $employee)
                <div class="card flex flex-wrap items-center gap-4">

                    {{-- Rank --}}
                    @if(isset($rankStyles[$employee->rank]))
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $rankStyles[$employee->rank][1] }}">
                            <i class="fa-solid {{ $rankStyles[$employee->rank][0] }}"></i>
                        </span>
                    @else
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/[0.06] text-sm font-bold text-muted">
                            {{ $employee->rank }}
                        </span>
                    @endif

                    {{-- Avatar --}}
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border-2 text-sm font-bold {{ $ringStyles[$employee->color] ?? $ringStyles['info'] }}">
                        {{ $employee->initials }}
                    </span>

                    {{-- Info --}}
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-fg">{{ $employee->name }}</h3>
                            <span class="badge badge-outline-info">{{ $employee->code }}</span>
                        </div>

                        <p class="mt-1.5 text-xs text-muted">
                            <i class="fa-solid fa-building-columns ml-1 text-info"></i>{{ $employee->bank }}
                        </p>

                        <p class="mt-1.5 flex gap-4 text-[11px] text-dim">
                            <span><i class="fa-solid fa-handshake ml-1 text-warning"></i>PTP {{ $employee->ptp }}</span>
                            <span><i class="fa-solid fa-folder-open ml-1 text-info"></i>حالات {{ $employee->cases }}</span>
                        </p>
                    </div>

                    <div class="flex-1"></div>

                    {{-- Efficiency --}}
                    <div class="w-44">
                        <div class="mb-1.5 flex items-center justify-between text-[11px]">
                            <span class="text-muted">الكفاءة</span>
                            <span class="font-bold {{ $toneText[$employee->tone] }}">{{ $employee->efficiency }}%</span>
                        </div>
                        <div class="progress">
                            <div class="h-full rounded-full {{ $toneBar[$employee->tone] }}" style="width: {{ $employee->efficiency }}%"></div>
                        </div>
                    </div>

                    {{-- Amount --}}
                    <div class="w-28">
                        <div class="text-[11px] text-muted">المبلغ</div>
                        <div class="mt-0.5 text-sm font-bold text-brand">
                            <span class="text-xs font-normal text-muted">ج.م</span>
                            {{ number_format($employee->amount) }}
                        </div>
                    </div>

                    {{-- Details --}}
                    <a href="{{ route('employees.show', $employee->code) }}" class="text-dim transition hover:text-fg" title="التفاصيل">
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>
                </div>
            @empty
                <div class="card py-10 text-center text-sm text-dim">لا يوجد موظفون مطابقون للفلاتر</div>
            @endforelse
        </div>
    </section>

@endsection