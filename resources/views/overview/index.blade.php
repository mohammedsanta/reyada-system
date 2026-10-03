{{-- resources/views/overview/index.blade.php --}}
@extends('layouts.app')

@section('title', 'نظرة عامة')

@section('content')

    <x-page-header title="نظرة عامة" subtitle="تحليل تكوين المحفظة وأداء البنوك" icon="fa-chart-pie" />

    {{-- FILTER --}}
    <form method="GET" action="{{ route('overview.index') }}" class="card filter-bar">
        <div class="w-64">
            <label for="bank" class="form-label">البنك</label>
            <select id="bank" name="bank" class="form-input" onchange="this.form.submit()">
                <option value="0">كل البنوك</option>
                @foreach($bankOptions as $id => $name)
                    <option value="{{ $id }}" @selected($selected === $id)>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <a href="{{ route('overview.index') }}" class="btn btn-secondary" title="إعادة ضبط"><i class="fa-solid fa-rotate-right"></i></a>
    </form>

    {{-- KPIs --}}
    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach($stats as $stat)
            <x-metric-card :label="$stat['label']" :value="$stat['value']" :unit="$stat['unit']" :icon="$stat['icon']" :color="$stat['color']" />
        @endforeach
    </section>

    {{-- ROW 1: trend + bucket --}}
    <section class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-3">
        <x-panel title="التحصيل مقابل المستهدف (ألف EGP)" class="xl:col-span-2">
            <div class="relative h-[270px]"><canvas id="trendChart"></canvas></div>
        </x-panel>

        <x-panel title="توزيع الحالات حسب الشريحة">
            <div class="relative flex h-[270px] justify-center"><canvas id="bucketChart"></canvas></div>
        </x-panel>
    </section>

    {{-- ROW 2: loan types + governorates --}}
    <section class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-3">
        <x-panel title="المحفظة حسب نوع القرض">
            <div class="relative flex h-[270px] justify-center"><canvas id="loanChart"></canvas></div>
        </x-panel>

        <x-panel title="المحفظة حسب المحافظة (EGP)" class="xl:col-span-2">
            <div class="relative h-[270px]"><canvas id="govChart"></canvas></div>
        </x-panel>
    </section>

    {{-- ROW 3: banks + aging --}}
    <section class="grid grid-cols-1 gap-4 xl:grid-cols-3">

        <x-panel title="أداء البنوك" icon="fa-building-columns" class="xl:col-span-2">
            <div class="table-wrap">
                <table class="data-table whitespace-nowrap">
                    <thead><tr><th>البنك</th><th>الحالات</th><th>المحفظة</th><th>المحصل</th><th class="w-44">نسبة التحصيل</th></tr></thead>
                    <tbody>
                        @foreach($banks as $bank)
                            @php $barClass = $bank->rate >= 40 ? 'bg-brand' : ($bank->rate >= 20 ? 'bg-warning' : 'bg-danger'); @endphp
                            <tr>
                                <td>
                                    <a href="{{ $bank->url }}" class="group inline-flex items-center gap-3">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white text-black"><i class="fa-solid fa-building-columns text-xs"></i></span>
                                        <span class="font-semibold text-fg transition group-hover:text-brand">{{ $bank->name }}</span>
                                    </a>
                                </td>
                                <td>{{ $bank->cases ?: '-' }}</td>
                                <td>{{ $bank->portfolio ? 'EGP ' . number_format($bank->portfolio) : '-' }}</td>
                                <td class="text-brand">{{ $bank->collected ? 'EGP ' . number_format($bank->collected) : '-' }}</td>
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
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-panel>

        <x-panel title="أعمار الديون (DPD)" icon="fa-hourglass-half">
            <ul class="space-y-4">
                @foreach($aging as $row)
                    <li>
                        <div class="mb-1 flex items-center justify-between text-xs">
                            <span class="font-semibold text-fg">{{ $row->label }}</span>
                            <span class="text-muted">{{ $row->percent }}%</span>
                        </div>
                        <div class="progress"><div class="h-full rounded-full {{ $row->bar }}" style="width: {{ $row->percent }}%"></div></div>
                        <div class="mt-1 flex justify-between text-[11px] text-dim"><span>{{ $row->cases }} حالة</span><span>EGP {{ number_format($row->amount) }}</span></div>
                    </li>
                @endforeach
            </ul>
        </x-panel>
    </section>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const css   = getComputedStyle(document.documentElement);
        const token = (name, fallback) => css.getPropertyValue(`--color-${name}`).trim() || fallback;

        const brand = token('brand', '#00ff66'), info = token('info', '#00aaff'), warning = token('warning', '#facc15'), danger = token('danger', '#f87171');
        const accent = token('accent', '#a855f7'), muted = token('muted', '#8c98a5'), line = token('line', 'rgba(255,255,255,0.06)');

        Chart.defaults.font.family = 'Cairo';
        Chart.defaults.color = muted;

        const legendBottom = { legend: { position: 'bottom' } };

        // collected (bars) vs target (dashed line)
        new Chart(document.getElementById('trendChart'), {
            type: 'bar',
            data: {
                labels: @json($trend['labels']),
                datasets: [
                    { label: 'المحصل', data: @json($trend['collected']), backgroundColor: brand, borderRadius: 6, maxBarThickness: 38 },
                    { type: 'line', label: 'المستهدف', data: @json($trend['target']), borderColor: info, borderWidth: 2, borderDash: [6, 6], pointRadius: 3, pointBackgroundColor: info, tension: 0.35 },
                ],
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { x: { grid: { color: line } }, y: { grid: { color: line }, beginAtZero: true } }, plugins: legendBottom },
        });

        new Chart(document.getElementById('bucketChart'), {
            type: 'doughnut',
            data: { labels: @json($bucket['labels']), datasets: [{ data: @json($bucket['data']), backgroundColor: [brand, info, warning, danger], borderWidth: 0 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: legendBottom },
        });

        new Chart(document.getElementById('loanChart'), {
            type: 'doughnut',
            data: { labels: @json($loanTypes['labels']), datasets: [{ data: @json($loanTypes['data']), backgroundColor: [info, accent, warning], borderWidth: 0 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: legendBottom },
        });

        // horizontal bars: governorates
        new Chart(document.getElementById('govChart'), {
            type: 'bar',
            data: { labels: @json($govs['labels']), datasets: [{ label: 'المحفظة', data: @json($govs['data']), backgroundColor: info, borderRadius: 6, maxBarThickness: 22 }] },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, scales: { x: { grid: { color: line }, beginAtZero: true }, y: { grid: { display: false } } }, plugins: { legend: { display: false } } },
        });
    </script>
@endpush