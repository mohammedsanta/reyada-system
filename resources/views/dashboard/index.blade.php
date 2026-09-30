{{-- resources/views/dashboard/index.blade.php --}}
@extends('layouts.app')

@section('title', 'مركز القيادة والتحكم - Collex')

@section('content')

    {{-- HEADER --}}
    <x-page-header
        title="مركز القيادة والتحكم"
        subtitle="نظرة عامة على أداء ومؤشرات التحصيل الحالية"
        icon="fa-gauge-high"
    >
        <x-slot:actions>
            <div class="chip">
                <span class="h-2 w-2 rounded-full bg-brand shadow-[0_0_8px_var(--color-brand)]"></span>
                <span>{{ $userName }}</span>
            </div>
        </x-slot:actions>
    </x-page-header>


    {{-- STATISTICS --}}
    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($stats as $stat)
            <x-stat-card :label="$stat['label']" :value="$stat['value']" :unit="$stat['unit']" />
        @endforeach
    </section>


    {{-- CHARTS --}}
    <section class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-3">
        <x-panel title="أداء التحصيل الشهري" class="xl:col-span-2">
            <div class="relative h-[250px]">
                <canvas id="monthlyLineChart"></canvas>
            </div>
        </x-panel>

        <x-panel title="توزيع المحفظة الاستراتيجية">
            <div class="relative flex h-[250px] justify-center">
                <canvas id="portfolioPieChart"></canvas>
            </div>
        </x-panel>
    </section>


    {{-- BOTTOM --}}
    <section class="grid grid-cols-1 gap-4 lg:grid-cols-2">

        {{-- Broken promises --}}
        <x-panel title="تنبيهات الوعود المكسورة" icon="fa-triangle-exclamation" tone="danger">
            <div class="space-y-2">
                @forelse($alerts as $alert)
                    <div class="flex items-center justify-between rounded-lg border-r-4 border-danger bg-danger/[0.08] p-3">
                        <span class="text-xs text-fg">{{ $alert['message'] }}</span>
                        <span class="rounded bg-danger px-2 py-1 text-[10px] font-semibold text-black">
                            {{ $alert['badge'] }}
                        </span>
                    </div>
                @empty
                    <p class="text-xs text-dim">لا توجد تنبيهات</p>
                @endforelse
            </div>
        </x-panel>

        {{-- Recent transactions --}}
        <x-panel title="آخر المعاملات الميدانية" icon="fa-receipt">
            @forelse($transactions as $tx)
                <div class="flex items-center justify-between border-b border-line py-3 last:border-b-0">
                    <div>
                        <h5 class="text-xs font-semibold text-fg">{{ $tx['title'] }}</h5>
                        <p class="mt-1 text-[11px] text-muted">{{ $tx['collector'] }}</p>
                    </div>

                    <div class="font-bold text-brand">
                        +{{ number_format($tx['amount']) }} EGP
                    </div>
                </div>
            @empty
                <p class="text-xs text-dim">لا توجد معاملات</p>
            @endforelse
        </x-panel>

    </section>

@endsection


@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Colors come from the design tokens in app.css (fallback = same values).
        const css   = getComputedStyle(document.documentElement);
        const token = (name, fallback) => css.getPropertyValue(`--color-${name}`).trim() || fallback;

        const brand = token('brand', '#00ff66');
        const muted = token('muted', '#8c98a5');
        const line  = token('line',  'rgba(255,255,255,0.06)');
        const track = token('track', '#1e252b');

        Chart.defaults.font.family = 'Cairo';
        Chart.defaults.color       = muted;

        // Monthly line chart
        new Chart(document.getElementById('monthlyLineChart'), {
            type: 'line',
            data: {
                labels: @json($monthlyChart['labels']),
                datasets: [{
                    label: 'معدل التحصيل المالي',
                    data: @json($monthlyChart['data']),
                    borderColor: brand,
                    borderWidth: 3,
                    backgroundColor: 'rgba(0, 255, 102, 0.05)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: brand,
                    pointBorderColor: brand,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: line } },
                    y: { grid: { color: line } },
                },
            },
        });

        // Portfolio doughnut
        new Chart(document.getElementById('portfolioPieChart'), {
            type: 'doughnut',
            data: {
                labels: @json($portfolio['labels']),
                datasets: [{
                    data: @json($portfolio['data']),
                    backgroundColor: [brand, track],
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: { legend: { position: 'bottom' } },
            },
        });
    </script>
@endpush