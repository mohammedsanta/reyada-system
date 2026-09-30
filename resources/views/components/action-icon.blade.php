{{-- resources/views/components/action-icon.blade.php --}}
@props([
    'icon',
    'color' => 'info',   // cyan | info | warning | accent | orange
    'title' => null,
    'href'  => '#',      // TODO: real route per action
])

<a href="{{ $href }}" title="{{ $title }}" aria-label="{{ $title }}" class="icon-btn icon-btn-{{ $color }}">
    <i class="fa-solid {{ $icon }}"></i>
</a>