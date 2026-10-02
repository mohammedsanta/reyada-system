{{-- resources/views/components/kpi-card.blade.php
     Big number + trend badge (▲ 8.4%) + small trend line (inline SVG, no JavaScript).
     good-up = false when a DECREASE is good news (e.g. "remaining to collect").
     With href the whole card is a link (used for the "needs attention" cards). --}}
@props([
    'label', 'value', 'unit' => null, 'icon', 'color' => 'brand',
    'delta' => null, 'goodUp' => true, 'spark' => [], 'hint' => null, 'href' => null,
])

@php
    // [icon box classes, sparkline stroke class]  (full class names so Tailwind can detect them)
    $tones = [
        'brand'   => ['bg-brand/15 text-brand',     'stroke-brand'],
        'info'    => ['bg-info/15 text-info',       'stroke-info'],
        'accent'  => ['bg-accent/15 text-accent',   'stroke-accent'],
        'warning' => ['bg-warning/15 text-warning', 'stroke-warning'],
        'danger'  => ['bg-danger/15 text-danger',   'stroke-danger'],
        'cyan'    => ['bg-cyan/15 text-cyan',       'stroke-cyan'],
        'orange'  => ['bg-orange/15 text-orange',   'stroke-orange'],
        'pink'    => ['bg-pink/15 text-pink',       'stroke-pink'],
    ];
    [$iconTone, $strokeTone] = $tones[$color] ?? $tones['brand'];

    // trend badge: green when the change is good news, red when it is bad news
    $up       = $delta !== null && $delta >= 0;
    $good     = $delta !== null && ($up === (bool) $goodUp);
    $deltaTone = $good ? 'bg-brand/10 text-brand' : 'bg-danger/10 text-danger';

    // sparkline points inside a 100 x 28 box
    $points = '';
    $values = array_values($spark);

    if (count($values) > 1) {
        $min = min($values);
        $max = max($values);
        $range = ($max - $min) ?: 1;
        $last = count($values) - 1;
        $coords = [];

        foreach ($values as $i => $v) {
            $coords[] = round($i / $last * 100, 1) . ',' . round(24 - (($v - $min) / $range) * 20, 1);
        }

        $points = implode(' ', $coords);
    }

    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if($href) href="{{ $href }}" @endif
    class="card relative flex flex-col justify-between gap-3 overflow-hidden {{ $href ? 'transition hover:-translate-y-0.5 hover:bg-white/[0.04]' : '' }}">

    <div class="flex items-start justify-between gap-3">
        <div>
            <div class="text-xs text-muted">{{ $label }}</div>

            <div class="mt-2 flex items-baseline gap-1.5">
                <span class="text-2xl font-bold text-fg">{{ $value }}</span>
                @if($unit) <span class="text-xs text-muted">{{ $unit }}</span> @endif
            </div>
        </div>

        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $iconTone }}">
            <i class="fa-solid {{ $icon }} text-sm"></i>
        </span>
    </div>

    @if($points !== '')
        <svg viewBox="0 0 100 28" preserveAspectRatio="none" class="h-8 w-full" aria-hidden="true">
            <polyline points="{{ $points }}" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                      vector-effect="non-scaling-stroke" class="{{ $strokeTone }}"/>
        </svg>
    @endif

    <div class="flex items-center justify-between gap-2 text-[11px]">
        @if($delta !== null)
            <span class="inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 font-bold {{ $deltaTone }}">
                <i class="fa-solid {{ $up ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }} text-[9px]"></i>{{ abs($delta) }}%
            </span>
        @endif

        <span class="text-dim">{{ $hint }}</span>
    </div>
</{{ $tag }}>