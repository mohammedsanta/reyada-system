{{-- <x-avatar name="Ahmed Borlsy" color="warning" size="h-11 w-11" /> : round initials --}}
@props(['name', 'color' => 'info', 'size' => 'h-9 w-9'])

@php
    $parts    = preg_split('/\s+/u', trim($name));
    $initials = mb_strtoupper(count($parts) > 1 ? mb_substr($parts[0], 0, 1) . mb_substr($parts[1], 0, 1) : mb_substr($parts[0], 0, 2));

    $tones = [
        'warning' => 'border-warning/60 bg-warning/10 text-warning',
        'brand'   => 'border-brand/60 bg-brand/10 text-brand',
        'orange'  => 'border-orange/60 bg-orange/10 text-orange',
        'info'    => 'border-info/60 bg-info/10 text-info',
        'accent'  => 'border-accent/60 bg-accent/10 text-accent',
        'cyan'    => 'border-cyan/60 bg-cyan/10 text-cyan',
    ];
@endphp

<span class="flex {{ $size }} shrink-0 items-center justify-center rounded-full border-2 text-xs font-bold {{ $tones[$color] ?? $tones['info'] }}">
    {{ $initials }}
</span>