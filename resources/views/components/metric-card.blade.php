{{-- resources/views/components/metric-card.blade.php (UPDATED: added warning + danger colors) --}}
@props([
    'label',
    'value',
    'unit'  => null,
    'icon',
    'color' => 'info',   // info | accent | brand | orange | cyan | warning | danger
])

@php
    $tones = [
        'info'    => 'bg-info/15 text-info',
        'accent'  => 'bg-accent/15 text-accent',
        'brand'   => 'bg-brand/15 text-brand',
        'orange'  => 'bg-orange/15 text-orange',
        'cyan'    => 'bg-cyan/15 text-cyan',
        'warning' => 'bg-warning/15 text-warning',
        'danger'  => 'bg-danger/15 text-danger',
    ];
@endphp

<div class="card flex items-center justify-between gap-4">
    <div>
        <div class="text-xs text-muted">{{ $label }}</div>

        <div class="mt-2 flex items-baseline gap-1.5">
            <span class="text-2xl font-bold text-fg">{{ $value }}</span>

            @if($unit)
                <span class="text-xs text-muted">{{ $unit }}</span>
            @endif
        </div>
    </div>

    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $tones[$color] ?? $tones['info'] }}">
        <i class="fa-solid {{ $icon }} text-sm"></i>
    </span>
</div>