{{-- resources/views/banks/distribution.blade.php --}}
@extends('layouts.app')

@section('title', 'توزيع الحالات - ' . $bank->name)

@section('content')

    <x-bank-header :bank="$bank" title="توزيع الحالات" subtitle="توزيع الحالات على الموظفين" />

    <x-flash />

    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-metric-card label="إجمالي الحالات" :value="$stats['total']" icon="fa-briefcase" color="info" />
        <x-metric-card label="حالات موزعة" :value="$stats['assigned']" icon="fa-user-check" color="brand" />
        <x-metric-card label="حالات غير موزعة" :value="$stats['unassigned']" icon="fa-user-xmark" color="warning" />
        <x-metric-card label="الموظفون المتاحون" :value="$stats['employees']" icon="fa-users" color="cyan" />
    </section>

    <form id="distributionForm" method="POST" action="{{ route('banks.distribution.store', $bank->id) }}"
          data-total="{{ $stats['total'] }}" data-unassigned="{{ $stats['unassigned'] }}"
          class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-3">
        @csrf

        {{-- SETTINGS --}}
        <div class="space-y-4">
            <x-section-card title="أي الحالات؟" icon="fa-filter" color="info">
                <div class="space-y-2">
                    @foreach($scopes as $key => $scope)
                        <label class="check-row">
                            <input type="radio" name="scope" value="{{ $key }}" class="accent-brand" @checked(old('scope', 'unassigned') === $key)>
                            <span><b class="text-fg">{{ $scope['label'] }}</b><span class="block text-[11px] text-dim">{{ $scope['hint'] }}</span></span>
                        </label>
                    @endforeach
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3">
                    <x-select-field name="bucket" label="الشريحة" :options="array_combine($buckets, $buckets)" placeholder="الكل" />
                    <x-select-field name="loan_type" label="نوع القرض" :options="array_combine($loanTypes, $loanTypes)" placeholder="الكل" />
                </div>
            </x-section-card>

            <x-section-card title="طريقة التوزيع" icon="fa-sliders" color="warning">
                <div class="space-y-2">
                    @foreach($modes as $key => $mode)
                        <label class="check-row">
                            <input type="radio" name="mode" value="{{ $key }}" class="accent-brand" @checked(old('mode', 'equal') === $key)>
                            <span><b class="text-fg">{{ $mode['label'] }}</b><span class="block text-[11px] text-dim">{{ $mode['hint'] }}</span></span>
                        </label>
                    @endforeach
                </div>
            </x-section-card>

            <div class="card">
                <p class="text-[11px] text-dim">ملخص التوزيع</p>
                <p id="preview" class="mt-1 text-sm font-semibold text-fg"></p>

                @error('counts')       <p class="form-error">{{ $message }}</p> @enderror
                @error('employee_ids') <p class="form-error">{{ $message }}</p> @enderror

                <div class="mt-4 flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-play text-xs"></i> تنفيذ التوزيع</button>
                    <a href="{{ route('banks.panel', $bank->id) }}" class="btn btn-secondary">إلغاء</a>
                </div>
            </div>
        </div>

        {{-- EMPLOYEES --}}
        <div class="xl:col-span-2">
            <x-panel title="الموظفون" icon="fa-users">
                <div class="table-wrap">
                    <table class="data-table whitespace-nowrap">
                        <thead>
                            <tr>
                                <th class="w-10"><input type="checkbox" class="accent-brand" data-check-all=".emp-check" aria-label="تحديد الكل" checked></th>
                                <th>الموظف</th>
                                <th>الرتبة</th>
                                <th class="w-56">الحمل الحالي</th>
                                <th class="w-32">عدد الحالات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($employees as $employee)
                                @php $load = (int) round($employee->current / $employee->capacity * 100); @endphp
                                <tr class="emp-row">
                                    <td>
                                        <input type="checkbox" name="employee_ids[]" value="{{ $employee->id }}" class="emp-check accent-brand"
                                               @checked(in_array($employee->id, old('employee_ids', $employees->pluck('id')->all())))>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <x-avatar :name="$employee->name" />
                                            <div>
                                                <div class="font-semibold text-fg">{{ $employee->name }}</div>
                                                <div class="text-[11px] text-dim">{{ $employee->code }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-outline-info">{{ $employee->role }}</span></td>
                                    <td>
                                        <div class="mb-1 flex justify-between text-[11px] text-muted"><span>{{ $employee->current }} / {{ $employee->capacity }}</span><span>{{ $load }}%</span></div>
                                        <div class="progress"><div class="h-full rounded-full {{ $load >= 80 ? 'bg-danger' : 'bg-brand' }}" style="width: {{ $load }}%"></div></div>
                                    </td>
                                    <td>
                                        <input type="number" min="0" name="counts[{{ $employee->id }}]" value="{{ old('counts.' . $employee->id) }}"
                                               class="emp-count form-input py-1.5 disabled:opacity-40" placeholder="0">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-panel>
        </div>
    </form>

    {{-- HISTORY --}}
    <x-panel title="آخر عمليات التوزيع" icon="fa-clock-rotate-left">
        <div class="table-wrap">
            <table class="data-table whitespace-nowrap">
                <thead><tr><th>الوقت</th><th>المنفذ</th><th>عدد الحالات</th><th>عدد الموظفين</th><th>الطريقة</th></tr></thead>
                <tbody>
                    @foreach($history as [$when, $by, $cases, $people, $mode])
                        <tr><td>{{ $when }}</td><td class="text-fg">{{ $by }}</td><td>{{ $cases }}</td><td>{{ $people }}</td><td><span class="badge badge-neutral">{{ $mode }}</span></td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-panel>

@endsection

@push('scripts')
    <script>
        // live summary + enable the manual count inputs only in "manual" mode
        const form    = document.getElementById('distributionForm');
        const preview = document.getElementById('preview');
        const pools   = { unassigned: Number(form.dataset.unassigned), all: Number(form.dataset.total) };

        function refresh() {
            const scope    = form.querySelector('[name="scope"]:checked').value;
            const mode     = form.querySelector('[name="mode"]:checked').value;
            const rows     = [...form.querySelectorAll('.emp-row')];
            const selected = rows.filter(row => row.querySelector('.emp-check').checked);
            const pool     = pools[scope];
            let text = '', warn = false;

            rows.forEach(row => {
                row.querySelector('.emp-count').disabled = !(mode === 'manual' && row.querySelector('.emp-check').checked);
            });

            if (!selected.length) {
                text = 'اختر موظفاً واحداً على الأقل'; warn = true;
            } else if (mode === 'equal') {
                text = `توزيع ${pool} حالة على ${selected.length} موظفين (≈ ${Math.floor(pool / selected.length)} لكل موظف)`;
            } else if (mode === 'capacity') {
                text = `توزيع ${pool} حالة حسب السعة المتاحة لكل موظف`;
            } else {
                const sum = selected.reduce((total, row) => total + (Number(row.querySelector('.emp-count').value) || 0), 0);
                warn = sum > pool;
                text = `المجموع اليدوي ${sum} من ${pool} حالة` + (warn ? ' (أكبر من المتاح)' : '');
            }

            preview.textContent = text;
            preview.classList.toggle('text-danger', warn);
            preview.classList.toggle('text-fg', !warn);
        }

        form.addEventListener('input', refresh);
        form.addEventListener('change', () => setTimeout(refresh, 0));   // wait for "select all" to finish
        refresh();
    </script>
@endpush