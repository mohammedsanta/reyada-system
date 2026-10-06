{{-- resources/views/banks/visits.blade.php --}}
@extends('layouts.app')

@section('title', 'إدارة الزيارات - ' . $bank->name)

@section('content')

    <x-bank-header :bank="$bank" title="إدارة الزيارات" subtitle="جدولة الزيارات الميدانية ومتابعة نتائجها">
        <x-slot:actions>
            <button type="button" class="btn btn-primary btn-sm" data-open-modal="scheduleModal"><i class="fa-solid fa-calendar-plus text-xs"></i> جدولة زيارة</button>
        </x-slot:actions>
    </x-bank-header>

    <x-flash />

    @error('action') <div class="alert alert-danger mb-6">{{ $message }}</div> @enderror

    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-metric-card label="زيارات اليوم" :value="$today" icon="fa-calendar-day" color="cyan" />
        <x-metric-card label="مجدولة" :value="$counts['scheduled'] ?? 0" icon="fa-calendar-check" color="info" />
        <x-metric-card label="تمت" :value="$counts['completed'] ?? 0" icon="fa-circle-check" color="brand" />
        <x-metric-card label="فائتة" :value="$counts['missed'] ?? 0" icon="fa-circle-xmark" color="danger" />
    </section>

    {{-- Tabs + collector filter --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div class="segmented">
            <a href="{{ route('banks.visits.index', ['bank' => $bank->id, 'collector' => $collector ?: null]) }}" class="segmented-item {{ $status === '' ? 'segmented-item-active' : '' }}">
                الكل <span class="text-[10px] opacity-70">({{ $counts['all'] }})</span>
            </a>
            @foreach($statuses as $key => $label)
                <a href="{{ route('banks.visits.index', ['bank' => $bank->id, 'status' => $key, 'collector' => $collector ?: null]) }}" class="segmented-item {{ $status === $key ? 'segmented-item-active' : '' }}">
                    {{ $label }} <span class="text-[10px] opacity-70">({{ $counts[$key] ?? 0 }})</span>
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('banks.visits.index', $bank->id) }}" class="w-56">
            <input type="hidden" name="status" value="{{ $status }}">
            <label for="collector" class="sr-only">المحصل</label>
            <select id="collector" name="collector" class="form-input" onchange="this.form.submit()">
                <option value="0">كل المحصلين</option>
                @foreach($employees as $id => $name)
                    <option value="{{ $id }}" @selected($collector === $id)>{{ $name }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="table-wrap">
        <table class="data-table whitespace-nowrap">
            <thead>
                <tr><th>الموعد</th><th>العميل</th><th>العنوان</th><th>المحصل</th><th>الحالة</th><th>النتيجة / ملاحظات</th><th>الإجراءات</th></tr>
            </thead>
            <tbody>
                @forelse($visits as $visit)
                    <tr>
                        <td @class(['font-semibold', 'text-brand' => $visit->when->isToday() && $visit->status === 'scheduled', 'text-fg' => ! ($visit->when->isToday() && $visit->status === 'scheduled')])>{{ $visit->when_label }}</td>
                        <td class="font-semibold text-fg">{{ $visit->client_name }} <span class="text-[11px] text-dim">({{ $visit->client_code }})</span></td>
                        <td class="whitespace-normal min-w-[240px]">{{ $visit->address }}</td>
                        <td>
                            <div class="flex items-center gap-2"><x-avatar :name="$visit->collector->name" size="h-8 w-8" /> {{ $visit->collector->name }}</div>
                        </td>
                        <td><x-status-badge type="visit" :status="$visit->status" /></td>
                        <td class="whitespace-normal min-w-[220px]">
                            @if($visit->outcome) <b class="text-fg">{{ $outcomes[$visit->outcome] }}</b><br> @endif
                            <span class="text-[11px] text-dim">{{ $visit->notes ?? '-' }}</span>
                        </td>
                        <td>
                            @if($visit->status === 'scheduled')
                                <div class="flex items-center gap-2">
                                    <button type="button" class="btn btn-outline-success btn-sm" data-result="{{ $visit->id }}" data-client="{{ $visit->client_name }}">
                                        <i class="fa-solid fa-clipboard-check text-xs"></i> تسجيل النتيجة
                                    </button>

                                    <form method="POST" action="{{ route('banks.visits.update', [$bank->id, $visit->id]) }}" onsubmit="return confirm('هل تريد إلغاء هذه الزيارة؟')">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="action" value="cancel">
                                        <button type="submit" class="btn btn-danger btn-sm">إلغاء</button>
                                    </form>
                                </div>
                            @else
                                <span class="text-dim">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><x-empty-state icon="fa-person-walking" text="لا توجد زيارات مطابقة" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- SCHEDULE A VISIT --}}
    <x-modal id="scheduleModal" title="جدولة زيارة ميدانية" width="42rem">
        <form method="POST" action="{{ route('banks.visits.store', $bank->id) }}" class="space-y-4">
            @csrf
            <input type="hidden" name="_form" value="schedule">

            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <x-select-field name="client_code" label="العميل" :options="$clients" placeholder="اختر العميل..." />
                <x-select-field name="user_id" label="المحصل" :options="$employees" placeholder="اختر المحصل..." />
            </div>

            <x-form-field name="scheduled_at" label="موعد الزيارة" type="datetime-local" />
            <x-form-field name="address" label="عنوان الزيارة (اتركه فارغاً لاستخدام عنوان العميل)" />
            <x-textarea-field name="notes" label="ملاحظات" rows="2" />

            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">جدولة</button>
                <button type="button" class="btn btn-secondary" data-close-modal>إلغاء</button>
            </div>
        </form>
    </x-modal>

    {{-- RECORD THE RESULT (one dialog reused for every row) --}}
    <x-modal id="resultModal" title="تسجيل نتيجة الزيارة" width="34rem">
        <form id="resultForm" method="POST" action="#" class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="_form" value="result">
            <input type="hidden" name="_visit_id" id="result-visit-id" value="">
            <input type="hidden" name="action" value="result">

            <p class="text-sm text-muted">زيارة العميل: <b id="result-client" class="text-fg"></b></p>

            <x-select-field name="outcome" label="النتيجة" :options="$outcomes" placeholder="اختر النتيجة..." />
            <x-textarea-field name="notes" label="ملاحظات" rows="3" />

            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">حفظ النتيجة</button>
                <button type="button" class="btn btn-secondary" data-close-modal>إلغاء</button>
            </div>
        </form>
    </x-modal>

@endsection

@push('scripts')
    <script>
        const updateUrl = @json(route('banks.visits.update', [$bank->id, '__ID__']));

        function openResult(id) {
            const button = document.querySelector(`[data-result="${id}"]`);
            if (!button) return;

            document.getElementById('resultForm').action = updateUrl.replace('__ID__', id);
            document.getElementById('result-visit-id').value = id;
            document.getElementById('result-client').textContent = button.dataset.client;
            document.getElementById('resultModal').showModal();
        }

        document.addEventListener('click', (event) => {
            const button = event.target.closest('[data-result]');
            if (button) openResult(button.dataset.result);
        });

        // validation failed: open the same dialog again so the errors are visible
        @if($errors->any() && old('_form') === 'schedule')
            document.getElementById('scheduleModal').showModal();
        @elseif($errors->any() && old('_form') === 'result' && old('_visit_id'))
            openResult(@json(old('_visit_id')));
        @endif
    </script>
@endpush