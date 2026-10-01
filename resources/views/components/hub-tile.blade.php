{{-- resources/views/components/hub-tile.blade.php
     href = null  => the page does not exist yet: the tile is dimmed and shows "قريباً". --}}
@props([
    'title',
    'description',
    'icon',
    'color' => 'info',   // cyan | brand | orange | accent | warning | danger | pink | info | muted
    'href'  => null,
])

@php
    // [icon box classes, hover border]  (full class names so Tailwind can detect them)
    $tones = [
        'cyan'    => ['border-cyan/20 bg-cyan/10 text-cyan',          'hover:border-cyan/40'],
        'brand'   => ['border-brand/20 bg-brand/10 text-brand',       'hover:border-brand/40'],
        'orange'  => ['border-orange/20 bg-orange/10 text-orange',    'hover:border-orange/40'],
        'accent'  => ['border-accent/20 bg-accent/10 text-accent',    'hover:border-accent/40'],
        'warning' => ['border-warning/20 bg-warning/10 text-warning', 'hover:border-warning/40'],
        'danger'  => ['border-danger/20 bg-danger/10 text-danger',    'hover:border-danger/40'],
        'pink'    => ['border-pink/20 bg-pink/10 text-pink',          'hover:border-pink/40'],
        'info'    => ['border-info/20 bg-info/10 text-info',          'hover:border-info/40'],
        'muted'   => ['border-line bg-white/[0.06] text-muted',       'hover:border-white/20'],
    ];

    [$iconTone, $hoverTone] = $tones[$color] ?? $tones['info'];

    $isLink = filled($href);
    $tag    = $isLink ? 'a' : 'div';
@endphp

<{{ $tag }}
    @if($isLink) href="{{ $href }}" @endif
    class="group relative flex flex-col items-center gap-3 rounded-2xl border border-line bg-surface px-6 py-8 text-center transition duration-200
           {{ $isLink ? 'hover:-translate-y-0.5 hover:bg-white/[0.04] ' . $hoverTone : 'cursor-not-allowed opacity-60' }}"
>
    <span class="flex h-11 w-11 items-center justify-center rounded-xl border {{ $iconTone }}">
        <i class="fa-solid {{ $icon }}"></i>
    </span>

    <h3 class="text-sm font-bold text-fg">{{ $title }}</h3>
    <p class="text-[11px] leading-5 text-dim">{{ $description }}</p>

    @unless($isLink)
        <span class="badge badge-neutral absolute left-3 top-3">قريباً</span>
    @endunless
</{{ $tag }}>