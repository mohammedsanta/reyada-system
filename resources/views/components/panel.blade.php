{{-- resources/views/components/panel.blade.php --}}
@props([
    'title',
    'icon' => null,
    'tone' => 'default',   // default | danger
])

@php $danger = $tone === 'danger'; @endphp

<div {{ $attributes->class([
    'rounded-2xl border p-5',
    'border-line bg-surface'           => ! $danger,
    'border-danger/20 bg-danger/[0.02]' => $danger,
]) }}>
    <div class="mb-5 flex items-center gap-2">
        @if($icon)
            <i class="fa-solid {{ $icon }} {{ $danger ? 'text-danger' : 'text-brand' }}"></i>
        @else
            <span class="h-2 w-2 rounded-full bg-brand"></span>
        @endif

        <h2 @class(['text-sm font-semibold', 'text-fg' => ! $danger, 'text-danger' => $danger])>
            {{ $title }}
        </h2>
    </div>

    {{ $slot }}
</div>