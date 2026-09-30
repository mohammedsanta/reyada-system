{{-- resources/views/components/page-header.blade.php (UPDATED: added optional icon) --}}
@props(['title', 'subtitle' => null, 'icon' => null])

<header class="mb-8 flex flex-wrap items-center justify-between gap-6">
    <div>
        <h1 class="flex items-center gap-3 text-2xl font-bold text-fg">
            @if($icon)
                <i class="fa-solid {{ $icon }} text-brand"></i>
            @endif
            {{ $title }}
        </h1>

        @if($subtitle)
            <p class="mt-2 text-sm text-muted">{{ $subtitle }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex items-center gap-2">{{ $actions }}</div>
    @endisset
</header>