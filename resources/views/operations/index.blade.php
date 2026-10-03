{{-- resources/views/operations/index.blade.php --}}
@extends('layouts.app')

@section('title', 'مركز العمليات')

@php
    $tones = [
        'brand' => 'bg-brand/15 text-brand', 'accent' => 'bg-accent/15 text-accent', 'warning' => 'bg-warning/15 text-warning',
        'pink' => 'bg-pink/15 text-pink', 'cyan' => 'bg-cyan/15 text-cyan', 'orange' => 'bg-orange/15 text-orange', 'danger' => 'bg-danger/15 text-danger',
    ];
@endphp

@section('content')

    <x-page-header title="مركز العمليات" subtitle="طوابير العمل واختصارات العمليات اليومية" icon="fa-briefcase" />

    {{-- WORK QUEUES --}}
    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach($queues as $queue)
            <a href="{{ $queue['url'] }}" class="card flex items-center justify-between gap-3 transition hover:-translate-y-0.5 hover:bg-white/[0.04]">
                <div>
                    <div class="text-xs text-muted">{{ $queue['label'] }}</div>
                    <div class="mt-2 text-2xl font-bold text-fg">{{ $queue['count'] }}</div>
                </div>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $tones[$queue['color']] }}"><i class="fa-solid {{ $queue['icon'] }} text-sm"></i></span>
            </a>
        @endforeach
    </section>

    {{-- SHORTCUTS FOR ONE BANK --}}
    <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
        <h2 class="flex items-center gap-2 text-sm font-bold text-fg"><i class="fa-solid fa-bolt text-brand"></i> اختصارات العمليات</h2>

        <form method="GET" action="{{ route('operations.index') }}" class="flex items-center gap-2">
            <input type="hidden" name="type" value="{{ $type }}">
            <label for="bank" class="text-xs text-muted">البنك:</label>
            <select id="bank" name="bank" class="form-input w-56 py-1.5" onchange="this.form.submit()">
                @foreach($bankOptions as $id => $name)
                    <option value="{{ $id }}" @selected($bank->id === $id)>{{ $name }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <section class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach($shortcuts as [$title, $hint, $icon, $color, $url])
            <x-hub-tile :title="$title" :description="$hint" :icon="$icon" :color="$color" :href="$url !== '#' ? $url : null" />
        @endforeach
    </section>

    {{-- RECENT OPERATIONS --}}
    <x-panel title="آخر العمليات" icon="fa-clock-rotate-left">
        <div class="segmented mb-4 flex-wrap">
            <a href="{{ route('operations.index', ['bank' => $bank->id]) }}" class="segmented-item {{ $type === '' ? 'segmented-item-active' : '' }}">الكل <span class="text-[10px] opacity-70">({{ $counts['all'] }})</span></a>
            @foreach($types as $key => [$label])
                <a href="{{ route('operations.index', ['bank' => $bank->id, 'type' => $key]) }}" class="segmented-item {{ $type === $key ? 'segmented-item-active' : '' }}">
                    {{ $label }} <span class="text-[10px] opacity-70">({{ $counts[$key] ?? 0 }})</span>
                </a>
            @endforeach
        </div>

        <div class="table-wrap">
            <table class="data-table whitespace-nowrap">
                <thead><tr><th>العملية</th><th>التفاصيل</th><th>البنك</th><th>المنفذ</th><th>الوقت</th><th>الحالة</th></tr></thead>
                <tbody>
                    @forelse($operations as $operation)
                        @php [$label, $icon, $color] = $types[$operation->type]; @endphp
                        <tr>
                            <td>
                                <span class="inline-flex items-center gap-2">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg {{ $tones[$color] }}"><i class="fa-solid {{ $icon }} text-[11px]"></i></span>
                                    <span class="font-semibold text-fg">{{ $label }}</span>
                                </span>
                            </td>
                            <td class="whitespace-normal min-w-[260px] text-fg">{{ $operation->text }}</td>
                            <td>{{ $operation->bank }}</td>
                            <td>{{ $operation->user }}</td>
                            <td>{{ $operation->time }}</td>
                            <td><x-status-badge type="import" :status="$operation->status" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-empty-state text="لا توجد عمليات من هذا النوع" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-panel>

@endsection