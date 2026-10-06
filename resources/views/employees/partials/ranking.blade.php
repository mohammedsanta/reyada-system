{{-- resources/views/employees/partials/ranking.blade.php --}}

@php
    $displayedCount = $summary['displayedCount'];
    $grandTotalCount = $summary['grandTotalCount'];

    $averageEfficiency = $summary['averageEfficiency'];
    $totalCases = $summary['totalCases'];
    $totalAmount = $summary['totalAmount'];
    $totalPtp = $summary['totalPtp'];

    $hasFilters = $filterState['hasFilters'];

    $toneText = [
        'excellent' => 'text-brand',
        'good'      => 'text-info',
        'average'   => 'text-warning',
        'low'       => 'text-danger',
        'inactive'  => 'text-muted',
    ];

    $toneBar = [
        'excellent' => 'bg-brand',
        'good'      => 'bg-info',
        'average'   => 'bg-warning',
        'low'       => 'bg-danger',
        'inactive'  => 'bg-muted',
    ];

    $ringStyles = [
        'brand' => 'border-brand/40 bg-brand/10 text-brand',
        'warning' => 'border-warning/40 bg-warning/10 text-warning',
        'danger' => 'border-danger/40 bg-danger/10 text-danger',
        'info' => 'border-info/40 bg-info/10 text-info',
        'orange' => 'border-orange-400/40 bg-orange-400/10 text-orange-400',
    ];

    $rankStyles = [
        1 => ['fa-trophy', 'bg-warning/15 text-warning'],
        2 => ['fa-medal', 'bg-slate-400/15 text-slate-300'],
        3 => ['fa-medal', 'bg-orange-400/15 text-orange-400'],
    ];
@endphp


<section>
    <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="flex items-center gap-2 text-lg font-bold text-fg">
                    <i class="fa-solid fa-chart-simple text-info"></i>
                    التصنيف المباشر
                </h2>

                <span class="badge badge-outline-info">
                    {{ $displayedCount }}/{{ $grandTotalCount }}
                </span>
            </div>

            <p class="mt-1 text-xs text-muted">
                ترتيب الموظفين بناءً على مقاييس الكفاءة
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="text-[11px] text-muted">متوسط الكفاءة</span>
            <span class="rounded-lg border border-border bg-white/5 px-2.5 py-1 text-xs font-bold text-fg">
                {{ number_format($averageEfficiency, 1) }}%
            </span>
        </div>
    </div>

    <div class="space-y-3">
        @forelse($employees as $employee)

@php
    $employeeEfficiency = min(
        max((float) ($employee['efficiency'] ?? 0), 0),
        100
    );

    $employeeAmount = (float) ($employee['amount'] ?? 0);

    $amountShare = $totalAmount != 0
        ? ($employeeAmount / $totalAmount) * 100
        : 0;

    $employeeTone = $toneText[$employee['tone'] ?? '']
        ?? 'text-info';

    $employeeBar = $toneBar[$employee['tone'] ?? '']
        ?? 'bg-info';

    $employeeRing = $ringStyles[$employee['color'] ?? '']
        ?? $ringStyles['info'];
@endphp


            <div class="card group flex flex-wrap items-center gap-4 transition duration-200 hover:border-info/30">

                {{-- Rank --}}
@if(isset($rankStyles[$employee['rank'] ?? 0]))
    <span
        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $rankStyles[$employee['rank']][1] }}"
        title="المركز {{ $employee['rank'] }}"
    >
        <i class="fa-solid {{ $rankStyles[$employee['rank']][0] }}"></i>
    </span>
@else
    <span
        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/[0.06] text-sm font-bold text-muted"
        title="المركز {{ $employee['rank'] ?? '-' }}"
    >
        {{ $employee['rank'] ?? '-' }}
    </span>
