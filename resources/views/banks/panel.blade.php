{{-- resources/views/banks/panel.blade.php --}}
@extends('layouts.app')

@section('title', $bank->name . ' - لوحة تحكم النطاق')

@section('content')

    {{-- HEADER --}}
    <header class="card relative mb-8 flex flex-wrap items-center justify-center gap-4 py-8">

        <a href="{{ route('banks.index') }}" class="btn btn-secondary btn-sm sm:absolute sm:right-6 sm:top-6">
            <i class="fa-solid fa-arrow-right text-xs"></i> رجوع
        </a>

        <div class="flex items-center gap-4">
            <span class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-xl bg-white text-black shadow-lg shadow-white/10">
                @if($bank->logo ?? null)
                    <img src="{{ $bank->logo }}" alt="{{ $bank->name }}" class="h-full w-full object-contain p-1">
                @else
                    <i class="fa-solid fa-building-columns text-xl"></i>
                @endif
            </span>

            <div>
                <h1 class="text-2xl font-bold text-fg">{{ $bank->name }}</h1>
                <p class="mt-1 text-xs text-muted">لوحة تحكم النطاق</p>
            </div>
        </div>
    </header>


    {{-- TILES --}}
    <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach($tiles as $tile)
            <x-hub-tile
                :title="$tile['title']"
                :description="$tile['description']"
                :icon="$tile['icon']"
                :color="$tile['color']"
                :href="$tile['href']"
            />
        @endforeach
    </section>

@endsection