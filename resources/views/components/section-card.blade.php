{{-- resources/views/components/section-card.blade.php --}}
@props([
    'title',
    'icon',
    'color' => 'info',   // info | brand | warning | accent | cyan | orange
])

@php
    $tones = [
        'info'    => ['text-info',    'bg-info/15'],
        'brand'   => ['text-brand',   'bg-brand/15'],
        'warning' => ['text-warning', 'bg-warning/15'],
        'accent'  => ['text-accent',  'bg-accent/15'],
        'cyan'    => ['text-cyan',    'bg-cyan/15'],
        'orange'  => ['text-orange',  'bg-orange/15'],
    ];
    [$text, $bg] = $tones[$color] ?? $tones['info'];
@endphp

<section class="rounded-2xl border border-line bg-surface p-5">
    <div class="mb-4 flex items-center gap-2.5">
        <span class="flex h-8 w-8 items-center justify-center rounded-lg {{ $bg }} {{ $text }}">
            <i class="fa-solid {{ $icon }} text-xs"></i>
        </span>
        <h3 class="text-sm font-bold {{ $text }}">{{ $title }}</h3>
    </div>

    {{ $slot }}
</section>