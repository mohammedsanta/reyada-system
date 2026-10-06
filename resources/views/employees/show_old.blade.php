{{-- resources/views/employees/show.blade.php : performance details of one employee --}}
@extends('layouts.app')

@section('title', 'أداء الموظف - ' . $user->name)

@php
    $toneText = ['brand' => 'text-brand', 'warning' => 'text-warning', 'danger' => 'text-danger'];
    $toneBar  = ['brand' => 'bg-brand',   'warning' => 'bg-warning',   'danger' => 'bg-danger'];
@endphp

@section('content')

    <x-page-header title="تفاصيل أداء الموظف" :subtitle="$user->name . ' · ' . $user->code" icon="fa-chart-line">
        <x-slot:actions>
            <a href="{{ route('users.show', $user->id) }}" class="btn btn-outline-info btn-sm"><i class="fa-solid fa-user text-xs"></i> ملف الموظف</a>
            <a href="{{ route('employees.index') }}" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-right text-xs"></i> رجوع</a>
        </x-slot:actions>
    </x-page-header>

    {{-- Identity + month filter --}}
    <div class="card mb-6 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <x-avatar :name="$user->name" color="brand" size="h-14 w-14 text-sm" />
            <div>
                <p class="text-base font-bold text-fg">{{ $user->name }}</p>
                <p class="mt-1 flex items-center gap-2 text-xs text-muted">
                    <span class="badge badge-outline-info">{{ $user->role }}</span>
                    @if($user->supervisor) المشرف: {{ $user->supervisor['name'] }} @endif
                </p>
            </div>
        </div>

        <form method="GET" action="{{ route('employees.show', $user->code) }}" class="flex items-end gap-3">
            <x-select-field name="month" label="الشهر" :options="$months" :value="$filters['month']" onchange="this.form.submit()" />
            <x-select-field name="year" label="السنة" :options="$years" :value="$filters['year']" onchange="this.form.submit()" />
        </form>
    </div>

    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach($stats as $stat)
            <x-metric-card :label="$stat['label']" :value="$stat['value']" :unit="$stat['unit']" :icon="$stat['icon']" :color="$stat['color']" />
        @endforeach
    </section>

    {{-- Charts --}}
    <section class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-3">
        <x-panel title="التحصيل مقابل المستهدف (ألف EGP)" class="xl:col-span-2">
            <div class="relative h-[260px]"><canvas id="collectChart"></canvas></div>
        </x-panel>

        <x-panel title="نتائج وعود الدفع">
            <div class="relative flex h-[260px] justify-center"><canvas id="promiseChart"></canvas></div>
        </x-panel>
    </section>

    <section class="grid grid-cols-1 gap-4 xl:grid-cols-2">

        {{-- Per bank --}}
        <x-panel title="الأداء حسب البنك" icon="fa-building-columns">
            <div class="table-wrap">
                <table class="data-table whitespace-nowrap">
                    <thead><tr><th>البنك</th><th>الحالات</th><th>التحصيل</th><th>محقق</th><th>مكسور</th><th class="w-40">الكفاءة</th></tr></thead>
                    <tbody>
                        @forelse($banks as $bank)
                            <tr>
                                <td class="font-semibold text-fg">{{ $bank->name }}</td>
                                <td>{{ $bank->cases }}</td>
                                <td class="text-brand">EGP {{ number_format($bank->collected) }}</td>
                                <td>{{ $bank->kept }}</td>
                                <td @class(['text-danger' => $bank->broken > 0])>{{ $bank->broken }}</td>
                                <td>
                                    <div class="mb-1 text-[11px] font-bold {{ $toneText[$tone] }}">{{ $bank->efficiency }}%</div>
                                    <div class="progress"><div class="h-full rounded-full {{ $toneBar[$tone] }}" style="width: {{ $bank->efficiency }}%"></div></div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-empty-state text="لا توجد بنوك مسندة لهذا الموظف" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-panel>

        {{-- Recent promises --}}
        <x-panel title="آخر الوعود" icon="fa-handshake">
            <div class="table-wrap">
                <table class="data-table whitespace-nowrap">
                    <thead><tr><th>العميل</th><th>المبلغ</th><th>الموعد</th><th>الحالة</th></tr></thead>
                    <tbody>
                        @forelse($profile['recent'] as [$client, $amount, $when, $status])
                            <tr>
                                <td class="font-semibold text-fg">{{ $client }}</td>
                                <td>EGP {{ number_format($amount) }}</td>
                                <td>{{ $when }}</td>
                                <td><x-status-badge type="ptp" :status="$status" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><x-empty-state text="لا توجد وعود" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-panel>

    </section>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const css   = getComputedStyle(document.documentElement);
        const token = (name, fallback) => css.getPropertyValue(`--color-${name}`).trim() || fallback;
        const brand = token('brand', '#00ff66'), info = token('info', '#00aaff'), warning = token('warning', '#facc15'), danger = token('danger', '#f87171');
        const muted = token('muted', '#8c98a5'), line = token('line', 'rgba(255,255,255,0.06)');

        Chart.defaults.font.family = 'Cairo';
        Chart.defaults.color = muted;

        // collected (bars) vs target (line)
        new Chart(document.getElementById('collectChart'), {
            type: 'bar',
            data: {
                labels: @json($labels),
                datasets: [
                    { label: 'المحصل', data: @json($profile['trend']['collected']), backgroundColor: brand, borderRadius: 6 },
                    { type: 'line', label: 'المستهدف', data: @json($profile['trend']['target']), borderColor: info, borderWidth: 2, borderDash: [6, 6], pointRadius: 3, tension: 0.3 },
                ],
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { x: { grid: { color: line } }, y: { grid: { color: line } } }, plugins: { legend: { position: 'bottom' } } },
        });

        // promise outcomes
        new Chart(document.getElementById('promiseChart'), {
            type: 'doughnut',
            data: {
                labels: ['محقق كلياً', 'محقق جزئياً', 'مكسور', 'نشط'],
                datasets: [{ data: @json(array_values([$profile['promises']['kept'], $profile['promises']['partial'], $profile['promises']['broken'], $profile['promises']['active']])), backgroundColor: [brand, info, danger, warning], borderWidth: 0 }],
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'bottom' } } },
        });
    </script>
@endpush