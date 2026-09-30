{{-- resources/views/components/sidebar-link.blade.php --}}
@props([
    'route'   => null,      // route name, e.g. 'banks.index'
    'pattern' => null,      // optional: string or array for routeIs(), e.g. ['banks.*', 'branches.*']
    'icon',
    'color'   => 'green',   // green | blue | purple | orange | yellow | red
    'badge'   => null,
])

@php
    // If the route doesn't exist yet, link to "#" instead of throwing an error.
    $href = $route && \Illuminate\Support\Facades\Route::has($route) ? route($route) : '#';

    // Default pattern: "banks.index" => "banks.*"
    $patterns = $pattern ?? ($route && str_contains($route, '.') ? \Illuminate\Support\Str::beforeLast($route, '.') . '.*' : $route);
    $active   = $patterns ? request()->routeIs($patterns) : false;

    // Full class names must be written out so Tailwind can detect them.
    $colors = [
        'green'  => ['idle' => 'text-[#687582]',    'text' => 'text-[#00ff66]',    'iconBg' => 'bg-[#00ff66]/10',    'hover' => 'group-hover:bg-[#00ff66]/10 group-hover:text-[#00ff66]',    'border' => 'border-[#00ff66]/10',    'from' => 'from-[#00ff66]/10',    'line' => 'bg-[#00ff66] shadow-[0_0_10px_#00ff66]'],
        'blue'   => ['idle' => 'text-[#687582]',    'text' => 'text-[#00aaff]',    'iconBg' => 'bg-[#00aaff]/10',    'hover' => 'group-hover:bg-[#00aaff]/10 group-hover:text-[#00aaff]',    'border' => 'border-[#00aaff]/10',    'from' => 'from-[#00aaff]/10',    'line' => 'bg-[#00aaff] shadow-[0_0_10px_#00aaff]'],
        'purple' => ['idle' => 'text-[#687582]',    'text' => 'text-[#a855f7]',    'iconBg' => 'bg-[#a855f7]/10',    'hover' => 'group-hover:bg-[#a855f7]/10 group-hover:text-[#a855f7]',    'border' => 'border-[#a855f7]/10',    'from' => 'from-[#a855f7]/10',    'line' => 'bg-[#a855f7] shadow-[0_0_10px_#a855f7]'],
        'orange' => ['idle' => 'text-[#687582]',    'text' => 'text-orange-400',   'iconBg' => 'bg-orange-400/10',   'hover' => 'group-hover:bg-orange-400/10 group-hover:text-orange-400',  'border' => 'border-orange-400/10',   'from' => 'from-orange-400/10',   'line' => 'bg-orange-400 shadow-[0_0_10px_#fb923c]'],
        'yellow' => ['idle' => 'text-yellow-400/80','text' => 'text-yellow-400',   'iconBg' => 'bg-yellow-400/10',   'hover' => 'group-hover:bg-yellow-400/10',                              'border' => 'border-yellow-400/10',   'from' => 'from-yellow-400/10',   'line' => 'bg-yellow-400 shadow-[0_0_10px_#facc15]'],
        'red'    => ['idle' => 'text-red-400/80',   'text' => 'text-red-400',      'iconBg' => 'bg-red-400/10',      'hover' => 'group-hover:bg-red-400/10',                                 'border' => 'border-red-400/10',      'from' => 'from-red-400/10',      'line' => 'bg-red-400 shadow-[0_0_10px_#f87171]'],
    ];

    $c = $colors[$color] ?? $colors['green'];
@endphp

<a
    href="{{ $href }}"
    @if($active) aria-current="page" @endif
    @class([
        'group relative mb-1 flex items-center gap-3 overflow-hidden rounded-xl px-3 py-2.5 text-sm',
        "border bg-gradient-to-r to-transparent {$c['border']} {$c['from']} {$c['text']}" => $active,
        'text-[#8c98a5] transition-all duration-200 hover:bg-white/[0.04] hover:text-white' => ! $active,
    ])
>
    {{-- Active indicator line --}}
    @if($active)
        <span class="absolute right-0 top-1/2 h-7 w-0.5 -translate-y-1/2 rounded-l-full {{ $c['line'] }}"></span>
    @endif

    {{-- Icon box --}}
    <span
        @class([
            'relative flex h-8 w-8 items-center justify-center rounded-lg',
            "{$c['iconBg']} {$c['text']}" => $active,
            "bg-white/[0.03] transition {$c['idle']} {$c['hover']}" => ! $active,
        ])
    >
        <i class="fa-solid {{ $icon }} text-xs"></i>

        @if($badge)
            <span class="absolute -right-1 -top-1 flex h-3.5 w-3.5 items-center justify-center rounded-full bg-red-500 text-[8px] font-bold text-white">
                {{ $badge }}
            </span>
        @endif
    </span>

    <span @class(['font-semibold' => $active])>{{ $slot }}</span>
</a>