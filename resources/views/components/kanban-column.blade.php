{{-- resources/views/components/kanban-column.blade.php --}}
@props([
    'title',
    'icon',
    'color' => 'warning',   // warning | cyan | brand | info | danger
    'count' => 0,
])

@php
    $tones = [
        'warning' => ['border-b-warning/40', 'bg-warning/15 text-warning', 'bg-warning text-black'],
        'cyan'    => ['border-b-cyan/40',    'bg-cyan/15 text-cyan',       'bg-cyan text-black'],
        'brand'   => ['border-b-brand/40',   'bg-brand/15 text-brand',     'bg-brand text-black'],
        'info'    => ['border-b-info/40',    'bg-info/15 text-info',       'bg-info text-black'],
        'danger'  => ['border-b-danger/40',  'bg-danger/15 text-danger',   'bg-danger text-black'],
    ];
    [$border, $iconTone, $countTone] = $tones[$color] ?? $tones['warning'];
@endphp

<section class="overflow-hidden rounded-2xl border border-line bg-surface">
    <header class="flex items-center justify-between gap-2 border-b-2 bg-white/[0.02] px-4 py-3 {{ $border }}">
        <div class="flex items-center gap-2">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg {{ $iconTone }}">
                <i class="fa-solid {{ $icon }} text-xs"></i>
            </span>
            <h3 class="text-sm font-bold text-fg">{{ $title }}</h3>
        </div>

        <span class="min-w-[24px] rounded-full px-2 py-0.5 text-center text-[10px] font-bold {{ $countTone }}">
            {{ $count }}
        </span>
    </header>

    <div class="max-h-[60vh] min-h-[150px] space-y-2 overflow-y-auto p-3">
        @if($count > 0)
            {{ $slot }}
        @else
            <div class="flex h-[120px] flex-col items-center justify-center gap-2 text-dim">
                <i class="fa-solid fa-inbox text-xl"></i>
                <span class="text-[11px]">لا توجد بيانات</span>
            </div>
        @endif
    </div>
</section>