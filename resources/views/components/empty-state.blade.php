{{-- <x-empty-state icon="fa-inbox" text="لا توجد بيانات" /> --}}
@props(['icon' => 'fa-inbox', 'text' => 'لا توجد بيانات'])

<div class="flex flex-col items-center justify-center gap-2 py-12 text-dim">
    <i class="fa-solid {{ $icon }} text-2xl"></i>
    <span class="text-xs">{{ $text }}</span>
</div>