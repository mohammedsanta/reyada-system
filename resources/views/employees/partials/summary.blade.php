{{-- resources/views/employees/partials/summary.blade.php --}}

@php
    $displayedCount = $summary['displayedCount'] ?? 0;
    $grandTotalCount = $summary['grandTotalCount'] ?? 0;

    $totalAmount = $summary['totalAmount'] ?? 0;
    $totalCases = $summary['totalCases'] ?? 0;
    $totalPtp = $summary['totalPtp'] ?? 0;

    $averageEfficiency = $summary['averageEfficiency'] ?? 0;

    $highestEfficiencyEmployee =
        $summary['highestEfficiencyEmployee'] ?? null;

    $highestAmountEmployee =
        $summary['highestAmountEmployee'] ?? null;

    $selectedInstitution = $filterState['institution'] ?? '';
    $selectedSearch = $filterState['search'] ?? '';
    $selectedMonth = $filterState['month'] ?? '';
    $selectedYear = $filterState['year'] ?? '';

    $monthName = $filterState['monthName'] ?? '';
    $hasFilters = $filterState['hasFilters'] ?? false;
@endphp

{{-- Current context --}}
<div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

    <div class="flex flex-wrap items-center gap-2">

        <span class="inline-flex items-center gap-2 rounded-full border border-border bg-card px-3 py-1.5 text-xs text-muted">
            <span class="h-2 w-2 rounded-full bg-brand"></span>
            الفترة:
            <span class="font-semibold text-fg">
                {{ $monthName ?: 'الفترة الحالية' }}
                {{ $selectedYear }}
            </span>
        </span>

        @if($selectedInstitution !== '')
            <span class="inline-flex items-center gap-2 rounded-full border border-info/20 bg-info/5 px-3 py-1.5 text-xs text-info">
                <i class="fa-solid fa-building-columns text-[10px]"></i>
                {{ $selectedInstitution }}
            </span>
        @endif

        @if($selectedSearch !== '')
            <span class="inline-flex max-w-[220px] items-center gap-2 rounded-full border border-accent/20 bg-accent/5 px-3 py-1.5 text-xs text-accent">
                <i class="fa-solid fa-magnifying-glass text-[10px]"></i>
                <span class="truncate">{{ $selectedSearch }}</span>
            </span>
        @endif

    </div>

    <div class="flex items-center gap-2 text-[11px] text-muted">
        <span>
            <i class="fa-regular fa-clock ml-1"></i>
            عرض مباشر
        </span>
        <span class="hidden text-border sm:inline">•</span>
        <span>{{ number_format($displayedCount) }} موظف معروض</span>
    </div>

</div>

{{-- Controller-provided metric cards --}}
<section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
    @foreach(($stats ?? []) as $stat)
        <x-metric-card
            :label="$stat['label']"
            :value="$stat['value']"
            :unit="$stat['unit']"
            :icon="$stat['icon']"
            :color="$stat['color']"
        />
    @endforeach
</section>

{{-- Performance summary --}}
<section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

    <div class="card transition hover:border-info/30">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-xs text-muted">الموظفون</p>
                <p class="mt-2 text-xl font-bold text-fg">
                    {{ number_format($displayedCount) }}
                </p>
                <p class="mt-1 text-[11px] text-muted">
                    من أصل {{ number_format($grandTotalCount) }}
                </p>
            </div>
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-info/10 text-info">
                <i class="fa-solid fa-users"></i>
            </span>
        </div>
    </div>

    <div class="card transition hover:border-brand/30">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-xs text-muted">إجمالي المبالغ</p>
                <p class="mt-2 text-xl font-bold text-brand">
                    {{ number_format($totalAmount) }}
                </p>
                <p class="mt-1 text-[11px] text-muted">ج.م</p>
            </div>
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand/10 text-brand">
                <i class="fa-solid fa-money-bill-wave"></i>
            </span>
        </div>
    </div>

    <div class="card transition hover:border-warning/30">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-xs text-muted">إجمالي الحالات</p>
                <p class="mt-2 text-xl font-bold text-fg">
                    {{ number_format($totalCases) }}
                </p>
                <p class="mt-1 text-[11px] text-muted">
                    الحالات المرتبطة بالعرض الحالي
                </p>
            </div>
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-warning/10 text-warning">
                <i class="fa-solid fa-folder-open"></i>
            </span>
        </div>
    </div>

    <div class="card transition hover:border-accent/30">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-xs text-muted">إجمالي PTP</p>
                <p class="mt-2 text-xl font-bold text-accent">
                    {{ number_format($totalPtp) }}
                </p>
                <p class="mt-1 text-[11px] text-muted">وعود السداد</p>
            </div>
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-accent/10 text-accent">
                <i class="fa-solid fa-handshake"></i>
            </span>
        </div>
    </div>

</section>

{{-- Performance insights --}}
<section class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-3">

    <div class="card">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-xs text-muted">متوسط الكفاءة</p>
                <p class="mt-2 text-2xl font-bold text-fg">
                    {{ number_format($averageEfficiency, 1) }}%
                </p>
            </div>
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-info/10 text-info">
                <i class="fa-solid fa-gauge-high"></i>
            </span>
        </div>

        <div class="mt-4">
            <div class="mb-1 flex justify-between text-[10px] text-muted">
                <span>متوسط العرض الحالي</span>
                <span>{{ number_format($averageEfficiency, 1) }}%</span>
            </div>
            <div class="progress">
                <div
                    class="h-full rounded-full bg-info"
                    style="width: {{ min(max($averageEfficiency, 0), 100) }}%"
                ></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-xs text-muted">أعلى كفاءة في العرض</p>

@if($highestEfficiencyEmployee)
    <p class="mt-2 truncate text-sm font-bold text-fg">
        {{ $highestEfficiencyEmployee['name'] ?? '-' }}
    </p>

    <p class="mt-1 text-xs text-brand">
        {{ number_format((float) ($highestEfficiencyEmployee['efficiency'] ?? 0), 1) }}%
    </p>
@else
    <p class="mt-2 text-sm font-bold text-muted">
        لا توجد بيانات
    </p>
@endif

            </div>

            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-warning/10 text-warning">
                <i class="fa-solid fa-trophy"></i>
            </span>
        </div>
    </div>

    <div class="card">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-xs text-muted">أعلى مبلغ في العرض</p>

@if($highestAmountEmployee)
    <p class="mt-2 truncate text-sm font-bold text-fg">
        {{ $highestAmountEmployee['name'] ?? '-' }}
    </p>

    <p class="mt-1 text-xs text-brand">
        ج.م {{ number_format((float) ($highestAmountEmployee['amount'] ?? 0)) }}
    </p>
@else
                    <p class="mt-2 text-sm font-bold text-muted">لا توجد بيانات</p>
                @endif
            </div>

            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand/10 text-brand">
                <i class="fa-solid fa-coins"></i>
            </span>
        </div>
    </div>

</section>