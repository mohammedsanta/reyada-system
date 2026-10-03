{{-- resources/views/notifications/index.blade.php --}}
@extends('layouts.app')

@section('title', 'الإشعارات')

@php
    // type => [icon, icon box classes]  (full class names for Tailwind)
    $types = [
        'payment'      => ['fa-money-bill-wave', 'bg-brand/15 text-brand'],
        'ptp'          => ['fa-handshake',       'bg-warning/15 text-warning'],
        'complaint'    => ['fa-headset',         'bg-pink/15 text-pink'],
        'distribution' => ['fa-sitemap',         'bg-accent/15 text-accent'],
        'visit'        => ['fa-person-walking',  'bg-cyan/15 text-cyan'],
        'system'       => ['fa-circle-info',     'bg-info/15 text-info'],
    ];
@endphp

@section('content')

    <x-page-header title="الإشعارات" subtitle="آخر التنبيهات والأحداث في النظام" icon="fa-bell">
        <x-slot:actions>
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit" class="btn btn-secondary btn-sm"><i class="fa-solid fa-check-double"></i> تحديد الكل كمقروء</button>
            </form>
        </x-slot:actions>
    </x-page-header>

    <x-flash />

    <div class="segmented mb-6">
        <a href="{{ route('notifications.index') }}" class="segmented-item {{ $filter === 'all' ? 'segmented-item-active' : '' }}">الكل</a>
        <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" class="segmented-item {{ $filter === 'unread' ? 'segmented-item-active' : '' }}">
            غير المقروءة <span class="badge badge-danger">{{ $unread }}</span>
        </a>
    </div>

    <div class="space-y-3">
        @forelse($items as $item)
            <form method="POST" action="{{ route('notifications.read', $item->id) }}">
                @csrf
                <button type="submit" @class(['card flex w-full items-center gap-4 text-right transition hover:bg-white/[0.04]', 'border-brand/20' => ! $item->read])>
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $types[$item->type][1] }}">
                        <i class="fa-solid {{ $types[$item->type][0] }}"></i>
                    </span>

                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-bold text-fg">{{ $item->title }}</span>
                        <span class="mt-1 block text-xs text-muted">{{ $item->body }}</span>
                    </span>

                    <span class="flex shrink-0 flex-col items-end gap-2 text-[11px] text-dim">
                        {{ $item->time }}
                        @unless($item->read) <span class="h-2 w-2 rounded-full bg-brand shadow-[0_0_8px_var(--color-brand)]"></span> @endunless
                    </span>
                </button>
            </form>
        @empty
            <div class="card"><x-empty-state icon="fa-bell-slash" text="لا توجد إشعارات" /></div>
        @endforelse
    </div>

@endsection