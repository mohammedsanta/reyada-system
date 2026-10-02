{{-- Generic dialog. Open it with any element that has data-open-modal="ID".
     Close buttons use data-close-modal (handled in resources/js/app.js). --}}
@props(['id', 'title', 'width' => '42rem'])

<dialog id="{{ $id }}" class="modal" style="max-width: {{ $width }}">
    <div class="flex items-center justify-between border-b border-line px-6 py-4">
        <h3 class="text-base font-bold text-fg">{{ $title }}</h3>
        <button type="button" class="btn btn-secondary btn-sm" data-close-modal aria-label="إغلاق">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <div class="p-6">{{ $slot }}</div>
</dialog>