@endif


                {{-- Avatar --}}
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border-2 text-sm font-bold {{ $employeeRing }}">
                    {{ $employee['initials'] ?? '—' }}
                </span>

                {{-- Employee info --}}
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="text-sm font-bold text-fg">
                            {{ $employee['name'] ?? '-' }}
                        </h3>

                        <span class="badge badge-outline-info">
                            {{ $employee['code'] ?? '-' }}
                        </span>
                    </div>

                    <p class="mt-1.5 text-xs text-muted">
                        <i class="fa-solid fa-building-columns ml-1 text-info"></i>
                        {{ $employee['bank'] ?? 'غير محدد' }}
                    </p>

                    <p class="mt-1.5 flex flex-wrap gap-4 text-[11px] text-dim">
                        <span>
                            <i class="fa-solid fa-handshake ml-1 text-warning"></i>
                            PTP
                            <strong class="text-fg">
                                {{ number_format($employee['ptp'] ?? 0) }}
                            </strong>
                        </span>

                        <span>
                            <i class="fa-solid fa-folder-open ml-1 text-info"></i>
                            حالات
                            <strong class="text-fg">
                                {{ number_format($employee['cases'] ?? 0) }}
                            </strong>
                        </span>
                    </p>
                </div>

                <div class="flex-1"></div>

                {{-- Efficiency --}}
                <div class="w-full sm:w-44">
                    <div class="mb-1.5 flex items-center justify-between text-[11px]">
                        <span class="text-muted">الكفاءة</span>
                        <span class="font-bold {{ $employeeTone }}">
                            {{ number_format($employeeEfficiency, 1) }}%
                        </span>
                    </div>

                    <div class="progress" title="{{ number_format($employeeEfficiency, 1) }}%">
                        <div
                            class="h-full rounded-full {{ $employeeBar }} transition-all duration-500"
                            style="width: {{ $employeeEfficiency }}%"
                        ></div>
                    </div>

                    <div class="mt-1 flex justify-between text-[9px] text-dim">
                        <span>الأداء</span>
                        <span>
                            {{ $employeeEfficiency >= 80
                                ? 'مستوى مرتفع'
                                : ($employeeEfficiency >= 50
                                    ? 'مستوى متوسط'
                                    : 'يحتاج متابعة') }}
                        </span>
                    </div>
                </div>

                {{-- Amount --}}
                <div class="w-full sm:w-32">
                    <div class="text-[11px] text-muted">المبلغ</div>

                    <div class="mt-0.5 text-sm font-bold text-brand">
                        <span class="text-xs font-normal text-muted">ج.م</span>
                        {{ number_format($employeeAmount) }}
                    </div>

                    @if($totalAmount != 0)
                        <div class="mt-1 text-[9px] text-dim">
                            {{ number_format($amountShare, 1) }}% من إجمالي العرض
                        </div>
                    @endif
                </div>

                {{-- Details --}}
                <a
                    href="{{ route('employees.show', $employee['code']) }}"
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-dim transition hover:bg-white/5 hover:text-fg"
                    title="عرض تفاصيل الموظف"
                    aria-label="عرض تفاصيل الموظف"
                >
                    <i class="fa-solid fa-chevron-left text-xs"></i>
                </a>

            </div>

        @empty

            <div class="card py-14 text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-info/10 text-info">
                    <i class="fa-solid fa-users-slash text-xl"></i>
                </span>

                <h3 class="mt-4 font-bold text-fg">
                    لا يوجد موظفون مطابقون للفلاتر
                </h3>

                <p class="mt-1 text-xs text-muted">
                    جرّب إزالة أحد الفلاتر أو البحث باستخدام كلمة مختلفة.
                </p>

                <div class="mt-5 flex flex-wrap justify-center gap-2">
                    @if($hasFilters)
                        <a href="{{ route('employees.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fa-solid fa-filter-circle-xmark"></i>
                            إزالة الفلاتر
                        </a>
                    @endif

                    <button type="button" id="focusEmployeeSearch" class="btn btn-secondary btn-sm">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        بحث جديد
                    </button>
                </div>
            </div>

        @endforelse
    </div>
</section>

{{-- Footer summary --}}
@if($displayedCount > 0)
    <div class="mt-6 flex flex-col gap-3 border-t border-border pt-5 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap items-center gap-3 text-[11px] text-muted">
            <span>
                <i class="fa-solid fa-users ml-1 text-info"></i>
                {{ number_format($displayedCount) }} موظف
            </span>

            <span class="hidden text-border sm:inline">•</span>

            <span>
                <i class="fa-solid fa-folder-open ml-1 text-warning"></i>
                {{ number_format($totalCases) }} حالة
            </span>

            <span class="hidden text-border sm:inline">•</span>

            <span>
                <i class="fa-solid fa-money-bill-wave ml-1 text-brand"></i>
                ج.م {{ number_format($totalAmount) }}
            </span>

            <span class="hidden text-border sm:inline">•</span>

            <span>
                <i class="fa-solid fa-handshake ml-1 text-accent"></i>
                {{ number_format($totalPtp) }} PTP
            </span>
        </div>

        <button
            type="button"
            id="scrollToTop"
            class="text-[11px] text-muted transition hover:text-brand"
        >
            <i class="fa-solid fa-arrow-up ml-1"></i>
            العودة للأعلى
        </button>
    </div>
@endif