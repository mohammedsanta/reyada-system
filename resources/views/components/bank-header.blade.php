{{-- Header used by every page that belongs to one bank.
     <x-bank-header :bank="$bank" title="توزيع الحالات"> <x-slot:actions> ...buttons... </x-slot:actions> </x-bank-header> --}}
@props(['bank', 'title', 'subtitle' => null, 'back' => null])

<header class="card mb-6 flex flex-wrap items-center justify-between gap-4">
    <div class="flex items-center gap-4">
        <a href="{{ $back ?? route('banks.panel', $bank->id) }}" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-arrow-right text-xs"></i> رجوع
        </a>

        <span class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-xl bg-white text-black">
            @if($bank->logo ?? null)
                <img src="{{ $bank->logo }}" alt="{{ $bank->name }}" class="h-full w-full object-contain p-1">
            @else
                <i class="fa-solid fa-building-columns text-lg"></i>
            @endif
        </span>

        <div>
            <h1 class="text-xl font-bold text-fg">{{ $title }}</h1>
            <p class="mt-1 text-xs text-muted">{{ $bank->name }}@if($subtitle) · {{ $subtitle }}@endif</p>
        </div>
    </div>

    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</header>