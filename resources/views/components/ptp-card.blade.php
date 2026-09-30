{{-- resources/views/components/ptp-card.blade.php --}}
@props([
    'ptp',
    'color' => 'warning',   // same color as its column
])

@php
    $accent = [
        'warning' => 'border-r-warning',
        'cyan'    => 'border-r-cyan',
        'brand'   => 'border-r-brand',
        'info'    => 'border-r-info',
        'danger'  => 'border-r-danger',
    ];

    $dueTone = [
        'muted'   => 'text-muted',
        'warning' => 'text-warning',
        'danger'  => 'text-danger',
        'brand'   => 'text-brand',
        'info'    => 'text-info',
    ];
@endphp

{{-- TODO: link to the promise / client details --}}
<a href="#" class="block rounded-xl border border-r-2 border-line bg-white/[0.03] p-3 transition hover:bg-white/[0.06] {{ $accent[$color] ?? $accent['warning'] }}">

    <div class="flex items-start justify-between gap-2">
        <h4 class="text-xs font-bold text-fg">{{ $ptp->client_name }}</h4>
        <span class="badge badge-info font-mono">{{ $ptp->client_code }}</span>
    </div>

    <div class="mt-2 flex items-baseline justify-between gap-2">
        <span class="text-sm font-bold text-fg">EGP {{ number_format($ptp->amount) }}</span>
        <span class="text-[11px] text-muted">
            <i class="fa-regular fa-calendar ml-1"></i>{{ $ptp->promise_date_label }}
        </span>
    </div>

    {{-- Partial payments: show how much was paid --}}
    @if($ptp->status === 'partial')
        <div class="mt-2">
            <div class="mb-1 flex justify-between text-[10px] text-muted">
                <span>المسدد EGP {{ number_format($ptp->paid) }}</span>
                <span>{{ $ptp->paid_percent }}%</span>
            </div>
            <div class="progress"><div class="h-full rounded-full bg-info" style="width: {{ $ptp->paid_percent }}%"></div></div>
        </div>
    @endif

    <div class="mt-2 flex items-center justify-between gap-2 text-[11px]">
        <span class="text-dim"><i class="fa-solid fa-user ml-1"></i>{{ $ptp->employee }}</span>
        <span class="font-semibold {{ $dueTone[$ptp->due_tone] ?? 'text-muted' }}">{{ $ptp->due_label }}</span>
    </div>
</a>