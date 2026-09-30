{{-- resources/views/components/stat-card.blade.php --}}
@props(['label', 'value', 'unit' => null])

<div class="card relative overflow-hidden">
    <div class="absolute right-0 top-0 h-0.5 w-full bg-brand/30"></div>

    <div class="text-sm text-muted">{{ $label }}</div>

    <div class="mt-3 flex items-baseline justify-between">
        <span class="text-2xl font-bold text-fg">{{ $value }}</span>

        @if($unit)
            <span class="text-sm text-brand">{{ $unit }}</span>
        @endif
    </div>
</div>