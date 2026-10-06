{{-- resources/views/activity-logs/index.blade.php --}}
@extends('layouts.app')

@section('title', 'سجل النشاط')

@php
    $eventTones = ['login' => 'badge-neutral', 'created' => 'badge-success', 'updated' => 'badge-info', 'deleted' => 'badge-danger', 'imported' => 'badge-outline-info', 'exported' => 'badge-outline-info', 'assigned' => 'badge-warning', 'confirmed' => 'badge-success'];
@endphp

@section('content')

    <x-page-header title="سجل النشاط" subtitle="من فعل ماذا ومتى ومن أين" icon="fa-clock-rotate-left" />

    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($stats as $stat)
            <x-metric-card :label="$stat['label']" :value="$stat['value']" :icon="$stat['icon']" :color="$stat['color']" />
        @endforeach
    </section>

    {{-- FILTERS --}}
    <form method="GET" action="{{ route('activity-logs.index') }}" class="card filter-bar">
        <div class="min-w-[220px] flex-1">
            <label for="search" class="sr-only">بحث</label>
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-dim"></i>
                <input id="search" name="search" type="search" value="{{ $filters['search'] }}" placeholder="ابحث في الوصف..." class="form-input pl-9">
            </div>
        </div>

        <div class="w-44">
            <label for="user" class="form-label">المستخدم</label>
            <select id="user" name="user" class="form-input" onchange="this.form.submit()">
                <option value="">الكل</option>
                @foreach($users as $name) <option value="{{ $name }}" @selected($filters['user'] === $name)>{{ $name }}</option> @endforeach
            </select>
        </div>

        <div class="w-40">
            <label for="event" class="form-label">الحدث</label>
            <select id="event" name="event" class="form-input" onchange="this.form.submit()">
                <option value="">الكل</option>
                @foreach($events as $key => $label) <option value="{{ $key }}" @selected($filters['event'] === $key)>{{ $label }}</option> @endforeach
            </select>
        </div>

        <div class="w-44">
            <label for="date" class="form-label">التاريخ</label>
            <input id="date" name="date" type="date" value="{{ $filters['date'] }}" class="form-input" onchange="this.form.submit()">
        </div>

        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter text-xs"></i> تصفية</button>
        <a href="{{ route('activity-logs.index') }}" class="btn btn-secondary" title="إعادة ضبط"><i class="fa-solid fa-rotate-right"></i></a>
    </form>

    {{-- TABLE --}}
    <div class="table-wrap">
        <table class="data-table whitespace-nowrap">
            <thead><tr><th>الوقت</th><th>المستخدم</th><th>الحدث</th><th>الوصف</th><th>الجهاز</th><th></th></tr></thead>

            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->at }}</td>
                        <td>
                            <div class="flex items-center gap-2"><x-avatar :name="$log->user" size="h-8 w-8" /> <span class="font-semibold text-fg">{{ $log->user }}</span></div>
                        </td>
                        <td><span class="badge {{ $eventTones[$log->event] }}">{{ $events[$log->event] }}</span></td>
                        <td class="whitespace-normal min-w-[280px]">
                            <span class="text-fg">{{ $log->description }}</span>
                            @if($log->subject) <span class="mr-1 text-[11px] text-dim">· {{ $log->subject }}</span> @endif
                        </td>
                        <td>
                            <span dir="ltr">{{ $log->ip }}</span><br>
                            <span class="text-[11px] text-dim">{{ $log->device }}</span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-secondary btn-sm" data-log="{{ $log->id }}"><i class="fa-solid fa-eye text-xs"></i> تفاصيل</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-empty-state text="لا توجد أنشطة مطابقة" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- DETAILS DIALOG --}}
    <x-modal id="logModal" title="تفاصيل النشاط" width="40rem">
        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div><dt class="text-[11px] text-dim">المستخدم</dt><dd id="log-user" class="mt-1 font-semibold text-fg"></dd></div>
            <div><dt class="text-[11px] text-dim">الحدث</dt><dd id="log-event" class="mt-1 text-fg"></dd></div>
            <div><dt class="text-[11px] text-dim">الوقت</dt><dd id="log-at" class="mt-1 text-fg"></dd></div>
            <div><dt class="text-[11px] text-dim">الجهاز</dt><dd class="mt-1 text-fg"><span id="log-ip" dir="ltr"></span> · <span id="log-device"></span></dd></div>
            <div class="col-span-2"><dt class="text-[11px] text-dim">الوصف</dt><dd id="log-description" class="mt-1 text-fg"></dd></div>
        </dl>

        <div id="log-changes-wrap" class="mt-5">
            <p class="mb-2 text-xs font-bold text-fg">التغييرات</p>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>الحقل</th><th>قبل</th><th>بعد</th></tr></thead>
                    <tbody id="log-changes"></tbody>
                </table>
            </div>
        </div>
        <p id="log-no-changes" class="mt-5 hidden text-xs text-dim">لا توجد قيم محفوظة لهذا النشاط.</p>
    </x-modal>

@endsection

@push('scripts')
    <script>
        const details = @json($details);

        const text = (id, value) => (document.getElementById(id).textContent = value ?? '-');

        document.addEventListener('click', (event) => {
            const button = event.target.closest('[data-log]');
            if (!button) return;

            const log = details[button.dataset.log];

            text('log-user', log.user);
            text('log-event', log.event);
            text('log-at', log.at);
            text('log-ip', log.ip);
            text('log-device', log.device);
            text('log-description', log.subject ? `${log.description} (${log.subject})` : log.description);

            // changes table (built with textContent, never innerHTML)
            const body = document.getElementById('log-changes');
            body.replaceChildren();

            log.changes.forEach(([field, before, after]) => {
                const row = body.insertRow();
                [field, before, after].forEach((value, index) => {
                    const cell = row.insertCell();
                    cell.textContent = value;
                    if (index === 1) cell.className = 'text-danger';
                    if (index === 2) cell.className = 'text-brand';
                });
            });

            document.getElementById('log-changes-wrap').classList.toggle('hidden', log.changes.length === 0);
            document.getElementById('log-no-changes').classList.toggle('hidden', log.changes.length !== 0);

            document.getElementById('logModal').showModal();
        });
    </script>
@endpush