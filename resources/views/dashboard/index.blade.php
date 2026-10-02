{{-- resources/views/dashboard/index.blade.php (REPLACES the old dashboard view) --}}
@extends('layouts.app')

@section('title', 'لوحة التحكم - Collex')

@php
    // Full class names so Tailwind can detect them
    $tones = [
        'brand' => 'bg-brand/15 text-brand', 'info' => 'bg-info/15 text-info', 'accent' => 'bg-accent/15 text-accent',
        'warning' => 'bg-warning/15 text-warning', 'danger' => 'bg-danger/15 text-danger', 'cyan' => 'bg-cyan/15 text-cyan',
        'orange' => 'bg-orange/15 text-orange', 'pink' => 'bg-pink/15 text-pink',
    ];

    $alertTones = [
        'danger'  => ['border-danger/20 bg-danger/[0.05]',   'text-danger'],
        'warning' => ['border-warning/20 bg-warning/[0.05]', 'text-warning'],
    ];

    $toneText = ['brand' => 'text-brand', 'warning' => 'text-warning', 'danger' => 'text-danger'];
    $toneBar  = ['brand' => 'bg-brand',   'warning' => 'bg-warning',   'danger' => 'bg-danger'];
@endphp

@section('content')

    {{-- =====================================================
        HEADER: greeting + period switch
    ====================================================== --}}
    <header class="mb-6 flex flex-wrap items-center justify-between gap-6">
        <div>
            <h1 class="flex items-center gap-3 text-2xl font-bold text-fg">
                <i class="fa-solid fa-gauge-high text-brand"></i>
                {{ $greeting }}، {{ $userName }}
            </h1>
            <p class="mt-2 text-sm text-muted">{{ $todayLabel }} · نظرة سريعة على أداء التحصيل</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="segmented">
                @foreach($periods as $key => $label)
                    <a href="{{ route('dashboard', ['period' => $key]) }}" class="segmented-item {{ $period === $key ? 'segmented-item-active' : '' }}">{{ $label }}</a>
                @endforeach
            </div>

            <a href="{{ request()->fullUrl() }}" class="btn btn-secondary btn-sm" title="تحديث">
                <i class="fa-solid fa-rotate"></i> <span class="text-[11px] text-muted">{{ $updatedAt }}</span>
            </a>
        </div>
    </header>


    {{-- =====================================================
        QUICK ACTIONS
    ====================================================== --}}
    <section class="mb-6 grid grid-cols-2 gap-3 xl:grid-cols-4">
        @foreach($quick as [$title, $hint, $icon, $color, $url])
            <a href="{{ $url }}" class="card flex items-center gap-3 py-4 transition hover:-translate-y-0.5 hover:bg-white/[0.04]">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $tones[$color] }}"><i class="fa-solid {{ $icon }}"></i></span>
                <span>
                    <span class="block text-sm font-bold text-fg">{{ $title }}</span>
                    <span class="block text-[11px] text-dim">{{ $hint }}</span>
                </span>
            </a>
        @endforeach
    </section>


    {{-- =====================================================
        MONEY KPIs
    ====================================================== --}}
    <section class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($kpis as $kpi)
            <x-kpi-card
                :label="$kpi['label']" :value="$kpi['value']" :unit="$kpi['unit']"
                :icon="$kpi['icon']" :color="$kpi['color']"
                :delta="$kpi['delta']" :good-up="$kpi['good_up']"
                :spark="$kpi['spark']" :hint="$kpi['hint']" :href="$kpi['href']"
            />
        @endforeach
    </section>


    {{-- =====================================================
        NEEDS ATTENTION (each card opens the page that solves it)
    ====================================================== --}}
    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($attention as $item)
            <x-kpi-card :label="$item['label']" :value="$item['value']" :icon="$item['icon']" :color="$item['color']" :hint="$item['hint']" :href="$item['href']" />
        @endforeach
    </section>


    {{-- =====================================================
        CHARTS
    ====================================================== --}}
    <section class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-3">
        <x-panel title="التحصيل مقابل المستهدف (ألف EGP)" class="xl:col-span-2">
            <div class="relative h-[270px]"><canvas id="trendChart"></canvas></div>
        </x-panel>

        <x-panel title="توزيع المحفظة (هذا الشهر)">
            <div class="relative flex h-[270px] justify-center"><canvas id="splitChart"></canvas></div>
        </x-panel>
    </section>


    {{-- =====================================================
        BANKS + BUCKETS
    ====================================================== --}}
    <section class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-3">

        <x-panel title="أداء البنوك (هذا الشهر)" icon="fa-building-columns" class="xl:col-span-2">
            <div class="table-wrap">
                <table class="data-table whitespace-nowrap">
                    <thead>
                        <tr><th>البنك</th><th>الحالات</th><th>المحفظة</th><th>المحصل</th><th class="w-44">نسبة التحصيل</th></tr>
                    </thead>

                    <tbody>
                        @foreach($banks as $bank)
                            @php $barClass = $bank->rate >= 40 ? 'bg-brand' : ($bank->rate >= 20 ? 'bg-warning' : 'bg-danger'); @endphp
                            <tr>
                                <td>
                                    <a href="{{ $bank->url }}" class="group inline-flex items-center gap-3">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white text-black"><i class="fa-solid fa-building-columns text-xs"></i></span>
                                        <span class="font-semibold text-fg transition group-hover:text-brand">{{ $bank->name }}</span>
                                        @unless($bank->is_active) <span class="badge badge-danger">متوقف</span> @endunless
                                    </a>
                                </td>
                                <td>{{ $bank->cases ?: '-' }}</td>
                                <td>{{ $bank->portfolio ? 'EGP ' . number_format($bank->portfolio) : '-' }}</td>
                                <td class="font-semibold text-brand">{{ $bank->collected ? 'EGP ' . number_format($bank->collected) : '-' }}</td>
                                <td>
                                    <div class="mb-1 text-[11px] text-muted">{{ $bank->rate }}%</div>
                                    <div class="progress"><div class="h-full rounded-full {{ $barClass }}" style="width: {{ $bank->rate }}%"></div></div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        <tr class="bg-white/[0.03] font-bold text-fg">
                            <td class="px-4 py-3">الإجمالي</td>
                            <td class="px-4 py-3">{{ $totals['cases'] }}</td>
                            <td class="px-4 py-3">EGP {{ number_format($totals['portfolio']) }}</td>
                            <td class="px-4 py-3 text-brand">EGP {{ number_format($totals['collected']) }}</td>
                            <td class="px-4 py-3"><a href="{{ $links['banks'] }}" class="text-xs font-semibold text-info hover:underline">كل البنوك <i class="fa-solid fa-chevron-left text-[9px]"></i></a></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-panel>

        <x-panel title="توزيع الحالات حسب الشريحة">
            <div class="relative flex h-[270px] justify-center"><canvas id="bucketChart"></canvas></div>
        </x-panel>
    </section>


    {{-- =====================================================
        TODAY: agenda | PTP | alerts
    ====================================================== --}}
    <section class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-3">

        {{-- Today's work --}}
        <x-panel title="مهام اليوم" icon="fa-list-check">
            <ul class="space-y-2">
                @foreach($agenda as [$text, $count, $icon, $toneClass, $url])
                    <li>
                        <a href="{{ $url }}" class="flex items-center gap-3 rounded-xl border border-line bg-white/[0.02] px-3 py-2.5 transition hover:bg-white/[0.05]">
                            <i class="fa-solid {{ $icon }} w-4 text-center text-sm {{ $toneClass }}"></i>
                            <span class="flex-1 text-sm text-fg">{{ $text }}</span>
                            <span class="badge badge-neutral">{{ $count }}</span>
                            <i class="fa-solid fa-chevron-left text-[10px] text-dim"></i>
                        </a>
                    </li>
                @endforeach
            </ul>
        </x-panel>

        {{-- PTP summary --}}
        <x-panel title="ملخص وعود الدفع (PTP)" icon="fa-handshake">
            <div class="mb-4 flex items-end justify-between">
                <div>
                    <div class="text-3xl font-bold text-brand">{{ $ptp['rate'] }}%</div>
                    <div class="text-[11px] text-dim">نسبة التحقيق</div>
                </div>
                <div class="text-left text-[11px] text-dim">{{ $ptp['total'] }} وعود إجمالاً</div>
            </div>

            <div class="space-y-3">
                @foreach($ptp['rows'] as [$label, $count, $barClass])
                    <div>
                        <div class="mb-1 flex justify-between text-[11px]"><span class="text-muted">{{ $label }}</span><span class="font-semibold text-fg">{{ $count }}</span></div>
                        <div class="progress"><div class="h-full rounded-full {{ $barClass }}" style="width: {{ $ptp['total'] > 0 ? round($count / $ptp['total'] * 100) : 0 }}%"></div></div>
                    </div>
                @endforeach
            </div>

            <a href="{{ $ptp['url'] }}" class="btn btn-outline-info btn-sm mt-5 w-full">فتح مركز الوعود <i class="fa-solid fa-chevron-left text-[9px]"></i></a>
        </x-panel>

        {{-- Alerts --}}
        <x-panel title="تنبيهات تحتاج إجراء" icon="fa-triangle-exclamation" tone="danger">
            <ul class="space-y-2">
                @forelse($alerts as [$icon, $tone, $text, $url])
                    <li>
                        <a href="{{ $url }}" class="flex items-start gap-3 rounded-xl border p-3 transition hover:brightness-125 {{ $alertTones[$tone][0] }}">
                            <i class="fa-solid {{ $icon }} mt-0.5 text-sm {{ $alertTones[$tone][1] }}"></i>
                            <span class="text-xs leading-5 text-fg">{{ $text }}</span>
                        </a>
                    </li>
                @empty
                    <x-empty-state icon="fa-circle-check" text="لا توجد تنبيهات، كل شيء على ما يرام" />
                @endforelse
            </ul>
        </x-panel>
    </section>


    {{-- =====================================================
        PEOPLE & MONEY: top employees | latest payments | activity
    ====================================================== --}}
    <section class="grid grid-cols-1 gap-4 xl:grid-cols-3">

        {{-- Top employees --}}
        <x-panel title="أفضل الموظفين" icon="fa-trophy">
            <ul class="space-y-4">
                @forelse($leaders as $leader)
                    <li>
                        <a href="{{ $leader->url }}" class="block">
                            <div class="flex items-center gap-3">
                                <span class="w-4 text-center text-xs font-bold text-dim">{{ $loop->iteration }}</span>
                                <x-avatar :name="$leader->name" :color="$loop->first ? 'warning' : 'info'" />
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="truncate text-sm font-semibold text-fg">{{ $leader->name }}</span>
                                        <span class="text-xs font-bold text-brand">EGP {{ number_format($leader->collected) }}</span>
                                    </div>
                                    <div class="mt-1.5 flex items-center gap-2">
                                        <div class="progress flex-1"><div class="h-full rounded-full {{ $toneBar[$leader->tone] }}" style="width: {{ $leader->efficiency }}%"></div></div>
                                        <span class="text-[11px] font-bold {{ $toneText[$leader->tone] }}">{{ $leader->efficiency }}%</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </li>
                @empty
                    <x-empty-state text="لا توجد بيانات" />
                @endforelse
            </ul>

            <a href="{{ $links['employees'] }}" class="btn btn-outline-info btn-sm mt-5 w-full">كل الموظفين <i class="fa-solid fa-chevron-left text-[9px]"></i></a>
        </x-panel>

        {{-- Latest payments --}}
        <x-panel title="آخر التحصيلات" icon="fa-receipt">
            <ul class="divide-y divide-line">
                @foreach($payments as [$receipt, $client, $amount, $method, $time, $status])
                    <li class="flex items-center justify-between gap-3 py-3 first:pt-0">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="badge badge-info font-mono">{{ $receipt }}</span>
                                <span class="truncate text-xs font-semibold text-fg">{{ $client }}</span>
                            </div>
                            <div class="mt-1 text-[11px] text-dim">{{ $methods[$method] }} · {{ $time }}</div>
                        </div>

                        <div class="shrink-0 text-left">
                            <div class="text-sm font-bold text-brand">+{{ number_format($amount) }}</div>
                            <x-status-badge type="payment" :status="$status" />
                        </div>
                    </li>
                @endforeach
            </ul>

            <a href="{{ $links['confirmations'] }}" class="btn btn-outline-info btn-sm mt-5 w-full">تأكيد التحصيلات <i class="fa-solid fa-chevron-left text-[9px]"></i></a>
        </x-panel>

        {{-- Activity --}}
        <x-panel title="آخر النشاط" icon="fa-clock-rotate-left">
            <ol class="space-y-4">
                @foreach($activity as [$user, $text, $time, $icon, $toneClass])
                    <li class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/[0.05] {{ $toneClass }}"><i class="fa-solid {{ $icon }} text-xs"></i></span>
                        <div>
                            <p class="text-xs leading-5 text-fg"><b>{{ $user }}</b> {{ $text }}</p>
                            <p class="mt-0.5 text-[11px] text-dim">{{ $time }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>

            <a href="{{ $links['activity'] }}" class="btn btn-outline-info btn-sm mt-5 w-full">سجل النشاط الكامل <i class="fa-solid fa-chevron-left text-[9px]"></i></a>
        </x-panel>

    </section>

@endsection


{{-- =========================================================
    CHARTS JAVASCRIPT (colors come from the design tokens)
========================================================== --}}
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const css   = getComputedStyle(document.documentElement);
        const token = (name, fallback) => css.getPropertyValue(`--color-${name}`).trim() || fallback;

        const brand   = token('brand',   '#00ff66');
        const info    = token('info',    '#00aaff');
        const warning = token('warning', '#facc15');
        const danger  = token('danger',  '#f87171');
        const muted   = token('muted',   '#8c98a5');
        const line    = token('line',    'rgba(255,255,255,0.06)');
        const track   = token('track',   '#1e252b');

        Chart.defaults.font.family = 'Cairo';
        Chart.defaults.color       = muted;

        // 1) collected (bars) vs target (dashed line)
        new Chart(document.getElementById('trendChart'), {
            type: 'bar',
            data: {
                labels: @json($trend['labels']),
                datasets: [
                    { label: 'المحصل', data: @json($trend['collected']), backgroundColor: brand, borderRadius: 6, maxBarThickness: 38 },
                    { type: 'line', label: 'المستهدف', data: @json($trend['target']), borderColor: info, borderWidth: 2, borderDash: [6, 6], pointRadius: 3, pointBackgroundColor: info, tension: 0.35 },
                ],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                scales: { x: { grid: { color: line } }, y: { grid: { color: line }, beginAtZero: true } },
                plugins: { legend: { position: 'bottom' } },
            },
        });

        // 2) collected vs remaining
        new Chart(document.getElementById('splitChart'), {
            type: 'doughnut',
            data: { labels: @json($split['labels']), datasets: [{ data: @json($split['data']), backgroundColor: [brand, track], borderWidth: 0 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '72%', plugins: { legend: { position: 'bottom' } } },
        });

        // 3) cases by bucket
        new Chart(document.getElementById('bucketChart'), {
            type: 'doughnut',
            data: { labels: @json($buckets['labels']), datasets: [{ data: @json($buckets['data']), backgroundColor: [brand, info, warning, danger], borderWidth: 0 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom' } } },
        });
    </script>
@endpush